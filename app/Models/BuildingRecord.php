<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuildingRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'building_spec_id',
        'occupancy_nature',
        'date_constructed',
        'acquisition_date',
        'property_number',
        'acquisition_cost',
        'estimated_useful_life',
        'appraised_value',
        'appraisal_date',
        'remarks'
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function spec()
    {
        return $this->belongsTo(BuildingSpec::class, 'building_spec_id');
    }
}
