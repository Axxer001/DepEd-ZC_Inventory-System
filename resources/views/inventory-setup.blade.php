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
    
    <div class="flex flex-wrap justify-center gap-6 max-w-5xl mx-auto px-4">
        
        {{-- Schools --}}
        <div onclick="nextStep(3, 'school')" class="bg-white p-8 w-full sm:w-[256px] rounded-[2.5rem] shadow-lg border border-slate-100 hover:border-[#c00000] hover:-translate-y-2 transition-all cursor-pointer group">
            <div class="w-12 h-12 bg-red-50 text-[#c00000] rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:rotate-12 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A4.833 4.833 0 0012 9a4.833 4.833 0 00-7.5 1.332V21m15 0h-15" /></svg>
            </div>
            <span class="block font-extrabold text-slate-800 uppercase text-[10px] tracking-widest">Schools</span>
        </div>

        {{-- Distributors --}}
<a href="{{ route('inventory.setup.add_distributors') }}" class="bg-white p-8 w-full sm:w-[256px] rounded-[2.5rem] shadow-lg border border-slate-100 hover:border-[#c00000] hover:-translate-y-2 transition-all cursor-pointer group block text-center">
    <div class="w-12 h-12 bg-orange-50 text-orange-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:rotate-12 transition-transform">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.129-1.125V11.25c0-4.446-3.604-8.1-8.1-8.1H9a8.1 8.1 0 0 0-8.1 8.1v3.375c0 .621.504 1.125 1.125 1.125H3.375M9 15h3.375M9 15V3.375M9 15h3.375M9 15h3.375" />
        </svg>
    </div>
    <span class="block font-extrabold text-slate-800 uppercase text-[10px] tracking-widest">Distributors</span>
</a>

{{-- Recipients --}}
<a href="{{ route('inventory.setup.add_recipients') }}" class="bg-white p-8 w-full sm:w-[256px] rounded-[2.5rem] shadow-lg border border-slate-100 hover:border-[#c00000] hover:-translate-y-2 transition-all cursor-pointer group block text-center">
    <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:rotate-12 transition-transform">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
        </svg>
    </div>
    <span class="block font-extrabold text-slate-800 uppercase text-[10px] tracking-widest">Recipients</span>
