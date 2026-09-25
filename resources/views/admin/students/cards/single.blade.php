<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Siswa - {{ $student->user?->name ?? $student->nis }}</title>
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
                size: auto;
                margin: 5mm;
            }
            body {
                background-color: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
            }
            .print\:hidden {
                display: none !important;
            }
            .student-card-side {
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-4">

    <!-- Action Toolbar -->
    <div class="mb-6 flex flex-wrap items-center justify-center gap-3 bg-white border-2 border-black p-3 rounded-lg shadow-[3px_3px_0px_#000] print:hidden">
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
            <span>Cetak Kartu</span>
        </button>
    </div>

    <!-- The Student Card (Front & Back) -->
    <div class="flex justify-center">
        <x-student-card :student="$student" :setting="$setting" side="both" orientation="landscape" size="standard" :interactive="false" />
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
