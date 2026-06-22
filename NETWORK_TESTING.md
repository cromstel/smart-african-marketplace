# Network Testing Guide - African Marketplace

## 🌐 Access the Website from Other Devices

### Your Network Configuration
- **Local IP Address**: `192.168.100.32`
- **Server Port**: `8000`
- **Network**: `192.168.100.0/24` (255.255.255.0)

---

## 🚀 Starting the Server for Network Access

### Option 1: Bind to All Network Interfaces (Recommended)
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

### Option 2: Bind to Specific IP Address
```bash
php artisan serve --host=192.168.100.32 --port=8000
```

---

## 📱 Accessing from Other Devices

Once the server is running, access the website from any device on your network:

### From Localhost (This Computer)
- http://localhost:8000
- http://127.0.0.1:8000
- http://192.168.100.32:8000

### From Other Computers on the Same Network
- http://192.168.100.32:8000

### From Mobile Phones/Tablets on the Same WiFi
- http://192.168.100.32:8000

---

## 🔥 Windows Firewall Configuration

If you can't access from other devices, you may need to allow port 8000 through Windows Firewall:

### Method 1: Using PowerShell (Run as Administrator)
```powershell
New-NetFirewallRule -DisplayName "Laravel Dev Server" -Direction Inbound -LocalPort 8000 -Protocol TCP -Action Allow
```

### Method 2: Using Windows Defender Firewall GUI
1. Open **Windows Defender Firewall with Advanced Security**
2. Click **Inbound Rules** → **New Rule**
3. Select **Port** → Next
4. Select **TCP** and enter `8000` → Next
5. Select **Allow the connection** → Next
6. Check all profiles (Domain, Private, Public) → Next
7. Name it "Laravel Dev Server" → Finish

### Method 3: Temporarily Disable Firewall (Testing Only)
```powershell
# Run as Administrator - FOR TESTING ONLY!
Set-NetFirewallProfile -Profile Domain,Public,Private -Enabled False

# Re-enable after testing
Set-NetFirewallProfile -Profile Domain,Public,Private -Enabled True
```

---

## 🧪 Testing Checklist

### On Host Computer (192.168.100.32)
- [ ] Start server: `php artisan serve --host=0.0.0.0 --port=8000`
- [ ] Test localhost: http://localhost:8000
- [ ] Test IP access: http://192.168.100.32:8000

### On Other Devices (Same Network)
- [ ] Connect to same WiFi network (`lan`)
- [ ] Open browser and navigate to: http://192.168.100.32:8000
- [ ] Test registration and login
- [ ] Test responsive design on mobile

---

## 🔧 Troubleshooting

### Issue: "Connection Refused" or "Can't Reach Server"

**Solution 1: Check Server is Running**
```bash
# Verify server is listening
netstat -an | findstr ":8000"
```

**Solution 2: Verify IP Address**
```bash
ipconfig
# Look for IPv4 Address under your active network adapter
```

**Solution 3: Test Firewall**
```bash
# From another device, try to ping the host
ping 192.168.100.32
```

**Solution 4: Check if Port is Blocked**
```bash
# Run on host computer
Test-NetConnection -ComputerName 192.168.100.32 -Port 8000
```

### Issue: "Mixed Content" Warnings

If you see mixed content warnings, ensure APP_URL in `.env` matches:
```env
APP_URL=http://192.168.100.32:8000
```

Then clear config cache:
```bash
php artisan config:clear
php artisan cache:clear
```

### Issue: Database Connection Error

Ensure MySQL is running and accessible:
```bash
# Check MySQL service
sc query MySQL80

# Start MySQL if stopped
net start MySQL80
```

---

## 📊 Demo User Accounts for Testing

### Admin Account
- **Email**: `admin@africanmarket.com`
- **Password**: `password`
- **Access**: Full admin dashboard

### Vendor Account
- **Email**: `vendor@africanmarket.com` (if seeded)
- **Password**: `password`
- **Access**: Vendor dashboard

### Customer Account
- **Email**: `customer@africanmarket.com` (if seeded)
- **Password**: `password`
- **Access**: Customer dashboard

---

## 🌍 Public Internet Access (Optional)

To make the site accessible from the internet (not just local network):

### Using Ngrok (Free)
```bash
# Download ngrok from https://ngrok.com
ngrok http 8000
```

This will give you a public URL like: `https://abc123.ngrok.io`

### Using LocalTunnel
```bash
npm install -g localtunnel
lt --port 8000 --subdomain africanmarket
```

---

## 📝 Important Notes

1. **APP_URL Configuration**: Already set to `http://192.168.100.32:8000`
2. **Debug Mode**: Enabled for testing (`APP_DEBUG=true`)
3. **Session Driver**: Using database (works across network)
4. **Cache**: File-based (works fine for local testing)

5. **Security Warning**: Don't expose local development server to public internet without proper security measures

---

## ✅ Quick Start Command

```bash
# Start server accessible from network
php artisan serve --host=0.0.0.0 --port=8000

# Then access from any device on network at:
# http://192.168.100.32:8000
```

---

## 🔗 Useful URLs

- **Homepage**: http://192.168.100.32:8000
- **Shop**: http://192.168.100.32:8000/shop
- **Salon Services**: http://192.168.100.32:8000/salons
- **Login**: http://192.168.100.32:8000/login
- **Register**: http://192.168.100.32:8000/register
- **Admin Dashboard**: http://192.168.100.32:8000/admin/dashboard

---

Last Updated: February 21, 2026
