@extends('layouts.app')

@section('content')
<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
    <h1>Dashboard Administrateur</h1>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-danger">Déconnexion</button>
    </form>
</div>

<!-- Compteurs de statistiques -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <!-- Total Clients -->
    <div class="card" style="text-align: center;">
        <div style="font-size: 2.5rem; color: var(--color-primary); margin-bottom: 0.5rem;">👥</div>
        <h3 style="font-size: 2rem; margin-bottom: 0.25rem;">{{ $stats['total_clients'] }}</h3>
        <p style="color: var(--text-secondary); font-size: 0.875rem;">Clients totaux</p>
        <small style="color: var(--color-success);">{{ $stats['clients_actifs'] }} actifs</small>
    </div>

    <!-- Total Centrex -->
    <div class="card" style="text-align: center;">
        <div style="font-size: 2.5rem; color: var(--color-secondary); margin-bottom: 0.5rem;">📞</div>
        <h3 style="font-size: 2rem; margin-bottom: 0.25rem;">{{ $stats['total_centrex'] }}</h3>
        <p style="color: var(--text-secondary); font-size: 0.875rem;">Centrex totaux</p>
        <small style="color: var(--color-success);">{{ $stats['centrex_actifs'] }} actifs</small>
    </div>

    <!-- Centrex En ligne -->
    <div class="card" style="text-align: center;">
        <div style="font-size: 2.5rem; color: var(--color-success); margin-bottom: 0.5rem;">✓</div>
        <h3 style="font-size: 2rem; margin-bottom: 0.25rem;">{{ $stats['centrex_online'] }}</h3>
        <p style="color: var(--text-secondary); font-size: 0.875rem;">En ligne</p>
    </div>

    <!-- Disponibilité -->
    <div class="card" style="text-align: center;">
        <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📊</div>
        <h3 style="font-size: 2rem; margin-bottom: 0.25rem;">{{ $stats['uptime_percentage'] }}%</h3>
        <p style="color: var(--text-secondary); font-size: 0.875rem;">Disponibilité</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
    <!-- Graphique statut des centrex -->
    <div class="card">
        <h3 style="margin-bottom: 1.5rem;">Statut des Centrex</h3>
        <canvas id="statusChart" style="max-height: 300px;"></canvas>
    </div>

    <!-- Dernières vérifications -->
    <div class="card">
        <h3 style="margin-bottom: 1.5rem;">Dernières vérifications</h3>
        @if($recentChecks->count() > 0)
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                @foreach($recentChecks as $centrex)
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background-color: var(--bg-tertiary); border-radius: var(--border-radius);">
                        <div>
                            <strong>{{ $centrex->name }}</strong>
                            <p style="font-size: 0.875rem; color: var(--text-secondary); margin-top: 0.25rem;">
                                {{ $centrex->last_check->diffForHumans() }}
                            </p>
                        </div>
                        <div>
                            @if($centrex->status === 'online')
                                <span style="background-color: var(--color-success); color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem;">● En ligne</span>
                            @elseif($centrex->status === 'offline')
                                <span style="background-color: var(--color-danger); color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem;">● Hors ligne</span>
                            @else
                                <span style="background-color: var(--color-warning); color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem;">● Maintenance</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p style="text-align: center; color: var(--text-secondary); padding: 2rem;">Aucune vérification effectuée</p>
        @endif
    </div>
</div>

<!-- Actions rapides -->
<div class="card">
    <h3 style="margin-bottom: 1rem;">Actions rapides</h3>
    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
        <a href="{{ route('admin.clients.index') }}" class="btn btn-primary">Gérer les clients</a>
        <a href="{{ route('admin.centrex.index') }}" class="btn btn-success">Gérer les centrex</a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Récupérer les couleurs CSS
    const styles = getComputedStyle(document.documentElement);
    
    // Créer le graphique
    const ctx = document.getElementById('statusChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($chartData['labels']) !!},
            datasets: [{
                data: {!! json_encode($chartData['data']) !!},
                backgroundColor: {!! json_encode($chartData['colors']) !!},
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: styles.getPropertyValue('--text-primary').trim(),
                        padding: 15,
                        font: {
                            size: 14
                        }
                    }
                }
            }
        }
    });
});
</script>
@endsection