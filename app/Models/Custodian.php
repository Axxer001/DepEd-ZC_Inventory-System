<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Custodian extends Model
{
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'employee_id',
        'position',
        'school_id',   // string school code (e.g. "105001") — references schools.school_id
        'office_id',   // FK to offices.id for division-office staff
        'contact_number',
        'status',
    ];

    public function getFullNameAttribute(): string
    {
        $parts = array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ]);
        return implode(' ', $parts);
    }

    public function school(): BelongsTo
    {
        // school_id in custodians is the string code, not the PK
        return $this->belongsTo(School::class, 'school_id', 'school_id');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
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
