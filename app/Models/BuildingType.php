<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuildingType extends Model
{
    use HasFactory;

    protected $fillable = ['building_classification_id', 'name'];

    public function classification()
    {
        return $this->belongsTo(BuildingClassification::class, 'building_classification_id');
    }

    public function specs()
    {
        return $this->hasMany(BuildingSpec::class);
    }
}
