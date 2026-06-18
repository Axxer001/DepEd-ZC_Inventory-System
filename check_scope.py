with open('resources/views/partials/scripts/item-manager.blade.php', 'r', encoding='utf-8') as f:
    lines = f.readlines()

# Print lines 1-80 to see top-level scope
print(''.join(lines[0:80]))
