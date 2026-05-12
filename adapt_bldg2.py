dst = 'resources/views/partials/building-edit-step.blade.php'
with open(dst, 'r', encoding='utf-8') as f:
    c = f.read()

# Title/subtitle
c = c.replace('text-blue-600">Inventory <span class="text-slate-900">Editor</span>', 'text-emerald-600">Infrastructure <span class="text-slate-900">Management</span>')
c = c.replace('Bulk update master inventory records', 'Bulk update building and facility records')

# Filter hide button color
c = c.replace('hover:border-blue-600 transition-all flex items-center gap-2 active:scale-95 shadow-sm italic">\n                <svg xmlns="http://www.w3.org/2000/svg"', 'hover:border-emerald-600 transition-all flex items-center gap-2 active:scale-95 shadow-sm italic">\n                <svg xmlns="http://www.w3.org/2000/svg"', 1)

# Row 1 filters - replace labels and selects
c = c.replace(
    '<label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Category</label>\n                <select id="bldgFilterCat" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all text-slate-500">\n                    <option value="">All Categories</option>\n                </select>',
    '<label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Office/School Type</label>\n                <select id="bldgFilterCat" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-emerald-50 focus:border-emerald-500 transition-all text-slate-500">\n                    <option value="">All Types</option>\n                </select>'
)
c = c.replace(
    '<label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Item</label>\n                <select id="bldgFilterArticle" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all text-slate-500">\n                    <option value="">All Items</option>\n                </select>',
    '<label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Article</label>\n                <select id="bldgFilterArticle" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-emerald-50 focus:border-emerald-500 transition-all text-slate-500">\n                    <option value="">All Articles</option>\n                </select>'
)
c = c.replace(
    '<label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Classification</label>\n                <select id="bldgFilterClass" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all text-slate-500">\n                    <option value="">All Classifications</option>\n                </select>',
    '<label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Classification</label>\n                <select id="bldgFilterClass" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-emerald-50 focus:border-emerald-500 transition-all text-slate-500">\n                    <option value="">All Classifications</option>\n                </select>'
)
c = c.replace(
    '<label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Cost Sorting</label>\n                <select id="bldgFilterSort" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all text-slate-500">',
    '<label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Cost Sorting</label>\n                <select id="bldgFilterSort" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-emerald-50 focus:border-emerald-500 transition-all text-slate-500">'
)

# Row 2 filters
c = c.replace(
    '<label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">School Name</label>\n                <select id="bldgFilterSchool" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all text-slate-500">\n                    <option value="">All Schools</option>\n                </select>',
    '<label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">School Name</label>\n                <select id="bldgFilterSchool" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-emerald-50 focus:border-emerald-500 transition-all text-slate-500">\n                    <option value="">All Schools</option>\n                </select>'
)
c = c.replace(
    '<label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Source of Acquisition</label>\n                <select id="bldgFilterOccupancy" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all text-slate-500">\n                    <option value="">All Sources</option>\n                </select>',
    '<label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Nature of Occupancy</label>\n                <select id="bldgFilterOccupancy" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-emerald-50 focus:border-emerald-500 transition-all text-slate-500">\n                    <option value="">All Occupancies</option>\n                </select>'
)
c = c.replace(
    '<label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Mode of Acquisition</label>\n                <select id="bldgFilterType" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all text-slate-500">\n                    <option value="">All Modes</option>\n                </select>',
    '<label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Date Constructed</label>\n                <input type="date" id="bldgFilterDate" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold focus:ring-4 focus:ring-emerald-50 focus:border-emerald-500 transition-all text-slate-500">'
)
c = c.replace(
    '<label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Date Acquired (Acceptance)</label>\n                <input type="date" id="bldgFilterDate" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all text-slate-500">',
    '<label class="text-[9px] font-black text-red-500 uppercase tracking-widest mb-2 block italic">Data Integrity (Empty Fields)</label>\n                <select id="bldgFilterIntegrity" class="w-full bg-slate-50 border border-red-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-500">\n                    <option value="">No Integrity Filter</option>\n                    <option value="classification">Missing Classification</option>\n                    <option value="article">Missing Article</option>\n                    <option value="description">Missing Description</option>\n                    <option value="office_name">Missing School Name</option>\n                    <option value="property_number">Missing Property Number</option>\n                    <option value="acquisition_cost">Missing Acquisition Cost</option>\n                    <option value="date_constructed">Missing Date Constructed</option>\n                </select>'
)

# Remove old integrity filter block
old_integrity = '''            <div>
                <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic text-red-500">Data Integrity (Empty Fields)</label>
                <select id="bldgFilterIntegrity" class="w-full bg-slate-50 border-red-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-red-500 transition-all text-slate-500">
                    <option value="">No Integrity Filter</option>
                    <option value="article">Missing Article/Item</option>
                    <option value="category">Missing Category</option>
                    <option value="classification">Missing Classification</option>
                    <option value="description">Missing Description</option>
                    <option value="property_number">Missing Property Number</option>
                    <option value="unit_of_measurement">Missing Unit (UOM)</option>
                    <option value="acq_source">Missing Acquisition Source</option>
                    <option value="mode_of_acquisition">Missing Mode</option>
                    <option value="acceptance_date">Missing Acceptance Date</option>
                    <option value="school_id">Missing School ID</option>
                    <option value="school_name">Missing School Name</option>
                    <option value="occupancy">Missing Nature of Occupancy</option>
                    <option value="location">Missing Location</option>
                    <option value="acquisition_date">Missing Acquisition Date</option>
                </select>
            </div>'''
c = c.replace(old_integrity, '')

# Action button colors
c = c.replace('hover:text-blue-600 transition-all italic">Clear All Filters', 'hover:text-emerald-600 transition-all italic">Clear All Filters')
c = c.replace('"px-8 py-2.5 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-600 transition-all active:scale-95 shadow-lg shadow-slate-200 italic">Apply Configuration', '"px-8 py-2.5 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-emerald-600 transition-all active:scale-95 shadow-lg shadow-slate-200 italic">Apply Configuration')

# Toolbar: remove asset source/dist tabs, use single label
old_toolbar_tabs = '''<div class="flex bg-slate-200/50 rounded-xl p-1 gap-1">
                    <button id="bldgTabSource" onclick="switchBldgTab(\'source\')"
                        class="px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg bg-blue-600 text-white shadow-sm transition-all">
                        Asset Source
                    </button>
                    <button id="bldgTabDist" onclick="switchBldgTab(\'distribution\')"
                        class="px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg text-slate-500 hover:text-slate-700 transition-all">
                        Asset Distribution
                    </button>
                </div>
                <span id="bldgTabLabel" class="hidden md:block text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">Asset Source</span>'''
new_toolbar_tabs = '<span class="hidden md:block text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">Building Records</span>'
c = c.replace(old_toolbar_tabs, new_toolbar_tabs)

# Bulk edit button color
c = c.replace(
    '"px-5 py-2.5 bg-blue-50 text-blue-600 rounded-xl text-[9px] font-black uppercase tracking-widest flex items-center gap-2 shadow-sm hover:bg-blue-100 transition-all active:scale-95 italic border border-blue-100"',
    '"px-5 py-2.5 bg-emerald-50 text-emerald-600 rounded-xl text-[9px] font-black uppercase tracking-widest flex items-center gap-2 shadow-sm hover:bg-emerald-100 transition-all active:scale-95 italic border border-emerald-100"'
)

with open(dst, 'w', encoding='utf-8') as f:
    f.write(c)
print('Step 2 done')
