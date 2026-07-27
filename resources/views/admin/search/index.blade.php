@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
    ['label' => 'Recherche'],
]" />
<div class="page-header">
    <h1 class="page-title">
        Recherche
        @if($query !== '')
            <small>{{ $totalResults }} résultat(s) pour « {{ $query }} »</small>
        @endif
    </h1>
    <div class="page-actions">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost">
            ← Retour
        </a>
    </div>
</div>

<div class="card mb-lg" style="padding: 1rem;">
    <form method="GET" action="{{ route('admin.search') }}" style="display: flex; gap: 0.75rem;">
        <input
            type="text"
            name="q"
            value="{{ $query }}"
            class="form-control"
            placeholder="Rechercher un client, un centrex, un IPBX (nom, email, IP, téléphone...)"
            style="flex: 1; padding: 0.75rem 1rem; font-size: 1rem;"
            autofocus
        >
        <button type="submit" class="btn btn-primary">Rechercher</button>
    </form>
</div>

@if($query === '')
    <div class="card">
        <div class="empty-state">
            <div class="empty-icon">🔍</div>
            <p class="empty-title">Recherchez à travers toute la plateforme</p>
            <p class="empty-description">Saisissez un nom, une entreprise, un email, une adresse IP ou un numéro de téléphone.</p>
        </div>
    </div>
@elseif($totalResults === 0)
    <div class="card">
        <div class="empty-state">
            <div class="empty-icon">🔍</div>
            <p class="empty-title">Aucun résultat</p>
            <p class="empty-description">Aucun client, centrex ou IPBX ne correspond à « {{ $query }} ».</p>
        </div>
    </div>
@else
    @if($clients->count() > 0)
        <div class="card mb-lg">
            <h3 class="section-title mb-lg" style="padding-bottom: 0.75rem; border-bottom: 2px solid var(--border-color);">
                Clients ({{ $clients->count() }})
            </h3>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Entreprise</th>
                            <th>Contact</th>
                            <th>Email</th>
                            <th class="actions-cell">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clients as $client)
                            <tr>
                                <td data-label="Entreprise" style="font-weight: 500;">{{ $client->company_name }}</td>
                                <td data-label="Contact">{{ $client->user->name }}</td>
                                <td data-label="Email">{{ $client->email }}</td>
                                <td class="actions-cell">
                                    <a href="{{ route('admin.clients.show', $client) }}" class="btn btn-sm btn-soft-primary">Voir</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if($centrex->count() > 0)
        <div class="card mb-lg">
            <h3 class="section-title mb-lg" style="padding-bottom: 0.75rem; border-bottom: 2px solid var(--border-color);">
                Centrex ({{ $centrex->count() }})
            </h3>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Adresse IP</th>
                            <th class="text-center">Statut</th>
                            <th class="actions-cell">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($centrex as $item)
                            <tr>
                                <td data-label="Nom" style="font-weight: 500;">{{ $item->name }}</td>
                                <td data-label="Adresse IP" style="font-family: monospace;">{{ $item->ip_address }}</td>
                                <td data-label="Statut" class="text-center">
                                    @if($item->status === 'online')
                                        <span class="status status-online">En ligne</span>
                                    @elseif($item->status === 'offline')
                                        <span class="status status-offline">Hors ligne</span>
                                    @else
                                        <span class="status status-maintenance">Maintenance</span>
                                    @endif
                                </td>
                                <td class="actions-cell">
                                    <a href="{{ route('admin.centrex.show', $item) }}" class="btn btn-sm btn-soft-primary">Voir</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if($ipbxs->count() > 0)
        <div class="card mb-lg">
            <h3 class="section-title mb-lg" style="padding-bottom: 0.75rem; border-bottom: 2px solid var(--border-color);">
                IPBX ({{ $ipbxs->count() }})
            </h3>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Adresse IP</th>
                            <th class="text-center">Statut</th>
                            <th class="actions-cell">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ipbxs as $ipbx)
                            <tr>
                                <td data-label="Client" style="font-weight: 500;">{{ $ipbx->client_name }}</td>
                                <td data-label="Adresse IP" style="font-family: monospace;">{{ $ipbx->ip_address }}</td>
                                <td data-label="Statut" class="text-center">
                                    @if($ipbx->status === 'online')
                                        <span class="status status-online">En ligne</span>
                                    @else
                                        <span class="status status-offline">Hors ligne</span>
                                    @endif
                                </td>
                                <td class="actions-cell">
                                    <a href="{{ route('admin.ipbx.show', $ipbx) }}" class="btn btn-sm btn-soft-primary">Voir</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endif
@endsection
