@extends('layouts.app')

@section('title', 'Buku Kas')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/fitur.css') }}">
<style>
    .view-section { display: none; }
    .view-section.active { display: block; animation: fadeIn 0.2s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

    .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
    .status-lunas { background: #E8F5E9; color: #2E7D32; }
    .status-belum { background: #FFF3E0; color: #E65100; }
    .chips-container { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
    .chip { background: #f1f5f9; color: #475569; padding: 6px 12px; border-radius: 20px; font-size: 0.8rem; cursor: pointer; transition: 0.2s; border: none; font-weight: 500; }
    .chip:hover { background: #e2e8f0; }
    .empty-state { text-align: center; padding: 40px; color: #999; }
    .is-invalid + .invalid-feedback { display: block !important; }

    /* Premium Scrollbar */
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: #f1f5f9; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

    .modal-content { max-height: 95vh; display: flex; flex-direction: column; overflow: hidden; padding: 0 !important; }
    .modal-header { padding: 20px 24px; border-bottom: 1px solid #f1f5f9; margin-bottom: 0 !important; }
    .modal-content form { display: flex; flex-direction: column; flex: 1; overflow: hidden; }
    .modal-body-scroll { flex: 1; overflow-y: auto; padding: 24px; }
    .modal-footer { padding: 20px 24px; border-top: 1px solid #f1f5f9; display: flex; gap: 12px; background: #fff; }
    .table-container { overflow-x: auto; }

    .nominal-wrapper { position: relative; display: flex; align-items: center; }
    .nominal-wrapper::before { content: "Rp"; position: absolute; left: 12px; font-weight: 700; color: #475569; font-size: 13px; pointer-events: none; }
    .nominal-wrapper input { padding-left: 35px !important; }
</style>
@endpush

@section('content')
<div class="fitur-container" id="bukukas-app">
    {{-- TAB NAVIGATION --}}
    @include('buku_kas.partials.tabs')

    {{-- ACTION BAR --}}
    <div class="action-bar">
        <div style="display: contents;">
            <div class="left-actions-group">
                <div class="search-wrapper">
                    <iconify-icon icon="solar:magnifer-linear" class="search-icon"></iconify-icon>
                    <input type="text" id="globalSearch" class="search-input" placeholder="Cari data..." onkeyup="filterTable()">
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
                        <button type="button" class="btn-action" style="width: 100%; justify-content: center; padding: 10px;" onclick="applyBukuKasRangeFilter()">Terapkan Filter</button>
                    </div>
                </div>

                @if(Auth::user()->role === 'owner' || (Auth::user()->role === 'kepala_toko' && $outlets->count() > 1))
                    <div class="dropdown">
                        <button type="button" class="btn-filter" title="Filter Toko" onclick="toggleDropdown(event)">
                            <iconify-icon icon="solar:shop-bold-duotone" style="font-size: 24px;" class="{{ $store_id != 'all' ? 'text-primary-blue' : '' }}"></iconify-icon>
                        </button>
                        <div class="dropdown-content">
                            <form id="storeForm" method="GET" action="{{ url()->current() }}">
                                <input type="hidden" name="store_id" id="storeFormStoreId" value="{{ $store_id }}">
                                <input type="hidden" name="tab" id="storeFormTab" value="{{ $active_tab }}">
                                <input type="hidden" name="start_date" value="{{ $start_date }}">
                                <input type="hidden" name="end_date" value="{{ $end_date }}">
                                <input type="hidden" name="status" value="{{ $status }}">
                            </form>
                            @if(Auth::user()->role === 'owner')
                                <a href="javascript:void(0)" onclick="document.getElementById('storeFormStoreId').value = 'all'; document.getElementById('storeForm').submit()" class="{{ $store_id === 'all' ? 'active-dropdown-item' : '' }}">Semua Outlet</a>
                            @endif
                            @foreach($outlets as $o)
                                <a href="javascript:void(0)" onclick="document.getElementById('storeFormStoreId').value = '{{ $o->uuid }}'; document.getElementById('storeForm').submit()" class="{{ $store_id == $o->uuid ? 'active-dropdown-item' : '' }}">{{ $o->nama }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Status Filter for Hutang/Piutang --}}
                <div class="dropdown" id="statusFilterDropdown" style="display: none;">
                    <button type="button" class="btn-filter" title="Filter Status" onclick="toggleDropdown(event)">
                        <iconify-icon icon="solar:filter-bold-duotone" style="font-size: 24px;" class="{{ $status ? 'text-primary-blue' : '' }}"></iconify-icon>
                    </button>
                    <div class="dropdown-content">
                        <a href="javascript:void(0)" onclick="applyStatusFilter('')" class="{{ !$status ? 'active-dropdown-item' : '' }}">Semua Status</a>
                        <a href="javascript:void(0)" onclick="applyStatusFilter('belum_lunas')" class="{{ $status == 'belum_lunas' ? 'active-dropdown-item' : '' }}">Belum Lunas</a>
                        <a href="javascript:void(0)" onclick="applyStatusFilter('lunas')" class="{{ $status == 'lunas' ? 'active-dropdown-item' : '' }}">Lunas</a>
                    </div>
                </div>
            </div>

            <div class="right-actions">
                <div class="dropdown">
                    <button type="button" class="btn-action" onclick="toggleDropdown(event)">
                        <iconify-icon icon="solar:document-text-bold-duotone"></iconify-icon>
                        <span>Extract</span>
                    </button>
                    <div class="dropdown-content">
                        <a href="javascript:void(0)" onclick="openExportModal('excel')"><iconify-icon icon="vscode-icons:file-type-excel" style="margin-right: 8px;"></iconify-icon> Excel</a>
                        <a href="javascript:void(0)" onclick="openExportModal('pdf')"><iconify-icon icon="vscode-icons:file-type-pdf" style="margin-right: 8px;"></iconify-icon> PDF</a>
                    </div>
                </div>
                
                {{-- Dynamic Add Button --}}
                <button type="button" class="btn-action" id="btnAddData" onclick="handleMainAdd()">
                    <iconify-icon icon="solar:add-circle-bold-duotone"></iconify-icon>
                    <span id="btnAddText">Tambah Data</span>
                </button>
            </div>
        </div>
    </div>

    {{-- SECTION PENGELUARAN --}}
    <div id="view-pengeluaran" class="view-section">
        <div class="main-content-box">
            <div class="table-container">
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
                    <tbody>
                        @forelse($pengeluaran as $p)
                        <tr class="row-pengeluaran">
                            <td>{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y H:i') }}</td>
                            <td>{{ $p->outlet->nama ?? '-' }}</td>
                            <td>{{ $p->user->username ?? ($p->user->name ?? '-') }}</td>
                            <td><strong>{{ preg_replace('/\(Trx: [a-f0-9-]{36}\)/i', '(Otomatis)', $p->keterangan) }}</strong></td>
                            <td class="price-text" style="color: #C62828;">- Rp {{ number_format($p->nominal, 0, ',', '.') }}</td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: var(--primary-blue);" data-item="{{ json_encode($p) }}" onclick="viewCashFlowDetail(JSON.parse(this.dataset.item))" title="Detail"><iconify-icon icon="solar:eye-bold-duotone"></iconify-icon></button>
                                    <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #FBC02D; border-color: #FFF9C4;" data-item="{{ json_encode($p) }}" onclick="openEditCashFlow(JSON.parse(this.dataset.item))" title="Edit"><iconify-icon icon="solar:pen-bold-duotone"></iconify-icon></button>
                                    <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #D9534F; border-color: #ffcccc;" onclick="deleteCf('{{ $p->uuid }}', '{{ $p->jenis }}')" title="Hapus"><iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon></button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="empty-state">Belum ada data pengeluaran.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- SECTION PEMASUKAN --}}
    <div id="view-pemasukan" class="view-section">
        <div class="main-content-box">
            <div class="table-container">
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
                    <tbody>
                        @forelse($pemasukan as $p)
                        <tr class="row-pemasukan">
                            <td>{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y H:i') }}</td>
                            <td>{{ $p->outlet->nama ?? '-' }}</td>
                            <td>{{ $p->user->username ?? ($p->user->name ?? '-') }}</td>
                            <td><strong>{{ preg_replace('/\(Trx: [a-f0-9-]{36}\)/i', '(Otomatis)', $p->keterangan) }}</strong></td>
                            <td class="price-text" style="color: #2E7D32;">+ Rp {{ number_format($p->nominal, 0, ',', '.') }}</td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: var(--primary-blue);" data-item="{{ json_encode($p) }}" onclick="viewCashFlowDetail(JSON.parse(this.dataset.item))" title="Detail"><iconify-icon icon="solar:eye-bold-duotone"></iconify-icon></button>
                                    <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #FBC02D; border-color: #FFF9C4;" data-item="{{ json_encode($p) }}" onclick="openEditCashFlow(JSON.parse(this.dataset.item))" title="Edit"><iconify-icon icon="solar:pen-bold-duotone"></iconify-icon></button>
                                    <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #D9534F; border-color: #ffcccc;" onclick="deleteCf('{{ $p->uuid }}', '{{ $p->jenis }}')" title="Hapus"><iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon></button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="empty-state">Belum ada data pemasukan lainnya.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- SECTION HUTANG --}}
    <div id="view-hutang" class="view-section">
        <div class="main-content-box">
            <div class="table-container">
                <table class="fitur-table">
                    <thead><tr><th>SUPPLIER</th><th>TOTAL HUTANG</th><th>SISA TAGIHAN</th><th>STATUS</th><th>JATUH TEMPO</th><th>AKSI</th></tr></thead>
                    <tbody>
                        @forelse($hutang as $h)
                        <tr class="row-hutang">
                            <td><strong>{{ $h->contact->nama ?? '-' }}</strong></td>
                            <td class="price-text">Rp {{ number_format($h->nominal, 0, ',', '.') }}</td>
                            <td class="price-text" style="color: var(--primary-blue);">Rp {{ number_format($h->sisa, 0, ',', '.') }}</td>
                            <td><span class="status-badge {{ $h->sisa <= 0 ? 'status-lunas' : 'status-belum' }}">{{ $h->sisa <= 0 ? 'Lunas' : 'Belum Lunas' }}</span></td>
                            <td>{{ \Carbon\Carbon::parse($h->jatuh_tempo)->format('d/m/Y') }}</td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: var(--primary-blue);" onclick="viewDebtDetail({{ json_encode($h) }}, {{ json_encode($h->contact) }}, {{ json_encode($h->detailDebts) }})" title="Detail"><iconify-icon icon="solar:eye-bold-duotone"></iconify-icon></button>
                                    <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #FBC02D; border-color: #FFF9C4;" onclick="openEditDebt({{ json_encode($h) }}, {{ json_encode($h->contact) }})" title="Edit"><iconify-icon icon="solar:pen-bold-duotone"></iconify-icon></button>
                                    <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #D9534F; border-color: #ffcccc;" onclick="deleteDebt('{{ $h->uuid }}', 'Hutang')" title="Hapus"><iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon></button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="empty-state">Belum ada data hutang supplier.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- SECTION PIUTANG --}}
    <div id="view-piutang" class="view-section">
        <div class="main-content-box">
            <div class="table-container">
                <table class="fitur-table">
                    <thead><tr><th>CUSTOMER</th><th>TOTAL PIUTANG</th><th>SISA TAGIHAN</th><th>STATUS</th><th>JATUH TEMPO</th><th>AKSI</th></tr></thead>
                    <tbody>
                        @forelse($piutang as $p)
                        <tr class="row-piutang">
                            <td><strong>{{ $p->contact->nama ?? '-' }}</strong></td>
                            <td class="price-text">Rp {{ number_format($p->nominal, 0, ',', '.') }}</td>
                            <td class="price-text" style="color: var(--primary-blue);">Rp {{ number_format($p->sisa, 0, ',', '.') }}</td>
                            <td><span class="status-badge {{ $p->sisa <= 0 ? 'status-lunas' : 'status-belum' }}">{{ $p->sisa <= 0 ? 'Lunas' : 'Belum Lunas' }}</span></td>
                            <td>{{ \Carbon\Carbon::parse($p->jatuh_tempo)->format('d/m/Y') }}</td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: var(--primary-blue);" onclick="viewDebtDetail({{ json_encode($p) }}, {{ json_encode($p->contact) }}, {{ json_encode($p->detailDebts) }})" title="Detail"><iconify-icon icon="solar:eye-bold-duotone"></iconify-icon></button>
                                    <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #FBC02D; border-color: #FFF9C4;" onclick="openEditDebt({{ json_encode($p) }}, {{ json_encode($p->contact) }})" title="Edit"><iconify-icon icon="solar:pen-bold-duotone"></iconify-icon></button>
                                    <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #D9534F; border-color: #ffcccc;" onclick="deleteDebt('{{ $p->uuid }}', 'Piutang')" title="Hapus"><iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon></button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="empty-state">Belum ada data piutang customer.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODALS CONSOLIDATED --}}
@include('buku_kas.partials.modals')

<form id="formGlobalDeleteCf" method="POST" style="display: none;">@csrf @method('DELETE')</form>
<form id="formGlobalDeleteDebt" method="POST" style="display: none;">@csrf @method('DELETE')</form>

<script>
    let currentTab = '{{ request('tab', $active_tab ?? 'pengeluaran') }}';

    window.addEventListener('DOMContentLoaded', () => {
        switchTab(currentTab);
    });

    function switchTab(tabId) {
        currentTab = tabId;
        
        // Tab UI
        document.querySelectorAll('.tab-pill').forEach(b => b.classList.remove('active'));
        let activePill = document.getElementById('pill-' + tabId);
        if(activePill) activePill.classList.add('active');
        
        // Visibility
        document.querySelectorAll('.view-section').forEach(v => v.classList.remove('active'));
        let viewObj = document.getElementById('view-' + tabId);
        if(viewObj) viewObj.classList.add('active');

        // URL Update
        const url = new URL(window.location);
        url.searchParams.set('tab', tabId);
        window.history.pushState({}, '', url);

        // UI Adjustments
        document.getElementById('statusFilterDropdown').style.display = (tabId === 'hutang' || tabId === 'piutang') ? 'block' : 'none';
        
        let addText = 'Tambah Pengeluaran';
        if(tabId === 'pemasukan') addText = 'Tambah Pemasukan';
        else if(tabId === 'hutang') addText = 'Tambah Hutang';
        else if(tabId === 'piutang') addText = 'Tambah Piutang';
        document.getElementById('btnAddText').innerText = addText;
    }

    function handleMainAdd() {
        if(currentTab === 'pengeluaran') openModal('modalPengeluaran');
        else if(currentTab === 'pemasukan') openModal('modalPemasukan');
        else if(currentTab === 'hutang') openModal('modalHutang');
        else if(currentTab === 'piutang') openModal('modalPiutang');
    }

    // Modal Helpers
    function openModal(id) { document.getElementById(id).style.display = 'flex'; }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }
    window.onclick = function(event) { if (event.target.classList.contains('modal-overlay')) event.target.style.display = 'none'; };

    // Dropdown Helpers
    function toggleDropdown(event) { event.stopPropagation(); const dropdown = event.currentTarget.nextElementSibling; document.querySelectorAll('.dropdown-content').forEach(d => { if (d !== dropdown) d.classList.remove('show'); }); dropdown.classList.toggle('show'); }
    document.addEventListener('click', () => { document.querySelectorAll('.dropdown-content').forEach(d => d.classList.remove('show')); });

    // Filter Helpers
    function applyBukuKasRangeFilter() { const start = document.getElementById('filterStartDate').value; const end = document.getElementById('filterEndDate').value; const url = new URL(window.location.href); url.searchParams.set('tab', currentTab); url.searchParams.set('start_date', start); url.searchParams.set('end_date', end); url.searchParams.set('period', 'harian'); window.location.href = url.toString(); }
    function applyStatusFilter(status) { const url = new URL(window.location.href); url.searchParams.set('tab', currentTab); url.searchParams.set('status', status); window.location.href = url.toString(); }
    function filterTable() { const search = document.getElementById('globalSearch').value.toLowerCase(); document.querySelectorAll('.view-section.active tbody tr').forEach(row => { if (row.querySelector('.empty-state')) return; row.style.display = row.innerText.toLowerCase().includes(search) ? '' : 'none'; }); }

    // CRUD Actions
    function deleteCf(uuid, jenis) { Swal.fire({ title: 'Hapus Data?', text: `Hapus data ${jenis} ini?`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33' }).then((result) => { if (result.isConfirmed) { const form = document.getElementById('formGlobalDeleteCf'); form.action = `/buku-kas/cashflow/${uuid}`; form.submit(); } }); }
    function deleteDebt(uuid, tipe) { Swal.fire({ title: 'Hapus Data?', text: `Hapus data ${tipe} ini?`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33' }).then((result) => { if (result.isConfirmed) { const form = document.getElementById('formGlobalDeleteDebt'); form.action = `/buku-kas/debt/${uuid}`; form.submit(); } }); }

    // Details & Edits (Injected from consolidated modals script)
    function viewCashFlowDetail(data) {
        const iconBox = document.getElementById('cfIcon'); const title = document.getElementById('cfTitle'); const nominal = document.getElementById('cfNominal'); const keterangan = document.getElementById('cfKeterangan'); const tanggal = document.getElementById('cfTanggal'); const toko = document.getElementById('cfToko'); const karyawan = document.getElementById('cfKaryawan'); const metode = document.getElementById('cfMetode'); const productSection = document.getElementById('cfProductSection'); const productList = document.getElementById('cfProductList');
        const isMasuk = data.jenis.toLowerCase() === 'pemasukan';
        title.innerText = isMasuk ? 'Detail Pemasukan Lainnya' : 'Detail Pengeluaran';
        iconBox.innerHTML = isMasuk ? '<iconify-icon icon="solar:round-arrow-right-up-bold-duotone"></iconify-icon>' : '<iconify-icon icon="solar:round-arrow-left-down-bold-duotone"></iconify-icon>';
        iconBox.style.background = isMasuk ? '#E8F5E9' : '#FFEBEE'; iconBox.style.color = isMasuk ? '#2E7D32' : '#C62828';
        nominal.innerText = (isMasuk ? '+ ' : '- ') + 'Rp ' + parseInt(data.nominal).toLocaleString('id-ID'); nominal.style.color = isMasuk ? '#2E7D32' : '#C62828';
        keterangan.innerText = data.keterangan.replace(/\(Trx: [a-f0-9-]{36}\)/i, '(Otomatis)');
        tanggal.innerText = new Date(data.tanggal).toLocaleString('id-ID', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        toko.innerText = data.outlet ? data.outlet.nama : '-'; karyawan.innerText = data.user ? (data.user.username || data.user.name) : '-'; metode.innerText = data.payment_method ? data.payment_method.nama_metode : '-';
        productSection.style.display = 'none'; productList.innerHTML = '';
        const refId = data.keterangan.match(/\(Trx: ([a-f0-9-]{36})\)/i);
        if (refId && refId[1]) { fetch(`/buku-kas/reference-detail/${refId[1]}`).then(res => res.json()).then(res => { if (res.success && res.items.length > 0) { productSection.style.display = 'block'; res.items.forEach(item => { productList.innerHTML += `<div style="display: flex; justify-content: space-between; font-size: 12px; padding: 5px 0; border-bottom: 1px solid #f1f5f9;"><span style="color: #475569;">${item.nama} <span style="color: #94a3b8;">x${item.qty}</span></span><span style="font-weight: 600; color: #1e293b;">Rp ${(item.harga * item.qty).toLocaleString('id-ID')}</span></div>`; }); } }); }
        openModal('modalDetailCashFlow');
    }

    function openEditCashFlow(data) { const form = document.getElementById('formEditCashFlow'); form.action = `/buku-kas/cashflow/${data.uuid}`; document.getElementById('editCfTitle').innerText = 'Edit ' + (data.jenis.toLowerCase() === 'pemasukan' ? 'Pemasukan' : 'Pengeluaran'); document.getElementById('editCfTanggalInput').value = data.tanggal.substring(0, 10); document.getElementById('editCfNominalInput').value = data.nominal; document.getElementById('editCfKeteranganInput').value = data.keterangan; document.getElementById('editCfMetodeInput').value = data.metode_pembayaran; openModal('modalEditCashFlow'); }

    function viewDebtDetail(debt, contact, details) { document.getElementById('debtDetailTitle').innerText = 'Detail ' + (debt.tipe.toLowerCase() === 'piutang' ? 'Piutang' : 'Hutang'); document.getElementById('debtDetailContact').innerText = contact ? contact.nama : '-'; document.getElementById('debtDetailSisa').innerText = 'Rp ' + parseInt(debt.sisa).toLocaleString('id-ID'); document.getElementById('debtDetailTotal').innerText = 'Rp ' + parseInt(debt.nominal).toLocaleString('id-ID'); document.getElementById('debtDetailTempo').innerText = new Date(debt.jatuh_tempo).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }); const historyList = document.getElementById('debtHistoryList'); historyList.innerHTML = ''; if (details.length === 0) { historyList.innerHTML = '<div style="text-align: center; color: #999; padding: 10px; font-size: 12px;">Belum ada riwayat pembayaran</div>'; } else { details.forEach(d => { historyList.innerHTML += `<div style="background: white; border: 1px solid #f1f5f9; padding: 10px; border-radius: 8px; font-size: 12px;"><div style="display: flex; justify-content: space-between; margin-bottom: 4px;"><span style="font-weight: 700; color: #2E7D32;">Bayar: Rp ${parseInt(d.bayar).toLocaleString('id-ID')}</span><span style="color: #94a3b8; font-size: 10px;">${new Date(d.tanggal).toLocaleDateString('id-ID')}</span></div><div style="display: flex; justify-content: space-between; color: #64748b; font-size: 11px;"><span>Sisa: Rp ${parseInt(d.sisa).toLocaleString('id-ID')}</span><span>Metode: ${d.payment_method ? d.payment_method.nama_metode : '-'}</span></div></div>`; }); } const btnBayar = document.getElementById('btnBukaModalBayar'); if (debt.sisa <= 0) { btnBayar.style.display = 'none'; } else { btnBayar.style.display = 'flex'; btnBayar.onclick = () => { document.getElementById('formBayarDebt').action = `/buku-kas/debt/${debt.uuid}/pay`; document.getElementById('inputBayarDebt').value = debt.sisa; document.getElementById('bayarMaxInfo').innerText = 'Maksimal pembayaran: Rp ' + parseInt(debt.sisa).toLocaleString('id-ID'); openModal('modalBayarDebt'); }; } const prodSection = document.getElementById('debtProductSection'); const prodList = document.getElementById('debtProductList'); prodSection.style.display = 'none'; prodList.innerHTML = ''; if (debt.transaction_id || debt.payment_order_id) { const refId = debt.transaction_id || debt.payment_order_id; fetch(`/buku-kas/reference-detail/${refId}`).then(res => res.json()).then(res => { if (res.success && res.items.length > 0) { prodSection.style.display = 'block'; res.items.forEach(item => { prodList.innerHTML += `<div style="display: flex; justify-content: space-between; font-size: 12px; padding: 3px 0;"><span style="color: #475569;">${item.nama} <span style="color: #94a3b8;">x${item.qty}</span></span><span style="font-weight: 600; color: #1e293b;">Rp ${(item.harga * item.qty).toLocaleString('id-ID')}</span></div>`; }); } }); } openModal('modalDetailDebt'); }

    function openEditDebt(debt, contact) { const form = document.getElementById('modalEditDebtForm'); form.action = `/buku-kas/debt/${debt.uuid}`; document.getElementById('editDebtTitle').innerText = 'Edit ' + (debt.tipe.toLowerCase() === 'piutang' ? 'Piutang' : 'Hutang'); document.getElementById('editDebtContactLabel').innerText = (debt.tipe.toLowerCase() === 'piutang' ? 'Customer' : 'Supplier') + ' *'; document.getElementById('editDebtContactInput').value = contact ? contact.nama : ''; document.getElementById('editDebtNominalInput').value = debt.nominal; document.getElementById('editDebtTempoInput').value = debt.jatuh_tempo.substring(0, 10); openModal('modalEditDebt'); }

    function openExportModal(format) { document.getElementById('exportFormatInput').value = format; document.getElementById('exportFormatLabel').innerText = format.toUpperCase(); openModal('modalExport'); }
    function toggleAllKategori(source) { document.querySelectorAll('.export-checkbox').forEach(cb => cb.checked = source.checked); }
    function checkKategoriStatus() { const total = document.querySelectorAll('.export-checkbox').length; const checked = document.querySelectorAll('.export-checkbox:checked').length; document.getElementById('checkAllKategori').checked = (total === checked); }

    @if(session('success')) Swal.fire({ icon: 'success', title: 'Berhasil!', text: "{{ session('success') }}", showConfirmButton: false, timer: 2000 }); @endif
</script>
@endsection
