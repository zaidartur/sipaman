@extends('layouts.admin')

@section('title', 'Atur Akun Pelaku Usaha')
@section('page-title', 'Atur Akun Pelaku Usaha')

@section('content')
<div class="space-y-5">
    @if ($errors->any())
        <x-alert type="danger">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </x-alert>
    @endif

    <x-alert type="info">
        Nama dan NIB dikunci agar tetap konsisten dengan data PIRT. Admin hanya mengatur status akun dan password.
    </x-alert>

    <form action="{{ route('admin.pelaku-usaha.update', $user) }}" method="POST" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PATCH')

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="text-sm font-semibold text-slate-700">Nama Pelaku Usaha</label>
                <div class="mt-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700">{{ $user->nama }}</div>
            </div>
            <div>
                <label class="text-sm font-semibold text-slate-700">NIB Login</label>
                <div class="mt-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700">{{ $user->nib ?: '-' }}</div>
            </div>
            <div>
                <label class="text-sm font-semibold text-slate-700">Jumlah Produk Terhubung</label>
                <div class="mt-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700">{{ number_format($user->produks_count) }}</div>
            </div>
            <div>
                <label class="text-sm font-semibold text-slate-700">Status Akun</label>
                <select name="status_akun" class="mt-1 w-full rounded-lg border-slate-300">
                    @foreach(['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif', 'kunci' => 'Kunci'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('status_akun', $user->status_akun) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status_akun')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="text-sm font-semibold text-slate-700">Password Baru</label>
                <input type="password" name="password" class="mt-1 w-full rounded-lg border-slate-300" placeholder="Kosongkan jika tidak diganti">
                @error('password')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                <p class="mt-1 text-xs text-slate-500">Isi untuk set password awal atau reset password.</p>
            </div>
            <div>
                <label class="text-sm font-semibold text-slate-700">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" class="mt-1 w-full rounded-lg border-slate-300" placeholder="Ulangi password baru">
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button class="rounded-lg bg-slate-900 px-5 py-2.5 font-semibold text-white">Simpan</button>
            <a href="{{ route('admin.pelaku-usaha.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 font-semibold">Batal</a>
        </div>
    </form>
</div>
@endsection
