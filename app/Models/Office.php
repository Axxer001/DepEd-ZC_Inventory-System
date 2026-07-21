<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Office extends Model
{
    protected $fillable = [
        'office_id',
        'name',
        'type',
        'location',
        'is_system',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Returns the ID of the canonical Property and Supply Unit (PSU) office.
     * Cached forever — bust via Cache::forget('office.psu_id') if re-seeded.
     */
    public static function psuId(): ?int
    {
        return 5;
    }
}
