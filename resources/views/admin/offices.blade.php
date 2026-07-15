<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Office Registry | DepEd Zamboanga City</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* === CSS Custom Properties for Light/Dark Theming === */
        :root {
            --bg-page:        #f8fafc;
            --bg-card:        #ffffff;
            --bg-secondary:   #f8fafc;
            --border-primary: #e2e8f0;
            --border-strong:  #cbd5e1;
            --text-primary:   #0f172a;
            --text-secondary: #1e293b;
            --text-muted:     #64748b;
            --text-faint:     #94a3b8;
            --scrollbar-thumb: #cbd5e1;
            --row-hover-bg:   rgba(192,0,0,0.03);
            --row-hover-border: #c00000;
            --row-active-bg:  rgba(192,0,0,0.08);
        }
        html.dark {
            --bg-page:        #0f172a;
            --bg-card:        #1e293b;
            --bg-secondary:   #0f172a;
            --border-primary: #334155;
            --border-strong:  #475569;
            --text-primary:   #f8fafc;
            --text-secondary: #e2e8f0;
            --text-muted:     #94a3b8;
            --text-faint:     #64748b;
            --scrollbar-thumb: #475569;
            --row-hover-bg:   rgba(192,0,0,0.07);
            --row-hover-border: #ef4444;
            --row-active-bg:  rgba(192,0,0,0.14);
        }

        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-page); color: var(--text-primary); }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade { animation: fadeIn 0.4s ease-out forwards; }
        .custom-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: var(--scrollbar-thumb); border-radius: 10px; border: 2px solid transparent; background-clip: padding-box; }
        .custom-scroll::-webkit-scrollbar-thumb:hover { background: #f87171; border: 2px solid transparent; background-clip: padding-box; }

        .back-btn-cool { background: var(--bg-card); border: 1px solid var(--border-primary); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .xls-th { padding: 14px 16px; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-muted); white-space: nowrap; border-right: 1px solid var(--border-primary); border-bottom: 2px solid var(--border-strong); background: var(--bg-secondary); position: sticky; top: 0; z-index: 20; box-shadow: 0 1px 2px rgba(0,0,0,0.02); }
        .xls-td { height: 52px; border-right: 1px solid var(--border-primary); border-bottom: 1px solid var(--border-primary); vertical-align: middle; padding: 0; background: var(--bg-card); transition: all 0.3s ease; color: var(--text-secondary); }
        .xls-row { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; position: relative; }
        .xls-row:hover { transform: translateX(4px); z-index: 10; }
        .xls-row:hover .xls-td { background-color: var(--row-hover-bg) !important; border-bottom-color: var(--row-hover-border); }
        .xls-row:hover .xls-td:first-child { box-shadow: inset 4px 0 0 var(--row-hover-border); }
        .xls-row:active { transform: scale(0.995); transition: all 0.1s; }
        .xls-row:active .xls-td { background-color: var(--row-active-bg) !important; }
        .xls-const { display: flex; align-items: center; padding: 0 16px; height: 100%; font-size: 11.5px; font-weight: 700; color: inherit; white-space: nowrap; }
        .xls-scroll-wrap { --col1-width: 40px; width: 100%; max-width: 100%; min-width: 0; position: relative; overflow-x: auto; overflow-y: auto; height: calc(100vh - 350px); min-height: 400px; background: var(--bg-card); flex-grow: 1; transition: height 0.4s cubic-bezier(0.4, 0, 0.2, 1); border-top: 1px solid var(--border-primary); }
        .xls-scroll-wrap.expanded { height: calc(100vh - 250px); }

        /* Adaptive Tailwind overrides */
        .bg-white  { background-color: var(--bg-card)      !important; }
        .bg-slate-50 { background-color: var(--bg-secondary) !important; }
        .border-slate-200 { border-color: var(--border-primary) !important; }
        .border-slate-100 { border-color: var(--border-primary) !important; }
        .text-slate-900 { color: var(--text-primary)   !important; }
        .text-slate-800 { color: var(--text-secondary) !important; }
        .text-slate-700 { color: var(--text-muted)     !important; }
        .text-slate-600 { color: var(--text-muted)     !important; }
        .text-slate-500, .text-slate-400 { color: var(--text-faint) !important; }

        .pg-btn {
            padding: 8px 18px;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            border-radius: 9999px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid var(--border-primary);
            background: var(--bg-card);
            color: var(--text-muted);
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
            background: var(--bg-secondary);
        }

        /* Glass Indicator Box */
        .glass-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: var(--bg-card);
            border: 1px solid var(--border-primary);
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        /* Filter Dropdowns */
        .filter-select-wrap { display: flex; flex-direction: column; gap: 6px; }
        .filter-select-label {
            font-size: 9px; font-weight: 900; text-transform: uppercase;
            letter-spacing: 0.15em; color: #94a3b8;
            display: flex; align-items: center; gap: 6px;
        }
        .filter-select {
            width: 100%; padding: 10px 14px; font-size: 11px; font-weight: 700;
            border: 1.5px solid var(--border-primary); border-radius: 12px;
            background: var(--bg-secondary); color: var(--text-secondary);
            appearance: none; -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='2.5' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19.5 8.25l-7.5 7.5-7.5-7.5'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 12px center;
            background-size: 14px; padding-right: 36px; cursor: pointer;
            transition: border-color 0.2s, box-shadow 0.2s; outline: none;
        }
        .filter-select:focus { border-color: #c00000; box-shadow: 0 0 0 3px rgba(192,0,0,0.08); }
        .filter-select:hover { border-color: var(--border-strong); }

        /* Filter panel background */
        #officeFilterSection {
            background: var(--bg-card);
            border-color: var(--border-primary);
        }
        #officeTableFooter {
            background: var(--bg-card);
            border-color: var(--border-primary);
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex text-slate-900 overflow-x-hidden selection:bg-red-100 selection:text-red-900 relative">
    <div class="absolute inset-0 z-[-1] opacity-[0.03] pointer-events-none" style="background-image: radial-gradient(#000 1px, transparent 1px); background-size: 24px 24px;"></div>

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll relative">
    <div class="w-full mx-auto p-6 lg:p-10 min-h-screen flex flex-col relative z-10">

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-10 px-2 animate-fade">
            <div class="shrink-0">
                <h2 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-red-700 to-red-500 uppercase italic leading-tight drop-shadow-sm tracking-tight pb-1 pr-4">Office Registry</h2>
                <p class="text-slate-500 text-[11px] font-bold uppercase tracking-[0.25em] mt-3 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse shadow-[0_0_8px_rgba(239,68,68,0.6)]"></span>
                    Administrative & Division Units
                </p>
            </div>

            {{-- Main Search --}}
            <div class="flex-grow max-w-2xl relative">
                <div class="relative group">
                    <input type="text" id="officeFilterSearch" oninput="officeDebouncedSearch()" placeholder="SEARCH OFFICE NAME..." autocomplete="off" class="w-full bg-white border-2 border-slate-100 rounded-2xl px-6 py-4 text-xs font-black uppercase tracking-widest focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-700 shadow-sm pr-12 group-hover:border-slate-200">
                    <div class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 group-hover:text-red-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 shrink-0">
                @if(auth()->user()->role === 'super_admin' || auth()->user()->role === 'admin')
                <button onclick="openAddOfficeModal()" class="px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest text-white bg-gradient-to-r from-red-700 to-red-500 hover:from-red-800 hover:to-red-600 hover:-translate-y-0.5 hover:shadow-md hover:shadow-red-200 active:translate-y-0 transition-all duration-300 flex items-center gap-2 group italic">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4 group-hover:scale-110 transition-transform"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Add Office
                </button>
                @endif
                <button onclick="toggleOfficeFilters()" id="toggleFilterBtn" class="px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest text-slate-600 bg-white border border-slate-200 hover:text-[#c00000] hover:-translate-y-0.5 hover:shadow-md active:translate-y-0 transition-all duration-300 flex items-center gap-2 group italic">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 group-hover:rotate-12 transition-transform duration-300"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg>
                    Filters
                </button>
                <a href="/dashboard" class="px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest text-slate-600 bg-white border border-slate-200 hover:text-[#c00000] hover:-translate-y-0.5 hover:shadow-md active:translate-y-0 transition-all duration-300 flex items-center gap-2 group italic">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 group-hover:-translate-x-1 transition-transform duration-300"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                    Back
                </a>
            </div>
        </div>

        
        <div id="officeFilterSection" class="hidden bg-white rounded-[2.5rem] shadow-xl border border-slate-100 p-8 mb-8 relative z-50 animate-fade transition-all duration-300 origin-top">
            <div class="flex items-center gap-2 mb-6">
                <span class="w-2 h-2 rounded-full bg-red-600"></span>
                <span class="text-[10px] font-black text-slate-900 uppercase tracking-widest italic">Filter Configuration</span>
            </div>
            <div class="flex gap-5 relative z-10" style="max-width:480px;">

                {{-- Costing --}}
                <div class="filter-select-wrap flex-1">
                    <label class="filter-select-label">
                        <svg class="w-3 h-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 7.5L7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5"/></svg>
                        Costing
                    </label>
                    <select id="filterCosting" class="filter-select">
                        <option value="">Any Order</option>
                        <option value="high_low">High to Low</option>
                        <option value="low_high">Low to High</option>
                    </select>
                </div>

                {{-- Sorting --}}
                <div class="filter-select-wrap flex-1">
                    <label class="filter-select-label">
                        <svg class="w-3 h-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 4.5h14.25M3 9h9.75M3 13.5h5.25m5.25-.75L17.25 9m0 0L21 12.75M17.25 9v12"/></svg>
                        Sorting
                    </label>
                    <select id="filterSort" class="filter-select">
                        <option value="az">A &rarr; Z</option>
                        <option value="za">Z &rarr; A</option>
                    </select>
                </div>

            </div>

            <div class="mt-6 flex justify-end items-center gap-8 relative z-10 pt-6 border-t border-slate-100/60">
                <button onclick="clearOfficeFilters()" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] hover:text-[#c00000] hover:-translate-y-0.5 transition-all duration-300 italic">Clear All Filters</button>
                <button onclick="officeFetchData()" class="px-10 py-4 bg-gradient-to-r from-red-700 to-red-500 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:from-red-800 hover:to-red-600 transition-all duration-300 active:translate-y-0 shadow-lg shadow-red-500/30 italic transform hover:-translate-y-0.5 group flex items-center gap-2">
                    Apply Filter Configuration
                    <svg class="w-4 h-4 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </button>
            </div>
        </div>

        <div class="w-full max-w-full rounded-[2rem] border border-slate-200/60 shadow-xl shadow-slate-200/50 overflow-hidden flex flex-col animate-fade relative ring-1 ring-black/5">
            <div class="xls-scroll-wrap expanded">
                <table class="w-full border-collapse" style="min-width:1200px;">
                    <thead><tr>
                        <th class="xls-th w-10 text-center sticky top-0 left-0 z-40 bg-[var(--bg-secondary)]">#</th>
                        <th class="xls-th sticky top-0 z-40 bg-[var(--bg-secondary)]" style="left: var(--col1-width); min-width:300px">Office Name</th>
                        <th class="xls-th sticky top-0 z-30 bg-[var(--bg-secondary)]" style="min-width:180px">Type</th>
                        <th class="xls-th sticky top-0 z-30 bg-[var(--bg-secondary)]" style="min-width:180px">Location</th>
                        <th class="xls-th sticky top-0 z-30 bg-[var(--bg-secondary)] text-right" style="min-width:150px">Total Bldg Cost</th>
                        <th class="xls-th sticky top-0 z-30 bg-[var(--bg-secondary)] text-right" style="min-width:150px">Total PPE Cost</th>
                        <th class="xls-th sticky top-0 z-30 bg-[var(--bg-secondary)] text-right" style="min-width:150px">Total Semi-PPE Cost</th>
                    </tr></thead>
                    <tbody id="officeBody"></tbody>
                </table>
                
                {{-- Loading State --}}
                <div id="officeLoading" class="absolute inset-0 bg-white/80 backdrop-blur-[4px] z-50 flex items-center justify-center hidden transition-all duration-300">
                    <div class="flex flex-col items-center gap-5 bg-white px-10 py-8 rounded-3xl shadow-2xl shadow-slate-200/50 border border-slate-100">
                        <div class="w-12 h-12 border-4 border-slate-100 border-t-red-600 rounded-full animate-spin"></div>
                        <p class="text-[10px] font-black text-slate-800 uppercase tracking-widest italic animate-pulse">Fetching Office Data...</p>
                    </div>
                </div>

                {{-- Empty State --}}
                <div id="officeEmpty" class="absolute inset-0 flex items-center justify-center pointer-events-none transition-all duration-300 bg-white/50 backdrop-blur-[2px]">
                    <div class="inline-flex flex-col items-center gap-4 bg-slate-50/80 px-12 py-10 rounded-[2.5rem] border border-dashed border-slate-200 shadow-sm">
                        <div class="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center text-red-400 shadow-inner">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15" /></svg>
                        </div>
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-[0.25em]">No offices found — adjust filters</p>
                    </div>
                </div>
            </div>

            <div id="officeTableFooter" class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 relative z-30 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] bg-white">
                <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6 w-full sm:w-auto">
                    <p id="officeRowCountLabel" class="text-[9px] font-black text-slate-400 uppercase tracking-widest text-center sm:text-left">0 Rows</p>
                    <div id="officePaginationControls" class="flex items-center justify-center gap-3 border-t sm:border-t-0 sm:border-l border-slate-200 pt-4 sm:pt-0 sm:pl-6 w-full sm:w-auto">
                        <button onclick="officePrevPage()" id="officePrevBtn" class="pg-btn">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
                            Prev
                        </button>
                        <div class="glass-indicator">
                            <span id="officeCurrentPage" class="text-[10px] font-black text-red-600">1</span>
                            <span class="text-[10px] font-bold text-slate-500">/</span>
                            <span id="officeTotalPages" class="text-[10px] font-black text-slate-500">1</span>
                        </div>
                        <button onclick="officeNextPage()" id="officeNextBtn" class="pg-btn">
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
        let officeRowsData = [];
        let officeCurrentPage = 1;
        const officeRowsPerPage = 50;

        async function officeFetchFilters() {
            // No dynamic selects to populate — dropdowns are static
        }

        let officeSearchTimer;
        function officeDebouncedSearch() {
            clearTimeout(officeSearchTimer);
            officeSearchTimer = setTimeout(() => officeFetchData(), 400);
        }

        async function officeFetchData() {
            const loading = document.getElementById('officeLoading');
            loading.classList.remove('hidden');

            const val = id => document.getElementById(id)?.value || null;

            const filters = {
                costing: val('filterCosting'),
                sort:    val('filterSort') || 'az',
                search:  document.getElementById('officeFilterSearch').value || null,
            };
            try {
                const res = await fetch("{{ route('api.offices.preview') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ filters: filters })
                });
                const data = await res.json();
                officeRowsData = data.rows || [];
                officeCurrentPage = 1;
                renderOfficeTable();
            } catch (e) {
                console.error('Failed to fetch offices', e);
            } finally {
                loading.classList.add('hidden');
            }
        }

        function clearOfficeFilters() {
            const costEl = document.getElementById('filterCosting');
            if (costEl) costEl.value = '';
            const sortEl = document.getElementById('filterSort');
            if (sortEl) sortEl.value = 'az';
            document.getElementById('officeFilterSearch').value = '';
            officeCurrentPage = 1;
            officeFetchData();
        }

        function renderOfficeTable() {
            const tbody = document.getElementById('officeBody');
            tbody.innerHTML = '';
            if (officeRowsData.length === 0) {
                document.getElementById('officeEmpty').classList.remove('hidden');
                document.getElementById('officeRowCountLabel').textContent = '0 Rows';
                return;
            }
            document.getElementById('officeEmpty').classList.add('hidden');
            const start = (officeCurrentPage - 1) * officeRowsPerPage;
            const pageData = officeRowsData.slice(start, start + officeRowsPerPage);
            pageData.forEach((row, idx) => {
                const displayNum = start + idx + 1;
                const tr = document.createElement('tr');
                tr.className = 'xls-row group border-b border-slate-100 cursor-pointer';
                tr.onclick = () => window.location.href = '/offices/' + row.id;
                
                const cell = (val, extra = '') => `<td class="xls-td relative ${extra}"><span class="xls-const uppercase">${val || ''}</span></td>`;
                const costCell = (val, color) => `<td class="xls-td relative text-right"><span class="xls-const font-black italic ${color} justify-end">₱ ${Number(val || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></td>`;

                tr.innerHTML = `
                    <td class="xls-td text-center sticky left-0 w-10 z-20 bg-[var(--bg-card)]"><span class="text-[10px] font-black text-slate-500">${displayNum}</span></td>
                    <td class="xls-td relative sticky z-20 bg-[var(--bg-card)]" style="left: var(--col1-width);">
                        <span class="xls-const font-bold text-slate-800 uppercase">${row.name || ''}</span>
                    </td>
                    ${cell(row.type)}
                    ${cell(row.location)}
                    ${costCell(row.total_bldg_cost, 'text-emerald-600')}
                    ${costCell(row.total_ppe_cost, 'text-blue-600')}
                    ${costCell(row.total_semi_ppe_cost, 'text-amber-600')}
                `;
                tbody.appendChild(tr);
            });
            const totalPages = Math.ceil(officeRowsData.length / officeRowsPerPage) || 1;
            document.getElementById('officeRowCountLabel').textContent = officeRowsData.length + " Offices Found";
            
            document.getElementById('officeCurrentPage').textContent = officeCurrentPage;
            document.getElementById('officeTotalPages').textContent = totalPages;
            document.getElementById('officePrevBtn').disabled = officeCurrentPage === 1;
            document.getElementById('officeNextBtn').disabled = officeCurrentPage === totalPages;
        }

        function officePrevPage() { if (officeCurrentPage > 1) { officeCurrentPage--; renderOfficeTable(); } }
        function officeNextPage() { const t = Math.ceil(officeRowsData.length/officeRowsPerPage); if (officeCurrentPage < t) { officeCurrentPage++; renderOfficeTable(); } }

        function toggleOfficeFilters() {
            const section = document.getElementById('officeFilterSection');
            const btn = document.getElementById('toggleFilterBtn');
            const tableWrap = document.querySelector('.xls-scroll-wrap');
            if (section.classList.contains('hidden')) {
                section.classList.remove('hidden');
                tableWrap.classList.remove('expanded');
                btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg> Hide Filters`;
            } else {
                section.classList.add('hidden');
                tableWrap.classList.add('expanded');
                btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 group-hover:rotate-12 transition-transform duration-300"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg> Filters`;
            }
        }

        function openAddOfficeModal() {
            document.getElementById('addOfficeModal').classList.remove('hidden');
        }

        function closeAddOfficeModal() {
            document.getElementById('addOfficeModal').classList.add('hidden');
        }

        document.addEventListener('DOMContentLoaded', () => {
            officeFetchFilters();
            officeFetchData();
        });
    </script>

    
    <div id="addOfficeModal" class="fixed inset-0 z-[100] flex items-center justify-center hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeAddOfficeModal()"></div>
        <form action="{{ route('admin.offices.store') }}" method="POST" class="bg-white dark:bg-[#1e293b] rounded-3xl shadow-2xl w-full max-w-xl mx-4 relative z-10 flex flex-col overflow-hidden border border-slate-100 dark:border-slate-800">
            @csrf
            
            <div class="bg-slate-50 dark:bg-[#0f172a] border-b border-slate-100 dark:border-slate-800 px-6 py-5 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-red-100 text-red-600 rounded-2xl flex items-center justify-center shadow-inner">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-800 dark:text-slate-200 uppercase tracking-[0.1em]">Add New Office</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase mt-0.5">Register a new administrative or division unit</p>
                    </div>
                </div>
                <button type="button" onclick="closeAddOfficeModal()" class="text-slate-400 hover:text-slate-600 hover:bg-slate-200/50 p-2.5 rounded-full transition-colors active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            
            <div class="p-6 space-y-6">
                <div>
                    <label class="block text-[10px] font-black text-slate-600 dark:text-slate-400 uppercase tracking-widest mb-2 ml-1">Office Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="ENTER OFFICE NAME..." class="w-full bg-white dark:bg-[#0f172a] border-2 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-xs font-black text-slate-700 dark:text-slate-300 uppercase focus:border-red-500 focus:ring-4 focus:ring-red-500/10 outline-none transition-all shadow-sm hover:border-slate-300">
                </div>

                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label class="block text-[10px] font-black text-slate-600 dark:text-slate-400 uppercase tracking-widest mb-2 ml-1">Office Code / ID <span class="text-red-500">*</span></label>
                        <input type="text" name="office_id" value="095" required class="w-full bg-white dark:bg-[#0f172a] border-2 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-xs font-black text-slate-700 dark:text-slate-300 uppercase focus:border-red-500 focus:ring-4 focus:ring-red-500/10 outline-none transition-all shadow-sm hover:border-slate-300">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-600 dark:text-slate-400 uppercase tracking-widest mb-2 ml-1">Office Type <span class="text-red-500">*</span></label>
                        <input type="text" name="type" value="office" required class="w-full bg-white dark:bg-[#0f172a] border-2 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-xs font-black text-slate-700 dark:text-slate-300 uppercase focus:border-red-500 focus:ring-4 focus:ring-red-500/10 outline-none transition-all shadow-sm hover:border-slate-300">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-600 dark:text-slate-400 uppercase tracking-widest mb-2 ml-1">Location <span class="text-red-500">*</span></label>
                    <input type="text" name="location" value="Deped SDO - Baliwasan Chico rd., Zamboanga City" required class="w-full bg-white dark:bg-[#0f172a] border-2 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-xs font-black text-slate-700 dark:text-slate-300 uppercase focus:border-red-500 focus:ring-4 focus:ring-red-500/10 outline-none transition-all shadow-sm hover:border-slate-300">
                </div>
            </div>

            
            <div class="bg-slate-50 dark:bg-[#0f172a] border-t border-slate-100 dark:border-slate-800 px-6 py-4 flex justify-end gap-3">
                <button type="button" onclick="closeAddOfficeModal()" class="px-5 py-2.5 bg-white dark:bg-[#1e293b] border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-black text-slate-500 uppercase tracking-widest hover:bg-slate-100 hover:text-slate-700 transition-all duration-300">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-red-700 to-red-500 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:from-red-800 hover:to-red-600 transition-all duration-300 shadow-md shadow-red-500/20 flex items-center gap-2">
                    Register Office
                </button>
            </div>
        </form>
    </div>
</body>
</html>
