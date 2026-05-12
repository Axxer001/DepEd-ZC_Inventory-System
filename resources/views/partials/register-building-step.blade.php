<div id="stepAddBuilding" class="step-content">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-6 mt-6">
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight italic uppercase text-red-600">Building <span class="text-slate-900">Records</span></h2>
            <p class="text-slate-400 text-sm font-bold uppercase mt-1 tracking-widest leading-tight">View and manage master building registry</p>
        </div>
        <div></div>
    </div>

    <!-- Filter Configuration -->
    <div class="bg-white rounded-[2.5rem] shadow-lg border border-slate-100 p-8 mb-8 relative overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-8 relative z-10">
            {{-- Row 1 --}}
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Classification</label>
                <select id="bldgFilterClass" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-500">
                    <option value="">All Classifications</option>
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Office Type</label>
                <select id="bldgFilterType" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-500">
                    <option value="">All Types</option>
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">School Name</label>
                <select id="bldgFilterSchool" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-500">
                    <option value="">All Schools</option>
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Cost Sorting</label>
                <select id="bldgFilterSort" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-500">
                    <option value="">Default (ID)</option>
                    <option value="low_to_high">Acquisition Cost: Low to High</option>
                    <option value="high_to_low">Acquisition Cost: High to Low</option>
                </select>
            </div>

            {{-- Row 2 --}}
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Location</label>
                <select id="bldgFilterLoc" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-500">
                    <option value="">All Locations</option>
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Acquisition Date</label>
                <input type="date" id="bldgFilterDate" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-500">
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic text-red-600">Data Integrity (Empty Fields)</label>
                <select id="bldgFilterIntegrity" class="w-full bg-slate-50 border-red-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-500">
                    <option value="">No Integrity Filter</option>
                    <option value="classification">Missing Classification</option>
                    <option value="school_id">Missing School ID</option>
                    <option value="school_name">Missing School Name</option>
                    <option value="property_number">Missing Property Number</option>
                    <option value="description">Missing Description</option>
                    <option value="location">Missing Location</option>
                    <option value="acquisition_date">Missing Acquisition Date</option>
                    <option value="acquisition_cost">Missing Cost</option>
                    <option value="condition">Missing Condition</option>
                </select>
            </div>
        </div>
        <div class="mt-8 flex justify-end items-center gap-8 relative z-10">
            <button onclick="clearBldgFilters()" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] hover:text-red-600 transition-all italic">Clear All Filters</button>
            <button onclick="bldgFetchData()" class="px-8 py-2.5 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-red-600 transition-all active:scale-95 shadow-lg shadow-slate-200 italic">Apply Configuration</button>
        </div>
    </div>

    <!-- Data Grid -->
    <div id="bldgTableCard" class="bg-white rounded-[2rem] border border-slate-100 shadow-xl overflow-hidden relative">
        <div class="xls-scroll-wrap overflow-x-auto custom-scroll">
            <table class="w-full border-collapse" style="min-width:2400px;">
                <thead>
                    <tr>
                        <th class="xls-th w-10 text-center sticky left-0 z-10">#</th>
                        <th class="xls-th" style="min-width:90px">Region</th>
                        <th class="xls-th" style="min-width:190px">Division</th>
                        <th class="xls-th" style="min-width:140px">Office/School Type</th>
                        <th class="xls-th" style="min-width:100px">School ID</th>
                        <th class="xls-th" style="min-width:200px">Office/School Name</th>
                        <th class="xls-th" style="min-width:180px">Address</th>
                        <th class="xls-th" style="min-width:70px">Storeys</th>
                        <th class="xls-th" style="min-width:90px">Classrooms</th>
                        <th class="xls-th" style="min-width:140px">Article</th>
                        <th class="xls-th" style="min-width:170px">Description</th>
                        <th class="xls-th" style="min-width:130px">Classification</th>
                        <th class="xls-th" style="min-width:130px">Occupancy</th>
                        <th class="xls-th" style="min-width:150px">Location</th>
                        <th class="xls-th" style="min-width:120px">Date Constructed</th>
                        <th class="xls-th" style="min-width:120px">Acquisition Date</th>
                        <th class="xls-th" style="min-width:130px">Property No.</th>
                        <th class="xls-th text-right" style="min-width:120px">Acq. Cost (₱)</th>
                        <th class="xls-th text-center" style="min-width:100px">Est. Useful Life</th>
                        <th class="xls-th text-right" style="min-width:120px">Appraised Value</th>
                        <th class="xls-th" style="min-width:120px">Appraisal Date</th>
                        <th class="xls-th" style="min-width:140px">Remarks</th>
                    </tr>
                </thead>
                <tbody id="bldgBody"></tbody>
            </table>
            
            {{-- Loading State --}}
            <div id="bldgLoading" class="absolute inset-0 bg-white/60 backdrop-blur-[2px] z-50 flex items-center justify-center hidden">
                <div class="flex flex-col items-center gap-4">
                    <div class="w-12 h-12 border-4 border-slate-100 border-t-red-600 rounded-full animate-spin"></div>
                    <p class="text-[10px] font-black text-slate-800 uppercase tracking-widest italic">Fetching Building Data...</p>
                </div>
            </div>

            {{-- Empty State --}}
            <div id="bldgEmpty" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="inline-flex flex-col items-center gap-3 opacity-30">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21"/></svg>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.25em]">No buildings found — adjust filters</p>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div id="bldgTableFooter" class="px-5 py-3 border-t border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center gap-6">
                <p id="bldgRowCountLabel" class="text-[9px] font-black text-slate-400 uppercase tracking-widest">0 Rows</p>
                <div id="bldgPaginationControls" class="flex items-center gap-1.5 border-l border-slate-200 pl-6">
                    {{-- Dynamically populated --}}
                </div>
            </div>
            <div></div> {{-- Spacer --}}
        </div>
    </div>
