<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Centrex;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Afficher le dashboard admin avec statistiques
     */
    public function index()
    {
        // Statistiques générales
        $stats = [
            'total_clients' => Client::count(),
            'clients_actifs' => Client::where('is_active', true)->count(),
            'total_centrex' => Centrex::count(),
            'centrex_actifs' => Centrex::where('is_active', true)->count(),
            'centrex_online' => Centrex::where('status', 'online')->where('is_active', true)->count(),
            'centrex_offline' => Centrex::where('status', 'offline')->where('is_active', true)->count(),
            'centrex_maintenance' => Centrex::where('status', 'maintenance')->where('is_active', true)->count(),
        ];

        // Calculer le pourcentage de disponibilité
        $stats['uptime_percentage'] = $stats['centrex_actifs'] > 0 
            ? round(($stats['centrex_online'] / $stats['centrex_actifs']) * 100, 1)
            : 0;

        // Derniers centrex vérifiés
        $recentChecks = Centrex::whereNotNull('last_check')
            ->orderBy('last_check', 'desc')
            ->take(5)
            ->get();

        // Données pour le graphique (statut des centrex)
        $chartData = [
            'labels' => ['En ligne', 'Hors ligne', 'Maintenance'],
            'data' => [
                $stats['centrex_online'],
                $stats['centrex_offline'],
                $stats['centrex_maintenance']
            ],
            'colors' => ['#10b981', '#ef4444', '#f59e0b'] // success, danger, warning
        ];

        return view('admin.dashboard', compact('stats', 'recentChecks', 'chartData'));
    }
}