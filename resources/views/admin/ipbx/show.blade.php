@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1 class="page-title">{{ $ipbx->client_name }}</h1>
    <div class="page-actions">
        <a href="{{ route('admin.ipbx.index') }}" class="btn btn-ghost">
            Retour a la liste
        </a>
        <a href="{{ route('admin.ipbx.edit', $ipbx) }}" class="btn btn-secondary">
            Modifier
        </a>
        @if($ipbx->login)
            <a href="{{ route('admin.ipbx.view', $ipbx) }}" class="btn btn-info">
                Acceder via Proxy
            </a>
        @endif
        <a href="{{ $ipbx->url }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary">
            Ouvrir l'IPBX
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
    <!-- Informations client -->
    <div class="card">
        <h3 class="card-title" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; margin-bottom: 1rem;">
            Informations client
        </h3>

        <div class="detail-row">
            <span class="detail-label">Nom du client</span>
            <span class="detail-value">{{ $ipbx->client_name }}</span>
        </div>

        @if($ipbx->contact_name)
        <div class="detail-row">
            <span class="detail-label">Contact</span>
            <span class="detail-value">{{ $ipbx->contact_name }}</span>
        </div>
        @endif

        @if($ipbx->email)
        <div class="detail-row">
            <span class="detail-label">Email</span>
            <span class="detail-value">
                <a href="mailto:{{ $ipbx->email }}">{{ $ipbx->email }}</a>
            </span>
        </div>
        @endif

        @if($ipbx->phone)
        <div class="detail-row">
            <span class="detail-label">Telephone</span>
            <span class="detail-value">
                <a href="tel:{{ $ipbx->phone }}">{{ $ipbx->phone }}</a>
            </span>
        </div>
        @endif

        @if($ipbx->address)
        <div class="detail-row">
            <span class="detail-label">Adresse</span>
            <span class="detail-value">{{ $ipbx->address }}</span>
        </div>
        @endif

        <div class="detail-row">
            <span class="detail-label">Cree le</span>
            <span class="detail-value">{{ $ipbx->created_at->format('d/m/Y a H:i') }}</span>
        </div>
    </div>

    <!-- Connexion IPBX -->
    <div class="card">
        <h3 class="card-title" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; margin-bottom: 1rem;">
            Connexion IPBX
        </h3>

        <div class="detail-row">
            <span class="detail-label">Adresse IP</span>
            <span class="detail-value" style="font-family: monospace;">{{ $ipbx->ip_address }}</span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Port</span>
            <span class="detail-value" style="font-family: monospace;">{{ $ipbx->port }}</span>
        </div>

        <div class="detail-row">
            <span class="detail-label">URL d'acces</span>
            <span class="detail-value">
                <a href="{{ $ipbx->url }}" target="_blank" rel="noopener noreferrer" style="font-family: monospace;">
                    {{ $ipbx->url }}
                </a>
            </span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Mode d'acces</span>
            <span class="detail-value">
                @if($ipbx->login)
                    <span class="badge badge-success">Proxy active</span>
                    <span style="margin-left: 0.5rem; color: var(--text-secondary); font-size: 0.875rem;">
                        (Login: {{ $ipbx->login }})
                    </span>
                @else
                    <span class="badge badge-secondary">Acces direct</span>
                @endif
            </span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Statut</span>
            <span class="detail-value status-indicator" id="status-indicator">
                @if($ipbx->status === 'online')
                    <span class="status status-online">En ligne</span>
                @else
                    <span class="status status-offline">Hors ligne</span>
                @endif
                <button type="button" id="ping-btn" class="btn btn-sm btn-ghost" style="margin-left: 0.5rem;" title="Verifier maintenant">
                    Refresh
                </button>
            </span>
        </div>

        @if($ipbx->last_ping)
        <div class="detail-row">
            <span class="detail-label">Dernier ping</span>
            <span class="detail-value" id="last-ping">{{ $ipbx->last_ping->format('d/m/Y a H:i:s') }}</span>
        </div>
        @endif

        <div class="detail-row">
            <span class="detail-label">Etat</span>
            <span class="detail-value">
                @if($ipbx->is_active)
                    <span class="badge badge-success">Actif</span>
                @else
                    <span class="badge badge-danger">Inactif</span>
                @endif
            </span>
        </div>
    </div>
