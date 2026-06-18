with open('resources/views/partials/inventory-edit-step.blade.php', 'r', encoding='utf-8') as f:
    lines = f.readlines()

# Find bulk edit modal script section
for i, line in enumerate(lines):
    if 'filterBulkEditClassDropdown' in line or 'filterBulkEditCatDropdown' in line or 'filterEditBulkEmpDropdown' in line or 'selectBulkEditClass' in line or 'selectEditBulkEmp' in line:
        print(f"Line {i+1}: {line.rstrip()}")
