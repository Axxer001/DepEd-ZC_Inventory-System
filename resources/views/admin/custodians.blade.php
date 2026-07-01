<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custodian Registry | DepEd Zamboanga City</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade { animation: fadeIn 0.4s ease-out forwards; }
        .custom-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; border: 2px solid transparent; background-clip: padding-box; }
        .custom-scroll::-webkit-scrollbar-thumb:hover { background: #f87171; border: 2px solid transparent; background-clip: padding-box; }
        .xls-th { padding: 14px 16px; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; color: #475569; white-space: nowrap; border-right: 1px solid #e2e8f0; border-bottom: 2px solid #cbd5e1; background: #f8fafc; position: sticky; top: 0; z-index: 20; box-shadow: 0 1px 2px rgba(0,0,0,0.02); }
        .xls-td { height: 52px; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; vertical-align: middle; padding: 0; background: white; transition: all 0.3s ease; }
        .xls-row { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; position: relative; }
        .xls-row:hover { transform: translateX(4px); z-index: 10; }
        .xls-row:hover .xls-td { background-color: #fdf3f3 !important; border-bottom-color: #c00000; }
        .xls-row:hover .xls-td:first-child { box-shadow: inset 4px 0 0 #c00000; }
        .xls-row:active { transform: scale(0.995); transition: all 0.1s; }
        .xls-row:active .xls-td { background-color: #fbe3e3 !important; }
        .xls-const { display: flex; align-items: center; padding: 0 16px; height: 100%; font-size: 11.5px; font-weight: 700; color: inherit; white-space: nowrap; }
        .xls-scroll-wrap { position: relative; overflow-x: auto; overflow-y: auto; height: calc(100vh - 350px); min-height: 400px; background: white; flex-grow: 1; transition: height 0.4s cubic-bezier(0.4, 0, 0.2, 1); border-top: 1px solid #e2e8f0; }
        .xls-scroll-wrap.expanded { height: calc(100vh - 250px); }
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
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.05);
        }
        .pg-btn:hover:not(:disabled) {
            border-color: #ef4444;
            color: #ef4444;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.15);
        }
        .pg-btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
            background: #f1f5f9;
        }

        /* Glass Indicator Box */
        .glass-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        /* Filter Chips */
        .filter-chip {
            padding: 8px 16px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-radius: 12px;
            border: 1px solid rgba(226, 232, 240, 0.5);
            background: transparent;
            color: inherit;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .filter-chip.active-red { background: #c00000; border-color: #c00000; color: white; box-shadow: 0 4px 12px rgba(192, 0, 0, 0.2); }
        .filter-chip:hover:not(.active-red) { border-color: #c00000; color: #c00000; background: rgba(192, 0, 0, 0.05); }

        .filter-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            max-height: 200px;
            overflow-y: auto;
            padding: 4px;
        }

        /* Dark Mode Overrides */
        html.dark body { background-color: #0f172a; color: #f8fafc; }
        html.dark .bg-white { background-color: #1e293b !important; border-color: #334155 !important; }
        html.dark .text-slate-800 { color: #f8fafc !important; }
        html.dark .text-slate-900 { color: #f8fafc !important; }
        html.dark .bg-slate-50 { background-color: #0f172a !important; border-color: #1e293b !important; }
        html.dark .bg-slate-50\/50 { background-color: #1e293b !important; }
        html.dark .border-t { border-color: #334155 !important; }
        html.dark .xls-td { background-color: #1e293b !important; border-color: #334155 !important; }
        html.dark .xls-th { background-color: #0f172a !important; border-color: #334155 !important; color: #94a3b8 !important; }
        html.dark .xls-scroll-wrap { background-color: #1e293b !important; border-color: #334155 !important; }
        html.dark .xls-row:hover .xls-td { background-color: #27212b !important; }
        html.dark .xls-row:active .xls-td { background-color: #35232d !important; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex text-slate-900 overflow-x-hidden selection:bg-red-100 selection:text-red-900 relative">
    <div class="absolute inset-0 z-[-1] opacity-[0.03] pointer-events-none" style="background-image: radial-gradient(#000 1px, transparent 1px); background-size: 24px 24px;"></div>

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll relative">
    <div class="w-full mx-auto p-6 lg:p-10 min-h-screen flex flex-col relative z-10">

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-10 px-2 animate-fade">
            <div class="shrink-0">
                <h2 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-red-700 to-red-500 uppercase italic leading-none drop-shadow-sm tracking-tight">Custodian Registry</h2>
                <p class="text-slate-500 text-[11px] font-bold uppercase tracking-[0.25em] mt-3 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse shadow-[0_0_8px_rgba(239,68,68,0.6)]"></span>
                    Asset Custodians & Accountable Personnel
                </p>
            </div>

            {{-- Main Search --}}
            <div class="flex-grow max-w-2xl relative">
                <div class="relative group">
                    <input type="text" id="custodianFilterSearch" oninput="custodianDebouncedSearch()" placeholder="SEARCH BY NAME OR EMPLOYEE ID..." autocomplete="off" class="w-full bg-white border-2 border-slate-100 rounded-2xl px-6 py-4 text-xs font-black uppercase tracking-widest focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-700 shadow-sm pr-12 group-hover:border-slate-200">
                    <div class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 group-hover:text-red-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 shrink-0">

                @if(auth()->check() && auth()->user()->isSuperAdmin())
                <button onclick="openCreateEmployeeModal()" class="px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest text-white bg-red-700 hover:bg-red-800 hover:-translate-y-0.5 hover:shadow-lg active:translate-y-0 transition-all duration-300 flex items-center gap-2 group italic shadow-md shadow-red-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 group-hover:scale-110 transition-transform duration-300"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Add Employee
                </button>
                @endif
                <button onclick="toggleCustodianFilters()" id="toggleFilterBtn" class="px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest text-slate-600 bg-white border border-slate-200 hover:text-[#c00000] hover:-translate-y-0.5 hover:shadow-md active:translate-y-0 transition-all duration-300 flex items-center gap-2 group italic">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 group-hover:rotate-12 transition-transform duration-300"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg>
                    Filters
                </button>
                <a href="/dashboard" class="px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest text-slate-600 bg-white border border-slate-200 hover:text-[#c00000] hover:-translate-y-0.5 hover:shadow-md active:translate-y-0 transition-all duration-300 flex items-center gap-2 group italic">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 group-hover:-translate-x-1 transition-transform duration-300"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                    Back
                </a>
            </div>
        </div>

        <!-- Filter Configuration -->
        <div id="custodianFilterSection" class="hidden bg-white rounded-[2.5rem] shadow-xl border border-slate-100 p-8 mb-8 relative z-50 animate-fade transition-all duration-300 origin-top">
            <div class="flex flex-col gap-8 relative z-10">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <label class="text-[10px] font-black text-slate-900 uppercase tracking-widest italic flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                                Status
                            </label>
                        </div>
                        <div id="statusChipContainer" class="filter-container">
                            {{-- Chips injected via JS --}}
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <label class="text-[10px] font-black text-slate-900 uppercase tracking-widest italic flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                                Clearance
                            </label>
                        </div>
                        <div id="clearanceChipContainer" class="filter-container">
                            <div class="filter-chip" data-value="has_assets" onclick="toggleChip(this, 'clearanceChipContainer', 'active-red')">Has Assets</div>
                            <div class="filter-chip" data-value="cleared" onclick="toggleChip(this, 'clearanceChipContainer', 'active-red')">Cleared (No Assets)</div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <label class="text-[10px] font-black text-slate-900 uppercase tracking-widest italic flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                                Position
                            </label>
                        </div>
                        <div id="positionChipContainer" class="filter-container">
                            {{-- Chips injected via JS --}}
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-8 flex justify-end items-center gap-8 relative z-10 pt-6 border-t border-slate-100/60">
                <button onclick="clearCustodianFilters()" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] hover:text-[#c00000] hover:-translate-y-0.5 transition-all duration-300 italic">Clear All Filters</button>
                <button onclick="custodianFetchData()" class="px-10 py-4 bg-gradient-to-r from-red-700 to-red-500 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:from-red-800 hover:to-red-600 transition-all duration-300 active:translate-y-0 shadow-lg shadow-red-500/30 italic transform hover:-translate-y-0.5 group flex items-center gap-2">
                    Apply Filter Configuration
                    <svg class="w-4 h-4 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </button>
            </div>
        </div>

        <div class="rounded-[2rem] border border-slate-200/60 shadow-xl shadow-slate-200/50 overflow-hidden flex flex-col animate-fade relative ring-1 ring-black/5">
            <div class="xls-scroll-wrap expanded">
                <table class="w-full border-collapse" style="min-width:1200px;">
                    <thead><tr>
                        <th class="xls-th w-10 text-center sticky left-0 z-30">#</th>
                        <th class="xls-th sticky left-[40px] z-30" style="min-width:250px">Full Name</th>
                        <th class="xls-th" style="min-width:150px">Employee ID</th>
                        <th class="xls-th" style="min-width:200px">Position</th>
                        <th class="xls-th" style="min-width:250px">Assigned Station</th>
                        <th class="xls-th text-center" style="min-width:120px">Assets</th>
                        <th class="xls-th text-right" style="min-width:180px">Portfolio Value</th>
                        <th class="xls-th" style="min-width:120px">Status</th>
                    </tr></thead>
                    <tbody id="custodianBody"></tbody>
                </table>
                
                {{-- Loading State --}}
                <div id="custodianLoading" class="absolute inset-0 bg-white/80 backdrop-blur-[4px] z-50 flex items-center justify-center hidden transition-all duration-300">
                    <div class="flex flex-col items-center gap-5 bg-white px-10 py-8 rounded-3xl shadow-2xl shadow-slate-200/50 border border-slate-100">
                        <div class="w-12 h-12 border-4 border-slate-100 border-t-red-600 rounded-full animate-spin"></div>
                        <p class="text-[10px] font-black text-slate-800 uppercase tracking-widest italic animate-pulse">Fetching Custodian Data...</p>
                    </div>
                </div>

                {{-- Empty State --}}
                <div id="custodianEmpty" class="absolute inset-0 flex items-center justify-center pointer-events-none transition-all duration-300 bg-white/50 backdrop-blur-[2px]">
                    <div class="inline-flex flex-col items-center gap-4 bg-slate-50/80 px-12 py-10 rounded-[2.5rem] border border-dashed border-slate-200 shadow-sm">
                        <div class="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center text-red-400 shadow-inner">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                        </div>
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-[0.25em]">No custodians found — adjust filters</p>
                    </div>
                </div>
            </div>

            <div id="custodianTableFooter" class="px-6 py-4 border-t border-slate-100 flex items-center justify-between relative z-30 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                <div class="flex items-center gap-6">
                    <p id="custodianRowCountLabel" class="text-[9px] font-black text-slate-400 uppercase tracking-widest">0 Rows</p>
                    <div id="custodianPaginationControls" class="flex items-center gap-3 border-l border-slate-200 pl-6">
                        <button onclick="custodianPrevPage()" id="custodianPrevBtn" class="pg-btn">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
                            Prev
                        </button>
                        <div class="glass-indicator">
                            <span id="custodianCurrentPage" class="text-[10px] font-black text-red-600">1</span>
                            <span class="text-[10px] font-bold text-slate-500">/</span>
                            <span id="custodianTotalPages" class="text-[10px] font-black text-slate-500">1</span>
                        </div>
                        <button onclick="custodianNextPage()" id="custodianNextBtn" class="pg-btn">
                            Next
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <script>
        let custodianRowsData = [];
        let custodianCurrentPage = 1;
        const custodianRowsPerPage = 50;

        async function custodianFetchFilters() {
            try {
                const res = await fetch(`{{ route('api.employees.filters') }}`);
                const data = await res.json();
                
                const renderChips = (containerId, list, themeClass) => {
                    const container = document.getElementById(containerId);
                    container.innerHTML = '';
                    (list || []).forEach(item => {
                        if (!item) return;
                        const chip = document.createElement('div');
                        chip.className = `filter-chip`;
                        chip.textContent = item;
                        chip.dataset.value = item;
                        chip.onclick = () => {
                            const isActive = chip.classList.contains(themeClass);
                            document.querySelectorAll(`#${containerId} .filter-chip`).forEach(c => c.classList.remove(themeClass));
                            if (!isActive) chip.classList.add(themeClass);
                        };
                        container.appendChild(chip);
                    });
                };
                
                renderChips('statusChipContainer', data.statuses, 'active-red');
                renderChips('positionChipContainer', data.positions, 'active-red');
            } catch (e) { console.error('Failed to fetch custodian filters', e); }
        }

        function toggleChip(chip, containerId, themeClass) {
            const isActive = chip.classList.contains(themeClass);
            document.querySelectorAll(`#${containerId} .filter-chip`).forEach(c => c.classList.remove(themeClass));
            if (!isActive) chip.classList.add(themeClass);
        }

        let custodianSearchTimer;
        function custodianDebouncedSearch() {
            clearTimeout(custodianSearchTimer);
            custodianSearchTimer = setTimeout(() => custodianFetchData(), 400);
        }

        async function custodianFetchData() {
            const loading = document.getElementById('custodianLoading');
            loading.classList.remove('hidden');

            const getSelected = (containerId, themeClass) => {
                const active = document.querySelector(`#${containerId} .filter-chip.${themeClass}`);
                return active ? active.dataset.value : null;
            };

            const filters = {
                status: getSelected('statusChipContainer', 'active-red'),
                position: getSelected('positionChipContainer', 'active-red'),
                clearance: getSelected('clearanceChipContainer', 'active-red'),
                search: document.getElementById('custodianFilterSearch').value
            };
            try {
                const res = await fetch("{{ route('api.employees.preview') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ filters: filters })
                });
                const data = await res.json();
                custodianRowsData = data.rows || [];
                custodianCurrentPage = 1;
                renderCustodianTable();
            } catch (e) {
                console.error('Failed to fetch custodians', e);
            } finally {
                loading.classList.add('hidden');
            }
        }

        function clearCustodianFilters() {
            document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active-red'));
            document.getElementById('custodianFilterSearch').value = '';
            custodianCurrentPage = 1;
            custodianFetchData();
        }

        function renderCustodianTable() {
            const tbody = document.getElementById('custodianBody');
            tbody.innerHTML = '';
            if (custodianRowsData.length === 0) {
                document.getElementById('custodianEmpty').classList.remove('hidden');
                document.getElementById('custodianRowCountLabel').textContent = '0 Rows';
                return;
            }
            document.getElementById('custodianEmpty').classList.add('hidden');
            const start = (custodianCurrentPage - 1) * custodianRowsPerPage;
            const pageData = custodianRowsData.slice(start, start + custodianRowsPerPage);
            pageData.forEach((row, idx) => {
                const displayNum = start + idx + 1;
                const tr = document.createElement('tr');
                tr.className = 'xls-row group border-b border-slate-100';
                tr.onclick = () => window.location.href = '{{ url('/admin/custodians') }}/' + row.id;
                
                const cell = (val, extra = '') => `<td class="xls-td relative ${extra}"><span class="xls-const uppercase">${val || ''}</span></td>`;
                const costCell = (val, color) => `<td class="xls-td relative text-right"><span class="xls-const font-black italic ${color} justify-end">₱ ${Number(val || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></td>`;
                const statusBadge = (val) => {
                    const isAsst = (val || '').toLowerCase() === 'active';
                    return `<td class="xls-td relative"><span class="xls-const"><span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest ${isAsst ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'}">${val || 'N/A'}</span></span></td>`;
                };

                tr.innerHTML = `
                    <td class="xls-td text-center sticky left-0 w-10 z-20"><span class="text-[10px] font-black text-slate-500">${displayNum}</span></td>
                    <td class="xls-td relative sticky left-[40px] z-20">
                        <span class="xls-const font-bold text-slate-800 uppercase">${row.first_name} ${row.last_name}</span>
                    </td>
                    ${cell(row.employee_id)}
                    ${cell(row.position)}
                    ${cell(row.school_name)}
                    <td class="xls-td relative text-center"><span class="xls-const font-black text-red-600 justify-center">${row.total_assets}</span></td>
                    ${costCell(row.total_value, 'text-slate-900')}
                    ${statusBadge(row.status)}
                `;
                tbody.appendChild(tr);
            });
            const totalPages = Math.ceil(custodianRowsData.length / custodianRowsPerPage) || 1;
            document.getElementById('custodianRowCountLabel').textContent = custodianRowsData.length + " Custodians Found";
            
            document.getElementById('custodianCurrentPage').textContent = custodianCurrentPage;
            document.getElementById('custodianTotalPages').textContent = totalPages;
            document.getElementById('custodianPrevBtn').disabled = custodianCurrentPage === 1;
            document.getElementById('custodianNextBtn').disabled = custodianCurrentPage === totalPages;
        }

        function custodianPrevPage() { if (custodianCurrentPage > 1) { custodianCurrentPage--; renderCustodianTable(); } }
        function custodianNextPage() { const t = Math.ceil(custodianRowsData.length/custodianRowsPerPage); if (custodianCurrentPage < t) { custodianCurrentPage++; renderCustodianTable(); } }

        function toggleCustodianFilters() {
            const section = document.getElementById('custodianFilterSection');
            const btn = document.getElementById('toggleFilterBtn');
            const tableWrap = document.querySelector('.xls-scroll-wrap');
            if (section.classList.contains('hidden')) {
                section.classList.remove('hidden');
                tableWrap.classList.remove('expanded');
            } else {
                section.classList.add('hidden');
                tableWrap.classList.add('expanded');
            }
        }

        let createSchoolTomSelect = null;
        let createOfficeTomSelect = null;

        let createEmployeeModalLoaded = false;
        async function openCreateEmployeeModal() {
            const modal = document.getElementById('createEmployeeModal');
            modal.classList.remove('hidden');

            if (!createEmployeeModalLoaded) {
                try {
                    // Fetch Schools
                    const schoolRes = await fetch("{{ route('api.locations.search') }}?type=school");
                    const schools = await schoolRes.json();
                    const schoolSelect = document.getElementById('modalSchoolSelect');
                    schoolSelect.innerHTML = '<option value="">-- Select a School --</option>';
                    schools.forEach(s => {
                        const opt = document.createElement('option');
                        opt.value = s.id;
                        opt.textContent = `${s.name} (${s.entity_id})`;
                        schoolSelect.appendChild(opt);
                    });

                    // Fetch Offices
                    const officeRes = await fetch("{{ route('api.locations.search') }}?type=office");
                    const offices = await officeRes.json();
                    const officeSelect = document.getElementById('modalOfficeSelect');
                    officeSelect.innerHTML = '<option value="">-- Select an Office --</option>';
                    offices.forEach(o => {
                        const opt = document.createElement('option');
                        opt.value = o.id;
                        opt.textContent = `${o.name} (${o.entity_id})`;
                        officeSelect.appendChild(opt);
                    });

                    createSchoolTomSelect = new TomSelect('#modalSchoolSelect', { 
                        create: false, 
                        sortField: { field: "text", direction: "asc" },
                        onChange: function(value) {
                            if (value && value !== '') {
                                createOfficeTomSelect.disable();
                            } else {
                                createOfficeTomSelect.enable();
                            }
                        }
                    });
                    createOfficeTomSelect = new TomSelect('#modalOfficeSelect', { 
                        create: false, 
                        sortField: { field: "text", direction: "asc" },
                        onChange: function(value) {
                            if (value && value !== '') {
                                createSchoolTomSelect.disable();
                            } else {
                                createSchoolTomSelect.enable();
                            }
                        }
                    });

                    createEmployeeModalLoaded = true;
                } catch (e) {
                    console.error('Failed to load stations', e);
                }
            }
        }

        function closeCreateEmployeeModal() {
            document.getElementById('createEmployeeModal').classList.add('hidden');
        }

        function toggleStationTypeFields(type) {
            // (kept for backwards compatibility if needed)
        }

        document.addEventListener('DOMContentLoaded', () => {
            custodianFetchFilters();
            custodianFetchData();
        });
    </script>

    <!-- Create Employee Modal -->
    <div id="createEmployeeModal" class="fixed inset-0 z-[100] flex items-center justify-center hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeCreateEmployeeModal()"></div>
        <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] shadow-2xl border border-slate-100 dark:border-slate-700 w-full max-w-xl p-8 relative z-10 animate-fade mx-4">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-2xl font-black text-slate-800 dark:text-white uppercase tracking-tight">Register Employee</h3>
                    <p class="text-slate-500 text-[11px] font-bold uppercase tracking-widest mt-1">Add new custodian or staff member</p>
                </div>
                <button onclick="closeCreateEmployeeModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('admin.employees.store') }}" method="POST" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">First Name</label>
                        <input type="text" name="first_name" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-sm font-semibold dark:bg-slate-900 dark:border-slate-700 dark:text-white" placeholder="John">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Middle Name</label>
                        <input type="text" name="middle_name" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-sm font-semibold dark:bg-slate-900 dark:border-slate-700 dark:text-white" placeholder="Doe">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Last Name</label>
                        <input type="text" name="last_name" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-sm font-semibold dark:bg-slate-900 dark:border-slate-700 dark:text-white" placeholder="Smith">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Employee ID</label>
                        <input type="text" name="employee_id" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-sm font-semibold dark:bg-slate-900 dark:border-slate-700 dark:text-white" placeholder="EMP-2026-0001">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Position</label>
                        <input type="text" name="position" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-sm font-semibold dark:bg-slate-900 dark:border-slate-700 dark:text-white" placeholder="Teacher I / Admin Assistant">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Status</label>
                        <select name="status" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-sm font-semibold dark:bg-slate-900 dark:border-slate-700 dark:text-white">
                            <option value="Active" selected>Active</option>
                            <option value="Inactive">Inactive (Resigned/Retired)</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-3 p-4 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest">Station Assignment</label>
                    
                    <!-- School Selection -->
                    <div id="schoolAssignmentField" class="space-y-1">
                        <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Select School</label>
                        <select name="school_id" id="modalSchoolSelect" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-sm font-semibold dark:bg-slate-800 dark:border-slate-700 dark:text-white">
                            <option value="">-- Select a School --</option>
                        </select>
                    </div>

                    <!-- Office Selection -->
                    <div id="officeAssignmentField" class="space-y-1 mt-3">
                        <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Select Office</label>
                        <select name="office_id" id="modalOfficeSelect" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-sm font-semibold dark:bg-slate-800 dark:border-slate-700 dark:text-white">
                            <option value="">-- Select an Office --</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" onclick="closeCreateEmployeeModal()" class="px-6 py-3 border border-slate-200 text-slate-500 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-900 transition-all">Cancel</button>
                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-red-700 to-red-500 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:from-red-800 hover:to-red-600 transition-all shadow-md shadow-red-500/20">Register</button>
                </div>
            </form>
        </div>
    </div>



    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: "{{ session('success') }}",
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            });
        </script>
    @endif
    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: "{{ $errors->first() }}",
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 4000
                });
            });
        </script>
    @endif
</body>
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<style>
    /* TomSelect Custom Overrides for standard styling */
    .ts-wrapper.single .ts-control {
        border-radius: 0.75rem;
        padding: 0.75rem 1rem;
        font-weight: 600;
        font-size: 0.875rem;
        border-color: #e2e8f0;
        background-color: #f8fafc;
        color: #1e293b;
    }
    .ts-dropdown { 
        border-radius: 0.75rem; 
        overflow: hidden; 
        font-size: 0.875rem; 
        font-weight: 600; 
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); 
        border-color: #e2e8f0;
    }
    .ts-dropdown .option { padding: 0.5rem 1rem; }
    
    html.dark .ts-wrapper.single .ts-control {
        background-color: #0f172a;
        border-color: #334155;
        color: #ffffff;
    }
    html.dark .ts-dropdown {
        background-color: #1e293b;
        border-color: #334155;
        color: #e2e8f0;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5); 
    }
    html.dark .ts-dropdown .option:hover, html.dark .ts-dropdown .option.active {
        background-color: #334155;
        color: #ffffff;
    }
    html.dark .ts-control > input {
        color: #ffffff;
    }
</style>
</html>
