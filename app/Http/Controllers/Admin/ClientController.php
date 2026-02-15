<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use App\Models\ConnectionType;
use App\Models\Provider;
use App\Models\Equipment;
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
        $connectionTypes = ConnectionType::active()->ordered()->get();
        $providers = Provider::active()->orderBy('name')->get();
        $equipment = Equipment::active()->orderBy('category')->orderBy('name')->get();

        return view('admin.clients.create', compact('connectionTypes', 'providers', 'equipment'));
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
            // Nouveaux champs
            'connection_types' => 'nullable|array',
            'connection_types.*' => 'exists:connection_types,id',
            'providers' => 'nullable|array',
            'providers.*' => 'exists:providers,id',
            'equipment' => 'nullable|array',
            'equipment.*.id' => 'exists:equipment,id',
            'equipment.*.quantity' => 'nullable|integer|min:1',
            'equipment.*.notes' => 'nullable|string|max:500',
            'custom_equipment' => 'nullable|string|max:1000',
            'has_4g5g_backup' => 'nullable|boolean',
            'backup_operator' => 'nullable|string|max:255',
            'backup_sim_number' => 'nullable|string|max:255',
            'backup_phone_number' => 'nullable|string|max:255',
            'backup_notes' => 'nullable|string|max:1000',
        ], [
            'password.regex' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial (@$!%*?&#).',
        ]);

        DB::transaction(function () use ($validated, $request) {
            // Créer l'utilisateur
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => 'client',
            ]);

            // Créer le client
            $client = Client::create([
                'user_id' => $user->id,
                'company_name' => $validated['company_name'],
                'contact_name' => $validated['contact_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'has_4g5g_backup' => $request->has('has_4g5g_backup'),
                'backup_operator' => $validated['backup_operator'] ?? null,
                'backup_sim_number' => $validated['backup_sim_number'] ?? null,
                'backup_phone_number' => $validated['backup_phone_number'] ?? null,
                'backup_notes' => $validated['backup_notes'] ?? null,
            ]);

            // Sync des types de connexion
            if (!empty($validated['connection_types'])) {
                $client->connectionTypes()->sync($validated['connection_types']);
            }

            // Sync des fournisseurs
            if (!empty($validated['providers'])) {
                $client->providers()->sync($validated['providers']);
            }

            // Sync des équipements avec quantités
            if (!empty($validated['equipment'])) {
                $equipmentSync = [];
                foreach ($validated['equipment'] as $eq) {
                    if (isset($eq['id'])) {
                        $equipmentSync[$eq['id']] = [
                            'quantity' => $eq['quantity'] ?? 1,
                            'notes' => $eq['notes'] ?? null,
                        ];
                    }
                }
                $client->equipment()->sync($equipmentSync);
            }

            // Créer les équipements personnalisés
            if (!empty($validated['custom_equipment'])) {
                $customItems = array_filter(array_map('trim', explode(',', $validated['custom_equipment'])));
                foreach ($customItems as $itemName) {
                    if (!empty($itemName)) {
                        $equipment = Equipment::firstOrCreate(
                            ['name' => $itemName, 'is_predefined' => false],
                            ['category' => 'Personnalisé', 'is_active' => true]
                        );
                        $client->equipment()->attach($equipment->id, ['quantity' => 1]);
                    }
                }
            }
        });

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client créé avec succès !');
    }

    /**
     * Afficher un client spécifique
     */
    public function show(Client $client)
    {
        $client->load('user', 'centrex', 'ipbx', 'connectionTypes', 'providers', 'equipment');
        return view('admin.clients.show', compact('client'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Client $client)
    {
        $client->load('connectionTypes', 'providers', 'equipment');
        $connectionTypes = ConnectionType::active()->ordered()->get();
        $providers = Provider::active()->orderBy('name')->get();
        $equipment = Equipment::active()->orderBy('category')->orderBy('name')->get();

        return view('admin.clients.edit', compact('client', 'connectionTypes', 'providers', 'equipment'));
    }

    /**
     * Mettre à jour un client
     */
    public function update(Request $request, Client $client)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            // Nouveaux champs
            'connection_types' => 'nullable|array',
            'connection_types.*' => 'exists:connection_types,id',
            'providers' => 'nullable|array',
            'providers.*' => 'exists:providers,id',
            'equipment' => 'nullable|array',
            'equipment.*.id' => 'exists:equipment,id',
            'equipment.*.quantity' => 'nullable|integer|min:1',
            'equipment.*.notes' => 'nullable|string|max:500',
            'custom_equipment' => 'nullable|string|max:1000',
            'has_4g5g_backup' => 'nullable|boolean',
            'backup_operator' => 'nullable|string|max:255',
            'backup_sim_number' => 'nullable|string|max:255',
            'backup_phone_number' => 'nullable|string|max:255',
            'backup_notes' => 'nullable|string|max:1000',
        ];

        // Ajouter la validation du mot de passe seulement s'il est fourni
        if ($request->filled('password')) {
            $rules['password'] = 'min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]+$/';
        }

        $validated = $request->validate($rules, [
            'password.regex' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial (@$!%*?&#).',
        ]);

        DB::transaction(function () use ($validated, $client, $request) {
            // Préparer les données de l'utilisateur
            $userData = ['name' => $validated['name']];

            // Ajouter le mot de passe si fourni
            if ($request->filled('password')) {
                $userData['password'] = $validated['password'];
            }

            // Mettre à jour l'utilisateur
            $client->user->update($userData);

            // Mettre à jour le client
            $client->update([
                'company_name' => $validated['company_name'],
                'contact_name' => $validated['contact_name'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'is_active' => $request->has('is_active'),
                'has_4g5g_backup' => $request->has('has_4g5g_backup'),
                'backup_operator' => $validated['backup_operator'] ?? null,
                'backup_sim_number' => $validated['backup_sim_number'] ?? null,
                'backup_phone_number' => $validated['backup_phone_number'] ?? null,
                'backup_notes' => $validated['backup_notes'] ?? null,
            ]);

            // Sync des types de connexion
            $client->connectionTypes()->sync($validated['connection_types'] ?? []);

            // Sync des fournisseurs
            $client->providers()->sync($validated['providers'] ?? []);

            // Sync des équipements avec quantités
            $equipmentSync = [];
            if (!empty($validated['equipment'])) {
                foreach ($validated['equipment'] as $eq) {
                    if (isset($eq['id'])) {
                        $equipmentSync[$eq['id']] = [
                            'quantity' => $eq['quantity'] ?? 1,
                            'notes' => $eq['notes'] ?? null,
                        ];
                    }
                }
            }
            $client->equipment()->sync($equipmentSync);

            // Créer les équipements personnalisés
            if (!empty($validated['custom_equipment'])) {
                $customItems = array_filter(array_map('trim', explode(',', $validated['custom_equipment'])));
                foreach ($customItems as $itemName) {
                    if (!empty($itemName)) {
                        $equipment = Equipment::firstOrCreate(
                            ['name' => $itemName, 'is_predefined' => false],
                            ['category' => 'Personnalisé', 'is_active' => true]
                        );
                        if (!$client->equipment()->where('equipment_id', $equipment->id)->exists()) {
                            $client->equipment()->attach($equipment->id, ['quantity' => 1]);
                        }
                    }
                }
            }
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
