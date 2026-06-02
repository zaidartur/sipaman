<?php

namespace App\Jobs;

use App\Models\PirtExpiryNotificationLog;
use App\Services\StarSenderClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class SendPirtExpiryWarningWhatsApp implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(public int $notificationLogId)
    {
    }

    public function handle(StarSenderClient $starSenderClient): void
    {
        $log = PirtExpiryNotificationLog::query()->find($this->notificationLogId);

        if (! $log instanceof PirtExpiryNotificationLog || $log->status !== PirtExpiryNotificationLog::STATUS_PENDING) {
            return;
        }

        try {
            if (! $starSenderClient->isConfigured()) {
                $this->markFailed($log, 'API key StarSender belum diisi.');

                return;
            }

            $response = $starSenderClient->sendText($log->recipient_phone, (string) $log->message_body);

            $log->forceFill([
                'status' => PirtExpiryNotificationLog::STATUS_SENT,
                'response_payload' => $response,
                'error_message' => null,
                'sent_at' => now(),
            ])->save();
        } catch (Throwable $e) {
            $this->markFailed($log, $e->getMessage());
        }
    }

    private function markFailed(PirtExpiryNotificationLog $log, string $message): void
    {
        $log->forceFill([
            'status' => PirtExpiryNotificationLog::STATUS_FAILED,
            'error_message' => $message,
        ])->save();
    }
}
