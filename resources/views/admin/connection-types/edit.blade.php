@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1 class="page-title">
        Modifier le type de connexion
        <small>{{ $connectionType->name }}</small>
    </h1>
    <div class="page-actions">
        <a href="{{ route('admin.connection-types.index') }}" class="btn btn-ghost">
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

    <form method="POST" action="{{ route('admin.connection-types.update', $connectionType) }}">
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
                value="{{ old('name', $connectionType->name) }}"
                required
                class="form-input"
            >
        </div>

        <div class="form-group">
            <label for="description" class="form-label">Description</label>
            <input
                type="text"
                id="description"
                name="description"
                value="{{ old('description', $connectionType->description) }}"
                class="form-input"
            >
        </div>

        <div class="form-group">
            <label for="sort_order" class="form-label">Ordre d'affichage</label>
            <input
                type="number"
                id="sort_order"
                name="sort_order"
                value="{{ old('sort_order', $connectionType->sort_order) }}"
                min="0"
                class="form-input"
            >
        </div>

        <div class="form-group">
            <label class="form-check">
                <input
                    type="checkbox"
                    name="is_active"
                    {{ old('is_active', $connectionType->is_active) ? 'checked' : '' }}
                >
                <span class="form-check-label">Actif</span>
            </label>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.connection-types.index') }}" class="btn btn-ghost">Annuler</a>
            <button type="submit" class="btn btn-primary">Mettre à jour</button>
        </div>
    </form>
</div>
@endsection
