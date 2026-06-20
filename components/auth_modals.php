<?php
// components/auth_modals.php
require_once __DIR__ . '/../includes/auth.php';
$currentUser = get_logged_in_user();
$csrfToken = get_csrf_token();
?>

<!-- Base Modal Overlay -->
<div id="modal-backdrop" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden opacity-0 transition-opacity duration-300 flex items-center justify-center p-4">
    
    <!-- 1. LOGIN MODAL -->
    <div id="login-modal" class="modal-content hidden bg-white w-full max-w-sm rounded-3xl shadow-xl overflow-hidden transform scale-95 transition-transform duration-300">
        <div class="relative px-6 py-8">
            <button onclick="closeActiveModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 mb-3">
                    <i class="fa-solid fa-right-to-bracket text-xl"></i>
                </div>
                <h3 class="font-bold text-xl text-slate-800">Selamat Datang Kembali</h3>
                <p class="text-xs text-slate-400 mt-1">Masuk untuk menyelamatkan makanan surplus hari ini</p>
            </div>
            
            <form id="login-form" onsubmit="submitLogin(event)" class="space-y-4">
                <input type="hidden" name="action" value="login">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Username atau Email</label>
                    <input type="text" name="identity" required placeholder="Masukkan username/email" class="w-full px-4 py-3 text-sm rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Password</label>
                    <input type="password" name="password" required placeholder="Masukkan password" class="w-full px-4 py-3 text-sm rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition">
                </div>
                
                <div class="flex items-center justify-between text-xs py-1">
                    <label class="flex items-center gap-2 text-slate-600 cursor-pointer">
                        <input type="checkbox" name="remember" value="1" checked class="w-4 h-4 rounded text-emerald-600 border-slate-300 focus:ring-emerald-500">
                        <span>Ingat saya di HP ini</span>
                    </label>
                    <button type="button" onclick="switchModal('forgot-password-modal')" class="text-emerald-600 font-semibold hover:underline">Lupa Password?</button>
                </div>
                
                <!-- Inline error message for login -->
                <div id="login-error-msg" class="hidden bg-red-50 border border-red-200 text-red-700 text-xs font-medium px-4 py-2.5 rounded-xl flex items-center gap-2">
                    <i class="fa-solid fa-circle-xmark text-red-500"></i>
                    <span></span>
                </div>

                <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold shadow-md shadow-emerald-100 hover:shadow-emerald-200 transition duration-200">
                    Masuk Sekarang
                </button>
            </form>
            
            <div class="text-center mt-6 pt-4 border-t border-slate-100 text-xs text-slate-500">
                Belum punya akun? 
                <button onclick="switchModal('register-modal')" class="text-emerald-600 font-semibold hover:underline">Daftar Rescuer</button>
            </div>
        </div>
    </div>

    <!-- 1b. FORGOT PASSWORD MODAL -->
    <div id="forgot-password-modal" class="modal-content hidden bg-white w-full max-w-sm rounded-3xl shadow-xl overflow-hidden transform scale-95 transition-transform duration-300">
        <div class="relative px-6 py-8">
            <button onclick="closeActiveModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 mb-3">
                    <i class="fa-solid fa-key text-xl"></i>
                </div>
                <h3 class="font-bold text-xl text-slate-800">Lupa Password?</h3>
                <p class="text-xs text-slate-400 mt-1">Masukkan email Anda dan kami akan mengirimkan instruksi reset password</p>
            </div>
            
            <form id="forgot-password-form" onsubmit="submitForgotPassword(event)" class="space-y-4">
                <input type="hidden" name="action" value="forgot_password">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Email Terdaftar</label>
                    <input type="email" name="email" required placeholder="Masukkan alamat email" class="w-full px-4 py-3 text-sm rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition">
                </div>
                
                <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold shadow-md shadow-emerald-100 hover:shadow-emerald-200 transition duration-200">
                    Kirim Instruksi Reset
                </button>
            </form>
            
            <div id="forgot-password-result" class="hidden mt-4"></div>
            
            <div class="text-center mt-6 pt-4 border-t border-slate-100 text-xs text-slate-500">
                Ingat password Anda?
                <button onclick="switchModal('login-modal')" class="text-emerald-600 font-semibold hover:underline">Kembali ke Login</button>
            </div>
        </div>
    </div>

    <!-- 1c. RESET PASSWORD MODAL -->
    <div id="reset-password-modal" class="modal-content hidden bg-white w-full max-w-sm rounded-3xl shadow-xl overflow-hidden transform scale-95 transition-transform duration-300">
        <div class="relative px-6 py-8">
            <button onclick="closeActiveModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 mb-3">
                    <i class="fa-solid fa-lock text-xl"></i>
                </div>
                <h3 class="font-bold text-xl text-slate-800">Buat Password Baru</h3>
                <p class="text-xs text-slate-400 mt-1">Masukkan password baru Anda di bawah ini</p>
            </div>
            
            <form id="reset-password-form" onsubmit="submitResetPassword(event)" class="space-y-4">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="email" id="reset-email-input" value="">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Password Baru</label>
                    <input type="password" name="password" required placeholder="Minimal 6 karakter" class="w-full px-4 py-3 text-sm rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Konfirmasi Password</label>
                    <input type="password" name="password_confirm" required placeholder="Ulangi password baru" class="w-full px-4 py-3 text-sm rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition">
                </div>
                
                <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold shadow-md shadow-emerald-100 hover:shadow-emerald-200 transition duration-200">
                    Ubah Password
                </button>
            </form>
            
            <div class="text-center mt-6 pt-4 border-t border-slate-100 text-xs text-slate-500">
                <button onclick="switchModal('login-modal')" class="text-emerald-600 font-semibold hover:underline">Kembali ke Login</button>
            </div>
        </div>
    </div>

    <!-- 2. REGISTER RESCUER MODAL -->
    <div id="register-modal" class="modal-content hidden bg-white w-full max-w-sm rounded-3xl shadow-xl overflow-hidden transform scale-95 transition-transform duration-300">
        <div class="relative px-6 py-8">
            <button onclick="closeActiveModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 mb-3">
                    <i class="fa-solid fa-user-plus text-xl"></i>
                </div>
                <h3 class="font-bold text-xl text-slate-800">Daftar Sebagai Rescuer</h3>
                <p class="text-xs text-slate-400 mt-1">Bergabung bersama ribuan penyelamat makanan</p>
            </div>
            
            <form id="register-form" onsubmit="submitRegister(event)" class="space-y-4">
                <input type="hidden" name="action" value="register">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Username</label>
                    <input type="text" name="username" required placeholder="Pilih username unik" class="w-full px-4 py-3 text-sm rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Email</label>
                    <input type="email" name="email" required placeholder="Masukkan alamat email aktif" class="w-full px-4 py-3 text-sm rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Password</label>
                    <input type="password" name="password" required placeholder="Minimal 6 karakter" class="w-full px-4 py-3 text-sm rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition">
                </div>
                
                <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold shadow-md shadow-emerald-100 hover:shadow-emerald-200 transition duration-200">
                    Daftar Sekarang
                </button>
            </form>
            
            <div class="text-center mt-6 pt-4 border-t border-slate-100 text-xs text-slate-500">
                Sudah punya akun? 
                <button onclick="switchModal('login-modal')" class="text-emerald-600 font-semibold hover:underline">Masuk</button>
            </div>
        </div>
    </div>

    <!-- 3. MERCHANT REGISTRATION MODAL -->
    <div id="merchant-reg-modal" class="modal-content hidden bg-white w-full max-w-md rounded-3xl shadow-xl overflow-hidden transform scale-95 transition-transform duration-300 max-h-[90vh] overflow-y-auto no-scrollbar">
        <div class="relative px-6 py-6">
            <button onclick="closeActiveModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition z-10">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
            
            <div class="text-center mb-4">
                <div class="inline-flex items-center justify-center w-11 h-11 rounded-2xl bg-amber-50 text-amber-600 mb-2">
                    <i class="fa-solid fa-store text-lg"></i>
                </div>
                <h3 class="font-bold text-lg text-slate-800">Daftarkan Toko Anda</h3>
                <p class="text-xs text-slate-400 mt-0.5">Jual makanan berlebih dan kurangi sampah pangan</p>
            </div>
            
            <form id="merchant-reg-form" onsubmit="submitMerchantReg(event)" class="space-y-3.5">
                <input type="hidden" name="action" value="register_merchant">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                
                <!-- Account details (Only shown if NOT logged in) -->
                <?php if (!$currentUser): ?>
                    <div class="bg-amber-50/50 p-3.5 rounded-2xl border border-amber-100/60 space-y-3">
                        <span class="block text-xs font-bold text-amber-800 uppercase tracking-wide">Buat Akun Merchant Baru</span>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-500 mb-0.5">Username</label>
                            <input type="text" name="username" required placeholder="Username unik toko" class="w-full px-3.5 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:border-emerald-500 transition bg-white">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-500 mb-0.5">Email</label>
                                <input type="email" name="email" required placeholder="Email aktif" class="w-full px-3.5 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:border-emerald-500 transition bg-white">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-500 mb-0.5">Password</label>
                                <input type="password" name="password" required placeholder="Min 6 char" class="w-full px-3.5 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:border-emerald-500 transition bg-white">
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Shop details -->
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-0.5">Nama Toko/Restoran</label>
                        <input type="text" name="business_name" required placeholder="Contoh: Roti Gembong Barokah" class="w-full px-3.5 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:border-emerald-500 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-0.5">Nomor HP/WhatsApp Toko</label>
                        <input type="tel" name="phone" required placeholder="Contoh: 08123456789" class="w-full px-3.5 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:border-emerald-500 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-0.5">Alamat Lengkap Toko</label>
                        <textarea name="address" required rows="2" placeholder="Masukkan jalan, nomor, RT/RW, kelurahan" class="w-full px-3.5 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:border-emerald-500 transition resize-none"></textarea>
                    </div>
                    
                    <!-- Coordinate selection map picker -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1 flex justify-between items-center">
                            <span>Tentukan Lokasi Toko di Peta</span>
                            <span class="text-[10px] text-emerald-600 font-normal"><i class="fa-solid fa-location-crosshairs mr-1"></i>Klik pada peta untuk geser pin</span>
                        </label>
                        <!-- Mini Map container -->
                        <div id="modal-map-picker" class="h-32 w-full rounded-xl border border-slate-200 z-10"></div>
                        
                        <!-- Invisible fields to send coordinate -->
                        <div class="grid grid-cols-2 gap-2 mt-2">
                            <div>
                                <label class="text-[9px] text-slate-400 block">Latitude</label>
                                <input type="text" name="latitude" id="reg-lat" readonly required class="w-full px-2 py-1 text-[11px] rounded bg-slate-100 border border-slate-200 text-slate-500 focus:outline-none text-center">
                            </div>
                            <div>
                                <label class="text-[9px] text-slate-400 block">Longitude</label>
                                <input type="text" name="longitude" id="reg-longitude" readonly required class="w-full px-2 py-1 text-[11px] rounded bg-slate-100 border border-slate-200 text-slate-500 focus:outline-none text-center">
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="w-full py-3 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-sm font-semibold shadow-md shadow-amber-100 hover:shadow-amber-200 transition duration-200 mt-2">
                    Daftar Sebagai Merchant
                </button>
            </form>
        </div>
    </div>

    <!-- 4. ORDERS HISTORY MODAL (RESCUER CLAIMS) -->
    <div id="orders-history-modal" class="modal-content hidden bg-white w-full max-w-md rounded-3xl shadow-xl overflow-hidden transform scale-95 transition-transform duration-300 max-h-[85vh] flex flex-col">
        <div class="relative px-6 py-5 border-b border-slate-100 flex justify-between items-center">
            <h3 class="font-bold text-base text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-receipt text-emerald-600"></i>
                Riwayat Klaim Makanan Saya
            </h3>
            <button onclick="closeActiveModal()" class="text-slate-400 hover:text-slate-600 transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        
        <div class="p-6 overflow-y-auto no-scrollbar flex-1 space-y-4" id="orders-list-container">
            <!-- Dynamic claims injected here -->
            <div class="text-center py-8 text-slate-400 text-xs">
                <i class="fa-solid fa-spinner fa-spin text-xl text-emerald-500 mb-2 block"></i>
                Memuat riwayat klaim...
            </div>
        </div>
    </div>

    <!-- 6. QR TICKET MODAL -->
    <div id="qr-ticket-modal" class="modal-content hidden bg-white w-full max-w-sm rounded-3xl shadow-xl overflow-hidden transform scale-95 transition-transform duration-300">
        <div class="relative px-6 py-8">
            <button onclick="closeActiveModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
            <div class="text-center mb-5">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 mb-3 animate-pulse">
                    <i class="fa-solid fa-qrcode text-xl"></i>
                </div>
                <h3 class="font-bold text-base text-slate-800" id="qr-ticket-title">Tiket Pengambilan</h3>
                <p class="text-xs text-slate-400 mt-1">Tunjukkan QR Code ini ke kasir merchant untuk verifikasi pengambilan makanan</p>
            </div>
            
            <div class="flex items-center justify-center p-4 bg-slate-50 rounded-2xl border border-slate-100 max-w-[200px] mx-auto mb-4 shadow-inner">
                <img id="qr-ticket-image" src="" alt="QR Code Ticket" class="w-full h-full object-contain">
            </div>
            
            <div class="text-center text-xs space-y-1 bg-emerald-50/50 py-3 px-4 rounded-xl border border-emerald-100/30">
                <div class="font-bold text-slate-800 text-sm" id="qr-ticket-store">Nama Toko</div>
                <div class="text-slate-600 font-medium" id="qr-ticket-qty">Jumlah: 1 Porsi</div>
                <div id="qr-ticket-payment" class="font-extrabold text-emerald-700">Metode: Cash</div>
                <div id="qr-ticket-order-id" class="text-[9px] text-slate-400 font-mono pt-1">ID: #0</div>
            </div>
        </div>
    </div>

    <!-- 7. QRIS PAYMENT MODAL -->
    <div id="qris-payment-modal" class="modal-content hidden bg-white w-full max-w-sm rounded-3xl shadow-xl overflow-hidden transform scale-95 transition-transform duration-300">
        <div class="relative px-6 py-6 text-center space-y-4">
            <button onclick="closeActiveModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
            <div>
                <span class="block text-[10px] text-emerald-600 font-extrabold uppercase tracking-wider">Pembayaran Elektronik</span>
                <h3 class="font-extrabold text-base text-slate-800">QRIS FoodRescue</h3>
            </div>
            
            <div class="w-44 h-44 mx-auto bg-white border border-slate-200/60 rounded-2xl flex items-center justify-center p-3 shadow-md relative">
                <!-- Mock QRIS code using server API -->
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=qris-foodrescue-payment" class="w-full h-full object-contain" alt="QRIS Code">
                <div class="absolute -bottom-2 bg-slate-900 text-[8px] text-slate-200 font-bold px-2 py-0.5 rounded-full shadow border border-slate-800 uppercase">GPN / QRIS NYATA</div>
            </div>
            
            <div class="bg-emerald-50/60 p-4 rounded-2xl border border-emerald-100/30 text-xs">
                <div class="text-slate-500 font-medium">Total Pembayaran:</div>
                <div class="text-xl font-black text-emerald-600 mt-0.5" id="qris-payment-amount">Rp0</div>
                <div class="text-[9px] text-slate-400 mt-1">Penerima: <span id="qris-payment-merchant" class="font-bold text-slate-600">Toko</span></div>
            </div>
            
            <button onclick="confirmQrisPayment()" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-50 transition active:scale-95 duration-200">
                Saya Sudah Membayar (Konfirmasi)
            </button>
        </div>
      </div>



