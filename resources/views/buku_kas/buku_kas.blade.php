@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/fitur.css') }}">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .tab-pill, .btn-action, .chip, .close-modal, .btn-filter {
        user-select: none; /* ANTI BLOK TEKS */
    }

    .status-badge {
        padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600;
    }
    .status-lunas { background: #E8F5E9; color: #2E7D32; }
    .status-belum { background: #FFF3E0; color: #E65100; }
    
    .chips-container { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
    .chip {
        background: #f1f5f9; color: #475569; padding: 6px 12px; border-radius: 20px;
        font-size: 0.8rem; cursor: pointer; transition: 0.2s; border: none; font-weight: 500;
    }
    .chip:hover { background: #e2e8f0; }
    
    .empty-state { text-align: center; padding: 40px; color: #999; }

    /* Invalid Field Styling */
    .form-control.is-invalid {
        border-color: #ef4444 !important;
        background-color: #fef2f2 !important;
    }
    .invalid-feedback {
        color: #ef4444;
        font-size: 11px;
        margin-top: 4px;
        display: none;
    }
    .form-control.is-invalid + .invalid-feedback {
        display: block;
    }
    .tab-pill.active {
        background: var(--primary-blue) !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }
    .tab-pill:hover:not(.active) {
        background: #f1f5f9;
        transform: translateY(-1px);
    }
    .nominal-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }
    .nominal-wrapper::before {
        content: "Rp";
        position: absolute;
        left: 12px;
        font-weight: 700;
        color: #475569;
        font-size: 13px;
        pointer-events: none;
    }
    .nominal-wrapper input {
        padding-left: 35px !important;
    }
</style>

<div class="fitur-container" id="bukukas-app">
    {{-- PILL TABS --}}
    <div class="tab-navigation">
        <a href="#pengeluaran" class="tab-pill" onclick="switchTab('pengeluaran')" id="pill-pengeluaran">
            <iconify-icon icon="solar:round-arrow-left-down-bold-duotone"></iconify-icon>
            <span>Pengeluaran</span>
        </a>
        <a href="#pemasukan" class="tab-pill" onclick="switchTab('pemasukan')" id="pill-pemasukan">
            <iconify-icon icon="solar:round-arrow-right-up-bold-duotone"></iconify-icon>
            <span>Pemasukan Lainnya</span>
        </a>
        <a href="#hutang" class="tab-pill" onclick="switchTab('hutang')" id="pill-hutang">
            <iconify-icon icon="solar:wallet-money-bold-duotone"></iconify-icon>
            <span>Hutang</span>
        </a>
        <a href="#piutang" class="tab-pill" onclick="switchTab('piutang')" id="pill-piutang">
            <iconify-icon icon="solar:hand-money-bold-duotone"></iconify-icon>
            <span>Piutang</span>
        </a>
    </div>

    {{-- ACTION BAR --}}
    <div class="action-bar">
        <div style="display: contents;">
            <div class="left-actions-group">
                <div class="search-wrapper">
                    <iconify-icon icon="solar:magnifer-linear" class="search-icon"></iconify-icon>
                    <input type="text" id="globalSearch" class="search-input" placeholder="Cari keterangan..." onkeyup="filterTable()">
                </div>
                
                <div class="dropdown">
                    <button type="button" class="btn-filter" title="Rentang Waktu" onclick="toggleDropdown(event)">
                        <iconify-icon icon="solar:calendar-bold-duotone" style="font-size: 24px;" class="{{ request('start_date') || request('end_date') ? 'text-primary-blue' : '' }}"></iconify-icon>
                    </button>
                    <div class="dropdown-content" style="padding: 15px; width: 280px; left: 0; right: auto;">
                        <div style="font-size: 12px; font-weight: 600; margin-bottom: 10px; color: var(--primary-blue);">RENTANG TANGGAL</div>
                        <div class="form-group" style="margin-bottom: 12px;">
                            <label style="font-size: 11px; color: #666;">Dari</label>
                            <input type="date" id="filterStartDate" class="form-control" style="font-size: 12px;" value="{{ $start_date }}">
                        </div>
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label style="font-size: 11px; color: #666;">Sampai</label>
                            <input type="date" id="filterEndDate" class="form-control" style="font-size: 12px;" value="{{ $end_date }}">
                        </div>
                        <button type="button" class="btn-action" style="width: 100%; justify-content: center; padding: 10px;" onclick="applyBukuKasRangeFilter()">
                            Terapkan Filter
                        </button>
                    </div>
                </div>

                @if(Auth::user()->role === 'owner' || (Auth::user()->role === 'kepala_toko' && $outlets->count() > 1))
                    <div class="dropdown">
                        <button type="button" class="btn-filter" title="Filter Toko: {{ $store_id == 'all' ? 'Semua Outlet' : ($outlets->firstWhere('uuid', $store_id)->nama ?? 'Semua') }}" onclick="toggleDropdown(event)">
                            <iconify-icon icon="solar:shop-bold-duotone" style="font-size: 24px;" class="{{ $store_id != 'all' ? 'text-primary-blue' : '' }}"></iconify-icon>
                        </button>
                        <div class="dropdown-content">
                            <form id="storeForm" method="GET" action="{{ route('keuangan.transaksi') }}">
                                <input type="hidden" name="active_tab" id="storeFormActiveTab" value="{{ $active_tab }}">
                                <input type="hidden" name="store_id" id="storeFormStoreId" value="{{ $store_id }}">
                                <input type="hidden" name="status" id="storeFormStatus" value="{{ $status }}">
                            </form>
                            @if(Auth::user()->role === 'owner')
                                <a href="javascript:void(0)" onclick="document.getElementById('storeFormActiveTab').value = currentTab; document.getElementById('storeFormStoreId').value = 'all'; document.getElementById('storeForm').submit()" class="{{ $store_id === 'all' ? 'active-dropdown-item' : '' }}">
                                    Semua Outlet
                                </a>
                            @endif
                            @foreach($outlets as $o)
                                <a href="javascript:void(0)" onclick="document.getElementById('storeFormActiveTab').value = currentTab; document.getElementById('storeFormStoreId').value = '{{ $o->uuid }}'; document.getElementById('storeForm').submit()" class="{{ $store_id == $o->uuid ? 'active-dropdown-item' : '' }}">
                                    {{ $o->nama }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="dropdown" id="statusFilterDropdown" style="display: none;">
                    <button type="button" class="btn-filter" title="Filter Status: {{ $status == 'lunas' ? 'Lunas' : ($status == 'belum_lunas' ? 'Belum Lunas' : 'Semua') }}" onclick="toggleDropdown(event)">
                        <iconify-icon icon="solar:filter-bold-duotone" style="font-size: 24px;" class="{{ $status ? 'text-primary-blue' : '' }}"></iconify-icon>
                    </button>
                    <div class="dropdown-content">
                        <a href="javascript:void(0)" onclick="applyStatusFilter('')" class="{{ !$status ? 'active-dropdown-item' : '' }}">Semua Status</a>
                        <a href="javascript:void(0)" onclick="applyStatusFilter('belum_lunas')" class="{{ $status == 'belum_lunas' ? 'active-dropdown-item' : '' }}">Belum Lunas</a>
                        <a href="javascript:void(0)" onclick="applyStatusFilter('lunas')" class="{{ $status == 'lunas' ? 'active-dropdown-item' : '' }}">Lunas</a>
                    </div>
                </div>
            </div>

            <!-- Table Actions -->
            <div class="right-actions">
                <div class="dropdown">
                    <button type="button" class="btn-action" onclick="toggleDropdown(event)">
                        <iconify-icon icon="solar:document-text-bold-duotone"></iconify-icon>
                        <span>Extract</span>
                    </button>
                    <div class="dropdown-content">
                        <a href="javascript:void(0)" onclick="openExportModal('excel')">
                            <iconify-icon icon="vscode-icons:file-type-excel" style="margin-right: 8px;"></iconify-icon>
                            Excel
                        </a>
                        <a href="javascript:void(0)" onclick="openExportModal('pdf')">
                            <iconify-icon icon="vscode-icons:file-type-pdf" style="margin-right: 8px;"></iconify-icon>
                            PDF
                        </a>
                    </div>
                </div>
                <button type="button" class="btn-action" id="btnAddMain" onclick="openCurrentModal()">
                    <iconify-icon icon="solar:add-circle-bold-duotone"></iconify-icon>
                    <span id="txtAddMain">Tambah Pengeluaran</span>
                </button>
            </div>
        </div>
    </div>
    
    <form id="formGlobalDeleteCf" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
    <form id="formGlobalDeleteDebt" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    {{-- MAIN BOX --}}
    <div class="main-content-box">
        <div class="table-container">
            
            <!-- VIEW PENGELUARAN -->
            <div id="view-pengeluaran">
                <table class="fitur-table">
                    <thead>
                        <tr>
                            <th>TANGGAL</th>
                            <th>TOKO</th>
                            <th>KARYAWAN</th>
                            <th>KETERANGAN</th>
                            <th>NOMINAL</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-pengeluaran">
                        @forelse($pengeluaran as $p)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y H:i') }}</td>
                            <td>{{ $p->outlet->nama ?? '-' }}</td>
                            <td>{{ $p->user->username ?? ($p->user->name ?? '-') }}</td>
                            <td>
                                <strong>{{ preg_replace('/\(Trx: [a-f0-9-]{36}\)/i', '(Otomatis)', $p->keterangan) }}</strong>
                            </td>
                            <td class="price-text" style="color: #C62828;">- Rp {{ number_format($p->nominal, 0, ',', '.') }}</td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: var(--primary-blue);" data-item="{{ json_encode($p) }}" onclick="viewCashFlowDetail(JSON.parse(this.dataset.item))" title="Detail">
                                        <iconify-icon icon="solar:eye-bold-duotone"></iconify-icon>
                                    </button>
                                    <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #FBC02D; border-color: #FFF9C4;" data-item="{{ json_encode($p) }}" onclick="openEditCashFlow(JSON.parse(this.dataset.item))" title="Edit">
                                        <iconify-icon icon="solar:pen-bold-duotone"></iconify-icon>
                                    </button>
                                    <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #D9534F; border-color: #ffcccc;" onclick="deleteCf('{{ $p->uuid }}', '{{ $p->jenis }}')" title="Hapus">
                                        <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="empty-state">Belum ada data pengeluaran.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- VIEW PEMASUKAN -->
            <div id="view-pemasukan" style="display: none;">
                <table class="fitur-table">
                    <thead>
                        <tr>
                            <th>TANGGAL</th>
                            <th>TOKO</th>
                            <th>KARYAWAN</th>
                            <th>KETERANGAN</th>
                            <th>NOMINAL</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-pemasukan">
                        @forelse($pemasukan as $p)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y H:i') }}</td>
                            <td>{{ $p->outlet->nama ?? '-' }}</td>
                            <td>{{ $p->user->username ?? ($p->user->name ?? '-') }}</td>
                            <td>
                                <strong>{{ preg_replace('/\(Trx: [a-f0-9-]{36}\)/i', '(Otomatis)', $p->keterangan) }}</strong>
                            </td>
                            <td class="price-text" style="color: #2E7D32;">+ Rp {{ number_format($p->nominal, 0, ',', '.') }}</td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: var(--primary-blue);" data-item="{{ json_encode($p) }}" onclick="viewCashFlowDetail(JSON.parse(this.dataset.item))" title="Detail">
                                        <iconify-icon icon="solar:eye-bold-duotone"></iconify-icon>
                                    </button>
                                    <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #FBC02D; border-color: #FFF9C4;" data-item="{{ json_encode($p) }}" onclick="openEditCashFlow(JSON.parse(this.dataset.item))" title="Edit">
                                        <iconify-icon icon="solar:pen-bold-duotone"></iconify-icon>
                                    </button>
                                    <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #D9534F; border-color: #ffcccc;" onclick="deleteCf('{{ $p->uuid }}', '{{ $p->jenis }}')" title="Hapus">
                                        <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="empty-state">Belum ada data pemasukan lainnya.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- VIEW HUTANG -->
            <div id="view-hutang" style="display: none;">
                <table class="fitur-table">
                    <thead>
                        <tr>
                            <th>SUPPLIER</th>
                            <th>TOTAL HUTANG</th>
                            <th>SISA TAGIHAN</th>
                            <th>STATUS</th>
                            <th>JATUH TEMPO</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-hutang">
                        @forelse($hutang as $h)
                        <tr>
                            <td><strong>{{ $h->contact->nama ?? '-' }}</strong></td>
                            <td class="price-text">Rp {{ number_format($h->nominal, 0, ',', '.') }}</td>
                            <td class="price-text" style="color: var(--primary-blue);">Rp {{ number_format($h->sisa, 0, ',', '.') }}</td>
                            <td>
                                <span class="status-badge {{ $h->sisa <= 0 ? 'status-lunas' : 'status-belum' }}">
                                    {{ $h->sisa <= 0 ? 'Lunas' : 'Belum Lunas' }}
                                </span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($h->jatuh_tempo)->format('d/m/Y') }}</td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: var(--primary-blue);" onclick="viewDebtDetail({{ json_encode($h) }}, {{ json_encode($h->contact) }}, {{ json_encode($h->detailDebts) }})" title="Detail">
                                        <iconify-icon icon="solar:eye-bold-duotone"></iconify-icon>
                                    </button>
                                    <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #FBC02D; border-color: #FFF9C4;" onclick="openEditDebt({{ json_encode($h) }}, {{ json_encode($h->contact) }})" title="Edit">
                                        <iconify-icon icon="solar:pen-bold-duotone"></iconify-icon>
                                    </button>
                                    <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #D9534F; border-color: #ffcccc;" onclick="deleteDebt('{{ $h->uuid }}', 'Hutang')" title="Hapus">
                                        <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="empty-state">Belum ada data hutang supplier.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- VIEW PIUTANG -->
            <div id="view-piutang" style="display: none;">
                <table class="fitur-table">
                    <thead>
                        <tr>
                            <th>CUSTOMER</th>
                            <th>TOTAL PIUTANG</th>
                            <th>SISA TAGIHAN</th>
                            <th>STATUS</th>
                            <th>JATUH TEMPO</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-piutang">
                        @forelse($piutang as $p)
                        <tr>
                            <td><strong>{{ $p->contact->nama ?? '-' }}</strong></td>
                            <td class="price-text">Rp {{ number_format($p->nominal, 0, ',', '.') }}</td>
                            <td class="price-text" style="color: var(--primary-blue);">Rp {{ number_format($p->sisa, 0, ',', '.') }}</td>
                            <td>
                                <span class="status-badge {{ $p->sisa <= 0 ? 'status-lunas' : 'status-belum' }}">
                                    {{ $p->sisa <= 0 ? 'Lunas' : 'Belum Lunas' }}
                                </span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($p->jatuh_tempo)->format('d/m/Y') }}</td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: var(--primary-blue);" onclick="viewDebtDetail({{ json_encode($p) }}, {{ json_encode($p->contact) }}, {{ json_encode($p->detailDebts) }})" title="Detail">
                                        <iconify-icon icon="solar:eye-bold-duotone"></iconify-icon>
                                    </button>
                                    <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #FBC02D; border-color: #FFF9C4;" onclick="openEditDebt({{ json_encode($p) }}, {{ json_encode($p->contact) }})" title="Edit">
                                        <iconify-icon icon="solar:pen-bold-duotone"></iconify-icon>
                                    </button>
                                    <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #D9534F; border-color: #ffcccc;" onclick="deleteDebt('{{ $p->uuid }}', 'Piutang')" title="Hapus">
                                        <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="empty-state">Belum ada data piutang customer.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<!-- ================= MODALS ================= -->

<!-- Modal Detail Pemasukan / Pengeluaran (CashFlow) -->
<div id="modalDetailCashFlow" class="modal-overlay">
    <div class="modal-content" style="max-width: 380px; padding: 30px;">
        <div class="modal-header" style="margin-bottom: 0; padding-bottom: 0;">
            <h3></h3>
            <button type="button" class="close-modal" onclick="closeModal('modalDetailCashFlow')">&times;</button>
        </div>
        <div style="text-align: center;">
            <div id="cfIcon" style="width: 60px; height: 60px; border-radius: 30px; display: inline-flex; justify-content: center; align-items: center; font-size: 30px; margin-bottom: 10px; background: #E8F5E9; color: #2E7D32;">
                <!-- Filled by JS -->
            </div>
            <h4 id="cfTitle" style="margin: 0; color: #333; font-size: 16px; font-weight: 700;">Detail Pemasukan Lainnya</h4>
            <h2 id="cfNominal" style="margin: 10px 0 25px 0; font-size: 28px; color: #1e293b;">Rp 0</h2>
        </div>
        
        <div style="font-size: 13px; color: #475569; display: flex; flex-direction: column; gap: 15px;">
            <div style="display: flex; gap: 10px;">
                <iconify-icon icon="solar:document-text-outline" style="font-size: 18px; color: #94a3b8;"></iconify-icon>
                <div>
                    <div style="color: #94a3b8; font-size: 11px; margin-bottom: 2px;">Keterangan</div>
                    <div id="cfKeterangan" style="font-weight: 600;">-</div>
                </div>
            </div>
            <div style="display: flex; gap: 10px;">
                <iconify-icon icon="solar:clock-circle-outline" style="font-size: 18px; color: #94a3b8;"></iconify-icon>
                <div>
                    <div style="color: #94a3b8; font-size: 11px; margin-bottom: 2px;">Tanggal & Waktu</div>
                    <div id="cfTanggal" style="font-weight: 600;">-</div>
                </div>
            </div>
            <div style="display: flex; gap: 10px;">
                <iconify-icon icon="solar:shop-outline" style="font-size: 18px; color: #94a3b8;"></iconify-icon>
                <div>
                    <div style="color: #94a3b8; font-size: 11px; margin-bottom: 2px;">Toko</div>
                    <div id="cfToko" style="font-weight: 600;">-</div>
                </div>
            </div>
            <div style="display: flex; gap: 10px;">
                <iconify-icon icon="solar:user-outline" style="font-size: 18px; color: #94a3b8;"></iconify-icon>
                <div>
                    <div style="color: #94a3b8; font-size: 11px; margin-bottom: 2px;">Karyawan</div>
                    <div id="cfKaryawan" style="font-weight: 600;">-</div>
                </div>
            </div>
            <div style="display: flex; gap: 10px;">
                <iconify-icon icon="solar:card-outline" style="font-size: 18px; color: #94a3b8;"></iconify-icon>
                <div>
                    <div style="color: #94a3b8; font-size: 11px; margin-bottom: 2px;">Metode Pembayaran</div>
                    <div id="cfMetode" style="font-weight: 600;">-</div>
                </div>
            </div>
        </div>

        <div id="cfProductSection" style="display: none; margin-top: 20px;">
            <div style="font-size: 12px; font-weight: 700; color: #1e293b; margin-bottom: 10px; border-top: 1px dashed #e2e8f0; padding-top: 15px;">Daftar Produk / Item:</div>
            <div id="cfProductList" style="display: flex; flex-direction: column; gap: 8px; max-height: 200px; overflow-y: auto;">
                <!-- Filled by JS -->
            </div>
        </div>

        <div style="display: flex; justify-content: center; margin-top: 35px;">
            <button type="button" class="btn-action" style="background: #f1f5f9; color: #475569; width: 100%; justify-content: center;" onclick="closeModal('modalDetailCashFlow')">Tutup</button>
        </div>
    </div>
</div>

<!-- Modal Tambah Pengeluaran -->
<div id="modalPengeluaran" class="modal-overlay">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header">
            <h3>Tambah Pengeluaran</h3>
            <button type="button" class="close-modal" onclick="closeModal('modalPengeluaran')">&times;</button>
        </div>
        <form action="{{ route('keuangan.cashflow.store') }}" method="POST" novalidate>
            @csrf
            @if($store_id === 'all')
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-size: 11px; color: #888; display: block;">Pilih Outlet Tujuan *</label>
                    <select name="store_id" class="form-control" style="border: 1px solid #ddd; padding: 8px; border-radius: 8px;" required>
                        <option value="">-- Pilih Outlet --</option>
                        @foreach($outlets as $o)
                            <option value="{{ $o->uuid }}">{{ $o->nama }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="store_id" value="{{ $store_id }}">
            @endif
            <input type="hidden" name="jenis" value="Pengeluaran">
            
            <div class="form-group" style="border: 1px solid #ddd; border-radius: 8px; padding: 10px; margin-bottom: 15px;">
                <label style="font-size: 11px; color: #888; display: block;">Tanggal Transaksi *</label>
                <input type="date" name="tanggal" class="form-control" style="border:none; padding:5px 0" value="{{ date('Y-m-d') }}" required>
            </div>

            <div class="form-group">
                <label>Nominal *</label>
                <div class="nominal-wrapper">
                    <input type="number" name="nominal" class="form-control" placeholder="0" required>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-size: 11px; color: #888; display: block;">Metode Pembayaran *</label>
                <select name="metode_pembayaran" class="form-control" style="border: 1px solid #ddd; padding: 8px; border-radius: 8px;" required>
                    <option value="">-- Pilih Metode --</option>
                    @foreach($paymentMethods as $pm)
                        <option value="{{ $pm->uuid }}">{{ $pm->nama_metode }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group">
                <label>Keterangan *</label>
                <textarea name="keterangan" id="ketPengeluaran" class="form-control" style="min-height: 80px;" placeholder="Tulis keterangan pengeluaran..." required></textarea>
                <div style="font-size: 11px; color: #888; margin-top: 10px;">Saran:</div>
                <div class="chips-container">
                    @foreach(['Gaji', 'Sewa Tempat', 'Listrik', 'Air', 'Bensin', 'Bahan Baku', 'Lain-lain'] as $sar)
                        <button type="button" class="chip" onclick="document.getElementById('ketPengeluaran').value = '{{ $sar }}'">{{ $sar }}</button>
                    @endforeach
                </div>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeModal('modalPengeluaran')" class="btn-action btn-danger" style="flex:1; justify-content:center;">Batal</button>
                <button type="submit" class="btn-action" style="flex:1; justify-content:center;">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Pemasukan -->
<div id="modalPemasukan" class="modal-overlay">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header">
            <h3>Tambah Pemasukan Lainnya</h3>
            <button type="button" class="close-modal" onclick="closeModal('modalPemasukan')">&times;</button>
        </div>
        <form action="{{ route('keuangan.cashflow.store') }}" method="POST" novalidate>
            @csrf
            @if($store_id === 'all')
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-size: 11px; color: #888; display: block;">Pilih Outlet Tujuan *</label>
                    <select name="store_id" class="form-control" style="border: 1px solid #ddd; padding: 8px; border-radius: 8px;" required>
                        <option value="">-- Pilih Outlet --</option>
                        @foreach($outlets as $o)
                            <option value="{{ $o->uuid }}">{{ $o->nama }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="store_id" value="{{ $store_id }}">
            @endif
            <input type="hidden" name="jenis" value="Pemasukan">
            
            <div class="form-group" style="border: 1px solid #ddd; border-radius: 8px; padding: 10px; margin-bottom: 15px;">
                <label style="font-size: 11px; color: #888; display: block;">Tanggal Transaksi *</label>
                <input type="date" name="tanggal" class="form-control" style="border:none; padding:5px 0" value="{{ date('Y-m-d') }}" required>
            </div>

            <div class="form-group">
                <label>Nominal *</label>
                <div class="nominal-wrapper">
                    <input type="number" name="nominal" class="form-control" placeholder="0" required>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-size: 11px; color: #888; display: block;">Metode Pembayaran *</label>
                <select name="metode_pembayaran" class="form-control" style="border: 1px solid #ddd; padding: 8px; border-radius: 8px;" required>
                    <option value="">-- Pilih Metode --</option>
                    @foreach($paymentMethods as $pm)
                        <option value="{{ $pm->uuid }}">{{ $pm->nama_metode }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group">
                <label>Keterangan *</label>
                <textarea name="keterangan" id="ketPemasukan" class="form-control" style="min-height: 80px;" placeholder="Tulis keterangan pemasukan lainnya..." required></textarea>
                <div style="font-size: 11px; color: #888; margin-top: 10px;">Saran:</div>
                <div class="chips-container">
                    @foreach(['Tip/Upah', 'Bonus', 'Pengembalian', 'Koreksi Kas', 'Lain-lain'] as $sar)
                        <button type="button" class="chip" onclick="document.getElementById('ketPemasukan').value = '{{ $sar }}'">{{ $sar }}</button>
                    @endforeach
                </div>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeModal('modalPemasukan')" class="btn-action btn-danger" style="flex:1; justify-content:center;">Batal</button>
                <button type="submit" class="btn-action" style="flex:1; justify-content:center;">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Pemasukan / Pengeluaran (CashFlow) -->
<div id="modalEditCashFlow" class="modal-overlay">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header">
            <h3 id="editCfTitle">Edit Transaksi</h3>
            <button type="button" class="close-modal" onclick="closeModal('modalEditCashFlow')">&times;</button>
        </div>
        <form id="formEditCashFlow" method="POST" novalidate>
            @csrf
            @method('PUT')
            
            <div class="form-group" style="border: 1px solid #ddd; border-radius: 8px; padding: 10px; margin-bottom: 15px;">
                <label style="font-size: 11px; color: #888; display: block;">Tanggal Transaksi</label>
                <input type="date" name="tanggal" id="editCfTanggalInput" class="form-control" style="border:none; padding:5px 0" required>
            </div>

            <div class="form-group">
                <label>Nominal *</label>
                <div class="nominal-wrapper">
                    <input type="number" name="nominal" id="editCfNominalInput" class="form-control" placeholder="0" required>
                </div>
            </div>
            
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-size: 11px; color: #888; display: block;">Metode Pembayaran *</label>
                <select name="metode_pembayaran" id="editCfMetodeInput" class="form-control" style="border: 1px solid #ddd; padding: 8px; border-radius: 8px;" required>
                    <option value="">-- Pilih Metode --</option>
                    @foreach($paymentMethods as $pm)
                        <option value="{{ $pm->uuid }}">{{ $pm->nama_metode }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group">
                <label>Keterangan</label>
                <textarea name="keterangan" id="editCfKeteranganInput" class="form-control" style="min-height: 80px;" placeholder="Tulis keterangan..."></textarea>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeModal('modalEditCashFlow')" class="btn-action btn-danger" style="flex:1; justify-content:center;">Batal</button>
                <button type="submit" class="btn-action" style="flex:1; justify-content:center; background: #007BFF;">Update</button>
            </div>
        </form>
    </div>
</div>

<div id="modalHutang" class="modal-overlay">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header">
            <h3>Tambah Hutang Baru</h3>
            <button type="button" class="close-modal" onclick="closeModal('modalHutang')">&times;</button>
        </div>
        <form action="{{ route('keuangan.debt.store') }}" method="POST" novalidate>
            @csrf
            @if($store_id === 'all')
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-size: 11px; color: #888; display: block;">Pilih Outlet Tujuan *</label>
                    <select name="store_id" class="form-control" style="border: 1px solid #ddd; padding: 8px; border-radius: 8px;" required>
                        <option value="">-- Pilih Outlet --</option>
                        @foreach($outlets as $o)
                            <option value="{{ $o->uuid }}">{{ $o->nama }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="store_id" value="{{ $store_id }}">
            @endif
            <input type="hidden" name="tipe" value="Hutang">
            
            <div class="form-group">
                <label>Supplier / Kontak *</label>
                <select name="kontak_nama" class="form-control" style="border-radius: 8px;" required>
                    <option value="">-- Pilih Supplier --</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->nama }}">{{ $supplier->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Total Nilai Hutang *</label>
                <div class="nominal-wrapper">
                    <input type="number" name="nominal" class="form-control" placeholder="0" required>
                </div>
            </div>
            <div class="form-group">
                <label>Opsi: Uang Muka / DP</label>
                <div class="nominal-wrapper">
                    <input type="number" name="uang_muka" class="form-control" placeholder="Masukkan jika ada DP">
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-size: 11px; color: #888; display: block;">Metode Pembayaran (Untuk DP)</label>
                <select name="metode_pembayaran" class="form-control" style="border: 1px solid #ddd; padding: 8px; border-radius: 8px;">
                    <option value="">-- Pilih Metode --</option>
                    @foreach($paymentMethods as $pm)
                        <option value="{{ $pm->uuid }}">{{ $pm->nama_metode }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Jatuh Tempo *</label>
                <input type="date" name="jatuh_tempo" class="form-control" required>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeModal('modalHutang')" class="btn-action btn-danger" style="flex:1; justify-content:center;">Batal</button>
                <button type="submit" class="btn-action" style="flex:1; justify-content:center;">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div id="modalPiutang" class="modal-overlay">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header">
            <h3>Tambah Piutang Baru</h3>
            <button type="button" class="close-modal" onclick="closeModal('modalPiutang')">&times;</button>
        </div>
        <form action="{{ route('keuangan.debt.store') }}" method="POST" novalidate>
            @csrf
            @if($store_id === 'all')
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-size: 11px; color: #888; display: block;">Pilih Outlet Tujuan *</label>
                    <select name="store_id" class="form-control" style="border: 1px solid #ddd; padding: 8px; border-radius: 8px;" required>
                        <option value="">-- Pilih Outlet --</option>
                        @foreach($outlets as $o)
                            <option value="{{ $o->uuid }}">{{ $o->nama }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="store_id" value="{{ $store_id }}">
            @endif
            <input type="hidden" name="tipe" value="Piutang">
            
            <div class="form-group">
                <label>Customer / Kontak *</label>
                <select name="kontak_nama" class="form-control" style="border-radius: 8px;" required>
                    <option value="">-- Pilih Customer --</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->nama }}">{{ $customer->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Total Nilai Piutang *</label>
                <div class="nominal-wrapper">
                    <input type="number" name="nominal" class="form-control" placeholder="0" required>
                </div>
            </div>
            <div class="form-group">
                <label>Opsi: Uang Muka / DP</label>
                <div class="nominal-wrapper">
                    <input type="number" name="uang_muka" class="form-control" placeholder="Masukkan jika ada DP">
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-size: 11px; color: #888; display: block;">Metode Pembayaran (Untuk DP)</label>
                <select name="metode_pembayaran" class="form-control" style="border: 1px solid #ddd; padding: 8px; border-radius: 8px;">
                    <option value="">-- Pilih Metode --</option>
                    @foreach($paymentMethods as $pm)
                        <option value="{{ $pm->uuid }}">{{ $pm->nama_metode }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Jatuh Tempo *</label>
                <input type="date" name="jatuh_tempo" class="form-control" required>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeModal('modalPiutang')" class="btn-action btn-danger" style="flex:1; justify-content:center;">Batal</button>
                <button type="submit" class="btn-action" style="flex:1; justify-content:center;">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Detail Dept -->
<div id="modalDetailDebt" class="modal-overlay">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3 id="debtDetailTitle">Detail Tagihan</h3>
            <button type="button" class="close-modal" onclick="closeModal('modalDetailDebt')">&times;</button>
        </div>
        
        <div style="background: #f8fbff; padding: 20px; border-radius: 12px; margin-bottom: 20px; border: 1px solid #dbeafe;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <div style="font-weight: 600; color: #333;" id="debtContactName"><iconify-icon icon="solar:user-bold-duotone" style="color: var(--primary-blue)"></iconify-icon> Nama</div>
                <div id="debtStatus" class="status-badge">Status</div>
            </div>
            <div style="font-size: 12px; color: #666; margin-bottom: 15px;" id="debtDueDate">
                <iconify-icon icon="solar:calendar-bold-duotone"></iconify-icon> Jatuh tempo: -
            </div>
            
            <div style="display: flex; justify-content: space-between; padding-bottom: 10px; margin-bottom: 5px;">
                <div>
                    <div style="font-size: 11px; color: #666;">Total Tagihan</div>
                    <div style="font-weight: 700; font-size: 16px; color: #333;" id="debtTotal">Rp 0</div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 11px; color: #666;">Sisa Tagihan</div>
                    <div style="font-weight: 700; font-size: 18px; color: var(--primary-blue);" id="debtSisa">Rp 0</div>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div style="margin-bottom: 15px; border-bottom: 1px dashed #bfdbfe; padding-bottom: 15px;">
                <div style="display: flex; justify-content: space-between; font-size: 11px; color: #666; margin-bottom: 6px; font-weight: 600;">
                    <span id="debtPaidText">Sudah Dibayar: Rp 0</span>
                    <span id="debtProgressText">0%</span>
                </div>
                <div style="width: 100%; height: 10px; background: #e2e8f0; border-radius: 5px; overflow: hidden; box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);">
                    <div id="debtProgressBar" style="width: 0%; height: 100%; background: linear-gradient(90deg, #3b82f6, #60a5fa); transition: width 1.5s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.5s;"></div>
                </div>
            </div>
            
            <form id="formPayDebt" method="POST">
                @csrf
                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 150px;">
                        <div class="nominal-wrapper">
                            <input type="number" name="bayar" class="form-control" style="margin: 0;" placeholder="0" required>
                        </div>
                    </div>
                    <div style="flex: 1; min-width: 150px;">
                        <select name="metode_pembayaran" class="form-control" style="margin: 0; border: 1px solid #ddd; padding: 8px; border-radius: 8px;" required>
                            <option value="">-- Pilih Metode --</option>
                            @foreach($paymentMethods as $pm)
                                <option value="{{ $pm->uuid }}">{{ $pm->nama_metode }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn-action" style="white-space: nowrap; background: #2E7D32;">Topup / Bayar</button>
                </div>
            </form>
        </div>

        <div id="debtProductSection" style="display: none; margin-bottom: 20px;">
            <h4 style="font-size: 13px; margin-bottom: 10px; color: #333; font-weight: 600;">Daftar Produk yang Dihutang</h4>
            <div id="debtProductList" style="display: flex; flex-direction: column; gap: 8px; max-height: 150px; overflow-y: auto;">
                <!-- Filled by JS -->
            </div>
        </div>

        <h4 style="font-size: 13px; margin-bottom: 10px; color: #333; font-weight: 600;">Log Pembayaran</h4>
        <div id="debtHistoryList" style="display: flex; flex-direction: column; gap: 8px; max-height: 200px; overflow-y: auto;"></div>

        <div style="display: flex; justify-content: center; margin-top: 25px;">
            <button type="button" class="btn-action" style="background: #f1f5f9; color: #475569; width: 100%; justify-content: center;" onclick="closeModal('modalDetailDebt')">Tutup</button>
        </div>
    </div>
</div>

<!-- Modal Edit Debt -->
<div id="modalEditDebt" class="modal-overlay">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header">
            <h3>Edit Tagihan</h3>
            <button type="button" class="close-modal" onclick="closeModal('modalEditDebt')">&times;</button>
        </div>
        <form id="formEditDebtAction" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label>Nama / Kontak *</label>
                <input type="text" id="editDebtKontak" name="kontak_nama" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Total Nilai *</label>
                <div class="nominal-wrapper">
                    <input type="number" id="editDebtNominal" name="nominal" class="form-control" placeholder="0" required>
                </div>
            </div>
            <div class="form-group">
                <label>Jatuh Tempo *</label>
                <input type="date" id="editDebtJatuhTempo" name="jatuh_tempo" class="form-control" required>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeModal('modalEditDebt')" class="btn-action btn-danger" style="flex:1; justify-content:center;">Batal</button>
                <button type="submit" class="btn-action" style="flex:1; justify-content:center; background: #007BFF;">Update</button>
            </div>
        </form>
    </div>
</div>


<!-- Modal Export -->
<div id="modalExport" class="modal-overlay">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header">
            <h3>Export Data <span id="exportFormatLabel"></span></h3>
            <button type="button" class="close-modal" onclick="closeModal('modalExport')">&times;</button>
        </div>
        <form id="formExport" method="GET" action="{{ route('keuangan.export') }}">
            <input type="hidden" name="format" id="exportFormatInput" value="">
            
            @if(Auth::user()->role === 'owner' || (Auth::user()->role === 'kepala_toko' && $outlets->count() > 1))
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px;">Pilih Outlet</label>
                    <select name="store_id" class="form-control" required>
                        @if(Auth::user()->role === 'owner')
                            <option value="all" {{ $store_id === 'all' ? 'selected' : '' }}>Semua Outlet</option>
                        @endif
                        @foreach($outlets as $o)
                            <option value="{{ $o->uuid }}" {{ $store_id == $o->uuid ? 'selected' : '' }}>{{ $o->nama }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="store_id" value="{{ $store_id }}">
            @endif
            
            <div class="form-group">
                <label style="display: block; margin-bottom: 8px;">Pilih Data yang Diekstrak</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; padding: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600; color: var(--primary-blue); grid-column: span 2; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 4px;">
                        <input type="checkbox" id="checkAllKategori" onchange="toggleAllKategori(this)"> Semua Kategori
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: normal;">
                        <input type="checkbox" name="kategori[]" value="Pemasukan" class="export-checkbox" onchange="checkKategoriStatus()"> Pemasukan
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: normal;">
                        <input type="checkbox" name="kategori[]" value="Pengeluaran" class="export-checkbox" onchange="checkKategoriStatus()"> Pengeluaran
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: normal;">
                        <input type="checkbox" name="kategori[]" value="Hutang" class="export-checkbox" onchange="checkKategoriStatus()"> Hutang
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: normal;">
                        <input type="checkbox" name="kategori[]" value="Piutang" class="export-checkbox" onchange="checkKategoriStatus()"> Piutang
                    </label>
                </div>
            </div>

            <div class="form-group" style="margin-top: 15px;">
                <label>Rentang Waktu (Opsional)</label>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="date" name="start_date" class="form-control" style="flex: 1;">
                    <span>-</span>
                    <input type="date" name="end_date" class="form-control" style="flex: 1;">
                </div>
                <small style="color: #888; font-size: 11px;">Kosongkan jika ingin mengekstrak semua tanggal.</small>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 25px;">
                <button type="button" onclick="closeModal('modalExport')" class="btn-action btn-danger" style="flex:1; justify-content:center;">Batal</button>
                <button type="submit" class="btn-action" style="flex:1; justify-content:center; background: #2E7D32;" onclick="setTimeout(() => closeModal('modalExport'), 1000)">
                    <iconify-icon icon="solar:download-bold-duotone"></iconify-icon> Download
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleDropdown(event) {
        event.stopPropagation();
        const dropdown = event.currentTarget.nextElementSibling;
        document.querySelectorAll('.dropdown-content').forEach(el => {
            if (el !== dropdown) el.classList.remove('show');
        });
        dropdown.classList.toggle('show');
    }

    // Close dropdowns when clicking outside
    window.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-content').forEach(d => d.classList.remove('show'));
        }
    });

    let currentTab = '{{ $active_tab }}';

    document.addEventListener('DOMContentLoaded', function () {
        // Auto-hide success alert
        let alertObj = document.getElementById('alertSuccess');
        if (alertObj) {
            setTimeout(() => {
                alertObj.style.opacity = '0';
                setTimeout(() => alertObj.style.display = 'none', 500);
            }, 3000);
        }
        
        // Anti-Future Date Restriction
        const today = new Date().toISOString().split('T')[0];
        const monthToday = new Date().toISOString().slice(0, 7);
        document.querySelectorAll('input[type="date"]').forEach(el => {
            if (el.name !== 'jatuh_tempo' && !el.hasAttribute('min')) {
                el.setAttribute('max', today);
            }
        });
        document.querySelectorAll('input[type="month"]').forEach(el => {
            el.setAttribute('max', monthToday);
        });

        // Form Validation
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            if (form.id !== 'storeForm' && form.id !== 'formExport') {
                form.addEventListener('submit', function (event) {
                    let isValid = true;
                    form.querySelectorAll('[required]').forEach(field => {
                        const formGroup = field.closest('.form-group');
                        let feedback = formGroup ? formGroup.querySelector('.invalid-feedback') : null;
                        if (!field.value || field.value.trim() === '' || field.value === '0') {
                            field.style.borderColor = '#dc3545';
                            if (feedback) feedback.style.display = 'block';
                            isValid = false;
                        } else {
                            field.style.borderColor = '';
                            if (feedback) feedback.style.display = 'none';
                        }
                    });
                    if (!isValid) event.preventDefault();
                });
            }
        });

        // Init Default Tab
        switchTab(currentTab);
        document.querySelectorAll('.current-tab-input').forEach(el => el.value = currentTab);

        // Add Rp prefix logic to nominal inputs
        document.querySelectorAll('input[type="number"][name*="nominal"], input[type="number"][name*="bayar"], input[type="number"][name*="uang_muka"]').forEach(el => {
            el.placeholder = '0';
        });
    });

    function switchTab(tab) {
        currentTab = tab;
        location.hash = tab;
        
        // Hide all views
        const views = ['view-pengeluaran', 'view-pemasukan', 'view-hutang', 'view-piutang'];
        views.forEach(id => {
            const el = document.getElementById(id);
            if(el) el.style.display = 'none';
        });
        
        // Remove active class from all pills
        document.querySelectorAll('.tab-pill').forEach(p => p.classList.remove('active'));
        
        // Show selected view and activate pill
        const view = document.getElementById('view-' + tab);
        const pill = document.getElementById('pill-' + tab);
        if(view) view.style.display = 'block';
        if(pill) pill.classList.add('active');

        // Update context for search and add button
        const btnAdd = document.getElementById('btnAddMain');
        const txtAdd = document.getElementById('txtAddMain');
        const searchInput = document.getElementById('globalSearch');
        const statusFilter = document.getElementById('statusFilterDropdown');

        if (tab === 'pengeluaran') {
            btnAdd.style.display = 'flex';
            txtAdd.innerText = 'Tambah Pengeluaran';
            searchInput.placeholder = 'Cari pengeluaran...';
            statusFilter.style.display = 'none';
        } else if (tab === 'pemasukan') {
            btnAdd.style.display = 'flex';
            txtAdd.innerText = 'Tambah Pemasukan Lainnya';
            searchInput.placeholder = 'Cari pemasukan...';
            statusFilter.style.display = 'none';
        } else if (tab === 'hutang') {
            btnAdd.style.display = 'flex';
            txtAdd.innerText = 'Tambah Hutang';
            searchInput.placeholder = 'Cari supplier...';
            statusFilter.style.display = 'block';
        } else if (tab === 'piutang') {
            btnAdd.style.display = 'flex';
            txtAdd.innerText = 'Tambah Piutang';
            searchInput.placeholder = 'Cari customer...';
            statusFilter.style.display = 'block';
        }

        filterTable();
    }

    // Auto switch to tab from hash or server-side active_tab
    window.addEventListener('DOMContentLoaded', () => {
        let tab = location.hash.replace('#', '') || '{{ $active_tab }}';
        if (['pengeluaran', 'pemasukan', 'hutang', 'piutang'].includes(tab)) {
            switchTab(tab);
        } else {
            switchTab('pengeluaran');
        }
    });

    function applyStatusFilter(status) {
        document.getElementById('storeFormActiveTab').value = currentTab;
        document.getElementById('storeFormStatus').value = status;
        document.getElementById('storeForm').submit();
    }

    function updatePickerInputs(val) {
        document.getElementById('pickerHarian').style.display = (val === 'harian') ? 'block' : 'none';
        document.getElementById('pickerBulanan').style.display = (val === 'bulanan') ? 'block' : 'none';
        document.getElementById('pickerTahunan').style.display = (val === 'tahunan') ? 'block' : 'none';
    }

    function openCurrentModal() {
        if(currentTab == 'pengeluaran') openModal('modalPengeluaran');
        else if(currentTab == 'pemasukan') openModal('modalPemasukan');
        else if(currentTab == 'hutang') openModal('modalHutang');
        else if(currentTab == 'piutang') openModal('modalPiutang');
    }

    function openModal(id) { 
        document.getElementById(id).style.display = 'flex'; 
    }
    
    function closeModal(id) { 
        document.getElementById(id).style.display = 'none'; 
    }

    function toggleAllKategori(source) {
        document.querySelectorAll('.export-checkbox').forEach(cb => cb.checked = source.checked);
    }

    function checkKategoriStatus() {
        const total = document.querySelectorAll('.export-checkbox').length;
        const checked = document.querySelectorAll('.export-checkbox:checked').length;
        document.getElementById('checkAllKategori').checked = (total === checked && total > 0);
    }

    function applyBukuKasRangeFilter() {
        const start = document.getElementById('filterStartDate').value;
        const end = document.getElementById('filterEndDate').value;
        if (!start || !end) {
            Swal.fire('Peringatan', 'Harap pilih rentang tanggal yang lengkap.', 'warning');
            return;
        }
        window.location.href = `{{ route('keuangan.transaksi') }}?period=harian&start_date=${start}&end_date=${end}&active_tab=${currentTab}&store_id={{ $store_id }}`;
    }

    function filterTableByDate(val) {
        const filter = val;
        const tables = ['tbody-pengeluaran', 'tbody-pemasukan', 'tbody-hutang', 'tbody-piutang'];
        
        tables.forEach(tbodyId => {
            const tbody = document.getElementById(tbodyId);
            if (!tbody) return;
            const rows = tbody.getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                if (rows[i].classList.contains('empty-state')) continue;
                const dateCell = rows[i].getElementsByTagName('td')[0];
                if (dateCell) {
                    const cellText = dateCell.textContent.trim();
                    if (!filter) {
                        rows[i].style.display = '';
                        continue;
                    }
                    const [fYear, fMonth, fDay] = filter.split('-');
                    const formattedFilter = `${fDay}/${fMonth}/${fYear}`;
                    rows[i].style.display = (cellText === formattedFilter) ? '' : 'none';
                }
            }
        });
    }

    function openExportModal(format) {
        document.getElementById('exportFormatInput').value = format;
        document.getElementById('exportFormatLabel').innerText = format.toUpperCase();
        
        const inputStart = document.querySelector('input[name="start_date"]');
        const inputEnd = document.querySelector('input[name="end_date"]');
        
        if (inputStart && inputEnd) {
            document.querySelector('#modalExport input[name="start_date"]').value = inputStart.value;
            document.querySelector('#modalExport input[name="end_date"]').value = inputEnd.value;
        }

        document.querySelectorAll('.export-checkbox').forEach(cb => cb.checked = false);
        if (currentTab === 'pengeluaran') document.querySelector('.export-checkbox[value="Pengeluaran"]').checked = true;
        else if (currentTab === 'pemasukan') document.querySelector('.export-checkbox[value="Pemasukan"]').checked = true;
        else if (currentTab === 'hutang') document.querySelector('.export-checkbox[value="Hutang"]').checked = true;
        else if (currentTab === 'piutang') document.querySelector('.export-checkbox[value="Piutang"]').checked = true;
        
        checkKategoriStatus();
        openModal('modalExport');
    }

    function filterTable() {
        const searchText = document.getElementById('globalSearch').value.toLowerCase();

        const rows = document.querySelectorAll(`#tbody-${currentTab} tr`);
        rows.forEach(row => {
            if(row.querySelector('.empty-state')) return;
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(searchText) ? '' : 'none';
        });
    }

    function viewCashFlowDetail(cf) {
        const isPemasukan = cf.jenis.toLowerCase() === 'pemasukan';
        const iconDiv = document.getElementById('cfIcon');
        iconDiv.style.background = isPemasukan ? '#E8F5E9' : '#FFEBEE';
        iconDiv.style.color = isPemasukan ? '#2E7D32' : '#C62828';
        iconDiv.innerHTML = isPemasukan ? '<iconify-icon icon="solar:round-arrow-right-up-bold-duotone"></iconify-icon>' : '<iconify-icon icon="solar:round-arrow-left-down-bold-duotone"></iconify-icon>';

        let jenisName = cf.jenis.charAt(0).toUpperCase() + cf.jenis.slice(1).toLowerCase();
        if (isPemasukan) jenisName += ' Lainnya';
        
        document.getElementById('cfTitle').innerText = 'Detail ' + jenisName;
        const formatter = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });
        document.getElementById('cfNominal').innerText = 'Rp ' + formatter.format(cf.nominal);
        document.getElementById('cfKeterangan').innerText = (cf.keterangan || '-').replace(/\(Trx: [a-f0-9-]{36}\)/i, '(Otomatis)');
        
        let dateObj = new Date(cf.created_at || cf.tanggal);
        let timeStr = dateObj.toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'});
        document.getElementById('cfTanggal').innerText = dateObj.toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'}) + ' ' + timeStr;
        document.getElementById('cfToko').innerText = cf.outlet ? cf.outlet.nama : '-';
        document.getElementById('cfKaryawan').innerText = cf.user ? (cf.user.username || cf.user.name || '-') : '-';
        document.getElementById('cfMetode').innerText = cf.payment_method ? cf.payment_method.nama_metode : '-';
        
        const productSection = document.getElementById('cfProductSection');
        const productList = document.getElementById('cfProductList');
        productList.innerHTML = '<div style="text-align:center; padding:10px;"><iconify-icon icon="line-md:loading-twotone-loop" style="font-size:24px; color:var(--primary-blue);"></iconify-icon></div>';
        
        let items = [];
        if (cf.transaction && cf.transaction.details) {
            if (cf.transaction.user) document.getElementById('cfKaryawan').innerText = cf.transaction.user.name;
            const isPurchase = cf.transaction.jenis === 'pembelian';
            items = cf.transaction.details.map(d => ({
                nama: d.product ? (d.product.nama_produk || d.product.nama) : 'Produk Tidak Dikenal',
                qty: d.jmlh,
                harga: isPurchase ? (d.harga_modal || d.harga_jual) : d.harga_jual
            }));
        } else if (cf.payment_order && cf.payment_order.items) {
            items = cf.payment_order.items.map(d => ({
                nama: d.product_name,
                qty: d.quantity,
                harga: d.price
            }));
        }

        if (items.length > 0) {
            renderProductList(items, productList, productSection);
        } else if (cf.extracted_trx_id) {
            fetch(`/buku-kas/reference-detail/${cf.extracted_trx_id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.user) document.getElementById('cfKaryawan').innerText = data.user;
                        if (data.store) document.getElementById('cfToko').innerText = data.store;
                        if (data.formatted_date) document.getElementById('cfTanggal').innerText = data.formatted_date;
                        renderProductList(data.items, productList, productSection);
                    } else {
                        productList.innerHTML = '<div style="text-align:center; color:#999; padding:10px;">Data produk tidak tersedia</div>';
                        productSection.style.display = 'none';
                    }
                })
                .catch(() => { productSection.style.display = 'none'; });
        } else {
            productSection.style.display = 'none';
        }
        openModal('modalDetailCashFlow');
    }

    function renderProductList(items, container, section) {
        const formatter = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });
        container.innerHTML = '';
        if (items.length > 0) {
            section.style.display = 'block';
            items.forEach(item => {
                container.innerHTML += `
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px; display: flex; justify-content: space-between; align-items: center;">
                        <div style="flex: 1;">
                            <div style="font-weight: 600; font-size: 12px; color: #1e293b;">${item.nama}</div>
                            <div style="font-size: 11px; color: #64748b;">${item.qty} x Rp ${formatter.format(item.harga)}</div>
                        </div>
                        <div style="font-weight: 700; font-size: 12px; color: #0f172a;">Rp ${formatter.format(item.qty * item.harga)}</div>
                    </div>`;
            });
        } else {
            section.style.display = 'none';
        }
    }

    function openEditCashFlow(cf) {
        const isPemasukan = cf.jenis.toLowerCase() === 'pemasukan';
        let jenisName = cf.jenis.charAt(0).toUpperCase() + cf.jenis.slice(1).toLowerCase();
        if (isPemasukan) jenisName += ' Lainnya';
        
        document.getElementById('editCfTitle').innerText = 'Edit ' + jenisName;
        document.getElementById('formEditCashFlow').action = '/buku-kas/cashflow/' + cf.uuid;
        let d = new Date(cf.tanggal);
        let localISOTime = (new Date(d.getTime() - (d.getTimezoneOffset() * 60000))).toISOString().slice(0, 10);
        document.getElementById('editCfTanggalInput').value = localISOTime;
        document.getElementById('editCfNominalInput').value = cf.nominal;
        document.getElementById('editCfMetodeInput').value = cf.metode_pembayaran || '';
        document.getElementById('editCfKeteranganInput').value = cf.keterangan;
        openModal('modalEditCashFlow');
    }

    function deleteCf(id, typeName) {
        Swal.fire({
            title: `Hapus ${typeName}?`,
            text: "Data ini akan dihapus secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#aaa',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                let form = document.getElementById('formGlobalDeleteCf');
                form.action = '/buku-kas/cashflow/' + id;
                form.submit();
            }
        });
    }

    function viewDebtDetail(debt, contact, details) {
        document.getElementById('debtDetailTitle').innerText = 'Detail ' + debt.tipe;
        document.getElementById('debtContactName').innerHTML = contact ? contact.nama : '-';
        
        const formatID = (date) => {
            if(!date) return '-';
            let d = new Date(date);
            return d.toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'});
        };

        let rawDate = debt.created_at || (debt.transaction ? debt.transaction.created_at : null) || (debt.payment_order ? debt.payment_order.created_at : null);
        
        const updateDateUI = (dibuat, tempo) => {
            document.getElementById('debtDueDate').innerHTML = `
                <div style="display:flex; flex-direction:column; gap:4px;">
                    <div><iconify-icon icon="solar:calendar-bold-duotone" style="color:var(--primary-blue)"></iconify-icon> <strong>Tgl Dibuat:</strong> <span id="valTglDibuat">${formatID(dibuat)}</span></div>
                    <div><iconify-icon icon="solar:alarm-bold-duotone" style="color:#d32f2f"></iconify-icon> <strong>Tempo:</strong> ${formatID(tempo)}</div>
                    <div id="debtAsalTrx" style="font-size:10px; color:#888; margin-top:4px;">Asal: Mencari...</div>
                </div>`;
        };

        updateDateUI(rawDate, debt.jatuh_tempo);

        const formatter = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });
        document.getElementById('debtTotal').innerText = 'Rp ' + formatter.format(debt.nominal);
        document.getElementById('debtSisa').innerText = 'Rp ' + formatter.format(debt.sisa);
        const badge = document.getElementById('debtStatus');
        const progressBar = document.getElementById('debtProgressBar');
        const paidText = document.getElementById('debtPaidText');
        const progressText = document.getElementById('debtProgressText');
        const paidAmount = debt.nominal - debt.sisa;
        let percentage = Math.min(100, Math.max(0, (paidAmount / debt.nominal) * 100));
        progressBar.style.width = '0%';
        paidText.innerText = 'Sudah Dibayar: Rp ' + formatter.format(paidAmount);
        progressText.innerText = percentage.toFixed(0) + '%';
        setTimeout(() => { progressBar.style.width = percentage + '%'; }, 100);

        if (debt.sisa <= 0) {
            badge.innerText = 'Lunas'; badge.className = 'status-badge status-lunas';
            document.getElementById('formPayDebt').style.display = 'none';
            progressBar.style.background = 'linear-gradient(90deg, #10b981, #34d399)';
        } else {
            badge.innerText = 'Belum Lunas'; badge.className = 'status-badge status-belum';
            document.getElementById('formPayDebt').style.display = 'flex';
            progressBar.style.background = 'linear-gradient(90deg, #3b82f6, #60a5fa)';
        }
        document.getElementById('formPayDebt').action = '/buku-kas/debt/' + debt.uuid + '/pay';

        const debtProductSection = document.getElementById('debtProductSection');
        const debtProductList = document.getElementById('debtProductList');
        debtProductList.innerHTML = '<div style="text-align:center; padding:10px;"><iconify-icon icon="line-md:loading-twotone-loop" style="font-size:24px; color:var(--primary-blue);"></iconify-icon></div>';
        
        let debtItems = [];
        if (debt.transaction && debt.transaction.details) {
            debtItems = debt.transaction.details.map(d => ({ nama: d.product ? (d.product.nama_produk || d.product.nama) : 'Produk Tidak Dikenal', qty: d.jmlh, harga: d.harga_modal || d.harga_jual }));
        } else if (debt.payment_order && debt.payment_order.items) {
            debtItems = debt.payment_order.items.map(d => ({ nama: d.product_name, qty: d.quantity, harga: d.price }));
        }

        if (debtItems.length > 0) { 
            renderProductList(debtItems, debtProductList, debtProductSection); 
            document.getElementById('debtAsalTrx').innerText = 'Asal: Transaksi Otomatis';
        } else if (debt.reference_id) {
            const asalLabel = document.getElementById('debtAsalTrx');
            fetch(`/buku-kas/reference-detail/${debt.reference_id}`).then(response => response.json()).then(data => {
                if (data.success) {
                    asalLabel.innerText = 'Asal: ' + (data.type === 'restok' ? 'Pembelian/Restok' : 'Penjualan Toko');
                    renderProductList(data.items, debtProductList, debtProductSection);
                    // Update Tgl Dibuat if it was missing
                    if (!rawDate && data.created_at) {
                        document.getElementById('valTglDibuat').innerText = formatID(data.created_at);
                    }
                } else {
                    asalLabel.innerText = 'Asal: Input Manual';
                    debtProductSection.style.display = 'none';
                }
            }).catch(() => { asalLabel.innerText = 'Asal: Input Manual'; debtProductSection.style.display = 'none'; });
        } else { 
            document.getElementById('debtAsalTrx').innerText = 'Asal: Input Manual'; 
            debtProductSection.style.display = 'none'; 
        }
        const list = document.getElementById('debtHistoryList');
        list.innerHTML = details.length === 0 ? '<div style="font-size:12px; color:#888; text-align:center; padding: 10px;">Belum ada riwayat pembayaran</div>' : '';
        details.forEach(d => {
            let mLabel = d.payment_method ? d.payment_method.nama_metode : '-';
            let dDate = d.tanggal ? new Date(d.tanggal) : new Date();
            let dStr = dDate.toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'}) + ' ' + dDate.toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'});
            
            list.innerHTML += `<div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; gap: 8px; align-items: center;">
                    <div style="background: #e0f2fe; color: #0ea5e9; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center;"><iconify-icon icon="solar:money-bag-bold-duotone" style="font-size: 1.1rem;"></iconify-icon></div>
                    <div>
                        <div style="font-weight: 600; font-size: 13px; color: #333;">Bayar: Rp ${formatter.format(d.bayar)} (${mLabel})</div>
                        <div style="font-size: 11px; color: #666;">${dStr} • Sisa: Rp ${formatter.format(d.sisa)}</div>
                    </div>
                </div>
            </div>`;
        });
        openModal('modalDetailDebt');
    }

    function openEditDebt(debt, contact) {
        document.getElementById('formEditDebtAction').action = '/buku-kas/debt/' + debt.uuid;
        document.getElementById('editDebtKontak').value = contact ? contact.nama : '';
        document.getElementById('editDebtNominal').value = debt.nominal;
        document.getElementById('editDebtJatuhTempo').value = debt.jatuh_tempo;
        openModal('modalEditDebt');
    }

    function deleteDebt(id, typeName) {
        Swal.fire({ title: `Hapus ${typeName}?`, text: "Data ini beserta log cicilannya akan terhapus!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#aaa', confirmButtonText: 'Hapus', cancelButtonText: 'Batal' }).then((result) => { if (result.isConfirmed) { let form = document.getElementById('formGlobalDeleteDebt'); form.action = '/buku-kas/debt/' + id; form.submit(); } });
    }

    @if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '{{ session("success") }}',
        showConfirmButton: false,
        timer: 2000
    });
    @endif

    @if($errors->any())
    Swal.fire({
        icon: 'error',
        title: 'Terjadi Kesalahan!',
        html: '<ul style="text-align: left;">@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>',
    });
    @endif
</script>
@endsection
