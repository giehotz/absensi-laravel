@extends('layouts.supervisi')
@section('title', 'Review Pra-Observasi (Form A)')
@section('page_title', 'Persetujuan Formulir A & Coaching TIRTA Pra-Observasi')

@section('content')
<div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-3">
    <h2 class="font-heading font-black text-base">{{ $jadwal->teacher?->user?->name }} — {{ $jadwal->subject?->name ?? '-' }}</h2>
    @if(!$jadwal->praObservasi)
        <p class="text-xs font-bold text-amber-800 bg-amber-50 p-3 border-2 border-black rounded-lg">Guru belum mengisi atau mengajukan Formulir A Pra-Observasi.</p>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
            <div class="p-3 bg-slate-50 border-2 border-black rounded-lg">
                <div class="font-black text-slate-600 uppercase text-[10px]">Indikator PMM & Fokus Perilaku Dipilih</div>
                <div class="font-black mt-1">{{ $jadwal->indikatorPmm?->nama ?? '-' }}</div>
                <ul class="list-disc pl-4 mt-1 space-y-0.5">
                    @foreach($fokusDipilihList as $f)
                        <li><strong>{{ $f->nama_aspek }}</strong>: {{ $f->fokus_perilaku }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="p-3 bg-slate-50 border-2 border-black rounded-lg space-y-2">
                <div><strong>Tujuan Pembelajaran Sesi:</strong><br>{{ $jadwal->praObservasi->tujuan_pembelajaran_sesi ?: '-' }}</div>
                <div><strong>Upaya Mempelajari Target Perilaku:</strong><br>{{ $jadwal->praObservasi->upaya_mempelajari ?: '-' }}</div>
                <div><strong>Area Pengembangan:</strong><br>{{ $jadwal->praObservasi->area_pengembangan ?: '-' }}</div>
                <div><strong>Strategi Persiapan:</strong><br>{{ $jadwal->praObservasi->strategi_persiapan ?: '-' }}</div>
            </div>
        </div>

        <form action="{{ route('supervisi.' . ($portalPrefix ?? 'supervisor') . '.pra-observasi.agree', $jadwal) }}" method="POST" class="pt-4 border-t-2 border-black space-y-4">
            @csrf
            <h3 class="font-heading font-black text-sm">Catatan Coaching Alur TIRTA Pra-Observasi</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-black mb-1">T — Tujuan Coaching</label>
                    <textarea name="tujuan_coaching" rows="2" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg">{{ old('tujuan_coaching', $coachingPra?->tujuan_coaching) }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-black mb-1">I — Identifikasi Kondisi</label>
                    <textarea name="identifikasi_kondisi" rows="2" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg">{{ old('identifikasi_kondisi', $coachingPra?->identifikasi_kondisi) }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-black mb-1">R — Rencana Aksi</label>
                    <textarea name="rencana_aksi" rows="2" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg">{{ old('rencana_aksi', $coachingPra?->rencana_aksi) }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-black mb-1">TA — Tanggung Jawab</label>
                    <textarea name="tanggung_jawab" rows="2" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg">{{ old('tanggung_jawab', $coachingPra?->tanggung_jawab) }}</textarea>
                </div>
            </div>
            <div>
                <label class="block text-xs font-black mb-1">Catatan Kesepakatan Supervisor</label>
                <textarea name="catatan_supervisor_pra" rows="2" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg">{{ old('catatan_supervisor_pra', $jadwal->praObservasi->catatan_supervisor_pra) }}</textarea>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2 bg-[#20C997] text-black font-black text-xs rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                    ✅ Sepakati Formulir A & Buka Observasi
                </button>
            </div>
        </form>
    @endif
</div>
@endsection
