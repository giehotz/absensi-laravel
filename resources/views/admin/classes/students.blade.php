@extends('layouts.admin')

@section('title', 'Daftar Siswa Kelas ' . $class->name)
@section('page-title', 'Rombel: ' . $class->name)

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Back Action -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.classes.index') }}" class="neo-btn bg-white hover:bg-slate-100 text-black px-3.5 py-2 text-xs uppercase flex items-center gap-2 cursor-pointer font-heading">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali ke Daftar Kelas</span>
        </a>

        <div class="flex items-center gap-2">
            <span class="neo-badge bg-[#E7F5FF] text-blue-900 font-mono text-xs">
                T.A: {{ $class->academicYear->name ?? '-' }} ({{ ucfirst($class->academicYear->semester ?? '') }})
            </span>
        </div>
    </div>

    <!-- Class Header & Info Banner -->
    <div class="bg-[#FFF9DB] neo-box-lg p-6 sm:p-7 relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div class="space-y-2 z-10">
            <div class="flex items-center gap-2">
                <span class="neo-badge bg-[#5294FF] text-white">{{ $class->level }}</span>
                <span class="text-xs font-mono font-bold bg-white px-2 py-0.5 border border-black text-black">
                    Wali Kelas: {{ $class->homeroomTeacher->user->name ?? 'Belum Ditentukan' }}
                </span>
            </div>
            <h1 class="font-heading text-2xl sm:text-3xl font-black text-black tracking-tight">
                {{ $class->name }}
            </h1>
            <p class="text-xs font-semibold text-slate-700 max-w-xl">
                Daftar siswa resmi terdaftar pada rombel ini. Anda dapat menginput siswa satu-per-satu atau mengimpor data sekaligus dari Excel.
            </p>
        </div>

        <!-- Quick Statistics Badges -->
        <div class="flex flex-wrap gap-3 z-10">
            <div class="bg-white text-black neo-box px-4 py-2.5 text-center min-w-[90px]">
                <div class="text-[10px] uppercase font-bold text-slate-500">Total Siswa</div>
                <div class="text-xl font-heading font-black text-black">{{ $studentsTotal }}</div>
            </div>
            <div class="bg-[#D0EBFF] text-blue-950 neo-box px-3.5 py-2.5 text-center min-w-[75px]">
                <div class="text-[10px] uppercase font-bold text-blue-800">Laki-laki</div>
                <div class="text-xl font-heading font-black text-blue-950">{{ $maleCount }}</div>
            </div>
            <div class="bg-[#FCC2D7] text-rose-950 neo-box px-3.5 py-2.5 text-center min-w-[75px]">
                <div class="text-[10px] uppercase font-bold text-rose-800">Perempuan</div>
                <div class="text-xl font-heading font-black text-rose-950">{{ $femaleCount }}</div>
            </div>
        </div>
    </div>

    <!-- Action Bar & Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white neo-box p-4">
        <div class="flex items-center gap-2">
            <span class="text-sm font-bold text-black font-heading flex items-center gap-1.5">
                <span>👥</span> Daftar Anggota Rombel
            </span>
            <span class="neo-badge bg-slate-100 text-black text-[10px] font-mono">
                {{ $students->count() }} Data
            </span>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Unduh Template Excel -->
            <a href="{{ route('admin.classes.students.template', $class) }}" class="neo-btn bg-[#FFF9DB] hover:bg-[#ffec99] text-black px-3.5 py-2 text-xs uppercase flex items-center gap-1.5 cursor-pointer font-heading" title="Unduh Template Excel Khusus Kelas Ini">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span>Unduh Template</span>
            </a>

            <!-- Upload Excel Siswa -->
            <button onclick="openModal('importStudentModal')" class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-3.5 py-2 text-xs uppercase flex items-center gap-1.5 cursor-pointer font-heading">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <span>Upload Excel</span>
            </button>

            <!-- Tambah Siswa Manual -->
            <button onclick="openModal('createStudentModal')" class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black px-4 py-2 text-xs uppercase flex items-center gap-1.5 cursor-pointer font-heading">
                <span>+</span> Tambah Siswa Manual
            </button>
        </div>
    </div>

    <!-- Student Table -->
    <div class="bg-white neo-box overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-[#FFF9DB] border-b-2 border-black text-xs font-black uppercase tracking-wider">
                    <tr>
                        <th class="p-3.5 border-r border-black">No</th>
                        <th class="p-3.5 border-r border-black">NIS / NISN</th>
                        <th class="p-3.5 border-r border-black">Nama Siswa & Email Akun</th>
                        <th class="p-3.5 border-r border-black text-center">L/P</th>
                        <th class="p-3.5 border-r border-black">No. Kontak</th>
                        <th class="p-3.5 border-r border-black">QR Identifier</th>
                        <th class="p-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black">
                    @forelse($students as $index => $student)
                    <tr class="hover:bg-slate-50 font-medium">
                        <td class="p-3.5 font-bold text-center border-r border-black">
                            {{ $loop->iteration }}
                        </td>
                        <td class="p-3.5 border-r border-black font-mono text-xs">
                            <span class="font-bold text-black">{{ $student->nis }}</span>
                            @if($student->nisn)
                                <span class="text-slate-400 block text-[11px]">{{ $student->nisn }}</span>
                            @endif
                        </td>
                        <td class="p-3.5 border-r border-black">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 border-2 border-black overflow-hidden shrink-0 bg-slate-100 flex items-center justify-center font-bold text-xs shadow-[1px_1px_0px_0px_#000]">
                                    @if($student->photo)
                                        <img src="{{ $student->photo_url }}" alt="{{ $student->user->name ?? 'Siswa' }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-slate-500 font-mono text-[10px]">{{ strtoupper(substr($student->user->name ?? 'S', 0, 2)) }}</span>
                                    @endif
                                </div>
                                <div>
                                    <div class="font-bold text-black">{{ $student->user->name ?? '-' }}</div>
                                    <div class="text-xs text-slate-500 font-mono">{{ $student->user->email ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-3.5 border-r border-black text-center">
                            @if($student->gender === 'L')
                                <span class="neo-badge bg-[#D0EBFF] text-blue-900 text-[10px]">Laki-laki</span>
                            @else
                                <span class="neo-badge bg-[#FCC2D7] text-rose-900 text-[10px]">Perempuan</span>
                            @endif
                        </td>
                        <td class="p-3.5 border-r border-black font-mono text-xs">
                            {{ $student->phone ?? '-' }}
                        </td>
                        <td class="p-3.5 border-r border-black">
                            <button onclick="previewQr({{ json_encode([
                                'name' => $student->user->name ?? '-',
                                'nis' => $student->nis,
                                'class' => $class->name,
                                'qr' => $student->qr_code_identifier,
                            ]) }})" class="neo-badge bg-black text-white hover:bg-slate-800 cursor-pointer font-mono text-[10px] flex items-center gap-1.5 w-fit">
                                <span>📱</span>
                                <span class="truncate max-w-[130px]">{{ $student->qr_code_identifier }}</span>
                            </button>
                        </td>
                        <td class="p-3.5 text-center">
                            <form action="{{ route('admin.students.destroy', $student) }}" method="POST" onsubmit="return confirm('Hapus siswa {{ $student->user->name ?? '' }} dari kelas dan sistem?')">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="redirect_to" value="{{ route('admin.classes.students', $class) }}">
                                <button type="submit" class="neo-btn bg-[#FF6B6B] hover:bg-[#ff5252] text-white px-2.5 py-1 text-xs cursor-pointer">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-500 font-semibold space-y-2">
                            <div class="text-3xl">📭</div>
                            <div class="font-bold text-black">Belum ada siswa di kelas {{ $class->name }}</div>
                            <div class="text-xs text-slate-500">Silakan tambahkan siswa manual atau upload template file Excel.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>


    </div>
