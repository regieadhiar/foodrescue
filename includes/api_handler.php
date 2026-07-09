<?php
// includes/api_handler.php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/auth.php';

// Set response header to JSON
header('Content-Type: application/json');

/**
 * Send JSON response helper
 */
function send_json($status, $message, $data = []) {
    echo json_encode(array_merge([
        'success' => $status,
        'message' => $message
    ], $data));
    exit;
}

/**
 * Handle user registration
 */
function api_register($data) {
    global $pdo;
    
    $username = trim($data['username'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    
    if (empty($username) || empty($email) || empty($password)) {
        send_json(false, 'Semua data registrasi harus diisi.');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        send_json(false, 'Format email tidak valid.');
    }
    
    if (strlen($password) < 6) {
        send_json(false, 'Password minimal terdiri dari 6 karakter.');
    }
    
    try {
        // Check if username/email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            send_json(false, 'Username atau email sudah terdaftar.');
        }
        
        // Insert user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'rescuer')");
        $stmt->execute([$username, $email, $hashedPassword]);
        
        $userId = $pdo->lastInsertId();
        
        // Auto-login
        login_user($userId, $username, 'rescuer', true);
        
        send_json(true, 'Registrasi berhasil! Anda telah masuk.', ['role' => 'rescuer']);
    } catch (PDOException $e) {
        send_json(false, 'Gagal melakukan registrasi: ' . $e->getMessage());
    }
}

/**
 * Handle user login
 */
function api_login($data) {
    global $pdo;
    
    $identity = trim($data['identity'] ?? ''); // username or email
    $password = $data['password'] ?? '';
    $remember = isset($data['remember']) && $data['remember'] == '1';
    
    if (empty($identity) || empty($password)) {
        send_json(false, 'Username/Email dan Password harus diisi.');
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$identity, $identity]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($password, $user['password'])) {
            send_json(false, 'Username/Email atau password salah.');
        }
        
        login_user($user['id'], $user['username'], $user['role'], $remember);
        
        send_json(true, 'Login berhasil! Selamat datang kembali.', ['role' => $user['role']]);
    } catch (PDOException $e) {
        send_json(false, 'Gagal login: ' . $e->getMessage());
    }
}

/**
 * Handle user logout
 */
function api_logout() {
    logout_user();
    send_json(true, 'Berhasil keluar.');
}

/**
 * Register as Merchant
 */
function api_register_merchant($data) {
    global $pdo;
    
    $businessName = trim($data['business_name'] ?? '');
    $address = trim($data['address'] ?? '');
    $latitude = floatval($data['latitude'] ?? 0);
    $longitude = floatval($data['longitude'] ?? 0);
    $phone = trim($data['phone'] ?? '');
    
    if (empty($businessName) || empty($address) || empty($phone) || $latitude === 0.0 || $longitude === 0.0) {
        send_json(false, 'Semua data toko dan koordinat lokasi map harus diisi.');
    }
    
    $user = get_logged_in_user();
    
    $pdo->beginTransaction();
    try {
        // Case 1: User is not logged in, we need to register user account first
        if (!$user) {
            $username = trim($data['username'] ?? '');
            $email = trim($data['email'] ?? '');
            $password = $data['password'] ?? '';
            
            if (empty($username) || empty($email) || empty($password)) {
                $pdo->rollBack();
                send_json(false, 'Akun merchant belum login. Silakan isi form pembuatan akun merchant.');
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $pdo->rollBack();
                send_json(false, 'Format email tidak valid.');
            }
            
            if (strlen($password) < 6) {
                $pdo->rollBack();
                send_json(false, 'Password minimal terdiri dari 6 karakter.');
            }
            
            // Check existence
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $pdo->rollBack();
                send_json(false, 'Username atau email sudah terdaftar.');
            }
            
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'merchant')");
            $stmt->execute([$username, $email, $hashedPassword]);
            
            $userId = $pdo->lastInsertId();
        } else {
            // Case 2: User is logged in, change role to merchant if they aren't already
            $userId = $user['id'];
            if ($user['role'] !== 'merchant' && $user['role'] !== 'admin') {
                $stmt = $pdo->prepare("UPDATE users SET role = 'merchant' WHERE id = ?");
                $stmt->execute([$userId]);
            }
        }
        
        // Ensure no merchant profile already exists for this user ID
        $stmt = $pdo->prepare("SELECT id FROM merchants WHERE user_id = ?");
        $stmt->execute([$userId]);
        if ($stmt->fetch()) {
            $pdo->rollBack();
            send_json(false, 'Akun Anda sudah memiliki profil Merchant.');
        }
        
        // Insert Merchant Profile (Initially inactive = 0, waiting for admin)
        $stmt = $pdo->prepare("INSERT INTO merchants (user_id, business_name, address, latitude, longitude, phone, is_active) VALUES (?, ?, ?, ?, ?, ?, 0)");
        $stmt->execute([$userId, $businessName, $address, $latitude, $longitude, $phone]);
        
        $pdo->commit();
        
        // Log in/re-login the user to update session roles
        $stmtUser = $pdo->prepare("SELECT id, username, role FROM users WHERE id = ?");
        $stmtUser->execute([$userId]);
        $updatedUser = $stmtUser->fetch();
        
        login_user($updatedUser['id'], $updatedUser['username'], $updatedUser['role'], true);
        
        send_json(true, 'Registrasi merchant berhasil! Menunggu verifikasi admin agar akun aktif.', ['role' => 'merchant']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        send_json(false, 'Gagal mendaftar sebagai merchant: ' . $e->getMessage());
    }
}

