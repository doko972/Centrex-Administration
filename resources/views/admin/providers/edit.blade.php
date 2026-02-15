@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1 class="page-title">
        Modifier le fournisseur
        <small>{{ $provider->name }}</small>
    </h1>
    <div class="page-actions">
        <a href="{{ route('admin.providers.index') }}" class="btn btn-ghost">
            ← Retour
        </a>
    </div>
</div>

<div class="card" style="max-width: 600px;">
    @if ($errors->any())
        <div class="alert alert-danger mb-lg">
            <span class="alert-icon">!</span>
            <div class="alert-content">
                <ul style="margin: 0; padding-left: 1rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.providers.update', $provider) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name" class="form-label">
                Nom <span class="required">*</span>
            </label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name', $provider->name) }}"
                required
                class="form-input"
            >
        </div>

        <div class="form-group">
            <label for="url" class="form-label">URL du site</label>
            <input
                type="url"
                id="url"
                name="url"
                value="{{ old('url', $provider->url) }}"
                class="form-input"
                placeholder="https://www.exemple.com"
            >
        </div>

        <div class="form-group">
            <label style="display: flex; align-items: center; cursor: pointer;">
                <input
                    type="checkbox"
                    name="is_active"
                    {{ old('is_active', $provider->is_active) ? 'checked' : '' }}
                    style="margin-right: 0.5rem; width: auto;"
                >
                <span style="font-weight: 500;">Actif</span>
            </label>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.providers.index') }}" class="btn btn-ghost">Annuler</a>
            <button type="submit" class="btn btn-primary">Mettre à jour</button>
        </div>
    </form>
</div>
@endsection
