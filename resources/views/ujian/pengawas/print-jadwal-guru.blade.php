<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Tugas & Jadwal Pengawas - {{ $activePeriode->nama_periode ?? 'Madrasah' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.2cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.35;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 10px;
        }
        .page-break {
            page-break-after: always;
        }
        .judul-surat {
            text-align: center;
            margin: 15px 0 12px 0;
        }
        .judul-surat h3 {
            margin: 0;
            font-size: 13pt;
            text-transform: uppercase;
            text-decoration: underline;
            font-weight: bold;
        }
        .judul-surat .nomor-surat {
            font-size: 10pt;
            margin-top: 2px;
        }
        .paragraf-dasar {
            text-align: justify;
            text-indent: 2.5em;
            margin-bottom: 10px;
        }
        .tabel-identitas {
            margin-left: 20px;
            margin-bottom: 12px;
            border-collapse: collapse;
            font-size: 10.5pt;
        }
        .tabel-identitas td {
            padding: 2.5px 5px;
            vertical-align: top;
        }
        .tabel-jadwal {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 14px;
        }
        .tabel-jadwal th, .tabel-jadwal td {
            border: 1px solid #000;
            padding: 5px 6px;
            font-size: 9.5pt;
        }
        .tabel-jadwal th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
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
        .print-toolbar {
            position: fixed;
            top: 15px;
            right: 15px;
            background: #FFD43B;
            border: 2px solid #000;
            padding: 8px 16px;
            font-family: Arial, sans-serif;
            font-weight: bold;
            font-size: 12px;
            cursor: pointer;
            box-shadow: 2px 2px 0 #000;
            border-radius: 6px;
            z-index: 999;
        }
        @media print {
            .print-toolbar { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <button type="button" onclick="window.print()" class="print-toolbar">
        🖨️ Cetak / Simpan PDF
    </button>

    @forelse($teacherSchedules as $item)
        @php
            $teacher = $item['teacher'];
            $duties = $item['duties'];
        @endphp

        <div class="{{ !$loop->last ? 'page-break' : '' }}">
            <!-- Kop Surat Resmi -->
            <x-kop-surat :useBase64="true" />

            <div class="judul-surat">
                <h3>SURAT TUGAS PENGAWAS UJIAN</h3>
                <div class="nomor-surat">
                    Nomor: B-...../Mi.08.04/PP.01.1/{{ date('m/Y') }}
                </div>
            </div>

            <p class="paragraf-dasar">
                Yang bertanda tangan di bawah ini, Kepala Madrasah Ibtidaiyah Negeri 2 Tanggamus, dengan ini menugaskan kepada Guru yang namanya tersebut di bawah ini:
            </p>

            <table class="tabel-identitas">
                <tr>
                    <td style="width: 140px;">Nama Lengkap</td>
                    <td style="width: 10px;">:</td>
                    <td class="font-bold">{{ $teacher->user?->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td>NIP</td>
                    <td>:</td>
                    <td>{{ $teacher->nip ?: '-' }}</td>
                </tr>
                <tr>
                    <td>Jabatan / Unit Tugas</td>
                    <td>:</td>
                    <td>Guru Madrasah / MIN 2 Tanggamus</td>
                </tr>
            </table>

            <p style="margin-bottom: 6px;">
                Untuk melaksanakan tugas sebagai <strong>Pengawas Ruang Ujian</strong> pada pelaksanaan <strong>{{ strtoupper($activePeriode->nama_periode ?? 'UJIAN MADRASAH') }}</strong> Tahun Pelajaran {{ $activePeriode->tahun_pelajaran ?? '-' }}, dengan rincian jadwal tugas sebagai berikut:
            </p>

            <table class="tabel-jadwal">
                <thead>
                    <tr>
                        <th style="width: 25px;">No</th>
                        <th style="width: 90px;">Hari, Tanggal</th>
                        <th style="width: 40px;">Sesi</th>
                        <th style="width: 80px;">Waktu</th>
                        <th style="width: 75px;">Ruang</th>
                        <th>Mata Pelajaran</th>
                        <th style="width: 110px;">Pasangan Pengawas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($duties as $idx => $d)
                        @php
                            $pasangan = ($d->pengawas_1_id === $teacher->id) 
                                ? $d->pengawas2?->user?->name 
                                : $d->pengawas1?->user?->name;
                        @endphp
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td>
                                {{ $d->jadwal?->tanggal_ujian ? $d->jadwal->tanggal_ujian->locale('id')->translatedFormat('l, d/m/y') : '-' }}
                            </td>
                            <td class="text-center">{{ $d->jadwal?->sesi_ke ?? '1' }}</td>
                            <td class="text-center font-mono">
                                {{ substr($d->jadwal?->jam_mulai ?? '', 0, 5) }} - {{ substr($d->jadwal?->jam_selesai ?? '', 0, 5) }}
                            </td>
                            <td class="text-center font-bold">{{ $d->ruang?->nama_ruang ?? '-' }}</td>
                            <td class="font-bold">{{ $d->jadwal?->nama_mapel ?? '-' }}</td>
                            <td class="text-center">{{ $pasangan ?: '(Pengawas Tunggal)' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center" style="padding: 12px; font-style: italic;">
                                Belum ada plotting jadwal mengawas untuk guru yang bersangkutan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <p style="margin-top: 10px; margin-bottom: 20px;">
                Demikian surat tugas ini diberikan kepada yang bersangkutan untuk dapat dilaksanakan dengan sebaik-baiknya dan penuh rasa tanggung jawab.
            </p>

            <table class="ttd-table">
                <tr>
                    <td>
                        <div>Menyatakan Siap Bertugas,</div>
                        <div class="font-bold">Guru Pengawas,</div>
                        <div style="height: 55px;"></div>
                        <div class="font-bold" style="text-decoration: underline;">
                            {{ $teacher->user?->name }}
                        </div>
                        <div style="font-size: 9.5pt;">
                            NIP. {{ $teacher->nip ?: '....................................' }}
                        </div>
                    </td>
                    <td>
                        <div>{{ $activePeriode->tempat_cetak ?? 'Tanggamus' }}, {{ date('d F Y') }}</div>
                        <div class="font-bold">Kepala Madrasah,</div>
                        <div style="height: 55px;"></div>
                        <div class="font-bold" style="text-decoration: underline;">
                            {{ $ttdSetting->nama_kepala ?? ($setting->card_principal_name ?? 'Sipulloh, M.Pd') }}
                        </div>
                        <div style="font-size: 9.5pt;">
                            NIP. {{ $ttdSetting->nip_kepala ?? ($setting->card_principal_nip ?? '197005272007011022') }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    @empty
        <div style="text-align: center; padding: 50px; font-family: sans-serif;">
            <h3>Tidak ada jadwal pengawas yang dapat dicetak.</h3>
        </div>
    @endforelse
</body>
</html>
