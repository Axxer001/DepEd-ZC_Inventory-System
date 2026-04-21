<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Setup | DepEd Zamboanga City</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
<div id="step2" class="step-content">
    <h3 id="step2Title" class="text-lg font-black text-slate-400 uppercase tracking-[0.3em] text-center mb-6 -mt-6">Select Category</h3>
    
<div class="grid grid-cols-2 gap-6 max-w-3xl mx-auto px-4 mb-8">        
        {{-- Schools --}}
        <div onclick="nextStep(3, 'school')" class="bg-white p-8 w-full rounded-[2.5rem] shadow-xl border border-slate-100 hover:border-[#c00000] hover:-translate-y-2 transition-all cursor-pointer group text-center flex flex-col items-center justify-center">
            <div class="w-16 h-16 bg-red-50 text-[#c00000] rounded-[1.5rem] flex items-center justify-center mb-4 group-hover:rotate-12 transition-transform shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A4.833 4.833 0 0012 9a4.833 4.833 0 00-7.5 1.332V21m15 0h-15" /></svg>
            </div>
            <span class="block font-black text-slate-800 uppercase text-xs tracking-widest">Schools</span>
            <p class="category-subtext text-[11px] text-slate-400 uppercase font-bold mt-2 tracking-tight leading-tight opacity-80" data-add="Register new school profiles" data-edit="Modify existing school records"></p>
        </div>
        
        {{-- Inventory Items --}}
        <div onclick="nextStep(3, 'item')" class="bg-white p-8 w-full rounded-[2.5rem] shadow-xl border border-slate-100 hover:border-[#c00000] hover:-translate-y-2 transition-all cursor-pointer group text-center flex flex-col items-center justify-center">
            <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-[1.5rem] flex items-center justify-center mb-4 group-hover:rotate-12 transition-transform shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>
            </div>
            <span class="block font-black text-slate-800 uppercase text-xs tracking-widest">Inventory Items</span>
            <p class="category-subtext text-[11px] text-slate-400 uppercase font-bold mt-2 tracking-tight leading-tight opacity-80" data-add="Register new supply items" data-edit="Edit item specifications"></p>
        </div>

        {{-- Asset Distribution --}}
        <div onclick="nextStep(3, 'distribution')" class="bg-white p-8 w-full rounded-[2.5rem] shadow-xl border border-slate-100 hover:border-[#c00000] hover:-translate-y-2 transition-all cursor-pointer group text-center flex flex-col items-center justify-center">
            <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-[1.5rem] flex items-center justify-center mb-4 group-hover:rotate-12 transition-transform shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-10.5v.008H15V4.5zm0 9v.008H15V13.5zm0-4.5v.008H15V9zm0-4.5v.008H15V4.5zM9 15l-3 1.5L3 15V5.25l3-1.5L9 5.25M9 15l3.047-1.524c.499-.25 1.096-.217 1.565.083L17.25 15l3-1.5V4.5l-3 1.5-3.638-2.046c-.469-.264-1.025-.264-1.494 0L9 5.25" /></svg>
            </div>
            <span class="block font-black text-slate-800 uppercase text-xs tracking-widest">Asset Distribution</span>
            <p class="category-subtext text-[11px] text-slate-400 uppercase font-bold mt-2 tracking-tight leading-tight opacity-80" data-add="Record new asset deployment" data-edit="Modify deployment history"></p>
        </div>

        {{-- Stakeholders --}}
<div onclick= "window.location.href='/stakeholders'" class="bg-white p-8 w-full rounded-[2.5rem] shadow-xl border border-slate-100 hover:border-[#c00000] hover:-translate-y-2 transition-all cursor-pointer group text-center flex flex-col items-center justify-center">
    <div class="w-16 h-16 bg-purple-50 text-purple-600 rounded-[1.5rem] flex items-center justify-center mb-4 group-hover:rotate-12 transition-transform shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-8 h-8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.998 5.998 0 00-4.03-5.754m-4.44 1.158A7.012 7.012 0 005.432 19m12.738-1.486a9.03 9.03 0 00-4.682-2.72m0 0a4.5 4.5 0 11-8.962 0m8.962 0a4.5 4.5 0 00-8.962 0m6.5 2.25l-3-3m3 3l3-3" />
        </svg>
    </div>
    <span class="block font-black text-slate-800 uppercase text-xs tracking-widest">Stakeholders</span>
    <p class="category-subtext text-[11px] text-slate-400 uppercase font-bold mt-2 tracking-tight leading-tight opacity-80" 
       data-add="Register new stakeholder profiles" 
       data-edit="Update stakeholder information">
    </p>
</div>

    </div>
