# DEPED ZAMBOANGA CITY INVENTORY SYSTEM - ARCHITECTURAL DIRECTIVES

## 1. DATABASE GROUND TRUTH
- **Schema Focus:** `building_records` contains separate `address` (varchar) and `location` (varchar) columns. `acquisition_cost` is `decimal(15,2)`.
- **Core Entities:** `users`, `items`, `categories`, `classifications`, `schools`, `districts`, `quadrants`, `asset_assignments`, `building_records`, `building_specs`, `building_types`.

## 2. SECURITY & BACKEND PROTOCOLS
- **Mass-Assignment:** `approved` column in `User` model must NEVER be in `$fillable`.
- **N+1 Prevention:** Pluck relationship IDs into local arrays for batch operations (e.g., `storeBatch`).
- **Authorization:** Explicit `abort(403)` blocks for mutating methods in `AssetController` and similar sensitive endpoints.
- **Strict Typing (Larastan L5):**
    - Always use `Model::query()` for Eloquent builders.
    - Always use `$request->input('key')` for request data.
    - Use inline `/** @var \App\Models\User $user */` for `Auth::user()` calls.

## 3. UI/UX DESIGN SYSTEM (TAILWIND)
- **Palette:** Tailwind `slate` or `zinc` (e.g., `bg-slate-900`, `bg-slate-800`, `text-slate-300`).
- **Grid:** Strict 8px grid (multipliers of 8px/`p-2`, `p-4`, etc.).
- **Density:** High information density; minimal unnecessary whitespace.
- **Borders:** 1px micro-borders (`border-slate-700/50`) with transitions (`duration-200`).

## 4. EXECUTION GUIDELINES
- No conversational filler.
- Only output mutated lines in code blocks (use `// ...` for unchanged blocks).
- Prioritize structural directives over transient chat inputs.
