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

        .custom-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .custom-scroll::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }

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
        html.dark .back-btn-cool {
            background: #141f33;
            border-color: #1e2e47;
            color: #94a3b8;
        }
        html.dark .back-btn-cool:hover {
            border-color: #c00000;
            color: white;
            background: #c00000;
        }

        /* â”€â”€ Excel-like registration table â”€â”€ */
        .xls-th {
            padding: 14px 16px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #64748b;
            white-space: nowrap;
            border-right: 1px solid #e2e8f0;
            border-bottom: 2px solid #f1f5f9;
            background: #f8fafc;
            position: sticky;
            top: 0;
            z-index: 20;
        }
        .xls-td {
            height: 48px;
            border-right: 1px solid #e2e8f0;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            position: relative;
            padding: 0;
            background: #ffffff;
        }
        /* row highlight */
        .xls-row { transition: background 0.1s; }
        .xls-row:hover .xls-td { background-color: #f8fafc !important; }
        .xls-row:hover .xls-td.xls-sticky-col { background-color: #f8fafc !important; }
        /* inputs inside cells */
        .xls-input {
            width: 100%;
            padding: 11px 14px;
            font-size: 11.5px;
            font-weight: 600;
            color: #334155;
            background: transparent;
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
        .xls-input::placeholder { color: #cbd5e1; font-weight: 500; }
        .xls-const {
            display: flex;
            align-items: center;
            padding: 0 16px;
            height: 100%;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            white-space: nowrap;
            font-style: normal;
        }
        /* Scroll container: min-height = 10 rows, scrollable beyond */
        .xls-scroll-wrap {
            position: relative;
            overflow-x: auto;
            overflow-y: auto;
            width: 100%;
            max-width: 100%;
            min-height: 400px;
            max-height: calc(100vh - 450px);
            flex-grow: 1;
            background: #ffffff;
        }
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
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        html.dark .pg-btn {
            background: white;
            color: #1e293b;
            border-color: rgba(255,255,255,0.1);
        }
        .pg-btn:hover:not(:disabled) {
            border-color: #c00000;
            color: #c00000;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .pg-btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }
        .pg-btn-active {
            background: #c00000 !important;
            color: white !important;
            border-color: #c00000 !important;
        }
        /* â”€â”€ Dark mode overrides (keeping table white/light even in dark mode if preferred, or matching dark theme) â”€â”€ */
        html.dark .xls-th {
            background: #0f172a !important;
            color: #94a3b8 !important;
            border-color: #1e293b !important;
        }
        html.dark .xls-td { 
            background: #0f172a !important;
            border-color: #1e293b !important; 
        }
        html.dark .xls-row:hover .xls-td { background-color: #1e293b !important; }
        html.dark .xls-row:hover .xls-td.xls-sticky-col { background-color: #1e293b !important; }
        /* Typebox enhancements */
        html.dark .xls-input { background: transparent; color: #e2e8f0; }
        html.dark .xls-input:focus { background: rgba(192,0,0,0.1); border-color: #c00000; box-shadow: 0 0 0 2px rgba(192,0,0,0.2); }
        html.dark .xls-input::placeholder { color: #475569; }
        html.dark .xls-scroll-wrap { background-color: #0f172a !important; }
        html.dark .xls-const { color: #94a3b8 !important; }
        html.dark .xls-sticky-col { background-color: #0f172a !important; }

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

        /* Pagination Styles */
        .pg-btn {
            padding: 8px 16px;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            border-radius: 12px;
            transition: all 0.2s;
            display: flex;
            items-center: center;
            gap: 6px;
        }
        .pg-btn:not(:disabled):hover { background: #f1f5f9; color: #c00000; }
        .pg-btn:disabled { opacity: 0.3; cursor: not-allowed; }
        html.dark .pg-btn { background: #1e293b !important; color: #cbd5e1 !important; }
        html.dark .pg-btn:not(:disabled):hover { background: #c00000 !important; color: white !important; }

        /* Column Coloring */
        .col-identity { background-color: #eff6ff !important; border-color: #dbeafe !important; }
        .col-context  { background-color: #f8fafc !important; border-color: #f1f5f9 !important; }
        .col-personnel{ background-color: #fffbeb !important; border-color: #fef3c7 !important; }
        .col-financial{ background-color: #eef2ff !important; border-color: #e0e7ff !important; }
        .col-temporal { background-color: #ecfdf5 !important; border-color: #d1fae5 !important; }
        .col-status   { background-color: #f5f3ff !important; border-color: #ede9fe !important; }

        html.dark .col-identity { background-color: rgba(30, 58, 138, 0.15) !important; border-color: rgba(30, 58, 138, 0.3) !important; }
        html.dark .col-context  { background-color: rgba(30, 41, 59, 0.15) !important; border-color: rgba(30, 41, 59, 0.3) !important; }
        html.dark .col-personnel{ background-color: rgba(120, 53, 15, 0.15) !important; border-color: rgba(120, 53, 15, 0.3) !important; }
        html.dark .col-financial{ background-color: rgba(49, 46, 129, 0.15) !important; border-color: rgba(49, 46, 129, 0.3) !important; }
        html.dark .col-temporal { background-color: rgba(6, 78, 59, 0.15) !important; border-color: rgba(6, 78, 59, 0.3) !important; }
        html.dark .col-status   { background-color: rgba(76, 29, 149, 0.15) !important; border-color: rgba(76, 29, 149, 0.3) !important; }

        /* Stronger background for TH */
        th.col-identity { background-color: #dbeafe !important; }
        th.col-context  { background-color: #f1f5f9 !important; }
        th.col-personnel{ background-color: #fef3c7 !important; }
        th.col-financial{ background-color: #e0e7ff !important; }
        th.col-temporal { background-color: #d1fae5 !important; }
        th.col-status   { background-color: #ede9fe !important; }

        html.dark th.col-identity { background-color: rgba(30, 58, 138, 0.4) !important; }
        html.dark th.col-context  { background-color: rgba(30, 41, 59, 0.4) !important; }
        html.dark th.col-personnel{ background-color: rgba(120, 53, 15, 0.4) !important; }
        html.dark th.col-financial{ background-color: rgba(49, 46, 129, 0.4) !important; }
        html.dark th.col-temporal { background-color: rgba(6, 78, 59, 0.4) !important; }
        html.dark th.col-status   { background-color: rgba(76, 29, 149, 0.4) !important; }
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10 px-4 mb-10">
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
                        <div class="w-20 h-20 bg-red-50 text-[#c00000] rounded-3xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-10 h-10">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                            </svg>
                        </div>
                        <h4 class="text-3xl font-black text-slate-800 tracking-tight uppercase">Add Building</h4>
                        <p class="text-slate-400 text-xs font-bold uppercase mt-3 tracking-widest leading-tight">Register new school buildings or infrastructure units</p>
                    </div>
                </div>

                {{-- Bottom Row: Management Cards (Slim Horizontal side-by-side) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 px-4 mt-10">
                    {{-- Inventory Management --}}
                    <div onclick="nextStep(2, 'edit')" class="group bg-white p-6 rounded-[2.5rem] shadow-xl shadow-slate-200/40 border-2 border-transparent hover:border-blue-600 transition-all duration-300 cursor-pointer flex items-center justify-between relative overflow-hidden">
                        <div class="flex items-center gap-5 relative z-10">
                            <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-7 h-7">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                </svg>
                            </div>
                            <div class="text-left">
                                <h4 class="text-lg font-black text-slate-800 tracking-tight uppercase">Inventory Management</h4>
                                <p class="text-slate-400 text-[8px] font-bold uppercase tracking-widest mt-1">Master Registry Records</p>
                            </div>
                        </div>
                        <div class="text-blue-600 group-hover:translate-x-2 transition-transform relative z-10">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </div>
                    </div>

                    {{-- Infrastructure Management --}}
                    <div onclick="nextStep(2, 'infra')" class="group bg-white p-6 rounded-[2.5rem] shadow-xl shadow-slate-200/40 border-2 border-transparent hover:border-emerald-600 transition-all duration-300 cursor-pointer flex items-center justify-between relative overflow-hidden">
                        <div class="flex items-center gap-5 relative z-10">
                            <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-7 h-7">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1m1.5-1.5l-3-1m-3.182-5.182L15 4.5" />
                                </svg>
                            </div>
                            <div class="text-left">
                                <h4 class="text-lg font-black text-slate-800 tracking-tight uppercase">Infrastructure Management</h4>
                                <p class="text-slate-400 text-[8px] font-bold uppercase tracking-widest mt-1">Buildings & Facilities</p>
                            </div>
                        </div>
                        <div class="text-emerald-600 group-hover:translate-x-2 transition-transform relative z-10">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </div>
                    </div>
                </div>

            </div>

{{-- Step 2: Category Selection --}}
<div id="step2" class="step-content">
    <h3 id="step2Title" class="text-lg font-black text-slate-900 uppercase tracking-[0.3em] text-center mb-6 -mt-6">Select Category</h3>
    
<div id="categoryGrid" class="grid grid-cols-2 gap-6 max-w-3xl mx-auto px-4 mb-8">        
    {{-- Empty Grid --}}


</div>
</div>

{{-- â•â•â•â•â•â•â• STEP: ADD NEW RECORD â€” Registration Form â•â•â•â•â•â•â• --}}
    @include('partials.register-item-step')

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

            @include('partials.inventory-edit-step')
            @include('partials.building-edit-step')

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
        const allCustodiansList = @json($allCustodians);
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

        // Edit Items (Inventory Management)
        if (value === 'edit') {
            document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
            document.getElementById('stepInventoryEdit').classList.add('active');
            document.getElementById('mainContent').classList.replace('max-w-5xl', 'max-w-full');
            stepHistory.push('edit');
            updateBackButton();
            if (typeof initInventoryEdit === 'function') initInventoryEdit();
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

        // Infrastructure Management (Building Editor)
        if (value === 'infra') {
            document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
            document.getElementById('stepBuildingEdit').classList.add('active');
            document.getElementById('mainContent').classList.replace('max-w-5xl', 'max-w-full');
            stepHistory.push('infra');
            updateBackButton();
            if (typeof initBldgEdit === 'function') initBldgEdit();
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

                if (leavingStep === 'addnew' || leavingStep === 'addbuilding' || leavingStep === 'edit' || leavingStep === 'infra') {
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

        // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
        @include('partials.scripts.item-manager')
        @include('partials.scripts.autocomplete-engine')
    </script>

    @include('partials.inventory-modals')

</body>
</html>
