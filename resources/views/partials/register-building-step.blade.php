<div id="stepAddBuilding" class="step-content">
    <div class="bg-white rounded-[2rem] border border-slate-100 shadow-lg overflow-hidden animate-fade mt-6">
        <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 bg-[#c00000] rounded-xl flex items-center justify-center text-white text-xs font-black">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <div>
                    <h3 class="font-black text-slate-800 uppercase tracking-tight text-xs">Building Records</h3>
                    <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">Fill in building details per row</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="openBldgBulkModal()" class="flex items-center gap-2 px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-wider hover:bg-slate-100 transition-all active:scale-95">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                    Bulk Add
                </button>
                <button onclick="addBldgRow()" class="flex items-center gap-2 px-4 py-2.5 bg-[#c00000] text-white rounded-xl text-[10px] font-black uppercase tracking-wider hover:bg-red-700 transition-all shadow-sm active:scale-95">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Add Row
                </button>
            </div>
        </div>

        <div class="xls-scroll-wrap">
            <table class="w-full border-collapse" style="min-width:2400px;">
                <thead><tr>
                    <th class="xls-th w-10 text-center sticky left-0" style="z-index:6">#</th>
                    <th class="xls-th" style="min-width:90px">Region</th>
                    <th class="xls-th" style="min-width:190px">Division</th>
                    <th class="xls-th" style="min-width:140px">Office/School Type</th>
                    <th class="xls-th" style="min-width:100px">School ID</th>
                    <th class="xls-th" style="min-width:200px">Office/School Name</th>
                    <th class="xls-th" style="min-width:180px">Address</th>
                    <th class="xls-th" style="min-width:70px">Storeys</th>
                    <th class="xls-th" style="min-width:90px">Classrooms</th>
                    <th class="xls-th" style="min-width:140px">Article</th>
                    <th class="xls-th" style="min-width:170px">Description</th>
                    <th class="xls-th" style="min-width:130px">Classification</th>
                    <th class="xls-th" style="min-width:130px">Occupancy</th>
                    <th class="xls-th" style="min-width:150px">Location</th>
                    <th class="xls-th" style="min-width:120px">Date Constructed</th>
                    <th class="xls-th" style="min-width:120px">Acquisition Date</th>
                    <th class="xls-th" style="min-width:130px">Property No.</th>
                    <th class="xls-th text-right" style="min-width:120px">Acq. Cost (₱)</th>
                    <th class="xls-th text-right" style="min-width:120px">Appraised Value</th>
                    <th class="xls-th" style="min-width:120px">Appraisal Date</th>
                    <th class="xls-th" style="min-width:140px">Remarks</th>
                    <th class="xls-th w-10 text-center">Del</th>
                </tr></thead>
                <tbody id="bldgBody"></tbody>
            </table>
            <div id="bldgEmpty" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="inline-flex flex-col items-center gap-3 opacity-30">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21"/></svg>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.25em]">No rows — click Add Row to begin</p>
                </div>
            </div>
        </div>

        <div class="px-5 py-3 border-t border-slate-100 flex items-center justify-between bg-slate-50/50">
            <p id="bldgRowCountLabel" class="text-[9px] font-black text-slate-400 uppercase tracking-widest">0 Rows</p>
            <button onclick="confirmBldgSubmit()" class="px-6 py-2.5 bg-[#c00000] text-white rounded-xl font-black text-[10px] uppercase tracking-wider hover:bg-red-700 transition-all shadow-sm active:scale-95 flex items-center gap-2">
                Register Buildings ⚡
            </button>
        </div>
    </div>
</div>

{{-- BULK ADD MODAL FOR BUILDINGS --}}
<div id="bldgBulkModal" class="hidden fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4 opacity-0 transition-all duration-300">
    <div class="transform scale-95 transition-all duration-300 bg-white rounded-[2.5rem] shadow-2xl w-full max-w-2xl max-h-[85vh] overflow-y-auto custom-scroll">
        <div class="p-8">
            <div class="flex justify-between items-center mb-8">
                <h3 class="text-xl font-black text-slate-800 uppercase italic">Bulk Add Buildings</h3>
                <button onclick="closeBldgBulkModal()" class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-slate-400 hover:text-red-500 transition-all">✕</button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="text-[9px] font-black text-[#c00000] uppercase tracking-widest mb-1 block">Number of Rows</label>
                    <input type="number" id="bldgBulkCount" value="5" min="1" max="50" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm outline-none focus:ring-2 focus:ring-red-100">
                </div>
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Pre-fill shared fields (optional)</p>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Office/School Type</label><input type="text" id="bkType" class="w-full p-3 bg-slate-50 border border-slate-100 rounded-xl font-bold text-xs outline-none" placeholder="e.g. Elementary School"></div>
                    <div><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">School ID</label><input type="text" id="bkSchoolId" class="w-full p-3 bg-slate-50 border border-slate-100 rounded-xl font-bold text-xs outline-none" placeholder="School ID"></div>
                    <div><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Office/School Name</label><input type="text" id="bkName" class="w-full p-3 bg-slate-50 border border-slate-100 rounded-xl font-bold text-xs outline-none" placeholder="School name"></div>
                    <div><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Address</label><input type="text" id="bkAddr" class="w-full p-3 bg-slate-50 border border-slate-100 rounded-xl font-bold text-xs outline-none" placeholder="Address"></div>
                    <div><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Storeys</label><input type="number" id="bkStoreys" class="w-full p-3 bg-slate-50 border border-slate-100 rounded-xl font-bold text-xs outline-none" placeholder="0" min="0"></div>
                    <div><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Classrooms</label><input type="number" id="bkClassrooms" class="w-full p-3 bg-slate-50 border border-slate-100 rounded-xl font-bold text-xs outline-none" placeholder="0" min="0"></div>
                    <div><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Article</label><input type="text" id="bkArticle" class="w-full p-3 bg-slate-50 border border-slate-100 rounded-xl font-bold text-xs outline-none" placeholder="Article"></div>
                    <div><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Description</label><input type="text" id="bkDesc" class="w-full p-3 bg-slate-50 border border-slate-100 rounded-xl font-bold text-xs outline-none" placeholder="Description"></div>
                    <div><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Classification</label><input type="text" id="bkClass" class="w-full p-3 bg-slate-50 border border-slate-100 rounded-xl font-bold text-xs outline-none" placeholder="Classification"></div>
                    <div><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Occupancy</label><input type="text" id="bkOcc" class="w-full p-3 bg-slate-50 border border-slate-100 rounded-xl font-bold text-xs outline-none" placeholder="Nature of Occupancy"></div>
                    <div><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Location</label><input type="text" id="bkLoc" class="w-full p-3 bg-slate-50 border border-slate-100 rounded-xl font-bold text-xs outline-none" placeholder="Location"></div>
                    <div><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Date Constructed</label><input type="date" id="bkDateConst" class="w-full p-3 bg-slate-50 border border-slate-100 rounded-xl font-bold text-xs outline-none"></div>
                    <div><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Acquisition Date</label><input type="date" id="bkAcqDate" class="w-full p-3 bg-slate-50 border border-slate-100 rounded-xl font-bold text-xs outline-none"></div>
                    <div><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Property No.</label><input type="text" id="bkPropNo" class="w-full p-3 bg-slate-50 border border-slate-100 rounded-xl font-bold text-xs outline-none" placeholder="Property No."></div>
                    <div><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Acq. Cost (₱)</label><input type="number" id="bkCost" class="w-full p-3 bg-slate-50 border border-slate-100 rounded-xl font-bold text-xs outline-none" placeholder="0.00" min="0" step="0.01"></div>
                    <div><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Appraised Value</label><input type="number" id="bkAppVal" class="w-full p-3 bg-slate-50 border border-slate-100 rounded-xl font-bold text-xs outline-none" placeholder="0.00" min="0" step="0.01"></div>
                    <div><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Appraisal Date</label><input type="date" id="bkAppDate" class="w-full p-3 bg-slate-50 border border-slate-100 rounded-xl font-bold text-xs outline-none"></div>
                    <div><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Remarks</label><input type="text" id="bkRemarks" class="w-full p-3 bg-slate-50 border border-slate-100 rounded-xl font-bold text-xs outline-none" placeholder="Remarks"></div>
                </div>
            </div>
            <div class="flex justify-end mt-8 gap-3">
                <button onclick="closeBldgBulkModal()" class="px-6 py-3 bg-slate-100 text-slate-600 rounded-xl font-black text-xs uppercase hover:bg-slate-200 transition-all">Cancel</button>
                <button onclick="doBldgBulkAdd()" class="px-8 py-3 bg-[#c00000] text-white rounded-xl font-black text-xs uppercase hover:bg-red-700 transition-all shadow-sm">Generate Rows</button>
            </div>
        </div>
    </div>
</div>

<script>
    let _bldgRn = 0, _bldgRc = 0;

    function addBldgRow(prefill = {}) {
        _bldgRn++; _bldgRc++;
        document.getElementById('bldgEmpty').classList.add('hidden');
        const tbody = document.getElementById('bldgBody');
        const tr = document.createElement('tr');
        tr.id = `bldg-brow-${_bldgRn}`;
        tr.className = 'xls-row group border-b border-slate-100';
        tr.dataset.rid = _bldgRn;
        const today = new Date().toISOString().split('T')[0];
        const p = prefill;
        tr.innerHTML = `
            <td class="xls-td text-center sticky left-0 w-10 xls-sticky-col"><span class="row-num text-[10px] font-black text-slate-300">${_bldgRc}</span></td>
            <td class="xls-td"><span class="xls-const">Region IX</span></td>
            <td class="xls-td"><span class="xls-const">Division of Zamboanga City</span></td>
            <td class="xls-td"><input type="text" data-bf="office_type" class="xls-input" placeholder="School Type" value="${p.office_type||''}"></td>
            <td class="xls-td"><input type="text" data-bf="school_identifier" class="xls-input" placeholder="School ID" value="${p.school_identifier||''}"></td>
            <td class="xls-td"><input type="text" data-bf="office_name" class="xls-input" placeholder="Office/School Name *" value="${p.office_name||''}"></td>
            <td class="xls-td"><input type="text" data-bf="address" class="xls-input" placeholder="Address" value="${p.address||''}"></td>
            <td class="xls-td"><input type="number" data-bf="storeys" class="xls-input text-center" placeholder="0" min="0" value="${p.storeys||''}"></td>
            <td class="xls-td"><input type="number" data-bf="classrooms" class="xls-input text-center" placeholder="0" min="0" value="${p.classrooms||''}"></td>
            <td class="xls-td"><input type="text" data-bf="article" class="xls-input" placeholder="Article" value="${p.article||''}"></td>
            <td class="xls-td"><input type="text" data-bf="description" class="xls-input" placeholder="Description" value="${p.description||''}"></td>
            <td class="xls-td"><input type="text" data-bf="classification" class="xls-input" placeholder="Classification" value="${p.classification||''}"></td>
            <td class="xls-td"><input type="text" data-bf="occupancy_nature" class="xls-input" placeholder="Occupancy" value="${p.occupancy_nature||''}"></td>
            <td class="xls-td"><input type="text" data-bf="location" class="xls-input" placeholder="Location" value="${p.location||''}"></td>
            <td class="xls-td"><input type="date" data-bf="date_constructed" class="xls-input" value="${p.date_constructed||today}"></td>
            <td class="xls-td"><input type="date" data-bf="acquisition_date" class="xls-input" value="${p.acquisition_date||today}"></td>
            <td class="xls-td"><input type="text" data-bf="property_number" class="xls-input" placeholder="Property No." value="${p.property_number||''}"></td>
            <td class="xls-td"><input type="number" data-bf="acquisition_cost" class="xls-input text-right" placeholder="0.00" min="0" step="0.01" value="${p.acquisition_cost||''}"></td>
            <td class="xls-td"><input type="number" data-bf="appraised_value" class="xls-input text-right" placeholder="0.00" min="0" step="0.01" value="${p.appraised_value||''}"></td>
            <td class="xls-td"><input type="date" data-bf="appraisal_date" class="xls-input" value="${p.appraisal_date||''}"></td>
            <td class="xls-td"><input type="text" data-bf="remarks" class="xls-input" placeholder="Remarks" value="${p.remarks||''}"></td>
            <td class="xls-td text-center w-10"><button onclick="delBldgRow(${_bldgRn})" class="p-1.5 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button></td>`;
        tbody.appendChild(tr);
        updBldgCount();
    }

    function delBldgRow(id) {
        const r = document.getElementById(`bldg-brow-${id}`);
        if (r) r.remove();
        _bldgRc = document.querySelectorAll('#bldgBody tr').length;
        document.querySelectorAll('#bldgBody tr').forEach((r, i) => { const s = r.querySelector('.row-num'); if (s) s.textContent = i + 1; });
        if (_bldgRc === 0) document.getElementById('bldgEmpty').classList.remove('hidden');
        updBldgCount();
    }

    function updBldgCount() {
        document.getElementById('bldgRowCountLabel').textContent = _bldgRc + ' Row' + (_bldgRc !== 1 ? 's' : '');
    }

    function openBldgBulkModal() {
        const m = document.getElementById('bldgBulkModal');
        m.classList.remove('hidden');
        setTimeout(() => { m.classList.remove('opacity-0'); m.querySelector('.transform').classList.remove('scale-95'); }, 10);
    }
    
    function closeBldgBulkModal() {
        const m = document.getElementById('bldgBulkModal');
        m.classList.add('opacity-0');
        m.querySelector('.transform').classList.add('scale-95');
        setTimeout(() => m.classList.add('hidden'), 300);
    }
    
    function doBldgBulkAdd() {
        const n = parseInt(document.getElementById('bldgBulkCount').value) || 1;
        const pf = {
            office_type: document.getElementById('bkType').value,
            school_identifier: document.getElementById('bkSchoolId').value,
            office_name: document.getElementById('bkName').value,
            address: document.getElementById('bkAddr').value,
            storeys: document.getElementById('bkStoreys').value,
            classrooms: document.getElementById('bkClassrooms').value,
            article: document.getElementById('bkArticle').value,
            description: document.getElementById('bkDesc').value,
            classification: document.getElementById('bkClass').value,
            occupancy_nature: document.getElementById('bkOcc').value,
            location: document.getElementById('bkLoc').value,
            date_constructed: document.getElementById('bkDateConst').value,
            acquisition_date: document.getElementById('bkAcqDate').value,
            property_number: document.getElementById('bkPropNo').value,
            acquisition_cost: document.getElementById('bkCost').value,
            appraised_value: document.getElementById('bkAppVal').value,
            appraisal_date: document.getElementById('bkAppDate').value,
            remarks: document.getElementById('bkRemarks').value,
        };
        for (let i = 0; i < n; i++) addBldgRow(pf);
        closeBldgBulkModal();
    }

    function confirmBldgSubmit() {
        const rows = document.querySelectorAll('#bldgBody tr');
        if (rows.length === 0) {
            Swal.fire({ title: 'No Buildings', text: 'Add at least one building row before submitting.', icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
            return;
        }
        
        let missing = 0;
        rows.forEach((r, i) => { if (!(r.querySelector('[data-bf="office_name"]')?.value?.trim())) missing++; });
        if (missing > 0) {
            Swal.fire({ title: 'Missing Data', text: `${missing} row(s) are missing Office/School Name. Please fill them in.`, icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
            return;
        }

        Swal.fire({
            title: 'Register Buildings?',
            html: `<strong>${rows.length}</strong> building(s) will be added to the masterlist.`,
            icon: 'question', showCancelButton: true,
            confirmButtonColor: '#1e293b', cancelButtonColor: '#94a3b8',
            confirmButtonText: '⚡ Register Now', cancelButtonText: 'Cancel',
            customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6', cancelButton: 'rounded-xl font-bold px-6' },
            showLoaderOnConfirm: true,
            preConfirm: () => {
                const payloadRows = [];
                const fields = ['office_type','school_identifier','office_name','address','storeys','classrooms','article','description','classification','occupancy_nature','location','date_constructed','acquisition_date','property_number','acquisition_cost','appraised_value','appraisal_date','remarks'];
                rows.forEach((row, i) => {
                    let rowData = { region: 'REGION IX', division: 'Division of Zamboanga City' };
                    fields.forEach(f => {
                        rowData[f] = row.querySelector(`[data-bf="${f}"]`)?.value || '';
                    });
                    payloadRows.push(rowData);
                });

                return fetch("{{ route('register.building.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ rows: payloadRows })
                })
                .then(response => {
                    if (!response.ok) throw new Error(response.statusText);
                    return response.json();
                })
                .catch(error => {
                    Swal.showValidationMessage(`Request failed: ${error}`);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                if (result.value.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: result.value.message,
                        icon: 'success',
                        confirmButtonColor: '#10b981',
                        customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' }
                    }).then(() => {
                        window.location.href = '/inventory-setup';
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: result.value.message || 'Registration failed.',
                        icon: 'error',
                        confirmButtonColor: '#c00000',
                        customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' }
                    });
                }
            }
        });
    }
</script>
