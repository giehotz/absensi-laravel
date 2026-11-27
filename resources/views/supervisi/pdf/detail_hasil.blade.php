<x-print-preview-layout title="Laporan Hasil Supervisi Guru" paperSize="A4" orientation="portrait">
    @php
        $nilaiAkhirDisplay = (float) ($ringkasanSummary['nilai_akhir'] ?? $summary['nilai_akhir'] ?? 0);
        $kategoriDisplay = $ringkasanSummary['ketercapaian'] ?? ($summary['ketercapaian'] ?? 'Kurang');
        $totalSkorDisplay = (int) ($ringkasanSummary['total_skor'] ?? $summary['total_skor'] ?? 0);
        $skorMaksDisplay = (int) ($ringkasanSummary['skor_maksimal'] ?? $summary['skor_maksimal'] ?? 0);

        $kategoriBadgeColors = match ($kategoriDisplay) {
            'Baik Sekali' => ['bg' => '#198754', 'color' => '#ffffff'],
            'Baik' => ['bg' => '#0891b2', 'color' => '#ffffff'],
            'Cukup' => ['bg' => '#eab308', 'color' => '#111827'],
            default => ['bg' => '#dc3545', 'color' => '#ffffff'],
        };
    @endphp

    <!-- Halaman 1: Kop Surat, Identitas & Ringkasan Nilai Supervisi -->
    <x-kop-surat :useBase64="true" />

    <div style="text-align: center; margin-bottom: 12px;">
        <h1 style="font-size: 13pt; font-weight: bold; text-transform: uppercase; margin: 0;">Laporan Hasil Supervisi Pembelajaran</h1>
        <p style="font-size: 9.5pt; margin: 2px 0 0 0;">Tahun Ajaran {{ $jadwal->academicYear?->name }} — Semester {{ ucfirst($jadwal->academicYear?->semester ?? '') }}</p>
    </div>

    <table style="width: 100%; font-size: 9.5pt; margin-bottom: 12px; border-collapse: collapse;">
        <tr>
            <td style="width: 24%; font-weight: bold; padding: 2px 0;">Nama Guru</td>
            <td style="width: 28%; padding: 2px 0;">: {{ $jadwal->teacher?->user?->name }}</td>
            <td style="width: 22%; font-weight: bold; padding: 2px 0;">Kelas / Mapel</td>
            <td style="padding: 2px 0;">: {{ $jadwal->schoolClass?->name ?? '-' }} / {{ $jadwal->subject?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; padding: 2px 0;">NIP</td>
            <td style="padding: 2px 0;">: {{ $jadwal->teacher?->nip ?: '-' }}</td>
            <td style="font-weight: bold; padding: 2px 0;">Tanggal Observasi</td>
            <td style="padding: 2px 0;">: {{ $jadwal->tanggal?->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; padding: 2px 0;">Supervisor Penilai</td>
            <td style="padding: 2px 0;">: {{ $jadwal->supervisor_display_name }}</td>
            <td style="font-weight: bold; padding: 2px 0;">Nilai Akhir</td>
            <td style="padding: 2px 0;">: <strong>{{ number_format($nilaiAkhirDisplay, 2) }}% ({{ $kategoriDisplay }})</strong></td>
        </tr>
    </table>

    <div style="margin-bottom: 14px; page-break-inside: avoid; break-inside: avoid;">
        <div style="background-color: #EAEAEA; padding: 5px 10px; font-weight: bold; font-size: 10pt; border-left: 4px solid #0b5ed7; margin-bottom: 8px; color: #111827;">
            Hasil Penilaian: Ringkasan Nilai Supervisi
        </div>

        <table style="width: 100%; border-collapse: collapse; font-size: 9pt; margin-bottom: 12px;">
            <thead>
                <tr style="background-color: #f8fafc;">
                    <th style="border: 1px solid #000; padding: 5px 6px; width: 6%; text-align: center;">No</th>
                    <th style="border: 1px solid #000; padding: 5px 8px; text-align: left;">Tahap &amp; Komponen Penilaian</th>
                    <th style="border: 1px solid #000; padding: 5px 8px; width: 17%; text-align: center;">Skor Diperoleh</th>
                    <th style="border: 1px solid #000; padding: 5px 8px; width: 17%; text-align: center;">Persentase (%)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ringkasanKomponen as $row)
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px 6px; text-align: center;">{{ $row['tahap_no'] }}</td>
                        <td style="border: 1px solid #000; padding: 5px 8px;">{{ $row['label'] }}</td>
                        <td style="border: 1px solid #000; padding: 5px 8px; text-align: center;">{{ $row['skor'] }}</td>
                        <td style="border: 1px solid #000; padding: 5px 8px; text-align: center;">{{ number_format((float) $row['persentase'], 2) }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="border: 1px solid #000; padding: 6px; text-align: center;">Belum ada komponen instrumen penilaian.</td>
                    </tr>
                @endforelse
                <tr style="font-weight: bold; background-color: #f1f5f9;">
                    <td style="border: 1px solid #000; padding: 5px 6px;"></td>
                    <td style="border: 1px solid #000; padding: 5px 8px; text-align: center;">Total</td>
                    <td style="border: 1px solid #000; padding: 5px 8px; text-align: center;">{{ $totalSkorDisplay }}</td>
                    <td style="border: 1px solid #000; padding: 5px 8px; text-align: center;">{{ number_format($nilaiAkhirDisplay, 2) }}%</td>
                </tr>
            </tbody>
        </table>

        <table style="width: 100%; border-collapse: collapse; page-break-inside: avoid; break-inside: avoid;">
            <tr>
                <td style="width: 55%; vertical-align: top; padding-right: 16px;">
                    <div style="font-size: 10pt; font-weight: bold; margin-bottom: 6px; color: #111827;">Hasil Akhir</div>
                    <div style="font-size: 9pt; margin-bottom: 8px; line-height: 1.6;">
                        Nilai Akhir:
                        <span style="display: inline-block; background-color: #0b5ed7; color: #ffffff; font-weight: bold; font-size: 12pt; padding: 3px 10px; border-radius: 4px; vertical-align: middle; margin: 0 6px 0 3px;">
                            {{ number_format($nilaiAkhirDisplay, 2) }}%
                        </span>
                        Kategori:
                        <span style="display: inline-block; background-color: {{ $kategoriBadgeColors['bg'] }}; color: {{ $kategoriBadgeColors['color'] }}; font-weight: bold; font-size: 11.5pt; padding: 3px 10px; border-radius: 4px; vertical-align: middle; margin-left: 3px;">
                            {{ $kategoriDisplay }}
                        </span>
                    </div>

                    <div style="background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 5px; padding: 7px 10px; font-size: 8.5pt; line-height: 1.45; color: #1e293b;">
                        <strong>Rumus Perhitungan:</strong><br>
                        Nilai Akhir = (Total Skor / Skor Maksimal) &times; 100<br>
                        = ({{ $totalSkorDisplay }} / {{ $skorMaksDisplay }}) &times; 100 = <strong>{{ number_format($nilaiAkhirDisplay, 2) }}%</strong>
                    </div>
                </td>

                <td style="width: 45%; vertical-align: top;">
                    <div style="font-size: 10pt; font-weight: bold; margin-bottom: 6px; color: #111827;">Kategori Penilaian</div>
                    <table style="width: 100%; border-collapse: collapse; font-size: 8.8pt;">
                        @foreach($kategoriSkorLegend as $item)
                            <tr>
                                <td style="padding: 2.5px 4px; width: 65%;">{{ $item['label'] }}</td>
                                <td style="padding: 2.5px 4px; text-align: center; width: 35%;">
                                    <span style="display: inline-block; min-width: 58px; background-color: {{ $item['bg'] }}; color: {{ $item['color'] }}; font-weight: bold; font-size: 8pt; padding: 2px 7px; border-radius: 4px;">
                                        {{ $item['range'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </td>
            </tr>
        </table>
    </div>

    @if($jadwal->hasils->isNotEmpty())
        <div class="page-break"></div>

        <!-- Halaman 2+: Detail Penilaian Per Komponen -->
        @foreach($jadwal->hasils as $hasil)
            <div style="margin-bottom: 12px; page-break-inside: avoid;">
                <div style="font-weight: bold; font-size: 10pt; background: #e2e8f0; padding: 4px 8px; border: 1px solid #000;">
                    {{ $hasil->jenisPenilaian?->nama }} — Skor: {{ $hasil->total_skor }}/{{ $hasil->skor_maksimal }} ({{ number_format((float) $hasil->nilai_akhir, 2) }}% &middot; {{ $hasil->ketercapaian }})
                </div>
                <table style="width: 100%; border-collapse: collapse; font-size: 9pt;">
                    <thead>
                        <tr style="background: #f8fafc;">
                            <th style="border: 1px solid #000; padding: 4px; width: 32px;">No</th>
                            <th style="border: 1px solid #000; padding: 4px; text-align: left;">Aspek Penilaian</th>
                            <th style="border: 1px solid #000; padding: 4px; width: 55px;">Skor</th>
                            <th style="border: 1px solid #000; padding: 4px; text-align: left;">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($hasil->detailHasils as $idx => $detail)
                            <tr>
                                <td style="border: 1px solid #000; padding: 4px; text-align: center; vertical-align: top;">{{ $idx + 1 }}</td>
                                <td style="border: 1px solid #000; padding: 4px; vertical-align: top;">
                                    <div style="font-weight: bold;">{{ $detail->aspek?->nama_aspek }}</div>
                                    @if($detail->aspek?->fokus_perilaku)
                                        <div style="font-size: 8pt; color: #475569; margin-top: 1px;">{{ $detail->aspek->fokus_perilaku }}</div>
                                    @endif
                                </td>
                                <td style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold; vertical-align: top;">{{ $detail->skor }}</td>
                                <td style="border: 1px solid #000; padding: 4px; vertical-align: top;">{{ $detail->catatan ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($hasil->rekomendasi)
                    <div style="border: 1px solid #000; border-top: none; padding: 5px 8px; font-size: 9pt;"><strong>Rekomendasi:</strong> {{ $hasil->rekomendasi }}</div>
                @endif
            </div>
        @endforeach
    @endif

    @include('supervisi.pdf.partials.signature_blocks')
</x-print-preview-layout>
