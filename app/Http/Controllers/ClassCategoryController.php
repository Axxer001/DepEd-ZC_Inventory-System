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
            ->orderBy('name')
            ->get();

        $categories = Category::with('classification')
            ->leftJoin('items as i', 'categories.id', '=', 'i.category_id')
            ->leftJoin('asset_sources as asrc', 'i.id', '=', 'asrc.item_id')
            ->leftJoin('asset_assignments as ad', 'asrc.id', '=', 'ad.asset_source_id')
            ->select('categories.*')
            ->selectRaw('COUNT(ad.id) as assets_count')
            ->groupBy('categories.id', 'categories.name', 'categories.classification_id', 'categories.see_category_code', 'categories.ppe_category_code', 'categories.created_at', 'categories.updated_at')
            ->orderBy('categories.name')
            ->get();

        return view('admin.class-category.index', compact('classifications', 'categories'));
    }

    public function showClassification($id)
    {
        $classification = Classification::findOrFail($id);

        $categories = Category::where('classification_id', $id)
            ->leftJoin('items as i', 'categories.id', '=', 'i.category_id')
            ->leftJoin('asset_sources as asrc', 'i.id', '=', 'asrc.item_id')
            ->leftJoin('asset_assignments as ad', 'asrc.id', '=', 'ad.asset_source_id')
            ->select('categories.*')
            ->selectRaw('COUNT(ad.id) as assets_count')
            ->groupBy('categories.id', 'categories.name', 'categories.classification_id', 'categories.see_category_code', 'categories.ppe_category_code', 'categories.created_at', 'categories.updated_at')
            ->orderBy('categories.name')
            ->get();

        return view('admin.class-category.class-profile', compact('classification', 'categories'));
    }

    public function showCategory($id)
    {
        $category = Category::with('classification')->findOrFail($id);

        $assets = DB::table('asset_assignments as ad')
            ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
            ->join('items as i', 'asrc.item_id', '=', 'i.id')
            ->leftJoin('employees as e', 'ad.employee_id', '=', 'e.id')
            ->leftJoin('schools as s', 'ad.school_id', '=', 's.id')
            ->leftJoin('offices as o', 'ad.office_id', '=', 'o.id')
            ->where('i.category_id', $id)
            ->select(
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
}
