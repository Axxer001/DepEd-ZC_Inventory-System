import sys
filepath = r'C:\Users\Arween\OneDrive\Documents\DepEd_ZC - Inventory System\DepEd-ZC-Inventory-System-main\resources\views\inventory-modifier.blade.php'
with open(filepath, 'r', encoding='utf-8') as f:
    lines = f.readlines()

start_idx = -1
end_idx = -1
for i, line in enumerate(lines):
    if '{{-- Step 1: Add or Edit Selection --}}' in line:
        start_idx = i
    if '{{-- Step 3: Form Content --}}' in line:
        end_idx = i
        break

if start_idx != -1 and end_idx != -1:
    lines[end_idx + 1] = lines[end_idx + 1].replace('class="step-content"', 'class="step-content active"')
    del lines[start_idx:end_idx]

for i, line in enumerate(lines):
    if 'let history = [1];' in line:
        lines[i] = '        let history = [3];\n'
    elif "let currentMode = '';" in line:
        lines[i] = "        let currentMode = 'edit';\n"
    elif "let currentModule = '';" in line:
        lines[i] = "        let currentModule = 'distribution';\n        document.addEventListener('DOMContentLoaded', () => { renderForm(); });\n"
        break

start_rf = -1
end_rf = -1
for i, line in enumerate(lines):
    if 'function renderForm() {' in line:
        start_rf = i
    if start_rf != -1 and 'function addSubItemField() {' in line:
        end_rf = i
        break

if start_rf != -1 and end_rf != -1:
    new_rf = """        function renderForm() {
            const container = document.getElementById('formContent');
            const parentWrap = container.parentElement;
            
            parentWrap.classList.remove('max-w-2xl', 'overflow-hidden');
            parentWrap.classList.add('max-w-5xl', 'overflow-visible');

            let html = `<h4 class="text-2xl font-black text-slate-800 mb-8 uppercase tracking-tight italic">Asset Modifier</h4>`;

            html += `
                    <div id="distPreSelectionPhase" class="space-y-6 animate-in fade-in zoom-in duration-300">
                        <div class="text-center mb-8">
                            <h4 class="text-2xl font-black text-slate-800 uppercase tracking-tight italic">Step 1: Select Schools</h4>
                            <p class="text-slate-500 text-sm mt-2 font-medium">Select up to 6 schools to modify their asset distribution. You may select the same school multiple times.</p>
                        </div>
                        <div class="max-w-xl mx-auto space-y-4">
                            <div class="relative">
                                <input type="text" id="preDistSchoolSearch" placeholder="Type school name or ID..." class="w-full p-5 bg-slate-50 border border-slate-200 rounded-2xl outline-none font-bold text-slate-700 transition-all text-center focus:border-[#c00000] focus:ring-4 focus:ring-red-100" autocomplete="off" oninput="filterPreDistSchools()" onfocus="filterPreDistSchools()">
                                <div id="preDistSchoolDropdownList" class="hidden absolute z-30 w-full mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-[250px] overflow-y-auto custom-scroll"></div>
                            </div>
                            <div id="preDistSelectedSchoolsContainer" class="flex flex-col gap-2 mt-4 min-h-[50px]">
                                <span class="text-slate-400 text-xs font-bold italic w-full text-center mt-1 select-prompt">No schools selected yet.</span>
                            </div>
                            <p id="preDistLimitWarning" class="hidden text-center text-xs font-bold text-red-500 mt-2">⚠ Maximum of 6 schools reached.</p>
                            <button type="button" id="proceedDistBtn" onclick="proceedToDistributionTabs()" class="w-full mt-6 py-5 bg-slate-200 text-slate-400 rounded-3xl font-black uppercase tracking-widest cursor-not-allowed transition-all" disabled>Proceed to Modify Assets</button>
                        </div>
                    </div>
                    
                    <div id="distTabsPhase" class="hidden space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-6 gap-4">
                            <div>
                                <h4 class="text-2xl font-black text-slate-800 uppercase tracking-tight italic">Step 2: Modify Assets</h4>
                                <p class="text-slate-500 text-sm mt-1 font-medium">Add, subtract, or delete asset distributions per tab.</p>
                            </div>
                            <button type="button" onclick="backToPreSelectionPhase()" class="text-xs font-bold text-slate-400 hover:text-[#c00000] underline underline-offset-4 shrink-0 transition-colors bg-transparent border-0">« Revise Schools</button>
                        </div>
                        <div class="flex flex-col md:flex-row gap-6">
                            <div class="md:w-1/4 flex flex-col gap-2 border-r border-slate-100 pr-4 max-h-[500px] overflow-y-auto custom-scroll" id="distTabsHeader"></div>
                            <div id="distTabsContentContainer" class="md:w-3/4 min-h-[400px]"></div>
                        </div>

                        <div class="pt-6 border-t border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-4">
                            <span class="text-[10px] font-black tracking-widest uppercase text-slate-500 bg-slate-100 rounded-xl px-4 py-2" id="tabStatusCount">0 Assets Ready</span>
                            <button type="button" onclick="confirmDistributeAll()" id="distributeAllBtn" class="px-8 py-4 bg-[#c00000] hover:bg-red-700 text-white rounded-2xl font-black shadow-xl hover:-translate-y-1 active:scale-95 transition-all text-sm uppercase tracking-wider w-full sm:w-auto">Confirm Modifications</button>
                        </div>
                    </div>
                `;
            container.innerHTML = html;
            preSelectedSchools = [];
            distTabsData = [];
            currentActiveTab = 0;
            renderPreSelectedSchools();
        }
"""
    lines[start_rf:end_rf] = [new_rf]

for i, line in enumerate(lines):
    if "route('inventory.setup.distribution')" in line:
        lines[i] = line.replace("route('inventory.setup.distribution')", "route('inventory.modifier.distribution')")

with open(filepath, 'w', encoding='utf-8') as f:
    f.writelines(lines)
print('DONE!')
