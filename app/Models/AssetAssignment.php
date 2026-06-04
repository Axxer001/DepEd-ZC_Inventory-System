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
        // NOTE: school_id and office_id are now on custodians — call via custodian()->school() / custodian()->office()
        'condition',
        'office_school_type',
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

    public function transfers(): HasMany
    {
        return $this->hasMany(AssetTransfer::class);
    }
}
