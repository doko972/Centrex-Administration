<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protection CSRF des routes proxy FreePBX.
 *
 * Le jeton CSRF Laravel ne peut pas être utilisé (les formulaires FreePBX ne le contiennent pas),
 * on vérifie donc la provenance de la requête : seules les requêtes émises depuis l'application
 * elle-même (iframe, AJAX, formulaires FreePBX proxifiés) ou saisies directement par l'utilisateur
 * sont acceptées. Une requête déclenchée depuis un site tiers (lien, formulaire, image) est refusée.
 */
class VerifyProxyOrigin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Navigateurs modernes : en-tête fiable, non falsifiable par une page web
        $fetchSite = $request->headers->get('Sec-Fetch-Site');

        if ($fetchSite !== null) {
            // same-origin : requête émise par l'application / none : URL saisie ou favori
            if (in_array($fetchSite, ['same-origin', 'none'], true)) {
                return $next($request);
            }

            return $this->reject($request, "Sec-Fetch-Site: {$fetchSite}");
        }

        // Navigateurs anciens : repli sur Origin puis Referer
        $source = $request->headers->get('Origin') ?: $request->headers->get('Referer');

        if ($source === null || $source === 'null') {
            // Sans information de provenance, on tolère la lecture mais pas l'écriture
            return $request->isMethodSafe()
                ? $next($request)
                : $this->reject($request, 'provenance absente');
        }

        if ($this->isTrustedSource($request, $source)) {
            return $next($request);
        }

        return $this->reject($request, "provenance: {$source}");
    }

    /**
     * La provenance correspond-elle à l'hôte courant ou à APP_URL ?
     */
    private function isTrustedSource(Request $request, string $source): bool
    {
        $sourceHost = $this->hostWithPort($source);

        if ($sourceHost === null) {
            return false;
        }

        $trustedHosts = array_filter([
            strtolower($request->getHttpHost()),
            $this->hostWithPort((string) config('app.url')),
        ]);

        return in_array($sourceHost, $trustedHosts, true);
    }

    private function hostWithPort(string $url): ?string
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (!$host) {
            return null;
        }

        $port = parse_url($url, PHP_URL_PORT);

        return strtolower($host) . ($port ? ":{$port}" : '');
    }

    private function reject(Request $request, string $reason): Response
    {
        Log::warning('Proxy FreePBX : requête cross-site bloquée', [
            'reason' => $reason,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'user_id' => $request->user()?->id,
            'ip' => $request->ip(),
        ]);

        abort(403, 'Requête refusée : provenance non autorisée.');
    }
}
