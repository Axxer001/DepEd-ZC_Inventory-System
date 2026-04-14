<<<<
            'type'        => 'required|string|max:255',
            'parent_id'   => 'nullable|integer|exists:stakeholders,id',
            'school_id'   => 'nullable|integer|exists:schools,id',
            'entity_type' => 'nullable|string|in:School,District,Division,Individual,External',
        ]);
====
            'type'        => 'required|string|max:255',
            'parent_id'   => 'nullable|integer|exists:stakeholders,id',
            'school_id'   => 'nullable|integer|exists:schools,id',
            'source_type' => 'nullable|string|in:Government,Contractor,Donor,NGO,Other',
            'entity_type' => 'nullable|string|in:School,District,Division,Individual,External',
        ]);
>>>>

<<<<
            'parent_id'   => $request->parent_id,
            'name'        => trim($request->name),
            'type'        => trim($request->type),
            'school_id'   => $request->school_id,
            'entity_type' => $request->entity_type ?? 'School',
            'created_at'  => now(),
====
            'parent_id'   => $request->parent_id,
            'name'        => trim($request->name),
            'type'        => trim($request->type),
            'school_id'   => $request->school_id,
            'source_type' => $request->source_type,
            'entity_type' => $request->entity_type ?? 'School',
            'created_at'  => now(),
>>>>

<<<<
            'type'        => 'required|string|max:255',
            'entity_type' => 'nullable|string|in:School,District,Division,Individual,External',
        ]);

        $userName   = auth()->user() ? auth()->user()->name : 'System';
        $type       = trim($request->type);
        $orgName    = trim($request->org_name);
        $entityType = $request->entity_type ?? ($type === 'Distributor' ? 'External' : 'School');
====
            'type'        => 'required|string|max:255',
            'source_type' => 'nullable|string|in:Government,Contractor,Donor,NGO,Other',
            'entity_type' => 'nullable|string|in:School,District,Division,Individual,External',
        ]);

        $userName   = auth()->user() ? auth()->user()->name : 'System';
        $type       = trim($request->type);
        $orgName    = trim($request->org_name);
        $sourceType = $request->source_type;
        $entityType = $request->entity_type ?? ($type === 'Distributor' ? 'External' : 'School');
>>>>

<<<<
            if ($parent) {
                $parentId = $parent->id ?? null;
            } else {
====
            if ($parent) {
                // If exists and this is a Distributor, update source_type if not already set
                if ($type === 'Distributor' && $sourceType && empty($parent->source_type)) {
                    DB::table('stakeholders')->where('id', $parent->id)->update(['source_type' => $sourceType, 'updated_at' => now()]);
                }
                $parentId = $parent->id ?? null;
            } else {
>>>>

<<<<
                $parentId = DB::table('stakeholders')->insertGetId([
                    'name'        => $orgName,
                    'type'        => $type,
                    'entity_type' => $entityType,
                    'parent_id'   => null,
                    'created_at'  => now(),
====
                $parentId = DB::table('stakeholders')->insertGetId([
                    'name'        => $orgName,
                    'type'        => $type,
                    'source_type' => $sourceType,
                    'entity_type' => $entityType,
                    'parent_id'   => null,
                    'created_at'  => now(),
>>>>

<<<<
                        DB::table('stakeholders')->insert([
                            'name'        => $personName,
                            'type'        => $type,
                            'entity_type' => $entityType,
                            'parent_id'   => $parentId,
                            'created_at'  => now(),
====
                        DB::table('stakeholders')->insert([
                            'name'        => $personName,
                            'type'        => $type,
                            'source_type' => $sourceType,
                            'entity_type' => $entityType,
                            'parent_id'   => $parentId,
                            'created_at'  => now(),
>>>>

<<<<
                    $newParentId = DB::table('stakeholders')->insertGetId([
                        'name'        => $oldParent->name,
                        'type'        => $type,
                        'entity_type' => $entityType,
                        'parent_id'   => null,
                        'created_at'  => now(),
====
                    $newParentId = DB::table('stakeholders')->insertGetId([
                        'name'        => $oldParent->name,
                        'type'        => $type,
                        'source_type' => $sourceType,
                        'entity_type' => $entityType,
                        'parent_id'   => null,
                        'created_at'  => now(),
>>>>

<<<<
                            DB::table('stakeholders')->insert([
                                'name'        => $oldChild->name,
                                'type'        => $type,
                                'entity_type' => $entityType,
                                'parent_id'   => $newParentId,
                                'created_at'  => now(),
                            ]);
====
                            DB::table('stakeholders')->insert([
                                'name'        => $oldChild->name,
                                'type'        => $type,
                                'source_type' => $sourceType,
                                'entity_type' => $entityType,
                                'parent_id'   => $newParentId,
                                'created_at'  => now(),
                            ]);
>>>>

<<<<
        $stakeholders = DB::table('stakeholders')
            ->select('id', 'parent_id', 'name', 'type', 'school_id', 'entity_type', 'position')
            ->orderBy('name')
====
        $stakeholders = DB::table('stakeholders')
            ->select('id', 'parent_id', 'name', 'type', 'school_id', 'source_type', 'entity_type', 'position')
            ->orderBy('name')
>>>>
