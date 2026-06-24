import pandas as pd
import openpyxl

# Define columns
columns = [
    "#", 
    "Title", 
    "Description", 
    "Preconditions", 
    "Test Steps", 
    "Expected Result", 
    "Actual Result", 
    "Pass/Fail", 
    "Bug Description/Comments"
]

def format_steps(steps):
    return "\n".join([f"{i+1}. {step}" for i, step in enumerate(steps)])

# --- Public View Data ---
public_data = [
    [
        1, 
        "User Registration", 
        "Verify that a new user can register an account.", 
        "User is on the registration page.", 
        format_steps([
            "Enter a valid first name, last name, and email address.",
            "Click on the 'Send OTP' button.",
            "Check the email for the OTP.",
            "Enter the received OTP in the verification field.",
            "Enter a strong password (min 8 chars, uppercase, lowercase, numbers, no special chars).",
            "Confirm the password.",
            "Submit the registration form."
        ]), 
        "User is successfully registered and placed in pending approval state. A notification is sent to Super Admins.",
        "", "", ""
    ],
    [
        2, 
        "User Login (Pending Approval)", 
        "Verify that a newly registered user cannot access the system until approved.", 
        "User has registered but not yet been approved by a Super Admin.", 
        format_steps([
            "Navigate to the login page.",
            "Enter the registered email and password.",
            "Click the login button."
        ]), 
        "System displays an error message stating the account is pending approval. Login is denied.",
        "", "", ""
    ],
    [
        3, 
        "User Login (Approved)", 
        "Verify that an approved user can successfully log in.", 
        "User account has been approved by a Super Admin.", 
        format_steps([
            "Navigate to the login page.",
            "Enter the registered email and password.",
            "Click the login button."
        ]), 
        "User is successfully authenticated and redirected to the Dashboard.",
        "", "", ""
    ],
    [
        4, 
        "Forgot Password - Request PIN", 
        "Verify that a user can request a password reset PIN.", 
        "User has a registered account.", 
        format_steps([
            "Navigate to the login page.",
            "Click on the 'Forgot Password' link.",
            "Enter the registered email address.",
            "Submit the request."
        ]), 
        "System sends a PIN to the user's email and redirects to the PIN verification page.",
        "", "", ""
    ],
    [
        5, 
        "Forgot Password - Reset Password", 
        "Verify that a user can reset their password using the PIN.", 
        "User has received the reset PIN via email.", 
        format_steps([
            "Enter the PIN on the verify PIN page.",
            "Submit the PIN.",
            "Enter a new strong password and confirm it.",
            "Submit the new password."
        ]), 
        "Password is successfully updated. User can now log in with the new password.",
        "", "", ""
    ]
]

