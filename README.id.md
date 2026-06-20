# 🍃 FoodRescue — Platform Penyelamatan Makanan Sisa

Aplikasi web berbasis mobile yang menghubungkan pedagang makanan dengan makanan sisa kepada penyelamat yang ingin menyelamatkan makanan dari pemborosan. Dibangun dengan **PHP**, **Tailwind CSS**, **Vanilla JavaScript**, dan **MySQL**.

![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?style=flat&logo=php&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.4+-06B6D4?style=flat&logo=tailwindcss&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat&logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green)

---

## ✨ Fitur

### 🗺️ Tampilan Penyelamat
- **Peta Interaktif** — OpenStreetMap + Leaflet.js dengan lokasi pedagang real-time
- **Katalog Makanan** — Jelajahi makanan sisa dengan pencarian, filter harga, dan kedaluwarsa
- **Sistem Klaim** — Reservasi porsi makanan dengan tiket QR untuk verifikasi pengambilan
- **Riwayat Pesanan** — Lacak semua pesanan yang diklaim dengan pembaruan status
- **Integrasi WhatsApp** — Kontak langsung ke pedagang untuk koordinasi pengambilan

### 🏪 Dashboard Pedagang
- **Manajemen Inventaris** — Posting makanan sisa dengan foto, harga, dan timer kedaluwarsa
- **Pemindai QR** — Verifikasi tiket klaim penyelamat dengan memindai kode QR
- **Pelacakan Pesanan** — Lihat semua klaim masuk dan statistik pendapatan
- **Alur Verifikasi** — Sistem pendaftaran pedagang yang disetujui admin

### 🔐 Portal Admin
- **Verifikasi Pedagang** — Tinjau dan aktifkan/nonaktifkan akun pedagang
- **Metrik Dashboard** — Total pedagang, jumlah aktif, dan verifikasi tertunda
- **Akses Berbasis Peran** — Akses admin-only yang aman dengan validasi sesi

### 🎨 UI/UX
- **Desain Mobile-First** — Dioptimalkan untuk layar smartphone dengan navigasi bawah
- **Efek Glassmorphism** — Komponen UI kaca buram modern
- **Notifikasi Toast** — Alert slide-in non-intrusif untuk semua umpan balik pengguna
- **Sistem Modal** — Login, registrasi, konfirmasi logout, dan modal detail makanan
- **Tata Letak Responsif** — Beradaptasi dari mobile ke desktop dengan panel sidebar

---

## 📂 Struktur Proyek

```
foodrescue/
├── config/
│   └── db.php                  # Koneksi MySQL + pembuatan schema otomatis
├── includes/
│   ├── auth.php                # Manajemen sesi, CSRF, cookie remember-me
│   └── api_handler.php         # Semua logika API backend (14 endpoint)
├── components/
│   ├── header.php              # Navbar sticky + dropdown profil + container toast
│   ├── footer.php              # Nav bawah mobile + import script
│   ├── map.php                 # Kanvas peta Leaflet + kontrol
│   ├── rescuer_dashboard.php   # Daftar makanan + peta + filter pencarian
│   ├── merchant_dashboard.php  # Panel pedagang + inventaris + pesanan
│   └── auth_modals.php         # Modal login, register, registrasi pedagang, logout
├── assets/
│   ├── css/
│   │   ├── input.css           # Source Tailwind + style kustom
│   │   └── output.css          # Output Tailwind yang dikompilasi
│   └── js/
│       ├── app.js              # Logika core app, AJAX, sistem toast
│       └── map.js              # Peta Leaflet, marker, geolokasi
├── uploads/                    # Gambar makanan yang diupload pengguna
├── index.php                   # Entry point utama + view router
├── api.php                     # Router API (dispatch berbasis action)
├── admin.php                   # Portal manajemen pedagang admin
├── tailwind.config.js          # Konfigurasi Tailwind
├── package.json                # npm scripts (build/watch)
└── walkthrough.md              # Panduan detail proyek
```

---

## 🚀 Memulai

### Prasyarat

- **PHP 7.4+** dengan ekstensi PDO MySQL
- **MySQL 5.7+** atau **MariaDB 10.3+**
- **Node.js 16+** (untuk kompilasi Tailwind CSS)
- **Composer** (opsional, tidak diperlukan)

### Instalasi

1. **Clone repository**
   ```bash
   git clone https://github.com/yourusername/foodrescue.git
   cd foodrescue
   ```

2. **Konfigurasi database** (opsional — auto-create saat pertama kali load)

   Edit [`config/db.php`](config/db.php) jika kredensial MySQL berbeda:
   ```php
   $host = '127.0.0.1';
   $user = 'root';
   $pass = '';        // password MySQL kamu
   $dbname = 'foodrescue';
   ```

3. **Install dependencies dan build CSS**
   ```bash
   npm install
   npm run build
   ```

4. **Start development server**
   ```bash
   php -S localhost:8000
   ```

5. **Buka di browser**
   ```
   http://localhost:8000
   ```
   > 💡 Gunakan mobile view Chrome DevTools (Ctrl+Shift+M) untuk pengalaman terbaik.

---

## 🔑 Akun Default

