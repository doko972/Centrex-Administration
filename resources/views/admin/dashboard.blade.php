@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1 class="page-title">
        Dashboard Administrateur
        <small>Vue d'ensemble de votre plateforme Centrex</small>
    </h1>
</div>

<!-- Stats Cards -->
<div class="grid grid-4 mb-xl">
    <!-- Total Clients -->
    <div class="stats-card">
        <div class="stats-icon icon-primary">
            👥
        </div>
        <div class="stats-value">{{ $stats['total_clients'] }}</div>
        <div class="stats-label">Clients totaux</div>
        <span class="stats-trend trend-up">{{ $stats['clients_actifs'] }} actifs</span>
    </div>

    <!-- Total Centrex -->
    <div class="stats-card stats-secondary">
        <div class="stats-icon icon-secondary">
            📞
        </div>
        <div class="stats-value">{{ $stats['total_centrex'] }}</div>
        <div class="stats-label">Centrex totaux</div>
        <span class="stats-trend trend-up">{{ $stats['centrex_actifs'] }} actifs</span>
    </div>

    <!-- Centrex En ligne -->
    <div class="stats-card stats-success">
        <div class="stats-icon icon-success">
            ✓
        </div>
        <div class="stats-value">{{ $stats['centrex_online'] }}</div>
        <div class="stats-label">En ligne</div>
    </div>

    <!-- Disponibilité -->
    <div class="stats-card stats-info">
        <div class="stats-icon icon-info">
            📊
        </div>
        <div class="stats-value">{{ $stats['uptime_percentage'] }}%</div>
        <div class="stats-label">Disponibilité</div>
    </div>
</div>

<div class="grid grid-2 mb-xl">
    <!-- Graphique statut des centrex -->
    <div class="card">
        <div class="card-header">
            <h3 class="section-title">Statut des Centrex</h3>
        </div>
        <canvas id="statusChart" style="max-height: 300px;"></canvas>
    </div>

    <!-- Dernières vérifications -->
    <div class="card">
        <div class="card-header">
            <h3 class="section-title">Dernières vérifications</h3>
        </div>
        @if($recentChecks->count() > 0)
            <div class="list-stack">
                @foreach($recentChecks as $centrex)
                    <div class="list-item">
                        <div class="list-item-content">
                            <div class="list-item-title">{{ $centrex->name }}</div>
                            <div class="list-item-subtitle">{{ $centrex->last_check->diffForHumans() }}</div>
                        </div>
                        @if($centrex->status === 'online')
                            <span class="status status-online">En ligne</span>
                        @elseif($centrex->status === 'offline')
                            <span class="status status-offline">Hors ligne</span>
                        @else
                            <span class="status status-maintenance">Maintenance</span>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <div class="empty-icon">📋</div>
                <p class="empty-title">Aucune vérification</p>
                <p class="empty-description">Aucune vérification n'a été effectuée pour le moment.</p>
            </div>
        @endif
    </div>
</div>

<!-- Actions rapides -->
<div class="card">
    <div class="card-header">
        <h3 class="section-title">Actions rapides</h3>
    </div>
    <div class="d-flex gap-md flex-wrap">
        <a href="{{ route('admin.clients.index') }}" class="btn btn-primary">
            👥 Gérer les clients
        </a>
        <a href="{{ route('admin.centrex.index') }}" class="btn btn-success">
            📞 Gérer les centrex
        </a>
        <a href="{{ route('admin.clients.create') }}" class="btn btn-outline">
            ➕ Nouveau client
        </a>
        <a href="{{ route('admin.centrex.create') }}" class="btn btn-outline">
            ➕ Nouveau centrex
        </a>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const styles = getComputedStyle(document.documentElement);
    const ctx = document.getElementById('statusChart').getContext('2d');

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($chartData['labels']) !!},
            datasets: [{
                data: {!! json_encode($chartData['data']) !!},
                backgroundColor: {!! json_encode($chartData['colors']) !!},
                borderWidth: 0,
                borderRadius: 4,
                spacing: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: styles.getPropertyValue('--text-primary').trim(),
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        font: {
                            size: 13,
                            weight: '500'
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush
@endsection
