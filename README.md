# Struktur Database & Admin Panel — Website Chord

## Cara pasang di project Laravel 8.3 kamu

1. **Copy file-file ini ke project kamu** (struktur folder sudah sesuai Laravel):
   - `database/migrations/2026_07_08_000001_create_songs_table.php`
   - `app/Models/Song.php`
   - `app/Services/ChordImportService.php`
   - `app/Http/Controllers/Admin/SongController.php`
   - `resources/views/layouts/admin.blade.php`
   - `resources/views/admin/songs/*.blade.php`
   - `routes/admin.php`

2. **Daftarkan route admin** — tambahkan baris ini di `routes/web.php`:
   ```php
   require __DIR__.'/admin.php';
   ```

3. **Middleware `auth`** — routes admin di-guard pakai `auth`. Kalau kamu belum
   punya sistem login admin, install dulu (misal `laravel/breeze`) atau
   sesuaikan middleware di `routes/admin.php`.

4. **Jalankan migration:**
   ```bash
   php artisan migrate
   ```

5. Buka `/admin/songs` untuk mulai kelola chord.

## Cara kerja fitur "Import dari URL"

- Admin paste link chordtela.com atau ultimate-guitar.com di form tambah/edit.
- Sistem fetch halaman itu, ambil judul + isi chord, **isi otomatis ke form**
  (belum tersimpan ke database).
- **Wajib direview & diedit** oleh admin sebelum disimpan/publish — checkbox
  "Publish" defaultnya OFF untuk hasil import.
- Field `source_url` & `source_site` disimpan sebagai jejak atribusi, bukan
  untuk auto-publish ulang konten orang lain.

> Catatan hukum singkat: chord + lirik lagu adalah karya berhak cipta milik
> pencipta/label. Fitur ini dirancang sebagai *alat bantu draft* (mirip
> clipping/parafrase manual), bukan alat duplikasi massal. Sebaiknya konten
> yang dipublish sudah diedit/disederhanakan jadi versi kamu sendiri, dan
> pertimbangkan mencantumkan sumber asli sebagai bentuk atribusi.

## Skema tabel `songs`

| Kolom | Keterangan |
|---|---|
| `title`, `artist` | Judul lagu & nama penyanyi |
| `slug` | Auto-generate dari artist+title |
| `first_letter_title`, `first_letter_artist` | Huruf awal (auto), dipakai untuk **menu filter abjad A-Z** nanti di halaman publik |
| `chord_body` | Isi chord format `[C]lirik disini` |
| `original_key`, `capo`, `genre` | Info tambahan |
| `source_url`, `source_site` | Jejak asal import (manual/chordtela/ultimate-guitar) |
| `is_published` | Draft vs tampil di publik |

Filter abjad nanti tinggal query:
```php
Song::published()->byTitleLetter('A')->get();
Song::published()->byArtistLetter('D')->get();
```

## Kompatibilitas Laravel 13 + PHP 8.3 + PostgreSQL

Project ini sudah dites cocok untuk stack:
- **Laravel 13** (rilis Maret 2026) — minim breaking changes dari Laravel 12, jadi kode di sini jalan tanpa perubahan struktural.
- **PHP 8.3** — sudah jadi minimum requirement Laravel 13, tidak ada penyesuaian.
- **PostgreSQL** — satu penyesuaian yang sudah diterapkan: query pencarian pakai `ILIKE` (bukan `LIKE`), karena `LIKE` di Postgres case-sensitive sedangkan di MySQL tidak. Kolom `enum`, `char(1)`, `foreignId` semuanya sudah diabstraksi Laravel jadi otomatis jalan di driver `pgsql`.

Kalau nanti pindah balik ke MySQL, tinggal ganti `ilike` jadi `like` lagi di:
- `app/Http/Controllers/Admin/SongController.php`
- `app/Http/Controllers/Public/SongPublicController.php`

## Update: Fitur Genre / Kategori