</div>

<script>
    let bldgRowsData = [];
    let bldgCurrentPage = 1;
    const bldgRowsPerPage = 50;

    // Fetch filters on load
    async function bldgFetchFilters() {
        try {
            const res = await fetch("{{ route('api.buildings.filters') }}");
            const data = await res.json();
            
            const populate = (id, list) => {
                const el = document.getElementById(id);
                const currentVal = el.value;
                el.innerHTML = `<option value="">All ${id.replace('bldgFilter', '')}s</option>`;
                list.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item;
                    opt.textContent = item;
                    if(item === currentVal) opt.selected = true;
                    el.appendChild(opt);
                });
            };

            populate('bldgFilterClass', data.classifications);
            populate('bldgFilterType', data.officeTypes);
            populate('bldgFilterSchool', data.schools);
            populate('bldgFilterLoc', data.locations);
        } catch (e) { console.error('Failed to fetch building filters', e); }
    }

    async function bldgFetchData() {
        const loading = document.getElementById('bldgLoading');
        loading.classList.remove('hidden');
        
        const filters = {
            classification: document.getElementById('bldgFilterClass').value,
            officeType: document.getElementById('bldgFilterType').value,
            schoolName: document.getElementById('bldgFilterSchool').value,
            location: document.getElementById('bldgFilterLoc').value,
            sortCost: document.getElementById('bldgFilterSort').value,
            dateAcquired: document.getElementById('bldgFilterDate').value,
            emptyCol: document.getElementById('bldgFilterIntegrity').value
        };

        try {
            const res = await fetch("{{ route('api.buildings.preview') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ filters: filters })
            });
            const data = await res.json();
            bldgRowsData = data.rows || [];
            bldgCurrentPage = 1;
            renderBldgTable();
            
            // Re-fetch filters to update counts or options if needed
            bldgFetchFilters();
        } catch (e) {
            console.error('Failed to fetch buildings', e);
            Swal.fire('Error', 'Failed to load building data.', 'error');
        } finally {
            loading.classList.add('hidden');
        }
    }

    function clearBldgFilters() {
        document.getElementById('bldgFilterClass').value = '';
        document.getElementById('bldgFilterType').value = '';
        document.getElementById('bldgFilterSchool').value = '';
        document.getElementById('bldgFilterSort').value = '';
        document.getElementById('bldgFilterLoc').value = '';
        document.getElementById('bldgFilterDate').value = '';
        document.getElementById('bldgFilterIntegrity').value = '';
        bldgFetchData();
    }

    function renderBldgTable() {
        const tbody = document.getElementById('bldgBody');
        tbody.innerHTML = '';
        
        if (bldgRowsData.length === 0) {
            document.getElementById('bldgEmpty').classList.remove('hidden');
            document.getElementById('bldgRowCountLabel').textContent = '0 Rows';
            return;
        }
        document.getElementById('bldgEmpty').classList.add('hidden');

        const start = (bldgCurrentPage - 1) * bldgRowsPerPage;
        const pageData = bldgRowsData.slice(start, start + bldgRowsPerPage);

        pageData.forEach((row, idx) => {
            const displayNum = start + idx + 1;
            const tr = document.createElement('tr');
            tr.className = 'xls-row group border-b border-slate-100';
            
            const cell = (val, extra = '') => `<td class="xls-td relative ${extra}"><span class="xls-const">${val || ''}</span></td>`;
            const numCell = (val, extra = '') => `<td class="xls-td relative ${extra}"><span class="xls-const justify-center">${val || '0'}</span></td>`;
            const costCell = (val, extra = '') => `<td class="xls-td relative ${extra}"><span class="xls-const justify-end font-bold text-red-600">₱ ${val ? parseFloat(val).toLocaleString(undefined, {minimumFractionDigits: 2}) : '0.00'}</span></td>`;

            tr.innerHTML = `
                <td class="xls-td text-center sticky left-0 w-10 bg-[#0f172a] z-10"><span class="text-[10px] font-black text-slate-500">${displayNum}</span></td>
                ${cell(row.region)}
                ${cell(row.division)}
                ${cell(row.office_type)}
                ${cell(row.school_identifier)}
                ${cell(row.office_name)}
                ${cell(row.address)}
                ${numCell(row.storeys)}
                ${numCell(row.classrooms)}
                ${cell(row.article)}
                ${cell(row.description)}
                ${cell(row.classification)}
                ${cell(row.occupancy_nature)}
                ${cell(row.location)}
                ${cell(row.date_constructed)}
                ${cell(row.acquisition_date)}
                ${cell(row.property_number)}
                ${costCell(row.acquisition_cost)}
                ${numCell(row.estimated_useful_life)}
                ${costCell(row.appraised_value)}
                ${cell(row.appraisal_date)}
                ${cell(row.remarks)}
            `;
            tbody.appendChild(tr);
        });

        const totalPages = Math.ceil(bldgRowsData.length / bldgRowsPerPage) || 1;
        document.getElementById('bldgRowCountLabel').textContent = bldgRowsData.length + " Buildings Found";
        
        renderBldgPagination(totalPages);
    }

    function renderBldgPagination(totalPages) {
        const container = document.getElementById('bldgPaginationControls');
        container.innerHTML = '';

        // Prev
        const prevBtn = document.createElement('button');
        prevBtn.className = 'pg-btn';
        prevBtn.disabled = bldgCurrentPage === 1;
        prevBtn.onclick = bldgPrevPage;
        prevBtn.innerHTML = `<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg> Prev`;
        container.appendChild(prevBtn);

        // Pages
        let startPage = Math.max(1, bldgCurrentPage - 2);
        let endPage = Math.min(totalPages, startPage + 4);
        if (endPage - startPage < 4) startPage = Math.max(1, endPage - 4);
        
        for (let i = startPage; i <= endPage; i++) {
            const btn = document.createElement('button');
            btn.className = `pg-btn min-w-[36px] ${i === bldgCurrentPage ? 'pg-btn-active' : ''}`;
            btn.textContent = i;
            btn.onclick = () => { bldgCurrentPage = i; renderBldgTable(); };
            container.appendChild(btn);
        }

        // Next
        const nextBtn = document.createElement('button');
        nextBtn.className = 'pg-btn';
        nextBtn.disabled = bldgCurrentPage === totalPages;
        nextBtn.onclick = bldgNextPage;
        nextBtn.innerHTML = `Next <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>`;
        container.appendChild(nextBtn);
    }

    function bldgPrevPage() { if (bldgCurrentPage > 1) { bldgCurrentPage--; renderBldgTable(); } }
    function bldgNextPage() { const t = Math.ceil(bldgRowsData.length/bldgRowsPerPage); if (bldgCurrentPage < t) { bldgCurrentPage++; renderBldgTable(); } }

    // Initialize when shown
    // We can hook into nextStep or just run it if the container is active
    document.addEventListener('DOMContentLoaded', () => {
        // Initial fetch if we are already on this step (unlikely but safe)
        if(document.getElementById('stepAddBuilding').classList.contains('active')) {
            bldgFetchFilters();
            bldgFetchData();
        }
    });

    // We need to override the renderBldgTable call in nextStep if it exists
    const originalRenderBldgTable = typeof renderBldgTable === 'function' ? renderBldgTable : null;
    window.renderBldgTable = function() {
        bldgFetchFilters();
        bldgFetchData();
    };
</script>