</a>

        {{-- Inventory Items --}}
        <div onclick="nextStep(3, 'item')" class="bg-white p-8 w-full sm:w-[256px] rounded-[2.5rem] shadow-lg border border-slate-100 hover:border-[#c00000] hover:-translate-y-2 transition-all cursor-pointer group">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:rotate-12 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>
            </div>
            <span class="block font-extrabold text-slate-800 uppercase text-[10px] tracking-widest">Inventory Items</span>
        </div>

        {{-- Asset Distribution --}}
        <div onclick="nextStep(3, 'distribution')" class="bg-white p-8 w-full sm:w-[256px] rounded-[2.5rem] shadow-lg border border-slate-100 hover:border-[#c00000] hover:-translate-y-2 transition-all cursor-pointer group">
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:rotate-12 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-10.5v.008H15V4.5zm0 9v.008H15V13.5zm0-4.5v.008H15V9zm0-4.5v.008H15V4.5zM9 15l-3 1.5L3 15V5.25l3-1.5L9 5.25M9 15l3.047-1.524c.499-.25 1.096-.217 1.565.083L17.25 15l3-1.5V4.5l-3 1.5-3.638-2.046c-.469-.264-1.025-.264-1.494 0L9 5.25" /></svg>
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

                <div class="max-w-2xl mx-auto bg-white p-10 rounded-[3rem] shadow-2xl border border-slate-50 relative overflow-visible">
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

        document.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('step') === '2' && urlParams.get('mode') === 'edit') {
                nextStep(2, 'edit');
            }
        });

        const rawCategories = {{ Js::from($categories) }};
        const rawItems = {{ Js::from($items) }};
        const rawSubItems = {{ Js::from($subItems) }};
        
        const rawDistricts = @json($districts);
        const rawLds = @json($legislativeDistricts);
        const rawQuadrants = @json($quadrants);
        const allSchoolsList = @json($allSchools);
        const rawStakeholders = @json($stakeholders);
        const rawOwnerships = @json($stakeholderOwnerships);
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
                if (currentMode === 'edit' && value === 'school') {
                    window.location.href = '/inventory-modifier/school';
                    return;
                }
                if (currentMode === 'edit' && value === 'distribution') {
                    window.location.href = '/inventory-modifier';
                    return;
                }
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
            const parentWrap = container.parentElement;
            
            if (currentModule === 'distribution') {
                parentWrap.classList.remove('max-w-2xl', 'overflow-hidden');
                parentWrap.classList.add('max-w-5xl', 'overflow-visible');
            } else {
                parentWrap.classList.remove('max-w-5xl', 'overflow-visible');
                parentWrap.classList.add('max-w-2xl', 'overflow-hidden');
            }

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
            } else if (currentModule === 'item' && currentMode === 'edit') {
                // ===== EDIT MODE: UPDATE / DELETE ITEMS =====
                html += `
                    <p id="editModeDesc" class="text-slate-400 text-xs font-semibold mb-6 -mt-4">Choose an action, then select the record type and target.</p>

                    {{-- Action Mode Selector --}}
                    <div class="flex gap-3 mb-6">
                        <button type="button" id="editModeUpdateBtn" onclick="setEditAction('update')" class="flex-1 py-4 rounded-2xl font-bold text-sm transition-all border-2 border-[#c00000] bg-red-50 text-[#c00000]">
                            ✏️ Update / Rename
                        </button>
                        <button type="button" id="editModeDeleteBtn" onclick="setEditAction('delete')" class="flex-1 py-4 rounded-2xl font-bold text-sm transition-all border-2 border-slate-200 bg-white text-slate-400 hover:border-red-300 hover:text-red-400">
                            🗑️ Delete
                        </button>
                    </div>

                    {{-- Type Selector --}}
                    <div class="space-y-2 mb-6">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Record Type <span class="text-red-500">*</span></label>
                        <select id="renameType" onchange="onRenameTypeChange()" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold focus:ring-2 focus:ring-red-100 transition-all cursor-pointer">
                            <option value="">-- Select record type --</option>
                            <option value="category">Category</option>
                            <option value="item">Item</option>
                            <option value="sub_item">Sub-Item</option>
                        </select>
                    </div>

                    {{-- Cascading Dropdowns Row --}}
                    <div id="renameCascadeRow" class="hidden flex flex-col sm:flex-row gap-3 mb-6">
                        <div id="renameCatWrap" class="flex-1 space-y-2 hidden">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Select Category <span class="text-red-500">*</span></label>
                            <select id="renameCatSelect" onchange="onRenameCatChange()" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold focus:ring-2 focus:ring-red-100 transition-all cursor-pointer">
                                <option value="">-- Choose Category --</option>
                                ${rawCategories.map(c => `<option value="${c.id}" data-name="${c.name.replace(/"/g,'&quot;')}">${c.name}</option>`).join('')}
                            </select>
                        </div>
                        <div id="renameItemWrap" class="flex-1 space-y-2 hidden">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Select Item <span class="text-red-500">*</span></label>
                            <select id="renameItemSelect" onchange="onRenameItemChange()" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold focus:ring-2 focus:ring-red-100 transition-all cursor-pointer disabled:opacity-50">
                                <option value="">-- Choose Item --</option>
                            </select>
                        </div>
                        <div id="renameSubWrap" class="flex-1 space-y-2 hidden">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Select Sub-Item <span class="text-red-500">*</span></label>
                            <select id="renameSubSelect" onchange="onRenameSubChange()" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold focus:ring-2 focus:ring-red-100 transition-all cursor-pointer disabled:opacity-50">
                                <option value="">-- Choose Sub-Item --</option>
                            </select>
                        </div>
                    </div>

                    {{-- Rename Input Box (UPDATE mode only) --}}
                    <div id="renameInputWrap" class="hidden space-y-2 mb-6">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">New Name <span class="text-red-500">*</span></label>
                        <input type="text" id="renameNewName" placeholder="Enter new name..." class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold transition-all focus:ring-2 focus:ring-red-100">
                        <p id="renameCurrentHint" class="text-xs font-semibold text-slate-400 ml-1"></p>
                    </div>

                    {{-- Delete Warning (DELETE mode only) --}}
                    <div id="deleteWarningWrap" class="hidden mb-6">
                        <p id="deleteCurrentHint" class="text-xs font-semibold text-slate-500 ml-1 mb-2"></p>
                        <div id="deleteImpactBox" class="hidden p-4 bg-red-50 border border-red-200 rounded-2xl space-y-1">
                            <p class="text-xs font-black text-red-600 uppercase tracking-widest mb-2">⚠ Deletion Impact</p>
                            <p id="deleteImpactDetails" class="text-sm font-semibold text-red-700 leading-relaxed"></p>
                        </div>
                    </div>

                    <button type="button" id="renameSubmitBtn" onclick="submitRename()" class="hidden w-full py-5 bg-[#c00000] hover:bg-red-700 shadow-red-100 text-white rounded-3xl font-bold shadow-xl transition-all hover:-translate-y-1 active:scale-95">
                        Rename Record
                    </button>
                    <button type="button" id="deleteSubmitBtn" onclick="submitDelete()" class="hidden w-full py-5 bg-red-600 hover:bg-red-800 shadow-red-200 text-white rounded-3xl font-bold shadow-xl transition-all hover:-translate-y-1 active:scale-95">
                        🗑️ Permanently Delete Record
                    </button>
                `;
            } else if (currentModule === 'item') {
                // ===== MODULE 1: MASTER REGISTRY (Inventory Items) — ADD MODE =====
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

                            <div class="space-y-4 pt-4 border-t border-slate-100">
                                <div class="space-y-3">
                                    <div class="flex justify-between items-center ml-1">
                                    <div class="flex flex-col">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Specifications / Sub-Items <span class="text-red-500">*</span></label>
                                        <span class="text-[10px] text-slate-400 font-medium">Add specs & quantities (Max 10 total). Required for initial stock.</span>
                                    </div>
                                    <button type="button" id="addSpecBtn" onclick="addSubItemField()" class="text-[10px] font-bold bg-slate-100 text-slate-500 px-3 py-1 rounded-lg hover:bg-slate-200 transition-all">+ Add New Spec</button>
                                </div>
                                
                                <div id="existingSubItemBlock" class="hidden mt-4 bg-emerald-50/50 p-4 border border-emerald-100 rounded-[1.5rem]">
                                    <label class="block text-[10px] font-bold text-emerald-700 uppercase tracking-widest mb-3">1. Add Stock to Existing Specs</label>
                                    <div class="relative">
                                         <input type="text" id="existingSubItemSearch" placeholder="Search existing sub-items..." class="w-full p-4 bg-white border border-emerald-200 rounded-2xl outline-none font-bold text-slate-700 transition-all text-sm focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100" autocomplete="off" oninput="filterExistingSubItems()" onfocus="filterExistingSubItems()">
                                         <div id="existingSubItemDropdownList" class="hidden absolute z-30 w-full mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-[200px] overflow-y-auto custom-scroll text-left"></div>
                                    </div>
                                    <div id="existingSubItemCardsContainer" class="flex flex-col gap-2 mt-3 empty:hidden"></div>
                                </div>

                                <div class="mt-4">
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 ml-1">2. Create New Specifications</label>
                                    <div id="subItemContainer" class="space-y-3 max-h-[250px] overflow-y-auto pr-2 custom-scroll">
                                        <div class="flex gap-2 group sub-item-row relative">
                                            <input type="text" name="sub_items[]" placeholder="e.g. Default/General or RAM 8GB" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-sm flex-1" required autocomplete="off" oninput="checkSubItemDuplicate(this)">
                                            <input type="number" name="sub_item_quantities[]" placeholder="Qty" min="1" step="1" class="w-20 p-4 flex-shrink-0 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-sm text-center" required>
                                            <select name="sub_item_conditions[]" class="w-36 p-4 flex-shrink-0 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-sm cursor-pointer" title="Condition">
                                                <option value="Serviceable" selected>Serviceable</option>
                                                <option value="Unserviceable">Unserviceable</option>
                                                <option value="For Repair">For Repair</option>
                                            </select>
                                            <button type="button" onclick="removeSubItemField(this)" class="px-3 text-slate-300 hover:text-red-500 font-bold transition-colors">✕</button>
                                        </div>
                                    </div>
                                </div>
                                
                                <p id="subItemLimitWarning" class="hidden text-xs font-bold text-red-500 ml-1">⚠ Maximum of 10 sub-items allowed total.</p>
                                </div>
                            </div>
                            <button type="button" onclick="confirmMasterItemSubmit()" class="w-full py-5 ${btnColor} text-white rounded-3xl font-bold shadow-xl transition-all hover:-translate-y-1 active:scale-95">${modeText} Item</button>
                        </form>`;


            } else if (currentModule === 'distribution') {
                html += `
                    <div id="distPreSelectionPhase" class="space-y-6 animate-in fade-in zoom-in duration-300">
                        <div class="text-center mb-8">
                            <h4 class="text-2xl font-black text-slate-800 uppercase tracking-tight italic">Step 3a: Select Source & Targets</h4>
                            <p class="text-slate-500 text-sm mt-2 font-medium">Select where the assets are coming from, and up to 6 recipients.</p>
                        </div>
                        <div class="max-w-xl mx-auto space-y-6">
                            <!-- Source Distributor Selection -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Distributor (Source) <span class="text-red-500">*</span></label>
                                <select id="globalSourceDistributor" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none font-bold text-slate-700 transition-all focus:border-[#c00000] focus:ring-4 focus:ring-red-100 cursor-pointer">
                                    <optgroup label="System Categories">
                                        <option value="">-- Fetching System Stakeholders --</option>
                                    </optgroup>
                                </select>
                            </div>

                            <hr class="border-slate-100">

                            <!-- Target Recipients Search (Phased out allSchoolsList logic) -->
                            <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Target Recipients (Max 6)</label>
                            <div class="relative">
                                <input type="text" id="preDistSchoolSearch" placeholder="Type name or code..." class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none font-bold text-slate-700 transition-all text-center focus:border-[#c00000] focus:ring-4 focus:ring-red-100" autocomplete="off" oninput="filterPreDistSchools()" onfocus="filterPreDistSchools()">
                                <div id="preDistSchoolDropdownList" class="hidden absolute z-30 w-full mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-[250px] overflow-y-auto custom-scroll"></div>
                            </div>
                            <div id="preDistSelectedSchoolsContainer" class="flex flex-col gap-2 mt-4 min-h-[50px]">
                                <span class="text-slate-400 text-xs font-bold italic w-full text-center mt-1 select-prompt">No recipients selected yet.</span>
                            </div>
                            <p id="preDistLimitWarning" class="hidden text-center text-xs font-bold text-red-500 mt-2">⚠ Maximum of 6 recipients reached.</p>
                            </div>
                            <button type="button" id="proceedDistBtn" onclick="proceedToDistributionTabs()" class="w-full mt-8 py-5 bg-slate-200 text-slate-400 rounded-3xl font-black uppercase tracking-widest cursor-not-allowed transition-all" disabled>Proceed to Assign Assets</button>
                        </div>
                    </div>
                    
                    <div id="distTabsPhase" class="hidden space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-6 gap-4">
                            <div>
                                <h4 class="text-2xl font-black text-slate-800 uppercase tracking-tight italic">Step 3b: Assign Assets</h4>
                                <p class="text-slate-500 text-sm mt-1 font-medium">Distribute assets individually per tab, or all at once.</p>
                                <p id="distPhaseSourceLabel" class="text-xs font-bold text-[#c00000] mt-1 bg-red-50 inline-block px-3 py-1 rounded-lg border border-red-100"></p>
                            </div>
                            <button type="button" onclick="backToPreSelectionPhase()" class="text-xs font-bold text-slate-400 hover:text-[#c00000] underline underline-offset-4 shrink-0 transition-colors bg-transparent border-0">« Revise Source/Targets</button>
                        </div>
                        <div class="flex flex-col md:flex-row gap-6">
                            <div class="md:w-1/4 flex flex-col gap-2 border-r border-slate-100 pr-4 max-h-[500px] overflow-y-auto custom-scroll" id="distTabsHeader"></div>
                            <div id="distTabsContentContainer" class="md:w-3/4 min-h-[400px]"></div>
                        </div>

                        <div class="pt-6 border-t border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-4">
                            <span class="text-[10px] font-black tracking-widest uppercase text-slate-500 bg-slate-100 rounded-xl px-4 py-2" id="tabStatusCount">0 Assets Ready</span>
                            <button type="button" onclick="confirmDistributeAll()" id="distributeAllBtn" class="px-8 py-4 bg-[#c00000] hover:bg-red-700 text-white rounded-2xl font-black shadow-xl hover:-translate-y-1 active:scale-95 transition-all text-sm uppercase tracking-wider w-full sm:w-auto">Distribute All Tabs</button>
                        </div>
                    </div>
                `;
            }
            container.innerHTML = html;
            if (currentModule === 'item' && currentMode !== 'edit') {
                document.getElementById('itemDropdownList').classList.add('hidden');
                document.getElementById('itemName').readOnly = false;
                document.getElementById('itemName').classList.remove('bg-emerald-50', 'border-emerald-200', 'bg-blue-50', 'border-blue-400');
            }
            if (currentModule === 'distribution') {
                preSelectedSchools = [];
                distTabsData = [];
                currentActiveTab = 0;
                renderPreSelectedSchools();
                populateSourceStakeholders();
            }
        }
        
        function populateSourceStakeholders() {
            const select = document.getElementById('globalSourceDistributor');
            if (!select) return;
            let html = '<option value="">-- Select Source Distributor --</option>';
            
            const grouped = rawStakeholders.reduce((acc, obj) => {
                const key = obj.type || 'Other';
                if (!acc[key]) acc[key] = [];
                acc[key].push(obj);
                return acc;
            }, {});
            
            for (const type in grouped) {
                html += `<optgroup label="${type} Stakeholders">`;
                grouped[type].forEach(s => {
                    html += `<option value="${s.id}" data-name="${s.name}">${s.name}</option>`;
                });
                html += `</optgroup>`;
            }
            select.innerHTML = html;
            
            // Auto-select System Warehouse if it exists
            const systemWarehouse = rawStakeholders.find(s => s.type === 'System' && s.name.includes('Warehouse'));
            if (systemWarehouse) {
                select.value = systemWarehouse.id;
            }
        }

        function addSubItemField() {
            if (getTotalSubItemRows() >= 10) {
                document.getElementById('subItemLimitWarning').classList.remove('hidden');
                document.getElementById('addSpecBtn').classList.add('opacity-50', 'cursor-not-allowed');
                return;
            }
            
            document.getElementById('subItemLimitWarning').classList.add('hidden');
            const container = document.getElementById('subItemContainer');
            const div = document.createElement('div');
            div.className = "flex gap-2 group animate-in fade-in slide-in-from-top-2 duration-300 sub-item-row relative";
            div.innerHTML = `
                <input type="text" name="sub_items[]" placeholder="Enter specification" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-sm flex-1" required autocomplete="off" oninput="checkSubItemDuplicate(this)">
                <input type="number" name="sub_item_quantities[]" placeholder="Qty" min="1" step="1" class="w-20 p-4 flex-shrink-0 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-sm text-center" required>
                <select name="sub_item_conditions[]" class="w-36 p-4 flex-shrink-0 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-sm cursor-pointer" title="Condition">
                    <option value="Serviceable" selected>Serviceable</option>
                    <option value="Unserviceable">Unserviceable</option>
                    <option value="For Repair">For Repair</option>
                </select>
                <button type="button" onclick="removeSubItemField(this)" class="px-3 text-slate-300 hover:text-red-500 font-bold transition-colors">✕</button>
            `;
            container.appendChild(div);
            container.scrollTop = container.scrollHeight;
            updateSubItemRowStates();
        }

        function removeSubItemField(btnEl) {
            btnEl.closest('.sub-item-row').remove();
            updateSubItemRowStates();
        }

        function getTotalSubItemRows() {
            return document.querySelectorAll('#existingSubItemCardsContainer .sub-item-row').length + document.querySelectorAll('#subItemContainer .sub-item-row').length;
        }

        function updateSubItemRowStates() {
            const total = getTotalSubItemRows();
            const warning = document.getElementById('subItemLimitWarning');
            const btn = document.getElementById('addSpecBtn');
            const search = document.getElementById('existingSubItemSearch');
            if (total >= 10) {
                warning.classList.remove('hidden');
                btn.classList.add('opacity-50', 'cursor-not-allowed');
                if(search) search.disabled = true;
            } else {
                warning.classList.add('hidden');
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
                if(search) search.disabled = false;
            }
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

        let selectedExistingSpecs = [];

        function toggleExistingSpecBlock(itemId) {
            const block = document.getElementById('existingSubItemBlock');
            if (!itemId) {
                block.classList.add('hidden');
                return;
            }
            const available = rawSubItems.filter(s => s.item_id == itemId);
            if (available.length > 0) {
                block.classList.remove('hidden');
            } else {
                block.classList.add('hidden');
            }
            // Clear selections when switching item
            selectedExistingSpecs = [];
            document.getElementById('existingSubItemCardsContainer').innerHTML = '';
            document.getElementById('existingSubItemSearch').value = '';
            updateSubItemRowStates();
        }

        function filterExistingSubItems() {
            const dropdown = document.getElementById('existingSubItemDropdownList');
            const q = document.getElementById('existingSubItemSearch').value.trim().toLowerCase();
            const existingId = document.getElementById('existingItemId').value;
            const itemName = document.getElementById('itemName').value.trim().toLowerCase();
            const exactItemMatch = rawItems.find(i => i.name.toLowerCase() === itemName);
            const resolvedItemId = existingId || (exactItemMatch ? exactItemMatch.id : null);
            
            if(!resolvedItemId) {
                dropdown.classList.add('hidden');
                return;
            }

            const available = rawSubItems.filter(s => s.item_id == resolvedItemId && !selectedExistingSpecs.includes(s.id));
            const filtered = q ? available.filter(s => s.name.toLowerCase().includes(q)) : available;

            dropdown.classList.remove('hidden');
            let html = '<div class="p-3 text-xs text-slate-400 font-bold uppercase tracking-widest sticky top-0 bg-white border-b border-slate-50">Select spec</div>';
            if (filtered.length === 0) {
                html += '<div class="px-4 py-3 text-sm text-slate-400 italic">No exact matches left.</div>';
            } else {
                filtered.forEach(s => {
                    html += `<div onclick="selectExistingSpecCard(${s.id}, '${s.name.replace(/'/g, "\\'")}', ${s.quantity})" class="px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 cursor-pointer transition-colors border-b border-slate-50 truncate">${s.name} <span class="text-xs font-bold text-slate-400 ml-2">(${s.quantity} in stock)</span></div>`;
                });
            }
            html += `<div onclick="closeExistingSpecDropdown()" class="px-4 py-3 text-xs font-bold text-slate-400 hover:bg-slate-50 cursor-pointer transition-colors text-center border-t border-slate-100">✕ Close</div>`;
            dropdown.innerHTML = html;
        }

        function closeExistingSpecDropdown() {
            const drp = document.getElementById('existingSubItemDropdownList');
            if(drp) drp.classList.add('hidden');
        }

        function selectExistingSpecCard(id, name, stock) {
            if (getTotalSubItemRows() >= 10) {
                document.getElementById('subItemLimitWarning').classList.remove('hidden');
                return;
            }

            selectedExistingSpecs.push(id);
            document.getElementById('existingSubItemSearch').value = '';
            closeExistingSpecDropdown();

            const container = document.getElementById('existingSubItemCardsContainer');
            const div = document.createElement('div');
            div.className = "flex items-center justify-between p-4 bg-white border border-slate-200 rounded-2xl shadow-sm animate-in fade-in zoom-in duration-300 sub-item-row";
            div.innerHTML = `
                <div class="flex flex-col">
                    <span class="font-bold text-sm text-slate-800">${name}</span>
                    <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600">${stock} IN STOCK</span>
                </div>
                <div class="flex items-center gap-3">
                    <input type="hidden" name="sub_items[]" value="${name}">
                    <input type="number" name="sub_item_quantities[]" placeholder="Qty" min="1" step="1" class="w-20 p-3 bg-slate-50 border border-slate-100 rounded-xl outline-none font-bold text-sm text-center focus:border-emerald-400" required>
                    <select name="sub_item_conditions[]" class="w-36 p-3 bg-slate-50 border border-slate-100 rounded-xl outline-none font-semibold text-sm cursor-pointer" title="Condition">
                        <option value="Serviceable" selected>Serviceable</option>
                        <option value="Unserviceable">Unserviceable</option>
                        <option value="For Repair">For Repair</option>
                    </select>
                    <button type="button" onclick="removeExistingSpecCard(this, ${id})" class="text-slate-300 hover:text-red-500 font-bold p-2 transition-colors">✕</button>
                </div>
            `;
            container.appendChild(div);
            updateSubItemRowStates();
            
            Array.from(document.querySelectorAll('#subItemContainer input[name="sub_items[]"]')).forEach(i => checkSubItemDuplicate(i));
        }

        function removeExistingSpecCard(btn, id) {
            selectedExistingSpecs = selectedExistingSpecs.filter(i => i !== id);
            btn.closest('.sub-item-row').remove();
            updateSubItemRowStates();
            Array.from(document.querySelectorAll('#subItemContainer input[name="sub_items[]"]')).forEach(i => checkSubItemDuplicate(i));
        }

        function checkSubItemDuplicate(inputEl) {
            const val = inputEl.value.trim().toLowerCase();
            const itemId = document.getElementById('existingItemId').value; 
            const itemName = document.getElementById('itemName').value.trim().toLowerCase();
            const exactItemMatch = rawItems.find(i => i.name.toLowerCase() === itemName);
            const resolvedItemId = itemId || (exactItemMatch ? exactItemMatch.id : null);

            let isDuplicate = false;
            if (resolvedItemId && val) {
                 isDuplicate = rawSubItems.some(s => s.item_id == resolvedItemId && s.name.toLowerCase() === val);
            }

            if (isDuplicate) {
                 inputEl.classList.add('border-red-400', 'bg-red-50', 'text-red-700');
                 inputEl.setAttribute('title', 'Spec already exists for this Item. Use the Existing Selection tool above.');
            } else {
                 inputEl.classList.remove('border-red-400', 'bg-red-50', 'text-red-700');
                 inputEl.removeAttribute('title');
            }
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
            toggleExistingSpecBlock(id);
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
            toggleExistingSpecBlock(null);
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
                toggleExistingSpecBlock(existingId);
                return;
            }

            const exactMatch = rawItems.find(i => i.name.toLowerCase() === name);
            if (name && exactMatch) {
                warning.classList.remove('hidden');
                if(newHint) newHint.classList.add('hidden');
                itemDuplicateBlocked = true;
                input.classList.add('border-red-400', 'bg-red-50');
                input.classList.remove('border-blue-400', 'bg-blue-50');
                toggleExistingSpecBlock(exactMatch.id);
            } else if (name) {
                warning.classList.add('hidden');
                if(newHint) newHint.classList.remove('hidden');
                itemDuplicateBlocked = false;
                input.classList.remove('border-red-400', 'bg-red-50');
                input.classList.add('border-blue-400', 'bg-blue-50');
                toggleExistingSpecBlock(null);
            } else {
                warning.classList.add('hidden');
                if(newHint) newHint.classList.add('hidden');
                itemDuplicateBlocked = false;
                input.classList.remove('border-red-400', 'bg-red-50', 'border-blue-400', 'bg-blue-50');
                toggleExistingSpecBlock(null);
            }
        }

        function confirmMasterItemSubmit() {
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
            if (itemDuplicateBlocked && !existingId) {
                Swal.fire({ title: 'Duplicate Item', text: `"${itemName}" already exists. Use the dropdown to select it.`, icon: 'error', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                return;
            }

            const existingRows = Array.from(document.querySelectorAll('#existingSubItemCardsContainer .sub-item-row'));
            const newRows = Array.from(document.querySelectorAll('#subItemContainer .sub-item-row'));
            const allSubRows = existingRows.concat(newRows);

            if (allSubRows.length === 0) {
                Swal.fire({ title: 'Sub-Item Required', text: 'Please add at least one specification with its quantity.', icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                return;
            }
            
            let totalQty = 0;
            let validSubCount = 0;
            let hasError = false;

            allSubRows.forEach(row => {
                const nameInput = row.querySelector('input[name="sub_items[]"]').value.trim();
                const qtyInput = row.querySelector('input[name="sub_item_quantities[]"]').value;
                if (!nameInput || !qtyInput || parseInt(qtyInput) < 1) {
                    hasError = true;
                } else {
                    validSubCount++;
                    totalQty += parseInt(qtyInput);
                }
            });

            if (hasError || validSubCount === 0) {
                Swal.fire({ title: 'Invalid Sub-Items', text: 'Please ensure all specifications have a name and a valid quantity of at least 1.', icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                return;
            }

            let msg = existingId
                ? `Update existing item "${itemName}" by adding ${totalQty} unit(s)`
                : `Register new item "${itemName}" with total quantity of ${totalQty} unit(s)`;
            msg += ` across ${validSubCount} specification(s)?`;

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
        let preSelectedSchools = []; // Array of objects { id, name, uid } to allow duplicates
        let distTabsData = []; // State for each tab
        let currentActiveTab = 0;

        // --- Phase 1: Pre-selection ---
        function filterPreDistSchools() {
            const dd = document.getElementById('preDistSchoolDropdownList');
            const q = document.getElementById('preDistSchoolSearch').value.trim().toLowerCase();
            dd.classList.remove('hidden');
            
            // Also filter out the currently selected Source
            const sourceSelect = document.getElementById('globalSourceDistributor');
            const sourceId = sourceSelect ? parseInt(sourceSelect.value) : null;
            
            const f = rawStakeholders.filter(s => 
                (s.id !== sourceId) && 
                (s.name.toLowerCase().includes(q) || (s.school_id && s.school_id.toString().includes(q)))
            ).slice(0, 50);
            
            let h = '<div class="p-3 text-[10px] text-slate-400 font-extrabold uppercase tracking-widest sticky top-0 bg-white/90 backdrop-blur border-b border-slate-100 z-10">Select recipient</div>';
            h += f.length === 0 ? '<div class="px-4 py-4 text-sm font-bold text-slate-400 text-center italic">No recipients found</div>'
                : f.map(s => `<div onclick="addPreDistSchool(${s.id},'${s.name.replace(/'/g,"\\'")}')" class="px-4 py-3 text-sm font-bold text-slate-700 hover:bg-red-50 hover:text-[#c00000] cursor-pointer transition-colors border-b border-slate-50 last:border-0 truncate"><span class="text-[10px] bg-slate-100 text-slate-500 px-2 py-0.5 rounded-md mr-2">${s.type}</span> ${s.school_id ? s.school_id+' - ':''}${s.name}</div>`).join('');
            dd.innerHTML = h;
        }

        function addPreDistSchool(id, name) {
            const sourceSelect = document.getElementById('globalSourceDistributor');
            if (sourceSelect && !sourceSelect.value) {
                Swal.fire({ title: 'Select Source', text: 'Please select a Distributor (Source) first.', icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                return;
            }
            if (preSelectedSchools.length >= 6) {
                document.getElementById('preDistLimitWarning').classList.remove('hidden');
                return;
            }
            preSelectedSchools.push({ id, name, uid: Date.now() + Math.random() });
            renderPreSelectedSchools();
            document.getElementById('preDistSchoolSearch').value = '';
            document.getElementById('preDistSchoolDropdownList').classList.add('hidden');
            checkPreDistLimit();
        }

        function removePreDistSchool(uid) {
            preSelectedSchools = preSelectedSchools.filter(s => s.uid !== uid);
            renderPreSelectedSchools();
            checkPreDistLimit();
        }

        function checkPreDistLimit() {
            const warning = document.getElementById('preDistLimitWarning');
            const btn = document.getElementById('proceedDistBtn');
            warning.classList.toggle('hidden', preSelectedSchools.length < 6);
            if (preSelectedSchools.length > 0) {
                btn.className = "w-full mt-6 py-5 bg-[#c00000] hover:bg-red-700 text-white rounded-3xl font-black uppercase tracking-widest shadow-xl hover:-translate-y-1 active:scale-95 transition-all";
                btn.disabled = false;
            } else {
                btn.className = "w-full mt-6 py-5 bg-slate-200 text-slate-400 rounded-3xl font-black uppercase tracking-widest cursor-not-allowed transition-all";
                btn.disabled = true;
            }
        }

        function renderPreSelectedSchools() {
            const container = document.getElementById('preDistSelectedSchoolsContainer');
            if (preSelectedSchools.length === 0) {
                container.innerHTML = '<span class="text-slate-400 text-xs font-bold italic w-full text-center mt-1 select-prompt">No schools selected yet.</span>';
                return;
            }
            container.innerHTML = preSelectedSchools.map((s, idx) => `
                <div class="px-4 py-3 bg-white border border-slate-200 shadow-sm rounded-xl flex items-center gap-4 animate-in fade-in slide-in-from-top-2 duration-200">
                    <div class="w-6 h-6 rounded-full bg-slate-100 text-slate-500 font-extrabold text-[10px] flex items-center justify-center shrink-0">${idx + 1}</div>
                    <span class="text-sm font-bold text-slate-700 truncate w-full" title="${s.name}">${s.name}</span>
                    <button type="button" onclick="removePreDistSchool(${s.uid})" class="w-8 h-8 rounded-full hover:bg-red-50 text-slate-300 hover:text-red-500 flex items-center justify-center shrink-0 transition-colors">✕</button>
                </div>
            `).join('');
        }

        // --- Phase 2: Tabs ---
        function proceedToDistributionTabs() {
            if (preSelectedSchools.length === 0) return;
            
            const sourceSelect = document.getElementById('globalSourceDistributor');
            if (!sourceSelect || !sourceSelect.value) {
                Swal.fire({ title: 'Select Source', text: 'Please select a Distributor (Source) before proceeding.', icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                return;
            }
            const sourceId = sourceSelect.value;
            const sourceName = sourceSelect.options[sourceSelect.selectedIndex].text;
            
            document.getElementById('distPreSelectionPhase').classList.add('hidden');
            document.getElementById('distTabsPhase').classList.remove('hidden');
            
            document.getElementById('distPhaseSourceLabel').innerText = `Source: ${sourceName}`;
            
            // Initialize tab data states
            distTabsData = preSelectedSchools.map((school, i) => ({
                tabIndex: i,
                distributor_id: parseInt(sourceId),
                distributor_name: sourceName,
                recipient_id: school.id,
                recipient_name: school.name,
                category_id: null,
                item_id: null,
                subItemsSelected: [] // array of { id, name, available_qty, selected_qty, condition }
            }));

            renderTabsUI();
            switchTab(0);
        }

        function backToPreSelectionPhase() {
            document.getElementById('distTabsPhase').classList.add('hidden');
            document.getElementById('distPreSelectionPhase').classList.remove('hidden');
        }

        function renderTabsUI() {
            const headerObj = document.getElementById('distTabsHeader');
            const contentContainer = document.getElementById('distTabsContentContainer');
            
            headerObj.innerHTML = distTabsData.map((tab, i) => `
                <button type="button" id="tabBtn_${i}" onclick="switchTab(${i})" class="px-4 py-4 rounded-2xl font-bold text-sm text-left transition-all border-2 border-transparent text-slate-400 hover:text-slate-600 hover:bg-slate-50 w-full shrink-0">
                    <span class="text-[10px] uppercase font-black text-slate-300 block leading-none mb-1">Tab ${i + 1}</span>
                    <span class="block w-full leading-snug truncate" title="${tab.recipient_name}">${tab.recipient_name}</span>
                </button>
            `).join('');

            contentContainer.innerHTML = distTabsData.map((tab, i) => `
                <div id="tabContent_${i}" class="hidden space-y-6">
                    <div class="p-4 bg-slate-50 rounded-2xl border border-dashed border-slate-300 mb-6 flex flex-col sm:flex-row justify-between sm:items-center gap-4">
                        <div>
                            <span class="text-xs font-black text-slate-400 uppercase tracking-widest block mb-1">Distributing Asset To:</span>
                            <span class="text-lg font-bold text-slate-800">${tab.recipient_name}</span>
                        </div>
                        <button type="button" onclick="confirmDistributeSingleTab(${i})" class="px-6 py-3 bg-slate-800 hover:bg-slate-900 text-white rounded-xl font-bold shadow-md hover:-translate-y-1 active:scale-95 transition-all text-xs uppercase tracking-wider whitespace-nowrap">Distribute This Tab</button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Category Selection -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Main Category <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="text" id="tabCatSearch_${i}" placeholder="Search category..." class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold transition-all focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-50" autocomplete="off" onfocus="filterTabCat(${i})" oninput="filterTabCat(${i})">
                                <div id="tabCatDropdown_${i}" class="hidden absolute z-30 w-full mt-1 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-[200px] overflow-y-auto custom-scroll"></div>
                            </div>
                            <p id="tabCatError_${i}" class="hidden text-xs font-bold text-red-500 ml-1 mt-1"></p>
                        </div>

                        <!-- Item Selection -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Item Name <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="text" id="tabItemSearch_${i}" placeholder="Search existing item..." class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold transition-all focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-50 disabled:opacity-50 disabled:cursor-not-allowed" autocomplete="off" onfocus="filterTabItem(${i})" oninput="filterTabItem(${i})" disabled>
                                <div id="tabItemDropdown_${i}" class="hidden absolute z-20 w-full mt-1 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-[200px] overflow-y-auto custom-scroll"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Sub-items Selection -->
                    <div class="space-y-4 pt-4 border-t border-slate-100">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Select Sub-Item(s) to Distribute</label>
                            <div class="relative">
                                <input type="text" id="tabSubSearch_${i}" placeholder="Search sub-items..." class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold transition-all focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-50 disabled:opacity-50 disabled:cursor-not-allowed" autocomplete="off" onfocus="filterTabSub(${i})" oninput="filterTabSub(${i})" disabled>
                                <div id="tabSubDropdown_${i}" class="hidden absolute z-10 w-full mt-1 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-[200px] overflow-y-auto custom-scroll"></div>
                            </div>
                            <div id="tabSubContainer_${i}" class="space-y-3 mt-4"></div>
                        </div>
                    </div>
                </div>
            `).join('');
            
            updateReadyStatus();
        }

        function switchTab(index) {
            currentActiveTab = index;
            distTabsData.forEach((_, i) => {
                const btn = document.getElementById(`tabBtn_${i}`);
                const content = document.getElementById(`tabContent_${i}`);
                if (i === index) {
                    btn.classList.add('text-[#c00000]', 'border-red-100', 'bg-red-50');
                    btn.classList.remove('text-slate-400', 'border-transparent', 'bg-slate-50');
                    content.classList.remove('hidden');
                    content.classList.add('animate-in', 'fade-in', 'duration-300');
                } else {
                    btn.classList.remove('text-[#c00000]', 'border-red-100', 'bg-red-50');
                    btn.classList.add('text-slate-400', 'border-transparent');
                    content.classList.add('hidden');
                }
            });
        }

        function filterTabCat(tabId) {
            const dd = document.getElementById(`tabCatDropdown_${tabId}`);
            const q = document.getElementById(`tabCatSearch_${tabId}`).value.trim().toLowerCase();
            dd.classList.remove('hidden');

            const currentSchoolId = distTabsData[tabId].school_id;
            const usedCategories = [];
            distTabsData.forEach((tab, index) => {
                if (index !== tabId && tab.school_id === currentSchoolId && tab.category_id) {
                    usedCategories.push(tab.category_id);
                }
            });

            const f = rawCategories.filter(c => c.name.toLowerCase().includes(q));
            
            let h = '<div class="p-3 text-[10px] text-slate-400 font-extrabold uppercase tracking-widest sticky top-0 bg-white/90 backdrop-blur border-b border-slate-100">Select category</div>';
            h += f.length === 0 ? '<div class="px-4 py-3 text-sm text-slate-400 italic">No categories found</div>'
                : f.map(c => {
                    if (usedCategories.includes(c.id)) {
                        return `<div class="px-4 py-3 text-sm font-semibold text-slate-300 bg-slate-50 border-b border-slate-50 cursor-not-allowed line-through" title="Already selected for this school in another tab">${c.name}</div>`;
                    }
                    return `<div onclick="selectTabCat(${tabId}, ${c.id}, '${c.name.replace(/'/g,"\\'")}')" class="px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-red-50 hover:text-[#c00000] cursor-pointer transition-colors border-b border-slate-50 last:border-0">${c.name}</div>`;
                }).join('');
            dd.innerHTML = h;
        }

        function selectTabCat(tabId, catId, catName) {
            const tab = distTabsData[tabId];
            tab.category_id = catId;
            tab.item_id = null;
            tab.subItemsSelected = [];

            document.getElementById(`tabCatSearch_${tabId}`).value = catName;
            document.getElementById(`tabCatDropdown_${tabId}`).classList.add('hidden');
            
            const itemSearch = document.getElementById(`tabItemSearch_${tabId}`);
            itemSearch.disabled = false;
            itemSearch.value = '';
            
            const subSearch = document.getElementById(`tabSubSearch_${tabId}`);
            subSearch.disabled = true;
            subSearch.value = '';
            
            document.getElementById(`tabSubContainer_${tabId}`).innerHTML = '';
            document.getElementById(`tabCatError_${tabId}`).classList.add('hidden');
            updateReadyStatus();
        }

        function filterTabItem(tabId) {
            const dd = document.getElementById(`tabItemDropdown_${tabId}`);
            const q = document.getElementById(`tabItemSearch_${tabId}`).value.trim().toLowerCase();
            const catId = distTabsData[tabId].category_id;
            dd.classList.remove('hidden');

            const pool = catId ? rawItems.filter(i => i.category_id == catId) : rawItems;
            const f = pool.filter(i => i.name.toLowerCase().includes(q)).slice(0, 50);

            let h = '<div class="p-3 text-[10px] text-slate-400 font-extrabold uppercase tracking-widest sticky top-0 bg-white/90 backdrop-blur border-b border-slate-100">Select item</div>';
            h += f.length === 0 ? '<div class="px-4 py-3 text-sm text-slate-400 italic">No items found</div>'
                : f.map(i => `<div onclick="selectTabItem(${tabId}, ${i.id}, '${i.name.replace(/'/g,"\\'")}')" class="px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 cursor-pointer transition-colors border-b border-slate-50 last:border-0">${i.name}</div>`).join('');
            dd.innerHTML = h;
        }

        function selectTabItem(tabId, itemId, itemName) {
            const tab = distTabsData[tabId];
            tab.item_id = itemId;
            tab.subItemsSelected = [];

            document.getElementById(`tabItemSearch_${tabId}`).value = itemName;
            document.getElementById(`tabItemDropdown_${tabId}`).classList.add('hidden');
            
            const subSearch = document.getElementById(`tabSubSearch_${tabId}`);
            subSearch.disabled = false;
            subSearch.value = '';
            
            document.getElementById(`tabSubContainer_${tabId}`).innerHTML = '';
            updateReadyStatus();
        }

        // Calculate effective remaining stock for a sub-item across ALL tabs
        function getEffectiveStock(subId) {
            if (distTabsData.length === 0) return 0;
            const distributorId = distTabsData[0].distributor_id;
            
            let initialStock = 0;
            const sysWarehouse = rawStakeholders.find(s => s.type === 'System' && s.name.includes('Warehouse'));
            if (sysWarehouse && distributorId === sysWarehouse.id) {
                const raw = rawSubItems.find(s => s.id === subId);
                initialStock = raw ? raw.quantity : 0;
            } else {
                const owns = rawOwnerships[distributorId] || [];
                initialStock = owns.filter(o => o.sub_item_id === subId).reduce((sum, o) => sum + o.quantity, 0);
            }

            let totalAllocated = 0;
            distTabsData.forEach(tab => {
                tab.subItemsSelected.forEach(si => {
                    if (si.id === subId && si.selected_qty > 0) {
                        totalAllocated += si.selected_qty;
                    }
                });
            });
            return Math.max(0, initialStock - totalAllocated);
        }

        function filterTabSub(tabId) {
            const dd = document.getElementById(`tabSubDropdown_${tabId}`);
            const q = document.getElementById(`tabSubSearch_${tabId}`).value.trim().toLowerCase();
            const itemId = distTabsData[tabId].item_id;
            if(!itemId) return;

            dd.classList.remove('hidden');
            const pool = rawSubItems.filter(s => s.item_id == itemId);
            const selectedIds = distTabsData[tabId].subItemsSelected.map(s => s.id);
            const f = pool.filter(s => !selectedIds.includes(s.id) && s.name.toLowerCase().includes(q)).slice(0, 50);

            let h = '<div class="p-3 text-[10px] text-slate-400 font-extrabold uppercase tracking-widest sticky top-0 bg-white/90 backdrop-blur border-b border-slate-100">Select sub-item</div>';
            h += f.length === 0 ? '<div class="px-4 py-3 text-sm text-slate-400 italic">No sub-items available</div>'
                : f.map(s => {
                    const stock = getEffectiveStock(s.id);
                    if(stock <= 0) {
                        return `<div class="px-4 py-3 text-sm font-semibold text-slate-300 bg-slate-50 cursor-not-allowed border-b border-slate-50">${s.name} <span class="text-red-400 text-xs">(Out of stock)</span></div>`;
                    }
                    return `<div onclick="selectTabSub(${tabId}, ${s.id}, '${s.name.replace(/'/g,"\\'")}', ${stock})" class="px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-blue-50 hover:text-blue-700 cursor-pointer transition-colors border-b border-slate-50 flex justify-between"><span>${s.name}</span> <span class="text-xs font-black text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-xl">${stock} available</span></div>`;
                }).join('');
            dd.innerHTML = h;
        }

        function selectTabSub(tabId, subId, subName, availableQty) {
            const tab = distTabsData[tabId];
            tab.subItemsSelected.push({ id: subId, name: subName, available_qty: availableQty, selected_qty: 0 });
            
            document.getElementById(`tabSubSearch_${tabId}`).value = '';
            document.getElementById(`tabSubDropdown_${tabId}`).classList.add('hidden');
            renderTabSubItems(tabId);
            updateReadyStatus();
        }

        function removeTabSub(tabId, subId) {
            const tab = distTabsData[tabId];
            tab.subItemsSelected = tab.subItemsSelected.filter(s => s.id !== subId);
            renderTabSubItems(tabId);
            // Refresh all other tabs that show the same sub-item (stock freed up)
            refreshAllTabsForSubItem(subId, tabId);
            updateReadyStatus();
        }

        function updateTabSubQty(tabId, subId, valStr) {
            const tab = distTabsData[tabId];
            const sub = tab.subItemsSelected.find(s => s.id === subId);
            if (!sub) return;
            
            let val = parseInt(valStr);
            if(isNaN(val) || val < 0) val = 0;
            sub.selected_qty = val;

            // Recalculate effective stock for this sub-item across all tabs
            const effectiveStock = getEffectiveStock(subId);
            // The available amount for THIS specific entry = effectiveStock + this entry's own qty
            const maxForThis = effectiveStock + val;
            
            const input = document.getElementById(`subQtyInput_${tabId}_${subId}`);
            const errorLabel = document.getElementById(`subQtyError_${tabId}_${subId}`);
            const stockLabel = document.getElementById(`subStockLabel_${tabId}_${subId}`);
            
            // Update the stock display for this card
            if (stockLabel) {
                const remaining = maxForThis - val;
                stockLabel.textContent = `${remaining} AVAILABLE`;
                stockLabel.classList.toggle('text-emerald-600', remaining > 0);
                stockLabel.classList.toggle('text-red-500', remaining <= 0);
            }

            if (val > maxForThis || val <= 0) {
                if (val > maxForThis) {
                    errorLabel.textContent = `Exceeds available stock (${maxForThis})!`;
                } else {
                    errorLabel.textContent = `Enter a quantity ≥ 1`;
                }
                errorLabel.classList.remove('hidden');
                input.classList.add('border-red-400', 'bg-red-50', 'text-red-600');
            } else {
                errorLabel.classList.add('hidden');
                input.classList.remove('border-red-400', 'bg-red-50', 'text-red-600');
            }

            // Update available_qty to reflect effective stock for validation
            sub.available_qty = maxForThis;

            // Refresh stock display in all OTHER tabs that have the same sub-item
            refreshAllTabsForSubItem(subId, tabId);
            updateReadyStatus();
        }

        // Refresh the stock label and validation for a sub-item across all tabs except excludeTab
        function refreshAllTabsForSubItem(subId, excludeTabId) {
            distTabsData.forEach((tab, i) => {
                if (i === excludeTabId) return;
                const si = tab.subItemsSelected.find(s => s.id === subId);
                if (!si) return;

                const effectiveStock = getEffectiveStock(subId);
                const maxForThis = effectiveStock + si.selected_qty;
                si.available_qty = maxForThis;

                const stockLabel = document.getElementById(`subStockLabel_${i}_${subId}`);
                const input = document.getElementById(`subQtyInput_${i}_${subId}`);
                const errorLabel = document.getElementById(`subQtyError_${i}_${subId}`);

                if (stockLabel) {
                    const remaining = maxForThis - si.selected_qty;
                    stockLabel.textContent = `${remaining} AVAILABLE`;
                    stockLabel.classList.toggle('text-emerald-600', remaining > 0);
                    stockLabel.classList.toggle('text-red-500', remaining <= 0);
                }

                // Re-validate
                if (input && errorLabel) {
                    if (si.selected_qty > maxForThis) {
                        errorLabel.textContent = `Exceeds available stock (${maxForThis})!`;
                        errorLabel.classList.remove('hidden');
                        input.classList.add('border-red-400', 'bg-red-50', 'text-red-600');
                    } else if (si.selected_qty > 0) {
                        errorLabel.classList.add('hidden');
                        input.classList.remove('border-red-400', 'bg-red-50', 'text-red-600');
                    }
                }
            });
        }

        function renderTabSubItems(tabId) {
            const tab = distTabsData[tabId];
            const container = document.getElementById(`tabSubContainer_${tabId}`);
            if (tab.subItemsSelected.length === 0) {
                container.innerHTML = '';
                return;
            }
            container.innerHTML = tab.subItemsSelected.map(si => {
                const effectiveStock = getEffectiveStock(si.id);
                const remaining = effectiveStock + si.selected_qty - si.selected_qty;
                const displayStock = effectiveStock;
                const maxForThis = effectiveStock + si.selected_qty;
                si.available_qty = maxForThis;

                return `
                <div class="flex items-center gap-3 p-4 bg-white border border-slate-200 shadow-sm rounded-2xl animate-in fade-in slide-in-from-top-2 duration-300">
                    <div class="flex-grow flex flex-col">
                        <span class="text-sm font-bold text-slate-800">${si.name}</span>
                        <span id="subStockLabel_${tabId}_${si.id}" class="text-[10px] font-black uppercase tracking-widest mt-1 ${displayStock > 0 ? 'text-emerald-600' : 'text-red-500'}">${displayStock} AVAILABLE</span>
                    </div>
                    <div class="flex flex-col items-center gap-1">
                        <input type="number" id="subQtyInput_${tabId}_${si.id}" min="1" max="${maxForThis}" placeholder="Qty" value="${si.selected_qty || ''}" oninput="updateTabSubQty(${tabId}, ${si.id}, this.value)" class="w-24 p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none font-black text-sm text-center focus:border-blue-400 focus:ring-4 focus:ring-blue-50 transition-all">
                        <span id="subQtyError_${tabId}_${si.id}" class="hidden text-[10px] font-bold text-red-500 text-center"></span>
                    </div>
                    <button type="button" onclick="removeTabSub(${tabId}, ${si.id})" class="text-slate-300 hover:text-red-500 hover:bg-red-50 p-2 rounded-xl transition-colors font-bold text-lg shrink-0">✕</button>
                </div>`;
            }).join('');
        }

        function updateReadyStatus() {
            let totalQty = 0;
            distTabsData.forEach(tab => {
                tab.subItemsSelected.forEach(sub => {
                    if (sub.selected_qty > 0 && sub.selected_qty <= sub.available_qty) {
                        totalQty += sub.selected_qty;
                    }
                });
            });
            document.getElementById('tabStatusCount').textContent = `${totalQty} Assets Ready`;
            
            const btn = document.getElementById('distributeAllBtn');
            if (totalQty > 0) {
                btn.classList.add('bg-[#c00000]', 'hover:bg-red-700');
                btn.classList.remove('bg-slate-300', 'cursor-not-allowed', 'opacity-50');
            } else {
                btn.classList.remove('bg-[#c00000]', 'hover:bg-red-700');
                btn.classList.add('bg-slate-300', 'cursor-not-allowed', 'opacity-50');
            }
        }

        function validatePayloadForTab(i) {
            const tab = distTabsData[i];
            let errors = [];
            let payload = null;

            if(!tab.item_id) { errors.push(`Tab ${i+1} (${tab.recipient_name}) is missing an Item.`); return { errors, payload }; }
            if(tab.subItemsSelected.length === 0) { errors.push(`Tab ${i+1} (${tab.recipient_name}) has no selected sub-items.`); return { errors, payload }; }
            
            let tabValid = true;
            let subItemsPayload = [];
            tab.subItemsSelected.forEach(sub => {
                if (sub.selected_qty <= 0) {
                    errors.push(`Tab ${i+1}: Sub-item "${sub.name}" needs a quantity greater than 0.`);
                    tabValid = false;
                } else if (sub.selected_qty > sub.available_qty) {
                    errors.push(`Tab ${i+1}: Sub-item "${sub.name}" requested (${sub.selected_qty}) exceeds stock (${sub.available_qty}).`);
                    tabValid = false;
                } else {
                    subItemsPayload.push({ id: sub.id, qty: sub.selected_qty });
                }
            });

            if(tabValid) {
                payload = {
                    tab_id: `tab_${i}`,
                    distributor_id: tab.distributor_id,
                    recipient_id: tab.recipient_id,
                    item_id: tab.item_id,
                    sub_items: subItemsPayload
                };
            }
            return { errors, payload };
        }

        function confirmDistributeSingleTab(tabIndex) {
            let result = validatePayloadForTab(tabIndex);
            if (result.errors.length > 0) {
                Swal.fire({ title: 'Validation Error', html: `<div class="text-left text-sm text-slate-600 block leading-relaxed space-y-2 font-medium">` + result.errors.map(e => `<div><span class="text-red-500 font-bold mr-2">•</span>${e}</div>`).join('') + `</div>`, icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                return;
            }

            const total = result.payload.sub_items.reduce((s, x) => s + x.qty, 0);

            Swal.fire({
                title: 'Confirm Distribution', 
                html: `<div class="text-sm text-slate-500 mt-2 font-medium leading-relaxed">Distribute <span class="font-black text-rose-600 text-lg mx-1">${total}</span> asset(s) to <span class="font-black text-slate-800 mx-1">${distTabsData[tabIndex].recipient_name}</span>?</div>`, 
                icon: 'question',
                showCancelButton: true, confirmButtonColor: '#c00000', cancelButtonColor: '#94a3b8', confirmButtonText: 'Yes, distribute!',
                customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6', cancelButton: 'rounded-xl font-bold px-6' }
            }).then((res) => { 
                if (res.isConfirmed) submitDistributionPayload([result.payload], tabIndex);
            });
        }

        function confirmDistributeAll() {
            let payload = [];
            let totalQty = 0;
            let errors = [];

            distTabsData.forEach((tab, i) => {
                if(!tab.item_id && tab.subItemsSelected.length === 0 && !tab.category_id) {
                    return; // Ignore completely untouched tabs
                }
                const res = validatePayloadForTab(i);
                if (res.errors.length > 0) {
                    errors = errors.concat(res.errors);
                } else if (res.payload) {
                    payload.push(res.payload);
                    totalQty += res.payload.sub_items.reduce((s, x) => s + x.qty, 0);
                }
            });

            if (errors.length > 0) {
                Swal.fire({ title: 'Validation Error', html: `<div class="text-left text-sm text-slate-600 block leading-relaxed space-y-2 font-medium">` + errors.map(e => `<div><span class="text-red-500 font-bold mr-2">•</span>${e}</div>`).join('') + `</div>`, icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                return;
            }

            if (payload.length === 0) {
                Swal.fire({ title: 'Nothing to distribute', text: 'Please fill out at least one tab completely.', icon: 'info', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                return;
            }

            Swal.fire({
                title: 'Confirm Batched Distribution', 
                html: `<div class="text-sm text-slate-500 mt-2 font-medium leading-relaxed">You are about to distribute a total of <span class="font-black text-rose-600 text-lg mx-1">${totalQty}</span> asset(s) across <span class="font-black text-rose-600 text-lg mx-1">${payload.length}</span> tab(s).<br><br>This will permanently deduct the literal quantities from the Master Stock. Proceed?</div>`, 
                icon: 'question',
                showCancelButton: true, confirmButtonColor: '#c00000', cancelButtonColor: '#94a3b8', confirmButtonText: 'Yes, distribute all!',
                customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6', cancelButton: 'rounded-xl font-bold px-6' }
            }).then((res) => { 
                if (res.isConfirmed) submitDistributionPayload(payload);
            });
        }

        async function submitDistributionPayload(payload, completedTabIndex) {
            Swal.fire({
                title: 'Distributing...', text: 'Updating ledgers and deducting stock...',
                allowOutsideClick: false, showConfirmButton: false, willOpen: () => { Swal.showLoading(); },
                customClass: { popup: 'rounded-[2rem]' }
            });

            try {
                const response = await fetch("{{ route('inventory.setup.distribution') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ distributions: payload })
                });
                const result = await response.json();
                
                if(response.ok) {
                    Swal.fire({ title: 'Success!', text: result.message, icon: 'success', confirmButtonColor: '#10b981', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } })
                    .then(() => { location.reload(); });

                } else {
                    Swal.fire({ title: 'Error', text: result.message || 'An error occurred during distribution.', icon: 'error', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                }
            } catch(e) {
                Swal.fire({ title: 'Submission Failed', text: e.message, icon: 'error', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
            }
        }

        // =============================================
        // RENAME MODULE — Update Items logic
        // =============================================
        let renameTargetId   = null;
        let renameTargetType = null;
        let renameTargetName = null;

        function onRenameTypeChange() {
            const type = document.getElementById('renameType').value;
            renameTargetId   = null;
            renameTargetType = type || null;
            renameTargetName = null;

            const cascadeRow  = document.getElementById('renameCascadeRow');
            const catWrap     = document.getElementById('renameCatWrap');
            const itemWrap    = document.getElementById('renameItemWrap');
            const subWrap     = document.getElementById('renameSubWrap');
            const inputWrap   = document.getElementById('renameInputWrap');
            const submitBtn   = document.getElementById('renameSubmitBtn');

            // Reset all child selects
            document.getElementById('renameCatSelect').value  = '';
            document.getElementById('renameItemSelect').innerHTML = '<option value="">-- Choose Item --</option>';
            document.getElementById('renameSubSelect').innerHTML  = '<option value="">-- Choose Sub-Item --</option>';
            document.getElementById('renameNewName').value     = '';
            document.getElementById('renameCurrentHint').textContent = '';

            if (typeof hideActionUI === 'function') hideActionUI();

            if (!type) {
                cascadeRow.classList.add('hidden');
                catWrap.classList.add('hidden');
                itemWrap.classList.add('hidden');
                subWrap.classList.add('hidden');
                return;
            }

            cascadeRow.classList.remove('hidden');
            // Category dropdown always shows
            catWrap.classList.remove('hidden');
            // Item dropdown shows for 'item' and 'sub_item'
            itemWrap.classList.toggle('hidden', type === 'category');
            // Sub-item dropdown shows only for 'sub_item'
            subWrap.classList.toggle('hidden', type !== 'sub_item');
        }

        function onRenameCatChange() {
            const type       = document.getElementById('renameType').value;
            const catSelect  = document.getElementById('renameCatSelect');
            const catId      = catSelect.value;
            const catName    = catId ? catSelect.options[catSelect.selectedIndex].text : '';

            const itemSelect = document.getElementById('renameItemSelect');
            const subSelect  = document.getElementById('renameSubSelect');

            renameTargetId   = null;
            renameTargetName = null;

            // Reset dependent dropdowns
            itemSelect.innerHTML = '<option value="">-- Choose Item --</option>';
            subSelect.innerHTML  = '<option value="">-- Choose Sub-Item --</option>';
            document.getElementById('renameNewName').value = '';
            document.getElementById('renameCurrentHint').textContent = '';
            if (typeof hideActionUI === 'function') hideActionUI();

            if (type === 'category') {
                // Selecting a category IS the target
                if (catId) {
                    renameTargetId   = parseInt(catId);
                    renameTargetType = 'category';
                    renameTargetName = catName;
                    document.getElementById('renameCurrentHint').textContent = `Currently named: "${catName}"`;
                    if (typeof triggerActionUI === 'function') triggerActionUI();
                }
                return;
            }

            // For item & sub_item: populate items filtered by chosen category
            if (!catId) return;
            const items = rawItems.filter(i => i.category_id == catId);
            itemSelect.innerHTML = '<option value="">-- Choose Item --</option>';
            items.forEach(i => {
                itemSelect.innerHTML += `<option value="${i.id}" data-name="${i.name.replace(/"/g,'&quot;')}">${i.name}</option>`;
            });
        }

        function onRenameItemChange() {
            const type      = document.getElementById('renameType').value;
            const itemSel   = document.getElementById('renameItemSelect');
            const itemId    = itemSel.value;
            const itemName  = itemId ? itemSel.options[itemSel.selectedIndex].text : '';
            const subSelect = document.getElementById('renameSubSelect');

            renameTargetId   = null;
            renameTargetName = null;

            subSelect.innerHTML = '<option value="">-- Choose Sub-Item --</option>';
            document.getElementById('renameNewName').value = '';
            document.getElementById('renameCurrentHint').textContent = '';
            if (typeof hideActionUI === 'function') hideActionUI();

            if (type === 'item') {
                if (itemId) {
                    renameTargetId   = parseInt(itemId);
                    renameTargetType = 'item';
                    renameTargetName = itemName;
                    document.getElementById('renameCurrentHint').textContent = `Currently named: "${itemName}"`;
                    if (typeof triggerActionUI === 'function') triggerActionUI();
                }
                return;
            }

            // For sub_item: populate sub-items filtered by chosen item
            if (!itemId) return;
            const subs = rawSubItems.filter(s => s.item_id == itemId);
            subSelect.innerHTML = '<option value="">-- Choose Sub-Item --</option>';
            subs.forEach(s => {
                subSelect.innerHTML += `<option value="${s.id}">${s.name}</option>`;
            });
        }

        function onRenameSubChange() {
            const subSel   = document.getElementById('renameSubSelect');
            const subId    = subSel.value;
            const subName  = subId ? subSel.options[subSel.selectedIndex].text : '';

            renameTargetId   = null;
            renameTargetName = null;

            document.getElementById('renameNewName').value = '';
            document.getElementById('renameCurrentHint').textContent = '';
            if (typeof hideActionUI === 'function') hideActionUI();

            if (subId) {
                renameTargetId   = parseInt(subId);
                renameTargetType = 'sub_item';
                renameTargetName = subName;
                document.getElementById('renameCurrentHint').textContent = `Currently named: "${subName}"`;
                if (typeof triggerActionUI === 'function') triggerActionUI();
            }
        }

        async function submitRename() {
            const newName = document.getElementById('renameNewName').value.trim();
            if (!renameTargetId || !renameTargetType) {
                Swal.fire({ title: 'Nothing Selected', text: 'Please complete all selections before renaming.', icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                return;
            }
            if (!newName) {
                Swal.fire({ title: 'New Name Required', text: 'Please enter a new name.', icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                return;
            }
            if (newName.toLowerCase() === renameTargetName.toLowerCase()) {
                Swal.fire({ title: 'No Change Detected', text: 'The new name is the same as the current name.', icon: 'info', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                return;
            }

            const typeLabel = renameTargetType === 'sub_item' ? 'Sub-Item' : (renameTargetType.charAt(0).toUpperCase() + renameTargetType.slice(1));
            const result = await Swal.fire({
                title: `Rename ${typeLabel}`,
                html: `Rename <b>"${renameTargetName}"</b> to <b>"${newName}"</b>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#c00000', cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, rename it!',
                customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6', cancelButton: 'rounded-xl font-bold px-6' }
            });

            if (!result.isConfirmed) return;

            Swal.fire({ title: 'Renaming...', allowOutsideClick: false, showConfirmButton: false, willOpen: () => Swal.showLoading(), customClass: { popup: 'rounded-[2rem]' } });

            try {
                const res = await fetch("{{ route('inventory.setup.rename') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ type: renameTargetType, id: renameTargetId, new_name: newName })
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    // Update local raw data so the dropdowns reflect the change immediately
                    if (renameTargetType === 'category') {
                        const cat = rawCategories.find(c => c.id === renameTargetId);
                        if (cat) cat.name = newName;
                    } else if (renameTargetType === 'item') {
                        const item = rawItems.find(i => i.id === renameTargetId);
                        if (item) item.name = newName;
                    } else if (renameTargetType === 'sub_item') {
                        const sub = rawSubItems.find(s => s.id === renameTargetId);
                        if (sub) sub.name = newName;
                    }
                    Swal.fire({ title: 'Success!', text: data.message, icon: 'success', confirmButtonColor: '#10b981', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } })
                    .then(() => {
                        // Reset the rename form
                        document.getElementById('renameType').value = '';
                        onRenameTypeChange();
                    });
                } else {
                    Swal.fire({ title: 'Error', text: data.message || 'An error occurred.', icon: 'error', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                }
            } catch(e) {
                Swal.fire({ title: 'Request Failed', text: e.message, icon: 'error', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
            }
        }

        // ===== EDIT MODE (Update/Delete) ACTIONS =====
        let currentEditAction = 'update';

        function setEditAction(action) {
            currentEditAction = action;
            const updateBtn = document.getElementById('editModeUpdateBtn');
            const deleteBtn = document.getElementById('editModeDeleteBtn');
            const inputWrap = document.getElementById('renameInputWrap');
            const warnWrap  = document.getElementById('deleteWarningWrap');
            const rnSubmit  = document.getElementById('renameSubmitBtn');
            const delSubmit = document.getElementById('deleteSubmitBtn');

            if (action === 'update') {
                updateBtn.className = 'flex-1 py-4 rounded-2xl font-bold text-sm transition-all border-2 border-[#c00000] bg-red-50 text-[#c00000]';
                deleteBtn.className = 'flex-1 py-4 rounded-2xl font-bold text-sm transition-all border-2 border-slate-200 bg-white text-slate-400 hover:border-red-300 hover:text-red-400';
                if (renameTargetId) {
                    inputWrap.classList.remove('hidden');
                    rnSubmit.classList.remove('hidden');
                }
                warnWrap.classList.add('hidden');
                delSubmit.classList.add('hidden');
            } else {
                updateBtn.className = 'flex-1 py-4 rounded-2xl font-bold text-sm transition-all border-2 border-slate-200 bg-white text-slate-400 hover:border-red-300 hover:text-red-400';
                deleteBtn.className = 'flex-1 py-4 rounded-2xl font-bold text-sm transition-all border-2 border-red-600 bg-red-50 text-red-600';
                inputWrap.classList.add('hidden');
                rnSubmit.classList.add('hidden');
                if (renameTargetId) {
                    warnWrap.classList.remove('hidden');
                    delSubmit.classList.remove('hidden');
                    previewDeleteImpact();
                }
            }
        }

        function triggerActionUI() {
            if (currentEditAction === 'update') {
                document.getElementById('renameInputWrap').classList.remove('hidden');
                document.getElementById('renameSubmitBtn').classList.remove('hidden');
                document.getElementById('deleteWarningWrap').classList.add('hidden');
                document.getElementById('deleteSubmitBtn').classList.add('hidden');
            } else {
                document.getElementById('renameInputWrap').classList.add('hidden');
                document.getElementById('renameSubmitBtn').classList.add('hidden');
                document.getElementById('deleteWarningWrap').classList.remove('hidden');
                document.getElementById('deleteCurrentHint').textContent = `Record to delete: "${renameTargetName}"`;
                document.getElementById('deleteSubmitBtn').classList.remove('hidden');
                previewDeleteImpact();
            }
        }

        function hideActionUI() {
            document.getElementById('renameInputWrap').classList.add('hidden');
            document.getElementById('renameSubmitBtn').classList.add('hidden');
            document.getElementById('deleteWarningWrap').classList.add('hidden');
            document.getElementById('deleteSubmitBtn').classList.add('hidden');
        }

        async function previewDeleteImpact() {
            const impactBox = document.getElementById('deleteImpactBox');
            const impactTxt = document.getElementById('deleteImpactDetails');
            
            impactBox.classList.remove('hidden');
            impactTxt.innerHTML = '<span class="text-slate-500 animate-pulse">Calculating impact...</span>';
            
            try {
                const res = await fetch("{{ route('inventory.setup.preview_delete') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ type: renameTargetType, id: renameTargetId })
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    const i = data.impact;
                    let msgs = [];
                    if (renameTargetType === 'category' && i.items > 0) msgs.push(`• <b>${i.items}</b> associated Item(s)`);
                    if (['category', 'item'].includes(renameTargetType) && i.sub_items > 0) msgs.push(`• <b>${i.sub_items}</b> Sub-Item(s) specification(s)`);
                    if (i.total_stock > 0) msgs.push(`• <b>${i.total_stock}</b> items in master stock`);
                    if (i.ownerships > 0) msgs.push(`• <b>${i.ownerships}</b> distributed physical asset(s) across <b>${i.schools_affected}</b> school(s)`);
                    
                    if (msgs.length === 0) {
                        impactTxt.innerHTML = '<span class="text-emerald-600 font-bold">Safe to delete: No associated records or stock will be affected.</span>';
                        impactBox.classList.replace('bg-red-50', 'bg-emerald-50');
                        impactBox.classList.replace('border-red-200', 'border-emerald-200');
                    } else {
                        impactTxt.innerHTML = 'This action will instantly delete the record AND cascade to permanently destroy:<br>' + msgs.join('<br>');
                        impactBox.classList.replace('bg-emerald-50', 'bg-red-50');
                        impactBox.classList.replace('border-emerald-200', 'border-red-200');
                    }
                } else {
                    impactTxt.innerHTML = '<span class="text-slate-500">Failed to calculate impact.</span>';
                }
            } catch (e) {
                impactTxt.innerHTML = '<span class="text-slate-500">Failed to calculate impact.</span>';
            }
        }

        async function submitDelete() {
            if (!renameTargetId || !renameTargetType) return;
            
            const typeLabel = renameTargetType === 'sub_item' ? 'Sub-Item' : (renameTargetType.charAt(0).toUpperCase() + renameTargetType.slice(1));
            const result = await Swal.fire({
                title: 'CRITICAL DELETION',
                html: `Are you absolutely sure you want to permanently delete the ${typeLabel} <b>"${renameTargetName}"</b>?<br><br><span class="text-red-600 font-bold text-sm">This action cannot be undone and will destroy any associated physical inventory records!</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#c00000', cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, permanently destroy it',
                cancelButtonText: 'Cancel',
                customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6', cancelButton: 'rounded-xl font-bold px-6' }
            });

            if (!result.isConfirmed) return;

            Swal.fire({ title: 'Destroying Records...', allowOutsideClick: false, showConfirmButton: false, willOpen: () => Swal.showLoading(), customClass: { popup: 'rounded-[2rem]' } });

            try {
                const res = await fetch("{{ route('inventory.setup.delete') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ type: renameTargetType, id: renameTargetId })
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    Swal.fire({ title: 'Deleted!', text: data.message, icon: 'success', confirmButtonColor: '#10b981', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } })
                    .then(() => { location.reload(); }); // Reload to refresh all related dropdowns and UI
                } else {
                    Swal.fire({ title: 'Error', text: data.message || 'An error occurred.', icon: 'error', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                }
            } catch(e) {
                Swal.fire({ title: 'Request Failed', text: e.message, icon: 'error', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
            }
        }

        // Close dropdowns on outside click handler
        document.addEventListener('click', function(e) {
            if(!document.getElementById('preDistSchoolDropdownList')?.classList.contains('hidden')) {
                const el = document.getElementById('preDistSchoolSearch');
                const dd = document.getElementById('preDistSchoolDropdownList');
                if(el && dd && !el.contains(e.target) && !dd.contains(e.target)) dd.classList.add('hidden');
            }
            
            distTabsData.forEach((_, i) => {
                ['Cat','Item','Sub'].forEach(type => {
                    const search = document.getElementById(`tab${type}Search_${i}`);
                    const dd = document.getElementById(`tab${type}Dropdown_${i}`);
                    if (search && dd && !dd.classList.contains('hidden') && !search.contains(e.target) && !dd.contains(e.target)) {
                        dd.classList.add('hidden');
                    }
                });
            });
            
            [['itemDropdownList','itemDropdownBtn','itemName'],['categoryDropdownList','categoryDropdownBtn','categoryName'],
             ['itemCategoryDropdownList','itemCategoryDropdownBtn','itemCategoryName']
            ].forEach(([ddId, btnId, inputId]) => {
                const dd = document.getElementById(ddId), btn = document.getElementById(btnId), inp = document.getElementById(inputId);
                if (dd && btn && !dd.contains(e.target) && !btn.contains(e.target) && e.target !== inp && e.target.id !== inputId) dd.classList.add('hidden');
            });
        });
    </script>
</body>
</html>
