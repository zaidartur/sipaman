<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jenis_barangs', function (Blueprint $table) {
            if (! Schema::hasColumn('jenis_barangs', 'nomor_kategori')) {
                $table->unsignedSmallInteger('nomor_kategori')->nullable()->index();
            }

            if (! Schema::hasColumn('jenis_barangs', 'kategori_resmi')) {
                $table->string('kategori_resmi', 255)->nullable()->index();
            }

            if (! Schema::hasColumn('jenis_barangs', 'keterangan')) {
                $table->text('keterangan')->nullable();
            }

            if (! Schema::hasColumn('jenis_barangs', 'status_pirt')) {
                $table->string('status_pirt', 60)->nullable()->index();
            }

            if (! Schema::hasColumn('jenis_barangs', 'dasar_hukum')) {
                $table->string('dasar_hukum', 160)->nullable();
            }
        });
    }

    public function down(): void
    {
        $columns = collect([
            'nomor_kategori',
            'kategori_resmi',
            'keterangan',
            'status_pirt',
            'dasar_hukum',
        ])
            ->filter(fn (string $column) => Schema::hasColumn('jenis_barangs', $column))
            ->all();

        if ($columns === []) {
            return;
        }

        Schema::table('jenis_barangs', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });
    }
};
