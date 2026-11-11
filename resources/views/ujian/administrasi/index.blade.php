@extends(auth()->user()->role === 'admin' ? 'layouts.admin' : 'layouts.ujian')

@section('title', 'Administrasi & Berkas Ruang Ujian')

@section('content')
<div class="space-y-6">
    <!-- Header Page -->
    <div class="p-6 bg-white border-4 border-black rounded-xl shadow-[4px_4px_0px_0px_#000]">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-slate-500 mb-1">
                    <span>Modul Ujian Madrasah</span>
                    <span>•</span>
                    <span class="text-blue-600">Dokumen Administrasi Fisik</span>
                </div>
                <h1 class="text-2xl font-black font-heading text-black flex items-center gap-2.5">
                    <span>📁</span>
                    <span>Administrasi & Berkas Ruang Ujian</span>
                </h1>
                <p class="text-xs text-slate-600 font-semibold mt-1">
                    Pusat cetak terpadu seluruh kelengkapan dokumen fisik ruang ujian madrasah (Presensi Siswa, BAP, Label Meja, Label Pintu, Denah Duduk, Cover Amplop, dan SK).
                </p>
            </div>

            <!-- Periode Selector -->
            <form action="{{ route('ujian.administrasi.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                <label class="text-xs font-bold text-slate-700 whitespace-nowrap">Periode Ujian:</label>
                <select name="periode_id" onchange="this.form.submit()" class="px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-bold">
                    @forelse($periodes as $p)
                        <option value="{{ $p->id }}" {{ $activePeriode && $activePeriode->id === $p->id ? 'selected' : '' }}>
                            {{ $p->nama_periode }} ({{ $p->tahun_pelajaran }}) {{ $p->is_active ? '★ Aktif' : '' }}
                        </option>
                    @empty
                        <option value="">-- Belum ada periode --</option>
                    @endforelse
                </select>
            </form>
        </div>
    </div>

    @if(!$activePeriode)
        <div class="p-6 bg-amber-50 border-4 border-black rounded-xl shadow-[4px_4px_0px_0px_#000] text-center">
            <div class="text-3xl mb-2">⚠️</div>
            <h3 class="text-base font-black text-black">Belum Ada Periode Ujian Aktif</h3>
            <p class="text-xs text-slate-600 mt-1">Silakan aktifkan periode ujian terlebih dahulu pada menu <strong>Jenis & Periode Ujian</strong>.</p>
        </div>
    @else
        <!-- Grid Berkas Administrasi Ruang -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            
            <!-- Dokumen 1: Daftar Hadir Peserta Ujian -->
            <div class="bg-white border-4 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] flex flex-col justify-between hover:translate-y-[-2px] transition-transform">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b-2 border-black mb-3">
                        <span class="px-2.5 py-0.5 rounded text-[10px] font-black uppercase bg-[#20C997] text-black border border-black shadow-[1px_1px_0px_0px_#000]">
                            Wajib Ruang
                        </span>
                        <span class="text-xl">📝</span>
                    </div>
                    <h3 class="text-sm font-black font-heading text-black mb-1">
                        Daftar Hadir Peserta Ujian
                    </h3>
                    <p class="text-[11px] text-slate-600 font-medium mb-3">
                        Lembar presensi siswa per mapel & ruang dengan kolom tanda tangan zig-zag ganjil-genap dan rekap kehadiran.
                    </p>

                    <form action="{{ route('ujian.administrasi.daftar-hadir-peserta') }}" method="GET" target="_blank" class="space-y-2">
                        <input type="hidden" name="periode_id" value="{{ $activePeriode->id }}">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 uppercase mb-0.5">Pilih Jadwal Mapel</label>
                            <select name="jadwal_id" class="w-full px-2.5 py-1.5 border-2 border-black rounded-lg bg-slate-50 text-[11px] font-bold">
                                <option value="">-- Semua Jadwal / Bebas --</option>
                                @foreach($jadwals as $j)
                                    <option value="{{ $j->id }}">
                                        {{ $j->tanggal_ujian->format('d/m') }} • Sesi {{ $j->sesi_ke }} • {{ $j->nama_mapel }} ({{ $j->target_kelas }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 uppercase mb-0.5">Pilih Ruang Ujian</label>
                            <select name="ruang_id" class="w-full px-2.5 py-1.5 border-2 border-black rounded-lg bg-slate-50 text-[11px] font-bold">
                                <option value="all">-- Semua Ruang (Bundle Cetak) --</option>
                                @foreach($ruangs as $r)
                                    <option value="{{ $r->id }}">{{ $r->nama_ruang }} ({{ $r->kapasitas_maksimal }} Kursi)</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="pt-2 flex flex-col gap-1.5">
                            <button type="submit" class="w-full py-2 bg-[#FFD43B] hover:bg-yellow-400 text-black border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center justify-center gap-1.5 cursor-pointer">
                                <span>🖨️</span>
                                <span>Cetak Daftar Hadir</span>
                            </button>
                            <button type="submit" name="blangko" value="1" class="w-full py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 border-2 border-black rounded-lg text-[11px] font-bold shadow-[1px_1px_0px_0px_#000] flex items-center justify-center gap-1 cursor-pointer">
                                <span>📄</span>
                                <span>Cetak Blangko Kosong</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Dokumen 2: Berita Acara Pelaksanaan (BAP) -->
            <div class="bg-white border-4 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] flex flex-col justify-between hover:translate-y-[-2px] transition-transform">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b-2 border-black mb-3">
                        <span class="px-2.5 py-0.5 rounded text-[10px] font-black uppercase bg-[#20C997] text-black border border-black shadow-[1px_1px_0px_0px_#000]">
                            Wajib Ruang
                        </span>
                        <span class="text-xl">📋</span>
                    </div>
                    <h3 class="text-sm font-black font-heading text-black mb-1">
                        Berita Acara Pelaksanaan (BAP)
                    </h3>
                    <p class="text-[11px] text-slate-600 font-medium mb-3">
                        Dokumen formal pertanggungjawaban jalannya ujian di tiap ruang, memuat klausul pernyataan, rekap siswa, dan catatan insiden.
                    </p>

                    <form action="{{ route('ujian.administrasi.berita-acara') }}" method="GET" target="_blank" class="space-y-2">
                        <input type="hidden" name="periode_id" value="{{ $activePeriode->id }}">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 uppercase mb-0.5">Pilih Jadwal Mapel</label>
                            <select name="jadwal_id" class="w-full px-2.5 py-1.5 border-2 border-black rounded-lg bg-slate-50 text-[11px] font-bold">
                                <option value="">-- Format Universal / Bebas --</option>
                                @foreach($jadwals as $j)
                                    <option value="{{ $j->id }}">
                                        {{ $j->tanggal_ujian->format('d/m') }} • Sesi {{ $j->sesi_ke }} • {{ $j->nama_mapel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 uppercase mb-0.5">Pilih Ruang Ujian</label>
                            <select name="ruang_id" class="w-full px-2.5 py-1.5 border-2 border-black rounded-lg bg-slate-50 text-[11px] font-bold">
                                <option value="all">-- Semua Ruang (Bundle Cetak) --</option>
                                @foreach($ruangs as $r)
                                    <option value="{{ $r->id }}">{{ $r->nama_ruang }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="pt-2 flex flex-col gap-1.5">
                            <button type="submit" class="w-full py-2 bg-[#5294FF] hover:bg-blue-400 text-white border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center justify-center gap-1.5 cursor-pointer">
                                <span>🖨️</span>
                                <span>Cetak Berita Acara</span>
                            </button>
                            <button type="submit" name="blangko" value="1" class="w-full py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 border-2 border-black rounded-lg text-[11px] font-bold shadow-[1px_1px_0px_0px_#000] flex items-center justify-center gap-1 cursor-pointer">
                                <span>📄</span>
                                <span>Cetak Blangko BAP Kosong</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Dokumen 3: Label Meja Peserta (Grid A4 2x5) -->
            <div class="bg-white border-4 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] flex flex-col justify-between hover:translate-y-[-2px] transition-transform">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b-2 border-black mb-3">
                        <span class="px-2.5 py-0.5 rounded text-[10px] font-black uppercase bg-[#FFD43B] text-black border border-black shadow-[1px_1px_0px_0px_#000]">
                            Navigasi Meja
                        </span>
                        <span class="text-xl">🏷️</span>
                    </div>
                    <h3 class="text-sm font-black font-heading text-black mb-1">
                        Label Nomor Meja Siswa
                    </h3>
                    <p class="text-[11px] text-slate-600 font-medium mb-3">
                        Format stiker/kartu meja A4 Grid 2x5 (10 label per lembar), nomor peserta tebal, cut guides, nama, rombel & QR Code.
                    </p>

                    <form action="{{ route('ujian.kartu.print-label-meja') }}" method="GET" target="_blank" class="space-y-2">
                        <input type="hidden" name="periode_id" value="{{ $activePeriode->id }}">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 uppercase mb-0.5">Filter Ruang Ujian</label>
                            <select name="ruang_id" class="w-full px-2.5 py-1.5 border-2 border-black rounded-lg bg-slate-50 text-[11px] font-bold">
                                <option value="all">-- Semua Ruang (Seluruh Peserta) --</option>
                                @foreach($ruangs as $r)
                                    <option value="{{ $r->id }}">{{ $r->nama_ruang }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="pt-5">
                            <button type="submit" class="w-full py-2 bg-[#FFD43B] hover:bg-yellow-400 text-black border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center justify-center gap-1.5 cursor-pointer">
                                <span>🖨️</span>
                                <span>Cetak Label Meja Grid A4</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Dokumen 4: Label Pintu & Denah Tempat Duduk -->
            <div class="bg-white border-4 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] flex flex-col justify-between hover:translate-y-[-2px] transition-transform">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b-2 border-black mb-3">
                        <span class="px-2.5 py-0.5 rounded text-[10px] font-black uppercase bg-[#F3D9FA] text-purple-950 border border-black shadow-[1px_1px_0px_0px_#000]">
                            Identitas Ruang
                        </span>
                        <span class="text-xl">🚪</span>
                    </div>
                    <h3 class="text-sm font-black font-heading text-black mb-1">
                        Label Pintu & Denah Duduk
                    </h3>
                    <p class="text-[11px] text-slate-600 font-medium mb-3">
                        Label nama ruang berukuran besar untuk ditempel di pintu dan matriks denah duduk 4 kolom meja siswa.
                    </p>

                    <div class="space-y-2">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 uppercase mb-0.5">Pilih Ruang Ujian</label>
                            <select id="select_ruang_identitas" class="w-full px-2.5 py-1.5 border-2 border-black rounded-lg bg-slate-50 text-[11px] font-bold">
                                @foreach($ruangs as $r)
                                    <option value="{{ $r->id }}">{{ $r->nama_ruang }} ({{ $r->kapasitas_maksimal }} Kursi)</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="pt-2 flex flex-col gap-1.5">
                            <button type="button" onclick="bukaLabelPintu()" class="w-full py-2 bg-[#5294FF] hover:bg-blue-400 text-white border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center justify-center gap-1.5 cursor-pointer">
                                <span>🚪</span>
                                <span>Cetak Label Pintu (A4 Landscape)</span>
                            </button>
                            <button type="button" onclick="bukaDenahDuduk()" class="w-full py-2 bg-[#20C997] hover:bg-emerald-400 text-black border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center justify-center gap-1.5 cursor-pointer">
                                <span>🪑</span>
                                <span>Cetak Denah Duduk Visual (A4)</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dokumen 5: Cover Amplop Naskah Soal & LJK -->
            <div class="bg-white border-4 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] flex flex-col justify-between hover:translate-y-[-2px] transition-transform">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b-2 border-black mb-3">
                        <span class="px-2.5 py-0.5 rounded text-[10px] font-black uppercase bg-amber-100 text-amber-950 border border-black shadow-[1px_1px_0px_0px_#000]">
                            Pengemasan Naskah
                        </span>
                        <span class="text-xl">✉️</span>
                    </div>
                    <h3 class="text-sm font-black font-heading text-black mb-1">
                        Cover Amplop Soal & LJK
                    </h3>
                    <p class="text-[11px] text-slate-600 font-medium mb-3">
                        Label amplop coklat naskah soal & lembar jawaban, dilengkapi rincian eksemplar dan lembar serah terima berkas.
                    </p>

                    <form action="{{ route('ujian.administrasi.label-amplop') }}" method="GET" target="_blank" class="space-y-2">
                        <input type="hidden" name="periode_id" value="{{ $activePeriode->id }}">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 uppercase mb-0.5">Pilih Jadwal Mapel</label>
                            <select name="jadwal_id" class="w-full px-2.5 py-1.5 border-2 border-black rounded-lg bg-slate-50 text-[11px] font-bold">
                                <option value="">-- Semua Jadwal --</option>
                                @foreach($jadwals as $j)
                                    <option value="{{ $j->id }}">
                                        {{ $j->tanggal_ujian->format('d/m') }} • Sesi {{ $j->sesi_ke }} • {{ $j->nama_mapel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 uppercase mb-0.5">Pilih Ruang Ujian</label>
                            <select name="ruang_id" class="w-full px-2.5 py-1.5 border-2 border-black rounded-lg bg-slate-50 text-[11px] font-bold">
                                <option value="all">-- Semua Ruang --</option>
                                @foreach($ruangs as $r)
                                    <option value="{{ $r->id }}">{{ $r->nama_ruang }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="pt-2">
                            <button type="submit" class="w-full py-2 bg-[#FFD43B] hover:bg-yellow-400 text-black border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center justify-center gap-1.5 cursor-pointer">
                                <span>🖨️</span>
                                <span>Cetak Cover Amplop</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Dokumen 6: Jadwal Tugas Mengawas Guru -->
            <div class="bg-white border-4 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] flex flex-col justify-between hover:translate-y-[-2px] transition-transform">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b-2 border-black mb-3">
                        <span class="px-2.5 py-0.5 rounded text-[10px] font-black uppercase bg-[#20C997] text-black border border-black shadow-[1px_1px_0px_0px_#000]">
                            Personal Guru
                        </span>
                        <span class="text-xl">👨‍🏫</span>
                    </div>
                    <h3 class="text-sm font-black font-heading text-black mb-1">
                        Jadwal Tugas Mengawas Guru
                    </h3>
                    <p class="text-[11px] text-slate-600 font-medium mb-3">
                        Surat tugas individual untuk masing-masing guru, berisi matriks hari, tanggal, sesi, ruang tugas, dan pasangan pengawas.
                    </p>

                    <form action="{{ route('ujian.pengawas.print-jadwal-guru') }}" method="GET" target="_blank" class="space-y-2">
                        <input type="hidden" name="periode_id" value="{{ $activePeriode->id }}">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 uppercase mb-0.5">Pilih Guru Pengawas</label>
                            <select name="teacher_id" class="w-full px-2.5 py-1.5 border-2 border-black rounded-lg bg-slate-50 text-[11px] font-bold">
                                <option value="all">-- Seluruh Guru yang Bertugas (Bundle Cetak) --</option>
                                @foreach($gurus as $g)
                                    <option value="{{ $g->id }}">{{ $g->user?->name }} (NIP: {{ $g->nip ?: '-' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="pt-5">
                            <button type="submit" class="w-full py-2 bg-[#5294FF] hover:bg-blue-400 text-white border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center justify-center gap-1.5 cursor-pointer">
                                <span>🖨️</span>
                                <span>Cetak Surat Tugas Guru</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Dokumen 7: Tata Tertib Peserta & Pengawas -->
            <div class="bg-white border-4 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] flex flex-col justify-between hover:translate-y-[-2px] transition-transform">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b-2 border-black mb-3">
                        <span class="px-2.5 py-0.5 rounded text-[10px] font-black uppercase bg-slate-100 text-black border border-black shadow-[1px_1px_0px_0px_#000]">
                            Tata Tertib
                        </span>
                        <span class="text-xl">📜</span>
                    </div>
                    <h3 class="text-sm font-black font-heading text-black mb-1">
                        Tata Tertib Peserta & Pengawas
                    </h3>
                    <p class="text-[11px] text-slate-600 font-medium mb-3">
                        Dokumen resmi tata tertib ruang ujian madrasah untuk ditempel di papan pengumuman dan setiap pintu ruang ujian.
                    </p>
                    <div class="pt-8 flex items-center gap-2">
                        <a href="{{ route('ujian.administrasi.tata-tertib', ['periode_id' => $activePeriode->id]) }}" target="_blank" class="flex-1 py-2 bg-slate-100 hover:bg-slate-200 text-black border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center justify-center gap-1.5 cursor-pointer">
                            <span>🖨️</span>
                            <span>Cetak Lembar Tata Tertib</span>
                        </a>
                        <button type="button" onclick="bukaEditorDokumen('tatib')" class="px-3.5 py-2 bg-[#FFD43B] hover:bg-yellow-400 text-black border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center justify-center gap-1.5 cursor-pointer" title="Edit Isi Tata Tertib">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 20h9"/>
                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                            </svg>
                            <span>Edit</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Dokumen 8: SK Panitia Ujian Resmi -->
            <div class="bg-white border-4 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] flex flex-col justify-between hover:translate-y-[-2px] transition-transform">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b-2 border-black mb-3">
                        <span class="px-2.5 py-0.5 rounded text-[10px] font-black uppercase bg-slate-100 text-black border border-black shadow-[1px_1px_0px_0px_#000]">
                            SK Legalitas
                        </span>
                        <span class="text-xl">🏛️</span>
                    </div>
                    <h3 class="text-sm font-black font-heading text-black mb-1">
                        SK Panitia Ujian Resmi
                    </h3>
                    <p class="text-[11px] text-slate-600 font-medium mb-3">
                        Surat Keputusan Kepala Madrasah tentang Penetapan Panitia Ujian lengkap dengan konsiderans dan lampiran struktur panitia.
                    </p>
                    <div class="pt-8 flex items-center gap-2">
                        <a href="{{ route('ujian.administrasi.sk-panitia', ['periode_id' => $activePeriode->id]) }}" target="_blank" class="flex-1 py-2 bg-[#FFD43B] hover:bg-yellow-400 text-black border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center justify-center gap-1.5 cursor-pointer">
                            <span>🖨️</span>
                            <span>Cetak SK Panitia Resmi</span>
                        </a>
                        <button type="button" onclick="bukaEditorDokumen('sk')" class="px-3.5 py-2 bg-white hover:bg-slate-100 text-black border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center justify-center gap-1.5 cursor-pointer" title="Edit Isi SK Panitia">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 20h9"/>
                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                            </svg>
                            <span>Edit</span>
                        </button>
                    </div>
                </div>
            </div>

        </div>

        <!-- Universal Editor Modal untuk Tata Tertib & SK Panitia -->
        @include('ujian.administrasi.partials.editor-modal')
    @endif
</div>

@push('scripts')
<script>
    function bukaLabelPintu() {
        const select = document.getElementById('select_ruang_identitas');
        if (!select || !select.value) {
            alert('Silakan pilih ruang ujian terlebih dahulu.');
            return;
        }
        window.open(`/ujian/ruang-ujian/${select.value}/cetak-label-pintu?periode_id={{ $activePeriode?->id }}`, '_blank');
    }

    function bukaDenahDuduk() {
        const select = document.getElementById('select_ruang_identitas');
        if (!select || !select.value) {
            alert('Silakan pilih ruang ujian terlebih dahulu.');
            return;
        }
        window.open(`/ujian/ruang-ujian/${select.value}/cetak-denah?periode_id={{ $activePeriode?->id }}`, '_blank');
    }
</script>
@endpush
@endsection
