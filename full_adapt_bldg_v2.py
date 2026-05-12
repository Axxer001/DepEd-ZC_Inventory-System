import re
import shutil

# Source file (Fresh copy of Inventory Editor)
src = 'resources/views/partials/inventory-edit-step.blade.php'
# Target file
dst = 'resources/views/partials/building-edit-step.blade.php'

# Start with a fresh copy
shutil.copy(src, dst)

with open(dst, 'r', encoding='utf-8') as f:
    c = f.read()

# 1. Global Renames (IDs and Functions)
# Note: the order matters for some overlapping names
replacements = [
    ('stepInventoryEdit', 'stepBuildingEdit'),
    ('editFilterSection', 'bldgFilterSection'),
    ('toggleEditFilters', 'toggleBldgFilters'),
    ('toggleEditFilterBtn', 'toggleBldgFilterBtn'),
    ('editFilterClass', 'bldgFilterClass'),
    ('editFilterCat', 'bldgFilterCat'),
    ('editFilterItem', 'bldgFilterArticle'),
    ('editFilterSort', 'bldgFilterSort'),
    ('editFilterSchool', 'bldgFilterSchool'),
    ('editFilterSource', 'bldgFilterOccupancy'),
    ('editFilterMode', 'bldgFilterType'),
    ('editFilterDate', 'bldgFilterDate'),
    ('editFilterIntegrity', 'bldgFilterIntegrity'),
    ('clearEditFilters', 'clearBldgFilters'),
    ('editAssetTableCard', 'bldgAssetTableCard'),
    ('editAssetToolbar', 'bldgAssetToolbar'),
    ('switchEditAssetTab', 'switchBldgTab'),
    ('editPanelAssetSource', 'bldgPanel'),
    ('editPanelAssetDist', 'bldgPanelDist'),
    ('editSourceScroll', 'bldgScroll'),
    ('editDistScroll', 'bldgDistScroll'),
    ('editAssetSourceBody', 'bldgTableBody'),
    ('editAssetDistBody', 'bldgDistBody'),
    ('editRowCountLabel', 'bldgRowCountLabel'),
    ('editPaginationControls', 'bldgPaginationControls'),
    ('editPrevBtn', 'bldgPrevBtn'),
    ('editNextBtn', 'bldgNextBtn'),
    ('editBulkModal', 'bldgBulkModal'),
    ('editBulkFrom', 'bldgBulkFrom'),
    ('editBulkTo', 'bldgBulkTo'),
    ('editUndoBtn', 'bldgUndoBtn'),
    ('editRedoBtn', 'bldgRedoBtn'),
    ('openEditBulkModal', 'openBldgBulkModal'),
    ('closeEditBulkModal', 'closeBldgBulkModal'),
    ('applyEditBulk', 'applyBldgBulk'),
    ('saveEditChanges', 'saveBldgChanges'),
    ('initInventoryEdit', 'initBldgEdit'),
    ('renderEditTable', 'renderBldgTable'),
    ('syncEditCell', 'syncBldgCell'),
    ('updateEditUndoBtn', 'updateBldgUndoBtn'),
    ('editPrevPage', 'bldgPrevPage'),
    ('editNextPage', 'bldgNextPage'),
    ('editFetchData', 'bldgFetchData'),
    ('editAllData', 'bldgAllData'),
    ('editOriginalData', 'bldgOriginalData'),
    ('editUndoStack', 'bldgUndoStack'),
    ('editRedoStack', 'bldgRedoStack'),
    ('editRowsPerPage', 'bldgRowsPerPage'),
    ('editUndo', 'bldgUndo'),
    ('editRedo', 'bldgRedo'),
    # Fix the pagination span IDs correctly
    ('id="editCurrentPage"', 'id="bldgCurrentPageNum"'),
    ('id="editTotalPages"', 'id="bldgTotalPages"'),
    # JS Variable for current page (renamed to bldgPageNum to avoid ID conflict)
    ('editCurrentPage', 'bldgPageNum'),
    ('editAssetLoading', 'bldgAssetLoading'),
]

