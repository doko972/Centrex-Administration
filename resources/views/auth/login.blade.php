@extends('layouts.app')

@section('content')
<div style="max-width: 400px; margin: 4rem auto;">
    <div class="card">
        <h2 style="text-align: center; margin-bottom: 2rem;">Connexion</h2>

        @if ($errors->any())
            <div style="background-color: var(--color-danger); color: white; padding: 1rem; border-radius: var(--border-radius); margin-bottom: 1rem;">
                <ul style="margin: 0;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div style="margin-bottom: 1rem;">
                <label for="email" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    required 
                    autofocus
                    style="width: 100%;"
                >
            </div>

            <div style="margin-bottom: 1rem;">
                <label for="password" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Mot de passe</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    style="width: 100%;"
                >
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: flex; align-items: center; cursor: pointer;">
                    <input type="checkbox" name="remember" style="margin-right: 0.5rem; width: auto;">
                    <span>Se souvenir de moi</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">
                Se connecter
            </button>
        </form>
    </div>
</div>
@endsection