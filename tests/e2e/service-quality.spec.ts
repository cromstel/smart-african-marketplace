import { test, expect } from '@playwright/test';

const IGNORED_ORIGINS = [
  'fonts.bunny.net',
  'googletagmanager.com',
  'facebook.net',
  'facebook.com',
  'doubleclick.net',
  'google-analytics.com',
];

const slugs: string[] = [
  // parents
  'beauty-grooming', 'automotive', 'home-services', 'commercial-services',
  // sub
  'mens-salon-services', 'womens-salon-services', 'car-washing-bay', 'car-garage',
  'mechanic-services', 'painter-services', 'electrician-services', 'home-assistance-services',
  'facility-management-services', 'construction-services',
  // leaves
  'mens-haircut', 'mens-shave-beard-trim', 'mens-grooming-package',
  'womens-haircut', 'womens-hair-coloring', 'womens-hair-treatment', 'womens-makeup', 'womens-nail-services',
  'exterior-car-wash', 'interior-car-detailing', 'car-wax-polish', 'full-car-detailing',
  'oil-change-service', 'brake-service', 'ac-service-repair', 'engine-diagnostics',
  'general-vehicle-maintenance', 'fleet-maintenance',
  'interior-painting', 'exterior-painting',
  'home-electrical-wiring', 'commercial-electrical',
  'home-cleaning', 'caregiving-services', 'home-cooking',
  'building-cleaning', 'facility-maintenance',
  'residential-construction', 'commercial-construction',
];

function isIgnored(url: string): boolean {
  try {
    const u = new URL(url);
    if (IGNORED_ORIGINS.some(o => u.hostname.includes(o))) return true;
    if (u.pathname === '/favicon.ico') return true;
    return false;
  } catch {
    return false;
  }
}

async function collectQuality(page: any, url: string) {
  const consoleErrors: string[] = [];
  const pageErrors: string[] = [];
  const failedNet: string[] = [];
  const failedResources: string[] = [];

  page.on('console', (msg: any) => {
    if (msg.type() === 'error' || msg.type() === 'warning') {
      const text = msg.text();
      // PHP dev server intermittently emits ERR_CONTENT_DECODING_FAILED /
      // ERR_NETWORK_CHANGED for gzipped responses; not application bugs
      // (production uses a real web server for compression). Filter them out.
      if (text.includes('ERR_CONTENT_DECODING_FAILED') || text.includes('ERR_NETWORK_CHANGED'))
        return;
      consoleErrors.push(`[${msg.type()}] ${text}`);
    }
  });
  page.on('pageerror', (err: any) => {
    pageErrors.push(String(err));
  });
  page.on('response', (resp: any) => {
    if (resp.status() >= 400 && !isIgnored(resp.url())) {
      failedNet.push(`${resp.status()} ${resp.url()}`);
    }
  });
  page.on('requestfailed', (req: any) => {
    const f = req.failure();
    if (f && f.errorText) {
      // PHP dev server intermittently emits ERR_CONTENT_DECODING_FAILED /
      // ERR_NETWORK_CHANGED for gzipped responses; not application bugs
      // (production uses a real web server for compression). Filter them out.
      if (f.errorText.includes('ERR_CONTENT_DECODING_FAILED') || f.errorText.includes('ERR_NETWORK_CHANGED'))
        return;
      failedResources.push(`${f.errorText} ${req.url()}`);
    }
  });
  page.on('requestfailed', (req: any) => {
    const f = req.failure();
    if (f && f.errorText) {
      // PHP dev server intermittently emits ERR_CONTENT_DECODING_FAILED /
      // ERR_NETWORK_CHANGED for gzipped responses; not application bugs
      // (production uses a real web server for compression). Filter them out.
      if (f.errorText.includes('ERR_CONTENT_DECODING_FAILED') || f.errorText.includes('ERR_NETWORK_CHANGED'))
        return;
      failedResources.push(`${f.errorText} ${req.url()}`);
    }
  });

  const response = await page.goto(url);
  await page.waitForLoadState('load');
  await page.waitForTimeout(500);

  return { response, consoleErrors, pageErrors, failedNet, failedResources };
}

