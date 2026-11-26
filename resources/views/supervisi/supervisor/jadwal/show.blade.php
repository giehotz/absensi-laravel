@extends('layouts.supervisi')
@section('title', 'Detail Jadwal Supervisor')
@section('page_title', 'Detail Siklus Supervisi Guru')

@section('content')
<div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="font-heading font-black text-lg">{{ $jadwal->teacher?->user?->name }}</h2>
            <p class="text-xs font-semibold text-slate-600">{{ $jadwal->schoolClass?->name ?? '-' }} · {{ $jadwal->subject?->name ?? '-' }} · {{ $jadwal->tanggal?->format('d/m/Y') }}</p>
        </div>
        <span class="px-3 py-1 rounded-lg border-2 border-black text-xs font-black {{ $jadwal->statusEnum()->badgeClass() }}">{{ $jadwal->statusEnum()->label() }}</span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
        <a href="{{ route('supervisi.supervisor.dokumen-ajar.index', $jadwal) }}" class="p-4 rounded-xl border-2 border-black bg-slate-50 hover:bg-amber-50">
            <div class="font-black">1. Perangkat Ajar</div>
            <div class="text-slate-600 mt-1">{{ $jadwal->dokumenAjars->count() }} dokumen diunggah</div>
        </a>
        <a href="{{ route('supervisi.supervisor.pra-observasi.show', $jadwal) }}" class="p-4 rounded-xl border-2 border-black bg-slate-50 hover:bg-amber-50">
            <div class="font-black">2. Pra-Observasi (Form A)</div>
            <div class="text-slate-600 mt-1">Status: {{ $jadwal->praObservasi?->status_kesepakatan ?? 'Belum diisi' }}</div>
        </a>
        <a href="{{ route('supervisi.supervisor.penilaian.edit', $jadwal) }}" class="p-4 rounded-xl border-2 border-black bg-slate-50 hover:bg-sky-50">
            <div class="font-black">3. Observasi Kelas (Form B)</div>
            <div class="text-slate-600 mt-1">Nilai: {{ $jadwal->nilai_akhir ?? '-' }}</div>
        </a>
        <a href="{{ route('supervisi.supervisor.tindak-lanjut.show', $jadwal) }}" class="p-4 rounded-xl border-2 border-black bg-slate-50 hover:bg-orange-50">
            <div class="font-black">4. Tindak Lanjut (Form C & D)</div>
            <div class="text-slate-600 mt-1">Status: {{ $jadwal->tindakLanjut?->status_tindak_lanjut ?? 'Belum dimulai' }}</div>
        </a>
    </div>

    @if($jadwal->status === 'selesai')
        <div class="pt-2">
            <a href="{{ route('supervisi.supervisor.hasil.show', $jadwal) }}" class="inline-block px-4 py-2 bg-[#20C997] text-black font-black text-xs rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                Lihat Hasil Lengkap & Tanda Tangan Digital →
            </a>
        </div>
    @endif
</div>
@endsection
