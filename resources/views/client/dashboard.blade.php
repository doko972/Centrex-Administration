@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">
            Mes Centrex
            <small>{{ $client->company_name }}</small>
        </h1>
    </div>
</div>

<div class="card mb-xl">
    <div class="d-flex items-center gap-lg">
        <div class="avatar avatar-lg">
            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
        </div>
        <div>
            <h3 class="mb-xs">Bienvenue, {{ Auth::user()->name }} !</h3>
            <p class="text-secondary mb-0">Voici la liste de vos centrex FreePBX. Cliquez sur un centrex pour y accéder.</p>
        </div>
    </div>
</div>

@if ($centrex->count() > 0)
    <div class="grid grid-auto-lg">
        @foreach ($centrex as $item)
            <div class="centrex-card">
                @if ($item->image)
                    <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="centrex-image">
                @else
                    <div class="centrex-placeholder">
                        📞
                    </div>
                @endif

                <h3 class="centrex-title">{{ $item->name }}</h3>

                @if ($item->description)
                    <p class="centrex-description">
                        {{ Str::limit($item->description, 80) }}
                    </p>
                @endif

                <div class="mb-md">
                    @if ($item->status === 'online')
                        <span class="status status-online">En ligne</span>
                    @elseif($item->status === 'offline')
                        <span class="status status-offline">Hors ligne</span>
                    @else
                        <span class="status status-maintenance">Maintenance</span>
                    @endif
                </div>

                <div class="centrex-meta">
                    <strong>IP:</strong> {{ $item->ip_address }}{{ $item->port != 80 ? ':' . $item->port : '' }}
                </div>

                <div class="centrex-actions">
                    <a href="{{ route('client.centrex.view', $item) }}" class="btn btn-primary btn-block">
                        Accéder au Centrex
                    </a>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="card">
        <div class="empty-state">
            <div class="empty-icon">📞</div>
            <p class="empty-title">Aucun centrex</p>
            <p class="empty-description">
                Aucun centrex n'est actuellement associé à votre compte.<br>
                Veuillez contacter votre administrateur.
            </p>
        </div>
    </div>
@endif
@endsection
