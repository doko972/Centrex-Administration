import './bootstrap';
import { Chart, registerables } from 'chart.js';

// Enregistrer tous les composants de Chart.js
Chart.register(...registerables);

// Exposer Chart globalement pour l'utiliser dans les vues
window.Chart = Chart;

// ============================
// THEME SWITCHER (Light/Dark)
// ============================
document.addEventListener('DOMContentLoaded', function() {
    const currentTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', currentTheme);

    const themeToggle = document.getElementById('theme-toggle');

    if (themeToggle) {
        updateThemeIcon(currentTheme);

        themeToggle.addEventListener('click', function() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';

            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);

            updateThemeIcon(newTheme);
        });
    }

    function updateThemeIcon(theme) {
        const iconLight = document.querySelector('.theme-icon-light');
        const iconDark = document.querySelector('.theme-icon-dark');
        if (iconLight && iconDark) {
            if (theme === 'dark') {
                iconLight.style.display = 'none';
                iconDark.style.display = 'inline';
            } else {
                iconLight.style.display = 'inline';
                iconDark.style.display = 'none';
            }
        }
    }
});