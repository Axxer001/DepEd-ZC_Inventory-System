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
                <button id="backBtn" onclick="goBack()" class="px-6 py-3 back-btn-cool rounded-2xl text-sm font-bold text-slate-600 flex items-center gap-2 shadow-sm active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Back
                </button>
            </header>

            {{-- Step 3: Form Content --}}
            <div id="step3" class="step-content active">
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
        let history = [3];
        let currentMode = 'edit';
        let currentModule = 'distribution';
        document.addEventListener('DOMContentLoaded', () => { renderForm(); });

        const rawCategories = {{ Js::from($categories) }};
        const rawItems = {{ Js::from($items) }};
        const rawSubItems = {{ Js::from($subItems) }};
        
        const rawDistricts = @json($districts);
        const rawLds = @json($legislativeDistricts);
        const rawQuadrants = @json($quadrants);
        const allSchoolsList = @json($allSchools);
        const schoolOwnershipsList = @json($schoolOwnerships);
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
            window.location.href = '/inventory-setup?step=2&mode=edit';
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
            
            parentWrap.classList.remove('max-w-2xl', 'overflow-hidden');
            parentWrap.classList.add('max-w-5xl', 'overflow-visible');

            let html = `<h4 class="text-2xl font-black text-slate-800 mb-8 uppercase tracking-tight italic">Asset Modifier</h4>`;

            html += `
                    <div id="distPreSelectionPhase" class="space-y-6 animate-in fade-in zoom-in duration-300">
                        <div class="text-center mb-8">
                            <h4 class="text-2xl font-black text-slate-800 uppercase tracking-tight italic">Step 1: Select Schools</h4>
                            <p class="text-slate-500 text-sm mt-2 font-medium">Select up to 6 schools to modify their asset distribution. You may select the same school multiple times.</p>
                        </div>
                        <div class="max-w-xl mx-auto space-y-4">
                            <div class="relative">
                                <input type="text" id="preDistSchoolSearch" placeholder="Type school name or ID..." class="w-full p-5 bg-slate-50 border border-slate-200 rounded-2xl outline-none font-bold text-slate-700 transition-all text-center focus:border-[#c00000] focus:ring-4 focus:ring-red-100" autocomplete="off" oninput="filterPreDistSchools()" onfocus="filterPreDistSchools()">
                                <div id="preDistSchoolDropdownList" class="hidden absolute z-30 w-full mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-[250px] overflow-y-auto custom-scroll"></div>
                            </div>
                            <div id="preDistSelectedSchoolsContainer" class="flex flex-col gap-2 mt-4 min-h-[50px]">
                                <span class="text-slate-400 text-xs font-bold italic w-full text-center mt-1 select-prompt">No schools selected yet.</span>
                            </div>
                            <p id="preDistLimitWarning" class="hidden text-center text-xs font-bold text-red-500 mt-2">⚠ Maximum of 6 schools reached.</p>
                            <button type="button" id="proceedDistBtn" onclick="proceedToDistributionTabs()" class="w-full mt-6 py-5 bg-slate-200 text-slate-400 rounded-3xl font-black uppercase tracking-widest cursor-not-allowed transition-all" disabled>Proceed to Modify Assets</button>
                        </div>
                    </div>
                    
                    <div id="distTabsPhase" class="hidden space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-6 gap-4">
                            <div>
                                <h4 class="text-2xl font-black text-slate-800 uppercase tracking-tight italic">Step 2: Modify Assets</h4>
                                <p class="text-slate-500 text-sm mt-1 font-medium">Add, subtract, or delete asset distributions per tab.</p>
                            </div>
                            <button type="button" onclick="backToPreSelectionPhase()" class="text-xs font-bold text-slate-400 hover:text-[#c00000] underline underline-offset-4 shrink-0 transition-colors bg-transparent border-0">« Revise Schools</button>
                        </div>
                        <div class="flex flex-col md:flex-row gap-6">
                            <div class="md:w-1/4 flex flex-col gap-2 border-r border-slate-100 pr-4 max-h-[500px] overflow-y-auto custom-scroll" id="distTabsHeader"></div>
                            <div id="distTabsContentContainer" class="md:w-3/4 min-h-[400px]"></div>
                        </div>

                        <div class="pt-6 border-t border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-4">
                            <span class="text-[10px] font-black tracking-widest uppercase text-slate-500 bg-slate-100 rounded-xl px-4 py-2" id="tabStatusCount">0 Assets Ready</span>
                            <button type="button" onclick="confirmDistributeAll()" id="distributeAllBtn" class="px-8 py-4 bg-[#c00000] hover:bg-red-700 text-white rounded-2xl font-black shadow-xl hover:-translate-y-1 active:scale-95 transition-all text-sm uppercase tracking-wider w-full sm:w-auto">Confirm Modifications</button>
                        </div>
                    </div>
                `;
            container.innerHTML = html;
            preSelectedSchools = [];
            distTabsData = [];
            currentActiveTab = 0;
            renderPreSelectedSchools();
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
                <input type="number" name="sub_item_quantities[]" placeholder="Qty" min="1" step="1" class="w-24 p-4 flex-shrink-0 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-sm text-center" required>
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
            
            const f = allSchoolsList.filter(s => s.name.toLowerCase().includes(q) || (s.school_id && s.school_id.toString().includes(q))).slice(0, 50);
            
            let h = '<div class="p-3 text-[10px] text-slate-400 font-extrabold uppercase tracking-widest sticky top-0 bg-white/90 backdrop-blur border-b border-slate-100 z-10">Select school</div>';
            h += f.length === 0 ? '<div class="px-4 py-4 text-sm font-bold text-slate-400 text-center italic">No schools found</div>'
                : f.map(s => `<div onclick="addPreDistSchool(${s.id},'${s.name.replace(/'/g,"\\'")}')" class="px-4 py-3 text-sm font-bold text-slate-700 hover:bg-red-50 hover:text-[#c00000] cursor-pointer transition-colors border-b border-slate-50 last:border-0 flex justify-between items-center group">
                    <span class="truncate pr-2">${s.school_id ? s.school_id+' - ':''}${s.name}</span>
                    <span class="text-[10px] font-black tracking-widest text-slate-400 bg-slate-50 group-hover:text-[#c00000] group-hover:bg-white px-2 py-0.5 rounded border border-slate-200 group-hover:border-red-200 transition-colors uppercase shrink-0">${s.total_assets} Assets</span>
                </div>`).join('');
            dd.innerHTML = h;
        }

        function addPreDistSchool(id, name) {
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
            document.getElementById('distPreSelectionPhase').classList.add('hidden');
            document.getElementById('distTabsPhase').classList.remove('hidden');
            
            // Initialize tab data states
            distTabsData = preSelectedSchools.map((school, i) => ({
                tabIndex: i,
                school_id: school.id,
                school_name: school.name,
                category_id: null,
                item_id: null,
                subItemsSelected: [] // array of { id, name, available_qty, selected_qty }
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
                    <span class="block w-full leading-snug" title="${tab.school_name}">${tab.school_name}</span>
                </button>
            `).join('');

            contentContainer.innerHTML = distTabsData.map((tab, i) => `
                <div id="tabContent_${i}" class="hidden space-y-6">
                    <div class="p-4 bg-slate-50 rounded-2xl border border-dashed border-slate-300 mb-6 flex flex-col sm:flex-row justify-between sm:items-center gap-4">
                        <div>
                            <span class="text-xs font-black text-slate-400 uppercase tracking-widest block mb-1">Distributing Asset To:</span>
                            <span class="text-lg font-bold text-slate-800">${tab.school_name}</span>
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

            // Limit to categories the school actually owns
            const ownedSubItems = schoolOwnershipsList[currentSchoolId] || [];
            const ownedCategoryIds = [...new Set(ownedSubItems.map(o => o.category_id))];

            const f = rawCategories.filter(c => ownedCategoryIds.includes(c.id) && c.name.toLowerCase().includes(q));
            
            let h = '<div class="p-3 text-[10px] text-slate-400 font-extrabold uppercase tracking-widest sticky top-0 bg-white/90 backdrop-blur border-b border-slate-100">Select category</div>';
            h += f.length === 0 ? '<div class="px-4 py-3 text-sm text-slate-400 italic">No categories found or owned</div>'
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
            const currentSchoolId = distTabsData[tabId].school_id;
            dd.classList.remove('hidden');

            const ownedSubItems = schoolOwnershipsList[currentSchoolId] || [];
            const ownedInCat = ownedSubItems.filter(o => o.category_id == catId);
            const ownedItemIds = [...new Set(ownedInCat.map(o => o.item_id))];

            const pool = catId ? rawItems.filter(i => ownedItemIds.includes(i.id)) : [];
            const f = pool.filter(i => i.name.toLowerCase().includes(q)).slice(0, 50);

            let h = '<div class="p-3 text-[10px] text-slate-400 font-extrabold uppercase tracking-widest sticky top-0 bg-white/90 backdrop-blur border-b border-slate-100">Select item</div>';
            h += f.length === 0 ? '<div class="px-4 py-3 text-sm text-slate-400 italic">No items found or owned</div>'
                : f.map(i => {
                    const totalOwned = ownedInCat.filter(o => o.item_id == i.id).reduce((sum, o) => sum + o.quantity, 0);
                    return `<div onclick="selectTabItem(${tabId}, ${i.id}, '${i.name.replace(/'/g,"\\'")}')" class="px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 cursor-pointer transition-colors border-b border-slate-50 last:border-0 flex justify-between items-center group">
                        <span class="truncate pr-2">${i.name}</span>
                        <span class="text-[10px] font-black tracking-widest text-[#c00000] bg-red-50 group-hover:bg-white px-2 py-0.5 rounded border border-slate-200 group-hover:border-red-200 uppercase shrink-0">${totalOwned} Owned</span>
                    </div>`;
                }).join('');
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
            const raw = rawSubItems.find(s => s.id === subId);
            if (!raw) return 0;
            let totalAllocated = 0;
            distTabsData.forEach(tab => {
                tab.subItemsSelected.forEach(si => {
                    if (si.id === subId && si.selected_qty > 0) {
                        totalAllocated += si.selected_qty;
                    }
                });
            });
            return Math.max(0, raw.quantity - totalAllocated);
        }

        function filterTabSub(tabId) {
            const dd = document.getElementById(`tabSubDropdown_${tabId}`);
            const q = document.getElementById(`tabSubSearch_${tabId}`).value.trim().toLowerCase();
            const itemId = distTabsData[tabId].item_id;
            const currentSchoolId = distTabsData[tabId].school_id;
            if(!itemId) return;

            dd.classList.remove('hidden');
            
            const ownedSubItems = schoolOwnershipsList[currentSchoolId] || [];
            const ownedInItem = ownedSubItems.filter(o => o.item_id == itemId);
            const ownedSubIds = ownedInItem.map(o => o.sub_item_id);

            const pool = rawSubItems.filter(s => ownedSubIds.includes(s.id));
            const selectedIds = distTabsData[tabId].subItemsSelected.map(s => s.id);
            const f = pool.filter(s => !selectedIds.includes(s.id) && s.name.toLowerCase().includes(q)).slice(0, 50);

            let h = '<div class="p-3 text-[10px] text-slate-400 font-extrabold uppercase tracking-widest sticky top-0 bg-white/90 backdrop-blur border-b border-slate-100">Select sub-item</div>';
            h += f.length === 0 ? '<div class="px-4 py-3 text-sm text-slate-400 italic">No sub-items available or owned</div>'
                : f.map(s => {
                    const ownedData = ownedInItem.find(o => o.sub_item_id == s.id);
                    const qtyOwned = ownedData ? ownedData.quantity : 0;
                    return `<div onclick="selectTabSub(${tabId}, ${s.id}, '${s.name.replace(/'/g,"\\'")}', ${qtyOwned})" class="px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-blue-50 hover:text-blue-700 cursor-pointer transition-colors border-b border-slate-50 flex justify-between items-center group">
                        <span class="truncate pr-2">${s.name}</span>
                        <span class="text-[10px] font-black tracking-widest text-[#c00000] bg-red-50 group-hover:bg-white px-2 py-0.5 rounded border border-slate-200 group-hover:border-red-200 uppercase shrink-0">${qtyOwned} Owned</span>
                    </div>`;
                }).join('');
            dd.innerHTML = h;
        }

        function selectTabSub(tabId, subId, subName, qtyOwned) {
            const tab = distTabsData[tabId];
            tab.subItemsSelected.push({ id: subId, name: subName, owned_qty: qtyOwned, selected_qty: 0, action: 'subtract' });
            
            document.getElementById(`tabSubSearch_${tabId}`).value = '';
            document.getElementById(`tabSubDropdown_${tabId}`).classList.add('hidden');
            renderTabSubItems(tabId);
            updateReadyStatus();
        }

        function removeTabSub(tabId, subId) {
            const tab = distTabsData[tabId];
            tab.subItemsSelected = tab.subItemsSelected.filter(s => s.id !== subId);
            renderTabSubItems(tabId);
            refreshAllTabsForSubItem(subId, tabId);
            updateReadyStatus();
        }

        // Calculate net change on the pool.
        function getEffectiveStock(subId) {
            const raw = rawSubItems.find(s => s.id === subId);
            if (!raw) return 0;
            let totalAllocated = 0;
            distTabsData.forEach(tab => {
                tab.subItemsSelected.forEach(si => {
                    if (si.id === subId && si.selected_qty > 0) {
                        if (si.action === 'subtract' || si.action === 'delete_all') {
                            totalAllocated -= si.selected_qty;
                        }
                    }
                });
            });
            return raw.quantity - totalAllocated;
        }

        function validateSubItemUI(tabId, subId) {
            const tab = distTabsData[tabId];
            const sub = tab.subItemsSelected.find(s => s.id === subId);
            if (!sub) return;

            const input = document.getElementById(`subQtyInput_${tabId}_${subId}`);
            const errorLabel = document.getElementById(`subQtyError_${tabId}_${subId}`);
            if (!input || !errorLabel) return;
            
            let hasError = false;
            let errMsg = '';

            if (sub.action === 'subtract') {
                if (sub.selected_qty > sub.owned_qty) {
                    hasError = true;
                    errMsg = `Cannot subtract more than owned (${sub.owned_qty})!`;
                }
            }

            if (sub.selected_qty <= 0 && sub.action !== 'delete_all') {
                hasError = true;
                errMsg = `Enter a quantity ≥ 1`;
            }

            if (hasError) {
                errorLabel.textContent = errMsg;
                errorLabel.classList.remove('hidden');
                input.classList.add('border-red-400', 'bg-red-50', 'text-red-600');
            } else {
                errorLabel.classList.add('hidden');
                input.classList.remove('border-red-400', 'bg-red-50', 'text-red-600');
            }
        }

        function updateTabSubQty(tabId, subId, valStr) {
            const tab = distTabsData[tabId];
            const sub = tab.subItemsSelected.find(s => s.id === subId);
            if (!sub) return;
            
            let val = parseInt(valStr);
            if(isNaN(val) || val < 0) val = 0;
            if (sub.action === 'delete_all') val = sub.owned_qty;

            sub.selected_qty = val;
            
            validateSubItemUI(tabId, subId);
            refreshAllTabsForSubItem(subId, tabId);
            updateReadyStatus();
        }

        function refreshAllTabsForSubItem(subId, excludeTabId) {
            // Need to update UI visually for the pool count
            distTabsData.forEach((tab, i) => {
                const si = tab.subItemsSelected.find(s => s.id === subId);
                if (!si) return;
                
                const stockLabel = document.getElementById(`subStockLabelPool_${i}_${subId}`);
                if (stockLabel) {
                    const effectiveStock = getEffectiveStock(subId);
                    let viewingLimit = effectiveStock;
                    if (si.action === 'subtract' || si.action === 'delete_all') viewingLimit -= si.selected_qty;
                    
                    stockLabel.textContent = viewingLimit;
                }

                if (i !== excludeTabId) validateSubItemUI(i, subId);
            });
        }

        function setSubItemAction(tabId, subId, state) {
            const tab = distTabsData[tabId];
            const sub = tab.subItemsSelected.find(s => s.id === subId);
            if (!sub) return;
            sub.action = state;
            if (state === 'delete_all') {
                sub.selected_qty = sub.owned_qty;
            } else if (state === 'subtract' && sub.selected_qty > sub.owned_qty) {
                sub.selected_qty = sub.owned_qty;
            }
            renderTabSubItems(tabId);
            updateTabSubQty(tabId, subId, sub.selected_qty); // re-trigger mapping logic
            refreshAllTabsForSubItem(subId, tabId);
        }

        function renderTabSubItems(tabId) {
            const tab = distTabsData[tabId];
            const container = document.getElementById(`tabSubContainer_${tabId}`);
            if (tab.subItemsSelected.length === 0) {
                container.innerHTML = '';
                return;
            }
            container.innerHTML = tab.subItemsSelected.map(si => {
                const effectiveStockObj = getEffectiveStock(si.id);
                let viewingLimit = effectiveStockObj;
                if (si.action === 'subtract' || si.action === 'delete_all') viewingLimit -= si.selected_qty;

                return `
                <div class="flex flex-col gap-3 p-4 bg-white border border-slate-200 shadow-sm rounded-2xl animate-in fade-in slide-in-from-top-2 duration-300">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col">
                            <span class="text-sm font-bold text-slate-800">${si.name}</span>
                            <span class="text-[10px] font-black uppercase tracking-widest mt-1 text-slate-500">Currently Owned: <span class="text-[#c00000]">${si.owned_qty}</span> | Unallocated Pool: <span id="subStockLabelPool_${tabId}_${si.id}" class="text-emerald-600">${viewingLimit}</span></span>
                        </div>
                        <button type="button" onclick="removeTabSub(${tabId}, ${si.id})" class="text-slate-300 hover:text-red-500 hover:bg-red-50 p-2 rounded-xl transition-colors font-bold text-lg shrink-0">✕</button>
                    </div>
                    
                    <div class="flex items-center gap-2 mt-2">
                        <input type="number" id="subQtyInput_${tabId}_${si.id}" min="0" placeholder="0" value="${si.selected_qty}" oninput="updateTabSubQty(${tabId}, ${si.id}, this.value)" class="w-24 p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none font-black text-sm text-center focus:border-blue-400 focus:ring-4 focus:ring-blue-50 transition-all ${si.action === 'delete_all' ? 'opacity-50 cursor-not-allowed bg-slate-100' : ''}" ${si.action === 'delete_all' ? 'disabled' : ''}>
                        
                        <div class="flex flex-wrap gap-2">
                            <button type="button" onclick="setSubItemAction(${tabId}, ${si.id}, 'subtract')" class="flex items-center gap-1.5 px-3 py-2 text-[10px] font-black uppercase tracking-widest rounded-lg transition-all border ${si.action === 'subtract' ? 'bg-orange-50 text-orange-700 border-orange-300 shadow-sm' : 'bg-white text-slate-400 border-slate-200 hover:bg-slate-50'}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" /></svg>
                                Subtract
                            </button>
                            <button type="button" onclick="setSubItemAction(${tabId}, ${si.id}, 'delete_all')" class="flex items-center gap-1.5 px-3 py-2 text-[10px] font-black uppercase tracking-widest rounded-lg transition-all border ${si.action === 'delete_all' ? 'bg-red-50 text-red-700 border-red-300 shadow-sm' : 'bg-white text-slate-400 border-slate-200 hover:bg-slate-50'}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                Delete All
                            </button>
                        </div>
                    </div>
                    <span id="subQtyError_${tabId}_${si.id}" class="hidden text-[10px] font-bold text-red-500 mt-1"></span>
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

            if(!tab.item_id) { errors.push(`Tab ${i+1} (${tab.school_name}) is missing an Item.`); return { errors, payload }; }
            if(tab.subItemsSelected.length === 0) { errors.push(`Tab ${i+1} (${tab.school_name}) has no selected sub-items.`); return { errors, payload }; }
            
            let tabValid = true;
            let subItemsPayload = [];
            tab.subItemsSelected.forEach(sub => {
                if (sub.selected_qty <= 0 && sub.action !== 'delete_all') {
                    errors.push(`Tab ${i+1}: Sub-item "${sub.name}" needs a quantity greater than 0.`);
                    tabValid = false;
                } else if (sub.action === 'subtract' && sub.selected_qty > sub.owned_qty) {
                    errors.push(`Tab ${i+1}: Sub-item "${sub.name}" requested subtraction (${sub.selected_qty}) exceeds owned (${sub.owned_qty}).`);
                    tabValid = false;
                } else {
                    subItemsPayload.push({ id: sub.id, qty: sub.selected_qty, action: sub.action });
                }
            });

            if(tabValid) {
                payload = {
                    tab_id: `tab_${i}`,
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
                html: `<div class="text-sm text-slate-500 mt-2 font-medium leading-relaxed">Distribute <span class="font-black text-rose-600 text-lg mx-1">${total}</span> asset(s) to <span class="font-black text-slate-800 mx-1">${distTabsData[tabIndex].school_name}</span>?</div>`, 
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
                const response = await fetch("{{ route('inventory.modifier.distribution') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ distributions: payload })
                });
                const result = await response.json();
                
                if(response.ok) {
                    // If a single tab was distributed (not batch), remove it and stay on page
                    if (completedTabIndex !== undefined && completedTabIndex !== null && distTabsData.length > 1) {
                        // Deduct distributed quantities from local rawSubItems data
                        const tab = distTabsData[completedTabIndex];
                        if (tab) {
                            tab.subItemsSelected.forEach(sub => {
                                const localSub = rawSubItems.find(s => s.id === sub.id);
                                if (localSub) {
                                    localSub.quantity = Math.max(0, localSub.quantity - sub.selected_qty);
                                }
                            });
                        }

                        // Remove the completed tab
                        distTabsData.splice(completedTabIndex, 1);
                        // Re-index tabs
                        distTabsData.forEach((t, i) => t.tabIndex = i);
                        // Also update the preSelectedSchools array to stay in sync
                        preSelectedSchools.splice(completedTabIndex, 1);

                        Swal.fire({ 
                            title: 'Success!', text: result.message, icon: 'success', 
                            confirmButtonColor: '#10b981', 
                            customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } 
                        }).then(() => {
                            // Re-render tabs UI (creates fresh empty DOM)
                            renderTabsUI();

                            // Restore each remaining tab's visual state from distTabsData
                            distTabsData.forEach((t, i) => {
                                // Restore category selection
                                if (t.category_id) {
                                    const cat = rawCategories.find(c => c.id === t.category_id);
                                    if (cat) {
                                        document.getElementById(`tabCatSearch_${i}`).value = cat.name;
                                    }
                                    // Enable item search
                                    const itemSearch = document.getElementById(`tabItemSearch_${i}`);
                                    if (itemSearch) itemSearch.disabled = false;
                                }

                                // Restore item selection
                                if (t.item_id) {
                                    const item = rawItems.find(x => x.id === t.item_id);
                                    if (item) {
                                        document.getElementById(`tabItemSearch_${i}`).value = item.name;
                                    }
                                    // Enable sub-item search
                                    const subSearch = document.getElementById(`tabSubSearch_${i}`);
                                    if (subSearch) subSearch.disabled = false;
                                }

                                // Restore sub-item cards with quantities
                                if (t.subItemsSelected.length > 0) {
                                    renderTabSubItems(i);
                                }
                            });

                            // Switch to the next available tab
                            switchTab(Math.min(completedTabIndex, distTabsData.length - 1));
                            updateReadyStatus();
                        });
                    } else {
                        // Batch distribute or last remaining tab — reload to reset everything
                        Swal.fire({ title: 'Success!', text: result.message, icon: 'success', confirmButtonColor: '#10b981', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } })
                        .then(() => { location.reload(); });
                    }
                } else {
                    Swal.fire({ title: 'Error', text: result.message || 'An error occurred during distribution.', icon: 'error', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                }
            } catch(e) {
                Swal.fire({ title: 'Submission Failed', text: e.message, icon: 'error', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
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
