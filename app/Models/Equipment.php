<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;

    protected $table = 'equipment';

    protected $fillable = [
        'name',
        'category',
        'is_predefined',
        'is_active',
    ];

    protected $casts = [
        'is_predefined' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Relation Many-to-Many avec Client (avec pivot quantity et notes)
     */
    public function clients()
    {
        return $this->belongsToMany(Client::class, 'client_equipment')
                    ->withPivot('quantity', 'notes')
                    ->withTimestamps();
    }

    /**
     * Scope pour les équipements actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les équipements prédéfinis
     */
    public function scopePredefined($query)
    {
        return $query->where('is_predefined', true);
    }

    /**
     * Scope pour les équipements personnalisés
     */
    public function scopeCustom($query)
    {
        return $query->where('is_predefined', false);
    }
}
