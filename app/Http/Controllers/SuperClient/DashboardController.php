<?php

namespace App\Http\Controllers\SuperClient;

use App\Http\Controllers\Controller;
use App\Models\Centrex;
use App\Models\Client;
use App\Models\Ipbx;

class DashboardController extends Controller
{
    /**
     * Afficher le dashboard superclient avec tous les Centrex et IPBX actifs
     */
    public function index()
    {
        $centrex = Centrex::where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();

        $ipbx = Ipbx::where('is_active', true)
            ->orderBy('client_name', 'asc')
            ->get();

        $totalEquipment = $centrex->count() + $ipbx->count();
        $totalOnline = $centrex->where('status', 'online')->count() + $ipbx->where('status', 'online')->count();

        $stats = [
            'total_clients' => Client::where('is_active', true)->count(),
            'total_centrex' => $centrex->count(),
            'total_ipbx' => $ipbx->count(),
            'total_offline' => $totalEquipment - $totalOnline,
            'uptime_percentage' => $totalEquipment > 0 ? round(($totalOnline / $totalEquipment) * 100, 1) : 0,
        ];

        return view('superclient.dashboard', compact('centrex', 'ipbx', 'stats'));
    }
}
