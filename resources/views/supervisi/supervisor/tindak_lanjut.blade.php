@extends('layouts.supervisi')
@section('title', 'Review Tindak Lanjut (Form C & D)')
@section('page_title', 'Review Tindak Lanjut & Evaluasi Refleksi Guru')

@section('content')
@php
    use App\Models\Supervisi\SupervisiCoachingTirta;

    $tl = $jadwal->tindakLanjut;
    $routePrefix = $portalPrefix ?? 'supervisor';
    $scoredComponents = $jadwal->hasils->filter(fn ($h): bool => $h->detailHasils->isNotEmpty());
    $tirtaStatus = $coachingPasca?->status ?? SupervisiCoachingTirta::STATUS_DRAFT;
    $tirtaBadge = match ($tirtaStatus) {
        SupervisiCoachingTirta::STATUS_CONFIRMED => ['bg-emerald-200 text-emerald-950', '✓ Dikonfirmasi Guru'],
        SupervisiCoachingTirta::STATUS_SUBMITTED => ['bg-[#FFD43B] text-black', '⏳ Menunggu Konfirmasi Guru'],
        default => ['bg-slate-200 text-slate-800', '📝 Draf Coach'],
    };
@endphp

<div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-4">
    <h2 class="font-heading font-black text-base">{{ $jadwal->teacher?->user?->name }} — Nilai Observasi: {{ $jadwal->nilai_akhir ?? '-' }} ({{ $jadwal->ketercapaian ?? '-' }})</h2>

    @if(!$tl)
        <p class="text-xs font-bold text-amber-900 bg-amber-50 p-3 border-2 border-black rounded-lg">Guru belum mengisi Formulir C (Rencana Tindak Lanjut).</p>
    @else
        <div class="p-4 bg-slate-50 border-2 border-black rounded-lg text-xs space-y-2">
            <h3 class="font-black text-sm">Isian Formulir C Guru</h3>
            <div><strong>Refleksi Guru:</strong> {{ $tl->refleksi_guru }}</div>
            <div><strong>Tujuan & Upaya Tindak Lanjut:</strong> {{ $tl->tujuan_tindak_lanjut }} — {{ $tl->upaya_tindak_lanjut }}</div>
            <div><strong>Kebutuhan Dukungan:</strong> {{ $tl->kebutuhan_dukungan ?: '-' }} ({{ $tl->detail_dukungan ?: '-' }})</div>
        </div>

        <form action="{{ route("supervisi.{$routePrefix}.tindak-lanjut.feedback-c", $jadwal) }}" method="POST" class="space-y-3 border-t-2 border-slate-200 pt-3">
            @csrf
            <h4 class="font-black text-xs">Umpan Balik Supervisor untuk Formulir C</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <input type="text" name="respon_supervisor_c" value="{{ old('respon_supervisor_c', $tl->respon_supervisor_c ?: 'Disetujui & Mendukung Penuh') }}" required placeholder="Respon Supervisor" class="px-3 py-2 text-xs border-2 border-black rounded-lg">
                <input type="text" name="catatan_supervisor_c" value="{{ old('catatan_supervisor_c', $tl->catatan_supervisor_c) }}" placeholder="Catatan pembinaan Form C" class="px-3 py-2 text-xs border-2 border-black rounded-lg">
            </div>
            <button type="submit" class="px-4 py-2 bg-[#FFD43B] text-black text-xs font-black rounded-lg border-2 border-black cursor-pointer">Simpan Feedback Form C</button>
        </form>

        @if($tl->inspirasi_baru)
            <div class="p-4 bg-emerald-50 border-2 border-black rounded-lg text-xs space-y-2 mt-4">
                <h3 class="font-black text-sm">Isian Formulir D (Refleksi) Guru</h3>
                <div><strong>Inspirasi Baru:</strong> {{ $tl->inspirasi_baru }}</div>
                <div><strong>Perubahan Praktik:</strong> {{ $tl->perubahan_praktik }}</div>
                <div><strong>Kendala & Perbaikan Berkelanjutan:</strong> {{ $tl->kendala_tindak_lanjut ?: '-' }} / {{ $tl->rencana_perbaikan_berkelanjutan ?: '-' }}</div>
            </div>

            <form action="{{ route("supervisi.{$routePrefix}.tindak-lanjut.evaluate-d", $jadwal) }}" method="POST" class="space-y-3 border-t-2 border-slate-200 pt-3">
                @csrf
                <h4 class="font-black text-xs">Evaluasi Akhir Supervisor untuk Formulir D</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <input type="text" name="penilaian_supervisor_d" value="{{ old('penilaian_supervisor_d', $tl->penilaian_supervisor_d ?: 'Sangat Berdampak & Konsisten') }}" required placeholder="Penilaian Dampak" class="px-3 py-2 text-xs border-2 border-black rounded-lg">
                    <input type="text" name="catatan_supervisor_d" value="{{ old('catatan_supervisor_d', $tl->catatan_supervisor_d) }}" placeholder="Catatan penutup" class="px-3 py-2 text-xs border-2 border-black rounded-lg">
                </div>
                <button type="submit" class="px-4 py-2 bg-[#20C997] text-black text-xs font-black rounded-lg border-2 border-black cursor-pointer">✅ Sahkan Evaluasi Form D & Selesaikan Siklus</button>
            </form>
        @endif
    @endif
</div>

