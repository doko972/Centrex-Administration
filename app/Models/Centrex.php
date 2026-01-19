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
     * Déchiffrer le mot de passe à la lecture
     */
    public function getPasswordAttribute($value)
    {
        return Crypt::decryptString($value);
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