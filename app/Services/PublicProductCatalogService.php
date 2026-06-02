<?php

namespace App\Services;

use App\Models\Produk;
use App\Support\SystemSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class PublicProductCatalogService
{
    public function query(array $filters = []): Builder
    {
        return Produk::with(['kecamatan', 'jenisBarang', 'gambarUtama'])
            ->verified()
            ->search($this->stringFilter($filters, 'search'))
            ->byKecamatan($filters['kecamatan_id'] ?? null)
            ->byJenisBarang($filters['jenis_barang_id'] ?? null)
            ->when($this->stringFilter($filters, 'kecamatan'), function (Builder $query, string $keyword): void {
                $query->where(function (Builder $query) use ($keyword): void {
                    $query->where('wilayah', 'like', "%{$keyword}%")
                        ->orWhereHas('kecamatan', fn (Builder $kecamatanQuery) => $kecamatanQuery
                            ->where('nama_kecamatan', 'like', "%{$keyword}%"));
                });
            })
            ->orderBy('nama_branding');
    }

    public function paginate(array $filters = [], mixed $perPage = null): LengthAwarePaginator
    {
        return $this->query($filters)
            ->paginate(SystemSettings::pagination($perPage));
    }

    private function stringFilter(array $filters, string $key): ?string
    {
        $value = $filters[$key] ?? null;

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