<!-- 6. LOGOUT CONFIRMATION MODAL -->
<div id="logout-confirm-modal" class="modal-content hidden bg-white w-full max-w-xs rounded-3xl shadow-xl overflow-hidden transform scale-95 transition-transform duration-300 flex items-center justify-center">
    <div class="relative px-6 py-8 text-center">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-red-50 text-red-500 mb-4">
            <i class="fa-solid fa-right-from-bracket text-2xl"></i>
        </div>
        <h3 class="font-bold text-lg text-slate-800">Keluar dari Akun?</h3>
        <p class="text-xs text-slate-400 mt-2 mb-6">Anda akan dikembalikan ke halaman utama sebagai tamu.</p>
        
        <div class="flex gap-3">
            <button onclick="closeActiveModal()" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                Batal
            </button>
            <button onclick="confirmLogout()" class="flex-1 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-bold shadow-md shadow-red-100 transition">
                Ya, Keluar
            </button>
        </div>
    </div>
</div>

</div>
<!-- 5. FOOD CLAIM BOTTOM SHEET (Sliding bottom sheet on mobile maps) -->
<div id="food-detail-sheet" class="fixed bottom-0 left-0 right-0 z-50 bg-white rounded-t-[2.5rem] shadow-2xl max-w-md mx-auto transform translate-y-full transition-transform duration-300 border-t border-slate-100 max-h-[80vh] overflow-y-auto no-scrollbar hidden">
    <!-- Drag handle indicator for mobile feel -->
    <div class="w-12 h-1 bg-slate-200 rounded-full mx-auto my-3 cursor-pointer" onclick="closeFoodDetailSheet()"></div>
    
    <div class="px-6 pb-6 pt-1 space-y-4" id="food-detail-content">
        <!-- Renders dynamically when clicking pins -->
    </div>
</div>