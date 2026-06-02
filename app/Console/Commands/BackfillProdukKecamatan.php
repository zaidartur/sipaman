<?php

namespace App\Console\Commands;

use App\Support\KecamatanResolver;
use Illuminate\Console\Command;

class BackfillProdukKecamatan extends Command
{
    protected $signature = 'produk:backfill-kecamatan {--dry-run : Tampilkan jumlah calon perubahan tanpa menyimpan data}';

    protected $description = 'Mengisi kecamatan_id produk dari wilayah/alamat jika nama kecamatan dapat dikenali.';

    public function handle(KecamatanResolver $resolver): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $result = $resolver->backfillExistingProducts($dryRun);

        $this->info($dryRun ? 'DRY-RUN backfill kecamatan produk selesai.' : 'Backfill kecamatan produk selesai.');
        $this->line('Produk dicek: '.number_format($result['checked']));
        $this->line(($dryRun ? 'Akan diisi: ' : 'Berhasil diisi: ').number_format($result['updated']));
        $this->line('Belum dikenali: '.number_format($result['unmatched']));

        return self::SUCCESS;
    }
}
