<!-- SECTION 3: DATE FILTER & MONITORING CONTEXT BAR -->
<section class="neo-card bg-white rounded-xl px-4 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <!-- Left: Context Label -->
    <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-blue-100 border-2 border-slate-900 flex items-center justify-center shrink-0">
            <i data-lucide="calendar-search" class="w-4 h-4 text-blue-700"></i>
        </div>
        <div>
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                <p class="text-xs font-extrabold uppercase tracking-wider text-slate-800">
                    Data Presensi Tanggal Terpilih
                </p>
            </div>
            <p class="text-[11px] text-slate-500 font-medium">
                Menampilkan ringkasan kehadiran seluruh rombel secara real-time.
            </p>
        </div>
    </div>

    <!-- Right: Date Stepper Control -->
    <div class="flex items-center gap-2 self-end sm:self-auto flex-wrap">
        <!-- Hari Sebelumnya -->
        <a href="{{ route('admin.dashboard', ['date' => $prevDate]) }}" 
           title="Hari Sebelumnya: {{ \Carbon\Carbon::parse($prevDate)->translatedFormat('d M Y') }}"
           class="neo-btn bg-white hover:bg-slate-50 text-slate-700 rounded-lg px-3 py-2 text-xs font-bold flex items-center gap-1 cursor-pointer">
            <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
            <span>{{ \Carbon\Carbon::parse($prevDate)->translatedFormat('d M') }}</span>
        </a>

        <!-- Tanggal Aktif / Trigger Datepicker -->
        <div onclick="document.getElementById('dashboardDatePicker').showPicker ? document.getElementById('dashboardDatePicker').showPicker() : document.getElementById('dashboardDatePicker').focus()"
             class="neo-btn bg-[#FEF9C3] hover:bg-[#FEF08A] rounded-lg px-4 py-1.5 flex items-center gap-2.5 text-left cursor-pointer select-none">
            <i data-lucide="calendar" class="w-4 h-4 text-amber-700 shrink-0"></i>
            <div class="leading-tight">
                <span class="block text-[9px] font-extrabold uppercase tracking-wider text-amber-800">
                    {{ $carbonDate->isToday() ? 'HARI INI' : ($carbonDate->isYesterday() ? 'KEMARIN' : ($carbonDate->isTomorrow() ? 'BESOK' : 'TANGGAL')) }}
                </span>
                <span class="text-xs font-extrabold text-slate-900">
                    {{ $carbonDate->translatedFormat('l, d F Y') }}
                </span>
            </div>
        </div>

        <!-- Hidden Native Datepicker Form -->
        <form id="dashboardDateForm" action="{{ route('admin.dashboard') }}" method="GET" class="inline">
            <input type="date" 
                   id="dashboardDatePicker" 
                   name="date" 
                   value="{{ $date }}" 
                   onchange="this.form.submit()" 
                   class="sr-only">
        </form>

        <!-- Hari Berikutnya -->
        <a href="{{ route('admin.dashboard', ['date' => $nextDate]) }}" 
           title="Hari Berikutnya: {{ \Carbon\Carbon::parse($nextDate)->translatedFormat('d M Y') }}"
           class="neo-btn bg-white hover:bg-slate-50 text-slate-700 rounded-lg px-3 py-2 text-xs font-bold flex items-center gap-1 cursor-pointer">
            <span>{{ \Carbon\Carbon::parse($nextDate)->translatedFormat('d M') }}</span>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
        </a>

        @if(!$carbonDate->isToday())
            <a href="{{ route('admin.dashboard') }}" 
               class="neo-btn bg-[#22C55E] hover:bg-emerald-400 text-slate-950 rounded-lg px-2.5 py-2 text-xs font-extrabold flex items-center gap-1 cursor-pointer">
                <i data-lucide="zap" class="w-3 h-3"></i>
                <span>Hari Ini</span>
            </a>
        @endif
    </div>
</section>

<!-- Alert Banner Hari Libur Nasional / Cuti Bersama (Jika Aktif) -->
@if(isset($holiday) && $holiday && $holiday->is_active)
    <div class="neo-card bg-[#FFF4E6] rounded-xl p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-2 border-slate-900 shadow-[4px_4px_0_#0F172A]">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-lg bg-rose-100 border-2 border-slate-900 flex items-center justify-center shrink-0">
                <i data-lucide="calendar-off" class="w-5 h-5 text-rose-600"></i>
            </div>
            <div class="space-y-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1 bg-[#FF6B6B] text-white border-2 border-slate-900 rounded-md px-2.5 py-0.5 text-xs font-extrabold uppercase shadow-[2px_2px_0_#0F172A]">
                        HARI LIBUR: {{ strtoupper($holiday->name) }}
                    </span>
                    <span class="text-xs font-semibold text-amber-900 bg-white/90 border border-slate-900/30 rounded px-2 py-0.5">
                        {{ $holiday->is_cuti_bersama ? '📌 Cuti Bersama Resmi' : ($holiday->is_national ? '🇮🇩 Libur Nasional SKB 3 Menteri' : '🏫 Libur Internal Sekolah') }}
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-amber-950 font-medium">
                    Tanggal <strong>{{ $carbonDate->translatedFormat('l, d F Y') }}</strong> merupakan agenda hari libur. Presensi harian reguler tidak diwajibkan.
                </p>
            </div>
        </div>
    </div>
@endif
