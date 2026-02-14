import './bootstrap';
import { Chart, registerables } from 'chart.js';

// Enregistrer tous les composants de Chart.js
Chart.register(...registerables);

// Exposer Chart globalement pour l'utiliser dans les vues
window.Chart = Chart;

// ============================
// THEME SWITCHER (Light/Dark)
// ============================
(function() {
    // Appliquer le theme immediatement (avant DOMContentLoaded)
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
})();

document.addEventListener('DOMContentLoaded', function() {
    const currentTheme = localStorage.getItem('theme') || 'light';
    updateThemeIcons(currentTheme);

    // Desktop theme toggle
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', toggleTheme);
    }

    // Mobile theme toggle
    const mobileThemeToggle = document.getElementById('mobile-theme-toggle');
    if (mobileThemeToggle) {
        mobileThemeToggle.addEventListener('click', toggleTheme);
    }
});

function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    updateThemeIcons(newTheme);
}

function updateThemeIcons(theme) {
    // Desktop icons
    const iconLight = document.querySelector('.theme-icon-light');
    const iconDark = document.querySelector('.theme-icon-dark');
    // Mobile icons
    const mobileIconLight = document.querySelector('.mobile-theme-icon-light');
    const mobileIconDark = document.querySelector('.mobile-theme-icon-dark');
    // Logos
    const logosLight = document.querySelectorAll('.logo-light');
    const logosDark = document.querySelectorAll('.logo-dark');

    if (theme === 'dark') {
        if (iconLight) iconLight.style.display = 'none';
        if (iconDark) iconDark.style.display = 'flex';
        if (mobileIconLight) mobileIconLight.style.display = 'none';
        if (mobileIconDark) mobileIconDark.style.display = 'flex';
        logosLight.forEach(el => el.style.display = 'none');
        logosDark.forEach(el => el.style.display = 'flex');
    } else {
        if (iconLight) iconLight.style.display = 'flex';
        if (iconDark) iconDark.style.display = 'none';
        if (mobileIconLight) mobileIconLight.style.display = 'flex';
        if (mobileIconDark) mobileIconDark.style.display = 'none';
        logosLight.forEach(el => el.style.display = 'flex');
        logosDark.forEach(el => el.style.display = 'none');
    }
}