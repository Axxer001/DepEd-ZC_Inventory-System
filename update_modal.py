import re

with open('resources/views/partials/inventory-edit-step.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update editBulkModal HTML
# Find the start of Edit Bulk Modal HTML
start_html = content.find('<!-- Bulk Edit Modal -->')
end_html = content.find('</div>\n</div>', start_html)
if start_html != -1 and end_html != -1:
    modal_html = content[start_html:end_html+12]
else:
    print('Failed to find bulk edit modal HTML')
    exit(1)

# Now apply replacements to modal_html:
# ebClassification: Add NEW badge
modal_html = modal_html.replace(
    '<label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Classification</label>',
    '<label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1 flex items-center gap-2"><span>Classification</span><span id="ebClassificationNewBadge" class="hidden px-1.5 py-0.5 text-[8px] font-extrabold uppercase bg-blue-600 text-white rounded tracking-wider leading-none">NEW</span></label>'
)
# ebCategory: Add NEW badge
modal_html = modal_html.replace(
    '<label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Category</label>',
    '<label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1 flex items-center gap-2"><span>Category</span><span id="ebCategoryNewBadge" class="hidden px-1.5 py-0.5 text-[8px] font-extrabold uppercase bg-blue-600 text-white rounded tracking-wider leading-none">NEW</span></label>'
)

# Disable Fields:
to_disable = [
    'id="ebUom"', 'id="ebMode"', 'id="ebPosition"', 'id="ebCost"', 
    'id="ebQty"', 'id="ebLife"', 'id="ebDate1"', 'id="ebPropertyNo"', 'id="ebDate2"'
]
for target in to_disable:
    modal_html = re.sub(rf'({target}[^>]*class="[^"]*)(")', r'\1 edit-readonly" readonly disabled="disabled"', modal_html)

# ebAcqSource & ebPersonnel (remove custom triggers and dd, and make disabled)
acq_source_block = re.search(r'<div class="relative col-personnel p-1 rounded-2xl" style="position:relative;overflow:visible">\s*<label[^>]*>.*?Acquisition Source.*?</label>\s*<input[^>]*id="ebAcqSource".*?>\s*<div id="edit-bulk-acq-source-dd" class="xls-custom-dd" style="display:none; width: 100%;"></div>\s*</div>', modal_html, re.DOTALL)
if acq_source_block:
    new_acq_source = '<div class="relative col-personnel p-1 rounded-2xl">\n                        <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Acquisition Source</label>\n                        <input type="text" id="ebAcqSource" class="xls-input !border border-slate-100 rounded-xl edit-readonly" placeholder="Cannot be modified" readonly disabled="disabled">\n                    </div>'
    modal_html = modal_html.replace(acq_source_block.group(0), new_acq_source)

personnel_block = re.search(r'<div class="relative col-personnel p-1 rounded-2xl">\s*<label[^>]*>Source Personnel</label>\s*<input[^>]*id="ebPersonnel".*?>\s*</div>', modal_html)
if personnel_block:
    new_personnel = '<div class="relative col-personnel p-1 rounded-2xl">\n                        <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Source Personnel</label>\n                        <input type="text" id="ebPersonnel" class="xls-input !border border-slate-100 rounded-xl edit-readonly" placeholder="Cannot be modified" readonly disabled="disabled">\n                    </div>'
    modal_html = modal_html.replace(personnel_block.group(0), new_personnel)

# Remove Nature of Occupancy
occupancy_block = re.search(r'<div class="relative col-context p-1 rounded-2xl"><label class="text-\[9px\] font-black text-slate-900 uppercase tracking-widest ml-1 block mb-1">Nature of Occupancy</label>\s*<input type="text" id="ebOccupancy"[^>]*>\s*</div>', modal_html)
if occupancy_block:
    modal_html = modal_html.replace(occupancy_block.group(0), '')

# Replace modal HTML in content
content = content[:start_html] + modal_html + content[end_html+12:]


with open('resources/views/partials/inventory-edit-step.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("Modal HTML updated successfully.")
