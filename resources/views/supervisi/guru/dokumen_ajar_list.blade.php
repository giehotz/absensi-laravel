@extends('layouts.supervisi')
@section('title', 'Perangkat Ajar')
@section('page_title', 'Daftar Perangkat Ajar per Jadwal')

@section('content')
<div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] overflow-x-auto">
    <table class="w-full text-xs border-collapse border-2 border-black">
        <thead class="bg-slate-900 text-white">
            <tr>
                <th class="p-2 border border-black text-left">Tanggal</th>
                <th class="p-2 border border-black text-left">Kelas / Mapel</th>
                <th class="p-2 border border-black text-center">Jumlah Dokumen</th>
                <th class="p-2 border border-black text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($jadwals as $j)
                <tr class="odd:bg-white even:bg-slate-50">
                    <td class="p-2 border border-black font-bold">{{ $j->tanggal?->format('d/m/Y') }}</td>
                    <td class="p-2 border border-black font-black">{{ $j->schoolClass?->name ?? '-' }} / {{ $j->subject?->name ?? '-' }}</td>
                    <td class="p-2 border border-black text-center font-black tabular-nums">{{ $j->dokumen_ajars_count }}</td>
                    <td class="p-2 border border-black text-center">
                        <a href="{{ route('supervisi.guru.dokumen-ajar.manage', $j) }}" class="px-3 py-1 bg-[#FFD43B] border border-black rounded font-black">Kelola Perangkat Ajar</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="p-4 text-center text-slate-500">Belum ada jadwal supervisi.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
