@extends('layouts.guest')

@section('content')
<h1 class="auth-title">Connexion</h1>
<p class="auth-subtitle">Accédez à votre espace Centrex</p>

@if ($errors->any())
    <div class="alert alert-danger mb-lg">
        <span class="alert-icon">!</span>
        <div class="alert-content">
            @foreach ($errors->all() as $error)
                <p class="alert-message mb-0">{{ $error }}</p>
            @endforeach
        </div>
    </div>
@endif

@if (session('status'))
    <div class="alert alert-success mb-lg">
        <span class="alert-icon">✓</span>
        <div class="alert-content">
            <p class="alert-message mb-0">{{ session('status') }}</p>
        </div>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-warning mb-lg">
        <span class="alert-icon">⚠</span>
        <div class="alert-content">
            <p class="alert-message mb-0">{{ session('error') }}</p>
        </div>
    </div>
@endif

<form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="form-group">
        <label for="email" class="form-label">Email</label>
        <input
            type="email"
            id="email"
            name="email"
            value="{{ old('email') }}"
            required
            autofocus
            autocomplete="username"
            class="form-input"
            placeholder="votre@email.com"
        >
    </div>

    <div class="form-group">
        <label for="password" class="form-label">Mot de passe</label>
        <div style="position: relative;">
            <input
                type="password"
                id="password"
                name="password"
                required
                autocomplete="current-password"
                class="form-input"
                placeholder="Votre mot de passe"
                style="padding-right: 2.75rem;"
            >
            <button
                type="button"
                id="toggle-password"
                onclick="togglePassword()"
                style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; padding: 0; color: var(--text-secondary); display: flex; align-items: center;"
                aria-label="Afficher/masquer le mot de passe"
            >
                <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
                <svg id="eye-off-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                    <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                    <line x1="1" y1="1" x2="23" y2="23"/>
                </svg>
            </button>
        </div>
    </div>

    @push('scripts')
    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            const eyeOffIcon = document.getElementById('eye-off-icon');

            if (input.type === 'password') {
                input.type = 'text';
                eyeIcon.style.display = 'none';
                eyeOffIcon.style.display = 'block';
            } else {
                input.type = 'password';
                eyeIcon.style.display = 'block';
                eyeOffIcon.style.display = 'none';
            }
        }
    </script>
    @endpush

    <div class="form-group">
        <label class="custom-checkbox">
            <input type="checkbox" name="remember">
            <span class="checkmark"></span>
            <span>Se souvenir de moi</span>
        </label>
    </div>

    <button type="submit" class="btn btn-primary btn-block btn-lg">
        Se connecter
    </button>
</form>

<div class="auth-footer">
    <p>&copy; {{ date('Y') }} Téléphonie VOIP Dashboard</p>
</div>
@endsection
