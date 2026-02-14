@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1 class="page-title">
        Gestion des Centrex
        <small>{{ $centrex->count() }} centrex enregistré(s)</small>
    </h1>
    <div class="page-actions">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost">
            ← Retour
        </a>
        <a href="{{ route('admin.centrex.create') }}" class="btn btn-primary">
            + Nouveau
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

@if($centrex->count() > 0)
    <!-- Barre de recherche -->
    <div class="card mb-lg" style="padding: 1rem;">
        <input
            type="text"
            id="centrex-search"
            class="form-control"
            placeholder="Rechercher un centrex (nom, IP, description)..."
            style="width: 100%; padding: 0.75rem 1rem; font-size: 1rem;"
        >
    </div>

    <!-- Liste des centrex -->
    <div id="centrex-list">
        @foreach($centrex as $item)
            <div class="card mb-md centrex-list-item centrex-card"
                 data-name="{{ strtolower($item->name) }}"
                 data-ip="{{ strtolower($item->ip_address) }}"
                 data-description="{{ strtolower($item->description ?? '') }}">
                <div class="centrex-card__content">
                    <div class="centrex-card__header">
                        @if($item->image)
                            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="centrex-card__image">
                        @else
                            <div class="centrex-card__image centrex-card__image--placeholder">
                                📞
                            </div>
                        @endif
                        <div class="centrex-card__title-wrap">
                            <h3 class="centrex-card__title">{{ $item->name }}</h3>
                        </div>
                    </div>

                    <div class="centrex-card__body">
                        <p class="centrex-card__address">
                            {{ $item->ip_address }}:{{ $item->port }}
                        </p>
                        <div class="centrex-card__meta">
                            @if($item->status === 'online')
                                <span class="status status-online">En ligne</span>
                            @elseif($item->status === 'offline')
                                <span class="status status-offline">Hors ligne</span>
                            @else
                                <span class="status status-maintenance">Maintenance</span>
                            @endif

                            @if(!$item->is_active)
                                <span class="badge badge-danger">Inactif</span>
                            @endif

                            <span class="centrex-card__clients">
                                {{ $item->clients->count() }} client(s) associé(s)
                            </span>
                        </div>
                    </div>

                    <div class="centrex-card__actions">
                        @if($item->is_active && $item->status === 'online')
                            <a href="{{ route('admin.centrex.view', $item) }}" class="btn btn-sm btn-primary">
                                Ouvrir FreePBX
                            </a>
                        @endif
                        <a href="{{ route('admin.centrex.show', $item) }}" class="btn btn-sm btn-soft-primary">
                            Voir
                        </a>
                        <a href="{{ route('admin.centrex.edit', $item) }}" class="btn btn-sm btn-soft-secondary">
                            Modifier
                        </a>
                        <form method="POST" action="{{ route('admin.centrex.destroy', $item) }}" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-soft-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce centrex ?')">
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
            <p class="empty-description">Aucun centrex ne correspond à votre recherche.</p>
        </div>
    </div>

    <script>
        document.getElementById('centrex-search').addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const items = document.querySelectorAll('.centrex-list-item');
            let visibleCount = 0;

            items.forEach(function(item) {
                const name = item.dataset.name || '';
                const ip = item.dataset.ip || '';
                const description = item.dataset.description || '';
                const matches = name.includes(query) || ip.includes(query) || description.includes(query);

                item.style.display = matches ? '' : 'none';
                if (matches) visibleCount++;
            });

            document.getElementById('no-results').style.display = visibleCount === 0 ? '' : 'none';
        });
    </script>
@else
    <div class="card">
        <div class="empty-state">
            <div class="empty-icon">📞</div>
            <p class="empty-title">Aucun centrex</p>
            <p class="empty-description">Vous n'avez pas encore de centrex enregistrés. Commencez par en créer un.</p>
            <a href="{{ route('admin.centrex.create') }}" class="btn btn-primary">
                + Créer un centrex
            </a>
        </div>
    </div>
@endif
@endsection
