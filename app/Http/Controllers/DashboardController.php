<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $schools = \Illuminate\Support\Facades\DB::table('schools')->orderBy('name')->get();
        // Dito natin kukunin ang data sa susunod (Total Items, etc.)
        return view('dashboard', compact('schools'));
    }
}