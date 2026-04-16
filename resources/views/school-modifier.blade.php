<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update School | DepEd Zamboanga City</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .custom-scroll::-webkit-scrollbar { width: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .custom-scroll::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
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
                <button onclick="window.location.href='/inventory-setup?step=2&mode=edit'" class="px-6 py-3 back-btn-cool rounded-2xl text-sm font-bold text-slate-600 flex items-center gap-2 shadow-sm active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Back
                </button>
            </header>

            @if($errors->any())
                <div class="max-w-xl mx-auto mb-6 bg-red-50 text-red-600 p-6 font-bold rounded-3xl shadow-sm border border-red-100 flex items-start gap-4">
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
                <h4 class="text-2xl font-black text-slate-800 mb-8 uppercase tracking-tight italic">Modify School</h4>
                
                <div class="flex gap-4 mb-8">
                    <button type="button" id="editModeUpdateBtn" onclick="setEditAction('update')" class="flex items-center justify-center gap-2 flex-1 py-4 rounded-2xl font-bold text-sm transition-all border-2 border-[#c00000] bg-red-50 text-[#c00000]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                        Update / Rename
                    </button>

                    <button type="button" id="editModeDeleteBtn" onclick="setEditAction('delete')" class="flex items-center justify-center gap-2 flex-1 py-4 rounded-2xl font-bold text-sm transition-all border-2 border-slate-200 bg-white text-slate-400 hover:border-red-300 hover:text-red-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            <line x1="10" y1="11" x2="10" y2="17"></line>
                            <line x1="14" y1="11" x2="14" y2="17"></line>
                        </svg>
                        Delete
                    </button>
                </div>

                <form id="updateSchoolForm" action="{{ route('inventory.modifier.school') }}" method="POST" class="space-y-6">
                    @csrf
                    <input type="hidden" name="id" id="schoolIdInput">

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Search School <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="text" id="schoolSearchDropdown" placeholder="Search school name or ID..." class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold transition-all focus:border-[#c00000]" autocomplete="off" oninput="filterSchools()" onfocus="filterSchools()">
                            <div id="schoolDropdownList" class="hidden absolute z-30 w-full mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-[200px] overflow-y-auto custom-scroll"></div>
                        </div>
                    </div>

                    <div id="renameSection" class="opacity-50 transition-opacity duration-300 space-y-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">New School ID (6-Digits) <span class="text-red-500">*</span></label>
                            <input type="text" name="new_school_id" id="newSchoolIdInput" placeholder="e.g. 123456" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold transition-all focus:border-[#c00000] disabled:cursor-not-allowed" disabled required>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">New School Name <span class="text-red-500">*</span></label>
                            <input type="text" name="new_school_name" id="newSchoolNameInput" placeholder="e.g. Ayala National High School" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold transition-all focus:border-[#c00000] disabled:cursor-not-allowed" disabled required>
                        </div>
                    </div>

                    <div id="deleteWarningWrap" class="hidden mt-6">
                        <div id="deleteImpactBox" class="bg-red-50 border border-red-200 p-6 rounded-3xl transition-all">
                            <h5 class="text-red-600 font-black text-xs uppercase tracking-widest flex items-center gap-2 mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                DELETION IMPACT
                            </h5>
                            <p id="deleteImpactDetails" class="text-xs font-semibold text-red-800/80 leading-relaxed space-y-1"></p>
                        </div>
                    </div>

                    <button type="button" id="submitBtn" onclick="confirmUpdate()" class="w-full py-4 mt-8 bg-slate-200 text-slate-400 rounded-[1.5rem] font-black shadow-none transition-transform text-sm disabled:cursor-not-allowed" disabled>Update School</button>
                    
                    <button type="button" id="deleteSubmitBtn" onclick="submitDelete()" class="hidden w-full py-5 mt-8 bg-red-600 hover:bg-red-800 shadow-red-200 text-white rounded-3xl font-bold shadow-xl transition-all hover:-translate-y-1 active:scale-95">
                        🗑️ Permanently Delete Record
                    </button>
                </form>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const allSchools = @json($allSchools);
        let selectedSchool = null;
        let currentEditAction = 'update';

        function setEditAction(action) {
            currentEditAction = action;
            const updateBtn = document.getElementById('editModeUpdateBtn');
            const deleteBtn = document.getElementById('editModeDeleteBtn');
            
            if (action === 'update') {
                // Update button active style
                updateBtn.classList.add('border-[#c00000]', 'bg-red-50', 'text-[#c00000]');
                updateBtn.classList.remove('border-slate-200', 'bg-white', 'text-slate-400');
                
                // Delete button inactive style
                deleteBtn.classList.remove('border-red-600', 'text-red-600');
                deleteBtn.classList.add('border-slate-200', 'bg-white', 'text-slate-400');
            } else {
                // Delete button active style
                deleteBtn.classList.add('border-red-600', 'bg-red-50', 'text-red-600');
                deleteBtn.classList.remove('border-slate-200', 'bg-white', 'text-slate-400');
                
                // Update button inactive style
                updateBtn.classList.remove('border-[#c00000]', 'bg-red-50', 'text-[#c00000]');
                updateBtn.classList.add('border-slate-200', 'bg-white', 'text-slate-400');
            }

            triggerActionUI();
        }

        function triggerActionUI() {
            const renameSec = document.getElementById('renameSection');
            const warnWrap  = document.getElementById('deleteWarningWrap');
            const submitBtn = document.getElementById('submitBtn');
            const delBtn    = document.getElementById('deleteSubmitBtn');

            if (currentEditAction === 'update') {
                renameSec.classList.remove('hidden');
                submitBtn.classList.remove('hidden');
                warnWrap.classList.add('hidden');
                delBtn.classList.add('hidden');
            } else {
                renameSec.classList.add('hidden');
                submitBtn.classList.add('hidden');
                
                if (selectedSchool) {
                    warnWrap.classList.remove('hidden');
                    delBtn.classList.remove('hidden');
                    previewDeleteImpact();
                }
            }
        }

        function filterSchools() {
            const query = document.getElementById('schoolSearchDropdown').value.toLowerCase();
            const list = document.getElementById('schoolDropdownList');
            if (!query) { list.classList.add('hidden'); return; }

            const filtered = allSchools.filter(s => 
                s.name.toLowerCase().includes(query) || 
                (s.school_id && s.school_id.toLowerCase().includes(query))
            );

            if (filtered.length > 0) {
                list.innerHTML = filtered.map(s => `
                    <div onclick="selectSchool(${s.id}, '${s.school_id.replace(/'/g, "\\'")}', '${s.name.replace(/'/g, "\\'")}')" class="px-5 py-3 hover:bg-red-50 cursor-pointer border-b border-slate-50 last:border-0 transition-colors">
                        <div class="font-bold text-sm text-slate-800">${s.name}</div>
                        <div class="text-[10px] font-black text-slate-400 tracking-wider">ID: ${s.school_id}</div>
                    </div>
                `).join('');
                list.classList.remove('hidden');
            } else {
                list.innerHTML = `<div class="px-5 py-3 text-xs font-bold text-slate-400 italic text-center">No schools found</div>`;
                list.classList.remove('hidden');
            }
        }

        function selectSchool(id, schoolId, name) {
            selectedSchool = { id, schoolId, name };
            document.getElementById('schoolSearchDropdown').value = name;
            document.getElementById('schoolDropdownList').classList.add('hidden');
            document.getElementById('schoolIdInput').value = id;
            
            const newIdInput = document.getElementById('newSchoolIdInput');
            const newNameInput = document.getElementById('newSchoolNameInput');
            const submitBtn = document.getElementById('submitBtn');
            const section = document.getElementById('renameSection');

            newIdInput.value = schoolId;
            newNameInput.value = name;
            newIdInput.disabled = false;
            newNameInput.disabled = false;
            submitBtn.disabled = false;

            section.classList.remove('opacity-50');
            submitBtn.classList.remove('bg-slate-200', 'text-slate-400');
            submitBtn.classList.add('bg-[#c00000]', 'text-white', 'shadow-lg');

            triggerActionUI();
        }

        async function previewDeleteImpact() {
            if (!selectedSchool) return;
            const impactTxt = document.getElementById('deleteImpactDetails');
            impactTxt.innerHTML = '<span class="text-slate-500 animate-pulse">Calculating impact...</span>';
            
            try {
                const res = await fetch("{{ route('inventory.setup.preview_delete') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ type: 'school', id: selectedSchool.id })
                });
                const data = await res.json();
                if (data.success) {
                    impactTxt.innerHTML = data.impact.ownerships > 0 
                        ? `This will delete <b>"${selectedSchool.name}"</b> and recover <b>${data.impact.ownerships}</b> assets.`
                        : '<span class="text-emerald-600 font-bold">Safe to delete: No assets owned.</span>';
                }
            } catch (e) { impactTxt.innerHTML = 'Error calculating impact.'; }
        }

        async function submitDelete() {
            if (!selectedSchool) return;
            
            const result = await Swal.fire({
                title: 'SCHOOL DELETION',
                html: `Are you absolutely sure you want to permanently delete the School <b>"[${selectedSchool.schoolId}] ${selectedSchool.name}"</b>?<br><br><span class="text-slate-600 font-bold text-sm italic">Any distributed assets owned by this school will be safely returned to the Master Registry available stock.</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#c00000', cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, permanently delete it',
                cancelButtonText: 'Cancel',
                customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6', cancelButton: 'rounded-xl font-bold px-6' }
            });

            if (!result.isConfirmed) return;

            Swal.fire({ title: 'Deleting School...', allowOutsideClick: false, showConfirmButton: false, willOpen: () => Swal.showLoading(), customClass: { popup: 'rounded-[2rem]' } });

            try {
                const res = await fetch("{{ route('inventory.setup.delete') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ type: 'school', id: selectedSchool.id })
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    Swal.fire({ title: 'Deleted!', text: data.message, icon: 'success', confirmButtonColor: '#10b981', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } })
                    .then(() => { location.reload(); });
                } else {
                    Swal.fire({ title: 'Error', text: data.message || 'An error occurred.', icon: 'error', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                }
            } catch(e) {
                Swal.fire({ title: 'Request Failed', text: e.message, icon: 'error', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
            }
        }

        function confirmUpdate() {
            if(!selectedSchool) return;

            const newId = document.getElementById('newSchoolIdInput').value.trim();
            const newName = document.getElementById('newSchoolNameInput').value.trim();

            if (!newId || !newName) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Incomplete Fields',
                    text: 'Please provide both the School ID and School Name.',
                    confirmButtonColor: '#c00000'
                });
                return;
            }

            Swal.fire({
                title: 'Confirm Update',
                html: `Are you sure you want to update this school?<br><br>
                       <div class="text-xs text-left bg-slate-50 p-4 rounded-xl space-y-2 mt-2 border border-slate-200">
                           <div class="text-slate-500"><b>Old:</b> [${selectedSchool.schoolId}] ${selectedSchool.name}</div>
                           <div class="text-[#c00000] font-bold"><b>New:</b> [${newId}] ${newName}</div>
                       </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#c00000',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Update School',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('updateSchoolForm').submit();
                }
            });
        }

        document.addEventListener('click', function(e) {
            if (!document.getElementById('schoolSearchDropdown').contains(e.target)) {
                document.getElementById('schoolDropdownList').classList.add('hidden');
            }
        });
    </script>
</body>
</html>
