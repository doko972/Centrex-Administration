@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1 class="page-title">
        Fournisseurs
        <small>Gérer les fournisseurs disponibles</small>
    </h1>
    <div class="page-actions">
        <a href="{{ route('admin.providers.create') }}" class="btn btn-primary">
            + Nouveau fournisseur
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
                    <th>URL</th>
                    <th class="text-center">Statut</th>
                    <th class="text-center">Clients</th>
                    <th class="actions-cell">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($providers as $provider)
                    <tr>
                        <td data-label="Nom" style="font-weight: 500;">{{ $provider->name }}</td>
                        <td data-label="URL">
                            @if($provider->url)
                                <a href="{{ $provider->url }}" target="_blank" style="color: var(--color-primary);">
                                    {{ Str::limit($provider->url, 40) }}
                                </a>
                            @else
                                <span style="color: var(--text-tertiary);">-</span>
                            @endif
                        </td>
                        <td data-label="Statut" class="text-center">
                            @if($provider->is_active)
                                <span style="background-color: var(--color-success); color: white; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.75rem;">Actif</span>
                            @else
                                <span style="background-color: var(--color-danger); color: white; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.75rem;">Inactif</span>
                            @endif
                        </td>
                        <td data-label="Clients" class="text-center">{{ $provider->clients->count() }}</td>
                        <td class="actions-cell">
                            <a href="{{ route('admin.providers.edit', $provider) }}" class="btn btn-sm btn-outline">Modifier</a>
                            <form action="{{ route('admin.providers.destroy', $provider) }}" method="POST" onsubmit="return confirm('Supprimer ce fournisseur ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="padding: 2rem; text-align: center; color: var(--text-secondary);">
                            Aucun fournisseur. <a href="{{ route('admin.providers.create') }}">Créer le premier</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
