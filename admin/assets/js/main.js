// assets/js/main.js - All JavaScript functionality

// Initialize AOS
AOS.init({
    duration: 600,
    once: true,
    offset: 50
});

// Theme Toggle
const themeToggle = document.getElementById('themeToggle');
const themeIcon = document.getElementById('themeIcon');
const htmlElement = document.documentElement;

// Check for saved theme
const savedTheme = localStorage.getItem('theme') || 'light';
htmlElement.setAttribute('data-theme', savedTheme);
updateThemeIcon(savedTheme);

themeToggle.addEventListener('click', () => {
    const currentTheme = htmlElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    
    htmlElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    updateThemeIcon(newTheme);
    updateChartsTheme(newTheme);
});

function updateThemeIcon(theme) {
    themeIcon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
}

// Mobile Menu
const mobileMenuBtn = document.getElementById('mobileMenuBtn');
const mobileMenuToggle = document.getElementById('mobileMenuToggle');
const sidebar = document.getElementById('sidebar');
const sidebarClose = document.getElementById('sidebarClose');
const overlay = document.getElementById('mobileMenuOverlay');

function openMobileMenu() {
    sidebar.classList.add('active');
    overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeMobileMenu() {
    sidebar.classList.remove('active');
    overlay.classList.remove('active');
    document.body.style.overflow = '';
}

if (mobileMenuBtn) mobileMenuBtn.addEventListener('click', openMobileMenu);
if (mobileMenuToggle) mobileMenuToggle.addEventListener('click', openMobileMenu);
if (sidebarClose) sidebarClose.addEventListener('click', closeMobileMenu);
if (overlay) overlay.addEventListener('click', closeMobileMenu);

// Charts
let revenueChart, shiftChart;

function initCharts() {
    const isDark = htmlElement.getAttribute('data-theme') === 'dark';
    const textColor = isDark ? '#f1f5f9' : '#1e293b';
    const gridColor = isDark ? '#334155' : '#e2e8f0';

    // Revenue Chart
    const ctx1 = document.getElementById('revenueChart')?.getContext('2d');
    if (ctx1) {
        if (revenueChart) revenueChart.destroy();
        
        revenueChart = new Chart(ctx1, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    data: [3200, 4100, 3800, 4250, 4700, 5200, 4900],
                    borderColor: '#4361ee',
                    backgroundColor: 'rgba(67, 97, 238, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#4361ee'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: textColor } },
                    y: { grid: { color: gridColor }, ticks: { color: textColor } }
                }
            }
        });
    }

    // Shift Chart
    const ctx2 = document.getElementById('shiftChart')?.getContext('2d');
    if (ctx2) {
        if (shiftChart) shiftChart.destroy();
        
        shiftChart = new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Morning', 'Evening', 'Full Day'],
                datasets: [{
                    data: [45, 38, 27],
                    backgroundColor: ['#4361ee', '#ffb703', '#06d6a0'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: { legend: { display: false } }
            }
        });
    }
}

function updateChartsTheme(theme) {
    const isDark = theme === 'dark';
    const textColor = isDark ? '#f1f5f9' : '#1e293b';
    const gridColor = isDark ? '#334155' : '#e2e8f0';

    if (revenueChart) {
        revenueChart.options.scales.x.ticks.color = textColor;
        revenueChart.options.scales.y.ticks.color = textColor;
        revenueChart.options.scales.y.grid.color = gridColor;
        revenueChart.update();
    }
}

// Initialize on load
window.addEventListener('load', initCharts);

// Handle resize
window.addEventListener('resize', () => {
    if (revenueChart) revenueChart.resize();
    if (shiftChart) shiftChart.resize();
});