@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1 class="page-title">{{ $ipbx->client_name }}</h1>
    <div class="page-actions">
        <a href="{{ route('admin.ipbx.index') }}" class="btn btn-ghost">
            ← Retour à la liste
        </a>
        <a href="{{ route('admin.ipbx.edit', $ipbx) }}" class="btn btn-secondary">
            Modifier
        </a>
        <a href="{{ $ipbx->url }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary">
            Ouvrir l'IPBX ↗
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
    <!-- Informations client -->
    <div class="card">
        <h3 class="card-title" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; margin-bottom: 1rem;">
            👤 Informations client
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
            <span class="detail-label">Téléphone</span>
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
            <span class="detail-label">Créé le</span>
            <span class="detail-value">{{ $ipbx->created_at->format('d/m/Y à H:i') }}</span>
        </div>
    </div>

    <!-- Connexion IPBX -->
    <div class="card">
        <h3 class="card-title" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; margin-bottom: 1rem;">
            🖥️ Connexion IPBX
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
            <span class="detail-label">URL d'accès</span>
            <span class="detail-value">
                <a href="{{ $ipbx->url }}" target="_blank" rel="noopener noreferrer" style="font-family: monospace;">
                    {{ $ipbx->url }} ↗
                </a>
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
                <button type="button" id="ping-btn" class="btn btn-sm btn-ghost" style="margin-left: 0.5rem;" title="Vérifier maintenant">
                    🔄
                </button>
            </span>
        </div>

        @if($ipbx->last_ping)
        <div class="detail-row">
            <span class="detail-label">Dernier ping</span>
            <span class="detail-value" id="last-ping">{{ $ipbx->last_ping->format('d/m/Y à H:i:s') }}</span>
        </div>
        @endif

        <div class="detail-row">
            <span class="detail-label">État</span>
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

@if($ipbx->description)
<div class="card mt-lg">
    <h3 class="card-title" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; margin-bottom: 1rem;">
        📝 Notes
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
</style>

<script>
    document.getElementById('ping-btn').addEventListener('click', function() {
        const btn = this;
        btn.disabled = true;
        btn.textContent = '⏳';

        fetch('{{ route("admin.ipbx.ping", $ipbx) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            const indicator = document.getElementById('status-indicator');
            if (data.status === 'online') {
                indicator.innerHTML = '<span class="status status-online">En ligne</span>' +
                    '<button type="button" id="ping-btn" class="btn btn-sm btn-ghost" style="margin-left: 0.5rem;" title="Vérifier maintenant">🔄</button>';
            } else {
                indicator.innerHTML = '<span class="status status-offline">Hors ligne</span>' +
                    '<button type="button" id="ping-btn" class="btn btn-sm btn-ghost" style="margin-left: 0.5rem;" title="Vérifier maintenant">🔄</button>';
            }

            const lastPing = document.getElementById('last-ping');
            if (lastPing) {
                lastPing.textContent = data.last_ping;
            }

            // Re-attacher l'événement
            document.getElementById('ping-btn').addEventListener('click', arguments.callee);
        })
        .catch(error => {
            console.error('Erreur:', error);
            btn.textContent = '❌';
            setTimeout(() => {
                btn.textContent = '🔄';
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
