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
        <div class="form-text">
            Format inline: <code>[C]lirik lagu disini</code> — atau format chord-di-atas-lirik biasa juga otomatis dikenali.
        </div>
        <button type="button" id="btn_to_inline" class="btn btn-sm btn-outline-secondary mt-2">
            🔧 Ubah ke Format Inline [C]
        </button>
        <span class="text-muted small">(chord yang kedetect otomatis dibungkus <code>[C]</code>; yang tidak kedetect dibiarkan apa adanya di tempatnya, tinggal tambahin <code>[ ]</code> manual)</span>
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

document.getElementById('btn_to_inline').addEventListener('click', function () {
    const textarea = document.getElementById('chord_body');
    textarea.value = convertToInlineFormat(textarea.value);
});

/**
 * Ubah teks format "chord-di-atas / lirik-di-bawah" (dan baris label
 * seperti "Intro : C Am F G") jadi format inline [C]lirik. Baris yang
 * sudah inline atau lirik biasa dibiarkan apa adanya.
 */
function convertToInlineFormat(text) {
    const quality = 'maj9|maj7|maj|min11|min9|min7|min|m11|m9|m7|sus2|sus4|sus|dim7|dim|aug|add11|add9|6\\/9|13|11|9|7|6|5|4|2|m';
    const chordTokenRe = new RegExp('^[A-G](#|b)?(?:' + quality + ')*(?:\\/[A-G](?:#|b)?)?$');

    function getTokensWithOffset(line) {
        const tokens = [];
        const re = /\S+/g;
        let m;
        while ((m = re.exec(line)) !== null) {
            tokens.push({ text: m[0], offset: m.index });
        }
        return tokens;
    }

    function isChordLikeLine(line) {
        const tokens = getTokensWithOffset(line);
        if (tokens.length === 0) return false;
        const matched = tokens.filter(t => chordTokenRe.test(t.text)).length;
        // Minimal separuh token di baris ini kelihatan seperti chord.
        // Token yang tidak match tetap dibiarkan apa adanya (tidak dibungkus []),
        // supaya gampang kamu tambahin manual kalau memang itu chord.
        return (matched / tokens.length) >= 0.5;
    }

    function mergeChordIntoLyric(chordLine, lyricLine) {
        const tokens = getTokensWithOffset(chordLine);
        let chars = Array.from(lyricLine);
        let shift = 0;

        tokens.forEach(t => {
            // Hanya token yang KEDETECT sebagai chord yang dibungkus [ ].
            // Yang tidak terdeteksi disisipkan apa adanya (polos) di posisi yang sama,
            // biar kelihatan jelas mana yang perlu ditambah [] manual.
            const isChord = chordTokenRe.test(t.text);
            const insertText = isChord ? `[${t.text}]` : t.text;
            const insertPos = Math.min(t.offset + shift, chars.length);
            chars.splice(insertPos, 0, ...Array.from(insertText));
            shift += insertText.length;
        });

        return chars.join('');
    }

    const lines = text.split(/\r\n|\r|\n/);
    const result = [];
    let i = 0;

    while (i < lines.length) {
        const line = lines[i];

        // Sudah format inline atau baris kosong -> biarkan
        if (line.includes('[') || line.trim() === '') {
            result.push(line);
            i++;
            continue;
        }

        if (isChordLikeLine(line)) {
            const nextLine = lines[i + 1];
            const nextIsLyric = nextLine !== undefined && nextLine.trim() !== '' && !isChordLikeLine(nextLine) && !nextLine.includes('[');

            if (nextIsLyric) {
                result.push(mergeChordIntoLyric(line, nextLine));
                i += 2;
            } else {
                // Baris chord berdiri sendiri (mis. intro/outro tanpa lirik di bawahnya).
                // Token yang kedetect dibungkus [C], yang tidak kedetect dibiarkan polos.
                const bracketed = getTokensWithOffset(line)
                    .map(t => chordTokenRe.test(t.text) ? `[${t.text}]` : t.text)
                    .join(' ');
                result.push(bracketed);
                i++;
            }
            continue;
        }

        // Cek baris label + chord, mis. "Intro : C Am F G"
        const tokens = getTokensWithOffset(line);
        let k = 0;
        for (let j = tokens.length - 1; j >= 0; j--) {
            if (chordTokenRe.test(tokens[j].text)) k++; else break;
        }
        const hasSep = line.includes(':') || line.includes('-');
        if (k > 0 && k < tokens.length && (hasSep || (tokens.length - k) <= 3)) {
            const splitIdx = tokens.length - k;
            const splitPos = tokens[splitIdx].offset;
            const prefix = line.slice(0, splitPos);
            const chordPart = tokens.slice(splitIdx).map(t => `[${t.text}]`).join(' ');
            result.push(prefix + chordPart);
            i++;
            continue;
        }

        result.push(line);
        i++;
    }

    return result.join('\n');
}
</script>

