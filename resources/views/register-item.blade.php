<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Registration | DepEd Command Center</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .step-active { color: #c00000; }
        .step-active .icon-box { background-color: #c00000; color: white; border-color: #c00000; box-shadow: 0 10px 15px -3px rgba(192, 0, 0, 0.2); transform: scale(1.05); }
        .step-inactive { color: #cbd5e1; }
        .step-inactive .icon-box { background-color: white; color: #cbd5e1; border-color: #e2e8f0; }
        .step-complete .icon-box { background-color: #1e293b; color: white; border-color: #1e293b; }
        
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade { animation: fadeIn 0.4s ease-out forwards; }

        .stepper-line { flex-grow: 1; height: 3px; background-color: #e2e8f0; position: relative; top: -14px; margin: 0 20px; border-radius: 99px; }
        .stepper-line.active { background-color: #c00000; transition: all 0.5s ease; }
        
        .custom-scroll::-webkit-scrollbar { width: 5px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

        .autocomplete-dropdown { position: absolute; z-index: 50; width: 100%; background: white; border: 1px solid #e2e8f0; border-radius: 1.5rem; box-shadow: 0 20px 40px -10px rgba(0,0,0,0.1); max-height: 200px; overflow-y: auto; top: calc(100% + 6px); }
        .autocomplete-item { padding: 0.85rem 1.25rem; font-size: 0.8rem; font-weight: 600; color: #334155; cursor: pointer; transition: all 0.15s; }
        .autocomplete-item:hover { background: #fef2f2; color: #c00000; }
        .autocomplete-item .hint { font-size: 0.65rem; color: #94a3b8; font-weight: 500; }

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
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex text-slate-900 overflow-x-hidden" x-data="{
    sourceType: '',
    locationQuery: '',
    locationResults: [],
    selectedLocation: null,
    employeeQuery: '',
    employeeResults: [],
    selectedEmployee: null,
    classifications: [],
    allCategories: [],
    categories: [],
    classificationId: '',
    loading: false,
    async init() {
        const res = await fetch('/api/inventory/dropdown-data');
        const data = await res.json();
        this.classifications = data.classifications;
        this.allCategories = data.categories;
    },
    async searchLocations() {
        if (this.locationQuery.length < 2) { this.locationResults = []; return; }
        this.loading = true;
        const res = await fetch(`/api/locations/search?q=${encodeURIComponent(this.locationQuery)}&type=${this.sourceType === 'school' ? 'school' : 'office'}`);
        this.locationResults = await res.json();
        this.loading = false;
    },
    selectLocation(loc) {
        this.selectedLocation = loc;
        this.locationQuery = loc.name;
        this.locationResults = [];
    },
    async searchEmployees() {
        if (this.employeeQuery.length < 2) { this.employeeResults = []; return; }
        this.loading = true;
        const res = await fetch(`/api/employees/search?q=${encodeURIComponent(this.employeeQuery)}`);
        this.employeeResults = await res.json();
        this.loading = false;
    },
    selectEmployee(emp) {
        this.selectedEmployee = emp;
        this.employeeQuery = emp.full_name;
        this.employeeResults = [];
    }
}">

    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    title: 'Item Registered!',
                    text: @json(session('success')),
                    icon: 'success',
                    confirmButtonColor: '#c00000',
                    customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' }
                });
            });
        </script>
    @endif

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll">

    <div class="max-w-[1100px] mx-auto p-6 lg:p-12 min-h-screen flex flex-col">
        
        {{-- Header Section --}}
        <div class="flex justify-between items-center mb-16 px-2">
            <div>
                <h2 class="text-3xl font-black text-slate-800 uppercase italic leading-none">Register new Inventory Item</h2>
                <p class="text-slate-400 text-xs font-bold uppercase tracking-[0.2em] mt-2">Department of Education • Zamboanga City</p>
            </div>
            <a href="/inventory-setup?step=2&mode=add" class="back-btn-cool px-6 py-3 rounded-2xl text-sm font-bold text-slate-600 flex items-center gap-2 shadow-sm active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Back
            </a>
        </div>

        {{-- Stepper --}}
        <div class="flex items-center justify-between mb-20 px-12 relative">
            <div id="step1-indicator" class="flex flex-col items-center gap-4 step-active z-10 transition-all duration-500">
                <div class="icon-box w-16 h-16 rounded-[1.8rem] border-2 flex items-center justify-center transition-all duration-500 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <span class="text-[10px] font-black uppercase tracking-widest leading-none italic">Asset Source</span>
            </div>
            <div id="line-1" class="stepper-line"></div>
            <div id="step2-indicator" class="flex flex-col items-center gap-4 step-inactive z-10 transition-all duration-500">
                <div class="icon-box w-16 h-16 rounded-[1.8rem] border-2 flex items-center justify-center transition-all duration-500 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                </div>
                <span class="text-[10px] font-black uppercase tracking-widest leading-none italic">Specifications</span>
            </div>
            <div id="line-2" class="stepper-line"></div>
            <div id="step3-indicator" class="flex flex-col items-center gap-4 step-inactive z-10 transition-all duration-500">
                <div class="icon-box w-16 h-16 rounded-[1.8rem] border-2 flex items-center justify-center transition-all duration-500 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <span class="text-[10px] font-black uppercase tracking-widest leading-none italic">Confirmation</span>
            </div>
        </div>

        <div class="flex-grow">

            {{-- ============================== --}}
            {{-- STEP 1: SOURCE                 --}}
            {{-- ============================== --}}
            <div id="step1-content" class="animate-fade space-y-8">
                <div class="bg-white border border-slate-100 rounded-[3.5rem] p-12 shadow-sm">
                    <h3 class="text-2xl font-black text-slate-800 uppercase italic mb-10">01. Source Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10 mb-10">
                        {{-- Entity Type --}}
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-[#c00000] uppercase tracking-widest ml-1 italic underline underline-offset-4 decoration-2">Entity Type <span class="text-red-500">*</span></label>
                            <select id="sourceEntityType" x-model="sourceType" @change="handleEntityTypeChange(); selectedLocation = null; locationQuery = '';" class="w-full p-6 bg-slate-50 border border-slate-100 rounded-3xl font-black text-slate-700 outline-none focus:ring-4 focus:ring-red-50 transition-all cursor-pointer appearance-none">
                                <option value="" selected disabled>-- Select Entity Type --</option>
                                <option value="school">School (Internal)</option>
                                <option value="external">External (Supplier / Office)</option>
                            </select>
                        </div>
                        {{-- Dynamic source input (Location Search) --}}
                        <div class="space-y-3 relative">
                            <label id="sourceDynamicLabel" class="text-[10px] font-black text-slate-300 uppercase tracking-widest ml-1 italic">Provider Name</label>
                            <div class="relative">
                                <input type="text" id="sourceDynamicInput" x-model="locationQuery" @input.debounce.300ms="searchLocations()" :disabled="!sourceType" :placeholder="sourceType ? 'Search ' + sourceType + ' name...' : 'Select type first...'"
                                    class="w-full p-6 bg-slate-100 border border-slate-100 rounded-3xl font-bold text-slate-700 outline-none transition-all placeholder:text-slate-300 shadow-inner"
                                    autocomplete="off">
                                <div x-show="locationResults.length > 0" class="autocomplete-dropdown custom-scroll">
                                    <template x-for="loc in locationResults" :key="loc.entity_type + loc.id">
                                        <div @click="selectLocation(loc)" class="autocomplete-item">
                                            <span class="font-medium" x-text="loc.name"></span>
                                            <span class="ml-2 text-xs text-zinc-400" x-text="'(' + loc.entity_type + ')'"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div class="mt-3 grid grid-cols-3 gap-2" x-show="selectedLocation">
                                <div>
                                    <label class="block text-[8px] font-bold text-zinc-500 uppercase">ID</label>
                                    <input type="text" readonly :value="selectedLocation?.entity_id" class="w-full rounded-lg border border-zinc-100 bg-zinc-50 px-2 py-1 text-[10px] font-bold"/>
                                </div>
                                <div>
                                    <label class="block text-[8px] font-bold text-zinc-500 uppercase">Type</label>
                                    <input type="text" readonly :value="selectedLocation?.type" class="w-full rounded-lg border border-zinc-100 bg-zinc-50 px-2 py-1 text-[10px] font-bold"/>
                                </div>
                                <div>
                                    <label class="block text-[8px] font-bold text-zinc-500 uppercase">Location</label>
                                    <input type="text" readonly :value="selectedLocation?.location" class="w-full rounded-lg border border-zinc-100 bg-zinc-50 px-2 py-1 text-[10px] font-bold"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    </div>
                </div>
                <div class="flex justify-end pb-12">
                    <button id="step1-next" onclick="goToStep(2)" :disabled="!selectedLocation" class="group px-14 py-6 rounded-[2.5rem] font-black uppercase tracking-widest text-xs transition-all flex items-center gap-4 shadow-sm"
                        :class="selectedLocation ? 'bg-slate-900 text-white hover:bg-black cursor-pointer shadow-xl' : 'bg-slate-200 text-slate-400 cursor-not-allowed'">
                        Next Step
                        <svg class="w-5 h-5 transition-transform group-hover:translate-x-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- ============================== --}}
            {{-- STEP 2: ITEM SPECS + FORM      --}}
            {{-- ============================== --}}
            <div id="step2-content" class="hidden animate-fade space-y-8">
                <form id="registerItemForm" action="{{ route('register.item.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="acquisition_source_id" :value="selectedLocation?.id">

                  <div class="bg-white border border-slate-100 rounded-[3.5rem] p-12 shadow-sm">
    {{-- Header Section --}}
    <div class="flex justify-between items-center mb-10">
        <h3 class="text-2xl font-black text-slate-800 uppercase italic leading-none">02. Asset Details</h3>
        
        {{-- Add Button positioned at the top right of the card --}}
        <button type="button" onclick="addSubItemField()" 
            class="group bg-red-50 text-[#c00000] border border-red-100 px-6 py-3 rounded-2xl font-black text-[10px] uppercase hover:bg-[#c00000] hover:text-white transition-all flex items-center gap-2 shadow-sm active:scale-95">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4 transition-transform group-hover:rotate-90">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Add New Specification
        </button>
    </div>

                        {{-- Classification, Category & Item Name --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-10">
                            {{-- Classification --}}
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 italic">Classification <span class="text-red-500">*</span></label>
                                <select name="classification_id" x-model="classificationId" @change="categories = allCategories.filter(c => c.classification_id == classificationId)" required
                                    class="w-full p-6 bg-slate-50 border border-slate-100 rounded-3xl font-black text-sm outline-none focus:ring-4 focus:ring-red-50 transition-all cursor-pointer">
                                    <option value="">Select Classification</option>
                                    <template x-for="cls in classifications" :key="cls.id">
                                        <option :value="cls.id" x-text="cls.name"></option>
                                    </template>
                                </select>
                            </div>
                            {{-- Category --}}
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 italic">Category <span class="text-red-500">*</span></label>
                                <select name="category_id" required :disabled="!classificationId"
                                    class="w-full p-6 bg-slate-50 border border-slate-100 rounded-3xl font-black text-sm outline-none focus:ring-4 focus:ring-red-50 transition-all cursor-pointer disabled:bg-slate-100">
                                    <option value="">Select Category</option>
                                    <template x-for="cat in categories" :key="cat.id">
                                        <option :value="cat.id" x-text="cat.name"></option>
                                    </template>
                                </select>
                            </div>
                            {{-- Item Name --}}
                            <div class="space-y-2 relative">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 italic">Item Name <span class="text-red-500">*</span></label>
                                <input type="hidden" name="existing_item_id" id="existingItemId">
                                <input type="text" name="item_name" id="itemName" placeholder="e.g. Laptop, Table"
                                    class="w-full p-6 bg-slate-50 border border-slate-100 rounded-3xl font-black text-sm outline-none focus:ring-4 focus:ring-red-50 transition-all"
                                    autocomplete="off" oninput="filterItemInput()" onfocus="filterItemInput()" required>
                                <div id="itemDropdown" class="autocomplete-dropdown hidden custom-scroll"></div>
                                <p id="itemExistingHint" class="hidden text-[10px] font-semibold text-emerald-600 ml-2">✓ Adding stock to existing item</p>
                                <p id="itemNewHint" class="hidden text-[10px] font-semibold text-blue-600 ml-2">✦ Will register as new item</p>
                            </div>
                        </div>

                        {{-- Sub-item row (single spec) --}}
                        <div class="pt-10 border-t border-slate-50">
                            <div id="subItemContainer" class="space-y-8"></div>
                        </div>
                    </div>

                    <div class="flex justify-between py-6">
    
    {{-- Back Button --}}
    <button type="button" onclick="goToStep(1)" class="group px-10 py-6 bg-white border border-slate-200 text-slate-600 rounded-[2.5rem] font-black uppercase tracking-widest text-xs hover:bg-slate-50 transition-all flex items-center gap-4 italic shadow-sm active:scale-95">
        <svg class="w-5 h-5 transition-transform group-hover:-translate-x-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M11 17l-5-5m0 0l5-5m-5 5h12"></path>
        </svg>
        Source
    </button>

    {{-- Next Button --}}
    <button type="button" onclick="goToStep(3)" class="group px-14 py-6 bg-[#c00000] text-white rounded-[2.5rem] font-black uppercase tracking-widest text-xs shadow-xl shadow-red-100 hover:scale-105 hover:bg-red-700 transition-all flex items-center gap-4 italic">
        Final Review
        <svg class="w-5 h-5 transition-transform group-hover:translate-x-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
        </svg>
    </button>

</div>
                </form>
            </div>

            {{-- ============================== --}}
            {{-- STEP 3: REVIEW & SUBMIT        --}}
            {{-- ============================== --}}
            <div id="step3-content" class="hidden animate-fade space-y-10 pb-20">
                <div class="bg-slate-900 rounded-[4rem] p-16 shadow-2xl relative overflow-hidden">
                    <div class="relative z-10">
                        <h3 class="text-3xl font-black text-white uppercase italic mb-8 tracking-tight">Batch Summary Preview</h3>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
                            <div class="bg-white/5 border border-white/10 rounded-[2.5rem] p-8 shadow-inner">
                                <span class="text-[#c00000] text-[9px] font-black uppercase tracking-[0.3em] block mb-3">Asset Source</span>
                                <h4 id="sumSource" class="text-2xl font-black text-white italic underline decoration-red-500 underline-offset-8" x-text="selectedLocation?.name || '--'">--</h4>
                                <p id="sumType" class="text-slate-500 text-[10px] font-bold uppercase mt-4 tracking-widest italic" x-text="(sourceType || 'Unknown').toUpperCase() + ' SOURCE'">--</p>
                            </div>
                            <div class="bg-white/5 border border-white/10 rounded-[2.5rem] p-8 shadow-inner">
                                <span class="text-emerald-500 text-[9px] font-black uppercase tracking-[0.3em] block mb-3">Item Name</span>
                                <h4 id="sumItem" class="text-2xl font-black text-white italic underline decoration-emerald-500 underline-offset-8">--</h4>
                                <p id="sumCat" class="text-slate-500 text-[10px] font-bold uppercase mt-4 tracking-widest italic">--</p>
                            </div>
                        </div>
                        <div class="bg-white/5 border border-white/10 rounded-[2.5rem] overflow-hidden">
                            <table class="w-full text-left">
                                <thead class="border-b border-white/10 bg-white/5">
                                    <tr>
                                        <th class="p-6 text-[9px] font-black text-slate-500 uppercase italic tracking-[0.2em]">Specifications</th>
                                        <th class="p-6 text-[9px] font-black text-slate-500 uppercase italic tracking-[0.2em]">Unit Price</th>
                                        <th class="p-6 text-[9px] font-black text-slate-500 uppercase italic tracking-[0.2em]">Qty</th>
                                        <th class="p-6 text-[9px] font-black text-slate-500 uppercase italic tracking-[0.2em]">Condition</th>
                                    </tr>
                                </thead>
                                <tbody id="summaryTable" class="text-white text-xs font-bold uppercase tracking-tight"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="flex justify-between">
                    <button onclick="goToStep(2)" class="group px-10 py-6 bg-slate-200 text-slate-600 rounded-[2.5rem] font-black uppercase tracking-widest text-xs flex items-center gap-4">
                        <svg class="w-5 h-5 transition-transform group-hover:-translate-x-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M11 17l-5-5m0 0l5-5m-5 5h12"></path></svg>
                        Edit Specs
                    </button>
                    <button onclick="confirmSubmit()" class="px-20 py-6 bg-slate-900 text-white rounded-[2.5rem] font-black uppercase tracking-widest text-xs hover:bg-black transition-all shadow-2xl italic">
                        REGISTER TO MASTERLIST ⚡
                    </button>
                </div>
            </div>

        </div>
    </div>

    <script>
        // =============================================
        // DATA FROM BACKEND
        // =============================================
        const rawItems       = @json($items);
        const rawSubItems    = @json($subItems);

        // =============================================
        // STEPPER NAVIGATION
        // =============================================
        function goToStep(step) {
            ['step1-content','step2-content','step3-content'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.classList.add('hidden');
            });
            const content = document.getElementById(`step${step}-content`);
            if (content) content.classList.remove('hidden');

            document.getElementById('step1-indicator').className = 'flex flex-col items-center gap-4 z-10 transition-all duration-500 ' + (step === 1 ? 'step-active' : 'step-complete');
            document.getElementById('step2-indicator').className = 'flex flex-col items-center gap-4 z-10 transition-all duration-500 ' + (step === 2 ? 'step-active' : (step > 2 ? 'step-complete' : 'step-inactive'));
            document.getElementById('step3-indicator').className = 'flex flex-col items-center gap-4 z-10 transition-all duration-500 ' + (step === 3 ? 'step-active' : 'step-inactive');

            document.getElementById('line-1').className = 'stepper-line ' + (step >= 2 ? 'active' : '');
            document.getElementById('line-2').className = 'stepper-line ' + (step >= 3 ? 'active' : '');

            if (step === 2) {
                if (document.getElementById('subItemContainer').children.length === 0) {
                    addSubItemField();
                }
            }

            if (step === 3) buildSummary();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // =============================================
        // STEP 1 — ENTITY TYPE HANDLER
        // =============================================
        function handleEntityTypeChange() {
            const input = document.getElementById('sourceDynamicInput');
            const label = document.getElementById('sourceDynamicLabel');

            input.disabled = false;
            input.classList.remove('bg-slate-100', 'shadow-inner');
            input.classList.add('bg-white', 'border-slate-200', 'focus:ring-4', 'focus:ring-red-50');
            label.classList.add('text-[#c00000]');
            label.classList.remove('text-slate-300');

            const type = document.getElementById('sourceEntityType').value;
            label.innerText = type === 'school'
                ? 'Search School Name *'
                : 'External Provider / Office Name *';
        }

        function updateStep1NextBtn() {
            // Managed by Alpine.js :disabled
        }

        // =============================================
        // STEP 2 — ITEM NAME AUTOCOMPLETE
        // =============================================
        function filterItemInput() {
            const q = document.getElementById('itemName').value.trim().toLowerCase();
            const dd = document.getElementById('itemDropdown');
            document.getElementById('existingItemId').value = '';
            document.getElementById('itemExistingHint').classList.add('hidden');
            document.getElementById('itemNewHint').classList.add('hidden');

            const catId = document.querySelector('select[name="category_id"]').value;
            let matches = rawItems;
            if (catId) {
                matches = matches.filter(i => String(i.category_id) === String(catId));
            } else if (!q) {
                dd.innerHTML = '<div class="autocomplete-item" style="color:#94a3b8;font-style:italic;cursor:default;">Select Category first</div>';
                dd.classList.remove('hidden');
                return;
            }
            
            matches = matches.filter(i => !q || i.name.toLowerCase().includes(q)).slice(0, 15);

            const exactMatch = rawItems.find(i => i.name.toLowerCase() === q && (!catId || String(i.category_id) === String(catId)));
            if (exactMatch) {
                document.getElementById('existingItemId').value = exactMatch.id;
                document.getElementById('itemExistingHint').classList.remove('hidden');
            } else if (q) {
                document.getElementById('itemNewHint').classList.remove('hidden');
            }

            if (matches.length === 0) { dd.classList.add('hidden'); return; }

            dd.innerHTML = matches.map(i => {
                return `<div class="autocomplete-item" onclick="selectItem(${i.id}, '${i.name.replace(/'/g,"\\'")}', ${i.category_id})">
                    ${i.name}
                </div>`;
            }).join('');
            dd.classList.remove('hidden');
        }

        function selectItem(id, name, catId) {
            document.getElementById('existingItemId').value = id;
            document.getElementById('itemName').value = name;
            document.getElementById('itemDropdown').classList.add('hidden');
            document.getElementById('itemExistingHint').classList.remove('hidden');
            document.getElementById('itemNewHint').classList.add('hidden');
        }

       // =============================================
// STEP 2 — ADD SUB-ITEM ROW (single row only)
// =============================================
function addSubItemField() {
    const container = document.getElementById('subItemContainer');
    const id = Date.now();

    const html = `
        <div id="row-${id}" class="row-container p-8 bg-slate-50 border border-slate-100 rounded-[2.5rem] animate-fade relative group shadow-sm transition-all hover:border-[#c00000]/30 hover:bg-white">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-end">

                <div class="lg:col-span-5 space-y-2 relative">
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block ml-1 italic">Specifications / Materials <span class="text-red-500">*</span></label>
                    <input type="text" name="description[]" placeholder="e.g. Core i7, 4ft Steel Frame" required
                        autocomplete="off" oninput="filterSpecInput(this, ${id})" onfocus="filterSpecInput(this, ${id})"
                        class="spec-val w-full p-4 bg-white border border-slate-100 rounded-2xl font-bold text-sm outline-none focus:border-red-200 shadow-sm transition-all">
                    <div id="specDropdown-${id}" class="autocomplete-dropdown hidden custom-scroll"></div>
                    <p id="specExistingHint-${id}" class="hidden text-[9px] font-semibold text-emerald-600 ml-2 mt-1 italic">✓ Existing spec found</p>
                    <p id="specNewHint-${id}" class="hidden text-[9px] font-semibold text-blue-600 ml-2 mt-1 italic">✦ New spec entry</p>
                </div>

                <div class="lg:col-span-2 space-y-2">
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block ml-1 italic">Condition</label>
                    <select name="condition[]" class="cond-val w-full p-4 bg-white border border-slate-100 rounded-2xl font-bold text-xs outline-none shadow-sm cursor-pointer transition-all focus:border-red-200">
                        <option value="Good Condition">Good Condition</option>
                        <option value="Needs Repair">Needs Repair</option>
                        <option value="Unserviceable">Unserviceable</option>
                    </select>
                </div>

                <div class="lg:col-span-2 space-y-2">
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block text-center italic">Qty <span class="text-red-500">*</span></label>
                    <input type="number" name="quantity[]" id="qty-${id}" placeholder="0" min="1" required
                        class="qty-val w-full p-4 bg-white border border-slate-100 rounded-2xl font-bold text-sm text-center outline-none shadow-sm transition-all focus:border-red-200">
                </div>

                </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-6 pt-6 border-t border-slate-100/50">
                <div class="space-y-2">
                    <label class="text-[9px] font-black text-[#c00000] uppercase tracking-widest block ml-1 italic underline underline-offset-4">₱ Unit Price</label>
                    <div class="relative flex items-center">
                        <span class="absolute left-4 text-xs font-black text-slate-400 italic">₱</span>
                        <input type="number" name="asset_cost[]" placeholder="0.00" step="0.01" min="0"
                            class="price-val w-full pl-8 p-4 bg-white border border-red-50 rounded-2xl font-bold text-sm outline-none shadow-sm transition-all focus:ring-4 focus:ring-red-50">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-[9px] font-black text-[#c00000] uppercase tracking-widest block ml-1 italic underline underline-offset-4">📅 Date Acquired</label>
                    <input type="date" name="acquisition_date[]"
                        class="date-val w-full p-4 bg-white border border-red-50 rounded-2xl font-bold text-sm outline-none shadow-sm transition-all focus:ring-4 focus:ring-red-50 uppercase text-slate-500">
                </div>
            </div>

            </div>

            <button type="button" onclick="document.getElementById('row-${id}').remove()" 
                class="absolute -top-3 -right-3 w-10 h-10 bg-white border border-slate-100 text-slate-300 rounded-full hover:text-red-500 shadow-md flex items-center justify-center font-bold transition-all hover:scale-110 active:scale-95 italic">
                ✕
            </button>
        </div>`;
    container.insertAdjacentHTML('beforeend', html);
}

        function toggleSerial(id) {
            document.getElementById(`serial-panel-${id}`).classList.toggle('hidden');
        }

        function handleSerializedChange(checkbox, id) {
            const row = document.getElementById(`row-${id}`);
            const qtyInput = row.querySelector('.qty-val');
            const propInput = row.querySelector('.prop-val');
            
            if (checkbox.checked) {
                qtyInput.value = 1;
                qtyInput.readOnly = true;
                qtyInput.classList.add('bg-slate-100');
                
                propInput.disabled = false;
                propInput.classList.remove('bg-slate-100', 'placeholder:text-slate-300');
                propInput.classList.add('bg-white');
            } else {
                qtyInput.readOnly = false;
                qtyInput.classList.remove('bg-slate-100');
                
                propInput.disabled = true;
                propInput.value = '';
                propInput.classList.add('bg-slate-100', 'placeholder:text-slate-300');
                propInput.classList.remove('bg-white');
            }
        }

        function filterSpecInput(input, id) {
            const q = input.value.trim().toLowerCase();
            const dd = document.getElementById(`specDropdown-${id}`);
            const itemId = document.getElementById('existingItemId').value;

            if (!itemId) {
                dd.innerHTML = '<div class="autocomplete-item" style="color:#94a3b8;font-style:italic;cursor:default;">Select an item name first</div>';
                dd.classList.remove('hidden');
                return;
            }

            let specs = rawSubItems.filter(s => String(s.item_id) === String(itemId));
            
            const filtered = specs.filter(s =>
                !q || s.name.toLowerCase().includes(q)
            ).slice(0, 20);

            if (filtered.length === 0) { dd.classList.add('hidden'); return; }

            dd.innerHTML = filtered.map(s => {
                return `<div class="autocomplete-item" onclick="selectSpec(${id}, '${s.name.replace(/'/g,"\\'")}')">
                    ${s.name}
                </div>`;
            }).join('');
            dd.classList.remove('hidden');
        }

        function selectSpec(rowId, name) {
            const row = document.getElementById(`row-${rowId}`);
            row.querySelector('.spec-val').value = name;
            document.getElementById(`specDropdown-${rowId}`).classList.add('hidden');
        }

        // =============================================
        // STEP 3 — BUILD SUMMARY
        // =============================================
        function buildSummary() {
            const sumItem = document.getElementById('itemName').value || 'Unnamed Asset';
            document.getElementById('sumItem').innerText = sumItem;
            
            const catSelect = document.querySelector('select[name="category_id"]');
            const catName = catSelect.options[catSelect.selectedIndex]?.text || '--';
            document.getElementById('sumCat').innerText = catName;

            const table = document.getElementById('summaryTable');
            table.innerHTML = '';
            document.querySelectorAll('.row-container').forEach(row => {
                const spec  = row.querySelector('.spec-val').value || '—';
                const price = row.querySelector('.price-val').value;
                const qty   = row.querySelector('.qty-val').value || '0';
                const cond  = row.querySelector('.cond-val').value || '—';
                table.innerHTML += `
                    <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                        <td class="p-6 italic">${spec}</td>
                        <td class="p-6 text-emerald-400 italic">${price ? '₱ ' + parseFloat(price).toLocaleString() : '—'}</td>
                        <td class="p-6 text-slate-300">${qty}</td>
                        <td class="p-6 text-slate-300">${cond}</td>
                    </tr>`;
            });
        }

        function confirmSubmit() {
            Swal.fire({
                title: 'Register to Masterlist?',
                text: 'Are you sure you want to register these items?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1e293b',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: '⚡ Register Now',
                cancelButtonText: 'Review Again',
                customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6', cancelButton: 'rounded-xl font-bold px-6' }
            }).then(result => {
                if (result.isConfirmed) {
                    document.getElementById('registerItemForm').submit();
                }
            });
        }

        document.addEventListener('click', e => {
            ['itemDropdown', 'sourceDropdown', 'personnelDropdown'].forEach(id => {
                const dd = document.getElementById(id);
                if (dd && !dd.contains(e.target) && !e.target.closest('input')) {
                    dd.classList.add('hidden');
                }
            });
            document.querySelectorAll('[id^="specDropdown-"]').forEach(dd => {
                if (dd && !dd.contains(e.target) && !e.target.closest('input')) {
                    dd.classList.add('hidden');
                }
            });
        });
    </script>

    </div> {{-- end max-w content --}}
    </div> {{-- end flex-grow scroll wrapper --}}
</body>
</html>