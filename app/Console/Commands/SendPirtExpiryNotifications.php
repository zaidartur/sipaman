<?php

namespace App\Console\Commands;

use App\Services\PirtExpiryNotificationService;
use App\Services\StarSenderClient;
use App\Support\SystemSettings;
use Illuminate\Console\Command;

class SendPirtExpiryNotifications extends Command
{
    protected $signature = 'pirt:send-expiry-notifications {--dry-run : Tampilkan kandidat tanpa membuat log, job, atau panggilan StarSender}';

    protected $description = 'Mengirim pengingat WhatsApp masa berlaku PIRT melalui StarSender.';

    public function handle(PirtExpiryNotificationService $notificationService, StarSenderClient $starSenderClient): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if (! SystemSettings::pirtExpiryNotificationsEnabled() && ! $dryRun) {
            $this->warn('Notifikasi masa berlaku PIRT sedang nonaktif di System Settings.');

            return self::SUCCESS;
        }

        if (! SystemSettings::pirtExpiryNotificationsEnabled() && $dryRun) {
            $this->warn('Notifikasi sedang nonaktif. Dry-run tetap menampilkan kandidat tanpa mengirim pesan.');
        }

        if ($dryRun) {
            $plan = $notificationService->preview();

            $this->info('DRY-RUN Notifikasi Masa Berlaku PIRT');
            $this->line('Tidak ada log pending/sent yang dibuat, tidak ada job yang didispatch, dan StarSender tidak dipanggil.');
            $this->renderPlan($plan);

            return self::SUCCESS;
        }

        if (! $starSenderClient->isConfigured()) {
            $this->error('API key StarSender belum diisi. Isi STARSENDER_DEVICE_API_KEY di .env sebelum menjalankan pengiriman asli.');

            return self::FAILURE;
        }

        $plan = $notificationService->dispatchDueNotifications();

        $this->info('Pengiriman notifikasi masa berlaku PIRT diproses.');
        $this->renderPlan($plan);
        $this->line('Job dikirim: ' . number_format($plan['totals']['dispatched'] ?? 0));

        if (($plan['totals']['dispatch_errors'] ?? 0) > 0) {
            $this->warn('Ada error saat membuat log/dispatch job. Periksa output dan log aplikasi.');
        }

        return self::SUCCESS;
    }

    private function renderPlan(array $plan): void
    {
        $totals = $plan['totals'] ?? [];

        $this->line('Tipe notifikasi: ' . ($plan['notification_type'] ?? PirtExpiryNotificationService::NOTIFICATION_TYPE));
        $this->line('Hari peringatan: ' . implode(',', $plan['warning_days'] ?? []));
        $this->line('Produk dicek: ' . number_format($totals['checked'] ?? 0));
        $this->line('Kandidat siap kirim: ' . number_format($totals['candidates'] ?? 0));
        $this->line('Nomor invalid: ' . number_format($totals['invalid_phone'] ?? 0));
        $this->line('Diskip karena sudah pernah dibuat/dikirim: ' . number_format($totals['already_logged'] ?? 0));

        $this->renderRows('Kandidat siap kirim', $plan['candidates'] ?? []);
        $this->renderRows('Nomor invalid', $plan['invalid_phone'] ?? [], includePhone: true, phoneKey: 'raw_phone');
        $this->renderRows('Sudah pernah dibuat/dikirim', $plan['already_logged'] ?? [], includeStatus: true);
    }

    private function renderRows(string $title, array $rows, bool $includePhone = false, ?string $phoneKey = null, bool $includeStatus = false): void
    {
        if ($rows === []) {
            return;
        }

        $this->newLine();
        $this->line($title . ' (maksimal 10 baris):');

        $headers = ['Produk ID', 'No SPPIRT', 'Produk', 'Masa Berlaku', 'Hari'];

        if ($includePhone) {
            $headers[] = 'Nomor';
        }

        if ($includeStatus) {
            $headers[] = 'Status Log';
        }

        $this->table($headers, collect($rows)->take(10)->map(function (array $row) use ($includePhone, $phoneKey, $includeStatus) {
            $produk = $row['produk'];
            $data = [
                $produk->id,
                $produk->no_sppirt,
                $produk->nama_branding,
                $row['expiry_date'],
                $row['warning_days'],
            ];

            if ($includePhone) {
                $data[] = $row[$phoneKey ?: 'recipient_phone'] ?? '-';
            }

            if ($includeStatus) {
                $data[] = $row['status'] ?? '-';
            }

            return $data;
        })->all());
    }
}
