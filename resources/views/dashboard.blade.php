@extends('layouts.app')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    body {
        background-color: #f8fafc;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .dashboard-wrapper {
        padding: 0.5rem 1.5rem;
        animation: fadeIn 0.6s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Top Stats Card */
    .stat-card {
        border-radius: 48px;
        padding: 1.25rem;
        border: 1px solid rgba(0, 0, 0, 0.05);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        animation: fadeInUp 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 25px 30px -10px rgba(0, 0, 0, 0.15);
    }

    .icon-box {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .stat-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 0.75rem;
    }

    .stat-label {
        font-size: 0.725rem;
        color: #64748b;
        font-weight: 700;
        margin: 0;
        line-height: 1;
    }

    .stat-value {
        font-size: 1.25rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 0.25rem;
        text-align: center;
    }

    .stat-trend {
        font-size: 0.65rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 3px;
    }

    .trend-up { color: #10b981; }
    .trend-down { color: #f43f5e; }

    /* Layout Grids */
    .main-grid {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: 1.5rem;
        margin-top: 1.5rem;
    }

    .card {
        background: #fff;
        border-radius: 60px;
        border: 1px solid rgba(0, 0, 0, 0.05);
        padding: 2rem;
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        animation: fadeInUp 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        position: relative;
    }

    .card:hover {
        transform: translateY(-10px);
        box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.15);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .card-title {
        font-size: 1.1rem;
        font-weight: 800;
        color: #0f172a;
    }

    /* Custom Table Styling */
    .custom-table {
        width: 100%;
        border-collapse: collapse;
    }

    .custom-table th {
        text-align: left;
        font-size: 0.7rem;
        color: #94a3b8;
        text-transform: uppercase;
        padding-bottom: 1rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .custom-table td {
        padding: 1rem 0;
        vertical-align: middle;
        font-size: 0.9rem;
        color: #334155;
    }

    .product-img {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        object-fit: cover;
        background: #f1f5f9;
    }

    .status-badge {
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .badge-critical { background: #fee2e2; color: #b91c1c; }
    .badge-low { background: #ffedd5; color: #c2410c; }

    /* Custom Tabs for Stock/Expired - Compact Version */
    .stock-tabs {
        display: flex;
        gap: 3px;
        background: #f1f5f9;
        padding: 3px;
        border-radius: 10px;
        margin-bottom: 12px;
        width: fit-content; /* Make it only as wide as content */
        min-width: 250px;
    }

    .stock-tab-btn {
        flex: 1;
        padding: 4px 8px;
        border-radius: 8px;
        font-size: 0.65rem;
        font-weight: 800;
        color: #64748b;
        border: none;
        background: transparent;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
    }

    .stock-tab-btn.active {
        background: #0477bf;
        color: #ffffff;
        box-shadow: 0 2px 8px -1px rgba(4, 119, 191, 0.25);
    }

    .stock-content {
        display: none;
        animation: slideInUp 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    @keyframes slideInUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .stock-content.active {
        display: block;
    }

    .scrollable-table-container {
        max-height: 280px;
        overflow-y: auto;
        padding-right: 5px;
        scrollbar-width: thin;
        scrollbar-color: #3b82f6 #f1f5f9;
    }

    /* Modern Detail Button */
    .btn-detail-modern {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.7rem;
        font-weight: 700;
        color: var(--btn-theme, #6366f1);
        background: var(--btn-bg, rgba(99, 102, 241, 0.08));
        padding: 5px 12px;
        border-radius: 50px;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        text-decoration: none !important;
        width: fit-content;
        border: 1px solid var(--btn-border, rgba(99, 102, 241, 0.1));
    }

    .btn-detail-modern:hover {
        background: var(--btn-theme, #6366f1) !important;
        color: #fff !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }

    .btn-detail-modern iconify-icon {
        font-size: 1rem;
        transition: transform 0.3s ease;
    }

    .btn-detail-modern:hover iconify-icon {
        transform: translateX(3px);
    }

    /* Custom Scrollbar */
    .scrollable-table-container::-webkit-scrollbar {
        width: 4px;
    }
    .scrollable-table-container::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 10px;
    }
    .scrollable-table-container::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    .compact-table td {
        padding: 0.65rem 0 !important;
        font-size: 0.8rem !important;
    }

    /* Activity Feed */
    .activity-item {
        display: flex;
        gap: 12px;
        margin-bottom: 1.25rem;
        position: relative;
    }

    .activity-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .activity-content {
        flex: 1;
    }

    .activity-user {
        font-weight: 700;
        color: #0f172a;
        font-size: 0.85rem;
    }

    .activity-text {
        font-size: 0.8rem;
        color: #64748b;
        line-height: 1.4;
    }

    .activity-time {
        font-size: 0.75rem;
        color: #94a3b8;
        font-weight: 600;
    }

    /* Chart Select Styling */
    .chart-select {
        padding: 6px 12px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        font-size: 0.75rem;
        font-weight: 700;
        color: #475569;
        background: #fff;
        cursor: pointer;
    }

    /* Ultra-Clean Welcome Section */
    .welcome-section {
        margin-bottom: 0.75rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        padding: 0;
    }

    .welcome-title {
        font-size: 1.5rem !important;
        font-weight: 800 !important;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .user-name-gradient {
        background: linear-gradient(135deg, #0477bf 0%, #035a91 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        display: inline-block;
        position: relative;
    }

    .user-name-gradient::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 0;
        height: 3px;
        background: #0477bf;
        border-radius: 50px;
        animation: underlineIn 1s ease-out forwards;
    }

    @keyframes underlineIn {
        from { width: 0; }
        to { width: 100%; }
    }

    .waving-hand {
        display: inline-block;
        animation: wave 2.5s infinite;
        transform-origin: 70% 70%;
        font-size: 1.75rem;
    }

    @keyframes wave {
        0% { transform: rotate(0deg); }
        10% { transform: rotate(14deg); }
        20% { transform: rotate(-8deg); }
        30% { transform: rotate(14deg); }
        40% { transform: rotate(-4deg); }
        50% { transform: rotate(10deg); }
        60% { transform: rotate(0deg); }
        100% { transform: rotate(0deg); }
    }

    .filter-group-minimal {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 8px 20px;
        border-radius: 50px;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        animation: slideInRight 0.8s cubic-bezier(0.4, 0, 0.2, 1) backwards;
    }

    @keyframes slideInRight {
        from { opacity: 0; transform: translateX(20px); }
        to { opacity: 1; transform: translateX(0); }
    }

    .filter-group-minimal:hover {
        background: #fff;
        border-color: #0477bf;
        box-shadow: 0 10px 20px -5px rgba(4, 119, 191, 0.15);
        transform: translateY(-2px);
    }

    .filter-group-minimal:hover iconify-icon {
        animation: bounceShop 0.6s ease infinite alternate;
    }

    @keyframes bounceShop {
        from { transform: translateY(0); }
        to { transform: translateY(-3px); }
    }
</style>
@endpush

@section('content')
<div class="dashboard-wrapper">
    @if(Auth::user()->role === 'owner')
        <div class="welcome-section">
            <div>
                <h1 class="welcome-title">
                    Selamat Datang, 
                    <span class="user-name-gradient">{{ Auth::user()->name }}</span>
                    <span class="waving-hand">👋</span>
                </h1>
            </div>
            
            <div class="flex items-center gap-3 flex-wrap">
                <!-- Outlet Filter -->
                <div class="filter-group-minimal">
                    <iconify-icon icon="solar:shop-2-bold-duotone" style="color: #0477bf; font-size: 1.25rem;"></iconify-icon>
                    <select class="outlet-select" style="border: none; background: transparent; font-size: 0.8rem; font-weight: 700; color: #475569; outline: none; padding-right: 15px;" onchange="filterByStore(this.value)">
                        <option value="">Semua Outlet (Pusat)</option>
                        @foreach($stores ?? [] as $store)
                            <option value="{{ $store->uuid }}" {{ ($currentStoreId ?? '') == $store->uuid ? 'selected' : '' }}>
                                {{ $store->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    <!-- Top Row Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Total Transaksi -->
        <div class="stat-card" style="background: #f0f7ff; animation-delay: 0.1s;">
            <div class="stat-header">
                <div class="icon-box" style="background: #eff6ff; color: #3b82f6;">
                    <iconify-icon icon="solar:bill-list-bold-duotone"></iconify-icon>
                </div>
                <div class="stat-label">Total Transaksi</div>
            </div>
            <div class="stat-value">{{ number_format($total_transaksi) }}</div>
            <div class="stat-trend {{ $diff_transaksi >= 0 ? 'trend-up' : 'trend-down' }}">
                <iconify-icon icon="{{ $diff_transaksi >= 0 ? 'solar:alt-arrow-up-bold' : 'solar:alt-arrow-down-bold' }}"></iconify-icon>
                {{ abs($diff_transaksi) }}% dari kemarin
            </div>
        </div>

        <!-- Pendapatan Offline -->
        <div class="stat-card" style="background: #f0fdf4; animation-delay: 0.2s;">
            <div class="stat-header">
                <div class="icon-box" style="background: #f0fdf4; color: #10b981;">
                    <iconify-icon icon="solar:cart-large-minimalistic-bold"></iconify-icon>
                </div>
                <div class="stat-label">Pendapatan Offline</div>
            </div>
            <div class="stat-value">Rp {{ number_format($rev_offline / 1000, 0) }}k</div>
            <div class="stat-trend {{ $diff_offline >= 0 ? 'trend-up' : 'trend-down' }}">
                <iconify-icon icon="{{ $diff_offline >= 0 ? 'solar:alt-arrow-up-bold' : 'solar:alt-arrow-down-bold' }}"></iconify-icon>
                {{ abs($diff_offline) }}% dari kemarin
            </div>
        </div>

        <!-- Pendapatan Online -->
        <div class="stat-card" style="background: #f0f9ff; animation-delay: 0.3s;">
            <div class="stat-header">
                <div class="icon-box" style="background: #f0f9ff; color: #0ea5e9;">
                    <iconify-icon icon="solar:global-bold"></iconify-icon>
                </div>
                <div class="stat-label">Pendapatan Online</div>
            </div>
            <div class="stat-value">Rp {{ number_format($rev_online / 1000, 0) }}k</div>
            <div class="stat-trend {{ $diff_online >= 0 ? 'trend-up' : 'trend-down' }}">
                <iconify-icon icon="{{ $diff_online >= 0 ? 'solar:alt-arrow-up-bold' : 'solar:alt-arrow-down-bold' }}"></iconify-icon>
                {{ abs($diff_online) }}% dari kemarin
            </div>
        </div>

        <!-- Produk Terjual -->
        <div class="stat-card" style="background: #fffaf0; animation-delay: 0.4s;">
            <div class="stat-header">
                <div class="icon-box" style="background: #fff7ed; color: #f97316;">
                    <iconify-icon icon="solar:box-bold-duotone"></iconify-icon>
                </div>
                <div class="stat-label">Produk Terjual</div>
            </div>
            <div class="stat-value">{{ number_format($total_produk_terjual) }}</div>
            <div class="stat-trend {{ $diff_produk_terjual >= 0 ? 'trend-up' : 'trend-down' }}">
                <iconify-icon icon="{{ $diff_produk_terjual >= 0 ? 'solar:alt-arrow-up-bold' : 'solar:alt-arrow-down-bold' }}"></iconify-icon>
                {{ abs($diff_produk_terjual) }}% dari kemarin
            </div>
        </div>

        <!-- Total Customer -->
        <div class="stat-card" style="background: #f5f3ff; animation-delay: 0.5s;">
            <div class="stat-header">
                <div class="icon-box" style="background: #faf5ff; color: #8b5cf6;">
                    <iconify-icon icon="solar:users-group-two-rounded-bold-duotone"></iconify-icon>
                </div>
                <div class="stat-label">Total Customer</div>
            </div>
            <div class="stat-value">{{ number_format($total_customers) }}</div>
            <div style="margin-top: 10px;">
                <a href="{{ url('/users') }}" class="btn-detail-modern" style="--btn-theme: #8b5cf6; --btn-bg: rgba(139, 92, 246, 0.08); --btn-border: rgba(139, 92, 246, 0.1);">
                    <span>Lihat Detail</span>
                    <iconify-icon icon="solar:alt-arrow-right-bold"></iconify-icon>
                </a>
            </div>
        </div>
    </div>

    <!-- Second Row Charts -->
    <div class="main-grid">
        <!-- Main Sales Chart -->
        <div class="col-span-12 lg:col-span-8 card" style="background: #f0f7ff; border-color: rgba(59, 130, 246, 0.15);">
            <div class="card-header">
                <h3 class="card-title">Penjualan Hari Ini</h3>
                <div class="flex items-center gap-2">
                    <div id="year-range-picker" class="hidden flex items-center gap-2 mr-2">
                        <input type="number" id="year-from" class="chart-select w-20" value="{{ date('Y') - 4 }}" placeholder="Dari">
                        <span class="text-xs font-bold">-</span>
                        <input type="number" id="year-to" class="chart-select w-20" value="{{ date('Y') }}" placeholder="Ke">
                        <button onclick="applyYearRange()" class="p-2 bg-blue-500 text-white rounded-lg flex items-center justify-center">
                            <iconify-icon icon="solar:check-read-bold"></iconify-icon>
                        </button>
                    </div>
                    <select class="chart-select" onchange="updateMainChart(this.value)">
                        <option value="harian" selected>Harian</option>
                        <option value="mingguan">Mingguan</option>
                        <option value="bulanan">Bulanan</option>
                        <option value="tahunan">Tahunan</option>
                    </select>
                </div>
            </div>
            <div id="mainSalesChart" style="min-height: 300px;"></div>
            <div class="grid grid-cols-2 mt-2 pt-4 border-t border-slate-100">
                <div class="text-center">
                    <p class="stat-label">Total Penjualan Hari Ini</p>
                    <p class="value" style="font-size: 1.25rem; font-weight: 800; color: #3b82f6;">Rp {{ number_format($total_pendapatan, 0, ',', '.') }}</p>
                </div>
                <div class="text-center border-l border-slate-100">
                    <p class="stat-label">Rata-rata per Jam</p>
                    <p class="value" style="font-size: 1.25rem; font-weight: 800;">Rp {{ number_format($total_pendapatan / 24, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <!-- Right Side Charts -->
        <div class="col-span-12 lg:col-span-4 flex flex-col gap-6">
            <!-- Pemasukan & Pengeluaran -->
            <div class="card" style="background: #f0fdf4; border-color: rgba(16, 185, 129, 0.1);">
                <div class="card-header">
                    <h3 class="card-title" style="font-size: 0.95rem;">Pemasukan & Pengeluaran</h3>
                    <select class="chart-select" onchange="updateCashFlow(this.value)">
                        <option value="harian">Hari Ini</option>
                        <option value="mingguan">Minggu Ini</option>
                        <option value="bulanan" selected>Bulan Ini</option>
                        <option value="tahunan">Tahun Ini</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center text-green-500">
                                <iconify-icon icon="solar:alt-arrow-down-bold"></iconify-icon>
                            </div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Pemasukan</span>
                        </div>
                        <p class="font-extrabold text-green-600 text-sm" id="cf-total-pemasukan">Rp {{ number_format($cfBulanan['total_pemasukan'] / 1000, 0) }}k</p>
                        <div id="pemasukanChart" style="height: 60px;"></div>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center text-red-500">
                                <iconify-icon icon="solar:alt-arrow-up-bold"></iconify-icon>
                            </div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Pengeluaran</span>
                        </div>
                        <p class="font-extrabold text-red-600 text-sm" id="cf-total-pengeluaran">Rp {{ number_format($cfBulanan['total_pengeluaran'] / 1000, 0) }}k</p>
                        <div id="pengeluaranChart" style="height: 60px;"></div>
                    </div>
                </div>
            </div>

            <!-- Hutang & Piutang -->
            <div class="card flex-1" style="background: #fef2f2; border-color: rgba(239, 68, 68, 0.1);">
                <div class="card-header">
                    <h3 class="card-title" style="font-size: 0.95rem;">Hutang & Piutang</h3>
                    <iconify-icon icon="solar:alt-arrow-right-bold" style="color: #cbd5e1;"></iconify-icon>
                </div>
                <div class="flex items-center gap-4">
                    <div id="debtChart" style="width: 140px;"></div>
                    <div class="flex-1">
                        <div class="mb-4">
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                <span class="text-[11px] font-bold text-slate-500">Piutang</span>
                            </div>
                            <p style="font-size: 0.8rem; font-weight: 700; color: #0f172a;">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-2 h-2 rounded-full bg-red-500"></div>
                                <span class="text-[11px] font-bold text-slate-500">Hutang</span>
                            </div>
                            <p style="font-size: 0.8rem; font-weight: 700; color: #0f172a;">Rp {{ number_format($totalHutang, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Third Row Tables -->
    <div class="main-grid">
        <!-- Stok & Kadaluarsa Tabbed Card -->
        <div class="col-span-12 lg:col-span-4 card" style="background: #fffaf0; border-color: rgba(249, 115, 22, 0.1);">
            <div class="card-header" style="margin-bottom: 10px;">
                <h3 class="card-title">Stok & Kadaluarsa</h3>
                <a href="{{ url('/products?tab=stok') }}" class="btn-detail-modern" style="--btn-theme: #3b82f6; --btn-bg: rgba(59, 130, 246, 0.08); --btn-border: rgba(59, 130, 246, 0.1);">
                    <span>Kelola</span>
                    <iconify-icon icon="solar:settings-bold-duotone"></iconify-icon>
                </a>
            </div>
            
            <div class="stock-tabs">
                <button class="stock-tab-btn active" onclick="switchStockTab('stok', this)">
                    <iconify-icon icon="solar:Box-bold-duotone"></iconify-icon>
                    Stok Menipis
                </button>
                <button class="stock-tab-btn" onclick="switchStockTab('expired', this)">
                    <iconify-icon icon="solar:history-bold-duotone"></iconify-icon>
                    Segera Expired
                </button>
            </div>

            <!-- Tab Stok Menipis -->
            <div id="tab-stok" class="stock-content active">
                <div class="scrollable-table-container">
                    <table class="custom-table compact-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th style="text-align: center;">Tersedia</th>
                                <th style="text-align: right;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lowStockProducts as $ps)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $ps->product->resolved_image_url }}" class="product-img w-8 h-8">
                                        <div>
                                            <p class="font-bold text-[11px] mb-0 line-clamp-1">{{ $ps->product->nama_produk }}</p>
                                            <p class="text-[9px] text-slate-400 mb-0">{{ $ps->store->nama ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td style="text-align: center;" class="font-extrabold">{{ $ps->stok }}</td>
                                <td style="text-align: right;">
                                    <span class="status-badge {{ $ps->stok <= 2 ? 'badge-critical' : 'badge-low' }}" style="padding: 2px 6px; font-size: 0.6rem;">
                                        {{ $ps->stok <= 2 ? 'Kritis' : 'Rendah' }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-8 text-slate-400">Semua stok aman.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab Expired -->
            <div id="tab-expired" class="stock-content">
                <div class="scrollable-table-container">
                    <table class="custom-table compact-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th style="text-align: center;">Expired</th>
                                <th style="text-align: right;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expiredProducts ?? [] as $ps)
                            @php
                                $daysLeft = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($ps->kadaluarsa), false);
                            @endphp
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $ps->product->resolved_image_url }}" class="product-img w-8 h-8">
                                        <div>
                                            <p class="font-bold text-[11px] mb-0 line-clamp-1">{{ $ps->product->nama_produk }}</p>
                                            <p class="text-[9px] text-slate-400 mb-0">{{ $ps->store->nama ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td style="text-align: center;" class="font-extrabold text-[10px]">
                                    {{ \Carbon\Carbon::parse($ps->kadaluarsa)->format('d/m/y') }}
                                </td>
                                <td style="text-align: right;">
                                    <span class="status-badge {{ $daysLeft <= 7 ? 'badge-critical' : 'badge-low' }}" style="padding: 2px 6px; font-size: 0.6rem;">
                                        {{ $daysLeft <= 0 ? 'Expired' : ($daysLeft <= 7 ? 'Kritis' : 'Dekat') }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-8 text-slate-400">Tidak ada produk segera expired.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Produk Terlaris Table -->
        <div class="col-span-12 lg:col-span-4 card" style="background: #f5f3ff; border-color: rgba(139, 92, 246, 0.1);">
            <div class="card-header">
                <h3 class="card-title">Produk Terlaris</h3>
            </div>
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="width: 30px;">#</th>
                        <th>Produk</th>
                        <th style="text-align: center;">Terjual</th>
                        <th style="text-align: right;">Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topProducts as $index => $tp)
                    <tr>
                        <td class="font-bold text-slate-400">{{ $index + 1 }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <img src="{{ $tp->product->resolved_image_url }}" class="product-img w-8 h-8">
                                <span class="font-bold text-xs truncate max-w-[100px]">{{ $tp->product->nama_produk }}</span>
                            </div>
                        </td>
                        <td style="text-align: center;" class="font-extrabold">{{ $tp->total_qty }}</td>
                        <td style="text-align: right;" class="font-extrabold text-blue-600">Rp {{ number_format($tp->total_revenue / 1000, 0) }}k</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Aktivitas Penjualan Feed -->
        <div class="col-span-12 lg:col-span-4 card" style="background: #f0f4ff; border-color: rgba(99, 102, 241, 0.1);">
            <div class="card-header">
                <h3 class="card-title">Aktivitas Penjualan</h3>
                <iconify-icon icon="solar:history-bold-duotone" style="color: #64748b;"></iconify-icon>
            </div>
            <div class="activity-feed">
                @forelse($activities as $act)
                <div class="activity-item">
                    <div class="activity-icon" style="background: {{ $act['role'] == 'Online' ? '#e0e7ff' : '#f1f5f9' }};">
                        <iconify-icon icon="{{ $act['icon'] }}" style="color: {{ $act['role'] == 'Online' ? '#4f46e5' : '#64748b' }};"></iconify-icon>
                    </div>
                    <div class="activity-content">
                        <div class="flex justify-between items-start">
                            <span class="activity-user">{{ $act['user'] }}</span>
                            <span class="activity-time">{{ $act['time'] }}</span>
                        </div>
                        <p class="activity-text">
                            <span class="font-bold text-[10px] uppercase {{ $act['role'] == 'Online' ? 'text-indigo-500' : 'text-slate-400' }}">
                                {{ $act['role'] }}
                            </span> | {{ $act['action'] }}
                        </p>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <p class="text-slate-400 text-sm font-bold">Belum ada aktivitas penjualan hari ini.</p>
                </div>
                @endforelse
            </div>
        </div>
    @else
        <!-- Premium Dashboard for Kepala Toko / Staff -->
        <div class="welcome-section">
            <div>
                <h1 class="welcome-title">
                    Dashboard {{ ucfirst(Auth::user()->role) }}
                    <span class="waving-hand">👋</span>
                </h1>
                <p style="color: #64748b; font-size: 0.85rem; font-weight: 600; margin-top: 2px;">
                    Mengelola outlet: <span style="color: #0477bf;">{{ Auth::user()->store->nama ?? 'Semua Outlet' }}</span>
                </p>
            </div>
        </div>

        <!-- 5-Column Stats Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Total Transaksi -->
            <div class="stat-card" style="background: #ffffff; border-radius: 20px; padding: 1.25rem;">
                <div class="stat-header">
                    <div class="icon-box" style="background: #f0f7ff; color: #3b82f6; border-radius: 12px;">
                        <iconify-icon icon="solar:bill-list-bold-duotone"></iconify-icon>
                    </div>
                    <div>
                        <div class="stat-label" style="font-size: 0.65rem; color: #94a3b8;">Total Transaksi</div>
                        <div class="text-[10px] font-bold text-slate-400">Hari Ini</div>
                    </div>
                </div>
                <div class="stat-value" style="font-size: 1.5rem; margin: 0.5rem 0;">{{ number_format($total_transaksi) }}</div>
                <div class="stat-trend {{ $diff_transaksi >= 0 ? 'trend-up' : 'trend-down' }}" style="justify-content: flex-start;">
                    <iconify-icon icon="{{ $diff_transaksi >= 0 ? 'solar:alt-arrow-up-bold' : 'solar:alt-arrow-down-bold' }}"></iconify-icon>
                    {{ abs($diff_transaksi) }}% dari kemarin
                </div>
            </div>

            <!-- Pendapatan -->
            <div class="stat-card" style="background: #ffffff; border-radius: 20px; padding: 1.25rem;">
                <div class="stat-header">
                    <div class="icon-box" style="background: #f0fdf4; color: #10b981; border-radius: 12px;">
                        <iconify-icon icon="solar:wallet-money-bold-duotone"></iconify-icon>
                    </div>
                    <div>
                        <div class="stat-label" style="font-size: 0.65rem; color: #94a3b8;">Pendapatan</div>
                        <div class="text-[10px] font-bold text-slate-400">Hari Ini</div>
                    </div>
                </div>
                <div class="stat-value" style="font-size: 1.25rem; margin: 0.5rem 0;">Rp {{ number_format($total_pendapatan, 0, ',', '.') }}</div>
                <div class="stat-trend {{ $diff_pendapatan >= 0 ? 'trend-up' : 'trend-down' }}" style="justify-content: flex-start;">
                    <iconify-icon icon="{{ $diff_pendapatan >= 0 ? 'solar:alt-arrow-up-bold' : 'solar:alt-arrow-down-bold' }}"></iconify-icon>
                    {{ abs($diff_pendapatan) }}% dari kemarin
                </div>
            </div>

            <!-- Produk Terjual -->
            <div class="stat-card" style="background: #ffffff; border-radius: 20px; padding: 1.25rem;">
                <div class="stat-header">
                    <div class="icon-box" style="background: #fffaf0; color: #f97316; border-radius: 12px;">
                        <iconify-icon icon="solar:box-bold-duotone"></iconify-icon>
                    </div>
                    <div>
                        <div class="stat-label" style="font-size: 0.65rem; color: #94a3b8;">Produk Terjual</div>
                        <div class="text-[10px] font-bold text-slate-400">Hari Ini</div>
                    </div>
                </div>
                <div class="stat-value" style="font-size: 1.5rem; margin: 0.5rem 0;">{{ number_format($total_produk_terjual) }}</div>
                <div class="stat-trend {{ $diff_produk_terjual >= 0 ? 'trend-up' : 'trend-down' }}" style="justify-content: flex-start;">
                    <iconify-icon icon="{{ $diff_produk_terjual >= 0 ? 'solar:alt-arrow-up-bold' : 'solar:alt-arrow-down-bold' }}"></iconify-icon>
                    {{ abs($diff_produk_terjual) }}% dari kemarin
                </div>
            </div>

            <!-- Stok Menipis -->
            <div class="stat-card" style="background: #ffffff; border-radius: 20px; padding: 1.25rem;">
                <div class="stat-header">
                    <div class="icon-box" style="background: #fef2f2; color: #ef4444; border-radius: 12px;">
                        <iconify-icon icon="solar:danger-bold-duotone"></iconify-icon>
                    </div>
                    <div>
                        <div class="stat-label" style="font-size: 0.65rem; color: #94a3b8;">Stok Menipis</div>
                        <div class="text-[10px] font-bold text-slate-400">Hari Ini</div>
                    </div>
                </div>
                <div class="stat-value" style="font-size: 1.5rem; margin: 0.5rem 0;">{{ number_format($low_stock_count) }}</div>
                <a href="{{ url('/products?tab=stok') }}" style="font-size: 0.7rem; color: #3b82f6; font-weight: 700; text-decoration: underline;">Lihat detail</a>
            </div>

            <!-- Karyawan Aktif -->
            <div class="stat-card" style="background: #ffffff; border-radius: 20px; padding: 1.25rem;">
                <div class="stat-header">
                    <div class="icon-box" style="background: #f5f3ff; color: #8b5cf6; border-radius: 12px;">
                        <iconify-icon icon="solar:users-group-rounded-bold-duotone"></iconify-icon>
                    </div>
                    <div>
                        <div class="stat-label" style="font-size: 0.65rem; color: #94a3b8;">Karyawan Aktif</div>
                        <div class="text-[10px] font-bold text-slate-400">Hari Ini</div>
                    </div>
                </div>
                <div class="stat-value" style="font-size: 1.5rem; margin: 0.5rem 0;">{{ $active_employees }} <span style="font-size: 0.8rem; color: #94a3b8;">/ {{ $total_employees }}</span></div>
                <a href="{{ url('/users') }}" style="font-size: 0.7rem; color: #3b82f6; font-weight: 700; text-decoration: underline;">Lihat detail</a>
            </div>
        </div>

        <!-- Sales Chart -->
        <div class="card mt-6" style="border-radius: 24px; padding: 1.5rem; background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border: 1px solid rgba(226, 232, 240, 0.8);">
            <div class="card-header">
                <h3 class="card-title">Penjualan Hari Ini</h3>
                <div class="flex items-center gap-2">
                    <select class="chart-select" onchange="updateMainChart(this.value)" style="padding: 6px 16px; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 0.75rem; font-weight: 700; color: #475569; outline: none; cursor: pointer;">
                        <option value="harian">Per Jam</option>
                        <option value="mingguan">Per Hari</option>
                        <option value="bulanan">Per Bulan</option>
                        <option value="tahunan">Per Tahun</option>
                    </select>
                    <div id="year-range-picker" class="hidden flex items-center gap-2">
                        <input type="number" id="year-from" value="{{ date('Y')-4 }}" class="w-16 p-1 text-[10px] border rounded">
                        <span class="text-[10px]">-</span>
                        <input type="number" id="year-to" value="{{ date('Y') }}" class="w-16 p-1 text-[10px] border rounded">
                        <button onclick="applyYearRange()" class="p-1 bg-blue-500 text-white rounded"><iconify-icon icon="solar:check-read-bold"></iconify-icon></button>
                    </div>
                </div>
            </div>
            <div id="mainSalesChart" style="min-height: 300px;"></div>
        </div>

        <!-- Bottom Row -->
        <div class="grid grid-cols-12 gap-6 mt-6">
            <!-- Produk Terlaris -->
            <div class="col-span-12 lg:col-span-6 card" style="border-radius: 24px; padding: 1.5rem;">
                <div class="card-header">
                    <h3 class="card-title">Produk Terlaris Hari Ini</h3>
                    <a href="{{ url('/products') }}" style="font-size: 0.7rem; color: #3b82f6; font-weight: 700;">Lihat semua</a>
                </div>
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width: 30px;">#</th>
                            <th>Produk</th>
                            <th style="text-align: center;">Terjual</th>
                            <th style="text-align: right;">Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topProducts as $index => $tp)
                        <tr>
                            <td class="font-bold text-slate-400">{{ $index + 1 }}</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <img src="{{ $tp->product->resolved_image_url ?? '' }}" class="product-img w-8 h-8" style="border-radius: 8px;">
                                    <span class="font-bold text-xs">{{ $tp->product->nama_produk ?? 'Unknown' }}</span>
                                </div>
                            </td>
                            <td style="text-align: center;" class="font-extrabold">{{ $tp->total_qty }}</td>
                            <td style="text-align: right;" class="font-extrabold text-blue-600">Rp {{ number_format($tp->total_revenue, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Aktivitas Karyawan -->
            <div class="col-span-12 lg:col-span-6 card" style="border-radius: 24px; padding: 1.5rem;">
                <div class="card-header">
                    <h3 class="card-title">Aktivitas Karyawan Hari Ini</h3>
                    <a href="#" style="font-size: 0.7rem; color: #3b82f6; font-weight: 700;">Lihat semua</a>
                </div>
                <div class="activity-feed">
                    @forelse($activities as $act)
                    <div class="activity-item" style="margin-bottom: 1rem;">
                        <div class="activity-icon" style="background: #f1f5f9; border-radius: 50%;">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($act['user']) }}&background=random" class="w-full h-full rounded-full">
                        </div>
                        <div class="activity-content">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="activity-user" style="margin-bottom: 0;">{{ $act['user'] }}</p>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase">{{ $act['role'] }}</p>
                                </div>
                                <span class="activity-time">{{ $act['time'] }}</span>
                            </div>
                            <p class="activity-text" style="margin-top: 2px;">{{ $act['action'] }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-slate-400">Belum ada aktivitas.</div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>

@if(Auth::user()->role === 'owner' || Auth::user()->role === 'kepala toko')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    const datasets = {
        harian: { labels: @json($chartHarian['labels']), offline: @json($chartHarian['offline']), online: @json($chartHarian['online']) },
        mingguan: { labels: @json($chartMingguan['labels']), offline: @json($chartMingguan['offline']), online: @json($chartMingguan['online']) },
        bulanan: { labels: @json($chartBulanan['labels']), offline: @json($chartBulanan['offline']), online: @json($chartBulanan['online']) },
        tahunan: { labels: @json($chartTahunan['labels']), offline: @json($chartTahunan['offline']), online: @json($chartTahunan['online']) }
    };

    const mainChart = new ApexCharts(document.querySelector("#mainSalesChart"), {
        series: [{ name: 'Offline (Kasir)', data: datasets.harian.offline }, { name: 'Online (Web)', data: datasets.harian.online }],
        chart: {
            height: 300,
            type: 'area',
            toolbar: { show: false },
            zoom: { enabled: false },
            fontFamily: 'Plus Jakarta Sans',
            animations: { enabled: true, easing: 'easeinout', speed: 800 }
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 3 },
        colors: ['#3b82f6', '#10b981'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.35,
                opacityTo: 0.05,
                stops: [0, 90, 100]
            }
        },
        xaxis: {
            categories: datasets.harian.labels,
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: { style: { colors: '#94a3b8', fontWeight: 600 } }
        },
        yaxis: {
            labels: {
                formatter: function (val) { 
                    if (val >= 1000000) return "Rp " + (val / 1000000).toFixed(1) + "jt";
                    if (val >= 1000) return "Rp " + (val / 1000).toFixed(0) + "rb";
                    if (val === 0) return "Rp 0";
                    return "Rp " + val;
                },
                style: { colors: '#94a3b8', fontWeight: 600 }
            }
        },
        grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
        legend: {
            position: 'top',
            horizontalAlign: 'right',
            fontWeight: 700,
            labels: { colors: '#64748b' },
            markers: { radius: 12 }
        },
        markers: { size: 0, hover: { size: 5 } }
    });
    mainChart.render();

    function updateMainChart(preset) {
        const d = datasets[preset];
        const picker = document.getElementById('year-range-picker');
        
        if (preset === 'tahunan') {
            picker.classList.remove('hidden');
        } else {
            picker.classList.add('hidden');
        }

        mainChart.updateOptions({
            xaxis: {
                categories: d.labels,
                labels: { style: { colors: '#94a3b8', fontWeight: 600 } }
            },
            series: [
                { name: 'Offline (Kasir)', data: d.offline },
                { name: 'Online (Web)', data: d.online }
            ]
        });
    }

    function applyYearRange() {
        const from = document.getElementById('year-from').value;
        const to = document.getElementById('year-to').value;
        const url = new URL(window.location.href);
        url.searchParams.set('year_from', from);
        url.searchParams.set('year_to', to);
        window.location.href = url.toString();
    }

    // Mini Pemasukan Chart
    const miniChartOpts = (color, data) => ({
        series: [{ data }],
        chart: { type: 'line', height: 60, sparkline: { enabled: true } },
        stroke: { curve: 'smooth', width: 3, colors: [color] },
        tooltip: { enabled: false }
    });

    const cfData = {
        harian: @json($cfHarian),
        mingguan: @json($cfMingguan),
        bulanan: @json($cfBulanan),
        tahunan: @json($cfTahunan)
    };

    const pChart = new ApexCharts(document.querySelector("#pemasukanChart"), miniChartOpts('#10b981', cfData.bulanan.p_series));
    const eChart = new ApexCharts(document.querySelector("#pengeluaranChart"), miniChartOpts('#f43f5e', cfData.bulanan.e_series));
    pChart.render();
    eChart.render();

    function updateCashFlow(preset) {
        const d = cfData[preset];
        document.getElementById('cf-total-pemasukan').innerText = 'Rp ' + (d.total_pemasukan / 1000).toFixed(0) + 'k';
        document.getElementById('cf-total-pengeluaran').innerText = 'Rp ' + (d.total_pengeluaran / 1000).toFixed(0) + 'k';
        pChart.updateSeries([{ data: d.p_series }]);
        eChart.updateSeries([{ data: d.e_series }]);
    }

    // Debt Donut Chart
    const debtOptions = {
        series: [{{ $totalPiutang }}, {{ $totalHutang }}],
        chart: { type: 'donut', height: 140 },
        labels: ['Piutang', 'Hutang'],
        colors: ['#3b82f6', '#f43f5e'],
        dataLabels: { enabled: false },
        plotOptions: {
            pie: {
                donut: {
                    size: '75%',
                    labels: {
                        show: true,
                        name: { 
                            show: true,
                            fontSize: '9px',
                            fontWeight: 700,
                            color: '#64748b',
                            offsetY: -5
                        },
                        value: {
                            show: true,
                            fontSize: '11px',
                            fontWeight: 800,
                            color: '#0f172a',
                            offsetY: 5,
                            formatter: () => '{{ number_format($totalPiutang + $totalHutang, 0, "", ".") }}'
                        },
                        total: {
                            show: true,
                            label: 'TOTAL',
                            formatter: () => '{{ number_format($totalPiutang + $totalHutang, 0, "", ".") }}',
                            fontSize: '9px',
                            fontWeight: 700,
                            color: '#64748b'
                        }
                    }
                }
            }
        },
        legend: { show: false }
    };

    new ApexCharts(document.querySelector("#debtChart"), debtOptions).render();

    function filterByStore(storeId) {
        const url = new URL(window.location.href);
        if (storeId) {
            url.searchParams.set('store_id', storeId);
        } else {
            url.searchParams.delete('store_id');
        }
        window.location.href = url.toString();
    }

    function switchStockTab(tabId, btn) {
        document.querySelectorAll('.stock-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.stock-content').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('tab-' + tabId).classList.add('active');
    }
    @endif
</script>
@endsection
