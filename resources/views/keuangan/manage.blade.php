@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/fitur.css') }}">
<style>
    .view-section { display: none; }
    .view-section.active { display: block; animation: fadeIn 0.2s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

    /* Arus Uang Custom Styles */
    .finance-card-container { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 24px; }
    .finance-card { background: white; padding: 24px; border-radius: 24px; display: flex; align-items: center; gap: 20px; border: 1px solid #f1f5f9; transition: all 0.3s ease; }
    .finance-card:hover { transform: translateY(-5px); box-shadow: 0 12px 24px rgba(0,0,0,0.05); }
    .icon-box { width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 28px; }
    .bg-bersih { background: #eff6ff; color: #0081C9; }
    .bg-masuk { background: #f0fdf4; color: #16a34a; }
    .bg-keluar { background: #fff1f2; color: #dc2626; }
    .card-label { font-size: 13px; color: #64748b; font-weight: 500; }
    .card-value { font-size: 20px; font-weight: 700; color: #1e293b; margin-top: 4px; }
    .text-masuk { color: #16a34a; }
    .text-keluar { color: #dc2626; }
    .filter-pills { display: flex; gap: 8px; background: #f1f5f9; padding: 4px; border-radius: 12px; }
    .filter-pill { padding: 6px 16px; border: none; background: transparent; border-radius: 8px; font-size: 13px; font-weight: 600; color: #64748b; cursor: pointer; transition: 0.3s; }
    .filter-pill.active { background: white; color: #0081C9; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .badge-masuk { background: #dcfce7; color: #166534; }
    .badge-keluar { background: #fee2e2; color: #991b1b; }

    /* Modal & Validation */
    .is-invalid { border-color: #dc2626 !important; }
    .invalid-feedback { display: none; color: #dc2626; font-size: 12px; margin-top: 5px; font-weight: 600; }
    .is-invalid+.invalid-feedback { display: block !important; }
</style>
@endpush

@section('content')
<div class="fitur-container">
    @include('keuangan.partials.tabs')

    {{-- SECTION CASHBOX --}}
    <div id="view-cashbox" class="view-section">
        <div class="action-bar" style="margin-bottom: 20px;">
            <div class="left-actions-group">
                <div class="search-wrapper">
                    <iconify-icon icon="solar:magnifer-linear" class="search-icon"></iconify-icon>
                    <input type="text" id="cashboxSearch" class="search-input" placeholder="Cari nama cashbox..." onkeyup="filterCashbox()">
                </div>
            </div>
            <div class="right-actions">
                <button onclick="openModal('modalAddCashbox')" class="btn-action">
                    <iconify-icon icon="solar:add-circle-bold-duotone" style="font-size: 20px;"></iconify-icon>
                    <span>Tambah Cashbox</span>
                </button>
            </div>
        </div>

        <div class="main-content-box">
            <div class="table-container">
                <table class="fitur-table">
                    <thead>
                        <tr>
                            <th>Nama Metode</th>
                            <th style="width: 150px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-cashbox">
                        @forelse($cashboxes as $cb)
                            <tr>
                                <td style="font-weight: 600;">{{ $cb->nama_metode }}</td>
                                <td>
                                    <div style="display: flex; justify-content: center; gap: 10px;">
                                        <button class="btn-action" style="background: #eef2ff; color: #4f46e5;"
                                            onclick="openEditCashbox('{{ $cb->uuid }}', '{{ $cb->nama_metode }}')">
                                            <iconify-icon icon="solar:pen-bold-duotone"></iconify-icon>
                                            <span>Edit</span>
                                        </button>
                                        <button class="btn-action" style="background: #fef2f2; color: #ef4444;"
                                            onclick="deleteCashbox('{{ $cb->uuid }}', '{{ $cb->nama_metode }}')">
                                            <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                                            <span>Hapus</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="2" style="text-align: center; padding: 30px; color: #888;">Belum ada data Cashbox.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- SECTION ARUS UANG --}}
    <div id="view-arus-uang" class="view-section">
        <div class="action-bar-container">
            <form action="{{ route('keuangan.index') }}" method="GET" id="filterForm" class="action-bar" onchange="this.submit()">
                <input type="hidden" name="tab" value="arus-uang">
                <div class="left-actions-group">
                    <div class="search-wrapper">
                        <iconify-icon icon="solar:magnifer-linear" class="search-icon"></iconify-icon>
                        <input type="text" name="search" id="searchInput" class="search-input" value="{{ request('search') }}" placeholder="Cari keterangan, jenis, atau outlet..." onkeyup="realtimeSearch()">
                    </div>

                    <div class="dropdown">
                        <button type="button" class="btn-filter" onclick="toggleDropdown(event)">
                            <iconify-icon icon="solar:calendar-bold-duotone" style="font-size: 20px;"></iconify-icon>
                        </button>
                        <div class="dropdown-content" style="padding: 15px; width: 300px;">
                            <div style="display: flex; flex-direction: column; gap: 12px;">
                                <div>
                                    <label style="font-size: 11px; color: #888; display: block; margin-bottom: 4px;">Dari</label>
                                    <input type="date" name="start_date" class="form-control" value="{{ $start_date }}">
                                </div>
                                <div>
                                    <label style="font-size: 11px; color: #888; display: block; margin-bottom: 4px;">Sampai</label>
                                    <input type="date" name="end_date" class="form-control" value="{{ $end_date }}">
                                </div>
                                <button type="submit" class="btn-action" style="width: 100%; justify-content: center;">Terapkan</button>
                            </div>
                        </div>
                    </div>

                    @if(auth()->user()->role === 'owner')
                    <div class="dropdown">
                        <button type="button" class="btn-filter" onclick="toggleDropdown(event)">
                            <iconify-icon icon="solar:shop-bold-duotone" style="font-size: 20px;"></iconify-icon>
                        </button>
                        <div class="dropdown-content">
                            <a href="javascript:void(0)" onclick="setStore('all')">Semua Outlet</a>
                            @foreach($outlets as $outlet)
                                <a href="javascript:void(0)" onclick="setStore('{{ $outlet->uuid }}')">{{ $outlet->nama }}</a>
                            @endforeach
                        </div>
                    </div>
                    <input type="hidden" name="store_id" id="filter_store_id" value="{{ $store_id }}">
                    @endif
                </div>
                
                <div class="right-actions">
                    <button type="button" class="btn-action" onclick="exportToExcel()">
                        <iconify-icon icon="solar:file-download-bold-duotone"></iconify-icon>
                        <span>Export Excel</span>
                    </button>
                </div>
            </form>
        </div>

        <div class="main-content-box" style="background: transparent; padding: 0; box-shadow: none;">
            <div class="finance-card-container">
                <div class="finance-card">
                    <div class="icon-box bg-bersih"><iconify-icon icon="solar:wallet-money-bold-duotone"></iconify-icon></div>
                    <div class="card-info">
                        <div class="card-label">Saldo Bersih</div>
                        <div class="card-value">Rp {{ number_format($saldo_bersih, 0, ',', '.') }}</div>
                    </div>
                </div>
                <div class="finance-card">
                    <div class="icon-box bg-masuk"><iconify-icon icon="solar:round-arrow-left-down-bold-duotone"></iconify-icon></div>
                    <div class="card-info">
                        <div class="card-label">Saldo Masuk</div>
                        <div class="card-value text-masuk">Rp {{ number_format($pemasukan, 0, ',', '.') }}</div>
                    </div>
                </div>
                <div class="finance-card">
                    <div class="icon-box bg-keluar"><iconify-icon icon="solar:round-arrow-right-up-bold-duotone"></iconify-icon></div>
                    <div class="card-info">
                        <div class="card-label">Saldo Keluar</div>
                        <div class="card-value text-keluar">Rp {{ number_format($pengeluaran, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>

            <div class="table-container" style="background: white; padding: 24px; border-radius: 24px; border: 1px solid #f1f5f9;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <h3 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0;">Histori Transaksi</h3>
                    <div class="filter-pills">
                        <button type="button" class="filter-pill {{ !request('type') || request('type') == 'semua' ? 'active' : '' }}" onclick="setType('semua')">Semua</button>
                        <button type="button" class="filter-pill {{ request('type') == 'pemasukan' ? 'active' : '' }}" onclick="setType('pemasukan')">Masuk</button>
                        <button type="button" class="filter-pill {{ request('type') == 'pengeluaran' ? 'active' : '' }}" onclick="setType('pengeluaran')">Keluar</button>
                    </div>
                    <form id="typeFilterForm" action="{{ route('keuangan.index') }}" method="GET" style="display:none;">
                        <input type="hidden" name="tab" value="arus-uang">
                        <input type="hidden" name="type" id="filter_type">
                        @foreach(request()->except(['tab', 'type']) as $k => $v)
                            <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                        @endforeach
                    </form>
                </div>

                <table class="fitur-table" id="arusUangTable">
                    <thead>
                        <tr>
                            <th>TANGGAL</th>
                            <th>KETERANGAN</th>
                            <th>OUTLET</th>
                            <th>JENIS</th>
                            <th style="text-align: right;">NOMINAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $item)
                            <tr class="history-row">
                                <td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y') }} <br> <span style="font-size: 11px; color: #94a3b8;">{{ \Carbon\Carbon::parse($item->tanggal)->format('H:i') }}</span></td>
                                <td>
                                    <div style="font-weight: 600; color: #334155;">{{ $item->keterangan }}</div>
                                    <div style="font-size: 11px; color: #64748b;">Oleh: {{ $item->user->name ?? $item->user->username ?? '-' }}</div>
                                </td>
                                <td>{{ $item->outlet->nama ?? '-' }}</td>
                                <td><span class="status-badge {{ $item->jenis == 'pemasukan' ? 'badge-masuk' : 'badge-keluar' }}">{{ $item->jenis }}</span></td>
                                <td style="text-align: right; font-weight: 700; color: {{ $item->jenis == 'pemasukan' ? '#16a34a' : '#dc2626' }};">
                                    {{ $item->jenis == 'pemasukan' ? '+' : '-' }} Rp {{ number_format($item->nominal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" style="text-align: center; padding: 40px; color: #94a3b8;">Belum ada riwayat transaksi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="pagination-container" style="margin-top: 24px;">{{ $history->links() }}</div>
            </div>
        </div>
    </div>

    {{-- SECTION PEMINDAHAN SALDO --}}
    <div id="view-pemindahan-saldo" class="view-section">
        <div class="main-content-box">
            <x-coming-soon 
                title="Pemindahan Saldo" 
                icon="solar:card-transfer-bold-duotone" 
                description="Fitur Pemindahan Saldo sedang dikembangkan untuk memudahkan transfer antar akun kas atau outlet Anda."
            />
        </div>
    </div>
</div>

{{-- MODALS --}}
<div id="modalAddCashbox" class="modal-overlay">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header">
            <h3>Tambah Cashbox Baru</h3>
            <button type="button" class="close-modal" onclick="closeModal('modalAddCashbox')">&times;</button>
        </div>
        <form action="{{ route('keuangan.cashbox.store') }}" method="POST">
            @csrf
            <div class="form-group" style="margin-top: 15px;">
                <label>Nama Cashbox / Metode Pembayaran</label>
                <input type="text" name="nama_metode" class="form-control" placeholder="Contoh: Cash, QRIS, dll" required>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 25px;">
                <button type="button" onclick="closeModal('modalAddCashbox')" class="btn-action" style="flex:1; background:#f1f5f9; color:#64748b; justify-content:center;">Batal</button>
                <button type="submit" class="btn-action" style="flex:1; justify-content:center; background:#0081C9; color:white;">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEditCashbox" class="modal-overlay">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header">
            <h3>Edit Cashbox</h3>
            <button type="button" class="close-modal" onclick="closeModal('modalEditCashbox')">&times;</button>
        </div>
        <form id="formEditCashbox" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group" style="margin-top: 15px;">
                <label>Nama Cashbox / Metode Pembayaran</label>
                <input type="text" name="nama_metode" id="edit_nama_metode" class="form-control" required>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 25px;">
                <button type="button" onclick="closeModal('modalEditCashbox')" class="btn-action" style="flex:1; background:#f1f5f9; color:#64748b; justify-content:center;">Batal</button>
                <button type="submit" class="btn-action" style="flex:1; justify-content:center; background:#0081C9; color:white;">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<form id="formDeleteCashbox" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js" crossorigin="anonymous"></script>
<script>
    let currentTab = '{{ request('tab', 'cashbox') }}';

    window.addEventListener('DOMContentLoaded', () => {
        switchTab(currentTab);
    });

    function switchTab(tabId) {
        currentTab = tabId;
        document.querySelectorAll('.tab-pill').forEach(b => b.classList.remove('active'));
        let activePill = document.getElementById('pill-' + tabId);
        if(activePill) activePill.classList.add('active');
        
        document.querySelectorAll('.view-section').forEach(v => v.classList.remove('active'));
        let viewObj = document.getElementById('view-' + tabId);
        if(viewObj) viewObj.classList.add('active');

        // Update URL without reload
        const url = new URL(window.location);
        url.searchParams.set('tab', tabId);
        window.history.pushState({}, '', url);

        // Update Topbar Title and Icon Dynamically
        const titleEl = document.getElementById('topbar-title');
        const iconEl = document.getElementById('topbar-icon');
        if (titleEl && iconEl) {
            let title = 'Keuangan';
            let icon = 'trending-up';
            if (tabId === 'cashbox') { title = 'Cashbox'; icon = 'wallet'; }
            else if (tabId === 'arus-uang') { title = 'Arus Uang'; icon = 'arrow-left-right'; }
            else if (tabId === 'pemindahan-saldo') { title = 'Pemindahan Saldo'; icon = 'move'; }
            
            titleEl.innerText = title;
            iconEl.setAttribute('data-lucide', icon);
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    }

    function openModal(id) { document.getElementById(id).style.display = 'flex'; }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }

    function openEditCashbox(uuid, nama) {
        const form = document.getElementById('formEditCashbox');
        form.action = `/keuangan/cashbox/${uuid}`;
        document.getElementById('edit_nama_metode').value = nama;
        openModal('modalEditCashbox');
    }

    function deleteCashbox(uuid, nama) {
        Swal.fire({
            title: 'Hapus Cashbox?',
            text: `Hapus "${nama}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('formDeleteCashbox');
                form.action = `/keuangan/cashbox/${uuid}`;
                form.submit();
            }
        });
    }

    function filterCashbox() {
        const searchText = document.getElementById('cashboxSearch').value.toLowerCase();
        document.querySelectorAll('#tbody-cashbox tr').forEach(row => {
            if (row.cells.length < 2) return;
            row.style.display = row.cells[0].innerText.toLowerCase().includes(searchText) ? '' : 'none';
        });
    }

    function setStore(id) {
        document.getElementById('filter_store_id').value = id;
        document.getElementById('filterForm').submit();
    }

    function setType(type) {
        document.getElementById('filter_type').value = type;
        document.getElementById('typeFilterForm').submit();
    }

    function realtimeSearch() {
        const input = document.getElementById('searchInput').value.toLowerCase();
        document.querySelectorAll('.history-row').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(input) ? '' : 'none';
        });
    }

    function toggleDropdown(event) {
        event.stopPropagation();
        const dropdown = event.currentTarget.nextElementSibling;
        document.querySelectorAll('.dropdown-content').forEach(d => { if (d !== dropdown) d.classList.remove('show'); });
        dropdown.classList.toggle('show');
    }

    window.onclick = function(event) {
        if (!event.target.matches('.btn-filter') && !event.target.closest('.dropdown-content')) {
            document.querySelectorAll('.dropdown-content').forEach(d => d.classList.remove('show'));
        }
    }

    function exportToExcel() {
        const table = document.getElementById('arusUangTable');
        if (!table) return;
        const wb = XLSX.utils.table_to_book(table, {sheet: "Arus Uang"});
        XLSX.writeFile(wb, `Laporan_Arus_Uang.xlsx`);
    }
</script>
@endsection
