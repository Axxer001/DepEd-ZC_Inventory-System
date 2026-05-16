{{-- ═══════ STEP: ADD BUILDING ═══════ --}}
<div id="stepAddBuilding" class="step-content">

    {{-- Datalists --}}
    <datalist id="dl-bldg-region"><option value="REGION IX"></datalist>
    <datalist id="dl-bldg-division"><option value="Division of Zamboanga City"></datalist>
    <datalist id="dl-bldg-type">
        <option value="Elementary School">
        <option value="High School">
        <option value="Integrated School">
        <option value="Division Office">
        <option value="Government Building">
    </datalist>
    <datalist id="dl-bldg-class">
        <option value="Semi-Expendable">
        <option value="Non-Expendable">
        <option value="Expendable">
        <option value="Land Improvement">
    </datalist>
    <datalist id="dl-bldg-occupancy">
        <option value="Owned">
        <option value="Leased">
        <option value="Borrowed">
    </datalist>
    <datalist id="dl-bldg-school-id">
        @foreach($allSchools as $s)<option value="{{ $s->school_id }}">@endforeach
    </datalist>
    <datalist id="dl-bldg-school-name">
        @foreach($allSchools as $s)<option value="{{ $s->name }}">@endforeach
    </datalist>

    {{-- Toolbar --}}
    <div id="bldgToolbar" class="bg-white rounded-[2rem] border border-slate-100 shadow-lg overflow-hidden mb-4">
        <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between gap-3 flex-wrap">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 bg-[#c00000] rounded-xl flex items-center justify-center text-white text-xs font-black shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-black text-slate-800 uppercase tracking-tight">Add Building</p>
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Register school buildings and infrastructure</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="bldgBulkAdd()" class="flex items-center gap-2 px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-wider hover:bg-slate-100 transition-all active:scale-95">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                    Bulk Add
                </button>
                <button onclick="bldgBulkDelete()" class="flex items-center gap-2 px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-wider hover:bg-red-50 hover:text-red-600 hover:border-red-100 transition-all active:scale-95">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                    Bulk Delete
                </button>
                <button onclick="addBldgRow()" class="flex items-center gap-2 px-4 py-2.5 bg-[#c00000] text-white rounded-xl text-[10px] font-black uppercase tracking-wider hover:bg-red-700 transition-all shadow-sm active:scale-95">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Add Row
                </button>
            </div>
        </div>

        {{-- XLS Table --}}
        <div class="xls-scroll-wrap">
            <table class="w-full border-collapse" style="min-width:2600px;">
                <thead>
                    <tr>
                        <th class="xls-th w-10 text-center sticky left-0 z-20">#</th>
                        <th class="xls-th col-context" style="min-width:90px">Region</th>
                        <th class="xls-th col-context" style="min-width:180px">Division</th>
                        <th class="xls-th col-context" style="min-width:150px">Office/School Type</th>
                        <th class="xls-th col-identity" style="min-width:110px">School ID</th>
                        <th class="xls-th col-identity" style="min-width:200px">Office/School Name *</th>
                        <th class="xls-th col-context" style="min-width:180px">Address</th>
                        <th class="xls-th col-personnel text-right" style="min-width:75px">Storeys</th>
                        <th class="xls-th col-personnel text-right" style="min-width:90px">Classrooms</th>
                        <th class="xls-th col-personnel" style="min-width:140px">Article</th>
                        <th class="xls-th col-personnel" style="min-width:160px">Description</th>
                        <th class="xls-th col-identity" style="min-width:130px">Classification</th>
                        <th class="xls-th col-context" style="min-width:120px">Occupancy</th>
                        <th class="xls-th col-context" style="min-width:150px">Location</th>
                        <th class="xls-th col-temporal" style="min-width:130px">Date Constructed</th>
                        <th class="xls-th col-temporal" style="min-width:130px">Acquisition Date</th>
                        <th class="xls-th col-identity" style="min-width:140px">Property No.</th>
                        <th class="xls-th col-financial text-right" style="min-width:130px">Acquisition Cost (₱)</th>
                        <th class="xls-th col-temporal text-right" style="min-width:115px">Useful Life (yrs)</th>
                        <th class="xls-th col-financial text-right" style="min-width:130px">Appraised Value (₱)</th>
                        <th class="xls-th col-temporal" style="min-width:130px">Appraisal Date</th>
                        <th class="xls-th col-status" style="min-width:160px">Remarks</th>
                        <th class="xls-th w-10 text-center">Del</th>
                    </tr>
                </thead>
                <tbody id="bldgEntryBody"></tbody>
            </table>

            <div id="bldgEntryEmpty" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="inline-flex flex-col items-center gap-3 opacity-30">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21"/></svg>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.25em]">No rows — click Add Row to begin</p>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-5 py-3 border-t border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center gap-6">
                <p id="bldgEntryRowCount" class="text-[9px] font-black text-slate-400 uppercase tracking-widest">0 Rows</p>
                <div id="bldgEntryPagination" class="hidden flex items-center gap-2 border-l border-slate-200 pl-6">
                    <button onclick="bldgEntryPrev()" id="bldgPrevBtn" class="pg-btn text-slate-500">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg> Prev
                    </button>
                    <div class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 rounded-lg">
                        <span id="bldgEntryCurPage" class="text-[10px] font-black text-slate-800">1</span>
                        <span class="text-[10px] font-bold text-slate-400">/</span>
                        <span id="bldgEntryTotalPages" class="text-[10px] font-black text-slate-400">1</span>
                    </div>
                    <button onclick="bldgEntryNext()" id="bldgNextBtn" class="pg-btn text-slate-500">
                        Next <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
            <button onclick="submitBuildingRegistration()" class="px-6 py-2.5 bg-[#c00000] text-white rounded-xl font-black text-[10px] uppercase tracking-wider hover:bg-red-700 transition-all shadow-sm active:scale-95">
                Register Buildings
            </button>
        </div>
    </div>
