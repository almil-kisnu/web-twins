@extends('layouts.app')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    body {
        background-color: #f8fafc;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .dashboard-wrapper {
        padding: 1.5rem;
        animation: fadeIn 0.6s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Top Stats Card */
    .stat-card {
        background: #fff;
        border-radius: 16px;
        padding: 0.85rem 1rem;
        border: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
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
        border-radius: 24px;
        border: 1px solid #e2e8f0;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
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

    .welcome-section {
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1.5rem;
    }

    .filter-group {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #fff;
        padding: 8px 16px;
        border-radius: 50px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03);
    }

    .outlet-select {
        border: none;
        background: transparent;
        font-size: 0.85rem;
        font-weight: 700;
        color: #0f172a;
        cursor: pointer;
        padding-right: 20px;
        outline: none;
    }
</style>
@endpush

@section('content')
<div class="dashboard-wrapper">
    @if(Auth::user()->role === 'owner')
        <div class="welcome-section">
            <div>
                <h1 class="welcome-title" style="font-size: 1.75rem; margin-bottom: 0;">Dashboard Utama</h1>
                <p class="welcome-subtitle">Ringkasan performa bisnis TWINS.</p>
            </div>
            
            <div class="flex items-center gap-4 flex-wrap">
                <!-- Outlet Filter -->
                <div class="filter-group">
                    <iconify-icon icon="solar:shop-2-bold-duotone" style="color: #3b82f6; font-size: 1.25rem;"></iconify-icon>
                    <select class="outlet-select" onchange="filterByStore(this.value)">
                        <option value="">Semua Outlet (Pusat)</option>
                        @foreach($stores ?? [] as $store)
                            <option value="{{ $store->uuid }}" {{ ($currentStoreId ?? '') == $store->uuid ? 'selected' : '' }}>
                                {{ $store->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="date-chip" style="background: #fff; padding: 10px 20px; border-radius: 50px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 10px; font-weight: 700; color: #475569; font-size: 0.85rem;">
                    <iconify-icon icon="solar:calendar-bold-duotone" style="color: #3b82f6; font-size: 1.25rem;"></iconify-icon>
                    {{ \Carbon\Carbon::now()->isoFormat('D MMM Y') }}
                </div>
            </div>
        </div>
    @endif
    <!-- Top Row Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Total Transaksi -->
        <div class="stat-card">
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

        <!-- Pendapatan -->
        <div class="stat-card">
            <div class="stat-header">
                <div class="icon-box" style="background: #f0fdf4; color: #10b981;">
                    <iconify-icon icon="solar:wad-of-money-bold-duotone"></iconify-icon>
                </div>
                <div class="stat-label">Pendapatan</div>
            </div>
            <div class="stat-value">Rp {{ number_format($total_pendapatan / 1000, 0) }}k</div>
            <div class="stat-trend {{ $diff_pendapatan >= 0 ? 'trend-up' : 'trend-down' }}">
                <iconify-icon icon="{{ $diff_pendapatan >= 0 ? 'solar:alt-arrow-up-bold' : 'solar:alt-arrow-down-bold' }}"></iconify-icon>
                {{ abs($diff_pendapatan) }}% dari kemarin
            </div>
        </div>

        <!-- Produk Terjual -->
        <div class="stat-card">
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

        <!-- Stok Menipis -->
        <div class="stat-card">
            <div class="stat-header">
                <div class="icon-box" style="background: #fff1f2; color: #f43f5e;">
                    <iconify-icon icon="solar:shield-warning-bold-duotone"></iconify-icon>
                </div>
                <div class="stat-label">Stok Menipis</div>
            </div>
            <div class="stat-value">{{ $total_stok_menipis }}</div>
            <a href="#" class="stat-label" style="color: #3b82f6; text-decoration: underline;">Lihat detail</a>
        </div>

        <!-- Karyawan Aktif -->
        <div class="stat-card">
            <div class="stat-header">
                <div class="icon-box" style="background: #faf5ff; color: #8b5cf6;">
                    <iconify-icon icon="solar:users-group-rounded-bold-duotone"></iconify-icon>
                </div>
                <div class="stat-label">Karyawan Aktif</div>
            </div>
            <div class="stat-value">{{ $total_karyawan_aktif }}</div>
            <a href="#" class="stat-label" style="color: #3b82f6; text-decoration: underline;">Lihat detail</a>
        </div>
    </div>

    <!-- Second Row Charts -->
    <div class="main-grid">
        <!-- Main Sales Chart -->
        <div class="col-span-12 lg:col-span-8 card">
            <div class="card-header">
                <h3 class="card-title">Penjualan Hari Ini</h3>
                <select class="chart-select">
                    <option>Per Jam</option>
                    <option>Harian</option>
                </select>
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
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title" style="font-size: 0.95rem;">Pemasukan & Pengeluaran</h3>
                    <select class="chart-select">
                        <option>Bulan Ini</option>
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
                        <p class="font-extrabold text-green-600 text-sm">Rp {{ number_format($totalPemasukanMonth / 1000, 0) }}k</p>
                        <div id="pemasukanChart" style="height: 60px;"></div>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center text-red-500">
                                <iconify-icon icon="solar:alt-arrow-up-bold"></iconify-icon>
                            </div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Pengeluaran</span>
                        </div>
                        <p class="font-extrabold text-red-600 text-sm">Rp {{ number_format($totalPengeluaranMonth / 1000, 0) }}k</p>
                        <div id="pengeluaranChart" style="height: 60px;"></div>
                    </div>
                </div>
            </div>

            <!-- Hutang & Piutang -->
            <div class="card flex-1">
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
        <!-- Stok Menipis Table -->
        <div class="col-span-12 lg:col-span-4 card">
            <div class="card-header">
                <h3 class="card-title">Stok Menipis</h3>
                <a href="#" style="font-size: 0.75rem; color: #3b82f6; font-weight: 700;">Lihat semua</a>
            </div>
            <table class="custom-table">
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
                                <img src="{{ $ps->product->resolved_image_url }}" class="product-img">
                                <span class="font-bold text-xs">{{ $ps->product->nama_produk }}</span>
                            </div>
                        </td>
                        <td style="text-align: center;" class="font-extrabold">{{ $ps->stok }}</td>
                        <td style="text-align: right;">
                            <span class="status-badge {{ $ps->stok <= 2 ? 'badge-critical' : 'badge-low' }}">
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

        <!-- Produk Terlaris Table -->
        <div class="col-span-12 lg:col-span-4 card">
            <div class="card-header">
                <h3 class="card-title">Produk Terlaris</h3>
                <a href="#" style="font-size: 0.75rem; color: #3b82f6; font-weight: 700;">Lihat semua</a>
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

        <!-- Aktivitas Karyawan Feed -->
        <div class="col-span-12 lg:col-span-4 card">
            <div class="card-header">
                <h3 class="card-title">Aktivitas Karyawan</h3>
                <a href="#" style="font-size: 0.75rem; color: #3b82f6; font-weight: 700;">Lihat semua</a>
            </div>
            <div class="activity-feed">
                @forelse($activities as $act)
                <div class="activity-item">
                    <div class="activity-icon">
                        <iconify-icon icon="solar:user-circle-bold-duotone" style="color: #6366f1;"></iconify-icon>
                    </div>
                    <div class="activity-content">
                        <div class="flex justify-between items-start">
                            <span class="activity-user">{{ $act['user'] }}</span>
                            <span class="activity-time">{{ $act['time'] }}</span>
                        </div>
                        <p class="activity-text"><span class="font-bold text-[10px] uppercase text-slate-400">{{ $act['role'] }}</span> | {{ $act['action'] }}</p>
                    </div>
                </div>
                @empty
                <p class="text-center py-8 text-slate-400">Belum ada aktivitas.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    // Main Sales Chart
    const salesOptions = {
        series: [{
            name: 'Penjualan',
            data: @json(collect($chartData)->pluck('y'))
        }],
        chart: {
            height: 300,
            type: 'area',
            toolbar: { show: false },
            zoom: { enabled: false },
            fontFamily: 'Plus Jakarta Sans',
            sparkline: { enabled: false },
            padding: { top: 0, bottom: 0 }
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 4, colors: ['#3b82f6'] },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.45,
                opacityTo: 0.05,
                stops: [20, 100, 100, 100]
            }
        },
        xaxis: {
            categories: @json(collect($chartData)->pluck('x')),
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: { style: { colors: '#94a3b8', fontWeight: 600 } }
        },
        yaxis: {
            labels: {
                formatter: function (val) { return "Rp " + (val / 1000) + "k" },
                style: { colors: '#94a3b8', fontWeight: 600 }
            }
        },
        grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
        markers: { size: 4, colors: ['#3b82f6'], strokeWidth: 2, hover: { size: 7 } }
    };

    new ApexCharts(document.querySelector("#mainSalesChart"), salesOptions).render();

    // Mini Pemasukan Chart
    const miniChartOpts = (color) => ({
        series: [{ data: [10, 41, 35, 51, 49, 62, 69, 91, 148] }],
        chart: { type: 'line', height: 60, sparkline: { enabled: true } },
        stroke: { curve: 'smooth', width: 3, colors: [color] },
        tooltip: { enabled: false }
    });

    new ApexCharts(document.querySelector("#pemasukanChart"), miniChartOpts('#10b981')).render();
    new ApexCharts(document.querySelector("#pengeluaranChart"), miniChartOpts('#f43f5e')).render();

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
</script>
@endsection
