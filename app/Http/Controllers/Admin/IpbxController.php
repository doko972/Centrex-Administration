<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

        Ipbx::create($validated);

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

        return redirect()->route('admin.ipbx.index')
            ->with('success', 'IPBX mis a jour avec succes.');
    }

    public function destroy(Ipbx $ipbx)
    {
        $ipbx->delete();

        return redirect()->route('admin.ipbx.index')
            ->with('success', 'IPBX supprime avec succes.');
    }

    public function ping(Ipbx $ipbx)
    {
        $status = 'offline';
        $ip = $ipbx->ip_address;

        // Ping ICMP (Windows: -n 1 -w 3000, Linux: -c 1 -W 3)
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            exec("ping -n 1 -w 3000 " . escapeshellarg($ip), $output, $result);
        } else {
            exec("ping -c 1 -W 3 " . escapeshellarg($ip), $output, $result);
        }

        if ($result === 0) {
            $status = 'online';
        }

        $ipbx->update([
            'status' => $status,
            'last_ping' => now(),
        ]);

        return response()->json([
            'status' => $status,
            'last_ping' => $ipbx->last_ping->format('d/m/Y H:i:s'),
        ]);
    }
}
