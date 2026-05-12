with open('resources/views/partials/building-edit-step.blade.php', 'r', encoding='utf-8') as f:
    c = f.read()

import re
spans = re.findall(r'<span id="[^"]*"', c)
print('Span IDs found:')
for s in spans:
    print(' ', s)

print()
print('bldgTotalPages in HTML:', 'id="bldgTotalPages"' in c)
print('bldgCurrentPageNum in HTML:', 'id="bldgCurrentPageNum"' in c)

# Check buildings table query in controller
with open('app/Http/Controllers/InventorySetupController.php', 'r', encoding='utf-8') as f:
    ctrl = f.read()
print()
print('getBuildingEditPreview in controller:', 'getBuildingEditPreview' in ctrl)
idx = ctrl.index('getBuildingEditPreview')
print(ctrl[idx:idx+300])
