<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Denah Duduk - {{ $ruang->nama_ruang }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0.8cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            margin: 0;
            padding: 10px;
            color: #000;
            background: #fff;
        }
        .judul-denah {
            text-align: center;
            margin: 8px 0 12px 0;
        }
        .judul-denah h3 {
            margin: 0;
            font-size: 13pt;
            text-transform: uppercase;
            text-decoration: underline;
            font-weight: 900;
        }
        .judul-denah .sub-judul {
            font-size: 10.5pt;
            font-weight: bold;
            margin-top: 3px;
        }
        .papan-pengawas-box {
            width: 70%;
            margin: 0 auto 12px auto;
            border: 2px solid #000;
            background-color: #f2f2f2;
            text-align: center;
            padding: 6px;
            font-weight: bold;
            font-size: 10pt;
            letter-spacing: 1px;
            border-radius: 4px;
        }
        .grid-denah {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-bottom: 12px;
        }
        .kotak-meja {
            border: 1.5px solid #000;
            border-radius: 5px;
            padding: 5px 6px;
            min-height: 52px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background-color: #fff;
            text-align: center;
        }
        .kotak-meja.terisi {
            background-color: #fff;
            box-shadow: 1px 1px 0 #000;
        }
        .kotak-meja.kosong {
            background-color: #fafafa;
            border-style: dashed;
            color: #888;
        }
        .meja-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #ddd;
            padding-bottom: 2px;
            margin-bottom: 2px;
        }
        .meja-nomor {
            font-size: 7.5pt;
            font-weight: bold;
            background: #FFD43B;
            border: 0.5px solid #000;
            padding: 0 4px;
            border-radius: 2px;
        }
        .meja-kelas {
            font-size: 7pt;
            color: #444;
            font-weight: 600;
        }
        .meja-no-peserta {
            font-family: 'Courier New', Courier, monospace;
            font-size: 8.5pt;
            font-weight: bold;
            color: #000;
        }
        .meja-nama {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .arah-pintu-bar {
            display: flex;
            justify-content: space-between;
            font-size: 8pt;
            color: #555;
            margin-bottom: 10px;
            padding: 0 5px;
        }
        .ttd-footer {
            width: 100%;
            margin-top: 15px;
            border-collapse: collapse;
            font-size: 9.5pt;
        }
        .ttd-footer td {
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

    <!-- Kop Surat Resmi -->
    <x-kop-surat :useBase64="true" />

    <div class="judul-denah">
        <h3>DENAH TEMPAT DUDUK PESERTA UJIAN</h3>
        <div class="sub-judul">
            {{ $ruang->nama_ruang }} &bull; {{ $activePeriode->nama_periode ?? 'UJIAN MADRASAH' }} 
            @if($activePeriode && $activePeriode->tahun_pelajaran)
                (TP {{ $activePeriode->tahun_pelajaran }})
            @endif
        </div>
    </div>

    <!-- Papan Tulis & Meja Pengawas -->
    <div class="papan-pengawas-box">
        ═══════ [ DEPAN : PAPAN TULIS & MEJA PENGAWAS ] ═══════
    </div>

    <div class="arah-pintu-bar">
        <span>⬅️ Arah Pintu Masuk</span>
        <span>Arah Jendela ➡️</span>
    </div>

    <!-- Matriks 4 Kolom Meja -->
    <div class="grid-denah">
        @for($kursi = 1; $kursi <= ($baris * $kolom); $kursi++)
            @php
                $p = $mejaMap[$kursi] ?? null;
            @endphp

            @if($kursi <= $ruang->kapasitas_maksimal)
                <div class="kotak-meja {{ $p ? 'terisi' : 'kosong' }}">
                    <div class="meja-header">
                        <span class="meja-nomor">MEJA {{ str_pad($kursi, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="meja-kelas">{{ $p?->nama_kelas ?? ($p?->schoolClass?->name ?? '-') }}</span>
                    </div>
                    @if($p)
                        <div class="meja-no-peserta">{{ $p->nomor_peserta }}</div>
                        <div class="meja-nama" title="{{ $p->nama_peserta }}">{{ $p->nama_peserta }}</div>
                    @else
                        <div style="font-size: 8pt; color: #888; padding: 4px 0;">(KOSONG)</div>
                    @endif
                </div>
            @else
                <div class="kotak-meja kosong" style="visibility: hidden;"></div>
            @endif
        @endfor
    </div>

    <div style="font-size: 8.5pt; color: #444; border-top: 1px solid #ccc; padding-top: 4px; text-align: center;">
        Kapasitas Ruang: {{ $ruang->kapasitas_maksimal }} Meja &bull; Peserta Terdaftar: {{ $pesertas->count() }} Siswa &bull; Meja Kosong: {{ max(0, $ruang->kapasitas_maksimal - $pesertas->count()) }} Meja
    </div>

    <table class="ttd-footer">
        <tr>
            <td>
                <div>Mengetahui,</div>
                <div class="font-bold">Kepala Madrasah,</div>
                <div style="height: 48px;"></div>
                <div class="font-bold" style="text-decoration: underline;">
                    {{ $ttdSetting->nama_kepala ?? ($setting->card_principal_name ?? 'Sipulloh, M.Pd') }}
                </div>
                <div style="font-size: 8.5pt;">
                    NIP. {{ $ttdSetting->nip_kepala ?? ($setting->card_principal_nip ?? '197005272007011022') }}
                </div>
            </td>
            <td>
                <div>{{ $activePeriode->tempat_cetak ?? 'Tanggamus' }}, {{ date('d F Y') }}</div>
                <div class="font-bold">Ketua Panitia Ujian,</div>
                <div style="height: 48px;"></div>
                <div class="font-bold" style="text-decoration: underline;">
                    ............................................................
                </div>
                <div style="font-size: 8.5pt;">
                    NIP. ...................................................
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
