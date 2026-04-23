import subprocess, re, os, sys

path = r'resources\views\partials\import.blade.php'
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

# Extract all <script> block content
scripts = re.findall(r'<script>(.*?)</script>', content, re.DOTALL)
combined = '\n'.join(scripts)

# Strip @json Blade directives - replace with valid JS placeholders
combined = re.sub(r'@json\([^)]+\)', '[]', combined)

# Write to temp file
with open('_check_js.js', 'w', encoding='utf-8') as f:
    f.write(combined)

# Run Node.js syntax check
result = subprocess.run(['node', '--check', '_check_js.js'], capture_output=True, text=True)
if result.returncode == 0:
    print("JS OK - no syntax errors.")
else:
    print("JS ERROR:")
    print(result.stderr)

os.remove('_check_js.js')