</div>

{{-- ========================================== --}}
{{-- BLDG BULK ADD MODAL                        --}}
{{-- ========================================== --}}
<div id="bldgBulkAddModal" class="fixed inset-0 z-50 flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeBldgBulkAddModal()"></div>
    <div class="bg-white border border-slate-200 rounded-[2rem] shadow-2xl w-[90vw] max-w-5xl max-h-[90vh] flex flex-col relative z-10 transform scale-95 transition-transform duration-300">
        
        {{-- Header --}}
        <div class="px-8 py-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-2xl font-black text-slate-800 uppercase tracking-tight italic">Bulk Add Rows</h3>
                <p class="text-xs font-bold text-slate-900 uppercase tracking-widest mt-1">Pre-fill data across multiple new rows</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 bg-slate-50 px-4 py-2 rounded-xl border border-slate-200">
                    <label class="text-[10px] font-black text-slate-900 uppercase tracking-widest">Rows to add</label>
                    <input type="number" id="bldgBulkCount" value="1" min="1" max="500" class="w-16 bg-transparent text-center font-black text-slate-800 outline-none">
                </div>
                <button onclick="closeBldgBulkAddModal()" class="px-5 py-3 rounded-xl text-sm font-bold text-slate-900 hover:bg-slate-100 transition-all">Cancel</button>
                <button onclick="confirmBldgBulkAdd()" class="px-6 py-3 rounded-xl text-sm font-black text-white bg-[#c00000] hover:bg-red-700 shadow-lg shadow-red-500/30 transition-all">Confirm Bulk Add</button>
            </div>
        </div>

        {{-- Body --}}
        <div class="p-8 overflow-y-auto custom-scroll flex-1 space-y-10 bg-white">
            
            {{-- Building Identity --}}
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-6 h-6 bg-amber-500/10 text-amber-600 rounded-lg flex items-center justify-center text-[10px] font-black shrink-0">1</div>
                    <h4 class="font-black text-slate-800 uppercase tracking-widest text-xs">Building Identity</h4>
                </div>
                <div class="grid grid-cols-2 gap-x-6 gap-y-5">
                    <div class="relative col-context p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Region</label>
                        <div class="w-full px-4 py-[11px] font-semibold text-[11.5px] bg-white/50 border border-slate-100 rounded-xl text-slate-900 flex justify-between items-center cursor-not-allowed">Region IX</div>
                    </div>
                    <div class="relative col-context p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Division</label>
                        <div class="w-full px-4 py-[11px] font-semibold text-[11.5px] bg-white/50 border border-slate-100 rounded-xl text-slate-900 flex justify-between items-center cursor-not-allowed">Division of Zamboanga City</div>
                    </div>
                    <div class="relative col-context p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Office/School Type</label><input type="text" id="bldgBulkType" autocomplete="off" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Combo-box: type/select" list="dl-bldg-type"></div>
                    <div class="relative col-identity p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">School ID</label><input type="text" id="bldgBulkSchoolId" autocomplete="off" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Combo-box: type/select" list="dl-bldg-school-id"></div>
                    <div class="relative col-identity p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Office/School Name</label><input type="text" id="bldgBulkSchoolName" autocomplete="off" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Combo-box: type/select" list="dl-bldg-school-name"></div>
                    <div class="relative col-context p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Address</label><input type="text" id="bldgBulkAddress" autocomplete="off" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Leave empty to ignore"></div>
                    <div class="relative col-personnel p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Storeys</label><input type="number" id="bldgBulkStoreys" class="xls-input !border border-slate-100 rounded-xl bg-transparent text-right" placeholder="0" min="0" step="1"></div>
                    <div class="relative col-personnel p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Classrooms</label><input type="number" id="bldgBulkClassrooms" class="xls-input !border border-slate-100 rounded-xl bg-transparent text-right" placeholder="0" min="0" step="1"></div>
                </div>
            </div>

            <div class="border-t border-slate-100"></div>

            {{-- Building Details --}}
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-6 h-6 bg-amber-500/10 text-amber-600 rounded-lg flex items-center justify-center text-[10px] font-black shrink-0">2</div>
                    <h4 class="font-black text-slate-800 uppercase tracking-widest text-xs">Building Details</h4>
                </div>
                <div class="grid grid-cols-2 gap-x-6 gap-y-5">
                    <div class="relative col-personnel p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Article</label><input type="text" id="bldgBulkArticle" autocomplete="off" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Leave empty to ignore"></div>
                    <div class="relative col-personnel p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Description</label><input type="text" id="bldgBulkDescription" autocomplete="off" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Leave empty to ignore"></div>
                    <div class="relative col-identity p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Classification</label><input type="text" id="bldgBulkClass" autocomplete="off" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Combo-box: type/select" list="dl-bldg-class"></div>
                    <div class="relative col-context p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Nature of Occupancy</label><input type="text" id="bldgBulkOccupancy" autocomplete="off" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Combo-box: type/select" list="dl-bldg-occupancy"></div>
                    <div class="relative col-context p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Location</label><input type="text" id="bldgBulkLocation" autocomplete="off" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Combo-box: type/select" list="dl-bldg-school-name"></div>
                    <div class="relative col-identity p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Property Number</label><input type="text" id="bldgBulkPropertyNo" autocomplete="off" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Leave empty to ignore"></div>
                    <div class="relative col-temporal p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Date Constructed</label><input type="date" id="bldgBulkDateConstructed" class="xls-input !border border-slate-100 rounded-xl bg-transparent"></div>
                    <div class="relative col-temporal p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Acquisition Date</label><input type="date" id="bldgBulkAcqDate" class="xls-input !border border-slate-100 rounded-xl bg-transparent"></div>
                    <div class="relative col-financial p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Acquisition Cost (₱)</label><input type="number" id="bldgBulkAcqCost" class="xls-input !border border-slate-100 rounded-xl bg-transparent text-right" placeholder="0.00" min="0" step="0.01"></div>
                    <div class="relative col-temporal p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Expected Useful Life</label><input type="number" id="bldgBulkLife" class="xls-input !border border-slate-100 rounded-xl bg-transparent text-right" placeholder="25" min="0" step="1"></div>
                    <div class="relative col-financial p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Appraised Value (₱)</label><input type="number" id="bldgBulkAppraisedVal" class="xls-input !border border-slate-100 rounded-xl bg-transparent text-right" placeholder="0.00" min="0" step="0.01"></div>
                    <div class="relative col-temporal p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Appraisal Date</label><input type="date" id="bldgBulkAppraisalDate" class="xls-input !border border-slate-100 rounded-xl bg-transparent"></div>
                    <div class="relative col-status p-1 rounded-2xl col-span-2">
                        <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Remarks</label>
                        <select id="bldgBulkRemarks" class="xls-input !border border-slate-100 rounded-xl bg-transparent">
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

