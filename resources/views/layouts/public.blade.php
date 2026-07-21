<!DOCTYPE html>
<html lang="id" data-bs-theme="light" id="html-root">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Chord Musik Indonesia')</title>
    <meta name="description" content="@yield('meta_description', 'Kumpulan chord gitar lagu Indonesia, lengkap dengan transpose dan autoscroll.')">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        // Set tema SEBELUM halaman kelihatan, biar tidak ada "flash" putih->gelap
        (function () {
            const saved = localStorage.getItem('theme');
            const theme = saved || 'light';
            document.getElementById('html-root').setAttribute('data-bs-theme', theme);
        })();
    </script>
    <style>
        body { background: var(--bs-body-bg); }
        .letter-badge { min-width:42px; text-align:center; }
        .letter-badge.disabled { opacity:.35; pointer-events:none; }

        pre.chord-view {
            white-space: pre-wrap;
            font-size: 1rem;
            line-height: 1.9;
            background: var(--bs-tertiary-bg);
            color: var(--bs-body-color);
            padding: 1.25rem;
            border-radius: .5rem;
        }
        .chord-token { color:#0a2472; font-weight:700; }
        [data-bs-theme="dark"] .chord-token { color:#7aa7ff; }
        .chord-view.lyrics-only .chord-token { display: none; }

        @media print {
            .navbar, footer, .no-print { display: none !important; }
            body, .container { background: #fff !important; color: #000 !important; }
            pre.chord-view {
                background: #fff !important;
                color: #000 !important;
                border: none;
                padding: 0;
                font-size: 12pt;
            }
            .chord-token { color: #000 !important; font-weight: 700; }
            a[href]::after { content: ""; } /* jangan cetak URL link */
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">🎸 Chord Indonesia</a>
            <div class="d-flex align-items-center">
                <form action="{{ route('songs.search') }}" method="GET" class="d-flex me-2" role="search">
                    <input class="form-control form-control-sm me-2" type="search" name="q" value="{{ request('q') }}" placeholder="Cari judul / penyanyi...">
                    <button class="btn btn-sm btn-outline-light">Cari</button>
                </form>
                <button id="theme-toggle" type="button" class="btn btn-sm btn-outline-light" title="Ganti tema">
                    <span id="theme-icon">🌙</span>
                </button>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        @yield('content')
    </div>

    <footer class="text-center text-muted small py-4">
        &copy; {{ date('Y') }} Chord Indonesia
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            const root = document.getElementById('html-root');
            const icon = document.getElementById('theme-icon');

            function applyIcon() {
                icon.textContent = root.getAttribute('data-bs-theme') === 'dark' ? '☀️' : '🌙';
            }
            applyIcon();

            document.getElementById('theme-toggle').addEventListener('click', function () {
                const next = root.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
                root.setAttribute('data-bs-theme', next);
                localStorage.setItem('theme', next);
                applyIcon();
            });
        })();
    </script>
    @yield('scripts')
</body>
</html>
