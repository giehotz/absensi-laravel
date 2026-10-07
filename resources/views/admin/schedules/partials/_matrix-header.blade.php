<!-- Header Title Card: Kelas Terpilih -->
<div class="bg-[#FFF3BF] neo-box-lg p-6 sm:p-7 text-black relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
    <div class="space-y-1.5 z-10">
        <div class="flex items-center gap-2 flex-wrap">
            <span class="neo-badge bg-[#5294FF] text-white">ADMINISTRATOR</span>
            <span class="text-xs font-mono font-bold bg-white px-2 py-0.5 border border-black">
                {{ $academicYear->name ?? 'Tahun Ajaran Aktif' }}
            </span>
            <span class="text-xs font-mono font-bold bg-white px-2 py-0.5 border border-black hidden sm:inline-block">
                Semester {{ ucfirst($academicYear->semester ?? 'Ganjil') }}
            </span>
            <span class="text-xs font-heading font-black bg-[#CC5DE8] text-white px-2.5 py-0.5 border border-black">
                Kelas {{ $selectedClass->name }}
            </span>
        </div>
        <h1 class="font-heading text-2xl sm:text-3xl font-black tracking-tight text-black uppercase">
            PLOT JADWAL KELAS MINGGUAN: KELAS {{ $selectedClass->name }}
        </h1>
        <p class="text-xs sm:text-sm font-semibold text-slate-800 max-w-2xl">
            Plotting jadwal tatap muka kelas berbasis matriks mingguan EMIS GTK. Dilengkapi deteksi bentrok jadwal guru dan dukungan multi-jam pelajaran.
        </p>
    </div>

    <div class="z-10 flex flex-wrap gap-2 shrink-0">
        <a href="{{ route('admin.schedules.index') }}" class="neo-btn bg-white hover:bg-slate-100 text-black text-xs font-black px-3.5 py-2.5 flex items-center gap-1.5 cursor-pointer shadow-[2px_2px_0px_0px_#000]">
            <span>←</span> Kembali ke Daftar Kelas
        </a>
        <button type="button" onclick="window.print()" class="neo-btn bg-white hover:bg-slate-100 text-black text-xs font-bold px-3.5 py-2.5 flex items-center gap-1.5 cursor-pointer shadow-[2px_2px_0px_0px_#000]">
            <span>🖨️</span> Cetak Jadwal
        </button>
        <a href="{{ route('admin.schedules.slots.index') }}" class="neo-btn bg-white hover:bg-slate-100 text-black text-xs font-bold px-3.5 py-2.5 flex items-center gap-1.5 cursor-pointer shadow-[2px_2px_0px_0px_#000]">
            <span>⚙️</span> Atur Template Slot
        </a>
        <button type="button" onclick="openCreateScheduleModal()" class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black text-xs font-black px-4 py-2.5 flex items-center gap-1.5 cursor-pointer shadow-[2px_2px_0px_0px_#000]">
            <span>➕</span> Tambah Jadwal Baru
        </button>
    </div>
</div>
