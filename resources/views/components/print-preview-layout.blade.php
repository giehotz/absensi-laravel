@props([
    'title' => 'Pratinjau Dokumen Cetak',
    'backUrl' => null,
    'paperSize' => 'A4',
    'orientation' => 'portrait',
    'customStyle' => '',
    'sheetClass' => '',
    'pageMargin' => null,
])

@php
    $backUrl = $backUrl ?: url()->previous();
    $isLandscape = strtolower($orientation) === 'landscape';
    $paperWidthMm = $isLandscape ? '297mm' : '210mm';
    $paperMinHeightMm = $isLandscape ? '210mm' : '297mm';
    $effectivePageMargin = $pageMargin ?: ($isLandscape ? '5mm 8mm' : '10mm 12mm');
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @page {
            size: {{ $paperSize }} {{ $orientation }};
            margin: {{ $effectivePageMargin }};
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        @media screen {
            :root {
                --print-scale: 1;
            }

            .print-desk {
                background-color: #cbd5e1;
                background-image: radial-gradient(#94a3b8 1px, transparent 1px);
                background-size: 16px 16px;
            }

            .print-paper-sheet {
                width: {{ $paperWidthMm }};
                min-height: {{ $paperMinHeightMm }};
                transform: scale(var(--print-scale));
                transform-origin: top center;
                transition: transform 0.15s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .page-break {
                border-bottom: 2px dashed #94a3b8;
                margin-top: 2rem;
                margin-bottom: 2rem;
                padding-bottom: 2rem;
                position: relative;
            }
            .page-break::after {
                content: "✂ Batas Halaman (Page Break)";
                display: block;
                position: absolute;
                bottom: -11px;
                left: 50%;
                transform: translateX(-50%);
                background: #f1f5f9;
                color: #64748b;
                font-family: ui-monospace, monospace;
                font-size: 10px;
                font-weight: bold;
                padding: 1px 8px;
                border-radius: 4px;
                border: 1px dashed #cbd5e1;
            }
        }

        @media print {
            body {
                background: #fff !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .print-ui-hide {
                display: none !important;
            }

            .print-desk {
                background: transparent !important;
                padding: 0 !important;
                margin: 0 !important;
                min-height: auto !important;
                display: block !important;
            }

            .print-paper-sheet {
                transform: none !important;
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: none !important;
                min-height: auto !important;
            }

            .page-break {
                page-break-after: always !important;
                break-after: page !important;
                border: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .page-break::after {
                display: none !important;
            }
        }

        {!! $customStyle !!}
    </style>
</head>
<body class="font-sans antialiased text-slate-900 bg-slate-200">
    <div class="print-desk min-h-screen py-4 px-2 sm:px-4 flex flex-col items-center">
        {{-- Floating Neo-brutalist Toolbar --}}
        <header class="print-ui-hide sticky top-3 z-50 w-full max-w-6xl mb-4 bg-white border-4 border-black rounded-xl p-2.5 sm:p-3 shadow-[4px_4px_0px_0px_#000] flex flex-wrap items-center justify-between gap-2.5">
            {{-- Left: Kembali & Info Dokumen --}}
            <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
                <a href="{{ $backUrl }}" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-black border-2 border-black rounded-lg text-xs font-black flex items-center gap-1.5 shadow-[1.5px_1.5px_0px_0px_#000] cursor-pointer" title="Kembali">
                    <span>←</span>
                    <span class="hidden sm:inline">Kembali</span>
                </a>

                <div class="flex items-center gap-1.5">
                    <span class="font-black font-heading text-xs sm:text-sm text-black tracking-tight line-clamp-1">
                        {{ $title }}
                    </span>
                    <span class="text-[10px] font-mono px-1.5 py-0.5 rounded font-black border border-black {{ $isLandscape ? 'bg-sky-100 text-sky-950' : 'bg-amber-100 text-amber-950' }}">
                        {{ strtoupper($paperSize) }} {{ strtoupper($orientation) }}
                    </span>
                </div>
            </div>

            {{-- Center: Slot Aksi Spesifik Halaman (Switcher / Filter) --}}
            @if(isset($actions))
                <div class="flex items-center gap-1.5 flex-wrap">
                    {{ $actions }}
                </div>
            @endif

            {{-- Right: Kontrol Zoom & Tombol Cetak --}}
            <div class="flex items-center gap-2">
                {{-- Zoom Controls --}}
                <div class="hidden md:flex items-center bg-slate-100 border-2 border-black rounded-lg p-0.5 text-xs font-black shadow-[1.5px_1.5px_0px_0px_#000]">
                    <button type="button" onclick="adjustZoom(-0.1)" class="px-2 py-1 hover:bg-slate-200 rounded text-slate-800" title="Perkecil">−</button>
                    <button type="button" onclick="resetZoom()" id="zoomPercentDisplay" class="px-2 py-1 hover:bg-slate-200 rounded font-mono text-[11px] text-black w-14 text-center" title="Klik untuk Reset ke 100%">100%</button>
                    <button type="button" onclick="adjustZoom(0.1)" class="px-2 py-1 hover:bg-slate-200 rounded text-slate-800" title="Perbesar">+</button>
                    <button type="button" onclick="fitToWidth()" class="px-2 py-1 hover:bg-amber-200 bg-amber-100 border-l border-black text-[10px] text-amber-950 uppercase" title="Sesuaikan Lebar Layar">Fit</button>
                </div>

                {{-- Action Print Button --}}
                <button type="button" onclick="window.print()" class="px-3.5 py-1.5 bg-[#20C997] hover:bg-emerald-400 text-black border-2 border-black rounded-lg text-xs font-black flex items-center gap-1.5 shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                    <span>🖨️</span>
                    <span>Cetak PDF</span>
                </button>
            </div>
        </header>

        {{-- Paper Preview Wrapper --}}
        <main class="w-full flex justify-center overflow-x-auto pb-12">
            <article id="printPaperSheet" class="print-paper-sheet bg-white border-2 border-black rounded-sm shadow-[6px_6px_0px_0px_rgba(0,0,0,0.3)] {{ $sheetClass ?: 'p-4 sm:p-6' }} text-black">
                {{ $slot }}
            </article>
        </main>
    </div>

    {{-- Script Kontrol Zoom & Fit --}}
    <script>
        (function () {
            let currentScale = 1;
            const sheet = document.getElementById('printPaperSheet');
            const display = document.getElementById('zoomPercentDisplay');

            function applyScale(scale) {
                currentScale = Math.min(Math.max(scale, 0.4), 1.6);
                document.documentElement.style.setProperty('--print-scale', currentScale);
                if (display) {
                    display.innerText = Math.round(currentScale * 100) + '%';
                }
            }

            window.adjustZoom = function (delta) {
                applyScale(currentScale + delta);
            };

            window.resetZoom = function () {
                applyScale(1);
            };

            window.fitToWidth = function () {
                if (!sheet) return;
                const naturalWidth = {{ $isLandscape ? 1122 : 794 }}; // A4 pixel approx at 96dpi (297mm/210mm)
                const availableWidth = window.innerWidth - 32;
                if (availableWidth < naturalWidth) {
                    applyScale(availableWidth / naturalWidth);
                } else {
                    applyScale(1);
                }
            };

            // Otomatis sesuaikan zoom jika layar monitor lebih kecil dari kertas
            window.addEventListener('DOMContentLoaded', function () {
                const naturalWidth = {{ $isLandscape ? 1122 : 794 }};
                if (window.innerWidth < naturalWidth + 40) {
                    window.fitToWidth();
                }
            });
        })();
    </script>
</body>
</html>
