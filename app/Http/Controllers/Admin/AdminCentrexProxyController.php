<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Centrex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

class AdminCentrexProxyController extends Controller
{
    /**
     * Récupérer ou créer le CookieJar pour ce centrex
     */
    private function getCookieJar(int $centrexId): CookieJar
    {
        $sessionKey = "admin_centrex_cookies_{$centrexId}";
        $cookieData = session($sessionKey, []);

        return new CookieJar(false, $cookieData);
    }

    /**
     * Sauvegarder le CookieJar en session
     */
    private function saveCookieJar(int $centrexId, CookieJar $jar): void
    {
        $sessionKey = "admin_centrex_cookies_{$centrexId}";
        session([$sessionKey => $jar->toArray()]);
    }

    /**
     * Vérifier si on est authentifié sur le centrex
     */
    private function isAuthenticated(int $centrexId): bool
    {
        return session("admin_centrex_logged_in_{$centrexId}", false);
    }

    /**
     * Marquer comme authentifié
     */
    private function setAuthenticated(int $centrexId, bool $value = true): void
    {
        session(["admin_centrex_logged_in_{$centrexId}" => $value]);
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
            $client->get("http://{$centrex->ip_address}/admin/");

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

            $body = (string) $response->getBody();
            $isLoggedIn = !str_contains($body, 'id="loginform"') && !str_contains($body, 'id="login_form"');

            if ($isLoggedIn) {
                Log::info("Admin FreePBX Login successful for centrex {$centrex->id}");
                $this->saveCookieJar($centrex->id, $cookieJar);
                $this->setAuthenticated($centrex->id, true);
                return true;
            }

            Log::warning("Admin FreePBX Login failed for centrex {$centrex->id}");
            return false;

        } catch (\Exception $e) {
            Log::error("Admin FreePBX Login error for centrex {$centrex->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Afficher la page avec iframe du centrex
     */
    public function show(Request $request, Centrex $centrex)
    {
        return view('admin.centrex.view', compact('centrex'));
    }

    /**
     * Proxifier les requêtes vers FreePBX
     */
    public function proxy(Request $request, Centrex $centrex, $any = null)
    {
        $cookieJar = $this->getCookieJar($centrex->id);

        if (!$this->isAuthenticated($centrex->id)) {
            if (!$this->loginToFreePBX($centrex, $cookieJar)) {
                return response('Erreur de connexion au FreePBX', 401);
            }
            $cookieJar = $this->getCookieJar($centrex->id);
        }

        $path = $any ? '/' . $any : '/';

        $proxyPattern = '/^\/?(admin\/centrex\/\d+\/proxy\/?)/';
        $path = preg_replace($proxyPattern, '/', $path);

        $queryString = $request->getQueryString();
        if ($queryString) {
            $path .= '?' . $queryString;
        }

        $targetUrl = "http://{$centrex->ip_address}" . $path;

        // Corriger les URLs POST vers /admin?... pour utiliser /admin/config.php?...
        if ($request->method() === 'POST' && preg_match('/^\/admin\/?(\?.*)?$/', $path)) {
            $queryPart = $queryString ? '?' . $queryString : '';
            $targetUrl = "http://{$centrex->ip_address}/admin/config.php" . $queryPart;
        }

        if (config('app.debug')) {
            Log::debug('Admin Proxy Request:', [
                'method' => $request->method(),
                'targetUrl' => $targetUrl,
            ]);
        }

        try {
            $client = new Client([
                'verify' => false,
                'timeout' => 60,
                'connect_timeout' => 10,
                'allow_redirects' => false,
                'cookies' => $cookieJar,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Referer' => "http://{$centrex->ip_address}/admin/",
                    'Origin' => "http://{$centrex->ip_address}",
                ],
            ]);

            $options = [];
            $method = strtoupper($request->method());

            if ($method === 'POST') {
                $contentType = $request->header('Content-Type', '');

                if (str_contains($contentType, 'multipart/form-data') || count($request->allFiles()) > 0) {
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

                    // Fix IVR entries
                    if (str_contains($targetUrl, 'display=ivr') && isset($options['form_params']['entries'])) {
                        $entries = $options['form_params']['entries'];
                        if (isset($entries['ivr_ret']) && is_array($entries['ivr_ret'])) {
                            $ivrRet = array_values(array_filter($entries['ivr_ret'], function($v) {
                                return $v !== null;
                            }));
                            if (empty($ivrRet) && isset($entries['ext']) && is_array($entries['ext'])) {
                                $ivrRet = array_fill(0, count($entries['ext']), '0');
                            }
                            $options['form_params']['entries']['ivr_ret'] = $ivrRet;
                        }
                    }
                }
            }

            $response = $client->request($method, $targetUrl, $options);

            $this->saveCookieJar($centrex->id, $cookieJar);

            $statusCode = $response->getStatusCode();

            // Gérer les redirections
            if (in_array($statusCode, [301, 302, 303, 307, 308])) {
                $location = $response->getHeaderLine('Location');
                if ($location) {
                    $appUrl = rtrim(config('app.url'), '/');
                    $proxyBase = "{$appUrl}/admin/centrex/{$centrex->id}/proxy";

                    if (str_starts_with($location, 'http://') || str_starts_with($location, 'https://')) {
                        $fullLocation = $location;
                    } elseif (str_starts_with($location, '/')) {
                        $fullLocation = "http://{$centrex->ip_address}" . $location;
                    } else {
                        $fullLocation = "http://{$centrex->ip_address}/admin/" . $location;
                    }

                    $newLocation = preg_replace(
                        '/https?:\/\/' . preg_quote($centrex->ip_address, '/') . '(:\d+)?/',
                        $proxyBase,
                        $fullLocation
                    );

                    if ($method === 'POST') {
                        return redirect($newLocation);
                    }

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

                        if (str_starts_with($nextLocation, 'http://') || str_starts_with($nextLocation, 'https://')) {
                            $currentUrl = $nextLocation;
                        } elseif (str_starts_with($nextLocation, '/')) {
                            $currentUrl = "http://{$centrex->ip_address}" . $nextLocation;
                        } else {
                            $currentUrl = "http://{$centrex->ip_address}/admin/" . $nextLocation;
                        }

                        $redirectCount++;
                    }
                }
            }

            $body = (string) $response->getBody();
            $contentType = $response->getHeaderLine('Content-Type') ?: 'text/html';

            if (str_contains($contentType, 'text/html') &&
                (str_contains($body, 'id="loginform"') || str_contains($body, 'id="login_form"'))) {
                $this->setAuthenticated($centrex->id, false);
                return $this->proxy($request, $centrex, $any);
            }

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

            if (str_contains($contentType, 'text/html')) {
                $body = $this->rewriteHtml($body, $centrex->id, $centrex->ip_address);
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

            if (in_array($statusCode, [401, 403])) {
                $this->setAuthenticated($centrex->id, false);
                if (!session("admin_centrex_retry_{$centrex->id}")) {
                    session(["admin_centrex_retry_{$centrex->id}" => true]);
                    $result = $this->proxy($request, $centrex, $any);
                    session()->forget("admin_centrex_retry_{$centrex->id}");
                    return $result;
                }
            }

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
            Log::error('Admin Proxy: Exception', [
                'message' => $e->getMessage(),
                'url' => $targetUrl,
            ]);

            return response('Erreur de connexion au centrex: ' . $e->getMessage(), 502);
        }
    }

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

    private function rewriteHtml(string $body, int $centrexId, string $centrexIp): string
    {
        $appUrl = rtrim(config('app.url'), '/');
        $proxyBase = "{$appUrl}/admin/centrex/{$centrexId}/proxy";

        $body = preg_replace('/https?:\/\/' . preg_quote($centrexIp, '/') . '(:\d+)?/i', $proxyBase, $body);

        $body = preg_replace('/href=["\']\/((?!http)[^"\']*)["\']/i', 'href="' . $proxyBase . '/$1"', $body);
        $body = preg_replace('/src=["\']\/((?!http)[^"\']*)["\']/i', 'src="' . $proxyBase . '/$1"', $body);

        $body = preg_replace('/action=["\']\/admin\?([^"\']*)["\']/i', 'action="' . $proxyBase . '/admin/config.php?$1"', $body);
        $body = preg_replace('/action=["\']\/admin\/\?([^"\']*)["\']/i', 'action="' . $proxyBase . '/admin/config.php?$1"', $body);
        $body = preg_replace('/action=["\']\/((?!http|admin\?|admin\/\?)[^"\']*)["\']/i', 'action="' . $proxyBase . '/$1"', $body);

        $body = preg_replace('/<base[^>]*>/i', '', $body);

        $baseTag = '<base href="' . $proxyBase . '/admin/">';
        $body = preg_replace('/<head>/i', '<head>' . $baseTag, $body, 1);

        $ajaxInterceptor = <<<JS
        <script>
        (function() {
            var proxyBase = "{$proxyBase}";
            var adminBase = proxyBase + "/admin/";
            var freepbxIp = "{$centrexIp}";

            function rewriteUrl(url) {
                if (typeof url !== "string") return url;
                if (url.indexOf(proxyBase) !== -1) return url;
                if (url.indexOf("/proxy/") !== -1) return url;

                // Capturer les URLs mal formées du type /admin/centrex/X/... sans /proxy/
                var badUrlPattern = /^(https?:\/\/[^\/]+)?\/admin\/centrex\/(\d+)\/(?!proxy|view|edit|create)(.+)$/;
                var badMatch = url.match(badUrlPattern);
                if (badMatch) {
                    var origin = badMatch[1] || '';
                    var centrexId = badMatch[2];
                    var rest = badMatch[3];
                    var newUrl = origin + '/admin/centrex/' + centrexId + '/proxy/admin/' + rest;
                    console.log('[Admin Proxy] Bad URL fix:', url, '->', newUrl);
                    return newUrl;
                }

                if (url.indexOf(freepbxIp) !== -1) {
                    try {
                        var urlObj = new URL(url);
                        return proxyBase + urlObj.pathname + urlObj.search;
                    } catch(e) {
                        return url.replace(new RegExp('https?://' + freepbxIp.replace(/\./g, '\\\\.') + '(:\\\\d+)?', 'gi'), proxyBase);
                    }
                }
                if (url.startsWith("http://") || url.startsWith("https://")) {
                    try {
                        var urlObj = new URL(url);
                        if (urlObj.hostname.match(/^(192\\.168\\.|10\\.|172\\.(1[6-9]|2[0-9]|3[01])\\.|127\\.|localhost)/)) {
                            return proxyBase + urlObj.pathname + urlObj.search;
                        }
                    } catch(e) {}
                    return url;
                }
                if (url.startsWith("/")) {
                    return proxyBase + url;
                }
                if (url.match(/^[a-zA-Z0-9_-]+\\.php/) || url.match(/^[a-zA-Z0-9_-]+\\//)) {
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
                if (newUrl !== url) {
                    console.log('[Admin Proxy] XHR rewrite:', url, '->', newUrl);
                }
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

                    if (action.match(/^\\/admin\\?/) || action.match(/^\\/admin\\/\\?/)) {
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

            console.log('[Admin Proxy] Intercepteurs installés - proxyBase:', proxyBase);
        })();
        </script>
        JS;

        $body = preg_replace('/<\/head>/i', $ajaxInterceptor . '</head>', $body, 1);

        return $body;
    }
}
