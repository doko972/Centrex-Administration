@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1 class="page-title">
        Types de connexion
        <small>Gérer les types de connexion disponibles</small>
    </h1>
    <div class="page-actions">
        <a href="{{ route('admin.connection-types.create') }}" class="btn btn-primary">
            + Nouveau type
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
                    <th>Ordre</th>
                    <th>Nom</th>
                    <th>Description</th>
                    <th class="text-center">Statut</th>
                    <th class="text-center">Clients</th>
                    <th class="actions-cell">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($connectionTypes as $type)
                    <tr>
                        <td data-label="Ordre">{{ $type->sort_order }}</td>
                        <td data-label="Nom" style="font-weight: 500;">{{ $type->name }}</td>
                        <td data-label="Description" style="color: var(--text-secondary);">
                            {{ $type->description ?? '-' }}
                        </td>
                        <td data-label="Statut" class="text-center">
                            @if($type->is_active)
                                <span style="background-color: var(--color-success); color: white; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.75rem;">Actif</span>
                            @else
                                <span style="background-color: var(--color-danger); color: white; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.75rem;">Inactif</span>
                            @endif
                        </td>
                        <td data-label="Clients" class="text-center">{{ $type->clients->count() }}</td>
                        <td class="actions-cell">
                            <a href="{{ route('admin.connection-types.edit', $type) }}" class="btn btn-sm btn-outline">Modifier</a>
                            <form action="{{ route('admin.connection-types.destroy', $type) }}" method="POST" onsubmit="return confirm('Supprimer ce type de connexion ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding: 2rem; text-align: center; color: var(--text-secondary);">
                            Aucun type de connexion.
                            <a href="{{ route('admin.connection-types.create') }}">Créer le premier</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
