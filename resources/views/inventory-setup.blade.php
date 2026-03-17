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
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <div onclick="nextStep(3, 'school')" class="bg-white p-8 rounded-[2.5rem] shadow-lg border border-slate-100 hover:border-[#c00000] hover:-translate-y-2 transition-all cursor-pointer group">
                        <div class="w-12 h-12 bg-red-50 text-[#c00000] rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:rotate-12 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A4.833 4.833 0 0012 9a4.833 4.833 0 00-7.5 1.332V21m15 0h-15" />
                            </svg>
                        </div>
                        <span class="block font-extrabold text-slate-800 uppercase text-[10px] tracking-widest">Schools</span>
                    </div>

                    <div onclick="nextStep(3, 'district')" class="bg-white p-8 rounded-[2.5rem] shadow-lg border border-slate-100 hover:border-[#c00000] hover:-translate-y-2 transition-all cursor-pointer group">
                        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:rotate-12 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                        </div>
                        <span class="block font-extrabold text-slate-800 uppercase text-[10px] tracking-widest">Districts</span>
                    </div>

                    <div onclick="nextStep(3, 'category')" class="bg-white p-8 rounded-[2.5rem] shadow-lg border border-slate-100 hover:border-[#c00000] hover:-translate-y-2 transition-all cursor-pointer group">
                        <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:rotate-12 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.625-1.219a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 001.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            </svg>
                        </div>
                        <span class="block font-extrabold text-slate-800 uppercase text-[10px] tracking-widest text-center">Main Category</span>
                    </div>

                    <div onclick="nextStep(3, 'item')" class="bg-white p-8 rounded-[2.5rem] shadow-lg border border-slate-100 hover:border-[#c00000] hover:-translate-y-2 transition-all cursor-pointer group">
                        <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:rotate-12 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                            </svg>
                        </div>
                        <span class="block font-extrabold text-slate-800 uppercase text-[10px] tracking-widest">Inventory Items</span>
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
                html += `<form id="itemForm" action="{{ route('inventory.setup.item') }}" method="POST" class="space-y-6">
                            @csrf
                            <input type="hidden" name="existing_item_id" id="existingItemId" value="">
                            <div id="hiddenSchoolInputsContainer"></div>
                            
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Select School(s) <span class="text-xs text-slate-300 normal-case font-medium">(Optional)</span></label>
                                <div class="relative">
                                    <div class="flex">
                                        <input type="text" id="schoolSearch" placeholder="Type to search school..." class="flex-grow p-4 bg-slate-50 border border-slate-100 rounded-l-2xl outline-none font-semibold transition-all" autocomplete="off" oninput="filterSchools()">
                                        <button type="button" onclick="toggleSchoolDropdown()" id="schoolDropdownBtn" class="px-4 bg-slate-50 border border-l-0 border-slate-100 rounded-r-2xl text-slate-400 hover:text-[#c00000] hover:bg-red-50 transition-all" title="Select school">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                        </button>
                                    </div>
                                    <div id="schoolDropdownList" class="hidden absolute z-20 w-full mt-1 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-[200px] overflow-y-auto custom-scroll">
                                        <!-- Javascript populates this -->
                                    </div>
                                </div>
                                <div id="selectedSchoolsContainer" class="flex flex-wrap gap-2 mt-2 ml-1"></div>
                            </div>

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
                                    <div id="itemCategoryDropdownList" class="hidden absolute z-20 w-full mt-1 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-[200px] overflow-y-auto custom-scroll">
                                        <!-- Javascript populates this -->
                                    </div>
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

                            <div class="space-y-4 pt-4 border-t border-slate-100">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Select Existing Sub-Item(s) <span class="text-xs text-slate-300 normal-case font-medium">(Optional)</span></label>
                                    <div class="relative">
                                        <div class="flex">
                                            <input type="text" id="subItemSearch" placeholder="Type to search existing sub-items..." class="flex-grow p-4 bg-slate-50 border border-slate-100 rounded-l-2xl outline-none font-semibold transition-all" autocomplete="off" oninput="filterSubItems()">
                                            <button type="button" onclick="toggleSubItemDropdown()" id="subItemDropdownBtn" class="px-4 bg-slate-50 border border-l-0 border-slate-100 rounded-r-2xl text-slate-400 hover:text-[#c00000] hover:bg-red-50 transition-all" title="Select existing sub-item">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                            </button>
                                        </div>
                                        <div id="subItemDropdownList" class="hidden absolute z-20 w-full mt-1 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-[200px] overflow-y-auto custom-scroll">
                                            <!-- Javascript populates this -->
                                        </div>
                                    </div>
                                    <div id="selectedSubItemsContainer" class="flex flex-wrap gap-2 mt-2 ml-1"></div>
                                </div>

                                <div class="space-y-3">
                                    <div class="flex justify-between items-center ml-1">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Or create New Specifications / Sub-Items</label>
                                        <button type="button" onclick="addSubItemField()" class="text-[10px] font-bold bg-red-50 text-[#c00000] px-3 py-1 rounded-lg hover:bg-[#c00000] hover:text-white transition-all">+ Add Spec</button>
                                    </div>
                                <div id="subItemContainer" class="space-y-3 max-h-[200px] overflow-y-auto pr-2 custom-scroll">
                                    <div class="flex gap-2 group">
                                        <input type="text" name="sub_items[]" placeholder="e.g. RAM 8GB" class="flex-grow p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-sm">
                                        <button type="button" onclick="this.parentElement.remove()" class="px-4 text-slate-300 hover:text-red-500 font-bold">✕</button>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Quantity <span class="text-xs text-slate-400 font-medium normal-case">(If assigning to school)</span></label>
                                <input type="number" name="quantity" id="itemQuantity" min="1" step="any" placeholder="e.g. 10" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold transition-all disabled:opacity-50 disabled:bg-slate-100 disabled:cursor-not-allowed" disabled title="Select a school first to enable quantity">
                            </div>
                            <button type="button" onclick="confirmItemSubmit()" class="w-full py-5 ${btnColor} text-white rounded-3xl font-bold shadow-xl transition-all hover:-translate-y-1 active:scale-95">${modeText} Item</button>
                        </form>`;
            }
            container.innerHTML = html;
            if (currentModule === 'item') {
                selectedSchoolsArray = [];
                selectedSubItemsArray = [];
                renderSelectedSchools();
                rebuildSchoolDropdown();
                renderSelectedSubItems();
                rebuildSubItemDropdown();
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

        function confirmItemSubmit() {
            const form = document.getElementById('itemForm');
            const itemName = document.getElementById('itemName').value.trim();
            const categoryId = document.getElementById('itemCategoryId').value;
            const categoryName = document.getElementById('itemCategoryName').value.trim();
            const existingId = document.getElementById('existingItemId').value;

            if (!categoryId && !categoryName) {
                Swal.fire({ title: 'Category Required', text: 'Please select or type a main category.', icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                return;
            }
            if (!itemName) {
                Swal.fire({ title: 'Item Name Required', text: 'Please enter an item name or select an existing one.', icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                return;
            }
            // Block if duplicate detected and user is NOT selecting an existing item
            if (itemDuplicateBlocked && !existingId) {
                Swal.fire({ title: 'Duplicate Item', text: `"${itemName}" already exists in the system. Use the dropdown (▼) to select the existing item if you want to add sub-items to it.`, icon: 'error', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                return;
            }

            const quantityStr = document.getElementById('itemQuantity').value;
            const quantity = parseFloat(quantityStr);

            if (selectedSchoolsArray.length > 0 && (!quantityStr || quantity <= 0)) {
                Swal.fire({ title: 'Invalid Quantity', text: 'Please enter a valid quantity (greater than 0) when assigning items to schools.', icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                return;
            }

            // Populate hidden inputs for schools and existing sub-items
            const hiddenContainer = document.getElementById('hiddenSchoolInputsContainer');
            let hiddenHtml = selectedSchoolsArray.map(id => `<input type="hidden" name="school_ids[]" value="${id}">`).join('');
            hiddenHtml += selectedSubItemsArray.map(id => `<input type="hidden" name="existing_sub_item_ids[]" value="${id}">`).join('');
            hiddenContainer.innerHTML = hiddenHtml;

            // Gather sub-items for display
            const subInputs = document.querySelectorAll('#subItemContainer input[name="sub_items[]"]');
            const subNames = Array.from(subInputs).map(i => i.value.trim()).filter(v => v);

            let msg = existingId
                ? `Use existing item "${itemName}"`
                : `Add new item "${itemName}"`;
            let totalSubItems = subNames.length + selectedSubItemsArray.length;
            if (totalSubItems > 0) {
                msg += ` with ${totalSubItems} specification(s)`;
            }
            if (selectedSchoolsArray.length > 0) {
                msg += ` and assign quantity of ${quantity} to ${selectedSchoolsArray.length} school(s)`;
            }
            msg += '?';

            Swal.fire({
                title: 'Confirm Registration',
                text: msg,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#c00000',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, register it!',
                customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6', cancelButton: 'rounded-xl font-bold px-6' }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }

        // School Selection Logic
        function toggleSchoolDropdown() {
            const dropdown = document.getElementById('schoolDropdownList');
            rebuildSchoolDropdown();
            dropdown.classList.toggle('hidden');
        }

        function filterSchools() {
            rebuildSchoolDropdown();
            document.getElementById('schoolDropdownList').classList.remove('hidden');
        }

        function rebuildSchoolDropdown() {
            const dropdown = document.getElementById('schoolDropdownList');
            const query = document.getElementById('schoolSearch').value.toLowerCase();
            
            const filtered = allSchoolsList.filter(s => 
                !selectedSchoolsArray.includes(s.id) && 
                (s.name.toLowerCase().includes(query) || (s.school_id && s.school_id.toString().includes(query)))
            ).slice(0, 50); // limit to 50 results for performance

            let html = '<div class="p-3 text-xs text-slate-400 font-bold uppercase tracking-widest sticky top-0 bg-white border-b border-slate-50 w-full mb-1">Select school</div>';
            
            if (filtered.length === 0) {
                html += '<div class="px-4 py-3 text-sm text-slate-400 italic">No matches found</div>';
            } else {
                html += filtered.map(s => `
                    <div onclick="selectSchool(${s.id}, '${s.name.replace(/'/g, "\\'")}')"
                         class="px-4 py-3 text-xs font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 cursor-pointer transition-colors border-b border-slate-50 last:border-0 truncate">
                        ${s.school_id ? s.school_id + ' - ' : ''}${s.name}
                    </div>
                `).join('');
            }
            dropdown.innerHTML = html;
        }

        function selectSchool(id, name) {
            if (!selectedSchoolsArray.includes(id)) {
                selectedSchoolsArray.push(id);
                renderSelectedSchools();
            }
            document.getElementById('schoolSearch').value = '';
            document.getElementById('schoolDropdownList').classList.add('hidden');
            document.getElementById('itemQuantity').required = true;
            document.getElementById('itemQuantity').disabled = false;
        }

        function removeSchool(id) {
            selectedSchoolsArray = selectedSchoolsArray.filter(s => s !== id);
            renderSelectedSchools();
            if (selectedSchoolsArray.length === 0) {
                document.getElementById('itemQuantity').required = false;
                document.getElementById('itemQuantity').disabled = true;
                document.getElementById('itemQuantity').value = '';
            }
        }

        function renderSelectedSchools() {
            const container = document.getElementById('selectedSchoolsContainer');
            if (selectedSchoolsArray.length === 0) {
                container.innerHTML = '';
                return;
            }
            container.innerHTML = selectedSchoolsArray.map(id => {
                const school = allSchoolsList.find(s => s.id === id);
                return `
                    <div class="px-3 py-1.5 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-xl flex items-center gap-2 border border-emerald-100 shadow-sm animate-in fade-in zoom-in duration-200">
                        <span class="truncate max-w-[200px]" title="${school.name}">${school.name}</span>
                        <button type="button" onclick="removeSchool(${id})" class="text-emerald-400 hover:text-emerald-800 focus:outline-none ml-1 font-bold shrink-0">✕</button>
                    </div>
                `;
            }).join('');
        }

        // Sub-Item Selection Logic
        function toggleSubItemDropdown() {
            const dropdown = document.getElementById('subItemDropdownList');
            rebuildSubItemDropdown();
            dropdown.classList.toggle('hidden');
        }

        function filterSubItems() {
            rebuildSubItemDropdown();
            document.getElementById('subItemDropdownList').classList.remove('hidden');
        }

        function rebuildSubItemDropdown() {
            const dropdown = document.getElementById('subItemDropdownList');
            const query = document.getElementById('subItemSearch').value.toLowerCase();
            const itemId = document.getElementById('existingItemId').value;
            
            // Only show sub-items that belong to the currently selected item (if one is selected via dropdown)
            let possibleSubItems = rawSubItems;
            if (itemId) {
                possibleSubItems = rawSubItems.filter(s => s.item_id == itemId);
            }

            const filtered = possibleSubItems.filter(s => 
                !selectedSubItemsArray.includes(s.id) && 
                s.name.toLowerCase().includes(query)
            ).slice(0, 50);

            let html = '<div class="p-3 text-xs text-slate-400 font-bold uppercase tracking-widest sticky top-0 bg-white border-b border-slate-50 w-full mb-1">Select existing sub-item</div>';
            
            if (filtered.length === 0) {
                html += '<div class="px-4 py-3 text-sm text-slate-400 italic">No matches found</div>';
            } else {
                html += filtered.map(s => {
                    // Include parent item name if an item isn't explicitly selected
                    const parentItemName = itemId ? '' : ` <span class="text-[10px] text-slate-400 font-medium ml-1">in ${rawItems.find(i=>i.id == s.item_id)?.name || 'Unknown'}</span>`;
                    return `
                    <div onclick="selectSubItem(${s.id}, '${s.name.replace(/'/g, "\\'")}')"
                         class="px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 cursor-pointer transition-colors border-b border-slate-50 last:border-0 truncate flex justify-between items-center">
                        <span>${s.name} ${parentItemName}</span>
                        <div class="text-[10px] bg-slate-100 text-slate-400 px-2 py-0.5 rounded-md leading-none self-center">Spec</div>
                    </div>
                `}).join('');
            }
            dropdown.innerHTML = html;
        }

        function selectSubItem(id, name) {
            if (!selectedSubItemsArray.includes(id)) {
                selectedSubItemsArray.push(id);
                renderSelectedSubItems();
            }
            document.getElementById('subItemSearch').value = '';
            document.getElementById('subItemDropdownList').classList.add('hidden');
            
            // Selecting an existing sub-item forces the parent item to be selected too
            const sub = rawSubItems.find(s => s.id == id);
            const parentItem = rawItems.find(i => i.id == sub.item_id);
            if(sub && parentItem && !document.getElementById('existingItemId').value) {
                 selectExistingItem(parentItem.id, parentItem.name);
                 
                 // Also select the category
                 const parentCat = rawCategories.find(c => c.id == parentItem.category_id);
                 if (parentCat) selectItemCategory(parentCat.id, parentCat.name);
            }
        }

        function removeSubItem(id) {
            selectedSubItemsArray = selectedSubItemsArray.filter(s => s !== id);
            renderSelectedSubItems();
        }

        function renderSelectedSubItems() {
            const container = document.getElementById('selectedSubItemsContainer');
            if (selectedSubItemsArray.length === 0) {
                container.innerHTML = '';
                return;
            }
            container.innerHTML = selectedSubItemsArray.map(id => {
                const sub = rawSubItems.find(s => s.id === id);
                return `
                    <div class="px-3 py-1.5 bg-blue-50 text-blue-700 text-xs font-bold rounded-xl flex items-center gap-2 border border-blue-100 shadow-sm animate-in fade-in zoom-in duration-200">
                        <span class="truncate max-w-[200px]" title="${sub.name}">${sub.name}</span>
                        <button type="button" onclick="removeSubItem(${id})" class="text-blue-400 hover:text-blue-800 focus:outline-none ml-1 font-bold shrink-0">✕</button>
                    </div>
                `;
            }).join('');
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            const itemDropdown = document.getElementById('itemDropdownList');
            const itemBtn = document.getElementById('itemDropdownBtn');
            if (itemDropdown && itemBtn && !itemDropdown.contains(e.target) && !itemBtn.contains(e.target) && e.target.id !== 'itemName') {
                itemDropdown.classList.add('hidden');
            }

            const schoolDropdown = document.getElementById('schoolDropdownList');
            const schoolBtn = document.getElementById('schoolDropdownBtn');
            const schoolSearch = document.getElementById('schoolSearch');
            if (schoolDropdown && schoolBtn && !schoolDropdown.contains(e.target) && !schoolBtn.contains(e.target) && e.target !== schoolSearch) {
                schoolDropdown.classList.add('hidden');
            }

            const categoryDropdown = document.getElementById('categoryDropdownList');
            const categoryBtn = document.getElementById('categoryDropdownBtn');
            if (categoryDropdown && categoryBtn && !categoryDropdown.contains(e.target) && !categoryBtn.contains(e.target) && e.target.id !== 'categoryName') {
                categoryDropdown.classList.add('hidden');
            }
            const itemCategoryDropdown = document.getElementById('itemCategoryDropdownList');
            const itemCategoryBtn = document.getElementById('itemCategoryDropdownBtn');
            const itemCategoryName = document.getElementById('itemCategoryName');
            if (itemCategoryDropdown && itemCategoryBtn && !itemCategoryDropdown.contains(e.target) && !itemCategoryBtn.contains(e.target) && e.target !== itemCategoryName) {
                itemCategoryDropdown.classList.add('hidden');
            }

            const subItemDropdown = document.getElementById('subItemDropdownList');
            const subItemBtn = document.getElementById('subItemDropdownBtn');
            const subItemSearch = document.getElementById('subItemSearch');
            if (subItemDropdown && subItemBtn && !subItemDropdown.contains(e.target) && !subItemBtn.contains(e.target) && e.target !== subItemSearch) {
                subItemDropdown.classList.add('hidden');
            }
        });
    </script>
</body>
</html>