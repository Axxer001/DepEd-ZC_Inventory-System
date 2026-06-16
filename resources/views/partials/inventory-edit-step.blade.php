<div id="stepInventoryEdit" class="step-content">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-6">
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight italic uppercase text-blue-600">Inventory <span class="text-slate-900">Editor</span></h2>
            <p class="text-slate-400 text-sm font-bold uppercase mt-1 tracking-widest leading-tight">Bulk update master inventory records</p>
        </div>
        <div class="flex items-center gap-4">
            <button onclick="toggleEditFilters()" id="toggleEditFilterBtn" class="px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest text-slate-500 bg-white border border-slate-100 hover:border-blue-600 transition-all flex items-center gap-2 active:scale-95 shadow-sm italic">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg>
                Hide Filters
            </button>
        </div>
    </div>

    <!-- Filter Configuration -->
    <div id="editFilterSection" class="bg-white rounded-[2.5rem] shadow-lg border border-slate-100 p-8 mb-8 relative z-50 transition-all duration-300 origin-top">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-8 relative z-10">
            {{-- Row 1 --}}
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Classification</label>
                <select id="editFilterClass" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all text-slate-500">
                    <option value="">All Classifications</option>
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Category</label>
                <select id="editFilterCat" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all text-slate-500">
                    <option value="">All Categories</option>
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Item</label>
                <select id="editFilterItem" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all text-slate-500">
                    <option value="">All Items</option>
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Cost Sorting</label>
                <select id="editFilterSort" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all text-slate-500">
                    <option value="">Default (ID)</option>
                    <option value="low_to_high">Acquisition Cost: Low to High</option>
                    <option value="high_to_low">Acquisition Cost: High to Low</option>
                </select>
            </div>

            {{-- Row 2 --}}
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">School Name</label>
                <select id="editFilterSchool" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all text-slate-500">
                    <option value="">All Schools</option>
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Source of Acquisition</label>
                <select id="editFilterSource" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all text-slate-500">
                    <option value="">All Sources</option>
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Mode of Acquisition</label>
                <select id="editFilterMode" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all text-slate-500">
                    <option value="">All Modes</option>
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Date Acquired (Acceptance)</label>
                <input type="date" id="editFilterDate" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all text-slate-500">
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic text-red-500">Data Integrity (Empty Fields)</label>
                <select id="editFilterIntegrity" class="w-full bg-slate-50 border-red-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-500">
                    <option value="">No Integrity Filter</option>
                    <option value="article">Missing Article/Item</option>
                    <option value="category">Missing Category</option>
                    <option value="classification">Missing Classification</option>
                    <option value="description">Missing Description</option>
                    <option value="property_number">Missing Property Number</option>
                    <option value="unit_of_measurement">Missing Unit (UOM)</option>
                    <option value="acq_source">Missing Acquisition Source</option>
                    <option value="mode_of_acquisition">Missing Mode</option>
                    <option value="acceptance_date">Missing Acceptance Date</option>
                    <option value="school_id">Missing School ID</option>
                    <option value="school_name">Missing School Name</option>
                    <option value="occupancy">Missing Nature of Occupancy</option>
                    <option value="location">Missing Location</option>
                    <option value="acquisition_date">Missing Acquisition Date</option>
                </select>
            </div>
        </div>
        <div class="mt-8 flex justify-end items-center gap-8 relative z-10">
            <button onclick="clearEditFilters()" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] hover:text-blue-600 transition-all italic">Clear All Filters</button>
            <button onclick="editFetchData()" class="px-8 py-2.5 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-600 transition-all active:scale-95 shadow-lg shadow-slate-200 italic">Apply Configuration</button>
        </div>
    </div>

    <!-- Data Grid -->
    <div id="editAssetTableCard" class="bg-white rounded-[2rem] border border-slate-100 shadow-xl relative overflow-hidden flex flex-col">
        
        {{-- Toolbar --}}
        <div id="editAssetToolbar" class="px-5 py-3 border-b border-slate-100 flex items-center justify-between gap-3 flex-wrap bg-slate-50">
            <div class="flex items-center gap-3">
                <div class="flex bg-slate-200/50 rounded-xl p-1 gap-1">
                    <button id="editTabAssetSource" onclick="switchEditAssetTab('source')"
                        class="px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg bg-blue-600 text-white shadow-sm transition-all">
                        Asset Source
                    </button>
                    <button id="editTabAssetDist" onclick="switchEditAssetTab('distribution')"
                        class="px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg text-slate-500 hover:text-slate-700 transition-all">
                        Asset Distribution
                    </button>
                </div>
                <span id="editAssetTabLabel" class="hidden md:block text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">Asset Source</span>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex bg-slate-100 rounded-2xl p-1 gap-1 border border-slate-200">
                    <button onclick="editUndo()" id="editUndoBtn" class="px-4 py-2 text-slate-400 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-white hover:text-blue-600 transition-all active:scale-95 flex items-center gap-2 opacity-50 cursor-not-allowed group">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                        Undo
                    </button>
                    <div class="w-[1px] bg-slate-200 h-3 self-center my-auto"></div>
                    <button onclick="editRedo()" id="editRedoBtn" class="px-4 py-2 text-slate-400 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-white hover:text-blue-600 transition-all active:scale-95 flex items-center gap-2 opacity-50 cursor-not-allowed group">
                        Redo
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 10H11a8 8 0 00-8 8v2m18-10l-6 6m6-6l-6-6"/></svg>
                    </button>
                </div>

                <button onclick="openEditBulkModal()" class="px-5 py-2.5 bg-blue-50 text-blue-600 rounded-xl text-[9px] font-black uppercase tracking-widest flex items-center gap-2 shadow-sm hover:bg-blue-100 transition-all active:scale-95 italic border border-blue-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16.862 4.487l1.688-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"/></svg>
                    Bulk Edit
                </button>

                <button onclick="saveEditChanges()" class="px-5 py-2.5 bg-slate-900 text-white rounded-xl text-[9px] font-black uppercase tracking-widest flex items-center gap-2 shadow-lg hover:bg-blue-600 transition-all active:scale-95 italic">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Save Changes
                </button>
            </div>
        </div>

        {{-- ── Asset Source Table ── --}}
        <div id="editPanelAssetSource" class="flex-grow flex flex-col min-h-0">
            <div id="editSourceScroll" class="xls-scroll-wrap custom-scroll overflow-x-auto overflow-y-auto transition-all duration-300" style="max-height: calc(100vh - 450px);">
                <table class="w-full border-collapse" style="min-width:2800px;">
                    <thead class="sticky top-0 bg-slate-50 z-20 shadow-sm">
                        <tr>
                            <th class="xls-th w-10 text-center sticky left-0 z-30">#</th>
                            <th class="xls-th col-identity" style="min-width:140px">CLASSIFICATION</th>
                            <th class="xls-th col-identity" style="min-width:140px">CATEGORY</th>
                            <th class="xls-th col-identity" style="min-width:140px">ITEM</th>
                            <th class="xls-th col-context" style="min-width:180px">DESCRIPTION</th>
                            <th class="xls-th col-context" style="min-width:100px">UNIT</th>
                            <th class="xls-th col-status" style="min-width:160px">ACQUISITION SOURCE</th>
                            <th class="xls-th col-status" style="min-width:140px">MODE</th>
                            <th class="xls-th col-personnel" style="min-width:160px">SOURCE PERSONNEL</th>
                            <th class="xls-th col-personnel" style="min-width:160px">PERSONNEL POS</th>
                            <th class="xls-th col-financial text-right" style="min-width:120px">COST / UNIT (₱)</th>
                            <th class="xls-th col-financial text-right" style="min-width:80px">QTY</th>
                            <th class="xls-th col-temporal text-right" style="min-width:110px">USEFUL LIFE (YRS)</th>
                            <th class="xls-th col-temporal" style="min-width:140px">ACCEPTANCE DATE</th>
                            <th class="xls-th col-status" style="min-width:160px">CONDITION</th>
                        </tr>
                    </thead>
                    <tbody id="editAssetSourceBody"></tbody>
                </table>
            </div>
        </div>

        {{-- ── Asset Distribution Table ── --}}
        <div id="editPanelAssetDist" class="hidden flex-grow flex flex-col min-h-0">
            <div id="editDistScroll" class="xls-scroll-wrap custom-scroll overflow-x-auto overflow-y-auto transition-all duration-300" style="max-height: calc(100vh - 450px);">
                <table class="w-full border-collapse" style="min-width:2400px;">
                    <thead class="sticky top-0 bg-slate-50 z-20 shadow-sm">
                        <tr>
                            <th class="xls-th w-10 text-center sticky left-0 z-30">#</th>
                            <th class="xls-th col-context" style="min-width:120px">REGION</th>
                            <th class="xls-th col-context" style="min-width:180px">DIVISION</th>
                            <th class="xls-th col-personnel" style="min-width:200px">EMPLOYEE SEARCH</th>
                            <th class="xls-th col-personnel" style="min-width:120px">EMPLOYEE ID</th>
                            <th class="xls-th col-personnel" style="min-width:150px">EMPLOYEE NAME</th>
                            <th class="xls-th col-personnel" style="min-width:150px">EMPLOYEE POSITION</th>
                            <th class="xls-th col-personnel" style="min-width:120px">EMPLOYEE STATUS</th>
                            <th class="xls-th col-identity" style="min-width:200px">SCHOOL/OFFICE SEARCH</th>
                            <th class="xls-th col-identity" style="min-width:120px">OFFICE/SCHOOL ID</th>
                            <th class="xls-th col-identity" style="min-width:150px">OFFICE/SCHOOL TYPE</th>
                            <th class="xls-th col-identity" style="min-width:180px">OFFICE/SCHOOL NAME</th>
                            <th class="xls-th col-identity" style="min-width:180px">LOCATION</th>
                            <th class="xls-th col-identity" style="min-width:150px">PROPERTY NO.</th>
                            <th class="xls-th col-financial text-right" style="min-width:130px">ACQUISITION COST (₱)</th>
                            <th class="xls-th col-temporal" style="min-width:140px">ACQUISITION DATE</th>
                        </tr>
                    </thead>
                    <tbody id="editAssetDistBody"></tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="px-5 py-3 border-t border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center gap-6">
                <p id="editRowCountLabel" class="text-[9px] font-black text-slate-400 uppercase tracking-widest">0 Rows</p>
                <div id="editPaginationControls" class="flex items-center gap-3 border-l border-slate-200 pl-6">
                    <button onclick="editPrevPage()" id="editPrevBtn" class="pg-btn">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
                        Prev
                    </button>
                    <div class="flex items-center gap-2 px-4 py-2 bg-slate-900/40 dark:bg-slate-800/60 rounded-xl border border-slate-200/10 backdrop-blur-md">
                        <span id="editCurrentPage" class="text-[10px] font-black text-slate-700 dark:text-blue-400">1</span>
                        <span class="text-[10px] font-bold text-slate-400">/</span>
                        <span id="editTotalPages" class="text-[10px] font-black text-slate-400">1</span>
                    </div>
                    <button onclick="editNextPage()" id="editNextBtn" class="pg-btn">
                        Next
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Edit Modal -->
<div id="editBulkModal" class="fixed inset-0 z-[150] flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
    <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-md" onclick="closeEditBulkModal()"></div>
    <div class="bg-[#1e293b] border border-slate-700 rounded-[2rem] shadow-2xl w-[95vw] max-w-5xl max-h-[90vh] flex flex-col relative z-10 transform scale-95 transition-transform duration-300 overflow-hidden">
        
        {{-- Header --}}
        <div class="px-6 md:px-10 py-6 border-b border-slate-700/50 flex flex-col md:flex-row md:items-center justify-between gap-6 bg-slate-800/30">
            <div class="flex-grow">
                <h3 class="text-2xl md:text-3xl font-black text-blue-400 uppercase tracking-tighter italic">Bulk Edit Rows</h3>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] mt-1">Update specific columns for a range of rows</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-4 bg-slate-900/50 px-5 py-3 rounded-2xl border border-slate-700 shadow-inner">
                    <div class="flex flex-col">
                        <label class="text-[8px] font-black text-slate-500 uppercase tracking-widest leading-none mb-1">From Row #</label>
                        <input type="number" id="editBulkFrom" value="1" min="1" class="w-12 bg-transparent font-black text-white outline-none text-lg leading-none">
                    </div>
                    <div class="w-px h-6 bg-slate-700"></div>
                    <div class="flex flex-col">
                        <label class="text-[8px] font-black text-slate-500 uppercase tracking-widest leading-none mb-1">To Row #</label>
                        <input type="number" id="editBulkTo" value="1" min="1" class="w-16 bg-transparent font-black text-white outline-none text-lg leading-none">
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button onclick="closeEditBulkModal()" class="px-6 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-white transition-all italic">Cancel</button>
                    <button onclick="applyEditBulk()" class="px-8 py-4 rounded-2xl text-[11px] font-black text-white bg-blue-600 hover:bg-blue-500 shadow-xl shadow-blue-500/20 transition-all active:scale-95 uppercase tracking-widest italic">Apply Bulk Edit</button>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="p-6 md:p-10 overflow-y-auto custom-scroll flex-1 space-y-12 bg-slate-800/20">
            
            {{-- Source Section --}}
            <div class="animate-fade">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-8 h-8 bg-blue-500/10 text-blue-400 rounded-xl flex items-center justify-center text-xs font-black shrink-0 border border-blue-500/20 shadow-lg shadow-blue-500/5">1</div>
                    <h4 class="font-black text-white uppercase tracking-[0.15em] text-sm italic">Asset Data Entry (Source)</h4>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-8">
                    <div class="relative space-y-2" style="position:relative;overflow:visible">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Classification</label>
                        <input type="text" id="ebClassification" autocomplete="off" 
                            oninput="filterBulkEditClassDropdown(this.value)" onfocus="filterBulkEditClassDropdown(this.value)"
                            class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="Search Classification...">
                        <div id="bulk-edit-class-dd" class="xls-custom-dd" style="display:none; width:100%;"></div>
                    </div>
                    <div class="relative space-y-2" style="position:relative;overflow:visible">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Category</label>
                        <input type="text" id="ebCategory" autocomplete="off" 
                            oninput="filterBulkEditCatDropdown(this.value)" onfocus="filterBulkEditCatDropdown(this.value)"
                            class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="Search Category...">
                        <div id="bulk-edit-cat-dd" class="xls-custom-dd" style="display:none; width:100%;"></div>
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Item</label>
                        <input type="text" id="ebItem" autocomplete="off" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="Leave empty to ignore">
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Description</label>
                        <input type="text" id="ebDescription" autocomplete="off" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="Leave empty to ignore">
                    </div>

                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Unit of Measurement</label>
                        <input type="text" id="ebUom" autocomplete="off" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="Leave empty to ignore">
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Acquisition Source</label>
                        <input type="text" id="ebAcqSource" autocomplete="off" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="Leave empty to ignore">
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Mode of Procurement</label>
                        <input type="text" id="ebMode" autocomplete="off" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="Leave empty to ignore">
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Source Personnel</label>
                        <input type="text" id="ebPersonnel" autocomplete="off" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="Leave empty to ignore">
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Personnel Position</label>
                        <input type="text" id="ebPosition" autocomplete="off" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="Leave empty to ignore">
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:col-span-2">
                        <div class="relative space-y-2">
                            <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Cost per Unit</label>
                            <div class="relative">
                                <input type="number" id="ebCost" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all text-right pr-12 placeholder:text-slate-700" placeholder="Leave empty to ignore" min="0" step="0.01">
                                <span class="absolute right-5 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-600">₱</span>
                            </div>
                        </div>
                        <div class="relative space-y-2">
                            <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Quantity</label>
                            <input type="number" id="ebQty" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all text-right placeholder:text-slate-700" placeholder="Leave empty to ignore" min="0" step="1">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:col-span-2">
                        <div class="relative space-y-2">
                            <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Expected Useful Life</label>
                            <div class="relative">
                                <input type="number" id="ebLife" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all text-right pr-14 placeholder:text-slate-700" placeholder="Leave empty to ignore" min="0" step="1">
                                <span class="absolute right-5 top-1/2 -translate-y-1/2 text-[8px] font-black text-slate-600">YRS</span>
                            </div>
                        </div>
                        <div class="relative space-y-2">
                            <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Acceptance Date</label>
                            <input type="date" id="ebDate1" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all [color-scheme:dark]">
                        </div>
                    </div>

                    <div class="relative space-y-2 md:col-span-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Condition</label>
                        <select id="ebRemarks" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                            <option value="" class="bg-[#1e293b]">-- IGNORE CHANGES --</option>
                            <option value="Good Condition" class="bg-[#1e293b]">GOOD CONDITION</option>
                            <option value="Needs Repair" class="bg-[#1e293b]">NEEDS REPAIR</option>
                            <option value="Not Useable" class="bg-[#1e293b]">NOT USEABLE</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-700/50"></div>

            {{-- Target Section --}}
            <div class="animate-fade">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-8 h-8 bg-blue-500/10 text-blue-400 rounded-xl flex items-center justify-center text-xs font-black shrink-0 border border-blue-500/20 shadow-lg shadow-blue-500/5">2</div>
                    <h4 class="font-black text-white uppercase tracking-[0.15em] text-sm italic">Asset Distribution (Target)</h4>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-8">
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1 block">Region</label>
                        <div class="w-full bg-slate-900/30 border border-slate-800 rounded-xl px-5 py-4 text-xs font-black text-slate-600 uppercase tracking-widest italic opacity-50 cursor-not-allowed">Region IX</div>
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1 block">Division</label>
                        <div class="w-full bg-slate-900/30 border border-slate-800 rounded-xl px-5 py-4 text-xs font-black text-slate-600 uppercase tracking-widest italic opacity-50 cursor-not-allowed">Division of Zamboanga City</div>
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Office/School Type</label>
                        <input type="text" list="dl-school-type" id="ebSchoolType" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-600" placeholder="-- IGNORE CHANGES --">
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">School ID</label>
                        <input type="text" id="ebSchoolId" autocomplete="off" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="Leave empty to ignore" inputmode="numeric">
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Office/School Name</label>
                        <input type="text" id="ebSchoolName" autocomplete="off" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="Leave empty to ignore" oninput="
                            if(typeof allSchoolsList !== 'undefined' && typeof detectItemSchoolType === 'function'){
                                const s = allSchoolsList.find(x => x.name.toLowerCase() === this.value.toLowerCase());
                                if(s) {
                                    const t = detectItemSchoolType(s.name) || '';
                                    document.getElementById('ebSchoolType').value = t;
                                    document.getElementById('ebSchoolId').value = s.school_id;
                                    if(typeof cleanSchoolNameForLocation === 'function') document.getElementById('ebLocation').value = cleanSchoolNameForLocation(s.name);
                                } else if(this.value.trim()){
                                    const t = detectItemSchoolType(this.value);
                                    if(t) document.getElementById('ebSchoolType').value = t;
                                    document.getElementById('ebSchoolId').value = '';
                                    if(typeof cleanSchoolNameForLocation === 'function') document.getElementById('ebLocation').value = cleanSchoolNameForLocation(this.value);
                                }
                            }">
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Custodian Full Name</label>
                        <div class="grid grid-cols-3 gap-2">
                            <input type="text" id="ebCustFirst" placeholder="First" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-4 text-[10px] font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                            <input type="text" id="ebCustMiddle" placeholder="Middle" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-4 text-[10px] font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                            <input type="text" id="ebCustLast" placeholder="Last" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-4 text-[10px] font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                        </div>
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Custodian Position</label>
                        <input type="text" id="ebCustPos" autocomplete="off" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="Leave empty to ignore">
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Custodian Contact No.</label>
                        <input type="text" id="ebCustContact" autocomplete="off" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="Leave empty to ignore">
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Nature of Occupancy</label>
                        <input type="text" id="ebOccupancy" autocomplete="off" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="Leave empty to ignore">
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Location / Room</label>
                        <input type="text" id="ebLocation" autocomplete="off" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="Leave empty to ignore">
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Property Number</label>
                        <input type="text" id="ebPropertyNo" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all placeholder:text-slate-700" placeholder="Leave empty to ignore">
                    </div>
                    <div class="relative space-y-2">
                        <label class="text-[9px] font-black text-blue-400 uppercase tracking-[0.2em] ml-1 block">Acquisition Date</label>
                        <input type="date" id="ebDate2" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-xs font-bold text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all [color-scheme:dark]">
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
    let editAllData = [];
    let editOriginalData = []; // Deep copy to check diffs
    let editUndoStack = [];
    let editRedoStack = [];
    let editCurrentPage = 1;
    const editRowsPerPage = 50;

    function autofillLocationEdit(distId, val) {
        const row = editAllData.find(r => r.dist_id === distId);
        if(!row) return;
        const loc = globalLocations.find(l => l.name === val);
        if(loc) {
            row['school_id'] = loc.entity_id;
            row['school_type'] = loc.type || '';
            row['office_school_name'] = loc.name;
            row['location'] = loc.location || '';
            renderEditTable(); // Optional: Re-render to show readonly updates, or just DOM mutate
        }
    }

    function autofillEmployeeEdit(distId, val) {
        const row = editAllData.find(r => r.dist_id === distId);
        if(!row) return;
        const emp = globalEmployees.find(e => e.full_name === val);
        if(emp) {
            row['custodian_employee_id'] = emp.employee_id;
            row['custodian_name'] = emp.full_name;
            row['custodian_position'] = emp.position || '';
            row['custodian_status'] = emp.status || '';
            renderEditTable(); 
        }
    }

    function toggleEditFilters() {
        const section = document.getElementById('editFilterSection');
        const btn = document.getElementById('toggleEditFilterBtn');
        const srcScroll = document.getElementById('editSourceScroll');
        const distScroll = document.getElementById('editDistScroll');
        
        if (section.classList.contains('hidden')) {
            section.classList.remove('hidden');
            srcScroll.classList.remove('!max-h-[750px]');
            distScroll.classList.remove('!max-h-[750px]');
            btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg> Hide Filters`;
        } else {
            section.classList.add('hidden');
            srcScroll.classList.add('!max-h-[750px]');
            distScroll.classList.add('!max-h-[750px]');
            btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg> Show Filters`;
        }
    }

    function initInventoryEdit() {
        // Fetch filters on load
        fetch('{{ route("api.reports.filters") }}?report_type=ALL')
            .then(res => res.json())
            .then(data => {
                populateEditSelect('editFilterClass', data.classifications);
                populateEditSelect('editFilterCat', data.categories);
                populateEditSelect('editFilterItem', data.items);
                populateEditSelect('editFilterSchool', data.schools);
                populateEditSelect('editFilterSource', data.sources);
                populateEditSelect('editFilterMode', data.modes);
            });
    }

    function clearEditFilters() {
        ['editFilterClass', 'editFilterCat', 'editFilterItem', 'editFilterSort', 'editFilterSchool', 'editFilterSource', 'editFilterMode', 'editFilterDate', 'editFilterIntegrity'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        editFetchData();
    }

    function populateEditSelect(id, options) {
        const sel = document.getElementById(id);
        if (!sel) return;
        const originalFirstOption = sel.options[0];
        sel.innerHTML = '';
        sel.appendChild(originalFirstOption);
        options.forEach(opt => {
            const el = document.createElement('option');
            el.value = opt; el.textContent = opt;
            sel.appendChild(el);
        });
    }

    function editFetchData() {
        const filterIds = {
            'editFilterClass': 'classification',
            'editFilterCat': 'category',
            'editFilterItem': 'article',
            'editFilterSchool': 'schoolName',
            'editFilterSource': 'source',
            'editFilterMode': 'mode',
            'editFilterDate': 'dateAcquired',
            'editFilterIntegrity': 'emptyCol',
            'editFilterSort': 'sortCost'
        };

        const filters = {};
        for (const [id, key] of Object.entries(filterIds)) {
            const el = document.getElementById(id);
            filters[key] = el ? el.value : '';
        }

        const loader = document.getElementById('editAssetLoading');
        if (loader) loader.classList.remove('hidden');

        fetch('{{ route("api.inventory.edit_preview") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ report_type: 'ALL', filters: filters })
        })
        .then(res => res.json())
        .then(data => {
            editAllData = data.rows || [];
            editOriginalData = JSON.parse(JSON.stringify(editAllData));
            editCurrentPage = 1;
            editUndoStack = [];
            editRedoStack = [];
            updateEditUndoBtn();
            renderEditTable();
            if (editAllData.length === 0) {
                Swal.fire({
                    title: 'No Assets Found',
                    text: 'No records match your current filter configuration.',
                    icon: 'info',
                    customClass: { popup: 'rounded-[2rem]' }
                });
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire({
                title: 'Error',
                text: 'Failed to load inventory data.',
                icon: 'error',
                customClass: { popup: 'rounded-[2rem]' }
            });
        })
        .finally(() => {
            if (loader) loader.classList.add('hidden');
        });
    }

    function switchEditAssetTab(tab) {
        const srcPanel = document.getElementById('editPanelAssetSource');
        const distPanel = document.getElementById('editPanelAssetDist');
        const tabSrc   = document.getElementById('editTabAssetSource');
        const tabDst   = document.getElementById('editTabAssetDist');
        const label    = document.getElementById('editAssetTabLabel');
        const ON  = 'px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg bg-blue-600 text-white shadow-sm transition-all';
        const OFF = 'px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg text-slate-500 hover:text-slate-700 transition-all';
        if (tab === 'source') {
            srcPanel.classList.remove('hidden');
            distPanel.classList.add('hidden');
            tabSrc.className = ON; tabDst.className = OFF;
            label.textContent = 'Asset Source';
        } else {
            srcPanel.classList.add('hidden');
            distPanel.classList.remove('hidden');
            tabSrc.className = OFF; tabDst.className = ON;
            label.textContent = 'Asset Distribution';
        }
    }

    function renderEditTable() {
        const srcTbody = document.getElementById('editAssetSourceBody');
        const dstTbody = document.getElementById('editAssetDistBody');
        if (!srcTbody || !dstTbody) return;
        srcTbody.innerHTML = '';
        dstTbody.innerHTML = '';
        
        if (editAllData.length === 0) {
            document.getElementById('editRowCountLabel').textContent = "0 Rows";
            return;
        }

        const start = (editCurrentPage - 1) * editRowsPerPage;
        const end = start + editRowsPerPage;
        const pageData = editAllData.slice(start, end);

        pageData.forEach((row, idx) => {
            const displayNum = start + idx + 1;
            const orig = editOriginalData.find(o => String(o.dist_id) === String(row.dist_id)) || {};
            
            const renderCell = (col, val, isReadonly) => {
                const val1 = String(val ?? '').trim();
                const val2 = String(orig[col] ?? '').trim();
                const hasChanged = val1 !== val2;
                const badgeHtml = hasChanged ? `<span class="update-badge">Update</span>` : '';
                const safeVal = (val ?? '').toString().replace(/"/g, '&quot;');
                
                if (isReadonly) {
                    return `<td class="xls-td p-0 relative"><input type="text" data-id="${row.dist_id}" data-col="${col}" class="xls-input edit-readonly w-full h-full" value="${safeVal}" readonly tabindex="-1">${badgeHtml}</td>`;
                }
                
                if (col === 'classification') {
                    return `<td class="xls-td p-0 relative" style="overflow:visible">
                        <input type="text" data-id="${row.dist_id}" data-col="classification" value="${safeVal}" oninput="syncEditClass(${row.dist_id}, this.value); filterEditClassDropdown(${row.dist_id}, this.value)" onfocus="filterEditClassDropdown(${row.dist_id}, this.value)" autocomplete="off" class="xls-input w-full h-full bg-transparent">
                        <div id="edit-class-dd-${row.dist_id}" class="xls-custom-dd" style="display:none; width: 100%;"></div>
                        ${badgeHtml}
                    </td>`;
                }

                if (col === 'category') {
                    return `<td class="xls-td p-0 relative" style="overflow:visible">
                        <input type="text" data-id="${row.dist_id}" data-col="category" value="${safeVal}" oninput="syncEditCat(${row.dist_id}, this.value); filterEditCatDropdown(${row.dist_id}, this.value)" onfocus="filterEditCatDropdown(${row.dist_id}, this.value)" autocomplete="off" class="xls-input w-full h-full bg-transparent">
                        <div id="edit-cat-dd-${row.dist_id}" class="xls-custom-dd" style="display:none; width: 100%;"></div>
                        ${badgeHtml}
                    </td>`;
                }

                if (col === 'remarks') {
                    return `<td class="xls-td p-0 relative">
                        <select data-id="${row.dist_id}" data-col="${col}" onchange="syncEditCell(this)" class="xls-input w-full h-full bg-transparent">
                            <option value="Good Condition" ${val === 'Good Condition' ? 'selected' : ''}>Good Condition</option>
                            <option value="Needs Repair" ${val === 'Needs Repair' ? 'selected' : ''}>Needs Repair</option>
                            <option value="Not Useable" ${val === 'Not Useable' ? 'selected' : ''}>Not Useable</option>
                        </select>
                        ${badgeHtml}
                    </td>`;
                }
                
                return `<td class="xls-td p-0 relative"><input type="text" data-id="${row.dist_id}" data-col="${col}" value="${safeVal}" onchange="syncEditCell(this)" class="xls-input w-full h-full bg-transparent">${badgeHtml}</td>`;
            };

            // Source Table Row
            const srcTr = document.createElement('tr');
            srcTr.className = 'xls-row group border-b border-slate-100';
            srcTr.innerHTML = `
                <td class="xls-td text-center sticky left-0 w-10 bg-white z-10"><span class="text-[10px] font-black text-slate-300">${displayNum}</span></td>
                ${renderCell('classification', row.classification, false)}
                ${renderCell('category', row.category, false)}
                ${renderCell('article', row.article, false)}
                ${renderCell('description', row.description, false)}
                ${renderCell('unit_of_measurement', row.unit_of_measurement, false)}
                ${renderCell('acq_source', row.acq_source, false)}
                ${renderCell('mode_of_acquisition', row.mode_of_acquisition, false)}
                ${renderCell('source_personnel', row.source_personnel, false)}
                ${renderCell('personnel_position', row.personnel_position, false)}
                ${renderCell('asset_cost', row.asset_cost, false)}
                ${renderCell('quantity', row.quantity, false)}
                ${renderCell('estimated_useful_life', row.estimated_useful_life, false)}
                ${renderCell('acceptance_date', row.acceptance_date, false)}
                ${renderCell('remarks', row.remarks, false)}
            `;
            srcTbody.appendChild(srcTr);

            // Distribution Table Row
            const dstTr = document.createElement('tr');
            dstTr.className = 'xls-row group border-b border-slate-100';
            const costVal = parseFloat(row.asset_cost || 0);
            const qtyVal = parseInt(row.quantity || 0);
            const totalCost = (costVal * qtyVal).toFixed(2);
            dstTr.innerHTML = `
                <td class="xls-td text-center sticky left-0 w-10 bg-white z-10"><span class="text-[10px] font-black text-slate-300">${displayNum}</span></td>
                <td class="xls-td p-0 relative"><span class="xls-const w-full h-full flex items-center px-4">Region IX</span></td>
                <td class="xls-td p-0 relative"><span class="xls-const w-full h-full flex items-center px-4">Division of Zamboanga City</span></td>
                <td class="xls-td p-0 relative" style="overflow:visible">
                    <input type="text" oninput="autofillEmployeeEdit(${row.dist_id}, this.value); filterEditEmpDropdown(${row.dist_id}, this.value)" onfocus="filterEditEmpDropdown(${row.dist_id}, this.value)" data-id="${row.dist_id}" data-col="employee_search" value="" autocomplete="off" class="xls-input w-full h-full bg-transparent" placeholder="Search Employee...">
                    <div id="edit-emp-dd-${row.dist_id}" class="xls-custom-dd" style="display:none; width: 100%;"></div>
                </td>
                ${renderCell('custodian_employee_id', row.custodian_employee_id, true)}
                ${renderCell('custodian_name', row.custodian_name, true)}
                ${renderCell('custodian_position', row.custodian_position, true)}
                ${renderCell('custodian_status', row.custodian_status, true)}
                <td class="xls-td p-0 relative" style="overflow:visible">
                    <input type="text" oninput="autofillLocationEdit(${row.dist_id}, this.value); filterEditLocDropdown(${row.dist_id}, this.value)" onfocus="filterEditLocDropdown(${row.dist_id}, this.value)" data-id="${row.dist_id}" data-col="school_search" value="" autocomplete="off" class="xls-input w-full h-full bg-transparent" placeholder="Search Location...">
                    <div id="edit-loc-dd-${row.dist_id}" class="xls-custom-dd" style="display:none; width: 100%;"></div>
                </td>
                ${renderCell('school_id', row.school_id, true)}
                ${renderCell('school_type', row.school_type, true)}
                ${renderCell('office_school_name', row.office_school_name, true)}
                ${renderCell('location', row.location, true)}
                ${renderCell('property_number', row.property_number, false)}
                <td class="xls-td p-0 relative"><input type="text" class="xls-input edit-readonly text-right w-full h-full" value="${totalCost}" readonly tabindex="-1"></td>
                ${renderCell('acquisition_date', row.acquisition_date, false)}
            `;
            dstTbody.appendChild(dstTr);
        });

        const totalPages = Math.ceil(editAllData.length / editRowsPerPage) || 1;
        document.getElementById('editRowCountLabel').textContent = editAllData.length + " Rows (paired)";
        
        document.getElementById('editCurrentPage').textContent = editCurrentPage;
        document.getElementById('editTotalPages').textContent = totalPages;
        document.getElementById('editPrevBtn').disabled = editCurrentPage === 1;
        document.getElementById('editNextBtn').disabled = editCurrentPage === totalPages;
    }

    function syncEditClass(distId, val) {
        const row = editAllData.find(r => r.dist_id === distId);
        if (row) {
            const oldVal = row['classification'] ?? '';
            if (String(oldVal).trim() !== String(val).trim()) {
                editUndoStack.push({ type: 'single', rowId: distId, col: 'classification', oldVal: oldVal, newVal: val });
                row['classification'] = val;
                editRedoStack = [];
                updateEditUndoBtn();
            }
        }
    }

    function syncEditCat(distId, val) {
        const row = editAllData.find(r => r.dist_id === distId);
        if (row) {
            const oldVal = row['category'] ?? '';
            if (String(oldVal).trim() !== String(val).trim()) {
                editUndoStack.push({ type: 'single', rowId: distId, col: 'category', oldVal: oldVal, newVal: val });
                row['category'] = val;
                editRedoStack = [];
                updateEditUndoBtn();
            }
        }
    }

    function syncEditCell(input) {
        const id = parseInt(input.getAttribute('data-id'));
        const col = input.getAttribute('data-col');
        const newVal = input.value;
        const row = editAllData.find(r => r.dist_id === id);
        if (row) {
            const oldVal = row[col] ?? '';
            if (String(oldVal).trim() !== String(newVal).trim()) {
                editUndoStack.push({ type: 'single', rowId: id, col: col, oldVal: oldVal, newVal: newVal });
                row[col] = newVal;
                editRedoStack = [];
                updateEditUndoBtn();
                renderEditTable(); 
            }
        }
    }

    function editPrevPage() { if (editCurrentPage > 1) { editCurrentPage--; renderEditTable(); } }
    function editNextPage() { const t = Math.ceil(editAllData.length/editRowsPerPage); if (editCurrentPage < t) { editCurrentPage++; renderEditTable(); } }

    function openEditBulkModal() {
        if(editAllData.length === 0) return Swal.fire('No Data', 'Load assets first.', 'info');
        const m = document.getElementById('editBulkModal');
        m.classList.remove('hidden');
        document.querySelectorAll('#editBulkModal input:not([id="editBulkFrom"]):not([id="editBulkTo"])').forEach(i => i.value = '');
        document.getElementById('ebRemarks').value = '';

        // Default From=1, To=total fetched rows
        const maxRows = editAllData.length;
        const fromInput = document.getElementById('editBulkFrom');
        const toInput   = document.getElementById('editBulkTo');
        fromInput.value = 1;
        fromInput.max   = maxRows;
        toInput.value   = maxRows;
        toInput.max     = maxRows;

        // Live warning if user exceeds max
        toInput.oninput = function() {
            const val = parseInt(this.value);
            if (val > maxRows) {
                this.style.color = '#ef4444';
            } else {
                this.style.color = '';
            }
        };
        
        setTimeout(() => {
            m.classList.remove('opacity-0');
            m.querySelector('.transform').classList.remove('scale-95');
        }, 10);
    }
    
    function closeEditBulkModal() {
        const m = document.getElementById('editBulkModal');
        m.classList.add('opacity-0');
        m.querySelector('.transform').classList.add('scale-95');
        setTimeout(() => m.classList.add('hidden'), 300);
    }

    function applyEditBulk() {
        const from = parseInt(document.getElementById('editBulkFrom').value);
        const to = parseInt(document.getElementById('editBulkTo').value);

        const maxRows = editAllData.length;

        if (isNaN(from) || isNaN(to) || from < 1 || to < from) {
            return Swal.fire('Invalid Range', 'Enter a valid row range (From must be ≤ To).', 'error');
        }

        if (to > maxRows) {
            return Swal.fire({
                icon: 'warning',
                title: 'Exceeds Total Rows',
                html: `<b>To Row</b> cannot exceed <b>${maxRows}</b> (total fetched assets).<br>Please enter a value within range.`,
                confirmButtonColor: '#c00000',
                customClass: { popup: 'rounded-[2rem]' }
            });
        }

        if (from > maxRows) {
            return Swal.fire('Invalid Range', `From Row cannot exceed the total of ${maxRows} fetched assets.`, 'error');
        }

        const toLimit = Math.min(to, editAllData.length);
        
        const bulkMapping = {
            'ebClassification': 'classification',
            'ebCategory': 'category',
            'ebItem': 'article',
            'ebDescription': 'description',
            'ebUom': 'unit_of_measurement',
            'ebAcqSource': 'acq_source',
            'ebMode': 'mode_of_acquisition',
            'ebPersonnel': 'source_personnel',
            'ebPosition': 'personnel_position',
            'ebCost': 'asset_cost',
            'ebQty': 'quantity',
            'ebLife': 'estimated_useful_life',
            'ebDate1': 'acceptance_date',
            'ebRemarks': 'remarks',
            'ebSchoolType': 'school_type',
            'ebSchoolId': 'school_id',
            'ebSchoolName': 'office_school_name',
            'ebOccupancy': 'nature_of_occupancy',
            'ebLocation': 'location',
            'ebPropertyNo': 'property_number',
            'ebDate2': 'acquisition_date'
        };

        const activeUpdates = {};
        let hasUpdates = false;

        for (const [inputId, colKey] of Object.entries(bulkMapping)) {
            const val = document.getElementById(inputId).value;
            if (val !== "") {
                activeUpdates[colKey] = val;
                hasUpdates = true;
            }
        }

        if (!hasUpdates) {
            return Swal.fire('No Changes', 'You did not fill any fields to update.', 'info');
        }

        const previousStates = [];

        for (let i = from - 1; i < toLimit; i++) {
            const row = editAllData[i];
            const rowPreviousState = { rowId: row.dist_id, changes: [] };
            let rowChanged = false;

            for (const [col, newVal] of Object.entries(activeUpdates)) {
                const oldVal = row[col] ?? '';
                if (String(oldVal).trim() !== String(newVal).trim()) {
                    rowPreviousState.changes.push({ col: col, oldVal: oldVal });
                    row[col] = newVal;
                    rowChanged = true;
                }
            }

            if (rowChanged) {
                previousStates.push(rowPreviousState);
            }
        }

        if (previousStates.length > 0) {
            editUndoStack.push({ type: 'bulkMulti', states: previousStates });
            editRedoStack = [];
            updateEditUndoBtn();
            renderEditTable();
            Swal.fire({ icon: 'success', title: 'Bulk Edit Applied', text: `Updated ${previousStates.length} rows.`, timer: 1500, showConfirmButton: false });
        }

        closeEditBulkModal();
    }

    function editUndo() {
        if (editUndoStack.length === 0) return;
        const action = editUndoStack.pop();
        const redoStates = [];
        if (action.type === 'single') {
            const row = editAllData.find(r => r.dist_id === action.rowId);
            if (row) {
                redoStates.push({ rowId: row.dist_id, changes: [{ col: action.col, oldVal: row[action.col] }] });
                row[action.col] = action.oldVal;
            }
        } else if (action.type === 'bulkMulti') {
            action.states.forEach(state => {
                const row = editAllData.find(r => r.dist_id === state.rowId);
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
        editRedoStack.push({ type: 'bulkMulti', states: redoStates });
        updateEditUndoBtn();
        renderEditTable();
    }

    function editRedo() {
        if (editRedoStack.length === 0) return;
        const action = editRedoStack.pop();
        const undoStates = [];
        action.states.forEach(state => {
            const row = editAllData.find(r => r.dist_id === state.rowId);
            if (row) {
                const us = { rowId: state.rowId, changes: [] };
                state.changes.forEach(change => {
                    us.changes.push({ col: change.col, oldVal: row[change.col] });
                    row[change.col] = change.oldVal;
                });
                undoStates.push(us);
            }
        });
        editUndoStack.push({ type: 'bulkMulti', states: undoStates });
        updateEditUndoBtn();
        renderEditTable();
    }

    function updateEditUndoBtn() {
        const uBtn = document.getElementById('editUndoBtn');
        const rBtn = document.getElementById('editRedoBtn');
        if (uBtn) uBtn.className = editUndoStack.length > 0 ? 'px-4 py-2 text-blue-600 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-white transition-all active:scale-95 flex items-center gap-2' : 'px-4 py-2 text-slate-400 rounded-xl text-[9px] font-black uppercase tracking-widest opacity-50 cursor-not-allowed flex items-center gap-2';
        if (rBtn) rBtn.className = editRedoStack.length > 0 ? 'px-4 py-2 text-blue-600 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-white transition-all active:scale-95 flex items-center gap-2' : 'px-4 py-2 text-slate-400 rounded-xl text-[9px] font-black uppercase tracking-widest opacity-50 cursor-not-allowed flex items-center gap-2';
    }

    function saveEditChanges() {
        try {
            if (typeof Swal === 'undefined') {
                alert('SweetAlert is not loaded. Please wait or refresh.');
                return;
            }

            const updates = [];
            editAllData.forEach(row => {
                const orig = editOriginalData.find(o => String(o.dist_id) === String(row.dist_id));
                if (!orig) return;
                
                const changes = {};
                let hasChanged = false;
                
                const keys = [
                    'classification', 'category', 'article', 'description',
                    'unit_of_measurement', 'acq_source', 'asset_cost', 'quantity', 'estimated_useful_life',
                    'property_number', 'location', 'mode_of_acquisition', 
                    'source_personnel', 'personnel_position', 'acceptance_date', 'remarks', 
                    'school_search', 'school_id', 'school_type', 'office_school_name', 'acquisition_date', 
                    'employee_search', 'custodian_employee_id', 'custodian_name', 'custodian_position', 'custodian_status'
                ];

                keys.forEach(k => {
                    const val1 = String(row[k] ?? '').trim();
                    const val2 = String(orig[k] ?? '').trim();
                    
                    if (val1 !== val2) {
                        changes[k] = row[k];
                        hasChanged = true;
                    }
                });

                if (hasChanged) {
                    const payload = {
                        dist_id: row.dist_id,
                        src_id: row.src_id
                    };
                    
                    if (changes.hasOwnProperty('classification')) payload.classification = changes.classification;
                    if (changes.hasOwnProperty('category')) payload.category = changes.category;
                    if (changes.hasOwnProperty('article')) payload.article = changes.article;
                    if (changes.hasOwnProperty('description')) payload.description = changes.description;
                    if (changes.hasOwnProperty('unit_of_measurement')) payload.uom = changes.unit_of_measurement;
                    if (changes.hasOwnProperty('acq_source')) payload.acq_source = changes.acq_source;
                    if (changes.hasOwnProperty('asset_cost')) payload.cost = changes.asset_cost;
                    if (changes.hasOwnProperty('quantity')) payload.qty = changes.quantity;
                    if (changes.hasOwnProperty('estimated_useful_life')) payload.useful_life = changes.estimated_useful_life;
                    if (changes.hasOwnProperty('property_number')) payload.property_no = changes.property_number;
                    if (changes.hasOwnProperty('location')) payload.location = changes.location;
                    if (changes.hasOwnProperty('mode_of_acquisition')) payload.mode = changes.mode_of_acquisition;
                    if (changes.hasOwnProperty('source_personnel')) payload.personnel = changes.source_personnel;
                    if (changes.hasOwnProperty('personnel_position')) payload.position = changes.personnel_position;
                    if (changes.hasOwnProperty('acceptance_date')) payload.acceptance_date = changes.acceptance_date;
                    if (changes.hasOwnProperty('remarks')) payload.remarks = changes.remarks;
                    if (changes.hasOwnProperty('acquisition_date')) payload.acquisition_date = changes.acquisition_date;
                    
                    // New Entity Relationships
                    if (changes.hasOwnProperty('custodian_employee_id')) payload.employee_id = changes.custodian_employee_id;
                    if (changes.hasOwnProperty('school_id')) payload.school_id = changes.school_id;

                    updates.push(payload);
                }
            });

            if (updates.length === 0) {
                return Swal.fire({
                    title: 'No Changes',
                    text: 'No records were modified.',
                    icon: 'info',
                    customClass: { popup: 'rounded-[2rem]' }
                });
            }

            Swal.fire({
                title: 'Save Changes?',
                text: `You are about to modify ${updates.length} records. This cannot be undone.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#c00000',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, Save Updates',
                cancelButtonText: 'Cancel',
                customClass: { popup: 'rounded-[2rem]' }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Saving...',
                        text: 'Please wait while we update the records.',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    fetch('{{ route("inventory.setup.updateBatch") }}', {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json', 
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ updates: updates })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Saved!',
                                text: data.message,
                                icon: 'success',
                                customClass: { popup: 'rounded-[2rem]' }
                            }).then(() => {
                                editOriginalData = JSON.parse(JSON.stringify(editAllData));
                                editUndoStack = [];
                                editRedoStack = [];
                                updateEditUndoBtn();
                                renderEditTable();
                                editFetchData();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: data.message || 'Failed to save',
                                icon: 'error',
                                customClass: { popup: 'rounded-[2rem]' }
                            });
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire({
                            title: 'Error',
                            text: 'Server error.',
                            icon: 'error',
                            customClass: { popup: 'rounded-[2rem]' }
                        });
                    });
                }
            });
        } catch (e) {
            console.error(e);
            alert('A JavaScript error occurred: ' + e.message);
        }
    }
</script>
