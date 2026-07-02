{{-- ═══════ STEP: ADD NEW RECORD — Registration Form ═══════ --}}
<div id="stepAddNew" class="step-content">

    {{-- DATALISTS (shared option pools - reduced as per search-only requirements) --}}
    <datalist id="dl-condition">
        <option value="Good Condition">
        <option value="Needs Repair">
        <option value="Unserviceable">
    </datalist>
    <datalist id="dl-uom">
        <option value="Unit">
        <option value="Set">
        <option value="Piece">
        <option value="Box">
        <option value="Lot">
    </datalist>

    {{-- ── Section: Asset Data Table ── --}}
    <div id="assetTableCard" class="bg-white rounded-[2rem] border border-slate-100 shadow-lg overflow-hidden">

        {{-- Toolbar --}}
        <div id="assetToolbar" class="px-5 py-3 border-b border-slate-100 flex items-center justify-between gap-3 flex-wrap">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 bg-slate-800 rounded-xl flex items-center justify-center text-white text-xs font-black shrink-0">1</div>
                <span id="assetTabLabel" class="text-[10px] font-bold text-slate-900 uppercase tracking-widest italic">Asset Details</span>
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
            <table id="assetSourceTable" class="w-full border-collapse" style="min-width:1890px;">
                <thead>
                    <tr>
                        <th class="xls-th xls-sticky-col w-10 text-center sticky left-0" style="z-index:6">#</th>
                        <th class="xls-th col-identity" style="min-width:147px">Classification</th>
                        <th class="xls-th col-identity" style="min-width:147px">Category</th>
                        <th class="xls-th col-identity" style="min-width:147px">Item</th>
                        <th class="xls-th col-context" style="min-width:189px">Description</th>
                        <th class="xls-th col-context" style="min-width:126px">Unit</th>
                        <th class="xls-th col-status" style="min-width:158px">Mode of Acquisition</th>
                        <th class="xls-th col-identity" style="min-width:189px">Acquisition Source</th>
                        <th class="xls-th col-personnel" style="min-width:168px">Source Personnel</th>
                        <th class="xls-th col-personnel" style="min-width:168px">Personnel Position</th>
                        <th class="xls-th col-financial text-right" style="min-width:115px">Cost/Unit (₱)</th>
                        <th class="xls-th col-financial text-right" style="min-width:75px">Quantity</th>
                        <th class="xls-th col-temporal text-right" style="min-width:100px">Warranty (Mos)</th>
                        <th class="xls-th col-temporal text-right" style="min-width:100px">Useful Life(Yrs)</th>
                        <th class="xls-th col-status" style="min-width:185px">Condition</th>
                        <th class="xls-th col-temporal" style="min-width:165px">Date of Registration</th>
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
