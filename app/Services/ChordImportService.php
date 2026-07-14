<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use DOMDocument;
use DOMXPath;

/**
 * Import "1 URL -> 1 draft".
 *
 * PENTING: hasil dari service ini SELALU is_published = false.
 * Tujuannya cuma bantu isi form biar admin gak perlu ketik ulang manual,
 * tapi tetap wajib direview/di-edit dulu sebelum dipublish (hak cipta lagu
 * & lirik tetap milik pencipta/label aslinya, bukan hasil scraping situs lain).
 */
class ChordImportService
{
    public function importFromUrl(string $url): array
    {
        $host = parse_url($url, PHP_URL_HOST) ?? '';

        if (Str::contains($host, 'chordtela.com')) {
            return $this->parseChordtela($url);
        }

        if (Str::contains($host, 'ultimate-guitar.com')) {
            return $this->parseUltimateGuitar($url);
        }

        throw new \InvalidArgumentException('URL tidak dikenali. Saat ini hanya support chordtela.com dan ultimate-guitar.com.');
    }

    protected function fetch(string $url): string
    {
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
        ])->timeout(15)->get($url);

        if ($response->status() === 403) {
            throw new \RuntimeException(
                'Situs ini memblokir akses otomatis (403). Coba pakai mode "Tempel Manual" di bawah: buka link-nya sendiri di browser, copy isinya, lalu tempel di sana.'
            );
        }

        if (! $response->successful()) {
            throw new \RuntimeException('Gagal mengambil halaman (' . $response->status() . ').');
        }

        return $response->body();
    }

    protected function parseChordtela(string $url): array
    {
        $html = $this->fetch($url);
        $dom = $this->loadDom($html);
        $xpath = new DOMXPath($dom);

        // Judul ada di <h1>, formatnya biasanya:
        // "Kunci Gitar {Artist} - {Title} Chord Dasar"
        $h1 = $xpath->query('//h1');
        $rawTitle = $h1->length ? trim($h1->item(0)->textContent) : '';
        $rawTitle = preg_replace('/^kunci gitar\s+/i', '', $rawTitle);
        $rawTitle = preg_replace('/\s+chord dasar$/i', '', $rawTitle);

        [$artist, $title] = $this->splitArtistTitle($rawTitle);

        // Isi chord ada di dalam <pre> pertama pada konten
        $preNodes = $xpath->query('//pre');
        $chordBody = $preNodes->length ? trim($preNodes->item(0)->textContent) : '';

        // Buang bagian "===ORIGINAL CHORD===" duplikat kalau ada, biar admin
        // tinggal pilih salah satu versi saat edit (opsional, dibiarkan utuh
        // di textarea supaya tidak ada info yang hilang)
        return [
            'title' => $title ?: $rawTitle,
            'artist' => $artist ?: 'Unknown',
            'chord_body' => $chordBody,
            'original_key' => null,
            'capo' => $this->extractCapo($chordBody),
            'source_url' => $url,
            'source_site' => 'chordtela',
        ];
    }

    protected function parseUltimateGuitar(string $url): array
    {
        $html = $this->fetch($url);

        // UG menyimpan data di <div class="js-store" data-content="...json...">
        if (! preg_match('/class="js-store"\s+data-content="([^"]+)"/', $html, $m)) {
            throw new \RuntimeException('Format halaman Ultimate Guitar tidak dikenali (mungkin berubah).');
        }

        $json = html_entity_decode($m[1]);
        $data = json_decode($json, true);

        $tab = $data['store']['page']['data']['tab_view']['wiki_tab'] ?? null;
        $tabInfo = $data['store']['page']['data']['tab'] ?? null;

        $rawContent = $tab['content'] ?? '';
        $title = $tabInfo['song_name'] ?? '';
        $artist = $tabInfo['artist_name'] ?? '';

        // UG pakai tag [ch]C[/ch] untuk chord dan [tab]...[/tab] untuk section,
        // konversi ke format inline [C] yang konsisten dengan chordtela
        $chordBody = preg_replace('/\[\/?tab\]/', '', $rawContent);
        $chordBody = preg_replace('/\[ch\](.*?)\[\/ch\]/', '[$1]', $chordBody);
        $chordBody = trim(html_entity_decode($chordBody));

        return [
            'title' => $title ?: 'Untitled',
            'artist' => $artist ?: 'Unknown',
            'chord_body' => $chordBody,
            'original_key' => $tabInfo['tonality_name'] ?? null,
            'capo' => isset($tabInfo['capo']) && $tabInfo['capo'] > 0 ? 'fret ' . $tabInfo['capo'] : null,
            'source_url' => $url,
            'source_site' => 'ultimate-guitar',
        ];
    }

    protected function splitArtistTitle(string $text): array
    {
        if (Str::contains($text, ' - ')) {
            [$artist, $title] = array_map('trim', explode(' - ', $text, 2));
            return [$artist, $title];
        }

        return ['', $text];
    }

    protected function extractCapo(string $chordBody): ?string
    {
        if (preg_match('/capo\D{0,10}(fret\s*)?(\d+)/i', $chordBody, $m)) {
            return 'fret ' . $m[2];
        }

        return null;
    }

    protected function loadDom(string $html): DOMDocument
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        libxml_clear_errors();

        return $dom;
    }
}
