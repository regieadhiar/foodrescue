<?php
// components/map.php
?>
<div class="relative w-full h-[calc(100vh-128px)] rounded-2xl overflow-hidden border border-slate-100 shadow-inner" id="map-section">
    <!-- Map Canvas -->
    <div id="map" class="w-full h-full z-0 bg-slate-100"></div>

    <!-- Floating Map Control Overlays -->
    <div class="absolute bottom-4 right-4 z-10 flex flex-col gap-2">
        <!-- Center to my location button -->
        <button onclick="locateUser()" class="w-12 h-12 bg-white hover:bg-slate-50 text-slate-700 hover:text-emerald-600 rounded-full flex items-center justify-center shadow-lg border border-slate-200 transition active:scale-95 duration-200" title="Cari Lokasi Saya">
            <i class="fa-solid fa-location-crosshairs text-lg"></i>
        </button>
    </div>

    <!-- Map Loading Overlay -->
    <div id="map-loader" class="absolute inset-0 bg-slate-900/10 backdrop-blur-[2px] z-10 flex items-center justify-center pointer-events-none transition-opacity duration-300">
        <div class="bg-white/90 px-4 py-2.5 rounded-full shadow-lg border border-slate-100 text-xs font-semibold flex items-center gap-2">
            <i class="fa-solid fa-circle-notch fa-spin text-emerald-500 text-sm"></i>
            <span>Memuat peta lokasi...</span>
        </div>
    </div>
</div>
