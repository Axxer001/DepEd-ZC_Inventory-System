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
                <button onclick="openBldgBulkAddModal()" class="flex items-center gap-2 px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-wider hover:bg-slate-100 transition-all active:scale-95">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                    Bulk Add
                </button>
                <button onclick="openBldgBulkDeleteModal()" class="flex items-center gap-2 px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-wider hover:bg-red-50 hover:text-red-600 hover:border-red-100 transition-all active:scale-95">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-1.123a2.25 2.25 0 00-2.25-2.25h-4.5a2.25 2.25 0 00-2.25 2.25v1.123m9.913 0a11.405 11.405 0 00-9.913 0"/></svg>
                    Bulk Delete
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
            <div class="flex items-center gap-6">
                <p id="bldgRowCountLabel" class="text-[9px] font-black text-slate-400 uppercase tracking-widest">0 Rows</p>
                <div id="bldgPaginationControls" class="flex items-center gap-2 border-l border-slate-200 dark:border-slate-800 pl-6">
                    <button onclick="prevBldgPage()" id="bldgPrevBtn" class="pg-btn text-slate-500">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
                        Prev
                    </button>
                    <div class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 dark:bg-[#0a101d] rounded-lg">
                        <span id="bldgCurrentPageDisplay" class="text-[10px] font-black text-slate-800 dark:text-white">1</span>
                        <span class="text-[10px] font-bold text-slate-400">/</span>
                        <span id="bldgTotalPagesDisplay" class="text-[10px] font-black text-slate-400">1</span>
                    </div>
                    <button onclick="nextBldgPage()" id="bldgNextBtn" class="pg-btn text-slate-500">
                        Next
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
            <button onclick="confirmBldgSubmit()" class="px-6 py-2.5 bg-[#c00000] text-white rounded-xl font-black text-[10px] uppercase tracking-wider hover:bg-red-700 transition-all shadow-sm active:scale-95">
                Register Buildings
            </button>
        </div>
    </div>
</div>

{{-- BULK ADD MODAL FOR BUILDINGS --}}
<div id="bldgBulkModal" class="fixed inset-0 z-50 flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeBldgBulkModal()"></div>
    <div class="bg-white dark:bg-[#141f33] border border-slate-200 dark:border-slate-800 rounded-[2rem] shadow-2xl w-[90vw] max-w-5xl max-h-[90vh] flex flex-col relative z-10 transform scale-95 transition-transform duration-300">
        
        {{-- Header --}}
        <div class="px-8 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-2xl font-black text-slate-800 dark:text-white uppercase tracking-tight italic">Bulk Add Buildings</h3>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Pre-fill data across multiple new building rows</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 bg-slate-50 dark:bg-[#0a101d] px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800">
                    <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Rows to add</label>
                    <input type="number" id="bldgBulkCount" value="1" min="1" max="100" class="w-16 bg-transparent text-center font-black text-slate-800 dark:text-white outline-none">
                </div>
                <button onclick="closeBldgBulkModal()" class="px-5 py-3 rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">Cancel</button>
                <button onclick="doBldgBulkAdd()" class="px-6 py-3 rounded-xl text-sm font-black text-white bg-[#c00000] hover:bg-red-700 shadow-lg shadow-red-500/30 transition-all">Confirm Bulk Add</button>
            </div>
        </div>

        {{-- Body --}}
        <div class="p-8 overflow-y-auto custom-scroll flex-1 space-y-10">
            
            {{-- Data Entry Section --}}
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-6 h-6 bg-amber-500/20 text-amber-500 rounded-lg flex items-center justify-center text-[10px] font-black shrink-0">1</div>
                    <h4 class="font-black text-slate-800 dark:text-slate-200 uppercase tracking-widest text-xs">Building Data Entry</h4>
                </div>
                <div class="grid grid-cols-2 gap-x-6 gap-y-5">
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Office/School Type</label><input type="text" id="bkType" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Combo-box: type/select"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">School ID</label><input type="text" id="bkSchoolId" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Combo-box: type/select" inputmode="numeric"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Office/School Name</label><input type="text" id="bkName" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Combo-box: type/select"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Address</label><input type="text" id="bkAddr" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Combo-box: type/select"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Storeys</label><input type="number" id="bkStoreys" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl text-right" placeholder="0" min="0"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Classrooms</label><input type="number" id="bkClassrooms" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl text-right" placeholder="0" min="0"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Article</label><input type="text" id="bkArticle" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Combo-box: type/select"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Description</label><input type="text" id="bkDesc" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Combo-box: type/select"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Classification</label><input type="text" id="bkClass" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Combo-box: type/select"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Occupancy</label><input type="text" id="bkOcc" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Combo-box: type/select"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Location</label><input type="text" id="bkLoc" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Combo-box: type/select"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Property No.</label><input type="text" id="bkPropNo" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Combo-box: type/select"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Acq. Cost (₱)</label><input type="number" id="bkCost" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl text-right" placeholder="0.00" min="0" step="0.01"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Appraised Value</label><input type="number" id="bkAppVal" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl text-right" placeholder="0.00" min="0" step="0.01"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Date Constructed</label><input type="date" id="bkDateConst" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Acquisition Date</label><input type="date" id="bkAcqDate" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Appraisal Date</label><input type="date" id="bkAppDate" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Remarks</label><input type="text" id="bkRemarks" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Combo-box: type/select"></div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- BULK DELETE MODAL FOR BUILDINGS --}}
<div id="bldgBulkDeleteModal" class="hidden fixed inset-0 z-[60] bg-black/40 flex items-center justify-center p-4 opacity-0 transition-all duration-300">
    <div class="transform scale-95 transition-all duration-300 bg-white rounded-[2.5rem] shadow-2xl w-full max-w-md overflow-hidden">
        <div class="p-8">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h3 class="text-xl font-black text-slate-800 uppercase italic">Bulk Delete</h3>
                    <p class="text-[9px] font-bold text-red-500 uppercase tracking-widest mt-1">Warning: Permanent Action</p>
                </div>
                <div class="flex p-1 bg-slate-100 rounded-xl">
                    <button onclick="setBldgDeleteMode('rows')" id="btnBldgDelRows" class="px-4 py-1.5 text-[9px] font-black uppercase tracking-widest rounded-lg bg-white shadow-sm text-slate-800 transition-all">Rows</button>
                    <button onclick="setBldgDeleteMode('pages')" id="btnBldgDelPages" class="px-4 py-1.5 text-[9px] font-black uppercase tracking-widest rounded-lg text-slate-400 transition-all">Pages</button>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-6 mb-8">
                <div>
                    <label id="lblBldgDelFrom" class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block ml-1">From Row</label>
                    <input type="number" id="bldgDeleteFrom" value="1" min="1" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl font-black text-sm outline-none text-center focus:ring-2 focus:ring-red-100">
                </div>
                <div>
                    <label id="lblBldgDelTo" class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block ml-1">To Row</label>
                    <input type="number" id="bldgDeleteTo" value="1" min="1" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl font-black text-sm outline-none text-center focus:ring-2 focus:ring-red-100">
                </div>
            </div>
            <div class="flex gap-3">
                <button onclick="closeBldgBulkDeleteModal()" class="flex-1 py-4 bg-slate-100 text-slate-600 rounded-2xl font-black text-xs uppercase hover:bg-slate-200 transition-all">Cancel</button>
                <button onclick="confirmBldgBulkDelete()" class="flex-[2] py-4 bg-red-600 text-white rounded-2xl font-black text-xs uppercase hover:bg-red-700 transition-all shadow-lg shadow-red-100">Delete Range</button>
            </div>
        </div>
    </div>
