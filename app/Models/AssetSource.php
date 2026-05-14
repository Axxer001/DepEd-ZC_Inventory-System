<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetSource extends Model
{
    protected $fillable = [
        'item_id',
        'description',
        'acquisition_source_id',
        'mode_of_acquisition',
        'source_personnel',
        'personnel_position',
        'asset_cost',
        'quantity',
        'estimated_useful_life',
        'acceptance_date',
    ];

    protected $casts = [
        'acceptance_date' => 'date',
        'asset_cost' => 'decimal:2',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function acquisitionSource(): BelongsTo
    {
        return $this->belongsTo(AcquisitionSource::class);
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(AssetDistribution::class);
    }
}
