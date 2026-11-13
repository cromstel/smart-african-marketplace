• Proposed Plan
                                                                                                                                                               
  ## Add “Marketplace Management” admin menu (multivendor operations hub)                                                                                      
                                                                                                                                                               
  ### Summary                                                                                                                                                  
                                                                                                                                                               
  Create a new Admin sidebar flyout menu called Marketplace Management that consolidates all marketplace operations (vendors, services, subscriptions/         
  packages) and adds missing-but-already-implemented admin links (refunds, payouts, withdrawals, shipping settings). Update user-facing documentation to       
  reflect the new navigation.                                                                                                                                  
                                                                                                                                                               
  ———                                                                                                                                                          
                                                                                                                                                               
  ## Scope (locked)                                                                                                                                            

  - Add/organize navigation in the admin sidebar.                                                                                                              
  - No new DB tables, migrations, models, or business logic.                                                                                                   
  - Use existing admin routes/controllers/views already present in the repo.                                                                                   
                                                                                                                                                               
  ———                                                                                                                                                          
                                                                                                                                                               
  ## Implementation details (decision-complete)                                                                                                                
                                                                                                                                                               
  ### 1) Admin sidebar: add consolidated menu + move items under it                                                                                            
                                                                                                                                                               
  File: resources/views/admin/layout.blade.php                                                                                                                 
                                                                                                                                                               
  #### 1.1 Insert new flyout button                                                                                                                            
                                                                                                                                                               
  Place immediately after the existing Orders link and before the current Vendors link (which will be removed).
                                                                                                                                                               
  Add a new flyout button:
                                                                                                                                                               
  - Label: Marketplace Management                                                                                                                              
  - data-flyout-target="marketplace-submenu"                                                                                                                   
  - onclick="toggleMarketplaceMenu()"
  - Active state (bg-brand-gradient) when any of these route groups match:                                                                                     
      - admin.vendors.*                                                                                                                                        
      - admin.vendor-verifications.*                                                                                                                           
      - admin.subscriptions.*                                                                                                                                  
      - admin.coupons.*                                                                                                                                        
      - admin.salons.*                                                                                                                                         
      - admin.salon-stylists.*                                                                                                                                 
      - admin.salon-services.*                                                                                                                                 
      - admin.salon-categories.*                                                                                                                               
      - admin.bookings.*                                                                                                                                       
      - admin.shipping-settings.*                                                                                                                              
      - admin.refunds.*
      - admin.payouts.*
      - admin.withdrawals.*                                                                                                                                    
                                                                                                                                                               
  #### 1.2 Add the submenu container                                                                                                                           
                                                                                                                                                               
  Create:
                                                                                                                                                               
  - <div id="marketplace-submenu" ...> using the same classes/pattern as existing submenus (e.g., subscriptions-submenu, salon-submenu).                       
                                                                                                                                                               
  Inside, include these grouped sections (use <hr class="my-2 border-gray-700"> between groups):
                                                                                                                                                               
  A) Vendors                                                                                                                                                   
                                                                                                                                                               
  - Vendors → route('admin.vendors.index')                                                                                                                     
  - Vendor Verification → route('admin.vendor-verifications.index')                                                                                            
  - Coupons → route('admin.coupons.index') (promotions are vendor-relevant)
                                                                                                                                                               
  B) Services & Bookings (generic structure, salon-backed today)
                                                                                                                                                               
  - Salons → route('admin.salons.index')                                                                                                                       
  - Stylists → route('admin.salon-stylists.index')                                                                                                             
  - Services → route('admin.salon-services.index')                                                                                                             
  - Categories → route('admin.salon-categories.index')                                                                                                         
  - Bookings → route('admin.bookings.index')                                                                                                                   
  - (If you want the calendar surfaced) Booking Calendar → route('admin.bookings.calendar')                                                                    
                                                                                                                                                               
  C) Subscription Packages                                                                                                                                     
                                                                                                                                                               
  - Plans → route('admin.subscriptions.plans.index')                                                                                                           
  - Subscribers → route('admin.subscriptions.subscribers')                                                                                                     
                                                                                                                                                               
  D) Finance & Risk                                                                                                                                            
                                                                                                                                                               
  - Refund Requests → route('admin.refunds.index')                                                                                                             
  - Payouts → route('admin.payouts.index')                                                                                                                     
  - Withdrawals → route('admin.withdrawals.index')                                                                                                             
                                                                                                                                                               
  E) Logistics                                                                                                                                                 

  - Shipping Settings → route('admin.shipping-settings.index')
                                                                                                                                                               
  For each submenu link, set the active class using request()->routeIs(...) consistent with existing submenu items.                                            
                                                                                                                                                               
  #### 1.3 Remove (or relocate) existing top-level items to avoid duplicates
                                                                                                                                                               
  Still in resources/views/admin/layout.blade.php, delete these existing sidebar entries because they are now represented inside Marketplace Management:       
                                                                                                                                                               
  - The top-level Vendors link (route('admin.vendors.index'))                                                                                                  
  - The top-level Vendor Verification link (route('admin.vendor-verifications.index'))                                                                         
  - The entire Salon flyout block (salon-submenu)                                                                                                              
  - The top-level Coupons link (route('admin.coupons.index'))                                                                                                  
  - The entire Subscriptions flyout block (subscriptions-submenu)                                                                                              
                                                                                                                                                               
  ### 2) Mobile toggle function for the new menu                                                                                                               
                                                                                                                                                               
  File: resources/views/admin/layout.blade.php (script section)                                                                                                
                                                                                                                                                               
  Add:                                                                                                                                                         
                                                                                                                                                               
  - function toggleMarketplaceMenu() { if (window.innerWidth >= 1024) return; const submenu = document.getElementById('marketplace-submenu');                  
    submenu.classList.toggle('hidden'); }                                                                                                                      
                                                                                                                                                               
  No other JS changes are required because the desktop flyout logic automatically includes any element with data-flyout-target.                                
                                                                                                                                                               
  ———                                                                                                                                                          
                                                                                                                                                               
  ## Documentation updates (required)                                                                                                                          
                                                                                                                                                               
  Update docs to reflect that marketplace operations are now accessed via Marketplace Management in the admin sidebar.

  ### 3) Admin guide                                                                                                                                           
                                                                                                                                                               
  File: documentation/admin-guide.md                                                                                                                           
                                                                                                                                                               
  - Add a new section: ## Marketplace Management
  - Include a bullet list mapping the menu to URLs (admin routes):                                                                                             
      - Vendors: /admin/vendors                                                                                                                                
      - Vendor verification: /admin/vendor-verifications                                                                                                       
      - Services (salon): /admin/salons, /admin/salon-services, /admin/salon-stylists, /admin/salon-categories                                                 
      - Bookings: /admin/bookings (and /admin/bookings/calendar if surfaced)                                                                                   
      - Subscription packages: /admin/subscriptions/plans, /admin/subscriptions/subscribers                                                                    
      - Finance: /admin/refunds, /admin/payouts, /admin/withdrawals                                                                                            
      - Shipping: /admin/shipping-settings                                                                                                                     
  - Keep the rest of the doc intact; this is a navigation clarification, not a policy rewrite.                                                                 
                                                                                                                                                               
  ### 4) v2 onboarding + UI flows                                                                                                                              
                                                                                                                                                               
  Files:                                                                                                                                                       
                                                                                                                                                               
  - documentation/v2/onboarding-admin.md
      - Update “UI Walkthrough” steps to explicitly say: “Open Marketplace Management for vendors/subscriptions/services.”                                     
      - Update Day-0 checklist items similarly.                                                                                                                
  - documentation/v2/ui-flows.md                                                                                                                               
      - Update “Admin Control Flow” step 5 to reference Marketplace Management.                                                                                

  ### 5) Documentation index date                                                                                                                              
                                                                                                                                                               
  File: documentation/README.md                                                                                                                                
                                                                                                                                                               
  - Update “Last updated” to 2026-02-28.                                                                                                                       
                                                                                                                                                               
  ———                                                                                                                                                          
                                                                                                                                                               
  ## Testing / verification (manual acceptance)                                                                                                                
                                                                                                                                                               
  1. Login as admin, open /admin/dashboard.                                                                                                                    
  2. Verify sidebar shows Marketplace Management.                                                                                                              
  3. Desktop: hover/click to open flyout and confirm all links appear.
  4. Mobile width: tap Marketplace Management and confirm it expands/collapses.                                                                                
  5. Click each submenu link and confirm it routes correctly (no 404, no auth redirect):                                                                       
      - Vendors, vendor verification, salons/services/stylists/categories, bookings, plans/subscribers, refunds/payouts/withdrawals, shipping settings.        
  6. Confirm the active highlight state works when visiting any of the above pages.
                                                                                                                                                               
  ## Assumptions
    subsystems.