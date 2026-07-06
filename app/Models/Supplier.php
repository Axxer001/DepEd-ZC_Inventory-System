<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'supplier_personnel',
        'service_center',
        'contact_number',
        'contact_email',
    ];

    public function assetSources(): HasMany
    {
        return $this->hasMany(AssetSource::class);
    }
}
