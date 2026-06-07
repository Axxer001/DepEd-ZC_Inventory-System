<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $table = 'employees';

    protected $fillable = [
        'office_id',
        'school_id',
        'first_name',
        'middle_name',
        'last_name',
        'employee_id',
        'position',
        'status',
    ];

    // Computed full name accessor
    public function getFullNameAttribute(): string
    {
        return collect([$this->first_name, $this->middle_name, $this->last_name])
            ->filter()
            ->implode(' ');
    }

    // Relationships
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function assetAssignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class, 'employee_id');
    }

    // XOR guard — resolves the correct parent (office or school)
    public function getLocationEntityAttribute()
    {
        return $this->office ?? $this->school;
    }
}