{{-- ========================================== --}}
{{-- BLDG BULK DELETE MODAL                     --}}
{{-- ========================================== --}}
<div id="bldgBulkDeleteModal" class="fixed inset-0 z-[60] flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeBldgBulkDeleteModal()"></div>
    <div class="bg-white border border-slate-200 rounded-[2rem] shadow-2xl w-full max-w-md relative z-10 transform scale-95 transition-transform duration-300">
        <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-xl font-black text-slate-800 uppercase tracking-tight italic">Bulk Delete</h3>
                <p class="text-[10px] font-bold text-red-500 uppercase tracking-widest mt-1">Warning: Permanent Action</p>
            </div>
            <div class="flex p-1 bg-slate-100 rounded-xl">
                <button onclick="setBldgDeleteMode('rows')" id="btnBldgDelRows" class="px-4 py-1.5 text-[9px] font-black uppercase tracking-widest rounded-lg bg-white shadow-sm text-slate-800 transition-all">Rows</button>
                <button onclick="setBldgDeleteMode('pages')" id="btnBldgDelPages" class="px-4 py-1.5 text-[9px] font-black uppercase tracking-widest rounded-lg text-slate-900 transition-all">Pages</button>
            </div>
        </div>
        <div class="p-8 space-y-6 bg-white">
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label id="lblBldgDelFrom" class="text-[10px] font-black text-slate-900 uppercase tracking-widest ml-1">From Row</label>
                    <input type="number" id="bldgDeleteFromRow" min="1" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl font-black text-slate-800 outline-none focus:ring-2 focus:ring-red-100 transition-all text-center" placeholder="1">
                </div>
                <div class="space-y-2">
                    <label id="lblBldgDelTo" class="text-[10px] font-black text-slate-900 uppercase tracking-widest ml-1">To Row</label>
                    <input type="number" id="bldgDeleteToRow" min="1" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl font-black text-slate-800 outline-none focus:ring-2 focus:ring-red-100 transition-all text-center" placeholder="10">
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button onclick="closeBldgBulkDeleteModal()" class="flex-1 py-4 rounded-2xl font-black text-sm border-2 border-slate-200 text-slate-900 hover:border-slate-300 hover:bg-slate-50 transition-all">Cancel</button>
                <button onclick="confirmBldgBulkDelete()" class="flex-1 py-4 rounded-2xl font-black text-sm bg-red-600 hover:bg-red-700 text-white shadow-lg shadow-red-100 transition-all">Delete Range</button>
            </div>
        </div>
    </div>
