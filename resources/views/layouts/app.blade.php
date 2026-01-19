<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Centrex Dashboard') }}</title>

    <!-- Styles -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div class="wrapper">
        <!-- Header -->
        <header>
            <div class="container">
                <div class="header-content">
                    <div class="logo">
                        Centrex Admin
                    </div>
                    
                    <nav>
                        <button id="theme-toggle" class="btn btn-outline btn-sm">
                            🌙
                        </button>
                    </nav>
                </div>
            </div>
        </header>

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
</body>
</html>