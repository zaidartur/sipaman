@extends('layouts.admin')

@section('title', 'Detail Produk')
@section('page-title', 'Detail Produk')

@section('content')
    <div class="space-y-6">
        @if (session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
        @if ($errors->any()) <x-alert type="danger"><ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></x-alert> @endif

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
                <div>
                    <h2 class="font-display text-2xl font-bold">{{ $produk->nama_branding }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $produk->no_sppirt }}</p>
                    <div class="mt-3">@if ($produk->is_verified)<x-badge-status status="terverifikasi">Terverifikasi</x-badge-status>@else<x-badge-status status="belum_terverifikasi">Belum Terverifikasi</x-badge-status>@endif</div>
                </div>
            </div>

            <dl class="mt-6 grid gap-4 md:grid-cols-3">
                @foreach ([
                    'Kategori' => $produk->kategori_pangan ?? '-',
                    'Jenis Pangan' => $produk->jenis_pangan ?? '-',
                    'Jenis Barang' => $produk->jenisBarang?->nama_jenis ?? '-',
                    'Wilayah' => $produk->kecamatan?->nama_kecamatan ?? $produk->wilayah ?? '-',
                    'Pelaku Usaha' => $produk->nama_pelaku_usaha,
                    'NIB' => $produk->nib ?? '-',
                    'No HP' => $produk->no_hp ?? '-',
                    'Harga' => $produk->harga ? 'Rp ' . number_format($produk->harga, 0, ',', '.') : '-',
                    'Masa Berlaku' => $produk->masa_berlaku_pirt?->format('d/m/Y') ?? '-',
                ] as $label => $value)
                    <div class="rounded-lg bg-slate-50 p-4">
                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ $label }}</dt>
                        <dd class="mt-1 font-semibold text-slate-900">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div><h3 class="font-bold">Alamat</h3><p class="mt-2 text-sm leading-6 text-slate-600">{{ $produk->alamat }}</p></div>
                <div><h3 class="font-bold">Deskripsi</h3><p class="mt-2 text-sm leading-6 text-slate-600">{{ $produk->deskripsi ?? '-' }}</p></div>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col justify-between gap-3 md:flex-row md:items-start">
                <div>
                    <h3 class="font-display text-lg font-bold">Gambar Produk</h3>
                    <p class="mt-1 text-sm text-slate-600">Gambar produk dikelola dari menu khusus Gambar Produk dan hanya untuk produk terverifikasi.</p>
                </div>
                <a href="{{ route('admin.product-images.index') }}" class="inline-flex w-fit items-center gap-2 rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white">
                    <span class="material-symbols-outlined text-[18px]">image</span>
                    Buka Gambar Produk
                </a>
            </div>

            <div class="mt-5">
                @if ($produk->gambarUtama)
                    <div class="max-w-sm overflow-hidden rounded-lg border border-slate-200">
                        <img src="{{ $produk->gambarUtama->gambar_url }}" alt="{{ $produk->nama_branding }}" class="h-56 w-full object-cover">
                        <div class="p-3 text-sm font-semibold text-slate-700">Gambar aktif</div>
                    </div>
                @else
                    <p class="text-sm text-slate-500">Belum ada gambar produk.</p>
                @endif
            </div>
        </div>
    </div>
@endsection
