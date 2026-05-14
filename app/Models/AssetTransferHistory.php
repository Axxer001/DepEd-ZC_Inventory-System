<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetTransferHistory extends Model
{
    protected $table = 'asset_transfer_history';

    protected $fillable = [
        'asset_distribution_id',
        'from_custodian_id',
        'to_custodian_id',
        'transfer_date',
        'transfer_type',
        'remarks',
        'authorized_by',
    ];

    protected $casts = [
        'transfer_date' => 'date',
    ];

    public function assetDistribution(): BelongsTo
    {
        return $this->belongsTo(AssetDistribution::class);
    }

    public function fromCustodian(): BelongsTo
    {
        return $this->belongsTo(Custodian::class, 'from_custodian_id');
    }

    public function toCustodian(): BelongsTo
    {
        return $this->belongsTo(Custodian::class, 'to_custodian_id');
    }

    public function authorizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }
}