for old, new in replacements:
    c = c.replace(old, new)

# 2. Update UI Colors and Titles
c = c.replace('text-blue-600">Inventory <span class="text-slate-900">Editor</span>', 'text-emerald-600">Infrastructure <span class="text-slate-900">Management</span>')
c = c.replace('Bulk update master inventory records', 'Bulk update building and facility records')
c = c.replace('blue-600', 'emerald-600')
c = c.replace('blue-50', 'emerald-50')
c = c.replace('blue-500', 'emerald-500')
c = c.replace('blue-100', 'emerald-100')
c = c.replace('blue-700', 'emerald-700')

# 3. Replace the entire script block in 'c' with the robust building-specific logic
new_js_logic = r'''
    let bldgAllData = [];
    let bldgOriginalData = [];
    let bldgUndoStack = [];
    let bldgRedoStack = [];
    let bldgPageNum = 1;
    const bldgRowsPerPage = 50;

    function toggleBldgFilters() {
        const section = document.getElementById('bldgFilterSection');
        const btn = document.getElementById('toggleBldgFilterBtn');
        const srcScroll = document.getElementById('bldgScroll');
        
        if (section.classList.contains('hidden')) {
            section.classList.remove('hidden');
            srcScroll.classList.remove('!max-h-[750px]');
            btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg> Hide Filters`;
        } else {
            section.classList.add('hidden');
            srcScroll.classList.add('!max-h-[750px]');
            btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg> Show Filters`;
        }
    }

    function initBldgEdit() {
        fetch('{{ route("api.buildings.filters") }}')
            .then(res => res.json())
            .then(data => {
                populateBldgSelect('bldgFilterClass', data.classifications || []);
                populateBldgSelect('bldgFilterCat', data.office_types || []);
                populateBldgSelect('bldgFilterArticle', data.articles || []);
                populateBldgSelect('bldgFilterSchool', data.schools || []);
                populateBldgSelect('bldgFilterOccupancy', data.occupancies || []);
            });
    }

    function populateBldgSelect(id, options) {
        const sel = document.getElementById(id);
        if (!sel) return;
        const first = sel.options[0];
        sel.innerHTML = '';
        sel.appendChild(first);
        options.forEach(opt => {
            const el = document.createElement('option');
            el.value = opt; el.textContent = opt;
            sel.appendChild(el);
        });
    }

    function clearBldgFilters() {
        ['bldgFilterClass', 'bldgFilterCat', 'bldgFilterArticle', 'bldgFilterSort', 'bldgFilterSchool', 'bldgFilterOccupancy', 'bldgFilterDate', 'bldgFilterIntegrity'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        bldgFetchData();
    }

    function bldgFetchData() {
        const filters = {
            classification: (document.getElementById('bldgFilterClass')||{}).value || '',
            office_type:    (document.getElementById('bldgFilterCat')||{}).value || '',
            article:        (document.getElementById('bldgFilterArticle')||{}).value || '',
            school:         (document.getElementById('bldgFilterSchool')||{}).value || '',
            occupancy:      (document.getElementById('bldgFilterOccupancy')||{}).value || '',
            date:           (document.getElementById('bldgFilterDate')||{}).value || '',
            emptyCol:       (document.getElementById('bldgFilterIntegrity')||{}).value || '',
            sortCost:       (document.getElementById('bldgFilterSort')||{}).value || '',
        };

        const loader = document.getElementById('bldgAssetLoading');
        if (loader) loader.classList.remove('hidden');

        fetch('{{ route("api.buildings.edit_preview") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ filters: filters })
        })
        .then(res => res.json())
        .then(data => {
            bldgAllData = data.rows || [];
            bldgOriginalData = JSON.parse(JSON.stringify(bldgAllData));
            bldgPageNum = 1;
            bldgUndoStack = [];
            bldgRedoStack = [];
            updateBldgUndoBtn();
            renderBldgTable();
            if (bldgAllData.length === 0) {
                Swal.fire({ title: 'No Buildings Found', text: 'No records match your current filter configuration.', icon: 'info', customClass: { popup: 'rounded-[2rem]' } });
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire({ title: 'Error', text: 'Failed to load building data.', icon: 'error', customClass: { popup: 'rounded-[2rem]' } });
        })
        .finally(() => {
            if (loader) loader.classList.add('hidden');
        });
    }

    function switchBldgTab(tab) { /* buildings use single table */ }

    function renderBldgTable() {
        const tbody = document.getElementById('bldgTableBody');
        if (!tbody) return;
        tbody.innerHTML = '';

        if (bldgAllData.length === 0) {
            document.getElementById('bldgRowCountLabel').textContent = "0 Rows";
            document.getElementById('bldgCurrentPageNum').textContent = 1;
            document.getElementById('bldgTotalPages').textContent = 1;
            return;
        }

        const start = (bldgPageNum - 1) * bldgRowsPerPage;
        const end = start + bldgRowsPerPage;
        const pageData = bldgAllData.slice(start, end);

        pageData.forEach((row, idx) => {
            const displayNum = start + idx + 1;
            const orig = bldgOriginalData.find(o => String(o.id) === String(row.id)) || {};

            const renderCell = (col, val, readonly) => {
                const v1 = String(val ?? '').trim();
                const v2 = String(orig[col] ?? '').trim();
                const changed = v1 !== v2;
                const badge = changed ? `<span class="update-badge">Update</span>` : '';
                const safe = (val ?? '').toString().replace(/"/g, '&quot;');
                if (readonly) return `<td class="xls-td p-0 relative"><input type="text" class="xls-input edit-readonly w-full h-full" value="${safe}" readonly tabindex="-1">${badge}</td>`;
                if (col === 'remarks') return `<td class="xls-td p-0 relative"><select data-id="${row.id}" data-col="${col}" onchange="syncBldgCell(this)" class="xls-input w-full h-full bg-transparent"><option value="Good Condition" ${val==='Good Condition'?'selected':''}>Good Condition</option><option value="Needs Repair" ${val==='Needs Repair'?'selected':''}>Needs Repair</option><option value="Not Useable" ${val==='Not Useable'?'selected':''}>Not Useable</option></select>${badge}</td>`;
                return `<td class="xls-td p-0 relative"><input type="text" data-id="${row.id}" data-col="${col}" value="${safe}" onchange="syncBldgCell(this)" class="xls-input w-full h-full bg-transparent">${badge}</td>`;
            };

            const tr = document.createElement('tr');
            tr.className = 'xls-row group border-b border-slate-100';
            tr.innerHTML = `
                <td class="xls-td text-center sticky left-0 w-10 bg-white z-10"><span class="text-[10px] font-black text-slate-300">${displayNum}</span></td>
                <td class="xls-td p-0 relative"><span class="xls-const w-full h-full flex items-center px-4">Region IX</span></td>
                <td class="xls-td p-0 relative"><span class="xls-const w-full h-full flex items-center px-4">Division of Zamboanga City</span></td>
                ${renderCell('office_type', row.office_type, false)}
                ${renderCell('school_id', row.school_id, false)}
                ${renderCell('office_name', row.office_name, false)}
                ${renderCell('address', row.address, false)}
                ${renderCell('storeys', row.storeys, false)}
                ${renderCell('classrooms', row.classrooms, false)}
                ${renderCell('article', row.article, false)}
                ${renderCell('description', row.description, false)}
                ${renderCell('classification', row.classification, false)}
                ${renderCell('occupancy_nature', row.occupancy_nature, false)}
                ${renderCell('location', row.location, false)}
                ${renderCell('date_constructed', row.date_constructed, false)}
                ${renderCell('acquisition_date', row.acquisition_date, false)}
                ${renderCell('property_number', row.property_number, false)}
                ${renderCell('acquisition_cost', row.acquisition_cost, false)}
                ${renderCell('estimated_useful_life', row.estimated_useful_life, false)}
                ${renderCell('remarks', row.remarks, false)}
            `;
            tbody.appendChild(tr);
        });

        const totalPages = Math.ceil(bldgAllData.length / bldgRowsPerPage) || 1;
        document.getElementById('bldgRowCountLabel').textContent = bldgAllData.length + " Rows";
        document.getElementById('bldgCurrentPageNum').textContent = bldgPageNum;
        document.getElementById('bldgTotalPages').textContent = totalPages;
        document.getElementById('bldgPrevBtn').disabled = bldgPageNum === 1;
        document.getElementById('bldgNextBtn').disabled = bldgPageNum === totalPages;
    }

    function syncBldgCell(input) {
        const id = input.getAttribute('data-id');
        const col = input.getAttribute('data-col');
        const newVal = input.value;
        const row = bldgAllData.find(r => String(r.id) === String(id));
        if (row) {
            const oldVal = row[col] ?? '';
            if (String(oldVal).trim() !== String(newVal).trim()) {
                bldgUndoStack.push({ type: 'single', rowId: id, col: col, oldVal: oldVal, newVal: newVal });
                row[col] = newVal;
                bldgRedoStack = [];
                updateBldgUndoBtn();
                renderBldgTable(); 
            }
        }
    }

    function bldgPrevPage() { if (bldgPageNum > 1) { bldgPageNum--; renderBldgTable(); } }
    function bldgNextPage() { const t = Math.ceil(bldgAllData.length/bldgRowsPerPage); if (bldgPageNum < t) { bldgPageNum++; renderBldgTable(); } }

    function openBldgBulkModal() {
        if(bldgAllData.length === 0) return Swal.fire('No Data', 'Load assets first.', 'info');
        const m = document.getElementById('bldgBulkModal');
        m.classList.remove('hidden');
        document.querySelectorAll('#bldgBulkModal input:not([id="bldgBulkFrom"]):not([id="bldgBulkTo"])').forEach(i => i.value = '');
        const br = document.getElementById('bebRemarks'); if(br) br.value = '';

        const maxRows = bldgAllData.length;
        const fromInput = document.getElementById('bldgBulkFrom');
        const toInput   = document.getElementById('bldgBulkTo');
        fromInput.value = 1;
        fromInput.max   = maxRows;
        toInput.value   = maxRows;
        toInput.max     = maxRows;

        toInput.oninput = function() {
            const val = parseInt(this.value);
            if (val > maxRows) this.style.color = '#ef4444';
            else this.style.color = '';
        };
        
        setTimeout(() => {
            m.classList.remove('opacity-0');
            m.querySelector('.transform').classList.remove('scale-95');
        }, 10);
    }
    
    function closeBldgBulkModal() {
        const m = document.getElementById('bldgBulkModal');
        m.classList.add('opacity-0');
        m.querySelector('.transform').classList.add('scale-95');
        setTimeout(() => m.classList.add('hidden'), 300);
    }

    function applyBldgBulk() {
        const from = parseInt(document.getElementById('bldgBulkFrom').value);
        const to = parseInt(document.getElementById('bldgBulkTo').value);
        const maxRows = bldgAllData.length;

        if (isNaN(from) || isNaN(to) || from < 1 || to < from) {
            return Swal.fire('Invalid Range', 'Enter a valid row range.', 'error');
        }

        if (to > maxRows) {
            return Swal.fire({ icon: 'warning', title: 'Exceeds Total Rows', html: `<b>To Row</b> cannot exceed <b>${maxRows}</b>.`, confirmButtonColor: '#c00000' });
        }

        const toLimit = Math.min(to, bldgAllData.length);
        
        const bulkMapping = {
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
        };

        const activeUpdates = {};
        let hasUpdates = false;
        for (const [inputId, colKey] of Object.entries(bulkMapping)) {
            const val = document.getElementById(inputId).value;
            if (val !== "") { activeUpdates[colKey] = val; hasUpdates = true; }
        }

        if (!hasUpdates) return Swal.fire('No Changes', 'You did not fill any fields.', 'info');

        const previousStates = [];
        for (let i = from - 1; i < toLimit; i++) {
            const row = bldgAllData[i];
            const rowPreviousState = { rowId: row.id, changes: [] };
            let rowChanged = false;

            for (const [col, newVal] of Object.entries(activeUpdates)) {
                const oldVal = row[col] ?? '';
                if (String(oldVal).trim() !== String(newVal).trim()) {
                    rowPreviousState.changes.push({ col: col, oldVal: oldVal });
                    row[col] = newVal;
                    rowChanged = true;
                }
            }
            if (rowChanged) previousStates.push(rowPreviousState);
        }

        if (previousStates.length > 0) {
            bldgUndoStack.push({ type: 'bulkMulti', states: previousStates });
            bldgRedoStack = [];
            updateBldgUndoBtn();
            renderBldgTable();
            Swal.fire({ icon: 'success', title: 'Bulk Edit Applied', text: `Updated ${previousStates.length} rows.`, timer: 1500, showConfirmButton: false });
        }
        closeBldgBulkModal();
    }

    function bldgUndo() {
        if (bldgUndoStack.length === 0) return;
        const action = bldgUndoStack.pop();
        const redoStates = [];
        if (action.type === 'single') {
            const row = bldgAllData.find(r => String(r.id) === String(action.rowId));
            if (row) {
                redoStates.push({ rowId: row.id, changes: [{ col: action.col, oldVal: row[action.col] }] });
                row[action.col] = action.oldVal;
            }
        } else if (action.type === 'bulkMulti') {
            action.states.forEach(state => {
                const row = bldgAllData.find(r => String(r.id) === String(state.rowId));
                if (row) {
                    const rs = { rowId: state.rowId, changes: [] };
                    state.changes.forEach(change => {
                        rs.changes.push({ col: change.col, oldVal: row[change.col] });
                        row[change.col] = change.oldVal;
                    });
                    redoStates.push(rs);
                }
            });
        }
        bldgRedoStack.push({ type: 'bulkMulti', states: redoStates });
        updateBldgUndoBtn();
        renderBldgTable();
    }

    function bldgRedo() {
        if (bldgRedoStack.length === 0) return;
        const action = bldgRedoStack.pop();
        const undoStates = [];
        action.states.forEach(state => {
            const row = bldgAllData.find(r => String(r.id) === String(state.rowId));
            if (row) {
                const us = { rowId: state.rowId, changes: [] };
                state.changes.forEach(change => {
                    us.changes.push({ col: change.col, oldVal: row[change.col] });
                    row[change.col] = change.oldVal;
                });
                undoStates.push(us);
            }
        });
        bldgUndoStack.push({ type: 'bulkMulti', states: undoStates });
        updateBldgUndoBtn();
        renderBldgTable();
    }

    function updateBldgUndoBtn() {
        const uBtn = document.getElementById('bldgUndoBtn');
        const rBtn = document.getElementById('bldgRedoBtn');
        if (uBtn) uBtn.className = bldgUndoStack.length > 0 ? 'px-4 py-2 text-emerald-600 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-white transition-all active:scale-95 flex items-center gap-2' : 'px-4 py-2 text-slate-400 rounded-xl text-[9px] font-black uppercase tracking-widest opacity-50 cursor-not-allowed flex items-center gap-2';
        if (rBtn) rBtn.className = bldgRedoStack.length > 0 ? 'px-4 py-2 text-emerald-600 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-white transition-all active:scale-95 flex items-center gap-2' : 'px-4 py-2 text-slate-400 rounded-xl text-[9px] font-black uppercase tracking-widest opacity-50 cursor-not-allowed flex items-center gap-2';
    }

    function saveBldgChanges() {
        const updates = [];
        bldgAllData.forEach(row => {
            const orig = bldgOriginalData.find(o => String(o.id) === String(row.id));
            if (!orig) return;
            const changes = {};
            let hasChanged = false;
            const keys = ['office_type', 'school_id', 'office_name', 'address', 'storeys', 'classrooms', 'article', 'description', 'classification', 'occupancy_nature', 'location', 'date_constructed', 'acquisition_date', 'property_number', 'acquisition_cost', 'estimated_useful_life', 'remarks'];
            keys.forEach(k => {
                if (String(row[k] ?? '').trim() !== String(orig[k] ?? '').trim()) { changes[k] = row[k]; hasChanged = true; }
            });
            if (hasChanged) { updates.push({ id: row.id, ...changes }); }
        });

        if (updates.length === 0) return Swal.fire('No Changes', 'No records were modified.', 'info');

        Swal.fire({
            title: 'Save Changes?',
            text: `You are about to modify ${updates.length} records.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Yes, Save Updates'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Saving...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                fetch('{{ route("api.buildings.updateBatch") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ updates: updates })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Saved!', data.message, 'success').then(() => {
                            bldgOriginalData = JSON.parse(JSON.stringify(bldgAllData));
                            bldgUndoStack = []; bldgRedoStack = []; updateBldgUndoBtn(); renderBldgTable();
                        });
                    } else Swal.fire('Error', data.message, 'error');
                })
                .catch(err => Swal.fire('Error', 'Server error.', 'error'));
            }
        });
    }
'''

