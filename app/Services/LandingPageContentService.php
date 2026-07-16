<?php

namespace App\Services;

use App\Models\LandingPageContent;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class LandingPageContentService
{
    public const MANAGED_SECTIONS = [
        'hero' => [
            'label' => 'Banner Utama',
            'description' => 'Bagian paling atas halaman depan yang pertama kali dilihat pengunjung.',
            'allows_image' => true,
            'allows_secondary_button' => true,
            'defaults' => [
                'judul' => 'SIPAMAN',
                'subjudul' => 'Sistem Informasi Pangan Aman',
                'konten' => 'Temukan produk PIRT, pelaku usaha, dan potensi UMKM pangan aman dari Karanganyar dalam satu katalog yang mudah dicari.',
                'image_alt' => 'Produk pangan aman Karanganyar',
                'button_text' => 'Lihat Produk',
                'button_url' => '/products',
                'secondary_button_text' => 'Lihat UMKM',
                'secondary_button_url' => '/umkm',
            ],
        ],
        'featured_products' => [
            'label' => 'Bagian Produk Terverifikasi',
            'description' => 'Bagian ini tampil di halaman depan website untuk mengarahkan pengunjung melihat produk PIRT yang sudah terverifikasi.',
            'allows_image' => false,
            'allows_secondary_button' => false,
            'defaults' => [
                'judul' => 'Produk Pangan Terverifikasi',
                'subjudul' => 'Direktori',
                'konten' => 'Produk lokal Karanganyar yang sudah terverifikasi dan siap dikenalkan ke publik.',
                'button_text' => 'Lihat Semua Produk',
                'button_url' => '/products',
            ],
        ],
        'region_potential' => [
            'label' => 'Bagian Potensi Wilayah',
            'description' => 'Bagian ini menjelaskan potensi wilayah dan sebaran produk/pelaku usaha di Karanganyar.',
            'allows_image' => false,
            'allows_secondary_button' => false,
            'defaults' => [
                'judul' => 'Potensi Lokal Tiap Kecamatan',
                'subjudul' => 'Sebaran Wilayah',
                'konten' => 'SIPAMAN membantu masyarakat melihat produk PIRT, pelaku usaha, dan persebaran potensi pangan aman dari wilayah Karanganyar.',
                'button_text' => 'Jelajahi UMKM',
                'button_url' => '/umkm',
            ],
        ],
    ];

    public function managedSections(bool $createMissing = true): Collection
    {
        if ($createMissing) {
            $this->ensureManagedSections();
        }

        $contents = LandingPageContent::query()
            ->with('updatedBy')
            ->whereIn('section_key', $this->managedSectionKeys())
            ->get()
            ->keyBy('section_key');

        return collect($this->managedSectionKeys())
            ->map(fn (string $key) => $contents->get($key))
            ->filter()
            ->values();
    }

    public function managedSectionsForPublic(): Collection
    {
        $contents = LandingPageContent::query()
            ->active()
            ->whereIn('section_key', $this->managedSectionKeys())
            ->get()
            ->keyBy('section_key');

        return collect($this->managedSectionKeys())
            ->map(fn (string $key) => $contents->get($key))
            ->filter()
            ->keyBy('section_key');
    }

    public function sectionMeta(LandingPageContent|string $content): ?array
    {
        $sectionKey = $content instanceof LandingPageContent ? $content->section_key : $content;

        return self::MANAGED_SECTIONS[$sectionKey] ?? null;
    }

    public function managedSectionKeys(): array
    {
        return array_keys(self::MANAGED_SECTIONS);
    }

    public function assertManagedSection(LandingPageContent $content): void
    {
        abort_unless($this->sectionMeta($content), 404);
    }

    public function update(LandingPageContent $content, array $data, ?int $userId): LandingPageContent
    {
        $this->assertManagedSection($content);

        if (! ($this->sectionMeta($content)['allows_image'] ?? false)) {
            unset($data['image'], $data['remove_image'], $data['image_path'], $data['image_alt']);
        }

        if (! ($this->sectionMeta($content)['allows_secondary_button'] ?? false)) {
            unset($data['secondary_button_text'], $data['secondary_button_url']);
        }

        $oldImagePath = $content->image_path;
        $newImagePath = null;

        if (($data['image'] ?? null) instanceof UploadedFile) {
            // $newImagePath = $data['image']->store('landing-page', 'public');
            $newImagePath = $data['image']->store('landing-page', 's3');
            $data['image_path'] = $newImagePath;
        } elseif (! empty($data['remove_image'])) {
            $data['image_path'] = null;
        }

        unset($data['image'], $data['remove_image']);

        $content->fill($data);
        $content->updated_by = $userId;
        $content->save();

        if ($oldImagePath && ($newImagePath || array_key_exists('image_path', $data))) {
            // Storage::disk('public')->delete($oldImagePath);
            Storage::disk('s3')->delete($oldImagePath);
        }

        return $content->fresh('updatedBy');
    }

    private function ensureManagedSections(): void
    {
        foreach (self::MANAGED_SECTIONS as $sectionKey => $meta) {
            LandingPageContent::firstOrCreate(
                ['section_key' => $sectionKey],
                [
                    ...($meta['defaults'] ?? []),
                    'is_active' => true,
                ]
            );
        }
    }
}
