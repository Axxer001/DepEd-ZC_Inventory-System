<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcquisitionContact extends Model
{
    protected $fillable = [
        'acquisition_source_id',
        'name',
        'position',
        'contact_number',
        'email',
    ];

    public function acquisitionSource(): BelongsTo
    {
        return $this->belongsTo(AcquisitionSource::class);
    }

    public function assetSources(): HasMany
    {
        return $this->hasMany(AssetSource::class);
    }
}
