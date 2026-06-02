@extends('layouts.auth')

@section('title', 'Masuk ke SIPAMAN')

@section('content')
    <section class="grid min-h-[calc(100vh-80px)] md:grid-cols-2">
        {{-- Brand panel --}}
        <div class="relative hidden bg-primary md:block">
            <img
                class="absolute inset-0 h-full w-full object-cover opacity-35"
                src="https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80"
                alt="SIPAMAN"
            >
            <div class="absolute inset-0 bg-gradient-to-t from-primary via-primary/75 to-primary/30"></div>
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,rgba(201,154,63,0.20),transparent_55%)]"></div>
            <div class="relative flex h-full flex-col justify-between p-12 text-surface">
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-accent/20 text-accent">
                        <span class="material-symbols-outlined text-[22px]">landscape</span>
                    </span>
                    <span class="font-display text-lg font-600">SIPAMAN</span>
                </div>
                <div>
                    <p class="eyebrow text-[11px] font-600 text-accent">Akses Resmi</p>
                    <h1 class="font-display mt-3 text-4xl font-600 leading-tight md:text-5xl">Masuk ke SIPAMAN</h1>
                    <p class="mt-5 max-w-lg leading-8 text-surface/80">
                        Sistem Informasi Pangan Aman untuk admin, super admin, dan pelaku usaha terdaftar.
                    </p>
                </div>
            </div>
        </div>

        {{-- Form panel --}}
        <div class="flex items-center justify-center px-4 py-12">
            <div class="w-full max-w-md rounded-3xl border border-outline-variant/70 bg-surface p-8 shadow-soft">
                <p class="eyebrow text-[11px] font-600 text-secondary">Selamat Datang</p>
                <h2 class="font-display mt-2 text-3xl font-600 text-primary">Masuk ke SIPAMAN</h2>
                <p class="mt-2 text-on-surface-variant">Masukkan kredensial yang diberikan admin SIPAMAN.</p>

                <x-alert type="info" class="mt-6">
                    Akun pelaku usaha dibuat oleh admin SIPAMAN.
                </x-alert>

                @if ($errors->any())
                    <x-alert type="danger" class="mt-4">
                        {{ $errors->first() }}
                    </x-alert>
                @endif

                <form class="mt-6 space-y-5" method="POST" action="{{ route('login.store') }}">
                    @csrf
                    <div>
                        <label class="text-sm font-600 text-on-surface" for="identifier">NIB / Email Admin</label>
                        <div class="mt-2 flex items-center gap-2 rounded-xl border border-outline-variant bg-surface-container-low px-3.5 transition focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/25">
                            <span class="material-symbols-outlined text-on-surface-variant">person</span>
                            <input class="w-full border-0 bg-transparent py-3 text-on-surface placeholder:text-on-surface-variant focus:outline-none focus:ring-0" id="identifier" name="identifier" type="text" value="{{ old('identifier') }}" placeholder="NIB pelaku usaha atau email admin" required autofocus autocomplete="username">
                        </div>
                        <p class="mt-1.5 text-xs text-on-surface-variant">Admin login dengan email, pelaku usaha dengan NIB.</p>
                    </div>

                    <div>
                        <label class="text-sm font-600 text-on-surface" for="password">Password</label>
                        <div class="mt-2 flex items-center gap-2 rounded-xl border border-outline-variant bg-surface-container-low px-3.5 transition focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/25">
                            <span class="material-symbols-outlined text-on-surface-variant">lock</span>
                            <input class="w-full border-0 bg-transparent py-3 text-on-surface focus:outline-none focus:ring-0" id="password" name="password" type="password" required autocomplete="current-password">
                        </div>
                    </div>

                    <label class="flex items-center gap-2 text-sm font-500 text-on-surface-variant">
                        <input class="form-checkbox-sipaman" name="remember" type="checkbox" value="1">
                        Ingat saya
                    </label>

                    <button class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-5 py-3.5 font-600 text-surface transition-colors hover:bg-primary-container" type="submit">
                        <span class="material-symbols-outlined text-[20px]">login</span>
                        Masuk
                    </button>
                </form>
            </div>
        </div>
    </section>
@endsection
