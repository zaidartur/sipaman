<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php($siteName = ($siteSettings['site_name'] ?? null) ?: 'SIPAMAN')
    <title>@hasSection('title') @yield('title') | {{ $siteName }} @else Masuk ke {{ $siteName }} @endif</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                    maxWidth: { container: '1240px' },
                    borderRadius: { xl: '0.95rem', '2xl': '1.3rem', '3xl': '1.75rem' },
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
    </style>
</head>
<body class="min-h-screen bg-surface text-on-surface antialiased">
    @include('partials.public.navbar')

    <main>
        @yield('content')
    </main>
</body>
</html>
