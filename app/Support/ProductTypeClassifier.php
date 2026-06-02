<?php

namespace App\Support;

use App\Models\JenisBarang;
use App\Models\JenisBarangAlias;
use App\Models\Produk;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProductTypeClassifier
{
    public const FALLBACK_CATEGORY = 'Lainnya / Perlu Review';

    private ?Collection $activeAliases = null;

    private ?Collection $activeJenisBarangs = null;

    public function resolve(?string $kategoriPangan, ?string $jenisPangan): ?JenisBarang
    {
        $needle = $this->normalize((string) $jenisPangan);

        if ($needle === '') {
            return null;
        }

        $fromMaster = $this->resolveFromOfficialMaster($needle);

        if ($fromMaster) {
            return $fromMaster;
        }

        $fromAlias = $this->resolveFromDatabaseAliases($needle);

        if ($fromAlias) {
            return $fromAlias;
        }

        return $this->fallbackCategory();
    }

    public function seedDefaults(): void
    {
        $this->seedOfficialMaster();
        $this->seedFallbackCategory();
        $this->deactivateLegacyCategories();

        $this->activeJenisBarangs = null;
        $this->activeAliases = null;
    }

    public function fallbackCategory(): JenisBarang
    {
        return $this->seedFallbackCategory();
    }

    public function normalizeKeyword(string $value): string
    {
        return $this->normalize($value);
    }

    public function reclassifyExistingProducts(bool $dryRun = false): array
    {
        $checked = 0;
        $updated = 0;
        $fallback = 0;

        Produk::query()
            ->select(['id', 'kategori_pangan', 'jenis_pangan', 'jenis_barang_id'])
            ->orderBy('id')
            ->chunkById(100, function ($produks) use (&$checked, &$updated, &$fallback, $dryRun) {
                foreach ($produks as $produk) {
                    if (blank($produk->kategori_pangan) && blank($produk->jenis_pangan)) {
                        continue;
                    }

                    $checked++;
                    $jenisBarang = $this->resolve($produk->kategori_pangan, $produk->jenis_pangan);

                    if (! $jenisBarang) {
                        continue;
                    }

                    if ($jenisBarang->nama_jenis === self::FALLBACK_CATEGORY) {
                        $fallback++;
                    }

                    if ((int) $produk->jenis_barang_id !== (int) $jenisBarang->id) {
                        if (! $dryRun) {
                            $produk->forceFill(['jenis_barang_id' => $jenisBarang->id])->save();
                        }

                        $updated++;
                    }
                }
            });

        return [
            'checked' => $checked,
            'updated' => $updated,
            'fallback' => $fallback,
        ];
    }

    private function resolveFromOfficialMaster(string $needle): ?JenisBarang
    {
        $jenisBarangs = $this->activeJenisBarangs ??= JenisBarang::query()
            ->active()
            ->orderBy('nama_jenis')
            ->get();

        foreach ($jenisBarangs as $jenisBarang) {
            if ($this->normalize($jenisBarang->nama_jenis) === $needle) {
                return $jenisBarang;
            }
        }

        return null;
    }

    private function resolveFromDatabaseAliases(string $needle): ?JenisBarang
    {
        if (! Schema::hasTable('jenis_barang_aliases')) {
            return null;
        }

        $aliases = $this->activeAliases ??= JenisBarangAlias::query()
            ->with('jenisBarang')
            ->where('is_active', true)
            ->whereHas('jenisBarang', fn ($query) => $query->active())
            ->orderBy('priority')
            ->get();

        foreach ($aliases as $alias) {
            if ($alias->keyword && $this->normalize($alias->keyword) === $needle) {
                return $alias->jenisBarang;
            }
        }

        foreach ($aliases as $alias) {
            if ($alias->keyword && str_contains($needle, $this->normalize($alias->keyword))) {
                return $alias->jenisBarang;
            }
        }

        return null;
    }

    private function seedOfficialMaster(): void
    {
        $rows = require database_path('seeders/data/pirt_jenis_pangan.php');

        foreach ($rows as $row) {
            $name = trim((string) $row['nama_jenis']);
            $jenisBarang = JenisBarang::updateOrCreate(
                ['nama_jenis' => $name],
                $this->officialMasterData($row)
            );

            $this->seedExactAlias($jenisBarang, $name);
        }
    }

    private function seedFallbackCategory(): JenisBarang
    {
        return JenisBarang::updateOrCreate(
            ['nama_jenis' => self::FALLBACK_CATEGORY],
            $this->columnSafeData([
                'slug' => Str::slug(self::FALLBACK_CATEGORY),
                'deskripsi' => 'Jenis pangan dari Rekap PIRT belum cocok dengan master resmi dan perlu direview admin.',
                'keterangan' => 'Kategori khusus untuk review data import.',
                'status_pirt' => 'PERLU REVIEW',
                'is_active' => false,
            ])
        );
    }

    private function seedExactAlias(JenisBarang $jenisBarang, string $name): void
    {
        if (! Schema::hasTable('jenis_barang_aliases')) {
            return;
        }

        $keyword = $this->normalize($name);

        if ($keyword === '') {
            return;
        }

        JenisBarangAlias::updateOrCreate(
            ['keyword' => $keyword],
            [
                'jenis_barang_id' => $jenisBarang->id,
                'priority' => 1,
                'is_active' => true,
            ]
        );
    }

    private function deactivateLegacyCategories(): void
    {
        JenisBarang::query()
            ->whereIn('nama_jenis', [
                'Makanan Ringan',
                'Roti & Kue',
                'Minuman',
                'Bumbu & Sambal',
                'Olahan Hewani',
                'Olahan Buah & Sayur',
                'Olahan Kacang, Biji & Umbi',
                'Gula, Madu & Pemanis',
                'Makanan Siap Saji',
            ])
            ->update(['is_active' => false]);
    }

    private function officialMasterData(array $row): array
    {
        $name = trim((string) $row['nama_jenis']);
        $slug = Str::slug($name);

        return $this->columnSafeData([
            'slug' => $slug,
            'nomor_kategori' => $row['nomor_kategori'] ?? null,
            'kategori_resmi' => $row['kategori_resmi'] ?? null,
            'deskripsi' => $row['deskripsi'] ?? null,
            'keterangan' => $row['keterangan'] ?? null,
            'status_pirt' => $row['status_pirt'] ?? null,
            'dasar_hukum' => $row['dasar_hukum'] ?? null,
            'is_active' => true,
        ]);
    }

    private function columnSafeData(array $data): array
    {
        return collect($data)
            ->filter(fn ($value, string $column) => Schema::hasColumn('jenis_barangs', $column))
            ->all();
    }

    private function normalize(string $value): string
    {
        $value = preg_replace('/^\s*\d+\s*[\.)-]\s*/u', '', trim($value)) ?: $value;
        $value = Str::lower(Str::ascii($value));
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value) ?: $value;
        $value = preg_replace('/\s+/', ' ', $value) ?: $value;

        return trim($value);
    }
}
