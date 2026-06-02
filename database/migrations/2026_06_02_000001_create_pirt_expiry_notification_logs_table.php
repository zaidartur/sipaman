<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pirt_expiry_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produks')->cascadeOnDelete();
            $table->date('masa_berlaku_pirt');
            $table->unsignedSmallInteger('warning_days');
            $table->string('notification_type', 80);
            $table->string('recipient_phone', 30);
            $table->string('status', 30)->default('pending');
            $table->text('message_body')->nullable();
            $table->json('response_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['produk_id', 'masa_berlaku_pirt', 'warning_days', 'notification_type'],
                'pirt_expiry_notification_unique'
            );
            $table->index(['status', 'notification_type'], 'pirt_expiry_notification_status_type_idx');
            $table->index('masa_berlaku_pirt', 'pirt_expiry_notification_expiry_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pirt_expiry_notification_logs');
    }
};
