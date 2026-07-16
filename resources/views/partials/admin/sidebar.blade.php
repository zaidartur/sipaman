@php
    $role = auth()->user()?->role?->nama_role;

    $groups = [
        'Utama' => [
            ['label' => 'Dashboard', 'icon' => 'dashboard', 'route' => 'panel.dashboard', 'active' => 'panel.dashboard*'],
        ],
        'Data PIRT' => [
            ['label' => 'Produk', 'icon' => 'inventory_2', 'route' => 'panel.products.index', 'active' => 'panel.products.*'],
            ['label' => 'Gambar Produk', 'icon' => 'image', 'route' => 'panel.product-images.index', 'active' => 'panel.product-images.*'],
            ['label' => 'Jenis Barang', 'icon' => 'category', 'route' => 'panel.jenis-barang.index', 'active' => 'panel.jenis-barang.*'],
            ['label' => 'Verifikasi', 'icon' => 'verified', 'route' => 'panel.verifications.index', 'active' => 'panel.verifications.*'],
        ],
        'Pengguna' => [
            ['label' => 'Kelola Pelaku Usaha', 'icon' => 'badge', 'route' => 'panel.pelaku-usaha.index', 'active' => 'panel.pelaku-usaha.*'],
        ],
        'Konten Website' => [
            ['label' => 'Landing Page', 'icon' => 'web', 'route' => 'panel.landing-page.index', 'active' => 'panel.landing-page.*'],
        ],
        'Monitoring' => [
            ['label' => 'Log Aktivitas', 'icon' => 'history', 'route' => 'panel.logs.index', 'active' => 'panel.logs.*'],
            ['label' => 'Riwayat Import', 'icon' => 'upload_file', 'route' => 'panel.import-logs.index', 'active' => 'panel.import-logs.*'],
        ],
    ];

    if ($role === 'super_admin') {
        $groups['Administrasi Sistem'] = [
            ['label' => 'Kelola Admin', 'icon' => 'group', 'route' => 'super-admin.users.index', 'active' => 'super-admin.users.*'],
            ['label' => 'Pengaturan Sistem', 'icon' => 'settings', 'route' => 'super-admin.settings.index', 'active' => 'super-admin.settings.*'],
            ['label' => 'Audit Trail', 'icon' => 'manage_search', 'route' => 'super-admin.audit-trails.index', 'active' => 'super-admin.audit-trails.*'],
        ];
    }
@endphp

<aside class="hidden border-r border-outline-variant/70 bg-primary text-surface lg:block">
    <div class="sticky top-0 flex h-screen flex-col">
        <div class="border-b border-surface/10 px-6 py-6">
            <a href="{{ route('panel.dashboard') }}" class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-accent/20 text-accent">
                    <span class="material-symbols-outlined text-[20px]">verified_user</span>
                </span>
                <span class="leading-tight">
                    <span class="block font-display text-lg font-600">SIPAMAN</span>
                    <span class="eyebrow block text-[9px] font-600 text-surface/55">Sistem Informasi Pangan Aman</span>
                </span>
            </a>
        </div>

        <nav
            id="admin-sidebar-scroll"
            class="scrollbar-none flex-1 space-y-5 overflow-y-auto px-4 py-5"
            style="visibility: hidden;"
            aria-label="Navigasi panel"
        >
            @foreach($groups as $groupLabel => $items)
                <div>
                    <p class="px-3.5 pb-2 text-[10px] font-700 uppercase tracking-[0.16em] text-surface/45">
                        {{ $groupLabel }}
                    </p>

                    <div class="space-y-1">
                        @foreach($items as $item)
                            @php($active = request()->routeIs($item['active']))

                            <a
                                href="{{ route($item['route']) }}"
                                @if($active) aria-current="page" @endif
                                data-sidebar-active="{{ $active ? 'true' : 'false' }}"
                                class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-600 transition-colors {{ $active ? 'bg-accent text-primary shadow-soft' : 'text-surface/70 hover:bg-surface/10 hover:text-surface' }}"
                            >
                                <span class="material-symbols-outlined text-[20px]">{{ $item['icon'] }}</span>
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </nav>

        <noscript>
            <style nonce="{{ app(\Spatie\Csp\Nonce\NonceGenerator::class)->generate() }}">
                #admin-sidebar-scroll {
                    visibility: visible !important;
                }
            </style>
        </noscript>

        <div class="border-t border-surface/10 px-4 py-4">
            <div class="flex items-center gap-3 rounded-xl bg-surface/10 px-3.5 py-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-accent/25 text-sm font-700 text-accent">
                    {{ strtoupper(substr(auth()->user()?->nama ?? 'A', 0, 1)) }}
                </span>

                <div class="min-w-0">
                    <p class="truncate text-sm font-600 text-surface">
                        {{ auth()->user()?->nama ?? 'Pengelola' }}
                    </p>
                    <p class="eyebrow truncate text-[9px] font-600 text-surface/55">
                        {{ str_replace('_', ' ', $role ?? 'admin') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</aside>
