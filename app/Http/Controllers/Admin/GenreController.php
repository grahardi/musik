<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    public function index()
    {
        $genres = Genre::withCount('songs')->orderBy('name')->paginate(20);

        return view('admin.genres.index', compact('genres'));
    }

    public function create()
    {
        return view('admin.genres.create', ['genre' => new Genre()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:genres,name'],
        ]);

        Genre::create($data);

        return redirect()->route('admin.genres.index')->with('status', 'Genre ditambahkan.');
    }

    public function edit(Genre $genre)
    {
        return view('admin.genres.edit', compact('genre'));
    }

    public function update(Request $request, Genre $genre)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:genres,name,' . $genre->id],
        ]);

        $genre->update($data);

        return redirect()->route('admin.genres.index')->with('status', 'Genre diperbarui.');
    }

    public function destroy(Genre $genre)
    {
        $genre->delete(); // songs.genre_id otomatis null (nullOnDelete)

        return redirect()->route('admin.genres.index')->with('status', 'Genre dihapus.');
    }
}
