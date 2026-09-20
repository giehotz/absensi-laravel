<!-- Modal Tambah Tahun Ajaran -->
<div id="createAcademicYearModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-md w-full p-6 space-y-5 relative">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <h3 class="font-heading font-black text-lg text-black flex items-center gap-2">
                <span>➕</span> Tambah Tahun Ajaran Baru
            </h3>
            <button onclick="closeModal('createAcademicYearModal')" class="text-black font-black text-xl hover:opacity-75">✕</button>
        </div>

        <form action="{{ route('admin.academic-years.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Nama Tahun Ajaran *</label>
                <input type="text" name="name" required placeholder="Contoh: 2026/2027 atau 2027/2028" class="w-full px-3.5 py-2 neo-input text-sm bg-slate-50 font-bold">
            </div>

            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Semester *</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="neo-box p-3 cursor-pointer flex items-center gap-2 bg-white hover:bg-slate-50">
                        <input type="radio" name="semester" value="ganjil" checked class="w-4 h-4 text-[#5294FF]">
                        <span class="font-heading font-bold text-xs">Ganjil</span>
                    </label>
                    <label class="neo-box p-3 cursor-pointer flex items-center gap-2 bg-white hover:bg-slate-50">
                        <input type="radio" name="semester" value="genap" class="w-4 h-4 text-[#5294FF]">
                        <span class="font-heading font-bold text-xs">Genap</span>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">Tanggal Mulai *</label>
                    <input type="date" name="start_date" required class="w-full px-3 py-2 neo-input text-xs bg-slate-50 font-medium">
                </div>
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">Tanggal Selesai *</label>
                    <input type="date" name="end_date" required class="w-full px-3 py-2 neo-input text-xs bg-slate-50 font-medium">
                </div>
            </div>

            <div class="pt-2">
                <label class="neo-box p-3 cursor-pointer flex items-center gap-3 bg-[#FFF9DB]">
                    <input type="checkbox" name="is_active" value="1" class="w-4 h-4 text-[#20C997]">
                    <span class="font-heading font-bold text-xs text-black">
                        Langsung jadikan periode ini sebagai <strong>Tahun Ajaran Aktif</strong>
                    </span>
                </label>
            </div>

            <div class="pt-3 border-t-2 border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('createAcademicYearModal')" class="neo-btn bg-white text-black px-4 py-2 text-xs">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#20C997] text-black px-5 py-2 text-xs font-heading">
                    Simpan Periode
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Tahun Ajaran -->
<div id="editAcademicYearModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-md w-full p-6 space-y-5 relative">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <h3 class="font-heading font-black text-lg text-black flex items-center gap-2">
                <span>✏️</span> Edit Data Tahun Ajaran
            </h3>
            <button onclick="closeModal('editAcademicYearModal')" class="text-black font-black text-xl hover:opacity-75">✕</button>
        </div>

        <form id="editAcademicYearForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Nama Tahun Ajaran *</label>
                <input type="text" id="edit_ay_name" name="name" required class="w-full px-3.5 py-2 neo-input text-sm bg-slate-50 font-bold">
            </div>

            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Semester *</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="neo-box p-3 cursor-pointer flex items-center gap-2 bg-white hover:bg-slate-50">
                        <input type="radio" id="edit_ay_semester_ganjil" name="semester" value="ganjil" class="w-4 h-4 text-[#5294FF]">
                        <span class="font-heading font-bold text-xs">Ganjil</span>
                    </label>
                    <label class="neo-box p-3 cursor-pointer flex items-center gap-2 bg-white hover:bg-slate-50">
                        <input type="radio" id="edit_ay_semester_genap" name="semester" value="genap" class="w-4 h-4 text-[#5294FF]">
                        <span class="font-heading font-bold text-xs">Genap</span>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">Tanggal Mulai *</label>
                    <input type="date" id="edit_ay_start_date" name="start_date" required class="w-full px-3 py-2 neo-input text-xs bg-slate-50 font-medium">
                </div>
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">Tanggal Selesai *</label>
                    <input type="date" id="edit_ay_end_date" name="end_date" required class="w-full px-3 py-2 neo-input text-xs bg-slate-50 font-medium">
                </div>
            </div>

            <div class="pt-2">
                <label class="neo-box p-3 cursor-pointer flex items-center gap-3 bg-[#FFF9DB]">
                    <input type="checkbox" id="edit_ay_is_active" name="is_active" value="1" class="w-4 h-4 text-[#20C997]">
                    <span class="font-heading font-bold text-xs text-black">
                        Jadikan periode ini sebagai <strong>Tahun Ajaran Aktif</strong>
                    </span>
                </label>
            </div>

            <div class="pt-3 border-t-2 border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('editAcademicYearModal')" class="neo-btn bg-white text-black px-4 py-2 text-xs">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#5294FF] text-white px-5 py-2 text-xs font-heading">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edukatif 3-Langkah: Pengarsipan Database -->
