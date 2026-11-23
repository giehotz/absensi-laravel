<!-- Main Data Card (Integrated Toolbar + Table + Pagination) -->
<div class="overflow-hidden rounded-xl border-2 border-black bg-white shadow-[5px_5px_0px_0px_#000]">
    <!-- Search & Sort Toolbar -->
    <div class="border-b-2 border-black bg-[#FFFDF5] p-4">
        <form action="{{ route('admin.teachers.index') }}" method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <!-- Search Input -->
            <div class="relative flex-1 max-w-md">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-black" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text"
                       name="search"
                       value="{{ $search ?? '' }}"
                       placeholder="Cari nama guru, NUPTK, NIP, atau email..."
                       class="w-full rounded-lg border-2 border-black bg-white py-2 pl-9 pr-9 text-xs font-semibold text-black placeholder:font-medium placeholder:text-slate-400 shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFF9DB]/50 focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none sm:text-sm">
                @if(!empty($search))
                    <a href="{{ route('admin.teachers.index', request()->except(['search', 'page'])) }}"
                       class="absolute right-2 top-1/2 -translate-y-1/2 rounded border border-black bg-slate-100 p-0.5 text-black transition hover:bg-[#FF6B6B] hover:text-white"
                       title="Hapus kata kunci">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                @endif
            </div>

            <!-- Sort & Filter Controls -->
            <div class="flex flex-wrap items-center gap-2">
                <div class="flex items-center gap-2">
                    <label for="sort_select" class="text-xs font-extrabold uppercase tracking-wider text-black whitespace-nowrap">Urutkan:</label>
                    <select id="sort_select"
                            name="sort"
                            onchange="this.form.submit()"
                            class="cursor-pointer rounded-lg border-2 border-black bg-white py-2 pl-3 pr-8 text-xs font-bold text-black shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFF9DB]/50 focus:outline-none sm:text-sm">
                        <option value="name_asc" {{ ($sort ?? 'name_asc') === 'name_asc' ? 'selected' : '' }}>Nama: A - Z</option>
                        <option value="name_desc" {{ ($sort ?? '') === 'name_desc' ? 'selected' : '' }}>Nama: Z - A</option>
                        <option value="latest" {{ ($sort ?? '') === 'latest' ? 'selected' : '' }}>Terbaru Dibuat</option>
                        <option value="oldest" {{ ($sort ?? '') === 'oldest' ? 'selected' : '' }}>Paling Awal Dibuat</option>
                    </select>
                </div>

                <button type="submit"
                        class="inline-flex cursor-pointer items-center rounded-lg border-2 border-black bg-[#FFD43B] px-4 py-2 text-xs font-extrabold uppercase tracking-wide text-black shadow-[2px_2px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:bg-[#FCC419] hover:shadow-[3px_3px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none sm:text-sm">
                    Cari
                </button>

                @if(!empty($search) || ($sort ?? 'name_asc') !== 'name_asc')
                    <a href="{{ route('admin.teachers.index') }}"
                       class="inline-flex items-center gap-1 rounded-lg border-2 border-black bg-white px-3 py-2 text-xs font-bold text-black shadow-[2px_2px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:bg-slate-100 hover:shadow-[3px_3px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none sm:text-sm"
                       title="Reset Semua Filter">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span>Reset</span>
                    </a>
                @endif
            </div>
        </form>

        @if(!empty($search))
            <div class="mt-3 flex items-center justify-between rounded-lg border-2 border-black bg-[#FFF9DB] px-3.5 py-2 text-xs text-black shadow-[2px_2px_0px_0px_#000]">
                <span class="font-medium">
                    Menampilkan <strong class="font-black tabular-nums">{{ $teachers->total() }}</strong> hasil untuk
                    <strong class="font-black">"{{ $search }}"</strong>
                </span>
                <a href="{{ route('admin.teachers.index') }}" class="font-extrabold text-rose-700 underline decoration-2 hover:text-rose-900">
                    Hapus Pencarian
                </a>
            </div>
        @endif
    </div>

    <!-- Data Table -->
    <div class="overflow-x-auto">
        <table class="w-full min-w-[960px] text-left text-sm">
            <thead class="border-b-2 border-black bg-[#FFF9DB] text-xs font-black uppercase tracking-wider text-black">
                <tr>
                    <th class="w-12 py-3.5 pl-5 pr-3 text-right">No</th>
                    <th class="px-4 py-3.5">
                        <a href="{{ route('admin.teachers.index', array_merge(request()->except(['sort', 'page']), ['sort' => ($sort ?? 'name_asc') === 'name_asc' ? 'name_desc' : 'name_asc'])) }}"
                           class="group inline-flex items-center gap-1.5 text-black transition hover:text-slate-700"
                           title="Klik untuk membalikkan urutan A-Z / Z-A">
                            <span>Profil Guru</span>
                            @if(($sort ?? 'name_asc') === 'name_asc')
                                <span class="inline-flex items-center rounded border border-black bg-black px-1.5 py-0.5 text-[10px] font-extrabold text-[#FFD43B]">A-Z ↑</span>
                            @elseif(($sort ?? '') === 'name_desc')
                                <span class="inline-flex items-center rounded border border-black bg-black px-1.5 py-0.5 text-[10px] font-extrabold text-[#FFD43B]">Z-A ↓</span>
                            @else
                                <span class="rounded border border-black/30 bg-white px-1 py-0.5 text-[10px] text-slate-500 group-hover:border-black group-hover:text-black">↕</span>
                            @endif
                        </a>
                    </th>
                    <th class="px-4 py-3.5">NUPTK / NIP</th>
                    <th class="px-4 py-3.5">TTL & Pendidikan</th>
                    <th class="px-4 py-3.5">Peran & Penugasan</th>
                    <th class="py-3.5 pl-4 pr-5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y-2 divide-black/15 bg-white">
                @forelse($teachers as $index => $teacher)
                    @php
                        $teacherName = $teacher->user->name ?? '-';
                        $initials = collect(explode(' ', trim($teacherName)))
                            ->filter()
                            ->take(2)
                            ->map(fn($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                            ->implode('');
                        $avatarTone = match($teacher->gender) {
                            'L' => 'bg-[#A5D8FF] text-black',
                            'P' => 'bg-[#FCC2D7] text-black',
                            default => 'bg-slate-200 text-black',
                        };
                    @endphp
                    <tr class="transition-colors hover:bg-[#FFFDF0]">
                        <!-- No -->
                        <td class="py-4 pl-5 pr-3 text-right align-top text-xs font-extrabold text-slate-500 tabular-nums">
                            {{ $teachers->firstItem() + $index }}
                        </td>

                        <!-- Profil Guru (Avatar, Name, Gender, Email, Phone) -->
                        <td class="px-4 py-4 align-top">
                            <div class="flex items-start gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border-2 border-black text-xs font-black shadow-[2px_2px_0px_0px_#000] {{ $avatarTone }}">
                                    {{ $initials ?: '?' }}
                                </div>
                                <div class="min-w-0 space-y-1.5">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <span class="font-extrabold text-black leading-snug">
                                            {{ $teacherName }}
                                        </span>
                                        @if($teacher->gender === 'L')
                                            <span class="inline-flex items-center rounded border border-black bg-[#D0EBFF] px-1.5 py-0.5 text-[10px] font-black text-black" title="Laki-laki">L</span>
                                        @elseif($teacher->gender === 'P')
                                            <span class="inline-flex items-center rounded border border-black bg-[#FCC2D7] px-1.5 py-0.5 text-[10px] font-black text-black" title="Perempuan">P</span>
                                        @endif
                                    </div>

                                    <!-- Email & Verification Status -->
                                    <div class="flex flex-wrap items-center gap-1.5 text-xs">
                                        @if($teacher->user?->email)
                                            <span class="max-w-[200px] truncate font-mono text-[11px] font-semibold text-slate-700" title="{{ $teacher->user->email }}">
                                                {{ $teacher->user->email }}
                                            </span>
                                            <span class="inline-flex items-center gap-0.5 rounded border border-black bg-[#D3F9D8] px-1.5 py-0.5 text-[10px] font-extrabold text-black">
                                                <svg class="h-2.5 w-2.5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                                Terdaftar
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded border border-black bg-[#FFF3BF] px-2 py-0.5 text-[10px] font-extrabold text-black" title="Guru dapat melengkapi email secara mandiri saat login">
                                                <span class="h-1.5 w-1.5 rounded-full border border-black bg-[#F59F00]"></span>
                                                Email belum diisi
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Phone -->
                                    @if($teacher->phone)
                                        <div class="flex items-center gap-1 font-mono text-[11px] font-semibold text-slate-600 tabular-nums">
                                            <svg class="h-3.5 w-3.5 shrink-0 text-black" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
                                    <span class="w-12 shrink-0 rounded border border-black/20 bg-slate-100 px-1 py-0.5 text-center font-sans text-[10px] font-extrabold text-black">NUPTK</span>
                                    <span class="{{ $teacher->nuptk ? 'font-bold text-black' : 'text-slate-400' }}">
                                        {{ $teacher->nuptk ?: '—' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="w-12 shrink-0 rounded border border-black/20 bg-slate-100 px-1 py-0.5 text-center font-sans text-[10px] font-extrabold text-black">NIP</span>
                                    <span class="{{ $teacher->nip ? 'font-semibold text-slate-700' : 'text-slate-400' }}">
                                        {{ $teacher->nip ?: '—' }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        <!-- TTL & Pendidikan -->
                        <td class="px-4 py-4 align-top">
                            <div class="space-y-1.5">
                                <div class="text-xs">
                                    <div class="font-bold text-black">{{ $teacher->birth_place ?: '—' }}</div>
                                    <div class="font-mono text-[11px] font-semibold text-slate-600 tabular-nums">
                                        {{ $teacher->birth_date ? $teacher->birth_date->format('d-m-Y') : '—' }}
                                    </div>
                                </div>
                                @if($teacher->last_education)
                                    <span class="inline-flex items-center rounded border border-black bg-slate-100 px-2 py-0.5 text-[11px] font-extrabold text-black">
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
                                    <span class="w-9 shrink-0 pt-0.5 text-[10px] font-extrabold uppercase text-slate-500">Wali</span>
                                    @if($teacher->homeroomClasses->isNotEmpty())
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($teacher->homeroomClasses as $cls)
                                                <span class="inline-flex items-center rounded border border-black bg-[#D3F9D8] px-2 py-0.5 text-[11px] font-extrabold text-black">
                                                    {{ $cls->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="pt-0.5 text-[11px] font-medium text-slate-400 italic">Bukan wali kelas</span>
                                    @endif
                                </div>

                                <!-- KBM -->
                                <div class="flex items-start gap-2">
                                    <span class="w-9 shrink-0 pt-0.5 text-[10px] font-extrabold uppercase text-slate-500">KBM</span>
                                    @if($teacher->assignments->isNotEmpty())
                                        @php
                                            $assignedSubNames = $teacher->assignments->pluck('subject.name')->unique()->filter();
                                            $assignedClassCount = $teacher->assignments->pluck('school_class_id')->unique()->count();
                                        @endphp
                                        <div class="flex flex-wrap items-center gap-1">
                                            @foreach($assignedSubNames as $sName)
                                                <span class="inline-flex items-center rounded border border-black bg-[#E5DBFF] px-2 py-0.5 text-[11px] font-bold text-black">
                                                    {{ $sName }}
                                                </span>
                                            @endforeach
                                            <span class="inline-flex items-center rounded border border-black bg-white px-1.5 py-0.5 font-mono text-[10px] font-extrabold text-black tabular-nums">
                                                {{ $assignedClassCount }} Rombel
                                            </span>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center rounded border border-black bg-[#FFF9DB] px-2 py-0.5 text-[11px] font-bold text-black">
                                            Semua Mapel
                                        </span>
                                    @endif
                                </div>

                                <!-- Tambahan: Pengelola Tabungan & Petugas MBG -->
                                @if($teacher->is_savings_officer || $teacher->is_petugas_mbg)
                                    <div class="flex flex-wrap items-center gap-1.5 pt-0.5">
                                        @if($teacher->is_savings_officer)
                                            @if($teacher->savings_scope === 'all')
                                                <span class="inline-flex items-center gap-1 rounded border-2 border-black bg-[#20C997] px-2 py-0.5 text-[10px] font-black text-black shadow-[2px_2px_0px_0px_#000]"
                                                      title="Mengelola tabungan seluruh rombel/kelas">
                                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                    </svg>
                                                    <span>Tabungan: Semua Kelas</span>
                                                </span>
                                            @else
                                                @php
                                                    $managedCount = $teacher->managedSavingsClasses->count();
                                                    $managedNames = $teacher->managedSavingsClasses->pluck('name')->implode(', ');
                                                @endphp
                                                <span class="inline-flex items-center gap-1 rounded border-2 border-black bg-[#FFD43B] px-2 py-0.5 text-[10px] font-black text-black shadow-[2px_2px_0px_0px_#000]"
                                                      title="Mengelola kelas: {{ $managedNames }}">
                                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                    </svg>
                                                    <span>Tabungan: {{ $managedCount > 0 ? ($managedCount <= 2 ? $managedNames : $managedCount . ' Kelas') : 'Belum Ada Kelas' }}</span>
                                                </span>
                                            @endif
                                        @endif

                                        @if($teacher->is_petugas_mbg)
                                            <span class="inline-flex items-center gap-1 rounded border-2 border-black bg-[#FFC078] px-2 py-0.5 text-[10px] font-black text-black shadow-[2px_2px_0px_0px_#000]"
                                                  title="Ditugaskan sebagai Petugas MBG (Makan Bergizi Gratis)">
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24">
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
                            <div class="inline-flex flex-col items-end gap-2">
                                <!-- Role / Assignment Actions -->
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button"
                                            onclick="openAssignmentModal({{ $teacher->id }}, '{{ addslashes($teacher->user->name ?? 'Guru') }}', {{ $teacher->homeroomClasses->isNotEmpty() ? 'true' : 'false' }}, {{ json_encode($teacher->homeroomClasses->pluck('id')->all()) }}, {{ json_encode($teacher->homeroomClasses->pluck('name')->all()) }})"
                                            class="inline-flex cursor-pointer items-center gap-1 rounded-lg border-2 border-black bg-[#E5DBFF] px-2.5 py-1 text-[11px] font-extrabold text-black shadow-[2px_2px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:bg-[#D0BFFF] hover:shadow-[3px_3px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none"
                                            title="Atur Mata Pelajaran dan Rombel yang Diajar">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24">
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
                                            class="inline-flex cursor-pointer items-center gap-1 rounded-lg border-2 border-black px-2.5 py-1 text-[11px] font-extrabold text-black shadow-[2px_2px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:shadow-[3px_3px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none {{ $teacher->is_savings_officer ? 'bg-[#63E6BE] hover:bg-[#38D9A9]' : 'bg-white hover:bg-[#FFF3BF]' }}"
                                            title="{{ $teacher->is_savings_officer ? 'Atur Wewenang Rombel Pengelola Tabungan' : 'Tunjuk Sebagai Pengelola Tabungan Siswa' }}">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        <span>Tabungan</span>
                                    </button>
                                </div>

                                <!-- Edit & Delete Actions -->
                                <div class="inline-flex items-center gap-1.5">
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
                                            class="inline-flex cursor-pointer items-center gap-1 rounded-lg border-2 border-black bg-[#FFF3BF] px-2.5 py-1 text-[11px] font-extrabold text-black shadow-[2px_2px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:bg-[#FFD43B] hover:shadow-[3px_3px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none"
                                            title="Edit Data Guru">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24">
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
                                                class="inline-flex cursor-pointer items-center gap-1 rounded-lg border-2 border-black bg-[#FFE3E3] px-2.5 py-1 text-[11px] font-extrabold text-black shadow-[2px_2px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:bg-[#FF6B6B] hover:text-white hover:shadow-[3px_3px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none"
                                                title="Hapus Guru">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24">
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
                                <div class="flex h-14 w-14 items-center justify-center rounded-xl border-2 border-black bg-[#FFF9DB] text-black shadow-[3px_3px_0px_0px_#000]">
                                    <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                @if(!empty($search))
                                    <h3 class="mt-4 text-sm font-black text-black">
                                        Data guru tidak ditemukan
                                    </h3>
                                    <p class="mt-1 text-xs font-medium text-slate-600">
                                        Tidak ada guru yang cocok dengan kata kunci "{{ $search }}".
                                    </p>
                                    <a href="{{ route('admin.teachers.index') }}"
                                       class="mt-4 inline-flex items-center gap-1.5 rounded-lg border-2 border-black bg-[#FFD43B] px-4 py-2 text-xs font-extrabold uppercase text-black shadow-[3px_3px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:bg-[#FCC419] hover:shadow-[4px_4px_0px_0px_#000]">
                                        Tampilkan Semua Guru
                                    </a>
                                @else
                                    <h3 class="mt-4 text-sm font-black text-black">
                                        Belum ada data guru
                                    </h3>
                                    <p class="mt-1 text-xs font-medium text-slate-600">
                                        Mulai dengan menambahkan guru secara manual atau mengimpor dari file Excel.
                                    </p>
                                    <div class="mt-4 flex items-center gap-2.5">
                                        <button type="button"
                                                onclick="openModal('importTeacherModal')"
                                                class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border-2 border-black bg-[#FFD43B] px-3.5 py-2 text-xs font-extrabold uppercase text-black shadow-[3px_3px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:bg-[#FCC419]">
                                            Import Excel
                                        </button>
                                        <button type="button"
                                                onclick="openModal('createTeacherModal')"
                                                class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border-2 border-black bg-[#20C997] px-3.5 py-2 text-xs font-extrabold uppercase text-black shadow-[3px_3px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:bg-[#12B886]">
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
        <div class="border-t-2 border-black bg-[#FFFDF5] px-5 py-3.5">
            {{ $teachers->links() }}
        </div>
    @endif
</div>
