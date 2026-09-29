<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Absensi Siswa') - Sistem Presensi Sekolah</title>
    @if(!empty($schoolSetting->favicon) && \Illuminate\Support\Facades\Storage::disk('public')->exists($schoolSetting->favicon))
        <link rel="icon" href="{{ asset('storage/' . $schoolSetting->favicon) }}">
    @elseif(!empty($schoolSetting->logo) && \Illuminate\Support\Facades\Storage::disk('public')->exists($schoolSetting->logo))
        <link rel="icon" href="{{ asset('storage/' . $schoolSetting->logo) }}">
    @endif
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|space-grotesk:600,700" rel="stylesheet" />
    @vite(['resources/css/app.css'])
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F0F4F8;
            background-image: radial-gradient(#CBD5E1 1.5px, transparent 1.5px);
            background-size: 22px 22px;
        }
        .font-heading {
            font-family: 'Space Grotesk', sans-serif;
        }
    </style>
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script>
        if (typeof Swal === 'undefined') {
            document.write('<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"><\/script>');
        }
    </script>
    @stack('styles')
</head>
<body class="min-h-screen text-slate-900 flex flex-col antialiased selection:bg-[#FFD43B] selection:text-black">
    @unless(View::hasSection('hide_header'))
    <!-- Navbar Neobrutalism -->
    <header class="bg-white border-b-4 border-black sticky top-0 z-40 shadow-[0_4px_0px_0px_rgba(0,0,0,0.06)]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 h-18 sm:h-20 flex items-center justify-between gap-3">
            <!-- Brand Logo -->
            <div class="flex items-center gap-3">
                <a href="{{ route('siswa.dashboard') }}" class="flex items-center gap-2.5 group">
                    @if(!empty($schoolSetting->logo) && \Illuminate\Support\Facades\Storage::disk('public')->exists($schoolSetting->logo))
                        <div class="w-10 h-10 sm:w-11 sm:h-11 bg-white rounded-xl border-2 border-black shadow-[2.5px_2.5px_0px_0px_#000] p-1 flex items-center justify-center overflow-hidden shrink-0 group-hover:translate-x-0.5 group-hover:translate-y-0.5 group-hover:shadow-[1px_1px_0px_0px_#000] transition-all">
                            <img src="{{ asset('storage/' . $schoolSetting->logo) }}" alt="Logo {{ $schoolSetting->school_name ?? 'Sekolah' }}" class="w-full h-full object-contain">
                        </div>
                    @else
                        <div class="w-10 h-10 sm:w-11 sm:h-11 bg-[#5294FF] text-white font-heading font-black text-xl sm:text-2xl flex items-center justify-center rounded-xl border-2 border-black shadow-[2.5px_2.5px_0px_0px_#000] group-hover:translate-x-0.5 group-hover:translate-y-0.5 group-hover:shadow-[1px_1px_0px_0px_#000] transition-all shrink-0">
                            {{ !empty($schoolSetting->school_name) ? strtoupper(substr($schoolSetting->school_name, 0, 1)) : 'S' }}
                        </div>
                    @endif
                    <div>
                        <div class="font-heading font-black text-base sm:text-lg tracking-tight leading-none text-black flex items-center gap-1.5">
                            ABSENSI<span class="bg-[#FFD43B] text-black px-1.5 py-0.5 rounded-md border-2 border-black text-[11px] font-black uppercase shadow-[1.5px_1.5px_0px_0px_#000]">PORTAL SISWA</span>
                        </div>
                        <div class="text-[10px] font-bold text-slate-500 tracking-wider uppercase mt-0.5 truncate max-w-[150px] sm:max-w-xs">
                            {{ $schoolSetting->school_name ?? 'Portal Pelajar' }}
                        </div>
                    </div>
                </a>
            </div>

            <!-- Right Header: Date, Clock & User Status -->
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <!-- Date & Live Clock (Waktu Indonesia) -->
                @include('partials._header-clock')

                @auth
                <!-- Student Status Indicator -->
                <div class="hidden md:flex items-center gap-2 bg-[#FFF9DB] border-2 border-black px-3 py-1.5 shadow-[2px_2px_0px_0px_#000] rounded-xl">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#20C997] border border-black animate-pulse"></span>
                    <span class="text-xs font-black text-black truncate max-w-[120px]">{{ Auth::user()->name }}</span>
                    <span class="text-[9px] font-black uppercase px-1.5 py-0.5 bg-[#20C997] text-emerald-950 border border-black rounded shadow-[1px_1px_0px_0px_#000]">
                        Siswa
                    </span>
                </div>

                <form action="{{ route('logout') }}" method="POST" class="shrink-0">
                    @csrf
                    <button type="submit" class="w-8 h-8 sm:w-auto sm:px-3 sm:py-1.5 rounded-xl border-2 border-black bg-[#FF6B6B] hover:bg-rose-500 text-white font-black text-xs uppercase tracking-wider flex items-center justify-center gap-1.5 shadow-[2px_2px_0px_0px_#000] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-none transition-all cursor-pointer" title="Keluar Akun">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        <span class="hidden sm:inline">Keluar</span>
                    </button>
                </form>
                @endauth
            </div>
        </div>
    </header>
    @endunless

    <!-- Main Content Wrapper -->
    <main class="flex-1 max-w-5xl w-full mx-auto px-3 sm:px-6 py-4 sm:py-6">
        <!-- Flash Message Alerts with Auto-Dismiss -->
        @if(session('success'))
        <div id="flashAlertSuccess" class="mb-4 bg-[#D3F9D8] border-3 border-black p-3.5 sm:p-4 rounded-2xl shadow-[4px_4px_0px_0px_#000] flex items-center justify-between gap-3 text-emerald-950 font-bold transition-all duration-500">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-[#20C997] border-2 border-black flex items-center justify-center font-black text-black shrink-0 shadow-[1.5px_1.5px_0px_0px_#000]">✓</span>
                <span class="text-xs sm:text-sm font-black">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="dismissFlashAlert('flashAlertSuccess')" class="text-black font-black text-lg hover:opacity-75 cursor-pointer leading-none p-1">✕</button>
        </div>
        <script>
            setTimeout(() => {
                dismissFlashAlert('flashAlertSuccess');
            }, 4000);
        </script>
        @endif

        @if(session('error'))
        <div id="flashAlertError" class="mb-4 bg-[#FFE3E3] border-3 border-black p-3.5 sm:p-4 rounded-2xl shadow-[4px_4px_0px_0px_#000] flex items-center justify-between gap-3 text-rose-950 font-bold transition-all duration-500">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-[#FF6B6B] border-2 border-black flex items-center justify-center font-black text-white shrink-0 shadow-[1.5px_1.5px_0px_0px_#000]">!</span>
                <span class="text-xs sm:text-sm font-black">{{ session('error') }}</span>
            </div>
            <button type="button" onclick="dismissFlashAlert('flashAlertError')" class="text-black font-black text-lg hover:opacity-75 cursor-pointer leading-none p-1">✕</button>
        </div>
        <script>
            setTimeout(() => {
                dismissFlashAlert('flashAlertError');
            }, 6000);
        </script>
        @endif

        <script>
            function dismissFlashAlert(id) {
                const el = document.getElementById(id);
                if (el) {
                    el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                    el.style.opacity = '0';
                    el.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        if (el && el.parentNode) {
                            el.parentNode.removeChild(el);
                        }
                    }, 400);
                }
            }
        </script>

        @yield('content')
    </main>

    @unless(request()->routeIs('siswa.*'))
    <!-- Footer Neobrutalism -->
    <footer class="pt-6 pb-20 sm:pb-22 text-center text-xs font-bold text-slate-500">
        <div class="max-w-5xl mx-auto px-4 flex items-center justify-center gap-2 text-[11px]">
            <span>{{ $schoolSetting->school_name ?? 'Sistem Presensi' }}</span>
            <span>•</span>
            <span class="px-1.5 py-0.5 bg-[#FFD43B] text-black border border-black font-mono font-black text-[10px] rounded shadow-[1px_1px_0px_0px_#000]">2026/2027</span>
        </div>
    </footer>
    @endunless
    @stack('scripts')
</body>
</html>