</div>

<script>
// ── Add Building State ──
let bldgEntryRows = [];
let bldgEntryPage = 1;
const BLDG_RPP = 50;
let _bldgRowNum = 0;

function addBldgRow(defaults = {}) {
    _bldgRowNum++;
    const id = _bldgRowNum;
    bldgEntryRows.push({
        _id: id,
        region: defaults.region ?? 'REGION IX',
        division: defaults.division ?? 'Division of Zamboanga City',
        office_type: defaults.office_type ?? '',
        school_identifier: defaults.school_identifier ?? '',
        office_name: defaults.office_name ?? '',
        address: defaults.address ?? '',
        storeys: defaults.storeys ?? '',
        classrooms: defaults.classrooms ?? '',
        article: defaults.article ?? '',
        description: defaults.description ?? '',
        classification: defaults.classification ?? '',
        occupancy_nature: defaults.occupancy_nature ?? '',
        location: defaults.location ?? '',
        date_constructed: defaults.date_constructed ?? '',
        acquisition_date: defaults.acquisition_date ?? '',
        property_number: defaults.property_number ?? '',
        acquisition_cost: defaults.acquisition_cost ?? '',
        estimated_useful_life: defaults.estimated_useful_life ?? '25',
        appraised_value: defaults.appraised_value ?? '',
        appraisal_date: defaults.appraisal_date ?? '',
        remarks: defaults.remarks ?? '',
    });
    bldgEntryPage = Math.ceil(bldgEntryRows.length / BLDG_RPP);
    renderBldgEntryTable();
}

