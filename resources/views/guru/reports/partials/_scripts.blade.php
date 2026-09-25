<style>
@media print {
    aside, header, .print\:hidden {
        display: none !important;
    }
    main {
        padding: 0 !important;
        margin: 0 !important;
        max-width: 100% !important;
    }
    body {
        background: #ffffff !important;
    }
    .shadow-\[4px_4px_0px_0px_\#000\], .shadow-\[3px_3px_0px_0px_\#000\], .shadow-\[2px_2px_0px_0px_\#000\] {
        box-shadow: none !important;
    }
}
</style>

<!-- Chart.js CDN & Neobrutalism Chart Initializer -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Tab Switching Logic
    function switchTab(tabName) {
        const summaryPanel = document.getElementById('tab-panel-summary');
        const logsPanel = document.getElementById('tab-panel-logs');
        const summaryBtn = document.getElementById('tab-btn-summary');
        const logsBtn = document.getElementById('tab-btn-logs');
        const searchTabInput = document.getElementById('search-tab-input');

        if (searchTabInput) {
            searchTabInput.value = tabName;
        }

        if (tabName === 'summary') {
            summaryPanel.classList.remove('hidden');
            logsPanel.classList.add('hidden');

            summaryBtn.className = "px-4 py-2 text-xs font-bold border-2 border-black rounded cursor-pointer transition-all bg-black text-white shadow-[2px_2px_0px_0px_#000]";
            logsBtn.className = "px-4 py-2 text-xs font-bold border-2 border-black rounded cursor-pointer transition-all bg-white text-black hover:bg-slate-200";
        } else {
            summaryPanel.classList.add('hidden');
            logsPanel.classList.remove('hidden');

            logsBtn.className = "px-4 py-2 text-xs font-bold border-2 border-black rounded cursor-pointer transition-all bg-black text-white shadow-[2px_2px_0px_0px_#000]";
            summaryBtn.className = "px-4 py-2 text-xs font-bold border-2 border-black rounded cursor-pointer transition-all bg-white text-black hover:bg-slate-200";
        }

        const url = new URL(window.location);
        url.searchParams.set('tab', tabName);
        window.history.replaceState({}, '', url);
    }

    document.addEventListener('DOMContentLoaded', function () {
        const neobrutalismTooltip = {
            backgroundColor: '#ffffff',
            titleColor: '#000000',
            bodyColor: '#000000',
            borderColor: '#000000',
            borderWidth: 2,
            padding: 10,
            cornerRadius: 4,
            titleFont: { family: 'Plus Jakarta Sans', weight: 'bold', size: 12 },
            bodyFont: { family: 'Plus Jakarta Sans', weight: '600', size: 11 },
            displayColors: true,
            boxWidth: 10,
            boxHeight: 10,
            boxPadding: 4,
            callbacks: {
                label: function(context) {
                    let label = context.dataset.label || context.label || '';
                    if (label) {
                        label += ': ';
                    }
                    if (context.parsed.y !== undefined) {
                        label += context.parsed.y;
                    } else if (context.parsed !== undefined) {
                        label += context.parsed;
                    }
                    return label;
                }
            }
        };

        // Donut Chart
        const donutCtx = document.getElementById('donutChart').getContext('2d');
        const donutData = @json($donutChartData);
        const hasDonutData = donutData.data.some(val => val > 0);

        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: hasDonutData ? donutData.labels : ['Belum Ada Data'],
                datasets: [{
                    data: hasDonutData ? donutData.data : [1],
                    backgroundColor: hasDonutData ? donutData.colors : ['#e2e8f0'],
                    borderColor: '#000000',
                    borderWidth: 2.5,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: { display: false },
                    tooltip: hasDonutData ? neobrutalismTooltip : { enabled: false }
                }
            }
        });

        // Bar Chart
        const barCtx = document.getElementById('barChart').getContext('2d');
        const barData = @json($barChartData);

        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: barData.labels,
                datasets: [
                    {
                        label: 'Hadir',
                        data: barData.hadir,
                        backgroundColor: '#20C997',
                        borderColor: '#000000',
                        borderWidth: 2,
                        borderRadius: 3
                    },
                    {
                        label: 'Terlambat',
                        data: barData.terlambat,
                        backgroundColor: '#FFD43B',
                        borderColor: '#000000',
                        borderWidth: 2,
                        borderRadius: 3
                    },
                    {
                        label: 'Izin/Sakit',
                        data: barData.izin_sakit,
                        backgroundColor: '#74C0FC',
                        borderColor: '#000000',
                        borderWidth: 2,
                        borderRadius: 3
                    },
                    {
                        label: 'Alpa',
                        data: barData.alpa,
                        backgroundColor: '#FF6B6B',
                        borderColor: '#000000',
                        borderWidth: 2,
                        borderRadius: 3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    x: {
                        grid: { color: '#e2e8f0', tickColor: '#000000' },
                        ticks: { color: '#000000', font: { family: 'Plus Jakarta Sans', weight: 'bold', size: 10 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { stepSize: 1, color: '#000000', font: { family: 'Plus Jakarta Sans', weight: 'bold', size: 10 } }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...neobrutalismTooltip,
                        callbacks: {
                            ...neobrutalismTooltip.callbacks,
                            title: function(tooltipItems) {
                                if (!tooltipItems.length) return '';
                                const idx = tooltipItems[0].dataIndex;
                                return (barData.full_dates && barData.full_dates[idx]) ? barData.full_dates[idx] : tooltipItems[0].label;
                            }
                        }
                    }
                }
            }
        });
    });
</script>
