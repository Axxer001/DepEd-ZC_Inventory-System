<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Registry | DepEd Zamboanga City</title>
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
        .back-btn-cool { background: white; border: 1px solid #e2e8f0; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .xls-th { padding: 14px 16px; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; color: #475569; white-space: nowrap; border-right: 1px solid #e2e8f0; border-bottom: 2px solid #cbd5e1; background: #f8fafc; position: sticky; top: 0; z-index: 20; box-shadow: 0 1px 2px rgba(0,0,0,0.02); }
        .xls-td { height: 52px; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; vertical-align: middle; padding: 0; background: white; transition: all 0.3s ease; }
        .xls-row { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; position: relative; }
        .xls-row:hover { transform: translateX(4px); z-index: 10; }
        .xls-row:hover .xls-td { background-color: rgba(192, 0, 0, 0.03) !important; border-bottom-color: #c00000; }
        .xls-row:hover .xls-td:first-child { box-shadow: inset 4px 0 0 #c00000; }
        .xls-row:active { transform: scale(0.995); transition: all 0.1s; }
        .xls-row:active .xls-td { background-color: rgba(192, 0, 0, 0.08) !important; }
        .xls-const { display: flex; align-items: center; padding: 0 16px; height: 100%; font-size: 11.5px; font-weight: 700; color: inherit; white-space: nowrap; }
        .xls-scroll-wrap { position: relative; overflow-x: auto; overflow-y: auto; height: calc(100vh - 450px); min-height: 400px; background: white; flex-grow: 1; transition: height 0.4s cubic-bezier(0.4, 0, 0.2, 1); border-top: 1px solid #e2e8f0; }
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
        .filter-chip.active-blue { background: #2563eb; border-color: #2563eb; color: white; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2); }
        .filter-chip.active-red { background: #c00000; border-color: #c00000; color: white; box-shadow: 0 4px 12px rgba(192, 0, 0, 0.2); }
        
        .filter-chip:hover:not(.active-red):not(.active-blue) { border-color: #c00000; color: #c00000; background: rgba(192, 0, 0, 0.05); }
        .filter-chip.hover-blue:hover:not(.active-blue) { border-color: #2563eb; color: #2563eb; background: rgba(37, 99, 235, 0.05); }

        .filter-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            max-height: 200px;
            overflow-y: auto;
            padding: 4px;
        }
        .filter-container::-webkit-scrollbar { width: 4px; }
        .filter-container::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

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
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex text-slate-900 overflow-x-hidden selection:bg-red-100 selection:text-red-900 relative">
    <div class="absolute inset-0 z-[-1] opacity-[0.03] pointer-events-none" style="background-image: radial-gradient(#000 1px, transparent 1px); background-size: 24px 24px;"></div>

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll relative">
    <div class="w-full mx-auto p-6 lg:p-10 min-h-screen flex flex-col relative z-10">

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-10 px-2 animate-fade">
            <div class="shrink-0">
                <h2 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-red-700 to-red-500 uppercase italic leading-none drop-shadow-sm tracking-tight">School Registry</h2>
                <p class="text-slate-500 text-[11px] font-bold uppercase tracking-[0.25em] mt-3 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse shadow-[0_0_8px_rgba(239,68,68,0.6)]"></span>
                    Zamboanga City Division Master List
                </p>
            </div>

            {{-- Main Search --}}
            <div class="flex-grow max-w-2xl relative">
                <div class="relative group">
                    <input type="text" id="schoolFilterSearch" placeholder="SEARCH SCHOOL NAME OR ID..." autocomplete="off" class="w-full bg-white border-2 border-slate-100 rounded-2xl px-6 py-4 text-xs font-black uppercase tracking-widest focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-700 shadow-sm pr-12 group-hover:border-slate-200">
                    <div class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 group-hover:text-red-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 shrink-0">
                <button onclick="toggleSchoolFilters()" id="toggleFilterBtn" class="px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest text-slate-600 bg-white border border-slate-200 hover:text-[#c00000] hover:-translate-y-0.5 hover:shadow-md active:translate-y-0 transition-all duration-300 flex items-center gap-2 group italic">
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
        <div id="schoolFilterSection" class="hidden bg-white rounded-[2.5rem] shadow-xl border border-slate-100 p-8 mb-8 relative z-50 animate-fade transition-all duration-300 origin-top">
            <div class="flex flex-col gap-8 relative z-10">
                
                {{-- Quadrant Selection --}}
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <label class="text-[10px] font-black text-slate-900 uppercase tracking-widest italic flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                            Quadrants
                        </label>
                        <span id="quadrantCount" class="text-[9px] font-black text-slate-400 uppercase tracking-widest">0 SELECTED</span>
                    </div>
                    <div id="quadrantChipContainer" class="filter-container">
                        {{-- Chips injected via JS --}}
                    </div>
                </div>

                {{-- District Selection --}}
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <label class="text-[10px] font-black text-slate-900 uppercase tracking-widest italic flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                            Districts
                        </label>
                        <span id="districtCount" class="text-[9px] font-black text-slate-400 uppercase tracking-widest">0 SELECTED</span>
                    </div>
                    <div id="districtChipContainer" class="filter-container">
                        {{-- Chips injected via JS --}}
                    </div>
                </div>



            </div>
            
            <div class="mt-8 flex justify-end items-center gap-8 relative z-10 pt-6 border-t border-slate-100/60">
                <button onclick="clearSchoolFilters()" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] hover:text-[#c00000] hover:-translate-y-0.5 transition-all duration-300 italic">Clear All Filters</button>
                <button onclick="schoolFetchData()" class="px-10 py-4 bg-gradient-to-r from-red-700 to-red-500 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:from-red-800 hover:to-red-600 transition-all duration-300 active:translate-y-0 shadow-lg shadow-red-500/30 italic transform hover:-translate-y-0.5 group flex items-center gap-2">
                    Apply Filter Configuration
                    <svg class="w-4 h-4 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </button>
            </div>
        </div>

        <div class="rounded-[2rem] border border-slate-200/60 shadow-xl shadow-slate-200/50 overflow-hidden flex flex-col animate-fade relative ring-1 ring-black/5">
            <div class="xls-scroll-wrap expanded">
                <table class="w-full border-collapse" style="min-width:1200px;">
                    <thead><tr>
                        <th class="xls-th w-10 text-center sticky left-0 z-30">#</th>
                        <th class="xls-th sticky left-[40px] z-30" style="min-width:120px">School ID</th>
                        <th class="xls-th" style="min-width:300px">Institutional Name</th>
                        <th class="xls-th" style="min-width:180px">District</th>
                        <th class="xls-th" style="min-width:180px">Quadrant</th>
                        <th class="xls-th" style="min-width:150px">Total Bldg Cost</th>
                        <th class="xls-th" style="min-width:150px">Total PPE Cost</th>
                        <th class="xls-th" style="min-width:150px">Total Semi-PPE Cost</th>
                    </tr></thead>
                    <tbody id="schoolBody"></tbody>
                </table>
                
                {{-- Loading State --}}
                <div id="schoolLoading" class="absolute inset-0 bg-white/80 backdrop-blur-[4px] z-50 flex items-center justify-center hidden transition-all duration-300">
                    <div class="flex flex-col items-center gap-5 bg-white px-10 py-8 rounded-3xl shadow-2xl shadow-slate-200/50 border border-slate-100">
                        <div class="w-12 h-12 border-4 border-slate-100 border-t-red-600 rounded-full animate-spin"></div>
                        <p class="text-[10px] font-black text-slate-800 uppercase tracking-widest italic animate-pulse">Fetching School Data...</p>
                    </div>
                </div>

                {{-- Empty State --}}
                <div id="schoolEmpty" class="absolute inset-0 flex items-center justify-center pointer-events-none transition-all duration-300 bg-white/50 backdrop-blur-[2px]">
                    <div class="inline-flex flex-col items-center gap-4 bg-slate-50/80 px-12 py-10 rounded-[2.5rem] border border-dashed border-slate-200 shadow-sm">
                        <div class="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center text-red-400 shadow-inner">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A4.833 4.833 0 0012 9a4.833 4.833 0 00-7.5 1.332V21m15 0h-15"/></svg>
                        </div>
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-[0.25em]">No schools found — adjust filters</p>
                    </div>
                </div>
            </div>

            <div id="schoolTableFooter" class="px-6 py-4 border-t border-slate-100 flex items-center justify-between relative z-30 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                <div class="flex items-center gap-6">
                    <p id="schoolRowCountLabel" class="text-[9px] font-black text-slate-400 uppercase tracking-widest">0 Rows</p>
                    <div id="schoolPaginationControls" class="flex items-center gap-3 border-l border-slate-200 pl-6">
                        <button onclick="schoolPrevPage()" id="schoolPrevBtn" class="pg-btn">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
                            Prev
                        </button>
                        <div class="glass-indicator">
                            <span id="schoolCurrentPage" class="text-[10px] font-black text-red-600">1</span>
                            <span class="text-[10px] font-bold text-slate-500">/</span>
                            <span id="schoolTotalPages" class="text-[10px] font-black text-slate-500">1</span>
                        </div>
                        <button onclick="schoolNextPage()" id="schoolNextBtn" class="pg-btn">
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
        let schoolRowsData = [];
        let schoolCurrentPage = 1;
        const schoolRowsPerPage = 50;

        let allSchoolList = [];
        let isSearchInit = false;

        async function schoolFetchFilters() {
            try {
                const res = await fetch(`{{ route('api.schools.filters') }}`);
                const data = await res.json();
                
                const renderChips = (containerId, list, countId, themeClass) => {
                    const container = document.getElementById(containerId);
                    container.innerHTML = '';
                    
                    (list || []).forEach(item => {
                        if (!item) return;
                        const chip = document.createElement('div');
                        chip.className = `filter-chip ${themeClass === 'active-blue' ? 'hover-blue' : ''}`;
                        chip.textContent = item;
                        chip.dataset.value = item;
                        chip.onclick = () => {
                            chip.classList.toggle(themeClass);
                            updateChipCount(countId, containerId, themeClass);
                        };
                        container.appendChild(chip);
                    });
                };
                
                renderChips('quadrantChipContainer', data.quadrants, 'quadrantCount', 'active-red');
                renderChips('districtChipContainer', data.districts, 'districtCount', 'active-blue');
                
                allSchoolList = data.allSchools || [];
                if (!isSearchInit) {
                    initSchoolSearchAutocomplete();
                    isSearchInit = true;
                }
            } catch (e) { console.error('Failed to fetch school filters', e); }
        }

        function updateChipCount(countId, containerId, themeClass) {
            const count = document.querySelectorAll(`#${containerId} .filter-chip.${themeClass}`).length;
            document.getElementById(countId).textContent = `${count} SELECTED`;
        }

        function toggleSortChip(el) {
            // Sorting logic removed from UI
        }

        function initSchoolSearchAutocomplete() {
            const input = document.getElementById('schoolFilterSearch');
            
            input.oninput = (e) => {
                // Debounced auto-fetch for search
                clearTimeout(this.searchTimeout);
                this.searchTimeout = setTimeout(() => schoolFetchData(), 400);
            };
        }

        async function schoolFetchData() {
            const loading = document.getElementById('schoolLoading');
            loading.classList.remove('hidden');

            const getSelected = (containerId, themeClass) => {
                return Array.from(document.querySelectorAll(`#${containerId} .filter-chip.${themeClass}`))
                    .map(c => c.dataset.value);
            };

            const filters = {
                quadrant: getSelected('quadrantChipContainer', 'active-red'),
                district: getSelected('districtChipContainer', 'active-blue'),
                search: document.getElementById('schoolFilterSearch').value,
                sort: 'name_asc'
            };
            try {
                const res = await fetch("{{ route('api.schools.preview') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ filters: filters })
                });
                const data = await res.json();
                schoolRowsData = data.rows || [];
                schoolCurrentPage = 1;
                renderSchoolTable();
            } catch (e) {
                console.error('Failed to fetch schools', e);
                Swal.fire('Error', 'Failed to load school data.', 'error');
            } finally {
                loading.classList.add('hidden');
            }
        }

        function clearSchoolFilters() {
            document.querySelectorAll('.filter-chip').forEach(c => {
                c.classList.remove('active-red', 'active-blue');
            });
            document.getElementById('schoolFilterSearch').value = '';
            document.getElementById('quadrantCount').textContent = '0 SELECTED';
            document.getElementById('districtCount').textContent = '0 SELECTED';
            schoolCurrentPage = 1;
            schoolFetchData();
        }

        function renderSchoolTable() {
            const tbody = document.getElementById('schoolBody');
            tbody.innerHTML = '';
            if (schoolRowsData.length === 0) {
                document.getElementById('schoolEmpty').classList.remove('hidden');
                document.getElementById('schoolRowCountLabel').textContent = '0 Rows';
                return;
            }
            document.getElementById('schoolEmpty').classList.add('hidden');
            const start = (schoolCurrentPage - 1) * schoolRowsPerPage;
            const pageData = schoolRowsData.slice(start, start + schoolRowsPerPage);
            pageData.forEach((row, idx) => {
                const displayNum = start + idx + 1;
                const tr = document.createElement('tr');
                tr.className = 'xls-row group border-b border-slate-100';
                tr.onclick = (e) => {
                    if (e.target.closest('a')) return;
                    window.location.href = `/schools/${row.id}`;
                };
                
                const cell = (val, extra = '') => `<td class="xls-td relative ${extra}"><span class="xls-const">${val || ''}</span></td>`;
                const idCell = (val, extra = '') => `<td class="xls-td relative ${extra}"><span class="xls-const font-black text-red-600 italic">${val || ''}</span></td>`;
                const costCell = (val, color) => `<td class="xls-td relative"><span class="xls-const font-black italic ${color}">₱ ${Number(val || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></td>`;

                tr.innerHTML = `
                    <td class="xls-td text-center sticky left-0 w-10 z-20"><span class="text-[10px] font-black text-slate-500">${displayNum}</span></td>
                    ${idCell(row.school_id, 'sticky left-[40px] z-20')}
                    <td class="xls-td relative">
                        <span class="xls-const font-bold text-slate-800 uppercase">${row.name || ''}</span>
                    </td>
                    ${cell(row.district_name)}
                    ${cell(row.quadrant_name)}
                    ${costCell(row.total_bldg_cost, 'text-emerald-600')}
                    ${costCell(row.total_ppe_cost, 'text-blue-600')}
                    ${costCell(row.total_semi_ppe_cost, 'text-amber-600')}
                `;
                tbody.appendChild(tr);
            });
            const totalPages = Math.ceil(schoolRowsData.length / schoolRowsPerPage) || 1;
            document.getElementById('schoolRowCountLabel').textContent = schoolRowsData.length + " Schools Found";
            
            document.getElementById('schoolCurrentPage').textContent = schoolCurrentPage;
            document.getElementById('schoolTotalPages').textContent = totalPages;
            document.getElementById('schoolPrevBtn').disabled = schoolCurrentPage === 1;
            document.getElementById('schoolNextBtn').disabled = schoolCurrentPage === totalPages;
        }

        function schoolPrevPage() { if (schoolCurrentPage > 1) { schoolCurrentPage--; renderSchoolTable(); } }
        function schoolNextPage() { const t = Math.ceil(schoolRowsData.length/schoolRowsPerPage); if (schoolCurrentPage < t) { schoolCurrentPage++; renderSchoolTable(); } }

        function toggleSchoolFilters() {
            const section = document.getElementById('schoolFilterSection');
            const btn = document.getElementById('toggleFilterBtn');
            const tableWrap = document.querySelector('.xls-scroll-wrap');
            
            if (section.classList.contains('hidden')) {
                section.classList.remove('hidden');
                tableWrap.classList.remove('expanded');
                btn.classList.add('bg-red-50', 'text-red-600', 'border-red-200');
                btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg> Hide Filters`;
            } else {
                section.classList.add('hidden');
                tableWrap.classList.add('expanded');
                btn.classList.remove('bg-red-50', 'text-red-600', 'border-red-200');
                btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 group-hover:rotate-12 transition-transform duration-300"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg> Filters`;
            }
        }


        document.addEventListener('DOMContentLoaded', () => {
            schoolFetchFilters();
            schoolFetchData();
        });
    </script>
</body>
</html>
