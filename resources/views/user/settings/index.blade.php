@extends('layouts.public')
@section('title', 'Pengaturan Akun')
@section('content')
<section class="mx-auto max-w-container px-4 pt-10 md:px-6">
    <div class="relative overflow-hidden rounded-3xl border border-outline-variant bg-gradient-to-br from-primary-soft via-white to-secondary-soft p-8 md:p-10">
        <div class="absolute -right-10 -top-10 h-48 w-48 rounded-full bg-accent/25 blur-3xl"></div>
        <div class="relative">
            <x-badge-status status="info">Pengaturan Akun</x-badge-status>
            <h1 class="font-display mt-4 text-3xl font-700 text-ink md:text-4xl">Akun Pelaku Usaha</h1>
            <p class="mt-3 max-w-2xl leading-8 text-on-surface-variant">NIB dan nama akun mengikuti data PIRT. Dari halaman ini Anda hanya dapat memperbarui password.</p>
        </div>
    </div>
</section>

<section class="mx-auto max-w-container px-4 py-8 md:px-6">
    <div class="mb-6">
        <a href="{{ route('user.dashboard') }}" class="inline-flex items-center gap-1.5 text-sm font-600 text-on-surface-variant transition-colors hover:text-primary">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Kembali ke Dashboard
        </a>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="overflow-hidden rounded-3xl border border-outline-variant bg-white shadow-soft">
            <div class="flex items-center gap-2 border-b border-outline-variant px-6 py-5">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary-soft text-primary">
                    <span class="material-symbols-outlined text-[20px]">badge</span>
                </span>
                <h2 class="font-display text-xl font-700 text-ink">Identitas Login</h2>
            </div>

            <div class="space-y-5 p-6">
                <div>
                    <label class="mb-1.5 block text-sm font-600 text-on-surface">NIB Login</label>
                    <div class="flex items-center gap-2 rounded-xl border border-outline-variant bg-surface-container-low px-3.5 py-3 text-sm text-on-surface-variant">
                        <span class="material-symbols-outlined text-[18px]">badge</span>
                        {{ $user->nib ?? '-' }}
                        <span class="ml-auto rounded-full bg-surface-container px-2 py-0.5 text-[10px] font-600">Tidak dapat diubah</span>
                    </div>
                    <p class="mt-1 text-xs text-on-surface-variant">Pelaku usaha masuk ke SIPAMAN memakai NIB.</p>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-600 text-on-surface">Nama Pelaku Usaha</label>
                    <div class="flex items-center gap-2 rounded-xl border border-outline-variant bg-surface-container-low px-3.5 py-3 text-sm text-on-surface-variant">
                        <span class="material-symbols-outlined text-[18px]">person</span>
                        {{ $user->nama }}
                    </div>
                    <p class="mt-1 text-xs text-on-surface-variant">Nama mengikuti data PIRT dan perubahan dilakukan oleh admin bila diperlukan.</p>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-3xl border border-outline-variant bg-white shadow-soft">
            <div class="flex items-center gap-2 border-b border-outline-variant px-6 py-5">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-secondary-soft text-secondary">
                    <span class="material-symbols-outlined text-[20px]">lock</span>
                </span>
                <h2 class="font-display text-xl font-700 text-ink">Ubah Password</h2>
            </div>

            <div class="space-y-5 p-6">
                @if (session('success_password'))
                    <x-alert type="success">{{ session('success_password') }}</x-alert>
                @endif

                <form method="POST" action="{{ route('user.account.update-password') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="password_lama" class="mb-1.5 block text-sm font-600 text-on-surface">Password Lama</label>
                        <input type="password" id="password_lama" name="password_lama" autocomplete="current-password" class="form-input-sipaman w-full @error('password_lama') border-red-400 @enderror">
                        @error('password_lama')<p class="mt-1.5 text-xs font-600 text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="password_baru" class="mb-1.5 block text-sm font-600 text-on-surface">Password Baru</label>
                        <input type="password" id="password_baru" name="password_baru" autocomplete="new-password" class="form-input-sipaman w-full @error('password_baru') border-red-400 @enderror" placeholder="Minimal 8 karakter">
                        @error('password_baru')<p class="mt-1.5 text-xs font-600 text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="password_baru_confirmation" class="mb-1.5 block text-sm font-600 text-on-surface">Konfirmasi Password Baru</label>
                        <input type="password" id="password_baru_confirmation" name="password_baru_confirmation" autocomplete="new-password" class="form-input-sipaman w-full">
                    </div>

                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-5 py-2.5 text-sm font-600 text-white transition-colors hover:bg-primary-container">
                        <span class="material-symbols-outlined text-[18px]">key</span>
                        Perbarui Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