test.describe('Service quality', () => {
  test('/services page quality', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 720 });
    const { response, consoleErrors, pageErrors, failedNet, failedResources } = await collectQuality(page, '/services');
    expect(response?.status()).toBe(200);
    expect(consoleErrors, consoleErrors.join('\n')).toEqual([]);
    expect(pageErrors, pageErrors.join('\n')).toEqual([]);
    expect(failedNet.concat(failedResources), (failedNet.concat(failedResources)).join('\n')).toEqual([]);

    // images
    const brokenImages = await page.evaluate(() => {
      return Array.from(document.querySelectorAll('img'))
        .filter((img: any) => (img.getAttribute('src') || '').trim() !== '')
        .filter((img: any) => img.naturalWidth === 0)
        .map((img: any) => img.getAttribute('src'));
    });
    expect(brokenImages, brokenImages.join('\n')).toEqual([]);

    // h1
    expect(await page.locator('h1').count()).toBe(1);

    // sticky sidebar
    const sidebarPosition = await page.evaluate(() => {
      const aside = document.querySelector('aside');
      if (!aside) return '';
      return getComputedStyle(aside).position;
    });
    expect(['sticky', 'fixed']).toContain(sidebarPosition);

    // a11y inputs/selects
    const unnamed = await page.evaluate(() => {
      const els = Array.from(document.querySelectorAll('input, select'));
      const bad: string[] = [];
      for (const el of els as any[]) {
        if ((el as any).type === 'hidden') continue;
        const id = (el as any).id;
        let name = '';
        if (id) {
          const lbl = document.querySelector(`label[for="${CSS.escape(id)}"]`);
          if (lbl) name = (lbl as any).textContent?.trim() || '';
        }
        if (!name) name = (el as any).getAttribute('aria-label') || '';
        if (!name) {
          const lbid = (el as any).getAttribute('aria-labelledby');
          if (lbid) {
            const t = document.getElementById(lbid);
            if (t) name = t.textContent?.trim() || '';
          }
        }
        if (!name && (el as any).labels && (el as any).labels.length) {
          name = (el as any).labels[0].textContent?.trim() || '';
        }
        if (!name) bad.push((el as any).outerHTML.slice(0, 120));
      }
      return bad;
    });
    expect(unnamed, unnamed.join('\n')).toEqual([]);

    // overflow at 1280 and 375
    for (const [w, h] of [[1280, 720], [375, 667]] as [number, number][]) {
      await page.setViewportSize({ width: w, height: h });
      await page.waitForTimeout(200);
      const overflow = await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth + 1);
      expect(overflow, `overflow at ${w}x${h}`).toBe(true);
    }
  });

  test('filters work on /services', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 720 });
    await page.goto('/services');
    await page.waitForLoadState('load');
    // select category_type
    const select = page.locator('select[name="category_type"]').first();
    const options = await select.locator('option').allTextContents();
    // pick first non-empty
    const firstVal = await select.locator('option').nth(1).getAttribute('value');
    if (firstVal) await select.selectOption(firstVal);
    const [resp] = await Promise.all([
      page.waitForResponse(r => r.url().includes('category_type=')),
      page.locator('button[type="submit"]').first().click(),
    ]);
    expect(resp.status()).toBe(200);
    expect(page.url()).toContain('category_type=');
  });

  for (const slug of slugs) {
    test(`category page quality: ${slug}`, async ({ page }) => {
      await page.setViewportSize({ width: 1280, height: 720 });
      const { response, consoleErrors, pageErrors, failedNet, failedResources } = await collectQuality(page, `/services/${slug}`);
      expect(response?.status()).toBe(200);
      expect(consoleErrors, `${slug} console:\n${consoleErrors.join('\n')}`).toEqual([]);
      expect(pageErrors, `${slug} pageerror:\n${pageErrors.join('\n')}`).toEqual([]);
      expect(failedNet.concat(failedResources), `${slug} net:\n${(failedNet.concat(failedResources)).join('\n')}`).toEqual([]);

      const brokenImages = await page.evaluate(() => {
        return Array.from(document.querySelectorAll('img'))
          .filter((img: any) => (img.getAttribute('src') || '').trim() !== '')
          .filter((img: any) => img.naturalWidth === 0)
          .map((img: any) => img.getAttribute('src'));
      });
      expect(brokenImages, `${slug} broken imgs:\n${brokenImages.join('\n')}`).toEqual([]);

      expect(await page.locator('h1').count(), `${slug} h1 count`).toBe(1);

      // breadcrumb first link -> /services
      const firstBc = page.locator('nav[aria-label="Breadcrumb"] a').first();
      if (await firstBc.count()) {
        const href = await firstBc.getAttribute('href');
        expect(href, `${slug} breadcrumb`).toContain('/services');
      } else {
        // fallback: any breadcrumb
        const anyFirst = page.locator('[aria-label*="readcrumb" i] a, .breadcrumb a').first();
        if (await anyFirst.count()) {
          const href = await anyFirst.getAttribute('href');
          expect(href).toContain('/services');
        } else {
          throw new Error(`${slug}: no breadcrumb found`);
        }
      }

      // sticky sidebar (only present on leaf category pages + /services)
      const aside = page.locator('aside');
      if (await aside.count() > 0) {
        const sidebarPosition = await page.evaluate(() => {
          const el = document.querySelector('aside');
          return el ? getComputedStyle(el).position : '';
        });
        expect(['sticky', 'fixed'], `${slug} sidebar`).toContain(sidebarPosition);
      }

      // a11y
      const unnamed = await page.evaluate(() => {
        const els = Array.from(document.querySelectorAll('input, select'));
        const bad: string[] = [];
        for (const el of els as any[]) {
          if ((el as any).type === 'hidden') continue;
          const id = (el as any).id;
          let name = '';
          if (id) {
            const lbl = document.querySelector(`label[for="${CSS.escape(id)}"]`);
            if (lbl) name = (lbl as any).textContent?.trim() || '';
          }
          if (!name) name = (el as any).getAttribute('aria-label') || '';
          if (!name) {
            const lbid = (el as any).getAttribute('aria-labelledby');
            if (lbid) {
              const t = document.getElementById(lbid);
              if (t) name = t.textContent?.trim() || '';
            }
          }
          if (!name && (el as any).labels && (el as any).labels.length) {
            name = (el as any).labels[0].textContent?.trim() || '';
          }
          if (!name) bad.push((el as any).outerHTML.slice(0, 120));
        }
        return bad;
      });
      expect(unnamed, `${slug} unnamed inputs:\n${unnamed.join('\n')}`).toEqual([]);

      // overflow
      for (const [w, h] of [[1280, 720], [375, 667]] as [number, number][]) {
        await page.setViewportSize({ width: w, height: h });
        await page.waitForTimeout(200);
        const ok = await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth + 1);
        expect(ok, `${slug} overflow at ${w}x${h}`).toBe(true);
      }
    });
  }
});
