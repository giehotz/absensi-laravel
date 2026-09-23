<!-- Empty State: Guru Bukan Wali Kelas -->
<div class="bg-white neo-box p-12 text-center space-y-4 max-w-2xl mx-auto">
    <div class="text-5xl">🛡️</div>
    <div class="space-y-1">
        <h3 class="font-heading font-black text-xl text-black">Anda Belum Ditugaskan Sebagai Wali Kelas</h3>
        <p class="text-xs sm:text-sm font-medium text-slate-600">
            Akun Anda saat ini belum tercatat sebagai wali kelas untuk rombongan belajar manapun pada tahun ajaran aktif.
        </p>
    </div>
    <div class="p-4 bg-slate-50 border-2 border-black rounded-sm text-xs font-semibold text-slate-700 max-w-lg mx-auto text-left space-y-1">
        <div class="font-bold text-black flex items-center gap-1.5">
            <span>💡</span> Informasi:
        </div>
        <div>• Penugasan wali kelas dilakukan oleh Administrator Sekolah pada menu Data Master Kelas.</div>
        <div>• Jika Anda adalah wali kelas, silakan hubungi bagian tata usaha / admin sistem sekolah.</div>
    </div>
    <div class="pt-2">
        <a href="{{ route('guru.attendance.manual') }}" class="neo-btn bg-[#5294FF] text-white text-xs font-bold px-5 py-2.5 inline-flex items-center gap-2">
            <span>📋</span> Masuk ke Presensi Manual Pengajar
        </a>
    </div>
</div>
