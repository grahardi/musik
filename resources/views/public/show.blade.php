@extends('layouts.public')

@section('title', "{$song->title} - {$song->artist} Chord")
@section('meta_description', "Chord gitar {$song->title} oleh {$song->artist}, lengkap kunci dasar, transpose otomatis, dan autoscroll.")

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0">{{ $song->title }}</h4>
            <p class="text-muted mb-0">{{ $song->artist }}
                @if ($song->original_key) &middot; Kunci Dasar: {{ $song->original_key }} @endif
                @if ($song->capo) &middot; Capo: {{ $song->capo }} @endif
                @if ($song->genre) &middot; <a href="{{ route('songs.by-genre', $song->genre) }}">{{ $song->genre->name }}</a> @endif
            </p>
        </div>
    </div>

    {{-- Toolbar transpose + autoscroll, sticky biar tetap kelihatan saat scroll --}}
    <div class="card mb-3 sticky-top no-print" style="top: 0.5rem; z-index: 10;">
        <div class="card-body py-2 d-flex flex-wrap gap-3 align-items-center">
            <div class="d-flex align-items-center gap-2">
                <span class="small text-muted">Transpose:</span>
                <button id="btn-transpose-down" class="btn btn-sm btn-outline-secondary">-</button>
                <span id="transpose-label" class="fw-bold" style="min-width:60px; text-align:center;">Original</span>
                <button id="btn-transpose-up" class="btn btn-sm btn-outline-secondary">+</button>
                <button id="btn-transpose-reset" class="btn btn-sm btn-link">Reset</button>
            </div>

            <div class="vr d-none d-md-block"></div>

            <div class="d-flex align-items-center gap-2">
                <button id="btn-scroll-toggle" class="btn btn-sm btn-success" data-state="paused">▶ Autoscroll</button>
                <span class="small text-muted">Speed:</span>
                <input type="range" id="scroll-speed" min="0.5" max="20" step="0.5" value="3" style="width:100px;">
            </div>

            <div class="vr d-none d-md-block"></div>

            <button id="btn-print" type="button" class="btn btn-sm btn-outline-dark">🖨 Cetak</button>
        </div>
    </div>

    <pre class="chord-view">{!! $song->renderedChordHtml() !!}</pre>

    @if ($song->source_url)
        <p class="text-muted small mt-3 no-print">
            Referensi awal diambil dari <a href="{{ $song->source_url }}" target="_blank" rel="noopener nofollow">sumber asli</a>, disunting ulang untuk situs ini.
        </p>
    @endif

    @if ($related->isNotEmpty())
        <hr class="my-4 no-print">
        <div class="no-print">
        <h6 class="mb-3">Chord Lainnya {{ $related->contains('artist', $song->artist) ? "dari {$song->artist}" : 'yang Mungkin Kamu Suka' }}</h6>
        <div class="row row-cols-1 row-cols-md-2 g-2">
            @foreach ($related as $item)
                <div class="col">
                    <a href="{{ route('songs.show', $item) }}" class="text-decoration-none text-dark">
                        <div class="card h-100">
                            <div class="card-body py-2">
                                <div class="fw-semibold">{{ $item->title }}</div>
                                <div class="text-muted small">{{ $item->artist }}</div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
        </div>
    @endif
@endsection

@section('scripts')
    <script src="{{ asset('js/chord-tools.js') }}"></script>
    <script>
        document.getElementById('btn-print')?.addEventListener('click', function () {
            window.print();
        });
    </script>
@endsection
