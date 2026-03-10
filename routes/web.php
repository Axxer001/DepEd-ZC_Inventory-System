<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\DashboardController;
//dahamn
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
        $districts = \Illuminate\Support\Facades\DB::table('districts')
            ->join('quadrants', 'districts.quadrant_id', '=', 'quadrants.id')
            ->select('districts.id', 'districts.name', 'quadrants.legislative_district_id', 'quadrants.name as quadrant_name')
            ->get();
        $legislativeDistricts = \Illuminate\Support\Facades\DB::table('legislative_districts')->get();
        $quadrants = \Illuminate\Support\Facades\DB::table('quadrants')->get();
        return view('inventory-setup', compact('districts', 'legislativeDistricts', 'quadrants'));
    })->name('inventory.setup');
});

// Redirect /login GET to root
Route::get('/login', function() {
    return redirect('/');
});


Route::get('/admin/schools', function () {
    return view('admin.schools');
})->name('admin.schools');

Route::get('/admin/logs', function () {
    return view('admin.logs');
})->name('admin.logs');


Route::get('/admin/quadrant-1-1', function () {
    return view('admin.quadrants.q1-1');
})->name('quadrant.1.1');


Route::get('/admin/quadrant-1-2', function () {
    return view('admin.quadrants.q1-2');
})->name('quadrant.1.2');




Route::get('/admin/quadrant-2-1', function () {
    return view('admin.quadrants.q2-1');
})->name('quadrant.2.1');


Route::get('/admin/quadrant-2-2', function () {
    return view('admin.quadrants.q2-2');
})->name('quadrant.2.2');