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

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="pendekatan" class="block text-xs font-black mb-1">Pendekatan Supervisi (SK 6334/2021)</label>
            <select id="pendekatan" name="pendekatan" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">
                @foreach(\App\Models\Supervisi\SupervisiJadwal::PENDEKATAN_OPTIONS as $pKey => $pLabel)
                    <option value="{{ $pKey }}" {{ old('pendekatan', $jadwal->pendekatan ?? 'kolaboratif') === $pKey ? 'selected' : '' }}>{{ $pLabel }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="teknik_supervisi" class="block text-xs font-black mb-1">Teknik Supervisi</label>
            <select id="teknik_supervisi" name="teknik_supervisi" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">
                @foreach(\App\Models\Supervisi\SupervisiJadwal::TEKNIK_OPTIONS as $tKey => $tLabel)
                    <option value="{{ $tKey }}" {{ old('teknik_supervisi', $jadwal->teknik_supervisi ?? 'kunjungan_kelas') === $tKey ? 'selected' : '' }}>{{ $tLabel }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div id="guru-teaching-panel" class="p-3.5 rounded-xl border-2 border-black bg-[#FFF9DB] space-y-2 text-xs" hidden>
        <div class="font-black text-slate-900">💡 Jadwal KBM &amp; Mapel Ampuan Guru Terpilih</div>
        <div id="guru-teaching-list" class="flex flex-wrap gap-1.5"></div>
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

@push('scripts')
<script>
(() => {
    const teacherSelect = document.getElementById('teacher_id');
    const yearSelect = document.getElementById('academic_year_id');
    const classSelect = document.getElementById('school_class_id');
    const subjectSelect = document.getElementById('subject_id');
    const startInput = document.getElementById('jam_mulai');
    const endInput = document.getElementById('jam_selesai');
    const panel = document.getElementById('guru-teaching-panel');
    const list = document.getElementById('guru-teaching-list');

    async function loadTeacherTeaching() {
        const teacherId = teacherSelect?.value;
        if (!teacherId || !panel || !list) {
            return;
        }

        const yearId = yearSelect?.value || '';
        const res = await fetch(`/supervisi/admin/jadwal/guru-mengajar/${teacherId}?academic_year_id=${encodeURIComponent(yearId)}`, {
            headers: { Accept: 'application/json' },
        });
        if (!res.ok) {
            return;
        }
        const data = await res.json();
        list.replaceChildren();

        const items = data.has_schedules ? data.schedules : data.assignments;
        if (!items || items.length === 0) {
            panel.hidden = false;
            const span = document.createElement('span');
            span.className = 'text-[11px] font-bold text-rose-800';
            span.textContent = 'Guru ini belum memiliki data jadwal KBM (/admin/schedules) maupun mapel ampuan (teacher_assignments).';
            list.appendChild(span);
            return;
        }

        panel.hidden = false;
        items.forEach((item) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'px-2.5 py-1 rounded border border-black bg-white hover:bg-[#20C997] text-[11px] font-black cursor-pointer';
            btn.textContent = data.has_schedules
                ? `${item.hari} ${item.jam_mulai}-${item.jam_selesai} · ${item.kelas} / ${item.mapel}`
                : `${item.kelas} / ${item.mapel}${item.is_wali_kelas ? ' (Wali Kelas)' : ''}`;
            btn.addEventListener('click', () => {
                if (classSelect && item.school_class_id) classSelect.value = String(item.school_class_id);
                if (subjectSelect && item.subject_id) subjectSelect.value = String(item.subject_id);
                if (startInput && item.jam_mulai) startInput.value = item.jam_mulai;
                if (endInput && item.jam_selesai) endInput.value = item.jam_selesai;
            });
            list.appendChild(btn);
        });
    }

    teacherSelect?.addEventListener('change', loadTeacherTeaching);
    yearSelect?.addEventListener('change', loadTeacherTeaching);
    loadTeacherTeaching();
})();
</script>
@endpush
@endsection
