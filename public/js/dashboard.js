document.addEventListener('DOMContentLoaded', function () {
    // --- 1. Performance Chart Initialization ---
    const ctx = document.getElementById('performanceChart');
    if (ctx) {
        new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: '#1e293b',
                        padding: 12,
                        cornerRadius: 8,
                        titleFont: { size: 14, weight: 'bold' },
                        bodyFont: { size: 13 }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#64748b',
                            font: { size: 12 }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            color: '#e2e8f0',
                            borderDash: [5, 5]
                        },
                        ticks: {
                            color: '#64748b',
                            font: { size: 12 },
                            callback: function (value) {
                                return value + '%';
                            }
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });
    }

    // --- 2. Sidebar & Modal Logic ---
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebar-toggle');
    const closeBtn = document.getElementById('sidebar-close');
    const examModal = document.getElementById('examModal');
    const takeExamBtn = document.getElementById('takeExamBtn');
    const closeModalBtn = document.querySelector('.close-modal');

    // Sidebar
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.add('active');
        });
    }

    if (closeBtn && sidebar) {
        closeBtn.addEventListener('click', () => {
            sidebar.classList.remove('active');
        });
    }

    // Exam Modal
    if (takeExamBtn && examModal) {
        takeExamBtn.addEventListener('click', (e) => {
            e.preventDefault();
            requestAnimationFrame(() => {
                examModal.classList.add('active');
            });
        });
    }

    if (closeModalBtn && examModal) {
        closeModalBtn.addEventListener('click', (e) => {
            e.preventDefault();
            examModal.classList.remove('active');
        });
    }

    // Close on outside click
    document.addEventListener('click', (e) => {
        // Mobile sidebar close
        if (window.innerWidth <= 768) {
            if (sidebar.classList.contains('active') &&
                !sidebar.contains(e.target) &&
                !toggleBtn.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        }

        // Modal close
        if (examModal && examModal.classList.contains('active') && e.target === examModal) {
            examModal.classList.remove('active');
        }
    });

    // --- 3. Hover Effects & Extra Polish ---
    // (Optional: can add more micro-interactions here if needed)
});
