@extends('layouts.supervisi')
@section('title', 'Generate Jadwal Otomatis')
@section('page_title', 'Generator Jadwal Supervisi Otomatis')

@section('content')
<form action="{{ route('supervisi.admin.jadwal.generate.preview') }}" method="POST" class="bg-white border-2 border-black rounded-xl p-6 shadow-[4px_4px_0px_0px_#000] space-y-5">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label for="academic_year_id" class="block text-xs font-black mb-1">Tahun Ajaran</label>
            <select id="academic_year_id" name="academic_year_id" required class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">
                @foreach($academicYears as $yr)
                    <option value="{{ $yr->id }}" {{ $activeYear?->id === $yr->id ? 'selected' : '' }}>{{ $yr->name }} ({{ ucfirst($yr->semester) }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="tanggal_mulai" class="block text-xs font-black mb-1">Tanggal Mulai</label>
            <input id="tanggal_mulai" type="date" name="tanggal_mulai" value="{{ now()->toDateString() }}" required class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg">
        </div>
        <div>
            <label for="tanggal_selesai" class="block text-xs font-black mb-1">Tanggal Selesai</label>
            <input id="tanggal_selesai" type="date" name="tanggal_selesai" value="{{ now()->addWeeks(3)->toDateString() }}" required class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg">
        </div>
        <div>
            <label for="max_per_day" class="block text-xs font-black mb-1">Maks. Supervisi / Supervisor / Hari</label>
            <input id="max_per_day" type="number" name="max_per_day" value="2" min="1" max="6" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg">
        </div>
        <div class="md:col-span-2">
            <label for="indikator_pmm_id" class="block text-xs font-black mb-1">Mode Instrumen Default (Opsional)</label>
            <select id="indikator_pmm_id" name="indikator_pmm_id" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">
                <option value="">-- Supervisi Reguler / Guru Pilih Sendiri --</option>
                @foreach($pmmIndikators as $ind)
                    <option value="{{ $ind->id }}">PMM: {{ $ind->nama }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="pendekatan" class="block text-xs font-black mb-1">Pendekatan Default</label>
            <select id="pendekatan" name="pendekatan" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">
                @foreach(\App\Models\Supervisi\SupervisiJadwal::PENDEKATAN_OPTIONS as $pKey => $pLabel)
                    <option value="{{ $pKey }}" {{ $pKey === 'kolaboratif' ? 'selected' : '' }}>{{ $pLabel }}</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-2">
            <label for="teknik_supervisi" class="block text-xs font-black mb-1">Teknik Supervisi Default</label>
            <select id="teknik_supervisi" name="teknik_supervisi" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">
                @foreach(\App\Models\Supervisi\SupervisiJadwal::TEKNIK_OPTIONS as $tKey => $tLabel)
                    <option value="{{ $tKey }}" {{ $tKey === 'kunjungan_kelas' ? 'selected' : '' }}>{{ $tLabel }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div>
        <div class="text-xs font-black mb-2">Hari Kerja Aktif</div>
        <div class="flex flex-wrap gap-3 text-xs font-bold">
            @foreach([1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'] as $dNum => $dName)
                <label class="inline-flex items-center gap-1.5 px-3 py-1.5 border-2 border-black rounded-lg bg-slate-50 cursor-pointer">
                    <input type="checkbox" name="hari_aktif[]" value="{{ $dNum }}" {{ $dNum <= 5 ? 'checked' : '' }}>
                    <span>{{ $dName }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="p-4 border-2 border-black rounded-xl bg-slate-50 space-y-2 max-h-64 overflow-y-auto">
            <div class="text-xs font-black">Pilih Supervisor Penilai</div>
            @foreach($supervisors as $sup)
                <label class="flex items-center gap-2 text-xs font-semibold">
                    <input type="checkbox" name="supervisor_teacher_ids[]" value="{{ $sup->teacher_id }}" checked>
                    <span>{{ $sup->teacher?->user?->name }}</span>
                </label>
            @endforeach
        </div>
        <div class="p-4 border-2 border-black rounded-xl bg-slate-50 space-y-2 max-h-64 overflow-y-auto">
            <div class="text-xs font-black">Pilih Guru yang Dijadwalkan</div>
            @foreach($teachers as $t)
                <label class="flex items-center gap-2 text-xs font-semibold">
                    <input type="checkbox" name="teacher_ids[]" value="{{ $t->id }}" {{ !in_array($t->id, $scheduledTeacherIds, true) ? 'checked' : '' }}>
                    <span>{{ $t->user?->name }} @if(in_array($t->id, $scheduledTeacherIds, true)) <em class="text-emerald-700">(Sudah Terjadwal)</em> @endif</span>
                </label>
            @endforeach
        </div>
    </div>

    <div class="flex justify-end">
        <button type="submit" class="px-5 py-2.5 bg-[#5294FF] text-white text-xs font-black rounded-lg border-2 border-black shadow-[3px_3px_0px_0px_#000] cursor-pointer">
            ⚡ Buat Pratinjau Jadwal
        </button>
    </div>
</form>
@endsection
