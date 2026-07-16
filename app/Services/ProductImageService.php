<?php

namespace App\Services;

use App\Models\GambarProduk;
use App\Models\Produk;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProductImageService
{
    public const VERIFIED_ONLY_MESSAGE = 'Gambar produk hanya dapat diubah setelah produk terverifikasi.';

    public function replaceOne(Produk $produk, UploadedFile $file): GambarProduk
    {
        $this->ensureVerified($produk);

        // $newPath = $file->store("produk/{$produk->id}", 'public');
        $newPath = $file->store("produk/{$produk->id}", 's3');
        $oldPaths = [];

        try {
            $gambar = DB::transaction(function () use ($produk, $newPath, &$oldPaths) {
                $oldImages = $produk->gambarProduks()
                    ->select(['id', 'url_gambar'])
                    ->get();

                $oldPaths = $oldImages->pluck('url_gambar')->filter()->all();

                if ($oldImages->isNotEmpty()) {
                    $produk->gambarProduks()->delete();
                }

                return GambarProduk::create([
                    'produk_id' => $produk->id,
                    'url_gambar' => $newPath,
                    'is_primary' => true,
                    'uploaded_at' => now(),
                ]);
            });
        } catch (Throwable $e) {
            // Storage::disk('public')->delete($newPath);
            Storage::disk('s3')->delete($newPath);

            throw $e;
        }

        foreach (array_unique($oldPaths) as $oldPath) {
            // Storage::disk('public')->delete($oldPath);
            Storage::disk('s3')->delete($oldPath);
        }

        return $gambar->fresh() ?? $gambar;
    }

    public function delete(GambarProduk $gambarProduk): void
    {
        $gambarProduk->loadMissing('produk');
        $this->ensureVerified($gambarProduk->produk);

        DB::transaction(function () use ($gambarProduk) {
            $path = $gambarProduk->url_gambar;
            $gambarProduk->delete();
            // Storage::disk('public')->delete($path);
            Storage::disk('s3')->delete($path);
        });
    }

    private function ensureVerified(?Produk $produk): void
    {
        if (! $produk?->is_verified) {
            throw ValidationException::withMessages([
                'gambar' => self::VERIFIED_ONLY_MESSAGE,
            ]);
        }
    }
}
