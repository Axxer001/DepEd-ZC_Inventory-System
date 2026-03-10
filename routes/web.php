<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon; 
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventorySetupController;

// --- Public Routes ---
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/register', function () { return view('auth.register'); })->name('register');
Route::post('/register', [RegistrationController::class, 'register'])->name('register.post');
Route::get('/verify', [RegistrationController::class, 'verify'])->name('verify');
Route::post('/otp/send', [RegistrationController::class, 'sendOtp'])->name('otp.send');
Route::post('/otp/verify', [RegistrationController::class, 'verifyOtp'])->name('otp.verify');

// Redirect /login GET to root
Route::get('/login', function() { return redirect('/'); });

// --- Protected Admin Routes ---
Route::middleware('auth')->group(function () {
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/inventory-setup', function () {
        $districts = DB::table('districts')
            ->join('quadrants', 'districts.quadrant_id', '=', 'quadrants.id')
            ->select('districts.id', 'districts.name', 'quadrants.legislative_district_id', 'quadrants.name as quadrant_name')
            ->get();
        $legislativeDistricts = DB::table('legislative_districts')->get();
        $quadrants = DB::table('quadrants')->get();
        return view('inventory-setup', compact('districts', 'legislativeDistricts', 'quadrants'));
    })->name('inventory.setup');

    // Process form submissions from Setup
    Route::post('/inventory-setup/school', [InventorySetupController::class, 'storeSchool'])->name('inventory.setup.school');

    Route::get('/admin/schools', function () {
        return view('admin.schools');
    })->name('admin.schools');

    // --- SYSTEM LOGS ROUTE (ONE VERSION ONLY) ---
    Route::get('/admin/logs', function (Request $request) {
        // 1. Capture the action from the dropdown, default to 'All Actions'
        $action = $request->query('action', 'All Actions');
        
        $query = DB::table('system_logs');

        // 2. Filter logic
        if ($action !== 'All Actions') {
            switch ($action) {
                case 'Authentications':
                    $query->where(function($q) {
                        $q->whereIn('table_name', ['users', 'sessions'])
                          ->orWhere('activity', 'LIKE', '%login%')
                          ->orWhere('activity', 'LIKE', '%logout%');
                    });
                    break;
                case 'Schools': $query->where('table_name', 'schools'); break;
                case 'Items': $query->whereIn('table_name', ['items', 'inventory']); break;
                case 'Districts': $query->whereIn('table_name', ['districts', 'quadrants']); break;
                default: $query->where('activity', 'LIKE', '%' . $action . '%'); break;
            }
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(20);

        // 3. Convert to Philippines Time (Asia/Manila)
        $logs->getCollection()->transform(function ($log) {
            $log->ph_time = Carbon::parse($log->created_at)->timezone('Asia/Manila');
            return $log;
        });

        // 4. Pass variables to view
        return view('admin.logs', compact('action', 'logs'));
    })->name('admin.logs');

    // Quadrants
    Route::get('/admin/quadrant-1-1', function () { return view('admin.quadrants.q1-1'); })->name('quadrant.1.1');
    Route::get('/admin/quadrant-1-2', function () { return view('admin.quadrants.q1-2'); })->name('quadrant.1.2');
    Route::get('/admin/quadrant-2-1', function () { return view('admin.quadrants.q2-1'); })->name('quadrant.2.1');
    Route::get('/admin/quadrant-2-2', function () { return view('admin.quadrants.q2-2'); })->name('quadrant.2.2');
});