@extends('layouts.app')

@section('content')
<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
    <h1>Gestion des Clients</h1>
    <div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline">← Retour</a>
        <a href="{{ route('admin.clients.create') }}" class="btn btn-primary">+ Nouveau Client</a>
    </div>
</div>

@if(session('success'))
    <div style="background-color: var(--color-success); color: white; padding: 1rem; border-radius: var(--border-radius); margin-bottom: 1.5rem;">
        {{ session('success') }}
    </div>
@endif

<div class="card">
    @if($clients->count() > 0)
        <table style="width: 100%;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border-color);">
                    <th style="padding: 1rem; text-align: left;">Nom</th>
                    <th style="padding: 1rem; text-align: left;">Entreprise</th>
                    <th style="padding: 1rem; text-align: left;">Email</th>
                    <th style="padding: 1rem; text-align: left;">Téléphone</th>
                    <th style="padding: 1rem; text-align: center;">Statut</th>
                    <th style="padding: 1rem; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($clients as $client)
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 1rem;">{{ $client->user->name }}</td>
                        <td style="padding: 1rem;">{{ $client->company_name }}</td>
                        <td style="padding: 1rem;">{{ $client->email }}</td>
                        <td style="padding: 1rem;">{{ $client->phone ?? '-' }}</td>
                        <td style="padding: 1rem; text-align: center;">
                            @if($client->is_active)
                                <span style="background-color: var(--color-success); color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.875rem;">Actif</span>
                            @else
                                <span style="background-color: var(--color-danger); color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.875rem;">Inactif</span>
                            @endif
                        </td>
                        <td style="padding: 1rem; text-align: center;">
                            <a href="{{ route('admin.clients.show', $client) }}" class="btn btn-sm btn-primary">Voir</a>
                            <a href="{{ route('admin.clients.edit', $client) }}" class="btn btn-sm btn-secondary">Modifier</a>
                            <form method="POST" action="{{ route('admin.clients.destroy', $client) }}" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce client ?')">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-secondary" style="text-align: center; padding: 2rem;">Aucun client pour le moment.</p>
    @endif
</div>
@endsection