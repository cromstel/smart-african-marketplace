# African Market - Frontend Pages Development Plan

## Overview
Comprehensive plan for developing all required frontend pages with modern UI/UX, RBAC integration, and responsive design.

---

## 🎨 Design System

### Brand Colors
- **Primary**: Orange (#EA580C) to Red (#DC2626) gradient
- **Secondary**: Green (#16A34A) for success states
- **Neutral**: Gray scale for backgrounds
- **Accent**: Yellow (#EAB308) for highlights

### Typography
- **Font Family**: Inter (Google Fonts)
- **Headings**: Bold weights (600-700)
- **Body**: Regular weight (400)

### Components
- Tailwind CSS 3.x utility classes
- Custom components for reusability
- Dark mode support throughout

---

## 📄 Required Frontend Pages

### 1. **Landing/Home Page** (`/`)
**Priority**: HIGH  
**Status**: ⏸️ Pending

**Sections**:
- Hero section with CTA buttons (Shop Now, Browse Categories)
- Featured products carousel
- Category grid (Food, Clothing, Salon Services)
- Testimonials/reviews section
- Newsletter subscription
- Featured vendors
- Statistics (Products, Vendors, Happy Customers)
- Footer with links

**Features**:
- Responsive mega menu
- Search bar with autocomplete
- Shopping cart icon with badge
- Language switcher
- Dark/light mode toggle

---

### 2. **About Us Page** (`/about`)
**Priority**: MEDIUM  
**Status**: ⏸️ Pending

**Sections**:
- Company mission & vision
- Our story/history
- Team members (optional)
- Why choose us (USPs)
- Our values
- CTA to shop or contact

---

### 3. **Contact Us Page** (`/contact`)
**Priority**: HIGH  
**Status**: ⏸️ Pending

**Features**:
- Contact form (Name, Email, Subject, Message)
- Office location with Google Maps embed
- Phone numbers & email addresses
- Social media links
- Operating hours
- FAQ accordion section

**Backend**:
- `ContactController` to handle form submissions
- Email notification to admin
- Store inquiries in `inquiries` table

---

### 4. **Shop/Products Listing** (`/shop`)
**Priority**: HIGH  
**Status**: ✅ EXISTS (enhance UI)

**Enhancements Needed**:
- Advanced filters sidebar (price range, categories, ratings)
- Sort options (popular, newest, price low-high, price high-low)
- Grid/list view toggle
- Quick view modal
- Add to wishlist button
- Product badges (New, Sale, Out of Stock)
- Pagination with infinite scroll option

---

### 5. **Product Detail Page** (`/shop/{product}`)
**Priority**: HIGH  
**Status**: ✅ EXISTS (enhance UI)

**Enhancements Needed**:
- Image gallery with zoom
- Product variants selector (color, size)
- Quantity selector with stock validation
- Add to cart sticky button
- Related products
- Customer reviews/ratings section
- Vendor information card
- Share buttons (social media)
- Breadcrumb navigation

---

### 6. **Salon Services Listing** (`/salons`)
**Priority**: HIGH  
**Status**: ✅ EXISTS (enhance UI)

**Enhancements Needed**:
- Service categories filter
- Price range filter
- Location-based filtering
- Booking calendar integration
- Service provider profiles

---

### 7. **My Account Dashboard** (`/dashboard`)
**Priority**: HIGH  
**Status**: ✅ EXISTS (basic)

**Sections**:
- Overview cards (orders, wishlist, loyalty points, referrals)
- Recent orders
- Saved addresses
- Account settings link
- Logout button

---

### 8. **My Orders** (`/orders`)
**Priority**: HIGH  
**Status**: ✅ EXISTS

**Enhancements**:
- Order tracking timeline
- Download invoice (PDF)
- Reorder button
- Leave review button
- Filter by status

---

### 9. **Wishlist** (`/wishlist`)
**Priority**: MEDIUM  
**Status**: ✅ EXISTS

**Enhancements**:
- Move all to cart button
- Share wishlist feature
- Price drop notifications

---

### 10. **Cart** (`/cart`)
**Priority**: HIGH  
**Status**: ✅ EXISTS

**Enhancements**:
- Coupon code input
- Estimated delivery date
- Continue shopping link
- Save for later feature
- Apply loyalty points option

---

### 11. **Checkout** (`/checkout`)
**Priority**: HIGH  
**Status**: ✅ EXISTS

**Enhancements**:
- Multi-step wizard (Address → Shipping → Payment → Review)
- Guest checkout option
- Order summary sidebar (sticky)
- Apply coupon at checkout
- Multiple payment methods UI
- Terms & conditions checkbox

---

### 12. **Blog Listing** (`/blog`)
**Priority**: MEDIUM  
**Status**: ⏸️ Pending

**Features**:
- Blog post cards with featured image
- Categories filter
- Search functionality
- Pagination
- Recent posts sidebar
- Popular tags cloud

**Backend**: Use existing BlogPost model

---

### 13. **Blog Post Detail** (`/blog/{slug}`)
**Priority**: MEDIUM  
**Status**: ⏸️ Pending

**Features**:
- Full post content with rich formatting
- Author bio
- Share buttons
- Related posts
- Comments section (optional)
- Back to blog link

---

### 14. **Vendor Profile** (`/vendors/{vendor}`)
**Priority**: MEDIUM  
**Status**: ⏸️ Pending

**Features**:
- Vendor information & logo
- Products by vendor
- Vendor ratings/reviews
- Contact vendor button
- Follow/Unfollow vendor

---

### 15. **Subscription/Newsletter Page** (`/newsletter`)
**Priority**: LOW  
**Status**: ⏸️ Pending

**Features**:
- Email subscription form
- Benefits list
- Unsubscribe option
- Preview recent newsletters
- Preference management

**Backend**:
- `Newsletter` model & migration
- `NewsletterController`
- Integration with email service

---

### 16. **FAQ Page** (`/faq`)
**Priority**: MEDIUM  
**Status**: ⏸️ Pending

**Features**:
- Accordion-style Q&A
- Categories (General, Shipping, Returns, Payments)
- Search FAQs
- Contact us CTA

---

### 17. **Terms of Service** (`/terms`)
**Priority**: MEDIUM  
**Status**: ⏸️ Pending

**Content**:
- Legal terms & conditions
- User agreements
- Refund/return policy
- Shipping policy

---

### 18. **Privacy Policy** (`/privacy`)
**Priority**: MEDIUM  
**Status**: ⏸️ Pending

**Content**:
- Data collection & usage
- Cookie policy
- GDPR compliance
- User rights

---

### 19. **Referral Program** (`/referrals`)
**Priority**: MEDIUM  
**Status**: ⏸️ Pending

**Features**:
- User referral dashboard
- Unique referral link generator
- Copy to clipboard button
- Referral statistics (invites sent, accepted, earnings)
- Withdraw earnings button
- How it works section

**Backend**: Use existing Referral model

---

### 20. **Loyalty Program** (`/loyalty`)
**Priority**: MEDIUM  
**Status**: ⏸️ Pending

**Features**:
- Points balance display
- Points history table
- How to earn points
- Redeem points interface
- Expiring points alert

**Backend**: Use existing LoyaltyPointsService

---

### 21. **Track Order** (`/track/{order_number}`)
**Priority**: HIGH  
**Status**: ⏸️ Pending

**Features**:
- Order status timeline
- Current location (if GPS tracking enabled)
- Estimated delivery time
- Carrier information
- Contact support button

---

### 22. **Search Results** (`/search`)
**Priority**: HIGH  
**Status**: ⏸️ Pending

**Features**:
- Unified search (products, blog posts, categories)
- Filters & sorting
- Search suggestions
- Recent searches (session-based)
- No results state with suggestions

---

### 23. **404 Error Page**
**Priority**: MEDIUM  
**Status**: ⏸️ Pending

**Features**:
- Friendly error message
- Search bar
- Popular categories links
- Back to home button

---

### 24. **500 Error Page**
**Priority**: LOW  
**Status**: ⏸️ Pending

**Features**:
- Apology message
- Contact support
- Try again button

---

## 🧭 Global Header (Mega Menu)

### Structure

```
┌─────────────────────────────────────────────────────────────┐
│ [Logo]  [Shop ▼] [Salons ▼] [Blog]  [🔍 Search...]  [🛒 3] │
│                                        [Language] [Profile]  │
└─────────────────────────────────────────────────────────────┘
```

### Mega Menu - Shop Dropdown

```
┌───────────────────────────────────────────────────────┐
│  CATEGORIES        FEATURED         QUICK LINKS       │
│                                                         │
│  🍎 Food           [Product 1]      → New Arrivals    │
│  👗 Clothing       [Product 2]      → Best Sellers    │
│  💄 Beauty         [Product 3]      → On Sale         │
│  🏠 Home                             → All Products    │
│  🎁 Gifts                                              │
└───────────────────────────────────────────────────────┘
```

### Mega Menu - Salons Dropdown

```
┌───────────────────────────────────────────────────────┐
│  SERVICES          POPULAR          QUICK LINKS       │
│                                                         │
│  💇 Haircuts      [Service 1]       → Book Now        │
│  💅 Nails         [Service 2]       → All Services    │
│  💆 Spa           [Service 3]       → Gift Cards      │
│  👰 Bridal                           → Find Salon     │
└───────────────────────────────────────────────────────┘
```

### Implementation
- **Component**: `resources/views/components/header.blade.php`
- **Responsive**: Mobile hamburger menu
- **Sticky on scroll**
- **Search autocomplete with AJAX**
- **Cart count badge updates dynamically**

---

## 📱 Footer

### Structure

```
┌────────────────────────────────────────────────────────────┐
│ ABOUT US        QUICK LINKS      CUSTOMER SERVICE          │
│ - Our Story     - Shop           - Contact Us              │
│ - Team          - Salons         - FAQs                    │
│ - Careers       - Blog           - Shipping Info           │
│                 - Vendors        - Returns                 │
│                                                              │
│ NEWSLETTER                    FOLLOW US                     │
│ [Email input] [Subscribe]    [FB] [IG] [TW] [YT]          │
│                                                              │
│ © 2026 African Market | Privacy | Terms | Sitemap          │
└────────────────────────────────────────────────────────────┘
```

### Implementation
- **Component**: `resources/views/components/footer.blade.php`
- Payment method icons (Stripe, Paystack, MOMO)
- Back to top button
- Dark mode compatible

---

## 🔐 Role-Based Access Control (RBAC)

### Customer Pages (Public + Auth)
- Landing, Shop, Products, Salons, Blog, About, Contact
- Cart, Checkout (auth required)
- Dashboard, Orders, Wishlist, Profile (auth required)

### Vendor Pages (Vendor role)
- `/vendor/dashboard`
- `/vendor/products`
- `/vendor/orders`

### Admin Pages (Admin role)
- `/admin/dashboard`
- All admin management pages

### Middleware
- Public pages: No middleware
- Auth pages: `['auth']`
- Role-specific: `['auth', 'admin']` or `['auth', 'vendor']`

---

## 🎯 Next Steps

### Phase 1: Core Frontend (Week 1-2)
1. Landing/Home page
2. Enhanced shop listing
3. Enhanced product detail
4. Contact page
5. Global header with mega menu
6. Footer component

### Phase 2: Essential Pages (Week 3-4)
1. Blog listing & detail
2. About us
3. FAQ
4. Search results
5. 404/500 error pages

### Phase 3: User Features (Week 5-6)
1. Loyalty program page
2. Referral program page
3. Order tracking
4. Vendor profiles
5. Newsletter page

### Phase 4: Legal & Polish (Week 7)
1. Terms of service
2. Privacy policy
3. Performance optimization
4. SEO enhancements
5. Accessibility audit

---

## 🛠️ Technical Requirements

### Controllers to Create
- `HomeController` - Landing page
- `BlogController` - Public blog listing/detail
- `ContactController` - Contact form handler
- `NewsletterController` - Subscription management
- `SearchController` - Unified search
- `PageController` - Static pages (about, faq, terms, privacy)

### Views to Create
- `home.blade.php`
- `about.blade.php`
- `contact.blade.php`
- `faq.blade.php`
- `terms.blade.php`
- `privacy.blade.php`
- `blog/index.blade.php`
- `blog/show.blade.php`
- `search/index.blade.php`
- `errors/404.blade.php`
- `errors/500.blade.php`
- `components/header.blade.php`
- `components/footer.blade.php`
- `components/mega-menu.blade.php`

### Additional Migrations
- `create_inquiries_table` - Contact form submissions
- `create_newsletters_table` - Email subscriptions

### Assets
- Hero images for landing page
- Category images
- Placeholder images for missing products
- Icon set for categories

---

## 📊 Success Metrics

- **Page Load Time**: < 3 seconds
- **Mobile Responsive**: 100% pages
- **Accessibility**: WCAG 2.1 AA compliant
- **SEO Score**: 90+ (Lighthouse)
- **Conversion Rate**: Track through analytics

---

**Document Version**: 1.0  
**Last Updated**: {{ date('Y-m-d') }}  
**Status**: Ready for Implementation
