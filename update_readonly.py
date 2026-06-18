import re

with open('resources/views/partials/inventory-edit-step.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

fields = [
    'ebEmployeeId', 'ebEmployeeName', 'ebEmployeePos', 'ebEmployeeStatus',
    'ebSchoolId', 'ebSchoolType', 'ebSchoolName', 'ebLocation'
]

for field in fields:
    content = re.sub(rf'(<input type="text" id="{field}"[^>]*class=")([^"]*)(")', r'\1\2 edit-readonly cursor-not-allowed"\3 readonly disabled="disabled"', content)

with open('resources/views/partials/inventory-edit-step.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)
