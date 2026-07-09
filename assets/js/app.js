// assets/js/app.js

/**
 * Toast notification system
 * @param {string} message - Message to display
 * @param {'success'|'error'|'warning'|'info'} type - Toast type
 * @param {number} duration - Auto-dismiss in ms (default 4000)
 */
function showToast(message, type = 'info', duration = 4000) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const icons = {
        success: '<i class="fa-solid fa-circle-check toast-icon"></i>',
        error: '<i class="fa-solid fa-circle-xmark toast-icon"></i>',
        warning: '<i class="fa-solid fa-triangle-exclamation toast-icon"></i>',
        info: '<i class="fa-solid fa-circle-info toast-icon"></i>'
    };

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.style.position = 'relative';
    toast.innerHTML = `
        ${icons[type] || icons.info}
        <span>${message}</span>
        <button class="toast-close" onclick="this.parentElement.remove()" aria-label="Close">
            <i class="fa-solid fa-xmark text-sm"></i>
        </button>
        <div class="toast-progress" style="width:100%;transition-duration:${duration}ms"></div>
    `;

    container.appendChild(toast);

    // Trigger enter animation
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            toast.classList.add('show');
            const progress = toast.querySelector('.toast-progress');
            if (progress) progress.style.width = '0%';
        });
    });

    // Auto-dismiss
    setTimeout(() => {
        toast.classList.remove('show');
        toast.classList.add('hide');
        setTimeout(() => toast.remove(), 400);
    }, duration);
}

/**
 * Format number as Indonesian Rupiah
 */
function formatRupiah(num) {
    return parseFloat(num).toLocaleString('id-ID');
}

/**
 * Get CSRF token from meta tag
 */
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
}

window.allFoodItems = [];
window.activeFilters = {
    search: '',
    maxPrice: 0,
    maxExpiryHours: 0
};

// Start application when DOM loads
document.addEventListener('DOMContentLoaded', () => {
    // 1. Initialize user profile dropdown toggles
    initUserMenu();
    
    // 2. Determine active page view and run initial loads
    const currentView = document.body.dataset.view || 'rescuer';
    if (currentView === 'rescuer') {
        initMainMap();
        loadInitialData();
    }
    
    // 3. Check for reset token in URL hash
    checkResetTokenFromURL();
    
    // 3. Initialize merchant QR scanner if on merchant dashboard
    if (currentView === 'merchant') {
        initMerchantQRScanner();
    }
});

/**
 * Handle user dropdown menu toggle
 */
function initUserMenu() {
    const btn = document.getElementById('user-menu-btn');
    const menu = document.getElementById('user-dropdown');
    
    if (btn && menu) {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            menu.classList.toggle('hidden');
        });
        
        // Close menu if clicking anywhere else
        document.addEventListener('click', () => {
            menu.classList.add('hidden');
        });
    }
}

function toggleMobileNav() {
    const overlay = document.getElementById('mobile-nav-overlay');
    if (!overlay) return;
    const panel = document.getElementById('mobile-nav-panel');
    if (overlay.classList.contains('hidden')) {
        overlay.classList.remove('hidden');
        setTimeout(() => {
            panel.style.transform = 'translateX(0)';
        }, 50);
    } else {
        panel.style.transform = 'translateX(100%)';
        setTimeout(() => {
            overlay.classList.add('hidden');
        }, 300);
    }
}

/**
 * Modal Management System
 */
function handleLoginClick() {
    // If modals are available (index.php), open login modal directly
    if (document.getElementById('modal-backdrop')) {
        openModal('login-modal');
        return;
    }
    // On landing page, redirect to index
    window.location.href = 'index.php';
}

function openModal(modalId) {
    const backdrop = document.getElementById('modal-backdrop');
    if (!backdrop) return;
    
    // Hide all modal contents first
    backdrop.querySelectorAll('.modal-content').forEach(mc => {
        mc.classList.add('hidden');
        mc.classList.add('scale-95');
        mc.classList.remove('scale-100');
    });
    
    // Show target modal
    const target = document.getElementById(modalId);
    if (target) {
        backdrop.classList.remove('hidden');
        setTimeout(() => {
            backdrop.classList.remove('opacity-0');
            backdrop.classList.add('opacity-100');
            target.classList.remove('hidden');
            target.classList.remove('scale-95');
            target.classList.add('scale-100');
        }, 10);
        
        // Initialize map picker if merchant reg modal
        if (modalId === 'merchant-reg-modal') {
            setTimeout(() => {
                initPickerWithSearch({
                    containerEl: 'modal-map-picker',
                    latInputId: 'reg-lat',
                    lngInputId: 'reg-longitude',
                    searchInputId: 'reg-map-search',
                    searchResultsId: 'reg-map-results',
                    mapInstanceVar: 'pickerMap',
                    markerVar: 'pickerMarker'
                });
            }, 200);
        }
        
        // Hide login error when opening login modal
        if (modalId === 'login-modal') {
            const loginErr = document.getElementById('login-error-msg');
            if (loginErr) loginErr.classList.add('hidden');
        }
    }
}

function closeActiveModal() {
    const backdrop = document.getElementById('modal-backdrop');
    if (!backdrop) return;
    
    backdrop.classList.add('opacity-0');
    backdrop.classList.remove('opacity-100');
    
    setTimeout(() => {
        backdrop.classList.add('hidden');
        backdrop.querySelectorAll('.modal-content').forEach(mc => {
            mc.classList.add('hidden');
            mc.classList.add('scale-95');
            mc.classList.remove('scale-100');
        });
    }, 300);
}

