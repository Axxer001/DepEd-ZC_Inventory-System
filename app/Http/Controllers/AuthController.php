<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    // Show login page
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Handle email-only login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $email = strtolower(trim($request->email));

        $user = User::where('email', $email)->where('approved', 1)->first();

        if ($user) {
            Auth::login($user);
            
            \Illuminate\Support\Facades\DB::table('system_logs')->insert([
                'user' => $user->name,
                'activity' => 'User logged in',
                'module' => 'Authentication',
                'action_type' => 'Others',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return redirect('/dashboard');
        }

        // Check if pending approval
        if (\App\Models\PendingRegistration::where('email', $email)->exists()) {
            return back()->with('error', 'Your registration is still pending admin approval.');
        }

        // Check if blocked
        if (\App\Models\BlockedAccount::where('email', $email)->exists()) {
            return back()->with('error', 'This email has been blocked from accessing the system.');
        }

        return back()->with('error', 'This account is not registered. Please register first.');
    }

    // Handle logout
    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            \Illuminate\Support\Facades\DB::table('system_logs')->insert([
                'user' => $user->name,
                'activity' => 'User logged out',
                'module' => 'Authentication',
                'action_type' => 'Others',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}