@extends('layouts.admin')

@section('title', 'Manajemen Data Kelas')
@section('page-title', 'Manajemen Data Kelas')

@section('content')
<div class="space-y-6">
    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white neo-box p-5">
        <div>
            <h2 class="font-heading font-black text-xl text-black flex items-center gap-2">
                <span>🏫</span> Daftar Rombongan Belajar (Kelas)
            </h2>
            <p class="text-xs font-semibold text-slate-600 mt-1">
                Atur kelas, jenjang pendidikan, serta penugasan guru wali kelas.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <button onclick="openModal('syncClassesModal')" class="neo-btn bg-[#A5D8FF] hover:bg-[#74c0fc] text-black px-4 py-2.5 text-xs uppercase flex items-center gap-2 cursor-pointer font-heading" title="Tarik dan sinkronkan seluruh rombel resmi dari SIS-EMIS">
                <span>🔄</span> Tarik Rombel SIS-EMIS
            </button>
            <a href="{{ route('admin.classes.transfer') }}" class="neo-btn bg-[#FFF9DB] hover:bg-[#ffec99] text-black px-4 py-2.5 text-xs uppercase flex items-center gap-2 cursor-pointer font-heading" title="Pindahkan siswa yang salah kelas antar rombel">
                <span>⇄</span> Pindah Kelas
            </a>
            <button onclick="openModal('createClassModal')" class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black px-4 py-2.5 text-xs uppercase flex items-center gap-2 cursor-pointer font-heading">
                <span>+</span> Tambah Kelas Baru
            </button>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white neo-box overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-[#FFF9DB] border-b-2 border-black text-xs font-black uppercase tracking-wider">
                    <tr>
                        <th class="p-3.5 border-r border-black">No</th>
                        <th class="p-3.5 border-r border-black">Nama Kelas</th>
                        <th class="p-3.5 border-r border-black">Jenjang</th>
                        <th class="p-3.5 border-r border-black">Tahun Ajaran</th>
                        <th class="p-3.5 border-r border-black">Wali Kelas</th>
                        <th class="p-3.5 border-r border-black text-center">Jumlah Siswa</th>
                        <th class="p-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black">
                    @forelse($classes as $index => $class)
                    <tr class="hover:bg-slate-50 font-medium">
                        <td class="p-3.5 font-bold text-center border-r border-black">
                            {{ $classes->firstItem() + $index }}
                        </td>
                        <td class="p-3.5 font-bold text-black border-r border-black">
                            {{ $class->name }}
                        </td>
                        <td class="p-3.5 border-r border-black">
                            <span class="neo-badge bg-slate-100 text-black text-[10px]">
                                {{ $class->level }}
                            </span>
                        </td>
                        <td class="p-3.5 border-r border-black text-xs font-mono">
                            {{ $class->academicYear->name ?? '-' }} ({{ ucfirst($class->academicYear->semester ?? '') }})
                        </td>
                        <td class="p-3.5 border-r border-black text-xs">
                            {{ $class->homeroomTeacher->user->name ?? 'Belum Ditentukan' }}
                        </td>
                        <td class="p-3.5 border-r border-black text-center font-bold">
                            <span class="bg-[#D3F9D8] px-2.5 py-0.5 border border-black text-xs font-mono">
                                {{ $class->students->count() }}
                            </span>
                        </td>
                        <td class="p-3.5 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <!-- Tombol Kelola Siswa dengan Tooltip Neobrutalism -->
                                <div class="relative group inline-block">
                                    <a href="{{ route('admin.classes.students', $class) }}" 
                                       class="neo-btn bg-[#5294FF] hover:bg-[#3b82f6] text-white p-1.5 text-xs cursor-pointer flex items-center justify-center"
                                       aria-label="Lihat & Kelola Siswa Kelas Ini">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                                        </svg>
                                    </a>
                                    <!-- Tooltip Neobrutalism -->
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:flex flex-col items-center pointer-events-none z-50 transition-all duration-150">
                                        <div class="bg-[#FFF9DB] text-black text-[11px] font-bold px-2.5 py-1 whitespace-nowrap border-2 border-black shadow-[2px_2px_0px_0px_#000] rounded-sm">
                                            Lihat & Kelola Siswa Kelas Ini
                                        </div>
                                        <div class="w-2 h-2 bg-[#FFF9DB] border-r-2 border-b-2 border-black rotate-45 -mt-1"></div>
                                    </div>
                                </div>

                                <!-- Tombol Edit dengan Tooltip Neobrutalism -->
                                <div class="relative group inline-block">
                                    <button type="button"
                                            onclick="editClass(this)"
                                            data-id="{{ $class->id }}"
                                            data-name="{{ $class->name }}"
                                            data-level="{{ $class->level }}"
                                            data-academic-year-id="{{ $class->academic_year_id }}"
                                            data-homeroom-teacher-id="{{ $class->homeroom_teacher_id ?? '' }}"
                                            class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black p-1.5 text-xs cursor-pointer flex items-center justify-center"
                                            aria-label="Edit Data Kelas">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </button>
                                    <!-- Tooltip Neobrutalism -->
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:flex flex-col items-center pointer-events-none z-50 transition-all duration-150">
                                        <div class="bg-[#FFF9DB] text-black text-[11px] font-bold px-2.5 py-1 whitespace-nowrap border-2 border-black shadow-[2px_2px_0px_0px_#000] rounded-sm">
                                            Edit Data Kelas
                                        </div>
                                        <div class="w-2 h-2 bg-[#FFF9DB] border-r-2 border-b-2 border-black rotate-45 -mt-1"></div>
                                    </div>
                                </div>

                                <!-- Tombol Hapus dengan Tooltip Neobrutalism -->
                                <div class="relative group inline-block">
                                    <form action="{{ route('admin.classes.destroy', $class) }}" method="POST" onsubmit="return confirm('Menghapus kelas akan berdampak pada relasi siswa di dalamnya. Lanjutkan?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="neo-btn bg-[#FF6B6B] hover:bg-[#fa5252] text-white p-1.5 text-xs cursor-pointer flex items-center justify-center"
                                                aria-label="Hapus Kelas">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                    <!-- Tooltip Neobrutalism -->
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:flex flex-col items-center pointer-events-none z-50 transition-all duration-150">
                                        <div class="bg-[#FFF9DB] text-black text-[11px] font-bold px-2.5 py-1 whitespace-nowrap border-2 border-black shadow-[2px_2px_0px_0px_#000] rounded-sm">
                                            Hapus Kelas
                                        </div>
                                        <div class="w-2 h-2 bg-[#FFF9DB] border-r-2 border-b-2 border-black rotate-45 -mt-1"></div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-500 font-semibold">
                            Belum ada data kelas yang terdaftar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($classes->hasPages())
        <div class="p-4 border-t-2 border-black bg-slate-50">
            {{ $classes->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Tambah Kelas -->
<div id="createClassModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-md w-full p-6 space-y-5 relative">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <h3 class="font-heading font-black text-lg text-black flex items-center gap-2">
                <span>➕</span> Tambah Kelas Baru
            </h3>
            <button onclick="closeModal('createClassModal')" class="text-black font-black text-xl hover:opacity-75">✕</button>
        </div>

        <form action="{{ route('admin.classes.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Nama Kelas *</label>
                <input type="text" name="name" required placeholder="Contoh: Kelas 1 a, Kelas 6 c" class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
            </div>

            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Tingkat / Jenjang *</label>
                <select name="level" required class="w-full px-3 py-2 neo-input text-sm bg-slate-50 font-medium">
                    @for($lvl = 1; $lvl <= 6; $lvl++)
                        <option value="{{ $lvl }}">Tingkat {{ $lvl }} (Kelas {{ $lvl }})</option>
                    @endfor
                </select>
            </div>

            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Tahun Ajaran</label>
                <select name="academic_year_id" class="w-full px-3 py-2 neo-input text-sm bg-slate-50 font-medium">
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" {{ $year->is_active ? 'selected' : '' }}>
                            {{ $year->name }} ({{ ucfirst($year->semester) }}) {{ $year->is_active ? '★ Aktif' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Wali Kelas (Opsional)</label>
                <select name="homeroom_teacher_id" class="w-full px-3 py-2 neo-input text-sm bg-slate-50 font-medium">
                    <option value="">-- Pilih Wali Kelas --</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}">{{ $teacher->user->name ?? 'Guru' }} (NIP: {{ $teacher->nip }})</option>
                    @endforeach
                </select>
            </div>

            <div class="pt-3 border-t-2 border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('createClassModal')" class="neo-btn bg-white text-black px-4 py-2 text-xs">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#20C997] text-black px-5 py-2 text-xs font-heading">
                    Simpan Kelas
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Kelas -->
<div id="editClassModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-md w-full p-6 space-y-5 relative">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <h3 class="font-heading font-black text-lg text-black flex items-center gap-2">
                <span>✏️</span> Edit Data Kelas
            </h3>
            <button onclick="closeModal('editClassModal')" class="text-black font-black text-xl hover:opacity-75">✕</button>
        </div>

        <form id="editClassForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Nama Kelas *</label>
                <input type="text" id="edit_name" name="name" required class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
            </div>

            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Tingkat / Jenjang *</label>
                <select id="edit_level" name="level" required class="w-full px-3 py-2 neo-input text-sm bg-slate-50 font-medium">
                    @for($lvl = 1; $lvl <= 6; $lvl++)
                        <option value="{{ $lvl }}">Tingkat {{ $lvl }} (Kelas {{ $lvl }})</option>
                    @endfor
                </select>
            </div>

            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Tahun Ajaran *</label>
                <select id="edit_academic_year_id" name="academic_year_id" required class="w-full px-3 py-2 neo-input text-sm bg-slate-50 font-medium">
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}">
                            {{ $year->name }} ({{ ucfirst($year->semester) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Wali Kelas</label>
                <select id="edit_homeroom_teacher_id" name="homeroom_teacher_id" class="w-full px-3 py-2 neo-input text-sm bg-slate-50 font-medium">
                    <option value="">-- Pilih Wali Kelas --</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}">{{ $teacher->user->name ?? 'Guru' }} (NIP: {{ $teacher->nip }})</option>
                    @endforeach
                </select>
            </div>

            <div class="pt-3 border-t-2 border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('editClassModal')" class="neo-btn bg-white text-black px-4 py-2 text-xs">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#5294FF] text-white px-5 py-2 text-xs font-heading">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tarik Rombel SIS-EMIS -->
<div id="syncClassesModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-lg w-full p-6 space-y-5 relative">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <h3 class="font-heading font-black text-lg text-black flex items-center gap-2">
                <span>🔄</span> Tarik Rombel dari SIS-EMIS
            </h3>
            <button onclick="closeModal('syncClassesModal')" class="text-black font-black text-xl hover:opacity-75 cursor-pointer">✕</button>
        </div>

        <div class="bg-[#E7F5FF] border-2 border-black p-4 text-xs space-y-2">
            <p class="font-bold text-black flex items-center gap-1.5">
                <span>ℹ️</span> Sinkronisasi Rombongan Belajar Resmi
            </p>
            <p class="text-slate-700 leading-relaxed">
                Fitur ini akan menarik seluruh <b>17 Rombongan Belajar resmi</b> dari database <b>SIS-EMIS Kemenag</b> (Tingkat 1 s/d 6) dan menyimpannya ke tabel kelas aplikasi Absensi.
            </p>
            <ul class="list-disc list-inside space-y-1 text-slate-700 pl-1">
                <li>Rombel baru akan otomatis dibuat sesuai nama dan jenjang resminya (misal: <i>Kelas 1 a</i>, <i>Kelas 6 c</i>).</li>
                <li>Rombel yang sudah ada akan diperbarui secara aman tanpa menghapus siswa yang terhubung.</li>
            </ul>
        </div>

        <form action="{{ route('admin.classes.sync-sis') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1.5">Pilih Tahun Ajaran Tujuan *</label>
                <select name="academic_year_id" required class="w-full px-3 py-2 neo-input text-sm bg-slate-50 font-medium">
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" {{ $year->is_active ? 'selected' : '' }}>
                            {{ $year->name }} ({{ ucfirst($year->semester) }}) {{ $year->is_active ? '★ Tahun Ajaran Aktif' : '' }}
                        </option>
                    @endforeach
                </select>
                <span class="text-[11px] text-slate-500 font-semibold block mt-1">
                    Seluruh rombel yang ditarik akan dikaitkan dengan tahun ajaran yang dipilih di atas.
                </span>
            </div>

            <div class="pt-3 border-t-2 border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('syncClassesModal')" class="neo-btn bg-white text-black px-4 py-2 text-xs cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#A5D8FF] hover:bg-[#74c0fc] text-black px-5 py-2 text-xs font-heading flex items-center gap-1.5 cursor-pointer shadow-[2px_2px_0px_#000]">
                    <span>🚀</span> Mulai Sinkronkan Kelas
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function editClass(target) {
        let data;
        if (target && target.dataset) {
            data = {
                id: target.dataset.id || target.getAttribute('data-id'),
                name: target.dataset.name || target.getAttribute('data-name'),
                level: target.dataset.level || target.getAttribute('data-level'),
                academic_year_id: target.dataset.academicYearId || target.getAttribute('data-academic-year-id'),
                homeroom_teacher_id: target.dataset.homeroomTeacherId || target.getAttribute('data-homeroom-teacher-id'),
            };
        } else {
            data = target;
        }

        if (!data) return;

        const editName = document.getElementById('edit_name');
        const editLevel = document.getElementById('edit_level');
        const editYear = document.getElementById('edit_academic_year_id');
        const editTeacher = document.getElementById('edit_homeroom_teacher_id');
        const editForm = document.getElementById('editClassForm');

        if (editName) editName.value = data.name || '';
        if (editLevel) editLevel.value = data.level || '1';
        if (editYear) editYear.value = data.academic_year_id || '';
        if (editTeacher) editTeacher.value = data.homeroom_teacher_id || '';
        if (editForm) editForm.action = '/admin/classes/' + data.id;

        openModal('editClassModal');
    }
</script>
@endpush
@endsection
