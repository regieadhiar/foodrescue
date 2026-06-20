<?php
// components/footer.php
$currentUser = get_logged_in_user();
?>
    
    <!-- Mobile Bottom Navigation (Floating) -->
    <nav class="fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-slate-100 shadow-lg max-w-md mx-auto h-16 flex items-center justify-around px-2 md:hidden">
        <!-- Tab 1: Peta (Map) -->
        <button id="nav-map-btn" onclick="switchMobileTab('map')" class="flex flex-col items-center justify-center flex-1 text-emerald-600 font-semibold transition">
            <i class="fa-solid fa-map-location-dot text-lg"></i>
            <span class="text-[10px] mt-0.5">Peta Lokasi</span>
        </button>
        
        <!-- Tab 2: List Makanan -->
        <button id="nav-list-btn" onclick="switchMobileTab('list')" class="flex flex-col items-center justify-center flex-1 text-slate-400 hover:text-slate-600 transition">
            <i class="fa-solid fa-store text-lg"></i>
            <span class="text-[10px] mt-0.5">Jelajah Toko</span>
        </button>
        
        <!-- Tab 3: Claimed Orders History -->
        <button id="nav-orders-btn" onclick="openOrdersHistory()" class="flex flex-col items-center justify-center flex-1 text-slate-400 hover:text-slate-600 transition">
            <i class="fa-solid fa-receipt text-lg"></i>
            <span class="text-[10px] mt-0.5">Klaim Saya</span>
        </button>
        
        <!-- Tab 4: Merchant Panel / Become Merchant -->
        <?php if ($currentUser && $currentUser['role'] === 'merchant'): ?>
            <a href="index.php?switch_view=merchant" class="flex flex-col items-center justify-center flex-1 text-slate-400 hover:text-slate-600 transition">
                <i class="fa-solid fa-circle-user text-lg text-emerald-500"></i>
                <span class="text-[10px] mt-0.5">Seller Panel</span>
            </a>
        <?php else: ?>
            <button onclick="openModal('merchant-reg-modal')" class="flex flex-col items-center justify-center flex-1 text-slate-400 hover:text-slate-600 transition">
                <i class="fa-solid fa-shop text-lg"></i>
                <span class="text-[10px] mt-0.5">Jadi Merchant</span>
            </button>
        <?php endif; ?>
    </nav>

    <!-- Bottom Spacing to prevent navbar overlap -->
    <div class="h-16 w-full md:hidden"></div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- Custom Application Javascript Files -->
    <script src="assets/js/app.js"></script>
    <script src="assets/js/map.js"></script>
</body>
</html>
