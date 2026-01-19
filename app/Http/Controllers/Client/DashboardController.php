<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Afficher le dashboard client avec ses centrex
     */
    public function index()
    {
        $user = Auth::user();
        
        // Récupérer le client associé à l'utilisateur
        $client = $user->client;
        
        if (!$client) {
            abort(403, 'Aucun profil client associé à cet utilisateur.');
        }
        
        // Récupérer les centrex actifs associés au client
        $centrex = $client->centrex()
            ->where('is_active', true)
            ->get();
        
        return view('client.dashboard', compact('client', 'centrex'));
    }
}