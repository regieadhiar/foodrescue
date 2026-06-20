<?php
// components/rescuer_dashboard.php
require_once __DIR__ . '/../includes/auth.php';
$currentUser = get_logged_in_user();
?>

<!-- Rescuer Page Wrapper -->
<div class="flex-grow flex flex-col w-full max-w-6xl mx-auto px-4 pt-4 pb-6">
    
    <!-- Mini Stats & Search Header -->
    <div class="mb-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Search Bar & Filter (Left on Desktop) -->
        <div class="flex gap-2 flex-1 md:max-w-md order-2 md:order-1">
            <div class="relative flex-1">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3.5 text-slate-400 text-sm"></i>
                <input type="text" id="food-search-input" oninput="filterFoodList()" placeholder="Cari donat, nasi kotak, roti..." class="w-full pl-9 pr-4 py-3 text-xs rounded-xl bg-white border border-slate-200/80 focus:outline-none focus:border-emerald-500 transition shadow-sm">
            </div>
            <button onclick="toggleFilterModal()" class="w-11 h-11 bg-white hover:bg-slate-50 text-slate-500 hover:text-emerald-600 border border-slate-200/80 rounded-xl flex items-center justify-center shadow-sm transition active:scale-95 duration-200" title="Filter Pencarian">
                <i class="fa-solid fa-sliders text-sm"></i>
            </button>
        </div>

        <!-- Welcoming & Environmental Impact Stat (Right on Desktop) -->
        <div class="flex items-center justify-between md:justify-end gap-6 bg-emerald-50/60 p-3.5 rounded-2xl border border-emerald-100/30 order-1 md:order-2 md:bg-transparent md:border-none md:p-0">
            <div>
                <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider md:text-right">Penyelamatan Lingkungan</span>
                <span class="block text-xs font-semibold text-emerald-800 md:text-right"><i class="fa-solid fa-cloud-arrow-down mr-1"></i><span id="stat-co2">Memuat...</span></span>
            </div>
            <div class="text-right">
                <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider">Porsi Diselamatkan</span>
                <span class="block text-xs font-bold text-emerald-600"><i class="fa-solid fa-heart mr-1"></i><span id="stat-portions">Memuat...</span></span>
            </div>
        </div>
    </div>

    <!-- Active Views Panel (Grid on Desktop, Toggle tabs on Mobile) -->
    <div class="flex-grow grid grid-cols-1 md:grid-cols-5 gap-6">
        
        <!-- Left Side: List Panel (2/5 columns on desktop, hidden on mobile by default) -->
        <div id="panel-list" class="w-full md:col-span-2 md:block md:max-h-[calc(100vh-180px)] md:overflow-y-auto no-scrollbar space-y-4 hidden">
            <!-- Header for active food item count -->
            <div class="flex items-center justify-between px-1">
                <h4 class="font-bold text-xs text-slate-500 uppercase tracking-wide">Makanan Surplus Terdekat</h4>
                <span id="food-count-badge" class="text-[10px] bg-slate-200/70 text-slate-600 font-bold px-2.5 py-0.5 rounded-full">0 Item</span>
            </div>
            
            <!-- Food Card Feed (Injected dynamically via AJAX in app.js) -->
            <div id="food-list-container" class="space-y-3.5 pb-10">
                <!-- Loading indicator -->
                <div class="text-center py-12 text-slate-400">
                    <i class="fa-solid fa-circle-notch fa-spin text-2xl text-emerald-500 mb-2"></i>
                    <p class="text-xs">Mencari makanan surplus di sekitar Anda...</p>
                </div>
            </div>
        </div>

        <!-- Right Side: Map Panel (3/5 columns on desktop, full-width on mobile by default) -->
        <div id="panel-map" class="w-full md:col-span-3 md:block flex-1 flex flex-col h-[calc(100vh-220px)] md:h-[calc(100vh-180px)]">
            <?php include __DIR__ . '/map.php'; ?>
        </div>
        
    </div>
</div>

<!-- Category Filters Popover (Triggered by slider button) -->
<div id="filter-popover" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 hidden opacity-0 transition-opacity duration-300 flex items-end justify-center p-0">
    <div class="bg-white rounded-t-[2.5rem] w-full max-w-md p-6 space-y-4 transform translate-y-full transition-transform duration-300">
        <div class="flex justify-between items-center pb-2 border-b border-slate-100">
            <h4 class="font-bold text-sm text-slate-800">Filter Pencarian</h4>
            <button onclick="toggleFilterModal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        
        <!-- Filter selections -->
        <div class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-2">Harga Maksimal (Rp)</label>
                <div class="flex gap-2">
                    <button onclick="setPriceFilter(15000)" class="price-filter-btn flex-1 py-2 text-xs rounded-xl bg-slate-100 text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 font-medium border border-transparent transition">Under 15rb</button>
                    <button onclick="setPriceFilter(30000)" class="price-filter-btn flex-1 py-2 text-xs rounded-xl bg-slate-100 text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 font-medium border border-transparent transition">Under 30rb</button>
                    <button onclick="setPriceFilter(0)" class="price-filter-btn flex-1 py-2 text-xs rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 font-medium border border-transparent transition" id="price-filter-all">Semua Harga</button>
                </div>
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-2">Status Kadaluwarsa</label>
                <div class="flex gap-2">
                    <button onclick="setExpiryFilter(1)" class="expiry-filter-btn flex-1 py-2 text-xs rounded-xl bg-slate-100 text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 font-medium transition">Urgen (< 1 jam)</button>
                    <button onclick="setExpiryFilter(3)" class="expiry-filter-btn flex-1 py-2 text-xs rounded-xl bg-slate-100 text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 font-medium transition">Segera (< 3 jam)</button>
                    <button onclick="setExpiryFilter(0)" class="expiry-filter-btn flex-1 py-2 text-xs rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 font-medium transition" id="expiry-filter-all">Semua</button>
                </div>
            </div>
        </div>
        
        <button onclick="applyFilters()" class="w-full py-3 bg-emerald-600 text-white rounded-xl text-xs font-semibold shadow-md hover:bg-emerald-700 transition">
            Terapkan Filter
        </button>
    </div>
</div>
