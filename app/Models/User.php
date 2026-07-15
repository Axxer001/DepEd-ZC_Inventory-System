<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use Notifiable {
        notify as sendNotification;
    }


    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'dark_mode',
        'muted_notifications',
        'role',
        'approved',
        'system_type',
        'school_id',
    ];

    /**
     * Role checking helpers
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->role === 'super_admin';
    }

    public function isRegularUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'    => 'datetime',
            'password'             => 'hashed',
            'approved'             => 'boolean',
            'dark_mode'            => 'boolean',
            'muted_notifications'  => 'array',
        ];
    }

    /**
     * Check if a specific notification class (by short name) is muted by this user.
     */
    public function isNotificationMuted(string $type): bool
    {
        return in_array($type, $this->muted_notifications ?? []);
    }

    /**
     * Override notify() to silently drop muted notification types.
     *
     * @param  mixed  $instance
     */
    public function notify($instance): void
    {
        if ($this->isNotificationMuted(class_basename($instance))) {
            return;
        }
        $this->sendNotification($instance);
    }

    protected static function booted()
    {
        static::saving(function ($user) {
            if ($user->system_type === 'school' && $user->role === 'super_admin') {
                throw new \InvalidArgumentException("School system accounts cannot be assigned the Super Admin role.");
            }
        });
    }

    public function isMainSystem(): bool
    {
        return $this->system_type === 'main';
    }

    public function isSchoolSystem(): bool
    {
        return $this->system_type === 'school';
    }

    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the list of users who should be notified of an event.
     *
     * @param  int|null  $schoolId  The school_id associated with the event (null if SDO/Main)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getNotificationRecipients(?int $schoolId = null)
    {
        return self::where('approved', true)
            ->where(function ($query) use ($schoolId) {
                // Main admins always get all notifications
                $query->where(function ($q) {
                    $q->where('system_type', 'main')
                      ->whereIn('role', ['admin', 'super_admin']);
                });

                // If event is associated with a school, also notify that school's users
                if ($schoolId) {
                    $query->orWhere(function ($q) use ($schoolId) {
                        $q->where('system_type', 'school')
                          ->where('school_id', $schoolId);
                    });
                }
            })
            ->get();
    }
}
