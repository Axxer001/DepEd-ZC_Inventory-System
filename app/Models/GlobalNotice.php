<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalNotice extends Model
{
    protected $fillable = [
        'content',
        'link',
        'link_label',
        'active',
        'created_by'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
