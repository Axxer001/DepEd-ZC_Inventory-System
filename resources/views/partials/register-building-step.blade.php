{{-- ═══════ STEP: ADD BUILDING ═══════ --}}
<div id="stepAddBuilding" class="step-content">

    {{-- Datalists --}}
    <datalist id="dl-bldg-region"><option value="REGION IX"></datalist>
    <datalist id="dl-bldg-division"><option value="Division of Zamboanga City"></datalist>
    <datalist id="dl-bldg-type">
        <option value="Elementary School">
        <option value="High School">
        <option value="Integrated School">
        <option value="Division Office">
        <option value="Government Building">
    </datalist>
    <datalist id="dl-bldg-class">
        <option value="Semi-Expendable">
        <option value="Non-Expendable">
        <option value="Expendable">
        <option value="Land Improvement">
    </datalist>
    <datalist id="dl-bldg-occupancy">
        <option value="Owned">
        <option value="Leased">
        <option value="Borrowed">
    </datalist>
    <datalist id="dl-bldg-school-id">
        @foreach($allSchools as $s)<option value="{{ $s->school_id }}">@endforeach
    </datalist>
    <datalist id="dl-bldg-school-name">
        @foreach($allSchools as $s)<option value="{{ $s->name }}">@endforeach
    </datalist>

    {{-- Toolbar --}}
    <div id="bldgToolbar" class="bg-white rounded-[2rem] border border-slate-100 shadow-lg overflow-hidden mb-4">
        <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between gap-3 flex-wrap">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 bg-[#c00000] rounded-xl flex items-center justify-center text-white text-xs font-black shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-black text-slate-800 uppercase tracking-tight">Add Building</p>
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Register school buildings and infrastructure</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="bldgBulkAdd()" class="flex items-center gap-2 px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-wider hover:bg-slate-100 transition-all active:scale-95">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                    Bulk Add
                </button>
                <button onclick="bldgBulkDelete()" class="flex items-center gap-2 px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-wider hover:bg-red-50 hover:text-red-600 hover:border-red-100 transition-all active:scale-95">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                    Bulk Delete
                </button>
                <button onclick="addBldgRow()" class="flex items-center gap-2 px-4 py-2.5 bg-[#c00000] text-white rounded-xl text-[10px] font-black uppercase tracking-wider hover:bg-red-700 transition-all shadow-sm active:scale-95">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Add Row
                </button>
            </div>
        </div>

        {{-- XLS Table --}}
        <div class="xls-scroll-wrap">
            <table class="w-full border-collapse" style="min-width:2600px;">
                <thead>
                    <tr>
                        <th class="xls-th w-10 text-center sticky left-0 z-20">#</th>
                        <th class="xls-th" style="min-width:90px">Region</th>
                        <th class="xls-th" style="min-width:180px">Division</th>
                        <th class="xls-th" style="min-width:150px">Office/School Type</th>
                        <th class="xls-th" style="min-width:110px">School ID</th>
                        <th class="xls-th" style="min-width:200px">Office/School Name *</th>
                        <th class="xls-th" style="min-width:180px">Address</th>
                        <th class="xls-th text-right" style="min-width:75px">Storeys</th>
                        <th class="xls-th text-right" style="min-width:90px">Classrooms</th>
                        <th class="xls-th" style="min-width:140px">Article</th>
                        <th class="xls-th" style="min-width:160px">Description</th>
                        <th class="xls-th" style="min-width:130px">Classification</th>
                        <th class="xls-th" style="min-width:120px">Occupancy</th>
                        <th class="xls-th" style="min-width:150px">Location</th>
                        <th class="xls-th" style="min-width:130px">Date Constructed</th>
                        <th class="xls-th" style="min-width:130px">Acquisition Date</th>
                        <th class="xls-th" style="min-width:140px">Property No.</th>
                        <th class="xls-th text-right" style="min-width:130px">Acquisition Cost (₱)</th>
                        <th class="xls-th text-right" style="min-width:115px">Useful Life (yrs)</th>
                        <th class="xls-th text-right" style="min-width:130px">Appraised Value (₱)</th>
                        <th class="xls-th" style="min-width:130px">Appraisal Date</th>
                        <th class="xls-th" style="min-width:160px">Remarks</th>
                        <th class="xls-th w-10 text-center">Del</th>
                    </tr>
                </thead>
                <tbody id="bldgEntryBody"></tbody>
            </table>

            <div id="bldgEntryEmpty" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="inline-flex flex-col items-center gap-3 opacity-30">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21"/></svg>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.25em]">No rows — click Add Row to begin</p>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-5 py-3 border-t border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center gap-6">
                <p id="bldgEntryRowCount" class="text-[9px] font-black text-slate-400 uppercase tracking-widest">0 Rows</p>
                <div id="bldgEntryPagination" class="hidden flex items-center gap-2 border-l border-slate-200 pl-6">
                    <button onclick="bldgEntryPrev()" id="bldgPrevBtn" class="pg-btn text-slate-500">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg> Prev
                    </button>
                    <div class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 rounded-lg">
                        <span id="bldgEntryCurPage" class="text-[10px] font-black text-slate-800">1</span>
                        <span class="text-[10px] font-bold text-slate-400">/</span>
                        <span id="bldgEntryTotalPages" class="text-[10px] font-black text-slate-400">1</span>
                    </div>
                    <button onclick="bldgEntryNext()" id="bldgNextBtn" class="pg-btn text-slate-500">
                        Next <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
            <button onclick="submitBuildingRegistration()" class="px-6 py-2.5 bg-[#c00000] text-white rounded-xl font-black text-[10px] uppercase tracking-wider hover:bg-red-700 transition-all shadow-sm active:scale-95">
                Register Buildings
            </button>
        </div>
    </div>