</div>

<script>
    let bldgRowsData = [];
    let _bldgRnCounter = 0;
    let bldgCurrentPage = 1;
    const bldgRowsPerPage = 50;

    function syncBldgState(rowId, col, value) {
        const row = bldgRowsData.find(r => r.id === rowId);
        if (row) {
            row[col] = value;
        }
    }

    function renderBldgTable() {
        const tbody = document.getElementById('bldgBody');
        if (!tbody) return;
        tbody.innerHTML = '';
        
        if (bldgRowsData.length === 0) {
            document.getElementById('bldgEmpty').classList.remove('hidden');
            document.getElementById('bldgRowCountLabel').textContent = '0 Rows';
            document.getElementById('bldgPaginationControls').classList.add('hidden');
            return;
        }
        document.getElementById('bldgEmpty').classList.add('hidden');

        const totalPages = Math.max(1, Math.ceil(bldgRowsData.length / bldgRowsPerPage));
        if (bldgCurrentPage > totalPages) bldgCurrentPage = totalPages;
        if (bldgCurrentPage < 1) bldgCurrentPage = 1;

        const curDisplay = document.getElementById('bldgCurrentPageDisplay');
        const totalDisplay = document.getElementById('bldgTotalPagesDisplay');
        const prevBtn = document.getElementById('bldgPrevBtn');
        const nextBtn = document.getElementById('bldgNextBtn');

        if (curDisplay) curDisplay.textContent = bldgCurrentPage;
        if (totalDisplay) totalDisplay.textContent = totalPages;
        if (prevBtn) prevBtn.disabled = (bldgCurrentPage === 1);
        if (nextBtn) nextBtn.disabled = (bldgCurrentPage === totalPages);
        
        if (bldgRowsData.length <= bldgRowsPerPage) {
            document.getElementById('bldgPaginationControls').classList.add('hidden');
        } else {
            document.getElementById('bldgPaginationControls').classList.remove('hidden');
        }

        const start = (bldgCurrentPage - 1) * bldgRowsPerPage;
        const end = start + bldgRowsPerPage;
        const pageData = bldgRowsData.slice(start, end);

        pageData.forEach((row, index) => {
            const displayNum = start + index + 1;
            addBldgRowDOM(row, displayNum);
        });

        document.getElementById('bldgRowCountLabel').textContent = bldgRowsData.length + ' Row' + (bldgRowsData.length !== 1 ? 's' : '');
        updateBldgNewLabels();
    }

    function addBldgRowDOM(data, displayNum) {
        const tbody = document.getElementById('bldgBody');
        const tr = document.createElement('tr');
        tr.id = `bldg-row-${data.id}`;
        tr.className = 'xls-row group border-b border-slate-100';
        const today = new Date().toISOString().split('T')[0];
        
        tr.innerHTML = `
            <td class="xls-td text-center sticky left-0 w-10 xls-sticky-col" style="background:inherit"><span class="row-num text-[10px] font-black text-slate-300">${displayNum}</span></td>
            <td class="xls-td"><span class="xls-const">Region IX</span></td>
            <td class="xls-td"><span class="xls-const">Division of Zamboanga City</span></td>
            <td class="xls-td relative"><input type="text" oninput="syncBldgState(${data.id}, 'office_type', this.value)" data-col="office_type" class="xls-input" placeholder="School Type" value="${data.office_type||''}"></td>
            <td class="xls-td relative"><input type="text" oninput="syncBldgState(${data.id}, 'school_identifier', this.value)" data-col="school_identifier" class="xls-input" placeholder="School ID" value="${data.school_identifier||''}"></td>
            <td class="xls-td relative"><input type="text" oninput="syncBldgState(${data.id}, 'office_name', this.value)" data-col="office_name" class="xls-input" placeholder="Office/School Name *" value="${data.office_name||''}"></td>
            <td class="xls-td relative"><input type="text" oninput="syncBldgState(${data.id}, 'address', this.value)" data-col="address" class="xls-input" placeholder="Address" value="${data.address||''}"></td>
            <td class="xls-td relative"><input type="number" oninput="syncBldgState(${data.id}, 'storeys', this.value)" data-col="storeys" class="xls-input text-center" placeholder="0" min="0" value="${data.storeys||''}"></td>
            <td class="xls-td relative"><input type="number" oninput="syncBldgState(${data.id}, 'classrooms', this.value)" data-col="classrooms" class="xls-input text-center" placeholder="0" min="0" value="${data.classrooms||''}"></td>
            <td class="xls-td relative"><input type="text" oninput="syncBldgState(${data.id}, 'article', this.value)" data-col="article" class="xls-input" placeholder="Article" value="${data.article||''}"></td>
            <td class="xls-td relative"><input type="text" oninput="syncBldgState(${data.id}, 'description', this.value)" data-col="description" class="xls-input" placeholder="Description" value="${data.description||''}"></td>
            <td class="xls-td relative"><input type="text" oninput="syncBldgState(${data.id}, 'classification', this.value)" data-col="classification" class="xls-input" placeholder="Classification" value="${data.classification||''}"></td>
            <td class="xls-td relative"><input type="text" oninput="syncBldgState(${data.id}, 'occupancy_nature', this.value)" data-col="occupancy_nature" class="xls-input" placeholder="Occupancy" value="${data.occupancy_nature||''}"></td>
            <td class="xls-td relative"><input type="text" oninput="syncBldgState(${data.id}, 'location', this.value)" data-col="location" class="xls-input" placeholder="Location" value="${data.location||''}"></td>
            <td class="xls-td relative"><input type="date" oninput="syncBldgState(${data.id}, 'date_constructed', this.value)" data-col="date_constructed" class="xls-input" value="${data.date_constructed||today}"></td>
            <td class="xls-td relative"><input type="date" oninput="syncBldgState(${data.id}, 'acquisition_date', this.value)" data-col="acquisition_date" class="xls-input" value="${data.acquisition_date||today}"></td>
            <td class="xls-td relative"><input type="text" oninput="syncBldgState(${data.id}, 'property_number', this.value)" data-col="property_number" class="xls-input" placeholder="Property No." value="${data.property_number||''}"></td>
            <td class="xls-td relative"><input type="number" oninput="syncBldgState(${data.id}, 'acquisition_cost', this.value)" data-col="acquisition_cost" class="xls-input text-right" placeholder="0.00" min="0" step="0.01" value="${data.acquisition_cost||''}"></td>
            <td class="xls-td relative"><input type="number" oninput="syncBldgState(${data.id}, 'appraised_value', this.value)" data-col="appraised_value" class="xls-input text-right" placeholder="0.00" min="0" step="0.01" value="${data.appraised_value||''}"></td>
            <td class="xls-td relative"><input type="date" oninput="syncBldgState(${data.id}, 'appraisal_date', this.value)" data-col="appraisal_date" class="xls-input" value="${data.appraisal_date||''}"></td>
            <td class="xls-td relative"><input type="text" oninput="syncBldgState(${data.id}, 'remarks', this.value)" data-col="remarks" class="xls-input" placeholder="Remarks" value="${data.remarks||''}"></td>
            <td class="xls-td text-center w-10"><button onclick="delBldgRow(${data.id})" class="p-1.5 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button></td>`;
        tbody.appendChild(tr);
    }

    function addBldgRow() {
        const newId = ++_bldgRnCounter;
        const today = new Date().toISOString().split('T')[0];
        const row = { id: newId, office_type: '', school_identifier: '', office_name: '', address: '', storeys: '', classrooms: '', article: '', description: '', classification: '', occupancy_nature: '', location: '', date_constructed: today, acquisition_date: today, property_number: '', acquisition_cost: '', appraised_value: '', appraisal_date: '', remarks: '' };
        bldgRowsData.push(row);
        bldgCurrentPage = Math.ceil(bldgRowsData.length / bldgRowsPerPage);
        renderBldgTable();
    }

    function delBldgRow(id) {
        bldgRowsData = bldgRowsData.filter(r => r.id !== id);
        renderBldgTable();
    }

    function prevBldgPage() { if (bldgCurrentPage > 1) { bldgCurrentPage--; renderBldgTable(); } }
    function nextBldgPage() { if (bldgCurrentPage < Math.ceil(bldgRowsData.length / bldgRowsPerPage)) { bldgCurrentPage++; renderBldgTable(); } }
    function goToBldgPage(p) { p = parseInt(p); if (p >= 1 && p <= Math.ceil(bldgRowsData.length / bldgRowsPerPage)) { bldgCurrentPage = p; renderBldgTable(); } }

    // Bulk Delete
    let bldgDeleteMode = 'rows';
    function setBldgDeleteMode(mode) {
        bldgDeleteMode = mode;
        const btnRows = document.getElementById('btnBldgDelRows');
        const btnPages = document.getElementById('btnBldgDelPages');
        const lblFrom = document.getElementById('lblBldgDelFrom');
        const lblTo = document.getElementById('lblBldgDelTo');
        if (mode === 'rows') {
            btnRows.classList.add('bg-white', 'shadow-sm', 'text-slate-800'); btnRows.classList.remove('text-slate-400');
            btnPages.classList.remove('bg-white', 'shadow-sm', 'text-slate-800'); btnPages.classList.add('text-slate-400');
            lblFrom.textContent = 'From Row'; lblTo.textContent = 'To Row';
            document.getElementById('bldgDeleteTo').value = bldgRowsData.length;
        } else {
            btnPages.classList.add('bg-white', 'shadow-sm', 'text-slate-800'); btnPages.classList.remove('text-slate-400');
            btnRows.classList.remove('bg-white', 'shadow-sm', 'text-slate-800'); btnRows.classList.add('text-slate-400');
            lblFrom.textContent = 'From Page'; lblTo.textContent = 'To Page';
            document.getElementById('bldgDeleteTo').value = Math.ceil(bldgRowsData.length / bldgRowsPerPage);
        }
    }
    function openBldgBulkDeleteModal() {
        const m = document.getElementById('bldgBulkDeleteModal');
        m.classList.remove('hidden');
        setTimeout(() => { m.classList.remove('opacity-0'); m.querySelector('.transform').classList.remove('scale-95'); }, 10);
        setBldgDeleteMode('rows');
        document.getElementById('bldgDeleteFrom').value = 1;
    }
    function closeBldgBulkDeleteModal() {
        const m = document.getElementById('bldgBulkDeleteModal');
        m.classList.add('opacity-0'); m.querySelector('.transform').classList.add('scale-95');
        setTimeout(() => m.classList.add('hidden'), 300);
    }
    function confirmBldgBulkDelete() {
        let from = parseInt(document.getElementById('bldgDeleteFrom').value);
        let to = parseInt(document.getElementById('bldgDeleteTo').value);
        let fIdx, tIdx;
        if (bldgDeleteMode === 'rows') {
            if (isNaN(from) || isNaN(to) || from < 1 || to < from || to > bldgRowsData.length) { Swal.fire({ icon: 'error', title: 'Invalid Range' }); return; }
            fIdx = from - 1; tIdx = to - 1;
        } else {
            const tp = Math.ceil(bldgRowsData.length / bldgRowsPerPage);
            if (isNaN(from) || isNaN(to) || from < 1 || to < from || from > tp) { Swal.fire({ icon: 'error', title: 'Invalid Range' }); return; }
            if (to > tp) to = tp;
            fIdx = (from - 1) * bldgRowsPerPage;
            tIdx = (to * bldgRowsPerPage) - 1;
            if (tIdx >= bldgRowsData.length) tIdx = bldgRowsData.length - 1;
        }
        const count = tIdx - fIdx + 1;
        Swal.fire({ title: 'Confirm Delete', text: `Delete ${count} buildings?`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626' }).then(res => {
            if (res.isConfirmed) {
                bldgRowsData.splice(fIdx, count);
                renderBldgTable(); closeBldgBulkDeleteModal();
            }
        });
    }

    function openBldgBulkAddModal() {
        const m = document.getElementById('bldgBulkModal');
        m.classList.remove('hidden');
        setTimeout(() => { m.classList.remove('opacity-0'); m.querySelector('.transform').classList.remove('scale-95'); }, 10);
    }
    
    function closeBldgBulkModal() {
        const m = document.getElementById('bldgBulkModal');
        m.classList.add('opacity-0'); m.querySelector('.transform').classList.add('scale-95');
        setTimeout(() => m.classList.add('hidden'), 300);
    }
    
    function doBldgBulkAdd() {
        const n = parseInt(document.getElementById('bldgBulkCount').value) || 1;
        const today = new Date().toISOString().split('T')[0];
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
            date_constructed: document.getElementById('bkDateConst').value || today,
            acquisition_date: document.getElementById('bkAcqDate').value || today,
            property_number: document.getElementById('bkPropNo').value,
            acquisition_cost: document.getElementById('bkCost').value,
            appraised_value: document.getElementById('bkAppVal').value,
            appraisal_date: document.getElementById('bkAppDate').value,
            remarks: document.getElementById('bkRemarks').value,
        };
        for (let i = 0; i < n; i++) {
            const newId = ++_bldgRnCounter;
            bldgRowsData.push({ id: newId, ...pf });
        }
        bldgCurrentPage = Math.ceil(bldgRowsData.length / bldgRowsPerPage);
        renderBldgTable();
        closeBldgBulkModal();

        Swal.fire({
            icon: 'success',
            title: 'Bulk Buildings Added',
            text: `Successfully added ${n} rows.`,
            timer: 2000,
            showConfirmButton: false,
            background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#fff',
            color: document.documentElement.classList.contains('dark') ? '#fff' : '#1e293b',
            customClass: {
                popup: 'rounded-[2rem] border border-slate-200 dark:border-slate-800 shadow-2xl',
                title: 'text-xl font-black uppercase italic tracking-tight',
                htmlContainer: 'text-sm font-bold text-slate-400'
            }
        });
    }

    function updateBldgNewLabels() {
        const visibleInputs = document.querySelectorAll('#stepAddBuilding input[data-col]');
        if (visibleInputs.length === 0) return;
        const colNames = Array.from(new Set(Array.from(visibleInputs).map(el => el.getAttribute('data-col'))));
        const start = (bldgCurrentPage - 1) * bldgRowsPerPage;
        const pageData = bldgRowsData.slice(start, start + bldgRowsPerPage);
        const contexts = {};
        colNames.forEach(cn => { contexts[cn] = { seen: new Set(), first: new Map() }; });
        pageData.forEach(row => {
            colNames.forEach(cn => {
                const v = (row[cn] || "").toString().trim().toLowerCase();
                if (v && !contexts[cn].seen.has(v)) { contexts[cn].first.set(v, row.id); contexts[cn].seen.add(v); }
            });
        });
        visibleInputs.forEach(inp => {
            const cn = inp.getAttribute('data-col');
            const v = inp.value.trim().toLowerCase();
            const tr = inp.closest('tr');
            if (!tr) return;
            const rid = parseInt(tr.id.split('-')[2]); // bldg-row-ID
            const td = inp.closest('td');
            const badge = td.querySelector('.new-badge');
            if (badge) badge.remove();
            if (v !== '' && contexts[cn].first.get(v) === rid) {
                const b = document.createElement('span'); b.className = 'new-badge'; b.textContent = 'NEW'; td.appendChild(b);
            }
        });
    }

    function confirmBldgSubmit() {
        if (bldgRowsData.length === 0) { Swal.fire({ title: 'No Buildings', text: 'Add at least one row.', icon: 'warning' }); return; }
        let missing = 0;
        bldgRowsData.forEach(r => { if (!r.office_name?.trim()) missing++; });
        if (missing > 0) { Swal.fire({ title: 'Missing Data', text: `${missing} rows missing Office/School Name.`, icon: 'warning' }); return; }

        Swal.fire({
            title: 'Register Buildings?', html: `<strong>${bldgRowsData.length}</strong> buildings will be added.`,
            icon: 'question', showCancelButton: true, confirmButtonColor: '#1e293b', showLoaderOnConfirm: true,
            preConfirm: () => {
                return fetch("{{ route('register.building.store') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ rows: bldgRowsData.map(r => ({ ...r, region: 'REGION IX', division: 'Division of Zamboanga City' })) })
                })
                .then(res => { if (!res.ok) throw new Error(res.statusText); return res.json(); })
                .catch(err => Swal.showValidationMessage(`Request failed: ${err}`));
            }
        }).then(res => {
            if (res.isConfirmed && res.value.success) {
                Swal.fire({ title: 'Success!', text: res.value.message, icon: 'success' }).then(() => { window.location.href = '/inventory-setup'; });
            }
        });
    }
</script>
