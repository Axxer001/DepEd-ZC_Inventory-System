import re

blade_path = r'resources/views/inventory-setup.blade.php'

with open(blade_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace the HTML block in renderForm (the one around line 551-565)
old_block_1 = """                                    <div id="subItemContainer" class="space-y-3 max-h-[250px] overflow-y-auto pr-2 custom-scroll">
                                        <div class="flex gap-2 group sub-item-row relative">
                                            <select name="sub_item_distributors[]" class="w-40 p-4 flex-shrink-0 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-sm cursor-pointer" title="Distributor">
                                                ${getDistributorOptionsHtml()}
                                            </select>
                                            <input type="text" name="sub_items[]" placeholder="e.g. Default/General or RAM 8GB" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-sm flex-1" required autocomplete="off" oninput="checkSubItemDuplicate(this)">
                                            <input type="number" name="sub_item_quantities[]" placeholder="Qty" min="1" step="1" class="w-20 p-4 flex-shrink-0 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-sm text-center" required>
                                            <select name="sub_item_conditions[]" class="w-36 p-4 flex-shrink-0 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-sm cursor-pointer" title="Condition">
                                                <option value="Serviceable" selected>Serviceable</option>
                                                <option value="Unserviceable">Unserviceable</option>
                                                <option value="For Repair">For Repair</option>
                                            </select>
                                            <button type="button" onclick="removeSubItemField(this)" class="px-3 text-slate-300 hover:text-red-500 font-bold transition-colors">✕</button>
                                        </div>
                                    </div>"""

new_block_1 = """                                    <div id="subItemContainer" class="space-y-3 max-h-[400px] overflow-y-auto pr-2 custom-scroll">
                                        <div class="flex flex-col gap-1 group sub-item-row relative border border-slate-100 rounded-2xl p-3 bg-slate-50/50">
                                            <div class="flex gap-2">
                                                <select name="sub_item_distributors[]" class="w-40 p-3 flex-shrink-0 bg-white border border-slate-100 rounded-xl outline-none font-semibold text-xs cursor-pointer" title="Distributor">
                                                    ${getDistributorOptionsHtml()}
                                                </select>
                                                <input type="text" name="sub_items[]" placeholder="e.g. Default/General or RAM 8GB" class="w-full p-3 bg-white border border-slate-100 rounded-xl outline-none font-semibold text-sm flex-1" required autocomplete="off" oninput="checkSubItemDuplicate(this)">
                                                <input type="number" name="sub_item_quantities[]" placeholder="Qty" min="1" step="1" class="w-20 p-3 flex-shrink-0 bg-white border border-slate-100 rounded-xl outline-none font-semibold text-sm text-center" required>
                                                <select name="sub_item_conditions[]" class="w-34 p-3 flex-shrink-0 bg-white border border-slate-100 rounded-xl outline-none font-semibold text-xs cursor-pointer" title="Condition">
                                                    <option value="Serviceable" selected>Serviceable</option>
                                                    <option value="Unserviceable">Unserviceable</option>
                                                    <option value="For Repair">For Repair</option>
                                                </select>
                                                <button type="button" onclick="removeSubItemField(this)" class="px-3 text-slate-300 hover:text-red-500 font-bold transition-colors">✕</button>
                                            </div>
                                            <div class="flex gap-2 items-center">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">₱ Price</span>
                                                    <input type="number" name="sub_item_prices[]" placeholder="Unit price" min="0" step="0.01" class="w-28 p-2.5 bg-white border border-slate-100 rounded-xl outline-none font-semibold text-xs text-center">
                                                </div>
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">📅 Acquired</span>
                                                    <input type="date" name="sub_item_dates[]" class="p-2.5 bg-white border border-slate-100 rounded-xl outline-none font-semibold text-xs">
                                                </div>
                                                <button type="button" onclick="toggleSerialPanel(this)" class="ml-auto px-3 py-1.5 text-[10px] font-black bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-100 hover:text-[#c00000] transition-all uppercase tracking-wider">⚙ Serial Info</button>
                                            </div>
                                            <div class="serial-panel hidden flex gap-2 pt-2 border-t border-slate-100">
                                                <label class="flex items-center gap-2 text-xs font-bold text-slate-600 cursor-pointer">
                                                    <input type="checkbox" name="sub_item_serialized[]" value="1" class="w-4 h-4 accent-[#c00000]" onchange="toggleSerializedFields(this)"> Serialized Asset (Qty locked to 1)
                                                </label>
                                                <input type="text" name="sub_item_property_numbers[]" placeholder="Property No." class="serial-field hidden flex-1 p-2.5 bg-white border border-slate-100 rounded-xl outline-none font-semibold text-xs">
                                                <input type="text" name="sub_item_serial_numbers[]" placeholder="Serial No." class="serial-field hidden flex-1 p-2.5 bg-white border border-slate-100 rounded-xl outline-none font-semibold text-xs">
                                            </div>
                                        </div>
                                    </div>"""

# Replace the HTML block in addSubItemField (around line 686-698)
old_block_2 = """            div.innerHTML = `
                <select name="sub_item_distributors[]" class="w-40 p-4 flex-shrink-0 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-sm cursor-pointer" title="Distributor">
                    ${getDistributorOptionsHtml()}
                </select>
                <input type="text" name="sub_items[]" placeholder="Enter specification" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-sm flex-1" required autocomplete="off" oninput="checkSubItemDuplicate(this)">
                <input type="number" name="sub_item_quantities[]" placeholder="Qty" min="1" step="1" class="w-20 p-4 flex-shrink-0 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-sm text-center" required>
                <select name="sub_item_conditions[]" class="w-36 p-4 flex-shrink-0 bg-slate-50 border border-slate-100 rounded-2xl outline-none font-semibold text-sm cursor-pointer" title="Condition">
                    <option value="Serviceable" selected>Serviceable</option>
                    <option value="Unserviceable">Unserviceable</option>
                    <option value="For Repair">For Repair</option>
                </select>
                <button type="button" onclick="removeSubItemField(this)" class="px-3 text-slate-300 hover:text-red-500 font-bold transition-colors">✕</button>
            `;"""
            
new_block_2 = """            div.className = "flex flex-col gap-1 group animate-in fade-in slide-in-from-top-2 duration-300 sub-item-row relative border border-slate-100 rounded-2xl p-3 bg-slate-50/50";
            div.innerHTML = `
                <div class="flex gap-2">
                    <select name="sub_item_distributors[]" class="w-40 p-3 flex-shrink-0 bg-white border border-slate-100 rounded-xl outline-none font-semibold text-xs cursor-pointer" title="Distributor">
                        ${getDistributorOptionsHtml()}
                    </select>
                    <input type="text" name="sub_items[]" placeholder="Enter specification" class="w-full p-3 bg-white border border-slate-100 rounded-xl outline-none font-semibold text-sm flex-1" required autocomplete="off" oninput="checkSubItemDuplicate(this)">
                    <input type="number" name="sub_item_quantities[]" placeholder="Qty" min="1" step="1" class="w-20 p-3 flex-shrink-0 bg-white border border-slate-100 rounded-xl outline-none font-semibold text-sm text-center" required>
                    <select name="sub_item_conditions[]" class="w-34 p-3 flex-shrink-0 bg-white border border-slate-100 rounded-xl outline-none font-semibold text-xs cursor-pointer" title="Condition">
                        <option value="Serviceable" selected>Serviceable</option>
                        <option value="Unserviceable">Unserviceable</option>
                        <option value="For Repair">For Repair</option>
                    </select>
                    <button type="button" onclick="removeSubItemField(this)" class="px-3 text-slate-300 hover:text-red-500 font-bold transition-colors">✕</button>
                </div>
                <div class="flex gap-2 items-center">
                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">₱ Price</span>
                        <input type="number" name="sub_item_prices[]" placeholder="Unit price" min="0" step="0.01" class="w-28 p-2.5 bg-white border border-slate-100 rounded-xl outline-none font-semibold text-xs text-center">
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">📅 Acquired</span>
                        <input type="date" name="sub_item_dates[]" class="p-2.5 bg-white border border-slate-100 rounded-xl outline-none font-semibold text-xs">
                    </div>
                    <button type="button" onclick="toggleSerialPanel(this)" class="ml-auto px-3 py-1.5 text-[10px] font-black bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-100 hover:text-[#c00000] transition-all uppercase tracking-wider">⚙ Serial Info</button>
                </div>
                <div class="serial-panel hidden flex gap-2 pt-2 border-t border-slate-100">
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-600 cursor-pointer">
                        <input type="checkbox" name="sub_item_serialized[]" value="1" class="w-4 h-4 accent-[#c00000]" onchange="toggleSerializedFields(this)"> Serialized Asset (Qty locked to 1)
                    </label>
                    <input type="text" name="sub_item_property_numbers[]" placeholder="Property No." class="serial-field hidden flex-1 p-2.5 bg-white border border-slate-100 rounded-xl outline-none font-semibold text-xs">
                    <input type="text" name="sub_item_serial_numbers[]" placeholder="Serial No." class="serial-field hidden flex-1 p-2.5 bg-white border border-slate-100 rounded-xl outline-none font-semibold text-xs">
                </div>
            `;"""
            
new_content = content.replace(old_block_1, new_block_1)

# we also need to replace `div.className = "flex gap-2 group animate-in fade-in slide-in-from-top-2 duration-300 sub-item-row relative";` in block 2's context
start_block_2 = """            div.className = "flex gap-2 group animate-in fade-in slide-in-from-top-2 duration-300 sub-item-row relative";\n""" + old_block_2

new_content = new_content.replace(start_block_2, new_block_2)

# Check if successful
if new_content != content:
    with open(blade_path, 'w', encoding='utf-8') as f:
        f.write(new_content)
    print("PATCH SUCCESSFUL")
else:
    print("PATCH FAILED. No replacements made. Please check strings.")
