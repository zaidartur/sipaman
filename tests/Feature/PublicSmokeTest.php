<?php

namespace Tests\Feature;

use App\Models\Kecamatan;
use App\Models\JenisBarang;
use App\Models\Produk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders(): void
    {
        $this->withoutVite();

        $this->get('/')
            ->assertOk()
            ->assertSee('SIPAMAN');
    }

    public function test_home_kecamatan_filter_uses_database_options_and_kecamatan_id(): void
    {
        $this->withoutVite();

        Kecamatan::create([
            'nama_kecamatan' => 'Jaten',
            'kab_kota' => 'Karanganyar',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('name="kecamatan_id"', false)
            ->assertSee('Jaten');
    }

    public function test_public_catalog_only_shows_verified_products(): void
    {
        $this->withoutVite();

        $this->createProduct('Produk Publik', isVerified: true);
        $this->createProduct('Produk Tersembunyi', isVerified: false);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Produk Publik')
            ->assertDontSee('Produk Tersembunyi');

        $this->getJson('/api/produk')
            ->assertOk()
            ->assertJsonFragment(['nama_branding' => 'Produk Publik'])
            ->assertJsonMissing(['nama_branding' => 'Produk Tersembunyi']);
    }

    public function test_public_catalog_filters_by_kecamatan_jenis_barang_and_search(): void
    {
        $this->withoutVite();

        $jaten = Kecamatan::create(['nama_kecamatan' => 'Jaten', 'kab_kota' => 'Karanganyar']);
        $tasikmadu = Kecamatan::create(['nama_kecamatan' => 'Tasikmadu', 'kab_kota' => 'Karanganyar']);
        $abon = JenisBarang::create(['nama_jenis' => 'Abon Daging', 'slug' => 'abon-daging', 'is_active' => true]);
        $roti = JenisBarang::create(['nama_jenis' => 'Roti Manis', 'slug' => 'roti-manis', 'is_active' => true]);

        $this->createProduct('Abon Sapi Jaten', $jaten->id, $abon->id);
        $this->createProduct('Roti Tasikmadu', $tasikmadu->id, $roti->id);

        $filters = [
            'search' => 'Abon',
            'kecamatan_id' => $jaten->id,
            'jenis_barang_id' => $abon->id,
        ];

        $this->get(route('products.index', $filters))
            ->assertOk()
            ->assertSee('Abon Sapi Jaten')
            ->assertDontSee('Roti Tasikmadu');

        $this->getJson('/api/produk?'.http_build_query($filters))
            ->assertOk()
            ->assertJsonFragment(['nama_branding' => 'Abon Sapi Jaten'])
            ->assertJsonMissing(['nama_branding' => 'Roti Tasikmadu']);
    }

    private function createProduct(
        string $name,
        ?int $kecamatanId = null,
        ?int $jenisBarangId = null,
        bool $isVerified = true
    ): Produk {
        return Produk::create([
            'no_sppirt' => 'PIRT-'.str()->random(8),
            'nama_branding' => $name,
            'kategori_pangan' => 'Kategori',
            'jenis_pangan' => 'Abon Daging',
            'kecamatan_id' => $kecamatanId,
            'jenis_barang_id' => $jenisBarangId,
            'nama_pelaku_usaha' => 'Pelaku Test',
            'alamat' => 'Alamat Test',
            'is_verified' => $isVerified,
        ]);
    }
}
