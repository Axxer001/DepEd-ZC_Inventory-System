<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class School extends Model
{
    protected $fillable = [
        'school_id',
        'name',
        'type',
        'location',
        'district_id',
    ];

    protected static function booted()
    {
        static::created(function ($school) {
            DB::table('system_logs')->insert([
                'user'        => Auth::user()?->name ?? 'System',
                'activity'    => "Added new school: {$school->name}",
                'module'      => 'Schools',
                'action_type' => 'Create',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        });

        static::deleted(function ($school) {
            DB::table('system_logs')->insert([
                'user'        => Auth::user()?->name ?? 'System',
                'activity'    => "Deleted school: {$school->name}",
                'module'      => 'Schools',
                'action_type' => 'Delete',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        });
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    // public function district(): BelongsTo
    // {
    //     return $this->belongsTo(District::class);
    // }
}
