<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Custodian extends Model
{
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'employee_id',
        'position',
        'contact_number',
        'status',
    ];

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(AssetTransfer::class, 'to_custodian_id');
    }
}
