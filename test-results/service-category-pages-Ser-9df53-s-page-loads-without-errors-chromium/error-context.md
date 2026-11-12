# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: service-category-pages.spec.ts >> Service Category Pages >> main services page loads without errors
- Location: tests\e2e\service-category-pages.spec.ts:59:3

# Error details

```
Error: expect(page).toHaveTitle(expected) failed

Expected pattern: /Services/
Received string:  "Smart African Market"
Timeout: 5000ms

Call log:
  - Expect "toHaveTitle" with timeout 5000ms
    12 × locator resolved to <html lang="en">…</html>
       - unexpected value "Smart African Market"

```

```yaml
- navigation:
  - link "Smart African Market SmartAfricanMarketplace":
    - /url: http://localhost:8000
    - img "Smart African Market"
    - text: SmartAfricanMarketplace
  - link "Home":
    - /url: http://localhost:8000
  - link "Shop":
    - /url: http://localhost:8000/shop
  - link "Pricing":
    - /url: http://localhost:8000/pricing
  - link "Salons":
    - /url: http://localhost:8000/salon
  - link "Services":
    - /url: http://localhost:8000/services
  - link "Blog":
    - /url: http://localhost:8000/blog
  - link "About":
    - /url: http://localhost:8000/about
  - link "Contact":
    - /url: http://localhost:8000/contact
  - link "Login":
    - /url: http://localhost:8000/auth/login
    - img
    - text: Login
  - button "Change language":
    - img
    - text: English
- main:
  - heading "Services" [level=1]
  - paragraph: Discover and book professional services from vetted vendors across beauty, automotive, home, and commercial categories.
  - text: 0+ Services Available 4+ Categories 100% Professional Vendors
  - heading "All Services" [level=2]
  - paragraph: 0 services available
  - link "Clear All":
    - /url: http://localhost:8000/services
  - complementary "Service filters and categories":
    - text: Search services
    - textbox "Search services":
      - /placeholder: Search services...
    - text: Category Type
    - combobox "Category Type":
      - option "All Categories" [selected]
      - option "Beauty"
      - option "Automotive"
      - option "Home"
      - option "Commercial"
    - text: Gender Target
    - combobox "Gender Target":
      - option "All Genders" [selected]
      - option "Men"
      - option "Women"
      - option "Unisex"
    - button "Apply Filters"
    - heading "Service Categories" [level=3]
    - text: 4 categories
    - navigation "Service categories":
      - link "Beauty & Grooming 2":
        - /url: http://localhost:8000/services/beauty-grooming
      - link "Automotive 3":
        - /url: http://localhost:8000/services/automotive
      - link "Home Services 3":
        - /url: http://localhost:8000/services/home-services
      - link "Commercial Services 2":
        - /url: http://localhost:8000/services/commercial-services
    - heading "Popular Categories" [level=4]
    - text: Beauty & Grooming 0 services Automotive 0 services Home Services 0 services Commercial Services 0 services
- contentinfo:
  - heading "Smart African Market - Go to homepage Smart African Market" [level=4]:
    - link "Smart African Market - Go to homepage":
      - /url: http://localhost:8000
      - img "Smart African Market"
    - text: Smart African Market
  - paragraph: Connecting cultures through authentic African products and services.
  - img "Scan to download mobile app"
  - heading "Download App" [level=6]
  - paragraph: Scan QR code or click below
  - link "Android":
    - /url: http://localhost:8000/mobile
  - link "iOS":
    - /url: http://localhost:8000/mobile
  - heading "Quick Links" [level=5]
  - list:
    - listitem:
      - link "Shop":
        - /url: http://localhost:8000/shop
    - listitem:
      - link "Salon Services":
        - /url: http://localhost:8000/salon
    - listitem:
      - link "Blog":
        - /url: http://localhost:8000/blog
    - listitem:
      - link "About Us":
        - /url: http://localhost:8000/about
  - heading "Customer Service" [level=5]
  - list:
    - listitem:
      - link "Contact Us":
        - /url: http://localhost:8000/contact
    - listitem:
      - link "FAQs":
        - /url: http://localhost:8000/faq
    - listitem:
      - link "Terms of Service":
        - /url: http://localhost:8000/terms
    - listitem:
      - link "Privacy Policy":
        - /url: http://localhost:8000/privacy
  - heading "Newsletter" [level=5]
  - text: Your email
  - textbox "Your email"
  - button "Subscribe"
- paragraph: © 2025-2026 Smart African Market. All rights reserved.
- paragraph:
  - text: Powered by
  - link "CromStel IT Solutions":
    - /url: https://cromstelit.com
- button "Scroll to top":
  - img
- button "Chat Support"
```

# Test source

