@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1 class="page-title">
        Nouveau Client
        <small>Créer un nouveau compte client</small>
    </h1>
    <div class="page-actions">
        <a href="{{ route('admin.clients.index') }}" class="btn btn-ghost">
            ← Retour
        </a>
    </div>
</div>

<div class="card" style="max-width: 800px;">
    @if ($errors->any())
        <div class="alert alert-danger mb-lg">
            <span class="alert-icon">!</span>
            <div class="alert-content">
                <p class="alert-title">Erreurs de validation</p>
                <ul class="mt-sm" style="margin-left: 1rem; list-style: disc;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.clients.store') }}">
        @csrf

        <div class="section">
            <h3 class="section-title mb-lg" style="padding-bottom: 0.75rem; border-bottom: 2px solid var(--border-color);">
                🔐 Informations de connexion
            </h3>

            <div class="form-group">
                <label for="name" class="form-label">
                    Nom complet <span class="required">*</span>
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    class="form-input"
                    placeholder="Jean Dupont"
                >
            </div>

            <div class="form-group">
                <label for="email" class="form-label">
                    Email <span class="required">*</span>
                </label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    class="form-input"
                    placeholder="jean.dupont@entreprise.com"
                >
            </div>

            <div class="form-group">
                <label for="password" class="form-label">
                    Mot de passe <span class="required">*</span>
                </label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    class="form-input"
                    placeholder="Minimum 8 caractères"
                >
                <p class="form-help">Le mot de passe doit contenir au moins 8 caractères.</p>
            </div>
        </div>

        <div class="section">
            <h3 class="section-title mb-lg" style="padding-bottom: 0.75rem; border-bottom: 2px solid var(--border-color);">
                🏢 Informations entreprise
            </h3>

            <div class="form-group">
                <label for="company_name" class="form-label">
                    Nom de l'entreprise <span class="required">*</span>
                </label>
                <input
                    type="text"
                    id="company_name"
                    name="company_name"
                    value="{{ old('company_name') }}"
                    required
                    class="form-input"
                    placeholder="Ma Société SAS"
                >
            </div>

            <div class="grid grid-2">
                <div class="form-group">
                    <label for="contact_name" class="form-label">Nom du contact</label>
                    <input
                        type="text"
                        id="contact_name"
                        name="contact_name"
                        value="{{ old('contact_name') }}"
                        class="form-input"
                        placeholder="Personne à contacter"
                    >
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">Téléphone</label>
                    <input
                        type="text"
                        id="phone"
                        name="phone"
                        value="{{ old('phone') }}"
                        class="form-input"
                        placeholder="+33 1 23 45 67 89"
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="address" class="form-label">Adresse</label>
                <textarea
                    id="address"
                    name="address"
                    rows="3"
                    class="form-textarea"
                    placeholder="123 Rue de l'Exemple, 75001 Paris"
                >{{ old('address') }}</textarea>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.clients.index') }}" class="btn btn-ghost">Annuler</a>
            <button type="submit" class="btn btn-primary">
                ✓ Créer le client
            </button>
        </div>
    </form>
</div>
@endsection
