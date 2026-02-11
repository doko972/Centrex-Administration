<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    /**
     * Afficher la liste des clients
     */
    public function index()
    {
        $clients = Client::with('user')->orderBy('company_name', 'asc')->get();
        return view('admin.clients.index', compact('clients'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        return view('admin.clients.create');
    }

    /**
     * Enregistrer un nouveau client
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]+$/',
            'company_name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ], [
            'password.regex' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial (@$!%*?&#).',
        ]);

        DB::transaction(function () use ($validated) {
            // Créer l'utilisateur
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'], // Le cast 'hashed' du modèle User gère le hashage automatiquement
                'role' => 'client',
            ]);

            // Créer le client
            Client::create([
                'user_id' => $user->id,
                'company_name' => $validated['company_name'],
                'contact_name' => $validated['contact_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
            ]);
        });

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client créé avec succès !');
    }

    /**
     * Afficher un client spécifique
     */
    public function show(Client $client)
    {
        $client->load('user', 'centrex');
        return view('admin.clients.show', compact('client'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Client $client)
    {
        return view('admin.clients.edit', compact('client'));
    }

    /**
     * Mettre à jour un client
     */
    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $client, $request) {
            // Mettre à jour l'utilisateur
            $client->user->update([
                'name' => $validated['name'],
            ]);

            // Mettre à jour le client
            $client->update([
                'company_name' => $validated['company_name'],
                'contact_name' => $validated['contact_name'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'is_active' => $request->has('is_active'),
            ]);
        });

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client mis à jour avec succès !');
    }

    /**
     * Supprimer un client
     */
    public function destroy(Client $client)
    {
        $client->user->delete(); // Supprime aussi le client grâce à la cascade

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client supprimé avec succès !');
    }
}
