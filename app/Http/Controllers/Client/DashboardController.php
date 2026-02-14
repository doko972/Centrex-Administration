<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Afficher le dashboard client avec ses centrex et IPBX
     */
    public function index()
    {
        $user = Auth::user();

        // Recuperer le client associe a l'utilisateur
        $client = $user->client;

        if (!$client) {
            abort(403, 'Aucun profil client associe a cet utilisateur.');
        }

        // Recuperer les centrex actifs associes au client (ordre alphabetique)
        $centrex = $client->centrex()
            ->where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();

        // Recuperer les IPBX actifs associes au client (ordre alphabetique)
        $ipbx = $client->ipbx()
            ->where('is_active', true)
            ->orderBy('client_name', 'asc')
            ->get();

        return view('client.dashboard', compact('client', 'centrex', 'ipbx'));
    }
}
