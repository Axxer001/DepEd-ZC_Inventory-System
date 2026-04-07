file_path = 'resources/views/inventory-setup.blade.php'
with open(file_path, 'r', encoding='utf-8') as f:
    text = f.read()

idx = text.rfind('</script>')
if idx != -1:
    js_funcs = """
    function toggleSerialPanel(btn) {
        const panel = btn.parentElement.nextElementSibling;
        if (panel.classList.contains('hidden')) {
            panel.classList.remove('hidden');
            btn.classList.add('bg-slate-100', 'text-[#c00000]');
            btn.classList.remove('bg-white', 'text-slate-500');
        } else {
            panel.classList.add('hidden');
            btn.classList.remove('bg-slate-100', 'text-[#c00000]');
            btn.classList.add('bg-white', 'text-slate-500');
        }
    }

    function toggleSerializedFields(checkbox) {
        const fields = checkbox.closest('.serial-panel').querySelectorAll('.serial-field');
        const qtyInput = checkbox.closest('.sub-item-row').querySelector('input[name="sub_item_quantities[]"]');
        if (checkbox.checked) {
            fields.forEach(f => {
                f.classList.remove('hidden');
            });
            if (qtyInput) {
                qtyInput.value = 1;
                qtyInput.readOnly = true;
                qtyInput.classList.add('bg-slate-200', 'text-slate-400');
            }
        } else {
            fields.forEach(f => {
                f.classList.add('hidden');
                f.value = '';
            });
            if (qtyInput) {
                qtyInput.readOnly = false;
                qtyInput.classList.remove('bg-slate-200', 'text-slate-400');
            }
        }
    }
"""
    new_text = text[:idx] + js_funcs + text[idx:]
    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(new_text)
    print('Functions injected successfully!')
else:
    print('Could not find closing script tag!')
