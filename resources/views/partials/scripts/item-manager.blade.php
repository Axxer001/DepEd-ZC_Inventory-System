        //  ADD NEW REGISTRATION FORM â€” STATE-BASED
        // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
        let allRowsData = []; 
        let currentPage = 1;
        const rowsPerPage = 50;
        let _rowNumCounter = 0; 

        // Global datalist caches
        let globalLocations = [];
        let globalEmployees = [];

        async function initGlobalDatalists() {
            try {
                const locRes = await fetch('/api/locations/search?q=&type=all');
                globalLocations = await locRes.json();
                const dlLoc = document.getElementById('dl-locations');
                if(dlLoc) {
                    dlLoc.innerHTML = globalLocations.map(loc => `<option value="${loc.name}"></option>`).join('');
                }

                const empRes = await fetch('/api/employees/search?q=');
                globalEmployees = await empRes.json();
                const dlEmp = document.getElementById('dl-employees');
                if(dlEmp) {
                    dlEmp.innerHTML = globalEmployees.map(emp => `<option value="${emp.full_name}"></option>`).join('');
                }
            } catch (e) { console.error('Failed to init datalists', e); }
        }
        document.addEventListener('DOMContentLoaded', initGlobalDatalists);

        function autofillLocation(rowId, val) {
            const row = allRowsData.find(r => r.id === rowId);
            if(!row) return;
            const loc = globalLocations.find(l => l.name === val);
            if(loc) {
                row['school-id'] = loc.entity_id;
                row['school-type'] = loc.type || '';
                row['school-name'] = loc.name;
                row['location'] = loc.location || '';
                
                document.querySelector(`#dst-${rowId} input[data-col="school-id"]`).value = row['school-id'];
                document.querySelector(`#dst-${rowId} input[data-col="school-type"]`).value = row['school-type'];
                document.querySelector(`#dst-${rowId} input[data-col="school-name"]`).value = row['school-name'];
                document.querySelector(`#dst-${rowId} input[data-col="location"]`).value = row['location'];
            }
        }

        function autofillEmployee(rowId, val) {
            const row = allRowsData.find(r => r.id === rowId);
            if(!row) return;
            const emp = globalEmployees.find(e => e.full_name === val);
            if(emp) {
                row['employee-id'] = emp.employee_id;
                row['employee-name'] = emp.full_name;
                row['employee-pos'] = emp.position || '';
                row['employee-status'] = emp.status || '';

                document.querySelector(`#dst-${rowId} input[data-col="employee-id"]`).value = row['employee-id'];
                document.querySelector(`#dst-${rowId} input[data-col="employee-name"]`).value = row['employee-name'];
                document.querySelector(`#dst-${rowId} input[data-col="employee-pos"]`).value = row['employee-pos'];
                document.querySelector(`#dst-${rowId} input[data-col="employee-status"]`).value = row['employee-status'];
            }
        }

        function updatePaginationDisplay() {
            const totalPages = Math.max(1, Math.ceil(allRowsData.length / rowsPerPage));
            if (currentPage > totalPages) currentPage = totalPages;
            const curDisplay = document.getElementById('currentPageDisplay');
            const totalDisplay = document.getElementById('totalPagesDisplay');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const rowLabel = document.getElementById('rowCountLabel');
            if (curDisplay) curDisplay.textContent = currentPage;
            if (totalDisplay) totalDisplay.textContent = totalPages;
            if (prevBtn) prevBtn.disabled = (currentPage === 1);
            if (nextBtn) nextBtn.disabled = (currentPage === totalPages);
            if (rowLabel) rowLabel.textContent = `${allRowsData.length} Rows`;
            const controls = document.getElementById('paginationControls');
            if (controls) {
                if (allRowsData.length <= rowsPerPage) { controls.classList.add('hidden'); }
                else { controls.classList.remove('hidden'); }
            }
        }

        function nextPage() {
            const totalPages = Math.ceil(allRowsData.length / rowsPerPage);
            if (currentPage < totalPages) { currentPage++; renderAssetTable(); }
        }

        function prevPage() {
            if (currentPage > 1) { currentPage--; renderAssetTable(); }
        }

        function switchAssetTab(tab) {
            const srcPanel = document.getElementById('panelAssetSource');
            const distPanel = document.getElementById('panelAssetDist');
            const tabSrc   = document.getElementById('tabAssetSource');
            const tabDst   = document.getElementById('tabAssetDist');
            const label    = document.getElementById('assetTabLabel');
            const ON  = 'px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg bg-[#c00000] text-white shadow-sm transition-all';
            const OFF = 'px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg text-slate-900 hover:text-slate-900 transition-all';
            if (tab === 'source') {
                srcPanel.classList.remove('hidden');
                distPanel.classList.add('hidden');
                tabSrc.className = ON; tabDst.className = OFF;
                label.textContent = 'Asset Source';
            } else {
                srcPanel.classList.add('hidden');
                distPanel.classList.remove('hidden');
                tabSrc.className = OFF; tabDst.className = ON;
                label.textContent = 'Asset Distribution';
            }
            updateRowCount();
        }

        function renderAssetTable() {
            const totalPages = Math.max(1, Math.ceil(allRowsData.length / rowsPerPage));
            if (currentPage > totalPages) currentPage = totalPages;

            const tbodySource = document.getElementById('assetSourceBody');
            const tbodyDist = document.getElementById('assetDistBody');
            if (!tbodySource || !tbodyDist) return;
            tbodySource.innerHTML = ''; tbodyDist.innerHTML = '';
            if (allRowsData.length === 0) {
                document.getElementById('assetSourceEmpty').classList.remove('hidden');
                document.getElementById('assetDistEmpty').classList.remove('hidden');
                updatePaginationDisplay(); return;
            }
            document.getElementById('assetSourceEmpty').classList.add('hidden');
            document.getElementById('assetDistEmpty').classList.add('hidden');
            const start = (currentPage - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            const pageData = allRowsData.slice(start, end);
            pageData.forEach((row, index) => {
                const displayNum = start + index + 1;
                addSourceRowDOM(row, displayNum);
                addDistRowDOM(row, displayNum);
            });
            updatePaginationDisplay();
            updateNewLabels();
        }

        function detectItemSchoolType(name) {
            if (!name) return '';
            const n = name.toLowerCase();
            if (n.includes('elementary')) return 'Elementary School';
            if (n.includes('national high') || n.includes('high school')) return 'High School';
            if (n.includes('integrated')) return 'Integrated School';
            return '';
        }

        function cleanSchoolNameForLocation(name) {
            if (!name) return '';
            // Remove common school suffixes (case insensitive)
            const suffixes = [
                / elementary school/gi,
                / integrated school/gi,
                / national high school/gi,
                / high school/gi,
                / senior high school/gi,
                / - snhs/gi,
                / - standalone/gi,
                / central school/gi,
                / primary school/gi
            ];
            let cleaned = name;
            suffixes.forEach(regex => {
                cleaned = cleaned.replace(regex, '');
            });
            return cleaned.trim() + ", Zamboanga City";
        }

        function syncState(rowId, col, value) {
            const row = allRowsData.find(r => r.id === rowId);
            if (row) {
                row[col] = value;
                
                // --- Auto-fill logic for School ID <-> School Name <-> Location ---
                if (col === 'school-id') {
                    const school = allSchoolsList.find(s => String(s.school_id) === String(value));
                    if (school) {
                        row['school-name'] = school.name;
                        row['school-type'] = detectItemSchoolType(school.name);
                        const isSchool = row['school-type'].toLowerCase().includes('school');
                        row['location'] = isSchool ? cleanSchoolNameForLocation(school.name) : 'Zamboanga City';
                        // Update UI if on current page
                        const nameInp = document.querySelector(`#dst-${rowId} input[data-col="school-name"]`);
                        const typeInp = document.querySelector(`#dst-${rowId} input[data-col="school-type"]`);
                        const locInp = document.querySelector(`#dst-${rowId} input[data-col="location"]`);
                        if (nameInp) nameInp.value = school.name;
                        if (typeInp) typeInp.value = row['school-type'];
                        if (locInp) locInp.value = row['location'];
                    }
                } else if (col === 'school-name') {
                    const school = allSchoolsList.find(s => s.name.toLowerCase() === value.toLowerCase());
                    if (school) {
                        row['school-type'] = detectItemSchoolType(school.name);
                        row['school-id'] = school.school_id;
                        const isSchool = row['school-type'].toLowerCase().includes('school');
                        row['location'] = isSchool ? cleanSchoolNameForLocation(school.name) : 'Zamboanga City';
                        // Update UI if on current page
                        const typeInp = document.querySelector(`#dst-${rowId} input[data-col="school-type"]`);
                        const idInp = document.querySelector(`#dst-${rowId} input[data-col="school-id"]`);
                        const locInp = document.querySelector(`#dst-${rowId} input[data-col="location"]`);
                        if (typeInp) typeInp.value = row['school-type'];
                        if (idInp) idInp.value = school.school_id;
                        if (locInp) locInp.value = row['location'];
                    } else if (value.trim() !== "") {
                        row['school-type'] = detectItemSchoolType(value);
                        row['school-id'] = '';
                        const isSchool = row['school-type'].toLowerCase().includes('school');
                        row['location'] = isSchool ? cleanSchoolNameForLocation(value) : 'Zamboanga City';
                        
                        const typeInp = document.querySelector(`#dst-${rowId} input[data-col="school-type"]`);
                        const idInp = document.querySelector(`#dst-${rowId} input[data-col="school-id"]`);
                        const locInp = document.querySelector(`#dst-${rowId} input[data-col="location"]`);
                        if (typeInp) typeInp.value = row['school-type'];
                        if (idInp) idInp.value = '';
                        if (locInp) locInp.value = row['location'];
                    }
                } else if (col === 'school-type') {
                    const isSchool = value.toLowerCase().includes('school');
                    if (!isSchool) {
                        row['location'] = 'Zamboanga City';
                        const locInp = document.querySelector(`#dst-${rowId} input[data-col="location"]`);
                        if (locInp) locInp.value = 'Zamboanga City';
                    } else {
                        if (row['school-name']) {
                            row['location'] = cleanSchoolNameForLocation(row['school-name']);
                            const locInp = document.querySelector(`#dst-${rowId} input[data-col="location"]`);
                            if (locInp) locInp.value = row['location'];
                        }
                    }
                } else if (col === 'employee-first' || col === 'employee-last') {
                    // Smart Auto-fill: unique-match lookup against employees registry
                    const first = (row['employee-first'] || '').toLowerCase();
                    const last  = (row['employee-last']  || '').toLowerCase();
                    const matches = allCustodiansList.filter(c => {
                        const fMatch = first ? c.first_name.toLowerCase().includes(first) : true;
                        const lMatch = last  ? c.last_name.toLowerCase().includes(last)   : true;
                        return fMatch && lMatch;
                    });

                    // Auto-fill only on unique match
                    if (matches.length === 1 && (first || last)) {
                        const m = matches[0];
                        row['employee-first']  = m.first_name;
                        row['employee-middle'] = m.middle_name || '';
                        row['employee-last']   = m.last_name;
                        row['employee-pos']    = m.position || '';

                        const fInp = document.querySelector(`#dst-${rowId} input[data-col="employee-first"]`);
                        const mInp = document.querySelector(`#dst-${rowId} input[data-col="employee-middle"]`);
                        const lInp = document.querySelector(`#dst-${rowId} input[data-col="employee-last"]`);
                        const pInp = document.querySelector(`#dst-${rowId} input[data-col="employee-pos"]`);

                        if (fInp) fInp.value = row['employee-first'];
                        if (mInp) mInp.value = row['employee-middle'];
                        if (lInp) lInp.value = row['employee-last'];
                        if (pInp) pInp.value = row['employee-pos'];
                    }
                }

                if (col === 'cost' || col === 'qty') {
                    const cost = parseFloat(row.cost || 0);
                    const qty = parseInt(row.qty || 0);
                    const distInput = document.getElementById(`dst-cost-${rowId}`);
                    if (distInput) distInput.value = (cost * qty).toFixed(2);
                }
                if (col === 'property-no') {
                    if (value.trim() !== '') {
                        row.qty = 1;
                        const qtyInp = document.querySelector(`#src-${rowId} input[data-col="qty"]`);
                        if (qtyInp) {
                            qtyInp.value = 1;
                            qtyInp.readOnly = true;
                            qtyInp.classList.add('bg-slate-50', 'cursor-not-allowed');
                        }
                        const cost = parseFloat(row.cost || 0);
                        const distInput = document.getElementById(`dst-cost-${rowId}`);
                        if (distInput) distInput.value = (cost * 1).toFixed(2);
                    } else {
                        const qtyInp = document.querySelector(`#src-${rowId} input[data-col="qty"]`);
                        if (qtyInp) {
                            qtyInp.readOnly = false;
                            qtyInp.classList.remove('bg-slate-50', 'cursor-not-allowed');
                        }
                    }
                }
            }
        }

        function addAssetRow() {
            const today = new Date().toISOString().split('T')[0];
            const newRow = {
                id: ++_rowNumCounter,
                classification: '', category: '', item: '', description: '', uom: '', 
                mode: '', 
                personnel: '', position: '',
                cost: '', qty: '', 
                'useful-life': '', 
                'acceptance-date': today,
                condition: 'Good Condition',
                region: 'Region IX', division: 'Zamboanga City Division',
                'school-search': '', 'school-id': '', 'school-type': '', 'school-name': '', location: 'Zamboanga City',
                'employee-search': '', 'employee-id': '', 'employee-name': '', 'employee-pos': '', 'employee-status': '',
                'property-no': '', 'acquisition-date': today
            };
            allRowsData.push(newRow);
            currentPage = Math.ceil(allRowsData.length / rowsPerPage);
            renderAssetTable();
            setTimeout(() => {
                const tr = document.getElementById(`src-${newRow.id}`);
                if (tr) tr.querySelector('input').focus();
            }, 50);
        }

        function addSourceRowDOM(data, displayNum) {
            const tbody = document.getElementById('assetSourceBody');
            const tr = document.createElement('tr');
            tr.id = `src-${data.id}`;
            tr.className = 'xls-row group border-b border-slate-100';
            tr.innerHTML = `
                <td class="xls-td xls-sticky-col text-center sticky left-0 w-10" style="background:inherit">
                    <span class="row-num text-[10px] font-black text-slate-300">${displayNum}</span>
                </td>
                <td class="xls-td col-identity"><input type="text" oninput="syncState(${data.id}, 'classification', this.value)" data-col="classification" value="${data.classification}" autocomplete="off" class="xls-input" placeholder="e.g. Semi-Expendable"></td>
                <td class="xls-td col-identity"><input type="text" oninput="syncState(${data.id}, 'category', this.value)"       data-col="category"       value="${data.category}"       autocomplete="off" class="xls-input" placeholder="Category"></td>
                <td class="xls-td col-identity"><input type="text" oninput="syncState(${data.id}, 'item', this.value)"           data-col="item"           value="${data.item}"           autocomplete="off" class="xls-input" placeholder="Item"></td>
                <td class="xls-td col-context"><input type="text" oninput="syncState(${data.id}, 'description', this.value)"    data-col="description"    value="${data.description}"    autocomplete="off" class="xls-input" placeholder="Description"></td>
                <td class="xls-td col-context"><input type="text" oninput="syncState(${data.id}, 'uom', this.value)"            data-col="uom"            value="${data.uom}"            autocomplete="off" class="xls-input" placeholder="e.g. Unit, Set, Pcs"></td>
                <td class="xls-td col-status"><input type="text" oninput="syncState(${data.id}, 'mode', this.value)"           data-col="mode"           value="${data.mode}"           autocomplete="off" class="xls-input" placeholder="Mode of Procurement"></td>
                <td class="xls-td col-personnel"><input type="text" oninput="syncState(${data.id}, 'personnel', this.value)"      data-col="personnel"      value="${data.personnel}"      autocomplete="off" class="xls-input" placeholder="Personnel name"></td>
                <td class="xls-td col-personnel"><input type="text" oninput="syncState(${data.id}, 'position', this.value)"       data-col="position"       value="${data.position}"       autocomplete="off" class="xls-input" placeholder="Position"></td>
                <td class="xls-td col-financial"><input type="number" oninput="syncState(${data.id}, 'cost', this.value)" data-col="cost" value="${data.cost}" class="xls-input text-right" placeholder="0.00" min="0" step="0.01"></td>
                <td class="xls-td col-financial"><input type="number" oninput="syncState(${data.id}, 'qty', this.value)"  data-col="qty"  value="${data.qty}"  class="xls-input text-right ${data['property-no'] ? 'bg-slate-50 cursor-not-allowed' : ''}" placeholder="0" min="0" step="1" ${data['property-no'] ? 'readonly' : ''}></td>
                <td class="xls-td col-temporal"><input type="number" oninput="syncState(${data.id}, 'useful-life', this.value)" data-col="useful-life" value="${data['useful-life'] || ''}" class="xls-input text-right" placeholder="0"    min="0" step="1"></td>
                <td class="xls-td col-temporal"><input type="date"   oninput="syncState(${data.id}, 'acceptance-date', this.value)" data-col="acceptance-date" value="${data['acceptance-date']}" class="xls-input"></td>
                <td class="xls-td col-status">
                    <select onchange="syncState(${data.id}, 'condition', this.value)" data-col="condition" class="xls-input bg-transparent">
                        <option value="Good Condition" ${data.condition === 'Good Condition' ? 'selected' : ''}>Good Condition</option>
                        <option value="Needs Repair" ${data.condition === 'Needs Repair' ? 'selected' : ''}>Needs Repair</option>
                        <option value="Not Useable" ${data.condition === 'Not Useable' ? 'selected' : ''}>Not Useable</option>
                    </select>
                </td>
                <td class="xls-td text-center w-10">
                    <button onclick="deleteRow(${data.id})" class="p-1.5 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all" title="Remove row">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </td>`;
            tbody.appendChild(tr);
        }

        function addDistRowDOM(data, displayNum) {
            const tbody = document.getElementById('assetDistBody');
            const tr = document.createElement('tr');
            tr.id = `dst-${data.id}`;
            tr.className = 'xls-row group border-b border-slate-100';
            const total = (parseFloat(data.cost || 0) * parseInt(data.qty || 0)).toFixed(2);
            tr.innerHTML = `
                <td class="xls-td xls-sticky-col text-center sticky left-0 w-10" style="background:inherit">
                    <span class="row-num text-[10px] font-black text-slate-300">${displayNum}</span>
                </td>
                <td class="xls-td col-context"><input type="text" value="${data.region}" class="xls-input bg-slate-50 dark:bg-white/5 cursor-not-allowed text-slate-500" readonly tabindex="-1"></td>
                <td class="xls-td col-context"><input type="text" value="${data.division}" class="xls-input bg-slate-50 dark:bg-white/5 cursor-not-allowed text-slate-500" readonly tabindex="-1"></td>
                <td class="xls-td col-identity">
                    <input type="text" oninput="syncState(${data.id}, 'school-search', this.value); autofillLocation(${data.id}, this.value)" data-col="school-search" value="${data['school-search'] || ''}" autocomplete="off" class="xls-input" list="dl-locations" placeholder="Search Location...">
                </td>
                <td class="xls-td col-identity"><input type="text" data-col="school-id"   value="${data['school-id'] || ''}"   autocomplete="off" class="xls-input bg-slate-50 cursor-not-allowed" readonly tabindex="-1"></td>
                <td class="xls-td col-identity"><input type="text" data-col="school-type" value="${data['school-type'] || ''}" autocomplete="off" class="xls-input bg-slate-50 cursor-not-allowed" readonly tabindex="-1"></td>
                <td class="xls-td col-identity"><input type="text" data-col="school-name" value="${data['school-name'] || ''}" autocomplete="off" class="xls-input bg-slate-50 cursor-not-allowed" readonly tabindex="-1"></td>
                <td class="xls-td col-identity"><input type="text" data-col="location"    value="${data.location || ''}"       autocomplete="off" class="xls-input bg-slate-50 cursor-not-allowed" readonly tabindex="-1"></td>
                <td class="xls-td col-personnel">
                    <input type="text" oninput="syncState(${data.id}, 'employee-search', this.value); autofillEmployee(${data.id}, this.value)" data-col="employee-search" value="${data['employee-search'] || ''}" autocomplete="off" class="xls-input" list="dl-employees" placeholder="Search Employee...">
                </td>
                <td class="xls-td col-personnel"><input type="text" data-col="employee-id" value="${data['employee-id'] || ''}" autocomplete="off" class="xls-input bg-slate-50 cursor-not-allowed" readonly tabindex="-1"></td>
                <td class="xls-td col-personnel"><input type="text" data-col="employee-name" value="${data['employee-name'] || ''}" autocomplete="off" class="xls-input bg-slate-50 cursor-not-allowed" readonly tabindex="-1"></td>
                <td class="xls-td col-personnel"><input type="text" data-col="employee-pos" value="${data['employee-pos'] || ''}" autocomplete="off" class="xls-input bg-slate-50 cursor-not-allowed" readonly tabindex="-1"></td>
                <td class="xls-td col-personnel"><input type="text" data-col="employee-status" value="${data['employee-status'] || ''}" autocomplete="off" class="xls-input bg-slate-50 cursor-not-allowed" readonly tabindex="-1"></td>
                <td class="xls-td col-identity"><input type="text" oninput="syncState(${data.id}, 'property-no', this.value)" data-col="property-no" value="${data['property-no']}" autocomplete="off" class="xls-input" placeholder="Property number" id="dst-prop-${data.id}"></td>
                <td class="xls-td col-financial"><input type="number" id="dst-cost-${data.id}" data-col="cost-total" value="${total}" autocomplete="off" class="xls-input text-right bg-slate-50 dark:bg-white/5 cursor-not-allowed" placeholder="0.00" min="0" step="0.01" readonly tabindex="-1"></td>
                <td class="xls-td col-temporal"><input type="date"   oninput="syncState(${data.id}, 'acquisition-date', this.value)" data-col="acquisition-date" value="${data['acquisition-date']}" autocomplete="off" class="xls-input"></td>
                <td class="xls-td text-center w-10">
                    <button onclick="deleteRow(${data.id})" class="p-1.5 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all" title="Remove row">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </td>`;
            tbody.appendChild(tr);
        }

        function deleteRow(id) {
            allRowsData = allRowsData.filter(r => r.id !== id);
            renderAssetTable();
        }

        let deleteMode = 'rows';
        function setDeleteMode(mode) {
            deleteMode = mode;
            const btnRows = document.getElementById('btnDelRows');
            const btnPages = document.getElementById('btnDelPages');
            const lblFrom = document.getElementById('lblDelFrom');
            const lblTo = document.getElementById('lblDelTo');
            const ON = 'px-4 py-1.5 text-[9px] font-black uppercase tracking-widest rounded-lg bg-white dark:bg-slate-700 shadow-sm text-slate-800 dark:text-white transition-all';
            const OFF = 'px-4 py-1.5 text-[9px] font-black uppercase tracking-widest rounded-lg text-slate-900 transition-all';
            
            if (mode === 'rows') {
                btnRows.className = ON; btnPages.className = OFF;
                lblFrom.textContent = 'From Row'; lblTo.textContent = 'To Row';
                document.getElementById('deleteToRow').value = allRowsData.length;
            } else {
                btnRows.className = OFF; btnPages.className = ON;
                lblFrom.textContent = 'From Page'; lblTo.textContent = 'To Page';
                document.getElementById('deleteToRow').value = Math.ceil(allRowsData.length / rowsPerPage);
            }
        }

        function openBulkDeleteModal() {
            const m = document.getElementById('bulkDeleteModal'); if (!m) return;
            m.classList.remove('hidden');
            setTimeout(() => { m.classList.remove('opacity-0'); m.querySelector('.transform').classList.remove('scale-95'); }, 10);
            setDeleteMode('rows');
            document.getElementById('deleteFromRow').value = 1;
        }

        function closeBulkDeleteModal() {
            const m = document.getElementById('bulkDeleteModal'); if (!m) return;
            m.classList.add('opacity-0'); m.querySelector('.transform').classList.add('scale-95');
            setTimeout(() => m.classList.add('hidden'), 300);
        }

        function confirmBulkDelete() {
            let from = parseInt(document.getElementById('deleteFromRow').value);
            let to = parseInt(document.getElementById('deleteToRow').value);
            let fromIdx, toIdx;

            if (deleteMode === 'rows') {
                if (isNaN(from) || isNaN(to) || from < 1 || to < from || to > allRowsData.length) {
                    Swal.fire({ icon: 'error', title: 'Invalid Range', text: 'Please enter a valid row range.', confirmButtonColor: '#c00000' }); return;
                }
                fromIdx = from - 1; toIdx = to - 1;
            } else {
                const totalPages = Math.ceil(allRowsData.length / rowsPerPage);
                if (isNaN(from) || isNaN(to) || from < 1 || to < from || from > totalPages) {
                    Swal.fire({ icon: 'error', title: 'Invalid Range', text: 'Please enter a valid page range.', confirmButtonColor: '#c00000' }); return;
                }
                if (to > totalPages) to = totalPages;
                fromIdx = (from - 1) * rowsPerPage;
                toIdx = (to * rowsPerPage) - 1;
                if (toIdx >= allRowsData.length) toIdx = allRowsData.length - 1;
            }

            const countToDelete = toIdx - fromIdx + 1;
            Swal.fire({
                title: 'Confirm Delete', text: `Delete ${deleteMode === 'rows' ? 'rows' : 'pages'} ${from} to ${to} (${countToDelete} items)?`,
                icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626', confirmButtonText: 'Yes, delete',
                customClass: { popup: 'rounded-[2rem]' }
            }).then((result) => {
                if (result.isConfirmed) {
                    allRowsData.splice(fromIdx, countToDelete);
                    renderAssetTable(); closeBulkDeleteModal();
                    Swal.fire({ icon: 'success', title: 'Deleted', text: `Successfully removed ${countToDelete} items.`, timer: 1500, showConfirmButton: false });
                }
            });
        }

        function updateRowCount() {
            const srcVisible = !document.getElementById('panelAssetSource').classList.contains('hidden');
            const label = srcVisible ? 'Asset Source' : 'Asset Distribution';
            document.getElementById('rowCountLabel').textContent =
                _rowCount + ' ' + label + ' Row' + (_rowCount !== 1 ? 's' : '') + ' (paired)';
        }

        // =============================================
        // BULK ADD MODAL LOGIC
        // =============================================
        function bulkAutofillLocation(val) {
            const loc = globalLocations.find(l => l.name === val);
            if(loc) {
                document.getElementById('bSchoolId').value = loc.entity_id;
                document.getElementById('bSchoolType').value = loc.type || '';
                document.getElementById('bSchoolName').value = loc.name;
                document.getElementById('bLocation').value = loc.location || '';
            } else {
                document.getElementById('bSchoolId').value = '';
                document.getElementById('bSchoolType').value = '';
                document.getElementById('bSchoolName').value = '';
                document.getElementById('bLocation').value = '';
            }
        }

        function bulkAutofillEmployee(val) {
            const emp = globalEmployees.find(e => e.full_name === val);
            if(emp) {
                document.getElementById('bEmployeeId').value = emp.employee_id;
                document.getElementById('bEmployeeName').value = emp.full_name;
                document.getElementById('bEmployeePos').value = emp.position || '';
                document.getElementById('bEmployeeStatus').value = emp.status || '';
            } else {
                document.getElementById('bEmployeeId').value = '';
                document.getElementById('bEmployeeName').value = '';
                document.getElementById('bEmployeePos').value = '';
                document.getElementById('bEmployeeStatus').value = '';
            }
        }
        function openBulkAddModal() {
            const m = document.getElementById('bulkAddModal');
            m.classList.remove('hidden');
            setTimeout(() => {
                m.classList.remove('opacity-0');
                m.querySelector('.transform').classList.remove('scale-95');
            }, 10);
        }

        function closeBulkAddModal() {
            const m = document.getElementById('bulkAddModal');
            m.classList.add('opacity-0');
            m.querySelector('.transform').classList.add('scale-95');
            setTimeout(() => m.classList.add('hidden'), 300);
        }

        function confirmBulkAdd() {
            const count = parseInt(document.getElementById('bulkRowCount').value) || 1;
            const today = new Date().toISOString().split('T')[0];
            
            // Gather all pre-fill values
            const data = {
                classification: document.getElementById('bClassification').value,
                category: document.getElementById('bCategory').value,
                item: document.getElementById('bItem').value,
                description: document.getElementById('bDescription').value,
                uom: document.getElementById('bUom').value,
                mode: document.getElementById('bMode').value,
                personnel: document.getElementById('bPersonnel').value,
                position: document.getElementById('bPosition').value,
                cost1: document.getElementById('bCost').value,
                qty1: document.getElementById('bQty1').value,
                life: document.getElementById('bLife').value,
                date1: document.getElementById('bDate1').value,
                remarks: document.getElementById('bRemarks').value || 'Good Condition',

                schoolSearch: document.getElementById('bSchoolSearch') ? document.getElementById('bSchoolSearch').value : '',
                schoolType: document.getElementById('bSchoolType') ? document.getElementById('bSchoolType').value : '',
                schoolId: document.getElementById('bSchoolId') ? document.getElementById('bSchoolId').value : '',
                schoolName: document.getElementById('bSchoolName') ? document.getElementById('bSchoolName').value : '',
                employeeSearch: document.getElementById('bEmployeeSearch') ? document.getElementById('bEmployeeSearch').value : '',
                employeeId: document.getElementById('bEmployeeId') ? document.getElementById('bEmployeeId').value : '',
                employeeName: document.getElementById('bEmployeeName') ? document.getElementById('bEmployeeName').value : '',
                employeePos: document.getElementById('bEmployeePos') ? document.getElementById('bEmployeePos').value : '',
                employeeStatus: document.getElementById('bEmployeeStatus') ? document.getElementById('bEmployeeStatus').value : '',
                location: document.getElementById('bLocation') ? document.getElementById('bLocation').value : '',
                propertyNo: document.getElementById('bPropertyNo').value,
                cost2: document.getElementById('bCost2').value,
                date2: document.getElementById('bDate2').value
            };

            // Generate rows in data array first (fast)
            for (let i = 0; i < count; i++) {
                const newRow = {
                    id: ++_rowNumCounter,
                    classification: data.classification || '',
                    category: data.category || '',
                    item: data.item || '',
                    description: data.description || '',
                    uom: data.uom || '', 
                    mode: data.mode || '', 
                    personnel: data.personnel || '',
                    position: data.position || '',
                    cost: data.cost1 || '',
                    qty: data.qty1 || '', 
                    'useful-life': data.life || '', 
                    'acceptance-date': data.date1 || today,
                    condition: data.remarks,
                    region: 'Region IX', division: 'Zamboanga City Division',
                    'school-search': data.schoolSearch || '',
                    'school-type': data.schoolType || '',
                    'school-id': data.schoolId || '',
                    'school-name': data.schoolName || '',
                    'employee-search': data.employeeSearch || '',
                    'employee-id': data.employeeId || '',
                    'employee-name': data.employeeName || '',
                    'employee-pos': data.employeePos || '',
                    'employee-status': data.employeeStatus || '',
                    location: data.location || '',
                    'property-no': data.propertyNo || '',
                    'acquisition-date': data.date2 || today
                };
                allRowsData.push(newRow);
            }

            // Render ONCE
            currentPage = Math.ceil(allRowsData.length / rowsPerPage);
            renderAssetTable();
            updateNewLabels();

            // Clear modal inputs
            document.querySelectorAll('#bulkAddModal input').forEach(inp => {
                if(inp.id !== 'bulkRowCount') inp.value = '';
            });
            document.getElementById('bulkRowCount').value = '1';
            
            closeBulkAddModal();
        }

        function setColVal(row, colName, val) {
            const el = row.querySelector(`input[data-col="${colName}"]`);
            if (el) el.value = val;
        }


        function renderForm() {
            const container = document.getElementById('formContent');
            const parentWrap = container.parentElement;
            
            if (currentModule === 'distribution') {
                parentWrap.classList.remove('max-w-2xl', 'overflow-hidden');
                parentWrap.classList.add('max-w-5xl', 'overflow-visible');
            } else {
                parentWrap.classList.remove('max-w-5xl', 'overflow-visible');
                parentWrap.classList.add('max-w-2xl', 'overflow-hidden');
            }

            const modeText = currentMode === 'edit' ? 'Manage' : 'Update';
            const btnColor = 'bg-[#c00000] hover:bg-red-700 shadow-red-100';
            let html = `<h4 class="text-2xl font-black text-slate-800 mb-8 uppercase tracking-tight italic">${modeText} ${currentModule}</h4>`;

            if (currentModule === 'item') {
                // ===== EDIT MODE: UPDATE / DELETE ITEMS (Full Implementation) =====
                const distOnlyStakeholders = rawStakeholders.filter(s => s.type === 'Distributor');

                const distOptHtml = distOnlyStakeholders.map(s =>
                    `<option value="${s.id}">${s.name}</option>`
                ).join('');

                html += `
                    <p class="text-slate-900 text-xs font-semibold mb-5 -mt-4 italic text-center">Select a mode, then make your selections below.</p>

                    {{-- Mode Toggle Buttons --}}
                    <div class="flex gap-3 mb-7" id="updateItemModeToggle">
                        <button type="button" id="btnModeUpdate"
                            onclick="switchUpdateItemMode('update')"
                            class="flex-1 py-3.5 rounded-2xl font-black text-sm text-center border-2 border-[#c00000] bg-red-50 text-[#c00000] transition-all">
                            âœï¸ Update / Rename
                        </button>
                        <button type="button" id="btnModeDelete"
                            onclick="switchUpdateItemMode('delete')"
                            class="flex-1 py-3.5 rounded-2xl font-black text-sm text-center border-2 border-slate-200 bg-white text-slate-900 transition-all hover:border-slate-300">
                            ðŸ—‘ï¸ Delete
                        </button>
                    </div>

                    {{-- ===================== UPDATE / RENAME PANEL ===================== --}}
                    <div id="panelUpdate" class="space-y-5">

                        <div class="grid grid-cols-2 gap-3 items-center">
                            {{-- Row 1: Category --}}
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-900 uppercase tracking-widest ml-1">Category</label>
                                <select id="uCategoryDd"
                                    onchange="uOnCategoryChange()"
                                    class="w-full p-3.5 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-slate-900 text-sm focus:ring-2 focus:ring-red-100 cursor-pointer transition-all">
                                    <option value="">-- Select Category --</option>
                                    ${rawCategories.map(c => `<option value="${c.id}">${c.name}</option>`).join('')}
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-900 uppercase tracking-widest ml-1">Rename Category To</label>
                                <input type="text" id="uCategoryRename" placeholder="Leave blank to keep current name"
                                    class="w-full p-3.5 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-slate-900 text-sm focus:ring-2 focus:ring-red-100 transition-all">
                            </div>

                            {{-- Row 2: Item --}}
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-900 uppercase tracking-widest ml-1">Item</label>
                                <select id="uItemDd"
                                    onchange="uOnItemChange()"
                                    class="w-full p-3.5 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-slate-900 text-sm focus:ring-2 focus:ring-red-100 cursor-pointer transition-all"
                                    disabled>
                                    <option value="">-- Select Item --</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-900 uppercase tracking-widest ml-1">Rename Item To</label>
                                <input type="text" id="uItemRename" placeholder="Leave blank to keep current name"
                                    class="w-full p-3.5 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-slate-900 text-sm focus:ring-2 focus:ring-red-100 transition-all"
                                    disabled>
                            </div>

                            {{-- Row 3: Sub-item --}}
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-900 uppercase tracking-widest ml-1">Sub-item</label>
                                <select id="uSubItemDd"
                                    onchange="uOnSubItemChange()"
                                    class="w-full p-3.5 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-slate-900 text-sm focus:ring-2 focus:ring-red-100 cursor-pointer transition-all"
                                    disabled>
                                    <option value="">-- Select Sub-item --</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-900 uppercase tracking-widest ml-1">Rename Sub-item To</label>
                                <input type="text" id="uSubItemRename" placeholder="Leave blank to keep current name"
                                    class="w-full p-3.5 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-slate-900 text-sm focus:ring-2 focus:ring-red-100 transition-all"
                                    disabled>
                            </div>
                        </div>

                        {{-- Distributor Transfer Row --}}
                        <div class="pt-4 border-t border-slate-100">
                            <label class="text-[10px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-2">Transfer Distributor Ownership</label>
                            <p class="text-[10px] text-slate-900 font-medium ml-1 mb-3">Select a sub-item above first. The left shows the current distributor; pick a new one on the right to transfer ownership.</p>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-1">
                                    <label class="text-[10px] font-black text-slate-900 uppercase tracking-widest ml-1">Current Distributor</label>
                                    <select id="uCurrentDist" disabled
                                        class="w-full p-3.5 bg-slate-100 border border-slate-200 rounded-2xl outline-none font-semibold text-slate-900 text-sm cursor-not-allowed">
                                        <option value="">-- No Sub-item Selected --</option>
                                        ${distOptHtml}
                                    </select>
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] font-black text-slate-900 uppercase tracking-widest ml-1">Transfer To</label>
                                    <select id="uNewDist" disabled
                                        class="w-full p-3.5 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-slate-900 text-sm focus:ring-2 focus:ring-red-100 cursor-pointer transition-all">
                                        <option value="">-- Select New Distributor --</option>
                                        ${distOptHtml}
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Update Panel Action Buttons --}}
                        <div class="flex gap-3 pt-2">
                            <button type="button" onclick="uClearAll()"
                                class="flex-1 py-4 rounded-2xl font-black text-sm border-2 border-slate-200 text-slate-900 hover:border-slate-300 hover:bg-slate-50 transition-all active:scale-95">
                                Clear
                            </button>
                            <button type="button" onclick="uSaveChanges()"
                                class="flex-[2] py-4 rounded-2xl font-black text-sm bg-[#c00000] hover:bg-red-700 text-white shadow-lg shadow-red-100 transition-all hover:-translate-y-0.5 active:scale-95">
                                Save Changes
                            </button>
                        </div>
                    </div>

                    {{-- ===================== DELETE PANEL ===================== --}}
                    <div id="panelDelete" class="space-y-5 hidden">

                        <div class="grid grid-cols-2 gap-3 items-start">
                            {{-- Row 1: Category --}}
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-900 uppercase tracking-widest ml-1">Category</label>
                                <select id="dCategoryDd"
                                    onchange="dOnCategoryChange()"
                                    class="w-full p-3.5 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-slate-900 text-sm focus:ring-2 focus:ring-red-100 cursor-pointer transition-all">
                                    <option value="">-- Select Category --</option>
                                    ${rawCategories.map(c => `<option value="${c.id}">${c.name}</option>`).join('')}
                                </select>
                            </div>
                            <div class="flex flex-col justify-end space-y-1 pb-0.5">
                                <label class="text-[10px] font-black text-slate-900 uppercase tracking-widest ml-1 invisible">Label</label>
                                <label class="flex items-center gap-3 p-3.5 bg-red-50 border border-red-100 rounded-2xl cursor-pointer group">
                                    <input type="checkbox" id="dCategoryChk" onchange="dOnCategoryChkChange()"
                                        class="w-4 h-4 rounded accent-[#c00000] cursor-pointer">
                                    <span class="text-sm font-black text-red-700">Delete Category</span>
                                </label>
                                <p id="dCategoryWarn" class="hidden text-[10px] font-bold text-red-600 ml-1 leading-tight mt-1">
                                    âš ï¸ All items and sub-items under this category will also be deleted.
                                </p>
                            </div>

                            {{-- Row 2: Item --}}
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-900 uppercase tracking-widest ml-1">Item</label>
                                <select id="dItemDd"
                                    onchange="dOnItemChange()"
                                    class="w-full p-3.5 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-slate-900 text-sm focus:ring-2 focus:ring-red-100 cursor-pointer transition-all"
                                    disabled>
                                    <option value="">-- Select Item --</option>
                                </select>
                            </div>
                            <div class="flex flex-col justify-end space-y-1 pb-0.5">
                                <label class="text-[10px] font-black text-slate-900 uppercase tracking-widest ml-1 invisible">Label</label>
                                <label class="flex items-center gap-3 p-3.5 bg-red-50 border border-red-100 rounded-2xl cursor-pointer group" id="dItemChkWrap">
                                    <input type="checkbox" id="dItemChk" onchange="dOnItemChkChange()"
                                        class="w-4 h-4 rounded accent-[#c00000] cursor-pointer" disabled>
                                    <span class="text-sm font-black text-red-700">Delete Item</span>
                                </label>
                                <p id="dItemWarn" class="hidden text-[10px] font-bold text-red-600 ml-1 leading-tight mt-1">
                                    âš ï¸ All sub-items under this item will also be deleted.
                                </p>
                            </div>

                            {{-- Row 3: Sub-item --}}
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-900 uppercase tracking-widest ml-1">Sub-item</label>
                                <select id="dSubItemDd"
                                    class="w-full p-3.5 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-slate-900 text-sm focus:ring-2 focus:ring-red-100 cursor-pointer transition-all"
                                    disabled>
                                    <option value="">-- Select Sub-item --</option>
                                </select>
                            </div>
                            <div class="flex flex-col justify-end space-y-1 pb-0.5">
                                <label class="text-[10px] font-black text-slate-900 uppercase tracking-widest ml-1 invisible">Label</label>
                                <label class="flex items-center gap-3 p-3.5 bg-red-50 border border-red-100 rounded-2xl cursor-pointer group" id="dSubItemChkWrap">
                                    <input type="checkbox" id="dSubItemChk"
                                        class="w-4 h-4 rounded accent-[#c00000] cursor-pointer" disabled>
                                    <span class="text-sm font-black text-red-700">Delete Sub-item</span>
                                </label>
                            </div>
                        </div>

                        {{-- Delete Panel Action Buttons --}}
                        <div class="flex gap-3 pt-2">
                            <button type="button" onclick="dClearAll()"
                                class="flex-1 py-4 rounded-2xl font-black text-sm border-2 border-slate-200 text-slate-900 hover:border-slate-300 hover:bg-slate-50 transition-all active:scale-95">
                                Clear
                            </button>
                            <button type="button" onclick="dSaveChanges()"
                                class="flex-[2] py-4 rounded-2xl font-black text-sm bg-[#c00000] hover:bg-red-700 text-white shadow-lg shadow-red-100 transition-all hover:-translate-y-0.5 active:scale-95">
                                Confirm Delete
                            </button>
                        </div>
                    </div>
                `;

            }

            container.innerHTML = html;
        }


        
        function getDistributorOptionsHtml() {
            let html = '<option value="">-- Distributor --</option>';
            const distributors = rawStakeholders.filter(s => s.type === 'Distributor');
            distributors.forEach(d => {
                html += `<option value="${d.id}">${d.name}</option>`;
            });
            return html;
        }

        function populateSourceStakeholders() {
            const select = document.getElementById('globalSourceDistributor');
            if (!select) return;
            let html = '<option value="">-- Select Source Distributor --</option>';
            
            const grouped = rawStakeholders.reduce((acc, obj) => {
                const key = obj.type || 'Other';
                if (!acc[key]) acc[key] = [];
                acc[key].push(obj);
                return acc;
            }, {});
            
            for (const type in grouped) {
                html += `<optgroup label="${type} Stakeholders">`;
                grouped[type].forEach(s => {
                    html += `<option value="${s.id}" data-name="${s.name}">${s.name}</option>`;
                });
                html += `</optgroup>`;
            }
            select.innerHTML = html;
            
            // Auto-select System Warehouse if it exists
            const systemWarehouse = rawStakeholders.find(s => s.type === 'System' && s.name.includes('Warehouse'));
            if (systemWarehouse) {
                select.value = systemWarehouse.id;
            }
        }



        let categoryDuplicateBlocked = false;

        function rebuildCategoryDropdown() {
            const dropdown = document.getElementById('categoryDropdownList');
            let html = '<div class="p-3 text-xs text-slate-900 font-bold uppercase tracking-widest">Select existing category</div>';
            if (rawCategories.length === 0) {
                html += '<div class="px-4 py-3 text-sm text-slate-900 italic">No existing categories</div>';
            } else {
                rawCategories.forEach(c => {
                    html += `<div onclick="selectExistingCategory(${c.id}, '${c.name.replace(/'/g, "\\'")}')"
                                 class="px-4 py-3 text-sm font-semibold text-slate-900 hover:bg-red-50 hover:text-[#c00000] cursor-pointer transition-colors">${c.name}</div>`;
                });
            }
            html += `<div onclick="clearCategorySelection()" class="px-4 py-3 text-xs font-bold text-slate-900 hover:bg-slate-50 cursor-pointer border-t border-slate-100 transition-colors">âœ• Clear selection (type new category)</div>`;
            dropdown.innerHTML = html;
        }



        function clearCategorySelection() {
            document.getElementById('existingCategoryId').value = '';
            document.getElementById('categoryName').value = '';
            document.getElementById('categoryName').readOnly = false;
            document.getElementById('categoryName').classList.remove('bg-emerald-50', 'border-emerald-200', 'bg-blue-50', 'border-blue-400');
            document.getElementById('categoryExistingHint').classList.add('hidden');
            const newHint = document.getElementById('categoryNewHint');
            if(newHint) newHint.classList.add('hidden');
            document.getElementById('categoryDropdownList').classList.add('hidden');
            document.getElementById('categoryName').focus();
            checkCategoryDuplicate();
        }




        // =============================================
        // UPDATE ITEM: PANEL TOGGLE
        // =============================================
        function switchUpdateItemMode(mode) {
            const panelUpdate = document.getElementById('panelUpdate');
            const panelDelete = document.getElementById('panelDelete');
            const btnUpdate   = document.getElementById('btnModeUpdate');
            const btnDelete   = document.getElementById('btnModeDelete');
            if (!panelUpdate || !panelDelete) return;

            if (mode === 'update') {
                panelUpdate.classList.remove('hidden');
                panelDelete.classList.add('hidden');
                btnUpdate.className = 'flex-1 py-3.5 rounded-2xl font-black text-sm text-center border-2 border-[#c00000] bg-red-50 text-[#c00000] transition-all';
                btnDelete.className  = 'flex-1 py-3.5 rounded-2xl font-black text-sm text-center border-2 border-slate-200 bg-white text-slate-900 transition-all hover:border-slate-300';
            } else {
                panelUpdate.classList.add('hidden');
                panelDelete.classList.remove('hidden');
                btnDelete.className  = 'flex-1 py-3.5 rounded-2xl font-black text-sm text-center border-2 border-[#c00000] bg-red-50 text-[#c00000] transition-all';
                btnUpdate.className = 'flex-1 py-3.5 rounded-2xl font-black text-sm text-center border-2 border-slate-200 bg-white text-slate-900 transition-all hover:border-slate-300';
            }
        }

        // =============================================
        // UPDATE / RENAME PANEL LOGIC
        // =============================================

        function uOnCategoryChange() {
            const catId = document.getElementById('uCategoryDd').value;
            const itemDd = document.getElementById('uItemDd');
            const itemRename = document.getElementById('uItemRename');

            // Reset downstream
            itemDd.innerHTML = '<option value="">-- Select Item --</option>';
            itemDd.disabled = !catId;
            if (itemRename) { itemRename.value = ''; itemRename.disabled = !catId; }
            uResetSubItem();

            if (catId) {
                const filtered = rawItems.filter(i => String(i.category_id) === String(catId));
                filtered.forEach(i => {
                    itemDd.innerHTML += `<option value="${i.id}">${i.name}</option>`;
                });
            }
            uResetDistributor();
        }

        function uOnItemChange() {
            const itemId = document.getElementById('uItemDd').value;
            const subDd  = document.getElementById('uSubItemDd');
            const subRename = document.getElementById('uSubItemRename');

            subDd.innerHTML = '<option value="">-- Select Sub-item --</option>';
            subDd.disabled = !itemId;
            if (subRename) { subRename.value = ''; subRename.disabled = !itemId; }
            uResetDistributor();

            if (itemId) {
                const filtered = rawSubItems.filter(s => String(s.item_id) === String(itemId));
                filtered.forEach(s => {
                    subDd.innerHTML += `<option value="${s.id}">${s.name}</option>`;
                });
            }
        }

        function uOnSubItemChange() {
            const subId  = document.getElementById('uSubItemDd').value;
            const curDist = document.getElementById('uCurrentDist');
            const newDist = document.getElementById('uNewDist');

            if (!subId) {
                uResetDistributor();
                return;
            }

            // Enable new-distributor dropdown
            newDist.disabled = false;
            newDist.classList.remove('cursor-not-allowed', 'bg-slate-100', 'text-slate-900');
            newDist.classList.add('cursor-pointer', 'bg-slate-50', 'text-slate-900');

            // Auto-fill current distributor
            const sub = rawSubItems.find(s => String(s.id) === String(subId));
            if (sub && sub.distributor_id) {
                curDist.value = String(sub.distributor_id);
            } else {
                curDist.value = '';
            }
        }

        function uResetSubItem() {
            const subDd = document.getElementById('uSubItemDd');
            const subRename = document.getElementById('uSubItemRename');
            if (subDd) { subDd.innerHTML = '<option value="">-- Select Sub-item --</option>'; subDd.disabled = true; }
            if (subRename) { subRename.value = ''; subRename.disabled = true; }
            uResetDistributor();
        }

        function uResetDistributor() {
            const curDist = document.getElementById('uCurrentDist');
            const newDist = document.getElementById('uNewDist');
            if (curDist) { curDist.value = ''; }
            if (newDist) {
                newDist.value = '';
                newDist.disabled = true;
                newDist.classList.remove('cursor-pointer', 'bg-slate-50', 'text-slate-900');
                newDist.classList.add('cursor-not-allowed', 'bg-slate-100', 'text-slate-900');
            }
        }

        function uClearAll() {
            const dd = (id) => document.getElementById(id);
            if (dd('uCategoryDd'))  { dd('uCategoryDd').value = ''; }
            if (dd('uCategoryRename')) { dd('uCategoryRename').value = ''; }
            if (dd('uItemDd'))      { dd('uItemDd').innerHTML = '<option value="">-- Select Item --</option>'; dd('uItemDd').disabled = true; }
            if (dd('uItemRename'))  { dd('uItemRename').value = ''; dd('uItemRename').disabled = true; }
            uResetSubItem();
        }

        async function uSaveChanges() {
            const catId       = document.getElementById('uCategoryDd')?.value;
            const catRename   = document.getElementById('uCategoryRename')?.value.trim();
            const itemId      = document.getElementById('uItemDd')?.value;
            const itemRename  = document.getElementById('uItemRename')?.value.trim();
            const subId       = document.getElementById('uSubItemDd')?.value;
            const subRename   = document.getElementById('uSubItemRename')?.value.trim();
            const curDistId   = document.getElementById('uCurrentDist')?.value;
            const newDistId   = document.getElementById('uNewDist')?.value;

            // Build a summary of changes
            const changes = [];
            if (catId && catRename)  changes.push(`Rename category to "${catRename}"`);
            if (itemId && itemRename) changes.push(`Rename item to "${itemRename}"`);
            if (subId && subRename)  changes.push(`Rename sub-item to "${subRename}"`);
            if (subId && newDistId && newDistId !== curDistId) changes.push(`Transfer sub-item distributor`);

            if (changes.length === 0) {
                Swal.fire({
                    title: 'Nothing to Save',
                    text: 'Please make a selection and fill in at least one rename field or select a new distributor.',
                    icon: 'info',
                    confirmButtonColor: '#c00000',
                    customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' }
                });
                return;
            }

            const result = await Swal.fire({
                title: 'Confirm Changes',
                html: `<div class="text-left text-sm space-y-1">${changes.map(c => `<div class="flex gap-2"><span class="text-emerald-500">âœ“</span><span>${c}</span></div>`).join('')}</div>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#c00000',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Save Changes',
                cancelButtonText: 'Cancel',
                customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6', cancelButton: 'rounded-xl font-bold px-6' }
            });

            if (!result.isConfirmed) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const errors = [];
            const successes = [];

            // --- Rename calls ---
            const renameTasks = [];
            if (catId && catRename)   renameTasks.push({ type: 'category', id: catId, new_name: catRename });
            if (itemId && itemRename) renameTasks.push({ type: 'item',     id: itemId, new_name: itemRename });
            if (subId && subRename)   renameTasks.push({ type: 'sub_item', id: subId,  new_name: subRename });

            for (const task of renameTasks) {
                try {
                    const res = await fetch('/inventory-setup/rename', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: JSON.stringify(task)
                    });
                    const data = await res.json();
                    if (data.success) {
                        successes.push(data.message);
                        // Update local rawData so re-renders reflect changes instantly
                        if (task.type === 'category') { const c = rawCategories.find(x => String(x.id) === String(task.id)); if (c) c.name = task.new_name; }
                        if (task.type === 'item')     { const i = rawItems.find(x => String(x.id) === String(task.id));     if (i) i.name = task.new_name; }
                        if (task.type === 'sub_item') { const s = rawSubItems.find(x => String(x.id) === String(task.id));  if (s) s.name = task.new_name; }
                    } else {
                        errors.push(data.message);
                    }
                } catch (e) {
                    errors.push('Network error during rename.');
                }
            }

            // --- Transfer distributor call ---
            if (subId && newDistId && newDistId !== curDistId) {
                try {
                    const res = await fetch('/inventory-setup/transfer-distributor', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: JSON.stringify({ sub_item_id: subId, new_distributor_id: newDistId })
                    });
                    const data = await res.json();
                    if (data.success) {
                        successes.push(data.message);
                        // Update local rawSubItems distributor reference
                        const s = rawSubItems.find(x => String(x.id) === String(subId));
                        if (s) { s.distributor_id = newDistId; const nd = rawStakeholders.find(x => String(x.id) === String(newDistId)); if (nd) s.distributor_name = nd.name; }
                    } else {
                        errors.push(data.message);
                    }
                } catch (e) {
                    errors.push('Network error during distributor transfer.');
                }
            }

            if (errors.length > 0) {
                Swal.fire({
                    title: 'Some Changes Failed',
                    html: `<div class="text-left text-sm space-y-1">
                        ${successes.map(m => `<div class="text-emerald-600">âœ“ ${m}</div>`).join('')}
                        ${errors.map(m => `<div class="text-red-600">âœ— ${m}</div>`).join('')}
                    </div>`,
                    icon: 'warning',
                    confirmButtonColor: '#c00000',
                    customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' }
                });
            } else {
                Swal.fire({
                    title: 'Changes Saved!',
                    text: successes.join(' '),
                    icon: 'success',
                    confirmButtonColor: '#10b981',
                    customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' }
                });
                uClearAll();
            }
        }

        // =============================================
        // DELETE PANEL LOGIC
        // =============================================

        function dOnCategoryChange() {
            const catId = document.getElementById('dCategoryDd').value;
            const itemDd = document.getElementById('dItemDd');
            const itemChk = document.getElementById('dItemChk');

            // Reset item+subitem downstream
            itemDd.innerHTML = '<option value="">-- Select Item --</option>';
            itemDd.disabled  = !catId || document.getElementById('dCategoryChk').checked;
            if (itemChk) { itemChk.checked = false; itemChk.disabled = !catId || document.getElementById('dCategoryChk').checked; }
            dResetSubItem();

            if (catId) {
                const filtered = rawItems.filter(i => String(i.category_id) === String(catId));
                filtered.forEach(i => { itemDd.innerHTML += `<option value="${i.id}">${i.name}</option>`; });
            }
        }

        function dOnItemChange() {
            const itemId = document.getElementById('dItemDd').value;
            const subDd  = document.getElementById('dSubItemDd');
            const subChk = document.getElementById('dSubItemChk');

            subDd.innerHTML = '<option value="">-- Select Sub-item --</option>';
            subDd.disabled  = !itemId || document.getElementById('dItemChk').checked;
            if (subChk) { subChk.checked = false; subChk.disabled = !itemId || document.getElementById('dItemChk').checked; }

            if (itemId) {
                const filtered = rawSubItems.filter(s => String(s.item_id) === String(itemId));
                filtered.forEach(s => { subDd.innerHTML += `<option value="${s.id}">${s.name}</option>`; });
            }
        }

        function dOnCategoryChkChange() {
            const chk    = document.getElementById('dCategoryChk');
            const warn   = document.getElementById('dCategoryWarn');
            const itemDd = document.getElementById('dItemDd');
            const itemChk = document.getElementById('dItemChk');
            const subDd  = document.getElementById('dSubItemDd');
            const subChk = document.getElementById('dSubItemChk');
            const itemWarn = document.getElementById('dItemWarn');

            if (chk.checked) {
                warn.classList.remove('hidden');
                itemDd.disabled = true;
                subDd.disabled  = true;
                if (itemChk) { itemChk.checked = false; itemChk.disabled = true; }
                if (subChk)  { subChk.checked  = false; subChk.disabled  = true; }
                if (itemWarn) itemWarn.classList.add('hidden');
            } else {
                warn.classList.add('hidden');
                const catId = document.getElementById('dCategoryDd').value;
                itemDd.disabled  = !catId;
                if (itemChk) itemChk.disabled = !catId;
                // Sub-item stays disabled until item is chosen
                subDd.disabled = true;
                if (subChk) subChk.disabled = true;
            }
        }

        function dOnItemChkChange() {
            const chk   = document.getElementById('dItemChk');
            const warn  = document.getElementById('dItemWarn');
            const subDd = document.getElementById('dSubItemDd');
            const subChk = document.getElementById('dSubItemChk');

            if (chk.checked) {
                warn.classList.remove('hidden');
                subDd.disabled = true;
                if (subChk) { subChk.checked = false; subChk.disabled = true; }
            } else {
                warn.classList.add('hidden');
                const itemId = document.getElementById('dItemDd').value;
                subDd.disabled = !itemId;
                if (subChk) subChk.disabled = !itemId;
            }
        }

        function dResetSubItem() {
            const subDd  = document.getElementById('dSubItemDd');
            const subChk = document.getElementById('dSubItemChk');
            if (subDd)  { subDd.innerHTML = '<option value="">-- Select Sub-item --</option>'; subDd.disabled = true; }
            if (subChk) { subChk.checked = false; subChk.disabled = true; }
        }

        function dClearAll() {
            const dd = (id) => document.getElementById(id);
            if (dd('dCategoryDd'))  { dd('dCategoryDd').value = ''; }
            if (dd('dCategoryChk')) { dd('dCategoryChk').checked = false; }
            if (dd('dCategoryWarn')) dd('dCategoryWarn').classList.add('hidden');
            if (dd('dItemDd'))      { dd('dItemDd').innerHTML = '<option value="">-- Select Item --</option>'; dd('dItemDd').disabled = true; }
            if (dd('dItemChk'))     { dd('dItemChk').checked = false; dd('dItemChk').disabled = true; }
            if (dd('dItemWarn'))    dd('dItemWarn').classList.add('hidden');
            dResetSubItem();
        }

        async function dSaveChanges() {
            const catChk    = document.getElementById('dCategoryChk');
            const itemChk   = document.getElementById('dItemChk');
            const subChk    = document.getElementById('dSubItemChk');
            const catId     = document.getElementById('dCategoryDd')?.value;
            const itemId    = document.getElementById('dItemDd')?.value;
            const subId     = document.getElementById('dSubItemDd')?.value;

            let deleteType = null;
            let deleteId   = null;
            let confirmMsg = '';

            if (catChk?.checked && catId) {
                deleteType = 'category';
                deleteId   = catId;
                const catName = document.getElementById('dCategoryDd').options[document.getElementById('dCategoryDd').selectedIndex]?.text || 'this category';
                confirmMsg = `Delete category "<b>${catName}</b>" and ALL its items, sub-items, and ownership records?`;
            } else if (itemChk?.checked && itemId) {
                deleteType = 'item';
                deleteId   = itemId;
                const itemName = document.getElementById('dItemDd').options[document.getElementById('dItemDd').selectedIndex]?.text || 'this item';
                confirmMsg = `Delete item "<b>${itemName}</b>" and ALL its sub-items and ownership records?`;
            } else if (subChk?.checked && subId) {
                deleteType = 'sub_item';
                deleteId   = subId;
                const subName = document.getElementById('dSubItemDd').options[document.getElementById('dSubItemDd').selectedIndex]?.text || 'this sub-item';
                confirmMsg = `Delete sub-item "<b>${subName}</b>" and all its ownership records?`;
            }

            if (!deleteType || !deleteId) {
                Swal.fire({
                    title: 'Nothing to Delete',
                    text: 'Please select a record and check the corresponding Delete checkbox.',
                    icon: 'info',
                    confirmButtonColor: '#c00000',
                    customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' }
                });
                return;
            }

            const result = await Swal.fire({
                title: 'Confirm Deletion',
                html: `<div class="text-sm text-slate-900">${confirmMsg}</div><div class="text-xs text-red-500 font-bold mt-3">âš ï¸ This action cannot be undone.</div>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#c00000',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
                customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6', cancelButton: 'rounded-xl font-bold px-6' }
            });

            if (!result.isConfirmed) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            try {
                const res = await fetch('/inventory-setup/delete', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({ type: deleteType, id: deleteId })
                });
                const data = await res.json();
                if (data.success) {
                    // Remove deleted records from local JS arrays for instant UI refresh
                    if (deleteType === 'category') {
                        const itemIds = rawItems.filter(i => String(i.category_id) === String(deleteId)).map(i => i.id);
                        rawItems.splice(0, rawItems.length, ...rawItems.filter(i => String(i.category_id) !== String(deleteId)));
                        rawSubItems.splice(0, rawSubItems.length, ...rawSubItems.filter(s => !itemIds.map(String).includes(String(s.item_id))));
                        rawCategories.splice(0, rawCategories.length, ...rawCategories.filter(c => String(c.id) !== String(deleteId)));
                    } else if (deleteType === 'item') {
                        rawSubItems.splice(0, rawSubItems.length, ...rawSubItems.filter(s => String(s.item_id) !== String(deleteId)));
                        rawItems.splice(0, rawItems.length, ...rawItems.filter(i => String(i.id) !== String(deleteId)));
                    } else if (deleteType === 'sub_item') {
                        rawSubItems.splice(0, rawSubItems.length, ...rawSubItems.filter(s => String(s.id) !== String(deleteId)));
                    }

                    Swal.fire({
                        title: 'Deleted!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonColor: '#10b981',
                        customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' }
                    });
                    dClearAll();
                } else {
                    Swal.fire({
                        title: 'Delete Failed',
                        text: data.message,
                        icon: 'error',
                        confirmButtonColor: '#c00000',
                        customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' }
                    });
                }
            } catch (e) {
                Swal.fire({ title: 'Network Error', text: 'Could not reach the server.', icon: 'error', confirmButtonColor: '#c00000' });
            }
        }

        // =============================================
        // ASSET DISTRIBUTION MODULE
        // =============================================
        let preSelectedSchools = []; // Array of objects { id, name, uid } to allow duplicates
        let distTabsData = []; // State for each tab
        let currentActiveTab = 0;

        // --- Phase 1: Pre-selection ---
        function switchRecipTab(tab) {
            document.getElementById('currentRecipTab').value = tab;
            const btnSchool = document.getElementById('tabRecipSchoolBtn');
            const btnIndiv = document.getElementById('tabRecipIndivBtn');
            const search = document.getElementById('preDistSchoolSearch');
            
            if (tab === 'school') {
                btnSchool.className = "text-[10px] font-bold px-3 py-1 rounded-md bg-white shadow-sm text-slate-900 transition-all";
                btnIndiv.className = "text-[10px] font-bold px-3 py-1 rounded-md text-slate-900 hover:text-slate-900 transition-all bg-transparent";
                search.placeholder = "Search schools...";
            } else {
                btnIndiv.className = "text-[10px] font-bold px-3 py-1 rounded-md bg-white shadow-sm text-slate-900 transition-all";
                btnSchool.className = "text-[10px] font-bold px-3 py-1 rounded-md text-slate-900 hover:text-slate-900 transition-all bg-transparent";
                search.placeholder = "Search individual records or offices...";
            }
            
            filterPreDistSchools();
            document.getElementById('preDistSchoolSearch').focus();
        }






        // --- Phase 2: Tabs ---








        // Calculate effective remaining stock for a specific sub-item ID (which is distinct per distributor)




        // When the user changes which distributor's stock they want to deduct from









        // =============================================
        // RENAME MODULE â€” Update Items logic
        // =============================================
        let renameTargetId   = null;
        let renameTargetType = null;
        let renameTargetName = null;

        function onRenameTypeChange() {
            const type = document.getElementById('renameType').value;
            renameTargetId   = null;
            renameTargetType = type || null;
            renameTargetName = null;

            const cascadeRow  = document.getElementById('renameCascadeRow');
            const catWrap     = document.getElementById('renameCatWrap');
            const itemWrap    = document.getElementById('renameItemWrap');
            const subWrap     = document.getElementById('renameSubWrap');
            const inputWrap   = document.getElementById('renameInputWrap');
            const submitBtn   = document.getElementById('renameSubmitBtn');

            // Reset all child selects
            document.getElementById('renameCatSelect').value  = '';
            document.getElementById('renameItemSelect').innerHTML = '<option value="">-- Choose Item --</option>';
            document.getElementById('renameSubSelect').innerHTML  = '<option value="">-- Choose Sub-Item --</option>';
            document.getElementById('renameNewName').value     = '';
            document.getElementById('renameCurrentHint').textContent = '';

            if (typeof hideActionUI === 'function') hideActionUI();

            if (!type) {
                cascadeRow.classList.add('hidden');
                catWrap.classList.add('hidden');
                itemWrap.classList.add('hidden');
                subWrap.classList.add('hidden');
                return;
            }

            cascadeRow.classList.remove('hidden');
            // Category dropdown always shows
            catWrap.classList.remove('hidden');
            // Item dropdown shows for 'item' and 'sub_item'
            itemWrap.classList.toggle('hidden', type === 'category');
            // Sub-item dropdown shows only for 'sub_item'
            subWrap.classList.toggle('hidden', type !== 'sub_item');
        }

        function onRenameCatChange() {
            const type       = document.getElementById('renameType').value;
            const catSelect  = document.getElementById('renameCatSelect');
            const catId      = catSelect.value;
            const catName    = catId ? catSelect.options[catSelect.selectedIndex].text : '';

            const itemSelect = document.getElementById('renameItemSelect');
            const subSelect  = document.getElementById('renameSubSelect');

            renameTargetId   = null;
            renameTargetName = null;

            // Reset dependent dropdowns
            itemSelect.innerHTML = '<option value="">-- Choose Item --</option>';
            subSelect.innerHTML  = '<option value="">-- Choose Sub-Item --</option>';
            document.getElementById('renameNewName').value = '';
            document.getElementById('renameCurrentHint').textContent = '';
            if (typeof hideActionUI === 'function') hideActionUI();

            if (type === 'category') {
                // Selecting a category IS the target
                if (catId) {
                    renameTargetId   = parseInt(catId);
                    renameTargetType = 'category';
                    renameTargetName = catName;
                    document.getElementById('renameCurrentHint').textContent = `Currently named: "${catName}"`;
                    if (typeof triggerActionUI === 'function') triggerActionUI();
                }
                return;
            }

            // For item & sub_item: populate items filtered by chosen category
            if (!catId) return;
            const items = rawItems.filter(i => i.category_id == catId);
            itemSelect.innerHTML = '<option value="">-- Choose Item --</option>';
            items.forEach(i => {
                itemSelect.innerHTML += `<option value="${i.id}" data-name="${i.name.replace(/"/g,'&quot;')}">${i.name}</option>`;
            });
        }

        function onRenameItemChange() {
            const type      = document.getElementById('renameType').value;
            const itemSel   = document.getElementById('renameItemSelect');
            const itemId    = itemSel.value;
            const itemName  = itemId ? itemSel.options[itemSel.selectedIndex].text : '';
            const subSelect = document.getElementById('renameSubSelect');

            renameTargetId   = null;
            renameTargetName = null;

            subSelect.innerHTML = '<option value="">-- Choose Sub-Item --</option>';
            document.getElementById('renameNewName').value = '';
            document.getElementById('renameCurrentHint').textContent = '';
            if (typeof hideActionUI === 'function') hideActionUI();

            if (type === 'item') {
                if (itemId) {
                    renameTargetId   = parseInt(itemId);
                    renameTargetType = 'item';
                    renameTargetName = itemName;
                    document.getElementById('renameCurrentHint').textContent = `Currently named: "${itemName}"`;
                    if (typeof triggerActionUI === 'function') triggerActionUI();
                }
                return;
            }

            // For sub_item: populate sub-items filtered by chosen item
            if (!itemId) return;
            const subs = rawSubItems.filter(s => s.item_id == itemId);
            subSelect.innerHTML = '<option value="">-- Choose Sub-Item --</option>';
            subs.forEach(s => {
                subSelect.innerHTML += `<option value="${s.id}">${s.name}</option>`;
            });
        }

        function onRenameSubChange() {
            const subSel   = document.getElementById('renameSubSelect');
            const subId    = subSel.value;
            const subName  = subId ? subSel.options[subSel.selectedIndex].text : '';

            renameTargetId   = null;
            renameTargetName = null;

            document.getElementById('renameNewName').value = '';
            document.getElementById('renameCurrentHint').textContent = '';
            if (typeof hideActionUI === 'function') hideActionUI();

            if (subId) {
                renameTargetId   = parseInt(subId);
                renameTargetType = 'sub_item';
                renameTargetName = subName;
                document.getElementById('renameCurrentHint').textContent = `Currently named: "${subName}"`;
                if (typeof triggerActionUI === 'function') triggerActionUI();
            }
        }

        async function submitRename() {
            const newName = document.getElementById('renameNewName').value.trim();
            if (!renameTargetId || !renameTargetType) {
                Swal.fire({ title: 'Nothing Selected', text: 'Please complete all selections before renaming.', icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                return;
            }
            if (!newName) {
                Swal.fire({ title: 'New Name Required', text: 'Please enter a new name.', icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                return;
            }
            if (newName.toLowerCase() === renameTargetName.toLowerCase()) {
                Swal.fire({ title: 'No Change Detected', text: 'The new name is the same as the current name.', icon: 'info', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                return;
            }

            const typeLabel = renameTargetType === 'sub_item' ? 'Sub-Item' : (renameTargetType.charAt(0).toUpperCase() + renameTargetType.slice(1));
            const result = await Swal.fire({
                title: `Rename ${typeLabel}`,
                html: `Rename <b>"${renameTargetName}"</b> to <b>"${newName}"</b>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#c00000', cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, rename it!',
                customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6', cancelButton: 'rounded-xl font-bold px-6' }
            });

            if (!result.isConfirmed) return;

            Swal.fire({ title: 'Renaming...', allowOutsideClick: false, showConfirmButton: false, willOpen: () => Swal.showLoading(), customClass: { popup: 'rounded-[2rem]' } });

            try {
                const res = await fetch("#", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ type: renameTargetType, id: renameTargetId, new_name: newName })
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    // Update local raw data so the dropdowns reflect the change immediately
                    if (renameTargetType === 'category') {
                        const cat = rawCategories.find(c => c.id === renameTargetId);
                        if (cat) cat.name = newName;
                    } else if (renameTargetType === 'item') {
                        const item = rawItems.find(i => i.id === renameTargetId);
                        if (item) item.name = newName;
                    } else if (renameTargetType === 'sub_item') {
                        const sub = rawSubItems.find(s => s.id === renameTargetId);
                        if (sub) sub.name = newName;
                    }
                    Swal.fire({ title: 'Success!', text: data.message, icon: 'success', confirmButtonColor: '#10b981', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } })
                    .then(() => {
                        // Reset the rename form
                        document.getElementById('renameType').value = '';
                        onRenameTypeChange();
                    });
                } else {
                    Swal.fire({ title: 'Error', text: data.message || 'An error occurred.', icon: 'error', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                }
            } catch(e) {
                Swal.fire({ title: 'Request Failed', text: e.message, icon: 'error', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
            }
        }

        // ===== EDIT MODE (Update/Delete) ACTIONS =====
        let currentEditAction = 'update';

        function setEditAction(action) {
            currentEditAction = action;
            const updateBtn = document.getElementById('editModeUpdateBtn');
            const deleteBtn = document.getElementById('editModeDeleteBtn');
            const inputWrap = document.getElementById('renameInputWrap');
            const warnWrap  = document.getElementById('deleteWarningWrap');
            const rnSubmit  = document.getElementById('renameSubmitBtn');
            const delSubmit = document.getElementById('deleteSubmitBtn');

            if (action === 'update') {
                updateBtn.className = 'flex-1 py-4 rounded-2xl font-bold text-sm transition-all border-2 border-[#c00000] bg-red-50 text-[#c00000]';
                deleteBtn.className = 'flex-1 py-4 rounded-2xl font-bold text-sm transition-all border-2 border-slate-200 bg-white text-slate-900 hover:border-red-300 hover:text-red-400';
                if (renameTargetId) {
                    inputWrap.classList.remove('hidden');
                    rnSubmit.classList.remove('hidden');
                }
                warnWrap.classList.add('hidden');
                delSubmit.classList.add('hidden');
            } else {
                updateBtn.className = 'flex-1 py-4 rounded-2xl font-bold text-sm transition-all border-2 border-slate-200 bg-white text-slate-900 hover:border-red-300 hover:text-red-400';
                deleteBtn.className = 'flex-1 py-4 rounded-2xl font-bold text-sm transition-all border-2 border-red-600 bg-red-50 text-red-600';
                inputWrap.classList.add('hidden');
                rnSubmit.classList.add('hidden');
                if (renameTargetId) {
                    warnWrap.classList.remove('hidden');
                    delSubmit.classList.remove('hidden');
                    previewDeleteImpact();
                }
            }
        }

        function triggerActionUI() {
            if (currentEditAction === 'update') {
                document.getElementById('renameInputWrap').classList.remove('hidden');
                document.getElementById('renameSubmitBtn').classList.remove('hidden');
                document.getElementById('deleteWarningWrap').classList.add('hidden');
                document.getElementById('deleteSubmitBtn').classList.add('hidden');
            } else {
                document.getElementById('renameInputWrap').classList.add('hidden');
                document.getElementById('renameSubmitBtn').classList.add('hidden');
                document.getElementById('deleteWarningWrap').classList.remove('hidden');
                document.getElementById('deleteCurrentHint').textContent = `Record to delete: "${renameTargetName}"`;
                document.getElementById('deleteSubmitBtn').classList.remove('hidden');
                previewDeleteImpact();
            }
        }

        function hideActionUI() {
            document.getElementById('renameInputWrap').classList.add('hidden');
            document.getElementById('renameSubmitBtn').classList.add('hidden');
            document.getElementById('deleteWarningWrap').classList.add('hidden');
            document.getElementById('deleteSubmitBtn').classList.add('hidden');
        }

        async function previewDeleteImpact() {
            const impactBox = document.getElementById('deleteImpactBox');
            const impactTxt = document.getElementById('deleteImpactDetails');
            
            impactBox.classList.remove('hidden');
            impactTxt.innerHTML = '<span class="text-slate-900 animate-pulse">Calculating impact...</span>';
            
            try {
                const res = await fetch("#", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ type: renameTargetType, id: renameTargetId })
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    const i = data.impact;
                    let msgs = [];
                    if (renameTargetType === 'category' && i.items > 0) msgs.push(`â€¢ <b>${i.items}</b> associated Item(s)`);
                    if (['category', 'item'].includes(renameTargetType) && i.sub_items > 0) msgs.push(`â€¢ <b>${i.sub_items}</b> Sub-Item(s) specification(s)`);
                    if (i.total_stock > 0) msgs.push(`â€¢ <b>${i.total_stock}</b> items in master stock`);
                    if (i.ownerships > 0) msgs.push(`â€¢ <b>${i.ownerships}</b> distributed physical asset(s) across <b>${i.schools_affected}</b> school(s)`);
                    
                    if (msgs.length === 0) {
                        impactTxt.innerHTML = '<span class="text-emerald-600 font-bold">Safe to delete: No associated records or stock will be affected.</span>';
                        impactBox.classList.replace('bg-red-50', 'bg-emerald-50');
                        impactBox.classList.replace('border-red-200', 'border-emerald-200');
                    } else {
                        impactTxt.innerHTML = 'This action will instantly delete the record AND cascade to permanently destroy:<br>' + msgs.join('<br>');
                        impactBox.classList.replace('bg-emerald-50', 'bg-red-50');
                        impactBox.classList.replace('border-emerald-200', 'border-red-200');
                    }
                } else {
                    impactTxt.innerHTML = '<span class="text-slate-900">Failed to calculate impact.</span>';
                }
            } catch (e) {
                impactTxt.innerHTML = '<span class="text-slate-900">Failed to calculate impact.</span>';
            }
        }

        async function submitDelete() {
            if (!renameTargetId || !renameTargetType) return;
            
            const typeLabel = renameTargetType === 'sub_item' ? 'Sub-Item' : (renameTargetType.charAt(0).toUpperCase() + renameTargetType.slice(1));
            const result = await Swal.fire({
                title: 'CRITICAL DELETION',
                html: `Are you absolutely sure you want to permanently delete the ${typeLabel} <b>"${renameTargetName}"</b>?<br><br><span class="text-red-600 font-bold text-sm">This action cannot be undone and will destroy any associated physical inventory records!</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#c00000', cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, permanently destroy it',
                cancelButtonText: 'Cancel',
                customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6', cancelButton: 'rounded-xl font-bold px-6' }
            });

            if (!result.isConfirmed) return;

            Swal.fire({ title: 'Destroying Records...', allowOutsideClick: false, showConfirmButton: false, willOpen: () => Swal.showLoading(), customClass: { popup: 'rounded-[2rem]' } });

            try {
                const res = await fetch("#", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ type: renameTargetType, id: renameTargetId })
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    Swal.fire({ title: 'Deleted!', text: data.message, icon: 'success', confirmButtonColor: '#10b981', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } })
                    .then(() => { location.reload(); }); // Reload to refresh all related dropdowns and UI
                } else {
                    Swal.fire({ title: 'Error', text: data.message || 'An error occurred.', icon: 'error', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                }
            } catch(e) {
                Swal.fire({ title: 'Request Failed', text: e.message, icon: 'error', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
            }
        }

        // Close dropdowns on outside click handler
        document.addEventListener('click', function(e) {
            if(!document.getElementById('preDistSchoolDropdownList')?.classList.contains('hidden')) {
                const el = document.getElementById('preDistSchoolSearch');
                const dd = document.getElementById('preDistSchoolDropdownList');
                if(el && dd && !el.contains(e.target) && !dd.contains(e.target)) dd.classList.add('hidden');
            }
            
            distTabsData.forEach((_, i) => {
                ['Cat','Item','Sub'].forEach(type => {
                    const search = document.getElementById(`tab${type}Search_${i}`);
                    const dd = document.getElementById(`tab${type}Dropdown_${i}`);
                    if (search && dd && !dd.classList.contains('hidden') && !search.contains(e.target) && !dd.contains(e.target)) {
                        dd.classList.add('hidden');
                    }
                });
            });
            
            [['itemDropdownList','itemDropdownBtn','itemName'],['categoryDropdownList','categoryDropdownBtn','categoryName'],
             ['itemCategoryDropdownList','itemCategoryDropdownBtn','itemCategoryName']
            ].forEach(([ddId, btnId, inputId]) => {
                const dd = document.getElementById(ddId), btn = document.getElementById(btnId), inp = document.getElementById(inputId);
                if (dd && btn && !dd.contains(e.target) && !btn.contains(e.target) && e.target !== inp && e.target.id !== inputId) dd.classList.add('hidden');
            });
        });
    



        // ============================================================
        // DISTRIBUTION MODULE â€” RECIPIENT REGISTRY FUNCTIONS
        // ============================================================

        let distRecipientCount = 0;
        let distAddedIds = []; // tracks stakeholder IDs already in the list
        let distRecipientsCache = {}; // { id: { displayName, subLabel } } â€” survives rawStakeholders gap for NEW entries




        async function distAddRecipientToList() {
            const type = document.getElementById('distSourceType')?.value;
            if (!type) {
                Swal.fire('Required', 'Please select an Entity Type first.', 'warning');
                return;
            }

            const schoolInput = document.getElementById('distSchoolInput');
            const schoolId = parseInt(schoolInput?.dataset?.schoolId || '0') || null;
            const externalName = document.getElementById('distExternalInput')?.value?.trim() || '';
            const personName  = document.getElementById('distPersonnelName')?.value?.trim() || '';
            const position    = document.getElementById('distPersonnelPosition')?.value?.trim() || '';

            // Validation
            if (type === 'school' && !schoolId) {
                Swal.fire('Required', 'Please search and select a school from the dropdown.', 'warning');
                return;
            }
            if (type === 'external' && !externalName) {
                Swal.fire('Required', 'Please enter the external office or organization name.', 'warning');
                return;
            }

            // Show loading state
            const addBtn = document.querySelector('[onclick="distAddRecipientToList()"]');
            if (addBtn) { addBtn.disabled = true; addBtn.innerText = 'Adding...'; }

            try {
                const resp = await fetch('{{ route("recipients.add") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ entity_type: type, school_id: schoolId, external_name: externalName, person_name: personName, position })
                });

                const data = await resp.json();

                if (!resp.ok) {
                    Swal.fire('Error', data.error || 'Something went wrong.', 'error');
                    return;
                }

                // Duplicate guard
                if (distAddedIds.includes(data.id)) {
                    Swal.fire('Duplicate', `${data.display_name} is already in the recipient list.`, 'info');
                    return;
                }

                distAddedIds.push(data.id);
                distRecipientCount++;

                // Cache display data so distGoBackToRegistry can restore cards for NEW stakeholders
                distRecipientsCache[data.id] = { displayName: data.display_name, subLabel: data.sub_label };

                // Build card
                const newBadge = data.is_new
                    ? `<span class="text-[8px] font-black bg-emerald-400/20 text-emerald-400 px-2 py-0.5 rounded-full uppercase tracking-widest ml-1">NEW</span>`
                    : '';

                const card = document.createElement('div');
                card.className = 'bg-white/5 border border-white/10 p-4 rounded-2xl flex justify-between items-center';
                card.dataset.stakeholderId = data.id;
                card.innerHTML = `
                    <div class="overflow-hidden flex-1">
                        <div class="flex items-center gap-1">
                            <p class="text-white font-bold text-xs truncate">${data.display_name}</p>
                            ${newBadge}
                        </div>
                        <p class="text-slate-900 text-[9px] uppercase font-black tracking-widest truncate mt-0.5">${data.sub_label}</p>
                    </div>
                    <button onclick="distRemoveRecipient(this, ${data.id})" class="text-slate-600 hover:text-red-400 transition-colors ml-3 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>`;

                const activeList = document.getElementById('distActiveList');
                if (activeList) activeList.appendChild(card);

                document.getElementById('distEmptyState')?.classList.add('hidden');
                document.getElementById('distListFooter')?.classList.remove('hidden');
                distUpdateCount();

                // Clear fields after adding
                if (document.getElementById('distPersonnelName')) document.getElementById('distPersonnelName').value = '';
                if (document.getElementById('distPersonnelPosition')) document.getElementById('distPersonnelPosition').value = '';

            } catch (err) {
                console.error(err);
                Swal.fire('Error', 'Network error. Please try again.', 'error');
            } finally {
                if (addBtn) { addBtn.disabled = false; addBtn.innerText = 'Add Recipient'; }
            }
        }





        // â”€â”€â”€ External Office dropdown (shows all on focus, filters on type) â”€â”€â”€â”€


        // â”€â”€â”€ Personnel dropdown (scoped to selected parent, shows all on focus) â”€


        // â”€â”€â”€ Close dropdowns when clicking outside â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        document.addEventListener('click', function(e) {
            const extWrap  = document.getElementById('distExternalInput')?.closest('.relative');
            const persWrap = document.getElementById('distPersonnelName')?.closest('.relative');
            if (extWrap  && !extWrap.contains(e.target))  document.getElementById('distExternalDropdown')?.classList.add('hidden');
            if (persWrap && !persWrap.contains(e.target)) document.getElementById('distPersonnelDropdown')?.classList.add('hidden');
        });
        function calcBulkCost() {
            const cost = parseFloat(document.getElementById('bCost').value) || 0;
            const qty = parseFloat(document.getElementById('bQty1').value) || 0;
            const bCost2 = document.getElementById('bCost2');
            if (bCost2) bCost2.value = (cost * qty).toFixed(2);
        }

        function syncBulkCustodian() {
            const first = (document.getElementById('bCustodianFirst')?.value || '').toLowerCase();
            const last  = (document.getElementById('bCustodianLast')?.value  || '').toLowerCase();
            const matches = allCustodiansList.filter(c => {
                const fMatch = first ? c.first_name.toLowerCase().includes(first) : true;
                const lMatch = last  ? c.last_name.toLowerCase().includes(last)   : true;
                return fMatch && lMatch;
            });

            if (matches.length === 1 && (first || last)) {
                const m = matches[0];
                const fInp = document.getElementById('bCustodianFirst');
                const mInp = document.getElementById('bCustodianMiddle');
                const lInp = document.getElementById('bCustodianLast');
                const pInp = document.getElementById('bCustodianPos');

                if (fInp) fInp.value = m.first_name;
                if (mInp) mInp.value = m.middle_name || '';
                if (lInp) lInp.value = m.last_name;
                if (pInp) pInp.value = m.position || '';
            }
        }
        
        function checkBulkPropertyNumber() {
            const propInput = document.getElementById('bPropertyNo');
            const qtyInput = document.getElementById('bQty1');
            if (!propInput || !qtyInput) return;
            if (propInput.value.trim() !== '') {
                qtyInput.value = 1; qtyInput.readOnly = true;
                qtyInput.classList.add('bg-slate-50', 'dark:bg-white/5', 'cursor-not-allowed');
            } else {
                qtyInput.readOnly = false;
                qtyInput.classList.remove('bg-slate-50', 'dark:bg-white/5', 'cursor-not-allowed');
            }
            calcBulkCost();
        }


        function submitRegistration() {
            if (allRowsData.length === 0) {
                Swal.fire({ icon: 'warning', title: 'No Data', text: 'Please add at least one row.', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]' } });
                return;
            }
            const acqSourceInput = document.getElementById('acqSourceInput').value.trim();
            if (!acqSourceInput) {
                Swal.fire({ icon: 'warning', title: 'Missing Source', text: 'Please specify the Source of Acquisition.', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]' } });
                return;
            }
            let isValid = true;
            allRowsData.forEach(row => {
                const required = ['classification', 'category', 'item', 'uom', 'cost', 'qty', 'useful-life', 'acceptance-date', 'school-type', 'school-name', 'occupancy', 'location', 'acquisition-date'];
                required.forEach(field => { if (!row[field]) isValid = false; });
            });
            if (!isValid) {
                Swal.fire({ icon: 'error', title: 'Incomplete Fields', text: 'Please fill in all required fields across all pages.', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]' } });
                return;
            }
            Swal.fire({
                title: 'Confirm Registration', text: `Register ${allRowsData.length} items?`, icon: 'question', showCancelButton: true,
                confirmButtonColor: '#c00000', cancelButtonColor: '#94a3b8', confirmButtonText: 'Yes, Register',
                customClass: { popup: 'rounded-[2rem]' }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Registering...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                    fetch('/inventory-setup/batch', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                        body: JSON.stringify({ source_of_acquisition: acqSourceInput, rows: allRowsData })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({ icon: 'success', title: 'Success!', text: data.message, confirmButtonColor: '#10b981', customClass: { popup: 'rounded-[2rem]' } })
                            .then(() => { window.location.href = '/inventory-setup'; });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Failed', text: data.message, confirmButtonColor: '#c00000' });
                        }
                    }).catch(err => { console.error(err); Swal.fire({ icon: 'error', title: 'Error', text: 'A network error occurred.' }); });
                }
            });
        }
