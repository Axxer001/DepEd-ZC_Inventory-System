<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Setup | DepEd Zamboanga City</title>
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

        <main class="p-6 lg:p-10 max-w-5xl mx-auto w-full">
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
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                        </div>
                        <h4 class="text-3xl font-black text-slate-800 tracking-tight uppercase">Add New</h4>
                        <p class="text-slate-400 text-xs font-bold uppercase mt-3 tracking-widest leading-tight">Register new data to the system</p>
                    </div>

                    <div onclick="nextStep(2, 'edit')" class="group bg-white p-12 rounded-[3rem] shadow-xl shadow-slate-200/60 border-2 border-transparent hover:border-[#c00000] transition-all duration-300 cursor-pointer text-center">
                        <div class="w-20 h-20 bg-slate-50 text-slate-600 rounded-3xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-10 h-10">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                        </div>
                        <h4 class="text-3xl font-black text-slate-800 tracking-tight uppercase">Edit / Update</h4>
                        <p class="text-slate-400 text-xs font-bold uppercase mt-3 tracking-widest leading-tight">Modify or update existing records</p>
                    </div>
                </div>
            </div>

          {{-- Step 2: Category Selection --}}
<div id="step2" class="step-content text-center">
    <h3 id="step2Title" class="text-lg font-bold text-slate-400 uppercase tracking-[0.3em] mb-10">Select Category</h3>
    
    <div class="flex flex-wrap lg:flex-nowrap justify-center gap-6 max-w-5xl mx-auto px-4">
        
        <div onclick="nextStep(3, 'school')" class="bg-white p-8 w-full sm:w-64 rounded-[2.5rem] shadow-lg border border-slate-100 hover:border-[#c00000] hover:-translate-y-2 transition-all cursor-pointer group">
            <div class="w-12 h-12 bg-red-50 text-[#c00000] rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:rotate-12 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A4.833 4.833 0 0012 9a4.833 4.833 0 00-7.5 1.332V21m15 0h-15" />
                </svg>
            </div>
            <span class="block font-extrabold text-slate-800 uppercase text-[10px] tracking-widest">Schools</span>
        </div>             

        <div onclick="nextStep(3, 'item')" class="bg-white p-8 w-full sm:w-64 rounded-[2.5rem] shadow-lg border border-slate-100 hover:border-[#c00000] hover:-translate-y-2 transition-all cursor-pointer group">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:rotate-12 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                </svg>
            </div>
            <span class="block font-extrabold text-slate-800 uppercase text-[10px] tracking-widest">Inventory Items</span>
        </div>

        <div onclick="nextStep(3, 'distribution')" class="bg-white p-8 w-full sm:w-64 rounded-[2.5rem] shadow-lg border border-slate-100 hover:border-[#c00000] hover:-translate-y-2 transition-all cursor-pointer group">
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:rotate-12 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-10.5v.008H15V4.5zm0 9v.008H15V13.5zm0-4.5v.008H15V9zm0-4.5v.008H15V4.5zM9 15l-3 1.5L3 15V5.25l3-1.5L9 5.25M9 15l3.047-1.524c.499-.25 1.096-.217 1.565.083L17.25 15l3-1.5V4.5l-3 1.5-3.638-2.046c-.469-.264-1.025-.264-1.494 0L9 5.25" />
                </svg>
            </div>
            <span class="block font-extrabold text-slate-800 uppercase text-[10px] tracking-widest">Asset Distribution</span>
        </div>

    
    </div>
