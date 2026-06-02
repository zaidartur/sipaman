<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('tipe_file', 50)->nullable()->index();
            $table->string('nama_file', 255);
            $table->integer('jumlah_baris')->default(0);
            $table->integer('jumlah_berhasil')->default(0);
            $table->integer('jumlah_gagal')->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamp('imported_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_logs');
    }
};
