<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetService extends Model
{
    protected $table = 'asset_services';

    protected $fillable = [
        'asset_source_id',
        'asset_assignment_id',
        'supplier_id',
        'previous_custodian_id',
        'expected_return_date',
    ];

    protected $casts = [
        'expected_return_date' => 'date',
    ];

    public function assetSource(): BelongsTo
    {
        return $this->belongsTo(AssetSource::class);
    }

    public function assetAssignment(): BelongsTo
    {
        return $this->belongsTo(AssetAssignment::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function previousCustodian(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'previous_custodian_id');
    }
}
