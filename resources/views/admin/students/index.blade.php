@extends('layouts.admin')

@section('title', 'Manajemen Data Siswa & QR')
@section('page-title', 'Manajemen Data Siswa & Kartu QR')

@section('content')
<div class="space-y-6">
    <!-- Header, Filter & Action -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white neo-box p-5">
        <div>
            <h2 class="font-heading font-black text-xl text-black flex items-center gap-2">
                <span>🎓</span> Data Siswa & Identitas QR
            </h2>
            <div class="flex flex-wrap items-center gap-2 mt-1.5">
                <p class="text-xs font-semibold text-slate-600">
                    Kelola data profil siswa, generate identifikasi QR unik, dan cetak kartu absensi.
                </p>
                <!-- Badge Jumlah Siswa Sesuai Filter -->
                @if(!empty($selectedClass))
                    <span class="neo-badge bg-[#D0EBFF] text-blue-950 text-xs font-mono font-bold">
                        Rombel {{ $selectedClass->name }}: {{ $classStudentsCount }} Siswa
                    </span>
                    <span class="neo-badge bg-slate-100 text-slate-700 text-xs font-mono">
                        (Total Seluruh Kelas: {{ $totalStudentsCount }} Siswa)
                    </span>
                @else
                    <span class="neo-badge bg-[#FFF9DB] text-amber-950 text-xs font-mono font-bold">
                        Total Seluruh Siswa: {{ $totalStudentsCount }} Siswa
                    </span>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Filter Kelas -->
            <form method="GET" action="{{ route('admin.students.index') }}" class="flex items-center gap-2">
                @if(!empty($perPage) && $perPage != '25')
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                @endif
                <select name="class_id" onchange="this.form.submit()" class="px-3 py-2 neo-input text-xs bg-[#FFF9DB] font-bold cursor-pointer">
                    <option value="">-- Semua Kelas ({{ $totalStudentsCount }}) --</option>
                    @foreach($classes as $cls)
                        <option value="{{ $cls->id }}" {{ $selectedClassId == $cls->id ? 'selected' : '' }}>
                            {{ $cls->name }}
                        </option>
                    @endforeach
                </select>
            </form>

            <!-- Unduh Template Excel -->
            <a href="{{ route('admin.students.template') }}" 
               class="neo-btn bg-[#FFF9DB] hover:bg-[#ffec99] text-black p-2.5 text-xs flex items-center justify-center cursor-pointer font-heading shadow-[2px_2px_0px_#000] group relative" 
               title="Unduh Template Excel" aria-label="Unduh Template Excel">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span class="absolute -top-9 left-1/2 -translate-x-1/2 bg-black text-white text-[10px] font-black uppercase px-2 py-0.5 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap border border-black shadow-[2px_2px_0px_0px_#FFD43B] z-50">
                    Unduh Template
                </span>
            </a>

            <!-- Upload Excel Siswa -->
            <button type="button" onclick="openModal('importStudentModal')" 
                    class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black p-2.5 text-xs flex items-center justify-center cursor-pointer font-heading shadow-[2px_2px_0px_#000] group relative"
                    title="Upload File Excel" aria-label="Upload File Excel">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <span class="absolute -top-9 left-1/2 -translate-x-1/2 bg-black text-white text-[10px] font-black uppercase px-2 py-0.5 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap border border-black shadow-[2px_2px_0px_0px_#FFD43B] z-50">
                    Upload Excel
                </span>
            </button>

            <!-- Studio Cetak Kartu -->
            <a href="{{ route('admin.students.cards', ['class_id' => $selectedClassId]) }}" 
               class="neo-btn bg-white hover:bg-slate-100 text-black p-2.5 text-xs flex items-center justify-center cursor-pointer font-heading shadow-[2px_2px_0px_#000] group relative"
               title="Studio Cetak Kartu" aria-label="Studio Cetak Kartu">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                </svg>
                <span class="absolute -top-9 left-1/2 -translate-x-1/2 bg-black text-white text-[10px] font-black uppercase px-2 py-0.5 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap border border-black shadow-[2px_2px_0px_0px_#FFD43B] z-50">
                    Studio Cetak
                </span>
            </a>

            <!-- Kenaikan Kelas & Kelulusan -->
            <a href="{{ route('admin.students.promotion.index') }}" 
               class="neo-btn bg-[#B197FC] hover:bg-[#9775fa] text-black px-3 py-2 text-xs flex items-center gap-1.5 cursor-pointer font-heading font-black shadow-[2px_2px_0px_#000] group relative" 
               title="Kenaikan Kelas & Kelulusan Siswa" aria-label="Kenaikan Kelas & Kelulusan Siswa">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
                <span>Kenaikan Kelas</span>
            </a>

            <!-- Tambah Siswa Baru -->
            <button type="button" onclick="openModal('createStudentModal')" 
                    class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black p-2.5 text-xs flex items-center justify-center cursor-pointer font-heading shadow-[2px_2px_0px_#000] group relative"
                    title="Tambah Siswa Baru" aria-label="Tambah Siswa Baru">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span class="absolute -top-9 left-1/2 -translate-x-1/2 bg-black text-white text-[10px] font-black uppercase px-2 py-0.5 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap border border-black shadow-[2px_2px_0px_0px_#FFD43B] z-50">
                    Tambah Siswa
                </span>
            </button>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white neo-box overflow-hidden">
        <!-- Table Toolbar -->
        <div class="p-3.5 border-b-2 border-black bg-[#FFF9DB]/40 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="text-xs font-black uppercase tracking-wider font-heading text-black flex items-center gap-1.5">
                    <span>📋</span> Tabel Siswa
                </span>
                <span class="neo-badge bg-white text-black text-[11px] font-mono font-bold">
                    {{ $students->total() }} Data
                </span>
            </div>

            <!-- Filter Jumlah Tampilan (25, 50, 100, Semua) -->
            <form method="GET" action="{{ route('admin.students.index') }}" class="flex items-center gap-2">
                @if(!empty($selectedClassId))
                    <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
                @endif
                <div class="flex items-center gap-1 bg-white border-2 border-black px-2.5 py-1.5 shadow-[2px_2px_0px_#000]">
                    <span class="text-[10px] font-black uppercase text-slate-600 font-heading">Tampil:</span>
                    <select name="per_page" onchange="this.form.submit()" class="bg-transparent text-xs font-bold text-black focus:outline-none cursor-pointer">
                        <option value="25" {{ $perPage == '25' ? 'selected' : '' }}>25</option>
                        <option value="50" {{ $perPage == '50' ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $perPage == '100' ? 'selected' : '' }}>100</option>
                        <option value="semua" {{ $perPage == 'semua' || $perPage == 'all' ? 'selected' : '' }}>Semua</option>
                    </select>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-[#FFF9DB] border-b-2 border-black text-xs font-black uppercase tracking-wider">
                    <tr>
                        <th class="p-3.5 border-r border-black">No</th>
                        <th class="p-3.5 border-r border-black">NIS / NISN</th>
                        <th class="p-3.5 border-r border-black">Nama Siswa & Email</th>
                        <th class="p-3.5 border-r border-black">Kelas</th>
                        <th class="p-3.5 border-r border-black">L/P</th>
                        <th class="p-3.5 border-r border-black">QR Identifier</th>
                        <th class="p-3.5 text-center w-48">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black">
                    @forelse($students as $index => $student)
                    <tr class="hover:bg-slate-50 font-medium">
                        <td class="p-3.5 font-bold text-center border-r border-black">
                            {{ $students->firstItem() + $index }}
                        </td>
                        <td class="p-3.5 border-r border-black font-mono text-xs">
                            <span class="font-bold text-black">{{ $student->nis }}</span>
                            @if($student->nisn)
                                <span class="text-slate-400 block text-[10px]">NISN: {{ $student->nisn }}</span>
                            @endif
                        </td>
                        <td class="p-3.5 border-r border-black">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 border-2 border-black overflow-hidden shrink-0 bg-slate-100 flex items-center justify-center font-bold text-xs shadow-[1.5px_1.5px_0px_#000]">
                                    @if($student->photo)
                                        <img src="{{ $student->photo_url }}" alt="{{ $student->user->name ?? 'Siswa' }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-slate-500 font-mono text-xs">{{ strtoupper(substr($student->user->name ?? 'S', 0, 2)) }}</span>
                                    @endif
                                </div>
                                <div>
                                    <div class="font-bold text-black">{{ $student->user->name ?? '-' }}</div>
                                    <div class="text-xs text-slate-500 font-mono">{{ $student->user->email ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-3.5 border-r border-black">
                            <span class="neo-badge bg-[#E7F5FF] text-blue-900 text-[10px]">
                                {{ $student->schoolClass->name ?? '-' }}
                            </span>
                        </td>
                        <td class="p-3.5 border-r border-black font-bold text-center">
                            <span class="px-2 py-0.5 border border-black text-xs {{ $student->gender == 'L' ? 'bg-[#D0EBFF]' : 'bg-[#FCC2D7]' }}">
                                {{ $student->gender }}
                            </span>
                        </td>
                        <td class="p-3.5 border-r border-black">
                            <div class="flex items-center gap-2.5">
                                <button type="button" 
                                        onclick="previewQr({{ json_encode([
                                            'id' => $student->id,
                                            'name' => $student->user->name ?? '',
                                            'nis' => $student->nis,
                                            'nisn' => $student->nisn ?? '-',
                                            'class_name' => $student->schoolClass->name ?? '-',
                                            'birth_date' => $student->birth_date ? $student->birth_date->format('d/m/Y') : '-',
                                            'gender' => $student->gender == 'L' ? 'LAKI-LAKI' : 'PEREMPUAN',
                                            'qr' => $student->qr_code_identifier,
                                            'qr_image' => $student->qr_data_uri,
                                            'photo_url' => $student->photo ? $student->photo_url : null,
                                        ]) }})" 
                                        class="p-1 bg-white border-2 border-black shadow-[1.5px_1.5px_0px_#000] hover:bg-[#FFF9DB] shrink-0 cursor-pointer transition-transform hover:scale-105"
                                        title="Klik untuk melihat pratinjau kartu & QR">
                                    <img src="{{ $student->qr_data_uri }}" alt="QR {{ $student->nis }}" class="w-8 h-8 object-contain">
                                </button>
                                <div>
                                    <span class="font-mono font-bold text-xs text-black block">{{ $student->qr_code_identifier }}</span>
                                    <span class="text-[10px] text-slate-500 font-semibold flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span> Siap Scan
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="p-3.5 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <!-- 1. Kartu & QR (🪪) -->
                                <button type="button" 
                                        onclick="previewQr({{ json_encode([
                                            'id' => $student->id,
                                            'name' => $student->user->name ?? '',
                                            'nis' => $student->nis,
                                            'nisn' => $student->nisn ?? '-',
                                            'class_name' => $student->schoolClass->name ?? '-',
                                            'birth_date' => $student->birth_date ? $student->birth_date->format('d/m/Y') : '-',
                                            'gender' => $student->gender == 'L' ? 'LAKI-LAKI' : 'PEREMPUAN',
                                            'qr' => $student->qr_code_identifier,
                                            'qr_image' => $student->qr_data_uri,
                                            'photo_url' => $student->photo ? $student->photo_url : null,
                                        ]) }})" 
                                        class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black p-1.5 text-xs cursor-pointer group relative shadow-[1.5px_1.5px_0px_#000]"
                                        title="Kartu & QR" aria-label="Lihat Kartu & QR">
                                    <span class="text-sm leading-none block">🪪</span>
                                    <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-black text-white text-[10px] font-black uppercase px-2 py-0.5 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap border border-black shadow-[2px_2px_0px_0px_#FFD43B] z-50">
                                        Kartu & QR
                                    </span>
                                </button>

                                <!-- 2. Reset Password (Default NISN) -->
                                @php
                                    $resetPassDefault = !empty($student->nisn) ? $student->nisn : (!empty($student->nis) ? $student->nis : 'password');
                                    $resetPassLabel = !empty($student->nisn) ? 'NISN' : (!empty($student->nis) ? 'NIS' : 'Default');
                                @endphp
                                <form action="{{ route('admin.students.reset-password', $student) }}" method="POST">
                                    @csrf
                                    <button type="button" 
                                            onclick="confirmResetPassword(this, {{ json_encode($student->user->name ?? 'Siswa') }}, {{ json_encode($resetPassDefault) }}, {{ json_encode($resetPassLabel) }})"
                                            class="neo-btn bg-[#5294FF] hover:bg-blue-600 text-white p-1.5 text-xs cursor-pointer group relative shadow-[1.5px_1.5px_0px_#000]"
                                            title="Reset Password ({{ $resetPassLabel }})" aria-label="Reset Password">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                        </svg>
                                        <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-black text-white text-[10px] font-black uppercase px-2 py-0.5 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap border border-black shadow-[2px_2px_0px_0px_#FFD43B] z-50">
                                            Reset Password ({{ $resetPassLabel }})
                                        </span>
                                    </button>
                                </form>

                                <!-- 3. Edit Siswa -->
                                <button type="button" 
                                        onclick="editStudent({{ json_encode([
                                            'id' => $student->id,
                                            'name' => $student->user->name ?? '',
                                            'email' => $student->user->email ?? '',
                                            'nis' => $student->nis,
                                            'nisn' => $student->nisn ?? '',
                                            'school_class_id' => $student->school_class_id,
                                            'gender' => $student->gender,
                                            'birth_place' => $student->birth_place ?? '',
                                            'birth_date' => $student->birth_date ? $student->birth_date->format('Y-m-d') : '',
                                            'religion' => $student->religion ?? '',
                                            'phone' => $student->phone ?? '',
                                            'address' => $student->address ?? '',
                                            'family_status' => $student->family_status ?? '',
                                            'child_number' => $student->child_number ?? '',
                                            'previous_school' => $student->previous_school ?? '',
                                            'admission_date' => $student->admission_date ? $student->admission_date->format('Y-m-d') : '',
                                            'entry_grade' => $student->entry_grade ?? '',
                                            'father_name' => $student->father_name ?? '',
                                            'father_job' => $student->father_job ?? '',
                                            'mother_name' => $student->mother_name ?? '',
                                            'mother_job' => $student->mother_job ?? '',
                                            'parent_address' => $student->parent_address ?? '',
                                            'guardian_name' => $student->guardian_name ?? '',
                                            'guardian_job' => $student->guardian_job ?? '',
                                            'guardian_address' => $student->guardian_address ?? '',
                                            'photo_url' => $student->photo ? $student->photo_url : null,
                                        ]) }})" 
                                        class="neo-btn bg-[#FFD43B] hover:bg-yellow-400 text-black p-1.5 text-xs cursor-pointer group relative shadow-[1.5px_1.5px_0px_#000]"
                                        title="Edit Siswa" aria-label="Edit Siswa">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-black text-white text-[10px] font-black uppercase px-2 py-0.5 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap border border-black shadow-[2px_2px_0px_0px_#FFD43B] z-50">
                                        Edit
                                    </span>
                                </button>

                                <!-- 4. Hapus Siswa -->
                                <form action="{{ route('admin.students.destroy', $student) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" 
                                            onclick="confirmDeleteStudent(this, {{ json_encode($student->user->name ?? 'Siswa') }})"
                                            class="neo-btn bg-[#FF6B6B] hover:bg-red-600 text-white p-1.5 text-xs cursor-pointer group relative shadow-[1.5px_1.5px_0px_#000]"
                                            title="Hapus Siswa" aria-label="Hapus Siswa">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-black text-white text-[10px] font-black uppercase px-2 py-0.5 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap border border-black shadow-[2px_2px_0px_0px_#FFD43B] z-50">
                                            Hapus
                                        </span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-500 font-semibold">
                            Belum ada data siswa ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Footer / Pagination Info & Links -->
        <div class="p-4 border-t-2 border-black bg-slate-50 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="text-xs font-semibold text-slate-700">
                Menampilkan <span class="font-mono font-bold text-black">{{ $students->firstItem() ?? 0 }}</span> - <span class="font-mono font-bold text-black">{{ $students->lastItem() ?? 0 }}</span> dari <span class="font-mono font-bold text-black">{{ $students->total() }}</span> siswa
                @if(!empty($selectedClass))
                    <span class="text-slate-500 font-normal">(Kelas {{ $selectedClass->name }} • Total Semua Kelas: {{ $totalStudentsCount }})</span>
                @else
                    <span class="text-slate-500 font-normal">(Total Semua Kelas: {{ $totalStudentsCount }})</span>
                @endif
            </div>

            @if($students->hasPages())
                <div>
                    {{ $students->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
    <!-- Modal Upload Excel Siswa -->
<div id="importStudentModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-lg w-full p-6 space-y-5 relative">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <h3 class="font-heading font-black text-lg text-black flex items-center gap-2">
                <span>📑</span> Import Data Siswa dari Excel
            </h3>
            <button onclick="closeModal('importStudentModal')" class="text-black font-black text-xl hover:opacity-75 cursor-pointer">✕</button>
        </div>

        <div class="bg-[#E7F5FF] border-2 border-black p-3.5 text-xs text-blue-950 space-y-1.5 font-medium shadow-[2px_2px_0px_#000]">
            <div class="font-bold flex items-center gap-1.5 font-heading">
                <span>💡</span> Petunjuk Pengisian File:
            </div>
            <ul class="list-disc list-inside space-y-0.5 text-slate-700 pl-1">
                <li>Gunakan template resmi 24 kolom standar (No, NIS, NISN, Nama, JK, Tgl Lahir, dsb).</li>
                <li>Kolom <b>NAMA</b> dan <b>NIS</b> wajib terisi pada setiap baris data.</li>
                <li>Jika kolom <b>Password</b> kosong, sistem akan otomatis menggunakan NIS sebagai kata sandi.</li>
                <li>Format file yang didukung: <b>.xlsx, .xls, .csv</b> (Maksimal 5MB).</li>
            </ul>
            <div class="pt-1">
                <a href="{{ route('admin.students.template') }}" class="inline-flex items-center gap-1.5 text-blue-700 font-bold underline hover:text-blue-900">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Unduh file template siswa standar (.xlsx)
                </a>
            </div>
        </div>

        <form action="{{ route('admin.students.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1.5">
                    Pilih Kelas Tujuan / Default <span class="text-slate-500 font-normal">(opsional jika di Excel sudah ada kolom Kelas)</span>
                </label>
                <select name="school_class_id" class="w-full px-3 py-2 neo-input text-xs bg-slate-50 font-bold">
                    <option value="">-- Otomatis Dari Kolom Kelas di Excel --</option>
                    @foreach($classes as $cls)
                        <option value="{{ $cls->id }}" {{ $selectedClassId == $cls->id ? 'selected' : '' }}>
                            {{ $cls->name }} ({{ $cls->level }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1.5">Pilih File Excel / CSV *</label>
                <div class="border-2 border-dashed border-black rounded-lg p-5 bg-slate-50 text-center hover:bg-slate-100 transition-all cursor-pointer relative">
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="absolute inset-0 opacity-0 cursor-pointer w-full h-full" onchange="document.getElementById('fileNameDisplayStudents').textContent = this.files[0] ? this.files[0].name : 'Belum ada file dipilih'">
                    <div class="space-y-1.5 pointer-events-none">
                        <div class="text-3xl">📊</div>
                        <div class="text-xs font-bold text-black font-heading">Tarik & Lepas File ke Sini atau Klik untuk Memilih</div>
                        <div id="fileNameDisplayStudents" class="text-[11px] font-mono text-slate-500 font-semibold truncate max-w-xs mx-auto">
                            Format .xlsx, .xls, .csv (Maks 5MB)
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-[#FFF9DB] border-2 border-black p-3 flex items-start gap-2.5">
                <input type="checkbox" name="upsert" id="student_upsert_check" value="1" checked class="mt-0.5 border-2 border-black text-black focus:ring-0 cursor-pointer">
                <label for="student_upsert_check" class="text-xs font-semibold text-black cursor-pointer leading-tight">
                    <span class="font-bold block">Perbarui data jika NIS sudah terdaftar (Upsert)</span>
                    <span class="text-[11px] text-slate-600 block mt-0.5">Jika dicentang, siswa dengan NIS yang sama akan diperbarui biodatanya, bukan dilewati.</span>
                </label>
            </div>

            <div class="pt-3 border-t-2 border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('importStudentModal')" class="neo-btn bg-white text-black px-4 py-2 text-xs cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-5 py-2 text-xs font-heading flex items-center gap-1.5 cursor-pointer shadow-[2px_2px_0px_#000]">
                    <span>🚀</span> Upload & Proses Data Siswa
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Siswa -->
<div id="createStudentModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-lg w-full p-6 space-y-5 relative max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <h3 class="font-heading font-black text-lg text-black flex items-center gap-2">
                <span>➕</span> Tambah Siswa Baru
            </h3>
            <button onclick="closeModal('createStudentModal')" class="text-black font-black text-xl hover:opacity-75">✕</button>
        </div>

        <form action="{{ route('admin.students.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Nama Lengkap Siswa *</label>
                <input type="text" name="name" required placeholder="Contoh: Muhammad Rizky" class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
            </div>

            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Foto Siswa (Opsional)</label>
                <div class="flex items-center gap-3">
                    <div id="create_photo_preview_container" class="w-14 h-16 border-2 border-black bg-slate-100 flex items-center justify-center overflow-hidden shrink-0 shadow-[2px_2px_0px_0px_#000]">
                        <span id="create_photo_placeholder" class="text-[10px] text-slate-400 font-bold uppercase">Foto 3×4</span>
                        <img id="create_photo_preview" src="" alt="Preview" class="w-full h-full object-cover hidden">
                    </div>
                    <div class="flex-1">
                        <input type="file" name="photo" id="create_photo_input" accept="image/jpeg,image/png,image/jpg,image/webp" class="w-full text-xs file:mr-3 file:py-1.5 file:px-3 file:border-2 file:border-black file:text-xs file:font-heading file:font-bold file:bg-[#FFD43B] file:text-black hover:file:bg-[#fcc419] cursor-pointer" onchange="previewImage(this, 'create_photo_preview', 'create_photo_placeholder')">
                        <span class="text-[11px] text-slate-500 font-semibold block mt-1">Format: JPG, PNG, WEBP. Maksimal 2MB.</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">NIS (Nomor Induk Siswa) *</label>
                    <input type="text" name="nis" required placeholder="1234..." class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
                </div>
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">NISN (Opsional)</label>
                    <input type="text" name="nisn" placeholder="0081..." class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">Pilih Kelas *</label>
                    <select name="school_class_id" required class="w-full px-3 py-2 neo-input text-sm bg-slate-50 font-medium">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($classes as $cls)
                            <option value="{{ $cls->id }}">{{ $cls->name }} ({{ $cls->level }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">Jenis Kelamin *</label>
                    <select name="gender" required class="w-full px-3 py-2 neo-input text-sm bg-slate-50 font-medium">
                        <option value="L">Laki-laki (L)</option>
                        <option value="P">Perempuan (P)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">Tanggal Lahir</label>
                    <input type="date" name="birth_date" class="w-full px-3 py-2 neo-input text-sm bg-slate-50 font-medium">
                </div>
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">No. Kontak Siswa</label>
                    <input type="text" name="phone" placeholder="08..." class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
                </div>
            </div>

            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Alamat Email Login (Opsional)</label>
                <input type="email" name="email" placeholder="Kosongkan untuk otomatis dibuat dari NIS" class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
                <span class="text-[11px] text-slate-500 font-semibold mt-0.5 block">
                    Password akun otomatis diset: <code>password</code>
                </span>
            </div>

            <div class="pt-3 border-t-2 border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('createStudentModal')" class="neo-btn bg-white text-black px-4 py-2 text-xs">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#20C997] text-black px-5 py-2 text-xs font-heading">
                    Simpan & Generate QR
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Siswa Bertab -->
@include('partials._edit-student-modal', ['isHomeroom' => false, 'classes' => $classes])

<!-- Modal Preview Kartu QR Siswa -->
<div id="qrPreviewModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-md w-full p-6 space-y-4 relative max-h-[95vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b-2 border-black pb-2">
            <span class="neo-badge bg-[#FFD43B] text-black flex items-center gap-1.5">
                <span>🪪</span> PRATINJAU KARTU PELAJAR & QR
            </span>
            <button onclick="closeModal('qrPreviewModal')" class="text-black font-black text-xl hover:opacity-75 cursor-pointer">✕</button>
        </div>

        <!-- Kartu Pelajar Format Resmi (Diambil dari /admin/students/cards) -->
        <div class="border-2 border-black rounded bg-white shadow-[3px_3px_0px_0px_#000] overflow-hidden text-black font-sans select-none">
            <!-- Kop Sekolah -->
            <div class="bg-slate-900 text-white px-2.5 py-1.5 flex items-center justify-between border-b-2 border-black">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-[#FFD43B] border border-black flex items-center justify-center font-black text-xs text-black font-heading shrink-0">
                        🎓
                    </div>
                    <div class="leading-tight truncate">
                        <div class="font-heading font-black text-[11px] uppercase tracking-tight truncate text-[#FFD43B]">
                            {{ $setting->school_name ?? 'SMP NEGERI 1 GARUDA' }}
                        </div>
                        <div class="text-[8px] font-mono text-slate-300">
                            NPSN: {{ $setting->npsn ?? '-' }} • {{ $setting->level ?? 'SMP' }}
                        </div>
                    </div>
                </div>
                <span class="text-[8px] font-mono font-bold bg-[#20C997] text-black px-1.5 py-0.5 rounded border border-black uppercase shrink-0">
                    RESMI
                </span>
            </div>

            <!-- Pita Judul Kartu -->
            <div class="bg-[#FFD43B] text-black border-b border-black text-center py-0.5 font-heading font-black text-[9px] uppercase tracking-widest">
                KARTU TANDA PELAJAR & PRESENSI
            </div>

            <!-- Badan Kartu: Foto, Identitas, & Real QR Code dari /admin/students/cards -->
            <div class="p-3 flex items-center justify-between gap-2.5 bg-gradient-to-br from-white to-slate-50 min-h-[120px]">
                <!-- Foto Siswa -->
                <div class="w-16 h-20 bg-slate-100 border-2 border-black rounded shadow-[1.5px_1.5px_0px_#000] flex flex-col items-center justify-center shrink-0 relative overflow-hidden">
                    <img id="qr_preview_photo" src="" alt="Foto" class="w-full h-full object-cover hidden">
                    <div id="qr_preview_placeholder" class="flex flex-col items-center justify-center">
                        <span class="text-xl">👤</span>
                        <span class="text-[7px] font-bold text-slate-500 uppercase mt-0.5">FOTO 3×4</span>
                    </div>
                    <span id="qr_preview_gender_badge" class="absolute bottom-0 inset-x-0 bg-black text-white text-[7px] font-bold text-center py-0.2 uppercase">
                        LAKI-LAKI
                    </span>
                </div>

                <!-- Data Diri Siswa -->
                <div class="flex-1 min-w-0 space-y-0.5 text-[10px]">
                    <div id="qr_preview_name" class="font-heading font-black text-xs text-black truncate leading-snug">
                        Nama Siswa
                    </div>
                    <div class="grid grid-cols-3 gap-0.5 font-medium text-[9px] text-slate-700">
                        <span class="text-slate-500">NIS</span>
                        <span id="qr_preview_nis" class="col-span-2 font-mono font-bold text-black">: -</span>
                        
                        <span class="text-slate-500">NISN</span>
                        <span id="qr_preview_nisn" class="col-span-2 font-mono font-bold text-black">: -</span>

                        <span class="text-slate-500">Kelas</span>
                        <span id="qr_preview_class" class="col-span-2 font-bold text-black">: -</span>

                        <span class="text-slate-500">Lahir</span>
                        <span id="qr_preview_birth" class="col-span-2 font-mono text-black">: -</span>
                    </div>
                </div>

                <!-- Real QR Code Scanner Absensi (Diambil dari /admin/students/cards) -->
                <div class="flex flex-col items-center justify-center shrink-0">
                    <div class="bg-white p-1.5 border-2 border-black rounded shadow-[1.5px_1.5px_0px_#000]">
                        <img id="qr_preview_image" 
                             src="" 
                             alt="QR Code Absensi" 
                             class="w-16 h-16 object-contain">
                    </div>
                    <span class="text-[7px] font-black uppercase text-black mt-0.5 tracking-wider">
                        SCAN PRESENSI
                    </span>
                </div>
            </div>

            <!-- Footer Kartu -->
            <div class="bg-slate-100 px-2.5 py-1 border-t border-black flex items-center justify-between text-[8px] font-medium text-slate-600">
                <span>Berlaku Selama Menjadi Siswa Aktif</span>
                <span id="qr_preview_code" class="font-mono font-bold text-black">-</span>
            </div>
        </div>

        <div class="flex items-center justify-between pt-2 border-t border-slate-200">
            <a href="{{ route('admin.students.cards') }}" class="text-xs font-bold text-blue-700 hover:underline flex items-center gap-1">
                <span>🪪</span> Buka Studio Kartu
            </a>
            <div class="flex items-center gap-2">
                <a id="qr_print_single_btn" href="#" target="_blank" class="neo-btn bg-[#20C997] hover:bg-[#1bb386] text-black px-4 py-2 text-xs font-heading font-bold flex items-center gap-1.5 cursor-pointer shadow-[2px_2px_0px_0px_#000]">
                    <span>🖨️</span> Cetak Kartu Pelajar
                </a>
                <button onclick="closeModal('qrPreviewModal')" class="neo-btn bg-white text-black px-4 py-2 text-xs cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function previewImage(input, previewImgId, placeholderId) {
        const previewImg = document.getElementById(previewImgId);
        const placeholder = document.getElementById(placeholderId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewImg.classList.remove('hidden');
                if (placeholder) placeholder.classList.add('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function switchEditTab(tabName) {
        document.querySelectorAll('.edit-tab-pane').forEach(el => el.classList.add('hidden'));
        const pane = document.getElementById('editTabPane-' + tabName);
        if (pane) pane.classList.remove('hidden');

        document.querySelectorAll('.edit-tab-btn').forEach(btn => {
            btn.classList.remove('bg-black', 'text-white', 'shadow-[2px_2px_0px_#FFD43B]');
            btn.classList.add('bg-white', 'text-black', 'hover:bg-slate-100');
        });

        const activeBtn = document.getElementById('editTabBtn-' + tabName);
        if (activeBtn) {
            activeBtn.classList.remove('bg-white', 'text-black', 'hover:bg-slate-100');
            activeBtn.classList.add('bg-black', 'text-white', 'shadow-[2px_2px_0px_#FFD43B]');
        }
    }

    function editStudent(data) {
        // Tab 1: Identitas & Foto
        document.getElementById('edit_stu_name').value = data.name || '';
        document.getElementById('edit_stu_nis').value = data.nis || '';
        document.getElementById('edit_stu_nisn').value = data.nisn || '';
        document.getElementById('edit_stu_gender').value = data.gender || 'L';
        document.getElementById('edit_stu_birth_place').value = data.birth_place || '';
        document.getElementById('edit_stu_birth').value = data.birth_date || '';
        document.getElementById('edit_stu_religion').value = data.religion || '';

        const classSelect = document.getElementById('edit_stu_class');
        if (classSelect) classSelect.value = data.school_class_id || '';

        // Tab 2: Kontak & Alamat
        document.getElementById('edit_stu_phone').value = data.phone || '';
        document.getElementById('edit_stu_email').value = data.email || '';
        const passwordInput = document.getElementById('edit_stu_password');
        if (passwordInput) passwordInput.value = '';
        document.getElementById('edit_stu_address').value = data.address || '';

        // Tab 3: Status & Riwayat Masuk
        document.getElementById('edit_stu_family_status').value = data.family_status || '';
        document.getElementById('edit_stu_child_number').value = data.child_number || '';
        document.getElementById('edit_stu_previous_school').value = data.previous_school || '';
        document.getElementById('edit_stu_admission_date').value = data.admission_date || '';
        document.getElementById('edit_stu_entry_grade').value = data.entry_grade || '';

        // Tab 4: Orang Tua & Wali
        document.getElementById('edit_stu_father_name').value = data.father_name || '';
        document.getElementById('edit_stu_father_job').value = data.father_job || '';
        document.getElementById('edit_stu_mother_name').value = data.mother_name || '';
        document.getElementById('edit_stu_mother_job').value = data.mother_job || '';
        document.getElementById('edit_stu_parent_address').value = data.parent_address || '';
        document.getElementById('edit_stu_guardian_name').value = data.guardian_name || '';
        document.getElementById('edit_stu_guardian_job').value = data.guardian_job || '';
        document.getElementById('edit_stu_guardian_address').value = data.guardian_address || '';

        document.getElementById('editStudentForm').action = '/admin/students/' + data.id;

        // Reset foto input & preview
        const photoInput = document.getElementById('edit_photo_input');
        if (photoInput) photoInput.value = '';

        const photoPreview = document.getElementById('edit_photo_preview');
        const photoPlaceholder = document.getElementById('edit_photo_placeholder');
        const removePhotoWrapper = document.getElementById('remove_photo_wrapper');
        const removePhotoCheckbox = document.getElementById('edit_remove_photo');

        if (removePhotoCheckbox) removePhotoCheckbox.checked = false;

        if (data.photo_url) {
            photoPreview.src = data.photo_url;
            photoPreview.classList.remove('hidden');
            if (photoPlaceholder) photoPlaceholder.classList.add('hidden');
            if (removePhotoWrapper) {
                removePhotoWrapper.classList.remove('hidden');
                removePhotoWrapper.classList.add('flex');
            }
        } else {
            photoPreview.src = '';
            photoPreview.classList.add('hidden');
            if (photoPlaceholder) photoPlaceholder.classList.remove('hidden');
            if (removePhotoWrapper) {
                removePhotoWrapper.classList.add('hidden');
                removePhotoWrapper.classList.remove('flex');
            }
        }

        // Buka tab pertama
        switchEditTab('identitas');

        openModal('editStudentModal');
    }

    function previewQr(data) {
        document.getElementById('qr_preview_name').innerText = data.name;
        document.getElementById('qr_preview_nis').innerText = ': ' + data.nis;
        document.getElementById('qr_preview_nisn').innerText = ': ' + (data.nisn || '-');
        document.getElementById('qr_preview_class').innerText = ': ' + data.class_name;
        document.getElementById('qr_preview_birth').innerText = ': ' + (data.birth_date || '-');
        document.getElementById('qr_preview_code').innerText = data.qr;
        document.getElementById('qr_preview_gender_badge').innerText = data.gender || 'LAKI-LAKI';
        document.getElementById('qr_print_single_btn').href = '/admin/students/' + data.id + '/card';

        // Real QR image dari /admin/students/cards
        document.getElementById('qr_preview_image').src = data.qr_image;

        // Foto preview
        const photoEl = document.getElementById('qr_preview_photo');
        const placeholderEl = document.getElementById('qr_preview_placeholder');
        if (data.photo_url) {
            photoEl.src = data.photo_url;
            photoEl.classList.remove('hidden');
            placeholderEl.classList.add('hidden');
        } else {
            photoEl.src = '';
            photoEl.classList.add('hidden');
            placeholderEl.classList.remove('hidden');
        }

        openModal('qrPreviewModal');
    }

    function confirmResetPassword(button, studentName, defaultPassword, label) {
        const form = button.closest('form');
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Reset Kata Sandi?',
                html: `
                    <div class="text-left text-sm space-y-3 mt-1">
                        <p class="text-slate-700">Kata sandi akun siswa <strong class="text-black font-bold">${studentName}</strong> akan di-reset menggunakan <strong>${label}</strong>:</p>
                        <div class="p-3 bg-[#FFF9DB] border-2 border-black font-mono font-black text-center text-black text-lg shadow-[2px_2px_0px_#000] tracking-wider">
                            ${defaultPassword}
                        </div>
                        <p class="text-xs text-slate-500 italic text-center">Setelah di-reset, siswa dapat langsung login menggunakan kata sandi default di atas.</p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#5294FF',
                cancelButtonColor: '#475569',
                confirmButtonText: '🔑 Ya, Reset Sekarang',
                cancelButtonText: 'Batal',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        } else {
            if (confirm(`Reset kata sandi siswa ${studentName} ke ${label} (${defaultPassword})?`)) {
                form.submit();
            }
        }
    }

    function confirmDeleteStudent(button, studentName) {
        const form = button.closest('form');
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Hapus Data Siswa?',
                html: `
                    <div class="text-left text-sm space-y-2 mt-1">
                        <p class="text-slate-700">Apakah Anda yakin ingin menghapus data siswa <strong class="text-black font-bold">${studentName}</strong>?</p>
                        <p class="text-xs text-rose-700 font-bold bg-[#FFE3E3] border border-rose-300 p-2.5">
                            ⚠ Perhatian: Tindakan ini akan menghapus akun login dan seluruh histori presensi siswa ini secara permanen.
                        </p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#FF6B6B',
                cancelButtonColor: '#475569',
                confirmButtonText: '🗑️ Ya, Hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        } else {
            if (confirm(`Apakah Anda yakin ingin menghapus data siswa ${studentName}?`)) {
                form.submit();
            }
        }
    }
</script>
@endpush
@endsection
