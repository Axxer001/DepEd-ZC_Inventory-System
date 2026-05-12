dst = 'resources/views/partials/building-edit-step.blade.php'
with open(dst, 'r', encoding='utf-8') as f:
    c = f.read()

# Replace renderBldgTable function
old_render_start = "    function renderBldgTable() {"
old_render_end = "    function syncBldgCell(input) {"

start_idx = c.index(old_render_start)
end_idx = c.index(old_render_end)

new_render = '''    function renderBldgTable() {
        const tbody = document.getElementById('bldgTableBody');
        if (!tbody) return;
        tbody.innerHTML = '';

        if (bldgAllData.length === 0) {
            document.getElementById('bldgRowCountLabel').textContent = "0 Rows";
            document.getElementById('bldgCurrentPageNum').textContent = 1;
            document.getElementById('bldgTotalPages').textContent = 1;
            return;
        }

        const start = (bldgCurrentPageNum - 1) * bldgRowsPerPage;
        const end = start + bldgRowsPerPage;
        const pageData = bldgAllData.slice(start, end);

        pageData.forEach((row, idx) => {
            const displayNum = start + idx + 1;
            const orig = bldgOriginalData.find(o => String(o.id) === String(row.id)) || {};

            const renderCell = (col, val, readonly) => {
                const v1 = String(val ?? '').trim();
                const v2 = String(orig[col] ?? '').trim();
                const changed = v1 !== v2;
                const badge = changed ? '<span class="update-badge">Update</span>' : '';
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
        document.getElementById('bldgCurrentPageNum').textContent = bldgCurrentPageNum;
        document.getElementById('bldgTotalPages').textContent = totalPages;
        document.getElementById('bldgPrevBtn').disabled = bldgCurrentPageNum === 1;
        document.getElementById('bldgNextBtn').disabled = bldgCurrentPageNum === totalPages;
    }

'''

c = c[:start_idx] + new_render + c[end_idx:]

# Fix syncBldgCell to use id instead of dist_id
c = c.replace(
    "const id = parseInt(input.getAttribute('data-id'));\n        const col = input.getAttribute('data-col');\n        const newVal = input.value;\n        const row = bldgAllData.find(r => r.dist_id === id);",
    "const id = input.getAttribute('data-id');\n        const col = input.getAttribute('data-col');\n        const newVal = input.value;\n        const row = bldgAllData.find(r => String(r.id) === String(id));"
)
c = c.replace(
    "editUndoStack.push({ type: 'single', rowId: id, col: col, oldVal: oldVal, newVal: newVal });",
    "bldgUndoStack.push({ type: 'single', rowId: id, col: col, oldVal: oldVal, newVal: newVal });"
)

# Fix undo/redo to use id instead of dist_id
c = c.replace("bldgAllData.find(r => r.dist_id === action.rowId)", "bldgAllData.find(r => String(r.id) === String(action.rowId))")
c = c.replace("bldgAllData.find(r => r.dist_id === state.rowId)", "bldgAllData.find(r => String(r.id) === String(state.rowId))")
c = c.replace("bldgAllData.find(r => r.dist_id === action.rowId)", "bldgAllData.find(r => String(r.id) === String(action.rowId))")

with open(dst, 'w', encoding='utf-8') as f:
    f.write(c)
print('Step 4 done')
