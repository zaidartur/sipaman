<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PirtExpiryNotificationLog extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'produk_id',
        'masa_berlaku_pirt',
        'warning_days',
        'notification_type',
        'recipient_phone',
        'status',
        'message_body',
        'response_payload',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'masa_berlaku_pirt' => 'date',
        'response_payload' => 'array',
        'sent_at' => 'datetime',
    ];

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class);
    }
}
