@extends('layouts.admin')

@section('title', 'Edit Genre')

@section('content')
    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-3">Edit Genre</h5>
            <form method="POST" action="{{ route('admin.genres.update', $genre) }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Nama Genre</label>
                    <input type="text" name="name" value="{{ old('name', $genre->name) }}" class="form-control" required>
                    @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <button class="btn btn-primary">Simpan</button>
                <a href="{{ route('admin.genres.index') }}" class="btn btn-outline-secondary">Batal</a>
            </form>
        </div>
    </div>
@endsection
