@php
    $settings = $siteSettings ?? [];
    $siteName = ($settings['site_name'] ?? null) ?: 'SIPAMAN';
    $siteTagline = ($settings['site_tagline'] ?? null) ?: 'Sistem Informasi Pangan Aman';
    $logoPath = ($settings['site_logo_path'] ?? null) ?: ($settings['logo_path'] ?? null);
    $logoUrl = $logoPath ? \Illuminate\Support\Facades\Storage::disk('public')->url($logoPath) : null;
    $homeLabel = ($settings['nav_home_label'] ?? null) ?: 'Home';
    $productsLabel = ($settings['nav_products_label'] ?? null) ?: 'Produk';
    $umkmLabel = ($settings['nav_umkm_label'] ?? null) ?: 'UMKM';
    $dashboardRoute = null;
    $accountIdentifier = null;

    if (auth()->check()) {
        $dashboardRoute = auth()->user()->hasRole('user') ? 'user.dashboard' : 'admin.dashboard';
        $accountIdentifier = auth()->user()->hasRole('user')
            ? 'NIB ' . (auth()->user()->nib ?? '-')
            : (auth()->user()->email ?? '-');
    }

    $links = [
        ['label' => $homeLabel, 'route' => 'home', 'active' => request()->routeIs('home')],
        ['label' => $productsLabel, 'route' => 'products.index', 'active' => request()->routeIs('products.*')],
        ['label' => $umkmLabel, 'route' => 'umkm.index', 'active' => request()->routeIs('umkm.*')],
    ];
@endphp

