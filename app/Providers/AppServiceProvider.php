<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Define Gates for authorization
        \Illuminate\Support\Facades\Gate::define('manage-users', function ($user) {
            return $user->role === 'super_admin';
        });

        \Illuminate\Support\Facades\Gate::define('write-inventory', function ($user) {
            return in_array($user->role, ['super_admin', 'admin']);
        });

        \Illuminate\Support\Facades\Gate::define('read-inventory', function ($user) {
            return true;
        });

        // Smart Detect: If accessed via tunnel (Expose, Localhost.run, etc.), fix URL and HTTPS
        if (request()->getHost() !== 'localhost' && request()->getHost() !== '127.0.0.1') {
            \URL::forceRootUrl(request()->getSchemeAndHttpHost());
            
            // Force HTTPS for common tunnel providers
            if (str_contains(request()->getHost(), 'sharedwithexpose.com') || 
                str_contains(request()->getHost(), '.lhr.life') ||
                request()->header('X-Forwarded-Proto') === 'https') {
                \URL::forceScheme('https');
            }
        }
    }
}
