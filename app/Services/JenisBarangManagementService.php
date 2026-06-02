<?php

namespace App\Services;

use App\Models\JenisBarang;
use App\Models\JenisBarangAlias;
use App\Support\ProductTypeClassifier;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class JenisBarangManagementService
{
    public function __construct(private ProductTypeClassifier $classifier) {}

    public function create(array $data): JenisBarang
    {
        $jenisBarang = DB::transaction(function () use ($data) {
            $jenisBarang = JenisBarang::create($this->categoryData($data));
            $this->syncAliases($jenisBarang, $data['aliases'] ?? '');

            return $jenisBarang->fresh('aliases');
        });

        $this->forgetCatalogCache();

        return $jenisBarang;
    }

    public function update(JenisBarang $jenisBarang, array $data): JenisBarang
    {
        $updated = DB::transaction(function () use ($jenisBarang, $data) {
            $jenisBarang->update($this->categoryData($data));
            $this->syncAliases($jenisBarang, $data['aliases'] ?? '');

            return $jenisBarang->fresh('aliases');
        });

        $this->forgetCatalogCache();

        return $updated;
    }

    private function categoryData(array $data): array
    {
        $name = trim((string) $data['nama_jenis']);
        $slug = trim((string) ($data['slug'] ?? ''));

        return [
            'nama_jenis' => $name,
            'slug' => $slug !== '' ? Str::slug($slug) : Str::slug($name),
            'deskripsi' => $data['deskripsi'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];
    }

    private function syncAliases(JenisBarang $jenisBarang, ?string $rawAliases): void
    {
        $keywords = collect(preg_split('/\r\n|\r|\n|;/', (string) $rawAliases) ?: [])
            ->map(fn (string $keyword) => $this->classifier->normalizeKeyword($keyword))
            ->filter()
            ->unique()
            ->values();

        if ($keywords->isEmpty()) {
            $jenisBarang->aliases()->delete();

            return;
        }

        $jenisBarang->aliases()
            ->whereNotIn('keyword', $keywords->all())
            ->delete();

        $conflict = JenisBarangAlias::query()
            ->whereIn('keyword', $keywords->all())
            ->where('jenis_barang_id', '!=', $jenisBarang->id)
            ->orderBy('keyword')
            ->first();

        if ($conflict) {
            throw ValidationException::withMessages([
                'aliases' => "Alias '{$conflict->keyword}' sudah digunakan oleh jenis barang lain.",
            ]);
        }

        foreach ($keywords as $index => $keyword) {
            $jenisBarang->aliases()->updateOrCreate(
                ['keyword' => $keyword],
                [
                    'priority' => $index + 1,
                    'is_active' => true,
                ]
            );
        }
    }

    public function forgetCatalogCache(): void
    {
        Cache::forget('jenis_barangs_all');
    }
}