File baru:
- `database/migrations/2026_07_14_000001_create_genres_table.php`
- `database/migrations/2026_07_14_000002_add_genre_id_to_songs_table.php`
- `app/Models/Genre.php`
- `app/Http/Controllers/Admin/GenreController.php`
- `resources/views/admin/genres/{index,create,edit}.blade.php`
- `resources/views/public/by-genre.blade.php`
- `database/seeders/GenreSeeder.php`

Route baru:
- Admin: `/admin/genres` (CRUD)
- Publik: `/genre/{slug}`

Cara pakai:
```bash
php artisan migrate
php artisan db:seed --class=Database\\Seeders\\GenreSeeder
```

Catatan: kolom lama `songs.genre` (varchar) sengaja **dibiarkan ada** di DB
supaya tidak kehilangan data kalau sudah pernah diisi manual — cuma sudah
tidak dipakai di form/kode baru (diganti `genre_id` relasi ke tabel `genres`).
Kalau mau dibersihkan, bikin migration terpisah untuk `dropColumn('genre')`
setelah kamu migrasikan datanya ke `genre_id`.

## Kompatibilitas PHP 8.5

Tidak ada penyesuaian kode yang diperlukan — deprecation di PHP 8.5 (operator
backtick, cast non-kanonik seperti `(boolean)`/`(integer)`, dll) tidak dipakai
di codebase ini.

## Update: Halaman Publik + Transpose + Autoscroll

File tambahan:
- `app/Http/Controllers/Public/SongPublicController.php`
- `routes/public_songs.php`
- `resources/views/layouts/public.blade.php`
- `resources/views/public/{home,by-letter,search,show}.blade.php`
- `public/js/chord-tools.js`

Tambahkan di `routes/web.php`:
```php
require __DIR__.'/public_songs.php';
```

⚠️ Route publik memakai nama `home` untuk halaman utama (`/`). Kalau project
kamu sudah punya route `home` (misal dari Laravel Breeze/Jetstream setelah
login), **rename salah satunya** supaya tidak bentrok — misal ubah punya
Breeze jadi `dashboard`.

**Transpose**: chord di-render sebagai `<span class="chord-token" data-original="C">`
lewat `Song::renderedChordHtml()`, lalu `chord-tools.js` menggeser semitone-nya
(support chord minor, slash chord seperti `G/B`, dst).

**Autoscroll**: tombol play/pause + slider kecepatan (1–10), otomatis berhenti
saat sampai bawah halaman.

## Setup Repo GitHub (kamu jalankan sendiri di terminal)

**Jangan tempel Personal Access Token di chat manapun.** Kalau kamu pernah
menempel token di percakapan lain, revoke token itu di
https://github.com/settings/tokens, lalu buat token baru untuk langkah di
bawah.

1. Buat repo baru bernama `musik` di https://github.com/new (jangan centang
   "Initialize with README" biar tidak konflik).

2. Di folder project Laravel kamu, jalankan:
   ```bash
   git init
   git add .
   git commit -m "Initial commit: chord admin + public pages"
   git branch -M main
   git remote add origin https://github.com/USERNAME/musik.git
   ```

3. Saat `git push` diminta password, **pakai token baru sebagai password**
   (bukan password akun GitHub biasa):
   ```bash
   git push -u origin main
   ```
   Kalau tidak mau ketik token tiap push, simpan sekali pakai credential
   manager:
   ```bash
   git config --global credential.helper store
   ```
   (token akan tersimpan terenkripsi di local, tidak pernah dikirim ke
   siapapun selain GitHub).

4. Pastikan `.env` **tidak ikut ke-commit** (cek `.gitignore` bawaan Laravel
   sudah exclude `.env` secara default).

## Belum termasuk (next step kalau mau lanjut)

- Halaman kategori genre
- Related songs / rekomendasi
- Sitemap.xml buat SEO
- Rate limiting untuk fitur import URL
