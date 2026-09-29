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
<!-- TAB 6: PROFIL SISWA LENGKAP (PLAYFUL NEO-BRUTALISM) -->
<!-- ========================================================================= -->
<div id="tabContent-profil" class="tab-pane hidden space-y-4 sm:space-y-5">
    
    <!-- Sub-Tab Navigation Header -->
    <div class="bg-white rounded-2xl border-2 sm:border-[2.5px] border-black p-3 sm:p-4 shadow-[4px_4px_0px_0px_#000]">
        <div class="text-[10px] font-black uppercase text-slate-500 mb-2.5 px-1 flex items-center justify-between">
            <span class="flex items-center gap-1.5">
                <span>⚙️</span> INFORMASI AKUN & PROFIL SISWA
            </span>
            <span class="text-black font-mono bg-slate-100 px-2 py-0.5 rounded border border-black shadow-[1px_1px_0px_0px_#000]">
                EMIS GTK VERIFIED
            </span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
            <button type="button" onclick="switchProfileSubTab('card')" id="btnProfileSubTab-card"
                    class="profile-subtab-btn bg-[#FFD43B] text-black border-2 border-black py-2.5 px-2 rounded-xl text-xs font-black flex items-center justify-center gap-1.5 shadow-[2px_2px_0px_0px_#000] cursor-pointer transition-all">
                <span>🪪</span> <span class="truncate">Kartu Pelajar</span>
            </button>
            <button type="button" onclick="switchProfileSubTab('biodata')" id="btnProfileSubTab-biodata"
                    class="profile-subtab-btn bg-slate-100 hover:bg-slate-200 text-slate-700 border-2 border-transparent hover:border-black py-2.5 px-2 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 cursor-pointer transition-all">
                <span>👤</span> <span class="truncate">Biodata Diri</span>
            </button>
            <button type="button" onclick="switchProfileSubTab('ortu')" id="btnProfileSubTab-ortu"
                    class="profile-subtab-btn bg-slate-100 hover:bg-slate-200 text-slate-700 border-2 border-transparent hover:border-black py-2.5 px-2 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 cursor-pointer transition-all">
                <span>👨‍👩‍👦</span> <span class="truncate">Orang Tua / Wali</span>
            </button>
            <button type="button" onclick="switchProfileSubTab('keamanan')" id="btnProfileSubTab-keamanan"
                    class="profile-subtab-btn bg-slate-100 hover:bg-slate-200 text-slate-700 border-2 border-transparent hover:border-black py-2.5 px-2 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 cursor-pointer transition-all">
                <span>🔐</span> <span class="truncate">Keamanan Akun</span>
            </button>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- SUB-TAB 1: KARTU PELAJAR DIGITAL (KTP / KTS) RESMI -->
    <!-- ========================================================================= -->
    <div id="profileSubPane-card" class="profile-sub-pane space-y-4">
        <!-- Visualisasi Kartu Siswa -->
        <div class="bg-white rounded-2xl border-2 sm:border-[2.5px] border-black p-4 sm:p-5 space-y-4 shadow-[4px_4px_0px_0px_#000]">
            <div class="border-b-2 border-black pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="font-heading font-black text-sm sm:text-base uppercase text-black flex items-center gap-2">
                        <span>🪪</span> KARTU TANDA PELAJAR RESMI
                    </h2>
                    <p class="text-xs font-semibold text-slate-600 mt-0.5">
                        Kartu identitas resmi siswa yang dilengkapi barcode dan kode QR presensi sekolah.
                    </p>
                </div>
                <button type="button" onclick="printStudentCardOnly()" class="self-start sm:self-auto bg-[#20C997] hover:bg-[#12b886] text-black text-xs font-black px-4 py-2 rounded-xl border-2 border-black shadow-[2.5px_2.5px_0px_0px_#000] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-[1px_1px_0px_0px_#000] flex items-center gap-2 cursor-pointer transition-all shrink-0">
                    <span>🖨️</span> Cetak Kartu Fisik
                </button>
            </div>

            <!-- Kartu Pelajar Resmi Standar Studio -->
            <div id="studentCardPrintArea" class="flex justify-center my-3 overflow-x-auto p-1">
                <x-student-card :student="$student" side="both" orientation="landscape" :interactive="true" />
            </div>

            <!-- Petunjuk Penggunaan Kartu -->
            <div class="bg-[#FFF9DB] rounded-xl border-2 border-black p-4 text-xs font-semibold text-amber-950 space-y-1 shadow-[2px_2px_0px_0px_#000]">
                <span class="font-black text-black flex items-center gap-1.5 text-xs sm:text-sm">
                    <span>💡</span> Petunjuk Penggunaan Kartu Pelajar Digital:
                </span>
                <p class="leading-relaxed">
                    Tunjukkan kartu pelajar ini atau barcode di dalamnya ke scanner presensi gerbang sekolah. Anda juga dapat mencetak kartu dalam ukuran pas dompet menggunakan tombol <b>"Cetak Kartu Fisik"</b> di atas.
                </p>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- SUB-TAB 2: BIODATA LENGKAP SISWA (EMIS GTK VERIFIED) -->
    <!-- ========================================================================= -->
    <div id="profileSubPane-biodata" class="profile-sub-pane hidden space-y-4">
        <!-- Form Pembaruan Kontak & Foto Mandiri -->
        <div class="bg-white rounded-2xl border-2 sm:border-[2.5px] border-black p-4 sm:p-5 space-y-4 shadow-[4px_4px_0px_0px_#000]">
            <div class="border-b-2 border-black pb-3">
                <h2 class="font-heading font-black text-sm sm:text-base uppercase text-black flex items-center gap-2">
                    <span>✏️</span> Pengaturan Foto & Kontak Pribadi
                </h2>
                <p class="text-xs font-semibold text-slate-600 mt-0.5">
                    Perbarui foto profil akun dan nomor kontak telepon aktif Anda.
                </p>
            </div>

            <form action="{{ route('siswa.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')

                <!-- Foto Profil Siswa -->
                <div>
                    <label class="block text-xs font-black uppercase text-black mb-1.5">
                        Foto Profil Siswa
                    </label>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 bg-slate-50 rounded-xl border-2 border-black p-3.5 sm:p-4 shadow-[2px_2px_0px_0px_#000]">
                        <div class="w-20 h-24 bg-white rounded-xl border-2 border-black overflow-hidden flex items-center justify-center shrink-0 shadow-[2px_2px_0px_0px_#000]">
                            <img id="siswaPhotoPreview" 
                                 src="{{ $student->photo_url ?? '' }}" 
                                 alt="Pratinjau" 
                                 class="w-full h-full object-cover {{ $student->photo_url ? '' : 'hidden' }}">
                            <span id="siswaPhotoPlaceholder" class="text-3xl {{ $student->photo_url ? 'hidden' : '' }}">👤</span>
                        </div>
                        <div class="flex-1 space-y-2 w-full">
                            <input type="file" name="photo" id="siswaPhotoInput" accept="image/jpeg,image/png,image/jpg,image/webp"
                                   onchange="previewSiswaPhoto(this)"
                                   class="block w-full text-xs text-slate-700 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-2 file:border-black file:text-xs file:font-black file:bg-[#FFD43B] hover:file:bg-[#fcc419] file:cursor-pointer file:shadow-[1.5px_1.5px_0px_#000] cursor-pointer">
                            <div class="flex items-center justify-between flex-wrap gap-2 text-[11px] text-slate-500 font-semibold">
                                <span>Format file: JPG, PNG, WEBP (Maksimal 5MB).</span>
                                @if(!empty($student->photo))
                                    <label class="inline-flex items-center gap-1.5 cursor-pointer font-black text-rose-700 select-none">
                                        <input type="checkbox" name="remove_photo" value="1" class="w-4 h-4 rounded border-2 border-black text-rose-600 focus:ring-0 cursor-pointer">
                                        <span>Hapus Foto Profil</span>
                                    </label>
                                @endif
                            </div>
                            @error('photo')
                                <p class="text-xs font-bold text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
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
                               class="w-full px-3.5 py-2.5 rounded-xl border-2 border-black shadow-[2px_2px_0px_0px_#000] focus:shadow-[4px_4px_0px_0px_#000] text-xs font-mono font-bold text-black bg-white outline-none transition-all">
                        @error('phone')
                            <p class="text-xs font-bold text-rose-600 mt-0.5">{{ $message }}</p>
                        @enderror
                        <span class="text-[10px] text-slate-500 font-bold block">Nomor aktif untuk notifikasi presensi sekolah.</span>
                    </div>

                    <!-- Email Login -->
                    <div class="space-y-1">
                        <label for="student_email" class="block text-xs font-black uppercase text-black">
                            Alamat Email Siswa
                        </label>
                        <input type="email" name="email" id="student_email" value="{{ old('email', $student->user->email) }}"
                               placeholder="nama@domain.com"
                               class="w-full px-3.5 py-2.5 rounded-xl border-2 border-black shadow-[2px_2px_0px_0px_#000] focus:shadow-[4px_4px_0px_0px_#000] text-xs font-mono font-bold text-black bg-white outline-none transition-all">
                        @error('email')
                            <p class="text-xs font-bold text-rose-600 mt-0.5">{{ $message }}</p>
                        @enderror
                        <span class="text-[10px] text-slate-500 font-bold block">Digunakan untuk login alternatif & pemulihan akun.</span>
                    </div>
                </div>

                <div class="flex justify-end pt-1">
                    <button type="submit" class="bg-[#20C997] hover:bg-[#12b886] text-black text-xs sm:text-sm font-black uppercase px-6 py-2.5 rounded-xl border-2 border-black shadow-[3px_3px_0px_0px_#000] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-[1px_1px_0px_0px_#000] flex items-center gap-2 cursor-pointer transition-all">
                        <span>💾 Simpan Perubahan Kontak</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Data Pokok Akademik & Personal Siswa (Readonly EMIS GTK) -->
        <div class="bg-white rounded-2xl border-2 sm:border-[2.5px] border-black p-4 sm:p-5 space-y-4 shadow-[4px_4px_0px_0px_#000]">
            <div class="border-b-2 border-black pb-3 flex items-center justify-between">
                <div>
                    <h3 class="font-heading font-black text-sm sm:text-base uppercase text-black flex items-center gap-2">
                        <span>🏛️</span> Data Pokok Siswa (SIMPATIKA / EMIS)
                    </h3>
                    <p class="text-xs font-semibold text-slate-600 mt-0.5">
                        Data identitas terdaftar resmi di basis data madrasah/sekolah.
                    </p>
                </div>
                <span class="bg-[#E7F5FF] text-blue-950 text-[10px] font-black uppercase px-3 py-1 rounded-full border border-black shadow-[1.5px_1.5px_0px_0px_#000]">
                    TERVALIDASI
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div class="p-3.5 bg-slate-50 rounded-xl border-2 border-black shadow-[2px_2px_0px_0px_#000] space-y-1">
                    <span class="text-[10px] font-black uppercase text-slate-500 block">Nama Lengkap Siswa:</span>
                    <span class="font-heading font-black text-black text-base">{{ $student->user->name }}</span>
                </div>

                <div class="p-3.5 bg-slate-50 rounded-xl border-2 border-black shadow-[2px_2px_0px_0px_#000] space-y-1">
                    <span class="text-[10px] font-black uppercase text-slate-500 block">Nomor Induk Siswa (NIS):</span>
                    <span class="font-mono font-black text-black text-base">{{ $student->nis }}</span>
                </div>

                <div class="p-3.5 bg-slate-50 rounded-xl border-2 border-black shadow-[2px_2px_0px_0px_#000] space-y-1">
                    <span class="text-[10px] font-black uppercase text-slate-500 block">NISN Nasional:</span>
                    <span class="font-mono font-bold text-black text-sm">{{ $student->nisn ?: '-' }}</span>
                </div>

                <div class="p-3.5 bg-slate-50 rounded-xl border-2 border-black shadow-[2px_2px_0px_0px_#000] space-y-1">
                    <span class="text-[10px] font-black uppercase text-slate-500 block">Rombel / Kelas:</span>
                    <span class="font-bold text-black text-sm">{{ $student->schoolClass->name ?? '-' }} (Tingkat {{ $student->schoolClass->level ?? '-' }})</span>
                </div>

                <div class="p-3.5 bg-slate-50 rounded-xl border-2 border-black shadow-[2px_2px_0px_0px_#000] space-y-1">
                    <span class="text-[10px] font-black uppercase text-slate-500 block">Wali Kelas:</span>
                    <span class="font-bold text-blue-900 text-sm">{{ $student->schoolClass->homeroomTeacher->user->name ?? 'Belum Ditugaskan' }}</span>
                </div>

                <div class="p-3.5 bg-slate-50 rounded-xl border-2 border-black shadow-[2px_2px_0px_0px_#000] space-y-1">
                    <span class="text-[10px] font-black uppercase text-slate-500 block">Jenis Kelamin:</span>
                    <span class="font-bold text-black text-sm">{{ $student->gender === 'P' ? 'Perempuan (P)' : 'Laki-laki (L)' }}</span>
                </div>

                <div class="p-3.5 bg-slate-50 rounded-xl border-2 border-black shadow-[2px_2px_0px_0px_#000] space-y-1">
                    <span class="text-[10px] font-black uppercase text-slate-500 block">Tempat, Tanggal Lahir:</span>
                    <span class="font-bold text-black text-sm">{{ $student->birth_place ?: '-' }}, {{ $student->birth_date ? $student->birth_date->translatedFormat('d F Y') : '-' }}</span>
                </div>

                <div class="p-3.5 bg-slate-50 rounded-xl border-2 border-black shadow-[2px_2px_0px_0px_#000] space-y-1">
                    <span class="text-[10px] font-black uppercase text-slate-500 block">Agama:</span>
                    <span class="font-bold text-black text-sm">{{ $student->religion ?: 'Islam' }}</span>
                </div>

                <div class="p-3.5 bg-slate-50 rounded-xl border-2 border-black shadow-[2px_2px_0px_0px_#000] space-y-1">
                    <span class="text-[10px] font-black uppercase text-slate-500 block">Status Keluarga & Anak Ke:</span>
                    <span class="font-bold text-black text-sm">{{ $student->family_status ?: 'Anak Kandung' }} (Anak ke-{{ $student->child_number ?: '1' }})</span>
                </div>

                <div class="p-3.5 bg-slate-50 rounded-xl border-2 border-black shadow-[2px_2px_0px_0px_#000] space-y-1">
                    <span class="text-[10px] font-black uppercase text-slate-500 block">Sekolah Asal:</span>
                    <span class="font-bold text-black text-sm">{{ $student->previous_school ?: '-' }}</span>
                </div>

                <div class="p-3.5 bg-slate-50 rounded-xl border-2 border-black shadow-[2px_2px_0px_0px_#000] space-y-1 sm:col-span-2">
                    <span class="text-[10px] font-black uppercase text-slate-500 block">Alamat Tempat Tinggal:</span>
                    <span class="font-bold text-black text-sm leading-relaxed">{{ $student->address ?: 'Alamat belum diisi lengkap pada sistem.' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- SUB-TAB 3: DATA ORANG TUA & WALI -->
    <!-- ========================================================================= -->
    <div id="profileSubPane-ortu" class="profile-sub-pane hidden space-y-4">
        <div class="bg-white rounded-2xl border-2 sm:border-[2.5px] border-black p-4 sm:p-5 space-y-4 shadow-[4px_4px_0px_0px_#000]">
            <div class="border-b-2 border-black pb-3 flex items-center justify-between">
                <div>
                    <h2 class="font-heading font-black text-sm sm:text-base uppercase text-black flex items-center gap-2">
                        <span>👨‍👩‍👦</span> Data Orang Tua & Wali Siswa
                    </h2>
                    <p class="text-xs font-semibold text-slate-600 mt-0.5">
                        Informasi identitas orang tua kandung atau wali murid terdaftar.
                    </p>
                </div>
                <span class="bg-[#FFF3BF] text-amber-950 text-[10px] font-black uppercase px-3 py-1 rounded-full border border-black shadow-[1.5px_1.5px_0px_0px_#000]">
                    KELUARGA
                </span>
            </div>

            <!-- Identitas Orang Tua Kandung -->
            <div class="space-y-3">
                <div class="text-xs font-black uppercase text-black flex items-center gap-2 border-b border-slate-200 pb-1.5">
                    <span>🏠</span> Identitas Orang Tua Kandung
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="p-3.5 bg-slate-50 rounded-xl border-2 border-black shadow-[2px_2px_0px_0px_#000] space-y-1">
                        <span class="text-[10px] font-black uppercase text-slate-500 block">Nama Ayah Kandung:</span>
                        <span class="font-bold text-black text-sm sm:text-base">{{ $student->father_name ?: '-' }}</span>
                    </div>

                    <div class="p-3.5 bg-slate-50 rounded-xl border-2 border-black shadow-[2px_2px_0px_0px_#000] space-y-1">
                        <span class="text-[10px] font-black uppercase text-slate-500 block">Pekerjaan Ayah:</span>
                        <span class="font-bold text-black text-sm">{{ $student->father_job ?: '-' }}</span>
                    </div>

                    <div class="p-3.5 bg-slate-50 rounded-xl border-2 border-black shadow-[2px_2px_0px_0px_#000] space-y-1">
                        <span class="text-[10px] font-black uppercase text-slate-500 block">Nama Ibu Kandung:</span>
                        <span class="font-bold text-black text-sm sm:text-base">{{ $student->mother_name ?: '-' }}</span>
                    </div>

                    <div class="p-3.5 bg-slate-50 rounded-xl border-2 border-black shadow-[2px_2px_0px_0px_#000] space-y-1">
                        <span class="text-[10px] font-black uppercase text-slate-500 block">Pekerjaan Ibu:</span>
                        <span class="font-bold text-black text-sm">{{ $student->mother_job ?: '-' }}</span>
                    </div>

                    <div class="p-3.5 bg-slate-50 rounded-xl border-2 border-black shadow-[2px_2px_0px_0px_#000] space-y-1 sm:col-span-2">
                        <span class="text-[10px] font-black uppercase text-slate-500 block">Alamat Orang Tua:</span>
                        <span class="font-bold text-black text-sm leading-relaxed">{{ $student->parent_address ?: ($student->address ?: '-') }}</span>
                    </div>
                </div>
            </div>

            <!-- Identitas Wali Siswa -->
            @if(!empty($student->guardian_name))
            <div class="space-y-3 pt-2">
                <div class="text-xs font-black uppercase text-black flex items-center gap-2 border-b border-slate-200 pb-1.5">
                    <span>🤝</span> Data Wali Siswa
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="p-3.5 bg-slate-50 rounded-xl border-2 border-black shadow-[2px_2px_0px_0px_#000] space-y-1">
                        <span class="text-[10px] font-black uppercase text-slate-500 block">Nama Wali:</span>
                        <span class="font-bold text-black text-sm sm:text-base">{{ $student->guardian_name }}</span>
                    </div>

                    <div class="p-3.5 bg-slate-50 rounded-xl border-2 border-black shadow-[2px_2px_0px_0px_#000] space-y-1">
                        <span class="text-[10px] font-black uppercase text-slate-500 block">Pekerjaan Wali:</span>
                        <span class="font-bold text-black text-sm">{{ $student->guardian_job ?: '-' }}</span>
                    </div>

                    <div class="p-3.5 bg-slate-50 rounded-xl border-2 border-black shadow-[2px_2px_0px_0px_#000] space-y-1 sm:col-span-2">
                        <span class="text-[10px] font-black uppercase text-slate-500 block">Alamat Wali:</span>
                        <span class="font-bold text-black text-sm leading-relaxed">{{ $student->guardian_address ?: '-' }}</span>
                    </div>
                </div>
            </div>
            @endif

            <div class="bg-[#FFF9DB] rounded-xl border-2 border-black p-4 text-xs font-semibold text-amber-950 space-y-1 shadow-[2px_2px_0px_0px_#000]">
                <span class="font-black text-black flex items-center gap-1.5 text-xs sm:text-sm">
                    <span>ℹ️</span> Catatan Pembaruan Data Keluarga:
                </span>
                <p class="leading-relaxed">
                    Data nama dan pekerjaan orang tua dikelola secara terpusat untuk keperluan ijazah dan rapor. Apabila terdapat perubahan atau perbaikan, silakan melapor kepada Wali Kelas atau Tata Usaha sekolah.
                </p>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- SUB-TAB 4: KEAMANAN & KATA SANDI AKUN -->
    <!-- ========================================================================= -->
    <div id="profileSubPane-keamanan" class="profile-sub-pane hidden space-y-4">
        <div class="bg-white rounded-2xl border-2 sm:border-[2.5px] border-black p-4 sm:p-5 space-y-4 shadow-[4px_4px_0px_0px_#000]">
            <div class="border-b-2 border-black pb-3">
                <h2 class="font-heading font-black text-sm sm:text-base uppercase text-black flex items-center gap-2">
                    <span>🔐</span> Perbarui Kata Sandi Akun Siswa
                </h2>
                <p class="text-xs font-semibold text-slate-600 mt-0.5">
                    Ganti kata sandi secara berkala untuk menjaga keamanan akun dan privasi data Anda.
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
                               class="w-full pl-3.5 pr-10 py-2.5 rounded-xl border-2 border-black shadow-[2px_2px_0px_0px_#000] focus:shadow-[4px_4px_0px_0px_#000] text-xs font-mono font-bold text-black bg-white outline-none transition-all">
                        <button type="button" onclick="toggleSiswaPassVisibility('siswa_current_password', 'icon_siswa_current')" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-black cursor-pointer text-xs"
                                title="Lihat/Sembunyikan Kata Sandi">
                            <span id="icon_siswa_current">👁️</span>
                        </button>
                    </div>
                    @error('current_password')
                        <p class="text-xs font-bold text-rose-600 mt-0.5">{{ $message }}</p>
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
                                   class="w-full pl-3.5 pr-10 py-2.5 rounded-xl border-2 border-black shadow-[2px_2px_0px_0px_#000] focus:shadow-[4px_4px_0px_0px_#000] text-xs font-mono font-bold text-black bg-white outline-none transition-all">
                            <button type="button" onclick="toggleSiswaPassVisibility('siswa_password', 'icon_siswa_new')" 
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-black cursor-pointer text-xs"
                                    title="Lihat/Sembunyikan Kata Sandi">
                                <span id="icon_siswa_new">👁️</span>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-xs font-bold text-rose-600 mt-0.5">{{ $message }}</p>
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
                                   class="w-full pl-3.5 pr-10 py-2.5 rounded-xl border-2 border-black shadow-[2px_2px_0px_0px_#000] focus:shadow-[4px_4px_0px_0px_#000] text-xs font-mono font-bold text-black bg-white outline-none transition-all">
                            <button type="button" onclick="toggleSiswaPassVisibility('siswa_password_confirmation', 'icon_siswa_confirm')" 
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-black cursor-pointer text-xs"
                                    title="Lihat/Sembunyikan Kata Sandi">
                                <span id="icon_siswa_confirm">👁️</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-[#E7F5FF] rounded-xl border-2 border-black text-xs text-blue-950 space-y-1 shadow-[2px_2px_0px_0px_#000]">
                    <span class="font-black flex items-center gap-1.5">
                        <span>🛡️</span> Tips Keamanan Kata Sandi:
                    </span>
                    <ul class="list-disc list-inside text-xs font-medium text-blue-900 space-y-0.5 pl-1">
                        <li>Gunakan kombinasi huruf, angka, dan minimal 8 karakter.</li>
                        <li>Jangan bagikan kata sandi akun Anda kepada siapapun.</li>
                    </ul>
                </div>

                <div class="flex justify-end pt-1">
                    <button type="submit" class="bg-[#FFD43B] hover:bg-[#fcc419] text-black text-xs sm:text-sm font-black uppercase px-6 py-2.5 rounded-xl border-2 border-black shadow-[3px_3px_0px_0px_#000] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-[1px_1px_0px_0px_#000] flex items-center gap-2 cursor-pointer transition-all">
                        <span>🔑 Ganti Kata Sandi</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