</div>

<!-- Modal Tambah Siswa Manual -->
<div id="createStudentModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-lg w-full p-6 space-y-5 relative max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <div>
                <h3 class="font-heading font-black text-lg text-black flex items-center gap-2">
                    <span>➕</span> Tambah Siswa ke Kelas {{ $class->name }}
                </h3>
                <p class="text-xs font-semibold text-slate-600 mt-0.5">
                    Data siswa dan QR Code absensi akan dibuat otomatis.
                </p>
            </div>
            <button onclick="closeModal('createStudentModal')" class="text-black font-black text-xl hover:opacity-75 cursor-pointer">✕</button>
        </div>

        <form action="{{ route('admin.students.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" name="school_class_id" value="{{ $class->id }}">
            <input type="hidden" name="redirect_to" value="{{ route('admin.classes.students', $class) }}">

            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Nama Lengkap Siswa *</label>
                <input type="text" name="name" required placeholder="Contoh: Muhammad Rizki" class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
            </div>

            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Foto Siswa (Opsional)</label>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-14 border-2 border-black bg-slate-100 flex items-center justify-center overflow-hidden shrink-0 shadow-[2px_2px_0px_0px_#000]">
                        <span id="class_student_photo_placeholder" class="text-[9px] text-slate-400 font-bold uppercase">3×4</span>
                        <img id="class_student_photo_preview" src="" alt="Preview" class="w-full h-full object-cover hidden">
                    </div>
                    <div class="flex-1">
                        <input type="file" name="photo" id="class_student_photo_input" accept="image/jpeg,image/png,image/jpg,image/webp" class="w-full text-xs file:mr-3 file:py-1.5 file:px-3 file:border-2 file:border-black file:text-xs file:font-heading file:font-bold file:bg-[#FFD43B] file:text-black hover:file:bg-[#fcc419] cursor-pointer" onchange="previewClassStudentImage(this)">
                        <span class="text-[10px] text-slate-500 font-semibold block mt-0.5">Format: JPG, PNG, WEBP. Maks 2MB.</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">NIS (Nomor Induk Siswa) *</label>
                    <input type="text" name="nis" required placeholder="Contoh: 10291" class="w-full px-3 py-2 neo-input text-sm bg-slate-50 font-mono">
                </div>
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">NISN (Opsional)</label>
                    <input type="text" name="nisn" placeholder="0081234567" class="w-full px-3 py-2 neo-input text-sm bg-slate-50 font-mono">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">Jenis Kelamin *</label>
                    <select name="gender" required class="w-full px-3 py-2 neo-input text-sm bg-slate-50 font-medium">
                        <option value="L">Laki-laki (L)</option>
                        <option value="P">Perempuan (P)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">Tanggal Lahir</label>
                    <input type="date" name="birth_date" class="w-full px-3 py-2 neo-input text-sm bg-slate-50 font-medium">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">No. WhatsApp / HP</label>
                    <input type="text" name="phone" placeholder="08..." class="w-full px-3 py-2 neo-input text-sm bg-slate-50 font-mono">
                </div>
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">Email Akun (Opsional)</label>
                    <input type="email" name="email" placeholder="Otomatis jika kosong" class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
                </div>
            </div>

            <div class="pt-3 border-t-2 border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('createStudentModal')" class="neo-btn bg-white text-black px-4 py-2 text-xs cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black px-5 py-2 text-xs font-heading cursor-pointer">
                    Simpan Data Siswa
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Upload Excel Siswa -->
<div id="importStudentModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-lg w-full p-6 space-y-5 relative">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <h3 class="font-heading font-black text-lg text-black flex items-center gap-2">
                <span>📑</span> Import Siswa ke Kelas {{ $class->name }}
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
                <a href="{{ route('admin.classes.students.template', $class) }}" class="inline-flex items-center gap-1.5 text-blue-700 font-bold underline hover:text-blue-900">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Unduh file template siswa {{ $class->name }} (.xlsx)
                </a>
            </div>
        </div>

        <form action="{{ route('admin.classes.students.import', $class) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
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

            <div class="bg-[#FFF9DB] border-2 border-black p-3 flex items-start gap-2.5">
                <input type="checkbox" name="upsert" id="class_student_upsert_check" value="1" checked class="mt-0.5 border-2 border-black text-black focus:ring-0 cursor-pointer">
                <label for="class_student_upsert_check" class="text-xs font-semibold text-black cursor-pointer leading-tight">
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

<!-- Modal Preview Kartu QR Siswa -->
<div id="qrPreviewModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-sm w-full p-6 space-y-5 text-center relative">
        <div class="flex items-center justify-between border-b-2 border-black pb-2">
            <span class="neo-badge bg-[#FFD43B] text-black">KARTU ABSENSI</span>
            <button onclick="closeModal('qrPreviewModal')" class="text-black font-black text-xl hover:opacity-75 cursor-pointer">✕</button>
        </div>

        <div id="printableCard" class="bg-[#FFF9DB] neo-box p-5 space-y-3">
            <div class="w-12 h-12 bg-[#5294FF] text-white border-2 border-black mx-auto flex items-center justify-center font-heading font-black text-xl neo-box-sm">
                A
            </div>
            <div>
                <h4 id="qr_preview_name" class="font-heading font-black text-base text-black">Nama Siswa</h4>
                <p id="qr_preview_details" class="text-xs font-bold text-slate-700">Kelas • NIS: 12345</p>
            </div>

            <div class="bg-white border-2 border-black p-3 inline-block shadow-[2px_2px_0px_0px_#000]">
                <svg class="w-28 h-28 mx-auto" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="100" height="100" fill="white"/>
                    <rect x="10" y="10" width="25" height="25" fill="black"/>
                    <rect x="15" y="15" width="15" height="15" fill="white"/>
                    <rect x="18" y="18" width="9" height="9" fill="black"/>
                    <rect x="65" y="10" width="25" height="25" fill="black"/>
                    <rect x="70" y="15" width="15" height="15" fill="white"/>
                    <rect x="73" y="18" width="9" height="9" fill="black"/>
                    <rect x="10" y="65" width="25" height="25" fill="black"/>
                    <rect x="15" y="70" width="15" height="15" fill="white"/>
                    <rect x="18" y="73" width="9" height="9" fill="black"/>
                    <rect x="42" y="12" width="6" height="18" fill="black"/>
                    <rect x="42" y="38" width="18" height="6" fill="black"/>
                    <rect x="65" y="42" width="12" height="12" fill="black"/>
                    <rect x="42" y="65" width="18" height="18" fill="black"/>
                    <rect x="70" y="70" width="15" height="15" fill="black"/>
                </svg>
            </div>

            <div id="qr_preview_code" class="text-[11px] font-mono font-bold text-black break-all">
                QR-IDENTIFIER
            </div>
        </div>

        <div class="flex items-center justify-center gap-2 pt-2">
            <button onclick="window.print()" class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black px-4 py-2 text-xs font-heading cursor-pointer">
                🖨️ Cetak Kartu
            </button>
            <button onclick="closeModal('qrPreviewModal')" class="neo-btn bg-white text-black px-4 py-2 text-xs cursor-pointer">
                Tutup
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function previewClassStudentImage(input) {
        const previewImg = document.getElementById('class_student_photo_preview');
        const placeholder = document.getElementById('class_student_photo_placeholder');
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

    function previewQr(data) {
        document.getElementById('qr_preview_name').innerText = data.name;
        document.getElementById('qr_preview_details').innerText = data.class + ' • NIS: ' + data.nis;
        document.getElementById('qr_preview_code').innerText = data.qr;
        openModal('qrPreviewModal');
    }
</script>
@endpush
@endsection
