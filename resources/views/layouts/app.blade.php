<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'PT Janur Tangguh Abadi') | Freight Forwarding</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        :root {
            --navy: #0B2545;
            --navy-dark: #071a33;
            --steel: #5C6B73;
            --accent: #E8871E;
            --accent-dark: #c66f12;
            --bg-soft: #F5F7F9;
        }
        body {
            font-family: 'Inter', sans-serif;
            color: #1E2A32;
            background: #fff;
        }
        h1, h2, h3, h4, .brand-font {
            font-family: 'Poppins', sans-serif;
        }
        .navbar-janur {
            background: var(--navy);
        }
        .navbar-janur .nav-link {
            color: #C9D4DD;
            font-weight: 500;
        }
        .navbar-janur .nav-link:hover,
        .navbar-janur .nav-link.active {
            color: #fff;
        }
        .navbar-janur .navbar-brand {
            color: #fff;
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            letter-spacing: 0.3px;
        }
        .btn-accent {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
            font-weight: 600;
        }
        .btn-accent:hover {
            background: var(--accent-dark);
            border-color: var(--accent-dark);
            color: #fff;
        }
        .btn-outline-accent {
            border: 1.5px solid var(--accent);
            color: var(--accent);
            font-weight: 600;
        }
        .btn-outline-accent:hover {
            background: var(--accent);
            color: #fff;
        }
        .section-soft {
            background: var(--bg-soft);
        }
        .text-navy { color: var(--navy); }
        .text-steel { color: var(--steel); }

        footer.site-footer {
            background: var(--navy-dark);
            color: #B9C4CC;
        }
        footer.site-footer a {
            color: #DCE3E8;
        }
        footer.site-footer a:hover {
            color: var(--accent);
        }
    </style>

    @stack('styles')
</head>
<body>

    @include('partials.navbar')

    <main>
        @if (session('success'))
            <div class="container mt-3">
                <div class="alert alert-success">{{ session('success') }}</div>
            </div>
        @endif
        @if (session('error'))
            <div class="container mt-3">
                <div class="alert alert-danger">{{ session('error') }}</div>
            </div>
        @endif

        @yield('content')
    </main>

    @include('partials.footer')

    @stack('scripts')
</body>
</html>