function switchModal(modalId) {
    closeActiveModal();
    setTimeout(() => openModal(modalId), 350);
}

/**
 * Switch navigation tabs on mobile (Map vs Food List view)
 */
function switchMobileTab(tab) {
    const mapBtn = document.getElementById('nav-map-btn');
    const listBtn = document.getElementById('nav-list-btn');
    const mapPanel = document.getElementById('panel-map');
    const listPanel = document.getElementById('panel-list');
    
    if (!mapBtn || !listBtn) return;
    
    if (tab === 'map') {
        // Active Map View
        mapBtn.className = "flex flex-col items-center justify-center flex-1 text-emerald-600 font-semibold transition";
        listBtn.className = "flex flex-col items-center justify-center flex-1 text-slate-400 hover:text-slate-600 transition";
        
        mapPanel.classList.remove('hidden');
        listPanel.classList.add('hidden');
        
        // Recalculate leaflet map boundary sizes
        if (window.mainMap) {
            setTimeout(() => {
                window.mainMap.invalidateSize();
            }, 50);
        }
    } else {
        // Active List View
        listBtn.className = "flex flex-col items-center justify-center flex-1 text-emerald-600 font-semibold transition";
        mapBtn.className = "flex flex-col items-center justify-center flex-1 text-slate-400 hover:text-slate-600 transition";
        
        listPanel.classList.remove('hidden');
        mapPanel.classList.add('hidden');
    }
}

/**
 * Load all initial data (food items + merchants + stats) in one request
 */
async function loadInitialData() {
    try {
        const res = await fetch('api.php?action=get_initial_data');
        const data = await res.json();
        
        if (data.success) {
            // Food items
            window.allFoodItems = data.items || [];
            updateFoodDisplay();
            
            // Stats
            const co2El = document.getElementById('stat-co2');
            const portionsEl = document.getElementById('stat-portions');
            if (co2El) co2El.textContent = `${data.co2_saved}kg Emisi CO₂ Dihindari`;
            if (portionsEl) portionsEl.textContent = `${data.total_portions} Makanan`;
        } else {
            window.allFoodItems = [];
            updateFoodDisplay();
        }
    } catch (err) {
        console.error('Failed to load initial data:', err);
        window.allFoodItems = [];
        updateFoodDisplay();
    }
}

/**
 * Reload food items only (after claim)
 */
async function loadFoodItems() {
    try {
        const res = await fetch('api.php?action=get_food_items');
        const data = await res.json();
        
        if (data.success && data.items) {
            window.allFoodItems = data.items;
            updateFoodDisplay();
        } else {
            window.allFoodItems = [];
            updateFoodDisplay();
        }
    } catch (err) {
        console.error('Failed to load food items:', err);
        window.allFoodItems = [];
        updateFoodDisplay();
    }
}

/**
 * Apply filters and re-render food list + map markers
 */
function updateFoodDisplay() {
    let filtered = [...window.allFoodItems];
    
    // Search filter
    if (window.activeFilters.search) {
        const q = window.activeFilters.search.toLowerCase();
        filtered = filtered.filter(item =>
            item.title.toLowerCase().includes(q) ||
            item.business_name.toLowerCase().includes(q) ||
            item.description.toLowerCase().includes(q)
        );
    }
    
    // Price filter
    if (window.activeFilters.maxPrice > 0) {
        filtered = filtered.filter(item => parseFloat(item.rescue_price) <= window.activeFilters.maxPrice);
    }
    
    // Expiry filter (hours)
    if (window.activeFilters.maxExpiryHours > 0) {
        filtered = filtered.filter(item => item.minutes_left <= window.activeFilters.maxExpiryHours * 60);
    }
    
    // Update count badge
    const badge = document.getElementById('food-count-badge');
    if (badge) {
        badge.textContent = `${filtered.length} Item`;
    }
    
    // Render list feed
    renderListFeed(filtered);
    
    // Render map markers
    renderMapMarkers(filtered);
}

/**
 * Search input filter handler
 */
function filterFoodList() {
    const input = document.getElementById('food-search-input');
    if (input) {
        window.activeFilters.search = input.value.trim();
    }
    updateFoodDisplay();
}

/**
 * Toggle filter popover modal
 */
function toggleFilterModal() {
    const popover = document.getElementById('filter-popover');
    if (!popover) return;
    
    if (popover.classList.contains('hidden')) {
        popover.classList.remove('hidden');
        setTimeout(() => {
            popover.classList.remove('opacity-0');
            popover.classList.add('opacity-100');
            const inner = popover.querySelector('div');
            if (inner) {
                inner.classList.remove('translate-y-full');
            }
        }, 10);
    } else {
        popover.classList.add('opacity-0');
        popover.classList.remove('opacity-100');
        const inner = popover.querySelector('div');
        if (inner) {
            inner.classList.add('translate-y-full');
        }
        setTimeout(() => {
            popover.classList.add('hidden');
        }, 300);
    }
}

/**
 * Filter Management Rules
 */
