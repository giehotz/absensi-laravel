<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Analisis Minggu Efektif - TA {{ $selectedYear }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.2cm 1.5cm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            line-height: 1.35;
            font-size: 10.5pt;
            background: #fff;
            margin: 0;
            padding: 0;
        }

        .no-print-toolbar {
            background-color: #f8fafc;
            border-bottom: 2px solid #000;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            border: 2px solid #000;
            box-shadow: 2px 2px 0px 0px #000;
            transition: all 0.15s;
        }
        .btn:hover {
            transform: translate(-1px, -1px);
            box-shadow: 3px 3px 0px 0px #000;
        }
        .btn-print {
            background-color: #FFD43B;
            color: #000;
        }
        .btn-back {
            background-color: #fff;
            color: #000;
        }

        @media print {
            .no-print-toolbar {
                display: none !important;
            }
            body {
                padding: 0;
            }
            .page-break {
                page-break-before: always;
            }
        }

        .document-title {
            text-align: center;
            margin: 6px 0 16px 0;
        }

        .document-title h2 {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
            margin: 0;
            padding: 0;
        }

        .document-title p {
            font-size: 11pt;
            font-weight: bold;
            margin: 3px 0 0 0;
            text-transform: uppercase;
        }

        .meta-info-table {
            width: 100%;
            margin-bottom: 14px;
            border-collapse: collapse;
            font-size: 10.5pt;
        }

        .meta-info-table td {
            padding: 2px 4px;
            vertical-align: top;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 10pt;
        }

        .data-table th, 
        .data-table td {
            border: 1px solid #000;
            padding: 5px 6px;
        }

        .data-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9.5pt;
        }

        .section-header {
            font-size: 11pt;
            font-weight: bold;
            margin: 12px 0 6px 0;
            text-transform: uppercase;
        }

        .calc-box {
            border: 1px solid #000;
            padding: 8px 12px;
            margin-bottom: 14px;
            font-size: 10pt;
            background-color: #fafafa;
        }

        .calc-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .signatures-container {
            width: 100%;
            margin-top: 24px;
            font-size: 10.5pt;
            page-break-inside: avoid;
        }

        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }

        .signatures-table td {
            border: none;
            vertical-align: top;
            padding: 0;
        }
    </style>
