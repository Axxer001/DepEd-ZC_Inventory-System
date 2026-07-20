{{-- ------- STEP: ASSIGN ASSET ------- --}}
<div id="stepAssignAsset" class="step-content" x-data="assignApp()">
    <div class="flex-1 p-0 relative">
        <div class="bg-white rounded-[2rem] border border-slate-100 shadow-lg overflow-hidden flex flex-col relative" id="assetTableCard">
            
            {{-- Toolbar --}}
            <div id="assetToolbar" class="px-5 py-3 border-b border-slate-100 flex items-center justify-between gap-3 flex-wrap">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 bg-slate-800 rounded-xl flex items-center justify-center text-white text-xs font-black shrink-0">1</div>
                    <span class="text-[10px] font-bold text-slate-900 uppercase tracking-widest italic ml-2">Unassigned Assets Registry</span>
                </div>
                <div class="flex items-center gap-4">
                    <div class="relative flex items-center gap-2">
                        <div class="relative">
                            <svg class="w-4 h-4 absolute left-3 top-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" x-model="searchQuery" placeholder="Search Assets..." class="bg-slate-100 border border-transparent pl-10 pr-4 py-2 rounded-xl text-xs font-bold focus:outline-none focus:bg-white focus:border-slate-300 transition-all w-64">
                        </div>
                        
                        <!-- Advanced Filter Window -->
                        <div class="relative">
                            <button type="button" @click="showAdvancedFilters = !showAdvancedFilters" @click.away="showAdvancedFilters = false"
                                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-[10px] font-black uppercase tracking-widest rounded-xl transition-all flex items-center gap-2 shadow-sm active:scale-95"
                                :class="{ 'bg-[#c00000] text-white hover:bg-red-700 ring-2 ring-red-100': filterClassification || filterCategory || filterCondition }">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                                <span>Filter</span>
                            </button>
                            
                            <!-- Filter Window -->
                            <div x-show="showAdvancedFilters" style="display: none;" @click.stop
                                class="absolute right-0 top-full mt-2 w-72 bg-white border border-slate-200 shadow-2xl rounded-2xl p-5 z-[70] transform origin-top-right transition-all">
                                <h4 class="text-xs font-black text-slate-900 uppercase tracking-widest mb-4">Advanced Filters</h4>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wider mb-1.5">Classification</label>
                                        <select x-model="filterClassification" class="w-full text-xs font-bold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 outline-none focus:border-slate-400 focus:bg-white transition-all cursor-pointer">
                                            <option value="">All Classifications</option>
                                            <template x-for="cls in availableClassifications" :key="cls">
                                                <option :value="cls" x-text="cls"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wider mb-1.5">Category</label>
                                        <select x-model="filterCategory" class="w-full text-xs font-bold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 outline-none focus:border-slate-400 focus:bg-white transition-all cursor-pointer">
                                            <option value="">All Categories</option>
                                            <template x-for="cat in availableCategories" :key="cat">
                                                <option :value="cat" x-text="cat"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wider mb-1.5">Condition</label>
                                        <select x-model="filterCondition" class="w-full text-xs font-bold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 outline-none focus:border-slate-400 focus:bg-white transition-all cursor-pointer">
                                            <option value="">All Conditions</option>
                                            <template x-for="cond in availableConditions" :key="cond">
                                                <option :value="cond" x-text="cond"></option>
                                            </template>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-5 pt-4 border-t border-slate-100 flex justify-end">
                                    <button @click="filterClassification=''; filterCategory=''; filterCondition=''" 
                                            class="text-[10px] font-black text-slate-400 hover:text-[#c00000] uppercase tracking-widest transition-colors flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        Clear All
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <label class="flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 border border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-widest cursor-pointer transition-all select-none">
                        <input type="checkbox" x-model="hideAutofill" class="w-3.5 h-3.5 accent-[#c00000] rounded">
                        Hide Auto-Fill
                    </label>
                    @if(auth()->check() && auth()->user()->isAdmin() && auth()->user()->isMainSystem())
                    <button type="button" @click="openBulkHardDeleteModal()" class="flex items-center gap-2 px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-wider hover:bg-red-50 hover:text-red-600 hover:border-red-100 transition-all active:scale-95 cursor-pointer shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"></path></svg>
                        Bulk Hard-delete
                    </button>
                    @endif
                    <button type="button" @click="openBulkAssignModal"
                        class="px-6 py-2.5 bg-slate-800 hover:bg-slate-700 text-white text-[10px] font-black uppercase tracking-widest rounded-xl transition-all flex items-center gap-2 shadow-sm active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Bulk Assign</span>
                    </button>
                </div>
            </div>

            <!-- Loader -->
            <div x-show="loading" class="absolute inset-0 z-50 bg-white/80 backdrop-blur-sm flex flex-col items-center justify-center">
                <svg class="w-8 h-8 animate-spin text-[#c00000]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                <span class="mt-4 text-xs font-bold uppercase tracking-widest text-[#c00000]">Fetching AMU...</span>
            </div>

            <div class="xls-scroll-wrap" x-show="!loading">
                <table class="w-full border-collapse" style="min-width:2128px;">
                    <thead>
                        <tr>
                            <th class="xls-th w-10 text-center sticky left-0 z-20">#</th>
                            <th class="xls-th" style="min-width:158px">Classification</th>
                            <th class="xls-th" style="min-width:158px">Category</th>
                            <th class="xls-th" style="min-width:158px">Item Name</th>
                            <th class="xls-th" style="min-width:158px">Sub Item / Details</th>
                            <th class="xls-th text-right" style="min-width:158px">Asset Cost (₱)</th>
                            <th class="xls-th text-center" style="min-width:158px">Qty / UOM</th>
                            <th class="xls-th" style="min-width:158px">Condition</th>
                            <th class="xls-th col-temporal" style="min-width:158px">Property Number</th>
                            <th class="xls-th col-temporal" style="min-width:158px">Serial Number</th>
                            <th class="xls-th col-personnel" style="min-width:158px">Employee Search</th>
                            <th class="xls-th col-personnel" x-show="!hideAutofill" style="min-width:158px">Employee ID</th>
                            <th class="xls-th col-personnel" x-show="!hideAutofill" style="min-width:158px">Employee Name</th>
                            <th class="xls-th col-personnel" x-show="!hideAutofill" style="min-width:158px">Employee Position</th>
                            <th class="xls-th col-personnel" x-show="!hideAutofill" style="min-width:158px">Employee Status</th>
                            <th class="xls-th col-identity" style="min-width:158px">School/Office Search</th>
                            <th class="xls-th col-identity" x-show="!hideAutofill" style="min-width:158px">Office/School ID</th>
                            <th class="xls-th col-identity" x-show="!hideAutofill" style="min-width:158px">Office/School Type</th>
                            <th class="xls-th col-identity" x-show="!hideAutofill" style="min-width:158px">Office/School Name</th>
                            <th class="xls-th col-identity" x-show="!hideAutofill" style="min-width:158px">Location</th>
                            <th class="xls-th col-temporal" style="min-width:158px">Issuance Date</th>
                            @if(auth()->check() && auth()->user()->isAdmin() && auth()->user()->isMainSystem())
                            <th class="xls-th text-center" style="min-width:100px">Actions</th>
                            @endif
                            <th class="xls-th text-center" style="min-width:80px">
                                <span class="block mb-1">Select</span>
                                <input type="checkbox" @change="toggleSelectAll($event.target.checked)" class="w-3.5 h-3.5 text-[#c00000] focus:ring-[#c00000] border-slate-300 rounded cursor-pointer">
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(asset, index) in paginatedAssets" :key="asset.assignment_id">
                            <tr class="xls-row">
                                <td class="xls-td xls-sticky-col text-center font-bold text-slate-400 sticky left-0 z-10" x-text="((currentPage - 1) * perPage) + index + 1"></td>
                                                                <td class="xls-td"><input type="text" class="xls-input bg-transparent font-bold text-slate-500 cursor-default" readonly :value="asset.classification || '-'"></td>
                                <td class="xls-td"><input type="text" class="xls-input bg-transparent font-bold text-slate-500 cursor-default" readonly :value="asset.category || '-'"></td>
                                <td class="xls-td"><input type="text" class="xls-input bg-transparent font-bold cursor-default" readonly :value="asset.item_name || '-'"></td>
                                <td class="xls-td"><input type="text" class="xls-input bg-transparent cursor-default" readonly :value="asset.sub_item_name || 'General'"></td>
                                <td class="xls-td text-right"><input type="text" class="xls-input bg-transparent text-right font-semibold cursor-default" readonly :value="Number(asset.asset_cost || 0).toLocaleString()"></td>
                                <td class="xls-td text-center"><input type="text" class="xls-input bg-transparent text-center font-bold cursor-default" readonly :value="(asset.quantity || 0) + ' ' + (asset.uom || '')"></td>
                                <td class="xls-td text-center"><input type="text" class="xls-input bg-transparent text-center font-medium cursor-default" readonly :value="asset.condition || '-'"></td>
                                
                                <td class="xls-td col-temporal">
                                    <input type="text" x-model="asset.property_number" 
                                        :disabled="Number(asset.quantity || 0) > 1"
                                        :class="Number(asset.quantity || 0) > 1 ? 'xls-input bg-slate-50 cursor-not-allowed text-slate-400' : 'xls-input font-bold text-green-700 bg-green-50 placeholder-green-300'"
                                        placeholder="Property No.">
                                </td>
                                
                                <td class="xls-td col-temporal">
                                    <input type="text" x-model="asset.serial_number" 
                                        :disabled="Number(asset.quantity || 0) > 1"
                                        :class="Number(asset.quantity || 0) > 1 ? 'xls-input bg-slate-50 cursor-not-allowed text-slate-400' : 'xls-input font-bold text-green-700 bg-green-50 placeholder-green-300'"
                                        placeholder="Serial No.">
                                </td>
                                
                                <td class="xls-td col-personnel relative" style="overflow:visible;" @click.away="asset.showEmpDropdown = false">
                                    <input type="text" x-model="asset.empSearch" @input="searchEmployee(asset)" @focus="closeAllDropdowns(); asset.showEmpDropdown = true; searchEmployee(asset)" class="xls-input font-bold text-[#c00000]" placeholder="Search ID or Name...">
                                    <button type="button" @click="clearEmployee(asset)" x-show="asset.empSearch" class="absolute right-2 top-1/2 -translate-y-1/2 p-[2.3px] text-slate-400 hover:text-red-500 hover:bg-red-50 rounded transition-all cursor-pointer"><svg class="w-[13.2px] h-[13.2px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                                    <div x-show="asset.showEmpDropdown" class="xls-custom-dd" style="display: block; width: 100%;">
                                        <template x-for="emp in asset.empResults" :key="emp.id">
                                            <div class="xls-dd-item" @click="selectEmployee(asset, emp)">
                                                <span x-text="emp.first_name + ' ' + emp.last_name"></span><span style="color:#64748b;font-size:8px;margin-left:6px;" x-text="(emp.employee_id || '')"></span>
                                            </div>
                                        </template>
                                        <div x-show="asset.empResults.length === 0" class="xls-dd-empty">No employees found</div>
                                    </div>
                                </td>
                                
                                <td class="xls-td col-personnel" x-show="!hideAutofill"><input type="text" readonly x-model="asset.employee_id_str" class="xls-input bg-slate-50 cursor-not-allowed"></td>
                                <td class="xls-td col-personnel" x-show="!hideAutofill"><input type="text" readonly x-model="asset.employee_name" class="xls-input font-black text-slate-700 bg-slate-50 cursor-not-allowed" placeholder="Not Assigned"></td>
                                <td class="xls-td col-personnel" x-show="!hideAutofill"><input type="text" readonly x-model="asset.employee_pos" class="xls-input bg-slate-50 cursor-not-allowed"></td>
                                <td class="xls-td col-personnel" x-show="!hideAutofill"><input type="text" readonly x-model="asset.employee_status" class="xls-input bg-slate-50 cursor-not-allowed"></td>
 
                                <td class="xls-td col-identity relative" style="overflow:visible;" @click.away="asset.showLocDropdown = false">
                                    <input type="text" x-model="asset.locSearch" @input="searchLocation(asset)" @focus="closeAllDropdowns(); asset.showLocDropdown = true; searchLocation(asset)" class="xls-input font-bold text-[#c00000]" placeholder="Search School/Office...">
                                    <button type="button" @click="clearLocation(asset)" x-show="asset.locSearch" class="absolute right-2 top-1/2 -translate-y-1/2 -mt-1 p-[2.3px] text-slate-400 hover:text-red-500 hover:bg-red-50 rounded transition-all cursor-pointer"><svg class="w-[13.2px] h-[13.2px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                                    <div x-show="asset.showLocDropdown" class="xls-custom-dd" style="display: block; width: 100%;">
                                        <template x-for="loc in asset.locResults" :key="loc.id">
                                            <div class="xls-dd-item" @click="selectLocation(asset, loc)">
                                                <span x-text="loc.name || loc.id"></span><span style="color:#64748b;font-size:8px;margin-left:6px;" x-text="(loc.type || '') + ' - ' + (loc.location || '')"></span>
                                            </div>
                                        </template>
                                        <div x-show="asset.locResults.length === 0" class="xls-dd-empty">No locations found</div>
                                    </div>
                                </td>
                                
                                <td class="xls-td col-identity" x-show="!hideAutofill"><input type="text" readonly x-model="asset.school_id" class="xls-input bg-slate-50 cursor-not-allowed"></td>
                                <td class="xls-td col-identity" x-show="!hideAutofill"><input type="text" readonly x-model="asset.school_type" class="xls-input bg-slate-50 cursor-not-allowed"></td>
                                <td class="xls-td col-identity" x-show="!hideAutofill"><input type="text" readonly x-model="asset.school_name" class="xls-input bg-slate-50 cursor-not-allowed"></td>
                                <td class="xls-td col-identity" x-show="!hideAutofill"><input type="text" readonly x-model="asset.location" class="xls-input bg-slate-50 cursor-not-allowed"></td>
                                
                                <td class="xls-td col-temporal">
                                    <input type="date" x-model="asset.acquisition_date" class="xls-input uppercase font-bold text-slate-600">
                                </td>
                                @if(auth()->check() && auth()->user()->isAdmin() && auth()->user()->isMainSystem())
                                <td class="xls-td text-center">
                                    <button type="button" @click="confirmHardDeleteAssetInAssign(asset.assignment_id, asset.property_number || ('ID: ' + asset.assignment_id))" class="p-1.5 text-slate-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition-colors" title="Hard Delete Asset">
                                        <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </td>
                                @endif
                                <td class="xls-td text-center">
                                    <input type="checkbox" x-model="asset.selected" class="w-4 h-4 text-[#c00000] focus:ring-[#c00000] border-slate-300 rounded cursor-pointer">
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                
                <!-- Empty State inside scroll wrap -->
                <div x-show="!loading && filteredAssets.length === 0" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="inline-flex flex-col items-center gap-3 opacity-30">
                        <svg class="w-8 h-8 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0112 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125h17.25"/></svg>
                        <p class="text-[10px] font-black text-slate-900 uppercase tracking-[0.25em]" x-text="searchQuery ? 'No matching assets found' : 'No unassigned assets available'"></p>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div id="assetTableFooter" class="px-5 py-3 border-t border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-6">
                    <p class="text-[9px] font-black text-slate-900 uppercase tracking-widest"><span x-text="filteredAssets.length"></span> Rows</p>
                    <div class="flex items-center gap-2 border-l border-slate-200 pl-6">
                        <button type="button" @click="prevPage()" :disabled="currentPage === 1" class="pg-btn text-slate-900 disabled:opacity-40 disabled:cursor-not-allowed">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
                            Prev
                        </button>
                        <div class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 rounded-lg">
                            <span class="text-[10px] font-black text-slate-800" x-text="currentPage">1</span>
                            <span class="text-[10px] font-bold text-slate-900">/</span>
                            <span class="text-[10px] font-black text-slate-900" x-text="totalPages">1</span>
                        </div>
                        <button type="button" @click="nextPage()" :disabled="currentPage === totalPages" class="pg-btn text-slate-900 disabled:opacity-40 disabled:cursor-not-allowed">
                            Next
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>
                <button @click="submitBatch" :disabled="pendingCount === 0"
                    class="px-8 py-2.5 bg-[#c00000] disabled:bg-slate-300 disabled:cursor-not-allowed text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-red-700 transition-all flex items-center gap-3 shadow-sm active:scale-95">
                    <span>Deploy Assignments</span>
                    <div class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center" x-text="pendingCount">0</div>
                </button>
            </div>
        </div>
    </div>

    <!-- Bulk Assign Modal -->
    <div x-show="bulkAssignOpen" class="fixed inset-0 z-[60] flex items-center justify-center hidden" :class="{ 'hidden': !bulkAssignOpen }" style="display: none;">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="closeBulkAssignModal()"></div>
        <div class="bg-white border border-slate-200 rounded-[2rem] shadow-2xl w-full max-w-2xl relative z-10 transform transition-transform duration-300 p-8">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h3 class="text-xl font-black text-slate-800 uppercase tracking-tight italic">Bulk Assign Assets</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Fill fields to assign to all selected assets</p>
                </div>
                <button type="button" @click="closeBulkAssignModal()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-6">
                <!-- Bulk Select Range Helper -->
                <div class="relative p-4 bg-slate-50 border border-slate-100 rounded-2xl col-span-2 mb-2">
                    <label class="text-[9px] font-black text-slate-800 uppercase tracking-widest block mb-2">
                        Quick Row Selection Range
                    </label>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-bold text-slate-500">Select from #</span>
                        <input type="number" x-model="bulkData.selectFrom" class="w-20 px-3 py-2 text-xs font-black text-slate-700 border border-slate-200 rounded-xl focus:outline-none focus:border-slate-400 bg-white" placeholder="1">
                        <span class="text-xs font-bold text-slate-500">to #</span>
                        <input type="number" x-model="bulkData.selectTo" class="w-20 px-3 py-2 text-xs font-black text-slate-700 border border-slate-200 rounded-xl focus:outline-none focus:border-slate-400 bg-white" placeholder="50">
                        <button type="button" @click="selectRangeOnly()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-[10px] font-black uppercase tracking-wider transition-all active:scale-95">
                            Check Rows
                        </button>
                        <button type="button" @click="clearRangeSelection()" class="px-3 py-2 bg-slate-200 hover:bg-slate-300 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all active:scale-95">
                            Clear
                        </button>
                    </div>
                    <p class="text-[9px] font-bold text-slate-400 mt-1.5 uppercase">
                        Selects rows in the currently filtered table (Total: <span x-text="filteredAssets.length"></span> assets available)
                    </p>
                </div>


                <!-- Serial No -->
                <div class="relative p-1 rounded-2xl col-span-2">
                    <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Serial Number</label>
                    <input type="text" x-model="bulkData.serial_number" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Leave empty to keep current">
                </div>

                <!-- Employee Search -->
                <div class="relative p-1 rounded-2xl" style="position:relative;overflow:visible;" @click.away="bulkData.showEmpDropdown = false">
                    <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Employee Search</label>
                    <div class="relative w-full">
                        <input type="text" x-model="bulkData.empSearch" @input="searchBulkEmployee()" @focus="closeAllDropdowns(); bulkData.showEmpDropdown = true; searchBulkEmployee()" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Search ID or Name...">
                        <button type="button" x-show="bulkData.empSearch" @click="clearBulkEmployee()" class="absolute right-2 top-1/2 -translate-y-1/2 p-[2.3px] text-slate-400 hover:text-red-500 hover:bg-red-50 rounded transition-all cursor-pointer"><svg class="w-[13.2px] h-[13.2px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                    </div>
                    <div x-show="bulkData.showEmpDropdown" class="xls-custom-dd" style="display: block; width: 100%;">
                        <template x-for="emp in bulkData.empResults" :key="emp.id">
                            <div class="xls-dd-item" @mousedown="selectBulkEmployee(emp)">
                                <span x-text="emp.first_name + ' ' + emp.last_name"></span><span style="color:#64748b;font-size:8px;margin-left:6px;" x-text="(emp.employee_id || '')"></span>
                            </div>
                        </template>
                        <div x-show="bulkData.empResults.length === 0" class="xls-dd-empty">No employees found</div>
                    </div>
                </div>

                <!-- Employee Info (Read-only representation) -->
                <div class="relative p-1 rounded-2xl">
                    <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Employee Name</label>
                    <input type="text" readonly :value="bulkData.employee_name || 'Not Assigned'" class="xls-input bg-slate-50 text-slate-500 cursor-not-allowed">
                </div>

                <!-- School/Office Search -->
                <div class="relative p-1 rounded-2xl" style="position:relative;overflow:visible;" @click.away="bulkData.showLocDropdown = false">
                    <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">School/Office Search</label>
                    <div class="relative w-full">
                        <input type="text" x-model="bulkData.locSearch" @input="searchBulkLocation()" @focus="closeAllDropdowns(); bulkData.showLocDropdown = true; searchBulkLocation()" class="xls-input !border border-slate-100 rounded-xl bg-transparent" placeholder="Search School/Office...">
                        <button type="button" x-show="bulkData.locSearch" @click="clearBulkLocation()" class="absolute right-2 top-1/2 -translate-y-1/2 p-[2.3px] text-slate-400 hover:text-red-500 hover:bg-red-50 rounded transition-all cursor-pointer"><svg class="w-[13.2px] h-[13.2px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                    </div>
                    <div x-show="bulkData.showLocDropdown" class="xls-custom-dd" style="display: block; width: 100%;">
                        <template x-for="loc in bulkData.locResults" :key="loc.id">
                            <div class="xls-dd-item" @mousedown="selectBulkLocation(loc)">
                                <span x-text="loc.name || loc.id"></span><span style="color:#64748b;font-size:8px;margin-left:6px;" x-text="(loc.type || '') + ' - ' + (loc.location || '')"></span>
                            </div>
                        </template>
                        <div x-show="bulkData.locResults.length === 0" class="xls-dd-empty">No locations found</div>
                    </div>
                </div>

                <!-- School Info -->
                <div class="relative p-1 rounded-2xl">
                    <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Office/School Name</label>
                    <input type="text" readonly :value="bulkData.school_name || 'Not Assigned'" class="xls-input bg-slate-50 text-slate-500 cursor-not-allowed">
                </div>

                <!-- Issuance Date -->
                <div class="relative p-1 rounded-2xl col-span-2">
                    <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Issuance Date</label>
                    <input type="date" x-model="bulkData.acquisition_date" class="xls-input !border border-slate-100 rounded-xl bg-transparent">
                </div>
            </div>

            <div class="flex gap-3">
                <button type="button" @click="closeBulkAssignModal()" class="flex-1 py-4 rounded-2xl font-black text-sm border-2 border-slate-200 text-slate-900 hover:border-slate-300 hover:bg-slate-50 transition-all">Cancel</button>
                <button type="button" @click="applyBulkAssign()" class="flex-1 py-4 rounded-2xl font-black text-sm bg-[#c00000] hover:bg-red-700 text-white shadow-lg transition-all">Apply Bulk Assign</button>
            </div>
        </div>
    </div>

    <!-- Bulk Hard-delete Modal -->
    <div x-show="bulkHardDeleteOpen" class="fixed inset-0 z-[60] flex items-center justify-center hidden" :class="{ 'hidden': !bulkHardDeleteOpen }" style="display: none;">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="closeBulkHardDeleteModal()"></div>
        <div class="bg-white border border-slate-200 rounded-[2rem] shadow-2xl w-full max-w-md relative z-10 transform transition-transform duration-300">
            <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-black text-slate-800 uppercase tracking-tight italic">Bulk Hard-delete</h3>
                    <p class="text-[10px] font-bold text-red-500 uppercase tracking-widest mt-1">Warning: Permanent Action</p>
                </div>
                <div class="flex p-1 bg-slate-100 rounded-xl">
                    <button type="button" @click="setDeleteMode('rows')" :class="bulkDeleteMode === 'rows' ? 'bg-white shadow-sm text-slate-800' : 'text-slate-500'" class="px-4 py-1.5 text-[9px] font-black uppercase tracking-widest rounded-lg transition-all">Rows</button>
                    <button type="button" @click="setDeleteMode('pages')" :class="bulkDeleteMode === 'pages' ? 'bg-white shadow-sm text-slate-800' : 'text-slate-500'" class="px-4 py-1.5 text-[9px] font-black uppercase tracking-widest rounded-lg transition-all">Pages</button>
                </div>
            </div>
            <div class="p-8 space-y-6 bg-white">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label x-text="bulkDeleteMode === 'rows' ? 'From Row' : 'From Page'" class="text-[10px] font-black text-slate-900 uppercase tracking-widest ml-1"></label>
                        <input type="number" x-model="bulkDeleteFrom" min="1" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl font-black text-slate-800 outline-none focus:ring-2 focus:ring-red-100 transition-all text-center">
                    </div>
                    <div class="space-y-2">
                        <label x-text="bulkDeleteMode === 'rows' ? 'To Row' : 'To Page'" class="text-[10px] font-black text-slate-900 uppercase tracking-widest ml-1"></label>
                        <input type="number" x-model="bulkDeleteTo" min="1" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl font-black text-slate-800 outline-none focus:ring-2 focus:ring-red-100 transition-all text-center">
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" @click="closeBulkHardDeleteModal()" class="flex-1 py-4 rounded-2xl font-black text-sm border-2 border-slate-200 text-slate-900 hover:border-slate-300 hover:bg-slate-50 transition-all">Cancel</button>
                    <button type="button" @click="confirmBulkHardDeleteInAssign()" class="flex-1 py-4 rounded-2xl font-black text-sm bg-red-600 hover:bg-red-700 text-white shadow-lg shadow-red-100 transition-all">Delete Range</button>
                </div>
            </div>
        </div>
    </div>

    <!-- App Logic -->
    <script>
        function assignApp() {
            return {
                assets: [],
                searchQuery: '',
                loading: true,
                showAdvancedFilters: false,
                filterClassification: '',
                filterCategory: '',
                filterCondition: '',
                bulkAssignOpen: false,
                bulkHardDeleteOpen: false,
                bulkDeleteMode: 'rows',
                bulkDeleteFrom: 1,
                bulkDeleteTo: 10,
                currentPage: 1,
                perPage: 50,
                hideAutofill: false,
                bulkData: {
                    selectFrom: '',
                    selectTo: '',
                    property_number: '',
                    serial_number: '',
                    empSearch: '',
                    employee_id: null,
                    employee_id_str: '',
                    employee_name: '',
                    employee_pos: '',
                    employee_status: '',
                    showEmpDropdown: false,
                    empResults: [],
                    locSearch: '',
                    school_db_id: null,
                    is_office: false,
                    school_id: '',
                    school_type: '',
                    school_name: '',
                    location: '',
                    showLocDropdown: false,
                    locResults: [],
                    acquisition_date: ''
                },

                async init() {
                    this.$watch('searchQuery', () => this.currentPage = 1);
                    this.$watch('filterClassification', () => this.currentPage = 1);
                    this.$watch('filterCondition', () => this.currentPage = 1);
                    this.$watch('filterCategory', (val) => {
                        this.currentPage = 1;
                        if (val) {
                            const asset = this.assets.find(a => a.category === val);
                            if (asset && asset.classification) {
                                this.filterClassification = asset.classification;
                            }
                        }
                    });

                    this.$watch('filterClassification', (val) => {
                        if (val && this.filterCategory) {
                            const validCategories = [...new Set(this.assets.filter(a => a.classification === val).map(a => a.category).filter(Boolean))];
                            if (!validCategories.includes(this.filterCategory)) {
                                this.filterCategory = '';
                            }
                        }
                    });

                    try {
                        this.assets = unassignedAssetsList.map(a => ({
                            ...a,
                            selected: false,
                            empSearch: '',
                            employee_id: null,
                            employee_id_str: '',
                            employee_name: '',
                            employee_pos: '',
                            employee_status: '',
                            showEmpDropdown: false,
                            empResults: [],
                            
                            locSearch: '',
                            school_id: '',
                            school_type: '',
                            school_name: '',
                            location: '',
                            region: 'Region IX',
                            division: 'Division of Zamboanga City',
                            showLocDropdown: false,
                            locResults: [],
                            
                            property_number: a.property_number || '',
                            property_number_base: a.property_number || '',
                            serial_number: a.serial_number || '',
                            acquisition_date: a.acceptance_date || new Date().toISOString().split('T')[0]
                        }));
                    } catch (err) {
                        console.error(err);
                        Swal.fire('Error', 'Could not load unassigned assets.', 'error');
                    } finally {
                        this.loading = false;
                    }
                },

                get selectedCount() {
                    return this.assets.filter(a => a.selected).length;
                },

                toggleSelectAll(checked) {
                    this.filteredAssets.forEach(a => {
                        a.selected = checked;
                    });
                },

                confirmHardDeleteAssetInAssign(id, propertyNumber) {
                    const msg = `Are you sure you want to permanently delete asset "${propertyNumber}"? This will delete all transfer history, documents, services, photos, and cannot be undone!`;
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Confirm Hard Delete',
                            text: msg,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Yes, permanently delete it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                this.executeSingleDeleteInAssign(id);
                            }
                        });
                    } else {
                        if (confirm(msg)) {
                            this.executeSingleDeleteInAssign(id);
                        }
                    }
                },

                async executeSingleDeleteInAssign(id) {
                    this.loading = true;
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const response = await fetch(`/assets/${id}/delete`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ _method: 'DELETE' })
                        });
                        
                        if (response.ok) {
                            // Remove from assets local list
                            this.assets = this.assets.filter(a => a.assignment_id !== id);
                            
                            if (typeof Swal !== 'undefined') {
                                Swal.fire('Deleted!', 'Asset has been permanently deleted.', 'success');
                            } else {
                                alert('Asset has been permanently deleted.');
                            }
                        } else {
                            const errData = await response.json();
                            throw new Error(errData.error || 'Failed to delete asset.');
                        }
                    } catch (err) {
                        console.error(err);
                        Swal.fire('Error', err.message || 'An error occurred while deleting the asset.', 'error');
                    } finally {
                        this.loading = false;
                    }
                },

                openBulkDeleteModalInAssign() {
                    const selected = this.assets.filter(a => a.selected);
                    const ids = selected.map(a => a.assignment_id);
                    if (ids.length === 0) return;

                    const msg = `Are you sure you want to permanently delete the ${ids.length} selected asset(s)? This will delete all their transfer history, documents, services, photos, and cannot be undone!`;
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Confirm Bulk Hard Delete',
                            text: msg,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Yes, permanently delete them!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                this.executeBulkDeleteInAssign(ids);
                            }
                        });
                    } else {
                        if (confirm(msg)) {
                            this.executeBulkDeleteInAssign(ids);
                        }
                    }
                },

                async executeBulkDeleteInAssign(ids) {
                    this.loading = true;
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const response = await fetch('/api/assets/bulk-delete', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ ids: ids })
                        });
                        
                        const data = await response.json();
                        if (response.ok && data.success) {
                            // Remove from assets local list
                            this.assets = this.assets.filter(a => !ids.includes(a.assignment_id));
                            
                            if (typeof Swal !== 'undefined') {
                                Swal.fire('Deleted!', data.success, 'success');
                            } else {
                                alert(data.success);
                            }
                        } else {
                            throw new Error(data.error || 'Failed to bulk-delete assets.');
                        }
                    } catch (err) {
                        console.error(err);
                        Swal.fire('Error', err.message || 'An error occurred during bulk deletion.', 'error');
                    } finally {
                        this.loading = false;
                    }
                },

                closeAllDropdowns() {
                    this.assets.forEach(a => {
                        a.showEmpDropdown = false;
                        a.showLocDropdown = false;
                    });
                    this.bulkData.showEmpDropdown = false;
                    this.bulkData.showLocDropdown = false;
                },

                get availableClassifications() {
                    return [...new Set(this.assets.map(a => a.classification).filter(Boolean))].sort();
                },
                get availableCategories() {
                    let source = this.assets;
                    if (this.filterClassification) {
                        source = source.filter(a => a.classification === this.filterClassification);
                    }
                    return [...new Set(source.map(a => a.category).filter(Boolean))].sort();
                },
                get availableConditions() {
                    return [...new Set(this.assets.map(a => a.condition).filter(Boolean))].sort();
                },

                 get filteredAssets() {
                    const q = this.searchQuery.toLowerCase();
                    return this.assets.filter(a => {
                        const matchQ = (a.item_name || '').toLowerCase().includes(q) || 
                                       (a.sub_item_name || '').toLowerCase().includes(q) ||
                                       (a.classification || '').toLowerCase().includes(q) ||
                                       (a.property_number || '').toLowerCase().includes(q) ||
                                       (a.serial_number || '').toLowerCase().includes(q);
                        
                        const matchClass = !this.filterClassification || a.classification === this.filterClassification;
                        const matchCat   = !this.filterCategory || a.category === this.filterCategory;
                        const matchCond  = !this.filterCondition || a.condition === this.filterCondition;
                        
                        return matchQ && matchClass && matchCat && matchCond;
                    });
                },

                get totalPages() {
                    return Math.ceil(this.filteredAssets.length / this.perPage) || 1;
                },

                get paginatedAssets() {
                    const start = (this.currentPage - 1) * this.perPage;
                    return this.filteredAssets.slice(start, start + this.perPage);
                },

                prevPage() {
                    if (this.currentPage > 1) {
                        this.currentPage--;
                        this.scrollToTop();
                    }
                },

                nextPage() {
                    if (this.currentPage < this.totalPages) {
                        this.currentPage++;
                        this.scrollToTop();
                    }
                },

                scrollToTop() {
                    const tableContainer = document.querySelector('.xls-scroll-wrap');
                    if (tableContainer) {
                        tableContainer.scrollTop = 0;
                    }
                },

                get pendingCount() {
                    return this.assets.filter(a => a.employee_id || a.school_db_id).length;
                },

                searchEmployee(row) {
                    const q = (row.empSearch || '').toLowerCase().trim();
                    if (!q) {
                        row.empResults = allCustodiansList.slice(0, 15);
                    } else {
                        row.empResults = allCustodiansList.filter(e => {
                            const name = (e.first_name + ' ' + e.last_name).toLowerCase();
                            const id = (e.employee_id || '').toLowerCase();
                            return name.includes(q) || id.includes(q);
                        }).slice(0, 15);
                    }
                },

                selectEmployee(row, emp) {
                    row.employee_id = emp.id;
                    row.employee_id_str = emp.employee_id || '';
                    row.employee_name = emp.first_name + ' ' + emp.last_name;
                    row.employee_pos = emp.position || '';
                    row.employee_status = emp.status || '';
                    row.empSearch = emp.employee_id || row.employee_name;
                    row.showEmpDropdown = false;
                    
                    // Autofill Location
                    let loc = null;
                    if (emp.school_id) {
                        loc = allSchoolsList.find(s => !s.is_office && s.id === emp.school_id);
                    } else if (emp.office_id) {
                        loc = allSchoolsList.find(s => s.is_office && s.id === emp.office_id);
                    }
                    if (loc) {
                        this.selectLocation(row, loc);
                    } else {
                        row.location = 'Zamboanga City';
                    }
                },

                clearEmployee(row) {
                    row.empSearch = '';
                    row.employee_id = null;
                    row.employee_id_str = '';
                    row.employee_name = '';
                    row.employee_pos = '';
                    row.employee_status = '';
                    row.showEmpDropdown = false;
                    this.clearLocation(row);
                },

                searchLocation(row) {
                    const q = (row.locSearch || '').toLowerCase().trim();
                    if (!q) {
                        row.locResults = allSchoolsList.slice(0, 15);
                    } else {
                        row.locResults = allSchoolsList.filter(L => {
                            const matchStr = ((L.global_id || '') + ' ' + (L.type || '') + ' ' + (L.name || '') + ' ' + (L.location || '')).toLowerCase();
                            return matchStr.includes(q);
                        }).slice(0, 15);
                    }
                },

                selectLocation(row, loc) {
                    row.school_db_id = loc.id;
                    row.is_office = loc.is_office || false;
                    row.school_id = loc.global_id || '';
                    row.school_type = loc.type || '';
                    row.school_name = loc.name || '';
                    row.location = loc.location || 'Zamboanga City';
                    row.locSearch = loc.name || loc.global_id || '';
                    row.showLocDropdown = false;

                    // Append the school_id (if a school) or office_id (if an office) to the
                    // auto-generated property number: "{EQ} {YEAR}-{P1}-{P2}-{ORDER}-{school_id/office_id}"
                    if (loc.global_id) {
                        const base = row.property_number_base || row.property_number || '';
                        row.property_number = base + '-' + loc.global_id;
                    }
                },

                clearLocation(row) {
                    row.locSearch = '';
                    row.school_db_id = null;
                    row.is_office = false;
                    row.school_id = '';
                    row.school_type = '';
                    row.school_name = '';
                    row.location = '';
                    row.showLocDropdown = false;

                    // Revert property number back to its auto-generated base (no location suffix)
                    row.property_number = row.property_number_base || row.property_number;
                },

                openBulkAssignModal() {
                    this.bulkData = {
                        selectFrom: '',
                        selectTo: '',
                        serial_number: '',
                        empSearch: '',
                        employee_id: null,
                        employee_id_str: '',
                        employee_name: '',
                        employee_pos: '',
                        employee_status: '',
                        showEmpDropdown: false,
                        empResults: [],
                        locSearch: '',
                        school_db_id: null,
                        is_office: false,
                        school_id: '',
                        school_type: '',
                        school_name: '',
                        location: '',
                        showLocDropdown: false,
                        locResults: [],
                        acquisition_date: new Date().toISOString().split('T')[0]
                    };
                    this.bulkAssignOpen = true;
                },

                closeBulkAssignModal() {
                    this.bulkAssignOpen = false;
                },

                openBulkHardDeleteModal() {
                    this.bulkDeleteMode = 'rows';
                    this.bulkDeleteFrom = 1;
                    this.bulkDeleteTo = Math.min(10, this.filteredAssets.length);
                    this.bulkHardDeleteOpen = true;
                },

                closeBulkHardDeleteModal() {
                    this.bulkHardDeleteOpen = false;
                },

                setDeleteMode(mode) {
                    this.bulkDeleteMode = mode;
                    this.bulkDeleteFrom = 1;
                    if (mode === 'rows') {
                        this.bulkDeleteTo = Math.min(10, this.filteredAssets.length);
                    } else {
                        this.bulkDeleteTo = Math.min(2, this.totalPages);
                    }
                },

                confirmBulkHardDeleteInAssign() {
                    let targets = [];
                    if (this.bulkDeleteMode === 'rows') {
                        const startIdx = parseInt(this.bulkDeleteFrom) - 1;
                        const endIdx = parseInt(this.bulkDeleteTo) - 1;
                        if (isNaN(startIdx) || isNaN(endIdx) || startIdx < 0 || endIdx < startIdx) {
                            Swal.fire('Invalid Range', 'Please enter a valid row range.', 'error');
                            return;
                        }
                        if (endIdx >= this.filteredAssets.length) {
                            Swal.fire('Invalid Range', `The ending row exceeds the total unassigned assets count (${this.filteredAssets.length}).`, 'error');
                            return;
                        }
                        targets = this.filteredAssets.slice(startIdx, endIdx + 1);
                    } else {
                        const startPg = parseInt(this.bulkDeleteFrom);
                        const endPg = parseInt(this.bulkDeleteTo);
                        if (isNaN(startPg) || isNaN(endPg) || startPg < 1 || endPg < startPg) {
                            Swal.fire('Invalid Range', 'Please enter a valid page range.', 'error');
                            return;
                        }
                        if (startPg > this.totalPages) {
                            Swal.fire('Invalid Range', `The starting page exceeds the total page count (${this.totalPages}).`, 'error');
                            return;
                        }
                        const startIdx = (startPg - 1) * this.perPage;
                        const endIdx = Math.min((endPg * this.perPage) - 1, this.filteredAssets.length - 1);
                        targets = this.filteredAssets.slice(startIdx, endIdx + 1);
                    }

                    const ids = targets.map(a => a.assignment_id);
                    if (ids.length === 0) {
                        Swal.fire('No Assets', 'No assets found in the specified range.', 'info');
                        return;
                    }

                    const rangeText = this.bulkDeleteMode === 'rows' 
                        ? `Rows ${this.bulkDeleteFrom} to ${this.bulkDeleteTo}` 
                        : `Pages ${this.bulkDeleteFrom} to ${this.bulkDeleteTo}`;
                    const msg = `Are you sure you want to permanently delete ${ids.length} asset(s) in the range (${rangeText})? This will delete all their transfer history, documents, services, photos, and cannot be undone!`;

                    Swal.fire({
                        title: 'Confirm Bulk Hard Delete',
                        text: msg,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, permanently delete them!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.closeBulkHardDeleteModal();
                            this.executeBulkDeleteInAssign(ids);
                        }
                    });
                },

                searchBulkEmployee() {
                    const q = (this.bulkData.empSearch || '').toLowerCase().trim();
                    if (!q) {
                        this.bulkData.empResults = allCustodiansList.slice(0, 15);
                    } else {
                        this.bulkData.empResults = allCustodiansList.filter(e => {
                            const name = (e.first_name + ' ' + e.last_name).toLowerCase();
                            const id = (e.employee_id || '').toLowerCase();
                            return name.includes(q) || id.includes(q);
                        }).slice(0, 15);
                    }
                },

                selectBulkEmployee(emp) {
                    this.bulkData.employee_id = emp.id;
                    this.bulkData.employee_id_str = emp.employee_id || '';
                    this.bulkData.employee_name = emp.first_name + ' ' + emp.last_name;
                    this.bulkData.employee_pos = emp.position || '';
                    this.bulkData.employee_status = emp.status || '';
                    this.bulkData.empSearch = emp.employee_id || this.bulkData.employee_name;
                    this.bulkData.showEmpDropdown = false;

                    // Autofill Location
                    let loc = null;
                    if (emp.school_id) {
                        loc = allSchoolsList.find(s => !s.is_office && s.id === emp.school_id);
                    } else if (emp.office_id) {
                        loc = allSchoolsList.find(s => s.is_office && s.id === emp.office_id);
                    }
                    if (loc) {
                        this.selectBulkLocation(loc);
                    } else {
                        this.bulkData.location = 'Zamboanga City';
                    }
                },

                clearBulkEmployee() {
                    this.bulkData.empSearch = '';
                    this.bulkData.employee_id = null;
                    this.bulkData.employee_id_str = '';
                    this.bulkData.employee_name = '';
                    this.bulkData.employee_pos = '';
                    this.bulkData.employee_status = '';
                    this.bulkData.showEmpDropdown = false;
                    this.clearBulkLocation();
                },

                searchBulkLocation() {
                    const q = (this.bulkData.locSearch || '').toLowerCase().trim();
                    if (!q) {
                        this.bulkData.locResults = allSchoolsList.slice(0, 15);
                    } else {
                        this.bulkData.locResults = allSchoolsList.filter(L => {
                            const matchStr = ((L.global_id || '') + ' ' + (L.type || '') + ' ' + (L.name || '') + ' ' + (L.location || '')).toLowerCase();
                            return matchStr.includes(q);
                        }).slice(0, 15);
                    }
                },

                selectBulkLocation(loc) {
                    this.bulkData.school_db_id = loc.id;
                    this.bulkData.is_office = loc.is_office || false;
                    this.bulkData.school_id = loc.global_id || '';
                    this.bulkData.school_type = loc.type || '';
                    this.bulkData.school_name = loc.name || '';
                    this.bulkData.location = loc.location || 'Zamboanga City';
                    this.bulkData.locSearch = loc.name || loc.global_id || '';
                    this.bulkData.showLocDropdown = false;
                },

                clearBulkLocation() {
                    this.bulkData.locSearch = '';
                    this.bulkData.school_db_id = null;
                    this.bulkData.is_office = false;
                    this.bulkData.school_id = '';
                    this.bulkData.school_type = '';
                    this.bulkData.school_name = '';
                    this.bulkData.location = '';
                    this.bulkData.showLocDropdown = false;
                },

                selectRangeOnly() {
                    const fromNum = parseInt(this.bulkData.selectFrom);
                    const toNum = parseInt(this.bulkData.selectTo);
                    
                    if (isNaN(fromNum) || isNaN(toNum)) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Invalid Range',
                            text: 'Please specify both start and end row numbers.',
                            confirmButtonColor: '#c00000',
                            customClass: { popup: 'rounded-2xl' }
                        });
                        return;
                    }

                    if (fromNum <= 0 || toNum < fromNum || toNum > this.filteredAssets.length) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Invalid Range',
                            text: `Please enter a valid range between 1 and ${this.filteredAssets.length}.`,
                            confirmButtonColor: '#c00000',
                            customClass: { popup: 'rounded-2xl' }
                        });
                        return;
                    }

                    // Check them
                    this.filteredAssets.forEach((asset, idx) => {
                        const displayNum = idx + 1;
                        if (displayNum >= fromNum && displayNum <= toNum) {
                            asset.selected = true;
                        }
                    });

                    Swal.fire({
                        icon: 'success',
                        title: 'Selection Updated',
                        text: `Selected row #${fromNum} to #${toNum} successfully.`,
                        timer: 1500,
                        showConfirmButton: false,
                        customClass: { popup: 'rounded-2xl' }
                    });
                },

                clearRangeSelection() {
                    const fromNum = parseInt(this.bulkData.selectFrom);
                    const toNum = parseInt(this.bulkData.selectTo);

                    if (!isNaN(fromNum) && !isNaN(toNum)) {
                        this.filteredAssets.forEach((asset, idx) => {
                            const displayNum = idx + 1;
                            if (displayNum >= fromNum && displayNum <= toNum) {
                                asset.selected = false;
                            }
                        });
                    } else {
                        // Clear all selected assets in currently filtered view
                        this.filteredAssets.forEach(asset => asset.selected = false);
                    }
                },

                applyBulkAssign() {
                    // Auto-select range if specified
                    const fromNum = parseInt(this.bulkData.selectFrom);
                    const toNum = parseInt(this.bulkData.selectTo);
                    if (!isNaN(fromNum) && !isNaN(toNum)) {
                        if (fromNum > 0 && toNum >= fromNum && toNum <= this.filteredAssets.length) {
                            this.filteredAssets.forEach((asset, idx) => {
                                const displayNum = idx + 1;
                                if (displayNum >= fromNum && displayNum <= toNum) {
                                    asset.selected = true;
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Invalid Range',
                                text: `Please enter a valid range between 1 and ${this.filteredAssets.length}.`,
                                confirmButtonColor: '#c00000',
                                customClass: { popup: 'rounded-2xl' }
                            });
                            return;
                        }
                    }

                    const selected = this.assets.filter(a => a.selected);
                    if (selected.length === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'No Assets Selected',
                            text: 'Please select at least one asset using the checkbox on the right or enter a valid range.',
                            confirmButtonColor: '#c00000',
                            customClass: { popup: 'rounded-2xl' }
                        });
                        return;
                    }

                    // Check if serial number is being filled AND there is a selected asset with quantity > 1
                    if (this.bulkData.serial_number.trim() !== '') {
                        const invalidAsset = selected.find(a => Number(a.quantity || 0) > 1);
                        if (invalidAsset) {
                            // Find the display index of this invalid asset in the list
                            const displayIndex = this.assets.indexOf(invalidAsset) + 1;
                            Swal.fire({
                                icon: 'error',
                                title: 'Bulk Assign Failed',
                                text: 'Bulk assign could not be done due to Item No. (' + displayIndex + ') having their serial no. field disabled.',
                                confirmButtonColor: '#c00000',
                                customClass: { popup: 'rounded-2xl' }
                            });
                            return;
                        }
                    }

                    // Apply the fields
                    selected.forEach(asset => {
                        if (this.bulkData.serial_number.trim() !== '') {
                            asset.serial_number = this.bulkData.serial_number;
                        }
                        if (this.bulkData.employee_name) {
                            asset.empSearch = this.bulkData.empSearch;
                            asset.employee_id = this.bulkData.employee_id;
                            asset.employee_id_str = this.bulkData.employee_id_str;
                            asset.employee_name = this.bulkData.employee_name;
                            asset.employee_pos = this.bulkData.employee_pos;
                            asset.employee_status = this.bulkData.employee_status;
                        }
                        if (this.bulkData.school_name) {
                            asset.locSearch = this.bulkData.locSearch;
                            asset.school_db_id = this.bulkData.school_db_id;
                            asset.is_office = this.bulkData.is_office;
                            asset.school_id = this.bulkData.school_id;
                            asset.school_type = this.bulkData.school_type;
                            asset.school_name = this.bulkData.school_name;
                            asset.location = this.bulkData.location;

                            // Append school_id/office_id to this row's property number
                            if (this.bulkData.school_id) {
                                const base = asset.property_number_base || asset.property_number || '';
                                asset.property_number = base + '-' + this.bulkData.school_id;
                            }
                        }
                        if (this.bulkData.acquisition_date) {
                            asset.acquisition_date = this.bulkData.acquisition_date;
                        }
                    });

                    this.bulkAssignOpen = false;
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Bulk assign successfully applied to ' + selected.length + ' asset(s).',
                        timer: 1500,
                        showConfirmButton: false,
                        customClass: { popup: 'rounded-2xl' }
                    });
                },

                submitBatch() {
                    const toAssign = this.assets.filter(a => a.employee_id || a.school_db_id);
                    if (toAssign.length === 0) return;

                    const payload = {
                        assignments: toAssign.map(r => ({
                            assignment_id: r.assignment_id,
                            employee_id: r.employee_id,
                            school_name: r.school_name,
                            school_id: r.school_id,
                            school_db_id: r.school_db_id,
                            is_office: r.is_office,
                            school_type: r.school_type,
                            location: r.location,
                            property_number: r.property_number,
                            serial_number: r.serial_number,
                            acquisition_date: r.acquisition_date,
                            asset_cost: r.asset_cost,
                            quantity: r.quantity
                        }))
                    };

                    Swal.fire({
                        title: 'Deploy Assignments?',
                        text: "You are about to distribute " + toAssign.length + " asset(s).",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#c00000',
                        confirmButtonText: 'Yes, Deploy',
                        cancelButtonText: 'Cancel',
                        customClass: { popup: 'rounded-2xl', confirmButton: 'rounded-xl font-bold px-6', cancelButton: 'rounded-xl font-bold px-6' }
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            Swal.fire({ title: 'Deploying...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                            
                            try {
                                const res = await fetch('{{ route('assign_asset.storeBatch') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                    },
                                    body: JSON.stringify(payload)
                                });
                                
                                const data = await res.json();
                                if (!res.ok) throw new Error(data.message || 'Server error');
                                
                                Swal.fire({
                                    title: 'Success!',
                                    text: 'Assets successfully deployed.',
                                    icon: 'success',
                                    confirmButtonColor: '#10b981'
                                }).then(() => {
                                    window.location.reload();
                                });
                            } catch (err) {
                                console.error(err);
                                Swal.fire('Error', err.message, 'error');
                            }
                        }
                    });
                }
            }
        }
    </script>
</div>