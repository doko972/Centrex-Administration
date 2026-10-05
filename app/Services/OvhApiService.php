<?php

namespace App\Services;

use App\Exceptions\OvhApiException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Encapsule les appels à l'API OVHcloud (endpoint ovh-eu par défaut).
 *
 * Authentification : signature SHA1 conforme à la spec OVH
 *   signature = "$1$" . sha1(APP_SECRET+CONSUMER_KEY+METHOD+URL+BODY+TIMESTAMP)
 *
 * Droits minimum à donner au token lors de sa création :
 *   GET  /vps               GET  /vps/*            GET  /vps/* /tasks
 *   POST /vps/* /reboot     POST /vps/* /start     POST /vps/* /stop
 *   GET  /ip                GET  /ip/*
 */
class OvhApiService
{
    private string $endpoint;
    private ?string $applicationKey;
    private ?string $applicationSecret;
    private ?string $consumerKey;
    private int $timeout;

    public function __construct()
    {
        $this->endpoint = rtrim((string) config('services.ovh.endpoint'), '/');
        $this->applicationKey = config('services.ovh.application_key');
        $this->applicationSecret = config('services.ovh.application_secret');
        $this->consumerKey = config('services.ovh.consumer_key');
        $this->timeout = (int) config('services.ovh.timeout', 15);
    }

    /**
     * Les 3 identifiants sont-ils présents ? À tester avant d'afficher un widget OVH.
     */
    public function isConfigured(): bool
    {
        return filled($this->applicationKey)
            && filled($this->applicationSecret)
            && filled($this->consumerKey);
    }

    /**
     * Droits réellement accordés au token courant (rules, status, expiration).
     * Route toujours autorisée : sert de diagnostic quand une autre renvoie 403.
     */
    public function getCurrentCredential(): array
    {
        return $this->get('/auth/currentCredential');
    }

    /*
    |--------------------------------------------------------------------------
    | VPS
    |--------------------------------------------------------------------------
    */

    /**
     * Liste les noms de service des VPS du compte (ex: "vps-abc12345.vps.ovh.net").
     *
     * @return array<int, string>
     */
    public function listVps(): array
    {
        return $this->get('/vps');
    }

    /**
     * Détail d'un VPS : state, displayName, offerType, zone, model, etc.
     */
    public function getVps(string $serviceName): array
    {
        return $this->get('/vps/' . rawurlencode($serviceName));
    }

    /**
     * IP rattachées à un VPS (route native OVH, plus directe qu'un filtre sur /ip).
     *
     * @return array<int, string>
     */
    public function getVpsIps(string $serviceName): array
    {
        return $this->get('/vps/' . rawurlencode($serviceName) . '/ips');
    }

    /**
     * Tâches en cours ou passées sur un VPS (utile pour suivre un reboot).
     *
     * @return array<int, int>
     */
    public function getVpsTasks(string $serviceName): array
    {
        return $this->get('/vps/' . rawurlencode($serviceName) . '/tasks');
    }

    public function rebootVps(string $serviceName): array
    {
        return $this->post('/vps/' . rawurlencode($serviceName) . '/reboot');
    }

    public function startVps(string $serviceName): array
    {
        return $this->post('/vps/' . rawurlencode($serviceName) . '/start');
    }

    public function stopVps(string $serviceName): array
    {
        return $this->post('/vps/' . rawurlencode($serviceName) . '/stop');
    }

    /**
     * Liste enrichie : chaque VPS avec son détail et ses IP.
     * Mise en cache pour ne pas marteler l'API à chaque affichage du dashboard.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getVpsOverview(int $ttlSeconds = 60): array
    {
        return Cache::remember('ovh.vps.overview', $ttlSeconds, function () {
            return array_map(function (string $serviceName) {
                return array_merge($this->getVps($serviceName), [
                    'serviceName' => $serviceName,
                    'ips' => $this->getVpsIps($serviceName),
                ]);
            }, $this->listVps());
        });
    }

    /*
    |--------------------------------------------------------------------------
    | IP
    |--------------------------------------------------------------------------
    */

    /**
     * Liste les blocs IP du compte. $type : vps, dedicated, failover, cdn...
     *
     * @return array<int, string>
     */
    public function listIps(?string $type = null, ?string $routedTo = null): array
    {
        return $this->get('/ip', array_filter([
            'type' => $type,
            'routedTo.serviceName' => $routedTo,
        ]));
    }

    /**
     * Détail d'une IP ou d'un bloc (ex: "51.75.0.1" ou "51.75.0.0/32").
     */
    public function getIp(string $ip): array
    {
        return $this->get('/ip/' . rawurlencode($ip));
    }

    /*
    |--------------------------------------------------------------------------
    | Couche HTTP signée
    |--------------------------------------------------------------------------
    */

    public function get(string $path, array $query = []): array
    {
        if ($query !== []) {
            $path .= '?' . http_build_query($query);
        }

        return $this->request('GET', $path);
    }

    public function post(string $path, array $body = []): array
    {
        return $this->request('POST', $path, $body);
    }

    public function put(string $path, array $body = []): array
    {
        return $this->request('PUT', $path, $body);
    }

    public function delete(string $path): array
    {
        return $this->request('DELETE', $path);
    }

    /**
     * @throws OvhApiException
     */
    private function request(string $method, string $path, array $body = []): array
    {
        if (! $this->isConfigured()) {
            throw OvhApiException::notConfigured();
        }

        $url = $this->endpoint . $path;

        // OVH signe le corps brut : chaîne vide pour GET/DELETE, le JSON exact sinon.
        $rawBody = in_array($method, ['POST', 'PUT'], true) && $body !== []
            ? json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            : '';

        $timestamp = $this->timestamp();

        $request = Http::timeout($this->timeout)->withHeaders([
            'Content-Type' => 'application/json; charset=utf-8',
            'X-Ovh-Application' => $this->applicationKey,
            'X-Ovh-Consumer' => $this->consumerKey,
            'X-Ovh-Timestamp' => (string) $timestamp,
            'X-Ovh-Signature' => $this->sign($method, $url, $rawBody, $timestamp),
        ]);

        if ($rawBody !== '') {
            $request = $request->withBody($rawBody, 'application/json');
        }

        $response = $request->send($method, $url);
        $payload = $response->json();

        if ($response->failed()) {
            Log::warning('Appel API OVH en échec', [
                'method' => $method,
                'path' => $path,
                'status' => $response->status(),
                'body' => $payload,
            ]);

            throw OvhApiException::fromResponse(
                $method,
                $path,
                $response->status(),
                is_array($payload) ? $payload : null
            );
        }

        // Certaines routes renvoient un scalaire (null, bool) : on normalise en tableau.
        return is_array($payload) ? $payload : ['result' => $payload];
    }

    /**
     * Signature OVH. L'URL signée est l'URL complète, query string incluse.
     */
    private function sign(string $method, string $url, string $body, int $timestamp): string
    {
        return '$1$' . sha1(implode('+', [
            $this->applicationSecret,
            $this->consumerKey,
            $method,
            $url,
            $body,
            $timestamp,
        ]));
    }

    /**
     * Horloge OVH. On mémorise l'écart avec l'horloge locale : une dérive de plus
     * de ~30 s fait rejeter la signature par l'API (INVALID_SIGNATURE).
     */
    private function timestamp(): int
    {
        $drift = Cache::remember('ovh.api.time_drift', 3600, function () {
            $response = Http::timeout($this->timeout)->get($this->endpoint . '/auth/time');

            return $response->successful() ? (int) $response->body() - time() : 0;
        });

        return time() + (int) $drift;
    }
}
