<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Centrex;
use Illuminate\Http\Request;

class ClientCentrexController extends Controller
{
    /**
     * Afficher le formulaire d'association
     */
    public function manage(Client $client)
    {
        $allCentrex = Centrex::where('is_active', true)->get();
        $clientCentrex = $client->centrex->pluck('id')->toArray();
        
        return view('admin.clients.manage-centrex', compact('client', 'allCentrex', 'clientCentrex'));
    }

    /**
     * Mettre à jour les associations
     */
    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'centrex' => 'nullable|array',
            'centrex.*' => 'exists:centrex,id',
        ]);

        // Synchroniser les centrex (ajoute les nouveaux, retire les anciens)
        $client->centrex()->sync($validated['centrex'] ?? []);

        return redirect()->route('admin.clients.show', $client)
            ->with('success', 'Les centrex ont été associés avec succès !');
    }
}