<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php($siteName = ($siteSettings['site_name'] ?? null) ?: 'SIPAMAN')

    <title>
        @hasSection('title')
            @yield('title') | Panel Pengelola {{ $siteName }}
        @else
            Panel Pengelola {{ $siteName }}
        @endif
    </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet"
        nonce="{{ app(\Spatie\Csp\Nonce\NonceGenerator::class)->generate() }}"
    >
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet"
        nonce="{{ app(\Spatie\Csp\Nonce\NonceGenerator::class)->generate() }}"
    >

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="admin-shell min-h-screen bg-surface text-on-surface font-body antialiased">
    <div class="min-h-screen lg:grid lg:grid-cols-[280px_1fr]">
        @include('partials.admin.sidebar')

        <div class="min-w-0">
            @include('partials.admin.topbar')

            <main class="px-4 py-6 md:px-8">
                @include('partials.admin.breadcrumb')
                @yield('content')
            </main>
        </div>
    </div>

    <div id="panel-loading-overlay" class="panel-loading-overlay" aria-live="polite" aria-hidden="true">
        <div class="panel-loading-box">
            <div class="mx-auto h-10 w-10 animate-spin rounded-full border-4 border-slate-200 border-t-primary"></div>
            <p id="panel-loading-message" class="mt-4 font-semibold text-slate-900">Memproses data...</p>
            <p class="mt-1 text-sm text-slate-500">Mohon tunggu sampai proses selesai.</p>
        </div>
    </div>
</body>
</html>
