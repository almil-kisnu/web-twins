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
        <div class="main-content-box" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 60px 20px;">
            <div style="width: 80px; height: 80px; background: var(--light-blue); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; color: var(--primary-blue); font-size: 40px;">
                <iconify-icon icon="solar:chart-2-bold-duotone"></iconify-icon>
            </div>
            <h3 style="color: #334155; margin-bottom: 8px;">Fitur Kinerja Outlet Sedang Disiapkan</h3>
            <p style="color: #64748b; text-align: center; max-width: 400px;">Halaman untuk analisis omzet dan performa cabang akan segera hadir.</p>
            <button onclick="switchTab('data')" class="btn-action" style="margin-top: 24px;">
                <iconify-icon icon="solar:arrow-left-bold-duotone"></iconify-icon>
                Kembali ke Data Outlet
            </button>
        </div>
    </div>

    {{-- VIEW RIWAYAT --}}
    <div id="view-riwayat" class="tab-view" style="{{ $active_tab == 'riwayat' ? '' : 'display: none;' }}">
        <div class="main-content-box" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 60px 20px;">
            <div style="width: 80px; height: 80px; background: var(--light-blue); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; color: var(--primary-blue); font-size: 40px;">
                <iconify-icon icon="solar:history-bold-duotone"></iconify-icon>
            </div>
            <h3 style="color: #334155; margin-bottom: 8px;">Fitur Riwayat Stok Sedang Disiapkan</h3>
            <p style="color: #64748b; text-align: center; max-width: 400px;">Halaman untuk histori aktivitas stok outlet akan segera hadir.</p>
            <button onclick="switchTab('data')" class="btn-action" style="margin-top: 24px;">
                <iconify-icon icon="solar:arrow-left-bold-duotone"></iconify-icon>
                Kembali ke Data Outlet
            </button>
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
@endsection