function deleteBldgRow(id) {
    bldgEntryRows = bldgEntryRows.filter(r => r._id !== id);
    const totalPages = Math.max(1, Math.ceil(bldgEntryRows.length / BLDG_RPP));
    if (bldgEntryPage > totalPages) bldgEntryPage = totalPages;
    renderBldgEntryTable();
}

function syncBldgRow(id, col, val) {
    const row = bldgEntryRows.find(r => r._id === id);
    if (row) {
        row[col] = val;

        // --- Auto-fill logic for School ID <-> School Name <-> Location ---
        if (col === 'school_identifier') {
            const school = (typeof allSchoolsList !== 'undefined') ? allSchoolsList.find(s => String(s.school_id) === String(val)) : null;
            if (school) {
                row['office_name'] = school.name;
                if (typeof cleanSchoolNameForLocation === 'function') row['location'] = cleanSchoolNameForLocation(school.name);
                
                // Update UI if on current page
                const nameInp = document.querySelector(`#bldg-row-${id} input[data-bldg-col="office_name"]`);
                const locInp = document.querySelector(`#bldg-row-${id} input[data-bldg-col="location"]`);
                if (nameInp) nameInp.value = row['office_name'];
                if (locInp) locInp.value = row['location'];
            }
        } else if (col === 'office_name') {
            const school = (typeof allSchoolsList !== 'undefined') ? allSchoolsList.find(s => s.name.toLowerCase() === val.toLowerCase()) : null;
            if (school) {
                row['school_identifier'] = school.school_id;
                if (typeof cleanSchoolNameForLocation === 'function') row['location'] = cleanSchoolNameForLocation(school.name);
                
                // Update UI if on current page
                const idInp = document.querySelector(`#bldg-row-${id} input[data-bldg-col="school_identifier"]`);
                const locInp = document.querySelector(`#bldg-row-${id} input[data-bldg-col="location"]`);
                if (idInp) idInp.value = row['school_identifier'];
                if (locInp) locInp.value = row['location'];
            } else if (val.trim() !== "") {
                if (typeof cleanSchoolNameForLocation === 'function') row['location'] = cleanSchoolNameForLocation(val);
                const locInp = document.querySelector(`#bldg-row-${id} input[data-bldg-col="location"]`);
                if (locInp) locInp.value = row['location'];
            }
        }
    }
}

