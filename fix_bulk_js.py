path = r'resources\views\partials\import.blade.php'

with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

# ── Find and replace the two bulk autocomplete functions ──────────────────────
# We'll find them by their start/end markers and replace completely.

OLD_ITEM = content[content.index('// ── Bulk Item Autocomplete'):content.index('// ── Bulk Source Autocomplete')]
OLD_SRC  = content[content.index('// ── Bulk Source Autocomplete'):content.index('function applyBulkAdd')]

NEW_ITEM = r"""// -- Bulk Item Autocomplete ---------------------------------------------------
function bulkFilterItem() {
    const cat   = document.getElementById('bulkCat').value;
    const input = document.getElementById('bulkItemInput');
    const dd    = document.getElementById('bulkItemDrop');
    const val   = input.value.toLowerCase().trim();
    const pool  = (cat && rawItemsMap[cat]) ? rawItemsMap[cat] : Object.values(rawItemsMap).flat();
    const results = val ? pool.filter(i => i.toLowerCase().includes(val)) : pool;
    const unique  = [...new Set(results)].slice(0, 30);

    if (unique.length === 0 && !val) { dd.classList.add('hidden'); return; }

    let html = '';
    if (unique.length) {
        html += '<div class="px-3 py-1.5 text-[9px] font-black text-slate-400 uppercase tracking-widest sticky top-0 bg-white border-b border-slate-50">Items' + (cat ? ' under ' + cat : '') + '</div>';
        unique.forEach(function(i) {
            html += '<div class="px-4 py-2 text-xs font-bold hover:bg-red-50 hover:text-red-600 cursor-pointer border-b border-slate-50 last:border-0 bulk-item-opt" data-name="' + i.replace(/"/g, '&quot;') + '">' + i + '</div>';
        });
    }
    if (val && !pool.find(function(i) { return i.toLowerCase() === val; })) {
        html += '<div class="px-4 py-2 text-xs font-bold text-emerald-600 hover:bg-emerald-50 cursor-pointer border-t border-slate-100 flex items-center justify-between bulk-item-new">';
        html += '<span>+ New: <strong>' + input.value + '</strong></span>';
        html += '<span class="text-[8px] bg-emerald-100 text-emerald-600 px-1.5 py-0.5 rounded-full uppercase font-black ml-2">NEW</span>';
        html += '</div>';
    }
    if (!html) html = '<div class="px-4 py-3 text-xs text-slate-400 italic">No items found</div>';
    dd.innerHTML = html;
    dd.classList.remove('hidden');

    dd.querySelectorAll('.bulk-item-opt').forEach(function(el) {
        el.addEventListener('mousedown', function() { bulkSelectItem(this.dataset.name); });
    });
    dd.querySelectorAll('.bulk-item-new').forEach(function(el) {
        el.addEventListener('mousedown', function() { bulkSelectItem(input.value); });
    });
}
function bulkSelectItem(name) {
    document.getElementById('bulkItemInput').value = name;
    document.getElementById('bulkItemDrop').classList.add('hidden');
}

"""

NEW_SRC = r"""// -- Bulk Source Autocomplete --------------------------------------------------
function bulkFilterSrc() {
    const input = document.getElementById('bulkSrcInput');
    const dd    = document.getElementById('bulkSrcDrop');
    const val   = input.value.toLowerCase().trim();
    const results = val ? rawSources.filter(function(s) { return s.name.toLowerCase().includes(val); }) : rawSources;

    bulkSelectedSrcType = null;
    const srcTypeSel = document.getElementById('bulkSrcType');
    srcTypeSel.disabled = false;
    srcTypeSel.classList.remove('opacity-60', 'cursor-not-allowed', 'bg-slate-100');

    let html = '';
    if (results.length) {
        html += '<div class="px-3 py-1.5 text-[9px] font-black text-slate-400 uppercase tracking-widest sticky top-0 bg-white border-b border-slate-50">Existing Sources</div>';
        results.slice(0, 30).forEach(function(s) {
            const typeLabel = s.entity_type || '';
            const badgeCls  = srcTypeBadgeColors[typeLabel] || 'bg-slate-100 text-slate-500';
            const badge = typeLabel ? '<span class="text-[8px] ' + badgeCls + ' px-1.5 py-0.5 rounded-full uppercase font-black ml-1.5">' + typeLabel + '</span>' : '';
            html += '<div class="px-4 py-2 text-xs font-bold hover:bg-red-50 hover:text-red-600 cursor-pointer border-b border-slate-50 last:border-0 flex items-center justify-between bulk-src-exist" data-name="' + s.name.replace(/"/g, '&quot;') + '" data-type="' + typeLabel + '">';
            html += '<span class="truncate">' + s.name + '</span>';
            html += '<div class="flex items-center gap-1 shrink-0 ml-2">' + badge + '<span class="text-[8px] bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded-full uppercase font-black">EXISTS</span></div>';
            html += '</div>';
        });
    }
    if (val && !rawSources.find(function(s) { return s.name.toLowerCase() === val; })) {
        html += '<div class="px-4 py-2 text-xs font-bold text-emerald-600 hover:bg-emerald-50 cursor-pointer border-t border-slate-100 flex items-center justify-between bulk-src-new">';
        html += '<span>+ Register as New: <strong>' + input.value + '</strong></span>';
        html += '<span class="text-[8px] bg-emerald-100 text-emerald-600 px-1.5 py-0.5 rounded-full uppercase font-black ml-2">NEW</span>';
        html += '</div>';
    }
    if (!html) html = '<div class="px-4 py-3 text-xs text-slate-400 italic">No existing sources found</div>';
    dd.innerHTML = html;
    dd.classList.remove('hidden');

    dd.querySelectorAll('.bulk-src-exist').forEach(function(el) {
        el.addEventListener('mousedown', function() { bulkSelectSrc(this.dataset.name, this.dataset.type); });
    });
    dd.querySelectorAll('.bulk-src-new').forEach(function(el) {
        el.addEventListener('mousedown', function() { bulkSelectSrc(input.value, null); });
    });
}
function bulkSelectSrc(name, entityType) {
    document.getElementById('bulkSrcInput').value = name;
    document.getElementById('bulkSrcDrop').classList.add('hidden');
    const srcTypeSel = document.getElementById('bulkSrcType');
    if (entityType) {
        bulkSelectedSrcType = entityType;
        srcTypeSel.value = entityType;
        srcTypeSel.disabled = true;
        srcTypeSel.classList.add('opacity-60', 'cursor-not-allowed', 'bg-slate-100');
        srcTypeSel.title = 'Locked - registered as "' + entityType + '"';
    }
}

"""

content = content.replace(OLD_ITEM, NEW_ITEM)
content = content.replace(OLD_SRC,  NEW_SRC)

with open(path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Done. Both autocomplete functions rewritten.")
