<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verifikasi_produks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produks')->cascadeOnDelete();
            $table->foreignId('user_verifikator_id')->constrained('users')->restrictOnDelete();
            $table->boolean('verifikasi_produk')->default(false);
            $table->boolean('verifikasi_label')->default(false);
            $table->boolean('pkp')->default(false);
            $table->boolean('cppob_pemeriksaan_sarana')->default(false);
            $table->boolean('status_komitmen')->default(false);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verifikasi_produks');
    }
};