</div>

<script>
// ── Add Building State ──
let bldgEntryRows = [];
let bldgEntryPage = 1;
const BLDG_RPP = 50;
let _bldgRowNum = 0;

function addBldgRow(defaults = {}) {
    _bldgRowNum++;
    const id = _bldgRowNum;
    bldgEntryRows.push({
        _id: id,
        region: defaults.region ?? 'REGION IX',
        division: defaults.division ?? 'Division of Zamboanga City',
        office_type: defaults.office_type ?? '',
        school_identifier: defaults.school_identifier ?? '',
        office_name: defaults.office_name ?? '',
        address: defaults.address ?? '',
        storeys: defaults.storeys ?? '',
        classrooms: defaults.classrooms ?? '',
        article: defaults.article ?? '',
        description: defaults.description ?? '',
        classification: defaults.classification ?? '',
        occupancy_nature: defaults.occupancy_nature ?? '',
        location: defaults.location ?? '',
        date_constructed: defaults.date_constructed ?? '',
        acquisition_date: defaults.acquisition_date ?? '',
        property_number: defaults.property_number ?? '',
        acquisition_cost: defaults.acquisition_cost ?? '',
        estimated_useful_life: defaults.estimated_useful_life ?? '25',
        appraised_value: defaults.appraised_value ?? '',
        appraisal_date: defaults.appraisal_date ?? '',
        remarks: defaults.remarks ?? '',
    });
    bldgEntryPage = Math.ceil(bldgEntryRows.length / BLDG_RPP);
    renderBldgEntryTable();
}

function deleteBldgRow(id) {
    bldgEntryRows = bldgEntryRows.filter(r => r._id !== id);
    const totalPages = Math.max(1, Math.ceil(bldgEntryRows.length / BLDG_RPP));
    if (bldgEntryPage > totalPages) bldgEntryPage = totalPages;
    renderBldgEntryTable();
}

function syncBldgRow(id, col, val) {
    const row = bldgEntryRows.find(r => r._id === id);
    if (row) row[col] = val;
}

