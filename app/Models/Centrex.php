<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Centrex extends Model
{
    use HasFactory;

    protected $table = 'centrex';

    protected $fillable = [
        'name',
        'ip_address',
        'port',
        'login',
        'password',
        'image',
        'status',
        'last_check',
        'description',
        'is_active',
    ];

    protected $hidden = [
        'password', // Ne jamais exposer le mot de passe chiffré dans toArray/toJson
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_check' => 'datetime',
    ];

    /**
     * Chiffrer le mot de passe avant sauvegarde
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Crypt::encryptString($value);
    }

    /**
     * Méthode pour obtenir le mot de passe déchiffré
     * (Ne pas utiliser un accessor automatique pour éviter l'exposition dans toArray/toJson)
     */
    public function getDecryptedPassword()
    {
        return Crypt::decryptString($this->attributes['password']);
    }

    /**
     * Relation Many-to-Many avec Client
     */
    public function clients()
    {
        return $this->belongsToMany(Client::class, 'client_centrex')
                    ->withTimestamps();
    }
}