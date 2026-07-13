<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingRegistration extends Model
{
    protected $fillable = [
        'email',
        'token',
        'password',
        'expires_at',
        'system_type',
        'school_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isExpired(): bool
    {
        return $this->expires_at && now()->greaterThan($this->expires_at);
    }

    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function hasDomainMismatch(): bool
    {
        if ($this->system_type !== 'school' || !$this->school_id) {
            return false;
        }

        $school = $this->school;
        if (!$school) {
            return false;
        }

        $email = strtolower($this->email);
        $schoolCode = strtolower($school->school_id);
        
        // Pattern 1: Email has the school's code (e.g. 126097@deped.gov.ph or admin.126097@deped.gov.ph)
        if (str_contains($email, $schoolCode)) {
            return false;
        }

        // Pattern 2: Email has a clean slug of the school name (e.g. ayala.nhs in ayala.nhs@deped.gov.ph)
        $cleanName = preg_replace('/[^a-z0-9]/', ' ', strtolower($school->name));
        $words = array_filter(explode(' ', $cleanName), function ($w) {
            return strlen($w) >= 4 && !in_array($w, ['school', 'elementary', 'national', 'high', 'central']);
        });

        foreach ($words as $word) {
            if (str_contains($email, $word)) {
                return false;
            }
        }

        return true;
    }
}
