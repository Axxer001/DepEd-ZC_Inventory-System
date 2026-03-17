<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ownership extends Model
{
    protected $fillable = [
        'school_id',
        'item_id',
        'sub_item_id',
        'quantity',
    ];
}
