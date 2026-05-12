<div id="stepBuildingEdit" class="step-content">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-6">
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight italic uppercase text-blue-600">Building <span class="text-slate-900">Editor</span></h2>
            <p class="text-slate-400 text-sm font-bold uppercase mt-1 tracking-widest leading-tight">Bulk update master building records</p>
        </div>
        <div class="flex items-center gap-4">
            <button onclick="toggleBldgEditFilters()" id="toggleBldgEditFilterBtn" class="px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest text-slate-500 bg-white border border-slate-100 hover:border-blue-600 transition-all flex items-center gap-2 active:scale-95 shadow-sm italic">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg>
                Hide Filters
            </button>
        </div>
    </div>

    <!-- Filter Configuration -->
    <div id="bldgEditFilterSection" class="bg-white rounded-[2.5rem] shadow-lg border border-slate-100 p-8 mb-8 relative z-50 transition-all duration-300 origin-top">
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-8 relative z-10">
            {{-- Row 1 --}}
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Classification</label>
                <select id="bEditFilterClass" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-emerald-50 focus:border-emerald-500 transition-all text-slate-500">
                    <option value="">All Classifications</option>
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Office/School Type</label>
                <select id="bEditFilterType" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-emerald-50 focus:border-emerald-500 transition-all text-slate-500">
                    <option value="">All Types</option>
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Article</label>
                <select id="bEditFilterArticle" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-emerald-50 focus:border-emerald-500 transition-all text-slate-500">
                    <option value="">All Articles</option>
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Cost Sorting</label>
                <select id="bEditFilterSort" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-emerald-50 focus:border-emerald-500 transition-all text-slate-500">
                    <option value="">Default (ID)</option>
                    <option value="low_to_high">Acquisition Cost: Low to High</option>
                    <option value="high_to_low">Acquisition Cost: High to Low</option>
                </select>
            </div>

            {{-- Row 2 --}}
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">School Name</label>
                <select id="bEditFilterSchool" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-emerald-50 focus:border-emerald-500 transition-all text-slate-500">
                    <option value="">All Schools</option>
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Nature of Occupancy</label>
                <select id="bEditFilterOccupancy" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-emerald-50 focus:border-emerald-500 transition-all text-slate-500">
                    <option value="">All Occupancies</option>
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Date Constructed</label>
                <input type="date" id="bEditFilterDate" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold focus:ring-4 focus:ring-emerald-50 focus:border-emerald-500 transition-all text-slate-500">
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic text-red-500">Data Integrity (Empty Fields)</label>
                <select id="bEditFilterIntegrity" class="w-full bg-slate-50 border-red-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-500">
                    <option value="">No Integrity Filter</option>
                    <option value="classification">Missing Classification</option>
                    <option value="article">Missing Article</option>
                    <option value="description">Missing Description</option>
                    <option value="office_name">Missing School Name</option>
                    <option value="property_number">Missing Property Number</option>
                    <option value="acquisition_cost">Missing Acquisition Cost</option>
                    <option value="date_constructed">Missing Date Constructed</option>
                </select>
            </div>
        </div>
        <div class="mt-8 flex justify-between items-center relative z-10">
            <button onclick="document.getElementById('stepBuildingEdit').classList.remove('active'); document.getElementById('stepAddBuilding').classList.add('active');" class="px-5 py-2.5 bg-emerald-50 text-emerald-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-emerald-100 transition-all active:scale-95 italic">
                + Add New Buildings
            </button>
            <div class="flex items-center gap-8">
                <button onclick="clearBldgEditFilters()" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] hover:text-emerald-600 transition-all italic">Clear All Filters</button>
                <button onclick="bldgEditFetchData()" class="px-8 py-2.5 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-emerald-600 transition-all active:scale-95 shadow-lg shadow-slate-200 italic">Apply Configuration</button>
            </div>
        </div>

    </div>

    <!-- Data Grid -->
    <div id="bldgEditTableCard" class="bg-white rounded-[2rem] border border-slate-100 shadow-xl relative overflow-hidden flex flex-col">
        
        {{-- Toolbar --}}
        <div id="bldgEditToolbar" class="px-5 py-3 border-b border-slate-100 flex items-center justify-between gap-3 flex-wrap bg-slate-50">
            <div class="flex items-center gap-3">
                
                <span class="hidden md:block text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">Building Records</span>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex bg-slate-100 rounded-2xl p-1 gap-1 border border-slate-200">
                    <button onclick="bldgEditUndo()" id="bldgEditUndoBtn" class="px-4 py-2 text-slate-400 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-white hover:text-blue-600 transition-all active:scale-95 flex items-center gap-2 opacity-50 cursor-not-allowed group">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                        Undo
                    </button>
                    <div class="w-[1px] bg-slate-200 h-3 self-center my-auto"></div>
                    <button onclick="bldgEditRedo()" id="bldgEditRedoBtn" class="px-4 py-2 text-slate-400 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-white hover:text-blue-600 transition-all active:scale-95 flex items-center gap-2 opacity-50 cursor-not-allowed group">
                        Redo
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 10H11a8 8 0 00-8 8v2m18-10l-6 6m6-6l-6-6"/></svg>
                    </button>
                </div>

                <button onclick="openBldgEditBulkModal()" class="px-5 py-2.5 bg-blue-50 text-blue-600 rounded-xl text-[9px] font-black uppercase tracking-widest flex items-center gap-2 shadow-sm hover:bg-blue-100 transition-all active:scale-95 italic border border-blue-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16.862 4.487l1.688-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"/></svg>
                    Bulk Edit
                </button>

                <button onclick="saveBldgEditChanges()" class="px-5 py-2.5 bg-slate-900 text-white rounded-xl text-[9px] font-black uppercase tracking-widest flex items-center gap-2 shadow-lg hover:bg-blue-600 transition-all active:scale-95 italic">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Save Changes
                </button>
            </div>
        </div>

        {{-- ── Asset Source Table ── --}}
        <div id="editPanelAssetSource" class="flex-grow flex flex-col min-h-0">
            <div id="editSourceScroll" class="xls-scroll-wrap custom-scroll overflow-x-auto overflow-y-auto transition-all duration-300" style="max-height: calc(100vh - 450px);">
                <table class="w-full border-collapse" style="min-width:2400px;">
                    <thead class="sticky top-0 bg-slate-50 z-20 shadow-sm">
                        <tr>
                            <th class="xls-th w-10 text-center sticky left-0 z-30">#</th>
                            <th class="xls-th" style="min-width:140px">Classification</th>
                            <th class="xls-th" style="min-width:140px">Category</th>
                            <th class="xls-th" style="min-width:140px">Item</th>
                            <th class="xls-th text-blue-600" style="min-width:180px">Description</th>
                            <th class="xls-th text-blue-600" style="min-width:120px">Unit</th>
                            <th class="xls-th text-blue-600" style="min-width:160px">Acquisition Source</th>
                            <th class="xls-th text-blue-600" style="min-width:150px">Mode</th>
                            <th class="xls-th text-blue-600" style="min-width:160px">Source Personnel</th>
                            <th class="xls-th text-blue-600" style="min-width:160px">Personnel Position</th>
                            <th class="xls-th text-blue-600 text-right" style="min-width:120px">Cost / Unit (₱)</th>
                            <th class="xls-th text-blue-600 text-right" style="min-width:80px">Qty</th>
                            <th class="xls-th text-blue-600 text-right" style="min-width:110px">Useful Life (yrs)</th>
                            <th class="xls-th text-blue-600" style="min-width:140px">Acceptance Date</th>
                            <th class="xls-th text-blue-600" style="min-width:200px">Remarks</th>
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
                            <th class="xls-th" style="min-width:90px">Region</th>
                            <th class="xls-th" style="min-width:200px">Division</th>
                            <th class="xls-th text-blue-600" style="min-width:140px">Office/School Type</th>
                            <th class="xls-th text-blue-600" style="min-width:100px">School ID</th>
                            <th class="xls-th text-blue-600" style="min-width:210px">Office/School Name</th>
                            <th class="xls-th text-blue-600" style="min-width:160px">Nature of Occupancy</th>
                            <th class="xls-th text-blue-600" style="min-width:160px">Location</th>
                            <th class="xls-th text-blue-600" style="min-width:150px">Property No.</th>
                            <th class="xls-th text-blue-600 text-right" style="min-width:130px">Acquisition Cost (₱)</th>
                            <th class="xls-th text-blue-600" style="min-width:140px">Acquisition Date</th>
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
<div id="editBulkModal" class="fixed inset-0 z-[100] flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeBldgEditBulkModal()"></div>
    <div class="bg-white dark:bg-[#141f33] border border-slate-200 dark:border-slate-800 rounded-[2rem] shadow-2xl w-[90vw] max-w-5xl max-h-[90vh] flex flex-col relative z-10 transform scale-95 transition-transform duration-300">
        
        {{-- Header --}}
        <div class="px-8 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-2xl font-black text-slate-800 dark:text-white uppercase tracking-tight italic text-blue-600">Bulk Edit Rows</h3>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Update specific columns for a range of rows</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 bg-slate-50 dark:bg-[#0a101d] px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800">
                    <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">From Row #</label>
                    <input type="number" id="editBulkFrom" value="1" min="1" class="w-16 bg-transparent text-center font-black text-slate-800 dark:text-white outline-none">
                </div>
                <div class="flex items-center gap-2 bg-slate-50 dark:bg-[#0a101d] px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800">
                    <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">To Row #</label>
                    <input type="number" id="editBulkTo" value="1" min="1" class="w-20 bg-transparent text-center font-black text-slate-800 dark:text-white outline-none">
                </div>
                <button onclick="closeBldgEditBulkModal()" class="px-5 py-3 rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">Cancel</button>
                <button onclick="applyBldgEditBulk()" class="px-6 py-3 rounded-xl text-sm font-black text-white bg-blue-600 hover:bg-blue-700 shadow-lg shadow-blue-500/30 transition-all">Apply Bulk Edit</button>
            </div>
        </div>

        {{-- Body --}}
        <div class="p-8 overflow-y-auto custom-scroll flex-1 space-y-10">
            
            {{-- Source Section --}}
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-6 h-6 bg-blue-500/20 text-blue-600 rounded-lg flex items-center justify-center text-[10px] font-black shrink-0">1</div>
                    <h4 class="font-black text-slate-800 dark:text-slate-200 uppercase tracking-widest text-xs">Asset Data Entry (Source)</h4>
                </div>
                <div class="grid grid-cols-2 gap-x-6 gap-y-5">
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-blue-600">Classification</label><input type="text" id="ebClassification" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-blue-600">Category</label><input type="text" id="ebCategory" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-blue-600">Item</label><input type="text" id="ebItem" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-blue-600">Description</label><input type="text" id="ebDescription" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-blue-600">Unit of Measurement</label><input type="text" id="ebUom" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-blue-600">Acquisition Source</label><input type="text" id="ebAcqSource" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-blue-600">Mode of Procurement</label><input type="text" id="ebMode" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-blue-600">Source Personnel</label><input type="text" id="ebPersonnel" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-blue-600">Personnel Position</label><input type="text" id="ebPosition" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-blue-600">Cost per Unit</label><input type="number" id="ebCost" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl text-right" placeholder="Leave empty to ignore" min="0" step="0.01"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-blue-600">Quantity</label><input type="number" id="ebQty" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl text-right" placeholder="Leave empty to ignore" min="0" step="1"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-blue-600">Expected Useful Life</label><input type="number" id="ebLife" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl text-right" placeholder="Leave empty to ignore" min="0" step="1"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-blue-600">Acceptance Date</label><input type="date" id="ebDate1" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl"></div>
                    <div class="relative">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-blue-600">Remarks</label>
                        <select id="ebRemarks" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl bg-transparent">
                            <option value="">-- Ignore --</option>
                            <option value="Good Condition">Good Condition</option>
                            <option value="Needs Repair">Needs Repair</option>
                            <option value="Not Useable">Not Useable</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100 dark:border-slate-800"></div>

            {{-- Target Section --}}
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-6 h-6 bg-blue-500/20 text-blue-600 rounded-lg flex items-center justify-center text-[10px] font-black shrink-0">2</div>
                    <h4 class="font-black text-slate-800 dark:text-slate-200 uppercase tracking-widest text-xs">Asset Distribution (Target)</h4>
                </div>
                <div class="grid grid-cols-2 gap-x-6 gap-y-5">
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Region</label>
                        <div class="w-full px-4 py-[11px] font-semibold text-[11.5px] bg-slate-100/50 dark:bg-white/5 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-500 dark:text-slate-400 flex justify-between items-center cursor-not-allowed opacity-60">Region IX</div>
                    </div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Division</label>
                        <div class="w-full px-4 py-[11px] font-semibold text-[11.5px] bg-slate-100/50 dark:bg-white/5 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-500 dark:text-slate-400 flex justify-between items-center cursor-not-allowed opacity-60">Division of Zamboanga City</div>
                    </div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-blue-600">Office/School Type</label><input type="text" id="ebSchoolType" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-blue-600">School ID</label><input type="text" id="ebSchoolId" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore" inputmode="numeric"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-blue-600">Office/School Name</label><input type="text" id="ebSchoolName" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-blue-600">Nature of Occupancy</label><input type="text" id="ebOccupancy" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-blue-600">Location</label><input type="text" id="ebLocation" autocomplete="off" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-blue-600">Property Number</label><input type="text" id="ebPropertyNo" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl" placeholder="Leave empty to ignore"></div>
                    <div class="relative">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1">Acquisition Cost</label>
                        <div class="relative">
                            <input type="text" id="ebCostTotal" class="w-full px-4 py-[11px] font-semibold text-[11.5px] bg-slate-100/50 dark:bg-white/5 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-500 dark:text-slate-400 cursor-not-allowed outline-none text-right pr-10 opacity-60" placeholder="Auto calculated" readonly tabindex="-1">
                        </div>
                    </div>
                    <div class="relative"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1 text-blue-600">Acquisition Date</label><input type="date" id="ebDate2" class="xls-input !border border-slate-200 dark:border-slate-800 rounded-xl"></div>
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

    function toggleBldgEditFilters() {
        const section = document.getElementById('bldgEditFilterSection');
        const btn = document.getElementById('toggleBldgEditFilterBtn');
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

    function clearBldgEditFilters() {
        ['editFilterClass', 'editFilterCat', 'editFilterItem', 'editFilterSort', 'editFilterSchool', 'editFilterSource', 'editFilterMode', 'editFilterDate', 'editFilterIntegrity'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        bldgEditFetchData();
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

    function bldgEditFetchData() {
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
                    return `<td class="xls-td p-0 relative"><input type="text" class="xls-input edit-readonly w-full h-full" value="${safeVal}" readonly tabindex="-1">${badgeHtml}</td>`;
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
                ${renderCell('school_type', row.school_type, false)}
                ${renderCell('school_id', row.school_id, false)}
                ${renderCell('office_school_name', row.office_school_name, false)}
                ${renderCell('nature_of_occupancy', row.nature_of_occupancy, false)}
                ${renderCell('location', row.location, false)}
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

    function openBldgEditBulkModal() {
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
    
    function closeBldgEditBulkModal() {
        const m = document.getElementById('editBulkModal');
        m.classList.add('opacity-0');
        m.querySelector('.transform').classList.add('scale-95');
        setTimeout(() => m.classList.add('hidden'), 300);
    }

    function applyBldgEditBulk() {
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

        closeBldgEditBulkModal();
    }

    function bldgEditUndo() {
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

    function bldgEditRedo() {
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
        const uBtn = document.getElementById('bldgEditUndoBtn');
        const rBtn = document.getElementById('bldgEditRedoBtn');
        if (uBtn) uBtn.className = editUndoStack.length > 0 ? 'px-4 py-2 text-blue-600 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-white transition-all active:scale-95 flex items-center gap-2' : 'px-4 py-2 text-slate-400 rounded-xl text-[9px] font-black uppercase tracking-widest opacity-50 cursor-not-allowed flex items-center gap-2';
        if (rBtn) rBtn.className = editRedoStack.length > 0 ? 'px-4 py-2 text-blue-600 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-white transition-all active:scale-95 flex items-center gap-2' : 'px-4 py-2 text-slate-400 rounded-xl text-[9px] font-black uppercase tracking-widest opacity-50 cursor-not-allowed flex items-center gap-2';
    }

    function saveBldgEditChanges() {
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
                    'classification', 'category', 'article', 'description', 'unit_of_measurement', 
                    'acq_source', 'asset_cost', 'quantity', 'estimated_useful_life', 'property_number', 
                    'location', 'nature_of_occupancy', 'mode_of_acquisition', 'source_personnel', 
                    'personnel_position', 'acceptance_date', 'remarks', 'school_type', 'school_id', 
                    'office_school_name', 'acquisition_date'
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
                    if (changes.hasOwnProperty('nature_of_occupancy')) payload.occupancy = changes.nature_of_occupancy;
                    if (changes.hasOwnProperty('mode_of_acquisition')) payload.mode = changes.mode_of_acquisition;
                    if (changes.hasOwnProperty('source_personnel')) payload.personnel = changes.source_personnel;
                    if (changes.hasOwnProperty('personnel_position')) payload.position = changes.personnel_position;
                    if (changes.hasOwnProperty('acceptance_date')) payload.acceptance_date = changes.acceptance_date;
                    if (changes.hasOwnProperty('remarks')) payload.remarks = changes.remarks;
                    if (changes.hasOwnProperty('school_type')) payload.school_type = changes.school_type;
                    if (changes.hasOwnProperty('school_id')) payload.school_id = changes.school_id;
                    if (changes.hasOwnProperty('office_school_name')) payload.office_school_name = changes.office_school_name;
                    if (changes.hasOwnProperty('acquisition_date')) payload.acquisition_date = changes.acquisition_date;

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
                                bldgEditFetchData();
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