</div>

<!-- Clients associes -->
<div class="card mt-lg">
    <h3 class="card-title" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; margin-bottom: 1rem;">
        Clients associes ({{ $ipbx->clients->count() }})
    </h3>

    @if($ipbx->clients->count() > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem;">
            @foreach($ipbx->clients as $client)
                <a href="{{ route('admin.clients.show', $client) }}" style="text-decoration: none; color: inherit;">
                    <div style="border: 1px solid var(--border-color); border-radius: var(--border-radius); padding: 1rem; transition: border-color 0.2s;" onmouseover="this.style.borderColor='var(--color-primary)'" onmouseout="this.style.borderColor='var(--border-color)'">
                        <strong>{{ $client->company_name }}</strong>
                        <p style="font-size: 0.875rem; color: var(--text-secondary); margin-top: 0.25rem;">{{ $client->user->name }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <p style="text-align: center; color: var(--text-secondary); padding: 1rem;">
            Aucun client associe a cet IPBX.
        </p>
    @endif
</div>

@if($ipbx->description)
<div class="card mt-lg">
    <h3 class="card-title" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; margin-bottom: 1rem;">
        Notes
    </h3>
    <p style="white-space: pre-wrap; margin: 0;">{{ $ipbx->description }}</p>
</div>
@endif

<style>
    .detail-row {
        display: flex;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--border-color);
    }
    .detail-row:last-child {
        border-bottom: none;
    }
    .detail-label {
        width: 140px;
        color: var(--text-tertiary);
        font-size: 0.875rem;
        flex-shrink: 0;
    }
    .detail-value {
        flex: 1;
        color: var(--text-primary);
    }
    .badge-success {
        background: var(--color-success);
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: var(--border-radius);
        font-size: 0.75rem;
    }
    .badge-danger {
        background: var(--color-danger);
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: var(--border-radius);
        font-size: 0.75rem;
    }
    .badge-secondary {
        background: var(--text-tertiary);
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: var(--border-radius);
        font-size: 0.75rem;
    }
</style>

<script>
    document.getElementById('ping-btn').addEventListener('click', function() {
        var btn = this;
        btn.disabled = true;
        btn.textContent = '...';

        fetch('{{ route("admin.ipbx.ping", $ipbx) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            var indicator = document.getElementById('status-indicator');
            if (data.status === 'online') {
                indicator.innerHTML = '<span class="status status-online">En ligne</span>' +
                    '<button type="button" id="ping-btn" class="btn btn-sm btn-ghost" style="margin-left: 0.5rem;" title="Verifier maintenant">Refresh</button>';
            } else {
                indicator.innerHTML = '<span class="status status-offline">Hors ligne</span>' +
                    '<button type="button" id="ping-btn" class="btn btn-sm btn-ghost" style="margin-left: 0.5rem;" title="Verifier maintenant">Refresh</button>';
            }

            var lastPing = document.getElementById('last-ping');
            if (lastPing) {
                lastPing.textContent = data.last_ping;
            }

            // Re-attacher l'evenement
            document.getElementById('ping-btn').addEventListener('click', arguments.callee);
        })
        .catch(function(error) {
            console.error('Erreur:', error);
            btn.textContent = 'Erreur';
            setTimeout(function() {
                btn.textContent = 'Refresh';
                btn.disabled = false;
            }, 2000);
        });
    });

    // Ping automatique au chargement
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            document.getElementById('ping-btn').click();
        }, 500);
    });
</script>
@endsection
