<x-print-preview-layout title="Cetak Hasil Supervisi Berdasarkan Instrumen" paperSize="A4" orientation="landscape" :backUrl="$backUrl">
    <x-slot:actions>
        <form method="GET" class="flex flex-wrap items-center gap-1.5">
            <select name="academic_year_id" aria-label="Filter Tahun Ajaran" class="px-2 py-1 text-[11px] font-bold border-2 border-black rounded bg-white">
                <option value="all" {{ $selectedYearId === 'all' ? 'selected' : '' }}>Semua TA</option>
                @foreach($academicYears as $yr)
                    <option value="{{ $yr->id }}" {{ (string) $selectedYearId === (string) $yr->id ? 'selected' : '' }}>
                        {{ $yr->name }} ({{ ucfirst($yr->semester) }})
                    </option>
                @endforeach
            </select>

            <select name="kategori" aria-label="Filter Kategori Instrumen" class="px-2 py-1 text-[11px] font-bold border-2 border-black rounded bg-white">
                <option value="all" {{ $selectedKategori === 'all' ? 'selected' : '' }}>Semua Kategori</option>
                <option value="reguler" {{ $selectedKategori === 'reguler' ? 'selected' : '' }}>Reguler</option>
                <option value="observasi_kelas_pmm" {{ $selectedKategori === 'observasi_kelas_pmm' ? 'selected' : '' }}>Observasi PMM</option>
                <option value="perangkat_ajar" {{ $selectedKategori === 'perangkat_ajar' ? 'selected' : '' }}>Perangkat Ajar</option>
            </select>

            <select name="jenis_penilaian_id" aria-label="Filter Instrumen Spesifik" class="px-2 py-1 text-[11px] font-bold border-2 border-black rounded bg-white max-w-[240px]">
                <option value="all" {{ (string) $selectedJenisId === 'all' ? 'selected' : '' }}>Semua Instrumen</option>
                @foreach($masterJenisList as $jp)
                    <option value="{{ $jp->id }}" {{ (string) $selectedJenisId === (string) $jp->id ? 'selected' : '' }}>
                        {{ $jp->kode }} — {{ $jp->nama }}
                    </option>
                @endforeach
            </select>

            <select name="status" aria-label="Filter Status Jadwal" class="px-2 py-1 text-[11px] font-bold border-2 border-black rounded bg-white">
                <option value="all" {{ $selectedStatus === 'all' ? 'selected' : '' }}>Semua Status</option>
                @foreach($statusOptions as $st)
                    <option value="{{ $st->value }}" {{ $selectedStatus === $st->value ? 'selected' : '' }}>{{ $st->label() }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-2.5 py-1 bg-[#FFD43B] hover:bg-amber-300 text-black text-[11px] font-black rounded border-2 border-black cursor-pointer">
                Terapkan
            </button>
        </form>
    </x-slot:actions>

    @php
        $activeMatrixInstruments = $instrumentSections->pluck('jenis');
    @endphp

    <x-kop-surat :useBase64="true" />

    <div style="text-align: center; margin-bottom: 10px;">
        <h1 style="font-size: 13pt; font-weight: bold; text-transform: uppercase; margin: 0; color: #111827;">
            Laporan Hasil Supervisi Akademik Berdasarkan Instrumen
        </h1>
        <p style="font-size: 9pt; margin: 2px 0 0 0; color: #334155;">
            Tahun Ajaran: <strong>{{ $selectedYearLabel }}</strong> &nbsp;|&nbsp;
            Instrumen: <strong>{{ $selectedJenisLabel }}</strong>
        </p>
    </div>

    <!-- Bagian 1: Ringkasan Metrik & Distribusi Kualifikasi -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px; page-break-inside: avoid;">
        <tr>
            <td style="width: 34%; vertical-align: top; padding-right: 12px;">
                <table style="width: 100%; border-collapse: separate; border-spacing: 6px 0; margin: 0 -6px;">
                    <tr>
                        <td style="border: 1px solid #94a3b8; background: #f8fafc; border-radius: 4px; padding: 7px 8px; text-align: center; width: 33%;">
                            <div style="font-size: 7pt; font-weight: bold; color: #475569; text-transform: uppercase;">Guru Dinilai</div>
                            <div style="font-size: 13pt; font-weight: bold; color: #0b5ed7; margin-top: 2px;">{{ $totalGuru }}</div>
                        </td>
                        <td style="border: 1px solid #94a3b8; background: #f8fafc; border-radius: 4px; padding: 7px 8px; text-align: center; width: 33%;">
                            <div style="font-size: 7pt; font-weight: bold; color: #475569; text-transform: uppercase;">Total Jadwal</div>
                            <div style="font-size: 13pt; font-weight: bold; color: #0f172a; margin-top: 2px;">{{ $totalJadwal }}</div>
                        </td>
                        <td style="border: 1px solid #94a3b8; background: #f8fafc; border-radius: 4px; padding: 7px 8px; text-align: center; width: 34%;">
                            <div style="font-size: 7pt; font-weight: bold; color: #475569; text-transform: uppercase;">Rata-Rata</div>
                            <div style="font-size: 13pt; font-weight: bold; color: #198754; margin-top: 2px;">{{ number_format((float) $rataRata, 2) }}%</div>
                        </td>
                    </tr>
                </table>
            </td>

            <td style="width: 66%; vertical-align: top;">
                <table style="width: 100%; border-collapse: collapse; font-size: 8pt;">
                    <thead>
                        <tr style="background: #dbeafe; color: #1e3a8a;">
                            <th colspan="5" style="border: 1px solid #000; padding: 3px 6px; text-align: center; text-transform: uppercase;">
                                Distribusi Kualifikasi Hasil Supervisi
                            </th>
                        </tr>
                        <tr style="background: #f1f5f9;">
                            <th style="border: 1px solid #000; padding: 3px 6px; width: 24%;">Predikat</th>
                            <th style="border: 1px solid #000; padding: 3px 6px; width: 20%;">Rentang Nilai</th>
                            <th style="border: 1px solid #000; padding: 3px 6px; width: 18%;">Frekuensi</th>
                            <th style="border: 1px solid #000; padding: 3px 6px; width: 18%;">Persentase</th>
                            <th style="border: 1px solid #000; padding: 3px 6px; width: 20%;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($predikatStats as $predName => $pData)
                            @php
                                $pct = $totalJadwal > 0 ? round(($pData['count'] / $totalJadwal) * 100, 1) : 0;
                                $badgeColor = match ($predName) {
                                    'Baik Sekali' => 'background:#198754;color:#fff;',
                                    'Baik' => 'background:#0891b2;color:#fff;',
                                    'Cukup' => 'background:#eab308;color:#111827;',
                                    default => 'background:#dc3545;color:#fff;',
                                };
                            @endphp
                            <tr>
                                <td style="border: 1px solid #000; padding: 2.5px 6px; font-weight: bold;">
                                    <span style="display: inline-block; padding: 1px 6px; border-radius: 3px; font-size: 7.5pt; {{ $badgeColor }}">{{ $predName }}</span>
                                </td>
                                <td style="border: 1px solid #000; padding: 2.5px 6px; text-align: center;">{{ $pData['range'] }}</td>
                                <td style="border: 1px solid #000; padding: 2.5px 6px; text-align: center; font-weight: bold;">{{ $pData['count'] }}</td>
                                <td style="border: 1px solid #000; padding: 2.5px 6px; text-align: center;">{{ $pct }}%</td>
                                <td style="border: 1px solid #000; padding: 2.5px 6px; text-align: center; font-weight: bold; color: {{ in_array($predName, ['Baik Sekali', 'Baik'], true) ? '#15803d' : '#b91c1c' }};">
                                    {{ in_array($predName, ['Baik Sekali', 'Baik'], true) ? 'Tuntas' : 'Perlu Binaan' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <!-- Bagian 2: Matriks Rekap Skor Per Instrumen -->
    <div style="margin-bottom: 12px;">
        <div style="background-color: #EAEAEA; padding: 4px 8px; font-weight: bold; font-size: 9.5pt; border-left: 4px solid #0b5ed7; margin-bottom: 6px;">
            Matriks Rekapitulasi Nilai Guru Per Instrumen (%)
        </div>

        <table style="width: 100%; border-collapse: collapse; font-size: 7.8pt;">
            <thead>
                <tr style="background: #e2e8f0;">
                    <th rowspan="{{ $activeMatrixInstruments->isNotEmpty() ? 2 : 1 }}" style="border: 1px solid #000; padding: 4px; width: 28px; text-align: center;">No</th>
                    <th rowspan="{{ $activeMatrixInstruments->isNotEmpty() ? 2 : 1 }}" style="border: 1px solid #000; padding: 4px; text-align: left; min-width: 135px;">Nama Guru &amp; NIP</th>
                    <th rowspan="{{ $activeMatrixInstruments->isNotEmpty() ? 2 : 1 }}" style="border: 1px solid #000; padding: 4px; text-align: left; width: 105px;">Kelas / Mapel</th>
                    <th rowspan="{{ $activeMatrixInstruments->isNotEmpty() ? 2 : 1 }}" style="border: 1px solid #000; padding: 4px; text-align: center; width: 64px;">Tanggal</th>
                    @if($activeMatrixInstruments->isNotEmpty())
                        <th colspan="{{ $activeMatrixInstruments->count() }}" style="border: 1px solid #000; padding: 3px 4px; text-align: center;">Persentase Nilai per Instrumen (%)</th>
                    @endif
                    <th rowspan="{{ $activeMatrixInstruments->isNotEmpty() ? 2 : 1 }}" style="border: 1px solid #000; padding: 4px; text-align: center; width: 58px;">Nilai Akhir</th>
                    <th rowspan="{{ $activeMatrixInstruments->isNotEmpty() ? 2 : 1 }}" style="border: 1px solid #000; padding: 4px; text-align: center; width: 74px;">Predikat</th>
                </tr>
                @if($activeMatrixInstruments->isNotEmpty())
                    <tr style="background: #f8fafc;">
                        @foreach($activeMatrixInstruments as $inst)
                            <th style="border: 1px solid #000; padding: 3px; text-align: center; font-size: 7pt;" title="{{ $inst->nama }}">
                                {{ $inst->kode }}
                            </th>
                        @endforeach
                    </tr>
                @endif
            </thead>
            <tbody>
                @forelse($matrixRows as $idx => $row)
                    @php
                        $j = $row['jadwal'];
                        $pred = $row['ketercapaian'];
                        $predStyle = match ($pred) {
                            'Baik Sekali' => 'background:#198754;color:#fff;',
                            'Baik' => 'background:#0891b2;color:#fff;',
                            'Cukup' => 'background:#eab308;color:#111827;',
                            default => 'background:#dc3545;color:#fff;',
                        };
                    @endphp
                    <tr>
                        <td style="border: 1px solid #000; padding: 3.5px; text-align: center;">{{ $idx + 1 }}</td>
                        <td style="border: 1px solid #000; padding: 3.5px;">
                            <strong>{{ $j->teacher?->user?->name }}</strong>
                            @if($j->teacher?->nip)
                                <br><span style="font-size: 6.8pt; color: #475569;">NIP. {{ $j->teacher->nip }}</span>
                            @endif
                        </td>
                        <td style="border: 1px solid #000; padding: 3.5px;">{{ $j->schoolClass?->name ?? '-' }} / {{ $j->subject?->name ?? '-' }}</td>
                        <td style="border: 1px solid #000; padding: 3.5px; text-align: center;">{{ $j->tanggal?->format('d/m/Y') }}</td>
                        @foreach($activeMatrixInstruments as $inst)
                            @php $val = $row['nilai_per_jenis'][$inst->id] ?? null; @endphp
                            <td style="border: 1px solid #000; padding: 3.5px; text-align: center; font-weight: {{ $val !== null ? 'bold' : 'normal' }}; color: {{ $val !== null ? '#0f172a' : '#94a3b8' }};">
                                {{ $val !== null ? number_format($val, 2) . '%' : '-' }}
                            </td>
                        @endforeach
                        <td style="border: 1px solid #000; padding: 3.5px; text-align: center; font-weight: bold;">{{ number_format((float) $row['nilai_akhir'], 2) }}%</td>
                        <td style="border: 1px solid #000; padding: 3.5px; text-align: center;">
                            <span style="display: inline-block; padding: 1px 5px; border-radius: 3px; font-size: 7pt; font-weight: bold; {{ $predStyle }}">{{ $pred }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 6 + $activeMatrixInstruments->count() }}" style="border: 1px solid #000; padding: 12px; text-align: center; color: #64748b;">
                            Belum ada hasil penilaian supervisi untuk filter instrumen yang dipilih.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($matrixRows->isNotEmpty())
                <tfoot>
                    <tr style="background: #f1f5f9; font-weight: bold;">
                        <td colspan="4" style="border: 1px solid #000; padding: 4px; text-align: center;">RATA-RATA KESELURUHAN</td>
                        @foreach($activeMatrixInstruments as $inst)
                            @php
                                $colValues = $matrixRows->pluck("nilai_per_jenis.{$inst->id}")->filter(fn ($v): bool => $v !== null);
                                $colAvg = $colValues->isNotEmpty() ? round((float) $colValues->avg(), 2) : null;
                            @endphp
                            <td style="border: 1px solid #000; padding: 4px; text-align: center;">
                                {{ $colAvg !== null ? number_format($colAvg, 2) . '%' : '-' }}
                            </td>
                        @endforeach
                        <td style="border: 1px solid #000; padding: 4px; text-align: center; color: #0b5ed7;">{{ number_format((float) $rataRata, 2) }}%</td>
                        <td style="border: 1px solid #000; padding: 4px; text-align: center;">{{ $rataRataPredikat }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    <!-- Bagian 3: Rincian Skor Aspek Setiap Instrumen -->
    @if($instrumentSections->isNotEmpty())
        <div class="page-break"></div>

        <div style="text-align: center; margin-bottom: 10px;">
            <h2 style="font-size: 11.5pt; font-weight: bold; text-transform: uppercase; margin: 0;">
                Rincian Hasil Penilaian Berdasarkan Instrumen &amp; Aspek
            </h2>
        </div>

        @foreach($instrumentSections as $secIdx => $section)
            @php
                $jenis = $section['jenis'];
                $aspeks = $jenis->aspekPenilaians;
            @endphp

            <div style="margin-bottom: 14px; page-break-inside: avoid;">
                <div style="background-color: #EAEAEA; padding: 4px 8px; font-weight: bold; font-size: 9pt; border-left: 4px solid #0b5ed7; margin-bottom: 5px; display: flex; justify-content: space-between; align-items: center;">
                    <span>{{ $secIdx + 1 }}. [{{ $jenis->kode }}] {{ $jenis->nama }} ({{ ucwords(str_replace('_', ' ', $jenis->kategori_supervisi)) }})</span>
                    <span>Rata-rata: {{ number_format((float) $section['avgNilai'], 2) }}% ({{ $section['avgPredikat'] }})</span>
                </div>

                <table style="width: 100%; border-collapse: collapse; font-size: 7.5pt; margin-bottom: 4px;">
                    <thead>
                        <tr style="background: #f1f5f9;">
                            <th style="border: 1px solid #000; padding: 3px; width: 24px; text-align: center;">No</th>
                            <th style="border: 1px solid #000; padding: 3px; text-align: left; min-width: 120px;">Nama Guru</th>
                            <th style="border: 1px solid #000; padding: 3px; text-align: left; width: 95px;">Kelas / Mapel</th>
                            <th style="border: 1px solid #000; padding: 3px; text-align: center; width: 58px;">Tanggal</th>
                            @foreach($aspeks as $aspek)
                                <th style="border: 1px solid #000; padding: 3px 2px; text-align: center; width: 24px;" title="{{ $aspek->urutan }}. {{ $aspek->nama_aspek }}">
                                    A{{ $aspek->urutan }}
                                </th>
                            @endforeach
                            <th style="border: 1px solid #000; padding: 3px; text-align: center; width: 48px;">Skor</th>
                            <th style="border: 1px solid #000; padding: 3px; text-align: center; width: 48px;">Nilai</th>
                            <th style="border: 1px solid #000; padding: 3px; text-align: center; width: 64px;">Predikat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($section['rows'] as $rowIdx => $secRow)
                            @php
                                $j = $secRow['jadwal'];
                                $h = $secRow['hasil'];
                                $details = $secRow['detailsByAspek'];
                            @endphp
                            <tr>
                                <td style="border: 1px solid #000; padding: 3px; text-align: center;">{{ $rowIdx + 1 }}</td>
                                <td style="border: 1px solid #000; padding: 3px; font-weight: bold;">{{ $j->teacher?->user?->name }}</td>
                                <td style="border: 1px solid #000; padding: 3px;">{{ $j->schoolClass?->name ?? '-' }} / {{ $j->subject?->name ?? '-' }}</td>
                                <td style="border: 1px solid #000; padding: 3px; text-align: center;">{{ $j->tanggal?->format('d/m/Y') }}</td>
                                @foreach($aspeks as $aspek)
                                    @php $d = $details->get($aspek->id); @endphp
                                    <td style="border: 1px solid #000; padding: 3px 2px; text-align: center; font-weight: bold;">
                                        {{ $d ? $d->skor : '-' }}
                                    </td>
                                @endforeach
                                <td style="border: 1px solid #000; padding: 3px; text-align: center;">{{ $h->total_skor }}/{{ $h->skor_maksimal }}</td>
                                <td style="border: 1px solid #000; padding: 3px; text-align: center; font-weight: bold;">{{ number_format((float) $h->nilai_akhir, 2) }}%</td>
                                <td style="border: 1px solid #000; padding: 3px; text-align: center;">{{ $h->ketercapaian }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div style="font-size: 7pt; color: #334155; background: #f8fafc; border: 1px solid #cbd5e1; padding: 4px 6px; line-height: 1.4;">
                    <strong>Keterangan Aspek:</strong>
                    @foreach($aspeks as $aspek)
                        <span style="margin-right: 8px;"><strong>A{{ $aspek->urutan }}:</strong> {{ $aspek->nama_aspek }}</span>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif

    <!-- Blok Pengesahan Kepala Madrasah -->
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
