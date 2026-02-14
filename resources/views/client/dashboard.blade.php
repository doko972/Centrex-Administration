@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">
            Mon Espace
            <small>{{ $client->company_name }}</small>
        </h1>
    </div>
</div>

<div class="card mb-xl">
    <div class="d-flex items-center gap-lg">
        <div class="avatar avatar-lg">
            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
        </div>
        <div>
            <h3 class="mb-xs">Bienvenue, {{ Auth::user()->name }}</h3>
            <p class="text-secondary mb-0">Voici la liste de vos centrex et IPBX FreePBX. Cliquez sur un element pour y acceder.</p>
        </div>
    </div>
</div>

{{-- Section Centrex --}}
<h2 style="margin-bottom: 1rem; font-size: 1.25rem;">Mes Centrex</h2>

@if ($centrex->count() > 0)
    <!-- Barre de recherche Centrex -->
    <div class="card mb-lg" style="padding: 1rem;">
        <div class="search-box">
            <input
                type="text"
                id="centrex-search"
                class="form-control"
                placeholder="Rechercher un centrex..."
                style="width: 100%; padding: 0.75rem 1rem; font-size: 1rem;"
            >
        </div>
    </div>

    <!-- Liste des centrex -->
    <div class="centrex-list" id="centrex-list">
        @foreach ($centrex as $item)
            <div class="card centrex-list-item centrex-card mb-md" data-name="{{ strtolower($item->name) }}" data-description="{{ strtolower($item->description ?? '') }}">
                <div class="centrex-card__content">
                    <div class="centrex-card__header">
                        @if ($item->image)
                            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="centrex-card__image">
                        @else
                            <div class="centrex-card__image centrex-card__image--placeholder">
                                C
                            </div>
                        @endif
                        <div class="centrex-card__title-wrap">
                            <h3 class="centrex-card__title">{{ $item->name }}</h3>
                        </div>
                    </div>

                    <div class="centrex-card__body">
                        <h3>{{ $item->name }}</h3>
                        @if ($item->description)
                            <p class="centrex-card__description">
                                {{ Str::limit($item->description, 100) }}
                            </p>
                        @endif
                        <div class="centrex-card__meta">
                            @if ($item->status === 'online')
                                <span class="status status-online">En ligne</span>
                            @elseif($item->status === 'offline')
                                <span class="status status-offline">Hors ligne</span>
                            @else
                                <span class="status status-maintenance">Maintenance</span>
                            @endif
                            <span class="centrex-card__clients">
                                {{ $item->ip_address }}{{ $item->port != 80 ? ':' . $item->port : '' }}
                            </span>
                        </div>
                    </div>

                    <div class="centrex-card__actions">
                        <a href="{{ route('client.centrex.view', $item) }}" class="btn btn-primary">
                            Acceder
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Message si aucun resultat centrex -->
    <div id="no-results-centrex" class="card" style="display: none;">
        <div class="empty-state">
            <div class="empty-icon">?</div>
            <p class="empty-title">Aucun resultat</p>
            <p class="empty-description">Aucun centrex ne correspond a votre recherche.</p>
        </div>
    </div>
@else
    <div class="card mb-xl">
        <div class="empty-state">
            <div class="empty-icon">C</div>
            <p class="empty-title">Aucun centrex</p>
            <p class="empty-description">
                Aucun centrex n'est actuellement associe a votre compte.
            </p>
        </div>
    </div>
@endif

{{-- Section IPBX --}}
<h2 style="margin: 2rem 0 1rem 0; font-size: 1.25rem;">Mes IPBX</h2>

