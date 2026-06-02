<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PanelRoutingAndAccountManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_shared_management_routes_use_panel_names(): void
    {
        $this->assertTrue(Route::has('panel.dashboard'));
        $this->assertTrue(Route::has('panel.products.index'));
        $this->assertTrue(Route::has('panel.jenis-barang.index'));
        $this->assertTrue(Route::has('panel.pelaku-usaha.index'));
        $this->assertFalse(Route::has('panel.dashboard.alias'));

        $this->assertFalse(Route::has('admin.dashboard'));
        $this->assertFalse(Route::has('admin.products.index'));
        $this->assertFalse(Route::has('admin.jenis-barang.index'));
        $this->assertFalse(Route::has('admin.pelaku-usaha.index'));

        $this->assertTrue(Route::has('super-admin.users.index'));
        $this->assertTrue(Route::has('super-admin.settings.index'));
        $this->assertTrue(Route::has('super-admin.audit-trails.index'));
    }

    public function test_admin_login_redirects_to_panel_dashboard(): void
    {
        $admin = $this->createUser('admin', 'admin@example.test');

        $this->post('/login', [
            'identifier' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('panel.dashboard'));
    }

    public function test_admin_can_open_shared_panel_but_not_super_admin_users(): void
    {
        $this->withoutVite();

        $admin = $this->createUser('admin', 'admin@example.test');

        $this->actingAs($admin)
            ->get(route('panel.dashboard'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('panel.pelaku-usaha.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('super-admin.users.index'))
            ->assertForbidden();
    }

    public function test_super_admin_users_page_only_lists_admin_accounts(): void
    {
        $this->withoutVite();

        $superAdmin = $this->createUser('super_admin', 'super@example.test', 'Super Yang Login');
        $admin = $this->createUser('admin', 'listed-admin@example.test', 'Admin Yang Tampil');
        $pelakuUsaha = $this->createUser('user', null, 'Pelaku Yang Tidak Tampil', '1234567890123');

        $this->actingAs($superAdmin)
            ->get(route('super-admin.users.index'))
            ->assertOk()
            ->assertSee($admin->nama)
            ->assertDontSee($pelakuUsaha->nama)
            ->assertDontSee($superAdmin->email);
    }

    public function test_super_admin_user_api_matches_web_admin_account_scope(): void
    {
        $superAdmin = $this->createUser('super_admin', 'super@example.test', 'Super Yang Login');
        $admin = $this->createUser('admin', 'listed-admin@example.test', 'Admin Yang Tampil');
        $pelakuUsaha = $this->createUser('user', null, 'Pelaku Yang Tidak Tampil', '1234567890123');
        $normalAdmin = $this->createUser('admin', 'normal-admin@example.test', 'Normal Admin');

        Sanctum::actingAs($normalAdmin);
        $this->getJson('/api/super-admin/users')->assertForbidden();

        Sanctum::actingAs($superAdmin);
        $this->getJson('/api/super-admin/users')
            ->assertOk()
            ->assertJsonFragment(['email' => $admin->email])
            ->assertJsonMissing(['nama' => $pelakuUsaha->nama])
            ->assertJsonMissing(['email' => $superAdmin->email]);

        $this->getJson("/api/super-admin/users/{$admin->id}")
            ->assertOk()
            ->assertJsonPath('data.email', $admin->email);

        $this->getJson("/api/super-admin/users/{$pelakuUsaha->id}")
            ->assertForbidden();

        $this->patchJson("/api/super-admin/users/{$pelakuUsaha->id}", [
            'status_akun' => 'nonaktif',
        ])->assertForbidden();

        $this->deleteJson("/api/super-admin/users/{$pelakuUsaha->id}")
            ->assertForbidden();
    }

    private function createUser(string $roleName, ?string $email, string $name = 'User Test', ?string $nib = null): User
    {
        $role = Role::firstOrCreate(
            ['nama_role' => $roleName],
            ['deskripsi' => "{$roleName} test"]
        );

        return User::create([
            'nama' => $name,
            'email' => $email,
            'nib' => $nib,
            'password' => 'password',
            'role_id' => $role->id,
            'status_akun' => 'aktif',
        ]);
    }
}
