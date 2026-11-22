@php
    $isLandscape = in_array(($format ?? 'standar'), ['rekap', 'matriks_rombel'], true);
    $formatTitle = 'Jadwal Pelaksanaan Ujian';
    if (($format ?? 'standar') === 'matriks_rombel') {
        $formatTitle = 'Jadwal Mapel Rombel ' . (($kelompok ?? 'all') === '3-6' ? '3–6' : '1–2');
    } elseif (($format ?? 'standar') === 'rekap') {
        if (($kelompok ?? 'all') === '1-2') {
            $formatTitle = 'Rekap Pengawas Kelas 1–2';
        } elseif (($kelompok ?? 'all') === '3-6') {
            $formatTitle = 'Rekap Pengawas Kelas 3–6';
        } else {
            $formatTitle = 'Rekap Pengawas Semua Kelas (1–6)';
        }
    }
@endphp

<x-print-preview-layout 
    :title="$formatTitle . ' - ' . ($activePeriode->nama_periode ?? 'Madrasah')"
    :orientation="$isLandscape ? 'landscape' : 'portrait'"
    paperSize="A4"
    :backUrl="route('ujian.jadwal.index')"
    :pageMargin="$isLandscape ? '3mm 5mm 3mm 5mm' : '12mm 15mm'"
    :sheetClass="$isLandscape ? 'p-2 sm:p-3' : 'p-6 sm:p-8'"
