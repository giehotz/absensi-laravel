@extends('layouts.supervisi')
@section('title', 'Hub Penilaian 14 Form Supervisi')
@section('page_title', 'Formulir Observasi & Penilaian (Hub 14 Instrumen)')

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

    $groups = [
        ['title' => 'A. Administrasi & Telaah Perangkat Ajar', 'items' => $groupA],
        ['title' => 'B. Proses, Evaluasi & Pengembangan Diri', 'items' => $groupB],
        ['title' => 'C. Observasi Praktik Kinerja PMM', 'items' => $groupC],
    ];

    $completedOnJadwal = 0;
    $partialOnJadwal = 0;
    $totalAspectsAll = 0;
    $filledAspectsAll = 0;
    $grandTotalSkor = 0;
    $grandMaxSkor = 0;

    foreach ($jenisList as $jItem) {
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
                <div class="text-[10px] font-bold text-slate-500 uppercase">Form Lengkap</div>
                <div class="text-sm font-black tabular-nums">{{ $completedOnJadwal }} / {{ $jenisList->count() }}</div>
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
                    <span class="px-2 py-0.5 rounded border border-black bg-white text-[11px] font-black tabular-nums">{{ $completedOnJadwal }}/{{ $jenisList->count() }}</span>
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
                Pilih Form Penilaian (14 Jenis Instrumen · {{ $totalAspectsAll }} Aspek)
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
<section id="step-panel-2" data-hub-panel="2" {{ $initialStep === 2 ? '' : 'hidden' }} class="space-y-5">
    <div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h3 id="hub-heading-2" tabindex="-1" class="font-heading font-black text-base focus:outline-none">📸 Step 2: Dokumentasi Foto Bukti (Maks. 5 Foto)</h3>
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

    // Foto Bukti AJAX
    const fotoForm = document.getElementById('foto-upload-form');
    const gallery = document.getElementById('foto-gallery-grid');
    const emptyHint = document.getElementById('foto-empty-hint');
    const quotaBadge = document.getElementById('foto-quota-badge');
    const stepBadge = document.querySelector('[data-foto-step-count]');

    function updateFotoUI(count, max = 5) {
        if (quotaBadge) quotaBadge.textContent = `${count} / ${max} Foto`;
        if (stepBadge) stepBadge.textContent = `${count}/${max}`;
        if (gallery) gallery.hidden = count === 0;
        if (emptyHint) emptyHint.hidden = count > 0;
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

    fotoForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('btn-upload-foto');
        if (btn) btn.disabled = true;
        try {
            const res = await fetch(fotoForm.action, { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken }, body: new FormData(fotoForm) });
            const data = await res.json();
            if (!res.ok || data.status === 'error') {
                showBanner(data.message || 'Gagal mengunggah foto.', 'error');
                return;
            }
            if (gallery) {
                gallery.replaceChildren();
                (data.photos || []).forEach((photo) => {
                    const card = document.createElement('div');
                    card.className = 'border-2 border-black rounded-lg overflow-hidden bg-slate-50';
                    card.dataset.fotoCard = String(photo.id);
                    const img = document.createElement('img');
                    img.src = photo.url;
                    img.alt = photo.keterangan || 'Foto bukti';
                    img.className = 'w-full h-28 object-cover';
                    const foot = document.createElement('div');
                    foot.className = 'p-2 flex items-center justify-between gap-2';
                    const cap = document.createElement('span');
                    cap.className = 'text-[11px] font-semibold truncate';
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
                    db.type = 'submit'; db.className = 'text-[11px] font-black text-rose-700 hover:underline cursor-pointer'; db.textContent = 'Hapus';
                    df.append(tk, mt, db);
                    bindDelete(df);
                    foot.append(cap, df);
                    card.append(img, foot);
                    gallery.appendChild(card);
                });
            }
            updateFotoUI(data.count ?? 0, data.max ?? 5);
            fotoForm.reset();
            showBanner(data.message || 'Foto berhasil diunggah.');
        } catch (_) {
            showBanner('Gagal mengunggah foto.', 'error');
        } finally {
            if (btn) btn.disabled = false;
        }
    });
})();
</script>
@endpush
