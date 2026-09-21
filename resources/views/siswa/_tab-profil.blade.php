@push('styles')
<style>
@media print {
    body * {
        visibility: hidden !important;
    }
    #studentCardPrintArea, #studentCardPrintArea * {
        visibility: visible !important;
    }
    #studentCardPrintArea {
        position: fixed !important;
        left: 50% !important;
        top: 20% !important;
        transform: translate(-50%, 0) !important;
        margin: 0 !important;
        box-shadow: none !important;
        border: 2px solid #000 !important;
        width: 100% !important;
        max-width: 450px !important;
    }
}
</style>
@endpush

<!-- ========================================================================= -->
<!-- TAB 7: PROFIL SISWA LENGKAP (STANDAR DATA EMIS / SIMPATIKA) -->
<!-- ========================================================================= -->
<div id="tabContent-profil" class="tab-pane hidden space-y-4">
    <!-- Sub-Tab Navigation Header -->
    <div class="bg-white neo-box p-3">
        <div class="text-[10px] font-black uppercase text-slate-500 mb-2 px-1 flex items-center justify-between">
            <span>MENU PROFIL SISWA</span>
            <span class="text-black font-mono">EMIS GTK VERIFIED</span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-1.5">
            <button type="button" onclick="switchProfileSubTab('card')" id="btnProfileSubTab-card"
                    class="profile-subtab-btn bg-[#FFD43B] text-black border-2 border-black p-2 rounded text-xs font-black flex items-center justify-center gap-1.5 shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                <span>🪪</span> <span class="truncate">Kartu Pelajar</span>
            </button>
            <button type="button" onclick="switchProfileSubTab('biodata')" id="btnProfileSubTab-biodata"
                    class="profile-subtab-btn bg-slate-100 hover:bg-slate-200 text-slate-700 border-2 border-transparent p-2 rounded text-xs font-bold flex items-center justify-center gap-1.5 cursor-pointer">
                <span>👤</span> <span class="truncate">Biodata Diri</span>
            </button>
            <button type="button" onclick="switchProfileSubTab('ortu')" id="btnProfileSubTab-ortu"
                    class="profile-subtab-btn bg-slate-100 hover:bg-slate-200 text-slate-700 border-2 border-transparent p-2 rounded text-xs font-bold flex items-center justify-center gap-1.5 cursor-pointer">
                <span>👨‍👩‍👦</span> <span class="truncate">Orang Tua / Wali</span>
            </button>
            <button type="button" onclick="switchProfileSubTab('keamanan')" id="btnProfileSubTab-keamanan"
                    class="profile-subtab-btn bg-slate-100 hover:bg-slate-200 text-slate-700 border-2 border-transparent p-2 rounded text-xs font-bold flex items-center justify-center gap-1.5 cursor-pointer">
                <span>🔐</span> <span class="truncate">Keamanan Akun</span>
            </button>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- SUB-TAB 1: KARTU PELAJAR DIGITAL (KTP / KTS) RESMI -->
    <!-- ========================================================================= -->
    <div id="profileSubPane-card" class="profile-sub-pane space-y-4">
        <!-- Visualisasi Kartu Siswa -->
        <div class="bg-white neo-box p-4 sm:p-5 space-y-4">
            <div class="border-b-2 border-black pb-2.5 flex items-center justify-between">
                <div>
                    <h2 class="font-heading font-black text-sm uppercase text-black flex items-center gap-1.5">
                        <span>🪪</span> KARTU TANDA PELAJAR & PRESENSI DIGITAL
                    </h2>
                    <p class="text-[11px] font-semibold text-slate-600">
                        Kartu identitas resmi siswa yang dilengkapi kode QR presensi terenkripsi.
                    </p>
                </div>
                <button type="button" onclick="printStudentCardOnly()" class="neo-btn bg-[#20C997] hover:bg-emerald-400 text-black text-xs font-black px-3.5 py-1.5 flex items-center gap-1.5 shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                    <span>🖨️</span> Cetak Kartu
                </button>
            </div>

            <!-- Kartu Pelajar Resmi Standar Studio -->
            <div id="studentCardPrintArea" class="max-w-md mx-auto bg-white border-3 border-black rounded-lg shadow-[4px_4px_0px_0px_#000] overflow-hidden relative">
                <!-- Watermark Background -->
                <div class="absolute -right-4 -bottom-6 opacity-5 pointer-events-none select-none font-heading font-black text-7xl text-black">
                    STUDENT
                </div>

                <!-- Header Kartu -->
                <div class="bg-white p-3 border-b-2 border-black flex items-center gap-2.5">
                    @if(!empty($schoolSetting->logo) && \Illuminate\Support\Facades\Storage::disk('public')->exists($schoolSetting->logo))
                        <div class="w-10 h-10 bg-white border-2 border-black rounded p-0.5 shrink-0 flex items-center justify-center">
                            <img src="{{ asset('storage/' . $schoolSetting->logo) }}" alt="Logo" class="w-full h-full object-contain">
                        </div>
                    @else
                        <div class="w-10 h-10 bg-[#FFD43B] border-2 border-black rounded flex items-center justify-center font-heading font-black text-lg shrink-0">
                            🏫
                        </div>
                    @endif
                    <div class="min-w-0 flex-1 leading-tight">
                        <div class="font-heading font-black text-xs uppercase text-black tracking-tight truncate">
                            {{ $schoolSetting->school_name ?? 'SISTEM ABSENSI SEKOLAH' }}
                        </div>
                        <div class="text-[9px] text-slate-600 font-semibold truncate">
                            {{ $schoolSetting->address ?? 'Pusat Layanan Pendidikan & Presensi Terpadu' }}
                        </div>
                    </div>
                </div>

                <!-- Pita Sub-Header -->
                <div class="bg-[#FFD43B] text-black border-b-2 border-black text-center py-0.5 font-heading font-black text-[9px] uppercase tracking-widest">
                    KARTU TANDA PELAJAR & PRESENSI DIGITAL
                </div>

                <!-- Konten Kartu: Foto, Identitas, & Real QR Code -->
                <div class="p-3.5 flex items-center justify-between gap-3 bg-gradient-to-br from-white to-slate-50 min-h-[135px]">
                    <!-- Pas Foto Siswa -->
                    <div class="w-20 h-24 bg-slate-100 border-2 border-black rounded shadow-[2px_2px_0px_0px_#000] flex flex-col items-center justify-center shrink-0 relative overflow-hidden">
                        @if(!empty($student->photo) && \Illuminate\Support\Facades\Storage::disk('public')->exists($student->photo))
                            <img src="{{ asset('storage/' . $student->photo) }}" alt="{{ $student->user->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="flex flex-col items-center justify-center text-slate-400">
                                <span class="text-2xl">👤</span>
                                <span class="text-[8px] font-bold uppercase mt-0.5">FOTO 3×4</span>
                            </div>
                        @endif
                        <span class="absolute bottom-0 inset-x-0 bg-black text-white text-[7px] font-black text-center py-0.5 uppercase tracking-wider">
                            {{ $student->gender === 'P' ? 'PEREMPUAN' : 'LAKI-LAKI' }}
                        </span>
                    </div>

                    <!-- Data Diri Siswa -->
                    <div class="flex-1 min-w-0 space-y-1 text-xs">
                        <div class="font-heading font-black text-xs sm:text-sm text-black truncate leading-snug">
                            {{ $student->user->name }}
                        </div>
                        <div class="space-y-0.5 text-[10px] text-slate-700 font-medium">
                            <div class="flex items-center gap-1.5">
                                <span class="w-12 text-slate-500 font-bold shrink-0">NIS</span>
                                <span class="font-mono font-bold text-black">: {{ $student->nis }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-12 text-slate-500 font-bold shrink-0">NISN</span>
                                <span class="font-mono font-bold text-black">: {{ $student->nisn ?: '-' }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-12 text-slate-500 font-bold shrink-0">Kelas</span>
                                <span class="font-bold text-black">: {{ $student->schoolClass->name ?? '-' }} ({{ $student->schoolClass->level ?? '-' }})</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-12 text-slate-500 font-bold shrink-0">Lahir</span>
                                <span class="text-black truncate">: {{ $student->birth_place ?: '-' }}, {{ $student->birth_date ? $student->birth_date->translatedFormat('d F Y') : '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- QR Code Presensi Resmi -->
                    <div class="flex flex-col items-center justify-center shrink-0">
                        <div class="bg-white p-1 border-2 border-black rounded shadow-[2px_2px_0px_0px_#000]">
                            <img src="{{ $student->qr_data_uri }}" alt="QR Presensi" class="w-18 h-18 sm:w-20 sm:h-20 object-contain">
                        </div>
                        <span class="text-[8px] font-black uppercase text-black mt-1 tracking-wider">
                            SCAN ABSEN
                        </span>
                    </div>
                </div>

                <!-- Footer Kartu -->
                <div class="bg-slate-100 px-3 py-1.5 border-t-2 border-black flex items-center justify-between text-[9px] font-bold text-slate-700">
                    <span>Berlaku Selama Menjadi Siswa Aktif</span>
                    <span class="font-mono text-black">{{ $student->schoolClass->academicYear->name ?? '2026/2027' }}</span>
                </div>
            </div>

            <!-- Petunjuk Penggunaan Kartu -->
            <div class="bg-[#FFF9DB] border-2 border-black p-3 rounded text-xs space-y-1">
                <span class="font-black text-black flex items-center gap-1">
                    <span>💡</span> Petunjuk Kartu Pelajar Digital:
                </span>
                <p class="text-[11px] text-slate-700 leading-tight">
                    Tunjukkan kartu pelajar ini atau barcode di dalamnya ke scanner presensi gerbang sekolah. Anda juga dapat mencetak kartu dalam ukuran pas dompet menggunakan tombol <b>"Cetak Kartu"</b> di atas.
                </p>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- SUB-TAB 2: BIODATA LENGKAP SISWA (EMIS GTK VERIFIED) -->
    <!-- ========================================================================= -->
    <div id="profileSubPane-biodata" class="profile-sub-pane hidden space-y-4">
        <!-- Form Pembaruan Kontak & Foto Mandiri -->
        <div class="bg-white neo-box p-4 sm:p-5 space-y-4">
            <div class="border-b-2 border-black pb-2.5 flex items-center justify-between">
                <div>
                    <h2 class="font-heading font-black text-sm uppercase text-black flex items-center gap-1.5">
                        <span>✏️</span> Pengaturan Foto & Kontak Pribadi
                    </h2>
                    <p class="text-[11px] font-semibold text-slate-600">
                        Perbarui foto profil akun dan nomor kontak telepon aktif Anda.
                    </p>
                </div>
            </div>

            <form action="{{ route('siswa.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')

                <!-- Foto Profil Siswa -->
                <div>
                    <label class="block text-xs font-black uppercase text-black mb-1.5">
                        Foto Profil Siswa
                    </label>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 bg-slate-50 border-2 border-black p-3 rounded">
                        <div class="w-16 h-20 bg-white border-2 border-black rounded overflow-hidden flex items-center justify-center shrink-0 shadow-[2px_2px_0px_0px_#000]">
                            <img id="siswaPhotoPreview" 
                                 src="{{ !empty($student->photo) && \Illuminate\Support\Facades\Storage::disk('public')->exists($student->photo) ? asset('storage/' . $student->photo) : '' }}" 
                                 alt="Pratinjau" 
                                 class="w-full h-full object-cover {{ !empty($student->photo) && \Illuminate\Support\Facades\Storage::disk('public')->exists($student->photo) ? '' : 'hidden' }}">
                            <span id="siswaPhotoPlaceholder" class="text-2xl {{ !empty($student->photo) && \Illuminate\Support\Facades\Storage::disk('public')->exists($student->photo) ? 'hidden' : '' }}">👤</span>
                        </div>
                        <div class="flex-1 space-y-1.5 w-full">
                            <input type="file" name="photo" id="siswaPhotoInput" accept="image/jpeg,image/png,image/jpg,image/webp"
                                   onchange="previewSiswaPhoto(this)"
                                   class="block w-full text-xs text-slate-700 file:mr-3 file:py-1.5 file:px-3 file:border-2 file:border-black file:text-xs file:font-black file:bg-[#FFD43B] hover:file:bg-[#fcc419] file:cursor-pointer file:shadow-[1px_1px_0px_#000] cursor-pointer">
                            <div class="flex items-center justify-between flex-wrap gap-2 text-[10px] text-slate-500 font-semibold">
                                <span>Maksimal 5MB (JPG, PNG, WEBP).</span>
                                @if(!empty($student->photo))
                                    <label class="inline-flex items-center gap-1.5 cursor-pointer font-black text-rose-700 select-none">
                                        <input type="checkbox" name="remove_photo" value="1" class="w-3.5 h-3.5 rounded border border-black text-rose-600 focus:ring-0 cursor-pointer">
                                        <span>Hapus Foto Profil</span>
                                    </label>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <!-- Nomor Telepon -->
                    <div class="space-y-1">
                        <label for="student_phone" class="block text-xs font-black uppercase text-black">
                            No. Telepon / WhatsApp Siswa
                        </label>
                        <input type="text" name="phone" id="student_phone" value="{{ old('phone', $student->phone) }}"
                               placeholder="Contoh: 081234567890"
                               class="w-full px-3.5 py-2.5 neo-input text-xs font-mono font-bold text-black bg-white">
                        <span class="text-[10px] text-slate-500 font-semibold">Nomor aktif untuk informasi absensi sekolah.</span>
                    </div>

                    <!-- Email Login -->
                    <div class="space-y-1">
                        <label for="student_email" class="block text-xs font-black uppercase text-black">
                            Alamat Email Siswa
                        </label>
                        <input type="email" name="email" id="student_email" value="{{ old('email', $student->user->email) }}"
                               placeholder="nama@domain.com"
                               class="w-full px-3.5 py-2.5 neo-input text-xs font-mono font-bold text-black bg-white">
                        <span class="text-[10px] text-slate-500 font-semibold">Digunakan untuk login alternatif & pemulihan akun.</span>
                    </div>
                </div>

                <div class="flex justify-end pt-1">
                    <button type="submit" class="neo-btn bg-[#20C997] hover:bg-emerald-400 text-black text-xs font-black px-5 py-2 flex items-center gap-1.5 shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                        <span>💾</span> Simpan Perubahan Kontak
                    </button>
                </div>
            </form>
        </div>

        <!-- Data Pokok Akademik & Personal Siswa (Readonly EMIS GTK) -->
        <div class="bg-white neo-box p-4 sm:p-5 space-y-4">
            <div class="border-b-2 border-black pb-2.5 flex items-center justify-between">
                <div>
                    <h3 class="font-heading font-black text-sm uppercase text-black flex items-center gap-1.5">
                        <span>🏛️</span> Data Pokok Siswa (SIMPATIKA / EMIS)
                    </h3>
                    <p class="text-[11px] font-semibold text-slate-600">
                        Data identitas terdaftar resmi di basis data madrasah/sekolah.
                    </p>
                </div>
                <span class="neo-badge bg-[#E7F5FF] text-blue-950 text-[10px] font-black">TERVALIDASI</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div class="p-3 bg-slate-50 border-2 border-black rounded space-y-0.5">
                    <span class="text-[10px] font-black uppercase text-slate-500 block">Nama Lengkap Siswa:</span>
                    <span class="font-bold text-black text-sm">{{ $student->user->name }}</span>
                </div>

                <div class="p-3 bg-slate-50 border-2 border-black rounded space-y-0.5">
                    <span class="text-[10px] font-black uppercase text-slate-500 block">Nomor Induk Siswa (NIS):</span>
                    <span class="font-mono font-black text-black text-sm">{{ $student->nis }}</span>
                </div>

                <div class="p-3 bg-slate-50 border-2 border-black rounded space-y-0.5">
                    <span class="text-[10px] font-black uppercase text-slate-500 block">NISN Nasional:</span>
                    <span class="font-mono font-bold text-black">{{ $student->nisn ?: '-' }}</span>
                </div>

                <div class="p-3 bg-slate-50 border-2 border-black rounded space-y-0.5">
                    <span class="text-[10px] font-black uppercase text-slate-500 block">Rombel / Kelas:</span>
                    <span class="font-bold text-black">{{ $student->schoolClass->name ?? '-' }} (Tingkat {{ $student->schoolClass->level ?? '-' }})</span>
                </div>

                <div class="p-3 bg-slate-50 border-2 border-black rounded space-y-0.5">
                    <span class="text-[10px] font-black uppercase text-slate-500 block">Wali Kelas:</span>
                    <span class="font-bold text-blue-900">{{ $student->schoolClass->homeroomTeacher->user->name ?? 'Belum Ditugaskan' }}</span>
                </div>

                <div class="p-3 bg-slate-50 border-2 border-black rounded space-y-0.5">
                    <span class="text-[10px] font-black uppercase text-slate-500 block">Jenis Kelamin:</span>
                    <span class="font-bold text-black">{{ $student->gender === 'P' ? 'Perempuan (P)' : 'Laki-laki (L)' }}</span>
                </div>

                <div class="p-3 bg-slate-50 border-2 border-black rounded space-y-0.5">
                    <span class="text-[10px] font-black uppercase text-slate-500 block">Tempat, Tanggal Lahir:</span>
                    <span class="font-bold text-black">{{ $student->birth_place ?: '-' }}, {{ $student->birth_date ? $student->birth_date->translatedFormat('d F Y') : '-' }}</span>
                </div>

                <div class="p-3 bg-slate-50 border-2 border-black rounded space-y-0.5">
                    <span class="text-[10px] font-black uppercase text-slate-500 block">Agama:</span>
                    <span class="font-bold text-black">{{ $student->religion ?: 'Islam' }}</span>
                </div>

                <div class="p-3 bg-slate-50 border-2 border-black rounded space-y-0.5">
                    <span class="text-[10px] font-black uppercase text-slate-500 block">Status Keluarga & Anak Ke:</span>
                    <span class="font-bold text-black">{{ $student->family_status ?: 'Anak Kandung' }} (Anak ke-{{ $student->child_number ?: '1' }})</span>
                </div>

                <div class="p-3 bg-slate-50 border-2 border-black rounded space-y-0.5">
                    <span class="text-[10px] font-black uppercase text-slate-500 block">Sekolah Asal:</span>
                    <span class="font-bold text-black">{{ $student->previous_school ?: '-' }}</span>
                </div>

                <div class="p-3 bg-slate-50 border-2 border-black rounded space-y-0.5 sm:col-span-2">
                    <span class="text-[10px] font-black uppercase text-slate-500 block">Alamat Tempat Tinggal:</span>
                    <span class="font-bold text-black">{{ $student->address ?: 'Alamat belum diisi lengkap pada sistem.' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- SUB-TAB 3: DATA ORANG TUA & WALI -->
    <!-- ========================================================================= -->
    <div id="profileSubPane-ortu" class="profile-sub-pane hidden space-y-4">
        <div class="bg-white neo-box p-4 sm:p-5 space-y-4">
            <div class="border-b-2 border-black pb-2.5 flex items-center justify-between">
                <div>
                    <h2 class="font-heading font-black text-sm uppercase text-black flex items-center gap-1.5">
                        <span>👨‍👩‍👦</span> Data Orang Tua & Wali Siswa
                    </h2>
                    <p class="text-[11px] font-semibold text-slate-600">
                        Informasi identitas orang tua kandung atau wali murid terdaftar.
                    </p>
                </div>
                <span class="neo-badge bg-[#FFF3BF] text-amber-950 text-[10px] font-black">KELUARGA</span>
            </div>

            <!-- Identitas Orang Tua Kandung -->
            <div class="space-y-3">
                <div class="text-xs font-black uppercase text-black flex items-center gap-1.5 border-b border-slate-200 pb-1">
                    <span>🏠</span> Data Orang Tua Kandung
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="p-3 bg-slate-50 border-2 border-black rounded space-y-0.5">
                        <span class="text-[10px] font-black uppercase text-slate-500 block">Nama Ayah Kandung:</span>
                        <span class="font-bold text-black text-sm">{{ $student->father_name ?: '-' }}</span>
                    </div>

                    <div class="p-3 bg-slate-50 border-2 border-black rounded space-y-0.5">
                        <span class="text-[10px] font-black uppercase text-slate-500 block">Pekerjaan Ayah:</span>
                        <span class="font-bold text-black">{{ $student->father_job ?: '-' }}</span>
                    </div>

                    <div class="p-3 bg-slate-50 border-2 border-black rounded space-y-0.5">
                        <span class="text-[10px] font-black uppercase text-slate-500 block">Nama Ibu Kandung:</span>
                        <span class="font-bold text-black text-sm">{{ $student->mother_name ?: '-' }}</span>
                    </div>

                    <div class="p-3 bg-slate-50 border-2 border-black rounded space-y-0.5">
                        <span class="text-[10px] font-black uppercase text-slate-500 block">Pekerjaan Ibu:</span>
                        <span class="font-bold text-black">{{ $student->mother_job ?: '-' }}</span>
                    </div>

                    <div class="p-3 bg-slate-50 border-2 border-black rounded space-y-0.5 sm:col-span-2">
                        <span class="text-[10px] font-black uppercase text-slate-500 block">Alamat Orang Tua:</span>
                        <span class="font-bold text-black">{{ $student->parent_address ?: ($student->address ?: '-') }}</span>
                    </div>
                </div>
            </div>

            <!-- Identitas Wali Siswa -->
            @if(!empty($student->guardian_name))
            <div class="space-y-3 pt-2">
                <div class="text-xs font-black uppercase text-black flex items-center gap-1.5 border-b border-slate-200 pb-1">
                    <span>🤝</span> Data Wali Siswa
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="p-3 bg-slate-50 border-2 border-black rounded space-y-0.5">
                        <span class="text-[10px] font-black uppercase text-slate-500 block">Nama Wali:</span>
                        <span class="font-bold text-black text-sm">{{ $student->guardian_name }}</span>
                    </div>

                    <div class="p-3 bg-slate-50 border-2 border-black rounded space-y-0.5">
                        <span class="text-[10px] font-black uppercase text-slate-500 block">Pekerjaan Wali:</span>
                        <span class="font-bold text-black">{{ $student->guardian_job ?: '-' }}</span>
                    </div>

                    <div class="p-3 bg-slate-50 border-2 border-black rounded space-y-0.5 sm:col-span-2">
                        <span class="text-[10px] font-black uppercase text-slate-500 block">Alamat Wali:</span>
                        <span class="font-bold text-black">{{ $student->guardian_address ?: '-' }}</span>
                    </div>
                </div>
            </div>
            @endif

            <div class="bg-[#FFF9DB] border-2 border-black p-3 rounded text-[11px] font-semibold text-amber-950 space-y-1">
                <span class="font-black text-black flex items-center gap-1">
                    <span>ℹ️</span> Catatan Pembaruan Data Keluarga:
                </span>
                <p>
                    Data nama dan pekerjaan orang tua dikelola secara terpusat untuk keperluan ijazah dan rapor. Apabila terdapat kesalahan ketik atau perubahan, silakan lapor kepada Wali Kelas atau Tata Usaha madrasah.
                </p>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- SUB-TAB 4: KEAMANAN & KATA SANDI AKUN -->
    <!-- ========================================================================= -->
    <div id="profileSubPane-keamanan" class="profile-sub-pane hidden space-y-4">
        <div class="bg-white neo-box p-4 sm:p-5 space-y-4">
            <div class="border-b-2 border-black pb-2.5">
                <h2 class="font-heading font-black text-sm uppercase text-black flex items-center gap-1.5">
                    <span>🔐</span> Perbarui Kata Sandi Akun Siswa
                </h2>
                <p class="text-[11px] font-semibold text-slate-600">
                    Ganti kata sandi secara berkala untuk menjaga keamanan akun dan privasi tabungan Anda.
                </p>
            </div>

            <form action="{{ route('siswa.profile.password') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <!-- Password Saat Ini -->
                <div class="space-y-1">
                    <label for="siswa_current_password" class="block text-xs font-black uppercase text-black">
                        Kata Sandi Saat Ini <span class="text-rose-600">*</span>
                    </label>
                    <div class="relative">
                        <input type="password" name="current_password" id="siswa_current_password" required
                               placeholder="Masukkan kata sandi lama Anda"
                               class="w-full pl-3.5 pr-10 py-2.5 neo-input text-xs font-mono font-bold text-black bg-white">
                        <button type="button" onclick="toggleSiswaPassVisibility('siswa_current_password', 'icon_siswa_current')" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-black cursor-pointer text-xs"
                                title="Lihat/Sembunyikan Kata Sandi">
                            <span id="icon_siswa_current">👁️</span>
                        </button>
                    </div>
                    @error('current_password')
                        <p class="text-[11px] font-bold text-rose-600 mt-0.5">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <!-- Password Baru -->
                    <div class="space-y-1">
                        <label for="siswa_password" class="block text-xs font-black uppercase text-black">
                            Kata Sandi Baru <span class="text-rose-600">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" name="password" id="siswa_password" required minlength="8"
                                   placeholder="Minimal 8 karakter"
                                   class="w-full pl-3.5 pr-10 py-2.5 neo-input text-xs font-mono font-bold text-black bg-white">
                            <button type="button" onclick="toggleSiswaPassVisibility('siswa_password', 'icon_siswa_new')" 
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-black cursor-pointer text-xs"
                                    title="Lihat/Sembunyikan Kata Sandi">
                                <span id="icon_siswa_new">👁️</span>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-[11px] font-bold text-rose-600 mt-0.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Konfirmasi Password Baru -->
                    <div class="space-y-1">
                        <label for="siswa_password_confirmation" class="block text-xs font-black uppercase text-black">
                            Ulangi Kata Sandi Baru <span class="text-rose-600">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" id="siswa_password_confirmation" required minlength="8"
                                   placeholder="Ulangi kata sandi baru"
                                   class="w-full pl-3.5 pr-10 py-2.5 neo-input text-xs font-mono font-bold text-black bg-white">
                            <button type="button" onclick="toggleSiswaPassVisibility('siswa_password_confirmation', 'icon_siswa_confirm')" 
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-black cursor-pointer text-xs"
                                    title="Lihat/Sembunyikan Kata Sandi">
                                <span id="icon_siswa_confirm">👁️</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-[#E7F5FF] border-2 border-black rounded text-xs text-blue-950 space-y-1">
                    <span class="font-black flex items-center gap-1">
                        <span>🛡️</span> Tips Kata Sandi Aman:
                    </span>
                    <ul class="list-disc list-inside text-[11px] font-semibold text-blue-900 space-y-0.5 pl-1">
                        <li>Gunakan minimal 8 karakter.</li>
                        <li>Jangan berikan kata sandi Anda kepada orang lain.</li>
                    </ul>
                </div>

                <div class="flex justify-end pt-1">
                    <button type="submit" class="neo-btn bg-[#FFD43B] hover:bg-yellow-400 text-black text-xs font-black px-5 py-2 flex items-center gap-1.5 shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                        <span>🔑</span> Ganti Kata Sandi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
