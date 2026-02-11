<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Centrex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

class CentrexProxyController extends Controller
{
    /**
     * Vérifier que le client a accès au centrex
     */
    private function checkAccess(Centrex $centrex): void
    {
        $client = Auth::user()->client;

        if (!$client->centrex->contains($centrex->id)) {
            abort(403, 'Vous n\'avez pas accès à ce centrex.');
        }

        if (!$centrex->is_active) {
            abort(403, 'Ce centrex n\'est pas disponible actuellement.');
        }
    }

    /**
     * Récupérer ou créer le CookieJar pour ce centrex
     */
    private function getCookieJar(int $centrexId): CookieJar
    {
        $sessionKey = "centrex_cookies_{$centrexId}";
        $cookieData = session($sessionKey, []);

        return new CookieJar(false, $cookieData);
    }

    /**
     * Sauvegarder le CookieJar en session
     */
    private function saveCookieJar(int $centrexId, CookieJar $jar): void
    {
        $sessionKey = "centrex_cookies_{$centrexId}";
        session([$sessionKey => $jar->toArray()]);
    }

    /**
     * Vérifier si une session est déjà établie pour ce centrex
     */
    private function hasSession(int $centrexId): bool
    {
        $sessionKey = "centrex_authenticated_{$centrexId}";
        return session($sessionKey, false);
    }

    /**
     * Marquer la session comme établie
     */
    private function markAuthenticated(int $centrexId): void
    {
        $sessionKey = "centrex_authenticated_{$centrexId}";
        session([$sessionKey => true]);
    }

    /**
     * Afficher la page avec iframe du centrex
     */
    public function show(Request $request, Centrex $centrex)
    {
        $this->checkAccess($centrex);

        return view('client.centrex-view', compact('centrex'));
    }

