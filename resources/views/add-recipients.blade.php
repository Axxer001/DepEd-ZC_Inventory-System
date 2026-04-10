<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Recipient | DepEd ZC</title>
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

        /* MAANGAS BACK BUTTON HOVER */
        .back-btn-cool {
            background: white;
            border: 1px solid #e2e8f0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            color: #64748b;
        }
        .back-btn-cool:hover {
            border-color: #c00000;
            color: #c00000;
            box-shadow: 0 10px 15px -3px rgba(192, 0, 0, 0.12);
            transform: translateX(-6px);
        }
        .back-btn-cool svg { 
            transition: all 0.3s ease; 
            stroke: currentColor; 
        }
        .back-btn-cool:hover svg {
            transform: translateX(-3px);
            stroke: #c00000;
        }

        /* DYNAMIC TRACK SELECTOR HOVERS */
        .track-btn {
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            background: #f8fafc;
        }
        .track-btn:hover { background: white; transform: translateY(-8px); }

        .track-btn[data-track="school"]:hover, .track-btn[data-track="school"].selected {
            border-color: #f43f5e; color: #e11d48;
            box-shadow: 0 20px 25px -5px rgba(244, 63, 94, 0.15);
        }
        .track-btn[data-track="individual"]:hover, .track-btn[data-track="individual"].selected {
            border-color: #f59e0b; color: #d97706;
            box-shadow: 0 20px 25px -5px rgba(245, 158, 11, 0.15);
        }
        .track-btn[data-track="office"]:hover, .track-btn[data-track="office"].selected {
            border-color: #10b981; color: #059669;
            box-shadow: 0 20px 25px -5px rgba(16, 185, 129, 0.15);
        }

        .track-panel { display: none; }
        .track-panel.active { display: block; animation: slideUp 0.4s ease-out forwards; }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .bounce-slow { animation: bounce-slow 2s infinite; }
        @keyframes bounce-slow {
            0%, 100% { transform: translateY(-10px); }
            50% { transform: translateY(0); }
        }

        .dropdown-animate {
            animation: slideDown 0.2s ease-out forwards;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .sync-row { transition: all 0.3s ease; }
        .sync-row:hover {
            border-color: #e2e8f0;
            background-color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        }
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
                        <h2 class="text-2xl font-black text-slate-900 italic uppercase tracking-tighter">Register Recipient</h2>
                        <p class="text-sm text-slate-400 font-medium">Choose who is receiving the assets — a school, an individual, or an office.</p>
                    </div>

                    @if(session('success'))
                        <div class="mb-8 bg-emerald-50 border border-emerald-100 text-emerald-700 px-6 py-4 rounded-2xl font-bold flex items-center gap-3 animate-in fade-in duration-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            {{ session('success') }}
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="mb-8 bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-2xl font-bold">
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- TRACK SELECTOR --}}
                    <div class="space-y-4 mb-10">
                        <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Recipient Type <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-3 gap-4">
                            <button type="button" id="track-btn-school" data-track="school" onclick="selectTrack('school')" class="track-btn flex flex-col items-center gap-4 p-6 rounded-[2rem] text-rose-500">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" /></svg>
                                <div class="text-center">
                                    <div class="text-[11px] font-black uppercase tracking-widest">School</div>
                                    <div class="text-[9px] opacity-60 font-bold uppercase mt-1">Institutional</div>
                                </div>
                            </button>

                            <button type="button" id="track-btn-individual" data-track="individual" onclick="selectTrack('individual')" class="track-btn flex flex-col items-center gap-4 p-6 rounded-[2rem] text-amber-500">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0ZM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                                <div class="text-center">
                                    <div class="text-[11px] font-black uppercase tracking-widest">Individual</div>
                                    <div class="text-[9px] opacity-60 font-bold uppercase mt-1">Position-Based</div>
                                </div>
                            </button>

                            <button type="button" id="track-btn-office" data-track="office" onclick="selectTrack('office')" class="track-btn flex flex-col items-center gap-4 p-6 rounded-[2rem] text-emerald-500">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" /></svg>
                                <div class="text-center">
                                    <div class="text-[11px] font-black uppercase tracking-widest">Office / Dept</div>
                                    <div class="text-[9px] opacity-60 font-bold uppercase mt-1">Division-Level</div>
                                </div>
                            </button>
                        </div>
                    </div>

                    {{-- ===== TRACK A: SCHOOL ===== --}}
                    <div id="panel-school" class="track-panel space-y-8">
                        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-5">
                            <p class="text-[12px] font-bold text-blue-700 leading-relaxed">                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" /></svg>
 <strong>School Track</strong> — For bulk distributions to a school. Schools are already registered; this track confirms them as recipients and allows you to add rooms/teachers beneath them.</p>
                        </div>
                        <form id="formSchool" action="{{ route('inventory.setup.store_distributor_group') }}" method="POST" class="space-y-8">
                            @csrf
                            <input type="hidden" name="type" value="Recipient">
                            <input type="hidden" name="entity_type" value="School">

                            <div class="space-y-4 relative">
                                <div class="flex items-center justify-between">
                                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">School Name <span class="text-red-500">*</span></label>
                                    <span id="schoolStatusBadge" class="hidden px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest"></span>
                                </div>
                                <input type="text" id="schoolNameInput" name="org_name" placeholder="Search school name..." class="w-full p-6 input-style font-bold text-slate-700" autocomplete="off" onfocus="filterSchoolOrg()" oninput="filterSchoolOrg()">
                                <div id="schoolDropdown" class="hidden absolute z-30 w-full mt-2 bg-white border border-slate-200 rounded-3xl shadow-2xl max-h-[250px] overflow-y-auto custom-scroll"></div>
                            </div>

                            <div class="space-y-6 pt-8 border-t border-slate-50">
                                <div class="flex justify-between items-end ml-1">
                                    <div>
                                        <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Rooms / Teachers (Optional)</label>
                                        <p class="text-[10px] text-slate-300 font-bold mt-1 uppercase italic tracking-tighter">Internal school recipients</p>
                                    </div>
                                    <button type="button" onclick="addSchoolSubField()" class="px-5 py-2.5 bg-slate-900 text-white text-[10px] font-black uppercase rounded-2xl hover:bg-slate-700 transition-all tracking-widest shadow-lg shadow-slate-100">+ Add</button>
                                </div>
                                <div id="schoolSubContainer" class="space-y-3 max-h-[300px] overflow-y-auto pr-2 custom-scroll">
                                    <div class="flex items-center gap-3 group relative animate-in fade-in duration-300">
                                        <input type="text" name="personnel[]" placeholder="e.g. Science Lab 1" class="flex-grow p-5 input-style font-bold text-slate-700 text-sm transition-all pr-24" onkeyup="checkSchoolSub(this)">
                                        <span class="sub-badge hidden absolute right-[4.5rem] top-1/2 -translate-y-1/2 px-2 py-1 rounded-md text-[9px] font-black uppercase tracking-widest"></span>
                                        <button type="button" onclick="this.parentElement.remove()" class="w-12 h-12 flex items-center justify-center text-slate-200 hover:text-red-500 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <button type="button" onclick="confirmSchool()" class="w-full py-7 bg-slate-900 hover:bg-black text-white rounded-[2.5rem] font-black text-lg shadow-2xl shadow-slate-200 transition-all hover:scale-[1.01] active:scale-[0.98] uppercase italic tracking-widest">
                                Register School Recipient
                            </button>
                        </form>
                    </div>

                    {{-- ===== PANEL: INDIVIDUAL ===== --}}
                    <div id="panel-individual" class="track-panel space-y-8">
                        <div class="bg-amber-50 border border-amber-100 rounded-2xl p-5">
                            <p class="text-[12px] font-bold text-amber-700 leading-relaxed"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0ZM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg> <strong>Individual Track</strong> — For a specific person such as a Principal, Head Teacher, or Admin Officer. Tracking follows the person, even if they transfer offices.</p>
                        </div>
                        <form id="formIndividual" action="{{ route('inventory.setup.store_individual_recipient') }}" method="POST" class="space-y-8">
                            @csrf
                            <input type="hidden" name="type" value="Recipient">
                            <input type="hidden" name="entity_type" value="Individual">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1 text-amber-600">Full Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="person_name" placeholder="Full Name" class="w-full p-6 input-style font-bold text-slate-700" required>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1 text-amber-600">Current Position <span class="text-red-500">*</span></label>
                                    <input type="text" name="position" placeholder="Position" class="w-full p-6 input-style font-bold text-slate-700" required>
                                </div>
                            </div>

                            <div class="space-y-2 relative">
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Linked School (Optional)</label>
                                <input type="text" id="indivSchoolInput" name="org_name" placeholder="Search school to link..." class="w-full p-6 input-style font-bold text-slate-700 placeholder:text-slate-300" autocomplete="off" oninput="filterIndivSchool()">
                                <div id="indivSchoolDropdown" class="hidden absolute z-30 w-full mt-2 bg-white border border-slate-200 rounded-3xl shadow-2xl max-h-[200px] overflow-y-auto custom-scroll"></div>
                            </div>

                            <div class="space-y-4">
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Classification Badge</label>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    @foreach(['School' => ['M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z', 'rose'], 'District' => ['M15 10.5a3 3 0 11-6 0 3 3 0 016 0z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z', 'sky'], 'Division' => ['M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z', 'emerald'], 'External' => ['M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3', 'slate']] as $etype => $config)
                                        <label class="flex flex-col items-center gap-3 p-4 rounded-2xl border-2 border-transparent bg-slate-50 hover:bg-white transition-all cursor-pointer group has-[:checked]:border-{{$config[1]}}-500 has-[:checked]:bg-{{$config[1]}}-50/50 text-{{$config[1]}}-600">
                                            <input type="radio" name="individual_entity_type" value="{{ $etype }}" class="hidden" {{ $etype === 'School' ? 'checked' : '' }}>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 group-hover:scale-110 transition-transform"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $config[0] }}" /></svg>
                                            <span class="text-[10px] font-black uppercase">{{ $etype }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <button type="button" onclick="confirmIndividual()" class="w-full py-7 bg-slate-900 hover:bg-black text-white rounded-[2.5rem] font-black text-lg shadow-2xl transition-all uppercase italic tracking-widest">Register Individual</button>
                        </form>
                    </div>

                    {{-- ===== PANEL: OFFICE ===== --}}
                    <div id="panel-office" class="track-panel space-y-8">
                        <div class="bg-green-50 border border-green-100 rounded-2xl p-5">
                            <p class="text-[12px] font-bold text-green-700 leading-relaxed">                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" /></svg>
<strong>Office Track</strong> — For institutional recipients like the Division Office sections or District offices.</p>
                        </div>
                        <form id="formOffice" action="{{ route('inventory.setup.store_individual_recipient') }}" method="POST" class="space-y-8">
                            @csrf
                            <input type="hidden" name="type" value="Recipient">
                            <input type="hidden" name="is_office" value="1">
                            <div class="space-y-4">
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1 text-emerald-600">Office Name <span class="text-red-500">*</span></label>
                                <input type="text" name="org_name" placeholder="e.g. Supply Section" class="w-full p-6 input-style font-bold text-slate-700" required>
                            </div>

                            <div class="space-y-4">
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Office Classification</label>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    @foreach(['Division' => ['M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z', 'emerald'], 'District' => ['M15 10.5a3 3 0 11-6 0 3 3 0 016 0z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z', 'sky'], 'External' => ['M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3', 'slate'], 'Other' => ['M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M20.25 7.5i-16.5 0M20.25 7.5l-2.625-4.5h-11.25L3.75 7.5m16.5 0h-16.5', 'amber']] as $etype => $config)
                                        <label class="flex flex-col items-center gap-3 p-4 rounded-2xl border-2 border-transparent bg-slate-50 hover:bg-white transition-all cursor-pointer group has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50/50 text-{{$config[1]}}-600">
                                            <input type="radio" name="office_entity_type" value="{{ $etype }}" class="hidden" {{ $etype === 'Division' ? 'checked' : '' }}>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 group-hover:scale-110 transition-transform"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $config[0] }}" /></svg>
                                            <span class="text-[10px] font-black uppercase">{{ $etype }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <button type="button" onclick="confirmOffice()" class="w-full py-7 bg-slate-900 hover:bg-black text-white rounded-[2.5rem] font-black text-lg shadow-2xl transition-all uppercase italic tracking-widest">Register Office</button>
                        </form>
                    </div>

                    {{-- EMPTY STATE --}}
                    <div id="panel-empty" class="text-center py-24">
                        <div class="inline-flex flex-col items-center">
                            <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mb-8 bounce-slow shadow-inner">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-10 h-10 text-slate-300">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19.5v-15m0 0l-6.75 6.75M12 4.5l6.75 6.75" />
                                </svg>
                            </div>
                            <h3 class="text-slate-300 text-[10px] font-black uppercase tracking-[0.4em]">Choose a recipient track above</h3>
                        </div>
                    </div>

                </div>

                <div class="lg:col-span-1 bg-white p-8 main-card shadow-[0_20px_50px_rgba(0,0,0,0.02)] border border-slate-50 sticky top-10">
                    <h2 class="text-xl font-black text-slate-900 italic uppercase tracking-tighter leading-tight mb-2">Sync Registry</h2>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-10">Cross-Register Distributors</p>

                    <div class="space-y-4 max-h-[600px] overflow-y-auto pr-2 custom-scroll">
                        @forelse($oppositeMains ?? [] as $main)
                            <div class="sync-row border border-slate-100 rounded-2xl overflow-hidden bg-slate-50/30">
                                <label class="flex items-center gap-3 p-4 hover:bg-white cursor-pointer transition-all group">
                                    <input type="checkbox" name="copy_parents[]" value="{{ $main->id }}" form="formSchool" class="w-5 h-5 text-slate-900 border-slate-200 rounded-lg focus:ring-slate-900 cursor-pointer">
                                    <span class="text-[11px] font-black text-slate-600 uppercase tracking-tight group-hover:text-slate-900 truncate transition-colors">{{ $main->name }}</span>
                                </label>
                                <div id="sub_list_{{ $main->id }}" class="hidden bg-white p-4 border-t border-slate-100 dropdown-animate">
                                    @php $subs = $oppositeSubs->where('parent_id', $main->id); @endphp
                                    @if($subs->count() > 0)
                                        <div class="relative group/dropdown">
                                            <button type="button" class="w-full flex items-center justify-between bg-slate-50 p-3 rounded-xl border border-slate-100 text-[10px] font-black uppercase text-slate-500" onclick="toggleDropdown(this)">
                                                <span class="sub-count-{{ $main->id }}">0 Selected</span>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center border-2 border-dashed border-slate-100 rounded-3xl">
                                <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest leading-relaxed">Cross-registration logic appears here when data is detected.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </main>
    </div>

<script>
    const existingRecipients = @json($recipients ?? []);
    const existingSubRecipients = @json($subRecipients ?? []);
    const allSchools = @json($allSchools ?? []);

    function selectTrack(track) {
        document.querySelectorAll('.track-btn').forEach(btn => btn.classList.remove('selected'));
        document.querySelectorAll('.track-panel').forEach(p => p.classList.remove('active'));
        document.getElementById('panel-empty').style.display = 'none';

        const btn = document.getElementById('track-btn-' + track);
        const panel = document.getElementById('panel-' + track);
        btn.classList.add('selected');
        if (panel) panel.classList.add('active');
    }

    function filterSchoolOrg() {
        const input = document.getElementById('schoolNameInput');
        const dropdown = document.getElementById('schoolDropdown');
        const badge = document.getElementById('schoolStatusBadge');
        const query = input.value.trim().toLowerCase();
        dropdown.classList.remove('hidden');
        
        const filtered = existingRecipients.filter(r => r.name.toLowerCase().includes(query)).slice(0, 15);
        if (filtered.length === 0) {
            dropdown.innerHTML = '<div class="p-6 text-[10px] font-black text-slate-300 text-center uppercase tracking-widest">New Recipient Registration</div>';
        } else {
            dropdown.innerHTML = filtered.map(r => `<div onclick="document.getElementById('schoolNameInput').value='${r.name.replace(/'/g, "\\")}'; document.getElementById('schoolDropdown').classList.add('hidden');" class="px-6 py-4 text-xs font-bold text-slate-600 hover:bg-slate-50 hover:text-red-600 cursor-pointer border-b border-slate-50 last:border-0 truncate">${r.name}</div>`).join('');
        }
    }

    function addSchoolSubField() {
        const container = document.getElementById('schoolSubContainer');
        const div = document.createElement('div');
        div.className = "flex items-center gap-3 group relative animate-in fade-in slide-in-from-top-2 duration-300";
        div.innerHTML = `<input type="text" name="personnel[]" placeholder="e.g. Science Lab 1" class="flex-grow p-5 input-style font-bold text-slate-700 text-sm transition-all pr-24"><button type="button" onclick="this.parentElement.remove()" class="w-12 h-12 flex items-center justify-center text-slate-200 hover:text-red-500 transition-colors"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>`;
        container.appendChild(div);
    }

    function confirmSchool() {
        Swal.fire({ title: 'Confirm Registration', text: 'Register this school?', icon: 'question', showCancelButton: true, confirmButtonColor: '#0f172a' }).then(r => { if(r.isConfirmed) document.getElementById('formSchool').submit(); });
    }

    function confirmIndividual() {
        Swal.fire({ title: 'Confirm Individual', text: 'Register this person?', icon: 'question', showCancelButton: true, confirmButtonColor: '#0f172a' }).then(r => { if(r.isConfirmed) document.getElementById('formIndividual').submit(); });
    }

    function confirmOffice() {
        Swal.fire({ title: 'Confirm Office', text: 'Register this office?', icon: 'question', showCancelButton: true, confirmButtonColor: '#0f172a' }).then(r => { if(r.isConfirmed) document.getElementById('formOffice').submit(); });
    }

    function toggleDropdown(button) {
        const menu = button.nextElementSibling;
        const svg = button.querySelector('svg');
        menu.classList.toggle('hidden');
        svg.classList.toggle('rotate-180');
    }

    document.addEventListener('click', (e) => {
        if (e.target.id !== 'schoolNameInput' && !e.target.closest('#schoolDropdown')) document.getElementById('schoolDropdown')?.classList.add('hidden');
    });
</script>
</body>
</html>