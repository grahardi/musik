@csrf

{{-- Panel import: ambil draft dari URL, TIDAK langsung publish --}}
<div class="card border-info mb-4">
    <div class="card-body">
        <h6 class="card-title">Import dari URL (chordtela.com / ultimate-guitar.com)</h6>
        <p class="text-muted small mb-2">
            Tempel link chord, sistem akan mengisi form di bawah sebagai draft.
            Wajib dicek &amp; diedit dulu sebelum kamu centang "Publish".
        </p>
        <div class="input-group">
            <input type="url" id="import_url" class="form-control" placeholder="https://www.chordtela.com/....html">
            <button type="button" id="btn_import" class="btn btn-info text-white">Ambil Draft</button>
        </div>
        <div id="import_status" class="small mt-2"></div>

        <hr>
        <p class="text-muted small mb-2">
            Kalau muncul error 403 (situs blokir akses otomatis), pakai cara ini:
            buka link-nya sendiri di browser kamu, select-all isi chord-nya, copy,
            tempel di kotak bawah ini, lalu klik "Convert & Isi Form".
        </p>
        <textarea id="paste_raw" rows="6" class="form-control mb-2" placeholder="Tempel isi chord yang di-copy dari browser di sini..."></textarea>
        <button type="button" id="btn_convert_paste" class="btn btn-sm btn-outline-info">Convert &amp; Isi Form</button>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Judul Lagu</label>
        <input type="text" name="title" id="title" value="{{ old('title', $song->title) }}" class="form-control" required>
        @error('title') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Penyanyi / Band</label>
        <input type="text" name="artist" id="artist" value="{{ old('artist', $song->artist) }}" class="form-control" required>
        @error('artist') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Original Key</label>
        <input type="text" name="original_key" id="original_key" value="{{ old('original_key', $song->original_key) }}" class="form-control" placeholder="C / Am">
    </div>

    <div class="col-md-3">
        <label class="form-label">Capo</label>
        <input type="text" name="capo" id="capo" value="{{ old('capo', $song->capo) }}" class="form-control" placeholder="fret 4">
    </div>

    <div class="col-md-3">
        <label class="form-label">Genre</label>
        <select name="genre_id" class="form-select">
            <option value="">- Pilih Genre -</option>
            @foreach ($genres as $genre)
                <option value="{{ $genre->id }}" @selected(old('genre_id', $song->genre_id) == $genre->id)>
                    {{ $genre->name }}
                </option>
            @endforeach
        </select>
        <div class="form-text">
            Belum ada genre yang cocok? <a href="{{ route('admin.genres.create') }}" target="_blank">Tambah genre baru</a>.
        </div>
    </div>

    <div class="col-md-3">
        <label class="form-label">Status</label>
        <div class="form-check form-switch mt-2">
            <input class="form-check-input" type="checkbox" name="is_published" value="1" id="is_published"
                @checked(old('is_published', $song->is_published))>
            <label class="form-check-label" for="is_published">Publish</label>
        </div>
    </div>

    <div class="col-12">
        <label class="form-label">Isi Chord</label>
        <textarea name="chord_body" id="chord_body" rows="20" class="form-control font-monospace" required>{{ old('chord_body', $song->chord_body) }}</textarea>
        <div class="form-text">Format inline: <code>[C]lirik lagu [G]disini</code></div>
        @error('chord_body') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <input type="hidden" name="source_url" id="source_url" value="{{ old('source_url', $song->source_url) }}">
    <input type="hidden" name="source_site" id="source_site" value="{{ old('source_site', $song->source_site) }}">
</div>

<div class="mt-4">
    <button class="btn btn-primary">Simpan</button>
    <a href="{{ route('admin.songs.index') }}" class="btn btn-outline-secondary">Batal</a>
</div>

<script>
document.getElementById('btn_import').addEventListener('click', function () {
    const url = document.getElementById('import_url').value.trim();
    const statusEl = document.getElementById('import_status');

    if (!url) {
        statusEl.innerHTML = '<span class="text-danger">Isi URL dulu.</span>';
        return;
    }

    statusEl.innerHTML = '<span class="text-muted">Mengambil data...</span>';

    fetch(`{{ route('admin.songs.import-preview') }}?url=${encodeURIComponent(url)}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(res => {
        if (!res.success) {
            statusEl.innerHTML = `<span class="text-danger">${res.message}</span>`;
            return;
        }

        const d = res.data;
        document.getElementById('title').value = d.title || '';
        document.getElementById('artist').value = d.artist || '';
        document.getElementById('original_key').value = d.original_key || '';
        document.getElementById('capo').value = d.capo || '';
        document.getElementById('chord_body').value = d.chord_body || '';
        document.getElementById('source_url').value = d.source_url || '';
        document.getElementById('source_site').value = d.source_site || 'manual';

        statusEl.innerHTML = '<span class="text-success">Draft berhasil diambil. Cek & edit sebelum publish.</span>';
    })
    .catch(() => {
        statusEl.innerHTML = '<span class="text-danger">Gagal mengambil data. Coba lagi.</span>';
    });
});
document.getElementById('btn_convert_paste').addEventListener('click', function () {
    let raw = document.getElementById('paste_raw').value;

    if (!raw.trim()) {
        alert('Tempel dulu isi chord-nya di kotak.');
        return;
    }

    // Convert format Ultimate Guitar [ch]C[/ch] / [tab]...[/tab] jadi [C] biasa.
    // Kalau yang ditempel format chordtela ([C] biasa), ini tidak mengubah apa-apa.
    let converted = raw
        .replace(/\[\/?tab\]/g, '')
        .replace(/\[ch\](.*?)\[\/ch\]/g, '[$1]')
        .trim();

    document.getElementById('chord_body').value = converted;
    document.getElementById('source_site').value = 'manual';

    document.getElementById('import_status').innerHTML =
        '<span class="text-success">Berhasil di-convert ke kolom "Isi Chord" di bawah. Cek judul/penyanyi/key manual ya.</span>';
});
</script>

