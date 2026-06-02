<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('no_sppirt', 100)->unique();
            $table->string('nama_branding', 500);

            // Data legal PIRT dari file "Rekap Data PIRT Diterbitkan".
            $table->string('kategori_pangan', 500)->nullable();
            $table->string('jenis_pangan', 500)->nullable();
            $table->string('kemasan', 150)->nullable();
            $table->string('cara_penyimpanan', 150)->nullable();
            $table->string('wilayah', 500)->nullable();

            // Nullable karena file PIRT resmi tidak selalu menyediakan kecamatan terpisah.
            $table->foreignId('kecamatan_id')->nullable()->constrained('kecamatans')->nullOnDelete();
            $table->foreignId('jenis_barang_id')->nullable()->constrained('jenis_barangs')->nullOnDelete();

            $table->string('nama_pelaku_usaha', 150);
            $table->text('alamat');
            $table->string('nib', 50)->nullable();
            $table->string('no_hp', 100)->nullable();
            $table->string('nama_toko', 500)->nullable();
            $table->text('alamat_toko')->nullable();
            $table->unsignedInteger('harga')->nullable();
            $table->text('deskripsi')->nullable();
            $table->date('tanggal_pengajuan')->nullable();
            $table->date('tanggal_verifikasi')->nullable();
            $table->date('masa_berlaku_pirt')->nullable();
            $table->string('status_oss', 100)->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();

            $table->index('is_verified', 'idx_produks_is_verified');
            $table->index('user_id', 'idx_produks_user');
            $table->index('kecamatan_id', 'idx_produks_kecamatan');
            $table->index('jenis_barang_id', 'idx_produks_jenis_barang');
            $table->index('nama_branding', 'idx_produks_nama_branding');
            $table->index('masa_berlaku_pirt', 'idx_produks_masa_berlaku_pirt');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produks');
    }
};
