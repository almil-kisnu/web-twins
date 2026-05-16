@extends('layouts.app')

@section('content')
<style>
    :root {
        --primary: #2563eb;
        --primary-hover: #1d4ed8;
        --secondary: #64748b;
        --success: #10b981;
        --danger: #ef4444;
        --warning: #f59e0b;
        --bg-light: #f9fafb;
        --border-color: #e5e7eb;
        --text-main: #1f2937;
        --text-muted: #6b7280;
    }

    .app-container { 
        padding: 1.5rem; 
        font-family: 'Inter', sans-serif; 
        background: #fff; 
        min-height: 100vh; 
    }
    
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .page-title h2 { font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin: 0; }

    .tab-nav {
        display: flex;
        gap: 10px;
        background: #fff;
        padding: 10px;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        margin-bottom: 25px;
        overflow-x: auto;
    }
    .tab-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        border-radius: 10px;
        text-decoration: none;
        color: #64748b;
        font-weight: 500;
        font-size: 14px;
        transition: all 0.2s ease;
        border: none;
        background: none;
        cursor: pointer;
    }
    .tab-btn:hover {
        background: #f8fafc;
        color: #334155;
    }
    .tab-btn.active {
        background: #e0f2fe;
        color: #0ea5e9;
        font-weight: 600;
        box-shadow: 0 2px 4px rgba(14, 165, 233, 0.1);
    }
    .tab-content { display: none; }
    .tab-content.active { display: block; }

    .btn-custom {
        padding: 0.6rem 1.2rem;
        border-radius: 0.6rem;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s;
        border: 1px solid transparent;
        text-decoration: none;
    }
    .btn-outline { background: white; border-color: #d1d5db; color: #374151; }
    .btn-primary { background: var(--primary); color: white; }
    .btn-success { background: var(--success); color: white; }
    .btn-danger { background: var(--danger); color: white; }

    .search-container {
        position: relative;
        margin-bottom: 1rem;
        max-width: 350px;
    }
    .search-input {
        width: 100%;
        padding: 0.6rem 1rem 0.6rem 2.5rem;
        border: 1px solid var(--border-color);
        border-radius: 0.6rem;
        font-size: 0.875rem;
        outline: none;
    }
    .search-icon { position: absolute; left: 0.8rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); }

    .content-card {
        background: #fff;
        padding: 20px;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
    }
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .card-header h4 { margin: 0; font-size: 16px; color: #1e293b; }
    .header-actions { display: flex; gap: 10px; }
    .btn-primary-small {
        display: flex; align-items: center; gap: 6px; 
        background: #0ea5e9; color: white; padding: 8px 16px;
        border: none; border-radius: 8px; cursor: pointer; transition: 0.2s; font-size: 13px; font-weight: 600;
    }
    .btn-primary-small:hover { opacity: 0.9; }
    .table-responsive { overflow-x: auto; }

    .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .data-table th {
        text-align: left;
        padding: 12px 15px;
        background: #f8fafc;
        color: #64748b;
        font-size: 13px;
        font-weight: 600;
        border-bottom: 1px solid #e2e8f0;
    }
    .data-table td { padding: 15px; border-bottom: 1px solid #f1f5f9; font-size: 13px; color: #334155; }
    
    .action-buttons-table { display: flex; gap: 8px; }
    .btn-icon-table { 
        background: #f1f5f9; width: 32px; height: 32px; color: #64748b; 
        display: inline-flex; align-items: center; justify-content: center; 
        border-radius: 6px; border: none; cursor: pointer; transition: 0.2s;
    }
    .btn-icon-table:hover { background: #e0f2fe; color: #0ea5e9; }
    .btn-icon-table.text-danger:hover { background: #fee2e2; color: #ef4444; }

    /* Action Buttons Premium Style (Identical to Image) */
    .btn-action-premium {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 2px solid transparent;
    }
    .btn-read-premium {
        background: #f0f9ff;
        color: #0ea5e9;
        border-color: #38bdf8; /* Brighter blue border */
    }
    .btn-read-premium:hover {
        background: #0ea5e9;
        color: white;
        border-color: #0ea5e9;
    }
    .btn-delete-premium {
        background: #fff1f2;
        color: #f43f5e;
        border-color: #fecdd3; /* Soft pinkish border */
    }
    .btn-delete-premium:hover {
        background: #f43f5e;
        color: white;
        border-color: #f43f5e;
    }
    .btn-action-premium iconify-icon {
        font-size: 14px;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0; top: 0; width: 100%; height: 100%;
        background: rgba(15, 23, 42, 0.6);
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    .modal-content {
        background: white;
        padding: 2rem;
        border-radius: 1rem;
        width: 100%;
        max-width: 550px;
        max-height: 90vh;
        overflow-y: auto;
    }
    .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .detail-label { font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; }
    .detail-value { font-size: 0.9rem; color: var(--text-main); font-weight: 600; margin-bottom: 0.5rem; }

    .form-group { margin-bottom: 1rem; }
    .form-label { display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.4rem; color: var(--text-main); }
    .form-input { 
        width: 100%; 
        padding: 0.6rem; 
        border: 1px solid var(--border-color); 
        border-radius: 0.5rem; 
        outline: none;
    }
    .form-input:focus { 
        border-color: var(--primary); 
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2); 
    }
</style>

<div class="app-container">
    @if(session('success'))
        <div id="successAlert" style="background: #dcfce7; color: #166534; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; border: 1px solid #bbf7d0; display: flex; justify-content: space-between; align-items: center; transition: opacity 0.5s;">
            <div>
                <iconify-icon icon="solar:check-circle-bold-duotone" style="margin-right: 5px; vertical-align: -3px; font-size: 1.1rem;"></iconify-icon>
                {{ session('success') }}
            </div>
            <button onclick="closeAlert()" style="background:none; border:none; cursor:pointer; color: #166534; font-size: 1.25rem;">&times;</button>
        </div>
        <script>
            setTimeout(() => {
                let alert = document.getElementById('successAlert');
                if(alert) {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.style.display = 'none', 500);
                }
            }, 4000);
            function closeAlert() {
                let alert = document.getElementById('successAlert');
                if(alert) alert.style.display = 'none';
            }
        </script>
    @endif


    <div class="tab-nav">
        <button class="tab-btn active" onclick="switchTab(this, 'products')">
            <iconify-icon icon="solar:box-minimalistic-bold-duotone" style="font-size: 20px;"></iconify-icon>
            Produk
        </button>
        <button class="tab-btn" onclick="switchTab(this, 'opname')">
            <iconify-icon icon="solar:clipboard-list-bold-duotone" style="font-size: 20px;"></iconify-icon>
            Produk Opname
        </button>
        <button class="tab-btn" onclick="switchTab(this, 'request')">
            <iconify-icon icon="solar:document-add-bold-duotone" style="font-size: 20px;"></iconify-icon>
            Request Produk
        </button>
    </div>

<div class="search-container">
    <iconify-icon icon="solar:magnifer-linear" class="search-icon"></iconify-icon>
    <input type="text" id="searchInput" class="search-input" placeholder="Cari data...">
</div>

<!-- tabel produk -->
<div id="tab-products" class="tab-content active">
    <div class="content-card">
        <div class="card-header">
            <h4>Inventaris Produk</h4>
            <div class="header-actions">
                <button class="btn-primary-small" onclick="exportToExcel()">
                    <iconify-icon icon="solar:file-download-bold-duotone"></iconify-icon> Export Excel
                </button>
                <button class="btn-primary-small" onclick="openModal('addModal')">
                    <iconify-icon icon="solar:add-circle-bold-duotone"></iconify-icon> Tambah Produk
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="data-table" id="productTable">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Kategori</th>
                    <th>Stok</th>
                    <th>Lokasi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody class="table-body">
                @foreach($products as $product)
                <tr>
                    <td>
                        <div class="search-field" style="font-weight: 700;">{{ $product['nama_produk'] }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted)">{{ $product['sku'] }}</div>
                    </td>
                    <td>{{ $product['kategori'] }}</td>
                    <td>
                        @php
                            $isLowStock = $product['stok'] <= $product['minimal_stok'];
                            $stockColor = $isLowStock ? 'var(--danger)' : 'inherit';
                        @endphp
                        <strong style="color: var(--dynamic-color); --dynamic-color: {{ $stockColor }}">
                            {{ $product['stok'] }} {{ $product['satuan'] }}
                        </strong>
                    </td>
                    <td>{{ $product['lokasi_rak'] ?? '-' }}</td>
                    <td>
                        <div class="action-buttons-table">
                            <button class="btn-action-premium btn-read-premium" 
                                    data-product="{{ json_encode($product) }}"
                                    onclick="handleView(this)" title="Lihat Detail">
                                <iconify-icon icon="solar:eye-bold"></iconify-icon>
                            </button>
                            <button class="btn-action-premium btn-read-premium" style="background: #f0fdf4; color: #16a34a; border-color: #86efac;"
                                    data-product="{{ json_encode($product) }}"
                                    onclick="handleEdit(this)" title="Edit Produk">
                                <iconify-icon icon="solar:pen-bold"></iconify-icon>
                            </button>
                            <button class="btn-action-premium btn-delete-premium"
                                    onclick="confirmDelete('{{ $product['id'] }}', '{{ addslashes($product['nama_produk']) }}')" title="Hapus Produk">
                                <iconify-icon icon="solar:trash-bin-trash-bold"></iconify-icon>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    </div>
</div>

<!-- tabel produk opname-->
<div id="tab-opname" class="tab-content">
    <div class="content-card">
        <div class="card-header">
            <h4>Riwayat Produk Opname</h4>
            <div class="header-actions">
                <button class="btn-primary-small" onclick="openModal('addModalOpname')">
                    <iconify-icon icon="solar:add-circle-bold-duotone"></iconify-icon> Tambah Opname
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="data-table" id="opnameTable">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Petugas</th>
                        <th>Sistem</th>
                        <th>Fisik</th>
                        <th>Selisih</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                </tr>
            </thead>
            <tbody class="table-body">
                @foreach($opnames as $opname)
                <tr>
                    <td>{{ $opname['tanggal_cek'] }}</td>
                    <td class="search-field">{{ $opname['petugas'] }}</td>
                    <td>{{ $opname['stok_sistem'] }}</td>
                    <td>{{ $opname['stok_fisik'] }}</td>
                    <td>
                        @php $diffColor = $opname['selisih'] < 0 ? 'var(--danger)' : 'var(--success)'; @endphp
                        <span style="font-weight:bold; color: var(--diff-color); --diff-color: {{ $diffColor }}">
                            {{ $opname['selisih'] }}
                        </span>
                    </td>
                    <td>{{ $opname['keterangan'] }}</td>
                    <td>
                        <div class="action-buttons-table">
                            <button class="btn-action-premium btn-read-premium"
                                    onclick="openEditOpname({{ json_encode($opname) }})" title="Detail Opname">
                                <iconify-icon icon="solar:eye-bold"></iconify-icon>
                            </button>
                            <button class="btn-action-premium btn-delete-premium"
                                    onclick="confirmDeleteOpname('{{ $opname['id'] }}')" title="Hapus Opname">
                                <iconify-icon icon="solar:trash-bin-trash-bold"></iconify-icon>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- tabel request produk -->
<div id="tab-request" class="tab-content">
    <div class="content-card">
        <div class="card-header">
            <h4>Request Produk dari Cabang</h4>
        </div>
        <div class="table-responsive">
            <table class="data-table" id="requestTable">
                <thead>
                    <tr>
                        <th>Pemohon</th>
                        <th>Jumlah</th>
                        <th>Prioritas</th>
                        <th>Status</th>
                        <th>Alasan</th>
                        <th>Aksi</th>
                </tr>
            </thead>
            <tbody class="table-body">
                @foreach($requests as $req)
                <tr>
                    <td class="search-field"><strong>{{ $req['pemohon'] }}</strong></td>
                    <td>{{ $req['jumlah_minta'] }}</td>
                    <td>
                        @php
                            $isHigh = $req['prioritas'] == 'Tinggi';
                            $prioBg = $isHigh ? '#fee2e2' : '#fef3c7';
                            $prioText = $isHigh ? '#991b1b' : '#92400e';
                        @endphp
                        <span class="status-badge" style="--bg-color: {{ $prioBg }}; --text-color: {{ $prioText }}">
                            {{ $req['prioritas'] }}
                        </span>
                    </td>
                    <td><strong>{{ $req['status'] }}</strong></td>
                    <td>{{ $req['alasan_permintaan'] }}</td>
                    <td>
                        @if ($req['status'] == 'Pending')
                        <div class="action-buttons-table">
                            <form action="/products/request/{{ $req['id'] }}/approve" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn-icon-table text-success" title="Setujui" style="color: #166534; background: #dcfce7;">
                                    <iconify-icon icon="solar:check-circle-bold-duotone"></iconify-icon>
                                </button>
                            </form>
                            <form action="/products/request/{{ $req['id'] }}/reject" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn-icon-table text-danger" title="Tolak" style="color: #991b1b; background: #fee2e2;">
                                    <iconify-icon icon="solar:close-circle-bold-duotone"></iconify-icon>
                                </button>
                            </form>
                        </div>
                        @else
                            <span style="color:#64748b; font-size:12px;">{{ $req['status'] == 'Disetujui' ? 'Berhasil' : 'Ditolak' }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- modal detail -->
<div id="detailModal" class="modal">
    <div class="modal-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3>Detail Produk</h3>
            <button onclick="closeModal('detailModal')" style="border:none; background:none; cursor:pointer; font-size:1.5rem">&times;</button>
        </div>
        <div id="detailContent" class="detail-grid"></div>
        <button onclick="closeModal('detailModal')" class="btn-custom btn-outline" style="width:100%; margin-top:1.5rem; justify-content:center">Tutup</button>
    </div>
</div>

<!-- tambah data -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3>Tambah Produk Baru</h3>
            <button onclick="closeModal('addModal')" style="border:none; background:none; cursor:pointer; font-size:1.5rem">&times;</button>
        </div>
        <form action="/products" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Nama Produk</label>
                <input type="text" name="nama_produk" class="form-input" required>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">SKU</label>
                    <input type="text" name="sku" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Kategori</label>
                    <input type="text" name="kategori" class="form-input">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Harga Beli</label>
                    <input type="number" name="harga_beli" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Harga Jual</label>
                    <input type="number" name="harga_jual" class="form-input">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Stok Awal</label>
                    <input type="number" name="stok" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Satuan</label>
                    <input type="text" name="satuan" class="form-input" placeholder="Pcs/Kg">
                </div>
                <div class="form-group">
                    <label class="form-label">Lokasi Rak</label>
                    <input type="text" name="lokasi_rak" class="form-input" placeholder="A1/B2">
                </div>
            </div>
            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                <button type="button" onclick="closeModal('addModal')" class="btn-custom btn-outline" style="flex:1; justify-content:center">Batal</button>
                <button type="submit" class="btn-custom btn-primary" style="flex:1; justify-content:center">Simpan Produk</button>
            </div>
        </form>
    </div>
</div>

<!-- edit data -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3>Edit Produk</h3>
            <button onclick="closeModal('editModal')" style="border:none; background:none; cursor:pointer; font-size:1.5rem">&times;</button>
        </div>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label class="form-label">Nama Produk</label>
                <input type="text" name="nama_produk" id="edit_nama" class="form-input" required>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">SKU</label>
                    <input type="text" name="sku" id="edit_sku" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Kategori</label>
                    <input type="text" name="kategori" id="edit_kategori" class="form-input">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Harga Beli</label>
                    <input type="number" name="harga_beli" id="edit_hargabeli" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Harga Jual</label>
                    <input type="number" name="harga_jual" id="edit_hargajual" class="form-input">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Stok</label>
                    <input type="number" name="stok" id="edit_stok" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Satuan</label>
                    <input type="text" name="satuan" id="edit_satuan" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Lokasi Rak</label>
                    <input type="text" name="lokasi_rak" id="edit_lokasi" class="form-input">
                </div>
            </div>
            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="button" onclick="closeModal('editModal')" class="btn-custom btn-outline" style="flex:1; justify-content:center">Batal</button>
                <button type="submit" class="btn-custom btn-primary" style="flex:1; justify-content:center">Update Data</button>
            </div>
        </form>
    </div>
</div>

<!-- konfirmasi hapus -->
<div id="deleteModal" class="modal">
    <div class="modal-content" style="max-width: 400px; text-align: center;">
        <div style="color: var(--danger); font-size: 3rem; margin-bottom: 1rem;">
            <iconify-icon icon="solar:danger-bold-duotone"></iconify-icon>
        </div>
        <h3 id="delTitle">Hapus Data?</h3>
        <p id="delMsg" style="color: var(--text-muted); margin: 1rem 0; font-size: 0.875rem;"></p>
        <form id="deleteForm" method="POST">
            @csrf
            @method('DELETE')
            <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem;">
                <button type="button" onclick="closeModal('deleteModal')" class="btn-custom btn-outline" style="flex:1; justify-content: center;">Batal</button>
                <button type="submit" class="btn-custom btn-danger" style="flex:1; justify-content: center;">Hapus Sekarang</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Opname -->
<div id="addModalOpname" class="modal">
    <div class="modal-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3>Tambah Opname Baru</h3>
            <button onclick="closeModal('addModalOpname')" style="border:none; background:none; cursor:pointer; font-size:1.5rem">&times;</button>
        </div>
        <form action="/products/opname" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Stok Sistem</label>
                    <input type="number" name="stok_sistem" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Stok Fisik</label>
                    <input type="number" name="stok_fisik" class="form-input" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Keterangan</label>
                <input type="text" name="keterangan" class="form-input" placeholder="Opsional">
            </div>
            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                <button type="button" onclick="closeModal('addModalOpname')" class="btn-custom btn-outline" style="flex:1; justify-content:center">Batal</button>
                <button type="submit" class="btn-custom btn-primary" style="flex:1; justify-content:center">Simpan Opname</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Opname -->
<div id="editModalOpname" class="modal">
    <div class="modal-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3>Edit Opname</h3>
            <button onclick="closeModal('editModalOpname')" style="border:none; background:none; cursor:pointer; font-size:1.5rem">&times;</button>
        </div>
        <form id="editOpnameForm" method="POST">
            @csrf
            @method('PUT')
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Stok Sistem</label>
                    <input type="number" name="stok_sistem" id="edit_opname_sistem" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Stok Fisik</label>
                    <input type="number" name="stok_fisik" id="edit_opname_fisik" class="form-input" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Keterangan</label>
                <input type="text" name="keterangan" id="edit_opname_keterangan" class="form-input">
            </div>
            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                <button type="button" onclick="closeModal('editModalOpname')" class="btn-custom btn-outline" style="flex:1; justify-content:center">Batal</button>
                <button type="submit" class="btn-custom btn-primary" style="flex:1; justify-content:center">Update Data</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Opname -->
<div id="deleteModalOpname" class="modal">
    <div class="modal-content" style="max-width: 400px; text-align: center;">
        <div style="color: var(--danger); font-size: 3rem; margin-bottom: 1rem;">
            <iconify-icon icon="solar:danger-bold-duotone"></iconify-icon>
        </div>
        <h3>Hapus Opname?</h3>
        <p style="color: var(--text-muted); margin: 1rem 0; font-size: 0.875rem;">Data opname ini akan dihapus permanen.</p>
        <form id="deleteOpnameForm" method="POST">
            @csrf
            @method('DELETE')
            <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem;">
                <button type="button" onclick="closeModal('deleteModalOpname')" class="btn-custom btn-outline" style="flex:1; justify-content: center;">Batal</button>
                <button type="submit" class="btn-custom btn-danger" style="flex:1; justify-content: center;">Hapus</button>
            </div>
        </form>
    </div>
</div>

</div>

<!-- script -->
<script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js" crossorigin="anonymous"></script>

<script>
    function openModal(id) { 
        const modal = document.getElementById(id);
        if(modal) modal.style.display = 'flex'; 
    }
    
    function closeModal(id) { 
        const modal = document.getElementById(id);
        if(modal) modal.style.display = 'none'; 
    }

    function switchTab(btn, tabId) {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        if (btn) {
            btn.classList.add('active');
        }
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        const targetTab = document.getElementById('tab-' + tabId);
        if(targetTab) targetTab.classList.add('active');
        document.getElementById('searchInput').value = '';
        document.querySelectorAll('.table-body tr').forEach(row => row.style.display = '');
    }

    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchText = this.value.toLowerCase();
        const activeTab = document.querySelector('.tab-content.active');
        if(!activeTab) return;
        const rows = activeTab.querySelectorAll('.table-body tr');
        rows.forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(searchText) ? '' : 'none';
        });
    });
    
    function handleView(el) {
        const product = JSON.parse(el.getAttribute('data-product'));
        viewDetail(product);
    }

    function handleEdit(el) {
        const product = JSON.parse(el.getAttribute('data-product'));
        openEdit(product);
    }

    function viewDetail(product) {
        const content = document.getElementById('detailContent');
        const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' });
        content.innerHTML = `
            <div><div class="detail-label">Nama</div><div class="detail-value">${product.nama_produk}</div></div>
            <div><div class="detail-label">SKU</div><div class="detail-value">${product.sku}</div></div>
            <div><div class="detail-label">Harga Beli</div><div class="detail-value">${formatter.format(product.harga_beli)}</div></div>
            <div><div class="detail-label">Harga Jual</div><div class="detail-value">${formatter.format(product.harga_jual)}</div></div>
            <div><div class="detail-label">Stok</div><div class="detail-value">${product.stok} ${product.satuan}</div></div>
            <div><div class="detail-label">Lokasi</div><div class="detail-value">${product.lokasi_rak || '-'}</div></div>
        `;
        openModal('detailModal');
    }

    function openEdit(product) {
        document.getElementById('edit_nama').value = product.nama_produk;
        document.getElementById('edit_sku').value = product.sku;
        document.getElementById('edit_kategori').value = product.kategori;
        document.getElementById('edit_stok').value = product.stok;
        document.getElementById('edit_satuan').value = product.satuan;
        document.getElementById('edit_lokasi').value = product.lokasi_rak;
        document.getElementById('edit_hargabeli').value = product.harga_beli;
        document.getElementById('edit_hargajual').value = product.harga_jual;
        document.getElementById('editForm').action = `/products/${product.id}`;
        openModal('editModal');
    }

    function confirmDelete(id, name) {
        document.getElementById('delMsg').innerText = `Data "${name}" akan dihapus permanen.`;
        document.getElementById('deleteForm').action = `/products/${id}`;
        openModal('deleteModal');
    }

    function openEditOpname(op) {
        document.getElementById('edit_opname_sistem').value = op.stok_sistem;
        document.getElementById('edit_opname_fisik').value = op.stok_fisik;
        document.getElementById('edit_opname_keterangan').value = op.keterangan;
        document.getElementById('editOpnameForm').action = `/products/opname/${op.id}`;
        openModal('editModalOpname');
    }

    function confirmDeleteOpname(id) {
        document.getElementById('deleteOpnameForm').action = `/products/opname/${id}`;
        openModal('deleteModalOpname');
    }

    function exportToExcel() {
        const activeTab = document.querySelector('.tab-content.active');
        if(!activeTab) return;
        const table = activeTab.querySelector('table');
        const title = activeTab.querySelector('h4') ? activeTab.querySelector('h4').innerText : 'Data Export';
        if (typeof XLSX === 'undefined') return;
        const wb = XLSX.utils.table_to_book(table, {sheet: title});
        XLSX.writeFile(wb, `${title.replace(/\s+/g, '_')}.xlsx`);
    }

    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) event.target.style.display = 'none';
    }
</script>
@endsection