<?php

namespace Database\Seeders;

use App\Support\ProductTypeClassifier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class JenisBarangSeeder extends Seeder
{
    public function run(): void
    {
        app(ProductTypeClassifier::class)->seedDefaults();

        Cache::forget('jenis_barangs_all');
    }
}
