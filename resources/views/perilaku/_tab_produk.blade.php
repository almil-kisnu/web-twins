{{-- Tab: Perilaku Produk --}}
<div id="view-produk" style="display: none;">
    {{-- Summary Cards --}}
    <div class="perilaku-summary perilaku-summary-3">
        <div class="summary-card gradient-indigo">
            <div class="summary-icon">
                <iconify-icon icon="solar:money-bag-bold-duotone"></iconify-icon>
            </div>
            <div class="summary-content">
                <span class="summary-label">Total Omset</span>
                <span class="summary-value" id="prod-total-omset">Rp 0</span>
            </div>
        </div>
        <div class="summary-card gradient-emerald">
            <div class="summary-icon">
                <iconify-icon icon="solar:graph-up-bold-duotone"></iconify-icon>
            </div>
            <div class="summary-content">
                <span class="summary-label">Total Laba</span>
                <span class="summary-value" id="prod-total-laba">Rp 0</span>
            </div>
        </div>
        <div class="summary-card gradient-amber">
            <div class="summary-icon">
                <iconify-icon icon="solar:box-minimalistic-bold-duotone"></iconify-icon>
            </div>
            <div class="summary-content">
                <span class="summary-label">Total Frekuensi</span>
                <span class="summary-value" id="prod-total-freq">0 item</span>
            </div>
        </div>
    </div>

    {{-- Product List --}}
    <div id="product-list" class="perilaku-list">
        <div class="perilaku-empty">
            <iconify-icon icon="solar:ghost-bold-duotone" style="font-size: 64px; color: #cbd5e1;"></iconify-icon>
            <p>Pilih toko dan tahun untuk memuat data produk</p>
        </div>
    </div>
</div>
