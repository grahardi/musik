@extends('layouts.admin')

@section('title', 'Tambah Chord')

@section('content')
    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-3">Tambah Chord Baru</h5>
            <form method="POST" action="{{ route('admin.songs.store') }}">
                @include('admin.songs._form', ['song' => $song])
            </form>
        </div>
    </div>
@endsection
