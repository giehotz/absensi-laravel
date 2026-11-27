@extends('layouts.supervisi')
@section('title', 'Review Tindak Lanjut (Form C & D)')
@section('page_title', 'Review Tindak Lanjut & Evaluasi Refleksi Guru')

@section('content')
@php
    $tl = $jadwal->tindakLanjut;
    $routePrefix = $portalPrefix ?? 'supervisor';
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

<div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-3">
    <h3 class="font-heading font-black text-sm">Coaching Alur TIRTA Pasca-Observasi</h3>
    <form action="{{ route("supervisi.{$routePrefix}.tindak-lanjut.coaching", $jadwal) }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-3">
        @csrf
        <textarea name="tujuan_coaching" rows="2" required placeholder="Tujuan Coaching Pasca" class="px-3 py-2 text-xs border-2 border-black rounded-lg">{{ old('tujuan_coaching', $coachingPasca?->tujuan_coaching) }}</textarea>
        <textarea name="identifikasi_kondisi" rows="2" placeholder="Identifikasi Kondisi" class="px-3 py-2 text-xs border-2 border-black rounded-lg">{{ old('identifikasi_kondisi', $coachingPasca?->identifikasi_kondisi) }}</textarea>
        <textarea name="rencana_aksi" rows="2" placeholder="Rencana Aksi" class="px-3 py-2 text-xs border-2 border-black rounded-lg">{{ old('rencana_aksi', $coachingPasca?->rencana_aksi) }}</textarea>
        <textarea name="tanggung_jawab" rows="2" placeholder="Tanggung Jawab" class="px-3 py-2 text-xs border-2 border-black rounded-lg">{{ old('tanggung_jawab', $coachingPasca?->tanggung_jawab) }}</textarea>
        <div class="md:col-span-2 flex justify-end">
            <button type="submit" class="px-4 py-2 bg-[#5294FF] text-white text-xs font-black rounded-lg border-2 border-black cursor-pointer">Simpan Coaching TIRTA Pasca</button>
        </div>
    </form>
</div>
@endsection
