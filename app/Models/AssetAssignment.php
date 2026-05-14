<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetAssignment extends Model
{
    protected $table = 'asset_assignments';

    protected $fillable = [
        'asset_source_id',
        'custodian_id',
        'office_id',
        'condition',
        'office_school_type',
        'school_id',
        'nature_of_occupancy',
        'location',
        'property_number',
        'photo_path',
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

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(AssetTransfer::class);
    }
}
