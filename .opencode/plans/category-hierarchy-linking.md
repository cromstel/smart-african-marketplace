# Category Hierarchy & Linking Plan

## Objective
Ensure all vendor service categories and sub-categories are properly organized hierarchically (parent → child) and linked to their respective pages.

## Current State
- `categories` table (products): Already has `parent_id` with hierarchical structure (food → subcategories, clothing → subcategories, salon → subcategories)
- `vendor_service_categories` table: **Flat structure** - no `parent_id`, all 29 categories at same level
- `VendorServiceCategory` model: Missing parent/children relationships
- Public services page (`/services`): Shows flat category filter, no hierarchical navigation
- `/services/{category}` route: Works for individual categories but no sub-category tree display

## Missing Implementations

### Phase 1: Database Schema
- [ ] Add `parent_id` column to `vendor_service_categories` table (migration)
- [ ] Add foreign key constraint for self-referencing parent_id
- [ ] Add index on parent_id

### Phase 2: Model Updates
- [ ] Add `parent()` BelongsTo relationship to `VendorServiceCategory`
- [ ] Add `children()` HasMany relationship to `VendorServiceCategory`
- [ ] Add `fullPath()` method
- [ ] Add `scopeParents()` and `scopeActive()` scopes
- [ ] Add `isDescendantOf()` method

### Phase 3: Seeder Reorganization
- [ ] Create parent categories: Beauty & Grooming, Automotive, Home Services, Commercial Services
- [ ] Create sub-categories under each parent:
  - Beauty & Grooming → Men's Salon, Women's Salon
  - Automotive → Car Washing Bay, Car Garage
  - Home Services → Painter, Electrician, Home Assistance
  - Commercial Services → Facility Management, Construction, Mechanic (Fleet)
- [ ] Create leaf services under each sub-category (the current 29 categories become leaf nodes)
- [ ] Update `VendorServiceCategorySeeder` with hierarchical structure

### Phase 4: Controller Updates
- [ ] Update `Public\ServiceController@index` to load category tree with parents and children
- [ ] Update `Public\ServiceController@byCategory` to load sub-categories when viewing a parent

### Phase 5: View Updates
- [ ] Update `resources/views/public/services/index.blade.php` - add hierarchical category sidebar (like shop/index.blade.php)
- [ ] Update `resources/views/public/services/category.blade.php` - show sub-categories when viewing parent category
- [ ] Ensure all category links point to correct `/services/{slug}` routes

### Phase 6: Verification
- [ ] Run migrations and seeders
- [ ] Run Pint formatting
- [ ] Run full test suite
- [ ] Verify all category links work correctly