function renderBldgEntryTable() {
    const tbody = document.getElementById('bldgEntryBody');
    const empty = document.getElementById('bldgEntryEmpty');
    tbody.innerHTML = '';

    if (bldgEntryRows.length === 0) {
        empty.classList.remove('hidden');
        document.getElementById('bldgEntryRowCount').textContent = '0 Rows';
        document.getElementById('bldgEntryPagination').classList.add('hidden');
        return;
    }
    empty.classList.add('hidden');

    const start = (bldgEntryPage - 1) * BLDG_RPP;
    const page  = bldgEntryRows.slice(start, start + BLDG_RPP);

    page.forEach((row, idx) => {
        const displayNum = start + idx + 1;
        const tr = document.createElement('tr');
        tr.className = 'xls-row group border-b border-slate-800';
        tr.id = `bldg-row-${row._id}`;

        const inp = (col, list = '', ph = '') =>
            `<td class="xls-td"><input class="xls-input" data-bldg-col="${col}" data-bldg-id="${row._id}"
                value="${escBldg(row[col])}" placeholder="${ph}"
                ${list ? `list="${list}"` : ''} oninput="syncBldgRow(${row._id},'${col}',this.value)"></td>`;

        const numInp = (col, ph = '0') =>
            `<td class="xls-td"><input type="number" class="xls-input text-right" data-bldg-col="${col}" data-bldg-id="${row._id}"
                value="${escBldg(row[col])}" placeholder="${ph}"
                oninput="syncBldgRow(${row._id},'${col}',this.value)"></td>`;

        const dateInp = (col) =>
            `<td class="xls-td"><input type="date" class="xls-input" data-bldg-col="${col}" data-bldg-id="${row._id}"
                value="${escBldg(row[col])}"
                oninput="syncBldgRow(${row._id},'${col}',this.value)"></td>`;

        tr.innerHTML = `
            <td class="xls-td text-center sticky left-0 w-10 bg-[#0f172a] z-10"><span class="text-[10px] font-black text-slate-500">${displayNum}</span></td>
            ${inp('region','dl-bldg-region','REGION IX')}
            ${inp('division','dl-bldg-division','Division of ZC')}
            ${inp('office_type','dl-bldg-type','School Type')}
            ${inp('school_identifier','dl-bldg-school-id','School ID')}
            ${inp('office_name','dl-bldg-school-name','Office/School Name *')}
            ${inp('address','','Address')}
            ${numInp('storeys','0')}
            ${numInp('classrooms','0')}
            ${inp('article','','Article')}
            ${inp('description','','Description')}
            ${inp('classification','dl-bldg-class','Classification')}
            ${inp('occupancy_nature','dl-bldg-occupancy','Occupancy')}
            ${inp('location','dl-bldg-school-name','Location')}
            ${dateInp('date_constructed')}
            ${dateInp('acquisition_date')}
            ${inp('property_number','','Property No.')}
            <td class="xls-td"><input type="number" step="0.01" class="xls-input text-right" data-bldg-col="acquisition_cost" data-bldg-id="${row._id}"
                value="${escBldg(row['acquisition_cost'])}" placeholder="0.00"
                oninput="syncBldgRow(${row._id},'acquisition_cost',this.value)"></td>
            ${numInp('estimated_useful_life','25')}
            <td class="xls-td"><input type="number" step="0.01" class="xls-input text-right" data-bldg-col="appraised_value" data-bldg-id="${row._id}"
                value="${escBldg(row['appraised_value'])}" placeholder="0.00"
                oninput="syncBldgRow(${row._id},'appraised_value',this.value)"></td>
            ${dateInp('appraisal_date')}
            ${inp('remarks','','Remarks')}
            <td class="xls-td text-center">
                <button onclick="deleteBldgRow(${row._id})" class="w-7 h-7 flex items-center justify-center text-slate-500 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all mx-auto">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </td>`;
        tbody.appendChild(tr);
    });

    // Pagination
    const totalPages = Math.max(1, Math.ceil(bldgEntryRows.length / BLDG_RPP));
    document.getElementById('bldgEntryRowCount').textContent = `${bldgEntryRows.length} Rows`;
    document.getElementById('bldgEntryCurPage').textContent  = bldgEntryPage;
    document.getElementById('bldgEntryTotalPages').textContent = totalPages;
    document.getElementById('bldgPrevBtn').disabled = bldgEntryPage === 1;
    document.getElementById('bldgNextBtn').disabled = bldgEntryPage === totalPages;
    const pag = document.getElementById('bldgEntryPagination');
    bldgEntryRows.length > BLDG_RPP ? pag.classList.remove('hidden') : pag.classList.add('hidden');
}

