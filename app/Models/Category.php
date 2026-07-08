<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{

    protected $fillable = ['name', 'classification_id', 'see_category_code', 'ppe_category_code'];

    public function classification(): BelongsTo
    {
        return $this->belongsTo(Classification::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
