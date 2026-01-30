<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Centrex Dashboard') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📞</text></svg>">

    <!-- Styles -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div class="wrapper">
        <!-- Navigation -->
        <nav class="navbar">
            <div class="navbar-container">
                <a href="{{ Auth::user()->isAdmin() ? route('admin.dashboard') : route('client.dashboard') }}" class="navbar-brand">
                    <span class="brand-icon">📞</span>
                    <span class="brand-text">Centrex</span>
                </a>

                <div class="navbar-nav">
                    @if(Auth::user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <span class="nav-icon">📊</span>
                            Dashboard
                        </a>
                        <a href="{{ route('admin.clients.index') }}" class="nav-link {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">
                            <span class="nav-icon">👥</span>
                            Clients
                        </a>
                        <a href="{{ route('admin.centrex.index') }}" class="nav-link {{ request()->routeIs('admin.centrex.*') ? 'active' : '' }}">
                            <span class="nav-icon">📞</span>
                            Centrex
                        </a>
                    @else
                        <a href="{{ route('client.dashboard') }}" class="nav-link {{ request()->routeIs('client.dashboard') ? 'active' : '' }}">
                            <span class="nav-icon">📞</span>
                            Mes Centrex
                        </a>
                    @endif
                </div>

                <div class="navbar-actions">
                    <button type="button" id="theme-toggle" class="theme-toggle" aria-label="Toggle theme" onclick="toggleTheme()">
                        <span class="theme-icon-light">🌙</span>
                        <span class="theme-icon-dark" style="display: none;">☀️</span>
                    </button>

                    <div class="user-menu" id="user-menu">
                        <button class="user-trigger" onclick="toggleUserMenu()">
                            <div class="user-avatar">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <div class="user-info">
                                <span class="user-name">{{ Auth::user()->name }}</span>
                                <span class="user-role">{{ Auth::user()->isAdmin() ? 'Admin' : 'Client' }}</span>
                            </div>
                            <span class="dropdown-arrow">▼</span>
                        </button>

                        <div class="dropdown-menu" id="user-dropdown">
                            <a href="#" class="dropdown-item">
                                <span>👤</span>
                                Mon profil
                            </a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item danger">
                                    <span>🚪</span>
                                    Déconnexion
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main>
            <div class="container">
                @yield('content')
            </div>
        </main>

        <!-- Footer -->
        <footer>
            <div class="container">
                <p>&copy; {{ date('Y') }} Centrex Admin Dashboard. Tous droits réservés.</p>
            </div>
        </footer>
    </div>

    {{-- <script>
        // Apply saved theme immediately to prevent flash
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
            // Update icons immediately
            setTimeout(function() {
                updateThemeIcons(savedTheme);
            }, 0);
        })();

        // Update theme icons
        function updateThemeIcons(theme) {
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

        // Toggle theme function (called by onclick)
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme') || 'light';
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcons(newTheme);
        }

        // User menu dropdown
        function toggleUserMenu() {
            const menu = document.getElementById('user-menu');
            const dropdown = document.getElementById('user-dropdown');
            if (menu && dropdown) {
                menu.classList.toggle('open');
                dropdown.classList.toggle('show');
            }
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const userMenu = document.getElementById('user-menu');
            const dropdown = document.getElementById('user-dropdown');
            const themeToggle = document.getElementById('theme-toggle');

            // Don't close if clicking theme toggle
            if (themeToggle && themeToggle.contains(e.target)) {
                return;
            }

            if (userMenu && dropdown && !userMenu.contains(e.target)) {
                userMenu.classList.remove('open');
                dropdown.classList.remove('show');
            }
        });

        // Update icons when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            updateThemeIcons(savedTheme);
        });
    </script> --}}

    @stack('scripts')
</body>
</html>
