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
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    // --- Forgot Password Workflow ---
    
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetPin(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $email = strtolower(trim($request->email));

        $user = \App\Models\User::where('email', $email)->first();
        if (!$user) {
            return back()->with('error', 'We cannot find a user with that email address.');
        }

        $pin = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => \Illuminate\Support\Facades\Hash::make($pin), 'created_at' => now()]
        );

        \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\PasswordResetPinMail($pin));

        $request->session()->put('reset_email', $email);
        
        return redirect()->route('password.verify')->with('success', 'Verification PIN sent to your email.');
    }

    public function showVerifyPin(Request $request)
    {
        if (!$request->session()->has('reset_email')) {
            return redirect()->route('password.request');
        }
        return view('auth.verify-pin');
    }

    public function verifyPin(Request $request)
    {
        $request->validate(['pin' => 'required|numeric|digits:6']);
        $email = $request->session()->get('reset_email');
        
        if (!$email) {
            return redirect()->route('password.request')->with('error', 'Session expired. Please try again.');
        }

        $record = \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$record || !\Illuminate\Support\Facades\Hash::check($request->pin, $record->token)) {
            return back()->with('error', 'Invalid or expired verification PIN.');
        }

        $request->session()->put('pin_verified', true);
        return redirect()->route('password.reset')->with('success', 'PIN verified. You can now reset your password.');
    }

    public function showResetPassword(Request $request)
    {
        if (!$request->session()->has('pin_verified') || !$request->session()->get('pin_verified')) {
            return redirect()->route('password.request');
        }
        return view('auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $email = $request->session()->get('reset_email');
        if (!$email) {
            return redirect()->route('password.request')->with('error', 'Session expired. Please try again.');
        }

        $user = \App\Models\User::where('email', $email)->first();
        if ($user) {
            $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
            $user->save();
        }

        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $email)->delete();
        $request->session()->forget(['reset_email', 'pin_verified']);

        return redirect()->route('login.form')->with('success', 'Password reset successfully. You can now log in.');
    }
}