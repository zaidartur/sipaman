<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthenticationService
{
    public const STATUS_AUTHENTICATED = 'authenticated';
    public const STATUS_INVALID = 'invalid';
    public const STATUS_NEEDS_PASSWORD_SETUP = 'needs_password_setup';
    public const STATUS_INACTIVE = 'inactive';

    public const INVALID_CREDENTIALS_MESSAGE = 'Identitas login atau password salah.';
    public const NEEDS_PASSWORD_MESSAGE = 'Akun Anda belum diaktifkan. Silakan minta password ke admin SIPAMAN.';

    public function attempt(string $identifier, string $password): array
    {
        $identifier = trim($identifier);
        $user = $this->findUserByIdentifier($identifier);

        if (! $user) {
            return $this->result(self::STATUS_INVALID, self::INVALID_CREDENTIALS_MESSAGE);
        }

        if ($user->needsPasswordSetup()) {
            return $this->result(self::STATUS_NEEDS_PASSWORD_SETUP, self::NEEDS_PASSWORD_MESSAGE, $user);
        }

        if (! Hash::check($password, $user->password)) {
            return $this->result(self::STATUS_INVALID, self::INVALID_CREDENTIALS_MESSAGE);
        }

        if ($user->status_akun !== 'aktif') {
            return $this->result(
                self::STATUS_INACTIVE,
                'Akun Anda '.$user->status_akun.'. Silakan hubungi administrator.',
                $user
            );
        }

        return $this->result(self::STATUS_AUTHENTICATED, 'Login berhasil.', $user);
    }

    public function findUserByIdentifier(string $identifier): ?User
    {
        $identifier = trim($identifier);

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

    public function activityIdentity(User $user): string
    {
        $role = $user->role->nama_role ?? 'unknown';
        $identifier = $user->hasRole('user')
            ? 'NIB '.($user->nib ?? "user#{$user->id}")
            : 'email '.($user->email ?? "user#{$user->id}");

        return "role: {$role}, {$identifier}";
    }

    private function result(string $status, string $message, ?User $user = null): array
    {
        return [
            'status' => $status,
            'message' => $message,
            'user' => $user,
        ];
    }
}
