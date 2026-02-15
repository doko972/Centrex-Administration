<?php

namespace Database\Seeders;

use App\Models\ConnectionType;
use Illuminate\Database\Seeder;

class ConnectionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'ADSL', 'description' => 'Asymmetric Digital Subscriber Line', 'sort_order' => 1],
            ['name' => 'VDSL', 'description' => 'Very-high-bit-rate Digital Subscriber Line', 'sort_order' => 2],
            ['name' => 'SDSL', 'description' => 'Symmetric Digital Subscriber Line', 'sort_order' => 3],
            ['name' => 'FTTH', 'description' => 'Fiber To The Home', 'sort_order' => 4],
            ['name' => 'FTTE', 'description' => 'Fiber To The Enterprise', 'sort_order' => 5],
            ['name' => 'FTTO', 'description' => 'Fiber To The Office', 'sort_order' => 6],
            ['name' => '4G', 'description' => 'Connexion mobile 4G LTE', 'sort_order' => 7],
            ['name' => '5G', 'description' => 'Connexion mobile 5G', 'sort_order' => 8],
            ['name' => 'Satellite', 'description' => 'Connexion satellite', 'sort_order' => 9],
        ];

        foreach ($types as $type) {
            ConnectionType::firstOrCreate(
                ['name' => $type['name']],
                [
                    'description' => $type['description'],
                    'sort_order' => $type['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
