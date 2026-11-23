<x-print-preview-layout 
    :title="'Daftar Hadir Pengawas Ruang - ' . ($activePeriode->nama_periode ?? 'Ujian')"
    orientation="landscape"
    paperSize="A4"
    :backUrl="route('ujian.pengawas.index')"
    pageMargin="8mm 12mm"
    sheetClass="p-4 sm:p-6"
>
    <style>
        .sheet-daftar-hadir-pengawas {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.25;
            color: #000;
            width: 100%;
        }
        .judul-dokumen {
            text-align: center;
            margin: 6px 0 10px 0;
        }
        .judul-dokumen h3 {
            margin: 0;
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .judul-dokumen .periode-info {
            margin-top: 2px;
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .info-hari-box {
            margin-bottom: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11pt;
        }
        .tabel-data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        .tabel-data th, .tabel-data td {
            border: 1px solid #000;
            padding: 5px 6px;
            font-size: 11pt;
            vertical-align: middle;
        }
        .tabel-data th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .ttd-table {
            width: 100%;
            margin-top: 16px;
            border-collapse: collapse;
            font-size: 11pt;
        }
        .ttd-table td {
            border: none;
            padding: 0;
        }
        .sesi-sheet-content {
            page-break-inside: avoid;
            break-inside: avoid;
        }
    </style>

    <div class="sheet-daftar-hadir-pengawas">
        @forelse($groupedPengawas as $items)
            @php
                $firstItem = $items->first();
                $jadwalDate = $firstItem?->jadwal?->tanggal_ujian;
                $namaHari = $jadwalDate ? $jadwalDate->locale('id')->translatedFormat('l, d F Y') : '-';
                $sesiKe = $firstItem?->jadwal?->sesi_ke ?? 1;
                $jamMulai = substr($firstItem?->jadwal?->jam_mulai ?? '07:30', 0, 5);
                $jamSelesai = substr($firstItem?->jadwal?->jam_selesai ?? '09:30', 0, 5);
                $jumlahPengawas = $items->filter(fn ($pw) => $pw->pengawas1)->count()
                    + $items->filter(fn ($pw) => $pw->pengawas2)->count();
            @endphp

            <div class="{{ !$loop->last ? 'page-break' : '' }}">
                <div class="sesi-sheet-content">
                    {{-- Kop Surat Resmi Lembaga --}}
                    <x-kop-surat :useBase64="true" />

                    <div class="judul-dokumen">
                        <h3>DAFTAR HADIR PENGAWAS RUANG UJIAN {{ ($kelompok ?? 'all') === '1-2' ? '(KELOMPOK KELAS 1–2)' : (($kelompok ?? 'all') === '3-6' ? '(KELOMPOK KELAS 3–6)' : '') }}</h3>
                        <div class="periode-info">
                            {{ strtoupper($activePeriode->nama_periode ?? 'UJIAN MADRASAH') }}
                        </div>
                        <div style="font-size: 10pt; font-weight: normal; margin-top: 1px;">
                            Tahun Pelajaran {{ $activePeriode->tahun_pelajaran ?? '-' }} (Semester {{ ucfirst($activePeriode->semester ?? '-') }})
                        </div>
                    </div>

                    <div class="info-hari-box">
                        <div>
                            <strong>Hari / Tanggal :</strong> {{ $namaHari }}
                            <span style="margin: 0 8px;">|</span>
                            <strong>Sesi :</strong> {{ $sesiKe }}
                            <span style="font-size: 10pt;">({{ $jamMulai }} - {{ $jamSelesai }} WIB)</span>
                        </div>
                        <div>
                            <strong>Jumlah Pengawas :</strong> {{ $jumlahPengawas }} Orang ({{ $items->count() }} Ruang)
                        </div>
                    </div>

                <table class="tabel-data">
                    <thead>
                        <tr>
                            <th style="width: 35px;">No</th>
                            <th style="width: 260px;">Nama Pengawas Ruang</th>
                            <th style="width: 70px;">Sesi</th>
                            <th style="width: 160px;">Ruang</th>
                            <th>Mata Pelajaran</th>
                            <th style="width: 210px;">Tanda Tangan Pengawas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $idx => $pw)
                            <tr>
                                <td class="text-center">{{ $idx + 1 }}</td>
                                <td>
                                    <div>{{ $pw->pengawas1?->user?->name ?: '..................................................' }}</div>
                                    @if($pw->pengawas2)
                                        <div style="margin-top: 4px;">{{ $pw->pengawas2?->user?->name }}</div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="font-bold">Sesi {{ $pw->jadwal?->sesi_ke ?? '1' }}</span>
                                </td>
                                <td class="text-center font-bold">
                                    {{ $pw->ruang?->nama_ruang ?? '-' }}
                                </td>
                                <td>
                                    <strong>{{ $pw->jadwal?->nama_mapel ?? '-' }}</strong>
                                </td>
                                <td style="vertical-align: middle;">
                                    <div style="font-size: 9.5pt; padding: 2px 0; {{ ($idx + 1) % 2 === 1 ? 'text-align: left;' : 'text-align: right; padding-right: 15px;' }}">
                                        {{ $idx + 1 }}. ................................
                                    </div>
                                    @if($pw->pengawas2)
                                        <div style="font-size: 9.5pt; padding: 2px 0; {{ ($idx + 1) % 2 === 1 ? 'text-align: right; padding-right: 15px;' : 'text-align: left;' }}">
                                            {{ $idx + 1 }}. ................................
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center" style="padding: 20px;">
                                    Belum ada jadwal penugasan pengawas untuk tanggal ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Bagian Tanda Tangan 2 Kolom Sejajar: Kiri Kepala Madrasah, Kanan Ketua Panitia --}}
                <table class="ttd-table">
                    <tr>
                        <td style="width: 50%; text-align: center; vertical-align: top;">
                            <div>Mengetahui,</div>
                            <div style="font-weight: bold; margin-top: 2px;">Kepala Madrasah,</div>
                            <div style="height: 48px;"></div>
                            <div style="font-weight: bold; text-decoration: underline;">
                                {{ $namaKepala }}
                            </div>
                            <div style="font-size: 10pt; margin-top: 2px;">
                                NIP. {{ $nipKepala }}
                            </div>
                        </td>
                        <td style="width: 50%; text-align: center; vertical-align: top;">
                            <div>{{ $tempatCetak }}, {{ $tanggalCetak->locale('id')->translatedFormat('d F Y') }}</div>
                            <div style="font-weight: bold; margin-top: 2px;">Ketua Panitia Ujian,</div>
                            <div style="height: 48px;"></div>
                            <div style="font-weight: bold; text-decoration: underline;">
                                {{ $namaKetua }}
                            </div>
                            <div style="font-size: 10pt; margin-top: 2px;">
                                NIP. {{ $nipKetua }}
                            </div>
                        </td>
                    </tr>
                </table>
                </div>
            </div>
        @empty
            <div style="text-align: center; padding: 40px;">
                <x-kop-surat :useBase64="true" />
                <div class="judul-dokumen">
                    <h3>DAFTAR HADIR PENGAWAS RUANG UJIAN {{ ($kelompok ?? 'all') === '1-2' ? '(KELOMPOK KELAS 1–2)' : (($kelompok ?? 'all') === '3-6' ? '(KELOMPOK KELAS 3–6)' : '') }}</h3>
                </div>
                <p class="text-center" style="padding: 30px; font-style: italic;">
                    Tidak ada data jadwal pengawas ruang ujian untuk kriteria yang dipilih.
                </p>
            </div>
        @endforelse

        <script>
            (function () {
                var pageContentHeightPx = Math.floor(((210 - 16) / 25.4) * 96 * 0.92);

                function fitSessionSheets() {
                    document.querySelectorAll('.sesi-sheet-content').forEach(function (sheet) {
                        sheet.style.zoom = '';
                        var naturalHeight = sheet.offsetHeight;

                        if (naturalHeight > pageContentHeightPx) {
                            var scale = pageContentHeightPx / naturalHeight;
                            sheet.style.zoom = scale.toFixed(3);
                        }
                    });
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', fitSessionSheets);
                } else {
                    fitSessionSheets();
                }

                window.addEventListener('beforeprint', fitSessionSheets);
            })();
        </script>
    </div>
</x-print-preview-layout>
