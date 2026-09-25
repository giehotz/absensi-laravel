<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Kartu Siswa (Format A4) - {{ $setting->school_name ?? 'Absensi Siswa' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,600,700,800|space-grotesk:600,700" rel="stylesheet" />
    @vite(['resources/css/app.css'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
        }

        .student-card-side {
            box-sizing: border-box;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 8mm;
            }

            body {
                background-color: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .print\:hidden {
                display: none !important;
            }

            .print-page {
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
            }

            /* Hilangkan bayangan neobrutalism saat cetak fisik agar hasil presisi */
            .student-card-side {
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body class="min-h-screen text-slate-900 antialiased p-4 sm:p-6">

    <!-- Top Action Bar (Hidden when printed) -->
    <div class="max-w-5xl mx-auto mb-6 bg-white border-2 border-black p-4 rounded-lg shadow-[4px_4px_0px_#000] flex flex-col sm:flex-row sm:items-center justify-between gap-4 print:hidden">
        <div>
            <h1 class="font-heading font-black text-lg text-black flex items-center gap-2">
                <span>🖨️</span> Pratinjau Lembar Cetak Kartu Siswa (Format A4)
            </h1>
            <p class="text-xs text-slate-600 mt-0.5">
                Total <strong>{{ count($students) }}</strong> kartu siap dicetak. Ukuran kartu fisik disesuaikan dengan pengaturan ({{ $setting->card_width_cm }} × {{ $setting->card_height_cm }} cm).
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2">
                <label for="printSideSelector" class="text-xs font-bold text-black uppercase tracking-wider">Sisi Cetak:</label>
                <select id="printSideSelector" onchange="changePrintSide(this.value)" class="neo-input px-3 py-1.5 text-xs font-bold bg-slate-50 focus:bg-white">
                    <option value="both" selected>Kedua Sisi (Depan & Belakang)</option>
                    <option value="front">Sisi Depan Saja (Validasi)</option>
                    <option value="back">Sisi Belakang Saja (Presensi)</option>
                </select>
            </div>

            <button onclick="window.close()" class="neo-btn bg-white hover:bg-slate-100 text-black px-4 py-2 text-xs font-bold">
                Tutup
            </button>
            <button onclick="window.print()" class="neo-btn bg-[#20C997] hover:bg-[#1bb386] text-black px-5 py-2 text-xs font-bold flex items-center gap-1.5 shadow-[2px_2px_0px_#000]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                <span>Cetak Sekarang</span>
            </button>
        </div>
    </div>

    <!-- Lembar Kerja A4 Grid -->
    <div class="max-w-[210mm] mx-auto bg-white p-2 sm:p-4 rounded-lg shadow-sm border border-slate-300 print:border-none print-page">
        <div id="printCardsContainer" class="flex flex-wrap items-center justify-center gap-4">
            @foreach($students as $student)
                <x-student-card :student="$student" :setting="$setting" side="both" orientation="landscape" size="print" />
            @endforeach
        </div>
    </div>

    <script>
        function changePrintSide(side) {
            document.querySelectorAll('.card-front').forEach(el => {
                el.style.display = (side === 'back') ? 'none' : 'flex';
            });
            document.querySelectorAll('.card-back').forEach(el => {
                el.style.display = (side === 'front') ? 'none' : 'flex';
            });
        }
    </script>
</body>
</html>
