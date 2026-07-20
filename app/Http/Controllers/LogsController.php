<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogsController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('system_logs');

        $user = auth()->user();
        if ($user && $user->isSchoolSystem()) {
            $query->join('users', 'system_logs.user', '=', 'users.name')
                  ->where('users.school_id', $user->school_id)
                  ->select('system_logs.*');
        }

        // Handle filtering by Action Type
        $action = $request->query('action');
        if ($action && $action !== 'All Actions') {
            $query->where('action_type', $action);
        }

        $logs = $query->orderBy('system_logs.created_at', 'desc')->paginate(20)->withQueryString();

        return view('admin.logs', compact('logs', 'action'));
    }
}
