<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class School extends Model
{
    protected $fillable = [
        'school_id',
        'name',
        'district_id',
    ];

    public function offices(): HasMany
    {
        return $this->hasMany(Office::class);
    }

    public function custodians(): HasMany
    {
        // school_id in custodians is the string code, not the PK
        return $this->hasMany(Custodian::class, 'school_id', 'school_id');
    }

    /**
     * Resolve asset assignments through custodians since school_id was moved to custodians.
     */
    public function assetAssignments(): HasManyThrough
    {
        return $this->hasManyThrough(
            AssetAssignment::class,
            Custodian::class,
            'school_id', // Foreign key on custodians table...
            'custodian_id', // Foreign key on asset_assignments table...
            'school_id', // Local key on schools table...
            'id' // Local key on custodians table...
        );
    }
}
