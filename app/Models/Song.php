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
        'genre',
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

    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Ubah format inline [C]lirik jadi HTML aman, dengan chord dibungkus
     * <span class="chord-token"> supaya bisa di-transpose lewat JS.
     */
    public function renderedChordHtml(): string
    {
        $escaped = e($this->chord_body);

        return preg_replace_callback('/\[([A-G](?:#|b)?[^\]]*)\]/', function ($m) {
            $chord = $m[1];
            return '<span class="chord-token" data-original="' . e($chord) . '">' . e($chord) . '</span>';
        }, $escaped);
    }
}
