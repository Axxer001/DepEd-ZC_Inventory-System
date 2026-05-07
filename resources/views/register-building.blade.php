<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Building Registration | DepEd Command Center</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade { animation: fadeIn 0.4s ease-out forwards; }
        .custom-scroll::-webkit-scrollbar { width: 5px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .back-btn-cool { background: white; border: 1px solid #e2e8f0; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .back-btn-cool:hover { border-color: #c00000; color: #c00000; box-shadow: 0 10px 15px -3px rgba(192, 0, 0, 0.1); transform: translateX(-4px); }
        .xls-th { padding: 9px 14px; font-size: 9px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.12em; color: #64748b; white-space: nowrap; border-right: 1px solid #e8edf2; border-bottom: 2px solid #dde3ea; background: #f4f6f9; position: sticky; top: 0; z-index: 5; }
        .xls-td { padding: 0; border-right: 1px solid #eef0f4; border-bottom: 1px solid #f0f3f6; vertical-align: middle; }
        .xls-row { transition: background 0.1s; }
        .xls-row:hover .xls-td { background-color: rgba(244,246,249,0.9); }
        .xls-input { width: 100%; padding: 11px 14px; font-size: 11.5px; font-weight: 600; color: #334155; background: rgba(0,0,0,0.035); border: 1px solid transparent; outline: none; box-sizing: border-box; transition: all 0.2s; }
        .xls-input:focus { background: rgba(192,0,0,0.045); border-color: #c00000; box-shadow: 0 0 0 2px rgba(192,0,0,0.1); }
        .xls-input::placeholder { color: #c8d0db; font-weight: 500; }
        .xls-const { display: block; padding: 11px 14px; font-size: 11.5px; font-weight: 700; color: #94a3b8; white-space: nowrap; font-style: italic; }
        .xls-scroll-wrap { position: relative; overflow-x: auto; overflow-y: auto; min-height: 455px; max-height: 455px; }

        /* Custom Autocomplete */
        .custom-autocomplete { position: absolute; background: white; border: 1px solid #e2e8f0; border-radius: 12px; shadow: 0 10px 15px -3px rgba(0,0,0,0.1); z-index: 1000; overflow: hidden; }
        .custom-autocomplete-item { padding: 10px 14px; font-size: 11px; font-weight: 600; color: #475569; cursor: pointer; border-bottom: 1px solid #f1f5f9; }
        .custom-autocomplete-item:hover { background: #f8fafc; color: #c00000; }
        .custom-autocomplete-item:last-child { border-bottom: none; }

        .new-badge { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: #c00000; color: white; font-size: 7px; font-weight: 900; padding: 2px 5px; border-radius: 4px; pointer-events: none; letter-spacing: 0.05em; }
        .relative { position: relative; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex text-slate-900 overflow-x-hidden">

    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({ title: 'Building Registered!', text: @json(session('success')), icon: 'success', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
            });
        </script>
    @endif

    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({ title: 'Registration Error', html: `{!! implode('<br>', $errors->all()) !!}`, icon: 'error', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
            });
        </script>
    @endif

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll">
    <div class="w-full mx-auto p-6 lg:p-10 min-h-screen flex flex-col">

        <div class="flex justify-between items-center mb-10 px-2">
            <div>
                <h2 class="text-3xl font-black text-slate-800 uppercase italic leading-none">Register New Building</h2>
                <p class="text-slate-400 text-xs font-bold uppercase tracking-[0.2em] mt-2">Department of Education • Zamboanga City</p>
            </div>
            <a href="/inventory-setup" class="back-btn-cool px-6 py-3 rounded-2xl text-sm font-bold text-slate-600 flex items-center gap-2 shadow-sm active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                Back
            </a>
        </div>

        <div class="flex-grow">
        {{-- STEP 1: Building Table --}}
        <div id="step1-content" class="animate-fade">
            <div class="bg-white rounded-[2rem] border border-slate-100 shadow-lg overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 bg-[#c00000] rounded-xl flex items-center justify-center text-white text-xs font-black">1</div>
                        <div>
                            <h3 class="font-black text-slate-800 uppercase tracking-tight text-xs">Building Records</h3>
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">Fill in building details per row</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="openBulkDeleteModal()" class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-red-500 rounded-xl text-[10px] font-black uppercase tracking-wider hover:bg-red-50 transition-all active:scale-95 shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-1.123a2.25 2.25 0 00-2.25-2.25h-4.5a2.25 2.25 0 00-2.25 2.25v1.123m9.913 0a11.405 11.405 0 00-9.913 0"/></svg>
                            Bulk Delete
                        </button>
                        <button onclick="openBulkModal()" class="flex items-center gap-2 px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-wider hover:bg-slate-100 transition-all active:scale-95">
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
                    <div class="flex items-center gap-6">
                        <p id="rowCountLabel" class="text-[9px] font-black text-slate-400 uppercase tracking-widest">0 Rows</p>
                        {{-- Pagination Controls --}}
                        <div id="paginationControls" class="flex items-center gap-1">
                            <button onclick="prevPage()" class="p-1.5 rounded-lg hover:bg-slate-200 text-slate-400 transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg></button>
                            <div class="flex items-center gap-1 px-3 py-1 bg-white border border-slate-200 rounded-lg shadow-sm">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Page</span>
                                <input type="number" id="currentPageInput" onchange="goToPage(this.value)" value="1" min="1" class="w-8 text-center text-[10px] font-black text-slate-800 outline-none">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">of <span id="totalPages">1</span></span>
                            </div>
                            <button onclick="nextPage()" class="p-1.5 rounded-lg hover:bg-slate-200 text-slate-400 transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg></button>
                        </div>
                    </div>
                    <button onclick="confirmSubmit()" class="px-8 py-2.5 bg-slate-900 text-white rounded-xl font-black text-[10px] uppercase tracking-wider hover:bg-black transition-all shadow-sm active:scale-95 flex items-center gap-2">
                        Register Buildings ⚡
                    </button>
                </div>
            </div>
        </div>

        </div>
    </div>
    </div>

    {{-- BULK DELETE MODAL --}}
    <div id="bulkDeleteModal" class="hidden fixed inset-0 z-[60] bg-black/40 flex items-center justify-center p-4 opacity-0 transition-all duration-300">
        <div class="transform scale-95 transition-all duration-300 bg-white rounded-[2.5rem] shadow-2xl w-full max-w-md overflow-hidden">
            <div class="p-8">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h3 class="text-xl font-black text-slate-800 uppercase italic">Bulk Delete</h3>
                        <p class="text-[9px] font-bold text-red-500 uppercase tracking-widest mt-1">Warning: Permanent Action</p>
                    </div>
                    <div class="flex p-1 bg-slate-100 rounded-xl">
                        <button onclick="setDeleteMode('rows')" id="btnDelRows" class="px-4 py-1.5 text-[9px] font-black uppercase tracking-widest rounded-lg bg-white shadow-sm text-slate-800 transition-all">Rows</button>
                        <button onclick="setDeleteMode('pages')" id="btnDelPages" class="px-4 py-1.5 text-[9px] font-black uppercase tracking-widest rounded-lg text-slate-400 transition-all">Pages</button>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-6 mb-8">
                    <div>
                        <label id="lblDelFrom" class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block ml-1">From Row</label>
                        <input type="number" id="deleteFrom" value="1" min="1" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl font-black text-sm outline-none text-center focus:ring-2 focus:ring-red-100">
                    </div>
                    <div>
                        <label id="lblDelTo" class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block ml-1">To Row</label>
                        <input type="number" id="deleteTo" value="1" min="1" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl font-black text-sm outline-none text-center focus:ring-2 focus:ring-red-100">
                    </div>
                </div>
                <div class="flex gap-3">
                    <button onclick="closeBulkDeleteModal()" class="flex-1 py-4 bg-slate-100 text-slate-600 rounded-2xl font-black text-xs uppercase hover:bg-slate-200 transition-all">Cancel</button>
                    <button onclick="confirmBulkDelete()" class="flex-[2] py-4 bg-red-600 text-white rounded-2xl font-black text-xs uppercase hover:bg-red-700 transition-all shadow-lg shadow-red-100">Delete Range</button>
                </div>
            </div>
        </div>
    </div>

    {{-- HIDDEN FORM --}}
    <form id="buildingForm" action="{{ route('register.building.store') }}" method="POST" class="hidden">@csrf <div id="hiddenFields"></div></form>
    <script>
        const allSchools = @json($allSchools);
        let allRowsData = [];
        let _rowNumCounter = 0;
        let currentPage = 1;
        const rowsPerPage = 50;

        function syncState(rowId, col, value) {
            const row = allRowsData.find(r => r.id === rowId);
            if (row) {
                row[col] = value;
            }
        }

        function renderBuildingTable() {
            const tbody = document.getElementById('bldgBody');
            tbody.innerHTML = '';
            
            if (allRowsData.length === 0) {
                document.getElementById('bldgEmpty').classList.remove('hidden');
                document.getElementById('rowCountLabel').textContent = '0 Rows';
                return;
            }
            document.getElementById('bldgEmpty').classList.add('hidden');

            const totalPages = Math.ceil(allRowsData.length / rowsPerPage);
            if (currentPage > totalPages) currentPage = totalPages || 1;
            if (currentPage < 1) currentPage = 1;

            document.getElementById('totalPages').textContent = totalPages;
            document.getElementById('currentPageInput').value = currentPage;

            const start = (currentPage - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            const pageData = allRowsData.slice(start, end);

            pageData.forEach((row, index) => {
                const displayNum = start + index + 1;
                addBldgRowDOM(row, displayNum);
            });

            document.getElementById('rowCountLabel').textContent = allRowsData.length + ' Row' + (allRowsData.length !== 1 ? 's' : '');
            updateNewLabels();
        }

        function addBldgRowDOM(data, displayNum) {
            const tbody = document.getElementById('bldgBody');
            const tr = document.createElement('tr');
            tr.id = `brow-${data.id}`;
            tr.className = 'xls-row group border-b border-slate-100';
            const today = new Date().toISOString().split('T')[0];
            
            tr.innerHTML = `
                <td class="xls-td text-center sticky left-0 w-10" style="background:inherit"><span class="row-num text-[10px] font-black text-slate-300">${displayNum}</span></td>
                <td class="xls-td"><span class="xls-const">Region IX</span></td>
                <td class="xls-td"><span class="xls-const">Division of Zamboanga City</span></td>
                <td class="xls-td relative"><input type="text" oninput="syncState(${data.id}, 'office_type', this.value)" data-col="office_type" class="xls-input" placeholder="School Type" value="${data.office_type||''}"></td>
                <td class="xls-td relative"><input type="text" oninput="syncState(${data.id}, 'school_identifier', this.value)" data-col="school_identifier" class="xls-input" placeholder="School ID" value="${data.school_identifier||''}"></td>
                <td class="xls-td relative"><input type="text" oninput="syncState(${data.id}, 'office_name', this.value)" data-col="office_name" class="xls-input" placeholder="Office/School Name *" value="${data.office_name||''}"></td>
                <td class="xls-td relative"><input type="text" oninput="syncState(${data.id}, 'address', this.value)" data-col="address" class="xls-input" placeholder="Address" value="${data.address||''}"></td>
                <td class="xls-td relative"><input type="number" oninput="syncState(${data.id}, 'storeys', this.value)" data-col="storeys" class="xls-input text-center" placeholder="0" min="0" value="${data.storeys||''}"></td>
                <td class="xls-td relative"><input type="number" oninput="syncState(${data.id}, 'classrooms', this.value)" data-col="classrooms" class="xls-input text-center" placeholder="0" min="0" value="${data.classrooms||''}"></td>
                <td class="xls-td relative"><input type="text" oninput="syncState(${data.id}, 'article', this.value)" data-col="article" class="xls-input" placeholder="Article" value="${data.article||''}"></td>
                <td class="xls-td relative"><input type="text" oninput="syncState(${data.id}, 'description', this.value)" data-col="description" class="xls-input" placeholder="Description" value="${data.description||''}"></td>
                <td class="xls-td relative"><input type="text" oninput="syncState(${data.id}, 'classification', this.value)" data-col="classification" class="xls-input" placeholder="Classification" value="${data.classification||''}"></td>
                <td class="xls-td relative"><input type="text" oninput="syncState(${data.id}, 'occupancy_nature', this.value)" data-col="occupancy_nature" class="xls-input" placeholder="Occupancy" value="${data.occupancy_nature||''}"></td>
                <td class="xls-td relative"><input type="text" oninput="syncState(${data.id}, 'location', this.value)" data-col="location" class="xls-input" placeholder="Location" value="${data.location||''}"></td>
                <td class="xls-td relative"><input type="date" oninput="syncState(${data.id}, 'date_constructed', this.value)" data-col="date_constructed" class="xls-input" value="${data.date_constructed||''}"></td>
                <td class="xls-td relative"><input type="date" oninput="syncState(${data.id}, 'acquisition_date', this.value)" data-col="acquisition_date" class="xls-input" value="${data.acquisition_date||today}"></td>
                <td class="xls-td relative"><input type="text" oninput="syncState(${data.id}, 'property_number', this.value)" data-col="property_number" class="xls-input" placeholder="Property No." value="${data.property_number||''}"></td>
                <td class="xls-td relative"><input type="number" oninput="syncState(${data.id}, 'acquisition_cost', this.value)" data-col="acquisition_cost" class="xls-input text-right" placeholder="0.00" min="0" step="0.01" value="${data.acquisition_cost||''}"></td>
                <td class="xls-td relative"><input type="number" oninput="syncState(${data.id}, 'appraised_value', this.value)" data-col="appraised_value" class="xls-input text-right" placeholder="0.00" min="0" step="0.01" value="${data.appraised_value||''}"></td>
                <td class="xls-td relative"><input type="date" oninput="syncState(${data.id}, 'appraisal_date', this.value)" data-col="appraisal_date" class="xls-input" value="${data.appraisal_date||''}"></td>
                <td class="xls-td relative"><input type="text" oninput="syncState(${data.id}, 'remarks', this.value)" data-col="remarks" class="xls-input" placeholder="Remarks" value="${data.remarks||''}"></td>
                <td class="xls-td text-center w-10"><button onclick="delBldgRow(${data.id})" class="p-1.5 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button></td>`;
            tbody.appendChild(tr);
        }

        function addBldgRow() {
            const newId = ++_rowNumCounter;
            const today = new Date().toISOString().split('T')[0];
            const row = { id: newId, office_type: '', school_identifier: '', office_name: '', address: '', storeys: '', classrooms: '', article: '', description: '', classification: '', occupancy_nature: '', location: '', date_constructed: '', acquisition_date: today, property_number: '', acquisition_cost: '', appraised_value: '', appraisal_date: '', remarks: '' };
            allRowsData.push(row);
            currentPage = Math.ceil(allRowsData.length / rowsPerPage);
            renderBuildingTable();
        }

        function delBldgRow(id) {
            allRowsData = allRowsData.filter(r => r.id !== id);
            renderBuildingTable();
        }

        function prevPage() { if (currentPage > 1) { currentPage--; renderBuildingTable(); } }
        function nextPage() { if (currentPage < Math.ceil(allRowsData.length / rowsPerPage)) { currentPage++; renderBuildingTable(); } }
        function goToPage(p) { p = parseInt(p); if (p >= 1 && p <= Math.ceil(allRowsData.length / rowsPerPage)) { currentPage = p; renderBuildingTable(); } }

        // Bulk Delete
        let deleteMode = 'rows';
        function setDeleteMode(mode) {
            deleteMode = mode;
            const btnRows = document.getElementById('btnDelRows');
            const btnPages = document.getElementById('btnDelPages');
            const lblFrom = document.getElementById('lblDelFrom');
            const lblTo = document.getElementById('lblDelTo');
            if (mode === 'rows') {
                btnRows.classList.add('bg-white', 'shadow-sm', 'text-slate-800'); btnRows.classList.remove('text-slate-400');
                btnPages.classList.remove('bg-white', 'shadow-sm', 'text-slate-800'); btnPages.classList.add('text-slate-400');
                lblFrom.textContent = 'From Row'; lblTo.textContent = 'To Row';
                document.getElementById('deleteTo').value = allRowsData.length;
            } else {
                btnPages.classList.add('bg-white', 'shadow-sm', 'text-slate-800'); btnPages.classList.remove('text-slate-400');
                btnRows.classList.remove('bg-white', 'shadow-sm', 'text-slate-800'); btnRows.classList.add('text-slate-400');
                lblFrom.textContent = 'From Page'; lblTo.textContent = 'To Page';
                document.getElementById('deleteTo').value = Math.ceil(allRowsData.length / rowsPerPage);
            }
        }
        function openBulkDeleteModal() {
            const m = document.getElementById('bulkDeleteModal');
            m.classList.remove('hidden');
            setTimeout(() => { m.classList.remove('opacity-0'); m.querySelector('.transform').classList.remove('scale-95'); }, 10);
            setDeleteMode('rows');
            document.getElementById('deleteFrom').value = 1;
        }
        function closeBulkDeleteModal() {
            const m = document.getElementById('bulkDeleteModal');
            m.classList.add('opacity-0'); m.querySelector('.transform').classList.add('scale-95');
            setTimeout(() => m.classList.add('hidden'), 300);
        }
        function confirmBulkDelete() {
            let from = parseInt(document.getElementById('deleteFrom').value);
            let to = parseInt(document.getElementById('deleteTo').value);
            let fIdx, tIdx;
            if (deleteMode === 'rows') {
                if (isNaN(from) || isNaN(to) || from < 1 || to < from || to > allRowsData.length) { Swal.fire({ icon: 'error', title: 'Invalid Range' }); return; }
                fIdx = from - 1; tIdx = to - 1;
            } else {
                const tp = Math.ceil(allRowsData.length / rowsPerPage);
                if (isNaN(from) || isNaN(to) || from < 1 || to < from || from > tp) { Swal.fire({ icon: 'error', title: 'Invalid Range' }); return; }
                if (to > tp) to = tp;
                fIdx = (from - 1) * rowsPerPage;
                tIdx = (to * rowsPerPage) - 1;
                if (tIdx >= allRowsData.length) tIdx = allRowsData.length - 1;
            }
            const count = tIdx - fIdx + 1;
            Swal.fire({ title: 'Confirm Delete', text: `Delete ${count} buildings?`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626' }).then(res => {
                if (res.isConfirmed) {
                    allRowsData.splice(fIdx, count);
                    renderBuildingTable(); closeBulkDeleteModal();
                }
            });
        }

        // Bulk Modal (Add)
        function openBulkModal() {
            const m = document.getElementById('bulkModal');
            m.classList.remove('hidden');
            setTimeout(() => { m.classList.remove('opacity-0'); m.querySelector('.transform').classList.remove('scale-95'); }, 10);
        }
        function closeBulkModal() {
            const m = document.getElementById('bulkModal');
            m.classList.add('opacity-0'); m.querySelector('.transform').classList.add('scale-95');
            setTimeout(() => m.classList.add('hidden'), 300);
        }
        function doBulkAdd() {
            const n = parseInt(document.getElementById('bulkCount').value) || 1;
            const today = new Date().toISOString().split('T')[0];
            const pf = {
                office_type: document.getElementById('bkType').value,
                school_identifier: document.getElementById('bkSchoolId').value,
                office_name: document.getElementById('bkName').value,
                address: document.getElementById('bkAddr').value,
                classification: document.getElementById('bkClass').value,
                occupancy_nature: document.getElementById('bkOcc').value,
                location: document.getElementById('bkLoc').value,
                acquisition_date: document.getElementById('bkAcqDate').value || today
            };
            for (let i = 0; i < n; i++) {
                const newId = ++_rowNumCounter;
                allRowsData.push({ id: newId, ...pf, storeys: '', classrooms: '', article: '', description: '', date_constructed: '', property_number: '', acquisition_cost: '', appraised_value: '', appraisal_date: '', remarks: '' });
            }
            currentPage = Math.ceil(allRowsData.length / rowsPerPage);
            renderBuildingTable(); closeBulkModal();
        }

        // Autocomplete & NEW Labels
        const dbSuggestions = {};
        let activeAutocomplete = null;
        function closeAutocomplete() { if (activeAutocomplete) { activeAutocomplete.remove(); activeAutocomplete = null; } }
        
        function handleAutocompleteEvent(e) {
            const input = e.target;
            if (!input || input.tagName !== 'INPUT' || !input.hasAttribute('data-col')) return;
            const col = input.getAttribute('data-col');
            const typed = input.value.toLowerCase().trim();
            const start = (currentPage - 1) * rowsPerPage;
            const pageData = allRowsData.slice(start, start + rowsPerPage);
            const local = [];
            for (let i = pageData.length - 1; i >= 0; i--) {
                const v = (pageData[i][col] || "").toString().trim();
                if (v && !local.includes(v) && pageData[i].id !== parseInt(input.closest('tr').id.split('-')[1])) local.push(v);
            }
            const filtered = local.filter(v => v.toLowerCase().includes(typed)).slice(0, 5);
            closeAutocomplete();
            if (filtered.length === 0) return;
            const rect = input.getBoundingClientRect();
            const dd = document.createElement('div');
            dd.className = 'custom-autocomplete';
            dd.style.left = `${rect.left + window.scrollX}px`;
            if (window.innerHeight - rect.bottom < 150 && rect.top > 150) { dd.style.bottom = `${window.innerHeight - rect.top - window.scrollY}px`; } else { dd.style.top = `${rect.bottom + window.scrollY}px`; }
            dd.style.width = `${input.offsetWidth}px`;
            filtered.forEach(v => {
                const it = document.createElement('div');
                it.className = 'custom-autocomplete-item';
                it.textContent = v;
                it.addEventListener('mousedown', evt => {
                    evt.preventDefault(); input.value = v;
                    syncState(parseInt(input.closest('tr').id.split('-')[1]), col, v);
                    closeAutocomplete(); updateNewLabels();
                });
                dd.appendChild(it);
            });
            document.body.appendChild(dd); activeAutocomplete = dd;
        }

        function updateNewLabels() {
            const visibleInputs = document.querySelectorAll('input[data-col]');
            if (visibleInputs.length === 0) return;
            const colNames = Array.from(new Set(Array.from(visibleInputs).map(el => el.getAttribute('data-col'))));
            const start = (currentPage - 1) * rowsPerPage;
            const pageData = allRowsData.slice(start, start + rowsPerPage);
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
                const rid = parseInt(tr.id.split('-')[1]);
                const td = inp.closest('td');
                const badge = td.querySelector('.new-badge');
                if (badge) badge.remove();
                if (v !== '' && contexts[cn].first.get(v) === rid) {
                    const b = document.createElement('span'); b.className = 'new-badge'; b.textContent = 'NEW'; td.appendChild(b);
                }
            });
        }

        document.addEventListener('focusin', handleAutocompleteEvent);
        document.addEventListener('input', handleAutocompleteEvent);
        document.addEventListener('input', updateNewLabels);
        document.addEventListener('mousedown', e => { if (activeAutocomplete && !e.target.closest('.custom-autocomplete') && !e.target.hasAttribute('data-col')) closeAutocomplete(); });
        window.addEventListener('resize', closeAutocomplete);

        function confirmSubmit() {
            if (allRowsData.length === 0) { Swal.fire({ title: 'No Buildings', text: 'Add at least one row.', icon: 'warning' }); return; }
            let missing = 0;
            allRowsData.forEach(r => { if (!r.office_name?.trim()) missing++; });
            if (missing > 0) { Swal.fire({ title: 'Missing Data', text: `${missing} rows missing Office/School Name.`, icon: 'warning' }); return; }

            Swal.fire({
                title: 'Register Buildings?', html: `<strong>${allRowsData.length}</strong> buildings will be added.`,
                icon: 'question', showCancelButton: true, confirmButtonColor: '#1e293b', showLoaderOnConfirm: true,
                preConfirm: () => {
                    return fetch("{{ route('register.building.store') }}", {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ rows: allRowsData.map(r => ({ ...r, region: 'REGION IX', division: 'Division of Zamboanga City' })) })
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
</div>
</div>
</body>
</html>