```ts
  1   | import { test, expect } from '@playwright/test';
  2   | 
  3   | test.describe('Service Category Pages', () => {
  4   |   // All parent category slugs
  5   |   const parentCategories = [
  6   |     'beauty-grooming',
  7   |     'automotive',
  8   |     'home-services',
  9   |     'commercial-services',
  10  |   ];
  11  | 
  12  |   // All sub-category slugs
  13  |   const subCategories = [
  14  |     'mens-salon-services',
  15  |     'womens-salon-services',
  16  |     'car-washing-bay',
  17  |     'car-garage',
  18  |     'mechanic-services',
  19  |     'painter-services',
  20  |     'electrician-services',
  21  |     'home-assistance-services',
  22  |     'facility-management-services',
  23  |     'construction-services',
  24  |   ];
  25  | 
  26  |   // All leaf category slugs
  27  |   const leafCategories = [
  28  |     'mens-haircut',
  29  |     'mens-shave-beard-trim',
  30  |     'mens-grooming-package',
  31  |     'womens-haircut',
  32  |     'womens-hair-coloring',
  33  |     'womens-hair-treatment',
  34  |     'womens-makeup',
  35  |     'womens-nail-services',
  36  |     'exterior-car-wash',
  37  |     'interior-car-detailing',
  38  |     'car-wax-polish',
  39  |     'full-car-detailing',
  40  |     'oil-change-service',
  41  |     'brake-service',
  42  |     'ac-service-repair',
  43  |     'engine-diagnostics',
  44  |     'general-vehicle-maintenance',
  45  |     'fleet-maintenance',
  46  |     'interior-painting',
  47  |     'exterior-painting',
  48  |     'home-electrical-wiring',
  49  |     'commercial-electrical',
  50  |     'home-cleaning',
  51  |     'caregiving-services',
  52  |     'home-cooking',
  53  |     'building-cleaning',
  54  |     'facility-maintenance',
  55  |     'residential-construction',
  56  |     'commercial-construction',
  57  |   ];
  58  | 
  59  |   test('main services page loads without errors', async ({ page }) => {
  60  |     const response = await page.goto('/services');
  61  |     expect(response.status()).toBe(200);
> 62  |     await expect(page).toHaveTitle(/Services/);
      |                        ^ Error: expect(page).toHaveTitle(expected) failed
  63  |   });
  64  | 
  65  |   test.describe('Parent category pages', () => {
  66  |     for (const slug of parentCategories) {
  67  |       test(`loads /services/${slug} without errors`, async ({ page }) => {
  68  |         const response = await page.goto(`/services/${slug}`);
  69  |         expect(response.status()).toBe(200);
  70  |         await expect(page).toHaveTitle(/Services/);
  71  |       });
  72  |     }
  73  |   });
  74  | 
  75  |   test.describe('Sub-category pages', () => {
  76  |     for (const slug of subCategories) {
  77  |       test(`loads /services/${slug} without errors`, async ({ page }) => {
  78  |         const response = await page.goto(`/services/${slug}`);
  79  |         expect(response.status()).toBe(200);
  80  |         await expect(page).toHaveTitle(/Services/);
  81  |       });
  82  |     }
  83  |   });
  84  | 
  85  |   test.describe('Leaf category pages', () => {
  86  |     for (const slug of leafCategories) {
  87  |       test(`loads /services/${slug} without errors`, async ({ page }) => {
  88  |         const response = await page.goto(`/services/${slug}`);
  89  |         expect(response.status()).toBe(200);
  90  |         await expect(page).toHaveTitle(/Services/);
  91  |       });
  92  |     }
  93  |   });
  94  | 
  95  |   test('leaf category pages show services when available', async ({ page }) => {
  96  |     // Visit a leaf category page that should have services
  97  |     const response = await page.goto('/services/mens-haircut');
  98  |     expect(response.status()).toBe(200);
  99  |     
  100 |     // Check for either services listed or a "no services" message
  101 |     const hasServiceCards = await page.locator('.service-card').count() > 0;
  102 |     const hasNoServicesMessage = await page.locator('text=No services found').count() > 0;
  103 |     const hasServicesHeading = await page.locator('h1').count() > 0;
  104 |     
  105 |     expect(hasServiceCards || hasNoServicesMessage || hasServicesHeading).toBe(true);
  106 |   });
  107 | 
  108 |   test('category page has proper heading hierarchy', async ({ page }) => {
  109 |     await page.goto('/services/beauty-grooming');
  110 |     await expect(page.locator('h1')).toHaveCount(1);
  111 |   });
  112 | 
  113 |   test('category page links are functional', async ({ page }) => {
  114 |     await page.goto('/services/beauty-grooming');
  115 |     
  116 |     // Click on a sub-category link
  117 |     const menSalonLink = page.locator('a[href="/services/mens-salon-services"]').first();
  118 |     if (await menSalonLink.count() > 0) {
  119 |       await menSalonLink.click();
  120 |       await expect(page).toHaveURL(/.*\/services\/mens-salon-services/);
  121 |     }
  122 |   });
  123 | });
  124 | 
```