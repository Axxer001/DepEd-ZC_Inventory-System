// ─── Custom Autocomplete Logic ───────────────────────────────────────
const dbSuggestions = {};
// Parse existing datalists into dictionaries on load
document.querySelectorAll('datalist[id^="dl-"]').forEach(dl => {
    const colName = dl.id.replace('dl-', '');
    dbSuggestions[colName] = Array.from(dl.options).map(opt => opt.value).filter(Boolean);
});

let activeAutocomplete = null;

document.addEventListener('focusin', handleAutocompleteEvent);
document.addEventListener('input', handleAutocompleteEvent);
document.addEventListener('mousedown', (e) => {
    if (activeAutocomplete && !e.target.closest('.custom-autocomplete') && !e.target.hasAttribute('data-col')) {
        closeAutocomplete();
    }
});

function closeAutocomplete() {
    if (activeAutocomplete) {
        activeAutocomplete.remove();
        activeAutocomplete = null;
    }
}

function handleAutocompleteEvent(e) {
    const input = e.target;
    if (!input || input.tagName !== 'INPUT') return;

    let colName = input.getAttribute('data-col');
    let isBldgEntry = false;
    if (!colName) {
        colName = input.getAttribute('data-bldg-col');
        if (colName) isBldgEntry = true;
    }

    if (!colName) return;

    const typedValue = input.value.toLowerCase().trim();
    
    // Detect active module
    const isBuilding = document.getElementById('stepAddBuilding')?.classList.contains('active');
    
    let dataToUse, pageToUse, rowsPerPageToUse;
    if (isBldgEntry) {
        dataToUse = typeof bldgEntryRows !== 'undefined' ? bldgEntryRows : [];
        pageToUse = typeof bldgEntryPage !== 'undefined' ? bldgEntryPage : 1;
        rowsPerPageToUse = typeof BLDG_RPP !== 'undefined' ? BLDG_RPP : 50;
    } else {
        dataToUse = isBuilding ? (typeof bldgRowsData !== 'undefined' ? bldgRowsData : []) : allRowsData;
        pageToUse = isBuilding ? (typeof bldgCurrentPage !== 'undefined' ? bldgCurrentPage : 1) : currentPage;
        rowsPerPageToUse = isBuilding ? (typeof bldgRowsPerPage !== 'undefined' ? bldgRowsPerPage : 50) : rowsPerPage;
    }

    // Gather unique values from CURRENT PAGE only (most recent first)
    const start = (pageToUse - 1) * rowsPerPageToUse;
    const end   = start + rowsPerPageToUse;
    const pageData = dataToUse.slice(start, end);

    // Define columns in the Asset Distribution section to skip local suggestions (recently typed values)
    const skipLocalCols = [
        'property-no', 'property_number', 'property_no', 'propertyNo',
        'location', 'loc',
        'acquisition-date', 'acquisition_date',
        'school-search', 'school_search', 'school-id', 'school_id', 'school-type', 'school_type', 'school-name', 'office_school_name',
        'employee-search', 'employee_search', 'employee-id', 'custodian_employee_id', 'employee-name', 'custodian_name', 'employee-pos', 'custodian_position', 'employee-status', 'custodian_status'
    ];

    const localData = [];
    if (!skipLocalCols.includes(colName)) {
        for (let i = pageData.length - 1; i >= 0; i--) {
            const val = (pageData[i][colName] || "").toString().trim();
            if (val && !localData.includes(val)) {
                // Don't suggest the value currently being typed in the SAME row
                const tr = input.closest('tr');
                if (tr) {
                    const rowIdStr = tr.id.split('-').pop(); // Handle src-ID or bldg-row-ID
                    const rowId = parseInt(rowIdStr);
                    const dataId = isBldgEntry ? pageData[i]._id : pageData[i].id;
                    if (dataId !== rowId) {
                        localData.push(val);
                    }
                } else {
                    localData.push(val);
                }
            }
        }
    }
    const listId = input.getAttribute('list');
    const dbKey = listId ? listId.replace('dl-', '') : colName;
    const dbData = dbSuggestions[dbKey] || [];
    const suggestions = new Set(localData);
    for (const item of dbData) { suggestions.add(item); }
    
    const filtered = Array.from(suggestions)
        .filter(val => val.toLowerCase().includes(typedValue));

    closeAutocomplete();
    if (filtered.length === 0) return;

    const rect = input.getBoundingClientRect();
    const dropdown = document.createElement('div');
    dropdown.className = 'custom-autocomplete custom-scroll';
    dropdown.style.left = `${rect.left + window.scrollX}px`;
    const spaceBelow = window.innerHeight - rect.bottom;
    if (spaceBelow < 250 && rect.top > 250) {
        dropdown.style.bottom = `${window.innerHeight - rect.top - window.scrollY}px`;
        dropdown.style.top = 'auto';
    } else {
        dropdown.style.top = `${rect.bottom + window.scrollY}px`;
        dropdown.style.bottom = 'auto';
    }
    dropdown.style.width = `${input.offsetWidth}px`;
    dropdown.style.maxHeight = '250px';
    dropdown.style.overflowY = 'auto';

    filtered.forEach(val => {
        const item = document.createElement('div');
        item.className = 'custom-autocomplete-item';
        item.textContent = val;
        item.addEventListener('mousedown', (evt) => {
            evt.preventDefault();
            input.value = val;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            
            const tr = input.closest('tr');
            if (tr) {
                const rowId = parseInt(tr.id.split('-').pop());
                if (isBldgEntry) {
                    if (typeof syncBldgRow === 'function') syncBldgRow(rowId, colName, val);
                } else if (isBuilding) {
                    if (typeof syncBldgState === 'function') syncBldgState(rowId, colName, val);
                } else {
                    syncState(rowId, colName, val);
                }
            }
            closeAutocomplete();
            if (isBldgEntry || isBuilding) {
                if (typeof updateBldgNewLabels === 'function') updateBldgNewLabels();
            } else {
                updateNewLabels();
            }
        });
        dropdown.appendChild(item);
    });
    document.body.appendChild(dropdown);
    activeAutocomplete = dropdown;
}

