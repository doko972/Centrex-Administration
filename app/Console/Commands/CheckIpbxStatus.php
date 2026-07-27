<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ipbx;

class CheckIpbxStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ipbx:check-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifier le statut de tous les IPBX actifs (ping ICMP)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Vérification du statut des IPBX...');
        $this->newLine();

        $ipbxs = Ipbx::where('is_active', true)->get();

        if ($ipbxs->isEmpty()) {
            $this->warn('Aucun IPBX actif à vérifier.');
            return;
        }

        $online = 0;
        $offline = 0;

        foreach ($ipbxs as $ipbx) {
            $this->line("Vérification de {$ipbx->client_name} ({$ipbx->ip_address})...");

            $status = $ipbx->checkAndUpdateStatus();

            if ($status === 'online') {
                $this->info("✅ {$ipbx->client_name} : EN LIGNE");
                $online++;
            } else {
                $this->error("❌ {$ipbx->client_name} : HORS LIGNE");
                $offline++;
            }
        }

        $this->newLine();
        $this->info('✅ Vérification terminée !');
        $this->info("📊 Résultat : {$online} en ligne, {$offline} hors ligne sur " . $ipbxs->count() . ' IPBX.');
    }
}
