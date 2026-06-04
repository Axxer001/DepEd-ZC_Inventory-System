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
        .xls-scroll-wrap { position: relative; overflow-x: auto; overflow-y: auto; height: calc(100vh - 350px); min-height: 400px; background: white; flex-grow: 1; transition: height 0.3s ease-in-out; border-top: 1px solid #e2e8f0; }
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


        /* Premium Header Buttons */
        .hdr-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 14px;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #475569;
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            white-space: nowrap;
            font-style: italic;
            text-decoration: none;
        }
        .hdr-btn:hover {
            color: #c00000;
            border-color: #fca5a5;
            background: #fff5f5;
            box-shadow: 0 6px 16px rgba(192, 0, 0, 0.12), 0 2px 4px rgba(192, 0, 0, 0.08);
            transform: translateY(-2px);
        }
        .hdr-btn:active { transform: translateY(0); }
        .hdr-btn svg { flex-shrink: 0; transition: transform 0.25s ease; }
        .hdr-btn:hover svg { transform: scale(1.15); }
        .hdr-btn-back:hover svg { transform: translateX(-3px) !important; }
        .hdr-btn-filter:hover svg { transform: rotate(15deg) !important; }

        /* Premium Styled Select */
        .styled-select-wrap {
            position: relative;
            display: inline-flex;
            align-items: center;
        }
        .styled-select-wrap .select-icon {
            position: absolute;
            left: 14px;
            pointer-events: none;
            display: flex;
            align-items: center;
        }
        .styled-select-wrap select {
            appearance: none;
            -webkit-appearance: none;
            padding-left: 36px;
            padding-right: 36px;
            padding-top: 10px;
            padding-bottom: 10px;
            border-radius: 14px;
            border: 1.5px solid #e2e8f0;
            background: #ffffff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #374151;
            outline: none;
            cursor: pointer;
            transition: all 0.25s ease;
        }
        .styled-select-wrap select:hover {
            border-color: #fca5a5;
            box-shadow: 0 4px 12px rgba(192, 0, 0, 0.1);
        }
        .styled-select-wrap select:focus {
            border-color: #c00000;
            box-shadow: 0 0 0 3px rgba(192, 0, 0, 0.08), 0 4px 12px rgba(192, 0, 0, 0.1);
        }
        .styled-select-wrap .caret {
            position: absolute;
            right: 12px;
            pointer-events: none;
            color: #94a3b8;
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex text-slate-900 overflow-x-hidden">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll bg-slate-50">

        <!-- Sticky Header Bar -->

        <div class="sticky top-0 z-40 backdrop-blur-xl bg-white/90 border-b border-slate-100 py-5 px-10 flex justify-between items-center shadow-sm">
            <div class="flex items-center gap-3">
                <span class="p-2.5 bg-gradient-to-br from-red-50 to-rose-100 rounded-2xl text-[#c00000] shadow-sm border border-red-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </span>
                <div>
                    <h2 class="text-2xl font-black text-slate-800 uppercase italic leading-none tracking-tight">Asset Inventory</h2>
                    <p class="text-slate-400 text-[10px] font-bold uppercase tracking-[0.2em] mt-1">Division-Wide Property &amp; Equipment Registry</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="toggleAssetColumns()" id="toggleColumnsBtn" class="hdr-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                    View All Columns
                </button>
                <button onclick="toggleAssetFilters()" id="toggleFilterBtn" class="hdr-btn hdr-btn-filter">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg>
                    Show Filters
                </button>
                <a href="/dashboard" class="hdr-btn hdr-btn-back" style="font-style:normal;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                    Back
                </a>
            </div>
        </div>

        <div class="w-full mx-auto p-6 lg:p-10 flex flex-col gap-6">

        <!-- Filter Configuration -->
        <div id="assetFilterSection" class="bg-white rounded-[2.5rem] shadow-lg border border-slate-100 p-8 mb-8 relative z-50 animate-fade transition-all duration-300 origin-top hidden">
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
                <button onclick="clearAssetFilters()" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] hover:text-[#c00000] hover:-translate-y-0.5 transition-all duration-300 italic">Clear All Filters</button>
                <button onclick="assetFetchData()" class="px-8 py-2.5 bg-[#c00000] text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-red-800 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-red-200 active:translate-y-0 transition-all duration-300 shadow-md shadow-red-200 italic group flex items-center gap-2">
                    Apply Configuration
                    <svg class="w-3.5 h-3.5 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </button>
            </div>
        </div>

        <!-- Tab Selection + Filters Row -->
        <div class="flex flex-wrap justify-start items-center gap-3 mb-5">
            {{-- Tab Pill --}}
            <div class="inline-flex p-1 bg-slate-100 rounded-2xl border border-slate-200 shadow-inner">
                <button onclick="setAssetTab('source')" id="tabBtnSource" class="relative z-10 px-6 py-2 text-[10px] font-black uppercase tracking-widest transition-all duration-300 rounded-xl text-slate-500 hover:text-slate-700">
                    Asset Source
                </button>
                <button onclick="setAssetTab('distribution')" id="tabBtnDist" class="relative z-10 px-6 py-2 text-[10px] font-black uppercase tracking-widest transition-all duration-300 rounded-xl text-white bg-[#c00000] shadow-md shadow-red-200">
                    Asset Distribution
                </button>
            </div>

            {{-- PPE Type --}}
            <div class="styled-select-wrap">
                <span class="select-icon">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </span>
                <select id="assetPropertyType" onchange="assetFetchData()">
                    <option value="ALL">All Assets</option>
                    <option value="RPCPPE">PPE (≥ 50k)</option>
                    <option value="RPCSP">Semi-PPE (&lt; 50k)</option>
                </select>
                <span class="caret">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </span>
            </div>

            {{-- Status / Condition -- Custom SVG Dropdown --}}
            <div class="relative" id="conditionDropdownWrap">
                <button type="button" id="conditionDropdownBtn"
                    onclick="toggleConditionDropdown()"
                    class="inline-flex items-center gap-2.5 bg-white border-[1.5px] border-slate-200 rounded-[14px] px-4 py-[10px] text-[12px] font-black text-slate-700 uppercase tracking-widest shadow-sm cursor-pointer transition-all duration-250 hover:border-red-300 hover:shadow-[0_4px_12px_rgba(192,0,0,0.1)] focus:outline-none">
                    <svg id="conditionSelectedIcon" class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span id="conditionSelectedLabel">All Conditions</span>
                    <svg class="w-3.5 h-3.5 text-slate-400 ml-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                {{-- Hidden real select for form value --}}
                <select id="assetFilterStatus" onchange="assetFetchData()" class="sr-only" aria-hidden="true">
                    <option value="">All Conditions</option>
                    <option value="distributed">Distributed</option>
                    <option value="not_distributed">Not Yet Distributed</option>
                    <option value="serviceable">Serviceable</option>
                    <option value="to_repair">To Repair</option>
                    <option value="unserviceable">Unserviceable</option>
                </select>
                {{-- Custom dropdown panel --}}
                <div id="conditionDropdownPanel"
                    class="hidden absolute left-0 top-full mt-2 z-50 bg-white border border-slate-100 rounded-2xl shadow-2xl shadow-slate-200/60 overflow-hidden min-w-[220px]">
                    <div class="p-1.5 flex flex-col gap-0.5">
                        {{-- All --}}
                        <button type="button" onclick="setConditionFilter('', this)"
                            class="cond-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-[11px] font-black uppercase tracking-wider text-slate-600 hover:bg-slate-50 transition-all w-full text-left">
                            <span class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                            </span>
                            All Conditions
                        </button>
                        {{-- Distributed --}}
                        <button type="button" onclick="setConditionFilter('distributed', this)"
                            class="cond-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-[11px] font-black uppercase tracking-wider text-slate-600 hover:bg-blue-50 hover:text-blue-700 transition-all w-full text-left">
                            <span class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 7.5h-.75A2.25 2.25 0 004.5 9.75v7.5a2.25 2.25 0 002.25 2.25h7.5a2.25 2.25 0 002.25-2.25v-7.5a2.25 2.25 0 00-2.25-2.25h-.75m-6 3.75l3 3m0 0l3-3m-3 3V1.5m6 9h.75a2.25 2.25 0 012.25 2.25v7.5a2.25 2.25 0 01-2.25 2.25h-7.5a2.25 2.25 0 01-2.25-2.25v-.75"/></svg>
                            </span>
                            Distributed
                        </button>
                        {{-- Not Yet Distributed --}}
                        <button type="button" onclick="setConditionFilter('not_distributed', this)"
                            class="cond-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-[11px] font-black uppercase tracking-wider text-slate-600 hover:bg-amber-50 hover:text-amber-700 transition-all w-full text-left">
                            <span class="w-7 h-7 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            Not Yet Distributed
                        </button>
                        {{-- Serviceable --}}
                        <button type="button" onclick="setConditionFilter('serviceable', this)"
                            class="cond-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-[11px] font-black uppercase tracking-wider text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 transition-all w-full text-left">
                            <span class="w-7 h-7 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            Serviceable
                        </button>
                        {{-- To Repair --}}
                        <button type="button" onclick="setConditionFilter('to_repair', this)"
                            class="cond-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-[11px] font-black uppercase tracking-wider text-slate-600 hover:bg-orange-50 hover:text-orange-700 transition-all w-full text-left">
                            <span class="w-7 h-7 rounded-lg bg-orange-100 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z"/></svg>
                            </span>
                            To Repair
                        </button>
                        {{-- Unserviceable --}}
                        <button type="button" onclick="setConditionFilter('unserviceable', this)"
                            class="cond-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-[11px] font-black uppercase tracking-wider text-slate-600 hover:bg-red-50 hover:text-red-700 transition-all w-full text-left">
                            <span class="w-7 h-7 rounded-lg bg-red-100 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            </span>
                            Unserviceable
                        </button>
                    </div>
                </div>
            </div>

            {{-- Search Bar --}}
            <div class="ml-auto w-full max-w-md relative" id="assetSearchContainer">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" id="assetSearchInput" oninput="debounceAssetSearch()" autocomplete="off" placeholder="Search Property No. or Description..." class="w-full bg-white border border-slate-200 rounded-[1.2rem] pl-10 pr-4 py-2.5 text-[10px] font-black text-slate-700 uppercase tracking-widest focus:ring-4 focus:ring-red-50 focus:border-[#c00000] transition-all outline-none shadow-sm placeholder:text-slate-400">
                
                {{-- Suggestions Dropdown --}}
                <div id="assetSearchSuggestions" class="absolute left-0 right-0 top-full mt-2 bg-white rounded-xl shadow-xl border border-slate-100 z-50 overflow-hidden hidden flex-col max-h-60 overflow-y-auto custom-scroll">
                    <!-- Suggested items populated via JS -->
                </div>
            </div>
        </div>

        <div class="rounded-[2rem] border border-slate-100 shadow-lg overflow-hidden flex flex-col animate-fade relative">
            <div class="xls-scroll-wrap expanded">
                <table id="assetTable" class="w-full border-collapse" style="min-width:1000px;">
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

            <div id="assetTableFooter" class="px-6 py-4 border-t border-slate-100 flex items-center justify-between relative z-30 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
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
        let assetShowAllColumns = false;

        function toggleAssetColumns() {
            assetShowAllColumns = !assetShowAllColumns;
            const btn = document.getElementById('toggleColumnsBtn');
            const table = document.getElementById('assetTable');
            
            if (assetShowAllColumns) {
                btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" /></svg> Hide Extra Columns`;
                table.style.minWidth = '1400px';
            } else {
                btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg> View All Columns`;
                table.style.minWidth = '1000px';
            }
            renderAssetTable();
        }

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
            } catch (e) { console.error('Failed to fetch assets', e); } finally { loading.classList.add('hidden'); }
        }

        function renderAssetTable() {
            const header = document.getElementById('assetHeader');
            const tbody = document.getElementById('assetBody');
            
            if (currentAssetTab === 'source') {
                if (assetShowAllColumns) {
                    header.innerHTML = `<tr><th class="xls-th w-10 text-center sticky left-0 z-40">#</th><th class="xls-th" style="min-width:150px">Classification</th><th class="xls-th" style="min-width:150px">Category</th><th class="xls-th" style="min-width:180px">Article</th><th class="xls-th" style="min-width:300px">Description</th><th class="xls-th" style="min-width:80px">Unit</th><th class="xls-th" style="min-width:120px">Unit Cost</th><th class="xls-th" style="min-width:80px">Total Qty</th><th class="xls-th" style="min-width:150px">Total Cost</th><th class="xls-th" style="min-width:150px">Mode</th><th class="xls-th" style="min-width:200px">Source/Supplier</th><th class="xls-th" style="min-width:150px">Date Acquired</th></tr>`;
                } else {
                    header.innerHTML = `<tr><th class="xls-th w-10 text-center sticky left-0 z-40">#</th><th class="xls-th" style="min-width:180px">Article</th><th class="xls-th" style="min-width:300px">Description</th><th class="xls-th" style="min-width:80px">Unit</th><th class="xls-th" style="min-width:80px">Total Qty</th><th class="xls-th" style="min-width:150px">Total Cost</th><th class="xls-th" style="min-width:150px">Date Acquired</th></tr>`;
                }
            } else {
                if (assetShowAllColumns) {
                    header.innerHTML = `<tr>
                        <th class="xls-th w-10 text-center sticky left-0 z-40">#</th>
                        <th class="xls-th" style="min-width:100px">Region</th>
                        <th class="xls-th" style="min-width:180px">Division</th>
                        <th class="xls-th" style="min-width:150px">Office/School Type</th>
                        <th class="xls-th" style="min-width:100px">School ID</th>
                        <th class="xls-th" style="min-width:250px">Office/School Name</th>
                        <th class="xls-th" style="min-width:150px">Nature of Occupancy</th>
                        <th class="xls-th" style="min-width:150px">Location</th>
                        <th class="xls-th" style="min-width:180px">Property No.</th>
                        <th class="xls-th" style="min-width:140px">Condition</th>
                        <th class="xls-th" style="min-width:150px">Acquisition Cost (₱)</th>
                        <th class="xls-th" style="min-width:150px">Acquisition Date</th>
                    </tr>`;
                } else {
                    header.innerHTML = `<tr>
                        <th class="xls-th w-10 text-center sticky left-0 z-40">#</th>
                        <th class="xls-th" style="min-width:100px">School ID</th>
                        <th class="xls-th" style="min-width:250px">Office/School Name</th>
                        <th class="xls-th" style="min-width:150px">Location</th>
                        <th class="xls-th" style="min-width:180px">Property No.</th>
                        <th class="xls-th" style="min-width:140px">Condition</th>
                        <th class="xls-th" style="min-width:150px">Acquisition Cost (₱)</th>
                        <th class="xls-th" style="min-width:150px">Acquisition Date</th>
                    </tr>`;
                }
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
                tr.className = 'xls-row group border-b border-slate-100 cursor-pointer';
                tr.onclick = () => { window.location.href = `/assets/${row.id}/profile`; };
                const cell = (val, extra = '') => `<td class="xls-td relative ${extra}"><span class="xls-const">${val || ''}</span></td>`;
                const costCell = (val, extra = '') => `<td class="xls-td relative ${extra}"><span class="xls-const font-black text-emerald-600 italic">₱ ${Number(val || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></td>`;
                if (currentAssetTab === 'source') {
                    if (assetShowAllColumns) {
                        tr.innerHTML = `<td class="xls-td text-center sticky left-0 w-10 z-20"><span class="text-[10px] font-black text-slate-500">${start + idx + 1}</span></td>${cell(row.classification, 'text-blue-600 font-bold')}${cell(row.category, 'text-slate-500')}${cell(row.article, 'font-bold text-slate-800')}${cell(row.description, 'text-slate-600 italic')}${cell(row.unit_of_measurement)}${costCell(row.asset_cost)}${cell(row.quantity, 'font-black text-amber-600')}${costCell(row.acquisition_cost, 'bg-inherit')}${cell(row.mode_of_acquisition)}${cell(row.acq_source, 'font-bold text-blue-600')}${cell(row.acceptance_date)}`;
                    } else {
                        tr.innerHTML = `<td class="xls-td text-center sticky left-0 w-10 z-20"><span class="text-[10px] font-black text-slate-500">${start + idx + 1}</span></td>${cell(row.article, 'font-bold text-slate-800')}${cell(row.description, 'text-slate-600 italic')}${cell(row.unit_of_measurement)}${cell(row.quantity, 'font-black text-amber-600')}${costCell(row.acquisition_cost, 'bg-inherit')}${cell(row.acceptance_date)}`;
                    }
                } else {
                    let condBadge = '';
                    const lowerCond = (row.condition || '').toLowerCase();
                    if (lowerCond.includes('serviceable') || lowerCond.includes('good')) {
                        condBadge = `<span class="px-2.5 py-1 text-[9px] font-black rounded-full border border-emerald-200 bg-emerald-50 text-emerald-700 tracking-wider uppercase inline-flex items-center gap-1 shadow-sm"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>Serviceable</span>`;
                    } else if (lowerCond.includes('repair')) {
                        condBadge = `<span class="px-2.5 py-1 text-[9px] font-black rounded-full border border-amber-200 bg-amber-50 text-amber-700 tracking-wider uppercase inline-flex items-center gap-1 shadow-sm"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>To Repair</span>`;
                    } else if (lowerCond.includes('unserviceable') || lowerCond.includes('condemned') || lowerCond.includes('disposed')) {
                        condBadge = `<span class="px-2.5 py-1 text-[9px] font-black rounded-full border border-rose-200 bg-rose-50 text-rose-700 tracking-wider uppercase inline-flex items-center gap-1 shadow-sm"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>Unserviceable</span>`;
                    } else {
                        condBadge = `<span class="px-2.5 py-1 text-[9px] font-black rounded-full border border-slate-200 bg-slate-50 text-slate-500 tracking-wider uppercase inline-flex items-center gap-1 shadow-sm">${row.condition || 'N/A'}</span>`;
                    }
                    const condCell = `<td class="xls-td relative"><span class="xls-const">${condBadge}</span></td>`;

                    if (assetShowAllColumns) {
                        tr.innerHTML = `<td class="xls-td text-center sticky left-0 w-10 z-20"><span class="text-[10px] font-black text-slate-500">${start + idx + 1}</span></td>
                            <td class="xls-td"><span class="xls-const">Region IX</span></td>
                            <td class="xls-td"><span class="xls-const">Division of Zamboanga City</span></td>
                            ${cell(row.school_type)}
                            ${cell(row.school_id)}
                            ${cell(row.office_school_name, 'font-bold text-[#c00000]')}
                            ${cell(row.nature_of_occupancy)}
                            ${cell(row.location)}
                            ${cell(row.property_number)}
                            ${condCell}
                            ${costCell(row.acquisition_cost, 'bg-inherit')}
                            ${cell(row.acquisition_date)}`;
                    } else {
                        tr.innerHTML = `<td class="xls-td text-center sticky left-0 w-10 z-20"><span class="text-[10px] font-black text-slate-500">${start + idx + 1}</span></td>
                            ${cell(row.school_id)}
                            ${cell(row.office_school_name, 'font-bold text-[#c00000]')}
                            ${cell(row.location)}
                            ${cell(row.property_number)}
                            ${condCell}
                            ${costCell(row.acquisition_cost, 'bg-inherit')}
                            ${cell(row.acquisition_date)}`;
                    }
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
            const condWrap = document.getElementById('conditionDropdownWrap');
            if (condWrap && !condWrap.contains(e.target)) {
                document.getElementById('conditionDropdownPanel')?.classList.add('hidden');
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

        // ---- Condition Custom Dropdown ----
        function toggleConditionDropdown() {
            const panel = document.getElementById('conditionDropdownPanel');
            panel.classList.toggle('hidden');
        }

        function setConditionFilter(value, btn) {
            // Update hidden select
            const sel = document.getElementById('assetFilterStatus');
            sel.value = value;

            // Update button label — grab only direct text nodes (ignore child element text)
            const rawText = Array.from(btn.childNodes)
                .filter(n => n.nodeType === Node.TEXT_NODE)
                .map(n => n.textContent.trim())
                .filter(Boolean)
                .join(' ');
            document.getElementById('conditionSelectedLabel').textContent = rawText || 'All Conditions';

            // Grab the icon SVG from chosen item and copy to button icon slot
            const itemIcon = btn.querySelector('svg');
            const displayIcon = document.getElementById('conditionSelectedIcon');
            if (itemIcon && displayIcon) {
                displayIcon.innerHTML = itemIcon.innerHTML;
                displayIcon.setAttribute('class', itemIcon.getAttribute('class') || 'w-4 h-4 shrink-0');
            }

            // Close panel
            document.getElementById('conditionDropdownPanel').classList.add('hidden');

            // Trigger filter
            assetFetchData();
        }
        // ---- End Condition Dropdown ----

        document.addEventListener('DOMContentLoaded', () => { assetFetchFilters(); assetFetchData(); });
    </script>
</body>
</html>
