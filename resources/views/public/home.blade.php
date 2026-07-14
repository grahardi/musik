@extends('layouts.public')

@section('title', 'Chord Musik Indonesia - Kunci Gitar Lengkap')

@section('content')
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Cari berdasarkan huruf awal</h5>
                <div class="btn-group btn-group-sm">
                    <a href="{{ route('home', ['by' => 'title']) }}" class="btn btn-{{ $mode === 'title' ? 'primary' : 'outline-primary' }}">Judul Lagu</a>
                    <a href="{{ route('home', ['by' => 'artist']) }}" class="btn btn-{{ $mode === 'artist' ? 'primary' : 'outline-primary' }}">Penyanyi</a>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                @foreach ($alphabet as $letter)
                    @php $total = $counts[$letter] ?? 0; @endphp
                    <a href="{{ route('songs.by-letter', ['letter' => $letter, 'by' => $mode]) }}"
                       class="btn btn-sm btn-outline-secondary letter-badge {{ $total === 0 ? 'disabled' : '' }}">
                        {{ $letter }}
                        @if ($total) <span class="badge bg-secondary">{{ $total }}</span> @endif
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <h5 class="mb-3">Chord Terbaru</h5>
    <div class="row row-cols-1 row-cols-md-3 g-3">
        @forelse ($latest as $song)
            <div class="col">
                <a href="{{ route('songs.show', $song) }}" class="text-decoration-none text-dark">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="card-title mb-1">{{ $song->title }}</h6>
                            <p class="card-text text-muted small mb-0">{{ $song->artist }}</p>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <p class="text-muted">Belum ada chord yang dipublish.</p>
        @endforelse
    </div>
@endsection
