@extends('layouts.app')

@section('content')
@php
    $backRoute = Auth::user()->isSuperClient()
        ? route('superclient.dashboard')
        : route('client.dashboard');
    $proxyRootRoute = Auth::user()->isSuperClient()
        ? route('superclient.centrex.proxy.root', $centrex)
        : route('client.centrex.proxy.root', $centrex);
@endphp
<div class="page-header" style="margin-bottom: 1rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <a href="{{ $backRoute }}" class="btn btn-outline btn-sm">← Retour</a>
            <div>
                <h1 class="page-title" style="margin: 0;">{{ $centrex->name }}</h1>
                <p style="font-size: 0.875rem; color: var(--text-secondary); margin: 0;">
                    {{ $centrex->ip_address }}
                    @if($centrex->status === 'online')
                        <span class="status status-online" style="margin-left: 0.5rem;">En ligne</span>
                    @elseif($centrex->status === 'offline')
                        <span class="status status-offline" style="margin-left: 0.5rem;">Hors ligne</span>
                    @else
                        <span class="status status-maintenance" style="margin-left: 0.5rem;">Maintenance</span>
                    @endif
                </p>
            </div>
        </div>
        <a href="{{ $proxyRootRoute }}" target="_blank" class="btn btn-outline btn-sm">
            Nouvel onglet
        </a>
    </div>
</div>

<div class="card" style="padding: 0; overflow: hidden; height: calc(100vh - 220px); min-height: 500px;">
    <iframe
        id="centrex-iframe"
        src="{{ $proxyRootRoute }}"
        style="width: 100%; height: 100%; border: none;"
        title="{{ $centrex->name }}"
    ></iframe>
</div>
@endsection