/**
 * Add a food item (Merchant only)
 */
function api_add_food_item($data) {
    global $pdo;
    
    $user = get_logged_in_user();
    if (!$user || $user['role'] !== 'merchant') {
        send_json(false, 'Akses ditolak. Hanya merchant yang dapat menambahkan item.');
    }
    
    // Fetch merchant details
    $stmt = $pdo->prepare("SELECT id, is_active FROM merchants WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $merchant = $stmt->fetch();
    
    if (!$merchant) {
        send_json(false, 'Profil merchant tidak ditemukan.');
    }
    
    if ($merchant['is_active'] == 0) {
        send_json(false, 'Akun merchant Anda sedang dinonaktifkan atau belum disetujui oleh admin.');
    }
    
    $title = trim($data['title'] ?? '');
    $description = trim($data['description'] ?? '');
    $originalPrice = floatval($data['original_price'] ?? 0);
    $rescuePrice = floatval($data['rescue_price'] ?? 0);
    $quantity = intval($data['quantity'] ?? 0);
    $expiryHours = intval($data['expiry_hours'] ?? 2); // Expiry in hours from now
    
    if (empty($title) || empty($description) || $originalPrice <= 0 || $rescuePrice < 0 || $quantity <= 0) {
        send_json(false, 'Mohon isi semua data makanan dengan benar.');
    }
    
    // Process multiple uploaded images (min 1, max 3)
    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $uploadedPaths = [];
    if (isset($_FILES['food_images']) && !empty($_FILES['food_images']['name'][0])) {
        $files = $_FILES['food_images'];
        $count = count($files['name']);
        
        if ($count < 1 || $count > 3) {
            send_json(false, 'Harap upload minimal 1 dan maksimal 3 foto makanan.');
        }
        
        for ($i = 0; $i < $count; $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                send_json(false, 'Gagal mengupload foto makanan. Error code: ' . $files['error'][$i]);
            }
            
            // Validate image MIME type
            $fileType = mime_content_type($files['tmp_name'][$i]);
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($fileType, $allowedTypes)) {
                send_json(false, 'Format file tidak didukung. Unggah gambar JPG, PNG, atau WebP.');
            }
            
            // Validate size (max 5MB)
            if ($files['size'][$i] > 5 * 1024 * 1024) {
                send_json(false, 'Ukuran foto maksimal adalah 5MB.');
            }
            
            $ext = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
            if (empty($ext)) {
                $ext = 'jpg';
            }
            
            $newFilename = 'food_' . uniqid() . '_' . $i . '.' . $ext;
            $destination = $uploadDir . $newFilename;
            
            if (move_uploaded_file($files['tmp_name'][$i], $destination)) {
                $uploadedPaths[] = 'uploads/' . $newFilename;
            } else {
                send_json(false, 'Gagal menyimpan file foto ke server.');
            }
        }
    } else {
        send_json(false, 'Harap upload minimal 1 foto makanan.');
    }
    
    $imageUrl = json_encode($uploadedPaths);
    
    // Compute datetime matching MySQL context
    $expiryTime = date('Y-m-d H:i:s', strtotime("+$expiryHours hours"));
    
    try {
        $stmt = $pdo->prepare("INSERT INTO food_items (merchant_id, title, description, original_price, rescue_price, quantity, expiry_time, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$merchant['id'], $title, $description, $originalPrice, $rescuePrice, $quantity, $expiryTime, $imageUrl]);
        send_json(true, 'Makanan surplus berhasil dipublikasikan!');
    } catch (PDOException $e) {
        send_json(false, 'Gagal menambahkan makanan: ' . $e->getMessage());
    }
}

