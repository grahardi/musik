@extends('layouts.public')

@section('title', "Hasil cari: {$q}")

@section('content')
    <h5 class="mb-3">Hasil pencarian untuk "{{ $q }}"</h5>

    <div class="list-group mb-4">
        @forelse ($songs as $song)
            <a href="{{ route('songs.show', $song) }}" class="list-group-item list-group-item-action d-flex justify-content-between">
                <span><strong>{{ $song->title }}</strong> — {{ $song->artist }}</span>
                <span class="text-muted small">{{ $song->original_key }}</span>
            </a>
        @empty
            <p class="text-muted">Tidak ditemukan.</p>
        @endforelse
    </div>

    {{ $songs->links() }}
@endsection
