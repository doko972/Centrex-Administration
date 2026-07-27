@extends('layouts.app')

@section('content')
<div style="min-height: 60vh; display: flex; align-items: center; justify-content: center;">
    <div class="card" style="max-width: 600px; text-align: center;">
        <div style="font-size: 5rem; margin-bottom: 1rem;">🚦</div>

        <h1 style="color: var(--color-warning); margin-bottom: 1rem;">Trop de tentatives</h1>

        <p style="font-size: 1.125rem; color: var(--text-secondary); margin-bottom: 2rem;">
            Vous avez effectué trop de tentatives en peu de temps. Merci de patienter quelques instants avant de réessayer.
        </p>

        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="javascript:history.back()" class="btn btn-outline">← Retour</a>
            <a href="/" class="btn btn-primary">Accueil</a>
        </div>
    </div>
</div>
@endsection
