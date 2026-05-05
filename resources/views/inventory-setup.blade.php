<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Setup | DepEd Zamboanga City</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .step-content { display: none; }
        .step-content.active { display: block; animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        
        @keyframes fadeIn { 
            from { opacity: 0; transform: translateY(10px) scale(0.98); } 
            to { opacity: 1; transform: translateY(0) scale(1); } 
        }

        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        .toast-enter { animation: slideInRight 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .toast-exit { animation: slideOutRight 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

        .custom-scroll::-webkit-scrollbar { width: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

        .back-btn-cool {
            background: white;
            border: 1px solid #e2e8f0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .back-btn-cool:hover {
            border-color: #c00000;
            color: #c00000;
            box-shadow: 0 10px 15px -3px rgba(192, 0, 0, 0.1);
            transform: translateX(-4px);
        }

        /* ── Excel-like registration table ── */
        .xls-th {
            padding: 9px 14px;
            font-size: 9px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #64748b;
            white-space: nowrap;
            border-right: 1px solid #e8edf2;
            border-bottom: 2px solid #dde3ea;
            background: #f4f6f9;
            position: sticky;
            top: 0;
            z-index: 5;
        }
        .xls-td {
            padding: 0;
            border-right: 1px solid #eef0f4;
            border-bottom: 1px solid #f0f3f6;
            vertical-align: middle;
            position: relative;
        }
        /* row highlight */
        .xls-row { transition: background 0.1s; }
        .xls-row:hover .xls-td { background-color: rgba(244,246,249,0.9); }
        .xls-row:hover .xls-td.xls-sticky-col { background-color: #f4f6f9; }
        /* inputs inside cells */
        .xls-input {
            width: 100%;
            padding: 11px 14px;
            font-size: 11.5px;
            font-weight: 600;
            color: #334155;
            background: rgba(0, 0, 0, 0.035); /* darker hue for typeboxes */
            border: 1px solid transparent;
            outline: none;
            box-sizing: border-box;
            line-height: 1.4;
            transition: all 0.2s;
        }
        .xls-input:focus {
            background: rgba(192,0,0,0.045);
            border-color: #c00000;
            box-shadow: 0 0 0 2px rgba(192,0,0,0.1);
        }
        .xls-input::placeholder { color: #c8d0db; font-weight: 500; }
        .xls-const {
            display: block;
            padding: 11px 14px;
            font-size: 11.5px;
            font-weight: 700;
            color: #94a3b8;
            white-space: nowrap;
            font-style: italic;
        }
        /* Scroll container: min-height = 10 rows, scrollable beyond */
        .xls-scroll-wrap {
            position: relative;
            overflow-x: auto;
            overflow-y: auto;
            min-height: 455px;   /* ~ 10 × 44px row + 2px border each */
            max-height: 455px;
        }
        /* ── Dark mode ── */
        html.dark .xls-th {
            background: #0d1525 !important;
            color: #4a5568 !important;
            border-color: #1a2535 !important;
        }
        html.dark .xls-td { border-color: #1a2535 !important; }
        html.dark .xls-row:hover .xls-td { background-color: rgba(10,18,34,0.55) !important; }
        html.dark .xls-row:hover .xls-td.xls-sticky-col { background-color: #0d1525 !important; }
        /* Typebox enhancements */
        html.dark .xls-input { background: rgba(0, 0, 0, 0.25); color: #e2e8f0; }
        html.dark .xls-input:focus { background: rgba(192,0,0,0.1); border-color: #c00000; box-shadow: 0 0 0 2px rgba(192,0,0,0.2); }
        html.dark .xls-input::placeholder { color: #475569; }

        /* Custom Autocomplete */
        .custom-autocomplete {
            position: absolute;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            max-height: 200px;
            overflow-y: auto;
            z-index: 9999;
            min-width: 150px;
            font-family: inherit;
        }
        html.dark .custom-autocomplete {
            background: #141f33;
            border-color: #1e293b;
        }
        .custom-autocomplete-item {
            padding: 10px 14px;
            font-size: 11.5px;
            font-weight: 600;
            cursor: pointer;
            color: #334155;
            transition: background 0.1s;
        }
        html.dark .custom-autocomplete-item {
            color: #cbd5e1;
        }
        .custom-autocomplete-item:hover {
            background: #f8fafc;
        }
        html.dark .custom-autocomplete-item:hover {
            background: #1a2535;
        }

        /* NEW Badge */
        .new-badge {
            position: absolute;
            top: 3px;
            right: 3px;
            font-size: 8px;
            font-weight: 900;
            background: #10b981;
            color: white;
            padding: 1px 4px;
            border-radius: 4px;
            text-transform: uppercase;
            pointer-events: none;
            z-index: 10;
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
            letter-spacing: 0.5px;
        }
        html.dark .new-badge {
            background: #059669;
            color: #ecfdf5;
            box-shadow: none;
        }

        html.dark .xls-const { color: #2e4060 !important; }
        /* Dark: section 1 card */
        html.dark #acqSourceCard { background-color: #141f33 !important; border-color: #1e2e47 !important; }
        html.dark #acqSourceCard .border-b { border-color: #1e2e47 !important; }
        html.dark #acqSourceInput {
            background-color: #0d1525 !important;
            border-color: #1e2e47 !important;
            color: #94a3b8 !important;
        }
        /* Dark: section 2 table card */
        html.dark #assetTableCard { background-color: #141f33 !important; border-color: #1e2e47 !important; }
        html.dark #assetToolbar { background-color: #141f33 !important; border-color: #1e2e47 !important; }
        html.dark #assetToolbar .bg-slate-100 { background-color: #0d1525 !important; }
        html.dark #assetToolbar .bg-slate-50 { background-color: #0d1525 !important; border-color: #1e2e47 !important; }
        html.dark #assetToolbar .text-slate-600 { color: #64748b !important; }
        html.dark .xls-scroll-wrap { background-color: #141f33 !important; }
        html.dark #assetSourceEmpty, html.dark #assetDistEmpty { background: #141f33 !important; }
        html.dark #assetSourceEmpty p, html.dark #assetDistEmpty p { color: #253550 !important; }
        html.dark #assetSourceEmpty svg, html.dark #assetDistEmpty svg { color: #253550 !important; }
        /* Dark: footer */
        html.dark #assetTableFooter { background-color: #0d1525 !important; border-color: #1e2e47 !important; }
        html.dark #assetTableFooter #rowCountLabel { color: #2e4060 !important; }
        /* Dark: sticky row num col */
        html.dark .xls-sticky-col { background-color: #141f33 !important; }
        html.dark .xls-row:hover .xls-sticky-col { background-color: #0d1525 !important; }
    </style>

</head>
<body class="bg-slate-50 min-h-screen flex text-slate-800 overflow-x-hidden relative">

    @if(session('success'))
        <div id="successToast" class="fixed top-8 right-8 z-[100] bg-emerald-50 border border-emerald-200 text-emerald-700 px-6 py-4 rounded-2xl shadow-xl flex items-center gap-3 toast-enter">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-emerald-500">
                <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
            </svg>
            <div class="flex flex-col">
                <span class="font-bold text-sm tracking-tight">Success</span>
                <span class="text-xs font-semibold opacity-90">{{ session('success') }}</span>
            </div>
            <button onclick="closeToast()" class="ml-4 text-emerald-400 hover:text-emerald-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <script>
            function closeToast() {
                const toast = document.getElementById('successToast');
                if(toast) {
                    toast.classList.remove('toast-enter');
                    toast.classList.add('toast-exit');
                    setTimeout(() => toast.remove(), 400);
                }
            }
            setTimeout(closeToast, 4000);
        </script>
    @endif

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll">
        <header class="lg:hidden bg-white border-b border-slate-200 p-4 sticky top-0 z-30 flex items-center gap-4">
            <button onclick="toggleSidebar()" class="p-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-600 hover:bg-slate-100 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>
            <div class="flex items-center gap-2">
                <img src="{{ asset('images/deped_logo.png') }}" class="h-6 w-auto">
                <span class="font-extrabold italic text-sm">DepEd ZC</span>
            </div>
        </header>

        <main id="mainContent" class="p-6 lg:p-10 max-w-5xl mx-auto w-full transition-all duration-300">
            <header class="flex justify-between items-center mb-12">
                <div>
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight italic">Inventory Setup</h2>
                    <p class="text-slate-500 text-sm font-medium italic">Zamboanga City Division Asset Management</p>
                </div>
                <button id="backBtn" onclick="goBack()" class="hidden px-6 py-3 back-btn-cool rounded-2xl text-sm font-bold text-slate-600 flex items-center gap-2 shadow-sm active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Back
                </button>
            </header>

            {{-- Step 1: Add or Edit Selection --}}
            <div id="step1" class="step-content active">
                <h3 class="text-center text-lg font-bold text-slate-400 uppercase tracking-[0.3em] mb-10">What would you like to do?</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 px-4">
                    <div onclick="nextStep(2, 'add')" class="group bg-white p-12 rounded-[3rem] shadow-xl shadow-slate-200/60 border-2 border-transparent hover:border-[#c00000] transition-all duration-300 cursor-pointer text-center">
                        <div class="w-20 h-20 bg-red-50 text-[#c00000] rounded-3xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-10 h-10">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                        <h4 class="text-3xl font-black text-slate-800 tracking-tight uppercase">Add Item</h4>
                        <p class="text-slate-400 text-xs font-bold uppercase mt-3 tracking-widest leading-tight">Register new items or equipment to the system</p>
                    </div>

                    <div onclick="nextStep(2, 'building')" class="group bg-white p-12 rounded-[3rem] shadow-xl shadow-slate-200/60 border-2 border-transparent hover:border-[#c00000] transition-all duration-300 cursor-pointer text-center">
                        <div class="w-20 h-20 bg-slate-50 text-slate-600 rounded-3xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-10 h-10">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                            </svg>
                        </div>
                        <h4 class="text-3xl font-black text-slate-800 tracking-tight uppercase">Add Building</h4>
                        <p class="text-slate-400 text-xs font-bold uppercase mt-3 tracking-widest leading-tight">Register or manage school buildings and infrastructure</p>
                    </div>
                </div>
            </div>

{{-- Step 2: Category Selection --}}
<div id="step2" class="step-content">
    <h3 id="step2Title" class="text-lg font-black text-slate-400 uppercase tracking-[0.3em] text-center mb-6 -mt-6">Select Category</h3>
    
<div id="categoryGrid" class="grid grid-cols-2 gap-6 max-w-3xl mx-auto px-4 mb-8">        
    {{-- Empty Grid --}}


</div>
</div>

{{-- ═══════ STEP: ADD NEW RECORD — Registration Form ═══════ --}}
<div id="stepAddNew" class="step-content">

    {{-- DATALISTS (shared option pools) --}}
    <datalist id="dl-acq-source">
        <option value="DepEd Central Office">
        <option value="Local Government Unit">
        <option value="Private Donation">
        <option value="Congressional Allocation">
        <option value="MOOE Fund">
    </datalist>
    <datalist id="dl-classification">
        <option value="Semi-Expendable">
        <option value="Non-Expendable">
        <option value="Expendable">
    </datalist>
    <datalist id="dl-category">@foreach($categories as $c)<option value="{{ $c->name }}">@endforeach</datalist>
    <datalist id="dl-item">@foreach($items as $i)<option value="{{ $i->name }}">@endforeach</datalist>
    <datalist id="dl-description"><option value="General"><option value="Specific"></datalist>
    <datalist id="dl-mode">
        <option value="Public Bidding">
        <option value="Direct Contracting">
        <option value="Shopping">
        <option value="Negotiated Procurement">
    </datalist>
    <datalist id="dl-personnel"></datalist>
    <datalist id="dl-position"><option value="Supply Officer"><option value="Principal"><option value="Teacher"><option value="Clerk"></datalist>
    <datalist id="dl-school-type"><option value="Elementary School"><option value="High School"><option value="Integrated School"><option value="Division Office"></datalist>
    <datalist id="dl-school-id">@foreach($allSchools as $s)<option value="{{ $s->school_id }}">@endforeach</datalist>
    <datalist id="dl-school-name">@foreach($allSchools as $s)<option value="{{ $s->name }}">@endforeach</datalist>
    <datalist id="dl-occupancy"><option value="Owned"><option value="Leased"><option value="Borrowed"></datalist>
    <datalist id="dl-location">@foreach($allSchools as $s)<option value="{{ $s->name }}">@endforeach</datalist>

    {{-- ── Section 1: Acquisition Source ── --}}
    <div id="acqSourceCard" class="mb-6 bg-white rounded-[2rem] border border-slate-100 shadow-lg overflow-hidden">
        <div class="px-6 py-3 border-b border-slate-100 flex items-center gap-3">
            <div class="w-6 h-6 bg-[#c00000] rounded-lg flex items-center justify-center text-white text-[10px] font-black shrink-0">1</div>
            <div>
                <h3 class="font-black text-slate-800 uppercase tracking-tight text-xs">Acquisition Source</h3>
                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">Identify who provided the assets</p>
            </div>
        </div>
        <div class="px-6 py-4">
            <div class="max-w-sm">
                <label class="text-[9px] font-black text-[#c00000] uppercase tracking-widest mb-1.5 block">Source of Acquisition <span class="text-red-400">*</span></label>
                <div class="relative">
                    <input type="text" id="acqSourceInput" data-col="acq-source" autocomplete="off"
                        placeholder="Type or select a source..."
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-800 outline-none focus:ring-2 focus:ring-red-100 focus:border-[#c00000] transition-all">
                </div>
            </div>
        </div>
    </div>

    {{-- ── Section 2: Asset Source / Distribution Table ── --}}
    <div id="assetTableCard" class="bg-white rounded-[2rem] border border-slate-100 shadow-lg overflow-hidden">

        {{-- Toolbar --}}
        <div id="assetToolbar" class="px-5 py-3 border-b border-slate-100 flex items-center justify-between gap-3 flex-wrap">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 bg-slate-800 rounded-xl flex items-center justify-center text-white text-xs font-black shrink-0">2</div>
                <div class="flex bg-slate-100 rounded-xl p-1 gap-1">
                    <button id="tabAssetSource" onclick="switchAssetTab('source')"
                        class="px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg bg-[#c00000] text-white shadow-sm transition-all">
                        Asset Source
                    </button>
                    <button id="tabAssetDist" onclick="switchAssetTab('distribution')"
                        class="px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg text-slate-500 hover:text-slate-700 transition-all">
                        Asset Distribution
                    </button>
                </div>
                <span id="assetTabLabel" class="hidden md:block text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">Asset Source</span>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="openBulkAddModal()"
                <button onclick="openBulkAddModal()"
                    class="flex items-center gap-2 px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-wider hover:bg-slate-100 transition-all active:scale-95">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                    Bulk Add
                </button>
                <button onclick="addAssetRow()"
                    class="flex items-center gap-2 px-4 py-2.5 bg-[#c00000] text-white rounded-xl text-[10px] font-black uppercase tracking-wider hover:bg-red-700 transition-all shadow-sm active:scale-95">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Add Row
                </button>
            </div>
        </div>

        {{-- ── Asset Source Table ── --}}
        <div id="panelAssetSource">
            <div class="xls-scroll-wrap">
            <table class="w-full border-collapse" style="min-width:1500px;">
                <thead>
                    <tr>
                        <th class="xls-th w-10 text-center sticky left-0" style="z-index:6">#</th>
                        <th class="xls-th" style="min-width:140px">Classification</th>
                        <th class="xls-th" style="min-width:140px">Category</th>
                        <th class="xls-th" style="min-width:140px">Item</th>
                        <th class="xls-th" style="min-width:180px">Description</th>
                        <th class="xls-th" style="min-width:150px">Mode</th>
                        <th class="xls-th" style="min-width:160px">Source Personnel</th>
                        <th class="xls-th" style="min-width:160px">Personnel Position</th>
                        <th class="xls-th text-right" style="min-width:120px">Cost / Unit (₱)</th>
                        <th class="xls-th text-right" style="min-width:80px">Qty</th>
                        <th class="xls-th text-right" style="min-width:110px">Useful Life (yrs)</th>
                        <th class="xls-th" style="min-width:140px">Acceptance Date</th>
                        <th class="xls-th w-10 text-center">Del</th>
                    </tr>
                </thead>
                <tbody id="assetSourceBody"></tbody>
            </table>
            {{-- Empty state inside scroll wrap --}}
            <div id="assetSourceEmpty" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="inline-flex flex-col items-center gap-3 opacity-30">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0112 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125h17.25"/></svg>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.25em]">No rows — click Add Row to begin</p>
                </div>
            </div>
            </div>{{-- /xls-scroll-wrap --}}
        </div>

        {{-- ── Asset Distribution Table ── --}}
        <div id="panelAssetDist" class="hidden">
            <div class="xls-scroll-wrap">
            <table class="w-full border-collapse" style="min-width:1400px;">
                <thead>
                    <tr>
                        <th class="xls-th w-10 text-center sticky left-0 z-10">#</th>
                        <th class="xls-th" style="min-width:90px">Region</th>
                        <th class="xls-th" style="min-width:200px">Division</th>
                        <th class="xls-th" style="min-width:160px">Office/School Type</th>
                        <th class="xls-th" style="min-width:100px">School ID</th>
                        <th class="xls-th" style="min-width:210px">Office/School Name</th>
                        <th class="xls-th" style="min-width:160px">Nature of Occupancy</th>
                        <th class="xls-th" style="min-width:160px">Location</th>
                        <th class="xls-th" style="min-width:150px">Property No.</th>
                        <th class="xls-th text-right" style="min-width:130px">Acquisition Cost (₱)</th>
                        <th class="xls-th" style="min-width:140px">Acquisition Date</th>
                        <th class="xls-th w-10 text-center">Del</th>
                    </tr>
                </thead>
                <tbody id="assetDistBody"></tbody>
            </table>
            {{-- Empty state inside scroll wrap --}}
            <div id="assetDistEmpty" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="inline-flex flex-col items-center gap-3 opacity-30">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0112 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125h17.25"/></svg>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.25em]">No rows — click Add Row to begin</p>
                </div>
            </div>
            </div>{{-- /xls-scroll-wrap --}}
        </div>

        {{-- Footer --}}
        <div id="assetTableFooter" class="px-5 py-3 border-t border-slate-100 flex items-center justify-between bg-slate-50/50">
            <p id="rowCountLabel" class="text-[9px] font-black text-slate-400 uppercase tracking-widest">0 Rows</p>
            <button onclick="submitRegistration()"
                class="px-6 py-2.5 bg-[#c00000] text-white rounded-xl font-black text-[10px] uppercase tracking-wider hover:bg-red-700 transition-all shadow-sm active:scale-95">
                Register Records
            </button>
        </div>
    </div>
    </div> <!-- end stepAddNew -->

    @include('partials.register-building-step')

    {{-- Step 3: Form Content --}}
            <div id="step3" class="step-content">
                @if($errors->any())
                    <div class="max-w-4xl mx-auto mb-6 bg-red-50 text-red-600 p-6 font-bold rounded-3xl shadow-sm border border-red-100 flex items-start gap-4">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 text-red-500 shrink-0">
                            <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <h4 class="text-sm font-black tracking-tight mb-1">Please fix the following errors:</h4>
                            <ul class="list-disc list-inside text-xs font-semibold opacity-90 space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <div class="max-w-4xl mx-auto bg-white p-10 rounded-[3rem] shadow-2xl border border-slate-50 relative overflow-visible">
                    <div id="formContent"></div>
                </div>
            </div>

        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let stepHistory = [1];
        let currentMode = '';
        let currentModule = '';

        document.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('step') === '2' && urlParams.get('mode') === 'edit') {
                nextStep(2, 'edit');
            }
            if (urlParams.get('step') === '2' && urlParams.get('mode') === 'add') {
                nextStep(2, 'add');
            }

            @if(session('success'))
                Swal.fire({
                    title: 'Registration Successful!',
                    text: @json(session('success')),
                    icon: 'success',
                    confirmButtonColor: '#10b981',
                    customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' }
                });
            @endif
        });

        const rawCategories = {{ Js::from($categories) }};
        const rawItems = {{ Js::from($items) }};
        const rawSubItems = {{ Js::from($subItems) }};
        
        const rawDistricts = @json($districts);
        const rawLds = @json($legislativeDistricts);
        const rawQuadrants = @json($quadrants);
        const allSchoolsList = @json($allSchools);
        const rawStakeholders = @json($stakeholders);
        const rawOwnerships = @json($stakeholderOwnerships);
        const districtMap = {};
        rawDistricts.forEach(d => {
            districtMap[d.name] = { ld: d.legislative_district_id, quad: d.quadrant_name.replace('Quadrant ', '') };
        });

        let selectedSchoolsArray = [];
        let selectedSubItemsArray = [];

        function nextStep(step, value) {
    if (step === 2) {
        currentMode = value;

        // Add Item
        if (value === 'add') {
            document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
            document.getElementById('stepAddNew').classList.add('active');
            document.getElementById('mainContent').classList.replace('max-w-5xl', 'max-w-full');
            stepHistory.push('addnew');
            updateBackButton();
            return;
        }

        // Add Building
        if (value === 'building') {
            document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
            document.getElementById('stepAddBuilding').classList.add('active');
            document.getElementById('mainContent').classList.replace('max-w-5xl', 'max-w-full');
            stepHistory.push('addbuilding');
            updateBackButton();
            return;
        }
    }

    if (step === 3) {
        if (value === 'school') { window.location.href = '/inventory-modifier/school'; return; }
        if (value === 'distribution') { window.location.href = '/inventory-modifier'; return; }
        currentModule = value;
        renderForm();
    }

    document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
    document.getElementById('step' + step).classList.add('active');
    stepHistory.push(step);
    updateBackButton();
}

        function goBack() {
            if (stepHistory.length > 1) {
                const leavingStep = stepHistory[stepHistory.length - 1];
                stepHistory.pop();
                const prevStep = stepHistory[stepHistory.length - 1];

                if (leavingStep === 'addnew' || leavingStep === 'addbuilding') {
                    document.getElementById('mainContent').classList.replace('max-w-full', 'max-w-5xl');
                    document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
                    document.getElementById('step1').classList.add('active');
                    updateBackButton();
                    return;
                }

                document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
                const targetId = prevStep === 'addnew' ? 'stepAddNew' : ('step' + prevStep);
                document.getElementById(targetId).classList.add('active');
                
                updateBackButton();
            }
        }

        function updateBackButton() {
            const btn = document.getElementById('backBtn');
            btn.classList.toggle('hidden', stepHistory[stepHistory.length - 1] === 1);
        }

        function filterQuadrants() {
            const ld = document.getElementById('dist_ld').value;
            const quadSelect = document.getElementById('dist_quad');
            quadSelect.innerHTML = '<option value="">Select Quadrant</option>';
            if (ld) {
                const filtered = rawQuadrants.filter(q => q.legislative_district_id == ld);
                quadSelect.innerHTML += filtered.map(q => `<option value="${q.id}">${q.name}</option>`).join('');
            }
        }

        // ══════════════════════════════════════════════════
        //  ADD NEW REGISTRATION FORM  — JS
        //  Rows are always in sync: Asset Source ↔ Asset Distribution (1-to-1)
        // ══════════════════════════════════════════════════
        let _rowNum   = 0;   // ever-incrementing ID for pairing
        let _rowCount = 0;   // live count of paired rows

        function switchAssetTab(tab) {
            const srcPanel = document.getElementById('panelAssetSource');
            const distPanel = document.getElementById('panelAssetDist');
            const tabSrc   = document.getElementById('tabAssetSource');
            const tabDst   = document.getElementById('tabAssetDist');
            const label    = document.getElementById('assetTabLabel');
            const ON  = 'px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg bg-[#c00000] text-white shadow-sm transition-all';
            const OFF = 'px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg text-slate-500 hover:text-slate-700 transition-all';
            if (tab === 'source') {
                srcPanel.classList.remove('hidden');
                distPanel.classList.add('hidden');
                tabSrc.className = ON; tabDst.className = OFF;
                label.textContent = 'Asset Source';
            } else {
                srcPanel.classList.add('hidden');
                distPanel.classList.remove('hidden');
                tabSrc.className = OFF; tabDst.className = ON;
                label.textContent = 'Asset Distribution';
            }
            updateRowCount();
        }

        // Add Row always appends to BOTH tables at once
        function addAssetRow() {
            _rowNum++;
            _rowCount++;
            // Hide both empty states
            document.getElementById('assetSourceEmpty').classList.add('hidden');
            document.getElementById('assetDistEmpty').classList.add('hidden');
            addSourceRow(_rowNum, _rowCount);
            addDistRow(_rowNum, _rowCount);
            updateRowCount();
        }

        function addSourceRow(id, displayNum) {
            const tbody = document.getElementById('assetSourceBody');
            const tr = document.createElement('tr');
            tr.id = `src-${id}`;
            tr.className = 'xls-row group border-b border-slate-100';
            tr.dataset.rowIndex = id;
            const today = new Date().toISOString().split('T')[0];
            tr.innerHTML = `
                <td class="xls-td xls-sticky-col text-center sticky left-0 w-10" style="background:inherit">
                    <span class="row-num text-[10px] font-black text-slate-300">${displayNum}</span>
                </td>
                <td class="xls-td"><input type="text" data-col="classification" autocomplete="off" class="xls-input" placeholder="e.g. Semi-Expendable"></td>
                <td class="xls-td"><input type="text" data-col="category"       autocomplete="off" class="xls-input" placeholder="Category"></td>
                <td class="xls-td"><input type="text" data-col="item"           autocomplete="off" class="xls-input" placeholder="Item"></td>
                <td class="xls-td"><input type="text" data-col="description"    autocomplete="off" class="xls-input" placeholder="Description"></td>
                <td class="xls-td"><input type="text" data-col="mode"           autocomplete="off" class="xls-input" placeholder="Mode of Procurement"></td>
                <td class="xls-td"><input type="text" data-col="personnel"      autocomplete="off" class="xls-input" placeholder="Personnel name"></td>
                <td class="xls-td"><input type="text" data-col="position"       autocomplete="off" class="xls-input" placeholder="Position"></td>
                <td class="xls-td"><input type="number" id="src-cost-${id}" oninput="calcCost(${id})" class="xls-input text-right" placeholder="0.00" min="0" step="0.01"></td>
                <td class="xls-td"><input type="number" id="src-qty-${id}"  oninput="calcCost(${id})" class="xls-input text-right" placeholder="0"    min="0" step="1"></td>
                <td class="xls-td"><input type="number" class="xls-input text-right" placeholder="0"    min="0" step="1"></td>
                <td class="xls-td"><input type="date"   class="xls-input" value="${today}"></td>
                <td class="xls-td text-center w-10">
                    <button onclick="deleteRow(${id})" class="p-1.5 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all" title="Remove row">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </td>`;
            tbody.appendChild(tr);
        }

        function addDistRow(id, displayNum) {
            const tbody = document.getElementById('assetDistBody');
            const tr = document.createElement('tr');
            tr.id = `dst-${id}`;
            tr.className = 'xls-row group border-b border-slate-100';
            tr.dataset.rowIndex = id;
            const today = new Date().toISOString().split('T')[0];
            tr.innerHTML = `
                <td class="xls-td xls-sticky-col text-center sticky left-0 w-10" style="background:inherit">
                    <span class="row-num text-[10px] font-black text-slate-300">${displayNum}</span>
                </td>
                <td class="xls-td"><span class="xls-const">Region IX</span></td>
                <td class="xls-td"><span class="xls-const">Division of Zamboanga City</span></td>
                <td class="xls-td"><input type="text"   data-col="school-type" autocomplete="off" class="xls-input" placeholder="School/Office type"></td>
                <td class="xls-td"><input type="text"   data-col="school-id"   autocomplete="off" class="xls-input" placeholder="School ID" inputmode="numeric"></td>
                <td class="xls-td"><input type="text"   data-col="school-name" autocomplete="off" class="xls-input" placeholder="School / Office name"></td>
                <td class="xls-td"><input type="text"   data-col="occupancy"   autocomplete="off" class="xls-input" placeholder="Nature of occupancy"></td>
                <td class="xls-td"><input type="text"   data-col="location"    autocomplete="off" class="xls-input" placeholder="Location"></td>
                <td class="xls-td"><input type="text" id="dst-prop-${id}" oninput="checkPropertyNumber(${id})" autocomplete="off" class="xls-input" placeholder="Property number"></td>
                <td class="xls-td"><input type="number" id="dst-cost-${id}" autocomplete="off" class="xls-input text-right bg-slate-50 dark:bg-white/5 cursor-not-allowed" placeholder="0.00" min="0" step="0.01" readonly tabindex="-1"></td>
                <td class="xls-td"><input type="date"                          autocomplete="off" class="xls-input" value="${today}"></td>
                <td class="xls-td text-center w-10">
                    <button onclick="deleteRow(${id})" class="p-1.5 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all" title="Remove row">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </td>`;
            tbody.appendChild(tr);
        }

        function calcCost(id) {
            const cost = parseFloat(document.getElementById(`src-cost-${id}`).value) || 0;
            const qty = parseFloat(document.getElementById(`src-qty-${id}`).value) || 0;
            const dstCost = document.getElementById(`dst-cost-${id}`);
            if (dstCost) dstCost.value = (cost * qty).toFixed(2);
        }

        function checkPropertyNumber(id) {
            const propInput = document.getElementById(`dst-prop-${id}`);
            const qtyInput = document.getElementById(`src-qty-${id}`);
            if (!propInput || !qtyInput) return;
            
            if (propInput.value.trim() !== '') {
                qtyInput.value = 1;
                qtyInput.readOnly = true;
                qtyInput.classList.add('bg-slate-50', 'dark:bg-white/5', 'cursor-not-allowed');
            } else {
                qtyInput.readOnly = false;
                qtyInput.classList.remove('bg-slate-50', 'dark:bg-white/5', 'cursor-not-allowed');
            }
            calcCost(id);
        }

        // Delete paired rows from BOTH tables by shared row index id
        function deleteRow(id) {
            const srcRow  = document.getElementById(`src-${id}`);
            const distRow = document.getElementById(`dst-${id}`);
            if (srcRow)  srcRow.remove();
            if (distRow) distRow.remove();
            _rowCount = document.querySelectorAll('#assetSourceBody tr').length;
            // Renumber both tables together
            const srcRows  = document.querySelectorAll('#assetSourceBody tr');
            const distRows = document.querySelectorAll('#assetDistBody tr');
            srcRows.forEach((r, i)  => { const el = r.querySelector('.row-num');  if (el) el.textContent = i + 1; });
            distRows.forEach((r, i) => { const el = r.querySelector('.row-num');  if (el) el.textContent = i + 1; });
            // Show empty states if no rows remain
            if (_rowCount === 0) {
                document.getElementById('assetSourceEmpty').classList.remove('hidden');
                document.getElementById('assetDistEmpty').classList.remove('hidden');
            }
            updateRowCount();
            updateNewLabels();
        }

        function updateRowCount() {
            const srcVisible = !document.getElementById('panelAssetSource').classList.contains('hidden');
            const label = srcVisible ? 'Asset Source' : 'Asset Distribution';
            document.getElementById('rowCountLabel').textContent =
                _rowCount + ' ' + label + ' Row' + (_rowCount !== 1 ? 's' : '') + ' (paired)';
        }

        // =============================================
        // BULK ADD MODAL LOGIC
        // =============================================
        function openBulkAddModal() {
            const m = document.getElementById('bulkAddModal');
            m.classList.remove('hidden');
            setTimeout(() => {
                m.classList.remove('opacity-0');
                m.querySelector('.transform').classList.remove('scale-95');
            }, 10);
        }

        function closeBulkAddModal() {
            const m = document.getElementById('bulkAddModal');
            m.classList.add('opacity-0');
            m.querySelector('.transform').classList.add('scale-95');
            setTimeout(() => m.classList.add('hidden'), 300);
        }

        function confirmBulkAdd() {
            const count = parseInt(document.getElementById('bulkRowCount').value) || 1;
            
            // Gather all pre-fill values
            const data = {
                classification: document.getElementById('bClassification').value,
                category: document.getElementById('bCategory').value,
                item: document.getElementById('bItem').value,
                description: document.getElementById('bDescription').value,
                personnel: document.getElementById('bPersonnel').value,
                position: document.getElementById('bPosition').value,
                cost1: document.getElementById('bCost').value,
                qty1: document.getElementById('bQty1').value,
                life: document.getElementById('bLife').value,
                date1: document.getElementById('bDate1').value,

                schoolType: document.getElementById('bSchoolType').value,
                schoolId: document.getElementById('bSchoolId').value,
                schoolName: document.getElementById('bSchoolName').value,
                occupancy: document.getElementById('bOccupancy').value,
                location: document.getElementById('bLocation').value,
                propertyNo: document.getElementById('bPropertyNo').value,
                cost2: document.getElementById('bCost2').value,
                date2: document.getElementById('bDate2').value
            };

            const startIdx = document.querySelectorAll('#assetSourceBody tr').length;

            // Generate rows
            for (let i = 0; i < count; i++) {
                addAssetRow();
            }

            const srcRows = document.querySelectorAll('#assetSourceBody tr');
            const distRows = document.querySelectorAll('#assetDistBody tr');

            for (let i = startIdx; i < startIdx + count; i++) {
                const src = srcRows[i];
                const dst = distRows[i];

                if (src) {
                    if (data.classification) setColVal(src, 'classification', data.classification);
                    if (data.category)       setColVal(src, 'category', data.category);
                    if (data.item)           setColVal(src, 'item', data.item);
                    if (data.description)    setColVal(src, 'description', data.description);
                    if (data.personnel)      setColVal(src, 'personnel', data.personnel);
                    if (data.position)       setColVal(src, 'position', data.position);
                    
                    const inputs = src.querySelectorAll('input');
                    if (data.cost1 && inputs[7]) inputs[7].value = data.cost1;
                    if (data.qty1 && inputs[8])  inputs[8].value = data.qty1;
                    if (data.life && inputs[9])  inputs[9].value = data.life;
                    if (data.date1 && inputs[10]) inputs[10].value = data.date1;
                }
                
                if (dst) {
                    if (data.schoolType) setColVal(dst, 'school-type', data.schoolType);
                    if (data.schoolId)   setColVal(dst, 'school-id', data.schoolId);
                    if (data.schoolName) setColVal(dst, 'school-name', data.schoolName);
                    if (data.occupancy)  setColVal(dst, 'occupancy', data.occupancy);
                    if (data.location)   setColVal(dst, 'location', data.location);
                    
                    const inputs = dst.querySelectorAll('input');
                    if (data.propertyNo && inputs[5]) inputs[5].value = data.propertyNo;
                    if (data.cost2 && inputs[6])      inputs[6].value = data.cost2;
                    if (data.date2 && inputs[7])      inputs[7].value = data.date2;
                }
            }

            // Sync tags
            updateNewLabels();

            // Clear modal inputs
            document.querySelectorAll('#bulkAddModal input').forEach(inp => {
                if(inp.id !== 'bulkRowCount') inp.value = '';
            });
            document.getElementById('bulkRowCount').value = '1';
            
            closeBulkAddModal();
        }

        function setColVal(row, colName, val) {
            const el = row.querySelector(`input[data-col="${colName}"]`);
            if (el) el.value = val;
        }
        function closeBulkAddModal() { document.getElementById('bulkAddModal').classList.add('hidden'); }

        function submitRegistration() {
            Swal.fire({
                title: 'Backend Not Connected',
                text: 'Registration logic will be wired up after the design is confirmed.',
                icon: 'info',
                confirmButtonColor: '#c00000',
                customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' }
            });
        }

        function renderForm() {
            const container = document.getElementById('formContent');
            const parentWrap = container.parentElement;
            
            if (currentModule === 'distribution') {
                parentWrap.classList.remove('max-w-2xl', 'overflow-hidden');
                parentWrap.classList.add('max-w-5xl', 'overflow-visible');
            } else {
                parentWrap.classList.remove('max-w-5xl', 'overflow-visible');
                parentWrap.classList.add('max-w-2xl', 'overflow-hidden');
            }

            const modeText = currentMode === 'edit' ? 'Manage' : 'Update';
            const btnColor = 'bg-[#c00000] hover:bg-red-700 shadow-red-100';
            let html = `<h4 class="text-2xl font-black text-slate-800 mb-8 uppercase tracking-tight italic">${modeText} ${currentModule}</h4>`;

            if (currentModule === 'item') {
                // ===== EDIT MODE: UPDATE / DELETE ITEMS (Full Implementation) =====
                const distOnlyStakeholders = rawStakeholders.filter(s => s.type === 'Distributor');

                const distOptHtml = distOnlyStakeholders.map(s =>
                    `<option value="${s.id}">${s.name}</option>`
                ).join('');

                html += `
                    <p class="text-slate-400 text-xs font-semibold mb-5 -mt-4 italic text-center">Select a mode, then make your selections below.</p>

                    {{-- Mode Toggle Buttons --}}
                    <div class="flex gap-3 mb-7" id="updateItemModeToggle">
                        <button type="button" id="btnModeUpdate"
                            onclick="switchUpdateItemMode('update')"
                            class="flex-1 py-3.5 rounded-2xl font-black text-sm text-center border-2 border-[#c00000] bg-red-50 text-[#c00000] transition-all">
                            ✏️ Update / Rename
                        </button>
                        <button type="button" id="btnModeDelete"
                            onclick="switchUpdateItemMode('delete')"
                            class="flex-1 py-3.5 rounded-2xl font-black text-sm text-center border-2 border-slate-200 bg-white text-slate-400 transition-all hover:border-slate-300">
                            🗑️ Delete
                        </button>
                    </div>

                    {{-- ===================== UPDATE / RENAME PANEL ===================== --}}
                    <div id="panelUpdate" class="space-y-5">

                        <div class="grid grid-cols-2 gap-3 items-center">
                            {{-- Row 1: Category --}}
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Category</label>
                                <select id="uCategoryDd"
                                    onchange="uOnCategoryChange()"
                                    class="w-full p-3.5 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-slate-700 text-sm focus:ring-2 focus:ring-red-100 cursor-pointer transition-all">
                                    <option value="">-- Select Category --</option>
                                    ${rawCategories.map(c => `<option value="${c.id}">${c.name}</option>`).join('')}
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Rename Category To</label>
                                <input type="text" id="uCategoryRename" placeholder="Leave blank to keep current name"
                                    class="w-full p-3.5 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-slate-700 text-sm focus:ring-2 focus:ring-red-100 transition-all">
                            </div>

                            {{-- Row 2: Item --}}
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Item</label>
                                <select id="uItemDd"
                                    onchange="uOnItemChange()"
                                    class="w-full p-3.5 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-slate-700 text-sm focus:ring-2 focus:ring-red-100 cursor-pointer transition-all"
                                    disabled>
                                    <option value="">-- Select Item --</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Rename Item To</label>
                                <input type="text" id="uItemRename" placeholder="Leave blank to keep current name"
                                    class="w-full p-3.5 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-slate-700 text-sm focus:ring-2 focus:ring-red-100 transition-all"
                                    disabled>
                            </div>

                            {{-- Row 3: Sub-item --}}
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Sub-item</label>
                                <select id="uSubItemDd"
                                    onchange="uOnSubItemChange()"
                                    class="w-full p-3.5 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-slate-700 text-sm focus:ring-2 focus:ring-red-100 cursor-pointer transition-all"
                                    disabled>
                                    <option value="">-- Select Sub-item --</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Rename Sub-item To</label>
                                <input type="text" id="uSubItemRename" placeholder="Leave blank to keep current name"
                                    class="w-full p-3.5 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-slate-700 text-sm focus:ring-2 focus:ring-red-100 transition-all"
                                    disabled>
                            </div>
                        </div>

                        {{-- Distributor Transfer Row --}}
                        <div class="pt-4 border-t border-slate-100">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-2">Transfer Distributor Ownership</label>
                            <p class="text-[10px] text-slate-400 font-medium ml-1 mb-3">Select a sub-item above first. The left shows the current distributor; pick a new one on the right to transfer ownership.</p>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-1">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Current Distributor</label>
                                    <select id="uCurrentDist" disabled
                                        class="w-full p-3.5 bg-slate-100 border border-slate-200 rounded-2xl outline-none font-semibold text-slate-500 text-sm cursor-not-allowed">
                                        <option value="">-- No Sub-item Selected --</option>
                                        ${distOptHtml}
                                    </select>
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Transfer To</label>
                                    <select id="uNewDist" disabled
                                        class="w-full p-3.5 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-slate-700 text-sm focus:ring-2 focus:ring-red-100 cursor-pointer transition-all">
                                        <option value="">-- Select New Distributor --</option>
                                        ${distOptHtml}
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Update Panel Action Buttons --}}
                        <div class="flex gap-3 pt-2">
                            <button type="button" onclick="uClearAll()"
                                class="flex-1 py-4 rounded-2xl font-black text-sm border-2 border-slate-200 text-slate-500 hover:border-slate-300 hover:bg-slate-50 transition-all active:scale-95">
                                Clear
                            </button>
                            <button type="button" onclick="uSaveChanges()"
                                class="flex-[2] py-4 rounded-2xl font-black text-sm bg-[#c00000] hover:bg-red-700 text-white shadow-lg shadow-red-100 transition-all hover:-translate-y-0.5 active:scale-95">
                                Save Changes
                            </button>
                        </div>
                    </div>

                    {{-- ===================== DELETE PANEL ===================== --}}
                    <div id="panelDelete" class="space-y-5 hidden">

                        <div class="grid grid-cols-2 gap-3 items-start">
                            {{-- Row 1: Category --}}
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Category</label>
                                <select id="dCategoryDd"
                                    onchange="dOnCategoryChange()"
                                    class="w-full p-3.5 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-slate-700 text-sm focus:ring-2 focus:ring-red-100 cursor-pointer transition-all">
                                    <option value="">-- Select Category --</option>
                                    ${rawCategories.map(c => `<option value="${c.id}">${c.name}</option>`).join('')}
                                </select>
                            </div>
                            <div class="flex flex-col justify-end space-y-1 pb-0.5">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 invisible">Label</label>
                                <label class="flex items-center gap-3 p-3.5 bg-red-50 border border-red-100 rounded-2xl cursor-pointer group">
                                    <input type="checkbox" id="dCategoryChk" onchange="dOnCategoryChkChange()"
                                        class="w-4 h-4 rounded accent-[#c00000] cursor-pointer">
                                    <span class="text-sm font-black text-red-700">Delete Category</span>
                                </label>
                                <p id="dCategoryWarn" class="hidden text-[10px] font-bold text-red-600 ml-1 leading-tight mt-1">
                                    ⚠️ All items and sub-items under this category will also be deleted.
                                </p>
                            </div>

                            {{-- Row 2: Item --}}
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Item</label>
                                <select id="dItemDd"
                                    onchange="dOnItemChange()"
                                    class="w-full p-3.5 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-slate-700 text-sm focus:ring-2 focus:ring-red-100 cursor-pointer transition-all"
                                    disabled>
                                    <option value="">-- Select Item --</option>
                                </select>
                            </div>
                            <div class="flex flex-col justify-end space-y-1 pb-0.5">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 invisible">Label</label>
                                <label class="flex items-center gap-3 p-3.5 bg-red-50 border border-red-100 rounded-2xl cursor-pointer group" id="dItemChkWrap">
                                    <input type="checkbox" id="dItemChk" onchange="dOnItemChkChange()"
                                        class="w-4 h-4 rounded accent-[#c00000] cursor-pointer" disabled>
                                    <span class="text-sm font-black text-red-700">Delete Item</span>
                                </label>
                                <p id="dItemWarn" class="hidden text-[10px] font-bold text-red-600 ml-1 leading-tight mt-1">
                                    ⚠️ All sub-items under this item will also be deleted.
                                </p>
                            </div>

                            {{-- Row 3: Sub-item --}}
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Sub-item</label>
                                <select id="dSubItemDd"
                                    class="w-full p-3.5 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-slate-700 text-sm focus:ring-2 focus:ring-red-100 cursor-pointer transition-all"
                                    disabled>
                                    <option value="">-- Select Sub-item --</option>
                                </select>
                            </div>
                            <div class="flex flex-col justify-end space-y-1 pb-0.5">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 invisible">Label</label>
                                <label class="flex items-center gap-3 p-3.5 bg-red-50 border border-red-100 rounded-2xl cursor-pointer group" id="dSubItemChkWrap">
                                    <input type="checkbox" id="dSubItemChk"
                                        class="w-4 h-4 rounded accent-[#c00000] cursor-pointer" disabled>
                                    <span class="text-sm font-black text-red-700">Delete Sub-item</span>
                                </label>
                            </div>
                        </div>

                        {{-- Delete Panel Action Buttons --}}
                        <div class="flex gap-3 pt-2">
                            <button type="button" onclick="dClearAll()"
                                class="flex-1 py-4 rounded-2xl font-black text-sm border-2 border-slate-200 text-slate-500 hover:border-slate-300 hover:bg-slate-50 transition-all active:scale-95">
                                Clear
                            </button>
                            <button type="button" onclick="dSaveChanges()"
                                class="flex-[2] py-4 rounded-2xl font-black text-sm bg-[#c00000] hover:bg-red-700 text-white shadow-lg shadow-red-100 transition-all hover:-translate-y-0.5 active:scale-95">
                                Confirm Delete
                            </button>
                        </div>
                    </div>
                `;

            }

            container.innerHTML = html;
        }


        
        function getDistributorOptionsHtml() {
            let html = '<option value="">-- Distributor --</option>';
            const distributors = rawStakeholders.filter(s => s.type === 'Distributor');
            distributors.forEach(d => {
                html += `<option value="${d.id}">${d.name}</option>`;
            });
            return html;
        }

        function populateSourceStakeholders() {
            const select = document.getElementById('globalSourceDistributor');
            if (!select) return;
            let html = '<option value="">-- Select Source Distributor --</option>';
            
            const grouped = rawStakeholders.reduce((acc, obj) => {
                const key = obj.type || 'Other';
                if (!acc[key]) acc[key] = [];
                acc[key].push(obj);
                return acc;
            }, {});
            
            for (const type in grouped) {
                html += `<optgroup label="${type} Stakeholders">`;
                grouped[type].forEach(s => {
                    html += `<option value="${s.id}" data-name="${s.name}">${s.name}</option>`;
                });
                html += `</optgroup>`;
            }
            select.innerHTML = html;
            
            // Auto-select System Warehouse if it exists
            const systemWarehouse = rawStakeholders.find(s => s.type === 'System' && s.name.includes('Warehouse'));
            if (systemWarehouse) {
                select.value = systemWarehouse.id;
            }
        }



        let categoryDuplicateBlocked = false;

        function rebuildCategoryDropdown() {
            const dropdown = document.getElementById('categoryDropdownList');
            let html = '<div class="p-3 text-xs text-slate-400 font-bold uppercase tracking-widest">Select existing category</div>';
            if (rawCategories.length === 0) {
                html += '<div class="px-4 py-3 text-sm text-slate-400 italic">No existing categories</div>';
            } else {
                rawCategories.forEach(c => {
                    html += `<div onclick="selectExistingCategory(${c.id}, '${c.name.replace(/'/g, "\\'")}')"
                                 class="px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-red-50 hover:text-[#c00000] cursor-pointer transition-colors">${c.name}</div>`;
                });
            }
            html += `<div onclick="clearCategorySelection()" class="px-4 py-3 text-xs font-bold text-slate-400 hover:bg-slate-50 cursor-pointer border-t border-slate-100 transition-colors">✕ Clear selection (type new category)</div>`;
            dropdown.innerHTML = html;
        }



        function clearCategorySelection() {
            document.getElementById('existingCategoryId').value = '';
            document.getElementById('categoryName').value = '';
            document.getElementById('categoryName').readOnly = false;
            document.getElementById('categoryName').classList.remove('bg-emerald-50', 'border-emerald-200', 'bg-blue-50', 'border-blue-400');
            document.getElementById('categoryExistingHint').classList.add('hidden');
            const newHint = document.getElementById('categoryNewHint');
            if(newHint) newHint.classList.add('hidden');
            document.getElementById('categoryDropdownList').classList.add('hidden');
            document.getElementById('categoryName').focus();
            checkCategoryDuplicate();
        }




        // =============================================
        // UPDATE ITEM: PANEL TOGGLE
        // =============================================
        function switchUpdateItemMode(mode) {
            const panelUpdate = document.getElementById('panelUpdate');
            const panelDelete = document.getElementById('panelDelete');
            const btnUpdate   = document.getElementById('btnModeUpdate');
            const btnDelete   = document.getElementById('btnModeDelete');
            if (!panelUpdate || !panelDelete) return;

            if (mode === 'update') {
                panelUpdate.classList.remove('hidden');
                panelDelete.classList.add('hidden');
                btnUpdate.className = 'flex-1 py-3.5 rounded-2xl font-black text-sm text-center border-2 border-[#c00000] bg-red-50 text-[#c00000] transition-all';
                btnDelete.className  = 'flex-1 py-3.5 rounded-2xl font-black text-sm text-center border-2 border-slate-200 bg-white text-slate-400 transition-all hover:border-slate-300';
            } else {
                panelUpdate.classList.add('hidden');
                panelDelete.classList.remove('hidden');
                btnDelete.className  = 'flex-1 py-3.5 rounded-2xl font-black text-sm text-center border-2 border-[#c00000] bg-red-50 text-[#c00000] transition-all';
                btnUpdate.className = 'flex-1 py-3.5 rounded-2xl font-black text-sm text-center border-2 border-slate-200 bg-white text-slate-400 transition-all hover:border-slate-300';
            }
        }

        // =============================================
        // UPDATE / RENAME PANEL LOGIC
        // =============================================

        function uOnCategoryChange() {
            const catId = document.getElementById('uCategoryDd').value;
            const itemDd = document.getElementById('uItemDd');
            const itemRename = document.getElementById('uItemRename');

            // Reset downstream
            itemDd.innerHTML = '<option value="">-- Select Item --</option>';
            itemDd.disabled = !catId;
            if (itemRename) { itemRename.value = ''; itemRename.disabled = !catId; }
            uResetSubItem();

            if (catId) {
                const filtered = rawItems.filter(i => String(i.category_id) === String(catId));
                filtered.forEach(i => {
                    itemDd.innerHTML += `<option value="${i.id}">${i.name}</option>`;
                });
            }
            uResetDistributor();
        }

        function uOnItemChange() {
            const itemId = document.getElementById('uItemDd').value;
            const subDd  = document.getElementById('uSubItemDd');
            const subRename = document.getElementById('uSubItemRename');

            subDd.innerHTML = '<option value="">-- Select Sub-item --</option>';
            subDd.disabled = !itemId;
            if (subRename) { subRename.value = ''; subRename.disabled = !itemId; }
            uResetDistributor();

            if (itemId) {
                const filtered = rawSubItems.filter(s => String(s.item_id) === String(itemId));
                filtered.forEach(s => {
                    subDd.innerHTML += `<option value="${s.id}">${s.name}</option>`;
                });
            }
        }

        function uOnSubItemChange() {
            const subId  = document.getElementById('uSubItemDd').value;
            const curDist = document.getElementById('uCurrentDist');
            const newDist = document.getElementById('uNewDist');

            if (!subId) {
                uResetDistributor();
                return;
            }

            // Enable new-distributor dropdown
            newDist.disabled = false;
            newDist.classList.remove('cursor-not-allowed', 'bg-slate-100', 'text-slate-400');
            newDist.classList.add('cursor-pointer', 'bg-slate-50', 'text-slate-700');

            // Auto-fill current distributor
            const sub = rawSubItems.find(s => String(s.id) === String(subId));
            if (sub && sub.distributor_id) {
                curDist.value = String(sub.distributor_id);
            } else {
                curDist.value = '';
            }
        }

        function uResetSubItem() {
            const subDd = document.getElementById('uSubItemDd');
            const subRename = document.getElementById('uSubItemRename');
            if (subDd) { subDd.innerHTML = '<option value="">-- Select Sub-item --</option>'; subDd.disabled = true; }
            if (subRename) { subRename.value = ''; subRename.disabled = true; }
            uResetDistributor();
        }

        function uResetDistributor() {
            const curDist = document.getElementById('uCurrentDist');
            const newDist = document.getElementById('uNewDist');
            if (curDist) { curDist.value = ''; }
            if (newDist) {
                newDist.value = '';
                newDist.disabled = true;
                newDist.classList.remove('cursor-pointer', 'bg-slate-50', 'text-slate-700');
                newDist.classList.add('cursor-not-allowed', 'bg-slate-100', 'text-slate-500');
            }
        }

        function uClearAll() {
            const dd = (id) => document.getElementById(id);
            if (dd('uCategoryDd'))  { dd('uCategoryDd').value = ''; }
            if (dd('uCategoryRename')) { dd('uCategoryRename').value = ''; }
            if (dd('uItemDd'))      { dd('uItemDd').innerHTML = '<option value="">-- Select Item --</option>'; dd('uItemDd').disabled = true; }
            if (dd('uItemRename'))  { dd('uItemRename').value = ''; dd('uItemRename').disabled = true; }
            uResetSubItem();
        }

        async function uSaveChanges() {
            const catId       = document.getElementById('uCategoryDd')?.value;
            const catRename   = document.getElementById('uCategoryRename')?.value.trim();
            const itemId      = document.getElementById('uItemDd')?.value;
            const itemRename  = document.getElementById('uItemRename')?.value.trim();
            const subId       = document.getElementById('uSubItemDd')?.value;
            const subRename   = document.getElementById('uSubItemRename')?.value.trim();
            const curDistId   = document.getElementById('uCurrentDist')?.value;
            const newDistId   = document.getElementById('uNewDist')?.value;

            // Build a summary of changes
            const changes = [];
            if (catId && catRename)  changes.push(`Rename category to "${catRename}"`);
            if (itemId && itemRename) changes.push(`Rename item to "${itemRename}"`);
            if (subId && subRename)  changes.push(`Rename sub-item to "${subRename}"`);
            if (subId && newDistId && newDistId !== curDistId) changes.push(`Transfer sub-item distributor`);

            if (changes.length === 0) {
                Swal.fire({
                    title: 'Nothing to Save',
                    text: 'Please make a selection and fill in at least one rename field or select a new distributor.',
                    icon: 'info',
                    confirmButtonColor: '#c00000',
                    customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' }
                });
                return;
            }

            const result = await Swal.fire({
                title: 'Confirm Changes',
                html: `<div class="text-left text-sm space-y-1">${changes.map(c => `<div class="flex gap-2"><span class="text-emerald-500">✓</span><span>${c}</span></div>`).join('')}</div>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#c00000',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Save Changes',
                cancelButtonText: 'Cancel',
                customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6', cancelButton: 'rounded-xl font-bold px-6' }
            });

            if (!result.isConfirmed) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const errors = [];
            const successes = [];

            // --- Rename calls ---
            const renameTasks = [];
            if (catId && catRename)   renameTasks.push({ type: 'category', id: catId, new_name: catRename });
            if (itemId && itemRename) renameTasks.push({ type: 'item',     id: itemId, new_name: itemRename });
            if (subId && subRename)   renameTasks.push({ type: 'sub_item', id: subId,  new_name: subRename });

            for (const task of renameTasks) {
                try {
                    const res = await fetch('/inventory-setup/rename', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: JSON.stringify(task)
                    });
                    const data = await res.json();
                    if (data.success) {
                        successes.push(data.message);
                        // Update local rawData so re-renders reflect changes instantly
                        if (task.type === 'category') { const c = rawCategories.find(x => String(x.id) === String(task.id)); if (c) c.name = task.new_name; }
                        if (task.type === 'item')     { const i = rawItems.find(x => String(x.id) === String(task.id));     if (i) i.name = task.new_name; }
                        if (task.type === 'sub_item') { const s = rawSubItems.find(x => String(x.id) === String(task.id));  if (s) s.name = task.new_name; }
                    } else {
                        errors.push(data.message);
                    }
                } catch (e) {
                    errors.push('Network error during rename.');
                }
            }

            // --- Transfer distributor call ---
            if (subId && newDistId && newDistId !== curDistId) {
                try {
                    const res = await fetch('/inventory-setup/transfer-distributor', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: JSON.stringify({ sub_item_id: subId, new_distributor_id: newDistId })
                    });
                    const data = await res.json();
                    if (data.success) {
                        successes.push(data.message);
                        // Update local rawSubItems distributor reference
                        const s = rawSubItems.find(x => String(x.id) === String(subId));
                        if (s) { s.distributor_id = newDistId; const nd = rawStakeholders.find(x => String(x.id) === String(newDistId)); if (nd) s.distributor_name = nd.name; }
                    } else {
                        errors.push(data.message);
                    }
                } catch (e) {
                    errors.push('Network error during distributor transfer.');
                }
            }

            if (errors.length > 0) {
                Swal.fire({
                    title: 'Some Changes Failed',
                    html: `<div class="text-left text-sm space-y-1">
                        ${successes.map(m => `<div class="text-emerald-600">✓ ${m}</div>`).join('')}
                        ${errors.map(m => `<div class="text-red-600">✗ ${m}</div>`).join('')}
                    </div>`,
                    icon: 'warning',
                    confirmButtonColor: '#c00000',
                    customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' }
                });
            } else {
                Swal.fire({
                    title: 'Changes Saved!',
                    text: successes.join(' '),
                    icon: 'success',
                    confirmButtonColor: '#10b981',
                    customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' }
                });
                uClearAll();
            }
        }

        // =============================================
        // DELETE PANEL LOGIC
        // =============================================

        function dOnCategoryChange() {
            const catId = document.getElementById('dCategoryDd').value;
            const itemDd = document.getElementById('dItemDd');
            const itemChk = document.getElementById('dItemChk');

            // Reset item+subitem downstream
            itemDd.innerHTML = '<option value="">-- Select Item --</option>';
            itemDd.disabled  = !catId || document.getElementById('dCategoryChk').checked;
            if (itemChk) { itemChk.checked = false; itemChk.disabled = !catId || document.getElementById('dCategoryChk').checked; }
            dResetSubItem();

            if (catId) {
                const filtered = rawItems.filter(i => String(i.category_id) === String(catId));
                filtered.forEach(i => { itemDd.innerHTML += `<option value="${i.id}">${i.name}</option>`; });
            }
        }

        function dOnItemChange() {
            const itemId = document.getElementById('dItemDd').value;
            const subDd  = document.getElementById('dSubItemDd');
            const subChk = document.getElementById('dSubItemChk');

            subDd.innerHTML = '<option value="">-- Select Sub-item --</option>';
            subDd.disabled  = !itemId || document.getElementById('dItemChk').checked;
            if (subChk) { subChk.checked = false; subChk.disabled = !itemId || document.getElementById('dItemChk').checked; }

            if (itemId) {
                const filtered = rawSubItems.filter(s => String(s.item_id) === String(itemId));
                filtered.forEach(s => { subDd.innerHTML += `<option value="${s.id}">${s.name}</option>`; });
            }
        }

        function dOnCategoryChkChange() {
            const chk    = document.getElementById('dCategoryChk');
            const warn   = document.getElementById('dCategoryWarn');
            const itemDd = document.getElementById('dItemDd');
            const itemChk = document.getElementById('dItemChk');
            const subDd  = document.getElementById('dSubItemDd');
            const subChk = document.getElementById('dSubItemChk');
            const itemWarn = document.getElementById('dItemWarn');

            if (chk.checked) {
                warn.classList.remove('hidden');
                itemDd.disabled = true;
                subDd.disabled  = true;
                if (itemChk) { itemChk.checked = false; itemChk.disabled = true; }
                if (subChk)  { subChk.checked  = false; subChk.disabled  = true; }
                if (itemWarn) itemWarn.classList.add('hidden');
            } else {
                warn.classList.add('hidden');
                const catId = document.getElementById('dCategoryDd').value;
                itemDd.disabled  = !catId;
                if (itemChk) itemChk.disabled = !catId;
                // Sub-item stays disabled until item is chosen
                subDd.disabled = true;
                if (subChk) subChk.disabled = true;
            }
        }

        function dOnItemChkChange() {
            const chk   = document.getElementById('dItemChk');
            const warn  = document.getElementById('dItemWarn');
            const subDd = document.getElementById('dSubItemDd');
            const subChk = document.getElementById('dSubItemChk');

            if (chk.checked) {
                warn.classList.remove('hidden');
                subDd.disabled = true;
                if (subChk) { subChk.checked = false; subChk.disabled = true; }
            } else {
                warn.classList.add('hidden');
                const itemId = document.getElementById('dItemDd').value;
                subDd.disabled = !itemId;
                if (subChk) subChk.disabled = !itemId;
            }
        }

        function dResetSubItem() {
            const subDd  = document.getElementById('dSubItemDd');
            const subChk = document.getElementById('dSubItemChk');
            if (subDd)  { subDd.innerHTML = '<option value="">-- Select Sub-item --</option>'; subDd.disabled = true; }
            if (subChk) { subChk.checked = false; subChk.disabled = true; }
        }

        function dClearAll() {
            const dd = (id) => document.getElementById(id);
            if (dd('dCategoryDd'))  { dd('dCategoryDd').value = ''; }
            if (dd('dCategoryChk')) { dd('dCategoryChk').checked = false; }
            if (dd('dCategoryWarn')) dd('dCategoryWarn').classList.add('hidden');
            if (dd('dItemDd'))      { dd('dItemDd').innerHTML = '<option value="">-- Select Item --</option>'; dd('dItemDd').disabled = true; }
            if (dd('dItemChk'))     { dd('dItemChk').checked = false; dd('dItemChk').disabled = true; }
            if (dd('dItemWarn'))    dd('dItemWarn').classList.add('hidden');
            dResetSubItem();
        }

        async function dSaveChanges() {
            const catChk    = document.getElementById('dCategoryChk');
            const itemChk   = document.getElementById('dItemChk');
            const subChk    = document.getElementById('dSubItemChk');
            const catId     = document.getElementById('dCategoryDd')?.value;
            const itemId    = document.getElementById('dItemDd')?.value;
            const subId     = document.getElementById('dSubItemDd')?.value;

            let deleteType = null;
            let deleteId   = null;
            let confirmMsg = '';

            if (catChk?.checked && catId) {
                deleteType = 'category';
                deleteId   = catId;
                const catName = document.getElementById('dCategoryDd').options[document.getElementById('dCategoryDd').selectedIndex]?.text || 'this category';
                confirmMsg = `Delete category "<b>${catName}</b>" and ALL its items, sub-items, and ownership records?`;
            } else if (itemChk?.checked && itemId) {
                deleteType = 'item';
                deleteId   = itemId;
                const itemName = document.getElementById('dItemDd').options[document.getElementById('dItemDd').selectedIndex]?.text || 'this item';
                confirmMsg = `Delete item "<b>${itemName}</b>" and ALL its sub-items and ownership records?`;
            } else if (subChk?.checked && subId) {
                deleteType = 'sub_item';
                deleteId   = subId;
                const subName = document.getElementById('dSubItemDd').options[document.getElementById('dSubItemDd').selectedIndex]?.text || 'this sub-item';
                confirmMsg = `Delete sub-item "<b>${subName}</b>" and all its ownership records?`;
            }

            if (!deleteType || !deleteId) {
                Swal.fire({
                    title: 'Nothing to Delete',
                    text: 'Please select a record and check the corresponding Delete checkbox.',
                    icon: 'info',
                    confirmButtonColor: '#c00000',
                    customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' }
                });
                return;
            }

            const result = await Swal.fire({
                title: 'Confirm Deletion',
                html: `<div class="text-sm text-slate-700">${confirmMsg}</div><div class="text-xs text-red-500 font-bold mt-3">⚠️ This action cannot be undone.</div>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#c00000',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
                customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6', cancelButton: 'rounded-xl font-bold px-6' }
            });

            if (!result.isConfirmed) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            try {
                const res = await fetch('/inventory-setup/delete', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({ type: deleteType, id: deleteId })
                });
                const data = await res.json();
                if (data.success) {
                    // Remove deleted records from local JS arrays for instant UI refresh
                    if (deleteType === 'category') {
                        const itemIds = rawItems.filter(i => String(i.category_id) === String(deleteId)).map(i => i.id);
                        rawItems.splice(0, rawItems.length, ...rawItems.filter(i => String(i.category_id) !== String(deleteId)));
                        rawSubItems.splice(0, rawSubItems.length, ...rawSubItems.filter(s => !itemIds.map(String).includes(String(s.item_id))));
                        rawCategories.splice(0, rawCategories.length, ...rawCategories.filter(c => String(c.id) !== String(deleteId)));
                    } else if (deleteType === 'item') {
                        rawSubItems.splice(0, rawSubItems.length, ...rawSubItems.filter(s => String(s.item_id) !== String(deleteId)));
                        rawItems.splice(0, rawItems.length, ...rawItems.filter(i => String(i.id) !== String(deleteId)));
                    } else if (deleteType === 'sub_item') {
                        rawSubItems.splice(0, rawSubItems.length, ...rawSubItems.filter(s => String(s.id) !== String(deleteId)));
                    }

                    Swal.fire({
                        title: 'Deleted!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonColor: '#10b981',
                        customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' }
                    });
                    dClearAll();
                } else {
                    Swal.fire({
                        title: 'Delete Failed',
                        text: data.message,
                        icon: 'error',
                        confirmButtonColor: '#c00000',
                        customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' }
                    });
                }
            } catch (e) {
                Swal.fire({ title: 'Network Error', text: 'Could not reach the server.', icon: 'error', confirmButtonColor: '#c00000' });
            }
        }

        // =============================================
        // ASSET DISTRIBUTION MODULE
        // =============================================
        let preSelectedSchools = []; // Array of objects { id, name, uid } to allow duplicates
        let distTabsData = []; // State for each tab
        let currentActiveTab = 0;

        // --- Phase 1: Pre-selection ---
        function switchRecipTab(tab) {
            document.getElementById('currentRecipTab').value = tab;
            const btnSchool = document.getElementById('tabRecipSchoolBtn');
            const btnIndiv = document.getElementById('tabRecipIndivBtn');
            const search = document.getElementById('preDistSchoolSearch');
            
            if (tab === 'school') {
                btnSchool.className = "text-[10px] font-bold px-3 py-1 rounded-md bg-white shadow-sm text-slate-700 transition-all";
                btnIndiv.className = "text-[10px] font-bold px-3 py-1 rounded-md text-slate-500 hover:text-slate-700 transition-all bg-transparent";
                search.placeholder = "Search schools...";
            } else {
                btnIndiv.className = "text-[10px] font-bold px-3 py-1 rounded-md bg-white shadow-sm text-slate-700 transition-all";
                btnSchool.className = "text-[10px] font-bold px-3 py-1 rounded-md text-slate-500 hover:text-slate-700 transition-all bg-transparent";
                search.placeholder = "Search individual records or offices...";
            }
            
            filterPreDistSchools();
            document.getElementById('preDistSchoolSearch').focus();
        }






        // --- Phase 2: Tabs ---








        // Calculate effective remaining stock for a specific sub-item ID (which is distinct per distributor)




        // When the user changes which distributor's stock they want to deduct from









        // =============================================
        // RENAME MODULE — Update Items logic
        // =============================================
        let renameTargetId   = null;
        let renameTargetType = null;
        let renameTargetName = null;

        function onRenameTypeChange() {
            const type = document.getElementById('renameType').value;
            renameTargetId   = null;
            renameTargetType = type || null;
            renameTargetName = null;

            const cascadeRow  = document.getElementById('renameCascadeRow');
            const catWrap     = document.getElementById('renameCatWrap');
            const itemWrap    = document.getElementById('renameItemWrap');
            const subWrap     = document.getElementById('renameSubWrap');
            const inputWrap   = document.getElementById('renameInputWrap');
            const submitBtn   = document.getElementById('renameSubmitBtn');

            // Reset all child selects
            document.getElementById('renameCatSelect').value  = '';
            document.getElementById('renameItemSelect').innerHTML = '<option value="">-- Choose Item --</option>';
            document.getElementById('renameSubSelect').innerHTML  = '<option value="">-- Choose Sub-Item --</option>';
            document.getElementById('renameNewName').value     = '';
            document.getElementById('renameCurrentHint').textContent = '';

            if (typeof hideActionUI === 'function') hideActionUI();

            if (!type) {
                cascadeRow.classList.add('hidden');
                catWrap.classList.add('hidden');
                itemWrap.classList.add('hidden');
                subWrap.classList.add('hidden');
                return;
            }

            cascadeRow.classList.remove('hidden');
            // Category dropdown always shows
            catWrap.classList.remove('hidden');
            // Item dropdown shows for 'item' and 'sub_item'
            itemWrap.classList.toggle('hidden', type === 'category');
            // Sub-item dropdown shows only for 'sub_item'
            subWrap.classList.toggle('hidden', type !== 'sub_item');
        }

        function onRenameCatChange() {
            const type       = document.getElementById('renameType').value;
            const catSelect  = document.getElementById('renameCatSelect');
            const catId      = catSelect.value;
            const catName    = catId ? catSelect.options[catSelect.selectedIndex].text : '';

            const itemSelect = document.getElementById('renameItemSelect');
            const subSelect  = document.getElementById('renameSubSelect');

            renameTargetId   = null;
            renameTargetName = null;

            // Reset dependent dropdowns
            itemSelect.innerHTML = '<option value="">-- Choose Item --</option>';
            subSelect.innerHTML  = '<option value="">-- Choose Sub-Item --</option>';
            document.getElementById('renameNewName').value = '';
            document.getElementById('renameCurrentHint').textContent = '';
            if (typeof hideActionUI === 'function') hideActionUI();

            if (type === 'category') {
                // Selecting a category IS the target
                if (catId) {
                    renameTargetId   = parseInt(catId);
                    renameTargetType = 'category';
                    renameTargetName = catName;
                    document.getElementById('renameCurrentHint').textContent = `Currently named: "${catName}"`;
                    if (typeof triggerActionUI === 'function') triggerActionUI();
                }
                return;
            }

            // For item & sub_item: populate items filtered by chosen category
            if (!catId) return;
            const items = rawItems.filter(i => i.category_id == catId);
            itemSelect.innerHTML = '<option value="">-- Choose Item --</option>';
            items.forEach(i => {
                itemSelect.innerHTML += `<option value="${i.id}" data-name="${i.name.replace(/"/g,'&quot;')}">${i.name}</option>`;
            });
        }

        function onRenameItemChange() {
            const type      = document.getElementById('renameType').value;
            const itemSel   = document.getElementById('renameItemSelect');
            const itemId    = itemSel.value;
            const itemName  = itemId ? itemSel.options[itemSel.selectedIndex].text : '';
            const subSelect = document.getElementById('renameSubSelect');

            renameTargetId   = null;
            renameTargetName = null;

            subSelect.innerHTML = '<option value="">-- Choose Sub-Item --</option>';
            document.getElementById('renameNewName').value = '';
            document.getElementById('renameCurrentHint').textContent = '';
            if (typeof hideActionUI === 'function') hideActionUI();

            if (type === 'item') {
                if (itemId) {
                    renameTargetId   = parseInt(itemId);
                    renameTargetType = 'item';
                    renameTargetName = itemName;
                    document.getElementById('renameCurrentHint').textContent = `Currently named: "${itemName}"`;
                    if (typeof triggerActionUI === 'function') triggerActionUI();
                }
                return;
            }

            // For sub_item: populate sub-items filtered by chosen item
            if (!itemId) return;
            const subs = rawSubItems.filter(s => s.item_id == itemId);
            subSelect.innerHTML = '<option value="">-- Choose Sub-Item --</option>';
            subs.forEach(s => {
                subSelect.innerHTML += `<option value="${s.id}">${s.name}</option>`;
            });
        }

        function onRenameSubChange() {
            const subSel   = document.getElementById('renameSubSelect');
            const subId    = subSel.value;
            const subName  = subId ? subSel.options[subSel.selectedIndex].text : '';

            renameTargetId   = null;
            renameTargetName = null;

            document.getElementById('renameNewName').value = '';
            document.getElementById('renameCurrentHint').textContent = '';
            if (typeof hideActionUI === 'function') hideActionUI();

            if (subId) {
                renameTargetId   = parseInt(subId);
                renameTargetType = 'sub_item';
                renameTargetName = subName;
                document.getElementById('renameCurrentHint').textContent = `Currently named: "${subName}"`;
                if (typeof triggerActionUI === 'function') triggerActionUI();
            }
        }

        async function submitRename() {
            const newName = document.getElementById('renameNewName').value.trim();
            if (!renameTargetId || !renameTargetType) {
                Swal.fire({ title: 'Nothing Selected', text: 'Please complete all selections before renaming.', icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                return;
            }
            if (!newName) {
                Swal.fire({ title: 'New Name Required', text: 'Please enter a new name.', icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                return;
            }
            if (newName.toLowerCase() === renameTargetName.toLowerCase()) {
                Swal.fire({ title: 'No Change Detected', text: 'The new name is the same as the current name.', icon: 'info', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                return;
            }

            const typeLabel = renameTargetType === 'sub_item' ? 'Sub-Item' : (renameTargetType.charAt(0).toUpperCase() + renameTargetType.slice(1));
            const result = await Swal.fire({
                title: `Rename ${typeLabel}`,
                html: `Rename <b>"${renameTargetName}"</b> to <b>"${newName}"</b>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#c00000', cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, rename it!',
                customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6', cancelButton: 'rounded-xl font-bold px-6' }
            });

            if (!result.isConfirmed) return;

            Swal.fire({ title: 'Renaming...', allowOutsideClick: false, showConfirmButton: false, willOpen: () => Swal.showLoading(), customClass: { popup: 'rounded-[2rem]' } });

            try {
                const res = await fetch("#", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ type: renameTargetType, id: renameTargetId, new_name: newName })
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    // Update local raw data so the dropdowns reflect the change immediately
                    if (renameTargetType === 'category') {
                        const cat = rawCategories.find(c => c.id === renameTargetId);
                        if (cat) cat.name = newName;
                    } else if (renameTargetType === 'item') {
                        const item = rawItems.find(i => i.id === renameTargetId);
                        if (item) item.name = newName;
                    } else if (renameTargetType === 'sub_item') {
                        const sub = rawSubItems.find(s => s.id === renameTargetId);
                        if (sub) sub.name = newName;
                    }
                    Swal.fire({ title: 'Success!', text: data.message, icon: 'success', confirmButtonColor: '#10b981', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } })
                    .then(() => {
                        // Reset the rename form
                        document.getElementById('renameType').value = '';
                        onRenameTypeChange();
                    });
                } else {
                    Swal.fire({ title: 'Error', text: data.message || 'An error occurred.', icon: 'error', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                }
            } catch(e) {
                Swal.fire({ title: 'Request Failed', text: e.message, icon: 'error', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
            }
        }

        // ===== EDIT MODE (Update/Delete) ACTIONS =====
        let currentEditAction = 'update';

        function setEditAction(action) {
            currentEditAction = action;
            const updateBtn = document.getElementById('editModeUpdateBtn');
            const deleteBtn = document.getElementById('editModeDeleteBtn');
            const inputWrap = document.getElementById('renameInputWrap');
            const warnWrap  = document.getElementById('deleteWarningWrap');
            const rnSubmit  = document.getElementById('renameSubmitBtn');
            const delSubmit = document.getElementById('deleteSubmitBtn');

            if (action === 'update') {
                updateBtn.className = 'flex-1 py-4 rounded-2xl font-bold text-sm transition-all border-2 border-[#c00000] bg-red-50 text-[#c00000]';
                deleteBtn.className = 'flex-1 py-4 rounded-2xl font-bold text-sm transition-all border-2 border-slate-200 bg-white text-slate-400 hover:border-red-300 hover:text-red-400';
                if (renameTargetId) {
                    inputWrap.classList.remove('hidden');
                    rnSubmit.classList.remove('hidden');
                }
                warnWrap.classList.add('hidden');
                delSubmit.classList.add('hidden');
            } else {
                updateBtn.className = 'flex-1 py-4 rounded-2xl font-bold text-sm transition-all border-2 border-slate-200 bg-white text-slate-400 hover:border-red-300 hover:text-red-400';
                deleteBtn.className = 'flex-1 py-4 rounded-2xl font-bold text-sm transition-all border-2 border-red-600 bg-red-50 text-red-600';
                inputWrap.classList.add('hidden');
                rnSubmit.classList.add('hidden');
                if (renameTargetId) {
                    warnWrap.classList.remove('hidden');
                    delSubmit.classList.remove('hidden');
                    previewDeleteImpact();
                }
            }
        }

        function triggerActionUI() {
            if (currentEditAction === 'update') {
                document.getElementById('renameInputWrap').classList.remove('hidden');
                document.getElementById('renameSubmitBtn').classList.remove('hidden');
                document.getElementById('deleteWarningWrap').classList.add('hidden');
                document.getElementById('deleteSubmitBtn').classList.add('hidden');
            } else {
                document.getElementById('renameInputWrap').classList.add('hidden');
                document.getElementById('renameSubmitBtn').classList.add('hidden');
                document.getElementById('deleteWarningWrap').classList.remove('hidden');
                document.getElementById('deleteCurrentHint').textContent = `Record to delete: "${renameTargetName}"`;
                document.getElementById('deleteSubmitBtn').classList.remove('hidden');
                previewDeleteImpact();
            }
        }

        function hideActionUI() {
            document.getElementById('renameInputWrap').classList.add('hidden');
            document.getElementById('renameSubmitBtn').classList.add('hidden');
            document.getElementById('deleteWarningWrap').classList.add('hidden');
            document.getElementById('deleteSubmitBtn').classList.add('hidden');
        }

        async function previewDeleteImpact() {
            const impactBox = document.getElementById('deleteImpactBox');
            const impactTxt = document.getElementById('deleteImpactDetails');
            
            impactBox.classList.remove('hidden');
            impactTxt.innerHTML = '<span class="text-slate-500 animate-pulse">Calculating impact...</span>';
            
            try {
                const res = await fetch("#", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ type: renameTargetType, id: renameTargetId })
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    const i = data.impact;
                    let msgs = [];
                    if (renameTargetType === 'category' && i.items > 0) msgs.push(`• <b>${i.items}</b> associated Item(s)`);
                    if (['category', 'item'].includes(renameTargetType) && i.sub_items > 0) msgs.push(`• <b>${i.sub_items}</b> Sub-Item(s) specification(s)`);
                    if (i.total_stock > 0) msgs.push(`• <b>${i.total_stock}</b> items in master stock`);
                    if (i.ownerships > 0) msgs.push(`• <b>${i.ownerships}</b> distributed physical asset(s) across <b>${i.schools_affected}</b> school(s)`);
                    
                    if (msgs.length === 0) {
                        impactTxt.innerHTML = '<span class="text-emerald-600 font-bold">Safe to delete: No associated records or stock will be affected.</span>';
                        impactBox.classList.replace('bg-red-50', 'bg-emerald-50');
                        impactBox.classList.replace('border-red-200', 'border-emerald-200');
                    } else {
                        impactTxt.innerHTML = 'This action will instantly delete the record AND cascade to permanently destroy:<br>' + msgs.join('<br>');
                        impactBox.classList.replace('bg-emerald-50', 'bg-red-50');
                        impactBox.classList.replace('border-emerald-200', 'border-red-200');
                    }
                } else {
                    impactTxt.innerHTML = '<span class="text-slate-500">Failed to calculate impact.</span>';
                }
            } catch (e) {
                impactTxt.innerHTML = '<span class="text-slate-500">Failed to calculate impact.</span>';
            }
        }

        async function submitDelete() {
            if (!renameTargetId || !renameTargetType) return;
            
            const typeLabel = renameTargetType === 'sub_item' ? 'Sub-Item' : (renameTargetType.charAt(0).toUpperCase() + renameTargetType.slice(1));
            const result = await Swal.fire({
                title: 'CRITICAL DELETION',
                html: `Are you absolutely sure you want to permanently delete the ${typeLabel} <b>"${renameTargetName}"</b>?<br><br><span class="text-red-600 font-bold text-sm">This action cannot be undone and will destroy any associated physical inventory records!</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#c00000', cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, permanently destroy it',
                cancelButtonText: 'Cancel',
                customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6', cancelButton: 'rounded-xl font-bold px-6' }
            });

            if (!result.isConfirmed) return;

            Swal.fire({ title: 'Destroying Records...', allowOutsideClick: false, showConfirmButton: false, willOpen: () => Swal.showLoading(), customClass: { popup: 'rounded-[2rem]' } });

            try {
                const res = await fetch("#", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ type: renameTargetType, id: renameTargetId })
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    Swal.fire({ title: 'Deleted!', text: data.message, icon: 'success', confirmButtonColor: '#10b981', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } })
                    .then(() => { location.reload(); }); // Reload to refresh all related dropdowns and UI
                } else {
                    Swal.fire({ title: 'Error', text: data.message || 'An error occurred.', icon: 'error', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                }
            } catch(e) {
                Swal.fire({ title: 'Request Failed', text: e.message, icon: 'error', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
            }
        }

        // Close dropdowns on outside click handler
        document.addEventListener('click', function(e) {
            if(!document.getElementById('preDistSchoolDropdownList')?.classList.contains('hidden')) {
                const el = document.getElementById('preDistSchoolSearch');
                const dd = document.getElementById('preDistSchoolDropdownList');
                if(el && dd && !el.contains(e.target) && !dd.contains(e.target)) dd.classList.add('hidden');
            }
            
            distTabsData.forEach((_, i) => {
                ['Cat','Item','Sub'].forEach(type => {
                    const search = document.getElementById(`tab${type}Search_${i}`);
                    const dd = document.getElementById(`tab${type}Dropdown_${i}`);
                    if (search && dd && !dd.classList.contains('hidden') && !search.contains(e.target) && !dd.contains(e.target)) {
                        dd.classList.add('hidden');
                    }
                });
            });
            
            [['itemDropdownList','itemDropdownBtn','itemName'],['categoryDropdownList','categoryDropdownBtn','categoryName'],
             ['itemCategoryDropdownList','itemCategoryDropdownBtn','itemCategoryName']
            ].forEach(([ddId, btnId, inputId]) => {
                const dd = document.getElementById(ddId), btn = document.getElementById(btnId), inp = document.getElementById(inputId);
                if (dd && btn && !dd.contains(e.target) && !btn.contains(e.target) && e.target !== inp && e.target.id !== inputId) dd.classList.add('hidden');
            });
        });
    



        // ============================================================
        // DISTRIBUTION MODULE — RECIPIENT REGISTRY FUNCTIONS
        // ============================================================

        let distRecipientCount = 0;
        let distAddedIds = []; // tracks stakeholder IDs already in the list
        let distRecipientsCache = {}; // { id: { displayName, subLabel } } — survives rawStakeholders gap for NEW entries




        async function distAddRecipientToList() {
            const type = document.getElementById('distSourceType')?.value;
            if (!type) {
                Swal.fire('Required', 'Please select an Entity Type first.', 'warning');
                return;
            }

            const schoolInput = document.getElementById('distSchoolInput');
            const schoolId = parseInt(schoolInput?.dataset?.schoolId || '0') || null;
            const externalName = document.getElementById('distExternalInput')?.value?.trim() || '';
            const personName  = document.getElementById('distPersonnelName')?.value?.trim() || '';
            const position    = document.getElementById('distPersonnelPosition')?.value?.trim() || '';

            // Validation
            if (type === 'school' && !schoolId) {
                Swal.fire('Required', 'Please search and select a school from the dropdown.', 'warning');
                return;
            }
            if (type === 'external' && !externalName) {
                Swal.fire('Required', 'Please enter the external office or organization name.', 'warning');
                return;
            }

            // Show loading state
            const addBtn = document.querySelector('[onclick="distAddRecipientToList()"]');
            if (addBtn) { addBtn.disabled = true; addBtn.innerText = 'Adding...'; }

            try {
                const resp = await fetch('{{ route("recipients.add") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ entity_type: type, school_id: schoolId, external_name: externalName, person_name: personName, position })
                });

                const data = await resp.json();

                if (!resp.ok) {
                    Swal.fire('Error', data.error || 'Something went wrong.', 'error');
                    return;
                }

                // Duplicate guard
                if (distAddedIds.includes(data.id)) {
                    Swal.fire('Duplicate', `${data.display_name} is already in the recipient list.`, 'info');
                    return;
                }

                distAddedIds.push(data.id);
                distRecipientCount++;

                // Cache display data so distGoBackToRegistry can restore cards for NEW stakeholders
                distRecipientsCache[data.id] = { displayName: data.display_name, subLabel: data.sub_label };

                // Build card
                const newBadge = data.is_new
                    ? `<span class="text-[8px] font-black bg-emerald-400/20 text-emerald-400 px-2 py-0.5 rounded-full uppercase tracking-widest ml-1">NEW</span>`
                    : '';

                const card = document.createElement('div');
                card.className = 'bg-white/5 border border-white/10 p-4 rounded-2xl flex justify-between items-center';
                card.dataset.stakeholderId = data.id;
                card.innerHTML = `
                    <div class="overflow-hidden flex-1">
                        <div class="flex items-center gap-1">
                            <p class="text-white font-bold text-xs truncate">${data.display_name}</p>
                            ${newBadge}
                        </div>
                        <p class="text-slate-500 text-[9px] uppercase font-black tracking-widest truncate mt-0.5">${data.sub_label}</p>
                    </div>
                    <button onclick="distRemoveRecipient(this, ${data.id})" class="text-slate-600 hover:text-red-400 transition-colors ml-3 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>`;

                const activeList = document.getElementById('distActiveList');
                if (activeList) activeList.appendChild(card);

                document.getElementById('distEmptyState')?.classList.add('hidden');
                document.getElementById('distListFooter')?.classList.remove('hidden');
                distUpdateCount();

                // Clear fields after adding
                if (document.getElementById('distPersonnelName')) document.getElementById('distPersonnelName').value = '';
                if (document.getElementById('distPersonnelPosition')) document.getElementById('distPersonnelPosition').value = '';

            } catch (err) {
                console.error(err);
                Swal.fire('Error', 'Network error. Please try again.', 'error');
            } finally {
                if (addBtn) { addBtn.disabled = false; addBtn.innerText = 'Add Recipient'; }
            }
        }





        // ─── External Office dropdown (shows all on focus, filters on type) ────


        // ─── Personnel dropdown (scoped to selected parent, shows all on focus) ─


        // ─── Close dropdowns when clicking outside ────────────────────────────
        document.addEventListener('click', function(e) {
            const extWrap  = document.getElementById('distExternalInput')?.closest('.relative');
            const persWrap = document.getElementById('distPersonnelName')?.closest('.relative');
            if (extWrap  && !extWrap.contains(e.target))  document.getElementById('distExternalDropdown')?.classList.add('hidden');
            if (persWrap && !persWrap.contains(e.target)) document.getElementById('distPersonnelDropdown')?.classList.add('hidden');
        });
        // ─── Custom Autocomplete Logic ───────────────────────────────────────
        const dbSuggestions = {};
        // Parse existing datalists into dictionaries on load
        document.querySelectorAll('datalist[id^="dl-"]').forEach(dl => {
            const colName = dl.id.replace('dl-', '');
            dbSuggestions[colName] = Array.from(dl.options).map(opt => opt.value).filter(Boolean);
        });
        
        let activeAutocomplete = null;

        document.addEventListener('focusin', handleAutocompleteEvent);
        document.addEventListener('input', handleAutocompleteEvent);
        document.addEventListener('mousedown', (e) => {
            if (activeAutocomplete && !e.target.closest('.custom-autocomplete') && !e.target.hasAttribute('data-col')) {
                closeAutocomplete();
            }
        });

        function closeAutocomplete() {
            if (activeAutocomplete) {
                activeAutocomplete.remove();
                activeAutocomplete = null;
            }
        }

        function handleAutocompleteEvent(e) {
            const input = e.target;
            if (!input || input.tagName !== 'INPUT' || !input.hasAttribute('data-col')) return;

            const colName = input.getAttribute('data-col');
            const typedValue = input.value.toLowerCase().trim();
            
            // Gather most recent typed data per column (from DOM)
            const allInputsInCol = Array.from(document.querySelectorAll(`input[data-col="${colName}"]`));
            const localData = [];
            
            // Reverse to get most recent (assuming bottom rows are most recent)
            for (let i = allInputsInCol.length - 1; i >= 0; i--) {
                const val = allInputsInCol[i].value.trim();
                // We don't exclude current typed value if empty, to show all options
                if (val && !localData.includes(val) && allInputsInCol[i] !== input) {
                    localData.push(val);
                }
            }
            
            // Fallback to DB data
            const dbData = dbSuggestions[colName] || [];
            
            // Merge unique suggestions
            const suggestions = new Set(localData);
            for (const item of dbData) {
                suggestions.add(item);
            }
            
            // Filter by typed value and take up to 5
            const filtered = Array.from(suggestions)
                .filter(val => val.toLowerCase().includes(typedValue))
                .slice(0, 5);

            closeAutocomplete();

            if (filtered.length === 0) return;

            // Render dropdown
            const rect = input.getBoundingClientRect();
            const dropdown = document.createElement('div');
            dropdown.className = 'custom-autocomplete';
            dropdown.style.left = `${rect.left + window.scrollX}px`;
            // Check if there's space below, else show above
            const spaceBelow = window.innerHeight - rect.bottom;
            if (spaceBelow < 150 && rect.top > 150) {
                dropdown.style.bottom = `${window.innerHeight - rect.top - window.scrollY}px`;
                dropdown.style.top = 'auto';
            } else {
                dropdown.style.top = `${rect.bottom + window.scrollY}px`;
                dropdown.style.bottom = 'auto';
            }
            dropdown.style.width = `${input.offsetWidth}px`;

            filtered.forEach(val => {
                const item = document.createElement('div');
                item.className = 'custom-autocomplete-item';
                item.textContent = val;
                item.addEventListener('mousedown', (evt) => {
                    evt.preventDefault(); // keep focus
                    input.value = val;
                    closeAutocomplete();
                    updateNewLabels(); // Update NEW labels immediately
                });
                dropdown.appendChild(item);
            });

            document.body.appendChild(dropdown);
            activeAutocomplete = dropdown;
        }
        
        // Feature: "NEW" label for unrecognized/first-time data per column
        function updateNewLabels() {
            const cols = new Set();
            document.querySelectorAll('input[data-col]').forEach(el => cols.add(el.getAttribute('data-col')));

            cols.forEach(colName => {
                const dbData = (dbSuggestions[colName] || []).map(v => v.toLowerCase().trim());
                const seen = new Set(dbData);
                
                const inputs = document.querySelectorAll(`input[data-col="${colName}"]`);
                inputs.forEach(input => {
                    const val = input.value.trim().toLowerCase();
                    const td = input.closest('td') || input.parentElement;
                    
                    const existingBadge = td.querySelector('.new-badge');
                    if (existingBadge) existingBadge.remove();

                    if (val !== '') {
                        if (!seen.has(val)) {
                            // First time we see this unrecognized value
                            const badge = document.createElement('span');
                            badge.className = 'new-badge';
                            badge.textContent = 'NEW';
                            td.appendChild(badge);
                            seen.add(val); // Mark as seen so subsequent rows don't get the badge
                        }
                    }
                });
            });
        }

        // Attach input listener strictly for the new labels
        document.addEventListener('input', updateNewLabels);

        window.addEventListener('scroll', (e) => {
            // Ignore scroll events originating from the autocomplete dropdown itself
            if (activeAutocomplete && (e.target === activeAutocomplete || activeAutocomplete.contains(e.target))) return;
            closeAutocomplete();
        }, true);
        window.addEventListener('resize', closeAutocomplete);
</script>

{{-- ========================================== --}}
{{-- BULK ADD MODAL                             --}}
{{-- ========================================== --}}
<div id="bulkAddModal" class="fixed inset-0 z-50 flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeBulkAddModal()"></div>
    <div class="bg-white dark:bg-[#141f33] border border-slate-200 dark:border-slate-800 rounded-[2rem] shadow-2xl w-[90vw] max-w-5xl max-h-[90vh] flex flex-col relative z-10 transform scale-95 transition-transform duration-300">
        
        {{-- Header --}}
        <div class="px-8 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-2xl font-black text-slate-800 dark:text-white uppercase tracking-tight italic">Bulk Add Rows</h3>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Pre-fill data across multiple new rows</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 bg-slate-50 dark:bg-[#0a101d] px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800">
                    <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Rows to add</label>
                    <input type="number" id="bulkRowCount" value="1" min="1" max="100" class="w-16 bg-transparent text-center font-black text-slate-800 dark:text-white outline-none">
                </div>
                <button onclick="closeBulkAddModal()" class="px-5 py-3 rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">Cancel</button>
                <button onclick="confirmBulkAdd()" class="px-6 py-3 rounded-xl text-sm font-black text-white bg-[#c00000] hover:bg-red-700 shadow-lg shadow-red-500/30 transition-all">Confirm Bulk Add</button>
            </div>
        </div>

        {{-- Body --}}
        <div class="p-8 overflow-y-auto custom-scroll flex-1 space-y-10">
            
            {{-- Source Section --}}
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-6 h-6 bg-amber-500/20 text-amber-500 rounded-lg flex items-center justify-center text-[10px] font-black shrink-0">1</div>
                    <h4 class="font-black text-slate-800 dark:text-slate-200 uppercase tracking-widest text-xs">Asset Data Entry (Source)</h4>
                </div>
                <div class="grid grid-cols-2 gap-x-6 gap-y-5">
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Classification</label><input type="text" id="bClassification" data-col="classification" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Combo-box: type/select"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Category</label><input type="text" id="bCategory" data-col="category" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Combo-box: type/select"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Item</label><input type="text" id="bItem" data-col="item" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Combo-box: type/select"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Description</label><input type="text" id="bDescription" data-col="description" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Combo-box: type/select"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Source Personnel</label><input type="text" id="bPersonnel" data-col="personnel" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Combo-box: type/select"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Personnel Position</label><input type="text" id="bPosition" data-col="position" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Combo-box: type/select"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Cost per Unit</label><input type="number" id="bCost" oninput="calcBulkCost()" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl text-right" placeholder="1" min="0" step="0.01"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Quantity</label><input type="number" id="bQty1" oninput="calcBulkCost()" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl text-right" placeholder="1" min="0" step="1"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Expected Useful Life</label><input type="number" id="bLife" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl text-right" placeholder="1" min="0" step="1"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Acceptance Date</label><input type="date" id="bDate1" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl"></div>
                </div>
            </div>

            <div class="border-t border-slate-100 dark:border-slate-800"></div>

            {{-- Target Section --}}
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-6 h-6 bg-amber-500/20 text-amber-500 rounded-lg flex items-center justify-center text-[10px] font-black shrink-0">2</div>
                    <h4 class="font-black text-slate-800 dark:text-slate-200 uppercase tracking-widest text-xs">Asset Distribution (Target)</h4>
                </div>
                <div class="grid grid-cols-2 gap-x-6 gap-y-5">
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Region</label>
                        <div class="w-full px-4 py-[11px] font-semibold text-[11.5px] bg-slate-100/50 dark:bg-white/5 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-500 dark:text-slate-400 flex justify-between items-center cursor-not-allowed">Region IX <svg class="w-3.5 h-3.5 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></div>
                    </div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Division</label>
                        <div class="w-full px-4 py-[11px] font-semibold text-[11.5px] bg-slate-100/50 dark:bg-white/5 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-500 dark:text-slate-400 flex justify-between items-center cursor-not-allowed">Division of Zamboanga City <svg class="w-3.5 h-3.5 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></div>
                    </div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Office/School Type</label><input type="text" id="bSchoolType" data-col="school-type" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Combo-box: type/select"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">School ID</label><input type="text" id="bSchoolId" data-col="school-id" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Combo-box: type/select" inputmode="numeric"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Office/School Name</label><input type="text" id="bSchoolName" data-col="school-name" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Combo-box: type/select"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Nature of Occupancy</label><input type="text" id="bOccupancy" data-col="occupancy" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Combo-box: type/select"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Location</label><input type="text" id="bLocation" data-col="location" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Combo-box: type/select"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Property Number</label><input type="text" id="bPropertyNo" oninput="checkBulkPropertyNumber()" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Combo-box: type/select"></div>
                    <div class="relative">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Acquisition Cost</label>
                        <div class="relative">
                            <input type="number" id="bCost2" class="w-full px-4 py-[11px] font-semibold text-[11.5px] bg-slate-100/50 dark:bg-white/5 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-500 dark:text-slate-400 cursor-not-allowed outline-none text-right pr-10" placeholder="0.00" min="0" step="0.01" readonly tabindex="-1">
                            <svg class="w-3.5 h-3.5 opacity-50 absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                    </div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Acquisition Date</label><input type="date" id="bDate2" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl"></div>
                </div>
            </div>

        </div>
    </div>
</div>
<script>
    function calcBulkCost() {
        const cost = parseFloat(document.getElementById('bCost').value) || 0;
        const qty = parseFloat(document.getElementById('bQty1').value) || 0;
        document.getElementById('bCost2').value = (cost * qty).toFixed(2);
    }
    
    function checkBulkPropertyNumber() {
        const propInput = document.getElementById('bPropertyNo');
        const qtyInput = document.getElementById('bQty1');
        if (!propInput || !qtyInput) return;
        
        if (propInput.value.trim() !== '') {
            qtyInput.value = 1;
            qtyInput.readOnly = true;
            qtyInput.classList.add('bg-slate-50', 'dark:bg-white/5', 'cursor-not-allowed');
        } else {
            qtyInput.readOnly = false;
            qtyInput.classList.remove('bg-slate-50', 'dark:bg-white/5', 'cursor-not-allowed');
        }
        calcBulkCost();
    }
    
    function openBulkAddModal() {
        const m = document.getElementById('bulkAddModal');
        m.classList.remove('hidden');
        setTimeout(() => {
            m.classList.remove('opacity-0');
            m.querySelector('.transform').classList.remove('scale-95');
        }, 10);
    }

    function closeBulkAddModal() {
        const m = document.getElementById('bulkAddModal');
        m.classList.add('opacity-0');
        m.querySelector('.transform').classList.add('scale-95');
        setTimeout(() => m.classList.add('hidden'), 300);
    }

    function confirmBulkAdd() {
        const count = parseInt(document.getElementById('bulkRowCount').value) || 1;
        for(let i=0; i<count; i++) addAssetRow();
        
        const srcRows = Array.from(document.querySelectorAll('#assetSourceBody tr')).slice(-count);
        const dstRows = Array.from(document.querySelectorAll('#assetDistBody tr')).slice(-count);
        const modalInputs = document.getElementById('bulkAddModal').querySelectorAll('input[data-col]');
        
        for(let i=0; i<count; i++) {
            modalInputs.forEach(mi => {
                const col = mi.getAttribute('data-col');
                let target = srcRows[i].querySelector(`input[data-col="${col}"]`);
                if(!target) target = dstRows[i].querySelector(`input[data-col="${col}"]`);
                if(target) target.value = mi.value;
            });
            
            const id = srcRows[i].dataset.rowIndex;
            const targetCost = document.getElementById(`src-cost-${id}`);
            const targetQty = document.getElementById(`src-qty-${id}`);
            if(targetCost) targetCost.value = document.getElementById('bCost').value;
            if(targetQty) targetQty.value = document.getElementById('bQty1').value;
            calcCost(id);
            
            const dateInputs1 = srcRows[i].querySelectorAll('input[type="date"]');
            if (dateInputs1.length > 0) dateInputs1[0].value = document.getElementById('bDate1').value;
            const dateInputs2 = dstRows[i].querySelectorAll('input[type="date"]');
            if (dateInputs2.length > 0) dateInputs2[0].value = document.getElementById('bDate2').value;
        }
        
        updateNewLabels();
        closeBulkAddModal();
    }
    
    // Set default dates in bulk modal on load
    document.addEventListener('DOMContentLoaded', () => {
        const d = new Date().toISOString().split('T')[0];
        document.getElementById('bDate1').value = d;
        document.getElementById('bDate2').value = d;
    });

    function submitRegistration() {
        const rows = Array.from(document.querySelectorAll('#assetSourceBody tr'));
        if (rows.length === 0) {
            Swal.fire({ icon: 'warning', title: 'No Data', text: 'Please add at least one row to register.', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
            return;
        }

        const acqSourceInput = document.getElementById('acqSourceInput').value.trim();
        if (!acqSourceInput) {
            Swal.fire({ icon: 'warning', title: 'Missing Source', text: 'Please specify the Source of Acquisition at the top of the form.', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
            return;
        }

        let isValid = true;
        let payload = [];

        for (let i = 0; i < rows.length; i++) {
            const srcRow = rows[i];
            const id = srcRow.dataset.rowIndex;
            const dstRow = document.getElementById(`dst-${id}`);
            
            if (!dstRow) continue;

            const rowData = {};

            // Parse src row inputs
            const srcInputs = srcRow.querySelectorAll('input');
            srcInputs.forEach(input => {
                const col = input.getAttribute('data-col');
                if (col) {
                    rowData[col] = input.value.trim();
                    if (!rowData[col] && !['description', 'personnel', 'position'].includes(col)) isValid = false;
                } else if (input.id === `src-cost-${id}`) {
                    rowData['cost'] = input.value.trim();
                    if (!rowData['cost']) isValid = false;
                } else if (input.id === `src-qty-${id}`) {
                    rowData['qty'] = input.value.trim();
                    if (!rowData['qty']) isValid = false;
                } else if (input.placeholder === '0' && !input.id) {
                    rowData['useful-life'] = input.value.trim();
                } else if (input.type === 'date') {
                    rowData['acceptance-date'] = input.value.trim();
                    if (!rowData['acceptance-date']) isValid = false;
                }
            });

            // Parse dst row inputs
            const dstInputs = dstRow.querySelectorAll('input');
            dstInputs.forEach(input => {
                const col = input.getAttribute('data-col');
                if (col) {
                    rowData[col] = input.value.trim();
                    if (!rowData[col]) {
                        if (col !== 'property-no') isValid = false;
                    }
                } else if (input.id === `dst-prop-${id}`) {
                    rowData['property-no'] = input.value.trim();
                } else if (input.type === 'date') {
                    rowData['acquisition-date'] = input.value.trim();
                    if (!rowData['acquisition-date']) isValid = false;
                }
            });
            
            const spans = dstRow.querySelectorAll('.xls-const');
            if (spans.length >= 2) {
                rowData['region'] = spans[0].textContent.trim();
                rowData['division'] = spans[1].textContent.trim();
            }

            payload.push(rowData);
        }

        if (!isValid) {
            Swal.fire({
                icon: 'error',
                title: 'Incomplete Fields',
                text: 'Please fill in all required fields. Only Description, Source Personnel, Personnel Position, Useful Life, and Property Number can be left blank.',
                confirmButtonColor: '#c00000',
                customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' }
            });
            return;
        }

        Swal.fire({
            title: 'Confirm Registration',
            text: `Are you sure you want to register these ${payload.length} items?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#c00000',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Yes, Register',
            cancelButtonText: 'Cancel',
            customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6', cancelButton: 'rounded-xl font-bold px-6' }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Registering...',
                    text: 'Please wait while the system processes your batch.',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                fetch('/inventory-setup/batch', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        source_of_acquisition: acqSourceInput,
                        rows: payload
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message || 'Items successfully registered.',
                            confirmButtonColor: '#10b981',
                            customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' }
                        }).then(() => {
                            window.location.href = '/view-all-assets';
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Registration Failed', text: data.message || 'An error occurred.', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire({ icon: 'error', title: 'System Error', text: 'A network or server error occurred.', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                });
            }
        });
    }
</script>
</body>
</html>