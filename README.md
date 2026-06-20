# 🍃 FoodRescue — Surplus Food Saving Platform

A mobile-first web application that connects food merchants with surplus food to rescuers who want to save food from going to waste. Built with **PHP**, **Tailwind CSS**, **Vanilla JavaScript**, and **MySQL**.

![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?style=flat&logo=php&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.4+-06B6D4?style=flat&logo=tailwindcss&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat&logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green)

---

## ✨ Features

### 🗺️ Rescuer View
- **Interactive Map** — OpenStreetMap + Leaflet.js with real-time merchant locations
- **Food Catalog** — Browse surplus food items with search, price, and expiry filters
- **Claim System** — Reserve food portions with QR ticket for pickup verification
- **Order History** — Track all claimed orders with status updates
- **WhatsApp Integration** — Direct contact to merchant for pickup coordination

### 🏪 Merchant Dashboard
- **Inventory Management** — Post surplus food items with photos, pricing, and expiry timers
- **QR Scanner** — Verify rescuer claim tickets by scanning QR codes
- **Order Tracking** — View all incoming claims and revenue statistics
- **Verification Flow** — Admin-approved merchant registration system

### 🔐 Admin Portal
- **Merchant Verification** — Review and activate/deactivate merchant accounts
- **Dashboard Metrics** — Total merchants, active count, and pending verifications
- **Role-Based Access** — Secure admin-only access with session validation

### 🎨 UI/UX
- **Mobile-First Design** — Optimized for smartphone screens with bottom navigation
- **Glassmorphism Effects** — Modern frosted-glass UI components
- **Toast Notifications** — Non-intrusive slide-in alerts for all user feedback
- **Modal System** — Login, registration, logout confirmation, and food detail modals
- **Responsive Layout** — Adapts from mobile to desktop with sidebar panels

---

## 📂 Project Structure

```
foodrescue/
├── config/
│   └── db.php                  # MySQL connection + auto schema creation
├── includes/
│   ├── auth.php                # Session management, CSRF, remember-me cookies
│   └── api_handler.php         # All backend API logic (14 endpoints)
├── components/
│   ├── header.php              # Sticky navbar + profile dropdown + toast container
│   ├── footer.php              # Mobile bottom nav + script imports
│   ├── map.php                 # Leaflet map canvas + controls
│   ├── rescuer_dashboard.php   # Food list + map + search filters
│   ├── merchant_dashboard.php  # Merchant panel + inventory + orders
│   └── auth_modals.php         # Login, register, merchant reg, logout modals
├── assets/
│   ├── css/
│   │   ├── input.css           # Tailwind source + custom styles
│   │   └── output.css          # Compiled Tailwind output
│   └── js/
│       ├── app.js              # Core app logic, AJAX, toast system
│       └── map.js              # Leaflet map, markers, geolocation
├── uploads/                    # User-uploaded food images
├── index.php                   # Main entry point + view router
├── api.php                     # API router (action-based dispatch)
├── admin.php                   # Admin merchant management portal
├── tailwind.config.js          # Tailwind configuration
├── package.json                # npm scripts (build/watch)
└── walkthrough.md              # Detailed project walkthrough
```

---

## 🚀 Getting Started

### Prerequisites

