/**
 * Chord tools: transpose + autoscroll.
 * Dipakai di halaman public.show. Chord ditulis inline di HTML dalam
 * <span class="chord-token" data-chord="C">C</span> supaya gampang di-update teksnya.
 */
(function () {
    const NOTES_SHARP = ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'];
    const NOTES_FLAT  = ['C', 'Db', 'D', 'Eb', 'E', 'F', 'Gb', 'G', 'Ab', 'A', 'Bb', 'B'];

    const ENHARMONIC = { 'Db':'C#','Eb':'D#','Gb':'F#','Ab':'G#','Bb':'A#' };

    let currentStep = 0;

    function normalize(root) {
        return ENHARMONIC[root] || root;
    }

    /**
     * Transpose 1 chord token, misalnya "Am7", "G/B", "C#m", "F#dim7"
     */
    function transposeChordToken(token, steps) {
        const match = token.match(/^([A-G](?:#|b)?)(.*)$/);
        if (!match) return token;

        let [, root, rest] = match;
        root = normalize(root);

        let idx = NOTES_SHARP.indexOf(root);
        if (idx === -1) return token;

        idx = (idx + steps + 12) % 12;
        const newRoot = NOTES_SHARP[idx];

        // Handle slash chord: G/B -> transpose kedua sisi
        const slashMatch = rest.match(/^(.*)\/([A-G](?:#|b)?)$/);
        if (slashMatch) {
            const [, quality, bass] = slashMatch;
            const bassNorm = normalize(bass);
            let bassIdx = NOTES_SHARP.indexOf(bassNorm);
            if (bassIdx !== -1) {
                bassIdx = (bassIdx + steps + 12) % 12;
                return newRoot + quality + '/' + NOTES_SHARP[bassIdx];
            }
        }

        return newRoot + rest;
    }

    function applyTranspose(steps) {
        currentStep += steps;
        document.querySelectorAll('.chord-token').forEach(function (el) {
            const original = el.getAttribute('data-original');
            el.textContent = transposeChordToken(original, currentStep);
        });

        const label = document.getElementById('transpose-label');
        if (label) {
            const sign = currentStep > 0 ? '+' : '';
            label.textContent = currentStep === 0 ? 'Original' : `${sign}${currentStep}`;
        }
    }

    function resetTranspose() {
        currentStep = 0;
        applyTranspose(0);
    }

    // ---- Autoscroll ----
    let scrollInterval = null;
    let scrollSpeed = 3; // px per tick (nilai mentah dari slider), diatur user

    // PC/laptop kerasa 2x terlalu cepat, HP kerasa 2x terlalu lambat --
    // jadi dikasih pengali beda per device biar kerasa pas di keduanya.
    const isMobile = window.matchMedia('(max-width: 768px)').matches;
    const DEVICE_SPEED_MULTIPLIER = isMobile ? 2 : 0.5;

    function startAutoscroll() {
        stopAutoscroll();
        scrollInterval = setInterval(function () {
            window.scrollBy(0, scrollSpeed * DEVICE_SPEED_MULTIPLIER);

            // Auto-stop kalau sudah mentok bawah
            if ((window.innerHeight + window.scrollY) >= document.body.scrollHeight - 5) {
                stopAutoscroll();
            }
        }, 50);

        document.getElementById('btn-scroll-toggle')?.setAttribute('data-state', 'playing');
        const btn = document.getElementById('btn-scroll-toggle');
        if (btn) btn.textContent = '⏸ Pause';
    }

    function stopAutoscroll() {
        if (scrollInterval) clearInterval(scrollInterval);
        scrollInterval = null;
        const btn = document.getElementById('btn-scroll-toggle');
        if (btn) { btn.textContent = '▶ Autoscroll'; btn.setAttribute('data-state', 'paused'); }
    }

    function toggleAutoscroll() {
        if (scrollInterval) {
            stopAutoscroll();
        } else {
            startAutoscroll();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('btn-transpose-up')?.addEventListener('click', () => applyTranspose(1));
        document.getElementById('btn-transpose-down')?.addEventListener('click', () => applyTranspose(-1));
        document.getElementById('btn-transpose-reset')?.addEventListener('click', resetTranspose);

        document.getElementById('btn-scroll-toggle')?.addEventListener('click', toggleAutoscroll);

        const speedSlider = document.getElementById('scroll-speed');
        if (speedSlider) {
            speedSlider.addEventListener('input', function (e) {
                scrollSpeed = parseInt(e.target.value, 10);
            });
        }

        // Stop autoscroll kalau user scroll manual (wheel/touch)
        let manualScrollTimeout;
        window.addEventListener('wheel', function () {
            if (scrollInterval) {
                clearTimeout(manualScrollTimeout);
            }
        }, { passive: true });
    });
})();
