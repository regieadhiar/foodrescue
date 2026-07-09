// assets/js/map.js

// Geocoding search debounce timer
let _geoTimeout = null;

// Global maps instances
window.mainMap = null;
window.mainMapMarkerGroup = null;
window.mainMapMerchantGroup = null;
window.pickerMap = null;
window.pickerMarker = null;

// User coordinates fallback (Garut, Indonesia)
const defaultLat = -7.215373;
const defaultLng = 107.899351;

/**
 * Initialize main map for food rescuers
 */
function initMainMap() {
    const mapElement = document.getElementById('map');
    if (!mapElement || window.mainMap) return;

    window.mainMap = L.map('map', {
        zoomControl: false
    }).setView([defaultLat, defaultLng], 13);

    L.control.zoom({ position: 'topright' }).addTo(window.mainMap);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(window.mainMap);

    window.mainMapMarkerGroup = L.layerGroup().addTo(window.mainMap);
    window.mainMapMerchantGroup = L.layerGroup().addTo(window.mainMap);

    const loader = document.getElementById('map-loader');
    if (loader) {
        loader.classList.add('opacity-0');
        setTimeout(() => {
            if (loader.parentNode) {
                loader.parentNode.removeChild(loader);
            }
        }, 300);
    }

    locateUser();
    
    // Load all active merchant markers on the map
    loadMerchantMarkers();
}

/**
 * Locate user and pan map
 */
function locateUser() {
    if (!window.mainMap) return;

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                // Add a small pulse marker for the user's own location
                const userIcon = L.divIcon({
                    html: `<div class="relative flex h-5 w-5">
                             <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                             <span class="relative inline-flex rounded-full h-5 w-5 bg-blue-500 border-2 border-white shadow"></span>
                           </div>`,
                    className: 'user-loc-icon',
                    iconSize: [20, 20]
                });
                
                // Remove previous user marker if any
                if (window.userLocMarker) {
                    window.mainMap.removeLayer(window.userLocMarker);
                }
                
                window.userLocMarker = L.marker([lat, lng], { icon: userIcon }).addTo(window.mainMap);
                window.mainMap.setView([lat, lng], 15);
            },
            (error) => {
                console.warn('Geolocation failed or denied, using defaults.', error);
                window.mainMap.setView([defaultLat, defaultLng], 13);
            },
            { enableHighAccuracy: true, timeout: 5000 }
        );
    }
}

/**
 * Render food pins onto the main map
 */
function renderMapMarkers(items) {
    if (!window.mainMap || !window.mainMapMarkerGroup) return;

    // Clear previous markers
    window.mainMapMarkerGroup.clearLayers();

    // Group items by merchant to avoid duplicate pins on same spots
    const merchantGroups = {};
    items.forEach(item => {
        const key = `${item.latitude},${item.longitude}`;
        if (!merchantGroups[key]) {
            merchantGroups[key] = {
                business_name: item.business_name,
                address: item.address,
                phone: item.phone,
                latitude: item.latitude,
                longitude: item.longitude,
                foods: []
            };
        }
        merchantGroups[key].foods.push(item);
    });

    // Plot markers
    Object.values(merchantGroups).forEach(group => {
        const foodCount = group.foods.length;
        
        // Custom marker representing store green pin with counter badge
        const pinHtml = `
            <div class="relative w-10 h-10 flex items-center justify-center bg-emerald-600 rounded-full text-white border-2 border-white shadow-lg transition-transform hover:scale-110">
                <i class="fa-solid fa-utensils text-sm"></i>
                ${foodCount >= 1 ? `<span class="absolute -top-1.5 -right-1.5 bg-rose-500 text-white font-extrabold text-[9px] w-5 h-5 rounded-full flex items-center justify-center shadow">${foodCount}</span>` : ''}
            </div>
        `;

        const markerIcon = L.divIcon({
            html: pinHtml,
            className: 'custom-pin-container',
            iconSize: [40, 40],
            iconAnchor: [20, 40]
        });

        const marker = L.marker([group.latitude, group.longitude], { icon: markerIcon })
            .addTo(window.mainMapMarkerGroup);

        // Map marker click listener
        marker.on('click', () => {
            window.mainMap.panTo([group.latitude, group.longitude]);
            openFoodDetailSheet(group);
        });
    });
}

