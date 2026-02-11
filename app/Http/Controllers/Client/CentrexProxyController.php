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
     * Vérifier si on est authentifié sur le centrex
     */
    private function isAuthenticated(int $centrexId): bool
    {
        return session("centrex_logged_in_{$centrexId}", false);
    }

    /**
     * Marquer comme authentifié
     */
    private function setAuthenticated(int $centrexId, bool $value = true): void
    {
        session(["centrex_logged_in_{$centrexId}" => $value]);
    }

    /**
     * Se connecter au FreePBX via le formulaire de login
     */
    private function loginToFreePBX(Centrex $centrex, CookieJar $cookieJar): bool
    {
        $client = new Client([
            'verify' => false,
            'timeout' => 30,
            'connect_timeout' => 10,
            'allow_redirects' => true,
            'cookies' => $cookieJar,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ],
        ]);

        try {
            // 1. D'abord accéder à la page de login pour obtenir une session
            $client->get("http://{$centrex->ip_address}/admin/");

            // 2. Soumettre le formulaire de login
            $response = $client->post("http://{$centrex->ip_address}/admin/config.php", [
                'form_params' => [
                    'username' => $centrex->login,
                    'password' => $centrex->getDecryptedPassword(),
                ],
                'headers' => [
                    'Referer' => "http://{$centrex->ip_address}/admin/",
                    'Origin' => "http://{$centrex->ip_address}",
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
            ]);

            // Vérifier si la connexion a réussi (pas de formulaire de login dans la réponse)
            $body = (string) $response->getBody();
            $isLoggedIn = !str_contains($body, 'id="loginform"') && !str_contains($body, 'id="login_form"');

            if ($isLoggedIn) {
                Log::info("FreePBX Login successful for centrex {$centrex->id}");
                $this->saveCookieJar($centrex->id, $cookieJar);
                $this->setAuthenticated($centrex->id, true);
                return true;
            }

            Log::warning("FreePBX Login failed for centrex {$centrex->id} - login form still present");
            return false;

        } catch (\Exception $e) {
            Log::error("FreePBX Login error for centrex {$centrex->id}: " . $e->getMessage());
            return false;
        }
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

        // Récupérer le CookieJar de la session
        $cookieJar = $this->getCookieJar($centrex->id);

        // Si pas encore authentifié, se connecter d'abord
        if (!$this->isAuthenticated($centrex->id)) {
            if (!$this->loginToFreePBX($centrex, $cookieJar)) {
                return response('Erreur de connexion au FreePBX', 401);
            }
            // Recharger le cookie jar après login
            $cookieJar = $this->getCookieJar($centrex->id);
        }

        // Construire l'URL cible
        $path = $any ? '/' . $any : '/';

        // Nettoyer le path si il contient accidentellement le chemin du proxy
        $proxyPattern = '/^\/?(client\/centrex\/\d+\/proxy\/?)/';
        $path = preg_replace($proxyPattern, '/', $path);

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
            // Créer le client Guzzle SANS Basic Auth (on utilise la session)
            $client = new Client([
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
            ]);

            // Préparer les options de requête
            $options = [];

            // Exécuter la requête selon la méthode HTTP
            $method = strtoupper($request->method());

            if ($method === 'POST') {
                $options['form_params'] = $request->all();
            }

            $response = $client->request($method, $targetUrl, $options);

            // Sauvegarder les cookies mis à jour
            $this->saveCookieJar($centrex->id, $cookieJar);

            $body = (string) $response->getBody();
            $contentType = $response->getHeaderLine('Content-Type') ?: 'text/html';

            // Vérifier si on a été déconnecté (formulaire de login présent)
            if (str_contains($contentType, 'text/html') &&
                (str_contains($body, 'id="loginform"') || str_contains($body, 'id="login_form"'))) {
                // Session expirée, se reconnecter
                Log::info("Session expired for centrex {$centrex->id}, re-authenticating...");
                $this->setAuthenticated($centrex->id, false);

                // Réessayer la requête
                return $this->proxy($request, $centrex, $any);
            }

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

            // Si 401 ou 403, essayer de se reconnecter
            if (in_array($statusCode, [401, 403])) {
                Log::info("Got {$statusCode} for centrex {$centrex->id}, re-authenticating...");
                $this->setAuthenticated($centrex->id, false);

                // Réessayer la requête (une seule fois)
                if (!session("centrex_retry_{$centrex->id}")) {
                    session(["centrex_retry_{$centrex->id}" => true]);
                    $result = $this->proxy($request, $centrex, $any);
                    session()->forget("centrex_retry_{$centrex->id}");
                    return $result;
                }
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
            var adminBase = proxyBase + "/admin/";

            function rewriteUrl(url) {
                if (typeof url !== "string") return url;
                // Ne pas réécrire si l'URL contient déjà le proxyBase
                if (url.indexOf(proxyBase) !== -1) return url;
                // Ne pas réécrire les URLs absolues externes
                if (url.startsWith("http://") || url.startsWith("https://")) return url;
                // Réécrire les URLs absolues (commençant par /)
                if (url.startsWith("/")) {
                    return proxyBase + url;
                }
                // Réécrire les URLs relatives (ajax.php, config.php, etc.)
                if (url.match(/^[a-zA-Z0-9_-]+\.php/) || url.match(/^[a-zA-Z0-9_-]+\//)) {
                    return adminBase + url;
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
