@extends('layouts.admin')

@section('title', 'Genre')

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Daftar Genre</h5>
                <a href="{{ route('admin.genres.create') }}" class="btn btn-sm btn-primary">+ Tambah Genre</a>
            </div>

            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Jumlah Lagu</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($genres as $genre)
                        <tr>
                            <td>{{ $genre->name }}</td>
                            <td>{{ $genre->songs_count }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.genres.edit', $genre) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="{{ route('admin.genres.destroy', $genre) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus genre ini? Lagu yang pakai genre ini jadi tanpa genre.')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">Belum ada genre.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $genres->links() }}
        </div>
    </div>
@endsection
