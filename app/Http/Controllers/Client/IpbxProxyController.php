<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Ipbx;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

class IpbxProxyController extends Controller
{
    /**
     * Vérifier que le client a accès à l'IPBX
     */
    private function checkAccess(Ipbx $ipbx): void
    {
        $client = Auth::user()->client;

        if (!$client->ipbx->contains($ipbx->id)) {
            abort(403, 'Vous n\'avez pas accès à cet IPBX.');
        }

        if (!$ipbx->is_active) {
            abort(403, 'Cet IPBX n\'est pas disponible actuellement.');
        }
    }

    /**
     * Récupérer ou créer le CookieJar pour cet IPBX
     */
    private function getCookieJar(int $ipbxId): CookieJar
    {
        $sessionKey = "ipbx_cookies_{$ipbxId}";
        $cookieData = session($sessionKey, []);

        return new CookieJar(false, $cookieData);
    }

    /**
     * Sauvegarder le CookieJar en session
     */
    private function saveCookieJar(int $ipbxId, CookieJar $jar): void
    {
        $sessionKey = "ipbx_cookies_{$ipbxId}";
        session([$sessionKey => $jar->toArray()]);
    }

    /**
     * Vérifier si on est authentifié sur l'IPBX
     */
    private function isAuthenticated(int $ipbxId): bool
    {
        return session("ipbx_logged_in_{$ipbxId}", false);
    }

    /**
     * Marquer comme authentifié
     */
    private function setAuthenticated(int $ipbxId, bool $value = true): void
    {
        session(["ipbx_logged_in_{$ipbxId}" => $value]);
    }

