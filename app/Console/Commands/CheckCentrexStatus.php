<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Centrex;
use Illuminate\Support\Facades\Http;

class CheckCentrexStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'centrex:check-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifier le statut de tous les centrex actifs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Vérification du statut des centrex...');
        $this->newLine();

        $centrex = Centrex::where('is_active', true)->get();

        if ($centrex->isEmpty()) {
            $this->warn('Aucun centrex actif à vérifier.');
            return;
        }

        $online = 0;
        $offline = 0;

        foreach ($centrex as $item) {
            $url = "http://{$item->ip_address}";

            $this->line("Vérification de {$item->name} ({$url})...");

            try {
                // Tentative de connexion avec timeout de 5 secondes
                $response = Http::timeout(5)->get($url);

                if ($response->successful() || $response->status() === 401) {
                    // 200 = OK, 401 = Page de login (donc le serveur répond)
                    $item->update([
                        'status' => 'online',
                        'last_check' => now(),
                    ]);
                    $this->info("✅ {$item->name} : EN LIGNE");
                    $online++;
                } else {
                    $item->update([
                        'status' => 'offline',
                        'last_check' => now(),
                    ]);
                    $this->error("❌ {$item->name} : HORS LIGNE (HTTP {$response->status()})");
                    $offline++;
                }
            } catch (\Exception $e) {
                $item->update([
                    'status' => 'offline',
                    'last_check' => now(),
                ]);
                $this->error("❌ {$item->name} : HORS LIGNE (Connexion impossible)");
                $offline++;
            }
        }

        $this->newLine();
        $this->info("✅ Vérification terminée !");
        $this->info("📊 Résultat : {$online} en ligne, {$offline} hors ligne sur " . $centrex->count() . " centrex.");
    }
}