function setPriceFilter(maxPrice) {
    window.activeFilters.maxPrice = maxPrice;
    
    // Highlight active state UI
    document.querySelectorAll('.price-filter-btn').forEach(btn => {
        btn.className = "price-filter-btn flex-1 py-2 text-xs rounded-xl bg-slate-100 text-slate-600 font-medium border border-transparent transition";
    });
    // Find the clicked button by matching value
    document.querySelectorAll('.price-filter-btn').forEach(btn => {
        const btnVal = parseInt(btn.getAttribute('onclick').match(/\d+/)?.[0] || '0');
        if (btnVal === maxPrice) {
            btn.className = "price-filter-btn flex-1 py-2 text-xs rounded-xl bg-emerald-600 text-white font-medium border border-transparent transition";
        }
    });
}

function setExpiryFilter(maxHours) {
    window.activeFilters.maxExpiryHours = maxHours;
    
    // Highlight active state UI
    document.querySelectorAll('.expiry-filter-btn').forEach(btn => {
        btn.className = "expiry-filter-btn flex-1 py-2 text-xs rounded-xl bg-slate-100 text-slate-600 font-medium transition";
    });
    document.querySelectorAll('.expiry-filter-btn').forEach(btn => {
        const btnVal = parseInt(btn.getAttribute('onclick').match(/\d+/)?.[0] || '0');
        if (btnVal === maxHours) {
            btn.className = "expiry-filter-btn flex-1 py-2 text-xs rounded-xl bg-emerald-600 text-white font-medium transition";
        }
    });
}

function applyFilters() {
    updateFoodDisplay();
    toggleFilterModal();
}

/**
 * Render list feed cards HTML in list dashboard panel
 */
