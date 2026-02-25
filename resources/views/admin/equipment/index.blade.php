@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1 class="page-title">
        Équipements
        <small>Gérer les équipements disponibles</small>
    </h1>
    <div class="page-actions">
        <a href="{{ route('admin.equipment.create') }}" class="btn btn-primary">
            + Nouvel équipement
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-lg">
        <span class="alert-icon">✓</span>
        <div class="alert-content">
            {{ session('success') }}
        </div>
    </div>
@endif

<div class="card">
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Catégorie</th>
                    <th class="text-center">Type</th>
                    <th class="text-center">Statut</th>
                    <th class="text-center">Clients</th>
                    <th class="actions-cell">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($equipment as $eq)
                    <tr>
                        <td data-label="Nom" style="font-weight: 500;">{{ $eq->name }}</td>
                        <td data-label="Catégorie" style="color: var(--text-secondary);">{{ $eq->category ?? '-' }}</td>
                        <td data-label="Type" class="text-center">
                            @if($eq->is_predefined)
                                <span style="background-color: var(--color-primary); color: white; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.75rem;">Prédéfini</span>
                            @else
                                <span style="background-color: var(--color-warning); color: white; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.75rem;">Personnalisé</span>
                            @endif
                        </td>
                        <td data-label="Statut" class="text-center">
                            @if($eq->is_active)
                                <span style="background-color: var(--color-success); color: white; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.75rem;">Actif</span>
                            @else
                                <span style="background-color: var(--color-danger); color: white; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.75rem;">Inactif</span>
                            @endif
                        </td>
                        <td data-label="Clients" class="text-center">{{ $eq->clients->count() }}</td>
                        <td class="actions-cell">
                            <a href="{{ route('admin.equipment.edit', $eq) }}" class="btn btn-sm btn-outline">Modifier</a>
                            <form action="{{ route('admin.equipment.destroy', $eq) }}" method="POST" onsubmit="return confirm('Supprimer cet équipement ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding: 2rem; text-align: center; color: var(--text-secondary);">
                            Aucun équipement. <a href="{{ route('admin.equipment.create') }}">Créer le premier</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
