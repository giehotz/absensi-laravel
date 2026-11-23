<x-print-preview-layout 
    :title="'Rekapitulasi Presensi Pengawas Ujian - ' . ($activePeriode->nama_periode ?? 'Madrasah')"
    orientation="landscape"
    paperSize="A4"
    :backUrl="route('ujian.pengawas.presensi.index', ['periode_id' => $activePeriode?->id])"
    pageMargin="12mm 12mm"
    sheetClass="p-6 sm:p-10"
>
    <style>
        .sheet-rekap-presensi {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.35;
            color: #000;
            width: 100%;
        }
        .judul-dokumen {
            text-align: center;
            margin: 15px 0 15px 0;
        }
        .judul-dokumen h3 {
            margin: 0;
            font-size: 13pt;
            text-transform: uppercase;
            text-decoration: underline;
            font-weight: bold;
        }
        .judul-dokumen .sub-judul {
            font-size: 11pt;
            font-weight: bold;
            margin-top: 3px;
            text-transform: uppercase;
        }
        .tabel-rekap {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 20px;
        }
        .tabel-rekap th, .tabel-rekap td {
            border: 1px solid #000;
            padding: 5px 7px;
            font-size: 10pt;
        }
        .tabel-rekap th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .ttd-table {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
            font-size: 10.5pt;
        }
        .ttd-table td {
            text-align: center;
            vertical-align: top;
            width: 45%;
        }
    </style>

    <div class="sheet-rekap-presensi">
        <!-- Kop Surat Resmi -->
        <x-kop-surat :useBase64="true" />

        <div class="judul-dokumen">
            <h3>REKAPITULASI PRESENSI & KEHADIRAN PENGAWAS UJIAN</h3>
            <div class="sub-judul">
                {{ $activePeriode->nama_periode ?? 'UJIAN MADRASAH' }}
                @if($activePeriode && $activePeriode->tahun_pelajaran)
                    TAHUN PELAJARAN {{ $activePeriode->tahun_pelajaran }}
                @endif
            </div>
        </div>

        <table class="tabel-rekap">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 35px;">No</th>
                    <th rowspan="2">Nama Guru / Tenaga Pengajar</th>
                    <th rowspan="2" style="width: 140px;">NIP</th>
                    <th rowspan="2" style="width: 65px;">Total Sesi</th>
                    <th colspan="5">Keterangan Kehadiran (Sesi)</th>
                    <th rowspan="2" style="width: 70px;">% Hadir</th>
                </tr>
                <tr>
                    <th style="width: 50px;">Hadir</th>
                    <th style="width: 50px;">Izin</th>
                    <th style="width: 50px;">Sakit</th>
                    <th style="width: 50px;">Alpa</th>
                    <th style="width: 65px;">Digantikan</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $sumTotalSesi = 0;
                    $sumHadir = 0;
                    $sumIzin = 0;
                    $sumSakit = 0;
                    $sumAlpa = 0;
                    $sumDigantikan = 0;
                @endphp
                @forelse($rekapTeachers as $idx => $r)
                    @php
                        $sumTotalSesi += $r['total_sesi'];
                        $sumHadir += $r['hadir'];
                        $sumIzin += $r['izin'];
                        $sumSakit += $r['sakit'];
                        $sumAlpa += $r['alpa'];
                        $sumDigantikan += $r['digantikan'];
                    @endphp
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td class="font-bold">{{ $r['teacher']->user?->name ?? '-' }}</td>
                        <td class="text-center">{{ $r['teacher']->nip ?? '-' }}</td>
                        <td class="text-center font-bold">{{ $r['total_sesi'] }}</td>
                        <td class="text-center">{{ $r['hadir'] }}</td>
                        <td class="text-center">{{ $r['izin'] ?: '-' }}</td>
                        <td class="text-center">{{ $r['sakit'] ?: '-' }}</td>
                        <td class="text-center">{{ $r['alpa'] ?: '-' }}</td>
                        <td class="text-center">{{ $r['digantikan'] ?: '-' }}</td>
                        <td class="text-center font-bold">{{ $r['persen_hadir'] }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center" style="padding: 20px; font-style: italic;">
                            Belum ada data penugasan pengawas pada periode ujian ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($rekapTeachers->isNotEmpty())
                <tfoot>
                    <tr style="background-color: #f9f9f9; font-weight: bold;">
                        <td colspan="3" class="text-center">TOTAL KESELURUHAN</td>
                        <td class="text-center">{{ $sumTotalSesi }}</td>
                        <td class="text-center">{{ $sumHadir }}</td>
                        <td class="text-center">{{ $sumIzin }}</td>
                        <td class="text-center">{{ $sumSakit }}</td>
                        <td class="text-center">{{ $sumAlpa }}</td>
                        <td class="text-center">{{ $sumDigantikan }}</td>
                        <td class="text-center">
                            {{ $sumTotalSesi > 0 ? round(($sumHadir / $sumTotalSesi) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                </tfoot>
            @endif
        </table>

        <!-- Tanda Tangan Block -->
        <table class="ttd-table">
            <tr>
                <td>
                    <div>Mengetahui,</div>
                    <div class="font-bold">Kepala Madrasah,</div>
                    <div style="height: 60px;"></div>
                    <div class="font-bold" style="text-decoration: underline;">
                        {{ $ttdSetting->nama_kepala ?? ($setting->card_principal_name ?? '............................................') }}
                    </div>
                    <div style="font-size: 9.5pt;">
                        NIP. {{ $ttdSetting->nip_kepala ?? ($setting->card_principal_nip ?? '....................................') }}
                    </div>
                </td>
                <td style="width: 10%;"></td>
                <td>
                    <div>{{ $activePeriode->tempat_cetak ?? 'Tanggamus' }}, {{ date('d F Y') }}</div>
                    <div class="font-bold">Ketua Panitia Ujian,</div>
                    <div style="height: 60px;"></div>
                    <div class="font-bold" style="text-decoration: underline;">
                        ............................................
                    </div>
                    <div style="font-size: 9.5pt;">
                        NIP. ....................................
                    </div>
                </td>
            </tr>
        </table>
    </div>
</x-print-preview-layout>
