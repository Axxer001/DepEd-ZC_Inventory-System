<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
