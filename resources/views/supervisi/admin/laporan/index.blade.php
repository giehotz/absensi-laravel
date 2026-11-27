@extends('layouts.supervisi')
@section('title', 'Laporan Supervisi')
@section('page_title', 'Rekap Hasil & Ekspor Laporan')

@section('content')
<div class="bg-white border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000] flex flex-wrap items-center justify-between gap-3">
    <form method="GET" class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
        <select name="academic_year_id" class="w-full sm:w-auto px-3 py-2 text-xs border-2 border-black rounded-lg bg-white font-bold">
            <option value="all" {{ $selectedYearId === 'all' ? 'selected' : '' }}>Semua Tahun Ajaran</option>
            @foreach($academicYears as $yr)
                <option value="{{ $yr->id }}" {{ (string) $selectedYearId === (string) $yr->id ? 'selected' : '' }}>{{ $yr->name }} ({{ ucfirst($yr->semester) }})</option>
            @endforeach
        </select>
        <select name="status" class="w-full sm:w-auto px-3 py-2 text-xs border-2 border-black rounded-lg bg-white font-bold">
            <option value="all" {{ $selectedStatus === 'all' ? 'selected' : '' }}>Semua Status</option>
            @foreach(\App\Enums\SupervisiStatus::cases() as $st)
                <option value="{{ $st->value }}" {{ $selectedStatus === $st->value ? 'selected' : '' }}>{{ $st->label() }}</option>
            @endforeach
        </select>
        <select name="scope" aria-label="Cakupan Ketuntasan 14 Form" class="w-full sm:w-auto px-3 py-2 text-xs border-2 border-black rounded-lg bg-white font-bold">
            <option value="semester" {{ ($selectedScope ?? 'semester') === 'semester' ? 'selected' : '' }}>Progres 1 Semester</option>
            <option value="tahun" {{ ($selectedScope ?? 'semester') === 'tahun' ? 'selected' : '' }}>Progres 1 Tahun</option>
        </select>
        <button type="submit" class="px-3 py-2 bg-slate-900 text-white text-xs font-black rounded-lg border-2 border-black cursor-pointer">Filter</button>
    </form>

    <div class="flex flex-wrap gap-2">
        <a href="{{ route('supervisi.admin.laporan.print-rekap', ['academic_year_id' => $selectedYearId]) }}" target="_blank" class="px-3 py-2 bg-[#20C997] text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000]">🖨️ Cetak Rekap</a>
        <a href="{{ route('supervisi.admin.laporan.print-instrumen', ['academic_year_id' => $selectedYearId, 'status' => $selectedStatus]) }}" target="_blank" class="px-3 py-2 bg-violet-200 hover:bg-violet-300 text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000]">📑 Cetak Per Instrumen (1 PDF)</a>
        <a href="{{ route('supervisi.admin.laporan.export-excel', ['academic_year_id' => $selectedYearId, 'status' => $selectedStatus]) }}" class="px-3 py-2 bg-[#FFD43B] text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000]">📊 Export Excel</a>
        <a href="{{ route('supervisi.admin.laporan.batch-all', ['academic_year_id' => $selectedYearId]) }}" class="px-3 py-2 bg-[#5294FF] text-white text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000]">📚 Batch Multi-Sheet</a>
    </div>
</div>

<form action="{{ route('supervisi.admin.laporan.batch-excel') }}" method="POST" class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-3">
    @csrf
    <div class="flex justify-between items-center">
        <h2 class="font-heading font-black text-sm">Data Supervisi ({{ $jadwals->count() }})</h2>
        <button type="submit" class="px-3 py-1.5 bg-amber-100 text-xs font-black rounded-lg border-2 border-black cursor-pointer">📥 Export Terpilih</button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-xs border-collapse border-2 border-black">
            <thead class="bg-slate-900 text-white">
                <tr>
                    <th class="p-2 border border-black w-8">✓</th>
                    <th class="p-2 border border-black text-left">Guru</th>
                    <th class="p-2 border border-black text-left">Supervisor</th>
                    <th class="p-2 border border-black text-left">Tanggal</th>
                    <th class="p-2 border border-black text-center">Nilai Akhir</th>
                    <th class="p-2 border border-black text-center">Progres 14 Form</th>
                    <th class="p-2 border border-black text-center">TTD Kepala</th>
                    <th class="p-2 border border-black text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jadwals as $j)
                    @php
                        $sahKepala = $j->signatures->firstWhere('signer_role', 'kepala') !== null;
                        $prog = $portfolioProgressMap[$j->teacher_id] ?? null;
                    @endphp
                    <tr class="odd:bg-white even:bg-slate-50">
                        <td class="p-2 border border-black text-center"><input type="checkbox" name="selected_ids[]" value="{{ $j->id }}"></td>
                        <td class="p-2 border border-black font-black">{{ $j->teacher?->user?->name }}</td>
                        <td class="p-2 border border-black">{{ $j->supervisor_display_name }}</td>
                        <td class="p-2 border border-black">{{ $j->tanggal?->format('d/m/Y') }}</td>
                        <td class="p-2 border border-black text-center font-black tabular-nums">{{ $j->nilai_akhir !== null ? number_format((float) $j->nilai_akhir, 2).' ('.$j->ketercapaian.')' : '-' }}</td>
                        <td class="p-2 border border-black text-center">
                            @if($prog)
                                <span class="px-2 py-0.5 rounded border text-[10px] font-black {{ $prog['badge_class'] }}">{{ $prog['label'] }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="p-2 border border-black text-center">
                            <span class="px-2 py-0.5 rounded border border-black text-[10px] font-black {{ $sahKepala ? 'bg-emerald-200' : 'bg-slate-100' }}">{{ $sahKepala ? 'SAH' : 'BELUM' }}</span>
                        </td>
                        <td class="p-2 border border-black text-center">
                            <div class="inline-flex flex-wrap justify-center gap-1">
                                <a href="{{ route('supervisi.admin.laporan.detail', $j) }}" class="px-2 py-1 bg-emerald-100 border border-black rounded font-bold">Detail</a>
                                <a href="{{ route('supervisi.admin.penilaian.edit', $j) }}" class="px-2 py-1 bg-sky-100 border border-black rounded font-bold">Nilai 14 Form</a>
                                @if($j->teacher_id && $referenceYear)
                                    <a href="{{ route('supervisi.cetak.portofolio', ['teacher' => $j->teacher_id, 'academic_year_id' => $referenceYear->id, 'scope' => $selectedScope ?? 'semester']) }}" target="_blank" class="px-2 py-1 bg-violet-100 border border-black rounded font-bold">PDF 14 Form</a>
                                @endif
                                <a href="{{ route('supervisi.admin.laporan.export-detail-excel', $j) }}" class="px-2 py-1 bg-amber-100 border border-black rounded font-bold">XLSX</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="p-4 text-center text-slate-500">Belum ada data laporan supervisi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</form>
@endsection
