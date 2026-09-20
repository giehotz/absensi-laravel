@extends('layouts.guru')

@section('title', 'Profil Saya')
@section('page-title', 'Profil & Penugasan Guru')

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/cropperjs/cropper.min.css') }}">
<style>
    /* Styling lingkaran pembantu crop profil */
    .cropper-view-box,
    .cropper-face {
        border-radius: 50%;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Header Title Card -->
    <div class="bg-[#FFF3BF] neo-box-lg p-6 sm:p-8 text-black relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="space-y-1.5 z-10">
            <div class="flex items-center gap-2">
                <span class="neo-badge bg-[#5294FF] text-white">PORTAL GURU</span>
                <span class="text-xs font-mono font-bold bg-white px-2 py-0.5 border border-black">
                    NIP: {{ $teacher->nip ?? '-' }}
                </span>
                <span class="text-xs font-mono font-bold bg-white px-2 py-0.5 border border-black hidden sm:inline-block">
                    {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                </span>
            </div>
            <h1 class="font-heading text-2xl sm:text-3xl font-black tracking-tight text-black uppercase">
                PROFIL SAYA & PENUGASAN
            </h1>
            <p class="text-xs sm:text-sm font-semibold text-slate-800 max-w-2xl">
                Kelola informasi biodata, perbarui kata sandi akun, serta tinjau penugasan wali kelas dan jadwal mengajar mingguan Anda.
            </p>
        </div>

        <div class="z-10 flex flex-wrap gap-2">
            <a href="{{ route('guru.dashboard') }}" class="neo-btn bg-white text-black text-xs font-bold px-4 py-2.5 flex items-center gap-1.5 cursor-pointer hover:bg-slate-100">
                ← Dashboard
            </a>
        </div>
    </div>

    <!-- Layout 2 Kolom -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- KOLOM KIRI (5 Kolom): Kartu Identitas & Jadwal Mengajar -->
        <div class="lg:col-span-5 space-y-6">
            
            <!-- Kartu Identitas Guru -->
            <div class="bg-white neo-box p-6 space-y-5">
                <div class="flex items-center gap-4">
                    @if(!empty($teacher->photo) && \Illuminate\Support\Facades\Storage::disk('public')->exists($teacher->photo))
                        <div class="w-16 h-16 sm:w-20 sm:h-20 bg-white border-3 border-black neo-box-sm rounded-full overflow-hidden shrink-0 shadow-sm">
                            <img src="{{ asset('storage/' . $teacher->photo) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                        </div>
                    @else
                        <div class="w-16 h-16 sm:w-20 sm:h-20 bg-[#5294FF] border-3 border-black neo-box-sm rounded-full flex items-center justify-center text-3xl sm:text-4xl shadow-sm shrink-0">
                            👨‍🏫
                        </div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <span class="neo-badge bg-black text-white text-[10px] py-0.5">DEWAN GURU</span>
                        <h2 class="font-heading font-black text-lg sm:text-xl text-black truncate mt-1">
                            {{ $user->name }}
                        </h2>
                        <p class="text-xs font-mono font-bold text-slate-700">
                            NIP: {{ $teacher->nip ?? '-' }}
                        </p>
                    </div>
                </div>

                <!-- Status Wali Kelas -->
                <div class="p-3.5 border-2 border-black {{ $homeroomClasses->isNotEmpty() ? 'bg-[#D3F9D8]' : 'bg-slate-100' }} rounded-sm space-y-1">
                    <div class="text-[11px] font-black uppercase text-black flex items-center justify-between">
                        <span>🎓 Penugasan Wali Kelas</span>
                        <span class="text-xs">{{ $homeroomClasses->isNotEmpty() ? '✅ AKTIF' : 'ℹ️' }}</span>
                    </div>
                    @if($homeroomClasses->isNotEmpty())
                        @foreach($homeroomClasses as $hClass)
                            <div class="text-xs font-black text-emerald-950 flex items-center justify-between">
                                <span>{{ $hClass->name }} (Tingkat {{ $hClass->level }})</span>
                                <a href="{{ route('guru.classes.binaan') }}" class="text-[10px] font-bold text-blue-800 underline hover:text-blue-900">
                                    Buka Kelas →
                                </a>
                            </div>
                            <div class="text-[10px] font-medium text-emerald-900">
                                Tahun Ajaran: {{ $hClass->academicYear->name ?? 'Aktif' }}
                            </div>
                        @endforeach
                    @else
                        <p class="text-[11px] font-medium text-slate-600">
                            Saat ini Anda tidak ditugaskan sebagai wali kelas.
                        </p>
                    @endif
                </div>

                <!-- Kontak Ringkas -->
                <div class="space-y-2 pt-2 border-t-2 border-black/10 text-xs">
                    <div class="flex items-center justify-between py-0.5">
                        <span class="font-bold text-slate-500">NUPTK:</span>
                        <span class="font-mono font-bold text-black">{{ $teacher->nuptk ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-0.5">
                        <span class="font-bold text-slate-500">NIP:</span>
                        <span class="font-mono font-bold text-black">{{ $teacher->nip ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-0.5">
                        <span class="font-bold text-slate-500">Jenis Kelamin:</span>
                        <span class="font-bold text-black">{{ $teacher->gender === 'L' ? 'Laki-laki' : ($teacher->gender === 'P' ? 'Perempuan' : '-') }}</span>
                    </div>
                    <div class="flex items-center justify-between py-0.5">
                        <span class="font-bold text-slate-500">Tempat, Tgl Lahir:</span>
                        <span class="font-semibold text-black">{{ $teacher->birth_place ?: '-' }}, {{ $teacher->birth_date ? $teacher->birth_date->format('d-m-Y') : '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-0.5">
                        <span class="font-bold text-slate-500">Pendidikan Terakhir:</span>
                        <span class="neo-badge bg-slate-100 text-black text-[10px]">{{ $teacher->last_education ?: '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-0.5">
                        <span class="font-bold text-slate-500">Email Akun:</span>
                        @if($user->email)
                            <span class="font-mono font-bold text-black">{{ $user->email }}</span>
                        @else
                            <span class="neo-badge bg-[#FFF9DB] text-amber-950 text-[10px]">Belum Diisi</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between py-0.5">
                        <span class="font-bold text-slate-500">No. WhatsApp/HP:</span>
                        <span class="font-mono font-semibold text-black">{{ $teacher->phone ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-0.5">
                        <span class="font-bold text-slate-500">Total Jadwal Mengajar:</span>
                        <span class="font-mono font-black text-black">{{ $schedules->count() }} Pertemuan/Minggu</span>
                    </div>
                </div>
            </div>

            <!-- Ringkasan Jadwal Mengajar Mingguan -->
            <div class="bg-white neo-box p-6 space-y-4">
                <div class="flex items-center justify-between border-b-2 border-black pb-3">
                    <h3 class="font-heading font-black text-sm sm:text-base uppercase flex items-center gap-2 text-black">
                        <span>📅</span> Jadwal Mengajar
                    </h3>
                    <span class="text-[10px] font-mono font-bold bg-[#E7F5FF] text-blue-900 border border-black px-2 py-0.5">
                        {{ $schedules->count() }} Sesi
                    </span>
                </div>

                @if(empty($schedulesByDay))
                    <div class="text-center py-8 px-4 bg-slate-50 border-2 border-dashed border-slate-300 rounded-sm">
                        <span class="text-3xl block mb-2">📖</span>
                        <p class="text-xs font-bold text-slate-600">Belum ada jadwal mengajar yang terdaftar.</p>
                        <p class="text-[10px] text-slate-400 mt-1">Jadwal mengajar dikelola oleh administrator sekolah.</p>
                    </div>
                @else
                    <div class="space-y-4 max-h-[500px] overflow-y-auto pr-1">
                        @foreach($schedulesByDay as $dayName => $daySchedules)
                            <div class="space-y-2">
                                <div class="text-[11px] font-black uppercase text-black bg-[#FFF9DB] border border-black px-2.5 py-1 flex items-center justify-between">
                                    <span>{{ $dayName }}</span>
                                    <span class="text-[10px] font-mono text-slate-600">{{ count($daySchedules) }} Kelas</span>
                                </div>

                                <div class="space-y-1.5">
                                    @foreach($daySchedules as $sch)
                                        <div class="p-2.5 bg-slate-50 border-2 border-black rounded-sm flex items-center justify-between gap-2 hover:bg-amber-50 transition-colors">
                                            <div class="min-w-0">
                                                <div class="text-xs font-black text-black truncate">
                                                    {{ $sch->subject->name ?? 'Mata Pelajaran' }}
                                                </div>
                                                <div class="text-[10px] font-semibold text-slate-600 flex items-center gap-2 mt-0.5">
                                                    <span class="bg-white border border-black px-1.5 py-0.2 rounded font-mono">
                                                        {{ $sch->schoolClass->name ?? '-' }}
                                                    </span>
                                                    <span>•</span>
                                                    <span>{{ $sch->schoolClass->level ?? '' }}</span>
                                                </div>
                                            </div>
                                            <div class="text-right shrink-0">
                                                <span class="text-[10px] font-mono font-black bg-[#5294FF] text-white px-2 py-0.5 border border-black inline-block">
                                                    {{ substr($sch->start_time, 0, 5) }} - {{ substr($sch->end_time, 0, 5) }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

        <!-- KOLOM KANAN (7 Kolom): Formulir Ubah Biodata & Ganti Kata Sandi -->
        <div class="lg:col-span-7 space-y-6">
            
            <!-- Kartu 1: Formulir Ubah Biodata Diri -->
            <div class="bg-white neo-box p-6 space-y-5">
                <div class="border-b-2 border-black pb-3">
                    <h3 class="font-heading font-black text-base uppercase flex items-center gap-2 text-black">
                        <span>👤</span> Pengaturan Biodata Guru
                    </h3>
                    <p class="text-xs font-medium text-slate-600 mt-1">
                        Perbarui informasi nama lengkap, email login, dan nomor kontak Anda.
                    </p>
                </div>

                <form id="formUpdateProfile" action="{{ route('guru.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4" onsubmit="return confirmUpdateProfile(event)">
                    @csrf
                    @method('PUT')

                    <!-- NIP (Read-only) -->
                    <div>
                        <label class="block text-xs font-black uppercase text-black mb-1 flex items-center justify-between">
                            <span>Nomor Induk Pegawai (NIP)</span>
                            <span class="text-[10px] text-slate-500 font-bold">🔒 Tidak Dapat Diubah</span>
                        </label>
                        <div class="relative">
                            <input type="text" value="{{ $teacher->nip ?? '-' }}" disabled
                                   class="w-full bg-slate-100 border-2 border-black px-3 py-2 text-xs font-mono font-bold text-slate-700 cursor-not-allowed">
                        </div>
                        <p class="text-[10px] text-slate-500 mt-1">
                            NIP ditetapkan oleh administrator sistem sekolah. Hubungi admin jika terdapat kesalahan.
                        </p>
                    </div>

                    <!-- Nama Lengkap -->
                    <div>
                        <label class="block text-xs font-black uppercase text-black mb-1">
                            Nama Lengkap Guru *
                        </label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                               placeholder="Nama Lengkap dengan Gelar"
                               class="w-full bg-white border-2 border-black px-3 py-2 text-xs font-bold text-black focus:outline-hidden focus:ring-2 focus:ring-[#FFD43B]">
                        @error('name')
                            <p class="text-[11px] font-bold text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Alamat Email -->
                    <div>
                        <label class="block text-xs font-black uppercase text-black mb-1 flex items-center justify-between">
                            <span>Alamat Email Pribadi (Mandiri) *</span>
                            @if(empty($user->email))
                                <span class="neo-badge bg-[#FFE3E3] text-rose-950 text-[10px]">Wajib Dilengkapi</span>
                            @endif
                        </label>
                        @if(empty($user->email))
                            <div class="bg-[#FFF9DB] border-2 border-black p-2.5 mb-2 text-xs font-bold text-amber-950 flex items-center gap-2">
                                <span>⚠️</span>
                                <span>Email Anda belum terdaftar. Silakan lengkapi email aktif Anda di bawah ini untuk pemulihan akun.</span>
                            </div>
                        @endif
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                               placeholder="nama.anda@gmail.com"
                               class="w-full bg-white border-2 border-black px-3 py-2 text-xs font-mono font-bold text-black focus:outline-hidden focus:ring-2 focus:ring-[#FFD43B]">
                        <p class="text-[10px] text-slate-500 mt-1">
                            Email ini dapat Anda gunakan sebagai alternatif login ke sistem presensi.
                        </p>
                        @error('email')
                            <p class="text-[11px] font-bold text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nomor WhatsApp / HP -->
                    <div>
                        <label class="block text-xs font-black uppercase text-black mb-1">
                            Nomor Telepon / WhatsApp
                        </label>
                        <input type="text" name="phone" value="{{ old('phone', $teacher->phone) }}"
                               placeholder="Contoh: 081234567890"
                               class="w-full bg-white border-2 border-black px-3 py-2 text-xs font-mono font-bold text-black focus:outline-hidden focus:ring-2 focus:ring-[#FFD43B]">
                        <p class="text-[10px] text-slate-500 mt-1">
                            Nomor WhatsApp untuk koordinasi kedinasan dan komunikasi dengan wali murid.
                        </p>
                        @error('phone')
                            <p class="text-[11px] font-bold text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Foto Profil Guru -->
                    <div>
                        <label class="block text-xs font-black uppercase text-black mb-1">
                            Foto Profil Guru
                        </label>
                        <input type="hidden" name="photo_cropped" id="photoCroppedInput">

                        <div class="flex items-center gap-4 p-3 bg-slate-50 border-2 border-black rounded-sm">
                            <!-- Preview Box -->
                            <div class="w-16 h-16 bg-white border-2 border-black rounded-full overflow-hidden flex items-center justify-center shrink-0 shadow-[2px_2px_0px_#000]" id="avatarPreviewContainer">
                                @if(!empty($teacher->photo) && \Illuminate\Support\Facades\Storage::disk('public')->exists($teacher->photo))
                                    <img src="{{ asset('storage/' . $teacher->photo) }}" id="avatarPreviewImage" data-original-src="{{ asset('storage/' . $teacher->photo) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                    <span id="avatarPreviewEmoji" class="text-2xl hidden">👨‍🏫</span>
                                @else
                                    <span id="avatarPreviewEmoji" class="text-2xl">👨‍🏫</span>
                                    <img src="" id="avatarPreviewImage" data-original-src="" alt="{{ $user->name }}" class="w-full h-full object-cover hidden">
                                @endif
                            </div>

                            <div class="flex-1 min-w-0 space-y-1.5">
                                <input type="file" name="photo" id="teacherPhotoInput" accept="image/jpeg,image/png,image/jpg,image/webp"
                                       onchange="handleTeacherPhotoChange(this)"
                                       class="block w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:border-2 file:border-black file:text-xs file:font-black file:bg-[#FFD43B] hover:file:bg-[#fcc419] file:cursor-pointer file:shadow-[1px_1px_0px_#000] cursor-pointer">
                                
                                <div class="flex items-center justify-between flex-wrap gap-2">
                                    <p class="text-[10px] text-slate-500 font-medium">
                                        Maks. 5MB. Format: JPG, JPEG, PNG, WEBP.
                                    </p>

                                    <div class="flex items-center gap-2">
                                        <button type="button" id="btnRecrop" onclick="reopenCropper()" class="hidden neo-btn bg-white hover:bg-slate-100 text-black px-2 py-0.5 text-[10px] font-bold border border-black cursor-pointer">
                                            ✂️ Potong Ulang
                                        </button>

                                        @if(!empty($teacher->photo))
                                            <label class="inline-flex items-center gap-1.5 cursor-pointer text-[10px] font-black text-rose-700 select-none">
                                                <input type="checkbox" name="remove_photo" value="1" id="removePhotoCheckbox" onchange="toggleRemoveTeacherPhoto(this)" class="w-3.5 h-3.5 rounded border border-black text-rose-600 focus:ring-0 cursor-pointer">
                                                <span>Hapus Foto</span>
                                            </label>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @error('photo')
                            <p class="text-[11px] font-bold text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                        @error('photo_cropped')
                            <p class="text-[11px] font-bold text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Action Button -->
                    <div class="pt-3 border-t-2 border-black/10 flex justify-end">
                        <button type="submit" class="neo-btn bg-[#20C997] text-black text-xs font-black px-5 py-2.5 flex items-center gap-1.5 hover:bg-emerald-400 cursor-pointer shadow-sm">
                            <span>💾</span> Simpan Perubahan Biodata
                        </button>
                    </div>
                </form>
            </div>

            <!-- Kartu 2: Formulir Ganti Kata Sandi -->
            <div class="bg-white neo-box p-6 space-y-5">
                <div class="border-b-2 border-black pb-3">
                    <h3 class="font-heading font-black text-base uppercase flex items-center gap-2 text-black">
                        <span>🔐</span> Keamanan & Kata Sandi
                    </h3>
                    <p class="text-xs font-medium text-slate-600 mt-1">
                        Ganti kata sandi akun Anda secara berkala demi keamanan data presensi dan siswa.
                    </p>
                </div>

                <form id="formUpdatePassword" action="{{ route('guru.profile.password') }}" method="POST" class="space-y-4" onsubmit="return confirmUpdatePassword(event)">
                    @csrf
                    @method('PUT')

                    <!-- Password Saat Ini -->
                    <div>
                        <label class="block text-xs font-black uppercase text-black mb-1">
                            Kata Sandi Saat Ini *
                        </label>
                        <div class="relative">
                            <input type="password" id="current_password" name="current_password" required
                                   placeholder="Masukkan kata sandi lama Anda"
                                   class="w-full bg-white border-2 border-black px-3 py-2 pr-10 text-xs font-mono font-bold text-black focus:outline-hidden focus:ring-2 focus:ring-[#FFD43B]">
                            <button type="button" onclick="togglePasswordVisibility('current_password', 'icon_current')" 
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-black cursor-pointer text-xs">
                                <span id="icon_current">👁️</span>
                            </button>
                        </div>
                        @error('current_password')
                            <p class="text-[11px] font-bold text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Password Baru -->
                        <div>
                            <label class="block text-xs font-black uppercase text-black mb-1">
                                Kata Sandi Baru *
                            </label>
                            <div class="relative">
                                <input type="password" id="password" name="password" required minlength="8"
                                       placeholder="Minimal 8 karakter"
                                       class="w-full bg-white border-2 border-black px-3 py-2 pr-10 text-xs font-mono font-bold text-black focus:outline-hidden focus:ring-2 focus:ring-[#FFD43B]">
                                <button type="button" onclick="togglePasswordVisibility('password', 'icon_new')" 
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-black cursor-pointer text-xs">
                                    <span id="icon_new">👁️</span>
                                </button>
                            </div>
                            @error('password')
                                <p class="text-[11px] font-bold text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Konfirmasi Password Baru -->
                        <div>
                            <label class="block text-xs font-black uppercase text-black mb-1">
                                Konfirmasi Kata Sandi Baru *
                            </label>
                            <div class="relative">
                                <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8"
                                       placeholder="Ketik ulang kata sandi baru"
                                       class="w-full bg-white border-2 border-black px-3 py-2 pr-10 text-xs font-mono font-bold text-black focus:outline-hidden focus:ring-2 focus:ring-[#FFD43B]">
                                <button type="button" onclick="togglePasswordVisibility('password_confirmation', 'icon_confirm')" 
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-black cursor-pointer text-xs">
                                    <span id="icon_confirm">👁️</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 bg-[#E7F5FF] border-2 border-black rounded-sm text-[11px] font-semibold text-blue-950">
                        🛡️ <b>Tips Keamanan:</b> Gunakan minimal 8 karakter dengan kombinasi huruf besar, kecil, angka, dan simbol untuk perlindungan akun maksimal.
                    </div>

                    <!-- Action Button -->
                    <div class="pt-3 border-t-2 border-black/10 flex justify-end">
                        <button type="submit" class="neo-btn bg-[#FFD43B] text-black text-xs font-black px-5 py-2.5 flex items-center gap-1.5 hover:bg-yellow-400 cursor-pointer shadow-sm">
                            <span>🔑</span> Perbarui Kata Sandi
                        </button>
                    </div>
                </form>
            </div>

        </div>

    </div>
</div>

<!-- Modal Crop Foto Profil -->
<div id="cropModal" class="fixed inset-0 z-50 bg-black/75 hidden flex items-center justify-center p-3 sm:p-4">
    <div class="bg-white neo-box-lg w-full max-w-xl overflow-hidden flex flex-col max-h-[92vh] animate-in fade-in zoom-in duration-200">
        <!-- Header -->
        <div class="bg-[#FFD43B] p-4 text-black border-b-2 border-black flex items-center justify-between shrink-0">
            <h3 class="font-heading font-black text-sm uppercase flex items-center gap-2">
                <span>✂️</span> Sesuaikan & Potong Foto Profil
            </h3>
            <button type="button" onclick="cancelCrop()" class="font-black text-lg hover:opacity-75 cursor-pointer p-1">✕</button>
        </div>

        <!-- Viewport Gambar -->
        <div class="p-4 bg-slate-900 flex-1 min-h-[280px] max-h-[50vh] flex items-center justify-center overflow-hidden">
            <div class="w-full h-full max-h-[46vh] flex items-center justify-center">
                <img id="cropperImage" src="" alt="Potong Foto" class="max-w-full block">
            </div>
        </div>

        <!-- Toolbar Kontrol (Zoom, Rotate, Reset) -->
        <div class="p-3 bg-[#FFF9DB] border-t-2 border-b-2 border-black flex flex-wrap items-center justify-between gap-2 shrink-0 text-xs">
            <div class="flex items-center gap-1.5">
                <button type="button" onclick="cropperZoom(0.1)" class="neo-btn bg-white hover:bg-slate-100 text-black px-2.5 py-1 text-xs font-bold" title="Perbesar">
                    🔍 +
                </button>
                <button type="button" onclick="cropperZoom(-0.1)" class="neo-btn bg-white hover:bg-slate-100 text-black px-2.5 py-1 text-xs font-bold" title="Perkecil">
                    🔍 -
                </button>
                <button type="button" onclick="cropperRotate(-90)" class="neo-btn bg-white hover:bg-slate-100 text-black px-2.5 py-1 text-xs font-bold" title="Putar Kiri 90°">
                    ↺ 90°
                </button>
                <button type="button" onclick="cropperRotate(90)" class="neo-btn bg-white hover:bg-slate-100 text-black px-2.5 py-1 text-xs font-bold" title="Putar Kanan 90°">
                    ↻ 90°
                </button>
                <button type="button" onclick="cropperReset()" class="neo-btn bg-white hover:bg-slate-100 text-black px-2.5 py-1 text-xs font-bold" title="Reset Posisi">
                    🔄 Reset
                </button>
            </div>
            <span class="text-[10px] font-mono font-bold bg-white text-blue-900 px-2 py-0.5 border border-black rounded">
                Rasio: 1:1 (Profil Bulat)
            </span>
        </div>

        <!-- Footer Tombol Aksi -->
        <div class="p-3.5 sm:p-4 bg-white flex items-center justify-end gap-2.5 shrink-0">
            <button type="button" onclick="cancelCrop()" class="neo-btn bg-slate-200 text-black text-xs font-bold px-4 py-2 hover:bg-slate-300 cursor-pointer">
                Batal
            </button>
            <button type="button" onclick="applyCrop()" class="neo-btn bg-[#20C997] text-black text-xs font-black px-5 py-2 hover:bg-emerald-400 cursor-pointer flex items-center gap-1.5">
                <span>✂️</span> Terapkan Potongan Foto
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/cropperjs/cropper.min.js') }}"></script>
<script>
    let cropperInstance = null;
    let rawImageSource = null;

    function handleTeacherPhotoChange(input) {
        const file = input.files[0];
        const removeCb = document.getElementById('removePhotoCheckbox');
        if (removeCb) removeCb.checked = false;

        if (!file) return;

        // Check size (5MB = 5 * 1024 * 1024 bytes)
        if (file.size > 5 * 1024 * 1024) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'File Terlalu Besar',
                    text: 'Ukuran foto profil tidak boleh melebihi 5MB.',
                    confirmButtonColor: '#FF6B6B'
                });
            } else {
                alert('Ukuran foto profil tidak boleh melebihi 5MB.');
            }
            input.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            rawImageSource = e.target.result;
            openCropModal(rawImageSource);
        };
        reader.readAsDataURL(file);
    }

    function openCropModal(imageSrc) {
        const modal = document.getElementById('cropModal');
        const cropperImg = document.getElementById('cropperImage');

        if (!modal || !cropperImg) return;

        cropperImg.src = imageSrc;
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');

        if (cropperInstance) {
            cropperInstance.destroy();
            cropperInstance = null;
        }

        // Initialize Cropper.js
        cropperInstance = new Cropper(cropperImg, {
            aspectRatio: 1,
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 0.85,
            restore: false,
            guides: true,
            center: true,
            highlight: false,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false,
        });
    }

    function cropperZoom(ratio) {
        if (cropperInstance) cropperInstance.zoom(ratio);
    }

    function cropperRotate(deg) {
        if (cropperInstance) cropperInstance.rotate(deg);
    }

    function cropperReset() {
        if (cropperInstance) cropperInstance.reset();
    }

    function cancelCrop() {
        const modal = document.getElementById('cropModal');
        if (modal) modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');

        if (cropperInstance) {
            cropperInstance.destroy();
            cropperInstance = null;
        }

        const croppedInput = document.getElementById('photoCroppedInput');
        // Reset file input if no photo was ever cropped
        if (!croppedInput || !croppedInput.value) {
            const input = document.getElementById('teacherPhotoInput');
            if (input) input.value = '';
        }
    }

    function applyCrop() {
        if (!cropperInstance) return;

        const canvas = cropperInstance.getCroppedCanvas({
            width: 500,
            height: 500,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        });

        if (!canvas) return;

        const dataUrl = canvas.toDataURL('image/jpeg', 0.92);

        // Set hidden input value for base64
        const croppedInput = document.getElementById('photoCroppedInput');
        if (croppedInput) croppedInput.value = dataUrl;

        // Update live preview in form
        const previewImg = document.getElementById('avatarPreviewImage');
        const previewEmoji = document.getElementById('avatarPreviewEmoji');
        const btnRecrop = document.getElementById('btnRecrop');

        if (previewImg) {
            previewImg.src = dataUrl;
            previewImg.classList.remove('hidden');
        }
        if (previewEmoji) {
            previewEmoji.classList.add('hidden');
        }
        if (btnRecrop) {
            btnRecrop.classList.remove('hidden');
        }

        // Close modal
        cancelCrop();
    }

    function reopenCropper() {
        if (rawImageSource) {
            openCropModal(rawImageSource);
        }
    }

    function toggleRemoveTeacherPhoto(cb) {
        const input = document.getElementById('teacherPhotoInput');
        const croppedInput = document.getElementById('photoCroppedInput');
        const previewImg = document.getElementById('avatarPreviewImage');
        const previewEmoji = document.getElementById('avatarPreviewEmoji');
        const btnRecrop = document.getElementById('btnRecrop');

        if (cb.checked) {
            if (input) input.value = '';
            if (croppedInput) croppedInput.value = '';
            if (previewImg) previewImg.classList.add('hidden');
            if (previewEmoji) previewEmoji.classList.remove('hidden');
            if (btnRecrop) btnRecrop.classList.add('hidden');
        } else {
            // Reset to original photo if available
            if (previewImg && previewImg.getAttribute('data-original-src')) {
                previewImg.src = previewImg.getAttribute('data-original-src');
                previewImg.classList.remove('hidden');
                if (previewEmoji) previewEmoji.classList.add('hidden');
            }
        }
    }

    function togglePasswordVisibility(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.textContent = '🙈';
        } else {
            input.type = 'password';
            icon.textContent = '👁️';
        }
    }

    function confirmUpdateProfile(e) {
        e.preventDefault();
        const form = e.target;
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Simpan Perubahan Biodata?',
                text: 'Pastikan nama dan email yang Anda masukkan sudah benar.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#20C997',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Simpan',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        } else {
            if (confirm('Simpan perubahan biodata profil Anda?')) {
                form.submit();
            }
        }
        return false;
    }

    function confirmUpdatePassword(e) {
        e.preventDefault();
        const form = e.target;
        const pass = document.getElementById('password').value;
        const passConfirm = document.getElementById('password_confirmation').value;

        if (pass !== passConfirm) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Kata Sandi Tidak Cocok',
                    text: 'Konfirmasi kata sandi baru tidak sesuai dengan kata sandi baru.',
                    confirmButtonColor: '#FF6B6B'
                });
            } else {
                alert('Konfirmasi kata sandi baru tidak sesuai dengan kata sandi baru.');
            }
            return false;
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Perbarui Kata Sandi?',
                text: 'Anda akan diminta menggunakan kata sandi baru pada sesi login berikutnya.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#FFD43B',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Perbarui',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        } else {
            if (confirm('Perbarui kata sandi akun Anda?')) {
                form.submit();
            }
        }
        return false;
    }
</script>
@endpush
