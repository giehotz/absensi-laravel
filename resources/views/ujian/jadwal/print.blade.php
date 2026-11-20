<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Pelaksanaan Ujian - {{ $activePeriode->nama_periode ?? 'Madrasah' }}</title>
    <style>
        @page {
            size: {{ ($format ?? 'standar') === 'rekap' ? 'A4 landscape' : 'A4 portrait' }};
            margin: {{ ($format ?? 'standar') === 'rekap' ? '3mm 5mm 3mm 5mm' : '1.5cm' }};
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: {{ ($format ?? 'standar') === 'rekap' ? '7.5pt' : '11pt' }};
            line-height: {{ ($format ?? 'standar') === 'rekap' ? '1.15' : '1.25' }};
            color: #000;
            background: #fff;
            margin: 0;
            padding: {{ ($format ?? 'standar') === 'rekap' ? '4px 6px' : '20px' }};
        }
        .judul-dokumen {
            text-align: center;
            margin: {{ ($format ?? 'standar') === 'rekap' ? '2px 0 4px 0' : '15px 0' }};
        }
        .judul-dokumen h4 {
            margin: 0;
            font-size: {{ ($format ?? 'standar') === 'rekap' ? '9.5pt' : '12pt' }};
            text-transform: uppercase;
            text-decoration: underline;
        }
        .tabel-jadwal, .tabel-rekap, .tabel-legenda {
            border-collapse: collapse;
            width: 100%;
        }
        .tabel-jadwal th, .tabel-jadwal td,
        .tabel-rekap th, .tabel-rekap td,
        .tabel-legenda th, .tabel-legenda td {
            border: 1px solid #000;
            padding: {{ ($format ?? 'standar') === 'rekap' ? '1.5px 2px' : '6px 8px' }};
            font-size: {{ ($format ?? 'standar') === 'rekap' ? '7pt' : '10pt' }};
            line-height: {{ ($format ?? 'standar') === 'rekap' ? '1.15' : '1.25' }};
        }
        .tabel-jadwal th, .tabel-rekap th, .tabel-legenda th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .bg-shaded { background-color: #FEF08A; }

        /* Compact Kop Surat Khusus Rekap 1 Lembar Landscape */
        .kop-rekap-compact .kop-surat-container {
            margin-bottom: 2px !important;
        }
        .kop-rekap-compact img {
            width: 44px !important;
            height: 44px !important;
        }
        .kop-rekap-compact td {
            padding: 0 4px !important;
        }
        .kop-rekap-compact [style*="font-size: 13pt"] {
            font-size: 9.5pt !important;
        }
        .kop-rekap-compact [style*="font-size: 12pt"] {
            font-size: 8.5pt !important;
        }
        .kop-rekap-compact [style*="font-size: 14pt"] {
            font-size: 10.5pt !important;
            margin-top: 1px !important;
        }
        .kop-rekap-compact [style*="font-size: 9pt"] {
            font-size: 6.8pt !important;
            line-height: 1.15 !important;
        }
        .kop-rekap-compact [style*="border-top: 2.5px"] {
            border-top-width: 1.5px !important;
            margin-top: 2px !important;
        }
        .kop-rekap-compact [style*="border-top: 1px"] {
            margin-top: 1px !important;
        }

        .rekap-sheet {
            width: 100%;
        }

        .print-toolbar {
            position: fixed;
            top: 12px;
            right: 12px;
            background: #fff;
            border: 2px solid #000;
            padding: 6px 12px;
            font-family: Arial, sans-serif;
            font-size: 11px;
            box-shadow: 3px 3px 0 #000;
            border-radius: 8px;
            z-index: 999;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-toolbar {
            padding: 5px 10px;
            border: 1.5px solid #000;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            color: #000;
            font-size: 11px;
        }
        .btn-toolbar.active {
            background-color: #FFD43B;
            box-shadow: 1px 1px 0 #000;
        }
        .btn-print {
            background-color: #51CF66;
            color: #000;
            box-shadow: 1px 1px 0 #000;
        }
        @media print {
            .print-toolbar { display: none !important; }
            html, body {
                width: 100% !important;
                height: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden !important;
            }
            .rekap-sheet {
                max-height: 202mm !important;
                overflow: hidden !important;
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }
    </style>
</head>
<body>
    {{-- Toolbar Cetak & Ganti Format --}}
    <div class="print-toolbar">
        <span style="font-weight: bold; margin-right: 4px;">Pilihan Format:</span>
        <a href="{{ route('ujian.jadwal.print', ['periode_id' => $activePeriode->id, 'format' => 'standar']) }}" 
           class="btn-toolbar {{ ($format ?? 'standar') === 'standar' ? 'active' : '' }}">
            📄 Daftar Standar (Portrait)
        </a>
        <a href="{{ route('ujian.jadwal.print', ['periode_id' => $activePeriode->id, 'format' => 'rekap']) }}" 
           class="btn-toolbar {{ ($format ?? 'standar') === 'rekap' ? 'active' : '' }}">
            📊 Rekap Matriks 1 Lembar (Landscape)
        </a>
        <button type="button" onclick="window.print()" class="btn-toolbar btn-print">
            🖨️ Cetak / Simpan PDF
        </button>
    </div>

    @if(($format ?? 'standar') === 'rekap')
        {{-- ============================================================== --}}
        {{-- FORMAT B: REKAP MATRIKS 1 LEMBAR LANDSCAPE (SEPERTI JADWAL.PDF) --}}
        {{-- ============================================================== --}}
        <div class="rekap-sheet">
            {{-- Kop Surat Resmi Lembaga Compact --}}
            <div class="kop-rekap-compact">
                <x-kop-surat :useBase64="true" />
            </div>

            <div class="judul-dokumen">
                <h4>JADWAL PENGAWAS RUANG & MATA PELAJARAN UJIAN</h4>
                <div style="font-size: 8pt; font-weight: bold; margin-top: 1px;">
                    {{ strtoupper($activePeriode->nama_periode ?? 'ASESMEN SUMATIF') }} - TAHUN PELAJARAN {{ $activePeriode->tahun_pelajaran ?? '-' }}
                </div>
            </div>

            <div style="display: flex; gap: 6px; align-items: flex-start; width: 100%;">
                {{-- Tabel Matriks Kiri --}}
                <div style="flex: 1; min-width: 0;">
                    <table class="tabel-rekap">
                        <thead>
                            <tr>
                                <th rowspan="2" style="width: 18px;">NO</th>
                                <th rowspan="2" style="width: 68px;">HARI / TANGGAL</th>
                                <th rowspan="2" style="width: 20px;">JAM</th>
                                <th rowspan="2" style="width: 82px;">MAPEL KELAS 1-2</th>
                                <th rowspan="2" style="width: 88px;">MAPEL KELAS 3-6</th>
                                <th rowspan="2" style="width: 58px;">PUKUL</th>
                                <th colspan="3">KELAS 1</th>
                                <th colspan="3">KELAS 2</th>
                                <th colspan="2">KELAS 3</th>
                                <th colspan="3">KELAS 4</th>
                                <th colspan="3">KELAS 5</th>
                                <th colspan="2">KELAS 6</th>
                            </tr>
                            <tr>
                                <th style="width: 15px;">A</th>
                                <th style="width: 15px;">B</th>
                                <th style="width: 15px;">C</th>
                                <th style="width: 15px;">A</th>
                                <th style="width: 15px;">B</th>
                                <th style="width: 15px;">C</th>
                                <th style="width: 15px;">A</th>
                                <th style="width: 15px;">B</th>
                                <th style="width: 15px;">A</th>
                                <th style="width: 15px;">B</th>
                                <th style="width: 15px;">C</th>
                                <th style="width: 15px;">A</th>
                                <th style="width: 15px;">B</th>
                                <th style="width: 15px;">C</th>
                                <th style="width: 15px;">A</th>
                                <th style="width: 15px;">B</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rekapRows as $r)
                                <tr>
                                    <td class="text-center font-bold">{{ $r['no'] }}</td>
                                    <td style="white-space: nowrap;">
                                        @if($r['is_new_date'])
                                            <strong>{{ $r['tanggal']->locale('id')->translatedFormat('l') }},</strong><br>
                                            {{ $r['tanggal']->format('d/m/Y') }}
                                        @endif
                                    </td>
                                    <td class="text-center font-bold">{{ $r['sesi_ke'] }}</td>
                                    <td>{{ $r['mapel_12'] ?: '—' }}</td>
                                    <td class="font-bold">{{ $r['mapel_36'] ?: '—' }}</td>
                                    <td class="text-center font-mono" style="font-size: 6.8pt; white-space: nowrap;">{{ $r['pukul'] }}</td>

                                    {{-- 16 Ruangan --}}
                                    @foreach(['1A', '1B', '1C', '2A', '2B', '2C', '3A', '3B', '4A', '4B', '4C', '5A', '5B', '5C', '6A', '6B'] as $code)
                                        @php $val = $r['rooms'][$code] ?? null; @endphp
                                        @if($val === null)
                                            <td class="bg-shaded"></td>
                                        @else
                                            <td class="text-center font-bold {{ $val === '-' ? 'text-slate-400' : '' }}" style="font-size: 7.2pt;">
                                                {{ $val }}
                                            </td>
                                        @endif
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="22" class="text-center" style="padding: 10px;">Belum ada jadwal pelaksanaan ujian pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Tabel Legenda Pengawas Kanan (Compact Dual-Column jika > 14 orang) --}}
                @php
                    $totalLegenda = count($legendPengawas);
                    $isDualCol = $totalLegenda > 14;
                    $halfLegenda = $isDualCol ? (int) ceil($totalLegenda / 2) : $totalLegenda;
                    $legendaCol1 = array_slice($legendPengawas, 0, $halfLegenda);
                    $legendaCol2 = $isDualCol ? array_slice($legendPengawas, $halfLegenda) : [];
                @endphp
                <div style="{{ $isDualCol ? 'width: 380px;' : 'width: 235px;' }} flex-shrink: 0;">
                    <table class="tabel-legenda">
                        <thead>
                            @if($isDualCol)
                                <tr>
                                    <th style="width: 22px;" title="Kode Pengawas (ID User)">KODE</th>
                                    <th>NAMA PENGAWAS</th>
                                    <th style="width: 20px;">JML</th>
                                    <th style="width: 22px;" title="Kode Pengawas (ID User)">KODE</th>
                                    <th>NAMA PENGAWAS</th>
                                    <th style="width: 20px;">JML</th>
                                </tr>
                            @else
                                <tr>
                                    <th style="width: 30px;" title="Kode Pengawas (ID User)">KODE</th>
                                    <th>NAMA PENGAWAS</th>
                                    <th style="width: 28px;">JML</th>
                                </tr>
                            @endif
                        </thead>
                        <tbody>
                            @if($isDualCol)
                                @for($i = 0; $i < $halfLegenda; $i++)
                                    @php
                                        $p1 = $legendaCol1[$i] ?? null;
                                        $p2 = $legendaCol2[$i] ?? null;
                                    @endphp
                                    <tr>
                                        <td class="text-center font-bold">{{ $p1 ? $p1['user_id'] : '' }}</td>
                                        <td style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 115px;" title="{{ $p1['nama'] ?? '' }}">
                                            {{ $p1 ? $p1['nama'] : '' }}
                                        </td>
                                        <td class="text-center font-bold">{{ $p1 ? $p1['jumlah'] : '' }}</td>
                                        <td class="text-center font-bold">{{ $p2 ? $p2['user_id'] : '' }}</td>
                                        <td style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 115px;" title="{{ $p2['nama'] ?? '' }}">
                                            {{ $p2 ? $p2['nama'] : '' }}
                                        </td>
                                        <td class="text-center font-bold">{{ $p2 ? $p2['jumlah'] : '' }}</td>
                                    </tr>
                                @endfor
                            @else
                                @forelse($legendPengawas as $lp)
                                    <tr>
                                        <td class="text-center font-bold">{{ $lp['user_id'] }}</td>
                                        <td style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 160px;" title="{{ $lp['nama'] }}">
                                            {{ $lp['nama'] }}
                                        </td>
                                        <td class="text-center font-bold">{{ $lp['jumlah'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center" style="padding: 8px;">Belum ada pengawas teralokasi.</td>
                                    </tr>
                                @endforelse
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Tanda Tangan 2 Kolom Sejajar: Kiri Kepala Madrasah, Kanan Ketua Panitia --}}
            <table class="ttd-table" style="width: 100%; border-collapse: collapse; border: none; margin-top: 6px;">
                <tr>
                    <td style="width: 50%; text-align: center; vertical-align: top; border: none; padding: 0;">
                        <div>Mengetahui,</div>
                        <div style="font-weight: bold; margin-top: 1px;">Kepala Madrasah,</div>
                        <div style="height: 38px;"></div>
                        <div style="font-weight: bold; text-decoration: underline;">
                            {{ $namaKepala }}
                        </div>
                        <div style="font-size: 7.2pt; margin-top: 1px;">
                            NIP. {{ $nipKepala }}
                        </div>
                    </td>
                    <td style="width: 50%; text-align: center; vertical-align: top; border: none; padding: 0;">
                        <div>{{ $tempatCetak }}, {{ $tanggalCetak->locale('id')->translatedFormat('d F Y') }}</div>
                        <div style="font-weight: bold; margin-top: 1px;">Ketua Panitia Ujian,</div>
                        <div style="height: 38px;"></div>
                        <div style="font-weight: bold; text-decoration: underline;">
                            {{ $namaKetua }}
                        </div>
                        <div style="font-size: 7.2pt; margin-top: 1px;">
                            NIP. {{ $nipKetua }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>

    @else
        {{-- ============================================================== --}}
        {{-- FORMAT A: DAFTAR LIST JADWAL STANDAR PORTRAIT                  --}}
        {{-- ============================================================== --}}
        {{-- Kop Surat Resmi Lembaga --}}
        <x-kop-surat :useBase64="true" />

        <div class="judul-dokumen">
            <h4>JADWAL PELAKSANAAN UJIAN</h4>
            <div style="font-size: 10.5pt; font-weight: bold; margin-top: 3px;">
                {{ strtoupper($activePeriode->nama_periode ?? 'UJIAN MADRASAH') }}
            </div>
            <div style="font-size: 9.5pt;">
                Tahun Pelajaran {{ $activePeriode->tahun_pelajaran ?? '-' }} (Semester {{ $activePeriode->semester ?? '-' }})
            </div>
        </div>

        <table class="tabel-jadwal">
            <thead>
                <tr>
                    <th style="width: 30px;">No</th>
                    <th style="width: 95px;">Hari, Tanggal</th>
                    <th style="width: 45px;">Sesi</th>
                    <th style="width: 55px;">Mulai</th>
                    <th style="width: 55px;">Selesai</th>
                    <th style="width: 55px;">Durasi</th>
                    <th style="width: 75px;">Bentuk</th>
                    <th>Mata Pelajaran</th>
                    <th style="width: 90px;">Rombel / Kelas</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jadwals as $idx => $j)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>
                            {{ $j->tanggal_ujian->locale('id')->translatedFormat('l') }},<br>
                            {{ $j->tanggal_ujian->format('d/m/Y') }}
                        </td>
                        <td class="text-center font-bold">Sesi {{ $j->sesi_ke }}</td>
                        <td class="text-center">{{ substr($j->jam_mulai, 0, 5) }}</td>
                        <td class="text-center">{{ substr($j->jam_selesai, 0, 5) }}</td>
                        <td class="text-center">{{ $j->durasi_menit }} mnt</td>
                        <td class="text-center">{{ $j->jenis_soal }}</td>
                        <td style="font-weight: bold;">{{ $j->nama_mapel }}</td>
                        <td class="text-center">{{ $j->target_kelas }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center" style="padding: 20px;">Belum ada jadwal yang ditetapkan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Tanda Tangan 2 Kolom Sejajar: Kiri Kepala Madrasah, Kanan Ketua Panitia --}}
        <table class="ttd-table" style="width: 100%; border-collapse: collapse; border: none; margin-top: 25px;">
            <tr>
                <td style="width: 50%; text-align: center; vertical-align: top; border: none; padding: 0;">
                    <div>Mengetahui,</div>
                    <div style="font-weight: bold; margin-top: 2px;">Kepala Madrasah,</div>
                    <div style="height: 55px;"></div>
                    <div style="font-weight: bold; text-decoration: underline;">
                        {{ $namaKepala }}
                    </div>
                    <div style="font-size: 9.5pt; margin-top: 2px;">
                        NIP. {{ $nipKepala }}
                    </div>
                </td>
                <td style="width: 50%; text-align: center; vertical-align: top; border: none; padding: 0;">
                    <div>{{ $tempatCetak }}, {{ $tanggalCetak->locale('id')->translatedFormat('d F Y') }}</div>
                    <div style="font-weight: bold; margin-top: 2px;">Ketua Panitia Ujian,</div>
                    <div style="height: 55px;"></div>
                    <div style="font-weight: bold; text-decoration: underline;">
                        {{ $namaKetua }}
                    </div>
                    <div style="font-size: 9.5pt; margin-top: 2px;">
                        NIP. {{ $nipKetua }}
                    </div>
                </td>
            </tr>
        </table>
    @endif
</body>
</html>
