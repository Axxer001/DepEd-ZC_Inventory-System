<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Building Records | DepEd Zamboanga City</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
        .xls-scroll-wrap { --col1-width: 40px; position: relative; overflow-x: auto; overflow-y: auto; height: calc(100vh - 450px); min-height: 400px; background: white; flex-grow: 1; transition: height 0.4s cubic-bezier(0.4, 0, 0.2, 1); border-top: 1px solid #e2e8f0; }
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
        html.dark .dep-tooltip { background-color: rgba(30, 41, 59, 0.9) !important; border-color: #334155 !important; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important; }
        html.dark .dep-stat-box { background-color: #0f172a !important; border-color: #334155 !important; }
        html.dark .dep-tooltip p { color: #94a3b8 !important; }
        html.dark .dep-tooltip .border-t { border-color: #334155 !important; }
        html.dark #tipYear1 { color: #f87171 !important; }
        html.dark #tipYear25 { color: #34d399 !important; }
        
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


        /* Depreciation Tooltip */
        .dep-tooltip {
            position: fixed;
            pointer-events: none;
            z-index: 1000;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1.25rem;
            padding: 1.5rem;
            width: 280px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(12px);
            opacity: 0;
            transform: scale(0.95);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .dep-tooltip.active {
            opacity: 1;
            transform: scale(1);
        }
        .dep-stat-box {
            padding: 10px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }



        /* Custom Autocomplete */
        .custom-autocomplete {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-top: 4px;
            max-height: 250px;
            overflow-y: auto;
            z-index: 50;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .custom-autocomplete-item {
            padding: 12px 16px;
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
            padding-left: 20px;
        }
        html.dark .custom-autocomplete {
            background: #1e293b;
            border-color: #334155;
        }
        html.dark .custom-autocomplete-item {
            color: #94a3b8;
        }
        html.dark .custom-autocomplete-item:hover {
            background: #0f172a;
            color: #f8fafc;
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
                <h2 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-red-700 to-red-500 uppercase italic leading-tight drop-shadow-sm tracking-tight pb-1 pr-4">Building Records</h2>
                <p class="text-slate-500 text-[11px] font-bold uppercase tracking-[0.25em] mt-3 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse shadow-[0_0_8px_rgba(239,68,68,0.6)]"></span>
                    Master Building Registry
                </p>
            </div>

            <div class="flex-grow max-w-2xl relative">
                <div class="relative group">
                    <input type="text" id="bldgFilterSchool" oninput="bldgDebouncedSearch()" placeholder="SEARCH SCHOOL NAME OR PROPERTY #..." autocomplete="off" class="w-full bg-white border-2 border-slate-100 rounded-2xl px-6 py-4 text-xs font-black uppercase tracking-widest focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-700 shadow-sm pr-12 group-hover:border-slate-200">
                    <div class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 group-hover:text-red-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 shrink-0">
                <button onclick="toggleBldgColumns()" id="toggleColumnsBtn" class="px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest text-slate-600 bg-white border border-slate-200 hover:text-[#c00000] hover:-translate-y-0.5 hover:shadow-md active:translate-y-0 transition-all duration-300 flex items-center gap-2 group italic">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 group-hover:scale-110 transition-transform duration-300"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                    View All Columns
                </button>
                <button onclick="toggleBldgFilters()" id="toggleFilterBtn" class="px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest text-slate-600 bg-white border border-slate-200 hover:text-[#c00000] hover:-translate-y-0.5 hover:shadow-md active:translate-y-0 transition-all duration-300 flex items-center gap-2 group italic">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 group-hover:rotate-12 transition-transform duration-300"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg>
                    Show Filters
                </button>
                <a href="/dashboard" class="px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest text-slate-600 bg-white border border-slate-200 hover:text-[#c00000] hover:-translate-y-0.5 hover:shadow-md active:translate-y-0 transition-all duration-300 flex items-center gap-2 group italic">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 group-hover:-translate-x-1 transition-transform duration-300"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                    Back
                </a>
            </div>

        </div>

        <!-- Filter Configuration -->
        <div id="bldgFilterSection" class="hidden bg-white rounded-[2.5rem] shadow-lg border border-slate-100 p-8 mb-8 relative z-50 animate-fade transition-all duration-300 origin-top">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-8 relative z-10">
                <div>
                    <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Classification</label>
                    <select id="bldgFilterClass" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-500">
                        <option value="">All Classifications</option>
                    </select>
                </div>
                <div>
                    <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Office Type</label>
                    <select id="bldgFilterType" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-500">
                        <option value="">All Types</option>
                    </select>
                </div>

                <div>
                    <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Cost Sorting</label>
                    <select id="bldgFilterSort" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-500">
                        <option value="">Default (ID)</option>
                        <option value="low_to_high">Acquisition Cost: Low to High</option>
                        <option value="high_to_low">Acquisition Cost: High to Low</option>
                    </select>
                </div>
                <div>
                    <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Location</label>
                    <select id="bldgFilterLoc" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-500">
                        <option value="">All Locations</option>
                    </select>
                </div>
                <div>
                    <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Acquisition Date</label>
                    <input type="date" id="bldgFilterDate" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-500">
                </div>
                <div>
                    <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic text-red-500">Data Integrity (Empty Fields)</label>
                    <select id="bldgFilterIntegrity" class="w-full bg-slate-50 border-red-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-500">
                        <option value="">No Integrity Filter</option>
                        <option value="region">Missing Region</option>
                        <option value="division">Missing Division</option>
                        <option value="office_type">Missing Office Type</option>
                        <option value="school_identifier">Missing School ID</option>
                        <option value="office_name">Missing School Name</option>
                        <option value="address">Missing Address</option>
                        <option value="storeys">Missing Storeys</option>
                        <option value="classrooms">Missing Classrooms</option>
                        <option value="article">Missing Article</option>
                        <option value="description">Missing Description</option>
                        <option value="classification">Missing Classification</option>
                        <option value="occupancy_nature">Missing Occupancy</option>
                        <option value="location">Missing Location</option>
                        <option value="date_constructed">Missing Date Constructed</option>
                        <option value="acquisition_date">Missing Acquisition Date</option>
                        <option value="property_number">Missing Property Number</option>
                        <option value="acquisition_cost">Missing Acquisition Cost</option>
                        <option value="estimated_useful_life">Missing Useful Life</option>
                        <option value="appraised_value">Missing Appraised Value</option>
                        <option value="appraisal_date">Missing Appraisal Date</option>
                    </select>
                </div>
            </div>
                <div class="mt-8 flex justify-end items-center gap-8 relative z-10 pt-6 border-t border-slate-100/60">
                    <button onclick="clearBldgFilters()" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] hover:text-[#c00000] hover:-translate-y-0.5 transition-all duration-300 italic">Clear All Filters</button>
                    <button onclick="bldgFetchData()" class="px-8 py-3 bg-gradient-to-r from-red-700 to-red-500 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:from-red-800 hover:to-red-600 transition-all duration-300 active:translate-y-0 shadow-lg shadow-red-500/30 italic transform hover:-translate-y-0.5 group flex items-center gap-2">
                        Apply Configuration
                        <svg class="w-3.5 h-3.5 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </button>
                </div>
        </div>

        <div class="bg-white rounded-[2rem] border border-slate-200/60 shadow-xl shadow-slate-200/50 overflow-hidden flex flex-col animate-fade relative ring-1 ring-black/5">
            <div class="xls-scroll-wrap expanded">
                <table id="bldgTable" class="w-full border-collapse" style="min-width:1200px;">
                    <thead id="bldgHeader">
                        <tr>
                            <th class="xls-th w-10 text-center sticky top-0 left-0 z-40 bg-[#f8fafc] dark:bg-[#0f172a]">#</th>
                            <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:100px">School ID</th>
                            <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:200px">Office/School Name</th>
                            <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:140px">Article</th>
                            <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:170px">Description</th>
                            <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:130px">Property No.</th>
                            <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:70px">Storeys</th>
                            <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:90px">Classrooms</th>
                            <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a] text-right" style="min-width:120px">Acq. Cost (₱)</th>
                            <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:120px">Date Constructed</th>
                        </tr>
                    </thead>
                    <tbody id="bldgBody"></tbody>
                </table>
                
                {{-- Loading State --}}
                <div id="bldgLoading" class="absolute inset-0 bg-white/80 backdrop-blur-[4px] z-50 flex items-center justify-center hidden transition-all duration-300">
                    <div class="flex flex-col items-center gap-5 bg-white px-10 py-8 rounded-3xl shadow-2xl shadow-slate-200/50 border border-slate-100">
                        <div class="w-12 h-12 border-4 border-slate-100 border-t-red-600 rounded-full animate-spin"></div>
                        <p class="text-[10px] font-black text-slate-800 uppercase tracking-widest italic animate-pulse">Fetching Building Data...</p>
                    </div>
                </div>

                {{-- Empty State --}}
                <div id="bldgEmpty" class="absolute inset-0 flex items-center justify-center pointer-events-none transition-all duration-300 bg-white/50 backdrop-blur-[2px]">
                    <div class="inline-flex flex-col items-center gap-4 bg-slate-50/80 px-12 py-10 rounded-[2.5rem] border border-dashed border-slate-200 shadow-sm">
                        <div class="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center text-red-400 shadow-inner">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21"/></svg>
                        </div>
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-[0.25em]">No buildings found — adjust filters</p>
                    </div>
                </div>
            </div>

            <div id="bldgTableFooter" class="px-6 py-4 border-t border-slate-100 flex items-center justify-between relative z-30 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                <div class="flex items-center gap-6">
                    <p id="bldgRowCountLabel" class="text-[9px] font-black text-slate-400 uppercase tracking-widest">0 Rows</p>
                    <div id="bldgPaginationControls" class="flex items-center gap-3 border-l border-slate-200 pl-6">
                        <button onclick="bldgPrevPage()" id="bldgPrevBtn" class="pg-btn">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
                            Prev
                        </button>
                        <div class="glass-indicator">
                            <span id="bldgCurrentPage" class="text-[10px] font-black text-red-600">1</span>
                            <span class="text-[10px] font-bold text-slate-500">/</span>
                            <span id="bldgTotalPages" class="text-[10px] font-black text-slate-500">1</span>
                        </div>
                        <button onclick="bldgNextPage()" id="bldgNextBtn" class="pg-btn">
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
        let bldgRowsData = [];
        let bldgCurrentPage = 1;
        const bldgRowsPerPage = 50;
        let allSchools = [];
        let isAutocompleteInit = false;
        let bldgShowAllColumns = false;

        function toggleBldgColumns() {
            bldgShowAllColumns = !bldgShowAllColumns;
            const btn = document.getElementById('toggleColumnsBtn');
            const table = document.getElementById('bldgTable');
            const thead = document.getElementById('bldgHeader');
            
            if (bldgShowAllColumns) {
                btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" /></svg> Hide Extra Columns`;
                table.style.minWidth = '2400px';
                thead.innerHTML = `<tr>
                    <th class="xls-th w-10 text-center sticky top-0 left-0 z-40 bg-[#f8fafc] dark:bg-[#0f172a]">#</th>
                    <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:90px">Region</th>
                    <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:190px">Division</th>
                    <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:140px">Office/School Type</th>
                    <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:100px">School ID</th>
                    <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:200px">Office/School Name</th>
                    <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:180px">Address</th>
                    <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:70px">Storeys</th>
                    <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:90px">Classrooms</th>
                    <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:140px">Article</th>
                    <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:170px">Description</th>
                    <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:130px">Classification</th>
                    <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:130px">Occupancy</th>
                    <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:150px">Location</th>
                    <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:120px">Date Constructed</th>
                    <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:120px">Acquisition Date</th>
                    <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:130px">Property No.</th>
                    <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a] text-right" style="min-width:120px">Acq. Cost (₱)</th>
                    <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a] text-center" style="min-width:100px">Est. Useful Life</th>
                    <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a] text-right" style="min-width:120px">Appraised Value</th>
                    <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:120px">Appraisal Date</th>
                    <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:140px">Remarks</th>
                </tr>`;
            } else {
                btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg> View All Columns`;
                table.style.minWidth = '1200px';
                thead.innerHTML = `<tr>
                    <th class="xls-th w-10 text-center sticky top-0 left-0 z-40 bg-[#f8fafc] dark:bg-[#0f172a]">#</th>
                    <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:100px">School ID</th>
                    <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:200px">Office/School Name</th>
                    <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:140px">Article</th>
                    <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:170px">Description</th>
                    <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:130px">Property No.</th>
                    <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:70px">Storeys</th>
                    <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:90px">Classrooms</th>
                    <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a] text-right" style="min-width:120px">Acq. Cost (₱)</th>
                    <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:120px">Date Constructed</th>
                </tr>`;
            }
            renderBldgTable();
        }

        async function bldgFetchFilters() {
            try {
                const res = await fetch("{{ route('api.buildings.filters') }}");
                const data = await res.json();
                
                const populate = (id, list) => {
                    const el = document.getElementById(id);
                    const currentVal = el.value;
                    el.innerHTML = `<option value="">All ${id.replace('bldgFilter', '')}s</option>`;
                    list.forEach(item => {
                        const opt = document.createElement('option');
                        opt.value = item;
                        opt.textContent = item;
                        if(item === currentVal) opt.selected = true;
                        el.appendChild(opt);
                    });
                };
                
                populate('bldgFilterClass', data.classifications);
                populate('bldgFilterType', data.officeTypes);
                populate('bldgFilterLoc', data.locations);
                
                allSchools = data.schools;

            } catch (e) { console.error('Failed to fetch building filters', e); }
        }

        let bldgSearchTimer;
        function bldgDebouncedSearch() {
            clearTimeout(bldgSearchTimer);
            bldgSearchTimer = setTimeout(() => {
                bldgCurrentPage = 1;
                bldgFetchData();
            }, 500);
        }


        async function bldgFetchData() {
            const loading = document.getElementById('bldgLoading');
            loading.classList.remove('hidden');
            const filters = {
                classification: document.getElementById('bldgFilterClass').value,
                officeType: document.getElementById('bldgFilterType').value,
                schoolName: document.getElementById('bldgFilterSchool').value,
                location: document.getElementById('bldgFilterLoc').value,
                sortCost: document.getElementById('bldgFilterSort').value,
                dateAcquired: document.getElementById('bldgFilterDate').value,
                emptyCol: document.getElementById('bldgFilterIntegrity').value
            };
            try {
                const res = await fetch("{{ route('api.buildings.preview') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ filters: filters })
                });
                const data = await res.json();
                bldgRowsData = data.rows || [];
                bldgCurrentPage = 1;
                renderBldgTable();
                bldgFetchFilters();
            } catch (e) {
                console.error('Failed to fetch buildings', e);
                Swal.fire('Error', 'Failed to load building data.', 'error');
            } finally {
                loading.classList.add('hidden');
            }
        }

        function clearBldgFilters() {
            document.getElementById('bldgFilterClass').value = '';
            document.getElementById('bldgFilterType').value = '';
            document.getElementById('bldgFilterSchool').value = '';
            document.getElementById('bldgFilterSort').value = '';
            document.getElementById('bldgFilterLoc').value = '';
            document.getElementById('bldgFilterDate').value = '';
            document.getElementById('bldgFilterIntegrity').value = '';
            bldgCurrentPage = 1;
            bldgFetchData();
        }

        function renderBldgTable() {
            const tbody = document.getElementById('bldgBody');
            tbody.innerHTML = '';
            if (bldgRowsData.length === 0) {
                document.getElementById('bldgEmpty').classList.remove('hidden');
                document.getElementById('bldgRowCountLabel').textContent = '0 Rows';
                return;
            }
            document.getElementById('bldgEmpty').classList.add('hidden');
            const start = (bldgCurrentPage - 1) * bldgRowsPerPage;
            const pageData = bldgRowsData.slice(start, start + bldgRowsPerPage);
            pageData.forEach((row, idx) => {
                const displayNum = start + idx + 1;
                const tr = document.createElement('tr');
                tr.className = 'xls-row group border-b border-slate-100';
                tr.style.cursor = 'pointer';
                tr.onclick = (e) => {
                    if (e.target.closest('a')) return;
                    window.location.href = `/buildings/${row.id}`;
                };
                
                const cell = (val, extra = '') => `<td class="xls-td relative ${extra}"><span class="xls-const">${val || ''}</span></td>`;
                const numCell = (val, extra = '') => `<td class="xls-td relative ${extra}"><span class="xls-const justify-center">${val || '0'}</span></td>`;
                const costCell = (val, extra = '') => `<td class="xls-td relative ${extra}"><span class="xls-const justify-end font-bold text-red-600">₱ ${val ? parseFloat(val).toLocaleString(undefined, {minimumFractionDigits: 2}) : '0.00'}</span></td>`;

                if (bldgShowAllColumns) {
                    tr.innerHTML = `
                        <td class="xls-td text-center sticky left-0 w-10 z-20 bg-white dark:bg-[#1e293b]"><span class="text-[10px] font-black text-slate-500">${displayNum}</span></td>
                        ${cell(row.region)}
                        ${cell(row.division)}
                        ${cell(row.office_type)}
                        ${cell(row.school_identifier)}
                        ${cell(row.office_name)}
                        ${cell(row.address)}
                        ${numCell(row.storeys)}
                        ${numCell(row.classrooms)}
                        ${cell(row.article)}
                        ${cell(row.description)}
                        ${cell(row.classification)}
                        ${cell(row.occupancy_nature)}
                        ${cell(row.location)}
                        ${cell(row.date_constructed)}
                        ${cell(row.acquisition_date)}
                        ${cell(row.property_number)}
                        ${costCell(row.acquisition_cost)}
                        ${numCell(row.estimated_useful_life)}
                        ${costCell(row.appraised_value)}
                        ${cell(row.appraisal_date)}
                        ${cell(row.remarks)}
                    `;
                } else {
                    tr.innerHTML = `
                        <td class="xls-td text-center sticky left-0 w-10 z-20 bg-white dark:bg-[#1e293b]"><span class="text-[10px] font-black text-slate-500">${displayNum}</span></td>
                        ${cell(row.school_identifier)}
                        ${cell(row.office_name, 'font-bold text-[#c00000]')}
                        ${cell(row.article)}
                        ${cell(row.description)}
                        ${cell(row.property_number)}
                        ${numCell(row.storeys)}
                        ${numCell(row.classrooms)}
                        ${costCell(row.acquisition_cost)}
                        ${cell(row.date_constructed)}
                    `;
                }
                tr.onmouseenter = (e) => showDepTooltip(e, row);
                tr.onmouseleave = () => hideDepTooltip();
                tr.onmousemove = (e) => moveDepTooltip(e);
                tbody.appendChild(tr);
            });
            const totalPages = Math.ceil(bldgRowsData.length / bldgRowsPerPage) || 1;
            document.getElementById('bldgRowCountLabel').textContent = bldgRowsData.length + " Buildings Found";
            
            document.getElementById('bldgCurrentPage').textContent = bldgCurrentPage;
            document.getElementById('bldgTotalPages').textContent = totalPages;
            document.getElementById('bldgPrevBtn').disabled = bldgCurrentPage === 1;
            document.getElementById('bldgNextBtn').disabled = bldgCurrentPage === totalPages;
        }

        function bldgPrevPage() { if (bldgCurrentPage > 1) { bldgCurrentPage--; renderBldgTable(); } }
        function bldgNextPage() { const t = Math.ceil(bldgRowsData.length/bldgRowsPerPage); if (bldgCurrentPage < t) { bldgCurrentPage++; renderBldgTable(); } }

        function toggleBldgFilters() {
            const section = document.getElementById('bldgFilterSection');
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

        function showDepTooltip(e, row) {
            const tip = document.getElementById('depTooltip');
            const cost = parseFloat(row.acquisition_cost) || 0;
            const life = parseInt(row.estimated_useful_life) || 1;
            
            if (cost === 0) return;

            const residual = cost * 0.05;
            const annualDep = (cost - residual) / life;
            
            const year1Val = Math.max(residual, cost - annualDep);
            const year25Val = Math.max(residual, cost - (annualDep * 25));

            document.getElementById('tipAnnualDep').textContent = '₱ ' + annualDep.toLocaleString(undefined, {minimumFractionDigits: 2});
            document.getElementById('tipYear1').textContent = '₱ ' + year1Val.toLocaleString(undefined, {minimumFractionDigits: 2});
            document.getElementById('tipYear25').textContent = '₱ ' + year25Val.toLocaleString(undefined, {minimumFractionDigits: 2});

            tip.classList.add('active');
            moveDepTooltip(e);
        }

        function moveDepTooltip(e) {
            const tip = document.getElementById('depTooltip');
            const x = e.clientX + 20;
            const y = e.clientY + 20;
            
            // Boundary check
            const tipRect = tip.getBoundingClientRect();
            let finalX = x;
            let finalY = y;

            if (x + tipRect.width > window.innerWidth) finalX = e.clientX - tipRect.width - 20;
            if (y + tipRect.height > window.innerHeight) finalY = e.clientY - tipRect.height - 20;

            tip.style.left = finalX + 'px';
            tip.style.top = finalY + 'px';
        }

        function hideDepTooltip() {
            document.getElementById('depTooltip').classList.remove('active');
        }



        document.addEventListener('DOMContentLoaded', () => {
            bldgFetchFilters();
            bldgFetchData();
        });
    </script>
    <!-- Depreciation Tooltip -->
    <div id="depTooltip" class="dep-tooltip">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 rounded-lg bg-red-600/20 flex items-center justify-center">
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Financial Projection</p>
                <h4 class="text-xs font-bold text-slate-800 uppercase italic">Depreciation Preview</h4>
            </div>
        </div>
        
        <div class="space-y-3">
            <div class="dep-stat-box">
                <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">Annual Expense</p>
                <p id="tipAnnualDep" class="text-sm font-black text-slate-800 italic">₱ 0.00</p>
            </div>
            
            <div class="grid grid-cols-2 gap-3">
                <div class="dep-stat-box">
                    <p class="text-[8px] font-black text-slate-500 uppercase tracking-widest mb-1">Year 1 Book Value</p>
                    <p id="tipYear1" class="text-[11px] font-bold text-red-600">₱ 0.00</p>
                </div>
                <div class="dep-stat-box">
                    <p class="text-[8px] font-black text-slate-500 uppercase tracking-widest mb-1">Year 25 Book Value</p>
                    <p id="tipYear25" class="text-[11px] font-bold text-emerald-600">₱ 0.00</p>
                </div>
            </div>

            <div class="pt-2 border-t border-slate-100">
                <p class="text-[8px] font-bold text-slate-500 leading-relaxed">Calculated at 5% residual value over estimated useful life.</p>
            </div>
        </div>
    </div>


</body>
</html>