@extends('layouts.app')

@section('content')
<div style="min-height: 60vh; display: flex; align-items: center; justify-content: center;">
    <div class="card" style="max-width: 600px; text-align: center;">
        <div style="font-size: 5rem; margin-bottom: 1rem;">⚠️</div>

        <h1 style="color: var(--color-danger); margin-bottom: 1rem;">Erreur serveur</h1>

        <p style="font-size: 1.125rem; color: var(--text-secondary); margin-bottom: 2rem;">
            Une erreur inattendue s'est produite de notre côté. L'équipe technique a été informée, veuillez réessayer dans quelques instants.
        </p>

        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="javascript:history.back()" class="btn btn-outline">← Retour</a>

            @auth
                @if(Auth::user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">Dashboard Admin</a>
                @elseif(Auth::user()->isSuperClient())
                    <a href="{{ route('superclient.dashboard') }}" class="btn btn-primary">Mon Dashboard</a>
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
