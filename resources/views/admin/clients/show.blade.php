@extends('layouts.app')

@section('content')
<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
    <h1>Détails du Client</h1>
    <div>
        <a href="{{ route('admin.clients.index') }}" class="btn btn-outline">← Retour</a>
        <a href="{{ route('admin.clients.manage-centrex', $client) }}" class="btn btn-success">Gérer les centrex</a>
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
                            <span style="background-color: var(--color-success); color: white; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.75rem;">● En ligne</span>
                        @elseif($centrex->status === 'offline')
                            <span style="background-color: var(--color-danger); color: white; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.75rem;">● Hors ligne</span>
                        @else
                            <span style="background-color: var(--color-warning); color: white; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.75rem;">● Maintenance</span>
                        @endif
                    </p>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-secondary" style="text-align: center; padding: 2rem;">Aucun centrex associé pour le moment.</p>
    @endif
</div>
@endsection