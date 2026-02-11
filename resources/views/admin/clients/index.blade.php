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
            + Nouveau Client
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

@if($clients->count() > 0)
    <!-- Barre de recherche -->
    <div class="card mb-lg" style="padding: 1rem;">
        <input
            type="text"
            id="client-search"
            class="form-control"
            placeholder="Rechercher un client (nom, entreprise, email, téléphone)..."
            style="width: 100%; padding: 0.75rem 1rem; font-size: 1rem;"
        >
    </div>

    <!-- Liste des clients -->
    <div id="clients-list">
        @foreach($clients as $client)
            <div class="card mb-md client-list-item"
                 data-name="{{ strtolower($client->user->name) }}"
                 data-company="{{ strtolower($client->company_name) }}"
                 data-email="{{ strtolower($client->email) }}"
                 data-phone="{{ strtolower($client->phone ?? '') }}">
                <div style="display: flex; align-items: center; gap: 1.5rem;">
                    <div class="avatar avatar-lg" style="flex-shrink: 0;">
                        {{ strtoupper(substr($client->user->name, 0, 1)) }}
                    </div>

                    <div style="flex: 1; min-width: 0;">
                        <h3 style="margin: 0 0 0.25rem 0;">{{ $client->company_name }}</h3>
                        <p style="margin: 0 0 0.5rem 0; color: var(--text-secondary); font-size: 0.875rem;">
                            {{ $client->user->name }}
                        </p>
                        <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; font-size: 0.875rem; color: var(--text-tertiary);">
                            <span>{{ $client->email }}</span>
                            @if($client->phone)
                                <span>{{ $client->phone }}</span>
                            @endif
                            @if($client->is_active)
                                <span class="status status-active">Actif</span>
                            @else
                                <span class="status status-inactive">Inactif</span>
                            @endif
                        </div>
                    </div>

                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; flex-shrink: 0;">
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
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Message si aucun résultat -->
    <div id="no-results" class="card" style="display: none;">
        <div class="empty-state">
            <div class="empty-icon">🔍</div>
            <p class="empty-title">Aucun résultat</p>
            <p class="empty-description">Aucun client ne correspond à votre recherche.</p>
        </div>
    </div>

    <script>
        document.getElementById('client-search').addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const items = document.querySelectorAll('.client-list-item');
            let visibleCount = 0;

            items.forEach(function(item) {
                const name = item.dataset.name || '';
                const company = item.dataset.company || '';
                const email = item.dataset.email || '';
                const phone = item.dataset.phone || '';
                const matches = name.includes(query) || company.includes(query) || email.includes(query) || phone.includes(query);

                item.style.display = matches ? '' : 'none';
                if (matches) visibleCount++;
            });

            document.getElementById('no-results').style.display = visibleCount === 0 ? '' : 'none';
        });
    </script>
@else
    <div class="card">
        <div class="empty-state">
            <div class="empty-icon">👥</div>
            <p class="empty-title">Aucun client</p>
            <p class="empty-description">Vous n'avez pas encore de clients enregistrés. Commencez par en créer un.</p>
            <a href="{{ route('admin.clients.create') }}" class="btn btn-primary">
                + Créer un client
            </a>
        </div>
    </div>
@endif
@endsection
