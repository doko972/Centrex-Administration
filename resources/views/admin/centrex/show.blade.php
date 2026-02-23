@extends('layouts.app')

@section('content')
    <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
        <h1>Détails du Centrex</h1>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('admin.centrex.index') }}" class="btn btn-outline">← Retour</a>
            <a href="{{ route('admin.centrex.manage-clients', $centrex) }}" class="btn btn-success">Gérer les clients</a>
            @if($centrex->is_active && $centrex->status === 'online')
                <a href="{{ route('admin.centrex.view', $centrex) }}" class="btn btn-info">Ouvrir FreePBX</a>
            @endif
            <a href="{{ route('admin.centrex.edit', $centrex) }}" class="btn btn-primary">Modifier</a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
        <!-- Informations générales -->
        <div class="card">
            @if ($centrex->image)
                <img src="{{ asset('storage/' . $centrex->image) }}" alt="{{ $centrex->name }}"
                    style="width: 100%; max-height: 200px; object-fit: cover; border-radius: var(--border-radius); margin-bottom: 1.5rem;">
            @endif

            <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--border-color);">
                Informations générales</h3>

            <div style="margin-bottom: 1rem;">
                <strong style="color: var(--text-secondary); font-size: 0.875rem;">Nom du centrex</strong>
                <p style="margin-top: 0.25rem;">{{ $centrex->name }}</p>
            </div>

            <div style="margin-bottom: 1rem;">
                <strong style="color: var(--text-secondary); font-size: 0.875rem;">Description</strong>
                <p style="margin-top: 0.25rem; white-space: pre-line;">{{ $centrex->description ?? '-' }}</p>
            </div>

            <div style="margin-bottom: 1rem;">
                <strong style="color: var(--text-secondary); font-size: 0.875rem;">Statut</strong>
                <p style="margin-top: 0.25rem;">
                    @if ($centrex->status === 'online')
                        <span
                            style="background-color: var(--color-success); color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.875rem;">●
                            En ligne</span>
                    @elseif($centrex->status === 'offline')
                        <span
                            style="background-color: var(--color-danger); color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.875rem;">●
                            Hors ligne</span>
                    @else
                        <span
                            style="background-color: var(--color-warning); color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.875rem;">●
                            Maintenance</span>
                    @endif

                    @if (!$centrex->is_active)
                        <span
                            style="background-color: var(--color-danger); color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.875rem; margin-left: 0.5rem;">Inactif</span>
                    @endif
                </p>
            </div>

            <div>
                <strong style="color: var(--text-secondary); font-size: 0.875rem;">Date de création</strong>
                <p style="margin-top: 0.25rem;">{{ $centrex->created_at->format('d/m/Y à H:i') }}</p>
            </div>

            @if ($centrex->last_check)
                <div style="margin-top: 1rem;">
                    <strong style="color: var(--text-secondary); font-size: 0.875rem;">Dernière vérification</strong>
                    <p style="margin-top: 0.25rem;">{{ $centrex->last_check->format('d/m/Y à H:i') }}</p>
                </div>
            @endif
        </div>

        <!-- Informations de connexion -->
        <div class="card">
            <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--border-color);">
                Connexion FreePBX</h3>

            <div style="margin-bottom: 1rem;">
                <strong style="color: var(--text-secondary); font-size: 0.875rem;">Adresse IP</strong>
                <p style="margin-top: 0.25rem; font-family: monospace;">{{ $centrex->ip_address }}</p>
            </div>

            <div style="margin-bottom: 1rem;">
                <strong style="color: var(--text-secondary); font-size: 0.875rem;">Port</strong>
                <p style="margin-top: 0.25rem; font-family: monospace;">{{ $centrex->port }}</p>
            </div>

            <div style="margin-bottom: 1rem;">
                <strong style="color: var(--text-secondary); font-size: 0.875rem;">Login</strong>
                <p style="margin-top: 0.25rem; font-family: monospace;">{{ $centrex->login }}</p>
            </div>

            <div style="margin-bottom: 1rem;">
                <strong style="color: var(--text-secondary); font-size: 0.875rem;">Mot de passe</strong>
                <p style="margin-top: 0.25rem; font-family: monospace;">••••••••</p>
                <small style="color: var(--text-tertiary);">Le mot de passe est chiffré</small>
            </div>

            <div
                style="margin-top: 1.5rem; padding: 1rem; background-color: var(--bg-tertiary); border-radius: var(--border-radius);">
                <strong style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem;">Accès via le proxy</strong>
                @if($centrex->is_active && $centrex->status === 'online')
                    <a href="{{ route('admin.centrex.view', $centrex) }}"
                        style="color: var(--color-primary); font-family: monospace; word-break: break-all;">
                        Ouvrir FreePBX
                    </a>
                @else
                    <span style="color: var(--text-secondary);">FreePBX indisponible (inactif ou hors ligne)</span>
                @endif
            </div>
            <div
                style="margin-top: 1rem; padding: 1rem; background-color: var(--bg-tertiary); border-radius: var(--border-radius);">
                <strong style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem;">IP directe (info)</strong>
                <span style="font-family: monospace; color: var(--text-secondary);">
                    http://{{ $centrex->ip_address }}{{ $centrex->port != 80 ? ':' . $centrex->port : '' }}
                </span>
                <small style="display: block; margin-top: 0.25rem; color: var(--text-tertiary);">
                    Accès direct non disponible (seule l'IP du serveur est autorisée)
                </small>
            </div>
        </div>
    </div>

    <!-- Liste des clients associés -->
    <div class="card" style="margin-top: 1.5rem;">
        <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--border-color);">Clients
            associés ({{ $centrex->clients->count() }})</h3>

        @if ($centrex->clients->count() > 0)
            <table style="width: 100%;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border-color);">
                        <th style="padding: 1rem; text-align: left;">Nom</th>
                        <th style="padding: 1rem; text-align: left;">Entreprise</th>
                        <th style="padding: 1rem; text-align: left;">Email</th>
                        <th style="padding: 1rem; text-align: center;">Statut</th>
                        <th style="padding: 1rem; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($centrex->clients as $client)
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 1rem;">{{ $client->user->name }}</td>
                            <td style="padding: 1rem;">{{ $client->company_name }}</td>
                            <td style="padding: 1rem;">{{ $client->email }}</td>
                            <td style="padding: 1rem; text-align: center;">
                                @if ($client->is_active)
                                    <span
                                        style="background-color: var(--color-success); color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.875rem;">Actif</span>
                                @else
                                    <span
                                        style="background-color: var(--color-danger); color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.875rem;">Inactif</span>
                                @endif
                            </td>
                            <td style="padding: 1rem; text-align: center;">
                                <a href="{{ route('admin.clients.show', $client) }}"
                                    class="btn btn-sm btn-primary">Voir</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-secondary" style="text-align: center; padding: 2rem;">Aucun client associé pour le moment.</p>
        @endif
    </div>
@endsection