<div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <div>
            <h3 class="font-heading font-black text-sm">💬 Wawancara Coaching Alur TIRTA Pasca-Observasi (2 Arah)</h3>
            <p class="text-[11px] text-slate-600">Tahap Feedback Conference (Bab III B.3 Juknis SK 6334/2021): isi percakapan T-I-R-TA bersama Guru lalu kirim untuk konfirmasi.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="px-2.5 py-1 rounded border border-black text-[11px] font-black {{ $tirtaBadge[0] }}">
                {{ $tirtaBadge[1] }}
            </span>
            @if($coachingPasca)
                <a href="{{ route('supervisi.cetak', ['jadwal' => $jadwal, 'type' => 'coaching']) }}" target="_blank"
                    class="px-3 py-1 bg-[#20C997] hover:bg-emerald-400 text-black text-xs font-black rounded-lg border-2 border-black">
                    🖨️ Cetak Lembar TIRTA
                </a>
            @endif
        </div>
    </div>

    @if($scoredComponents->isNotEmpty())
        <div class="p-3 bg-sky-50 border-2 border-black rounded-lg space-y-1.5 text-xs">
            <div class="font-black text-sky-950">📊 Ringkasan Hasil Observasi Form B sebagai Bahan Wawancara</div>
            <div class="flex flex-wrap gap-1.5">
                @foreach($scoredComponents as $comp)
                    <span class="px-2 py-0.5 rounded border border-black bg-white text-[11px] font-bold">
                        {{ $comp->jenisPenilaian?->kode }}: <strong>{{ number_format((float) $comp->nilai_akhir, 1) }}%</strong> ({{ $comp->ketercapaian }})
                    </span>
                @endforeach
            </div>
        </div>
    @else
        <div class="p-3 bg-rose-50 border-2 border-black rounded-lg text-xs font-bold text-rose-950">
            ⚠️ Minimal satu komponen Form B harus diisi sebelum menyusun Coaching TIRTA.
        </div>
    @endif

    <form action="{{ route("supervisi.{$routePrefix}.tindak-lanjut.coaching", $jadwal) }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-3">
        @csrf
        <div>
            <label for="tirta_tujuan" class="block text-xs font-black mb-1">T — Tujuan Wawancara</label>
            <p class="text-[10px] text-slate-600 mb-1 italic">"Apa tujuan yang ingin Bapak/Ibu capai dan kompetensi apa yang ingin ditingkatkan?"</p>
            <textarea id="tirta_tujuan" name="tujuan_coaching" rows="2" required class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">{{ old('tujuan_coaching', $coachingPasca?->tujuan_coaching) }}</textarea>
        </div>
        <div>
            <label for="tirta_identifikasi" class="block text-xs font-black mb-1">I — Identifikasi Kondisi &amp; Kendala</label>
            <p class="text-[10px] text-slate-600 mb-1 italic">"Berdasarkan observasi kelas, bagian mana yang berjalan baik dan kendala apa yang masih muncul?"</p>
            <textarea id="tirta_identifikasi" name="identifikasi_kondisi" rows="2" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">{{ old('identifikasi_kondisi', $coachingPasca?->identifikasi_kondisi) }}</textarea>
        </div>
        <div>
            <label for="tirta_rencana" class="block text-xs font-black mb-1">R — Rencana Aksi Konkret</label>
            <p class="text-[10px] text-slate-600 mb-1 italic">"Langkah konkret dan sumber daya apa yang akan dijalankan untuk perbaikan?"</p>
            <textarea id="tirta_rencana" name="rencana_aksi" rows="2" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">{{ old('rencana_aksi', $coachingPasca?->rencana_aksi) }}</textarea>
        </div>
        <div>
            <label for="tirta_tanggung_jawab" class="block text-xs font-black mb-1">TA — Tanggung Jawab &amp; Jadwal</label>
            <p class="text-[10px] text-slate-600 mb-1 italic">"Kapan rencana mulai dijalankan dan bagaimana indikator keberhasilannya?"</p>
            <textarea id="tirta_tanggung_jawab" name="tanggung_jawab" rows="2" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">{{ old('tanggung_jawab', $coachingPasca?->tanggung_jawab) }}</textarea>
        </div>
        <div class="md:col-span-2">
            <label for="tirta_catatan_coach" class="block text-xs font-black mb-1">Catatan Penguatan dari Supervisor (Coach)</label>
            <textarea id="tirta_catatan_coach" name="catatan_coach" rows="2" placeholder="Umpan balik penguatan dari supervisor untuk coachee..." class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">{{ old('catatan_coach', $coachingPasca?->catatan_coach) }}</textarea>
        </div>

        @if($coachingPasca && $coachingPasca->catatan_guru)
            <div class="md:col-span-2 p-3 rounded-lg border-2 border-black bg-emerald-50 text-xs space-y-1">
                <div class="font-black text-emerald-950">✓ Komitmen &amp; Refleksi Guru ({{ $coachingPasca->guru_confirmed_at?->format('d/m/Y H:i') }})</div>
                <p class="text-slate-800">{{ $coachingPasca->catatan_guru }}</p>
            </div>
        @endif

        <div class="md:col-span-2 flex flex-wrap justify-end gap-2">
            <button type="submit" name="aksi" value="draft" class="px-4 py-2 bg-white hover:bg-slate-100 text-black text-xs font-black rounded-lg border-2 border-black cursor-pointer">
                💾 Simpan Draf
            </button>
            <button type="submit" name="aksi" value="submitted" class="px-4 py-2 bg-[#5294FF] hover:bg-blue-600 text-white text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                📤 Simpan &amp; Kirim ke Guru
            </button>
        </div>
    </form>
</div>
@endsection
