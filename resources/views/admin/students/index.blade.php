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
            <p class="text-xs font-semibold text-slate-600 mt-1">
                Kelola data profil siswa, generate identifikasi QR unik, dan cetak kartu absensi.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Filter Kelas -->
            <form method="GET" action="{{ route('admin.students.index') }}" class="flex items-center gap-2">
                <select name="class_id" onchange="this.form.submit()" class="px-3 py-2 neo-input text-xs bg-[#FFF9DB] font-bold">
                    <option value="">-- Semua Kelas --</option>
                    @foreach($classes as $cls)
                        <option value="{{ $cls->id }}" {{ $selectedClassId == $cls->id ? 'selected' : '' }}>
                            {{ $cls->name }}
                        </option>
                    @endforeach
                </select>
            </form>

            <a href="{{ route('admin.students.cards', ['class_id' => $selectedClassId]) }}" 
               class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-4 py-2.5 text-xs uppercase flex items-center gap-2 cursor-pointer font-heading shadow-[2px_2px_0px_#000]">
                <span>🪪</span> Studio Cetak Kartu
            </a>

            <button onclick="openModal('createStudentModal')" class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black px-4 py-2.5 text-xs uppercase flex items-center gap-2 cursor-pointer font-heading">
                <span>+</span> Tambah Siswa Baru
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
                        <th class="p-3.5 border-r border-black">NIS / NISN</th>
                        <th class="p-3.5 border-r border-black">Nama Siswa & Email</th>
                        <th class="p-3.5 border-r border-black">Kelas</th>
                        <th class="p-3.5 border-r border-black">L/P</th>
                        <th class="p-3.5 border-r border-black">QR Identifier</th>
                        <th class="p-3.5 text-center">Aksi</th>
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
                            <div class="flex items-center justify-center gap-2">
                                <!-- Tombol Preview Kartu QR -->
                                <button onclick="previewQr({{ json_encode([
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
                                ]) }})" class="neo-btn bg-[#20C997] text-black px-2.5 py-1 text-xs cursor-pointer flex items-center gap-1">
                                    <span>🪪</span> QR
                                </button>

                                <button onclick="editStudent({{ json_encode([
                                    'id' => $student->id,
                                    'name' => $student->user->name ?? '',
                                    'email' => $student->user->email ?? '',
                                    'nis' => $student->nis,
                                    'nisn' => $student->nisn ?? '',
                                    'school_class_id' => $student->school_class_id,
                                    'gender' => $student->gender,
                                    'birth_date' => $student->birth_date ? $student->birth_date->format('Y-m-d') : '',
                                    'phone' => $student->phone ?? '',
                                    'photo_url' => $student->photo ? $student->photo_url : null,
                                ]) }})" class="neo-btn bg-[#FFD43B] text-black px-2.5 py-1 text-xs cursor-pointer">
                                    Edit
                                </button>

                                <form action="{{ route('admin.students.destroy', $student) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data siswa ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="neo-btn bg-[#FF6B6B] text-white px-2.5 py-1 text-xs cursor-pointer">
                                        Hapus
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

        @if($students->hasPages())
        <div class="p-4 border-t-2 border-black bg-slate-50">
            {{ $students->links() }}
        </div>
        @endif
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

<!-- Modal Edit Siswa -->
<div id="editStudentModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-lg w-full p-6 space-y-5 relative max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <h3 class="font-heading font-black text-lg text-black flex items-center gap-2">
                <span>✏️</span> Edit Data Siswa
            </h3>
            <button onclick="closeModal('editStudentModal')" class="text-black font-black text-xl hover:opacity-75">✕</button>
        </div>

        <form id="editStudentForm" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Nama Lengkap Siswa *</label>
                <input type="text" id="edit_stu_name" name="name" required class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
            </div>

            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Foto Siswa</label>
                <div class="flex items-start gap-3">
                    <div id="edit_photo_preview_container" class="w-14 h-16 border-2 border-black bg-slate-100 flex items-center justify-center overflow-hidden shrink-0 shadow-[2px_2px_0px_0px_#000]">
                        <span id="edit_photo_placeholder" class="text-[10px] text-slate-400 font-bold uppercase">Foto 3×4</span>
                        <img id="edit_photo_preview" src="" alt="Foto Siswa" class="w-full h-full object-cover hidden">
                    </div>
                    <div class="flex-1 space-y-1.5">
                        <input type="file" name="photo" id="edit_photo_input" accept="image/jpeg,image/png,image/jpg,image/webp" class="w-full text-xs file:mr-3 file:py-1.5 file:px-3 file:border-2 file:border-black file:text-xs file:font-heading file:font-bold file:bg-[#FFD43B] file:text-black hover:file:bg-[#fcc419] cursor-pointer" onchange="previewImage(this, 'edit_photo_preview', 'edit_photo_placeholder')">
                        <div id="remove_photo_wrapper" class="hidden items-center gap-2 pt-0.5">
                            <label class="inline-flex items-center gap-1.5 text-xs font-bold text-red-600 cursor-pointer">
                                <input type="checkbox" name="remove_photo" id="edit_remove_photo" value="1" class="accent-red-600 w-3.5 h-3.5">
                                Hapus foto siswa saat ini
                            </label>
                        </div>
                        <span class="text-[11px] text-slate-500 font-semibold block">Pilih file baru jika ingin mengganti foto saat ini (JPG, PNG, WEBP. Maks 2MB).</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">NIS *</label>
                    <input type="text" id="edit_stu_nis" name="nis" required class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
                </div>
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">NISN</label>
                    <input type="text" id="edit_stu_nisn" name="nisn" class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">Kelas *</label>
                    <select id="edit_stu_class" name="school_class_id" required class="w-full px-3 py-2 neo-input text-sm bg-slate-50 font-medium">
                        @foreach($classes as $cls)
                            <option value="{{ $cls->id }}">{{ $cls->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">Jenis Kelamin *</label>
                    <select id="edit_stu_gender" name="gender" required class="w-full px-3 py-2 neo-input text-sm bg-slate-50 font-medium">
                        <option value="L">Laki-laki (L)</option>
                        <option value="P">Perempuan (P)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">Tanggal Lahir</label>
                    <input type="date" id="edit_stu_birth" name="birth_date" class="w-full px-3 py-2 neo-input text-sm bg-slate-50 font-medium">
                </div>
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">No. Kontak Siswa</label>
                    <input type="text" id="edit_stu_phone" name="phone" class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
                </div>
            </div>

            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Alamat Email</label>
                <input type="email" id="edit_stu_email" name="email" class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
            </div>

            <div class="pt-3 border-t-2 border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('editStudentModal')" class="neo-btn bg-white text-black px-4 py-2 text-xs">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#5294FF] text-white px-5 py-2 text-xs font-heading">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

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

    function editStudent(data) {
        document.getElementById('edit_stu_name').value = data.name;
        document.getElementById('edit_stu_nis').value = data.nis;
        document.getElementById('edit_stu_nisn').value = data.nisn || '';
        document.getElementById('edit_stu_class').value = data.school_class_id;
        document.getElementById('edit_stu_gender').value = data.gender;
        document.getElementById('edit_stu_birth').value = data.birth_date || '';
        document.getElementById('edit_stu_phone').value = data.phone || '';
        document.getElementById('edit_stu_email').value = data.email || '';
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
</script>
@endpush
@endsection
