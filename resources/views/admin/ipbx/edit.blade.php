@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1 class="page-title">Modifier l'IPBX</h1>
    <div class="page-actions">
        <a href="{{ route('admin.ipbx.index') }}" class="btn btn-ghost">
            Retour a la liste
        </a>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger mb-lg">
        <span class="alert-icon">!</span>
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

<form method="POST" action="{{ route('admin.ipbx.update', $ipbx) }}">
    @csrf
    @method('PUT')

    <div class="card mb-lg">
        <h3 class="card-title" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; margin-bottom: 1rem;">
            Informations client
        </h3>

        <div class="form-group">
            <label for="client_name" class="form-label">Nom du client *</label>
            <input type="text" id="client_name" name="client_name" class="form-input" value="{{ old('client_name', $ipbx->client_name) }}" required>
        </div>

        <div class="form-group">
            <label for="contact_name" class="form-label">Nom du contact</label>
            <input type="text" id="contact_name" name="contact_name" class="form-input" value="{{ old('contact_name', $ipbx->contact_name) }}">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-input" value="{{ old('email', $ipbx->email) }}">
            </div>

            <div class="form-group">
                <label for="phone" class="form-label">Telephone</label>
                <input type="text" id="phone" name="phone" class="form-input" value="{{ old('phone', $ipbx->phone) }}">
            </div>
        </div>

        <div class="form-group">
            <label for="address" class="form-label">Adresse</label>
            <textarea id="address" name="address" class="form-textarea" rows="2">{{ old('address', $ipbx->address) }}</textarea>
        </div>
    </div>

    <div class="card mb-lg">
        <h3 class="card-title" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; margin-bottom: 1rem;">
            Connexion IPBX
        </h3>

        <div style="display: grid; grid-template-columns: 3fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="ip_address" class="form-label">Adresse IP *</label>
                <input type="text" id="ip_address" name="ip_address" class="form-input" value="{{ old('ip_address', $ipbx->ip_address) }}" placeholder="192.168.1.100" required>
            </div>

            <div class="form-group">
                <label for="port" class="form-label">Port *</label>
                <input type="number" id="port" name="port" class="form-input" value="{{ old('port', $ipbx->port) }}" min="1" max="65535" required>
            </div>
        </div>

        <p class="form-help">L'URL d'acces sera: https://{{ $ipbx->ip_address }}:{{ $ipbx->port }}</p>
    </div>

    <div class="card mb-lg">
        <h3 class="card-title" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; margin-bottom: 1rem;">
            Authentification FreePBX
        </h3>

        <p class="form-help" style="margin-bottom: 1rem;">
            @if($ipbx->login)
                <span style="color: var(--color-success);">Proxy active</span> - Les identifiants sont configures.
            @else
                <span style="color: var(--text-secondary);">Acces direct</span> - Aucun identifiant configure.
            @endif
        </p>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="login" class="form-label">Login FreePBX</label>
                <input type="text" id="login" name="login" class="form-input" value="{{ old('login', $ipbx->login) }}" placeholder="admin">
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Mot de passe FreePBX</label>
                <div style="position: relative;">
                    <input type="password" id="password" name="password" class="form-input" placeholder="Laisser vide pour ne pas modifier">
                    <button type="button" onclick="togglePassword('password')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-secondary);">
                        Voir
                    </button>
                </div>
                <p class="form-help">Laissez vide pour conserver le mot de passe actuel.</p>
            </div>
        </div>
    </div>

    <div class="card mb-lg">
        <h3 class="card-title" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; margin-bottom: 1rem;">
            Notes
        </h3>

        <div class="form-group">
            <label for="description" class="form-label">Description / Notes</label>
            <textarea id="description" name="description" class="form-textarea" rows="3">{{ old('description', $ipbx->description) }}</textarea>
        </div>
    </div>

    <div class="card mb-lg">
        <h3 class="card-title" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; margin-bottom: 1rem;">
            Etat
        </h3>

        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
            <span>Statut actuel:</span>
            @if($ipbx->status === 'online')
                <span class="status status-online">En ligne</span>
            @else
                <span class="status status-offline">Hors ligne</span>
            @endif
            @if($ipbx->last_ping)
                <span style="color: var(--text-tertiary); font-size: 0.875rem;">
                    (Dernier ping: {{ $ipbx->last_ping->format('d/m/Y H:i:s') }})
                </span>
            @endif
        </div>

        <div class="form-group">
            <label class="checkbox-label" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $ipbx->is_active) ? 'checked' : '' }}>
                <span>IPBX actif</span>
            </label>
            <p class="form-help">Decochez pour desactiver temporairement cet IPBX sans le supprimer.</p>
        </div>
    </div>

    <div class="form-actions">
        <a href="{{ route('admin.ipbx.index') }}" class="btn btn-ghost">Annuler</a>
        <button type="submit" class="btn btn-primary">Mettre a jour</button>
    </div>
</form>

<script>
function togglePassword(fieldId) {
    var field = document.getElementById(fieldId);
    if (field.type === 'password') {
        field.type = 'text';
    } else {
        field.type = 'password';
    }
}
</script>
@endsection
