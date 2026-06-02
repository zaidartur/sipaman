@extends('layouts.admin')
@section('title', 'Jenis Barang')
@section('page-title', 'Jenis Barang')
@section('content')
<div class="space-y-5">
    @if (session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
    @if ($errors->any()) <x-alert type="danger"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></x-alert> @endif

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
            <div>
                <h2 class="font-display text-xl font-bold">Master Jenis Pangan PIRT</h2>
                <p class="mt-1 text-slate-600">Master mengikuti data resmi PIRT. Kelola alias untuk mencocokkan jenis pangan dari file Rekap PIRT.</p>
                <p class="mt-2 text-sm text-amber-700">
                    {{ number_format($fallbackProductsCount) }} produk berada di kategori {{ $fallback->nama_jenis }}.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('panel.jenis-barang.review') }}" class="rounded-lg border border-amber-200 px-4 py-2 font-semibold text-amber-700 hover:bg-amber-50">Lihat Perlu Review</a>
                <form
                    action="{{ route('panel.jenis-barang.sync') }}"
                    method="POST"
                    data-confirm="Sinkronkan ulang jenis produk berdasarkan alias terbaru?"
                >
                    @csrf
                    <button class="rounded-lg border border-blue-200 px-4 py-2 font-semibold text-blue-700 hover:bg-blue-50">Sinkronkan Ulang Jenis Produk</button>
                </form>
                <a href="{{ route('panel.jenis-barang.create') }}" class="rounded-lg bg-slate-900 px-4 py-2 font-semibold text-white">Tambah Jenis</a>
            </div>
        </div>

        <div class="mt-5 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3">Nama Jenis</th>
                        <th class="px-4 py-3">Kategori Resmi</th>
                        <th class="px-4 py-3">Keterangan</th>
                        <th class="px-4 py-3">Alias</th>
                        <th class="px-4 py-3">Produk</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jenisBarangs as $jenisBarang)
                        <tr class="border-t align-top">
                            <td class="px-4 py-3 font-semibold text-slate-900">
                                {{ $jenisBarang->nama_jenis }}
                                @if ($jenisBarang->slug)
                                    <span class="mt-1 block text-xs font-normal text-slate-500">{{ $jenisBarang->slug }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                {{ $jenisBarang->kategori_resmi ?: '-' }}
                                @if ($jenisBarang->nomor_kategori)
                                    <span class="mt-1 block text-xs text-slate-500">Kategori {{ $jenisBarang->nomor_kategori }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $jenisBarang->deskripsi ?: $jenisBarang->keterangan ?: '-' }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ number_format($jenisBarang->aliases_count) }} keyword</td>
                            <td class="px-4 py-3 text-slate-700">{{ number_format($jenisBarang->produks_count) }}</td>
                            <td class="px-4 py-3">
                                @if ($jenisBarang->is_active)
                                    <span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700">Aktif</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-600">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right"><a class="font-semibold text-blue-700" href="{{ route('panel.jenis-barang.edit', $jenisBarang) }}">Edit</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">Belum ada jenis barang.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $jenisBarangs->links() }}</div>
    </div>
</div>
@endsection
