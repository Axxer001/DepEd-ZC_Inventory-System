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
use App\Http\Controllers\BuildingImportController;

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

    // --- Dark Mode Preference ---
    Route::post('/user/dark-mode', function (Request $request) {
        $user = auth()->user();
        $user->dark_mode = !$user->dark_mode;
        $user->save();
        return response()->json(['dark_mode' => $user->dark_mode]);
    })->name('user.dark-mode');
    
    Route::get('/inventory-setup', function () {
        set_time_limit(300);
        
        $districts = DB::table('districts')
            ->join('quadrants', 'districts.quadrant_id', '=', 'quadrants.id')
            ->select('districts.id', 'districts.name', 'quadrants.legislative_district_id', 'quadrants.name as quadrant_name')
            ->get();
        $legislativeDistricts = DB::table('legislative_districts')->get();
        $quadrants = DB::table('quadrants')->get();
        $categories = DB::table('categories')->orderBy('name')->get();
        $items = DB::table('items')->orderBy('name')->get();
        
        // Fetch sub-items (asset_sources) mapping description to name for the view
        $subItems = DB::table('asset_sources')
            ->select('id', 'item_id', DB::raw('COALESCE(description, "General") as name'), 'acquisition_source_id as distributor_id')
            ->get();
            
        // Fetch stakeholders (acquisition_sources) mapping source_type to type for the view
        $stakeholders = DB::table('acquisition_sources')
            ->select('id', 'name', DB::raw('CASE WHEN source_type = "External" THEN "Distributor" ELSE "System" END as type'))
            ->get();
            
        // Fetch distributions (asset_distributions)
        $stakeholderOwnerships = DB::table('asset_distributions')->get();

        $allSchools = DB::table('schools')
            ->select('id', 'school_id', 'name')
            ->orderBy('name')
            ->get();

        return view('inventory-setup', compact('districts', 'legislativeDistricts', 'quadrants', 'categories', 'items', 'allSchools', 'subItems', 'stakeholders', 'stakeholderOwnerships'));
    })->name('inventory.setup');


    // --- Schools Registry with Quadrant/LD Filters ---
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
        $allSchools = DB::table('schools')->select('id', 'school_id', 'name')->orderBy('name')->get();
        $allDistricts = DB::table('districts')->select('name')->orderBy('name')->pluck('name')->toArray();
        $allQuadrants = DB::table('quadrants')->select('name')->orderBy('name')->pluck('name')->toArray();
        
        $districtQuadrantMapping = DB::table('districts')
            ->join('quadrants', 'districts.quadrant_id', '=', 'quadrants.id')
            ->select('districts.name as district', 'quadrants.name as quadrant')
            ->get();

        $legislativeDistricts = DB::table('legislative_districts')->get();
        $quadrantsByLD = DB::table('quadrants')
            ->join('legislative_districts', 'quadrants.legislative_district_id', '=', 'legislative_districts.id')
            ->select('quadrants.name', 'legislative_districts.name as ld_name', 'legislative_districts.id as ld_id')
            ->get()
            ->groupBy('ld_name');

        return view('admin.schools', compact('schools', 'search', 'allSchools', 'allDistricts', 'allQuadrants', 'districtQuadrantMapping', 'legislativeDistricts', 'quadrantsByLD'));
    })->name('admin.schools');

    Route::delete('/admin/schools/{id}', function ($id) {
        try {
            DB::table('schools')->where('id', $id)->delete();
            return redirect()->route('admin.schools')->with('success', 'School successfully deleted.');
        } catch (\Exception $e) {
            return redirect()->route('admin.schools')->with('error', 'Failed to delete school.');
        }
    })->name('admin.schools.destroy');

    // --- System Logs ---
    Route::get('/admin/logs', function (Request $request) {
        $action = $request->query('action', 'All Actions');
        $query = DB::table('system_logs');
        if ($action !== 'All Actions') {
            $actionTypes = ['Create', 'Update', 'Delete', 'Others'];
            if (in_array($action, $actionTypes)) { $query->where('action_type', $action); }
            else { $query->where('module', $action); }
        }
        $logs = $query->orderBy('created_at', 'desc')->paginate(20);
        $logs->getCollection()->transform(function ($log) {
            $log->ph_time = Carbon::parse($log->created_at)->timezone('Asia/Manila');
            return $log;
        });
        return view('admin.logs', compact('action', 'logs'));
    })->name('admin.logs');

    // --- Asset Viewing & Explorer ---
    Route::get('/view-assets', function () { return view('assets.view-assets'); })->name('assets.view');
    Route::get('/view-all-assets', [AssetController::class, 'viewAll'])->name('assets.view_all');
    Route::get('/api/schools/{id}/assets', [AssetController::class, 'getSchoolAssets'])->name('api.schools.assets');
    Route::get('/assets/asset-history', [AssetController::class, 'history'])->name('assets.history');
    Route::get('/asset-explorer', [AssetController::class, 'explorer'])->name('assets.explorer');

    // --- QR & Tags ---
    Route::get('/assets/print-tags', function (Illuminate\Http\Request $request) {
        $count = $request->query('count', 24);
        $tags = [];
        for ($i = 0; $i < $count; $i++) { $tags[] = (string) Illuminate\Support\Str::uuid(); }
        return view('assets.print-tags', compact('tags'));
    })->name('assets.print_tags');

    Route::get('/scan', function (Illuminate\Http\Request $request) {
        $tag = $request->query('tag');
        if (!$tag) { return redirect('/dashboard')->withErrors(['scan' => 'No QR tag sequence found.']); }
        return redirect()->route('inventory.setup', ['mode' => 'add', 'scanned_tag' => $tag]);
    })->name('assets.scan');

    // --- Stakeholders / Recipients Sub-group ---
    Route::prefix('recipients')->group(function () {
        Route::get('/', function () { return view('recipients.recipients'); })->name('recipients.index');
        Route::get('/list', function () { return view('recipients.list'); })->name('recipients.list');
        Route::get('/explorer', function () { return view('recipients.explorer'); })->name('recipients.explorer');
        Route::get('/history', function () { return view('recipients.history'); })->name('recipients.history');
    });


    // --- Download Reports ---
    Route::match(['get', 'post'], '/reports/template', [\App\Http\Controllers\ImportController::class, 'downloadTemplate'])->name('assets.reports.template');
    Route::get('/reports', [\App\Http\Controllers\ImportController::class, 'show'])->name('assets.reports');
    Route::post('/reports', [\App\Http\Controllers\ImportController::class, 'process'])->name('assets.reports.process');
    Route::post('/reports/confirm', [\App\Http\Controllers\ImportController::class, 'confirm'])->name('assets.reports.confirm');

    // --- Building PIF Import ---
    Route::get('/buildings/import', [BuildingImportController::class, 'show'])->name('buildings.import');
    Route::post('/buildings/import/preview', [BuildingImportController::class, 'preview'])->name('buildings.import.preview');
    Route::post('/buildings/import/confirm', [BuildingImportController::class, 'confirm'])->name('buildings.import.confirm');

    // --- Building Registration ---
    Route::get('/register-building', function () {
        $allSchools = DB::table('schools')->select('id', 'school_id', 'name')->orderBy('name')->get();
        return view('register-building', compact('allSchools'));
    })->name('register.building');
    Route::post('/register-building', [\App\Http\Controllers\InventorySetupController::class, 'storeBuilding'])->name('register.building.store');

    // --- Registration & Items ---
    Route::get('/register-distributions', function () { return view('register-distributions'); });
    Route::get('/register-item', function () {
        set_time_limit(300);
        $categories = DB::table('categories')->orderBy('name')->get();
        $items = DB::table('items')->orderBy('name')->get();
        
        // Fetch sub-items (asset_sources) mapping description to name for the view
        $subItems = DB::table('asset_sources')
            ->select('id', 'item_id', DB::raw('COALESCE(description, "General") as name'), 'acquisition_source_id as distributor_id')
            ->get();
            
        // Fetch stakeholders (acquisition_sources) mapping source_type to type for the view
        $stakeholders = DB::table('acquisition_sources')
            ->select('id', 'name', DB::raw('CASE WHEN source_type = "External" THEN "Distributor" ELSE "System" END as type'))
            ->get();

        $allSchools = DB::table('schools')->select('id', 'school_id', 'name')->orderBy('name')->get();
        return view('register-item', compact('categories', 'items', 'stakeholders', 'subItems', 'allSchools'));
    })->name('register.item');
    Route::post('/register-item', [InventorySetupController::class, 'storeItem'])->name('register.item.store');
    Route::post('/inventory-setup/batch', [InventorySetupController::class, 'storeBatch'])->name('inventory.setup.storeBatch');
    Route::post('/api/recipients/add', [\App\Http\Controllers\RecipientRegistryController::class, 'add'])->name('recipients.add');

});
