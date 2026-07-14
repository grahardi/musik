@extends('layouts.public')

@section('title', "Chord huruf {$letter} - " . ($mode === 'artist' ? 'Penyanyi' : 'Judul Lagu'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">
            {{ $mode === 'artist' ? 'Penyanyi' : 'Judul Lagu' }} berawalan huruf "{{ $letter }}"
        </h5>
        <a href="{{ route('home', ['by' => $mode]) }}" class="btn btn-sm btn-outline-secondary">&larr; Kembali</a>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-4">
        @foreach ($alphabet as $l)
            <a href="{{ route('songs.by-letter', ['letter' => $l, 'by' => $mode]) }}"
               class="btn btn-sm {{ $l === $letter ? 'btn-primary' : 'btn-outline-secondary' }} letter-badge">
                {{ $l }}
            </a>
        @endforeach
    </div>

    <div class="list-group mb-4">
        @forelse ($songs as $song)
            <a href="{{ route('songs.show', $song) }}" class="list-group-item list-group-item-action d-flex justify-content-between">
                <span><strong>{{ $song->title }}</strong> — {{ $song->artist }}</span>
                <span class="text-muted small">{{ $song->original_key }}</span>
            </a>
        @empty
            <p class="text-muted">Belum ada chord untuk huruf ini.</p>
        @endforelse
    </div>

    {{ $songs->links() }}
@endsection
