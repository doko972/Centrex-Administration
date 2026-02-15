<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#4f46e5">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">

    <title>{{ config('app.name', 'Centrex Dashboard') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('logo.ico') }}">

    <!-- Lottie Player -->
    <script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module"></script>

    <!-- Styles -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div class="wrapper">
        <!-- Navigation -->
        <nav class="navbar">
            <div class="navbar-container">
                <!-- Logo -->
                <a href="{{ Auth::user()->isAdmin() ? route('admin.dashboard') : route('client.dashboard') }}" class="navbar-brand">
                    <span class="logo-light">
                        <dotlottie-player id="logo-animation" src="{{ asset('logo.json') }}" background="transparent" speed="1" style="width: 36px; height: 36px;" autoplay></dotlottie-player>
                    </span>
                    <span class="logo-dark" style="display: none;">
                        <dotlottie-player id="logo-animation-dark" src="{{ asset('logo-dark.json') }}" background="transparent" speed="1" style="width: 36px; height: 36px;" autoplay></dotlottie-player>
                    </span>
                    <span class="brand-text">HR Télécoms-Manager</span>
                </a>

                <!-- Desktop Navigation -->
                <div class="navbar-nav">
                    @if(Auth::user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <span class="nav-icon">
                                <dotlottie-player src="{{ asset('icons/icon-dashboard.json') }}" background="transparent" speed="1" style="width: 22px; height: 22px;" loop hover></dotlottie-player>
                            </span>
                            Dashboard
                        </a>
                        <a href="{{ route('admin.clients.index') }}" class="nav-link {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">
                            <span class="nav-icon">
                                <dotlottie-player src="{{ asset('icons/icon-clients.json') }}" background="transparent" speed="1" style="width: 22px; height: 22px;" loop hover></dotlottie-player>
                            </span>
                            Clients
                        </a>
                        <a href="{{ route('admin.centrex.index') }}" class="nav-link {{ request()->routeIs('admin.centrex.*') ? 'active' : '' }}">
                            <span class="nav-icon">
                                <dotlottie-player src="{{ asset('icons/icon-phone.json') }}" background="transparent" speed="1" style="width: 22px; height: 22px;" loop hover></dotlottie-player>
                            </span>
                            Centrex
                        </a>
                        <a href="{{ route('admin.ipbx.index') }}" class="nav-link {{ request()->routeIs('admin.ipbx.*') ? 'active' : '' }}">
                            <span class="nav-icon">
                                <dotlottie-player src="{{ asset('icons/icon-server.json') }}" background="transparent" speed="1" style="width: 22px; height: 22px;" loop hover></dotlottie-player>
                            </span>
                            IPBX
                        </a>
                        <div class="nav-dropdown" id="config-menu">
                            <button class="nav-link {{ request()->routeIs('admin.connection-types.*') || request()->routeIs('admin.providers.*') || request()->routeIs('admin.equipment.*') ? 'active' : '' }}" onclick="toggleConfigMenu(event)">
                                <span class="nav-icon">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                                </span>
                                Configuration
                                <span class="dropdown-arrow" style="margin-left: 0.25rem;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                                </span>
                            </button>
                            <div class="nav-dropdown-menu" id="config-dropdown">
                                <a href="{{ route('admin.connection-types.index') }}" class="dropdown-item {{ request()->routeIs('admin.connection-types.*') ? 'active' : '' }}">
                                    Types de connexion
                                </a>
                                <a href="{{ route('admin.providers.index') }}" class="dropdown-item {{ request()->routeIs('admin.providers.*') ? 'active' : '' }}">
                                    Fournisseurs
                                </a>
                                <a href="{{ route('admin.equipment.index') }}" class="dropdown-item {{ request()->routeIs('admin.equipment.*') ? 'active' : '' }}">
                                    Équipements
                                </a>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('client.dashboard') }}" class="nav-link {{ request()->routeIs('client.dashboard') ? 'active' : '' }}">
                            <span class="nav-icon">
                                <dotlottie-player src="{{ asset('icons/icon-home.json') }}" background="transparent" speed="1" style="width: 22px; height: 22px;" loop hover></dotlottie-player>
                            </span>
                            Mon Espace
                        </a>
                    @endif
                </div>

                <!-- Desktop Actions -->
                <div class="navbar-actions">
                    <button type="button" id="theme-toggle" class="theme-toggle" aria-label="Toggle theme">
                        <span class="theme-icon-light">
                            <dotlottie-player src="{{ asset('icons/icon-sun.json') }}" background="transparent" speed="1" style="width: 24px; height: 24px;" loop hover></dotlottie-player>
                        </span>
                        <span class="theme-icon-dark" style="display: none;">
                            <dotlottie-player src="{{ asset('icons/icon-moon.json') }}" background="transparent" speed="1" style="width: 24px; height: 24px;" loop hover></dotlottie-player>
                        </span>
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
                            <span class="dropdown-arrow">
                                <dotlottie-player src="{{ asset('icons/icon-chevron.json') }}" background="transparent" speed="1" style="width: 16px; height: 16px;"></dotlottie-player>
                            </span>
                        </button>

                        <div class="dropdown-menu" id="user-dropdown">
                            <a href="#" class="dropdown-item">
                                <span class="dropdown-icon">
                                    <dotlottie-player src="{{ asset('icons/icon-profile.json') }}" background="transparent" speed="1" style="width: 20px; height: 20px;" loop hover></dotlottie-player>
                                </span>
                                Mon profil
                            </a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item danger">
                                    <span class="dropdown-icon">
                                        <dotlottie-player src="{{ asset('icons/icon-logout.json') }}" background="transparent" speed="1" style="width: 20px; height: 20px;" loop hover></dotlottie-player>
                                    </span>
                                    Deconnexion
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Mobile Menu Toggle -->
                <button type="button" class="navbar-toggle" id="mobile-menu-toggle" aria-label="Menu">
                    <div class="burger" id="burger-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </button>
            </div>
        </nav>

        <!-- Mobile Menu Overlay -->
        <div class="mobile-menu-overlay" id="mobile-overlay"></div>

        <!-- Mobile Navigation Panel -->
        <div class="mobile-nav" id="mobile-nav">
            <div class="mobile-nav-header">
                <a href="{{ Auth::user()->isAdmin() ? route('admin.dashboard') : route('client.dashboard') }}" class="navbar-brand" onclick="closeMobileMenu()">
                    <span class="logo-light">
                        <dotlottie-player src="{{ asset('logo.json') }}" background="transparent" speed="1" style="width: 32px; height: 32px;"></dotlottie-player>
                    </span>
                    <span class="logo-dark" style="display: none;">
                        <dotlottie-player src="{{ asset('logo-dark.json') }}" background="transparent" speed="1" style="width: 32px; height: 32px;"></dotlottie-player>
                    </span>
                    <span class="brand-text">Centrex</span>
                </a>
                <button type="button" class="mobile-nav-close" id="mobile-nav-close" aria-label="Fermer">
                    <dotlottie-player src="{{ asset('icons/icon-close.json') }}" background="transparent" speed="1" style="width: 24px; height: 24px;"></dotlottie-player>
                </button>
            </div>

            <div class="mobile-nav-body">
                <div class="mobile-nav-section">
                    <div class="mobile-nav-section-title">Navigation</div>

                    @if(Auth::user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="mobile-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" onclick="closeMobileMenu()">
                            <span class="mobile-nav-icon">
                                <dotlottie-player src="{{ asset('icons/icon-dashboard.json') }}" background="transparent" speed="1" style="width: 24px; height: 24px;" loop hover></dotlottie-player>
                            </span>
                            Dashboard
                        </a>
                        <a href="{{ route('admin.clients.index') }}" class="mobile-nav-link {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}" onclick="closeMobileMenu()">
                            <span class="mobile-nav-icon">
                                <dotlottie-player src="{{ asset('icons/icon-clients.json') }}" background="transparent" speed="1" style="width: 24px; height: 24px;" loop hover></dotlottie-player>
                            </span>
                            Clients
                        </a>
                        <a href="{{ route('admin.centrex.index') }}" class="mobile-nav-link {{ request()->routeIs('admin.centrex.*') ? 'active' : '' }}" onclick="closeMobileMenu()">
                            <span class="mobile-nav-icon">
                                <dotlottie-player src="{{ asset('icons/icon-phone.json') }}" background="transparent" speed="1" style="width: 24px; height: 24px;" loop hover></dotlottie-player>
                            </span>
                            Centrex
                        </a>
                        <a href="{{ route('admin.ipbx.index') }}" class="mobile-nav-link {{ request()->routeIs('admin.ipbx.*') ? 'active' : '' }}" onclick="closeMobileMenu()">
                            <span class="mobile-nav-icon">
                                <dotlottie-player src="{{ asset('icons/icon-server.json') }}" background="transparent" speed="1" style="width: 24px; height: 24px;" loop hover></dotlottie-player>
                            </span>
                            IPBX
                        </a>
                </div>

                <div class="mobile-nav-section">
                    <div class="mobile-nav-section-title">Configuration</div>
                        <a href="{{ route('admin.connection-types.index') }}" class="mobile-nav-link {{ request()->routeIs('admin.connection-types.*') ? 'active' : '' }}" onclick="closeMobileMenu()">
                            <span class="mobile-nav-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.55a11 11 0 0 1 14.08 0"></path><path d="M1.42 9a16 16 0 0 1 21.16 0"></path><path d="M8.53 16.11a6 6 0 0 1 6.95 0"></path><line x1="12" y1="20" x2="12.01" y2="20"></line></svg>
                            </span>
                            Types de connexion
                        </a>
                        <a href="{{ route('admin.providers.index') }}" class="mobile-nav-link {{ request()->routeIs('admin.providers.*') ? 'active' : '' }}" onclick="closeMobileMenu()">
                            <span class="mobile-nav-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                            </span>
                            Fournisseurs
                        </a>
                        <a href="{{ route('admin.equipment.index') }}" class="mobile-nav-link {{ request()->routeIs('admin.equipment.*') ? 'active' : '' }}" onclick="closeMobileMenu()">
                            <span class="mobile-nav-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect><rect x="9" y="9" width="6" height="6"></rect><line x1="9" y1="1" x2="9" y2="4"></line><line x1="15" y1="1" x2="15" y2="4"></line><line x1="9" y1="20" x2="9" y2="23"></line><line x1="15" y1="20" x2="15" y2="23"></line><line x1="20" y1="9" x2="23" y2="9"></line><line x1="20" y1="14" x2="23" y2="14"></line><line x1="1" y1="9" x2="4" y2="9"></line><line x1="1" y1="14" x2="4" y2="14"></line></svg>
                            </span>
                            Équipements
                        </a>
                    @else
                        <a href="{{ route('client.dashboard') }}" class="mobile-nav-link {{ request()->routeIs('client.dashboard') ? 'active' : '' }}" onclick="closeMobileMenu()">
                            <span class="mobile-nav-icon">
                                <dotlottie-player src="{{ asset('icons/icon-home.json') }}" background="transparent" speed="1" style="width: 24px; height: 24px;" loop hover></dotlottie-player>
                            </span>
                            Mon Espace
                        </a>
                    @endif
                </div>
            </div>

            <div class="mobile-nav-footer">
                <div class="mobile-user-info">
                    <div class="mobile-user-avatar">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="mobile-user-details">
                        <div class="mobile-user-name">{{ Auth::user()->name }}</div>
                        <div class="mobile-user-role">{{ Auth::user()->isAdmin() ? 'Administrateur' : 'Client' }}</div>
                    </div>
                </div>

                <div class="mobile-nav-actions">
                    <button type="button" id="mobile-theme-toggle" class="mobile-action-btn">
                        <span class="mobile-theme-icon-light">
                            <dotlottie-player src="{{ asset('icons/icon-sun.json') }}" background="transparent" speed="1" style="width: 20px; height: 20px;"></dotlottie-player>
                        </span>
                        <span class="mobile-theme-icon-dark" style="display: none;">
                            <dotlottie-player src="{{ asset('icons/icon-moon.json') }}" background="transparent" speed="1" style="width: 20px; height: 20px;"></dotlottie-player>
                        </span>
                        <span class="mobile-theme-text">Theme</span>
                    </button>

                    <form method="POST" action="{{ route('logout') }}" style="flex: 1; display: flex;">
                        @csrf
                        <button type="submit" class="mobile-action-btn danger" style="width: 100%;">
                            <span class="mobile-action-icon">
                                <dotlottie-player src="{{ asset('icons/icon-logout.json') }}" background="transparent" speed="1" style="width: 20px; height: 20px;"></dotlottie-player>
                            </span>
                            Quitter
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <main>
            <div class="container">
                @yield('content')
            </div>
        </main>

        <!-- Footer -->
        <footer>
            <div class="container">
                <p>Centrex Admin Dashboard {{ date('Y') }}</p>
            </div>
        </footer>
    </div>

    <script>
        // ============================
        // User Menu Dropdown (Desktop)
        // ============================
        function toggleUserMenu() {
            const menu = document.getElementById('user-menu');
            const dropdown = document.getElementById('user-dropdown');
            if (menu && dropdown) {
                menu.classList.toggle('open');
                dropdown.classList.toggle('show');
            }
        }

        // ============================
        // Config Menu Dropdown (Desktop)
        // ============================
        function toggleConfigMenu(e) {
            e.preventDefault();
            e.stopPropagation();
            const menu = document.getElementById('config-menu');
            const dropdown = document.getElementById('config-dropdown');
            if (menu && dropdown) {
                menu.classList.toggle('open');
                dropdown.classList.toggle('show');
            }
        }

        document.addEventListener('click', function(e) {
            const userMenu = document.getElementById('user-menu');
            const dropdown = document.getElementById('user-dropdown');
            const configMenu = document.getElementById('config-menu');
            const configDropdown = document.getElementById('config-dropdown');

            if (userMenu && dropdown && !userMenu.contains(e.target)) {
                userMenu.classList.remove('open');
                dropdown.classList.remove('show');
            }

            if (configMenu && configDropdown && !configMenu.contains(e.target)) {
                configMenu.classList.remove('open');
                configDropdown.classList.remove('show');
            }
        });

        // ============================
        // Mobile Menu
        // ============================
        function openMobileMenu() {
            const burger = document.getElementById('burger-icon');
            const overlay = document.getElementById('mobile-overlay');
            const nav = document.getElementById('mobile-nav');

            if (burger) burger.classList.add('active');
            if (overlay) overlay.classList.add('active');
            if (nav) nav.classList.add('active');
            document.body.classList.add('menu-open');
        }

        function closeMobileMenu() {
            const burger = document.getElementById('burger-icon');
            const overlay = document.getElementById('mobile-overlay');
            const nav = document.getElementById('mobile-nav');

            if (burger) burger.classList.remove('active');
            if (overlay) overlay.classList.remove('active');
            if (nav) nav.classList.remove('active');
            document.body.classList.remove('menu-open');
        }

        function toggleMobileMenu() {
            const nav = document.getElementById('mobile-nav');
            if (nav && nav.classList.contains('active')) {
                closeMobileMenu();
            } else {
                openMobileMenu();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Toggle button
            const menuToggle = document.getElementById('mobile-menu-toggle');
            if (menuToggle) {
                menuToggle.addEventListener('click', toggleMobileMenu);
            }

            // Close button
            const closeBtn = document.getElementById('mobile-nav-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', closeMobileMenu);
            }

            // Overlay click
            const overlay = document.getElementById('mobile-overlay');
            if (overlay) {
                overlay.addEventListener('click', closeMobileMenu);
            }

            // Close on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeMobileMenu();
                }
            });

            // Close on window resize (if going to desktop)
            let resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    if (window.innerWidth >= 769) {
                        closeMobileMenu();
                    }
                }, 100);
            });

            // ============================
            // Lottie Hover Animations
            // ============================
            initLottieHoverAnimations();
        });

        // ============================
        // Logo Animation
        // ============================
        document.addEventListener('DOMContentLoaded', function() {
            const logo = document.getElementById('logo-animation');
            if (logo) {
                logo.addEventListener('complete', function() {
                    setTimeout(function() {
                        logo.seek(0);
                        logo.play();
                    }, 5000);
                });
            }
        });

        // ============================
        // Lottie Hover Animation Handler
        // ============================
        function initLottieHoverAnimations() {
            // Get all nav links with lottie players
            const navLinks = document.querySelectorAll('.nav-link, .mobile-nav-link, .dropdown-item, .mobile-action-btn');

            navLinks.forEach(function(link) {
                const player = link.querySelector('dotlottie-player');
                if (player) {
                    // Play animation on hover
                    link.addEventListener('mouseenter', function() {
                        player.play();
                    });

                    // Stop and reset on mouse leave
                    link.addEventListener('mouseleave', function() {
                        player.stop();
                    });

                    // Play on touch for mobile
                    link.addEventListener('touchstart', function() {
                        player.play();
                    }, { passive: true });
                }
            });

            // Theme toggle buttons
            const themeToggles = document.querySelectorAll('#theme-toggle, #mobile-theme-toggle');
            themeToggles.forEach(function(btn) {
                const players = btn.querySelectorAll('dotlottie-player');
                btn.addEventListener('mouseenter', function() {
                    players.forEach(function(p) { p.play(); });
                });
                btn.addEventListener('mouseleave', function() {
                    players.forEach(function(p) { p.stop(); });
                });
            });
        }
    </script>

    @stack('scripts')
</body>
</html>
