@extends('layouts.supervisi')
@section('title', 'Edit Modul Coaching TIRTA')
@section('page_title', 'Penyesuaian Modul Coaching Alur TIRTA')

@section('content')
@php
    use App\Models\Supervisi\SupervisiCoachingTirta;

    $currentTab = old('tahap', $activeTahap);
    $stages = [
        SupervisiCoachingTirta::TAHAP_PRA => [
            'title' => 'Tahap 1: Coaching Pra-Observasi',
            'subtitle' => 'Dialog perencanaan sebelum observasi kelas: tujuan pembelajaran, fokus perilaku, dan strategi persiapan guru.',
            'record' => $coachingPra,
            'template' => $tirtaTemplates->get(SupervisiCoachingTirta::TAHAP_PRA),
            'unlocked' => true,
        ],
        SupervisiCoachingTirta::TAHAP_PASCA => [
            'title' => 'Tahap 2: Coaching Pasca-Observasi (Feedback Conference)',
            'subtitle' => 'Wawancara umpan balik berbasis hasil Form B untuk menggali refleksi, rencana perbaikan, dan komitmen guru.',
            'record' => $coachingPasca,
            'template' => $tirtaTemplates->get(SupervisiCoachingTirta::TAHAP_PASCA),
            'unlocked' => $hasFormBScores,
        ],
    ];
@endphp

<!-- Header Jadwal & Navigasi -->
<section class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="space-y-1">
            <a href="{{ route("supervisi.{$portalPrefix}.coaching.index") }}"
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border-2 border-black bg-white hover:bg-slate-100 text-xs font-black shadow-[1.5px_1.5px_0px_0px_#000]">
                ← Kembali ke Daftar Coaching TIRTA
            </a>
            <h2 class="font-heading font-black text-lg text-slate-900 pt-1">
                {{ $jadwal->teacher?->user?->name }} — {{ $jadwal->subject?->name ?? '-' }}
            </h2>
            <p class="text-xs font-semibold text-slate-600">
                Supervisor: <strong>{{ $jadwal->supervisor_display_name }}</strong> ·
                Kelas: <strong>{{ $jadwal->schoolClass?->name ?? '-' }}</strong> ·
                Tanggal: <strong>{{ $jadwal->tanggal?->format('d/m/Y') }}</strong> ·
                Status: <span class="px-1.5 py-0.5 rounded border border-black {{ $jadwal->statusEnum()->badgeClass() }}">{{ $jadwal->statusEnum()->label() }}</span>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route("supervisi.{$portalPrefix}.penilaian.edit", $jadwal) }}"
                class="px-3 py-2 bg-[#FFD43B] hover:bg-amber-300 text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                📝 Penilaian Form B
            </a>
            @if($portalPrefix === 'supervisor' && $hasFormBScores)
                <a href="{{ route('supervisi.supervisor.tindak-lanjut.show', $jadwal) }}"
                    class="px-3 py-2 bg-sky-100 hover:bg-sky-200 text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                    🔁 Form C &amp; D
                </a>
            @endif
            @if($coachingPra || $coachingPasca)
                <a href="{{ route('supervisi.cetak', ['jadwal' => $jadwal, 'type' => 'coaching']) }}" target="_blank"
                    class="px-3 py-2 bg-[#20C997] hover:bg-emerald-400 text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                    🖨️ Cetak Lembar TIRTA
                </a>
            @endif
        </div>
    </div>

    @if($isLocked)
        <div role="alert" class="p-3 rounded-lg border-2 border-black bg-rose-100 text-rose-950 text-xs font-bold">
            🔒 Dokumen jadwal ini telah mulai ditandatangani secara digital. Formulir Coaching TIRTA terkunci (baca-saja) kecuali tanda tangan di-reset oleh Admin.
        </div>
    @endif

    @if($hasFormBScores)
        <div class="p-3 bg-sky-50 border-2 border-black rounded-lg space-y-1.5 text-xs">
            <div class="font-black text-sky-950">📊 Ringkasan Hasil Observasi Form B ({{ $scoredComponents->count() }} Instrumen Terisi)</div>
            <div class="flex flex-wrap gap-1.5">
                @foreach($scoredComponents as $comp)
                    <span class="px-2 py-0.5 rounded border border-black bg-white text-[11px] font-bold">
                        {{ $comp->jenisPenilaian?->kode }}: <strong>{{ number_format((float) $comp->nilai_akhir, 1) }}%</strong> ({{ $comp->ketercapaian }})
                    </span>
                @endforeach
            </div>
        </div>
    @else
        <div class="p-3 bg-amber-50 border-2 border-black rounded-lg text-xs font-bold text-amber-950 flex flex-wrap items-center justify-between gap-2">
            <span>ℹ️ Form B (Observasi Kelas) belum diisi. Coaching <strong>Pra-Observasi</strong> tetap dapat diisi sekarang, sedangkan <strong>Pasca-Observasi</strong> terbuka setelah minimal 1 instrumen Form B tersimpan.</span>
        </div>
    @endif
