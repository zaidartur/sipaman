@php
    $hero = $contents->get('hero');
    $showHero = ! $hero || $hero->is_active;
    $heroTitle = $hero?->judul ?: 'SIPAMAN';
    $heroSubtitle = $hero?->subjudul ?: 'Sistem Informasi Pangan Aman';
    $heroContent = $hero?->konten ?: 'Temukan produk PIRT, pelaku usaha, dan potensi UMKM pangan aman dari Karanganyar dalam satu katalog yang mudah dicari.';
    $heroImage = $hero?->image_url ?: 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?auto=format&fit=crop&w=1200&q=80';
    $heroImageAlt = $hero?->image_alt ?: 'Produk pangan aman Karanganyar';
    $heroButtonText = $hero ? $hero->button_text : 'Lihat Produk';
    $heroButtonUrl = $hero ? $hero->button_url : route('products.index');
    $heroSecondaryButtonText = $hero ? $hero->secondary_button_text : 'Lihat UMKM';
    $heroSecondaryButtonUrl = $hero ? $hero->secondary_button_url : route('umkm.index');
@endphp

@if ($showHero)
    <section class="relative overflow-hidden bg-surface-container-low pb-14 md:pb-20">
        <div class="relative mx-auto max-w-container px-4 pb-20 pt-16 md:px-6 md:pb-24 md:pt-24">
            <div class="grid items-center gap-10 md:grid-cols-[1.05fr_0.95fr]">
                <div class="max-w-2xl">
                    <span class="rise inline-flex items-center gap-2 rounded-full border border-primary/20 bg-white px-4 py-1.5 text-xs font-600 text-primary shadow-soft">
                        <span class="material-symbols-outlined text-[16px]">verified_user</span>
                        <span class="eyebrow text-[10px]">{{ $heroSubtitle }}</span>
                    </span>
                    <h1 class="rise font-display mt-7 text-4xl font-700 leading-[1.07] text-ink md:text-6xl" style="animation-delay:.08s">
                        {{ $heroTitle }}
                    </h1>
                    <p class="rise mt-6 max-w-xl text-lg leading-8 text-on-surface-variant" style="animation-delay:.16s">
                        {{ $heroContent }}
                    </p>

                    <div class="rise mt-8 flex flex-wrap items-center gap-3" style="animation-delay:.2s">
                        @if ($heroButtonText && $heroButtonUrl)
                            <a href="{{ $heroButtonUrl }}" class="inline-flex items-center gap-2 rounded-full bg-primary px-5 py-3 font-600 text-white shadow-soft transition-colors hover:bg-primary-container">
                                {{ $heroButtonText }}
                                <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                            </a>
                        @endif
                        @if ($heroSecondaryButtonText && $heroSecondaryButtonUrl)
                            <a href="{{ $heroSecondaryButtonUrl }}" class="inline-flex items-center gap-2 rounded-full border border-outline-variant px-5 py-3 font-600 text-primary transition-colors hover:bg-white">
                                {{ $heroSecondaryButtonText }}
                                <span class="material-symbols-outlined text-[20px]">storefront</span>
                            </a>
                        @endif
                    </div>

                    <form action="{{ route('products.index') }}" method="GET" autocomplete="off" class="rise mt-8 grid max-w-2xl gap-2.5 rounded-3xl border border-outline-variant bg-white p-2.5 shadow-lift md:grid-cols-[1fr_200px_auto]" style="animation-delay:.24s">
                        <label class="flex items-center gap-2 rounded-2xl bg-surface-container-low px-3.5 transition focus-within:bg-primary-soft">
                            <span class="material-symbols-outlined text-primary">search</span>
                            <input class="w-full border-0 bg-transparent py-3.5 text-on-surface placeholder:text-on-surface-variant focus:outline-none focus:ring-0" name="search" value="{{ request('search') }}" placeholder="Cari nama produk..." type="text" autocomplete="off">
                        </label>
                        <label class="flex items-center gap-2 rounded-2xl bg-surface-container-low px-3.5 transition focus-within:bg-primary-soft">
                            <span class="material-symbols-outlined text-primary">location_on</span>
                            <select class="w-full rounded-xl border-0 bg-transparent py-3.5 text-on-surface transition focus:outline-none focus:ring-2 focus:ring-primary/25" name="kecamatan_id">
                                <option value="">Semua Kecamatan</option>
                                @foreach (($kecamatans ?? collect()) as $kecamatan)
                                    <option value="{{ $kecamatan->id }}" @selected((string) request('kecamatan_id') === (string) $kecamatan->id)>{{ $kecamatan->nama_kecamatan }}</option>
                                @endforeach
                            </select>
                        </label>
                        <button class="inline-flex items-center justify-center gap-1.5 rounded-2xl bg-primary px-6 py-3.5 font-600 text-white transition-colors hover:bg-primary-container" type="submit">
                            Cari
                            <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                        </button>
                    </form>
                </div>

                <div class="relative hidden md:block">
                    <div class="floaty overflow-hidden rounded-3xl border border-outline-variant bg-white shadow-lift">
                        <img class="aspect-[4/3] w-full object-cover" src="{{ $heroImage }}" alt="{{ $heroImageAlt }}">
                    </div>
                    <div class="absolute -bottom-5 -left-6 flex items-center gap-3 rounded-2xl border border-outline-variant bg-white p-3.5 shadow-lift">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-soft text-primary">
                            <span class="material-symbols-outlined">verified</span>
                        </span>
                        <div>
                            <p class="font-display text-sm font-700 text-ink">Produk Terverifikasi</p>
                            <p class="text-xs text-on-surface-variant">Lulus verifikasi pangan aman</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
