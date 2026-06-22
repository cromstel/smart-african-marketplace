# 🔐 Test Accounts - African Marketplace

## Demo User Credentials

All demo accounts use the same password: **`Password123!`**

---

## 👑 Admin Account

**Email:** `admin@africanmarket.com`  
**Password:** `Password123!`  
**Role:** Admin  
**Access:** Full admin dashboard with all privileges

### Admin Capabilities:
- ✅ Manage all products, vendors, and users
- ✅ View analytics and reports
- ✅ Manage blog posts, categories, and tags
- ✅ Configure system settings and brand colors
- ✅ Manage subscription plans
- ✅ Send vendor announcements
- ✅ Manage coupons and loyalty points
- ✅ View all orders and bookings

**Dashboard URL:** http://192.168.100.32:8000/admin/dashboard

---

## 🏪 Vendor Account

**Email:** `vendor@africanmarket.com`  
**Password:** `Password123!`  
**Role:** Vendor  
**Access:** Vendor dashboard

### Vendor Capabilities:
- ✅ Manage own products
- ✅ View and manage orders
- ✅ Track sales and earnings
- ✅ Manage inventory
- ✅ View analytics
- ✅ Manage withdrawals
- ✅ View announcements from admin
- ✅ Manage subscription plan

**Dashboard URL:** http://192.168.100.32:8000/vendor/dashboard

---

## 💇 Salon Owner Account

**Email:** `salon@africanmarket.com`  
**Password:** `Password123!`  
**Role:** Salon Owner  
**Access:** Salon management dashboard

### Salon Owner Capabilities:
- ✅ Manage salon services
- ✅ View and manage bookings
- ✅ Set service pricing and duration
- ✅ Manage availability calendar
- ✅ View booking analytics

**Dashboard URL:** http://192.168.100.32:8000/vendor/dashboard

---

## 🛍️ Customer Accounts

### Customer 1
**Email:** `customer@africanmarket.com`  
**Password:** `Password123!`  
**Role:** Customer  
**Location:** Johannesburg, South Africa

### Customer 2
**Email:** `customer2@africanmarket.com`  
**Password:** `Password123!`  
**Role:** Customer  
**Location:** Cairo, Egypt

### Customer Capabilities:
- ✅ Browse and purchase products
- ✅ Book salon services
- ✅ Manage wishlist
- ✅ View order history
- ✅ Track orders
- ✅ Manage addresses
- ✅ View loyalty points
- ✅ Use referral codes

**Customer Dashboard:** http://192.168.100.32:8000/dashboard

---

## 🧪 Testing Scenarios

### Scenario 1: Admin Operations
1. Login as admin: `admin@africanmarket.com`
2. Navigate to Admin Dashboard
3. Test vendor management
4. Create a new announcement for vendors
5. View analytics and reports

### Scenario 2: Vendor Operations
1. Login as vendor: `vendor@africanmarket.com`
2. Navigate to Vendor Dashboard
3. Add a new product
4. Manage inventory
5. View subscription status
6. Check announcements

### Scenario 3: Customer Shopping
1. Login as customer: `customer@africanmarket.com`
2. Browse products in shop
3. Add items to cart
4. Complete checkout process
5. View order history

### Scenario 4: Salon Booking
1. Login as customer: `customer@africanmarket.com`
2. Browse salon services
3. Book a salon appointment
4. View booking status

### Scenario 5: Multi-Device Testing
1. Login on desktop: `admin@africanmarket.com`
2. Login on mobile: `customer@africanmarket.com`
3. Test responsive design
4. Test concurrent sessions

---

## 🌐 Access URLs

### Public Pages (No Login Required)
- Homepage: http://192.168.100.32:8000
- Shop: http://192.168.100.32:8000/shop
- Salon Services: http://192.168.100.32:8000/salons
- Blog: http://192.168.100.32:8000/blog
- About: http://192.168.100.32:8000/about
- Contact: http://192.168.100.32:8000/contact

### Authentication Pages
- Login: http://192.168.100.32:8000/login
- Register: http://192.168.100.32:8000/register
- Forgot Password: http://192.168.100.32:8000/password/reset

### Dashboard URLs (After Login)
- Admin Dashboard: http://192.168.100.32:8000/admin/dashboard
- Vendor Dashboard: http://192.168.100.32:8000/vendor/dashboard
- Customer Dashboard: http://192.168.100.32:8000/dashboard

---

## 📱 Mobile Testing

### Test on Mobile Devices (Same Network)

1. **Connect mobile device to same WiFi** network (`lan`)
2. **Open browser** on mobile device
3. **Navigate to:** http://192.168.100.32:8000
4. **Test login** with any account above
5. **Test responsive design** on different screen sizes

### QR Code for Easy Mobile Access

You can generate a QR code for http://192.168.100.32:8000 using:
- https://www.qr-code-generator.com/
- Or use a QR code app

---

## 🔒 Security Notes

1. **Development Only**: These credentials are for testing only
2. **Change in Production**: All passwords MUST be changed before production deployment
3. **Email Verification**: All demo accounts have verified emails
4. **Active Status**: All accounts are active by default

---

## 🛠️ Database Reset (If Needed)

If you need to reset the database and reseed demo accounts:

```bash
# Fresh migration with seeders
php artisan migrate:fresh --seed

# Or just run seeders
php artisan db:seed
```

---

## ✅ Account Verification

To verify all demo accounts are in the database:

```bash
php artisan tinker
>>> User::select('email', 'role')->get()
```

Expected output:
- admin@africanmarket.com (admin)
- vendor@africanmarket.com (vendor)
- salon@africanmarket.com (salon_owner)
- customer@africanmarket.com (customer)
- customer2@africanmarket.com (customer)

---

## 🎯 Quick Test Checklist

- [ ] Admin can login and access admin dashboard
- [ ] Vendor can login and access vendor dashboard
- [ ] Salon owner can login and manage services
- [ ] Customer can login and browse products
- [ ] All accounts work from mobile devices
- [ ] Logout functionality works correctly
- [ ] Password reset flow works
- [ ] Registration creates new accounts properly

---

Last Updated: February 21, 2026  
Environment: Local Development (192.168.100.32:8000)