</head>
<body>

    <!-- Toolbar Web Navigasi & Cetak -->
    <div class="no-print-toolbar">
        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="javascript:history.back()" class="btn btn-back">
                &larr; Kembali
            </a>
            <span style="font-size: 13px; font-weight: bold; color: #334155;">
                Pratinjau Cetak: Analisis Minggu Efektif (TA {{ $selectedYear }})
            </span>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <select onchange="window.location.search = this.value" style="padding: 6px 10px; font-size: 12px; font-weight: bold; border: 2px solid #000;">
                <option value="{{ http_build_query(array_merge(request()->query(), ['print_semester' => 'all'])) }}" {{ $printSemester === 'all' ? 'selected' : '' }}>Cetak Semua Semester</option>
                <option value="{{ http_build_query(array_merge(request()->query(), ['print_semester' => 'ganjil'])) }}" {{ $printSemester === 'ganjil' ? 'selected' : '' }}>Semester Gasal Saja</option>
                <option value="{{ http_build_query(array_merge(request()->query(), ['print_semester' => 'genap'])) }}" {{ $printSemester === 'genap' ? 'selected' : '' }}>Semester Genap Saja</option>
            </select>
            <button onclick="window.print()" class="btn btn-print">
                🖨️ Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <!-- Official Kop Surat -->
    <x-kop-surat :useBase64="true" />

    <!-- Document Title -->
    <div class="document-title">
        <h2>ANALISIS MINGGU EFEKTIF DAN DISTRIBUSI ALOKASI WAKTU</h2>
        <p>TAHUN PELAJARAN {{ $selectedYear }}</p>
    </div>

    <!-- Meta Information Table -->
    <table class="meta-info-table">
        <tr>
            <td style="width: 18%; font-weight: bold;">Satuan Pendidikan</td>
            <td style="width: 2%;">:</td>
            <td style="width: 40%;">{{ $setting->school_name ?? config('app.name', 'Madrasah') }}</td>
            <td style="width: 16%; font-weight: bold;">Mata Pelajaran</td>
            <td style="width: 2%;">:</td>
            <td style="width: 22%;">{{ $subject?->name ?? 'Semua Mapel' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Kelas / Rombel</td>
            <td>:</td>
            <td>{{ $selectedClass?->name ?? 'Semua Kelas' }}</td>
            <td style="font-weight: bold;">Alokasi Waktu</td>
            <td>:</td>
            <td>{{ $params['jp_per_week'] }} JP / Minggu</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Guru Pengampu</td>
            <td>:</td>
            <td>{{ $teacher?->user?->name ?? '....................................' }}</td>
            <td style="font-weight: bold;">Semester</td>
            <td>:</td>
            <td>
                @if($printSemester === 'ganjil')
                    Gasal (1)
                @elseif($printSemester === 'genap')
                    Genap (2)
                @else
                    Gasal &amp; Genap
                @endif
            </td>
        </tr>
    </table>

    @php $semCount = 0; @endphp
    @foreach($semesters as $semKey => $semItem)
        @php
            $semCount++;
            $analysis = $semItem['analysis'];
            $hours = $semItem['hours'];
        @endphp

        @if($semCount > 1)
            <div class="page-break" style="margin-top: 20px;"></div>
            <!-- Ulang Kop Surat jika halaman berikutnya -->
            <x-kop-surat :useBase64="true" />
            <div class="document-title" style="margin-bottom: 12px;">
                <h2>ANALISIS MINGGU EFEKTIF (LANJUTAN)</h2>
                <p>SEMESTER {{ strtoupper($semItem['label']) }} - TAHUN PELAJARAN {{ $selectedYear }}</p>
            </div>
        @endif

        <div class="section-header">
            I. REKAPITULASI MINGGU EFEKTIF SEMESTER {{ strtoupper($semItem['label']) }}
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 35px;">NO</th>
                    <th style="width: 140px;">BULAN</th>
                    <th style="width: 90px;">JUMLAH MINGGU</th>
                    <th style="width: 100px;">MINGGU EFEKTIF</th>
                    <th style="width: 110px;">MINGGU TIDAK EFEKTIF</th>
                    <th>KETERANGAN / KEGIATAN</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach($analysis['months'] as $m)
                    <tr>
                        <td style="text-align: center;">{{ $no++ }}</td>
                        <td style="font-weight: bold;">{{ $m['month_name'] }}</td>
                        <td style="text-align: center;">{{ $m['total_weeks'] }}</td>
                        <td style="text-align: center; font-weight: bold;">{{ $m['effective_weeks'] }}</td>
                        <td style="text-align: center; font-weight: bold;">{{ $m['non_effective_weeks'] }}</td>
                        <td>
                            @if(!empty($m['reasons']))
                                {{ implode(', ', $m['reasons']) }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color: #f2f2f2; font-weight: bold;">
                    <td colspan="2" style="text-align: center; text-transform: uppercase;">JUMLAH TOTAL</td>
                    <td style="text-align: center;">{{ $analysis['total_weeks'] }}</td>
                    <td style="text-align: center;">{{ $analysis['total_effective_weeks'] }}</td>
                    <td style="text-align: center;">{{ $analysis['total_non_effective_weeks'] }}</td>
                    <td style="font-style: italic; font-size: 9pt;">
                        Hari Sekolah: {{ $params['school_days'] }} Hari / Minggu (Min: {{ $params['min_effective_days'] }} hari aktif)
                    </td>
                </tr>
            </tfoot>
        </table>

        <div class="section-header">
            II. DISTRIBUSI ALOKASI WAKTU PEMBELAJARAN
        </div>

        <div class="calc-box">
            <table style="width: 100%; border-collapse: collapse; font-size: 10pt;">
                <tr>
                    <td style="width: 50%; vertical-align: top; padding-right: 15px;">
                        <strong>A. Perhitungan Jam KBM Tatap Muka:</strong>
                        <table style="width: 100%; margin-top: 4px;">
                            <tr>
                                <td style="width: 65%;">1. Jumlah Minggu Efektif</td>
                                <td style="width: 5%;">:</td>
                                <td><strong>{{ $analysis['total_effective_weeks'] }}</strong> Minggu</td>
                            </tr>
                            <tr>
                                <td>2. Jam Pelajaran per Minggu</td>
                                <td>:</td>
                                <td><strong>{{ $params['jp_per_week'] }}</strong> JP</td>
                            </tr>
                            <tr style="border-top: 1px dashed #666;">
                                <td><strong>3. Total Jam Tatap Muka (1 &times; 2)</strong></td>
                                <td>:</td>
                                <td><strong>{{ $hours['teaching_hours'] }}</strong> JP</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 50%; vertical-align: top; border-left: 1px solid #ccc; padding-left: 15px;">
                        <strong>B. Distribusi Jam Pembelajaran:</strong>
                        <table style="width: 100%; margin-top: 4px;">
                            <tr>
                                <td style="width: 65%;">1. Materi Pokok (KD/Tatap Muka)</td>
                                <td style="width: 5%;">:</td>
                                <td><strong>{{ $hours['material_hours'] }}</strong> JP</td>
                            </tr>
                            <tr>
                                <td>2. Penilaian / Asesmen Sumatif</td>
                                <td>:</td>
                                <td><strong>{{ $hours['exam_hours'] }}</strong> JP</td>
                            </tr>
                            <tr>
                                <td>3. Jam Cadangan</td>
                                <td>:</td>
                                <td><strong>{{ $hours['reserve_hours'] }}</strong> JP</td>
                            </tr>
                            <tr style="border-top: 1px dashed #666;">
                                <td><strong>Total Alokasi Keseluruhan</strong></td>
                                <td>:</td>
                                <td><strong>{{ $hours['material_hours'] + $hours['exam_hours'] + $hours['reserve_hours'] }}</strong> JP</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    @endforeach

    <!-- Signatures Section -->
    <div class="signatures-container">
        <table class="signatures-table">
            <tr>
                <td style="width: 55%; text-align: left;">
                    Mengetahui,<br>
                    Kepala {{ $setting->school_name ?? config('app.name', 'Madrasah') }}
                    <br><br><br><br><br>
                    <strong><u>{{ $setting?->card_principal_name ?? $setting?->headmaster_name ?? '................................................' }}</u></strong><br>
                    NIP. {{ $setting?->card_principal_nip ?? $setting?->headmaster_nip ?? '................................................' }}
                </td>
                <td style="width: 45%; text-align: left;">
                    {{ $setting->city ?? 'Jepara' }}, {{ now()->translatedFormat('d F Y') }}<br>
                    Guru Mata Pelajaran,
                    <br><br><br><br><br>
                    <strong><u>{{ $teacher?->user?->name ?? '................................................' }}</u></strong><br>
                    NIP. {{ $teacher?->nip ?? '................................................' }}
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
