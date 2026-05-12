import re

with open('resources/views/partials/inventory-edit-step.blade.php', 'r') as f:
    content = f.read()

# Make basic renaming replacements
content = content.replace('Inventory <span class="text-slate-900">Editor</span>', 'Building <span class="text-slate-900">Editor</span>')
content = content.replace('Bulk update master inventory records', 'Bulk update master building records')
content = content.replace('id="stepInventoryEdit"', 'id="stepBuildingEdit"')

# Function names prefix from edit... to bldgEdit...
content = content.replace('toggleEditFilters', 'toggleBldgEditFilters')
content = content.replace('toggleEditFilterBtn', 'toggleBldgEditFilterBtn')
content = content.replace('editFilterSection', 'bldgEditFilterSection')
content = content.replace('clearEditFilters', 'clearBldgEditFilters')
content = content.replace('editFetchData', 'bldgEditFetchData')
content = content.replace('editAssetTableCard', 'bldgEditTableCard')
content = content.replace('editAssetToolbar', 'bldgEditToolbar')
content = content.replace('editUndoBtn', 'bldgEditUndoBtn')
content = content.replace('editRedoBtn', 'bldgEditRedoBtn')
content = content.replace('editUndo(', 'bldgEditUndo(')
content = content.replace('editRedo(', 'bldgEditRedo(')
content = content.replace('openEditBulkModal', 'openBldgEditBulkModal')
content = content.replace('closeEditBulkModal', 'closeBldgEditBulkModal')
content = content.replace('saveEditChanges', 'saveBldgEditChanges')
content = content.replace('applyEditBulk', 'applyBldgEditBulk')

# Replace the tabs - we don't need two tabs for buildings
content = re.sub(r'<div class="flex bg-slate-200/50.*?</div>', '', content, flags=re.DOTALL|re.IGNORECASE)
content = content.replace('<span id="editAssetTabLabel" class="hidden md:block text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">Asset Source</span>', '<span class="hidden md:block text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">Building Records</span>')

# Replace the Filter Section HTML entirely to match buildings
filter_html = """
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
"""
content = re.sub(r'<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-8 relative z-10">.*?<button onclick="bldgEditFetchData\(\)".*?</button>\s*</div>', filter_html, content, flags=re.DOTALL)

with open('scratch/generated.blade.php', 'w') as f:
    f.write(content)
