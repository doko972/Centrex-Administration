@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1 class="page-title">
        Gestion des IPBX
        <small>{{ $ipbxs->count() }} IPBX enregistré(s)</small>
    </h1>
    <div class="page-actions">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost">
            ← Retour
        </a>
        <button type="button" id="ping-all-btn" class="btn btn-secondary" {{ $ipbxs->count() === 0 ? 'disabled' : '' }}>
            Ping All
        </button>
        <a href="{{ route('admin.ipbx.create') }}" class="btn btn-primary">
            + Nouvel IPBX
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

@if($ipbxs->count() > 0)
    <!-- Barre de recherche -->
    <div class="card mb-lg" style="padding: 1rem;">
        <input
            type="text"
            id="ipbx-search"
            class="form-control"
            placeholder="Rechercher un IPBX (client, IP, téléphone)..."
            style="width: 100%; padding: 0.75rem 1rem; font-size: 1rem;"
        >
    </div>

    <!-- Liste des IPBX -->
    <div id="ipbx-list">
        @foreach($ipbxs as $item)
            <div class="card mb-md ipbx-list-item"
                 data-id="{{ $item->id }}"
                 data-client="{{ strtolower($item->client_name) }}"
                 data-ip="{{ strtolower($item->ip_address) }}"
                 data-phone="{{ strtolower($item->phone ?? '') }}">
                <div style="display: flex; align-items: center; gap: 1.5rem;">
                    <div style="width: 80px; height: 80px; background: var(--bg-tertiary); border-radius: var(--border-radius); display: flex; align-items: center; justify-content: center; font-size: 2rem; flex-shrink: 0;">
                        🖥️
                    </div>

                    <div style="flex: 1; min-width: 0;">
                        <h3 style="margin: 0 0 0.25rem 0;">{{ $item->client_name }}</h3>
                        @if($item->contact_name)
                            <p style="margin: 0 0 0.25rem 0; color: var(--text-secondary); font-size: 0.875rem;">
                                Contact: {{ $item->contact_name }}
                            </p>
                        @endif
                        @if($item->phone)
                            <p style="margin: 0 0 0.5rem 0; color: var(--text-secondary); font-size: 0.875rem;">
                                Tel: {{ $item->phone }}
                            </p>
                        @endif
                        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                            <a href="{{ $item->url }}" target="_blank" rel="noopener noreferrer"
                               style="font-family: monospace; color: var(--color-primary); text-decoration: none;"
                               title="Ouvrir dans un nouvel onglet">
                                {{ $item->ip_address }}:{{ $item->port }} ↗
                            </a>

                            <span class="status-indicator status-indicator-{{ $item->id }}" data-status="{{ $item->status }}">
                                @if($item->status === 'online')
                                    <span class="status status-online">En ligne</span>
                                @else
                                    <span class="status status-offline">Hors ligne</span>
                                @endif
                            </span>

                            @if(!$item->is_active)
                                <span class="badge badge-danger">Inactif</span>
                            @endif

                            @if($item->last_ping)
                                <span class="last-ping-{{ $item->id }}" style="color: var(--text-tertiary); font-size: 0.75rem;">
                                    Dernier ping: {{ $item->last_ping->format('d/m/Y H:i') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; flex-shrink: 0; align-items: center;">
                        <button type="button" class="btn btn-sm btn-ghost ping-btn" data-id="{{ $item->id }}" title="Vérifier la connexion">
                            🔄
                        </button>
                        <a href="{{ route('admin.ipbx.show', $item) }}" class="btn btn-sm btn-soft-primary">
                            Voir
                        </a>
                        <a href="{{ route('admin.ipbx.edit', $item) }}" class="btn btn-sm btn-soft-secondary">
                            Modifier
                        </a>
                        <form method="POST" action="{{ route('admin.ipbx.destroy', $item) }}" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-soft-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet IPBX ?')">
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
            <p class="empty-description">Aucun IPBX ne correspond à votre recherche.</p>
        </div>
    </div>

    <script>
        // Recherche
        document.getElementById('ipbx-search').addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const items = document.querySelectorAll('.ipbx-list-item');
            let visibleCount = 0;

            items.forEach(function(item) {
                const client = item.dataset.client || '';
                const ip = item.dataset.ip || '';
                const phone = item.dataset.phone || '';
                const matches = client.includes(query) || ip.includes(query) || phone.includes(query);

                item.style.display = matches ? '' : 'none';
                if (matches) visibleCount++;
            });

            document.getElementById('no-results').style.display = visibleCount === 0 ? '' : 'none';
        });

        // Ping individuel
        document.querySelectorAll('.ping-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                pingIpbx(id, this);
            });
        });

        // Ping All
        document.getElementById('ping-all-btn').addEventListener('click', function() {
            const buttons = document.querySelectorAll('.ping-btn');
            buttons.forEach(function(btn) {
                const id = btn.dataset.id;
                pingIpbx(id, btn);
            });
        });

        function pingIpbx(id, btn) {
            btn.disabled = true;
            btn.textContent = '⏳';

            fetch('{{ url("admin/ipbx") }}/' + id + '/ping', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                const indicator = document.querySelector('.status-indicator-' + id);
                if (data.status === 'online') {
                    indicator.innerHTML = '<span class="status status-online">En ligne</span>';
                } else {
                    indicator.innerHTML = '<span class="status status-offline">Hors ligne</span>';
                }

                const lastPing = document.querySelector('.last-ping-' + id);
                if (lastPing) {
                    lastPing.textContent = 'Dernier ping: ' + data.last_ping;
                }

                btn.textContent = '🔄';
                btn.disabled = false;
            })
            .catch(error => {
                console.error('Erreur:', error);
                btn.textContent = '❌';
                setTimeout(() => {
                    btn.textContent = '🔄';
                    btn.disabled = false;
                }, 2000);
            });
        }

        // Ping automatique au chargement
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                document.querySelectorAll('.ping-btn').forEach(function(btn) {
                    const id = btn.dataset.id;
                    pingIpbx(id, btn);
                });
            }, 500);
        });
    </script>
@else
    <div class="card">
        <div class="empty-state">
            <div class="empty-icon">🖥️</div>
            <p class="empty-title">Aucun IPBX</p>
            <p class="empty-description">Vous n'avez pas encore d'IPBX enregistrés. Commencez par en créer un.</p>
            <a href="{{ route('admin.ipbx.create') }}" class="btn btn-primary">
                + Créer un IPBX
            </a>
        </div>
    </div>
@endif
@endsection
