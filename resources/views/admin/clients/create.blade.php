@extends('layouts.app')

@section('content')
<div style="margin-bottom: 2rem;">
    <h1>Nouveau Client</h1>
</div>

<div class="card" style="max-width: 800px;">
    @if ($errors->any())
        <div style="background-color: var(--color-danger); color: white; padding: 1rem; border-radius: var(--border-radius); margin-bottom: 1.5rem;">
            <strong>Erreurs :</strong>
            <ul style="margin: 0.5rem 0 0 1.5rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.clients.store') }}">
        @csrf

        <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--border-color);">Informations de connexion</h3>

        <div style="margin-bottom: 1rem;">
            <label for="name" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Nom complet *</label>
            <input 
                type="text" 
                id="name" 
                name="name" 
                value="{{ old('name') }}" 
                required
                style="width: 100%;"
            >
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="email" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Email *</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                value="{{ old('email') }}" 
                required
                style="width: 100%;"
            >
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label for="password" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Mot de passe *</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                required
                style="width: 100%;"
            >
            <small style="color: var(--text-tertiary);">Minimum 8 caractères</small>
        </div>

        <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--border-color);">Informations entreprise</h3>

        <div style="margin-bottom: 1rem;">
            <label for="company_name" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Nom de l'entreprise *</label>
            <input 
                type="text" 
                id="company_name" 
                name="company_name" 
                value="{{ old('company_name') }}" 
                required
                style="width: 100%;"
            >
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="contact_name" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Nom du contact</label>
            <input 
                type="text" 
                id="contact_name" 
                name="contact_name" 
                value="{{ old('contact_name') }}"
                style="width: 100%;"
            >
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="phone" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Téléphone</label>
            <input 
                type="text" 
                id="phone" 
                name="phone" 
                value="{{ old('phone') }}"
                style="width: 100%;"
            >
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label for="address" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Adresse</label>
            <textarea 
                id="address" 
                name="address" 
                rows="3"
                style="width: 100%;"
            >{{ old('address') }}</textarea>
        </div>

        <div style="display: flex; gap: 1rem; justify-content: flex-end;">
            <a href="{{ route('admin.clients.index') }}" class="btn btn-outline">Annuler</a>
            <button type="submit" class="btn btn-primary">Créer le client</button>
        </div>
    </form>
</div>
@endsection