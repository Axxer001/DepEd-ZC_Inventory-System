<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Personnel Registry | DepEd Zamboanga City</title>
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
        .xls-th { padding: 14px 16px; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; color: #475569; white-space: nowrap; border-right: 1px solid #e2e8f0; border-bottom: 2px solid #cbd5e1; background: #f8fafc; position: sticky; top: 0; z-index: 20; box-shadow: 0 1px 2px rgba(0,0,0,0.02); }
        .xls-td { height: 52px; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; vertical-align: middle; padding: 0; transition: all 0.2s ease; }
        .xls-row { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; position: relative; }
        .xls-row:hover { transform: translateX(4px); z-index: 10; }
        .xls-row:hover .xls-td { background-color: rgba(192, 0, 0, 0.03) !important; border-bottom-color: #c00000; }
        .xls-row:hover .xls-td:first-child { box-shadow: inset 4px 0 0 #c00000; }
        .xls-const { display: flex; align-items: center; padding: 0 16px; height: 100%; font-size: 11.5px; font-weight: 700; color: inherit; white-space: nowrap; }
        .xls-scroll-wrap { position: relative; overflow-x: auto; overflow-y: auto; height: calc(100vh - 350px); min-height: 400px; background: transparent; flex-grow: 1; transition: height 0.4s cubic-bezier(0.4, 0, 0.2, 1); border-top: 1px solid #e2e8f0; }
        .xls-scroll-wrap.expanded { height: calc(100vh - 250px); }
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
        .filter-container { display: flex; flex-wrap: wrap; gap: 8px; max-height: 200px; overflow-y: auto; padding: 4px; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex text-slate-900 overflow-x-hidden relative">
    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll relative">
        <div class="w-full mx-auto p-6 lg:p-10 min-h-screen flex flex-col relative z-10">

            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-10 px-2 animate-fade">
                <div>
                    <h2 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-red-700 to-red-500 uppercase italic leading-none drop-shadow-sm tracking-tight">Supplier Personnel</h2>
                    <p class="text-slate-500 text-[11px] font-bold uppercase tracking-[0.25em] mt-3 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                        Acquisition Contacts & Provider Personnel
                    </p>
                </div>

                {{-- Main Search --}}
                <div class="flex-grow max-w-2xl relative">
                    <input type="text" id="contactSearch" oninput="contactDebouncedSearch()" placeholder="SEARCH PERSONNEL OR ORGANIZATION..." autocomplete="off" class="w-full bg-white border-2 border-slate-100 rounded-2xl px-6 py-4 text-xs font-black uppercase tracking-widest focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all shadow-sm group-hover:border-slate-200">
                </div>

                <div class="flex items-center gap-4">
                    <button onclick="toggleContactFilters()" id="toggleFilterBtn" class="px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest text-slate-600 bg-white border border-slate-200 hover:text-[#c00000] transition-all italic">Filters</button>
                    <a href="/dashboard" class="px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest text-slate-600 bg-white border border-slate-200 hover:text-[#c00000] transition-all italic">Back</a>
                </div>
            </div>

            <!-- Filter Configuration -->
            <div id="contactFilterSection" class="hidden bg-white rounded-[2.5rem] shadow-xl border border-slate-100 p-8 mb-8 animate-fade">
                <div>
                    <label class="text-[10px] font-black text-slate-900 uppercase tracking-widest italic mb-4 block">Organization</label>
                    <div id="sourceChipContainer" class="filter-container"></div>
                </div>
                <div class="mt-8 flex justify-end gap-8 pt-6 border-t border-slate-50">
                    <button onclick="clearContactFilters()" class="text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-[#c00000] transition-all italic">Clear Filters</button>
                    <button onclick="contactFetchData()" class="px-10 py-4 bg-slate-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-red-600 shadow-lg italic">Apply Configuration</button>
                </div>
            </div>

            <div class="rounded-[2rem] border border-slate-200/60 shadow-xl overflow-hidden flex flex-col animate-fade bg-white">
                <div class="xls-scroll-wrap expanded">
                    <table class="w-full border-collapse" style="min-width:1200px;">
                        <thead>
                            <tr>
                                <th class="xls-th w-10 text-center sticky left-0 z-30">#</th>
                                <th class="xls-th sticky left-[40px] z-30" style="min-width:250px">Personnel Name</th>
                                <th class="xls-th" style="min-width:200px">Position</th>
                                <th class="xls-th" style="min-width:250px">Organization (Source)</th>
                                <th class="xls-th" style="min-width:180px">Contact Number</th>
                                <th class="xls-th" style="min-width:220px">Email Address</th>
                            </tr>
                        </thead>
                        <tbody id="contactBody"></tbody>
                    </table>
                    <div id="contactEmpty" class="absolute inset-0 flex items-center justify-center hidden bg-white/50 backdrop-blur-[2px]">
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">No personnel found</p>
                    </div>
                    <div id="contactLoading" class="absolute inset-0 bg-white/80 z-50 flex items-center justify-center hidden">
                        <div class="w-12 h-12 border-4 border-slate-100 border-t-red-600 rounded-full animate-spin"></div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/30 flex items-center justify-between">
                    <p id="contactRowCountLabel" class="text-[9px] font-black text-slate-400 uppercase tracking-widest">0 Personnel Found</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let contactRowsData = [];

        async function fetchFilters() {
            try {
                const res = await fetch("{{ route('api.supplier_contacts.filters') }}");
                const data = await res.json();
                const container = document.getElementById('sourceChipContainer');
                container.innerHTML = '';
                data.sources.forEach(src => {
                    const chip = document.createElement('div');
                    chip.className = 'filter-chip';
                    chip.textContent = src;
                    chip.dataset.value = src;
                    chip.onclick = () => {
                        const active = chip.classList.contains('active-red');
                        document.querySelectorAll('#sourceChipContainer .filter-chip').forEach(c => c.classList.remove('active-red'));
                        if (!active) chip.classList.add('active-red');
                    };
                    container.appendChild(chip);
                });
            } catch (e) { console.error(e); }
        }

        let searchTimer;
        function contactDebouncedSearch() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => contactFetchData(), 400);
        }

        async function contactFetchData() {
            const loading = document.getElementById('contactLoading');
            loading.classList.remove('hidden');
            const activeChip = document.querySelector('#sourceChipContainer .filter-chip.active-red');
            const filters = {
                source: activeChip ? activeChip.dataset.value : null,
                search: document.getElementById('contactSearch').value
            };
            try {
                const res = await fetch("{{ route('api.supplier_contacts.preview') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ filters: filters })
                });
                const data = await res.json();
                contactRowsData = data.rows || [];
                renderTable();
            } catch (e) { console.error(e); }
            finally { loading.classList.add('hidden'); }
        }

        function renderTable() {
            const tbody = document.getElementById('contactBody');
            tbody.innerHTML = '';
            if (contactRowsData.length === 0) {
                document.getElementById('contactEmpty').classList.remove('hidden');
                document.getElementById('contactRowCountLabel').textContent = '0 Personnel Found';
                return;
            }
            document.getElementById('contactEmpty').classList.add('hidden');
            contactRowsData.forEach((row, idx) => {
                const tr = document.createElement('tr');
                tr.className = 'xls-row group border-b border-slate-100';
                tr.onclick = () => window.location.href = '/admin/supplier-contacts/' + row.id;
                tr.innerHTML = `
                    <td class="xls-td text-center sticky left-0 w-10 bg-slate-50 z-20"><span class="text-[10px] font-black text-slate-500">${idx + 1}</span></td>
                    <td class="xls-td relative sticky left-[40px] bg-slate-50 z-20">
                        <span class="xls-const font-bold text-slate-800 uppercase">${row.name || 'N/A'}</span>
                    </td>
                    <td class="xls-td relative"><span class="xls-const uppercase">${row.position || 'N/A'}</span></td>
                    <td class="xls-td relative"><span class="xls-const font-black text-red-600 uppercase">${row.organization || 'N/A'}</span></td>
                    <td class="xls-td relative"><span class="xls-const">${row.contact_number || 'N/A'}</span></td>
                    <td class="xls-td relative"><span class="xls-const lowercase text-slate-500">${row.email || 'N/A'}</span></td>
                `;
                tbody.appendChild(tr);
            });
            document.getElementById('contactRowCountLabel').textContent = `${contactRowsData.length} Personnel Found`;
        }

        function toggleContactFilters() {
            const section = document.getElementById('contactFilterSection');
            const wrap = document.querySelector('.xls-scroll-wrap');
            section.classList.toggle('hidden');
            wrap.classList.toggle('expanded');
        }

        function clearContactFilters() {
            document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active-red'));
            document.getElementById('contactSearch').value = '';
            contactFetchData();
        }

        document.addEventListener('DOMContentLoaded', () => {
            fetchFilters();
            contactFetchData();
        });
    </script>
</body>
</html>