/**
 * Get active food items (for map & list feeds)
 */
function api_get_food_items() {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT f.id, f.title, f.description, f.original_price, f.rescue_price, f.quantity, f.expiry_time, f.image_url,
                   m.business_name, m.address, m.latitude, m.longitude, m.phone, m.is_active
            FROM food_items f
            JOIN merchants m ON f.merchant_id = m.id
            WHERE f.quantity > 0 AND f.expiry_time > NOW() AND m.is_active = 1
            ORDER BY f.created_at DESC
        ");
        
        $stmt->execute();
        $items = $stmt->fetchAll();
        
        foreach ($items as &$item) {
            $diff = strtotime($item['expiry_time']) - time();
            $item['minutes_left'] = max(0, ceil($diff / 60));
            
            $rawImageUrl = $item['image_url'] ?? '';
            $images = json_decode($rawImageUrl, true);
            if (is_array($images) && !empty($images)) {
                $item['image_url'] = $images[0];
                $item['all_images'] = $images;
            } else {
                $item['image_url'] = '';
                $item['all_images'] = [];
            }
        }
        
        send_json(true, 'Data loaded.', ['items' => $items]);
    } catch (PDOException $e) {
        send_json(false, 'Gagal memuat item: ' . $e->getMessage());
    }
}

/**
 * Claim food item (Rescuer only)
 */
function api_claim_food_item($data) {
    global $pdo;
    
    $user = get_logged_in_user();
    if (!$user) {
        send_json(false, 'Silakan login terlebih dahulu untuk memesan.');
    }
    
    $foodItemId = intval($data['food_item_id'] ?? 0);
    $quantityToClaim = intval($data['quantity'] ?? 1);
    $paymentMethod = in_array($data['payment_method'] ?? 'cash', ['cash', 'qris']) ? $data['payment_method'] : 'cash';
    
    if ($foodItemId <= 0 || $quantityToClaim <= 0) {
        send_json(false, 'Item makanan tidak valid.');
    }
    
    $pdo->beginTransaction();
    try {
        // Lock food item for update to prevent race conditions
        $stmt = $pdo->prepare("SELECT * FROM food_items WHERE id = ? FOR UPDATE");
        $stmt->execute([$foodItemId]);
        $foodItem = $stmt->fetch();
        
        if (!$foodItem) {
            $pdo->rollBack();
            send_json(false, 'Makanan tidak ditemukan.');
        }
        
        if ($foodItem['quantity'] < $quantityToClaim) {
            $pdo->rollBack();
            send_json(false, 'Stok tidak mencukupi. Hanya tersisa ' . $foodItem['quantity'] . ' porsi.');
        }
        
        if (strtotime($foodItem['expiry_time']) < time()) {
            $pdo->rollBack();
            send_json(false, 'Makanan sudah kedaluwarsa.');
        }
        
        // Decrease quantity
        $newQty = $foodItem['quantity'] - $quantityToClaim;
        $stmt = $pdo->prepare("UPDATE food_items SET quantity = ? WHERE id = ?");
        $stmt->execute([$newQty, $foodItemId]);
        
        // Create order
        $paymentStatus = ($paymentMethod === 'qris') ? 'paid' : 'pending';
        $stmt = $pdo->prepare("INSERT INTO orders (food_item_id, rescuer_id, quantity, status, payment_method, payment_status) VALUES (?, ?, ?, 'claimed', ?, ?)");
        $stmt->execute([$foodItemId, $user['id'], $quantityToClaim, $paymentMethod, $paymentStatus]);
        
        $orderId = $pdo->lastInsertId();
        
        $pdo->commit();
        send_json(true, 'Pesanan berhasil diklaim! Tunjukkan QR tiket saat mengambil makanan.', [
            'order_id' => $orderId,
            'merchant_phone' => $data['merchant_phone'] ?? '',
            'business_name' => $data['business_name'] ?? '',
            'payment_method' => $paymentMethod
        ]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        send_json(false, 'Gagal memproses pesanan: ' . $e->getMessage());
    }
}

/**
 * Toggle Merchant status (Admin only)
 */
function api_toggle_merchant($data) {
    global $pdo;
    
    $user = get_logged_in_user();
    if (!$user || $user['role'] !== 'admin') {
        send_json(false, 'Akses ditolak. Hanya administrator yang diizinkan.');
    }
    
    $merchantId = intval($data['merchant_id'] ?? 0);
    $status = intval($data['status'] ?? 0); // 0 or 1
    
    if ($merchantId <= 0) {
        send_json(false, 'ID merchant tidak valid.');
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE merchants SET is_active = ? WHERE id = ?");
        $stmt->execute([$status, $merchantId]);
        
        $statusStr = $status ? 'diaktifkan' : 'dinonaktifkan';
        send_json(true, "Merchant berhasil $statusStr.");
    } catch (PDOException $e) {
        send_json(false, 'Gagal mengubah status merchant: ' . $e->getMessage());
    }
}

/**
 * Get rescuer claimed orders history (Rescuer only)
 */
function api_get_rescuer_orders() {
    global $pdo;
    
    $user = get_logged_in_user();
    if (!$user) {
        send_json(false, 'Silakan login terlebih dahulu.');
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                o.id, o.quantity, o.status, o.payment_method, o.payment_status, o.created_at, 
                f.title, f.rescue_price, f.image_url,
                m.business_name, m.address, m.phone
            FROM orders o
            JOIN food_items f ON o.food_item_id = f.id
            JOIN merchants m ON f.merchant_id = m.id
            WHERE o.rescuer_id = ?
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$user['id']]);
        $orders = $stmt->fetchAll();
        
        send_json(true, 'Data klaim berhasil diambil.', ['orders' => $orders]);
    } catch (PDOException $e) {
        send_json(false, 'Gagal mengambil riwayat klaim: ' . $e->getMessage());
    }
}

