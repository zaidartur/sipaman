@extends('layouts.admin')

@section('title', 'Riwayat Import')
@section('page-title', 'Riwayat Import')

@section('content')
    <div class="space-y-6">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                <div>
                    <h2 class="font-display text-xl font-bold">Daftar Riwayat Import</h2>
                    <p class="mt-1 text-slate-600">Pantau file yang pernah diimport, jumlah baris berhasil, gagal, dan keterangannya.</p>
                </div>
            </div>

            <form method="GET" action="{{ route('panel.import-logs.index') }}" autocomplete="off" class="mt-5 grid gap-3 md:grid-cols-[1fr_260px_auto]">
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari nama file atau keterangan..."
                    autocomplete="off"
                    class="form-input-sipaman"
                >
                <select name="tipe_file" class="form-select-sipaman">
                    <option value="">Semua Jenis Import</option>
                    <option value="rekap_pirt" @selected(request('tipe_file') === 'rekap_pirt')>Rekap Data PIRT</option>
                    <option value="status_komitmen" @selected(request('tipe_file') === 'status_komitmen')>Status Pemenuhan Komitmen</option>
                </select>
                <button type="submit" class="rounded-lg border border-slate-300 px-4 py-2 font-semibold text-slate-700 hover:bg-slate-50">
                    Filter
                </button>
            </form>

            <div class="mt-6 overflow-hidden rounded-lg border border-slate-200">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-4 py-3">File</th>
                            <th class="px-4 py-3">Jenis Import</th>
                            <th class="px-4 py-3">Baris</th>
                            <th class="px-4 py-3">Berhasil</th>
                            <th class="px-4 py-3">Gagal</th>
                            <th class="px-4 py-3">User</th>
                            <th class="px-4 py-3">Waktu</th>
                            <th class="px-4 py-3">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr class="border-t border-slate-200 align-top">
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $log->nama_file }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $log->jenis_import_label }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ number_format($log->jumlah_baris) }}</td>
                                <td class="px-4 py-3 text-emerald-700">{{ number_format($log->jumlah_berhasil) }}</td>
                                <td class="px-4 py-3 text-red-700">{{ number_format($log->jumlah_gagal) }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $log->user?->nama ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $log->imported_at?->format('d M Y H:i') ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $log->keterangan ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr class="border-t border-slate-200">
                                <td colspan="8" class="px-4 py-10 text-center text-slate-500">
                                    Belum ada riwayat import.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-5">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
@endsection
