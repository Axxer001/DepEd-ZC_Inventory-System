<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{

    protected $fillable = [
        'school_id',
        'name',
        'type',
        'location',
        'district_id',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
}
