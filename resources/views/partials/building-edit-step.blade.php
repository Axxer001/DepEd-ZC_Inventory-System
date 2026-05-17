<div id="stepBuildingEdit" class="step-content">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-6">
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight italic uppercase text-emerald-600">Infrastructure <span class="text-slate-900">Management</span></h2>
            <p class="text-slate-400 text-sm font-bold uppercase mt-1 tracking-widest leading-tight">Bulk update building and facility records</p>
        </div>
        <div class="flex items-center gap-4">
            <button onclick="toggleBldgFilters()" id="toggleBldgFilterBtn" class="px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest text-slate-500 bg-white border border-slate-100 hover:border-emerald-600 transition-all flex items-center gap-2 active:scale-95 shadow-sm italic">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg>
                Hide Filters
            </button>
        </div>
    </div>

    <!-- Filter Configuration -->
    <div id="bldgFilterSection" class="bg-white rounded-[2.5rem] shadow-lg border border-slate-100 p-8 mb-8 relative z-50 transition-all duration-300 origin-top">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-8 relative z-10">
            {{-- Row 1 --}}
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Classification</label>
                <select id="bldgFilterClass" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-emerald-50 focus:border-emerald-500 transition-all text-slate-500">
                    <option value="">All Classifications</option>
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Office/School Type</label>
                <select id="bldgFilterCat" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-emerald-50 focus:border-emerald-500 transition-all text-slate-500">
                    <option value="">All Types</option>
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Article</label>
                <select id="bldgFilterArticle" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-emerald-50 focus:border-emerald-500 transition-all text-slate-500">
                    <option value="">All Articles</option>
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Cost Sorting</label>
                <select id="bldgFilterSort" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-emerald-50 focus:border-emerald-500 transition-all text-slate-500">
                    <option value="">Default (ID)</option>
                    <option value="low_to_high">Acquisition Cost: Low to High</option>
                    <option value="high_to_low">Acquisition Cost: High to Low</option>
                </select>
            </div>

            {{-- Row 2 --}}
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">School Name</label>
                <select id="bldgFilterSchool" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-emerald-50 focus:border-emerald-500 transition-all text-slate-500">
                    <option value="">All Schools</option>
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Nature of Occupancy</label>
                <select id="bldgFilterOccupancy" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-emerald-50 focus:border-emerald-500 transition-all text-slate-500">
                    <option value="">All Status</option>
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Date Constructed</label>
                <input type="date" id="bldgFilterDate" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-emerald-50 focus:border-emerald-500 transition-all text-slate-500">
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Data Integrity (Empty Fields)</label>
                <select id="bldgFilterIntegrity" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-emerald-50 focus:border-emerald-500 transition-all text-slate-500">
                    <option value="">Show All Records</option>
                    <option value="office_type">Missing Office Type</option>
                    <option value="school_id">Missing School ID</option>
                    <option value="office_name">Missing School Name</option>
                    <option value="address">Missing Address</option>
                    <option value="article">Missing Article</option>
                    <option value="property_number">Missing Property No.</option>
                    <option value="acquisition_cost">Missing Cost</option>
                </select>
            </div>
        </div>

        <div class="mt-8 pt-8 border-t border-slate-50 flex justify-end gap-4">
            <button onclick="clearBldgFilters()" class="px-8 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-all active:scale-95 italic">
                Clear Filters
            </button>
            <button onclick="bldgFetchData()" class="px-10 py-3 bg-slate-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-slate-200 hover:bg-emerald-600 transition-all active:scale-95 italic">
                Apply Configuration
            </button>
        </div>
    </div>

    <!-- Table Section -->
    <div id="bldgAssetTableCard" class="bg-white rounded-[2.5rem] shadow-xl border border-slate-100 flex flex-col min-h-0 overflow-hidden">
        <div class="px-8 py-5 border-b border-slate-50 flex items-center justify-between bg-slate-50/30">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 bg-slate-800 rounded-xl flex items-center justify-center text-white text-xs font-black shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21" />
                    </svg>
                </div>
                <div class="flex bg-slate-100 rounded-xl p-1 gap-1">
                    <button id="tabEditBldgIdentity" onclick="switchEditBldgTab('identity')"
                        class="px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg bg-emerald-600 text-white shadow-sm transition-all">
                        Identity & Structure
                    </button>
                    <button id="tabEditBldgDetails" onclick="switchEditBldgTab('details')"
                        class="px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg text-slate-900 hover:text-slate-900 transition-all">
                        Registry & Value
                    </button>
                </div>
                <span id="editBldgTabLabel" class="hidden md:block text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">Identity & Structure</span>
            </div>

            <div id="bldgAssetToolbar" class="flex items-center gap-3">
                <div class="flex items-center bg-slate-100 rounded-2xl p-1.5 mr-2">
                    <button onclick="bldgUndo()" id="bldgUndoBtn" class="px-4 py-2 text-slate-400 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-white hover:text-emerald-600 transition-all active:scale-95 flex items-center gap-2 opacity-50 cursor-not-allowed group">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                        Undo
                    </button>
                    <div class="w-[1px] bg-slate-200 h-3 self-center my-auto"></div>
                    <button onclick="bldgRedo()" id="bldgRedoBtn" class="px-4 py-2 text-slate-400 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-white hover:text-emerald-600 transition-all active:scale-95 flex items-center gap-2 opacity-50 cursor-not-allowed group">
                        Redo
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 10H11a8 8 0 00-8 8v2m18-10l-6 6m6-6l-6-6"/></svg>
                    </button>
                </div>

                <button onclick="openBldgBulkModal()" class="px-5 py-2.5 bg-emerald-50 text-emerald-600 rounded-xl text-[9px] font-black uppercase tracking-widest flex items-center gap-2 shadow-sm hover:bg-emerald-100 transition-all active:scale-95 italic border border-emerald-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16.862 4.487l1.688-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"/></svg>
                    Bulk Edit
                </button>

                <button onclick="saveBldgChanges()" class="px-5 py-2.5 bg-slate-900 text-white rounded-xl text-[9px] font-black uppercase tracking-widest flex items-center gap-2 shadow-lg hover:bg-emerald-600 transition-all active:scale-95 italic">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Save Changes
                </button>
            </div>
        </div>

        {{-- ── Tab 1: Identity Table ── --}}
        <div id="panelEditBldgIdentity" class="flex-grow flex flex-col min-h-0">
            <div id="bldgIdentityScroll" class="xls-scroll-wrap custom-scroll overflow-x-auto overflow-y-auto transition-all duration-300" style="max-height: calc(100vh - 450px);">
                <table class="w-full border-collapse" style="min-width:1400px;">
                    <thead class="sticky top-0 bg-slate-50 z-20 shadow-sm">
                        <tr>
                            <th class="xls-th w-10 text-center sticky left-0 z-30">#</th>
                            <th class="xls-th col-context" style="min-width:90px">REGION</th>
                            <th class="xls-th col-context" style="min-width:200px">DIVISION</th>
                            <th class="xls-th col-context" style="min-width:140px">OFFICE TYPE</th>
                            <th class="xls-th col-identity" style="min-width:100px">SCHOOL ID</th>
                            <th class="xls-th col-identity" style="min-width:210px">SCHOOL NAME</th>
                            <th class="xls-th col-context" style="min-width:180px">ADDRESS</th>
                            <th class="xls-th col-personnel text-right" style="min-width:80px">STOREYS</th>
                            <th class="xls-th col-personnel text-right" style="min-width:100px">CLASSROOMS</th>
                        </tr>
                    </thead>
                    <tbody id="bldgIdentityTbody"></tbody>
                </table>
            </div>
        </div>

        {{-- ── Tab 2: Details Table ── --}}
        <div id="panelEditBldgDetails" class="hidden flex-grow flex flex-col min-h-0">
            <div id="bldgDetailsScroll" class="xls-scroll-wrap custom-scroll overflow-x-auto overflow-y-auto transition-all duration-300" style="max-height: calc(100vh - 450px);">
                <table class="w-full border-collapse" style="min-width:2000px;">
                    <thead class="sticky top-0 bg-slate-50 z-20 shadow-sm">
                        <tr>
                            <th class="xls-th w-10 text-center sticky left-0 z-30">#</th>
                            <th class="xls-th col-personnel" style="min-width:140px">ARTICLE</th>
                            <th class="xls-th col-personnel" style="min-width:200px">DESCRIPTION</th>
                            <th class="xls-th col-identity" style="min-width:140px">CLASSIFICATION</th>
                            <th class="xls-th col-context" style="min-width:160px">OCCUPANCY NATURE</th>
                            <th class="xls-th col-context" style="min-width:160px">LOCATION</th>
                            <th class="xls-th col-temporal" style="min-width:140px">DATE CONSTRUCTED</th>
                            <th class="xls-th col-temporal" style="min-width:140px">ACQUISITION DATE</th>
                            <th class="xls-th col-identity" style="min-width:150px">PROPERTY NO.</th>
                            <th class="xls-th col-financial text-right" style="min-width:140px">ACQUISITION COST (₱)</th>
                            <th class="xls-th col-temporal text-right" style="min-width:120px">USEFUL LIFE (YRS)</th>
                            <th class="xls-th col-financial text-right" style="min-width:140px">APPRAISED VALUE (₱)</th>
                            <th class="xls-th col-temporal" style="min-width:140px">APPRAISAL DATE</th>
                            <th class="xls-th col-status" style="min-width:200px">REMARKS</th>
                        </tr>
                    </thead>
                    <tbody id="bldgDetailsTbody"></tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="px-5 py-3 border-t border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center gap-6">
                <p id="bldgRowCountLabel" class="text-[9px] font-black text-slate-400 uppercase tracking-widest">0 Rows</p>
                <div id="bldgPaginationControls" class="flex items-center gap-3 border-l border-slate-200 pl-6">
                    <button onclick="bldgPrevPage()" id="bldgPrevBtn" class="pg-btn">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
                        Prev
                    </button>
                    <div class="flex items-center gap-2 px-4 py-2 bg-slate-900/40 dark:bg-slate-800/60 rounded-xl border border-slate-200/10 backdrop-blur-md">
                        <span id="bldgCurrentPageNum" class="text-[10px] font-black text-slate-700 dark:text-blue-400">1</span>
                        <span class="text-[10px] font-bold text-slate-400">/</span>
                        <span id="bldgTotalPages" class="text-[10px] font-black text-slate-400">1</span>
                    </div>
                    <button onclick="bldgNextPage()" id="bldgNextBtn" class="pg-btn">
                        Next
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div> <!-- End stepBuildingEdit -->

