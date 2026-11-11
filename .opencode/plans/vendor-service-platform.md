# Vendor Service Platform Implementation Plan

**Project:** Smart African Marketplace  
**Version:** 2.0  
**Date:** November 2026  
**Status:** Planned  
**Location:** `.opencode/plans/vendor-service-platform.md`

---

## 🎯 Objective

Extend the Smart African Marketplace to support multiple vendor types advertising and managing their services on the platform, including salon/barber services (Men/Women), automotive (Car Washing/Garage), construction, electrical, painting, facility management, and home assistance services.

---

## 🏗️ Vendor Taxonomy

### 16 Vendor Types

| ID | Vendor Type | Gender Target | Category |
|----|------------|---------------|----------|
| 1 | Private Painter | N/A | Home/Commercial |
| 2 | Corporate Painter | N/A | Home/Commercial |
| 3 | Private Facility Management | N/A | Commercial |
| 4 | Corporate Facility Management | N/A | Commercial |
| 5 | Private Construction | N/A | Residential/Commercial |
| 6 | Corporate Construction | N/A | Commercial |
| 7 | Private Home Assistance | N/A | Residential |
| 8 | Corporate Home Assistance | N/A | Residential |
| 9 | Private Mechanic | N/A | Automotive |
| 10 | Corporate Mechanic | N/A | Automotive |
| 11 | Private Electrician | N/A | Electrical |
| 12 | Corporate Electrician | N/A | Electrical |
| 13 | Men Salon | Men | Beauty/Grooming |
| 14 | Women Salon | Women | Beauty/Grooming |
| 15 | Car Washing Bay | N/A | Automotive |
| 16 | Car Garage | N/A | Automotive |

### Service Categories

- Men's Grooming (haircut, shave, beard trim)
- Women's Beauty (haircolor, styling, treatment)
- Car Detailing (exterior wash, interior vacuum, wax)
- Car Repair (oil change, brake repair, AC)
- Interior Painting, Exterior Painting
- Home Electrical, Commercial Electrical
- Vehicle Maintenance, Fleet Management
- Cleaning, Maintenance, Repairs
- Residential Construction, Commercial Development
- Home Cleaning, Cooking, Caregiving

---

## 🗄️ Database Schema Changes

### Migration 1: Add `vendor_type` to `vendors` table

```php
Schema::table('vendors', function (Blueprint $table) {
    $table->enum('vendor_type', [
        'private_painter', 'corporate_painter',
        'private_facility_management', 'corporate_facility_management',
        'private_construction', 'corporate_construction',
        'private_home_assistance', 'corporate_home_assistance',
        'private_mechanic', 'corporate_mechanic',
        'private_electrician', 'corporate_electrician',
        'men_salon', 'women_salon',
        'car_washing_bay', 'car_garage',
    ])->default('private_painter');
    $table->boolean('is_active')->default(true);
    $table->timestamps(); -- if not existing
});
```

### Migration 2: Create `vendor_service_categories` table

```php
Schema::create('vendor_service_categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->enum('category_type', ['beauty', 'automotive', 'home', 'commercial']);
    $table->enum('gender_target', ['men', 'women', 'unisex'])->nullable();
    $table->timestamps();
});
```

### Migration 3: Create `vendor_services` table

```php
Schema::create('vendor_services', function (Blueprint $table) {
    $table->id();
    $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
    $table->foreignId('vendor_service_category_id')->constrained()->onDelete('cascade');
    $table->string('name');
    $table->text('description')->nullable();
    $table->decimal('price', 10, 2);
    $table->integer('duration')->default(30);
    $table->enum('status', ['available', 'booked', 'unavailable'])->default('available');
    $table->timestamps();
});
```

### Migration 4: Add `service_type` to `salon_services` table

