import re

with open('scratch/generated.blade.php', 'r') as f:
    content = f.read()

# Replace endpoint
content = content.replace('api.inventory.edit_preview', 'api.buildings.edit_preview')
content = content.replace('editAllData', 'bldgEditAllData')
content = content.replace('editOriginalData', 'bldgEditOriginalData')
content = content.replace('editUndoStack', 'bldgEditUndoStack')
content = content.replace('editRedoStack', 'bldgEditRedoStack')
content = content.replace('editCurrentPage', 'bldgEditCurrentPage')
content = content.replace('editRowsPerPage', 'bldgEditRowsPerPage')

# Update bldgInventoryEdit to fetch building filters
content = content.replace("fetch('{{ route(\"api.reports.filters\") }}?report_type=ALL')", "fetch('{{ route(\"api.buildings.filters\") }}')")
content = content.replace("populateEditSelect('editFilterClass', data.classifications);", "populateEditSelect('bEditFilterClass', data.classifications);")
content = content.replace("populateEditSelect('editFilterCat', data.categories);", "populateEditSelect('bEditFilterType', data.types);")
content = content.replace("populateEditSelect('editFilterItem', data.items);", "populateEditSelect('bEditFilterArticle', data.articles);")
content = content.replace("populateEditSelect('editFilterSchool', data.schools);", "populateEditSelect('bEditFilterSchool', data.schools);")
content = content.replace("populateEditSelect('editFilterSource', data.sources);", "populateEditSelect('bEditFilterOccupancy', data.occupancies);")
content = content.replace("populateEditSelect('editFilterMode', data.modes);", "")

# Update filterIds mapping
content = re.sub(r'const filterIds = \{.*?\};', """const filterIds = {
            'bEditFilterClass': 'classification',
            'bEditFilterType': 'office_type',
            'bEditFilterArticle': 'article',
            'bEditFilterSchool': 'office_name',
            'bEditFilterOccupancy': 'occupancy_nature',
            'bEditFilterDate': 'date_constructed',
            'bEditFilterIntegrity': 'emptyCol',
            'bEditFilterSort': 'sortCost'
        };""", content, flags=re.DOTALL)

# Replace table head
new_thead = """
                        <tr>
                            <th class="xls-th w-10 text-center sticky left-0 z-30">#</th>
                            <th class="xls-th" style="min-width:140px">Region</th>
                            <th class="xls-th" style="min-width:180px">Division</th>
                            <th class="xls-th text-emerald-600" style="min-width:140px">Office Type</th>
                            <th class="xls-th text-emerald-600" style="min-width:120px">School ID</th>
                            <th class="xls-th text-emerald-600" style="min-width:200px">School Name *</th>
                            <th class="xls-th text-emerald-600" style="min-width:180px">Address</th>
                            <th class="xls-th text-emerald-600 text-right" style="min-width:80px">Storeys</th>
                            <th class="xls-th text-emerald-600 text-right" style="min-width:90px">Classrooms</th>
                            <th class="xls-th text-emerald-600" style="min-width:150px">Article</th>
                            <th class="xls-th text-emerald-600" style="min-width:180px">Description</th>
                            <th class="xls-th text-emerald-600" style="min-width:140px">Classification</th>
                            <th class="xls-th text-emerald-600" style="min-width:140px">Occupancy</th>
                            <th class="xls-th text-emerald-600" style="min-width:150px">Location</th>
                            <th class="xls-th text-emerald-600" style="min-width:130px">Constructed</th>
                            <th class="xls-th text-emerald-600" style="min-width:130px">Acq. Date</th>
                            <th class="xls-th text-emerald-600" style="min-width:130px">Property No.</th>
                            <th class="xls-th text-emerald-600 text-right" style="min-width:130px">Acq. Cost</th>
                            <th class="xls-th text-emerald-600 text-right" style="min-width:110px">Useful Life</th>
                            <th class="xls-th text-emerald-600 text-right" style="min-width:130px">Appraised Val</th>
                            <th class="xls-th text-emerald-600" style="min-width:130px">Appraisal Date</th>
                            <th class="xls-th text-emerald-600" style="min-width:200px">Remarks</th>
                        </tr>
"""
content = re.sub(r'<thead class="sticky top-0 bg-slate-50 z-20 shadow-sm">\s*<tr>.*?</tr>\s*</thead>', '<thead class="sticky top-0 bg-slate-50 z-20 shadow-sm">' + new_thead + '</thead>', content, flags=re.DOTALL)

