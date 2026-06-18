import re

with open('resources/views/partials/inventory-edit-step.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Replace the HTML
html_pattern = re.compile(r'<div id="editBulkModal".*?<!-- Bulk Edit Modal -->', re.DOTALL | re.IGNORECASE)
# wait, the HTML goes up to <style> ... wait, let me just find the indices

start_idx = content.find('<div id="editBulkModal"')
end_idx = content.find('<style>', start_idx)

new_html = '''<div id="editBulkModal" class="fixed inset-0 z-[150] flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeEditBulkModal()"></div>
    <div class="bg-white border border-slate-200 rounded-[2rem] shadow-2xl w-[90vw] max-w-5xl max-h-[90vh] flex flex-col relative z-10 transform scale-95 transition-transform duration-300">
        
        {{-- Header --}}
        <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-white rounded-t-[2rem]">
            <div>
                <h3 class="text-2xl font-black text-slate-800 uppercase tracking-tighter italic">Bulk Edit Rows</h3>
                <p class="text-[10px] font-bold text-blue-500 uppercase tracking-widest mt-1">Update specific columns for a range of rows</p>
            </div>
            
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-4 bg-slate-50 px-5 py-3 rounded-2xl border border-slate-200">
                    <div class="flex flex-col">
                        <label class="text-[8px] font-black text-slate-900 uppercase tracking-widest leading-none mb-1">From Row #</label>
                        <input type="number" id="editBulkFrom" value="1" min="1" class="w-12 bg-transparent font-black text-slate-800 outline-none text-lg leading-none">
                    </div>
                    <div class="w-px h-6 bg-slate-200"></div>
                    <div class="flex flex-col">
                        <label class="text-[8px] font-black text-slate-900 uppercase tracking-widest leading-none mb-1">To Row #</label>
                        <input type="number" id="editBulkTo" value="1" min="1" class="w-16 bg-transparent font-black text-slate-800 outline-none text-lg leading-none">
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button onclick="closeEditBulkModal()" class="px-6 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-slate-900 hover:bg-slate-50 transition-all italic">Cancel</button>
                    <button onclick="applyEditBulk()" class="px-8 py-4 rounded-2xl text-[11px] font-black text-white bg-blue-600 hover:bg-blue-500 shadow-xl shadow-blue-500/20 transition-all active:scale-95 uppercase tracking-widest italic">Apply Bulk Edit</button>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="p-8 overflow-y-auto custom-scroll flex-1 space-y-10 bg-white">
            
            {{-- Source Section --}}
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-6 h-6 bg-amber-500/10 text-amber-600 rounded-lg flex items-center justify-center text-[10px] font-black shrink-0">1</div>
                    <h4 class="font-black text-slate-800 uppercase tracking-widest text-xs">Asset Data Entry (Source)</h4>
                </div>
                <div class="grid grid-cols-2 gap-x-6 gap-y-5">
                    <div class="relative col-identity p-1 rounded-2xl" style="position:relative;overflow:visible">
                        <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Classification</label>
                        <input type="text" id="ebClassification" autocomplete="off" 
                            oninput="filterBulkEditClassDropdown(this.value)" onfocus="filterBulkEditClassDropdown(this.value)"
                            class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Search Classification...">
                        <div id="bulk-edit-class-dd" class="xls-custom-dd" style="display:none; width:100%;"></div>
                    </div>
                    <div class="relative col-identity p-1 rounded-2xl" style="position:relative;overflow:visible">
                        <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Category</label>
                        <input type="text" id="ebCategory" autocomplete="off" 
                            oninput="filterBulkEditCatDropdown(this.value)" onfocus="filterBulkEditCatDropdown(this.value)"
                            class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Search Category...">
                        <div id="bulk-edit-cat-dd" class="xls-custom-dd" style="display:none; width:100%;"></div>
                    </div>
                    <div class="relative col-identity p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Item</label><input type="text" id="ebItem" autocomplete="off" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Leave empty to ignore"></div>
                    <div class="relative col-context p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Description</label><input type="text" id="ebDescription" autocomplete="off" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Leave empty to ignore"></div>
                    <div class="relative col-context p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Unit of Measurement</label><input type="text" id="ebUom" autocomplete="off" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Leave empty to ignore"></div>
                    
                    <div class="relative col-personnel p-1 rounded-2xl" style="position:relative;overflow:visible">
                        <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1 flex items-center gap-2">
                            <span>Acquisition Source</span>
                            <span id="ebAcqSourceNewBadge" class="hidden px-1.5 py-0.5 text-[8px] font-extrabold uppercase bg-blue-600 text-white rounded tracking-wider leading-none">NEW</span>
                        </label>
                        <input type="text" id="ebAcqSource" autocomplete="off" oninput="filterEditBulkAcqSourceDropdown(this.value)" onfocus="filterEditBulkAcqSourceDropdown(this.value)" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Leave empty to ignore">
                        <div id="edit-bulk-acq-source-dd" class="xls-custom-dd" style="display:none; width: 100%;"></div>
                    </div>

                    <div class="relative col-status p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Mode of Procurement</label><input type="text" id="ebMode" autocomplete="off" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Leave empty to ignore"></div>
                    
                    <div class="relative col-personnel p-1 rounded-2xl">
                        <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Source Personnel</label>
                        <input type="text" id="ebPersonnel" autocomplete="off" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Leave empty to ignore">
                    </div>
                    
                    <div class="relative col-personnel p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Personnel Position</label><input type="text" id="ebPosition" autocomplete="off" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Leave empty to ignore"></div>
                    
                    <div class="relative col-financial p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Cost per Unit</label><input type="number" id="ebCost" class="xls-input !border border-slate-100 rounded-xl text-right bg-transparent" placeholder="Leave empty to ignore" min="0" step="0.01"></div>
                    <div class="relative col-financial p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Quantity</label><input type="number" id="ebQty" class="xls-input !border border-slate-100 rounded-xl text-right bg-transparent" placeholder="Leave empty to ignore" min="0" step="1"></div>
                    <div class="relative col-temporal p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Expected Useful Life</label><input type="number" id="ebLife" class="xls-input !border border-slate-100 rounded-xl text-right bg-transparent" placeholder="Leave empty to ignore" min="0" step="1"></div>
                    <div class="relative col-temporal p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Acceptance Date</label><input type="date" id="ebDate1" class="xls-input !border border-slate-100 rounded-xl bg-transparent"></div>
                    <div class="relative col-status p-1 rounded-2xl">
                        <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Condition</label>
                        <select id="ebRemarks" class="xls-input !border border-slate-100 rounded-xl bg-transparent">
                            <option value="">-- IGNORE CHANGES --</option>
                            <option value="Good Condition">Good Condition</option>
                            <option value="Needs Repair">Needs Repair</option>
                            <option value="Not Useable">Not Useable</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100"></div>

            {{-- Target Section --}}
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-6 h-6 bg-amber-500/10 text-amber-600 rounded-lg flex items-center justify-center text-[10px] font-black shrink-0">2</div>
                    <h4 class="font-black text-slate-800 uppercase tracking-widest text-xs">Asset Distribution (Target)</h4>
                </div>

                <div class="grid grid-cols-2 gap-x-6 gap-y-5">
                    <div class="relative col-context p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Region</label>
                        <div class="w-full px-4 py-[11px] font-semibold text-[11.5px] bg-white/50 border border-slate-100 rounded-xl text-slate-900 flex justify-between items-center cursor-not-allowed">Region IX <svg class="w-3.5 h-3.5 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></div>
                    </div>
                    <div class="relative col-context p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Division</label>
                        <div class="w-full px-4 py-[11px] font-semibold text-[11.5px] bg-white/50 border border-slate-100 rounded-xl text-slate-900 flex justify-between items-center cursor-not-allowed">Division of Zamboanga City <svg class="w-3.5 h-3.5 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></div>
                    </div>
                    
                    <div class="relative col-personnel p-1 rounded-2xl" style="position:relative;overflow:visible">
                        <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Employee Search</label>
                        <input type="text" id="ebEmployeeSearch" autocomplete="off" 
                            oninput="editBulkAutofillEmployee(this.value); filterEditBulkEmpDropdown(this.value)" onfocus="filterEditBulkEmpDropdown(this.value)"
                            class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Search Employee...">
                        <div id="edit-bulk-emp-dd" class="xls-custom-dd" style="display:none; width:100%;"></div>
                    </div>
                    <div class="relative col-personnel p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Employee ID</label>
                        <input type="text" id="ebEmployeeId" autocomplete="off" class="w-full px-4 py-[11px] font-semibold text-[11.5px] bg-slate-100 border border-slate-100 rounded-xl text-slate-500 outline-none" placeholder="Leave empty to ignore">
                    </div>
                    <div class="relative col-personnel p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Employee Name</label>
                        <input type="text" id="ebEmployeeName" autocomplete="off" class="w-full px-4 py-[11px] font-semibold text-[11.5px] bg-slate-100 border border-slate-100 rounded-xl text-slate-500 outline-none" placeholder="Leave empty to ignore">
                    </div>
                    <div class="relative col-personnel p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Employee Position</label>
                        <input type="text" id="ebEmployeePos" autocomplete="off" class="w-full px-4 py-[11px] font-semibold text-[11.5px] bg-slate-100 border border-slate-100 rounded-xl text-slate-500 outline-none" placeholder="Leave empty to ignore">
                    </div>
                    <div class="relative col-personnel p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Employee Status</label>
                        <input type="text" id="ebEmployeeStatus" autocomplete="off" class="w-full px-4 py-[11px] font-semibold text-[11.5px] bg-slate-100 border border-slate-100 rounded-xl text-slate-500 outline-none" placeholder="Leave empty to ignore">
                    </div>

                    <div class="relative col-identity p-1 rounded-2xl" style="position:relative;overflow:visible">
                        <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">School/Office Search</label>
                        <input type="text" id="ebSchoolSearch" autocomplete="off" 
                            oninput="editBulkAutofillLocation(this.value); filterEditBulkLocDropdown(this.value)" onfocus="filterEditBulkLocDropdown(this.value)"
                            class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Search Location...">
                        <div id="edit-bulk-loc-dd" class="xls-custom-dd" style="display:none; width:100%;"></div>
                    </div>
                    <div class="relative col-identity p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Office/School ID</label>
                        <input type="text" id="ebSchoolId" autocomplete="off" class="w-full px-4 py-[11px] font-semibold text-[11.5px] bg-slate-100 border border-slate-100 rounded-xl text-slate-500 outline-none" placeholder="Leave empty to ignore">
                    </div>
                    <div class="relative col-context p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Office/School Type</label>
                        <input type="text" id="ebSchoolType" autocomplete="off" class="w-full px-4 py-[11px] font-semibold text-[11.5px] bg-slate-100 border border-slate-100 rounded-xl text-slate-500 outline-none" placeholder="Leave empty to ignore">
                    </div>
                    <div class="relative col-identity p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Office/School Name</label>
                        <input type="text" id="ebSchoolName" autocomplete="off" class="w-full px-4 py-[11px] font-semibold text-[11.5px] bg-slate-100 border border-slate-100 rounded-xl text-slate-500 outline-none" placeholder="Leave empty to ignore">
                    </div>
                    <div class="relative col-context p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Location / Room</label>
                        <input type="text" id="ebLocation" autocomplete="off" class="w-full px-4 py-[11px] font-semibold text-[11.5px] bg-slate-100 border border-slate-100 rounded-xl text-slate-500 outline-none" placeholder="Leave empty to ignore">
                    </div>
                    <div class="relative col-context p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Nature of Occupancy</label>
                        <input type="text" id="ebOccupancy" autocomplete="off" class="w-full px-4 py-[11px] font-semibold text-[11.5px] bg-slate-100 border border-slate-100 rounded-xl text-slate-500 outline-none" placeholder="Leave empty to ignore">
                    </div>

                    <div class="relative col-identity p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Property Number</label><input type="text" id="ebPropertyNo" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Leave empty to ignore"></div>
                    <div class="relative col-temporal p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Acquisition Date</label><input type="date" id="ebDate2" class="xls-input !border border-slate-100 rounded-xl bg-transparent"></div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function editBulkAutofillEmployee(val) {
        const emp = (typeof globalEmployees !== 'undefined' ? globalEmployees : []).find(e => e.full_name === val);
        if(emp) {
            document.getElementById('ebEmployeeId').value     = emp.employee_id || '';
            document.getElementById('ebEmployeeName').value   = emp.full_name || '';
            document.getElementById('ebEmployeePos').value    = emp.position || '';
            document.getElementById('ebEmployeeStatus').value = emp.status || '';

            if (emp.location_name) {
                const ebSchoolSearch = document.getElementById('ebSchoolSearch');
                if (ebSchoolSearch) ebSchoolSearch.value = emp.location_name;
                document.getElementById('ebSchoolName').value = emp.location_name;
                document.getElementById('ebSchoolId').value   = emp.location_id || '';
                document.getElementById('ebSchoolType').value = emp.location_type_label || emp.location_type || '';
                document.getElementById('ebLocation').value   = emp.location || 'Zamboanga City';
            }
        } else if (!val) {
            document.getElementById('ebEmployeeId').value     = '';
            document.getElementById('ebEmployeeName').value   = '';
            document.getElementById('ebEmployeePos').value    = '';
            document.getElementById('ebEmployeeStatus').value = '';
        }
    }

    function filterEditBulkEmpDropdown(query) {
        const dd = document.getElementById('edit-bulk-emp-dd');
        if (!dd) return;
        const q = (query || '').trim().toLowerCase();
        const emps = typeof globalEmployees !== 'undefined' ? globalEmployees : [];
        const matches = q.length === 0 ? emps.slice(0, 50) : emps.filter(e => e.full_name.toLowerCase().includes(q) || (e.employee_id && e.employee_id.toLowerCase().includes(q))).slice(0, 50);

        if (matches.length === 0) dd.innerHTML = <div class="xls-dd-empty">No employees found</div>;
        else dd.innerHTML = matches.map(e => <div class="xls-dd-item" onmousedown="selectEditBulkEmp(this.getAttribute('data-name'))" data-name=""> <span style="font-size:8px;color:#64748b;margin-left:6px"></span></div>).join('');
        dd.style.display = 'block';
    }

    function selectEditBulkEmp(name) {
        const inp = document.getElementById('ebEmployeeSearch');
        if (inp) {
            inp.value = name;
            editBulkAutofillEmployee(name);
        }
        const dd = document.getElementById('edit-bulk-emp-dd');
        if (dd) dd.style.display = 'none';
    }

    function editBulkAutofillLocation(val) {
        const loc = (typeof globalLocations !== 'undefined' ? globalLocations : []).find(l => l.name === val);
        if(loc) {
            document.getElementById('ebSchoolId').value = loc.entity_id || '';
            document.getElementById('ebSchoolType').value = loc.type || '';
            document.getElementById('ebSchoolName').value = loc.name || '';
            document.getElementById('ebLocation').value = loc.location || '';
        } else if (!val) {
            document.getElementById('ebSchoolId').value = '';
            document.getElementById('ebSchoolType').value = '';
            document.getElementById('ebSchoolName').value = '';
            document.getElementById('ebLocation').value = '';
        }
    }

    function filterEditBulkLocDropdown(query) {
        const dd = document.getElementById('edit-bulk-loc-dd');
        if (!dd) return;
        const q = (query || '').trim().toLowerCase();
        const locs = typeof globalLocations !== 'undefined' ? globalLocations : [];
        const matches = q.length === 0 ? locs.slice(0, 50) : locs.filter(l => l.name.toLowerCase().includes(q) || (l.entity_id && l.entity_id.toLowerCase().includes(q))).slice(0, 50);

        if (matches.length === 0) dd.innerHTML = <div class="xls-dd-empty">No locations found</div>;
        else dd.innerHTML = matches.map(l => <div class="xls-dd-item" onmousedown="selectEditBulkLoc(this.getAttribute('data-name'))" data-name=""> <span style="font-size:8px;color:#64748b;margin-left:6px"></span></div>).join('');
        dd.style.display = 'block';
    }

    function selectEditBulkLoc(name) {
        const inp = document.getElementById('ebSchoolSearch');
        if (inp) {
            inp.value = name;
            editBulkAutofillLocation(name);
        }
        const dd = document.getElementById('edit-bulk-loc-dd');
        if (dd) dd.style.display = 'none';
    }
</script>
'''

content = content[:start_idx] + new_html + '\n' + content[end_idx:]

# Also update bulkMapping inside applyEditBulk()
mapping_str = """        const bulkMapping = {
            'ebClassification': 'classification',
            'ebCategory': 'category',
            'ebItem': 'article',
            'ebDescription': 'description',
            'ebUom': 'unit_of_measurement',
            'ebAcqSource': 'acq_source',
            'ebMode': 'mode_of_acquisition',
            'ebPersonnel': 'source_personnel',
            'ebPosition': 'personnel_position',
            'ebCost': 'asset_cost',
            'ebQty': 'quantity',
            'ebLife': 'estimated_useful_life',
            'ebDate1': 'acceptance_date',
            'ebRemarks': 'remarks',
            'ebEmployeeId': 'custodian_employee_id',
            'ebEmployeeName': 'custodian_name',
            'ebEmployeePos': 'custodian_position',
            'ebEmployeeStatus': 'custodian_status',
            'ebSchoolType': 'school_type',
            'ebSchoolId': 'school_id',
            'ebSchoolName': 'office_school_name',
            'ebOccupancy': 'nature_of_occupancy',
            'ebLocation': 'location',
            'ebPropertyNo': 'property_number',
            'ebDate2': 'acquisition_date'
        };"""

content = re.sub(r'const bulkMapping = \{.*?\} *;', mapping_str, content, flags=re.DOTALL)

with open('resources/views/partials/inventory-edit-step.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)
print("Done editing inventory-edit-step.blade.php")
