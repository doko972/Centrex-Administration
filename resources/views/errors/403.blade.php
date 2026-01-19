@extends('layouts.app')

@section('content')
<div style="min-height: 60vh; display: flex; align-items: center; justify-content: center;">
    <div class="card" style="max-width: 600px; text-align: center;">
        <div style="font-size: 5rem; margin-bottom: 1rem;">🚫</div>
        
        <h1 style="color: var(--color-danger); margin-bottom: 1rem;">Accès refusé</h1>
        
        <p style="font-size: 1.125rem; color: var(--text-secondary); margin-bottom: 2rem;">
            {{ $exception->getMessage() ?: 'Vous n\'avez pas les permissions nécessaires pour accéder à cette page.' }}
        </p>

        <div style="padding: 1rem; background-color: var(--bg-tertiary); border-radius: var(--border-radius); margin-bottom: 2rem;">
            <p style="font-size: 0.875rem; color: var(--text-secondary); margin: 0;">
                Si vous pensez qu'il s'agit d'une erreur, veuillez contacter votre administrateur.
            </p>
        </div>

        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="javascript:history.back()" class="btn btn-outline">← Retour</a>
            
            @auth
                @if(Auth::user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">Dashboard Admin</a>
                @else
                    <a href="{{ route('client.dashboard') }}" class="btn btn-primary">Mon Dashboard</a>
                @endif
            @else
                <a href="{{ route('login') }}" class="btn btn-primary">Se connecter</a>
            @endauth
        </div>
    </div>
</div>
@endsection