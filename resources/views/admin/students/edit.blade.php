@extends('layouts.admin')

@section('title', 'Edit Data Lengkap Siswa')

@php
    $studentName = $student->user?->name ?? 'Siswa';
    $initials = collect(explode(' ', trim($studentName)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
    if ($initials === '') {
        $initials = 'SW';
    }

    $hasStoredPhoto = ! empty($student->photo) && \Illuminate\Support\Facades\Storage::disk('public')->exists($student->photo);
    $isActive = (string) old('is_active', ($student->user?->is_active ?? true) ? '1' : '0');
    $currentReligion = old('religion', $student->religion);
    $religionOptions = ['Islam', 'Kristen Protestan', 'Katolik', 'Hindu', 'Buddha', 'Khonghucu'];
    $currentAuditStatus = old('audit_status', $student->audit_status ?: 'pending');
    $auditLabels = [
        'pending' => 'Menunggu Verifikasi',
        'verified' => 'Terverifikasi Valid',
        'flagged' => 'Bermasalah / Flagged',
    ];
    $dataSourceLabel = $student->data_source?->label() ?? 'Data Lama';
@endphp

@section('content')
<div class="mx-auto max-w-6xl space-y-6 pb-10">
    <!-- Header Halaman -->
    <div class="rounded-xl border-2 border-black bg-[#FFD43B] p-5 shadow-[5px_5px_0px_0px_#000]">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center rounded-md border-2 border-black bg-white px-2.5 py-0.5 text-[11px] font-black uppercase tracking-wide text-black shadow-[2px_2px_0px_0px_#000]">
                        Edit Data Lengkap Siswa
                    </span>
                    <span class="inline-flex items-center rounded-md border-2 border-black px-2.5 py-0.5 text-[11px] font-black uppercase tracking-wide text-black shadow-[2px_2px_0px_0px_#000] {{ ($student->user?->is_active ?? true) ? 'bg-[#D3F9D8]' : 'bg-[#FFE3E3]' }}">
                        {{ ($student->user?->is_active ?? true) ? 'Akun Aktif' : 'Akun Nonaktif' }}
                    </span>
                    @if($student->schoolClass)
                        <span class="inline-flex items-center rounded-md border-2 border-black bg-[#A5D8FF] px-2.5 py-0.5 text-[11px] font-black uppercase tracking-wide text-black shadow-[2px_2px_0px_0px_#000]">
                            Kelas {{ $student->schoolClass->name }}
                        </span>
                    @endif
                </div>
                <h1 class="text-xl font-black tracking-tight text-black sm:text-2xl">
                    {{ $studentName }}
                </h1>
                <div class="flex flex-wrap items-center gap-2 font-mono text-xs font-bold text-black/80">
                    <span class="rounded border border-black bg-white px-2 py-0.5">NIS: {{ $student->nis ?: '—' }}</span>
                    <span class="rounded border border-black bg-white px-2 py-0.5">NISN: {{ $student->nisn ?: '—' }}</span>
                    <span class="rounded border border-black bg-white px-2 py-0.5">NIK: {{ $student->nik ?: '—' }}</span>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('admin.students.index') }}"
                   class="inline-flex min-h-10 items-center gap-2 rounded-lg border-2 border-black bg-white px-4 py-2 text-xs font-extrabold uppercase tracking-wide text-black shadow-[3px_3px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:bg-slate-100 hover:shadow-[4px_4px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Kembali ke Daftar Siswa</span>
                </a>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.students.update', $student) }}"
          method="POST"
          enctype="multipart/form-data"
          class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:items-start">
            <!-- Kolom Kiri: Foto Profil, Akun, Audit & Info Sistem -->
            <div class="space-y-6 lg:col-span-4">
                <!-- Kartu Foto Siswa 3x4 -->
                <div class="rounded-xl border-2 border-black bg-white p-5 shadow-[5px_5px_0px_0px_#000]">
                    <h2 class="border-b-2 border-black pb-2.5 text-sm font-black uppercase tracking-wide text-black">
                        Foto Siswa (3×4)
                    </h2>

                    <div class="mt-4 flex flex-col items-center gap-4">
                        <div class="relative h-40 w-32 overflow-hidden rounded-xl border-2 border-black bg-[#FFF9DB] shadow-[4px_4px_0px_0px_#000]">
                            <img id="studentPhotoPreview"
                                 src="{{ $hasStoredPhoto ? asset('storage/' . $student->photo) : '' }}"
                                 alt="{{ $studentName }}"
                                 class="h-full w-full object-cover {{ $hasStoredPhoto ? '' : 'hidden' }}">
                            <div id="studentPhotoPlaceholder"
                                 class="flex h-full w-full flex-col items-center justify-center bg-[#A5D8FF] text-black {{ $hasStoredPhoto ? 'hidden' : '' }}">
                                <span class="text-3xl font-black">{{ $initials }}</span>
                                <span class="mt-1 text-[10px] font-extrabold uppercase tracking-wider text-black/70">Foto 3×4</span>
                            </div>
                        </div>

                        <div class="w-full space-y-2.5">
                            <label for="student_photo"
                                   class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-lg border-2 border-black bg-[#FFF3BF] px-3.5 py-2 text-xs font-extrabold uppercase text-black shadow-[2px_2px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:bg-[#FFD43B] hover:shadow-[3px_3px_0px_0px_#000]">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>Pilih / Ganti Foto</span>
                            </label>
                            <input type="file"
                                   id="student_photo"
                                   name="photo"
                                   accept="image/jpeg,image/png,image/jpg,image/webp"
                                   onchange="previewStudentPhoto(this)"
                                   class="hidden">

                            <p id="studentPhotoFilename" class="truncate text-center text-[11px] font-semibold text-slate-600">
                                Format JPG, PNG, atau WEBP (maks. 2MB)
                            </p>

                            @error('photo')
                                <p class="text-center text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror

                            @if($hasStoredPhoto)
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border-2 border-black bg-[#FFE3E3] px-3 py-2 text-xs font-extrabold text-black shadow-[2px_2px_0px_0px_#000] transition hover:bg-[#FFC9C9]">
                                    <input type="checkbox"
                                           id="remove_photo"
                                           name="remove_photo"
                                           value="1"
                                           {{ old('remove_photo') ? 'checked' : '' }}
                                           onchange="toggleRemoveStudentPhoto(this)"
                                           class="h-4 w-4 rounded border-2 border-black text-black focus:ring-0">
                                    <span>Hapus Foto Saat Ini</span>
                                </label>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Kartu Akun Login & Status -->
                <div class="rounded-xl border-2 border-black bg-white p-5 shadow-[5px_5px_0px_0px_#000]">
                    <h2 class="border-b-2 border-black pb-2.5 text-sm font-black uppercase tracking-wide text-black">
                        Akun Login & Status
                    </h2>

                    <div class="mt-4 space-y-4">
                        <div>
                            <label for="email" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">
                                Alamat Email Siswa
                            </label>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   value="{{ old('email', $student->user?->email) }}"
                                   placeholder="nis@{{ \App\Models\Student::getEmailDomain() }}"
                                   class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 font-mono text-sm font-semibold text-black placeholder:font-sans placeholder:font-normal placeholder:text-slate-400 shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none">
                            <p class="mt-1 text-[11px] font-medium text-slate-500">
                                Biarkan kosong jika ingin mempertahankan email login saat ini.
                            </p>
                            @error('email')
                                <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">
                                Password Baru (Opsional)
                            </label>
                            <div class="relative">
                                <input type="password"
                                       id="password"
                                       name="password"
                                       placeholder="Biarkan kosong jika tidak diubah"
                                       class="w-full rounded-lg border-2 border-black bg-white py-2 pl-3.5 pr-10 font-mono text-sm font-semibold text-black placeholder:font-sans placeholder:font-normal placeholder:text-slate-400 shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none">
                                <button type="button"
                                        onclick="toggleStudentPasswordVisibility()"
                                        class="absolute inset-y-0 right-0 flex cursor-pointer items-center pr-3 text-slate-600 transition hover:text-black focus:outline-none"
                                        title="Tampilkan / Sembunyikan Password">
                                    <span id="student_eye_icon">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </span>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="block text-xs font-extrabold uppercase tracking-wide text-black">
                                Status Akun <span class="text-rose-600">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-2.5">
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg border-2 border-black bg-white p-2.5 text-xs font-extrabold transition hover:bg-[#D3F9D8]/60 has-[:checked]:bg-[#D3F9D8] has-[:checked]:shadow-[2px_2px_0px_0px_#000]">
                                    <input type="radio"
                                           name="is_active"
                                           value="1"
                                           {{ $isActive === '1' ? 'checked' : '' }}
                                           class="h-4 w-4 border-2 border-black text-black focus:ring-0">
                                    <span>Aktif</span>
                                </label>
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg border-2 border-black bg-white p-2.5 text-xs font-extrabold transition hover:bg-[#FFE3E3]/60 has-[:checked]:bg-[#FFE3E3] has-[:checked]:shadow-[2px_2px_0px_0px_#000]">
                                    <input type="radio"
                                           name="is_active"
                                           value="0"
                                           {{ $isActive === '0' ? 'checked' : '' }}
                                           class="h-4 w-4 border-2 border-black text-black focus:ring-0">
                                    <span>Nonaktif</span>
                                </label>
                            </div>
                            @error('is_active')
                                <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Kartu Status & Catatan Audit -->
                <div class="rounded-xl border-2 border-black bg-white p-5 shadow-[5px_5px_0px_0px_#000]">
                    <h2 class="border-b-2 border-black pb-2.5 text-sm font-black uppercase tracking-wide text-black">
                        Status & Catatan Audit
                    </h2>

                    <div class="mt-4 space-y-4">
                        <div>
                            <label for="audit_status" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">
                                Status Audit Data
                            </label>
                            <select id="audit_status"
                                    name="audit_status"
                                    class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 text-sm font-semibold text-black shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:outline-none">
                                @foreach($auditLabels as $val => $label)
                                    <option value="{{ $val }}" {{ $currentAuditStatus === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('audit_status')
                                <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="audit_note" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">
                                Catatan Audit (Opsional)
                            </label>
                            <textarea id="audit_note"
                                      name="audit_note"
                                      rows="2"
                                      placeholder="Catatan hasil verifikasi atau keterangan tambahan..."
                                      class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 text-sm font-semibold text-black placeholder:font-normal placeholder:text-slate-400 shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none">{{ old('audit_note', $student->audit_note) }}</textarea>
                            @error('audit_note')
                                <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Kartu Info Sistem (Read-Only) -->
                <div class="rounded-xl border-2 border-black bg-[#FFFDF5] p-5 shadow-[5px_5px_0px_0px_#000]">
                    <h2 class="border-b-2 border-black pb-2.5 text-sm font-black uppercase tracking-wide text-black">
                        Info Sistem (Read-Only)
                    </h2>
                    <div class="mt-3.5 space-y-2.5 text-xs">
                        <div>
                            <div class="text-[10px] font-black uppercase tracking-wider text-slate-500">Kode QR Presensi</div>
                            <div class="mt-0.5 break-all rounded border border-black bg-white px-2.5 py-1 font-mono text-[11px] font-bold text-black">
                                {{ $student->qr_code_identifier }}
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <div class="text-[10px] font-black uppercase tracking-wider text-slate-500">Sumber Data</div>
                                <span class="mt-0.5 inline-flex items-center rounded border border-black bg-[#E7F5FF] px-2 py-0.5 text-[11px] font-extrabold text-black">
                                    {{ $dataSourceLabel }}
                                </span>
                            </div>
                            <div>
                                <div class="text-[10px] font-black uppercase tracking-wider text-slate-500">Sinkron Terakhir</div>
                                <div class="mt-0.5 font-mono text-[11px] font-bold text-slate-700">
                                    {{ $student->last_synced_at ? $student->last_synced_at->format('d-m-Y H:i') : '—' }}
                                </div>
                            </div>
                        </div>

                        @if($student->createdBy || $student->auditedBy)
                            <div class="space-y-1 border-t border-black/15 pt-2 text-[11px] font-medium text-slate-600">
                                @if($student->createdBy)
                                    <div>Dibuat oleh: <strong class="text-black">{{ $student->createdBy->name }}</strong></div>
                                @endif
                                @if($student->auditedBy)
                                    <div>Diaudit oleh: <strong class="text-black">{{ $student->auditedBy->name }}</strong> ({{ $student->audited_at?->format('d-m-Y H:i') ?? '-' }})</div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Seluruh Biodata Siswa -->
            <div class="space-y-6 lg:col-span-8">
                <!-- Kartu 1: Identitas Utama -->
                <div class="rounded-xl border-2 border-black bg-white p-6 shadow-[5px_5px_0px_0px_#000]">
                    <div class="border-b-2 border-black pb-3">
                        <h2 class="text-base font-black uppercase tracking-wide text-black">
                            1. Identitas Utama Siswa
                        </h2>
                        <p class="mt-0.5 text-xs font-semibold text-slate-600">
                            Data identitas pokok siswa sesuai database akademik.
                        </p>
                    </div>

                    <div class="mt-5 space-y-4">
                        <div>
                            <label for="name" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">
                                Nama Lengkap Siswa <span class="text-rose-600">*</span>
                            </label>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   value="{{ old('name', $student->user?->name) }}"
                                   required
                                   class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 text-sm font-semibold text-black shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none">
                            @error('name')
                                <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <label for="nis" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">
                                    NIS <span class="text-rose-600">*</span>
                                </label>
                                <input type="text"
                                       id="nis"
                                       name="nis"
                                       value="{{ old('nis', $student->nis) }}"
                                       required
                                       class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 font-mono text-sm font-semibold text-black shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none">
                                @error('nis')
                                    <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="nisn" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">NISN</label>
                                <input type="text"
                                       id="nisn"
                                       name="nisn"
                                       value="{{ old('nisn', $student->nisn) }}"
                                       placeholder="10 digit NISN"
                                       class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 font-mono text-sm font-semibold text-black placeholder:font-sans placeholder:font-normal placeholder:text-slate-400 shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none">
                                @error('nisn')
                                    <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="nik" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">NIK</label>
                                <input type="text"
                                       id="nik"
                                       name="nik"
                                       value="{{ old('nik', $student->nik) }}"
                                       placeholder="16 digit NIK"
                                       class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 font-mono text-sm font-semibold text-black placeholder:font-sans placeholder:font-normal placeholder:text-slate-400 shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none">
                                @error('nik')
                                    <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="school_class_id" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">
                                    Kelas / Rombel <span class="text-rose-600">*</span>
                                </label>
                                <select id="school_class_id"
                                        name="school_class_id"
                                        required
                                        class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 text-sm font-semibold text-black shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:outline-none">
                                    @foreach($classes as $cls)
                                        <option value="{{ $cls->id }}" {{ (int) old('school_class_id', $student->school_class_id) === (int) $cls->id ? 'selected' : '' }}>
                                            {{ $cls->name }} ({{ $cls->level }})@if($cls->academicYear) — {{ $cls->academicYear->name }}@endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('school_class_id')
                                    <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="gender" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">
                                    Jenis Kelamin <span class="text-rose-600">*</span>
                                </label>
                                <select id="gender"
                                        name="gender"
                                        required
                                        class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 text-sm font-semibold text-black shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:outline-none">
                                    <option value="L" {{ old('gender', $student->gender ?: 'L') === 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                                    <option value="P" {{ old('gender', $student->gender ?: 'L') === 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                                </select>
                                @error('gender')
                                    <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <label for="birth_place" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">Tempat Lahir</label>
                                <input type="text"
                                       id="birth_place"
                                       name="birth_place"
                                       value="{{ old('birth_place', $student->birth_place) }}"
                                       placeholder="Contoh: Jakarta"
                                       class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 text-sm font-semibold text-black placeholder:font-normal placeholder:text-slate-400 shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none">
                                @error('birth_place')
                                    <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="birth_date" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">Tanggal Lahir</label>
                                <input type="date"
                                       id="birth_date"
                                       name="birth_date"
                                       value="{{ old('birth_date', $student->birth_date ? $student->birth_date->format('Y-m-d') : '') }}"
                                       class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 font-mono text-sm font-semibold text-black shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:outline-none">
                                @error('birth_date')
                                    <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="religion" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">Agama</label>
                                <select id="religion"
                                        name="religion"
                                        class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 text-sm font-semibold text-black shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:outline-none">
                                    <option value="">-- Pilih Agama --</option>
                                    @if(! empty($currentReligion) && ! in_array($currentReligion, $religionOptions, true))
                                        <option value="{{ $currentReligion }}" selected>{{ $currentReligion }}</option>
                                    @endif
                                    @foreach($religionOptions as $rel)
                                        <option value="{{ $rel }}" {{ $currentReligion === $rel ? 'selected' : '' }}>{{ $rel }}</option>
                                    @endforeach
                                </select>
                                @error('religion')
                                    <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kartu 2: Kontak & Alamat -->
                <div class="rounded-xl border-2 border-black bg-white p-6 shadow-[5px_5px_0px_0px_#000]">
                    <div class="border-b-2 border-black pb-3">
                        <h2 class="text-base font-black uppercase tracking-wide text-black">
                            2. Kontak & Alamat Siswa
                        </h2>
                    </div>

                    <div class="mt-5 space-y-4">
                        <div>
                            <label for="phone" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">Nomor HP / WhatsApp Siswa</label>
                            <input type="text"
                                   id="phone"
                                   name="phone"
                                   value="{{ old('phone', $student->phone) }}"
                                   placeholder="08xxxxxxxxxx"
                                   class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 font-mono text-sm font-semibold text-black placeholder:font-sans placeholder:font-normal placeholder:text-slate-400 shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none">
                            @error('phone')
                                <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="address" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">Alamat Lengkap Tempat Tinggal Siswa</label>
                            <textarea id="address"
                                      name="address"
                                      rows="3"
                                      placeholder="Jl. Raya No..., RT/RW, Kelurahan, Kecamatan, Kota/Kabupaten"
                                      class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 text-sm font-semibold text-black placeholder:font-normal placeholder:text-slate-400 shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none">{{ old('address', $student->address) }}</textarea>
                            @error('address')
                                <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Kartu 3: Status Keluarga & Riwayat Masuk -->
                <div class="rounded-xl border-2 border-black bg-white p-6 shadow-[5px_5px_0px_0px_#000]">
                    <div class="border-b-2 border-black pb-3">
                        <h2 class="text-base font-black uppercase tracking-wide text-black">
                            3. Status Keluarga & Riwayat Masuk
                        </h2>
                    </div>

                    <div class="mt-5 space-y-4">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="family_status" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">Status dalam Keluarga</label>
                                <input type="text"
                                       id="family_status"
                                       name="family_status"
                                       value="{{ old('family_status', $student->family_status) }}"
                                       placeholder="Contoh: Anak Kandung, Anak Angkat"
                                       class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 text-sm font-semibold text-black placeholder:font-normal placeholder:text-slate-400 shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none">
                                @error('family_status')
                                    <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="child_number" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">Anak Ke-</label>
                                <input type="text"
                                       id="child_number"
                                       name="child_number"
                                       value="{{ old('child_number', $student->child_number) }}"
                                       placeholder="Contoh: 1, 2, dst."
                                       class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 font-mono text-sm font-semibold text-black placeholder:font-sans placeholder:font-normal placeholder:text-slate-400 shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none">
                                @error('child_number')
                                    <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="previous_school" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">Sekolah Asal</label>
                            <input type="text"
                                   id="previous_school"
                                   name="previous_school"
                                   value="{{ old('previous_school', $student->previous_school) }}"
                                   placeholder="Contoh: SD Negeri 01 Pagi"
                                   class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 text-sm font-semibold text-black placeholder:font-normal placeholder:text-slate-400 shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none">
                            @error('previous_school')
                                <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="admission_date" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">Tanggal Diterima di Sekolah</label>
                                <input type="date"
                                       id="admission_date"
                                       name="admission_date"
                                       value="{{ old('admission_date', $student->admission_date ? $student->admission_date->format('Y-m-d') : '') }}"
                                       class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 font-mono text-sm font-semibold text-black shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:outline-none">
                                @error('admission_date')
                                    <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="entry_grade" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">Diterima di Kelas / Tingkat Awal</label>
                                <input type="text"
                                       id="entry_grade"
                                       name="entry_grade"
                                       value="{{ old('entry_grade', $student->entry_grade) }}"
                                       placeholder="Contoh: VII, X, 1"
                                       class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 text-sm font-semibold text-black placeholder:font-normal placeholder:text-slate-400 shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none">
                                @error('entry_grade')
                                    <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kartu 4: Orang Tua Kandung & Wali Murid -->
                <div class="rounded-xl border-2 border-black bg-white p-6 shadow-[5px_5px_0px_0px_#000]">
                    <div class="border-b-2 border-black pb-3">
                        <h2 class="text-base font-black uppercase tracking-wide text-black">
                            4. Orang Tua Kandung & Wali Murid
                        </h2>
                    </div>

                    <div class="mt-5 space-y-5">
                        <!-- Orang Tua Kandung -->
                        <div class="space-y-4 rounded-xl border-2 border-black bg-[#F8FAFC] p-4 shadow-[3px_3px_0px_0px_#000]">
                            <div class="text-xs font-black uppercase tracking-wide text-black">Identitas Orang Tua Kandung</div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="father_name" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">Nama Lengkap Ayah</label>
                                    <input type="text"
                                           id="father_name"
                                           name="father_name"
                                           value="{{ old('father_name', $student->father_name) }}"
                                           placeholder="Nama Ayah"
                                           class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 text-sm font-semibold text-black placeholder:font-normal placeholder:text-slate-400 shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:outline-none">
                                    @error('father_name')
                                        <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="father_job" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">Pekerjaan Ayah</label>
                                    <input type="text"
                                           id="father_job"
                                           name="father_job"
                                           value="{{ old('father_job', $student->father_job) }}"
                                           placeholder="PNS, Wiraswasta, Buruh, dll."
                                           class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 text-sm font-semibold text-black placeholder:font-normal placeholder:text-slate-400 shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:outline-none">
                                    @error('father_job')
                                        <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="mother_name" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">Nama Lengkap Ibu</label>
                                    <input type="text"
                                           id="mother_name"
                                           name="mother_name"
                                           value="{{ old('mother_name', $student->mother_name) }}"
                                           placeholder="Nama Ibu"
                                           class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 text-sm font-semibold text-black placeholder:font-normal placeholder:text-slate-400 shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:outline-none">
                                    @error('mother_name')
                                        <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="mother_job" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">Pekerjaan Ibu</label>
                                    <input type="text"
                                           id="mother_job"
                                           name="mother_job"
                                           value="{{ old('mother_job', $student->mother_job) }}"
                                           placeholder="Ibu Rumah Tangga, Guru, dll."
                                           class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 text-sm font-semibold text-black placeholder:font-normal placeholder:text-slate-400 shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:outline-none">
                                    @error('mother_job')
                                        <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label for="parent_address" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">Alamat Lengkap Orang Tua</label>
                                <textarea id="parent_address"
                                          name="parent_address"
                                          rows="2"
                                          placeholder="Kosongkan jika sama dengan alamat siswa"
                                          class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 text-sm font-semibold text-black placeholder:font-normal placeholder:text-slate-400 shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:outline-none">{{ old('parent_address', $student->parent_address) }}</textarea>
                                @error('parent_address')
                                    <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Wali Murid -->
                        <div class="space-y-4 rounded-xl border-2 border-black bg-[#FFF9DB] p-4 shadow-[3px_3px_0px_0px_#000]">
                            <div class="text-xs font-black uppercase tracking-wide text-black">Identitas Wali Murid (Opsional)</div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="guardian_name" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">Nama Lengkap Wali</label>
                                    <input type="text"
                                           id="guardian_name"
                                           name="guardian_name"
                                           value="{{ old('guardian_name', $student->guardian_name) }}"
                                           placeholder="Nama Wali Siswa"
                                           class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 text-sm font-semibold text-black placeholder:font-normal placeholder:text-slate-400 shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:outline-none">
                                    @error('guardian_name')
                                        <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="guardian_job" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">Pekerjaan Wali</label>
                                    <input type="text"
                                           id="guardian_job"
                                           name="guardian_job"
                                           value="{{ old('guardian_job', $student->guardian_job) }}"
                                           placeholder="Pekerjaan Wali"
                                           class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 text-sm font-semibold text-black placeholder:font-normal placeholder:text-slate-400 shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:outline-none">
                                    @error('guardian_job')
                                        <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label for="guardian_address" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">Alamat Lengkap Wali</label>
                                <textarea id="guardian_address"
                                          name="guardian_address"
                                          rows="2"
                                          placeholder="Alamat tinggal wali murid"
                                          class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 text-sm font-semibold text-black placeholder:font-normal placeholder:text-slate-400 shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:outline-none">{{ old('guardian_address', $student->guardian_address) }}</textarea>
                                @error('guardian_address')
                                    <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Tombol Simpan -->
                <div class="flex flex-wrap items-center justify-end gap-3 rounded-xl border-2 border-black bg-white p-5 shadow-[5px_5px_0px_0px_#000]">
                    <a href="{{ route('admin.students.index') }}"
                       class="inline-flex min-h-11 items-center justify-center rounded-lg border-2 border-black bg-white px-5 py-2.5 text-xs font-extrabold uppercase tracking-wide text-black shadow-[3px_3px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:bg-slate-100 hover:shadow-[4px_4px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none">
                        Batal
                    </a>
                    <button type="submit"
                            class="inline-flex min-h-11 cursor-pointer items-center justify-center gap-2 rounded-lg border-2 border-black bg-[#20C997] px-6 py-2.5 text-xs font-black uppercase tracking-wide text-black shadow-[3px_3px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:bg-[#12B886] hover:shadow-[4px_4px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Simpan Perubahan Siswa</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function toggleStudentPasswordVisibility() {
        const input = document.getElementById('password');
        const iconContainer = document.getElementById('student_eye_icon');
        if (!input || !iconContainer) return;

        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';

        if (isPassword) {
            iconContainer.innerHTML = `
                <svg class="h-4 w-4 text-black" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                </svg>
            `;
        } else {
            iconContainer.innerHTML = `
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            `;
        }
    }

    function previewStudentPhoto(input) {
        const preview = document.getElementById('studentPhotoPreview');
        const placeholder = document.getElementById('studentPhotoPlaceholder');
        const filenameEl = document.getElementById('studentPhotoFilename');
        const removeCheckbox = document.getElementById('remove_photo');

        if (!input.files || !input.files[0]) return;

        const file = input.files[0];
        if (removeCheckbox) {
            removeCheckbox.checked = false;
        }
        if (filenameEl) {
            filenameEl.textContent = file.name;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            if (preview && placeholder) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                placeholder.classList.add('hidden');
            }
        };
        reader.readAsDataURL(file);
    }

    function toggleRemoveStudentPhoto(checkbox) {
        const preview = document.getElementById('studentPhotoPreview');
        const placeholder = document.getElementById('studentPhotoPlaceholder');
        const fileInput = document.getElementById('student_photo');
        const filenameEl = document.getElementById('studentPhotoFilename');

        if (checkbox.checked) {
            if (fileInput) fileInput.value = '';
            if (filenameEl) filenameEl.textContent = 'Foto akan dihapus saat perubahan disimpan.';
            if (preview) preview.classList.add('hidden');
            if (placeholder) placeholder.classList.remove('hidden');
        } else {
            if (filenameEl) filenameEl.textContent = 'Format JPG, PNG, atau WEBP (maks. 2MB)';
            if (preview && preview.getAttribute('src')) {
                preview.classList.remove('hidden');
                if (placeholder) placeholder.classList.add('hidden');
            }
        }
    }
</script>
@endpush
