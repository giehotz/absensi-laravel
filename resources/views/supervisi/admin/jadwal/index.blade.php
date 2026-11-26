@extends('layouts.supervisi')
@section('title', 'Jadwal Supervisi')
@section('page_title', 'Manajemen Jadwal Supervisi')

@section('content')
<div class="bg-white border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000] flex flex-wrap items-center justify-between gap-3">
    <form method="GET" class="flex flex-wrap items-center gap-2">
        <select name="academic_year_id" class="px-3 py-2 text-xs border-2 border-black rounded-lg bg-white font-bold">
            <option value="all" {{ $selectedYearId === 'all' ? 'selected' : '' }}>Semua Tahun Ajaran</option>
            @foreach($academicYears as $yr)
                <option value="{{ $yr->id }}" {{ (string) $selectedYearId === (string) $yr->id ? 'selected' : '' }}>{{ $yr->name }} ({{ ucfirst($yr->semester) }})</option>
            @endforeach
        </select>
        <button type="submit" class="px-3 py-2 bg-slate-900 text-white text-xs font-black rounded-lg border-2 border-black cursor-pointer">Filter</button>
    </form>

    <div class="flex flex-wrap gap-2">
        <a href="{{ route('supervisi.admin.jadwal.create') }}" class="px-3.5 py-2 bg-[#FFD43B] text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000]">+ Tambah Manual</a>
        <a href="{{ route('supervisi.admin.jadwal.generate') }}" class="px-3.5 py-2 bg-[#5294FF] text-white text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000]">⚡ Generate Otomatis</a>
        <a href="{{ route('supervisi.admin.jadwal.print', ['academic_year_id' => $selectedYearId]) }}" target="_blank" class="px-3.5 py-2 bg-[#20C997] text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000]">🖨️ Cetak Jadwal</a>
    </div>
</div>

<form action="{{ route('supervisi.admin.jadwal.bulk-destroy') }}" method="POST" class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-3">
    @csrf
    <div class="flex justify-between items-center">
        <h2 class="font-heading font-black text-sm">Daftar Jadwal ({{ $jadwals->count() }})</h2>
        <button type="submit" class="px-3 py-1.5 bg-rose-200 text-rose-950 text-xs font-black rounded-lg border-2 border-black cursor-pointer">Hapus Terpilih</button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-xs border-collapse border-2 border-black">
            <thead class="bg-slate-900 text-white">
                <tr>
                    <th class="p-2 border border-black w-8">✓</th>
                    <th class="p-2 border border-black text-left">Tanggal & Jam</th>
                    <th class="p-2 border border-black text-left">Guru</th>
                    <th class="p-2 border border-black text-left">Supervisor</th>
                    <th class="p-2 border border-black text-left">Kelas / Mapel</th>
                    <th class="p-2 border border-black text-left">Status</th>
                    <th class="p-2 border border-black text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jadwals as $j)
                    <tr class="odd:bg-white even:bg-slate-50">
                        <td class="p-2 border border-black text-center"><input type="checkbox" name="selected_ids[]" value="{{ $j->id }}"></td>
                        <td class="p-2 border border-black">
                            <div class="font-black">{{ $j->tanggal?->format('d/m/Y') }} ({{ $j->hari }})</div>
                            <div class="text-[11px] text-slate-600">Jam ke-{{ $j->jam_ke ?? '-' }} ({{ substr((string) $j->jam_mulai, 0, 5) }}–{{ substr((string) $j->jam_selesai, 0, 5) }})</div>
                        </td>
                        <td class="p-2 border border-black font-black">{{ $j->teacher?->user?->name }}</td>
                        <td class="p-2 border border-black">{{ $j->supervisor_display_name }}</td>
                        <td class="p-2 border border-black">{{ $j->schoolClass?->name ?? '-' }} / {{ $j->subject?->name ?? '-' }}</td>
                        <td class="p-2 border border-black"><span class="px-2 py-0.5 rounded border text-[11px] font-black {{ $j->statusEnum()->badgeClass() }}">{{ $j->statusEnum()->label() }}</span></td>
                        <td class="p-2 border border-black text-center">
                            <div class="inline-flex gap-1">
                                <a href="{{ route('supervisi.admin.penilaian.edit', $j) }}" class="px-2 py-1 bg-sky-100 border border-black rounded font-bold">Nilai</a>
                                <a href="{{ route('supervisi.admin.jadwal.edit', $j) }}" class="px-2 py-1 bg-amber-100 border border-black rounded font-bold">Edit</a>
                                <a href="{{ route('supervisi.admin.laporan.detail', $j) }}" class="px-2 py-1 bg-emerald-100 border border-black rounded font-bold">Detail</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-4 text-center text-slate-500">Belum ada jadwal supervisi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</form>
@endsection
