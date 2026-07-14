<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Song extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'artist',
        'artist_slug',
        'first_letter_title',
        'first_letter_artist',
        'original_key',
        'capo',
        'genre_id',
        'chord_body',
        'source_url',
        'source_site',
        'is_published',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    protected static function booted()
    {
        static::saving(function (Song $song) {
            // Slug otomatis dari artist + title
            if (empty($song->slug)) {
                $song->slug = Str::slug($song->artist . '-' . $song->title);
            }

            if (empty($song->artist_slug)) {
                $song->artist_slug = Str::slug($song->artist);
            }

            // Huruf awal buat filter abjad. Fallback '#' kalau bukan huruf (angka/simbol).
            $song->first_letter_title = static::firstLetter($song->title);
            $song->first_letter_artist = static::firstLetter($song->artist);

            // Kolom ini NOT NULL di DB (enum + default 'manual'), tapi kalau
            // form mengirim string kosong, middleware ConvertEmptyStringsToNull
            // bawaan Laravel bikin ini jadi null -- dan null EKSPLISIT tidak
            // memicu default kolom. Jadi kita jaga manual di sini.
            if (empty($song->source_site)) {
                $song->source_site = 'manual';
            }
        });
    }

    public static function firstLetter(?string $value): string
    {
        $value = trim((string) $value);
        $value = preg_replace('/^(the|a|an)\s+/i', '', $value); // abaikan prefix umum band/judul

        $char = mb_strtoupper(mb_substr($value, 0, 1));

        return ctype_alpha($char) ? $char : '#';
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeByTitleLetter($query, string $letter)
    {
        return $query->where('first_letter_title', mb_strtoupper($letter));
    }

    public function scopeByArtistLetter($query, string $letter)
    {
        return $query->where('first_letter_artist', mb_strtoupper($letter));
    }

    public function scopeByGenre($query, string $genreSlug)
    {
        return $query->whereHas('genre', fn ($g) => $g->where('slug', $genreSlug));
    }

    public function genre()
    {
        return $this->belongsTo(Genre::class);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Render chord_body jadi HTML aman, dengan chord dibungkus
     * <span class="chord-token"> supaya bisa di-transpose lewat JS.
     *
     * Support 2 format:
     * 1. Inline: [C]lirik lagu disini
     * 2. Chord-di-atas-lirik (paling umum dipakai situs chord Indonesia):
     *        Am      F         C
     *        separuh nafasku
     *    Baris yang isinya CUMA token chord (dipisah spasi) otomatis
     *    dikenali & di-highlight, spasi/posisi tetap dijaga biar sejajar
     *    dengan lirik di bawahnya.
     */
    public function renderedChordHtml(): string
    {
        $quality = 'maj9|maj7|maj|min11|min9|min7|min|m11|m9|m7|sus2|sus4|sus|dim7|dim|aug|add11|add9|6\/9|13|11|9|7|6|5|4|2|m';
        $chordToken = '[A-G](?:#|b)?(?:' . $quality . ')*(?:\/[A-G](?:#|b)?)?';

        $lines = preg_split('/\r\n|\r|\n/', $this->chord_body);

        $rendered = array_map(function ($line) use ($chordToken) {
            // Format inline [C]lirik
            if (preg_match('/\[[^\]]+\]/', $line)) {
                $escaped = e($line);

                return preg_replace_callback('/\[([^\]]+)\]/', function ($m) {
                    return '<span class="chord-token" data-original="' . e($m[1]) . '">' . e($m[1]) . '</span>';
                }, $escaped);
            }

            if (trim($line) === '') {
                return '';
            }

            // Ambil semua token non-spasi beserta posisinya di baris asli
            preg_match_all('/\S+/', $line, $m, PREG_OFFSET_CAPTURE);
            $tokens = $m[0];

            if (empty($tokens)) {
                return e($line);
            }

            $isChord = fn ($t) => preg_match('/^' . $chordToken . '$/', $t) === 1;

            // Hitung berapa token dari BELAKANG yang semuanya chord
            // (menangani baris label seperti "Intro : C Am F G")
            $k = 0;
            for ($i = count($tokens) - 1; $i >= 0; $i--) {
                if ($isChord($tokens[$i][0])) {
                    $k++;
                } else {
                    break;
                }
            }

            $wrapChords = function (string $segment) use ($chordToken) {
                return preg_replace_callback('/(?<=^|\s)(' . $chordToken . ')(?=\s|$)/', function ($mm) {
                    return '<span class="chord-token" data-original="' . e($mm[1]) . '">' . e($mm[1]) . '</span>';
                }, e($segment));
            };

            if ($k === count($tokens)) {
                // Semua token adalah chord -> baris chord murni
                return $wrapChords($line);
            }

            if ($k > 0) {
                $labelTokenCount = count($tokens) - $k;
                $hasColonOrDash = str_contains($line, ':') || str_contains($line, '-');

                // Hanya anggap "label + chord" kalau ada ':' / '-' sebagai penanda,
                // atau labelnya pendek (maks 3 kata) -- biar tidak salah tangkap
                // baris lirik biasa yang kebetulan diakhiri kata mirip chord.
                if ($hasColonOrDash || $labelTokenCount <= 3) {
                    $splitPos = $tokens[count($tokens) - $k][1];
                    $prefix = substr($line, 0, $splitPos);
                    $suffix = substr($line, $splitPos);

                    return e($prefix) . $wrapChords($suffix);
                }
            }

            return e($line);
        }, $lines);

        return implode("\n", $rendered);
    }
}
