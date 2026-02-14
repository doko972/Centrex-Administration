<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Ipbx;
use Illuminate\Http\Request;

class ClientIpbxController extends Controller
{
    /**
     * Afficher le formulaire d'association
     */
    public function manage(Client $client)
    {
        $allIpbx = Ipbx::where('is_active', true)->orderBy('client_name')->get();
        $clientIpbx = $client->ipbx->pluck('id')->toArray();

        return view('admin.clients.manage-ipbx', compact('client', 'allIpbx', 'clientIpbx'));
    }

    /**
     * Mettre à jour les associations
     */
    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'ipbx' => 'nullable|array',
            'ipbx.*' => 'exists:ipbx,id',
        ]);

        // Synchroniser les ipbx (ajoute les nouveaux, retire les anciens)
        $client->ipbx()->sync($validated['ipbx'] ?? []);

        return redirect()->route('admin.clients.show', $client)
            ->with('success', 'Les IPBX ont été associés avec succès !');
    }
}
