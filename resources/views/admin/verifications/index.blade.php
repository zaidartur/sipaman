@extends('layouts.admin')
@section('title', 'Verifikasi Produk')
@section('page-title', 'Verifikasi Produk')
@section('content')
<div class="space-y-6">
    @if (session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
    @if ($errors->any()) <x-alert type="danger"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></x-alert> @endif
    @if (session('import_failures') && count(session('import_failures')) > 0)
        <x-alert type="warning"><div class="font-semibold">Sebagian baris gagal dibaca. Contoh maksimal 5 baris:</div><ul class="mt-2 list-disc pl-5">@foreach(session('import_failures') as $failure)<li>Baris {{ $failure['baris'] ?? '-' }}: {{ $failure['errors'][0] ?? 'Data tidak valid.' }}</li>@endforeach</ul></x-alert>
    @endif

    <div class="grid gap-4 md:grid-cols-4">
        @foreach ([['Semua', $stats['total']], ['Terverifikasi', $stats['terverifikasi']], ['Proses', $stats['proses']], ['Belum', $stats['belum']]] as [$label, $value])
            <div class="panel-card"><p class="text-sm font-semibold text-slate-500">{{ $label }}</p><p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($value) }}</p></div>
        @endforeach
    </div>

    <div class="panel-form-card">
        <h2 class="font-display text-xl font-bold">Import Status Pemenuhan Komitmen</h2>
        <p class="mt-1 text-slate-600">Upload Excel status komitmen untuk sinkron otomatis ke verifikasi produk.</p>
        <p class="mt-1 text-sm font-semibold text-amber-700">Status verifikasi hanya diperbarui melalui import Excel Status Pemenuhan Komitmen agar sesuai dengan data sumber resmi.</p>
        <p class="mt-1 text-sm text-slate-500">Format yang didukung: .xls, .xlsx, dan .csv. Maksimal 10 MB.</p>
        @if ($lastImport)
            <p class="mt-2 text-sm text-slate-500">Import terakhir: <span class="font-semibold">{{ $lastImport->nama_file }}</span> oleh {{ $lastImport->user?->nama ?? '-' }}</p>
        @endif
        <form action="{{ route('panel.verifications.import') }}" method="POST" enctype="multipart/form-data" class="mt-5 flex flex-col gap-3 md:flex-row md:items-center" data-loading-form data-loading-message="Memproses import Status Pemenuhan Komitmen...">
            @csrf
            <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="form-file-sipaman">
            <button class="rounded-lg bg-blue-700 px-5 py-2 font-semibold text-white">Import Status</button>
        </form>
    </div>

    <div class="panel-table-card">
        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
            <div><h2 class="font-display text-xl font-bold">Daftar Verifikasi</h2><p class="mt-1 text-slate-600">Status ditampilkan read-only dan mengikuti hasil import Excel.</p></div>
            <div class="flex flex-wrap gap-2 text-sm font-semibold">
                @foreach(['semua'=>'Semua','terverifikasi'=>'Terverifikasi','proses'=>'Proses','belum'=>'Belum'] as $key => $label)
                    <a href="{{ route('panel.verifications.index', ['tab' => $key]) }}" class="rounded-lg border px-3 py-2 {{ $tab === $key ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-50' }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>
        <form method="GET" action="{{ route('panel.verifications.index') }}" autocomplete="off" class="mt-5 grid gap-3 {{ $tab === 'proses' ? 'md:grid-cols-[1fr_repeat(4,150px)_auto]' : 'md:grid-cols-[1fr_auto]' }}">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari produk / No SPPIRT..." autocomplete="off" class="form-input-sipaman w-full">
            @if ($tab === 'proses')
                @foreach ($trackingFilterLabels as $field => $label)
                    <select name="{{ $field }}" class="form-select-sipaman">
                        <option value="">{{ $label }}: Semua</option>
                        <option value="1" @selected(($trackingFilters[$field] ?? null) === '1')>{{ $label }}: Ya</option>
                        <option value="0" @selected(($trackingFilters[$field] ?? null) === '0')>{{ $label }}: Tidak</option>
                    </select>
                @endforeach
            @endif
            <button class="rounded-lg border border-slate-300 px-4 py-2 font-semibold">Filter</button>
        </form>
        <div class="panel-table-wrapper">
            <table class="panel-table">
                <thead><tr><th>Produk</th><th>Syarat</th><th>Status</th><th>Verifikator</th><th></th></tr></thead>
                <tbody>
                    @forelse($products as $product)
                        @php($v = $product->verifikasi)
                        <tr>
                            <td><div class="font-semibold">{{ $product->nama_branding }}</div><div class="text-xs text-slate-500">{{ $product->no_sppirt }}</div></td>
                            <td class="text-xs text-slate-600">
                                Produk: {{ $v?->verifikasi_produk ? 'Ya' : 'Tidak' }} /
                                Label: {{ $v?->verifikasi_label ? 'Ya' : 'Tidak' }} /
                                PKP: {{ $v?->pkp ? 'Ya' : 'Tidak' }} /
                                CPPOB: {{ $v?->cppob_pemeriksaan_sarana ? 'Ya' : 'Tidak' }}
                            </td>
                            <td>@if($product->is_verified)<x-badge-status status="terverifikasi">Terverifikasi</x-badge-status>@elseif($v)<x-badge-status status="proses">Proses</x-badge-status>@else<x-badge-status status="belum_terverifikasi">Belum</x-badge-status>@endif</td>
                            <td>{{ $v?->verifikator?->nama ?? '-' }}</td>
                            <td class="text-right"><a class="font-semibold text-blue-700" href="{{ route('panel.verifications.show', $product) }}">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-10 text-center text-slate-500">Tidak ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $products->links() }}</div>
    </div>
</div>
@endsection
