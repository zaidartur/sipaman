<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Models\JenisBarang;
use App\Models\JenisBarangAlias;
use App\Support\ProductTypeClassifier;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

trait ValidatesJenisBarangFields
{
    protected function validateJenisBarangFields(Validator $validator, ?int $ignoreJenisBarangId = null): void
    {
        $validator->after(function (Validator $validator) use ($ignoreJenisBarangId): void {
            $this->validateSlugAvailability($validator, $ignoreJenisBarangId);
            $this->validateAliasAvailability($validator, $ignoreJenisBarangId);
        });
    }

    private function validateSlugAvailability(Validator $validator, ?int $ignoreJenisBarangId): void
    {
        if ($validator->errors()->has('nama_jenis') || $validator->errors()->has('slug')) {
            return;
        }

        $source = trim((string) $this->input('slug')) !== ''
            ? (string) $this->input('slug')
            : (string) $this->input('nama_jenis');

        $slug = Str::slug($source);

        if ($slug === '') {
            return;
        }

        $exists = JenisBarang::query()
            ->where('slug', $slug)
            ->when(
                $ignoreJenisBarangId !== null,
                fn ($query) => $query->whereKeyNot($ignoreJenisBarangId)
            )
            ->exists();

        if ($exists) {
            $validator->errors()->add('slug', 'Slug jenis barang sudah digunakan.');
        }
    }

    private function validateAliasAvailability(Validator $validator, ?int $ignoreJenisBarangId): void
    {
        if ($validator->errors()->has('aliases')) {
            return;
        }

        $keywords = $this->normalizedAliasKeywords();

        if ($keywords === []) {
            return;
        }

        $duplicates = array_unique(array_diff_assoc($keywords, array_unique($keywords)));

        if ($duplicates !== []) {
            $alias = array_values($duplicates)[0];
            $validator->errors()->add('aliases', "Alias '{$alias}' ditulis lebih dari satu kali.");

            return;
        }

        $conflict = JenisBarangAlias::query()
            ->with('jenisBarang')
            ->whereIn('keyword', $keywords)
            ->when(
                $ignoreJenisBarangId !== null,
                fn ($query) => $query->where('jenis_barang_id', '!=', $ignoreJenisBarangId)
            )
            ->orderBy('keyword')
            ->first();

        if ($conflict) {
            $validator->errors()->add(
                'aliases',
                "Alias '{$conflict->keyword}' sudah digunakan oleh jenis barang lain."
            );
        }
    }

    private function normalizedAliasKeywords(): array
    {
        $classifier = app(ProductTypeClassifier::class);
        $rawAliases = preg_split('/\r\n|\r|\n|;/', (string) $this->input('aliases')) ?: [];

        return collect($rawAliases)
            ->map(fn (string $keyword) => $classifier->normalizeKeyword($keyword))
            ->filter()
            ->values()
            ->all();
    }
}
