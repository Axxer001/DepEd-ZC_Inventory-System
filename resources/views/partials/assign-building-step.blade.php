{{-- ------- STEP: ASSIGN BUILDING ------- --}}
<div id="stepAssignBuilding" class="step-content" x-data="assignBuildingApp()">
    <div class="flex-1 p-0 relative">
        <div class="bg-white rounded-[2rem] border border-slate-100 shadow-lg overflow-hidden flex flex-col relative" id="assetTableCard">
            
            {{-- Toolbar --}}
            <div id="assetToolbar" class="px-5 py-3 border-b border-slate-100 flex items-center justify-between gap-3 flex-wrap">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 bg-slate-800 rounded-xl flex items-center justify-center text-white text-xs font-black shrink-0">1</div>
                    <span class="text-[10px] font-bold text-slate-900 uppercase tracking-widest italic ml-2">Unassigned Buildings Registry</span>
                </div>
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <svg class="w-4 h-4 absolute left-3 top-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" x-model="searchQuery" placeholder="Filter Buildings..." class="bg-slate-100 border border-transparent pl-10 pr-4 py-2 rounded-xl text-xs font-bold focus:outline-none focus:bg-white focus:border-slate-300 transition-all w-64">
                    </div>
                    <button @click="submitBatch" :disabled="pendingCount === 0"
                        class="px-8 py-2.5 bg-[#c00000] disabled:bg-slate-300 disabled:cursor-not-allowed text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-red-700 transition-all flex items-center gap-3 shadow-sm active:scale-95">
                        <span>Deploy Assignments</span>
                        <div class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center" x-text="pendingCount">0</div>
                    </button>
                </div>
            </div>

            <!-- Loader -->
            <div x-show="loading" class="absolute inset-0 z-50 bg-white/80 backdrop-blur-sm flex flex-col items-center justify-center">
                <svg class="w-8 h-8 animate-spin text-[#c00000]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                <span class="mt-4 text-xs font-bold uppercase tracking-widest text-[#c00000]">Fetching Buildings...</span>
            </div>

            <div class="xls-scroll-wrap" x-show="!loading">
                <table class="w-full border-collapse" style="min-width:1800px;">
                    <thead>
                        <tr>
                            <th class="xls-th w-10 text-center sticky left-0 z-20">#</th>
                            <th class="xls-th col-identity" style="min-width:140px">Classification</th>
                            <th class="xls-th col-identity" style="min-width:160px">Building Type</th>
                            <th class="xls-th col-identity" style="min-width:180px">Building Name</th>
                            <th class="xls-th col-context" style="min-width:200px">Structure Details</th>
                            <th class="xls-th col-financial text-right" style="min-width:120px">Asset Cost (?)</th>
                            <th class="xls-th col-status" style="min-width:120px">Remarks</th>
                            <th class="xls-th col-personnel" style="min-width:220px">School Search</th>
                            <th class="xls-th col-personnel" style="min-width:180px">Assigned To</th>
                            <th class="xls-th col-personnel" style="min-width:150px">Property Number</th>
                            <th class="xls-th col-temporal" style="min-width:140px">Acquisition Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(asset, index) in filteredAssets" :key="asset.assignment_id">
                            <tr class="xls-row" :class="{'bg-[#fdf2f8]': asset.school_id}">
                                <td class="xls-td xls-sticky-col text-center font-bold text-slate-400 sticky left-0 z-10" x-text="index + 1"></td>
                                
                                <td class="xls-td col-identity"><input type="text" class="xls-input xls-const" readonly :value="asset.classification || '-'"></td>
                                <td class="xls-td col-identity"><input type="text" class="xls-input xls-const" readonly :value="asset.category || '-'"></td>
                                <td class="xls-td col-identity"><input type="text" class="xls-input xls-const font-bold" readonly :value="asset.item_name || '-'"></td>
                                <td class="xls-td col-context"><input type="text" class="xls-input xls-const" readonly :value="asset.sub_item_name || 'General'"></td>
                                <td class="xls-td col-financial text-right"><input type="text" class="xls-input xls-const text-right font-semibold" readonly :value="Number(asset.asset_cost || 0).toLocaleString()"></td>
                                <td class="xls-td col-status text-center"><input type="text" class="xls-input xls-const text-center text-[10px]" readonly :value="asset.condition || '-'"></td>
                                
                                <td class="xls-td col-personnel relative" style="overflow:visible;">
                                    <input type="text" x-model="asset.schoolSearch" @input="searchSchool(asset)" @focus="asset.showSchoolDropdown = true; searchSchool(asset)" @click.away="asset.showSchoolDropdown = false" class="xls-input font-bold text-[#c00000]" placeholder="Search ID or Name...">
                                    <div x-show="asset.showSchoolDropdown" class="custom-autocomplete" style="width: 250px;">
                                        <template x-for="sch in asset.schoolResults" :key="sch.id">
                                            <div class="custom-autocomplete-item" @click="selectSchool(asset, sch)">
                                                <div x-text="sch.name"></div>
                                                <div class="text-[9px] text-slate-400 mt-0.5" x-text="(sch.school_id || 'No ID')"></div>
                                            </div>
                                        </template>
                                        <div x-show="asset.schoolResults.length === 0" class="p-2 text-[10px] text-slate-400 italic">No matches...</div>
                                    </div>
                                </td>
                                
                                <td class="xls-td col-personnel">
                                    <input type="text" readonly x-model="asset.school_name" class="xls-input font-black text-slate-700 bg-white/50" placeholder="Not Assigned">
                                </td>
                                
                                <td class="xls-td col-personnel">
                                    <input type="text" x-model="asset.property_number" class="xls-input" placeholder="Property No.">
                                </td>
                                
                                <td class="xls-td col-temporal">
                                    <input type="date" x-model="asset.acquisition_date" class="xls-input uppercase font-bold text-slate-600">
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                
                <!-- Empty State inside scroll wrap -->
                <div x-show="!loading && filteredAssets.length === 0" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="inline-flex flex-col items-center gap-3 opacity-30">
                        <svg class="w-8 h-8 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0112 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125h17.25"/></svg>
                        <p class="text-[10px] font-black text-slate-900 uppercase tracking-[0.25em]" x-text="searchQuery ? 'No matching buildings found' : 'No unassigned buildings available'"></p>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div id="assetTableFooter" class="px-5 py-3 border-t border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-6">
                    <p class="text-[9px] font-black text-slate-900 uppercase tracking-widest"><span x-text="filteredAssets.length"></span> Rows</p>
                    <div class="flex items-center gap-2 border-l border-slate-200 pl-6">
                        <button disabled class="pg-btn text-slate-900">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
                            Prev
                        </button>
                        <div class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 rounded-lg">
                            <span class="text-[10px] font-black text-slate-800">1</span>
                            <span class="text-[10px] font-bold text-slate-900">/</span>
                            <span class="text-[10px] font-black text-slate-900">1</span>
                        </div>
                        <button disabled class="pg-btn text-slate-900">
                            Next
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- App Logic -->
    <script>
        function assignBuildingApp() {
            return {
                assets: [],
                searchQuery: '',
                loading: true,

                async init() {
                    try {
                        this.assets = unassignedBuildingsList.map(a => ({
                            ...a,
                            schoolSearch: '',
                            school_id: null,
                            school_name: '',
                            showSchoolDropdown: false,
                            schoolResults: [],
                            property_number: a.property_number || '',
                            acquisition_date: a.acceptance_date || new Date().toISOString().split('T')[0]
                        }));
                    } catch (err) {
                        console.error(err);
                        Swal.fire('Error', 'Could not load unassigned buildings.', 'error');
                    } finally {
                        this.loading = false;
                    }
                },

                get filteredAssets() {
                    const q = this.searchQuery.toLowerCase();
                    return this.assets.filter(a => {
                        return (a.item_name || '').toLowerCase().includes(q) || 
                               (a.sub_item_name || '').toLowerCase().includes(q) ||
                               (a.classification || '').toLowerCase().includes(q);
                    });
                },

                get pendingCount() {
                    return this.assets.filter(a => a.school_id).length;
                },

                searchSchool(row) {
                    const q = (row.schoolSearch || '').toLowerCase().trim();
                    if (!q) {
                        row.schoolResults = allSchoolsList.slice(0, 15);
                    } else {
                        row.schoolResults = allSchoolsList.filter(s => {
                            const name = (s.name || '').toLowerCase();
                            const id = (s.school_id || '').toLowerCase();
                            return name.includes(q) || id.includes(q);
                        }).slice(0, 15);
                    }
                },

                selectSchool(row, sch) {
                    row.school_id = sch.school_id;
                    row.school_name = sch.name;
                    row.schoolSearch = sch.school_id || row.school_name;
                    row.showSchoolDropdown = false;
                },

                submitBatch() {
                    const toAssign = this.assets.filter(a => a.school_id);
                    if (toAssign.length === 0) return;

                    const payload = {
                        assignments: toAssign.map(r => ({
                            assignment_id: r.assignment_id,
                            school_id: r.school_id,
                            property_number: r.property_number,
                            acquisition_date: r.acquisition_date
                        }))
                    };

                    Swal.fire({
                        title: 'Deploy Assignments?',
                        text: "You are about to assign " + toAssign.length + " building(s).",
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
                                const res = await fetch('{{ route('assign_building.storeBatch') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                    },
                                    body: JSON.stringify(payload)
                                });
                                
                                const data = await res.json();
                                if (!res.ok) throw new Error(data.message || 'Server error');
                                
                                Swal.fire({
                                    title: 'Success!',
                                    text: 'Buildings successfully assigned.',
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
