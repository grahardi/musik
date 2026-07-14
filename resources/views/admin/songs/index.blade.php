@extends('layouts.admin')

@section('title', 'Daftar Chord')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-5">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari judul / penyanyi...">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Semua status</option>
                        <option value="published" @selected(request('status') === 'published')>Published</option>
                        <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100">Filter</button>
                </div>
            </form>

            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Penyanyi</th>
                        <th>Sumber</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($songs as $song)
                        <tr>
                            <td>{{ $song->title }}</td>
                            <td>{{ $song->artist }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $song->source_site }}</span>
                            </td>
                            <td>
                                @if ($song->is_published)
                                    <span class="badge bg-success">Published</span>
                                @else
                                    <span class="badge bg-warning text-dark">Draft</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.songs.edit', $song) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="{{ route('admin.songs.destroy', $song) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus chord ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Belum ada chord.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $songs->links() }}
        </div>
    </div>
@endsection
