<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function index()
    {
        // Gawa muna tayo ng manual list para hindi mag-error ang @foreach sa blade
        $schools = [
            (object) ['id' => 1, 'name' => 'Zamboanga Central School'],
            (object) ['id' => 2, 'name' => 'Tetuan Central School'],
            (object) ['id' => 3, 'name' => 'Ayala National High School'],
        ];
        
        return view('view-assets', compact('schools'));
    }

    // Temporary method para sa dynamic categories (Mock response)
    public function getCategoriesBySchool($schoolId)
    {
        // Kunwari ito ang sagot ng database depende sa School ID
        $mockCategories = [
            '1' => ['DCP Package', 'Furniture'],
            '2' => ['Science Kit', 'DCP Package'],
            '3' => ['Furniture', 'Science Kit', 'Office Supplies']
        ];

        $categories = $mockCategories[$schoolId] ?? ['General Inventory'];

        return response()->json($categories);
    }
}