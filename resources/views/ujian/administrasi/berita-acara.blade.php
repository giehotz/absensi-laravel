<x-print-preview-layout 
    :title="'Berita Acara Pelaksanaan Ujian - ' . ($activePeriode->nama_periode ?? 'Madrasah')"
    orientation="portrait"
    paperSize="A4"
    :backUrl="route('ujian.administrasi.index')"
    pageMargin="12mm 12mm"
    sheetClass="p-6 sm:p-8"
>
    <style>
        .sheet-berita-acara {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.4;
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
        .paragraf-pernyataan {
            text-align: justify;
            text-indent: 2.5em;
            margin-bottom: 12px;
        }
        .tabel-detail {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
            font-size: 10.5pt;
        }
        .tabel-detail td {
            padding: 3px 5px;
            vertical-align: top;
        }
        .tabel-border {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 12px;
        }
        .tabel-border th, .tabel-border td {
            border: 1px solid #000;
            padding: 5px 8px;
            font-size: 10pt;
        }
        .tabel-border th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .kotak-catatan {
            border: 1px solid #000;
            min-height: 80px;
            padding: 8px;
            margin-top: 5px;
            margin-bottom: 15px;
            font-size: 10pt;
        }
        .ttd-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            font-size: 10.5pt;
        }
        .ttd-table td {
            text-align: center;
            vertical-align: top;
            width: 50%;
        }
    </style>

    <div class="sheet-berita-acara">
        @forelse($sheets as $sheetIndex => $sheet)
            @php
                $r = $sheet['ruang'];
                $j = $sheet['jadwal'];
                $pesertaCount = $sheet['peserta_count'];
                $pengawas = $sheet['pengawas'];
                $ba = (!$isBlangko && isset($sheet['berita_acara'])) ? $sheet['berita_acara'] : null;
            @endphp

            <div class="{{ !$loop->last ? 'page-break' : '' }}">
                <!-- Kop Surat Resmi -->
                <x-kop-surat :useBase64="true" />

                <div class="judul-dokumen">
                    <h3>BERITA ACARA PELAKSANAAN UJIAN</h3>
                    <div class="sub-judul">
                        {{ $activePeriode->nama_periode ?? 'UJIAN MADRASAH' }}
                        @if($activePeriode && $activePeriode->tahun_pelajaran)
                            TAHUN PELAJARAN {{ $activePeriode->tahun_pelajaran }}
                        @endif
                    </div>
                </div>

                <p class="paragraf-pernyataan">
                    Pada hari ini <strong>{{ $j?->tanggal_ujian ? $j->tanggal_ujian->locale('id')->translatedFormat('l') : '....................' }}</strong>, 
                    tanggal <strong>{{ $j?->tanggal_ujian ? $j->tanggal_ujian->locale('id')->translatedFormat('d') : '..........' }}</strong>, 
                    bulan <strong>{{ $j?->tanggal_ujian ? $j->tanggal_ujian->locale('id')->translatedFormat('F') : '....................' }}</strong>, 
                    tahun <strong>{{ $j?->tanggal_ujian ? $j->tanggal_ujian->locale('id')->translatedFormat('Y') : '..........' }}</strong>, 
                    di Madrasah Ibtidaiyah Negeri 2 Tanggamus telah diselenggarakan Ujian untuk:
                </p>

                <table class="tabel-detail">
                    <tr>
                        <td style="width: 28%;">Mata Pelajaran</td>
                        <td style="width: 2%;">:</td>
                        <td style="width: 70%;" class="font-bold">{{ $j->nama_mapel ?? '..................................................................' }}</td>
                    </tr>
                    <tr>
                        <td>Tingkat / Kelas</td>
                        <td>:</td>
                        <td class="font-bold">{{ $sheet['rombel'] ?? ($j->target_kelas ?? '..................................................................') }}</td>
                    </tr>
                    <tr>
                        <td>Pukul / Waktu</td>
                        <td>:</td>
                        <td>
                            @if($j)
                                {{ substr($j->jam_mulai, 0, 5) }} s.d. {{ substr($j->jam_selesai, 0, 5) }} WIB (Durasi: {{ $j->durasi_menit }} Menit)
                            @else
                                ................ s.d. ................ WIB
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Sesi Ke- / Ruang</td>
                        <td>:</td>
                        <td class="font-bold">
                            Sesi {{ $j->sesi_ke ?? '.....' }} / {{ $r->nama_ruang }}
                        </td>
                    </tr>
                </table>

                <div class="font-bold" style="margin-top: 10px; margin-bottom: 5px;">I. REKAPITULASI KEHADIRAN PESERTA:</div>
                <table class="tabel-detail" style="margin-left: 15px; width: 95%;">
                    <tr>
                        <td style="width: 45%;">1. Jumlah Peserta Seharusnya</td>
                        <td style="width: 3%;">:</td>
                        <td style="width: 52%;" class="font-bold">{{ $ba ? $ba->peserta_seharusnya : ($pesertaCount ?: '..........') }} Orang</td>
                    </tr>
                    <tr>
                        <td>2. Jumlah Peserta Hadir</td>
                        <td>:</td>
                        <td class="font-bold">{{ $ba ? $ba->peserta_hadir . ' Orang' : '.......... Orang' }}</td>
                    </tr>
                    <tr>
                        <td>3. Jumlah Peserta Tidak Hadir</td>
                        <td>:</td>
                        <td class="font-bold">{{ $ba ? $ba->peserta_tidak_hadir . ' Orang' : '.......... Orang' }}</td>
                    </tr>
                </table>

                <div class="font-bold" style="margin-top: 8px; margin-bottom: 4px;">II. CATATAN PESERTA YANG TIDAK HADIR:</div>
                <table class="tabel-border">
                    <thead>
                        <tr>
                            <th style="width: 30px;">No</th>
                            <th style="width: 110px;">No. Peserta</th>
                            <th>Nama Peserta</th>
                            <th style="width: 80px;">Kelas</th>
                            <th style="width: 110px;">Alasan (S/I/A)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($ba)
                            @php $daftarAbsen = $ba->daftar_absen_siswa ?: []; @endphp
                            @forelse($daftarAbsen as $idx => $absen)
                                <tr>
                                    <td class="text-center">{{ $idx + 1 }}</td>
                                    <td class="text-center">{{ $absen['nomor_peserta'] ?? '-' }}</td>
                                    <td>{{ $absen['nama_peserta'] ?? '-' }}</td>
                                    <td class="text-center">{{ $absen['nama_kelas'] ?? '-' }}</td>
                                    <td class="text-center">
                                        {{ $absen['alasan'] ?? 'Tanpa Keterangan' }}
                                        @if(!empty($absen['catatan']))
                                            <span style="font-size: 8.5pt; color: #444;">({{ $absen['catatan'] }})</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center" style="font-style: italic; color: #555; padding: 10px;">
                                        Nihil (Seluruh peserta hadir lengkap)
                                    </td>
                                </tr>
                            @endforelse
                        @else
                            @for($k = 1; $k <= 3; $k++)
                                <tr>
                                    <td class="text-center">{{ $k }}</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                            @endfor
                        @endif
                    </tbody>
                </table>

                <div class="font-bold" style="margin-top: 8px; margin-bottom: 4px;">III. CATATAN SELAMA PELAKSANAAN UJIAN:</div>
                <div class="kotak-catatan">
                    @if($ba)
                        <div>{!! nl2br(e($ba->catatan_kejadian ?: 'Pelaksanaan ujian berjalan tertib, tertib, dan lancar.')) !!}</div>
                        @if(($ba->naskah_soal_rusak ?? 0) > 0)
                            <div style="margin-top: 6px; font-weight: bold; color: #991b1b; font-size: 9.5pt;">
                                * Catatan Naskah: Teridentifikasi {{ $ba->naskah_soal_rusak }} eksemplar naskah soal rusak dan telah diganti naskah cadangan.
                            </div>
                        @endif
                    @else
                        <em>(Catatan kondisi naskah soal/LJK, ketertiban ruang, penggantian soal rusak, insiden khusus):</em>
                        <br><br><br>
                    @endif
                </div>

                <p style="text-indent: 2.5em; margin-bottom: 20px;">
                    Demikian Berita Acara ini dibuat dengan sesungguhnya untuk dapat dipergunakan sebagaimana mestinya.
                </p>

                <table class="ttd-table">
                    <tr>
                        <td>
                            <div>Mengetahui,</div>
                            <div class="font-bold">Ketua Panitia Ujian,</div>
                            <div style="height: 55px;"></div>
                            <div class="font-bold" style="text-decoration: underline;">
                                {{ $ttdSetting->nama_kepala ?? ($setting->card_principal_name ?? '............................................') }}
                            </div>
                            <div style="font-size: 9.5pt;">
                                NIP. {{ $ttdSetting->nip_kepala ?? ($setting->card_principal_nip ?? '....................................') }}
                            </div>
                        </td>
                        <td>
                            <div>{{ $activePeriode->tempat_cetak ?? 'Tanggamus' }}, {{ $j?->tanggal_ujian ? $j->tanggal_ujian->locale('id')->translatedFormat('d F Y') : date('d F Y') }}</div>
                            <div class="font-bold">Pengawas Ruang 1,</div>
                            <div style="height: 55px;"></div>
                            <div class="font-bold" style="text-decoration: underline;">
                                {{ $pengawas?->pengawas1?->user?->name ?? '............................................' }}
                            </div>
                            <div style="font-size: 9.5pt;">
                                NIP. {{ $pengawas?->pengawas1?->nip ?? '....................................' }}
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        @empty
            <div style="text-align: center; padding: 50px;">
                <h3>Tidak ada data yang dapat dicetak.</h3>
            </div>
        @endforelse
    </div>
</x-print-preview-layout>