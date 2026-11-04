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
            background-color: #F0F3F8;
            background-image: radial-gradient(#CBD5E1 1.5px, transparent 1.5px);
            background-size: 24px 24px;
        }

        .font-heading {
            font-family: 'Space Grotesk', sans-serif;
        }

        .student-card-side {
            box-sizing: border-box;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* Mode garis potong (cut guides) */
        .show-cut-guides .student-card-side {
            outline: 1px dashed #94a3b8 !important;
            outline-offset: 1px !important;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 6mm;
            }

            body {
                background-color: #ffffff !important;
                background-image: none !important;
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
                max-width: 100% !important;
            }

            .student-card-side {
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body class="min-h-screen text-slate-900 antialiased p-4 sm:p-6">

    <!-- Top Action Bar (Hidden when printed) -->
    <div class="max-w-5xl mx-auto mb-6 bg-white border-2 border-black p-4 rounded-xl shadow-[4px_4px_0px_#000] flex flex-col sm:flex-row sm:items-center justify-between gap-4 print:hidden">
        <div>
            <h1 class="font-heading font-black text-lg text-black flex items-center gap-2">
                <span>🖨️</span> Pratinjau Lembar Cetak Kartu Siswa (Format A4)
            </h1>
            <p class="text-xs text-slate-600 mt-0.5">
                Total <strong>{{ count($students) }}</strong> kartu siap dicetak ({{ $setting->card_width_cm }} × {{ $setting->card_height_cm }} cm). Pastikan opsi skala printer diatur ke <strong>100% / Actual Size</strong>.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <div class="flex items-center gap-1.5">
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Sisi:</span>
                <select id="printSideSelector" onchange="changePrintSide(this.value)" class="neo-input px-2.5 py-1 text-xs font-bold bg-slate-50 focus:bg-white cursor-pointer shadow-[2px_2px_0px_#000]">
                    <option value="both" selected>Kedua Sisi (Depan & Belakang)</option>
                    <option value="front">Sisi Depan Saja (Validasi)</option>
                    <option value="back">Sisi Belakang Saja (Presensi)</option>
                </select>
            </div>

            <!-- Toggle Garis Potong -->
            <button type="button" 
                    onclick="toggleCutGuides()" 
                    id="cutGuidesBtn"
                    class="neo-btn bg-white hover:bg-slate-100 text-black px-2.5 py-1.5 text-xs font-black shadow-[2px_2px_0px_#000]"
                    title="Tampilkan garis bantu potong">
                <span>✂️ Garis Potong</span>
            </button>

            <button onclick="window.close()" class="neo-btn bg-white hover:bg-slate-100 text-black px-3.5 py-1.5 text-xs font-bold">
                Tutup
            </button>
            <button onclick="window.print()" class="neo-btn bg-[#20C997] hover:bg-[#1bb386] text-black px-4 py-1.5 text-xs font-black flex items-center gap-1.5 shadow-[2px_2px_0px_#000] cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                <span>Cetak Lembar A4</span>
            </button>
        </div>
    </div>

    <!-- Lembar Kerja A4 Grid -->
    <div class="max-w-[210mm] mx-auto bg-white p-3 sm:p-5 rounded-xl shadow-[4px_4px_0px_#000] border-2 border-black print:border-none print-page">
        <div id="printCardsContainer" class="flex flex-wrap items-center justify-center gap-3 transition-all duration-200">
            @foreach($students as $student)
                <x-student-card :student="$student" :setting="$setting" side="both" orientation="landscape" size="print" :cardId="'print_' . $student->id" />
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

        let cutGuidesActive = false;
        function toggleCutGuides() {
            cutGuidesActive = !cutGuidesActive;
            const container = document.getElementById('printCardsContainer');
            const btn = document.getElementById('cutGuidesBtn');

            if (cutGuidesActive) {
                container.classList.add('show-cut-guides');
                btn.classList.add('bg-[#FFD43B]');
                btn.classList.remove('bg-white');
            } else {
                container.classList.remove('show-cut-guides');
                btn.classList.remove('bg-[#FFD43B]');
                btn.classList.add('bg-white');
            }
        }
    </script>
</body>
</html>
