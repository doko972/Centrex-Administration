@extends('layouts.app')

@section('content')
<div style="margin-bottom: 2rem;">
    <h1>Modifier le Centrex</h1>
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

    <form method="POST" action="{{ route('admin.centrex.update', $centrex) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--border-color);">Informations générales</h3>

        <div style="margin-bottom: 1rem;">
            <label for="name" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Nom du centrex *</label>
            <input 
                type="text" 
                id="name" 
                name="name" 
                value="{{ old('name', $centrex->name) }}" 
                required
                style="width: 100%;"
            >
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="description" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Description</label>
            <textarea 
                id="description" 
                name="description" 
                rows="3"
                style="width: 100%;"
            >{{ old('description', $centrex->description) }}</textarea>
        </div>

        @if($centrex->image)
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Image actuelle</label>
                <img src="{{ asset('storage/' . $centrex->image) }}" alt="{{ $centrex->name }}" style="max-width: 200px; border-radius: var(--border-radius); border: 1px solid var(--border-color);">
            </div>
        @endif

        <div style="margin-bottom: 1.5rem;">
            <label for="image" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Changer l'image / Logo</label>
            <input 
                type="file" 
                id="image" 
                name="image" 
                accept="image/*"
                style="width: 100%;"
            >
            <small style="color: var(--text-tertiary);">Formats acceptés: JPEG, PNG, JPG, GIF (max 2 Mo)</small>
        </div>

        <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--border-color);">Connexion FreePBX</h3>

        <div style="display: grid; grid-template-columns: 3fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div>
                <label for="ip_address" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Adresse IP *</label>
                <input 
                    type="text" 
                    id="ip_address" 
                    name="ip_address" 
                    value="{{ old('ip_address', $centrex->ip_address) }}" 
                    required
                    style="width: 100%;"
                >
            </div>

            <div>
                <label for="port" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Port *</label>
                <input 
                    type="number" 
                    id="port" 
                    name="port" 
                    value="{{ old('port', $centrex->port) }}" 
                    required
                    min="1"
                    max="65535"
                    style="width: 100%;"
                >
            </div>
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="login" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Login *</label>
            <input 
                type="text" 
                id="login" 
                name="login" 
                value="{{ old('login', $centrex->login) }}" 
                required
                style="width: 100%;"
            >
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label for="password" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Mot de passe</label>
            <div style="position: relative;">
                <input
                    type="password"
                    id="password"
                    name="password"
                    style="width: 100%; padding-right: 3rem;"
                    placeholder="Laisser vide pour conserver l'actuel"
                >
                <button
                    type="button"
                    onclick="togglePassword('password', this)"
                    style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 1.25rem; padding: 0.25rem;"
                    title="Afficher/Masquer le mot de passe"
                >
                    <span class="eye-icon">👁️</span>
                    <span class="eye-off-icon" style="display: none;">🔒</span>
                </button>
            </div>
            <small style="color: var(--text-tertiary);">Laisser vide si vous ne souhaitez pas le modifier</small>
        </div>

        <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--border-color);">État et statut</h3>

        <div style="margin-bottom: 1rem;">
            <label for="status" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Statut *</label>
            <select id="status" name="status" required style="width: 100%;">
                <option value="online" {{ old('status', $centrex->status) === 'online' ? 'selected' : '' }}>En ligne</option>
                <option value="offline" {{ old('status', $centrex->status) === 'offline' ? 'selected' : '' }}>Hors ligne</option>
                <option value="maintenance" {{ old('status', $centrex->status) === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
            </select>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: flex; align-items: center; cursor: pointer;">
                <input type="checkbox" name="is_active" {{ $centrex->is_active ? 'checked' : '' }} style="margin-right: 0.5rem; width: auto;">
                <span style="font-weight: 500;">Centrex actif</span>
            </label>
        </div>

        <div style="display: flex; gap: 1rem; justify-content: flex-end;">
            <a href="{{ route('admin.centrex.index') }}" class="btn btn-outline">Annuler</a>
            <button type="submit" class="btn btn-primary">Mettre à jour</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);
    const eyeIcon = button.querySelector('.eye-icon');
    const eyeOffIcon = button.querySelector('.eye-off-icon');

    if (input.type === 'password') {
        input.type = 'text';
        eyeIcon.style.display = 'none';
        eyeOffIcon.style.display = 'inline';
    } else {
        input.type = 'password';
        eyeIcon.style.display = 'inline';
        eyeOffIcon.style.display = 'none';
    }
}
</script>
@endpush