@extends('layouts.app')

@section('content')
<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
    <h1>Détails du Client</h1>
    <div>
        <a href="{{ route('admin.clients.index') }}" class="btn btn-outline">Retour</a>
        <a href="{{ route('admin.clients.manage-centrex', $client) }}" class="btn btn-success">Gérer les centrex</a>
        <a href="{{ route('admin.clients.manage-ipbx', $client) }}" class="btn btn-info">Gérer les IPBX</a>
        <a href="{{ route('admin.clients.edit', $client) }}" class="btn btn-primary">Modifier</a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
    <!-- Informations générales -->
    <div class="card">
        <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--border-color);">Informations générales</h3>

        <div style="margin-bottom: 1rem;">
            <strong style="color: var(--text-secondary); font-size: 0.875rem;">Nom complet</strong>
            <p style="margin-top: 0.25rem;">{{ $client->user->name }}</p>
        </div>

        <div style="margin-bottom: 1rem;">
            <strong style="color: var(--text-secondary); font-size: 0.875rem;">Email</strong>
            <p style="margin-top: 0.25rem;">{{ $client->email }}</p>
        </div>

        <div style="margin-bottom: 1rem;">
            <strong style="color: var(--text-secondary); font-size: 0.875rem;">Téléphone</strong>
            <p style="margin-top: 0.25rem;">{{ $client->phone ?? '-' }}</p>
        </div>

        <div style="margin-bottom: 1rem;">
            <strong style="color: var(--text-secondary); font-size: 0.875rem;">Statut</strong>
            <p style="margin-top: 0.25rem;">
                @if($client->is_active)
                    <span style="background-color: var(--color-success); color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.875rem;">Actif</span>
                @else
                    <span style="background-color: var(--color-danger); color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.875rem;">Inactif</span>
                @endif
            </p>
        </div>

        <div>
            <strong style="color: var(--text-secondary); font-size: 0.875rem;">Date de création</strong>
            <p style="margin-top: 0.25rem;">{{ $client->created_at->format('d/m/Y à H:i') }}</p>
        </div>
    </div>

    <!-- Informations entreprise -->
    <div class="card">
        <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--border-color);">Informations entreprise</h3>

        <div style="margin-bottom: 1rem;">
            <strong style="color: var(--text-secondary); font-size: 0.875rem;">Nom de l'entreprise</strong>
            <p style="margin-top: 0.25rem;">{{ $client->company_name }}</p>
        </div>

        <div style="margin-bottom: 1rem;">
            <strong style="color: var(--text-secondary); font-size: 0.875rem;">Nom du contact</strong>
            <p style="margin-top: 0.25rem;">{{ $client->contact_name ?? '-' }}</p>
        </div>

        <div>
            <strong style="color: var(--text-secondary); font-size: 0.875rem;">Adresse</strong>
            <p style="margin-top: 0.25rem; white-space: pre-line;">{{ $client->address ?? '-' }}</p>
        </div>
    </div>
</div>

<!-- Types de connexion -->
<div class="card" style="margin-top: 1.5rem;">
    <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--border-color);">Types de connexion ({{ $client->connectionTypes->count() }})</h3>

    @if($client->connectionTypes->count() > 0)
        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
            @foreach($client->connectionTypes as $type)
                <span style="background-color: var(--color-primary); color: white; padding: 0.375rem 0.75rem; border-radius: 16px; font-size: 0.875rem;">
                    {{ $type->name }}
                </span>
            @endforeach
        </div>
    @else
        <p class="text-secondary" style="text-align: center; padding: 1rem;">Aucun type de connexion défini.</p>
    @endif
</div>

