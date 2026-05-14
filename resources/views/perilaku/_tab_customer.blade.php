{{-- Tab: Perilaku Customer --}}
<div id="view-customer" style="display: none;">
    {{-- Filter Kanal --}}
    <div class="sub-tab-navigation" style="margin-bottom: 20px;">
        <button class="sub-tab-pill active" id="kanal-semua" onclick="switchKanal('semua')">Semua</button>
        <button class="sub-tab-pill" id="kanal-offline" onclick="switchKanal('offline')">Offline</button>
        <button class="sub-tab-pill" id="kanal-online" onclick="switchKanal('online')">Online</button>
    </div>

    {{-- Summary Cards --}}
    <div class="perilaku-summary">
        <div class="summary-card gradient-blue">
            <div class="summary-icon">
                <iconify-icon icon="solar:money-bag-bold-duotone"></iconify-icon>
            </div>
            <div class="summary-content">
                <span class="summary-label">Total Omset Tahunan</span>
                <span class="summary-value" id="cust-total-omset">Rp 0</span>
            </div>
        </div>
        <div class="summary-card gradient-teal">
            <div class="summary-icon">
                <iconify-icon icon="solar:users-group-rounded-bold-duotone"></iconify-icon>
            </div>
            <div class="summary-content">
                <span class="summary-label">Jumlah Customer</span>
                <span class="summary-value" id="cust-total-count">0 Customer</span>
            </div>
        </div>
    </div>

    {{-- Customer List --}}
    <div id="customer-list" class="perilaku-list">
        <div class="perilaku-empty">
            <iconify-icon icon="solar:ghost-bold-duotone" style="font-size: 64px; color: #cbd5e1;"></iconify-icon>
            <p>Pilih toko dan tahun untuk memuat data customer</p>
        </div>
    </div>
</div>
