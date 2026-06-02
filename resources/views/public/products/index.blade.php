@extends('layouts.public')
@section('title', 'Katalog Produk')
@section('content')
<section class="mx-auto max-w-container px-4 pt-10 md:px-6">
    <div class="relative overflow-hidden rounded-3xl border border-outline-variant bg-gradient-to-br from-primary-soft via-white to-secondary-soft p-8 md:p-12">
        <div class="absolute -right-12 -top-12 h-52 w-52 rounded-full bg-accent/25 blur-3xl"></div>
        <div class="relative">
            <p class="eyebrow text-[11px] font-600 text-secondary">Direktori PIRT</p>
            <h1 class="font-display mt-2 text-4xl font-700 text-ink md:text-5xl">Katalog Produk PIRT</h1>
            <p class="mt-3 max-w-2xl leading-7 text-on-surface-variant">Cari produk yang sudah terverifikasi berdasarkan nama, kecamatan, dan pelaku usaha.</p>
            <form class="mt-8 grid gap-2.5 rounded-3xl border border-outline-variant bg-white p-2.5 shadow-lift md:grid-cols-[1fr_200px_200px_auto]" method="GET" action="{{ route('products.index') }}" autocomplete="off">
                <label class="flex items-center gap-2 rounded-2xl bg-surface-container-low px-3.5 focus-within:bg-primary-soft">
                    <span class="material-symbols-outlined text-primary">search</span>
                    <input class="w-full border-0 bg-transparent py-3.5 text-on-surface placeholder:text-on-surface-variant focus:outline-none focus:ring-0" type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama produk..." autocomplete="off">
                </label>
                <label class="flex items-center gap-2 rounded-2xl bg-surface-container-low px-3.5 focus-within:bg-primary-soft">
                    <span class="material-symbols-outlined text-primary">location_on</span>
                    <select class="w-full rounded-xl border-0 bg-transparent py-3.5 text-on-surface transition focus:outline-none focus:ring-2 focus:ring-primary/25" name="kecamatan_id">
                        <option value="">Semua Kecamatan</option>
                        @foreach($kecamatans as $kecamatan)
                            <option value="{{ $kecamatan->id }}" @selected((string) request('kecamatan_id') === (string) $kecamatan->id)>{{ $kecamatan->nama_kecamatan }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="flex items-center gap-2 rounded-2xl bg-surface-container-low px-3.5 focus-within:bg-primary-soft">
                    <span class="material-symbols-outlined text-primary">category</span>
                    <select class="w-full rounded-xl border-0 bg-transparent py-3.5 text-on-surface transition focus:outline-none focus:ring-2 focus:ring-primary/25" name="jenis_barang_id">
                        <option value="">Semua Jenis</option>
                        @foreach(($jenisBarangs ?? []) as $jenisBarang)
                            <option value="{{ $jenisBarang->id }}" @selected((string) request('jenis_barang_id') === (string) $jenisBarang->id)>{{ $jenisBarang->nama_jenis }}</option>
                        @endforeach
                    </select>
                </label>
                <button class="inline-flex items-center justify-center gap-1.5 rounded-2xl bg-primary px-7 py-3.5 font-600 text-white transition-colors hover:bg-primary-container" type="submit">
                    <span class="material-symbols-outlined text-[20px]">tune</span>
                    Filter
                </button>
            </form>
        </div>
    </div>
</section>

<section class="mx-auto max-w-container px-4 py-14 md:px-6">
    <div class="mb-8 flex items-end justify-between gap-4">
        <div>
            <h2 class="font-display text-3xl font-700 text-ink">Daftar Produk</h2>
            <p class="mt-2 text-on-surface-variant">Hanya produk yang sudah lulus verifikasi yang tampil di halaman publik.</p>
        </div>
        <x-badge-status status="terverifikasi">Publik</x-badge-status>
    </div>
    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        @forelse($products as $product)
            <x-product-card
                :name="$product->nama_branding"
                :district="$product->kecamatan?->nama_kecamatan ?? $product->wilayah ?? 'Karanganyar'"
                :price="$product->harga ? 'Rp ' . number_format($product->harga, 0, ',', '.') : null"
                :description="$product->deskripsi ?? $product->jenis_pangan"
                :image="$product->gambarUtama?->gambar_url"
                :href="route('products.show', $product)"
            />
        @empty
            <div class="col-span-full rounded-3xl border border-dashed border-outline-variant bg-white p-12 text-center">
                <span class="material-symbols-outlined text-[40px] text-outline">search_off</span>
                <p class="mt-3 font-600 text-on-surface">Belum ada produk terverifikasi</p>
            </div>
        @endforelse
    </div>
    <div class="mt-10">{{ $products->links() }}</div>
</section>
@endsection
