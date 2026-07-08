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
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\AcquisitionSourceController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\AssetServiceController;

// --- Public Routes ---
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLoginForm'])->name('login.form');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    
    // --- Forgot Password Workflow ---
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetPin'])->name('password.email');
    
    Route::get('/verify-pin', [AuthController::class, 'showVerifyPin'])->name('password.verify');
    Route::post('/verify-pin', [AuthController::class, 'verifyPin'])->name('password.verify.post');
    
    Route::get('/reset-password', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/register', function () { return view('auth.register'); })->name('register');
Route::post('/register', [RegistrationController::class, 'register'])->name('register.post');
Route::get('/verify', [RegistrationController::class, 'verify'])->name('verify');
Route::post('/otp/send', [RegistrationController::class, 'sendOtp'])->name('otp.send');
Route::post('/otp/verify', [RegistrationController::class, 'verifyOtp'])->name('otp.verify');

// Redirect /login GET to root
Route::get('/login', function() { return redirect('/'); });

// --- Super Admin Only Routes ---
Route::middleware(['auth', 'role:super_admin'])->group(function () {
    Route::get('/admin/user-management', [UserManagementController::class, 'index'])->name('admin.user-management');
    Route::post('/admin/users/{id}/approve', [UserManagementController::class, 'approve'])->name('admin.users.approve');
    Route::delete('/admin/users/{id}/reject', [UserManagementController::class, 'reject'])->name('admin.users.reject');
    Route::patch('/admin/users/{id}/role', [UserManagementController::class, 'updateRole'])->name('admin.users.role');
    Route::patch('/admin/users/{id}/block', [UserManagementController::class, 'blockUser'])->name('admin.users.block');
    Route::patch('/admin/users/{id}/unblock', [UserManagementController::class, 'unblock'])->name('admin.users.unblock');
    Route::delete('/admin/users/{id}', [UserManagementController::class, 'destroy'])->name('admin.users.destroy');
    
    // Employee Management Actions (Super Admin Only)
    Route::post('/admin/employees', [EmployeeController::class, 'store'])->name('admin.employees.store');
    Route::post('/admin/employees/{id}/update', [EmployeeController::class, 'update'])->name('admin.employees.update');
    Route::post('/admin/employee-management/store', [EmployeeController::class, 'store'])->name('admin.employee-management.store');
    Route::post('/admin/employee-management/{id}/update', [EmployeeController::class, 'update'])->name('admin.employee-management.update');
    
    // Source Management Actions (Super Admin Only)
    Route::post('/admin/sources', [AcquisitionSourceController::class, 'store'])->name('admin.sources.store');
    Route::post('/admin/sources/{id}/update', [AcquisitionSourceController::class, 'update'])->name('admin.sources.update');

    // Supplier Management Actions (Super Admin Only)
    Route::post('/admin/suppliers', [SupplierController::class, 'store'])->name('admin.suppliers.store');
    Route::post('/admin/suppliers/{id}/update', [SupplierController::class, 'update'])->name('admin.suppliers.update');
});

// --- Protected Admin Routes ---
Route::middleware('auth')->group(function () {
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/dashboard/growth-data', [DashboardController::class, 'getGrowthData'])->name('api.dashboard.growth_data');
    Route::post('/dashboard/quick-asset', [DashboardController::class, 'storeQuickAsset'])->name('inventory.dashboard.store')->middleware('role:super_admin,admin');

    // --- Dark Mode Preference ---
    Route::post('/user/dark-mode', function (Request $request) {
        $user = auth()->user();
        $user->dark_mode = !$user->dark_mode;
        $user->save();
        return response()->json(['dark_mode' => $user->dark_mode]);
    })->name('user.dark-mode');

    // --- Polymorphic Notifications API ---
    Route::get('/api/notifications', function (Illuminate\Http\Request $request) {
        $user = auth()->user();
        $page = max(1, (int) $request->query('page', 1));
        $notifications = $user->notifications()->latest()->paginate(20, ['*'], 'page', $page);

        return response()->json([
            'notifications' => $notifications->items(),
            'unreadCount'   => $user->unreadNotifications()->count(),
            'pagination'    => [
                'current_page' => $notifications->currentPage(),
                'last_page'    => $notifications->lastPage(),
                'total'        => $notifications->total(),
            ],
        ]);
    })->name('api.notifications.index');

    Route::post('/api/notifications/read-all', function () {
        $user = auth()->user();
        $user->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    })->name('api.notifications.read_all');

    Route::post('/api/notifications/{id}/read', function ($id) {
        $user = auth()->user();
        $notification = $user->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->markAsRead();
        }
        return response()->json(['success' => true]);
    })->name('api.notifications.read');
    
    Route::post('/api/notifications/custom', function (Illuminate\Http\Request $request) {
        if (auth()->user()->role !== 'super_admin') abort(403);
        $request->validate(['message' => 'required|string']);
        
        $dummyData = (object)[
            'title' => 'System Announcement',
            'message' => \Illuminate\Support\Str::limit($request->message, 50),
            'detailed_message' => $request->message
        ];
        
        $users = \App\Models\User::where('approved', true)->get();
        foreach ($users as $u) {
            $u->notify(new \App\Notifications\SystemAnnouncementNotification($dummyData));
        }
        return response()->json(['success' => true]);
    })->name('api.notifications.custom');

    // --- Global Notice Board API ---
    Route::post('/api/global-notice', function (Illuminate\Http\Request $request) {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'content' => 'nullable|string',
            'link' => 'nullable|string',
            'link_label' => 'nullable|string|max:255',
            'active' => 'nullable|boolean'
        ]);

        // Deactivate all previous active notices
        \App\Models\GlobalNotice::where('active', true)->update(['active' => false]);

        $content = $request->input('content');
        if (!empty(trim($content ?? ''))) {
            $notice = \App\Models\GlobalNotice::create([
                'content' => $content,
                'link' => $request->input('link'),
                'link_label' => $request->input('link_label'),
                'active' => $request->input('active', true) ?? true,
                'created_by' => auth()->id()
            ]);
            return response()->json(['success' => true, 'notice' => $notice]);
        }

        return response()->json(['success' => true, 'notice' => null]);
    })->name('api.global-notice.store');
    
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
            
        // Fetch assignments (asset_assignments)
        $stakeholderOwnerships = DB::table('asset_assignments')->get();

        $schools = DB::table('schools')->select('id', 'school_id', 'name', 'type', 'location')->get()->map(function($s) {
            $s->is_office = false;
            $s->global_id = $s->school_id;
            return $s;
        });
        
        $offices = DB::table('offices')->select('id', 'office_id as school_id', 'name', 'type', 'location')->get()->map(function($o) {
            $o->is_office = true;
            $o->global_id = $o->school_id;
            $o->region = null;
            $o->division = null;
            return $o;
        });

        $allSchools = $schools->concat($offices)->sortBy('name')->values();

        $allCustodians = DB::table('employees')
            ->select('id', 'first_name', 'middle_name', 'last_name', 'position', 'employee_id', 'status', 'school_id', 'office_id')
            ->orderBy('last_name')
            ->get();

        $inventoryController = app(\App\Http\Controllers\InventorySetupController::class);
        $unassignedAssets = $inventoryController->getUnassignedAssets(request())->getData(true)['assets'] ?? [];
        $unassignedBuildings = $inventoryController->getUnassignedBuildings(request())->getData(true)['assets'] ?? [];
        $procurementModes = DB::table('procurement_modes')->orderBy('name')->get();

        return view('inventory-setup', compact('districts', 'legislativeDistricts', 'quadrants', 'categories', 'items', 'allSchools', 'subItems', 'stakeholders', 'stakeholderOwnerships', 'allCustodians', 'unassignedAssets', 'unassignedBuildings', 'procurementModes'));
    })->name('inventory.setup')->middleware('role:super_admin,admin');


    // --- Schools Registry ---
    Route::get('/admin/schools', function () {
        return view('admin.schools');
    })->name('admin.schools');

    // --- Offices Registry ---
    Route::get('/admin/offices', function () {
        return view('admin.offices');
    })->name('admin.offices');

    // --- Sources Registry ---
    Route::get('/admin/sources', [AcquisitionSourceController::class, 'managementIndex'])->name('admin.sources');
    Route::get('/admin/sources/{id}', [AcquisitionSourceController::class, 'managementProfile'])->name('admin.sources.profile');

    // --- Suppliers Registry ---
    Route::get('/admin/suppliers', [SupplierController::class, 'index'])->name('admin.suppliers');
    Route::get('/admin/suppliers/{id}', [SupplierController::class, 'profile'])->name('admin.suppliers.profile');
    Route::get('/api/suppliers/search', [SupplierController::class, 'apiSearch'])->name('api.suppliers.search');

    // --- Employee (formerly Custodian) Registry ---
    Route::get('/admin/employees', [EmployeeController::class, 'index'])->name('admin.employees');
    Route::get('/admin/custodians', [EmployeeController::class, 'index'])->name('admin.custodians');
    Route::get('/admin/employee-management', [EmployeeController::class, 'index'])->name('admin.employee-management');
    Route::get('/api/employees/search', [EmployeeController::class, 'searchEmployees'])->name('api.employees.search');
    Route::get('/api/locations/search', [EmployeeController::class, 'searchLocations'])->name('api.locations.search');
    Route::get('/api/classifications/search', [EmployeeController::class, 'searchClassifications'])->name('api.classifications.search');
    Route::get('/api/categories/search', [EmployeeController::class, 'searchCategories'])->name('api.categories.search');
    Route::get('/api/acquisition-sources/search', [EmployeeController::class, 'searchAcquisitionSources'])->name('api.acquisition_sources.search');
    Route::get('/api/acquisition-sources/list', [AcquisitionSourceController::class, 'apiSearch'])->name('api.acquisition_sources.list');

    Route::post('/api/employees/preview', [\App\Http\Controllers\ReportDownloadController::class, 'getCustodiansPreview'])->name('api.employees.preview');
    Route::get('/api/employees/filters', [\App\Http\Controllers\ReportDownloadController::class, 'getCustodiansFilterOptions'])->name('api.employees.filters');
    Route::get('/admin/employees/{id}', [EmployeeController::class, 'profile'])->name('employees.profile');
    Route::get('/admin/custodians/{id}', [EmployeeController::class, 'profile'])->name('custodians.profile');
    Route::get('/admin/employee-management/{id}', [EmployeeController::class, 'profile'])->name('admin.employee-management.profile');
    Route::post('/admin/employees/{id}/photo', [EmployeeController::class, 'uploadPhoto'])->name('admin.employees.photo.upload');



    Route::delete('/admin/schools/{id}', function ($id) {
        try {
            DB::table('schools')->where('id', $id)->delete();
            return redirect()->route('admin.schools')->with('success', 'School successfully deleted.');
        } catch (\Exception $e) {
            return redirect()->route('admin.schools')->with('error', 'Failed to delete school.');
        }
    })->name('admin.schools.destroy')->middleware('role:super_admin,admin');

    // --- System Logs ---
    Route::get('/admin/logs', function (Request $request) {
        $action = $request->query('action', 'All Actions');
        $date = $request->query('date');
        $query = DB::table('system_logs');
        if ($action !== 'All Actions') {
            $actionTypes = ['Create', 'Update', 'Delete', 'Others'];
            if (in_array($action, $actionTypes)) { $query->where('action_type', $action); }
            else { $query->where('module', $action); }
        }
        if ($date) {
            $query->whereDate('created_at', $date);
        }
        $logs = $query->orderBy('created_at', 'desc')->paginate(20);
        $logs->getCollection()->transform(function ($log) {
            $log->ph_time = Carbon::parse($log->created_at)->timezone('Asia/Manila');
            return $log;
        });
        return view('admin.logs', compact('action', 'date', 'logs'));
    })->name('admin.logs');

    // --- Asset Viewing & Explorer ---
    Route::get('/view-assets', function () { return view('assets.view-assets'); })->name('assets.view');

    Route::get('/api/schools/{id}/assets', [AssetController::class, 'getSchoolAssets'])->name('api.schools.assets');
    Route::get('/assets/asset-history', [AssetController::class, 'history'])->name('assets.history');
    Route::get('/assets/{id}/profile', [AssetController::class, 'profile'])->name('assets.profile');
    Route::post('/assets/{id}/update', [AssetController::class, 'update'])->name('assets.update')->middleware('role:super_admin,admin');
    Route::post('/assets/{id}/transfer', [AssetController::class, 'transfer'])->name('assets.transfer')->middleware('role:super_admin,admin');
    Route::post('/assets/{id}/return', [AssetController::class, 'returnAmu'])->name('assets.return')->middleware('role:super_admin,admin');
    Route::post('/assets/{id}/return-source', [AssetController::class, 'returnSource'])->name('assets.return_source')->middleware('role:super_admin,admin');
    Route::post('/assets/{id}/photo', [AssetController::class, 'uploadPhoto'])->name('assets.photo.upload')->middleware('role:super_admin,admin');

    // --- Asset Service (Repair Tracking) ---
    Route::get('/asset-service', [AssetServiceController::class, 'index'])->name('asset.service.index');
    Route::get('/asset-service/{id}', [AssetServiceController::class, 'show'])->name('asset.service.show');
    Route::post('/asset-service/{id}/return-custodian', [AssetServiceController::class, 'returnToCustodian'])->name('asset.service.return-custodian')->middleware('role:super_admin,admin');
    Route::post('/asset-service/{id}/return-amu', [AssetServiceController::class, 'returnToAmu'])->name('asset.service.return-amu')->middleware('role:super_admin,admin');
    Route::delete('/assets/{id}/photo', [AssetController::class, 'removePhoto'])->name('assets.photo.remove')->middleware('role:super_admin,admin');
    Route::post('/assets/{id}/document', [AssetController::class, 'uploadDocument'])->name('assets.document.upload')->middleware('role:super_admin,admin');
    Route::delete('/assets/document/{docId}', [AssetController::class, 'removeDocument'])->name('assets.document.remove')->middleware('role:super_admin,admin');
    Route::get('/asset-explorer', [AssetController::class, 'explorer'])->name('assets.explorer');

    // --- Print QR Stickers ---
    Route::get('/assets/print-stickers', function () {
        $assets = DB::table('asset_assignments as ad')
            ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
            ->join('items', 'asrc.item_id', '=', 'items.id')
            ->leftJoin('categories as cat', 'items.category_id', '=', 'cat.id')
            ->leftJoin('classifications as class', 'cat.classification_id', '=', 'class.id')
            ->leftJoin('employees as emp', 'ad.employee_id', '=', 'emp.id')
            ->leftJoin('offices as off', 'emp.office_id', '=', 'off.id')
            ->leftJoin('schools as sch', 'emp.school_id', '=', 'sch.id')
            ->select(
                'ad.id',
                'ad.property_number',
                'asrc.condition',
                'items.name as item_name',
                'class.name as classification_name',
                DB::raw('COALESCE(asrc.description, items.name) as description'),
                DB::raw('COALESCE(sch.name, off.name) as location')
            )
            ->orderBy('ad.id', 'desc')
            ->get();

        return view('assets.print-stickers', compact('assets'));
    })->name('assets.print_stickers');

    Route::get('/api/assets/print-list', function () {
        $assets = DB::table('asset_assignments as ad')
            ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
            ->join('items', 'asrc.item_id', '=', 'items.id')
            ->leftJoin('categories as cat', 'items.category_id', '=', 'cat.id')
            ->leftJoin('classifications as class', 'cat.classification_id', '=', 'class.id')
            ->leftJoin('employees as emp', 'ad.employee_id', '=', 'emp.id')
            ->leftJoin('offices as off', 'emp.office_id', '=', 'off.id')
            ->leftJoin('schools as sch', 'emp.school_id', '=', 'sch.id')
            ->select(
                'ad.id',
                'ad.property_number',
                'asrc.condition',
                'items.name as item_name',
                'class.name as classification_name',
                DB::raw('COALESCE(asrc.description, items.name) as description'),
                DB::raw('COALESCE(sch.name, off.name) as location')
            )
            ->orderBy('ad.id', 'desc')
            ->get();

        return response()->json(['assets' => $assets]);
    })->name('api.assets.print_list');

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
    Route::get('/reports', [\App\Http\Controllers\ImportController::class, 'show'])->name('assets.reports')->middleware('role:super_admin,admin');
    Route::post('/reports', [\App\Http\Controllers\ImportController::class, 'process'])->name('assets.reports.process')->middleware('role:super_admin,admin');
    Route::post('/reports/confirm', [\App\Http\Controllers\ImportController::class, 'confirm'])->name('assets.reports.confirm')->middleware('role:super_admin,admin');
    
    Route::post('/api/reports/preview', [\App\Http\Controllers\ReportDownloadController::class, 'getPreview'])->name('api.reports.preview');
    Route::get('/api/reports/filters', [\App\Http\Controllers\ReportDownloadController::class, 'getFilterOptions'])->name('api.reports.filters');
    Route::get('/api/assets/suggestions', [\App\Http\Controllers\ReportDownloadController::class, 'getAssetSuggestions'])->name('api.assets.suggestions');
    Route::post('/reports/download-rpc', [\App\Http\Controllers\ReportDownloadController::class, 'download'])->name('assets.reports.download_rpc');
    Route::get('/download-documentation-template/{type}', [\App\Http\Controllers\ReportDownloadController::class, 'downloadDocTemplate'])->name('admin.download_doc_template');

    // --- Inventory Management (Edit) ---
    Route::get('/api/inventory/dropdown-data', [InventorySetupController::class, 'getDropdownData'])->name('api.inventory.dropdown_data');
    Route::post('/api/inventory/edit-preview', [\App\Http\Controllers\ReportDownloadController::class, 'getEditPreview'])->name('api.inventory.edit_preview');
    Route::post('/inventory-setup/edit-batch', [\App\Http\Controllers\InventorySetupController::class, 'updateBatch'])->name('inventory.setup.updateBatch');


    // --- Building Management (View) ---
    Route::post('/api/buildings/preview', [\App\Http\Controllers\ReportDownloadController::class, 'getBuildingsPreview'])->name('api.buildings.preview');
    Route::get('/api/buildings/filters', [\App\Http\Controllers\ReportDownloadController::class, 'getBuildingsFilterOptions'])->name('api.buildings.filters');

    // --- Building Editor (Bulk Edit) ---
    Route::post('/api/buildings/edit-preview', [\App\Http\Controllers\InventorySetupController::class, 'getBuildingEditPreview'])->name('api.buildings.edit_preview');
    Route::post('/api/buildings/update-batch', [\App\Http\Controllers\InventorySetupController::class, 'updateBuildingBatch'])->name('api.buildings.updateBatch');

    // --- Building PIF Import (must be before /buildings/{id} wildcard) ---
    Route::get('/buildings/import', [BuildingImportController::class, 'show'])->name('buildings.import');
    Route::post('/buildings/import/preview', [BuildingImportController::class, 'preview'])->name('buildings.import.preview');
    Route::post('/buildings/import/confirm', [BuildingImportController::class, 'confirm'])->name('buildings.import.confirm');

    Route::get('/buildings/{id}', [\App\Http\Controllers\BuildingController::class, 'profile'])->name('buildings.profile');
    Route::post('/buildings/{id}/update', [\App\Http\Controllers\BuildingController::class, 'update'])->name('buildings.update');
    Route::get('/schools/{id}', [\App\Http\Controllers\SchoolController::class, 'profile'])->name('schools.profile');
    Route::get('/offices/{id}', [\App\Http\Controllers\OfficeController::class, 'profile'])->name('offices.profile');


    // --- School Management (View) ---
    Route::post('/api/schools/preview', [\App\Http\Controllers\ReportDownloadController::class, 'getSchoolsPreview'])->name('api.schools.preview');
    Route::get('/api/schools/filters', [\App\Http\Controllers\ReportDownloadController::class, 'getSchoolsFilterOptions'])->name('api.schools.filters');

    // --- Office Management (View) ---
    Route::post('/api/offices/preview', [\App\Http\Controllers\ReportDownloadController::class, 'getOfficesPreview'])->name('api.offices.preview');
    Route::get('/api/offices/filters', [\App\Http\Controllers\ReportDownloadController::class, 'getOfficesFilterOptions'])->name('api.offices.filters');
    Route::get('/api/offices/{id}/details', function ($id) {
        $office = DB::table('offices')
            ->leftJoin('schools', 'offices.school_id', '=', 'schools.id')
            ->where('offices.id', $id)
            ->select(
                'offices.id',
                'offices.school_id',
                'offices.name',
                'offices.office_code',
                'offices.room_number',
                'schools.name as school_name'
            )
            ->first();

        if (!$office) {
            return response()->json(['error' => 'Office not found'], 404);
        }

        $assets = DB::table('asset_assignments')
            ->join('asset_sources', 'asset_assignments.asset_source_id', '=', 'asset_sources.id')
            ->leftJoin('items', 'asset_sources.item_id', '=', 'items.id')
            ->where('asset_assignments.office_id', $id)
            ->select(
                'asset_assignments.property_number',
                'items.name as article',
                'asset_sources.description',
                'asset_assignments.acquisition_cost',
                'asset_assignments.condition'
            )
            ->get();

        $buildings = DB::table('building_records')
            ->leftJoin('building_specs', 'building_records.building_spec_id', '=', 'building_specs.id')
            ->leftJoin('building_types', 'building_specs.building_type_id', '=', 'building_types.id')
            ->where('building_records.school_id', $office->school_id)
            ->select(
                'building_records.property_number',
                'building_types.name as type',
                'building_records.acquisition_cost'
            )
            ->get();

        return response()->json([
            'office' => $office,
            'assets' => $assets,
            'buildings' => $buildings
        ]);
    })->name('api.offices.details');


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
    
    Route::get('/assign-asset', function () {
        return redirect()->route('inventory.setup');
    })->name('assign.asset');

    Route::get('/assign-building', function () {
        return redirect()->route('inventory.setup');
    })->name('assign.building');

    Route::post('/register-item', [InventorySetupController::class, 'storeItem'])->name('register.item.store');
    Route::post('/inventory-setup/batch', [InventorySetupController::class, 'storeBatch'])->name('inventory.setup.storeBatch');
    
    Route::get('/api/unassigned-assets', [InventorySetupController::class, 'getUnassignedAssets'])->name('api.unassigned_assets');
    Route::post('/assign-asset', [InventorySetupController::class, 'assignItem'])->name('assign_asset.store');
    Route::post('/assign-asset/batch', [InventorySetupController::class, 'assignBatch'])->name('assign_asset.storeBatch');
    
    Route::get('/api/unassigned-buildings', [InventorySetupController::class, 'getUnassignedBuildings'])->name('api.unassigned_buildings');
    Route::post('/assign-building/batch', [InventorySetupController::class, 'assignBuildingBatch'])->name('assign_building.storeBatch');

    Route::post('/api/recipients/add', [\App\Http\Controllers\RecipientRegistryController::class, 'add'])->name('recipients.add');

});
