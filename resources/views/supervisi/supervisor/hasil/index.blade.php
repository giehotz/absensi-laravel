@extends('layouts.supervisi')
@section('title', 'Hasil Supervisi Supervisor')
@section('page_title', 'Hasil Supervisi Selesai')

@section('content')
<div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] overflow-x-auto">
    <table class="w-full text-xs border-collapse border-2 border-black">
        <thead class="bg-slate-900 text-white">
            <tr>
                <th class="p-2 border border-black text-left">Tanggal</th>
                <th class="p-2 border border-black text-left">Guru</th>
                <th class="p-2 border border-black text-left">Kelas / Mapel</th>
                <th class="p-2 border border-black text-center">Nilai Akhir</th>
                <th class="p-2 border border-black text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($jadwals as $j)
                <tr class="odd:bg-white even:bg-slate-50">
                    <td class="p-2 border border-black font-bold">{{ $j->tanggal?->format('d/m/Y') }}</td>
                    <td class="p-2 border border-black font-black">{{ $j->teacher?->user?->name }}</td>
                    <td class="p-2 border border-black">{{ $j->schoolClass?->name ?? '-' }} / {{ $j->subject?->name ?? '-' }}</td>
                    <td class="p-2 border border-black text-center font-black tabular-nums">{{ $j->nilai_akhir ?? '-' }} ({{ $j->ketercapaian ?? '-' }})</td>
                    <td class="p-2 border border-black text-center">
                        <a href="{{ route('supervisi.supervisor.hasil.show', $j) }}" class="px-3 py-1 bg-[#20C997] border border-black rounded font-black">Detail & TTD</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="p-4 text-center text-slate-500">Belum ada supervisi berstatus Selesai.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
