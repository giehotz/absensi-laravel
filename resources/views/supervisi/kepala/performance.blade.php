@extends('layouts.supervisi')
@section('title', 'Performa Guru')
@section('page_title', 'Rekap Performa Kinerja Guru')

@section('content')
<div class="bg-white border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000] flex items-center justify-between">
    <form method="GET" class="flex items-center gap-2">
        <select name="academic_year_id" class="px-3 py-2 text-xs border-2 border-black rounded-lg bg-white font-bold">
            <option value="all" {{ $selectedYearId === 'all' ? 'selected' : '' }}>Semua Tahun Ajaran</option>
            @foreach($academicYears as $yr)
                <option value="{{ $yr->id }}" {{ (string) $selectedYearId === (string) $yr->id ? 'selected' : '' }}>{{ $yr->name }} ({{ ucfirst($yr->semester) }})</option>
            @endforeach
        </select>
        <button type="submit" class="px-3 py-2 bg-slate-900 text-white text-xs font-black rounded-lg border-2 border-black cursor-pointer">Filter</button>
    </form>
</div>

<div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] overflow-x-auto">
    <table class="w-full text-xs border-collapse border-2 border-black">
        <thead class="bg-slate-900 text-white">
            <tr>
                <th class="p-2 border border-black text-left">Peringkat</th>
                <th class="p-2 border border-black text-left">Nama Guru</th>
                <th class="p-2 border border-black text-left">Mapel</th>
                <th class="p-2 border border-black text-center">Jml Supervisi</th>
                <th class="p-2 border border-black text-center">Rata-rata</th>
                <th class="p-2 border border-black text-center">Predikat</th>
            </tr>
        </thead>
        <tbody>
            @foreach($performanceData as $idx => $row)
                <tr class="odd:bg-white even:bg-slate-50">
                    <td class="p-2 border border-black font-black">#{{ $idx + 1 }}</td>
                    <td class="p-2 border border-black font-black">{{ $row['teacher']->user?->name }}</td>
                    <td class="p-2 border border-black">{{ implode(', ', $row['subjects']) ?: '-' }}</td>
                    <td class="p-2 border border-black text-center font-bold tabular-nums">{{ $row['jumlah_supervisi'] }}</td>
                    <td class="p-2 border border-black text-center font-black tabular-nums">{{ number_format((float) $row['rata_rata'], 2) }}</td>
                    <td class="p-2 border border-black text-center font-black">{{ $row['kategori'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