- **PHP 7.4+** with PDO MySQL extension
- **MySQL 5.7+** or **MariaDB 10.3+**
- **Node.js 16+** (for Tailwind CSS compilation)
- **Composer** (optional, not required)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/foodrescue.git
   cd foodrescue
   ```

2. **Configure database** (optional — auto-creates on first load)

   Edit [`config/db.php`](config/db.php) if your MySQL credentials differ:
   ```php
   $host = '127.0.0.1';
   $user = 'root';
   $pass = '';        // your MySQL password
   $dbname = 'foodrescue';
   ```

3. **Install dependencies and build CSS**
   ```bash
   npm install
   npm run build
   ```

4. **Start the development server**
   ```bash
   php -S localhost:8000
   ```

5. **Open in browser**
   ```
   http://localhost:8000
   ```
   > 💡 Use Chrome DevTools mobile view (Ctrl+Shift+M) for the best experience.

---

## 🔑 Default Accounts

| Role | Username | Password | URL |
|------|----------|----------|-----|
| Admin | `admin` | `admin123` | `/admin.php` |
| Rescuer | *(register new)* | — | `/` |
| Merchant | *(register via "Jadi Merchant")* | — | `/` |

---

## 🔄 How It Works

### Rescuer Flow
1. Browse the map or food list to find surplus food nearby
2. Filter by price range and expiry urgency
3. Tap a food item to view details in a bottom sheet
4. Select quantity and payment method (Cash / QRIS)
5. Receive a QR ticket — show it at pickup
6. Track order status in "Klaim Saya"

### Merchant Flow
1. Register as a merchant with shop location on the map
2. Wait for admin verification (or verify yourself via admin portal)
3. Post surplus food items with photos, pricing, and expiry time
4. Rescuers claim food → receive QR tickets
5. Scan QR codes to verify pickups and complete transactions

### Admin Flow
1. Access `/admin.php` with admin credentials
2. View merchant registration metrics
3. Activate or deactivate merchant accounts
4. Monitor platform activity

---

## 🛡️ Security Features

- **CSRF Protection** — Token-based validation on all state-changing requests
- **Password Hashing** — `password_hash()` with `PASSWORD_DEFAULT` (bcrypt)
- **Session Security** — HttpOnly cookies, SameSite=Lax, secure flag support
- **Remember-Me Tokens** — Cryptographically secure 64-char tokens stored in DB
- **Role-Based Access** — Server-side role checks on all API endpoints
- **Input Validation** — Server-side sanitization on all form inputs
- **File Upload Security** — MIME type validation, size limits (5MB), allowed extensions

---

## 🗄️ Database Schema

Auto-created on first page load:

| Table | Purpose |
|-------|---------|
| `users` | User accounts (rescuer/merchant/admin roles) |
| `merchants` | Merchant profiles with location coordinates |
| `food_items` | Surplus food listings with pricing and expiry |
| `orders` | Claim records with payment and status tracking |

---

## 🛠️ API Endpoints

All endpoints are routed through [`api.php`](api.php) via POST requests:

| Action | Method | Auth Required | Description |
|--------|--------|---------------|-------------|
| `login` | POST | No | User authentication |
| `register` | POST | No | New rescuer registration |
| `logout` | POST | Yes | Session destruction |
| `register_merchant` | POST | Optional | Merchant registration |
| `add_food_item` | POST | Merchant | Post surplus food |
| `get_food_items` | GET | No | Fetch active food listings |
| `claim_food_item` | POST | Rescuer | Reserve food portions |
| `get_rescuer_orders` | POST | Rescuer | Order history |
| `verify_qr_claim` | POST | Merchant | Scan QR to complete order |
| `toggle_merchant_status` | POST | Admin | Activate/deactivate merchant |
| `get_merchants` | GET | No | Active merchant list |
| `forgot_password` | POST | No | Request password reset |
| `reset_password` | POST | No | Set new password |
| `get_initial_data` | GET | No | Combined data load |

---

## 🧪 Testing the Full Flow

1. **Register a merchant** → Click "Jadi Merchant" → Fill form → Pin location on map
2. **Approve via admin** → Login as `admin`/`admin123` at `/admin.php` → Click "Verifikasi & Aktifkan"
3. **Post food** → Switch to Merchant Dashboard → Add food item with photos
4. **Claim as rescuer** → Switch to Rescuer view → Find food on map → Claim → Get QR ticket
5. **Verify pickup** → Switch to Merchant → Scan QR → Transaction complete

---

## 📦 Tech Stack

| Layer | Technology |
|-------|-----------|
| **Frontend** | Vanilla JavaScript, Tailwind CSS 3.4, Leaflet.js 1.9 |
| **Backend** | PHP 7.4+ (no framework) |
| **Database** | MySQL / MariaDB with PDO |
| **Maps** | OpenStreetMap + Leaflet.js |
| **Icons** | Font Awesome 6.4 |
| **Fonts** | Google Fonts (Outfit + Inter) |
| **Build** | Tailwind CLI via npm |

---

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

---

<p align="center">
  <strong>🍃 FoodRescue</strong> — Save food, save the environment, one meal at a time.
</p>
