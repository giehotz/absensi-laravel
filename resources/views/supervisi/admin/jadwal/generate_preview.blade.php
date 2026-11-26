@extends('layouts.supervisi')
@section('title', 'Pratinjau Generate Jadwal')
@section('page_title', 'Pratinjau Jadwal Supervisi Otomatis')

@section('content')
<div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] flex flex-wrap items-center justify-between gap-3">
    <div>
        <div class="text-xs font-bold text-slate-600">Tahun Ajaran: {{ $academicYear->name }} · Hari Kerja Tersedia: {{ $workingDaysCount }} hari</div>
        <h2 class="font-heading font-black text-base">{{ count($generatedJadwals) }} Jadwal Siap Disimpan (Bebas Bentrok Supervisor)</h2>
    </div>
    @if(!empty($unscheduledTeachers))
        <span class="px-3 py-1 bg-amber-100 border-2 border-black rounded-lg text-xs font-bold">Belum Terplot: {{ count($unscheduledTeachers) }} guru</span>
    @endif
</div>

<form action="{{ route('supervisi.admin.jadwal.generate.store') }}" method="POST" class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-4">
    @csrf
    <input type="hidden" name="jadwals_json" value="{{ json_encode($generatedJadwals) }}">

    <div class="overflow-x-auto">
        <table class="w-full text-xs border-collapse border-2 border-black">
            <thead class="bg-slate-900 text-white">
                <tr>
                    <th class="p-2 border border-black">Kecualikan</th>
                    <th class="p-2 border border-black text-left">Guru</th>
                    <th class="p-2 border border-black text-left">Supervisor</th>
                    <th class="p-2 border border-black text-left">Kelas</th>
                    <th class="p-2 border border-black text-left">Mapel</th>
                    <th class="p-2 border border-black text-left">Tanggal & Jam</th>
                </tr>
            </thead>
            <tbody>
                @foreach($generatedJadwals as $idx => $item)
                    <tr class="odd:bg-white even:bg-slate-50">
                        <td class="p-2 border border-black text-center"><input type="checkbox" name="exclude_indices[]" value="{{ $idx }}"></td>
                        <td class="p-2 border border-black font-black">{{ $item['nama_guru'] }}</td>
                        <td class="p-2 border border-black">{{ $item['nama_supervisor'] }}</td>
                        <td class="p-2 border border-black">{{ $item['nama_kelas'] }}</td>
                        <td class="p-2 border border-black">{{ $item['nama_mapel'] }}</td>
                        <td class="p-2 border border-black">
                            <input type="date" name="overrides[{{ $idx }}][tanggal]" value="{{ $item['tanggal'] }}" class="px-2 py-1 border border-black rounded text-xs">
                            <span class="ml-1 text-[11px] font-mono">{{ $item['jam_mulai'] }}–{{ $item['jam_selesai'] }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="flex justify-end gap-2">
        <a href="{{ route('supervisi.admin.jadwal.generate') }}" class="px-4 py-2 bg-slate-100 text-xs font-bold rounded-lg border-2 border-black">Kembali</a>
        <button type="submit" class="px-5 py-2 bg-[#20C997] text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
            💾 Simpan Semua Jadwal
        </button>
    </div>
</form>
@endsection
