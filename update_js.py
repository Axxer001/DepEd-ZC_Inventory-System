import re

with open('resources/views/partials/inventory-edit-step.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update filterBulkEditClassDropdown and filterBulkEditCatDropdown
class_func = '''    function filterBulkEditClassDropdown(query) {
        const dd = document.getElementById('bulk-edit-class-dd');
        const badge = document.getElementById('ebClassificationNewBadge');
        if (!dd) return;
        const q = (query || '').trim().toLowerCase();
        const classes = typeof globalClassifications !== 'undefined' ? globalClassifications : [];
        const matches = q.length === 0 ? classes.slice(0, 50) : classes.filter(c => c.name.toLowerCase().includes(q)).slice(0, 50);

        if (q.length > 0 && !classes.some(c => c.name.toLowerCase() === q)) {
            if(badge) badge.classList.remove('hidden');
        } else {
            if(badge) badge.classList.add('hidden');
        }

        if (matches.length === 0) dd.innerHTML = <div class="xls-dd-empty">No classifications found</div>;
        else dd.innerHTML = matches.map(c => <div class="xls-dd-item" onmousedown="selectBulkEditClass(this.getAttribute('data-name'))" data-name=""></div>).join('');
        dd.style.display = 'block';
    }

    function selectBulkEditClass(name) {
        const inp = document.getElementById('ebClassification');
        const badge = document.getElementById('ebClassificationNewBadge');
        if (inp) inp.value = name;
        if (badge) badge.classList.add('hidden');
        const dd = document.getElementById('bulk-edit-class-dd');
        if (dd) dd.style.display = 'none';
        
        // Clear category when classification changes
        const catInp = document.getElementById('ebCategory');
        if (catInp) catInp.value = '';
    }'''

cat_func = '''    function filterBulkEditCatDropdown(query) {
        const dd = document.getElementById('bulk-edit-cat-dd');
        const badge = document.getElementById('ebCategoryNewBadge');
        if (!dd) return;
        const q = (query || '').trim().toLowerCase();
        
        const classInp = document.getElementById('ebClassification');
        const className = classInp ? classInp.value.trim() : '';

        const cats = typeof globalCategories !== 'undefined' ? globalCategories : [];
        let pool = cats;
        if (className !== '') {
            pool = cats.filter(c => c.classification_name && c.classification_name.toLowerCase() === className.toLowerCase());
        }

        const matches = q.length === 0 ? pool.slice(0, 50) : pool.filter(c => c.name.toLowerCase().includes(q)).slice(0, 50);

        if (q.length > 0 && !pool.some(c => c.name.toLowerCase() === q)) {
            if(badge) badge.classList.remove('hidden');
        } else {
            if(badge) badge.classList.add('hidden');
        }

        if (matches.length === 0) dd.innerHTML = <div class="xls-dd-empty">No categories found</div>;
        else dd.innerHTML = matches.map(c => <div class="xls-dd-item" onmousedown="selectBulkEditCat(this.getAttribute('data-name'))" data-name=""><span style="color:#64748b;font-size:8px;margin-left:6px;"></span></div>).join('');
        dd.style.display = 'block';
    }

    function selectBulkEditCat(name) {
        const inp = document.getElementById('ebCategory');
        const badge = document.getElementById('ebCategoryNewBadge');
        if (inp) inp.value = name;
        if (badge) badge.classList.add('hidden');
        const dd = document.getElementById('bulk-edit-cat-dd');
        if (dd) dd.style.display = 'none';
    }'''

# Replace placeholders or insert them
# Check if filterBulkEditClassDropdown exists
if 'function filterBulkEditClassDropdown' in content:
    content = re.sub(r'function filterBulkEditClassDropdown.*?function selectBulkEditClass.*?\n    }', class_func, content, flags=re.DOTALL)
else:
    # insert before filterEditBulkEmpDropdown
    content = content.replace('function filterEditBulkEmpDropdown', class_func + '\n\n' + '    function filterEditBulkEmpDropdown')

if 'function filterBulkEditCatDropdown' in content:
    content = re.sub(r'function filterBulkEditCatDropdown.*?function selectBulkEditCat.*?\n    }', cat_func, content, flags=re.DOTALL)
else:
    content = content.replace('function filterEditBulkEmpDropdown', cat_func + '\n\n' + '    function filterEditBulkEmpDropdown')

# Update editBulkAutofillEmployee
emp_autofill = '''    function editBulkAutofillEmployee(val) {
        const emp = (typeof globalEmployees !== 'undefined' ? globalEmployees : []).find(e => e.full_name === val);
        const ebSchoolSearch = document.getElementById('ebSchoolSearch');
        if(emp) {
            document.getElementById('ebEmployeeId').value     = emp.employee_id || '';
            document.getElementById('ebEmployeeName').value   = emp.full_name || '';
            document.getElementById('ebEmployeePos').value    = emp.position || '';
            document.getElementById('ebEmployeeStatus').value = emp.status || '';

            if (ebSchoolSearch) {
                ebSchoolSearch.disabled = true;
                ebSchoolSearch.classList.add('edit-readonly', 'cursor-not-allowed');
                ebSchoolSearch.value = emp.location_name || '';
            }

            if (emp.location_name) {
                document.getElementById('ebSchoolName').value = emp.location_name;
                document.getElementById('ebSchoolId').value   = emp.location_id || '';
                document.getElementById('ebSchoolType').value = emp.location_type_label || emp.location_type || '';
                document.getElementById('ebLocation').value   = emp.location || 'Zamboanga City';
            }
        } else if (!val) {
            document.getElementById('ebEmployeeId').value     = '';
            document.getElementById('ebEmployeeName').value   = '';
            document.getElementById('ebEmployeePos').value    = '';
            document.getElementById('ebEmployeeStatus').value = '';

            if (ebSchoolSearch) {
                ebSchoolSearch.disabled = false;
                ebSchoolSearch.classList.remove('edit-readonly', 'cursor-not-allowed');
                ebSchoolSearch.value = '';
            }
        }
    }'''
content = re.sub(r'function editBulkAutofillEmployee\(val\).*?}\n    }', emp_autofill, content, flags=re.DOTALL)

with open('resources/views/partials/inventory-edit-step.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)
print("JS updated")