function renderBldgEntryTable() {
    const tbody = document.getElementById('bldgEntryBody');
    const empty = document.getElementById('bldgEntryEmpty');
    tbody.innerHTML = '';

    if (bldgEntryRows.length === 0) {
        empty.classList.remove('hidden');
        document.getElementById('bldgEntryRowCount').textContent = '0 Rows';
        document.getElementById('bldgEntryPagination').classList.add('hidden');
        return;
    }
    empty.classList.add('hidden');

    const start = (bldgEntryPage - 1) * BLDG_RPP;
    const page  = bldgEntryRows.slice(start, start + BLDG_RPP);

    page.forEach((row, idx) => {
        const displayNum = start + idx + 1;
        const tr = document.createElement('tr');
        tr.className = 'xls-row group border-b border-slate-800';
        tr.id = `bldg-row-${row._id}`;

        const inp = (col, list = '', ph = '', cls = '') =>
            `<td class="xls-td ${cls}"><input class="xls-input" data-bldg-col="${col}" data-bldg-id="${row._id}"
                value="${escBldg(row[col])}" placeholder="${ph}"
                ${list ? `list="${list}"` : ''} oninput="syncBldgRow(${row._id},'${col}',this.value)"></td>`;

        const numInp = (col, ph = '0', cls = '') =>
            `<td class="xls-td ${cls}"><input type="number" class="xls-input text-right" data-bldg-col="${col}" data-bldg-id="${row._id}"
                value="${escBldg(row[col])}" placeholder="${ph}"
                oninput="syncBldgRow(${row._id},'${col}',this.value)"></td>`;

        const dateInp = (col, cls = '') =>
            `<td class="xls-td ${cls}"><input type="date" class="xls-input" data-bldg-col="${col}" data-bldg-id="${row._id}"
                value="${escBldg(row[col])}"
                oninput="syncBldgRow(${row._id},'${col}',this.value)"></td>`;

        tr.innerHTML = `
            <td class="xls-td text-center sticky left-0 w-10 bg-[#0f172a] z-10"><span class="text-[10px] font-black text-slate-500">${displayNum}</span></td>
            ${inp('region','dl-bldg-region','REGION IX','col-context')}
            ${inp('division','dl-bldg-division','Division of ZC','col-context')}
            ${inp('office_type','dl-bldg-type','School Type','col-context')}
            ${inp('school_identifier','dl-bldg-school-id','School ID','col-identity')}
            ${inp('office_name','dl-bldg-school-name','Office/School Name *','col-identity')}
            ${inp('address','','Address','col-context')}
            ${numInp('storeys','0','col-personnel')}
            ${numInp('classrooms','0','col-personnel')}
            ${inp('article','','Article','col-personnel')}
            ${inp('description','','Description','col-personnel')}
            ${inp('classification','dl-bldg-class','Classification','col-identity')}
            ${inp('occupancy_nature','dl-bldg-occupancy','Occupancy','col-context')}
            ${inp('location','dl-bldg-school-name','Location','col-context')}
            ${dateInp('date_constructed','col-temporal')}
            ${dateInp('acquisition_date','col-temporal')}
            ${inp('property_number','','Property No.','col-identity')}
            <td class="xls-td col-financial"><input type="number" step="0.01" class="xls-input text-right" data-bldg-col="acquisition_cost" data-bldg-id="${row._id}"
                value="${escBldg(row['acquisition_cost'])}" placeholder="0.00"
                oninput="syncBldgRow(${row._id},'acquisition_cost',this.value)"></td>
            ${numInp('estimated_useful_life','25','col-temporal')}
            <td class="xls-td col-financial"><input type="number" step="0.01" class="xls-input text-right" data-bldg-col="appraised_value" data-bldg-id="${row._id}"
                value="${escBldg(row['appraised_value'])}" placeholder="0.00"
                oninput="syncBldgRow(${row._id},'appraised_value',this.value)"></td>
            ${dateInp('appraisal_date','col-temporal')}
            ${inp('remarks','','Remarks','col-status')}
            <td class="xls-td text-center">
                <button onclick="deleteBldgRow(${row._id})" class="w-7 h-7 flex items-center justify-center text-slate-500 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all mx-auto">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </td>`;
        tbody.appendChild(tr);
    });

    // Pagination
    const totalPages = Math.max(1, Math.ceil(bldgEntryRows.length / BLDG_RPP));
    document.getElementById('bldgEntryRowCount').textContent = `${bldgEntryRows.length} Rows`;
    document.getElementById('bldgEntryCurPage').textContent  = bldgEntryPage;
    document.getElementById('bldgEntryTotalPages').textContent = totalPages;
    document.getElementById('bldgPrevBtn').disabled = bldgEntryPage === 1;
    document.getElementById('bldgNextBtn').disabled = bldgEntryPage === totalPages;
    const pag = document.getElementById('bldgEntryPagination');
    bldgEntryRows.length > BLDG_RPP ? pag.classList.remove('hidden') : pag.classList.add('hidden');
}

