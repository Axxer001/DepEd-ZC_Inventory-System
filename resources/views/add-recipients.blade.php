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
        .main-card { border-radius: 3.5rem; }
        .input-style {
            background-color: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: 1.25rem;
        }
        .input-style:focus {
            background-color: white;
            border-color: #c00000;
            box-shadow: 0 0 0 4px rgba(192, 0, 0, 0.05);
            outline: none;
        }
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
        .track-btn {
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.2s;
        }
        .track-btn.selected {
            border-color: #c00000;
        }
        .track-btn:hover { transform: translateY(-1px); }
        .track-panel { display: none; }
        .track-panel.active { display: block; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex text-slate-800 overflow-x-hidden relative">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll">
        <main class="p-6 lg:p-14">
            <header class="flex justify-between items-center mb-12 max-w-3xl mx-auto w-full px-4">
                <div>
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight italic uppercase">Inventory Setup</h2>
                    <p class="text-slate-500 text-sm font-medium italic">Register New Recipient</p>
                </div>
                <a href="{{ url('/inventory-setup?step=2&mode=add') }}" class="px-6 py-3 back-btn-cool rounded-2xl text-sm font-bold text-slate-600 flex items-center gap-2 shadow-sm active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                    Back
                </a>
            </header>

            <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                <div class="lg:col-span-2 bg-white p-12 main-card shadow-[0_20px_50px_rgba(0,0,0,0.05)] border border-slate-50 relative">
                    <h1 class="text-4xl font-black text-slate-900 mb-2 italic uppercase tracking-tighter">Register Recipient</h1>
                    <p class="text-sm text-slate-400 font-medium mb-8">Choose who is receiving the assets — a school, an individual, or an office.</p>

                    @if(session('success'))
                        <div class="mb-8 bg-emerald-50 border border-emerald-200 text-emerald-700 px-6 py-4 rounded-2xl font-bold flex items-center gap-3">
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
                    <div class="space-y-3 mb-8">
                        <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.15em] ml-1">Recipient Type <span class="text-[#c00000]">*</span></label>
                        <div class="grid grid-cols-3 gap-3">
                            <button type="button" id="track-btn-school" onclick="selectTrack('school')"
                                class="track-btn flex flex-col items-center gap-2 px-4 py-5 rounded-2xl bg-slate-50 hover:bg-red-50 text-slate-600 hover:text-[#c00000] transition-all">
                                <span class="text-2xl">🏫</span>
                                <div class="text-center">
                                    <div class="text-[11px] font-black uppercase tracking-wider">School</div>
                                    <div class="text-[10px] text-slate-400 font-medium mt-0.5">Institutional</div>
                                </div>
                            </button>
                            <button type="button" id="track-btn-individual" onclick="selectTrack('individual')"
                                class="track-btn flex flex-col items-center gap-2 px-4 py-5 rounded-2xl bg-slate-50 hover:bg-red-50 text-slate-600 hover:text-[#c00000] transition-all">
                                <span class="text-2xl">👤</span>
                                <div class="text-center">
                                    <div class="text-[11px] font-black uppercase tracking-wider">Individual</div>
                                    <div class="text-[10px] text-slate-400 font-medium mt-0.5">Position-Based</div>
                                </div>
                            </button>
                            <button type="button" id="track-btn-office" onclick="selectTrack('office')"
                                class="track-btn flex flex-col items-center gap-2 px-4 py-5 rounded-2xl bg-slate-50 hover:bg-red-50 text-slate-600 hover:text-[#c00000] transition-all">
                                <span class="text-2xl">🏢</span>
                                <div class="text-center">
                                    <div class="text-[11px] font-black uppercase tracking-wider">Office / Dept</div>
                                    <div class="text-[10px] text-slate-400 font-medium mt-0.5">Division-Level</div>
                                </div>
                            </button>
                        </div>
                    </div>

                    {{-- ===== TRACK A: SCHOOL ===== --}}
                    <div id="panel-school" class="track-panel hidden">
                        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-5 mb-6">
                            <p class="text-[12px] font-bold text-blue-700">🏫 <strong>School Track</strong> — For bulk distributions to a school. Schools are already registered; this track confirms them as recipients and allows you to add rooms/teachers beneath them.</p>
                        </div>
                        <form id="formSchool" action="{{ route('inventory.setup.store_distributor_group') }}" method="POST" class="space-y-6">
                            @csrf
                            <input type="hidden" name="type" value="Recipient">
                            <input type="hidden" name="entity_type" value="School">

                            <div class="space-y-3 relative">
                                <div class="flex items-center justify-between">
                                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.15em] ml-1">School Name <span class="text-[#c00000]">*</span></label>
                                    <span id="schoolStatusBadge" class="hidden px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-widest"></span>
                                </div>
                                <input type="text" id="schoolNameInput" name="org_name" placeholder="e.g. Ayala National High School" class="w-full p-5 input-style font-bold text-slate-700 transition-all" autocomplete="off" onfocus="filterSchoolOrg()" oninput="filterSchoolOrg()">
                                <div id="schoolDropdown" class="hidden absolute z-30 w-full mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-[250px] overflow-y-auto custom-scroll"></div>
                            </div>

                            <div class="space-y-6 pt-6 border-t border-slate-50">
                                <div class="flex justify-between items-end ml-1">
                                    <div>
                                        <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.15em]">Rooms / Teachers (Optional)</label>
                                        <p class="text-[10px] text-slate-300 font-bold mt-1 italic">Add specific rooms or teacher names under this school</p>
                                    </div>
                                    <button type="button" onclick="addSchoolSubField()" class="px-4 py-2 bg-slate-50 text-slate-500 text-[10px] font-black uppercase rounded-xl hover:bg-red-50 hover:text-[#c00000] transition-all tracking-wider">+ Add</button>
                                </div>
                                <div id="schoolSubContainer" class="space-y-3 max-h-[250px] overflow-y-auto pr-2 custom-scroll">
                                    <div class="flex items-center gap-3 group relative">
                                        <input type="text" name="personnel[]" placeholder="e.g. Science Lab 1 or Juan Dela Cruz" class="flex-grow p-5 input-style font-bold text-slate-700 text-sm transition-all pr-24" onkeyup="checkSchoolSub(this)">
                                        <span class="sub-badge hidden absolute right-[4.5rem] top-1/2 -translate-y-1/2 px-2 py-1 rounded-md text-[9px] font-black uppercase tracking-widest"></span>
                                        <button type="button" onclick="this.parentElement.remove()" class="w-12 h-12 flex items-center justify-center text-slate-200 hover:text-red-500 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <button type="button" onclick="confirmSchool()" class="w-full py-6 bg-[#c00000] hover:bg-[#a00000] text-white rounded-[1.5rem] font-bold text-lg shadow-xl shadow-red-100 transition-all hover:scale-[1.01] active:scale-[0.98]">
                                Register School Recipient
                            </button>
                        </form>
                    </div>

                    {{-- ===== TRACK B: INDIVIDUAL ===== --}}
                    <div id="panel-individual" class="track-panel hidden">
                        <div class="bg-amber-50 border border-amber-100 rounded-2xl p-5 mb-6">
                            <p class="text-[12px] font-bold text-amber-700">👤 <strong>Individual Track</strong> — For a specific person such as a Principal, Head Teacher, or Admin Officer. When they transfer, the system will flag their items for resolution.</p>
                        </div>
                        <form id="formIndividual" action="{{ route('inventory.setup.store_individual_recipient') }}" method="POST" class="space-y-6">
                            @csrf
                            <input type="hidden" name="type" value="Recipient">
                            <input type="hidden" name="entity_type" value="Individual">

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.15em] ml-1">Full Name <span class="text-[#c00000]">*</span></label>
                                    <input type="text" name="person_name" placeholder="e.g. Maria Santos" class="w-full p-5 input-style font-bold text-slate-700 transition-all" required>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.15em] ml-1">Position / Role <span class="text-[#c00000]">*</span></label>
                                    <input type="text" name="position" placeholder="e.g. Principal, Head Teacher, Admin Officer" class="w-full p-5 input-style font-bold text-slate-700 transition-all" required>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.15em] ml-1">Linked School (Optional)</label>
                                <p class="text-[10px] text-slate-400 font-bold ml-1">Leave blank for Division-level staff not tied to a specific school.</p>
                                <div class="relative">
                                    <input type="text" id="indivSchoolInput" name="org_name" placeholder="e.g. Zamboanga City High School (leave blank if Division-level)" class="w-full p-5 input-style font-bold text-slate-700 transition-all" autocomplete="off" oninput="filterIndivSchool()">
                                    <div id="indivSchoolDropdown" class="hidden absolute z-30 w-full mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-[200px] overflow-y-auto custom-scroll"></div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.15em] ml-1">Classification</label>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                    @foreach(['School' => '🏫', 'District' => '📍', 'Division' => '🏛️', 'External' => '🌐'] as $etype => $icon)
                                        <label class="flex items-center gap-2 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 cursor-pointer transition-all border-2 border-transparent has-[:checked]:border-[#c00000] has-[:checked]:bg-red-50">
                                            <input type="radio" name="individual_entity_type" value="{{ $etype }}" class="w-4 h-4 text-[#c00000] accent-[#c00000]" {{ $etype === 'School' ? 'checked' : '' }}>
                                            <span class="text-xs font-bold text-slate-600">{{ $icon }} {{ $etype }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <button type="button" onclick="confirmIndividual()" class="w-full py-6 bg-[#c00000] hover:bg-[#a00000] text-white rounded-[1.5rem] font-bold text-lg shadow-xl shadow-red-100 transition-all hover:scale-[1.01] active:scale-[0.98]">
                                Register Individual Recipient
                            </button>
                        </form>
                    </div>

                    {{-- ===== TRACK C: OFFICE/DEPT ===== --}}
                    <div id="panel-office" class="track-panel hidden">
                        <div class="bg-green-50 border border-green-100 rounded-2xl p-5 mb-6">
                            <p class="text-[12px] font-bold text-green-700">🏢 <strong>Office / Department Track</strong> — For offices like the DepEd Division Office, District Office, or an external agency. No school linkage required.</p>
                        </div>
                        <form id="formOffice" action="{{ route('inventory.setup.store_individual_recipient') }}" method="POST" class="space-y-6">
                            @csrf
                            <input type="hidden" name="type" value="Recipient">
                            <input type="hidden" name="is_office" value="1">

                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.15em] ml-1">Office Name <span class="text-[#c00000]">*</span></label>
                                    <span id="officeStatusBadge" class="hidden px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-widest"></span>
                                </div>
                                <div class="relative">
                                    <input type="text" id="officeNameInput" name="org_name" placeholder="e.g. DepEd Division Office — Zamboanga City" class="w-full p-5 input-style font-bold text-slate-700 transition-all" autocomplete="off" oninput="filterOfficeNames()">
                                    <div id="officeDropdown" class="hidden absolute z-30 w-full mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-[250px] overflow-y-auto custom-scroll"></div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.15em] ml-1">Office Classification</label>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                    @foreach(['Division' => '🏛️', 'District' => '📍', 'External' => '🌐', 'Individual' => '👤'] as $etype => $icon)
                                        <label class="flex items-center gap-2 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 cursor-pointer transition-all border-2 border-transparent has-[:checked]:border-[#c00000] has-[:checked]:bg-red-50">
                                            <input type="radio" name="office_entity_type" value="{{ $etype }}" class="w-4 h-4 text-[#c00000] accent-[#c00000]" {{ $etype === 'Division' ? 'checked' : '' }}>
                                            <span class="text-xs font-bold text-slate-600">{{ $icon }} {{ $etype }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <button type="button" onclick="confirmOffice()" class="w-full py-6 bg-[#c00000] hover:bg-[#a00000] text-white rounded-[1.5rem] font-bold text-lg shadow-xl shadow-red-100 transition-all hover:scale-[1.01] active:scale-[0.98]">
                                Register Office / Department
                            </button>
                        </form>
                    </div>

                    {{-- Default empty state --}}
                    <div id="panel-empty" class="text-center py-12 text-slate-300">
                        <div class="text-5xl mb-4">☝️</div>
                        <p class="font-bold text-sm">Select a recipient type above to continue</p>
                    </div>

                </div>

                {{-- CROSS-REGISTRATION PANEL --}}
                <div class="lg:col-span-1 bg-white p-8 main-card shadow-[0_20px_50px_rgba(0,0,0,0.05)] border border-slate-50 relative sticky top-8">
                    <h2 class="text-2xl font-black text-slate-900 mb-3 italic uppercase tracking-tighter leading-tight">Also Add Existing<br><span class="text-[#c00000]">Distributors</span></h2>
                    <p class="text-[11px] font-bold text-slate-400 mb-6 leading-relaxed">Select existing Distributors to also register them as Recipients.</p>

                    <div class="space-y-3 max-h-[600px] overflow-y-auto pr-2 pb-48 custom-scroll">
                        @forelse($oppositeMains as $main)
                            <div class="border border-slate-100 rounded-xl transition-all hover:border-red-100 relative bg-white">
                                <label class="flex items-center gap-3 p-4 bg-slate-50 hover:bg-red-50 cursor-pointer transition-colors group rounded-t-xl {{ $oppositeSubs->where('parent_id', $main->id)->count() === 0 ? 'rounded-b-xl' : '' }}">
                                    <input type="checkbox" name="copy_parents[]" value="{{ $main->id }}" form="formSchool" class="cross-checkbox w-4 h-4 text-[#c00000] border-slate-300 rounded focus:ring-[#c00000] cursor-pointer transition-all" onchange="toggleSubcategories(this, {{ $main->id }})">
                                    <span class="text-xs font-black text-slate-700 uppercase tracking-wider group-hover:text-[#c00000] transition-colors truncate">{{ $main->name }}</span>
                                </label>
                                <div id="sub_list_{{ $main->id }}" class="hidden bg-slate-50/50 p-4 border-t border-slate-100">
                                    @php $subs = $oppositeSubs->where('parent_id', $main->id); @endphp
                                    @if($subs->count() > 0)
                                        <div class="relative group/dropdown">
                                            <button type="button" class="w-full flex items-center justify-between bg-white p-3 rounded-xl border border-slate-200 text-[11px] font-bold text-slate-600 hover:border-slate-300 transition-colors shadow-sm" onclick="toggleDropdown(this)">
                                                <span class="sub-count-{{ $main->id }}">0 / {{ $subs->count() }} Selected</span>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            <div class="hidden absolute z-50 left-0 right-0 mt-2 bg-white border border-slate-200 shadow-[0_10px_40px_rgba(0,0,0,0.1)] rounded-xl p-2 dropdown-menu">
                                                <div class="sticky top-0 bg-white pb-2 z-10 border-b border-slate-100 mb-2">
                                                    <input type="text" placeholder="Search {{ $subs->count() }} items..." class="w-full text-[10px] p-2.5 bg-slate-50 border border-slate-100 rounded-lg outline-none focus:border-red-200 transition-all font-bold text-slate-600" onkeyup="filterDropdown(this)">
                                                </div>
                                                <div class="space-y-0.5 max-h-48 overflow-y-auto custom-scroll pr-1">
                                                    @foreach($subs as $sub)
                                                        <label class="flex items-center gap-3 cursor-pointer group py-2 px-3 hover:bg-slate-50 rounded-lg transition-colors">
                                                            <input type="checkbox" name="copy_children[]" value="{{ $sub->id }}" form="formSchool" class="w-4 h-4 text-[#c00000] border-slate-300 rounded focus:ring-[#c00000] cursor-pointer sub-checkbox-{{ $main->id }} transition-all" onchange="updateSubCount({{ $main->id }}, {{ $subs->count() }})">
                                                            <span class="text-[11px] font-bold text-slate-600 group-hover:text-slate-900 transition-colors truncate searchable-text">{{ $sub->name }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <p class="text-[10px] italic font-bold text-slate-400 text-center py-2">No sub-categories found.</p>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-center border-2 border-dashed border-slate-100 rounded-2xl">
                                <p class="text-xs font-bold text-slate-400 italic">No existing Distributors found.</p>
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

    let currentTrack = null;

    function selectTrack(track) {
        currentTrack = track;
        // Update button styles
        ['school', 'individual', 'office'].forEach(t => {
            const btn = document.getElementById('track-btn-' + t);
            if (t === track) {
                btn.classList.add('selected', 'bg-red-50', 'text-[#c00000]');
                btn.classList.remove('bg-slate-50', 'text-slate-600');
            } else {
                btn.classList.remove('selected', 'bg-red-50', 'text-[#c00000]');
                btn.classList.add('bg-slate-50', 'text-slate-600');
            }
        });

        // Show/hide panels
        document.getElementById('panel-empty').style.display = 'none';
        ['school', 'individual', 'office'].forEach(t => {
            const panel = document.getElementById('panel-' + t);
            panel.style.display = (t === track) ? 'block' : 'none';
        });
    }

    // ======= TRACK A: SCHOOL =======
    function filterSchoolOrg() {
        const input = document.getElementById('schoolNameInput');
        const dropdown = document.getElementById('schoolDropdown');
        const badge = document.getElementById('schoolStatusBadge');
        const query = input.value.trim().toLowerCase();

        dropdown.classList.remove('hidden');
        const exactMatch = existingRecipients.some(r => r.name.toLowerCase() === query);

        if (query === '') { badge.className = 'hidden'; }
        else if (exactMatch) {
            badge.className = 'px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-600 border border-emerald-200';
            badge.textContent = 'Existing';
        } else {
            badge.className = 'px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest bg-amber-50 text-amber-600 border border-amber-200';
            badge.textContent = 'New Entry';
        }

        const filtered = existingRecipients.filter(r => r.name.toLowerCase().includes(query)).slice(0, 30);
        if (filtered.length === 0) {
            dropdown.innerHTML = '<div class="px-4 py-4 text-sm font-bold text-slate-400 text-center italic">Type to create a new school recipient</div>';
        } else {
            let html = '<div class="p-3 text-[10px] text-slate-400 font-extrabold uppercase tracking-widest sticky top-0 bg-white/90 backdrop-blur border-b border-slate-100 z-10">Select existing or type new</div>';
            html += filtered.map(r => `<div onclick="document.getElementById('schoolNameInput').value='${r.name.replace(/'/g, "\\'")}'; document.getElementById('schoolDropdown').classList.add('hidden'); filterSchoolOrg();" class="px-4 py-3 text-sm font-bold text-slate-700 hover:bg-red-50 hover:text-[#c00000] cursor-pointer transition-colors border-b border-slate-50 last:border-0 truncate">${r.name}</div>`).join('');
            dropdown.innerHTML = html;
        }
    }

    function addSchoolSubField() {
        const container = document.getElementById('schoolSubContainer');
        const div = document.createElement('div');
        div.className = "flex items-center gap-3 group animate-in fade-in slide-in-from-top-2 duration-300 relative";
        div.innerHTML = `
            <input type="text" name="personnel[]" form="formSchool" placeholder="e.g. Science Lab 1 or Juan Dela Cruz" class="flex-grow p-5 input-style font-bold text-slate-700 text-sm transition-all pr-24" onkeyup="checkSchoolSub(this)">
            <span class="sub-badge hidden absolute right-[4.5rem] top-1/2 -translate-y-1/2 px-2 py-1 rounded-md text-[9px] font-black uppercase tracking-widest"></span>
            <button type="button" onclick="this.parentElement.remove()" class="w-12 h-12 flex items-center justify-center text-slate-200 hover:text-red-500 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        `;
        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
    }

    function checkSchoolSub(inputElement) {
        const badge = inputElement.parentElement.querySelector('.sub-badge');
        const parentName = document.getElementById('schoolNameInput')?.value.trim().toLowerCase() || '';
        const childName = inputElement.value.trim().toLowerCase();
        if (childName === '') { badge.className = 'hidden'; return; }
        const parentObj = existingRecipients.find(r => r.name.toLowerCase() === parentName);
        if (!parentObj) {
            badge.className = 'absolute right-[4.5rem] top-1/2 -translate-y-1/2 px-2 py-1 rounded-md text-[9px] font-bold tracking-widest bg-amber-50 text-amber-600 border border-amber-200 uppercase';
            badge.textContent = 'New Entry'; return;
        }
        const exactMatch = existingSubRecipients.some(sub => sub.parent_id === parentObj.id && sub.name.toLowerCase() === childName);
        badge.className = `absolute right-[4.5rem] top-1/2 -translate-y-1/2 px-2 py-1 rounded-md text-[9px] font-bold tracking-widest ${exactMatch ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-amber-50 text-amber-600 border border-amber-200'} uppercase`;
        badge.textContent = exactMatch ? 'Existing' : 'New Entry';
    }

    function confirmSchool() {
        const form = document.getElementById('formSchool');
        const orgName = form.querySelector('[name="org_name"]').value.trim();
        const checkedParents = document.querySelectorAll('[name="copy_parents[]"]:checked');
        const hasCross = checkedParents.length > 0;

        if (!orgName && !hasCross) {
            Swal.fire({ title: 'Nothing to Register', text: 'Please enter a school name or select Distributors to cross-register.', icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
            return;
        }

        let summaryLines = [];
        if (orgName) summaryLines.push(`<div class="flex items-start gap-2"><span class="text-[#c00000] font-bold">•</span> School Recipient: <strong>🏫 ${orgName}</strong></div>`);
        if (hasCross) {
            const names = Array.from(checkedParents).map(cb => cb.closest('label').querySelector('span').textContent.trim());
            summaryLines.push(`<div class="mt-2 text-emerald-700 font-bold">+ Cross-register ${names.length} Distributor(s) as Recipients</div>`);
        }

        Swal.fire({
            title: 'Confirm Registration',
            html: `<div class="text-left text-sm text-slate-600 leading-relaxed space-y-1 font-medium">${summaryLines.join('')}</div>`,
            icon: 'question', showCancelButton: true, confirmButtonText: 'Yes, Register', cancelButtonText: 'Cancel',
            confirmButtonColor: '#c00000', cancelButtonColor: '#64748b',
            customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6', cancelButton: 'rounded-xl font-bold px-6' }
        }).then(r => { if (r.isConfirmed) form.submit(); });
    }

    // ======= TRACK B: INDIVIDUAL =======
    function filterIndivSchool() {
        const input = document.getElementById('indivSchoolInput');
        const dropdown = document.getElementById('indivSchoolDropdown');
        const query = input.value.trim().toLowerCase();
        dropdown.classList.remove('hidden');
        if (query === '') { dropdown.classList.add('hidden'); return; }
        
        const filtered = allSchools.filter(r => (r.name.toLowerCase().includes(query) || (r.school_id && r.school_id.toString().includes(query)))).slice(0, 20);
        if (filtered.length === 0) { dropdown.innerHTML = '<div class="px-4 py-3 text-sm text-slate-400 font-bold text-center italic">No match — leave blank for Division-level</div>'; return; }
        
        let html = '<div class="p-3 text-[10px] text-slate-400 font-extrabold uppercase tracking-widest sticky top-0 bg-white/90 backdrop-blur border-b border-slate-100 z-10">Select school link</div>';
        html += filtered.map(r => `<div onclick="document.getElementById('indivSchoolInput').value='${r.name.replace(/'/g, "\\'")}';document.getElementById('indivSchoolDropdown').classList.add('hidden')" class="px-4 py-3 text-sm font-bold text-slate-700 hover:bg-red-50 hover:text-[#c00000] cursor-pointer border-b border-slate-50 last:border-0 truncate">${r.school_id ? r.school_id+' - ' : ''}${r.name}</div>`).join('');
        dropdown.innerHTML = html;
    }

    document.addEventListener('click', function(e) {
        if (!document.getElementById('indivSchoolInput')?.contains(e.target) && !document.getElementById('indivSchoolDropdown')?.contains(e.target))
            document.getElementById('indivSchoolDropdown')?.classList.add('hidden');
    });

    function confirmIndividual() {
        const form = document.getElementById('formIndividual');
        const personName = form.querySelector('[name="person_name"]').value.trim();
        const position = form.querySelector('[name="position"]').value.trim();

        if (!personName || !position) {
            Swal.fire({ title: 'Required Fields', text: 'Please fill in both the full name and position.', icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
            return;
        }

        const linkedSchool = form.querySelector('[name="org_name"]').value.trim();
        const entityType = form.querySelector('[name="individual_entity_type"]:checked')?.value;

        let summaryLines = [
            `<div class="flex items-start gap-2"><span class="text-[#c00000] font-bold">•</span> 👤 <strong>${personName}</strong></div>`,
            `<div class="flex items-start gap-2"><span class="text-slate-400 font-bold">•</span> Position: <strong>${position}</strong></div>`,
            `<div class="flex items-start gap-2"><span class="text-slate-400 font-bold">•</span> Classification: <strong>${entityType}</strong></div>`,
        ];
        if (linkedSchool) summaryLines.push(`<div class="flex items-start gap-2"><span class="text-slate-400 font-bold">•</span> Linked School: <strong>${linkedSchool}</strong></div>`);
        else summaryLines.push(`<div class="flex items-start gap-2"><span class="text-blue-500 font-bold">•</span> No school linkage — Division-level</div>`);

        Swal.fire({
            title: 'Confirm Registration',
            html: `<div class="text-left text-sm text-slate-600 leading-relaxed space-y-1 font-medium">${summaryLines.join('')}</div>`,
            icon: 'question', showCancelButton: true, confirmButtonText: 'Yes, Register', cancelButtonText: 'Cancel',
            confirmButtonColor: '#c00000', cancelButtonColor: '#64748b',
            customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6', cancelButton: 'rounded-xl font-bold px-6' }
        }).then(r => { if (r.isConfirmed) form.submit(); });
    }

    // ======= TRACK C: OFFICE =======
    function filterOfficeNames() {
        const input = document.getElementById('officeNameInput');
        const dropdown = document.getElementById('officeDropdown');
        const badge = document.getElementById('officeStatusBadge');
        const query = input.value.trim().toLowerCase();
        dropdown.classList.remove('hidden');
        const exactMatch = existingRecipients.some(r => r.name.toLowerCase() === query);
        if (query === '') { badge.className = 'hidden'; }
        else if (exactMatch) {
            badge.className = 'px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-600 border border-emerald-200';
            badge.textContent = 'Existing';
        } else {
            badge.className = 'px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest bg-amber-50 text-amber-600 border border-amber-200';
            badge.textContent = 'New Entry';
        }
        const filtered = existingRecipients.filter(r => r.name.toLowerCase().includes(query)).slice(0, 20);
        if (filtered.length === 0) { dropdown.innerHTML = '<div class="px-4 py-4 text-sm font-bold text-slate-400 text-center italic">Type to create new</div>'; return; }
        dropdown.innerHTML = '<div class="p-3 text-[10px] text-slate-400 font-extrabold uppercase tracking-widest sticky top-0 bg-white/90 backdrop-blur border-b border-slate-100">Existing or create new</div>' +
            filtered.map(r => `<div onclick="document.getElementById('officeNameInput').value='${r.name.replace(/'/g, "\\'")}';document.getElementById('officeDropdown').classList.add('hidden');filterOfficeNames()" class="px-4 py-3 text-sm font-bold text-slate-700 hover:bg-red-50 hover:text-[#c00000] cursor-pointer border-b border-slate-50 last:border-0 truncate">${r.name}</div>`).join('');
    }

    document.addEventListener('click', function(e) {
        if (!document.getElementById('officeNameInput')?.contains(e.target) && !document.getElementById('officeDropdown')?.contains(e.target))
            document.getElementById('officeDropdown')?.classList.add('hidden');
        if (!document.getElementById('schoolNameInput')?.contains(e.target) && !document.getElementById('schoolDropdown')?.contains(e.target))
            document.getElementById('schoolDropdown')?.classList.add('hidden');
    });

    function confirmOffice() {
        const form = document.getElementById('formOffice');
        const officeName = form.querySelector('[name="org_name"]').value.trim();
        const entityType = form.querySelector('[name="office_entity_type"]:checked')?.value;

        if (!officeName) {
            Swal.fire({ title: 'Office Name Required', text: 'Please enter the name of the office or department.', icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
            return;
        }

        const icons = { Division: '🏛️', District: '📍', External: '🌐', Individual: '👤' };
        let summaryLines = [
            `<div class="flex items-start gap-2"><span class="text-[#c00000] font-bold">•</span> 🏢 Office: <strong>${officeName}</strong></div>`,
            `<div class="flex items-start gap-2"><span class="text-slate-400 font-bold">•</span> Type: <strong>${icons[entityType] || ''} ${entityType}</strong></div>`,
            `<div class="flex items-start gap-2"><span class="text-blue-500 font-bold">•</span> No school linkage (Office-level recipient)</div>`,
        ];

        Swal.fire({
            title: 'Confirm Registration',
            html: `<div class="text-left text-sm text-slate-600 leading-relaxed space-y-1 font-medium">${summaryLines.join('')}</div>`,
            icon: 'question', showCancelButton: true, confirmButtonText: 'Yes, Register', cancelButtonText: 'Cancel',
            confirmButtonColor: '#c00000', cancelButtonColor: '#64748b',
            customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6', cancelButton: 'rounded-xl font-bold px-6' }
        }).then(r => { if (r.isConfirmed) form.submit(); });
    }

    // ======= SHARED (Cross-registration panel) =======
    function toggleSubcategories(checkbox, id) {
        const subList = document.getElementById('sub_list_' + id);
        if (subList) {
            if (checkbox.checked) { subList.classList.remove('hidden'); }
            else { subList.classList.add('hidden'); }
            const children = document.querySelectorAll('.sub-checkbox-' + id);
            children.forEach(child => child.checked = checkbox.checked);
            updateSubCount(id, children.length);
        }
    }

    function toggleDropdown(button) {
        const menu = button.nextElementSibling;
        const svgs = button.querySelectorAll('svg');
        if (menu.classList.contains('hidden')) {
            document.querySelectorAll('.dropdown-menu').forEach(el => el.classList.add('hidden'));
            menu.classList.remove('hidden');
            svgs.forEach(svg => svg.classList.add('rotate-180'));
            setTimeout(() => menu.querySelector('input')?.focus(), 50);
        } else {
            menu.classList.add('hidden');
            svgs.forEach(svg => svg.classList.remove('rotate-180'));
        }
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.group\\/dropdown')) {
            document.querySelectorAll('.dropdown-menu').forEach(el => el.classList.add('hidden'));
        }
    });

    function filterDropdown(input) {
        const query = input.value.toLowerCase();
        const labels = input.parentElement.nextElementSibling.querySelectorAll('label');
        labels.forEach(label => {
            const text = label.querySelector('.searchable-text').textContent.toLowerCase();
            label.classList.toggle('hidden', !text.includes(query));
        });
    }

    function updateSubCount(id, totalCount) {
        const checkboxes = document.querySelectorAll('.sub-checkbox-' + id);
        const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
        const span = document.querySelector('.sub-count-' + id);
        if (!span || totalCount === 0) return;
        if (checkedCount === totalCount) { span.textContent = `All Selected (${totalCount})`; span.className = `sub-count-${id} text-[#c00000]`; }
        else if (checkedCount === 0) { span.textContent = `None Selected (0/${totalCount})`; span.className = `sub-count-${id} text-slate-400`; }
        else { span.textContent = `${checkedCount} / ${totalCount} Selected`; span.className = `sub-count-${id} text-[#c00000]`; }
    }
</script>
</body>
</html>