/**
 * Verify QR Code claim ticket (Merchant scans Rescuer's QR code)
 */
function api_verify_qr_claim($data) {
    global $pdo;
    
    $user = get_logged_in_user();
    if (!$user || $user['role'] !== 'merchant') {
        send_json(false, 'Akses ditolak. Hanya merchant yang dapat men-scan tiket QR.');
    }
    
    // Fetch merchant profile
    $stmtMerchant = $pdo->prepare("SELECT id FROM merchants WHERE user_id = ?");
    $stmtMerchant->execute([$user['id']]);
    $merchantId = $stmtMerchant->fetchColumn();
    
    if (!$merchantId) {
        send_json(false, 'Profil merchant Anda tidak ditemukan.');
    }
    
    $qrData = trim($data['qr_data'] ?? '');
    
    // Parse order ID from qr code string format: foodrescue-order:ORDER_ID
    if (strpos($qrData, 'foodrescue-order:') !== 0) {
        send_json(false, 'Format QR Code tidak valid.');
    }
    
    $orderId = intval(substr($qrData, 17));
    if ($orderId <= 0) {
        send_json(false, 'ID order di dalam QR Code tidak valid.');
    }
    
    try {
        // Fetch order details
        $stmtOrder = $pdo->prepare("
            SELECT o.*, f.title, f.rescue_price, f.merchant_id, u.username as rescuer_name
            FROM orders o
            JOIN food_items f ON o.food_item_id = f.id
            JOIN users u ON o.rescuer_id = u.id
            WHERE o.id = ?
        ");
        $stmtOrder->execute([$orderId]);
        $order = $stmtOrder->fetch();
        
        if (!$order) {
            send_json(false, 'Tiket booking tidak ditemukan di database.');
        }
        
        if ($order['merchant_id'] != $merchantId) {
            send_json(false, 'Tiket booking ini bukan milik toko Anda.');
        }
        
        if ($order['status'] === 'completed') {
            send_json(false, 'Tiket booking ini sudah pernah digunakan (Selesai).');
        }
        
        if ($order['status'] === 'cancelled') {
            send_json(false, 'Tiket booking ini telah dibatalkan.');
        }
        
        // Update status to completed (Selesai) and payment status to paid (Lunas)
        $stmtUpdate = $pdo->prepare("UPDATE orders SET status = 'completed', payment_status = 'paid' WHERE id = ?");
        $stmtUpdate->execute([$orderId]);
        
        send_json(true, 'Tiket berhasil diverifikasi! Transaksi selesai.', [
            'order_id' => $orderId,
            'title' => $order['title'],
            'quantity' => $order['quantity'],
            'rescuer_name' => $order['rescuer_name'],
            'payment_method' => $order['payment_method'],
            'total_price' => $order['rescue_price'] * $order['quantity']
        ]);
        
    } catch (PDOException $e) {
        send_json(false, 'Gagal memproses verifikasi QR: ' . $e->getMessage());
    }
}

/**
 * Get all active merchants (for map markers)
 */
function api_get_merchants() {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT id, business_name, address, latitude, longitude, phone, is_active
            FROM merchants
            WHERE is_active = 1
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        $merchants = $stmt->fetchAll();
        
        send_json(true, 'Merchants loaded.', ['merchants' => $merchants]);
    } catch (PDOException $e) {
        send_json(false, 'Gagal memuat merchant: ' . $e->getMessage());
    }
}

