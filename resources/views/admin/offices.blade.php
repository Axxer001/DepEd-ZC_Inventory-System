<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Office Registry | DepEd Zamboanga City</title>
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
        
        /* Drawer Styles */
        #officeDrawer {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        #officeDrawerOverlay {
            transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        html.dark #officeDrawer {
            background-color: #1e293b !important;
            border-color: #334155 !important;
        }
        html.dark #officeDrawer .bg-slate-50 {
            background-color: #0f172a !important;
        }
        html.dark #officeDrawer .border-slate-100 {
            border-color: #334155 !important;
        }
        html.dark #officeDrawer .divide-slate-100 {
            border-color: #334155 !important;
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
                <h2 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-red-700 to-red-500 uppercase italic leading-none drop-shadow-sm tracking-tight">Office Registry</h2>
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

        <!-- Filter Configuration -->
        <div id="officeFilterSection" class="hidden bg-white rounded-[2.5rem] shadow-xl border border-slate-100 p-8 mb-8 relative z-50 animate-fade transition-all duration-300 origin-top">
            <div class="flex flex-col gap-8 relative z-10">
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <label class="text-[10px] font-black text-slate-900 uppercase tracking-widest italic flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                            Office Types
                        </label>
                        <span id="typeCount" class="text-[9px] font-black text-slate-400 uppercase tracking-widest">0 SELECTED</span>
                    </div>
                    <div id="typeChipContainer" class="filter-container">
                        {{-- Chips injected via JS --}}
                    </div>
                </div>
            </div>
            
            <div class="mt-8 flex justify-end items-center gap-8 relative z-10 pt-6 border-t border-slate-100/60">
                <button onclick="clearOfficeFilters()" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] hover:text-[#c00000] hover:-translate-y-0.5 transition-all duration-300 italic">Clear All Filters</button>
                <button onclick="officeFetchData()" class="px-10 py-4 bg-gradient-to-r from-red-700 to-red-500 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:from-red-800 hover:to-red-600 transition-all duration-300 active:translate-y-0 shadow-lg shadow-red-500/30 italic transform hover:-translate-y-0.5 group flex items-center gap-2">
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
                        <th class="xls-th sticky left-[40px] z-30" style="min-width:300px">Office Name</th>
                        <th class="xls-th" style="min-width:180px">Type</th>
                        <th class="xls-th" style="min-width:180px">Location</th>
                        <th class="xls-th text-right" style="min-width:150px">Total Bldg Cost</th>
                        <th class="xls-th text-right" style="min-width:150px">Total PPE Cost</th>
                        <th class="xls-th text-right" style="min-width:150px">Total Semi-PPE Cost</th>
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

            <div id="officeTableFooter" class="px-6 py-4 border-t border-slate-100 flex items-center justify-between relative z-30 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                <div class="flex items-center gap-6">
                    <p id="officeRowCountLabel" class="text-[9px] font-black text-slate-400 uppercase tracking-widest">0 Rows</p>
                    <div id="officePaginationControls" class="flex items-center gap-3 border-l border-slate-200 pl-6">
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
            </div>
        </div>
    </div>
    </div>

    <!-- Office Profile Drawer Overlay -->
    <div id="officeDrawerOverlay" class="fixed inset-0 bg-slate-950/40 backdrop-blur-[2px] z-[9998] hidden opacity-0 transition-opacity duration-300 pointer-events-auto" onclick="closeOfficeProfile()"></div>

    <!-- Office Profile Drawer -->
    <div id="officeDrawer" class="fixed right-0 top-0 bottom-0 w-[550px] max-w-full bg-white dark:bg-slate-900 border-l border-slate-200 dark:border-slate-800 shadow-2xl z-[9999] translate-x-full transition-transform duration-300 flex flex-col overflow-hidden">
        <!-- Drawer Header -->
        <div class="p-6 border-b border-slate-100 dark:border-slate-800/60 bg-slate-50 dark:bg-slate-950 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-950/50 text-red-600 flex items-center justify-center shadow-inner">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15" /></svg>
                </div>
                <div>
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest leading-none">Office Profile</h3>
                    <h4 id="drawerOfficeName" class="text-base font-extrabold text-slate-800 dark:text-slate-100 uppercase tracking-tight mt-1 truncate max-w-[340px]">Loading...</h4>
                </div>
            </div>
            <button onclick="closeOfficeProfile()" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-red-600 hover:rotate-90 transition-all duration-300 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <!-- Drawer Content -->
        <div class="flex-grow overflow-y-auto custom-scroll p-6 flex flex-col gap-6 relative">
            <!-- Drawer Loading State -->
            <div id="drawerLoading" class="absolute inset-0 bg-white/80 dark:bg-slate-900/80 backdrop-blur-[2px] z-[50] flex items-center justify-center hidden">
                <div class="flex flex-col items-center gap-3">
                    <div class="w-8 h-8 border-3 border-slate-100 border-t-red-600 rounded-full animate-spin"></div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest animate-pulse">Loading Details...</p>
                </div>
            </div>

            <!-- Metadata Badges Card -->
            <div class="grid grid-cols-2 gap-4">
                <div class="p-4 bg-slate-50 dark:bg-slate-950/40 rounded-2xl border border-slate-100 dark:border-slate-850">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Office Code</span>
                    <span id="drawerOfficeCode" class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase mt-1 block">-</span>
                </div>
                <div class="p-4 bg-slate-50 dark:bg-slate-950/40 rounded-2xl border border-slate-100 dark:border-slate-850">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Room Number</span>
                    <span id="drawerRoomNumber" class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase mt-1 block">-</span>
                </div>
                <div class="col-span-2 p-4 bg-slate-50 dark:bg-slate-950/40 rounded-2xl border border-slate-100 dark:border-slate-850">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Assigned Station / School</span>
                    <span id="drawerSchoolName" class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase mt-1 block">-</span>
                </div>
            </div>

            <!-- Summary Stats / Portfolio KPI Card -->
            <div>
                <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                    Financial Portfolio Summary
                </h4>
                <div class="grid grid-cols-3 gap-3">
                     <div class="p-3 bg-emerald-50/50 dark:bg-emerald-950/10 rounded-2xl border border-emerald-100/50 dark:border-emerald-900/20 text-center">
                         <span class="text-[8px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-wider block">Buildings</span>
                         <span id="drawerTotalBldg" class="text-xs font-black text-emerald-700 dark:text-emerald-300 italic block mt-1">₱ 0.00</span>
                     </div>
                     <div class="p-3 bg-blue-50/50 dark:bg-blue-950/10 rounded-2xl border border-blue-100/50 dark:border-blue-900/20 text-center">
                         <span class="text-[8px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-wider block">PPE Cost</span>
                         <span id="drawerTotalPpe" class="text-xs font-black text-blue-700 dark:text-blue-300 italic block mt-1">₱ 0.00</span>
                     </div>
                     <div class="p-3 bg-amber-50/50 dark:bg-amber-950/10 rounded-2xl border border-amber-100/50 dark:border-amber-900/20 text-center">
                         <span class="text-[8px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-wider block">Semi-PPE</span>
                         <span id="drawerTotalSemiPpe" class="text-xs font-black text-amber-700 dark:text-amber-300 italic block mt-1">₱ 0.00</span>
                     </div>
                </div>
            </div>

            <!-- Tabbed/List Sections for Assigned Assets & Buildings -->
            <div class="flex-grow flex flex-col gap-4">
                 <!-- Assigned Equipment List -->
                 <div>
                     <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3 flex items-center justify-between">
                         <span class="flex items-center gap-2">
                             <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                             Assigned PPE & Equipment
                         </span>
                         <span id="drawerAssetCountBadge" class="px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 text-[8px] font-black">0 ITEMS</span>
                     </h4>
                     <div class="border border-slate-100 dark:border-slate-800 rounded-2xl overflow-hidden max-h-[220px] overflow-y-auto custom-scroll bg-slate-50/30 dark:bg-slate-950/20">
                         <table class="w-full text-left border-collapse text-[10px]">
                             <thead class="bg-slate-50 dark:bg-slate-950 text-slate-400 uppercase font-black tracking-wider text-[8px] border-b border-slate-100 dark:border-slate-800">
                                 <tr>
                                     <th class="p-3">Property No.</th>
                                     <th class="p-3">Article / Desc</th>
                                     <th class="p-3 text-right">Cost</th>
                                     <th class="p-3 text-center">Condition</th>
                                 </tr>
                             </thead>
                             <tbody id="drawerAssetList" class="divide-y divide-slate-100 dark:divide-slate-800/60">
                                 <!-- Dynamic Assets -->
                             </tbody>
                         </table>
                         <div id="drawerAssetEmpty" class="p-8 text-center text-slate-400 dark:text-slate-650 text-[9px] font-black uppercase tracking-wider hidden">
                             No equipment assigned to this office.
                         </div>
                     </div>
                 </div>

                 <!-- Connected Buildings List -->
                 <div>
                     <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3 flex items-center justify-between">
                         <span class="flex items-center gap-2">
                             <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                             Associated Buildings
                         </span>
                         <span id="drawerBldgCountBadge" class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 text-[8px] font-black">0 BUILDINGS</span>
                     </h4>
                     <div class="border border-slate-100 dark:border-slate-800 rounded-2xl overflow-hidden max-h-[200px] overflow-y-auto custom-scroll bg-slate-50/30 dark:bg-slate-950/20">
                         <table class="w-full text-left border-collapse text-[10px]">
                             <thead class="bg-slate-50 dark:bg-slate-950 text-slate-400 uppercase font-black tracking-wider text-[8px] border-b border-slate-100 dark:border-slate-800">
                                 <tr>
                                     <th class="p-3">Property No.</th>
                                     <th class="p-3">Building Type</th>
                                     <th class="p-3 text-right">Cost</th>
                                     <th class="p-3 text-center">Condition</th>
                                 </tr>
                             </thead>
                             <tbody id="drawerBldgList" class="divide-y divide-slate-100 dark:divide-slate-800/60">
                                 <!-- Dynamic Buildings -->
                             </tbody>
                         </table>
                         <div id="drawerBldgEmpty" class="p-8 text-center text-slate-400 dark:text-slate-650 text-[9px] font-black uppercase tracking-wider hidden">
                             No buildings associated with this office.
                         </div>
                     </div>
                 </div>
             </div>
        </div>
    </div>

    <script>
        let officeRowsData = [];
        let officeCurrentPage = 1;
        const officeRowsPerPage = 50;

        async function officeFetchFilters() {
            try {
                const res = await fetch(`{{ route('api.offices.filters') }}`);
                const data = await res.json();
                
                const renderChips = (containerId, list, countId, themeClass) => {
                    const container = document.getElementById(containerId);
                    container.innerHTML = '';
                    (list || []).forEach(item => {
                        if (!item) return;
                        const chip = document.createElement('div');
                        chip.className = `filter-chip`;
                        chip.textContent = item;
                        chip.dataset.value = item;
                        chip.onclick = () => {
                            chip.classList.toggle(themeClass);
                            updateChipCount(countId, containerId, themeClass);
                        };
                        container.appendChild(chip);
                    });
                };
                
                renderChips('typeChipContainer', data.types, 'typeCount', 'active-red');
            } catch (e) { console.error('Failed to fetch office filters', e); }
        }

        function updateChipCount(countId, containerId, themeClass) {
            const count = document.querySelectorAll(`#${containerId} .filter-chip.${themeClass}`).length;
            document.getElementById(countId).textContent = `${count} SELECTED`;
        }

        let officeSearchTimer;
        function officeDebouncedSearch() {
            clearTimeout(officeSearchTimer);
            officeSearchTimer = setTimeout(() => officeFetchData(), 400);
        }

        async function officeFetchData() {
            const loading = document.getElementById('officeLoading');
            loading.classList.remove('hidden');

            const getSelected = (containerId, themeClass) => {
                return Array.from(document.querySelectorAll(`#${containerId} .filter-chip.${themeClass}`))
                    .map(c => c.dataset.value);
            };

            const filters = {
                type: getSelected('typeChipContainer', 'active-red'),
                search: document.getElementById('officeFilterSearch').value
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
            document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active-red'));
            document.getElementById('officeFilterSearch').value = '';
            document.getElementById('typeCount').textContent = '0 SELECTED';
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
                tr.onclick = () => openOfficeProfile(row.id);
                
                const cell = (val, extra = '') => `<td class="xls-td relative ${extra}"><span class="xls-const uppercase">${val || ''}</span></td>`;
                const costCell = (val, color) => `<td class="xls-td relative text-right"><span class="xls-const font-black italic ${color} justify-end">₱ ${Number(val || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></td>`;

                tr.innerHTML = `
                    <td class="xls-td text-center sticky left-0 w-10 z-20"><span class="text-[10px] font-black text-slate-500">${displayNum}</span></td>
                    <td class="xls-td relative sticky left-[40px] z-20">
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

        async function openOfficeProfile(id) {
            const overlay = document.getElementById('officeDrawerOverlay');
            const drawer = document.getElementById('officeDrawer');
            const loading = document.getElementById('drawerLoading');

            // Open overlay and drawer
            overlay.classList.remove('hidden');
            drawer.classList.remove('translate-x-full');
            loading.classList.remove('hidden');
            setTimeout(() => {
                overlay.classList.remove('opacity-0');
            }, 10);

            try {
                const res = await fetch(`/api/offices/${id}/details`);
                if (!res.ok) throw new Error('Office not found');
                const data = await res.json();

                // Inject office details
                document.getElementById('drawerOfficeName').textContent = data.office.name || '-';
                document.getElementById('drawerOfficeCode').textContent = data.office.office_code || '-';
                document.getElementById('drawerRoomNumber').textContent = data.office.room_number || '-';
                document.getElementById('drawerSchoolName').textContent = data.office.school_name || '-';

                // Find matching row in our local officeRowsData to get cost summary
                const matchedRow = officeRowsData.find(r => r.id === id) || {};
                
                const formatCost = (val) => '₱ ' + Number(val || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                document.getElementById('drawerTotalBldg').textContent = formatCost(matchedRow.total_bldg_cost);
                document.getElementById('drawerTotalPpe').textContent = formatCost(matchedRow.total_ppe_cost);
                document.getElementById('drawerTotalSemiPpe').textContent = formatCost(matchedRow.total_semi_ppe_cost);

                // Populate Equipment Assets
                const assetTbody = document.getElementById('drawerAssetList');
                assetTbody.innerHTML = '';
                document.getElementById('drawerAssetCountBadge').textContent = `${data.assets.length} ITEMS`;
                if (data.assets.length === 0) {
                    document.getElementById('drawerAssetEmpty').classList.remove('hidden');
                } else {
                    document.getElementById('drawerAssetEmpty').classList.add('hidden');
                    data.assets.forEach(asset => {
                        const tr = document.createElement('tr');
                        tr.className = 'border-b border-slate-100 dark:border-slate-800/40 hover:bg-slate-50/50 dark:hover:bg-slate-800/20';
                        
                        const conditionTheme = asset.condition === 'Good' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400';

                        tr.innerHTML = `
                            <td class="p-3 font-semibold text-slate-600 dark:text-slate-400">${asset.property_number || '-'}</td>
                            <td class="p-3">
                                <span class="font-bold text-slate-800 dark:text-slate-200 block uppercase">${asset.article || '-'}</span>
                                <span class="text-[9px] text-slate-400 dark:text-slate-500 uppercase mt-0.5 block truncate max-w-[200px]">${asset.description || ''}</span>
                            </td>
                            <td class="p-3 text-right font-black italic text-slate-700 dark:text-slate-300">${formatCost(asset.acquisition_cost)}</td>
                            <td class="p-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[8px] font-black uppercase ${conditionTheme}">${asset.condition || 'Good'}</span>
                            </td>
                        `;
                        assetTbody.appendChild(tr);
                    });
                }

                // Populate Buildings
                const bldgTbody = document.getElementById('drawerBldgList');
                bldgTbody.innerHTML = '';
                document.getElementById('drawerBldgCountBadge').textContent = `${data.buildings.length} BUILDINGS`;
                if (data.buildings.length === 0) {
                    document.getElementById('drawerBldgEmpty').classList.remove('hidden');
                } else {
                    document.getElementById('drawerBldgEmpty').classList.add('hidden');
                    data.buildings.forEach(b => {
                        const tr = document.createElement('tr');
                        tr.className = 'border-b border-slate-100 dark:border-slate-800/40 hover:bg-slate-50/50 dark:hover:bg-slate-800/20';
                        
                        const conditionTheme = b.condition === 'Good' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400';

                        tr.innerHTML = `
                            <td class="p-3 font-semibold text-slate-600 dark:text-slate-400">${b.property_number || '-'}</td>
                            <td class="p-3 font-bold text-slate-800 dark:text-slate-200 uppercase">${b.type || '-'}</td>
                            <td class="p-3 text-right font-black italic text-slate-700 dark:text-slate-300">${formatCost(b.acquisition_cost)}</td>
                            <td class="p-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[8px] font-black uppercase ${conditionTheme}">${b.condition || 'Good'}</span>
                            </td>
                        `;
                        bldgTbody.appendChild(tr);
                    });
                }

            } catch (e) {
                console.error(e);
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Failed to load office profile details.',
                    confirmButtonColor: '#c00000'
                });
                closeOfficeProfile();
            } finally {
                loading.classList.add('hidden');
            }
        }

        function closeOfficeProfile() {
            const overlay = document.getElementById('officeDrawerOverlay');
            const drawer = document.getElementById('officeDrawer');
            
            overlay.classList.add('opacity-0');
            drawer.classList.add('translate-x-full');
            setTimeout(() => {
                overlay.classList.add('hidden');
            }, 300);
        }

        document.addEventListener('DOMContentLoaded', () => {
            officeFetchFilters();
            officeFetchData();
        });
    </script>
</body>
</html>
