@extends('layouts.admin')

@section('title', 'Manajemen Data Guru')
@section('page-title', 'Manajemen Data Guru')

@section('content')
<div class="space-y-6">
    <!-- Page Header & Primary Actions -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="space-y-1">
            <div class="flex items-center gap-2.5">
                <h1 class="text-xl font-semibold tracking-tight text-slate-900">
                    Daftar Guru & Tenaga Pendidik
                </h1>
                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700 tabular-nums">
                    {{ $teachers->total() }} Guru
                </span>
            </div>
            <p class="text-sm text-slate-500">
                Kelola profil guru, akun login (NUPTK / NIP), penugasan mengajar, dan wewenang tabungan siswa.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.teachers.template') }}"
               class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
               title="Unduh Format Template Excel 9 Kolom">
                <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span>Template Excel</span>
            </a>

            <button type="button"
                    onclick="openModal('importTeacherModal')"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 cursor-pointer">
                <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <span>Import Excel</span>
            </button>

            <button type="button"
                    onclick="openModal('createTeacherModal')"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 cursor-pointer">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah Guru</span>
            </button>
        </div>
    </div>

    <!-- Main Data Card (Integrated Toolbar + Table + Pagination) -->
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <!-- Search & Sort Toolbar -->
        <div class="border-b border-slate-200/80 bg-slate-50/50 p-4">
            <form action="{{ route('admin.teachers.index') }}" method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <!-- Search Input -->
                <div class="relative flex-1 max-w-md">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text"
                           name="search"
                           value="{{ $search ?? '' }}"
                           placeholder="Cari nama guru, NUPTK, NIP, atau email..."
                           class="w-full rounded-lg border border-slate-300 bg-white py-2 pl-9 pr-9 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    @if(!empty($search))
                        <a href="{{ route('admin.teachers.index', request()->except(['search', 'page'])) }}"
                           class="absolute right-2.5 top-1/2 -translate-y-1/2 rounded p-0.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                           title="Hapus kata kunci">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    @endif
                </div>

                <!-- Sort & Filter Controls -->
                <div class="flex flex-wrap items-center gap-2">
                    <div class="flex items-center gap-2">
                        <label for="sort_select" class="text-xs font-medium text-slate-500 whitespace-nowrap">Urutkan</label>
                        <select id="sort_select"
                                name="sort"
                                onchange="this.form.submit()"
                                class="rounded-lg border border-slate-300 bg-white py-2 pl-3 pr-8 text-sm font-medium text-slate-700 transition hover:border-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 cursor-pointer">
                            <option value="name_asc" {{ ($sort ?? 'name_asc') === 'name_asc' ? 'selected' : '' }}>Nama: A - Z</option>
                            <option value="name_desc" {{ ($sort ?? '') === 'name_desc' ? 'selected' : '' }}>Nama: Z - A</option>
                            <option value="latest" {{ ($sort ?? '') === 'latest' ? 'selected' : '' }}>Terbaru Dibuat</option>
                            <option value="oldest" {{ ($sort ?? '') === 'oldest' ? 'selected' : '' }}>Paling Awal Dibuat</option>
                        </select>
                    </div>

                    <button type="submit"
                            class="inline-flex items-center rounded-lg bg-slate-900 px-3.5 py-2 text-sm font-medium text-white transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900/20 cursor-pointer">
                        Cari
                    </button>

                    @if(!empty($search) || ($sort ?? 'name_asc') !== 'name_asc')
                        <a href="{{ route('admin.teachers.index') }}"
                           class="inline-flex items-center gap-1 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-200/60 hover:text-slate-900"
                           title="Reset Semua Filter">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span>Reset</span>
                        </a>
                    @endif
                </div>
            </form>

            @if(!empty($search))
                <div class="mt-3 flex items-center justify-between rounded-lg border border-indigo-100 bg-indigo-50/70 px-3.5 py-2 text-xs text-indigo-900">
                    <span>
                        Menampilkan <strong class="font-semibold tabular-nums">{{ $teachers->total() }}</strong> hasil untuk
                        <strong class="font-semibold">"{{ $search }}"</strong>
                    </span>
                    <a href="{{ route('admin.teachers.index') }}" class="font-medium text-indigo-700 hover:text-indigo-900 hover:underline">
                        Hapus Pencarian
                    </a>
                </div>
            @endif
        </div>

        <!-- Data Table -->
        <div class="overflow-x-auto">
            <table class="w-full min-w-[960px] text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50/80 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="w-12 py-3.5 pl-5 pr-3 text-right">No</th>
                        <th class="px-4 py-3.5">
                            <a href="{{ route('admin.teachers.index', array_merge(request()->except(['sort', 'page']), ['sort' => ($sort ?? 'name_asc') === 'name_asc' ? 'name_desc' : 'name_asc'])) }}"
                               class="group inline-flex items-center gap-1.5 text-slate-600 transition hover:text-slate-900"
                               title="Klik untuk membalikkan urutan A-Z / Z-A">
                                <span>Profil Guru</span>
                                @if(($sort ?? 'name_asc') === 'name_asc')
                                    <span class="inline-flex items-center rounded bg-indigo-100 px-1.5 py-0.5 text-[10px] font-semibold text-indigo-700">A-Z ↑</span>
                                @elseif(($sort ?? '') === 'name_desc')
                                    <span class="inline-flex items-center rounded bg-indigo-100 px-1.5 py-0.5 text-[10px] font-semibold text-indigo-700">Z-A ↓</span>
                                @else
                                    <span class="text-slate-400 transition group-hover:text-slate-700">↕</span>
                                @endif
                            </a>
                        </th>
                        <th class="px-4 py-3.5">NUPTK / NIP</th>
                        <th class="px-4 py-3.5">TTL & Pendidikan</th>
                        <th class="px-4 py-3.5">Peran & Penugasan</th>
                        <th class="py-3.5 pl-4 pr-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($teachers as $index => $teacher)
                        @php
                            $teacherName = $teacher->user->name ?? '-';
                            $initials = collect(explode(' ', trim($teacherName)))
                                ->filter()
                                ->take(2)
                                ->map(fn($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                                ->implode('');
                            $avatarTone = match($teacher->gender) {
                                'L' => 'bg-sky-50 text-sky-700 ring-sky-200/80',
                                'P' => 'bg-rose-50 text-rose-700 ring-rose-200/80',
                                default => 'bg-slate-100 text-slate-600 ring-slate-200/80',
                            };
                        @endphp
                        <tr class="transition-colors hover:bg-slate-50/80">
                            <!-- No -->
                            <td class="py-4 pl-5 pr-3 text-right align-top text-xs font-medium text-slate-400 tabular-nums">
                                {{ $teachers->firstItem() + $index }}
                            </td>

                            <!-- Profil Guru (Avatar, Name, Gender, Phone, Email) -->
                            <td class="px-4 py-4 align-top">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-semibold ring-1 {{ $avatarTone }}">
                                        {{ $initials ?: '?' }}
                                    </div>
                                    <div class="min-w-0 space-y-1">
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <span class="font-semibold text-slate-900 leading-snug">
                                                {{ $teacherName }}
                                            </span>
                                            @if($teacher->gender === 'L')
                                                <span class="inline-flex items-center rounded bg-sky-50 px-1.5 py-0.5 text-[10px] font-semibold text-sky-700 ring-1 ring-inset ring-sky-600/15" title="Laki-laki">L</span>
                                            @elseif($teacher->gender === 'P')
                                                <span class="inline-flex items-center rounded bg-rose-50 px-1.5 py-0.5 text-[10px] font-semibold text-rose-700 ring-1 ring-inset ring-rose-600/15" title="Perempuan">P</span>
                                            @endif
                                        </div>

                                        <!-- Email & Verification Status -->
                                        <div class="flex flex-wrap items-center gap-1.5 text-xs">
                                            @if($teacher->user?->email)
                                                <span class="max-w-[200px] truncate text-slate-600" title="{{ $teacher->user->email }}">
                                                    {{ $teacher->user->email }}
                                                </span>
                                                <span class="inline-flex items-center gap-0.5 rounded-full bg-emerald-50 px-1.5 py-0.5 text-[10px] font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                                    <svg class="h-2.5 w-2.5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Terdaftar
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-medium text-amber-800 ring-1 ring-inset ring-amber-600/20" title="Guru dapat melengkapi email secara mandiri saat login">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                                    Email belum diisi
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Phone -->
                                        @if($teacher->phone)
                                            <div class="flex items-center gap-1 text-xs text-slate-500 tabular-nums">
                                                <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                </svg>
                                                <span>{{ $teacher->phone }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Identitas: NUPTK & NIP -->
                            <td class="px-4 py-4 align-top">
                                <div class="space-y-1.5 font-mono text-xs tabular-nums">
                                    <div class="flex items-center gap-2">
                                        <span class="w-11 shrink-0 font-sans text-[11px] font-medium text-slate-400">NUPTK</span>
                                        <span class="{{ $teacher->nuptk ? 'font-medium text-slate-800' : 'text-slate-400' }}">
                                            {{ $teacher->nuptk ?: '—' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="w-11 shrink-0 font-sans text-[11px] font-medium text-slate-400">NIP</span>
                                        <span class="{{ $teacher->nip ? 'text-slate-600' : 'text-slate-400' }}">
                                            {{ $teacher->nip ?: '—' }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- TTL & Pendidikan -->
                            <td class="px-4 py-4 align-top">
                                <div class="space-y-1.5">
                                    <div class="text-xs">
                                        <div class="font-medium text-slate-800">{{ $teacher->birth_place ?: '—' }}</div>
                                        <div class="text-slate-500 tabular-nums">
                                            {{ $teacher->birth_date ? $teacher->birth_date->format('d-m-Y') : '—' }}
                                        </div>
                                    </div>
                                    @if($teacher->last_education)
                                        <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-700 ring-1 ring-inset ring-slate-200">
                                            {{ $teacher->last_education }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Peran & Penugasan -->
                            <td class="px-4 py-4 align-top">
                                <div class="space-y-2 text-xs">
                                    <!-- Wali Kelas -->
                                    <div class="flex items-start gap-2">
                                        <span class="w-9 shrink-0 pt-0.5 text-[11px] font-medium text-slate-400">Wali</span>
                                        @if($teacher->homeroomClasses->isNotEmpty())
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($teacher->homeroomClasses as $cls)
                                                    <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                                        {{ $cls->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="pt-0.5 text-slate-400">Bukan wali kelas</span>
                                        @endif
                                    </div>

                                    <!-- KBM -->
                                    <div class="flex items-start gap-2">
                                        <span class="w-9 shrink-0 pt-0.5 text-[11px] font-medium text-slate-400">KBM</span>
                                        @if($teacher->assignments->isNotEmpty())
                                            @php
                                                $assignedSubNames = $teacher->assignments->pluck('subject.name')->unique()->filter();
                                                $assignedClassCount = $teacher->assignments->pluck('school_class_id')->unique()->count();
                                            @endphp
                                            <div class="flex flex-wrap items-center gap-1">
                                                @foreach($assignedSubNames as $sName)
                                                    <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-0.5 text-[11px] font-medium text-indigo-700 ring-1 ring-inset ring-indigo-600/15">
                                                        {{ $sName }}
                                                    </span>
                                                @endforeach
                                                <span class="inline-flex items-center rounded-md bg-slate-100 px-1.5 py-0.5 text-[11px] font-medium text-slate-600 tabular-nums">
                                                    {{ $assignedClassCount }} Rombel
                                                </span>
                                            </div>
                                        @else
                                            <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600">
                                                Semua Mapel
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Tambahan: Pengelola Tabungan & Petugas MBG -->
                                    @if($teacher->is_savings_officer || $teacher->is_petugas_mbg)
                                        <div class="flex flex-wrap items-center gap-1.5 pt-0.5">
                                            @if($teacher->is_savings_officer)
                                                @if($teacher->savings_scope === 'all')
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-teal-50 px-2 py-0.5 text-[11px] font-medium text-teal-800 ring-1 ring-inset ring-teal-600/20"
                                                          title="Mengelola tabungan seluruh rombel/kelas">
                                                        <svg class="h-3 w-3 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                        </svg>
                                                        <span>Tabungan: Semua Kelas</span>
                                                    </span>
                                                @else
                                                    @php
                                                        $managedCount = $teacher->managedSavingsClasses->count();
                                                        $managedNames = $teacher->managedSavingsClasses->pluck('name')->implode(', ');
                                                    @endphp
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-medium text-amber-800 ring-1 ring-inset ring-amber-600/20"
                                                          title="Mengelola kelas: {{ $managedNames }}">
                                                        <svg class="h-3 w-3 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                        </svg>
                                                        <span>Tabungan: {{ $managedCount > 0 ? ($managedCount <= 2 ? $managedNames : $managedCount . ' Kelas') : 'Belum Ada Kelas' }}</span>
                                                    </span>
                                                @endif
                                            @endif

                                            @if($teacher->is_petugas_mbg)
                                                <span class="inline-flex items-center gap-1 rounded-full bg-orange-50 px-2 py-0.5 text-[11px] font-medium text-orange-800 ring-1 ring-inset ring-orange-600/20"
                                                      title="Ditugaskan sebagai Petugas MBG (Makan Bergizi Gratis)">
                                                    <svg class="h-3 w-3 text-orange-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                                    </svg>
                                                    <span>Petugas MBG</span>
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <!-- Aksi -->
                            <td class="py-4 pl-4 pr-5 text-right align-top">
                                <div class="inline-flex flex-col items-end gap-1.5">
                                    <!-- Role / Assignment Actions -->
                                    <div class="inline-flex items-center gap-1.5">
                                        <button type="button"
                                                onclick="openAssignmentModal({{ $teacher->id }}, '{{ addslashes($teacher->user->name ?? 'Guru') }}')"
                                                class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-700 shadow-sm transition hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-700 cursor-pointer"
                                                title="Atur Mata Pelajaran dan Rombel yang Diajar">
                                            <svg class="h-3.5 w-3.5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                            </svg>
                                            <span>Mengajar</span>
                                        </button>

                                        <button type="button"
                                                onclick="openSavingsModal({{ json_encode([
                                                    'id' => $teacher->id,
                                                    'name' => $teacher->user->name ?? 'Guru',
                                                    'is_savings_officer' => (bool) $teacher->is_savings_officer,
                                                    'savings_scope' => $teacher->savings_scope ?? 'all',
                                                    'managed_class_ids' => $teacher->managedSavingsClasses->pluck('id')->all(),
                                                    'action_url' => route('admin.teachers.savings-assignment', $teacher),
                                                ]) }})"
                                                class="inline-flex items-center gap-1 rounded-lg border px-2.5 py-1.5 text-xs font-medium shadow-sm transition cursor-pointer {{ $teacher->is_savings_officer ? 'border-teal-200 bg-teal-50 text-teal-800 hover:bg-teal-100' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300 hover:bg-slate-50' }}"
                                                title="{{ $teacher->is_savings_officer ? 'Atur Wewenang Rombel Pengelola Tabungan' : 'Tunjuk Sebagai Pengelola Tabungan Siswa' }}">
                                            <svg class="h-3.5 w-3.5 {{ $teacher->is_savings_officer ? 'text-teal-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                            <span>Tabungan</span>
                                        </button>
                                    </div>

                                    <!-- Edit & Delete Actions -->
                                    <div class="inline-flex items-center gap-1">
                                        <button type="button"
                                                onclick="editTeacher({{ json_encode([
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
                                                ]) }})"
                                                class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 cursor-pointer"
                                                title="Edit Data Guru">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            <span>Edit</span>
                                        </button>

                                        <form action="{{ route('admin.teachers.destroy', $teacher) }}"
                                              method="POST"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus data guru ini?')"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-medium text-rose-600 transition hover:bg-rose-50 hover:text-rose-700 cursor-pointer"
                                                    title="Hapus Guru">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                <span>Hapus</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="mx-auto flex max-w-sm flex-col items-center">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </div>
                                    @if(!empty($search))
                                        <h3 class="mt-3 text-sm font-semibold text-slate-900">
                                            Data guru tidak ditemukan
                                        </h3>
                                        <p class="mt-1 text-xs text-slate-500">
                                            Tidak ada guru yang cocok dengan kata kunci "{{ $search }}".
                                        </p>
                                        <a href="{{ route('admin.teachers.index') }}"
                                           class="mt-4 inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-xs font-medium text-slate-700 shadow-sm transition hover:bg-slate-50">
                                            Tampilkan Semua Guru
                                        </a>
                                    @else
                                        <h3 class="mt-3 text-sm font-semibold text-slate-900">
                                            Belum ada data guru
                                        </h3>
                                        <p class="mt-1 text-xs text-slate-500">
                                            Mulai dengan menambahkan guru secara manual atau mengimpor dari file Excel.
                                        </p>
                                        <div class="mt-4 flex items-center gap-2">
                                            <button type="button"
                                                    onclick="openModal('importTeacherModal')"
                                                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-xs font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 cursor-pointer">
                                                Import Excel
                                            </button>
                                            <button type="button"
                                                    onclick="openModal('createTeacherModal')"
                                                    class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3.5 py-2 text-xs font-medium text-white shadow-sm transition hover:bg-indigo-700 cursor-pointer">
                                                + Tambah Guru
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($teachers->hasPages())
            <div class="border-t border-slate-200/80 bg-slate-50/50 px-5 py-3.5">
                {{ $teachers->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal 1: Import Excel Guru (Format 9 Kolom) -->
<div id="importTeacherModal"
     class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm hidden"
     onclick="if (event.target === this) closeModal('importTeacherModal')">
    <div class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
        <!-- Header -->
        <div class="flex items-start justify-between border-b border-slate-100 px-6 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-slate-900">Import Data Guru dari Excel</h3>
                    <p class="text-xs text-slate-500">Unggah file spreadsheet sesuai format resmi 9 kolom.</p>
                </div>
            </div>
            <button type="button"
                    onclick="closeModal('importTeacherModal')"
                    class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 cursor-pointer"
                    aria-label="Tutup modal">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form action="{{ route('admin.teachers.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="space-y-5 px-6 py-5">
                <!-- Info Box -->
                <div class="rounded-xl border border-sky-200/80 bg-sky-50/70 p-4 text-xs text-slate-700 space-y-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-semibold text-sky-900">Format Kolom Excel Resmi (9 Kolom)</span>
                        <a href="{{ route('admin.teachers.template') }}"
                           class="inline-flex items-center gap-1 font-medium text-indigo-600 hover:text-indigo-800 hover:underline">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Unduh Template (.xlsx)
                        </a>
                    </div>
                    <div class="overflow-x-auto rounded-lg border border-sky-200 bg-white px-3 py-2 font-mono text-[11px] text-slate-700 whitespace-nowrap">
                        No | NUPTK | NIP | NAMA | JENIS KELAMIN | Tempat Lahir | Tgl Lahir (dd-mm-yyyy) | Pendidikan Terakhir | Password
                    </div>
                    <ul class="list-disc space-y-1 pl-4 text-slate-600">
                        <li><strong class="font-medium text-slate-800">Email tidak perlu diisi:</strong> Guru melengkapi email secara mandiri di akun mereka.</li>
                        <li>Kolom <strong class="font-medium text-slate-800">Password</strong> opsional (default otomatis: <code class="rounded bg-white px-1 py-0.5 text-[11px] ring-1 ring-slate-200">password</code>).</li>
                        <li>Format tanggal lahir: <code class="rounded bg-white px-1 py-0.5 text-[11px] ring-1 ring-slate-200">dd-mm-yyyy</code> (contoh: <code class="rounded bg-white px-1 py-0.5 text-[11px] ring-1 ring-slate-200">14-05-1985</code>).</li>
                        <li>Jenis Kelamin: <code class="rounded bg-white px-1 py-0.5 text-[11px] ring-1 ring-slate-200">L</code> (Laki-laki) atau <code class="rounded bg-white px-1 py-0.5 text-[11px] ring-1 ring-slate-200">P</code> (Perempuan).</li>
                    </ul>
                </div>

                <!-- File Upload Dropzone -->
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">
                        File Excel / CSV <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50/70 px-6 py-8 text-center transition hover:border-indigo-400 hover:bg-indigo-50/30">
                        <input type="file"
                               name="file"
                               accept=".xlsx,.xls,.csv"
                               required
                               class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                               onchange="document.getElementById('fileNameDisplay').textContent = this.files[0] ? this.files[0].name : 'Format .xlsx, .xls, .csv (Maks 5MB)'">
                        <div class="pointer-events-none space-y-2">
                            <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-white text-indigo-600 shadow-sm ring-1 ring-slate-200">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="text-sm font-medium text-slate-800">
                                Tarik & lepas file ke sini, atau <span class="text-indigo-600">pilih file</span>
                            </div>
                            <div id="fileNameDisplay" class="mx-auto max-w-xs truncate text-xs text-slate-500">
                                Format .xlsx, .xls, .csv (Maks 5MB)
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end gap-2.5 border-t border-slate-100 bg-slate-50/80 px-6 py-4">
                <button type="button"
                        onclick="closeModal('importTeacherModal')"
                        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 cursor-pointer">
                    Upload & Proses Data
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Tambah Guru Baru -->
<div id="createTeacherModal"
     class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm hidden"
     onclick="if (event.target === this) closeModal('createTeacherModal')">
    <div class="relative flex max-h-[90vh] w-full max-w-xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
        <!-- Header -->
        <div class="flex items-start justify-between border-b border-slate-100 px-6 py-4">
            <div>
                <h3 class="text-base font-semibold text-slate-900">Tambah Guru Baru</h3>
                <p class="mt-0.5 text-xs text-slate-500">Lengkapi biodata dan informasi akun login guru.</p>
            </div>
            <button type="button"
                    onclick="closeModal('createTeacherModal')"
                    class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 cursor-pointer"
                    aria-label="Tutup modal">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form action="{{ route('admin.teachers.store') }}" method="POST" class="flex min-h-0 flex-1 flex-col">
            @csrf
            <div class="flex-1 space-y-4 overflow-y-auto px-6 py-5">
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">
                        Nama Lengkap & Gelar <span class="text-rose-500">*</span>
                    </label>
                    <input type="text"
                           name="name"
                           required
                           placeholder="Contoh: Ahmad Dahlan, S.Pd., M.Pd."
                           class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">NUPTK (Opsional)</label>
                        <input type="text"
                               name="nuptk"
                               placeholder="16 digit NUPTK"
                               class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 font-mono text-sm text-slate-900 placeholder:font-sans placeholder:text-slate-400 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">NIP (Opsional)</label>
                        <input type="text"
                               name="nip"
                               placeholder="18 digit NIP"
                               class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 font-mono text-sm text-slate-900 placeholder:font-sans placeholder:text-slate-400 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">
                            Jenis Kelamin <span class="text-rose-500">*</span>
                        </label>
                        <select name="gender"
                                required
                                class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                            <option value="L">Laki-laki (L)</option>
                            <option value="P">Perempuan (P)</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Pendidikan Terakhir</label>
                        <select name="last_education"
                                class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                            <option value="">-- Pilih Jenjang --</option>
                            <option value="D3">D3</option>
                            <option value="D4/S1" selected>D4 / S1</option>
                            <option value="S2">S2</option>
                            <option value="S3">S3</option>
                            <option value="SMA/SMK">SMA / SMK / Sederajat</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Tempat Lahir</label>
                        <input type="text"
                               name="birth_place"
                               placeholder="Kota / Kabupaten"
                               class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Tanggal Lahir</label>
                        <input type="date"
                               name="birth_date"
                               class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 font-mono text-sm text-slate-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">No. WhatsApp / HP</label>
                        <input type="text"
                               name="phone"
                               placeholder="081234567890"
                               class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 font-mono text-sm text-slate-900 placeholder:font-sans placeholder:text-slate-400 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Kata Sandi (Opsional)</label>
                        <div class="relative">
                            <input type="password"
                                   id="create_password"
                                   name="password"
                                   placeholder="Default: password"
                                   class="w-full rounded-lg border border-slate-300 bg-white py-2 pl-3.5 pr-10 font-mono text-sm text-slate-900 placeholder:font-sans placeholder:text-slate-400 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                            <button type="button"
                                    onclick="togglePasswordVisibility('create_password', 'create_eye_icon')"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 transition hover:text-slate-700 focus:outline-none cursor-pointer"
                                    title="Tampilkan / Sembunyikan Password">
                                <span id="create_eye_icon">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex items-start gap-2.5 rounded-lg border border-amber-200/80 bg-amber-50/70 p-3 text-xs text-amber-900">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>
                        <strong class="font-semibold">Catatan Email:</strong> Email tidak diwajibkan di sini. Guru dapat melengkapi email pribadi secara mandiri saat login pertama kali.
                    </span>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end gap-2.5 border-t border-slate-100 bg-slate-50/80 px-6 py-4">
                <button type="button"
                        onclick="closeModal('createTeacherModal')"
                        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 cursor-pointer">
                    Simpan Data Guru
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Edit Data Guru -->
<div id="editTeacherModal"
     class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm hidden"
     onclick="if (event.target === this) closeModal('editTeacherModal')">
    <div class="relative flex max-h-[90vh] w-full max-w-xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
        <!-- Header -->
        <div class="flex items-start justify-between border-b border-slate-100 px-6 py-4">
            <div>
                <h3 class="text-base font-semibold text-slate-900">Edit Data Guru</h3>
                <p class="mt-0.5 text-xs text-slate-500">Perbarui biodata, kontak, atau atur ulang kata sandi akun.</p>
            </div>
            <button type="button"
                    onclick="closeModal('editTeacherModal')"
                    class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 cursor-pointer"
                    aria-label="Tutup modal">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="editTeacherForm" method="POST" class="flex min-h-0 flex-1 flex-col">
            @csrf
            @method('PUT')
            <div class="flex-1 space-y-4 overflow-y-auto px-6 py-5">
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">
                        Nama Lengkap & Gelar <span class="text-rose-500">*</span>
                    </label>
                    <input type="text"
                           id="edit_name"
                           name="name"
                           required
                           class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">NUPTK</label>
                        <input type="text"
                               id="edit_nuptk"
                               name="nuptk"
                               placeholder="16 digit NUPTK"
                               class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 font-mono text-sm text-slate-900 placeholder:font-sans placeholder:text-slate-400 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">NIP</label>
                        <input type="text"
                               id="edit_nip"
                               name="nip"
                               placeholder="18 digit NIP"
                               class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 font-mono text-sm text-slate-900 placeholder:font-sans placeholder:text-slate-400 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">
                            Jenis Kelamin <span class="text-rose-500">*</span>
                        </label>
                        <select id="edit_gender"
                                name="gender"
                                required
                                class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                            <option value="L">Laki-laki (L)</option>
                            <option value="P">Perempuan (P)</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Pendidikan Terakhir</label>
                        <select id="edit_last_education"
                                name="last_education"
                                class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                            <option value="">-- Pilih Jenjang --</option>
                            <option value="D3">D3</option>
                            <option value="D4/S1">D4 / S1</option>
                            <option value="S2">S2</option>
                            <option value="S3">S3</option>
                            <option value="SMA/SMK">SMA / SMK / Sederajat</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Tempat Lahir</label>
                        <input type="text"
                               id="edit_birth_place"
                               name="birth_place"
                               class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Tanggal Lahir</label>
                        <input type="date"
                               id="edit_birth_date"
                               name="birth_date"
                               class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 font-mono text-sm text-slate-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">No. WhatsApp / HP</label>
                        <input type="text"
                               id="edit_phone"
                               name="phone"
                               class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 font-mono text-sm text-slate-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Email (Opsional)</label>
                        <input type="email"
                               id="edit_email"
                               name="email"
                               placeholder="email@domain.com"
                               class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Reset Kata Sandi (Opsional)</label>
                    <div class="relative">
                        <input type="password"
                               id="edit_password"
                               name="password"
                               placeholder="Biarkan kosong jika tidak ingin mengubah kata sandi"
                               class="w-full rounded-lg border border-slate-300 bg-white py-2 pl-3.5 pr-10 font-mono text-sm text-slate-900 placeholder:font-sans placeholder:text-slate-400 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        <button type="button"
                                onclick="togglePasswordVisibility('edit_password', 'edit_eye_icon')"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 transition hover:text-slate-700 focus:outline-none cursor-pointer"
                                title="Tampilkan / Sembunyikan Password">
                            <span id="edit_eye_icon">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end gap-2.5 border-t border-slate-100 bg-slate-50/80 px-6 py-4">
                <button type="button"
                        onclick="closeModal('editTeacherModal')"
                        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 cursor-pointer">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 4: Atur Penugasan Mengajar Guru -->
<div id="assignmentTeacherModal"
     class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm hidden"
     onclick="if (event.target === this) closeModal('assignmentTeacherModal')">
    <div class="relative flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
        <!-- Header -->
        <div class="flex items-start justify-between border-b border-slate-100 px-6 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-slate-900">Atur Penugasan Mengajar</h3>
                    <p id="assignment_teacher_subtitle" class="text-xs text-slate-500">
                        Memuat data guru...
                    </p>
                </div>
            </div>
            <button type="button"
                    onclick="closeModal('assignmentTeacherModal')"
                    class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 cursor-pointer"
                    aria-label="Tutup modal">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="assignmentTeacherForm" method="POST" class="flex min-h-0 flex-1 flex-col">
            @csrf
            <div class="flex-1 space-y-5 overflow-y-auto px-6 py-5">
                <!-- Info Status Wali Kelas -->
                <div id="assignment_homeroom_info" class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-white text-indigo-600 ring-1 ring-slate-200">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-slate-500">Status Peran Guru:</span>
                        <span id="assignment_homeroom_text" class="ml-1 font-semibold text-slate-900">—</span>
                    </div>
                </div>

                <!-- Mode Selector -->
                <div class="space-y-2.5">
                    <label class="block text-xs font-semibold text-slate-700">Aturan Pembatasan Mengajar</label>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-white p-3.5 transition hover:border-slate-300 has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/40 has-[:checked]:ring-1 has-[:checked]:ring-indigo-600">
                            <input type="radio"
                                   name="mode"
                                   value="unrestricted"
                                   id="mode_unrestricted"
                                   onchange="toggleAssignmentMode()"
                                   class="mt-0.5 h-4 w-4 border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <div class="space-y-0.5">
                                <span class="block text-sm font-medium text-slate-900">Bebas (Fleksibel)</span>
                                <span class="block text-xs text-slate-500">Dapat dijadwalkan mengajar mata pelajaran dan kelas apa saja.</span>
                            </div>
                        </label>

                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-white p-3.5 transition hover:border-slate-300 has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/40 has-[:checked]:ring-1 has-[:checked]:ring-indigo-600">
                            <input type="radio"
                                   name="mode"
                                   value="restricted"
                                   id="mode_restricted"
                                   onchange="toggleAssignmentMode()"
                                   class="mt-0.5 h-4 w-4 border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <div class="space-y-0.5">
                                <span class="block text-sm font-medium text-slate-900">Dibatasi Khusus</span>
                                <span class="block text-xs text-slate-500">Hanya mengajar mata pelajaran dan rombel yang ditentukan di bawah.</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Container Baris Penugasan Khusus -->
                <div id="restricted_container" class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-semibold text-slate-700">Daftar Mata Pelajaran & Rombel</label>
                        <button type="button"
                                onclick="addAssignmentRow()"
                                class="inline-flex items-center gap-1 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-700 transition hover:bg-indigo-100 cursor-pointer">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>Tambah Mapel</span>
                        </button>
                    </div>

                    <div id="assignment_rows" class="space-y-3">
                        <!-- Dynamic rows inserted by JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end gap-2.5 border-t border-slate-100 bg-slate-50/80 px-6 py-4">
                <button type="button"
                        onclick="closeModal('assignmentTeacherModal')"
                        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 cursor-pointer">
                    Simpan Penugasan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 5: Atur Pengelola Tabungan Siswa -->
<div id="savingsOfficerModal"
     class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm hidden"
     onclick="if (event.target === this) closeModal('savingsOfficerModal')">
    <div class="relative flex max-h-[90vh] w-full max-w-xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
        <!-- Header -->
        <div class="flex items-start justify-between border-b border-slate-100 px-6 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-50 text-teal-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-slate-900">Atur Pengelola Tabungan Siswa</h3>
                    <p id="savings_modal_subtitle" class="text-xs text-slate-500">
                        Atur status pengelola dan rombel/kelas yang dikelola.
                    </p>
                </div>
            </div>
            <button type="button"
                    onclick="closeModal('savingsOfficerModal')"
                    class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 cursor-pointer"
                    aria-label="Tutup modal">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="savingsOfficerForm" method="POST" class="flex min-h-0 flex-1 flex-col">
            @csrf
            @method('PATCH')
            <div class="flex-1 space-y-5 overflow-y-auto px-6 py-5">
                <!-- Status Pengelola Tabungan -->
                <div class="space-y-2.5">
                    <label class="block text-xs font-semibold text-slate-700">Status Penugasan Pengelola</label>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-white p-3.5 transition hover:border-slate-300 has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50/40 has-[:checked]:ring-1 has-[:checked]:ring-emerald-600">
                            <input type="radio"
                                   name="is_savings_officer"
                                   value="1"
                                   id="savings_officer_active"
                                   onchange="toggleSavingsOfficerStatus()"
                                   class="mt-0.5 h-4 w-4 border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <div class="space-y-0.5">
                                <span class="block text-sm font-medium text-slate-900">Aktif (Ditugaskan)</span>
                                <span class="block text-xs text-slate-500">Berwenang melayani transaksi tabungan siswa.</span>
                            </div>
                        </label>

                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-white p-3.5 transition hover:border-slate-300 has-[:checked]:border-rose-500 has-[:checked]:bg-rose-50/40 has-[:checked]:ring-1 has-[:checked]:ring-rose-500">
                            <input type="radio"
                                   name="is_savings_officer"
                                   value="0"
                                   id="savings_officer_inactive"
                                   onchange="toggleSavingsOfficerStatus()"
                                   class="mt-0.5 h-4 w-4 border-slate-300 text-rose-600 focus:ring-rose-500">
                            <div class="space-y-0.5">
                                <span class="block text-sm font-medium text-slate-900">Nonaktif</span>
                                <span class="block text-xs text-slate-500">Bukan / lepas jabatan pengelola tabungan siswa.</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Cakupan Wewenang Rombel / Kelas -->
                <div id="savings_scope_section" class="space-y-4 rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                    <div class="space-y-2.5">
                        <label class="block text-xs font-semibold text-slate-700">Cakupan Rombel / Kelas</label>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-white p-3 transition hover:border-slate-300 has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/40 has-[:checked]:ring-1 has-[:checked]:ring-indigo-600">
                                <input type="radio"
                                       name="savings_scope"
                                       value="all"
                                       id="savings_scope_all"
                                       onchange="toggleSavingsScope()"
                                       class="mt-0.5 h-4 w-4 border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <div class="space-y-0.5">
                                    <span class="block text-xs font-semibold text-slate-900">Semua Kelas</span>
                                    <span class="block text-[11px] text-slate-500">Dapat melayani seluruh rombel tanpa batasan.</span>
                                </div>
                            </label>

                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-white p-3 transition hover:border-slate-300 has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/40 has-[:checked]:ring-1 has-[:checked]:ring-indigo-600">
                                <input type="radio"
                                       name="savings_scope"
                                       value="restricted"
                                       id="savings_scope_restricted"
                                       onchange="toggleSavingsScope()"
                                       class="mt-0.5 h-4 w-4 border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <div class="space-y-0.5">
                                    <span class="block text-xs font-semibold text-slate-900">Kelas Tertentu</span>
                                    <span class="block text-[11px] text-slate-500">Hanya mengelola rombel yang dipilih di bawah.</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Checklist Kelas Tertentu -->
                    <div id="savings_classes_checklist_container" class="space-y-2.5 border-t border-slate-200 pt-3">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-medium text-slate-700">Pilih Kelas yang Dikelola</label>
                            <div class="flex items-center gap-2 text-xs">
                                <button type="button" onclick="toggleAllSavingsClasses(true)" class="font-medium text-indigo-600 hover:text-indigo-800 hover:underline cursor-pointer">
                                    Pilih Semua
                                </button>
                                <span class="text-slate-300">·</span>
                                <button type="button" onclick="toggleAllSavingsClasses(false)" class="text-slate-500 hover:text-slate-700 hover:underline cursor-pointer">
                                    Batalkan
                                </button>
                            </div>
                        </div>
                        <div class="grid max-h-48 grid-cols-2 gap-2 overflow-y-auto rounded-lg border border-slate-200 bg-white p-2.5 sm:grid-cols-3">
                            @foreach($classes as $c)
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200/80 bg-slate-50/60 px-2.5 py-2 text-xs transition hover:border-indigo-300 hover:bg-indigo-50/40 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/60">
                                    <input type="checkbox"
                                           name="class_ids[]"
                                           value="{{ $c->id }}"
                                           class="savings-class-checkbox h-3.5 w-3.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="truncate font-medium text-slate-800">{{ $c->name }}</span>
                                    <span class="ml-auto text-[11px] text-slate-400 tabular-nums">({{ $c->students_count ?? $c->students()->count() }})</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end gap-2.5 border-t border-slate-100 bg-slate-50/80 px-6 py-4">
                <button type="button"
                        onclick="closeModal('savingsOfficerModal')"
                        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 cursor-pointer">
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Tutup modal aktif saat menekan tombol Escape
    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;
        const modalIds = [
            'importTeacherModal',
            'createTeacherModal',
            'editTeacherModal',
            'assignmentTeacherModal',
            'savingsOfficerModal'
        ];
        modalIds.forEach(id => {
            const modal = document.getElementById(id);
            if (modal && !modal.classList.contains('hidden')) {
                closeModal(id);
            }
        });
    });

    function togglePasswordVisibility(inputId, iconContainerId) {
        const input = document.getElementById(inputId);
        const iconContainer = document.getElementById(iconContainerId);
        if (!input || !iconContainer) return;

        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';

        if (isPassword) {
            iconContainer.innerHTML = `
                <svg class="h-4 w-4 text-indigo-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                </svg>
            `;
        } else {
            iconContainer.innerHTML = `
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
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
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
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
                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200/80 bg-white px-2.5 py-1.5 text-xs transition hover:border-indigo-300 hover:bg-indigo-50/40 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/60">
                    <input type="checkbox" name="assignments[${idx}][class_ids][]" value="${c.id}" ${isChecked} class="class-checkbox-${idx} h-3.5 w-3.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="truncate font-medium text-slate-700">${c.name}</span>
                </label>
            `;
        });

        const rowCard = document.createElement('div');
        rowCard.className = 'assignment-row-card space-y-3 rounded-xl border border-slate-200 bg-slate-50/60 p-4';
        rowCard.id = `assignment_row_${idx}`;
        rowCard.innerHTML = `
            <div class="flex items-end gap-2.5">
                <div class="flex-1">
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Mata Pelajaran</label>
                    <select name="assignments[${idx}][subject_id]" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        ${subjectOptions}
                    </select>
                </div>
                <button type="button" onclick="removeAssignmentRow(${idx})" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-white px-3 py-2 text-xs font-medium text-rose-600 transition hover:bg-rose-50 cursor-pointer" title="Hapus Mapel Ini">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <span>Hapus</span>
                </button>
            </div>
            <div>
                <div class="mb-1.5 flex items-center justify-between">
                    <label class="text-xs font-medium text-slate-700">Kelas / Rombel yang Diajar</label>
                    <div class="flex items-center gap-2 text-xs">
                        <button type="button" onclick="toggleAllClasses(${idx}, true)" class="font-medium text-indigo-600 hover:text-indigo-800 hover:underline cursor-pointer">Pilih Semua</button>
                        <span class="text-slate-300">·</span>
                        <button type="button" onclick="toggleAllClasses(${idx}, false)" class="text-slate-500 hover:text-slate-700 hover:underline cursor-pointer">Batalkan</button>
                    </div>
                </div>
                <div class="grid max-h-36 grid-cols-2 gap-1.5 overflow-y-auto rounded-lg border border-slate-200 bg-slate-100/60 p-2 sm:grid-cols-4">
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

    function openSavingsModal(data) {
        document.getElementById('savingsOfficerForm').action = data.action_url;
        document.getElementById('savings_modal_subtitle').textContent = `Pengaturan wewenang tabungan siswa untuk ${data.name}`;

        if (data.is_savings_officer) {
            document.getElementById('savings_officer_active').checked = true;
        } else {
            document.getElementById('savings_officer_inactive').checked = true;
        }

        if (data.savings_scope === 'restricted') {
            document.getElementById('savings_scope_restricted').checked = true;
        } else {
            document.getElementById('savings_scope_all').checked = true;
        }

        const managedIds = data.managed_class_ids || [];
        document.querySelectorAll('.savings-class-checkbox').forEach(cb => {
            cb.checked = managedIds.includes(parseInt(cb.value));
        });

        toggleSavingsOfficerStatus();
        openModal('savingsOfficerModal');
    }

    function toggleSavingsOfficerStatus() {
        const isActive = document.getElementById('savings_officer_active').checked;
        const scopeSection = document.getElementById('savings_scope_section');
        if (isActive) {
            scopeSection.classList.remove('hidden');
            toggleSavingsScope();
        } else {
            scopeSection.classList.add('hidden');
        }
    }

    function toggleSavingsScope() {
        const isRestricted = document.getElementById('savings_scope_restricted').checked;
        const checklistContainer = document.getElementById('savings_classes_checklist_container');
        if (isRestricted) {
            checklistContainer.classList.remove('hidden');
        } else {
            checklistContainer.classList.add('hidden');
        }
    }

    function toggleAllSavingsClasses(check) {
        document.querySelectorAll('.savings-class-checkbox').forEach(cb => {
            cb.checked = check;
        });
    }
</script>
@endpush