/**
 * Open the custom bottom sheet detailed view
 */
function openFoodDetailSheet(merchantGroup) {
    const sheet = document.getElementById('food-detail-sheet');
    const container = document.getElementById('food-detail-content');
    if (!sheet || !container) return;

    // Render contents inside bottom sheet
    let foodsHtml = '';
    merchantGroup.foods.forEach((food) => {
        const discountPct = Math.round((1 - (food.rescue_price / food.original_price)) * 100);
        
        // Build image gallery from all_images array
        let imageHtml = '';
        const images = food.all_images || (food.image_url ? [food.image_url] : []);
        if (images.length > 1) {
            imageHtml = `<div class="flex gap-1.5 overflow-x-auto no-scrollbar">
                ${images.map(img => `<img src="${img}" class="w-16 h-16 rounded-xl object-cover border border-slate-100 shadow-inner flex-shrink-0" alt="${food.title}">`).join('')}
            </div>`;
        } else {
            const singleImg = images[0] || 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=200&q=80';
            imageHtml = `<img src="${singleImg}" class="w-16 h-16 rounded-xl object-cover border border-slate-100 shadow-inner flex-shrink-0" alt="${food.title}">`;
        }
        
        foodsHtml += `
            <div class="p-4 rounded-2xl border border-slate-100 bg-slate-50/50 space-y-3" data-rescue-price="${food.rescue_price}">
                <div class="flex gap-3.5">
                    ${imageHtml}
                    
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5">
                            <span class="px-2 py-0.5 text-[9px] font-extrabold bg-rose-100 text-rose-700 rounded-full">-${discountPct}% Rescue</span>
                            <span class="text-[9px] text-amber-600 font-bold bg-amber-50 px-2 py-0.5 rounded-full"><i class="fa-solid fa-clock mr-1"></i>${food.minutes_left} min</span>
                        </div>
                        <h4 class="font-bold text-slate-800 text-xs mt-1.5 truncate">${food.title}</h4>
                        <p class="text-[10px] text-slate-400 truncate mt-0.5">${food.description}</p>
                    </div>
                </div>

                <div class="flex justify-between items-center pt-2.5 border-t border-slate-200/40">
                    <div>
                        <span class="block text-[9px] text-slate-400 font-semibold">Harga Rescue</span>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-black text-emerald-600">Rp${formatRupiah(food.rescue_price)}</span>
                            <span class="text-[9px] line-through text-slate-400">Rp${formatRupiah(food.original_price)}</span>
                        </div>
                    </div>
                    
                    <div class="text-right">
                        <span class="block text-[9px] text-slate-400 font-semibold">Tersedia</span>
                        <span class="text-xs font-bold text-slate-700">${food.quantity} porsi</span>
                    </div>
                </div>

                <!-- Claim Form -->
                <form onsubmit="submitClaim(event, ${food.id}, '${merchantGroup.phone.replace(/'/g, "\\'")}', '${merchantGroup.business_name.replace(/'/g, "\\'")}')" class="flex items-center gap-2 pt-1">
                    <div class="flex items-center border border-slate-200 rounded-xl bg-white h-9 px-1.5">
                        <button type="button" onclick="adjustQty(this, -1)" class="w-6 h-6 text-slate-500 font-bold flex items-center justify-center">-</button>
                        <input type="number" name="quantity" value="1" min="1" max="${food.quantity}" readonly class="w-8 text-center text-xs font-bold focus:outline-none border-none">
                        <button type="button" onclick="adjustQty(this, 1, ${food.quantity})" class="w-6 h-6 text-slate-500 font-bold flex items-center justify-center">+</button>
                    </div>
                    <button type="submit" class="flex-1 h-9 bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold rounded-xl shadow-md shadow-emerald-50 transition active:scale-95 duration-200">
                        <i class="fa-solid fa-cart-shopping mr-1"></i> Klaim Makanan
                    </button>
                </form>
            </div>
        `;
    });

    container.innerHTML = `
        <div class="space-y-1">
            <span class="text-[9px] text-slate-400 font-extrabold uppercase tracking-widest block">Merchant Mitra</span>
            <h3 class="font-extrabold text-base text-slate-800">${merchantGroup.business_name}</h3>
            <p class="text-xs text-slate-400 flex items-start gap-1">
                <i class="fa-solid fa-map-pin text-emerald-600 mt-0.5"></i>
                <span>${merchantGroup.address}</span>
            </p>
            <a href="https://wa.me/${merchantGroup.phone}" target="_blank" class="inline-flex items-center gap-1.5 mt-2.5 text-xs font-semibold text-emerald-600 hover:underline">
                <i class="fa-brands fa-whatsapp text-emerald-500"></i> Kontak Toko via WA
            </a>
        </div>
        
        <div class="border-t border-slate-100 pt-3 space-y-3.5">
            <h4 class="font-bold text-xs text-slate-700 uppercase tracking-wide">Makanan Surplus</h4>
            <div class="space-y-3 max-h-[35vh] overflow-y-auto no-scrollbar">
                ${foodsHtml}
            </div>
        </div>
    `;

    // Show bottom sheet with animation
    sheet.classList.remove('hidden');
    setTimeout(() => {
        sheet.classList.remove('translate-y-full');
    }, 50);
}

