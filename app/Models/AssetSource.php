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
        'equipment',
        'contact_person',
        'contact_position',
        'supplier_personnel',
        'supplier_contact_number',
        'supplier_contact_email',
        'supplier_service_center',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->asset_cost <= 49999) {
                $model->equipment = 'SEE';
            } else {
                $model->equipment = 'PPE';
            }

            if (empty($model->contact_person) || empty($model->contact_position)) {
                $source = $model->acquisitionSource;
                if ($source) {
                    if (empty($model->contact_person)) {
                        $model->contact_person = $source->contact_person;
                    }
                    if (empty($model->contact_position)) {
                        $model->contact_position = $source->contact_position;
                    }
                }
            }

            if ($model->supplier_id && (empty($model->supplier_personnel) || empty($model->supplier_contact_number) || empty($model->supplier_contact_email) || empty($model->supplier_service_center))) {
                $supplier = $model->supplier;
                if ($supplier) {
                    if (empty($model->supplier_personnel)) {
                        $model->supplier_personnel = $supplier->supplier_personnel;
                    }
                    if (empty($model->supplier_contact_number)) {
                        $model->supplier_contact_number = $supplier->contact_number;
                    }
                    if (empty($model->supplier_contact_email)) {
                        $model->supplier_contact_email = $supplier->contact_email;
                    }
                    if (empty($model->supplier_service_center)) {
                        $model->supplier_service_center = $supplier->service_center;
                    }
                }
            }
        });
    }

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
