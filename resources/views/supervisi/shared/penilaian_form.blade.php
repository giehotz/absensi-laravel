@extends('layouts.supervisi')
@section('title', 'Penilaian Observasi Supervisi')
@section('page_title', 'Formulir Observasi & Penilaian (Form B)')

@section('content')
@php
    use App\Models\Supervisi\SupervisiJenisPenilaian;

    $saveRoute = route("supervisi.{$portalPrefix}.penilaian.save", $jadwal);
    $completeRoute = route("supervisi.{$portalPrefix}.penilaian.complete", $jadwal);
    $uploadFotoRoute = route("supervisi.{$portalPrefix}.penilaian.foto.upload", $jadwal);
    $deleteFotoRouteName = "supervisi.{$portalPrefix}.penilaian.foto.delete";

    $step1Jenis = $jenisList->filter(
        fn (SupervisiJenisPenilaian $j): bool => $j->kategori_supervisi === SupervisiJenisPenilaian::KATEGORI_PERANGKAT_AJAR
            || $j->kode === 'REG-ADM'
    )->values();

    $step1Ids = $step1Jenis->pluck('id')->all();
    $step2Jenis = $jenisList->reject(
        fn (SupervisiJenisPenilaian $j): bool => in_array($j->id, $step1Ids, true)
    )->values();

    $stepConfigs = [
        1 => [
            'title' => $jadwal->isKurmer() ? 'Step 1: Telaah Perangkat Ajar' : 'Step 1: Administrasi Perencanaan',
            'short' => 'Perangkat / Adm',
            'description' => 'Evaluasi kelengkapan perangkat pembelajaran sebelum atau saat observasi kelas.',
            'items' => $step1Jenis,
        ],
        2 => [
            'title' => $jadwal->isKurmer() ? 'Step 2: Observasi Kelas (PMM)' : 'Step 2: Observasi Kelas & Kinerja',
            'short' => 'Observasi Kelas',
            'description' => 'Nilai praktik pembelajaran dan interaksi kelas secara objektif atau gunakan Bulk-Skor.',
            'items' => $step2Jenis,
        ],
    ];

    $requestedStep = (int) request()->query('step', 1);
    $initialStep = in_array($requestedStep, [1, 2, 3], true) ? $requestedStep : 1;
    $fokusIds = $fokusDipilihIds ?? [];
@endphp

<!-- Header Jadwal & Live Score Summary -->
<section class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-4">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-1">
            <div class="flex flex-wrap items-center gap-2">
                <span class="px-2 py-0.5 rounded border-2 border-black bg-[#FFD43B] text-[11px] font-black uppercase">
                    {{ $jadwal->isKurmer() ? 'Kurikulum Merdeka / PMM' : 'Supervisi Reguler' }}
                </span>
                <span class="text-xs font-bold text-slate-600 uppercase">
                    {{ $jadwal->academicYear?->name }} ({{ ucfirst($jadwal->academicYear?->semester ?? '') }})
                </span>
            </div>
            <h2 class="text-lg sm:text-xl font-black font-heading text-slate-900">
                {{ $jadwal->teacher?->user?->name }} — {{ $jadwal->subject?->name ?? 'Mapel Umum' }}
            </h2>
            <p class="text-xs font-semibold text-slate-600">
                Kelas: {{ $jadwal->schoolClass?->name ?? '-' }} • Tanggal: {{ $jadwal->tanggal?->format('d/m/Y') }} •
                Status: <span class="font-black text-slate-900">{{ $jadwal->statusEnum()->label() }}</span>
            </p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 w-full lg:w-auto">
            <div class="px-3 py-2 rounded-lg border-2 border-black bg-slate-50">
                <div class="text-[10px] font-bold text-slate-500 uppercase">Aspek Dinilai</div>
                <div id="live-progress" class="text-sm font-black tabular-nums text-slate-900">0 / 0</div>
            </div>
            <div class="px-3 py-2 rounded-lg border-2 border-black bg-slate-50">
                <div class="text-[10px] font-bold text-slate-500 uppercase">Total Skor</div>
                <div id="live-total-skor" class="text-sm font-black tabular-nums text-slate-900">0 / 0</div>
            </div>
            <div class="px-3 py-2 rounded-lg border-2 border-black bg-[#FFD43B]">
                <div class="text-[10px] font-bold text-slate-800 uppercase">Nilai Akhir</div>
                <div id="live-nilai-akhir" class="text-sm font-black tabular-nums text-black">0.00</div>
            </div>
            <div class="px-3 py-2 rounded-lg border-2 border-black bg-emerald-100">
                <div class="text-[10px] font-bold text-emerald-900 uppercase">Predikat</div>
                <div id="live-predikat" class="text-sm font-black text-emerald-950">Belum Dinilai</div>
            </div>
        </div>
    </div>

    <!-- Stepper Navigation -->
    <nav id="wizard-stepper" aria-label="Tahapan Penilaian" class="pt-3 border-t-2 border-slate-200">
        <ol class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
            <li>
                <button type="button" data-step-nav="1" aria-current="{{ $initialStep === 1 ? 'step' : 'false' }}"
                    class="w-full text-left px-3.5 py-2.5 rounded-lg border-2 border-black transition-all cursor-pointer flex items-center justify-between gap-2 shadow-[2px_2px_0px_0px_#000]">
                    <span class="text-xs font-black">1. {{ $stepConfigs[1]['short'] }}</span>
                    <span data-step-count="1" class="px-2 py-0.5 rounded border border-black bg-white text-[11px] font-black tabular-nums">0/0</span>
                </button>
            </li>
            <li>
                <button type="button" data-step-nav="2" aria-current="{{ $initialStep === 2 ? 'step' : 'false' }}"
                    class="w-full text-left px-3.5 py-2.5 rounded-lg border-2 border-black transition-all cursor-pointer flex items-center justify-between gap-2 shadow-[2px_2px_0px_0px_#000]">
                    <span class="text-xs font-black">2. {{ $stepConfigs[2]['short'] }}</span>
                    <span data-step-count="2" class="px-2 py-0.5 rounded border border-black bg-white text-[11px] font-black tabular-nums">0/0</span>
                </button>
            </li>
            <li>
                <button type="button" data-step-nav="3" aria-current="{{ $initialStep === 3 ? 'step' : 'false' }}"
                    class="w-full text-left px-3.5 py-2.5 rounded-lg border-2 border-black transition-all cursor-pointer flex items-center justify-between gap-2 shadow-[2px_2px_0px_0px_#000]">
                    <span class="text-xs font-black">3. Foto Bukti &amp; Selesai</span>
                    <span data-step-count="3" class="px-2 py-0.5 rounded border border-black bg-white text-[11px] font-black tabular-nums">{{ $jadwal->fotoBuktis->count() }}/5</span>
                </button>
            </li>
        </ol>
    </nav>

    <!-- Live Status Notification -->
    <div id="wizard-status-banner" role="status" aria-live="polite" hidden
        class="p-3 rounded-lg border-2 border-black text-xs font-bold flex items-center justify-between gap-2"></div>
