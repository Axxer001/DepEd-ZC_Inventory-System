<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon; 
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventorySetupController;
use App\Http\Controllers\AssetController;

Route::get('/view-assets', [AssetController::class, 'index'])->name('assets.view');

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
        $categories = DB::table('categories')->orderBy('name')->get();
        $items = DB::table('items')->select('id', 'name', 'category_id')->orderBy('name')->get();
        return view('inventory-setup', compact('districts', 'legislativeDistricts', 'quadrants', 'categories', 'items'));
    })->name('inventory.setup');

    // Process form submissions from Setup
    Route::post('/inventory-setup/school', [InventorySetupController::class, 'storeSchool'])->name('inventory.setup.school');
    Route::post('/inventory-setup/category', [InventorySetupController::class, 'storeCategory'])->name('inventory.setup.category');
    Route::post('/inventory-setup/item', [InventorySetupController::class, 'storeItem'])->name('inventory.setup.item');

    Route::get('/admin/schools', function (Request $request) {
        $search = $request->query('search');
        
        $query = DB::table('schools')
            ->join('districts', 'schools.district_id', '=', 'districts.id')
            ->select('schools.id', 'schools.school_id', 'schools.name', 'districts.name as district_name');

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('schools.school_id', 'LIKE', '%' . $search . '%')
                  ->orWhere('schools.name', 'LIKE', '%' . $search . '%');
            });
        }

        $schools = $query->orderBy('schools.name')->paginate(20);
        
        // Fetch all schools for the autocomplete dropdown search
        $allSchools = DB::table('schools')->select('id', 'school_id', 'name')->orderBy('name')->get();

        return view('admin.schools', compact('schools', 'search', 'allSchools'));
    })->name('admin.schools');

    // Route to delete a school from the registry
    Route::delete('/admin/schools/{id}', function ($id) {
        try {
            DB::table('schools')->where('id', $id)->delete();
            return redirect()->route('admin.schools')->with('success', 'School successfully deleted from the system.');
        } catch (\Exception $e) {
            return redirect()->route('admin.schools')->with('error', 'Failed to delete school. It may have existing dependencies.');
        }
    })->name('admin.schools.destroy');

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

    // Quadrants — fetch districts & schools from database
    $quadrantHandler = function ($quadrantId, $view) {
        return function () use ($quadrantId, $view) {
            $districts = DB::table('districts')->where('quadrant_id', $quadrantId)->orderBy('name')->get();
            $schools = DB::table('schools')->whereIn('district_id', $districts->pluck('id'))->orderBy('name')->get();
            $schoolsByDistrict = [];
            foreach ($districts as $district) {
                $schoolsByDistrict[$district->id] = $schools->where('district_id', $district->id)->pluck('name')->values()->toArray();
            }
            $allSchools = $schools->map(function ($s) {
                return ['name' => $s->name, 'school_id' => $s->school_id, 'district_id' => $s->district_id];
            })->values()->toArray();
            return view($view, compact('districts', 'schoolsByDistrict', 'allSchools'));
        };
    };

    Route::get('/admin/quadrant-1-1', $quadrantHandler(1, 'admin.quadrants.q1-1'))->name('quadrant.1.1');
    Route::get('/admin/quadrant-1-2', $quadrantHandler(2, 'admin.quadrants.q1-2'))->name('quadrant.1.2');
    Route::get('/admin/quadrant-2-1', $quadrantHandler(3, 'admin.quadrants.q2-1'))->name('quadrant.2.1');
    Route::get('/admin/quadrant-2-2', $quadrantHandler(4, 'admin.quadrants.q2-2'))->name('quadrant.2.2');
});