@extends('layouts.admin')

@section('title', 'Manajemen Data Guru')
@section('page-title', 'Manajemen Data Guru')

@section('content')
<div class="space-y-6">
    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white neo-box p-5">
        <div>
            <h2 class="font-heading font-black text-xl text-black flex items-center gap-2">
                <span>👨‍🏫</span> Daftar Guru & Tenaga Pendidik
            </h2>
            <p class="text-xs font-semibold text-slate-600 mt-1">
                Kelola profil lengkap guru, akun login (NUPTK / NIP), dan penugasan wali kelas.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.teachers.template') }}" class="neo-btn bg-[#FFF9DB] hover:bg-[#ffec99] text-black px-3.5 py-2 text-xs uppercase flex items-center gap-1.5 cursor-pointer font-heading" title="Unduh Format Template Excel 9 Kolom">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span>Unduh Template (9 Kolom)</span>
            </a>

            <button onclick="openModal('importTeacherModal')" class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-3.5 py-2 text-xs uppercase flex items-center gap-1.5 cursor-pointer font-heading">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <span>Upload Excel</span>
            </button>

            <button onclick="openModal('createTeacherModal')" class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black px-4 py-2 text-xs uppercase flex items-center gap-1.5 cursor-pointer font-heading">
                <span>+</span> Tambah Guru Baru
            </button>
        </div>
    </div>

    <!-- Search & Sort Toolbar -->
    <div class="bg-white neo-box p-4">
        <form action="{{ route('admin.teachers.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between">
            <!-- Search Input -->
            <div class="relative flex-1">
                <input type="text" 
                       name="search" 
                       value="{{ $search ?? '' }}" 
                       placeholder="Cari nama guru, NUPTK, NIP, atau email..." 
                       class="w-full pl-9 pr-3 py-2 text-xs font-medium border-2 border-black rounded shadow-[2px_2px_0px_0px_#000] focus:outline-none focus:ring-2 focus:ring-[#FFD43B]">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <!-- Urutkan Dropdown -->
                <div class="flex items-center gap-1.5">
                    <span class="text-xs font-bold text-slate-700 whitespace-nowrap">Urutkan:</span>
                    <select name="sort" onchange="this.form.submit()" class="text-xs font-bold py-2 px-2.5 border-2 border-black rounded shadow-[2px_2px_0px_0px_#000] bg-white cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#FFD43B]">
                        <option value="name_asc" {{ ($sort ?? 'name_asc') === 'name_asc' ? 'selected' : '' }}>Nama: A - Z</option>
                        <option value="name_desc" {{ ($sort ?? '') === 'name_desc' ? 'selected' : '' }}>Nama: Z - A</option>
                        <option value="latest" {{ ($sort ?? '') === 'latest' ? 'selected' : '' }}>Terbaru Dibuat</option>
                        <option value="oldest" {{ ($sort ?? '') === 'oldest' ? 'selected' : '' }}>Paling Awal Dibuat</option>
                    </select>
                </div>

                <button type="submit" class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-3.5 py-2 text-xs font-bold cursor-pointer">
                    Cari
                </button>

                @if(!empty($search) || ($sort ?? 'name_asc') !== 'name_asc')
                    <a href="{{ route('admin.teachers.index') }}" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black px-3 py-2 text-xs font-bold cursor-pointer flex items-center gap-1" title="Reset Semua Filter">
                        <span>✕</span> Reset
                    </a>
                @endif
            </div>
        </form>

        @if(!empty($search))
            <div class="flex items-center justify-between text-xs bg-[#FFF9DB] border border-black p-2 rounded mt-3">
                <span class="font-medium text-slate-800">
                    Menampilkan <strong>{{ $teachers->total() }}</strong> guru untuk pencarian <strong>"{{ $search }}"</strong>
                </span>
                <a href="{{ route('admin.teachers.index') }}" class="text-[11px] font-bold text-red-600 underline hover:text-red-800">
                    Hapus Pencarian
                </a>
            </div>
        @endif
    </div>

    <!-- Table Card -->
    <div class="bg-white neo-box overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-[#FFF9DB] border-b-2 border-black text-xs font-black uppercase tracking-wider">
                    <tr>
                        <th class="p-3.5 border-r border-black text-center w-12">No</th>
                        <th class="p-3.5 border-r border-black">NUPTK / NIP</th>
                        <th class="p-3.5 border-r border-black">
                            <a href="{{ route('admin.teachers.index', array_merge(request()->except(['sort', 'page']), ['sort' => ($sort ?? 'name_asc') === 'name_asc' ? 'name_desc' : 'name_asc'])) }}" 
                               class="inline-flex items-center gap-1.5 text-black hover:text-blue-900 group"
                               title="Klik untuk membalikkan urutan A-Z / Z-A">
                                <span>Nama Guru</span>
                                @if(($sort ?? 'name_asc') === 'name_asc')
                                    <span class="text-[9px] bg-black text-white px-1.5 py-0.5 rounded font-mono font-bold shadow-[1px_1px_0px_0px_#000]">A-Z ▲</span>
                                @elseif(($sort ?? '') === 'name_desc')
                                    <span class="text-[9px] bg-black text-white px-1.5 py-0.5 rounded font-mono font-bold shadow-[1px_1px_0px_0px_#000]">Z-A ▼</span>
                                @else
                                    <span class="text-[11px] text-slate-400 group-hover:text-black">↕</span>
                                @endif
                            </a>
                        </th>
                        <th class="p-3.5 border-r border-black text-center w-14">L/P</th>
                        <th class="p-3.5 border-r border-black">Tempat, Tgl Lahir</th>
                        <th class="p-3.5 border-r border-black text-center">Pendidikan</th>
                        <th class="p-3.5 border-r border-black">Status Email</th>
                        <th class="p-3.5 border-r border-black">Wali Kelas & Penugasan</th>
                        <th class="p-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black">
                    @forelse($teachers as $index => $teacher)
                    <tr class="hover:bg-slate-50 font-medium">
                        <td class="p-3.5 font-bold text-center border-r border-black text-xs">
                            {{ $teachers->firstItem() + $index }}
                        </td>
                        <td class="p-3.5 border-r border-black text-xs font-mono space-y-1">
                            <div>
                                <span class="text-[10px] text-slate-500 uppercase font-bold">NUPTK:</span>
                                <span class="font-bold text-black">{{ $teacher->nuptk ?: '-' }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-500 uppercase font-bold">NIP:</span>
                                <span class="text-slate-700">{{ $teacher->nip ?: '-' }}</span>
                            </div>
                        </td>
                        <td class="p-3.5 border-r border-black">
                            <div class="font-bold text-black text-sm">{{ $teacher->user->name ?? '-' }}</div>
                            <div class="text-[11px] text-slate-500 font-mono">{{ $teacher->phone ?: 'No HP: -' }}</div>
                        </td>
                        <td class="p-3.5 border-r border-black text-center font-bold">
                            @if($teacher->gender === 'L')
                                <span class="neo-badge bg-[#D0EBFF] text-blue-900 text-[10px] px-2 py-0.5">L</span>
                            @elseif($teacher->gender === 'P')
                                <span class="neo-badge bg-[#FCC2D7] text-rose-900 text-[10px] px-2 py-0.5">P</span>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="p-3.5 border-r border-black text-xs">
                            <div class="font-bold text-slate-800">{{ $teacher->birth_place ?: '-' }}</div>
                            <div class="text-[11px] font-mono text-slate-500">
                                {{ $teacher->birth_date ? $teacher->birth_date->format('d-m-Y') : '-' }}
                            </div>
                        </td>
                        <td class="p-3.5 border-r border-black text-center">
                            @if($teacher->last_education)
                                <span class="neo-badge bg-slate-100 text-black text-[11px] font-bold">
                                    {{ $teacher->last_education }}
                                </span>
                            @else
                                <span class="text-slate-400 text-xs">-</span>
                            @endif
                        </td>
                        <td class="p-3 border-r border-black text-center whitespace-nowrap">
                            @if($teacher->user?->email)
                                <div class="inline-flex flex-col items-center">
                                    <span class="font-mono text-xs font-bold text-slate-900 truncate max-w-[160px]" title="{{ $teacher->user->email }}">
                                        {{ $teacher->user->email }}
                                    </span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 mt-0.5">
                                        <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span>Terdaftar</span>
                                    </span>
                                </div>
                            @else
                                <div class="inline-flex flex-col items-center gap-1">
                                    <span class="neo-badge bg-[#FFF9DB] text-amber-950 text-[10px] px-2.5 py-0.5 font-black">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5 animate-pulse shrink-0"></span>
                                        Belum Diisi
                                    </span>
                                    <span class="text-[9px] font-semibold text-slate-500 tracking-wide">Diisi Mandiri</span>
                                </div>
                            @endif
                        </td>
                        <td class="p-3 border-r border-black text-xs space-y-2">
                            <div class="flex items-center gap-1.5">
                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider shrink-0">Wali:</span>
                                @if($teacher->homeroomClasses->isNotEmpty())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($teacher->homeroomClasses as $cls)
                                            <span class="neo-badge bg-[#D3F9D8] text-emerald-950 text-[10px] font-black px-2 py-0.5">
                                                {{ $cls->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-[11px] text-slate-400 font-medium italic">Bukan Wali Kelas</span>
                                @endif
                            </div>

                            <div class="flex items-start gap-1.5">
                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider shrink-0 pt-0.5">KBM:</span>
                                @if($teacher->assignments->isNotEmpty())
                                    @php
                                        $assignedSubNames = $teacher->assignments->pluck('subject.name')->unique()->filter();
                                        $assignedClassCount = $teacher->assignments->pluck('school_class_id')->unique()->count();
                                    @endphp
                                    <div class="flex flex-wrap gap-1 items-center">
                                        @foreach($assignedSubNames as $sName)
                                            <span class="neo-badge bg-[#E7F5FF] text-blue-950 text-[10px] font-bold px-2 py-0.5">
                                                {{ $sName }}
                                            </span>
                                        @endforeach
                                        <span class="text-[10px] font-mono text-slate-700 bg-slate-100 px-1.5 py-0.5 border border-slate-300 rounded font-semibold whitespace-nowrap">
                                            ({{ $assignedClassCount }} Rombel)
                                        </span>
                                    </div>
                                @else
                                    <span class="neo-badge bg-[#FFF9DB] text-amber-900 text-[10px] font-bold px-2 py-0.5">
                                        Bebas (Semua Mapel)
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="p-3 text-center">
                            <div class="flex flex-col items-center justify-center gap-1.5 max-w-[135px] mx-auto">
                                <button onclick="openAssignmentModal({{ $teacher->id }}, '{{ addslashes($teacher->user->name ?? 'Guru') }}')" class="w-full neo-btn bg-[#CC5DE8] hover:bg-[#b142cc] text-white px-2 py-1 text-[11px] cursor-pointer flex items-center justify-center gap-1 font-bold whitespace-nowrap shadow-[1.5px_1.5px_0px_0px_#000]" title="Atur Mata Pelajaran dan Rombel yang Diajar">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                    <span>Atur Mengajar</span>
                                </button>

                                <div class="flex items-center gap-1 w-full">
                                    <button onclick="editTeacher({{ json_encode([
                                        'id' => $teacher->id,
                                        'name' => $teacher->user->name ?? '',
                                        'nuptk' => $teacher->nuptk ?? '',
                                        'nip' => $teacher->nip ?? '',
                                        'gender' => $teacher->gender ?? 'L',
                                        'birth_place' => $teacher->birth_place ?? '',
                                        'birth_date' => $teacher->birth_date ? $teacher->birth_date->format('Y-m-d') : '',
                                        'last_education' => $teacher->last_education ?? '',
                                        'email' => $teacher->user->email ?? '',
                                        'phone' => $teacher->phone ?? '',
                                    ]) }})" class="flex-1 neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-2 py-1 text-[11px] cursor-pointer font-bold shadow-[1.5px_1.5px_0px_0px_#000]" title="Edit Data Guru">
                                        Edit
                                    </button>

                                    <form action="{{ route('admin.teachers.destroy', $teacher) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data guru ini?')" class="flex-1">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full neo-btn bg-[#FF6B6B] hover:bg-[#fa5252] text-white px-2 py-1 text-[11px] cursor-pointer font-bold shadow-[1.5px_1.5px_0px_0px_#000]" title="Hapus Guru">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="p-8 text-center text-slate-500 font-semibold space-y-2">
                            @if(!empty($search))
                                <div class="text-sm font-bold text-slate-800">
                                    Tidak ada data guru yang cocok dengan pencarian "{{ $search }}".
                                </div>
                                <div class="pt-2">
                                    <a href="{{ route('admin.teachers.index') }}" class="neo-btn bg-[#FFD43B] text-black text-xs px-3.5 py-1.5 font-bold inline-flex items-center gap-1">
                                        <span>↺</span> Tampilkan Semua Guru (A-Z)
                                    </a>
                                </div>
                            @else
                                <div>Belum ada data guru. Klik tombol "+ Tambah Guru Baru" atau "Upload Excel" untuk menambahkan.</div>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($teachers->hasPages())
        <div class="p-4 border-t-2 border-black bg-slate-50">
            {{ $teachers->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Import Excel Guru (Format 9 Kolom) -->
<div id="importTeacherModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-lg w-full p-6 space-y-5 relative">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <h3 class="font-heading font-black text-lg text-black flex items-center gap-2">
                <span>📑</span> Import Data Guru dari Excel
            </h3>
            <button onclick="closeModal('importTeacherModal')" class="text-black font-black text-xl hover:opacity-75">✕</button>
        </div>

        <div class="bg-[#E7F5FF] border-2 border-black p-3.5 rounded text-xs text-blue-950 space-y-1.5 font-medium">
            <div class="font-bold flex items-center gap-1.5">
                <span>💡</span> Format Kolom Excel Resmi (9 Kolom):
            </div>
            <div class="bg-white p-2 border border-black font-mono text-[11px] space-y-0.5">
                <div>No | NUPTK | NIP | NAMA | JENIS KELAMIN | Tempat Lahir | Tgl Lahir (dd-mm-yyyy) | Pendidikan Terakhir | Password</div>
            </div>
            <ul class="list-disc list-inside space-y-0.5 text-slate-700 pl-1 text-[11px]">
                <li><strong>Email tidak perlu diisi:</strong> Guru akan melengkapi email secara mandiri di akun mereka.</li>
                <li>Kolom <strong>Password</strong> opsional (default otomatis: <code>password</code>).</li>
                <li>Format tanggal lahir: <code>dd-mm-yyyy</code> (contoh: <code>14-05-1985</code>).</li>
                <li>Jenis Kelamin: <code>L</code> (Laki-laki) atau <code>P</code> (Perempuan).</li>
            </ul>
            <div class="pt-1">
                <a href="{{ route('admin.teachers.template') }}" class="inline-flex items-center gap-1.5 text-blue-700 font-bold underline hover:text-blue-900">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Unduh file template resmi di sini (.xlsx)
                </a>
            </div>
        </div>

        <form action="{{ route('admin.teachers.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1.5">Pilih File Excel / CSV *</label>
                <div class="border-2 border-dashed border-black rounded-lg p-5 bg-slate-50 text-center hover:bg-slate-100 transition-all cursor-pointer relative">
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="absolute inset-0 opacity-0 cursor-pointer w-full h-full" onchange="document.getElementById('fileNameDisplay').textContent = this.files[0] ? this.files[0].name : 'Belum ada file dipilih'">
                    <div class="space-y-1.5 pointer-events-none">
                        <div class="text-3xl">📊</div>
                        <div class="text-xs font-bold text-black font-heading">Tarik & Lepas File ke Sini atau Klik untuk Memilih</div>
                        <div id="fileNameDisplay" class="text-[11px] font-mono text-slate-500 font-semibold truncate max-w-xs mx-auto">
                            Format .xlsx, .xls, .csv (Maks 5MB)
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-3 border-t-2 border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('importTeacherModal')" class="neo-btn bg-white text-black px-4 py-2 text-xs">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-5 py-2 text-xs font-heading flex items-center gap-1.5">
                    <span>🚀</span> Upload & Proses Data
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Guru -->
<div id="createTeacherModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-xl w-full p-6 space-y-4 relative max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <h3 class="font-heading font-black text-lg text-black flex items-center gap-2">
                <span>➕</span> Tambah Guru Baru
            </h3>
            <button onclick="closeModal('createTeacherModal')" class="text-black font-black text-xl hover:opacity-75">✕</button>
        </div>

        <form action="{{ route('admin.teachers.store') }}" method="POST" class="space-y-3.5 text-xs">
            @csrf
            <div>
                <label class="block font-heading font-bold text-black mb-1">Nama Lengkap & Gelar *</label>
                <input type="text" name="name" required placeholder="Contoh: Ahmad Dahlan, S.Pd., M.Pd." class="w-full px-3 py-2 neo-input text-xs bg-slate-50 font-medium">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-heading font-bold text-black mb-1">NUPTK (Opsional)</label>
                    <input type="text" name="nuptk" placeholder="16 digit NUPTK..." class="w-full px-3 py-2 neo-input text-xs bg-slate-50 font-mono">
                </div>
                <div>
                    <label class="block font-heading font-bold text-black mb-1">NIP (Opsional)</label>
                    <input type="text" name="nip" placeholder="18 digit NIP..." class="w-full px-3 py-2 neo-input text-xs bg-slate-50 font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-heading font-bold text-black mb-1">Jenis Kelamin *</label>
                    <select name="gender" required class="w-full px-3 py-2 neo-input text-xs bg-slate-50 font-medium">
                        <option value="L">Laki-laki (L)</option>
                        <option value="P">Perempuan (P)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-heading font-bold text-black mb-1">Pendidikan Terakhir</label>
                    <select name="last_education" class="w-full px-3 py-2 neo-input text-xs bg-slate-50 font-medium">
                        <option value="">-- Pilih Jenjang --</option>
                        <option value="D3">D3</option>
                        <option value="D4/S1" selected>D4 / S1</option>
                        <option value="S2">S2</option>
                        <option value="S3">S3</option>
                        <option value="SMA/SMK">SMA / SMK / Sederajat</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-heading font-bold text-black mb-1">Tempat Lahir</label>
                    <input type="text" name="birth_place" placeholder="Kota / Kabupaten..." class="w-full px-3 py-2 neo-input text-xs bg-slate-50 font-medium">
                </div>
                <div>
                    <label class="block font-heading font-bold text-black mb-1">Tanggal Lahir</label>
                    <input type="date" name="birth_date" class="w-full px-3 py-2 neo-input text-xs bg-slate-50 font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-heading font-bold text-black mb-1">No. WhatsApp / HP</label>
                    <input type="text" name="phone" placeholder="Contoh: 081234567890" class="w-full px-3 py-2 neo-input text-xs bg-slate-50 font-mono">
                </div>
                <div>
                    <label class="block font-heading font-bold text-black mb-1">Kata Sandi (Opsional)</label>
                    <div class="relative">
                        <input type="password" id="create_password" name="password" placeholder="Default: password" class="w-full pl-3 pr-10 py-2 neo-input text-xs bg-slate-50 font-mono">
                        <button type="button" onclick="togglePasswordVisibility('create_password', 'create_eye_icon')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-500 hover:text-black focus:outline-none cursor-pointer" title="Tampilkan / Sembunyikan Password">
                            <span id="create_eye_icon">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="bg-[#FFF9DB] border-2 border-black p-2.5 text-[11px] text-slate-700">
                <span>ℹ️</span> <strong>Catatan Email:</strong> Email sengaja tidak diwajibkan di sini. Guru dapat mengisi email pribadi secara mandiri saat login pertama kali.
            </div>

            <div class="pt-3 border-t-2 border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('createTeacherModal')" class="neo-btn bg-white text-black px-4 py-2">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#20C997] text-black px-5 py-2 font-heading font-black">
                    Simpan Data Guru
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Guru -->
<div id="editTeacherModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-xl w-full p-6 space-y-4 relative max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <h3 class="font-heading font-black text-lg text-black flex items-center gap-2">
                <span>✏️</span> Edit Data Guru
            </h3>
            <button onclick="closeModal('editTeacherModal')" class="text-black font-black text-xl hover:opacity-75">✕</button>
        </div>

        <form id="editTeacherForm" method="POST" class="space-y-3.5 text-xs">
            @csrf
            @method('PUT')
            <div>
                <label class="block font-heading font-bold text-black mb-1">Nama Lengkap & Gelar *</label>
                <input type="text" id="edit_name" name="name" required class="w-full px-3 py-2 neo-input text-xs bg-slate-50 font-medium">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-heading font-bold text-black mb-1">NUPTK</label>
                    <input type="text" id="edit_nuptk" name="nuptk" placeholder="16 digit NUPTK..." class="w-full px-3 py-2 neo-input text-xs bg-slate-50 font-mono">
                </div>
                <div>
                    <label class="block font-heading font-bold text-black mb-1">NIP</label>
                    <input type="text" id="edit_nip" name="nip" placeholder="18 digit NIP..." class="w-full px-3 py-2 neo-input text-xs bg-slate-50 font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-heading font-bold text-black mb-1">Jenis Kelamin *</label>
                    <select id="edit_gender" name="gender" required class="w-full px-3 py-2 neo-input text-xs bg-slate-50 font-medium">
                        <option value="L">Laki-laki (L)</option>
                        <option value="P">Perempuan (P)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-heading font-bold text-black mb-1">Pendidikan Terakhir</label>
                    <select id="edit_last_education" name="last_education" class="w-full px-3 py-2 neo-input text-xs bg-slate-50 font-medium">
                        <option value="">-- Pilih Jenjang --</option>
                        <option value="D3">D3</option>
                        <option value="D4/S1">D4 / S1</option>
                        <option value="S2">S2</option>
                        <option value="S3">S3</option>
                        <option value="SMA/SMK">SMA / SMK / Sederajat</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-heading font-bold text-black mb-1">Tempat Lahir</label>
                    <input type="text" id="edit_birth_place" name="birth_place" class="w-full px-3 py-2 neo-input text-xs bg-slate-50 font-medium">
                </div>
                <div>
                    <label class="block font-heading font-bold text-black mb-1">Tanggal Lahir</label>
                    <input type="date" id="edit_birth_date" name="birth_date" class="w-full px-3 py-2 neo-input text-xs bg-slate-50 font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-heading font-bold text-black mb-1">No. WhatsApp / HP</label>
                    <input type="text" id="edit_phone" name="phone" class="w-full px-3 py-2 neo-input text-xs bg-slate-50 font-mono">
                </div>
                <div>
                    <label class="block font-heading font-bold text-black mb-1">Email (Bisa diisi jika diminta guru)</label>
                    <input type="email" id="edit_email" name="email" placeholder="email@domain.com" class="w-full px-3 py-2 neo-input text-xs bg-slate-50 font-mono">
                </div>
            </div>

            <div>
                <label class="block font-heading font-bold text-black mb-1">Reset Kata Sandi (Opsional)</label>
                <div class="relative">
                    <input type="password" id="edit_password" name="password" placeholder="Biarkan kosong jika tidak ingin mengubah password" class="w-full pl-3 pr-10 py-2 neo-input text-xs bg-slate-50 font-mono">
                    <button type="button" onclick="togglePasswordVisibility('edit_password', 'edit_eye_icon')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-500 hover:text-black focus:outline-none cursor-pointer" title="Tampilkan / Sembunyikan Password">
                        <span id="edit_eye_icon">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </span>
                    </button>
                </div>
            </div>

            <div class="pt-3 border-t-2 border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('editTeacherModal')" class="neo-btn bg-white text-black px-4 py-2">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#5294FF] text-white px-5 py-2 font-heading font-black">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Atur Penugasan Mengajar Guru -->
<div id="assignmentTeacherModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white neo-box max-w-2xl w-full p-6 my-8 max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between border-b-2 border-black pb-3 mb-4">
            <div>
                <h3 class="font-heading font-black text-lg text-black flex items-center gap-2">
                    <span class="w-3 h-3 bg-[#CC5DE8] border border-black inline-block"></span>
                    Atur Penugasan Mengajar Guru
                </h3>
                <p id="assignment_teacher_subtitle" class="text-xs text-slate-600 mt-0.5 font-medium">
                    Memuat data guru...
                </p>
            </div>
            <button type="button" onclick="closeModal('assignmentTeacherModal')" class="text-black font-black hover:text-rose-600 text-lg">✕</button>
        </div>

        <form id="assignmentTeacherForm" method="POST" class="flex-1 flex flex-col min-h-0 space-y-4">
            @csrf
            
            <!-- Info Status Wali Kelas Guru -->
            <div id="assignment_homeroom_info" class="p-3 bg-[#E7F5FF] border-2 border-black rounded text-xs flex items-center gap-2.5">
                <span class="text-base">👨‍🏫</span>
                <div>
                    <span class="font-bold text-blue-950">Status Peran Guru:</span>
                    <span id="assignment_homeroom_text" class="font-black text-black ml-1">-</span>
                </div>
            </div>

            <!-- Mode Selector -->
            <div class="border-2 border-black p-3 bg-slate-50 space-y-2">
                <label class="block text-xs font-black uppercase tracking-wider text-black">Aturan Pembatasan Mengajar:</label>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <label class="flex items-start gap-2.5 p-2.5 bg-white border border-black cursor-pointer hover:bg-emerald-50">
                        <input type="radio" name="mode" value="unrestricted" id="mode_unrestricted" onchange="toggleAssignmentMode()" class="mt-0.5 text-emerald-600 focus:ring-0">
                        <div>
                            <span class="block text-xs font-black text-black">Bebas (Fleksibel)</span>
                            <span class="block text-[11px] text-slate-500 font-medium">Bisa dijadwalkan mengajar mata pelajaran dan kelas apa saja tanpa batasan.</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-2.5 p-2.5 bg-white border border-black cursor-pointer hover:bg-purple-50">
                        <input type="radio" name="mode" value="restricted" id="mode_restricted" onchange="toggleAssignmentMode()" class="mt-0.5 text-purple-600 focus:ring-0">
                        <div>
                            <span class="block text-xs font-black text-black">Dibatasi Khusus</span>
                            <span class="block text-[11px] text-slate-500 font-medium">Hanya boleh mengajar mata pelajaran dan kelas yang ditentukan di bawah ini.</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Container Baris Penugasan Khusus -->
            <div id="restricted_container" class="flex-1 overflow-y-auto space-y-3 pr-1 min-h-[160px]">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-black uppercase text-black">Daftar Mata Pelajaran & Kelas yang Diajar:</label>
                    <button type="button" onclick="addAssignmentRow()" class="neo-btn bg-[#20C997] text-black px-2.5 py-1 text-xs font-bold flex items-center gap-1 cursor-pointer">
                        <span>+</span> Tambah Mapel Lain
                    </button>
                </div>

                <div id="assignment_rows" class="space-y-3">
                    <!-- Dynamic Rows inserted by JavaScript -->
                </div>
            </div>

            <div class="pt-3 border-t-2 border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('assignmentTeacherModal')" class="neo-btn bg-white text-black px-4 py-2 text-xs">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#5294FF] text-white px-5 py-2 text-xs font-heading font-black">
                    Simpan Penugasan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePasswordVisibility(inputId, iconContainerId) {
        const input = document.getElementById(inputId);
        const iconContainer = document.getElementById(iconContainerId);
        if (!input || !iconContainer) return;

        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';

        if (isPassword) {
            iconContainer.innerHTML = `
                <svg class="w-4 h-4 text-[#5294FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                </svg>
            `;
        } else {
            iconContainer.innerHTML = `
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            `;
        }
    }

    function editTeacher(data) {
        document.getElementById('edit_name').value = data.name || '';
        document.getElementById('edit_nuptk').value = data.nuptk || '';
        document.getElementById('edit_nip').value = data.nip || '';
        document.getElementById('edit_gender').value = data.gender || 'L';
        document.getElementById('edit_birth_place').value = data.birth_place || '';
        document.getElementById('edit_birth_date').value = data.birth_date || '';
        document.getElementById('edit_last_education').value = data.last_education || 'D4/S1';
        document.getElementById('edit_phone').value = data.phone || '';
        document.getElementById('edit_email').value = data.email || '';

        const editPasswordInput = document.getElementById('edit_password');
        if (editPasswordInput) {
            editPasswordInput.value = '';
            editPasswordInput.type = 'password';
            const iconContainer = document.getElementById('edit_eye_icon');
            if (iconContainer) {
                iconContainer.innerHTML = `
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                `;
            }
        }

        document.getElementById('editTeacherForm').action = `{{ url('admin/teachers') }}/${data.id}`;
        openModal('editTeacherModal');
    }

    // Data Master untuk modal Atur Mengajar
    const availableSubjects = @json($subjects ?? []);
    const availableClasses = @json($classes ?? []);
    let assignmentRowIndex = 0;

    function toggleAssignmentMode() {
        const isRestricted = document.getElementById('mode_restricted').checked;
        const container = document.getElementById('restricted_container');
        if (isRestricted) {
            container.classList.remove('hidden');
            if (document.querySelectorAll('.assignment-row-card').length === 0) {
                addAssignmentRow();
            }
        } else {
            container.classList.add('hidden');
        }
    }

    function addAssignmentRow(existingData = null) {
        const container = document.getElementById('assignment_rows');
        const idx = assignmentRowIndex++;

        let subjectOptions = '<option value="">-- Pilih Mata Pelajaran --</option>';
        availableSubjects.forEach(s => {
            const isSelected = (existingData && existingData.subject_id == s.id) ? 'selected' : '';
            subjectOptions += `<option value="${s.id}" ${isSelected}>${s.name} (${s.code || '-'})</option>`;
        });

        let classCheckboxes = '';
        availableClasses.forEach(c => {
            const isChecked = (existingData && existingData.class_ids && existingData.class_ids.includes(c.id)) ? 'checked' : '';
            classCheckboxes += `
                <label class="flex items-center gap-1.5 p-1.5 bg-slate-50 border border-slate-200 hover:border-black cursor-pointer text-xs">
                    <input type="checkbox" name="assignments[${idx}][class_ids][]" value="${c.id}" ${isChecked} class="class-checkbox-${idx} text-purple-600 focus:ring-0 rounded-xs">
                    <span class="font-medium text-black">${c.name}</span>
                </label>
            `;
        });

        const rowCard = document.createElement('div');
        rowCard.className = 'assignment-row-card border-2 border-black p-3 bg-white space-y-2.5';
        rowCard.id = `assignment_row_${idx}`;
        rowCard.innerHTML = `
            <div class="flex items-center justify-between gap-2">
                <div class="flex-1">
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Mata Pelajaran:</label>
                    <select name="assignments[${idx}][subject_id]" required class="w-full px-3 py-1.5 neo-input text-xs bg-slate-50 font-medium">
                        ${subjectOptions}
                    </select>
                </div>
                <button type="button" onclick="removeAssignmentRow(${idx})" class="neo-btn bg-[#FF6B6B] text-white px-2 py-1 text-xs self-end cursor-pointer font-bold" title="Hapus Mapel Ini">
                    Hapus
                </button>
            </div>
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="text-[11px] font-bold text-slate-700">Kelas / Rombel yang Diajar:</label>
                    <div class="flex items-center gap-2 text-[10px]">
                        <button type="button" onclick="toggleAllClasses(${idx}, true)" class="text-blue-700 hover:underline font-bold">Pilih Semua</button>
                        <span>|</span>
                        <button type="button" onclick="toggleAllClasses(${idx}, false)" class="text-slate-600 hover:underline">Batalkan Semua</button>
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-1.5 max-h-36 overflow-y-auto p-1 bg-slate-100 border border-slate-300">
                    ${classCheckboxes}
                </div>
            </div>
        `;
        container.appendChild(rowCard);
    }

    function removeAssignmentRow(idx) {
        const el = document.getElementById(`assignment_row_${idx}`);
        if (el) el.remove();
    }

    function toggleAllClasses(rowIdx, check) {
        document.querySelectorAll(`.class-checkbox-${rowIdx}`).forEach(cb => cb.checked = check);
    }

    function openAssignmentModal(teacherId, teacherName) {
        const form = document.getElementById('assignmentTeacherForm');
        form.action = `{{ url('admin/teachers') }}/${teacherId}/assignments`;

        document.getElementById('assignment_teacher_subtitle').textContent = `Mengatur penugasan mata pelajaran & rombel untuk ${teacherName}`;
        document.getElementById('assignment_rows').innerHTML = '';
        assignmentRowIndex = 0;

        document.getElementById('assignment_homeroom_text').textContent = 'Memuat status...';
        document.getElementById('mode_unrestricted').checked = true;
        toggleAssignmentMode();

        openModal('assignmentTeacherModal');

        fetch(`{{ url('admin/teachers') }}/${teacherId}/assignments`)
            .then(res => res.json())
            .then(data => {
                if (data.teacher) {
                    if (data.teacher.is_homeroom) {
                        document.getElementById('assignment_homeroom_text').textContent = `Wali Kelas di ${data.teacher.homeroom_classes.join(', ')}`;
                    } else {
                        document.getElementById('assignment_homeroom_text').textContent = 'Guru Mata Pelajaran (Bukan Wali Kelas)';
                    }
                }

                if (data.has_restrictions && data.assignments && data.assignments.length > 0) {
                    document.getElementById('mode_restricted').checked = true;
                    toggleAssignmentMode();
                    document.getElementById('assignment_rows').innerHTML = '';
                    data.assignments.forEach(item => {
                        addAssignmentRow(item);
                    });
                } else {
                    document.getElementById('mode_unrestricted').checked = true;
                    toggleAssignmentMode();
                }
            })
            .catch(err => {
                console.error('Error loading assignments:', err);
                document.getElementById('assignment_homeroom_text').textContent = 'Gagal memuat status';
            });
    }
</script>
@endpush
