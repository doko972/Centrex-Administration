@extends('layouts.guest')

@section('content')
<h1 class="auth-title">Mot de passe oublié</h1>
<p class="auth-subtitle">Indiquez votre email, nous vous enverrons un lien de réinitialisation.</p>

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

<form method="POST" action="{{ route('password.email') }}">
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

    <button type="submit" class="btn btn-primary btn-block btn-lg">
        Envoyer le lien de réinitialisation
    </button>
</form>

<div class="auth-footer">
    <a href="{{ route('login') }}">← Retour à la connexion</a>
</div>
@endsection
