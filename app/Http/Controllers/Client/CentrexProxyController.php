<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Centrex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
     * Afficher la page avec iframe du centrex
     */
    public function show(Request $request, Centrex $centrex)
    {
        $this->checkAccess($centrex);

        return $this->proxy($request, $centrex);
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

        // Log en mode debug uniquement
        if (config('app.debug')) {
            Log::debug('Proxy Request:', [
                'method' => $request->method(),
                'targetUrl' => $targetUrl,
            ]);
        }

        try {
            $httpClient = Http::withOptions([
                'verify' => false,
                'timeout' => 60,
                'connect_timeout' => 10,
            ])
            ->withHeaders([
                'User-Agent' => 'Centrex-Dashboard-Proxy/1.0',
            ])
            ->withBasicAuth($centrex->login, $centrex->getDecryptedPassword());

            // Exécuter la requête selon la méthode HTTP
            $method = strtolower($request->method());
            $response = match ($method) {
                'post' => $httpClient->asForm()->post($targetUrl, $request->all()),
                'get' => $httpClient->get($targetUrl),
                default => $httpClient->{$method}($targetUrl, $request->all()),
            };

            // Vérifier si le serveur distant a retourné une erreur
            if ($response->failed()) {
                Log::warning('Proxy: Erreur serveur distant', [
                    'status' => $response->status(),
                    'url' => $targetUrl,
                ]);
            }

            $body = $response->body();
            $contentType = $response->header('Content-Type') ?? 'text/html';

            // Assets (JS, CSS, images, fonts) : retourner sans modification
            if ($this->isAsset($path, $contentType)) {
                return response($body, $response->status())
                    ->withHeaders([
                        'Content-Type' => $contentType,
                        'Cache-Control' => 'public, max-age=86400',
                    ]);
            }

            // HTML : réécrire les URLs et injecter l'intercepteur AJAX
            if (str_contains($contentType, 'text/html')) {
                $body = $this->rewriteHtml($body, $centrex->id);
            }

            return response($body, $response->status())
                ->withHeaders([
                    'Content-Type' => $contentType,
                    'X-Frame-Options' => 'SAMEORIGIN',
                ]);

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
            var origOpen = XMLHttpRequest.prototype.open;
            XMLHttpRequest.prototype.open = function(method, url) {
                if (url.startsWith("/")) {
                    url = proxyBase + url;
                }
                return origOpen.apply(this, [method, url]);
            };

            var origFetch = window.fetch;
            window.fetch = function(url, options) {
                if (typeof url === "string" && url.startsWith("/")) {
                    url = proxyBase + url;
                }
                return origFetch.apply(this, [url, options]);
            };
        })();
        </script>
        JS;

        $body = preg_replace('/<\/head>/i', $ajaxInterceptor . '</head>', $body, 1);

        return $body;
    }
}