function escBldg(v) { return String(v ?? '').replace(/"/g, '&quot;'); }
function bldgEntryPrev() { if (bldgEntryPage > 1) { bldgEntryPage--; renderBldgEntryTable(); } }
function bldgEntryNext() { const t = Math.ceil(bldgEntryRows.length / BLDG_RPP); if (bldgEntryPage < t) { bldgEntryPage++; renderBldgEntryTable(); } }

// Bulk Add modal
function bldgBulkAdd() {
    Swal.fire({
        title: 'Bulk Add Rows',
        html: `<label class="text-xs font-bold text-slate-600 block mb-2">Number of rows to add:</label>
               <input id="bldgBulkCount" type="number" min="1" max="500" value="10"
                 class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-red-100 focus:border-red-500 outline-none">`,
        confirmButtonText: 'Add Rows',
        confirmButtonColor: '#c00000',
        showCancelButton: true,
        customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' },
        preConfirm: () => {
            const n = parseInt(document.getElementById('bldgBulkCount').value);
            if (!n || n < 1) { Swal.showValidationMessage('Enter a valid number'); return false; }
            return n;
        }
    }).then(r => { if (r.isConfirmed) { for (let i = 0; i < r.value; i++) addBldgRow(); } });
}

// Bulk delete
function bldgBulkDelete() {
    if (bldgEntryRows.length === 0) return;
    Swal.fire({
        title: 'Delete All Rows?',
        text: `This will remove all ${bldgEntryRows.length} building row(s).`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete All',
        confirmButtonColor: '#c00000',
        customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' },
    }).then(r => {
        if (r.isConfirmed) { bldgEntryRows = []; bldgEntryPage = 1; renderBldgEntryTable(); }
    });
}

// Submit
async function submitBuildingRegistration() {
    const validRows = bldgEntryRows.filter(r => r.office_name.trim() !== '');
    if (validRows.length === 0) {
        Swal.fire({ title: 'No Data', text: 'Add at least one row with an Office/School Name.', icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold' } });
        return;
    }

    const result = await Swal.fire({
        title: 'Register Buildings?',
        text: `Submit ${validRows.length} building record(s)?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Register',
        confirmButtonColor: '#c00000',
        customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' }
    });
    if (!result.isConfirmed) return;

    Swal.fire({ title: 'Registering...', allowOutsideClick: false, didOpen: () => Swal.showLoading(), customClass: { popup: 'rounded-[2rem]' } });

    try {
        const res = await fetch("{{ route('register.building.store') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ rows: validRows })
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire({ title: 'Success!', text: data.message, icon: 'success', confirmButtonColor: '#10b981', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold' } })
                .then(() => { bldgEntryRows = []; bldgEntryPage = 1; renderBldgEntryTable(); });
        } else {
            Swal.fire({ title: 'Error', text: data.message, icon: 'error', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold' } });
        }
    } catch(e) {
        Swal.fire({ title: 'Error', text: 'Registration failed. Please try again.', icon: 'error', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold' } });
    }
}

// Override the renderBldgTable hook called by nextStep
window.renderBldgTable = function() {
    renderBldgEntryTable();
};
</script>