/**
 * Handle forgot password request - generate reset token
 */
function api_forgot_password($data) {
    global $pdo;
    
    $email = trim($data['email'] ?? '');
    
    if (empty($email)) {
        send_json(false, 'Email harus diisi.');
    }
    
    try {
        $stmt = $pdo->prepare("SELECT id, email FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            send_json(false, 'Email tidak terdaftar di sistem.');
        }
        
        // Dev environment: no email server, just confirm email exists
        send_json(true, 'Email valid! Silakan buat password baru Anda.', [
            'email' => $email
        ]);
    } catch (PDOException $e) {
        send_json(false, 'Gagal memproses permintaan reset password.');
    }
}

/**
 * Handle password reset with token
 */
function api_reset_password($data) {
    global $pdo;
    
    $email = trim($data['email'] ?? '');
    $newPassword = $data['password'] ?? '';
    
    if (empty($email) || empty($newPassword)) {
        send_json(false, 'Email dan password baru harus diisi.');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        send_json(false, 'Format email tidak valid.');
    }
    
    if (strlen($newPassword) < 6) {
        send_json(false, 'Password minimal terdiri dari 6 karakter.');
    }
    
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            send_json(false, 'Email tidak terdaftar di sistem.');
        }
        
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $user['id']]);
        
        send_json(true, 'Password berhasil diubah! Silakan masuk dengan password baru Anda.');
    } catch (PDOException $e) {
        send_json(false, 'Gagal mengubah password.');
    }
}

/**
 * Get all initial data in one request (food items + merchants + stats)
 */
