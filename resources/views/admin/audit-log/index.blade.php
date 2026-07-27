@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
    ['label' => 'Journal d\'audit'],
]" />
<div class="page-header">
    <h1 class="page-title">
        Journal d'audit
        <small>{{ $logs->total() }} événement(s) enregistré(s)</small>
    </h1>
    <div class="page-actions">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost">
            ← Retour
        </a>
    </div>
</div>

<div class="card mb-lg" style="padding: 1rem;">
    <form method="GET" action="{{ route('admin.audit-log.index') }}" style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
        <input
            type="text"
            name="search"
            value="{{ $search }}"
            class="form-control"
            placeholder="Rechercher (description, administrateur)..."
            style="flex: 1; min-width: 200px; padding: 0.75rem 1rem; font-size: 1rem;"
        >
        <select name="action" class="form-input" style="width: auto; min-width: 220px;" onchange="this.form.submit()">
            <option value="">Toutes les actions</option>
            @foreach($actions as $a)
                <option value="{{ $a }}" {{ $action === $a ? 'selected' : '' }}>{{ $a }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary">Filtrer</button>
        @if($search !== '' || $action !== '')
            <a href="{{ route('admin.audit-log.index') }}" class="btn btn-ghost">Réinitialiser</a>
        @endif
    </form>
</div>

<div class="card">
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Administrateur</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    @php
                        $badgeColor = match (true) {
                            str_ends_with($log->action, 'deleted') => 'var(--color-danger)',
                            str_ends_with($log->action, 'password_changed') => 'var(--color-warning)',
                            str_ends_with($log->action, 'created') => 'var(--color-success)',
                            default => 'var(--color-primary)',
                        };
                    @endphp
                    <tr>
                        <td data-label="Date" style="white-space: nowrap;">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        <td data-label="Administrateur">{{ $log->user_name ?? 'Système' }}</td>
                        <td data-label="Action">
                            <span style="background-color: {{ $badgeColor }}; color: white; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.75rem; white-space: nowrap;">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td data-label="Description">{{ $log->description }}</td>
                        <td data-label="IP" style="font-family: monospace; font-size: 0.8125rem;">{{ $log->ip_address ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="padding: 2rem; text-align: center; color: var(--text-secondary);">
                            Aucun événement enregistré{{ $search !== '' || $action !== '' ? ' pour ces critères' : '' }}.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $logs->links('pagination.custom') }}
</div>
@endsection
