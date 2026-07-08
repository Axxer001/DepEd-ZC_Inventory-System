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
        'employee_id',
        'school_id',
        'office_id',
        'property_number',
        'serial_number',
        'photo_path',
        'acquisition_cost',
        'acquisition_date',
    ];

    protected $casts = [
        'acquisition_date' => 'date',
        'acquisition_cost' => 'decimal:2',
    ];

    // Relationships
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function assetSource(): BelongsTo
    {
        return $this->belongsTo(AssetSource::class);
    }

    // public function documents(): HasMany
    // {
    //     return $this->hasMany(AssetDocument::class, 'asset_distribution_id');
    // }

    public function transfers(): HasMany
    {
        return $this->hasMany(AssetTransfer::class, 'asset_assignment_id');
    }
}
