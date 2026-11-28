@extends('layouts.supervisi')
@section('title', 'Modul Coaching TIRTA')
@section('page_title', 'Daftar & Penyesuaian Modul Coaching TIRTA')

@section('content')
@php
    use App\Models\Supervisi\SupervisiCoachingTirta;

    $activeTemplateTab = old('tahap', request('template_tahap', SupervisiCoachingTirta::TAHAP_PASCA));
    if (! in_array($activeTemplateTab, [SupervisiCoachingTirta::TAHAP_PRA, SupervisiCoachingTirta::TAHAP_PASCA], true)) {
        $activeTemplateTab = SupervisiCoachingTirta::TAHAP_PASCA;
    }

    $renderTirtaBadge = static function (?SupervisiCoachingTirta $tirta): array {
        if (! $tirta) {
            return ['bg-slate-100 text-slate-600', 'Belum Diisi'];
        }

        return match ($tirta->status) {
            SupervisiCoachingTirta::STATUS_CONFIRMED => ['bg-emerald-200 text-emerald-950', '✓ Dikonfirmasi Guru'],
            SupervisiCoachingTirta::STATUS_SUBMITTED => ['bg-[#FFD43B] text-black', '⏳ Menunggu Guru'],
            default => ['bg-sky-100 text-sky-950', '📝 Draf Coach'],
        };
    };
@endphp

