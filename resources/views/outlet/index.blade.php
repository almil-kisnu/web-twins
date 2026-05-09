@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/fitur.css') }}">

<style>
    .fitur-layout-wrapper {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        margin-top: 20px;
    }
    .main-content-box {
        flex: 1;
        min-width: 0;
        margin-top: 0 !important;
    }
    .detail-side-panel {
        width: 280px;
        background: white;
        border: 2px solid var(--border-blue);
        border-radius: 20px;
        padding: 16px;
        position: sticky;
        top: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
    }
    .detail-header {
        margin-bottom: 12px;
    }
    .detail-title {
        font-size: 12px;
        color: #64748b;
        margin-bottom: 4px;
        font-weight: 500;
    }
    .detail-store-name {
        font-size: 16px;
        font-weight: 700;
        color: var(--primary-blue);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .info-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f1f5f9;
    }
    .info-item {
        display: flex;
        gap: 12px;
    }
    .info-icon {
        width: 28px;
        height: 28px;
        background: #f0f9ff;
        color: var(--primary-blue);
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }
    .info-content label {
        display: block;
        font-size: 11px;
        color: #94a3b8;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 2px;
    }
    .info-content span {
        font-size: 13px;
        font-weight: 500;
        color: #334155;
        line-height: 1.4;
    }
    .perf-title {
        font-size: 12px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 12px;
    }
    .perf-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }
    .perf-card {
        padding: 8px;
        background: #f8fafc;
        border-radius: 8px;
        border: 1px solid #f1f5f9;
    }
    .perf-card label {
        display: block;
        font-size: 9px;
        color: #64748b;
        margin-bottom: 2px;
    }
    .perf-card .value {
        display: block;
        font-size: 12px;
        font-weight: 700;
        color: #334155;
    }
    .perf-card .sub-value {
        font-size: 9px;
        color: #10b981;
        font-weight: 600;
        margin-top: 2px;
        display: block;
    }
    .outlet-row {
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .outlet-row:hover {
        background-color: #f0f9ff !important;
    }
    .outlet-row.active-row {
        background-color: #e0f2fe !important;
    }
    .detail-side-panel .status-badge {
        font-size: 10px;
        padding: 2px 8px;
        border-radius: 6px;
    }

    /* Kinerja Styles */
    .kinerja-stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 15px;
    }
    .kpi-card {
        background: white;
        padding: 16px;
        border-radius: 16px;
        border: 2px solid var(--border-blue);
        display: flex;
        flex-direction: column;
        gap: 8px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    .kpi-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    .kpi-label {
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .kpi-value {
        font-size: 20px;
        font-weight: 800;
        color: #1e293b;
    }
    .kpi-trend {
        font-size: 10px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .trend-up { color: #10b981; }
    .trend-down { color: #ef4444; }

    .kinerja-main-grid {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 12px;
    }
    .chart-container-box {
        background: white;
        padding: 16px;
        border-radius: 16px;
        border: 2px solid var(--border-blue);
        height: 100%;
    }
    .summary-table-card {
        background: white;
        padding: 16px;
        border-radius: 16px;
        border: 2px solid var(--border-blue);
    }
    .summary-item-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 12px;
        background: #f8fafc;
        border-radius: 12px;
        border: 1px solid #f1f5f9;
        margin-bottom: 8px;
        transition: all 0.2s;
    }
    .summary-item-card:hover {
        background: white;
        border-color: var(--primary-blue);
        transform: scale(1.01);
    }
    .summary-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .summary-val {
        font-weight: 800;
        color: #1e293b;
        font-size: 14px;
    }
    .btn-search-trigger {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        padding: 7px 20px;
        background: var(--primary-blue);
        color: white;
        border: none;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        z-index: 5;
    }

    .btn-search-trigger:hover {
        opacity: 0.9;
        transform: translateY(-50%) scale(1.05);
    }

    .btn-search-trigger:active {
        transform: translateY(-50%) scale(0.95);
    }
</style>




<div class="fitur-container">
    {{-- PILL TABS --}}
    <div class="tab-navigation">
        <a href="javascript:void(0)" onclick="switchTab('data')" id="pill-data" class="tab-pill {{ $active_tab == 'data' ? 'active' : '' }}">
            <iconify-icon icon="solar:shop-bold-duotone"></iconify-icon>
            <span>Data Outlet</span>
        </a>
        <a href="javascript:void(0)" onclick="switchTab('kinerja')" id="pill-kinerja" class="tab-pill {{ $active_tab == 'kinerja' ? 'active' : '' }}">
            <iconify-icon icon="solar:chart-2-bold-duotone"></iconify-icon>
            <span>Kinerja Outlet</span>
        </a>
        <a href="javascript:void(0)" onclick="switchTab('riwayat')" id="pill-riwayat" class="tab-pill {{ $active_tab == 'riwayat' ? 'active' : '' }}">
            <iconify-icon icon="solar:history-bold-duotone"></iconify-icon>
            <span>Riwayat Stok</span>
        </a>
    </div>

    <div id="view-data" class="tab-view" style="{{ $active_tab == 'data' ? '' : 'display: none;' }}">
        {{-- ACTION BAR --}}
        <div class="action-bar">
            <div class="left-actions-group">
                <div class="search-wrapper">
                    <iconify-icon icon="solar:magnifer-linear" class="search-icon"></iconify-icon>
                    <input type="text" id="outletSearch" class="search-input" placeholder="Cari nama atau alamat..." onkeyup="filterOutlets()">
                </div>
            </div>
            <div class="right-actions">
                <button class="btn-action" onclick="openModal('addModal')">
                    <iconify-icon icon="solar:shop-bold-duotone"></iconify-icon>
                    <span>Tambah Outlet</span>
                </button>
            </div>
        </div>

        <div class="fitur-layout-wrapper">
            {{-- MAIN BOX --}}
            <div class="main-content-box">
                <div class="table-container">
                    <table class="fitur-table">
                        <thead>
                            <tr>
                                <th>NAMA OUTLET</th>
                                <th>ALAMAT</th>
                                <th>NO. TELP</th>
                                <th>JAM BUKA</th>
                                <th>STATUS</th>
                                <th>AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($outlets as $index => $outlet)
                            <tr class="outlet-row {{ $index === 0 ? 'active-row' : '' }}" 
                                data-name="{{ strtolower($outlet->nama) }}" 
                                data-address="{{ strtolower($outlet->alamat) }}"
                                data-outlet='@json($outlet)'>
                                <td style="font-weight: 600;">{{ $outlet->nama }}</td>
                                <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $outlet->alamat ?? '-' }}</td>
                                <td>{{ $outlet->notelp ?? '-' }}</td>
                                <td>
                                    <span class="status-badge" style="background: rgba(14, 165, 233, 0.1); color: var(--accent-purple); border: 1px solid rgba(14, 165, 233, 0.2);">
                                        {{ $outlet->jam_buka ?? '08.00 - 23.59' }}
                                    </span>
                                </td>
                                <td>
                                    @if($outlet->status_aktif)
                                        <span class="status-badge status-active">Aktif</span>
                                    @else
                                        <span class="status-badge status-inactive">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; gap: 4px;">
                                        <button type="button" class="btn-filter" style="width: 28px; height: 28px; border-radius: 6px; color: var(--primary-blue);" data-item='@json($outlet)' onclick="event.stopPropagation(); openEditModal(JSON.parse(this.dataset.item))" title="Edit Outlet">
                                            <iconify-icon icon="solar:pen-bold-duotone"></iconify-icon>
                                        </button>
                                        <button type="button" class="btn-filter" style="width: 28px; height: 28px; border-radius: 6px; color: {{ $outlet->status_aktif ? '#ef4444' : '#10b981' }};" onclick="event.stopPropagation(); toggleStatus('{{ $outlet->uuid }}', {{ $outlet->status_aktif ? 'true' : 'false' }})" title="{{ $outlet->status_aktif ? 'Nonaktifkan Outlet' : 'Aktifkan Outlet' }}">
                                            <iconify-icon icon="{{ $outlet->status_aktif ? 'solar:shop-2-bold-duotone' : 'solar:shop-bold-duotone' }}"></iconify-icon>
                                        </button>
                                        <button type="button" class="btn-filter" style="width: 28px; height: 28px; border-radius: 6px; color: #D9534F; border-color: #ffcccc;" onclick="event.stopPropagation(); openDeleteModal('{{ $outlet->uuid }}')" title="Hapus Outlet">
                                            <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" style="text-align: center; color: #999; padding: 40px;">Belum ada data outlet</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- DETAIL SIDE PANEL --}}
            <div class="detail-side-panel">
                <div id="sideDetailContent">
                    @if(count($outlets ?? []) > 0)
                        @php $first = $outlets[0]; @endphp
                        <div class="detail-header">
                            <div class="detail-title">Detail Outlet</div>
                            <div class="detail-store-name">
                                <span id="side_nama">{{ $first->nama }}</span>
                                <span id="side_status">
                                    @if($first->status_aktif)
                                        <span class="status-badge status-active">Aktif</span>
                                    @else
                                        <span class="status-badge status-inactive">Nonaktif</span>
                                    @endif
                                </span>
                            </div>
                        </div>

                        <div class="info-list">
                            <div class="info-item">
                                <div class="info-icon"><iconify-icon icon="solar:map-point-bold-duotone"></iconify-icon></div>
                                <div class="info-content">
                                    <label>Alamat</label>
                                    <span id="side_alamat">{{ $first->alamat ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon"><iconify-icon icon="solar:user-bold-duotone"></iconify-icon></div>
                                <div class="info-content">
                                    <label>Kepala Toko</label>
                                    <span id="side_kepala">{{ $first->users->where('role', 'kepala_toko')->first()->username ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon"><iconify-icon icon="solar:phone-bold-duotone"></iconify-icon></div>
                                <div class="info-content">
                                    <label>No. Telepon</label>
                                    <span id="side_notelp">{{ $first->notelp ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon"><iconify-icon icon="solar:letter-bold-duotone"></iconify-icon></div>
                                <div class="info-content">
                                    <label>Email</label>
                                    <span id="side_email">{{ $first->users->where('role', 'kepala_toko')->first()->email ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon"><iconify-icon icon="solar:clock-circle-bold-duotone"></iconify-icon></div>
                                <div class="info-content">
                                    <label>Jam Operasional</label>
                                    <span id="side_jam">{{ $first->jam_buka ?? '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="perf-title">Ringkasan Performa Outlet</div>
                        <div class="perf-grid">
                            <div class="perf-card">
                                <label>Omzet</label>
                                <span class="value" id="side_omzet">Rp {{ number_format(rand(10000000, 30000000), 0, ',', '.') }}</span>
                                <span class="sub-value">▲ 12.5% dari bulan lalu</span>
                            </div>
                            <div class="perf-card">
                                <label>Transaksi</label>
                                <span class="value" id="side_transaksi">{{ number_format(rand(500, 1500), 0, ',', '.') }}</span>
                                <span class="sub-value">▲ 8.3% dari bulan lalu</span>
                            </div>
                            <div class="perf-card">
                                <label>Produk Terlaris</label>
                                <span class="value" id="side_terlaris">Roti Tawar</span>
                                <span style="font-size: 10px; color: #64748b;" id="side_terlaris_qty">320 pcs terjual</span>
                            </div>
                            <div class="perf-card">
                                <label>Stok Menipis</label>
                                <span class="value" id="side_stok">12 Produk</span>
                                <a href="{{ route('products.request') }}" style="font-size: 10px; color: var(--primary-blue); text-decoration: none; font-weight: 600;">Lihat Detail ></a>
                            </div>
                        </div>
                    @else
                        <div style="text-align: center; color: #94a3b8; padding: 40px 0;">
                            <iconify-icon icon="solar:shop-linear" style="font-size: 48px; margin-bottom: 12px;"></iconify-icon>
                            <p>Pilih outlet untuk melihat detail</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- VIEW KINERJA --}}
    <div id="view-kinerja" class="tab-view" style="{{ $active_tab == 'kinerja' ? '' : 'display: none;' }}">
        <div class="kinerja-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <div>
                <h2 style="font-size: 16px; font-weight: 800; color: #1e293b; margin: 0;">Analisis Kinerja Cabang</h2>
                <p style="font-size: 11px; color: #64748b; margin-top: 2px;">Pantau performa finansial seluruh outlet.</p>
            </div>
            <div class="dropdown">
                <select id="kinerjaOutletSelector" class="form-control" style="border-radius: 10px; min-width: 180px; height: 38px; border: 2px solid var(--border-blue); font-weight: 600; font-size: 13px;" onchange="updateKinerjaData(this.value)">
                    <option value="all">Semua Outlet (Agregat)</option>
                    @foreach($outlets as $outlet)
                        <option value="{{ $outlet->uuid }}">{{ $outlet->nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="kinerja-stats-grid">
            <div class="kpi-card">
                <div class="kpi-icon" style="background: #e0f2fe; color: #0ea5e9;">
                    <iconify-icon icon="solar:banknote-bold-duotone"></iconify-icon>
                </div>
                <div class="kpi-label">Omset Penjualan</div>
                <div class="kpi-value" id="kpi-omset">Rp 0</div>
                <div class="kpi-trend trend-up">
                    <iconify-icon icon="solar:round-alt-arrow-up-bold-duotone"></iconify-icon>
                    <span>Terakumulasi</span>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon" style="background: #fef9c3; color: #ca8a04;">
                    <iconify-icon icon="solar:box-bold-duotone"></iconify-icon>
                </div>
                <div class="kpi-label">Laba Kotor</div>
                <div class="kpi-value" id="kpi-laba-kotor">Rp 0</div>
                <div class="kpi-trend trend-up">
                    <iconify-icon icon="solar:round-alt-arrow-up-bold-duotone"></iconify-icon>
                    <span>Estimasi HPP</span>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon" style="background: #dcfce7; color: #16a34a;">
                    <iconify-icon icon="solar:graph-up-bold-duotone"></iconify-icon>
                </div>
                <div class="kpi-label">Laba Bersih</div>
                <div class="kpi-value" id="kpi-laba-bersih">Rp 0</div>
                <div class="kpi-trend trend-up">
                    <iconify-icon icon="solar:round-alt-arrow-up-bold-duotone"></iconify-icon>
                    <span>Setelah Biaya</span>
                </div>
            </div>
        </div>

        <div class="kinerja-main-grid">
            <div class="chart-container-box">
                <h3 style="font-size: 13px; font-weight: 800; color: #1e293b; margin-bottom: 15px;">Komposisi Arus Kas</h3>
                <div style="height: 180px; position: relative;">
                    <canvas id="kinerjaDonutChart"></canvas>
                </div>
                <div style="margin-top: 15px; display: grid; gap: 8px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: #f0fdf4; border-radius: 10px; border: 1px solid #dcfce7;">
                        <span style="font-size: 11px; font-weight: 700; color: #16a34a;">Pemasukan</span>
                        <span style="font-weight: 800; color: #16a34a; font-size: 13px;" id="stat-pemasukan">Rp 0</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: #fef2f2; border-radius: 10px; border: 1px solid #fee2e2;">
                        <span style="font-size: 11px; font-weight: 700; color: #ef4444;">Pengeluaran</span>
                        <span style="font-weight: 800; color: #ef4444; font-size: 13px;" id="stat-pengeluaran">Rp 0</span>
                    </div>
                </div>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <div class="summary-table-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <h3 style="font-size: 13px; font-weight: 800; color: #1e293b; margin: 0;">Ringkasan Finansial</h3>
                        <span style="font-size: 10px; font-weight: 700; color: #64748b; background: #f1f5f9; padding: 2px 8px; border-radius: 50px;">Bulan Ini</span>
                    </div>
                    
                    <div class="summary-item-card">
                        <div class="summary-info">
                            <div style="width: 32px; height: 32px; border-radius: 8px; background: #f0f9ff; color: #0ea5e9; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                                <iconify-icon icon="solar:banknote-linear"></iconify-icon>
                            </div>
                            <div>
                                <div style="font-size: 10px; color: #64748b; font-weight: 600; text-transform: uppercase;">Omset</div>
                                <div class="summary-val" id="row-omset">Rp 0</div>
                            </div>
                        </div>
                    </div>

                    <div class="summary-item-card">
                        <div class="summary-info">
                            <div style="width: 32px; height: 32px; border-radius: 8px; background: #fefce8; color: #ca8a04; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                                <iconify-icon icon="solar:box-linear"></iconify-icon>
                            </div>
                            <div>
                                <div style="font-size: 10px; color: #64748b; font-weight: 600; text-transform: uppercase;">Laba Kotor</div>
                                <div class="summary-val" id="row-laba-kotor">Rp 0</div>
                            </div>
                        </div>
                    </div>

                    <div class="summary-item-card">
                        <div class="summary-info">
                            <div style="width: 32px; height: 32px; border-radius: 8px; background: #f0fdf4; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                                <iconify-icon icon="solar:chart-square-linear"></iconify-icon>
                            </div>
                            <div>
                                <div style="font-size: 10px; color: #64748b; font-weight: 600; text-transform: uppercase;">Pemasukan</div>
                                <div class="summary-val" id="row-pemasukan">Rp 0</div>
                            </div>
                        </div>
                    </div>

                    <div class="summary-item-card" style="margin-bottom: 0;">
                        <div class="summary-info">
                            <div style="width: 32px; height: 32px; border-radius: 8px; background: #fef2f2; color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                                <iconify-icon icon="solar:card-2-linear"></iconify-icon>
                            </div>
                            <div>
                                <div style="font-size: 10px; color: #64748b; font-weight: 600; text-transform: uppercase;">Biaya</div>
                                <div class="summary-val" id="row-pengeluaran">Rp 0</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="summary-table-card">
                    <h3 style="font-size: 13px; font-weight: 800; color: #1e293b; margin-bottom: 15px;">Top 3 Produk Terlaris</h3>
                    <div id="top-products-container" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                        @foreach($topProductsAll as $index => $prod)
                        <div onclick="showProductDetails({{ json_encode($prod) }})" style="padding: 12px 8px; background: #f8fafc; border-radius: 16px; border: 1px solid #f1f5f9; display: flex; flex-direction: column; align-items: center; gap: 8px; cursor: pointer; transition: all 0.3s; text-align: center; position: relative;" onmouseover="this.style.borderColor='var(--primary-blue)';this.style.background='white';this.style.transform='translateY(-2px)';this.style.boxShadow='0 10px 20px rgba(0,0,0,0.05)'" onmouseout="this.style.borderColor='#f1f5f9';this.style.background='#f8fafc';this.style.transform='translateY(0)';this.style.boxShadow='none'">
                            <div style="position: absolute; top: -6px; left: -6px; background: var(--primary-blue); color: white; width: 22px; height: 22px; border-radius: 50%; font-size: 10px; font-weight: 800; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">{{ $index + 1 }}</div>
                            <img src="{{ $prod['image'] }}" style="width: 50px; height: 50px; border-radius: 12px; object-fit: cover; border: 1px solid #e2e8f0; background: white;">
                            <div style="width: 100%;">
                                <div style="font-size: 10px; font-weight: 800; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 2px;">{{ $prod['nama'] }}</div>
                                <div style="font-size: 9px; color: #16a34a; font-weight: 700; background: #f0fdf4; padding: 2px 6px; border-radius: 50px; display: inline-block;">{{ $prod['qty'] }} Terjual</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>


            </div>
        </div>
    </div>

    {{-- MODAL DETAIL PRODUK --}}
    <div id="productDetailModal" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
        <div class="modal-content" style="background: white; width: 320px; border-radius: 24px; padding: 24px; position: relative; border: 2px solid var(--border-blue); box-shadow: 0 20px 50px rgba(0,0,0,0.1);">
            <button onclick="closeProductDetails()" style="position: absolute; top: 16px; right: 16px; background: #f1f5f9; border: none; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #64748b;">
                <iconify-icon icon="solar:close-circle-bold"></iconify-icon>
            </button>
            <div style="text-align: center;">
                <img id="modal-product-img" src="" style="width: 120px; height: 120px; border-radius: 20px; object-fit: cover; margin-bottom: 16px; border: 3px solid #f8fafc; box-shadow: 0 8px 16px rgba(0,0,0,0.05);">
                <h3 id="modal-product-name" style="font-size: 18px; font-weight: 800; color: #1e293b; margin-bottom: 4px;">Nama Produk</h3>
                <span style="font-size: 12px; font-weight: 600; color: #64748b; background: #f1f5f9; padding: 4px 12px; border-radius: 50px;">Produk Terlaris</span>
                
                <div style="margin-top: 24px; display: grid; gap: 12px;">
                    <div style="background: #f0fdf4; padding: 12px; border-radius: 16px; border: 1px solid #dcfce7;">
                        <div style="font-size: 11px; font-weight: 700; color: #16a34a; text-transform: uppercase;">Total Terjual</div>
                        <div id="modal-product-qty" style="font-size: 24px; font-weight: 800; color: #16a34a;">0</div>
                        <div style="font-size: 10px; color: #16a34a; opacity: 0.8;">Bulan ini</div>
                    </div>
                </div>
                
                <button onclick="closeProductDetails()" class="btn-action" style="width: 100%; margin-top: 20px; justify-content: center;">
                    Tutup Detail
                </button>
            </div>
        </div>
    </div>


    {{-- VIEW RIWAYAT --}}
    <div id="view-riwayat" class="tab-view" style="{{ $active_tab == 'riwayat' ? '' : 'display: none;' }}">
        <!-- Header Section (Standard Layout) -->
        <div class="action-bar">
            <div class="left-actions-group">
                <!-- Search Box (Standard) -->
                <div class="search-wrapper">
                    <iconify-icon icon="solar:magnifer-linear" class="search-icon"></iconify-icon>
                    <input type="text" id="stock-search" class="search-input" oninput="debounceSearch()" placeholder="Cari produk atau barcode...">
                    <button type="button" class="btn-search-trigger" onclick="applyStockFilters()">
                        Cari
                    </button>
                </div>

                <!-- Outlet Filter -->
                <div class="dropdown">
                    <button type="button" class="btn-filter" title="Filter Outlet" onclick="toggleDropdown(event)">
                        <iconify-icon icon="solar:shop-bold-duotone" style="font-size: 24px;"></iconify-icon>
                    </button>
                    <div class="dropdown-content">
                        <a href="javascript:void(0)" onclick="setStockOutlet('all')" class="active-dropdown-item">Semua Outlet</a>
                        @foreach($outlets as $outlet)
                            <a href="javascript:void(0)" onclick="setStockOutlet('{{ $outlet->uuid }}')">{{ $outlet->nama }}</a>
                        @endforeach
                    </div>
                    <input type="hidden" id="stock-outlet-filter" value="all">
                </div>

                <!-- Date Range Filter -->
                <div class="dropdown">
                    <button type="button" class="btn-filter" title="Filter Rentang Waktu" onclick="toggleDropdown(event)">
                        <iconify-icon icon="solar:calendar-bold-duotone" style="font-size: 24px;"></iconify-icon>
                    </button>
                    <div class="dropdown-content" style="padding: 20px; width: 300px; left: 0; right: auto;">
                        <div style="font-size: 12px; font-weight: 800; color: var(--primary-blue); margin-bottom: 15px; text-transform: uppercase; letter-spacing: 0.5px;">Rentang Waktu</div>
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label style="font-size: 11px; font-weight: 700; color: #64748b; margin-bottom: 5px; display: block;">DARI TANGGAL</label>
                            <input type="date" id="stock-start-date" class="form-control" onchange="applyStockFilters()">
                        </div>
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label style="font-size: 11px; font-weight: 700; color: #64748b; margin-bottom: 5px; display: block;">SAMPAI TANGGAL</label>
                            <input type="date" id="stock-end-date" class="form-control" onchange="applyStockFilters()">
                        </div>
                        <button type="button" class="btn-action" style="width: 100%; justify-content: center;" onclick="applyStockFilters()">
                            <iconify-icon icon="solar:check-circle-bold-duotone"></iconify-icon>
                            Terapkan Filter
                        </button>
                    </div>
                </div>

                <!-- Reset -->
                <button onclick="resetStockFilters()" class="btn-filter" title="Reset Filter">
                    <iconify-icon icon="solar:restart-bold-duotone" style="font-size: 24px;"></iconify-icon>
                </button>
            </div>

            <div class="right-actions">
                <div class="dropdown">
                    <button type="button" class="btn-action dropdown-toggle" onclick="toggleDropdown(event)">
                        <iconify-icon icon="solar:document-text-bold-duotone"></iconify-icon>
                        <span>Extract</span>
                    </button>
                    <div class="dropdown-content" style="right: 0; left: auto;">
                        <a href="javascript:void(0)" onclick="exportStock('excel')">
                            <iconify-icon icon="vscode-icons:file-type-excel" style="margin-right: 8px;"></iconify-icon>
                            Excel (.xlsx)
                        </a>
                        <a href="javascript:void(0)" onclick="exportStock('pdf')">
                            <iconify-icon icon="vscode-icons:file-type-pdf" style="margin-right: 8px;"></iconify-icon>
                            PDF Document
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content (Standard Layout) -->
        <div class="main-content-box">
            <div class="table-container">
                <table class="fitur-table">
                    <thead>
                        <tr>
                            <th style="width: 150px;">WAKTU</th>
                            <th>OUTLET</th>
                            <th>PRODUK</th>
                            <th style="width: 100px; text-align: center;">MUTASI</th>
                            <th>KETERANGAN</th>
                        </tr>
                    </thead>
                    <tbody id="stock-history-table-body">
                        @fragment('stock-history-table')
                        @foreach($stockHistory as $item)
                        <tr class="stock-row" data-outlet="{{ $item->store_id }}" data-date="{{ $item->created_at->format('Y-m-d') }}">
                            <td style="color: #64748b; font-size: 12px; white-space: nowrap;">
                                {{ $item->created_at->format('d/m/Y') }}
                                <div style="font-size: 10px; opacity: 0.7;">{{ $item->created_at->format('H:i:s') }}</div>
                            </td>
                            <td style="font-weight: 700; color: var(--primary-blue);">
                                {{ $item->store->nama ?? '-' }}
                            </td>
                            <td>
                                <div class="product-info">
                                    <img src="{{ $item->product->resolved_image_url ?? asset('images/placeholder-product.png') }}" class="product-img">
                                    <div>
                                        <div style="font-weight: 700; color: #1e293b;">{{ $item->product->nama_produk ?? '-' }}</div>
                                        <div style="font-size: 11px; color: #94a3b8; font-family: monospace;">{{ $item->product->barcode ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <div style="font-size: 16px; font-weight: 900; color: {{ $item->jmlh > 0 ? '#16a34a' : '#ef4444' }}; background: {{ $item->jmlh > 0 ? '#f0fdf4' : '#fef2f2' }}; padding: 8px; border-radius: 10px; border: 1px solid {{ $item->jmlh > 0 ? '#dcfce7' : '#fee2e2' }};">
                                    {{ $item->jmlh > 0 ? '+' : '' }}{{ $item->jmlh }}
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    @php
                                        $icon = 'solar:info-circle-bold-duotone';
                                        $color = '#5ab2e8';
                                        $label = strtolower($item->keterangan);
                                        if(str_contains($label, 'penjualan')) { $icon = 'solar:cart-large-minimalistic-bold-duotone'; $color = '#3b82f6'; }
                                        elseif(str_contains($label, 'restock')) { $icon = 'solar:box-bold-duotone'; $color = '#10b981'; }
                                        elseif(str_contains($label, 'opname')) { $icon = 'solar:clipboard-check-bold-duotone'; $color = '#f59e0b'; }
                                        elseif(str_contains($label, 'transfer')) { $icon = 'solar:transfer-horizontal-bold-duotone'; $color = '#8b5cf6'; }
                                    @endphp
                                    <iconify-icon icon="{{ $icon }}" style="font-size: 20px; color: {{ $color }};"></iconify-icon>
                                    <span style="font-weight: 600; color: #475569;">{{ $item->keterangan }}</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        <tr>
                            <td colspan="5">
                                <div class="pagination-container">
                                    {{ $stockHistory->links() }}
                                </div>
                            </td>
                        </tr>
                        @endfragment
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</div>

<!-- Modal Tambah -->
<div id="addModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Tambah Outlet Baru</h3>
            <button class="close-modal" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form action="{{ route('outlet.store') }}" method="POST">
            @csrf
            <div class="modal-body" style="padding: 20px;">
                <div class="form-group">
                    <label>Nama Outlet</label>
                    <input type="text" name="nama" class="form-control" placeholder="Contoh: TWINS Bakery Pusat" required>
                </div>
                <div class="form-group">
                    <label>Alamat Lengkap</label>
                    <textarea name="alamat" class="form-control" rows="3" placeholder="Jl. Raya No. 123..."></textarea>
                </div>
                <div class="form-group">
                    <label>Nomor Telepon</label>
                    <input type="text" name="notelp" class="form-control" placeholder="08123456789">
                </div>
                <div class="form-group">
                    <label>Jam Operasional</label>
                    <input type="text" name="jam_buka" class="form-control" placeholder="Contoh: 08.00 - 22.00" value="08.00 - 23.59">
                </div>
            </div>
            <div style="padding: 0 20px 20px; display: flex; gap: 10px;">
                <button type="button" class="btn-action btn-danger" style="flex: 1; justify-content: center;" onclick="closeModal('addModal')">Batal</button>
                <button type="submit" class="btn-action" style="flex: 1; justify-content: center;">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="editModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Edit Outlet</h3>
            <button class="close-modal" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body" style="padding: 20px;">
                <div class="form-group">
                    <label>Nama Outlet</label>
                    <input type="text" name="nama" id="edit_nama" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Alamat Lengkap</label>
                    <textarea name="alamat" id="edit_alamat" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Nomor Telepon</label>
                    <input type="text" name="notelp" id="edit_notelp" class="form-control">
                </div>
                <div class="form-group">
                    <label>Jam Operasional</label>
                    <input type="text" name="jam_buka" id="edit_jam_buka" class="form-control">
                </div>
            </div>
            <div style="padding: 0 20px 20px; display: flex; gap: 10px;">
                <button type="button" class="btn-action btn-danger" style="flex: 1; justify-content: center;" onclick="closeModal('editModal')">Batal</button>
                <button type="submit" class="btn-action" style="flex: 1; justify-content: center;">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal View -->
<div id="viewModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Detail Outlet</h3>
            <button class="close-modal" onclick="closeModal('viewModal')">&times;</button>
        </div>
        <div class="modal-body" style="padding: 20px;">
            <div style="margin-bottom: 15px;">
                <label style="font-size: 12px; color: #888;">NAMA OUTLET</label>
                <div id="view_nama" style="font-weight: 600; color: #334155; font-size: 16px;">-</div>
            </div>
            <div style="margin-bottom: 15px;">
                <label style="font-size: 12px; color: #888;">ALAMAT</label>
                <div id="view_alamat" style="font-weight: 500; color: #334155;">-</div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="font-size: 12px; color: #888;">NO. TELP</label>
                    <div id="view_notelp" style="font-weight: 600; color: #334155;">-</div>
                </div>
                <div>
                    <label style="font-size: 12px; color: #888;">JAM OPERASIONAL</label>
                    <div id="view_jam_buka" style="font-weight: 600; color: #334155;">-</div>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label style="font-size: 12px; color: #888;">RATING</label>
                    <div id="view_rating" style="font-weight: 700; color: #f59e0b; display: flex; align-items: center; gap: 4px;">
                        <iconify-icon icon="solar:star-bold"></iconify-icon>
                        <span>-</span>
                    </div>
                </div>
                <div>
                    <label style="font-size: 12px; color: #888;">STATUS</label>
                    <div id="view_status">-</div>
                </div>
            </div>
        </div>
        <div style="padding: 0 20px 20px; display: flex; justify-content: flex-end;">
            <button type="button" class="btn-action" style="padding: 10px 24px;" onclick="closeModal('viewModal')">Tutup</button>
        </div>
    </div>
</div>

<script>
    function openModal(id) { document.getElementById(id).style.display = 'flex'; }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }

    let currentTab = '{{ $active_tab }}';

    function switchTab(tabId) {
        currentTab = tabId;
        
        // Reset pills
        document.querySelectorAll('.tab-pill').forEach(b => b.classList.remove('active'));
        let activePill = document.getElementById('pill-' + tabId);
        if(activePill) activePill.classList.add('active');
        
        // Hide all views
        document.querySelectorAll('.tab-view').forEach(v => v.style.display = 'none');
        
        // Show active view
        let viewObj = document.getElementById('view-' + tabId);
        if(viewObj) viewObj.style.display = 'block';

        // Update URL without reload (Optional, for better UX)
        const url = new URL(window.location);
        url.searchParams.set('active_tab', tabId);
        window.history.pushState({}, '', url);
    }

    function selectOutlet(row, data) {
        // Remove active class from all rows
        document.querySelectorAll('.outlet-row').forEach(r => r.classList.remove('active-row'));
        // Add active class to clicked row
        row.classList.add('active-row');

        // Update side panel info
        document.getElementById('side_nama').innerText = data.nama;
        document.getElementById('side_alamat').innerText = data.alamat || '-';
        document.getElementById('side_notelp').innerText = data.notelp || '-';
        document.getElementById('side_jam').innerText = data.jam_buka || '-';
        
        // Update status badge in side panel
        const statusEl = document.getElementById('side_status');
        if (data.status_aktif) {
            statusEl.innerHTML = '<span class="status-badge status-active">Aktif</span>';
        } else {
            statusEl.innerHTML = '<span class="status-badge status-inactive">Nonaktif</span>';
        }

        // Get kepala toko from data
        const sideKepala = document.getElementById('side_kepala');
        const sideEmail = document.getElementById('side_email');
        
        if (data.users && data.users.length > 0) {
            const head = data.users.find(u => u.operator && (u.operator.nama === 'Kepala Toko' || u.operator.nama === 'kepala_toko')) || data.users[0];
            sideKepala.innerText = head.username || head.name || '-';
            sideEmail.innerText = head.email || '-';
        } else {
            sideKepala.innerText = '-';
            sideEmail.innerText = '-';
        }

        // Mock updates for performance (Since this is layout focus)
        // In real app, you might fetch this via AJAX
        const mockOmzet = Math.floor(Math.random() * (30000000 - 10000000 + 1) + 10000000);
        const mockTransaksi = Math.floor(Math.random() * (1500 - 500 + 1) + 500);
        const mockStok = Math.floor(Math.random() * 20);
        
        document.getElementById('side_omzet').innerText = 'Rp ' + mockOmzet.toLocaleString('id-ID');
        document.getElementById('side_transaksi').innerText = mockTransaksi.toLocaleString('id-ID');
        document.getElementById('side_stok').innerText = mockStok + ' Produk';
    }

    function openViewModal(data) {
        document.getElementById('view_nama').innerText = data.nama;
        document.getElementById('view_alamat').innerText = data.alamat || '-';
        document.getElementById('view_notelp').innerText = data.notelp || '-';
        document.getElementById('view_jam_buka').innerText = data.jam_buka || '-';
        document.getElementById('view_rating').querySelector('span').innerText = parseFloat(data.rating || 0).toFixed(1);
        
        const statusEl = document.getElementById('view_status');
        if (data.status_aktif) {
            statusEl.innerHTML = '<span class="status-badge status-active">Aktif</span>';
        } else {
            statusEl.innerHTML = '<span class="status-badge status-inactive">Nonaktif</span>';
        }
        
        openModal('viewModal');
    }

    function openEditModal(data) {
        document.getElementById('editForm').action = `/outlet/${data.uuid}`;
        document.getElementById('edit_nama').value = data.nama;
        document.getElementById('edit_alamat').value = data.alamat || '';
        document.getElementById('edit_notelp').value = data.notelp || '';
        document.getElementById('edit_jam_buka').value = data.jam_buka || '';
        openModal('editModal');
    }

    function toggleStatus(id, isAktif) {
        const action = isAktif ? 'Nonaktifkan' : 'Aktifkan';
        Swal.fire({
            title: `${action} Outlet?`,
            text: `Apakah Anda yakin ingin ${action.toLowerCase()} outlet ini?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: isAktif ? '#ef4444' : '#10b981',
            cancelButtonColor: '#64748b',
            confirmButtonText: `Ya, ${action}!`,
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/outlet/${id}/toggle-status`;
                form.innerHTML = `@csrf`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function openDeleteModal(id) {
        Swal.fire({
            title: 'Hapus Outlet?',
            text: "Data outlet dan relasi terkait akan dihapus secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/outlet/${id}`;
                form.innerHTML = `@csrf @method('DELETE')`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function filterOutlets() {
        const search = document.getElementById('outletSearch').value.toLowerCase();
        const rows = document.querySelectorAll('.outlet-row');
        
        rows.forEach(row => {
            const name = row.dataset.name;
            const address = row.dataset.address;
            if (name.includes(search) || address.includes(search)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Handle Row Clicks
        document.querySelectorAll('.outlet-row').forEach(row => {
            row.addEventListener('click', function() {
                const data = JSON.parse(this.dataset.outlet);
                selectOutlet(this, data);
            });
        });

        @if(session('success'))
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: "{{ session('success') }}", timer: 3000, showConfirmButton: false });
        @endif
        @if(session('error'))
            Swal.fire({ icon: 'error', title: 'Oops...', text: "{{ session('error') }}" });
        @endif
    });
</script>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const performanceData = @json($performanceData);
    let performanceChart = null;

    function animateValue(obj, start, end, duration) {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            const currentVal = Math.floor(progress * (end - start) + start);
            obj.innerHTML = 'Rp ' + currentVal.toLocaleString('id-ID');
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }

    function updateKinerjaData(outletUuid) {
        let data;
        if (outletUuid === 'all') {
            data = {
                omset: performanceData.reduce((acc, curr) => acc + curr.omset, 0),
                laba_kotor: performanceData.reduce((acc, curr) => acc + curr.laba_kotor, 0),
                laba_bersih: performanceData.reduce((acc, curr) => acc + curr.laba_bersih, 0),
                pemasukan: performanceData.reduce((acc, curr) => acc + curr.pemasukan, 0),
                pengeluaran: performanceData.reduce((acc, curr) => acc + curr.pengeluaran, 0),
                top_products: @json($topProductsAll)
            };
        } else {
            data = performanceData.find(d => d.outlet_uuid === outletUuid);
        }

        if (!data) return;

        // Animate Stat Cards
        animateValue(document.getElementById('kpi-omset'), 0, data.omset, 1000);
        animateValue(document.getElementById('kpi-laba-kotor'), 0, data.laba_kotor, 1000);
        animateValue(document.getElementById('kpi-laba-bersih'), 0, data.laba_bersih, 1000);

        // Update Summary Cards
        document.getElementById('row-omset').innerText = 'Rp ' + data.omset.toLocaleString('id-ID');
        document.getElementById('row-laba-kotor').innerText = 'Rp ' + data.laba_kotor.toLocaleString('id-ID');
        document.getElementById('row-pemasukan').innerText = 'Rp ' + data.pemasukan.toLocaleString('id-ID');
        document.getElementById('row-pengeluaran').innerText = 'Rp ' + data.pengeluaran.toLocaleString('id-ID');
        
        document.getElementById('stat-pemasukan').innerText = 'Rp ' + data.pemasukan.toLocaleString('id-ID');
        document.getElementById('stat-pengeluaran').innerText = 'Rp ' + data.pengeluaran.toLocaleString('id-ID');

        // Extra Info
        renderTopProducts(data.top_products);

        // Update Chart
        updateChart(data.pemasukan, data.pengeluaran);
    }

    function renderTopProducts(products) {
        const container = document.getElementById('top-products-container');
        container.innerHTML = '';
        
        if (!products || products.length === 0) {
            container.innerHTML = '<div style="grid-column: span 3; text-align:center; padding: 20px; color: #64748b; font-size: 11px;">Tidak ada data produk</div>';
            return;
        }

        products.forEach((prod, index) => {
            const item = document.createElement('div');
            item.onclick = () => showProductDetails(prod);
            item.style = "padding: 12px 8px; background: #f8fafc; border-radius: 16px; border: 1px solid #f1f5f9; display: flex; flex-direction: column; align-items: center; gap: 8px; cursor: pointer; transition: all 0.3s; text-align: center; position: relative;";
            
            // Hover effect via JS
            item.onmouseover = () => { 
                item.style.borderColor='var(--primary-blue)'; 
                item.style.background='white';
                item.style.transform='translateY(-2px)';
                item.style.boxShadow='0 10px 20px rgba(0,0,0,0.05)';
            };
            item.onmouseout = () => { 
                item.style.borderColor='#f1f5f9'; 
                item.style.background='#f8fafc';
                item.style.transform='translateY(0)';
                item.style.boxShadow='none';
            };

            item.innerHTML = `
                <div style="position: absolute; top: -6px; left: -6px; background: var(--primary-blue); color: white; width: 22px; height: 22px; border-radius: 50%; font-size: 10px; font-weight: 800; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">${index + 1}</div>
                <img src="${prod.image}" style="width: 50px; height: 50px; border-radius: 12px; object-fit: cover; border: 1px solid #e2e8f0; background: white;">
                <div style="width: 100%;">
                    <div style="font-size: 10px; font-weight: 800; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 2px;">${prod.nama}</div>
                    <div style="font-size: 9px; color: #16a34a; font-weight: 700; background: #f0fdf4; padding: 2px 6px; border-radius: 50px; display: inline-block;">${prod.qty} Terjual</div>
                </div>
            `;
            container.appendChild(item);
        });
    }


    function showProductDetails(prod) {
        if (!prod || prod.nama === 'N/A') return;
        
        document.getElementById('modal-product-name').innerText = prod.nama;
        document.getElementById('modal-product-img').src = prod.image;
        document.getElementById('modal-product-qty').innerText = prod.qty.toLocaleString('id-ID') + ' pcs';
        
        document.getElementById('productDetailModal').style.display = 'flex';
    }

    function closeProductDetails() {
        document.getElementById('productDetailModal').style.display = 'none';
    }

    function updateChart(inflow, outflow) {
        const ctx = document.getElementById('kinerjaDonutChart').getContext('2d');
        
        if (performanceChart) {
            performanceChart.destroy();
        }

        // Handle case where both are zero to show a "no data" ring
        const labels = ['Pemasukan', 'Pengeluaran'];
        const chartData = [inflow, outflow];
        const colors = ['#10b981', '#ef4444'];

        if (inflow === 0 && outflow === 0) {
            chartData.push(1);
            colors.push('#f1f5f9');
            labels.push('Belum Ada Data');
        }

        performanceChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: chartData,
                    backgroundColor: colors,
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: inflow > 0 || outflow > 0,
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) label += ': ';
                                if (context.parsed !== null) {
                                    label += 'Rp ' + context.parsed.toLocaleString('id-ID');
                                }
                                return label;
                            }
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    animateScale: true
                }
            }
        });
    }

    function toggleDropdown(event) {
        event.stopPropagation();
        const dropdown = event.currentTarget.nextElementSibling;
        const isOpen = dropdown.classList.contains('show');
        
        // Close all other dropdowns
        document.querySelectorAll('.dropdown-content').forEach(d => d.classList.remove('show'));
        
        if (!isOpen) {
            dropdown.classList.add('show');
        }
    }

    // Close dropdowns when clicking outside
    window.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-content').forEach(d => d.classList.remove('show'));
    });

    function setStockOutlet(uuid) {
        document.getElementById('stock-outlet-filter').value = uuid;
        
        // Update active class in dropdown
        const items = document.querySelectorAll('#view-riwayat .dropdown-content a');
        items.forEach(item => {
            if (item.getAttribute('onclick') && item.getAttribute('onclick').includes(uuid)) {
                item.classList.add('active-dropdown-item');
            } else {
                item.classList.remove('active-dropdown-item');
            }
        });

        applyStockFilters();
    }

    let searchTimeout;
    function debounceSearch() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            applyStockFilters();
        }, 500);
    }

    function applyStockFilters() {
        const search = document.getElementById('stock-search').value;
        const outlet = document.getElementById('stock-outlet-filter').value;
        const startDate = document.getElementById('stock-start-date').value;
        const endDate = document.getElementById('stock-end-date').value;

        // Build query params
        const params = new URLSearchParams();
        params.append('active_tab', 'riwayat');
        if (search) params.append('search', search);
        if (outlet && outlet !== 'all') params.append('store_id', outlet);
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);

        const tableBody = document.getElementById('stock-history-table-body');
        
        // Visual feedback (dimming)
        tableBody.style.opacity = '0.5';
        tableBody.style.pointerEvents = 'none';

        fetch('{{ route("outlet.index") }}?' + params.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            tableBody.innerHTML = html;
            tableBody.style.opacity = '1';
            tableBody.style.pointerEvents = 'auto';
        })
        .catch(err => {
            console.error('Search failed:', err);
            tableBody.style.opacity = '1';
            tableBody.style.pointerEvents = 'auto';
        });
    }

    function resetStockFilters() {
        document.getElementById('stock-search').value = '';
        document.getElementById('stock-outlet-filter').value = 'all';
        document.getElementById('stock-start-date').value = '';
        document.getElementById('stock-end-date').value = '';
        
        // Reset active state in outlet dropdown
        const items = document.querySelectorAll('#view-riwayat .dropdown-content a');
        items.forEach(item => {
            if (item.getAttribute('onclick') && item.getAttribute('onclick').includes('all')) {
                item.classList.add('active-dropdown-item');
            } else {
                item.classList.remove('active-dropdown-item');
            }
        });

        applyStockFilters();
    }

    function exportStock(format) {
        const search = document.getElementById('stock-search').value;
        const outlet = document.getElementById('stock-outlet-filter').value;
        const startDate = document.getElementById('stock-start-date').value;
        const endDate = document.getElementById('stock-end-date').value;

        let url = format === 'excel' ? '{{ route("products.export.excel") }}' : '{{ route("products.export.pdf") }}';
        
        // Build query params
        const params = new URLSearchParams();
        params.append('active_tab', 'riwayat');
        if (search) params.append('search', search);
        if (outlet && outlet !== 'all') params.append('store_id', outlet);
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);

        window.open(url + '?' + params.toString(), '_blank');
    }

    // AJAX Pagination handler
    document.addEventListener('click', function(e) {
        const paginationLink = e.target.closest('#stock-history-table-body .pagination a');
        if (paginationLink) {
            e.preventDefault();
            const url = paginationLink.href;
            const tableBody = document.getElementById('stock-history-table-body');
            
            tableBody.style.opacity = '0.5';
            tableBody.style.pointerEvents = 'none';

            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.text())
            .then(html => {
                tableBody.innerHTML = html;
                tableBody.style.opacity = '1';
                tableBody.style.pointerEvents = 'auto';
                // Scroll to top of table
                document.querySelector('.main-content-box').scrollTop = 0;
            })
            .catch(err => {
                console.error('Pagination load failed:', err);
                tableBody.style.opacity = '1';
                tableBody.style.pointerEvents = 'auto';
            });
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Initial data load
        if ('{{ $active_tab }}' === 'kinerja') {
            updateKinerjaData('all');
        }
        
        // Tab observer to trigger animation when switching to kinerja
        const originalSwitchTab = window.switchTab;
        window.switchTab = function(tabId) {
            originalSwitchTab(tabId);
            if (tabId === 'kinerja') {
                setTimeout(() => updateKinerjaData(document.getElementById('kinerjaOutletSelector').value), 100);
            }
        };
    });
</script>
@endpush
@endsection

