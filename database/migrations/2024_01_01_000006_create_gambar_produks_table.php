<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gambar_produks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produks')->cascadeOnDelete();
            $table->string('url_gambar', 500);
            $table->boolean('is_primary')->default(false);
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamps();

            $table->index(['produk_id', 'is_primary'], 'idx_gambar_produk_primary');
            $table->unique('produk_id', 'gambar_produks_produk_id_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gambar_produks');
    }
};
