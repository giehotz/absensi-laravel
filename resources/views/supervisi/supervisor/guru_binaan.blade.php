@extends('layouts.supervisi')
@section('title', 'Guru Binaan')
@section('page_title', 'Pantauan Siklus Guru Binaan')

@section('content')
<div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] overflow-x-auto">
    <table class="w-full text-xs border-collapse border-2 border-black">
        <thead class="bg-slate-900 text-white">
            <tr>
                <th class="p-2 border border-black text-left">Guru & Mapel</th>
                <th class="p-2 border border-black text-center">Form A (Pra)</th>
                <th class="p-2 border border-black text-center">Form B (Obs)</th>
                <th class="p-2 border border-black text-center">Form C (RTL)</th>
                <th class="p-2 border border-black text-center">Form D (Ref)</th>
                <th class="p-2 border border-black text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($jadwals as $j)
                <tr class="odd:bg-white even:bg-slate-50">
                    <td class="p-2 border border-black">
                        <div class="font-black">{{ $j->teacher?->user?->name }}</div>
                        <div class="text-[11px] text-slate-600">{{ $j->schoolClass?->name ?? '-' }} · {{ $j->subject?->name ?? '-' }} ({{ $j->tanggal?->format('d/m/Y') }})</div>
                    </td>
                    <td class="p-2 border border-black text-center font-bold">{{ $j->praObservasi?->status_kesepakatan ?? 'belum' }}</td>
                    <td class="p-2 border border-black text-center font-bold">{{ $j->nilai_akhir !== null ? number_format((float) $j->nilai_akhir, 1).'%' : 'belum' }}</td>
                    <td class="p-2 border border-black text-center font-bold">{{ $j->tindakLanjut?->tujuan_tindak_lanjut ? ($j->tindakLanjut?->respon_supervisor_c ?: 'terisi') : 'belum' }}</td>
                    <td class="p-2 border border-black text-center font-bold">{{ $j->tindakLanjut?->inspirasi_baru ? ($j->tindakLanjut?->penilaian_supervisor_d ?: 'terisi') : 'belum' }}</td>
                    <td class="p-2 border border-black text-center">
                        <a href="{{ route('supervisi.supervisor.jadwal.show', $j) }}" class="px-2.5 py-1 bg-[#FFD43B] border border-black rounded font-black">Detail</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="p-4 text-center text-slate-500">Belum ada guru binaan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
