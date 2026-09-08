## Sticky + Collapsible Sidebars (Admin + Vendor)

Implement sticky positioning for both the admin and vendor dashboard sidebars so they remain visible during scroll, and add collapsible menu functionality that allows users to toggle the sidebar between expanded and collapsed states with smooth transitions

### Summary
Update the Admin and Vendor dashboard layouts so:
- The sidebar stays visible while content scrolls (sticky on desktop).
- Users can toggle the sidebar between expanded and collapsed states with **smooth transitions**.
- Collapse preference persists **separately** for Admin vs Vendor (per your choice).

---

## Files to change
- `resources/views/admin/layout.blade.php`
- `resources/views/vendor/layout.blade.php`

---

## Implementation details (decision-complete)

### 1) Make Vendor sidebar sticky on desktop
In `resources/views/vendor/layout.blade.php`, update the `<aside id="sidebar" ...>` classes:
- Replace `lg:static` with `lg:sticky lg:top-0 lg:h-screen`
- Keep existing mobile behavior (`fixed`, `transform`, `-translate-x-full`, etc.) unchanged.

Target result (class intent):
- Mobile: off-canvas sidebar (current behavior)
- Desktop: sticky sidebar pinned to top of viewport

---

### 2) Add smooth collapse/expand transitions (width + label fade/slide)
In **both** layout files, update the inline `<style>` block to:
1) Add an `#sidebar` rule to animate width + transform:
   - `transition-property: width, transform;`
   - `transition-duration: 200ms, 300ms;`
   - `transition-timing-function: ease-in-out;`
   - `will-change: width, transform;`
   - `overflow-x: hidden;` (prevents label spill during animation)

2) Replace the current “collapsed hides labels via `display: none`” rules with animated variants:
   - Base (desktop only):
     - `#sidebar .sidebar-brand-text` and `#sidebar .sidebar-label`:
       - `display: inline-block` (for `.sidebar-label`)
       - `overflow: hidden; white-space: nowrap;`
       - `max-width: <non-zero>; opacity: 1;`
       - `transition: max-width 200ms ease-in-out, opacity 150ms ease-in-out, margin-left 200ms ease-in-out;`
   - Collapsed (desktop only):
     - Set `max-width: 0`, `opacity: 0`, and `margin-left: 0 !important` on:
       - `.sidebar-brand-text` (also removes the `space-x-*` margin)
       - `.sidebar-label` (removes the `space-x-*` margin)
     - Keep the existing centering rules:
       - `body.sidebar-collapsed #sidebar nav a, ... button { justify-content: center; ... }`
     - Keep submenu chevrons hidden in collapsed mode (retain current `display: none` approach for `.submenu-indicator` to avoid `ml-auto` layout issues).

This yields a smooth “shrink to icon rail” animation without reworking the HTML structure.

---

### 3) Separate persistence keys (Admin vs Vendor)
Update the JS in each layout:

- In `resources/views/admin/layout.blade.php`:
  - Change `const SIDEBAR_COLLAPSED_KEY = 'sidebar_collapsed';`
  - To `const SIDEBAR_COLLAPSED_KEY = 'admin_sidebar_collapsed';`

- In `resources/views/vendor/layout.blade.php`:
  - Change `const SIDEBAR_COLLAPSED_KEY = 'sidebar_collapsed';`
  - To `const SIDEBAR_COLLAPSED_KEY = 'vendor_sidebar_collapsed';`

No other code needs to share state; keep logic identical otherwise.

---

### 4) Improve toggle button UX (aria + icon rotation)
In both layouts:
- Add/update in JS when applying collapsed state:
  - Set `sidebarCollapseToggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true')`
  - Set `sidebarCollapseToggle.setAttribute('title', collapsed ? 'Expand sidebar' : 'Collapse sidebar')`

- Add a tiny CSS rule for the collapse button icon:
  - `#sidebar-collapse-toggle svg { transition: transform 200ms ease-in-out; }`
  - `body.sidebar-collapsed #sidebar-collapse-toggle svg { transform: rotate(180deg); }`

---

## Test plan (manual)
1) Admin dashboard (desktop ≥1024px):
- Scroll main content: sidebar remains visible.
- Click collapse button: sidebar width animates smoothly; labels/brand fade/slide out; icons stay centered.
- Reload: collapse state persists for Admin only.

2) Vendor dashboard (desktop ≥1024px):
- Scroll main content: sidebar remains visible.
- Collapse/expand behaves smoothly.
- Reload: collapse state persists for Vendor only.

3) Mobile (<1024px) for both:
- Hamburger button opens/closes sidebar (existing slide-in/out remains correct).
- Collapse button remains hidden on mobile (existing behavior).

4) Flyout submenus (desktop):
- Hover/focus flyout menus still position correctly in both expanded and collapsed states.

---

## Assumptions / defaults
- “Sticky during scroll” means desktop sticky sidebar pinned to viewport top (`top: 0`).
- Keep existing off-canvas mobile behavior unchanged.
- Keep submenu chevrons non-animated in collapsed mode to avoid layout regressions.