</div>
            {{-- Step 3: Form Content --}}
            <div id="step3" class="step-content">
                @if($errors->any())
                    <div class="max-w-4xl mx-auto mb-6 bg-red-50 text-red-600 p-6 font-bold rounded-3xl shadow-sm border border-red-100 flex items-start gap-4">
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

                <div class="max-w-4xl mx-auto bg-white p-10 rounded-[3rem] shadow-2xl border border-slate-50 relative overflow-visible">
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

        document.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('step') === '2' && urlParams.get('mode') === 'edit') {
                nextStep(2, 'edit');
            }
            if (urlParams.get('step') === '2' && urlParams.get('mode') === 'add') {
                nextStep(2, 'add');
            }

            @if(session('success'))
                Swal.fire({
                    title: 'Registration Successful!',
                    text: @json(session('success')),
                    icon: 'success',
                    confirmButtonColor: '#10b981',
                    customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' }
                });
            @endif
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
        currentMode = value; // 'add' or 'edit'
        
        // Update the main header text of Step 2
        document.getElementById('step2Title').innerText = (value === 'add' ? 'ADD NEW' : 'EDIT') + ' RECORD';

        // Update all sub-texts dynamically based on the mode
        const subTexts = document.querySelectorAll('.category-subtext');
        subTexts.forEach(p => {
            if (currentMode === 'add') {
                p.innerText = p.getAttribute('data-add');
            } else {
                p.innerText = p.getAttribute('data-edit');
            }
        });


    }

    if (step === 3) {
        // Redirection logic for Edit mode
        if (currentMode === 'edit' && value === 'school') {
            window.location.href = '/inventory-modifier/school';
            return;
        }
        if (currentMode === 'edit' && value === 'distribution') {
            window.location.href = '/inventory-modifier';
            return;
        }

        // Redirect to dedicated Register Item page for Add > Inventory Items
        if (currentMode === 'add' && value === 'item') {
            window.location.href = '/register-item';
            return;
        }

        currentModule = value;
        renderForm();
    }

    // Navigation Logic
    document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
    document.getElementById('step' + step).classList.add('active');
    
    // Track history for the back button
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
                // ===== EDIT MODE: UPDATE / DELETE ITEMS (Static Design) =====
                html += `
                    <p class="text-slate-400 text-xs font-semibold mb-6 -mt-4 italic text-center">Update / Delete Item (Placeholder Design)</p>

                    <div class="flex gap-3 mb-6 opacity-60">
                        <div class="flex-1 py-4 rounded-2xl font-bold text-sm text-center border-2 border-slate-200 bg-white text-slate-400">✏️ Update / Rename</div>
                        <div class="flex-1 py-4 rounded-2xl font-bold text-sm text-center border-2 border-slate-200 bg-white text-slate-400">🗑️ Delete</div>
                    </div>

                    <div class="space-y-2 mb-6 opacity-60">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Record Type</label>
                        <div class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl font-semibold text-slate-400">Select record type</div>
                    </div>

                    <div class="p-8 border-2 border-dashed border-slate-100 rounded-[2rem] text-center">
                        <p class="text-slate-300 text-sm font-bold uppercase tracking-widest">Replacement Panel for Update/Delete</p>
                    </div>
                `;
            } else if (currentModule === 'item') {
                // ===== MODULE 1: MASTER REGISTRY (Inventory Items) — ADD MODE (Static Design) =====
                html += `
                    <div class="space-y-6">
                        <h4 class="text-2xl font-black text-slate-800 mb-8 uppercase tracking-tight italic">Register Item</h4>
                        
                        <div class="space-y-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Main Category <span class="text-red-500">*</span></label>
                                <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl font-semibold text-slate-400">e.g. Electronics</div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Item Name <span class="text-red-500">*</span></label>
                                <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl font-semibold text-slate-400">e.g. Smart TV</div>
                            </div>

                            <div class="space-y-4 pt-4 border-t border-slate-100">
                                <div class="flex flex-col">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Initial Specification (Sub-Item) <span class="text-red-500">*</span></label>
                                    <span class="text-[10px] text-slate-400 font-medium">Add spec & quantity. Required for initial stock.</span>
                                </div>

                                <div class="mt-4">
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 ml-1">Specification Details</label>
                                    <div class="space-y-3">
                                        <div class="flex flex-col gap-1 border border-slate-100 rounded-2xl p-3 bg-slate-50/50">
                                            <div class="flex flex-wrap gap-2 text-xs font-semibold text-slate-400 italic">
                                                <div class="w-40 p-3 bg-white border border-slate-100 rounded-xl">-- Distributor --</div>
                                                <div class="flex-grow min-w-[150px] p-3 bg-white border border-slate-100 rounded-xl">e.g. Default/General</div>
                                                <div class="w-20 p-3 bg-white border border-slate-100 rounded-xl text-center">Qty</div>
                                                <div class="w-34 p-3 bg-white border border-slate-100 rounded-xl">Serviceable</div>
                                            </div>
                                            <div class="flex flex-wrap gap-2 items-center">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">₱ Price</span>
                                                    <div class="w-28 p-2.5 bg-white border border-slate-100 rounded-xl text-[10px] text-slate-400 text-center">Unit price</div>
                                                </div>
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">📅 Acquired</span>
                                                    <div class="p-2.5 bg-white border border-slate-100 rounded-xl text-[10px] text-slate-400">mm/dd/yyyy</div>
                                                </div>
                                                <button type="button" class="ml-auto px-3 py-1.5 text-[10px] font-black bg-white border border-slate-200 text-slate-500 rounded-xl uppercase tracking-wider">⚙ Serial Info</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between bg-slate-50 border border-slate-100 p-4 rounded-2xl opacity-60">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-slate-700">⚡ Rapid Registration Mode</span>
                                    <span class="text-[10px] text-slate-400 font-medium tracking-wide">Save details to quickly scan and register the next item.</span>
                                </div>
                                <div class="w-11 h-6 bg-slate-200 rounded-full relative">
                                    <div class="absolute top-[2px] left-[2px] bg-white w-5 h-5 rounded-full border border-gray-300"></div>
                                </div>
                            </div>

                            <button type="button" class="w-full py-5 bg-[#c00000] text-white rounded-3xl font-bold shadow-xl opacity-90 cursor-default">Register Item</button>
                        </div>
                    </div>`;


            } else if (currentModule === 'distribution') {
                html += `
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 w-full -mx-2">

                        {{-- Left: Recipient Builder --}}
                        <div class="lg:col-span-7 space-y-6">
                            <div>
                                <h4 class="text-2xl font-black text-slate-800 uppercase tracking-tight italic">Recipient Registry</h4>
                                <p class="text-slate-400 text-xs font-bold uppercase mt-1 tracking-widest">Register new entities to the master list</p>
                            </div>

                            <div class="space-y-5">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Entity Type <span class="text-red-500">*</span></label>
                                    <select id="distSourceType" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-bold text-slate-700 cursor-pointer transition-all focus:border-[#c00000] focus:ring-4 focus:ring-red-100" onchange="distToggleSource()">
                                        <option value="">-- Select Entity Type --</option>
                                        <option value="school">School</option>
                                        <option value="external">External (Offices / Individuals)</option>
                                    </select>
                                </div>

                                <div id="distSchoolBox" class="hidden space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Search School <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <input id="distSchoolInput" data-school-id="" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-bold text-slate-700 transition-all focus:border-[#c00000] focus:ring-4 focus:ring-red-100" placeholder="Type school name..." oninput="distFilterSchools()">
                                        <div id="distSchoolDropdown" class="hidden absolute z-20 w-full mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-52 overflow-y-auto custom-scroll"></div>
                                    </div>
                                </div>

                                <div id="distExternalBox" class="hidden space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">External Office / Organization <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <input type="text" id="distExternalInput" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-bold text-slate-700 transition-all focus:border-[#c00000] focus:ring-4 focus:ring-red-100" placeholder="Click to browse or type a new office..." oninput="distFilterExternal()" onfocus="distFilterExternal()" autocomplete="off">
                                        <div id="distExternalDropdown" class="hidden absolute z-20 w-full mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-52 overflow-y-auto custom-scroll"></div>
                                    </div>
                                </div>

                                <div id="distPersonnelSection" class="hidden pt-6 border-t border-slate-100 space-y-4">
                                    <div>
                                        <h3 class="font-black text-slate-800 uppercase tracking-tight italic text-base">Personnel Details <span class="text-slate-400 text-sm font-medium italic lowercase">(optional)</span></h3>
                                        <p class="text-[10px] text-slate-400 font-bold uppercase mt-1">Assign an authorized receiver</p>
                                    </div>

                                    <div class="flex flex-col sm:flex-row gap-3 p-4 bg-slate-50/50 rounded-3xl border border-slate-100">
                                        <div class="relative flex-grow">
                                            <input type="text" id="distPersonnelName" placeholder="Click to browse or type a new name..." class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-bold text-slate-700 text-sm transition-all focus:border-[#c00000]" oninput="distFilterPersonnel()" onfocus="distFilterPersonnel()" autocomplete="off">
                                            <div id="distPersonnelDropdown" class="hidden absolute z-20 w-full mt-1 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-48 overflow-y-auto custom-scroll"></div>
                                        </div>
                                        <input type="text" id="distPersonnelPosition" placeholder="Job Title / Position" class="flex-grow p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-bold text-slate-700 text-sm transition-all focus:border-[#c00000]">
                                    </div>

                                    <button type="button" onclick="distAddRecipientToList()" class="w-full py-5 bg-[#c00000] hover:bg-red-700 text-white rounded-[2rem] font-black uppercase tracking-[0.2em] shadow-xl transition-all hover:-translate-y-1 active:scale-95">
                                        Add Recipient
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Right: Recipient List --}}
                        <div class="lg:col-span-5 bg-slate-900 p-6 rounded-[2.5rem] shadow-2xl border border-slate-800 flex flex-col overflow-hidden relative" style="min-height: 400px;">
                            <div class="mb-6 flex justify-between items-start">
                                <div>
                                    <h4 class="text-lg font-black text-white uppercase tracking-tight italic">Recipient List</h4>
                                    <p class="text-slate-400 text-[10px] font-bold uppercase mt-1 tracking-widest">Selected targets for batch</p>
                                </div>
                                <span id="distRecipientCount" class="bg-white/10 text-white px-4 py-1 rounded-full text-[10px] font-black uppercase">0 People</span>
                            </div>

                            <div id="distActiveList" class="space-y-3 flex-grow overflow-y-auto custom-scroll pr-2">
                                <div id="distEmptyState" class="text-center py-16">
                                    <div class="w-14 h-14 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-3 border border-white/5">
                                        <svg class="w-7 h-7 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    </div>
                                    <p class="text-slate-500 text-xs font-bold uppercase tracking-widest italic">List is empty</p>
                                </div>
                            </div>

                            <div id="distListFooter" class="hidden pt-5 mt-5 border-t border-white/10">
                                <button type="button" onclick="distProceedToAssign()" class="w-full py-4 bg-white text-slate-900 rounded-2xl font-black uppercase tracking-widest hover:bg-slate-100 transition-all text-xs">
                                    Proceed to Assign Assets
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }   // <-- closes else if (currentModule === 'distribution')

            container.innerHTML = html;
        }


        
        function getDistributorOptionsHtml() {
            let html = '<option value="">-- Distributor --</option>';
            const distributors = rawStakeholders.filter(s => s.type === 'Distributor');
            distributors.forEach(d => {
                html += `<option value="${d.id}">${d.name}</option>`;
            });
            return html;
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


        // =============================================
        // ASSET DISTRIBUTION MODULE
        // =============================================
        let preSelectedSchools = []; // Array of objects { id, name, uid } to allow duplicates
        let distTabsData = []; // State for each tab
        let currentActiveTab = 0;

        // --- Phase 1: Pre-selection ---
        function switchRecipTab(tab) {
            document.getElementById('currentRecipTab').value = tab;
            const btnSchool = document.getElementById('tabRecipSchoolBtn');
            const btnIndiv = document.getElementById('tabRecipIndivBtn');
            const search = document.getElementById('preDistSchoolSearch');
            
            if (tab === 'school') {
                btnSchool.className = "text-[10px] font-bold px-3 py-1 rounded-md bg-white shadow-sm text-slate-700 transition-all";
                btnIndiv.className = "text-[10px] font-bold px-3 py-1 rounded-md text-slate-500 hover:text-slate-700 transition-all bg-transparent";
                search.placeholder = "Search schools...";
            } else {
                btnIndiv.className = "text-[10px] font-bold px-3 py-1 rounded-md bg-white shadow-sm text-slate-700 transition-all";
                btnSchool.className = "text-[10px] font-bold px-3 py-1 rounded-md text-slate-500 hover:text-slate-700 transition-all bg-transparent";
                search.placeholder = "Search individual records or offices...";
            }
            
            filterPreDistSchools();
            document.getElementById('preDistSchoolSearch').focus();
        }

        function filterPreDistSchools() {
            const dd = document.getElementById('preDistSchoolDropdownList');
            const searchInput = document.getElementById('preDistSchoolSearch');
            if(!searchInput) return;
            const q = searchInput.value.trim().toLowerCase();
            const tabInput = document.getElementById('currentRecipTab');
            const tab = tabInput ? tabInput.value : 'school';
            
            dd.classList.remove('hidden');
            let h = '<div class="p-3 text-[10px] text-slate-400 font-extrabold uppercase tracking-widest sticky top-0 bg-white/90 backdrop-blur border-b border-slate-100 z-10">Select recipient</div>';
            
            if (tab === 'school') {
                const filteredList = allSchoolsList.filter(s => 
                    s.name.toLowerCase().includes(q) || (s.school_id && s.school_id.toString().includes(q))
                ).slice(0, 50);

                if (filteredList.length === 0) {
                    h += '<div class="px-4 py-4 text-sm font-bold text-slate-400 text-center italic">No schools found</div>';
                } else {
                    h += filteredList.map(s => {
                        let schoolCodeText = s.school_id ? `<span class="text-xs font-normal text-slate-500 mr-2">${s.school_id}</span>` : '';
                        let stakeholder = rawStakeholders.find(st => st.school_id === s.id && st.type === 'Recipient');
                        let stakeholderId = stakeholder ? stakeholder.id : 'null';
                        
                        return `<div onclick="addPreDistSchool(${stakeholderId}, ${s.id}, '${s.name.replace(/'/g,"\\'")}')" class="px-4 py-3 text-sm font-bold text-slate-700 hover:bg-red-50 hover:text-[#c00000] cursor-pointer transition-colors border-b border-slate-50 last:border-0 truncate flex items-center">
                            <span class="text-[10px] bg-slate-100 text-slate-500 px-2 py-0.5 rounded-md mr-2">School</span>
                            ${schoolCodeText}${s.name}
                        </div>`;
                    }).join('');
                }
            } else {
                let filteredList = rawStakeholders.filter(s => s.type === 'Recipient' && s.entity_type !== 'School');
                const matches = filteredList.filter(s => 
                    (s.person_name && s.person_name.toLowerCase().includes(q)) || (s.name && s.name.toLowerCase().includes(q))
                ).slice(0, 50);

                if (matches.length === 0) {
                    h += '<div class="px-4 py-4 text-sm font-bold text-slate-400 text-center italic">No recipients found</div>';
                } else {
                    h += matches.map(s => {
                        let positionBadge = s.position ? `<span class="text-[9px] bg-slate-100 text-slate-500 ml-2 px-1.5 py-0.5 rounded uppercase tracking-widest">${s.position}</span>` : '';
                        let displayName = (s.person_name || s.name);
                        
                        return `<div onclick="addPreDistSchool(${s.id}, null, '${displayName.replace(/'/g,"\\'")}')" class="px-4 py-3 text-sm font-bold text-slate-700 hover:bg-red-50 hover:text-[#c00000] cursor-pointer transition-colors border-b border-slate-50 last:border-0 truncate flex items-center">
                            <span class="text-[10px] bg-slate-100 text-slate-500 px-2 py-0.5 rounded-md mr-2">${s.entity_type || 'Entity'}</span>
                            ${displayName}${positionBadge}
                        </div>`;
                    }).join('');
                }
            }
            dd.innerHTML = h;
        }

        function addPreDistSchool(recipient_id, school_id, name) {
            if (preSelectedSchools.length >= 6) {
                document.getElementById('preDistLimitWarning').classList.remove('hidden');
                return;
            }
            preSelectedSchools.push({ id: recipient_id, school_id: school_id, name, uid: Date.now() + Math.random() });
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
            
            document.getElementById('distPreSelectionPhase').classList.add('hidden');
            document.getElementById('distTabsPhase').classList.remove('hidden');
            
            // Initialize tab data states
            distTabsData = preSelectedSchools.map((school, i) => ({
                tabIndex: i,
                recipient_id: school.id,
                school_id: school.school_id,
                recipient_name: school.name,
                category_id: null,
                item_id: null,
                subItemsSelected: [] // array of { id, name, available_qty, selected_qty, distributor_id, sub_item_id }
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

        // Calculate effective remaining stock for a specific sub-item ID (which is distinct per distributor)
        function getEffectiveStock(subId) {
            if (distTabsData.length === 0) return 0;
            
            // Total stock = the specific sub-item's own quantity (warehouse inventory for this distributor)
            const raw = rawSubItems.find(s => s.id === subId);
            let totalStock = raw ? raw.quantity : 0;

            // Subtract already-distributed quantities (across all ownerships)
            let alreadyDistributed = 0;
            Object.values(rawOwnerships).forEach(ownershipList => {
                ownershipList.forEach(o => {
                    if (o.sub_item_id === subId) {
                        alreadyDistributed += o.quantity;
                    }
                });
            });

            // Subtract quantities allocated in the current distribution session across ALL tabs
            let sessionAllocated = 0;
            distTabsData.forEach(tab => {
                tab.subItemsSelected.forEach(si => {
                    if (si.sub_item_id === subId && si.selected_qty > 0) {
                        sessionAllocated += si.selected_qty;
                    }
                });
            });

            return Math.max(0, totalStock - alreadyDistributed - sessionAllocated);
        }

        function filterTabSub(tabId) {
            const dd = document.getElementById(`tabSubDropdown_${tabId}`);
            const q = document.getElementById(`tabSubSearch_${tabId}`).value.trim().toLowerCase();
            const itemId = distTabsData[tabId].item_id;
            if(!itemId) return;

            dd.classList.remove('hidden');
            const pool = rawSubItems.filter(s => s.item_id == itemId && s.distributor_id); // Only those with distributors
            
            // Group by sub-item name to consolidate multiple distributors
            const grouped = {};
            pool.forEach(s => {
                if (!grouped[s.name]) {
                    grouped[s.name] = { name: s.name, total_stock: 0, sources: [] };
                }
                const stock = getEffectiveStock(s.id);
                grouped[s.name].sources.push({ ...s, effective_stock: stock });
                grouped[s.name].total_stock += stock;
            });

            const selectedNames = distTabsData[tabId].subItemsSelected.map(s => s.name);
            const availableGroups = Object.values(grouped).filter(g => !selectedNames.includes(g.name));
            const f = availableGroups.filter(g => g.name.toLowerCase().includes(q)).slice(0, 50);

            let h = '<div class="p-3 text-[10px] text-slate-400 font-extrabold uppercase tracking-widest sticky top-0 bg-white/90 backdrop-blur border-b border-slate-100">Select sub-item</div>';
            h += f.length === 0 ? '<div class="px-4 py-3 text-sm text-slate-400 italic">No sub-items available</div>'
                : f.map(g => {
                    if(g.total_stock <= 0) {
                        return `<div class="px-4 py-3 text-sm font-semibold text-slate-300 bg-slate-50 cursor-not-allowed border-b border-slate-50">${g.name} <span class="text-red-400 text-xs">(Out of stock everywhere)</span></div>`;
                    }
                    // For selecting, we pass the group name. The specific sub_item_id/distributor is resolved inside the card.
                    return `<div onclick="selectTabSub(${tabId}, '${g.name.replace(/'/g,"\\'")}')" class="px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-blue-50 hover:text-blue-700 cursor-pointer transition-colors border-b border-slate-50 flex justify-between"><span>${g.name}</span> <span class="text-xs font-black text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-xl">${g.total_stock} across ${g.sources.length} source(s)</span></div>`;
                }).join('');
            dd.innerHTML = h;
        }

        function selectTabSub(tabId, subName) {
            const tab = distTabsData[tabId];
            const itemId = tab.item_id;
            const pool = rawSubItems.filter(s => s.item_id == itemId && s.name === subName && s.distributor_id);
            
            // Calculate current stock for each source
            const sources = pool.map(s => ({ ...s, effective_stock: getEffectiveStock(s.id) })).filter(s => s.effective_stock > 0);
            
            if (sources.length === 0) return; // Paranoia

            // Default to the first available source
            const defaultSource = sources[0];

            tab.subItemsSelected.push({ 
                name: subName, 
                sub_item_id: defaultSource.id, 
                distributor_id: defaultSource.distributor_id,
                available_qty: defaultSource.effective_stock, 
                selected_qty: 0,
                all_sources: sources // Store all possible sources to feed the dropdown
            });
            
            document.getElementById(`tabSubSearch_${tabId}`).value = '';
            document.getElementById(`tabSubDropdown_${tabId}`).classList.add('hidden');
            renderTabSubItems(tabId);
            updateReadyStatus();
        }

        function removeTabSub(tabId, subName) {
            const tab = distTabsData[tabId];
            const sub = tab.subItemsSelected.find(s => s.name === subName);
            if (!sub) return;
            const freedSubId = sub.sub_item_id;
            
            tab.subItemsSelected = tab.subItemsSelected.filter(s => s.name !== subName);
            renderTabSubItems(tabId);
            refreshAllTabsForSubItem(freedSubId, tabId);
            updateReadyStatus();
        }

        // When the user changes which distributor's stock they want to deduct from
        function changeSubItemSource(tabId, subName, newSubIdStr) {
            const tab = distTabsData[tabId];
            const sub = tab.subItemsSelected.find(s => s.name === subName);
            if (!sub) return;
            
            const oldSubId = sub.sub_item_id;
            const newSubId = parseInt(newSubIdStr);
            const newSource = sub.all_sources.find(s => s.id === newSubId);
            
            if (newSource) {
                // Return stock to old source
                sub.selected_qty = 0; 
                refreshAllTabsForSubItem(oldSubId, -1); // Refresh all including this one
                
                // Switch to new source
                sub.sub_item_id = newSubId;
                sub.distributor_id = newSource.distributor_id;
                
                // Effective stock check for new source
                const effectiveStock = getEffectiveStock(newSubId);
                sub.available_qty = effectiveStock;
                
                renderTabSubItems(tabId); // Re-render this card
                refreshAllTabsForSubItem(newSubId, tabId); // Update others using this new source
                updateReadyStatus();
            }
        }

        function updateTabSubQty(tabId, subName, valStr) {
            const tab = distTabsData[tabId];
            const sub = tab.subItemsSelected.find(s => s.name === subName);
            if (!sub) return;
            
            let val = parseInt(valStr);
            if(isNaN(val) || val < 0) val = 0;
            sub.selected_qty = val;

            const effectiveStock = getEffectiveStock(sub.sub_item_id);
            const maxForThis = effectiveStock + val;
            
            // Clean subName string for use in element IDs to avoid CSS selector issues
            const safeName = subName.replace(/[^a-zA-Z0-9]/g, '_');
            
            const input = document.getElementById(`subQtyInput_${tabId}_${safeName}`);
            const errorLabel = document.getElementById(`subQtyError_${tabId}_${safeName}`);
            const stockLabel = document.getElementById(`subStockLabel_${tabId}_${safeName}`);
            
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

            sub.available_qty = maxForThis;
            refreshAllTabsForSubItem(sub.sub_item_id, tabId);
            updateReadyStatus();
        }

        function refreshAllTabsForSubItem(subId, excludeTabId) {
            distTabsData.forEach((tab, i) => {
                if (i === excludeTabId) return;
                const si = tab.subItemsSelected.find(s => s.sub_item_id === subId);
                if (!si) return;

                const effectiveStock = getEffectiveStock(subId);
                const maxForThis = effectiveStock + si.selected_qty;
                si.available_qty = maxForThis;

                const safeName = si.name.replace(/[^a-zA-Z0-9]/g, '_');
                const stockLabel = document.getElementById(`subStockLabel_${i}_${safeName}`);
                const input = document.getElementById(`subQtyInput_${i}_${safeName}`);
                const errorLabel = document.getElementById(`subQtyError_${i}_${safeName}`);

                if (stockLabel) {
                    const remaining = maxForThis - si.selected_qty;
                    stockLabel.textContent = `${remaining} AVAILABLE`;
                    stockLabel.classList.toggle('text-emerald-600', remaining > 0);
                    stockLabel.classList.toggle('text-red-500', remaining <= 0);
                }

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
                const effectiveStock = getEffectiveStock(si.sub_item_id);
                // The display stock shouldn't include this tab's selected qty because the getEffectiveStock already subtracted it
                const displayStock = effectiveStock + si.selected_qty - si.selected_qty; // equivalent to effectiveStock
                const maxForThis = effectiveStock + si.selected_qty;
                si.available_qty = maxForThis;
                
                const safeName = si.name.replace(/[^a-zA-Z0-9]/g, '_');
                const escapedName = si.name.replace(/'/g, "\\'");
                
                let sourceBadgeHtml = '';
                const distributor = rawStakeholders.find(st => st.id == si.distributor_id);
                if (distributor) {
                    const st = distributor.source_type;
                    if (st === 'Government') sourceBadgeHtml = `<span class="bg-blue-50 text-blue-600 border border-blue-200 px-1.5 py-0.5 rounded-md text-[9px] font-black uppercase tracking-widest ml-2" title="Source Category: Government">🏛️ Govt</span>`;
                    else if (st === 'Donor') sourceBadgeHtml = `<span class="bg-purple-50 text-purple-600 border border-purple-200 px-1.5 py-0.5 rounded-md text-[9px] font-black uppercase tracking-widest ml-2" title="Source Category: Donor">🤝 Donor</span>`;
                    else if (st === 'Contractor') sourceBadgeHtml = `<span class="bg-orange-50 text-orange-600 border border-orange-200 px-1.5 py-0.5 rounded-md text-[9px] font-black uppercase tracking-widest ml-2" title="Source Category: Contractor">🏢 Contract</span>`;
                    else if (st === 'NGO') sourceBadgeHtml = `<span class="bg-emerald-50 text-emerald-600 border border-emerald-200 px-1.5 py-0.5 rounded-md text-[9px] font-black uppercase tracking-widest ml-2" title="Source Category: NGO">🌍 NGO</span>`;
                    else if (st) sourceBadgeHtml = `<span class="bg-slate-100 text-slate-500 border border-slate-200 px-1.5 py-0.5 rounded-md text-[9px] font-black uppercase tracking-widest ml-2">📦 ${st}</span>`;
                }

                if (si.all_sources && si.all_sources.length > 1) {
                    const options = si.all_sources.map(s => {
                        const stockText = s.id === si.sub_item_id 
                            ? ` (${getEffectiveStock(s.id) + si.selected_qty} left)` 
                            : ` (${getEffectiveStock(s.id)} left)`;
                        const isSelected = s.id === si.sub_item_id ? 'selected' : '';
                        return `<option value="${s.id}" ${isSelected}>Source: ${s.distributor_name}${stockText}</option>`;
                    }).join('');
                    
                    sourceDropdownHtml = `
                        <div class="flex items-center mt-1.5">
                            <select onchange="changeSubItemSource(${tabId}, '${escapedName}', this.value)" class="flex-grow p-2 text-xs bg-slate-50 border border-slate-200 rounded outline-none font-semibold text-slate-600 cursor-pointer focus:border-blue-400 max-w-[280px]">
                                ${options}
                            </select>
                            ${sourceBadgeHtml}
                        </div>
                    `;
                } else if (si.all_sources && si.all_sources.length === 1) {
                     sourceDropdownHtml = `<div class="mt-1 flex items-center text-[10px] text-slate-500 font-medium">Source: ${si.all_sources[0].distributor_name} ${sourceBadgeHtml}</div>`;
                }

                return `
                <div class="flex items-center gap-3 p-4 bg-white border border-slate-200 shadow-sm rounded-2xl animate-in fade-in slide-in-from-top-2 duration-300">
                    <div class="flex-grow flex flex-col">
                        <span class="text-sm font-bold text-slate-800">${si.name}</span>
                        ${sourceDropdownHtml}
                        <span id="subStockLabel_${tabId}_${safeName}" class="text-[10px] font-black uppercase tracking-widest mt-1 ${displayStock > 0 ? 'text-emerald-600' : 'text-red-500'}">${displayStock} AVAILABLE FROM SOURCE</span>
                    </div>
                    <div class="flex flex-col items-center gap-1">
                        <input type="number" id="subQtyInput_${tabId}_${safeName}" min="1" max="${maxForThis}" placeholder="Qty" value="${si.selected_qty || ''}" oninput="updateTabSubQty(${tabId}, '${escapedName}', this.value)" class="w-24 p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none font-black text-sm text-center focus:border-blue-400 focus:ring-4 focus:ring-blue-50 transition-all">
                        <span id="subQtyError_${tabId}_${safeName}" class="hidden text-[10px] font-bold text-red-500 text-center"></span>
                    </div>
                    <button type="button" onclick="removeTabSub(${tabId}, '${escapedName}')" class="text-slate-300 hover:text-red-500 hover:bg-red-50 p-2 rounded-xl transition-colors font-bold text-lg shrink-0">✕</button>
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
                    subItemsPayload.push({ 
                        id: sub.sub_item_id, 
                        distributor_id: sub.distributor_id,
                        qty: sub.selected_qty 
                    });
                }
            });

            if(tabValid) {
                payload = {
                    tab_id: `tab_${i}`,
                    recipient_id: tab.recipient_id,
                    school_id: tab.school_id,
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
    



        // ============================================================
        // DISTRIBUTION MODULE — RECIPIENT REGISTRY FUNCTIONS
        // ============================================================

        let distRecipientCount = 0;
        let distAddedIds = []; // tracks stakeholder IDs already in the list
        let distRecipientsCache = {}; // { id: { displayName, subLabel } } — survives rawStakeholders gap for NEW entries

        function distToggleSource() {
            const type = document.getElementById('distSourceType')?.value;
            const sBox = document.getElementById('distSchoolBox');
            const eBox = document.getElementById('distExternalBox');
            const pSection = document.getElementById('distPersonnelSection');
            if (sBox) sBox.classList.toggle('hidden', type !== 'school');
            if (eBox) eBox.classList.toggle('hidden', type !== 'external');
            if (pSection) pSection.classList.toggle('hidden', type === '');

            // Clear previous inputs on switch
            const schoolInput = document.getElementById('distSchoolInput');
            if (schoolInput) { schoolInput.value = ''; schoolInput.dataset.schoolId = ''; }
            const extInput = document.getElementById('distExternalInput');
            if (extInput) extInput.value = '';
            const pName = document.getElementById('distPersonnelName');
            const pPos = document.getElementById('distPersonnelPosition');
            if (pName) pName.value = '';
            if (pPos) pPos.value = '';
        }

        function distFilterSchools() {
            const input = document.getElementById('distSchoolInput');
            const dropdown = document.getElementById('distSchoolDropdown');
            if (!input || !dropdown) return;

            // Clear school id when user types again
            input.dataset.schoolId = '';

            const val = input.value.toLowerCase().trim();
            if (!val) { dropdown.classList.add('hidden'); return; }

            const results = allSchoolsList.filter(s => s.name.toLowerCase().includes(val)).slice(0, 20);
            dropdown.innerHTML = results.length
                ? results.map(s =>
                    `<div onclick="distSelectSchool('${s.name.replace(/'/g,"\\'")}', ${s.id})"
                          class="px-5 py-3 hover:bg-red-50 hover:text-[#c00000] cursor-pointer font-bold text-sm border-b border-slate-50 last:border-0">
                        ${s.name}
                    </div>`
                  ).join('')
                : `<div class="px-5 py-4 text-slate-400 text-sm font-semibold">No schools found</div>`;

            dropdown.classList.remove('hidden');
        }

        function distSelectSchool(name, schoolId) {
            const input = document.getElementById('distSchoolInput');
            const dropdown = document.getElementById('distSchoolDropdown');
            if (input) { input.value = name; input.dataset.schoolId = schoolId; }
            if (dropdown) dropdown.classList.add('hidden');
        }

        async function distAddRecipientToList() {
            const type = document.getElementById('distSourceType')?.value;
            if (!type) {
                Swal.fire('Required', 'Please select an Entity Type first.', 'warning');
                return;
            }

            const schoolInput = document.getElementById('distSchoolInput');
            const schoolId = parseInt(schoolInput?.dataset?.schoolId || '0') || null;
            const externalName = document.getElementById('distExternalInput')?.value?.trim() || '';
            const personName  = document.getElementById('distPersonnelName')?.value?.trim() || '';
            const position    = document.getElementById('distPersonnelPosition')?.value?.trim() || '';

            // Validation
            if (type === 'school' && !schoolId) {
                Swal.fire('Required', 'Please search and select a school from the dropdown.', 'warning');
                return;
            }
            if (type === 'external' && !externalName) {
                Swal.fire('Required', 'Please enter the external office or organization name.', 'warning');
                return;
            }

            // Show loading state
            const addBtn = document.querySelector('[onclick="distAddRecipientToList()"]');
            if (addBtn) { addBtn.disabled = true; addBtn.innerText = 'Adding...'; }

            try {
                const resp = await fetch('{{ route("recipients.add") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ entity_type: type, school_id: schoolId, external_name: externalName, person_name: personName, position })
                });

                const data = await resp.json();

                if (!resp.ok) {
                    Swal.fire('Error', data.error || 'Something went wrong.', 'error');
                    return;
                }

                // Duplicate guard
                if (distAddedIds.includes(data.id)) {
                    Swal.fire('Duplicate', `${data.display_name} is already in the recipient list.`, 'info');
                    return;
                }

                distAddedIds.push(data.id);
                distRecipientCount++;

                // Cache display data so distGoBackToRegistry can restore cards for NEW stakeholders
                distRecipientsCache[data.id] = { displayName: data.display_name, subLabel: data.sub_label };

                // Build card
                const newBadge = data.is_new
                    ? `<span class="text-[8px] font-black bg-emerald-400/20 text-emerald-400 px-2 py-0.5 rounded-full uppercase tracking-widest ml-1">NEW</span>`
                    : '';

                const card = document.createElement('div');
                card.className = 'bg-white/5 border border-white/10 p-4 rounded-2xl flex justify-between items-center';
                card.dataset.stakeholderId = data.id;
                card.innerHTML = `
                    <div class="overflow-hidden flex-1">
                        <div class="flex items-center gap-1">
                            <p class="text-white font-bold text-xs truncate">${data.display_name}</p>
                            ${newBadge}
                        </div>
                        <p class="text-slate-500 text-[9px] uppercase font-black tracking-widest truncate mt-0.5">${data.sub_label}</p>
                    </div>
                    <button onclick="distRemoveRecipient(this, ${data.id})" class="text-slate-600 hover:text-red-400 transition-colors ml-3 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>`;

                const activeList = document.getElementById('distActiveList');
                if (activeList) activeList.appendChild(card);

                document.getElementById('distEmptyState')?.classList.add('hidden');
                document.getElementById('distListFooter')?.classList.remove('hidden');
                distUpdateCount();

                // Clear fields after adding
                if (document.getElementById('distPersonnelName')) document.getElementById('distPersonnelName').value = '';
                if (document.getElementById('distPersonnelPosition')) document.getElementById('distPersonnelPosition').value = '';

            } catch (err) {
                console.error(err);
                Swal.fire('Error', 'Network error. Please try again.', 'error');
            } finally {
                if (addBtn) { addBtn.disabled = false; addBtn.innerText = 'Add Recipient'; }
            }
        }

        function distRemoveRecipient(btn, stakeholderId) {
            btn.closest('[data-stakeholder-id]')?.remove();
            distAddedIds = distAddedIds.filter(id => id !== stakeholderId);
            distRecipientCount--;
            distUpdateCount();
        }

        function distUpdateCount() {
            const counter = document.getElementById('distRecipientCount');
            if (counter) counter.innerText = `${distRecipientCount} ${distRecipientCount === 1 ? 'Person' : 'People'}`;
            if (distRecipientCount <= 0) {
                distRecipientCount = 0;
                document.getElementById('distEmptyState')?.classList.remove('hidden');
                document.getElementById('distListFooter')?.classList.add('hidden');
            }
        }

        function distProceedToAssign() {
            if (distRecipientCount === 0) {
                Swal.fire('No Recipients', 'Add at least one recipient before proceeding.', 'warning');
                return;
            }

            // Build preSelectedSchools from the recipient list cards
            preSelectedSchools = [];
            const cards = document.querySelectorAll('#distActiveList [data-stakeholder-id]');
            cards.forEach(card => {
                const id = parseInt(card.dataset.stakeholderId);
                const st = rawStakeholders.find(s => s.id === id);
                const nameEl = card.querySelector('p.text-white');
                const displayName = nameEl ? nameEl.textContent.trim() : (st ? (st.person_name || st.name) : 'Recipient');
                preSelectedSchools.push({
                    id: id,
                    school_id: st ? st.school_id : null,
                    name: displayName,
                    uid: Date.now() + Math.random()
                });
            });

            if (preSelectedSchools.length === 0) {
                Swal.fire('Error', 'Could not read recipient list. Please try again.', 'error');
                return;
            }

            // Initialize distTabsData for the tabs phase
            distTabsData = preSelectedSchools.map((r, i) => ({
                tabIndex: i,
                recipient_id: r.id,
                school_id: r.school_id,
                recipient_name: r.name,
                category_id: null,
                item_id: null,
                subItemsSelected: []
            }));

            // Replace the distribution form content with the tabs interface
            const container = document.getElementById('formContent');
            const parentWrap = container.parentElement;
            parentWrap.classList.remove('max-w-5xl', 'max-w-4xl');
            parentWrap.classList.add('max-w-6xl');

            container.innerHTML = `
                <div class="mb-8 flex flex-wrap justify-between items-center gap-4">
                    <div>
                        <h4 class="text-2xl font-black text-slate-800 uppercase tracking-tight italic">Assign Assets</h4>
                        <p class="text-slate-400 text-xs font-bold uppercase mt-1 tracking-widest">Allocating to ${preSelectedSchools.length} recipient${preSelectedSchools.length > 1 ? 's' : ''}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="distGoBackToRegistry()" class="px-5 py-3 bg-slate-100 text-slate-600 rounded-2xl font-bold text-xs hover:bg-slate-200 transition-all">← Back to Registry</button>
                        <button type="button" id="distributeAllBtn" onclick="confirmDistributeAll()" class="px-6 py-3 bg-[#c00000] text-white rounded-2xl font-bold text-xs shadow-lg hover:bg-red-700 transition-all opacity-40 cursor-not-allowed" disabled>Distribute All</button>
                    </div>
                </div>
                <div class="grid grid-cols-12 gap-6">
                    <div id="distTabsHeader" class="col-span-3 space-y-2 overflow-y-auto custom-scroll" style="max-height:620px;"></div>
                    <div id="distTabsContentContainer" class="col-span-9"></div>
                </div>
            `;

            renderTabsUI();
            switchTab(0);
        }

        function distGoBackToRegistry() {
            // Rebuild the registry view, preserving the existing recipient list
            const savedIds = [...distAddedIds];
            const savedCount = distRecipientCount;

            const container = document.getElementById('formContent');
            const parentWrap = container.parentElement;
            parentWrap.classList.remove('max-w-6xl');
            parentWrap.classList.add('max-w-5xl'); // restore distribution mode width

            currentModule = 'distribution';
            renderForm();

            // Restore recipient cards from distRecipientsCache (works for NEW stakeholders not in rawStakeholders)
            setTimeout(() => {
                savedIds.forEach(id => {
                    // Prefer cache (always has data). Fallback to rawStakeholders for pre-existing ones
                    const cached = distRecipientsCache[id];
                    const st = !cached ? rawStakeholders.find(s => s.id === id) : null;

                    const displayName = cached
                        ? cached.displayName
                        : (st ? (st.person_name || st.name) : null);
                    const subLabel = cached
                        ? cached.subLabel
                        : (st ? [st.position, st.name].filter(Boolean).join(' • ') : null);

                    if (!displayName) return; // skip if nothing to show

                    const card = document.createElement('div');
                    card.className = 'bg-white/5 border border-white/10 p-4 rounded-2xl flex justify-between items-center';
                    card.dataset.stakeholderId = id;
                    card.innerHTML = `
                        <div class="overflow-hidden flex-1">
                            <p class="text-white font-bold text-xs truncate">${displayName}</p>
                            <p class="text-slate-500 text-[9px] uppercase font-black tracking-widest truncate mt-0.5">${subLabel}</p>
                        </div>
                        <button onclick="distRemoveRecipient(this, ${id})" class="text-slate-600 hover:text-red-400 transition-colors ml-3 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>`;
                    const activeList = document.getElementById('distActiveList');
                    if (activeList) activeList.appendChild(card);
                });

                if (savedIds.length > 0) {
                    distAddedIds = savedIds;
                    distRecipientCount = savedCount;
                    document.getElementById('distEmptyState')?.classList.add('hidden');
                    document.getElementById('distListFooter')?.classList.remove('hidden');
                    distUpdateCount();
                }
            }, 50);
        }

        // ─── External Office dropdown (shows all on focus, filters on type) ────
        function distFilterExternal() {
            const input    = document.getElementById('distExternalInput');
            const dropdown = document.getElementById('distExternalDropdown');
            if (!input || !dropdown) return;

            const val = input.value.toLowerCase().trim();

            // All existing external org-level stakeholders, filtered by val if present
            const all = rawStakeholders.filter(s => s.entity_type === 'External' && !s.person_name && s.name);
            const results = val ? all.filter(s => s.name.toLowerCase().includes(val)).slice(0, 20) : all.slice(0, 30);

            let html = '';

            if (results.length > 0) {
                html += `<div class="px-4 py-2 text-[10px] font-black text-slate-400 uppercase tracking-widest sticky top-0 bg-white border-b border-slate-50">Existing Offices</div>`;
                html += results.map(s =>
                    `<div onmousedown="distSelectExternal('${s.name.replace(/'/g, "\\'")}')" class="px-5 py-3 hover:bg-red-50 hover:text-[#c00000] cursor-pointer border-b border-slate-50 last:border-0 flex items-center justify-between">
                        <span class="font-bold text-sm">${s.name}</span>
                        <span class="text-[9px] font-black bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full uppercase tracking-widest ml-2 shrink-0">EXISTS</span>
                    </div>`
                ).join('');
            }

            // Show "create new" option if typed value doesn't exactly match any existing
            const exactMatch = all.find(s => s.name.toLowerCase() === val);
            if (val && !exactMatch) {
                html += `<div onmousedown="distSelectExternal('${input.value.replace(/'/g, "\\'")}')" class="px-5 py-3 hover:bg-emerald-50 hover:text-emerald-700 cursor-pointer border-t border-slate-100 flex items-center gap-3">
                    <span class="text-sm font-bold text-emerald-600">+ Create New:</span>
                    <span class="text-sm font-bold">${input.value}</span>
                    <span class="text-[9px] font-black bg-emerald-100 text-emerald-600 px-2 py-0.5 rounded-full uppercase tracking-widest ml-auto">NEW</span>
                </div>`;
            }

            if (!html) {
                html = `<div class="px-5 py-4 text-slate-400 text-xs font-semibold italic text-center">No external offices yet — type a name to create one</div>`;
            }

            dropdown.innerHTML = html;
            dropdown.classList.remove('hidden');
        }

        function distSelectExternal(name) {
            const input    = document.getElementById('distExternalInput');
            const dropdown = document.getElementById('distExternalDropdown');
            if (input)    input.value = name;
            if (dropdown) dropdown.classList.add('hidden');
            // Reset personnel when office changes
            const pName = document.getElementById('distPersonnelName');
            const pPos  = document.getElementById('distPersonnelPosition');
            if (pName) pName.value = '';
            if (pPos)  pPos.value  = '';
            document.getElementById('distPersonnelDropdown')?.classList.add('hidden');
        }

        // ─── Personnel dropdown (scoped to selected parent, shows all on focus) ─
        function distFilterPersonnel() {
            const input    = document.getElementById('distPersonnelName');
            const dropdown = document.getElementById('distPersonnelDropdown');
            if (!input || !dropdown) return;

            const val         = input.value.toLowerCase().trim();
            const type        = document.getElementById('distSourceType')?.value;
            const schoolInput = document.getElementById('distSchoolInput');
            const schoolId    = parseInt(schoolInput?.dataset?.schoolId || '0') || null;
            const extInput    = document.getElementById('distExternalInput');
            const extName     = extInput?.value?.trim().toLowerCase() || '';

            let pool = [];
            let parentLabel = '';

            if (type === 'school') {
                if (!schoolId) {
                    dropdown.innerHTML = `<div class="px-5 py-4 text-slate-400 text-xs font-semibold italic text-center">Select a school first to see existing personnel</div>`;
                    dropdown.classList.remove('hidden');
                    return;
                }
                pool = rawStakeholders.filter(s => s.school_id === schoolId && s.person_name);
                parentLabel = schoolInput?.value || 'this school';

            } else if (type === 'external') {
                if (!extName) {
                    dropdown.innerHTML = `<div class="px-5 py-4 text-slate-400 text-xs font-semibold italic text-center">Enter the external office name first to see existing personnel</div>`;
                    dropdown.classList.remove('hidden');
                    return;
                }
                const parent = rawStakeholders.find(s =>
                    s.entity_type === 'External' && !s.person_name && s.name && s.name.toLowerCase() === extName
                );
                if (parent) {
                    pool = rawStakeholders.filter(s => s.parent_id === parent.id && s.person_name);
                }
                parentLabel = extInput?.value || 'this office';
            }

            const matches = val ? pool.filter(s => s.person_name.toLowerCase().includes(val)).slice(0, 15) : pool.slice(0, 20);

            let html = '';

            if (matches.length > 0) {
                html += `<div class="px-4 py-2 text-[10px] font-black text-slate-400 uppercase tracking-widest sticky top-0 bg-white border-b border-slate-50">Personnel under ${parentLabel}</div>`;
                html += matches.map(s => {
                    const pos = s.position || '';
                    const posBadge = pos ? `<span class="text-[9px] bg-slate-100 text-slate-500 ml-1.5 px-1.5 py-0.5 rounded uppercase tracking-widest">${pos}</span>` : '';
                    return `<div onmousedown="distSelectPersonnel('${s.person_name.replace(/'/g, "\\'")}', '${pos.replace(/'/g, "\\'")}')" class="px-5 py-3 hover:bg-red-50 hover:text-[#c00000] cursor-pointer border-b border-slate-50 last:border-0 flex items-center justify-between">
                        <span class="font-bold text-sm flex items-center">${s.person_name}${posBadge}</span>
                        <span class="text-[9px] font-black bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full uppercase tracking-widest ml-2 shrink-0">EXISTS</span>
                    </div>`;
                }).join('');
            }

            // "Create new" if typed name doesn't match existing
            const exactMatch = pool.find(s => s.person_name.toLowerCase() === val);
            if (val && !exactMatch) {
                html += `<div onmousedown="distSelectPersonnel('${input.value.replace(/'/g, "\\'")}', '')" class="px-5 py-3 hover:bg-emerald-50 cursor-pointer border-t border-slate-100 flex items-center gap-3">
                    <span class="text-sm font-bold text-emerald-600">+ Add New:</span>
                    <span class="text-sm font-bold">${input.value}</span>
                    <span class="text-[9px] font-black bg-emerald-100 text-emerald-600 px-2 py-0.5 rounded-full uppercase tracking-widest ml-auto">NEW</span>
                </div>`;
            }

            if (!html) {
                html = `<div class="px-5 py-4 text-slate-400 text-xs font-semibold italic text-center">No personnel registered under ${parentLabel} yet</div>`;
            }

            dropdown.innerHTML = html;
            dropdown.classList.remove('hidden');
        }

        function distSelectPersonnel(name, position) {
            const nameIn   = document.getElementById('distPersonnelName');
            const posIn    = document.getElementById('distPersonnelPosition');
            const dropdown = document.getElementById('distPersonnelDropdown');
            if (nameIn) nameIn.value = name;
            if (posIn && position) posIn.value = position;
            if (dropdown) dropdown.classList.add('hidden');
        }

        // ─── Close dropdowns when clicking outside ────────────────────────────
        document.addEventListener('click', function(e) {
            const extWrap  = document.getElementById('distExternalInput')?.closest('.relative');
            const persWrap = document.getElementById('distPersonnelName')?.closest('.relative');
            if (extWrap  && !extWrap.contains(e.target))  document.getElementById('distExternalDropdown')?.classList.add('hidden');
            if (persWrap && !persWrap.contains(e.target)) document.getElementById('distPersonnelDropdown')?.classList.add('hidden');
        });

</script>
</body>
</html>
