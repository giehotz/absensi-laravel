<x-print-preview-layout 
    :title="'Jadwal Pengawas Ruang Matriks - ' . (($kelompok ?? '3-6') === '1-2' ? 'Sheet 4 (Kelas 1–2)' : (($kelompok ?? '3-6') === '3-6' ? 'Sheet 3 (Kelas 3–6)' : 'Rekap Semua (1–6)'))"
    orientation="landscape"
    paperSize="A4"
    :backUrl="route('ujian.pengawas.index')"
    pageMargin="3mm 5mm 3mm 5mm"
    sheetClass="p-2 sm:p-3"
>
    <x-slot:actions>
        <span class="text-[11px] font-bold text-slate-600 hidden sm:inline mr-1">Matriks:</span>
        <a href="{{ route('ujian.pengawas.print-matriks', ['periode_id' => $activePeriode->id, 'kelompok' => '1-2', 'show_mapel' => 0]) }}"
           class="px-2 py-1 text-xs font-bold border-2 border-black rounded shadow-[1.5px_1.5px_0px_0px_#000] {{ ($kelompok ?? '3-6') === '1-2' ? 'bg-[#FFD43B] text-black font-black' : 'bg-white hover:bg-slate-100 text-slate-800' }}">
            📋 Sheet 4: Kelompok 1–2
        </a>
        <a href="{{ route('ujian.pengawas.print-matriks', ['periode_id' => $activePeriode->id, 'kelompok' => '3-6', 'show_mapel' => 1]) }}"
           class="px-2 py-1 text-xs font-bold border-2 border-black rounded shadow-[1.5px_1.5px_0px_0px_#000] {{ ($kelompok ?? '3-6') === '3-6' ? 'bg-[#FFD43B] text-black font-black' : 'bg-white hover:bg-slate-100 text-slate-800' }}">
            📊 Sheet 3: Kelompok 3–6
        </a>
        <a href="{{ route('ujian.pengawas.print-matriks', ['periode_id' => $activePeriode->id, 'kelompok' => 'all', 'show_mapel' => 1]) }}"
           class="px-2 py-1 text-xs font-bold border-2 border-black rounded shadow-[1.5px_1.5px_0px_0px_#000] {{ in_array(($kelompok ?? '3-6'), ['all', '1-6'], true) ? 'bg-[#FFD43B] text-black font-black' : 'bg-white hover:bg-slate-100 text-slate-800' }}">
            📊 Rekap Semua (1–6)
        </a>
    </x-slot:actions>

    <style>
        .sheet-matriks {
            font-family: 'Times New Roman', Times, serif;
            font-size: 7.5pt;
            line-height: 1.15;
            color: #000;
            width: 100%;
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

        @media print {
            .sheet-matriks {
                max-height: 202mm !important;
                overflow: hidden !important;
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }
    </style>

    <div class="sheet-matriks">
        {{-- Kop Surat Resmi Lembaga Compact --}}
        <div class="kop-rekap-compact">
            <x-kop-surat :useBase64="true" />
        </div>

        <div class="judul-dokumen">
            <h4>
                @if(($kelompok ?? '3-6') === '1-2')
                    JADWAL PENGAWAS RUANG UJIAN (KELOMPOK KELAS 1–2)
                @elseif(($kelompok ?? '3-6') === '3-6')
                    JADWAL PENGAWAS RUANG & MATA PELAJARAN UJIAN (KELOMPOK KELAS 3–6)
                @else
                    JADWAL PENGAWAS RUANG & MATA PELAJARAN UJIAN (SEMUA KELAS 1–6)
                @endif
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
                                <th style="min-width: 18px;">{{ strtoupper(substr($rc, 1) ?: $rc) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $r)
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
                                <td colspan="25" class="text-center" style="padding: 10px;">Belum ada jadwal pelaksanaan ujian pada kelompok ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(($kelompok ?? '3-6') === '1-2' && !empty($legendMapel))
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

        {{-- Blok Tanda Tangan 3 Pihak: Atas Kiri Ketua, Atas Kanan Sekretaris, Bawah Tengah Kepala Madrasah --}}
        @php
            $formattedTglCetak = $tanggalCetak instanceof \Carbon\Carbon 
                ? $tanggalCetak->locale('id')->translatedFormat('d F Y') 
                : \Carbon\Carbon::parse($tanggalCetak)->locale('id')->translatedFormat('d F Y');
        @endphp
        <table class="ttd-table" style="width: 100%; border-collapse: collapse; border: none; margin-top: 6px; font-size: 7.5pt;">
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
                    <div>{{ $tempatCetak }}, {{ $formattedTglCetak }}</div>
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
    </div>
</x-print-preview-layout>