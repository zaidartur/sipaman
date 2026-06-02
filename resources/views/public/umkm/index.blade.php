@extends('layouts.public')
@section('title', 'UMKM')
@section('content')
<section class="mx-auto max-w-container px-4 pt-10 md:px-6">
    <div class="relative overflow-hidden rounded-3xl border border-outline-variant bg-gradient-to-br from-secondary-soft via-white to-primary-soft p-8 md:p-12">
        <div class="absolute -right-12 -top-12 h-52 w-52 rounded-full bg-primary/15 blur-3xl"></div>
        <div class="relative">
            <p class="eyebrow text-[11px] font-600 text-secondary">Pelaku Usaha</p>
            <h1 class="font-display mt-2 text-4xl font-700 text-ink md:text-5xl">Pelaku Usaha / UMKM</h1>
            <p class="mt-3 max-w-2xl leading-7 text-on-surface-variant">Daftar pelaku usaha yang memiliki produk PIRT terverifikasi.</p>
            <form class="mt-8 flex max-w-xl gap-2.5 rounded-3xl border border-outline-variant bg-white p-2.5 shadow-lift" method="GET" autocomplete="off">
                <label class="flex flex-1 items-center gap-2 rounded-2xl bg-surface-container-low px-3.5 focus-within:bg-primary-soft">
                    <span class="material-symbols-outlined text-primary">search</span>
                    <input name="search" value="{{ request('search') }}" placeholder="Cari pelaku usaha..." autocomplete="off" class="w-full border-0 bg-transparent py-3 text-on-surface placeholder:text-on-surface-variant focus:outline-none focus:ring-0">
                </label>
                <button class="rounded-2xl bg-primary px-6 py-3 font-600 text-white transition-colors hover:bg-primary-container">Cari</button>
            </form>
        </div>
    </div>
</section>

<section class="mx-auto max-w-container px-4 py-14 md:px-6">
    <div class="grid gap-5 md:grid-cols-3">
        @forelse($umkms as $umkm)
            <a href="{{ route('umkm.show', \Illuminate\Support\Str::slug($umkm->nama_pelaku_usaha)) }}" class="group rounded-3xl border border-outline-variant bg-white p-6 shadow-soft transition-all duration-300 hover:-translate-y-1 hover:shadow-lift">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary-soft text-primary transition-colors group-hover:bg-primary group-hover:text-white">
                    <span class="material-symbols-outlined">storefront</span>
                </span>
                <h2 class="font-display mt-4 text-xl font-700 text-ink transition-colors group-hover:text-primary">{{ $umkm->nama_pelaku_usaha }}</h2>
                <p class="mt-1.5 flex items-center gap-1 text-sm text-on-surface-variant">
                    <span class="material-symbols-outlined text-[16px]">location_on</span>
                    {{ $umkm->wilayah ?? 'Karanganyar' }}
                </p>
                <p class="mt-3 inline-flex items-center gap-1 rounded-full bg-secondary-soft px-2.5 py-1 text-xs font-600 text-secondary">
                    <span class="material-symbols-outlined text-[14px]">inventory_2</span>
                    {{ $umkm->total_produk }} produk
                </p>
            </a>
        @empty
            <div class="col-span-full rounded-3xl border border-dashed border-outline-variant bg-white p-12 text-center">
                <span class="material-symbols-outlined text-[40px] text-outline">storefront</span>
                <p class="mt-3 font-600 text-on-surface">Belum ada UMKM terverifikasi</p>
            </div>
        @endforelse
    </div>
    <div class="mt-10">{{ $umkms->links() }}</div>
</section>
@endsection
