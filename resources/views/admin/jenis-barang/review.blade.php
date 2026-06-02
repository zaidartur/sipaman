@extends('layouts.admin')
@section('title', 'Produk Perlu Review')
@section('page-title', 'Produk Perlu Review')
@section('content')
<div class="space-y-5">
    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
        Produk di halaman ini masuk {{ $fallback->nama_jenis }} karena jenis pangan dari file import belum cocok dengan master resmi atau alias yang tersedia.
        Tambahkan alias pada jenis barang yang tepat, lalu jalankan Sinkronkan Ulang Jenis Produk.
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
            <div>
                <h2 class="font-display text-xl font-bold">Daftar Produk Perlu Review</h2>
                <p class="mt-1 text-slate-600">Gunakan data Kategori Pangan dan Jenis Pangan mentah sebagai acuan membuat alias.</p>
            </div>
            <a href="{{ route('panel.jenis-barang.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 font-semibold text-slate-700 hover:bg-slate-50">Kembali</a>
        </div>

        <form method="GET" autocomplete="off" class="mt-5 grid gap-3 md:grid-cols-[1fr_auto]">
            <input name="search" value="{{ request('search') }}" placeholder="Cari produk, No SPPIRT, pelaku usaha, kategori, atau jenis pangan" autocomplete="off" class="form-input-sipaman">
            <button class="rounded-lg border border-slate-300 px-4 py-2 font-semibold">Cari</button>
        </form>

        <div class="mt-5 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3">Produk</th>
                        <th class="px-4 py-3">No SPPIRT</th>
                        <th class="px-4 py-3">Kategori Pangan</th>
                        <th class="px-4 py-3">Jenis Pangan</th>
                        <th class="px-4 py-3">Pelaku Usaha</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr class="border-t align-top">
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $product->nama_branding }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $product->no_sppirt }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $product->kategori_pangan ?: '-' }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $product->jenis_pangan ?: '-' }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $product->nama_pelaku_usaha }}</td>
                            <td class="px-4 py-3 text-right">
                                <a class="font-semibold text-blue-700" href="{{ route('panel.products.show', $product) }}">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">Tidak ada produk yang perlu review.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $products->links() }}</div>
    </div>
</div>
@endsection
