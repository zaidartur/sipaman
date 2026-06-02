<?php

namespace App\Console\Commands;

use App\Support\ProductTypeClassifier;
use Illuminate\Console\Command;

class BackfillProdukJenisBarang extends Command
{
    protected $signature = 'produk:backfill-jenis-barang {--dry-run : Tampilkan jumlah calon perubahan tanpa menyimpan data}';

    protected $description = 'Menyinkronkan produk lama ke master jenis pangan resmi berdasarkan jenis_pangan.';

    public function handle(ProductTypeClassifier $classifier): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $result = $classifier->reclassifyExistingProducts($dryRun);

        $this->info($dryRun ? 'DRY-RUN backfill jenis barang produk selesai.' : 'Backfill jenis barang produk selesai.');
        $this->line('Produk dicek: '.number_format($result['checked']));
        $this->line(($dryRun ? 'Akan diperbarui: ' : 'Berhasil diperbarui: ').number_format($result['updated']));
        $this->line('Masih perlu review: '.number_format($result['fallback']));

        return self::SUCCESS;
    }
}
