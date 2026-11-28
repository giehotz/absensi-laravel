@extends('layouts.supervisi')
@section('title', 'Hub Penilaian 14 Form Supervisi')
@section('page_title', 'Formulir Observasi & Penilaian (Hub 14 Instrumen)')

@push('styles')
<style>
    .foto-visually-hidden {
        position: absolute;
        clip-path: inset(50%);
        overflow: hidden;
        width: 1px;
        height: 1px;
        margin: -1px;
        padding: 0;
        border: 0;
        white-space: nowrap;
    }

    .foto-progress {
        appearance: none;
        border: 2px solid #000;
        border-radius: 9999px;
        overflow: clip;
        height: 0.65rem;
        width: 100%;
        background: #f1f5f9;
    }

    .foto-progress::-webkit-progress-bar {
        background: transparent;
    }

    .foto-progress::-webkit-progress-value {
        background: var(--foto-progress-fill, #20c997);
        border-radius: 9999px;
        transition: inline-size 0.25s ease;
    }

    .foto-progress::-moz-progress-bar {
        background: var(--foto-progress-fill, #20c997);
        border-radius: 9999px;
    }

    .foto-dropzone:has(input:focus-visible) {
        outline: 3px solid #1d4ed8;
        outline-offset: 2px;
    }
</style>
@endpush

@section('content')
@php
    use App\Models\Supervisi\SupervisiJenisPenilaian;

    $completeRoute = route("supervisi.{$portalPrefix}.penilaian.complete", $jadwal);
    $uploadFotoRoute = route("supervisi.{$portalPrefix}.penilaian.foto.upload", $jadwal);
    $deleteFotoRouteName = "supervisi.{$portalPrefix}.penilaian.foto.delete";

    $groupA = $jenisList->filter(
        fn (SupervisiJenisPenilaian $j): bool => $j->kode === 'REG-ADM'
            || $j->kategori_supervisi === SupervisiJenisPenilaian::KATEGORI_PERANGKAT_AJAR
    )->values();

    $groupB = $jenisList->filter(
        fn (SupervisiJenisPenilaian $j): bool => $j->kategori_supervisi === SupervisiJenisPenilaian::KATEGORI_REGULER
            && $j->kode !== 'REG-ADM'
    )->values();

    $groupC = $jenisList->filter(
        fn (SupervisiJenisPenilaian $j): bool => $j->kategori_supervisi === SupervisiJenisPenilaian::KATEGORI_OBSERVASI_PMM
    )->values();

    $groupD = $jenisList->filter(
        fn (SupervisiJenisPenilaian $j): bool => $j->kategori_supervisi === SupervisiJenisPenilaian::KATEGORI_KMA_6334
    )->values();

    $standardJenisList = $jenisList->filter(
        fn (SupervisiJenisPenilaian $j): bool => $j->kategori_supervisi !== SupervisiJenisPenilaian::KATEGORI_KMA_6334
    )->values();

    $groups = [
        ['title' => 'A. Administrasi & Telaah Perangkat Ajar', 'items' => $groupA],
        ['title' => 'B. Proses, Evaluasi & Pengembangan Diri', 'items' => $groupB],
        ['title' => 'C. Observasi Praktik Kinerja PMM', 'items' => $groupC],
        ['title' => 'D. Instrumen Juknis SK 6334/2021 (MI)', 'items' => $groupD],
    ];

    $completedOnJadwal = 0;
    $partialOnJadwal = 0;
    $totalAspectsAll = 0;
    $filledAspectsAll = 0;
    $grandTotalSkor = 0;
    $grandMaxSkor = 0;

    foreach ($standardJenisList as $jItem) {
        $reqCount = $jItem->aspek_penilaians_count ?? $jItem->aspekPenilaians->count();
        $hItem = $existingHasils->get($jItem->id);
        $detCount = $hItem ? $hItem->detailHasils->count() : 0;

        $totalAspectsAll += $reqCount;
        $filledAspectsAll += $detCount;

        if ($hItem && $detCount > 0) {
            $grandTotalSkor += (int) $hItem->total_skor;
            $grandMaxSkor += (int) $hItem->skor_maksimal;
            if ($reqCount > 0 && $detCount >= $reqCount) {
                $completedOnJadwal++;
            } else {
                $partialOnJadwal++;
            }
        }
    }

    foreach ($groupD as $kmaItem) {
        $hKma = $existingHasils->get($kmaItem->id);
        if ($hKma && $hKma->detailHasils->isNotEmpty()) {
            $grandTotalSkor += (int) $hKma->total_skor;
            $grandMaxSkor += (int) $hKma->skor_maksimal;
        }
    }

    $jadwalNilai = $grandMaxSkor > 0 ? round(($grandTotalSkor / $grandMaxSkor) * 100, 2) : 0.0;
    $requestedStep = (int) request()->query('step', 1);
    $initialStep = in_array($requestedStep, [1, 2, 3], true) ? $requestedStep : 1;
@endphp

<!-- Header Ringkasan Jadwal -->
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
                <div class="text-[10px] font-bold text-slate-500 uppercase">Form Standar</div>
                <div class="text-sm font-black tabular-nums">{{ $completedOnJadwal }} / {{ $standardJenisList->count() }}</div>
            </div>
            <div class="px-3 py-2 rounded-lg border-2 border-black bg-slate-50">
                <div class="text-[10px] font-bold text-slate-500 uppercase">Aspek Dinilai</div>
                <div class="text-sm font-black tabular-nums">{{ $filledAspectsAll }} / {{ $totalAspectsAll }}</div>
            </div>
            <div class="px-3 py-2 rounded-lg border-2 border-black bg-[#FFD43B]">
                <div class="text-[10px] font-bold text-slate-800 uppercase">Nilai Jadwal</div>
                <div class="text-sm font-black tabular-nums text-black">{{ number_format($jadwalNilai, 2) }}%</div>
            </div>
            <div class="px-3 py-2 rounded-lg border-2 border-black bg-emerald-100">
                <div class="text-[10px] font-bold text-emerald-900 uppercase">Portofolio Guru</div>
                <div class="text-xs font-black mt-0.5">
                    @if($jadwal->teacher)
                        <a href="{{ route('supervisi.cetak.portofolio', ['teacher' => $jadwal->teacher_id, 'academic_year_id' => $jadwal->academic_year_id]) }}" target="_blank" class="underline">
                            🖨️ PDF 14 Form
                        </a>
                    @else
                        -
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Stepper Navigasi Hub 3 Tahap -->
    <nav id="hub-stepper" aria-label="Tahapan Penilaian Supervisor" class="pt-3 border-t-2 border-slate-200">
        <ol class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
            <li>
                <button type="button" data-hub-nav="1" aria-current="{{ $initialStep === 1 ? 'step' : 'false' }}"
                    class="w-full text-left px-3.5 py-2.5 rounded-lg border-2 border-black transition-all cursor-pointer flex items-center justify-between gap-2 shadow-[2px_2px_0px_0px_#000] {{ $initialStep === 1 ? 'bg-[#FFD43B]' : 'bg-white' }}">
                    <span class="text-xs font-black">1. Checklist 14 Form Penilaian</span>
                    <span class="px-2 py-0.5 rounded border border-black bg-white text-[11px] font-black tabular-nums">{{ $completedOnJadwal }}/{{ $standardJenisList->count() }}</span>
                </button>
            </li>
            <li>
                <button type="button" data-hub-nav="2" aria-current="{{ $initialStep === 2 ? 'step' : 'false' }}"
                    class="w-full text-left px-3.5 py-2.5 rounded-lg border-2 border-black transition-all cursor-pointer flex items-center justify-between gap-2 shadow-[2px_2px_0px_0px_#000] {{ $initialStep === 2 ? 'bg-[#FFD43B]' : 'bg-white' }}">
                    <span class="text-xs font-black">2. Dokumentasi Foto Bukti</span>
                    <span data-foto-step-count class="px-2 py-0.5 rounded border border-black bg-white text-[11px] font-black tabular-nums">{{ $jadwal->fotoBuktis->count() }}/5</span>
                </button>
            </li>
            <li>
                <button type="button" data-hub-nav="3" aria-current="{{ $initialStep === 3 ? 'step' : 'false' }}"
                    class="w-full text-left px-3.5 py-2.5 rounded-lg border-2 border-black transition-all cursor-pointer flex items-center justify-between gap-2 shadow-[2px_2px_0px_0px_#000] {{ $initialStep === 3 ? 'bg-[#FFD43B]' : 'bg-white' }}">
                    <span class="text-xs font-black">3. Ringkasan &amp; Selesai</span>
                    <span class="px-2 py-0.5 rounded border border-black bg-white text-[11px] font-black">Final</span>
                </button>
            </li>
        </ol>
    </nav>

    <div id="hub-status-banner" role="status" aria-live="polite" hidden
        class="p-3 rounded-lg border-2 border-black text-xs font-bold"></div>
</section>

<!-- Step 1: Checklist 14 Form Penilaian -->
<section id="step-panel-1" data-hub-panel="1" {{ $initialStep === 1 ? '' : 'hidden' }} class="space-y-5">
    <div class="bg-white border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000] flex flex-wrap items-center justify-between gap-3">
        <div>
            <h3 id="hub-heading-1" tabindex="-1" class="font-heading font-black text-base focus:outline-none">
                Pilih Form Penilaian ({{ $standardJenisList->count() }} Form Standar + {{ $groupD->count() }} Form SK 6334)
            </h3>
            <p class="text-xs text-slate-600">Setiap instrumen memiliki halaman form tersendiri lengkap dengan keterangan aspek, Bulk-Skor, dan catatan otomatis. Dapat diisi sekaligus atau bertahap dalam 1 semester.</p>
        </div>
        <div class="flex flex-wrap gap-1.5 text-[10px] font-black">
            <span class="px-2 py-0.5 rounded border border-black bg-emerald-200 text-emerald-950">✓ Lengkap</span>
            <span class="px-2 py-0.5 rounded border border-black bg-[#FFD43B] text-black">⏳ Sebagian</span>
            <span class="px-2 py-0.5 rounded border border-black bg-rose-200 text-rose-950">✕ Belum Diisi</span>
        </div>
    </div>

    @foreach($groups as $group)
        @if($group['items']->isNotEmpty())
            <div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-3">
                <h4 class="font-heading font-black text-sm border-b-2 border-black pb-2">{{ $group['title'] }} ({{ $group['items']->count() }} Form)</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($group['items'] as $jenis)
                        @php
                            $hasil = $existingHasils->get($jenis->id);
                            $reqCount = $jenis->aspek_penilaians_count ?? $jenis->aspekPenilaians->count();
                            $filledCount = $hasil ? $hasil->detailHasils->count() : 0;
                            $semEntry = $semesterFormsMap[$jenis->id] ?? null;
                            $isTargetPmm = (int) $jadwal->indikator_pmm_id === (int) $jenis->id;

                            if ($filledCount === 0) {
                                $stateClass = 'bg-rose-50 border-rose-700';
                                $badgeClass = 'bg-rose-200 text-rose-950';
                                $badgeText = '✕ Belum Diisi';
                            } elseif ($reqCount > 0 && $filledCount < $reqCount) {
                                $stateClass = 'bg-amber-50 border-amber-600';
                                $badgeClass = 'bg-[#FFD43B] text-black';
                                $badgeText = "⏳ Sebagian ({$filledCount}/{$reqCount})";
                            } else {
                                $stateClass = 'bg-emerald-50 border-emerald-700';
                                $badgeClass = 'bg-emerald-200 text-emerald-950';
                                $badgeText = "✓ Lengkap ({$filledCount}/{$reqCount})";
                            }
                        @endphp

                        <article class="p-4 rounded-xl border-2 flex flex-col justify-between gap-3 {{ $stateClass }}">
                            <div class="space-y-1.5">
                                <div class="flex flex-wrap items-center justify-between gap-1.5">
                                    <span class="px-1.5 py-0.5 rounded border border-black bg-white font-mono text-[10px] font-black">{{ $jenis->kode }}</span>
                                    <span class="px-2 py-0.5 rounded border border-black text-[10px] font-black {{ $badgeClass }}">{{ $badgeText }}</span>
                                </div>

                                <h5 class="font-heading font-black text-xs text-slate-900 leading-snug">{{ $jenis->nama }}</h5>
                                <p class="text-[11px] text-slate-700">{{ $reqCount }} Aspek • Skala 1–{{ $jenis->skor_maksimal }}</p>

                                @if($isTargetPmm)
                                    <span class="inline-block px-2 py-0.5 rounded border border-black bg-[#FFD43B] text-[10px] font-black">
                                        🎯 Target Utama Jadwal
                                    </span>
                                @endif

                                @if($semEntry && $semEntry['state'] === 'done' && $filledCount === 0)
                                    <span class="inline-block px-2 py-0.5 rounded border border-black bg-sky-100 text-sky-950 text-[10px] font-black">
                                        ✓ Sudah diisi di jadwal lain semester ini ({{ number_format((float) $semEntry['nilai_akhir'], 1) }}%)
                                    </span>
                                @endif

                                @if($hasil && $filledCount > 0)
                                    <div class="text-xs font-black text-slate-900 tabular-nums">
                                        Skor: {{ $hasil->total_skor }}/{{ $hasil->skor_maksimal }} ({{ number_format((float) $hasil->nilai_akhir, 2) }}% · {{ $hasil->ketercapaian }})
                                    </div>
                                @endif
                            </div>

                            <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-black/15">
                                <a href="{{ route("supervisi.{$portalPrefix}.penilaian.form", ['jadwal' => $jadwal, 'jenis' => $jenis]) }}"
                                    class="flex-1 text-center px-3 py-1.5 bg-white hover:bg-[#FFD43B] text-black text-xs font-black rounded-lg border-2 border-black shadow-[1.5px_1.5px_0px_0px_#000]">
                                    📝 {{ $filledCount > 0 ? 'Edit Form' : 'Isi Form' }} →
                                </a>
                                @if($hasil && $filledCount > 0)
                                    <a href="{{ route('supervisi.cetak.instrumen', ['jadwal' => $jadwal, 'jenis' => $jenis]) }}" target="_blank"
                                        class="px-2.5 py-1.5 bg-[#20C997] text-black text-xs font-black rounded-lg border-2 border-black">
                                        🖨️ PDF
                                    </a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach

    <div class="bg-white border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000] flex justify-end">
        <button type="button" data-hub-go="2" enterkeyhint="next"
            class="px-5 py-2.5 bg-[#FFD43B] hover:bg-amber-300 text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
            Lanjut ke Step 2 (Foto Bukti) →
        </button>
    </div>
</section>

<!-- Step 2: Dokumentasi Foto Bukti -->
@php
    $uploadedFotoCount = $jadwal->fotoBuktis->count();
    $remainingFotoSlots = max(0, 5 - $uploadedFotoCount);
@endphp
<section id="step-panel-2" data-hub-panel="2" {{ $initialStep === 2 ? '' : 'hidden' }} class="space-y-5">
    <div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="space-y-1">
                <h3 id="hub-heading-2" tabindex="-1" class="font-heading font-black text-base focus:outline-none">
                    📸 Step 2: Dokumentasi Foto Bukti (Maks. 5 Foto)
                </h3>
                <p class="text-xs font-semibold text-slate-600">
                    Pilih sekaligus beberapa foto atau tambahkan bertahap lewat seret &amp; lepas (*drag &amp; drop*). Format JPG, PNG, atau WebP (maks. 5 MB per foto).
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span id="foto-remaining-badge" class="px-2.5 py-1 rounded-lg border-2 border-black bg-[#FFF9DB] text-xs font-black tabular-nums">
                    {{ $remainingFotoSlots > 0 ? "Sisa {$remainingFotoSlots} slot" : 'Kuota penuh' }}
                </span>
                <span id="foto-quota-badge" class="px-2.5 py-1 rounded-lg border-2 border-black bg-slate-100 text-xs font-black tabular-nums">
                    {{ $uploadedFotoCount }} / 5 Foto
                </span>
            </div>
        </div>

        <div class="space-y-1">
            <label for="foto-quota-progress" class="foto-visually-hidden">Kuota foto bukti terunggah</label>
            <progress id="foto-quota-progress" class="foto-progress" max="5" value="{{ $uploadedFotoCount }}"
                style="--foto-progress-fill: {{ $uploadedFotoCount >= 5 ? '#20c997' : '#5294ff' }};"></progress>
        </div>

        <div id="foto-quota-full-banner" {{ $remainingFotoSlots === 0 ? '' : 'hidden' }}
            class="p-3.5 rounded-xl border-2 border-black bg-emerald-50 text-xs font-bold text-emerald-950">
            ✅ Kuota maksimal <strong>5 / 5 foto bukti</strong> sudah terpenuhi. Hapus salah satu foto di galeri jika ingin mengganti.
        </div>

        <form id="foto-upload-form" action="{{ $uploadFotoRoute }}" method="POST" enctype="multipart/form-data"
            data-max-fotos="5" data-uploaded-count="{{ $uploadedFotoCount }}"
            aria-labelledby="hub-heading-2"
            {{ $remainingFotoSlots === 0 ? 'hidden' : '' }}
            class="space-y-4">
            @csrf

            <div id="foto-dropzone"
                class="foto-dropzone relative rounded-xl border-2 border-dashed border-black bg-[#FFF9DB]/50 hover:bg-[#FFF9DB] transition-colors p-5 text-center space-y-3">
                <input id="photos"
                    type="file"
                    name="photos[]"
                    multiple
                    accept="image/jpeg,image/png,image/webp"
                    aria-invalid="false"
                    data-error="foto-selection-error"
                    aria-describedby="foto-dropzone-help"
                    class="foto-visually-hidden">

                <div class="w-11 h-11 mx-auto rounded-xl border-2 border-black bg-[#FFD43B] flex items-center justify-center text-xl shadow-[2px_2px_0px_0px_#000]" aria-hidden="true">
                    🖼️
                </div>

                <div class="space-y-1">
                    <div class="text-sm font-black text-slate-900">Seret &amp; lepas beberapa foto di sini</div>
                    <p id="foto-dropzone-help" class="text-xs font-semibold text-slate-600">
                        atau klik tombol di bawah untuk memilih hingga <span id="foto-dropzone-remaining">{{ $remainingFotoSlots }}</span> foto sekaligus (JPG, PNG, WebP · maks. 5 MB/foto).
                    </p>
                </div>

                <div class="flex flex-wrap justify-center gap-2 pt-1">
                    <label for="photos"
                        class="inline-flex items-center gap-1.5 min-h-[44px] px-4 py-2 bg-white hover:bg-[#FFD43B] text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer select-none">
                        <span>📂 Pilih / Tambah Foto</span>
                    </label>
                    <button type="button" id="btn-clear-foto-queue" hidden
                        class="min-h-[44px] px-3.5 py-2 bg-rose-100 hover:bg-rose-200 text-rose-950 text-xs font-black rounded-lg border-2 border-black cursor-pointer">
                        ✕ Kosongkan Antrean
                    </button>
                </div>
            </div>

            <div id="foto-selection-error" class="text-xs font-bold text-rose-800 bg-rose-100 border-2 border-black rounded-lg p-2.5" hidden></div>

            <!-- Antrean Pratinjau Multiple Selection Sebelum Unggah -->
            <div id="foto-staging-wrapper" hidden class="p-4 rounded-xl border-2 border-black bg-slate-50 space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="text-xs font-black text-slate-900">
                        Antrean Siap Unggah (<span id="foto-staging-count" class="tabular-nums">0</span> foto)
                    </span>
                    <span class="text-[11px] font-semibold text-slate-600">Klik ✕ pada kartu untuk membatalkan salah satu foto.</span>
                </div>
                <div id="foto-staging-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-[1fr_auto] gap-3 items-end">
                <div class="space-y-1">
                    <label for="keterangan_foto" class="block text-xs font-black text-slate-900">
                        Keterangan Foto <span class="font-semibold text-slate-500">(opsional, diterapkan ke batch ini)</span>
                    </label>
                    <input id="keterangan_foto" type="text" name="keterangan" maxlength="255"
                        placeholder="Contoh: Kegiatan inti &amp; diskusi kelompok kelas 6A"
                        class="w-full min-h-[44px] px-3 py-2 text-base sm:text-xs border-2 border-black rounded-lg bg-white">
                </div>
                <button type="submit" id="btn-upload-foto" enterkeyhint="send"
                    class="min-h-[44px] px-5 py-2.5 bg-[#FFD43B] hover:bg-amber-300 text-black font-black text-xs rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                    ⬆️ Unggah Foto (<span id="foto-upload-btn-count">0</span>)
                </button>
            </div>

            <div id="foto-upload-progress-wrap" hidden class="space-y-1.5 p-3 rounded-lg border-2 border-black bg-sky-50">
                <div class="flex items-center justify-between text-xs font-black text-sky-950">
                    <label for="foto-upload-progress">Mengunggah foto bukti...</label>
                    <span id="foto-upload-progress-pct" class="tabular-nums">0%</span>
                </div>
                <progress id="foto-upload-progress" class="foto-progress" max="100" value="0" style="--foto-progress-fill: #5294ff;"></progress>
            </div>
        </form>

        <div class="space-y-2 pt-2 border-t-2 border-slate-200">
            <h4 class="text-xs font-black text-slate-900 uppercase tracking-wider">Galeri Foto Terunggah</h4>
            <div id="foto-gallery-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3" {{ $jadwal->fotoBuktis->isEmpty() ? 'hidden' : '' }}>
                @foreach($jadwal->fotoBuktis as $foto)
                    <article class="border-2 border-black rounded-xl overflow-hidden bg-slate-50 shadow-[2px_2px_0px_0px_#000] flex flex-col justify-between" data-foto-card="{{ $foto->id }}">
                        <img src="{{ $foto->url }}" alt="{{ $foto->keterangan ? 'Foto bukti: ' . $foto->keterangan : 'Foto bukti observasi' }}" class="w-full h-28 object-cover border-b-2 border-black">
                        <div class="p-2 flex items-center justify-between gap-2">
                            <span class="text-[11px] font-bold truncate" title="{{ $foto->keterangan ?? 'Bukti' }}">{{ $foto->keterangan ?? 'Bukti' }}</span>
                            <form action="{{ route($deleteFotoRouteName, $foto) }}" method="POST" data-foto-delete-form="{{ $foto->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    aria-label="Hapus foto bukti {{ $foto->keterangan ?? 'observasi' }}"
                                    class="min-h-[32px] px-2 py-0.5 rounded border border-black bg-rose-100 hover:bg-rose-200 text-[11px] font-black text-rose-950 cursor-pointer">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
            <p id="foto-empty-hint" class="text-xs text-slate-500 font-semibold" {{ $jadwal->fotoBuktis->isNotEmpty() ? 'hidden' : '' }}>
                Belum ada foto bukti diunggah.
            </p>
        </div>
    </div>

    <div class="bg-white border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000] flex flex-wrap justify-between gap-2">
        <button type="button" data-hub-go="1" enterkeyhint="previous" class="px-4 py-2 bg-white hover:bg-slate-100 text-black text-xs font-black rounded-lg border-2 border-black cursor-pointer">
            ← Kembali ke Step 1
        </button>
        <button type="button" data-hub-go="3" enterkeyhint="next" class="px-5 py-2.5 bg-[#FFD43B] hover:bg-amber-300 text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
            Lanjut ke Step 3 (Selesai) →
        </button>
    </div>
</section>

<!-- Step 3: Ringkasan & Selesai -->
<section id="step-panel-3" data-hub-panel="3" {{ $initialStep === 3 ? '' : 'hidden' }} class="space-y-5">
    <div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-4">
        <h3 id="hub-heading-3" tabindex="-1" class="font-heading font-black text-base focus:outline-none">✅ Step 3: Ringkasan &amp; Selesaikan Penilaian Jadwal</h3>
        <p class="text-xs text-slate-600">Minimal satu komponen instrumen harus terisi penuh seluruh aspeknya untuk menyelesaikan observasi pada jadwal ini. Instrumen lainnya tetap dapat dilengkapi pada jadwal lain dalam semester berjalan.</p>

        @if($grandMaxSkor > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($existingHasils as $h)
                    @if($h->detailHasils->isNotEmpty())
                        <div class="p-3 rounded-lg border-2 border-black bg-slate-50 flex items-center justify-between gap-2">
                            <span class="text-xs font-black">{{ $h->jenisPenilaian?->nama }}</span>
                            <span class="px-2 py-0.5 rounded border border-black bg-[#FFD43B] text-xs font-black tabular-nums">
                                {{ $h->total_skor }}/{{ $h->skor_maksimal }} ({{ number_format((float) $h->nilai_akhir, 2) }}%)
                            </span>
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            <div class="p-4 rounded-lg border-2 border-black bg-rose-50 text-xs font-bold text-rose-950">
                Belum ada instrumen yang dinilai pada jadwal ini. Kembali ke Step 1 untuk mengisi minimal satu form instrumen.
            </div>
        @endif
    </div>

    <div class="bg-white border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000] flex flex-wrap justify-between gap-2">
        <button type="button" data-hub-go="2" enterkeyhint="previous" class="px-4 py-2 bg-white hover:bg-slate-100 text-black text-xs font-black rounded-lg border-2 border-black cursor-pointer">
            ← Kembali ke Step 2
        </button>

        <form action="{{ $completeRoute }}" method="POST">
            @csrf
            <button type="submit" enterkeyhint="done"
                class="px-5 py-2.5 bg-[#20C997] hover:bg-emerald-400 text-black text-xs font-black rounded-lg border-2 border-black shadow-[3px_3px_0px_0px_#000] cursor-pointer">
                ✅ Selesaikan Penilaian Observasi
            </button>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
(() => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const navButtons = Array.from(document.querySelectorAll('[data-hub-nav]'));
    const panels = Array.from(document.querySelectorAll('[data-hub-panel]'));
    const statusBanner = document.getElementById('hub-status-banner');
    let currentStep = @json($initialStep);

    function showBanner(msg, tone = 'success') {
        if (!statusBanner) {
            return;
        }
        statusBanner.textContent = msg;
        statusBanner.classList.remove('bg-emerald-100', 'text-emerald-950', 'bg-rose-100', 'text-rose-950');
        statusBanner.classList.add(tone === 'error' ? 'bg-rose-100' : 'bg-emerald-100', tone === 'error' ? 'text-rose-950' : 'text-emerald-950');
        statusBanner.hidden = false;
    }

    function goToHubStep(targetStep) {
        if (targetStep === currentStep) {
            return;
        }
        currentStep = targetStep;
        panels.forEach((p) => {
            p.hidden = parseInt(p.dataset.hubPanel || '0', 10) !== currentStep;
        });
        navButtons.forEach((b) => {
            const active = parseInt(b.dataset.hubNav || '0', 10) === currentStep;
            b.setAttribute('aria-current', active ? 'step' : 'false');
            b.classList.toggle('bg-[#FFD43B]', active);
            b.classList.toggle('bg-white', !active);
        });
        document.getElementById(`hub-heading-${currentStep}`)?.focus({ preventScroll: true });
    }

    navButtons.forEach((b) => b.addEventListener('click', () => goToHubStep(parseInt(b.dataset.hubNav || '1', 10))));
    document.querySelectorAll('[data-hub-go]').forEach((b) => b.addEventListener('click', () => goToHubStep(parseInt(b.dataset.hubGo || '1', 10))));

    // Foto Bukti Modern Multi-Selection & AJAX Upload
    const fotoForm = document.getElementById('foto-upload-form');
    const fileInput = document.getElementById('photos');
    const dropzone = document.getElementById('foto-dropzone');
    const selectionError = document.getElementById('foto-selection-error');
    const stagingWrapper = document.getElementById('foto-staging-wrapper');
    const stagingGrid = document.getElementById('foto-staging-grid');
    const stagingCount = document.getElementById('foto-staging-count');
    const clearQueueBtn = document.getElementById('btn-clear-foto-queue');
    const uploadBtn = document.getElementById('btn-upload-foto');
    const uploadBtnCount = document.getElementById('foto-upload-btn-count');
    const uploadProgressWrap = document.getElementById('foto-upload-progress-wrap');
    const uploadProgress = document.getElementById('foto-upload-progress');
    const uploadProgressPct = document.getElementById('foto-upload-progress-pct');
    const quotaFullBanner = document.getElementById('foto-quota-full-banner');
    const quotaProgress = document.getElementById('foto-quota-progress');
    const remainingBadge = document.getElementById('foto-remaining-badge');
    const dropzoneRemaining = document.getElementById('foto-dropzone-remaining');
    const gallery = document.getElementById('foto-gallery-grid');
    const emptyHint = document.getElementById('foto-empty-hint');
    const quotaBadge = document.getElementById('foto-quota-badge');
    const stepBadge = document.querySelector('[data-foto-step-count]');

    const MAX_FOTOS = parseInt(fotoForm?.dataset.maxFotos || '5', 10);
    const MAX_FILE_BYTES = 5 * 1024 * 1024;
    const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
    let uploadedCount = parseInt(fotoForm?.dataset.uploadedCount || '0', 10);
    let stagedFiles = [];
    let previewUrls = [];

    function setSelectionError(msg) {
        if (!fileInput || !selectionError) return;
        const hasError = Boolean(msg);
        selectionError.textContent = msg || '';
        selectionError.hidden = !hasError;
        fileInput.setAttribute('aria-invalid', hasError ? 'true' : 'false');
        const describedBy = (fileInput.getAttribute('aria-describedby') || '')
            .split(/\s+/)
            .filter((id) => id && id !== 'foto-selection-error');
        if (hasError) describedBy.push('foto-selection-error');
        if (describedBy.length) fileInput.setAttribute('aria-describedby', describedBy.join(' '));
        else fileInput.removeAttribute('aria-describedby');
    }

    function formatBytes(bytes) {
        if (bytes < 1024) return `${bytes} B`;
        if (bytes < 1024 * 1024) return `${Math.round(bytes / 1024)} KB`;
        return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
    }

    function syncInputFiles() {
        if (!fileInput || typeof DataTransfer === 'undefined') return;
        const dt = new DataTransfer();
        stagedFiles.forEach((f) => dt.items.add(f));
        fileInput.files = dt.files;
    }

    function revokePreviewUrls() {
        previewUrls.forEach((u) => URL.revokeObjectURL(u));
        previewUrls = [];
    }

    function renderStagingQueue() {
        revokePreviewUrls();
        const count = stagedFiles.length;
        if (stagingCount) stagingCount.textContent = String(count);
        if (uploadBtnCount) uploadBtnCount.textContent = String(count);
        if (stagingWrapper) stagingWrapper.hidden = count === 0;
        if (clearQueueBtn) clearQueueBtn.hidden = count === 0;
        if (!stagingGrid) return;

        stagingGrid.replaceChildren();
        stagedFiles.forEach((file, idx) => {
            const url = URL.createObjectURL(file);
            previewUrls.push(url);

            const card = document.createElement('div');
            card.className = 'relative border-2 border-black rounded-xl overflow-hidden bg-white shadow-[2px_2px_0px_0px_#000]';

            const img = document.createElement('img');
            img.src = url;
            img.alt = `Pratinjau foto ${file.name}`;
            img.className = 'w-full h-24 object-cover border-b-2 border-black';

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'absolute top-1.5 right-1.5 w-7 h-7 rounded-lg border-2 border-black bg-rose-200 hover:bg-rose-300 text-rose-950 text-xs font-black cursor-pointer flex items-center justify-center';
            removeBtn.setAttribute('aria-label', `Hapus foto dari antrean: ${file.name}`);
            removeBtn.textContent = '✕';
            removeBtn.addEventListener('click', () => {
                stagedFiles.splice(idx, 1);
                syncInputFiles();
                setSelectionError('');
                renderStagingQueue();
            });

            const meta = document.createElement('div');
            meta.className = 'p-2 space-y-0.5';
            const nameEl = document.createElement('div');
            nameEl.className = 'text-[11px] font-bold text-slate-900 truncate';
            nameEl.textContent = file.name;
            const sizeEl = document.createElement('div');
            sizeEl.className = 'text-[10px] font-semibold text-slate-500 tabular-nums';
            sizeEl.textContent = formatBytes(file.size);

            meta.append(nameEl, sizeEl);
            card.append(img, removeBtn, meta);
            stagingGrid.appendChild(card);
        });
    }

    function updateFotoUI(count, max = 5) {
        uploadedCount = count;
        const remaining = Math.max(0, max - count);
        if (quotaBadge) quotaBadge.textContent = `${count} / ${max} Foto`;
        if (stepBadge) stepBadge.textContent = `${count}/${max}`;
        if (remainingBadge) remainingBadge.textContent = remaining > 0 ? `Sisa ${remaining} slot` : 'Kuota penuh';
        if (dropzoneRemaining) dropzoneRemaining.textContent = String(remaining);
        if (quotaProgress) {
            quotaProgress.value = count;
            quotaProgress.style.setProperty('--foto-progress-fill', count >= max ? '#20c997' : '#5294ff');
        }
        if (quotaFullBanner) quotaFullBanner.hidden = remaining > 0;
        if (fotoForm) fotoForm.hidden = remaining === 0;
        if (gallery) gallery.hidden = count === 0;
        if (emptyHint) emptyHint.hidden = count > 0;
    }

    function queueFiles(fileList) {
        const incoming = Array.from(fileList || []);
        if (incoming.length === 0) return;

        const availableSlots = Math.max(0, MAX_FOTOS - uploadedCount);
        if (availableSlots === 0) {
            setSelectionError('Kuota maksimal 5 foto sudah terpenuhi. Hapus foto lama terlebih dahulu.');
            return;
        }

        const errors = [];
        for (const file of incoming) {
            const isValidType = ALLOWED_TYPES.includes(file.type) || /\.(jpe?g|png|webp)$/i.test(file.name);
            if (!isValidType) {
                errors.push(`${file.name}: hanya JPG, PNG, atau WebP yang diizinkan.`);
                continue;
            }
            if (file.size > MAX_FILE_BYTES) {
                errors.push(`${file.name}: ukuran melebihi batas 5 MB.`);
                continue;
            }
            const duplicate = stagedFiles.some((f) => f.name === file.name && f.size === file.size && f.lastModified === file.lastModified);
            if (duplicate) continue;

            if (stagedFiles.length >= availableSlots) {
                errors.push(`Maksimal ${availableSlots} foto lagi yang dapat ditambahkan (batas total 5 foto).`);
                break;
            }
            stagedFiles.push(file);
        }

        syncInputFiles();
        renderStagingQueue();
        setSelectionError(errors.join(' '));
    }

    fileInput?.addEventListener('change', (e) => {
        queueFiles(e.target.files);
    });

    clearQueueBtn?.addEventListener('click', () => {
        stagedFiles = [];
        if (fileInput) fileInput.value = '';
        setSelectionError('');
        renderStagingQueue();
    });

    if (dropzone) {
        ['dragenter', 'dragover'].forEach((evName) => {
            dropzone.addEventListener(evName, (e) => {
                e.preventDefault();
                dropzone.classList.add('bg-[#FFD43B]/40', 'border-solid');
            });
        });
        ['dragleave', 'drop'].forEach((evName) => {
            dropzone.addEventListener(evName, (e) => {
                e.preventDefault();
                dropzone.classList.remove('bg-[#FFD43B]/40', 'border-solid');
            });
        });
        dropzone.addEventListener('drop', (e) => {
            queueFiles(e.dataTransfer?.files);
        });
    }

    function bindDelete(form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            try {
                const res = await fetch(form.action, { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken }, body: new FormData(form) });
                const data = await res.json();
                if (!res.ok || data.status === 'error') {
                    showBanner(data.message || 'Gagal menghapus foto.', 'error');
                    return;
                }
                form.closest('[data-foto-card]')?.remove();
                updateFotoUI(data.count ?? 0, data.max ?? 5);
                showBanner(data.message || 'Foto dihapus.');
            } catch (_) {
                showBanner('Terjadi gangguan jaringan saat menghapus foto.', 'error');
            }
        });
    }

    document.querySelectorAll('[data-foto-delete-form]').forEach(bindDelete);

    function renderGalleryCards(photos) {
        if (!gallery) return;
        gallery.replaceChildren();
        (photos || []).forEach((photo) => {
            const card = document.createElement('article');
            card.className = 'border-2 border-black rounded-xl overflow-hidden bg-slate-50 shadow-[2px_2px_0px_0px_#000] flex flex-col justify-between';
            card.dataset.fotoCard = String(photo.id);

            const img = document.createElement('img');
            img.src = photo.url;
            img.alt = photo.keterangan ? `Foto bukti: ${photo.keterangan}` : 'Foto bukti observasi';
            img.className = 'w-full h-28 object-cover border-b-2 border-black';

            const foot = document.createElement('div');
            foot.className = 'p-2 flex items-center justify-between gap-2';
            const cap = document.createElement('span');
            cap.className = 'text-[11px] font-bold truncate';
            cap.title = photo.keterangan || 'Bukti';
            cap.textContent = photo.keterangan || 'Bukti';

            const df = document.createElement('form');
            df.action = photo.delete_url;
            df.method = 'POST';
            df.dataset.fotoDeleteForm = String(photo.id);
            const tk = document.createElement('input');
            tk.type = 'hidden'; tk.name = '_token'; tk.value = csrfToken;
            const mt = document.createElement('input');
            mt.type = 'hidden'; mt.name = '_method'; mt.value = 'DELETE';
            const db = document.createElement('button');
            db.type = 'submit';
            db.setAttribute('aria-label', `Hapus foto bukti ${photo.keterangan || 'observasi'}`);
            db.className = 'min-h-[32px] px-2 py-0.5 rounded border border-black bg-rose-100 hover:bg-rose-200 text-[11px] font-black text-rose-950 cursor-pointer';
            db.textContent = 'Hapus';

            df.append(tk, mt, db);
            bindDelete(df);
            foot.append(cap, df);
            card.append(img, foot);
            gallery.appendChild(card);
        });
    }

    function submitPhotosWithProgress(formData) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', fotoForm.action, true);
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
            xhr.upload.addEventListener('progress', (ev) => {
                if (!ev.lengthComputable || !uploadProgress) return;
                const pct = Math.min(100, Math.round((ev.loaded / ev.total) * 100));
                uploadProgress.value = pct;
                if (uploadProgressPct) uploadProgressPct.textContent = `${pct}%`;
            });
            xhr.addEventListener('load', () => {
                try {
                    const data = JSON.parse(xhr.responseText || '{}');
                    resolve({ ok: xhr.status >= 200 && xhr.status < 300, status: xhr.status, data });
                } catch (_) {
                    reject(new Error('Respon server tidak valid.'));
                }
            });
            xhr.addEventListener('error', () => reject(new Error('Gangguan jaringan saat mengunggah foto.')));
            xhr.send(formData);
        });
    }

    fotoForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (stagedFiles.length === 0) {
            setSelectionError('Pilih minimal 1 foto bukti terlebih dahulu.');
            fileInput?.focus();
            return;
        }
        if (uploadedCount + stagedFiles.length > MAX_FOTOS) {
            setSelectionError(`Total foto melebihi batas 5 foto (sisa ${Math.max(0, MAX_FOTOS - uploadedCount)} slot).`);
            fileInput?.focus();
            return;
        }

        setSelectionError('');
        if (uploadBtn) uploadBtn.disabled = true;
        if (uploadProgressWrap) uploadProgressWrap.hidden = false;
        if (uploadProgress) uploadProgress.value = 0;
        if (uploadProgressPct) uploadProgressPct.textContent = '0%';

        const formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('keterangan', document.getElementById('keterangan_foto')?.value || '');
        stagedFiles.forEach((f) => formData.append('photos[]', f));

        try {
            const { ok, data } = await submitPhotosWithProgress(formData);
            if (!ok || data.status === 'error') {
                const msg = data.message || 'Gagal mengunggah foto.';
                setSelectionError(msg);
                showBanner(msg, 'error');
                return;
            }
            renderGalleryCards(data.photos || []);
            updateFotoUI(data.count ?? 0, data.max ?? MAX_FOTOS);
            stagedFiles = [];
            fotoForm.reset();
            renderStagingQueue();
            showBanner(data.message || 'Foto bukti supervisi berhasil diunggah.');
        } catch (err) {
            showBanner(err?.message || 'Gagal mengunggah foto.', 'error');
        } finally {
            if (uploadBtn) uploadBtn.disabled = false;
            if (uploadProgressWrap) uploadProgressWrap.hidden = true;
        }
    });
})();
</script>
@endpush
