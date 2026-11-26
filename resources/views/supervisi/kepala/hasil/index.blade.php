@extends('layouts.supervisi')
@section('title', 'Hasil & Pengesahan Kepala')
@section('page_title', 'Daftar Hasil Supervisi & Pengesahan Kepala Madrasah')

@section('content')
<div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-4 overflow-x-auto">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <h2 class="font-heading font-black text-sm">Daftar Hasil Supervisi Selesai</h2>
        <a href="{{ route('supervisi.kepala.hasil.print-instrumen') }}" target="_blank" class="px-3 py-2 bg-violet-200 hover:bg-violet-300 text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000]">
            📑 Cetak Semua Per Instrumen (1 PDF)
        </a>
    </div>
    <table class="w-full text-xs border-collapse border-2 border-black">
        <thead class="bg-slate-900 text-white">
            <tr>
                <th class="p-2 border border-black text-left">Tanggal</th>
                <th class="p-2 border border-black text-left">Guru</th>
                <th class="p-2 border border-black text-left">Supervisor</th>
                <th class="p-2 border border-black text-center">Nilai Akhir</th>
                <th class="p-2 border border-black text-center">Status TTD Kepala</th>
                <th class="p-2 border border-black text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($jadwals as $j)
                @php $signedKepala = $j->signatures->firstWhere('signer_role', 'kepala') !== null; @endphp
                <tr class="odd:bg-white even:bg-slate-50">
                    <td class="p-2 border border-black font-bold">{{ $j->tanggal?->format('d/m/Y') }}</td>
                    <td class="p-2 border border-black font-black">{{ $j->teacher?->user?->name }}</td>
                    <td class="p-2 border border-black">{{ $j->supervisor_display_name }}</td>
                    <td class="p-2 border border-black text-center font-black tabular-nums">{{ $j->nilai_akhir ?? '-' }} ({{ $j->ketercapaian ?? '-' }})</td>
                    <td class="p-2 border border-black text-center">
                        <span class="px-2 py-0.5 rounded border border-black text-[10px] font-black {{ $signedKepala ? 'bg-emerald-200' : 'bg-amber-100' }}">{{ $signedKepala ? 'SUDAH SAH' : 'MENUNGGU' }}</span>
                    </td>
                    <td class="p-2 border border-black text-center">
                        <a href="{{ route('supervisi.kepala.hasil.show', $j) }}" class="px-3 py-1 bg-[#20C997] border border-black rounded font-black">Detail & TTD</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="p-4 text-center text-slate-500">Belum ada hasil supervisi yang selesai.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
