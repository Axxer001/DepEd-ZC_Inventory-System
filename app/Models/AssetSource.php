<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetSource extends Model
{

    protected $fillable = [
        'item_id',
        'acquisition_source_id',
        'supplier_id',
        'procurement_mode_id',
        'description',
        'unit_of_measurement',
        'asset_cost',
        'quantity',
        'estimated_useful_life',
        'warranty',
        'acceptance_date',
        'condition',           // renamed from remarks
    ];

    protected $casts = [
        'acceptance_date' => 'date',
        'asset_cost'      => 'decimal:2',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function acquisitionSource(): BelongsTo
    {
        return $this->belongsTo(AcquisitionSource::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function procurementMode(): BelongsTo
    {
        return $this->belongsTo(ProcurementMode::class);
    }


    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class);
    }
}
