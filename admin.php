<?php
// admin.php

require_once __DIR__ . '/includes/auth.php';
include __DIR__ . '/components/auth_modals.php';

$currentUser = get_logged_in_user();
$csrfToken = get_csrf_token();

// Security check: Only allow admin role
if (!$currentUser || $currentUser['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

global $pdo;

// Fetch all merchants with owner user account details
$stmt = $pdo->query("
    SELECT m.*, u.username as owner_username, u.email as owner_email
    FROM merchants m
    JOIN users u ON m.user_id = u.id
    ORDER BY m.created_at DESC
");
$merchants = $stmt->fetchAll();

// Calculations
$totalMerchants = count($merchants);
$activeMerchants = count(array_filter($merchants, function($m) { return $m['is_active'] == 1; }));
$pendingMerchants = $totalMerchants - $activeMerchants;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodRescue - Admin Merchant Portal</title>
    <link rel="shortcut icon" href="./assets/images/favicon.ico" type="image/x-icon">
    
    <!-- Google Fonts: Outfit & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS output -->
    <link rel="stylesheet" href="assets/css/output.css">
    
    <!-- CSRF Token for AJAX -->
    <meta name="csrf-token" content="<?= $csrfToken ?>">
    
    <!-- FontAwesome icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 text-slate-800 font-sans min-h-screen flex flex-col antialiased">

    <!-- Admin Header -->
    <header class="sticky top-0 z-40 bg-slate-900 text-white shadow-md">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
            <a href="index.php" class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-emerald-500 flex items-center justify-center text-white">
                    <i class="fa-solid fa-utensils text-sm"></i>
                </div>
                <div>
                    <span class="font-extrabold text-sm tracking-tight text-white block">FoodRescue Admin</span>
                    <span class="block text-[9px] text-slate-400 font-medium tracking-wide">VERIFIKASI MITRA MERCHANT</span>
                </div>
            </a>
            
            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <span class="block text-xs font-bold text-slate-200"><?= htmlspecialchars($currentUser['username']) ?></span>
                    <span class="block text-[9px] text-emerald-400 uppercase font-semibold">Administrator</span>
                </div>
                <button onclick="handleLogout()" class="py-1.5 px-3 bg-red-600 hover:bg-red-700 text-white rounded-full text-xs font-bold transition flex items-center gap-1.5 shadow">
                    <i class="fa-solid fa-right-from-bracket"></i> Keluar
                </button>
            </div>
        </div>
    </header>

    <main class="flex-grow max-w-6xl mx-auto w-full px-4 py-8 space-y-8">
        <!-- Welcoming title -->
        <div>
            <h1 class="text-2xl font-black text-slate-800">Verifikasi & Manajemen Toko</h1>
            <p class="text-xs text-slate-400 mt-1">Kelola data pendaftaran dan status keaktifan merchant FoodRescue</p>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Card 1: Total -->
            <div class="bg-white p-5 rounded-3xl border border-slate-200/50 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-store"></i>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase">Pendaftar Merchant</span>
                    <span class="block text-xl font-extrabold text-slate-800 mt-0.5"><?= $totalMerchants ?> Toko</span>
                </div>
            </div>
            
            <!-- Card 2: Active -->
            <div class="bg-white p-5 rounded-3xl border border-slate-200/50 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase">Merchant Aktif</span>
                    <span class="block text-xl font-extrabold text-emerald-600 mt-0.5"><?= $activeMerchants ?> Toko</span>
                </div>
            </div>

            <!-- Card 3: Pending -->
            <div class="bg-white p-5 rounded-3xl border border-slate-200/50 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase">Menunggu Verifikasi</span>
                    <span class="block text-xl font-extrabold text-amber-600 mt-0.5"><?= $pendingMerchants ?> Toko</span>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="font-extrabold text-slate-700 text-sm">Daftar Mitra Merchant</h3>
                <span class="text-xs bg-slate-200/80 text-slate-600 font-bold px-2.5 py-0.5 rounded-full"><?= $totalMerchants ?> Total</span>
            </div>

            <!-- Desktop Table View / Mobile Details List -->
            <div class="overflow-x-auto">
                <?php if (empty($merchants)): ?>
                    <div class="py-16 text-center text-slate-400 space-y-2">
                        <i class="fa-solid fa-store-slash text-4xl text-slate-200"></i>
                        <p class="text-sm">Belum ada merchant yang mendaftarkan diri.</p>
                    </div>
                <?php else: ?>
                    <table class="w-full text-left border-collapse min-w-[800px] text-xs">
                        <thead>
                            <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider bg-slate-50/30">
                                <th class="py-3.5 px-6">Informasi Toko</th>
                                <th class="py-3.5 px-6">Pemilik (User)</th>
                                <th class="py-3.5 px-6">Kontak / Telepon</th>
                                <th class="py-3.5 px-6">Lokasi Koordinat</th>
                                <th class="py-3.5 px-6">Terdaftar Pada</th>
                                <th class="py-3.5 px-6">Status</th>
                                <th class="py-3.5 px-6 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <?php foreach ($merchants as $m): ?>
                                <tr class="hover:bg-slate-50/50 transition duration-150">
                                    <!-- Store Info -->
                                    <td class="py-4 px-6 space-y-1">
                                        <span class="font-extrabold text-slate-800 text-sm block"><?= htmlspecialchars($m['business_name']) ?></span>
                                        <span class="text-slate-400 block max-w-xs truncate" title="<?= htmlspecialchars($m['address']) ?>">
                                            <i class="fa-solid fa-map-pin text-[10px] mr-1 text-slate-300"></i><?= htmlspecialchars($m['address']) ?>
                                        </span>
                                    </td>
                                    
                                    <!-- Owner Account -->
                                    <td class="py-4 px-6">
                                        <span class="font-bold text-slate-700 block"><?= htmlspecialchars($m['owner_username']) ?></span>
                                        <span class="text-[10px] text-slate-400 block"><?= htmlspecialchars($m['owner_email']) ?></span>
                                    </td>
                                    
                                    <!-- Contact -->
                                    <td class="py-4 px-6">
                                        <a href="https://wa.me/<?= htmlspecialchars($m['phone']) ?>" target="_blank" class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 hover:underline font-bold transition">
                                            <i class="fa-brands fa-whatsapp text-emerald-500"></i> <?= htmlspecialchars($m['phone']) ?>
                                        </a>
                                    </td>
                                    
                                    <!-- Coordinates -->
                                    <td class="py-4 px-6">
                                        <a href="https://www.openstreetmap.org/?mlat=<?= htmlspecialchars($m['latitude']) ?>&mlon=<?= htmlspecialchars($m['longitude']) ?>#map=17/<?= htmlspecialchars($m['latitude']) ?>/<?= htmlspecialchars($m['longitude']) ?>" target="_blank" class="text-blue-600 hover:underline font-medium flex items-center gap-1">
                                            <i class="fa-solid fa-location-crosshairs text-slate-400"></i> <?= htmlspecialchars(round($m['latitude'], 4)) ?>, <?= htmlspecialchars(round($m['longitude'], 4)) ?>
                                        </a>
                                    </td>
                                    
                                    <!-- Registered At -->
                                    <td class="py-4 px-6 text-slate-400">
                                        <?= date('d/m/Y H:i', strtotime($m['created_at'])) ?>
                                    </td>
                                    
                                    <!-- Status -->
                                    <td class="py-4 px-6">
                                        <?php if ($m['is_active'] == 1): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Inaktif
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Action -->
                                    <td class="py-4 px-6 text-center">
                                        <?php if ($m['is_active'] == 1): ?>
                                            <button onclick="toggleMerchantStatus(<?= $m['id'] ?>, 0)" class="py-1.5 px-3 bg-rose-50 hover:bg-rose-100 text-rose-700 hover:border-rose-300 border border-rose-200 rounded-xl font-bold transition">
                                                Nonaktifkan
                                            </button>
                                        <?php else: ?>
                                            <button onclick="toggleMerchantStatus(<?= $m['id'] ?>, 1)" class="py-1.5 px-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold shadow transition">
                                                Verifikasi & Aktifkan
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Back to main app shortcut link -->
        <div class="text-center">
            <a href="index.php" class="inline-flex items-center gap-2 py-2.5 px-6 rounded-full bg-slate-900 text-white text-xs font-bold shadow hover:bg-slate-800 transition">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Tampilan Pengguna
            </a>
        </div>
    </main>

    <footer class="bg-white border-t border-slate-200/50 py-6 text-center text-xs text-slate-400 mt-12">
        <div class="max-w-6xl mx-auto px-4">
            &copy; 2026 FoodRescue Admin Portal. Seluruh hak cipta dilindungi.
        </div>
    </footer>

    <!-- Toast Container -->
    <div id="toast-container"></div>

    <!-- Include core scripts -->
    <script src="assets/js/app.js"></script>
</body>
</html>


