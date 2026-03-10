<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogsController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('system_logs')->orderByDesc('created_at');

        // Handle filtering by Action Type
        $action = $request->query('action');
        if ($action && $action !== 'All Actions') {
            $query->where('action_type', $action);
        }

        $logs = $query->paginate(20)->withQueryString();

        return view('admin.logs', compact('logs', 'action'));
    }
}
