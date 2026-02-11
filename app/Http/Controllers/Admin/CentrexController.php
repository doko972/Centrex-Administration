<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Centrex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CentrexController extends Controller
{
    /**
     * Afficher la liste des centrex
     */
    public function index()
    {
        $centrex = Centrex::with('clients')->orderBy('name', 'asc')->get();
        return view('admin.centrex.index', compact('centrex'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        return view('admin.centrex.create');
    }

    /**
     * Enregistrer un nouveau centrex
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535',
            'login' => 'required|string|max:255',
            'password' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
        ]);

        // Gestion de l'upload de l'image
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('centrex', 'public');
            $validated['image'] = $imagePath;
        }

        Centrex::create($validated);

        return redirect()->route('admin.centrex.index')
            ->with('success', 'Centrex créé avec succès !');
    }

    /**
     * Afficher un centrex spécifique
     */
    public function show(Centrex $centrex)
    {
        $centrex->load('clients');
        return view('admin.centrex.show', compact('centrex'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Centrex $centrex)
    {
        return view('admin.centrex.edit', compact('centrex'));
    }

    /**
     * Mettre à jour un centrex
     */
    public function update(Request $request, Centrex $centrex)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535',
            'login' => 'required|string|max:255',
            'password' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
            'status' => 'required|in:online,offline,maintenance',
        ]);

        // Gestion de l'upload de l'image
        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image si elle existe
            if ($centrex->image) {
                Storage::disk('public')->delete($centrex->image);
            }
            $imagePath = $request->file('image')->store('centrex', 'public');
            $validated['image'] = $imagePath;
        }

        // Ne mettre à jour le password que s'il est fourni
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->has('is_active');

        $centrex->update($validated);

        return redirect()->route('admin.centrex.index')
            ->with('success', 'Centrex mis à jour avec succès !');
    }

    /**
     * Supprimer un centrex
     */
    public function destroy(Centrex $centrex)
    {
        // Supprimer l'image si elle existe
        if ($centrex->image) {
            Storage::disk('public')->delete($centrex->image);
        }

        $centrex->delete();
        
        return redirect()->route('admin.centrex.index')
            ->with('success', 'Centrex supprimé avec succès !');
    }
}