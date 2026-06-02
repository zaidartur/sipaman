<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Models\User;
use App\Services\AuthenticationService;
use App\Traits\LogsAuditTrail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use LogsAuditTrail;

    public function __construct(private AuthenticationService $authenticationService)
    {
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        $attempt = $this->authenticationService->attempt($credentials['identifier'], $credentials['password']);

        if ($attempt['status'] === AuthenticationService::STATUS_INVALID) {
            throw ValidationException::withMessages([
                'identifier' => [$attempt['message']],
            ]);
        }

        if ($attempt['status'] === AuthenticationService::STATUS_NEEDS_PASSWORD_SETUP) {
            return response()->json([
                'message' => $attempt['message'],
                'needs_password_setup' => true,
            ], 403);
        }

        if ($attempt['status'] === AuthenticationService::STATUS_INACTIVE) {
            return response()->json([
                'message' => $attempt['message'],
            ], 403);
        }

        /** @var User $user */
        $user = $attempt['user'];
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        $this->logActivity('Login API berhasil - '.$this->authenticationService->activityIdentity($user), $user->id);

        return response()->json([
            'message' => 'Login berhasil.',
            'token' => $token,
            'user' => $this->formatUser($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->logActivity('Logout API - '.$this->authenticationService->activityIdentity($user), $user->id);
        $user->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->formatUser($request->user()->load('role')),
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $sebelum = $user->only(['nama', 'email']);

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $user->update($data);

        $this->logAudit('update', 'users', $user->id, $sebelum, $user->fresh()->only(['nama', 'email']));

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'user' => $this->formatUser($user->fresh()->load('role')),
        ]);
    }

    private function formatUser(User $user): array
    {
        $data = [
            'id' => $user->id,
            'nama' => $user->nama,
            'nib' => $user->nib,
            'role' => $user->role->nama_role ?? null,
            'status_akun' => $user->status_akun,
        ];

        if (! $user->hasRole('user')) {
            $data['email'] = $user->email;
        }

        return $data;
    }
}
