<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    public function index()
    {
        return view('admin.supplier-management');
    }

    public function profile($id)
    {
        $supplier = Supplier::findOrFail($id);

        $stats = DB::table('asset_sources as asrc')
            ->where('asrc.supplier_id', $id)
            ->selectRaw('COUNT(asrc.id) as total_assets, COALESCE(SUM(asrc.asset_cost * asrc.quantity), 0) as total_value')
            ->first();

        $assets = DB::table('asset_sources as asrc')
            ->join('asset_assignments as ad', 'ad.asset_source_id', '=', 'asrc.id')
            ->join('items as i', 'asrc.item_id', '=', 'i.id')
            ->join('categories as cat', 'i.category_id', '=', 'cat.id')
            ->leftJoin('employees as e', 'ad.employee_id', '=', 'e.id')
            ->leftJoin('schools as s', 'e.school_id', '=', 's.id')
            ->leftJoin('offices as o', 'e.office_id', '=', 'o.id')
            ->where('asrc.supplier_id', $id)
            ->select(
                'ad.id',
                'ad.property_number',
                'ad.serial_number',
                'ad.acquisition_date',
                'asrc.asset_cost',
                'asrc.acceptance_date',
                'asrc.created_at as registered_at',
                'i.name as item_name',
                'cat.name as category_name',
                'asrc.condition',
                'asrc.quantity',
                DB::raw('COALESCE(s.name, o.name) as location_name'),
                DB::raw("CONCAT(COALESCE(e.first_name,''), ' ', COALESCE(e.last_name,'')) as custodian_name")
            )
            ->orderByDesc('asrc.acceptance_date')
            ->paginate(50, ['*'], 'assets_page');

        // Service history: transfers related to assets from this supplier
        $history = DB::table('asset_transfers as at')
            ->join('asset_assignments as ad', 'at.asset_assignment_id', '=', 'ad.id')
            ->join('asset_sources as asrc', 'ad.asset_source_id', '=', 'asrc.id')
            ->join('items as i', 'asrc.item_id', '=', 'i.id')
            ->leftJoin('employees as from_emp', 'at.from_custodian_id', '=', 'from_emp.id')
            ->leftJoin('employees as to_emp', 'at.to_custodian_id', '=', 'to_emp.id')
            ->where('asrc.supplier_id', $id)
            ->where(function ($q) {
                $q->whereIn('at.transfer_type', ['Return to Source', 'Return to Supplier', 'Returned from Supplier', 'Return to Custodian'])
                  ->orWhere(function ($sub) {
                      $sub->where('at.transfer_type', 'Return')
                          ->whereNotNull('at.repair_status');
                  });
            })
            ->select(
                'at.id',
                'at.transfer_type',
                'at.transfer_date',
                'at.return_date',
                'at.remarks',
                'ad.property_number',
                'ad.serial_number',
                'asrc.condition',
                'i.name as item_name',
                DB::raw("CONCAT(COALESCE(from_emp.first_name,''), ' ', COALESCE(from_emp.last_name,'')) as from_custodian"),
                DB::raw("CONCAT(COALESCE(to_emp.first_name,''), ' ', COALESCE(to_emp.last_name,'')) as to_custodian"),
                'at.created_at'
            )
            ->orderByDesc('at.transfer_date')
            ->orderByDesc('at.created_at')
            ->paginate(50, ['*'], 'history_page');

        return view('admin.supplier-management-profile', compact('supplier', 'stats', 'assets', 'history'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255|unique:suppliers,name',
            'supplier_personnel' => 'nullable|string|max:255',
            'service_center'     => 'nullable|string|max:255',
            'contact_number'     => 'nullable|string|max:50',
            'contact_email'      => 'nullable|email|max:255',
        ]);

        Supplier::create($validated);

        return redirect()->route('admin.suppliers')->with('success', 'Supplier created successfully.');
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $validated = $request->validate([
            'name'               => 'required|string|max:255|unique:suppliers,name,' . $id,
            'supplier_personnel' => 'nullable|string|max:255',
            'service_center'     => 'nullable|string|max:255',
            'contact_number'     => 'nullable|string|max:50',
            'contact_email'      => 'nullable|email|max:255',
        ]);

        $supplier->update($validated);

        return redirect()->back()->with('success', 'Supplier updated successfully.');
    }

    public function apiSearch(Request $request)
    {
        $q = $request->input('q', '');

        $suppliers = DB::table('suppliers as sup')
            ->leftJoin(DB::raw('(SELECT supplier_id, COUNT(id) as cnt FROM asset_sources GROUP BY supplier_id) as src'), 'src.supplier_id', '=', 'sup.id')
            ->when($q !== '', fn($query) => $query->where('sup.name', 'like', '%' . $q . '%')
                ->orWhere('sup.service_center', 'like', '%' . $q . '%'))
            ->select(
                'sup.id',
                'sup.name',
                'sup.supplier_personnel',
                'sup.service_center',
                'sup.contact_number',
                'sup.contact_email',
                DB::raw('COALESCE(src.cnt, 0) as asset_count')
            )
            ->orderBy('sup.name')
            ->limit(500)
            ->get();

        return response()->json($suppliers);
    }
}
