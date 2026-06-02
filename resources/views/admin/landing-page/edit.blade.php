@extends('layouts.admin')

@section('title', 'Edit Landing Page')
@section('page-title', 'Edit Landing Page')

@section('content')
    @php
        $buttonOptions = [
            'products' => ['label' => 'Halaman Produk', 'url' => '/products'],
            'umkm' => ['label' => 'Halaman UMKM', 'url' => '/umkm'],
            'home' => ['label' => 'Beranda', 'url' => '/'],
            'none' => ['label' => 'Tidak memakai tombol', 'url' => null],
            'custom' => ['label' => 'Link khusus', 'url' => null],
        ];

        $resolveButtonType = function (?string $url) use ($buttonOptions) {
            foreach (['products', 'umkm', 'home'] as $optionKey) {
                if ($buttonOptions[$optionKey]['url'] === $url) {
                    return $optionKey;
                }
            }

            return $url ? 'custom' : 'none';
        };

        $currentButtonUrl = $landingPage->button_url;
        $matchedButtonType = $resolveButtonType($currentButtonUrl);

        $buttonType = old('button_url_type', $matchedButtonType);
        $customButtonUrl = old('custom_button_url', $buttonType === 'custom' ? $currentButtonUrl : '');
        $allowsSecondaryButton = $sectionMeta['allows_secondary_button'] ?? false;
        $currentSecondaryButtonUrl = $landingPage->secondary_button_url;
        $matchedSecondaryButtonType = $resolveButtonType($currentSecondaryButtonUrl);
        $secondaryButtonType = old('secondary_button_url_type', $matchedSecondaryButtonType);
        $secondaryCustomButtonUrl = old('secondary_custom_button_url', $secondaryButtonType === 'custom' ? $currentSecondaryButtonUrl : '');
    @endphp

    <div class="space-y-5">
        <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
            <div>
                <a href="{{ route('panel.landing-page.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-600 hover:text-slate-900">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                    Kembali ke daftar bagian
                </a>
                <h2 class="font-display mt-2 text-2xl font-bold text-slate-900">{{ $sectionMeta['label'] }}</h2>
                <p class="mt-1 max-w-3xl text-sm leading-6 text-slate-600">{{ $sectionMeta['description'] }}</p>
            </div>
        </div>

        @if ($errors->any())
            <x-alert type="danger">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        <x-alert type="info">
            Admin hanya mengubah isi konten yang aman. Layout, urutan bagian, route teknis, dan struktur tampilan dikunci oleh sistem.
        </x-alert>

        <form action="{{ route('panel.landing-page.update', $landingPage) }}" method="POST" enctype="multipart/form-data" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            @method('PUT')

            <div>
                <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $landingPage->is_active)) class="form-checkbox-sipaman">
                    Tampilkan di website
                </label>

                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Judul yang tampil di website</label>
                        <input name="judul" value="{{ old('judul', $landingPage->judul) }}" class="form-input-sipaman mt-1 w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Teks kecil di bawah judul</label>
                        <input name="subjudul" value="{{ old('subjudul', $landingPage->subjudul) }}" class="form-input-sipaman mt-1 w-full">
                    </div>
                </div>

                <label class="mt-4 block text-sm font-semibold text-slate-700">Deskripsi singkat</label>
                <textarea name="konten" rows="6" class="form-textarea-sipaman mt-1 w-full">{{ old('konten', $landingPage->konten) }}</textarea>

                @if ($allowsSecondaryButton)
                    <div class="mt-4 grid gap-4 lg:grid-cols-2">
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <h3 class="text-sm font-bold text-slate-900">Tombol Utama</h3>
                            <div class="mt-3 grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700">Tulisan tombol utama</label>
                                    <input name="button_text" value="{{ old('button_text', $landingPage->button_text) }}" class="form-input-sipaman mt-1 w-full" placeholder="Contoh: Lihat Produk">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700">Tujuan tombol utama</label>
                                    <select name="button_url_type" data-button-url-select class="form-select-sipaman mt-1 w-full">
                                        @foreach($buttonOptions as $key => $option)
                                            <option value="{{ $key }}" @selected($buttonType === $key)>{{ $option['label'] }}</option>
                                        @endforeach
                                    </select>
                                    <div data-custom-url-field class="{{ $buttonType === 'custom' ? '' : 'hidden' }}">
                                        <input name="custom_button_url" value="{{ $customButtonUrl }}" class="form-input-sipaman mt-2 w-full" placeholder="Contoh: /products atau https://contoh.go.id">
                                        <p class="mt-1 text-xs text-slate-500">Link khusus harus diawali http://, https://, /, atau #.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <h3 class="text-sm font-bold text-slate-900">Tombol Kedua</h3>
                            <div class="mt-3 grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700">Tulisan tombol kedua</label>
                                    <input name="secondary_button_text" value="{{ old('secondary_button_text', $landingPage->secondary_button_text) }}" class="form-input-sipaman mt-1 w-full" placeholder="Contoh: Lihat UMKM">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700">Tujuan tombol kedua</label>
                                    <select name="secondary_button_url_type" data-button-url-select class="form-select-sipaman mt-1 w-full">
                                        @foreach($buttonOptions as $key => $option)
                                            <option value="{{ $key }}" @selected($secondaryButtonType === $key)>{{ $option['label'] }}</option>
                                        @endforeach
                                    </select>
                                    <div data-custom-url-field class="{{ $secondaryButtonType === 'custom' ? '' : 'hidden' }}">
                                        <input name="secondary_custom_button_url" value="{{ $secondaryCustomButtonUrl }}" class="form-input-sipaman mt-2 w-full" placeholder="Contoh: /umkm atau https://contoh.go.id">
                                        <p class="mt-1 text-xs text-slate-500">Link khusus harus diawali http://, https://, /, atau #.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Tulisan pada tombol</label>
                            <input name="button_text" value="{{ old('button_text', $landingPage->button_text) }}" class="form-input-sipaman mt-1 w-full" placeholder="Contoh: Lihat Produk">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Tujuan tombol</label>
                            <select name="button_url_type" data-button-url-select class="form-select-sipaman mt-1 w-full">
                                @foreach($buttonOptions as $key => $option)
                                    <option value="{{ $key }}" @selected($buttonType === $key)>{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                            <div data-custom-url-field class="{{ $buttonType === 'custom' ? '' : 'hidden' }}">
                                <input name="custom_button_url" value="{{ $customButtonUrl }}" class="form-input-sipaman mt-2 w-full" placeholder="Contoh: /products atau https://contoh.go.id">
                                <p class="mt-1 text-xs text-slate-500">Link khusus harus diawali http://, https://, /, atau #.</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($sectionMeta['allows_image'] ?? false)
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Gambar Banner Utama</label>
                            <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" class="form-file-sipaman mt-1">
                            <p class="mt-1 text-xs text-slate-500">Gunakan gambar JPG/PNG/WebP maksimal 2 MB. Rekomendasi rasio 4:3 atau 16:9.</p>

                            @if ($landingPage->image_url)
                                <label class="mt-3 flex items-center gap-2 text-sm font-semibold text-red-700">
                                    <input type="checkbox" name="remove_image" value="1" class="form-checkbox-sipaman text-red-600">
                                    Hapus gambar saat disimpan
                                </label>
                            @endif
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Keterangan gambar</label>
                            <input name="image_alt" value="{{ old('image_alt', $landingPage->image_alt) }}" class="form-input-sipaman mt-1 w-full" placeholder="Contoh: Produk PIRT Karanganyar">
                            <p class="mt-1 text-xs text-slate-500">Isi singkat agar gambar mudah dipahami saat tidak tampil.</p>
                        </div>
                    </div>
                @else
                    <div class="mt-4 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                        Bagian ini tidak memakai gambar. Gambar hanya dikelola pada Banner Utama.
                    </div>
                @endif

                <div class="mt-6 flex flex-wrap gap-3">
                    <button class="rounded-lg bg-slate-900 px-4 py-2 font-semibold text-white">Simpan Konten</button>
                    <a href="{{ route('panel.landing-page.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 font-semibold text-slate-700">Batal</a>
                </div>
            </div>
        </form>
    </div>

@endsection
