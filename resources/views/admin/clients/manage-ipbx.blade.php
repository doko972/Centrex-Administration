@extends('layouts.app')

@section('content')
<div style="margin-bottom: 2rem;">
    <h1>Gerer les IPBX de {{ $client->company_name }}</h1>
</div>

<div class="card" style="max-width: 900px;">
    <div style="background-color: var(--bg-tertiary); padding: 1rem; border-radius: var(--border-radius); margin-bottom: 1.5rem;">
        <strong>Client:</strong> {{ $client->user->name }} - {{ $client->company_name }}<br>
        <strong>Email:</strong> {{ $client->email }}
    </div>

    <form method="POST" action="{{ route('admin.clients.update-ipbx', $client) }}">
        @csrf

        <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--border-color);">
            Selectionnez les IPBX a associer
        </h3>

        @if($allIpbx->count() > 0)
            <div style="display: grid; gap: 1rem;">
                @foreach($allIpbx as $ipbx)
                    <label style="display: flex; align-items: start; padding: 1rem; border: 2px solid var(--border-color); border-radius: var(--border-radius); cursor: pointer; transition: all 0.2s ease;"
                           onmouseover="this.style.borderColor='var(--color-primary)'"
                           onmouseout="this.style.borderColor='var(--border-color)'">
                        <input
                            type="checkbox"
                            name="ipbx[]"
                            value="{{ $ipbx->id }}"
                            {{ in_array($ipbx->id, $clientIpbx) ? 'checked' : '' }}
                            style="margin-right: 1rem; margin-top: 0.25rem; width: auto; flex-shrink: 0;"
                        >
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                                <div>
                                    <strong style="font-size: 1.125rem;">{{ $ipbx->client_name }}</strong>
                                    <p style="font-size: 0.875rem; color: var(--text-secondary); margin-top: 0.25rem;">
                                        {{ $ipbx->ip_address }}:{{ $ipbx->port }}
                                    </p>
                                </div>
                            </div>

                            @if($ipbx->contact_name)
                                <p style="font-size: 0.875rem; color: var(--text-secondary);">
                                    Contact: {{ $ipbx->contact_name }}
                                </p>
                            @endif

                            @if($ipbx->description)
                                <p style="font-size: 0.875rem; color: var(--text-secondary); margin-top: 0.5rem;">
                                    {{ Str::limit($ipbx->description, 100) }}
                                </p>
                            @endif

                            <div style="margin-top: 0.5rem;">
                                @if($ipbx->status === 'online')
                                    <span style="background-color: var(--color-success); color: white; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.75rem;">En ligne</span>
                                @else
                                    <span style="background-color: var(--color-danger); color: white; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.75rem;">Hors ligne</span>
                                @endif

                                @if($ipbx->login)
                                    <span style="background-color: var(--color-primary); color: white; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.75rem; margin-left: 0.5rem;">Proxy active</span>
                                @else
                                    <span style="background-color: var(--color-warning); color: white; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.75rem; margin-left: 0.5rem;">Acces direct</span>
                                @endif
                            </div>
                        </div>
                    </label>
                @endforeach
            </div>

            <div style="margin-top: 1.5rem; padding: 1rem; background-color: var(--bg-tertiary); border-radius: var(--border-radius);">
                <strong>Note:</strong> Les IPBX coches seront visibles par ce client dans son dashboard.
                <br><small style="color: var(--text-secondary);">Les IPBX avec "Proxy active" utilisent l'authentification automatique via le proxy.</small>
            </div>

            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                <a href="{{ route('admin.clients.show', $client) }}" class="btn btn-outline">Annuler</a>
                <button type="submit" class="btn btn-primary">Enregistrer les associations</button>
            </div>
        @else
            <div style="text-align: center; padding: 2rem;">
                <p class="text-secondary">Aucun IPBX actif disponible.</p>
                <a href="{{ route('admin.ipbx.create') }}" class="btn btn-primary" style="margin-top: 1rem;">Creer un IPBX</a>
            </div>
        @endif
    </form>
</div>
@endsection
