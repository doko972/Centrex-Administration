<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Ipbx extends Model
{
    protected $table = 'ipbx';

    protected $fillable = [
        'client_name',
        'contact_name',
        'email',
        'phone',
        'address',
        'ip_address',
        'port',
        'login',
        'password',
        'description',
        'status',
        'last_ping',
        'is_active',
    ];

    protected $hidden = [
        'password', // Ne jamais exposer le mot de passe chiffré dans toArray/toJson
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_ping' => 'datetime',
        'port' => 'integer',
    ];

    /**
     * Chiffrer le mot de passe avant sauvegarde
     */
    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['password'] = Crypt::encryptString($value);
        }
    }

    /**
     * Méthode pour obtenir le mot de passe déchiffré
     */
    public function getDecryptedPassword()
    {
        if (empty($this->attributes['password'])) {
            return null;
        }
        return Crypt::decryptString($this->attributes['password']);
    }

    /**
     * URL d'accès à l'IPBX
     */
    public function getUrlAttribute(): string
    {
        return "https://{$this->ip_address}:{$this->port}";
    }

    /**
     * Relation Many-to-Many avec Client
     */
    public function clients()
    {
        return $this->belongsToMany(Client::class, 'client_ipbx')
                    ->withTimestamps();
    }
}
