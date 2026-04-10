<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Asset Source | DepEd ZC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .custom-scroll::-webkit-scrollbar { width: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        
        .main-card { border-radius: 2.5rem; }
        
        .input-style {
            background-color: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: 1.25rem;
            transition: all 0.2s ease;
        }
        .input-style:focus {
            background-color: white;
            border-color: #0f172a;
            box-shadow: 0 0 0 4px rgba(15, 23, 42, 0.05);
            outline: none;
        }

        /* MAANGAS BACK BUTTON HOVER WITH RED ARROW */
        .back-btn-cool {
            background: white;
            border: 1px solid #e2e8f0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            color: #64748b; /* Original Slate Color */
        }
        .back-btn-cool:hover {
            border-color: #c00000;
            color: #c00000; /* Text becomes Red */
            box-shadow: 0 10px 15px -3px rgba(192, 0, 0, 0.12);
            transform: translateX(-6px);
        }
        .back-btn-cool svg { 
            transition: all 0.3s ease; 
            stroke: currentColor; /* Sumusunod sa text color */
        }
        .back-btn-cool:hover svg {
            transform: translateX(-3px);
            stroke: #c00000; /* Force Arrow to Red */
        }

        /* DYNAMIC CATEGORY CHIP HOVERS */
        .type-chip {
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #f8fafc;
        }
        .type-chip:hover {
            background: white;
            transform: translateY(-8px);
        }

        /* Color-Matched Accents */
        .type-chip[data-type="Government"]:hover, .type-chip[data-type="Government"].selected {
            border-color: #3b82f6; color: #2563eb;
            box-shadow: 0 20px 25px -5px rgba(59, 130, 246, 0.15);
        }
        .type-chip[data-type="Contractor"]:hover, .type-chip[data-type="Contractor"].selected {
            border-color: #f97316; color: #ea580c;
            box-shadow: 0 20px 25px -5px rgba(249, 115, 22, 0.15);
        }
        .type-chip[data-type="Donor"]:hover, .type-chip[data-type="Donor"].selected {
            border-color: #ec4899; color: #db2777;
            box-shadow: 0 20px 25px -5px rgba(236, 72, 153, 0.15);
        }
        .type-chip[data-type="NGO"]:hover, .type-chip[data-type="NGO"].selected {
            border-color: #10b981; color: #059669;
            box-shadow: 0 20px 25px -5px rgba(16, 185, 129, 0.15);
        }
        .type-chip[data-type="Other"]:hover, .type-chip[data-type="Other"].selected {
            border-color: #64748b; color: #475569;
            box-shadow: 0 20px 25px -5px rgba(100, 116, 139, 0.15);
        }

        .type-chip svg { transition: transform 0.4s ease; }
        .type-chip:hover svg { transform: scale(1.1); }

        .dropdown-animate {
            animation: slideDown 0.2s ease-out forwards;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .sync-row { transition: all 0.3s ease; }
        .sync-row:hover { border-color: #e2e8f0; background-color: white; transform: translateY(-2px); }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex text-slate-800 overflow-x-hidden relative">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll">
        <main class="p-6 lg:p-14">
            
            <header class="flex justify-between items-center mb-12 max-w-7xl mx-auto w-full px-4">
                <div>
                    <h1 class="text-4xl font-black text-slate-900 tracking-tighter italic uppercase leading-none">Inventory Setup</h1>
                    <p class="text-slate-400 text-sm font-bold mt-1 tracking-tight">Zamboanga City Division Asset Management</p>
                </div>
                <a href="{{ url('/inventory-setup?step=2&mode=add') }}" class="back-btn-cool px-7 py-3 rounded-full text-sm font-black uppercase tracking-widest flex items-center gap-2.5 shadow-sm active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                    Back
                </a>
            </header>

            <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8 items-start pb-20">
                
                <div class="lg:col-span-2 bg-white p-12 main-card shadow-[0_20px_60px_rgba(0,0,0,0.02)] border border-slate-100 relative">
                    <div class="mb-10">
                        <h2 class="text-2xl font-black text-slate-900 italic uppercase tracking-tighter">Register Asset Source</h2>
                        <p class="text-sm text-slate-400 font-medium">Categorize the organization and enter the primary office name.</p>
                    </div>

                    @if(session('success'))
                        <div class="mb-8 bg-emerald-50 border border-emerald-100 text-emerald-700 px-6 py-4 rounded-2xl font-bold flex items-center gap-3 animate-in fade-in duration-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            {{ session('success') }}
                        </div>
                    @endif

                    <form id="stakeholderForm" action="{{ route('inventory.setup.store_distributor_group') }}" method="POST" class="space-y-10">
                        @csrf
                        <input type="hidden" name="type" value="Distributor">
                        <input type="hidden" name="source_type" id="sourceTypeHidden" value="">

                        <div class="space-y-4">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Source Category <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3" id="sourceTypeGrid">

                                <button type="button" data-type="Government" class="type-chip flex flex-col items-center gap-3 p-6 rounded-[2rem] text-blue-500" onclick="selectSourceType('Government', this)">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" /></svg>
                                    <span class="text-[10px] font-black uppercase tracking-widest">Government</span>
                                </button>

                                <button type="button" data-type="Contractor" class="type-chip flex flex-col items-center gap-3 p-6 rounded-[2rem] text-orange-500" onclick="selectSourceType('Contractor', this)">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" /></svg>
                                    <span class="text-[10px] font-black uppercase tracking-widest">Contractor</span>
                                </button>

                                <button type="button" data-type="Donor" class="type-chip flex flex-col items-center gap-3 p-6 rounded-[2rem] text-pink-500" onclick="selectSourceType('Donor', this)">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg>
                                    <span class="text-[10px] font-black uppercase tracking-widest">Donor</span>
                                </button>

                                <button type="button" data-type="NGO" class="type-chip flex flex-col items-center gap-3 p-6 rounded-[2rem] text-emerald-500" onclick="selectSourceType('NGO', this)">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-.856.12-1.685.344-2.468m15.372 4.721a11.94 11.94 0 01-5.716 1.247c-2.11 0-4.076-.541-5.783-1.498m11.499 0a8.959 8.959 0 01-4.716 1.498c-2.185 0-4.17-.793-5.716-2.112" /></svg>
                                    <span class="text-[10px] font-black uppercase tracking-widest">NGO</span>
                                </button>

                                <button type="button" data-type="Other" class="type-chip flex flex-col items-center gap-3 p-6 rounded-[2rem] text-slate-500" onclick="selectSourceType('Other', this)">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 01-1.44-4.282m3.102.069a18.03 18.03 0 01-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 018.835 2.535c.608.266 1.333-.042 1.577-.653l.235-.588c.268-.669-.03-1.445-.711-1.73l-1.674-.7a18.33 18.33 0 00-8.262-1.364z" /></svg>
                                    <span class="text-[10px] font-black uppercase tracking-widest">Other</span>
                                </button>
                            </div>
                            <p id="sourceTypeNote" class="text-[11px] text-slate-400 font-bold italic ml-1 hidden py-2 px-4 border-l-2 border-slate-200 bg-slate-50 rounded-lg"></p>
                        </div>

                        <div id="govtSourcesPanel" class="hidden space-y-4 animate-in fade-in duration-300">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Pre-Seeded Agencies</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2" id="govtSourceList"></div>
                        </div>

                        <div class="space-y-4 relative">
                            <div class="flex items-center justify-between">
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Organization Name <span class="text-red-500">*</span></label>
                                <span id="orgStatusBadge" class="hidden px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest"></span>
                            </div>
                            <input type="text" id="orgNameInput" name="org_name" placeholder="Type here..." class="w-full p-6 input-style font-bold text-slate-700 placeholder:text-slate-300" autocomplete="off" onfocus="filterOrgNames()" oninput="filterOrgNames()">
                            <div id="orgDropdown" class="hidden absolute z-30 w-full mt-2 bg-white border border-slate-200 rounded-3xl shadow-2xl max-h-[250px] overflow-y-auto custom-scroll"></div>
                        </div>

                        <div class="space-y-6 pt-10 border-t border-slate-50">
                            <div class="flex justify-between items-end ml-1">
                                <div>
                                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Personnel / Sub-Offices</label>
                                    <p class="text-[10px] text-slate-300 font-bold mt-1 uppercase italic tracking-tighter">Optional additional entries</p>
                                </div>
                                <button type="button" onclick="addPersonnelField()" class="px-5 py-2.5 bg-slate-900 text-white text-[10px] font-black uppercase rounded-2xl hover:bg-slate-700 transition-all tracking-widest shadow-lg shadow-slate-100">+ Add New</button>
                            </div>
                            <div id="personnelContainer" class="space-y-3 max-h-[400px] overflow-y-auto pr-2 custom-scroll">
                                <div class="flex items-center gap-3 group animate-in fade-in slide-in-from-top-2 duration-300 relative">
                                    <input type="text" name="personnel[]" placeholder="Enter personnel or office branch" class="flex-grow p-6 input-style font-bold text-slate-700 text-sm transition-all pr-24" onkeyup="checkSubCategory(this)">
                                    <span class="sub-badge hidden absolute right-[4.5rem] top-1/2 -translate-y-1/2 px-2 py-1 rounded-md text-[9px] font-black uppercase"></span>
                                    <button type="button" onclick="this.parentElement.remove()" class="w-12 h-12 flex items-center justify-center text-slate-200 hover:text-red-500 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button type="button" onclick="confirmRegistration()" class="w-full py-7 bg-slate-900 hover:bg-black text-white rounded-[2.5rem] font-black text-lg shadow-2xl shadow-slate-200 transition-all hover:scale-[1.01] active:scale-[0.98] uppercase italic tracking-widest">
                            Register Asset Source
                        </button>
                    </form>
                </div>

                <div class="lg:col-span-1 bg-white p-8 main-card shadow-[0_20px_50px_rgba(0,0,0,0.02)] border border-slate-50 sticky top-10">
                    <div class="mb-8">
                        <h2 class="text-xl font-black text-slate-900 italic uppercase tracking-tighter leading-tight">Sync Existing<br><span class="text-red-600">Recipients</span></h2>
                        <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mt-1">Cross-register as Distributor</p>
                    </div>

                    <div class="space-y-3 max-h-[550px] overflow-y-auto pr-2 pb-20 custom-scroll">
                        @forelse($oppositeMains as $main)
                            <div class="sync-row border border-slate-100 rounded-2xl overflow-hidden bg-slate-50/30">
                                <label class="flex items-center gap-3 p-4 hover:bg-white cursor-pointer transition-all group">
                                    <input type="checkbox" name="copy_parents[]" value="{{ $main->id }}" form="stakeholderForm" class="w-5 h-5 text-slate-900 border-slate-200 rounded-lg focus:ring-slate-900 cursor-pointer" onchange="toggleSubcategories(this, {{ $main->id }})">
                                    <span class="text-[11px] font-black text-slate-600 uppercase tracking-tight group-hover:text-slate-900 transition-colors truncate">{{ $main->name }}</span>
                                </label>
                            </div>
                        @empty
                            <div class="p-12 text-center border-2 border-dashed border-slate-100 rounded-3xl">
                                <p class="text-[10px] font-black text-slate-300 uppercase tracking-[0.2em]">Registry Empty</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </main>
    </div>

<script>
    const existingDistributors = @json($distributors ?? []);
    const existingSubDistributors = @json($subDistributors ?? []);

    const sourceTypeNotes = {
        'Government': '🏛️ Standard government agencies, LGUs, and DepEd division offices.',
        'Contractor': '🏢 Registered suppliers and contractors from procurement bids.',
        'Donor': '🤝 Individual contributors, PTAs, and private organization donors.',
        'NGO': '🌐 Non-profit organizations and foundations.',
        'Other': '⚙️ Miscellaneous entities outside standard categories.',
    };

    function selectSourceType(type, el) {
        document.querySelectorAll('.type-chip').forEach(btn => btn.classList.remove('selected'));
        el.classList.add('selected');
        document.getElementById('sourceTypeHidden').value = type;
        const note = document.getElementById('sourceTypeNote');
        note.textContent = sourceTypeNotes[type] || '';
        note.classList.remove('hidden');

        const govtPanel = document.getElementById('govtSourcesPanel');
        if (type === 'Government') {
            govtPanel.classList.remove('hidden');
            renderGovtSources();
        } else {
            govtPanel.classList.add('hidden');
        }
    }

    function renderGovtSources() {
        const list = document.getElementById('govtSourceList');
        const govtSources = existingDistributors.filter(d => d.source_type === 'Government');
        if (govtSources.length === 0) {
            list.innerHTML = '<p class="text-[10px] text-slate-300 font-bold uppercase py-6 text-center border border-dashed rounded-2xl border-slate-100">No seeded agencies</p>';
            return;
        }
        list.innerHTML = govtSources.map(d => `
            <button type="button" onclick="fillFromGovt('${d.name.replace(/'/g, "\\'")}')"
                class="flex items-center gap-2 px-4 py-4 bg-slate-50/50 hover:bg-blue-600 hover:text-white text-slate-600 text-[10px] font-black uppercase rounded-2xl text-left transition-all border border-slate-100 group">
                <span class="truncate group-hover:pl-1 transition-all">${d.name}</span>
            </button>
        `).join('');
    }

    function filterOrgNames() {
        const dropdown = document.getElementById('orgDropdown');
        const input = document.getElementById('orgNameInput');
        const badge = document.getElementById('orgStatusBadge');
        const query = input.value.trim().toLowerCase();
        dropdown.classList.remove('hidden');
        const exactMatch = existingDistributors.some(d => d.name.toLowerCase() === query);

        if (query === '') {
            badge.className = 'hidden';
        } else if (exactMatch) {
            badge.className = 'px-3 py-1 rounded-lg text-[9px] font-black uppercase bg-emerald-50 text-emerald-600 border border-emerald-100';
            badge.textContent = 'Existing Record';
        } else {
            badge.className = 'px-3 py-1 rounded-lg text-[9px] font-black uppercase bg-amber-50 text-amber-600 border border-amber-100';
            badge.textContent = 'New Entry';
        }

        const filtered = existingDistributors.filter(d => d.name.toLowerCase().includes(query)).slice(0, 15);
        if (filtered.length === 0) {
            dropdown.innerHTML = '<div class="px-4 py-8 text-[10px] font-black text-slate-300 text-center uppercase tracking-widest">Registering as new</div>';
        } else {
            let html = '<div class="p-4 text-[9px] text-slate-400 font-black uppercase tracking-[0.2em] sticky top-0 bg-white/95 backdrop-blur border-b border-slate-50 z-10">Select Matching</div>';
            html += filtered.map(d => `<div onclick="selectOrg('${d.name.replace(/'/g, "\\'")}')" class="px-6 py-4 text-xs font-bold text-slate-600 hover:bg-slate-50 hover:text-red-600 cursor-pointer transition-colors border-b border-slate-50 last:border-0 truncate">${d.name}</div>`).join('');
            dropdown.innerHTML = html;
        }
    }

    function selectOrg(name) {
        document.getElementById('orgNameInput').value = name;
        document.getElementById('orgDropdown').classList.add('hidden');
        filterOrgNames();
    }

    function addPersonnelField() {
        const container = document.getElementById('personnelContainer');
        const div = document.createElement('div');
        div.className = "flex items-center gap-3 group animate-in fade-in slide-in-from-top-2 duration-300 relative";
        div.innerHTML = `
            <input type="text" name="personnel[]" placeholder="Enter personnel or branch" class="flex-grow p-6 input-style font-bold text-slate-700 text-sm transition-all pr-24" onkeyup="checkSubCategory(this)">
            <span class="sub-badge hidden absolute right-[4.5rem] top-1/2 -translate-y-1/2 px-2 py-1 rounded-md text-[9px] font-black uppercase"></span>
            <button type="button" onclick="this.parentElement.remove()" class="w-12 h-12 flex items-center justify-center text-slate-200 hover:text-red-500 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        `;
        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
    }

    function checkSubCategory(inputElement) {
        const badge = inputElement.parentElement.querySelector('.sub-badge');
        const parentName = document.getElementById('orgNameInput').value.trim().toLowerCase();
        const childName = inputElement.value.trim().toLowerCase();
        if (childName === '') { badge.className = 'hidden'; return; }
        const parentObj = existingDistributors.find(d => d.name.toLowerCase() === parentName);
        if (!parentObj) {
            badge.className = 'absolute right-[4.5rem] top-1/2 -translate-y-1/2 px-2 py-1 rounded-md text-[9px] font-black uppercase bg-amber-50 text-amber-600 border border-amber-100';
            badge.textContent = 'New';
            return;
        }
        const exactMatch = existingSubDistributors.some(sub => sub.parent_id === parentObj.id && sub.name.toLowerCase() === childName);
        badge.className = exactMatch ? 'absolute right-[4.5rem] top-1/2 -translate-y-1/2 px-2 py-1 rounded-md text-[9px] font-black uppercase bg-emerald-50 text-emerald-600 border border-emerald-100' : 'absolute right-[4.5rem] top-1/2 -translate-y-1/2 px-2 py-1 rounded-md text-[9px] font-black uppercase bg-amber-50 text-amber-600 border border-amber-100';
        badge.textContent = exactMatch ? 'Existing' : 'New';
    }

    function confirmRegistration() {
        const form = document.getElementById('stakeholderForm');
        const orgName = form.querySelector('[name="org_name"]').value.trim();
        const sourceType = document.getElementById('sourceTypeHidden').value;
        if (!orgName && document.querySelectorAll('[name="copy_parents[]"]:checked').length === 0) {
            Swal.fire({ title: 'Validation Error', text: 'Enter an organization name or sync existing recipients.', icon: 'error', confirmButtonColor: '#0f172a' });
            return;
        }
        if (orgName && !sourceType) {
            Swal.fire({ title: 'Category Missing', text: 'Please select a source type.', icon: 'warning', confirmButtonColor: '#0f172a' });
            return;
        }
        Swal.fire({
            title: 'Confirm Registration?',
            text: "This will add the source to your asset management registry.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Confirm',
            confirmButtonColor: '#0f172a',
            customClass: { popup: 'rounded-[2.5rem]' }
        }).then((result) => { if (result.isConfirmed) form.submit(); });
    }

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.group\\/dropdown')) document.querySelectorAll('.dropdown-menu').forEach(el => el.classList.add('hidden'));
        if (e.target.id !== 'orgNameInput' && !e.target.closest('#orgDropdown')) document.getElementById('orgDropdown').classList.add('hidden');
    });
</script>
</body>
</html>