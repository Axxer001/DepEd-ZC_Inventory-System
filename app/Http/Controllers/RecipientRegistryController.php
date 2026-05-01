<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RecipientRegistryController extends Controller
{
    /**
     * Recipient registry — temporarily disabled pending new schema integration.
     * The stakeholders table has been dropped and replaced by acquisition_sources.
     * This feature will be redesigned as part of the new Asset Distribution workflow.
     */
    public function add(Request $request)
    {
        return response()->json([
            'error' => 'Recipient registry is being redesigned for the new asset management system.'
        ], 503);
    }
}
