{{-- ========================================== --}}
{{-- BULK ADD MODAL                            --}}
{{-- ========================================== --}}
<div id="bulkAddModal" class="fixed inset-0 z-50 flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeBulkAddModal()"></div>
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
                    <input type="number" id="bulkRowCount" value="1" min="1" max="100" class="w-16 bg-transparent text-center font-black text-slate-800 outline-none">
                </div>
                <button onclick="closeBulkAddModal()" class="px-5 py-3 rounded-xl text-sm font-bold text-slate-900 hover:bg-slate-100 transition-all">Cancel</button>
                <button onclick="confirmBulkAdd()" class="px-6 py-3 rounded-xl text-sm font-black text-white bg-[#c00000] hover:bg-red-700 shadow-lg shadow-red-500/30 transition-all">Confirm Bulk Add</button>
            </div>
        </div>

        {{-- Body --}}
        <div class="p-8 overflow-y-auto custom-scroll flex-1 space-y-10 bg-white">

            {{-- Source Section --}}
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-6 h-6 bg-amber-500/10 text-amber-600 rounded-lg flex items-center justify-center text-[10px] font-black shrink-0">1</div>
                    <h4 class="font-black text-slate-800 uppercase tracking-widest text-xs">Asset Data Entry (Source)</h4>
                </div>
                <div class="grid grid-cols-2 gap-x-6 gap-y-5">
                    <div class="relative col-identity p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Classification</label><input type="text" id="bClassification" data-col="classification" autocomplete="off" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Combo-box: type/select"></div>
                    <div class="relative col-identity p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Category</label><input type="text" id="bCategory" data-col="category" autocomplete="off" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Combo-box: type/select"></div>
                    <div class="relative col-identity p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Item</label><input type="text" id="bItem" data-col="item" autocomplete="off" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Combo-box: type/select"></div>
                    <div class="relative col-context p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Description</label><input type="text" id="bDescription" data-col="description" autocomplete="off" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Combo-box: type/select"></div>
                    <div class="relative col-context p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Unit of Measurement</label><input type="text" id="bUom" data-col="uom" autocomplete="off" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="e.g. Unit, Set, Pcs"></div>
                    <div class="relative col-status p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Mode of Procurement</label><input type="text" id="bMode" data-col="mode" autocomplete="off" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="e.g. Public Bidding"></div>
                    <div class="relative col-personnel p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Source Personnel</label><input type="text" id="bPersonnel" data-col="personnel" autocomplete="off" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Combo-box: type/select"></div>
                    <div class="relative col-personnel p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Personnel Position</label><input type="text" id="bPosition" data-col="position" autocomplete="off" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Combo-box: type/select"></div>
                    <div class="relative col-financial p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Cost per Unit</label><input type="number" id="bCost" oninput="calcBulkCost()" class="xls-input !border border-slate-100 rounded-xl text-right bg-transparent" placeholder="1" min="0" step="0.01"></div>
                    <div class="relative col-financial p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Quantity</label><input type="number" id="bQty1" oninput="calcBulkCost()" class="xls-input !border border-slate-100 rounded-xl text-right bg-transparent" placeholder="1" min="0" step="1"></div>
                    <div class="relative col-temporal p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Expected Useful Life</label><input type="number" id="bLife" class="xls-input !border border-slate-100 rounded-xl text-right bg-transparent" placeholder="1" min="0" step="1"></div>
                    <div class="relative col-temporal p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Acceptance Date</label><input type="date" id="bDate1" class="xls-input !border border-slate-100 rounded-xl bg-transparent"></div>
                    <div class="relative col-status p-1 rounded-2xl">
                        <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Remarks</label>
                        <select id="bRemarks" class="xls-input !border border-slate-100 rounded-xl bg-transparent">
                            <option value="">-- Default (Good Condition) --</option>
                            <option value="Good Condition">Good Condition</option>
                            <option value="Needs Repair">Needs Repair</option>
                            <option value="Not Useable">Not Useable</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100"></div>

            {{-- Target Section --}}
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-6 h-6 bg-amber-500/10 text-amber-600 rounded-lg flex items-center justify-center text-[10px] font-black shrink-0">2</div>
                    <h4 class="font-black text-slate-800 uppercase tracking-widest text-xs">Asset Distribution (Target)</h4>
                </div>
                <div class="grid grid-cols-2 gap-x-6 gap-y-5">
                    <div class="relative col-context p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Region</label>
                        <div class="w-full px-4 py-[11px] font-semibold text-[11.5px] bg-white/50 border border-slate-100 rounded-xl text-slate-900 flex justify-between items-center cursor-not-allowed">Region IX <svg class="w-3.5 h-3.5 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></div>
                    </div>
                    <div class="relative col-context p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Division</label>
                        <div class="w-full px-4 py-[11px] font-semibold text-[11.5px] bg-white/50 border border-slate-100 rounded-xl text-slate-900 flex justify-between items-center cursor-not-allowed">Division of Zamboanga City <svg class="w-3.5 h-3.5 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></div>
                    </div>
                    <div class="relative col-context p-1 rounded-2xl">
                        <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Office/School Type</label>
                        <input type="text" list="dl-school-type" id="bSchoolType" data-col="school-type" autocomplete="off" 
                            oninput="const isSchool=this.value.toLowerCase().includes('school'); if(!isSchool){document.getElementById('bLocation').value='Zamboanga City';}else{const sName=document.getElementById('bSchoolName').value; if(sName && typeof cleanSchoolNameForLocation==='function'){document.getElementById('bLocation').value=cleanSchoolNameForLocation(sName);}}"
                            class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Combo-box: type/select">
                    </div>
                    <div class="relative col-identity p-1 rounded-2xl">
                        <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">School ID</label>
                        <input type="text" id="bSchoolId" data-col="school-id" autocomplete="off" 
                            oninput="const s=allSchoolsList.find(x=>String(x.school_id)===this.value); if(s){document.getElementById('bSchoolName').value=s.name; const t=typeof detectItemSchoolType==='function'?detectItemSchoolType(s.name):''; document.getElementById('bSchoolType').value=t; const isSchool=t.toLowerCase().includes('school'); document.getElementById('bLocation').value=isSchool?cleanSchoolNameForLocation(s.name):'Zamboanga City';}"
                            class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Combo-box: type/select" inputmode="numeric">
                    </div>
                    <div class="relative col-identity p-1 rounded-2xl">
                        <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Office/School Name</label>
                        <input type="text" id="bSchoolName" data-col="school-name" autocomplete="off" 
                            oninput="const s=allSchoolsList.find(x=>x.name.toLowerCase()===this.value.toLowerCase()); if(s){ const t = detectItemSchoolType(s.name); document.getElementById('bSchoolType').value=t; document.getElementById('bSchoolId').value=s.school_id; const isSchool=t.toLowerCase().includes('school'); document.getElementById('bLocation').value=isSchool?cleanSchoolNameForLocation(s.name):'Zamboanga City';} else if(this.value.trim()){ const t = detectItemSchoolType(this.value); if(t) document.getElementById('bSchoolType').value=t; document.getElementById('bSchoolId').value=''; const isSchool=t.toLowerCase().includes('school'); document.getElementById('bLocation').value=isSchool?cleanSchoolNameForLocation(this.value):'Zamboanga City';}"
                            class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Combo-box: type/select">
                    </div>
                    <div class="relative col-personnel p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">First Name</label><input type="text" id="bCustodianFirst" data-col="custodian-first" oninput="syncBulkCustodian()" autocomplete="off" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="First Name"></div>
                    <div class="relative col-personnel p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Middle Name</label><input type="text" id="bCustodianMiddle" data-col="custodian-middle" autocomplete="off" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Middle Name"></div>
                    <div class="relative col-personnel p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Last Name</label><input type="text" id="bCustodianLast" data-col="custodian-last" oninput="syncBulkCustodian()" autocomplete="off" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Last Name"></div>
                    <div class="relative col-personnel p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Custodian Position</label><input type="text" id="bCustodianPos" autocomplete="off" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Custodian Position"></div>
                    <div class="relative col-personnel p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Custodian Contact No.</label><input type="text" id="bCustodianContact" autocomplete="off" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Contact No."></div>
                    <div class="relative col-context p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Nature of Occupancy</label><input type="text" id="bOccupancy" data-col="occupancy" autocomplete="off" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Combo-box: type/select"></div>
                    <div class="relative col-context p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Location</label><input type="text" id="bLocation" data-col="location" autocomplete="off" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Combo-box: type/select"></div>
                    <div class="relative col-identity p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Property Number</label><input type="text" id="bPropertyNo" oninput="checkBulkPropertyNumber()" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Combo-box: type/select"></div>
                    <div class="relative col-financial p-1 rounded-2xl">
                        <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Acquisition Cost</label>
                        <div class="relative">
                            <input type="number" id="bCost2" class="w-full px-4 py-[11px] font-semibold text-[11.5px] bg-white/30 border border-slate-100 rounded-xl text-slate-900 cursor-not-allowed outline-none text-right pr-10" placeholder="0.00" min="0" step="0.01" readonly tabindex="-1">
                            <svg class="w-3.5 h-3.5 opacity-50 absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                    </div>
                    <div class="relative col-temporal p-1 rounded-2xl"><label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Acquisition Date</label><input type="date" id="bDate2" class="xls-input !border border-slate-100 rounded-xl bg-transparent"></div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ========================================== --}}
{{-- BULK DELETE MODAL                          --}}
{{-- ========================================== --}}
<div id="bulkDeleteModal" class="fixed inset-0 z-[60] flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeBulkDeleteModal()"></div>
    <div class="bg-white border border-slate-200 rounded-[2rem] shadow-2xl w-full max-w-md relative z-10 transform scale-95 transition-transform duration-300">
        <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-xl font-black text-slate-800 uppercase tracking-tight italic">Bulk Delete</h3>
                <p class="text-[10px] font-bold text-red-500 uppercase tracking-widest mt-1">Warning: Permanent Action</p>
            </div>
            <div class="flex p-1 bg-slate-100 rounded-xl">
                <button onclick="setDeleteMode('rows')" id="btnDelRows" class="px-4 py-1.5 text-[9px] font-black uppercase tracking-widest rounded-lg bg-white shadow-sm text-slate-800 transition-all">Rows</button>
                <button onclick="setDeleteMode('pages')" id="btnDelPages" class="px-4 py-1.5 text-[9px] font-black uppercase tracking-widest rounded-lg text-slate-900 transition-all">Pages</button>
            </div>
        </div>
        <div class="p-8 space-y-6 bg-white">
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label id="lblDelFrom" class="text-[10px] font-black text-slate-900 uppercase tracking-widest ml-1">From Row</label>
                    <input type="number" id="deleteFromRow" min="1" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl font-black text-slate-800 outline-none focus:ring-2 focus:ring-red-100 transition-all text-center" placeholder="1">
                </div>
                <div class="space-y-2">
                    <label id="lblDelTo" class="text-[10px] font-black text-slate-900 uppercase tracking-widest ml-1">To Row</label>
                    <input type="number" id="deleteToRow" min="1" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl font-black text-slate-800 outline-none focus:ring-2 focus:ring-red-100 transition-all text-center" placeholder="10">
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button onclick="closeBulkDeleteModal()" class="flex-1 py-4 rounded-2xl font-black text-sm border-2 border-slate-200 text-slate-900 hover:border-slate-300 hover:bg-slate-50 transition-all">Cancel</button>
                <button onclick="confirmBulkDelete()" class="flex-1 py-4 rounded-2xl font-black text-sm bg-red-600 hover:bg-red-700 text-white shadow-lg shadow-red-100 transition-all">Delete Range</button>
            </div>

        </div>
    </div>
</div>
