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
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background-color: var(--bg-secondary);">
                <th style="text-align: left; padding: 0.75rem; border-bottom: 1px solid var(--border-color);">Ordre</th>
                <th style="text-align: left; padding: 0.75rem; border-bottom: 1px solid var(--border-color);">Nom</th>
                <th style="text-align: left; padding: 0.75rem; border-bottom: 1px solid var(--border-color);">Description</th>
                <th style="text-align: center; padding: 0.75rem; border-bottom: 1px solid var(--border-color);">Statut</th>
                <th style="text-align: center; padding: 0.75rem; border-bottom: 1px solid var(--border-color);">Clients</th>
                <th style="text-align: right; padding: 0.75rem; border-bottom: 1px solid var(--border-color);">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($connectionTypes as $type)
                <tr>
                    <td style="padding: 0.75rem; border-bottom: 1px solid var(--border-color);">
                        {{ $type->sort_order }}
                    </td>
                    <td style="padding: 0.75rem; border-bottom: 1px solid var(--border-color); font-weight: 500;">
                        {{ $type->name }}
                    </td>
                    <td style="padding: 0.75rem; border-bottom: 1px solid var(--border-color); color: var(--text-secondary);">
                        {{ $type->description ?? '-' }}
                    </td>
                    <td style="padding: 0.75rem; border-bottom: 1px solid var(--border-color); text-align: center;">
                        @if($type->is_active)
                            <span style="background-color: var(--color-success); color: white; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.75rem;">Actif</span>
                        @else
                            <span style="background-color: var(--color-danger); color: white; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.75rem;">Inactif</span>
                        @endif
                    </td>
                    <td style="padding: 0.75rem; border-bottom: 1px solid var(--border-color); text-align: center;">
                        {{ $type->clients->count() }}
                    </td>
                    <td style="padding: 0.75rem; border-bottom: 1px solid var(--border-color); text-align: right;">
                        <a href="{{ route('admin.connection-types.edit', $type) }}" class="btn btn-sm btn-outline">Modifier</a>
                        <form action="{{ route('admin.connection-types.destroy', $type) }}" method="POST" style="display: inline;" onsubmit="return confirm('Supprimer ce type de connexion ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="padding: 2rem; text-align: center; color: var(--text-secondary);">
                        Aucun type de connexion. <a href="{{ route('admin.connection-types.create') }}">Créer le premier</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
