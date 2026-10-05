<?php

namespace App\Console\Commands;

use App\Exceptions\OvhApiException;
use App\Services\OvhApiService;
use Illuminate\Console\Command;

class OvhTestConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ovh:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tester la connexion à l\'API OVH et lister les VPS avec leurs IP';

    /**
     * Execute the console command.
     */
    public function handle(OvhApiService $ovh)
    {
        if (! $ovh->isConfigured()) {
            $this->error('Identifiants OVH absents. Renseigne OVH_APPLICATION_KEY, '
                . 'OVH_APPLICATION_SECRET et OVH_CONSUMER_KEY dans le .env.');

            return self::FAILURE;
        }

        $this->info('🔌 Connexion à l\'API OVH...');

        // Diagnostic : affiche les droits réellement portés par le token.
        try {
            $credential = $ovh->getCurrentCredential();

            $this->line('   Statut du token : ' . ($credential['status'] ?? '—'));
            $this->line('   Expiration      : ' . ($credential['expiration'] ?? 'illimitée'));
            $this->newLine();
            $this->line('   Droits accordés :');

            foreach ($credential['rules'] ?? [] as $rule) {
                $this->line("     {$rule['method']}\t{$rule['path']}");
            }

            $this->newLine();
        } catch (OvhApiException $e) {
            $this->error('Impossible de lire les droits du token : ' . $e->getMessage());

            return self::FAILURE;
        }

        // /me n'est pas dans les scopes recommandés : simple bonus si le droit existe.
        try {
            $me = $ovh->get('/me');
            $this->line("   Compte : {$me['nichandle']} ({$me['email']})");
        } catch (OvhApiException) {
            // Droit GET /me absent du token : sans conséquence pour le reste.
        }

        try {
            $vpsList = $ovh->listVps();
        } catch (OvhApiException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        if ($vpsList === []) {
            $this->warn('Aucun VPS sur ce compte.');

            return self::SUCCESS;
        }

        $this->newLine();

        $rows = [];

        foreach ($vpsList as $serviceName) {
            try {
                $vps = $ovh->getVps($serviceName);
                $ips = $ovh->getVpsIps($serviceName);

                $rows[] = [
                    $serviceName,
                    $vps['displayName'] ?? '—',
                    $vps['state'] ?? '—',
                    $vps['zone'] ?? '—',
                    implode(', ', $ips),
                ];
            } catch (OvhApiException $e) {
                $rows[] = [$serviceName, '—', 'erreur', '—', $e->getMessage()];
            }
        }

        $this->table(['Service', 'Nom affiché', 'État', 'Zone', 'IP'], $rows);

        return self::SUCCESS;
    }
}
