<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrustedDevice extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'token', 'user_agent', 'expires_at'];

    protected $casts = [
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Libellé lisible du navigateur/OS à partir du user-agent stocké (détection simple, sans dépendance externe)
     */
    public function label(): string
    {
        $ua = (string) $this->user_agent;

        if ($ua === '') {
            return 'Appareil inconnu';
        }

        $browser = match (true) {
            str_contains($ua, 'Edg/') => 'Edge',
            str_contains($ua, 'OPR/') || str_contains($ua, 'Opera') => 'Opera',
            str_contains($ua, 'Chrome/') => 'Chrome',
            str_contains($ua, 'Firefox/') => 'Firefox',
            str_contains($ua, 'Safari/') => 'Safari',
            default => 'Navigateur inconnu',
        };

        $os = match (true) {
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'Mac OS') => 'macOS',
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'iPhone') || str_contains($ua, 'iPad') => 'iOS',
            str_contains($ua, 'Linux') => 'Linux',
            default => null,
        };

        return $os ? "{$browser} sur {$os}" : $browser;
    }
}