</section>

<!-- Switcher Tab 2 Tahap -->
<div class="flex flex-wrap gap-2" role="tablist" aria-label="Tahap Coaching TIRTA">
    @foreach($stages as $tahapKey => $stage)
        @php
            $rec = $stage['record'];
            $isActiveTab = $currentTab === $tahapKey;
            $statusBadge = match ($rec?->status) {
                SupervisiCoachingTirta::STATUS_CONFIRMED => '✓ Dikonfirmasi',
                SupervisiCoachingTirta::STATUS_SUBMITTED => '⏳ Terkirim',
                SupervisiCoachingTirta::STATUS_DRAFT => '📝 Draf',
                default => 'Belum Diisi',
            };
        @endphp
        <button type="button"
            role="tab"
            aria-selected="{{ $isActiveTab ? 'true' : 'false' }}"
            data-tirta-tab="{{ $tahapKey }}"
            class="px-4 py-2.5 rounded-xl border-2 border-black text-xs font-black cursor-pointer transition-all {{ $isActiveTab ? 'bg-[#FFD43B] text-black shadow-[3px_3px_0px_0px_#000]' : 'bg-white text-slate-700 hover:bg-slate-100' }}">
            <span>{{ $tahapKey === SupervisiCoachingTirta::TAHAP_PRA ? '1. Pra-Observasi' : '2. Pasca-Observasi' }}</span>
            <span class="ml-1.5 px-1.5 py-0.5 rounded border border-black bg-white text-[10px]">{{ $statusBadge }}</span>
        </button>
    @endforeach
</div>