# Replace the entire script block in 'c'
c = re.sub(r'<script>.*?</script>', f'<script>{new_js_logic}</script>', c, flags=re.DOTALL)

# 4. Replace Toolbar Tabs with Single Label
old_toolbar_tabs = r'<div class="flex bg-slate-200/50 rounded-xl p-1 gap-1">.*?Asset Distribution\s*</button>\s*</div>'
c = re.sub(old_toolbar_tabs, '<span class="hidden md:block text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">Building Records</span>', c, flags=re.DOTALL)

# 5. Replace Tables with Single Building Table
old_tables_block = r'{{-- ── Asset Source Table ── --}}.*?{{-- ── Asset Distribution Table ── --}}.*?</table>\s*</div>\s*</div>'
new_bldg_table = '''        {{-- ── Buildings Table ── --}}
        <div id="bldgPanel" class="flex-grow flex flex-col min-h-0">
            <div id="bldgScroll" class="xls-scroll-wrap custom-scroll overflow-x-auto overflow-y-auto transition-all duration-300" style="max-height: calc(100vh - 450px);">
                <table class="w-full border-collapse" style="min-width:2800px;">
                    <thead class="sticky top-0 bg-slate-50 z-20 shadow-sm">
                        <tr>
                            <th class="xls-th w-10 text-center sticky left-0 z-30">#</th>
                            <th class="xls-th" style="min-width:90px">Region</th>
                            <th class="xls-th" style="min-width:200px">Division</th>
                            <th class="xls-th text-emerald-600" style="min-width:140px">Office Type</th>
                            <th class="xls-th text-emerald-600" style="min-width:100px">School ID</th>
                            <th class="xls-th text-emerald-600" style="min-width:210px">School Name</th>
                            <th class="xls-th text-emerald-600" style="min-width:180px">Address</th>
                            <th class="xls-th text-emerald-600 text-right" style="min-width:80px">Storeys</th>
                            <th class="xls-th text-emerald-600 text-right" style="min-width:100px">Classrooms</th>
                            <th class="xls-th text-emerald-600" style="min-width:140px">Article</th>
                            <th class="xls-th text-emerald-600" style="min-width:200px">Description</th>
                            <th class="xls-th text-emerald-600" style="min-width:140px">Classification</th>
                            <th class="xls-th text-emerald-600" style="min-width:160px">Nature of Occupancy</th>
                            <th class="xls-th text-emerald-600" style="min-width:160px">Location</th>
                            <th class="xls-th text-emerald-600" style="min-width:140px">Date Constructed</th>
                            <th class="xls-th text-emerald-600" style="min-width:140px">Acquisition Date</th>
                            <th class="xls-th text-emerald-600" style="min-width:150px">Property No.</th>
                            <th class="xls-th text-emerald-600 text-right" style="min-width:140px">Acquisition Cost (₱)</th>
                            <th class="xls-th text-emerald-600 text-right" style="min-width:120px">Useful Life (yrs)</th>
                            <th class="xls-th text-emerald-600" style="min-width:200px">Remarks</th>
                        </tr>
                    </thead>
                    <tbody id="bldgTableBody"></tbody>
                </table>
            </div>
        </div>'''