function api_get_initial_data() {
    global $pdo;
    try {
        // Food items
        $stmtFood = $pdo->prepare("
            SELECT f.id, f.title, f.description, f.original_price, f.rescue_price, f.quantity, f.expiry_time, f.image_url,
                   m.business_name, m.address, m.latitude, m.longitude, m.phone, m.is_active
            FROM food_items f
            JOIN merchants m ON f.merchant_id = m.id
            WHERE f.quantity > 0 AND f.expiry_time > NOW() AND m.is_active = 1
            ORDER BY f.created_at DESC
        ");
        $stmtFood->execute();
        $items = $stmtFood->fetchAll();
        
        foreach ($items as &$item) {
            $diff = strtotime($item['expiry_time']) - time();
            $item['minutes_left'] = max(0, ceil($diff / 60));
            $rawImageUrl = $item['image_url'] ?? '';
            $images = json_decode($rawImageUrl, true);
            if (is_array($images) && !empty($images)) {
                $item['image_url'] = $images[0];
                $item['all_images'] = $images;
            } else {
                $item['image_url'] = '';
                $item['all_images'] = [];
            }
        }
        
        // Merchants (active only, for map markers)
        $stmtMerchants = $pdo->prepare("
            SELECT id, business_name, address, latitude, longitude, phone, is_active
            FROM merchants WHERE is_active = 1 ORDER BY created_at DESC
        ");
        $stmtMerchants->execute();
        $merchants = $stmtMerchants->fetchAll();
        
        // Stats
        $stmtStats = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) as total_portions FROM orders WHERE status = 'completed'");
        $stmtStats->execute();
        $totalPortions = $stmtStats->fetchColumn();
        $co2Saved = round($totalPortions * 0.5, 1);
        
        send_json(true, 'Initial data loaded.', [
            'items' => $items,
            'merchants' => $merchants,
            'total_portions' => intval($totalPortions),
            'co2_saved' => $co2Saved
        ]);
    } catch (PDOException $e) {
        send_json(false, 'Gagal memuat data: ' . $e->getMessage());
    }
}

/**
 * Update user & merchant profile
 */
function api_update_profile($data) {
    global $pdo;
    $user = get_logged_in_user();
    if (!$user) {
        send_json(false, 'Anda harus login.');
    }

    $username = trim($data['username'] ?? '');
    $email = trim($data['email'] ?? '');
    if (empty($username) || empty($email)) {
        send_json(false, 'Username dan email wajib diisi.');
    }

    // Check uniqueness
    $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $stmt->execute([$username, $email, $user['id']]);
    if ($stmt->fetch()) {
        send_json(false, 'Username atau email sudah digunakan.');
    }

    $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?")->execute([$username, $email, $user['id']]);

    // Update session
    $_SESSION['username'] = $username;

    // If merchant, update merchant fields
    if ($user['role'] === 'merchant') {
        $business_name = trim($data['business_name'] ?? '');
        $address = trim($data['address'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $latitude = floatval($data['latitude'] ?? 0);
        $longitude = floatval($data['longitude'] ?? 0);

        if ($latitude != 0 && $longitude != 0) {
            $stmtM = $pdo->prepare("UPDATE merchants SET business_name = ?, address = ?, phone = ?, latitude = ?, longitude = ? WHERE user_id = ?");
            $stmtM->execute([$business_name, $address, $phone, $latitude, $longitude, $user['id']]);
        } else {
            $stmtM = $pdo->prepare("UPDATE merchants SET business_name = ?, address = ?, phone = ? WHERE user_id = ?");
            $stmtM->execute([$business_name, $address, $phone, $user['id']]);
        }
    }

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_picture'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $fileType = mime_content_type($file['tmp_name']);
        if (!in_array($fileType, $allowedTypes)) {
            send_json(false, 'Format foto profil harus JPG, PNG, atau WebP.');
        }
        if ($file['size'] > 2 * 1024 * 1024) {
            send_json(false, 'Ukuran foto profil maksimal 2MB.');
        }
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'jpg';
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $filename = 'profile_' . $user['id'] . '_' . uniqid() . '.' . $ext;
        $dest = $uploadDir . $filename;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $profileUrl = 'uploads/' . $filename;
            $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?")->execute([$profileUrl, $user['id']]);
        }
    }

    send_json(true, 'Profil berhasil diperbarui.');
}

/**
 * Deactivate merchant account (set is_active=0)
 */
function api_deactivate_merchant() {
    global $pdo;
    $user = get_logged_in_user();
    if (!$user || $user['role'] !== 'merchant') {
        send_json(false, 'Hanya merchant yang dapat mengajukan nonaktif.');
    }

    $pdo->prepare("UPDATE merchants SET is_active = 0 WHERE user_id = ?")->execute([$user['id']]);
    $_SESSION['merchant_active'] = 0;

    send_json(true, 'Pengajuan nonaktif berhasil. Toko Anda sekarang tidak aktif.');
}

/**
 * Get platform statistics (CO2 saved, portions rescued, merchants, rescuers)
 */
function api_get_stats() {
    global $pdo;
    try {
        // Total portions rescued (completed orders)
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) as total_portions FROM orders WHERE status = 'completed'");
        $stmt->execute();
        $totalPortions = $stmt->fetchColumn();
        
        // Total active merchants
        $stmtM = $pdo->prepare("SELECT COUNT(*) FROM merchants WHERE is_active = 1");
        $stmtM->execute();
        $totalMerchants = $stmtM->fetchColumn();
        
        // Total rescuers (users with role rescuer)
        $stmtR = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'rescuer'");
        $stmtR->execute();
        $totalRescuers = $stmtR->fetchColumn();
        
        // Estimate CO2: ~0.5kg CO2 per portion of food saved from landfill
        $co2Saved = round($totalPortions * 0.5, 1);
        
        send_json(true, 'Stats loaded.', [
            'total_portions' => intval($totalPortions),
            'total_merchants' => intval($totalMerchants),
            'total_rescuers' => intval($totalRescuers),
            'co2_saved' => $co2Saved
        ]);
    } catch (PDOException $e) {
        send_json(false, 'Gagal memuat statistik.');
    }
}
