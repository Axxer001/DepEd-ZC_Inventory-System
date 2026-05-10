# Inventory Management - Bulk Editing Feature

This document outlines the implementation plan for the new "Inventory Management" feature requested for the `inventory-setup.blade.php` page. The goal is to provide a high-performance, cell-based input grid that allows users to seamlessly edit existing inventory data directly from the system, bulk-edit specific columns across a range of rows, and undo mistakes.

## User Review Required
> [!IMPORTANT]
> - Since this feature allows bulk modification of existing database records, I will implement a robust server-side validation and confirmation mechanism (SweetAlert) to prevent accidental data overwrites.
> - Please review the proposed column locking (Region and Division will be uneditable). Let me know if any other columns should be locked.
> - The undo stack will be *client-side only*. Once the user clicks "Save Changes" and the SweetAlert is confirmed, the data is persisted to the database and can no longer be undone via the undo button.

## Open Questions
> [!NOTE]  
> 1. In the "ADD ITEMS" template, there are two separate views (Asset Source & Asset Distribution). For this Edit feature, should we combine them into a single wide table (like the PIF report) where users can edit everything on one screen, or keep the tabbed/split view? *I am proposing a single wide table for better usability during bulk edits.*
> 2. The pagination is currently set to 50 items per page. When doing a "Bulk Edit" (e.g. Rows 1 to 50), should it apply *only* to the currently filtered assets visible on the table, or the entire filtered dataset? *I am proposing it applies only to the fetched/filtered rows based on their visual row number (1 to X).*

## Proposed Changes

---

### Frontend UI & Logic (`inventory-setup.blade.php`)
We will introduce a new step `#stepInventoryEdit` that activates when the user selects "Inventory Management" from the main menu.

#### [MODIFY] `resources/views/inventory-setup.blade.php`
- **New Step Container (`#stepInventoryEdit`)**: Add the Alpine.js filter component (matching `download-reports.blade.php`) at the top.
- **Interactive Grid**: Below the filters, render an Excel-like grid using `.xls-th` and `.xls-td` typeboxes.
- **State Management**:
  - `editRowsData`: An array holding the fetched rows.
  - `originalRowsData`: A deep copy of the fetched rows to detect changes.
  - `undoStack`: An array pushing state objects `{ rowId, column, oldValue, newValue, type: 'single'|'bulk' }` every time a cell is changed.
- **Update Badge Logic**: An `oninput` handler `syncEditState(rowId, colName, value)` that compares the new value against `originalRowsData`. If different, a `<span class="update-badge">UPDATE</span>` is injected into the cell's UI.
- **Bulk Edit Modal**: 
  - A new modal containing dropdowns for: *Column to Edit*, *New Value*, *Row Start*, and *Row End*.
  - Applying this pushes a bulk action to the `undoStack` and updates the UI grid.
- **Pagination**: Logic to slice `editRowsData` into chunks of 50 for the table view.

### Backend Controllers & Endpoints
We need a dedicated API to fetch the precise IDs needed for updating, and an endpoint to process the saves.

#### [MODIFY] `routes/web.php`
- Add `GET /api/inventory/edit-preview` (to fetch the editable grid data).
- Add `POST /inventory-setup/edit-batch` (to save the modified rows).

#### [MODIFY] `app/Http/Controllers/ReportDownloadController.php`
- Add a new method `getEditPreview(Request $request)`. This will mirror `getPreview()` but ensure `asset_distributions.id as dist_id` and `asset_sources.id as src_id` are explicitly selected so the frontend knows exactly which database rows to update.

#### [NEW] `app/Http/Controllers/InventoryEditController.php` (or merge into `InventorySetupController.php`)
- Add `updateBatch(Request $request)`:
  - Iterates over the submitted array of modifications.
  - Groups updates for `asset_sources` (e.g., Description, Cost, Quantity) and `asset_distributions` (e.g., Property Number, Location).
  - Uses `DB::transaction()` to apply updates.
  - Inserts a single summarized `system_logs` entry (e.g., "Bulk Edit: Updated 45 inventory records").

## Verification Plan

### Automated Tests
- N/A (Standard manual browser testing)

### Manual Verification
1. Navigate to Inventory Setup -> Inventory Management.
2. Filter for a specific school and classification.
3. Verify the grid populates with existing data.
4. Modify a specific cell (e.g., Description). Verify the "UPDATE" badge appears.
5. Click "Undo" and verify the cell reverts and the badge disappears.
6. Open "Bulk Edit", select "Category", set to "Furniture", apply to rows 1-10. Verify rows update.
7. Click "Save Changes", accept the SweetAlert, and check the database / System Logs to ensure the changes were permanently saved.
