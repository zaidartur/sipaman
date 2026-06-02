<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuthenticationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_and_super_admin_login_with_email(): void
    {
        $admin = $this->createUser('admin', email: 'admin@example.test');
        $superAdmin = $this->createUser('super_admin', email: 'super@example.test');

        $this->post('/login', [
            'identifier' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('panel.dashboard'));

        $this->postJson('/api/auth/login', [
            'identifier' => $superAdmin->email,
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonPath('user.role', 'super_admin')
            ->assertJsonStructure(['token']);
    }

    public function test_pelaku_usaha_login_with_nib_not_email(): void
    {
        $user = $this->createUser('user', email: 'pelaku@example.test', nib: '1234567890123');

        $this->post('/login', [
            'identifier' => $user->nib,
            'password' => 'password',
        ])->assertRedirect(route('user.dashboard'));

        Auth::guard('web')->logout();
        $this->flushSession();

        $this->post('/login', [
            'identifier' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('identifier');

        $this->postJson('/api/auth/login', [
            'identifier' => $user->email,
            'password' => 'password',
        ])->assertJsonValidationErrors('identifier');
    }

    public function test_null_password_and_inactive_accounts_are_blocked(): void
    {
        $needsPassword = $this->createUser('user', nib: '1234567890124', password: null);
        $inactiveAdmin = $this->createUser('admin', email: 'inactive@example.test', status: 'nonaktif');

        $this->postJson('/api/auth/login', [
            'identifier' => $needsPassword->nib,
            'password' => 'password',
        ])
            ->assertForbidden()
            ->assertJson([
                'needs_password_setup' => true,
            ]);

        $this->postJson('/api/auth/login', [
            'identifier' => $inactiveAdmin->email,
            'password' => 'password',
        ])
            ->assertForbidden()
            ->assertJsonPath('message', 'Akun Anda nonaktif. Silakan hubungi administrator.');
    }

    private function createUser(
        string $roleName,
        ?string $email = null,
        ?string $nib = null,
        ?string $password = 'password',
        string $status = 'aktif'
    ): User {
        $role = Role::firstOrCreate(
            ['nama_role' => $roleName],
            ['deskripsi' => "{$roleName} test"]
        );

        return User::create([
            'nama' => "{$roleName} Test",
            'email' => $email,
            'nib' => $nib,
            'password' => $password,
            'role_id' => $role->id,
            'status_akun' => $status,
        ]);
    }
}
