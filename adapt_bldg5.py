dst = 'resources/views/partials/building-edit-step.blade.php'
with open(dst, 'r', encoding='utf-8') as f:
    c = f.read()

# 1. Fix stale populateEditSelect reference (should be populateBldgSelect)
c = c.replace("populateEditSelect(", "populateBldgSelect(")

# 2. Fix saveBldgChanges: replace inventory.setup.updateBatch with buildings.updateBatch
c = c.replace('route("inventory.setup.updateBatch")', 'route("api.buildings.updateBatch")')

# 3. Fix saveBldgChanges: replace dist_id/src_id based payload with building id
old_save_keys = """                const keys = [
                    'classification', 'category', 'article', 'description', 'unit_of_measurement', 
                    'acq_source', 'asset_cost', 'quantity', 'estimated_useful_life', 'property_number', 
                    'location', 'nature_of_occupancy', 'mode_of_acquisition', 'source_personnel', 
                    'personnel_position', 'acceptance_date', 'remarks', 'school_type', 'school_id', 
                    'office_school_name', 'acquisition_date'
                ];"""
new_save_keys = """                const keys = [
                    'office_type', 'school_id', 'office_name', 'address', 'storeys', 'classrooms',
                    'article', 'description', 'classification', 'occupancy_nature', 'location',
                    'date_constructed', 'acquisition_date', 'property_number', 'acquisition_cost',
                    'estimated_useful_life', 'remarks'
                ];"""
c = c.replace(old_save_keys, new_save_keys)

old_save_payload = """                if (hasChanged) {
                    const payload = {
                        dist_id: row.dist_id,
                        src_id: row.src_id
                    };
                    
                    if (changes.hasOwnProperty('classification')) payload.classification = changes.classification;
                    if (changes.hasOwnProperty('category')) payload.category = changes.category;
                    if (changes.hasOwnProperty('article')) payload.article = changes.article;
                    if (changes.hasOwnProperty('description')) payload.description = changes.description;
                    if (changes.hasOwnProperty('unit_of_measurement')) payload.uom = changes.unit_of_measurement;
                    if (changes.hasOwnProperty('acq_source')) payload.acq_source = changes.acq_source;
                    if (changes.hasOwnProperty('asset_cost')) payload.cost = changes.asset_cost;
                    if (changes.hasOwnProperty('quantity')) payload.qty = changes.quantity;
                    if (changes.hasOwnProperty('estimated_useful_life')) payload.useful_life = changes.estimated_useful_life;
                    if (changes.hasOwnProperty('property_number')) payload.property_no = changes.property_number;
                    if (changes.hasOwnProperty('location')) payload.location = changes.location;
                    if (changes.hasOwnProperty('nature_of_occupancy')) payload.occupancy = changes.nature_of_occupancy;
                    if (changes.hasOwnProperty('mode_of_acquisition')) payload.mode = changes.mode_of_acquisition;
                    if (changes.hasOwnProperty('source_personnel')) payload.personnel = changes.source_personnel;
                    if (changes.hasOwnProperty('personnel_position')) payload.position = changes.personnel_position;
                    if (changes.hasOwnProperty('acceptance_date')) payload.acceptance_date = changes.acceptance_date;
                    if (changes.hasOwnProperty('remarks')) payload.remarks = changes.remarks;
                    if (changes.hasOwnProperty('school_type')) payload.school_type = changes.school_type;
                    if (changes.hasOwnProperty('school_id')) payload.school_id = changes.school_id;
                    if (changes.hasOwnProperty('office_school_name')) payload.office_school_name = changes.office_school_name;
                    if (changes.hasOwnProperty('acquisition_date')) payload.acquisition_date = changes.acquisition_date;

                    updates.push(payload);
                }"""
new_save_payload = """                if (hasChanged) {
                    const payload = { id: row.id };
                    Object.keys(changes).forEach(k => { payload[k] = changes[k]; });
                    updates.push(payload);
                }"""
c = c.replace(old_save_payload, new_save_payload)

# 4. Fix applyBldgBulk bulkMapping to use building columns
old_bulk_mapping = """        const bulkMapping = {
            'bebClassification': 'classification',
            'bebCategory': 'category',
            'bebArticle': 'article',
            'bebDescription': 'description',
            'bebUom': 'unit_of_measurement',
            'bebAcqSource': 'acq_source',
            'bebMode': 'mode_of_acquisition',
            'bebPersonnel': 'source_personnel',
            'bebPosition': 'personnel_position',
            'bebCost': 'asset_cost',
            'bebQty': 'quantity',
            'bebLife': 'estimated_useful_life',
            'bebDate1': 'acceptance_date',
            'bebRemarks': 'remarks',
            'bebSchoolType': 'school_type',
            'bebSchoolId': 'school_id',
            'bebSchoolName': 'office_school_name',
            'bebOccupancy': 'nature_of_occupancy',
            'bebLocation': 'location',
            'bebPropertyNo': 'property_number',
            'bebDate2': 'acquisition_date'
        };"""
new_bulk_mapping = """        const bulkMapping = {
            'bebOfficeType': 'office_type',
            'bebSchoolId': 'school_id',
            'bebSchoolName': 'office_name',
            'bebAddress': 'address',
            'bebStoreys': 'storeys',
            'bebClassrooms': 'classrooms',
            'bebArticle': 'article',
            'bebDescription': 'description',
            'bebClassification': 'classification',
            'bebOccupancy': 'occupancy_nature',
            'bebLocation': 'location',
            'bebDateConstructed': 'date_constructed',
            'bebAcqDate': 'acquisition_date',
            'bebPropertyNo': 'property_number',
            'bebAcqCost': 'acquisition_cost',
            'bebLife': 'estimated_useful_life',
            'bebRemarks': 'remarks'
        };"""
c = c.replace(old_bulk_mapping, new_bulk_mapping)

# 5. Fix also the openBldgBulkModal clear inputs reference to 'bebRemarks'
c = c.replace(
    "document.querySelector('#bldgBulkModal input:not([id=\"bldgBulkFrom\"]):not([id=\"bldgBulkTo\"])').forEach(i => i.value = '');\n        document.getElementById('bebRemarks').value = '';",
    "document.querySelectorAll('#bldgBulkModal input:not([id=\"bldgBulkFrom\"]):not([id=\"bldgBulkTo\"])').forEach(i => i.value = '');\n        const br = document.getElementById('bebRemarks'); if(br) br.value = '';"
)

# 6. Fix undo rowId comparisons to use id not dist_id
c = c.replace("redoStates.push({ rowId: row.dist_id,", "redoStates.push({ rowId: row.id,")
c = c.replace("rowPreviousState = { rowId: row.dist_id,", "rowPreviousState = { rowId: row.id,")

with open(dst, 'w', encoding='utf-8') as f:
    f.write(c)
print('Step 5 done')