    /**
     * Se connecter au FreePBX (IPBX) via le formulaire de login
     */
    private function loginToFreePBX(Ipbx $ipbx, CookieJar $cookieJar): bool
    {
        // Si pas de login/password configuré, on considère l'auth OK
        if (empty($ipbx->login) || empty($ipbx->getDecryptedPassword())) {
            Log::info("IPBX {$ipbx->id}: No credentials configured, skipping auto-login");
            $this->setAuthenticated($ipbx->id, true);
            return true;
        }

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
            $client->get("https://{$ipbx->ip_address}:{$ipbx->port}/admin/");

            // 2. Soumettre le formulaire de login
            $response = $client->post("https://{$ipbx->ip_address}:{$ipbx->port}/admin/config.php", [
                'form_params' => [
                    'username' => $ipbx->login,
                    'password' => $ipbx->getDecryptedPassword(),
                ],
                'headers' => [
                    'Referer' => "https://{$ipbx->ip_address}:{$ipbx->port}/admin/",
                    'Origin' => "https://{$ipbx->ip_address}:{$ipbx->port}",
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
            ]);

            // Vérifier si la connexion a réussi (pas de formulaire de login dans la réponse)
            $body = (string) $response->getBody();
            $isLoggedIn = !str_contains($body, 'id="loginform"') && !str_contains($body, 'id="login_form"');

            if ($isLoggedIn) {
                Log::info("FreePBX Login successful for IPBX {$ipbx->id}");
                $this->saveCookieJar($ipbx->id, $cookieJar);
                $this->setAuthenticated($ipbx->id, true);
                return true;
            }

            Log::warning("FreePBX Login failed for IPBX {$ipbx->id} - login form still present");
            return false;

        } catch (\Exception $e) {
            Log::error("FreePBX Login error for IPBX {$ipbx->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Afficher la page avec iframe de l'IPBX
     */
    public function show(Request $request, Ipbx $ipbx)
    {
        $this->checkAccess($ipbx);

        return view('client.ipbx-view', compact('ipbx'));
    }

    /**
     * Proxifier les requêtes vers FreePBX (IPBX)
     */
    public function proxy(Request $request, Ipbx $ipbx, $any = null)
    {
        $this->checkAccess($ipbx);

        // Récupérer le CookieJar de la session
        $cookieJar = $this->getCookieJar($ipbx->id);

        // Si pas encore authentifié, se connecter d'abord
        if (!$this->isAuthenticated($ipbx->id)) {
            if (!$this->loginToFreePBX($ipbx, $cookieJar)) {
                return response('Erreur de connexion au FreePBX', 401);
            }
            // Recharger le cookie jar après login
            $cookieJar = $this->getCookieJar($ipbx->id);
        }

        // Construire l'URL cible
        $path = $any ? '/' . $any : '/';

        // Nettoyer le path si il contient accidentellement le chemin du proxy
        $proxyPattern = '/^\/?(client\/ipbx\/\d+\/proxy\/?)/';
        $path = preg_replace($proxyPattern, '/', $path);

        $queryString = $request->getQueryString();
        if ($queryString) {
            $path .= '?' . $queryString;
        }

        $targetUrl = "https://{$ipbx->ip_address}:{$ipbx->port}" . $path;

        // Corriger les URLs POST vers /admin?... pour utiliser /admin/config.php?...
        if ($request->method() === 'POST' && preg_match('/^\/admin\/?(\?.*)?$/', $path)) {
            $queryPart = $queryString ? '?' . $queryString : '';
            $targetUrl = "https://{$ipbx->ip_address}:{$ipbx->port}/admin/config.php" . $queryPart;
            Log::debug('Proxy IPBX: Fixed POST URL to use config.php', ['original' => $path, 'fixed' => "/admin/config.php{$queryPart}"]);
        }

        if (config('app.debug')) {
            Log::debug('Proxy IPBX Request:', [
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
                'allow_redirects' => false,
                'cookies' => $cookieJar,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Referer' => "https://{$ipbx->ip_address}:{$ipbx->port}/admin/",
                    'Origin' => "https://{$ipbx->ip_address}:{$ipbx->port}",
                ],
            ]);

            // Préparer les options de requête
            $options = [];

            // Exécuter la requête selon la méthode HTTP
            $method = strtoupper($request->method());

            if ($method === 'POST') {
                $contentType = $request->header('Content-Type', '');

                if (str_contains($contentType, 'multipart/form-data') || $request->hasFile('file') || count($request->allFiles()) > 0) {
                    $multipart = [];

                    $flattenArray = function($array, $prefix = '') use (&$flattenArray, &$multipart) {
                        foreach ($array as $key => $value) {
                            $name = $prefix ? "{$prefix}[{$key}]" : $key;
                            if (is_array($value)) {
                                $flattenArray($value, $name);
                            } else {
                                $multipart[] = [
                                    'name' => $name,
                                    'contents' => (string) $value,
                                ];
                            }
                        }
                    };

                    $flattenArray($request->all());

                    foreach ($request->allFiles() as $key => $files) {
                        $files = is_array($files) ? $files : [$files];
                        foreach ($files as $index => $file) {
                            if ($file && $file->isValid()) {
                                $multipart[] = [
                                    'name' => is_array($request->file($key)) ? "{$key}[{$index}]" : $key,
                                    'contents' => fopen($file->getRealPath(), 'r'),
                                    'filename' => $file->getClientOriginalName(),
                                ];
                            }
                        }
                    }

                    if (!empty($multipart)) {
                        $options['multipart'] = $multipart;
                    } else {
                        $options['form_params'] = $request->all();
                    }
                } else {
                    $options['form_params'] = $request->all();
                }
            }

            $response = $client->request($method, $targetUrl, $options);

            // Sauvegarder les cookies mis à jour
            $this->saveCookieJar($ipbx->id, $cookieJar);

            $statusCode = $response->getStatusCode();

            // Gérer les redirections
            if (in_array($statusCode, [301, 302, 303, 307, 308])) {
                $location = $response->getHeaderLine('Location');
                if ($location) {
                    $appUrl = rtrim(config('app.url'), '/');
                    $proxyBase = "{$appUrl}/client/ipbx/{$ipbx->id}/proxy";

                    if (str_starts_with($location, 'http://') || str_starts_with($location, 'https://')) {
                        $fullLocation = $location;
                    } elseif (str_starts_with($location, '/')) {
                        $fullLocation = "https://{$ipbx->ip_address}:{$ipbx->port}" . $location;
                    } else {
                        $fullLocation = "https://{$ipbx->ip_address}:{$ipbx->port}/admin/" . $location;
                    }

                    $newLocation = preg_replace(
                        '/https?:\/\/' . preg_quote($ipbx->ip_address, '/') . '(:\d+)?/',
                        $proxyBase,
                        $fullLocation
                    );

                    if ($method === 'POST') {
                        return redirect($newLocation);
                    }

                    // Pour GET, suivre côté serveur
                    $maxRedirects = 5;
                    $redirectCount = 0;
                    $currentUrl = $fullLocation;

                    while ($redirectCount < $maxRedirects) {
                        $response = $client->request('GET', $currentUrl, []);
                        $statusCode = $response->getStatusCode();
                        $this->saveCookieJar($ipbx->id, $cookieJar);

                        if (!in_array($statusCode, [301, 302, 303, 307, 308])) {
                            break;
                        }

                        $nextLocation = $response->getHeaderLine('Location');
                        if (!$nextLocation) {
                            break;
                        }

                        if (str_starts_with($nextLocation, 'http://') || str_starts_with($nextLocation, 'https://')) {
                            $currentUrl = $nextLocation;
                        } elseif (str_starts_with($nextLocation, '/')) {
                            $currentUrl = "https://{$ipbx->ip_address}:{$ipbx->port}" . $nextLocation;
                        } else {
                            $currentUrl = "https://{$ipbx->ip_address}:{$ipbx->port}/admin/" . $nextLocation;
                        }

                        $redirectCount++;
                    }
                }
            }

            $body = (string) $response->getBody();
            $contentType = $response->getHeaderLine('Content-Type') ?: 'text/html';

            // Vérifier si on a été déconnecté
            if (str_contains($contentType, 'text/html') &&
                (str_contains($body, 'id="loginform"') || str_contains($body, 'id="login_form"'))) {
                Log::info("Session expired for IPBX {$ipbx->id}, re-authenticating...");
                $this->setAuthenticated($ipbx->id, false);
                return $this->proxy($request, $ipbx, $any);
            }

            // Assets
            if ($this->isAsset($path, $contentType)) {
                if (str_contains($path, 'ivr.js')) {
                    $body = str_replace(
                        'var res = id.split("goto");',
                        'if (!id) return; var res = id.split("goto");',
                        $body
                    );
                }

                return response($body, $response->getStatusCode())
                    ->withHeaders([
                        'Content-Type' => $contentType,
                        'Cache-Control' => 'public, max-age=86400',
                    ]);
            }

            // HTML : réécrire les URLs
            if (str_contains($contentType, 'text/html')) {
                $body = $this->rewriteHtml($body, $ipbx->id, $ipbx->ip_address, $ipbx->port);
            }

            return response($body, $response->getStatusCode())
                ->withHeaders([
                    'Content-Type' => $contentType,
                    'X-Frame-Options' => 'SAMEORIGIN',
                ]);

        } catch (\GuzzleHttp\Exception\ServerException $e) {
            $response = $e->getResponse();
            $statusCode = $response ? $response->getStatusCode() : 500;
            $body = $response ? (string) $response->getBody() : '';

            Log::error('Proxy IPBX: Server Error', [
                'status' => $statusCode,
                'url' => $targetUrl,
            ]);

            if ($response) {
                $contentType = $response->getHeaderLine('Content-Type') ?: 'text/html';
                if (str_contains($contentType, 'text/html')) {
                    $body = $this->rewriteHtml($body, $ipbx->id, $ipbx->ip_address, $ipbx->port);
                }
                return response($body, $statusCode)
                    ->withHeaders(['Content-Type' => $contentType]);
            }

            return response('Erreur serveur IPBX: ' . $e->getMessage(), 500);

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response ? $response->getStatusCode() : 500;

            if (in_array($statusCode, [401, 403])) {
                Log::info("Got {$statusCode} for IPBX {$ipbx->id}, re-authenticating...");
                $this->setAuthenticated($ipbx->id, false);

                if (!session("ipbx_retry_{$ipbx->id}")) {
                    session(["ipbx_retry_{$ipbx->id}" => true]);
                    $result = $this->proxy($request, $ipbx, $any);
                    session()->forget("ipbx_retry_{$ipbx->id}");
                    return $result;
                }
            }

            if ($response) {
                $body = (string) $response->getBody();
                $contentType = $response->getHeaderLine('Content-Type') ?: 'text/html';

                if (str_contains($contentType, 'text/html')) {
                    $body = $this->rewriteHtml($body, $ipbx->id, $ipbx->ip_address, $ipbx->port);
                }

                return response($body, $statusCode)
                    ->withHeaders(['Content-Type' => $contentType]);
            }

            return response('Erreur: Ressource non trouvée', $statusCode);

        } catch (\Exception $e) {
            Log::error('Proxy IPBX: Exception', [
                'message' => $e->getMessage(),
                'url' => $targetUrl,
            ]);

            return response('Erreur de connexion à l\'IPBX: ' . $e->getMessage(), 502);
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
    private function rewriteHtml(string $body, int $ipbxId, string $ipbxIp, int $ipbxPort): string
    {
        $appUrl = rtrim(config('app.url'), '/');
        $proxyBase = "{$appUrl}/client/ipbx/{$ipbxId}/proxy";

        // Réécrire les URLs absolues contenant l'IP de l'IPBX
        $body = preg_replace('/https?:\/\/' . preg_quote($ipbxIp, '/') . '(:\d+)?/i', $proxyBase, $body);

        // Réécrire les chemins absolus
        $body = preg_replace('/href=["\']\/((?!http)[^"\']*)["\']/i', 'href="' . $proxyBase . '/$1"', $body);
        $body = preg_replace('/src=["\']\/((?!http)[^"\']*)["\']/i', 'src="' . $proxyBase . '/$1"', $body);

        // Réécrire les actions de formulaire
        $body = preg_replace('/action=["\']\/admin\?([^"\']*)["\']/i', 'action="' . $proxyBase . '/admin/config.php?$1"', $body);
        $body = preg_replace('/action=["\']\/admin\/\?([^"\']*)["\']/i', 'action="' . $proxyBase . '/admin/config.php?$1"', $body);
        $body = preg_replace('/action=["\']\/((?!http|admin\?|admin\/\?)[^"\']*)["\']/i', 'action="' . $proxyBase . '/$1"', $body);

        // Supprimer toutes les balises <base> existantes
        $body = preg_replace('/<base[^>]*>/i', '', $body);

        // Ajouter notre balise base
        $baseTag = '<base href="' . $proxyBase . '/admin/">';
        $body = preg_replace('/<head>/i', '<head>' . $baseTag, $body, 1);

        // Injecter l'intercepteur AJAX/Fetch
        $ajaxInterceptor = <<<JS
        <script>
        (function() {
            var proxyBase = "{$proxyBase}";
            var adminBase = proxyBase + "/admin/";
            var ipbxIp = "{$ipbxIp}";

            function rewriteUrl(url) {
                if (typeof url !== "string") return url;
                if (url.indexOf(proxyBase) !== -1) return url;
                if (url.indexOf(ipbxIp) !== -1) {
                    try {
                        var urlObj = new URL(url);
                        return proxyBase + urlObj.pathname + urlObj.search;
                    } catch(e) {
                        return url.replace(new RegExp('https?://' + ipbxIp.replace(/\./g, '\\.') + '(:\\d+)?', 'gi'), proxyBase);
                    }
                }
                if (url.startsWith("http://") || url.startsWith("https://")) {
                    try {
                        var urlObj = new URL(url);
                        if (urlObj.hostname.match(/^(192\.168\.|10\.|172\.(1[6-9]|2[0-9]|3[01])\.|127\.|localhost)/)) {
                            return proxyBase + urlObj.pathname + urlObj.search;
                        }
                    } catch(e) {}
                    return url;
                }
                if (url.startsWith("/")) {
                    return proxyBase + url;
                }
                if (url.match(/^[a-zA-Z0-9_-]+\.php/) || url.match(/^[a-zA-Z0-9_-]+\//)) {
                    return adminBase + url;
                }
                if (url.startsWith("?")) {
                    return adminBase + "config.php" + url;
                }
                return url;
            }

            var origOpen = XMLHttpRequest.prototype.open;
            XMLHttpRequest.prototype.open = function(method, url) {
                var newUrl = rewriteUrl(url);
                return origOpen.apply(this, [method, newUrl]);
            };

            var origFetch = window.fetch;
            window.fetch = function(url, options) {
                var newUrl = rewriteUrl(url);
                return origFetch.apply(this, [newUrl, options]);
            };

            var origAssign = window.location.assign;
            var origReplace = window.location.replace;

            window.location.assign = function(url) {
                return origAssign.call(window.location, rewriteUrl(url));
            };

            window.location.replace = function(url) {
                return origReplace.call(window.location, rewriteUrl(url));
            };

            document.addEventListener('click', function(e) {
                var link = e.target.closest('a');
                if (link) {
                    var href = link.getAttribute('href');
                    if (!href || href === '#' || href.startsWith('javascript:')) return;

                    var newHref = rewriteUrl(href);
                    if (newHref !== href) {
                        e.preventDefault();
                        e.stopPropagation();
                        origAssign.call(window.location, newHref);
                    }
                }
            }, true);

            document.addEventListener('submit', function(e) {
                var form = e.target;
                if (form) {
                    var action = form.getAttribute('action') || '';
                    var newAction = action;

                    if (action.match(/^\/admin\?/) || action.match(/^\/admin\/\?/)) {
                        newAction = proxyBase + '/admin/config.php?' + action.split('?')[1];
                    } else if (action.startsWith('?')) {
                        newAction = adminBase + 'config.php' + action;
                    } else if (action) {
                        newAction = rewriteUrl(action);
                    } else {
                        return;
                    }

                    if (newAction !== action) {
                        form.action = newAction;
                    }
                }
            }, true);

            console.log('[Proxy IPBX] Intercepteurs installes - proxyBase:', proxyBase);
        })();
        </script>
        JS;

        $body = preg_replace('/<\/head>/i', $ajaxInterceptor . '</head>', $body, 1);

        return $body;
    }
}
