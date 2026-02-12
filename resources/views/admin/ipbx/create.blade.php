@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1 class="page-title">Nouvel IPBX</h1>
    <div class="page-actions">
        <a href="{{ route('admin.ipbx.index') }}" class="btn btn-ghost">
            ← Retour à la liste
        </a>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger mb-lg">
        <span class="alert-icon">⚠</span>
        <div class="alert-content">
            <p class="alert-title">Erreurs de validation</p>
            <ul style="margin: 0.5rem 0 0 1rem; padding: 0;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<form method="POST" action="{{ route('admin.ipbx.store') }}">
    @csrf

    <div class="card mb-lg">
        <h3 class="card-title" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; margin-bottom: 1rem;">
            👤 Informations client
        </h3>

        <div class="form-group">
            <label for="client_name" class="form-label">Nom du client *</label>
            <input type="text" id="client_name" name="client_name" class="form-input" value="{{ old('client_name') }}" required>
        </div>

        <div class="form-group">
            <label for="contact_name" class="form-label">Nom du contact</label>
            <input type="text" id="contact_name" name="contact_name" class="form-input" value="{{ old('contact_name') }}">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-input" value="{{ old('email') }}">
            </div>

            <div class="form-group">
                <label for="phone" class="form-label">Téléphone</label>
                <input type="text" id="phone" name="phone" class="form-input" value="{{ old('phone') }}">
            </div>
        </div>

        <div class="form-group">
            <label for="address" class="form-label">Adresse</label>
            <textarea id="address" name="address" class="form-textarea" rows="2">{{ old('address') }}</textarea>
        </div>
    </div>

    <div class="card mb-lg">
        <h3 class="card-title" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; margin-bottom: 1rem;">
            🖥️ Connexion IPBX
        </h3>

        <div style="display: grid; grid-template-columns: 3fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="ip_address" class="form-label">Adresse IP *</label>
                <input type="text" id="ip_address" name="ip_address" class="form-input" value="{{ old('ip_address') }}" placeholder="192.168.1.100" required>
            </div>

            <div class="form-group">
                <label for="port" class="form-label">Port *</label>
                <input type="number" id="port" name="port" class="form-input" value="{{ old('port', 443) }}" min="1" max="65535" required>
            </div>
        </div>

        <p class="form-help">L'URL d'accès sera: https://[IP]:[Port]</p>
    </div>

    <div class="card mb-lg">
        <h3 class="card-title" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; margin-bottom: 1rem;">
            📝 Notes
        </h3>

        <div class="form-group">
            <label for="description" class="form-label">Description / Notes</label>
            <textarea id="description" name="description" class="form-textarea" rows="3" placeholder="Informations complémentaires...">{{ old('description') }}</textarea>
        </div>
    </div>

    <div class="form-actions">
        <a href="{{ route('admin.ipbx.index') }}" class="btn btn-ghost">Annuler</a>
        <button type="submit" class="btn btn-primary">Créer l'IPBX</button>
    </div>
</form>
@endsection
