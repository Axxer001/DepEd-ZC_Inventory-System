import os
import re

dir_to_scan = r"C:\Users\Axxer\Documents\DepEd_ZC - Inventory System\DepEd-ZC-Inventory-System-main\resources\views"

cdn_patterns = [
    r'<!--.*?-->',  # Keep this in mind, but let's target specific scripts
    r'<script\s+defer\s+src="https://unpkg\.com/alpinejs@3\.x\.x/dist/cdn\.min\.js"></script>',
    r'<script\s+defer\s+src="https://unpkg\.com/@alpinejs/collapse@3\.x\.x/dist/cdn\.min\.js"></script>',
    r'<link\s+href="https://cdn\.jsdelivr\.net/npm/tom-select@2\.2\.2/dist/css/tom-select\.css"\s+rel="stylesheet">',
    r'<script\s+src="https://cdn\.jsdelivr\.net/npm/tom-select@2\.2\.2/dist/js/tom-select\.complete\.min\.js"></script>',
    r'<script\s+src="https://cdn\.jsdelivr\.net/npm/sweetalert2@11"></script>',
    r'<script\s+defer\s+src="https://cdn\.jsdelivr\.net/npm/alpinejs@3\.x\.x/dist/cdn\.min\.js"></script>'
]

modified_files = []

for root, dirs, files in os.walk(dir_to_scan):
    for file in files:
        if file.endswith('.blade.php'):
            filepath = os.path.join(root, file)
            with open(filepath, 'r', encoding='utf-8') as f:
                content = f.read()
            
            orig_content = content
            
            # Check if file uses Vite. If not, we might not want to strip CDNs without caution.
            # But all of our layout/profiles use `@vite` anyway.
            has_vite = "@vite" in content
            
            if has_vite:
                for pattern in cdn_patterns:
                    content = re.sub(pattern, '', content)
                
                # Clean up any leftover blank lines caused by removing tags
                # (Optional, but makes it cleaner)
                if content != orig_content:
                    with open(filepath, 'w', encoding='utf-8') as f:
                        f.write(content)
                    modified_files.append(filepath)

print(f"Modified {len(modified_files)} files:")
for f in modified_files:
    print(f"  - {f}")
