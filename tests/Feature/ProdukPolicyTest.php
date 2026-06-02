<?php

namespace Tests\Feature;

use App\Models\Produk;
use App\Models\Role;
use App\Models\User;
use App\Policies\ProdukPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProdukPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_and_super_admin_cannot_manually_mutate_official_product_data(): void
    {
        $policy = new ProdukPolicy();
        $product = $this->createProduct();

        foreach (['admin', 'super_admin'] as $role) {
            $user = $this->createUser($role);

            $this->assertFalse($policy->create($user));
            $this->assertFalse($policy->update($user, $product));
            $this->assertFalse($policy->delete($user, $product));
        }
    }

    public function test_product_view_policy_still_allows_public_verified_admin_and_owner_access(): void
    {
        $policy = new ProdukPolicy();
        $owner = $this->createUser('user', '1234567890123');
        $admin = $this->createUser('admin');
        $verified = $this->createProduct(['is_verified' => true]);
        $ownedUnverified = $this->createProduct(['user_id' => $owner->id, 'is_verified' => false]);

        $this->assertTrue($policy->view(null, $verified));
        $this->assertTrue($policy->view($admin, $ownedUnverified));
        $this->assertTrue($policy->view($owner, $ownedUnverified));
    }

    private function createUser(string $roleName, ?string $nib = null): User
    {
        $role = Role::firstOrCreate(
            ['nama_role' => $roleName],
            ['deskripsi' => "{$roleName} test"]
        );

        return User::create([
            'nama' => "{$roleName} Test",
            'email' => $roleName === 'user' ? null : "{$roleName}@example.test",
            'nib' => $nib,
            'password' => 'password',
            'role_id' => $role->id,
            'status_akun' => 'aktif',
        ]);
    }

    private function createProduct(array $overrides = []): Produk
    {
        return Produk::create([
            'no_sppirt' => 'PIRT-'.str()->random(8),
            'nama_branding' => 'Produk Policy',
            'kategori_pangan' => 'Kategori',
            'jenis_pangan' => 'Abon Daging',
            'nama_pelaku_usaha' => 'Pelaku Test',
            'alamat' => 'Alamat Test',
            'is_verified' => false,
            ...$overrides,
        ]);
    }
}
