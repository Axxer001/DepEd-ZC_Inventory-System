<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BuildingRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted()
    {
        static::addGlobalScope('school_scope', function ($builder) {
            if (auth()->check() && auth()->user()->isSchoolSystem()) {
                $builder->where('building_records.school_id', auth()->user()->school_id);
            }
        });
    }

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
        'remarks',
        'origin_system_type',
        'registered_by_school_id',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function spec()
    {
        return $this->belongsTo(BuildingSpec::class, 'building_spec_id');
    }

    public function registeredBySchool()
    {
        return $this->belongsTo(School::class, 'registered_by_school_id');
    }
}
