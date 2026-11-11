<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Label Meja Siswa (A4 Grid 2x5) - {{ $activePeriode->nama_periode ?? ($periode->nama_periode ?? 'Madrasah') }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #e0e0e0;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 0;
        }
        .kertas-a4 {
            width: 21cm;
            height: 29.7cm;
            background: white;
            padding: 0.6cm;
            box-sizing: border-box;
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: repeat(5, 1fr);
            gap: 0.35cm;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.15);
            page-break-after: always;
        }
        .label-meja-card {
            border: 2px dashed #000;
            border-radius: 6px;
            padding: 6px 8px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: #fff;
            position: relative;
            overflow: hidden;
        }
        .label-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1.5px solid #000;
            padding-bottom: 3px;
            margin-bottom: 4px;
        }
        .label-header-left {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .label-logo {
            width: 22px;
            height: 22px;
            object-fit: contain;
        }
        .label-instansi {
            font-size: 7.5pt;
            font-weight: bold;
            line-height: 1.1;
            text-transform: uppercase;
        }
        .label-ruang-badge {
            background: #FFD43B;
            border: 1px solid #000;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: 900;
            letter-spacing: 0.3px;
        }
        .label-body {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex: 1;
        }
        .label-info {
            flex: 1;
            padding-right: 6px;
        }
        .label-nomor-peserta {
            font-family: 'Courier New', Courier, monospace;
            font-size: 13pt;
            font-weight: 900;
            color: #000;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }
        .label-nama {
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 195px;
            margin-bottom: 1px;
        }
        .label-kelas {
            font-size: 8pt;
            color: #333;
            font-weight: 600;
        }
        .label-qr {
            width: 44px;
            height: 44px;
            border: 1px solid #000;
            padding: 1px;
            background: white;
            flex-shrink: 0;
        }
        .label-qr img {
            width: 100%;
            height: 100%;
            display: block;
        }
        .label-footer {
            border-top: 1px solid #ccc;
            padding-top: 2px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 6.5pt;
            color: #555;
            text-transform: uppercase;
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
            body {
                background: white;
                padding: 0;
            }
            .kertas-a4 {
                box-shadow: none;
                margin: 0;
            }
            .print-toolbar {
                display: none;
            }
        }
    </style>
</head>
<body>
    <button type="button" onclick="window.print()" class="print-toolbar">
        🖨️ Cetak / Simpan PDF
    </button>

    @php
        $periodeObj = $periode ?? $activePeriode;
        $logoPath = null;
        if (!empty($setting?->logo) && \Illuminate\Support\Facades\Storage::disk('public')->exists($setting->logo)) {
            $logoPath = asset('storage/' . $setting->logo);
        }
    @endphp

    @forelse($pesertaList->chunk(10) as $page)
        <div class="kertas-a4">
            @foreach($page as $p)
                <div class="label-meja-card">
                    <!-- Header Kartu Meja -->
                    <div class="label-header">
                        <div class="label-header-left">
                            @if($logoPath)
                                <img src="{{ $logoPath }}" alt="Logo" class="label-logo">
                            @endif
                            <div class="label-instansi">
                                <div>{{ $pengaturan->nama_instansi ?? ($setting->school_name ?? 'MIN 2 TANGGAMUS') }}</div>
                                <div style="font-size: 6pt; color: #555;">{{ $periodeObj->nama_periode ?? 'ASESMEN SUMATIF' }}</div>
                            </div>
                        </div>
                        <div class="label-ruang-badge">
                            {{ $p->ruang?->nama_ruang ?? 'RUANG' }} • MEJA {{ str_pad($p->nomor_kursi ?: 1, 2, '0', STR_PAD_LEFT) }}
                        </div>
                    </div>

                    <!-- Body Kartu Meja -->
                    <div class="label-body">
                        <div class="label-info">
                            <div class="label-nomor-peserta">{{ $p->nomor_peserta }}</div>
                            <div class="label-nama" title="{{ $p->nama_peserta }}">{{ $p->nama_peserta }}</div>
                            <div class="label-kelas">
                                NISN: {{ $p->nisn ?: '-' }} &bull; {{ $p->nama_kelas ?? ($p->schoolClass?->name ?? 'Kelas') }}
                            </div>
                        </div>
                        <div class="label-qr">
                            <img src="{{ $p->qr_data_uri }}" alt="QR">
                        </div>
                    </div>

                    <!-- Footer Kartu Meja -->
                    <div class="label-footer">
                        <span>Harap tempel di sudut kanan atas meja</span>
                        <span>TP {{ $periodeObj->tahun_pelajaran ?? '-' }}</span>
                    </div>
                </div>
            @endforeach

            <!-- Isi grid kosong jika jumlah di halaman terakhir < 10 -->
            @for($empty = $page->count(); $empty < 10; $empty++)
                <div class="label-meja-card" style="border-style: dotted; opacity: 0.25;">
                    <div class="label-header">
                        <span style="font-size: 8pt; font-weight: bold;">(BLANGKO KOSONG)</span>
                    </div>
                    <div style="flex: 1; display: flex; align-items: center; justify-content: center; font-size: 8pt; color: #777;">
                        Cadangan Meja
                    </div>
                </div>
            @endfor
        </div>
    @empty
        <div style="text-align: center; padding: 50px; font-family: sans-serif;">
            <h3>Tidak ada peserta yang terdaftar untuk dicetak label mejanya.</h3>
        </div>
    @endforelse
</body>
</html>
