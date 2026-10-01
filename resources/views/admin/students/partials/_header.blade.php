    <!-- Header, Filter & Action -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white neo-box p-5">
        <div>
            <h2 class="font-heading font-black text-xl text-black flex items-center gap-2">
                <span>🎓</span> Data Siswa & Identitas QR
            </h2>
            <p class="text-xs font-semibold text-slate-600 mt-1.5">
                Kelola data profil siswa, generate identifikasi QR unik, dan cetak kartu absensi.
            </p>
        </div>

            <!-- Filter Periode & Kelas -->
            <form method="GET" action="{{ route('admin.students.index') }}" class="flex flex-wrap items-center gap-2">
                @if(!empty($perPage) && $perPage != '25')
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                @endif
                @if(!empty($search))
                    <input type="hidden" name="search" value="{{ $search }}">
                @endif

                <!-- Filter Tahun Pelajaran -->
                @if(!empty($availableAcademicYears) && count($availableAcademicYears) > 0)
                    <select name="academic_year" onchange="this.form.submit()" class="px-3 py-2 neo-input text-xs bg-white font-black text-black cursor-pointer shadow-[2px_2px_0px_#000]" title="Pilih Tahun Pelajaran">
                        @foreach($availableAcademicYears as $yearName)
                            <option value="{{ $yearName }}" {{ $selectedAcademicYear == $yearName ? 'selected' : '' }}>
                                TP {{ $yearName }} {{ $yearName == ($activeYearName ?? '') ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                @endif

                <!-- Filter Kelas -->
                <select name="class_id" onchange="this.form.submit()" class="px-3 py-2 neo-input text-xs bg-[#FFF9DB] font-bold cursor-pointer shadow-[2px_2px_0px_#000]" title="Filter Berdasarkan Rombel">
                    <option value="">-- Semua Kelas --</option>
                    @foreach($classes as $cls)
                        <option value="{{ $cls->id }}" {{ $selectedClassId == $cls->id ? 'selected' : '' }}>
                            {{ $cls->name }}
                        </option>
                    @endforeach
                </select>
            </form>

            <!-- Unduh Template Excel -->
            <a href="{{ route('admin.students.template') }}" 
               class="neo-btn bg-[#FFF9DB] hover:bg-[#ffec99] text-black p-2.5 text-xs flex items-center justify-center cursor-pointer font-heading shadow-[2px_2px_0px_#000] group relative" 
               title="Unduh Template Excel" aria-label="Unduh Template Excel">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span class="absolute -top-9 left-1/2 -translate-x-1/2 bg-black text-white text-[10px] font-black uppercase px-2 py-0.5 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap border border-black shadow-[2px_2px_0px_0px_#FFD43B] z-50">
                    Unduh Template
                </span>
            </a>

            <!-- Upload Excel Siswa -->
            <button type="button" onclick="openModal('importStudentModal')" 
                    class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black p-2.5 text-xs flex items-center justify-center cursor-pointer font-heading shadow-[2px_2px_0px_#000] group relative"
                    title="Upload File Excel" aria-label="Upload File Excel">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <span class="absolute -top-9 left-1/2 -translate-x-1/2 bg-black text-white text-[10px] font-black uppercase px-2 py-0.5 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap border border-black shadow-[2px_2px_0px_0px_#FFD43B] z-50">
                    Upload Excel
                </span>
            </button>

            <!-- Studio Cetak Kartu -->
            <a href="{{ route('admin.students.cards', ['class_id' => $selectedClassId]) }}" 
               class="neo-btn bg-white hover:bg-slate-100 text-black p-2.5 text-xs flex items-center justify-center cursor-pointer font-heading shadow-[2px_2px_0px_#000] group relative"
               title="Studio Cetak Kartu" aria-label="Studio Cetak Kartu">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                </svg>
                <span class="absolute -top-9 left-1/2 -translate-x-1/2 bg-black text-white text-[10px] font-black uppercase px-2 py-0.5 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap border border-black shadow-[2px_2px_0px_0px_#FFD43B] z-50">
                    Studio Cetak
                </span>
            </a>

            <!-- Kenaikan Kelas & Kelulusan -->
            <a href="{{ route('admin.students.promotion.index') }}" 
               class="neo-btn bg-[#B197FC] hover:bg-[#9775fa] text-black px-3 py-2 text-xs flex items-center gap-1.5 cursor-pointer font-heading font-black shadow-[2px_2px_0px_#000] group relative" 
               title="Kenaikan Kelas & Kelulusan Siswa" aria-label="Kenaikan Kelas & Kelulusan Siswa">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
                <span class="absolute -top-9 left-1/2 -translate-x-1/2 bg-black text-white text-[10px] font-black uppercase px-2 py-0.5 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap border border-black shadow-[2px_2px_0px_0px_#FFD43B] z-50">Kenaikan Kelas</span>
            </a>

            <!-- Tambah Siswa Baru -->
            <button type="button" onclick="openModal('createStudentModal')" 
                    class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black p-2.5 text-xs flex items-center justify-center cursor-pointer font-heading shadow-[2px_2px_0px_#000] group relative"
                    title="Tambah Siswa Baru" aria-label="Tambah Siswa Baru">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span class="absolute -top-9 left-1/2 -translate-x-1/2 bg-black text-white text-[10px] font-black uppercase px-2 py-0.5 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap border border-black shadow-[2px_2px_0px_0px_#FFD43B] z-50">
                    Tambah Siswa
                </span>
            </button>
        </div>
    </div>