```php
Schema::table('salon_services', function (Blueprint $table) {
    $table->foreignId('vendor_service_category_id')->nullable()->constrained()->onDelete('cascade');
    $table->enum('service_type', ['men', 'women', 'unisex', 'automotive', 'home', 'commercial'])->default('unisex');
    $table->boolean('is_active')->default(true);
});
```

---

## 🔌 Routes

### Public Routes

```
GET    /services                                          → ServiceController@index
GET    /services/{category_slug}                          → ServiceController@byCategory
GET    /services/{service}                                → ServiceController@show
GET    /vendors/{vendor}                                  → VendorController@show
```

### Vendor Console Routes (`/vendor`)

```
GET    /vendor/dashboard                                  → Vendor\DashboardController@index
GET    /vendor/services                                   → Vendor\ServiceController@index
GET    /vendor/services/create                            → Vendor\ServiceController@create
POST   /vendor/services                                   → Vendor\ServiceController@store
GET    /vendor/services/{service}/edit                    → Vendor\ServiceController@edit
PUT    /vendor/services/{service}                         → Vendor\ServiceController@update
DELETE /vendor/services/{service}                         → Vendor\ServiceController@destroy
GET    /vendor/bookings                                   → Vendor\BookingController@index
GET    /vendor/bookings/{booking}                         → Vendor\BookingController@show
GET    /vendor/profile                                    → Vendor\ProfileController@edit
PUT    /vendor/profile                                    → Vendor\ProfileController@update
```

### Admin Console Routes (extend existing)

```
GET    /admin/vendors                                     → Admin\VendorController@index
GET    /admin/vendors/{vendor}                            → Admin\VendorController@show
GET    /admin/vendor-types                                → Admin\VendorTypeController@index
```

---

## 👥 Controllers

### Public Controllers

| Controller | Methods | Purpose |
|------------|---------|---------|
| `Public\ServiceController` | index, byCategory, show | Browse public services with filters |
| `Public\VendorController` | show | Vendor public profile page |

### Vendor Console Controllers

| Controller | Methods | Purpose |
|------------|---------|---------|
| `Vendor\DashboardController` | index | Vendor overview with stats |
| `Vendor\ServiceController` | index/create/store/edit/update/destroy | Manage vendor services |
| `Vendor\BookingController` | index/show | View bookings |
| `Vendor\ProfileController` | edit/update | Vendor profile management |

### Admin Controllers (extend)

| Controller | Methods | Purpose |
|------------|---------|---------|
| `Admin\VendorController` | index/show | Manage all vendors |
| `Admin\VendorTypeController` | index | Vendor type management |

---

## 📱 Views

### Public Views

```
resources/views/public/services/
├── index.blade.php    # Grid with filters (type, category, gender)
├── category.blade.php # Category-specific service listing
└── show.blade.php     # Individual service details
```

### Vendor Console Views

```
resources/views/vendor/
├── layouts/dashboard.blade.php
├── dashboard/
│   └── index.blade.php
├── services/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── _form.blade.php
├── bookings/
│   ├── index.blade.php
│   └── show.blade.php
└── profile/
    └── edit.blade.php
```

---

## 🧩 Implementation Phases

### Phase 1: Database Foundation (Days 1-2)
- [ ] Migration: `vendor_type` enum on vendors table
- [ ] Migration: `vendor_service_categories` table
- [ ] Migration: `vendor_services` table
- [ ] Migration: `service_type` + `vendor_service_category_id` on salon_services
- [ ] Seeder: 16 vendor types + 30+ service categories
- [ ] Update Vendor model with relationships

### Phase 2: Public Service Browsing (Days 3-4)
- [ ] Public ServiceController (index, byCategory, show)
- [ ] Public routes registration
- [ ] `/services` blade view with category/gender filters
- [ ] Service cards with type badges (Men/Women/Automotive etc.)
- [ ] Update `/salon` route to redirect or coexist with `/services`