function renderListFeed(items) {
    const container = document.getElementById('food-list-container');
    if (!container) return;
    
    if (items.length === 0) {
        container.innerHTML = `
            <div class="bg-white rounded-3xl p-8 border border-slate-100 text-center text-slate-400 space-y-2">
                <i class="fa-solid fa-face-frown text-3xl text-slate-200 animate-bounce"></i>
                <p class="text-xs">Maaf, makanan surplus yang Anda cari tidak ditemukan.</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    items.forEach((item, idx) => {
        const discountPct = Math.round((1 - (item.rescue_price / item.original_price)) * 100);
        
        html += `
            <div data-food-idx="${idx}" onclick="handleFoodCardClick(this)" class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm flex gap-3.5 hover:border-emerald-500/30 transition duration-200 cursor-pointer active:scale-[0.98]">
                <img src="${item.image_url || 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=200&q=80'}" class="w-18 h-18 rounded-xl object-cover border border-slate-100 shadow-inner flex-shrink-0" alt="${item.title}">
                
                <div class="flex-grow min-w-0">
                    <div class="flex items-center gap-1.5">
                        <span class="px-2 py-0.5 text-[9px] font-extrabold bg-rose-100 text-rose-700 rounded-full">-${discountPct}% Rescue</span>
                        <span class="text-[9px] text-amber-600 font-bold bg-amber-50 px-2 py-0.5 rounded-full"><i class="fa-solid fa-clock mr-1"></i>${item.minutes_left} min</span>
                    </div>
                    
                    <h4 class="font-bold text-slate-800 text-xs mt-1 truncate">${item.title}</h4>
                    <span class="text-[9px] text-slate-400 block -mt-0.5 truncate"><i class="fa-solid fa-shop text-[8px] mr-1 text-slate-300"></i>${item.business_name}</span>
                    
                    <div class="flex justify-between items-center mt-1.5">
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-black text-emerald-600">Rp${formatRupiah(item.rescue_price)}</span>
                            <span class="text-[9px] line-through text-slate-400">Rp${formatRupiah(item.original_price)}</span>
                        </div>
                        <span class="text-[9px] text-slate-400 font-medium">Stok: <strong>${item.quantity}</strong></span>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

/**
 * Handle food card click via data attribute (XSS-safe alternative to inline JSON in onclick)
 */
function handleFoodCardClick(el) {
    const idx = parseInt(el.dataset.foodIdx);
    if (isNaN(idx) || !window.allFoodItems[idx]) return;
    const item = window.allFoodItems[idx];
    panToFoodItem(item.latitude, item.longitude, item);
}

/**
 * Clicking a list food card triggers centering and bottom sheet mapping
 */
function panToFoodItem(lat, lng, itemData) {
    switchMobileTab('map');
    
    if (window.mainMap) {
        window.mainMap.setView([lat, lng], 16);
        
        // Package format to match merchantGroups
        const packagedGroup = {
            business_name: itemData.business_name,
            address: itemData.address,
            phone: itemData.phone,
            latitude: lat,
            longitude: lng,
            foods: [itemData]
        };
        
        setTimeout(() => {
            openFoodDetailSheet(packagedGroup);
        }, 150);
    }
}

/**
 * Forms Submissions Operations
 */

async function submitLogin(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    
    try {
        const res = await fetch('api.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            // Show error inside login modal if visible
            const loginErr = document.getElementById('login-error-msg');
            if (loginErr) {
                loginErr.querySelector('span').textContent = data.message;
                loginErr.classList.remove('hidden');
            } else {
                showToast(data.message, 'error');
            }
        }
    } catch (err) {
        showToast('Terjadi kesalahan saat menghubungi server.', 'error');
    }
}

async function submitRegister(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    
    try {
        const res = await fetch('api.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showToast(data.message, 'error');
        }
    } catch (err) {
        showToast('Terjadi kesalahan saat menghubungi server.', 'error');
    }
}

async function submitMerchantReg(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    
    const lat = formData.get('latitude');
    const lng = formData.get('longitude');
    
    if (!lat || !lng || parseFloat(lat) === 0 || parseFloat(lng) === 0) {
        showToast('Harap tentukan lokasi koordinat toko Anda pada peta.', 'warning');
        return;
    }
    
    try {
        const res = await fetch('api.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            // Redirect to merchant dashboard view
            setTimeout(() => window.location.href = 'index.php?switch_view=merchant', 1500);
        } else {
            showToast(data.message, 'error');
        }
    } catch (err) {
        showToast('Gagal mendaftarkan merchant. Silakan cek koneksi Anda.', 'error');
    }
}

async function submitAddFood(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    
    try {
        const res = await fetch('api.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showToast(data.message, 'error');
        }
    } catch (err) {
        showToast('Gagal menambahkan makanan surplus.', 'error');
    }
}

async function submitClaim(e, foodId, phone, businessName) {
    e.preventDefault();
    const form = e.target;
    const qty = form.querySelector('input[name="quantity"]').value;
    const rescuePrice = form.closest('[data-rescue-price]')?.dataset.rescuePrice || '0';
    
    // Show payment method selection modal instead of direct claim
    showPaymentMethodModal(foodId, qty, phone, businessName, rescuePrice);
}

/**
 * Show payment method selection before claiming
 */
function showPaymentMethodModal(foodId, qty, phone, businessName, rescuePrice) {
    const totalPrice = parseFloat(rescuePrice) * parseInt(qty);
    
    // Create or reuse payment selection modal
    let modal = document.getElementById('payment-select-modal');
    if (!modal) {
        // Create the modal dynamically
        const backdrop = document.getElementById('modal-backdrop');
        modal = document.createElement('div');
        modal.id = 'payment-select-modal';
        modal.className = 'modal-content hidden bg-white w-full max-w-sm rounded-3xl shadow-xl overflow-hidden transform scale-95 transition-transform duration-300';
        modal.innerHTML = `
            <div class="relative px-6 py-8">
                <button onclick="closeActiveModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 mb-3">
                        <i class="fa-solid fa-wallet text-xl"></i>
                    </div>
                    <h3 class="font-bold text-lg text-slate-800">Metode Pembayaran</h3>
                    <p class="text-xs text-slate-400 mt-1">Pilih cara pembayaran untuk makanan surplus ini</p>
                </div>
                
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 mb-5 text-center">
                    <span class="text-[10px] text-slate-400 font-bold uppercase block">Total Pembayaran</span>
                    <span class="text-xl font-black text-emerald-600 block mt-1" id="pm-total-price">Rp0</span>
                    <span class="text-[10px] text-slate-500 mt-0.5 block" id="pm-detail-info">0 porsi</span>
                </div>
                
                <div class="space-y-3" id="pm-buttons-container">
                    <button id="pm-btn-cash" class="w-full py-3.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-semibold shadow-md transition duration-200 flex items-center justify-center gap-2.5 active:scale-95">
                        <i class="fa-solid fa-money-bill-wave text-emerald-400"></i>
                        Bayar Tunai (Cash) di Toko
                    </button>
                    <button id="pm-btn-qris" class="w-full py-3.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl text-sm font-semibold shadow-md transition duration-200 flex items-center justify-center gap-2.5 active:scale-95">
                        <i class="fa-solid fa-qrcode"></i>
                        Bayar via QRIS
                    </button>
                </div>
                
                <p class="text-[9px] text-slate-400 text-center mt-4">Pembayaran akan dikonfirmasi saat Anda mengambil makanan di toko</p>
            </div>
        `;
        backdrop.appendChild(modal);
    }
    
    // Update modal content
    document.getElementById('pm-total-price').textContent = `Rp${formatRupiah(totalPrice)}`;
    document.getElementById('pm-detail-info').textContent = `${qty} porsi di ${businessName}`;
    
    // Attach handlers
    document.getElementById('pm-btn-cash').onclick = () => executeClaim(foodId, qty, phone, businessName, 'cash');
    document.getElementById('pm-btn-qris').onclick = () => {
        closeActiveModal();
        // Show QRIS payment modal first
        showQrisPaymentModal(totalPrice, businessName, () => {
            executeClaim(foodId, qty, phone, businessName, 'qris');
        });
    };
    
    // Close bottom sheet and open payment modal
    closeFoodDetailSheet();
    setTimeout(() => {
        openModal('payment-select-modal');
    }, 350);
}

/**
 * Show QRIS payment modal with QR code
 */
function showQrisPaymentModal(amount, merchantName, onConfirm) {
    // Update QRIS modal content
    const amountEl = document.getElementById('qris-payment-amount');
    const merchantEl = document.getElementById('qris-payment-merchant');
    if (amountEl) amountEl.textContent = `Rp${formatRupiah(amount)}`;
    if (merchantEl) merchantEl.textContent = merchantName;
    
    // Store confirm callback
    window._qrisConfirmCallback = onConfirm;
    
    openModal('qris-payment-modal');
}

function confirmQrisPayment() {
    closeActiveModal();
    if (typeof window._qrisConfirmCallback === 'function') {
        setTimeout(() => window._qrisConfirmCallback(), 350);
    }
}

/**
 * Execute the claim API call with selected payment method
 */
async function executeClaim(foodId, qty, phone, businessName, paymentMethod) {
    closeActiveModal();
    
    const payload = {
        action: 'claim_food_item',
        food_item_id: foodId,
        quantity: qty,
        merchant_phone: phone,
        business_name: businessName,
        payment_method: paymentMethod,
        csrf_token: getCsrfToken()
    };
    
    try {
        const res = await fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        
        if (data.success) {
            // Show QR ticket modal with order details
            showQRTicket(data.order_id, businessName, qty, paymentMethod);
        } else {
            showToast(data.message, 'error');
            // If not logged in, trigger login popup
            if (data.message.includes('login')) {
                openModal('login-modal');
            }
        }
    } catch (err) {
        showToast('Gagal mengklaim pesanan.', 'error');
    }
}

/**
 * Show QR Ticket after successful claim
 */
function showQRTicket(orderId, businessName, quantity, paymentMethod) {
    const qrData = `foodrescue-order:${orderId}`;
    const qrImageUrl = `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(qrData)}&bgcolor=ffffff&color=0f172a&margin=10`;
    
    // Update QR ticket modal content
    document.getElementById('qr-ticket-image').src = qrImageUrl;
    document.getElementById('qr-ticket-store').textContent = businessName;
    document.getElementById('qr-ticket-qty').textContent = `Jumlah: ${quantity} Porsi`;
    document.getElementById('qr-ticket-order-id').textContent = `Order ID: #${orderId}`;
    
    const paymentLabel = paymentMethod === 'qris' ? 'QRIS (Elektronik)' : 'Cash (Tunai)';
    document.getElementById('qr-ticket-payment').textContent = `Metode: ${paymentLabel}`;
    
    openModal('qr-ticket-modal');
    
    // Reload food items to update stock
    setTimeout(() => loadFoodItems(), 500);
}

/**
 * Handle order QR button click via data attributes (XSS-safe)
 */
function handleOrderQRClick(el) {
    const orderId = parseInt(el.dataset.orderId);
    const businessName = decodeURIComponent(el.dataset.biz);
    const quantity = parseInt(el.dataset.qty);
    const paymentMethod = el.dataset.pm;
    showOrderQR(orderId, businessName, quantity, paymentMethod);
}

/**
 * Show QR code for an existing order (from order history)
 */
function showOrderQR(orderId, businessName, quantity, paymentMethod) {
    showQRTicket(orderId, businessName, quantity, paymentMethod);
}

async function handleLogout() {
    // Show confirmation modal
    const modal = document.getElementById('modal-backdrop');
    const logoutModal = document.getElementById('logout-confirm-modal');
    if (modal && logoutModal) {
        modal.classList.remove('hidden');
        logoutModal.classList.remove('hidden');
        requestAnimationFrame(() => {
            modal.style.opacity = '1';
            logoutModal.style.transform = 'scale(1)';
        });
    }
}

async function confirmLogout() {
    const modal = document.getElementById('modal-backdrop');
    const logoutModal = document.getElementById('logout-confirm-modal');
    if (modal && logoutModal) {
        modal.style.opacity = '0';
        logoutModal.style.transform = 'scale(0.95)';
        setTimeout(() => {
            modal.classList.add('hidden');
            logoutModal.classList.add('hidden');
        }, 300);
    }
    
    try {
        const res = await fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'logout', csrf_token: getCsrfToken() })
        });
        const data = await res.json();
        if (data.success) {
            showToast('Berhasil keluar. Sampai jumpa!', 'success');
            setTimeout(() => window.location.href = 'index.php', 1200);
        }
    } catch (err) {
        showToast('Gagal keluar dari akun.', 'error');
    }
}

