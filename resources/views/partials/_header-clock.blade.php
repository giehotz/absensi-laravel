<div class="flex items-center gap-1.5 sm:gap-2">
    <!-- Date Pill (Waktu Indonesia) -->
    <div class="hidden sm:flex items-center gap-1.5 bg-[#FFF9DB] text-slate-900 border-2 border-black px-2.5 sm:px-3 py-1 text-xs font-mono font-bold shadow-[2px_2px_0px_0px_#000]" title="Tanggal Hari Ini (WIB)">
        <svg class="w-3.5 h-3.5 text-slate-700 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
        </svg>
        <span class="live-clock-date">{{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('l, d M Y') }}</span>
    </div>

    <!-- Live Clock Pill (Jam Waktu Indonesia Barat) -->
    <div class="flex items-center gap-1.5 bg-[#E7F5FF] text-black border-2 border-black px-2.5 sm:px-3 py-1 text-xs font-mono font-bold shadow-[2px_2px_0px_0px_#000]" title="Waktu Indonesia Barat (WIB)">
        <svg class="w-3.5 h-3.5 text-[#1971C2] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="9" stroke-width="2.2"></circle>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 7v5l3 2"></path>
        </svg>
        <span class="live-clock-time tracking-wider">{{ \Carbon\Carbon::now('Asia/Jakarta')->format('H:i:s') }}</span>
        <span class="bg-[#339AF0] text-white text-[10px] font-black px-1 py-0.2 border border-black leading-tight rounded-none shadow-[1px_1px_0px_0px_#000]">WIB</span>
    </div>
</div>

<script>
    (function() {
        if (window.__indoClockInitialized) return;
        window.__indoClockInitialized = true;

        function updateIndonesianClock() {
            try {
                const now = new Date();
                const timeFormatter = new Intl.DateTimeFormat('id-ID', {
                    timeZone: 'Asia/Jakarta',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false
                });
                const formattedTime = timeFormatter.format(now).replace(/\./g, ':');
                document.querySelectorAll('.live-clock-time').forEach(function(el) {
                    el.textContent = formattedTime;
                });

                const dateFormatter = new Intl.DateTimeFormat('id-ID', {
                    timeZone: 'Asia/Jakarta',
                    weekday: 'long',
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric'
                });
                const formattedDate = dateFormatter.format(now);
                document.querySelectorAll('.live-clock-date').forEach(function(el) {
                    el.textContent = formattedDate;
                });
            } catch (e) {
                const now = new Date();
                const pad = function(n) { return String(n).padStart(2, '0'); };
                const fallbackTime = pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds());
                document.querySelectorAll('.live-clock-time').forEach(function(el) {
                    el.textContent = fallbackTime;
                });
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                updateIndonesianClock();
                setInterval(updateIndonesianClock, 1000);
            });
        } else {
            updateIndonesianClock();
            setInterval(updateIndonesianClock, 1000);
        }
    })();
</script>
