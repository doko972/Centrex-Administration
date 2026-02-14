@extends('layouts.app')

@section('content')
<div class="page-header" style="margin-bottom: 1rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <a href="{{ route('client.dashboard') }}" class="btn btn-outline btn-sm">Retour</a>
            <div>
                <h1 class="page-title" style="margin: 0;">{{ $ipbx->client_name }}</h1>
                <p style="font-size: 0.875rem; color: var(--text-secondary); margin: 0;">
                    {{ $ipbx->ip_address }}:{{ $ipbx->port }}
                    @if($ipbx->status === 'online')
                        <span class="status status-online" style="margin-left: 0.5rem;">En ligne</span>
                    @else
                        <span class="status status-offline" style="margin-left: 0.5rem;">Hors ligne</span>
                    @endif
                </p>
            </div>
        </div>
        <a href="{{ route('client.ipbx.proxy.root', $ipbx) }}" target="_blank" class="btn btn-outline btn-sm">
            Ouvrir dans un nouvel onglet
        </a>
    </div>
</div>

<div class="card" style="padding: 0; overflow: hidden; height: calc(100vh - 220px); min-height: 500px;">
    <iframe
        id="ipbx-iframe"
        src="{{ route('client.ipbx.proxy.root', $ipbx) }}"
        style="width: 100%; height: 100%; border: none;"
        title="{{ $ipbx->client_name }}"
    ></iframe>
</div>
@endsection
