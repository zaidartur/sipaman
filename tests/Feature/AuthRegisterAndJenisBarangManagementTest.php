<?php

namespace Tests\Feature;

use App\Models\JenisBarang;
use App\Models\JenisBarangAlias;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AuthRegisterAndJenisBarangManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_register_routes_are_removed(): void
    {
        $this->assertFalse(Route::has('register'));
        $this->assertFalse(Route::has('api.auth.register'));

        $this->get('/register')->assertNotFound();
        $this->postJson('/api/auth/register')->assertNotFound();
    }

    public function test_admin_can_create_jenis_barang_with_auto_slug_and_aliases(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->post(route('admin.jenis-barang.store'), [
                'nama_jenis' => 'Keripik Tradisional',
                'slug' => '',
                'deskripsi' => 'Camilan kering produksi UMKM.',
                'is_active' => '1',
                'aliases' => "Keripik\nKripik",
            ])
            ->assertRedirect(route('admin.jenis-barang.index'))
            ->assertSessionHas('success');

        $jenisBarang = JenisBarang::where('slug', 'keripik-tradisional')->firstOrFail();

        $this->assertSame('Keripik Tradisional', $jenisBarang->nama_jenis);
        $this->assertTrue($jenisBarang->is_active);
        $this->assertDatabaseHas('jenis_barang_aliases', [
            'jenis_barang_id' => $jenisBarang->id,
            'keyword' => 'keripik',
            'priority' => 1,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('jenis_barang_aliases', [
            'jenis_barang_id' => $jenisBarang->id,
            'keyword' => 'kripik',
            'priority' => 2,
            'is_active' => true,
        ]);
    }

    public function test_auto_slug_conflict_returns_validation_error(): void
    {
        $admin = $this->createAdmin();

        JenisBarang::create([
            'nama_jenis' => 'Kategori Lama',
            'slug' => 'keripik-tradisional',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.jenis-barang.create'))
            ->post(route('admin.jenis-barang.store'), [
                'nama_jenis' => 'Keripik Tradisional',
                'slug' => '',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.jenis-barang.create'))
            ->assertSessionHasErrors('slug');
    }

    public function test_admin_can_update_jenis_barang_and_replace_own_aliases(): void
    {
        $admin = $this->createAdmin();
        $jenisBarang = JenisBarang::create([
            'nama_jenis' => 'Kategori Awal',
            'slug' => 'kategori-awal',
            'deskripsi' => 'Deskripsi awal.',
            'is_active' => true,
        ]);
        JenisBarangAlias::create([
            'jenis_barang_id' => $jenisBarang->id,
            'keyword' => 'alias lama',
            'priority' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.jenis-barang.update', $jenisBarang), [
                'nama_jenis' => 'Kategori Baru',
                'slug' => 'Kategori Baru',
                'deskripsi' => 'Deskripsi baru.',
                'is_active' => '0',
                'aliases' => "Alias Baru\nAlias Kedua",
            ])
            ->assertRedirect(route('admin.jenis-barang.index'))
            ->assertSessionHas('success');

        $jenisBarang->refresh();

        $this->assertSame('Kategori Baru', $jenisBarang->nama_jenis);
        $this->assertSame('kategori-baru', $jenisBarang->slug);
        $this->assertFalse($jenisBarang->is_active);
        $this->assertDatabaseMissing('jenis_barang_aliases', [
            'jenis_barang_id' => $jenisBarang->id,
            'keyword' => 'alias lama',
        ]);
        $this->assertDatabaseHas('jenis_barang_aliases', [
            'jenis_barang_id' => $jenisBarang->id,
            'keyword' => 'alias baru',
            'priority' => 1,
        ]);
        $this->assertDatabaseHas('jenis_barang_aliases', [
            'jenis_barang_id' => $jenisBarang->id,
            'keyword' => 'alias kedua',
            'priority' => 2,
        ]);
    }

    public function test_alias_conflict_is_rejected_without_moving_alias(): void
    {
        $admin = $this->createAdmin();
        $owner = JenisBarang::create([
            'nama_jenis' => 'Pemilik Alias',
            'slug' => 'pemilik-alias',
            'is_active' => true,
        ]);
        $target = JenisBarang::create([
            'nama_jenis' => 'Target Edit',
            'slug' => 'target-edit',
            'is_active' => true,
        ]);
        $alias = JenisBarangAlias::create([
            'jenis_barang_id' => $owner->id,
            'keyword' => 'keripik',
            'priority' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.jenis-barang.edit', $target))
            ->put(route('admin.jenis-barang.update', $target), [
                'nama_jenis' => 'Target Edit',
                'slug' => 'target-edit',
                'is_active' => '1',
                'aliases' => 'keripik',
            ])
            ->assertRedirect(route('admin.jenis-barang.edit', $target))
            ->assertSessionHasErrors('aliases');

        $this->assertDatabaseHas('jenis_barang_aliases', [
            'id' => $alias->id,
            'jenis_barang_id' => $owner->id,
            'keyword' => 'keripik',
        ]);
    }

    private function createAdmin(): User
    {
        $role = Role::firstOrCreate(
            ['nama_role' => 'admin'],
            ['deskripsi' => 'Admin test']
        );

        return User::create([
            'nama' => 'Admin Test',
            'email' => 'admin@example.test',
            'password' => 'password',
            'role_id' => $role->id,
            'status_akun' => 'aktif',
        ]);
    }
}
