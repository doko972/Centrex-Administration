@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1 class="page-title">
        Gestion des Centrex
        <small>{{ $centrex->count() }} centrex enregistré(s)</small>
    </h1>
    <div class="page-actions">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost">
            ← Retour
        </a>
        <a href="{{ route('admin.centrex.create') }}" class="btn btn-primary">
            ➕ Nouveau Centrex
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-lg">
        <span class="alert-icon">✓</span>
        <div class="alert-content">
            <p class="alert-message">{{ session('success') }}</p>
        </div>
    </div>
@endif

@if($centrex->count() > 0)
    <div class="grid grid-auto-lg">
        @foreach($centrex as $item)
            <div class="card card-interactive">
                @if($item->image)
                    <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="centrex-image">
                @else
                    <div class="centrex-placeholder">
                        📞
                    </div>
                @endif

                <h3 class="centrex-title">{{ $item->name }}</h3>

                <p class="text-sm text-secondary mb-md">
                    {{ $item->ip_address }}:{{ $item->port }}
                </p>

                <div class="d-flex gap-sm flex-wrap mb-md">
                    @if($item->status === 'online')
                        <span class="status status-online">En ligne</span>
                    @elseif($item->status === 'offline')
                        <span class="status status-offline">Hors ligne</span>
                    @else
                        <span class="status status-maintenance">Maintenance</span>
                    @endif

                    @if(!$item->is_active)
                        <span class="badge badge-danger">Inactif</span>
                    @endif
                </div>

                <div class="text-sm text-secondary mb-lg">
                    <strong>Clients associés:</strong>
                    <span class="badge badge-neutral">{{ $item->clients->count() }}</span>
                </div>

                <div class="d-flex gap-sm flex-wrap">
                    <a href="{{ route('admin.centrex.show', $item) }}" class="btn btn-sm btn-soft-primary">
                        Voir
                    </a>
                    <a href="{{ route('admin.centrex.edit', $item) }}" class="btn btn-sm btn-soft-secondary">
                        Modifier
                    </a>
                    <form method="POST" action="{{ route('admin.centrex.destroy', $item) }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-soft-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce centrex ?')">
                            Supprimer
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="card">
        <div class="empty-state">
            <div class="empty-icon">📞</div>
            <p class="empty-title">Aucun centrex</p>
            <p class="empty-description">Vous n'avez pas encore de centrex enregistrés. Commencez par en créer un.</p>
            <a href="{{ route('admin.centrex.create') }}" class="btn btn-primary">
                ➕ Créer un centrex
            </a>
        </div>
    </div>
@endif
@endsection
