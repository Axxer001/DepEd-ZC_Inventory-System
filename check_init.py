with open('resources/views/partials/building-edit-step.blade.php', 'r', encoding='utf-8') as f:
    c = f.read()

# Check initBldgEdit function
idx = c.index('function initBldgEdit')
print('=== initBldgEdit ===')
print(c[idx:idx+500])

print()
# Check that bldgFetchData is called on Apply Configuration button
idx2 = c.index('Apply Configuration')
print('=== Apply Configuration button ===')
print(c[idx2-200:idx2+100])
