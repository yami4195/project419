document.addEventListener('DOMContentLoaded', function () {
    // --- 1. Sidebar Toggle ---
    const menuBtn = document.getElementById('menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');

    if (menuBtn && sidebar) {
        menuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('mobile-active');
        });
    }

    // --- 2. Chart Initialization ---
    // These would typically be populated from the PHP backend
    const ctx = document.getElementById('performanceChart');
    if (ctx && typeof performanceData !== 'undefined') {
        new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: performanceData.labels,
                datasets: [{
                    label: 'Avg Score %',
                    data: performanceData.scores,
                    backgroundColor: 'rgba(79, 70, 229, 0.8)',
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 12,
                        cornerRadius: 8
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: { color: '#f1f5f9' },
                        ticks: { color: '#64748b' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748b' }
                    }
                }
            }
        });
    }

    // --- 3. Participation Chart ---
    const ctx2 = document.getElementById('participationChart');
    if (ctx2 && typeof participationData !== 'undefined') {
        new Chart(ctx2.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: participationData.labels,
                datasets: [{
                    data: participationData.counts,
                    backgroundColor: [
                        '#4f46e5',
                        '#10b981',
                        '#f59e0b',
                        '#f43f5e',
                        '#8b5cf6'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: { size: 12 }
                        }
                    }
                }
            }
        });
    }
});
