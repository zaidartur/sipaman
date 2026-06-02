<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_trails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('aksi', 50); // create, update, delete, verify, import
            $table->string('tabel_terkait', 100);
            $table->unsignedBigInteger('record_id')->nullable();
            $table->json('data_lama')->nullable();
            $table->json('data_baru')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at'], 'idx_audit_user_created');
            $table->index('aksi', 'idx_audit_aksi');
            $table->index('tabel_terkait', 'idx_audit_tabel');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_trails');
    }
};
