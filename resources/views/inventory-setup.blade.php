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
        let stepHistory = [1];
        let currentMode = '';
        let currentModule = '';

        const rawCategories = {{ Js::from($categories) }};
        const rawItems = {{ Js::from($items) }};
        
<<<<<<< HEAD
        const rawDistricts = @json($districts);
        const rawLds = @json($legislativeDistricts);
        const rawQuadrants = @json($quadrants);
=======
        const rawDistricts = {{ Js::from($districts) }};
        const rawLds = {{ Js::from($legislativeDistricts) }};
        const rawQuadrants = {{ Js::from($quadrants) }};
        const districtMap = {};
        rawDistricts.forEach(d => {
            districtMap[d.name] = { ld: d.legislative_district_id, quad: d.quadrant_name.replace('Quadrant ', '') };
        });
>>>>>>> f2aaa546900485130db9a3682139afada578a9e0

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
            stepHistory.push(step);
            updateBackButton();
        }

        function goBack() {
            if (stepHistory.length > 1) {
                stepHistory.pop();
                const prevStep = stepHistory[stepHistory.length - 1];
                document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
                document.getElementById('step' + prevStep).classList.add('active');
                updateBackButton();
            }
        }

        function updateBackButton() {
            const btn = document.getElementById('backBtn');
            btn.classList.toggle('hidden', stepHistory[stepHistory.length - 1] === 1);
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
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Main Category Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" id="categoryName" placeholder="e.g. Electronics" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold transition-all" required>
                            </div>
<<<<<<< HEAD
                            <button class="w-full py-5 ${btnColor} text-white rounded-3xl font-bold shadow-xl active:scale-95">${modeText} Category</button>
                        </div>`;
=======
                            <button type="button" onclick="confirmCategorySubmit()" class="w-full py-5 ${btnColor} text-white rounded-3xl font-bold shadow-xl transition-all hover:-translate-y-1 active:scale-95">${modeText} Category Settings</button>
                        </form>`;
>>>>>>> f2aaa546900485130db9a3682139afada578a9e0
            } else if (currentModule === 'item') {
                html += `<form id="itemForm" action="{{ route('inventory.setup.item') }}" method="POST" class="space-y-6">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="existing_item_id" id="existingItemId" value="">
                            <div class="space-y-2">
<<<<<<< HEAD
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Main Category</label>
                                <select class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold cursor-pointer">
                                    <option value="">Select Category</option>
                                    ${mainCategories.map(c => `<option value="${c}">${c}</option>`).join('')}
=======
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Select Main Category <span class="text-red-500">*</span></label>
                                <select name="category_id" id="itemCategory" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold cursor-pointer focus:ring-2 focus:ring-red-100 transition-all" required onchange="onCategoryChange()">
                                    <option value="">-- Choose Category --</option>
                                    ${rawCategories.map(c => `<option value="${c.id}">${c.name}</option>`).join('')}
>>>>>>> f2aaa546900485130db9a3682139afada578a9e0
                                </select>
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
                                <p id="itemDuplicateWarning" class="hidden text-xs font-semibold text-red-600 ml-1">⚠ This item already exists in the system. Please use the dropdown to select it instead.</p>
                            </div>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center ml-1">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Specifications / Sub-Items</label>
                                    <button type="button" onclick="addSubItemField()" class="text-[10px] font-bold bg-red-50 text-[#c00000] px-3 py-1 rounded-lg hover:bg-[#c00000] hover:text-white transition-all">+ Add Spec</button>
                                </div>
                                <div id="subItemContainer" class="space-y-3 max-h-[200px] overflow-y-auto pr-2 custom-scroll">
                                    <div class="flex gap-2 group">
                                        <input type="text" name="sub_items[]" placeholder="e.g. RAM 8GB" class="flex-grow p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-sm">
                                        <button type="button" onclick="this.parentElement.remove()" class="px-4 text-slate-300 hover:text-red-500 font-bold">✕</button>
                                    </div>
                                </div>
                            </div>
<<<<<<< HEAD
                            <button class="w-full py-5 ${btnColor} text-white rounded-3xl font-bold shadow-xl active:scale-95">${modeText} Item</button>
                        </div>`;
=======
                            <button type="button" onclick="confirmItemSubmit()" class="w-full py-5 ${btnColor} text-white rounded-3xl font-bold shadow-xl transition-all hover:-translate-y-1 active:scale-95">${modeText} Item Details</button>
                        </form>`;
>>>>>>> f2aaa546900485130db9a3682139afada578a9e0
            }
            container.innerHTML = html;
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

        function confirmCategorySubmit() {
            const form = document.getElementById('categoryForm');
            if (form.checkValidity()) {
                const name = document.getElementById('categoryName').value;
                Swal.fire({
                    title: "Add New Category",
                    text: `Are you sure you want to add "${name}" as a new category?`,
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonColor: "#c00000",
                    cancelButtonColor: "#94a3b8",
                    confirmButtonText: "Yes, add it!",
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

        function onCategoryChange() {
            const catId = document.getElementById('itemCategory').value;
            const dropdown = document.getElementById('itemDropdownList');
            // Reset item selection
            document.getElementById('existingItemId').value = '';
            document.getElementById('itemName').value = '';
            document.getElementById('itemName').readOnly = false;
            document.getElementById('itemName').classList.remove('bg-emerald-50', 'border-emerald-200');
            document.getElementById('itemExistingHint').classList.add('hidden');
            // Rebuild dropdown items filtered by category
            rebuildItemDropdown(catId);
        }

        function rebuildItemDropdown(catId) {
            const dropdown = document.getElementById('itemDropdownList');
            const filtered = catId ? rawItems.filter(i => i.category_id == catId) : rawItems;
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
            const catId = document.getElementById('itemCategory').value;
            if (!catId) {
                Swal.fire({ title: 'Select a Category First', text: 'Please choose a category before selecting an item.', icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
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
            document.getElementById('itemExistingHint').classList.remove('hidden');
            document.getElementById('itemDropdownList').classList.add('hidden');
        }

        function clearItemSelection() {
            document.getElementById('existingItemId').value = '';
            document.getElementById('itemName').value = '';
            document.getElementById('itemName').readOnly = false;
            document.getElementById('itemName').classList.remove('bg-emerald-50', 'border-emerald-200');
            document.getElementById('itemExistingHint').classList.add('hidden');
            document.getElementById('itemDropdownList').classList.add('hidden');
            document.getElementById('itemName').focus();
        }

        let itemDuplicateBlocked = false;

        function checkItemDuplicate() {
            const input = document.getElementById('itemName');
            const warning = document.getElementById('itemDuplicateWarning');
            const existingId = document.getElementById('existingItemId').value;
            const name = input.value.trim().toLowerCase();

            // Skip check if user selected an existing item from dropdown
            if (existingId) {
                warning.classList.add('hidden');
                itemDuplicateBlocked = false;
                input.classList.remove('border-red-400', 'bg-red-50');
                return;
            }

            if (name && rawItems.some(i => i.name.toLowerCase() === name)) {
                warning.classList.remove('hidden');
                itemDuplicateBlocked = true;
                input.classList.add('border-red-400', 'bg-red-50');
            } else {
                warning.classList.add('hidden');
                itemDuplicateBlocked = false;
                input.classList.remove('border-red-400', 'bg-red-50');
            }
        }

        function confirmItemSubmit() {
            const form = document.getElementById('itemForm');
            const itemName = document.getElementById('itemName').value.trim();
            const categoryId = document.getElementById('itemCategory').value;
            const existingId = document.getElementById('existingItemId').value;

            if (!categoryId) {
                Swal.fire({ title: 'Category Required', text: 'Please select a main category.', icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
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

            // Gather sub-items for display
            const subInputs = document.querySelectorAll('#subItemContainer input[name="sub_items[]"]');
            const subNames = Array.from(subInputs).map(i => i.value.trim()).filter(v => v);


            let msg = existingId
                ? `Use existing item "${itemName}"`
                : `Add new item "${itemName}"`;
            if (subNames.length > 0) {
                msg += ` with ${subNames.length} sub-item(s): ${subNames.join(', ')}`;
            }
            msg += '?';

            Swal.fire({
                title: 'Confirm Item',
                text: msg,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#c00000',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, add it!',
                customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6', cancelButton: 'rounded-xl font-bold px-6' }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('itemDropdownList');
            const btn = document.getElementById('itemDropdownBtn');
            if (dropdown && btn && !dropdown.contains(e.target) && !btn.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    </script>
</body>
</html>