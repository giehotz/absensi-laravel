<!-- Tab 4: Pemeliharaan & Arsip Database Presensi -->
<div id="tab-database" class="tab-panel hidden">
    <div class="bg-white neo-box-lg p-6 sm:p-8 space-y-6">
        <div class="border-b-2 border-black pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <span class="neo-badge bg-[#845EF7] text-white">PEMELIHARAAN DATABASE</span>
                <h2 class="font-heading font-black text-xl text-black mt-2">
                    Kapasitas & Pengarsipan Data Presensi
                </h2>
                <p class="text-xs text-slate-600 font-semibold mt-0.5">
                    Pindahkan data presensi dari Tahun Ajaran non-aktif ke tabel arsip agar database tetap cepat dan responsif.
                </p>
            </div>

            <!-- Tombol Defragmentasi / Optimize Table -->
            <form action="{{ route('admin.database.optimize') }}" method="POST"
                  class="form-optimize-database">
                @csrf
                <button type="submit"
                        class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-4 py-2 text-xs font-bold flex items-center gap-2 cursor-pointer shadow-[3px_3px_0px_0px_#000]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <span>⚡ Optimasi Database</span>
                </button>
            </form>
        </div>

        <!-- Realtime Database Metrics -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Data Aktif -->
            <div class="neo-box p-4 bg-[#F4F6FB] flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Tabel Aktif (attendances)</span>
                    <span class="w-2.5 h-2.5 rounded-full bg-[#20C997]"></span>
                </div>
                <div class="mt-2 font-heading font-black text-2xl text-black">
                    {{ number_format($activeAttendanceCount) }} <span class="text-xs font-normal text-slate-500">baris</span>
                </div>
                <div class="mt-1 flex items-center gap-1.5 text-[11px] font-semibold text-slate-600">
                    <span class="neo-badge bg-[#20C997] text-white text-[9px] px-1 py-0">LIVE</span>
                    <span>Digunakan scanner QR aktif</span>
                </div>
            </div>

            <!-- Data Terarsip -->
            <div class="neo-box p-4 bg-[#F4F6FB] flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Tabel Arsip (archive)</span>
                    <span class="w-2.5 h-2.5 rounded-full bg-[#5294FF]"></span>
                </div>
                <div class="mt-2 font-heading font-black text-2xl text-black">
                    {{ number_format($archivedAttendanceCount) }} <span class="text-xs font-normal text-slate-500">baris</span>
                </div>
                <div class="mt-1 flex items-center gap-1.5 text-[11px] font-semibold text-slate-600">
                    <span class="neo-badge bg-[#5294FF] text-white text-[9px] px-1 py-0">COLD</span>
                    <span>Tersimpan aman & dapat dipulihkan</span>
                </div>
            </div>

            <!-- Status Efisiensi -->
            <div class="neo-box p-4 bg-black text-white flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-300 uppercase tracking-wider">Status Performa</span>
                    <span class="text-xs font-mono font-bold text-[#FFD43B]">MySQL</span>
                </div>
                <div class="mt-2 font-heading font-black text-xl text-[#20C997]">
                    {{ $activeAttendanceCount < 200000 ? 'Sangat Prima (Fast)' : 'Perlu Diarsip' }}
                </div>
                <div class="mt-1 text-[11px] text-slate-400">
                    Indeks B-Tree Terpantau
                </div>
            </div>
        </div>

        <!-- Educational Callout Box: Petunjuk Detail Database -->
        <div class="bg-[#E7F5FF] border-2 border-black p-5 rounded-lg shadow-[3px_3px_0px_0px_#000] space-y-3">
            <div class="flex items-center gap-2 font-heading font-black text-sm text-blue-950">
                <span class="text-lg">ℹ️</span>
                <span>PETUNJUK SISTEM: Mengapa & Bagaimana Pengarsipan Bekerja pada Database?</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs text-blue-950 font-medium">
                <div class="bg-white/90 p-3 rounded border border-blue-300">
                    <div class="font-bold text-black mb-1">1. Mengapa Perlu Diarsip?</div>
                    Setiap 500 siswa dengan 26 hari belajar menghasilkan <strong>~13.000 data/bulan</strong> atau <strong>~156.000 data/tahun</strong>. Pengarsipan menjaga tabel utama tetap kecil sehingga proses scan kartu QR dan perhitungan rekap tetap secepat kilat (<50ms).
                </div>
                <div class="bg-white/90 p-3 rounded border border-blue-300">
                    <div class="font-bold text-black mb-1">2. Apakah Data Aman?</div>
                    <strong>100% AMAN.</strong> Sistem TIDAK menghapus data Anda secara permanen. Data hanya dipindahkan ke tabel <code>attendances_archive</code> dan dapat <strong>dipulihkan (restore)</strong> kapan saja jika diperlukan untuk keperluan akreditasi atau audit.
                </div>
                <div class="bg-white/90 p-3 rounded border border-blue-300">
                    <div class="font-bold text-black mb-1">3. Kapan Harus Dilakukan?</div>
                    Lakukan pengarsipan ketika <strong>Tahun Ajaran telah berakhir</strong> dan seluruh nilai rapor atau laporan kehadiran telah diserahkan, sebelum mengaktifkan Tahun Ajaran yang baru.
                </div>
            </div>
        </div>

        <!-- Tabel Kelola Arsip per Tahun Ajaran -->
        <div class="space-y-3 pt-2">
            <h3 class="font-heading font-bold text-sm text-black">
                Daftar Periode & Status Pengarsipan
            </h3>

            <div class="overflow-x-auto neo-box">
                <table class="w-full text-left text-xs text-slate-800">
                    <thead class="bg-slate-100 border-b-2 border-black font-bold uppercase text-[11px] text-black">
                        <tr>
                            <th class="p-3">Tahun Ajaran</th>
                            <th class="p-3">Semester</th>
                            <th class="p-3 text-center">Status Periode</th>
                            <th class="p-3 text-center">Data Presensi Aktif</th>
                            <th class="p-3 text-center">Data Terarsip</th>
                            <th class="p-3 text-center">Aksi Pemeliharaan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-slate-100 font-medium">
                        @forelse($academicYears as $year)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-3 font-heading font-black text-black">
                                {{ $year->name }}
                            </td>
                            <td class="p-3 uppercase font-bold text-slate-600">
                                {{ $year->semester }}
                            </td>
                            <td class="p-3 text-center">
                                @if($year->is_active)
                                    <span class="neo-badge bg-[#20C997] text-white">AKTIF SEKARANG</span>
                                @else
                                    <span class="neo-badge bg-slate-200 text-slate-700">NON-AKTIF</span>
                                @endif
                            </td>
                            <td class="p-3 text-center font-mono font-bold">
                                @if($year->active_records_count > 0)
                                    <span class="text-emerald-700 bg-[#D3F9D8] px-2 py-0.5 rounded border border-black">
                                        {{ number_format($year->active_records_count) }} baris
                                    </span>
                                @else
                                    <span class="text-slate-400">0 baris</span>
                                @endif
                            </td>
                            <td class="p-3 text-center font-mono font-bold">
                                @if($year->archived_records_count > 0)
                                    <span class="text-blue-700 bg-[#D0EBFF] px-2 py-0.5 rounded border border-black">
                                        {{ number_format($year->archived_records_count) }} baris
                                    </span>
                                @else
                                    <span class="text-slate-400">0 baris</span>
                                @endif
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    @if($year->is_active)
                                        <span class="text-[11px] font-bold text-slate-400 italic">
                                            Terkunci (Periode Aktif)
                                        </span>
                                    @else
                                        @if($year->active_records_count > 0)
                                            <button type="button"
                                                    onclick="openArchiveModal({{ $year->id }})"
                                                    class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-2.5 py-1 text-[11px] font-bold flex items-center gap-1 cursor-pointer">
                                                <span>📦</span>
                                                <span>Arsipkan</span>
                                            </button>
                                        @endif

                                        @if($year->archived_records_count > 0)
                                            <button type="button"
                                                    onclick="openRestoreModal({{ $year->id }}, '{{ $year->name }}', '{{ $year->semester }}', {{ $year->archived_records_count }})"
                                                    class="neo-btn bg-white hover:bg-slate-100 text-black px-2.5 py-1 text-[11px] font-bold flex items-center gap-1 cursor-pointer border-2 border-black">
                                                <span>🔄</span>
                                                <span>Pulihkan</span>
                                            </button>
                                        @endif

                                        @if($year->active_records_count == 0 && $year->archived_records_count == 0)
                                            <span class="text-slate-400 text-[11px]">Tidak ada data</span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-slate-500 font-bold">
                                Belum ada data tahun ajaran.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
