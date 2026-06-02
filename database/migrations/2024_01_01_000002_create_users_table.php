<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 150);
            $table->string('email', 150)->nullable()->unique();
            $table->string('nib', 50)->nullable()->unique();
            $table->string('password')->nullable();
            $table->foreignId('role_id')->constrained('roles')->restrictOnDelete();
            $table->enum('status_akun', ['aktif', 'nonaktif', 'kunci'])->default('aktif');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
