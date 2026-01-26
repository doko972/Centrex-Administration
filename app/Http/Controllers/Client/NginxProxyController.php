<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Centrex;
use Illuminate\Support\Facades\Auth;

class NginxProxyController extends Controller
{
    /**
     * Afficher la page avec iframe du centrex via le proxy Nginx
     */
    public function show(Centrex $centrex)
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

        return view('client.centrex-nginx-proxy', compact('centrex'));
    }
}
