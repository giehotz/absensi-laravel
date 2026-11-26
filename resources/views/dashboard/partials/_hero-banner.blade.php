<!-- SECTION 2: HERO INSTITUTION BANNER & QUICK ACTIONS -->
<section class="neo-card bg-[#3563E9] rounded-2xl p-5 sm:p-6 text-white relative overflow-hidden">
    <!-- Decorative Watermark Background -->
    <div class="pointer-events-none select-none absolute -right-6 -bottom-10 text-[120px] font-black text-white/5 tracking-tighter leading-none">
        MIN2
    </div>

    <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
        <!-- Left Column (7 cols): School Identity & Academic Metadata -->
        <div class="lg:col-span-7 space-y-4">
            <!-- Top Badges -->
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 bg-[#FFD43B] text-slate-900 border-2 border-slate-900 rounded-md px-2.5 py-0.5 text-[11px] font-extrabold uppercase tracking-wide shadow-[2px_2px_0_#0F172A]">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-900"></span>
                    PUSAT KONTROL ADMIN
                </span>
                <span class="bg-white/15 backdrop-blur-sm border border-white/30 rounded-md px-2.5 py-0.5 text-[11px] font-bold tracking-wide">
                    NPSN: {{ $schoolSetting->npsn ?? '60705691' }}
                </span>
                <span class="bg-emerald-300 text-slate-900 border-2 border-slate-900 rounded-md px-2.5 py-0.5 text-[11px] font-extrabold shadow-[1.5px_1.5px_0_#0F172A]">
                    {{ $schoolSetting->level ?? 'Madrasah Ibtidaiyah' }}
                </span>
            </div>

            <!-- Brand Logo & Name -->
            <div class="flex items-center gap-4">
                @if(!empty($schoolSetting->logo) && \Illuminate\Support\Facades\Storage::disk('public')->exists($schoolSetting->logo))
                    <div class="w-14 h-14 sm:w-16 sm:h-16 bg-white border-2 border-slate-900 shadow-[3px_3px_0_#0F172A] rounded-xl flex items-center justify-center p-1 shrink-0">
                        <img src="{{ asset('storage/' . $schoolSetting->logo) }}" alt="Logo {{ $schoolSetting->school_name ?? 'Sekolah' }}" class="w-full h-full object-contain">
                    </div>
                @else
                    <div class="w-14 h-14 sm:w-16 sm:h-16 bg-[#FFD43B] text-slate-900 border-2 border-slate-900 shadow-[3px_3px_0_#0F172A] rounded-xl flex items-center justify-center font-black text-2xl sm:text-3xl shrink-0">
                        {{ !empty($schoolSetting->school_name) ? strtoupper(substr($schoolSetting->school_name, 0, 1)) : 'M' }}
                    </div>
                @endif
                <div>
                    <h2 class="text-2xl sm:text-3xl font-black tracking-tight leading-none uppercase">
                        {{ $schoolSetting->school_name ?? 'MIN 2 TANGGAMUS' }}
                    </h2>
                    <p class="mt-1.5 text-xs sm:text-sm text-blue-100 flex items-center gap-1.5 font-medium">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-rose-300 shrink-0"></i>
                        <span>{{ $schoolSetting->school_address ?? 'Jl. Lapangan Ampera Purwodadi No. 109, Kec. Gisting' }}</span>
                    </p>
                </div>
            </div>

            <!-- Unified Single-Row Academic Metadata Pills (Eliminates Orphan Pill) -->
            <div class="flex flex-wrap items-center gap-2 pt-1">
                <div class="inline-flex items-center gap-1.5 bg-white text-slate-900 border-2 border-slate-900 rounded-lg px-2.5 py-1 text-[11px] font-bold shadow-[2px_2px_0_#0F172A]">
                    <i data-lucide="graduation-cap" class="w-3.5 h-3.5 text-blue-600"></i>
                    <span>T.A: {{ $academicYear->name ?? '2026/2027' }}</span>
                    <span class="text-slate-300">|</span>
                    <span class="text-blue-700 font-extrabold uppercase">Semester {{ $academicYear->semester ?? 'Ganjil' }}</span>
                </div>
                <div class="inline-flex items-center gap-1.5 bg-white text-slate-900 border-2 border-slate-900 rounded-lg px-2.5 py-1 text-[11px] font-bold shadow-[2px_2px_0_#0F172A]">
                    <i data-lucide="alarm-clock" class="w-3.5 h-3.5 text-rose-500"></i>
                    <span>Masuk: {{ substr($setting->school_start_time ?? '07:00:00', 0, 5) }} WIB</span>
                    <span class="text-slate-300">•</span>
                    <span>Toleransi: {{ $setting->tolerance_minutes ?? 15 }}m</span>
                </div>
                <div class="inline-flex items-center gap-1.5 bg-[#E0E7FF] text-slate-900 border-2 border-slate-900 rounded-lg px-2.5 py-1 text-[11px] font-bold shadow-[2px_2px_0_#0F172A]">
                    <i data-lucide="layers" class="w-3.5 h-3.5 text-indigo-700"></i>
                    <span>Mode: <strong class="text-indigo-700 uppercase">{{ $setting->mode ?? 'DAILY' }}</strong></span>
                </div>
            </div>
        </div>

        <!-- Right Column (5 cols): Quick Actions Panel -->
        <div class="lg:col-span-5 bg-slate-950/25 border-2 border-slate-900/40 rounded-xl p-4">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-blue-100 flex items-center gap-1.5">
                    <i data-lucide="zap" class="w-3.5 h-3.5 text-[#FFD43B] fill-[#FFD43B]"></i>
                    {{ Auth::user()?->isKepala() ? 'Pintasan Monitoring Kepala' : 'Aksi Cepat Administrator' }}
                </span>
                <span class="text-[10px] font-semibold text-blue-200">6 Pintasan</span>
            </div>

            <div class="grid grid-cols-2 gap-2.5">
                @if(Auth::user()?->isKepala())
                    <a href="{{ route('admin.reports.attendance', ['start_date' => $date, 'end_date' => $date]) }}" class="neo-btn bg-[#FFD43B] hover:bg-[#ffe066] text-slate-950 rounded-lg px-3 py-2 text-xs font-extrabold flex items-center gap-2 text-left cursor-pointer transition-all active:translate-x-0.5 active:translate-y-0.5">
                        <i data-lucide="bar-chart-3" class="w-4 h-4 shrink-0"></i>
                        <span class="truncate">Rekap Laporan</span>
                    </a>
                    <a href="{{ route('admin.teaching-journals.index') }}" class="neo-btn bg-white hover:bg-slate-100 text-slate-900 rounded-lg px-3 py-2 text-xs font-bold flex items-center gap-2 text-left cursor-pointer transition-all active:translate-x-0.5 active:translate-y-0.5">
                        <i data-lucide="book-open" class="w-4 h-4 text-blue-600 shrink-0"></i>
                        <span class="truncate">Jurnal Guru</span>
                    </a>
                    <a href="{{ route('admin.teachers.index') }}" class="neo-btn bg-[#D3F9D8] hover:bg-[#b2f2bb] text-slate-950 rounded-lg px-3 py-2 text-xs font-extrabold flex items-center gap-2 text-left cursor-pointer transition-all active:translate-x-0.5 active:translate-y-0.5">
                        <i data-lucide="users" class="w-4 h-4 shrink-0"></i>
                        <span class="truncate">Guru &amp; Tendik</span>
                    </a>
                    <a href="{{ route('admin.academic-calendar.index') }}" class="neo-btn bg-[#FEF08A] hover:bg-[#ffec99] text-slate-950 rounded-lg px-3 py-2 text-xs font-extrabold flex items-center gap-2 text-left cursor-pointer transition-all active:translate-x-0.5 active:translate-y-0.5">
                        <i data-lucide="calendar-days" class="w-4 h-4 shrink-0"></i>
                        <span class="truncate">Kalender Akademik</span>
                    </a>
                    <a href="{{ route('ujian.dashboard') }}" class="neo-btn bg-[#E5DBFF] hover:bg-[#d0bfff] text-slate-950 rounded-lg px-3 py-2 text-xs font-extrabold flex items-center gap-2 text-left cursor-pointer transition-all active:translate-x-0.5 active:translate-y-0.5">
                        <i data-lucide="file-text" class="w-4 h-4 shrink-0"></i>
                        <span class="truncate">Monitoring Ujian</span>
                    </a>
                    <a href="{{ route('mbg.rekap.index') }}" class="neo-btn bg-[#7DD3FC] hover:bg-[#a5d8ff] text-slate-950 rounded-lg px-3 py-2 text-xs font-extrabold flex items-center gap-2 text-left cursor-pointer transition-all active:translate-x-0.5 active:translate-y-0.5">
                        <i data-lucide="utensils" class="w-4 h-4 shrink-0"></i>
                        <span class="truncate">Rekap MBG</span>
                    </a>
                @else
                    <a href="{{ route('admin.attendances.manual', ['date' => $date]) }}" class="neo-btn bg-[#22C55E] hover:bg-emerald-400 text-slate-950 rounded-lg px-3 py-2 text-xs font-extrabold flex items-center gap-2 text-left cursor-pointer transition-all active:translate-x-0.5 active:translate-y-0.5">
                        <i data-lucide="clipboard-check" class="w-4 h-4 shrink-0"></i>
                        <span class="truncate">Presensi Manual</span>
                    </a>
                    <a href="{{ route('admin.reports.attendance', ['start_date' => $date, 'end_date' => $date]) }}" class="neo-btn bg-[#FFD43B] hover:bg-[#ffe066] text-slate-950 rounded-lg px-3 py-2 text-xs font-extrabold flex items-center gap-2 text-left cursor-pointer transition-all active:translate-x-0.5 active:translate-y-0.5">
                        <i data-lucide="bar-chart-3" class="w-4 h-4 shrink-0"></i>
                        <span class="truncate">Rekap Laporan</span>
                    </a>
                    <a href="{{ route('admin.teaching-journals.index') }}" class="neo-btn bg-white hover:bg-slate-100 text-slate-900 rounded-lg px-3 py-2 text-xs font-bold flex items-center gap-2 text-left cursor-pointer transition-all active:translate-x-0.5 active:translate-y-0.5">
                        <i data-lucide="book-open" class="w-4 h-4 text-blue-600 shrink-0"></i>
                        <span class="truncate">Jurnal Guru</span>
                    </a>
                    <a href="{{ route('admin.savings.index') }}" class="neo-btn bg-[#7DD3FC] hover:bg-[#a5d8ff] text-slate-950 rounded-lg px-3 py-2 text-xs font-extrabold flex items-center gap-2 text-left cursor-pointer transition-all active:translate-x-0.5 active:translate-y-0.5">
                        <i data-lucide="wallet" class="w-4 h-4 shrink-0"></i>
                        <span class="truncate">Kas Tabungan</span>
                    </a>
                    <a href="{{ route('admin.academic-calendar.index') }}" class="neo-btn bg-[#FEF08A] hover:bg-[#ffec99] text-slate-950 rounded-lg px-3 py-2 text-xs font-extrabold flex items-center gap-2 text-left cursor-pointer transition-all active:translate-x-0.5 active:translate-y-0.5">
                        <i data-lucide="calendar-days" class="w-4 h-4 shrink-0"></i>
                        <span class="truncate">Kalender Akademik</span>
                    </a>
                    <a href="{{ route('admin.settings.index') }}" class="neo-btn bg-slate-100 hover:bg-slate-200 text-slate-900 rounded-lg px-3 py-2 text-xs font-bold flex items-center gap-2 text-left cursor-pointer transition-all active:translate-x-0.5 active:translate-y-0.5">
                        <i data-lucide="settings" class="w-4 h-4 text-slate-600 shrink-0"></i>
                        <span class="truncate">Pengaturan</span>
                    </a>
                @endif
            </div>
        </div>
    </div>
</section>
