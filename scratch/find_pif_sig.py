import openpyxl
import os

path = r'c:\Users\Axxer\Documents\DepEd_ZC - Inventory System\PIF.xlsx'
if os.path.exists(path):
    wb = openpyxl.load_workbook(path, data_only=True)
    sheet = wb.active
    # Look for signatories keywords in any column
    for row in range(1, 100):
        for col in range(1, 10):
            val = sheet.cell(row=row, column=col).value
            if val and any(k in str(val).upper() for k in ['CERTIFIED', 'APPROVED', 'RECEIVED', 'CERTIFIED CORRECT']):
                print(f"PIF Signature Row: {row}")
                exit()
    print("Signatures not found in first 100 rows")
else:
    print("PIF.xlsx not found")
