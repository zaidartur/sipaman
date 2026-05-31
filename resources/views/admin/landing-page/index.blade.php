@extends('layouts.admin')

@section('title', 'Landing Page')
@section('page-title', 'Landing Page')

@section('content')
    @php
        $sectionLabels = [
            'hero' => 'Banner Utama',
            'featured_products' => 'Bagian Produk Terverifikasi',
            'region_potential' => 'Bagian Potensi Wilayah',
            'umkm' => 'Bagian UMKM/Pelaku Usaha',
            'about' => 'Bagian Tentang SIPAMAN',
            'features' => 'Bagian Keunggulan Layanan',
            'products' => 'Bagian Produk Terverifikasi',
            'cta' => 'Bagian Ajakan Pengunjung',
        ];

        $sectionDescriptions = [
            'hero' => 'Bagian paling atas halaman depan yang pertama kali dilihat pengunjung.',
            'featured_products' => 'Bagian ini tampil di halaman depan website untuk mengarahkan pengunjung melihat produk PIRT yang sudah terverifikasi.',
            'region_potential' => 'Bagian ini menjelaskan potensi wilayah dan sebaran produk/pelaku usaha di Karanganyar.',
            'umkm' => 'Bagian ini mengarahkan pengunjung untuk melihat daftar UMKM atau pelaku usaha PIRT.',
            'about' => 'Bagian ini menjelaskan fungsi SIPAMAN secara singkat kepada masyarakat.',
            'features' => 'Bagian ini menampilkan manfaat utama layanan SIPAMAN.',
            'products' => 'Bagian ini mengarahkan pengunjung menuju katalog produk PIRT.',
            'cta' => 'Bagian ini berisi ajakan singkat agar pengunjung membuka halaman penting.',
        ];

        $buttonOptions = [
            'products' => ['label' => 'Halaman Produk', 'url' => '/products'],
            'umkm' => ['label' => 'Halaman UMKM', 'url' => '/umkm'],
            'home' => ['label' => 'Beranda', 'url' => '/'],
            'custom' => ['label' => 'Link khusus', 'url' => null],
        ];
    @endphp

    <div class="space-y-5">
        @if (session('success'))
            <x-alert type="success">{{ session('success') }}</x-alert>
        @endif

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
            Admin hanya mengubah teks, gambar, tombol, dan status tampil. Layout halaman, urutan bagian, dan kode teknis dikunci oleh sistem.
        </x-alert>

        @forelse($contents as $content)
            @php
                $currentButtonUrl = $content->button_url;
                $matchedButtonType = collect($buttonOptions)
                    ->except('custom')
                    ->search(fn ($option) => $option['url'] === $currentButtonUrl) ?: 'custom';
                $buttonType = old('button_url_type', $matchedButtonType);
                $customButtonUrl = old('custom_button_url', $buttonType === 'custom' ? $currentButtonUrl : '');
                $previewButtonUrl = $buttonType === 'custom'
                    ? $customButtonUrl
                    : ($buttonOptions[$buttonType]['url'] ?? $currentButtonUrl);
            @endphp

            <form action="{{ route('admin.landing-page.update', $content) }}" method="POST" enctype="multipart/form-data" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                @csrf
                @method('PUT')

                <div class="grid gap-6 lg:grid-cols-[1fr_300px]">
                    <div>
                        <div class="flex flex-col justify-between gap-3 md:flex-row md:items-start">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Bagian halaman depan</p>
                                <h2 class="font-display mt-1 text-lg font-bold text-slate-900">{{ $sectionLabels[$content->section_key] ?? 'Bagian Konten Website' }}</h2>
                                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">{{ $sectionDescriptions[$content->section_key] ?? 'Bagian ini tampil di halaman depan website dan dapat diisi sesuai kebutuhan informasi publik.' }}</p>
                            </div>
                            <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $content->is_active)) class="rounded border-slate-300 text-slate-900">
                                Tampilkan di website
                            </label>
                        </div>

                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Judul yang tampil di website</label>
                                <input name="judul" value="{{ old('judul', $content->judul) }}" class="mt-1 w-full rounded-lg border-slate-300">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Teks kecil di bawah judul</label>
                                <input name="subjudul" value="{{ old('subjudul', $content->subjudul) }}" class="mt-1 w-full rounded-lg border-slate-300">
                            </div>
                        </div>

                        <label class="mt-4 block text-sm font-semibold text-slate-700">Deskripsi singkat</label>
                        <textarea name="konten" rows="1" data-auto-resize class="scrollbar-none mt-1 min-h-[7rem] w-full overflow-hidden rounded-lg border-slate-300">{{ old('konten', $content->konten) }}</textarea>

                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Tulisan pada tombol</label>
                                <input name="button_text" value="{{ old('button_text', $content->button_text) }}" class="mt-1 w-full rounded-lg border-slate-300" placeholder="Contoh: Lihat Produk">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Tujuan tombol</label>
                                <select name="button_url_type" data-button-url-select class="mt-1 w-full rounded-lg border-slate-300">
                                    @foreach($buttonOptions as $key => $option)
                                        <option value="{{ $key }}" @selected($buttonType === $key)>{{ $option['label'] }}</option>
                                    @endforeach
                                </select>
                                <div data-custom-url-field class="{{ $buttonType === 'custom' ? '' : 'hidden' }}">
                                    <input name="custom_button_url" value="{{ $customButtonUrl }}" class="mt-2 w-full rounded-lg border-slate-300" placeholder="Contoh: /products atau https://contoh.go.id">
                                    <p class="mt-1 text-xs text-slate-500">Link khusus harus diawali http://, https://, /, atau #.</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Gambar bagian</label>
                                <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" class="mt-1 block w-full rounded-lg border border-slate-300 text-sm file:mr-4 file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:font-semibold file:text-slate-700 hover:file:bg-slate-200">
                                <p class="mt-1 text-xs text-slate-500">Gunakan gambar JPG/PNG/WebP maksimal 2 MB. Rekomendasi rasio 4:3 atau 16:9.</p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Keterangan gambar</label>
                                <input name="image_alt" value="{{ old('image_alt', $content->image_alt) }}" class="mt-1 w-full rounded-lg border-slate-300" placeholder="Contoh: Produk PIRT Karanganyar">
                                <p class="mt-1 text-xs text-slate-500">Isi singkat agar gambar mudah dipahami saat tidak tampil.</p>
                            </div>
                        </div>

                        <button class="mt-5 rounded-lg bg-slate-900 px-4 py-2 font-semibold text-white">Simpan Konten</button>
                    </div>

                    <div>
                        <p class="text-sm font-semibold text-slate-700">Preview tampilan</p>
                        <div class="mt-2 overflow-hidden rounded-xl border border-slate-200">
                            @if ($content->image_url)
                                <img src="{{ $content->image_url }}" alt="{{ $content->image_alt ?? $content->judul }}" class="aspect-[4/3] w-full object-cover">
                            @else
                                <div class="flex aspect-[4/3] w-full items-center justify-center bg-slate-50 text-center text-sm text-slate-500">
                                    Belum ada gambar
                                </div>
                            @endif
                            <div class="space-y-2 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ old('subjudul', $content->subjudul) ?: 'Teks kecil di bawah judul' }}</p>
                                <p class="font-display text-lg font-bold text-slate-900">{{ old('judul', $content->judul) ?: 'Judul bagian' }}</p>
                                <p class="text-sm leading-6 text-slate-600">{{ old('konten', $content->konten) ?: 'Deskripsi singkat akan tampil di sini.' }}</p>
                                @if(old('button_text', $content->button_text))
                                    <a href="{{ $previewButtonUrl ?: '#' }}" target="_blank" rel="noopener" class="inline-flex rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white">Preview tombol: {{ old('button_text', $content->button_text) }}</a>
                                @endif
                            </div>
                        </div>

                        @if ($content->image_url)
                            <label class="mt-3 flex items-center gap-2 text-sm font-semibold text-red-700">
                                <input type="checkbox" name="remove_image" value="1" class="rounded border-slate-300 text-red-600">
                                Hapus gambar saat disimpan
                            </label>
                        @endif

                        <div class="mt-4 rounded-xl bg-slate-50 p-4 text-sm text-slate-600">
                            <p class="font-semibold text-slate-800">Terakhir diedit</p>
                            <p class="mt-1">{{ $content->updatedBy?->nama ?? '-' }}</p>
                            <p>{{ $content->updated_at?->format('d M Y H:i') ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </form>
        @empty
            <x-alert type="info">Belum ada konten landing page di database. Jalankan seeder default agar admin tinggal mengedit bagian yang sudah disediakan.</x-alert>
        @endforelse
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-button-url-select]').forEach((select) => {
                const wrapper = select.closest('form')?.querySelector('[data-custom-url-field]');
                const toggleCustomUrl = () => wrapper?.classList.toggle('hidden', select.value !== 'custom');

                toggleCustomUrl();
                select.addEventListener('change', toggleCustomUrl);
            });
        });
    </script>
@endsection
