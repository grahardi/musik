<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Song;
use App\Services\ChordImportService;
use Illuminate\Http\Request;

class SongController extends Controller
{
    public function index(Request $request)
    {
        $query = Song::query()->latest();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('title', 'ilike', "%{$q}%")
                  ->orWhere('artist', 'ilike', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_published', $request->status === 'published');
        }

        $songs = $query->paginate(20)->withQueryString();

        return view('admin.songs.index', compact('songs'));
    }

    public function create()
    {
        $genres = \App\Models\Genre::orderBy('name')->get();

        return view('admin.songs.create', ['song' => new Song(), 'genres' => $genres]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by'] = $request->user()?->id;
        $data['updated_by'] = $request->user()?->id;
        $data['is_published'] = $request->boolean('is_published');

        $song = Song::create($data);

        return redirect()
            ->route('admin.songs.edit', $song)
            ->with('status', 'Chord berhasil disimpan sebagai draft.');
    }

    public function edit(Song $song)
    {
        $genres = \App\Models\Genre::orderBy('name')->get();

        return view('admin.songs.edit', compact('song', 'genres'));
    }

    public function update(Request $request, Song $song)
    {
        $data = $this->validated($request, $song->id);
        $data['updated_by'] = $request->user()?->id;
        $data['is_published'] = $request->boolean('is_published');

        $song->update($data);

        return redirect()
            ->route('admin.songs.edit', $song)
            ->with('status', 'Perubahan disimpan.');
    }

    public function destroy(Song $song)
    {
        $song->delete();

        return redirect()
            ->route('admin.songs.index')
            ->with('status', 'Chord dihapus.');
    }

    /**
     * Ambil draft dari URL chordtela/ultimate-guitar.
     * Hanya mengembalikan data ke form (JSON) -- BELUM disimpan ke DB,
     * admin masih harus klik Simpan setelah mengedit isinya.
     */
    public function importPreview(Request $request, ChordImportService $importer)
    {
        $request->validate([
            'url' => ['required', 'url'],
        ]);

        try {
            $draft = $importer->importFromUrl($request->input('url'));

            return response()->json([
                'success' => true,
                'data' => $draft,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'artist' => ['required', 'string', 'max:255'],
            'original_key' => ['nullable', 'string', 'max:10'],
            'capo' => ['nullable', 'string', 'max:10'],
            'genre_id' => ['nullable', 'exists:genres,id'],
            'chord_body' => ['required', 'string'],
            'source_url' => ['nullable', 'url'],
            'source_site' => ['nullable', 'in:manual,chordtela,ultimate-guitar'],
        ]);
    }
}
