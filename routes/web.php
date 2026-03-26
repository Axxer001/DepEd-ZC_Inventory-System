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
    Route::post('/dashboard/quick-asset', [DashboardController::class, 'storeQuickAsset'])->name('inventory.dashboard.store');
    
    Route::get('/inventory-setup', function () {
        $districts = DB::table('districts')
            ->join('quadrants', 'districts.quadrant_id', '=', 'quadrants.id')
            ->select('districts.id', 'districts.name', 'quadrants.legislative_district_id', 'quadrants.name as quadrant_name')
            ->get();
        $legislativeDistricts = DB::table('legislative_districts')->get();
        $quadrants = DB::table('quadrants')->get();
        $categories = DB::table('categories')->orderBy('name')->get();
        $items = DB::table('items')
            ->leftJoin(DB::raw('(SELECT item_id, COALESCE(SUM(quantity), 0) as distributed_quantity FROM ownerships GROUP BY item_id) as dist'), 'items.id', '=', 'dist.item_id')
            ->select('items.id', 'items.name', 'items.category_id', 'items.master_quantity', DB::raw('COALESCE(dist.distributed_quantity, 0) as distributed_quantity'))
            ->orderBy('items.name')
            ->get();
        $subItems = DB::table('sub_items')->select('id', 'name', 'item_id', 'quantity')->orderBy('name')->get();
        $allSchools = DB::table('schools')
            ->leftJoin('ownerships', 'schools.id', '=', 'ownerships.school_id')
            ->select('schools.id', 'schools.school_id', 'schools.name', DB::raw('COALESCE(SUM(ownerships.quantity), 0) as total_assets'))
            ->groupBy('schools.id', 'schools.school_id', 'schools.name')
            ->orderBy('schools.name')
            ->get();
            
        $schoolOwnerships = DB::table('ownerships')
            ->join('items', 'ownerships.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->join('sub_items', 'ownerships.sub_item_id', '=', 'sub_items.id')
            ->select(
                'ownerships.school_id',
                'categories.id as category_id',
                'categories.name as category_name',
                'items.id as item_id',
                'items.name as item_name',
                'sub_items.id as sub_item_id',
                'sub_items.name as sub_item_name',
                'ownerships.quantity'
            )
            ->get()
            ->groupBy('school_id');

        return view('inventory-setup', compact('districts', 'legislativeDistricts', 'quadrants', 'categories', 'items', 'subItems', 'allSchools', 'schoolOwnerships'));
    })->name('inventory.setup');

    // --- NEW: Dedicated Route for the Editor (Asset Modifier) ---
    Route::get('/inventory-modifier', function () {
        $districts = DB::table('districts')
            ->join('quadrants', 'districts.quadrant_id', '=', 'quadrants.id')
            ->select('districts.id', 'districts.name', 'quadrants.legislative_district_id', 'quadrants.name as quadrant_name')
            ->get();
        $legislativeDistricts = DB::table('legislative_districts')->get();
        $quadrants = DB::table('quadrants')->get();
        $categories = DB::table('categories')->orderBy('name')->get();
        $items = DB::table('items')
            ->leftJoin(DB::raw('(SELECT item_id, COALESCE(SUM(quantity), 0) as distributed_quantity FROM ownerships GROUP BY item_id) as dist'), 'items.id', '=', 'dist.item_id')
            ->select('items.id', 'items.name', 'items.category_id', 'items.master_quantity', DB::raw('COALESCE(dist.distributed_quantity, 0) as distributed_quantity'))
            ->orderBy('items.name')
            ->get();
        $subItems = DB::table('sub_items')->select('id', 'name', 'item_id', 'quantity')->orderBy('name')->get();
        $allSchools = DB::table('schools')
            ->leftJoin('ownerships', 'schools.id', '=', 'ownerships.school_id')
            ->select('schools.id', 'schools.school_id', 'schools.name', DB::raw('COALESCE(SUM(ownerships.quantity), 0) as total_assets'))
            ->groupBy('schools.id', 'schools.school_id', 'schools.name')
            ->orderBy('schools.name')
            ->get();
            
        $schoolOwnerships = DB::table('ownerships')
            ->join('items', 'ownerships.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->join('sub_items', 'ownerships.sub_item_id', '=', 'sub_items.id')
            ->select(
                'ownerships.school_id',
                'categories.id as category_id',
                'categories.name as category_name',
                'items.id as item_id',
                'items.name as item_name',
                'sub_items.id as sub_item_id',
                'sub_items.name as sub_item_name',
                'ownerships.quantity'
            )
            ->get()
            ->groupBy('school_id');

        return view('inventory-modifier', compact('districts', 'legislativeDistricts', 'quadrants', 'categories', 'items', 'subItems', 'allSchools', 'schoolOwnerships'));
    })->name('inventory.modifier');

    // --- NEW: Dedicated Route for the School Modifier ---
    Route::get('/inventory-modifier/school', function () {
        $allSchools = DB::table('schools')
            ->join('districts', 'schools.district_id', '=', 'districts.id')
            ->select('schools.id', 'schools.school_id', 'schools.name', 'schools.district_id', 'districts.name as district_name')
            ->orderBy('schools.name')
            ->get();
            
        return view('school-modifier', compact('allSchools'));
    })->name('inventory.modifier.school');
    // --- END NEW ROUTE ---
    // --- END NEW ROUTE ---

    // Process form submissions from Setup
    Route::post('/inventory-setup/school', [InventorySetupController::class, 'storeSchool'])->name('inventory.setup.school');
    Route::post('/inventory-setup/category', [InventorySetupController::class, 'storeCategory'])->name('inventory.setup.category');
    Route::post('/inventory-setup/item', [InventorySetupController::class, 'storeItem'])->name('inventory.setup.item');
    Route::post('/inventory-setup/distribution', [InventorySetupController::class, 'storeDistribution'])->name('inventory.setup.distribution');
    
    // Dedicated processors for the Modifier forms
    Route::post('/inventory-modifier/distribution', [InventorySetupController::class, 'updateDistribution'])->name('inventory.modifier.distribution');
    Route::post('/inventory-modifier/school', [InventorySetupController::class, 'updateSchool'])->name('inventory.modifier.school');
    Route::post('/inventory-setup/rename', [InventorySetupController::class, 'renameRecord'])->name('inventory.setup.rename');
    Route::post('/inventory-setup/delete', [InventorySetupController::class, 'deleteRecord'])->name('inventory.setup.delete');
    Route::post('/inventory-setup/preview-delete', [InventorySetupController::class, 'previewDelete'])->name('inventory.setup.preview_delete');

    Route::get('/admin/schools', function (Request $request) {
        $search = $request->query('search');
        $districtFilter = $request->query('districts');
        $quadrantFilter = $request->query('quadrants');
        
        $query = DB::table('schools')
            ->join('districts', 'schools.district_id', '=', 'districts.id')
            ->join('quadrants', 'districts.quadrant_id', '=', 'quadrants.id')
            ->select('schools.id', 'schools.school_id', 'schools.name', 'districts.name as district_name', 'quadrants.name as quadrant_name');

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('schools.school_id', 'LIKE', '%' . $search . '%')
                  ->orWhere('schools.name', 'LIKE', '%' . $search . '%');
            });
        }

        if (!empty($districtFilter)) {
            $districtsArray = explode(',', $districtFilter);
            if (count($districtsArray) > 0) {
                $query->whereIn('districts.name', $districtsArray);
            }
        }

        if (!empty($quadrantFilter)) {
            $quadrantsArray = explode(',', $quadrantFilter);
            if (count($quadrantsArray) > 0) {
                $query->whereIn('quadrants.name', $quadrantsArray);
            }
        }

        $schools = $query->orderBy('schools.name')->paginate(20);
        
        // Fetch all schools for the autocomplete dropdown search
        $allSchools = DB::table('schools')->select('id', 'school_id', 'name')->orderBy('name')->get();
        
        // Fetch arrays for the filter UI
        $allDistricts = DB::table('districts')->select('name')->orderBy('name')->pluck('name')->toArray();
        $allQuadrants = DB::table('quadrants')->select('name')->orderBy('name')->pluck('name')->toArray();
        
        // Fetch mapping of district to quadrant for dynamic UI disabling
        $districtQuadrantMapping = DB::table('districts')
            ->join('quadrants', 'districts.quadrant_id', '=', 'quadrants.id')
            ->select('districts.name as district', 'quadrants.name as quadrant')
            ->get();

        return view('admin.schools', compact('schools', 'search', 'allSchools', 'allDistricts', 'allQuadrants', 'districtQuadrantMapping'));
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
            // Filter by action_type (Create, Update, Delete)
            $actionTypes = ['Create', 'Update', 'Delete', 'Others'];
            if (in_array($action, $actionTypes)) {
                $query->where('action_type', $action);
            } else {
                // Filter by module name
                $query->where('module', $action);
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
                $schoolsByDistrict[$district->id] = $schools->where('district_id', $district->id)->map(function($s) {
                    return ['id' => $s->id, 'name' => $s->name];
                })->values()->toArray();
            }
            $allSchools = $schools->map(function ($s) {
                return ['id' => $s->id, 'name' => $s->name, 'school_id' => $s->school_id, 'district_id' => $s->district_id];
            })->values()->toArray();
            return view($view, compact('districts', 'schoolsByDistrict', 'allSchools'));
        };

        
    };