    /**
     * Proxifier les requêtes vers FreePBX
     */
    public function proxy(Request $request, Centrex $centrex, $any = null)
    {
        $this->checkAccess($centrex);

        // Construire l'URL cible
        $path = $any ? '/' . $any : '/';
        $queryString = $request->getQueryString();
        if ($queryString) {
            $path .= '?' . $queryString;
        }

        $targetUrl = "http://{$centrex->ip_address}" . $path;

        if (config('app.debug')) {
            Log::debug('Proxy Request:', [
                'method' => $request->method(),
                'targetUrl' => $targetUrl,
            ]);
        }

        try {
            // Récupérer le CookieJar de la session
            $cookieJar = $this->getCookieJar($centrex->id);

            // Configuration de base du client
            $clientConfig = [
                'verify' => false,
                'timeout' => 60,
                'connect_timeout' => 10,
                'allow_redirects' => [
                    'max' => 5,
                    'track_redirects' => true,
                ],
                'cookies' => $cookieJar,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Referer' => "http://{$centrex->ip_address}/admin/",
                    'Origin' => "http://{$centrex->ip_address}",
                ],
            ];

            // Ajouter l'auth Basic uniquement si pas encore authentifié
            if (!$this->hasSession($centrex->id)) {
                $clientConfig['auth'] = [$centrex->login, $centrex->getDecryptedPassword()];
            }

            $client = new Client($clientConfig);

            // Préparer les options de requête
            $options = [];

            // Exécuter la requête selon la méthode HTTP
            $method = strtoupper($request->method());

            if ($method === 'POST') {
                $options['form_params'] = $request->all();
            }

            $response = $client->request($method, $targetUrl, $options);

            // Sauvegarder les cookies mis à jour et marquer comme authentifié
            $this->saveCookieJar($centrex->id, $cookieJar);
            $this->markAuthenticated($centrex->id);

            $body = (string) $response->getBody();
            $contentType = $response->getHeaderLine('Content-Type') ?: 'text/html';

            // Assets (JS, CSS, images, fonts) : retourner sans modification
            if ($this->isAsset($path, $contentType)) {
                return response($body, $response->getStatusCode())
                    ->withHeaders([
                        'Content-Type' => $contentType,
                        'Cache-Control' => 'public, max-age=86400',
                    ]);
            }

            // HTML : réécrire les URLs et injecter l'intercepteur AJAX
            if (str_contains($contentType, 'text/html')) {
                $body = $this->rewriteHtml($body, $centrex->id);
            }

            return response($body, $response->getStatusCode())
                ->withHeaders([
                    'Content-Type' => $contentType,
                    'X-Frame-Options' => 'SAMEORIGIN',
                ]);

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response ? $response->getStatusCode() : 500;

            // Si 401 et qu'on était authentifié, réinitialiser et réessayer
            if ($statusCode === 401 && $this->hasSession($centrex->id)) {
                session()->forget("centrex_authenticated_{$centrex->id}");
                session()->forget("centrex_cookies_{$centrex->id}");

                // Réessayer avec authentification (une seule fois)
                return $this->proxy($request, $centrex, $any);
            }

            Log::warning('Proxy: Client Error', [
                'status' => $statusCode,
                'url' => $targetUrl,
            ]);

            if ($response) {
                $body = (string) $response->getBody();
                $contentType = $response->getHeaderLine('Content-Type') ?: 'text/html';

                if (str_contains($contentType, 'text/html')) {
                    $body = $this->rewriteHtml($body, $centrex->id);
                }

                return response($body, $statusCode)
                    ->withHeaders(['Content-Type' => $contentType]);
            }

            return response('Erreur: Ressource non trouvée', $statusCode);

        } catch (\Exception $e) {
            Log::error('Proxy: Exception', [
                'message' => $e->getMessage(),
                'url' => $targetUrl,
            ]);

            return response('Erreur de connexion au centrex: ' . $e->getMessage(), 502);
        }
    }

    /**
     * Détecter si la requête concerne un asset statique
     */
    private function isAsset(string $path, string $contentType): bool
    {
        $assetExtensions = '/\.(js|css|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot|map)$/i';
        $assetContentTypes = ['javascript', 'css', 'image/', 'font/', 'application/font'];

        if (preg_match($assetExtensions, $path)) {
            return true;
        }

        foreach ($assetContentTypes as $type) {
            if (str_contains($contentType, $type)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Réécrire les URLs dans le HTML et injecter l'intercepteur AJAX
     */
    private function rewriteHtml(string $body, int $centrexId): string
    {
        $proxyBase = url("client/centrex/{$centrexId}/proxy");

        // Réécrire les chemins absolus (href, src, action)
        $body = preg_replace('/href=["\']\/((?!http)[^"\']*)["\']/i', 'href="' . $proxyBase . '/$1"', $body);
        $body = preg_replace('/src=["\']\/((?!http)[^"\']*)["\']/i', 'src="' . $proxyBase . '/$1"', $body);
        $body = preg_replace('/action=["\']\/((?!http)[^"\']*)["\']/i', 'action="' . $proxyBase . '/$1"', $body);

        // Ajouter la base tag
        $baseTag = '<base href="' . $proxyBase . '/admin/">';
        $body = preg_replace('/<head>/i', '<head>' . $baseTag, $body, 1);

        // Injecter l'intercepteur AJAX/Fetch
        $ajaxInterceptor = <<<JS
        <script>
        (function() {
            var proxyBase = "{$proxyBase}";

            function rewriteUrl(url) {
                if (typeof url !== "string") return url;
                // Ne pas réécrire si l'URL contient déjà le proxyBase
                if (url.indexOf(proxyBase) !== -1) return url;
                // Ne pas réécrire les URLs absolues externes
                if (url.startsWith("http://") || url.startsWith("https://")) return url;
                // Réécrire les URLs relatives
                if (url.startsWith("/")) {
                    return proxyBase + url;
                }
                return url;
            }

            var origOpen = XMLHttpRequest.prototype.open;
            XMLHttpRequest.prototype.open = function(method, url) {
                return origOpen.apply(this, [method, rewriteUrl(url)]);
            };

            var origFetch = window.fetch;
            window.fetch = function(url, options) {
                return origFetch.apply(this, [rewriteUrl(url), options]);
            };
        })();
        </script>
        JS;

        $body = preg_replace('/<\/head>/i', $ajaxInterceptor . '</head>', $body, 1);

        return $body;
    }
}
