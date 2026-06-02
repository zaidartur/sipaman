<?php

namespace App\Support;

use App\Models\Kecamatan;
use App\Models\Produk;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class KecamatanResolver
{
    private ?Collection $kecamatans = null;

    public function resolve(?string $wilayah, ?string $alamat): ?Kecamatan
    {
        $text = $this->normalize(trim(($wilayah ?? '').' '.($alamat ?? '')));

        if ($text === '') {
            return null;
        }

        $withMarker = $this->resolveWithDistrictMarker($text);

        if ($withMarker) {
            return $withMarker;
        }

        return $this->resolveStandaloneName($text);
    }

    public function backfillExistingProducts(bool $dryRun = false): array
    {
        $checked = 0;
        $updated = 0;
        $unmatched = 0;

        Produk::query()
            ->whereNull('kecamatan_id')
            ->select(['id', 'wilayah', 'alamat', 'kecamatan_id'])
            ->orderBy('id')
            ->chunkById(100, function ($produks) use (&$checked, &$updated, &$unmatched, $dryRun): void {
                foreach ($produks as $produk) {
                    $checked++;

                    $kecamatan = $this->resolve($produk->wilayah, $produk->alamat);

                    if (! $kecamatan) {
                        $unmatched++;

                        continue;
                    }

                    if (! $dryRun) {
                        $produk->forceFill(['kecamatan_id' => $kecamatan->id])->save();
                    }

                    $updated++;
                }
            });

        return [
            'checked' => $checked,
            'updated' => $updated,
            'unmatched' => $unmatched,
        ];
    }

    private function resolveWithDistrictMarker(string $text): ?Kecamatan
    {
        foreach ($this->kecamatans() as $kecamatan) {
            $name = $this->normalize($kecamatan->nama_kecamatan);
            $compactName = str_replace(' ', '', $name);
            $compactText = str_replace(' ', '', $text);

            if (
                str_contains($text, "kecamatan {$name}")
                || str_contains($text, "kec {$name}")
                || str_contains($compactText, "kec{$compactName}")
            ) {
                return $kecamatan;
            }
        }

        return null;
    }

    private function resolveStandaloneName(string $text): ?Kecamatan
    {
        $matches = collect();

        foreach ($this->kecamatans() as $kecamatan) {
            $name = $this->normalize($kecamatan->nama_kecamatan);

            if ($name === 'karanganyar') {
                continue;
            }

            if (preg_match('/(^|\s)'.preg_quote($name, '/').'(\s|$)/', $text) === 1) {
                $matches->push($kecamatan);
            }
        }

        return $matches->count() === 1 ? $matches->first() : null;
    }

    private function kecamatans(): Collection
    {
        return $this->kecamatans ??= Kecamatan::query()
            ->orderByRaw('LENGTH(nama_kecamatan) DESC')
            ->orderBy('nama_kecamatan')
            ->get();
    }

    private function normalize(string $value): string
    {
        $value = Str::lower(Str::ascii($value));
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value) ?: $value;
        $value = preg_replace('/\s+/', ' ', $value) ?: $value;

        return trim($value);
    }
}