<header class="sticky top-0 z-50 border-b border-outline-variant/70 bg-white/85 backdrop-blur-md">
    <div class="mx-auto max-w-container px-4 md:px-6">
        <div class="flex h-20 items-center justify-between">
            <a href="{{ route('home') }}" class="group flex items-center gap-3">
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="h-11 w-11 rounded-xl object-cover shadow-soft">
                @else
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary text-surface shadow-soft transition-transform group-hover:-rotate-6">
                        <span class="material-symbols-outlined text-[22px]">verified_user</span>
                    </span>
                @endif
                <span class="leading-tight">
                    <span class="block font-display text-lg font-700 text-primary">{{ $siteName }}</span>
                    <span class="eyebrow block text-[10px] font-600 text-on-surface-variant">{{ $siteTagline }}</span>
                </span>
            </a>

            <nav class="hidden items-center gap-1 md:flex" aria-label="Navigasi utama">
                @foreach ($links as $link)
                    <a
                        href="{{ route($link['route']) }}"
                        class="rounded-full px-4 py-2 text-sm font-600 transition-colors {{ $link['active'] ? 'bg-primary-soft text-primary' : 'text-on-surface-variant hover:bg-surface-container hover:text-primary' }}"
                        @if ($link['active']) aria-current="page" @endif
                    >
                        {{ $link['label'] }}
                    </a>
                @endforeach

                @auth
                    <div class="relative ml-2" data-account-menu>
                        <button
                            type="button"
                            aria-label="Menu akun"
                            aria-expanded="false"
                            class="flex h-11 w-11 items-center justify-center rounded-full border border-outline-variant text-primary transition-colors hover:bg-primary hover:text-surface {{ request()->routeIs('user.*') || request()->routeIs('admin.*') ? 'bg-primary-soft' : '' }}"
                            onclick="this.closest('[data-account-menu]').classList.toggle('is-open'); this.setAttribute('aria-expanded', this.closest('[data-account-menu]').classList.contains('is-open'))"
                        >
                            <span class="material-symbols-outlined text-[24px]">person</span>
                        </button>

                        <div class="account-dropdown absolute right-0 mt-2 w-56 overflow-hidden rounded-2xl border border-outline-variant bg-white shadow-lift">
                            <div class="border-b border-outline-variant px-4 py-3">
                                <p class="truncate text-sm font-700 text-ink">{{ auth()->user()->nama }}</p>
                                <p class="truncate text-xs text-on-surface-variant">{{ $accountIdentifier }}</p>
                            </div>
                            <a
                                href="{{ route($dashboardRoute) }}"
                                class="flex items-center gap-2.5 px-4 py-2.5 text-sm font-600 text-on-surface transition-colors hover:bg-primary-soft hover:text-primary"
                            >
                                <span class="material-symbols-outlined text-[20px]">dashboard</span>
                                Dashboard
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="flex w-full items-center gap-2.5 px-4 py-2.5 text-left text-sm font-600 text-red-600 transition-colors hover:bg-red-50"
                                >
                                    <span class="material-symbols-outlined text-[20px]">logout</span>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a
                        href="{{ route('login') }}"
                        class="ml-2 inline-flex items-center gap-1.5 rounded-full bg-primary px-5 py-2 text-sm font-600 text-surface shadow-soft transition-colors hover:bg-primary-container"
                    >
                        <span class="material-symbols-outlined text-[18px]">login</span>
                        Login
                    </a>
                @endauth
            </nav>

            <button
                type="button"
                class="rounded-xl p-2 text-primary transition-colors hover:bg-surface-container md:hidden"
                aria-controls="mobile-navigation"
                aria-expanded="false"
                onclick="document.getElementById('mobile-navigation').classList.toggle('hidden')"
            >
                <span class="material-symbols-outlined">menu</span>
            </button>
        </div>

        <nav id="mobile-navigation" class="hidden pb-4 md:hidden" aria-label="Navigasi mobile">
            <div class="space-y-1 rounded-2xl border border-outline-variant/70 bg-surface-container-low p-2">
                @foreach ($links as $link)
                    <a
                        href="{{ route($link['route']) }}"
                        class="block rounded-xl px-4 py-2.5 text-sm font-600 {{ $link['active'] ? 'bg-primary text-surface' : 'text-on-surface-variant hover:bg-surface-container hover:text-primary' }}"
                        @if ($link['active']) aria-current="page" @endif
                    >
                        {{ $link['label'] }}
                    </a>
                @endforeach

                @auth
                    <div class="mt-1 flex items-center gap-2.5 rounded-xl bg-surface-container px-4 py-2.5">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary text-surface">
                            <span class="material-symbols-outlined text-[20px]">person</span>
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-700 text-ink">{{ auth()->user()->nama }}</span>
                            <span class="block truncate text-xs text-on-surface-variant">{{ $accountIdentifier }}</span>
                        </span>
                    </div>
                    <a
                        href="{{ route($dashboardRoute) }}"
                        class="flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-600 {{ request()->routeIs('user.dashboard') || request()->routeIs('admin.*') ? 'bg-primary text-surface' : 'text-on-surface-variant hover:bg-surface-container hover:text-primary' }}"
                    >
                        <span class="material-symbols-outlined text-[20px]">dashboard</span>
                        Dashboard
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="flex w-full items-center gap-2 rounded-xl px-4 py-2.5 text-left text-sm font-600 text-red-600 hover:bg-red-50" type="submit">
                            <span class="material-symbols-outlined text-[20px]">logout</span>
                            Logout
                        </button>
                    </form>
                @else
                    <a
                        href="{{ route('login') }}"
                        class="block rounded-xl bg-primary px-4 py-2.5 text-sm font-600 text-surface"
                        @if (request()->routeIs('login')) aria-current="page" @endif
                    >
                        Login
                    </a>
                @endauth
            </div>
        </nav>
    </div>
</header>

<style>
    [data-account-menu] .account-dropdown {
        opacity: 0;
        visibility: hidden;
        transform: translateY(-6px);
        transition: opacity .15s ease, transform .15s ease, visibility .15s;
    }
    [data-account-menu].is-open .account-dropdown {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
</style>
<script>
    document.addEventListener('click', function (e) {
        document.querySelectorAll('[data-account-menu].is-open').forEach(function (menu) {
            if (!menu.contains(e.target)) {
                menu.classList.remove('is-open');
                var btn = menu.querySelector('button[aria-label="Menu akun"]');
                if (btn) btn.setAttribute('aria-expanded', 'false');
            }
        });
    });
</script>
