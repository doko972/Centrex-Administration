@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1 class="page-title">
        Gestion des Clients
        <small>{{ $clients->count() }} client(s) enregistré(s)</small>
    </h1>
    <div class="page-actions">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost">
            ← Retour
        </a>
        <a href="{{ route('admin.clients.create') }}" class="btn btn-primary">
            ➕ Nouveau Client
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-lg">
        <span class="alert-icon">✓</span>
        <div class="alert-content">
            <p class="alert-message">{{ session('success') }}</p>
        </div>
    </div>
@endif

<div class="card">
    @if($clients->count() > 0)
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Entreprise</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th class="text-center">Statut</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($clients as $client)
                        <tr>
                            <td>
                                <div class="d-flex items-center gap-sm">
                                    <div class="avatar avatar-sm">{{ strtoupper(substr($client->user->name, 0, 1)) }}</div>
                                    <span class="font-medium">{{ $client->user->name }}</span>
                                </div>
                            </td>
                            <td>{{ $client->company_name }}</td>
                            <td>{{ $client->email }}</td>
                            <td>{{ $client->phone ?? '-' }}</td>
                            <td class="text-center">
                                @if($client->is_active)
                                    <span class="status status-active">Actif</span>
                                @else
                                    <span class="status status-inactive">Inactif</span>
                                @endif
                            </td>
                            <td class="actions-cell">
                                <a href="{{ route('admin.clients.show', $client) }}" class="btn btn-sm btn-soft-primary">
                                    Voir
                                </a>
                                <a href="{{ route('admin.clients.edit', $client) }}" class="btn btn-sm btn-soft-secondary">
                                    Modifier
                                </a>
                                <form method="POST" action="{{ route('admin.clients.destroy', $client) }}" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-soft-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce client ?')">
                                        Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">
            <div class="empty-icon">👥</div>
            <p class="empty-title">Aucun client</p>
            <p class="empty-description">Vous n'avez pas encore de clients enregistrés. Commencez par en créer un.</p>
            <a href="{{ route('admin.clients.create') }}" class="btn btn-primary">
                ➕ Créer un client
            </a>
        </div>
    @endif
</div>
@endsection