// Route para sa main selection (yung 2 big buttons)
Route::get('/view-assets', function () {
    return view('assets.view-assets');
})->name('assets.view');

// Route para sa Master List
Route::get('/view-all-assets', [AssetController::class, 'viewAll'])->name('assets.view_all');

// Route para sa isang school's assets
Route::get('/api/schools/{id}/assets', [AssetController::class, 'getSchoolAssets'])->name('api.schools.assets');

// Sa routes/web.php mo
Route::get('/assets/asset-history', [AssetController::class, 'history'])->name('assets.history');

// Route para sa Explorer
Route::get('/asset-explorer', [AssetController::class, 'explorer'])->name('assets.explorer');

Route::get('/inventory-setup/add-distributors', function () {
    return view('add-distributors'); // dapat add-distributors.blade.php ang filename
})->name('inventory.setup.add_distributors');

// Route para sa Add Recipients
Route::get('/inventory-setup/add-recipients', function () {
    return view('add-recipients'); // dapat add-recipients.blade.php ang filename
})->name('inventory.setup.add_recipients');


Route::middleware('auth')->group(function () {
    // Distributors View Page
    Route::get('/distributors', function () {
        return view('distributors');
    })->name('distributors.index');

    // Recipients View Page
    Route::get('/recipients', function () {
        return view('recipients');
    })->name('recipients.index');
});

    Route::get('/admin/quadrant-1-1', $quadrantHandler(1, 'admin.quadrants.q1-1'))->name('quadrant.1.1');
    Route::get('/admin/quadrant-1-2', $quadrantHandler(2, 'admin.quadrants.q1-2'))->name('quadrant.1.2');
    Route::get('/admin/quadrant-2-1', $quadrantHandler(3, 'admin.quadrants.q2-1'))->name('quadrant.2.1');
    Route::get('/admin/quadrant-2-2', $quadrantHandler(4, 'admin.quadrants.q2-2'))->name('quadrant.2.2');
});