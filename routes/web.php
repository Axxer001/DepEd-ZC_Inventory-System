<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\DashboardController;

//push check!
// Login Page
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login.form');

// Process Login Attempt
Route::post('/login', [AuthController::class, 'login'])->name('login');

// Process Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Display Registration Page
Route::get('/register', function () {
    return view('auth.register');
})->name('register');

// Process Registration Form
Route::post('/register', [RegistrationController::class, 'register'])->name('register.post');

// Admin Verification (Accept/Reject/Block from email link)
Route::get('/verify', [RegistrationController::class, 'verify'])->name('verify');

// OTP Email Verification (AJAX)
Route::post('/otp/send', [RegistrationController::class, 'sendOtp'])->name('otp.send');
Route::post('/otp/verify', [RegistrationController::class, 'verifyOtp'])->name('otp.verify');

// Dashboard and Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/inventory-setup', function () {
        return view('inventory-setup');
    })->name('inventory.setup');
});

// Redirect /login GET to root
Route::get('/login', function() {
    return redirect('/');
});