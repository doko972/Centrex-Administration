<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'description',
        'status',
        'last_ping',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_ping' => 'datetime',
        'port' => 'integer',
    ];

    public function getUrlAttribute(): string
    {
        return "https://{$this->ip_address}:{$this->port}";
    }
}
