{{-- Komponen Persistensi Scroll Sidebar & Auto-Scroll Menu Aktif --}}
<script>
    (function() {
        const portal = '{{ $portalKey ?? "default" }}';
        const storageKey = 'sidebar_scroll_pos_{{ $portalKey ?? "default" }}';
        const nav = document.getElementById('sidebarNav') || document.querySelector('#sidebar nav') || document.querySelector('.sidebar-scroll');
        if (!nav) return;

        // 1. Pulihkan posisi scroll yang tersimpan
        const savedPos = sessionStorage.getItem(storageKey);
        if (savedPos !== null && !isNaN(savedPos)) {
            nav.scrollTop = parseInt(savedPos, 10);
        }

        // 2. Pastikan menu aktif terlihat di dalam viewport navigasi
        const activeItem = nav.querySelector('[aria-current="page"]') || 
                           nav.querySelector('a.bg-\\[\\#FFD43B\\]') || 
                           nav.querySelector('.active-menu-item');
        if (activeItem) {
            const navRect = nav.getBoundingClientRect();
            const itemRect = activeItem.getBoundingClientRect();
            if (itemRect.top < navRect.top || itemRect.bottom > navRect.bottom) {
                activeItem.scrollIntoView({ block: 'nearest', behavior: 'auto' });
            }
        }

        // 3. Simpan posisi scroll secara pasif saat user menggulir
        let scrollTimeout;
        nav.addEventListener('scroll', function() {
            if (scrollTimeout) cancelAnimationFrame(scrollTimeout);
            scrollTimeout = requestAnimationFrame(function() {
                sessionStorage.setItem(storageKey, nav.scrollTop);
            });
        }, { passive: true });

        // 4. Tangani klik menu: Simpan posisi saat ini atau reset jika klik Dashboard
        nav.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (!link) return;

            const href = link.getAttribute('href') || '';
            const isDashboard = href.endsWith('/dashboard') || href.endsWith('/admin') || href.endsWith('/guru') || href.endsWith('/mbg');
            if (isDashboard) {
                sessionStorage.setItem(storageKey, '0');
            } else {
                sessionStorage.setItem(storageKey, nav.scrollTop);
            }
        });

        // 5. Simpan posisi sebelum halaman berpindah (fallback)
        window.addEventListener('beforeunload', function() {
            sessionStorage.setItem(storageKey, nav.scrollTop);
        });
    })();
</script>
