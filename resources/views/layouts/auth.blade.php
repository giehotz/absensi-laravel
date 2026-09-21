<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Masuk ke Sistem') - {{ $schoolSetting->school_name ?? config('app.name', 'Sistem Presensi') }}</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|space-grotesk:600,700,800" rel="stylesheet" />
    
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
    </style>
    
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script>
        if (typeof Swal === 'undefined') {
            document.write('<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"><\/script>');
        }
    </script>
    
    @stack('styles')
</head>
<body class="min-h-screen text-slate-900 flex flex-col justify-between antialiased selection:bg-[#FFD43B] selection:text-black">
    <!-- Main Auth Content Area (Tanpa Header Navbar) -->
    <main class="flex-1 flex flex-col items-center justify-center p-4 sm:p-6 w-full">
        @yield('content')
    </main>

    <!-- Minimalist Clean Footer -->
    <footer class="py-4 text-center text-xs font-semibold text-slate-600">
        <div class="max-w-md mx-auto px-4 flex items-center justify-center gap-2">
            <span>© {{ date('Y') }} {{ $schoolSetting->school_name ?? 'Sistem Presensi Sekolah' }}</span>
            <span>•</span>
            <a href="{{ url('/') }}" class="font-bold text-slate-800 hover:text-blue-600 underline">Beranda</a>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
