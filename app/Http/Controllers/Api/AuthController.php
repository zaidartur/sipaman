<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\LogsAuditTrail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use LogsAuditTrail;

    // ──────────────────────────────────────────────────────────
    // ──────────────────────────────────────────────────────────
    //  POST /api/auth/login  (Publik)
    //  Body: { "identifier": "<NIB pelaku usaha atau email admin>", "password": "..." }
    //
    //  identifier diterima sebagai NIB untuk pelaku usaha atau email untuk admin.
    //  - Admin/super_admin: pakai email
    //  - Pelaku usaha: pakai NIB
    // ──────────────────────────────────────────────────────────
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'identifier' => 'required|string',
            'password' => 'required|string',
        ]);

        $identifier = trim($request->identifier);

        $user = $this->findUserByIdentifier($identifier);

        // Pesan generik untuk identifier yang tidak ditemukan — jangan
        // membocorkan apakah identitas login valid.
        if (! $user) {
            throw ValidationException::withMessages([
                'identifier' => ['Identitas login atau password salah.'],
            ]);
        }

        // Akun pelaku usaha yang baru auto-create dari import: password masih null.
        // Pesan khusus supaya pelaku usaha tahu harus minta password ke admin.
        if ($user->needsPasswordSetup()) {
            return response()->json([
                'message' => 'Akun Anda belum diaktifkan. Silakan minta password ke admin SIPAMAN.',
                'needs_password_setup' => true,
            ], 403);
        }

        if (! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'identifier' => ['Identitas login atau password salah.'],
            ]);
        }

        if ($user->status_akun !== 'aktif') {
            return response()->json([
                'message' => 'Akun Anda '.$user->status_akun.'. Silakan hubungi administrator.',
            ], 403);
        }

        // Hapus token lama supaya tidak menumpuk
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        $this->logActivity('Login API berhasil - '.$this->activityIdentity($user), $user->id);

        return response()->json([
            'message' => 'Login berhasil.',
            'token' => $token,
            'user' => $this->formatUser($user),
        ]);
    }

    // ──────────────────────────────────────────────────────────
    //  POST /api/auth/logout  (Auth)
    // ──────────────────────────────────────────────────────────
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->logActivity('Logout API - '.$this->activityIdentity($user), $user->id);
        $user->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil.']);
    }

    // ──────────────────────────────────────────────────────────
    //  GET /api/auth/me  (Auth)
    // ──────────────────────────────────────────────────────────
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->formatUser($request->user()->load('role')),
        ]);
    }

    // ──────────────────────────────────────────────────────────
    //  POST /api/auth/update-profile  (Auth)
    // ──────────────────────────────────────────────────────────
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasRole('user')) {
            $data = $request->validate([
                'nama' => ['prohibited'],
                'email' => ['prohibited'],
                'password' => ['sometimes', 'confirmed', Password::min(8)],
            ], [
                'nama.prohibited' => 'Nama pelaku usaha mengikuti data PIRT dan tidak dapat diubah dari akun user.',
                'email.prohibited' => 'Akun pelaku usaha login memakai NIB, bukan email.',
            ]);
        } else {
            $data = $request->validate([
                'nama' => 'sometimes|string|max:150',
                'email' => "sometimes|required|email|max:150|unique:users,email,{$user->id}",
                'password' => ['sometimes', 'confirmed', Password::min(8)],
            ]);
        }

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

    // ── Private Helper ────────────────────────────────────────
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

    private function findUserByIdentifier(string $identifier): ?User
    {
        return User::with('role')
            ->where(function ($query) use ($identifier) {
                $query->where(function ($userQuery) use ($identifier) {
                    $userQuery->where('nib', $identifier)
                        ->whereHas('role', fn ($roleQuery) => $roleQuery->where('nama_role', 'user'));
                })->orWhere(function ($adminQuery) use ($identifier) {
                    $adminQuery->where('email', $identifier)
                        ->whereHas('role', fn ($roleQuery) => $roleQuery->whereIn('nama_role', ['admin', 'super_admin']));
                });
            })
            ->first();
    }

    private function activityIdentity(User $user): string
    {
        $role = $user->role->nama_role ?? 'unknown';
        $identifier = $user->hasRole('user')
            ? 'NIB '.($user->nib ?? "user#{$user->id}")
            : 'email '.($user->email ?? "user#{$user->id}");

        return "role: {$role}, {$identifier}";
    }
}
