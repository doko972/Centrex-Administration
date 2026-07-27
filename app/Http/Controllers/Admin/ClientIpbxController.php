<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
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

        $before = $client->ipbx->pluck('id')->toArray();

        // Synchroniser les ipbx (ajoute les nouveaux, retire les anciens)
        $client->ipbx()->sync($validated['ipbx'] ?? []);

        $after = $validated['ipbx'] ?? [];
        $added = Ipbx::whereIn('id', array_diff($after, $before))->pluck('client_name')->all();
        $removed = Ipbx::whereIn('id', array_diff($before, $after))->pluck('client_name')->all();

        if ($added || $removed) {
            $parts = [];
            if ($added) $parts[] = 'ajouté(s) : ' . implode(', ', $added);
            if ($removed) $parts[] = 'retiré(s) : ' . implode(', ', $removed);

            AuditLog::record(
                'client.ipbx_associations_updated',
                "IPBX associés à {$client->company_name} mis à jour — " . implode(' / ', $parts),
                $client
            );
        }

        return redirect()->route('admin.clients.show', $client)
            ->with('success', 'Les IPBX ont été associés avec succès !');
    }
}
