<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    protected $fillable = [
        'school_id',
        'region',
        'division',
        'office_type',
        'school_identifier',
        'office_name',
        'address',
        'storeys',
        'classrooms',
        'article',
        'description',
        'classification',
        'occupancy_nature',
        'location',
        'date_constructed',
        'acquisition_date',
        'property_number',
        'acquisition_cost',
        'appraised_value',
        'appraisal_date',
        'remarks',
    ];

    protected $casts = [
        'date_constructed' => 'date',
        'acquisition_date' => 'date',
        'appraisal_date' => 'date',
        'acquisition_cost' => 'decimal:2',
        'appraised_value' => 'decimal:2',
        'storeys' => 'integer',
        'classrooms' => 'integer',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
