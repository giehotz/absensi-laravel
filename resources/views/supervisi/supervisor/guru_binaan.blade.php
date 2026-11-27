@extends('layouts.supervisi')
@section('title', 'Guru Binaan')
@section('page_title', 'Pantauan Siklus & 14 Form Guru Binaan')

@section('content')
<div class="bg-white border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000] flex flex-wrap items-center justify-between gap-2">
    <div class="text-xs font-bold text-slate-700">
        Periode Tracker: <strong>{{ $referenceYear ? ($scope === 'tahun' ? "Tahun {$referenceYear->name}" : "{$referenceYear->name} (" . ucfirst($referenceYear->semester) . ")") : '-' }}</strong>
    </div>
    <form method="GET" class="flex items-center gap-2">
        <select name="scope" aria-label="Periode Tracker" class="px-3 py-1.5 text-xs border-2 border-black rounded-lg bg-white font-bold">
            <option value="semester" {{ $scope === 'semester' ? 'selected' : '' }}>Progres 1 Semester</option>
            <option value="tahun" {{ $scope === 'tahun' ? 'selected' : '' }}>Progres 1 Tahun Pelajaran</option>
        </select>
        <button type="submit" class="px-3 py-1.5 bg-slate-900 text-white text-xs font-black rounded-lg border-2 border-black cursor-pointer">Terapkan</button>
    </form>
</div>

<div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] overflow-x-auto">
    <table class="w-full text-xs border-collapse border-2 border-black">
        <thead class="bg-slate-900 text-white">
            <tr>
                <th class="p-2 border border-black text-left">Guru &amp; Mapel</th>
                <th class="p-2 border border-black text-center">Progres 14 Form</th>
                <th class="p-2 border border-black text-center">Form A (Pra)</th>
                <th class="p-2 border border-black text-center">Form B (Obs)</th>
                <th class="p-2 border border-black text-center">Form C / D</th>
                <th class="p-2 border border-black text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($jadwals as $j)
                @php $prog = $portfolioProgressMap[$j->teacher_id] ?? null; @endphp
                <tr class="odd:bg-white even:bg-slate-50">
                    <td class="p-2 border border-black">
                        <div class="font-black">{{ $j->teacher?->user?->name }}</div>
                        <div class="text-[11px] text-slate-600">{{ $j->schoolClass?->name ?? '-' }} · {{ $j->subject?->name ?? '-' }} ({{ $j->tanggal?->format('d/m/Y') }})</div>
                    </td>
                    <td class="p-2 border border-black text-center">
                        @if($prog)
                            <span class="px-2 py-0.5 rounded border text-[10px] font-black {{ $prog['badge_class'] }}">{{ $prog['label'] }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td class="p-2 border border-black text-center font-bold">{{ $j->praObservasi?->status_kesepakatan ?? 'belum' }}</td>
                    <td class="p-2 border border-black text-center font-bold">{{ $j->nilai_akhir !== null ? number_format((float) $j->nilai_akhir, 1).'%' : 'belum' }}</td>
                    <td class="p-2 border border-black text-center font-bold">{{ $j->tindakLanjut?->inspirasi_baru ? 'lengkap' : ($j->tindakLanjut?->tujuan_tindak_lanjut ? 'form C' : 'belum') }}</td>
                    <td class="p-2 border border-black text-center">
                        <div class="inline-flex flex-wrap justify-center gap-1">
                            <a href="{{ route('supervisi.supervisor.penilaian.edit', $j) }}" class="px-2 py-1 bg-sky-100 border border-black rounded font-black">14 Form</a>
                            <a href="{{ route('supervisi.supervisor.jadwal.show', $j) }}" class="px-2 py-1 bg-[#FFD43B] border border-black rounded font-black">Detail</a>
                            @if($j->teacher_id && $referenceYear)
                                <a href="{{ route('supervisi.cetak.portofolio', ['teacher' => $j->teacher_id, 'academic_year_id' => $referenceYear->id, 'scope' => $scope]) }}" target="_blank" class="px-2 py-1 bg-violet-100 border border-black rounded font-bold">PDF 14 Form</a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="p-4 text-center text-slate-500">Belum ada guru binaan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