/**
 * Close bottom sheet detailed view
 */
function closeFoodDetailSheet() {
    const sheet = document.getElementById('food-detail-sheet');
    if (!sheet) return;
    sheet.classList.add('translate-y-full');
    setTimeout(() => {
        sheet.classList.add('hidden');
    }, 300);
}

/**
 * Quantity adjustment helper for claim forms
 */
function adjustQty(btn, delta, max = 99) {
    const input = btn.parentNode.querySelector('input[name="quantity"]');
    if (!input) return;
    let val = parseInt(input.value) + delta;
    if (val < 1) val = 1;
    if (val > max) val = max;
    input.value = val;
}

/**
 * Initialize coordinates picker map for merchant registration
 */
function initMapPicker() {
    const pickerElement = document.getElementById('modal-map-picker');
    if (!pickerElement) return;

    if (window.pickerMap) {
        setTimeout(() => {
            window.pickerMap.invalidateSize();
        }, 300);
        return;
    }

    // Default center
    const center = [defaultLat, defaultLng];

    // Initialize Map inside modal
    window.pickerMap = L.map('modal-map-picker', {
        zoomControl: false
    }).setView(center, 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(window.pickerMap);

    // Draggable Pin Icon
    const pinHtml = `<div class="w-8 h-8 flex items-center justify-center bg-amber-500 rounded-full text-white border-2 border-white shadow-lg"><i class="fa-solid fa-store text-xs"></i></div>`;
    const pickerIcon = L.divIcon({
        html: pinHtml,
        className: 'custom-pin-picker',
        iconSize: [32, 32],
        iconAnchor: [16, 32]
    });

    // Create marker
    window.pickerMarker = L.marker(center, {
        draggable: true,
        icon: pickerIcon
    }).addTo(window.pickerMap);

    // Sync initial coordinates to inputs
    updatePickerInputs(center[0], center[1]);

    // Marker drag end listener
    window.pickerMarker.on('dragend', function (event) {
        const marker = event.target;
        const position = marker.getLatLng();
        updatePickerInputs(position.lat, position.lng);
    });

    // Map click listener
    window.pickerMap.on('click', function (event) {
        const latlng = event.latlng;
        window.pickerMarker.setLatLng(latlng);
        updatePickerInputs(latlng.lat, latlng.lng);
    });

    // Force Leaflet to recalculate container boundaries once modal renders
    setTimeout(() => {
        window.pickerMap.invalidateSize();
        
        // Try to geolocate user to center the registration pin
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition((pos) => {
                const myLat = pos.coords.latitude;
                const myLng = pos.coords.longitude;
                window.pickerMap.setView([myLat, myLng], 15);
                window.pickerMarker.setLatLng([myLat, myLng]);
                updatePickerInputs(myLat, myLng);
            });
        }
    }, 400);
}

/**
 * Update form input fields for registration map coordinates
 */
