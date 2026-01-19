@extends('layouts.app')

@section('content')
<div style="min-height: 60vh; display: flex; align-items: center; justify-content: center;">
    <div class="card" style="max-width: 600px; text-align: center;">
        <div style="font-size: 5rem; margin-bottom: 1rem;">🔍</div>
        
        <h1 style="color: var(--color-warning); margin-bottom: 1rem;">Page introuvable</h1>
        
        <p style="font-size: 1.125rem; color: var(--text-secondary); margin-bottom: 2rem;">
            Désolé, la page que vous recherchez n'existe pas ou a été déplacée.
        </p>

        <div style="padding: 1rem; background-color: var(--bg-tertiary); border-radius: var(--border-radius); margin-bottom: 2rem;">
            <p style="font-size: 0.875rem; color: var(--text-secondary); margin: 0;">
                <strong>URL demandée :</strong><br>
                <code style="font-family: monospace; background-color: var(--bg-primary); padding: 0.25rem 0.5rem; border-radius: 4px; margin-top: 0.5rem; display: inline-block;">
                    {{ request()->url() }}
                </code>
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
                <a href="/" class="btn btn-primary">Accueil</a>
            @endauth
        </div>
    </div>
</div>
@endsection