| Peran | Username | Password | URL |
|------|----------|----------|-----|
| Admin | `admin` | `admin123` | `/admin.php` |
| Penyelamat | *(daftar baru)* | — | `/` |
| Pedagang | *(daftar via "Jadi Merchant")* | — | `/` |

---

## 🔄 Cara Kerja

### Alur Penyelamat
1. Jelajahi peta atau daftar makanan untuk menemukan makanan sisa terdekat
2. Filter berdasarkan rentang harga dan urgensi kedaluwarsa
3. Ketuk item makanan untuk melihat detail di bottom sheet
4. Pilih jumlah dan metode pembayaran (Tunai / QRIS)
5. Terima tiket QR — tunjukkan saat pengambilan
6. Lacak status pesanan di "Klaim Saya"

### Alur Pedagang
1. Daftar sebagai pedagang dengan lokasi toko di peta
2. Tunggu verifikasi admin (atau verifikasi sendiri via portal admin)
3. Posting makanan sisa dengan foto, harga, dan waktu kedaluwarsa
4. Penyelamat klaim makanan → terima tiket QR
5. Pindai kode QR untuk verifikasi pengambilan dan selesaikan transaksi

### Alur Admin
1. Akses `/admin.php` dengan kredensial admin
2. Lihat metrik pendaftaran pedagang
3. Aktifkan atau nonaktifkan akun pedagang
4. Pantau aktivitas platform

---

## 🛡️ Fitur Keamanan

- **Proteksi CSRF** — Validasi token pada semua request yang mengubah state
- **Hashing Password** — `password_hash()` dengan `PASSWORD_DEFAULT` (bcrypt)
- **Keamanan Sesi** — Cookie HttpOnly, SameSite=Lax, dukungan secure flag
- **Token Remember-Me** — Token 64 karakter kriptografis aman yang disimpan di DB
- **Akses Berbasis Peran** — Cek peran di server pada semua endpoint API
- **Validasi Input** — Sanitasi di server pada semua input form
- **Keamanan Upload File** — Validasi MIME type, batas ukuran (5MB), ekstensi yang diizinkan

---

## 🗄️ Skema Database

Auto-dibuat saat pertama kali load halaman:

| Tabel | Tujuan |
|-------|--------|
| `users` | Akun pengguna (peran penyelamat/pedagang/admin) |
| `merchants` | Profil pedagang dengan koordinat lokasi |
| `food_items` | Listing makanan sisa dengan harga dan kedaluwarsa |
| `orders` | Catatan klaim dengan pelacakan pembayaran dan status |

---

## 🛠️ Endpoint API

Semua endpoint dirutekan melalui [`api.php`](api.php) via request POST:

| Action | Method | Auth Diperlukan | Deskripsi |
|--------|--------|---------------|-------------|
| `login` | POST | Tidak | Autentikasi pengguna |
| `register` | POST | Tidak | Pendaftaran penyelamat baru |
| `logout` | POST | Ya | Penghancuran sesi |
| `register_merchant` | POST | Opsional | Pendaftaran pedagang |
| `add_food_item` | POST | Pedagang | Posting makanan sisa |
| `get_food_items` | GET | Tidak | Ambil listing makanan aktif |
| `claim_food_item` | POST | Penyelamat | Reservasi porsi makanan |
| `get_rescuer_orders` | POST | Penyelamat | Riwayat pesanan |
| `verify_qr_claim` | POST | Pedagang | Pindai QR untuk selesaikan pesanan |
| `toggle_merchant_status` | POST | Admin | Aktifkan/nonaktifkan pedagang |
| `get_merchants` | GET | Tidak | Daftar pedagang aktif |
| `forgot_password` | POST | Tidak | Request reset password |
| `reset_password` | POST | Tidak | Set password baru |
| `get_initial_data` | GET | Tidak | Load data gabungan |

---

## 🧪 Menguji Alur Lengkap

1. **Daftar pedagang** → Klik "Jadi Merchant" → Isi form → Pin lokasi di peta
2. **Setujui via admin** → Login sebagai `admin`/`admin123` di `/admin.php` → Klik "Verifikasi & Aktifkan"
3. **Posting makanan** → Beralih ke Dashboard Pedagang → Tambah item makanan dengan foto
4. **Klaim sebagai penyelamat** → Beralih ke tampilan Penyelamat → Temukan makanan di peta → Klaim → Dapatkan tiket QR
5. **Verifikasi pengambilan** → Beralih ke Pedagang → Pindai QR → Transaksi selesai

---

## 📦 Tech Stack

| Layer | Teknologi |
|-------|-----------|
| **Frontend** | Vanilla JavaScript, Tailwind CSS 3.4, Leaflet.js 1.9 |
| **Backend** | PHP 7.4+ (tanpa framework) |
| **Database** | MySQL / MariaDB dengan PDO |
| **Maps** | OpenStreetMap + Leaflet.js |
| **Icons** | Font Awesome 6.4 |
| **Fonts** | Google Fonts (Outfit + Inter) |
| **Build** | Tailwind CLI via npm |

---

## 📄 Lisensi

Proyek ini open source dan tersedia di bawah [Lisensi MIT](LICENSE).

---

<p align="center">
  <strong>🍃 FoodRescue</strong> — Selamatkan makanan, selamatkan lingkungan, satu makanan pada satu waktu.
</p>