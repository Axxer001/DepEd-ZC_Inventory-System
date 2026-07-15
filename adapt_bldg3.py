dst = 'resources/views/partials/building-edit-step.blade.php'
with open(dst, 'r', encoding='utf-8') as f:
    c = f.read()

# Replace the two-panel table structure with one single buildings table
old_source_table = '''        {{-- ── Asset Source Table ── --}}
        <div id="bldgPanel" class="flex-grow flex flex-col min-h-0">
            <div id="bldgScroll" class="xls-scroll-wrap custom-scroll overflow-x-auto overflow-y-auto transition-all duration-300" style="max-height: calc(100vh - 450px);">
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
                            <th class="xls-th text-blue-600 text-right" style="min-width:120px">Cost / Unit (&#8369;)</th>
                            <th class="xls-th text-blue-600 text-right" style="min-width:80px">Qty</th>
                            <th class="xls-th text-blue-600 text-right" style="min-width:110px">Useful Life (yrs)</th>
                            <th class="xls-th text-blue-600" style="min-width:140px">Acceptance Date</th>
                            <th class="xls-th text-blue-600" style="min-width:200px">Remarks</th>
                        </tr>
                    </thead>
                    <tbody id="bldgTableBody"></tbody>
                </table>
            </div>
        </div>

        {{-- ── Asset Distribution Table ── --}}
        <div id="bldgPanelDist" class="hidden flex-grow flex flex-col min-h-0">
            <div id="bldgDistScroll" class="xls-scroll-wrap custom-scroll overflow-x-auto overflow-y-auto transition-all duration-300" style="max-height: calc(100vh - 450px);">
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
                            <th class="xls-th text-blue-600 text-right" style="min-width:130px">Acquisition Cost (&#8369;)</th>
                            <th class="xls-th text-blue-600" style="min-width:140px">Issuance Date</th>
                        </tr>
                    </thead>
                    <tbody id="bldgDistBody"></tbody>
                </table>
            </div>
        </div>'''

new_bldg_table = '''        {{-- ── Buildings Table ── --}}
        <div id="bldgPanel" class="flex-grow flex flex-col min-h-0">
            <div id="bldgScroll" class="xls-scroll-wrap custom-scroll overflow-x-auto overflow-y-auto transition-all duration-300" style="max-height: calc(100vh - 450px);">
                <table class="w-full border-collapse" style="min-width:2800px;">
                    <thead class="sticky top-0 bg-slate-50 z-20 shadow-sm">
                        <tr>
                            <th class="xls-th w-10 text-center sticky left-0 z-30">#</th>
                            <th class="xls-th" style="min-width:90px">Region</th>
                            <th class="xls-th" style="min-width:200px">Division</th>
                            <th class="xls-th text-emerald-600" style="min-width:140px">Office Type</th>
                            <th class="xls-th text-emerald-600" style="min-width:100px">School ID</th>
                            <th class="xls-th text-emerald-600" style="min-width:210px">School Name</th>
                            <th class="xls-th text-emerald-600" style="min-width:180px">Address</th>
                            <th class="xls-th text-emerald-600 text-right" style="min-width:80px">Storeys</th>
                            <th class="xls-th text-emerald-600 text-right" style="min-width:100px">Classrooms</th>
                            <th class="xls-th text-emerald-600" style="min-width:140px">Article</th>
                            <th class="xls-th text-emerald-600" style="min-width:200px">Description</th>
                            <th class="xls-th text-emerald-600" style="min-width:140px">Classification</th>
                            <th class="xls-th text-emerald-600" style="min-width:160px">Nature of Occupancy</th>
                            <th class="xls-th text-emerald-600" style="min-width:160px">Location</th>
                            <th class="xls-th text-emerald-600" style="min-width:140px">Date Constructed</th>
                            <th class="xls-th text-emerald-600" style="min-width:140px">Issuance Date</th>
                            <th class="xls-th text-emerald-600" style="min-width:150px">Property No.</th>
                            <th class="xls-th text-emerald-600 text-right" style="min-width:140px">Acquisition Cost (&#8369;)</th>
                            <th class="xls-th text-emerald-600 text-right" style="min-width:120px">Useful Life (yrs)</th>
                            <th class="xls-th text-emerald-600" style="min-width:200px">Remarks</th>
                        </tr>
                    </thead>
                    <tbody id="bldgTableBody"></tbody>
                </table>
            </div>
        </div>'''

c = c.replace(old_source_table, new_bldg_table)