# --- Admin Data ---
admin_data = [
    [
        1, 
        "Dashboard - View Data", 
        "Verify that the Admin dashboard displays correct growth data and summaries.", 
        "User is logged in as an Admin.", 
        format_steps([
            "Navigate to the Dashboard.",
            "Observe the displayed statistics (assets, users, growth data)."
        ]), 
        "Dashboard correctly retrieves and displays data charts and summaries.",
        "", "", ""
    ],
    [
        2, 
        "Dashboard - Quick Asset Entry", 
        "Verify that Admin can quickly add a new asset from the dashboard.", 
        "User is logged in as an Admin.", 
        format_steps([
            "Click on the 'Quick Asset' button on the dashboard.",
            "Fill in required asset details (category, item, quantity).",
            "Submit the form."
        ]), 
        "Asset is successfully added to the database and a success message is displayed.",
        "", "", ""
    ],
    [
        3, 
        "Inventory Setup - Add Item", 
        "Verify that Admin can register new items in the system.", 
        "User is logged in as an Admin.", 
        format_steps([
            "Navigate to 'Register Item'.",
            "Select a category or add a new one.",
            "Enter the item name and details.",
            "Assign to a stakeholder or sub-item if applicable.",
            "Submit the item registration."
        ]), 
        "Item is saved and appears in the items dropdown for future use.",
        "", "", ""
    ],
    [
        4, 
        "Asset Explorer - View Assets", 
        "Verify that Admin can view and filter the list of existing assets.", 
        "Assets exist in the system. Admin is logged in.", 
        format_steps([
            "Navigate to 'Asset Explorer'.",
            "Use filters (school, category, status) to search for assets.",
            "Click on an asset to view its profile."
        ]), 
        "Assets matching the filters are displayed. Clicking an asset opens its detailed profile.",
        "", "", ""
    ],
    [
        5, 
        "Asset Management - Transfer Asset", 
        "Verify that Admin can transfer an asset to a different custodian or location.", 
        "Admin is viewing a specific asset's profile.", 
        format_steps([
            "Click the 'Transfer' button.",
            "Select the new school/office and custodian.",
            "Confirm the transfer."
        ]), 
        "Asset is successfully transferred. History log is updated with the transfer details.",
        "", "", ""
    ],
    [
        6, 
        "Asset Management - Upload Photo/Document", 
        "Verify that Admin can attach photos and documents to an asset.", 
        "Admin is viewing an asset's profile.", 
        format_steps([
            "Navigate to the 'Attachments' section.",
            "Upload a valid image file for the photo.",
            "Upload a valid PDF/Document for the asset files.",
            "Save the changes."
        ]), 
        "Files are uploaded, stored, and visible on the asset profile.",
        "", "", ""
    ],
    [
        7, 
        "Generate Reports (RPC)", 
        "Verify that Admin can generate and download RPC reports.", 
        "Admin is logged in.", 
        format_steps([
            "Navigate to the 'Reports' section.",
            "Select the 'RPC' report type.",
            "Apply desired filters (e.g., specific school or date range).",
            "Click 'Download Report'."
        ]), 
        "An Excel/PDF report is generated with correct data and downloaded to the local machine.",
        "", "", ""
    ],
    [
        8, 
        "Print QR Stickers", 
        "Verify that Admin can generate a list of QR stickers for assets.", 
        "Assets exist in the system.", 
        format_steps([
            "Navigate to 'Print Stickers' under Assets.",
            "Select the assets to print.",
            "Click the Print button."
        ]), 
        "A printable view of QR stickers with correct asset information is displayed.",
        "", "", ""
    ],
    [
        9, 
        "Building Registry - Import PIF", 
        "Verify that Admin can import building records via PIF Excel file.", 
        "Admin has a valid PIF Excel template filled with building data.", 
        format_steps([
            "Navigate to 'Buildings > Import'.",
            "Upload the PIF Excel file.",
            "Preview the parsed data.",
            "Confirm the import."
        ]), 
        "Building records are successfully imported and saved to the database.",
        "", "", ""
    ]
]

