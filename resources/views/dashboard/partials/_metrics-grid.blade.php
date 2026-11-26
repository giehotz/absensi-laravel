<!-- SECTION 4: KPI & INSTITUTIONAL SUMMARY METRICS (DUAL 5-COLUMN GRID) -->
<section class="space-y-4">
    <!-- Row 4A: Primary Attendance KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Card 1: Persentase Hadir -->
        <div class="neo-card bg-[#DCFCE7] rounded-xl p-4 flex flex-col justify-between min-h-[132px]">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-emerald-900">Persentase Hadir</span>
                <span class="w-7 h-7 rounded-md bg-white border-2 border-slate-900 flex items-center justify-center shadow-[1.5px_1.5px_0_#0F172A]">
                    <i data-lucide="trending-up" class="w-4 h-4 text-emerald-700"></i>
                </span>
            </div>
            <div class="my-2">
                <div class="text-3xl font-black text-slate-900 font-mono leading-none">{{ $stats['attendance_rate'] }}%</div>
                <div class="mt-2.5 w-full h-2.5 bg-white border-2 border-slate-900 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-500 transition-all duration-500" style="width: {{ min(100, $stats['attendance_rate']) }}%"></div>
                </div>
            </div>
            <p class="text-[11px] font-bold text-emerald-900/80">{{ $stats['present_today'] }} / {{ $stats['total_students'] }} Siswa</p>
        </div>

        <!-- Card 2: Tepat Waktu -->
        <a href="{{ route('admin.reports.attendance', ['status' => 'hadir', 'start_date' => $date, 'end_date' => $date, 'tab' => 'logs']) }}" 
           class="neo-card bg-[#ECFDF5] hover:bg-[#d1fae5] rounded-xl p-4 flex flex-col justify-between min-h-[132px] transition-transform hover:-translate-y-0.5 cursor-pointer">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-teal-900">Tepat Waktu</span>
                <span class="w-7 h-7 rounded-md bg-emerald-500 border-2 border-slate-900 flex items-center justify-center shadow-[1.5px_1.5px_0_#0F172A]">
                    <i data-lucide="check" class="w-4 h-4 text-white stroke-[3]"></i>
                </span>
            </div>
            <div class="text-3xl font-black text-slate-900 font-mono leading-none my-2">{{ $stats['hadir_today'] }}</div>
            <p class="text-[11px] font-bold text-teal-800">Hadir Tepat Waktu</p>
        </a>

        <!-- Card 3: Terlambat -->
        <a href="{{ route('admin.reports.attendance', ['status' => 'terlambat', 'start_date' => $date, 'end_date' => $date, 'tab' => 'logs']) }}" 
           class="neo-card bg-[#FEF9C3] hover:bg-[#fef08a] rounded-xl p-4 flex flex-col justify-between min-h-[132px] transition-transform hover:-translate-y-0.5 cursor-pointer">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-amber-900">Terlambat</span>
                <span class="w-7 h-7 rounded-md bg-amber-400 border-2 border-slate-900 flex items-center justify-center shadow-[1.5px_1.5px_0_#0F172A]">
                    <i data-lucide="clock-alert" class="w-4 h-4 text-slate-900"></i>
                </span>
            </div>
            <div class="text-3xl font-black text-slate-900 font-mono leading-none my-2">{{ $stats['terlambat_today'] }}</div>
            <p class="text-[11px] font-bold text-amber-900/80">&gt; {{ $setting->tolerance_minutes ?? 15 }} menit toleransi</p>
        </a>

        <!-- Card 4: Izin & Sakit -->
        <a href="{{ route('admin.reports.attendance', ['status' => 'izin_sakit', 'start_date' => $date, 'end_date' => $date, 'tab' => 'logs']) }}" 
           class="neo-card bg-[#E0F2FE] hover:bg-[#bae6fd] rounded-xl p-4 flex flex-col justify-between min-h-[132px] transition-transform hover:-translate-y-0.5 cursor-pointer">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-sky-900">Izin &amp; Sakit</span>
                <span class="w-7 h-7 rounded-md bg-sky-400 border-2 border-slate-900 flex items-center justify-center shadow-[1.5px_1.5px_0_#0F172A]">
                    <i data-lucide="file-text" class="w-4 h-4 text-slate-900"></i>
                </span>
            </div>
            <div class="text-3xl font-black text-slate-900 font-mono leading-none my-2">{{ $stats['izin_today'] + $stats['sakit_today'] }}</div>
            <p class="text-[11px] font-bold text-sky-900/80">{{ $stats['sakit_today'] }} Sakit • {{ $stats['izin_today'] }} Izin</p>
        </a>

        <!-- Card 5: Alpa / Belum -->
        <a href="{{ route('admin.reports.attendance', ['status' => 'alpa', 'start_date' => $date, 'end_date' => $date, 'tab' => 'logs']) }}" 
           class="neo-card bg-[#FFE4E6] hover:bg-[#fecdd3] rounded-xl p-4 flex flex-col justify-between min-h-[132px] transition-transform hover:-translate-y-0.5 cursor-pointer">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-rose-900">Alpa / Belum</span>
                <span class="w-7 h-7 rounded-md bg-rose-500 border-2 border-slate-900 flex items-center justify-center shadow-[1.5px_1.5px_0_#0F172A]">
                    <i data-lucide="x" class="w-4 h-4 text-white stroke-[3]"></i>
                </span>
            </div>
            <div class="text-3xl font-black text-slate-900 font-mono leading-none my-2">{{ $stats['alpa_today'] }}</div>
            <p class="text-[11px] font-bold text-rose-900/80">Belum Terdata Hadir</p>
        </a>
    </div>

    <!-- Row 4B: Institutional Resource Summary Strip -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <!-- Total Siswa -->
        <a href="{{ route('admin.students.index') }}" 
           class="neo-card-sm bg-white hover:bg-slate-50 rounded-xl p-3.5 flex items-center gap-3 cursor-pointer transition-transform active:translate-x-0.5 active:translate-y-0.5">
            <div class="w-10 h-10 rounded-lg bg-blue-500 border-2 border-slate-900 flex items-center justify-center shrink-0 shadow-[1.5px_1.5px_0_#0F172A]">
                <i data-lucide="users" class="w-5 h-5 text-white"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Total Siswa</p>
                <p class="text-lg font-black text-slate-900 font-mono leading-tight">{{ $stats['total_students'] }}</p>
                <p class="text-[10px] font-semibold text-slate-500 truncate">L: {{ $stats['male_students'] }} | P: {{ $stats['female_students'] }}</p>
            </div>
        </a>

        <!-- Guru / Tendik -->
        <a href="{{ route('admin.teachers.index') }}" 
           class="neo-card-sm bg-white hover:bg-slate-50 rounded-xl p-3.5 flex items-center gap-3 cursor-pointer transition-transform active:translate-x-0.5 active:translate-y-0.5">
            <div class="w-10 h-10 rounded-lg bg-[#FFD43B] border-2 border-slate-900 flex items-center justify-center shrink-0 shadow-[1.5px_1.5px_0_#0F172A]">
                <i data-lucide="user-check" class="w-5 h-5 text-slate-900"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Guru / Tendik</p>
                <p class="text-lg font-black text-slate-900 font-mono leading-tight">{{ $stats['total_teachers'] }}</p>
                <p class="text-[10px] font-semibold text-slate-500 truncate">Tenaga Pendidik</p>
            </div>
        </a>

        <!-- Rombel Kelas -->
        <a href="{{ route('admin.classes.index') }}" 
           class="neo-card-sm bg-white hover:bg-slate-50 rounded-xl p-3.5 flex items-center gap-3 cursor-pointer transition-transform active:translate-x-0.5 active:translate-y-0.5">
            <div class="w-10 h-10 rounded-lg bg-emerald-400 border-2 border-slate-900 flex items-center justify-center shrink-0 shadow-[1.5px_1.5px_0_#0F172A]">
                <i data-lucide="school" class="w-5 h-5 text-slate-900"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Rombel Kelas</p>
                <p class="text-lg font-black text-slate-900 font-mono leading-tight">{{ $stats['total_classes'] }}</p>
                <p class="text-[10px] font-semibold text-slate-500 truncate">Kelas Binaan</p>
            </div>
        </a>

        <!-- Izin Pending -->
        <div class="neo-card-sm {{ $stats['pending_leaves'] > 0 ? 'bg-rose-50 border-rose-900' : 'bg-white' }} rounded-xl p-3.5 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg {{ $stats['pending_leaves'] > 0 ? 'bg-rose-500 text-white animate-bounce' : 'bg-rose-100 text-rose-600' }} border-2 border-slate-900 flex items-center justify-center shrink-0 shadow-[1.5px_1.5px_0_#0F172A]">
                <i data-lucide="inbox" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Izin Pending</p>
                <p class="text-lg font-black {{ $stats['pending_leaves'] > 0 ? 'text-rose-600' : 'text-slate-900' }} font-mono leading-tight">{{ $stats['pending_leaves'] }}</p>
                <p class="text-[10px] font-semibold text-slate-500 truncate">Menunggu Peninjauan</p>
            </div>
        </div>

        @if(! Auth::user()?->isKepala() && $stats['total_savings'] !== null)
            <!-- Kas Tabungan -->
            <a href="{{ route('admin.savings.index') }}" 
               class="neo-card-sm bg-[#FEF9C3] hover:bg-[#fef08a] rounded-xl p-3.5 flex items-center gap-3 col-span-2 sm:col-span-1 cursor-pointer transition-transform active:translate-x-0.5 active:translate-y-0.5">
                <div class="w-10 h-10 rounded-lg bg-amber-400 border-2 border-slate-900 flex items-center justify-center shrink-0 shadow-[1.5px_1.5px_0_#0F172A]">
                    <i data-lucide="piggy-bank" class="w-5 h-5 text-slate-900"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] font-extrabold uppercase tracking-wider text-amber-900">Kas Tabungan</p>
                    <p class="text-lg font-black text-slate-900 font-mono leading-tight truncate">
                        Rp {{ number_format($stats['total_savings'], 0, ',', '.') }}
                    </p>
                    <p class="text-[10px] font-semibold text-amber-800 truncate">Saldo Siswa Terhimpun</p>
                </div>
            </a>
        @endif
    </div>
</section>