c = re.sub(old_tables_block, new_bldg_table, c, flags=re.DOTALL)

# 6. Replace Bulk Modal Body
old_bulk_body = r'{{-- Body --}}\s*<div class="p-8 overflow-y-auto custom-scroll flex-1 space-y-10">.*?</div>\s*</div>\s*</div>'
new_bulk_body = '''{{-- Body --}}
        <div class="p-8 overflow-y-auto custom-scroll flex-1 space-y-10">
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-6 h-6 bg-emerald-500/20 text-emerald-600 rounded-lg flex items-center justify-center text-[10px] font-black shrink-0">1</div>
                    <h4 class="font-black text-slate-800 dark:text-slate-200 uppercase tracking-widest text-xs">Building Identity</h4>
                </div>
                <div class="grid grid-cols-2 gap-x-6 gap-y-5">
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Office/School Type</label><input type="text" id="bebOfficeType" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">School ID</label><input type="text" id="bebSchoolId" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">School Name</label><input type="text" id="bebSchoolName" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Address</label><input type="text" id="bebAddress" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Storeys</label><input type="number" id="bebStoreys" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl text-right" placeholder="Leave empty to ignore" min="0" step="1"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Classrooms</label><input type="number" id="bebClassrooms" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl text-right" placeholder="Leave empty to ignore" min="0" step="1"></div>
                </div>
            </div>
            <div class="border-t border-slate-100 dark:border-slate-800"></div>
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-6 h-6 bg-emerald-500/20 text-emerald-600 rounded-lg flex items-center justify-center text-[10px] font-black shrink-0">2</div>
                    <h4 class="font-black text-slate-800 dark:text-slate-200 uppercase tracking-widest text-xs">Building Details</h4>
                </div>
                <div class="grid grid-cols-2 gap-x-6 gap-y-5">
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Article</label><input type="text" id="bebArticle" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Description</label><input type="text" id="bebDescription" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Classification</label><input type="text" id="bebClassification" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Nature of Occupancy</label><input type="text" id="bebOccupancy" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Location</label><input type="text" id="bebLocation" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Property Number</label><input type="text" id="bebPropertyNo" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Acquisition Cost (₱)</label><input type="number" id="bebAcqCost" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl text-right" placeholder="Leave empty to ignore" min="0" step="0.01"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Useful Life (yrs)</label><input type="number" id="bebLife" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl text-right" placeholder="Leave empty to ignore" min="0" step="1"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Date Constructed</label><input type="date" id="bebDateConstructed" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Acquisition Date</label><input type="date" id="bebAcqDate" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl"></div>
                    <div class="relative col-span-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Remarks</label>
                        <select id="bebRemarks" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl bg-transparent">
                            <option value="">-- Ignore --</option>
                            <option value="Good Condition">Good Condition</option>
                            <option value="Needs Repair">Needs Repair</option>
                            <option value="Not Useable">Not Useable</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>'''
c = re.sub(old_bulk_body, new_bulk_body, c, flags=re.DOTALL)

with open(dst, 'w', encoding='utf-8') as f:
    f.write(c)

print("Full adaptation complete.")
