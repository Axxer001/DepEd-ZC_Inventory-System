import re

new_methods = '''
    /**
     * GET building filter options for the Infrastructure Editor
     */
    public function getBuildingEditFilters(Request $request)
    {
        $classifications = DB::table('buildings')->whereNotNull('classification')->distinct()->orderBy('classification')->pluck('classification');
        $office_types    = DB::table('buildings')->whereNotNull('office_type')->distinct()->orderBy('office_type')->pluck('office_type');
        $articles        = DB::table('buildings')->whereNotNull('article')->distinct()->orderBy('article')->pluck('article');
        $schools         = DB::table('buildings')->whereNotNull('office_name')->distinct()->orderBy('office_name')->pluck('office_name');
        $occupancies     = DB::table('buildings')->whereNotNull('occupancy_nature')->distinct()->orderBy('occupancy_nature')->pluck('occupancy_nature');

        return response()->json(compact('classifications', 'office_types', 'articles', 'schools', 'occupancies'));
    }

    /**
     * POST building editor preview rows
     */
    public function getBuildingEditPreview(Request $request)
    {
        $filters = $request->input('filters', []);

        $q = DB::table('buildings');

        if (!empty($filters['classification'])) $q->where('classification', $filters['classification']);
        if (!empty($filters['office_type']))    $q->where('office_type', $filters['office_type']);
        if (!empty($filters['article']))        $q->where('article', $filters['article']);
        if (!empty($filters['school']))         $q->where('office_name', $filters['school']);
        if (!empty($filters['occupancy']))      $q->where('occupancy_nature', $filters['occupancy']);
        if (!empty($filters['date']))           $q->whereDate('date_constructed', $filters['date']);

        if (!empty($filters['emptyCol'])) {
            $colMap = [
                'classification'   => 'classification',
                'article'          => 'article',
                'description'      => 'description',
                'office_name'      => 'office_name',
                'property_number'  => 'property_number',
                'acquisition_cost' => 'acquisition_cost',
                'date_constructed' => 'date_constructed',
            ];
            if (isset($colMap[$filters['emptyCol']])) {
                $q->where(function($sub) use ($colMap, $filters) {
                    $sub->whereNull($colMap[$filters['emptyCol']])->orWhere($colMap[$filters['emptyCol']], '');
                });
            }
        }

        if (!empty($filters['sortCost'])) {
            $q->orderBy('acquisition_cost', $filters['sortCost'] === 'low_to_high' ? 'asc' : 'desc');
        } else {
            $q->orderBy('id', 'asc');
        }

        $rows = $q->select([
            'id', 'school_id', 'region', 'division', 'office_type', 'school_identifier',
            'office_name', 'address', 'storeys', 'classrooms', 'article', 'description',
            'classification', 'occupancy_nature', 'location', 'date_constructed',
            'acquisition_date', 'property_number', 'acquisition_cost',
            'estimated_useful_life', 'remarks'
        ])->get();

        return response()->json(['rows' => $rows]);
    }

    /**
     * POST batch update buildings
     */
    public function updateBuildingBatch(Request $request)
    {
        $updates = $request->input('updates', []);
        if (empty($updates)) {
            return response()->json(['success' => false, 'message' => 'No updates provided.'], 422);
        }

        $allowedCols = [
            'office_type', 'school_id', 'office_name', 'address', 'storeys', 'classrooms',
            'article', 'description', 'classification', 'occupancy_nature', 'location',
            'date_constructed', 'acquisition_date', 'property_number', 'acquisition_cost',
            'estimated_useful_life', 'remarks'
        ];

        DB::beginTransaction();
        try {
            $count = 0;
            foreach ($updates as $update) {
                $id = $update['id'] ?? null;
                if (!$id) continue;
                $data = array_filter(
                    array_intersect_key($update, array_flip($allowedCols)),
                    fn($v) => $v !== null
                );
                if (!empty($data)) {
                    DB::table('buildings')->where('id', $id)->update($data);
                    $count++;
                }
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => "Updated {$count} building(s) successfully."]);
        } catch (\\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed: ' . $e->getMessage()], 500);
        }
    }
'''

with open('app/Http/Controllers/InventorySetupController.php', 'r', encoding='utf-8') as f:
    c = f.read()

# Insert before last closing brace of class
last_brace = c.rfind('}')
c = c[:last_brace] + new_methods + '\n}'
with open('app/Http/Controllers/InventorySetupController.php', 'w', encoding='utf-8') as f:
    f.write(c)
print('Controller methods added')
