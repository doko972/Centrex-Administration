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
     * Afficher la page avec iframe du centrex
     */
    public function show(Centrex $centrex)
    {
        $user = Auth::user();
        $client = $user->client;

        // Vérifier que le client a bien accès à ce centrex
        if (!$client->centrex->contains($centrex->id)) {
            abort(403, 'Vous n\'avez pas accès à ce centrex.');
        }

        // Vérifier que le centrex est actif
        if (!$centrex->is_active) {
            abort(403, 'Ce centrex n\'est pas disponible actuellement.');
        }

        // Au lieu d'utiliser l'iframe, charger directement le contenu
        return $this->proxy($request ?? request(), $centrex);
    }

    /**
     * Proxifier les requêtes vers FreePBX
     */
    public function proxy(Request $request, Centrex $centrex, $any = null)
    {
        $user = Auth::user();
        $client = $user->client;

        // Vérifier l'accès
        if (!$client->centrex->contains($centrex->id)) {
            abort(403);
        }

        // Construire l'URL cible
        $baseUrl = "http://{$centrex->ip_address}";

        // Récupérer le chemin demandé
        $path = $any ? '/' . $any : '/';

        // Ajouter les query parameters
        $queryString = $request->getQueryString();
        if ($queryString) {
            $path .= '?' . $queryString;
        }

        $targetUrl = $baseUrl . $path;

        // DEBUG : Logger toutes les requêtes
        Log::info('Proxy Request Full:', [
            'method' => $request->method(),
            'any' => $any,
            'path' => $path,
            'targetUrl' => $targetUrl,
            'queryString' => $request->getQueryString(),
            'postData' => $request->all()
        ]);

        try {
            // Configuration de base
            $httpClient = Http::withOptions([
                'verify' => false,
                'timeout' => 30,
            ])
                ->withHeaders([
                    'User-Agent' => 'Centrex-Dashboard-Proxy/1.0',
                ])
                ->withBasicAuth($centrex->login, $centrex->password);

            // Selon la méthode HTTP
            $method = strtolower($request->method());

            if ($method === 'post') {
                // Pour POST, envoyer les données comme formulaire
                $response = $httpClient->asForm()->post($targetUrl, $request->all());
            } elseif ($method === 'get') {
                $response = $httpClient->get($targetUrl);
            } else {
                $response = $httpClient->{$method}($targetUrl, $request->all());
            }

            $body = $response->body();
            $contentType = $response->header('Content-Type') ?? 'text/html';

            // Si c'est du HTML, réécrire les chemins
            if (strpos($contentType, 'text/html') !== false) {
                $proxyBase = url("client/centrex/{$centrex->id}/proxy");

                // Remplacer href="/..."
                $body = preg_replace('/href=["\']\/((?!http)[^"\']*)["\']/i', 'href="' . $proxyBase . '/$1"', $body);

                // Remplacer src="/..."
                $body = preg_replace('/src=["\']\/((?!http)[^"\']*)["\']/i', 'src="' . $proxyBase . '/$1"', $body);

                // Remplacer action="/..."
                $body = preg_replace('/action=["\']\/((?!http)[^"\']*)["\']/i', 'action="' . $proxyBase . '/$1"', $body);

                // Ajouter une base tag
                $baseTag = '<base href="' . $proxyBase . '/admin/">';
                $body = preg_replace('/<head>/i', '<head>' . $baseTag, $body, 1);

                // NOUVEAU : Injecter un script pour intercepter les requêtes AJAX
                $ajaxInterceptor = '<script>
    (function() {
        var proxyBase = "' . $proxyBase . '";
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
    </script>';

                $body = preg_replace('/<\/head>/i', $ajaxInterceptor . '</head>', $body, 1);
            }

            // Retourner la réponse modifiée
            return response($body, $response->status())
                ->withHeaders([
                    'Content-Type' => $contentType,
                    'X-Frame-Options' => 'SAMEORIGIN',
                ]);
        } catch (\Exception $e) {
            return response('Erreur de connexion au centrex: ' . $e->getMessage(), 500);
        }
    }
}
