{{-- ═══════ STEP: ADD NEW RECORD — Registration Form ═══════ --}}
<div id="stepAddNew" class="step-content">

    {{-- DATALISTS (shared option pools) --}}
    <datalist id="dl-acq-source">
        <option value="DepEd Central Office">
        <option value="Local Government Unit">
        <option value="Private Donation">
        <option value="Congressional Allocation">
        <option value="MOOE Fund">
    </datalist>
    <datalist id="dl-classification">
        <option value="Semi-Expendable">
        <option value="Non-Expendable">
        <option value="Expendable">
    </datalist>
    <datalist id="dl-category">@foreach($categories->unique('name') as $c)<option value="{{ $c->name }}">@endforeach</datalist>
    <datalist id="dl-item">@foreach($items->unique('name') as $i)<option value="{{ $i->name }}">@endforeach</datalist>
    <datalist id="dl-description"><option value="General"><option value="Specific"></datalist>
    <datalist id="dl-mode">
        <option value="Deped Central Office">
        <option value="Public Bidding">
        <option value="Direct Contracting">
        <option value="Shopping">
        <option value="Negotiated Procurement">
    </datalist>
    <datalist id="dl-personnel"></datalist>
    <datalist id="dl-position"><option value="Supply Officer"><option value="Principal"><option value="Teacher"><option value="Clerk"></datalist>
    <datalist id="dl-school-type">
        <option value="Elementary School">
        <option value="High School">
        <option value="Integrated School">
        <option value="Division Office">
        <option value="Government Building">
    </datalist>
    <datalist id="dl-school-id">@foreach($allSchools->unique('school_id') as $s)<option value="{{ $s->school_id }}">@endforeach</datalist>
    <datalist id="dl-school-name">@foreach($allSchools->unique('name') as $s)<option value="{{ $s->name }}">@endforeach</datalist>
    <datalist id="dl-custodian">@foreach($allCustodians->unique(fn($c) => $c->first_name . $c->last_name) as $c)<option value="{{ $c->first_name }} {{ $c->last_name }}">@endforeach</datalist>
    <datalist id="dl-custodian-pos">@foreach($allCustodians->unique('position') as $c)@if($c->position)<option value="{{ $c->position }}">@endif@endforeach</datalist>
    <datalist id="dl-custodian-contact">@foreach($allCustodians->unique('contact_number') as $c)@if($c->contact_number)<option value="{{ $c->contact_number }}">@endif@endforeach</datalist>
    <datalist id="dl-occupancy"><option value="Owned"><option value="Leased"><option value="Borrowed"></datalist>
    <datalist id="dl-location">@foreach($allSchools->unique('name') as $s)<option value="{{ $s->name }}">@endforeach</datalist>

    {{-- ── Section 1: Acquisition Source ── --}}
    <div id="acqSourceCard" class="mb-6 bg-white rounded-[2rem] border border-slate-100 shadow-lg overflow-hidden">
        <div class="px-6 py-3 border-b border-slate-100 flex items-center gap-3">
            <div class="w-6 h-6 bg-[#c00000] rounded-lg flex items-center justify-center text-white text-[10px] font-black shrink-0">1</div>
            <div>
                <h3 class="font-black text-slate-800 uppercase tracking-tight text-xs">Acquisition Source</h3>
                <p class="text-[9px] text-slate-900 font-bold uppercase tracking-widest">Identify who provided the assets</p>
            </div>
        </div>
        <div class="px-6 py-4">
            <div class="max-w-sm">
                <label class="text-[9px] font-black text-[#c00000] uppercase tracking-widest mb-1.5 block">Source of Acquisition <span class="text-red-400">*</span></label>
                <div class="relative">
                    <input type="text" id="acqSourceInput" data-col="acq-source" autocomplete="off"
                        placeholder="Type or select a source..."
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-800 outline-none focus:ring-2 focus:ring-red-100 focus:border-[#c00000] transition-all">
                </div>
            </div>
        </div>
    </div>

    {{-- ── Section 2: Asset Source / Distribution Table ── --}}
    <div id="assetTableCard" class="bg-white rounded-[2rem] border border-slate-100 shadow-lg overflow-hidden">

        {{-- Toolbar --}}
        <div id="assetToolbar" class="px-5 py-3 border-b border-slate-100 flex items-center justify-between gap-3 flex-wrap">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 bg-slate-800 rounded-xl flex items-center justify-center text-white text-xs font-black shrink-0">2</div>
                <div class="flex bg-slate-100 rounded-xl p-1 gap-1">
                    <button id="tabAssetSource" onclick="switchAssetTab('source')"
                        class="px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg bg-[#c00000] text-white shadow-sm transition-all">
                        Asset Source
                    </button>
                    <button id="tabAssetDist" onclick="switchAssetTab('distribution')"
                        class="px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg text-slate-900 hover:text-slate-900 transition-all">
                        Asset Distribution
                    </button>
                </div>
                <span id="assetTabLabel" class="hidden md:block text-[10px] font-bold text-slate-900 uppercase tracking-widest italic">Asset Source</span>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="openBulkAddModal()"
                    class="flex items-center gap-2 px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-wider hover:bg-slate-100 transition-all active:scale-95">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                    Bulk Add
                </button>
                <button onclick="openBulkDeleteModal()"
                    class="flex items-center gap-2 px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-wider hover:bg-red-50 hover:text-red-600 hover:border-red-100 transition-all active:scale-95">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                    Bulk Delete
                </button>
                <button onclick="addAssetRow()"
                    class="flex items-center gap-2 px-4 py-2.5 bg-[#c00000] text-white rounded-xl text-[10px] font-black uppercase tracking-wider hover:bg-red-700 transition-all shadow-sm active:scale-95">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Add Row
                </button>
            </div>
        </div>

        {{-- ── Asset Source Table ── --}}
        <div id="panelAssetSource">
            <div class="xls-scroll-wrap">
            <table class="w-full border-collapse" style="min-width:1500px;">
                <thead>
                    <tr>
                        <th class="xls-th w-10 text-center sticky left-0" style="z-index:6">#</th>
                        <th class="xls-th col-identity" style="min-width:140px">Classification</th>
                        <th class="xls-th col-identity" style="min-width:140px">Category</th>
                        <th class="xls-th col-identity" style="min-width:140px">Item</th>
                        <th class="xls-th col-context" style="min-width:180px">Description</th>
                        <th class="xls-th col-context" style="min-width:120px">Unit</th>
                        <th class="xls-th col-status" style="min-width:150px">Mode of Acquisition</th>
                        <th class="xls-th col-personnel" style="min-width:160px">Source Personnel</th>
                        <th class="xls-th col-personnel" style="min-width:160px">Personnel Position</th>
                        <th class="xls-th col-financial text-right" style="min-width:120px">Cost/Unit (₱)</th>
                        <th class="xls-th col-financial text-right" style="min-width:80px">Quantity</th>
                        <th class="xls-th col-temporal text-right" style="min-width:110px">Useful Life(Yrs)</th>
                        <th class="xls-th col-temporal" style="min-width:140px">Acceptance Date</th>
                        <th class="xls-th col-status" style="min-width:160px">Condition</th>
                        <th class="xls-th w-10 text-center">Del</th>
                    </tr>
                </thead>
                <tbody id="assetSourceBody"></tbody>
            </table>
            {{-- Empty state inside scroll wrap --}}
            <div id="assetSourceEmpty" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="inline-flex flex-col items-center gap-3 opacity-30">
                    <svg class="w-8 h-8 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0112 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125h17.25"/></svg>
                    <p class="text-[10px] font-black text-slate-900 uppercase tracking-[0.25em]">No rows — click Add Row to begin</p>
                </div>
            </div>
            </div>{{-- /xls-scroll-wrap --}}
        </div>

        {{-- ── Asset Distribution Table ── --}}
        <div id="panelAssetDist" class="hidden">
            <div class="xls-scroll-wrap">
            <table class="w-full border-collapse" style="min-width:1400px;">
                <thead>
                    <tr>
                        <th class="xls-th w-10 text-center sticky left-0 z-10">#</th>
                        <th class="xls-th col-context" style="min-width:120px">Region</th>
                        <th class="xls-th col-context" style="min-width:180px">Division</th>
                        <th class="xls-th col-context" style="min-width:160px">Office/School Type</th>
                        <th class="xls-th col-identity" style="min-width:100px">School ID</th>
                        <th class="xls-th col-identity" style="min-width:210px">Office/School Name</th>
                        <th class="xls-th col-personnel" style="min-width:160px">Custodian First Name</th>
                        <th class="xls-th col-personnel" style="min-width:160px">Custodian Middle Name</th>
                        <th class="xls-th col-personnel" style="min-width:160px">Custodian Last Name</th>
                        <th class="xls-th col-personnel" style="min-width:160px">Custodian Position</th>
                        <th class="xls-th col-personnel" style="min-width:160px">Custodian Contact No.</th>
                        <th class="xls-th col-context" style="min-width:160px">Nature of Occupancy</th>
                        <th class="xls-th col-context" style="min-width:160px">Location</th>
                        <th class="xls-th col-identity" style="min-width:150px">Property No.</th>
                        <th class="xls-th col-financial text-right" style="min-width:130px">Acquisition Cost (₱)</th>
                        <th class="xls-th col-temporal" style="min-width:140px">Acquisition Date</th>
                        <th class="xls-th w-10 text-center">Del</th>
                    </tr>
                </thead>
                <tbody id="assetDistBody"></tbody>
            </table>
            {{-- Empty state inside scroll wrap --}}
            <div id="assetDistEmpty" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="inline-flex flex-col items-center gap-3 opacity-30">
                    <svg class="w-8 h-8 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0112 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125h17.25"/></svg>
                    <p class="text-[10px] font-black text-slate-900 uppercase tracking-[0.25em]">No rows — click Add Row to begin</p>
                </div>
            </div>
            </div>{{-- /xls-scroll-wrap --}}
        </div>

        {{-- Footer --}}
        <div id="assetTableFooter" class="px-5 py-3 border-t border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center gap-6">
                <p id="rowCountLabel" class="text-[9px] font-black text-slate-900 uppercase tracking-widest">0 Rows</p>
                <div id="paginationControls" class="flex items-center gap-2 border-l border-slate-200 dark:border-slate-800 pl-6">
                    <button onclick="prevPage()" id="prevBtn" class="pg-btn text-slate-900 dark:text-slate-100">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
                        Prev
                    </button>
                    <div class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 dark:bg-[#141f33] rounded-lg">
                        <span id="currentPageDisplay" class="text-[10px] font-black text-slate-800 dark:text-white">1</span>
                        <span class="text-[10px] font-bold text-slate-900 dark:text-slate-400">/</span>
                        <span id="totalPagesDisplay" class="text-[10px] font-black text-slate-900 dark:text-slate-400">1</span>
                    </div>
                    <button onclick="nextPage()" id="nextBtn" class="pg-btn text-slate-900 dark:text-slate-100">
                        Next
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
            <button onclick="submitRegistration()"
                class="px-6 py-2.5 bg-[#c00000] text-white rounded-xl font-black text-[10px] uppercase tracking-wider hover:bg-red-700 transition-all shadow-sm active:scale-95">
                Register Records
            </button>
        </div>
    </div>
</div> <!-- end stepAddNew -->