# Replace Bulk Edit modal title
c = c.replace(
    '<h3 class="text-2xl font-black text-slate-800 dark:text-white uppercase tracking-tight italic text-blue-600">Bulk Edit Rows</h3>',
    '<h3 class="text-2xl font-black text-slate-800 dark:text-white uppercase tracking-tight italic text-emerald-600">Bulk Edit Rows</h3>'
)
c = c.replace('"px-6 py-3 rounded-xl text-sm font-black text-white bg-blue-600 hover:bg-blue-700 shadow-lg shadow-blue-500/30 transition-all">Apply Bulk Edit',
              '"px-6 py-3 rounded-xl text-sm font-black text-white bg-emerald-600 hover:bg-emerald-700 shadow-lg shadow-emerald-500/30 transition-all">Apply Bulk Edit')

# Replace entire JS section
old_js_start = '''<script>
    let bldgAllData = [];
    let bldgOriginalData = []; // Deep copy to check diffs
    let bldgUndoStack = [];
    let bldgRedoStack = [];
    let bldgCurrentPageNum = 1;
    const bldgRowsPerPage = 50;

    function toggleBldgFilters() {
        const section = document.getElementById('bldgFilterSection');
        const btn = document.getElementById('toggleBldgFilterBtn');
        const srcScroll = document.getElementById('bldgScroll');
        const distScroll = document.getElementById('bldgDistScroll');'''

new_js_start = '''<script>
    let bldgAllData = [];
    let bldgOriginalData = [];
    let bldgUndoStack = [];
    let bldgRedoStack = [];
    let bldgCurrentPageNum = 1;
    const bldgRowsPerPage = 50;

    function toggleBldgFilters() {
        const section = document.getElementById('bldgFilterSection');
        const btn = document.getElementById('toggleBldgFilterBtn');
        const srcScroll = document.getElementById('bldgScroll');
        const distScroll = srcScroll;'''

c = c.replace(old_js_start, new_js_start)

# Fix initBldgEdit to use building filters API
old_init = '''    function initBldgEdit() {
        // Fetch filters on load
        fetch('{{ route("api.reports.filters") }}?report_type=ALL')
            .then(res => res.json())
            .then(data => {
                populateEditSelect('bldgFilterClass', data.classifications);
                populateEditSelect('bldgFilterCat', data.categories);
                populateEditSelect('bldgFilterArticle', data.items);
                populateEditSelect('bldgFilterSchool', data.schools);
                populateEditSelect('bldgFilterOccupancy', data.sources);
                populateEditSelect('bldgFilterType', data.modes);
            });
    }'''

new_init = '''    function initBldgEdit() {
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
    }'''

c = c.replace(old_init, new_init)

# Fix clearBldgFilters filter IDs
c = c.replace(
    "['bldgFilterClass', 'bldgFilterCat', 'bldgFilterArticle', 'bldgFilterSort', 'bldgFilterSchool', 'bldgFilterOccupancy', 'bldgFilterType', 'bldgFilterDate', 'bldgFilterIntegrity']",
    "['bldgFilterClass', 'bldgFilterCat', 'bldgFilterArticle', 'bldgFilterSort', 'bldgFilterSchool', 'bldgFilterOccupancy', 'bldgFilterDate', 'bldgFilterIntegrity']"
)

# Fix bldgFetchData to use buildings API
old_fetch = """    function bldgFetchData() {
        const filterIds = {
            'bldgFilterClass': 'classification',
            'bldgFilterCat': 'category',
            'bldgFilterArticle': 'article',
            'bldgFilterSchool': 'schoolName',
            'bldgFilterOccupancy': 'source',
            'bldgFilterType': 'mode',
            'bldgFilterDate': 'dateAcquired',
            'bldgFilterIntegrity': 'emptyCol',
            'bldgFilterSort': 'sortCost'
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
            bldgAllData = data.rows || [];
            bldgOriginalData = JSON.parse(JSON.stringify(bldgAllData));
            bldgCurrentPageNum = 1;
            bldgUndoStack = [];
            bldgRedoStack = [];
            updateBldgUndoBtn();
            renderBldgTable();
            if (bldgAllData.length === 0) {
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
    }"""

new_fetch = """    function bldgFetchData() {
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

        fetch('{{ route("api.buildings.edit_preview") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ filters: filters })
        })
        .then(res => res.json())
        .then(data => {
            bldgAllData = data.rows || [];
            bldgOriginalData = JSON.parse(JSON.stringify(bldgAllData));
            bldgCurrentPageNum = 1;
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
        });
    }"""

c = c.replace(old_fetch, new_fetch)

# Fix switchBldgTab (no longer needed but keep for safety - make it a no-op)
old_switch = """    function switchBldgTab(tab) {
        const srcPanel = document.getElementById('bldgPanel');
        const distPanel = document.getElementById('bldgPanelDist');
        const tabSrc   = document.getElementById('bldgTabSource');
        const tabDst   = document.getElementById('bldgTabDist');
        const label    = document.getElementById('bldgTabLabel');
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
    }"""
c = c.replace(old_switch, '    function switchBldgTab(tab) { /* buildings use single table */ }')

with open(dst, 'w', encoding='utf-8') as f:
    f.write(c)
print('Step 3 done')
