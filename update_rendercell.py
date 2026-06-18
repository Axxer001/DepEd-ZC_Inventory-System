import re

with open('resources/views/partials/inventory-edit-step.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update renderCell definition to actually use isReadonly
renderCell_old = '''            const renderCell = (col, val, isReadonly = false) => {
                let safeVal = (val || '').toString().replace(/"/g, '&quot;');
                let badgeHtml = '';
                
                // Track unsaved edits
                const changed = editOriginalData.find(o => o.dist_id === row.dist_id);
                if (changed && changed[col] !== undefined && String(changed[col]) !== String(val)) {
                    badgeHtml = '<span class="update-badge">EDITED</span>';
                }

                if (col === 'acq_source') {
                    const val1 = safeVal || '';
                    const isNew = !globalAcqSources.some(s => s.name.toLowerCase() === val1.toLowerCase()) && val1 !== '';
                    return <td class="xls-td p-0 relative" style="overflow:visible">
                        <input type="text" data-id="" data-col="acq_source" value="" oninput="syncEditAcqSource(, this.value); filterEditAcqSourceDropdown(, this.value)" onfocus="filterEditAcqSourceDropdown(, this.value)" autocomplete="off" class="xls-input w-full h-full bg-transparent pr-10">
                        <div id="edit-acq-source-dd-" class="xls-custom-dd" style="display:none; width: 100%;"></div>
                        <span id="edit-acq-source-badge-" class="absolute right-2 top-1/2 -translate-y-1/2 px-1 py-0.5 text-[7px] font-extrabold uppercase bg-blue-600 text-white rounded tracking-wider leading-none ">NEW</span>
                        
                    </td>;
                }

                if (col === 'classification') {
                    return <td class="xls-td p-0 relative" style="overflow:visible">
                        <input type="text" data-id="" data-col="classification" value="" oninput="syncEditClass(, this.value); filterEditClassDropdown(, this.value)" onfocus="filterEditClassDropdown(, this.value)" autocomplete="off" class="xls-input w-full h-full bg-transparent">
                        <div id="edit-class-dd-" class="xls-custom-dd" style="display:none; width: 100%;"></div>
                        
                    </td>;
                }

                if (col === 'category') {
                    return <td class="xls-td p-0 relative" style="overflow:visible">
                        <input type="text" data-id="" data-col="category" value="" oninput="syncEditCat(, this.value); filterEditCatDropdown(, this.value)" onfocus="filterEditCatDropdown(, this.value)" autocomplete="off" class="xls-input w-full h-full bg-transparent">
                        <div id="edit-cat-dd-" class="xls-custom-dd" style="display:none; width: 100%;"></div>
                        
                    </td>;
                }

                if (col === 'source_personnel') {
                    return <td class="xls-td p-0 relative" style="overflow:visible">
                        <input type="text" data-id="" data-col="source_personnel" value="" oninput="syncEditPersonnel(, this.value); filterEditContactDropdown(, this.value)" onfocus="filterEditContactDropdown(, this.value)" autocomplete="off" class="xls-input w-full h-full bg-transparent" placeholder="Search Personnel...">
                        <div id="edit-contact-dd-" class="xls-custom-dd" style="display:none; width: 100%;"></div>
                        
                    </td>;
                }

                if (col === 'remarks') {
                    return <td class="xls-td p-0 relative">
                        <select data-id="" data-col="" onchange="syncEditCell(this)" class="xls-input w-full h-full bg-transparent">
                            <option value="Good Condition" >Good Condition</option>
                            <option value="Needs Repair" >Needs Repair</option>
                            <option value="Not Useable" >Not Useable</option>
                        </select>
                        
                    </td>;
                }
                
                let extraClasses = '';
                if (['asset_cost', 'quantity', 'estimated_useful_life'].includes(col)) {
                    extraClasses = ' text-right font-mono';
                }
                return <td class="xls-td p-0 relative"><input type="text" data-id="" data-col="" value="" onchange="syncEditCell(this)" class="xls-input w-full h-full bg-transparent"></td>;
            };'''

renderCell_new = '''            const renderCell = (col, val, isReadonly = false) => {
                let safeVal = (val || '').toString().replace(/"/g, '&quot;');
                let badgeHtml = '';
                
                const changed = editOriginalData.find(o => o.dist_id === row.dist_id);
                if (changed && changed[col] !== undefined && String(changed[col]) !== String(val)) {
                    badgeHtml = '<span class="update-badge">EDITED</span>';
                }

                const readonlyAttrs = isReadonly ? 'readonly disabled="disabled"' : '';
                const readonlyClass = isReadonly ? 'edit-readonly cursor-not-allowed' : 'bg-transparent';
                const inputClass = xls-input w-full h-full ;

                if (col === 'classification' && !isReadonly) {
                    return <td class="xls-td p-0 relative" style="overflow:visible">
                        <input type="text" data-id="" data-col="classification" value="" oninput="syncEditClass(, this.value); filterEditClassDropdown(, this.value)" onfocus="filterEditClassDropdown(, this.value)" autocomplete="off" class="" >
                        <div id="edit-class-dd-" class="xls-custom-dd" style="display:none; width: 100%;"></div>
                        
                    </td>;
                }

                if (col === 'category' && !isReadonly) {
                    return <td class="xls-td p-0 relative" style="overflow:visible">
                        <input type="text" data-id="" data-col="category" value="" oninput="syncEditCat(, this.value); filterEditCatDropdown(, this.value)" onfocus="filterEditCatDropdown(, this.value)" autocomplete="off" class="" >
                        <div id="edit-cat-dd-" class="xls-custom-dd" style="display:none; width: 100%;"></div>
                        
                    </td>;
                }

                if (col === 'remarks' && !isReadonly) {
                    return <td class="xls-td p-0 relative">
                        <select data-id="" data-col="" onchange="syncEditCell(this)" class="" >
                            <option value="Good Condition" >Good Condition</option>
                            <option value="Needs Repair" >Needs Repair</option>
                            <option value="Not Useable" >Not Useable</option>
                        </select>
                        
                    </td>;
                }
                
                let extraClasses = '';
                if (['asset_cost', 'quantity', 'estimated_useful_life'].includes(col)) {
                    extraClasses = ' text-right font-mono';
                }
                return <td class="xls-td p-0 relative"><input type="text" data-id="" data-col="" value="" onchange="syncEditCell(this)" class="" ></td>;
            };'''

# Execute renderCell replace using regex since the old might be slightly modified
content = re.sub(r'const renderCell = \(col, val, isReadonly = false\) => \{.*?(?=\s*// Source Table Row)', renderCell_new + '\n\n', content, flags=re.DOTALL)

# 2. Update srcTr renderCell calls
srcTr_old = '''                
                
                
                
                
                
                
                
                
                
                
                
                
                '''

srcTr_new = '''                
                
                
                
                
                
                
                
                
                
                
                
                
                '''

content = content.replace(srcTr_old, srcTr_new)

# 3. Update dstTr renderCell calls (property_number and acquisition_date)
dstTr_old = '''                
                <td class="xls-td p-0 relative"><input type="text" class="xls-input edit-readonly text-right w-full h-full" value="" readonly tabindex="-1"></td>
                '''

dstTr_new = '''                
                <td class="xls-td p-0 relative"><input type="text" class="xls-input edit-readonly text-right w-full h-full" value="" readonly tabindex="-1"></td>
                '''

content = content.replace(dstTr_old, dstTr_new)

with open('resources/views/partials/inventory-edit-step.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("renderCell updated")
