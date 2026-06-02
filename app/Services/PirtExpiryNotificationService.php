<?php

namespace App\Services;

use App\Jobs\SendPirtExpiryWarningWhatsApp;
use App\Models\PirtExpiryNotificationLog;
use App\Models\Produk;
use App\Support\SystemSettings;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class PirtExpiryNotificationService
{
    public const NOTIFICATION_TYPE = 'pirt_expiry_warning';

    public function __construct(
        private PhoneNumberNormalizer $phoneNumberNormalizer,
        private PirtExpiryMessageRenderer $messageRenderer,
    ) {
    }

    public function preview(?array $warningDays = null): array
    {
        return $this->buildPlan($this->normalizeWarningDays($warningDays));
    }

    public function dispatchDueNotifications(?array $warningDays = null): array
    {
        $plan = $this->preview($warningDays);
        $plan['dispatched'] = [];
        $plan['dispatch_errors'] = [];

        foreach ($plan['candidates'] as $candidate) {
            try {
                $log = DB::transaction(function () use ($candidate) {
                    $existingLog = $this->existingLog(
                        (int) $candidate['produk']->id,
                        $candidate['expiry_date'],
                        (int) $candidate['warning_days']
                    );

                    if ($existingLog instanceof PirtExpiryNotificationLog) {
                        return null;
                    }

                    return PirtExpiryNotificationLog::create([
                        'produk_id' => $candidate['produk']->id,
                        'masa_berlaku_pirt' => $candidate['expiry_date'],
                        'warning_days' => $candidate['warning_days'],
                        'notification_type' => self::NOTIFICATION_TYPE,
                        'recipient_phone' => $candidate['recipient_phone'],
                        'status' => PirtExpiryNotificationLog::STATUS_PENDING,
                        'message_body' => $candidate['message_body'],
                    ]);
                });

                if (! $log instanceof PirtExpiryNotificationLog) {
                    $plan['already_logged'][] = [
                        'produk' => $candidate['produk'],
                        'warning_days' => $candidate['warning_days'],
                        'expiry_date' => $candidate['expiry_date'],
                        'status' => 'pending',
                    ];

                    continue;
                }

                SendPirtExpiryWarningWhatsApp::dispatch($log->id);

                $plan['dispatched'][] = [
                    'log' => $log,
                    'produk' => $candidate['produk'],
                    'warning_days' => $candidate['warning_days'],
                    'expiry_date' => $candidate['expiry_date'],
                    'recipient_phone' => $candidate['recipient_phone'],
                ];
            } catch (QueryException $e) {
                $plan['dispatch_errors'][] = [
                    'produk' => $candidate['produk'],
                    'warning_days' => $candidate['warning_days'],
                    'expiry_date' => $candidate['expiry_date'],
                    'error' => $e->getMessage(),
                ];
            }
        }

        $plan['totals']['already_logged'] = count($plan['already_logged']);
        $plan['totals']['dispatched'] = count($plan['dispatched']);
        $plan['totals']['dispatch_errors'] = count($plan['dispatch_errors']);

        return $plan;
    }

    private function buildPlan(array $warningDays): array
    {
        $plan = [
            'notification_type' => self::NOTIFICATION_TYPE,
            'warning_days' => $warningDays,
            'candidates' => [],
            'invalid_phone' => [],
            'already_logged' => [],
            'totals' => [
                'checked' => 0,
                'candidates' => 0,
                'invalid_phone' => 0,
                'already_logged' => 0,
            ],
        ];

        foreach ($warningDays as $warningDay) {
            $expiryDate = now()->startOfDay()->addDays($warningDay)->toDateString();

            Produk::query()
                ->verified()
                ->whereDate('masa_berlaku_pirt', $expiryDate)
                ->orderBy('id')
                ->chunkById(200, function ($products) use (&$plan, $warningDay, $expiryDate) {
                    foreach ($products as $produk) {
                        $plan['totals']['checked']++;

                        $recipientPhone = $this->phoneNumberNormalizer->normalize($produk->no_hp);

                        if (! $recipientPhone) {
                            $plan['invalid_phone'][] = [
                                'produk' => $produk,
                                'warning_days' => $warningDay,
                                'expiry_date' => $expiryDate,
                                'raw_phone' => $produk->no_hp,
                            ];

                            continue;
                        }

                        $existingLog = $this->existingLog((int) $produk->id, $expiryDate, $warningDay);

                        if ($existingLog instanceof PirtExpiryNotificationLog) {
                            $plan['already_logged'][] = [
                                'produk' => $produk,
                                'warning_days' => $warningDay,
                                'expiry_date' => $expiryDate,
                                'status' => $existingLog->status,
                            ];

                            continue;
                        }

                        $plan['candidates'][] = [
                            'produk' => $produk,
                            'warning_days' => $warningDay,
                            'expiry_date' => $expiryDate,
                            'recipient_phone' => $recipientPhone,
                            'message_body' => $this->messageRenderer->render($produk, $warningDay),
                        ];
                    }
                });
        }

        $plan['totals']['candidates'] = count($plan['candidates']);
        $plan['totals']['invalid_phone'] = count($plan['invalid_phone']);
        $plan['totals']['already_logged'] = count($plan['already_logged']);

        return $plan;
    }

    private function existingLog(int $produkId, string $expiryDate, int $warningDays): ?PirtExpiryNotificationLog
    {
        return PirtExpiryNotificationLog::query()
            ->where('produk_id', $produkId)
            ->whereDate('masa_berlaku_pirt', $expiryDate)
            ->where('warning_days', $warningDays)
            ->where('notification_type', self::NOTIFICATION_TYPE)
            ->first();
    }

    private function normalizeWarningDays(?array $warningDays): array
    {
        $days = collect($warningDays ?? SystemSettings::pirtExpiryWarningDays())
            ->map(fn ($day) => is_numeric($day) ? (int) $day : null)
            ->filter(fn (?int $day) => $day !== null && $day >= 1 && $day <= 365)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        return $days === [] ? SystemSettings::pirtExpiryWarningDays() : $days;
    }
}
