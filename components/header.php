<?php
// components/header.php
require_once __DIR__ . '/../includes/auth.php';
$currentUser = get_logged_in_user();
$currentView = $_SESSION['active_view'] ?? 'rescuer'; // 'rescuer' or 'merchant'
$csrfToken = get_csrf_token();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>FoodRescue - Selamatkan Makanan, Selamatkan Lingkungan</title>
    <link rel="shortcut icon" href="./assets/images/favicon.ico" type="image/x-icon">
    
    <!-- CSRF Token for AJAX -->
    <meta name="csrf-token" content="<?= $csrfToken ?>">
    
    <!-- Google Fonts: Outfit & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS output -->
    <link rel="stylesheet" href="assets/css/output.css">
    
    <!-- Leaflet.js CSS for OpenStreetMap -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 text-slate-800 font-sans min-h-screen flex flex-col antialiased" data-view="<?= $currentView ?>">

    <!-- Top Sticky Header -->
    <header class="sticky top-0 z-40 bg-white/80 backdrop-blur-md border-b border-slate-100 shadow-sm transition-all">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between w-full">
            
            <!-- Logo & Brand -->
            <div class="flex items-center gap-6">
                <a href="index.php" class="flex items-center gap-2 group">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center text-white shadow-md shadow-emerald-200">
                        <i class="fa-solid fa-utensils text-base transition-transform group-hover:scale-110"></i>
                    </div>
                    <div>
                        <span class="font-bold text-lg tracking-tight bg-gradient-to-r from-emerald-600 to-emerald-700 bg-clip-text text-transparent">FoodRescue</span>
                        <span class="block text-[9px] -mt-1 text-slate-400 font-medium tracking-wide">SURPLUS SAVER</span>
                    </div>
                </a>

                <!-- Desktop Navigation Links -->
                <nav class="hidden md:flex items-center gap-6 text-xs font-semibold text-slate-500">
                    <a href="index.php?switch_view=rescuer" class="hover:text-emerald-600 transition <?= $currentView === 'rescuer' ? 'text-emerald-600 border-b-2 border-emerald-500 pb-1 mt-1' : '' ?>">Jelajah Makanan</a>
                    <a href="faq.php" class="hover:text-emerald-600 transition">FAQ</a>
                    <?php if ($currentUser): ?>
                        <button onclick="openOrdersHistory()" class="hover:text-emerald-600 transition">Klaim Saya</button>
                        <?php if ($currentUser['role'] === 'merchant'): ?>
                            <a href="index.php?switch_view=merchant" class="hover:text-emerald-600 transition <?= $currentView === 'merchant' ? 'text-emerald-600 border-b-2 border-emerald-500 pb-1 mt-1' : '' ?>">Dashboard Toko</a>
                        <?php else: ?>
                            <button onclick="openModal('merchant-reg-modal')" class="hover:text-emerald-600 transition">Jadi Merchant</button>
                        <?php endif; ?>
                        <?php if ($currentUser['role'] === 'admin'): ?>
                            <a href="admin.php" class="hover:text-blue-600 text-blue-500 transition">Admin Portal</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <button onclick="openModal('login-modal')" class="hover:text-emerald-600 transition">Klaim Saya</button>
                        <button onclick="openModal('merchant-reg-modal')" class="hover:text-emerald-600 transition">Daftar Toko</button>
                    <?php endif; ?>
                </nav>
            </div>

            <!-- Action buttons / Account Details -->
            <div class="flex items-center gap-2">
                <?php if ($currentUser): ?>
                    <!-- If user logged in -->
                    <div class="relative inline-block text-left" id="user-menu-btn-container">
                        <button id="user-menu-btn" class="flex items-center gap-1.5 py-1.5 px-3 rounded-full hover:bg-slate-100 transition duration-200 border border-slate-200">
                            <?php if ($pic = get_profile_pic_url($currentUser)): ?>
                                <img src="<?= $pic ?>" class="w-6 h-6 rounded-full object-cover">
                            <?php else: ?>
                                <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs uppercase">
                                    <?= substr(htmlspecialchars($currentUser['username']), 0, 1); ?>
                                </div>
                            <?php endif; ?>
                            <span class="text-xs font-semibold text-slate-700 truncate max-w-[70px]"><?= htmlspecialchars($currentUser['username']) ?></span>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                        </button>
                        
                        <!-- User Dropdown Menu -->
                        <div id="user-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-2xl shadow-lg border border-slate-100 py-1.5 z-50 animate-fade-in origin-top-right">
                            <div class="px-4 py-2 border-b border-slate-100">
                                <span class="block text-xs text-slate-400">Masuk sebagai</span>
                                <span class="block text-sm font-semibold text-slate-700 truncate"><?= htmlspecialchars($currentUser['username']) ?></span>
                                <span class="inline-block mt-1 px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-800 uppercase"><?= htmlspecialchars($currentUser['role']) ?></span>
                            </div>
                            
                            <!-- Role Toggles -->
                            <?php if ($currentUser['role'] === 'merchant'): ?>
                                <a href="index.php?switch_view=rescuer" class="flex items-center gap-2.5 px-4 py-2 text-xs text-slate-600 hover:bg-slate-50 transition">
                                    <i class="fa-solid fa-heart text-rose-500 w-4"></i> Tampilan Rescuer (Pesan)
                                </a>
                                <a href="index.php?switch_view=merchant" class="flex items-center gap-2.5 px-4 py-2 text-xs text-slate-600 hover:bg-slate-50 transition border-b border-slate-100">
                                    <i class="fa-solid fa-store text-emerald-600 w-4"></i> Dashboard Toko
                                </a>
                            <?php elseif ($currentUser['role'] === 'admin'): ?>
                                <a href="admin.php" class="flex items-center gap-2.5 px-4 py-2 text-xs text-slate-600 hover:bg-slate-50 transition border-b border-slate-100">
                                    <i class="fa-solid fa-shield-halved text-blue-600 w-4"></i> Halaman Admin
                                </a>
                            <?php else: ?>
                                <button onclick="openModal('merchant-reg-modal')" class="w-full flex items-center gap-2.5 px-4 py-2 text-xs text-left text-slate-600 hover:bg-slate-50 transition border-b border-slate-100">
                                    <i class="fa-solid fa-store-slash text-amber-500 w-4"></i> Jadi Merchant Toko
                                </button>
                            <?php endif; ?>
  
                            <a href="profile.php" class="flex items-center gap-2.5 px-4 py-2 text-xs text-slate-600 hover:bg-slate-50 transition">
                                <i class="fa-solid fa-user text-slate-500 w-4"></i> Profil Saya
                            </a>
                            <button onclick="handleLogout()" class="w-full flex items-center gap-2.5 px-4 py-2 text-xs text-left text-red-600 hover:bg-red-50 transition">
                                <i class="fa-solid fa-right-from-bracket w-4"></i> Keluar
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- If Guest -->
                    <?php $isLanding = basename($_SERVER['SCRIPT_NAME']) === 'landing.php'; ?>
                    <?php if (!$isLanding): ?>
                        <button onclick="openModal('login-modal')" class="text-xs font-semibold py-2 px-4 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-100 transition-all hover:shadow-emerald-200">
                            Masuk / Daftar
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
        </div>
    </header>

    <!-- Toast Container -->
    <div id="toast-container"></div>
