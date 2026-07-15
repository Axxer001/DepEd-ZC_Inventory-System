<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suppliers Registry | DepEd Zamboanga City</title>
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
        .xls-th { padding: 14px 16px; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; color: #475569; white-space: nowrap; border-right: 1px solid #e2e8f0; border-bottom: 2px solid #cbd5e1; background: #f8fafc; position: sticky; top: 0; z-index: 20; box-shadow: 0 1px 2px rgba(0,0,0,0.02); }
        .xls-td { height: 52px; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; vertical-align: middle; padding: 0; background: white; transition: all 0.3s ease; }
        .xls-row { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; position: relative; }
        .xls-row:hover { transform: translateX(4px); z-index: 10; }
        .xls-row:hover .xls-td { background-color: #fdf3f3 !important; border-bottom-color: #c00000; }
        .xls-row:hover .xls-td:first-child { box-shadow: inset 4px 0 0 #c00000; }
        .xls-row:active { transform: scale(0.995); transition: all 0.1s; }
        .xls-row:active .xls-td { background-color: #fbe3e3 !important; }
        .xls-const { display: flex; align-items: center; padding: 0 16px; height: 100%; font-size: 11.5px; font-weight: 700; color: inherit; white-space: nowrap; }
        .filter-select-wrap { display: flex; flex-direction: column; gap: 6px; }
        .filter-select-label { font-size: 9px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.15em; color: #94a3b8; display: flex; align-items: center; gap: 6px; }
        .filter-select { width: 100%; padding: 10px 14px; font-size: 11px; font-weight: 700; border: 1.5px solid #e2e8f0; border-radius: 12px; background: #f8fafc; color: #334155; appearance: none; -webkit-appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='2.5' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19.5 8.25l-7.5 7.5-7.5-7.5'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; background-size: 14px; padding-right: 36px; cursor: pointer; transition: border-color 0.2s, box-shadow 0.2s; outline: none; }
        .filter-select:focus { border-color: #c00000; box-shadow: 0 0 0 3px rgba(192,0,0,0.08); }
        .filter-select:hover { border-color: #cbd5e1; }
        .xls-scroll-wrap { --col1-width: 40px; width: 100%; max-width: 100%; min-width: 0; position: relative; overflow-x: auto; overflow-y: auto; height: calc(100vh - 350px); min-height: 400px; background: white; flex-grow: 1; transition: height 0.4s cubic-bezier(0.4, 0, 0.2, 1); border-top: 1px solid #e2e8f0; }
        .xls-scroll-wrap.expanded { height: calc(100vh - 250px); }
        .pg-btn { padding: 8px 18px; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; border-radius: 9999px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border: 1px solid #e2e8f0; background: white; color: #475569; display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.05); }
        .pg-btn:hover:not(:disabled) { border-color: #ef4444; color: #ef4444; transform: translateY(-2px); box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.15); }
        .pg-btn:disabled { opacity: 0.3; cursor: not-allowed; background: #f1f5f9; }
        .glass-indicator { display: flex; align-items: center; gap: 8px; padding: 8px 16px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .sort-btn { padding: 7px 14px; font-size: 9px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; border-radius: 9999px; border: 1px solid #e2e8f0; background: white; color: #64748b; cursor: pointer; transition: all 0.2s; }
        .sort-btn.active { border-color: #c00000; color: #c00000; background: #fef2f2; }

        /* Dark Mode Overrides */
        html.dark body { background-color: #0f172a; color: #f8fafc; }
        html.dark .bg-white { background-color: #1e293b !important; border-color: #334155 !important; }
        html.dark .text-slate-800 { color: #f8fafc !important; }
        html.dark .text-slate-900 { color: #f8fafc !important; }
        html.dark .bg-slate-50 { background-color: #0f172a !important; border-color: #1e293b !important; }
        html.dark .xls-td { background-color: #1e293b !important; border-color: #334155 !important; }
        html.dark .xls-th { background-color: #0f172a !important; border-color: #334155 !important; color: #94a3b8 !important; }
        html.dark .xls-scroll-wrap { background-color: #1e293b !important; border-color: #334155 !important; }
        html.dark .xls-row:hover .xls-td { background-color: #27212b !important; }
        html.dark .sort-btn { background: #1e293b !important; border-color: #334155 !important; color: #94a3b8 !important; }
        html.dark .sort-btn.active { border-color: #c00000 !important; color: #f87171 !important; background: #2d1a1a !important; }
        html.dark .filter-select { background-color: #1e293b; border-color: #334155; color: #e2e8f0; }
        html.dark .filter-select:focus { border-color: #c00000; }
        html.dark .filter-select-label { color: #64748b; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex text-slate-900 overflow-x-hidden selection:bg-red-100 selection:text-red-900 relative">
    <div class="absolute inset-0 z-[-1] opacity-[0.03] pointer-events-none" style="background-image: radial-gradient(#000 1px, transparent 1px); background-size: 24px 24px;"></div>

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll relative">
    <div class="w-full mx-auto p-6 lg:p-10 min-h-screen flex flex-col relative z-10">

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-10 px-2 animate-fade">
            <div class="shrink-0">
                <h2 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-red-700 to-red-500 uppercase italic leading-tight drop-shadow-sm tracking-tight pb-1 pr-4">Suppliers Registry</h2>
                <p class="text-slate-500 text-[11px] font-bold uppercase tracking-[0.25em] mt-3 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse shadow-[0_0_8px_rgba(239,68,68,0.6)]"></span>
                    Vendor & Supplier Directory
                </p>
            </div>

            <div class="flex-grow max-w-2xl relative">
                <div class="relative group">
                    <input type="text" id="searchInput" oninput="debounceSearch()" placeholder="SEARCH SUPPLIER NAME OR SERVICE CENTER..." autocomplete="off" class="w-full bg-white border-2 border-slate-100 rounded-2xl px-6 py-4 text-xs font-black uppercase tracking-widest focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-700 shadow-sm pr-12 group-hover:border-slate-200">
                    <div class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 group-hover:text-red-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 shrink-0">
                {{-- Filters Toggle --}}
                <button onclick="toggleFilters()" id="toggleFilterBtn" class="px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest text-slate-600 bg-white border border-slate-200 hover:text-[#c00000] hover:-translate-y-0.5 hover:shadow-md active:translate-y-0 transition-all duration-300 flex items-center gap-2 group italic">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 group-hover:rotate-12 transition-transform duration-300"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg>
                    Filters
                </button>

                @if(auth()->check() && auth()->user()->isSuperAdmin() && auth()->user()->isMainSystem())
                <button onclick="openCreateModal()" class="px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest text-white bg-red-700 hover:bg-red-800 hover:-translate-y-0.5 hover:shadow-lg active:translate-y-0 transition-all duration-300 flex items-center gap-2 group italic shadow-md shadow-red-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 group-hover:scale-110 transition-transform duration-300"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Add Supplier
                </button>
                @endif
                <a href="/dashboard" class="px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest text-slate-600 bg-white border border-slate-200 hover:text-[#c00000] hover:-translate-y-0.5 hover:shadow-md active:translate-y-0 transition-all duration-300 flex items-center gap-2 group italic">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 group-hover:-translate-x-1 transition-transform duration-300"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                    Back
                </a>
            </div>
        </div>

        @if(session('success'))
        <div class="mb-6 p-4 rounded-2xl bg-green-50 border border-green-200 text-green-700 text-sm font-bold flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-sm font-bold flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            Please check the form for errors.
        </div>
        @endif

        <!-- Filter Configuration -->
        <div id="filterSection" class="bg-white rounded-[2.5rem] shadow-lg border border-slate-100 p-8 mb-8 relative z-50 animate-fade transition-all duration-300 origin-top hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-8 relative z-10">
                {{-- Sort By --}}
                <div class="filter-select-wrap">
                    <label class="filter-select-label">
                        <svg class="w-3 h-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 7.5L7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5"/></svg>
                        Sort Suppliers
                    </label>
                    <select id="sortSelect" class="filter-select">
                        <option value="az">A–Z (Ascending)</option>
                        <option value="za">Z–A (Descending)</option>
                    </select>
                </div>

                {{-- Asset Count --}}
                <div class="filter-select-wrap">
                    <label class="filter-select-label">
                        <svg class="w-3 h-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/></svg>
                        Asset Count
                    </label>
                    <select id="assetCountSelect" class="filter-select">
                        <option value="">Default (No sorting)</option>
                        <option value="high">Assets ↓ (High to Low)</option>
                        <option value="low">Assets ↑ (Low to High)</option>
                    </select>
                </div>
            </div>
            <div class="mt-8 flex justify-end items-center gap-8 relative z-10 border-t border-slate-100/60 pt-6">
                <button onclick="clearFilters()" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] hover:text-[#c00000] hover:-translate-y-0.5 transition-all duration-300 italic">Reset Default</button>
                <button onclick="applyFilters()" class="px-8 py-2.5 bg-[#c00000] text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-red-800 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-red-200 active:translate-y-0 transition-all duration-300 shadow-md shadow-red-200 italic group flex items-center gap-2">
                    Apply Filters
                    <svg class="w-3.5 h-3.5 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </button>
            </div>
        </div>

        <div class="w-full max-w-full rounded-[2rem] border border-slate-100 shadow-lg overflow-hidden flex flex-col animate-fade relative ring-1 ring-black/5">
            <div class="xls-scroll-wrap expanded">
                <table class="w-full border-collapse" style="min-width:1000px;">
                    <thead><tr>
                        <th class="xls-th w-10 text-center sticky top-0 left-0 z-40 bg-[#f8fafc] dark:bg-[#0f172a]">#</th>
                        <th class="xls-th sticky top-0 z-40 bg-[#f8fafc] dark:bg-[#0f172a]" style="left: var(--col1-width); min-width:260px">Supplier Name</th>
                        <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:200px">Supplier Personnel</th>
                        <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:200px">Service Center</th>
                        <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:160px">Contact Number</th>
                        <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a]" style="min-width:220px">Contact Email</th>
                        <th class="xls-th sticky top-0 z-30 bg-[#f8fafc] dark:bg-[#0f172a] text-center" style="min-width:100px">Assets</th>
                    </tr></thead>
                    <tbody id="supplierBody"></tbody>
                </table>

                {{-- Empty State --}}
                <div id="supplierEmpty" class="absolute inset-0 flex items-center justify-center pointer-events-none transition-all duration-300 bg-white/50 backdrop-blur-[2px]">
                    <div class="inline-flex flex-col items-center gap-4 bg-slate-50/80 px-12 py-10 rounded-[2.5rem] border border-dashed border-slate-200 shadow-sm">
                        <div class="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center text-red-400 shadow-inner">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" /></svg>
                        </div>
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-[0.25em]">No suppliers found — adjust filters</p>
                    </div>
                </div>
            </div>

            <div id="tableFooter" class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 relative z-30 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] bg-white">
                <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6 w-full sm:w-auto">
                    <p id="rowCountLabel" class="text-[9px] font-black text-slate-400 uppercase tracking-widest text-center sm:text-left">0 Rows</p>
                    <div class="flex items-center justify-center gap-3 border-t sm:border-t-0 sm:border-l border-slate-200 pt-4 sm:pt-0 sm:pl-6 w-full sm:w-auto">
                        <button onclick="prevPage()" id="prevBtn" class="pg-btn">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
                            Prev
                        </button>
                        <div class="glass-indicator">
                            <span id="currentPage" class="text-[10px] font-black text-red-600">1</span>
                            <span class="text-[10px] font-bold text-slate-500">/</span>
                            <span id="totalPages" class="text-[10px] font-black text-slate-500">1</span>
                        </div>
                        <button onclick="nextPage()" id="nextBtn" class="pg-btn">
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

    <!-- Hidden Form for Submit -->
    <form id="createForm" method="POST" action="{{ route('admin.suppliers.store') }}" style="display: none;">
        @csrf
        <input type="text" name="name" id="f_name">
        <input type="text" name="supplier_personnel" id="f_supplier_personnel">
        <input type="text" name="service_center" id="f_service_center">
        <input type="text" name="contact_number" id="f_contact_number">
        <input type="text" name="contact_email" id="f_contact_email">
    </form>

    <script>
        let allSuppliers = [];
        let suppliers = [];
        let currentPage = 1;
        const rowsPerPage = 50;
        let currentSort = 'az';

        function fetchSuppliers() {
            const q = document.getElementById('searchInput').value;
            fetch(`/api/suppliers/search?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(data => {
                    allSuppliers = data;
                    currentPage = 1;
                    applySortAndRender();
                });
        }

        let debounceTimer;
        function debounceSearch() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(fetchSuppliers, 300);
        }

        function toggleFilters() {
            const section = document.getElementById('filterSection');
            const btn = document.getElementById('toggleFilterBtn');
            if (section.classList.contains('hidden')) {
                section.classList.remove('hidden');
                btn.classList.add('bg-slate-100', 'border-slate-300');
                btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg> Hide Filters`;
            } else {
                section.classList.add('hidden');
                btn.classList.remove('bg-slate-100', 'border-slate-300');
                btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 group-hover:rotate-12 transition-transform duration-300"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg> Filters`;
            }
        }

        function clearFilters() {
            document.getElementById('sortSelect').value = 'az';
            document.getElementById('assetCountSelect').value = '';
            applyFilters();
        }

        function applyFilters() {
            let countSort = document.getElementById('assetCountSelect').value;
            let nameSort = document.getElementById('sortSelect').value;
            currentSort = countSort ? countSort : nameSort;
            
            const section = document.getElementById('filterSection');
            if (!section.classList.contains('hidden')) {
                toggleFilters();
            }
            applySortAndRender();
        }

        function applySortAndRender() {
            suppliers = [...allSuppliers];
            if (currentSort === 'az') suppliers.sort((a, b) => a.name.localeCompare(b.name));
            else if (currentSort === 'za') suppliers.sort((a, b) => b.name.localeCompare(a.name));
            else if (currentSort === 'high') suppliers.sort((a, b) => (b.asset_count || 0) - (a.asset_count || 0));
            else if (currentSort === 'low') suppliers.sort((a, b) => (a.asset_count || 0) - (b.asset_count || 0));
            currentPage = 1;
            renderTable();
        }

        function renderTable() {
            const tbody = document.getElementById('supplierBody');
            tbody.innerHTML = '';

            if (suppliers.length === 0) {
                document.getElementById('supplierEmpty').classList.remove('hidden');
                document.getElementById('rowCountLabel').textContent = '0 Rows';
                document.getElementById('currentPage').innerText = 1;
                document.getElementById('totalPages').innerText = 1;
                document.getElementById('prevBtn').disabled = true;
                document.getElementById('nextBtn').disabled = true;
                return;
            }

            document.getElementById('supplierEmpty').classList.add('hidden');

            const start = (currentPage - 1) * rowsPerPage;
            const pageData = suppliers.slice(start, start + rowsPerPage);

            pageData.forEach((s, i) => {
                const displayNum = start + i + 1;
                const tr = document.createElement('tr');
                tr.className = 'xls-row group border-b border-slate-100';
                tr.onclick = () => window.location.href = '/admin/suppliers/' + s.id;

                const cell = (val, extra = '') => `<td class="xls-td relative ${extra}"><span class="xls-const uppercase">${val || '-'}</span></td>`;
                const emailCell = (val) => val
                    ? `<td class="xls-td relative"><span class="xls-const text-blue-600 lowercase">${val}</span></td>`
                    : `<td class="xls-td relative"><span class="xls-const">-</span></td>`;
                const countCell = (val) => `<td class="xls-td relative text-center"><span class="px-3 py-1 rounded-full text-[9px] font-black ${(val||0)>0?'bg-red-50 text-red-700':'bg-slate-100 text-slate-500'}">${val||0}</span></td>`;

                tr.innerHTML = `
                    <td class="xls-td text-center sticky left-0 w-10 z-20 bg-white dark:bg-[#1e293b]"><span class="text-[10px] font-black text-slate-500">${displayNum}</span></td>
                    <td class="xls-td relative sticky z-20 bg-white dark:bg-[#1e293b]" style="left: var(--col1-width);">
                        <span class="xls-const font-bold text-slate-800 uppercase">${s.name}</span>
                    </td>
                    ${cell(s.supplier_personnel)}
                    ${cell(s.service_center)}
                    ${cell(s.contact_number)}
                    ${emailCell(s.contact_email)}
                    ${countCell(s.asset_count)}
                `;
                tbody.appendChild(tr);
            });

            const totalPages = Math.ceil(suppliers.length / rowsPerPage) || 1;
            document.getElementById('currentPage').innerText = currentPage;
            document.getElementById('totalPages').innerText = totalPages;
            document.getElementById('rowCountLabel').innerText = `${suppliers.length} Rows`;
            document.getElementById('prevBtn').disabled = currentPage === 1;
            document.getElementById('nextBtn').disabled = currentPage === totalPages;
        }

        function prevPage() { if (currentPage > 1) { currentPage--; renderTable(); } }
        function nextPage() { if (currentPage < Math.ceil(suppliers.length / rowsPerPage)) { currentPage++; renderTable(); } }

        function openCreateModal() {
            Swal.fire({
                title: '<h2 class="text-xl font-black text-slate-800 uppercase tracking-wider">Add Supplier</h2>',
                html: `
                    <div class="text-left space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Supplier Name *</label>
                            <input type="text" id="swal-name" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Supplier Personnel (Optional)</label>
                            <input type="text" id="swal-personnel" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Service Center (Optional)</label>
                            <input type="text" id="swal-center" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Contact Number (Optional)</label>
                            <input type="text" id="swal-phone" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Contact Email (Optional)</label>
                            <input type="email" id="swal-email" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Add Supplier',
                cancelButtonText: 'Cancel',
                customClass: {
                    confirmButton: 'px-6 py-3 rounded-xl text-xs font-black uppercase tracking-wider text-white bg-red-600 hover:bg-red-700 mx-2',
                    cancelButton: 'px-6 py-3 rounded-xl text-xs font-black uppercase tracking-wider text-slate-600 bg-slate-100 hover:bg-slate-200 mx-2',
                    popup: 'rounded-[2rem] p-6'
                },
                buttonsStyling: false,
                preConfirm: () => {
                    const name = document.getElementById('swal-name').value.trim();
                    if (!name) { Swal.showValidationMessage('Supplier name is required'); return false; }
                    return {
                        name,
                        personnel: document.getElementById('swal-personnel').value,
                        center: document.getElementById('swal-center').value,
                        phone: document.getElementById('swal-phone').value,
                        email: document.getElementById('swal-email').value,
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('f_name').value = result.value.name;
                    document.getElementById('f_supplier_personnel').value = result.value.personnel;
                    document.getElementById('f_service_center').value = result.value.center;
                    document.getElementById('f_contact_number').value = result.value.phone;
                    document.getElementById('f_contact_email').value = result.value.email;
                    document.getElementById('createForm').submit();
                }
            });
        }

        document.addEventListener('DOMContentLoaded', fetchSuppliers);
    </script>
</body>
</html>
