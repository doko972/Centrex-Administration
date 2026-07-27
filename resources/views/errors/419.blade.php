@extends('layouts.app')

@section('content')
<div style="min-height: 60vh; display: flex; align-items: center; justify-content: center;">
    <div class="card" style="max-width: 600px; text-align: center;">
        <div style="font-size: 5rem; margin-bottom: 1rem;">⏳</div>

        <h1 style="color: var(--color-warning); margin-bottom: 1rem;">Session expirée</h1>

        <p style="font-size: 1.125rem; color: var(--text-secondary); margin-bottom: 2rem;">
            Votre session a expiré, probablement parce que la page est restée ouverte trop longtemps. Veuillez recharger la page et réessayer.
        </p>

        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            @auth
                <a href="javascript:location.reload()" class="btn btn-primary">Recharger la page</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary">Se reconnecter</a>
            @endauth
        </div>
    </div>
</div>
@endsection
