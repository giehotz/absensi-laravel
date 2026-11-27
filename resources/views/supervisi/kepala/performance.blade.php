@extends('layouts.supervisi')
@section('title', 'Performa Guru')
@section('page_title', 'Rekap Performa & Ketuntasan 14 Form Guru')

@section('content')
<div class="bg-white border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000] flex flex-wrap items-center justify-between gap-2">
    <form method="GET" class="flex flex-wrap items-center gap-2">
        <select name="academic_year_id" aria-label="Filter Tahun Ajaran" class="px-3 py-2 text-xs border-2 border-black rounded-lg bg-white font-bold">
            <option value="all" {{ $selectedYearId === 'all' ? 'selected' : '' }}>Semua Tahun Ajaran</option>
            @foreach($academicYears as $yr)
                <option value="{{ $yr->id }}" {{ (string) $selectedYearId === (string) $yr->id ? 'selected' : '' }}>{{ $yr->name }} ({{ ucfirst($yr->semester) }})</option>
            @endforeach
        </select>
        <select name="scope" aria-label="Cakupan Periode" class="px-3 py-2 text-xs border-2 border-black rounded-lg bg-white font-bold">
            <option value="semester" {{ $selectedScope === 'semester' ? 'selected' : '' }}>Progres 1 Semester</option>
            <option value="tahun" {{ $selectedScope === 'tahun' ? 'selected' : '' }}>Progres 1 Tahun Pelajaran</option>
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
                <th class="p-2 border border-black text-center">Progres 14 Form</th>
                <th class="p-2 border border-black text-center">Rata-rata</th>
                <th class="p-2 border border-black text-center">Predikat</th>
                <th class="p-2 border border-black text-center">Portofolio</th>
            </tr>
        </thead>
        <tbody>
            @foreach($performanceData as $idx => $row)
                @php $prog = $row['progress'] ?? null; @endphp
                <tr class="odd:bg-white even:bg-slate-50">
                    <td class="p-2 border border-black font-black">#{{ $idx + 1 }}</td>
                    <td class="p-2 border border-black font-black">{{ $row['teacher']->user?->name }}</td>
                    <td class="p-2 border border-black">{{ implode(', ', $row['subjects']) ?: '-' }}</td>
                    <td class="p-2 border border-black text-center font-bold tabular-nums">{{ $row['jumlah_supervisi'] }}</td>
                    <td class="p-2 border border-black text-center">
                        @if($prog)
                            <span class="px-2 py-0.5 rounded border text-[10px] font-black {{ $prog['badge_class'] }}">{{ $prog['label'] }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td class="p-2 border border-black text-center font-black tabular-nums">{{ number_format((float) $row['rata_rata'], 2) }}</td>
                    <td class="p-2 border border-black text-center font-black">{{ $row['kategori'] }}</td>
                    <td class="p-2 border border-black text-center">
                        @if($referenceYear)
                            <a href="{{ route('supervisi.cetak.portofolio', ['teacher' => $row['teacher']->id, 'academic_year_id' => $referenceYear->id, 'scope' => $selectedScope]) }}" target="_blank"
                                class="px-2.5 py-1 bg-violet-100 border border-black rounded font-black">
                                🖨️ PDF 14 Form
                            </a>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
