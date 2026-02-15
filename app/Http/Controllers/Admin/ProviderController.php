<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    /**
     * Afficher la liste des fournisseurs
     */
    public function index()
    {
        $providers = Provider::orderBy('name')->get();
        return view('admin.providers.index', compact('providers'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        return view('admin.providers.create');
    }

    /**
     * Enregistrer un nouveau fournisseur
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:providers,name',
            'url' => 'nullable|url|max:500',
        ]);

        Provider::create([
            'name' => $validated['name'],
            'url' => $validated['url'] ?? null,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.providers.index')
            ->with('success', 'Fournisseur créé avec succès !');
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Provider $provider)
    {
        return view('admin.providers.edit', compact('provider'));
    }

    /**
     * Mettre à jour un fournisseur
     */
    public function update(Request $request, Provider $provider)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:providers,name,' . $provider->id,
            'url' => 'nullable|url|max:500',
        ]);

        $provider->update([
            'name' => $validated['name'],
            'url' => $validated['url'] ?? null,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.providers.index')
            ->with('success', 'Fournisseur mis à jour avec succès !');
    }

    /**
     * Supprimer un fournisseur
     */
    public function destroy(Provider $provider)
    {
        $provider->delete();

        return redirect()->route('admin.providers.index')
            ->with('success', 'Fournisseur supprimé avec succès !');
    }
}
