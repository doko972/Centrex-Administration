<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ipbx;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

class AdminIpbxProxyController extends Controller
{
    /**
     * Récupérer ou créer le CookieJar pour cet IPBX
     */
    private function getCookieJar(int $ipbxId): CookieJar
    {
        $sessionKey = "admin_ipbx_cookies_{$ipbxId}";
        $cookieData = session($sessionKey, []);

        return new CookieJar(false, $cookieData);
    }

    /**
     * Sauvegarder le CookieJar en session
     */
    private function saveCookieJar(int $ipbxId, CookieJar $jar): void
    {
        $sessionKey = "admin_ipbx_cookies_{$ipbxId}";
        session([$sessionKey => $jar->toArray()]);
    }

    /**
     * Vérifier si on est authentifié sur l'IPBX
     */
    private function isAuthenticated(int $ipbxId): bool
    {
        return session("admin_ipbx_logged_in_{$ipbxId}", false);
    }

    /**
     * Marquer comme authentifié
     */
    private function setAuthenticated(int $ipbxId, bool $value = true): void
    {
        session(["admin_ipbx_logged_in_{$ipbxId}" => $value]);
    }

    /**
     * Se connecter au FreePBX (IPBX) via le formulaire de login
     */
    private function loginToFreePBX(Ipbx $ipbx, CookieJar $cookieJar): bool
    {
        // Si pas de login/password configuré, on considère l'auth OK
        if (empty($ipbx->login) || empty($ipbx->getDecryptedPassword())) {
            Log::info("Admin IPBX {$ipbx->id}: No credentials configured, skipping auto-login");
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
            $client->get("https://{$ipbx->ip_address}:{$ipbx->port}/admin/");

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

            $body = (string) $response->getBody();
            $isLoggedIn = !str_contains($body, 'id="loginform"') && !str_contains($body, 'id="login_form"');

            if ($isLoggedIn) {
                Log::info("Admin FreePBX Login successful for IPBX {$ipbx->id}");
                $this->saveCookieJar($ipbx->id, $cookieJar);
                $this->setAuthenticated($ipbx->id, true);
                return true;
            }

            Log::warning("Admin FreePBX Login failed for IPBX {$ipbx->id}");
            return false;

        } catch (\Exception $e) {
            Log::error("Admin FreePBX Login error for IPBX {$ipbx->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Afficher la page avec iframe de l'IPBX
     */
    public function show(Request $request, Ipbx $ipbx)
    {
        return view('admin.ipbx.view', compact('ipbx'));
    }

    /**
     * Proxifier les requêtes vers FreePBX (IPBX)
     */
    public function proxy(Request $request, Ipbx $ipbx, $any = null)
    {
        $cookieJar = $this->getCookieJar($ipbx->id);

        if (!$this->isAuthenticated($ipbx->id)) {
            if (!$this->loginToFreePBX($ipbx, $cookieJar)) {
                return response('Erreur de connexion au FreePBX', 401);
            }
            $cookieJar = $this->getCookieJar($ipbx->id);
        }

        $path = $any ? '/' . $any : '/';

        $proxyPattern = '/^\/?(admin\/ipbx\/\d+\/proxy\/?)/';
        $path = preg_replace($proxyPattern, '/', $path);

        $queryString = $request->getQueryString();
        if ($queryString) {
            $path .= '?' . $queryString;
        }

        $targetUrl = "https://{$ipbx->ip_address}:{$ipbx->port}" . $path;

        if ($request->method() === 'POST' && preg_match('/^\/admin\/?(\?.*)?$/', $path)) {
            $queryPart = $queryString ? '?' . $queryString : '';
            $targetUrl = "https://{$ipbx->ip_address}:{$ipbx->port}/admin/config.php" . $queryPart;
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
                    'Referer' => "https://{$ipbx->ip_address}:{$ipbx->port}/admin/",
                    'Origin' => "https://{$ipbx->ip_address}:{$ipbx->port}",
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
                }
            }

            $response = $client->request($method, $targetUrl, $options);

            $this->saveCookieJar($ipbx->id, $cookieJar);

            $statusCode = $response->getStatusCode();

            if (in_array($statusCode, [301, 302, 303, 307, 308])) {
                $location = $response->getHeaderLine('Location');
                if ($location) {
                    $appUrl = rtrim(config('app.url'), '/');
                    $proxyBase = "{$appUrl}/admin/ipbx/{$ipbx->id}/proxy";

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

            if (str_contains($contentType, 'text/html') &&
                (str_contains($body, 'id="loginform"') || str_contains($body, 'id="login_form"'))) {
                Log::info("Admin session expired for IPBX {$ipbx->id}, re-authenticating...");
                $this->setAuthenticated($ipbx->id, false);

                if (!session("admin_ipbx_retry_{$ipbx->id}")) {
                    session(["admin_ipbx_retry_{$ipbx->id}" => true]);
                    $result = $this->proxy($request, $ipbx, $any);
                    session()->forget("admin_ipbx_retry_{$ipbx->id}");
                    return $result;
                }
            }

            if ($this->isAsset($path, $contentType)) {
                return response($body, $response->getStatusCode())
                    ->withHeaders([
                        'Content-Type' => $contentType,
                        'Cache-Control' => 'public, max-age=86400',
                    ]);
            }

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
                $this->setAuthenticated($ipbx->id, false);

                if (!session("admin_ipbx_retry_{$ipbx->id}")) {
                    session(["admin_ipbx_retry_{$ipbx->id}" => true]);
                    $result = $this->proxy($request, $ipbx, $any);
                    session()->forget("admin_ipbx_retry_{$ipbx->id}");
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
            Log::error('Admin Proxy IPBX: Exception', [
                'message' => $e->getMessage(),
                'url' => $targetUrl,
            ]);

            return response('Erreur de connexion à l\'IPBX: ' . $e->getMessage(), 502);
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

    private function rewriteHtml(string $body, int $ipbxId, string $ipbxIp, int $ipbxPort): string
    {
        $appUrl = rtrim(config('app.url'), '/');
        $proxyBase = "{$appUrl}/admin/ipbx/{$ipbxId}/proxy";

        $body = preg_replace('/https?:\/\/' . preg_quote($ipbxIp, '/') . '(:\d+)?/i', $proxyBase, $body);

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
                return origOpen.apply(this, [method, rewriteUrl(url)]);
            };

            var origFetch = window.fetch;
            window.fetch = function(url, options) {
                return origFetch.apply(this, [rewriteUrl(url), options]);
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
        })();
        </script>
        JS;

        $body = preg_replace('/<\/head>/i', $ajaxInterceptor . '</head>', $body, 1);

        return $body;
    }
}
