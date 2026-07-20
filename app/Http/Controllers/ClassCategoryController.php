<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Classification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ClassCategoryController extends Controller
{
    public function index()
    {
        $classifications = Classification::withCount('categories')
            ->orderByDesc('created_at')
            ->get();

        $user = Auth::user();
        $categoriesQuery = Category::with('classification')
            ->leftJoin('items as i', 'categories.id', '=', 'i.category_id')
            ->leftJoin('asset_sources as asrc', 'i.id', '=', 'asrc.item_id');

        if ($user && $user->isSchoolSystem()) {
            $schoolId = $user->school_id;
            $categoriesQuery->leftJoin('asset_assignments as ad', function($join) use ($schoolId) {
                $join->on('asrc.id', '=', 'ad.asset_source_id')
                     ->where(function($q) use ($schoolId) {
                         $q->where('ad.school_id', $schoolId)
                           ->orWhereExists(function ($sub) use ($schoolId) {
                               $sub->select(DB::raw(1))
                                   ->from('employees')
                                   ->whereColumn('employees.id', 'ad.employee_id')
                                   ->where('employees.school_id', $schoolId);
                           });
                      });
            });
        } else {
            $categoriesQuery->leftJoin('asset_assignments as ad', 'asrc.id', '=', 'ad.asset_source_id');
        }

        $categories = $categoriesQuery->select('categories.*')
            ->selectRaw('COUNT(ad.id) as assets_count')
            ->groupBy('categories.id', 'categories.name', 'categories.classification_id', 'categories.see_category_code', 'categories.ppe_category_code', 'categories.created_at', 'categories.updated_at')
            ->orderByDesc('categories.created_at')
            ->get();

        return view('admin.class-category.index', compact('classifications', 'categories'));
    }

    public function showClassification($id)
    {
        $classification = Classification::findOrFail($id);

        $user = Auth::user();
        $categoriesQuery = Category::where('classification_id', $id)
            ->leftJoin('items as i', 'categories.id', '=', 'i.category_id')
            ->leftJoin('asset_sources as asrc', 'i.id', '=', 'asrc.item_id');

        if ($user && $user->isSchoolSystem()) {
            $schoolId = $user->school_id;
            $categoriesQuery->leftJoin('asset_assignments as ad', function($join) use ($schoolId) {
                $join->on('asrc.id', '=', 'ad.asset_source_id')
                     ->where(function($q) use ($schoolId) {
                         $q->where('ad.school_id', $schoolId)
                           ->orWhereExists(function ($sub) use ($schoolId) {
                               $sub->select(DB::raw(1))
                                   ->from('employees')
                                   ->whereColumn('employees.id', 'ad.employee_id')
                                   ->where('employees.school_id', $schoolId);
                           });
                      });
            });
        } else {
            $categoriesQuery->leftJoin('asset_assignments as ad', 'asrc.id', '=', 'ad.asset_source_id');
        }

        $categories = $categoriesQuery->select('categories.*')
            ->selectRaw('COUNT(ad.id) as assets_count')
            ->groupBy('categories.id', 'categories.name', 'categories.classification_id', 'categories.see_category_code', 'categories.ppe_category_code', 'categories.created_at', 'categories.updated_at')
            ->orderByDesc('categories.created_at')
            ->get();

        return view('admin.class-category.class-profile', compact('classification', 'categories'));
    }

    public function showCategory($id)
    {
        $category = Category::with('classification')->findOrFail($id);

        $query = DB::table('asset_assignments as ad')
            ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
            ->join('items as i', 'asrc.item_id', '=', 'i.id')
            ->leftJoin('employees as e', 'ad.employee_id', '=', 'e.id')
            ->leftJoin('schools as s', 'ad.school_id', '=', 's.id')
            ->leftJoin('offices as o', 'ad.office_id', '=', 'o.id')
            ->where('i.category_id', $id);

        $user = Auth::user();
        if ($user && $user->isSchoolSystem()) {
            $schoolId = $user->school_id;
            $query->where(function($q) use ($schoolId) {
                $q->where('ad.school_id', $schoolId)
                  ->orWhereExists(function ($sub) use ($schoolId) {
                      $sub->select(DB::raw(1))
                          ->from('employees')
                          ->whereColumn('employees.id', 'ad.employee_id')
                          ->where('employees.school_id', $schoolId);
                  });
            });
        }

        $assets = $query->select(
                'ad.id',
                'ad.property_number',
                'ad.serial_number',
                'ad.acquisition_date',
                'ad.acquisition_cost as asset_cost',
                'i.name as item_name',
                'asrc.condition',
                DB::raw("CONCAT(COALESCE(e.first_name,''), ' ', COALESCE(e.last_name,'')) as custodian_name"),
                DB::raw("COALESCE(s.name, o.name) as location_name")
            )
            ->orderByDesc('ad.acquisition_date')
            ->paginate(50, ['*'], 'assets_page');

        return view('admin.class-category.category-profile', compact('category', 'assets'));
    }

    public function storeClassification(Request $request)
    {
        if (!Auth::user()->isAdmin() || !Auth::user()->isMainSystem()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:classifications,name',
        ]);

        Classification::create($validated);

        return redirect()->back()->with('success', 'Classification created successfully.');
    }

    public function storeCategory(Request $request)
    {
        if (!Auth::user()->isAdmin() || !Auth::user()->isMainSystem()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'classification_id'        => 'required|exists:classifications,id',
            'name'                     => 'required|string|max:255|unique:categories,name',
            'see_category_code'        => 'required|string|max:255',
            'ppe_category_code'        => 'required|string|max:255',
        ]);

        Category::create($validated);

        return redirect()->back()->with('success', 'Category created successfully.');
    }

    public function updateClassification(Request $request, $id)
    {
        if (!Auth::user()->isAdmin() || !Auth::user()->isMainSystem()) {
            abort(403, 'Unauthorized action.');
        }

        $classification = Classification::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:classifications,name,' . $id,
        ]);

        $classification->update($validated);

        DB::table('system_logs')->insert([
            'user' => Auth::user()->name,
            'activity' => "Updated classification: {$classification->name}",
            'module' => 'Class & Category',
            'action_type' => 'Update',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Classification updated successfully.');
    }

    public function updateCategory(Request $request, $id)
    {
        if (!Auth::user()->isAdmin() || !Auth::user()->isMainSystem()) {
            abort(403, 'Unauthorized action.');
        }

        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'classification_id'        => 'required|exists:classifications,id',
            'name'                     => 'required|string|max:255|unique:categories,name,' . $id,
            'see_category_code'        => 'required|string|max:255',
            'ppe_category_code'        => 'required|string|max:255',
        ]);

        $category->update($validated);

        DB::table('system_logs')->insert([
            'user' => Auth::user()->name,
            'activity' => "Updated category: {$category->name}",
            'module' => 'Class & Category',
            'action_type' => 'Update',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Category updated successfully.');
    }

    public function hardDeleteClassification($id)
    {
        if (!Auth::user()->isSuperAdmin() || !Auth::user()->isMainSystem()) {
            abort(403, 'Unauthorized action.');
        }

        $classification = Classification::findOrFail($id);

        /** @var \App\Services\DeletionEligibilityService $svc */
        $svc = app(\App\Services\DeletionEligibilityService::class);
        $reasons = $svc->checkClassification($classification->id);

        if (!empty($reasons)) {
            return back()->with('error', 'Cannot permanently delete: ' . implode(' ', $reasons));
        }

        DB::transaction(function () use ($classification) {
            Classification::query()->lockForUpdate()->find($classification->id);

            $name = $classification->name;
            $classification->delete();

            DB::table('system_logs')->insert([
                'user'        => Auth::user()->name,
                'action_type' => 'Delete',
                'module'      => 'Class & Category',
                'activity'    => "Classification \"{$name}\" was permanently deleted from the system.",
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        });

        return redirect()->route('admin.class-category.index')->with('success', 'Classification permanently deleted.');
    }

    public function hardDeleteCategory($id)
    {
        if (!Auth::user()->isSuperAdmin() || !Auth::user()->isMainSystem()) {
            abort(403, 'Unauthorized action.');
        }

        $category = Category::findOrFail($id);

        /** @var \App\Services\DeletionEligibilityService $svc */
        $svc = app(\App\Services\DeletionEligibilityService::class);
        $reasons = $svc->checkCategory($category->id);

        if (!empty($reasons)) {
            return back()->with('error', 'Cannot permanently delete: ' . implode(' ', $reasons));
        }

        DB::transaction(function () use ($category) {
            Category::query()->lockForUpdate()->find($category->id);

            $classId = $category->classification_id;
            $name    = $category->name;
            $category->delete();

            DB::table('system_logs')->insert([
                'user'        => Auth::user()->name,
                'action_type' => 'Delete',
                'module'      => 'Class & Category',
                'activity'    => "Category \"{$name}\" was permanently deleted from the system.",
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        });

        return redirect()->route('admin.class-category.index')->with('success', 'Category permanently deleted.');
    }
}
