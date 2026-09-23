<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal Guru') - Absensi Siswa</title>
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
            background-color: #F0F3F8;
            background-image: radial-gradient(#CBD5E1 1.5px, transparent 1.5px);
            background-size: 24px 24px;
        }
        .font-heading {
            font-family: 'Space Grotesk', sans-serif;
        }
        .sidebar-scroll::-webkit-scrollbar {
            width: 5px;
        }
        .sidebar-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 4px;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: #475569;
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
<body class="min-h-screen text-slate-900 flex antialiased">
    <!-- Mobile Sidebar Backdrop -->
    <div id="mobileBackdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-black/70 z-40 hidden transition-opacity"></div>

    <!-- Hidden utilities for Tailwind compiler -->
    <div class="hidden lg:-translate-x-full lg:translate-x-0 lg:pl-0 lg:pl-64 translate-x-0 -translate-x-full"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-[#0F172A] border-r-4 border-black flex flex-col justify-between transition-all duration-300 ease-in-out -translate-x-full lg:translate-x-0 shadow-[4px_0px_0px_0px_rgba(0,0,0,0.2)]">
        <div class="flex flex-col h-full overflow-hidden">
            <!-- Sidebar Header -->
            <div class="h-20 border-b-4 border-black px-5 flex items-center justify-between bg-[#1E293B] shrink-0">
                <a href="{{ route('guru.dashboard') }}" class="flex items-center gap-3 group">
                    @if(!empty($schoolSetting->logo) && \Illuminate\Support\Facades\Storage::disk('public')->exists($schoolSetting->logo))
                        <div class="w-10 h-10 bg-white rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000000] overflow-hidden flex items-center justify-center p-0.5 shrink-0 group-hover:translate-x-0.5 group-hover:translate-y-0.5 group-hover:shadow-none transition-all">
                            <img src="{{ asset('storage/' . $schoolSetting->logo) }}" alt="Logo {{ $schoolSetting->school_name ?? 'Sekolah' }}" class="w-full h-full object-contain">
                        </div>
                    @else
                        <div class="w-10 h-10 bg-[#5294FF] text-white font-heading font-black text-xl flex items-center justify-center rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000000] group-hover:translate-x-0.5 group-hover:translate-y-0.5 group-hover:shadow-none transition-all shrink-0">
                            {{ !empty($schoolSetting->school_name) ? strtoupper(substr($schoolSetting->school_name, 0, 1)) : 'G' }}
                        </div>
                    @endif
                    <div class="min-w-0">
                        <div class="font-heading font-bold text-base tracking-tight leading-none text-white flex items-center gap-1.5">
                            ABSENSI<span class="bg-[#5294FF] text-white px-1.5 py-0.5 text-[10px] font-black rounded border border-black uppercase shadow-[1px_1px_0px_0px_#000]">Guru</span>
                        </div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1 truncate" title="{{ $schoolSetting->school_name ?? 'Portal Tenaga Pengajar' }}">
                            Portal Tenaga Pengajar
                        </div>
                    </div>
                </a>
                <button onclick="toggleSidebar()" class="lg:hidden text-white font-black text-lg p-1.5 hover:bg-white/10 rounded border border-slate-600 transition-colors">✕</button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 overflow-y-auto sidebar-scroll p-4 space-y-4">
                <!-- Group: Menu Utama -->
                <div>
                    <div class="px-2 mb-1.5 text-[10px] font-black uppercase tracking-wider text-slate-400 font-heading">
                        Menu Utama
                    </div>
                    <div class="space-y-1">
                        <!-- Dashboard -->
                        <a href="{{ route('guru.dashboard') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs transition-all duration-150 cursor-pointer group
                           {{ request()->routeIs('guru.dashboard') 
                                ? 'bg-[#5294FF] text-white border-2 border-black font-black shadow-[3px_3px_0px_0px_#000000] translate-x-1' 
                                : 'text-slate-300 border-2 border-transparent font-bold hover:bg-slate-800/90 hover:text-white hover:border-slate-700' }}">
                            <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('guru.dashboard') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                            </svg>
                            <span>Dashboard</span>
                        </a>

                        <!-- Presensi Manual -->
                        <a href="{{ route('guru.attendance.manual') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs transition-all duration-150 cursor-pointer group
                           {{ request()->routeIs('guru.attendance.manual*') 
                                ? 'bg-[#5294FF] text-white border-2 border-black font-black shadow-[3px_3px_0px_0px_#000000] translate-x-1' 
                                : 'text-slate-300 border-2 border-transparent font-bold hover:bg-slate-800/90 hover:text-white hover:border-slate-700' }}">
                            <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('guru.attendance.manual*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 2l3 3h-3V4zm-4 7a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1zm0 4a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span>Presensi Manual</span>
                        </a>
                    </div>
                </div>

                <!-- Group: Aktivitas & Kelas -->
                <div>
                    <div class="px-2 mb-1.5 text-[10px] font-black uppercase tracking-wider text-slate-400 font-heading">
                        Aktivitas & Kelas
                    </div>
                    <div class="space-y-1">
                        <!-- Jadwal Mengajar -->
                        <a href="{{ route('guru.jadwal') }}" 
                           class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs transition-all duration-150 cursor-pointer group
                           {{ request()->routeIs('guru.jadwal') 
                                ? 'bg-[#FFD43B] text-black border-2 border-black font-black shadow-[3px_3px_0px_0px_#000000] translate-x-1' 
                                : 'text-slate-300 border-2 border-transparent font-bold hover:bg-slate-800/90 hover:text-white hover:border-slate-700' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('guru.jadwal') ? 'text-black' : 'text-slate-400 group-hover:text-white' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                </svg>
                                <span>Jadwal Mengajar</span>
                            </div>
                            <span class="text-[10px] font-black {{ request()->routeIs('guru.jadwal') ? 'bg-black text-yellow-300' : 'bg-[#FFF3BF] text-amber-950' }} px-1.5 py-0.5 rounded border border-black shadow-[1px_1px_0px_0px_#000]">KBM</span>
                        </a>

                        <!-- Kelas Binaan (Wali Kelas) -->
                        <a href="{{ route('guru.classes.binaan') }}" 
                           class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs transition-all duration-150 cursor-pointer group
                           {{ request()->routeIs('guru.classes.*') 
                                ? 'bg-[#5294FF] text-white border-2 border-black font-black shadow-[3px_3px_0px_0px_#000000] translate-x-1' 
                                : 'text-slate-300 border-2 border-transparent font-bold hover:bg-slate-800/90 hover:text-white hover:border-slate-700' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('guru.classes.*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                                </svg>
                                <span>Kelas Binaan</span>
                            </div>
                            <span class="text-[10px] font-black bg-[#D3F9D8] text-emerald-950 px-1.5 py-0.5 rounded border border-black shadow-[1px_1px_0px_0px_#000]">Wali</span>
                        </a>

                        <!-- Perizinan Siswa -->
                        <a href="{{ route('guru.leave-requests.index') }}" 
                           class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs transition-all duration-150 cursor-pointer group
                           {{ request()->routeIs('guru.leave-requests.*') 
                                ? 'bg-[#5294FF] text-white border-2 border-black font-black shadow-[3px_3px_0px_0px_#000000] translate-x-1' 
                                : 'text-slate-300 border-2 border-transparent font-bold hover:bg-slate-800/90 hover:text-white hover:border-slate-700' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('guru.leave-requests.*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                                </svg>
                                <span>Perizinan Siswa</span>
                            </div>
                            <span class="text-[10px] font-black bg-[#FFE3E3] text-rose-950 px-1.5 py-0.5 rounded border border-black shadow-[1px_1px_0px_0px_#000]">Izin</span>
                        </a>

                        @if(Auth::user()->isSavingsOfficer())
                        <!-- Tabungan Siswa (Bendahara Tabungan) -->
                        <a href="{{ route('guru.savings.index') }}" 
                           onclick="try { const sc = localStorage.getItem('guru_tabungan_selected_class_{{ auth()->id() }}'); if (sc) { this.href = '{{ route('guru.savings.index') }}?class_id=' + encodeURIComponent(sc); } } catch(e) {}"
                           class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs transition-all duration-150 cursor-pointer group
                           {{ request()->routeIs('guru.savings.*') 
                                ? 'bg-[#20C997] text-white border-2 border-black font-black shadow-[3px_3px_0px_0px_#000000] translate-x-1' 
                                : 'text-slate-300 border-2 border-transparent font-bold hover:bg-slate-800/90 hover:text-white hover:border-slate-700' }}">
                            <div class="flex items-center gap-3">
                                <span class="text-sm">💰</span>
                                <span>Tabungan Siswa</span>
                            </div>
                            <span class="text-[10px] font-black {{ request()->routeIs('guru.savings.*') ? 'bg-black text-emerald-300' : 'bg-[#D3F9D8] text-emerald-950' }} px-1.5 py-0.5 rounded border border-black shadow-[1px_1px_0px_0px_#000]">Kas</span>
                        </a>
                        @endif
                    </div>
                </div>


                <!-- Group: Laporan -->
                <div>
                    <div class="px-2 mb-1.5 text-[10px] font-black uppercase tracking-wider text-slate-400 font-heading">
                        Laporan
                    </div>
                    <div class="space-y-1">
                        <!-- Rekap Kehadiran -->
                        <a href="{{ route('guru.reports.attendance') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs transition-all duration-150 cursor-pointer group
                           {{ request()->routeIs('guru.reports.*') 
                                ? 'bg-[#5294FF] text-white border-2 border-black font-black shadow-[3px_3px_0px_0px_#000000] translate-x-1' 
                                : 'text-slate-300 border-2 border-transparent font-bold hover:bg-slate-800/90 hover:text-white hover:border-slate-700' }}">
                            <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('guru.reports.*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                            </svg>
                            <span>Rekap Kehadiran</span>
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Sidebar Footer: High Contrast User Profile Card & Logout -->
            <div class="p-3 border-t-4 border-black bg-[#1E293B] shrink-0 space-y-2.5">
                <!-- Tombol Akses Profil Saya -->
                <a href="{{ route('guru.profile.index') }}" 
                   class="flex items-center gap-2.5 p-2 border-2 border-black rounded-lg transition-all duration-150 cursor-pointer group
                   {{ request()->routeIs('guru.profile.*') 
                        ? 'bg-[#5294FF] text-white shadow-[3px_3px_0px_0px_#000000] translate-x-0.5' 
                        : 'bg-[#0F172A] text-slate-200 hover:bg-slate-800/90 hover:border-slate-600 shadow-[2px_2px_0px_0px_#000]' }}"
                   title="Buka Profil Saya">
                    @if(!empty(Auth::user()->teacher?->photo) && \Illuminate\Support\Facades\Storage::disk('public')->exists(Auth::user()->teacher->photo))
                        <div class="w-8 h-8 rounded-md bg-white border-2 border-black overflow-hidden shrink-0 shadow-[1px_1px_0px_0px_#000]">
                            <img src="{{ asset('storage/' . Auth::user()->teacher->photo) }}" alt="{{ Auth::user()->name }}" class="w-full h-full object-cover">
                        </div>
                    @else
                        <div class="w-8 h-8 rounded-md {{ request()->routeIs('guru.profile.*') ? 'bg-black text-white' : 'bg-[#5294FF] text-white' }} border-2 border-black flex items-center justify-center font-bold text-xs shrink-0 shadow-[1px_1px_0px_0px_#000]">
                            👨‍🏫
                        </div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <div class="text-xs font-bold text-white truncate group-hover:text-yellow-300 transition-colors">
                            {{ Auth::user()->name }}
                        </div>
                        <div class="text-[10px] font-semibold {{ request()->routeIs('guru.profile.*') ? 'text-white' : 'text-[#5294FF]' }} flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#20C997]"></span>
                            <span>Dewan Guru</span>
                        </div>
                    </div>
                    <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-white group-hover:translate-x-0.5 transition-all shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-xs font-bold border-2 border-black text-slate-200 bg-[#0F172A] hover:bg-[#FF6B6B] hover:text-white shadow-[2px_2px_0px_0px_#000] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-none transition-all cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        <span>Keluar Sistem</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div id="mainContent" class="flex-1 flex flex-col min-w-0 transition-all duration-300 ease-in-out lg:pl-64">
        <!-- Topbar -->
        <header class="h-20 bg-white border-b-4 border-black px-4 sm:px-8 flex items-center justify-between sticky top-0 z-30 shadow-[0_4px_0px_0px_rgba(0,0,0,0.05)]">
            <div class="flex items-center gap-3.5">
                <button onclick="toggleSidebar()" class="neo-btn bg-[#FFD43B] p-2 text-black cursor-pointer hover:bg-yellow-400" title="Toggle Sidebar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <div>
                    <div class="hidden sm:flex items-center gap-1.5 text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-0.5">
                        <span class="text-black font-black">Portal Guru</span>
                        <span>/</span>
                        <span>Navigasi</span>
                    </div>
                    <h1 class="font-heading font-black text-lg sm:text-xl text-black leading-tight">
                        @yield('page-title', 'Portal Guru')
                    </h1>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-2.5">
                <!-- Date & Live Clock (Waktu Indonesia) -->
                @include('partials._header-clock')
                
                <!-- Online Badge -->
                <div class="neo-badge bg-[#20C997] text-white flex items-center gap-1.5 px-2.5 py-1">
                    <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                    <span class="hidden xs:inline">Aktif</span>
                </div>
            </div>
        </header>

        <!-- Page Body -->
        <main class="flex-1 p-4 sm:p-8 max-w-7xl w-full mx-auto">
            <!-- Flash Alerts via SweetAlert2 -->
            @if(session('success'))
            <script>
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: @json(session('success')),
                        confirmButtonColor: '#20C997',
                        timer: 3000,
                        timerProgressBar: true,
                    });
                } else {
                    alert(@json(session('success')));
                }
            </script>
            @endif

            @if(session('error'))
            <script>
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: @json(session('error')),
                        confirmButtonColor: '#FF6B6B',
                    });
                } else {
                    alert(@json(session('error')));
                }
            </script>
            @endif

            @if($errors->any())
            <div class="mb-6 bg-[#FFE3E3] border-3 border-black p-4 neo-box text-rose-950 space-y-1">
                <div class="font-black flex items-center gap-2 text-sm">
                    <span>⚠</span> Terjadi kesalahan pengisian data:
                </div>
                <ul class="list-disc list-inside text-xs font-semibold pl-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Modal & Sidebar Helper Scripts -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const backdrop = document.getElementById('mobileBackdrop');
            const isDesktop = window.innerWidth >= 1024;

            if (isDesktop) {
                // Desktop Mode: Toggle between expanded and collapsed
                const isCollapsed = sidebar.classList.contains('lg:-translate-x-full');
                if (isCollapsed) {
                    sidebar.classList.remove('lg:-translate-x-full');
                    sidebar.classList.add('lg:translate-x-0');
                    mainContent.classList.remove('lg:pl-0');
                    mainContent.classList.add('lg:pl-64');
                    localStorage.setItem('guru_sidebar_desktop_collapsed', 'false');
                } else {
                    sidebar.classList.remove('lg:translate-x-0');
                    sidebar.classList.add('lg:-translate-x-full');
                    mainContent.classList.remove('lg:pl-64');
                    mainContent.classList.add('lg:pl-0');
                    localStorage.setItem('guru_sidebar_desktop_collapsed', 'true');
                }
            } else {
                // Mobile Mode: Toggle drawer overlay
                const isOpen = sidebar.classList.contains('translate-x-0');
                if (isOpen) {
                    sidebar.classList.remove('translate-x-0');
                    sidebar.classList.add('-translate-x-full');
                    backdrop.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                } else {
                    sidebar.classList.remove('-translate-x-full');
                    sidebar.classList.add('translate-x-0');
                    backdrop.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden');
                }
            }
        }

        // Restore desktop preference on load
        document.addEventListener('DOMContentLoaded', () => {
            if (window.innerWidth >= 1024 && localStorage.getItem('guru_sidebar_desktop_collapsed') === 'true') {
                const sidebar = document.getElementById('sidebar');
                const mainContent = document.getElementById('mainContent');
                if (sidebar && mainContent) {
                    sidebar.classList.remove('lg:translate-x-0');
                    sidebar.classList.add('lg:-translate-x-full');
                    mainContent.classList.remove('lg:pl-64');
                    mainContent.classList.add('lg:pl-0');
                }
            }
        });

        // Clean up mobile state when resizing to desktop
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                const backdrop = document.getElementById('mobileBackdrop');
                if (backdrop) backdrop.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        });

        function openModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }
        }

        function closeModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        }
    </script>
    @stack('scripts')
</body>
</html>

