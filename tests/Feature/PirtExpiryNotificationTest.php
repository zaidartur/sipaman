<?php

namespace Tests\Feature;

use App\Jobs\SendPirtExpiryWarningWhatsApp;
use App\Models\PirtExpiryNotificationLog;
use App\Models\Produk;
use App\Models\Role;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\PhoneNumberNormalizer;
use App\Services\PirtExpiryNotificationService;
use App\Services\StarSenderClient;
use App\Support\SystemSettings;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PirtExpiryNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_product_image_forms_show_square_photo_recommendation_without_dimension_validation(): void
    {
        $this->withoutVite();

        $admin = $this->createUser('admin', 'admin@example.test');
        $pelakuUsaha = $this->createUser('user', null, 'Pelaku Usaha Test', '1234567890123');
        $produk = $this->createProduct([
            'user_id' => $pelakuUsaha->id,
            'is_verified' => true,
        ]);

        $recommendation = 'Rekomendasi foto: gunakan rasio 1:1 (persegi), minimal 800×800 px, ideal 1200×1200 px. Format JPG, JPEG, PNG, atau WebP. Maksimal ukuran file 2 MB.';

        $this->actingAs($admin)
            ->get(route('panel.product-images.index'))
            ->assertOk()
            ->assertSee($recommendation, false);

        $this->actingAs($pelakuUsaha)
            ->get(route('user.products.setting.edit', $produk->id))
            ->assertOk()
            ->assertSee($recommendation, false);
    }

    public function test_notification_settings_validate_and_normalize_warning_days(): void
    {
        $this->withoutVite();

        $superAdmin = $this->createUser('super_admin', 'super@example.test');

        $this->actingAs($superAdmin)
            ->from(route('super-admin.settings.index') . '#settings-notifications')
            ->put(route('super-admin.settings.update-group', 'notifications'), [
                'return_anchor' => 'settings-notifications',
                'values' => [
                    'pirt_expiry_notification_enabled' => '1',
                    'pirt_expiry_warning_days' => '30,abc',
                    'pirt_expiry_notification_time' => '08:00',
                    'pirt_expiry_message_template' => 'Pesan {nama_produk}',
                ],
            ])
            ->assertRedirect(route('super-admin.settings.index') . '#settings-notifications')
            ->assertSessionHasErrors('values.pirt_expiry_warning_days');

        $this->actingAs($superAdmin)
            ->put(route('super-admin.settings.update-group', 'notifications'), [
                'return_anchor' => 'settings-notifications',
                'values' => [
                    'pirt_expiry_notification_enabled' => '1',
                    'pirt_expiry_warning_days' => '7,30,14,30',
                    'pirt_expiry_notification_time' => '09:15',
                    'pirt_expiry_message_template' => 'Pesan {nama_produk}',
                ],
            ])
            ->assertRedirect(route('super-admin.settings.index') . '#settings-notifications')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('system_settings', [
            'key' => 'pirt_expiry_warning_days',
            'value' => '30,14,7',
        ]);
        $this->assertDatabaseHas('system_settings', [
            'key' => 'pirt_expiry_notification_time',
            'value' => '09:15',
        ]);
    }

    public function test_phone_normalizer_handles_common_indonesian_mobile_formats(): void
    {
        $normalizer = new PhoneNumberNormalizer();

        $this->assertSame('6281234567890', $normalizer->normalize('0812-3456-7890'));
        $this->assertSame('6281234567890', $normalizer->normalize('+62 812 3456 7890'));
        $this->assertSame('6281234567890', $normalizer->normalize('81234567890'));
        $this->assertNull($normalizer->normalize('0271-123456'));
        $this->assertNull($normalizer->normalize(null));
    }

    public function test_preview_finds_candidates_skips_invalid_phone_and_applies_duplicate_guard(): void
    {
        Carbon::setTestNow('2026-06-02 08:00:00');

        $candidate = $this->createProduct([
            'no_sppirt' => 'PIRT-CANDIDATE',
            'no_hp' => '081234567890',
            'masa_berlaku_pirt' => now()->addDays(30)->toDateString(),
            'is_verified' => true,
        ]);
        $this->createProduct([
            'no_sppirt' => 'PIRT-INVALID-PHONE',
            'no_hp' => '0271-123456',
            'masa_berlaku_pirt' => now()->addDays(30)->toDateString(),
            'is_verified' => true,
        ]);
        $this->createProduct([
            'no_sppirt' => 'PIRT-WRONG-DAY',
            'no_hp' => '081234567891',
            'masa_berlaku_pirt' => now()->addDays(14)->toDateString(),
            'is_verified' => true,
        ]);

        $service = app(PirtExpiryNotificationService::class);
        $plan = $service->preview([30]);

        $this->assertSame(1, $plan['totals']['candidates']);
        $this->assertSame(1, $plan['totals']['invalid_phone']);
        $this->assertSame('6281234567890', $plan['candidates'][0]['recipient_phone']);

        PirtExpiryNotificationLog::create([
            'produk_id' => $candidate->id,
            'masa_berlaku_pirt' => $candidate->masa_berlaku_pirt,
            'warning_days' => 30,
            'notification_type' => PirtExpiryNotificationService::NOTIFICATION_TYPE,
            'recipient_phone' => '6281234567890',
            'status' => PirtExpiryNotificationLog::STATUS_SENT,
            'message_body' => 'Sudah dikirim.',
            'sent_at' => now(),
        ]);

        $guardedPlan = $service->preview([30]);

        $this->assertSame(0, $guardedPlan['totals']['candidates']);
        $this->assertSame(1, $guardedPlan['totals']['already_logged']);
        $this->assertSame(PirtExpiryNotificationLog::STATUS_SENT, $guardedPlan['already_logged'][0]['status']);
    }

    public function test_job_sends_text_message_with_http_fake_and_marks_log_sent(): void
    {
        Carbon::setTestNow('2026-06-02 08:00:00');
        config()->set('services.starsender.api_key', 'device-key-test');
        config()->set('services.starsender.endpoint', 'https://api.starsender.online/api/send');

        Http::fake([
            'https://api.starsender.online/api/send' => Http::response([
                'success' => true,
                'message' => 'Success sent message',
            ], 200),
        ]);

        $produk = $this->createProduct([
            'no_sppirt' => 'PIRT-JOB',
            'masa_berlaku_pirt' => now()->addDays(30)->toDateString(),
            'is_verified' => true,
        ]);
        $log = PirtExpiryNotificationLog::create([
            'produk_id' => $produk->id,
            'masa_berlaku_pirt' => $produk->masa_berlaku_pirt,
            'warning_days' => 30,
            'notification_type' => PirtExpiryNotificationService::NOTIFICATION_TYPE,
            'recipient_phone' => '6281234567890',
            'status' => PirtExpiryNotificationLog::STATUS_PENDING,
            'message_body' => 'Pesan uji',
        ]);

        (new SendPirtExpiryWarningWhatsApp($log->id))->handle(app(StarSenderClient::class));

        $log->refresh();

        $this->assertSame(PirtExpiryNotificationLog::STATUS_SENT, $log->status);
        $this->assertNotNull($log->sent_at);

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://api.starsender.online/api/send'
                && $request->hasHeader('Authorization', 'device-key-test')
                && $request['messageType'] === 'text'
                && $request['to'] === '6281234567890'
                && $request['body'] === 'Pesan uji';
        });
    }

    public function test_dry_run_does_not_create_logs_dispatch_jobs_or_call_starsender(): void
    {
        Carbon::setTestNow('2026-06-02 08:00:00');
        Bus::fake();
        Http::fake();
        $this->enablePirtExpiryNotifications('30');

        $this->createProduct([
            'no_sppirt' => 'PIRT-DRY-RUN',
            'no_hp' => '081234567890',
            'masa_berlaku_pirt' => now()->addDays(30)->toDateString(),
            'is_verified' => true,
        ]);

        $this->artisan('pirt:send-expiry-notifications --dry-run')
            ->expectsOutput('DRY-RUN Notifikasi Masa Berlaku PIRT')
            ->assertExitCode(0);

        Bus::assertNotDispatched(SendPirtExpiryWarningWhatsApp::class);
        Http::assertNothingSent();
        $this->assertDatabaseCount('pirt_expiry_notification_logs', 0);
    }

    public function test_missing_starsender_api_key_returns_controlled_command_failure_without_pending_log(): void
    {
        Carbon::setTestNow('2026-06-02 08:00:00');
        Bus::fake();
        config()->set('services.starsender.api_key', null);
        $this->enablePirtExpiryNotifications('30');

        $this->createProduct([
            'no_sppirt' => 'PIRT-NO-KEY',
            'no_hp' => '081234567890',
            'masa_berlaku_pirt' => now()->addDays(30)->toDateString(),
            'is_verified' => true,
        ]);

        $this->artisan('pirt:send-expiry-notifications')
            ->expectsOutput('API key StarSender belum diisi. Isi STARSENDER_DEVICE_API_KEY di .env sebelum menjalankan pengiriman asli.')
            ->assertExitCode(1);

        Bus::assertNotDispatched(SendPirtExpiryWarningWhatsApp::class);
        $this->assertDatabaseCount('pirt_expiry_notification_logs', 0);
    }

    private function enablePirtExpiryNotifications(string $warningDays): void
    {
        foreach ([
            'pirt_expiry_notification_enabled' => '1',
            'pirt_expiry_warning_days' => $warningDays,
            'pirt_expiry_notification_time' => '08:00',
            'pirt_expiry_message_template' => 'Pesan {nama_produk}',
        ] as $key => $value) {
            SystemSetting::updateOrCreate(['key' => $key], [
                'value' => $value,
                'deskripsi' => 'Test setting',
            ]);
        }

        SystemSettings::forget();
    }

    private function createUser(string $roleName, ?string $email, string $name = 'User Test', ?string $nib = null): User
    {
        $role = Role::firstOrCreate(
            ['nama_role' => $roleName],
            ['deskripsi' => "{$roleName} test"]
        );

        return User::create([
            'nama' => $name,
            'email' => $email,
            'nib' => $nib,
            'password' => 'password',
            'role_id' => $role->id,
            'status_akun' => 'aktif',
        ]);
    }

    private function createProduct(array $overrides = []): Produk
    {
        static $counter = 1;

        return Produk::create([
            'no_sppirt' => $overrides['no_sppirt'] ?? 'PIRT-TEST-' . $counter++,
            'nama_branding' => $overrides['nama_branding'] ?? 'Produk Test',
            'nama_pelaku_usaha' => $overrides['nama_pelaku_usaha'] ?? 'Pelaku Usaha Test',
            'alamat' => $overrides['alamat'] ?? 'Alamat test',
            'nib' => $overrides['nib'] ?? '1234567890123',
            'no_hp' => $overrides['no_hp'] ?? '081234567890',
            'masa_berlaku_pirt' => $overrides['masa_berlaku_pirt'] ?? now()->addDays(30)->toDateString(),
            'is_verified' => $overrides['is_verified'] ?? true,
            'user_id' => $overrides['user_id'] ?? null,
        ]);
    }
}
