<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - Absensi Siswa</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|space-grotesk:600,700" rel="stylesheet" />
    @vite(['resources/css/app.css'])
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F4F6FB;
        }
        .font-heading {
            font-family: 'Space Grotesk', sans-serif;
        }
    </style>
</head>
<body class="min-h-screen text-slate-900 flex antialiased">
    <!-- Mobile Sidebar Backdrop -->
    <div id="mobileBackdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-black/60 z-40 hidden transition-opacity"></div>

    <!-- Hidden utilities for Tailwind compiler -->
    <div class="hidden lg:-translate-x-full lg:translate-x-0 lg:pl-0 lg:pl-64 translate-x-0 -translate-x-full"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r-2 border-black flex flex-col justify-between transition-all duration-300 ease-in-out -translate-x-full lg:translate-x-0">
        <div>
            <!-- Sidebar Header -->
            <div class="h-20 border-b-2 border-black px-6 flex items-center justify-between bg-white">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5">
                    <div class="w-10 h-10 bg-black text-white font-heading font-black text-xl flex items-center justify-center rounded-lg border-2 border-black">
                        A
                    </div>
                    <div>
                        <div class="font-heading font-bold text-base tracking-tight leading-none text-black flex items-center gap-1.5">
                            ABSENSI<span class="bg-[#FF6B6B] text-white px-1.5 py-0.5 text-[10px] font-black rounded border border-black uppercase">Admin</span>
                        </div>
                        <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mt-0.5">Panel Sekolah</div>
                    </div>
                </a>
                <button onclick="toggleSidebar()" class="lg:hidden text-black font-black text-xl hover:opacity-75">✕</button>
            </div>

            <!-- Navigation Links -->
            <nav class="p-4 space-y-1.5">
                <!-- Dashboard -->
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-lg text-sm transition-all duration-150 cursor-pointer group
                   {{ request()->routeIs('admin.dashboard') 
                        ? 'border-2 border-black bg-white text-black font-bold' 
                        : 'border-2 border-transparent text-slate-800 font-medium hover:border-black hover:bg-white hover:text-black' }}">
                    <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('admin.dashboard') ? 'text-black' : 'text-slate-900 group-hover:text-black' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                    </svg>
                    <span>Dashboard</span>
                </a>

                <!-- Data Guru -->
                <a href="{{ route('admin.teachers.index') }}" 
                   class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-lg text-sm transition-all duration-150 cursor-pointer group
                   {{ request()->routeIs('admin.teachers.*') 
                        ? 'border-2 border-black bg-white text-black font-bold' 
                        : 'border-2 border-transparent text-slate-800 font-medium hover:border-black hover:bg-white hover:text-black' }}">
                    <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('admin.teachers.*') ? 'text-black' : 'text-slate-900 group-hover:text-black' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                    </svg>
                    <span>Data Guru</span>
                </a>

                <!-- Data Kelas -->
                <a href="{{ route('admin.classes.index') }}" 
                   class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-lg text-sm transition-all duration-150 cursor-pointer group
                   {{ request()->routeIs('admin.classes.*') 
                        ? 'border-2 border-black bg-white text-black font-bold' 
                        : 'border-2 border-transparent text-slate-800 font-medium hover:border-black hover:bg-white hover:text-black' }}">
                    <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('admin.classes.*') ? 'text-black' : 'text-slate-900 group-hover:text-black' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                    <span>Data Kelas</span>
                </a>

                <!-- Data Siswa & QR -->
                <a href="{{ route('admin.students.index') }}" 
                   class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-lg text-sm transition-all duration-150 cursor-pointer group
                   {{ request()->routeIs('admin.students.*') 
                        ? 'border-2 border-black bg-white text-black font-bold' 
                        : 'border-2 border-transparent text-slate-800 font-medium hover:border-black hover:bg-white hover:text-black' }}">
                    <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('admin.students.*') ? 'text-black' : 'text-slate-900 group-hover:text-black' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a10.973 10.973 0 00-.074 1.949v2.5a1 1 0 00.553.894l4 2a1 1 0 00.894 0l4-2a1 1 0 00.553-.894V10c0-.663-.025-1.317-.074-1.949l2.644-1.131a1 1 0 000-1.84l-7-3zM4.77 7.02L10 9.26l5.23-2.24L10 4.78 4.77 7.02z"/>
                    </svg>
                    <span>Data Siswa & QR</span>
                </a>

                <!-- Mata Pelajaran -->
                <a href="{{ route('admin.subjects.index') }}" 
                   class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-lg text-sm transition-all duration-150 cursor-pointer group
                   {{ request()->routeIs('admin.subjects.*') 
                        ? 'border-2 border-black bg-white text-black font-bold' 
                        : 'border-2 border-transparent text-slate-800 font-medium hover:border-black hover:bg-white hover:text-black' }}">
                    <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('admin.subjects.*') ? 'text-black' : 'text-slate-900 group-hover:text-black' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/>
                    </svg>
                    <span>Mata Pelajaran</span>
                </a>

                <!-- Laporan Presensi -->
                <a href="{{ route('admin.reports.attendance') }}" 
                   class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-lg text-sm transition-all duration-150 cursor-pointer group
                   {{ request()->routeIs('admin.reports.*') 
                        ? 'border-2 border-black bg-white text-black font-bold' 
                        : 'border-2 border-transparent text-slate-800 font-medium hover:border-black hover:bg-white hover:text-black' }}">
                    <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('admin.reports.*') ? 'text-black' : 'text-slate-900 group-hover:text-black' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"/>
                        <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"/>
                    </svg>
                    <span>Laporan Presensi</span>
                </a>

                <!-- Pengaturan -->
                <a href="{{ route('admin.settings.index') }}" 
                   class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-lg text-sm transition-all duration-150 cursor-pointer group
                   {{ request()->routeIs('admin.settings.*') 
                        ? 'border-2 border-black bg-white text-black font-bold' 
                        : 'border-2 border-transparent text-slate-800 font-medium hover:border-black hover:bg-white hover:text-black' }}">
                    <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('admin.settings.*') ? 'text-black' : 'text-slate-900 group-hover:text-black' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                    </svg>
                    <span>Pengaturan</span>
                </a>
            </nav>
        </div>

        <!-- Sidebar Footer -->
        <div class="p-4 border-t-2 border-black bg-white space-y-3">
            <div class="flex items-center gap-2.5 px-2">
                <div class="w-8 h-8 rounded-full bg-[#FFD43B] border-2 border-black flex items-center justify-center font-bold text-xs">
                    👑
                </div>
                <div class="truncate">
                    <div class="text-xs font-bold text-black truncate">{{ Auth::user()->name }}</div>
                    <div class="text-[10px] font-semibold text-slate-500">Administrator</div>
                </div>
            </div>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-3.5 py-2 rounded-lg text-xs font-bold border-2 border-transparent text-slate-700 hover:border-black hover:text-black hover:bg-white transition-all duration-150 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div id="mainContent" class="flex-1 flex flex-col min-w-0 transition-all duration-300 ease-in-out lg:pl-64">
        <!-- Topbar -->
        <header class="h-20 bg-white border-b-4 border-black px-4 sm:px-8 flex items-center justify-between sticky top-0 z-30">
            <div class="flex items-center gap-3">
                <button onclick="toggleSidebar()" class="neo-btn bg-[#FFD43B] p-2 text-black cursor-pointer" title="Toggle Sidebar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <h1 class="font-heading font-black text-lg sm:text-xl text-black">
                    @yield('page-title', 'Panel Admin')
                </h1>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-xs font-mono font-bold bg-[#E7F5FF] text-blue-900 border-2 border-black px-2.5 py-1 hidden sm:inline-block">
                    {{ \Carbon\Carbon::now()->translatedFormat('d M Y') }}
                </span>
                <span class="neo-badge bg-[#20C997] text-white">Online</span>
            </div>
        </header>

        <!-- Page Body -->
        <main class="flex-1 p-4 sm:p-8 max-w-7xl w-full mx-auto">
            <!-- Flash Alerts -->
            @if(session('success'))
            <div class="mb-6 bg-[#D3F9D8] border-3 border-black p-4 neo-box flex items-center justify-between gap-3 text-emerald-950 font-bold">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-[#20C997] border-2 border-black flex items-center justify-center font-black text-black">✓</span>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-black font-black text-xl hover:opacity-75">✕</button>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-6 bg-[#FFE3E3] border-3 border-black p-4 neo-box flex items-center justify-between gap-3 text-rose-950 font-bold">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-[#FF6B6B] border-2 border-black flex items-center justify-center font-black text-white">!</span>
                    <span>{{ session('error') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-black font-black text-xl hover:opacity-75">✕</button>
            </div>
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
                    localStorage.setItem('sidebar_desktop_collapsed', 'false');
                } else {
                    sidebar.classList.remove('lg:translate-x-0');
                    sidebar.classList.add('lg:-translate-x-full');
                    mainContent.classList.remove('lg:pl-64');
                    mainContent.classList.add('lg:pl-0');
                    localStorage.setItem('sidebar_desktop_collapsed', 'true');
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
            if (window.innerWidth >= 1024 && localStorage.getItem('sidebar_desktop_collapsed') === 'true') {
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
