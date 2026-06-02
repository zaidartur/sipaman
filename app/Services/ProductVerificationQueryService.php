<?php

namespace App\Services;

use App\Models\Produk;
use Illuminate\Database\Eloquent\Builder;

class ProductVerificationQueryService
{
    private const PROCESS_TRACKING_FILTERS = [
        'verifikasi_produk' => ['label' => 'Produk', 'column' => 'verifikasi_produk'],
        'verifikasi_label' => ['label' => 'Label', 'column' => 'verifikasi_label'],
        'pkp' => ['label' => 'PKP', 'column' => 'pkp'],
        'cppob' => ['label' => 'CPPOB', 'column' => 'cppob_pemeriksaan_sarana'],
    ];

    public function resolveTab(array $filters): string
    {
        $tab = (string) ($filters['tab'] ?? 'semua');

        return in_array($tab, ['semua', 'terverifikasi', 'proses', 'belum'], true) ? $tab : 'semua';
    }

    public function resolveTrackingFilters(array $filters, string $tab): array
    {
        if ($tab !== 'proses') {
            return $this->emptyTrackingFilters();
        }

        return collect(self::PROCESS_TRACKING_FILTERS)
            ->mapWithKeys(function (array $config, string $field) use ($filters): array {
                $value = $filters[$field] ?? ($field === 'cppob' ? ($filters['cppob_pemeriksaan_sarana'] ?? null) : null);

                return [$field => in_array($value, ['0', '1'], true) ? $value : null];
            })
            ->all();
    }

    public function query(string $tab, array $trackingFilters): Builder
    {
        $query = Produk::with(['kecamatan', 'verifikasi.verifikator', 'commitmentStatus']);

        $this->applyTabFilter($query, $tab, $trackingFilters);

        return $query;
    }

    public function stats(): array
    {
        return [
            'total' => Produk::count(),
            'terverifikasi' => Produk::where('is_verified', true)->count(),
            'belum' => Produk::where('is_verified', false)->whereDoesntHave('verifikasi')->count(),
            'proses' => Produk::where('is_verified', false)->whereHas('verifikasi')->count(),
        ];
    }

    public function trackingFilterLabels(): array
    {
        return collect(self::PROCESS_TRACKING_FILTERS)
            ->mapWithKeys(fn (array $config, string $field): array => [$field => $config['label']])
            ->all();
    }

    private function emptyTrackingFilters(): array
    {
        return collect(array_keys(self::PROCESS_TRACKING_FILTERS))
            ->mapWithKeys(fn (string $field): array => [$field => null])
            ->all();
    }

    private function applyTabFilter(Builder $query, string $tab, array $trackingFilters): void
    {
        match ($tab) {
            'terverifikasi' => $query->where('is_verified', true),
            'belum' => $query->where('is_verified', false)->whereDoesntHave('verifikasi'),
            'proses' => $query->where('is_verified', false)
                ->whereHas('verifikasi', function (Builder $verifikasiQuery) use ($trackingFilters): void {
                    foreach ($trackingFilters as $field => $value) {
                        if ($value === null) {
                            continue;
                        }

                        $verifikasiQuery->where(self::PROCESS_TRACKING_FILTERS[$field]['column'], $value === '1');
                    }
                }),
            default => null,
        };
    }
}
