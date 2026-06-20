<?php
// components/merchant_dashboard.php
require_once __DIR__ . '/../includes/auth.php';

$currentUser = get_logged_in_user();
$csrfToken = get_csrf_token();
if (!$currentUser) {
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

global $pdo;

// Fetch merchant details for logged in user
$stmt = $pdo->prepare("SELECT * FROM merchants WHERE user_id = ?");
$stmt->execute([$currentUser['id']]);
$merchant = $stmt->fetch();

if (!$merchant) {
    // If somehow user has role merchant but no merchant profile, redirect to build profile
    echo "<script>openModal('merchant-reg-modal');</script>";
    echo "<div class='p-6 text-center text-slate-500'>Memuat pendaftaran merchant...</div>";
    exit;
}

$isMerchantActive = (int)$merchant['is_active'];
$merchantId = $merchant['id'];

// Get active products
$stmtItems = $pdo->prepare("
    SELECT *, (expiry_time > NOW() AND quantity > 0) as is_valid
    FROM food_items 
    WHERE merchant_id = ? 
    ORDER BY created_at DESC
");
$stmtItems->execute([$merchantId]);
$allItems = $stmtItems->fetchAll();

// Get orders received
$stmtOrders = $pdo->prepare("
    SELECT o.id, o.quantity, o.status, o.created_at, f.title, f.rescue_price, u.username as rescuer_name, u.email as rescuer_email
    FROM orders o
    JOIN food_items f ON o.food_item_id = f.id
    JOIN users u ON o.rescuer_id = u.id
    WHERE f.merchant_id = ?
    ORDER BY o.created_at DESC
");
$stmtOrders->execute([$merchantId]);
$receivedOrders = $stmtOrders->fetchAll();

// Calculate total revenue from claimed or completed items
$totalRevenue = 0;
$totalClaimsCount = 0;
foreach ($receivedOrders as $order) {
    if ($order['status'] === 'claimed' || $order['status'] === 'completed') {
        $totalRevenue += ($order['rescue_price'] * $order['quantity']);
        $totalClaimsCount++;
    }
}
?>

<div class="flex-grow flex flex-col w-full max-w-4xl mx-auto px-4 pt-4 pb-8">
    
    <!-- Title / Identity Banner -->
    <div class="mb-4 bg-slate-900 text-white rounded-3xl p-5 shadow-lg relative overflow-hidden">
        <div class="absolute -right-4 -bottom-6 w-24 h-24 bg-emerald-500/20 rounded-full blur-xl"></div>
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <span class="text-[10px] text-emerald-400 font-bold uppercase tracking-wider">Dashboard Merchant</span>
                <h2 class="text-xl font-bold text-slate-100 truncate"><?= htmlspecialchars($merchant['business_name']) ?></h2>
                <p class="text-xs text-slate-400 mt-1 flex items-center gap-1.5">
                    <i class="fa-solid fa-map-pin text-emerald-500"></i>
                    <span class="truncate"><?= htmlspecialchars($merchant['address']) ?></span>
                </p>
            </div>
            <div class="text-xs text-slate-400 flex items-center gap-2">
                <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-medium">Merchant ID: <?= $merchantId ?></span>
            </div>
        </div>
    </div>

    <?php if ($isMerchantActive === 0): ?>
        <!-- PENDING VERIFICATION BANNER -->
        <div class="bg-amber-50 border border-amber-200/80 rounded-3xl p-6 text-center space-y-4 shadow-sm animate-soft-bounce max-w-md mx-auto my-6">
            <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center mx-auto">
                <i class="fa-solid fa-hourglass-half text-2xl"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-800 text-base">Toko Sedang Ditinjau Admin</h3>
                <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                    Demi keamanan pembeli, Admin FoodRescue sedang meninjau pendaftaran toko <strong><?= htmlspecialchars($merchant['business_name']) ?></strong>. Akun Anda akan aktif otomatis setelah terverifikasi dalam 1x24 jam.
                </p>
            </div>
            <div class="pt-2">
                <a href="https://wa.me/<?= htmlspecialchars($merchant['phone']) ?>" target="_blank" class="inline-flex items-center gap-2 py-2 px-5 rounded-full bg-white text-xs font-semibold text-slate-700 border border-slate-200 shadow-sm hover:bg-slate-50 transition">
                    <i class="fa-brands fa-whatsapp text-emerald-600"></i> Hubungi Dukungan CS
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- ACTIVE MERCHANT DASHBOARD -->
        <div class="space-y-6">
            
            <!-- Quick stats bar -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i class="fa-solid fa-box-open text-base"></i>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 font-semibold uppercase block">Listing Aktif</span>
                        <span class="font-bold text-slate-800 text-sm">
                            <?= count(array_filter($allItems, function($i) { return $i['is_valid'] == 1; })) ?> Item
                        </span>
                    </div>
                </div>
                
                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i class="fa-solid fa-handshake text-base"></i>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 font-semibold uppercase block">Total Klaim</span>
                        <span class="font-bold text-slate-800 text-sm">
                            <?= $totalClaimsCount ?> Klaim
                        </span>
                    </div>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <i class="fa-solid fa-money-bill-wave text-base"></i>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 font-semibold uppercase block">Pendapatan Rescue</span>
                        <span class="font-bold text-slate-800 text-sm">
                            Rp<?= number_format($totalRevenue, 0, ',', '.') ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Responsive Dashboard Split Layout -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                
                <!-- Left Side: Product Lists & Incoming Claims (2 cols on desktop) -->
                <div class="md:col-span-2 space-y-4">
                    <!-- Tab Toggles: View Listings / Orders History -->
                    <div class="flex bg-slate-100 p-1 rounded-xl">
                        <button onclick="switchMerchantSection('list')" id="m-btn-list" class="m-tab-btn flex-1 py-2 text-xs font-semibold rounded-lg bg-white text-slate-700 shadow-sm transition">Daftar Jualan</button>
                        <button onclick="switchMerchantSection('claims')" id="m-btn-claims" class="m-tab-btn flex-1 py-2 text-xs font-semibold rounded-lg text-slate-500 hover:text-slate-700 transition">Klaim Masuk</button>
                        <button onclick="switchMerchantSection('post')" id="m-btn-post" class="m-tab-btn flex-1 py-2 text-xs font-semibold rounded-lg text-slate-500 hover:text-slate-700 transition md:hidden">Jual Makanan</button>
                    </div>

                    <!-- Section 1: Product Listings -->
                    <div id="m-sect-list" class="merchant-section merchant-section-left space-y-3">
                        <div class="flex items-center justify-between px-1">
                            <h4 class="font-bold text-xs text-slate-500 uppercase tracking-wide">Makanan yang Diposting</h4>
                        </div>
                        
                        <?php if (empty($allItems)): ?>
                            <div class="bg-white rounded-3xl p-8 border border-slate-100 text-center text-slate-400 space-y-2">
                                <i class="fa-solid fa-burger text-3xl text-slate-200"></i>
                                <p class="text-xs">Belum ada makanan surplus yang Anda posting.</p>
                                <button onclick="switchMerchantSection('post')" class="text-xs font-bold text-emerald-600 hover:underline md:hidden">Mulai Posting Pertama <i class="fa-solid fa-arrow-right ml-1"></i></button>
                            </div>
                        <?php else: ?>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-1 gap-3">
                                <?php foreach ($allItems as $item): ?>
                                    <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm flex gap-3 relative hover:border-emerald-500/10 transition">
                                        <?php if ($item['is_valid'] == 0): ?>
                                            <div class="absolute inset-0 bg-slate-100/50 rounded-2xl flex items-center justify-center z-10 border border-slate-200/50">
                                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-slate-500 text-white shadow-sm">KADALUWARSA / HABIS</span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <img src="<?= htmlspecialchars($item['image_url'] ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=150&q=80') ?>" class="w-16 h-16 rounded-xl object-cover border border-slate-100 flex-shrink-0" alt="<?= htmlspecialchars($item['title']) ?>">
                                        
                                        <div class="flex-1 min-w-0">
                                            <h5 class="font-bold text-xs text-slate-700 truncate"><?= htmlspecialchars($item['title']) ?></h5>
                                            <p class="text-[10px] text-slate-400 truncate mt-0.5"><?= htmlspecialchars($item['description']) ?></p>
                                            
                                            <div class="flex items-center gap-2 mt-1.5">
                                                <span class="text-xs font-extrabold text-emerald-600">Rp<?= number_format($item['rescue_price'], 0, ',', '.') ?></span>
                                                <span class="text-[10px] line-through text-slate-400">Rp<?= number_format($item['original_price'], 0, ',', '.') ?></span>
                                            </div>
                                            
                                            <div class="flex items-center justify-between mt-1 text-[9px] font-medium text-slate-400">
                                                <span>Stok: <strong><?= $item['quantity'] ?> porsi</strong></span>
                                                <span>Exp: <strong><?= date('H:i d/m', strtotime($item['expiry_time'])) ?></strong></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Section 3: Incoming Claims -->
                    <div id="m-sect-claims" class="merchant-section merchant-section-left hidden space-y-3">
                        <div class="flex items-center justify-between px-1">
                            <h4 class="font-bold text-xs text-slate-500 uppercase tracking-wide">Klaim Pengambilan Masuk</h4>
                        </div>
                        
                        <?php if (empty($receivedOrders)): ?>
                            <div class="bg-white rounded-3xl p-8 border border-slate-100 text-center text-slate-400 space-y-2">
                                <i class="fa-solid fa-receipt text-3xl text-slate-200"></i>
                                <p class="text-xs">Belum ada klaim pesanan yang masuk ke toko Anda.</p>
                            </div>
                        <?php else: ?>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-1 gap-3">
                                <?php foreach ($receivedOrders as $order): ?>
                                    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm space-y-2 hover:border-emerald-500/10 transition">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h5 class="font-bold text-xs text-slate-700"><?= htmlspecialchars($order['title']) ?></h5>
                                                <span class="text-[10px] text-slate-400">Dipesan oleh: <strong><?= htmlspecialchars($order['rescuer_name']) ?></strong></span>
                                            </div>
                                            <span class="px-2 py-0.5 text-[9px] font-bold rounded-full bg-emerald-50 text-emerald-700 uppercase">
                                                <?= htmlspecialchars($order['status']) ?>
                                            </span>
                                        </div>
                                        
                                        <div class="flex justify-between items-center text-[10px] pt-1.5 border-t border-slate-50 text-slate-500">
                                            <span>Porsi: <strong><?= $order['quantity'] ?>x</strong></span>
                                            <span>Total: <strong>Rp<?= number_format($order['rescue_price'] * $order['quantity'], 0, ',', '.') ?></strong></span>
                                            <span><?= date('H:i d/m', strtotime($order['created_at'])) ?></span>
                                        </div>

                                        <div class="pt-1 flex items-center justify-end gap-2">
                                            <a href="mailto:<?= htmlspecialchars($order['rescuer_email']) ?>" class="py-1 px-3 bg-slate-50 hover:bg-slate-100 text-slate-600 border border-slate-200 rounded-lg text-[9px] font-semibold flex items-center gap-1 transition">
                                                <i class="fa-solid fa-envelope"></i> Email Pembeli
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right Side: Post Food Item Form (Sticky on desktop, hidden on mobile by default) -->
                <div id="m-sect-post" class="merchant-section hidden md:block md:col-span-1 bg-white p-5 rounded-3xl border border-slate-100 shadow-sm space-y-4 md:sticky md:top-20">
                    <h4 class="font-bold text-sm text-slate-800 border-b border-slate-50 pb-2">Posting Surplus Baru</h4>
                    <form id="add-food-form" onsubmit="submitAddFood(event)" enctype="multipart/form-data" class="space-y-3">
                        <input type="hidden" name="action" value="add_food_item">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Nama Makanan</label>
                            <input type="text" name="title" required placeholder="Contoh: Donat Rasa Campur x5" class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Deskripsi & Catatan Pengambilan</label>
                            <textarea name="description" required rows="2" placeholder="Contoh: Kondisi masih bagus, sisa display toko hari ini." class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition resize-none"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Harga Awal (Rp)</label>
                                <input type="number" name="original_price" required placeholder="Harga normal" class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Harga Rescue (Rp)</label>
                                <input type="number" name="rescue_price" required placeholder="Harga diskon" class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Jumlah Porsi</label>
                                <input type="number" name="quantity" required value="1" min="1" class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Batas Ambil</label>
                                <select name="expiry_hours" class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition bg-white">
                                    <option value="1">1 Jam</option>
                                    <option value="2" selected>2 Jam</option>
                                    <option value="3">3 Jam</option>
                                    <option value="5">5 Jam</option>
                                    <option value="8">8 Jam</option>
                                    <option value="12">12 Jam</option>
                                </select>
                            </div>
                        </div>

                        <!-- Multi-Image Upload File Field -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1 flex justify-between items-center">
                                <span>Foto Makanan (Minimal 1, Maksimal 3)</span>
                                <span class="text-[9px] text-slate-400 font-normal">Format: JPG/PNG/WebP</span>
                            </label>
                            <input type="file" name="food_images[]" id="food-images-input" multiple accept="image/*" required onchange="validateImageUploadCount(this)" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                            <div id="image-upload-preview" class="grid grid-cols-3 gap-2 mt-2 hidden"></div>
                        </div>

                        <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold shadow-md shadow-emerald-100 hover:shadow-emerald-200 transition duration-200 mt-2">
                            Publikasikan Makanan
                        </button>
                    </form>
                </div>
                
            </div>
            
        </div>
    <?php endif; ?>
    
</div>
