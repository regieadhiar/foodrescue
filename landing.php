<?php
// landing.php - Welcome splash for new visitors
include __DIR__ . '/components/header.php';
?>
<div class="min-h-screen flex flex-col bg-gradient-to-b from-emerald-50 via-white to-white">
    <!-- Hero Section -->
    <section class="flex-1 flex flex-col items-center justify-center px-6 py-16 text-center max-w-3xl mx-auto">
        <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center text-white shadow-xl shadow-emerald-200 mb-6">
            <i class="fa-solid fa-utensils text-3xl"></i>
        </div>
        <h1 class="text-5xl md:text-6xl font-extrabold tracking-tight text-slate-800">
            Food<span class="text-emerald-600">Rescue</span>
        </h1>
        <p class="text-base md:text-lg text-slate-500 mt-4 max-w-lg leading-relaxed">
            Platform <strong>penyelamatan makanan surplus</strong> — 
            hubungkan merchant dengan makanan berlebih ke masyarakat 
            yang membutuhkan dengan harga terjangkau.
        </p>

        <!-- Stats -->
        <div class="grid grid-cols-3 gap-8 mt-10 w-full max-w-sm">
            <div>
                <div class="text-2xl font-black text-emerald-600"><span id="stat-merchants">0</span>+</div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide">Merchant</div>
            </div>
            <div>
                <div class="text-2xl font-black text-emerald-600"><span id="stat-portions">0</span>+</div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide">Porsi Terselamatkan</div>
            </div>
            <div>
                <div class="text-2xl font-black text-emerald-600"><span id="stat-rescuers">0</span>+</div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide">Rescuer Aktif</div>
            </div>
        </div>

        <!-- CTA -->
        <div class="flex flex-col sm:flex-row gap-3 mt-10 w-full max-w-xs">
            <a href="index.php?skip=true" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-emerald-200 hover:shadow-emerald-300 transition-all text-center">
                Mulai Jelajah
            </a>
            <a href="faq.php" class="w-full py-3.5 bg-white border border-slate-200 hover:border-emerald-200 text-slate-600 rounded-xl text-sm font-semibold transition-all text-center">
                Pelajari Dulu
            </a>
        </div>
    </section>

    <!-- How It Works -->
    <section class="px-6 py-12 max-w-4xl mx-auto w-full">
        <h2 class="text-center text-lg font-bold text-slate-700 mb-8">Cara Kerja FoodRescue</h2>
        <div class="grid md:grid-cols-3 gap-6">
            <div class="bg-slate-50 rounded-2xl p-6 text-center border border-slate-100">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-store text-lg"></i>
                </div>
                <h3 class="font-bold text-sm text-slate-700">1. Merchant Upload</h3>
                <p class="text-xs text-slate-400 mt-1">Toko/restaurant upload makanan surplus beserta harga dan lokasi.</p>
            </div>
            <div class="bg-slate-50 rounded-2xl p-6 text-center border border-slate-100">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-map-pin text-lg"></i>
                </div>
                <h3 class="font-bold text-sm text-slate-700">2. Temukan di Peta</h3>
                <p class="text-xs text-slate-400 mt-1">Rescuer lihat makanan tersedia di peta interaktif, filter terdekat.</p>
            </div>
            <div class="bg-slate-50 rounded-2xl p-6 text-center border border-slate-100">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-hand-holding-heart text-lg"></i>
                </div>
                <h3 class="font-bold text-sm text-slate-700">3. Klaim & Ambil</h3>
                <p class="text-xs text-slate-400 mt-1">Pesan, dapatkan QR tiket, ambil langsung ke toko — kurangi food waste!</p>
            </div>
        </div>
    </section>

    <!-- Footer note -->
    <footer class="text-center py-6 text-[10px] text-slate-400 border-t border-slate-100">
        &copy; 2025 FoodRescue — Bersama kurangi sampah makanan
    </footer>
</div>

<script>
document.addEventListener('DOMContentLoaded', async function() {
    try {
        const res = await fetch('api.php?action=get_stats');
        const data = await res.json();
        if (data.success) {
            // Animate counters
            function animate(el, target) {
                if (!el) return;
                let current = 0;
                const step = Math.ceil(target / 60);
                const interval = setInterval(() => {
                    current += step;
                    if (current >= target) {
                        current = target;
                        clearInterval(interval);
                    }
                    el.textContent = current.toLocaleString('id-ID');
                }, 25);
            }
            animate(document.getElementById('stat-merchants'), data.total_merchants || 0);
            animate(document.getElementById('stat-portions'), data.total_portions || 0);
            animate(document.getElementById('stat-rescuers'), data.total_rescuers || 0);
        }
    } catch (e) {
        // fallback: keep 0
    }
});
</script>
