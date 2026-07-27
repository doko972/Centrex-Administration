<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\User;
use App\Models\ConnectionType;
use App\Models\Provider;
use App\Models\Equipment;
use App\Mail\WelcomeNewUser;
use App\Mail\PasswordChangedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ClientController extends Controller
{
    /**
     * Afficher la liste des clients
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $clients = $this->searchQuery($search)
            ->orderBy('company_name', 'asc')
            ->paginate(20)
            ->withQueryString();

        return view('admin.clients.index', compact('clients', 'search'));
    }

    /**
     * Exporter la liste des clients (respecte le filtre de recherche courant) au format CSV
     */
    public function export(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $clients = $this->searchQuery($search)->orderBy('company_name', 'asc')->get();

        $filename = 'clients-' . now()->format('Y-m-d_H-i') . '.csv';

        return response()->streamDownload(function () use ($clients) {
            $handle = fopen('php://output', 'w');
            // BOM UTF-8 pour un affichage correct des accents dans Excel
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Entreprise', 'Contact', 'Nom', 'Email', 'Téléphone', 'Statut', 'Date de création'], ';');

            foreach ($clients as $client) {
                fputcsv($handle, [
                    $client->company_name,
                    $client->contact_name,
                    $client->user->name,
                    $client->email,
                    $client->phone,
                    $client->is_active ? 'Actif' : 'Inactif',
                    $client->created_at->format('d/m/Y'),
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Requête de base filtrée par recherche, partagée entre l'index et l'export
     */
    private function searchQuery(string $search)
    {
        return Client::with('user')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('company_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            });
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

        $plainPassword = $validated['password'];

        DB::transaction(function () use ($validated, $request, $plainPassword) {
            // Créer l'utilisateur avec flag de changement de mot de passe obligatoire
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $plainPassword,
                'role' => 'client',
                'must_change_password' => true,
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

        // Envoyer l'email de bienvenue avec les identifiants provisoires
        $createdUser = User::where('email', $validated['email'])->first();
        if ($createdUser) {
            try {
                Mail::to($createdUser->email)->send(new WelcomeNewUser($createdUser, $plainPassword));
            } catch (\Exception $e) {
                // L'email échoue silencieusement pour ne pas bloquer la création
            }
        }

        $client = Client::where('email', $validated['email'])->first();
        AuditLog::record(
            'client.created',
            "Client créé : {$validated['company_name']} ({$validated['email']})",
            $client
        );

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client créé avec succès ! Un email de bienvenue lui a été envoyé.');
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

        AuditLog::record(
            'client.updated',
            "Client modifié : {$client->company_name} ({$client->email})",
            $client
        );

        if ($request->filled('password')) {
            $client->user->refresh();
            try {
                Mail::to($client->user->email)->send(
                    new PasswordChangedMail($client->user, 'par un administrateur')
                );
            } catch (\Exception $e) {
                // L'email échoue silencieusement pour ne pas bloquer la mise à jour
            }
            AuditLog::record(
                'client.password_changed',
                "Mot de passe modifié par un administrateur pour : {$client->company_name} ({$client->email})",
                $client
            );
        }

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client mis à jour avec succès !');
    }

    /**
     * Supprimer un client
     */
    public function destroy(Client $client)
    {
        AuditLog::record(
            'client.deleted',
            "Client supprimé : {$client->company_name} ({$client->email})",
            $client
        );

        $client->user->delete(); // Supprime aussi le client grâce à la cascade

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client supprimé avec succès !');
    }
}
