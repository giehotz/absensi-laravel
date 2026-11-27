@extends('layouts.supervisi')
@section('title', 'Dashboard Guru Supervisi')
@section('page_title', 'Portal Supervisi Guru')

@section('content')
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
    <div class="bg-white border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000]"><div class="text-[11px] font-black text-slate-500">Total Jadwal</div><div class="text-2xl font-black mt-1 tabular-nums">{{ $stats['total'] }}</div></div>
    <div class="bg-[#FFD43B] border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000]"><div class="text-[11px] font-black text-slate-800">Berlangsung</div><div class="text-2xl font-black mt-1 tabular-nums">{{ $stats['mendatang'] }}</div></div>
    <div class="bg-[#20C997] border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000]"><div class="text-[11px] font-black text-slate-900">Selesai</div><div class="text-2xl font-black mt-1 tabular-nums">{{ $stats['selesai'] }}</div></div>
    <div class="bg-sky-100 border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000]"><div class="text-[11px] font-black text-sky-950">Rata-rata Nilai</div><div class="text-2xl font-black mt-1 tabular-nums">{{ number_format((float) $stats['rata_rata'], 2) }}</div></div>
</div>

@if($portfolio)
    <div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-heading font-black text-sm">Checklist Kelengkapan 14 Form Supervisi ({{ $portfolio['doneCount'] }}/{{ $portfolio['totalForms'] }} Form)</h2>
                <p class="text-xs text-slate-600">{{ $portfolio['periodLabel'] }} · Nilai Akumulasi: <strong>{{ number_format((float) $portfolio['nilaiAkhir'], 2) }}% ({{ $portfolio['ketercapaian'] }})</strong></p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <form method="GET" class="flex flex-wrap items-center gap-1.5">
                    <select name="academic_year_id" aria-label="Pilih Tahun Ajaran" class="px-2.5 py-1.5 text-xs border-2 border-black rounded-lg bg-white font-bold">
                        @foreach($academicYears as $yr)
                            <option value="{{ $yr->id }}" {{ $referenceYear && (int) $referenceYear->id === (int) $yr->id ? 'selected' : '' }}>{{ $yr->name }} ({{ ucfirst($yr->semester) }})</option>
                        @endforeach
                    </select>
                    <select name="scope" aria-label="Periode" class="px-2.5 py-1.5 text-xs border-2 border-black rounded-lg bg-white font-bold">
                        <option value="semester" {{ $selectedScope === 'semester' ? 'selected' : '' }}>1 Semester</option>
                        <option value="tahun" {{ $selectedScope === 'tahun' ? 'selected' : '' }}>1 Tahun</option>
                    </select>
                    <button type="submit" class="px-3 py-1.5 bg-slate-900 text-white text-xs font-black rounded-lg border-2 border-black cursor-pointer">Tampilkan</button>
                </form>
                @if($referenceYear)
                    <a href="{{ route('supervisi.cetak.portofolio', ['teacher' => $teacher->id, 'academic_year_id' => $referenceYear->id, 'scope' => $selectedScope]) }}" target="_blank"
                        class="px-3 py-1.5 bg-violet-200 hover:bg-violet-300 text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                        🖨️ PDF Portofolio 14 Form
                    </a>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
            @foreach($portfolio['forms'] as $f)
                <div class="p-3 rounded-lg border-2 border-black bg-slate-50 flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <div class="text-[10px] font-mono font-black text-slate-600">{{ $f['jenis']->kode }}</div>
                        <div class="text-xs font-black text-slate-900 leading-tight">{{ $f['jenis']->nama }}</div>
                        <div class="text-[11px] text-slate-600 mt-0.5">
                            @if($f['hasil'])
                                Nilai: <strong>{{ number_format((float) $f['nilai_akhir'], 2) }}%</strong> ({{ $f['ketercapaian'] }})
                            @else
                                Belum dinilai supervisor
                            @endif
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded border text-[10px] font-black shrink-0 {{ $f['badge_class'] }}">
                        {{ $f['icon'] }} {{ $f['status_label'] }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
@endif

<div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-3">
    <h2 class="font-heading font-black text-sm">Jadwal Supervisi Saya</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-xs border-collapse border-2 border-black">
            <thead class="bg-slate-900 text-white">
                <tr>
                    <th class="p-2 border border-black text-left">Tanggal</th>
                    <th class="p-2 border border-black text-left">Supervisor</th>
                    <th class="p-2 border border-black text-left">Kelas / Mapel</th>
                    <th class="p-2 border border-black text-left">Status</th>
                    <th class="p-2 border border-black text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jadwals->take(6) as $j)
                    <tr class="odd:bg-white even:bg-slate-50">
                        <td class="p-2 border border-black font-bold">{{ $j->tanggal?->format('d/m/Y') }}</td>
                        <td class="p-2 border border-black font-black">{{ $j->supervisor_display_name }}</td>
                        <td class="p-2 border border-black">{{ $j->schoolClass?->name ?? '-' }} / {{ $j->subject?->name ?? '-' }}</td>
                        <td class="p-2 border border-black"><span class="px-2 py-0.5 rounded border text-[11px] font-black {{ $j->statusEnum()->badgeClass() }}">{{ $j->statusEnum()->label() }}</span></td>
                        <td class="p-2 border border-black text-center">
                            <a href="{{ route('supervisi.guru.siklus.show', $j) }}" class="px-2.5 py-1 bg-[#FFD43B] border border-black rounded font-black">
                                📋 Isi Step Form
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-4 text-center text-slate-500">Belum ada jadwal supervisi untuk Anda.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
