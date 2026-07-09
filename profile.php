<?php
// profile.php
require_once __DIR__ . '/includes/auth.php';
$user = get_logged_in_user();
if (!$user) {
    header("Location: index.php");
    exit;
}
global $pdo;

// Fetch merchant details if role is merchant
$merchant = null;
if ($user['role'] === 'merchant') {
    $stmt = $pdo->prepare("SELECT * FROM merchants WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $merchant = $stmt->fetch();
}

include __DIR__ . '/components/header.php';
?>
<?php
$profilePic = !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : null;
?>
<div class="max-w-2xl mx-auto px-4 py-10">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <!-- Profile Header -->
        <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 px-6 py-8 text-white">
            <div class="flex items-center gap-4">
                <?php if ($profilePic): ?>
                    <img src="<?= $profilePic ?>" class="w-16 h-16 rounded-full object-cover border-2 border-white/30 shadow-inner" alt="Profile">
                <?php else: ?>
                    <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center text-2xl font-bold uppercase shadow-inner">
                        <?= substr(htmlspecialchars($user['username']), 0, 1); ?>
                    </div>
                <?php endif; ?>
                <div>
                    <h1 class="text-xl font-extrabold"><?= htmlspecialchars($user['username']) ?></h1>
                    <p class="text-sm text-emerald-100"><?= htmlspecialchars($user['email']) ?></p>
                    <span class="inline-block mt-1.5 px-3 py-0.5 text-[10px] font-bold rounded-full bg-white/20 text-white uppercase">
                        <?= htmlspecialchars($user['role']) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Profile Details -->
        <div class="px-6 py-6 space-y-6">
            <section>
                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Profil</h2>
                <div class="space-y-3 text-sm">
                    <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                </div>
            </section>

            <?php if ($merchant): ?>
            <hr class="border-slate-100">
            <section>
                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Toko</h2>
                <div class="space-y-3 text-sm">
                    <p><strong>Nama Toko:</strong> <?= htmlspecialchars($merchant['business_name']) ?></p>
                    <p><strong>Alamat:</strong> <?= htmlspecialchars($merchant['address']) ?></p>
                    <p><strong>No. HP:</strong> <?= htmlspecialchars($merchant['phone']) ?></p>
                </div>
            </section>
            <?php endif; ?>
            
            <button onclick="toggleEditProfile()" class="w-full py-3 bg-slate-100 text-slate-600 rounded-xl text-sm font-bold">Edit Profil</button>
        </div>

        <!-- Profile Edit Form -->
        <form id="profile-edit-form" onsubmit="submitProfileEdit(event)" class="hidden px-6 py-6 space-y-6 border-t border-slate-100" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_profile">
            <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
            
            <section>
                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Edit Profil</h2>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Foto Profil</label>
                        <input type="file" name="profile_picture" accept="image/jpeg,image/png,image/webp" class="w-full px-4 py-2 text-sm rounded-xl border border-slate-200 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                        <p class="text-[10px] text-slate-400 mt-1">Format: JPG, PNG, WebP. Maks 2MB. Biarkan kosong jika tidak ingin ganti.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Username</label>
                        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" class="w-full px-4 py-2 text-sm rounded-xl border border-slate-200">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="w-full px-4 py-2 text-sm rounded-xl border border-slate-200">
                    </div>
                </div>
            </section>

            <?php if ($merchant): ?>
            <hr class="border-slate-100">
            <section>
                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Edit Toko</h2>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Nama Toko</label>
                        <input type="text" name="business_name" value="<?= htmlspecialchars($merchant['business_name']) ?>" class="w-full px-4 py-2 text-sm rounded-xl border border-slate-200">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Alamat</label>
                        <textarea name="address" class="w-full px-4 py-2 text-sm rounded-xl border border-slate-200"><?= htmlspecialchars($merchant['address']) ?></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">No. HP</label>
                        <input type="tel" name="phone" value="<?= htmlspecialchars($merchant['phone']) ?>" class="w-full px-4 py-2 text-sm rounded-xl border border-slate-200">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Lokasi Toko di Peta</label>
                        <div class="relative mb-2">
                            <input type="text" id="edit-map-search" placeholder="Cari lokasi (jalan, kota, dll)..." class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:border-emerald-500 transition pl-8">
                            <i class="fa-solid fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-[11px]"></i>
                            <div id="edit-map-results" class="hidden absolute top-full left-0 right-0 bg-white border border-slate-200 rounded-lg shadow-lg z-20 max-h-40 overflow-y-auto"></div>
                        </div>
                        <div id="edit-map-picker" class="h-40 w-full rounded-xl border border-slate-200 z-10"></div>
                        <div class="grid grid-cols-2 gap-2 mt-2">
                            <div>
                                <label class="text-[9px] text-slate-400 block">Latitude</label>
                                <input type="text" name="latitude" id="edit-lat" readonly value="<?= htmlspecialchars($merchant['latitude']) ?>" class="w-full px-2 py-1 text-[11px] rounded bg-slate-100 border border-slate-200 text-slate-500 focus:outline-none text-center">
                            </div>
                            <div>
                                <label class="text-[9px] text-slate-400 block">Longitude</label>
                                <input type="text" name="longitude" id="edit-lng" readonly value="<?= htmlspecialchars($merchant['longitude']) ?>" class="w-full px-2 py-1 text-[11px] rounded bg-slate-100 border border-slate-200 text-slate-500 focus:outline-none text-center">
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <?php endif; ?>

            <button type="submit" class="w-full py-3 bg-emerald-600 text-white rounded-xl text-sm font-bold">Simpan Perubahan</button>
        </form>

        <?php if ($merchant): ?>
        <div class="px-6 pb-6">
            <button onclick="submitDeactivateMerchant()" class="w-full py-3 bg-red-50 text-red-600 rounded-xl text-sm font-bold border border-red-100 hover:bg-red-100">
                Pengajuan Nonaktif Merchant
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/components/footer.php'; ?>
