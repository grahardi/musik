@extends('layouts.public')

@section('title', "Chord {$genre->name}")

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Genre: {{ $genre->name }}</h5>
        <a href="{{ route('home') }}" class="btn btn-sm btn-outline-secondary">&larr; Kembali</a>
    </div>

    <div class="list-group mb-4">
        @forelse ($songs as $song)
            <a href="{{ route('songs.show', $song) }}" class="list-group-item list-group-item-action d-flex justify-content-between">
                <span><strong>{{ $song->title }}</strong> — {{ $song->artist }}</span>
                <span class="text-muted small">{{ $song->original_key }}</span>
            </a>
        @empty
            <p class="text-muted">Belum ada chord untuk genre ini.</p>
        @endforelse
    </div>

    {{ $songs->links() }}
@endsection
