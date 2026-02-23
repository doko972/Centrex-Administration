<?php

namespace App\Http\Controllers\SuperClient;

use App\Http\Controllers\Controller;
use App\Models\Centrex;
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

        return view('superclient.dashboard', compact('centrex', 'ipbx'));
    }
}
