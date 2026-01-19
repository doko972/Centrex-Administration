<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Centrex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

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

        // Construire l'URL du proxy
        $proxyUrl = route('client.centrex.proxy', $centrex);

        return view('client.centrex-proxy', compact('centrex', 'proxyUrl'));
    }

    /**
     * Proxifier les requêtes vers FreePBX
     */
    public function proxy(Request $request, Centrex $centrex)
    {
        $user = Auth::user();
        $client = $user->client;

        // Vérifier l'accès
        if (!$client->centrex->contains($centrex->id)) {
            abort(403);
        }

        // Construire l'URL cible
        $baseUrl = "http://{$centrex->ip_address}";

        // Extraire le chemin après /proxy/
        $fullPath = $request->path();
        $proxyPrefix = "client/centrex/{$centrex->id}/proxy";

        if (strpos($fullPath, $proxyPrefix) === 0) {
            $path = substr($fullPath, strlen($proxyPrefix));
        } else {
            $path = '';
        }

        // Si le chemin est vide, utiliser /
        if (empty($path) || $path === '/') {
            $path = '/';
        }

        $targetUrl = $baseUrl . $path;

        // Ajouter les query parameters
        if ($request->getQueryString()) {
            $targetUrl .= '?' . $request->getQueryString();
        }

        try {
            // Faire la requête vers FreePBX
            $response = Http::withOptions([
                'verify' => false,
                'timeout' => 30,
            ])
                ->withHeaders([
                    'User-Agent' => 'Centrex-Dashboard-Proxy/1.0',
                ])
                ->withBasicAuth($centrex->login, $centrex->password)
                ->{strtolower($request->method())}($targetUrl, $request->all());

            $body = $response->body();
            $contentType = $response->header('Content-Type') ?? 'text/html';

            // Si c'est du HTML, réécrire les chemins
            if (strpos($contentType, 'text/html') !== false) {
                $proxyBase = url("client/centrex/{$centrex->id}/proxy");

                // Remplacer les chemins absolus
                // Remplacer href="/..."
                $body = preg_replace('/href=["\']\/([^"\']*)["\']/i', 'href="' . $proxyBase . '/$1"', $body);

                // Remplacer src="/..."
                $body = preg_replace('/src=["\']\/([^"\']*)["\']/i', 'src="' . $proxyBase . '/$1"', $body);

                // Remplacer action="/..."
                $body = preg_replace('/action=["\']\/([^"\']*)["\']/i', 'action="' . $proxyBase . '/$1"', $body);

                // Ajouter une base tag pour les chemins relatifs
                $baseTag = '<base href="' . $proxyBase . '/">';
                $body = preg_replace('/<head>/i', '<head>' . $baseTag, $body, 1);
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
