import re

with open('resources/views/partials/inventory-edit-step.blade.php', 'r', encoding='utf-8') as f:
    lines = f.readlines()

start = -1
for i, line in enumerate(lines):
    if 'const dstTr = document.createElement' in line:
        start = i
        break

if start != -1:
    print(''.join(lines[start:start+70]))
else:
    print('Not found')
