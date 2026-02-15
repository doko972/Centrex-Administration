<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    /**
     * Afficher la liste des équipements
     */
    public function index()
    {
        $equipment = Equipment::orderBy('category')->orderBy('name')->get();
        return view('admin.equipment.index', compact('equipment'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $categories = Equipment::distinct()->pluck('category')->filter()->toArray();
        return view('admin.equipment.create', compact('categories'));
    }

    /**
     * Enregistrer un nouvel équipement
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
        ]);

        Equipment::create([
            'name' => $validated['name'],
            'category' => $validated['category'] ?? null,
            'is_predefined' => $request->has('is_predefined'),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.equipment.index')
            ->with('success', 'Équipement créé avec succès !');
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Equipment $equipment)
    {
        $categories = Equipment::distinct()->pluck('category')->filter()->toArray();
        return view('admin.equipment.edit', compact('equipment', 'categories'));
    }

    /**
     * Mettre à jour un équipement
     */
    public function update(Request $request, Equipment $equipment)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
        ]);

        $equipment->update([
            'name' => $validated['name'],
            'category' => $validated['category'] ?? null,
            'is_predefined' => $request->has('is_predefined'),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.equipment.index')
            ->with('success', 'Équipement mis à jour avec succès !');
    }

    /**
     * Supprimer un équipement
     */
    public function destroy(Equipment $equipment)
    {
        $equipment->delete();

        return redirect()->route('admin.equipment.index')
            ->with('success', 'Équipement supprimé avec succès !');
    }
}
