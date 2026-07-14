@extends('layouts.admin')

@section('title', 'Edit Chord')

@section('content')
    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-3">Edit Chord — {{ $song->title }}</h5>
            <form method="POST" action="{{ route('admin.songs.update', $song) }}">
                @method('PUT')
                @include('admin.songs._form', ['song' => $song, 'genres' => $genres])
            </form>
        </div>
    </div>
@endsection
