<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetDistribution extends Model
{
    protected $fillable = [
        'asset_source_id',
        'custodian_id',
        'region',
        'division',
        'office_school_type',
        'school_id',
        'office_school_name',
        'nature_of_occupancy',
        'location',
        'property_number',
        'acquisition_cost',
        'acquisition_date',
    ];

    protected $casts = [
        'acquisition_date' => 'date',
        'acquisition_cost' => 'decimal:2',
    ];

    public function assetSource(): BelongsTo
    {
        return $this->belongsTo(AssetSource::class);
    }

    public function custodian(): BelongsTo
    {
        return $this->belongsTo(Custodian::class);
    }

    public function transferHistory(): HasMany
    {
        return $this->hasMany(AssetTransferHistory::class);
    }
}
