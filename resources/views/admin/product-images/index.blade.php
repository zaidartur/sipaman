@extends('layouts.admin')

@section('title', 'Gambar Produk')
@section('page-title', 'Gambar Produk')

@section('content')
<div class="space-y-6">
    @if (session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
    @if ($errors->any()) <x-alert type="danger"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></x-alert> @endif

    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">Total Produk Terverifikasi</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">Sudah Ada Gambar</p>
            <p class="mt-2 text-3xl font-bold text-emerald-700">{{ number_format($stats['available']) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">Belum Ada Gambar</p>
            <p class="mt-2 text-3xl font-bold text-amber-700">{{ number_format($stats['missing']) }}</p>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
            <div>
                <h2 class="font-display text-xl font-bold">Kelola Gambar Produk</h2>
                <p class="mt-1 text-slate-600">Halaman ini hanya menampilkan produk terverifikasi. Satu produk hanya menyimpan satu gambar aktif, dan upload baru akan mengganti gambar lama.</p>
            </div>
        </div>

        <form method="GET" class="mt-5 grid gap-3 md:grid-cols-[1fr_220px_auto]">
            <input name="search" value="{{ request('search') }}" placeholder="Cari nama produk, No SPPIRT, atau pelaku usaha" class="rounded-lg border-slate-300">
            <select name="image_status" class="rounded-lg border-slate-300">
                <option value="">Semua Status Gambar</option>
                <option value="available" @selected(request('image_status') === 'available')>Sudah Ada Gambar</option>
                <option value="missing" @selected(request('image_status') === 'missing')>Belum Ada Gambar</option>
            </select>
            <button class="rounded-lg border border-slate-300 px-4 py-2 font-semibold">Filter</button>
        </form>

        <div class="mt-6 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3">Preview</th>
                        <th class="px-4 py-3">Produk</th>
                        <th class="px-4 py-3">Pelaku Usaha</th>
                        <th class="px-4 py-3">Status Gambar</th>
                        <th class="px-4 py-3">Ganti Gambar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr class="border-t align-top">
                            <td class="px-4 py-3">
                                @if ($product->gambarUtama)
                                    <img src="{{ $product->gambarUtama->gambar_url }}" alt="{{ $product->nama_branding }}" class="h-20 w-28 rounded-lg object-cover">
                                @else
                                    <div class="flex h-20 w-28 items-center justify-center rounded-lg border border-dashed border-slate-300 bg-slate-50 text-slate-400">
                                        <span class="material-symbols-outlined">image</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-900">{{ $product->nama_branding }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $product->no_sppirt }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $product->jenisBarang?->nama_jenis ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-700">{{ $product->nama_pelaku_usaha }}</td>
                            <td class="px-4 py-3">
                                @if ($product->gambarUtama)
                                    <span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700">Sudah ada</span>
                                @else
                                    <span class="rounded-full bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700">Belum ada</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <form action="{{ route('admin.product-images.update', $product) }}" method="POST" enctype="multipart/form-data" class="flex min-w-[260px] flex-col gap-2">
                                    @csrf
                                    <input type="file" name="gambar" accept="image/jpeg,image/png,image/jpg,image/webp" required class="block w-full rounded-lg border border-slate-300 text-xs file:mr-3 file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:font-semibold">
                                    <button class="rounded-lg bg-blue-700 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-800">Ganti Gambar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">Tidak ada produk terverifikasi yang cocok dengan filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $products->links() }}</div>
    </div>
</div>
@endsection
