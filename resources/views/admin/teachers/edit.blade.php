@extends('layouts.admin')

@section('title', 'Edit Data Guru')

@php
    $teacherName = $teacher->user?->name ?? 'Guru';
    $initials = collect(explode(' ', trim($teacherName)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
    if ($initials === '') {
        $initials = 'GR';
    }

    $hasStoredPhoto = ! empty($teacher->photo) && \Illuminate\Support\Facades\Storage::disk('public')->exists($teacher->photo);
    $currentEducation = old('last_education', $teacher->last_education);
    $educationOptions = [
        'D3' => 'D3',
        'D4/S1' => 'D4 / S1',
        'S2' => 'S2',
        'S3' => 'S3',
        'SMA/SMK' => 'SMA / SMK / Sederajat',
    ];
    $isActive = (string) old('is_active', $teacher->user?->is_active ?? true ? '1' : '0');
    $isPetugasMbg = (bool) old('is_petugas_mbg', $teacher->is_petugas_mbg);
    $isSavingsOfficer = (string) old('is_savings_officer', $teacher->is_savings_officer ? '1' : '0');
    $savingsScope = (string) old('savings_scope', $teacher->savings_scope ?: 'all');
    $selectedSavingsClassIds = collect(old('savings_class_ids', $teacher->managedSavingsClasses->pluck('id')->all()))
        ->map(fn ($id) => (int) $id)
        ->all();
    $assignedSubjects = $teacher->assignments->pluck('subject.name')->filter()->unique()->values();
@endphp

@section('content')
<div class="mx-auto max-w-6xl space-y-6 pb-10">
    <!-- Header Halaman -->
    <div class="rounded-xl border-2 border-black bg-[#A5D8FF] p-5 shadow-[5px_5px_0px_0px_#000]">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center rounded-md border-2 border-black bg-white px-2.5 py-0.5 text-[11px] font-black uppercase tracking-wide text-black shadow-[2px_2px_0px_0px_#000]">
                        Edit Data Guru
                    </span>
                    <span class="inline-flex items-center rounded-md border-2 border-black px-2.5 py-0.5 text-[11px] font-black uppercase tracking-wide text-black shadow-[2px_2px_0px_0px_#000] {{ ($teacher->user?->is_active ?? true) ? 'bg-[#D3F9D8]' : 'bg-[#FFE3E3]' }}">
                        {{ ($teacher->user?->is_active ?? true) ? 'Akun Aktif' : 'Akun Nonaktif' }}
                    </span>
                </div>
                <h1 class="text-xl font-black tracking-tight text-black sm:text-2xl">
                    {{ $teacherName }}
                </h1>
                <div class="flex flex-wrap items-center gap-2 font-mono text-xs font-bold text-black/80">
                    <span class="rounded border border-black bg-white px-2 py-0.5">NUPTK: {{ $teacher->nuptk ?: '—' }}</span>
                    <span class="rounded border border-black bg-white px-2 py-0.5">NIP: {{ $teacher->nip ?: '—' }}</span>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('admin.teachers.index') }}"
                   class="inline-flex min-h-10 items-center gap-2 rounded-lg border-2 border-black bg-white px-4 py-2 text-xs font-extrabold uppercase tracking-wide text-black shadow-[3px_3px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:bg-slate-100 hover:shadow-[4px_4px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Kembali ke Daftar Guru</span>
                </a>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.teachers.update', $teacher) }}"
          method="POST"
          enctype="multipart/form-data"
          class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:items-start">
            <!-- Kolom Kiri: Foto Profil, Akun Login & Ringkasan Penugasan -->
            <div class="space-y-6 lg:col-span-4">
                <!-- Kartu Foto Profil -->
                <div class="rounded-xl border-2 border-black bg-white p-5 shadow-[5px_5px_0px_0px_#000]">
                    <h2 class="border-b-2 border-black pb-2.5 text-sm font-black uppercase tracking-wide text-black">
                        Foto Profil
                    </h2>

                    <div class="mt-4 flex flex-col items-center gap-4">
                        <div class="relative h-32 w-32 overflow-hidden rounded-xl border-2 border-black bg-[#FFF9DB] shadow-[4px_4px_0px_0px_#000]">
                            <img id="teacherPhotoPreview"
                                 src="{{ $hasStoredPhoto ? asset('storage/' . $teacher->photo) : '' }}"
                                 alt="{{ $teacherName }}"
                                 class="h-full w-full object-cover {{ $hasStoredPhoto ? '' : 'hidden' }}">
                            <div id="teacherPhotoPlaceholder"
                                 class="flex h-full w-full items-center justify-center bg-[#A5D8FF] text-3xl font-black text-black {{ $hasStoredPhoto ? 'hidden' : '' }}">
                                {{ $initials }}
                            </div>
                        </div>

                        <div class="w-full space-y-2.5">
                            <label for="teacher_photo"
                                   class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-lg border-2 border-black bg-[#FFF3BF] px-3.5 py-2 text-xs font-extrabold uppercase text-black shadow-[2px_2px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:bg-[#FFD43B] hover:shadow-[3px_3px_0px_0px_#000]">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>Pilih / Ganti Foto</span>
                            </label>
                            <input type="file"
                                   id="teacher_photo"
                                   name="photo"
                                   accept="image/jpeg,image/png,image/jpg,image/webp"
                                   onchange="previewTeacherPhoto(this)"
                                   class="hidden">

                            <p id="teacherPhotoFilename" class="truncate text-center text-[11px] font-semibold text-slate-600">
                                Format JPG, PNG, atau WEBP (maks. 5MB)
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
                                           onchange="toggleRemoveTeacherPhoto(this)"
                                           class="h-4 w-4 rounded border-2 border-black text-black focus:ring-0">
                                    <span>Hapus Foto Saat Ini</span>
                                </label>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Kartu Akun & Status Login -->
                <div class="rounded-xl border-2 border-black bg-white p-5 shadow-[5px_5px_0px_0px_#000]">
                    <h2 class="border-b-2 border-black pb-2.5 text-sm font-black uppercase tracking-wide text-black">
                        Akun Login & Status
                    </h2>

                    <div class="mt-4 space-y-4">
                        <div>
                            <label for="email" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">
                                Email (Opsional)
                            </label>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   value="{{ old('email', $teacher->user?->email) }}"
                                   placeholder="email@domain.com (kosongkan untuk hapus)"
                                   class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 text-sm font-semibold text-black placeholder:font-normal placeholder:text-slate-400 shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none">
                            @error('email')
                                <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">
                                Reset Kata Sandi (Opsional)
                            </label>
                            <div class="relative">
                                <input type="password"
                                       id="password"
                                       name="password"
                                       placeholder="Biarkan kosong jika tidak diubah"
                                       class="w-full rounded-lg border-2 border-black bg-white py-2 pl-3.5 pr-10 font-mono text-sm font-semibold text-black placeholder:font-sans placeholder:font-normal placeholder:text-slate-400 shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none">
                                <button type="button"
                                        onclick="toggleEditPasswordVisibility()"
                                        class="absolute inset-y-0 right-0 flex cursor-pointer items-center pr-3 text-slate-600 transition hover:text-black focus:outline-none"
                                        title="Tampilkan / Sembunyikan Password">
                                    <span id="edit_page_eye_icon">
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

                <!-- Ringkasan Wali Kelas & KBM (Konteks) -->
                <div class="rounded-xl border-2 border-black bg-[#FFFDF5] p-5 shadow-[5px_5px_0px_0px_#000]">
                    <h2 class="border-b-2 border-black pb-2.5 text-sm font-black uppercase tracking-wide text-black">
                        Ringkasan Penugasan KBM
                    </h2>
                    <div class="mt-3.5 space-y-3 text-xs">
                        <div>
                            <div class="mb-1 text-[10px] font-black uppercase tracking-wider text-slate-500">Wali Kelas</div>
                            @if($teacher->homeroomClasses->isNotEmpty())
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($teacher->homeroomClasses as $cls)
                                        <span class="inline-flex items-center rounded border border-black bg-[#D3F9D8] px-2 py-0.5 font-extrabold text-black">
                                            {{ $cls->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="font-medium italic text-slate-500">Bukan wali kelas</span>
                            @endif
                        </div>

                        <div>
                            <div class="mb-1 text-[10px] font-black uppercase tracking-wider text-slate-500">Penugasan Mengajar</div>
                            @if($assignedSubjects->isNotEmpty())
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($assignedSubjects as $subjectName)
                                        <span class="inline-flex items-center rounded border border-black bg-[#E5DBFF] px-2 py-0.5 font-bold text-black">
                                            {{ $subjectName }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="inline-flex items-center rounded border border-black bg-[#FFF9DB] px-2 py-0.5 font-bold text-black">
                                    Semua Mata Pelajaran (Bebas)
                                </span>
                            @endif
                        </div>

                        <p class="pt-1 text-[11px] font-medium text-slate-600">
                            Pengaturan wali kelas dan jadwal mapel dapat diubah melalui tombol <strong>Mengajar</strong> pada daftar guru.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Biodata Lengkap & Peran Tambahan -->
            <div class="space-y-6 lg:col-span-8">
                <!-- Kartu Biodata & Identitas -->
                <div class="rounded-xl border-2 border-black bg-white p-6 shadow-[5px_5px_0px_0px_#000]">
                    <div class="border-b-2 border-black pb-3">
                        <h2 class="text-base font-black uppercase tracking-wide text-black">
                            Biodata & Identitas Guru
                        </h2>
                        <p class="mt-0.5 text-xs font-semibold text-slate-600">
                            Data identitas resmi guru sesuai database sistem.
                        </p>
                    </div>

                    <div class="mt-5 space-y-4">
                        <div>
                            <label for="name" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">
                                Nama Lengkap & Gelar <span class="text-rose-600">*</span>
                            </label>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   value="{{ old('name', $teacher->user?->name) }}"
                                   required
                                   class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 text-sm font-semibold text-black shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none">
                            @error('name')
                                <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="nuptk" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">NUPTK</label>
                                <input type="text"
                                       id="nuptk"
                                       name="nuptk"
                                       value="{{ old('nuptk', $teacher->nuptk) }}"
                                       placeholder="16 digit NUPTK"
                                       class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 font-mono text-sm font-semibold text-black placeholder:font-sans placeholder:font-normal placeholder:text-slate-400 shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none">
                                @error('nuptk')
                                    <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="nip" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">NIP</label>
                                <input type="text"
                                       id="nip"
                                       name="nip"
                                       value="{{ old('nip', $teacher->nip) }}"
                                       placeholder="18 digit NIP"
                                       class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 font-mono text-sm font-semibold text-black placeholder:font-sans placeholder:font-normal placeholder:text-slate-400 shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none">
                                @error('nip')
                                    <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="gender" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">
                                    Jenis Kelamin <span class="text-rose-600">*</span>
                                </label>
                                <select id="gender"
                                        name="gender"
                                        required
                                        class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 text-sm font-semibold text-black shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:outline-none">
                                    <option value="L" {{ old('gender', $teacher->gender ?: 'L') === 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                                    <option value="P" {{ old('gender', $teacher->gender ?: 'L') === 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                                </select>
                                @error('gender')
                                    <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="last_education" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">Pendidikan Terakhir</label>
                                <select id="last_education"
                                        name="last_education"
                                        class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 text-sm font-semibold text-black shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:outline-none">
                                    <option value="">-- Pilih Jenjang --</option>
                                    @if(! empty($currentEducation) && ! array_key_exists($currentEducation, $educationOptions))
                                        <option value="{{ $currentEducation }}" selected>{{ $currentEducation }}</option>
                                    @endif
                                    @foreach($educationOptions as $val => $label)
                                        <option value="{{ $val }}" {{ $currentEducation === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('last_education')
                                    <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="birth_place" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">Tempat Lahir</label>
                                <input type="text"
                                       id="birth_place"
                                       name="birth_place"
                                       value="{{ old('birth_place', $teacher->birth_place) }}"
                                       class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 text-sm font-semibold text-black shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none">
                                @error('birth_place')
                                    <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="birth_date" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">Tanggal Lahir</label>
                                <input type="date"
                                       id="birth_date"
                                       name="birth_date"
                                       value="{{ old('birth_date', $teacher->birth_date ? $teacher->birth_date->format('Y-m-d') : '') }}"
                                       class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 font-mono text-sm font-semibold text-black shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:outline-none">
                                @error('birth_date')
                                    <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="phone" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">No. WhatsApp / HP</label>
                            <input type="text"
                                   id="phone"
                                   name="phone"
                                   value="{{ old('phone', $teacher->phone) }}"
                                   placeholder="08xxxxxxxxxx"
                                   class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 font-mono text-sm font-semibold text-black placeholder:font-sans placeholder:font-normal placeholder:text-slate-400 shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none">
                            @error('phone')
                                <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Kartu Peran Tambahan: Petugas MBG & Pengelola Tabungan -->
                <div class="rounded-xl border-2 border-black bg-white p-6 shadow-[5px_5px_0px_0px_#000]">
                    <div class="border-b-2 border-black pb-3">
                        <h2 class="text-base font-black uppercase tracking-wide text-black">
                            Peran & Wewenang Tambahan
                        </h2>
                        <p class="mt-0.5 text-xs font-semibold text-slate-600">
                            Atur status Petugas MBG dan Pengelola Tabungan Siswa untuk guru ini.
                        </p>
                    </div>

                    <div class="mt-5 space-y-5">
                        <!-- Petugas MBG -->
                        <div class="rounded-xl border-2 border-black bg-[#FFF9DB] p-4 shadow-[3px_3px_0px_0px_#000]">
                            <label class="flex cursor-pointer items-start gap-3">
                                <input type="hidden" name="is_petugas_mbg" value="0">
                                <input type="checkbox"
                                       id="is_petugas_mbg"
                                       name="is_petugas_mbg"
                                       value="1"
                                       {{ $isPetugasMbg ? 'checked' : '' }}
                                       class="mt-0.5 h-5 w-5 rounded border-2 border-black text-black focus:ring-0">
                                <div class="space-y-0.5">
                                    <span class="block text-sm font-black text-black">Petugas MBG (Makan Bergizi Gratis)</span>
                                    <span class="block text-xs font-medium text-slate-700">
                                        Memberikan akses kepada guru ini untuk mencatat dan mengelola distribusi MBG.
                                    </span>
                                </div>
                            </label>
                        </div>

                        <!-- Pengelola Tabungan Siswa -->
                        <div class="space-y-4 rounded-xl border-2 border-black bg-[#F8FAFC] p-4 shadow-[3px_3px_0px_0px_#000]">
                            <div class="space-y-2.5">
                                <label class="block text-xs font-extrabold uppercase tracking-wide text-black">
                                    Status Pengelola Tabungan Siswa
                                </label>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border-2 border-black bg-white p-3.5 transition-all hover:bg-[#D3F9D8]/50 has-[:checked]:bg-[#D3F9D8] has-[:checked]:shadow-[3px_3px_0px_0px_#000]">
                                        <input type="radio"
                                               name="is_savings_officer"
                                               value="1"
                                               id="edit_savings_officer_active"
                                               onchange="toggleEditSavingsSection()"
                                               {{ $isSavingsOfficer === '1' ? 'checked' : '' }}
                                               class="mt-0.5 h-4 w-4 border-2 border-black text-black focus:ring-0">
                                        <div class="space-y-0.5">
                                            <span class="block text-sm font-black text-black">Aktif (Ditugaskan)</span>
                                            <span class="block text-xs font-medium text-slate-700">Berwenang melayani transaksi tabungan siswa.</span>
                                        </div>
                                    </label>

                                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border-2 border-black bg-white p-3.5 transition-all hover:bg-[#FFE3E3]/60 has-[:checked]:bg-[#FFE3E3] has-[:checked]:shadow-[3px_3px_0px_0px_#000]">
                                        <input type="radio"
                                               name="is_savings_officer"
                                               value="0"
                                               id="edit_savings_officer_inactive"
                                               onchange="toggleEditSavingsSection()"
                                               {{ $isSavingsOfficer === '0' ? 'checked' : '' }}
                                               class="mt-0.5 h-4 w-4 border-2 border-black text-black focus:ring-0">
                                        <div class="space-y-0.5">
                                            <span class="block text-sm font-black text-black">Nonaktif</span>
                                            <span class="block text-xs font-medium text-slate-700">Bukan pengelola tabungan siswa.</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Cakupan Wewenang Kelas Tabungan -->
                            <div id="edit_savings_scope_section" class="space-y-4 border-t-2 border-black/15 pt-4 {{ $isSavingsOfficer === '1' ? '' : 'hidden' }}">
                                <div class="space-y-2.5">
                                    <label class="block text-xs font-extrabold uppercase tracking-wide text-black">
                                        Cakupan Rombel / Kelas Tabungan
                                    </label>
                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border-2 border-black bg-white p-3 transition-all hover:bg-[#D0EBFF]/50 has-[:checked]:bg-[#D0EBFF] has-[:checked]:shadow-[2px_2px_0px_0px_#000]">
                                            <input type="radio"
                                                   name="savings_scope"
                                                   value="all"
                                                   id="edit_savings_scope_all"
                                                   onchange="toggleEditSavingsScope()"
                                                   {{ $savingsScope === 'all' ? 'checked' : '' }}
                                                   class="mt-0.5 h-4 w-4 border-2 border-black text-black focus:ring-0">
                                            <div class="space-y-0.5">
                                                <span class="block text-xs font-black text-black">Semua Kelas</span>
                                                <span class="block text-[11px] font-medium text-slate-700">Dapat melayani seluruh rombel tanpa batasan.</span>
                                            </div>
                                        </label>

                                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border-2 border-black bg-white p-3 transition-all hover:bg-[#FFF3BF]/60 has-[:checked]:bg-[#FFF3BF] has-[:checked]:shadow-[2px_2px_0px_0px_#000]">
                                            <input type="radio"
                                                   name="savings_scope"
                                                   value="restricted"
                                                   id="edit_savings_scope_restricted"
                                                   onchange="toggleEditSavingsScope()"
                                                   {{ $savingsScope === 'restricted' ? 'checked' : '' }}
                                                   class="mt-0.5 h-4 w-4 border-2 border-black text-black focus:ring-0">
                                            <div class="space-y-0.5">
                                                <span class="block text-xs font-black text-black">Kelas Tertentu</span>
                                                <span class="block text-[11px] font-medium text-slate-700">Hanya mengelola rombel yang dipilih di bawah.</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div id="edit_savings_classes_container" class="space-y-2.5 {{ $savingsScope === 'restricted' ? '' : 'hidden' }}">
                                    <div class="flex items-center justify-between">
                                        <label class="text-xs font-extrabold text-black">Pilih Kelas yang Dikelola</label>
                                        <div class="flex items-center gap-2 text-xs">
                                            <button type="button" onclick="toggleAllEditSavingsClasses(true)" class="cursor-pointer font-extrabold text-black underline decoration-2 hover:text-blue-700">
                                                Pilih Semua
                                            </button>
                                            <span class="font-black text-black/40">·</span>
                                            <button type="button" onclick="toggleAllEditSavingsClasses(false)" class="cursor-pointer font-bold text-slate-600 hover:text-black hover:underline">
                                                Batalkan
                                            </button>
                                        </div>
                                    </div>

                                    <div class="grid max-h-52 grid-cols-2 gap-2 overflow-y-auto rounded-lg border-2 border-black bg-white p-2.5 sm:grid-cols-3">
                                        @forelse($classes as $c)
                                            <label class="flex cursor-pointer items-center gap-2 rounded-lg border-2 border-black/20 bg-slate-50 px-2.5 py-1.5 text-xs transition hover:border-black hover:bg-[#FFF9DB] has-[:checked]:border-black has-[:checked]:bg-[#FFD43B] has-[:checked]:shadow-[2px_2px_0px_0px_#000]">
                                                <input type="checkbox"
                                                       name="savings_class_ids[]"
                                                       value="{{ $c->id }}"
                                                       {{ in_array((int) $c->id, $selectedSavingsClassIds, true) ? 'checked' : '' }}
                                                       class="edit-savings-class-checkbox h-3.5 w-3.5 rounded border-2 border-black text-black focus:ring-0">
                                                <span class="truncate font-extrabold text-black">{{ $c->name }}</span>
                                                <span class="ml-auto font-mono text-[10px] font-bold text-black/70 tabular-nums">({{ $c->students_count }})</span>
                                            </label>
                                        @empty
                                            <p class="col-span-full py-2 text-center text-xs font-medium text-slate-500">
                                                Belum ada data kelas.
                                            </p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Tombol Simpan -->
                <div class="flex flex-wrap items-center justify-end gap-3 rounded-xl border-2 border-black bg-white p-5 shadow-[5px_5px_0px_0px_#000]">
                    <a href="{{ route('admin.teachers.index') }}"
                       class="inline-flex min-h-11 items-center justify-center rounded-lg border-2 border-black bg-white px-5 py-2.5 text-xs font-extrabold uppercase tracking-wide text-black shadow-[3px_3px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:bg-slate-100 hover:shadow-[4px_4px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none">
                        Batal
                    </a>
                    <button type="submit"
                            class="inline-flex min-h-11 cursor-pointer items-center justify-center gap-2 rounded-lg border-2 border-black bg-[#74C0FC] px-6 py-2.5 text-xs font-black uppercase tracking-wide text-black shadow-[3px_3px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:bg-[#4DABF7] hover:shadow-[4px_4px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function toggleEditPasswordVisibility() {
        const input = document.getElementById('password');
        const iconContainer = document.getElementById('edit_page_eye_icon');
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

    function previewTeacherPhoto(input) {
        const preview = document.getElementById('teacherPhotoPreview');
        const placeholder = document.getElementById('teacherPhotoPlaceholder');
        const filenameEl = document.getElementById('teacherPhotoFilename');
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

    function toggleRemoveTeacherPhoto(checkbox) {
        const preview = document.getElementById('teacherPhotoPreview');
        const placeholder = document.getElementById('teacherPhotoPlaceholder');
        const fileInput = document.getElementById('teacher_photo');
        const filenameEl = document.getElementById('teacherPhotoFilename');

        if (checkbox.checked) {
            if (fileInput) fileInput.value = '';
            if (filenameEl) filenameEl.textContent = 'Foto akan dihapus saat perubahan disimpan.';
            if (preview) preview.classList.add('hidden');
            if (placeholder) placeholder.classList.remove('hidden');
        } else {
            if (filenameEl) filenameEl.textContent = 'Format JPG, PNG, atau WEBP (maks. 5MB)';
            if (preview && preview.getAttribute('src')) {
                preview.classList.remove('hidden');
                if (placeholder) placeholder.classList.add('hidden');
            }
        }
    }

    function toggleEditSavingsSection() {
        const isActive = document.getElementById('edit_savings_officer_active')?.checked;
        const scopeSection = document.getElementById('edit_savings_scope_section');
        if (!scopeSection) return;

        if (isActive) {
            scopeSection.classList.remove('hidden');
            toggleEditSavingsScope();
        } else {
            scopeSection.classList.add('hidden');
        }
    }

    function toggleEditSavingsScope() {
        const isRestricted = document.getElementById('edit_savings_scope_restricted')?.checked;
        const classesContainer = document.getElementById('edit_savings_classes_container');
        if (!classesContainer) return;

        if (isRestricted) {
            classesContainer.classList.remove('hidden');
        } else {
            classesContainer.classList.add('hidden');
        }
    }

    function toggleAllEditSavingsClasses(checked) {
        document.querySelectorAll('.edit-savings-class-checkbox').forEach(cb => {
            cb.checked = checked;
        });
    }
</script>
@endpush
