with open('resources/views/partials/building-edit-step.blade.php', 'r', encoding='utf-8') as f:
    c = f.read()

# Fix the prev/next page functions - conditions still use old variable name
c = c.replace(
    'function bldgPrevPage() { if (bldgCurrentPageNum > 1) { bldgPageNum--; renderBldgTable(); } }',
    'function bldgPrevPage() { if (bldgPageNum > 1) { bldgPageNum--; renderBldgTable(); } }'
)
c = c.replace(
    'function bldgNextPage() { const t = Math.ceil(bldgAllData.length/bldgRowsPerPage); if (bldgCurrentPageNum < t) { bldgPageNum++; renderBldgTable(); } }',
    'function bldgNextPage() { const t = Math.ceil(bldgAllData.length/bldgRowsPerPage); if (bldgPageNum < t) { bldgPageNum++; renderBldgTable(); } }'
)

# Also fix the in-fetch reset lines where bldgPageNum = 1 might have bad indentation
c = c.replace(
    "document.getElementById('bldgCurrentPageNum').textContent = 1;\n            document.getElementById('bldgTotalPages').textContent = 1;",
    "document.getElementById('bldgCurrentPageNum').textContent = 1;\n        document.getElementById('bldgTotalPages').textContent = 1;"
)

with open('resources/views/partials/building-edit-step.blade.php', 'w', encoding='utf-8') as f:
    f.write(c)
print('Prev/next page functions fixed')

# Verify
for line in c.split('\n'):
    if 'bldgPrevPage' in line or 'bldgNextPage' in line:
        print(line.strip())
