import re

with open('resources/views/partials/building-edit-step.blade.php', 'r', encoding='utf-8') as f:
    c = f.read()

# Fix the missed rename: editTotalPages -> bldgTotalPages
c = c.replace('id="editTotalPages"', 'id="bldgTotalPages"')

# Check for any remaining stale edit* element IDs
remaining = re.findall(r'id="edit[A-Za-z]+"', c)
print('Remaining edit* IDs after fix:', remaining)

# Also fix the variable name collision: rename JS var bldgCurrentPageNum to bldgPageNum
# to avoid conflict with the HTML element id="bldgCurrentPageNum"
c = c.replace('let bldgCurrentPageNum = 1;', 'let bldgPageNum = 1;')
c = c.replace('bldgCurrentPageNum = 1;', 'bldgPageNum = 1;')
c = c.replace('bldgCurrentPageNum--', 'bldgPageNum--')
c = c.replace('bldgCurrentPageNum++', 'bldgPageNum++')
# In renderBldgTable - the JS variable references
c = c.replace('(bldgCurrentPageNum - 1) * bldgRowsPerPage', '(bldgPageNum - 1) * bldgRowsPerPage')
c = c.replace('bldgCurrentPageNum === 1', 'bldgPageNum === 1')
c = c.replace('bldgCurrentPageNum === totalPages', 'bldgPageNum === totalPages')
c = c.replace("document.getElementById('bldgCurrentPageNum').textContent = bldgCurrentPageNum;", "document.getElementById('bldgCurrentPageNum').textContent = bldgPageNum;")
# In bldgFetchData reset
c = c.replace('bldgPageNum = 1;\n            bldgUndoStack', 'bldgPageNum = 1;\n        bldgUndoStack')

# In prevPage/nextPage
c = c.replace('if (bldgPageNum > 1) { bldgPageNum--', 'if (bldgPageNum > 1) { bldgPageNum--')
c = c.replace('if (bldgPageNum < t) { bldgPageNum++', 'if (bldgPageNum < t) { bldgPageNum++')

with open('resources/views/partials/building-edit-step.blade.php', 'w', encoding='utf-8') as f:
    f.write(c)

print('Fixed.')