### Phase 3: Vendor Service CRUD (Days 5-6)
- [ ] Vendor\ServiceController (all methods)
- [ ] Vendor service views (index, create, edit, form)
- [ ] Vendor routes registration under `/vendor`
- [ ] Validation: service belongs to correct category type

### Phase 4: Vendor Dashboard (Days 7-8)
- [ ] Vendor\DashboardController
- [ ] Vendor dashboard layout (reuse sidebar pattern)
- [ ] Dashboard overview stats view
- [ ] Booking management views
- [ ] Profile edit view

### Phase 5: Admin Platform Extension (Days 9-10)
- [ ] Admin vendor management views
- [ ] Vendor type administration
- [ ] Platform-wide service oversight
- [ ] Vendor verification workflow

### Phase 6: Testing & Polish (Days 11-12)
- [ ] Unit tests for all new controllers
- [ ] Feature tests for service browsing
- [ ] PHP int Pint formatting
- [ ] Full test suite run (569+ new tests)
- [ ] UI/UX polish

---

## 📋 Todo Checklist

### Phase 1: Database Foundation
- [ ] Create migration: `vendor_type` enum on vendors table
- [ ] Create migration: `vendor_service_categories` table
- [ ] Create migration: `vendor_services` table
- [ ] Create migration: `service_type` column on salon_services
- [ ] Create seeder: vendor types and service categories
- [ ] Update Vendor model with service_category relationship
- [ ] Run migrations and seeders
- [ ] Verify with `php artisan migrate:fresh --seed`

### Phase 2: Public Service Browsing
- [ ] Create `Public\ServiceController`
- [ ] Register public `/services` routes
- [ ] Create `/services` blade view with filters
- [ ] Add gender filter (Men/Women/Unisex) UI
- [ ] Add service type badges
- [ ] Verify public browsing works

### Phase 3: Vendor Service CRUD
- [ ] Create `Vendor\ServiceController`
- [ ] Register vendor service routes
- [ ] Create vendor service index view
- [ ] Create vendor service create view
- [ ] Create vendor service edit view
- [ ] Create shared service form partial
- [ ] Add validation rules per vendor type
- [ ] Verify vendor CRUD works

### Phase 4: Vendor Dashboard
- [ ] Create `Vendor\DashboardController`
- [ ] Create `Vendor\BookingController`
- [ ] Create `Vendor\ProfileController`
- [ ] Create vendor dashboard layout (sticky sidebar)
- [ ] Create dashboard overview view
- [ ] Create bookings index/show views
- [ ] Create profile edit view
- [ ] Verify vendor dashboard works

### Phase 5: Admin Extension
- [ ] Extend Admin vendor management
- [ ] Create vendor type administration
- [ ] Create platform service oversight views
- [ ] Add vendor verification workflow

### Phase 6: Testing & Polish
- [ ] Write unit tests for new controllers
- [ ] Write feature tests for service browsing
- [ ] Run Pint formatting on all new files
- [ ] Run full test suite (target 800+ tests)
- [ ] UI/UX review and polish
- [ ] Commit all changes

---

## ✅ Definition of Done

- [ ] All 16 vendor types supported
- [ ] Public `/services` page with Men/Women/Unisex filters
- [ ] Vendor dashboard with sticky sidebar
- [ ] Vendor can CRUD their services
- [ ] All views use existing sidebar sticky pattern
- [ ] Pint formatting passes
- [ ] 800+ tests passing
- [ ] Routes registered and working
- [ ] Database migrations runnable fresh

---

## 🔄 Parallel Execution Strategy

| Phase | Parallel Tasks |
|-------|---------------|
| Phase 1 | All migrations can run sequentially (dependencies), seeders parallel |
| Phase 2 | Controller + Views + Routes can be parallel |
| Phase 3 | ServiceController + views + routes can be parallel |
| Phase 4 | Dashboard + Booking + Profile controllers + views parallel |
| Phase 5 | Admin views + routes parallel |
| Phase 6 | Testing + Pint + Polish parallel |
