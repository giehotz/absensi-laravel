@extends('layouts.guru')

@section('title', 'Buat Paket Penilaian Sumatif')
@section('page-title', 'Buat Paket Penilaian')

@section('content')
<div class="max-w-2xl mx-auto space-y-6 pb-12">
    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between">
        <a href="{{ route('guru.penilaian.index') }}" class="text-xs font-bold text-slate-600 hover:text-black flex items-center gap-1.5 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali ke Daftar Paket</span>
        </a>
    </div>

    <!-- Main Card -->
    <div class="bg-white border-2 border-black rounded-lg shadow-[5px_5px_0px_0px_#000] overflow-hidden">
        <div class="bg-[#FFD43B] border-b-2 border-black p-5">
            <h2 class="font-heading font-black text-xl text-black">Form Pembuatan Paket Penilaian</h2>
            <p class="text-xs text-black/80 font-medium mt-0.5">
                Pilih kelas rombel dan mata pelajaran yang Anda ampu untuk menginisialisasi 15 lembar sumatif (SUM 1 s.d. 15).
            </p>
        </div>

        <form action="{{ route('guru.penilaian.store') }}" method="POST" class="p-6 space-y-5">
            @csrf

            <!-- Info Tahun Ajaran Aktif -->
            <div class="p-3.5 bg-slate-50 border-2 border-slate-200 rounded-lg flex items-center justify-between">
                <div>
                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Tahun Ajaran Aktif</div>
                    <div class="text-xs font-black text-black mt-0.5">
                        {{ $activeYear->name ?? 'Belum ditentukan' }} (Semester {{ ucfirst($activeYear->semester ?? '-') }})
                    </div>
                </div>
                <span class="text-[10px] font-black bg-emerald-100 text-emerald-950 border border-emerald-400 px-2 py-0.5 rounded">
                    Aktif
                </span>
            </div>

            <!-- Pilih Kelas -->
            <div>
                <label for="school_class_id" class="block text-xs font-black text-black uppercase tracking-wider mb-1.5">
                    Kelas / Rombongan Belajar <span class="text-red-500">*</span>
                </label>
                <select name="school_class_id" id="school_class_id" required
                        class="w-full text-xs font-bold border-2 border-black p-2.5 rounded bg-white focus:outline-none focus:ring-2 focus:ring-[#FFD43B]">
                    <option value="">-- Pilih Kelas Rombel --</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ old('school_class_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }} (Tingkat {{ $c->level }})
                        </option>
                    @endforeach
                </select>
                @error('school_class_id')
                    <p class="text-red-600 text-xs font-bold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Pilih Mata Pelajaran -->
            <div>
                <label for="subject_id" class="block text-xs font-black text-black uppercase tracking-wider mb-1.5">
                    Mata Pelajaran <span class="text-red-500">*</span>
                </label>
                <select name="subject_id" id="subject_id" required
                        class="w-full text-xs font-bold border-2 border-black p-2.5 rounded bg-white focus:outline-none focus:ring-2 focus:ring-[#FFD43B]">
                    <option value="">-- Pilih Mata Pelajaran --</option>
                    @foreach($subjects as $s)
                        <option value="{{ $s->id }}" {{ old('subject_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->name }} ({{ $s->code ?? 'MAPEL' }})
                        </option>
                    @endforeach
                </select>
                @error('subject_id')
                    <p class="text-red-600 text-xs font-bold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Judul Paket Penilaian -->
            <div>
                <label for="title" class="block text-xs font-black text-black uppercase tracking-wider mb-1.5">
                    Judul Paket Penilaian <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" id="title" required
                       value="{{ old('title', 'Penilaian Sumatif Semester ' . ucfirst($activeYear->semester ?? 'Ganjil') . ' ' . ($activeYear->name ?? '')) }}"
                       placeholder="Contoh: Penilaian Sumatif Semester Ganjil 2026/2027"
                       class="w-full text-xs font-bold border-2 border-black p-2.5 rounded bg-white focus:outline-none focus:ring-2 focus:ring-[#FFD43B]">
                @error('title')
                    <p class="text-red-600 text-xs font-bold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Standar KKTP Default -->
            <div>
                <label for="kktp_default" class="block text-xs font-black text-black uppercase tracking-wider mb-1.5">
                    Standar KKTP Default (Kriteria Ketercapaian Tujuan Pembelajaran) <span class="text-red-500">*</span>
                </label>
                <div class="flex items-center gap-3">
                    <input type="number" name="kktp_default" id="kktp_default" required min="0" max="100"
                           value="{{ old('kktp_default', 75) }}"
                           class="w-32 text-xs font-black border-2 border-black p-2.5 rounded bg-white text-center focus:outline-none focus:ring-2 focus:ring-[#FFD43B]">
                    <span class="text-xs text-slate-500">Nilai minimum tuntas (0 - 100). Default: 75.</span>
                </div>
                @error('kktp_default')
                    <p class="text-red-600 text-xs font-bold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t-2 border-slate-200 flex items-center justify-end gap-3">
                <a href="{{ route('guru.penilaian.index') }}" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-black">
                    Batal
                </a>
                <button type="submit" class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-5 py-2.5 text-xs font-black rounded border-2 border-black shadow-[3px_3px_0px_0px_#000] cursor-pointer">
                    Simpan & Inisialisasi Paket
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
