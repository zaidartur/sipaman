<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_page_contents', function (Blueprint $table) {
            $table->id();
            $table->string('section_key', 100)->unique();
            $table->string('judul', 200)->nullable();
            $table->string('subjudul', 255)->nullable();
            $table->text('konten')->nullable();
            $table->string('image_path', 255)->nullable();
            $table->string('image_alt', 255)->nullable();
            $table->string('button_text', 100)->nullable();
            $table->string('button_url', 500)->nullable();
            $table->string('secondary_button_text', 100)->nullable();
            $table->string('secondary_button_url', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_page_contents');
    }
};