<!-- Panel Form Edit Per Tahap -->
@foreach($stages as $tahapKey => $stage)
    @php
        $rec = $stage['record'];
        $tpl = $stage['template'];
        $isActiveTab = $currentTab === $tahapKey;
        $isFormTab = old('tahap') === $tahapKey;
        $canEditStage = ! $isLocked && $stage['unlocked'];
        $stageStatus = $rec?->status ?? SupervisiCoachingTirta::STATUS_DRAFT;
        $stageStatusClass = match ($stageStatus) {
            SupervisiCoachingTirta::STATUS_CONFIRMED => 'bg-emerald-200 text-emerald-950',
            SupervisiCoachingTirta::STATUS_SUBMITTED => 'bg-[#FFD43B] text-black',
            default => 'bg-slate-200 text-slate-800',
        };
        $stageStatusLabel = match ($stageStatus) {
            SupervisiCoachingTirta::STATUS_CONFIRMED => '✓ Dikonfirmasi Guru',
            SupervisiCoachingTirta::STATUS_SUBMITTED => '⏳ Menunggu Konfirmasi Guru',
            default => $rec ? '📝 Draf Coach' : 'Belum Tersimpan',
        };
        $tplPayload = [
            'tujuan_coaching' => (string) ($tpl?->template_tujuan ?? ''),
            'identifikasi_kondisi' => (string) ($tpl?->template_identifikasi ?? ''),
            'rencana_aksi' => (string) ($tpl?->template_rencana ?? ''),
            'tanggung_jawab' => (string) ($tpl?->template_tanggung_jawab ?? ''),
            'catatan_coach' => (string) ($tpl?->template_catatan_coach ?? ''),
        ];
    @endphp
    <section data-tirta-panel="{{ $tahapKey }}" {{ $isActiveTab ? '' : 'hidden' }}
        class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-4">
        <div class="flex flex-wrap items-start justify-between gap-2 border-b-2 border-slate-200 pb-3">
            <div>
                <h3 class="font-heading font-black text-base text-slate-900">{{ $stage['title'] }}</h3>
                <p class="text-[11px] font-semibold text-slate-600">{{ $stage['subtitle'] }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if($canEditStage)
                    <button type="button"
                        data-fill-tirta-template="{{ $tahapKey }}"
                        data-template-payload='@json($tplPayload)'
                        class="px-3 py-1.5 rounded-lg border-2 border-black bg-[#FFF9DB] hover:bg-[#FFD43B] text-black text-[11px] font-black shadow-[1.5px_1.5px_0px_0px_#000] cursor-pointer">
                        ⚡ Isi dari Template Sistem
                    </button>
                @endif
                <span class="px-2.5 py-1 rounded border border-black text-[11px] font-black {{ $stageStatusClass }}">
                    {{ $stageStatusLabel }}
                </span>
            </div>
        </div>

        @if(! $stage['unlocked'])
            <div class="p-4 rounded-xl border-2 border-black bg-rose-50 text-xs font-bold text-rose-950 flex flex-wrap items-center justify-between gap-3">
                <span>⚠️ Tahap Pasca-Observasi membutuhkan minimal 1 komponen penilaian Form B terlebih dahulu.</span>
                <a href="{{ route("supervisi.{$portalPrefix}.penilaian.edit", $jadwal) }}"
                    class="px-3 py-1.5 rounded-lg border-2 border-black bg-[#FFD43B] text-black font-black">
                    Isi Penilaian Form B →
                </a>
            </div>
        @else
            <form data-tirta-form="{{ $tahapKey }}" action="{{ route("supervisi.{$portalPrefix}.coaching.update", $jadwal) }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="tahap" value="{{ $tahapKey }}">

                <div>
                    <label for="tujuan_{{ $tahapKey }}" class="block text-xs font-black mb-1">
                        T — Tujuan Wawancara <span aria-hidden="true" class="text-rose-600">*</span>
                    </label>
                    <p class="text-[10px] text-slate-600 mb-1 italic">"{{ $tpl?->pertanyaan_tujuan }}"</p>
                    <textarea id="tujuan_{{ $tahapKey }}" name="tujuan_coaching" rows="3" required {{ $canEditStage ? '' : 'disabled' }}
                        class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg {{ $canEditStage ? 'bg-white' : 'bg-slate-100 text-slate-600' }}">{{ $isFormTab ? old('tujuan_coaching') : $rec?->tujuan_coaching }}</textarea>
                </div>

                <div>
                    <label for="identifikasi_{{ $tahapKey }}" class="block text-xs font-black mb-1">I — Identifikasi Kondisi &amp; Kendala</label>
                    <p class="text-[10px] text-slate-600 mb-1 italic">"{{ $tpl?->pertanyaan_identifikasi }}"</p>
                    <textarea id="identifikasi_{{ $tahapKey }}" name="identifikasi_kondisi" rows="3" {{ $canEditStage ? '' : 'disabled' }}
                        class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg {{ $canEditStage ? 'bg-white' : 'bg-slate-100 text-slate-600' }}">{{ $isFormTab ? old('identifikasi_kondisi') : $rec?->identifikasi_kondisi }}</textarea>
                </div>

                <div>
                    <label for="rencana_{{ $tahapKey }}" class="block text-xs font-black mb-1">R — Rencana Aksi Konkret</label>
                    <p class="text-[10px] text-slate-600 mb-1 italic">"{{ $tpl?->pertanyaan_rencana }}"</p>
                    <textarea id="rencana_{{ $tahapKey }}" name="rencana_aksi" rows="3" {{ $canEditStage ? '' : 'disabled' }}
                        class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg {{ $canEditStage ? 'bg-white' : 'bg-slate-100 text-slate-600' }}">{{ $isFormTab ? old('rencana_aksi') : $rec?->rencana_aksi }}</textarea>
                </div>

                <div>
                    <label for="tanggung_jawab_{{ $tahapKey }}" class="block text-xs font-black mb-1">TA — Tanggung Jawab &amp; Jadwal</label>
                    <p class="text-[10px] text-slate-600 mb-1 italic">"{{ $tpl?->pertanyaan_tanggung_jawab }}"</p>
                    <textarea id="tanggung_jawab_{{ $tahapKey }}" name="tanggung_jawab" rows="3" {{ $canEditStage ? '' : 'disabled' }}
                        class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg {{ $canEditStage ? 'bg-white' : 'bg-slate-100 text-slate-600' }}">{{ $isFormTab ? old('tanggung_jawab') : $rec?->tanggung_jawab }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label for="catatan_coach_{{ $tahapKey }}" class="block text-xs font-black mb-1">Catatan Penguatan dari Supervisor / Admin (Coach)</label>
                    <textarea id="catatan_coach_{{ $tahapKey }}" name="catatan_coach" rows="2" {{ $canEditStage ? '' : 'disabled' }}
                        placeholder="Umpan balik penguatan untuk coachee..."
                        class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg {{ $canEditStage ? 'bg-white' : 'bg-slate-100 text-slate-600' }}">{{ $isFormTab ? old('catatan_coach') : $rec?->catatan_coach }}</textarea>
                </div>

                @if($rec && $rec->catatan_guru)
                    <div class="md:col-span-2 p-3 rounded-lg border-2 border-black bg-emerald-50 text-xs space-y-1">
                        <div class="font-black text-emerald-950">✓ Komitmen &amp; Refleksi Guru ({{ $rec->guru_confirmed_at?->format('d/m/Y H:i') }})</div>
                        <p class="text-slate-800">{{ $rec->catatan_guru }}</p>
                    </div>
                @endif

                @if($canEditStage)
                    <div class="md:col-span-2 flex flex-wrap justify-end gap-2 pt-2 border-t-2 border-slate-200">
                        <button type="submit" name="aksi" value="{{ SupervisiCoachingTirta::STATUS_DRAFT }}"
                            class="px-4 py-2 bg-white hover:bg-slate-100 text-black text-xs font-black rounded-lg border-2 border-black cursor-pointer">
                            💾 Simpan Draf
                        </button>
                        <button type="submit" name="aksi" value="{{ SupervisiCoachingTirta::STATUS_SUBMITTED }}"
                            class="px-4 py-2 bg-[#5294FF] hover:bg-blue-600 text-white text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                            📤 Simpan &amp; Kirim ke Guru
                        </button>
                    </div>
                @endif
            </form>
        @endif
    </section>
@endforeach
@endsection

@push('scripts')
<script>
(() => {
    const tabs = Array.from(document.querySelectorAll('[data-tirta-tab]'));
    const panels = Array.from(document.querySelectorAll('[data-tirta-panel]'));

    tabs.forEach((btn) => {
        btn.addEventListener('click', () => {
            const targetKey = btn.dataset.tirtaTab;
            tabs.forEach((t) => {
                const active = t.dataset.tirtaTab === targetKey;
                t.setAttribute('aria-selected', active ? 'true' : 'false');
                t.className = `px-4 py-2.5 rounded-xl border-2 border-black text-xs font-black cursor-pointer transition-all ${
                    active ? 'bg-[#FFD43B] text-black shadow-[3px_3px_0px_0px_#000]' : 'bg-white text-slate-700 hover:bg-slate-100'
                }`;
            });

            panels.forEach((panel) => {
                panel.hidden = panel.dataset.tirtaPanel !== targetKey;
            });
        });
    });

    document.querySelectorAll('[data-fill-tirta-template]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const key = btn.dataset.fillTirtaTemplate;
            const form = document.querySelector(`[data-tirta-form="${key}"]`);
            if (!form) return;

            let payload = {};
            try {
                payload = JSON.parse(btn.dataset.templatePayload || '{}');
            } catch (e) {
                return;
            }

            Object.entries(payload).forEach(([field, val]) => {
                const el = form.querySelector(`[name="${field}"]`);
                if (el && !el.disabled && typeof val === 'string') {
                    el.value = val;
                }
            });
        });
    });
})();
</script>
@endpush
