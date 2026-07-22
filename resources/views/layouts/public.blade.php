<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="author" content="Diskominfo Kabupaten Karanganyar">
    <meta name="description" content="Sistem Informasi Pangan Aman oleh Dinas Kesehatan Kabupaten Karanganyar">

    @php($siteName = ($siteSettings['site_name'] ?? null) ?: 'SIPAMAN')
    <title>@hasSection('title') @yield('title') | {{ $siteName }} @else {{ $siteName }} @endif</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" nonce="{{ app(\Spatie\Csp\Nonce\NonceGenerator::class)->generate() }}">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" nonce="{{ app(\Spatie\Csp\Nonce\NonceGenerator::class)->generate() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="public-shell min-h-screen bg-surface text-on-surface antialiased">
    @include('partials.public.navbar')

    <main>
        @yield('content')
    </main>

    @include('partials.public.footer')

    @stack('scripts')
</body>
</html>
