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

    // Handle email and password login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        $email = strtolower(trim($request->email));

        if (Auth::attempt(['email' => $email, 'password' => $request->password, 'approved' => true])) {
            $user = Auth::user();
            
            \Illuminate\Support\Facades\DB::table('system_logs')->insert([
                'user' => $user->name,
                'activity' => 'User logged in',
                'module' => 'Authentication',
                'action_type' => 'Others',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return redirect()->intended('/dashboard');
        }

        // Check if pending approval
        if (\App\Models\PendingRegistration::where('email', $email)->exists()) {
            return back()->with('error', 'Your registration is still pending admin approval.');
        }

        // Check if blocked
        if (\App\Models\BlockedAccount::where('email', $email)->exists()) {
            return back()->with('error', 'This email has been blocked from accessing the system.');
        }

        // Check if user exists but wrong password/unapproved
        $user = \App\Models\User::where('email', $email)->first();
        if ($user) {
            if (!$user->approved) {
                return back()->with('error', 'Your account has not been approved yet.');
            }
            return back()->with('error', 'Invalid email or password.');
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