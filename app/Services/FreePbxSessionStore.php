<?php

namespace App\Services;

use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Session FreePBX (cookies + état de connexion) utilisée par les contrôleurs proxy.
 *
 * Stockée dans le cache plutôt que dans la session Laravel : la session est réécrite en entier
 * à la fin de chaque requête, donc avec les dizaines de requêtes parallèles d'une page FreePBX,
 * une requête lente écrasait les cookies obtenus par une autre et forçait des reconnexions en boucle.
 * Ici chaque valeur a sa propre clé et n'est écrite que lorsqu'elle change.
 *
 * Les clés sont propres à la session Laravel de l'utilisateur ($scope distingue admin/client, centrex/ipbx).
 */
class FreePbxSessionStore
{
    /** Durée max d'une connexion FreePBX (le login a un timeout Guzzle de 30 s) */
    private const LOGIN_LOCK_SECONDS = 45;

    /** Attente max d'une requête parallèle pendant qu'une autre se connecte */
    private const LOGIN_WAIT_SECONDS = 35;

    public function cookieJar(string $scope, int $id): CookieJar
    {
        return new CookieJar(false, Cache::get($this->key($scope, $id, 'cookies'), []));
    }

    public function saveCookieJar(string $scope, int $id, CookieJar $jar): void
    {
        $key = $this->key($scope, $id, 'cookies');
        $cookies = $jar->toArray();

        if (Cache::get($key) !== $cookies) {
            Cache::put($key, $cookies, $this->ttl());
        }
    }

    public function isAuthenticated(string $scope, int $id): bool
    {
        return (bool) Cache::get($this->key($scope, $id, 'logged_in'), false);
    }

    public function setAuthenticated(string $scope, int $id, bool $value): void
    {
        Cache::put($this->key($scope, $id, 'logged_in'), $value, $this->ttl());
    }

    /**
     * Se connecter si nécessaire, une seule requête à la fois : les requêtes parallèles attendent
     * puis réutilisent la session ouverte au lieu de lancer chacune leur propre login.
     */
    public function ensureLoggedIn(string $scope, int $id, callable $login): bool
    {
        try {
            return Cache::lock($this->key($scope, $id, 'login_lock'), self::LOGIN_LOCK_SECONDS)
                ->block(self::LOGIN_WAIT_SECONDS, function () use ($scope, $id, $login) {
                    return $this->isAuthenticated($scope, $id) || $login();
                });
        } catch (LockTimeoutException $e) {
            Log::warning("FreePBX login lock timeout ({$scope} {$id})");

            return false;
        }
    }

    private function key(string $scope, int $id, string $name): string
    {
        return 'freepbx:' . $scope . ':' . $id . ':' . sha1(session()->getId()) . ':' . $name;
    }

    private function ttl(): int
    {
        return (int) config('session.lifetime', 120) * 60;
    }
}
