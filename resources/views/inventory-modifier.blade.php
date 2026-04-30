<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Modifier | DepEd Zamboanga City</title>
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

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll">
        <main class="p-6 lg:p-10 max-w-7xl mx-auto w-full">
            <div id="step3" class="step-content active">
                <div id="formContent">
                    {{-- DYNAMIC CONTENT LOADED BY JS --}}
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const rawCategories = {{ Js::from($categories) }};
        const rawItems = {{ Js::from($items) }};
        const rawSubItems = {{ Js::from($subItems) }};
        const allSchoolsList = @json($allSchools);
        const schoolOwnershipsList = @json($schoolOwnerships);

        let preSelectedSchools = [];
        let distTabsData = [];
        let currentActiveTab = 0;

        document.addEventListener('DOMContentLoaded', () => { renderPreSelectionUI(); });

        // ==========================================
        // PHASE 1: RECIPIENT REGISTRY (Two-Column)
        // ==========================================
        function renderPreSelectionUI() {
            const container = document.getElementById('formContent');
            container.innerHTML = `
                <div id="distPreSelectionPhase" class="animate-in fade-in zoom-in duration-500 bg-white p-10 rounded-[3rem] shadow-2xl border border-slate-50">
                    <h4 class="text-3xl font-black text-slate-800 uppercase tracking-tighter italic mb-10">Register Distribution</h4>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                        
                        <div class="lg:col-span-7 space-y-8">
                            <div>
                                <h4 class="text-2xl font-black text-slate-800 uppercase tracking-tight italic">Recipient Registry</h4>
                                <p class="text-slate-400 text-[10px] font-bold uppercase mt-1 tracking-widest">Register new entities to the master list</p>
                            </div>

                            <div class="space-y-6">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Entity Type <span class="text-red-500">*</span></label>
                                    <select id="entityType" class="w-full p-5 bg-slate-50 border border-slate-100 rounded-[1.5rem] outline-none font-bold text-slate-700 cursor-pointer transition-all focus:border-[#c00000] focus:ring-4 focus:ring-red-50">
                                        <option value="school">School</option>
                                        <option value="external">External (Offices / Individuals)</option>
                                    </select>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Search Recipient <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <input type="text" id="preDistSchoolSearch" placeholder="Type name..." 
                                            class="w-full p-5 bg-slate-50 border border-slate-100 rounded-[1.5rem] outline-none font-bold text-slate-700 transition-all focus:border-[#c00000] focus:ring-4 focus:ring-red-100" 
                                            oninput="filterPreDistSchools()">
                                        <div id="preDistSchoolDropdownList" class="hidden absolute z-30 w-full mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-[250px] overflow-y-auto custom-scroll"></div>
                                    </div>
                                </div>

                                <div class="pt-4 space-y-4 border-t border-slate-50">
                                    <div>
                                        <h3 class="font-black text-slate-800 uppercase tracking-tight italic text-base">Personnel Details <span class="text-slate-400 text-sm font-medium italic lowercase">(optional)</span></h3>
                                        <p class="text-[10px] text-slate-400 font-bold uppercase mt-1">Assign an authorized receiver</p>
                                    </div>
                                    <div class="flex flex-col sm:flex-row gap-3">
                                        <input type="text" id="pName" placeholder="Personnel Name" class="flex-grow p-5 bg-slate-50 border border-slate-100 rounded-[1.5rem] outline-none font-bold text-slate-700 text-sm focus:border-[#c00000]">
                                        <input type="text" id="pPos" placeholder="Position" class="w-full sm:w-1/3 p-5 bg-slate-50 border border-slate-100 rounded-[1.5rem] outline-none font-bold text-slate-700 text-sm focus:border-[#c00000]">
                                    </div>
                                </div>

                                <button type="button" onclick="addRecipientToList()" class="w-full py-6 bg-[#c00000] hover:bg-red-700 text-white rounded-[2rem] font-black uppercase tracking-[0.2em] shadow-xl shadow-red-100 transition-all hover:-translate-y-1 active:scale-95 text-lg">
                                    Add Recipient
                                </button>
                            </div>
                        </div>

                        <div class="lg:col-span-5 bg-[#0f172a] p-8 rounded-[3.5rem] shadow-2xl flex flex-col min-h-[520px] relative overflow-hidden">
                            <div class="flex justify-between items-start mb-8">
                                <div>
                                    <h4 class="text-xl font-black text-white uppercase tracking-tight italic">Recipient List</h4>
                                    <p class="text-slate-400 text-[10px] font-bold uppercase mt-1 tracking-widest">Selected targets for modification</p>
                                </div>
                                <span id="distRecipientCount" class="bg-white/10 text-white px-4 py-1 rounded-full text-[10px] font-black uppercase tracking-widest tracking-widest">0 People</span>
                            </div>

                            <div id="preDistSelectedSchoolsContainer" class="space-y-4 flex-grow overflow-y-auto custom-scroll pr-2">
                                <div id="distEmptyState" class="flex flex-col items-center justify-center py-24">
                                    <div class="w-20 h-20 border-2 border-dashed border-white/10 rounded-full flex items-center justify-center mb-6">
                                        <span class="text-4xl text-white/10 font-thin">+</span>
                                    </div>
                                    <p class="text-white/20 text-sm font-black uppercase tracking-[0.3em] italic">List is Empty</p>
                                </div>
                            </div>

                            <div id="proceedBtnContainer" class="hidden pt-8 mt-4 border-t border-white/5 animate-in slide-in-from-bottom-2">
                                <button type="button" onclick="proceedToDistributionTabs()" class="w-full py-5 bg-white text-slate-900 rounded-[1.5rem] font-black uppercase tracking-widest hover:bg-slate-100 transition-all text-xs active:scale-95">
                                    Proceed to Adjust Assets
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // --- Phase 1 Functions ---
        function filterPreDistSchools() {
            const dd = document.getElementById('preDistSchoolDropdownList');
            const q = document.getElementById('preDistSchoolSearch').value.trim().toLowerCase();
            if(!q) { dd.classList.add('hidden'); return; }
            dd.classList.remove('hidden');
            const f = allSchoolsList.filter(s => s.name.toLowerCase().includes(q)).slice(0, 10);
            
            dd.innerHTML = f.map(s => `<div onclick="setSchoolSelection('${s.name.replace(/'/g,"\\'")}', ${s.id})" class="p-4 hover:bg-red-50 font-bold text-sm cursor-pointer border-b border-slate-50 last:border-0">${s.name}</div>`).join('');
        }

        let tempSelection = null;
        function setSchoolSelection(name, id) {
            document.getElementById('preDistSchoolSearch').value = name;
            document.getElementById('preDistSchoolDropdownList').classList.add('hidden');
            tempSelection = { id, name };
        }

        function addRecipientToList() {
            const name = document.getElementById('preDistSchoolSearch').value;
            if(!name) return;
            const pName = document.getElementById('pName').value || 'Authorized Personnel';
            
            preSelectedSchools.push({ 
                id: tempSelection ? tempSelection.id : 0, 
                name: name, 
                pName: pName,
                uid: Date.now() + Math.random()
            });

            document.getElementById('distEmptyState').classList.add('hidden');
            document.getElementById('proceedBtnContainer').classList.remove('hidden');
            
            renderPreSelectedSchools();
            updateCountLabel();

            // Reset fields
            document.getElementById('preDistSchoolSearch').value = '';
            document.getElementById('pName').value = '';
            document.getElementById('pPos').value = '';
            tempSelection = null;
        }

        function renderPreSelectedSchools() {
            const container = document.getElementById('preDistSelectedSchoolsContainer');
            container.innerHTML = preSelectedSchools.map((s) => `
                <div class="px-6 py-5 bg-white/5 border border-white/10 rounded-[1.5rem] flex items-center justify-between group animate-in slide-in-from-top-2">
                    <div class="flex flex-col">
                        <div class="flex items-center gap-2">
                             <span class="text-white font-black text-sm uppercase tracking-tight italic">${s.name}</span>
                             <span class="bg-emerald-500/20 text-emerald-400 text-[8px] font-black px-1.5 py-0.5 rounded uppercase tracking-widest">New</span>
                        </div>
                        <span class="text-slate-500 text-[9px] font-bold uppercase tracking-widest">${s.pName} • Zamboanga City Division</span>
                    </div>
                    <button type="button" onclick="removePreDistSchool(${s.uid})" class="text-white/20 hover:text-red-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            `).join('');
        }

        function removePreDistSchool(uid) {
            preSelectedSchools = preSelectedSchools.filter(s => s.uid !== uid);
            if (preSelectedSchools.length === 0) {
                document.getElementById('distEmptyState').classList.remove('hidden');
                document.getElementById('proceedBtnContainer').classList.add('hidden');
            }
            renderPreSelectedSchools();
            updateCountLabel();
        }

        function updateCountLabel() {
            const el = document.getElementById('distRecipientCount');
            el.textContent = `${preSelectedSchools.length} ${preSelectedSchools.length === 1 ? 'Person' : 'People'}`;
        }

        // ==========================================
        // PHASE 2: ASSIGN ASSETS (Modifier Tabs)
        // ==========================================
        function proceedToDistributionTabs() {
            const container = document.getElementById('formContent');
            distTabsData = preSelectedSchools.map((school, i) => ({
                tabIndex: i, school_id: school.id, school_name: school.name,
                category_id: null, item_id: null, subItemsSelected: []
            }));

            container.innerHTML = `
                <div id="distTabsPhase" class="animate-in fade-in zoom-in duration-500">
                    <div class="flex flex-col md:flex-row justify-between items-center mb-10 gap-4">
                        <div>
                            <h4 class="text-4xl font-black text-slate-800 uppercase tracking-tighter italic leading-none">Assign Assets</h4>
                            <p class="text-slate-400 text-xs font-black uppercase mt-2 tracking-[0.2em]">Allocating to ${preSelectedSchools.length} Recipient(s)</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <button onclick="renderPreSelectionUI()" class="bg-slate-100 text-slate-600 px-6 py-4 rounded-2xl font-bold text-sm hover:bg-slate-200 transition-all flex items-center gap-2">← Back to Registry</button>
                            <button type="button" id="distributeAllBtn" onclick="confirmDistributeAll()" class="bg-red-200 text-white px-8 py-4 rounded-2xl font-black uppercase text-sm tracking-widest shadow-lg transition-all opacity-50 cursor-not-allowed" disabled>Update All</button>
                        </div>
                    </div>

                    <div class="flex flex-col lg:flex-row gap-10">
                        <div class="lg:w-1/4" id="distTabsHeader"></div>
                        <div class="lg:w-3/4 bg-white min-h-[500px]" id="distTabsContentContainer"></div>
                    </div>
                </div>
            `;
            renderTabsUI();
            switchTab(0);
        }

        function renderTabsUI() {
            const header = document.getElementById('distTabsHeader');
            const content = document.getElementById('distTabsContentContainer');
            
            header.innerHTML = distTabsData.map((tab, i) => `
                <button type="button" id="tabBtn_${i}" onclick="switchTab(${i})" 
                    class="w-full text-left p-5 rounded-2xl transition-all border-2 mb-3 ${i === currentActiveTab ? 'bg-red-50 border-red-100 shadow-sm' : 'bg-transparent border-transparent opacity-60'}">
                    <span class="text-[10px] uppercase font-black text-red-300 block mb-1">Tab ${i + 1}</span>
                    <span class="block w-full truncate font-black text-red-600 italic uppercase tracking-tight">${tab.school_name}</span>
                </button>
            `).join('');

            content.innerHTML = distTabsData.map((tab, i) => `
                <div id="tabContent_${i}" class="${i === currentActiveTab ? 'block' : 'hidden'} space-y-8 animate-in slide-in-from-right-4 duration-500">
                    <div class="p-6 border-2 border-dashed border-slate-200 rounded-[2.5rem] flex flex-col md:flex-row justify-between items-center bg-slate-50/50 gap-4">
                        <div>
                            <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest block mb-1">Distributing Asset To:</span>
                            <h5 class="text-2xl font-black text-slate-800 uppercase italic tracking-tighter">${tab.school_name}</h5>
                        </div>
                        <button type="button" onclick="confirmSingleTab(${i})" class="bg-[#1e293b] text-white px-8 py-4 rounded-2xl font-black uppercase text-xs tracking-widest shadow-xl active:scale-95 transition-all">Update This Tab</button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Main Category</label>
                            <input type="text" id="tabCatSearch_${i}" placeholder="Search category..." class="w-full p-5 bg-slate-50 border-none rounded-[1.5rem] outline-none font-bold text-slate-600 focus:ring-4 focus:ring-red-100 transition-all" onfocus="filterTabCat(${i})">
                            <div id="tabCatDropdown_${i}" class="hidden absolute z-30 w-[300px] mt-1 bg-white border border-slate-100 rounded-2xl shadow-2xl overflow-hidden"></div>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Item Name</label>
                            <input type="text" id="tabItemSearch_${i}" placeholder="Search item..." class="w-full p-5 bg-slate-50 border-none rounded-[1.5rem] outline-none font-bold text-slate-600 focus:ring-4 focus:ring-red-100 transition-all disabled:opacity-40" onfocus="filterTabItem(${i})" disabled>
                            <div id="tabItemDropdown_${i}" class="hidden absolute z-20 w-[300px] mt-1 bg-white border border-slate-100 rounded-2xl shadow-2xl overflow-hidden"></div>
                        </div>
                    </div>

                    <div class="space-y-4 pt-6 border-t border-slate-100">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Select Sub-Item(s) to Adjust</label>
                        <input type="text" id="tabSubSearch_${i}" placeholder="Search sub-items..." class="w-full p-5 bg-slate-50 border-none rounded-[1.5rem] outline-none font-bold text-slate-600 focus:ring-4 focus:ring-red-100 transition-all disabled:opacity-40" onfocus="filterTabSub(${i})" disabled>
                        <div id="tabSubDropdown_${i}" class="hidden absolute z-10 w-[400px] mt-1 bg-white border border-slate-100 rounded-2xl shadow-2xl overflow-hidden"></div>
                        <div id="tabSubContainer_${i}" class="grid grid-cols-1 gap-4 mt-6"></div>
                    </div>
                </div>
            `).join('');
        }

        function switchTab(index) {
            currentActiveTab = index;
            renderTabsUI();
        }

        // --- Helper filters for Modifier Logic ---
        function filterTabCat(tabId) {
            const dd = document.getElementById(`tabCatDropdown_${tabId}`);
            const schoolId = distTabsData[tabId].school_id;
            const ownedSubItems = schoolOwnershipsList[schoolId] || [];
            const ownedCategoryIds = [...new Set(ownedSubItems.map(o => o.category_id))];
            const filtered = rawCategories.filter(c => ownedCategoryIds.includes(c.id));
            
            dd.classList.remove('hidden');
            dd.innerHTML = filtered.map(c => `<div onclick="selectTabCat(${tabId}, ${c.id}, '${c.name}')" class="px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-red-50 hover:text-[#c00000] cursor-pointer border-b border-slate-50">${c.name}</div>`).join('');
        }

        function selectTabCat(tabId, catId, name) {
            const tab = distTabsData[tabId];
            tab.category_id = catId;
            document.getElementById(`tabCatSearch_${tabId}`).value = name;
            document.getElementById(`tabCatDropdown_${tabId}`).classList.add('hidden');
            document.getElementById(`tabItemSearch_${tabId}`).disabled = false;
            updateReadyStatus();
        }

        function filterTabItem(tabId) {
            const dd = document.getElementById(`tabItemDropdown_${tabId}`);
            const catId = distTabsData[tabId].category_id;
            const schoolId = distTabsData[tabId].school_id;
            const ownedSubItems = schoolOwnershipsList[schoolId] || [];
            const filteredItems = rawItems.filter(i => ownedSubItems.some(o => o.item_id == i.id && i.category_id == catId));

            dd.classList.remove('hidden');
            dd.innerHTML = filteredItems.map(i => `<div onclick="selectTabItem(${tabId}, ${i.id}, '${i.name}')" class="px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 cursor-pointer border-b border-slate-50">${i.name}</div>`).join('');
        }

        function selectTabItem(tabId, itemId, name) {
            const tab = distTabsData[tabId];
            tab.item_id = itemId;
            document.getElementById(`tabItemSearch_${tabId}`).value = name;
            document.getElementById(`tabItemDropdown_${tabId}`).classList.add('hidden');
            document.getElementById(`tabSubSearch_${tabId}`).disabled = false;
            updateReadyStatus();
        }

        function filterTabSub(tabId) {
            const dd = document.getElementById(`tabSubDropdown_${tabId}`);
            const itemId = distTabsData[tabId].item_id;
            const schoolId = distTabsData[tabId].school_id;
            const ownedSubItems = schoolOwnershipsList[schoolId] || [];
            const filteredSubs = rawSubItems.filter(s => ownedSubItems.some(o => o.sub_item_id == s.id && s.item_id == itemId));

            dd.classList.remove('hidden');
            dd.innerHTML = filteredSubs.map(s => {
                const owned = ownedSubItems.find(o => o.sub_item_id == s.id).quantity;
                return `<div onclick="selectTabSub(${tabId}, ${s.id}, '${s.name}', ${owned})" class="px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-blue-50 hover:text-blue-700 cursor-pointer border-b border-slate-50 flex justify-between">
                    <span>${s.name}</span>
                    <span class="text-[10px] font-black text-[#c00000] uppercase">${owned} Owned</span>
                </div>`;
            }).join('');
        }

        function selectTabSub(tabId, subId, name, owned) {
            const tab = distTabsData[tabId];
            if (tab.subItemsSelected.some(s => s.id === subId)) return;
            tab.subItemsSelected.push({ id: subId, name, owned, selected_qty: 0, action: 'subtract' });
            document.getElementById(`tabSubDropdown_${tabId}`).classList.add('hidden');
            renderTabSubItems(tabId);
        }

        function renderTabSubItems(tabId) {
            const tab = distTabsData[tabId];
            const container = document.getElementById(`tabSubContainer_${tabId}`);
            container.innerHTML = tab.subItemsSelected.map(si => `
                <div class="p-4 bg-white border border-slate-200 rounded-2xl shadow-sm flex flex-col gap-3 animate-in slide-in-from-top-2">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-bold text-slate-800">${si.name}</p>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Record: <span class="text-emerald-600">${si.owned} Items Deployed</span></p>
                        </div>
                        <button type="button" onclick="removeTabSub(${tabId}, ${si.id})" class="text-slate-300 hover:text-red-500">✕</button>
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="number" value="${si.selected_qty}" oninput="updateQty(${tabId}, ${si.id}, this.value)" class="w-24 p-3 bg-slate-50 border border-slate-200 rounded-xl font-black text-center focus:border-[#c00000] ${si.action === 'delete_all' ? 'opacity-30' : ''}" ${si.action === 'delete_all' ? 'disabled' : ''}>
                        <div class="flex gap-1">
                            <button onclick="setAction(${tabId}, ${si.id}, 'subtract')" class="px-3 py-2 text-[9px] font-black rounded-lg border ${si.action === 'subtract' ? 'bg-orange-50 text-orange-600 border-orange-200' : 'bg-white text-slate-400 border-slate-100'}">SUBTRACT</button>
                            <button onclick="setAction(${tabId}, ${si.id}, 'delete_all')" class="px-3 py-2 text-[9px] font-black rounded-lg border ${si.action === 'delete_all' ? 'bg-red-50 text-red-600 border-red-200' : 'bg-white text-slate-400 border-slate-100'}">DELETE RECORD</button>
                        </div>
                    </div>
                </div>
            `).join('');
            updateReadyStatus();
        }

        // Action Logic
        function setAction(tabId, subId, action) {
            const sub = distTabsData[tabId].subItemsSelected.find(s => s.id === subId);
            sub.action = action;
            if (action === 'delete_all') sub.selected_qty = sub.owned;
            renderTabSubItems(tabId);
        }

        function updateQty(tabId, subId, val) {
            const sub = distTabsData[tabId].subItemsSelected.find(s => s.id === subId);
            sub.selected_qty = Math.min(sub.owned, Math.max(0, parseInt(val) || 0));
            updateReadyStatus();
        }

        function removeTabSub(tabId, subId) {
            distTabsData[tabId].subItemsSelected = distTabsData[tabId].subItemsSelected.filter(s => s.id !== subId);
            renderTabSubItems(tabId);
        }

        function updateReadyStatus() {
            let total = 0;
            distTabsData.forEach(t => t.subItemsSelected.forEach(s => total += s.selected_qty));
            const btn = document.getElementById('distributeAllBtn');
            if(btn) {
                btn.disabled = total === 0;
                btn.classList.toggle('bg-[#c00000]', total > 0);
                btn.classList.toggle('opacity-50', total === 0);
                btn.classList.toggle('cursor-not-allowed', total === 0);
            }
        }

        function confirmDistributeAll() {
            Swal.fire({
                title: 'Confirm All Changes?',
                text: "Updating records and returning stock to inventory.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#c00000',
                confirmButtonText: 'Yes, update all',
                customClass: { popup: 'rounded-[2rem]' }
            }).then((result) => { if (result.isConfirmed) location.reload(); });
        }
    </script>
</body>
</html>