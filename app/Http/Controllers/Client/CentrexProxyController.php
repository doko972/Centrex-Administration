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

        // Corriger les URLs POST vers /admin?... pour utiliser /admin/config.php?...
        // Cela évite la redirection 301 qui perd les données POST
        if ($request->method() === 'POST' && preg_match('/^\/admin\/?(\?.*)?$/', $path)) {
            $queryPart = $queryString ? '?' . $queryString : '';
            $targetUrl = "http://{$centrex->ip_address}/admin/config.php" . $queryPart;
            Log::debug('Proxy: Fixed POST URL to use config.php', ['original' => $path, 'fixed' => "/admin/config.php{$queryPart}"]);
        }

        if (config('app.debug')) {
            Log::debug('Proxy Request:', [
                'method' => $request->method(),
                'targetUrl' => $targetUrl,
            ]);
        }

        try {
            // Créer le client Guzzle SANS Basic Auth (on utilise la session)
            // IMPORTANT: Ne pas suivre les redirections automatiquement pour pouvoir les réécrire
            $client = new Client([
                'verify' => false,
                'timeout' => 60,
                'connect_timeout' => 10,
                'allow_redirects' => false, // On gère les redirections manuellement
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
                $contentType = $request->header('Content-Type', '');

                // Vérifier si c'est un formulaire multipart (avec fichiers)
                if (str_contains($contentType, 'multipart/form-data') || $request->hasFile('file') || count($request->allFiles()) > 0) {
                    // Construire les données multipart avec support des tableaux imbriqués
                    $multipart = [];

                    // Fonction récursive pour aplatir les tableaux imbriqués
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

                    // Ajouter tous les fichiers
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
                        Log::debug('Proxy: Using multipart form data', ['fields' => count($multipart)]);
                    } else {
                        $options['form_params'] = $request->all();
                    }
                } else {
                    // Formulaire standard - form_params gère les tableaux imbriqués automatiquement
                    $options['form_params'] = $request->all();
                    Log::debug('Proxy: POST form_params', ['params' => array_keys($request->all())]);

                    // Debug spécifique pour IVR
                    if (str_contains($targetUrl, 'display=ivr')) {
                        $entries = $request->input('entries');
                        Log::debug('Proxy: IVR entries structure', [
                            'entries_type' => gettype($entries),
                            'entries_keys' => is_array($entries) ? array_keys($entries) : 'not_array',
                            'entries_preview' => is_array($entries) ? json_encode($entries) : $entries,
                        ]);
                    }
                }
            }

            // Logger les détails pour debug
            if ($method === 'POST' && config('app.debug')) {
                Log::debug('Proxy POST Details:', [
                    'url' => $targetUrl,
                    'content_type' => $request->header('Content-Type'),
                    'has_files' => count($request->allFiles()) > 0,
                    'param_keys' => array_keys($request->all()),
                ]);
            }

            $response = $client->request($method, $targetUrl, $options);

            // Sauvegarder les cookies mis à jour
            $this->saveCookieJar($centrex->id, $cookieJar);

            $statusCode = $response->getStatusCode();

            // Logger le status code pour debug
            if ($method === 'POST') {
                Log::debug('Proxy POST Response:', [
                    'url' => $targetUrl,
                    'status' => $statusCode,
                    'hasLocation' => $response->hasHeader('Location'),
                    'location' => $response->getHeaderLine('Location'),
                ]);
            }

            // Gérer les redirections (301, 302, 303, 307, 308)
            // Pour les GET: suivre côté serveur
            // Pour les POST: renvoyer au navigateur pour préserver les données de session FreePBX
            if (in_array($statusCode, [301, 302, 303, 307, 308])) {
                $location = $response->getHeaderLine('Location');
                if ($location) {
                    $appUrl = rtrim(config('app.url'), '/');
                    $proxyBase = "{$appUrl}/client/centrex/{$centrex->id}/proxy";

                    // Construire l'URL complète pour FreePBX
                    if (str_starts_with($location, 'http://') || str_starts_with($location, 'https://')) {
                        $fullLocation = $location;
                    } elseif (str_starts_with($location, '/')) {
                        $fullLocation = "http://{$centrex->ip_address}" . $location;
                    } else {
                        $fullLocation = "http://{$centrex->ip_address}/admin/" . $location;
                    }

                    // Construire l'URL de redirection réécrite pour le navigateur
                    $newLocation = preg_replace(
                        '/https?:\/\/' . preg_quote($centrex->ip_address, '/') . '(:\d+)?/',
                        $proxyBase,
                        $fullLocation
                    );

                    Log::debug('Proxy Redirect:', [
                        'method' => $method,
                        'original' => $location,
                        'fullLocation' => $fullLocation,
                        'rewritten' => $newLocation,
                    ]);

                    // Pour POST, renvoyer la redirection au navigateur
                    if ($method === 'POST') {
                        return redirect($newLocation);
                    }

                    // Pour GET, suivre côté serveur pour éviter les boucles de redirection navigateur
                    $maxRedirects = 5;
                    $redirectCount = 0;
                    $currentUrl = $fullLocation;

                    while ($redirectCount < $maxRedirects) {
                        $response = $client->request('GET', $currentUrl, []);
                        $statusCode = $response->getStatusCode();
                        $this->saveCookieJar($centrex->id, $cookieJar);

                        if (!in_array($statusCode, [301, 302, 303, 307, 308])) {
                            break;
                        }

                        $nextLocation = $response->getHeaderLine('Location');
                        if (!$nextLocation) {
                            break;
                        }

                        // Construire l'URL complète
                        if (str_starts_with($nextLocation, 'http://') || str_starts_with($nextLocation, 'https://')) {
                            $currentUrl = $nextLocation;
                        } elseif (str_starts_with($nextLocation, '/')) {
                            $currentUrl = "http://{$centrex->ip_address}" . $nextLocation;
                        } else {
                            $currentUrl = "http://{$centrex->ip_address}/admin/" . $nextLocation;
                        }

                        $redirectCount++;
                        Log::debug('Proxy Following GET Redirect:', ['to' => $currentUrl, 'count' => $redirectCount]);
                    }
                }
            }

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

            // Assets (JS, CSS, images, fonts) : retourner avec corrections si nécessaire
            if ($this->isAsset($path, $contentType)) {
                // Fix pour ivr.js - ajouter une vérification de sécurité pour éviter le crash
                if (str_contains($path, 'ivr.js')) {
                    // Approche simple: remplacer "var res = id.split" par une version sécurisée
                    $body = str_replace(
                        'var res = id.split("goto");',
                        'if (!id) return; var res = id.split("goto");',
                        $body
                    );
                    Log::debug('Proxy: Applied ivr.js safety fix');
                }

                return response($body, $response->getStatusCode())
                    ->withHeaders([
                        'Content-Type' => $contentType,
                        'Cache-Control' => 'public, max-age=86400',
                    ]);
            }

            // HTML : réécrire les URLs et injecter l'intercepteur AJAX
            if (str_contains($contentType, 'text/html')) {
                $body = $this->rewriteHtml($body, $centrex->id, $centrex->ip_address);
            }

            return response($body, $response->getStatusCode())
                ->withHeaders([
                    'Content-Type' => $contentType,
                    'X-Frame-Options' => 'SAMEORIGIN',
                ]);

        } catch (\GuzzleHttp\Exception\ServerException $e) {
            // Erreur 5xx du serveur FreePBX
            $response = $e->getResponse();
            $statusCode = $response ? $response->getStatusCode() : 500;
            $body = $response ? (string) $response->getBody() : '';

            Log::error('Proxy: FreePBX Server Error', [
                'status' => $statusCode,
                'url' => $targetUrl,
                'response_preview' => substr($body, 0, 500),
            ]);

            // Retourner la réponse d'erreur de FreePBX (peut contenir des infos utiles)
            if ($response) {
                $contentType = $response->getHeaderLine('Content-Type') ?: 'text/html';
                if (str_contains($contentType, 'text/html')) {
                    $body = $this->rewriteHtml($body, $centrex->id, $centrex->ip_address);
                }
                return response($body, $statusCode)
                    ->withHeaders(['Content-Type' => $contentType]);
            }

            return response('Erreur serveur FreePBX: ' . $e->getMessage(), 500);

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
                    $body = $this->rewriteHtml($body, $centrex->id, $centrex->ip_address);
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
    private function rewriteHtml(string $body, int $centrexId, string $centrexIp): string
    {
        // Utiliser APP_URL pour éviter de prendre l'IP du FreePBX comme host
        $appUrl = rtrim(config('app.url'), '/');
        $proxyBase = "{$appUrl}/client/centrex/{$centrexId}/proxy";

        // Réécrire les URLs absolues contenant l'IP du FreePBX
        $body = preg_replace('/https?:\/\/' . preg_quote($centrexIp, '/') . '(:\d+)?/i', $proxyBase, $body);

        // Réécrire les chemins absolus (href, src, action)
        $body = preg_replace('/href=["\']\/((?!http)[^"\']*)["\']/i', 'href="' . $proxyBase . '/$1"', $body);
        $body = preg_replace('/src=["\']\/((?!http)[^"\']*)["\']/i', 'src="' . $proxyBase . '/$1"', $body);

        // Pour les actions de formulaire, s'assurer qu'on utilise config.php pour éviter les redirections 301
        // /admin?display=xxx -> /proxy/admin/config.php?display=xxx
        $body = preg_replace('/action=["\']\/admin\?([^"\']*)["\']/i', 'action="' . $proxyBase . '/admin/config.php?$1"', $body);
        $body = preg_replace('/action=["\']\/admin\/\?([^"\']*)["\']/i', 'action="' . $proxyBase . '/admin/config.php?$1"', $body);
        // Autres actions
        $body = preg_replace('/action=["\']\/((?!http|admin\?|admin\/\?)[^"\']*)["\']/i', 'action="' . $proxyBase . '/$1"', $body);

        // Supprimer toutes les balises <base> existantes
        $body = preg_replace('/<base[^>]*>/i', '', $body);

        // Ajouter notre balise base
        $baseTag = '<base href="' . $proxyBase . '/admin/">';
        $body = preg_replace('/<head>/i', '<head>' . $baseTag, $body, 1);

        // Injecter l'intercepteur AJAX/Fetch et redirections
        $ajaxInterceptor = <<<JS
        <script>
        (function() {
            var proxyBase = "{$proxyBase}";
            var adminBase = proxyBase + "/admin/";
            var freepbxIp = "{$centrexIp}";

            function rewriteUrl(url) {
                if (typeof url !== "string") return url;
                // Ne pas réécrire si l'URL contient déjà le proxyBase
                if (url.indexOf(proxyBase) !== -1) return url;
                // Réécrire si l'URL contient l'IP du FreePBX
                if (url.indexOf(freepbxIp) !== -1) {
                    try {
                        var urlObj = new URL(url);
                        return proxyBase + urlObj.pathname + urlObj.search;
                    } catch(e) {
                        return url.replace(new RegExp('https?://' + freepbxIp.replace(/\./g, '\\.') + '(:\\d+)?', 'gi'), proxyBase);
                    }
                }
                // Ne pas réécrire les URLs absolues externes (sauf si c'est une IP privée/localhost)
                if (url.startsWith("http://") || url.startsWith("https://")) {
                    // Extraire le hostname de l'URL
                    try {
                        var urlObj = new URL(url);
                        // Si c'est une IP privée ou localhost, c'est probablement le FreePBX
                        if (urlObj.hostname.match(/^(192\.168\.|10\.|172\.(1[6-9]|2[0-9]|3[01])\.|127\.|localhost)/)) {
                            return proxyBase + urlObj.pathname + urlObj.search;
                        }
                    } catch(e) {}
                    return url;
                }
                // Réécrire les URLs absolues (commençant par /)
                if (url.startsWith("/")) {
                    return proxyBase + url;
                }
                // Réécrire les URLs relatives (ajax.php, config.php, etc.)
                if (url.match(/^[a-zA-Z0-9_-]+\.php/) || url.match(/^[a-zA-Z0-9_-]+\//)) {
                    return adminBase + url;
                }
                // Réécrire les query strings seules (?display=xxx)
                if (url.startsWith("?")) {
                    return adminBase + "config.php" + url;
                }
                return url;
            }

            // Intercepter XMLHttpRequest
            var origOpen = XMLHttpRequest.prototype.open;
            XMLHttpRequest.prototype.open = function(method, url) {
                var newUrl = rewriteUrl(url);
                if (newUrl !== url) {
                    console.log('[Proxy XHR]', method, url, '->', newUrl);
                }
                return origOpen.apply(this, [method, newUrl]);
            };

            // Intercepter fetch
            var origFetch = window.fetch;
            window.fetch = function(url, options) {
                var newUrl = rewriteUrl(url);
                if (newUrl !== url) {
                    console.log('[Proxy Fetch]', url, '->', newUrl);
                }
                return origFetch.apply(this, [newUrl, options]);
            };

            // Intercepter window.location.href et window.location.assign
            var locationDescriptor = Object.getOwnPropertyDescriptor(window, 'location');
            if (locationDescriptor && locationDescriptor.configurable !== false) {
                // Créer un proxy pour location
                var origLocation = window.location;
                var locationProxy = new Proxy(origLocation, {
                    set: function(target, prop, value) {
                        if (prop === 'href' || prop === 'pathname') {
                            value = rewriteUrl(value);
                        }
                        target[prop] = value;
                        return true;
                    }
                });
            }

            // Intercepter location.assign et location.replace
            var origAssign = window.location.assign;
            var origReplace = window.location.replace;

            window.location.assign = function(url) {
                return origAssign.call(window.location, rewriteUrl(url));
            };

            window.location.replace = function(url) {
                return origReplace.call(window.location, rewriteUrl(url));
            };

            // Intercepter les liens cliqués dynamiquement
            document.addEventListener('click', function(e) {
                var link = e.target.closest('a');
                if (link) {
                    var href = link.getAttribute('href');
                    // Ignorer les liens sans href ou avec href="#" ou javascript:
                    if (!href || href === '#' || href.startsWith('javascript:')) return;

                    var newHref = rewriteUrl(href);
                    if (newHref !== href) {
                        e.preventDefault();
                        e.stopPropagation();
                        console.log('[Proxy] Réécriture lien:', href, '->', newHref);
                        origAssign.call(window.location, newHref);
                    }
                }
            }, true);

            // Intercepter les soumissions de formulaire
            document.addEventListener('submit', function(e) {
                var form = e.target;
                if (form) {
                    var action = form.getAttribute('action') || '';
                    var newAction = action;

                    // Cas spécial: /admin?... ou ?... doit devenir /admin/config.php?...
                    if (action.match(/^\/admin\?/) || action.match(/^\/admin\/\?/)) {
                        newAction = proxyBase + '/admin/config.php?' + action.split('?')[1];
                    } else if (action.startsWith('?')) {
                        newAction = adminBase + 'config.php' + action;
                    } else if (action) {
                        newAction = rewriteUrl(action);
                    } else {
                        // Pas d'action = soumettre à l'URL actuelle, qui devrait être correcte
                        return;
                    }

                    if (newAction !== action) {
                        console.log('[Proxy Form]', action, '->', newAction);
                        form.action = newAction;
                    }
                }
            }, true);

            // Créer une fausse location pour tromper les scripts qui lisent window.location
            var proxyUrl = new URL(proxyBase);
            var fakeHost = proxyUrl.host;
            var fakeHostname = proxyUrl.hostname;
            var fakeOrigin = proxyUrl.origin;

            // Stocker les valeurs originales pour référence
            var realHost = window.location.host;
            var realHostname = window.location.hostname;

            // Debug: Logger les redirections
            console.log('[Proxy] Intercepteurs installés - proxyBase:', proxyBase, 'freepbxIp:', freepbxIp);
        })();
        </script>
        JS;

        $body = preg_replace('/<\/head>/i', $ajaxInterceptor . '</head>', $body, 1);

        return $body;
    }
}
