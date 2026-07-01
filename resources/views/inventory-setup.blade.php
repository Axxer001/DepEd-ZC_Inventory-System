<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Setup | DepEd Zamboanga City</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @include('partials.inventory-styles')

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

        <main id="mainContent" class="p-6 lg:p-10 max-w-5xl mx-auto w-full transition-all duration-300">
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 px-4 mb-10 max-w-4xl mx-auto">
                    <div onclick="nextStep(2, 'register')" class="group bg-white p-10 rounded-[3rem] shadow-xl shadow-slate-200/60 border-2 border-transparent hover:border-[#c00000] transition-all duration-300 cursor-pointer text-center">
                        <div class="w-20 h-20 bg-red-50 text-[#c00000] rounded-3xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-10 h-10">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                        <h4 class="text-2xl font-black text-slate-800 tracking-tight uppercase">Register Asset</h4>
                        <p class="text-slate-400 text-[10px] font-bold uppercase mt-3 tracking-widest leading-tight">Stock items to AMU</p>
                    </div>

                    <div onclick="nextStep(2, 'assign_asset')" class="group block bg-white p-10 rounded-[3rem] shadow-xl shadow-slate-200/60 border-2 border-transparent hover:border-[#c00000] transition-all duration-300 cursor-pointer text-center">
                        <div class="w-20 h-20 bg-red-50 text-[#c00000] rounded-3xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-10 h-10">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                            </svg>
                        </div>
                        <h4 class="text-2xl font-black text-slate-800 tracking-tight uppercase">Assign Asset</h4>
                        <p class="text-slate-400 text-[10px] font-bold uppercase mt-3 tracking-widest leading-tight">Distribute to Personnel</p>
                    </div>

                    <div onclick="nextStep(2, 'building')" class="group bg-white p-10 rounded-[3rem] shadow-xl shadow-slate-200/60 border-2 border-transparent hover:border-[#c00000] transition-all duration-300 cursor-pointer text-center">
                        <div class="w-20 h-20 bg-red-50 text-[#c00000] rounded-3xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-10 h-10">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                            </svg>
                        </div>
                        <h4 class="text-2xl font-black text-slate-800 tracking-tight uppercase">Register Building</h4>
                        <p class="text-slate-400 text-[10px] font-bold uppercase mt-3 tracking-widest leading-tight">Unassigned buildings</p>
                    </div>

                    <div onclick="nextStep(2, 'assign_building')" class="group block bg-white p-10 rounded-[3rem] shadow-xl shadow-slate-200/60 border-2 border-transparent hover:border-[#c00000] transition-all duration-300 cursor-pointer text-center">
                        <div class="w-20 h-20 bg-red-50 text-[#c00000] rounded-3xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-10 h-10">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125V21m8.25-3v-6c0-1.242-.56-2.42-1.543-3.176l-9-6.938a3.75 3.75 0 00-4.614 0l-9 6.938A3.75 3.75 0 002.25 15v6" />
                            </svg>
                        </div>
                        <h4 class="text-2xl font-black text-slate-800 tracking-tight uppercase">Assign Building</h4>
                        <p class="text-slate-400 text-[10px] font-bold uppercase mt-3 tracking-widest leading-tight">Distribute to Schools</p>
                    </div>
                </div>



            </div>

{{-- Step 2: Category Selection --}}
<div id="step2" class="step-content">
    <h3 id="step2Title" class="text-lg font-black text-slate-900 uppercase tracking-[0.3em] text-center mb-6 -mt-6">Select Category</h3>
    
<div id="categoryGrid" class="grid grid-cols-2 gap-6 max-w-3xl mx-auto px-4 mb-8">        
    {{-- Empty Grid --}}


</div>
</div>

{{-- ═══════ STEP: ADD NEW RECORD — Registration Form ═══════ --}}
    @include('partials.register-item-step')

    @include('partials.register-building-step')
    
    @include('partials.assign-asset-step')
    
    @include('partials.assign-building-step')

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
        const rawProcurementModes = {{ Js::from($procurementModes) }};
        
        const rawDistricts = @json($districts);
        const rawLds = @json($legislativeDistricts);
        const rawQuadrants = @json($quadrants);
        const allSchoolsList = @json($allSchools);
        const allCustodiansList = @json($allCustodians);
        const rawStakeholders = @json($stakeholders);
        const rawOwnerships = @json($stakeholderOwnerships);
        const unassignedAssetsList = @json($unassignedAssets);
        const unassignedBuildingsList = @json($unassignedBuildings);
        const districtMap = {};
        rawDistricts.forEach(d => {
            districtMap[d.name] = { ld: d.legislative_district_id, quad: d.quadrant_name.replace('Quadrant ', '') };
        });

        let selectedSchoolsArray = [];
        let selectedSubItemsArray = [];

        function nextStep(step, value) {
    if (step === 2) {
        currentMode = value;

        // Register Asset
        if (value === 'register') {
            document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
            document.getElementById('stepAddNew').classList.add('active');
            document.getElementById('mainContent').classList.replace('max-w-5xl', 'max-w-full');
            
            stepHistory.push('register');
            updateBackButton();
            return;
        }

        // Assign Asset
        if (value === 'assign_asset') {
            document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
            document.getElementById('stepAssignAsset').classList.add('active');
            document.getElementById('mainContent').classList.replace('max-w-5xl', 'max-w-full');
            
            stepHistory.push('assign_asset');
            updateBackButton();
            return;
        }

        // Add Building
        if (value === 'building') {
            document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
            document.getElementById('stepAddBuilding').classList.add('active');
            document.getElementById('mainContent').classList.replace('max-w-5xl', 'max-w-full');
            stepHistory.push('addbuilding');
            updateBackButton();
            return;
        }

        // Assign Building
        if (value === 'assign_building') {
            document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
            document.getElementById('stepAssignBuilding').classList.add('active');
            document.getElementById('mainContent').classList.replace('max-w-5xl', 'max-w-full');
            stepHistory.push('assign_building');
            updateBackButton();
            return;
        }
    }

    if (step === 3) {
        if (value === 'school') { window.location.href = '/inventory-modifier/school'; return; }
        if (value === 'distribution') { window.location.href = '/inventory-modifier'; return; }
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
                const leavingStep = stepHistory[stepHistory.length - 1];
                stepHistory.pop();
                const prevStep = stepHistory[stepHistory.length - 1];

                if (['register', 'assign', 'addbuilding', 'assign_asset', 'assign_building'].includes(leavingStep)) {
                    document.getElementById('mainContent').classList.replace('max-w-full', 'max-w-5xl');
                    document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
                    document.getElementById('step1').classList.add('active');
                    updateBackButton();
                    return;
                }

                document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
                const targetId = (prevStep === 'register' || prevStep === 'assign') ? 'stepAddNew' : ('step' + prevStep);
                document.getElementById(targetId).classList.add('active');
                
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

        // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
        @include('partials.scripts.item-manager')
        @include('partials.scripts.autocomplete-engine')
    </script>

    @include('partials.inventory-modals')

</body>
</html>