/**
 * Open Claims history for rescuers
 */
async function openOrdersHistory() {
    openModal('orders-history-modal');
    
    const container = document.getElementById('orders-list-container');
    if (!container) return;
    
    container.innerHTML = `
        <div class="text-center py-8 text-slate-400 text-xs">
            <i class="fa-solid fa-spinner fa-spin text-xl text-emerald-500 mb-2 block"></i>
            Memuat riwayat klaim...
        </div>
    `;
    
    try {
        const res = await fetch('api.php?action=get_rescuer_orders');
        const data = await res.json();
        
        if (data.success) {
            const orders = data.orders || [];
            if (orders.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-12 text-slate-400 space-y-2 bg-slate-50 rounded-2xl border border-slate-100 p-6">
                        <i class="fa-solid fa-receipt text-3xl text-slate-200"></i>
                        <p class="text-xs font-medium">Anda belum pernah mengklaim makanan surplus.</p>
                        <p class="text-[10px] text-slate-400">Peta lokasi memiliki banyak makanan lezat yang menunggu diselamatkan!</p>
                    </div>
                `;
                return;
            }
            
            let html = '';
            orders.forEach(order => {
                const totalCost = parseFloat(order.rescue_price) * parseInt(order.quantity);
                const orderDate = new Date(order.created_at).toLocaleDateString('id-ID', {
                    day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit'
                });
                
                let statusBadge = '';
                if (order.status === 'completed') {
                    statusBadge = `<span class="px-2.5 py-0.5 text-[9px] font-bold rounded-full bg-emerald-50 text-emerald-700 uppercase">SELESAI</span>`;
                } else if (order.status === 'claimed') {
                    statusBadge = `<span class="px-2.5 py-0.5 text-[9px] font-bold rounded-full bg-blue-50 text-blue-700 uppercase">MENUNGGU AMBIL</span>`;
                } else if (order.status === 'cancelled') {
                    statusBadge = `<span class="px-2.5 py-0.5 text-[9px] font-bold rounded-full bg-red-50 text-red-600 uppercase">BATAL</span>`;
                } else {
                    statusBadge = `<span class="px-2.5 py-0.5 text-[9px] font-bold rounded-full bg-slate-100 text-slate-500 uppercase">${order.status}</span>`;
                }
                
                const paymentBadge = order.payment_method === 'qris' 
                    ? `<span class="px-2 py-0.5 text-[9px] font-bold rounded-full bg-indigo-50 text-indigo-600"><i class="fa-solid fa-qrcode mr-0.5"></i>QRIS</span>`
                    : `<span class="px-2 py-0.5 text-[9px] font-bold rounded-full bg-amber-50 text-amber-600"><i class="fa-solid fa-money-bill mr-0.5"></i>CASH</span>`;

                // Parse image URL
                let displayImg = 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=100&q=80';
                if (order.image_url) {
                    try {
                        const imgs = JSON.parse(order.image_url);
                        if (Array.isArray(imgs) && imgs.length > 0) displayImg = imgs[0];
                    } catch(e) {
                        if (order.image_url.length > 0) displayImg = order.image_url;
                    }
                }

                // WhatsApp text
                const waMsg = `Halo ${order.business_name}, saya ingin mengambil klaim pesanan FoodRescue saya: ${order.title} (${order.quantity} porsi).`;
                const waUrl = `https://wa.me/${order.phone}?text=${encodeURIComponent(waMsg)}`;

                html += `
                    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm space-y-3.5 hover:border-emerald-500/10 transition">
                        <div class="flex justify-between items-start gap-2">
                            <div>
                                <span class="text-[10px] font-extrabold text-emerald-600 block uppercase">${order.business_name}</span>
                                <h4 class="font-bold text-slate-700 text-xs mt-0.5">${order.title}</h4>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                ${statusBadge}
                                ${paymentBadge}
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-3 bg-slate-50/50 p-2.5 rounded-xl border border-slate-100/50">
                            <img src="${displayImg}" class="w-11 h-11 object-cover rounded-lg border border-slate-200/50 flex-shrink-0">
                            <div class="text-[10px] text-slate-500 space-y-0.5">
                                <div>Jumlah: <strong>${order.quantity} porsi</strong></div>
                                <div>Total Bayar: <strong class="text-slate-800">Rp${formatRupiah(totalCost)}</strong></div>
                                <div>Alamat Toko: <span class="truncate block max-w-[180px] text-slate-400">${order.address}</span></div>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center text-[10px] pt-1.5 border-t border-slate-100/60">
                            <span class="text-slate-400 font-medium">${orderDate}</span>
                            <div class="flex gap-1.5">
                                ${order.status === 'claimed' ? `
                                    <button data-order-id="${order.id}" data-biz="${encodeURIComponent(order.business_name)}" data-qty="${order.quantity}" data-pm="${order.payment_method}" onclick="handleOrderQRClick(this)" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-[9px] flex items-center gap-1 transition active:scale-95 shadow-sm">
                                        <i class="fa-solid fa-qrcode"></i> Tiket QR
                                    </button>
                                ` : ''}
                                <a href="${waUrl}" target="_blank" class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100/80 border border-emerald-100 text-emerald-700 rounded-lg font-bold text-[9px] flex items-center gap-1 transition">
                                    <i class="fa-brands fa-whatsapp text-emerald-500"></i> WA
                                </a>
                                <a href="https://maps.google.com/?q=${encodeURIComponent(order.address)}" target="_blank" class="px-3 py-1.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-lg font-bold text-slate-600 text-[9px] flex items-center gap-1 transition">
                                    <i class="fa-solid fa-map"></i> Rute
                                </a>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        } else {
            container.innerHTML = `
                <div class="text-center py-10 text-red-500 text-xs">
                    <i class="fa-solid fa-triangle-exclamation text-lg mb-1 block"></i>
                    ${data.message}
                </div>
            `;
        }
    } catch (err) {
        container.innerHTML = `
            <div class="text-center py-10 text-red-500 text-xs">
                <i class="fa-solid fa-triangle-exclamation text-lg mb-1 block"></i>
                Terjadi kesalahan koneksi server.
            </div>
        `;
    }
}

/**
 * Image upload validation
 */
function validateImageUploadCount(input) {
    const preview = document.getElementById('image-upload-preview');
    if (!preview) return;
    
    const files = input.files;
    if (files.length === 0) {
        preview.classList.add('hidden');
        preview.innerHTML = '';
        return;
    }
    
    if (files.length > 3) {
        showToast('Maksimal 3 foto makanan. Silakan pilih ulang.', 'warning');
        input.value = '';
        preview.classList.add('hidden');
        preview.innerHTML = '';
        return;
    }
    
    // Show preview thumbnails
    preview.classList.remove('hidden');
    preview.innerHTML = '';
    
    Array.from(files).forEach(file => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'w-full h-20 object-cover rounded-lg border border-slate-200';
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
}

/**
 * Merchant Dashboard Section Switcher
 */
function switchMerchantSection(section) {
    const isDesktop = window.innerWidth >= 768; // md breakpoint
    
    // Hide left-panel sections (list & claims only)
    document.querySelectorAll('.merchant-section-left').forEach(sec => {
        sec.classList.add('hidden');
    });
    
    // On mobile, also hide post form when switching away from it
    if (!isDesktop) {
        document.getElementById('m-sect-post')?.classList.add('hidden');
    }
    
    // Reset tab button styles (preserve md:hidden on post button for desktop)
    document.querySelectorAll('.m-tab-btn').forEach(btn => {
        const isPostBtn = btn.id === 'm-btn-post';
        btn.className = 'm-tab-btn flex-1 py-2 text-xs font-semibold rounded-lg text-slate-500 hover:text-slate-700 transition' + (isPostBtn ? ' md:hidden' : '');
    });
    
    // Show target section and activate tab
    if (section === 'list') {
        document.getElementById('m-sect-list')?.classList.remove('hidden');
        document.getElementById('m-btn-list')?.classList.add('bg-white', 'text-slate-700', 'shadow-sm');
    } else if (section === 'post') {
        // On mobile, show post form as full section; on desktop it's always visible as sidebar
        if (!isDesktop) {
            document.getElementById('m-sect-post')?.classList.remove('hidden');
        }
        document.getElementById('m-btn-post')?.classList.add('bg-white', 'text-slate-700', 'shadow-sm');
    } else if (section === 'claims') {
        document.getElementById('m-sect-claims')?.classList.remove('hidden');
        document.getElementById('m-btn-claims')?.classList.add('bg-white', 'text-slate-700', 'shadow-sm');
    }
}

/**
 * Merchant QR Scanner for verifying customer tickets
 */
function initMerchantQRScanner() {
    // Check if scanner button already exists
    if (document.getElementById('merchant-qr-scanner-btn')) return;
    
    // Add scanner button to merchant dashboard header
    const header = document.querySelector('.bg-slate-900.text-white.rounded-3xl');
    if (!header) return;
    
    const scannerBtn = document.createElement('button');
    scannerBtn.id = 'merchant-qr-scanner-btn';
    scannerBtn.onclick = openMerchantQRScanner;
    scannerBtn.className = 'mt-3 inline-flex items-center gap-2 py-2 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md transition active:scale-95';
    scannerBtn.innerHTML = '<i class="fa-solid fa-camera"></i> Scan Tiket QR Pembeli';
    
    const titleDiv = header.querySelector('.relative.z-10');
    if (titleDiv) {
        titleDiv.appendChild(scannerBtn);
    }
}

function openMerchantQRScanner() {
    // Create scanner modal dynamically
    let modal = document.getElementById('merchant-qr-scanner-modal');
    if (!modal) {
        const backdrop = document.getElementById('modal-backdrop');
        modal = document.createElement('div');
        modal.id = 'merchant-qr-scanner-modal';
        modal.className = 'modal-content hidden bg-white w-full max-w-sm rounded-3xl shadow-xl overflow-hidden transform scale-95 transition-transform duration-300';
        modal.innerHTML = `
            <div class="relative px-6 py-8">
                <button onclick="closeActiveModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 mb-3">
                        <i class="fa-solid fa-camera text-xl"></i>
                    </div>
                    <h3 class="font-bold text-lg text-slate-800">Verifikasi Tiket Pembeli</h3>
                    <p class="text-xs text-slate-400 mt-1">Masukkan kode QR dari tiket pembeli untuk memverifikasi pengambilan</p>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Kode QR / Order ID</label>
                        <input type="text" id="scanner-qr-input" placeholder="Contoh: foodrescue-order:5 atau cukup angka 5" class="w-full px-4 py-3 text-sm rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition font-mono">
                    </div>
                    
                    <button onclick="submitQRVerification()" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold shadow-md shadow-emerald-100 hover:shadow-emerald-200 transition duration-200 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-check-circle"></i> Verifikasi & Selesaikan Pesanan
                    </button>
                    
                    <div id="qr-verify-result" class="hidden"></div>
                </div>
            </div>
        `;
        backdrop.appendChild(modal);
    }
    
    // Clear previous result
    const resultDiv = document.getElementById('qr-verify-result');
    if (resultDiv) {
        resultDiv.classList.add('hidden');
        resultDiv.innerHTML = '';
    }
    
    // Clear input
    const input = document.getElementById('scanner-qr-input');
    if (input) input.value = '';
    
    openModal('merchant-qr-scanner-modal');
}

async function submitQRVerification() {
    const input = document.getElementById('scanner-qr-input');
    const resultDiv = document.getElementById('qr-verify-result');
    if (!input || !resultDiv) return;
    
    let qrData = input.value.trim();
    if (!qrData) {
        showToast('Masukkan kode QR atau Order ID.', 'warning');
        return;
    }
    
    // Auto-format: if user just typed a number, prepend the prefix
    if (/^\d+$/.test(qrData)) {
        qrData = `foodrescue-order:${qrData}`;
    }
    
    resultDiv.classList.remove('hidden');
    resultDiv.innerHTML = `
        <div class="bg-slate-50 p-4 rounded-xl text-center text-xs text-slate-500">
            <i class="fa-solid fa-spinner fa-spin text-emerald-500 mr-1"></i> Memverifikasi...
        </div>
    `;
    
    try {
        const res = await fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'verify_qr_claim',
                qr_data: qrData,
                csrf_token: getCsrfToken()
            })
        });
        const data = await res.json();
        
        if (data.success) {
            resultDiv.innerHTML = `
                <div class="bg-emerald-50 p-4 rounded-xl border border-emerald-200 space-y-2">
                    <div class="flex items-center gap-2 text-emerald-700 font-bold text-sm">
                        <i class="fa-solid fa-circle-check"></i> Verifikasi Berhasil!
                    </div>
                    <div class="text-xs text-slate-600 space-y-1">
                        <div>Pembeli: <strong>${data.rescuer_name}</strong></div>
                        <div>Makanan: <strong>${data.title}</strong> (${data.quantity} porsi)</div>
                        <div>Total: <strong>Rp${formatRupiah(data.total_price)}</strong></div>
                        <div>Bayar: <strong>${data.payment_method === 'qris' ? 'QRIS' : 'Cash'}</strong></div>
                    </div>
                </div>
            `;
            // Clear input
            input.value = '';
        } else {
            resultDiv.innerHTML = `
                <div class="bg-red-50 p-4 rounded-xl border border-red-200 text-xs text-red-600">
                    <i class="fa-solid fa-circle-xmark mr-1"></i> ${data.message}
                </div>
            `;
        }
    } catch (err) {
        resultDiv.innerHTML = `
            <div class="bg-red-50 p-4 rounded-xl border border-red-200 text-xs text-red-600">
                <i class="fa-solid fa-triangle-exclamation mr-1"></i> Gagal menghubungi server.
            </div>
        `;
    }
}

/**
 * Admin Panel Toggle Merchant State Action
 */
async function toggleMerchantStatus(merchantId, status) {
    const actionStr = status ? 'mengaktifkan' : 'menonaktifkan';
    const confirmToggle = confirm(`Apakah Anda yakin ingin ${actionStr} merchant ini?`);
    if (!confirmToggle) return;
    
    const payload = {
        action: 'toggle_merchant_status',
        merchant_id: merchantId,
        status: status,
        csrf_token: getCsrfToken()
    };
    
    try {
        const res = await fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showToast(data.message, 'error');
        }
    } catch (err) {
        showToast('Gagal memperbarui status merchant.', 'error');
    }
}

/**
 * Forgot Password - submit email to request reset
 */
async function submitForgotPassword(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const resultDiv = document.getElementById('forgot-password-result');
    
    try {
        const res = await fetch('api.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if (data.success && data.email) {
            // Email valid — go straight to reset password modal
            openResetPasswordModal(data.email);
        } else {
            resultDiv.classList.remove('hidden');
            resultDiv.innerHTML = `
                <div class="bg-red-50 p-4 rounded-xl border border-red-200 text-xs text-red-600">
                    <i class="fa-solid fa-circle-xmark mr-1"></i> ${data.message}
                </div>
            `;
        }
    } catch (err) {
        resultDiv.classList.remove('hidden');
        resultDiv.innerHTML = `
            <div class="bg-red-50 p-4 rounded-xl border border-red-200 text-xs text-red-600">
                <i class="fa-solid fa-triangle-exclamation mr-1"></i> Terjadi kesalahan koneksi server.
            </div>
        `;
    }
}

/**
 * Open reset password modal with token pre-filled
 */
function openResetPasswordModal(email) {
    const emailInput = document.getElementById('reset-email-input');
    if (emailInput) {
        emailInput.value = email;
    }
    switchModal('reset-password-modal');
}

/**
 * Check URL hash for reset token (e.g. #reset=TOKEN)
 */
function checkResetTokenFromURL() {
    const hash = window.location.hash;
    if (hash && hash.startsWith('#reset=')) {
        const token = hash.substring(7);
        if (token) {
            openResetPasswordModal(token);
            // Clean URL hash
            history.replaceState(null, null, window.location.pathname);
        }
    }
}

/**
 * Reset Password - submit new password with token
 */
async function submitResetPassword(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    
    const password = formData.get('password');
    const passwordConfirm = formData.get('password_confirm');
    
    if (password !== passwordConfirm) {
        showToast('Password konfirmasi tidak cocok.', 'warning');
        return;
    }
    
    try {
        const res = await fetch('api.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => window.location.href = 'index.php', 1500);
        } else {
            showToast(data.message, 'error');
        }
    } catch (err) {
        showToast('Terjadi kesalahan saat menghubungi server.', 'error');
    }
}

async function submitProfileEdit(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    try {
        const res = await fetch('api.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showToast(data.message, 'error');
        }
    } catch (err) {
        showToast('Gagal memperbarui profil.', 'error');
    }
}

function toggleEditProfile() {
    const form = document.getElementById('profile-edit-form');
    if (!form) return;
    const wasHidden = form.classList.contains('hidden');
    form.classList.toggle('hidden');
    if (wasHidden) {
        setTimeout(() => {
            const lat = document.getElementById('edit-lat')?.value || '-7.215373';
            const lng = document.getElementById('edit-lng')?.value || '107.899351';
            initPickerWithSearch({
                containerEl: 'edit-map-picker',
                latInputId: 'edit-lat',
                lngInputId: 'edit-lng',
                searchInputId: 'edit-map-search',
                searchResultsId: 'edit-map-results',
                mapInstanceVar: 'editMap',
                markerVar: 'editMarker',
                lat: parseFloat(lat),
                lng: parseFloat(lng)
            });
        }, 200);
    }
}

async function submitDeactivateMerchant() {
    if (!confirm('Yakin ingin mengajukan nonaktif merchant?')) return;
    try {
        const res = await fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'deactivate_merchant', csrf_token: getCsrfToken() })
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showToast(data.message, 'error');
        }
    } catch (err) {
        showToast('Gagal mengajukan nonaktif.', 'error');
    }
}
