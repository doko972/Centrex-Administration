<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relation Many-to-Many avec Client
     */
    public function clients()
    {
        return $this->belongsToMany(Client::class, 'client_provider')
                    ->withTimestamps();
    }

    /**
     * Scope pour les fournisseurs actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
