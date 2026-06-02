@extends('layouts.admin')
@section('title', 'Kelola Admin')
@section('page-title', 'Kelola Admin')
@section('content')
<div class="space-y-5">
    @if (session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
    @if ($errors->any()) <x-alert type="danger"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></x-alert> @endif

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
            <div>
                <h2 class="font-display text-xl font-bold">Manajemen Akun Admin</h2>
                <p class="mt-1 text-slate-600">Super admin dapat membuat, mengatur password/status, dan menghapus akun admin.</p>
            </div>
            <a href="{{ route('super-admin.users.create') }}" class="rounded-lg bg-slate-900 px-4 py-2 font-semibold text-white">Tambah Admin</a>
        </div>

        <form method="GET" autocomplete="off" class="mt-5 grid gap-3 md:grid-cols-[1fr_auto]">
            <input name="search" value="{{ request('search') }}" placeholder="Cari nama atau email admin" autocomplete="off" class="form-input-sipaman">
            <button class="rounded-lg border px-4 py-2 font-semibold">Filter</button>
        </form>

        <div class="mt-5 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Identifier Login</th>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        @php($role = $user->role?->nama_role)
                        <tr class="border-t">
                            <td class="px-4 py-3 font-semibold">{{ $user->nama }}</td>
                            <td class="px-4 py-3">
                                <span class="font-semibold">Email:</span> {{ $user->email ?: 'Belum ada email' }}
                            </td>
                            <td class="px-4 py-3">{{ str_replace('_', ' ', $role ?? '-') }}</td>
                            <td class="px-4 py-3">{{ $user->status_akun }}</td>
                            <td class="px-4 py-3 text-right">
                                @if ($user->id !== auth()->id() && $role === 'admin')
                                    <a class="font-semibold text-blue-700" href="{{ route('super-admin.users.edit', $user) }}">Atur</a>
                                @else
                                    <span class="text-slate-400">Dikunci</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada akun admin.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $users->links() }}</div>
    </div>
</div>
@endsection
