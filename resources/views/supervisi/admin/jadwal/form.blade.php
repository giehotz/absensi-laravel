@extends('layouts.supervisi')
@section('title', isset($jadwal) ? 'Edit Jadwal Supervisi' : 'Tambah Jadwal Supervisi')
@section('page_title', isset($jadwal) ? 'Edit Jadwal Supervisi' : 'Tambah Jadwal Supervisi')

@section('content')
<form action="{{ isset($jadwal) ? route('supervisi.admin.jadwal.update', $jadwal) : route('supervisi.admin.jadwal.store') }}" method="POST" class="bg-white border-2 border-black rounded-xl p-6 shadow-[4px_4px_0px_0px_#000] space-y-4">
    @csrf
    @if(isset($jadwal)) @method('PUT') @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="academic_year_id" class="block text-xs font-black mb-1">Tahun Ajaran</label>
            <select id="academic_year_id" name="academic_year_id" required class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">
                @foreach($academicYears as $yr)
                    <option value="{{ $yr->id }}" {{ old('academic_year_id', $jadwal->academic_year_id ?? $activeYear?->id) == $yr->id ? 'selected' : '' }}>{{ $yr->name }} ({{ ucfirst($yr->semester) }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="indikator_pmm_id" class="block text-xs font-black mb-1">Mode / Indikator PMM (Kosongkan untuk Reguler)</label>
            <select id="indikator_pmm_id" name="indikator_pmm_id" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">
                <option value="">-- Supervisi Reguler (4 Komponen) --</option>
                @foreach($pmmIndikators as $ind)
                    <option value="{{ $ind->id }}" {{ old('indikator_pmm_id', $jadwal->indikator_pmm_id ?? '') == $ind->id ? 'selected' : '' }}>PMM: {{ $ind->nama }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="teacher_id" class="block text-xs font-black mb-1">Guru yang Disupervisi</label>
            <select id="teacher_id" name="teacher_id" required class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">
                @foreach($teachers as $t)
                    <option value="{{ $t->id }}" {{ old('teacher_id', $jadwal->teacher_id ?? '') == $t->id ? 'selected' : '' }}>{{ $t->user?->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="supervisor_teacher_id" class="block text-xs font-black mb-1">Supervisor Penilai</label>
            <select id="supervisor_teacher_id" name="supervisor_teacher_id" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">
                <option value="">-- Pilih Supervisor --</option>
                @foreach($supervisors as $sup)
                    <option value="{{ $sup->teacher_id }}" {{ old('supervisor_teacher_id', $jadwal->supervisor_teacher_id ?? '') == $sup->teacher_id ? 'selected' : '' }}>{{ $sup->teacher?->user?->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="school_class_id" class="block text-xs font-black mb-1">Kelas</label>
            <select id="school_class_id" name="school_class_id" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">
                <option value="">-- Pilih Kelas --</option>
                @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ old('school_class_id', $jadwal->school_class_id ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="subject_id" class="block text-xs font-black mb-1">Mata Pelajaran</label>
            <select id="subject_id" name="subject_id" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">
                <option value="">-- Pilih Mapel --</option>
                @foreach($subjects as $s)
                    <option value="{{ $s->id }}" {{ old('subject_id', $jadwal->subject_id ?? '') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="tanggal" class="block text-xs font-black mb-1">Tanggal Supervisi</label>
            <input id="tanggal" type="date" name="tanggal" value="{{ old('tanggal', isset($jadwal) ? $jadwal->tanggal?->toDateString() : now()->toDateString()) }}" required class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg">
        </div>
        <div class="grid grid-cols-3 gap-2">
            <div>
                <label for="jam_ke" class="block text-xs font-black mb-1">Jam Ke</label>
                <input id="jam_ke" type="text" name="jam_ke" value="{{ old('jam_ke', $jadwal->jam_ke ?? '1') }}" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg">
            </div>
            <div>
                <label for="jam_mulai" class="block text-xs font-black mb-1">Mulai</label>
                <input id="jam_mulai" type="time" name="jam_mulai" value="{{ old('jam_mulai', isset($jadwal) ? substr((string) $jadwal->jam_mulai, 0, 5) : '07:30') }}" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg">
            </div>
            <div>
                <label for="jam_selesai" class="block text-xs font-black mb-1">Selesai</label>
                <input id="jam_selesai" type="time" name="jam_selesai" value="{{ old('jam_selesai', isset($jadwal) ? substr((string) $jadwal->jam_selesai, 0, 5) : '08:05') }}" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg">
            </div>
        </div>
    </div>

    <div>
        <label for="materi" class="block text-xs font-black mb-1">Materi / Topik Pembelajaran</label>
        <input id="materi" type="text" name="materi" value="{{ old('materi', $jadwal->materi ?? '') }}" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg">
    </div>

    <div class="flex justify-end gap-2">
        <a href="{{ route('supervisi.admin.jadwal.index') }}" class="px-4 py-2 bg-slate-100 text-xs font-bold rounded-lg border-2 border-black">Batal</a>
        <button type="submit" class="px-5 py-2 bg-[#FFD43B] text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">Simpan Jadwal</button>
    </div>
</form>
@endsection
