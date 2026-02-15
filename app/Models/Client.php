<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'contact_name',
        'email',
        'phone',
        'address',
        'is_active',
        'has_4g5g_backup',
        'backup_operator',
        'backup_sim_number',
        'backup_phone_number',
        'backup_notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'has_4g5g_backup' => 'boolean',
    ];

    /**
     * Relation avec User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * RelationMany-to-Many avec Centrex
     */
    public function centrex()
    {
        return $this->belongsToMany(Centrex::class, 'client_centrex')
                    ->withTimestamps();
    }

    /**
     * Relation Many-to-Many avec Ipbx
     */
    public function ipbx()
    {
        return $this->belongsToMany(Ipbx::class, 'client_ipbx')
                    ->withTimestamps();
    }

    /**
     * Relation Many-to-Many avec ConnectionType
     */
    public function connectionTypes()
    {
        return $this->belongsToMany(ConnectionType::class, 'client_connection_type')
                    ->withTimestamps();
    }

    /**
     * Relation Many-to-Many avec Provider
     */
    public function providers()
    {
        return $this->belongsToMany(Provider::class, 'client_provider')
                    ->withTimestamps();
    }

    /**
     * Relation Many-to-Many avec Equipment (avec pivot quantity et notes)
     */
    public function equipment()
    {
        return $this->belongsToMany(Equipment::class, 'client_equipment')
                    ->withPivot('quantity', 'notes')
                    ->withTimestamps();
    }
}