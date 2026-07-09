<?php
// faq.php
require_once __DIR__ . '/includes/auth.php';
include __DIR__ . '/components/header.php';
?>
<div class="max-w-3xl mx-auto px-4 py-10">
    <h1 class="text-3xl font-extrabold text-slate-800 mb-2">FAQ</h1>
    <p class="text-sm text-slate-400 mb-8">Pertanyaan umum seputar FoodRescue</p>

    <div class="space-y-3">
        <details class="bg-white border border-slate-200 rounded-2xl p-5 open:shadow-sm transition cursor-pointer group w-full">
            <summary class="font-bold text-sm text-slate-700 list-none flex items-center justify-between">
                Apa itu FoodRescue?
                <i class="fa-solid fa-chevron-down text-slate-300 group-open:rotate-180 transition text-xs"></i>
            </summary>
            <p class="mt-3 text-xs text-slate-500 leading-relaxed break-words">FoodRescue platform hubungkan merchant (toko/restoran) punya makanan surplus dengan masyarakat (rescuer) bisa beli harga terjangkau. Tujuan: kurangi food waste & bantu masyarakat dapat makanan murah.</p>
        </details>

        <details class="bg-white border border-slate-200 rounded-2xl p-5 open:shadow-sm transition cursor-pointer group w-full">
            <summary class="font-bold text-sm text-slate-700 list-none flex items-center justify-between">
                Siapa itu Rescuer?
                <i class="fa-solid fa-chevron-down text-slate-300 group-open:rotate-180 transition text-xs"></i>
            </summary>
            <p class="mt-3 text-xs text-slate-500 leading-relaxed break-words">Rescuer user biasa beli & klaim makanan surplus dari merchant. Cukup daftar akun gratis, lihat peta, pesan, ambil langsung ke toko.</p>
        </details>

        <details class="bg-white border border-slate-200 rounded-2xl p-5 open:shadow-sm transition cursor-pointer group w-full">
            <summary class="font-bold text-sm text-slate-700 list-none flex items-center justify-between">
                Bagaimana cara daftar jadi Merchant?
                <i class="fa-solid fa-chevron-down text-slate-300 group-open:rotate-180 transition text-xs"></i>
            </summary>
            <p class="mt-3 text-xs text-slate-500 leading-relaxed break-words">Klik "Daftar Toko" di header atau dari dropdown profil. Isi data toko, alamat, titik koordinat di peta. Admin akan verifikasi akun merchant kamu.</p>
        </details>

        <details class="bg-white border border-slate-200 rounded-2xl p-5 open:shadow-sm transition cursor-pointer group w-full">
            <summary class="font-bold text-sm text-slate-700 list-none flex items-center justify-between">
                Cara klaim makanan?
                <i class="fa-solid fa-chevron-down text-slate-300 group-open:rotate-180 transition text-xs"></i>
            </summary>
            <p class="mt-3 text-xs text-slate-500 leading-relaxed break-words">Buka peta, klik pin makanan tersedia, muncul detail sheet. Pilih jumlah, metode bayar (cash/QRIS), klik "Klaim Sekarang". Dapat QR tiket — tunjukkan ke kasir merchant.</p>
        </details>

        <details class="bg-white border border-slate-200 rounded-2xl p-5 open:shadow-sm transition cursor-pointer group w-full">
            <summary class="font-bold text-sm text-slate-700 list-none flex items-center justify-between">
                Metode pembayaran apa saja?
                <i class="fa-solid fa-chevron-down text-slate-300 group-open:rotate-180 transition text-xs"></i>
            </summary>
            <p class="mt-3 text-xs text-slate-500 leading-relaxed break-words">Cash (bayar langsung ke toko) dan QRIS (bayar online via QR code). Untuk QRIS, konfirmasi pembayaran setelah transfer.</p>
        </details>

        <details class="bg-white border border-slate-200 rounded-2xl p-5 open:shadow-sm transition cursor-pointer group w-full">
            <summary class="font-bold text-sm text-slate-700 list-none flex items-center justify-between">
                Bagaimana jika merchant tidak aktif?
                <i class="fa-solid fa-chevron-down text-slate-300 group-open:rotate-180 transition text-xs"></i>
            </summary>
            <p class="mt-3 text-xs text-slate-500 leading-relaxed break-words">Merchant harus diverifikasi admin dulu. Status aktif/nonaktif diatur admin. Hubungi admin lewat kontak di website jika ada masalah.</p>
        </details>

        <details class="bg-white border border-slate-200 rounded-2xl p-5 open:shadow-sm transition cursor-pointer group w-full">
            <summary class="font-bold text-sm text-slate-700 list-none flex items-center justify-between">
                Apakah ada biaya?
                <i class="fa-solid fa-chevron-down text-slate-300 group-open:rotate-180 transition text-xs"></i>
            </summary>
            <p class="mt-3 text-xs text-slate-500 leading-relaxed break-words">Daftar akun rescuer gratis. Merchant mungkin dikenakan biaya admin kecil (tentatif). Semua transaksi transparan.</p>
        </details>
    </div>
</div>
<?php include __DIR__ . '/components/footer.php'; ?>
