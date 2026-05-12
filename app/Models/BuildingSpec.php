<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuildingSpec extends Model
{
    use HasFactory;

    protected $fillable = ['building_type_id', 'description', 'storeys', 'classrooms'];

    public function type()
    {
        return $this->belongsTo(BuildingType::class, 'building_type_id');
    }

    public function records()
    {
        return $this->hasMany(BuildingRecord::class);
    }
}
