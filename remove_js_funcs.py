import re
import os

filepath = r"c:\Users\Axxer\Documents\DepEd_ZC - Inventory System\DepEd-ZC-Inventory-System-main\resources\views\inventory-setup.blade.php"

with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

funcs_to_remove = [
    'confirmSchoolSubmit', 'checkCategoryDuplicate', 'toggleCategoryDropdown',
    'selectExistingCategory', 'confirmCategorySubmit', 'filterPreDistSchools',
    'addPreDistSchool', 'removePreDistSchool', 'checkPreDistLimit',
    'renderPreSelectedSchools', 'proceedToDistributionTabs', 'backToPreSelectionPhase',
    'renderTabsUI', 'switchTab', 'filterTabCat', 'selectTabCat', 'filterTabItem',
    'selectTabItem', 'getEffectiveStock', 'filterTabSub', 'selectTabSub', 'removeTabSub',
    'changeSubItemSource', 'updateTabSubQty', 'refreshAllTabsForSubItem', 'renderTabSubItems',
    'updateReadyStatus', 'validatePayloadForTab', 'confirmDistributeSingleTab',
    'confirmDistributeAll', 'submitDistributionPayload', 'distToggleSource', 'distFilterSchools',
    'distSelectSchool', 'distRemoveRecipient', 'distUpdateCount', 'distProceedToAssign',
    'distGoBackToRegistry', 'distFilterExternal', 'distSelectExternal', 'distFilterPersonnel',
    'distSelectPersonnel'
]

# This regex matches the function declaration, arguments, and then matches the balanced braces block
# Note: we need a proper parser to handle nested braces for functions, regex is tricky.
# Let's write a simple brace-counting parser.

lines = content.splitlines()
new_lines = []
in_func = False
brace_count = 0

for line in lines:
    if not in_func:
        # Check if line starts one of our functions
        match = re.search(r'function\s+(' + '|'.join(funcs_to_remove) + r')\s*\(', line)
        if match:
            in_func = True
            brace_count = line.count('{') - line.count('}')
            continue
        new_lines.append(line)
    else:
        brace_count += line.count('{') - line.count('}')
        if brace_count <= 0:
            in_func = False

with open(filepath, 'w', encoding='utf-8') as f:
    f.write('\n'.join(new_lines))

print("Removed specified functions.")
