<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\AuthenticationService;
use App\Traits\LogsAuditTrail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    use LogsAuditTrail;

    public function __construct(private AuthenticationService $authenticationService)
    {
    }

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();
        $attempt = $this->authenticationService->attempt($credentials['identifier'], $credentials['password']);

        if ($attempt['status'] !== AuthenticationService::STATUS_AUTHENTICATED) {
            throw ValidationException::withMessages([
                'identifier' => $attempt['message'],
            ]);
        }

        /** @var User $user */
        $user = $attempt['user'];
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $this->logActivity('Login web berhasil - '.$this->authenticationService->activityIdentity($user), $user->id);

        return redirect()->intended($this->redirectPath($user));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user) {
            $this->logActivity('Logout web - '.$this->authenticationService->activityIdentity($user), $user->id);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function redirectPath(User $user): string
    {
        return match ($user->role->nama_role ?? null) {
            'admin', 'super_admin' => route('panel.dashboard'),
            'user' => route('user.dashboard'),
            default => route('home'),
        };
    }
}
