<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Custodian extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'employee_id',
        'position',
        'school_id',
        'contact_number',
        'status',
    ];

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(AssetDistribution::class);
    }

    public function transferHistory(): HasMany
    {
        return $this->hasMany(AssetTransferHistory::class, 'to_custodian_id');
    }
}