function updateNewLabels() {
    const visibleInputs = document.querySelectorAll('input[data-col], input[data-bldg-col]');
    if (visibleInputs.length === 0) return;

    const colNames = Array.from(new Set(Array.from(visibleInputs).map(el => el.getAttribute('data-col') || el.getAttribute('data-bldg-col'))));
    const colContexts = {};
    colNames.forEach(cn => {
        colContexts[cn] = {
            seen: new Set((dbSuggestions[cn] || []).map(v => v.toLowerCase().trim())),
            firstOccurrences: new Map()
        };
    });

    // Process only current page state to find first occurrences
    const isBldgStep = document.getElementById('stepAddBuilding')?.classList.contains('active');
    
    let dataToUse, pageToUse, rowsPerPageToUse;
    if (isBldgStep) {
        dataToUse = typeof bldgEntryRows !== 'undefined' ? bldgEntryRows : [];
        pageToUse = typeof bldgEntryPage !== 'undefined' ? bldgEntryPage : 1;
        rowsPerPageToUse = typeof BLDG_RPP !== 'undefined' ? BLDG_RPP : 50;
    } else {
        // Determine if we are in building management or item management
        const isBldgMgmt = false; // Add logic if needed
        dataToUse = isBldgMgmt ? (typeof bldgRowsData !== 'undefined' ? bldgRowsData : []) : allRowsData;
        pageToUse = isBldgMgmt ? (typeof bldgCurrentPage !== 'undefined' ? bldgCurrentPage : 1) : currentPage;
        rowsPerPageToUse = isBldgMgmt ? (typeof bldgRowsPerPage !== 'undefined' ? bldgRowsPerPage : 50) : rowsPerPage;
    }

    const start = (pageToUse - 1) * rowsPerPageToUse;
    const end   = start + rowsPerPageToUse;
    const pageData = dataToUse.slice(start, end);

    pageData.forEach(row => {
        colNames.forEach(cn => {
            const val = (row[cn] || "").toString().trim().toLowerCase();
            const dataId = isBldgStep ? row._id : row.id;
            if (val && !colContexts[cn].seen.has(val)) {
                colContexts[cn].firstOccurrences.set(val, dataId);
                colContexts[cn].seen.add(val);
            }
        });
    });

    // Update only visible DOM elements
    visibleInputs.forEach(input => {
        const cn = input.getAttribute('data-col') || input.getAttribute('data-bldg-col');
        const val = input.value.trim().toLowerCase();
        const tr = input.closest('tr');
        if (!tr) return;
        const rowId = parseInt(tr.id.split('-').pop());
        const td = input.closest('td');
        
        const existingBadge = td.querySelector('.new-badge');
        if (existingBadge) existingBadge.remove();

        if (val !== '' && colContexts[cn].firstOccurrences.get(val) === rowId) {
            const badge = document.createElement('span');
            badge.className = 'new-badge';
            badge.textContent = 'NEW';
            td.appendChild(badge);
        }
    });
}

// Attach input listener strictly for the new labels
document.addEventListener('input', updateNewLabels);

window.addEventListener('scroll', (e) => {
    if (activeAutocomplete && (e.target === activeAutocomplete || activeAutocomplete.contains(e.target))) return;
    closeAutocomplete();
}, true);
window.addEventListener('resize', closeAutocomplete);
