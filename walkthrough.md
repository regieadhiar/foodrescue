# Walkthrough - FoodRescue Web Application

The **FoodRescue** codebase is complete. We have built a mobile-first, modular web app using **native PHP**, **Tailwind CSS (via Tailwind CLI)**, and **Vanilla JavaScript** backed by a **MySQL** database.

---

## 📂 File Architecture Overview

Here is a summary of the modular components and files that have been created:

| File / Directory | Description |
| :--- | :--- |
| **[`config/db.php`](file:///home/egyadya/FlyEnv/foodrescue/config/db.php)** | MySQL database connection. Auto-creates the database `foodrescue` and tables, and seeds the default admin user. |
| **[`includes/auth.php`](file:///home/egyadya/FlyEnv/foodrescue/includes/auth.php)** | Session and login library. Implements persistent cookies ("Remember Me") to stay logged in on the same device. |
| **[`includes/api_handler.php`](file:///home/egyadya/FlyEnv/foodrescue/includes/api_handler.php)** | Backend logical actions (Auth, Merchant Signup, Food Posting, Order claiming, Admin toggles). |
| **[`components/header.php`](file:///home/egyadya/FlyEnv/foodrescue/components/header.php)** | Responsive navbar, profile dropdown, and active view switcher (Rescuer View / Merchant Dashboard). |
| **[`components/footer.php`](file:///home/egyadya/FlyEnv/foodrescue/components/footer.php)** | Mobile-app style floating bottom navigation bar and Leaflet imports. |
| **[`components/map.php`](file:///home/egyadya/FlyEnv/foodrescue/components/map.php)** | Leaflet OpenStreetMap canvas container with locate-button overlay. |
| **[`components/rescuer_dashboard.php`](file:///home/egyadya/FlyEnv/foodrescue/components/rescuer_dashboard.php)** | Rescuer front page with map view, food list catalog feed, search inputs, and filters. |
| **[`components/merchant_dashboard.php`](file:///home/egyadya/FlyEnv/foodrescue/components/merchant_dashboard.php)** | Merchant panel. Adapts to verification states (`is_active` status) and provides inventory, post forms, and order claims lists. |
| **[`components/auth_modals.php`](file:///home/egyadya/FlyEnv/foodrescue/components/auth_modals.php)** | Login, Register, Become Merchant (with modal map coordination picker), and claim history listings. |
| **[`assets/js/map.js`](file:///home/egyadya/FlyEnv/foodrescue/assets/js/map.js)** | Leaflet mapping code. Handles dynamic pins, clustering, location tracking, and mobile bottom sheet sliders. |
| **[`assets/js/app.js`](file:///home/egyadya/FlyEnv/foodrescue/assets/js/app.js)** | Core JS. Handles view switching, filtering feeds, category selectors, and AJAX submissions. |
| **[`api.php`](file:///home/egyadya/FlyEnv/foodrescue/api.php)** | API router that maps frontend AJAX calls to `api_handler.php` methods. |
| **[`index.php`](file:///home/egyadya/FlyEnv/foodrescue/index.php)** | Core application gateway managing sessions, role validations, and component layouts. |
| **[`admin.php`](file:///home/egyadya/FlyEnv/foodrescue/admin.php)** | Admin portal dashboard. Displays metrics and verified merchant logs, and handles activation toggles. |
| **[`package.json`](file:///home/egyadya/FlyEnv/foodrescue/package.json)** & **[`tailwind.config.js`](file:///home/egyadya/FlyEnv/foodrescue/tailwind.config.js)** | Tailwind configuration and npm script build setups. |
| **[`assets/css/input.css`](file:///home/egyadya/FlyEnv/foodrescue/assets/css/input.css)** | Source stylesheet injecting tailwind bases, custom scrolls, and Leaflet overrides. |

---

## 🚀 How to Set Up and Run the Application

Follow these steps on your system to run the application:

### 1. Database Configuration
Make sure your local **MySQL Server** is running. By default, the database config is set to:
* **Host**: `127.0.0.1`
* **Username**: `root`
* **Password**: `""` (empty)
* **Database Name**: `foodrescue` *(The script will automatically try to create this database and all tables for you on the first page load)*

> [!NOTE]
> If your MySQL configurations differ (e.g. port or password), open [**`config/db.php`**](file:///home/egyadya/FlyEnv/foodrescue/config/db.php#L4-L8) and adjust the connection credentials.

### 2. Compile Tailwind CSS
Since we are using the Tailwind CLI, run the following commands to install dependencies and compile the style bundle:
```bash
# Install DevDependencies (tailwindcss)
npm install

# Compile Tailwind for production
npm run build

# OR Run the compiler in watch mode during development
npm run watch
```

### 3. Launch Local PHP Development Server
Start the built-in PHP server inside the project root:
```bash
php -S localhost:8000
```
Open **`http://localhost:8000`** in your browser. Select **Mobile View** in your browser's Developer Tools (Ctrl+Shift+M) for the best mobile-first layout experience!

---

## 🔑 Default Accounts & Testing Flows

### Administrator Portal
* **URL**: `http://localhost:8000/admin.php`
* **Default Admin Credentials**:
  * **Username**: `admin`
  * **Password**: `admin123`
* **Purpose**: Access this page to verify and activate newly registered merchants so they can post food items.

### Testing the Rescuer & Merchant Flows:
1. **Visit the Homepage**: You will arrive as a **Rescuer** (default view) looking at the OpenStreetMap map centered on your location.
2. **Register a Merchant**:
   - Tap **"Jadi Merchant"** in the bottom navigation.
   - Fill in your details. You can click on the mini map to position your shop's coordinate pin.
   - Submit registration. You will be redirected to the Merchant Dashboard showing a **"Toko Sedang Ditinjau Admin"** pending verification status.
3. **Approve the Merchant**:
   - Log out of your current session.
   - Go to `http://localhost:8000/admin.php`, log in with `admin` / `admin123`.
   - You will see the new merchant list. Click **"Verifikasi & Aktifkan"**.
4. **Sell Surplus Food**:
   - Go back to the homepage. Log back into your merchant account.
   - Switch view to **"Dashboard Toko"** (if not already there).
   - The listing section is now unlocked! Go to **"Jual Makanan"**, add a surplus food item, select a category preset illustration, and submit.
5. **Claim Food (Rescuer)**:
   - Log out or create a new **Rescuer** account.
   - On the map, you will see a green food pin at the merchant's coordinates.
   - Click the pin -> a mobile bottom sheet slides up showing the details, discount, and claim form.
   - Select the portion and click **"Klaim Makanan"**.
   - Your order is recorded! A confirmation pops up and links to a pre-filled WhatsApp message redirecting you to contact the merchant for pickups. You can view your active claims in **"Klaim Saya"** on the bottom navigation bar.
