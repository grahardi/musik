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
    /**
     * Render chord_body jadi HTML aman, dengan chord dibungkus
     * <span class="chord-token"> supaya bisa di-transpose lewat JS.
     *
     * Aturan deteksi: token HURUF KAPITAL (A-G, boleh # / b / angka / m /
     * slash bass macam G/B) dianggap chord, di MANA PUN posisinya dalam
     * baris -- baris chord berdiri sendiri, baris label ("Intro : C Am"),
     * bahkan nyempil di tengah lirik ("Lirik lagu C disini D lagi"), atau
     * disambung strip ("C-D-E"). Huruf KECIL (c, d, dst) tidak dianggap
     * chord. Format lama inline [C]lirik tetap didukung.
     */
    public function renderedChordHtml(): string
    {
        $quality = 'maj9|maj7|maj|min11|min9|min7|min|m11|m9|m7|sus2|sus4|sus|dim7|dim|aug|add11|add9|6\/9|13|11|9|7|6|5|4|2|m';
        $chordToken = '[A-G](?:#|b)?(?:' . $quality . ')*(?:\/[A-G](?:#|b)?)?';
        $boundary = '[\s\-,;:!?()]';

        $lines = preg_split('/\r\n|\r|\n/', $this->chord_body);

        $rendered = array_map(function ($line) use ($chordToken, $boundary) {
            // Format lama inline [C]lirik (kalau masih ada yang pakai)
            if (preg_match('/\[[^\]]+\]/', $line)) {
                $escaped = e($line);

                return preg_replace_callback('/\[([^\]]+)\]/', function ($m) {
                    return '<span class="chord-token" data-original="' . $m[1] . '">' . $m[1] . '</span>';
                }, $escaped);
            }

            if (trim($line) === '') {
                return '';
            }

            $escaped = e($line);

            return preg_replace_callback(
                '/(^|' . $boundary . ')(' . $chordToken . ')(?=$|' . $boundary . ')/',
                function ($m) {
                    return $m[1] . '<span class="chord-token" data-original="' . $m[2] . '">' . $m[2] . '</span>';
                },
                $escaped
            );
        }, $lines);

        return implode("\n", $rendered);
    }
}
