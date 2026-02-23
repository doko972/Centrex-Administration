
@extends('layouts.app')

@section('content')
<div style="margin-bottom: 2rem;">
    <h1>Gérer les clients du Centrex "{{ $centrex->name }}"</h1>
</div>

<div class="card" style="max-width: 900px;">
    <div style="background-color: var(--bg-tertiary); padding: 1rem; border-radius: var(--border-radius); margin-bottom: 1.5rem;">
        <strong>Centrex :</strong> {{ $centrex->name }}<br>
        <strong>IP :</strong> {{ $centrex->ip_address }}:{{ $centrex->port }}
    </div>

    @if(session('success'))
        <div style="background-color: var(--color-success); color: white; padding: 1rem; border-radius: var(--border-radius); margin-bottom: 1.5rem;">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.centrex.update-clients', $centrex) }}">
        @csrf

        <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--border-color);">
            Sélectionnez les clients ayant accès à ce centrex
        </h3>

        @if($allClients->count() > 0)
            <div style="display: grid; gap: 1rem;">
                @foreach($allClients as $client)
                    <label style="display: flex; align-items: center; padding: 1rem; border: 2px solid var(--border-color); border-radius: var(--border-radius); cursor: pointer; transition: all 0.2s ease;"
                           onmouseover="this.style.borderColor='var(--color-primary)'"
                           onmouseout="this.style.borderColor='{{ in_array($client->id, $centrexClients) ? 'var(--color-primary)' : 'var(--border-color)' }}'">
                        <input
                            type="checkbox"
                            name="clients[]"
                            value="{{ $client->id }}"
                            {{ in_array($client->id, $centrexClients) ? 'checked' : '' }}
                            style="margin-right: 1rem; width: auto; flex-shrink: 0;"
                        >
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <div>
                                    <strong style="font-size: 1rem;">{{ $client->company_name }}</strong>
                                    <span style="font-size: 0.875rem; color: var(--text-secondary); margin-left: 0.75rem;">
                                        {{ $client->user->name }}
                                    </span>
                                </div>
                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    <span style="font-size: 0.8rem; color: var(--text-secondary);">
                                        {{ $client->email }}
                                    </span>
                                    @if($client->is_active)
                                        <span style="background-color: var(--color-success); color: white; padding: 0.2rem 0.5rem; border-radius: 12px; font-size: 0.75rem;">Actif</span>
                                    @else
                                        <span style="background-color: var(--color-danger); color: white; padding: 0.2rem 0.5rem; border-radius: 12px; font-size: 0.75rem;">Inactif</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </label>
                @endforeach
            </div>

            <div style="margin-top: 1.5rem; padding: 1rem; background-color: var(--bg-tertiary); border-radius: var(--border-radius);">
                <strong>Note :</strong> Les clients cochés pourront accéder à ce centrex depuis leur dashboard.
            </div>

            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                <a href="{{ route('admin.centrex.show', $centrex) }}" class="btn btn-outline">Annuler</a>
                <button type="submit" class="btn btn-primary">Enregistrer les associations</button>
            </div>
        @else
            <div style="text-align: center; padding: 2rem;">
                <p class="text-secondary">Aucun client actif disponible.</p>
                <a href="{{ route('admin.clients.create') }}" class="btn btn-primary" style="margin-top: 1rem;">Créer un client</a>
            </div>
        @endif
    </form>
</div>
@endsection
