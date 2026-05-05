<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Import PIF | DepEd Zamboanga City</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; scroll-behavior: smooth; }
        .custom-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .text-deped { color: #c00000; }
        .bg-deped { background-color: #c00000; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade { animation: fadeIn 0.4s ease-out forwards; }

        .drop-zone { 
            border: 3px dashed #e2e8f0; 
            transition: all 0.3s ease;
        }
        .drop-zone.dragover {
            border-color: #c00000;
            background-color: #fef2f2;
        }

        /* Table pinning */
        .preview-table th, .preview-table td {
            white-space: nowrap;
            min-width: 130px;
        }
        .preview-table th:first-child, .preview-table td:first-child {
            position: sticky;
            left: 0;
            z-index: 2;
        }
    </style>
</head>

<body class="bg-slate-50 min-h-screen flex text-slate-800 overflow-x-hidden">

@include('partials.sidebar')

<div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll">

    {{-- Mobile Header --}}
    <header class="lg:hidden bg-white border-b p-4 flex items-center gap-4 sticky top-0 z-30">
        <button onclick="toggleSidebar()" class="p-2 rounded-xl border bg-slate-50">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-slate-600">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
        </button>
        <div class="flex items-center gap-2">
            <img src="{{ asset('images/deped_logo.png') }}" class="h-6">
            <span class="font-black italic text-sm tracking-tight">DepEd ZC</span>
        </div>
    </header>

    <main class="p-6 lg:p-10 w-full">

        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4 max-w-[1600px] mx-auto">
            <div>
                <h1 class="text-3xl lg:text-4xl font-black text-slate-900 tracking-tight italic uppercase leading-none">Import PIF</h1>
                <p class="text-sm font-bold text-slate-400 uppercase tracking-widest mt-2">Property Inventory Form • Multi-Template Import</p>
            </div>

            <button onclick="window.location.href='/dashboard'"
                class="group px-6 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-600 flex items-center gap-2 shadow-sm hover:border-[#c00000] hover:text-[#c00000] transition-all active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 transition-transform group-hover:-translate-x-1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Back to Dashboard
            </button>
        </div>

        {{-- SUCCESS MESSAGE --}}
        @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    title: 'Import Successful!',
                    text: @json(session('success')),
                    icon: 'success',
                    confirmButtonColor: '#c00000',
                    customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' }
                });
            });
        </script>
        @endif

        {{-- ERROR MESSAGES --}}
        @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    title: 'Import Error',
                    html: `{!! implode('<br>', $errors->all()) !!}`,
                    icon: 'error',
                    confirmButtonColor: '#c00000',
                    customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' }
                });
            });
        </script>
        @endif


        @if(isset($allGroups) && (($totalBuildings ?? 0) > 0 || ($totalAssets ?? 0) > 0))
        {{-- ═══════════════════════════════════════════════════ --}}
        {{-- PREVIEW STATE                                      --}}
        {{-- ═══════════════════════════════════════════════════ --}}
        <div class="animate-fade max-w-[1600px] mx-auto">
            {{-- Stats Bar --}}
            <div class="flex flex-wrap items-center gap-4 mb-6">
                @if(($totalBuildings ?? 0) > 0)
                <div class="bg-white border border-slate-100 rounded-2xl px-6 py-4 shadow-sm flex items-center gap-3">
                    <div class="w-10 h-10 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" /></svg>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Buildings</p>
                        <p class="text-2xl font-black text-slate-900 tracking-tighter">{{ number_format($totalBuildings) }}</p>
                    </div>
                </div>
                @endif
                @if(($totalAssets ?? 0) > 0)
                <div class="bg-white border border-slate-100 rounded-2xl px-6 py-4 shadow-sm flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Assets (PPE PIF / Semi-PPE PIF)</p>
                        <p class="text-2xl font-black text-slate-900 tracking-tighter">{{ number_format($totalAssets) }}</p>
                    </div>
                </div>
                @endif
                <div class="bg-white border border-slate-100 rounded-2xl px-6 py-4 shadow-sm flex items-center gap-3">
                    <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Total Rows</p>
                        <p class="text-2xl font-black text-slate-900 tracking-tighter">{{ number_format(($totalBuildings ?? 0) + ($totalAssets ?? 0)) }}</p>
                    </div>
                </div>
                <div class="flex-grow"></div>
                <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest" id="pageIndicator">Page 1 of 1</div>
            </div>

            {{-- Tab Buttons --}}
            <div class="flex gap-2 mb-4" id="tabButtons"></div>

            {{-- Data Table --}}
            <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl overflow-hidden">
                <div class="overflow-x-auto custom-scroll">
                    <table class="preview-table w-full text-left border-separate border-spacing-0">
                        <thead id="previewTableHead">
                        </thead>
                        <tbody id="previewTableBody" class="text-xs font-bold text-slate-700">
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Controls --}}
                <div class="flex items-center justify-between px-8 py-5 border-t border-slate-100 bg-slate-50/50">
                    <button onclick="goToPage(currentPage - 1)" id="prevBtn"
                        class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-500 hover:border-[#c00000] hover:text-[#c00000] transition-all disabled:opacity-30 disabled:cursor-not-allowed shadow-sm">
                        ← Previous
                    </button>
                    <div class="flex items-center gap-1" id="pageNumbers"></div>
                    <button onclick="goToPage(currentPage + 1)" id="nextBtn"
                        class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-500 hover:border-[#c00000] hover:text-[#c00000] transition-all disabled:opacity-30 disabled:cursor-not-allowed shadow-sm">
                        Next →
                    </button>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex justify-between items-center mt-8">
                <a href="{{ route('buildings.import') }}"
                    class="group px-8 py-4 bg-white border border-slate-200 rounded-2xl text-sm font-black text-slate-500 uppercase tracking-widest flex items-center gap-3 shadow-sm hover:border-red-200 hover:text-[#c00000] transition-all active:scale-95">
                    <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    Cancel
                </a>
                <form action="{{ route('buildings.import.confirm') }}" method="POST">
                    @csrf
                    <button type="submit" onclick="return confirmImport(event)"
                        class="group px-12 py-4 bg-slate-900 text-white rounded-2xl text-sm font-black uppercase tracking-widest shadow-xl hover:bg-[#c00000] transition-all active:scale-95 flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Confirm Registration — {{ number_format(($totalBuildings ?? 0) + ($totalAssets ?? 0)) }} Records
                    </button>
                </form>
            </div>
        </div>

        <script>
            const allGroups = @json($allGroups);
            const ROWS_PER_PAGE = 20;
            let currentTab = null;
            let currentPage = 1;
            let currentRows = [];
            let totalPages = 1;

            const tabs = [];
            if (allGroups.buildings && allGroups.buildings.length > 0) tabs.push({ key: 'buildings', label: 'Buildings', count: allGroups.buildings.length });
            if (allGroups.assets && allGroups.assets.length > 0) tabs.push({ key: 'assets', label: 'Assets (PPE PIF / Semi-PPE PIF)', count: allGroups.assets.length });

            // Build tab buttons
            const tabContainer = document.getElementById('tabButtons');
            if (tabs.length > 1) {
                tabs.forEach(t => {
                    tabContainer.innerHTML += `<button onclick="switchTab('${t.key}')" id="tab_${t.key}"
                        class="px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest transition-all border shadow-sm">${t.label} (${t.count.toLocaleString()})</button>`;
                });
            }

            const buildingHeaders = ['#','Region','Division','Office/School Type','School ID','Office/School Name','Address','Storeys','Classrooms','Article/Item','Description','Classification','Occupancy','Location','Date Constructed','Acquisition Date','Property No.','Acquisition Cost','Appraised Value','Appraisal Date','Remarks'];
            const assetHeaders = ['#','Region','Division','Office/School Type','School ID','Office/School Name','Article/Item','Description','Classification','Occupancy','Location','Acquisition Date','Property No.','Acquisition Cost'];

            function switchTab(key) {
                currentTab = key;
                currentPage = 1;
                currentRows = allGroups[key] || [];
                totalPages = Math.ceil(currentRows.length / ROWS_PER_PAGE) || 1;

                // Update tab button styles
                tabs.forEach(t => {
                    const btn = document.getElementById('tab_' + t.key);
                    if (!btn) return;
                    if (t.key === key) {
                        btn.className = 'px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest transition-all border shadow-sm bg-[#c00000] text-white border-[#c00000]';
                    } else {
                        btn.className = 'px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest transition-all border shadow-sm bg-white text-slate-500 border-slate-200 hover:border-[#c00000] hover:text-[#c00000]';
                    }
                });

                // Render header
                const headers = key === 'buildings' ? buildingHeaders : assetHeaders;
                const thead = document.getElementById('previewTableHead');
                let hHtml = '<tr class="text-[9px] font-black text-slate-400 uppercase tracking-widest bg-slate-50 border-b-2 border-slate-100">';
                headers.forEach((h, i) => {
                    const cls = i === 0 ? 'px-4 py-5 bg-slate-50' : (h.includes('Cost') || h.includes('Value') ? 'px-4 py-5 text-right' : 'px-4 py-5');
                    hHtml += `<th class="${cls}">${h}</th>`;
                });
                hHtml += '</tr>';
                thead.innerHTML = hHtml;

                renderPage(1);
            }

            function renderPage(page) {
                currentPage = page;
                const start = (page - 1) * ROWS_PER_PAGE;
                const end = Math.min(start + ROWS_PER_PAGE, currentRows.length);
                const pageRows = currentRows.slice(start, end);
                const tbody = document.getElementById('previewTableBody');

                let html = '';
                pageRows.forEach((row, idx) => {
                    const n = start + idx + 1;
                    if (currentTab === 'buildings') {
                        html += `<tr class="hover:bg-slate-50/80 transition-colors border-b border-slate-50">
                            <td class="px-4 py-4 text-slate-400 font-black italic bg-white">${n}</td>
                            <td class="px-4 py-4">${esc(row.region)}</td>
                            <td class="px-4 py-4">${esc(row.division)}</td>
                            <td class="px-4 py-4">${esc(row.office_type)}</td>
                            <td class="px-4 py-4 text-slate-500">${esc(row.school_identifier)}</td>
                            <td class="px-4 py-4 text-slate-900">${esc(row.office_name)}</td>
                            <td class="px-4 py-4">${esc(row.address)}</td>
                            <td class="px-4 py-4 text-center">${row.storeys ?? '—'}</td>
                            <td class="px-4 py-4 text-center">${row.classrooms ?? '—'}</td>
                            <td class="px-4 py-4">${esc(row.article)}</td>
                            <td class="px-4 py-4">${esc(row.description)}</td>
                            <td class="px-4 py-4">${esc(row.classification)}</td>
                            <td class="px-4 py-4">${esc(row.occupancy_nature)}</td>
                            <td class="px-4 py-4">${esc(row.location)}</td>
                            <td class="px-4 py-4">${esc(row.date_constructed)}</td>
                            <td class="px-4 py-4">${esc(row.acquisition_date)}</td>
                            <td class="px-4 py-4 text-[#c00000] font-black">${esc(row.property_number)}</td>
                            <td class="px-4 py-4 text-right font-black">${fmtCost(row.acquisition_cost)}</td>
                            <td class="px-4 py-4 text-right">${fmtCost(row.appraised_value)}</td>
                            <td class="px-4 py-4">${esc(row.appraisal_date)}</td>
                            <td class="px-4 py-4 text-slate-500 italic">${esc(row.remarks)}</td>
                        </tr>`;
                    } else {
                        html += `<tr class="hover:bg-slate-50/80 transition-colors border-b border-slate-50">
                            <td class="px-4 py-4 text-slate-400 font-black italic bg-white">${n}</td>
                            <td class="px-4 py-4">${esc(row.region)}</td>
                            <td class="px-4 py-4">${esc(row.division)}</td>
                            <td class="px-4 py-4">${esc(row.office_type)}</td>
                            <td class="px-4 py-4 text-slate-500">${esc(row.school_identifier)}</td>
                            <td class="px-4 py-4 text-slate-900">${esc(row.office_name)}</td>
                            <td class="px-4 py-4">${esc(row.article)}</td>
                            <td class="px-4 py-4">${esc(row.description)}</td>
                            <td class="px-4 py-4">${esc(row.classification)}</td>
                            <td class="px-4 py-4">${esc(row.occupancy_nature)}</td>
                            <td class="px-4 py-4">${esc(row.location)}</td>
                            <td class="px-4 py-4">${esc(row.acquisition_date)}</td>
                            <td class="px-4 py-4 text-[#c00000] font-black">${esc(row.property_number)}</td>
                            <td class="px-4 py-4 text-right font-black">${fmtCost(row.acquisition_cost)}</td>
                        </tr>`;
                    }
                });
                tbody.innerHTML = html;

                document.getElementById('prevBtn').disabled = page <= 1;
                document.getElementById('nextBtn').disabled = page >= totalPages;
                document.getElementById('pageIndicator').textContent = `Page ${page} of ${totalPages}`;

                const pnC = document.getElementById('pageNumbers');
                let pn = '';
                const mv = 5;
                let sp = Math.max(1, page - Math.floor(mv / 2));
                let ep = Math.min(totalPages, sp + mv - 1);
                if (ep - sp < mv - 1) sp = Math.max(1, ep - mv + 1);
                if (sp > 1) pn += '<span class="text-slate-400 text-xs px-1">...</span>';
                for (let i = sp; i <= ep; i++) {
                    pn += `<button onclick="goToPage(${i})" class="w-8 h-8 rounded-lg text-[10px] font-black transition-all ${i === page ? 'bg-[#c00000] text-white shadow-md' : 'text-slate-400 hover:bg-slate-100'}">${i}</button>`;
                }
                if (ep < totalPages) pn += '<span class="text-slate-400 text-xs px-1">...</span>';
                pnC.innerHTML = pn;
            }

            function goToPage(p) { if (p >= 1 && p <= totalPages) renderPage(p); }
            function esc(v) { if (v === null || v === undefined || v === '') return '<span class="text-slate-300">—</span>'; const d = document.createElement('div'); d.textContent = String(v); return d.innerHTML; }
            function fmtCost(v) { return v != null ? '₱' + Number(v).toLocaleString('en-PH', {minimumFractionDigits: 2}) : '<span class="text-slate-300">—</span>'; }

            function confirmImport(e) {
                e.preventDefault();
                const parts = [];
                if (allGroups.buildings && allGroups.buildings.length) parts.push(`<strong>${allGroups.buildings.length.toLocaleString()}</strong> building(s)`);
                if (allGroups.assets && allGroups.assets.length) parts.push(`<strong>${allGroups.assets.length.toLocaleString()}</strong> asset(s)`);
                Swal.fire({
                    title: 'Confirm Import?',
                    html: `<p class="text-sm text-slate-600">You are about to register ${parts.join(' and ')} into the system database.</p><p class="text-xs text-slate-400 mt-2">This action cannot be undone.</p>`,
                    icon: 'question', showCancelButton: true, confirmButtonColor: '#c00000', cancelButtonColor: '#64748b',
                    confirmButtonText: 'Yes, Register All', cancelButtonText: 'Cancel',
                    customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6', cancelButton: 'rounded-xl font-bold px-6' }
                }).then(r => { if (r.isConfirmed) e.target.closest('form').submit(); });
                return false;
            }

            document.addEventListener('DOMContentLoaded', () => switchTab(tabs[0].key));
        </script>

        @else
        {{-- ═══════════════════════════════════════════════════ --}}
        {{-- UPLOAD STATE                                       --}}
        {{-- ═══════════════════════════════════════════════════ --}}
        <div class="max-w-3xl mx-auto animate-fade">
            <form action="{{ route('buildings.import.preview') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                @csrf
                <div class="bg-white rounded-[3rem] border border-slate-100 shadow-xl p-12 lg:p-16">
                    {{-- Upload Zone --}}
                    <div id="dropZone" class="drop-zone rounded-[2.5rem] p-16 text-center cursor-pointer transition-all"
                         onclick="document.getElementById('fileInput').click()">
                        <div class="w-20 h-20 bg-red-50 text-[#c00000] rounded-3xl flex items-center justify-center mx-auto mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                            </svg>
                        </div>
                        <h2 class="text-xl font-black text-slate-800 uppercase tracking-tight italic mb-2">Upload Property Inventory Form</h2>
                        <p class="text-slate-400 font-bold uppercase tracking-widest text-[10px] max-w-sm mx-auto leading-relaxed mb-6">
                            Drop your .xlsx file here or click to browse. The system will auto-detect Building PIF, PPE PIF, and Semi-PPE PIF templates from all sheets.
                        </p>
                        <p id="fileName" class="hidden text-sm font-black text-[#c00000] italic mb-4"></p>
                        <input type="file" name="xlsx_file" id="fileInput" accept=".xlsx,.xls" class="hidden" onchange="handleFileSelect(this)">
                    </div>

                    {{-- Info --}}
                    <div class="mt-8 p-6 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-blue-50 text-blue-500 rounded-lg flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-600 uppercase tracking-widest mb-1">Supported Templates</p>
                                <p class="text-[11px] text-slate-500 font-bold leading-relaxed">
                                    The system auto-detects 3 template types:
                                    <span class="text-amber-600 italic">Building PIF</span>,
                                    <span class="text-blue-600 italic">PPE PIF</span>, and
                                    <span class="text-blue-600 italic">Semi-PPE PIF</span>.
                                    Data rows should start from row 11. Multi-sheet files are fully supported.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit" id="submitBtn" disabled
                        class="w-full mt-8 py-5 bg-slate-200 text-slate-400 rounded-2xl font-black uppercase tracking-widest text-sm transition-all cursor-not-allowed flex items-center justify-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
                        Upload & Preview
                    </button>
                </div>
            </form>
        </div>

        <script>
            const dropZone = document.getElementById('dropZone');
            const fileInput = document.getElementById('fileInput');
            const submitBtn = document.getElementById('submitBtn');
            const fileNameEl = document.getElementById('fileName');

            // Drag and drop
            ['dragenter', 'dragover'].forEach(evt => {
                dropZone.addEventListener(evt, (e) => { e.preventDefault(); dropZone.classList.add('dragover'); });
            });
            ['dragleave', 'drop'].forEach(evt => {
                dropZone.addEventListener(evt, (e) => { e.preventDefault(); dropZone.classList.remove('dragover'); });
            });
            dropZone.addEventListener('drop', (e) => {
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    handleFileSelect(fileInput);
                }
            });

            function handleFileSelect(input) {
                if (input.files.length > 0) {
                    const name = input.files[0].name;
                    fileNameEl.textContent = '📄 ' + name;
                    fileNameEl.classList.remove('hidden');
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('bg-slate-200', 'text-slate-400', 'cursor-not-allowed');
                    submitBtn.classList.add('bg-slate-900', 'text-white', 'hover:bg-[#c00000]', 'shadow-xl', 'cursor-pointer');
                }
            }
        </script>
        @endif

    </main>
</div>

</body>
</html>
