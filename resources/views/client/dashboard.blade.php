@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">
            Mes Centrex
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
            <h3 class="mb-xs">Bienvenue, {{ Auth::user()->name }} !</h3>
            <p class="text-secondary mb-0">Voici la liste de vos centrex FreePBX. Cliquez sur un centrex pour y accéder.</p>
        </div>
    </div>
</div>

@if ($centrex->count() > 0)
    <!-- Barre de recherche -->
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
            <div class="card centrex-list-item mb-md" data-name="{{ strtolower($item->name) }}" data-description="{{ strtolower($item->description ?? '') }}">
                <div style="display: flex; align-items: center; gap: 1.5rem;">
                    @if ($item->image)
                        <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}"
                            style="width: 80px; height: 80px; object-fit: cover; border-radius: var(--border-radius); flex-shrink: 0;">
                    @else
                        <div style="width: 80px; height: 80px; background: var(--bg-tertiary); border-radius: var(--border-radius); display: flex; align-items: center; justify-content: center; font-size: 2rem; flex-shrink: 0;">
                            📞
                        </div>
                    @endif

                    <div style="flex: 1; min-width: 0;">
                        <h3 style="margin: 0 0 0.25rem 0;">{{ $item->name }}</h3>
                        @if ($item->description)
                            <p style="margin: 0 0 0.5rem 0; color: var(--text-secondary); font-size: 0.875rem;">
                                {{ Str::limit($item->description, 100) }}
                            </p>
                        @endif
                        <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                            @if ($item->status === 'online')
                                <span class="status status-online">En ligne</span>
                            @elseif($item->status === 'offline')
                                <span class="status status-offline">Hors ligne</span>
                            @else
                                <span class="status status-maintenance">Maintenance</span>
                            @endif
                            <span style="color: var(--text-tertiary); font-size: 0.875rem;">
                                {{ $item->ip_address }}{{ $item->port != 80 ? ':' . $item->port : '' }}
                            </span>
                        </div>
                    </div>

                    <div style="flex-shrink: 0;">
                        <a href="{{ route('client.centrex.view', $item) }}" class="btn btn-primary">
                            Accéder
                        </a>
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
                const description = item.dataset.description || '';
                const matches = name.includes(query) || description.includes(query);

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
            <p class="empty-description">
                Aucun centrex n'est actuellement associé à votre compte.<br>
                Veuillez contacter votre administrateur.
            </p>
        </div>
    </div>
@endif
@endsection
