<x-print-preview-layout :title="'Portofolio Supervisi: ' . ($teacher->user?->name ?? 'Guru')" paperSize="A4" orientation="portrait">
    <x-slot:actions>
        <form method="GET" class="flex flex-wrap items-center gap-1.5">
            <select name="academic_year_id" aria-label="Pilih Tahun Ajaran" class="px-2 py-1 text-[11px] font-bold border-2 border-black rounded bg-white">
                @foreach($academicYears as $yr)
                    <option value="{{ $yr->id }}" {{ $referenceYear && (int) $referenceYear->id === (int) $yr->id ? 'selected' : '' }}>
                        {{ $yr->name }} ({{ ucfirst($yr->semester) }})
                    </option>
                @endforeach
            </select>
            <select name="scope" aria-label="Cakupan Periode" class="px-2 py-1 text-[11px] font-bold border-2 border-black rounded bg-white">
                <option value="semester" {{ $scope === 'semester' ? 'selected' : '' }}>1 Semester</option>
                <option value="tahun" {{ $scope === 'tahun' ? 'selected' : '' }}>1 Tahun Pelajaran</option>
            </select>
            <button type="submit" class="px-2.5 py-1 bg-[#FFD43B] text-black text-[11px] font-black rounded border-2 border-black cursor-pointer">
                Terapkan
            </button>
        </form>
    </x-slot:actions>

    @php
        $badgeColorMap = [
            'Baik Sekali' => ['bg' => '#198754', 'color' => '#ffffff'],
            'Baik' => ['bg' => '#0891b2', 'color' => '#ffffff'],
            'Cukup' => ['bg' => '#eab308', 'color' => '#111827'],
            'Kurang' => ['bg' => '#dc3545', 'color' => '#ffffff'],
        ];
        $predColor = $badgeColorMap[$ketercapaian] ?? ['bg' => '#64748b', 'color' => '#ffffff'];
    @endphp

    <!-- Halaman 1: Kop Surat, Profil Guru, Rekapitulasi 14/14 Form -->
    <x-kop-surat :useBase64="true" />

    <div style="text-align: center; margin-bottom: 12px;">
        <h1 style="font-size: 13pt; font-weight: bold; text-transform: uppercase; margin: 0;">Portofolio Hasil Supervisi Guru (14 Instrumen)</h1>
        <p style="font-size: 9.5pt; margin: 2px 0 0 0;">{{ $periodLabel }}</p>
    </div>

    <table style="width: 100%; font-size: 9.2pt; margin-bottom: 12px; border-collapse: collapse;">
        <tr>
            <td style="width: 22%; font-weight: bold; padding: 2px 0;">Nama Guru</td>
            <td style="width: 30%; padding: 2px 0;">: {{ $teacher->user?->name }}</td>
            <td style="width: 22%; font-weight: bold; padding: 2px 0;">Ketuntasan Form</td>
            <td style="padding: 2px 0;">: <strong>{{ $doneCount }} / {{ $totalForms }} Form Lengkap</strong></td>
        </tr>
        <tr>
            <td style="font-weight: bold; padding: 2px 0;">NIP</td>
            <td style="padding: 2px 0;">: {{ $teacher->nip ?: '-' }}</td>
            <td style="font-weight: bold; padding: 2px 0;">Nilai Akhir Periode</td>
            <td style="padding: 2px 0;">: <strong>{{ number_format((float) $nilaiAkhir, 2) }}% ({{ $ketercapaian }})</strong></td>
        </tr>
    </table>

    <div style="background-color: #EAEAEA; padding: 5px 10px; font-weight: bold; font-size: 10pt; border-left: 4px solid #0b5ed7; margin-bottom: 8px;">
        Rekapitulasi Ketuntasan &amp; Nilai Seluruh 14 Form Penilaian
    </div>

    <table style="width: 100%; border-collapse: collapse; font-size: 8.5pt; margin-bottom: 12px;">
        <thead>
            <tr style="background: #f8fafc;">
                <th style="border: 1px solid #000; padding: 4px; width: 28px; text-align: center;">No</th>
                <th style="border: 1px solid #000; padding: 4px; text-align: left;">Kode &amp; Nama Form Penilaian</th>
                <th style="border: 1px solid #000; padding: 4px; width: 95px; text-align: center;">Status</th>
                <th style="border: 1px solid #000; padding: 4px; width: 70px; text-align: center;">Tgl Nilai</th>
                <th style="border: 1px solid #000; padding: 4px; width: 65px; text-align: center;">Skor</th>
                <th style="border: 1px solid #000; padding: 4px; width: 65px; text-align: center;">Nilai (%)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($forms as $f)
                <tr>
                    <td style="border: 1px solid #000; padding: 4px; text-align: center;">{{ $f['nomor'] }}</td>
                    <td style="border: 1px solid #000; padding: 4px;"><strong>{{ $f['jenis']->kode }}</strong> — {{ $f['jenis']->nama }}</td>
                    <td style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold;">{{ $f['icon'] }} {{ $f['status_label'] }}</td>
                    <td style="border: 1px solid #000; padding: 4px; text-align: center;">{{ $f['jadwal']?->tanggal?->format('d/m/Y') ?? '-' }}</td>
                    <td style="border: 1px solid #000; padding: 4px; text-align: center;">{{ $f['total_skor'] }}/{{ $f['skor_maksimal'] }}</td>
                    <td style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold;">{{ number_format((float) $f['nilai_akhir'], 2) }}%</td>
                </tr>
            @endforeach
            <tr style="background: #f1f5f9; font-weight: bold;">
                <td colspan="4" style="border: 1px solid #000; padding: 5px; text-align: center;">TOTAL AKUMULASI {{ $totalForms }} FORM</td>
                <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ $totalSkor }}/{{ $skorMaksimal }}</td>
                <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ number_format((float) $nilaiAkhir, 2) }}%</td>
            </tr>
        </tbody>
    </table>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px; page-break-inside: avoid;">
        <tr>
            <td style="width: 60%; vertical-align: top; padding-right: 12px;">
                <div style="font-size: 9.5pt; margin-bottom: 6px;">
                    <strong>Hasil Akhir Portofolio:</strong>
                    <span style="display: inline-block; background: #0b5ed7; color: #fff; font-weight: bold; padding: 2px 8px; border-radius: 4px; margin: 0 4px;">
                        {{ number_format((float) $nilaiAkhir, 2) }}%
                    </span>
                    <span style="display: inline-block; background: {{ $predColor['bg'] }}; color: {{ $predColor['color'] }}; font-weight: bold; padding: 2px 8px; border-radius: 4px;">
                        {{ $ketercapaian }}
                    </span>
                </div>
                <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px; padding: 6px 8px; font-size: 8.5pt;">
                    Rumus: ({{ $totalSkor }} / {{ $skorMaksimal }}) &times; 100 = <strong>{{ number_format((float) $nilaiAkhir, 2) }}%</strong>
                </div>
            </td>
            <td style="width: 40%; vertical-align: top; font-size: 8pt;">
                <div><strong>Kategori:</strong></div>
                <div>Baik Sekali: 86–100% · Baik: 70–85%</div>
                <div>Cukup: 55–69% · Kurang: 0–54%</div>
            </td>
        </tr>
    </table>

    <!-- Halaman 2+: Rincian Seluruh Aspek (81 Aspek + Keterangan) -->
    @if($forms->isNotEmpty())
        <div class="page-break"></div>

        @foreach($forms as $f)
            @php $jenis = $f['jenis']; @endphp
            <div style="margin-bottom: 12px; page-break-inside: avoid;">
                <div style="background: #e2e8f0; border: 1px solid #000; padding: 4px 8px; font-size: 9.2pt; font-weight: bold;">
                    {{ $f['nomor'] }}. [{{ $jenis->kode }}] {{ $jenis->nama }} — {{ $f['total_skor'] }}/{{ $f['skor_maksimal'] }} ({{ number_format((float) $f['nilai_akhir'], 2) }}% · {{ $f['status_label'] }})
                </div>
                <table style="width: 100%; border-collapse: collapse; font-size: 8pt;">
                    <thead>
                        <tr style="background: #f8fafc;">
                            <th style="border: 1px solid #000; padding: 3px; width: 24px; text-align: center;">No</th>
                            <th style="border: 1px solid #000; padding: 3px; text-align: left; width: 44%;">Aspek &amp; Keterangan Indikator</th>
                            <th style="border: 1px solid #000; padding: 3px; width: 40px; text-align: center;">Skor</th>
                            <th style="border: 1px solid #000; padding: 3px; text-align: left;">Catatan Penilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jenis->aspekPenilaians as $aspek)
                            @php $d = $f['details_by_aspek']->get($aspek->id); @endphp
                            <tr>
                                <td style="border: 1px solid #000; padding: 3px; text-align: center; vertical-align: top;">{{ $aspek->urutan }}</td>
                                <td style="border: 1px solid #000; padding: 3px; vertical-align: top;">
                                    <div style="font-weight: bold;">{{ $aspek->nama_aspek }}</div>
                                    @if($aspek->fokus_perilaku)
                                        <div style="font-size: 7.5pt; color: #475569;">{{ $aspek->fokus_perilaku }}</div>
                                    @endif
                                </td>
                                <td style="border: 1px solid #000; padding: 3px; text-align: center; font-weight: bold; vertical-align: top;">
                                    {{ $d ? $d->skor : '-' }}
                                </td>
                                <td style="border: 1px solid #000; padding: 3px; vertical-align: top;">{{ $d?->catatan ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($f['hasil']?->rekomendasi)
                    <div style="border: 1px solid #000; border-top: none; padding: 4px 6px; font-size: 8pt;">
                        <strong>Rekomendasi:</strong> {{ $f['hasil']->rekomendasi }}
                    </div>
                @endif
            </div>
        @endforeach
    @endif

    <table style="width: 100%; border-collapse: collapse; margin-top: 16px; page-break-inside: avoid; font-size: 9pt;">
        <tr>
            <td style="width: 60%;"></td>
            <td style="width: 40%; text-align: center;">
                {{ $kotaMadrasah }}, {{ now()->translatedFormat('d F Y') }}<br>
                <strong>Kepala Madrasah</strong>
                <div style="height: 55px;"></div>
                <div style="font-weight: bold; text-decoration: underline;">{{ $namaKepala }}</div>
                <div style="font-size: 8pt; color: #475569;">NIP. {{ $nipKepala }}</div>
            </td>
        </tr>
    </table>
</x-print-preview-layout>