<!-- Fournisseurs -->
<div class="card" style="margin-top: 1.5rem;">
    <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--border-color);">Fournisseurs ({{ $client->providers->count() }})</h3>

    @if($client->providers->count() > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem;">
            @foreach($client->providers as $provider)
                <div style="border: 1px solid var(--border-color); border-radius: var(--border-radius); padding: 1rem;">
                    <h4 style="margin-bottom: 0.5rem;">{{ $provider->name }}</h4>
                    @if($provider->url)
                        <a href="{{ $provider->url }}" target="_blank" style="font-size: 0.875rem; color: var(--color-primary);">
                            Accéder au site
                        </a>
                    @else
                        <span style="font-size: 0.875rem; color: var(--text-tertiary);">Pas de lien</span>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <p class="text-secondary" style="text-align: center; padding: 1rem;">Aucun fournisseur associé.</p>
    @endif
</div>

<!-- Matériels en place -->
<div class="card" style="margin-top: 1.5rem;">
    <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--border-color);">Matériels en place ({{ $client->equipment->count() }})</h3>

    @if($client->equipment->count() > 0)
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: var(--bg-secondary);">
                    <th style="text-align: left; padding: 0.75rem; border-bottom: 1px solid var(--border-color);">Équipement</th>
                    <th style="text-align: left; padding: 0.75rem; border-bottom: 1px solid var(--border-color);">Catégorie</th>
                    <th style="text-align: center; padding: 0.75rem; border-bottom: 1px solid var(--border-color);">Quantité</th>
                    <th style="text-align: left; padding: 0.75rem; border-bottom: 1px solid var(--border-color);">Modèle / Détails</th>
                </tr>
            </thead>
            <tbody>
                @foreach($client->equipment as $eq)
                    <tr>
                        <td style="padding: 0.75rem; border-bottom: 1px solid var(--border-color);">
                            {{ $eq->name }}
                            @if(!$eq->is_predefined)
                                <span style="background-color: var(--color-warning); color: white; padding: 0.125rem 0.5rem; border-radius: 8px; font-size: 0.75rem; margin-left: 0.5rem;">Personnalisé</span>
                            @endif
                        </td>
                        <td style="padding: 0.75rem; border-bottom: 1px solid var(--border-color); color: var(--text-secondary);">
                            {{ $eq->category ?? '-' }}
                        </td>
                        <td style="padding: 0.75rem; border-bottom: 1px solid var(--border-color); text-align: center; font-weight: 600;">
                            {{ $eq->pivot->quantity }}
                        </td>
                        <td style="padding: 0.75rem; border-bottom: 1px solid var(--border-color); color: var(--text-secondary); font-size: 0.875rem;">
                            {{ $eq->pivot->notes ?? '-' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-secondary" style="text-align: center; padding: 1rem;">Aucun matériel en place.</p>
    @endif
</div>

<!-- Backup 4G/5G -->
@if($client->has_4g5g_backup)
<div class="card" style="margin-top: 1.5rem;">
    <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--border-color);">
        Backup 4G/5G
        <span style="background-color: var(--color-success); color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem; margin-left: 0.5rem;">Actif</span>
    </h3>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem;">
        <div>
            <strong style="color: var(--text-secondary); font-size: 0.875rem;">Opérateur</strong>
            <p style="margin-top: 0.25rem;">{{ $client->backup_operator ?? '-' }}</p>
        </div>

        <div>
            <strong style="color: var(--text-secondary); font-size: 0.875rem;">Numéro de téléphone</strong>
            <p style="margin-top: 0.25rem;">{{ $client->backup_phone_number ?? '-' }}</p>
        </div>

        <div>
            <strong style="color: var(--text-secondary); font-size: 0.875rem;">Numéro SIM</strong>
            <p style="margin-top: 0.25rem;">{{ $client->backup_sim_number ?? '-' }}</p>
        </div>
    </div>

    @if($client->backup_notes)
        <div style="margin-top: 1rem; padding: 1rem; background: var(--bg-secondary); border-radius: var(--border-radius);">
            <strong style="color: var(--text-secondary); font-size: 0.875rem;">Notes</strong>
            <p style="margin-top: 0.25rem; white-space: pre-line;">{{ $client->backup_notes }}</p>
        </div>
    @endif
</div>
@endif

<!-- Liste des centrex associés -->
<div class="card" style="margin-top: 1.5rem;">
    <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--border-color);">Centrex associés ({{ $client->centrex->count() }})</h3>

    @if($client->centrex->count() > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1rem;">
            @foreach($client->centrex as $centrex)
                <div style="border: 1px solid var(--border-color); border-radius: var(--border-radius); padding: 1rem;">
                    <h4 style="margin-bottom: 0.5rem;">{{ $centrex->name }}</h4>
                    <p style="font-size: 0.875rem; color: var(--text-secondary);">{{ $centrex->ip_address }}</p>
                    <p style="margin-top: 0.5rem;">
                        @if($centrex->status === 'online')
                            <span style="background-color: var(--color-success); color: white; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.75rem;">En ligne</span>
                        @elseif($centrex->status === 'offline')
                            <span style="background-color: var(--color-danger); color: white; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.75rem;">Hors ligne</span>
                        @else
                            <span style="background-color: var(--color-warning); color: white; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.75rem;">Maintenance</span>
                        @endif
                    </p>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-secondary" style="text-align: center; padding: 2rem;">Aucun centrex associé pour le moment.</p>
    @endif
</div>

<!-- Liste des IPBX associés -->
<div class="card" style="margin-top: 1.5rem;">
    <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--border-color);">IPBX associés ({{ $client->ipbx->count() }})</h3>

    @if($client->ipbx->count() > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1rem;">
            @foreach($client->ipbx as $ipbx)
                <div style="border: 1px solid var(--border-color); border-radius: var(--border-radius); padding: 1rem;">
                    <h4 style="margin-bottom: 0.5rem;">{{ $ipbx->client_name }}</h4>
                    <p style="font-size: 0.875rem; color: var(--text-secondary);">{{ $ipbx->ip_address }}:{{ $ipbx->port }}</p>
                    @if($ipbx->contact_name)
                        <p style="font-size: 0.75rem; color: var(--text-tertiary);">{{ $ipbx->contact_name }}</p>
                    @endif
                    <p style="margin-top: 0.5rem;">
                        @if($ipbx->status === 'online')
                            <span style="background-color: var(--color-success); color: white; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.75rem;">En ligne</span>
                        @else
                            <span style="background-color: var(--color-danger); color: white; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.75rem;">Hors ligne</span>
                        @endif
                        @if($ipbx->login)
                            <span style="background-color: var(--color-primary); color: white; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.75rem; margin-left: 0.25rem;">Proxy</span>
                        @endif
                    </p>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-secondary" style="text-align: center; padding: 2rem;">Aucun IPBX associé pour le moment.</p>
    @endif
</div>
@endsection
