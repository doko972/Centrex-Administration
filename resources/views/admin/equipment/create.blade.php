@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1 class="page-title">
        Nouvel équipement
    </h1>
    <div class="page-actions">
        <a href="{{ route('admin.equipment.index') }}" class="btn btn-ghost">
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

    <form method="POST" action="{{ route('admin.equipment.store') }}">
        @csrf

        <div class="form-group">
            <label for="name" class="form-label">
                Nom <span class="required">*</span>
            </label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}"
                required
                class="form-input"
                placeholder="Ex: Routeur, Switch, Téléphone IP..."
            >
        </div>

        <div class="form-group">
            <label for="category" class="form-label">Catégorie</label>
            <input
                type="text"
                id="category"
                name="category"
                value="{{ old('category') }}"
                class="form-input"
                list="categories"
                placeholder="Ex: Réseau, Téléphonie, Sécurité..."
            >
            <datalist id="categories">
                @foreach($categories as $cat)
                    <option value="{{ $cat }}">
                @endforeach
            </datalist>
        </div>

        <div class="form-group">
            <label class="form-check">
                <input
                    type="checkbox"
                    name="is_predefined"
                    checked
                >
                <span class="form-check-label">Équipement prédéfini</span>
            </label>
            <p class="form-help">Les équipements prédéfinis apparaissent dans la liste de sélection lors de la création de clients.</p>
        </div>

        <div class="form-group">
            <label class="form-check">
                <input
                    type="checkbox"
                    name="is_active"
                    checked
                >
                <span class="form-check-label">Actif</span>
            </label>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.equipment.index') }}" class="btn btn-ghost">Annuler</a>
            <button type="submit" class="btn btn-primary">Créer</button>
        </div>
    </form>
</div>
@endsection