# --- Super Admin Data ---
super_admin_data = [
    [
        1, 
        "User Management - Approve User", 
        "Verify that Super Admin can approve pending user registrations.", 
        "There is a pending user registration. Super Admin is logged in.", 
        format_steps([
            "Navigate to 'Admin > User Management'.",
            "Locate the pending user in the list.",
            "Click the 'Approve' button.",
            "Confirm the action."
        ]), 
        "User status changes to approved. The user can now log in.",
        "", "", ""
    ],
    [
        2, 
        "User Management - Reject User", 
        "Verify that Super Admin can reject and delete pending users.", 
        "There is a pending user registration.", 
        format_steps([
            "Navigate to 'User Management'.",
            "Locate the pending user.",
            "Click the 'Reject' button.",
            "Confirm the action."
        ]), 
        "User account is permanently deleted from the database.",
        "", "", ""
    ],
    [
        3, 
        "User Management - Update Role", 
        "Verify that Super Admin can change a user's role.", 
        "An approved user exists.", 
        format_steps([
            "Navigate to 'User Management'.",
            "Select a user.",
            "Change the role dropdown from 'admin' to 'super_admin' (or vice versa).",
            "Save the changes."
        ]), 
        "User's role is updated in the database and their access rights change immediately.",
        "", "", ""
    ],
    [
        4, 
        "User Management - Block/Unblock User", 
        "Verify that Super Admin can restrict an approved user's access.", 
        "An approved user exists.", 
        format_steps([
            "Navigate to 'User Management'.",
            "Click the 'Block' button for the user.",
            "Verify the user cannot log in.",
            "Click the 'Unblock' button.",
            "Verify the user can log in again."
        ]), 
        "User access is successfully restricted and restored as expected.",
        "", "", ""
    ],
    [
        5, 
        "System Announcements", 
        "Verify that Super Admin can broadcast a custom notification to all users.", 
        "Super Admin is logged in.", 
        format_steps([
            "Open the notifications panel.",
            "Click on 'Broadcast Announcement'.",
            "Type a custom message.",
            "Submit the announcement."
        ]), 
        "The announcement is dispatched to all approved users and appears in their notifications list.",
        "", "", ""
    ],
    [
        6, 
        "Employee Management - Add Employee", 
        "Verify that Super Admin can manually add employee records.", 
        "Super Admin is logged in.", 
        format_steps([
            "Navigate to 'Admin > Employee Management'.",
            "Click 'Add Employee'.",
            "Fill in employee details (Name, Position, ID, Office/School).",
            "Save the record."
        ]), 
        "Employee is successfully added to the system registry.",
        "", "", ""
    ],
    [
        7, 
        "Super Admin Role Enforcement", 
        "Verify that regular Admins cannot access Super Admin exclusive routes.", 
        "Logged in as a regular Admin.", 
        format_steps([
            "Attempt to navigate directly to the '/admin/user-management' URL.",
            "Attempt to submit a request to the '/api/notifications/custom' endpoint."
        ]), 
        "System returns a 403 Forbidden error for all Super Admin restricted routes.",
        "", "", ""
    ]
]

# Convert lists to DataFrames
df_public = pd.DataFrame(public_data, columns=columns)
df_admin = pd.DataFrame(admin_data, columns=columns)
df_super_admin = pd.DataFrame(super_admin_data, columns=columns)

# Write to Excel
output_file = 'Feature_Testing.xlsx'
with pd.ExcelWriter(output_file, engine='openpyxl') as writer:
    df_admin.to_excel(writer, sheet_name='Admin', index=False)
    df_super_admin.to_excel(writer, sheet_name='Super Admin', index=False)
    df_public.to_excel(writer, sheet_name='Public View', index=False)

    # Auto-adjust column widths and formatting
    for sheet_name in writer.sheets:
        worksheet = writer.sheets[sheet_name]
        for idx, col in enumerate(worksheet.columns):
            max_length = 0
            column_name = col[0].column_letter # Get column name (A, B, C...)
            for cell in col:
                # Wrap text for specific columns to avoid extremely wide columns
                if column_name in ['C', 'D', 'E', 'F', 'I']: # Description, Preconditions, Test Steps, Expected Result, Comments
                    cell.alignment = openpyxl.styles.Alignment(wrap_text=True, vertical='top')
                else:
                    cell.alignment = openpyxl.styles.Alignment(vertical='top')

                try: 
                    if len(str(cell.value)) > max_length:
                        max_length = len(cell.value)
                except:
                    pass
            
            # Set width limits
            adjusted_width = (max_length + 2)
            if column_name == 'A':
                adjusted_width = 5
            elif column_name in ['C', 'D', 'E', 'F']:
                adjusted_width = 45 # Cap width for text-heavy columns
            elif column_name in ['G', 'H', 'I']:
                adjusted_width = 20
            elif column_name == 'B':
                adjusted_width = 30
                
            worksheet.column_dimensions[column_name].width = adjusted_width

print(f"Excel file '{output_file}' generated successfully.")