# Modify renderEditTable function for building rows
new_render_row = """
        bldgEditAllData.forEach((row, idx) => {
            const displayNum = start + idx + 1;
            const orig = bldgEditOriginalData.find(o => String(o.id) === String(row.id)) || {};
            
            const renderCell = (col, val, isReadonly, isNum=false, isDate=false) => {
                const val1 = String(val ?? '').trim();
                const val2 = String(orig[col] ?? '').trim();
                const hasChanged = val1 !== val2;
                const badgeHtml = hasChanged ? `<span class="update-badge">Update</span>` : '';
                const safeVal = (val ?? '').toString().replace(/"/g, '&quot;');
                
                if (isReadonly) {
                    return `<td class="xls-td p-0 relative"><input type="text" class="xls-input edit-readonly w-full h-full ${isNum?'text-right':''}" value="${safeVal}" readonly tabindex="-1">${badgeHtml}</td>`;
                }
                
                const typ = isDate ? 'date' : (isNum ? 'number' : 'text');
                return `<td class="xls-td p-0 relative"><input type="${typ}" data-id="${row.id}" data-col="${col}" value="${safeVal}" onchange="syncBldgEditCell(this)" class="xls-input w-full h-full bg-transparent ${isNum?'text-right':''}">${badgeHtml}</td>`;
            };

            const srcTr = document.createElement('tr');
            srcTr.className = 'xls-row group border-b border-slate-100';
            srcTr.innerHTML = `
                <td class="xls-td text-center sticky left-0 w-10 bg-white z-10"><span class="text-[10px] font-black text-slate-300">${displayNum}</span></td>
                ${renderCell('region', row.region, false)}
                ${renderCell('division', row.division, false)}
                ${renderCell('office_type', row.office_type, false)}
                ${renderCell('school_identifier', row.school_identifier, false)}
                ${renderCell('office_name', row.office_name, false)}
                ${renderCell('address', row.address, false)}
                ${renderCell('storeys', row.storeys, false, true)}
                ${renderCell('classrooms', row.classrooms, false, true)}
                ${renderCell('article', row.article, false)}
                ${renderCell('description', row.description, false)}
                ${renderCell('classification', row.classification, false)}
                ${renderCell('occupancy_nature', row.occupancy_nature, false)}
                ${renderCell('location', row.location, false)}
                ${renderCell('date_constructed', row.date_constructed, false, false, true)}
                ${renderCell('acquisition_date', row.acquisition_date, false, false, true)}
                ${renderCell('property_number', row.property_number, false)}
                ${renderCell('acquisition_cost', row.acquisition_cost, false, true)}
                ${renderCell('estimated_useful_life', row.estimated_useful_life, false, true)}
                ${renderCell('appraised_value', row.appraised_value, false, true)}
                ${renderCell('appraisal_date', row.appraisal_date, false, false, true)}
                ${renderCell('remarks', row.remarks, false)}
            `;
            srcTbody.appendChild(srcTr);
        });
"""
content = re.sub(r'bldgEditAllData\.forEach\(\(row, idx\).*?dstTbody\.appendChild\(dstTr\);\s*}\);', new_render_row, content, flags=re.DOTALL)

# Sync edits
content = content.replace("row.dist_id === id", "row.id === id")
content = content.replace("syncEditCell(", "syncBldgEditCell(")

