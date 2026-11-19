<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Hadir Peserta Ujian - {{ $activePeriode->nama_periode ?? 'Madrasah' }}</title>
    <style>
        * {
            box-sizing: border-box;
        }
        @page {
            size: A4 portrait;
            margin: 1cm 1.2cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 9.5pt;
            line-height: 1.25;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
        }
        .page-sheet {
            width: 100%;
            page-break-inside: avoid;
        }
        .page-break {
            page-break-after: always;
            break-after: page;
        }
        .judul-dokumen {
            text-align: center;
            margin: 4px 0 6px 0;
        }
        .judul-dokumen h3 {
            margin: 0;
            font-size: 12.5pt;
            text-transform: uppercase;
            text-decoration: underline;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .judul-dokumen .sub-judul {
            font-size: 10.5pt;
            font-weight: bold;
            margin-top: 1.5px;
            text-transform: uppercase;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 6px;
            font-size: 9.5pt;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 1.5px 3px;
            vertical-align: top;
        }
        .tabel-peserta {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            page-break-inside: auto;
        }
        .tabel-peserta th, .tabel-peserta td {
            border: 1px solid #000;
            padding: 3px 4px;
            font-size: 9pt;
        }
        .tabel-peserta th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .ttd-box-cell {
            height: 22px;
            position: relative;
            vertical-align: middle;
        }
        .ttd-nomor {
            font-size: 8.5pt;
            font-weight: normal;
        }
        .rekap-box {
            border: 1px solid #000;
            padding: 5px 8px;
            font-size: 9pt;
            line-height: 1.3;
        }
        .print-toolbar {
            position: fixed;
            top: 15px;
            right: 15px;
            z-index: 999;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 6px;
        }
        .btn-print-action {
            background: #FFD43B;
            border: 2px solid #000;
            padding: 9px 18px;
            font-family: Arial, sans-serif;
            font-weight: bold;
            font-size: 13px;
            cursor: pointer;
            box-shadow: 2px 2px 0 #000;
            border-radius: 6px;
        }
        .btn-print-action:hover {
            background: #fcc419;
            transform: translate(-1px, -1px);
            box-shadow: 3px 3px 0 #000;
        }
        .print-hint {
            background: #fff;
            border: 1px solid #000;
            padding: 6px 10px;
            border-radius: 4px;
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
            max-width: 280px;
            text-align: right;
            box-shadow: 1px 1px 0 #000;
        }
        @media screen {
            body {
                background-color: #e2e8f0;
                padding: 20px 10px;
            }
            .page-sheet {
                background: #fff;
                padding: 1cm 1.2cm;
                margin: 0 auto 25px auto;
                max-width: 210mm;
                min-height: 297mm;
                box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
            }
        }
        @media print {
            @page {
                size: A4 portrait;
                margin: 1cm 1.2cm;
            }
            .print-toolbar { display: none !important; }
            html, body {
                background: #fff !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
            }
            .page-sheet {
                background: transparent !important;
                padding: 0 !important;
                margin: 0 !important;
                box-shadow: none !important;
                max-width: none !important;
                min-height: auto !important;
                width: 100% !important;
            }
        }
    </style>
</head>
<body>
    <div class="print-toolbar">
        <button type="button" onclick="window.print()" class="btn-print-action">
            🖨️ Cetak / Simpan PDF
        </button>
        <div class="print-hint">
            💡 Tips: Pada dialog cetak browser, gunakan margin <strong>Default</strong>. Hilangkan centang <strong>Header and footer</strong> agar bebas dari teks URL/tanggal di tepi kertas.
        </div>
    </div>

    @forelse($sheets as $sheetIndex => $sheet)
        @php
            $r = $sheet['ruang'];
            $j = $sheet['jadwal'];
            $pesertas = $sheet['pesertas'];
            $pengawas = $sheet['pengawas'];
            $hasPengawas2 = !empty($pengawas?->pengawas_2_id);
        @endphp

        <div class="page-sheet {{ !$loop->last ? 'page-break' : '' }}">
            <!-- Kop Surat Resmi -->
            <x-kop-surat :useBase64="true" />

            <div class="judul-dokumen">
                <h3>DAFTAR HADIR PESERTA UJIAN</h3>
                <div class="sub-judul">
                    {{ $activePeriode->nama_periode ?? 'UJIAN MADRASAH' }}
                    @if($activePeriode && $activePeriode->tahun_pelajaran)
                        - TP {{ $activePeriode->tahun_pelajaran }}
                    @endif
                </div>
            </div>

            <!-- Meta Data Jadwal & Ruang -->
            <table class="meta-table">
                <tr>
                    <td style="width: 16%;">Mata Pelajaran</td>
                    <td style="width: 2%;">:</td>
                    <td style="width: 38%;" class="font-bold">
                        @if($isBlangko)
                            ................................................
                        @else
                            {{ $j->nama_mapel ?? 'Semua Mapel' }}
                        @endif
                    </td>
                    <td style="width: 16%;">Ruang Ujian</td>
                    <td style="width: 2%;">:</td>
                    <td style="width: 26%;" class="font-bold">{{ $r->nama_ruang }}</td>
                </tr>
                <tr>
                    <td>Hari, Tanggal</td>
                    <td>:</td>
                    <td>
                        @if($isBlangko || !$j)
                            ................................................
                        @else
                            {{ $j->tanggal_ujian ? $j->tanggal_ujian->locale('id')->translatedFormat('l, d F Y') : '-' }}
                        @endif
                    </td>
                    <td>Sesi / Waktu</td>
                    <td>:</td>
                    <td>
                        @if($isBlangko || !$j)
                            Sesi ..... / ...... s.d ...... WIB
                        @else
                            Sesi {{ $j->sesi_ke }} ({{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }} WIB)
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Tingkat / Kelas</td>
                    <td>:</td>
                    <td class="font-bold">
                        @if($isBlangko)
                            ................................................
                        @else
                            {{ $sheet['rombel'] ?? ($j->target_kelas ?? 'Semua Tingkat') }}
                        @endif
                    </td>
                    <td>Bentuk Ujian</td>
                    <td>:</td>
                    <td>{{ $j->jenis_soal ?? 'Tertulis / CBT' }}</td>
                </tr>
            </table>

            <!-- Tabel Daftar Peserta -->
            <table class="tabel-peserta">
                <thead>
                    <tr>
                        <th style="width: 28px;">No</th>
                        <th style="width: 95px;">No. Peserta</th>
                        <th style="width: 85px;">NISN</th>
                        <th>Nama Peserta</th>
                        <th style="width: 65px;">Kelas</th>
                        <th style="width: 130px;">Tanda Tangan</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalPeserta = $isBlangko ? max(20, $r->kapasitas_maksimal) : $pesertas->count();
                    @endphp

                    @for($i = 0; $i < $totalPeserta; $i++)
                        @php
                            $p = $isBlangko ? null : $pesertas->get($i);
                            $nomorUrut = $i + 1;
                        @endphp
                        <tr>
                            <td class="text-center">{{ $nomorUrut }}</td>
                            <td class="text-center font-bold font-mono">{{ $p?->nomor_peserta ?? '..............' }}</td>
                            <td class="text-center">{{ $p?->nisn ?? ($p?->nis_lokal ?? '..........') }}</td>
                            <td class="font-bold">{{ $p?->nama_peserta ?? '' }}</td>
                            <td class="text-center">{{ $p?->nama_kelas ?? ($p?->schoolClass?->name ?? '') }}</td>
                            <td class="ttd-box-cell">
                                @if($nomorUrut % 2 !== 0)
                                    <span class="ttd-nomor">{{ $nomorUrut }}. ....................</span>
                                @else
                                    <div style="text-align: right; padding-right: 5px;">
                                        <span class="ttd-nomor">{{ $nomorUrut }}. ....................</span>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>

            <!-- Rekapitulasi & Tanda Tangan Pengawas (Kompak 1 Halaman) -->
            @if($hasPengawas2)
                {{-- Jika Ada Pengawas 2: Tampilkan Keduanya --}}
                <table style="width: 100%; margin-top: 10px; border-collapse: collapse; font-size: 9pt; page-break-inside: avoid;">
                    <tr>
                        <td style="width: 44%; vertical-align: top;">
                            <div class="rekap-box">
                                <div class="font-bold" style="text-decoration: underline; margin-bottom: 2px;">REKAPITULASI RUANG:</div>
                                <div>• Jumlah Peserta Seharusnya : {{ $pesertas->count() ?: '.....' }} Orang</div>
                                <div>• Jumlah Peserta Hadir : ..... Orang</div>
                                <div>• Jumlah Peserta Tidak Hadir : ..... Orang</div>
                            </div>
                        </td>
                        <td style="width: 4%;"></td>
                        <td style="width: 52%; vertical-align: top;">
                            <div style="text-align: center; margin-bottom: 3px;">
                                {{ $activePeriode->tempat_cetak ?? 'Tanggamus' }}, {{ $j?->tanggal_ujian ? $j->tanggal_ujian->locale('id')->translatedFormat('d F Y') : date('d F Y') }}
                            </div>
                            <table style="width: 100%; border-collapse: collapse; text-align: center; font-size: 8.5pt;">
                                <tr>
                                    <td style="width: 50%; vertical-align: top;">
                                        <div class="font-bold">Pengawas 1</div>
                                        <div style="height: 42px;"></div>
                                        <div class="font-bold" style="text-decoration: underline;">
                                            {{ $pengawas?->pengawas1?->user?->name ?? '....................................' }}
                                        </div>
                                        <div style="font-size: 8pt; margin-top: 1px;">
                                            NIP. {{ $pengawas?->pengawas1?->nip ?? '............................' }}
                                        </div>
                                    </td>
                                    <td style="width: 50%; vertical-align: top;">
                                        <div class="font-bold">Pengawas 2</div>
                                        <div style="height: 42px;"></div>
                                        <div class="font-bold" style="text-decoration: underline;">
                                            {{ $pengawas?->pengawas2?->user?->name }}
                                        </div>
                                        <div style="font-size: 8pt; margin-top: 1px;">
                                            NIP. {{ $pengawas?->pengawas2?->nip ?? '-' }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            @else
                {{-- Hanya Pengawas 1 (Pengawas 2 Diabaikan / Tidak Ditampilkan) --}}
                <table style="width: 100%; margin-top: 10px; border-collapse: collapse; font-size: 9pt; page-break-inside: avoid;">
                    <tr>
                        <td style="width: 48%; vertical-align: top;">
                            <div class="rekap-box">
                                <div class="font-bold" style="text-decoration: underline; margin-bottom: 2px;">REKAPITULASI RUANG:</div>
                                <div>• Jumlah Peserta Seharusnya : {{ $pesertas->count() ?: '.....' }} Orang</div>
                                <div>• Jumlah Peserta Hadir : ..... Orang</div>
                                <div>• Jumlah Peserta Tidak Hadir : ..... Orang</div>
                            </div>
                        </td>
                        <td style="width: 10%;"></td>
                        <td style="width: 42%; vertical-align: top; text-align: center;">
                            <div>{{ $activePeriode->tempat_cetak ?? 'Tanggamus' }}, {{ $j?->tanggal_ujian ? $j->tanggal_ujian->locale('id')->translatedFormat('d F Y') : date('d F Y') }}</div>
                            <div class="font-bold" style="margin-top: 1px;">Pengawas Ruang Ujian,</div>
                            <div style="height: 42px;"></div>
                            <div class="font-bold" style="text-decoration: underline;">
                                {{ $pengawas?->pengawas1?->user?->name ?? '............................................' }}
                            </div>
                            <div style="font-size: 8pt; margin-top: 1px;">
                                NIP. {{ $pengawas?->pengawas1?->nip ?? '....................................' }}
                            </div>
                        </td>
                    </tr>
                </table>
            @endif
        </div>
    @empty
        <div style="text-align: center; padding: 50px; font-family: sans-serif;">
            <h3>Tidak ada data peserta atau ruang yang dapat dicetak.</h3>
            <p>Silakan pilih periode, ruang, atau jadwal ujian yang valid.</p>
        </div>
    @endforelse
</body>
</html>
