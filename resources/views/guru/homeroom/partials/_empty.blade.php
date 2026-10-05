<!-- Empty State: Guru Bukan Wali Kelas -->
<div class="bg-white neo-box p-8 sm:p-12 text-center space-y-5 max-w-2xl mx-auto">
    <div class="text-5xl sm:text-6xl">🛡️</div>
    <div class="space-y-1.5">
        <h3 class="font-heading font-black text-xl sm:text-2xl text-black">Anda Belum Ditugaskan Sebagai Wali Kelas</h3>
        <p class="text-xs sm:text-sm font-medium text-slate-600 leading-relaxed">
            Akun Anda saat ini belum tercatat sebagai wali kelas untuk rombongan belajar manapun pada tahun ajaran aktif.
        </p>
    </div>
    <div class="p-4 bg-slate-50 border-2 border-black rounded-xs text-xs font-semibold text-slate-700 max-w-lg mx-auto text-left space-y-1.5">
        <div class="font-black text-black flex items-center gap-1.5 uppercase text-[11px]">
            <span>💡</span> Informasi Penting:
        </div>
        <div>• Penugasan wali kelas dikelola langsung oleh Administrator pada menu Manajemen Kelas.</div>
        <div>• Jika Anda merasa seharusnya ditugaskan sebagai wali kelas, silakan berkoordinasi dengan admin madrasah / kurikulum.</div>
    </div>
    <div class="pt-2">
        <a href="{{ route('guru.attendance.manual') }}" class="neo-btn bg-[#339AF0] hover:bg-blue-600 text-white text-xs font-bold px-5 py-2.5 min-h-[44px] inline-flex items-center gap-2 cursor-pointer transition-colors">
            <span>📋</span> Buka Presensi Manual Pengajar →
        </a>
    </div>
</div>
