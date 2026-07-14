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

## Update: Related Songs

Di bawah halaman detail chord sekarang tampil 10 lagu terkait — diambil
lewat `SongPublicController::getRelatedSongs()`:
1. Diutamakan lagu lain dari **penyanyi/band yang sama** (acak, maks 10).
2. Kalau kurang dari 10, sisanya diisi dari lagu **genre yang sama**.

Tidak perlu migration tambahan — fitur ini murni query dari data yang sudah ada.

## Catatan: menjalankan artisan dengan namespace berbackslash di bash

Kalau jalankan command seperti:
```bash
php artisan db:seed --class=Database\Seeders\GenreSeeder
```
tanpa kutip, shell bash akan "memakan" backslash-nya sehingga jadi
`DatabaseSeedersGenreSeeder` (class tidak ditemukan). Selalu bungkus pakai
kutip tunggal:
```bash
php artisan db:seed --class='Database\Seeders\GenreSeeder'
```

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

## Update: Login Admin (baru dibuat, sebelumnya belum ada)

Sebelumnya saya salah asumsi kamu sudah punya sistem login. Sekarang sudah
dibuatkan login sederhana pakai auth bawaan Laravel (tanpa Breeze/Jetstream):

File baru:
- `app/Http/Controllers/Admin/AuthController.php`
- `resources/views/admin/auth/login.blade.php`
- `database/seeders/AdminUserSeeder.php`

**Wajib** tambahkan ini di `bootstrap/app.php` (struktur baru Laravel 11+/13,
bukan lagi `Kernel.php`), supaya waktu belum login diarahkan ke
`/admin/login` bukan `/login` bawaan default:

```php
->withMiddleware(function (Illuminate\Foundation\Configuration\Middleware $middleware) {
    $middleware->redirectGuestsTo(fn () => route('admin.login'));
})
```

Setup akun admin pertama:
```bash
php artisan db:seed --class='Database\Seeders\AdminUserSeeder'
```
Login pakai `admin@musik.raisa.id` / `GantiSegera123!`, **lalu langsung ganti
password** (lewat tinker atau bikin halaman ganti password sendiri — belum
saya buatkan karena belum diminta).

Route login: `/admin/login` (GET tampil form, POST submit). Logout: tombol
di navbar admin (POST ke `/admin/logout`).

## Update: Fix deteksi baris "Label : Chord" + Convert Manual ke Inline

**Fix**: baris seperti `Intro : C Am F G` atau `Interlude: Am F C G` sebelumnya
gagal dianggap baris chord (karena kata "Intro"/"Interlude" bikin seluruh
baris dianggap lirik biasa). Sekarang `Song::renderedChordHtml()` mendeteksi
pola "label diikuti token-token chord di akhir baris" — label-nya dibiarkan
teks biasa, token chord-nya tetap di-bold+highlight+bisa di-transpose.
Supaya tidak salah tangkap lirik biasa, pola ini cuma dipakai kalau ada
tanda `:` / `-`, atau label-nya pendek (maks 3 kata).

**Baru**: tombol "🔧 Ubah ke Format Inline [C]" di form admin (dekat kolom
Isi Chord). Ini konversi PAKSA dari format chord-di-atas-lirik jadi format
inline `[C]lirik`, disimpan permanen setelah klik Simpan. Berguna sebagai
jalan pintas kalau auto-detect di halaman publik masih meleset di kasus
yang aneh-aneh — begitu diubah ke inline, tidak butuh deteksi lagi karena
formatnya sudah eksplisit.

## Update: Fitur Cetak Chord

Tombol "🖨 Cetak" di halaman detail lagu, sebelah tombol Autoscroll.
Klik langsung buka dialog print browser (`window.print()`).

CSS `@media print` di `layouts/public.blade.php` otomatis:
- Sembunyikan navbar, footer, toolbar transpose/autoscroll, related songs, dan catatan sumber (semua yang dikasih class `no-print`)
- Paksa warna putih/hitam walau lagi dark mode (biar hemat tinta & tetap kebaca)
- Judul lagu, penyanyi, key/capo, dan isi chord tetap muncul (termasuk chord yang lagi di-transpose — karena itu langsung ubah teks di DOM, bukan cuma tampilan)

## Update: Deteksi Chord Disederhanakan (Kapital = Chord)

`Song::renderedChordHtml()` diganti total pakai aturan yang lebih simpel &
lebih kuat: token **HURUF KAPITAL** yang sesuai grammar chord (A-G, boleh
`#`/`b`, angka, `m`, slash bass seperti `G/B`) dianggap chord **di mana pun**
posisinya dalam baris:
- Baris chord berdiri sendiri: `Am F C`
- Baris label: `Intro : C Am F G`
- Nyempil di tengah lirik: `Lirik lagu C disini D lagi`
- Disambung strip: `C-D-E`

**Huruf kecil TIDAK dianggap chord** (`c`, `d`, dst tetap teks biasa). Sudah
dites terhadap kata-kata kapital di awal kalimat yang berisiko salah tangkap
(`Cinta`, `Dan`, `Bila`, `Fajar`, `Emma`, dll) — semua aman, tetap dianggap
lirik biasa karena tidak match grammar chord (bukan cuma huruf pertama).

Format lama inline `[C]lirik` tetap didukung juga.

## Update: Widget "Baru Ditambahkan" & "Baru Diedit" di Admin

Di atas tabel daftar chord (`/admin/songs`), sekarang ada 2 kartu:
- **🆕 Baru Ditambahkan** — 5 lagu terakhir yang dibuat (`created_at` terbaru)
- **✏️ Baru Diedit** — 5 lagu terakhir yang diedit (`updated_at` > `created_at`, jadi lagu yang belum pernah diedit tidak dobel muncul di sini)

Klik judul lagu di widget langsung ke halaman edit.

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
