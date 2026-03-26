<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Recipient | DepEd ZC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .custom-scroll::-webkit-scrollbar { width: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        
        .main-card { border-radius: 3.5rem; }
        .input-style { 
            background-color: #f8fafc; 
            border: 1px solid #f1f5f9;
            border-radius: 1.25rem;
        }
        .input-style:focus {
            background-color: white;
            border-color: #c00000;
            box-shadow: 0 0 0 4px rgba(192, 0, 0, 0.05);
        }

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
        <main class="p-6 lg:p-14">
            <header class="flex justify-between items-center mb-12 max-w-3xl mx-auto w-full px-4">
                <div>
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight italic uppercase">Inventory Setup</h2>
                    <p class="text-slate-500 text-sm font-medium italic">Register New Recipient</p>
                </div>
                <a href="{{ url('/inventory-setup?step=2&mode=add') }}" class="px-6 py-3 back-btn-cool rounded-2xl text-sm font-bold text-slate-600 flex items-center gap-2 shadow-sm active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                    Back
                </a>
            </header>

            <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                <div class="lg:col-span-2 bg-white p-12 main-card shadow-[0_20px_50px_rgba(0,0,0,0.05)] border border-slate-50 relative">
                    <h1 class="text-4xl font-black text-slate-900 mb-8 italic uppercase tracking-tighter">Register Recipient</h1>

                    @if(session('success'))
                        <div class="mb-8 bg-emerald-50 border border-emerald-200 text-emerald-700 px-6 py-4 rounded-2xl font-bold flex items-center gap-3 animate-in fade-in slide-in-from-top-4 duration-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            {{ session('success') }}
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="mb-8 bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-2xl font-bold animate-in fade-in slide-in-from-top-4 duration-500">
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="stakeholderForm" action="{{ route('inventory.setup.store_distributor_group') }}" method="POST" class="space-y-10">
                    @csrf
                    <input type="hidden" name="type" value="Recipient">
                    
                    <div class="space-y-3 relative">
                        <div class="flex items-center justify-between">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.15em] ml-1">Main Organization / School</label>
                            <span id="orgStatusBadge" class="hidden px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-widest"></span>
                        </div>
                        <input type="text" id="orgNameInput" name="org_name" placeholder="e.g. Ayala National High School" class="w-full p-5 input-style outline-none font-bold text-slate-700 transition-all" autocomplete="off" onfocus="filterOrgNames()" oninput="filterOrgNames()">
                        <div id="orgDropdown" class="hidden absolute z-30 w-full mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-[250px] overflow-y-auto custom-scroll"></div>
                    </div>

                    <div class="space-y-6 pt-6 border-t border-slate-100">
                        <div class="flex justify-between items-end ml-1">
                            <div>
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.15em]">Personnel / Rooms</label>
                                <p class="text-[10px] text-slate-300 font-bold mt-1 italic">Add teachers or specific room locations</p>
                            </div>
                            <button type="button" onclick="addRecipientField()" class="px-4 py-2 bg-slate-50 text-slate-500 text-[10px] font-black uppercase rounded-xl hover:bg-red-50 hover:text-[#c00000] transition-all tracking-wider">+ Add Recipient</button>
                        </div>

                        <div id="recipientContainer" class="space-y-3 max-h-[300px] overflow-y-auto pr-2 custom-scroll">
                            <div class="flex items-center gap-3 group animate-in fade-in duration-300 relative">
                                <input type="text" name="personnel[]" placeholder="e.g. Science Lab 1" class="flex-grow p-5 input-style outline-none font-bold text-slate-700 text-sm transition-all pr-24" onkeyup="checkSubCategory(this)">
                                <span class="sub-badge hidden absolute right-[4.5rem] top-1/2 -translate-y-1/2 px-2 py-1 rounded-md text-[9px] font-black uppercase tracking-widest"></span>
                                <button type="button" onclick="this.parentElement.remove()" class="w-12 h-12 flex items-center justify-center text-slate-200 hover:text-red-500 transition-colors relative z-10">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="button" onclick="confirmRegistration()" class="w-full py-6 bg-[#c00000] hover:bg-[#a00000] text-white rounded-[1.5rem] font-bold text-lg shadow-xl shadow-red-100 transition-all hover:scale-[1.01] active:scale-[0.98]">
                        Register Recipient
                    </button>
                </form>
                </div>
                
                <div class="lg:col-span-1 bg-white p-8 main-card shadow-[0_20px_50px_rgba(0,0,0,0.05)] border border-slate-50 relative sticky top-8">
                    <h2 class="text-2xl font-black text-slate-900 mb-3 italic uppercase tracking-tighter leading-tight drop-shadow-sm">Also Add Existing<br><span class="text-[#c00000]">Distributors</span></h2>
                    <p class="text-[11px] font-bold text-slate-400 mb-6 leading-relaxed">Select existing Distributors below to have them automatically copied and registered as Recipients alongside your new entry.</p>

                    <div class="space-y-3 max-h-[600px] overflow-y-auto pr-2 pb-48 custom-scroll">
                        @forelse($oppositeMains as $main)
                            <div class="border border-slate-100 rounded-xl transition-all hover:border-red-100 relative bg-white">
                                <label class="flex items-center gap-3 p-4 bg-slate-50 hover:bg-red-50 cursor-pointer transition-colors group rounded-t-xl {{ $oppositeSubs->where('parent_id', $main->id)->count() === 0 ? 'rounded-b-xl' : '' }}">
                                    <input type="checkbox" name="copy_parents[]" value="{{ $main->id }}" form="stakeholderForm" class="w-4 h-4 text-[#c00000] border-slate-300 rounded focus:ring-[#c00000] cursor-pointer transition-all" onchange="toggleSubcategories(this, {{ $main->id }})">
                                    <span class="text-xs font-black text-slate-700 uppercase tracking-wider group-hover:text-[#c00000] transition-colors truncate">{{ $main->name }}</span>
                                </label>
                                
                                <div id="sub_list_{{ $main->id }}" class="hidden bg-slate-50/50 p-4 border-t border-slate-100">
                                    @php
                                        $subs = $oppositeSubs->where('parent_id', $main->id);
                                    @endphp
                                    @if($subs->count() > 0)
                                        <div class="relative group/dropdown">
                                            <button type="button" class="w-full flex items-center justify-between bg-white p-3 rounded-xl border border-slate-200 text-[11px] font-bold text-slate-600 hover:border-slate-300 transition-colors shadow-sm" onclick="toggleDropdown(this)">
                                                <span class="sub-count-{{ $main->id }}">0 / {{ $subs->count() }} Selected</span>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            <div class="hidden absolute z-50 left-0 right-0 mt-2 bg-white border border-slate-200 shadow-[0_10px_40px_rgba(0,0,0,0.1)] rounded-xl p-2 dropdown-menu">
                                                <div class="sticky top-0 bg-white pb-2 z-10 border-b border-slate-100 mb-2">
                                                    <input type="text" placeholder="Search {{ $subs->count() }} items..." class="w-full text-[10px] p-2.5 bg-slate-50 border border-slate-100 rounded-lg outline-none focus:border-red-200 focus:bg-white transition-all font-bold text-slate-600 placeholder:text-slate-400" onkeyup="filterDropdown(this)">
                                                </div>
                                                <div class="space-y-0.5 max-h-48 overflow-y-auto custom-scroll pr-1">
                                                    @foreach($subs as $sub)
                                                        <label class="flex items-center gap-3 cursor-pointer group py-2 px-3 hover:bg-slate-50 rounded-lg transition-colors">
                                                            <input type="checkbox" name="copy_children[]" value="{{ $sub->id }}" form="stakeholderForm" class="w-4 h-4 text-[#c00000] border-slate-300 rounded focus:ring-[#c00000] cursor-pointer sub-checkbox-{{ $main->id }} transition-all" onchange="updateSubCount({{ $main->id }}, {{ $subs->count() }})">
                                                            <span class="text-[11px] font-bold text-slate-600 group-hover:text-slate-900 transition-colors truncate searchable-text">{{ $sub->name }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <p class="text-[10px] italic font-bold text-slate-400 text-center py-2">No structured personnel/sub-categories.</p>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-center border-2 border-dashed border-slate-100 rounded-2xl">
                                <p class="text-xs font-bold text-slate-400 italic">No existing Distributors found.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        const existingRecipients = @json($recipients ?? []);
        const existingSubRecipients = @json($subRecipients ?? []);

        function filterOrgNames() {
            const dropdown = document.getElementById('orgDropdown');
            const input = document.getElementById('orgNameInput');
            const badge = document.getElementById('orgStatusBadge');
            const query = input.value.trim().toLowerCase();
            
            dropdown.classList.remove('hidden');
            
            // Check for exact match
            const exactMatch = existingRecipients.some(r => r.name.toLowerCase() === query);
            
            if (query === '') {
                badge.className = 'hidden';
            } else if (exactMatch) {
                badge.className = 'px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all bg-emerald-50 text-emerald-600 border border-emerald-200';
                badge.textContent = 'Existing';
            } else {
                badge.className = 'px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all bg-amber-50 text-amber-600 border border-amber-200';
                badge.textContent = 'New Entry';
            }
            
            // Re-check all personnel fields as parent context changed
            document.querySelectorAll('input[name="personnel[]"]').forEach(input => checkSubCategory(input));
            
            const filtered = existingRecipients.filter(r => r.name.toLowerCase().includes(query)).slice(0, 30);
            
            if (filtered.length === 0) {
                dropdown.innerHTML = '<div class="px-4 py-4 text-sm font-bold text-slate-400 text-center italic">Type to create a new organization/school</div>';
            } else {
                let html = '<div class="p-3 text-[10px] text-slate-400 font-extrabold uppercase tracking-widest sticky top-0 bg-white/90 backdrop-blur border-b border-slate-100 z-10">Select existing or type new</div>';
                html += filtered.map(r => `
                    <div onclick="selectOrg('${r.name.replace(/'/g,"\\'")}')" class="px-4 py-3 text-sm font-bold text-slate-700 hover:bg-red-50 hover:text-[#c00000] cursor-pointer transition-colors border-b border-slate-50 last:border-0 truncate">
                        ${r.name}
                    </div>
                `).join('');
                dropdown.innerHTML = html;
            }
        }

        function selectOrg(name) {
            const input = document.getElementById('orgNameInput');
            input.value = name;
            document.getElementById('orgDropdown').classList.add('hidden');
            filterOrgNames(); // trigger badge update
        }

        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('orgDropdown');
            const input = document.getElementById('orgNameInput');
            if (e.target !== input && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });

        function addRecipientField() {
            const container = document.getElementById('recipientContainer');
            const div = document.createElement('div');
            div.className = "flex items-center gap-3 group animate-in fade-in slide-in-from-top-2 duration-300 relative";
            div.innerHTML = `
                <input type="text" name="personnel[]" placeholder="e.g. Science Lab 1" class="flex-grow p-5 input-style outline-none font-bold text-slate-700 text-sm transition-all pr-24" required onkeyup="checkSubCategory(this)">
                <span class="sub-badge hidden absolute right-[4.5rem] top-1/2 -translate-y-1/2 px-2 py-1 rounded-md text-[9px] font-black uppercase tracking-widest"></span>
                <button type="button" onclick="this.parentElement.remove()" class="w-12 h-12 flex items-center justify-center text-slate-200 hover:text-red-500 transition-colors relative z-10">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            `;
            container.appendChild(div);
            container.scrollTop = container.scrollHeight;
        }

        function checkSubCategory(inputElement) {
            const badge = inputElement.parentElement.querySelector('.sub-badge');
            const parentName = document.getElementById('orgNameInput').value.trim().toLowerCase();
            const childName = inputElement.value.trim().toLowerCase();
            
            if (childName === '') {
                badge.className = 'hidden';
                return;
            }

            const parentObj = existingRecipients.find(r => r.name.toLowerCase() === parentName);
            
            if (!parentObj) {
                badge.className = 'absolute right-[4.5rem] top-1/2 -translate-y-1/2 px-2 py-1 rounded-md text-[9px] font-bold tracking-widest transition-all bg-amber-50 text-amber-600 border border-amber-200 uppercase';
                badge.textContent = 'New Entry';
                return;
            }
            
            const exactMatch = existingSubRecipients.some(sub => sub.parent_id === parentObj.id && sub.name.toLowerCase() === childName);
            
            if (exactMatch) {
                 badge.className = 'absolute right-[4.5rem] top-1/2 -translate-y-1/2 px-2 py-1 rounded-md text-[9px] font-bold tracking-widest transition-all bg-emerald-50 text-emerald-600 border border-emerald-200 uppercase';
                 badge.textContent = 'Existing';
            } else {
                 badge.className = 'absolute right-[4.5rem] top-1/2 -translate-y-1/2 px-2 py-1 rounded-md text-[9px] font-bold tracking-widest transition-all bg-amber-50 text-amber-600 border border-amber-200 uppercase';
                 badge.textContent = 'New Entry';
            }
        }

        function toggleSubcategories(checkbox, id) {
            const subList = document.getElementById('sub_list_' + id);
            if (subList) {
                if (checkbox.checked) {
                    subList.classList.remove('hidden');
                    // Automatically check all children when parent is checked
                    const children = document.querySelectorAll('.sub-checkbox-' + id);
                    children.forEach(child => child.checked = true);
                    updateSubCount(id, children.length);
                } else {
                    subList.classList.add('hidden');
                    // Uncheck all children when parent is unchecked
                    const children = document.querySelectorAll('.sub-checkbox-' + id);
                    children.forEach(child => child.checked = false);
                    updateSubCount(id, children.length);
                }
            }
        }

        function toggleDropdown(button) {
            const menu = button.nextElementSibling;
            const svgs = button.querySelectorAll('svg');
            
            if (menu.classList.contains('hidden')) {
                // Close other dropdowns
                document.querySelectorAll('.dropdown-menu').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('button svg').forEach(el => el.classList.remove('rotate-180'));
                
                menu.classList.remove('hidden');
                svgs.forEach(svg => svg.classList.add('rotate-180'));
                // Focus the search box
                setTimeout(() => menu.querySelector('input').focus(), 50);
            } else {
                menu.classList.add('hidden');
                svgs.forEach(svg => svg.classList.remove('rotate-180'));
            }
        }

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.group\\/dropdown')) {
                document.querySelectorAll('.dropdown-menu').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('.group\\/dropdown button svg').forEach(el => el.classList.remove('rotate-180'));
            }
        });

        function filterDropdown(input) {
            const query = input.value.toLowerCase();
            const labels = input.parentElement.nextElementSibling.querySelectorAll('label');
            labels.forEach(label => {
                const text = label.querySelector('.searchable-text').textContent.toLowerCase();
                if (text.includes(query)) {
                    label.classList.remove('hidden');
                    label.classList.add('flex');
                } else {
                    label.classList.add('hidden');
                    label.classList.remove('flex');
                }
            });
        }

        function updateSubCount(id, totalCount) {
            const checkboxes = document.querySelectorAll('.sub-checkbox-' + id);
            const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
            const span = document.querySelector('.sub-count-' + id);
            if (span && totalCount > 0) {
                if (checkedCount === totalCount) {
                    span.textContent = `All Selected (${totalCount})`;
                    span.className = `sub-count-${id} text-[#c00000]`;
                } else if (checkedCount === 0) {
                    span.textContent = `None Selected (0/${totalCount})`;
                    span.className = `sub-count-${id} text-slate-400`;
                } else {
                    span.textContent = `${checkedCount} / ${totalCount} Selected`;
                    span.className = `sub-count-${id} text-[#c00000]`;
                }
            }
        }
        function confirmRegistration() {
            const form = document.getElementById('stakeholderForm');
            const orgName = form.querySelector('[name="org_name"]').value.trim();
            const personnelInputs = form.querySelectorAll('[name="personnel[]"]');
            const checkedParents = document.querySelectorAll('[name="copy_parents[]"]:checked');
            const checkedChildren = document.querySelectorAll('[name="copy_children[]"]:checked');

            const personnel = Array.from(personnelInputs).map(i => i.value.trim()).filter(v => v);
            const hasCross = checkedParents.length > 0 || checkedChildren.length > 0;

            if (!orgName && !hasCross) {
                Swal.fire({ title: 'Nothing to Register', text: 'Please enter a main organization or select existing Distributors to cross-register.', icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' } });
                return;
            }

            let summaryLines = [];
            if (orgName) {
                summaryLines.push(`<div class="flex items-start gap-2"><span class="text-[#c00000] font-bold">•</span> New Recipient: <strong>${orgName}</strong></div>`);
                if (personnel.length > 0) {
                    summaryLines.push(`<div class="flex items-start gap-2"><span class="text-[#c00000] font-bold">•</span> ${personnel.length} sub-category/personnel</div>`);
                }
            }
            if (hasCross) {
                const parentLabels = Array.from(checkedParents).map(cb => cb.closest('label').querySelector('span').textContent.trim());
                summaryLines.push(`<div class="flex items-start gap-2 mt-2"><span class="text-emerald-600 font-bold">+</span> Cross-register ${parentLabels.length} Distributor(s) as Recipients:</div>`);
                parentLabels.forEach(name => {
                    summaryLines.push(`<div class="ml-5 text-slate-500">↳ ${name}</div>`);
                });
                if (checkedChildren.length > 0) {
                    summaryLines.push(`<div class="flex items-start gap-2"><span class="text-emerald-600 font-bold">+</span> Including ${checkedChildren.length} sub-categories</div>`);
                }
            }

            Swal.fire({
                title: 'Confirm Registration',
                html: `<div class="text-left text-sm text-slate-600 leading-relaxed space-y-1 font-medium">${summaryLines.join('')}</div>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Register',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#c00000',
                cancelButtonColor: '#64748b',
                customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6', cancelButton: 'rounded-xl font-bold px-6' }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    </script>
</body>
</html>