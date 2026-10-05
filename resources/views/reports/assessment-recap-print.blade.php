<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Rekapitulasi {{ $package->type_label }} - {{ $package->schoolClass->name }} - {{ $package->subject->name }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1.2cm 1.5cm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            line-height: 1.3;
            font-size: 10pt;
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

        .btn-print {
            background-color: #ffd43b;
            color: #000;
        }

        .btn-print:hover {
            background-color: #fcc419;
            transform: translate(1px, 1px);
            box-shadow: 1px 1px 0px 0px #000;
        }

        .btn-close {
            background-color: #fff;
            color: #000;
        }

        .document-container {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
        }

        .title-section {
            text-align: center;
            margin-top: 10px;
            margin-bottom: 14px;
        }

        .title-section h2 {
            font-size: 13pt;
            font-weight: bold;
            margin: 0 0 3px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .title-section p {
            margin: 0;
            font-size: 10.5pt;
            font-weight: normal;
        }

        .info-table {
            width: 100%;
            margin-bottom: 12px;
            font-size: 9.5pt;
        }

        .info-table td {
            padding: 2px 4px;
            vertical-align: top;
        }

        .recap-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-bottom: 18px;
        }

        .recap-table th, 
        .recap-table td {
            border: 1px solid #000;
            padding: 4px 3px;
        }

        .recap-table th {
            background-color: #f1f5f9;
            text-align: center;
            font-weight: bold;
            vertical-align: middle;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        .signature-section {
            width: 100%;
            margin-top: 24px;
            page-break-inside: avoid;
        }

        .signature-table {
            width: 100%;
            border: none;
            font-size: 10pt;
        }

        .signature-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }

        .signature-space {
            height: 60px;
        }

        @media print {
            .no-print-toolbar {
                display: none !important;
            }
            body {
                background: transparent;
            }
        }
    </style>
</head>
<body>

    <!-- Print Action Bar -->
    <div class="no-print-toolbar">
        <div>
            <span style="font-weight: bold; font-size: 14px;">Pratinjau Cetak Lembar Rekapitulasi Nilai Sumatif</span>
            <span style="font-size: 12px; color: #64748b; margin-left: 8px;">(Kertas A4 Landscape)</span>
        </div>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" class="btn btn-print">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Cetak Dokumen
            </button>
            <button onclick="window.close()" class="btn btn-close">Tutup Tab</button>
        </div>
    </div>

    <div class="document-container">
        <!-- Official Kop Surat -->
        <x-kop-surat :useBase64="true" />

        <!-- Document Title -->
        <div class="title-section">
            <h2>REKAPITULASI PENILAIAN SUMATIF SISWA</h2>
            <div style="font-size: 11pt; font-weight: bold; margin-top: 2px;">{{ strtoupper($package->type_label) }}</div>
            <p>TAHUN AJARAN {{ strtoupper($package->academicYear->name) }} (SEMESTER {{ strtoupper($package->academicYear->semester) }})</p>
        </div>

        <!-- Meta Info -->
        <table class="info-table">
            <tr>
                <td style="width: 14%;"><strong>Mata Pelajaran</strong></td>
                <td style="width: 1%;">:</td>
                <td style="width: 35%;">{{ $package->subject->name }}</td>
                <td style="width: 14%;"><strong>Kelas / Rombel</strong></td>
                <td style="width: 1%;">:</td>
                <td style="width: 35%;">{{ $package->schoolClass->name }}</td>
            </tr>
            <tr>
                <td><strong>Guru Pengampu</strong></td>
                <td>:</td>
                <td>{{ $package->teacher->user?->name ?? '-' }} (NIP: {{ $package->teacher->nip ?? '-' }})</td>
                <td><strong>Standar KKTP</strong></td>
                <td>:</td>
                <td>{{ $package->kktp_default }} (Kriteria Ketercapaian Tujuan Pembelajaran)</td>
            </tr>
        </table>

        <!-- Table of Scores -->
        <table class="recap-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 25px;">No</th>
                    <th rowspan="2" style="width: 65px;">NIS</th>
                    <th rowspan="2">Nama Siswa</th>
                    <th colspan="{{ $activeAssessments->count() }}">{{ $package->isSts() ? 'Asesmen Tengah Semester (STS)' : ($package->isSas() ? 'Asesmen Akhir Semester (SAS)' : 'Lembar Sumatif (SUM)') }}</th>
                    <th rowspan="2" style="width: 48px;">Rata-Rata</th>
                    <th rowspan="2" style="width: 55px;">Status</th>
                </tr>
                <tr>
                    @foreach($activeAssessments as $asm)
                        <th style="min-width: 28px;">{{ $asm->sheet_name ?: 'S' . $asm->sheet_number }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($students as $idx => $student)
                    @php
                        $stat = $studentStats[$student->id] ?? ['average' => null, 'status' => '-'];
                    @endphp
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="text-center">{{ $student->nis ?? '-' }}</td>
                        <td>{{ $student->user?->name ?? '-' }}</td>
                        @foreach($activeAssessments as $asm)
                            @php
                                $score = $asm->scores->firstWhere('student_id', $student->id)?->score;
                            @endphp
                            <td class="text-center">
                                {{ $score !== null ? (float)$score : '-' }}
                            </td>
                        @endforeach
                        <td class="text-center font-bold">
                            {{ $stat['average'] !== null ? $stat['average'] : '-' }}
                        </td>
                        <td class="text-center font-bold">
                            {{ $stat['status'] }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $activeAssessments->count() + 5 }}" class="text-center">Belum ada siswa terdaftar pada kelas ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Signature Section -->
        <div class="signature-section">
            <table class="signature-table">
                <tr>
                    <td style="width: 60%;">
                        Mengetahui,<br>
                        Kepala Sekolah<br>
                        <div class="signature-space"></div>
                        <strong>{{ $setting->headmaster_name ?? '............................................' }}</strong><br>
                        NIP: {{ $setting->headmaster_nip ?? '....................................' }}
                    </td>
                    <td style="width: 40%; text-align: left;">
                        {{ $setting->city ?? 'Tempat' }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                        Guru Mata Pelajaran,<br>
                        <div class="signature-space"></div>
                        <strong>{{ $package->teacher->user?->name ?? '............................................' }}</strong><br>
                        NIP: {{ $package->teacher->nip ?? '-' }}
                    </td>
                </tr>
            </table>
        </div>
    </div>

</body>
</html>
