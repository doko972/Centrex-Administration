<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Ipbx;
use Illuminate\Http\Request;

class IpbxController extends Controller
{
    public function index()
    {
        $ipbxs = Ipbx::orderBy('client_name')->get();
        return view('admin.ipbx.index', compact('ipbxs'));
    }

    public function create()
    {
        return view('admin.ipbx.create');
    }

    /**
     * Exporter la liste des IPBX au format CSV
     */
    public function export()
    {
        $ipbxs = Ipbx::orderBy('client_name')->get();
        $filename = 'ipbx-' . now()->format('Y-m-d_H-i') . '.csv';

        return response()->streamDownload(function () use ($ipbxs) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Client', 'Contact', 'Email', 'Téléphone', 'Adresse IP', 'Port', 'Statut', 'Actif', 'Dernier ping'], ';');

            foreach ($ipbxs as $ipbx) {
                fputcsv($handle, [
                    $ipbx->client_name,
                    $ipbx->contact_name,
                    $ipbx->email,
                    $ipbx->phone,
                    $ipbx->ip_address,
                    $ipbx->port,
                    $ipbx->status,
                    $ipbx->is_active ? 'Oui' : 'Non',
                    $ipbx->last_ping?->format('d/m/Y H:i') ?? '-',
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'ip_address' => 'required|ip',
            'port' => 'required|integer|between:1,65535',
            'login' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Ne pas inclure le password s'il est vide
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $ipbx = Ipbx::create($validated);

        AuditLog::record(
            'ipbx.created',
            "IPBX créé : {$ipbx->client_name} ({$ipbx->ip_address})",
            $ipbx
        );

        return redirect()->route('admin.ipbx.index')
            ->with('success', 'IPBX ajoute avec succes.');
    }

    public function show(Ipbx $ipbx)
    {
        // Charger les clients associes
        $ipbx->load('clients.user');
        return view('admin.ipbx.show', compact('ipbx'));
    }

    public function edit(Ipbx $ipbx)
    {
        return view('admin.ipbx.edit', compact('ipbx'));
    }

    public function update(Request $request, Ipbx $ipbx)
    {
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'ip_address' => 'required|ip',
            'port' => 'required|integer|between:1,65535',
            'login' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        // Ne pas modifier le password s'il est vide (conserver l'ancien)
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $ipbx->update($validated);

        AuditLog::record(
            'ipbx.updated',
            "IPBX modifié : {$ipbx->client_name} ({$ipbx->ip_address})",
            $ipbx
        );

        return redirect()->route('admin.ipbx.index')
            ->with('success', 'IPBX mis a jour avec succes.');
    }

    public function destroy(Ipbx $ipbx)
    {
        AuditLog::record(
            'ipbx.deleted',
            "IPBX supprimé : {$ipbx->client_name} ({$ipbx->ip_address})",
            $ipbx
        );

        $ipbx->delete();

        return redirect()->route('admin.ipbx.index')
            ->with('success', 'IPBX supprime avec succes.');
    }

    public function ping(Ipbx $ipbx)
    {
        $status = $ipbx->checkAndUpdateStatus();

        return response()->json([
            'status' => $status,
            'last_ping' => $ipbx->last_ping->format('d/m/Y H:i:s'),
        ]);
    }
}
