<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Office extends Model
{
    protected $fillable = [
        'school_id',
        'name',
        'office_code',
        'room_number',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function custodians(): HasMany
    {
        return $this->hasMany(Custodian::class);
    }

    /**
     * Resolve asset assignments through custodians since office_id was moved to custodians.
     */
    public function assetAssignments(): HasManyThrough
    {
        return $this->hasManyThrough(AssetAssignment::class, Custodian::class);
    }
}