>
    <x-slot:actions>
        <span class="text-[11px] font-bold text-slate-600 hidden sm:inline mr-1">Format:</span>
        <a href="{{ route('ujian.jadwal.print', ['periode_id' => $activePeriode->id, 'format' => 'standar']) }}" 
           class="px-2 py-1 text-xs font-bold border-2 border-black rounded shadow-[1.5px_1.5px_0px_0px_#000] {{ ($format ?? 'standar') === 'standar' ? 'bg-[#FFD43B] text-black font-black' : 'bg-white hover:bg-slate-100 text-slate-800' }}">
            📄 Standar
        </a>
        <a href="{{ route('ujian.jadwal.print', ['periode_id' => $activePeriode->id, 'format' => 'matriks_rombel', 'kelompok' => '1-2']) }}" 
           class="px-2 py-1 text-xs font-bold border-2 border-black rounded shadow-[1.5px_1.5px_0px_0px_#000] {{ (($format ?? 'standar') === 'matriks_rombel' && ($kelompok ?? 'all') === '1-2') ? 'bg-[#FFD43B] text-black font-black' : 'bg-white hover:bg-slate-100 text-slate-800' }}">
            📑 Sheet 1 (Mapel 1–2)
        </a>
        <a href="{{ route('ujian.jadwal.print', ['periode_id' => $activePeriode->id, 'format' => 'matriks_rombel', 'kelompok' => '3-6']) }}" 
           class="px-2 py-1 text-xs font-bold border-2 border-black rounded shadow-[1.5px_1.5px_0px_0px_#000] {{ (($format ?? 'standar') === 'matriks_rombel' && ($kelompok ?? 'all') === '3-6') ? 'bg-[#FFD43B] text-black font-black' : 'bg-white hover:bg-slate-100 text-slate-800' }}">
            📋 Sheet 2 (Mapel 3–6)
        </a>
        <span class="text-slate-300 mx-0.5 hidden sm:inline">|</span>
        <a href="{{ route('ujian.jadwal.print', ['periode_id' => $activePeriode->id, 'format' => 'rekap', 'kelompok' => '1-2']) }}" 
           title="Rekap Pengawas Matriks Kelas 1–2"
           class="px-2 py-1 text-xs font-bold border-2 border-black rounded shadow-[1.5px_1.5px_0px_0px_#000] {{ (($format ?? 'standar') === 'rekap' && ($kelompok ?? 'all') === '1-2') ? 'bg-[#FFD43B] text-black font-black' : 'bg-white hover:bg-slate-100 text-slate-800' }}">
            📊 Pengawas 1–2
        </a>
        <a href="{{ route('ujian.jadwal.print', ['periode_id' => $activePeriode->id, 'format' => 'rekap', 'kelompok' => '3-6']) }}" 
           title="Rekap Pengawas Matriks Kelas 3–6"
           class="px-2 py-1 text-xs font-bold border-2 border-black rounded shadow-[1.5px_1.5px_0px_0px_#000] {{ (($format ?? 'standar') === 'rekap' && ($kelompok ?? 'all') === '3-6') ? 'bg-[#FFD43B] text-black font-black' : 'bg-white hover:bg-slate-100 text-slate-800' }}">
            📊 Pengawas 3–6
        </a>
        <a href="{{ route('ujian.jadwal.print', ['periode_id' => $activePeriode->id, 'format' => 'rekap', 'kelompok' => 'all']) }}" 
           title="Rekap Pengawas Matriks Gabungan Semua Kelas (1–6)"
           class="px-2 py-1 text-xs font-bold border-2 border-black rounded shadow-[1.5px_1.5px_0px_0px_#000] {{ (($format ?? 'standar') === 'rekap' && in_array(($kelompok ?? 'all'), ['all', '1-6'], true)) ? 'bg-[#FFD43B] text-black font-black' : 'bg-white hover:bg-slate-100 text-slate-800' }}">
            📊 Rekap Semua (1–6)
        </a>
    </x-slot:actions>

    <style>
        .sheet-jadwal {
            font-family: 'Times New Roman', Times, serif;
            font-size: {{ $isLandscape ? '7.5pt' : '11pt' }};
            line-height: {{ $isLandscape ? '1.15' : '1.25' }};
            color: #000;
            width: 100%;
        }
        .judul-dokumen {
            text-align: center;
            margin: {{ $isLandscape ? '2px 0 4px 0' : '12px 0 16px 0' }};
        }
        .judul-dokumen h4 {
            margin: 0;
            font-size: {{ $isLandscape ? '9.5pt' : '12pt' }};
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
            padding: {{ $isLandscape ? '1.5px 2px' : '6px 8px' }};
            font-size: {{ $isLandscape ? '7pt' : '10pt' }};
            line-height: {{ $isLandscape ? '1.15' : '1.25' }};
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

        @media print {
            .rekap-sheet-print {
                max-height: 202mm !important;
                overflow: hidden !important;
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }
    </style>

    <div class="sheet-jadwal {{ $isLandscape ? 'rekap-sheet-print' : '' }}">
        @if($isLandscape)
            {{-- ============================================================== --}}
            {{-- FORMAT B & SHEET 1 / 2: REKAP MATRIKS DINAMIS LANDSCAPE        --}}
            {{-- ============================================================== --}}
            <div class="kop-rekap-compact">
                <x-kop-surat :useBase64="true" />
            </div>

            <div class="judul-dokumen">
                <h4>
                    @if(($format ?? 'standar') === 'matriks_rombel')
                        JADWAL MATA PELAJARAN UJIAN PER ROMBEL (KELOMPOK KELAS {{ ($kelompok ?? 'all') === '3-6' ? '3–6' : '1–2' }})
                    @else
                        @if(($kelompok ?? 'all') === '1-2')
                            JADWAL PENGAWAS RUANG & MATA PELAJARAN UJIAN (KELOMPOK KELAS 1–2)
                        @elseif(($kelompok ?? 'all') === '3-6')
                            JADWAL PENGAWAS RUANG & MATA PELAJARAN UJIAN (KELOMPOK KELAS 3–6)
                        @else
                            JADWAL PENGAWAS RUANG & MATA PELAJARAN UJIAN (SEMUA KELAS 1–6)
                        @endif
                    @endif
                </h4>
                <div style="font-size: 8pt; font-weight: bold; margin-top: 1px;">
                    {{ strtoupper($activePeriode->nama_periode ?? 'ASESMEN SUMATIF') }} - TAHUN PELAJARAN {{ $activePeriode->tahun_pelajaran ?? '-' }}
                </div>
            </div>

            @if(($format ?? 'standar') === 'matriks_rombel')
                {{-- ========================================================== --}}
                {{-- TABEL MATRIKS ROMBEL: FULL WIDTH 100% (TANPA LEGENDA)       --}}
                {{-- ========================================================== --}}
                <div style="width: 100%;">
                    <table class="tabel-rekap" style="width: 100%;">
                        <thead>
                            <tr>
                                <th rowspan="2" style="width: 20px;">NO</th>
                                <th rowspan="2" style="width: 76px;">HARI / TANGGAL</th>
                                <th rowspan="2" style="width: 22px;">JAM</th>
                                <th rowspan="2" style="width: 68px;">PUKUL</th>
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
                                    <th style="min-width: 22px;">{{ strtoupper(substr($rc, 1) ?: $rc) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rekapRows as $r)
                                <tr>
                                    @if($r['is_new_date'])
                                        <td rowspan="{{ $r['date_rowspan'] ?? 1 }}" class="text-center font-bold" style="vertical-align: middle;">
                                            {{ $r['no'] }}
                                        </td>
                                        <td rowspan="{{ $r['date_rowspan'] ?? 1 }}" style="white-space: nowrap; vertical-align: middle; text-align: center;">
                                            <strong>{{ $r['tanggal']->locale('id')->translatedFormat('l') }},</strong><br>
                                            {{ $r['tanggal']->format('d/m/Y') }}
                                        </td>
                                    @endif
                                    <td class="text-center font-bold">{{ $r['sesi_ke'] }}</td>
                                    <td class="text-center font-mono" style="font-size: 7pt; white-space: nowrap;">{{ $r['pukul'] }}</td>

                                    {{-- Kolom Ruangan Dinamis --}}
                                    @foreach($roomCodes ?? [] as $code)
                                        @php $val = $r['rooms'][$code] ?? null; @endphp
                                        @if($val === null)
                                            <td class="bg-shaded"></td>
                                        @else
                                            <td class="text-center font-bold {{ $val === '-' ? 'text-slate-400' : '' }}" style="font-size: 7.5pt; line-height: 1.15; padding: 2.5px 3px;">
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

            @else
                {{-- ========================================================== --}}
                {{-- FORMAT REKAP GABUNGAN: DENGAN TABEL LEGENDA PENGAWAS KANAN  --}}
                {{-- ========================================================== --}}
                <div style="display: flex; gap: 6px; align-items: flex-start; width: 100%;">
                    {{-- Tabel Matriks Kiri --}}
                    <div style="flex: 1; min-width: 0;">
                        <table class="tabel-rekap">
                            <thead>
                                <tr>
                                    <th rowspan="2" style="width: 18px;">NO</th>
                                    <th rowspan="2" style="width: 68px;">HARI / TANGGAL</th>
                                    <th rowspan="2" style="width: 20px;">JAM</th>
                                    @if(($kelompok ?? 'all') === '3-6')
                                        <th rowspan="2" style="width: 110px;">MAPEL KELAS 3-6</th>
                                    @elseif(($kelompok ?? 'all') === 'all')
                                        <th rowspan="2" style="width: 82px;">MAPEL KELAS 1-2</th>
                                        <th rowspan="2" style="width: 88px;">MAPEL KELAS 3-6</th>
                                    @endif
                                    <th rowspan="2" style="width: 58px;">PUKUL</th>
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
                                        <th style="min-width: 16px;">{{ strtoupper(substr($rc, 1) ?: $rc) }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rekapRows as $r)
                                    <tr>
                                        @if($r['is_new_date'])
                                            <td rowspan="{{ $r['date_rowspan'] ?? 1 }}" class="text-center font-bold" style="vertical-align: middle;">
                                                {{ $r['no'] }}
                                            </td>
                                            <td rowspan="{{ $r['date_rowspan'] ?? 1 }}" style="white-space: nowrap; vertical-align: middle; text-align: center;">
                                                <strong>{{ $r['tanggal']->locale('id')->translatedFormat('l') }},</strong><br>
                                                {{ $r['tanggal']->format('d/m/Y') }}
                                            </td>
                                        @endif
                                        <td class="text-center font-bold">{{ $r['sesi_ke'] }}</td>
                                        @if(($kelompok ?? 'all') === '3-6')
                                            <td class="font-bold" style="font-size: 7.5pt; vertical-align: middle;">{{ $r['mapel_36'] ?: '—' }}</td>
                                        @elseif(($kelompok ?? 'all') === 'all')
                                            <td style="font-size: 7.2pt; vertical-align: middle;">{{ $r['mapel_12'] ?: '—' }}</td>
                                            <td class="font-bold" style="font-size: 7.5pt; vertical-align: middle;">{{ $r['mapel_36'] ?: '—' }}</td>
                                        @endif
                                        <td class="text-center font-mono" style="font-size: 6.8pt; white-space: nowrap;">{{ $r['pukul'] }}</td>

                                        {{-- Kolom Ruangan Dinamis --}}
                                        @foreach($roomCodes ?? [] as $code)
                                            @php $val = $r['rooms'][$code] ?? null; @endphp
                                            @if($val === null)
                                                <td class="bg-shaded"></td>
                                            @else
                                                @php
                                                    $isArr = is_array($val);
                                                    $pw = $isArr ? ($val['pengawas'] ?? '-') : $val;
                                                    $mpl = $isArr ? ($val['mapel'] ?? '—') : null;
                                                @endphp
                                                <td class="text-center font-bold {{ $pw === '-' ? 'text-slate-400' : '' }}" style="font-size: 7.2pt; padding: 2px 1px; vertical-align: middle;">
                                                    @if($isArr)
                                                        <div style="font-size: 7pt; font-weight: 800; line-height: 1.1; color: #0f172a;">{{ $mpl }}</div>
                                                        <div style="font-size: 6.8pt; line-height: 1.1; margin-top: 1.5px; font-weight: 700; color: {{ $pw === '-' ? '#94a3b8' : '#334155' }};">
                                                            {{ $pw === '-' ? '-' : '[' . $pw . ']' }}
                                                        </div>
                                                    @else
                                                        {{ $val }}
                                                    @endif
                                                </td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="25" class="text-center" style="padding: 10px;">Belum ada jadwal pelaksanaan ujian pada periode ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(($kelompok ?? 'all') === '1-2' && !empty($legendMapel))
                        {{-- Tabel Keterangan Kode & Mata Pelajaran (Setelah Tabel Kelas 2, Sebelum Tabel Kode Pengawas) --}}
                        <div style="width: 220px; flex-shrink: 0;">
                            <table class="tabel-legenda">
                                <thead>
                                    <tr>
                                        <th style="width: 44px;" title="Kode Mata Pelajaran">KODE</th>
                                        <th>MATA PELAJARAN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($legendMapel as $lm)
                                        <tr>
                                            <td class="text-center font-bold" style="font-size: 7.2pt;">{{ $lm['code'] }}</td>
                                            <td style="font-size: 7pt; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 170px;" title="{{ $lm['nama'] }}">{{ $lm['nama'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    {{-- Tabel Legenda Pengawas Kanan --}}
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
            @endif

            {{-- Tanda Tangan 3 Pihak: Atas Kiri Ketua, Atas Kanan Sekretaris, Bawah Tengah Kepala Madrasah --}}
            <table class="ttd-table" style="width: 100%; border-collapse: collapse; border: none; margin-top: 6px;">
                <tr>
                    <td style="width: 44%; text-align: center; vertical-align: top; border: none; padding: 0;">
                        <div style="visibility: hidden; font-size: 7pt; line-height: 1.15;">&nbsp;</div>
                        <div style="font-weight: bold;">Ketua</div>
                        <div style="height: 38px;"></div>
                        <div style="font-weight: bold; text-decoration: underline;">
                            {{ $namaKetua }}
                        </div>
                        <div style="font-size: 7.2pt; margin-top: 1px;">
                            NIP. {{ $nipKetua }}
                        </div>
                    </td>
                    <td style="width: 12%; border: none;"></td>
                    <td style="width: 44%; text-align: center; vertical-align: top; border: none; padding: 0;">
                        <div>{{ $tempatCetak }}, {{ $tanggalCetak->locale('id')->translatedFormat('d F Y') }}</div>
                        <div style="font-weight: bold;">Sekretaris</div>
                        <div style="height: 38px;"></div>
                        <div style="font-weight: bold; text-decoration: underline;">
                            {{ $namaSekretaris }}
                        </div>
                        <div style="font-size: 7.2pt; margin-top: 1px;">
                            NIP. {{ $nipSekretaris ?? '' }}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align: center; vertical-align: top; border: none; padding-top: 6px;">
                        <div>Mengetahui</div>
                        <div style="font-weight: bold; margin-top: 1px;">Kepala Madrasah,</div>
                        <div style="height: 38px;"></div>
                        <div style="font-weight: bold; text-decoration: underline;">
                            {{ $namaKepala }}
                        </div>
                        <div style="font-size: 7.2pt; margin-top: 1px;">
                            NIP. {{ $nipKepala }}
                        </div>
                    </td>
                </tr>
            </table>

        @else
            {{-- ============================================================== --}}
            {{-- FORMAT A: DAFTAR LIST JADWAL STANDAR PORTRAIT                  --}}
            {{-- ============================================================== --}}
            <x-kop-surat :useBase64="true" />

            <div class="judul-dokumen">
                <h4>JADWAL PELAKSANAAN UJIAN</h4>
                <div style="font-size: 10.5pt; font-weight: bold; margin-top: 3px;">
                    {{ strtoupper($activePeriode->nama_periode ?? 'UJIAN MADRASAH') }}
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

            {{-- Tanda Tangan 3 Pihak: Atas Kiri Ketua, Atas Kanan Sekretaris, Bawah Tengah Kepala Madrasah --}}
            <table class="ttd-table" style="width: 100%; border-collapse: collapse; border: none; margin-top: 25px;">
                <tr>
                    <td style="width: 44%; text-align: center; vertical-align: top; border: none; padding: 0;">
                        <div style="visibility: hidden; font-size: 9.5pt; line-height: 1.25;">&nbsp;</div>
                        <div style="font-weight: bold;">Ketua</div>
                        <div style="height: 52px;"></div>
                        <div style="font-weight: bold; text-decoration: underline;">
                            {{ $namaKetua }}
                        </div>
                        <div style="font-size: 9.5pt; margin-top: 2px;">
                            NIP. {{ $nipKetua }}
                        </div>
                    </td>
                    <td style="width: 12%; border: none;"></td>
                    <td style="width: 44%; text-align: center; vertical-align: top; border: none; padding: 0;">
                        <div>{{ $tempatCetak }}, {{ $tanggalCetak->locale('id')->translatedFormat('d F Y') }}</div>
                        <div style="font-weight: bold;">Sekretaris</div>
                        <div style="height: 52px;"></div>
                        <div style="font-weight: bold; text-decoration: underline;">
                            {{ $namaSekretaris }}
                        </div>
                        <div style="font-size: 9.5pt; margin-top: 2px;">
                            NIP. {{ $nipSekretaris ?? '' }}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align: center; vertical-align: top; border: none; padding-top: 16px;">
                        <div>Mengetahui</div>
                        <div style="font-weight: bold; margin-top: 2px;">Kepala Madrasah,</div>
                        <div style="height: 52px;"></div>
                        <div style="font-weight: bold; text-decoration: underline;">
                            {{ $namaKepala }}
                        </div>
                        <div style="font-size: 9.5pt; margin-top: 2px;">
                            NIP. {{ $nipKepala }}
                        </div>
                    </td>
                </tr>
            </table>
        @endif
    </div>
</x-print-preview-layout>