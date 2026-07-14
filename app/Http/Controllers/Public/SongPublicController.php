<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use App\Models\Song;
use Illuminate\Http\Request;

class SongPublicController extends Controller
{
    protected array $alphabet;

    public function __construct()
    {
        $this->alphabet = array_merge(range('A', 'Z'), ['#']);
    }

    public function home(Request $request)
    {
        $mode = $request->get('by', 'title'); // 'title' atau 'artist'

        // Jumlah lagu per huruf, buat ditampilkan di grid A-Z (huruf kosong tetap tampil tapi disabled)
        $column = $mode === 'artist' ? 'first_letter_artist' : 'first_letter_title';

        $counts = Song::published()
            ->selectRaw("{$column} as letter, count(*) as total")
            ->groupBy('letter')
            ->pluck('total', 'letter');

        $latest = Song::published()->latest()->take(12)->get();

        $genres = Genre::whereHas('songs', fn ($q) => $q->published())
            ->withCount(['songs' => fn ($q) => $q->published()])
            ->orderBy('name')
            ->get();

        return view('public.home', [
            'alphabet' => $this->alphabet,
            'counts' => $counts,
            'mode' => $mode,
            'latest' => $latest,
            'genres' => $genres,
        ]);
    }

    public function byGenre(Genre $genre)
    {
        $songs = $genre->songs()->published()->orderBy('title')->paginate(24)->withQueryString();

        return view('public.by-genre', compact('songs', 'genre'));
    }

    public function byLetter(Request $request, string $letter)
    {
        $mode = $request->get('by', 'title');
        $letter = mb_strtoupper($letter);

        $query = Song::published();

        if ($mode === 'artist') {
            $query->byArtistLetter($letter)->orderBy('artist');
        } else {
            $query->byTitleLetter($letter)->orderBy('title');
        }

        $songs = $query->paginate(24)->withQueryString();

        return view('public.by-letter', [
            'songs' => $songs,
            'letter' => $letter,
            'mode' => $mode,
            'alphabet' => $this->alphabet,
        ]);
    }

    public function search(Request $request)
    {
        $q = $request->get('q', '');

        $songs = Song::published()
            ->where(function ($w) use ($q) {
                $w->where('title', 'ilike', "%{$q}%")
                  ->orWhere('artist', 'ilike', "%{$q}%");
            })
            ->orderBy('title')
            ->paginate(24)
            ->withQueryString();

        return view('public.search', compact('songs', 'q'));
    }

    public function show(Song $song)
    {
        abort_unless($song->is_published, 404);

        $song->load('genre');
        $song->increment('views_count');

        $related = $this->getRelatedSongs($song);

        return view('public.show', compact('song', 'related'));
    }

    /**
     * 10 lagu terkait: diutamakan dari penyanyi/band yang sama,
     * sisanya (kalau kurang dari 10) diisi dari genre yang sama.
     */
    protected function getRelatedSongs(Song $song, int $limit = 10)
    {
        $sameArtist = Song::published()
            ->where('id', '!=', $song->id)
            ->where('artist', $song->artist)
            ->inRandomOrder()
            ->take($limit)
            ->get();

        if ($sameArtist->count() >= $limit || ! $song->genre_id) {
            return $sameArtist;
        }

        $remaining = $limit - $sameArtist->count();

        $sameGenre = Song::published()
            ->where('id', '!=', $song->id)
            ->where('genre_id', $song->genre_id)
            ->whereNotIn('id', $sameArtist->pluck('id'))
            ->inRandomOrder()
            ->take($remaining)
            ->get();

        return $sameArtist->concat($sameGenre);
    }
}