# Modal section update
new_bulk_body = """
        {{-- Body --}}
        <div class="p-8 overflow-y-auto custom-scroll flex-1 space-y-10">
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-6 h-6 bg-emerald-500/20 text-emerald-600 rounded-lg flex items-center justify-center text-[10px] font-black shrink-0">1</div>
                    <h4 class="font-black text-slate-800 dark:text-slate-200 uppercase tracking-widest text-xs">Building Data Update</h4>
                </div>
                <div class="grid grid-cols-3 gap-x-6 gap-y-5">
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Region</label><input type="text" id="bbRegion" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Division</label><input type="text" id="bbDivision" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Office Type</label><input type="text" id="bbType" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">School ID</label><input type="text" id="bbSchoolId" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">School Name</label><input type="text" id="bbSchoolName" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Address</label><input type="text" id="bbAddress" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Storeys</label><input type="number" id="bbStoreys" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl text-right" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Classrooms</label><input type="number" id="bbClassrooms" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl text-right" placeholder="Leave empty to ignore"></div>
                    
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Article</label><input type="text" id="bbArticle" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Description</label><input type="text" id="bbDescription" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Classification</label><input type="text" id="bbClass" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Occupancy Nature</label><input type="text" id="bbOccupancy" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Location</label><input type="text" id="bbLocation" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Date Constructed</label><input type="date" id="bbConstructed" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl"></div>
                    
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Issuance Date</label><input type="date" id="bbAcqDate" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Property No.</label><input type="text" id="bbPropertyNo" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Acquisition Cost</label><input type="number" id="bbCost" step="0.01" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl text-right" placeholder="Leave empty to ignore"></div>
                    
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Useful Life</label><input type="number" id="bbLife" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl text-right" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Appraised Value</label><input type="number" id="bbAppraised" step="0.01" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl text-right" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Appraisal Date</label><input type="date" id="bbAppraisalDate" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl"></div>
                    <div class="relative col-span-3"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Remarks</label><input type="text" id="bbRemarks" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                </div>
            </div>
        </div>
"""
content = re.sub(r'\{\{-- Body --\}\}.*?</div>\s*</div>\s*</div>\s*</div>', new_bulk_body + '</div>\n</div>\n</div>', content, flags=re.DOTALL)

# Bulk mapping update
new_mapping = """
        const bulkMapping = {
            'bbRegion': 'region',
            'bbDivision': 'division',
            'bbType': 'office_type',
            'bbSchoolId': 'school_identifier',
            'bbSchoolName': 'office_name',
            'bbAddress': 'address',
            'bbStoreys': 'storeys',
            'bbClassrooms': 'classrooms',
            'bbArticle': 'article',
            'bbDescription': 'description',
            'bbClass': 'classification',
            'bbOccupancy': 'occupancy_nature',
            'bbLocation': 'location',
            'bbConstructed': 'date_constructed',
            'bbAcqDate': 'acquisition_date',
            'bbPropertyNo': 'property_number',
            'bbCost': 'acquisition_cost',
            'bbLife': 'estimated_useful_life',
            'bbAppraised': 'appraised_value',
            'bbAppraisalDate': 'appraisal_date',
            'bbRemarks': 'remarks'
        };
"""
content = re.sub(r'const bulkMapping = \{.*?\};', new_mapping, content, flags=re.DOTALL)

# saveBldgEditChanges payload update
save_keys = """
                const keys = [
                    'region', 'division', 'office_type', 'school_identifier', 'office_name', 
                    'address', 'storeys', 'classrooms', 'article', 'description', 
                    'classification', 'occupancy_nature', 'location', 'date_constructed', 
                    'acquisition_date', 'property_number', 'acquisition_cost', 'estimated_useful_life', 
                    'appraised_value', 'appraisal_date', 'remarks'
                ];
"""
content = re.sub(r'const keys = \[[^\]]+\];', save_keys, content, flags=re.DOTALL)
content = content.replace("updates.push({ dist_id: row.dist_id, changes: changes });", "updates.push({ id: row.id, changes: changes });")
content = content.replace("{{ route('inventory.setup.updateBatch') }}", "{{ route('inventory.setup.buildingUpdateBatch') }}")

with open('resources/views/partials/building-edit-step.blade.php', 'w') as f:
    f.write(content)
