@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1 class="page-title">Mon profil</h1>
</div>

@if (session('success'))
    <div class="alert alert-success mb-lg">
        <span class="alert-icon">✓</span>
        <div class="alert-content">
            <p class="alert-message mb-0">{{ session('success') }}</p>
        </div>
    </div>
@endif

<div class="card" style="max-width: 640px;">
    <div class="section">
        <h3 class="section-title mb-lg" style="padding-bottom: 0.75rem; border-bottom: 2px solid var(--border-color);">
            Informations personnelles
        </h3>

        @if ($errors->updateInfo->any())
            <div class="alert alert-danger mb-lg">
                <span class="alert-icon">!</span>
                <div class="alert-content">
                    @foreach ($errors->updateInfo->all() as $error)
                        <p class="alert-message mb-0">{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PATCH')

            <div class="form-group">
                <label for="name" class="form-label">Nom complet</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $user->name) }}"
                    required
                    class="form-input"
                >
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email', $user->email) }}"
                    required
                    class="form-input"
                >
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<div class="card" style="max-width: 640px;">
    <div class="section">
        <h3 class="section-title mb-lg" style="padding-bottom: 0.75rem; border-bottom: 2px solid var(--border-color);">
            Changer le mot de passe
        </h3>

        @if ($errors->updatePassword->any())
            <div class="alert alert-danger mb-lg">
                <span class="alert-icon">!</span>
                <div class="alert-content">
                    @foreach ($errors->updatePassword->all() as $error)
                        <p class="alert-message mb-0">{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('profile.password.update') }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="current_password" class="form-label">Mot de passe actuel</label>
                <div style="position: relative;">
                    <input
                        type="password"
                        id="current_password"
                        name="current_password"
                        required
                        autocomplete="current-password"
                        class="form-input"
                        style="padding-right: 2.75rem;"
                    >
                    <button type="button" class="password-toggle" onclick="togglePassword('current_password', this)" aria-label="Afficher/Masquer le mot de passe" style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-secondary); padding: 0.25rem;">
                        <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="eye-off-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Nouveau mot de passe</label>
                <div style="position: relative;">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        autocomplete="new-password"
                        class="form-input"
                        placeholder="Min. 8 car. avec maj., chiffre et symbole"
                        style="padding-right: 2.75rem;"
                        oninput="checkPasswordStrength(this.value)"
                    >
                    <button type="button" class="password-toggle" onclick="togglePassword('password', this)" aria-label="Afficher/Masquer le mot de passe" style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-secondary); padding: 0.25rem;">
                        <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="eye-off-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </button>
                </div>

                <div id="password-strength" style="margin-top: 0.5rem; display: none;">
                    <div style="height: 4px; border-radius: 2px; background: var(--border-color); overflow: hidden;">
                        <div id="strength-bar" style="height: 100%; width: 0; border-radius: 2px; transition: width 0.3s, background-color 0.3s;"></div>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.35rem;">
                        <span id="strength-label" style="font-size: 0.75rem; font-weight: 600;"></span>
                        <span id="strength-hints" style="font-size: 0.7rem; color: var(--text-tertiary);"></span>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="password_confirmation" class="form-label">Confirmer le nouveau mot de passe</label>
                <div style="position: relative;">
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        class="form-input"
                        style="padding-right: 2.75rem;"
                    >
                    <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation', this)" aria-label="Afficher/Masquer le mot de passe" style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-secondary); padding: 0.25rem;">
                        <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="eye-off-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </button>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Changer le mot de passe</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePassword(fieldId, btn) {
    var field = document.getElementById(fieldId);
    var eyeIcon = btn.querySelector('.eye-icon');
    var eyeOffIcon = btn.querySelector('.eye-off-icon');
    if (field.type === 'password') {
        field.type = 'text';
        eyeIcon.style.display = 'none';
        eyeOffIcon.style.display = 'block';
    } else {
        field.type = 'password';
        eyeIcon.style.display = 'block';
        eyeOffIcon.style.display = 'none';
    }
}

function checkPasswordStrength(password) {
    var wrapper = document.getElementById('password-strength');
    var bar = document.getElementById('strength-bar');
    var label = document.getElementById('strength-label');
    var hints = document.getElementById('strength-hints');

    if (!password) {
        wrapper.style.display = 'none';
        return;
    }
    wrapper.style.display = 'block';

    var score = 0;
    var missing = [];

    if (password.length >= 8) score++; else missing.push('8 car.');
    if (/[A-Z]/.test(password)) score++; else missing.push('majuscule');
    if (/[a-z]/.test(password)) score++; else missing.push('minuscule');
    if (/[0-9]/.test(password)) score++; else missing.push('chiffre');
    if (/[^A-Za-z0-9]/.test(password)) score++; else missing.push('symbole');

    var levels = [
        { label: 'Très faible', color: '#ef4444', width: '20%' },
        { label: 'Faible',      color: '#f97316', width: '40%' },
        { label: 'Moyen',       color: '#eab308', width: '60%' },
        { label: 'Fort',        color: '#22c55e', width: '80%' },
        { label: 'Très fort',   color: '#16a34a', width: '100%' },
    ];

    var level = levels[Math.max(0, score - 1)];
    bar.style.width = level.width;
    bar.style.backgroundColor = level.color;
    label.textContent = level.label;
    label.style.color = level.color;
    hints.textContent = missing.length ? 'Il manque : ' + missing.join(', ') : '';
}
</script>
@endpush