function escBldg(v) { return String(v ?? '').replace(/"/g, '&quot;'); }
function bldgEntryPrev() { if (bldgEntryPage > 1) { bldgEntryPage--; renderBldgEntryTable(); } }
function bldgEntryNext() { const t = Math.ceil(bldgEntryRows.length / BLDG_RPP); if (bldgEntryPage < t) { bldgEntryPage++; renderBldgEntryTable(); } }

// Bulk Add modal logic
function bldgBulkAdd() {
    const m = document.getElementById('bldgBulkAddModal');
    m.classList.remove('hidden');
    // small delay for transition
    setTimeout(() => {
        m.classList.remove('opacity-0');
        m.children[1].classList.remove('scale-95');
        m.children[1].classList.add('scale-100');
    }, 10);
    document.getElementById('bldgBulkCount').value = 1;
}

function closeBldgBulkAddModal() {
    const m = document.getElementById('bldgBulkAddModal');
    m.classList.add('opacity-0');
    m.children[1].classList.remove('scale-100');
    m.children[1].classList.add('scale-95');
    setTimeout(() => m.classList.add('hidden'), 300);
}

function confirmBldgBulkAdd() {
    const n = parseInt(document.getElementById('bldgBulkCount').value);
    if (!n || n < 1) {
        Swal.fire({title:'Error', text:'Please enter a valid number of rows', icon:'error', customClass:{popup:'rounded-[2rem]'}});
        return;
    }
    
    const defaults = {
        office_type: document.getElementById('bldgBulkType').value,
        school_identifier: document.getElementById('bldgBulkSchoolId').value,
        office_name: document.getElementById('bldgBulkSchoolName').value,
        address: document.getElementById('bldgBulkAddress').value,
        storeys: document.getElementById('bldgBulkStoreys').value,
        classrooms: document.getElementById('bldgBulkClassrooms').value,
        article: document.getElementById('bldgBulkArticle').value,
        description: document.getElementById('bldgBulkDescription').value,
        classification: document.getElementById('bldgBulkClass').value,
        occupancy_nature: document.getElementById('bldgBulkOccupancy').value,
        location: document.getElementById('bldgBulkLocation').value,
        property_number: document.getElementById('bldgBulkPropertyNo').value,
        date_constructed: document.getElementById('bldgBulkDateConstructed').value,
        acquisition_date: document.getElementById('bldgBulkAcqDate').value,
        acquisition_cost: document.getElementById('bldgBulkAcqCost').value,
        estimated_useful_life: document.getElementById('bldgBulkLife').value || '25',
        appraised_value: document.getElementById('bldgBulkAppraisedVal').value,
        appraisal_date: document.getElementById('bldgBulkAppraisalDate').value,
        remarks: document.getElementById('bldgBulkRemarks').value,
    };
    
    for (let i = 0; i < n; i++) {
        addBldgRow(defaults);
    }
    closeBldgBulkAddModal();
}

// Bulk Delete modal logic
let bldgDeleteMode = 'rows';
function setBldgDeleteMode(mode) {
    bldgDeleteMode = mode;
    const btnRows = document.getElementById('btnBldgDelRows');
    const btnPages = document.getElementById('btnBldgDelPages');
    const lblFrom = document.getElementById('lblBldgDelFrom');
    const lblTo = document.getElementById('lblBldgDelTo');
    const inpFrom = document.getElementById('bldgDeleteFromRow');
    const inpTo = document.getElementById('bldgDeleteToRow');

    if(mode === 'rows') {
        btnRows.className = 'px-4 py-1.5 text-[9px] font-black uppercase tracking-widest rounded-lg bg-white shadow-sm text-slate-800 transition-all';
        btnPages.className = 'px-4 py-1.5 text-[9px] font-black uppercase tracking-widest rounded-lg text-slate-900 transition-all';
        lblFrom.textContent = 'From Row';
        lblTo.textContent = 'To Row';
        inpFrom.placeholder = '1';
        inpTo.placeholder = '10';
        inpFrom.max = bldgEntryRows.length;
        inpTo.max = bldgEntryRows.length;
    } else {
        btnPages.className = 'px-4 py-1.5 text-[9px] font-black uppercase tracking-widest rounded-lg bg-white shadow-sm text-slate-800 transition-all';
        btnRows.className = 'px-4 py-1.5 text-[9px] font-black uppercase tracking-widest rounded-lg text-slate-900 transition-all';
        lblFrom.textContent = 'From Page';
        lblTo.textContent = 'To Page';
        inpFrom.placeholder = '1';
        inpTo.placeholder = '1';
        const t = Math.ceil(bldgEntryRows.length / BLDG_RPP);
        inpFrom.max = t;
        inpTo.max = t;
    }
}

function bldgBulkDelete() {
    if (bldgEntryRows.length === 0) return;
    const m = document.getElementById('bldgBulkDeleteModal');
    m.classList.remove('hidden');
    setTimeout(() => {
        m.classList.remove('opacity-0');
        m.children[1].classList.remove('scale-95');
        m.children[1].classList.add('scale-100');
    }, 10);
    setBldgDeleteMode('rows');
    document.getElementById('bldgDeleteFromRow').value = 1;
    document.getElementById('bldgDeleteToRow').value = bldgEntryRows.length;
}

function closeBldgBulkDeleteModal() {
    const m = document.getElementById('bldgBulkDeleteModal');
    m.classList.add('opacity-0');
    m.children[1].classList.remove('scale-100');
    m.children[1].classList.add('scale-95');
    setTimeout(() => m.classList.add('hidden'), 300);
}

function confirmBldgBulkDelete() {
    const f = parseInt(document.getElementById('bldgDeleteFromRow').value);
    const t = parseInt(document.getElementById('bldgDeleteToRow').value);

    if(!f || !t || f > t || f < 1) {
        Swal.fire({title:'Invalid Range', text:'Please enter a valid from/to range.', icon:'error', customClass:{popup:'rounded-[2rem]'}});
        return;
    }

    let startIdx, endIdx;
    if(bldgDeleteMode === 'rows') {
        if(t > bldgEntryRows.length) {
            Swal.fire({title:'Invalid Range', text:`Max rows is ${bldgEntryRows.length}`, icon:'error', customClass:{popup:'rounded-[2rem]'}});
            return;
        }
        startIdx = f - 1;
        endIdx = t - 1;
    } else {
        const totalPages = Math.ceil(bldgEntryRows.length / BLDG_RPP);
        if(t > totalPages) {
            Swal.fire({title:'Invalid Range', text:`Max pages is ${totalPages}`, icon:'error', customClass:{popup:'rounded-[2rem]'}});
            return;
        }
        startIdx = (f - 1) * BLDG_RPP;
        endIdx = (t * BLDG_RPP) - 1;
    }

    // Keep elements not in range
    const toKeep = [];
    for(let i=0; i<bldgEntryRows.length; i++) {
        if(i < startIdx || i > endIdx) {
            toKeep.push(bldgEntryRows[i]);
        }
    }
    bldgEntryRows = toKeep;
    
    // reset pagination
    const newTotal = Math.max(1, Math.ceil(bldgEntryRows.length / BLDG_RPP));
    if (bldgEntryPage > newTotal) bldgEntryPage = newTotal;
    
    renderBldgEntryTable();
    closeBldgBulkDeleteModal();
    
    Swal.fire({
        toast: true, position: 'top-end', icon: 'success',
        title: 'Rows deleted', showConfirmButton: false, timer: 2000
    });
}

// Submit
async function submitBuildingRegistration() {
    const validRows = bldgEntryRows.filter(r => r.office_name.trim() !== '');
    if (validRows.length === 0) {
        Swal.fire({ title: 'No Data', text: 'Add at least one row with an Office/School Name.', icon: 'warning', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold' } });
        return;
    }

    const result = await Swal.fire({
        title: 'Register Buildings?',
        text: `Submit ${validRows.length} building record(s)?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Register',
        confirmButtonColor: '#c00000',
        customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' }
    });
    if (!result.isConfirmed) return;

    Swal.fire({ title: 'Registering...', allowOutsideClick: false, didOpen: () => Swal.showLoading(), customClass: { popup: 'rounded-[2rem]' } });

    try {
        const res = await fetch("{{ route('register.building.store') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ rows: validRows })
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire({ title: 'Success!', text: data.message, icon: 'success', confirmButtonColor: '#10b981', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold' } })
                .then(() => { bldgEntryRows = []; bldgEntryPage = 1; renderBldgEntryTable(); });
        } else {
            Swal.fire({ title: 'Error', text: data.message, icon: 'error', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold' } });
        }
    } catch(e) {
        Swal.fire({ title: 'Error', text: 'Registration failed. Please try again.', icon: 'error', confirmButtonColor: '#c00000', customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold' } });
    }
}

// Override the renderBldgTable hook called by nextStep
window.renderBldgTable = function() {
    renderBldgEntryTable();
};
</script>
