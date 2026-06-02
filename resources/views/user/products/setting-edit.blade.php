@extends('layouts.public')
@section('title', 'Edit Produk - ' . $produk->nama_branding)
@section('content')
<section class="mx-auto max-w-container px-4 pt-10 md:px-6">
    <div class="relative overflow-hidden rounded-3xl border border-outline-variant bg-gradient-to-br from-primary-soft via-white to-secondary-soft p-8 md:p-10">
        <div class="absolute -right-10 -top-10 h-48 w-48 rounded-full bg-accent/25 blur-3xl"></div>
        <div class="relative">
            <x-badge-status status="info">Edit Terbatas Produk</x-badge-status>
            <h1 class="font-display mt-4 text-3xl font-700 text-ink md:text-4xl">{{ $produk->nama_branding }}</h1>
            <p class="mt-3 text-on-surface-variant">No. SPPIRT: {{ $produk->no_sppirt }}</p>
        </div>
    </div>
</section>

<section class="mx-auto max-w-container px-4 py-8 md:px-6">
    <div class="mb-6">
        <a href="{{ route('user.products.setting.index') }}" class="inline-flex items-center gap-1.5 text-sm font-600 text-on-surface-variant transition-colors hover:text-primary">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Kembali ke Daftar
        </a>
    </div>

    @if (session('success'))
        <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
    @endif

    @if ($errors->any())
        <x-alert type="danger" class="mb-6">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </x-alert>
    @endif

    <div class="grid gap-6 lg:grid-cols-[1fr_1.2fr]">
        <div class="overflow-hidden rounded-3xl border border-outline-variant bg-white shadow-soft">
            <div class="flex items-center gap-2 border-b border-outline-variant px-6 py-5">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary-soft text-primary">
                    <span class="material-symbols-outlined text-[20px]">verified</span>
                </span>
                <h2 class="font-display text-xl font-700 text-ink">Data Resmi PIRT</h2>
            </div>
            <dl class="grid gap-4 p-6 text-sm">
                @foreach ([
                    'Nama Branding' => $produk->nama_branding,
                    'Nama Pelaku Usaha' => $produk->nama_pelaku_usaha,
                    'NIB' => $produk->nib ?: '-',
                    'Jenis Pangan' => $produk->jenis_pangan ?: '-',
                    'Kategori Pangan' => $produk->kategori_pangan ?: '-',
                    'Nama Toko' => $produk->nama_toko ?: '-',
                    'Status Verifikasi' => $produk->is_verified ? 'Terverifikasi' : 'Belum terverifikasi',
                    'Masa Berlaku PIRT' => $produk->masa_berlaku_pirt?->format('d/m/Y') ?? '-',
                ] as $label => $value)
                    <div class="rounded-xl bg-surface-container-low p-3">
                        <dt class="text-xs font-700 uppercase text-on-surface-variant">{{ $label }}</dt>
                        <dd class="mt-1 font-600 text-on-surface">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        <div class="space-y-6">
            <div class="overflow-hidden rounded-3xl border border-outline-variant bg-white shadow-soft">
                <div class="flex items-center gap-2 border-b border-outline-variant px-6 py-5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary-soft text-primary">
                        <span class="material-symbols-outlined text-[20px]">sell</span>
                    </span>
                    <h2 class="font-display text-xl font-700 text-ink">Informasi Tampilan</h2>
                </div>

                <form method="POST" action="{{ route('user.products.setting.update', $produk->id) }}" class="space-y-4 p-6">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="harga" class="mb-1.5 block text-sm font-600 text-on-surface">Harga (Rp)</label>
                        <input type="number" id="harga" name="harga" min="0" step="1" value="{{ old('harga', $produk->harga) }}" class="form-input-sipaman w-full @error('harga') border-red-400 @enderror" placeholder="Kosongkan jika belum ada harga">
                        @error('harga')<p class="mt-1.5 text-xs font-600 text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="deskripsi" class="mb-1.5 block text-sm font-600 text-on-surface">Deskripsi Tampilan</label>
                        <textarea id="deskripsi" name="deskripsi" rows="5" class="form-textarea-sipaman w-full @error('deskripsi') border-red-400 @enderror" placeholder="Deskripsi singkat untuk katalog publik">{{ old('deskripsi', $produk->deskripsi) }}</textarea>
                        @error('deskripsi')<p class="mt-1.5 text-xs font-600 text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-5 py-2.5 text-sm font-600 text-white transition-colors hover:bg-primary-container">
                        <span class="material-symbols-outlined text-[18px]">save</span>
                        Simpan Informasi
                    </button>
                </form>
            </div>

            <div class="overflow-hidden rounded-3xl border border-outline-variant bg-white shadow-soft">
                <div class="flex items-center gap-2 border-b border-outline-variant px-6 py-5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-secondary-soft text-secondary">
                        <span class="material-symbols-outlined text-[20px]">image</span>
                    </span>
                    <h2 class="font-display text-xl font-700 text-ink">Gambar Produk</h2>
                </div>

                <div class="space-y-5 p-6">
                    @if ($produk->gambarUtama)
                        <img src="{{ $produk->gambarUtama->gambar_url }}" alt="{{ $produk->nama_branding }}" class="aspect-square w-full rounded-2xl border border-outline-variant object-cover">
                    @else
                        <div class="flex aspect-square w-full items-center justify-center rounded-2xl border border-dashed border-outline-variant bg-surface-container-low text-center text-sm text-on-surface-variant">
                            Belum ada gambar produk.
                        </div>
                    @endif

                    @if ($produk->is_verified)
                        <form method="POST" action="{{ route('user.products.setting.upload-gambar', $produk->id) }}" enctype="multipart/form-data" class="space-y-3">
                            @csrf
                            <label for="gambar" class="block text-sm font-600 text-on-surface">Ganti Gambar</label>
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                <input type="file" id="gambar" name="gambar" accept="image/jpeg,image/png,image/jpg,image/webp" class="form-file-sipaman">
                                <button type="submit" class="inline-flex shrink-0 items-center justify-center gap-1.5 rounded-xl bg-primary px-5 py-2.5 text-sm font-600 text-white transition-colors hover:bg-primary-container">
                                    <span class="material-symbols-outlined text-[18px]">upload</span>
                                    Ganti Gambar
                                </button>
                            </div>
                            <p class="text-xs leading-5 text-on-surface-variant">Rekomendasi foto: gunakan rasio 1:1 (persegi), minimal 800×800 px, ideal 1200×1200 px. Format JPG, JPEG, PNG, atau WebP. Maksimal ukuran file 2 MB. Upload baru akan mengganti gambar lama.</p>
                        </form>
                    @else
                        <x-alert type="warning">Belum terverifikasi — gambar belum dapat diubah.</x-alert>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
