<!-- Tab Navigasi Data Siswa & Pengecekan Duplikat NIS -->
<div class="border-b-2 border-black flex flex-wrap items-center gap-2">
    <a href="{{ route('admin.students.index') }}"
       class="px-5 py-3 text-xs font-heading font-black uppercase tracking-wider flex items-center gap-2 border-2 border-b-0 border-black transition-all
       {{ request()->routeIs('admin.students.index') 
            ? 'bg-[#FFD43B] text-black shadow-[3px_-2px_0px_0px_#000] -mb-[2px] z-10' 
            : 'bg-white text-slate-600 hover:bg-slate-100 hover:text-black' }}">
        <span>🎓</span>
        <span>Data Siswa & Kartu QR</span>
        @php $totalStudentCount = \App\Models\Student::count(); @endphp
        <span class="text-[10px] px-2 py-0.5 rounded-full border border-black {{ request()->routeIs('admin.students.index') ? 'bg-black text-white' : 'bg-slate-200 text-slate-800' }}">
            {{ $totalStudentCount }} Siswa
        </span>
    </a>

    <a href="{{ route('admin.students.cek-duplikat.index') }}"
       class="px-5 py-3 text-xs font-heading font-black uppercase tracking-wider flex items-center gap-2 border-2 border-b-0 border-black transition-all
       {{ request()->routeIs('admin.students.cek-duplikat.*') 
            ? 'bg-[#FFD43B] text-black shadow-[3px_-2px_0px_0px_#000] -mb-[2px] z-10' 
            : 'bg-white text-slate-600 hover:bg-slate-100 hover:text-black' }}">
        <span>⚠️</span>
        <span>Pengecekan Duplikat NIS</span>
        @php $pendingCount = \App\Models\StudentDuplicateConflict::where('status', 'pending')->count(); @endphp
        @if($pendingCount > 0)
            <span class="text-[10px] px-2 py-0.5 rounded-full border border-black bg-[#FF6B6B] text-white font-black animate-pulse shadow-[1px_1px_0px_#000]">
                {{ $pendingCount }} Kasus
            </span>
        @else
            <span class="text-[10px] px-2 py-0.5 rounded-full border border-black {{ request()->routeIs('admin.students.cek-duplikat.*') ? 'bg-black text-white' : 'bg-slate-200 text-slate-800' }}">
                0
            </span>
        @endif
    </a>
</div>
