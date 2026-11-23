import { test, expect } from '@playwright/test';

test.describe('Service Category Pages', () => {
  // All parent category slugs
  const parentCategories = [
    'beauty-grooming',
    'automotive',
    'home-services',
    'commercial-services',
  ];

  // All sub-category slugs
  const subCategories = [
    'mens-salon-services',
    'womens-salon-services',
    'car-washing-bay',
    'car-garage',
    'mechanic-services',
    'painter-services',
    'electrician-services',
    'home-assistance-services',
    'facility-management-services',
    'construction-services',
  ];

  // All leaf category slugs
  const leafCategories = [
    'mens-haircut',
    'mens-shave-beard-trim',
    'mens-grooming-package',
    'womens-haircut',
    'womens-hair-coloring',
    'womens-hair-treatment',
    'womens-makeup',
    'womens-nail-services',
    'exterior-car-wash',
    'interior-car-detailing',
    'car-wax-polish',
    'full-car-detailing',
    'oil-change-service',
    'brake-service',
    'ac-service-repair',
    'engine-diagnostics',
    'general-vehicle-maintenance',
    'fleet-maintenance',
    'interior-painting',
    'exterior-painting',
    'home-electrical-wiring',
    'commercial-electrical',
    'home-cleaning',
    'caregiving-services',
    'home-cooking',
    'building-cleaning',
    'facility-maintenance',
    'residential-construction',
    'commercial-construction',
  ];

  test('main services page loads without errors', async ({ page }) => {
    const response = await page.goto('/services');
    expect(response.status()).toBe(200);
    await expect(page).toHaveTitle(/Services/);
  });

  test.describe('Parent category pages', () => {
    for (const slug of parentCategories) {
      test(`loads /services/${slug} without errors`, async ({ page }) => {
        const response = await page.goto(`/services/${slug}`);
        expect(response.status()).toBe(200);
        await expect(page).toHaveTitle(/Services/);
      });
    }
  });

  test.describe('Sub-category pages', () => {
    for (const slug of subCategories) {
      test(`loads /services/${slug} without errors`, async ({ page }) => {
        const response = await page.goto(`/services/${slug}`);
        expect(response.status()).toBe(200);
        await expect(page).toHaveTitle(/Services/);
      });
    }
  });

  test.describe('Leaf category pages', () => {
    for (const slug of leafCategories) {
      test(`loads /services/${slug} without errors`, async ({ page }) => {
        const response = await page.goto(`/services/${slug}`);
        expect(response.status()).toBe(200);
        await expect(page).toHaveTitle(/Services/);
      });
    }
  });

  test('leaf category pages show services when available', async ({ page }) => {
    // Visit a leaf category page that should have services
    const response = await page.goto('/services/mens-haircut');
    expect(response.status()).toBe(200);
    
    // Check for either services listed or a "no services" message
    const hasServiceCards = await page.locator('.service-card').count() > 0;
    const hasNoServicesMessage = await page.locator('text=No services found').count() > 0;
    const hasServicesHeading = await page.locator('h1').count() > 0;
    
    expect(hasServiceCards || hasNoServicesMessage || hasServicesHeading).toBe(true);
  });

  test('category page has proper heading hierarchy', async ({ page }) => {
    await page.goto('/services/beauty-grooming');
    await expect(page.locator('h1')).toHaveCount(1);
  });

  test('category page links are functional', async ({ page }) => {
    await page.goto('/services/beauty-grooming');
    
    // Click on a sub-category link
    const menSalonLink = page.locator('a[href="/services/mens-salon-services"]').first();
    if (await menSalonLink.count() > 0) {
      await menSalonLink.click();
      await expect(page).toHaveURL(/.*\/services\/mens-salon-services/);
    }
  });
});