</section>

<!-- Step 1 & Step 2 Panels -->
@foreach($stepConfigs as $stepNumber => $stepMeta)
    @php
        /** @var \Illuminate\Support\Collection<int, \App\Models\Supervisi\SupervisiJenisPenilaian> $stepItems */
        $stepItems = $stepMeta['items'];
        $stepMaxScore = $stepItems->max(function (SupervisiJenisPenilaian $item): int {
            $aspectMax = $item->aspekPenilaians->max(fn ($a): int => $a->maxSkor());

            return max((int) ($aspectMax ?? 0), ((int) $item->skor_maksimal === 3 ? 3 : 4));
        }) ?: 4;
        $hasMixedScale = $stepItems->contains(fn (SupervisiJenisPenilaian $item): bool => (int) $item->skor_maksimal === 3)
            && $stepItems->contains(fn (SupervisiJenisPenilaian $item): bool => (int) $item->skor_maksimal >= 4);
    @endphp

    <section id="step-panel-{{ $stepNumber }}" data-step-panel="{{ $stepNumber }}" {{ $initialStep === $stepNumber ? '' : 'hidden' }} class="space-y-5">
        <div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 id="step-heading-{{ $stepNumber }}" tabindex="-1" class="font-heading font-black text-base text-slate-900 focus:outline-none">
                        {{ $stepMeta['title'] }}
                    </h3>
                    <p class="text-xs font-medium text-slate-600 mt-0.5">{{ $stepMeta['description'] }}</p>
                </div>
                <span class="px-2.5 py-1 rounded-lg border-2 border-black bg-slate-100 text-xs font-black">
                    {{ $stepItems->count() }} Instrumen
                </span>
            </div>

            @if($stepItems->isNotEmpty())
                <!-- Bulk-Skor Global Step -->
                <div class="p-4 rounded-xl border-2 border-black bg-[#FFF9DB] space-y-2.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <div class="text-xs font-black text-slate-900">⚡ Penilaian Global (Bulk-Skor Step {{ $stepNumber }})</div>
                            <p class="text-[11px] font-semibold text-slate-700">
                                Klik satu angka untuk mengisi skor &amp; catatan otomatis pada seluruh instrumen di step ini.
                                @if($stepMaxScore === 3)
                                    Skala PMM: 1 = Belum Dilakukan, 2 = Belum Efektif, 3 = Efektif.
                                @elseif($hasMixedScale)
                                    Aspek berskala maks. 3 otomatis disesuaikan ke 3 bila Anda memilih 4.
                                @else
                                    Skala 1–4: 1 = Kurang, 2 = Cukup, 3 = Baik, 4 = Sangat Baik.
                                @endif
                            </p>
                        </div>
                        <button type="button" data-bulk-step-reset-notes="{{ $stepNumber }}"
                            class="px-3 py-1.5 rounded-lg border-2 border-black bg-white hover:bg-slate-100 text-[11px] font-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                            ↻ Refresh Catatan Step Ini
                        </button>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-black text-slate-800 mr-1">Set Semua Skor Step {{ $stepNumber }}:</span>
                        @for($s = 1; $s <= $stepMaxScore; $s++)
                            @php
                                $globalLabel = match (true) {
                                    $stepMaxScore === 3 && $s === 1 => '1 · Belum',
                                    $stepMaxScore === 3 && $s === 2 => '2 · Kurang Efektif',
                                    $stepMaxScore === 3 && $s === 3 => '3 · Efektif',
                                    $s === 1 => '1 · Kurang',
                                    $s === 2 => '2 · Cukup',
                                    $s === 3 => '3 · Baik',
                                    default => '4 · Sangat Baik',
                                };
                            @endphp
                            <button type="button" data-bulk-step="{{ $stepNumber }}" data-score="{{ $s }}"
                                class="min-h-[40px] px-3.5 py-1.5 rounded-lg border-2 border-black bg-white hover:bg-[#FFD43B] text-xs font-black shadow-[2px_2px_0px_0px_#000] cursor-pointer transition-colors">
                                {{ $globalLabel }}
                            </button>
                        @endfor
                    </div>
                </div>
            @endif
        </div>

        @if($stepItems->isEmpty())
            <div class="bg-white border-2 border-black rounded-xl p-6 shadow-[4px_4px_0px_0px_#000] text-center space-y-2">
                <p class="text-sm font-black text-slate-800">Tidak ada instrumen aktif pada step ini.</p>
                <p class="text-xs text-slate-600">Anda dapat langsung melanjutkan ke tahap berikutnya.</p>
            </div>
        @else
            @foreach($stepItems as $idx => $jenis)
                @php
                    $hasil = $existingHasils->get($jenis->id);
                    $detailsMap = $existingDetails[$jenis->id] ?? collect();
                    $jenisMaxSkor = $jenis->aspekPenilaians->max(fn ($a): int => $a->maxSkor())
                        ?: ((int) $jenis->skor_maksimal === 3 ? 3 : 4);
                    $filledInJenis = $detailsMap->count();
                    $totalInJenis = $jenis->aspekPenilaians->count();
                    $isInitiallyOpen = $idx === 0;
                @endphp

                <article class="bg-white border-2 border-black rounded-xl shadow-[4px_4px_0px_0px_#000] overflow-hidden" data-jenis-card="{{ $jenis->id }}">
                    <div class="p-4 sm:p-5 bg-slate-50 border-b-2 border-black flex flex-wrap items-center justify-between gap-3">
                        <button type="button"
                            data-accordion-toggle="{{ $jenis->id }}"
                            aria-expanded="{{ $isInitiallyOpen ? 'true' : 'false' }}"
                            aria-controls="instrument-body-{{ $jenis->id }}"
                            class="flex-1 text-left flex items-start gap-3 cursor-pointer group focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700 rounded">
                            <span data-accordion-icon="{{ $jenis->id }}" class="mt-0.5 inline-flex items-center justify-center w-6 h-6 rounded border-2 border-black bg-white text-xs font-black shrink-0">
                                {{ $isInitiallyOpen ? '−' : '+' }}
                            </span>
                            <div class="min-w-0">
                                <h4 id="instrument-title-{{ $jenis->id }}" class="font-heading font-black text-sm text-slate-900 group-hover:underline">
                                    {{ $jenis->nama }}
                                </h4>
                                @if($jenis->deskripsi)
                                    <p class="text-xs text-slate-600 mt-0.5">{{ $jenis->deskripsi }}</p>
                                @endif
                            </div>
                        </button>

                        <div class="flex flex-wrap items-center gap-2">
                            <span data-jenis-progress="{{ $jenis->id }}" class="px-2.5 py-1 rounded-lg border-2 border-black bg-white text-[11px] font-black tabular-nums">
                                {{ $filledInJenis }}/{{ $totalInJenis }} aspek
                            </span>
                            <span data-jenis-score="{{ $jenis->id }}" class="px-2.5 py-1 rounded-lg border-2 border-black bg-[#FFD43B] text-[11px] font-black tabular-nums">
                                {{ $hasil && $hasil->skor_maksimal > 0 ? number_format((float) $hasil->nilai_akhir, 2) . ' (' . $hasil->ketercapaian . ')' : 'Belum dinilai' }}
                            </span>
                            <span data-jenis-save-state="{{ $jenis->id }}" class="px-2.5 py-1 rounded-lg border-2 border-black {{ $hasil ? 'bg-emerald-100 text-emerald-950' : 'bg-slate-200 text-slate-700' }} text-[11px] font-black">
                                {{ $hasil ? 'Tersimpan' : 'Belum disimpan' }}
                            </span>
                        </div>
                    </div>

                    <div id="instrument-body-{{ $jenis->id }}" {{ $isInitiallyOpen ? '' : 'hidden' }} class="p-4 sm:p-5 space-y-4">
                        <form action="{{ $saveRoute }}" method="POST"
                            data-komponen-form="{{ $jenis->id }}"
                            data-step="{{ $stepNumber }}"
                            data-jenis-nama="{{ $jenis->nama }}"
                            data-kategori="{{ $jenis->kategori_supervisi }}"
                            aria-labelledby="instrument-title-{{ $jenis->id }}"
                            class="space-y-4">
                            @csrf
                            <input type="hidden" name="jenis_penilaian_id" value="{{ $jenis->id }}">

                            <!-- Bulk-Skor Per Instrumen -->
                            <div class="p-3 rounded-lg border-2 border-black bg-blue-50 flex flex-wrap items-center justify-between gap-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-black text-slate-900">Bulk-Skor Instrumen:</span>
                                    @for($s = 1; $s <= $jenisMaxSkor; $s++)
                                        <button type="button" data-bulk-jenis="{{ $jenis->id }}" data-score="{{ $s }}"
                                            class="min-w-[38px] min-h-[38px] px-2.5 py-1 rounded-lg border-2 border-black bg-white hover:bg-[#FFD43B] text-xs font-black shadow-[1.5px_1.5px_0px_0px_#000] cursor-pointer">
                                            {{ $s }}
                                        </button>
                                    @endfor
                                </div>
                                <button type="button" data-bulk-jenis-reset-notes="{{ $jenis->id }}"
                                    class="px-2.5 py-1.5 rounded-lg border-2 border-black bg-white hover:bg-slate-100 text-[11px] font-black cursor-pointer">
                                    ↻ Reset Catatan Instrumen
                                </button>
                            </div>

                            <!-- Daftar Aspek -->
                            <div class="space-y-3">
                                @foreach($jenis->aspekPenilaians as $aspek)
                                    @php
                                        $maxSkor = $aspek->maxSkor();
                                        $detail = $detailsMap->get($aspek->id);
                                        $selectedSkor = $detail?->skor;
                                        $selectedCatatan = $detail?->catatan;
                                        $isFokusUtama = in_array((int) $aspek->id, $fokusIds, true);
                                    @endphp
                                    <fieldset data-aspek-card="{{ $aspek->id }}"
                                        data-jenis-id="{{ $jenis->id }}"
                                        data-max-skor="{{ $maxSkor }}"
                                        data-nama-aspek="{{ $aspek->nama_aspek }}"
                                        data-fokus-perilaku="{{ $aspek->fokus_perilaku ?? '' }}"
                                        class="p-4 rounded-xl border-2 border-black {{ $isFokusUtama ? 'bg-amber-50/70' : 'bg-slate-50' }} space-y-3">
                                        <legend class="sr-only">{{ $aspek->urutan }}. {{ $aspek->nama_aspek }}</legend>

                                        <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                                            <div class="flex-1 space-y-1.5">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="text-xs font-black text-slate-900">{{ $aspek->urutan }}. {{ $aspek->nama_aspek }}</span>
                                                    @if($isFokusUtama)
                                                        <span class="px-2 py-0.5 rounded border border-black bg-[#FFD43B] text-[10px] font-black uppercase">
                                                            ⭐ Fokus Utama (Form A)
                                                        </span>
                                                    @endif
                                                </div>
                                                @if($aspek->fokus_perilaku)
                                                    <div class="text-xs font-semibold text-slate-700">{{ $aspek->fokus_perilaku }}</div>
                                                @endif
                                                @if($aspek->perilaku_dianjurkan)
                                                    <div class="text-[11px] text-emerald-950 bg-emerald-50 border border-emerald-300 rounded-lg p-2.5 whitespace-pre-line">
                                                        <strong>Dianjurkan:</strong><br>{{ $aspek->perilaku_dianjurkan }}
                                                    </div>
                                                @endif
                                                @if($aspek->perilaku_dihindari)
                                                    <div class="text-[11px] text-rose-950 bg-rose-50 border border-rose-300 rounded-lg p-2.5 whitespace-pre-line">
                                                        <strong>Dihindari:</strong><br>{{ $aspek->perilaku_dihindari }}
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="w-full lg:w-[360px] shrink-0 space-y-2.5">
                                                <div>
                                                    <div class="text-[11px] font-black text-slate-700 mb-1.5">Pilih Skor (1–{{ $maxSkor }})</div>
                                                    <div class="flex flex-wrap gap-1.5">
                                                        @for($s = 1; $s <= $maxSkor; $s++)
                                                            @php
                                                                $radioId = "skor_{$jenis->id}_{$aspek->id}_{$s}";
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
                                                                    class="inline-flex flex-col items-center justify-center min-w-[54px] min-h-[44px] px-2.5 py-1 rounded-lg border-2 border-black bg-white hover:bg-amber-100 peer-checked:bg-[#FFD43B] peer-focus-visible:ring-2 peer-focus-visible:ring-blue-700 peer-focus-visible:ring-offset-1 shadow-[1.5px_1.5px_0px_0px_#000] cursor-pointer select-none transition-colors">
                                                                    <span class="text-xs font-black leading-none">{{ $s }}</span>
                                                                    <span class="text-[10px] font-bold text-slate-700 mt-0.5 leading-none">{{ $scoreWord }}</span>
                                                                </label>
                                                            </div>
                                                        @endfor
                                                    </div>
                                                </div>

                                                <div class="space-y-1">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <label for="catatan_{{ $jenis->id }}_{{ $aspek->id }}" class="text-[11px] font-black text-slate-700">
                                                            Catatan Aspek
                                                        </label>
                                                        <div class="flex items-center gap-1.5">
                                                            <span data-note-mode="{{ $aspek->id }}" class="px-1.5 py-0.5 rounded border border-black bg-white text-[10px] font-bold">
                                                                {{ filled($selectedCatatan) ? 'Tersimpan' : 'Siap Auto' }}
                                                            </span>
                                                            <button type="button" data-note-regen="{{ $aspek->id }}" title="Buat ulang catatan otomatis sesuai skor"
                                                                class="px-2 py-0.5 rounded border border-black bg-white hover:bg-amber-100 text-[10px] font-black cursor-pointer">
                                                                ↻ Auto
                                                            </button>
                                                            <button type="button" data-note-clear="{{ $aspek->id }}" title="Kosongkan catatan"
                                                                class="px-2 py-0.5 rounded border border-black bg-white hover:bg-rose-100 text-[10px] font-black cursor-pointer">
                                                                ✕
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <textarea id="catatan_{{ $jenis->id }}_{{ $aspek->id }}"
                                                        name="catatan[{{ $aspek->id }}]"
                                                        rows="2"
                                                        data-note-input="{{ $aspek->id }}"
                                                        placeholder="Catatan otomatis muncul saat skor dipilih, atau ketik manual..."
                                                        class="w-full px-2.5 py-1.5 text-xs border-2 border-black rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-700">{{ $selectedCatatan }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>
                                @endforeach
                            </div>

                            <div>
                                <label class="block text-xs font-black mb-1" for="rekomendasi_{{ $jenis->id }}">Rekomendasi Komponen</label>
                                <textarea id="rekomendasi_{{ $jenis->id }}" name="rekomendasi" rows="2" data-rekomendasi-input
                                    placeholder="Catatan rekomendasi khusus untuk komponen ini (opsional)"
                                    class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">{{ $hasil?->rekomendasi }}</textarea>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit"
                                    class="px-4 py-2 bg-[#5294FF] hover:bg-blue-500 text-white font-black text-xs rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                                    💾 Simpan Komponen Ini
                                </button>
                            </div>
                        </form>
                    </div>
                </article>
            @endforeach
        @endif

        <!-- Navigasi Bawah Step 1 & Step 2 -->
        <div class="bg-white border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000] flex flex-wrap items-center justify-between gap-3">
            @if($stepNumber === 1)
                <span class="text-xs font-semibold text-slate-600">Komponen pada Step 1 otomatis tersimpan saat Anda melanjutkan ke Step 2.</span>
                <button type="button" data-go-step="2" enterkeyhint="next"
                    class="px-5 py-2.5 bg-[#FFD43B] hover:bg-amber-300 text-black font-black text-xs rounded-lg border-2 border-black shadow-[3px_3px_0px_0px_#000] cursor-pointer">
                    Simpan &amp; Lanjut ke Step 2 (Observasi) →
                </button>
            @else
                <button type="button" data-go-step="1" enterkeyhint="previous"
                    class="px-4 py-2.5 bg-white hover:bg-slate-100 text-black font-black text-xs rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                    ← Kembali ke Step 1
                </button>
                <button type="button" data-go-step="3" enterkeyhint="next"
                    class="px-5 py-2.5 bg-[#FFD43B] hover:bg-amber-300 text-black font-black text-xs rounded-lg border-2 border-black shadow-[3px_3px_0px_0px_#000] cursor-pointer">
                    Simpan &amp; Lanjut ke Step 3 (Foto Bukti &amp; Selesai) →
                </button>
            @endif
        </div>
    </section>
@endforeach

<!-- Step 3: Dokumentasi Foto Bukti & Penyelesaian -->
<section id="step-panel-3" data-step-panel="3" {{ $initialStep === 3 ? '' : 'hidden' }} class="space-y-5">
    <div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b-2 border-black pb-3">
            <div>
                <h3 id="step-heading-3" tabindex="-1" class="font-heading font-black text-base text-slate-900 focus:outline-none">
                    Step 3: Ringkasan Nilai, Foto Bukti &amp; Penyelesaian
                </h3>
                <p class="text-xs font-medium text-slate-600 mt-0.5">
                    Periksa kembali kelengkapan seluruh instrumen, unggah foto dokumentasi (opsional), lalu selesaikan observasi.
                </p>
            </div>
        </div>

        <!-- Ringkasan Status Instrumen -->
        <div class="space-y-2">
            <h4 class="text-xs font-black uppercase tracking-wider text-slate-700">Ringkasan Komponen Penilaian</h4>
            <div id="step3-summary-list" class="grid grid-cols-1 md:grid-cols-2 gap-3"></div>
        </div>
    </div>

    <!-- Foto Bukti -->
    <div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h4 class="font-heading font-black text-sm">📸 Dokumentasi Foto Bukti (Maks. 5 Foto)</h4>
            <span id="foto-quota-badge" class="px-2.5 py-1 rounded-lg border-2 border-black bg-slate-100 text-xs font-black tabular-nums">
                {{ $jadwal->fotoBuktis->count() }} / 5 Foto
            </span>
        </div>

        <form id="foto-upload-form" action="{{ $uploadFotoRoute }}" method="POST" enctype="multipart/form-data" class="flex flex-wrap items-end gap-3">
            @csrf
            <div>
                <label for="photos" class="block text-xs font-bold mb-1">Pilih Foto (JPG/PNG/WebP)</label>
                <input id="photos" type="file" name="photos[]" multiple accept="image/*" required class="text-xs border-2 border-black rounded-lg p-1.5 bg-white">
            </div>
            <div class="flex-1 min-w-[200px]">
                <label for="keterangan_foto" class="block text-xs font-bold mb-1">Keterangan</label>
                <input id="keterangan_foto" type="text" name="keterangan" placeholder="Dokumentasi observasi pembelajaran" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">
            </div>
            <button type="submit" id="btn-upload-foto" class="px-4 py-2 bg-[#FFD43B] hover:bg-amber-300 text-black font-black text-xs rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                Unggah Foto
            </button>
        </form>

        <div id="foto-gallery-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3" {{ $jadwal->fotoBuktis->isEmpty() ? 'hidden' : '' }}>
            @foreach($jadwal->fotoBuktis as $foto)
                <div class="border-2 border-black rounded-lg overflow-hidden bg-slate-50" data-foto-card="{{ $foto->id }}">
                    <img src="{{ $foto->url }}" alt="{{ $foto->keterangan ?? 'Foto bukti' }}" class="w-full h-28 object-cover">
                    <div class="p-2 flex items-center justify-between gap-2">
                        <span class="text-[11px] font-semibold truncate">{{ $foto->keterangan ?? 'Bukti' }}</span>
                        <form action="{{ route($deleteFotoRouteName, $foto) }}" method="POST" data-foto-delete-form="{{ $foto->id }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-[11px] font-black text-rose-700 hover:underline cursor-pointer">Hapus</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
        <p id="foto-empty-hint" class="text-xs text-slate-500 font-medium" {{ $jadwal->fotoBuktis->isNotEmpty() ? 'hidden' : '' }}>
            Belum ada foto bukti diunggah.
        </p>
    </div>

    <!-- Navigasi Bawah Step 3 & Selesai -->
    <div class="bg-white border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000] flex flex-wrap items-center justify-between gap-3">
        <button type="button" data-go-step="2" enterkeyhint="previous"
            class="px-4 py-2.5 bg-white hover:bg-slate-100 text-black font-black text-xs rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
            ← Kembali ke Step 2
        </button>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" id="btn-open-complete-dialog" enterkeyhint="done"
                class="px-5 py-2.5 bg-[#20C997] hover:bg-emerald-400 text-black font-black text-xs rounded-lg border-2 border-black shadow-[3px_3px_0px_0px_#000] cursor-pointer">
                ✅ Selesaikan Penilaian Observasi
            </button>
        </div>
    </div>
</section>

<!-- Dialog Konfirmasi Penyelesaian -->
<dialog id="complete-confirm-dialog" aria-labelledby="complete-dialog-title"
    class="m-auto w-full max-w-md rounded-xl border-4 border-black bg-white p-5 shadow-[6px_6px_0px_0px_#000]">
    <div class="space-y-4">
        <h3 id="complete-dialog-title" class="font-heading font-black text-base text-slate-900">
            Selesaikan Penilaian Observasi?
        </h3>
        <p id="complete-dialog-summary" class="text-xs font-medium text-slate-700 leading-relaxed"></p>
        <div class="flex flex-wrap items-center justify-end gap-2 pt-2 border-t-2 border-slate-200">
            <button type="button" id="btn-cancel-complete"
                class="px-4 py-2 rounded-lg border-2 border-black bg-white hover:bg-slate-100 text-xs font-black cursor-pointer">
                Batal
            </button>
            <form id="complete-penilaian-form" action="{{ $completeRoute }}" method="POST">
                @csrf
                <button type="submit" id="btn-confirm-complete" autofocus
                    class="px-4 py-2 rounded-lg border-2 border-black bg-[#20C997] hover:bg-emerald-400 text-black text-xs font-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                    Ya, Simpan Semua &amp; Selesaikan
                </button>
            </form>
        </div>
    </div>
</dialog>
@endsection

@push('scripts')
<script>
(() => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const initialStep = @json($initialStep);

    const statusBanner = document.getElementById('wizard-status-banner');
    const stepButtons = Array.from(document.querySelectorAll('[data-step-nav]'));
    const stepPanels = Array.from(document.querySelectorAll('[data-step-panel]'));
    const componentForms = Array.from(document.querySelectorAll('[data-komponen-form]'));

    const dirtyForms = new Set();
    let currentStep = initialStep;

    const AUTO_NOTE_PREFIXES = [
        'Aspek ',
        'Pelaksanaan ',
        'Penyusunan ',
        'Aspek sudah ',
        'Perangkat pembelajaran ',
        'Implementasi aspek ',
        'Diperlukan pendampingan ',
        'Aspek belum ',
    ];

    function showBanner(message, tone = 'info') {
        if (!statusBanner) {
            return;
        }
        statusBanner.textContent = message;
        statusBanner.classList.remove(
            'bg-emerald-100', 'text-emerald-950',
            'bg-amber-100', 'text-amber-950',
            'bg-rose-100', 'text-rose-950',
            'bg-blue-100', 'text-blue-950'
        );
        if (tone === 'success') {
            statusBanner.classList.add('bg-emerald-100', 'text-emerald-950');
        } else if (tone === 'warning') {
            statusBanner.classList.add('bg-amber-100', 'text-amber-950');
        } else if (tone === 'error') {
            statusBanner.classList.add('bg-rose-100', 'text-rose-950');
        } else {
            statusBanner.classList.add('bg-blue-100', 'text-blue-950');
        }
        statusBanner.hidden = false;
    }

    function cleanLabel(text) {
        return (text || '')
            .replace(/^\d+\.\s*/, '')
            .replace(/\s+/g, ' ')
            .trim()
            .replace(/[.]+$/, '');
    }

    function isAutoGeneratedNote(value) {
        const trimmed = (value || '').trim();
        if (trimmed === '') {
            return true;
        }

        return AUTO_NOTE_PREFIXES.some((prefix) => trimmed.startsWith(prefix));
    }

    function generateContextualNote(score, maxSkor, namaAspek, fokusPerilaku, kategori, jenisNama = '') {
        const num = parseInt(score, 10);
        if (!num) {
            return '';
        }

        const label = cleanLabel(fokusPerilaku || namaAspek || 'aspek pembelajaran');
        const isPerangkat = kategori === 'perangkat_ajar'
            || (jenisNama || '').toLowerCase().includes('administrasi');

        if (maxSkor === 3) {
            if (num === 3) {
                return `Pelaksanaan ${label} dilakukan secara konsisten dan efektif sehingga berdampak positif terhadap proses belajar murid.`;
            }
            if (num === 2) {
                return `Aspek ${label} sudah dilakukan selama observasi, namun efektivitas dan konsistensinya masih dapat ditingkatkan.`;
            }

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
        if (num === 2) {
            return `Aspek sudah tampak pada ${label}, namun masih memerlukan penguatan agar pelaksanaannya lebih optimal.`;
        }

        return `Diperlukan pendampingan dan perbaikan terstruktur pada ${label} sebelum supervisi berikutnya.`;
    }

    function calculatePredikat(nilai) {
        if (nilai >= 86) {
            return 'Baik Sekali';
        }
        if (nilai >= 70) {
            return 'Baik';
        }
        if (nilai >= 55) {
            return 'Cukup';
        }

        return 'Kurang';
    }

    function updateNoteModeBadge(card, isManual, hasText) {
        const badge = card.querySelector('[data-note-mode]');
        if (!badge) {
            return;
        }
        if (!hasText) {
            badge.textContent = 'Siap Auto';
        } else {
            badge.textContent = isManual ? 'Manual' : 'Auto';
        }
    }

    function applyScoreToCard(card, rawScore, kategori, { overwriteManual = false, jenisNama = '' } = {}) {
        const maxSkor = parseInt(card.dataset.maxSkor || '4', 10);
        const clampedScore = Math.max(1, Math.min(maxSkor, parseInt(rawScore, 10) || 1));

        const radio = card.querySelector(`input[type="radio"][value="${clampedScore}"]`);
        if (radio) {
            radio.checked = true;
        }

        const noteInput = card.querySelector('[data-note-input]');
        if (noteInput) {
            const isManual = noteInput.dataset.manual === 'true';
            if (overwriteManual || !isManual || noteInput.value.trim() === '') {
                const generated = generateContextualNote(
                    clampedScore,
                    maxSkor,
                    card.dataset.namaAspek,
                    card.dataset.fokusPerilaku,
                    kategori,
                    jenisNama
                );
                noteInput.value = generated;
                noteInput.dataset.manual = 'false';
                updateNoteModeBadge(card, false, generated.trim() !== '');
            }
        }
    }

    function setFormSaveState(jenisId, stateLabel, toneClass) {
        const badge = document.querySelector(`[data-jenis-save-state="${jenisId}"]`);
        if (!badge) {
            return;
        }
        badge.textContent = stateLabel;
        badge.classList.remove('bg-emerald-100', 'text-emerald-950', 'bg-amber-100', 'text-amber-950', 'bg-slate-200', 'text-slate-700');
        toneClass.split(' ').forEach((cls) => badge.classList.add(cls));
    }

    function markFormDirty(form) {
        const jenisId = form.dataset.komponenForm;
        if (!jenisId) {
            return;
        }
        dirtyForms.add(jenisId);
        setFormSaveState(jenisId, 'Belum disimpan', 'bg-amber-100 text-amber-950');
    }

    function recalculateAll() {
        let grandTotal = 0;
        let grandMax = 0;
        let grandFilled = 0;
        let grandAspects = 0;

        const stepStats = {
            1: { filled: 0, total: 0 },
            2: { filled: 0, total: 0 },
        };

        const summaryContainer = document.getElementById('step3-summary-list');
        if (summaryContainer) {
            summaryContainer.replaceChildren();
        }

        componentForms.forEach((form) => {
            const jenisId = form.dataset.komponenForm;
            const stepNum = parseInt(form.dataset.step || '1', 10);
            const cards = Array.from(form.querySelectorAll('[data-aspek-card]'));

            let formTotal = 0;
            let formMax = 0;
            let formFilled = 0;

            cards.forEach((card) => {
                grandAspects += 1;
                if (stepStats[stepNum]) {
                    stepStats[stepNum].total += 1;
                }

                const maxSkor = parseInt(card.dataset.maxSkor || '4', 10);
                const checked = card.querySelector('input[type="radio"]:checked');
                if (checked) {
                    const val = parseInt(checked.value, 10) || 0;
                    formTotal += val;
                    formMax += maxSkor;
                    formFilled += 1;
                    grandFilled += 1;
                    if (stepStats[stepNum]) {
                        stepStats[stepNum].filled += 1;
                    }
                }
            });

            grandTotal += formTotal;
            grandMax += formMax;

            const jenisNilai = formMax > 0 ? (formTotal / formMax) * 100 : 0;
            const jenisPredikat = formMax > 0 ? calculatePredikat(jenisNilai) : null;

            const progressBadge = document.querySelector(`[data-jenis-progress="${jenisId}"]`);
            if (progressBadge) {
                progressBadge.textContent = `${formFilled}/${cards.length} aspek`;
            }

            const scoreBadge = document.querySelector(`[data-jenis-score="${jenisId}"]`);
            if (scoreBadge) {
                scoreBadge.textContent = formMax > 0
                    ? `${jenisNilai.toFixed(2)} (${jenisPredikat})`
                    : 'Belum dinilai';
            }

            if (summaryContainer) {
                const summaryItem = document.createElement('div');
                summaryItem.className = 'p-3.5 rounded-lg border-2 border-black bg-slate-50 flex items-center justify-between gap-3';

                const left = document.createElement('div');
                left.className = 'min-w-0';

                const title = document.createElement('div');
                title.className = 'text-xs font-black text-slate-900 truncate';
                title.textContent = form.dataset.jenisNama || 'Komponen';

                const sub = document.createElement('div');
                sub.className = 'text-[11px] font-semibold text-slate-600 mt-0.5';
                sub.textContent = `Terisi ${formFilled}/${cards.length} aspek • ${dirtyForms.has(jenisId) ? 'Perubahan menunggu disimpan' : (formFilled > 0 ? 'Siap' : 'Belum diisi')}`;

                left.appendChild(title);
                left.appendChild(sub);

                const right = document.createElement('span');
                right.className = `px-2.5 py-1 rounded-lg border-2 border-black text-[11px] font-black shrink-0 ${formMax > 0 ? 'bg-[#FFD43B] text-black' : 'bg-white text-slate-600'}`;
                right.textContent = formMax > 0 ? `${jenisNilai.toFixed(2)} (${jenisPredikat})` : '0.00';

                summaryItem.appendChild(left);
                summaryItem.appendChild(right);
                summaryContainer.appendChild(summaryItem);
            }
        });

        const grandNilai = grandMax > 0 ? (grandTotal / grandMax) * 100 : 0;

        const elProgress = document.getElementById('live-progress');
        const elTotal = document.getElementById('live-total-skor');
        const elNilai = document.getElementById('live-nilai-akhir');
        const elPredikat = document.getElementById('live-predikat');

        if (elProgress) {
            elProgress.textContent = `${grandFilled} / ${grandAspects}`;
        }
        if (elTotal) {
            elTotal.textContent = `${grandTotal} / ${grandMax}`;
        }
        if (elNilai) {
            elNilai.textContent = grandNilai.toFixed(2);
        }
        if (elPredikat) {
            elPredikat.textContent = grandMax > 0 ? calculatePredikat(grandNilai) : 'Belum Dinilai';
        }

        [1, 2].forEach((stepNum) => {
            const countBadge = document.querySelector(`[data-step-count="${stepNum}"]`);
            if (countBadge) {
                countBadge.textContent = `${stepStats[stepNum].filled}/${stepStats[stepNum].total}`;
            }
        });
    }

    async function saveComponentForm(form, { silentIfEmpty = false } = {}) {
        const jenisId = form.dataset.komponenForm;
        const anyChecked = Boolean(form.querySelector('input[type="radio"]:checked'));
        if (silentIfEmpty && !anyChecked && !dirtyForms.has(jenisId)) {
            return true;
        }

        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
        }

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: formData,
            });

            const payload = await response.json();
            if (!response.ok || payload.status === 'error') {
                showBanner(payload.message || 'Gagal menyimpan komponen penilaian.', 'error');
                return false;
            }

            dirtyForms.delete(jenisId);
            setFormSaveState(jenisId, 'Tersimpan', 'bg-emerald-100 text-emerald-950');
            return true;
        } catch (error) {
            showBanner('Koneksi terganggu saat menyimpan komponen. Coba lagi.', 'error');
            return false;
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
            }
        }
    }

    async function saveDirtyFormsInStep(stepNumber) {
        const forms = componentForms.filter((form) => parseInt(form.dataset.step || '1', 10) === stepNumber);
        let savedCount = 0;

        for (const form of forms) {
            const jenisId = form.dataset.komponenForm;
            if (!dirtyForms.has(jenisId)) {
                continue;
            }
            const ok = await saveComponentForm(form);
            if (!ok) {
                return false;
            }
            savedCount += 1;
        }

        if (savedCount > 0) {
            showBanner(`${savedCount} komponen pada Step ${stepNumber} berhasil disimpan otomatis.`, 'success');
            recalculateAll();
        }

        return true;
    }

    async function goToStep(targetStep, { saveCurrent = true } = {}) {
        if (targetStep === currentStep) {
            return;
        }

        if (saveCurrent && (currentStep === 1 || currentStep === 2)) {
            const ok = await saveDirtyFormsInStep(currentStep);
            if (!ok) {
                return;
            }
        }

        currentStep = targetStep;
        stepPanels.forEach((panel) => {
            const panelStep = parseInt(panel.dataset.stepPanel || '0', 10);
            panel.hidden = panelStep !== currentStep;
        });

        stepButtons.forEach((btn) => {
            const btnStep = parseInt(btn.dataset.stepNav || '0', 10);
            const active = btnStep === currentStep;
            btn.setAttribute('aria-current', active ? 'step' : 'false');
            btn.classList.toggle('bg-[#FFD43B]', active);
            btn.classList.toggle('bg-white', !active);
        });

        recalculateAll();

        const heading = document.getElementById(`step-heading-${currentStep}`);
        if (heading) {
            heading.focus({ preventScroll: true });
        }
        document.getElementById('wizard-stepper')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // Initialize existing notes state & event bindings
    componentForms.forEach((form) => {
        const kategori = form.dataset.kategori || 'reguler';
        const jenisNama = form.dataset.jenisNama || '';
        const cards = Array.from(form.querySelectorAll('[data-aspek-card]'));

        cards.forEach((card) => {
            const noteInput = card.querySelector('[data-note-input]');
            if (noteInput) {
                const initiallyManual = !isAutoGeneratedNote(noteInput.value);
                noteInput.dataset.manual = initiallyManual ? 'true' : 'false';
                updateNoteModeBadge(card, initiallyManual, noteInput.value.trim() !== '');

                noteInput.addEventListener('input', () => {
                    const hasText = noteInput.value.trim() !== '';
                    noteInput.dataset.manual = hasText ? 'true' : 'false';
                    updateNoteModeBadge(card, hasText, hasText);
                    markFormDirty(form);
                });
            }

            card.querySelectorAll('[data-score-radio]').forEach((radio) => {
                radio.addEventListener('change', () => {
                    if (!radio.checked) {
                        return;
                    }
                    applyScoreToCard(card, radio.value, kategori, { overwriteManual: false, jenisNama });
                    markFormDirty(form);
                    recalculateAll();
                });
            });

            card.querySelector('[data-note-regen]')?.addEventListener('click', () => {
                const checked = card.querySelector('input[type="radio"]:checked');
                if (!checked) {
                    showBanner('Pilih skor terlebih dahulu sebelum membuat catatan otomatis.', 'warning');
                    return;
                }
                if (noteInput) {
                    const maxSkor = parseInt(card.dataset.maxSkor || '4', 10);
                    const generated = generateContextualNote(
                        checked.value,
                        maxSkor,
                        card.dataset.namaAspek,
                        card.dataset.fokusPerilaku,
                        kategori,
                        jenisNama
                    );
                    noteInput.value = generated;
                    noteInput.dataset.manual = 'false';
                    updateNoteModeBadge(card, false, true);
                    markFormDirty(form);
                }
            });

            card.querySelector('[data-note-clear]')?.addEventListener('click', () => {
                if (noteInput) {
                    noteInput.value = '';
                    noteInput.dataset.manual = 'false';
                    updateNoteModeBadge(card, false, false);
                    markFormDirty(form);
                }
            });
        });

        form.querySelector('[data-rekomendasi-input]')?.addEventListener('input', () => {
            markFormDirty(form);
        });

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const ok = await saveComponentForm(form);
            if (ok) {
                showBanner(`Komponen "${form.dataset.jenisNama}" berhasil disimpan.`, 'success');
                recalculateAll();
            }
        });
    });

    // Bulk-Skor Global per Step
    document.querySelectorAll('[data-bulk-step]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const stepNum = parseInt(btn.dataset.bulkStep || '1', 10);
            const targetScore = parseInt(btn.dataset.score || '4', 10);
            const forms = componentForms.filter((f) => parseInt(f.dataset.step || '1', 10) === stepNum);

            let affectedAspects = 0;
            forms.forEach((form) => {
                const kategori = form.dataset.kategori || 'reguler';
                const jenisNama = form.dataset.jenisNama || '';
                form.querySelectorAll('[data-aspek-card]').forEach((card) => {
                    applyScoreToCard(card, targetScore, kategori, { overwriteManual: false, jenisNama });
                    affectedAspects += 1;
                });
                markFormDirty(form);
            });

            recalculateAll();
            showBanner(`Skor global ${targetScore} diterapkan ke ${affectedAspects} aspek pada Step ${stepNum}.`, 'info');
        });
    });

    // Reset Catatan Global per Step
    document.querySelectorAll('[data-bulk-step-reset-notes]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const stepNum = parseInt(btn.dataset.bulkStepResetNotes || '1', 10);
            const forms = componentForms.filter((f) => parseInt(f.dataset.step || '1', 10) === stepNum);

            let resetCount = 0;
            forms.forEach((form) => {
                const kategori = form.dataset.kategori || 'reguler';
                const jenisNama = form.dataset.jenisNama || '';
                form.querySelectorAll('[data-aspek-card]').forEach((card) => {
                    const checked = card.querySelector('input[type="radio"]:checked');
                    if (checked) {
                        applyScoreToCard(card, checked.value, kategori, { overwriteManual: true, jenisNama });
                        resetCount += 1;
                    }
                });
                markFormDirty(form);
            });

            recalculateAll();
            showBanner(`Catatan otomatis diperbarui pada ${resetCount} aspek terisi di Step ${stepNum}.`, 'info');
        });
    });

    // Bulk-Skor per Instrumen
    document.querySelectorAll('[data-bulk-jenis]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const jenisId = btn.dataset.bulkJenis;
            const targetScore = parseInt(btn.dataset.score || '4', 10);
            const form = document.querySelector(`[data-komponen-form="${jenisId}"]`);
            if (!form) {
                return;
            }

            const kategori = form.dataset.kategori || 'reguler';
            const jenisNama = form.dataset.jenisNama || '';
            form.querySelectorAll('[data-aspek-card]').forEach((card) => {
                applyScoreToCard(card, targetScore, kategori, { overwriteManual: false, jenisNama });
            });
            markFormDirty(form);
            recalculateAll();
            showBanner(`Skor ${targetScore} diterapkan ke seluruh aspek pada "${form.dataset.jenisNama}".`, 'info');
        });
    });

    // Reset Catatan per Instrumen
    document.querySelectorAll('[data-bulk-jenis-reset-notes]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const jenisId = btn.dataset.bulkJenisResetNotes;
            const form = document.querySelector(`[data-komponen-form="${jenisId}"]`);
            if (!form) {
                return;
            }

            const kategori = form.dataset.kategori || 'reguler';
            const jenisNama = form.dataset.jenisNama || '';
            form.querySelectorAll('[data-aspek-card]').forEach((card) => {
                const checked = card.querySelector('input[type="radio"]:checked');
                if (checked) {
                    applyScoreToCard(card, checked.value, kategori, { overwriteManual: true, jenisNama });
                }
            });
            markFormDirty(form);
            showBanner(`Catatan otomatis pada "${form.dataset.jenisNama}" berhasil di-refresh.`, 'info');
        });
    });

    // Accordion per Instrumen
    document.querySelectorAll('[data-accordion-toggle]').forEach((toggle) => {
        toggle.addEventListener('click', () => {
            const jenisId = toggle.dataset.accordionToggle;
            const body = document.getElementById(`instrument-body-${jenisId}`);
            const icon = document.querySelector(`[data-accordion-icon="${jenisId}"]`);
            if (!body) {
                return;
            }
            const willExpand = body.hidden;
            body.hidden = !willExpand;
            toggle.setAttribute('aria-expanded', willExpand ? 'true' : 'false');
            if (icon) {
                icon.textContent = willExpand ? '−' : '+';
            }
        });
    });

    // Stepper & Bottom Step Navigation
    stepButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            const targetStep = parseInt(btn.dataset.stepNav || '1', 10);
            goToStep(targetStep);
        });
    });

    document.querySelectorAll('[data-go-step]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const targetStep = parseInt(btn.dataset.goStep || '1', 10);
            goToStep(targetStep);
        });
    });

    // AJAX Upload & Delete Foto Bukti di Step 3
    const fotoUploadForm = document.getElementById('foto-upload-form');
    const fotoGalleryGrid = document.getElementById('foto-gallery-grid');
    const fotoEmptyHint = document.getElementById('foto-empty-hint');
    const fotoQuotaBadge = document.getElementById('foto-quota-badge');
    const step3CountBadge = document.querySelector('[data-step-count="3"]');

    function updateFotoCountUI(count, max = 5) {
        if (fotoQuotaBadge) {
            fotoQuotaBadge.textContent = `${count} / ${max} Foto`;
        }
        if (step3CountBadge) {
            step3CountBadge.textContent = `${count}/${max}`;
        }
        if (fotoGalleryGrid) {
            fotoGalleryGrid.hidden = count === 0;
        }
        if (fotoEmptyHint) {
            fotoEmptyHint.hidden = count > 0;
        }
    }

    function bindDeleteFotoForm(form) {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: new FormData(form),
                });
                const payload = await response.json();
                if (!response.ok || payload.status === 'error') {
                    showBanner(payload.message || 'Gagal menghapus foto bukti.', 'error');
                    return;
                }
                form.closest('[data-foto-card]')?.remove();
                updateFotoCountUI(payload.count ?? 0, payload.max ?? 5);
                showBanner(payload.message || 'Foto bukti berhasil dihapus.', 'success');
            } catch (error) {
                showBanner('Terjadi gangguan saat menghapus foto.', 'error');
            }
        });
    }

    function renderFotoGallery(photos, count, max = 5) {
        if (!fotoGalleryGrid) {
            return;
        }
        fotoGalleryGrid.replaceChildren();

        photos.forEach((photo) => {
            const card = document.createElement('div');
            card.className = 'border-2 border-black rounded-lg overflow-hidden bg-slate-50';
            card.dataset.fotoCard = String(photo.id);

            const img = document.createElement('img');
            img.src = photo.url;
            img.alt = photo.keterangan || 'Foto bukti';
            img.className = 'w-full h-28 object-cover';

            const footer = document.createElement('div');
            footer.className = 'p-2 flex items-center justify-between gap-2';

            const caption = document.createElement('span');
            caption.className = 'text-[11px] font-semibold truncate';
            caption.textContent = photo.keterangan || 'Bukti';

            const delForm = document.createElement('form');
            delForm.action = photo.delete_url;
            delForm.method = 'POST';
            delForm.dataset.fotoDeleteForm = String(photo.id);

            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = csrfToken;

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';

            const delBtn = document.createElement('button');
            delBtn.type = 'submit';
            delBtn.className = 'text-[11px] font-black text-rose-700 hover:underline cursor-pointer';
            delBtn.textContent = 'Hapus';

            delForm.append(tokenInput, methodInput, delBtn);
            bindDeleteFotoForm(delForm);

            footer.append(caption, delForm);
            card.append(img, footer);
            fotoGalleryGrid.appendChild(card);
        });

        updateFotoCountUI(count, max);
    }

    document.querySelectorAll('[data-foto-delete-form]').forEach(bindDeleteFotoForm);

    fotoUploadForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const uploadBtn = document.getElementById('btn-upload-foto');
        if (uploadBtn) {
            uploadBtn.disabled = true;
            uploadBtn.textContent = 'Mengunggah...';
        }

        try {
            const response = await fetch(fotoUploadForm.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: new FormData(fotoUploadForm),
            });
            const payload = await response.json();
            if (!response.ok || payload.status === 'error') {
                showBanner(payload.message || 'Gagal mengunggah foto bukti.', 'error');
                return;
            }
            renderFotoGallery(payload.photos || [], payload.count ?? 0, payload.max ?? 5);
            fotoUploadForm.reset();
            showBanner(payload.message || 'Foto bukti berhasil diunggah.', 'success');
        } catch (error) {
            showBanner('Gagal mengunggah foto. Periksa ukuran file dan koneksi.', 'error');
        } finally {
            if (uploadBtn) {
                uploadBtn.disabled = false;
                uploadBtn.textContent = 'Unggah Foto';
            }
        }
    });

    // Complete Dialog & Auto-Save All Dirty Forms Before Complete
    const completeDialog = document.getElementById('complete-confirm-dialog');
    const completeSummary = document.getElementById('complete-dialog-summary');
    const completeForm = document.getElementById('complete-penilaian-form');

    document.getElementById('btn-open-complete-dialog')?.addEventListener('click', () => {
        recalculateAll();
        const progressText = document.getElementById('live-progress')?.textContent || '0 / 0';
        const nilaiText = document.getElementById('live-nilai-akhir')?.textContent || '0.00';
        const predikatText = document.getElementById('live-predikat')?.textContent || '-';
        const dirtyText = dirtyForms.size > 0
            ? ` Terdapat ${dirtyForms.size} komponen yang akan disimpan otomatis terlebih dahulu.`
            : '';

        if (completeSummary) {
            completeSummary.textContent = `Aspek terisi: ${progressText} • Nilai akhir sementara: ${nilaiText} (${predikatText}).${dirtyText}`;
        }

        if (completeDialog && typeof completeDialog.showModal === 'function') {
            completeDialog.showModal();
        }
    });

    document.getElementById('btn-cancel-complete')?.addEventListener('click', () => {
        completeDialog?.close();
    });

    completeForm?.addEventListener('submit', async (event) => {
        if (dirtyForms.size === 0) {
            return;
        }
        event.preventDefault();
        const confirmBtn = document.getElementById('btn-confirm-complete');
        if (confirmBtn) {
            confirmBtn.disabled = true;
            confirmBtn.textContent = 'Menyimpan komponen...';
        }

        for (const form of componentForms) {
            const jenisId = form.dataset.komponenForm;
            if (!dirtyForms.has(jenisId)) {
                continue;
            }
            const ok = await saveComponentForm(form);
            if (!ok) {
                if (confirmBtn) {
                    confirmBtn.disabled = false;
                    confirmBtn.textContent = 'Ya, Simpan Semua & Selesaikan';
                }
                completeDialog?.close();
                return;
            }
        }

        completeForm.submit();
    });

    // Sync active stepper UI on initial load
    stepButtons.forEach((btn) => {
        const btnStep = parseInt(btn.dataset.stepNav || '0', 10);
        const active = btnStep === currentStep;
        btn.classList.toggle('bg-[#FFD43B]', active);
        btn.classList.toggle('bg-white', !active);
    });
    recalculateAll();
})();
</script>
@endpush
