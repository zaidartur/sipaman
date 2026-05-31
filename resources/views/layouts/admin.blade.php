<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php($siteName = ($siteSettings['site_name'] ?? null) ?: 'SIPAMAN')
    <title>@hasSection('title') @yield('title') | Admin {{ $siteName }} @else Admin {{ $siteName }} @endif</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0d9488',
                        'primary-container': '#0f766e',
                        'primary-soft': '#d6f5f0',
                        secondary: '#f97316',
                        'secondary-soft': '#ffe9d5',
                        accent: '#fbbf24',
                        surface: '#f3faf8',
                        'surface-container': '#e6f4f1',
                        'surface-container-low': '#edf8f6',
                        'surface-variant': '#dcefeb',
                        'on-surface': '#16302c',
                        'on-surface-variant': '#4c635e',
                        outline: '#8aa6a0',
                        'outline-variant': '#c9e0db',
                        ink: '#16302c',
                    },
                    fontFamily: {
                        display: ['Sora', 'sans-serif'],
                        body: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    borderRadius: {
                        xl: '0.95rem',
                        '2xl': '1.3rem',
                        '3xl': '1.75rem',
                    },
                    boxShadow: {
                        'soft': '0 2px 4px rgba(13,148,136,0.05), 0 12px 28px -14px rgba(13,148,136,0.22)',
                        'lift': '0 6px 12px rgba(13,148,136,0.08), 0 28px 56px -22px rgba(13,148,136,0.35)',
                    },
                },
            },
        };
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        h1, h2, h3, h4, h5, h6, .font-display { font-family: 'Sora', sans-serif; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        .eyebrow { font-family: 'Plus Jakarta Sans', sans-serif; letter-spacing: 0.16em; text-transform: uppercase; }

        /* ===== Remap legacy utilities so existing CRUD pages adopt the fresh theme ===== */
        /* surfaces */
        .bg-white        { background-color: #ffffff !important; }
        .bg-slate-50     { background-color: #edf8f6 !important; }
        .bg-slate-100    { background-color: #e6f4f1 !important; }
        .file\:bg-slate-100::file-selector-button { background-color: #d6f5f0 !important; }
        .hover\:file\:bg-slate-200:hover::file-selector-button { background-color: #b9ebe3 !important; }
        /* primary action surfaces (slate-900 / blue-700 buttons -> teal) */
        .bg-slate-900    { background-color: #0d9488 !important; }
        .hover\:bg-slate-800:hover { background-color: #0f766e !important; }
        .hover\:bg-slate-700:hover { background-color: #0f766e !important; }
        .bg-blue-700     { background-color: #0d9488 !important; }
        .hover\:bg-blue-800:hover { background-color: #0f766e !important; }
        .bg-blue-50      { background-color: #d6f5f0 !important; }
        /* borders -> soft mint outline */
        .border-slate-200 { border-color: #c9e0db !important; }
        .border-slate-300 { border-color: #bcd8d2 !important; }
        .border-slate-100 { border-color: #dcefeb !important; }
        .border-blue-100  { border-color: #c9e0db !important; }
        /* text */
        .text-slate-500  { color: #4c635e !important; }
        .text-slate-600  { color: #3f5651 !important; }
        .text-slate-700  { color: #324a45 !important; }
        .text-slate-800  { color: #243b37 !important; }
        .text-slate-900  { color: #16302c !important; }
        .text-blue-700   { color: #0d9488 !important; }
        .text-blue-800   { color: #0f766e !important; }
        .hover\:text-blue-900:hover { color: #0f766e !important; }
        /* focus rings -> teal */
        .focus\:border-slate-900:focus { border-color: #0d9488 !important; }
        .focus\:ring-slate-900:focus  { --tw-ring-color: #0d9488 !important; }
        .text-slate-900.rounded,
        .text-slate-900[type=checkbox] { color: #0d9488 !important; }
        /* alert / accent tints stay close but warmer */
        .bg-emerald-50   { background-color: #d6f5ec !important; }
        .text-emerald-700{ color: #047857 !important; }
        .text-amber-700  { color: #b45309 !important; }
        .bg-amber-50     { background-color: #fef3da !important; }

        /* soften every card: legacy rounded-xl + shadow-sm gets the new soft shadow */
        .shadow-sm { box-shadow: 0 2px 4px rgba(13,148,136,0.05), 0 12px 28px -14px rgba(13,148,136,0.22) !important; }

        @keyframes rise { from { opacity:0; transform:translateY(14px);} to {opacity:1; transform:translateY(0);} }
        .rise { animation: rise .6s cubic-bezier(.16,1,.3,1) both; }

        .scrollbar-none { scrollbar-width: none; -ms-overflow-style: none; }
        .scrollbar-none::-webkit-scrollbar { display: none; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-auto-resize]').forEach((textarea) => {
                const resize = () => {
                    textarea.style.height = 'auto';
                    textarea.style.height = `${textarea.scrollHeight}px`;
                };

                textarea.classList.add('scrollbar-none');
                textarea.style.overflow = 'hidden';
                resize();
                textarea.addEventListener('input', resize);
            });

            const sidebar = document.getElementById('admin-sidebar-scroll');
            if (sidebar) {
                const storageKey = 'sipaman.admin.sidebar.scrollTop';
                const savedPosition = Number.parseInt(localStorage.getItem(storageKey) || '', 10);

                if (! Number.isNaN(savedPosition)) {
                    sidebar.scrollTop = savedPosition;
                }

                const activeItem = sidebar.querySelector('[data-sidebar-active="true"]');
                if (activeItem) {
                    activeItem.scrollIntoView({ block: 'nearest' });
                }

                const savePosition = () => localStorage.setItem(storageKey, String(sidebar.scrollTop));
                sidebar.addEventListener('click', savePosition);
                window.addEventListener('beforeunload', savePosition);
            }
        });
    </script>
</head>
<body class="min-h-screen bg-surface text-on-surface antialiased">
    <div class="min-h-screen lg:grid lg:grid-cols-[280px_1fr]">
        @include('partials.admin.sidebar')

        <div class="min-w-0">
            @include('partials.admin.topbar')

            <main class="rise px-4 py-6 md:px-8">
                @include('partials.admin.breadcrumb')
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
