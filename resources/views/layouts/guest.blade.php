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

    <!-- Lottie Web -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js"></script>

    <style>
        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--gradient-dark);
            padding: var(--spacing-lg);
            position: relative;
            overflow: hidden;
        }

        .auth-wrapper::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.15) 0%, transparent 50%);
            animation: auth-bg-pulse 15s ease-in-out infinite;
        }

        .auth-wrapper::after {
            content: '';
            position: absolute;
            bottom: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 0%, transparent 50%);
            animation: auth-bg-pulse 20s ease-in-out infinite reverse;
        }

        @keyframes auth-bg-pulse {
            0%, 100% {
                transform: translate(0, 0);
            }
            50% {
                transform: translate(5%, 5%);
            }
        }

        .auth-container {
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 1;
        }

        .auth-card {
            background: var(--bg-card);
            border-radius: var(--border-radius-xl);
            box-shadow: var(--shadow-2xl);
            padding: var(--spacing-2xl);
            animation: auth-slide-up 0.5s ease;
        }

        @keyframes auth-slide-up {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .auth-logo {
            text-align: center;
            margin-bottom: var(--spacing-xl);
        }

        #auth-lottie-container {
            width: 120px;
            height: 120px;
            margin: 0 auto var(--spacing-sm);
        }

        .auth-logo-text {
            font-size: 1.75rem;
            font-weight: 700;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        [data-theme="dark"] .auth-logo-text {
            background: none;
            -webkit-background-clip: unset;
            background-clip: unset;
            -webkit-text-fill-color: white;
            color: white;
        }

        .auth-title {
            font-size: 1.5rem;
            font-weight: 600;
            text-align: center;
            color: var(--text-primary);
            margin-bottom: var(--spacing-xs);
        }

        .auth-subtitle {
            text-align: center;
            color: var(--text-secondary);
            margin-bottom: var(--spacing-xl);
        }

        .auth-footer {
            text-align: center;
            margin-top: var(--spacing-xl);
            padding-top: var(--spacing-lg);
            border-top: 1px solid var(--border-color);
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        .auth-footer a {
            color: var(--color-primary);
            font-weight: 500;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-logo">
                    <div id="auth-lottie-container"></div>
                    <div class="auth-logo-text">Téléphonie VOIP</div>
                </div>

                @yield('content')
            </div>
        </div>
    </div>

    <script>
        // Appliquer le thème sauvegardé avant le rendu (évite le flash)
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);

        let lottieAnim = null;
        let lottieTimer = null;

        function scheduleReplay() {
            clearTimeout(lottieTimer);
            lottieTimer = setTimeout(function () {
                if (lottieAnim) {
                    lottieAnim.goToAndPlay(0);
                }
            }, 5000);
        }

        function loadLottieForTheme(theme) {
            const container = document.getElementById('auth-lottie-container');
            if (!container || typeof lottie === 'undefined') return;

            clearTimeout(lottieTimer);
            if (lottieAnim) {
                lottieAnim.destroy();
                lottieAnim = null;
            }

            lottieAnim = lottie.loadAnimation({
                container: container,
                renderer: 'svg',
                loop: false,
                autoplay: true,
                path: theme === 'dark' ? '/logo-dark.json' : '/logo.json'
            });

            // Quand l'animation est terminée, attendre 5s puis relancer
            lottieAnim.addEventListener('complete', scheduleReplay);
        }

        document.addEventListener('DOMContentLoaded', function () {
            loadLottieForTheme(document.documentElement.getAttribute('data-theme') || 'light');

            // Surveiller les changements de thème (ex: toggle depuis app.blade.php partagé)
            new MutationObserver(function (mutations) {
                mutations.forEach(function (m) {
                    if (m.attributeName === 'data-theme') {
                        loadLottieForTheme(document.documentElement.getAttribute('data-theme') || 'light');
                    }
                });
            }).observe(document.documentElement, { attributes: true });
        });
    </script>

    @stack('scripts')
</body>
</html>
