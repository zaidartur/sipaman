@extends('layouts.public')
@section('title', 'Dashboard Pelaku Usaha')
@section('content')
<style nonce="{{ app(\Spatie\Csp\Nonce\NonceGenerator::class)->generate() }}">
    /* Pengaman: dialog yang tertutup tidak boleh menghalangi klik */
    dialog:not([open]) { display: none; }
</style>
<section class="mx-auto max-w-container px-4 pt-10 md:px-6">
    <div class="relative overflow-hidden rounded-3xl border border-outline-variant bg-gradient-to-br from-primary-soft via-white to-secondary-soft p-8 md:p-10">
        <div class="pointer-events-none absolute -right-10 -top-10 h-48 w-48 rounded-full bg-accent/25 blur-3xl"></div>
        <div class="relative flex flex-col justify-between gap-5 md:flex-row md:items-end">
            <div>
                <x-badge-status status="info">Dashboard Pelaku Usaha</x-badge-status>
                <h1 class="font-display mt-4 text-3xl font-700 text-ink md:text-4xl">Data toko dan produk milik Anda</h1>
                <p class="mt-3 max-w-2xl leading-8 text-on-surface-variant">Anda dapat melengkapi harga, deskripsi tampilan, dan gambar produk. Data resmi PIRT tetap dikunci oleh admin.</p>
            </div>

        </div>
    </div>
</section>

{{-- Menu Cepat --}}
<section class="relative z-10 mx-auto max-w-container px-4 pt-6 md:px-6">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

        {{-- Konfigurasi Produk --}}
        <a href="{{ route('user.products.setting.index') }}"
           class="flex items-center gap-3 rounded-2xl border border-outline-variant bg-white p-4 shadow-soft transition-colors hover:border-primary hover:bg-primary-soft">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-primary">
                <span class="material-symbols-outlined text-[20px]">tune</span>
            </span>
            <div>
                <p class="text-sm font-700 text-ink">Konfigurasi Produk</p>
                <p class="text-xs text-on-surface-variant">Atur harga, deskripsi, gambar</p>
            </div>
        </a>

        {{-- Pengaturan Akun --}}
        <a href="{{ route('user.account.index') }}"
           class="flex items-center gap-3 rounded-2xl border border-outline-variant bg-white p-4 shadow-soft transition-colors hover:border-primary hover:bg-primary-soft">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-primary">
                <span class="material-symbols-outlined text-[20px]">manage_accounts</span>
            </span>
            <div>
                <p class="text-sm font-700 text-ink">Pengaturan Akun</p>
                <p class="text-xs text-on-surface-variant">NIB & password</p>
            </div>
        </a>

        {{-- Jumlah Produk (info) --}}
        <div class="flex items-center gap-3 rounded-2xl border border-outline-variant bg-white p-4 shadow-soft">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-secondary-soft text-secondary">
                <span class="material-symbols-outlined text-[20px]">inventory_2</span>
            </span>
            <div>
                <p class="text-sm font-700 text-ink">{{ $products->total() }} Produk</p>
                <p class="text-xs text-on-surface-variant">Total terdaftar</p>
            </div>
        </div>

    </div>
</section>

<section class="relative z-10 mx-auto max-w-container px-4 py-8 md:px-6">
    @if (session('success'))
        <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
    @endif

    <div class="rounded-3xl border border-outline-variant bg-white p-6 shadow-soft md:p-8">
        <div class="mb-6 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary-soft text-primary">
                    <span class="material-symbols-outlined text-[20px]">inventory_2</span>
                </span>
                <h2 class="font-display text-xl font-700 text-ink">Produk Saya</h2>
            </div>
            <span class="rounded-full bg-surface-container px-3 py-1 text-xs font-600 text-on-surface-variant">
                {{ $products->total() }} produk
            </span>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($products as $product)
                {{-- Kartu produk dengan gambar --}}
                <article class="group relative flex flex-col overflow-hidden rounded-2xl border border-outline-variant/70 bg-surface shadow-soft transition-all duration-300 hover:-translate-y-1 hover:border-primary/30 hover:shadow-lift">
                    {{-- Gambar produk --}}
                    <a href="{{ route('user.products.setting.edit', $product->id) }}" class="block">
                        <div class="relative aspect-[4/3] overflow-hidden bg-surface-container">
                            <img
                                class="h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-110"
                                src="{{ $product->gambarUtama?->gambar_url ?? 'https://images.unsplash.com/photo-1606914501449-5a96b6ce24ca?auto=format&fit=crop&w=900&q=80' }}"
                                alt="{{ $product->nama_branding }}"
                            >
                            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-ink/40 to-transparent"></div>
                            <div class="absolute left-3 top-3">
                                @if($product->is_verified)
                                    <x-badge-status status="terverifikasi">Terverifikasi</x-badge-status>
                                @else
                                    <x-badge-status status="belum_terverifikasi">Belum Verifikasi</x-badge-status>
                                @endif
                            </div>
                        </div>
                    </a>

                    {{-- Isi kartu --}}
                    <div class="flex flex-1 flex-col gap-2.5 p-5">
                        <p class="eyebrow flex items-center gap-1 text-[10px] font-600 text-secondary">
                            <span class="material-symbols-outlined text-[14px]">qr_code_2</span>
                            {{ $product->no_sppirt }}
                        </p>
                        <h3 class="font-display text-xl font-600 leading-snug text-primary transition-colors group-hover:text-secondary">
                            <a href="{{ route('user.products.setting.edit', $product->id) }}">{{ $product->nama_branding }}</a>
                        </h3>
                        <p class="line-clamp-2 text-sm leading-6 text-on-surface-variant">
                            {{ $product->deskripsi ?? $product->jenis_pangan ?? $product->kategori_pangan ?? 'Produk PIRT' }}
                        </p>

                        {{-- Harga --}}
                        <div class="mt-auto flex items-center justify-between gap-3 border-t border-outline-variant/60 pt-3">
                            @if($product->harga)
                                <p class="font-display text-lg font-600 text-primary">
                                    Rp {{ number_format($product->harga, 0, ',', '.') }}
                                </p>
                            @else
                                <a href="{{ route('user.products.setting.edit', $product->id) }}"
                                   class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-600 text-amber-700 hover:bg-amber-100"
                                   title="Klik untuk atur harga">
                                    <span class="material-symbols-outlined text-[14px]">warning</span>
                                    Atur harga
                                </a>
                            @endif
                        </div>

                        {{-- Tombol aksi --}}
                        <div class="relative z-10 flex items-center justify-end border-t border-outline-variant/60 pt-3">
                            <a href="{{ route('user.products.setting.edit', $product->id) }}" title="Edit produk"
                               class="inline-flex items-center gap-1.5 rounded-lg bg-primary-soft px-3 py-2 text-xs font-600 text-primary transition-colors hover:bg-primary hover:text-white">
                                <span class="material-symbols-outlined text-[17px]">edit</span>
                                Edit
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-outline-variant bg-surface-container-low px-6 py-14 text-center text-on-surface-variant">
                    <span class="material-symbols-outlined text-[40px] text-outline">inventory_2</span>
                    <p class="mt-2 font-600 text-on-surface">Belum ada produk</p>
                    <p class="mt-1 text-sm">Hubungi admin untuk menambahkan produk PIRT Anda.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="mt-6">{{ $products->links() }}</div>
</section>
@endsection
