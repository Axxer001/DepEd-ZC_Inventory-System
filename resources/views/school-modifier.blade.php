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

            @if(session('success'))
                <div class="max-w-xl mx-auto mb-6 bg-green-50 text-green-700 p-6 font-bold rounded-3xl shadow-sm border border-green-100 flex items-start gap-4">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 text-green-500 shrink-0">
                        <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
                    </svg>
                    <div class="mt-1 flex-1">
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            <div class="max-w-2xl mx-auto bg-white p-10 rounded-[3rem] shadow-2xl border border-slate-50 relative overflow-visible">
                <h4 class="text-2xl font-black text-slate-800 mb-8 uppercase tracking-tight italic">Modify School</h4>

                <div class="flex gap-4 mb-8">
                    <button type="button" id="editModeUpdateBtn" onclick="setEditAction('update')" class="flex-1 py-4 rounded-2xl font-bold text-sm transition-all border-2 border-[#c00000] bg-red-50 text-[#c00000]">
                        ✏️ Update / Rename
                    </button>
                    <button type="button" id="editModeDeleteBtn" onclick="setEditAction('delete')" class="flex-1 py-4 rounded-2xl font-bold text-sm transition-all border-2 border-slate-200 bg-white text-slate-400 hover:border-red-300 hover:text-red-400">
                        🗑️ Delete
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
                        <div id="deleteImpactBox" class="bg-red-50 border border-red-200 p-6 rounded-3xl hidden transition-all">
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
            const renameSec = document.getElementById('renameSection');
            const warnWrap  = document.getElementById('deleteWarningWrap');
            const submitBtn = document.getElementById('submitBtn');
            const delBtn    = document.getElementById('deleteSubmitBtn');

            if (action === 'update') {
                updateBtn.className = 'flex-1 py-4 rounded-2xl font-bold text-sm transition-all border-2 border-[#c00000] bg-red-50 text-[#c00000]';
                deleteBtn.className = 'flex-1 py-4 rounded-2xl font-bold text-sm transition-all border-2 border-slate-200 bg-white text-slate-400 hover:border-red-300 hover:text-red-400';
                
                renameSec.classList.remove('hidden');
                submitBtn.classList.remove('hidden');
                warnWrap.classList.add('hidden');
                delBtn.classList.add('hidden');
            } else {
                updateBtn.className = 'flex-1 py-4 rounded-2xl font-bold text-sm transition-all border-2 border-slate-200 bg-white text-slate-400 hover:border-red-300 hover:text-red-400';
                deleteBtn.className = 'flex-1 py-4 rounded-2xl font-bold text-sm transition-all border-2 border-red-600 bg-red-50 text-red-600';
                
                renameSec.classList.add('hidden');
                submitBtn.classList.add('hidden');
                if (selectedSchool) {
                    warnWrap.classList.remove('hidden');
                    delBtn.classList.remove('hidden');
                    previewDeleteImpact();
                }
            }
        }

        function triggerActionUI() {
            if (currentEditAction === 'update') {
                document.getElementById('renameSection').classList.remove('hidden');
                document.getElementById('submitBtn').classList.remove('hidden');
                document.getElementById('deleteWarningWrap').classList.add('hidden');
                document.getElementById('deleteSubmitBtn').classList.add('hidden');
            } else {
                document.getElementById('renameSection').classList.add('hidden');
                document.getElementById('submitBtn').classList.add('hidden');
                document.getElementById('deleteWarningWrap').classList.remove('hidden');
                document.getElementById('deleteSubmitBtn').classList.remove('hidden');
                previewDeleteImpact();
            }
        }

        function filterSchools() {
            const query = document.getElementById('schoolSearchDropdown').value.toLowerCase();
            const list = document.getElementById('schoolDropdownList');
            
            if (!query) {
                list.classList.add('hidden');
                return;
            }

            const filtered = allSchools.filter(s => 
                s.name.toLowerCase().includes(query) || 
                (s.school_id && s.school_id.toLowerCase().includes(query))
            );

            if (filtered.length > 0) {
                list.innerHTML = filtered.map(s => `
                    <div onclick="selectSchool(${s.id}, '${s.school_id.replace(/'/g, "\\'")}', '${s.name.replace(/'/g, "\\'")}')" class="px-5 py-3 hover:bg-red-50 cursor-pointer border-b border-slate-50 last:border-0 transition-colors">
                        <div class="font-bold text-sm text-slate-800">${s.name}</div>
                        <div class="text-[10px] font-black text-slate-400 tracking-wider">ID: ${s.school_id} • ${s.district_name || 'N/A'}</div>
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
            submitBtn.classList.remove('bg-slate-200', 'text-slate-400', 'shadow-none', 'disabled:cursor-not-allowed');
            submitBtn.classList.add('bg-[#c00000]', 'hover:bg-red-700', 'text-white', 'shadow-lg', 'shadow-red-100', 'hover:-translate-y-1', 'active:scale-95');

            triggerActionUI();
        }

        async function previewDeleteImpact() {
            if (!selectedSchool) return;

            const impactBox = document.getElementById('deleteImpactBox');
            const impactTxt = document.getElementById('deleteImpactDetails');
            
            impactBox.classList.remove('hidden');
            impactTxt.innerHTML = '<span class="text-slate-500 animate-pulse">Calculating impact...</span>';
            
            try {
                const res = await fetch("{{ route('inventory.setup.preview_delete') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ type: 'school', id: selectedSchool.id })
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    const i = data.impact;
                    let msgs = [];
                    if (i.ownerships > 0) msgs.push(`• <b>${i.ownerships}</b> distributed asset(s) will be recovered`);
                    
                    if (msgs.length === 0) {
                        impactTxt.innerHTML = '<span class="text-emerald-600 font-bold">Safe to delete: This school currently owns no assets.</span>';
                        impactBox.classList.replace('bg-red-50', 'bg-emerald-50');
                        impactBox.classList.replace('border-red-200', 'border-emerald-200');
                    } else {
                        impactTxt.innerHTML = `This action will instantly delete the school <b>"${selectedSchool.name}"</b> AND all its <b>distributed assets</b> will be returned to the Master Registry as available stock:<br>` + msgs.join('<br>');
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
