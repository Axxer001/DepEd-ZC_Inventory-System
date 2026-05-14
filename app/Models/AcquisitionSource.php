<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcquisitionSource extends Model
{
    protected $fillable = ['name', 'source_type'];

    public function assetSources(): HasMany
    {
        return $this->hasMany(AssetSource::class);
    }
}
