<?php

namespace Tests\Feature;

use App\Models\JenisBarang;
use App\Models\Produk;
use App\Support\ProductTypeClassifier;
use Database\Seeders\JenisBarangSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTypeClassifierBackfillTest extends TestCase
{
    use RefreshDatabase;

    public function test_dry_run_reports_changes_without_updating_products(): void
    {
        $this->seed(JenisBarangSeeder::class);
        $product = $this->createProduct('Produk Dry Run', '1. Abon Daging');

        $result = app(ProductTypeClassifier::class)->reclassifyExistingProducts(dryRun: true);

        $this->assertSame(1, $result['checked']);
        $this->assertSame(1, $result['updated']);
        $this->assertNull($product->fresh()->jenis_barang_id);
    }

    public function test_backfill_updates_matching_products(): void
    {
        $this->seed(JenisBarangSeeder::class);
        $target = JenisBarang::where('nama_jenis', 'Abon Daging')->firstOrFail();
        $product = $this->createProduct('Produk Match', '1. Abon Daging');

        $result = app(ProductTypeClassifier::class)->reclassifyExistingProducts();

        $this->assertSame(1, $result['checked']);
        $this->assertSame(1, $result['updated']);
        $this->assertSame($target->id, $product->fresh()->jenis_barang_id);
    }

    public function test_unknown_products_go_to_review_fallback_without_creating_legacy_categories(): void
    {
        $this->seed(JenisBarangSeeder::class);
        $fallback = app(ProductTypeClassifier::class)->fallbackCategory();
        $product = $this->createProduct('Produk Perlu Review', 'Jenis Pangan Tidak Ada Di Master');

        $result = app(ProductTypeClassifier::class)->reclassifyExistingProducts();

        $this->assertSame(1, $result['checked']);
        $this->assertSame(1, $result['fallback']);
        $this->assertSame($fallback->id, $product->fresh()->jenis_barang_id);
        $this->assertDatabaseMissing('jenis_barangs', [
            'nama_jenis' => 'Makanan Ringan',
            'is_active' => true,
        ]);
    }

    private function createProduct(string $name, string $jenisPangan): Produk
    {
        return Produk::create([
            'no_sppirt' => 'PIRT-'.str()->random(8),
            'nama_branding' => $name,
            'kategori_pangan' => 'Kategori',
            'jenis_pangan' => $jenisPangan,
            'nama_pelaku_usaha' => 'Pelaku Test',
            'alamat' => 'Alamat Test',
            'is_verified' => false,
        ]);
    }
}
