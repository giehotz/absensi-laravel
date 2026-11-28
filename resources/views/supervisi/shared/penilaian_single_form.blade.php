@extends('layouts.supervisi')
@section('title', 'Form Penilaian: ' . $jenis->nama)
@section('page_title', 'Form Penilaian Supervisor (' . $jenis->kode . ')')

@section('content')
@php
    $saveRoute = route("supervisi.{$portalPrefix}.penilaian.save", $jadwal);
    $hubRoute = route("supervisi.{$portalPrefix}.penilaian.edit", $jadwal);
    $jenisMaxSkor = $jenis->aspekPenilaians->max(fn ($a): int => $a->maxSkor()) ?: ((int) $jenis->skor_maksimal === 3 ? 3 : 4);
@endphp

<!-- Header Navigasi Form Satuan -->
<section class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="space-y-1">
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ $hubRoute }}" class="px-2.5 py-1 rounded-lg border-2 border-black bg-white hover:bg-slate-100 text-xs font-black shadow-[1.5px_1.5px_0px_0px_#000]">
                    ← Kembali ke Hub 14 Form
                </a>
                <span class="px-2 py-0.5 rounded border border-black bg-[#FFD43B] font-mono text-[11px] font-black">
                    Form {{ $currentNumber }} / {{ $allJenis->count() }} · {{ $jenis->kode }}
                </span>
            </div>
            <h2 class="font-heading font-black text-lg text-slate-900">{{ $jenis->nama }}</h2>
            <p class="text-xs font-semibold text-slate-600">
                Guru: <strong>{{ $jadwal->teacher?->user?->name }}</strong> · {{ $jadwal->schoolClass?->name ?? '-' }} / {{ $jadwal->subject?->name ?? '-' }} · {{ $jadwal->tanggal?->format('d/m/Y') }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <div class="px-3 py-1.5 rounded-lg border-2 border-black bg-slate-50">
                <div class="text-[10px] font-bold text-slate-500 uppercase">Progres Form</div>
                <div id="single-progress" class="text-sm font-black tabular-nums">0 / {{ $jenis->aspekPenilaians->count() }}</div>
            </div>
            <div class="px-3 py-1.5 rounded-lg border-2 border-black bg-[#FFD43B]">
                <div class="text-[10px] font-bold text-slate-800 uppercase">Nilai Komponen</div>
                <div id="single-nilai" class="text-sm font-black tabular-nums">{{ $hasil && $hasil->skor_maksimal > 0 ? number_format((float) $hasil->nilai_akhir, 2) . '% (' . $hasil->ketercapaian . ')' : '0.00%' }}</div>
            </div>
            <a href="{{ route('supervisi.cetak.instrumen', ['jadwal' => $jadwal, 'jenis' => $jenis]) }}" target="_blank"
                class="px-3 py-2 bg-[#20C997] hover:bg-emerald-400 text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                🖨️ Cetak PDF Form Ini
            </a>
        </div>
    </div>

    <!-- Switcher Cepat ke 14 Form -->
    <div class="flex flex-wrap gap-1.5 pt-3 border-t-2 border-slate-200">
        @foreach($allJenis as $idx => $jOpt)
            <a href="{{ route("supervisi.{$portalPrefix}.penilaian.form", ['jadwal' => $jadwal, 'jenis' => $jOpt]) }}"
                title="{{ $jOpt->nama }}"
                class="px-2 py-1 rounded border border-black text-[10px] font-black {{ (int) $jOpt->id === (int) $jenis->id ? 'bg-slate-900 text-white' : 'bg-white hover:bg-amber-100 text-slate-800' }}">
                {{ $idx + 1 }}. {{ $jOpt->kode }}
            </a>
        @endforeach
    </div>

    <div id="single-status-banner" role="status" aria-live="polite" hidden
        class="p-3 rounded-lg border-2 border-black text-xs font-bold"></div>
</section>

<!-- Form Penilaian Komponen Tunggal -->
<form id="single-instrument-form" action="{{ $saveRoute }}" method="POST"
    data-jenis-nama="{{ $jenis->nama }}"
    data-kategori="{{ $jenis->kategori_supervisi }}"
    class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-5">
    @csrf
    <input type="hidden" name="jenis_penilaian_id" value="{{ $jenis->id }}">

    <!-- Bulk-Skor Instrumen Ini -->
    <div class="p-4 rounded-xl border-2 border-black bg-[#FFF9DB] flex flex-wrap items-center justify-between gap-3">
        <div class="space-y-1">
            <div class="text-xs font-black text-slate-900">⚡ Bulk-Skor Instrumen: Isi Semua Aspek Sekaligus</div>
            <p class="text-[11px] font-semibold text-slate-700">
                @if($jenisMaxSkor === 3)
                    Skala PMM: 1 = Belum Dilakukan, 2 = Dilakukan tapi Belum Efektif, 3 = Dilakukan dan Efektif.
                @else
                    Skala 1–4: 1 = Kurang, 2 = Cukup, 3 = Baik, 4 = Sangat Baik.
                @endif
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @for($s = 1; $s <= $jenisMaxSkor; $s++)
                <button type="button" data-bulk-single="{{ $s }}"
                    class="min-w-[40px] min-h-[40px] px-3 py-1 rounded-lg border-2 border-black bg-white hover:bg-[#FFD43B] text-xs font-black shadow-[1.5px_1.5px_0px_0px_#000] cursor-pointer">
                    {{ $s }}
                </button>
            @endfor
            <button type="button" id="btn-reset-notes-single"
                class="px-3 py-2 rounded-lg border-2 border-black bg-white hover:bg-slate-100 text-[11px] font-black cursor-pointer">
                ↻ Refresh Catatan
            </button>
        </div>
    </div>

    @php
        $isKma6334 = $jenis->kategori_supervisi === \App\Models\Supervisi\SupervisiJenisPenilaian::KATEGORI_KMA_6334;
    @endphp
    <!-- Daftar Aspek beserta Keterangan -->
    <div class="space-y-3">
        @foreach($jenis->aspekPenilaians as $aspek)
            @php
                $maxSkor = $aspek->maxSkor();
                $detail = $detailsMap->get($aspek->id);
                $selectedSkor = $detail?->skor;
                $selectedCatatan = $detail?->catatan;
                $selectedRekomendasiAspek = $detail?->rekomendasi;
                $isFokusUtama = in_array((int) $aspek->id, $fokusDipilihIds, true);
            @endphp
            <fieldset data-aspek-card="{{ $aspek->id }}"
                data-max-skor="{{ $maxSkor }}"
                data-nama-aspek="{{ $aspek->nama_aspek }}"
                data-fokus-perilaku="{{ $aspek->fokus_perilaku ?? '' }}"
                class="p-4 rounded-xl border-2 border-black {{ $isFokusUtama ? 'bg-amber-50/70' : 'bg-slate-50' }} space-y-3">
                <legend class="sr-only">{{ $aspek->urutan }}. {{ $aspek->nama_aspek }}</legend>

                <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                    <div class="flex-1 space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm font-black text-slate-900">{{ $aspek->urutan }}. {{ $aspek->nama_aspek }}</span>
                            @if($isFokusUtama)
                                <span class="px-2 py-0.5 rounded border border-black bg-[#FFD43B] text-[10px] font-black uppercase">
                                    ⭐ Fokus Utama (Form A)
                                </span>
                            @endif
                        </div>

                        @if($aspek->fokus_perilaku)
                            <div class="text-xs font-semibold text-blue-950 bg-blue-50 border border-blue-300 rounded-lg p-2.5">
                                <strong>Keterangan Indikator:</strong> {{ $aspek->fokus_perilaku }}
                            </div>
                        @endif

                        @if($aspek->perilaku_dianjurkan)
                            <div class="text-[11px] text-emerald-950 bg-emerald-50 border border-emerald-300 rounded-lg p-2.5 whitespace-pre-line">
                                <strong>Indikator Keterpenuhan / Dianjurkan:</strong><br>{{ $aspek->perilaku_dianjurkan }}
                            </div>
                        @endif

                        @if($aspek->perilaku_dihindari)
                            <div class="text-[11px] text-rose-950 bg-rose-50 border border-rose-300 rounded-lg p-2.5 whitespace-pre-line">
                                <strong>Dihindari:</strong><br>{{ $aspek->perilaku_dihindari }}
                            </div>
                        @endif
                    </div>

                    <div class="w-full lg:w-[370px] shrink-0 space-y-2.5">
                        <div>
                            <div class="text-[11px] font-black text-slate-700 mb-1.5">Pilih Skor (1–{{ $maxSkor }})</div>
                            <div class="flex flex-wrap gap-1.5">
                                @for($s = 1; $s <= $maxSkor; $s++)
                                    @php
                                        $radioId = "skor_single_{$aspek->id}_{$s}";
                                        $scoreWord = match (true) {
                                            $maxSkor === 3 && $s === 1 => 'Belum',
                                            $maxSkor === 3 && $s === 2 => 'Kurang Efektif',
                                            $maxSkor === 3 && $s === 3 => 'Efektif',
                                            $s === 1 => 'Kurang',
                                            $s === 2 => 'Cukup',
                                            $s === 3 => 'Baik',
                                            default => 'Sangat Baik',
                                        };
                                    @endphp
                                    <div class="relative">
                                        <input type="radio"
                                            id="{{ $radioId }}"
                                            name="skor[{{ $aspek->id }}]"
                                            value="{{ $s }}"
                                            data-score-radio
                                            {{ (int) $selectedSkor === $s ? 'checked' : '' }}
                                            class="peer sr-only">
                                        <label for="{{ $radioId }}"
                                            class="inline-flex flex-col items-center justify-center min-w-[54px] min-h-[44px] px-2.5 py-1 rounded-lg border-2 border-black bg-white hover:bg-amber-100 peer-checked:bg-[#FFD43B] peer-focus-visible:ring-2 peer-focus-visible:ring-blue-700 shadow-[1.5px_1.5px_0px_0px_#000] cursor-pointer select-none transition-colors">
                                            <span class="text-xs font-black leading-none">{{ $s }}</span>
                                            <span class="text-[10px] font-bold text-slate-700 mt-0.5 leading-none">{{ $scoreWord }}</span>
                                        </label>
                                    </div>
                                @endfor
                            </div>
                        </div>

                        <div class="space-y-1">
                            <div class="flex items-center justify-between gap-2">
                                <label for="catatan_single_{{ $aspek->id }}" class="text-[11px] font-black text-slate-700">
                                    {{ $isKma6334 ? 'Hasil Pengamatan' : 'Catatan Aspek' }}
                                </label>
                                <div class="flex items-center gap-1.5">
                                    <span data-note-mode class="px-1.5 py-0.5 rounded border border-black bg-white text-[10px] font-bold">
                                        {{ filled($selectedCatatan) ? 'Tersimpan' : 'Siap Auto' }}
                                    </span>
                                    <button type="button" data-note-regen class="px-2 py-0.5 rounded border border-black bg-white hover:bg-amber-100 text-[10px] font-black cursor-pointer">↻ Auto</button>
                                    <button type="button" data-note-clear class="px-2 py-0.5 rounded border border-black bg-white hover:bg-rose-100 text-[10px] font-black cursor-pointer">✕</button>
                                </div>
                            </div>
                            <textarea id="catatan_single_{{ $aspek->id }}"
                                name="catatan[{{ $aspek->id }}]"
                                rows="2"
                                data-note-input
                                placeholder="{{ $isKma6334 ? 'Catat hasil pengamatan nyata di kelas / dokumen...' : 'Catatan otomatis muncul saat skor dipilih, atau ketik manual...' }}"
                                class="w-full px-2.5 py-1.5 text-xs border-2 border-black rounded-lg bg-white">{{ $selectedCatatan }}</textarea>
                        </div>

                        @if($isKma6334)
                            <div class="space-y-1">
                                <label for="rekomendasi_aspek_{{ $aspek->id }}" class="block text-[11px] font-black text-slate-700">
                                    Rekomendasi Aspek (Format SK 6334)
                                </label>
                                <textarea id="rekomendasi_aspek_{{ $aspek->id }}"
                                    name="rekomendasi_aspek[{{ $aspek->id }}]"
                                    rows="2"
                                    placeholder="Saran perbaikan / penguatan spesifik untuk aspek ini..."
                                    class="w-full px-2.5 py-1.5 text-xs border-2 border-black rounded-lg bg-white">{{ $selectedRekomendasiAspek }}</textarea>
                            </div>
                        @endif
                    </div>
                </div>
            </fieldset>
        @endforeach
    </div>

    <div>
        <label class="block text-xs font-black mb-1" for="rekomendasi_single">Rekomendasi Komponen Ini</label>
        <textarea id="rekomendasi_single" name="rekomendasi" rows="2" placeholder="Catatan rekomendasi khusus untuk form {{ $jenis->nama }}"
            class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">{{ $hasil?->rekomendasi }}</textarea>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t-2 border-black">
        <div>
            @if($prevJenis)
                <a href="{{ route("supervisi.{$portalPrefix}.penilaian.form", ['jadwal' => $jadwal, 'jenis' => $prevJenis]) }}"
                    class="px-4 py-2 bg-white hover:bg-slate-100 text-black text-xs font-black rounded-lg border-2 border-black">
                    ← {{ $prevJenis->kode }}
                </a>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="submit" class="px-4 py-2 bg-[#5294FF] hover:bg-blue-500 text-white text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                💾 Simpan Form Ini
            </button>
            @if($nextJenis)
                <button type="submit" name="next_jenis_id" value="{{ $nextJenis->id }}"
                    class="px-5 py-2 bg-[#FFD43B] hover:bg-amber-300 text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                    💾 Simpan &amp; Lanjut ({{ $nextJenis->kode }}) →
                </button>
            @else
                <button type="submit" name="redirect_to" value="hub"
                    class="px-5 py-2 bg-[#20C997] hover:bg-emerald-400 text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                    💾 Selesai &amp; Kembali ke Hub →
                </button>
            @endif
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
(() => {
    const form = document.getElementById('single-instrument-form');
    if (!form) return;

    const jenisNama = form.dataset.jenisNama || '';
    const kategori = form.dataset.kategori || 'reguler';
    const cards = Array.from(form.querySelectorAll('[data-aspek-card]'));
    const statusBanner = document.getElementById('single-status-banner');
    const elProgress = document.getElementById('single-progress');
    const elNilai = document.getElementById('single-nilai');

    const AUTO_PREFIXES = [
        'Aspek ', 'Pelaksanaan ', 'Penyusunan ', 'Implementasi aspek ', 'Diperlukan pendampingan ',
    ];

    function isAutoNote(val) {
        const t = (val || '').trim();
        return t === '' || AUTO_PREFIXES.some((p) => t.startsWith(p));
    }

    function cleanLabel(text) {
        return (text || '').replace(/^\d+\.\s*/, '').replace(/\s+/g, ' ').trim().replace(/[.]+$/, '');
    }

    function generateNote(score, maxSkor, namaAspek, fokus) {
        const num = parseInt(score, 10);
        const label = cleanLabel(fokus || namaAspek || 'aspek pembelajaran');
        const isPerangkat = kategori === 'perangkat_ajar' || jenisNama.toLowerCase().includes('administrasi');

        if (maxSkor === 3) {
            if (num === 3) return `Pelaksanaan ${label} dilakukan secara konsisten dan efektif sehingga berdampak positif terhadap proses belajar murid.`;
            if (num === 2) return `Aspek ${label} sudah dilakukan selama observasi, namun efektivitas dan konsistensinya masih dapat ditingkatkan.`;
            return `Aspek belum terlihat optimal pada ${label} dan direkomendasikan menjadi fokus penguatan pada sesi tindak lanjut.`;
        }
        if (num >= 4) {
            return isPerangkat
                ? `Penyusunan ${label} lengkap, sistematis, dan selaras dengan tujuan pembelajaran yang direncanakan.`
                : `Implementasi aspek ${label} terlaksana sangat baik, terstruktur, dan mendorong keterlibatan aktif peserta didik.`;
        }
        if (num === 3) {
            return isPerangkat
                ? `Penyusunan ${label} sudah baik dan memenuhi standar utama perencanaan pembelajaran.`
                : `Implementasi aspek ${label} berjalan baik dan sesuai dengan alur pembelajaran yang dirancang.`;
        }
        if (num === 2) return `Aspek sudah tampak pada ${label}, namun masih memerlukan penguatan agar pelaksanaannya lebih optimal.`;
        return `Diperlukan pendampingan dan perbaikan terstruktur pada ${label} sebelum supervisi berikutnya.`;
    }

    function predikat(n) {
        if (n >= 86) return 'Baik Sekali';
        if (n >= 70) return 'Baik';
        if (n >= 55) return 'Cukup';
        return 'Kurang';
    }

    function updateSummary() {
        let totalSkor = 0;
        let maxTotal = 0;
        let filled = 0;
        cards.forEach((c) => {
            const m = parseInt(c.dataset.maxSkor || '4', 10);
            maxTotal += m;
            const checked = c.querySelector('input[type="radio"]:checked');
            if (checked) {
                filled += 1;
                totalSkor += parseInt(checked.value, 10) || 0;
            }
        });
        const nilai = maxTotal > 0 ? (totalSkor / maxTotal) * 100 : 0;
        if (elProgress) elProgress.textContent = `${filled} / ${cards.length}`;
        if (elNilai) elNilai.textContent = filled > 0 ? `${nilai.toFixed(2)}% (${predikat(nilai)})` : '0.00%';
    }

    function applyToCard(card, rawScore, overwriteManual = false) {
        const m = parseInt(card.dataset.maxSkor || '4', 10);
        const s = Math.max(1, Math.min(m, parseInt(rawScore, 10) || 1));
        const r = card.querySelector(`input[type="radio"][value="${s}"]`);
        if (r) r.checked = true;
        const note = card.querySelector('[data-note-input]');
        const badge = card.querySelector('[data-note-mode]');
        if (note && (overwriteManual || note.dataset.manual !== 'true' || note.value.trim() === '')) {
            note.value = generateNote(s, m, card.dataset.namaAspek, card.dataset.fokusPerilaku);
            note.dataset.manual = 'false';
            if (badge) badge.textContent = 'Auto';
        }
    }

    cards.forEach((card) => {
        const note = card.querySelector('[data-note-input]');
        const badge = card.querySelector('[data-note-mode]');
        if (note) {
            const manual = !isAutoNote(note.value);
            note.dataset.manual = manual ? 'true' : 'false';
            if (badge) badge.textContent = note.value.trim() === '' ? 'Siap Auto' : (manual ? 'Manual' : 'Auto');
            note.addEventListener('input', () => {
                const has = note.value.trim() !== '';
                note.dataset.manual = has ? 'true' : 'false';
                if (badge) badge.textContent = has ? 'Manual' : 'Siap Auto';
            });
        }
        card.querySelectorAll('[data-score-radio]').forEach((radio) => {
            radio.addEventListener('change', () => {
                if (!radio.checked) return;
                applyToCard(card, radio.value, false);
                updateSummary();
            });
        });
        card.querySelector('[data-note-regen]')?.addEventListener('click', () => {
            const c = card.querySelector('input[type="radio"]:checked');
            if (!c) {
                if (statusBanner) {
                    statusBanner.textContent = 'Pilih skor terlebih dahulu sebelum membuat catatan otomatis.';
                    statusBanner.className = 'p-3 rounded-lg border-2 border-black text-xs font-bold bg-amber-100 text-amber-950';
                    statusBanner.hidden = false;
                }
                return;
            }
            applyToCard(card, c.value, true);
        });
        card.querySelector('[data-note-clear]')?.addEventListener('click', () => {
            if (note) {
                note.value = '';
                note.dataset.manual = 'false';
                if (badge) badge.textContent = 'Siap Auto';
            }
        });
    });

    document.querySelectorAll('[data-bulk-single]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const target = btn.dataset.bulkSingle;
            cards.forEach((c) => applyToCard(c, target, false));
            updateSummary();
        });
    });

    document.getElementById('btn-reset-notes-single')?.addEventListener('click', () => {
        cards.forEach((c) => {
            const ch = c.querySelector('input[type="radio"]:checked');
            if (ch) applyToCard(c, ch.value, true);
        });
    });

    updateSummary();
})();
</script>
@endpush
