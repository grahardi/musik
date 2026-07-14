<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Chord Musik Indonesia')</title>
    <meta name="description" content="@yield('meta_description', 'Kumpulan chord gitar lagu Indonesia, lengkap dengan transpose dan autoscroll.')">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#f7f7f9; }
        .letter-badge { min-width:42px; text-align:center; }
        .letter-badge.disabled { opacity:.35; pointer-events:none; }
        pre.chord-view { white-space: pre-wrap; font-size: 1rem; line-height: 1.9; background:#fff; padding:1.25rem; border-radius:.5rem; }
        .chord-token { color:#0d6efd; font-weight:700; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">🎸 Chord Indonesia</a>
            <form action="{{ route('songs.search') }}" method="GET" class="d-flex ms-auto" role="search">
                <input class="form-control form-control-sm me-2" type="search" name="q" value="{{ request('q') }}" placeholder="Cari judul / penyanyi...">
                <button class="btn btn-sm btn-outline-light">Cari</button>
            </form>
        </div>
    </nav>

    <div class="container pb-5">
        @yield('content')
    </div>

    <footer class="text-center text-muted small py-4">
        &copy; {{ date('Y') }} Chord Indonesia
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
