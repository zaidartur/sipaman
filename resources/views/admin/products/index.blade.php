@extends('layouts.admin')

@section('title', 'Kelola Produk')
@section('page-title', 'Kelola Produk')

@section('content')
    <div class="space-y-6">
        @if (session('success'))
            <x-alert type="success">
                {{ session('success') }}
            </x-alert>
        @endif

        @if ($errors->any())
            <x-alert type="danger">
                <div class="font-semibold">Ada kesalahan:</div>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        @if (session('import_failures') && count(session('import_failures')) > 0)
            <x-alert type="warning">
                <div class="font-semibold">Sebagian baris gagal dibaca. Contoh maksimal 5 baris:</div>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach (session('import_failures') as $failure)
                        <li>
                            Baris {{ $failure['baris'] ?? '-' }}:
                            {{ $failure['errors'][0] ?? 'Data tidak valid.' }}
                        </li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        @if (session('import_warnings') && count(session('import_warnings')) > 0)
            <x-alert type="warning">
                <div class="font-semibold">Sebagian jenis pangan perlu review master data. Contoh maksimal 5 baris:</div>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach (session('import_warnings') as $warning)
                        <li>
                            Baris {{ $warning['baris'] ?? '-' }}:
                            {{ $warning['message'] ?? 'Jenis pangan belum cocok dengan master resmi.' }}
                            @if (! empty($warning['nilai']['jenis_pangan']))
                                <span class="text-slate-600">({{ $warning['nilai']['jenis_pangan'] }})</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        {{-- Stats --}}
        <div class="grid gap-4 md:grid-cols-3">
            <div class="panel-card">
                <p class="text-sm font-semibold text-slate-500">Total Produk</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($stats['total'] ?? 0) }}</p>
            </div>
            <div class="panel-card">
                <p class="text-sm font-semibold text-slate-500">Terverifikasi</p>
                <p class="mt-2 text-3xl font-bold text-emerald-700">{{ number_format($stats['verified'] ?? 0) }}</p>
            </div>
            <div class="panel-card">
                <p class="text-sm font-semibold text-slate-500">Belum Terverifikasi</p>
                <p class="mt-2 text-3xl font-bold text-amber-700">{{ number_format($stats['unverified'] ?? 0) }}</p>
            </div>
        </div>

        {{-- Import Rekap PIRT --}}
        <div class="panel-form-card">
            <div class="flex items-start gap-3">
                <span class="material-symbols-outlined rounded-lg bg-blue-50 p-2 text-blue-700">upload_file</span>
                <div>
                    <h2 class="font-display text-xl font-bold">Import Rekap Data PIRT Diterbitkan</h2>
                    <p class="mt-1 text-slate-600">
                        Upload file Excel rekap PIRT untuk mengisi data produk: No SPPIRT, nama branding,
                        kategori pangan, NIB, wilayah, status OSS, pelaku usaha, dan alamat.
                    </p>
                    <p class="mt-1 text-sm text-slate-500">
                        Format yang didukung: .xls, .xlsx, dan .csv. Maksimal 10 MB.
                    </p>
                    <p class="mt-1 text-sm text-amber-700 font-medium">
                        Produk baru yang diimport akan berstatus <span class="font-bold">Belum Verifikasi</span>.
                        Lakukan verifikasi di halaman <a href="{{ route('panel.verifications.index') }}" class="underline">Verifikasi Produk</a>.
                    </p>
                    @if ($lastImport)
                        <p class="mt-2 text-sm text-slate-500">
                            Import terakhir: <span class="font-semibold">{{ $lastImport->nama_file }}</span>
                            — berhasil {{ $lastImport->jumlah_berhasil }}, gagal {{ $lastImport->jumlah_gagal }}.
                        </p>
                    @endif
                </div>
            </div>

            <form action="{{ route('panel.products.import.rekap-pirt') }}" method="POST" enctype="multipart/form-data" class="mt-5" data-loading-form data-loading-message="Memproses import Rekap PIRT...">
                @csrf
                <div class="flex flex-col gap-3 md:flex-row md:items-center">
                    <input
                        type="file"
                        name="file"
                        accept=".xlsx,.xls,.csv"
                        required
                        class="form-file-sipaman"
                    >
                    <button type="submit" class="shrink-0 rounded-lg bg-blue-700 px-5 py-2 font-semibold text-white hover:bg-blue-800">
                        Import Rekap PIRT
                    </button>
                </div>
            </form>
        </div>

        {{-- Tabel Produk --}}
        <div class="panel-table-card">
            <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                <div>
                    <h2 class="font-display text-xl font-bold">Data Produk PIRT</h2>
                    <p class="mt-1 text-slate-600">Data produk resmi hanya dibaca dari import Rekap Data PIRT. Admin dapat melihat detail, mencari, memfilter, dan melakukan import.</p>
                </div>
            </div>

            <form method="GET" action="{{ route('panel.products.index') }}" autocomplete="off" class="mt-5 grid gap-3 md:grid-cols-[1fr_190px_190px_190px_auto]">
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari nama produk, pelaku usaha, No SPPIRT..."
                    autocomplete="off"
                    class="form-input-sipaman"
                >
                <select name="kecamatan_id" class="form-select-sipaman">
                    <option value="">Semua Kecamatan</option>
                    @foreach ($kecamatans as $kecamatan)
                        <option value="{{ $kecamatan->id }}" @selected((string) request('kecamatan_id') === (string) $kecamatan->id)>{{ $kecamatan->nama_kecamatan }}</option>
                    @endforeach
                </select>
                <select name="jenis_barang_id" class="form-select-sipaman">
                    <option value="">Semua Jenis</option>
                    @foreach ($jenisBarangs as $jenisBarang)
                        <option value="{{ $jenisBarang->id }}" @selected((string) request('jenis_barang_id') === (string) $jenisBarang->id)>{{ $jenisBarang->nama_jenis }}</option>
                    @endforeach
                </select>
                <select name="status" class="form-select-sipaman">
                    <option value="">Semua Status</option>
                    <option value="verified" @selected(request('status') === 'verified')>Terverifikasi</option>
                    <option value="unverified" @selected(request('status') === 'unverified')>Belum Terverifikasi</option>
                </select>
                <button type="submit" class="rounded-lg border border-slate-300 px-4 py-2 font-semibold text-slate-700 hover:bg-slate-50">
                    Filter
                </button>
            </form>

            <div class="panel-table-wrapper">
                <table class="panel-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>No SPPIRT</th>
                            <th>Wilayah</th>
                            <th>Pelaku Usaha</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr>
                                <td>
                                    <div class="font-semibold text-slate-900">{{ $product->nama_branding }}</div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ $product->jenisBarang?->nama_jenis ?? 'Belum diklasifikasi' }}
                                        <span class="text-slate-400">/ {{ $product->jenis_pangan ?? $product->kategori_pangan ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="text-slate-700">{{ $product->no_sppirt }}</td>
                                <td class="text-slate-700">
                                    {{ $product->kecamatan->nama_kecamatan ?? $product->wilayah ?? '-' }}
                                </td>
                                <td class="text-slate-700">{{ $product->nama_pelaku_usaha }}</td>
                                <td>
                                    @if ($product->is_verified)
                                        <x-badge-status status="terverifikasi">Terverifikasi</x-badge-status>
                                    @else
                                        <x-badge-status status="belum_terverifikasi">Belum</x-badge-status>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a class="font-semibold text-blue-700 hover:text-blue-900" href="{{ route('panel.products.show', $product) }}">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-10 text-center text-slate-500">
                                    Belum ada data produk. Import file Rekap Data PIRT terlebih dahulu.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-5">
                {{ $products->links() }}
            </div>
        </div>
    </div>
@endsection