function updatePickerInputs(lat, lng) {
    const latInput = document.getElementById('reg-lat');
    const lngInput = document.getElementById('reg-longitude');
    if (latInput && lngInput) {
        latInput.value = lat.toFixed(6);
        lngInput.value = lng.toFixed(6);
    }
}

/**
 * Generic map picker with search (Nominatim geocoding) + draggable pin
 * @param {Object} opts
 * @param {string} opts.containerEl - map container element ID
 * @param {string} opts.latInputId - latitude input element ID
 * @param {string} opts.lngInputId - longitude input element ID
 * @param {string} opts.searchInputId - search input element ID (optional)
 * @param {string} opts.searchResultsId - search results container ID (optional)
 * @param {number} opts.lat - initial latitude
 * @param {number} opts.lng - initial longitude
 * @param {string} opts.mapInstanceVar - window variable name to store map instance
 * @param {string} opts.markerVar - window variable name to store marker instance
 */
function initPickerWithSearch(opts) {
    const el = document.getElementById(opts.containerEl);
    if (!el) return;

    // Use existing map instance if already initialized
    const mapVar = opts.mapInstanceVar || 'pickerMap';
    const markerVar = opts.markerVar || 'pickerMarker';
    if (window[mapVar]) {
        setTimeout(() => { window[mapVar].invalidateSize(); }, 300);
        return;
    }

    const centerLat = opts.lat || defaultLat;
    const centerLng = opts.lng || defaultLng;

    // Initialize map
    const map = L.map(opts.containerEl, { zoomControl: false }).setView([centerLat, centerLng], 14);
    L.control.zoom({ position: 'topright' }).addTo(map);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    // Draggable pin
    const pinHtml = `<div class="w-8 h-8 flex items-center justify-center bg-amber-500 rounded-full text-white border-2 border-white shadow-lg"><i class="fa-solid fa-store text-xs"></i></div>`;
    const icon = L.divIcon({ html: pinHtml, className: 'custom-pin-picker', iconSize: [32, 32], iconAnchor: [16, 32] });

    const marker = L.marker([centerLat, centerLng], { draggable: true, icon: icon }).addTo(map);
    window[markerVar] = marker;
    window[mapVar] = map;

    // Sync coordinates
    function syncCoords(lat, lng) {
        const latInp = document.getElementById(opts.latInputId);
        const lngInp = document.getElementById(opts.lngInputId);
        if (latInp && lngInp) {
            latInp.value = lat.toFixed(6);
            lngInp.value = lng.toFixed(6);
        }
    }
    syncCoords(centerLat, centerLng);

    marker.on('dragend', function (e) {
        const pos = e.target.getLatLng();
        syncCoords(pos.lat, pos.lng);
    });
    map.on('click', function (e) {
        marker.setLatLng(e.latlng);
        syncCoords(e.latlng.lat, e.latlng.lng);
    });

    // Search via Nominatim
    const searchInput = document.getElementById(opts.searchInputId);
    const resultsEl = document.getElementById(opts.searchResultsId);
    if (searchInput && resultsEl) {
        let timeout = null;
        searchInput.addEventListener('input', function () {
            clearTimeout(timeout);
            const q = this.value.trim();
            if (q.length < 3) { resultsEl.classList.add('hidden'); return; }
            timeout = setTimeout(() => {
                fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=5&q=${encodeURIComponent(q)}&accept-language=id`)
                    .then(r => r.json())
                    .then(data => {
                        if (!data.length) { resultsEl.classList.add('hidden'); return; }
                        resultsEl.classList.remove('hidden');
                        resultsEl.innerHTML = data.map((place, i) =>
                            `<button type="button" data-idx="${i}" class="w-full text-left px-3 py-2 text-xs hover:bg-emerald-50 border-b border-slate-100 last:border-0 flex items-start gap-2">
                                <i class="fa-solid fa-location-dot text-emerald-500 mt-0.5 text-[10px]"></i>
                                <span>${place.display_name}</span>
                            </button>`
                        ).join('');
                        // Click handler
                        resultsEl.querySelectorAll('button').forEach(btn => {
                            btn.addEventListener('click', function () {
                                const idx = parseInt(this.dataset.idx);
                                const place = data[idx];
                                if (!place) return;
                                const lat = parseFloat(place.lat);
                                const lng = parseFloat(place.lon);
                                map.setView([lat, lng], 16);
                                marker.setLatLng([lat, lng]);
                                syncCoords(lat, lng);
                                resultsEl.classList.add('hidden');
                                searchInput.value = place.display_name;
                            });
                        });
                    })
                    .catch(() => { resultsEl.classList.add('hidden'); });
            }, 400);
        });
        // Hide results on blur
        searchInput.addEventListener('blur', () => {
            setTimeout(() => resultsEl.classList.add('hidden'), 300);
        });
        searchInput.addEventListener('focus', () => {
            if (resultsEl.children.length) resultsEl.classList.remove('hidden');
        });
    }

    // Invalidate size after render
    setTimeout(() => { map.invalidateSize(); }, 400);
}

// Helpers (formatRupiah is defined in app.js)

/**
 * Load all active merchant markers on the map
 */
async function loadMerchantMarkers() {
    if (!window.mainMap || !window.mainMapMerchantGroup) return;
    
    // Only load once
    if (window._merchantMarkersLoaded) return;
    window._merchantMarkersLoaded = true;
    
    try {
        const res = await fetch('api.php?action=get_merchants');
        const data = await res.json();
        
        if (data.success && data.merchants) {
            data.merchants.forEach(m => {
                const pinHtml = `
                    <div class="relative w-10 h-10 flex items-center justify-center bg-slate-500 rounded-full text-white border-2 border-white shadow-lg transition-transform hover:scale-110">
                        <i class="fa-solid fa-store text-sm"></i>
                    </div>
                `;
                
                const markerIcon = L.divIcon({
                    html: pinHtml,
                    className: 'custom-pin-container',
                    iconSize: [40, 40],
                    iconAnchor: [20, 40]
                });
                
                const marker = L.marker([m.latitude, m.longitude], { icon: markerIcon })
                    .addTo(window.mainMapMerchantGroup);
                
                marker.on('click', () => {
                    window.mainMap.panTo([m.latitude, m.longitude]);
                    openMerchantInfoSheet({
                        business_name: m.business_name,
                        address: m.address,
                        phone: m.phone,
                        latitude: m.latitude,
                        longitude: m.longitude
                    });
                });
            });
        }
    } catch (err) {
        console.error('Failed to load merchant markers:', err);
    }
}

/**
 * Open bottom sheet for merchant info (no food items)
 */
function openMerchantInfoSheet(merchantGroup) {
    const sheet = document.getElementById('food-detail-sheet');
    const container = document.getElementById('food-detail-content');
    if (!sheet || !container) return;
    
    container.innerHTML = `
        <div class="space-y-1">
            <span class="text-[9px] text-slate-400 font-extrabold uppercase tracking-widest block">Merchant Mitra</span>
            <h3 class="font-extrabold text-base text-slate-800">${merchantGroup.business_name}</h3>
            <p class="text-xs text-slate-400 flex items-start gap-1">
                <i class="fa-solid fa-map-pin text-emerald-600 mt-0.5"></i>
                <span>${merchantGroup.address}</span>
            </p>
            <a href="https://wa.me/${merchantGroup.phone}" target="_blank" class="inline-flex items-center gap-1.5 mt-2.5 text-xs font-semibold text-emerald-600 hover:underline">
                <i class="fa-brands fa-whatsapp text-emerald-500"></i> Kontak Toko via WA
            </a>
        </div>
        
        <div class="border-t border-slate-100 pt-3">
            <div class="bg-slate-50 p-6 rounded-2xl text-center text-slate-400 space-y-2">
                <i class="fa-solid fa-burger text-2xl text-slate-200"></i>
                <p class="text-xs">Toko ini belum memposting makanan surplus saat ini.</p>
                <p class="text-[10px] text-slate-400">Coba kembali nanti atau hubungi toko untuk info lebih lanjut.</p>
            </div>
        </div>
    `;
    
    // Show bottom sheet with animation
    sheet.classList.remove('hidden');
    setTimeout(() => {
        sheet.classList.remove('translate-y-full');
    }, 50);
}
