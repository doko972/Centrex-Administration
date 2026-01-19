
@extends('layouts.app')

@section('content')
<div style="margin-bottom: 2rem;">
    <h1>Nouveau Centrex</h1>
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

    <form method="POST" action="{{ route('admin.centrex.store') }}" enctype="multipart/form-data">
        @csrf

        <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--border-color);">Informations générales</h3>

        <div style="margin-bottom: 1rem;">
            <label for="name" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Nom du centrex *</label>
            <input 
                type="text" 
                id="name" 
                name="name" 
                value="{{ old('name') }}" 
                required
                style="width: 100%;"
                placeholder="Ex: Centrex Paris"
            >
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="description" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Description</label>
            <textarea 
                id="description" 
                name="description" 
                rows="3"
                style="width: 100%;"
                placeholder="Description optionnelle du centrex"
            >{{ old('description') }}</textarea>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label for="image" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Image / Logo</label>
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
                    value="{{ old('ip_address') }}" 
                    required
                    style="width: 100%;"
                    placeholder="Ex: 192.168.1.100"
                >
            </div>

            <div>
                <label for="port" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Port *</label>
                <input 
                    type="number" 
                    id="port" 
                    name="port" 
                    value="{{ old('port', 80) }}" 
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
                value="{{ old('login') }}" 
                required
                style="width: 100%;"
                placeholder="Identifiant FreePBX"
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
                placeholder="Mot de passe FreePBX"
            >
            <small style="color: var(--text-tertiary);">Le mot de passe sera chiffré en base de données</small>
        </div>

        <div style="display: flex; gap: 1rem; justify-content: flex-end;">
            <a href="{{ route('admin.centrex.index') }}" class="btn btn-outline">Annuler</a>
            <button type="submit" class="btn btn-primary">Créer le centrex</button>
        </div>
    </form>
</div>
@endsection