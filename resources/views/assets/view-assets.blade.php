<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Inventory | DepEd Zamboanga City</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade { animation: fadeIn 0.4s ease-out forwards; }
        .custom-scroll::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .xls-th { padding: 12px 16px; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; color: #64748b; white-space: nowrap; border-right: 1px solid #f1f5f9; border-bottom: 2px solid #e2e8f0; background: #f8fafc; position: sticky; top: 0; z-index: 20; }
        .xls-td { height: 48px; border-right: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; vertical-align: middle; padding: 0; background: #ffffff; }
        .xls-row { transition: background 0.1s; }
        .xls-row:hover .xls-td { background-color: #fff1f2 !important; }
        .xls-const { display: flex; align-items: center; padding: 0 16px; height: 100%; font-size: 11.5px; font-weight: 700; color: #334155; white-space: nowrap; }
        .xls-scroll-wrap { position: relative; overflow-x: auto; overflow-y: auto; height: calc(100vh - 620px); min-height: 350px; background: #ffffff; flex-grow: 1; transition: height 0.3s ease-in-out; }
        .xls-scroll-wrap.expanded { height: calc(100vh - 280px); }
        
        .pg-btn {
            padding: 8px 18px;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            border-radius: 9999px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid #e2e8f0;
            background: white;
            color: #1e293b;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .pg-btn:hover:not(:disabled) {
            border-color: #c00000;
            color: #c00000;
            transform: translateY(-1px);
        }
        .pg-btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
            background: #f1f5f9;
        }
        

        
        .glass-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .custom-autocomplete {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-top: 4px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 100;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .custom-autocomplete-item {
            padding: 10px 16px;
            font-size: 10px;
            font-weight: 700;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s;
            text-transform: uppercase;
        }
        .custom-autocomplete-item:hover {
            background: #f8fafc;
            color: #c00000;
        }

    </style>
</head>
<body class="bg-slate-50 min-h-screen flex text-slate-900 overflow-x-hidden">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll">
    <div class="w-full mx-auto p-6 lg:p-10 flex flex-col h-full">

        <div class="flex justify-between items-center mb-10 px-2">
            <div>
                <h2 class="text-3xl font-black text-slate-800 uppercase italic leading-none">Asset Inventory</h2>
                <p class="text-slate-400 text-xs font-bold uppercase tracking-[0.2em] mt-2">Division-Wide Property & Equipment Registry</p>
            </div>
            <div class="flex items-center gap-4">
                <button onclick="toggleAssetFilters()" id="toggleFilterBtn" class="px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest text-slate-500 bg-white border border-slate-100 hover:border-red-600 transition-all flex items-center gap-2 active:scale-95 shadow-sm italic">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg>
                    Hide Filters
                </button>
                <a href="/dashboard" class="back-btn-cool px-6 py-3 rounded-2xl text-sm font-bold text-slate-600 flex items-center gap-2 shadow-sm active:scale-95 bg-white border border-slate-100 hover:border-red-600 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                    Back
                </a>
            </div>
        </div>

        <!-- Filter Configuration -->
        <div id="assetFilterSection" class="bg-white rounded-[2.5rem] shadow-lg border border-slate-100 p-8 mb-8 relative z-50 animate-fade transition-all duration-300 origin-top">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-8 relative z-10">
                <div>
                    <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Classification</label>
                    <select id="assetFilterClassification" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-500">
                        <option value="">All Classifications</option>
                    </select>
                </div>
                <div>
                    <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Category</label>
                    <select id="assetFilterCategory" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-500">
                        <option value="">All Categories</option>
                    </select>
                </div>
                <div>
                    <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Item (Article)</label>
                    <select id="assetFilterItem" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-500">
                        <option value="">All Items</option>
                    </select>
                </div>
                <div>
                    <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Cost Sorting</label>
                    <select id="assetFilterSort" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-500">
                        <option value="">Default (ID)</option>
                        <option value="high_to_low">Value: High to Low</option>
                        <option value="low_to_high">Value: Low to High</option>
                    </select>
                </div>
                <div class="relative">
                    <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Office / School</label>
                    <div class="relative">
                        <input type="text" id="assetFilterSchool" placeholder="Search School..." autocomplete="off" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-500 pr-10">
                        <div id="assetSchoolDropdown" class="custom-autocomplete hidden"></div>
                    </div>
                </div>
                <div>
                    <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Source of Acquisition</label>
                    <select id="assetFilterSource" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-500">
                        <option value="">All Sources</option>
                    </select>
                </div>
                <div>
                    <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Mode of Acquisition</label>
                    <select id="assetFilterMode" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-500">
                        <option value="">All Modes</option>
                    </select>
                </div>
                <div>
                    <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Date Acquired (Acceptance)</label>
                    <input type="date" id="assetFilterDate" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-500">
                </div>
                <div>
                    <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Data Integrity (Empty Fields)</label>
                    <select id="assetFilterEmptyCol" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-500">
                        <option value="">None (All Records)</option>
                        <option value="classification">Missing Classification</option>
                        <option value="category">Missing Category</option>
                        <option value="article">Missing Article</option>
                        <option value="description">Missing Description</option>
                        <option value="property_number">Missing Property No.</option>
                        <option value="unit_of_measurement">Missing Unit of Measure</option>
                        <option value="acq_source">Missing Source</option>
                        <option value="mode_of_acquisition">Missing Mode</option>
                        <option value="acceptance_date">Missing Date</option>
                        <option value="school_id">Missing School ID</option>
                        <option value="school_name">Missing School Name</option>
                        <option value="occupancy">Missing Nature of Occupancy</option>
                        <option value="location">Missing Location</option>
                        <option value="acquisition_date">Missing Acquisition Date</option>
                    </select>
                </div>
            </div>
            <div class="mt-8 flex justify-end items-center gap-8 relative z-10">
                <button onclick="clearAssetFilters()" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] hover:text-red-600 transition-all italic">Clear All Filters</button>
                <button onclick="assetFetchData()" class="px-8 py-2.5 bg-[#c00000] text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-red-700 transition-all active:scale-95 shadow-lg shadow-red-200 italic">Apply Configuration</button>
            </div>
        </div>

        <!-- Tab Selection -->
        <div class="flex justify-start mb-6 px-2">
            <div class="inline-flex p-1 bg-white rounded-[1.2rem] border border-slate-200 shadow-sm relative overflow-hidden">
                <button onclick="setAssetTab('source')" id="tabBtnSource" class="relative z-10 px-8 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all duration-300 rounded-xl text-slate-500 hover:text-[#c00000]">
                    Asset Source
                </button>
                <button onclick="setAssetTab('distribution')" id="tabBtnDist" class="relative z-10 px-8 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all duration-300 rounded-xl text-white bg-[#c00000] shadow-md shadow-red-200">
                    Asset Distribution
                </button>
            </div>

            {{-- PPE/Semi-PPE Dropdown --}}
            <div class="ml-4">
                <select id="assetPropertyType" onchange="assetFetchData()" class="bg-white border border-slate-200 rounded-[1.2rem] px-6 py-2.5 text-[10px] font-black text-slate-700 uppercase tracking-widest focus:ring-4 focus:ring-red-50 focus:border-[#c00000] transition-all outline-none shadow-sm">
                    <option value="ALL">All Assets</option>
                    <option value="RPCPPE">PPE (≥ 50k)</option>
                    <option value="RPCSP">Semi-PPE (< 50k)</option>
                </select>
            </div>
        </div>

        <div class="bg-white rounded-[2rem] border border-slate-100 shadow-lg overflow-hidden flex flex-col animate-fade relative">
            <div class="xls-scroll-wrap">
                <table class="w-full border-collapse" style="min-width:1400px;">
                    <thead id="assetHeader"></thead>
                    <tbody id="assetBody"></tbody>
                </table>
                
                {{-- Loading State --}}
                <div id="assetLoading" class="absolute inset-0 bg-white/60 backdrop-blur-[2px] z-50 flex items-center justify-center hidden">
                    <div class="flex flex-col items-center gap-4">
                        <div class="w-12 h-12 border-4 border-slate-100 border-t-red-600 rounded-full animate-spin"></div>
                        <p class="text-[10px] font-black text-slate-800 uppercase tracking-widest italic">Fetching Asset Records...</p>
                    </div>
                </div>

                {{-- Empty State --}}
                <div id="assetEmpty" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="inline-flex flex-col items-center gap-3 opacity-30">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.25em]">No assets found — adjust filters</p>
                    </div>
                </div>
            </div>

            <div id="assetTableFooter" class="px-6 py-4 border-t border-slate-100 flex items-center justify-between bg-white relative z-30 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                <div class="flex items-center gap-6">
                    <p id="assetRowCountLabel" class="text-[9px] font-black text-slate-400 uppercase tracking-widest">0 Rows</p>
                    <div id="assetPaginationControls" class="flex items-center gap-3 border-l border-slate-200 pl-6">
                        <button onclick="assetPrevPage()" id="assetPrevBtn" class="pg-btn">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
                            Prev
                        </button>
                        <div class="glass-indicator">
                            <span id="assetCurrentPage" class="text-[10px] font-black text-[#c00000]">1</span>
                            <span class="text-[10px] font-bold text-slate-400">/</span>
                            <span id="assetTotalPages" class="text-[10px] font-black text-slate-500">1</span>
                        </div>
                        <button onclick="assetNextPage()" id="assetNextBtn" class="pg-btn">
                            Next
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>
                <div></div>
            </div>
        </div>
    </div>
    </div>

    <script>
        let assetRowsData = [];
        let assetCurrentPage = 1;
        const assetRowsPerPage = 50;
        let allSchoolList = [];
        let isSchoolInit = false;
        let currentAssetTab = 'distribution';

        function setAssetTab(tab) {
            currentAssetTab = tab;
            const sourceBtn = document.getElementById('tabBtnSource');
            const distBtn = document.getElementById('tabBtnDist');
            if (tab === 'source') {
                sourceBtn.className = "relative z-10 px-8 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all duration-300 rounded-xl text-white bg-[#c00000] shadow-md shadow-red-200";
                distBtn.className = "relative z-10 px-8 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all duration-300 rounded-xl text-slate-500 hover:text-[#c00000]";
                document.getElementById('assetFilterSchool').parentElement.parentElement.classList.add('opacity-30', 'pointer-events-none');
            } else {
                distBtn.className = "relative z-10 px-8 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all duration-300 rounded-xl text-white bg-[#c00000] shadow-md shadow-red-200";
                sourceBtn.className = "relative z-10 px-8 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all duration-300 rounded-xl text-slate-500 hover:text-[#c00000]";
                document.getElementById('assetFilterSchool').parentElement.parentElement.classList.remove('opacity-30', 'pointer-events-none');
            }
            assetCurrentPage = 1;
            assetFetchData();
        }

        async function assetFetchFilters() {
            try {
                const res = await fetch("{{ route('api.reports.filters') }}");
                const data = await res.json();
                const populate = (id, list, label) => {
                    const el = document.getElementById(id);
                    el.innerHTML = `<option value="">All ${label}s</option>`;
                    list.forEach(item => {
                        const opt = document.createElement('option');
                        opt.value = item;
                        opt.textContent = item;
                        el.appendChild(opt);
                    });
                };
                populate('assetFilterClassification', data.classifications, 'Classification');
                populate('assetFilterCategory', data.categories, 'Category');
                populate('assetFilterItem', data.items, 'Item');
                populate('assetFilterSource', data.sources, 'Source');
                populate('assetFilterMode', data.modes, 'Mode');
                allSchoolList = data.schools || [];
                if (!isSchoolInit) { initSchoolAutocomplete(); isSchoolInit = true; }
            } catch (e) { console.error('Failed to fetch filters', e); }
        }

        function initSchoolAutocomplete() {
            const input = document.getElementById('assetFilterSchool');
            const dropdown = document.getElementById('assetSchoolDropdown');
            input.onfocus = () => showDropdown(input.value);
            input.oninput = (e) => showDropdown(e.target.value);
            document.addEventListener('click', (e) => {
                if (!input.contains(e.target) && !dropdown.contains(e.target)) dropdown.classList.add('hidden');
            });
            function showDropdown(query = '') {
                const filtered = allSchoolList.filter(s => s.toLowerCase().includes(query.toLowerCase()));
                dropdown.innerHTML = '';
                if (filtered.length === 0) { dropdown.classList.add('hidden'); return; }
                filtered.slice(0, 10).forEach(school => {
                    const item = document.createElement('div');
                    item.className = 'custom-autocomplete-item';
                    item.textContent = school;
                    item.onclick = () => { input.value = school; dropdown.classList.add('hidden'); assetFetchData(); };
                    dropdown.appendChild(item);
                });
                dropdown.classList.remove('hidden');
            }
        }

        async function assetFetchData() {
            const loading = document.getElementById('assetLoading');
            loading.classList.remove('hidden');
            const filters = {
                classification: document.getElementById('assetFilterClassification').value,
                category: document.getElementById('assetFilterCategory').value,
                article: document.getElementById('assetFilterItem').value,
                source: document.getElementById('assetFilterSource').value,
                mode: document.getElementById('assetFilterMode').value,
                dateAcquired: document.getElementById('assetFilterDate').value,
                schoolName: document.getElementById('assetFilterSchool').value,
                sortCost: document.getElementById('assetFilterSort').value,
                emptyCol: document.getElementById('assetFilterEmptyCol').value,
                tab: currentAssetTab
            };
            const reportType = document.getElementById('assetPropertyType').value;
            try {
                const res = await fetch("{{ route('api.reports.preview') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ filters: filters, report_type: reportType })
                });
                const data = await res.json();
                assetRowsData = data.rows || [];
                renderAssetTable();
            } catch (e) { console.error('Failed to fetch assets', e); } finally { loading.classList.add('hidden'); }
        }

        function renderAssetTable() {
            const header = document.getElementById('assetHeader');
            const tbody = document.getElementById('assetBody');
            if (currentAssetTab === 'source') {
                header.innerHTML = `<tr><th class="xls-th w-10 text-center sticky left-0 z-10">#</th><th class="xls-th" style="min-width:150px">Classification</th><th class="xls-th" style="min-width:150px">Category</th><th class="xls-th" style="min-width:180px">Article</th><th class="xls-th" style="min-width:300px">Description</th><th class="xls-th" style="min-width:80px">Unit</th><th class="xls-th" style="min-width:120px">Unit Cost</th><th class="xls-th" style="min-width:80px">Total Qty</th><th class="xls-th" style="min-width:150px">Total Cost</th><th class="xls-th" style="min-width:150px">Mode</th><th class="xls-th" style="min-width:200px">Source/Supplier</th><th class="xls-th" style="min-width:150px">Date Acquired</th></tr>`;
            } else {
                header.innerHTML = `<tr>
                    <th class="xls-th w-10 text-center sticky left-0 z-10">#</th>
                    <th class="xls-th" style="min-width:100px">Region</th>
                    <th class="xls-th" style="min-width:180px">Division</th>
                    <th class="xls-th" style="min-width:150px">Office/School Type</th>
                    <th class="xls-th" style="min-width:100px">School ID</th>
                    <th class="xls-th" style="min-width:250px">Office/School Name</th>
                    <th class="xls-th" style="min-width:150px">Nature of Occupancy</th>
                    <th class="xls-th" style="min-width:150px">Location</th>
                    <th class="xls-th" style="min-width:180px">Property No.</th>
                    <th class="xls-th" style="min-width:150px">Acquisition Cost (₱)</th>
                    <th class="xls-th" style="min-width:150px">Acquisition Date</th>
                </tr>`;
            }
            tbody.innerHTML = '';
            if (assetRowsData.length === 0) {
                document.getElementById('assetEmpty').classList.remove('hidden');
                document.getElementById('assetRowCountLabel').textContent = '0 Rows';
                return;
            }
            document.getElementById('assetEmpty').classList.add('hidden');
            const start = (assetCurrentPage - 1) * assetRowsPerPage;
            const pageData = assetRowsData.slice(start, start + assetRowsPerPage);
            pageData.forEach((row, idx) => {
                const tr = document.createElement('tr');
                tr.className = 'xls-row group border-b border-slate-100';
                const cell = (val, extra = '') => `<td class="xls-td relative ${extra}"><span class="xls-const">${val || ''}</span></td>`;
                const costCell = (val, extra = '') => `<td class="xls-td relative ${extra}"><span class="xls-const font-black text-emerald-600 italic">₱ ${Number(val || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></td>`;
                if (currentAssetTab === 'source') {
                    tr.innerHTML = `<td class="xls-td text-center sticky left-0 w-10 bg-[#f8fafc] z-10"><span class="text-[10px] font-black text-slate-500">${start + idx + 1}</span></td>${cell(row.classification, 'text-blue-600 font-bold')}${cell(row.category, 'text-slate-500')}${cell(row.article, 'font-bold text-slate-800')}${cell(row.description, 'text-slate-600 italic')}${cell(row.unit_of_measurement)}${costCell(row.asset_cost)}${cell(row.quantity, 'font-black text-amber-600')}${costCell(row.acquisition_cost, 'bg-slate-50')}${cell(row.mode_of_acquisition)}${cell(row.acq_source, 'font-bold text-blue-600')}${cell(row.acceptance_date)}`;
                } else {
                    tr.innerHTML = `<td class="xls-td text-center sticky left-0 w-10 bg-[#f8fafc] z-10"><span class="text-[10px] font-black text-slate-500">${start + idx + 1}</span></td>
                        <td class="xls-td"><span class="xls-const">Region IX</span></td>
                        <td class="xls-td"><span class="xls-const">Division of Zamboanga City</span></td>
                        ${cell(row.school_type)}
                        ${cell(row.school_id)}
                        ${cell(row.office_school_name, 'font-bold text-[#c00000]')}
                        ${cell(row.nature_of_occupancy)}
                        ${cell(row.location)}
                        ${cell(row.property_number)}
                        ${costCell(row.acquisition_cost, 'bg-slate-50')}
                        ${cell(row.acquisition_date)}`;
                }
                tbody.appendChild(tr);
            });
            const totalPages = Math.ceil(assetRowsData.length / assetRowsPerPage) || 1;
            document.getElementById('assetRowCountLabel').textContent = assetRowsData.length + " Assets Found";
            document.getElementById('assetCurrentPage').textContent = assetCurrentPage;
            document.getElementById('assetTotalPages').textContent = totalPages;
            document.getElementById('assetPrevBtn').disabled = assetCurrentPage === 1;
            document.getElementById('assetNextBtn').disabled = assetCurrentPage === totalPages;
        }

        function clearAssetFilters() {
            document.getElementById('assetFilterClassification').value = '';
            document.getElementById('assetFilterCategory').value = '';
            document.getElementById('assetFilterItem').value = '';
            document.getElementById('assetFilterSource').value = '';
            document.getElementById('assetFilterMode').value = '';
            document.getElementById('assetFilterDate').value = '';
            document.getElementById('assetFilterSchool').value = '';
            document.getElementById('assetFilterSort').value = '';
            document.getElementById('assetFilterEmptyCol').value = '';
            assetCurrentPage = 1;
            assetFetchData();
        }

        function assetPrevPage() { if (assetCurrentPage > 1) { assetCurrentPage--; renderAssetTable(); } }
        function assetNextPage() { const t = Math.ceil(assetRowsData.length/assetRowsPerPage); if (assetCurrentPage < t) { assetCurrentPage++; renderAssetTable(); } }

        function toggleAssetFilters() {
            const section = document.getElementById('assetFilterSection');
            const btn = document.getElementById('toggleFilterBtn');
            const tableWrap = document.querySelector('.xls-scroll-wrap');
            
            if (section.classList.contains('hidden')) {
                section.classList.remove('hidden');
                tableWrap.classList.remove('expanded');
                btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg> Hide Filters`;
            } else {
                section.classList.add('hidden');
                tableWrap.classList.add('expanded');
                btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg> Show Filters`;
            }
        }

        document.addEventListener('DOMContentLoaded', () => { assetFetchFilters(); assetFetchData(); });
    </script>
</body>
</html>
