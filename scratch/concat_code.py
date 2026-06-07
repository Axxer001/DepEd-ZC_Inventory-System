import os

files = [
    "app/Http/Controllers/InventorySetupController.php",
    "app/Http/Controllers/ImportController.php",
    "app/Http/Controllers/BuildingImportController.php",
    "app/Http/Controllers/AssetController.php",
    "app/Http/Controllers/ReportDownloadController.php",
    "app/Http/Controllers/BuildingController.php",
    "resources/views/register-item.blade.php",
    "resources/views/inventory-setup.blade.php",
    "resources/views/partials/download-reports.blade.php",
    "resources/views/register-building.blade.php",
    "resources/views/import-buildings.blade.php",
    "resources/views/assets/view-all.blade.php",
    "resources/views/assets/profile.blade.php",
    "resources/views/partials/inventory-edit-step.blade.php",
    "resources/views/buildings/profile.blade.php",
    "resources/views/partials/building-edit-step.blade.php"
]

output_file = "system_code_context.md"
base_dir = r"c:\Users\Axxer\Documents\DepEd_ZC - Inventory System\DepEd-ZC-Inventory-System-main"

with open(os.path.join(base_dir, output_file), "w", encoding="utf-8") as out:
    out.write("# System Code Context\n\n")
    out.write("This document contains the source code for the controllers and views responsible for Item/Building Management, Imports, and Bulk Edits.\n\n")
    
    for rel_path in files:
        abs_path = os.path.join(base_dir, rel_path)
        out.write(f"## File: `{rel_path}`\n\n")
        
        if os.path.exists(abs_path):
            with open(abs_path, "r", encoding="utf-8") as f:
                content = f.read()
            
            ext = rel_path.split('.')[-1]
            if rel_path.endswith('.blade.php'):
                ext = 'html' # better formatting for blade usually
            elif ext == 'php':
                ext = 'php'
                
            out.write(f"```{ext}\n")
            out.write(content)
            if not content.endswith("\n"):
                out.write("\n")
            out.write("```\n\n")
        else:
            out.write(f"> **Warning:** File not found at path: {abs_path}\n\n")

print(f"Successfully generated {output_file} at {base_dir}")
