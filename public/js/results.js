document.addEventListener('DOMContentLoaded', function () {
    // --- 1. Results Trend Chart ---
    const ctx = document.getElementById('resultsTrendChart');
    if (ctx && typeof trendData !== 'undefined') {
        new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: trendData.labels,
                datasets: [{
                    label: 'Score %',
                    data: trendData.scores,
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#4f46e5',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: '#1e293b',
                        padding: 12,
                        cornerRadius: 8
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748b' }
                    },
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: { color: '#e2e8f0', borderDash: [5, 5] },
                        ticks: {
                            color: '#64748b',
                            callback: value => value + '%'
                        }
                    }
                }
            }
        });
    }
});
