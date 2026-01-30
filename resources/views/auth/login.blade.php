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
        <input
            type="password"
            id="password"
            name="password"
            required
            autocomplete="current-password"
            class="form-input"
            placeholder="Votre mot de passe"
        >
    </div>

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
    <p>&copy; {{ date('Y') }} Centrex Admin Dashboard</p>
</div>
@endsection
