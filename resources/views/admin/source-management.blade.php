<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sources Registry | DepEd Zamboanga City</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: { deped: '#c00000', deped_light: '#fef2f2' }
                }
            }
        }
    </script>
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
        .xls-scroll-wrap { position: relative; overflow-x: auto; overflow-y: auto; height: calc(100vh - 350px); min-height: 400px; background: white; flex-grow: 1; transition: height 0.4s cubic-bezier(0.4, 0, 0.2, 1); border-top: 1px solid #e2e8f0; }
        .xls-scroll-wrap.expanded { height: calc(100vh - 250px); }
        .pg-btn { padding: 8px 18px; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; border-radius: 9999px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border: 1px solid #e2e8f0; background: white; color: #475569; display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.05); }
        .pg-btn:hover:not(:disabled) { border-color: #ef4444; color: #ef4444; transform: translateY(-2px); box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.15); }
        .pg-btn:disabled { opacity: 0.3; cursor: not-allowed; background: #f1f5f9; }
        .glass-indicator { display: flex; align-items: center; gap: 8px; padding: 8px 16px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }

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
        html.dark .xls-row:hover .xls-td { background-color: #27212b !important; }
        html.dark .xls-row:active .xls-td { background-color: #35232d !important; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex text-slate-900 overflow-x-hidden selection:bg-red-100 selection:text-red-900 relative">
    <div class="absolute inset-0 z-[-1] opacity-[0.03] pointer-events-none" style="background-image: radial-gradient(#000 1px, transparent 1px); background-size: 24px 24px;"></div>

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll relative">
    <div class="w-full mx-auto p-6 lg:p-10 min-h-screen flex flex-col relative z-10">

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-10 px-2 animate-fade">
            <div class="shrink-0">
                <h2 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-red-700 to-red-500 uppercase italic leading-none drop-shadow-sm tracking-tight">Sources Registry</h2>
                <p class="text-slate-500 text-[11px] font-bold uppercase tracking-[0.25em] mt-3 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse shadow-[0_0_8px_rgba(239,68,68,0.6)]"></span>
                    Acquisition Source Registry
                </p>
            </div>

            <div class="flex-grow max-w-2xl relative">
                <div class="relative group">
                    <input type="text" id="searchInput" oninput="debounceSearch()" placeholder="SEARCH SOURCE NAME OR TYPE..." autocomplete="off" class="w-full bg-white border-2 border-slate-100 rounded-2xl px-6 py-4 text-xs font-black uppercase tracking-widest focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-700 shadow-sm pr-12 group-hover:border-slate-200">
                    <div class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 group-hover:text-red-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 shrink-0">
                @if(auth()->check() && auth()->user()->isSuperAdmin())
                <button onclick="openCreateModal()" class="px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest text-white bg-red-700 hover:bg-red-800 hover:-translate-y-0.5 hover:shadow-lg active:translate-y-0 transition-all duration-300 flex items-center gap-2 group italic shadow-md shadow-red-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 group-hover:scale-110 transition-transform duration-300"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Register Source
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

        <div class="rounded-[2rem] border border-slate-200/60 shadow-xl shadow-slate-200/50 overflow-hidden flex flex-col animate-fade relative ring-1 ring-black/5">
            <div class="xls-scroll-wrap expanded">
                <table class="w-full border-collapse" style="min-width:900px;">
                    <thead><tr>
                        <th class="xls-th w-10 text-center sticky left-0 z-30">#</th>
                        <th class="xls-th sticky left-[40px] z-30" style="min-width:250px">Source Name</th>
                        <th class="xls-th" style="min-width:120px">Type</th>
                        <th class="xls-th" style="min-width:200px">Contact Person</th>
                        <th class="xls-th" style="min-width:200px">Contact Position</th>
                    </tr></thead>
                    <tbody id="sourceBody"></tbody>
                </table>
                
                {{-- Empty State --}}
                <div id="sourceEmpty" class="absolute inset-0 flex items-center justify-center pointer-events-none transition-all duration-300 bg-white/50 backdrop-blur-[2px]">
                    <div class="inline-flex flex-col items-center gap-4 bg-slate-50/80 px-12 py-10 rounded-[2.5rem] border border-dashed border-slate-200 shadow-sm">
                        <div class="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center text-red-400 shadow-inner">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                        </div>
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-[0.25em]">No sources found — adjust filters</p>
                    </div>
                </div>
            </div>

            <div id="tableFooter" class="px-6 py-4 border-t border-slate-100 flex items-center justify-between relative z-30 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] bg-white">
                <div class="flex items-center gap-6">
                    <p id="rowCountLabel" class="text-[9px] font-black text-slate-400 uppercase tracking-widest">0 Rows</p>
                    <div class="flex items-center gap-3 border-l border-slate-200 pl-6">
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
            </div>
        </div>
    </div>
    </div>

    <!-- Hidden Form for Submit -->
    <form id="createForm" method="POST" action="{{ route('admin.sources.store') }}" style="display: none;">
        @csrf
        <input type="text" name="name" id="f_name">
        <input type="text" name="source_type" id="f_source_type">
        <input type="text" name="contact_person" id="f_contact_person">
        <input type="text" name="contact_position" id="f_contact_position">
    </form>

    <script>
        let sources = [];
        let currentPage = 1;
        const rowsPerPage = 50;

        function fetchSources() {
            const q = document.getElementById('searchInput').value;
            fetch(`/api/acquisition-sources/list?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(data => {
                    sources = data;
                    currentPage = 1;
                    renderTable();
                });
        }

        let debounceTimer;
        function debounceSearch() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(fetchSources, 300);
        }

        function renderTable() {
            const tbody = document.getElementById('sourceBody');
            tbody.innerHTML = '';
            
            if (sources.length === 0) {
                document.getElementById('sourceEmpty').classList.remove('hidden');
                document.getElementById('rowCountLabel').textContent = '0 Rows';
                return;
            }
            
            document.getElementById('sourceEmpty').classList.add('hidden');
            
            const start = (currentPage - 1) * rowsPerPage;
            const pageData = sources.slice(start, start + rowsPerPage);
            
            pageData.forEach((s, i) => {
                const displayNum = start + i + 1;
                const tr = document.createElement('tr');
                tr.className = 'xls-row group border-b border-slate-100';
                tr.onclick = () => window.location.href = '/admin/source-management/' + s.id;
                
                const cell = (val, extra = '') => `<td class="xls-td relative ${extra}"><span class="xls-const uppercase">${val || '-'}</span></td>`;
                const typeBadge = (val) => {
                    const isInternal = val === 'Internal';
                    return `<td class="xls-td relative"><span class="xls-const"><span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest ${isInternal ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'}">${val}</span></span></td>`;
                };

                tr.innerHTML = `
                    <td class="xls-td text-center sticky left-0 w-10 z-20"><span class="text-[10px] font-black text-slate-500">${displayNum}</span></td>
                    <td class="xls-td relative sticky left-[40px] z-20">
                        <span class="xls-const font-bold text-slate-800 uppercase">${s.name}</span>
                    </td>
                    ${typeBadge(s.source_type)}
                    ${cell(s.contact_person)}
                    ${cell(s.contact_position)}
                `;
                tbody.appendChild(tr);
            });

            const totalPages = Math.ceil(sources.length / rowsPerPage) || 1;
            document.getElementById('currentPage').innerText = currentPage;
            document.getElementById('totalPages').innerText = totalPages;
            document.getElementById('rowCountLabel').innerText = `${sources.length} Rows`;
            document.getElementById('prevBtn').disabled = currentPage === 1;
            document.getElementById('nextBtn').disabled = currentPage === totalPages;
        }

        function prevPage() { if (currentPage > 1) { currentPage--; renderTable(); } }
        function nextPage() { if (currentPage < Math.ceil(sources.length / rowsPerPage)) { currentPage++; renderTable(); } }

        function openCreateModal() {
            Swal.fire({
                title: '<h2 class="text-xl font-black text-slate-800 uppercase tracking-wider">Register Source</h2>',
                html: `
                    <div class="text-left space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Source Name *</label>
                            <input type="text" id="swal-name" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Source Type *</label>
                            <select id="swal-type" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-red-500">
                                <option value="Internal">Internal (System)</option>
                                <option value="External">External (Distributor)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Contact Person (Optional)</label>
                            <input type="text" id="swal-person" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Contact Position (Optional)</label>
                            <input type="text" id="swal-position" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Register Source',
                cancelButtonText: 'Cancel',
                customClass: {
                    confirmButton: 'px-6 py-3 rounded-xl text-xs font-black uppercase tracking-wider text-white bg-red-600 hover:bg-red-700 mx-2',
                    cancelButton: 'px-6 py-3 rounded-xl text-xs font-black uppercase tracking-wider text-slate-600 bg-slate-100 hover:bg-slate-200 mx-2',
                    popup: 'rounded-[2rem] p-6'
                },
                buttonsStyling: false,
                preConfirm: () => {
                    const name = document.getElementById('swal-name').value;
                    const type = document.getElementById('swal-type').value;
                    if (!name) {
                        Swal.showValidationMessage('Source name is required');
                        return false;
                    }
                    return { name, type, person: document.getElementById('swal-person').value, position: document.getElementById('swal-position').value };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('f_name').value = result.value.name;
                    document.getElementById('f_source_type').value = result.value.type;
                    document.getElementById('f_contact_person').value = result.value.person;
                    document.getElementById('f_contact_position').value = result.value.position;
                    document.getElementById('createForm').submit();
                }
            });
        }

        // Init
        document.addEventListener('DOMContentLoaded', fetchSources);
    </script>
</body>
</html>
