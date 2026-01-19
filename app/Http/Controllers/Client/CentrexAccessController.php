<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Centrex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CentrexAccessController extends Controller
{
    /**
     * Afficher la page d'accès au centrex avec authentification automatique
     */
    public function access(Centrex $centrex)
    {
        $user = Auth::user();
        $client = $user->client;

        // Vérifier que le client a bien accès à ce centrex
        if (!$client->centrex->contains($centrex->id)) {
            abort(403, 'Vous n\'avez pas accès à ce centrex.');
        }

        // Vérifier que le centrex est actif
        if (!$centrex->is_active) {
            abort(403, 'Ce centrex n\'est pas disponible actuellement.');
        }

        // Construire l'URL FreePBX
        $url = "http://{$centrex->ip_address}";

        return view('client.centrex-access', compact('centrex', 'url'));
    }
}
