<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AMU Stock Registry | DepEd Zamboanga City</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade { animation: fadeIn 0.4s ease-out forwards; }
        .custom-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        html.dark .custom-scroll::-webkit-scrollbar-thumb { background: #475569; }
        .xls-th { 
            padding: 14px 18px; 
            font-size: 10px; 
            font-weight: 850; 
            text-transform: uppercase; 
            letter-spacing: 0.12em; 
            color: #475569; 
            white-space: nowrap; 
            border-right: 1px solid #f1f5f9; 
            border-bottom: 2px solid #e2e8f0; 
            background: #f8fafc; 
            position: sticky; 
            top: 0; 
            z-index: 20; 
            transition: background 0.3s ease;
        }
        .xls-td { 
            height: 52px; 
            border-right: 1px solid #f1f5f9; 
            border-bottom: 1px solid #f1f5f9; 
            vertical-align: middle; 
            padding: 0; 
            background: white; 
            transition: background-color 0.25s ease, border-color 0.25s ease; 
        }
        .xls-row { 
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); 
            cursor: pointer; 
            position: relative; 
        }
        .xls-row:hover { 
            transform: translateY(-1.5px); 
            z-index: 10; 
            box-shadow: 0 10px 20px -5px rgba(192, 0, 0, 0.05), 0 8px 8px -6px rgba(192, 0, 0, 0.05);
        }
        .xls-row:hover .xls-td { 
            background-color: rgba(192, 0, 0, 0.015) !important; 
            border-bottom-color: #f1a3a3; 
        }
        .xls-row:hover .xls-td:first-child { 
            box-shadow: inset 4px 0 0 #c00000; 
        }
        .xls-const { display: flex; align-items: center; padding: 0 18px; height: 100%; font-size: 11.5px; font-weight: 700; color: inherit; white-space: nowrap; }
        .xls-scroll-wrap { --col1-width: 40px; width: 100%; max-width: 100%; min-width: 0; position: relative; overflow-x: auto; overflow-y: auto; height: calc(100vh - 350px); min-height: 400px; background: white; flex-grow: 1; transition: height 0.3s ease-in-out; border-top: 1px solid #e2e8f0; }
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

        /* Dark Mode Overrides */
        html.dark body { background-color: #0f172a !important; color: #e2e8f0 !important; }
        html.dark .backdrop-blur-xl.bg-white\/90 { background-color: rgba(30,41,59,0.92) !important; }
        html.dark .xls-th { background: #0f172a !important; color: #94a3b8 !important; border-right-color: #334155 !important; border-bottom-color: #334155 !important; }
        html.dark .xls-td { background: #1e293b !important; border-right-color: #334155 !important; border-bottom-color: #334155 !important; }
        html.dark .xls-row:hover .xls-td { background-color: rgba(192, 0, 0, 0.06) !important; border-bottom-color: rgba(192, 0, 0, 0.25) !important; }
        html.dark .xls-row:hover { box-shadow: 0 10px 20px -5px rgba(0,0,0,0.4), 0 8px 8px -6px rgba(0,0,0,0.3) !important; }
        html.dark .xls-td.sticky { background: #1e293b !important; }
        html.dark .xls-th.sticky { background: #0f172a !important; }
        html.dark .xls-const { color: #cbd5e1; }
        html.dark .xls-td .text-slate-800 { color: #e2e8f0 !important; }
        html.dark .xls-td .text-slate-600 { color: #94a3b8 !important; }
        html.dark .xls-td .text-slate-500 { color: #64748b !important; }
        html.dark .xls-scroll-wrap { background: #1e293b !important; border-top-color: #334155 !important; }
        html.dark .rounded-\[2rem\] { border-color: #334155 !important; }
        html.dark .pg-btn { background: #1e293b !important; color: #e2e8f0 !important; border-color: #334155 !important; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.4) !important; }
        html.dark .pg-btn:hover:not(:disabled) { border-color: #c00000 !important; color: #c00000 !important; }
        html.dark .pg-btn:disabled { background: #0f172a !important; color: #475569 !important; }
        html.dark .glass-indicator { background: rgba(30,41,59,0.8) !important; border-color: #334155 !important; }
        html.dark #assetFilterSection { background: #1e293b !important; border-color: #334155 !important; }
        html.dark #assetTableFooter { border-top-color: #334155 !important; background: #1e293b !important; box-shadow: 0 -4px 6px -1px rgba(0,0,0,0.3) !important; }
        html.dark #assetSearchSuggestions { background: #1e293b !important; border-color: #334155 !important; }
        html.dark .border-emerald-200 { border-color: #14532d !important; }
        html.dark .bg-emerald-50 { background-color: rgba(5,46,22,0.5) !important; }
        html.dark .border-amber-200 { border-color: #78350f !important; }
        html.dark .bg-amber-50 { background-color: rgba(69,26,3,0.5) !important; }
        html.dark .border-rose-200 { border-color: #881337 !important; }
        html.dark .bg-rose-50 { background-color: rgba(76,5,25,0.5) !important; }
        html.dark .xls-td .border-slate-100 { border-color: #334155 !important; }
        html.dark .xls-td .bg-white { background-color: #0f172a !important; }
        
        /* Filter Styles */
        .filter-select-wrap { display: flex; flex-direction: column; gap: 6px; }
        .filter-select-label { font-size: 9px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.15em; color: #94a3b8; display: flex; align-items: center; gap: 6px; }
        .filter-select { width: 100%; padding: 10px 14px; font-size: 11px; font-weight: 700; border: 1.5px solid #e2e8f0; border-radius: 12px; background: #f8fafc; color: #334155; appearance: none; -webkit-appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='2.5' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19.5 8.25l-7.5 7.5-7.5-7.5'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; background-size: 14px; padding-right: 36px; cursor: pointer; transition: border-color 0.2s, box-shadow 0.2s; outline: none; }
        .filter-select:focus { border-color: #c00000; box-shadow: 0 0 0 3px rgba(192,0,0,0.08); }
        .filter-select:hover { border-color: #cbd5e1; }
        .filter-input { width: 100%; padding: 10px 14px; font-size: 11px; font-weight: 700; border: 1.5px solid #e2e8f0; border-radius: 12px; background: #f8fafc; color: #334155; outline: none; transition: border-color 0.2s, box-shadow 0.2s; }
        .filter-input:focus { border-color: #c00000; box-shadow: 0 0 0 3px rgba(192,0,0,0.08); }
        .filter-input:hover { border-color: #cbd5e1; }
        html.dark .filter-select { background-color: #1e293b; border-color: #334155; color: #e2e8f0; }
        html.dark .filter-select:focus { border-color: #c00000; }
        html.dark .filter-input { background-color: #1e293b; border-color: #334155; color: #e2e8f0; }
        html.dark .filter-input:focus { border-color: #c00000; }
        html.dark .filter-select-label { color: #64748b; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex text-slate-900 overflow-x-hidden">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll bg-slate-50">
        <div class="w-full mx-auto p-6 lg:p-10 min-h-screen flex flex-col relative z-10 gap-6">

            {{-- Page Header --}}
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 px-2 animate-fade">
                <div class="shrink-0">
                    <h2 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-red-700 to-red-500 uppercase italic leading-none drop-shadow-sm tracking-tight">AMU Stock Registry</h2>
                    <p class="text-slate-500 text-[11px] font-bold uppercase tracking-[0.25em] mt-3 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse shadow-[0_0_8px_rgba(239,68,68,0.6)]"></span>
                        Property & Supply Unit Warehouse Registry
                    </p>
                </div>

                {{-- Search Bar --}}
                <div class="flex-grow max-w-2xl relative" id="assetSearchContainer">
                    <div class="relative group">
                        <input type="text" id="assetSearchInput" oninput="debounceAssetSearch()" placeholder="SEARCH PROPERTY NO. OR DESCRIPTION..." autocomplete="off" class="w-full bg-white border-2 border-slate-100 rounded-2xl px-6 py-4 text-xs font-black uppercase tracking-widest focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-700 shadow-sm pr-12 group-hover:border-slate-200">
                        <div class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 group-hover:text-red-500 transition-colors pointer-events-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </div>
                    <div id="assetSearchSuggestions" class="absolute left-0 right-0 top-full mt-2 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 overflow-hidden hidden flex-col max-h-60 overflow-y-auto custom-scroll"></div>
                </div>

                <div class="flex items-center gap-4 shrink-0">
                    <button onclick="toggleAssetFilters()" id="toggleFilterBtn" class="px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest text-slate-600 bg-white border border-slate-200 hover:text-[#c00000] hover:-translate-y-0.5 hover:shadow-md active:translate-y-0 transition-all duration-300 flex items-center gap-2 group italic">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 group-hover:rotate-12 transition-transform duration-300"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg>
                        Filters
                    </button>
                    <a href="/dashboard" class="px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest text-slate-600 bg-white border border-slate-200 hover:text-[#c00000] hover:-translate-y-0.5 hover:shadow-md active:translate-y-0 transition-all duration-300 flex items-center gap-2 group italic">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 group-hover:-translate-x-1 transition-transform duration-300"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                        Back
                    </a>
                </div>
            </div>

            {{-- Filter Panel (Hidden by default, matches view-assets minus School/Office since it's pre-filtered) --}}
            <div id="assetFilterSection" class="bg-white rounded-[2.5rem] shadow-lg border border-slate-100 p-8 mb-8 relative z-50 animate-fade transition-all duration-300 origin-top hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-8 relative z-10">
                    {{-- PPE Type --}}
                    <div class="filter-select-wrap">
                        <label class="filter-select-label">
                            <svg class="w-3 h-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            PPE Type
                        </label>
                        <select id="assetPropertyType" class="filter-select">
                            <option value="ALL">All Assets</option>
                            <option value="RPCPPE">PPE (≥ 50k)</option>
                            <option value="RPCSP">Semi-PPE (&lt; 50k)</option>
                        </select>
                    </div>

                    {{-- Status / Condition --}}
                    <div class="filter-select-wrap">
                        <label class="filter-select-label">
                            <svg class="w-3 h-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.955 11.955 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                            Condition
                        </label>
                        <select id="assetFilterStatus" class="filter-select">
                            <option value="">All Conditions</option>
                            <option value="serviceable">Serviceable</option>
                            <option value="to_repair">To Repair</option>
                            <option value="unserviceable">Unserviceable</option>
                        </select>
                    </div>

                    {{-- Classification --}}
                    <div class="filter-select-wrap">
                        <label class="filter-select-label">
                            <svg class="w-3 h-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 00-1.884 2.233c.124 2.207 1.256 4.946 2.68 7.3a2.25 2.25 0 001.884 1.233h11.492a2.25 2.25 0 001.884-1.233c1.424-2.354 2.556-5.093 2.68-7.3a2.25 2.25 0 00-1.884-2.233m-16.5 0V6.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v3.375m16.5 0V6.375c0-.621-.504-1.125-1.125-1.125h-2.25c-.621 0-1.125.504-1.125 1.125v3.375m-9.75-3h9.75"/></svg>
                            Classification
                        </label>
                        <select id="assetFilterClassification" class="filter-select">
                            <option value="">All Classifications</option>
                        </select>
                    </div>

                    {{-- Category --}}
                    <div class="filter-select-wrap">
                        <label class="filter-select-label">
                            <svg class="w-3 h-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6.429 9.75L2.25 12l4.179 2.25m11.142 0L21.75 12l-4.179-2.25M12 5.75L6.429 9.75 12 13.75l5.571-4L12 5.75zM6.429 14.25L12 18.25l5.571-4"/></svg>
                            Category
                        </label>
                        <select id="assetFilterCategory" class="filter-select">
                            <option value="">All Categories</option>
                        </select>
                    </div>

                    {{-- Item (Article) --}}
                    <div class="filter-select-wrap">
                        <label class="filter-select-label">
                            <svg class="w-3 h-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            Item (Article)
                        </label>
                        <select id="assetFilterItem" class="filter-select">
                            <option value="">All Items</option>
                        </select>
                    </div>

                    {{-- Cost Sorting --}}
                    <div class="filter-select-wrap">
                        <label class="filter-select-label">
                            <svg class="w-3 h-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 7.5L7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5"/></svg>
                            Cost Sorting
                        </label>
                        <select id="assetFilterSort" class="filter-select">
                            <option value="">Default (Newest)</option>
                            <option value="high_to_low">Value: High to Low</option>
                            <option value="low_to_high">Value: Low to High</option>
                        </select>
                    </div>

                    {{-- Source of Acquisition --}}
                    <div class="filter-select-wrap">
                        <label class="filter-select-label">
                            <svg class="w-3 h-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                            Source of Acquisition
                        </label>
                        <select id="assetFilterSource" class="filter-select">
                            <option value="">All Sources</option>
                        </select>
                    </div>

                    {{-- Mode of Acquisition --}}
                    <div class="filter-select-wrap">
                        <label class="filter-select-label">
                            <svg class="w-3 h-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
                            Mode of Acquisition
                        </label>
                        <select id="assetFilterMode" class="filter-select">
                            <option value="">All Modes</option>
                        </select>
                    </div>

                    {{-- Date Acquired (Acceptance) --}}
                    <div class="filter-select-wrap">
                        <label class="filter-select-label">
                            <svg class="w-3 h-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z"/></svg>
                            Date Acquired
                        </label>
                        <input type="date" id="assetFilterDate" class="filter-input">
                    </div>

                    {{-- Asset Expiry / Life --}}
                    <div class="filter-select-wrap">
                        <label class="filter-select-label">
                            <svg class="w-3 h-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Asset Expiry / Life
                        </label>
                        <select id="assetFilterExpiry" class="filter-select">
                            <option value="">All (Include Expired)</option>
                            <option value="active">Active (Good Life)</option>
                            <option value="nearing_expiry">Nearing Expiry (<= 6 Mo.)</option>
                            <option value="expired">Expired (End of Life)</option>
                        </select>
                    </div>

                    {{-- Data Integrity (Empty Fields) --}}
                    <div class="filter-select-wrap">
                        <label class="filter-select-label">
                            <svg class="w-3 h-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.03L3.07 19.5h17.86L12 2.72zm0 15.75h.007v.008H12v-.008z"/></svg>
                            Data Integrity
                        </label>
                        <select id="assetFilterEmptyCol" class="filter-select">
                            <option value="">None (All Records)</option>
                            <option value="classification">Missing Classification</option>
                            <option value="category">Missing Category</option>
                            <option value="article">Missing Article</option>
                            <option value="description">Missing Description</option>
                            <option value="property_number">Missing Property No.</option>
                            <option value="serial_number">Missing Serial No.</option>
                            <option value="unit_of_measurement">Missing Unit of Measure</option>
                            <option value="acq_source">Missing Source</option>
                            <option value="mode_of_acquisition">Missing Mode</option>
                            <option value="acceptance_date">Missing Date</option>
                        </select>
                    </div>
                </div>
                <div class="mt-8 flex justify-end items-center gap-8 relative z-10 border-t border-slate-100/60 pt-6">
                    <button onclick="clearAssetFilters()" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] hover:text-[#c00000] hover:-translate-y-0.5 transition-all duration-300 italic">Clear All Filters</button>
                    <button onclick="assetFetchData()" class="px-8 py-2.5 bg-[#c00000] text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-red-800 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-red-200 active:translate-y-0 transition-all duration-300 shadow-md shadow-red-200 italic group flex items-center gap-2">
                        Apply Configuration
                        <svg class="w-3.5 h-3.5 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </button>
                </div>
            </div>

            {{-- Grid / Table View matching view-assets registry style --}}
            <div class="w-full max-w-full rounded-[2rem] border border-slate-100 shadow-lg overflow-hidden flex flex-col animate-fade relative">
                <div class="xls-scroll-wrap expanded">
                    <table id="assetTable" class="w-full border-collapse" style="min-width:1000px;">
                        <thead id="assetHeader"></thead>
                        <tbody id="assetBody"></tbody>
                    </table>
                    
                    {{-- Loading State --}}
                    <div id="assetLoading" class="absolute inset-0 bg-white/60 backdrop-blur-[2px] z-50 flex items-center justify-center hidden">
                        <div class="flex flex-col items-center gap-4">
                            <div class="w-12 h-12 border-4 border-slate-100 border-t-red-600 rounded-full animate-spin"></div>
                            <p class="text-[10px] font-black text-slate-800 uppercase tracking-widest italic">Fetching AMU Asset Records...</p>
                        </div>
                    </div>

                    {{-- Empty State --}}
                    <div id="assetEmpty" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                        <div class="inline-flex flex-col items-center gap-3 opacity-30">
                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.25em]">No assets in AMU warehouse — adjust filters</p>
                        </div>
                    </div>
                </div>

                {{-- Table Footer --}}
                <div id="assetTableFooter" class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 relative z-30 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] bg-white">
                    <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6 w-full sm:w-auto">
                        <p id="assetRowCountLabel" class="text-[9px] font-black text-slate-400 uppercase tracking-widest text-center sm:text-left">0 Rows</p>
                        <div id="assetPaginationControls" class="flex items-center justify-center gap-3 border-t sm:border-t-0 sm:border-l border-slate-200 pt-4 sm:pt-0 sm:pl-6 w-full sm:w-auto">
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
        let currentAssetTab = 'distribution';

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
            } catch (e) { console.error('Failed to fetch filters', e); }
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
                quadrant: '',
                district: '',
                dateAcquired: document.getElementById('assetFilterDate').value,
                schoolName: '',
                officeName: 'Property and Supply Unit', // Forced filter to only show PSU/AMU assets
                expiry: document.getElementById('assetFilterExpiry').value,
                sortCost: document.getElementById('assetFilterSort').value,
                emptyCol: document.getElementById('assetFilterEmptyCol').value,
                status: document.getElementById('assetFilterStatus').value,
                tab: currentAssetTab,
                search: document.getElementById('assetSearchInput')?.value || ''
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
            } catch (e) { 
                console.error('Failed to fetch assets', e);
                assetRowsData = [];
                renderAssetTable();
            } finally { loading.classList.add('hidden'); }
        }

        function renderAssetTable() {
            const header = document.getElementById('assetHeader');
            const tbody = document.getElementById('assetBody');
            
            header.innerHTML = `<tr>
                <th class="xls-th w-10 text-center sticky top-0 left-0 z-40 bg-[#f8fafc] dark:bg-[#0f172a]">#</th>
                <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:180px">Item</th>
                <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:240px">Description</th>
                <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:70px">Unit</th>
                <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:130px">Cost (₱)</th>
                <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:160px">Acquisition</th>
                <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:240px">Office/School Name</th>
                <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:150px">Property #</th>
                <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:140px">Issuance Date</th>
                <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:130px">Condition</th>
            </tr>`;
            
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
                tr.className = 'xls-row group border-b border-slate-100 cursor-pointer';
                tr.onclick = () => { window.location.href = `/assets/${row.id}/profile`; };
                const cell = (val, extra = '') => `<td class="xls-td relative ${extra}"><span class="xls-const">${val || ''}</span></td>`;
                const costCell = (val, extra = '') => `<td class="xls-td relative ${extra}"><span class="xls-const font-black text-emerald-600 italic">₱ ${Number(val || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></td>`;
                
                const srcCond = (row.condition || row.remarks || '').toLowerCase();
                let srcBadge;
                if (srcCond.includes('good') || srcCond.includes('serviceable')) {
                    srcBadge = `<span class="px-2.5 py-1 text-[9px] font-black rounded-full border border-emerald-200 bg-emerald-50 text-emerald-700 tracking-wider uppercase inline-flex items-center gap-1 shadow-sm"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>${row.condition || row.remarks || 'Good Condition'}</span>`;
                } else if (srcCond.includes('repair')) {
                    srcBadge = `<span class="px-2.5 py-1 text-[9px] font-black rounded-full border border-amber-200 bg-amber-50 text-amber-700 tracking-wider uppercase inline-flex items-center gap-1 shadow-sm"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>${row.condition || row.remarks || 'Needs Repair'}</span>`;
                } else if (srcCond.includes('unserviceable')) {
                    srcBadge = `<span class="px-2.5 py-1 text-[9px] font-black rounded-full border border-rose-200 bg-rose-50 text-rose-700 tracking-wider uppercase inline-flex items-center gap-1 shadow-sm"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>${row.condition || row.remarks || 'Unserviceable'}</span>`;
                } else {
                    srcBadge = `<span class="px-2.5 py-1 text-[9px] font-black rounded-full border border-slate-200 bg-slate-50 text-slate-400 tracking-wider uppercase inline-flex items-center gap-1 shadow-sm">${row.condition || row.remarks || 'N/A'}</span>`;
                }
                const srcCondCell = `<td class="xls-td relative"><span class="xls-const">${srcBadge}</span></td>`;
                
                tr.innerHTML = `<td class="xls-td text-center sticky left-0 w-10 z-20 bg-white dark:bg-[#1e293b]"><span class="text-[10px] font-black text-slate-500">${start + idx + 1}</span></td>
                    ${cell(row.article, 'font-bold text-slate-800')}
                    ${cell(row.description, 'text-slate-600 italic')}
                    ${cell(row.unit_of_measurement)}
                    ${costCell(row.asset_cost)}
                    ${cell(row.acq_source, 'text-blue-700 font-bold')}
                    ${cell(row.office_school_name, 'font-bold text-[#c00000]')}
                    ${cell(row.property_number, 'font-bold text-emerald-600')}
                    ${cell(row.acquisition_date)}
                    ${srcCondCell}`;
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
            document.getElementById('assetFilterExpiry').value = '';
            document.getElementById('assetFilterSort').value = '';
            document.getElementById('assetFilterEmptyCol').value = '';
            document.getElementById('assetFilterStatus').value = '';
            const searchInput = document.getElementById('assetSearchInput');
            if(searchInput) searchInput.value = '';
            assetFetchData();
        }

        let searchTimeout = null;
        async function debounceAssetSearch() {
            clearTimeout(searchTimeout);
            const input = document.getElementById('assetSearchInput').value;
            const suggestionsBox = document.getElementById('assetSearchSuggestions');
            
            if (input.length < 2) {
                suggestionsBox.classList.add('hidden');
                suggestionsBox.innerHTML = '';
                if (input.length === 0) assetFetchData();
                return;
            }

            searchTimeout = setTimeout(async () => {
                try {
                    const res = await fetch(`/api/assets/suggestions?q=${encodeURIComponent(input)}&tab=${currentAssetTab}`);
                    const data = await res.json();
                    
                    if (data.length > 0) {
                        suggestionsBox.innerHTML = '<div class="px-4 py-2 text-[8px] font-black text-slate-400 uppercase tracking-widest bg-slate-50 border-b border-slate-100 italic">Suggested Matches</div>';
                        data.forEach(item => {
                            const btn = document.createElement('button');
                            btn.className = 'custom-autocomplete-item w-full text-left px-4 py-3 text-[10px] font-black text-slate-600 tracking-widest uppercase border-b border-slate-100 last:border-0 transition-all';
                            btn.textContent = item;
                            btn.onclick = () => {
                                document.getElementById('assetSearchInput').value = item;
                                suggestionsBox.classList.add('hidden');
                                assetFetchData();
                            };
                            suggestionsBox.appendChild(btn);
                        });
                        suggestionsBox.classList.remove('hidden');
                        suggestionsBox.classList.add('flex');
                    } else {
                        suggestionsBox.classList.add('hidden');
                    }
                    
                    assetFetchData();
                } catch (e) {
                    console.error('Failed to fetch suggestions', e);
                }
            }, 300);
        }

        document.addEventListener('click', (e) => {
            const searchContainer = document.getElementById('assetSearchContainer');
            if (searchContainer && !searchContainer.contains(e.target)) {
                document.getElementById('assetSearchSuggestions')?.classList.add('hidden');
            }
        });

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

        document.addEventListener('DOMContentLoaded', () => { 
            renderAssetTable();
            assetFetchFilters(); 
            assetFetchData();
        });
    </script>
</body>
</html>