<!-- Pengaturan Pertanyaan Global & Template Sistem Coaching TIRTA -->
<section class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="font-heading font-black text-base text-slate-900">⚙️ Pertanyaan Global &amp; Template Sistem Coaching TIRTA</h2>
            <p class="text-xs font-semibold text-slate-600">
                Pertanyaan T-I-R-TA yang diatur di sini berlaku <strong>global untuk seluruh guru</strong> (tampil pada form Supervisor, tampilan Guru, dan cetakan PDF). Template isian dapat digunakan sekali klik atau diterapkan massal ke jadwal yang belum terkirim.
            </p>
        </div>
        <div class="flex flex-wrap gap-1.5" role="tablist" aria-label="Tahap Template Global TIRTA">
            @foreach([SupervisiCoachingTirta::TAHAP_PRA => '1. Pra-Observasi', SupervisiCoachingTirta::TAHAP_PASCA => '2. Pasca-Observasi'] as $tahapKey => $tahapTitle)
                <button type="button"
                    role="tab"
                    aria-selected="{{ $activeTemplateTab === $tahapKey ? 'true' : 'false' }}"
                    data-global-tpl-tab="{{ $tahapKey }}"
                    class="px-3 py-1.5 rounded-lg border-2 border-black text-xs font-black cursor-pointer {{ $activeTemplateTab === $tahapKey ? 'bg-[#FFD43B] text-black shadow-[2px_2px_0px_0px_#000]' : 'bg-white text-slate-700 hover:bg-slate-100' }}">
                    {{ $tahapTitle }}
                </button>
            @endforeach
        </div>
    </div>

    @foreach([SupervisiCoachingTirta::TAHAP_PRA, SupervisiCoachingTirta::TAHAP_PASCA] as $tahapKey)
        @php
            $tpl = $tirtaTemplates->get($tahapKey);
            $isOldThisTab = old('tahap') === $tahapKey;
            $tahapLabel = $tahapKey === SupervisiCoachingTirta::TAHAP_PRA ? 'Pra-Observasi' : 'Pasca-Observasi';
        @endphp
        <div data-global-tpl-panel="{{ $tahapKey }}" {{ $activeTemplateTab === $tahapKey ? '' : 'hidden' }} class="space-y-4 pt-2 border-t-2 border-slate-200">
            <form action="{{ route("supervisi.{$portalPrefix}.coaching.template.update") }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="tahap" value="{{ $tahapKey }}">

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div class="p-4 rounded-xl border-2 border-black bg-[#FFF9DB]/60 space-y-3">
                        <div class="text-xs font-black text-slate-900 uppercase tracking-wide">
                            Pertanyaan Pemandu Global ({{ $tahapLabel }}) — Seragam untuk Semua Guru
                        </div>
                        <div>
                            <label for="q_tujuan_{{ $tahapKey }}" class="block text-[11px] font-black mb-1">Pertanyaan T — Tujuan</label>
                            <textarea id="q_tujuan_{{ $tahapKey }}" name="pertanyaan_tujuan" rows="2" required class="w-full px-3 py-1.5 text-xs border-2 border-black rounded-lg bg-white">{{ $isOldThisTab ? old('pertanyaan_tujuan') : $tpl?->pertanyaan_tujuan }}</textarea>
                        </div>
                        <div>
                            <label for="q_ident_{{ $tahapKey }}" class="block text-[11px] font-black mb-1">Pertanyaan I — Identifikasi Kondisi</label>
                            <textarea id="q_ident_{{ $tahapKey }}" name="pertanyaan_identifikasi" rows="2" required class="w-full px-3 py-1.5 text-xs border-2 border-black rounded-lg bg-white">{{ $isOldThisTab ? old('pertanyaan_identifikasi') : $tpl?->pertanyaan_identifikasi }}</textarea>
                        </div>
                        <div>
                            <label for="q_rencana_{{ $tahapKey }}" class="block text-[11px] font-black mb-1">Pertanyaan R — Rencana Aksi</label>
                            <textarea id="q_rencana_{{ $tahapKey }}" name="pertanyaan_rencana" rows="2" required class="w-full px-3 py-1.5 text-xs border-2 border-black rounded-lg bg-white">{{ $isOldThisTab ? old('pertanyaan_rencana') : $tpl?->pertanyaan_rencana }}</textarea>
                        </div>
                        <div>
                            <label for="q_tj_{{ $tahapKey }}" class="block text-[11px] font-black mb-1">Pertanyaan TA — Tanggung Jawab</label>
                            <textarea id="q_tj_{{ $tahapKey }}" name="pertanyaan_tanggung_jawab" rows="2" required class="w-full px-3 py-1.5 text-xs border-2 border-black rounded-lg bg-white">{{ $isOldThisTab ? old('pertanyaan_tanggung_jawab') : $tpl?->pertanyaan_tanggung_jawab }}</textarea>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl border-2 border-black bg-sky-50/70 space-y-3">
                        <div class="text-xs font-black text-slate-900 uppercase tracking-wide">
                            Template Isian Bawaan Sistem ({{ $tahapLabel }}) — Untuk Tombol Isi Cepat / Massal
                        </div>
                        <div>
                            <label for="t_tujuan_{{ $tahapKey }}" class="block text-[11px] font-black mb-1">Template T — Tujuan</label>
                            <textarea id="t_tujuan_{{ $tahapKey }}" name="template_tujuan" rows="2" class="w-full px-3 py-1.5 text-xs border-2 border-black rounded-lg bg-white">{{ $isOldThisTab ? old('template_tujuan') : $tpl?->template_tujuan }}</textarea>
                        </div>
                        <div>
                            <label for="t_ident_{{ $tahapKey }}" class="block text-[11px] font-black mb-1">Template I — Identifikasi Kondisi</label>
                            <textarea id="t_ident_{{ $tahapKey }}" name="template_identifikasi" rows="2" class="w-full px-3 py-1.5 text-xs border-2 border-black rounded-lg bg-white">{{ $isOldThisTab ? old('template_identifikasi') : $tpl?->template_identifikasi }}</textarea>
                        </div>
                        <div>
                            <label for="t_rencana_{{ $tahapKey }}" class="block text-[11px] font-black mb-1">Template R — Rencana Aksi</label>
                            <textarea id="t_rencana_{{ $tahapKey }}" name="template_rencana" rows="2" class="w-full px-3 py-1.5 text-xs border-2 border-black rounded-lg bg-white">{{ $isOldThisTab ? old('template_rencana') : $tpl?->template_rencana }}</textarea>
                        </div>
                        <div>
                            <label for="t_tj_{{ $tahapKey }}" class="block text-[11px] font-black mb-1">Template TA — Tanggung Jawab</label>
                            <textarea id="t_tj_{{ $tahapKey }}" name="template_tanggung_jawab" rows="2" class="w-full px-3 py-1.5 text-xs border-2 border-black rounded-lg bg-white">{{ $isOldThisTab ? old('template_tanggung_jawab') : $tpl?->template_tanggung_jawab }}</textarea>
                        </div>
                        <div>
                            <label for="t_coach_{{ $tahapKey }}" class="block text-[11px] font-black mb-1">Template Catatan Penguatan Coach</label>
                            <textarea id="t_coach_{{ $tahapKey }}" name="template_catatan_coach" rows="2" class="w-full px-3 py-1.5 text-xs border-2 border-black rounded-lg bg-white">{{ $isOldThisTab ? old('template_catatan_coach') : $tpl?->template_catatan_coach }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-2">
                    <button type="submit" class="px-4 py-2 bg-[#5294FF] hover:bg-blue-600 text-white text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                        💾 Simpan Pertanyaan &amp; Template Global ({{ $tahapLabel }})
                    </button>
                </div>
            </form>

            <form action="{{ route("supervisi.{$portalPrefix}.coaching.template.apply") }}" method="POST"
                onsubmit="return confirm('Terapkan template sistem {{ $tahapLabel }} ke jadwal yang masih kosong/draf (jadwal terkirim/dikonfirmasi/terkunci tidak akan tertimpa)?');"
                class="p-3 rounded-xl border-2 border-black bg-emerald-50 flex flex-wrap items-center justify-between gap-2">
                @csrf
                <input type="hidden" name="tahap" value="{{ $tahapKey }}">
                <input type="hidden" name="academic_year_id" value="{{ $selectedYearId }}">
                <div class="text-xs font-semibold text-emerald-950">
                    <strong>Terapkan Massal ({{ $tahapLabel }}):</strong> Mengisi otomatis seluruh jadwal {{ $portalPrefix === 'supervisor' ? 'binaan Anda ' : '' }}yang masih kosong/draf (untuk Pasca-Observasi hanya jadwal yang sudah memiliki Form B).
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <select name="aksi" aria-label="Status saat terapkan massal" class="px-2.5 py-1.5 text-xs border-2 border-black rounded-lg bg-white font-bold">
                        <option value="{{ SupervisiCoachingTirta::STATUS_DRAFT }}">Simpan sebagai Draf Coach</option>
                        <option value="{{ SupervisiCoachingTirta::STATUS_SUBMITTED }}">Simpan &amp; Kirim ke Guru</option>
                    </select>
                    <button type="submit" class="px-3.5 py-1.5 bg-[#20C997] hover:bg-emerald-400 text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                        ⚡ Terapkan Template ke Semua Jadwal Kosong/Draf
                    </button>
                </div>
            </form>
        </div>
    @endforeach
</section>

<!-- Filter & KPI -->
<section class="bg-white border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000] space-y-4">
    <form method="GET" action="{{ route("supervisi.{$portalPrefix}.coaching.index") }}" class="flex flex-wrap items-end gap-2.5">
        <div>
            <label for="filter_year" class="block text-[10px] font-black uppercase text-slate-500 mb-1">Tahun Ajaran</label>
            <select id="filter_year" name="academic_year_id" class="px-3 py-2 text-xs border-2 border-black rounded-lg bg-white font-bold">
                <option value="all" {{ $selectedYearId === 'all' ? 'selected' : '' }}>Semua Tahun Ajaran</option>
                @foreach($academicYears as $yr)
                    <option value="{{ $yr->id }}" {{ (string) $selectedYearId === (string) $yr->id ? 'selected' : '' }}>
                        {{ $yr->name }} ({{ ucfirst($yr->semester) }})
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="filter_status_tirta" class="block text-[10px] font-black uppercase text-slate-500 mb-1">Status Coaching TIRTA</label>
            <select id="filter_status_tirta" name="status_tirta" class="px-3 py-2 text-xs border-2 border-black rounded-lg bg-white font-bold">
                <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>Semua Status</option>
                <option value="none" {{ $statusFilter === 'none' ? 'selected' : '' }}>Belum Ada Catatan TIRTA</option>
                <option value="{{ SupervisiCoachingTirta::STATUS_DRAFT }}" {{ $statusFilter === SupervisiCoachingTirta::STATUS_DRAFT ? 'selected' : '' }}>Draf Coach</option>
                <option value="{{ SupervisiCoachingTirta::STATUS_SUBMITTED }}" {{ $statusFilter === SupervisiCoachingTirta::STATUS_SUBMITTED ? 'selected' : '' }}>Menunggu Konfirmasi Guru</option>
                <option value="{{ SupervisiCoachingTirta::STATUS_CONFIRMED }}" {{ $statusFilter === SupervisiCoachingTirta::STATUS_CONFIRMED ? 'selected' : '' }}>Sudah Dikonfirmasi Guru</option>
            </select>
        </div>

        <div class="flex-1 min-w-[180px]">
            <label for="filter_search" class="block text-[10px] font-black uppercase text-slate-500 mb-1">Cari Guru / Mapel</label>
            <input id="filter_search" type="search" name="q" value="{{ $search }}" placeholder="Ketik nama guru atau mapel..."
                class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white font-semibold">
        </div>

        <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-black rounded-lg border-2 border-black cursor-pointer">
            Terapkan Filter
        </button>
    </form>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 pt-2 border-t-2 border-slate-200">
        <div class="p-3 rounded-lg border-2 border-black bg-slate-50">
            <div class="text-[10px] font-black uppercase text-slate-500">Jadwal Aktif</div>
            <div class="text-xl font-heading font-black tabular-nums">{{ $summary['total'] }}</div>
        </div>
        <div class="p-3 rounded-lg border-2 border-black bg-sky-50">
            <div class="text-[10px] font-black uppercase text-sky-900">TIRTA Pra-Observasi</div>
            <div class="text-xl font-heading font-black tabular-nums text-sky-950">{{ $summary['pra_terisi'] }}</div>
        </div>
        <div class="p-3 rounded-lg border-2 border-black bg-[#FFF9DB]">
            <div class="text-[10px] font-black uppercase text-amber-900">TIRTA Pasca-Observasi</div>
            <div class="text-xl font-heading font-black tabular-nums text-amber-950">{{ $summary['pasca_terisi'] }}</div>
        </div>
        <div class="p-3 rounded-lg border-2 border-black bg-emerald-50">
            <div class="text-[10px] font-black uppercase text-emerald-900">Dikonfirmasi Guru</div>
            <div class="text-xl font-heading font-black tabular-nums text-emerald-950">{{ $summary['confirmed'] }}</div>
        </div>
    </div>
</section>

<!-- Daftar Sesi Coaching TIRTA -->
<section class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-3">
    <div>
        <h2 class="font-heading font-black text-sm">Daftar Sesi Coaching Alur TIRTA ({{ $jadwals->count() }})</h2>
        <p class="text-[11px] font-semibold text-slate-600">
            Kelola dialog Coaching T-I-R-TA tahap Pra-Observasi dan Pasca-Observasi per jadwal guru. Tahap Pasca-Observasi terbuka otomatis setelah minimal 1 instrumen Form B diisi.
        </p>
    </div>

    <div tabindex="0" role="region" aria-label="Tabel Daftar Sesi Coaching TIRTA" class="overflow-x-auto">
        <table class="w-full text-xs border-collapse border-2 border-black">
            <thead class="bg-slate-900 text-white">
                <tr>
                    <th class="p-2 border border-black text-left">Tanggal &amp; Guru</th>
                    <th class="p-2 border border-black text-left">Supervisor &amp; Mapel</th>
                    <th class="p-2 border border-black text-left">Form B</th>
                    <th class="p-2 border border-black text-left">TIRTA Pra-Observasi</th>
                    <th class="p-2 border border-black text-left">TIRTA Pasca-Observasi</th>
                    <th class="p-2 border border-black text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jadwals as $j)
                    @php
                        $pra = $j->coachingTirtas->firstWhere('tahap', SupervisiCoachingTirta::TAHAP_PRA);
                        $pasca = $j->coachingTirtas->firstWhere('tahap', SupervisiCoachingTirta::TAHAP_PASCA);
                        $praBadge = $renderTirtaBadge($pra);
                        $pascaBadge = $renderTirtaBadge($pasca);
                        $hasFormB = (int) ($j->scored_components_count ?? 0) > 0;
                        $isLocked = $j->signatures->where('dokumen_type', 'supervisi')->isNotEmpty();
                    @endphp
                    <tr class="odd:bg-white even:bg-slate-50">
                        <td class="p-2 border border-black">
                            <div class="font-black">{{ $j->teacher?->user?->name ?? '-' }}</div>
                            <div class="text-[11px] text-slate-600">{{ $j->tanggal?->format('d/m/Y') }} · {{ $j->statusEnum()->label() }}</div>
                        </td>
                        <td class="p-2 border border-black">
                            <div class="font-bold">{{ $j->supervisor_display_name }}</div>
                            <div class="text-[11px] text-slate-600">{{ $j->schoolClass?->name ?? '-' }} / {{ $j->subject?->name ?? '-' }}</div>
                        </td>
                        <td class="p-2 border border-black">
                            @if($hasFormB)
                                <span class="px-2 py-0.5 rounded border border-black bg-emerald-100 text-emerald-950 text-[11px] font-black">
                                    ✓ {{ $j->scored_components_count }} Instrumen
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded border border-black bg-slate-100 text-slate-600 text-[11px] font-bold">
                                    Belum Ada
                                </span>
                            @endif
                        </td>
                        <td class="p-2 border border-black">
                            <span class="px-2 py-0.5 rounded border border-black text-[11px] font-black {{ $praBadge[0] }}">
                                {{ $praBadge[1] }}
                            </span>
                        </td>
                        <td class="p-2 border border-black">
                            <span class="px-2 py-0.5 rounded border border-black text-[11px] font-black {{ $pascaBadge[0] }}">
                                {{ $pascaBadge[1] }}
                            </span>
                            @if($isLocked)
                                <span class="ml-1 px-1.5 py-0.5 rounded border border-black bg-rose-100 text-rose-950 text-[10px] font-black">🔒 TTD</span>
                            @endif
                        </td>
                        <td class="p-2 border border-black text-center">
                            <div class="inline-flex flex-wrap justify-center gap-1">
                                <a href="{{ route("supervisi.{$portalPrefix}.coaching.edit", $j) }}"
                                    class="px-2.5 py-1 bg-[#FFD43B] hover:bg-amber-300 text-black border border-black rounded font-black">
                                    {{ $isLocked ? '👁️ Lihat TIRTA' : '✏️ Edit TIRTA' }}
                                </a>
                                @if($j->coachingTirtas->isNotEmpty())
                                    <a href="{{ route('supervisi.cetak', ['jadwal' => $j, 'type' => 'coaching']) }}" target="_blank"
                                        class="px-2.5 py-1 bg-[#20C997] hover:bg-emerald-400 text-black border border-black rounded font-black">
                                        🖨️ Cetak
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-4 text-center text-slate-500 font-semibold">
                            Tidak ada jadwal aktif yang cocok dengan filter Coaching TIRTA.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection

@push('scripts')
<script>
(() => {
    const tabs = Array.from(document.querySelectorAll('[data-global-tpl-tab]'));
    const panels = Array.from(document.querySelectorAll('[data-global-tpl-panel]'));
    if (tabs.length === 0) return;

    tabs.forEach((btn) => {
        btn.addEventListener('click', () => {
            const target = btn.dataset.globalTplTab;
            tabs.forEach((t) => {
                const active = t.dataset.globalTplTab === target;
                t.setAttribute('aria-selected', active ? 'true' : 'false');
                t.className = `px-3 py-1.5 rounded-lg border-2 border-black text-xs font-black cursor-pointer ${
                    active ? 'bg-[#FFD43B] text-black shadow-[2px_2px_0px_0px_#000]' : 'bg-white text-slate-700 hover:bg-slate-100'
                }`;
            });
            panels.forEach((p) => {
                p.hidden = p.dataset.globalTplPanel !== target;
            });
        });
    });
})();
</script>
@endpush
