import './bootstrap';
import focus from '@alpinejs/focus';

// Alpine.js is provided by Livewire v3 -- it starts its own Alpine
// instance, so plugins register through the livewire:init event rather
// than importing/starting Alpine directly here (that would start it
// twice). `focus` adds x-trap, used on the real modal dialogs (not
// lightweight dropdown/disclosure menus) so Tab can't leak keyboard
// focus into the page behind an open modal.
document.addEventListener('livewire:init', () => {
  Alpine.plugin(focus);
});

const normalizeImageMetadata = () => {
  const appName = document.title?.split(' - ').pop()?.trim() || 'Smart African Market';
  const images = document.querySelectorAll('img');

  images.forEach((img) => {
    if (!img.hasAttribute('loading')) {
      const isNearViewport = img.getBoundingClientRect().top < window.innerHeight * 1.5;
      img.setAttribute('loading', isNearViewport ? 'eager' : 'lazy');
    }

    if (!img.hasAttribute('decoding')) {
      img.setAttribute('decoding', 'async');
    }

    const altText = (img.getAttribute('alt') || '').trim();
    const isDecorative =
      img.getAttribute('aria-hidden') === 'true' || img.getAttribute('role') === 'presentation';
    if (!altText && !isDecorative) {
      const src = img.getAttribute('src') || '';
      const fileName = src.split('/').pop()?.split('?')[0] || '';
      const fallbackAlt = fileName
        ? fileName
            .replace(/\.[a-z0-9]+$/i, '')
            .replace(/[-_]+/g, ' ')
            .trim()
        : appName;
      img.setAttribute('alt', fallbackAlt || appName);
    }
  });
};

document.addEventListener('DOMContentLoaded', normalizeImageMetadata);

if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js').catch(() => {
      // No-op: service worker registration should not block runtime.
    });
  });
}

const trackCoreWebVitals = () => {
  const metrics = {
    fcp: null,
    lcp: null,
    cls: 0,
    inp: null,
    ttfb: null,
    path: window.location.pathname,
  };

  const navEntry = performance.getEntriesByType('navigation')[0];
  if (navEntry && typeof navEntry.responseStart === 'number') {
    metrics.ttfb = Number(navEntry.responseStart.toFixed(2));
  }

  const paintEntries = performance.getEntriesByName('first-contentful-paint');
  if (paintEntries.length > 0) {
    metrics.fcp = Number(paintEntries[0].startTime.toFixed(2));
  }

  let clsValue = 0;
  let inpValue = 0;

  if ('PerformanceObserver' in window) {
    try {
      const lcpObserver = new PerformanceObserver((entryList) => {
        const entries = entryList.getEntries();
        const lastEntry = entries[entries.length - 1];
        if (lastEntry) {
          metrics.lcp = Number(lastEntry.startTime.toFixed(2));
        }
      });
      lcpObserver.observe({ type: 'largest-contentful-paint', buffered: true });

      const clsObserver = new PerformanceObserver((entryList) => {
        for (const entry of entryList.getEntries()) {
          if (!entry.hadRecentInput) {
            clsValue += entry.value;
          }
        }
        metrics.cls = Number(clsValue.toFixed(4));
      });
      clsObserver.observe({ type: 'layout-shift', buffered: true });

      const inpObserver = new PerformanceObserver((entryList) => {
        for (const entry of entryList.getEntries()) {
          if (entry.duration > inpValue) {
            inpValue = entry.duration;
          }
        }
        metrics.inp = Number(inpValue.toFixed(2));
      });
      inpObserver.observe({ type: 'event', buffered: true, durationThreshold: 40 });
    } catch (error) {
      // Some browsers do not support all observer entry types.
    }
  }

  const flush = () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
      return;
    }

    const payload = new URLSearchParams();
    payload.append('_token', csrfToken);
    payload.append('payload[path]', metrics.path);

    if (metrics.fcp !== null) payload.append('payload[fcp]', String(metrics.fcp));
    if (metrics.lcp !== null) payload.append('payload[lcp]', String(metrics.lcp));
    payload.append('payload[cls]', String(metrics.cls));
    if (metrics.inp !== null) payload.append('payload[inp]', String(metrics.inp));
    if (metrics.ttfb !== null) payload.append('payload[ttfb]', String(metrics.ttfb));

    fetch('/analytics/web-vitals', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: payload.toString(),
      credentials: 'same-origin',
      keepalive: true,
    }).catch(() => {});
  };

  setTimeout(flush, 5000);
  window.addEventListener(
    'visibilitychange',
    () => {
      if (document.visibilityState === 'hidden') {
        flush();
      }
    },
    { once: true }
  );
};

window.addEventListener('load', trackCoreWebVitals);