@if ($ipbx->count() > 0)
    <!-- Barre de recherche IPBX -->
    <div class="card mb-lg" style="padding: 1rem;">
        <div class="search-box">
            <input
                type="text"
                id="ipbx-search"
                class="form-control"
                placeholder="Rechercher un IPBX..."
                style="width: 100%; padding: 0.75rem 1rem; font-size: 1rem;"
            >
        </div>
    </div>

    <!-- Liste des IPBX -->
    <div class="ipbx-list" id="ipbx-list">
        @foreach ($ipbx as $item)
            <div class="card ipbx-list-item ipbx-card mb-md" data-name="{{ strtolower($item->client_name) }}" data-description="{{ strtolower($item->description ?? '') }}">
                <div class="ipbx-card__content">
                    <div class="ipbx-card__header">
                        <div class="ipbx-card__image ipbx-card__image--placeholder">
                            I
                        </div>
                        <div class="ipbx-card__title-wrap">
                            <h3 class="ipbx-card__title">{{ $item->client_name }}</h3>
                        </div>
                    </div>

                    <div class="ipbx-card__body">
                        <h3 class="ipbx-card__name">{{ $item->client_name }}</h3>
                        @if ($item->contact_name)
                            <p class="ipbx-card__contact">
                                Contact: {{ $item->contact_name }}
                            </p>
                        @endif
                        @if ($item->description)
                            <p class="ipbx-card__contact">
                                {{ Str::limit($item->description, 100) }}
                            </p>
                        @endif
                        <div class="ipbx-card__meta">
                            @if ($item->status === 'online')
                                <span class="status status-online">En ligne</span>
                            @else
                                <span class="status status-offline">Hors ligne</span>
                            @endif
                            <span class="ipbx-card__ping">
                                {{ $item->ip_address }}:{{ $item->port }}
                            </span>
                        </div>
                    </div>

                    <div class="ipbx-card__actions">
                        <a href="{{ route('client.ipbx.view', $item) }}" class="btn btn-primary">
                            Acceder
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Message si aucun resultat IPBX -->
    <div id="no-results-ipbx" class="card" style="display: none;">
        <div class="empty-state">
            <div class="empty-icon">?</div>
            <p class="empty-title">Aucun resultat</p>
            <p class="empty-description">Aucun IPBX ne correspond a votre recherche.</p>
        </div>
    </div>
@else
    <div class="card">
        <div class="empty-state">
            <div class="empty-icon">I</div>
            <p class="empty-title">Aucun IPBX</p>
            <p class="empty-description">
                Aucun IPBX n'est actuellement associe a votre compte.
            </p>
        </div>
    </div>
@endif

@if ($centrex->count() == 0 && $ipbx->count() == 0)
    <div class="card" style="margin-top: 1rem;">
        <div class="empty-state">
            <p class="empty-description">
                Veuillez contacter votre administrateur pour obtenir l'acces a vos equipements.
            </p>
        </div>
    </div>
@endif

<script>
    // Recherche Centrex
    var centrexSearch = document.getElementById('centrex-search');
    if (centrexSearch) {
        centrexSearch.addEventListener('input', function() {
            var query = this.value.toLowerCase().trim();
            var items = document.querySelectorAll('.centrex-list-item');
            var visibleCount = 0;

            items.forEach(function(item) {
                var name = item.dataset.name || '';
                var description = item.dataset.description || '';
                var matches = name.includes(query) || description.includes(query);

                item.style.display = matches ? '' : 'none';
                if (matches) visibleCount++;
            });

            document.getElementById('no-results-centrex').style.display = visibleCount === 0 ? '' : 'none';
        });
    }

    // Recherche IPBX
    var ipbxSearch = document.getElementById('ipbx-search');
    if (ipbxSearch) {
        ipbxSearch.addEventListener('input', function() {
            var query = this.value.toLowerCase().trim();
            var items = document.querySelectorAll('.ipbx-list-item');
            var visibleCount = 0;

            items.forEach(function(item) {
                var name = item.dataset.name || '';
                var description = item.dataset.description || '';
                var matches = name.includes(query) || description.includes(query);

                item.style.display = matches ? '' : 'none';
                if (matches) visibleCount++;
            });

            document.getElementById('no-results-ipbx').style.display = visibleCount === 0 ? '' : 'none';
        });
    }
</script>
@endsection
