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
    use HasFactory, Notifiable;

    protected static function booted()
    {
        static::created(function ($user) {
            DB::table('system_logs')->insert([
                'user'        => Auth::user()?->name ?? 'System',
                'activity'    => "Created new account: {$user->email}",
                'module'      => 'Accounts',
                'action_type' => 'Create',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        });

        static::deleted(function ($user) {
            DB::table('system_logs')->insert([
                'user'        => Auth::user()?->name ?? 'System',
                'activity'    => "Deleted account: {$user->email}",
                'module'      => 'Accounts',
                'action_type' => 'Delete',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        });
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
        'role',
        'approved',
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
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'approved' => 'boolean',
            'dark_mode' => 'boolean',
        ];
    }
}
