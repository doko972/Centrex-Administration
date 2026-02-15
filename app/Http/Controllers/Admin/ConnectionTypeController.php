<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConnectionType;
use Illuminate\Http\Request;

class ConnectionTypeController extends Controller
{
    /**
     * Afficher la liste des types de connexion
     */
    public function index()
    {
        $connectionTypes = ConnectionType::ordered()->get();
        return view('admin.connection-types.index', compact('connectionTypes'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        return view('admin.connection-types.create');
    }

    /**
     * Enregistrer un nouveau type de connexion
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:connection_types,name',
            'description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        ConnectionType::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.connection-types.index')
            ->with('success', 'Type de connexion créé avec succès !');
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(ConnectionType $connectionType)
    {
        return view('admin.connection-types.edit', compact('connectionType'));
    }

    /**
     * Mettre à jour un type de connexion
     */
    public function update(Request $request, ConnectionType $connectionType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:connection_types,name,' . $connectionType->id,
            'description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $connectionType->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.connection-types.index')
            ->with('success', 'Type de connexion mis à jour avec succès !');
    }

    /**
     * Supprimer un type de connexion
     */
    public function destroy(ConnectionType $connectionType)
    {
        $connectionType->delete();

        return redirect()->route('admin.connection-types.index')
            ->with('success', 'Type de connexion supprimé avec succès !');
    }
}
