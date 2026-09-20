<!-- Tab 1: Aturan Presensi -->
<div id="tab-absensi" class="tab-panel">
    <div class="bg-white neo-box-lg p-6 sm:p-8 space-y-6">
        <div class="border-b-2 border-black pb-3">
            <span class="neo-badge bg-[#5294FF] text-white">KONFIGURASI ABSENSI</span>
            <h2 class="font-heading font-black text-xl text-black mt-2">
                Metode & Parameter Presensi
            </h2>
            <p class="text-xs text-slate-600 font-semibold mt-0.5">
                Tentukan bagaimana absensi diproses oleh scanner QR dan batas waktu toleransi terlambat.
            </p>
        </div>

        <!-- Mode Presensi -->
        <div>
            <label class="block font-heading font-bold text-sm text-black mb-2">Mode Absensi Sekolah *</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Daily Mode -->
                <label class="neo-box p-4 cursor-pointer flex items-start gap-3 transition-all hover:bg-slate-50 relative {{ $setting->mode === 'daily' ? 'bg-[#FFF9DB]' : 'bg-white' }}">
                    <input type="radio" name="mode" value="daily" class="mt-1 w-4 h-4 text-[#5294FF] border-2 border-black" {{ $setting->mode === 'daily' ? 'checked' : '' }}>
                    <div>
                        <div class="font-heading font-black text-sm text-black flex items-center gap-1.5">
                            <span>🌅</span> Mode Harian (Daily)
                        </div>
                        <p class="text-xs text-slate-600 font-medium mt-1">
                            Siswa absen 1 kali per hari (Jam Masuk dan Jam Pulang). Cocok untuk jenjang SD atau MI.
                        </p>
                    </div>
                </label>

                <!-- Per Lesson Mode -->
                <label class="neo-box p-4 cursor-pointer flex items-start gap-3 transition-all hover:bg-slate-50 relative {{ $setting->mode === 'per_lesson' ? 'bg-[#FFF9DB]' : 'bg-white' }}">
                    <input type="radio" name="mode" value="per_lesson" class="mt-1 w-4 h-4 text-[#5294FF] border-2 border-black" {{ $setting->mode === 'per_lesson' ? 'checked' : '' }}>
                    <div>
                        <div class="font-heading font-black text-sm text-black flex items-center gap-1.5">
                            <span>⏱️</span> Mode Per Mata Pelajaran
                        </div>
                        <p class="text-xs text-slate-600 font-medium mt-1">
                            Kehadiran dicatat setiap jam mata pelajaran oleh guru mapel. Cocok untuk SMP/SMA/SMK.
                        </p>
                    </div>
                </label>
            </div>
        </div>

        <!-- Toleransi Keterlambatan & Tahun Ajaran -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-2">
            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1.5">
                    Batas Toleransi Keterlambatan (Menit) *
                </label>
                <div class="relative">
                    <input
                        type="number"
                        name="tolerance_minutes"
                        value="{{ old('tolerance_minutes', $setting->tolerance_minutes) }}"
                        min="0"
                        max="120"
                        required
                        class="w-full px-3.5 py-2.5 neo-input bg-slate-50 font-bold text-sm text-black"
                    >
                </div>
                <span class="text-[11px] text-slate-500 font-semibold mt-1 block">
                    Siswa yang melakukan scan setelah jam masuk + toleransi ini otomatis berstatus <strong>Terlambat</strong>.
                </span>
            </div>

            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1.5">
                    Tahun Ajaran Aktif Saat Ini *
                </label>
                <select name="active_academic_year_id" class="w-full px-3.5 py-2.5 neo-input bg-slate-50 font-bold text-sm text-black">
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" {{ $year->is_active ? 'selected' : '' }}>
                            {{ $year->name }} - Semester {{ ucfirst($year->semester) }} {{ $year->is_active ? '(Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
                <span class="text-[11px] text-slate-500 font-semibold mt-1 block">
                    Menentukan periode semester yang sedang berlangsung pada jadwal.
                </span>
            </div>
        </div>

        <!-- Submit Absensi -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t-2 border-slate-200">
            <button type="submit" class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black px-6 py-3 text-sm uppercase tracking-wider font-heading cursor-pointer">
                💾 Simpan Konfigurasi Absensi
            </button>
        </div>
    </div>
</div>
