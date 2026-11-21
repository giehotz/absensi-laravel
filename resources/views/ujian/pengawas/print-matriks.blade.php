<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Pengawas Ruang Matriks - {{ ($kelompok ?? '3-6') === '1-2' ? 'Sheet 4 (Kelas 1–2)' : 'Sheet 3 (Kelas 3–6)' }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 3mm 5mm 3mm 5mm;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 7.5pt;
            line-height: 1.15;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 4px 6px;
        }
        .judul-dokumen {
            text-align: center;
            margin: 2px 0 4px 0;
        }
        .judul-dokumen h4 {
            margin: 0;
            font-size: 9.5pt;
            text-transform: uppercase;
            text-decoration: underline;
        }
        .tabel-matriks, .tabel-legenda {
            border-collapse: collapse;
            width: 100%;
        }
        .tabel-matriks th, .tabel-matriks td,
        .tabel-legenda th, .tabel-legenda td {
            border: 1px solid #000;
            padding: 2px 3px;
            font-size: 7.2pt;
            line-height: 1.15;
        }
        .tabel-matriks th, .tabel-legenda th {
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
            box-shadow: 2px 2px 0 0 #000;
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: sans-serif;
            font-size: 11px;
            z-index: 9999;
        }
        .btn-toolbar {
            padding: 4px 10px;
            border: 1px solid #000;
            background: #f8fafc;
            color: #000;
            font-weight: bold;
            text-decoration: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-toolbar.active {
            background: #FFD43B;
        }
        .btn-toolbar:hover {
            background: #e2e8f0;
        }
        .btn-print {
            background: #51CF66 !important;
            color: #000 !important;
            font-weight: 900;
        }

        @media print {
            .print-toolbar {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    {{-- Toolbar Cetak & Pilihan Format --}}
    <div class="print-toolbar">
        <span style="font-weight: bold;">Pilihan Cetak Matriks:</span>
        <a href="{{ route('ujian.pengawas.print-matriks', ['periode_id' => $activePeriode->id, 'kelompok' => '3-6', 'show_mapel' => 1]) }}"
           class="btn-toolbar {{ ($kelompok ?? '3-6') === '3-6' ? 'active' : '' }}">
            📊 Sheet 3: Kelompok 3–6 (Mapel + Kode 1..N)
        </a>
        <a href="{{ route('ujian.pengawas.print-matriks', ['periode_id' => $activePeriode->id, 'kelompok' => '1-2', 'show_mapel' => 0]) }}"
           class="btn-toolbar {{ ($kelompok ?? '3-6') === '1-2' ? 'active' : '' }}">
            📋 Sheet 4: Kelompok 1–2 (Kode 1..M)
        </a>
        <button type="button" onclick="window.print()" class="btn-toolbar btn-print">
            🖨️ Cetak / Simpan PDF
        </button>
    </div>

    <div class="rekap-sheet">
        {{-- Kop Surat Resmi Lembaga Compact --}}
        <div class="kop-rekap-compact">
            <x-kop-surat :useBase64="true" />
        </div>

        <div class="judul-dokumen">
            <h4>
                {{ ($kelompok ?? '3-6') === '1-2' ? 'JADWAL PENGAWAS RUANG UJIAN (KELOMPOK KELAS 1–2)' : 'JADWAL PENGAWAS RUANG & MATA PELAJARAN UJIAN (KELOMPOK KELAS 3–6)' }}
            </h4>
            <div style="font-size: 8pt; font-weight: bold; margin-top: 1px;">
                {{ strtoupper($activePeriode->nama_periode ?? 'ASESMEN SUMATIF') }} - TAHUN PELAJARAN {{ $activePeriode->tahun_pelajaran ?? '-' }}
            </div>
        </div>

        <div style="display: flex; gap: 6px; align-items: flex-start; width: 100%;">
            {{-- Tabel Matriks Kiri --}}
            <div style="flex: 1; min-width: 0;">
                <table class="tabel-matriks">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 18px;">NO</th>
                            <th rowspan="2" style="width: 72px;">HARI / TANGGAL</th>
                            <th rowspan="2" style="width: 22px;">JAM</th>
                            @if($showMapel)
                                <th rowspan="2" style="width: 110px;">MATA PELAJARAN</th>
                            @endif
                            <th rowspan="2" style="width: 62px;">PUKUL</th>
                            @php
                                $groupedRoomsByLevel = [];
                                foreach($roomCodes ?? [] as $rc) {
                                    $lvl = substr($rc, 0, 1);
                                    $groupedRoomsByLevel[$lvl][] = $rc;
                                }
                            @endphp
                            @foreach($groupedRoomsByLevel as $lvl => $rcs)
                                <th colspan="{{ count($rcs) }}">KELAS {{ $lvl }}</th>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($roomCodes ?? [] as $rc)
                                <th style="min-width: 18px;">{{ substr($rc, 1) ?: $rc }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $r)
                            <tr>
                                <td class="text-center font-bold">{{ $r['no'] }}</td>
                                <td style="white-space: nowrap;">
                                    @if($r['is_new_date'])
                                        <strong>{{ $r['tanggal']->locale('id')->translatedFormat('l') }},</strong><br>
                                        {{ $r['tanggal']->format('d/m/Y') }}
                                    @endif
                                </td>
                                <td class="text-center font-bold">{{ $r['sesi_ke'] }}</td>
                                @if($showMapel)
                                    <td class="font-bold">{{ $r['mapel'] ?: '—' }}</td>
                                @endif
                                <td class="text-center font-mono" style="font-size: 6.8pt; white-space: nowrap;">{{ $r['pukul'] }}</td>

                                {{-- Kolom Ruangan Dinamis --}}
                                @foreach($roomCodes ?? [] as $code)
                                    @php $val = $r['rooms'][$code] ?? null; @endphp
                                    @if($val === null)
                                        <td class="bg-shaded"></td>
                                    @else
                                        <td class="text-center font-bold {{ $val === '-' ? 'text-slate-400' : '' }}" style="font-size: 7.5pt;">
                                            {{ $val }}
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="25" class="text-center" style="padding: 10px;">Belum ada jadwal pelaksanaan ujian pada kelompok ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Tabel Legenda Pengawas Kanan --}}
            @php
                $totalLegenda = count($legend);
                $isDualCol = $totalLegenda > 12;
                $halfLegenda = $isDualCol ? (int) ceil($totalLegenda / 2) : $totalLegenda;
                $legendaCol1 = array_slice($legend, 0, $halfLegenda);
                $legendaCol2 = $isDualCol ? array_slice($legend, $halfLegenda) : [];
            @endphp
            <div style="{{ $isDualCol ? 'width: 360px;' : 'width: 220px;' }} flex-shrink: 0;">
                <table class="tabel-legenda">
                    <thead>
                        @if($isDualCol)
                            <tr>
                                <th style="width: 22px;">KD</th>
                                <th>NAMA PENGAWAS</th>
                                <th style="width: 20px;">JML</th>
                                <th style="width: 22px;">KD</th>
                                <th>NAMA PENGAWAS</th>
                                <th style="width: 20px;">JML</th>
                            </tr>
                        @else
                            <tr>
                                <th style="width: 26px;">KODE</th>
                                <th>NAMA PENGAWAS</th>
                                <th style="width: 24px;">JML</th>
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
                                    @if($p1)
                                        <td class="text-center font-bold">{{ $p1['code'] }}</td>
                                        <td style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 130px;">{{ $p1['nama'] }}</td>
                                        <td class="text-center">{{ $p1['jumlah'] }}</td>
                                    @else
                                        <td colspan="3"></td>
                                    @endif

                                    @if($p2)
                                        <td class="text-center font-bold">{{ $p2['code'] }}</td>
                                        <td style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 130px;">{{ $p2['nama'] }}</td>
                                        <td class="text-center">{{ $p2['jumlah'] }}</td>
                                    @else
                                        <td colspan="3"></td>
                                    @endif
                                </tr>
                            @endfor
                        @else
                            @forelse($legend as $lp)
                                <tr>
                                    <td class="text-center font-bold">{{ $lp['code'] }}</td>
                                    <td style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 160px;">{{ $lp['nama'] }}</td>
                                    <td class="text-center">{{ $lp['jumlah'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center" style="padding: 6px;">Belum ada penugasan</td>
                                </tr>
                            @endforelse
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Blok Tanda Tangan Resmi Compact --}}
        <table style="width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 7.5pt;">
            <tr>
                <td style="width: 50%; vertical-align: top; border: none; padding: 0 4px;">
                    <div>Mengetahui,</div>
                    <div style="font-weight: bold;">Kepala Madrasah</div>
                    <div style="height: 38px;"></div>
                    <div style="font-weight: bold; text-decoration: underline;">{{ $namaKepala }}</div>
                    <div>NIP. {{ $nipKepala }}</div>
                </td>
                <td style="width: 50%; vertical-align: top; text-align: right; border: none; padding: 0 4px;">
                    <div>{{ $tempatCetak }}, {{ $tanggalCetak instanceof \Carbon\Carbon ? $tanggalCetak->locale('id')->translatedFormat('d F Y') : \Carbon\Carbon::parse($tanggalCetak)->locale('id')->translatedFormat('d F Y') }}</div>
                    <div style="font-weight: bold;">Ketua Panitia Ujian</div>
                    <div style="height: 38px;"></div>
                    <div style="font-weight: bold; text-decoration: underline;">{{ $namaKetua }}</div>
                    <div>NIP. {{ $nipKetua }}</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
