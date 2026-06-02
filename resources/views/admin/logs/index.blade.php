@extends('layouts.admin')
@section('title', 'Log Aktivitas')
@section('page-title', 'Log Aktivitas')
@section('content')
<div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
        <div>
            <h2 class="font-display text-xl font-bold">Log Aktivitas Akses</h2>
            <p class="mt-1 text-slate-600">Mencatat login dan logout pengguna. Perubahan data penting tetap dicatat di Audit Trail.</p>
        </div>
    </div>

    <form method="GET" autocomplete="off" class="mt-5 grid gap-3 md:grid-cols-[1fr_auto]">
        <input name="search" value="{{ request('search') }}" placeholder="Cari nama, NIB, email admin, aktivitas, atau IP" autocomplete="off" class="form-input-sipaman">
        <button class="rounded-lg border border-slate-300 px-4 py-2 font-semibold">Cari</button>
    </form>

    <div class="mt-5 overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-4 py-3">Waktu</th>
                    <th class="px-4 py-3">Pengguna</th>
                    <th class="px-4 py-3">Role</th>
                    <th class="px-4 py-3">Aktivitas</th>
                    <th class="px-4 py-3">IP</th>
                    <th class="px-4 py-3">Perangkat</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    @php($role = $log->user?->role?->nama_role)
                    <tr class="border-t align-top">
                        <td class="px-4 py-3 whitespace-nowrap">{{ $log->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">
                            <div class="font-semibold text-slate-900">{{ $log->user?->nama ?? '-' }}</div>
                            <div class="mt-1 text-xs text-slate-500">
                                @if ($role === 'user')
                                    NIB {{ $log->user?->nib ?? '-' }}
                                @elseif ($log->user)
                                    {{ $log->user->email ?? '-' }}
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-slate-700">{{ $role ? str_replace('_', ' ', $role) : '-' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $log->aktivitas }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $log->ip_address ?: '-' }}</td>
                        <td class="max-w-xs px-4 py-3 text-xs text-slate-500">{{ $log->user_agent ?: '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada log aktivitas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $logs->links() }}</div>
</div>
@endsection
