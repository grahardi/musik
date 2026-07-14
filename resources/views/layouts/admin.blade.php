<!DOCTYPE html>
<html lang="id" data-bs-theme="light" id="html-root">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Chord')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        (function () {
            const saved = localStorage.getItem('theme');
            document.getElementById('html-root').setAttribute('data-bs-theme', saved || 'light');
        })();
    </script>
</head>
<body class="bg-body">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="{{ route('admin.songs.index') }}">Admin Chord</a>
            <div>
                <button id="theme-toggle" type="button" class="btn btn-sm btn-outline-light me-1" title="Ganti tema">
                    <span id="theme-icon">🌙</span>
                </button>
                <a class="btn btn-sm btn-outline-light me-1" href="{{ route('admin.genres.index') }}">Genre</a>
                <a class="btn btn-sm btn-outline-light me-1" href="{{ route('admin.songs.create') }}">+ Tambah Chord</a>
                <form action="{{ route('admin.logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-sm btn-outline-danger">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            const root = document.getElementById('html-root');
            const icon = document.getElementById('theme-icon');
            function applyIcon() { icon.textContent = root.getAttribute('data-bs-theme') === 'dark' ? '☀️' : '🌙'; }
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
