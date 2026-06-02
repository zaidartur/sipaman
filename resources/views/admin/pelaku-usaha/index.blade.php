@extends('layouts.admin')

@section('title', 'Akun Pelaku Usaha')
@section('page-title', 'Akun Pelaku Usaha')

@section('content')
<div class="space-y-5">
    @if (session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
    @if ($errors->any()) <x-alert type="danger"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></x-alert> @endif

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div>
            <h2 class="font-display text-xl font-bold">Manajemen Akun Pelaku Usaha</h2>
            <p class="mt-1 text-slate-600">Admin dapat mengaktifkan, menonaktifkan, mengunci, dan mengatur password akun pelaku usaha. Identitas login pelaku usaha adalah NIB.</p>
        </div>

        <form method="GET" autocomplete="off" class="mt-5 grid gap-3 md:grid-cols-[1fr_180px_210px_auto]">
            <input name="search" value="{{ request('search') }}" placeholder="Cari nama atau NIB" autocomplete="off" class="form-input-sipaman">
            <select name="status_akun" class="form-select-sipaman">
                <option value="">Semua Status</option>
                @foreach (['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif', 'kunci' => 'Kunci'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('status_akun') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="password_status" class="form-select-sipaman">
                <option value="">Semua Password</option>
                <option value="needs_setup" @selected(request('password_status') === 'needs_setup')>Belum Diset</option>
                <option value="ready" @selected(request('password_status') === 'ready')>Sudah Diset</option>
            </select>
            <button class="rounded-lg border border-slate-300 px-4 py-2 font-semibold">Filter</button>
        </form>

        <div class="mt-5 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">NIB Login</th>
                        <th class="px-4 py-3">Produk</th>
                        <th class="px-4 py-3">Password</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr class="border-t">
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $user->nama }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $user->nib ?: '-' }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ number_format($user->produks_count) }}</td>
                            <td class="px-4 py-3">
                                @if ($user->needsPasswordSetup())
                                    <span class="rounded-full bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700">Belum diset</span>
                                @else
                                    <span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700">Sudah diset</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-700">{{ ucfirst($user->status_akun) }}</td>
                            <td class="px-4 py-3 text-right">
                                <a class="font-semibold text-blue-700" href="{{ route('panel.pelaku-usaha.edit', $user) }}">Atur</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada akun pelaku usaha.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $users->links() }}</div>
    </div>
</div>
@endsection
