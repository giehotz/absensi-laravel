<script>
document.addEventListener('DOMContentLoaded', function () {
    const previewUrl = @json(route('ujian.jenis.pemetaan-hari.preview'));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('input[name="_token"]')?.value;

    const debounceTimers = { bawah: null, atas: null };

    function syncSessionRows(kelompok) {
        const selectJumlah = document.getElementById(kelompok + '_jumlah_sesi');
        const selectTerakhir = document.getElementById(kelompok + '_sesi_hari_terakhir');
        if (!selectJumlah || !selectTerakhir) return;

        const count = parseInt(selectJumlah.value, 10) || 2;
        const container = document.getElementById('container_jam_' + kelompok);
        if (container) {
            const rows = container.querySelectorAll('.session-row-' + kelompok);
            rows.forEach(row => {
                const sesi = parseInt(row.getAttribute('data-sesi'), 10);
                if (sesi <= count) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
        }

        // Clamp & disable opsi sesi hari terakhir yang melebihi jumlah sesi
        Array.from(selectTerakhir.options).forEach(opt => {
            const val = parseInt(opt.value, 10);
            if (val > count) {
                opt.disabled = true;
                opt.hidden = true;
            } else {
                opt.disabled = false;
                opt.hidden = false;
            }
        });

        if (parseInt(selectTerakhir.value, 10) > count) {
            selectTerakhir.value = count;
        }
    }

    function collectPreviewPayload(kelompok) {
        const form = document.getElementById('form_pemetaan_' + kelompok);
        if (!form) return null;

        const formData = new FormData(form);
        const rawLibur = formData.get('tanggal_libur') || '';
        const parsedLibur = rawLibur.split(/[\r\n,]+/)
            .map(s => s.trim())
            .filter(s => s.length > 0);

        const count = parseInt(formData.get('jumlah_sesi_per_hari') || '2', 10);
        const jamSesi = [];
        for (let i = 0; i < count; i++) {
            const sesi = formData.get(`jam_sesi[${i}][sesi]`);
            const mulai = formData.get(`jam_sesi[${i}][mulai]`);
            const selesai = formData.get(`jam_sesi[${i}][selesai]`);
            if (sesi && mulai && selesai) {
                jamSesi.push({ sesi: parseInt(sesi, 10), mulai, selesai });
            }
        }

        return {
            _token: csrfToken,
            ujian_periode_id: formData.get('ujian_periode_id'),
            kelompok: kelompok,
            tanggal_mulai: formData.get('tanggal_mulai'),
            tanggal_selesai: formData.get('tanggal_selesai'),
            kecualikan_minggu: formData.get('kecualikan_minggu') ? 1 : 0,
            tanggal_libur: parsedLibur,
            jumlah_sesi_per_hari: count,
            sesi_hari_terakhir: parseInt(formData.get('sesi_hari_terakhir') || '1', 10),
            jam_sesi: jamSesi
        };
    }

    function refreshPreview(kelompok) {
        if (debounceTimers[kelompok]) {
            clearTimeout(debounceTimers[kelompok]);
        }

        debounceTimers[kelompok] = setTimeout(async function () {
            const payload = collectPreviewPayload(kelompok);
            if (!payload || !payload.tanggal_mulai || !payload.tanggal_selesai) return;

            try {
                const response = await fetch(previewUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payload)
                });

                if (response.ok) {
                    const data = await response.json();
                    const badge = document.getElementById('badge_preview_' + kelompok);
                    const chips = document.getElementById('chips_preview_' + kelompok);

                    if (badge) {
                        badge.textContent = `${data.hari_efektif} Hari Efektif • ${data.total_slot} Slot Sesi`;
                    }

                    if (chips) {
                        if (data.tanggal && data.tanggal.length > 0) {
                            chips.innerHTML = data.tanggal.map(t =>
                                `<span class="px-2 py-0.5 bg-white border border-black rounded text-[10px] font-bold text-slate-800 shadow-[1px_1px_0px_0px_#000]">${t.nama_hari}, ${t.label}</span>`
                            ).join(' ');
                        } else {
                            chips.innerHTML = '<span class="text-[11px] text-rose-600 font-bold">Tidak ada hari efektif (semua hari Minggu/libur).</span>';
                        }
                    }
                }
            } catch (err) {
                console.warn('Gagal memuat preview pemetaan hari:', err);
            }
        }, 400);
    }

    ['bawah', 'atas'].forEach(kelompok => {
        const form = document.getElementById('form_pemetaan_' + kelompok);
        if (form) {
            syncSessionRows(kelompok);

            const selectJumlah = document.getElementById(kelompok + '_jumlah_sesi');
            if (selectJumlah) {
                selectJumlah.addEventListener('change', () => {
                    syncSessionRows(kelompok);
                    refreshPreview(kelompok);
                });
            }

            form.querySelectorAll('input, select, textarea').forEach(el => {
                el.addEventListener('input', () => refreshPreview(kelompok));
                el.addEventListener('change', () => refreshPreview(kelompok));
            });
        }
    });
});
</script>
