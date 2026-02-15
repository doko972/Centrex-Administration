<?php

namespace Database\Seeders;

use App\Models\Equipment;
use Illuminate\Database\Seeder;

class EquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $equipment = [
            // Réseau
            ['name' => 'Routeur', 'category' => 'Réseau'],
            ['name' => 'Switch', 'category' => 'Réseau'],
            ['name' => 'Switch PoE', 'category' => 'Réseau'],
            ['name' => 'Point d\'accès WiFi', 'category' => 'Réseau'],
            ['name' => 'Modem', 'category' => 'Réseau'],
            ['name' => 'ONT (Fibre)', 'category' => 'Réseau'],

            // Téléphonie
            ['name' => 'Téléphone IP', 'category' => 'Téléphonie'],
            ['name' => 'Téléphone IP avec écran', 'category' => 'Téléphonie'],
            ['name' => 'Téléphone sans fil DECT', 'category' => 'Téléphonie'],
            ['name' => 'Base DECT', 'category' => 'Téléphonie'],
            ['name' => 'Casque téléphonique', 'category' => 'Téléphonie'],
            ['name' => 'Gateway FXS', 'category' => 'Téléphonie'],
            ['name' => 'Gateway FXO', 'category' => 'Téléphonie'],

            // Sécurité
            ['name' => 'Firewall', 'category' => 'Sécurité'],
            ['name' => 'Pare-feu UTM', 'category' => 'Sécurité'],
            ['name' => 'VPN Concentrator', 'category' => 'Sécurité'],

            // Backup
            ['name' => 'Routeur 4G/5G', 'category' => 'Backup'],
            ['name' => 'Antenne 4G/5G', 'category' => 'Backup'],

            // Serveurs
            ['name' => 'Serveur', 'category' => 'Serveurs'],
            ['name' => 'NAS', 'category' => 'Serveurs'],
            ['name' => 'UPS / Onduleur', 'category' => 'Serveurs'],
        ];

        foreach ($equipment as $eq) {
            Equipment::firstOrCreate(
                ['name' => $eq['name']],
                [
                    'category' => $eq['category'],
                    'is_predefined' => true,
                    'is_active' => true,
                ]
            );
        }
    }
}
