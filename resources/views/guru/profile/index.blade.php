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

@php
    $initialTab = request()->query('tab', ($errors->has('current_password') || $errors->has('password') || $errors->has('password_confirmation')) ? 'keamanan' : 'biodata');
    $teachingClassesCount = $schedules->pluck('school_class_id')->unique()->count();
    $teachingSubjectsCount = $schedules->pluck('subject_id')->unique()->count();
@endphp

@section('content')
<div class="space-y-6">
    <!-- Header Hero Profile Banner -->
    <div class="bg-[#FFF3BF] border-3 border-black p-5 sm:p-7 neo-box relative overflow-hidden">
        <!-- Background Decorative Watermark -->
        <div class="absolute -right-6 -bottom-8 opacity-10 select-none pointer-events-none font-heading font-black text-8xl text-black">
            GURU
        </div>

        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <!-- Left Info: Avatar & Profile Details -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 sm:gap-5 min-w-0">
                <!-- Avatar with Quick Photo Trigger -->
                <div class="relative group shrink-0">
                    <div class="w-20 h-20 sm:w-24 sm:h-24 bg-white border-3 border-black rounded-full overflow-hidden flex items-center justify-center shadow-[3px_3px_0px_0px_#000]">
                        @if(!empty($teacher->photo) && \Illuminate\Support\Facades\Storage::disk('public')->exists($teacher->photo))
                            <img src="{{ asset('storage/' . $teacher->photo) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-[#5294FF] flex items-center justify-center text-4xl sm:text-5xl">
                                👨‍🏫
                            </div>
                        @endif
                    </div>
                    <button type="button" onclick="document.getElementById('teacherPhotoInput').click()" 
                            class="absolute -bottom-1 -right-1 bg-[#FFD43B] hover:bg-yellow-400 border-2 border-black rounded-full p-1.5 shadow-[2px_2px_0px_0px_#000] cursor-pointer"
                            title="Ganti Foto Profil">
                        <svg class="w-3.5 h-3.5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </button>
                </div>

                <!-- Identity Text & Badges -->
                <div class="space-y-1.5 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="neo-badge bg-[#5294FF] text-white text-[10px] font-black uppercase py-0.5">
                            PROFIL SAYA
                        </span>
                        <span class="neo-badge bg-black text-white text-[10px] font-black uppercase py-0.5">
                            DEWAN GURU
                        </span>
                        @if($homeroomClasses->isNotEmpty())
                            <span class="neo-badge bg-[#20C997] text-black text-[10px] font-black uppercase py-0.5">
                                Wali Kelas {{ $homeroomClasses->pluck('name')->implode(', ') }}
                            </span>
                        @endif
                        @if($user->isSavingsOfficer())
                            <span class="neo-badge bg-[#FFD43B] text-black text-[10px] font-black uppercase py-0.5">
                                Pengelola Tabungan
                            </span>
                        @endif
                    </div>

                    <h1 class="font-heading font-black text-xl sm:text-2xl text-black truncate leading-tight">
                        {{ $user->name }}
                    </h1>

                    <div class="flex items-center gap-2 sm:gap-3 flex-wrap text-xs font-semibold text-slate-800">
                        <span class="bg-white border border-black px-2 py-0.5 font-mono text-[11px] shadow-[1px_1px_0px_0px_#000]">
                            NIP: {{ $teacher->nip ?? '-' }}
                        </span>
                        @if(!empty($teacher->nuptk))
                            <span class="bg-white border border-black px-2 py-0.5 font-mono text-[11px] shadow-[1px_1px_0px_0px_#000]">
                                NUPTK: {{ $teacher->nuptk }}
                            </span>
                        @endif
                        <span class="text-slate-600 flex items-center gap-1">
                            ✉️ {{ $user->email ?: 'Email belum diatur' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right Quick Stats & CTA -->
            <div class="flex flex-wrap md:flex-col items-start md:items-end gap-2 w-full md:w-auto shrink-0 border-t-2 md:border-t-0 border-black/15 pt-3 md:pt-0">
                <div class="flex items-center gap-2">
                    <div class="bg-white border-2 border-black px-3 py-1.5 text-center shadow-[2px_2px_0px_0px_#000]">
                        <span class="text-[9px] font-black uppercase text-slate-500 block">Sesi KBM</span>
                        <span class="font-mono font-black text-sm text-black">{{ $schedules->count() }} Jam/Mgg</span>
                    </div>
                    <div class="bg-white border-2 border-black px-3 py-1.5 text-center shadow-[2px_2px_0px_0px_#000]">
                        <span class="text-[9px] font-black uppercase text-slate-500 block">Rombel</span>
                        <span class="font-mono font-black text-sm text-black">{{ $teachingClassesCount }} Kelas</span>
                    </div>
                </div>
                <a href="{{ route('guru.jadwal') }}" class="neo-btn bg-white hover:bg-slate-100 text-black text-xs font-black px-3.5 py-1.5 flex items-center gap-1.5 shadow-[2px_2px_0px_0px_#000]">
                    <span>📅</span> Buka Jadwal KBM →
                </a>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs (Struktur Rapi & Terorganisir) -->
    <div class="flex border-b-2 border-black gap-2 overflow-x-auto pb-0.5">
        <button type="button" onclick="switchProfileTab('biodata')" id="tabBtn-biodata"
            class="px-5 py-2.5 font-heading font-black text-xs uppercase border-t-2 border-x-2 border-black transition-all cursor-pointer whitespace-nowrap
            {{ $initialTab === 'biodata' ? 'bg-white -mb-[2px] border-b-2 border-b-white z-10 text-black shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
            👤 Biodata & Informasi Diri
        </button>
        <button type="button" onclick="switchProfileTab('keamanan')" id="tabBtn-keamanan"
            class="px-5 py-2.5 font-heading font-black text-xs uppercase border-t-2 border-x-2 border-black transition-all cursor-pointer whitespace-nowrap
            {{ $initialTab === 'keamanan' ? 'bg-white -mb-[2px] border-b-2 border-b-white z-10 text-black shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
            🔐 Keamanan & Kata Sandi
        </button>
        <button type="button" onclick="switchProfileTab('penugasan')" id="tabBtn-penugasan"
            class="px-5 py-2.5 font-heading font-black text-xs uppercase border-t-2 border-x-2 border-black transition-all cursor-pointer whitespace-nowrap
            {{ $initialTab === 'penugasan' ? 'bg-white -mb-[2px] border-b-2 border-b-white z-10 text-black shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
            📋 Penugasan & Jam Mengajar ({{ $schedules->count() }})
        </button>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 1: BIODATA & INFORMASI DIRI -->
    <!-- ========================================================================= -->
    <div id="tabContent-biodata" class="space-y-6 {{ $initialTab === 'biodata' ? '' : 'hidden' }}">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Kolom Formulir Utama (8 Kolom) -->
            <div class="lg:col-span-8 bg-white neo-box p-6 space-y-5">
                <div class="border-b-2 border-black pb-3 flex items-center justify-between">
                    <div>
                        <h2 class="font-heading font-black text-base uppercase text-black flex items-center gap-2">
                            <span>👤</span> Pengaturan Biodata Guru
                        </h2>
                        <p class="text-xs font-medium text-slate-600 mt-0.5">
                            Perbarui nama lengkap, email login, nomor kontak, serta foto profil Anda.
                        </p>
                    </div>
                </div>

                <form id="formUpdateProfile" action="{{ route('guru.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5" onsubmit="return confirmUpdateProfile(event)">
                    @csrf
                    @method('PUT')

                    <!-- Grid 2 Kolom untuk Input -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Nama Lengkap -->
                        <div class="sm:col-span-2 space-y-1.5">
                            <label class="block text-xs font-black uppercase text-black">
                                Nama Lengkap Guru & Gelar <span class="text-rose-600">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                   placeholder="Contoh: Ahmad Fauzi, S.Pd.I"
                                   class="w-full px-3.5 py-2.5 neo-input text-xs font-bold text-black bg-white focus:ring-2 focus:ring-[#FFD43B]">
                            @error('name')
                                <p class="text-[11px] font-bold text-rose-600 mt-0.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Alamat Email -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-black uppercase text-black flex items-center justify-between">
                                <span>Alamat Email Login <span class="text-rose-600">*</span></span>
                                @if(empty($user->email))
                                    <span class="neo-badge bg-[#FFE3E3] text-rose-950 text-[9px]">Wajib Dilengkapi</span>
                                @endif
                            </label>
                            @if(empty($user->email))
                                <div class="bg-[#FFF9DB] border-2 border-black p-2.5 mb-2 text-xs font-bold text-amber-950 flex items-center gap-2">
                                    <span>⚠️</span>
                                    <span>Email Anda belum terdaftar. Silakan lengkapi email aktif Anda di bawah ini untuk pemulihan akun.</span>
                                </div>
                            @endif
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                   placeholder="nama@sekolah.sch.id"
                                   class="w-full px-3.5 py-2.5 neo-input text-xs font-mono font-bold text-black bg-white focus:ring-2 focus:ring-[#FFD43B]">
                            <p class="text-[10px] text-slate-500">Email untuk login alternatif dan pemulihan akun.</p>
                            @error('email')
                                <p class="text-[11px] font-bold text-rose-600 mt-0.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nomor Telepon / WhatsApp -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-black uppercase text-black">
                                No. Telepon / WhatsApp
                            </label>
                            <input type="text" name="phone" value="{{ old('phone', $teacher->phone) }}"
                                   placeholder="081234567890"
                                   class="w-full px-3.5 py-2.5 neo-input text-xs font-mono font-bold text-black bg-white focus:ring-2 focus:ring-[#FFD43B]">
                            <p class="text-[10px] text-slate-500">Nomor aktif untuk koordinasi dengan sekolah.</p>
                            @error('phone')
                                <p class="text-[11px] font-bold text-rose-600 mt-0.5">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Upload & Preview Foto Profil -->
                    <div class="p-4 bg-slate-50 border-2 border-black rounded-sm space-y-3">
                        <label class="block text-xs font-black uppercase text-black">
                            Foto Profil Pegawai
                        </label>
                        <input type="hidden" name="photo_cropped" id="photoCroppedInput">

                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                            <!-- Preview Avatar Box -->
                            <div class="w-16 h-16 bg-white border-2 border-black rounded-full overflow-hidden flex items-center justify-center shrink-0 shadow-[2px_2px_0px_0px_#000]" id="avatarPreviewContainer">
                                @if(!empty($teacher->photo) && \Illuminate\Support\Facades\Storage::disk('public')->exists($teacher->photo))
                                    <img src="{{ asset('storage/' . $teacher->photo) }}" id="avatarPreviewImage" data-original-src="{{ asset('storage/' . $teacher->photo) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                    <span id="avatarPreviewEmoji" class="text-2xl hidden">👨‍🏫</span>
                                @else
                                    <span id="avatarPreviewEmoji" class="text-2xl">👨‍🏫</span>
                                    <img src="" id="avatarPreviewImage" data-original-src="" alt="{{ $user->name }}" class="w-full h-full object-cover hidden">
                                @endif
                            </div>

                            <div class="flex-1 min-w-0 space-y-2 w-full">
                                <input type="file" name="photo" id="teacherPhotoInput" accept="image/jpeg,image/png,image/jpg,image/webp"
                                       onchange="handleTeacherPhotoChange(this)"
                                       class="block w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:border-2 file:border-black file:text-xs file:font-black file:bg-[#FFD43B] hover:file:bg-[#fcc419] file:cursor-pointer file:shadow-[1px_1px_0px_#000] cursor-pointer">
                                
                                <div class="flex items-center justify-between flex-wrap gap-2 text-[10px] text-slate-500">
                                    <span>Maksimal 5MB. Format: JPG, PNG, WEBP.</span>
                                    <div class="flex items-center gap-2">
                                        <button type="button" id="btnRecrop" onclick="reopenCropper()" class="hidden neo-btn bg-white hover:bg-slate-100 text-black px-2 py-0.5 text-[10px] font-bold border border-black cursor-pointer">
                                            ✂️ Potong Ulang
                                        </button>

                                        @if(!empty($teacher->photo))
                                            <label class="inline-flex items-center gap-1.5 cursor-pointer font-black text-rose-700 select-none">
                                                <input type="checkbox" name="remove_photo" value="1" id="removePhotoCheckbox" onchange="toggleRemoveTeacherPhoto(this)" class="w-3.5 h-3.5 rounded border border-black text-rose-600 focus:ring-0 cursor-pointer">
                                                <span>Hapus Foto</span>
                                            </label>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Submit Button -->
                    <div class="pt-2 flex justify-end">
                        <button type="submit" class="neo-btn bg-[#20C997] hover:bg-emerald-400 text-black text-xs font-black px-6 py-2.5 flex items-center gap-1.5 cursor-pointer shadow-[3px_3px_0px_0px_#000]">
                            <span>💾</span> Simpan Biodata Profil
                        </button>
                    </div>
                </form>
            </div>

            <!-- Kolom Data Pokok Kepegawaian (4 Kolom - Readonly Info) -->
            <div class="lg:col-span-4 bg-white neo-box p-6 space-y-4">
                <div class="border-b-2 border-black pb-2.5">
                    <h3 class="font-heading font-black text-sm uppercase text-black flex items-center gap-2">
                        <span>🏛️</span> Data Pokok Pegawai
                    </h3>
                    <p class="text-[11px] font-semibold text-slate-500">Data resmi dari SIMPATIKA / EMIS Madrasah</p>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div class="p-2.5 bg-slate-50 border-2 border-black rounded-sm flex items-center justify-between">
                        <span class="font-bold text-slate-600">NIP:</span>
                        <span class="font-mono font-black text-black">{{ $teacher->nip ?? '-' }}</span>
                    </div>

                    <div class="p-2.5 bg-slate-50 border-2 border-black rounded-sm flex items-center justify-between">
                        <span class="font-bold text-slate-600">NUPTK:</span>
                        <span class="font-mono font-black text-black">{{ $teacher->nuptk ?? '-' }}</span>
                    </div>

                    <div class="p-2.5 bg-slate-50 border-2 border-black rounded-sm flex items-center justify-between">
                        <span class="font-bold text-slate-600">Jenis Kelamin:</span>
                        <span class="font-bold text-black">
                            {{ $teacher->gender === 'L' ? 'Laki-laki' : ($teacher->gender === 'P' ? 'Perempuan' : '-') }}
                        </span>
                    </div>

                    <div class="p-2.5 bg-slate-50 border-2 border-black rounded-sm flex items-center justify-between">
                        <span class="font-bold text-slate-600">Tempat, Tgl Lahir:</span>
                        <span class="font-semibold text-black text-right">
                            {{ $teacher->birth_place ?: '-' }}@if($teacher->birth_date), {{ $teacher->birth_date->format('d/m/Y') }}@endif
                        </span>
                    </div>

                    <div class="p-2.5 bg-slate-50 border-2 border-black rounded-sm flex items-center justify-between">
                        <span class="font-bold text-slate-600">Pendidikan Terakhir:</span>
                        <span class="neo-badge bg-[#E7F5FF] text-blue-950 text-[10px] font-black">
                            {{ $teacher->last_education ?: '-' }}
                        </span>
                    </div>
                </div>

                <div class="bg-[#FFF9DB] border-2 border-black p-3 rounded-sm text-[11px] font-medium text-amber-950 space-y-1">
                    <div class="font-black flex items-center gap-1.5">
                        <span>ℹ️</span> Catatan Sinkronisasi:
                    </div>
                    <p>
                        Data NIP, NUPTK, dan riwayat pendidikan dikelola secara terpusat oleh Administrator. Hubungi Tata Usaha jika data membutuhkan penyesuaian.
                    </p>
                </div>
            </div>

        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 2: KEAMANAN & KATA SANDI -->
    <!-- ========================================================================= -->
    <div id="tabContent-keamanan" class="space-y-6 {{ $initialTab === 'keamanan' ? '' : 'hidden' }}">
        <div class="max-w-3xl mx-auto bg-white neo-box p-6 sm:p-8 space-y-6">
            <div class="border-b-2 border-black pb-3">
                <h2 class="font-heading font-black text-base sm:text-lg uppercase text-black flex items-center gap-2">
                    <span>🔐</span> Perbarui Kata Sandi Akun
                </h2>
                <p class="text-xs font-medium text-slate-600 mt-1">
                    Ganti kata sandi secara berkala untuk menjaga keamanan akun dan kerahasiaan data siswa.
                </p>
            </div>

            <form id="formUpdatePassword" action="{{ route('guru.profile.password') }}" method="POST" class="space-y-5" onsubmit="return confirmUpdatePassword(event)">
                @csrf
                @method('PUT')

                <!-- Password Saat Ini -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-black uppercase text-black">
                        Kata Sandi Saat Ini <span class="text-rose-600">*</span>
                    </label>
                    <div class="relative">
                        <input type="password" id="current_password" name="current_password" required
                               placeholder="Masukkan kata sandi lama Anda"
                               class="w-full pl-3.5 pr-10 py-2.5 neo-input text-xs font-mono font-bold text-black bg-white focus:ring-2 focus:ring-[#FFD43B]">
                        <button type="button" onclick="togglePasswordVisibility('current_password', 'icon_current')" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-black cursor-pointer text-xs"
                                title="Lihat/Sembunyikan Kata Sandi">
                            <span id="icon_current">👁️</span>
                        </button>
                    </div>
                    @error('current_password')
                        <p class="text-[11px] font-bold text-rose-600 mt-0.5">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Password Baru -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-black uppercase text-black">
                            Kata Sandi Baru <span class="text-rose-600">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required minlength="8"
                                   placeholder="Minimal 8 karakter"
                                   class="w-full pl-3.5 pr-10 py-2.5 neo-input text-xs font-mono font-bold text-black bg-white focus:ring-2 focus:ring-[#FFD43B]">
                            <button type="button" onclick="togglePasswordVisibility('password', 'icon_new')" 
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-black cursor-pointer text-xs"
                                    title="Lihat/Sembunyikan Kata Sandi">
                                <span id="icon_new">👁️</span>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-[11px] font-bold text-rose-600 mt-0.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Konfirmasi Password Baru -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-black uppercase text-black">
                            Konfirmasi Kata Sandi Baru <span class="text-rose-600">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8"
                                   placeholder="Ulangi kata sandi baru"
                                   class="w-full pl-3.5 pr-10 py-2.5 neo-input text-xs font-mono font-bold text-black bg-white focus:ring-2 focus:ring-[#FFD43B]">
                            <button type="button" onclick="togglePasswordVisibility('password_confirmation', 'icon_confirm')" 
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-black cursor-pointer text-xs"
                                    title="Lihat/Sembunyikan Kata Sandi">
                                <span id="icon_confirm">👁️</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Petunjuk Keamanan -->
                <div class="p-3.5 bg-[#E7F5FF] border-2 border-black rounded-sm text-xs text-blue-950 space-y-1">
                    <div class="font-black flex items-center gap-1.5">
                        <span>🛡️</span> Pedoman Kata Sandi yang Kuat:
                    </div>
                    <ul class="list-disc list-inside text-[11px] font-semibold text-blue-900 space-y-0.5 pl-1">
                        <li>Minimal 8 karakter.</li>
                        <li>Kombinasikan huruf kapital, huruf kecil, dan angka.</li>
                        <li>Hindari menggunakan tanggal lahir atau nomor telepon pribadi.</li>
                    </ul>
                </div>

                <!-- Tombol Submit -->
                <div class="pt-2 flex justify-end">
                    <button type="submit" class="neo-btn bg-[#FFD43B] hover:bg-yellow-400 text-black text-xs font-black px-6 py-2.5 flex items-center gap-1.5 cursor-pointer shadow-[3px_3px_0px_0px_#000]">
                        <span>🔑</span> Perbarui Kata Sandi Akun
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 3: PENUGASAN & JADWAL MENGAJAR -->
    <!-- ========================================================================= -->
    <div id="tabContent-penugasan" class="space-y-6 {{ $initialTab === 'penugasan' ? '' : 'hidden' }}">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Ringkasan Penugasan Wali Kelas (4 Kolom) -->
            <div class="lg:col-span-4 space-y-4">
                <div class="bg-white neo-box p-5 space-y-3.5">
                    <div class="border-b-2 border-black pb-2.5 flex items-center justify-between">
                        <h3 class="font-heading font-black text-sm uppercase text-black flex items-center gap-1.5">
                            <span>🎓</span> Wali Kelas Binaan
                        </h3>
                        <span class="text-[10px] font-black {{ $homeroomClasses->isNotEmpty() ? 'bg-[#D3F9D8] text-emerald-950' : 'bg-slate-100 text-slate-600' }} px-2 py-0.5 rounded border border-black">
                            {{ $homeroomClasses->isNotEmpty() ? 'AKTIF' : 'NON-WALI' }}
                        </span>
                    </div>

                    @if($homeroomClasses->isNotEmpty())
                        <div class="space-y-3">
                            @foreach($homeroomClasses as $hClass)
                                <div class="p-3.5 bg-[#D3F9D8] border-2 border-black rounded-sm space-y-1.5">
                                    <div class="flex items-center justify-between">
                                        <div class="font-heading font-black text-base text-emerald-950">
                                            {{ $hClass->name }}
                                        </div>
                                        <span class="neo-badge bg-black text-white text-[9px]">Tingkat {{ $hClass->level }}</span>
                                    </div>
                                    <p class="text-[11px] font-medium text-emerald-900">
                                        Tahun Ajaran: <b>{{ $hClass->academicYear->name ?? 'Aktif' }}</b>
                                    </p>
                                    <div class="pt-1 flex items-center gap-2">
                                        <a href="{{ route('guru.classes.binaan') }}" class="neo-btn bg-white hover:bg-slate-100 text-black text-[11px] font-bold px-3 py-1 flex items-center gap-1 shadow-[2px_2px_0px_0px_#000]">
                                            Buka Kelas Binaan →
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6 px-4 bg-slate-50 border-2 border-dashed border-slate-300 rounded-sm space-y-1">
                            <span class="text-2xl block">🏫</span>
                            <div class="text-xs font-bold text-slate-700">Tidak Menjabat Wali Kelas</div>
                            <p class="text-[11px] text-slate-500">Saat ini akun Anda tidak ditugaskan sebagai wali kelas rombel.</p>
                        </div>
                    @endif
                </div>

                <!-- Informasi Rekap Beban Mengajar -->
                <div class="bg-[#E7F5FF] neo-box p-5 space-y-3 border-3 border-black">
                    <div class="font-heading font-black text-sm uppercase text-blue-950 flex items-center gap-1.5">
                        <span>📊</span> Beban Jam Tatap Muka
                    </div>
                    <div class="space-y-2 text-xs font-semibold text-blue-950">
                        <div class="flex items-center justify-between pb-1 border-b border-black/10">
                            <span>Total Sesi Mingguan:</span>
                            <span class="font-mono font-black text-sm">{{ $schedules->count() }} Jam KBM</span>
                        </div>
                        <div class="flex items-center justify-between pb-1 border-b border-black/10">
                            <span>Jumlah Rombel Diampu:</span>
                            <span class="font-mono font-black text-sm">{{ $teachingClassesCount }} Kelas</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Mata Pelajaran:</span>
                            <span class="font-mono font-black text-sm">{{ $teachingSubjectsCount }} Mapel</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Matriks Sesi Mengajar Mingguan (8 Kolom) -->
            <div class="lg:col-span-8 bg-white neo-box p-6 space-y-4">
                <div class="border-b-2 border-black pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h2 class="font-heading font-black text-base uppercase text-black flex items-center gap-2">
                            <span>📅</span> Jadwal Mengajar Mingguan
                        </h2>
                        <p class="text-xs font-medium text-slate-600 mt-0.5">
                            Rincian jam tatap muka yang dijadwalkan untuk Anda per hari.
                        </p>
                    </div>
                    <a href="{{ route('guru.jadwal') }}" class="neo-btn bg-[#FFD43B] hover:bg-yellow-400 text-black text-xs font-black px-3.5 py-1.5 flex items-center gap-1 self-start sm:self-auto shadow-[2px_2px_0px_0px_#000]">
                        <span>🔍</span> Matriks KBM Lengkap →
                    </a>
                </div>

                @if(empty($schedulesByDay))
                    <div class="text-center py-12 px-4 bg-slate-50 border-2 border-dashed border-slate-300 rounded-sm space-y-2">
                        <span class="text-4xl block">☕</span>
                        <h4 class="font-heading font-bold text-sm text-slate-700">Belum Ada Jadwal Mengajar</h4>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto">
                            Jadwal KBM dikelola terpusat oleh administrator sekolah. Silakan hubungi admin untuk pembagian jam mengajar.
                        </p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($schedulesByDay as $dayName => $daySchedules)
                            <div class="bg-slate-50 border-2 border-black rounded-sm p-3.5 space-y-2.5">
                                <div class="flex items-center justify-between border-b-2 border-black pb-1.5">
                                    <span class="font-heading font-black text-xs uppercase text-black flex items-center gap-1.5">
                                        <span class="w-2 h-2 bg-black rounded-full inline-block"></span>
                                        {{ $dayName }}
                                    </span>
                                    <span class="text-[10px] font-mono font-bold bg-white border border-black px-1.5 py-0.2">
                                        {{ count($daySchedules) }} Sesi
                                    </span>
                                </div>

                                <div class="space-y-2">
                                    @foreach($daySchedules as $sch)
                                        <div class="p-2.5 bg-white border-2 border-black rounded-sm space-y-1 shadow-[2px_2px_0px_0px_#000]">
                                            <div class="flex items-center justify-between gap-1">
                                                <span class="font-mono text-[10px] font-black bg-slate-100 border border-black px-1.5 py-0.5">
                                                    {{ substr($sch->start_time, 0, 5) }} - {{ substr($sch->end_time, 0, 5) }}
                                                </span>
                                                <span class="neo-badge bg-[#5294FF] text-white text-[9px] font-black">
                                                    {{ $sch->schoolClass->name ?? 'Kelas' }}
                                                </span>
                                            </div>
                                            <div class="font-heading font-black text-xs text-black leading-tight truncate">
                                                {{ $sch->subject->name ?? 'Mata Pelajaran' }}
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

    function switchProfileTab(tab) {
        const tabs = ['biodata', 'keamanan', 'penugasan'];
        tabs.forEach(t => {
            const content = document.getElementById('tabContent-' + t);
            const btn = document.getElementById('tabBtn-' + t);
            if (content && btn) {
                if (t === tab) {
                    content.classList.remove('hidden');
                    btn.className = "px-5 py-2.5 font-heading font-black text-xs uppercase border-t-2 border-x-2 border-black transition-all bg-white -mb-[2px] border-b-2 border-b-white z-10 text-black shadow-sm cursor-pointer whitespace-nowrap";
                } else {
                    content.classList.add('hidden');
                    btn.className = "px-5 py-2.5 font-heading font-black text-xs uppercase border-t-2 border-x-2 border-black transition-all bg-slate-100 text-slate-600 hover:bg-slate-200 cursor-pointer whitespace-nowrap";
                }
            }
        });

        // Update URL query tanpa reload
        const url = new URL(window.location);
        url.searchParams.set('tab', tab);
        window.history.replaceState({}, '', url);
    }

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
        if (!input) return;
        if (input.type === 'password') {
            input.type = 'text';
            if (icon) icon.textContent = '🙈';
        } else {
            input.type = 'password';
            if (icon) icon.textContent = '👁️';
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