</div>

            {{-- Step 3: Form Content --}}
            <div id="step3" class="step-content">
                @if($errors->any())
                    <div class="max-w-2xl mx-auto mb-6 bg-red-50 text-red-600 p-6 font-bold rounded-3xl shadow-sm border border-red-100 flex items-start gap-4">
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

                <div class="max-w-2xl mx-auto bg-white p-10 rounded-[3rem] shadow-2xl border border-slate-50 relative overflow-hidden">
                    <div id="formContent"></div>
                </div>
            </div>

        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let history = [1];
        let currentMode = '';
        let currentModule = '';

        const rawCategories = {{ Js::from($categories) }};
        const rawItems = {{ Js::from($items) }};
        const rawSubItems = {{ Js::from($subItems) }};
        
        const rawDistricts = @json($districts);
        const rawLds = @json($legislativeDistricts);
        const rawQuadrants = @json($quadrants);
        const allSchoolsList = @json($allSchools);
        const districtMap = {};
        rawDistricts.forEach(d => {
            districtMap[d.name] = { ld: d.legislative_district_id, quad: d.quadrant_name.replace('Quadrant ', '') };
        });

        let selectedSchoolsArray = [];
        let selectedSubItemsArray = [];

        function nextStep(step, value) {
            if (step === 2) {
                currentMode = value;
                document.getElementById('step2Title').innerText = (value === 'add' ? 'ADD NEW' : 'EDIT') + ' RECORD';
            }
            if (step === 3) {
                currentModule = value;
                renderForm();
            }
            document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
            document.getElementById('step' + step).classList.add('active');
            history.push(step);
            updateBackButton();
        }

        function goBack() {
            if (history.length > 1) {
                history.pop();
                const prevStep = history[history.length - 1];
                document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
                document.getElementById('step' + prevStep).classList.add('active');
                updateBackButton();
            }
        }

        function updateBackButton() {
            const btn = document.getElementById('backBtn');
            btn.classList.toggle('hidden', history[history.length - 1] === 1);
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

        function renderForm() {
            const container = document.getElementById('formContent');
            const modeText = currentMode === 'add' ? 'Register' : 'Update';
            const btnColor = 'bg-[#c00000] hover:bg-red-700 shadow-red-100';
            let html = `<h4 class="text-2xl font-black text-slate-800 mb-8 uppercase tracking-tight italic">${modeText} ${currentModule}</h4>`;

            if (currentModule === 'school') {
                html += `<form id="schoolForm" action="{{ route('inventory.setup.school') }}" method="POST" class="space-y-6">
                            @csrf
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Select District <span class="text-red-500">*</span></label>
                                <select name="district_id" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold focus:ring-2 focus:ring-red-100 transition-all cursor-pointer" required>
                                    <option value="">Select District</option>
                                    ${rawDistricts.map(d => `<option value="${d.id}">${d.name}</option>`).join('')}
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">School ID (6-Digits) <span class="text-red-500">*</span></label>
                                <input type="text" name="school_id" placeholder="e.g. 123456" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold transition-all" required maxlength="6" pattern="[0-9]{6}">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">School Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" placeholder="e.g. Ayala National High School" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold transition-all" required>
                            </div>
                            <button type="button" onclick="confirmSchoolSubmit()" class="w-full py-5 ${btnColor} text-white rounded-3xl font-bold shadow-xl transition-all hover:-translate-y-1 active:scale-95">${modeText} School</button>
                        </form>`;
            } else if (currentModule === 'district') {
                html += `<div class="space-y-6">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Legislative District</label>
                                    <select id="dist_ld" onchange="filterQuadrants()" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold focus:ring-2 focus:ring-blue-100 transition-all">
                                        <option value="">Select LD</option>
                                        ${rawLds.map(ld => `<option value="${ld.id}">${ld.name}</option>`).join('')}
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Quadrant</label>
                                    <select id="dist_quad" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold focus:ring-2 focus:ring-blue-100 transition-all">
                                        <option value="">Select Quadrant</option>
                                    </select>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">District Name</label>
                                <input type="text" placeholder="e.g. District 1" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold transition-all">
                            </div>
                            <button class="w-full py-5 ${btnColor} text-white rounded-3xl font-bold shadow-xl active:scale-95">${modeText} District</button>
                        </div>`;
            } else if (currentModule === 'category') {
                html += `<form id="categoryForm" action="{{ route('inventory.setup.category') }}" method="POST" class="space-y-6">
                            @csrf
                            <input type="hidden" name="existing_category_id" id="existingCategoryId" value="">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Main Category Name <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="flex">
                                        <input type="text" name="name" id="categoryName" placeholder="e.g. Electronics" class="flex-grow p-4 bg-slate-50 border border-slate-100 rounded-l-2xl outline-none font-semibold transition-all" required oninput="checkCategoryDuplicate()">
                                        <button type="button" onclick="toggleCategoryDropdown()" id="categoryDropdownBtn" class="px-4 bg-slate-50 border border-l-0 border-slate-100 rounded-r-2xl text-slate-400 hover:text-[#c00000] hover:bg-red-50 transition-all" title="Select existing category">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                        </button>
                                    </div>
                                    <div id="categoryDropdownList" class="hidden absolute z-10 w-full mt-1 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-[200px] overflow-y-auto custom-scroll">
                                        <div class="p-3 text-xs text-slate-400 font-bold uppercase tracking-widest">Select existing category</div>
                                    </div>
                                </div>
                                <p id="categoryExistingHint" class="hidden text-xs font-semibold text-emerald-600 ml-1">✓ Using existing category — no duplicate will be created.</p>
                                <p id="categoryNewHint" class="hidden text-xs font-semibold text-blue-600 ml-1">✦ Creating new category.</p>
                                <p id="categoryDuplicateWarning" class="hidden text-xs font-semibold text-red-600 ml-1">⚠ This category already exists in the system. Please use the dropdown to select it instead.</p>
                            </div>
                            <button type="button" onclick="confirmCategorySubmit()" class="w-full py-5 ${btnColor} text-white rounded-3xl font-bold shadow-xl transition-all hover:-translate-y-1 active:scale-95">${modeText} Category</button>
                        </form>`;
            } else if (currentModule === 'item') {
                // ===== MODULE 1: MASTER REGISTRY (Inventory Items) =====
                html += `<form id="itemForm" action="{{ route('inventory.setup.item') }}" method="POST" class="space-y-6">
                            @csrf
                            <input type="hidden" name="existing_item_id" id="existingItemId" value="">

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Main Category <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="flex">
                                        <input type="hidden" name="category_id" id="itemCategoryId" value="">
                                        <input type="text" name="category_name" id="itemCategoryName" placeholder="e.g. Electronics" class="flex-grow p-4 bg-slate-50 border border-slate-100 rounded-l-2xl outline-none font-semibold transition-all" required autocomplete="off" oninput="filterItemCategory()">
                                        <button type="button" onclick="toggleItemCategoryDropdown()" id="itemCategoryDropdownBtn" class="px-4 bg-slate-50 border border-l-0 border-slate-100 rounded-r-2xl text-slate-400 hover:text-[#c00000] hover:bg-red-50 transition-all" title="Select existing category">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                        </button>
                                    </div>
                                    <div id="itemCategoryDropdownList" class="hidden absolute z-20 w-full mt-1 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-[200px] overflow-y-auto custom-scroll"></div>
                                </div>
                                <p id="itemCategoryExistingHint" class="hidden text-xs font-semibold text-emerald-600 ml-1">✓ Using existing category.</p>
                                <p id="itemCategoryNewHint" class="hidden text-xs font-semibold text-blue-600 ml-1">✦ Creating new category.</p>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Item Name <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="flex">
                                        <input type="text" name="item_name" id="itemName" placeholder="e.g. Smart TV" class="flex-grow p-4 bg-slate-50 border border-slate-100 rounded-l-2xl outline-none font-semibold transition-all" required oninput="checkItemDuplicate()">
                                        <button type="button" onclick="toggleItemDropdown()" id="itemDropdownBtn" class="px-4 bg-slate-50 border border-l-0 border-slate-100 rounded-r-2xl text-slate-400 hover:text-[#c00000] hover:bg-red-50 transition-all" title="Select existing item">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                        </button>
                                    </div>
                                    <div id="itemDropdownList" class="hidden absolute z-10 w-full mt-1 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-[200px] overflow-y-auto custom-scroll">
                                        <div class="p-3 text-xs text-slate-400 font-bold uppercase tracking-widest">Select existing item</div>
                                    </div>
                                </div>
                                <p id="itemExistingHint" class="hidden text-xs font-semibold text-emerald-600 ml-1">✓ Using existing item — no duplicate will be created.</p>
                                <p id="itemNewHint" class="hidden text-xs font-semibold text-blue-600 ml-1">✦ Creating new item.</p>
                                <p id="itemDuplicateWarning" class="hidden text-xs font-semibold text-red-600 ml-1">⚠ This item already exists in the system. Please use the dropdown to select it instead.</p>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Master Quantity <span class="text-red-500">*</span> <span class="text-xs text-slate-300 normal-case font-medium">(Total units in the system)</span></label>
                                <input type="number" name="master_quantity" id="masterQuantity" min="1" step="1" placeholder="e.g. 50" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold transition-all" required>
                            </div>

                            <div class="space-y-4 pt-4 border-t border-slate-100">
                                <div class="space-y-3">
                                    <div class="flex justify-between items-center ml-1">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Specifications / Sub-Items <span class="text-xs text-slate-300 normal-case font-medium">(Optional)</span></label>
                                        <button type="button" onclick="addSubItemField()" class="text-[10px] font-bold bg-red-50 text-[#c00000] px-3 py-1 rounded-lg hover:bg-[#c00000] hover:text-white transition-all">+ Add Spec</button>
                                    </div>
                                    <div id="subItemContainer" class="space-y-3 max-h-[200px] overflow-y-auto pr-2 custom-scroll">
                                        <div class="flex gap-2 group">
                                            <input type="text" name="sub_items[]" placeholder="e.g. RAM 8GB" class="flex-grow p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-sm">
                                            <button type="button" onclick="this.parentElement.remove()" class="px-4 text-slate-300 hover:text-red-500 font-bold">✕</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" onclick="confirmMasterItemSubmit()" class="w-full py-5 ${btnColor} text-white rounded-3xl font-bold shadow-xl transition-all hover:-translate-y-1 active:scale-95">${modeText} Item</button>
                        </form>`;

            } else if (currentModule === 'distribution') {
                // ===== MODULE 2: ASSET DISTRIBUTION =====
                html += `<form id="distributionForm" action="{{ route('inventory.setup.distribution') }}" method="POST" class="space-y-6">
                            @csrf
                            <input type="hidden" name="dist_item_id" id="distItemId" value="">
                            <div id="hiddenDistInputsContainer"></div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Main Category <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="flex">
                                        <input type="hidden" name="dist_category_id" id="distCategoryId" value="">
                                        <input type="text" id="distCategoryName" placeholder="e.g. Electronics" class="flex-grow p-4 bg-slate-50 border border-slate-100 rounded-l-2xl outline-none font-semibold transition-all" required autocomplete="off" oninput="filterDistCategory()">
                                        <button type="button" onclick="toggleDistCategoryDropdown()" id="distCategoryDropdownBtn" class="px-4 bg-slate-50 border border-l-0 border-slate-100 rounded-r-2xl text-slate-400 hover:text-[#c00000] hover:bg-red-50 transition-all">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                        </button>
                                    </div>
                                    <div id="distCategoryDropdownList" class="hidden absolute z-20 w-full mt-1 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-[200px] overflow-y-auto custom-scroll"></div>
                                </div>
                                <p id="distCategoryMissing" class="hidden text-xs font-semibold text-red-600 ml-1">⚠ This category does not exist in the Master Registry.</p>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Item Name <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="flex">
                                        <input type="text" id="distItemName" placeholder="e.g. Smart TV" class="flex-grow p-4 bg-slate-50 border border-slate-100 rounded-l-2xl outline-none font-semibold transition-all" required autocomplete="off" oninput="filterDistItem()">
                                        <button type="button" onclick="toggleDistItemDropdown()" id="distItemDropdownBtn" class="px-4 bg-slate-50 border border-l-0 border-slate-100 rounded-r-2xl text-slate-400 hover:text-[#c00000] hover:bg-red-50 transition-all">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                        </button>
                                    </div>
                                    <div id="distItemDropdownList" class="hidden absolute z-10 w-full mt-1 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-[200px] overflow-y-auto custom-scroll"></div>
                                </div>
                                <p id="distItemMissing" class="hidden text-xs font-semibold text-red-600 ml-1">⚠ This item does not exist in the Master Registry. Please register it first under Inventory Items.</p>
                                <div id="distMasterStockLabel" class="hidden ml-1 mt-1">
                                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Remaining Stock:</span>
                                    <span id="distMasterStockValue" class="text-sm font-extrabold text-emerald-600 ml-1">0</span>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Select School(s) <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="flex">
                                        <input type="text" id="distSchoolSearch" placeholder="Type to search school..." class="flex-grow p-4 bg-slate-50 border border-slate-100 rounded-l-2xl outline-none font-semibold transition-all" autocomplete="off" oninput="filterDistSchools()">
                                        <button type="button" onclick="toggleDistSchoolDropdown()" id="distSchoolDropdownBtn" class="px-4 bg-slate-50 border border-l-0 border-slate-100 rounded-r-2xl text-slate-400 hover:text-[#c00000] hover:bg-red-50 transition-all">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                        </button>
                                    </div>
                                    <div id="distSchoolDropdownList" class="hidden absolute z-20 w-full mt-1 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-[200px] overflow-y-auto custom-scroll"></div>
                                </div>
                                <div id="distSelectedSchoolsContainer" class="flex flex-wrap gap-2 mt-2 ml-1"></div>
                            </div>

                            <div class="space-y-4 pt-4 border-t border-slate-100">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Select Sub-Item(s) to Distribute <span class="text-xs text-slate-300 normal-case font-medium">(Max 5)</span></label>
                                    <div class="relative">
                                        <div class="flex">
                                            <input type="text" id="distSubItemSearch" placeholder="Type to search sub-items..." class="flex-grow p-4 bg-slate-50 border border-slate-100 rounded-l-2xl outline-none font-semibold transition-all" autocomplete="off" oninput="filterDistSubItems()">
                                            <button type="button" onclick="toggleDistSubItemDropdown()" id="distSubItemDropdownBtn" class="px-4 bg-slate-50 border border-l-0 border-slate-100 rounded-r-2xl text-slate-400 hover:text-[#c00000] hover:bg-red-50 transition-all">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                            </button>
                                        </div>
                                        <div id="distSubItemDropdownList" class="hidden absolute z-20 w-full mt-1 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-[200px] overflow-y-auto custom-scroll"></div>
                                    </div>
                                    <p id="distSubItemMissing" class="hidden text-xs font-semibold text-red-600 ml-1">⚠ This sub-item does not exist in the Master Registry.</p>
                                    <div id="distSubItemQtyContainer" class="space-y-3 mt-3"></div>
                                    <p id="distQtyWarning" class="hidden text-xs font-semibold text-red-600 ml-1">⚠ Total sub-item quantities exceed the Master Stock!</p>
                                </div>
                            </div>
                            <button type="button" onclick="confirmDistributionSubmit()" class="w-full py-5 ${btnColor} text-white rounded-3xl font-bold shadow-xl transition-all hover:-translate-y-1 active:scale-95">Distribute Assets</button>
                        </form>`;
            }
            container.innerHTML = html;
            if (currentModule === 'item') {
                // Master Registry init — no schools/sub-item selectors needed
            }
            if (currentModule === 'distribution') {
                distSelectedSchools = [];
                distSelectedSubItems = [];
                renderDistSelectedSchools();
                renderDistSubItemQtyBoxes();
            }
        }

        function addSubItemField() {
            const container = document.getElementById('subItemContainer');
            const div = document.createElement('div');
            div.className = "flex gap-2 group animate-in fade-in slide-in-from-top-2 duration-300";
            div.innerHTML = `
                <input type="text" name="sub_items[]" placeholder="Enter specification" class="flex-grow p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-sm">
                <button type="button" onclick="this.parentElement.remove()" class="px-4 text-slate-300 hover:text-red-500 font-bold transition-colors">✕</button>
            `;
            container.appendChild(div);
            container.scrollTop = container.scrollHeight;
        }

        function confirmSchoolSubmit() {
            const form = document.getElementById('schoolForm');
            if (form.checkValidity()) {
                Swal.fire({
                    title: "Add New School",
                    text: "Review all details before saving. Continue?",
                    icon: "info",
                    showCancelButton: true,
                    confirmButtonColor: "#c00000",
                    cancelButtonColor: "#94a3b8",
                    confirmButtonText: "Confirm Registration",
                    customClass: {
                        popup: "rounded-[2.5rem]",
                        confirmButton: "rounded-xl font-bold",
                        cancelButton: "rounded-xl font-bold"
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            } else {
                form.reportValidity();
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

        function toggleCategoryDropdown() {
            const dropdown = document.getElementById('categoryDropdownList');
            rebuildCategoryDropdown();
            dropdown.classList.toggle('hidden');
        }

        function selectExistingCategory(id, name) {
            document.getElementById('existingCategoryId').value = id;
            document.getElementById('categoryName').value = name;
            document.getElementById('categoryName').readOnly = true;
            document.getElementById('categoryName').classList.add('bg-emerald-50', 'border-emerald-200');
            document.getElementById('categoryName').classList.remove('bg-blue-50', 'border-blue-400');
            document.getElementById('categoryExistingHint').classList.remove('hidden');
            const newHint = document.getElementById('categoryNewHint');
            if(newHint) newHint.classList.add('hidden');
            document.getElementById('categoryDropdownList').classList.add('hidden');
            checkCategoryDuplicate();
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

        function checkCategoryDuplicate() {
            const input = document.getElementById('categoryName');
            const warning = document.getElementById('categoryDuplicateWarning');
            const newHint = document.getElementById('categoryNewHint');
            const existingId = document.getElementById('existingCategoryId').value;
            const name = input.value.trim().toLowerCase();

            if (existingId) {
                warning.classList.add('hidden');
                if(newHint) newHint.classList.add('hidden');
                categoryDuplicateBlocked = false;
                input.classList.remove('border-red-400', 'bg-red-50', 'border-blue-400', 'bg-blue-50');
                return;
            }

            if (name && rawCategories.some(c => c.name.toLowerCase() === name)) {
                warning.classList.remove('hidden');
                if(newHint) newHint.classList.add('hidden');
                categoryDuplicateBlocked = true;
                input.classList.add('border-red-400', 'bg-red-50');
                input.classList.remove('border-blue-400', 'bg-blue-50');
            } else if (name) {
                warning.classList.add('hidden');
                if(newHint) newHint.classList.remove('hidden');
                categoryDuplicateBlocked = false;
                input.classList.remove('border-red-400', 'bg-red-50');
                input.classList.add('border-blue-400', 'bg-blue-50');
            } else {
                warning.classList.add('hidden');
                if(newHint) newHint.classList.add('hidden');
                categoryDuplicateBlocked = false;
                input.classList.remove('border-red-400', 'bg-red-50', 'border-blue-400', 'bg-blue-50');
            }
        }

        function confirmCategorySubmit() {
            const form = document.getElementById('categoryForm');
            if (form.checkValidity()) {
                const categoryName = document.getElementById('categoryName').value.trim();
                const existingId = document.getElementById('existingCategoryId').value;

                if (categoryDuplicateBlocked && !existingId) {
                    Swal.fire({ title: 'Duplicate Category', text: `"${categoryName}" already exists in the system. Use the dropdown (▼) to select the existing category.`, icon: 'error', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                    return;
                }

                const msg = existingId 
                    ? `Use existing category "${categoryName}"?` 
                    : `Are you sure you want to add "${categoryName}" as a new category?`;

                Swal.fire({
                    title: existingId ? "Confirm Category Selection" : "Add New Category",
                    text: msg,
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonColor: "#c00000",
                    cancelButtonColor: "#94a3b8",
                    confirmButtonText: existingId ? "Yes, use it!" : "Yes, add it!",
                    customClass: {
                        popup: "rounded-[2rem]",
                        confirmButton: "rounded-xl font-bold px-6",
                        cancelButton: "rounded-xl font-bold px-6"
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            } else {
                form.reportValidity();
            }
        }

        function rebuildItemCategoryDropdown() {
            const dropdown = document.getElementById('itemCategoryDropdownList');
            const query = document.getElementById('itemCategoryName').value.toLowerCase();
            
            const filtered = rawCategories.filter(c => c.name.toLowerCase().includes(query));

            let html = '<div class="p-3 text-xs text-slate-400 font-bold uppercase tracking-widest sticky top-0 bg-white border-b border-slate-50 w-full mb-1">Select existing category</div>';
            
            if (filtered.length === 0) {
                html += '<div class="px-4 py-3 text-sm text-slate-400 italic">No matching categories</div>';
            } else {
                filtered.forEach(c => {
                    html += `<div onclick="selectItemCategory(${c.id}, '${c.name.replace(/'/g, "\\'")}')"
                                 class="px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 cursor-pointer transition-colors border-b border-slate-50 last:border-0 truncate">${c.name}</div>`;
                });
            }
            html += `<div onclick="clearItemCategorySelection()" class="px-4 py-3 text-xs font-bold text-slate-400 hover:bg-slate-50 cursor-pointer border-t border-slate-100 transition-colors">✕ Clear selection (type new category)</div>`;
            dropdown.innerHTML = html;
        }

        function filterItemCategory() {
            rebuildItemCategoryDropdown();
            document.getElementById('itemCategoryDropdownList').classList.remove('hidden');
            
            const input = document.getElementById('itemCategoryName');
            const existingId = document.getElementById('itemCategoryId').value;
            const name = input.value.trim().toLowerCase();
            
            if (existingId) {
                 document.getElementById('itemCategoryId').value = '';
                 input.classList.remove('bg-emerald-50', 'border-emerald-200');
            }

            const exactMatch = rawCategories.find(c => c.name.toLowerCase() === name);
            if (name && exactMatch) {
               document.getElementById('itemCategoryExistingHint').classList.remove('hidden');
               document.getElementById('itemCategoryNewHint').classList.add('hidden');
               input.classList.remove('border-blue-400', 'bg-blue-50');
            } else if (name && !exactMatch){
               document.getElementById('itemCategoryExistingHint').classList.add('hidden');
               document.getElementById('itemCategoryNewHint').classList.remove('hidden');
               input.classList.add('border-blue-400', 'bg-blue-50');
            } else {
               document.getElementById('itemCategoryExistingHint').classList.add('hidden');
               document.getElementById('itemCategoryNewHint').classList.add('hidden');
               input.classList.remove('border-blue-400', 'bg-blue-50');
            }
            
            onCategoryChange(); 
        }

        function toggleItemCategoryDropdown() {
            const dropdown = document.getElementById('itemCategoryDropdownList');
            rebuildItemCategoryDropdown();
            dropdown.classList.toggle('hidden');
        }

        function selectItemCategory(id, name) {
            document.getElementById('itemCategoryId').value = id;
            document.getElementById('itemCategoryName').value = name;
            
            const input = document.getElementById('itemCategoryName');
            input.classList.add('bg-emerald-50', 'border-emerald-200');
            input.classList.remove('bg-blue-50', 'border-blue-400');
            
            document.getElementById('itemCategoryExistingHint').classList.remove('hidden');
            document.getElementById('itemCategoryNewHint').classList.add('hidden');
            document.getElementById('itemCategoryDropdownList').classList.add('hidden');
            
            onCategoryChange();
        }

        function clearItemCategorySelection() {
            document.getElementById('itemCategoryId').value = '';
            document.getElementById('itemCategoryName').value = '';
            
            const input = document.getElementById('itemCategoryName');
            input.classList.remove('bg-emerald-50', 'border-emerald-200', 'bg-blue-50', 'border-blue-400');
            
            document.getElementById('itemCategoryExistingHint').classList.add('hidden');
            document.getElementById('itemCategoryNewHint').classList.add('hidden');
            document.getElementById('itemCategoryDropdownList').classList.add('hidden');
            document.getElementById('itemCategoryName').focus();
            
            onCategoryChange();
        }

        function onCategoryChange() {
            const catId = document.getElementById('itemCategoryId').value;
            const dropdown = document.getElementById('itemDropdownList');
            // Reset item selection
            document.getElementById('existingItemId').value = '';
            document.getElementById('itemName').value = '';
            document.getElementById('itemName').readOnly = false;
            document.getElementById('itemName').classList.remove('bg-emerald-50', 'border-emerald-200', 'bg-blue-50', 'border-blue-400');
            document.getElementById('itemExistingHint').classList.add('hidden');
            const newHint = document.getElementById('itemNewHint');
            if(newHint) newHint.classList.add('hidden');
            const warning = document.getElementById('itemDuplicateWarning');
            if(warning) warning.classList.add('hidden');
            // Rebuild dropdown items filtered by category
            rebuildItemDropdown(catId);
        }

        function rebuildItemDropdown(catId) {
            const dropdown = document.getElementById('itemDropdownList');
            const filtered = catId ? rawItems.filter(i => i.category_id == catId) : [];
            let html = '<div class="p-3 text-xs text-slate-400 font-bold uppercase tracking-widest">Select existing item</div>';
            if (filtered.length === 0) {
                html += '<div class="px-4 py-3 text-sm text-slate-400 italic">No existing items in this category</div>';
            } else {
                filtered.forEach(i => {
                    html += `<div onclick="selectExistingItem(${i.id}, '${i.name.replace(/'/g, "\\'")}')"
                                 class="px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-red-50 hover:text-[#c00000] cursor-pointer transition-colors">${i.name}</div>`;
                });
            }
            html += `<div onclick="clearItemSelection()" class="px-4 py-3 text-xs font-bold text-slate-400 hover:bg-slate-50 cursor-pointer border-t border-slate-100 transition-colors">✕ Clear selection (type new item)</div>`;
            dropdown.innerHTML = html;
        }

        function toggleItemDropdown() {
            const catId = document.getElementById('itemCategoryId').value;
            const catName = document.getElementById('itemCategoryName').value.trim();
            if (!catId && !catName) {
                Swal.fire({ title: 'Category Required', text: 'Please choose or type a category before selecting an item.', icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                return;
            }
            const dropdown = document.getElementById('itemDropdownList');
            rebuildItemDropdown(catId);
            dropdown.classList.toggle('hidden');
        }

        function selectExistingItem(id, name) {
            document.getElementById('existingItemId').value = id;
            document.getElementById('itemName').value = name;
            document.getElementById('itemName').readOnly = true;
            document.getElementById('itemName').classList.add('bg-emerald-50', 'border-emerald-200');
            document.getElementById('itemName').classList.remove('bg-blue-50', 'border-blue-400');
            document.getElementById('itemExistingHint').classList.remove('hidden');
            const newHint = document.getElementById('itemNewHint');
            if(newHint) newHint.classList.add('hidden');
            document.getElementById('itemDropdownList').classList.add('hidden');
        }

        function clearItemSelection() {
            document.getElementById('existingItemId').value = '';
            document.getElementById('itemName').value = '';
            document.getElementById('itemName').readOnly = false;
            document.getElementById('itemName').classList.remove('bg-emerald-50', 'border-emerald-200', 'bg-blue-50', 'border-blue-400');
            document.getElementById('itemExistingHint').classList.add('hidden');
            const newHint = document.getElementById('itemNewHint');
            if(newHint) newHint.classList.add('hidden');
            const warning = document.getElementById('itemDuplicateWarning');
            if(warning) warning.classList.add('hidden');
            document.getElementById('itemDropdownList').classList.add('hidden');
            document.getElementById('itemName').focus();
        }

        let itemDuplicateBlocked = false;

        function checkItemDuplicate() {
            const input = document.getElementById('itemName');
            const warning = document.getElementById('itemDuplicateWarning');
            const newHint = document.getElementById('itemNewHint');
            const existingId = document.getElementById('existingItemId').value;
            const name = input.value.trim().toLowerCase();

            // Skip check if user selected an existing item from dropdown
            if (existingId) {
                warning.classList.add('hidden');
                if(newHint) newHint.classList.add('hidden');
                itemDuplicateBlocked = false;
                input.classList.remove('border-red-400', 'bg-red-50', 'border-blue-400', 'bg-blue-50');
                return;
            }

            if (name && rawItems.some(i => i.name.toLowerCase() === name)) {
                warning.classList.remove('hidden');
                if(newHint) newHint.classList.add('hidden');
                itemDuplicateBlocked = true;
                input.classList.add('border-red-400', 'bg-red-50');
                input.classList.remove('border-blue-400', 'bg-blue-50');
            } else if (name) {
                warning.classList.add('hidden');
                if(newHint) newHint.classList.remove('hidden');
                itemDuplicateBlocked = false;
                input.classList.remove('border-red-400', 'bg-red-50');
                input.classList.add('border-blue-400', 'bg-blue-50');
            } else {
                warning.classList.add('hidden');
                if(newHint) newHint.classList.add('hidden');
                itemDuplicateBlocked = false;
                input.classList.remove('border-red-400', 'bg-red-50', 'border-blue-400', 'bg-blue-50');
            }
        }

        function confirmMasterItemSubmit() {
            const form = document.getElementById('itemForm');
            const itemName = document.getElementById('itemName').value.trim();
            const categoryId = document.getElementById('itemCategoryId').value;
            const categoryName = document.getElementById('itemCategoryName').value.trim();
            const existingId = document.getElementById('existingItemId').value;
            const masterQty = document.getElementById('masterQuantity').value;

            if (!categoryId && !categoryName) {
                Swal.fire({ title: 'Category Required', text: 'Please select or type a main category.', icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                return;
            }
            if (!itemName) {
                Swal.fire({ title: 'Item Name Required', text: 'Please enter an item name or select an existing one.', icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                return;
            }
            if (itemDuplicateBlocked && !existingId) {
                Swal.fire({ title: 'Duplicate Item', text: `"${itemName}" already exists. Use the dropdown to select it.`, icon: 'error', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                return;
            }
            if (!masterQty || parseInt(masterQty) < 1) {
                Swal.fire({ title: 'Master Quantity Required', text: 'Please enter the total number of units available in the system.', icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                return;
            }

            const subInputs = document.querySelectorAll('#subItemContainer input[name="sub_items[]"]');
            const subNames = Array.from(subInputs).map(i => i.value.trim()).filter(v => v);

            let msg = existingId
                ? `Update existing item "${itemName}" with master quantity of ${masterQty}`
                : `Register new item "${itemName}" with master quantity of ${masterQty}`;
            if (subNames.length > 0) msg += ` and ${subNames.length} specification(s)`;
            msg += '?';

            Swal.fire({
                title: 'Confirm Registration', text: msg, icon: 'question',
                showCancelButton: true, confirmButtonColor: '#c00000', cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, register it!',
                customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6', cancelButton: 'rounded-xl font-bold px-6' }
            }).then((result) => { if (result.isConfirmed) form.submit(); });
        }

        // =============================================
        // ASSET DISTRIBUTION MODULE
        // =============================================
        let distSelectedSchools = [];
        let distSelectedSubItems = [];
        let currentDistMasterStock = 0;

        // --- Distribution Category ---
        function toggleDistCategoryDropdown() { rebuildDistCategoryDropdown(); document.getElementById('distCategoryDropdownList').classList.toggle('hidden'); }
        function filterDistCategory() {
            rebuildDistCategoryDropdown();
            document.getElementById('distCategoryDropdownList').classList.remove('hidden');
            const name = document.getElementById('distCategoryName').value.trim().toLowerCase();
            const el = document.getElementById('distCategoryMissing');
            el.classList.toggle('hidden', !name || rawCategories.some(c => c.name.toLowerCase() === name));
        }
        function rebuildDistCategoryDropdown() {
            const dd = document.getElementById('distCategoryDropdownList');
            const q = document.getElementById('distCategoryName').value.toLowerCase();
            const f = rawCategories.filter(c => c.name.toLowerCase().includes(q));
            let h = '<div class="p-3 text-xs text-slate-400 font-bold uppercase tracking-widest sticky top-0 bg-white border-b border-slate-50">Select category</div>';
            h += f.length === 0 ? '<div class="px-4 py-3 text-sm text-slate-400 italic">No categories found</div>'
                : f.map(c => `<div onclick="selectDistCategory(${c.id},'${c.name.replace(/'/g,"\\'")}')" class="px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-red-50 hover:text-[#c00000] cursor-pointer transition-colors border-b border-slate-50 last:border-0">${c.name}</div>`).join('');
            dd.innerHTML = h;
        }
        function selectDistCategory(id, name) {
            document.getElementById('distCategoryId').value = id;
            document.getElementById('distCategoryName').value = name;
            document.getElementById('distCategoryDropdownList').classList.add('hidden');
            document.getElementById('distCategoryMissing').classList.add('hidden');
            document.getElementById('distItemId').value = '';
            document.getElementById('distItemName').value = '';
            document.getElementById('distMasterStockLabel').classList.add('hidden');
            distSelectedSubItems = []; renderDistSubItemQtyBoxes();
        }

        // --- Distribution Item ---
        function toggleDistItemDropdown() { rebuildDistItemDropdown(); document.getElementById('distItemDropdownList').classList.toggle('hidden'); }
        function filterDistItem() {
            rebuildDistItemDropdown();
            document.getElementById('distItemDropdownList').classList.remove('hidden');
            const name = document.getElementById('distItemName').value.trim().toLowerCase();
            const catId = document.getElementById('distCategoryId').value;
            let pool = catId ? rawItems.filter(i => i.category_id == catId) : rawItems;
            document.getElementById('distItemMissing').classList.toggle('hidden', !name || pool.some(i => i.name.toLowerCase() === name));
        }
        function rebuildDistItemDropdown() {
            const dd = document.getElementById('distItemDropdownList');
            const q = document.getElementById('distItemName').value.toLowerCase();
            const catId = document.getElementById('distCategoryId').value;
            let pool = catId ? rawItems.filter(i => i.category_id == catId) : rawItems;
            const f = pool.filter(i => i.name.toLowerCase().includes(q)).slice(0, 50);
            let h = '<div class="p-3 text-xs text-slate-400 font-bold uppercase tracking-widest sticky top-0 bg-white border-b border-slate-50">Select item</div>';
            h += f.length === 0 ? '<div class="px-4 py-3 text-sm text-slate-400 italic">No items found</div>'
                : f.map(i => `<div onclick="selectDistItem(${i.id},'${i.name.replace(/'/g,"\\'")}')" class="px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 cursor-pointer transition-colors border-b border-slate-50 last:border-0">${i.name}</div>`).join('');
            dd.innerHTML = h;
        }
        function selectDistItem(id, name) {
            document.getElementById('distItemId').value = id;
            document.getElementById('distItemName').value = name;
            document.getElementById('distItemDropdownList').classList.add('hidden');
            document.getElementById('distItemMissing').classList.add('hidden');
            const item = rawItems.find(i => i.id == id);
            const masterQty = item && item.master_quantity ? Number(item.master_quantity) : 0;
            const distributed = item && item.distributed_quantity ? Number(item.distributed_quantity) : 0;
            currentDistMasterStock = Math.max(0, masterQty - distributed);
            const stockEl = document.getElementById('distMasterStockValue');
            stockEl.textContent = currentDistMasterStock;
            stockEl.classList.toggle('text-emerald-600', currentDistMasterStock > 0);
            stockEl.classList.toggle('text-red-600', currentDistMasterStock === 0);
            document.getElementById('distMasterStockLabel').classList.remove('hidden');
            if (!document.getElementById('distCategoryId').value && item) {
                const cat = rawCategories.find(c => c.id == item.category_id);
                if (cat) { document.getElementById('distCategoryId').value = cat.id; document.getElementById('distCategoryName').value = cat.name; }
            }
            distSelectedSubItems = []; renderDistSubItemQtyBoxes();
        }

        // --- Distribution Schools ---
        function toggleDistSchoolDropdown() { rebuildDistSchoolDropdown(); document.getElementById('distSchoolDropdownList').classList.toggle('hidden'); }
        function filterDistSchools() { rebuildDistSchoolDropdown(); document.getElementById('distSchoolDropdownList').classList.remove('hidden'); }
        function rebuildDistSchoolDropdown() {
            const dd = document.getElementById('distSchoolDropdownList');
            const q = document.getElementById('distSchoolSearch').value.toLowerCase();
            const f = allSchoolsList.filter(s => !distSelectedSchools.includes(s.id) && (s.name.toLowerCase().includes(q) || (s.school_id && s.school_id.toString().includes(q)))).slice(0, 50);
            let h = '<div class="p-3 text-xs text-slate-400 font-bold uppercase tracking-widest sticky top-0 bg-white border-b border-slate-50">Select school</div>';
            h += f.length === 0 ? '<div class="px-4 py-3 text-sm text-slate-400 italic">No matches found</div>'
                : f.map(s => `<div onclick="selectDistSchool(${s.id},'${s.name.replace(/'/g,"\\'")}')" class="px-4 py-3 text-xs font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 cursor-pointer transition-colors border-b border-slate-50 last:border-0 truncate">${s.school_id ? s.school_id+' - ':''}${s.name}</div>`).join('');
            dd.innerHTML = h;
        }
        function selectDistSchool(id) { if (!distSelectedSchools.includes(id)) distSelectedSchools.push(id); renderDistSelectedSchools(); document.getElementById('distSchoolSearch').value=''; document.getElementById('distSchoolDropdownList').classList.add('hidden'); }
        function removeDistSchool(id) { distSelectedSchools = distSelectedSchools.filter(s => s !== id); renderDistSelectedSchools(); }
        function renderDistSelectedSchools() {
            const c = document.getElementById('distSelectedSchoolsContainer');
            if (!distSelectedSchools.length) { c.innerHTML = ''; return; }
            c.innerHTML = distSelectedSchools.map(id => { const s = allSchoolsList.find(x => x.id === id); return `<div class="px-3 py-1.5 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-xl flex items-center gap-2 border border-emerald-100 shadow-sm"><span class="truncate max-w-[200px]" title="${s.name}">${s.name}</span><button type="button" onclick="removeDistSchool(${id})" class="text-emerald-400 hover:text-emerald-800 ml-1 font-bold shrink-0">✕</button></div>`; }).join('');
        }

        // --- Distribution Sub-Items with Dynamic Qty ---
        function toggleDistSubItemDropdown() { rebuildDistSubItemDropdown(); document.getElementById('distSubItemDropdownList').classList.toggle('hidden'); }
        function filterDistSubItems() {
            rebuildDistSubItemDropdown();
            document.getElementById('distSubItemDropdownList').classList.remove('hidden');
            const name = document.getElementById('distSubItemSearch').value.trim().toLowerCase();
            const itemId = document.getElementById('distItemId').value;
            let pool = itemId ? rawSubItems.filter(s => s.item_id == itemId) : rawSubItems;
            document.getElementById('distSubItemMissing').classList.toggle('hidden', !name || pool.some(s => s.name.toLowerCase() === name));
        }
        function rebuildDistSubItemDropdown() {
            const dd = document.getElementById('distSubItemDropdownList');
            const q = document.getElementById('distSubItemSearch').value.toLowerCase();
            const itemId = document.getElementById('distItemId').value;
            let pool = itemId ? rawSubItems.filter(s => s.item_id == itemId) : rawSubItems;
            const f = pool.filter(s => !distSelectedSubItems.some(ds => ds.id === s.id) && s.name.toLowerCase().includes(q)).slice(0, 50);
            let h = '<div class="p-3 text-xs text-slate-400 font-bold uppercase tracking-widest sticky top-0 bg-white border-b border-slate-50">Select sub-item</div>';
            h += f.length === 0 ? '<div class="px-4 py-3 text-sm text-slate-400 italic">No sub-items found</div>'
                : f.map(s => `<div onclick="selectDistSubItem(${s.id},'${s.name.replace(/'/g,"\\'")}')" class="px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-blue-50 hover:text-blue-700 cursor-pointer transition-colors border-b border-slate-50 last:border-0">${s.name}</div>`).join('');
            dd.innerHTML = h;
        }
        function selectDistSubItem(id, name) {
            if (distSelectedSubItems.length >= 5) { Swal.fire({ title: 'Limit Reached', text: 'Max 5 sub-items per distribution.', icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } }); return; }
            if (!distSelectedSubItems.some(ds => ds.id === id)) { distSelectedSubItems.push({ id, name, qty: 0 }); renderDistSubItemQtyBoxes(); }
            document.getElementById('distSubItemSearch').value = '';
            document.getElementById('distSubItemDropdownList').classList.add('hidden');
            document.getElementById('distSubItemMissing').classList.add('hidden');
        }
        function removeDistSubItem(id) { distSelectedSubItems = distSelectedSubItems.filter(s => s.id !== id); renderDistSubItemQtyBoxes(); validateDistQty(); }
        function renderDistSubItemQtyBoxes() {
            const c = document.getElementById('distSubItemQtyContainer');
            if (!distSelectedSubItems.length) { c.innerHTML = ''; return; }
            c.innerHTML = distSelectedSubItems.map(si => `
                <div class="flex items-center gap-3 p-3 bg-blue-50 rounded-2xl border border-blue-100 animate-in fade-in slide-in-from-top-2 duration-300">
                    <span class="flex-grow text-xs font-bold text-blue-700">${si.name}</span>
                    <input type="number" min="1" step="1" placeholder="Qty" value="${si.qty || ''}" oninput="updateDistSubItemQty(${si.id}, this.value)" class="w-24 p-3 bg-white border border-blue-200 rounded-xl outline-none font-bold text-sm text-center focus:ring-2 focus:ring-blue-200 transition-all">
                    <button type="button" onclick="removeDistSubItem(${si.id})" class="text-blue-400 hover:text-red-500 font-bold text-lg shrink-0">✕</button>
                </div>`).join('');
        }
        function updateDistSubItemQty(id, val) { const si = distSelectedSubItems.find(s => s.id === id); if (si) si.qty = parseInt(val) || 0; validateDistQty(); }
        function validateDistQty() {
            const totalPerSchool = distSelectedSubItems.reduce((s, x) => s + (x.qty || 0), 0);
            const numSchools = Math.max(1, distSelectedSchools.length);
            const total = totalPerSchool * numSchools;
            document.getElementById('distQtyWarning').classList.toggle('hidden', total <= currentDistMasterStock || currentDistMasterStock === 0);
        }

        // --- Distribution Confirm Submit ---
        function confirmDistributionSubmit() {
            const itemId = document.getElementById('distItemId').value;
            const itemName = document.getElementById('distItemName').value.trim();
            if (!itemId) { Swal.fire({ title: 'Item Required', text: 'Please select an existing item from the Master Registry.', icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } }); return; }
            if (!distSelectedSchools.length) { Swal.fire({ title: 'School Required', text: 'Please select at least one school.', icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } }); return; }
            if (!distSelectedSubItems.length) { Swal.fire({ title: 'Sub-Item Required', text: 'Please select at least one sub-item to distribute.', icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } }); return; }
            
            const totalPerSchool = distSelectedSubItems.reduce((s, x) => s + (x.qty || 0), 0);
            const totalQty = totalPerSchool * distSelectedSchools.length;
            
            if (distSelectedSubItems.some(s => !s.qty || s.qty <= 0)) { Swal.fire({ title: 'Invalid Quantity', text: 'Enter a valid quantity for every sub-item.', icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } }); return; }
            if (totalQty > currentDistMasterStock && currentDistMasterStock > 0) { Swal.fire({ title: 'Exceeds Remaining Stock', text: `Total requested (${totalQty}) exceeds Remaining Stock (${currentDistMasterStock}).`, icon: 'error', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } }); return; }

            const hc = document.getElementById('hiddenDistInputsContainer');
            let hh = distSelectedSchools.map(id => `<input type="hidden" name="school_ids[]" value="${id}">`).join('');
            distSelectedSubItems.forEach(si => { hh += `<input type="hidden" name="dist_sub_items[${si.id}]" value="${si.qty}">`; });
            hc.innerHTML = hh;

            Swal.fire({
                title: 'Confirm Distribution', text: `Distribute "${itemName}" (${distSelectedSubItems.length} sub-item(s), total qty: ${totalQty}) to ${distSelectedSchools.length} school(s)?`, icon: 'question',
                showCancelButton: true, confirmButtonColor: '#c00000', cancelButtonColor: '#94a3b8', confirmButtonText: 'Yes, distribute!',
                customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6', cancelButton: 'rounded-xl font-bold px-6' }
            }).then((result) => { if (result.isConfirmed) document.getElementById('distributionForm').submit(); });
        }

        // Close all dropdowns on outside click
        document.addEventListener('click', function(e) {
            [['itemDropdownList','itemDropdownBtn','itemName'],['categoryDropdownList','categoryDropdownBtn','categoryName'],
             ['itemCategoryDropdownList','itemCategoryDropdownBtn','itemCategoryName'],
             ['distCategoryDropdownList','distCategoryDropdownBtn','distCategoryName'],
             ['distItemDropdownList','distItemDropdownBtn','distItemName'],
             ['distSchoolDropdownList','distSchoolDropdownBtn','distSchoolSearch'],
             ['distSubItemDropdownList','distSubItemDropdownBtn','distSubItemSearch']
            ].forEach(([ddId, btnId, inputId]) => {
                const dd = document.getElementById(ddId), btn = document.getElementById(btnId), inp = document.getElementById(inputId);
                if (dd && btn && !dd.contains(e.target) && !btn.contains(e.target) && e.target !== inp && e.target.id !== inputId) dd.classList.add('hidden');
            });
        });
    </script>
</body>
</html>