<div id="archiveModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-lg w-full p-6 space-y-5 relative">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <div class="flex items-center gap-2">
                <span class="text-2xl">📦</span>
                <div>
                    <h3 class="font-heading font-black text-lg text-black">
                        Pengarsipan Data Presensi
                    </h3>
                    <div class="text-[11px] font-bold text-slate-500" id="archiveModalSubtitle">
                        Tahun Ajaran ...
                    </div>
                </div>
            </div>
            <button onclick="closeModal('archiveModal')" class="text-black font-black text-xl hover:opacity-75">✕</button>
        </div>

        <form id="archiveForm" action="{{ route('admin.database.archive') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="academic_year_id" id="archive_academic_year_id">

            <!-- Langkah 1: Pratinjau Data -->
            <div class="bg-[#FFF9DB] border-2 border-black p-3.5 rounded">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-950 uppercase tracking-wider mb-2">
                    <span class="w-5 h-5 rounded-full bg-black text-white flex items-center justify-center text-[10px]">1</span>
                    <span>Pratinjau Data yang Dipindahkan</span>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div>
                        <span class="text-slate-500 block text-[10px] font-bold uppercase">Rentang Tanggal</span>
                        <span class="font-bold text-black" id="archivePreviewDates">-</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[10px] font-bold uppercase">Jumlah Baris Presensi</span>
                        <span class="font-heading font-black text-emerald-700 text-sm" id="archivePreviewCount">- baris</span>
                    </div>
                </div>
            </div>

            <!-- Langkah 2: Rincian Teknis di Database (Transparan & Detail) -->
            <div class="border-2 border-black p-3.5 rounded bg-slate-50 space-y-2">
                <div class="flex items-center gap-1.5 text-xs font-black text-black uppercase tracking-wider mb-1">
                    <span class="w-5 h-5 rounded-full bg-black text-white flex items-center justify-center text-[10px]">2</span>
                    <span>Rincian Tindakan Sistem pada Database</span>
                </div>
                <ul class="text-xs text-slate-700 space-y-1.5 pl-1">
                    <li class="flex items-start gap-2">
                        <span class="text-emerald-600 font-bold">✓</span>
                        <span>Seluruh <strong id="archivePreviewCountText">-</strong> catatan absensi disalin ke tabel arsip aman <code>attendances_archive</code>.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-emerald-600 font-bold">✓</span>
                        <span>Catatan dihapus dari tabel aktif <code>attendances</code> agar tabel utama menjadi ramping kembali.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-emerald-600 font-bold">✓</span>
                        <span>Data <strong>tidak hilang</strong> dan bisa Anda pulihkan (restore) sewaktu-waktu dengan 1 klik.</span>
                    </li>
                </ul>
            </div>

            <!-- Langkah 3: Konfirmasi Eksekusi -->
            <div class="pt-2">
                <div class="flex items-center gap-1.5 text-xs font-black text-black uppercase tracking-wider mb-2">
                    <span class="w-5 h-5 rounded-full bg-black text-white flex items-center justify-center text-[10px]">3</span>
                    <span>Konfirmasi Tindakan</span>
                </div>
                <p class="text-xs text-slate-600 font-medium mb-3">
                    Pastikan seluruh proses pelaporan nilai/rapor pada tahun ajaran ini telah selesai sebelum melakukan pengarsipan.
                </p>

                <div class="flex items-center justify-end gap-3 pt-3 border-t-2 border-slate-200">
                    <button type="button" onclick="closeModal('archiveModal')" class="neo-btn bg-white text-black px-4 py-2 text-xs">
                        Batal
                    </button>
                    <button type="submit" class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-5 py-2 text-xs font-bold">
                        Saya Paham, Jalankan Pengarsipan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Pemulihan (Restore) Data -->
<div id="restoreModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-md w-full p-6 space-y-5 relative">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <div class="flex items-center gap-2">
                <span class="text-2xl">🔄</span>
                <h3 class="font-heading font-black text-lg text-black">
                    Pulihkan Data Presensi
                </h3>
            </div>
            <button onclick="closeModal('restoreModal')" class="text-black font-black text-xl hover:opacity-75">✕</button>
        </div>

        <form id="restoreForm" action="{{ route('admin.database.restore') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="academic_year_id" id="restore_academic_year_id">

            <div class="bg-[#D0EBFF] border-2 border-black p-3.5 rounded text-xs text-blue-950 font-medium">
                Data presensi Tahun Ajaran <strong id="restoreYearName">-</strong> (<strong id="restoreCount">-</strong> catatan) akan disalin kembali dari tabel arsip ke tabel utama <code>attendances</code>.
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t-2 border-slate-200">
                <button type="button" onclick="closeModal('restoreModal')" class="neo-btn bg-white text-black px-4 py-2 text-xs">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#20C997] text-black px-5 py-2 text-xs font-bold">
                    Pulihkan ke Tabel Aktif
                </button>
            </div>
        </form>
    </div>
</div>
