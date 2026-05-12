import re

with open('resources/views/partials/building-edit-step.blade.php', 'r', encoding='utf-8') as f:
    c = f.read()

# Verify pagination JS is correct
print('=== Pagination JS check ===')
for line in c.split('\n'):
    if 'bldgPageNum' in line or 'bldgCurrentPageNum' in line or 'bldgTotalPages' in line:
        print(line.strip())

print()
print('=== Filter fetch check ===')
idx = c.index('function bldgFetchData')
print(c[idx:idx+600])
