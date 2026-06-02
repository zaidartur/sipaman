<?php

namespace Tests\Feature;

use App\Models\Produk;
use App\Models\Role;
use App\Models\User;
use App\Models\VerifikasiProduk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminVerificationProcessFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_verification_tabs(): void
    {
        $this->withoutVite();

        $admin = $this->createAdmin();
        $verified = $this->createProduct('Produk Terverifikasi', 'PIRT-TAB-001', true);
        $process = $this->createProduct('Produk Proses', 'PIRT-TAB-002');
        $notStarted = $this->createProduct('Produk Belum', 'PIRT-TAB-003');

        VerifikasiProduk::create([
            'produk_id' => $process->id,
            'user_verifikator_id' => $admin->id,
            'verifikasi_produk' => true,
            'verifikasi_label' => true,
            'pkp' => false,
            'cppob_pemeriksaan_sarana' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('panel.verifications.index'))
            ->assertOk()
            ->assertSee($verified->nama_branding)
            ->assertSee($process->nama_branding)
            ->assertSee($notStarted->nama_branding);

        $this->actingAs($admin)
            ->get(route('panel.verifications.index', ['tab' => 'terverifikasi']))
            ->assertOk()
            ->assertSee($verified->nama_branding)
            ->assertDontSee($process->nama_branding)
            ->assertDontSee($notStarted->nama_branding);

        $this->actingAs($admin)
            ->get(route('panel.verifications.index', ['tab' => 'proses']))
            ->assertOk()
            ->assertSee($process->nama_branding)
            ->assertDontSee($verified->nama_branding)
            ->assertDontSee($notStarted->nama_branding);

        $this->actingAs($admin)
            ->get(route('panel.verifications.index', ['tab' => 'belum']))
            ->assertOk()
            ->assertSee($notStarted->nama_branding)
            ->assertDontSee($verified->nama_branding)
            ->assertDontSee($process->nama_branding);
    }

    public function test_admin_can_filter_process_tab_by_tracking_combination(): void
    {
        $this->withoutVite();

        $admin = $this->createAdmin();
        $matching = $this->createProduct('Produk Cocok', 'PIRT-001');
        $notMatching = $this->createProduct('Produk Tidak Cocok', 'PIRT-002');

        VerifikasiProduk::create([
            'produk_id' => $matching->id,
            'user_verifikator_id' => $admin->id,
            'verifikasi_produk' => true,
            'verifikasi_label' => false,
            'pkp' => true,
            'cppob_pemeriksaan_sarana' => false,
        ]);

        VerifikasiProduk::create([
            'produk_id' => $notMatching->id,
            'user_verifikator_id' => $admin->id,
            'verifikasi_produk' => true,
            'verifikasi_label' => true,
            'pkp' => true,
            'cppob_pemeriksaan_sarana' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('panel.verifications.index', [
                'tab' => 'proses',
                'verifikasi_produk' => '1',
                'verifikasi_label' => '0',
                'pkp' => '1',
                'cppob' => '0',
            ]))
            ->assertOk()
            ->assertSee('Produk Cocok')
            ->assertDontSee('Produk Tidak Cocok')
            ->assertSee('Produk: Ya')
            ->assertSee('Label: Tidak');
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

    private function createProduct(string $name, string $noSppirt, bool $isVerified = false): Produk
    {
        return Produk::create([
            'no_sppirt' => $noSppirt,
            'nama_branding' => $name,
            'kategori_pangan' => 'Kategori',
            'jenis_pangan' => 'Abon Daging',
            'nama_pelaku_usaha' => 'Pelaku Test',
            'alamat' => 'Alamat Test',
            'is_verified' => $isVerified,
        ]);
    }
}