<!-- Bulk Edit Modal -->
<div id="bldgBulkModal" class="fixed inset-0 z-[150] flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeBldgBulkModal()"></div>
    <div class="bg-white dark:bg-[#141f33] border border-slate-200 dark:border-slate-800 rounded-[2rem] shadow-2xl w-[90vw] max-w-5xl max-h-[90vh] flex flex-col relative z-10 transform scale-95 transition-transform duration-300">
        
        {{-- Header --}}
        <div class="px-8 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-2xl font-black text-slate-800 dark:text-white uppercase tracking-tight italic text-emerald-600">Bulk Edit Rows</h3>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Update specific columns for a range of rows</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 bg-slate-50 dark:bg-[#0a101d] px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800">
                    <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">From Row #</label>
                    <input type="number" id="bldgBulkFrom" value="1" min="1" class="w-16 bg-transparent text-center font-black text-slate-800 dark:text-white outline-none">
                </div>
                <div class="flex items-center gap-2 bg-slate-50 dark:bg-[#0a101d] px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800">
                    <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">To Row #</label>
                    <input type="number" id="bldgBulkTo" value="1" min="1" class="w-20 bg-transparent text-center font-black text-slate-800 dark:text-white outline-none">
                </div>
                <button onclick="closeBldgBulkModal()" class="px-5 py-3 rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">Cancel</button>
                <button onclick="applyBldgBulk()" class="px-6 py-3 rounded-xl text-sm font-black text-white bg-emerald-600 hover:bg-emerald-700 shadow-lg shadow-emerald-500/30 transition-all">Apply Bulk Edit</button>
            </div>
        </div>

        {{-- Body --}}
        <div class="p-8 overflow-y-auto custom-scroll flex-1 space-y-10">
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-6 h-6 bg-emerald-500/20 text-emerald-600 rounded-lg flex items-center justify-center text-[10px] font-black shrink-0">1</div>
                    <h4 class="font-black text-slate-800 dark:text-slate-200 uppercase tracking-widest text-xs">Building Identity</h4>
                </div>
                <div class="grid grid-cols-2 gap-x-6 gap-y-5">
                    <div class="relative">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Office/School Type</label>
                        <input type="text" list="dl-bldg-type" id="bebOfficeType" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl bg-transparent" placeholder="-- Ignore --">
                    </div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">School ID</label><input type="text" id="bebSchoolId" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">School Name</label><input type="text" id="bebSchoolName" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore" oninput="
                        if(this.value.trim() && typeof detectItemSchoolType === 'function'){
                            const t = detectItemSchoolType(this.value);
                            if(t) document.getElementById('bebOfficeType').value = t;
                            if(typeof cleanSchoolNameForLocation === 'function') document.getElementById('bebLocation').value = cleanSchoolNameForLocation(this.value);
                        }
                    "></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Address</label><input type="text" id="bebAddress" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Storeys</label><input type="number" id="bebStoreys" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl text-right" placeholder="Leave empty to ignore" min="0" step="1"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Classrooms</label><input type="number" id="bebClassrooms" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl text-right" placeholder="Leave empty to ignore" min="0" step="1"></div>
                </div>
            </div>
            <div class="border-t border-slate-100 dark:border-slate-800"></div>
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-6 h-6 bg-emerald-500/20 text-emerald-600 rounded-lg flex items-center justify-center text-[10px] font-black shrink-0">2</div>
                    <h4 class="font-black text-slate-800 dark:text-slate-200 uppercase tracking-widest text-xs">Building Details</h4>
                </div>
                <div class="grid grid-cols-2 gap-x-6 gap-y-5">
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Article</label><input type="text" id="bebArticle" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Description</label><input type="text" id="bebDescription" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Classification</label><input type="text" id="bebClassification" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Nature of Occupancy</label><input type="text" id="bebOccupancy" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Location</label><input type="text" id="bebLocation" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Property Number</label><input type="text" id="bebPropertyNo" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Acquisition Cost (₱)</label><input type="number" id="bebAcqCost" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl text-right" placeholder="Leave empty to ignore" min="0" step="0.01"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Useful Life (yrs)</label><input type="number" id="bebLife" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl text-right" placeholder="Leave empty to ignore" min="0" step="1"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Date Constructed</label><input type="date" id="bebDateConstructed" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Acquisition Date</label><input type="date" id="bebAcqDate" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Appraised Value (₱)</label><input type="number" id="bebAppraisedValue" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl text-right" placeholder="Leave empty to ignore" min="0" step="0.01"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Appraisal Date</label><input type="date" id="bebAppraisalDate" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl"></div>
                    <div class="relative col-span-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-emerald-600">Remarks</label>
                        <select id="bebRemarks" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl bg-transparent">
                            <option value="">-- Ignore --</option>
                            <option value="Good Condition">Good Condition</option>
                            <option value="Needs Repair">Needs Repair</option>
                            <option value="Not Useable">Not Useable</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .update-badge {
        position: absolute; top: 3px; left: 3px; font-size: 8px; font-weight: 900; background: #3b82f6; color: white; padding: 1px 4px; border-radius: 4px; text-transform: uppercase; pointer-events: none; z-index: 10; box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2); letter-spacing: 0.5px;
    }
    .edit-input {
        width: 100%; padding: 11px 14px; font-size: 11.5px; font-weight: 600; color: #334155; background: rgba(59, 130, 246, 0.03); border: 1px solid transparent; outline: none; box-sizing: border-box; line-height: 1.4; transition: all 0.2s; height: 100%; min-height: 40px;
    }
    .edit-input:focus {
        background: rgba(59, 130, 246, 0.05); border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
    }
    .edit-readonly {
        background: rgba(0,0,0,0.02) !important; color: #94a3b8 !important; cursor: not-allowed;
    }
</style>

<script>
    let bldgAllData = [];
    let bldgOriginalData = [];
    let bldgUndoStack = [];
    let bldgRedoStack = [];
    let bldgPageNum = 1;
    const bldgRowsPerPage = 50;

    function switchEditBldgTab(tab) {
        const identPanel = document.getElementById('panelEditBldgIdentity');
        const detPanel   = document.getElementById('panelEditBldgDetails');
        const tabIdent   = document.getElementById('tabEditBldgIdentity');
        const tabDet     = document.getElementById('tabEditBldgDetails');
        const label      = document.getElementById('editBldgTabLabel');
        const ON  = 'px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg bg-emerald-600 text-white shadow-sm transition-all';
        const OFF = 'px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg text-slate-900 hover:text-slate-900 transition-all';
        if (tab === 'identity') {
            identPanel.classList.remove('hidden');
            detPanel.classList.add('hidden');
            tabIdent.className = ON; tabDet.className = OFF;
            label.textContent = 'Identity & Structure';
        } else {
            identPanel.classList.add('hidden');
            detPanel.classList.remove('hidden');
            tabIdent.className = OFF; tabDet.className = ON;
            label.textContent = 'Registry & Value';
        }
    }

    function toggleBldgFilters() {
        const section = document.getElementById('bldgFilterSection');
        const btn = document.getElementById('toggleBldgFilterBtn');
        if (section.classList.contains('hidden')) {
            section.classList.remove('hidden');
            btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg> Hide Filters`;
        } else {
            section.classList.add('hidden');
            btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg> Show Filters`;
        }
    }

    function initBldgEdit() {
        fetch('{{ route("api.buildings.filters") }}')
            .then(res => res.json())
            .then(data => {
                populateBldgSelect('bldgFilterClass', data.classifications || []);
                populateBldgSelect('bldgFilterCat', data.office_types || []);
                populateBldgSelect('bldgFilterArticle', data.articles || []);
                populateBldgSelect('bldgFilterSchool', data.schools || []);
                populateBldgSelect('bldgFilterOccupancy', data.occupancies || []);
            });
    }

    function populateBldgSelect(id, options) {
        const sel = document.getElementById(id);
        if (!sel) return;
        const first = sel.options[0];
        sel.innerHTML = '';
        sel.appendChild(first);
        options.forEach(opt => {
            const el = document.createElement('option');
            el.value = opt; el.textContent = opt;
            sel.appendChild(el);
        });
    }

    function clearBldgFilters() {
        ['bldgFilterClass', 'bldgFilterCat', 'bldgFilterArticle', 'bldgFilterSort', 'bldgFilterSchool', 'bldgFilterOccupancy', 'bldgFilterDate', 'bldgFilterIntegrity'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        bldgFetchData();
    }

    function bldgFetchData() {
        const filters = {
            classification: (document.getElementById('bldgFilterClass')||{}).value || '',
            office_type:    (document.getElementById('bldgFilterCat')||{}).value || '',
            article:        (document.getElementById('bldgFilterArticle')||{}).value || '',
            school:         (document.getElementById('bldgFilterSchool')||{}).value || '',
            occupancy:      (document.getElementById('bldgFilterOccupancy')||{}).value || '',
            date:           (document.getElementById('bldgFilterDate')||{}).value || '',
            emptyCol:       (document.getElementById('bldgFilterIntegrity')||{}).value || '',
            sortCost:       (document.getElementById('bldgFilterSort')||{}).value || '',
        };

        const loader = document.getElementById('bldgAssetLoading');
        if (loader) loader.classList.remove('hidden');

        fetch('{{ route("api.buildings.edit_preview") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ filters: filters })
        })
        .then(res => res.json())
        .then(data => {
            bldgAllData = data.rows || [];
            bldgOriginalData = JSON.parse(JSON.stringify(bldgAllData));
            bldgPageNum = 1;
            bldgUndoStack = [];
            bldgRedoStack = [];
            updateBldgUndoBtn();
            renderBldgTable();
            if (bldgAllData.length === 0) {
                Swal.fire({ title: 'No Buildings Found', text: 'No records match your current filter configuration.', icon: 'info', customClass: { popup: 'rounded-[2rem]' } });
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire({ title: 'Error', text: 'Failed to load building data.', icon: 'error', customClass: { popup: 'rounded-[2rem]' } });
        })
        .finally(() => {
            if (loader) loader.classList.add('hidden');
        });
    }

    function renderBldgTable() {
        const tbodyIdent = document.getElementById('bldgIdentityTbody');
        const tbodyDet   = document.getElementById('bldgDetailsTbody');
        if (!tbodyIdent || !tbodyDet) return;
        tbodyIdent.innerHTML = ''; tbodyDet.innerHTML = '';

        if (bldgAllData.length === 0) {
            document.getElementById('bldgRowCountLabel').textContent = "0 Rows";
            document.getElementById('bldgCurrentPageNum').textContent = 1;
            document.getElementById('bldgTotalPages').textContent = 1;
            return;
        }

        const start = (bldgPageNum - 1) * bldgRowsPerPage;
        const pageData = bldgAllData.slice(start, start + bldgRowsPerPage);

        pageData.forEach((row, idx) => {
            const displayNum = start + idx + 1;
            const orig = bldgOriginalData.find(o => String(o.id) === String(row.id)) || {};

            const renderCell = (col, val, readonly, list = '') => {
                const v1 = String(val ?? '').trim();
                const v2 = String(orig[col] ?? '').trim();
                const changed = v1 !== v2;
                const badge = changed ? `<span class="update-badge">Update</span>` : '';
                const safe = (val ?? '').toString().replace(/"/g, '&quot;');
                if (readonly) return `<td class="xls-td p-0 relative"><input type="text" class="xls-input edit-readonly w-full h-full" value="${safe}" readonly tabindex="-1">${badge}</td>`;
                if (col === 'remarks') return `<td class="xls-td p-0 relative"><select data-id="${row.id}" data-col="${col}" onchange="syncBldgCell(this)" class="xls-input w-full h-full bg-transparent"><option value="Good Condition" ${val==='Good Condition'?'selected':''}>Good Condition</option><option value="Needs Repair" ${val==='Needs Repair'?'selected':''}>Needs Repair</option><option value="Not Useable" ${val==='Not Useable'?'selected':''}>Not Useable</option></select>${badge}</td>`;
                return `<td class="xls-td p-0 relative"><input type="text" data-id="${row.id}" data-col="${col}" value="${safe}" onchange="syncBldgCell(this)" autocomplete="off" ${list ? `list="${list}"` : ''} class="xls-input w-full h-full bg-transparent">${badge}</td>`;
            };

            const trI = document.createElement('tr');
            trI.className = 'xls-row group border-b border-slate-100';
            trI.innerHTML = `
                <td class="xls-td text-center sticky left-0 w-10 bg-white z-10"><span class="text-[10px] font-black text-slate-300">${displayNum}</span></td>
                <td class="xls-td col-context p-0 relative"><span class="xls-const w-full h-full flex items-center px-4">Region IX</span></td>
                <td class="xls-td col-context p-0 relative"><span class="xls-const w-full h-full flex items-center px-4">Division of Zamboanga City</span></td>
                ${renderCell('office_type', row.office_type, false, 'dl-bldg-type')}
                ${renderCell('school_id', row.school_id, false)}
                ${renderCell('office_name', row.office_name, false)}
                ${renderCell('address', row.address, false)}
                ${renderCell('storeys', row.storeys, false)}
                ${renderCell('classrooms', row.classrooms, false)}
            `;
            tbodyIdent.appendChild(trI);

            const trD = document.createElement('tr');
            trD.className = 'xls-row group border-b border-slate-100';
            trD.innerHTML = `
                <td class="xls-td text-center sticky left-0 w-10 bg-white z-10"><span class="text-[10px] font-black text-slate-300">${displayNum}</span></td>
                ${renderCell('article', row.article, false)}
                ${renderCell('description', row.description, false)}
                ${renderCell('classification', row.classification, false, 'dl-bldg-class')}
                ${renderCell('occupancy_nature', row.occupancy_nature, false, 'dl-bldg-occupancy')}
                ${renderCell('location', row.location, false)}
                ${renderCell('date_constructed', row.date_constructed, false)}
                ${renderCell('acquisition_date', row.acquisition_date, false)}
                ${renderCell('property_number', row.property_number, false)}
                ${renderCell('acquisition_cost', row.acquisition_cost, false)}
                ${renderCell('estimated_useful_life', row.estimated_useful_life, false)}
                ${renderCell('appraised_value', row.appraised_value, false)}
                ${renderCell('appraisal_date', row.appraisal_date, false)}
                ${renderCell('remarks', row.remarks, false)}
            `;
            tbodyDet.appendChild(trD);
        });

        const totalPages = Math.ceil(bldgAllData.length / bldgRowsPerPage) || 1;
        document.getElementById('bldgRowCountLabel').textContent = bldgAllData.length + " Rows (paired)";
        document.getElementById('bldgCurrentPageNum').textContent = bldgPageNum;
        document.getElementById('bldgTotalPages').textContent = totalPages;
        document.getElementById('bldgPrevBtn').disabled = bldgPageNum === 1;
        document.getElementById('bldgNextBtn').disabled = bldgPageNum === totalPages;
    }

    function syncBldgCell(input) {
        const id = input.getAttribute('data-id');
        const col = input.getAttribute('data-col');
        const newVal = input.value;
        const row = bldgAllData.find(r => String(r.id) === String(id));
        if (row) {
            const oldVal = row[col] ?? '';
            if (String(oldVal).trim() !== String(newVal).trim()) {
                bldgUndoStack.push({ type: 'single', rowId: id, col: col, oldVal: oldVal, newVal: newVal });
                row[col] = newVal;

                // Targeted DOM Update Function (No focus loss)
                const updateDOM = (column, newValue) => {
                    const el = document.querySelector(`input[data-id="${id}"][data-col="${column}"]`);
                    if (el && document.activeElement !== el) el.value = newValue;
                };

                if (col === 'office_name') {
                    const trimmed = newVal.trim();
                    if (trimmed !== '') {
                        if (typeof detectItemSchoolType === 'function') {
                            const detected = detectItemSchoolType(trimmed);
                            if (detected) { row['office_type'] = detected; updateDOM('office_type', detected); }
                        }
                        if (typeof cleanSchoolNameForLocation === 'function') {
                            const loc = cleanSchoolNameForLocation(trimmed);
                            row['location'] = loc; updateDOM('location', loc);
                        }
                    }
                }
                
                bldgRedoStack = [];
                updateBldgUndoBtn();
                // We update badges visually without full re-render
                const td = input.closest('td');
                if (td && !td.querySelector('.update-badge')) {
                    const badge = document.createElement('span'); badge.className = 'update-badge'; badge.textContent = 'Update';
                    td.appendChild(badge);
                }
            }
        }
    }

    function bldgPrevPage() { if (bldgPageNum > 1) { bldgPageNum--; renderBldgTable(); } }
    function bldgNextPage() { const t = Math.ceil(bldgAllData.length/bldgRowsPerPage); if (bldgPageNum < t) { bldgPageNum++; renderBldgTable(); } }

    function openBldgBulkModal() {
        if(bldgAllData.length === 0) return Swal.fire('No Data', 'Load assets first.', 'info');
        const m = document.getElementById('bldgBulkModal');
        m.classList.remove('hidden');
        document.querySelectorAll('#bldgBulkModal input:not([id="bldgBulkFrom"]):not([id="bldgBulkTo"])').forEach(i => i.value = '');
        const br = document.getElementById('bebRemarks'); if(br) br.value = '';

        const maxRows = bldgAllData.length;
        const fromInput = document.getElementById('bldgBulkFrom');
        const toInput   = document.getElementById('bldgBulkTo');
        fromInput.value = 1;
        fromInput.max   = maxRows;
        toInput.value   = maxRows;
        toInput.max     = maxRows;

        setTimeout(() => {
            m.classList.remove('opacity-0');
            m.querySelector('.transform').classList.remove('scale-95');
        }, 10);
    }
    
    function closeBldgBulkModal() {
        const m = document.getElementById('bldgBulkModal');
        m.classList.add('opacity-0');
        m.querySelector('.transform').classList.add('scale-95');
        setTimeout(() => m.classList.add('hidden'), 300);
    }

    function applyBldgBulk() {
        const from = parseInt(document.getElementById('bldgBulkFrom').value);
        const to = parseInt(document.getElementById('bldgBulkTo').value);
        const maxRows = bldgAllData.length;

        if (isNaN(from) || isNaN(to) || from < 1 || to < from || to > maxRows) return Swal.fire('Error', 'Invalid Range', 'error');

        const bulkMapping = {
            'bebOfficeType': 'office_type', 'bebSchoolId': 'school_id', 'bebSchoolName': 'office_name',
            'bebAddress': 'address', 'bebStoreys': 'storeys', 'bebClassrooms': 'classrooms',
            'bebArticle': 'article', 'bebDescription': 'description', 'bebClassification': 'classification',
            'bebOccupancy': 'occupancy_nature', 'bebLocation': 'location', 'bebDateConstructed': 'date_constructed',
            'bebAcqDate': 'acquisition_date', 'bebPropertyNo': 'property_number', 'bebAcqCost': 'acquisition_cost',
            'bebLife': 'estimated_useful_life', 'bebAppraisedValue': 'appraised_value', 'bebAppraisalDate': 'appraisal_date', 'bebRemarks': 'remarks'
        };

        const updates = {}; let has = false;
        for (const [id, col] of Object.entries(bulkMapping)) {
            const v = document.getElementById(id).value;
            if (v !== "") { updates[col] = v; has = true; }
        }
        if (!has) return closeBldgBulkModal();

        const previousStates = [];
        for (let i = from - 1; i < to; i++) {
            const row = bldgAllData[i];
            const rowPrev = { rowId: row.id, changes: [] };
            let changed = false;
            for (const [col, newVal] of Object.entries(updates)) {
                if (String(row[col] ?? '').trim() !== String(newVal).trim()) {
                    rowPrev.changes.push({ col: col, oldVal: row[col] });
                    row[col] = newVal; changed = true;
                }
            }
            if (changed) previousStates.push(rowPrev);
        }

        if (previousStates.length > 0) {
            bldgUndoStack.push({ type: 'bulkMulti', states: previousStates });
            bldgRedoStack = []; updateBldgUndoBtn(); renderBldgTable();
        }
        closeBldgBulkModal();
    }

    function bldgUndo() {
        if (bldgUndoStack.length === 0) return;
        const action = bldgUndoStack.pop();
        const redoStates = [];
        if (action.type === 'single') {
            const row = bldgAllData.find(r => String(r.id) === String(action.rowId));
            if (row) {
                redoStates.push({ rowId: row.id, changes: [{ col: action.col, oldVal: row[action.col] }] });
                row[action.col] = action.oldVal;
            }
        } else if (action.type === 'bulkMulti') {
            action.states.forEach(state => {
                const row = bldgAllData.find(r => String(r.id) === String(state.rowId));
                if (row) {
                    const rs = { rowId: state.rowId, changes: [] };
                    state.changes.forEach(change => {
                        rs.changes.push({ col: change.col, oldVal: row[change.col] });
                        row[change.col] = change.oldVal;
                    });
                    redoStates.push(rs);
                }
            });
        }
        bldgRedoStack.push({ type: 'bulkMulti', states: redoStates });
        updateBldgUndoBtn(); renderBldgTable();
    }

    function bldgRedo() {
        if (bldgRedoStack.length === 0) return;
        const action = bldgRedoStack.pop();
        const undoStates = [];
        action.states.forEach(state => {
            const row = bldgAllData.find(r => String(r.id) === String(state.rowId));
            if (row) {
                const us = { rowId: state.rowId, changes: [] };
                state.changes.forEach(change => {
                    us.changes.push({ col: change.col, oldVal: row[change.col] });
                    row[change.col] = change.oldVal;
                });
                undoStates.push(us);
            }
        });
        bldgUndoStack.push({ type: 'bulkMulti', states: undoStates });
        updateBldgUndoBtn(); renderBldgTable();
    }

    function updateBldgUndoBtn() {
        const uBtn = document.getElementById('bldgUndoBtn'), rBtn = document.getElementById('bldgRedoBtn');
        if (uBtn) uBtn.className = bldgUndoStack.length > 0 ? 'px-4 py-2 text-emerald-600 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-white transition-all active:scale-95 flex items-center gap-2' : 'px-4 py-2 text-slate-400 rounded-xl text-[9px] font-black uppercase tracking-widest opacity-50 cursor-not-allowed flex items-center gap-2';
        if (rBtn) rBtn.className = bldgRedoStack.length > 0 ? 'px-4 py-2 text-emerald-600 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-white transition-all active:scale-95 flex items-center gap-2' : 'px-4 py-2 text-slate-400 rounded-xl text-[9px] font-black uppercase tracking-widest opacity-50 cursor-not-allowed flex items-center gap-2';
    }

    function saveBldgChanges() {
        const updates = [];
        bldgAllData.forEach(row => {
            const orig = bldgOriginalData.find(o => String(o.id) === String(row.id));
            if (!orig) return;
            const changes = {}; let has = false;
            ['office_type', 'school_id', 'office_name', 'address', 'storeys', 'classrooms', 'article', 'description', 'classification', 'occupancy_nature', 'location', 'date_constructed', 'acquisition_date', 'property_number', 'acquisition_cost', 'estimated_useful_life', 'appraised_value', 'appraisal_date', 'remarks'].forEach(k => {
                if (String(row[k] ?? '').trim() !== String(orig[k] ?? '').trim()) { changes[k] = row[k]; has = true; }
            });
            if (has) updates.push({ id: row.id, ...changes });
        });

        if (updates.length === 0) return Swal.fire('No Changes', 'No records were modified.', 'info');
        Swal.fire({ title: 'Save Changes?', text: `Modify ${updates.length} records?`, icon: 'question', showCancelButton: true, confirmButtonColor: '#10b981' }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Saving...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                fetch('{{ route("api.buildings.updateBatch") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ updates: updates })
                }).then(res => res.json()).then(data => {
                    if (data.success) {
                        Swal.fire('Saved!', data.message, 'success').then(() => {
                            bldgOriginalData = JSON.parse(JSON.stringify(bldgAllData));
                            bldgUndoStack = []; bldgRedoStack = []; updateBldgUndoBtn(); renderBldgTable();
                        });
                    } else Swal.fire('Error', data.message, 'error');
                }).catch(() => Swal.fire('Error', 'Server error.', 'error'));
            }
        });
    }
</script>
