@extends('layouts.app')

@section('title', 'Buku Kas')

@section('content')
<link rel="stylesheet" href="{{ asset('css/fitur.css') }}">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .tab-pill, .btn-action, .chip, .close-modal, .btn-filter { user-select: none; }
    .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
    .status-lunas { background: #E8F5E9; color: #2E7D32; }
    .status-belum { background: #FFF3E0; color: #E65100; }
    .empty-state { text-align: center; padding: 40px; color: #999; }
    .form-control.is-invalid { border-color: #ef4444 !important; background-color: #fef2f2 !important; }
    .invalid-feedback { color: #ef4444; font-size: 12px; margin-top: 4px; font-weight: 500; display: none; }
    .is-invalid + .invalid-feedback { display: block !important; }
    .tab-pill.active { background: var(--primary-blue) !important; color: white !important; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); }
    .tab-pill:hover:not(.active) { background: #f1f5f9; transform: translateY(-1px); }
    .nominal-wrapper { position: relative; display: flex; align-items: center; }
    .nominal-wrapper::before { content: "Rp"; position: absolute; left: 12px; font-weight: 700; color: #475569; font-size: 13px; pointer-events: none; }
    .nominal-wrapper input { padding-left: 35px !important; }
</style>

<div class="fitur-container" id="bukukas-app">
    @include('buku_kas.partials.tabs')

    <div class="action-bar">
        <div style="display: contents;">
            <div class="left-actions-group">
                <div class="search-wrapper">
                    <iconify-icon icon="solar:magnifer-linear" class="search-icon"></iconify-icon>
                    <input type="text" id="globalSearch" class="search-input" placeholder="Cari customer..." onkeyup="filterTable()">
                </div>
                <div class="dropdown">
                    <button type="button" class="btn-filter" title="Rentang Waktu" onclick="toggleDropdown(event)"><iconify-icon icon="solar:calendar-bold-duotone" style="font-size: 24px;" class="{{ request('start_date') || request('end_date') ? 'text-primary-blue' : '' }}"></iconify-icon></button>
                    <div class="dropdown-content" style="padding: 15px; width: 280px; left: 0; right: auto;">
                        <div style="font-size: 12px; font-weight: 600; margin-bottom: 10px; color: var(--primary-blue);">RENTANG TANGGAL</div>
                        <div class="form-group" style="margin-bottom: 12px;"><label style="font-size: 11px; color: #666;">Dari</label><input type="date" id="filterStartDate" class="form-control" style="font-size: 12px;" value="{{ $start_date }}"></div>
                        <div class="form-group" style="margin-bottom: 15px;"><label style="font-size: 11px; color: #666;">Sampai</label><input type="date" id="filterEndDate" class="form-control" style="font-size: 12px;" value="{{ $end_date }}"></div>
                        <button type="button" class="btn-action" style="width: 100%; justify-content: center; padding: 10px;" onclick="applyBukuKasRangeFilter()">Terapkan Filter</button>
                    </div>
                </div>
                @if(Auth::user()->role === 'owner' || (Auth::user()->role === 'kepala_toko' && $outlets->count() > 1))
                    <div class="dropdown">
                        <button type="button" class="btn-filter" title="Filter Toko" onclick="toggleDropdown(event)"><iconify-icon icon="solar:shop-bold-duotone" style="font-size: 24px;" class="{{ $store_id != 'all' ? 'text-primary-blue' : '' }}"></iconify-icon></button>
                        <div class="dropdown-content">
                            <form id="storeForm" method="GET" action="{{ url()->current() }}"><input type="hidden" name="store_id" id="storeFormStoreId" value="{{ $store_id }}"><input type="hidden" name="status" value="{{ $status }}"><input type="hidden" name="start_date" value="{{ $start_date }}"><input type="hidden" name="end_date" value="{{ $end_date }}"></form>
                            @if(Auth::user()->role === 'owner')<a href="javascript:void(0)" onclick="document.getElementById('storeFormStoreId').value = 'all'; document.getElementById('storeForm').submit()" class="{{ $store_id === 'all' ? 'active-dropdown-item' : '' }}">Semua Outlet</a>@endif
                            @foreach($outlets as $o)<a href="javascript:void(0)" onclick="document.getElementById('storeFormStoreId').value = '{{ $o->uuid }}'; document.getElementById('storeForm').submit()" class="{{ $store_id == $o->uuid ? 'active-dropdown-item' : '' }}">{{ $o->nama }}</a>@endforeach
                        </div>
                    </div>
                @endif
                <div class="dropdown" id="statusFilterDropdown">
                    <button type="button" class="btn-filter" title="Filter Status" onclick="toggleDropdown(event)"><iconify-icon icon="solar:filter-bold-duotone" style="font-size: 24px;" class="{{ $status ? 'text-primary-blue' : '' }}"></iconify-icon></button>
                    <div class="dropdown-content">
                        <a href="javascript:void(0)" onclick="applyStatusFilter('')" class="{{ !$status ? 'active-dropdown-item' : '' }}">Semua Status</a>
                        <a href="javascript:void(0)" onclick="applyStatusFilter('belum_lunas')" class="{{ $status == 'belum_lunas' ? 'active-dropdown-item' : '' }}">Belum Lunas</a>
                        <a href="javascript:void(0)" onclick="applyStatusFilter('lunas')" class="{{ $status == 'lunas' ? 'active-dropdown-item' : '' }}">Lunas</a>
                    </div>
                </div>
            </div>
            <div class="right-actions">
                <div class="dropdown">
                    <button type="button" class="btn-action" onclick="toggleDropdown(event)"><iconify-icon icon="solar:document-text-bold-duotone"></iconify-icon><span>Extract</span></button>
                    <div class="dropdown-content">
                        <a href="javascript:void(0)" onclick="openExportModal('excel')"><iconify-icon icon="vscode-icons:file-type-excel" style="margin-right: 8px;"></iconify-icon> Excel</a>
                        <a href="javascript:void(0)" onclick="openExportModal('pdf')"><iconify-icon icon="vscode-icons:file-type-pdf" style="margin-right: 8px;"></iconify-icon> PDF</a>
                    </div>
                </div>
                <button type="button" class="btn-action" onclick="openModal('modalPiutang')"><iconify-icon icon="solar:add-circle-bold-duotone"></iconify-icon><span>Tambah Piutang</span></button>
            </div>
        </div>
    </div>

    <div class="main-content-box">
        <div class="table-container">
            <table class="fitur-table">
                <thead><tr><th>CUSTOMER</th><th>TOTAL PIUTANG</th><th>SISA TAGIHAN</th><th>STATUS</th><th>JATUH TEMPO</th><th>AKSI</th></tr></thead>
                <tbody id="tbody-piutang">
                    @forelse($piutang as $p)
                    <tr>
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

<div id="modalPiutang" class="modal-overlay">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header"><h3>Tambah Piutang Baru</h3><button type="button" class="close-modal" onclick="closeModal('modalPiutang')">&times;</button></div>
        <form action="{{ route('keuangan.debt.store') }}" method="POST" novalidate>
            @csrf
            @if($store_id === 'all')
                <div class="form-group" style="margin-bottom: 15px;"><label style="font-size: 11px; color: #888; display: block;">Pilih Outlet Tujuan *</label>
                    <select name="store_id" class="form-control" style="border: 1px solid #ddd; padding: 8px; border-radius: 8px;" required_js><option value="">-- Pilih Outlet --</option>@foreach($outlets as $o)<option value="{{ $o->uuid }}">{{ $o->nama }}</option>@endforeach</select>
                    <div class="invalid-feedback">Outlet wajib dipilih</div>
                </div>
            @else <input type="hidden" name="store_id" value="{{ $store_id }}"> @endif
            <input type="hidden" name="tipe" value="Piutang">
            <div class="form-group"><label>Customer / Kontak *</label><select name="kontak_nama" class="form-control" required_js><option value="">-- Pilih Customer --</option>@foreach($customers as $customer)<option value="{{ $customer->nama }}">{{ $customer->nama }}</option>@endforeach</select><div class="invalid-feedback">Kontak wajib dipilih</div></div>
            <div class="form-group"><label>Total Nilai Piutang *</label><div class="nominal-wrapper"><input type="number" name="nominal" class="form-control" placeholder="0" required_js><div class="invalid-feedback">Nominal wajib diisi</div></div></div>
            <div class="form-group"><label>Opsi: Uang Muka / DP</label><div class="nominal-wrapper"><input type="number" name="uang_muka" class="form-control" placeholder="Masukkan jika ada DP"></div></div>
            <div class="form-group" style="margin-bottom: 15px;"><label style="font-size: 11px; color: #888; display: block;">Metode Pembayaran (Untuk DP)</label><select name="metode_pembayaran" class="form-control" style="border: 1px solid #ddd; padding: 8px; border-radius: 8px;"><option value="">-- Pilih Metode --</option>@foreach($paymentMethods as $pm)<option value="{{ $pm->uuid }}">{{ $pm->nama_metode }}</option>@endforeach</select></div>
            <div class="form-group"><label>Jatuh Tempo *</label><input type="date" name="jatuh_tempo" class="form-control" required_js><div class="invalid-feedback">Jatuh tempo wajib diisi</div></div>
            <div style="display: flex; gap: 10px; margin-top: 20px;"><button type="button" onclick="closeModal('modalPiutang')" class="btn-action btn-danger" style="flex:1; justify-content:center;">Batal</button><button type="submit" class="btn-action" style="flex:1; justify-content:center;">Simpan</button></div>
        </form>
    </div>
</div>

{{-- COMMON MODALS FOR DEBT --}}
<div id="modalDetailDebt" class="modal-overlay">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header"><h3 id="debtDetailTitle">Detail Tagihan</h3><button type="button" class="close-modal" onclick="closeModal('modalDetailDebt')">&times;</button></div>
        <div style="padding: 20px; border-bottom: 1px solid #f1f5f9;"><div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;"><div><div style="font-size: 12px; color: #64748b; margin-bottom: 4px;">Nama Kontak</div><div id="debtDetailContact" style="font-size: 18px; font-weight: 700; color: #1e293b;">-</div></div><div style="text-align: right;"><div style="font-size: 12px; color: #64748b; margin-bottom: 4px;">Sisa Tagihan</div><div id="debtDetailSisa" style="font-size: 18px; font-weight: 700; color: var(--primary-blue);">Rp 0</div></div></div><div style="background: #f8fafc; border-radius: 12px; padding: 15px; display: grid; grid-template-columns: 1fr 1fr; gap: 15px;"><div><div style="font-size: 11px; color: #64748b; margin-bottom: 2px;">Total Tagihan</div><div id="debtDetailTotal" style="font-weight: 600;">Rp 0</div></div><div><div style="font-size: 11px; color: #64748b; margin-bottom: 2px;">Jatuh Tempo</div><div id="debtDetailTempo" style="font-weight: 600;">-</div></div></div><div id="debtProductSection" style="display: none; margin-top: 15px; border-top: 1px dashed #e2e8f0; padding-top: 15px;"><div style="font-size: 12px; font-weight: 700; color: #1e293b; margin-bottom: 10px;">Daftar Produk:</div><div id="debtProductList" style="display: flex; flex-direction: column; gap: 8px;"></div></div></div>
        <div style="padding: 20px;"><div style="font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 12px; display: flex; justify-content: space-between;"><span>Riwayat Pembayaran</span><button type="button" id="btnBukaModalBayar" class="btn-action" style="padding: 4px 10px; font-size: 11px; background: #E8F5E9; color: #2E7D32;">+ Bayar Cicilan</button></div><div id="debtHistoryList" style="display: flex; flex-direction: column; gap: 10px; max-height: 200px; overflow-y: auto;"></div></div>
    </div>
</div>
<div id="modalBayarDebt" class="modal-overlay">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header"><h3>Catat Pembayaran</h3><button type="button" class="close-modal" onclick="closeModal('modalBayarDebt')">&times;</button></div>
        <form id="formBayarDebt" method="POST">@csrf<div class="form-group"><label>Nominal Bayar *</label><div class="nominal-wrapper"><input type="number" name="bayar" id="inputBayarDebt" class="form-control" placeholder="0" required></div><div id="bayarMaxInfo" style="font-size: 11px; color: #64748b; margin-top: 5px;"></div></div><div class="form-group" style="margin-top: 15px;"><label>Metode Pembayaran *</label><select name="metode_pembayaran" class="form-control" required><option value="">-- Pilih Metode --</option>@foreach($paymentMethods as $pm)<option value="{{ $pm->uuid }}">{{ $pm->nama_metode }}</option>@endforeach</select></div><div style="display: flex; gap: 10px; margin-top: 25px;"><button type="button" onclick="closeModal('modalBayarDebt')" class="btn-action btn-danger" style="flex:1; justify-content:center;">Batal</button><button type="submit" class="btn-action" style="flex:1; justify-content:center;">Simpan</button></div></form>
    </div>
</div>
<div id="modalEditDebt" class="modal-overlay">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header"><h3 id="editDebtTitle">Edit Tagihan</h3><button type="button" class="close-modal" onclick="closeModal('modalEditDebt')">&times;</button></div>
        <form id="modalEditDebtForm" method="POST">@csrf @method('PUT')<div class="form-group"><label id="editDebtContactLabel">Nama / Kontak *</label><input type="text" id="editDebtContactInput" name="kontak_nama" class="form-control" required></div><div class="form-group"><label>Total Nilai *</label><div class="nominal-wrapper"><input type="number" id="editDebtNominalInput" name="nominal" class="form-control" placeholder="0" required></div></div><div class="form-group"><label>Jatuh Tempo *</label><input type="date" id="editDebtTempoInput" name="jatuh_tempo" class="form-control" required></div><div style="display: flex; gap: 10px; margin-top: 20px;"><button type="button" onclick="closeModal('modalEditDebt')" class="btn-action btn-danger" style="flex:1; justify-content:center;">Batal</button><button type="submit" class="btn-action" style="flex:1; justify-content:center; background: #007BFF;">Update</button></div></form>
    </div>
</div>
<div id="modalExport" class="modal-overlay">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header"><h3>Export Data <span id="exportFormatLabel"></span></h3><button type="button" class="close-modal" onclick="closeModal('modalExport')">&times;</button></div>
        <form id="formExport" method="GET" action="{{ route('keuangan.export') }}"><input type="hidden" name="format" id="exportFormatInput" value=""><input type="hidden" name="store_id" value="{{ $store_id }}">
            <div class="form-group"><label style="display: block; margin-bottom: 8px;">Pilih Data yang Diekstrak</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; padding: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600; color: var(--primary-blue); grid-column: span 2; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 4px;"><input type="checkbox" id="checkAllKategori" onchange="toggleAllKategori(this)"> Semua Kategori</label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: normal;"><input type="checkbox" name="kategori[]" value="Pemasukan" class="export-checkbox" onchange="checkKategoriStatus()"> Pemasukan</label><label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: normal;"><input type="checkbox" name="kategori[]" value="Pengeluaran" class="export-checkbox" onchange="checkKategoriStatus()"> Pengeluaran</label><label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: normal;"><input type="checkbox" name="kategori[]" value="Hutang" class="export-checkbox" onchange="checkKategoriStatus()"> Hutang</label><label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: normal;"><input type="checkbox" name="kategori[]" value="Piutang" class="export-checkbox" onchange="checkKategoriStatus()"> Piutang</label>
                </div>
            </div>
            <div class="form-group" style="margin-top: 15px;"><label>Rentang Waktu (Opsional)</label><div style="display: flex; gap: 10px; align-items: center;"><input type="date" name="start_date" class="form-control" style="flex: 1;"><span>-</span><input type="date" name="end_date" class="form-control" style="flex: 1;"></div></div>
            <div style="display: flex; gap: 10px; margin-top: 25px;"><button type="button" onclick="closeModal('modalExport')" class="btn-action btn-danger" style="flex:1; justify-content:center;">Batal</button><button type="submit" class="btn-action" style="flex:1; justify-content:center; background: #2E7D32;" onclick="setTimeout(() => closeModal('modalExport'), 1000)"><iconify-icon icon="solar:download-bold-duotone"></iconify-icon> Download</button></div>
        </form>
    </div>
</div>
<form id="formGlobalDeleteDebt" method="POST" style="display: none;">@csrf @method('DELETE')</form>

<script>
    function openModal(id) { const modal = document.getElementById(id); if (modal) { const form = modal.querySelector('form'); if (form && !id.toLowerCase().includes('edit') && !id.toLowerCase().includes('detail') && !id.toLowerCase().includes('export')) { form.reset(); form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid')); } modal.style.display = 'flex'; } }
    function closeModal(id) { const modal = document.getElementById(id); if (modal) modal.style.display = 'none'; }
    window.onclick = function(event) { if (event.target.classList.contains('modal-overlay')) event.target.style.display = 'none'; };
    function toggleDropdown(event) { event.stopPropagation(); const dropdown = event.currentTarget.nextElementSibling; document.querySelectorAll('.dropdown-content').forEach(d => { if (d !== dropdown) d.classList.remove('show'); }); dropdown.classList.toggle('show'); }
    document.addEventListener('click', () => { document.querySelectorAll('.dropdown-content').forEach(d => d.classList.remove('show')); });
    document.querySelectorAll('form').forEach(form => { form.addEventListener('submit', function(e) { let isValid = true; const inputs = this.querySelectorAll('[required_js]'); inputs.forEach(input => { if (!input.value.trim()) { isValid = false; input.classList.add('is-invalid'); } else { input.classList.remove('is-invalid'); } }); if (!isValid) e.preventDefault(); }); });
    document.querySelectorAll('[required_js]').forEach(input => { input.addEventListener('input', function() { if (this.value.trim()) this.classList.remove('is-invalid'); }); });
    function applyBukuKasRangeFilter() { const start = document.getElementById('filterStartDate').value; const end = document.getElementById('filterEndDate').value; const url = new URL(window.location.href); url.searchParams.set('start_date', start); url.searchParams.set('end_date', end); url.searchParams.set('period', 'harian'); window.location.href = url.toString(); }
    function applyStatusFilter(status) { const url = new URL(window.location.href); url.searchParams.set('status', status); window.location.href = url.toString(); }
    function filterTable() { const search = document.getElementById('globalSearch').value.toLowerCase(); const rows = document.querySelectorAll('tbody tr'); rows.forEach(row => { if (row.querySelector('.empty-state')) return; const text = row.innerText.toLowerCase(); row.style.display = text.includes(search) ? '' : 'none'; }); }
    function deleteDebt(uuid, tipe) { Swal.fire({ title: 'Hapus Data?', text: `Apakah Anda yakin ingin menghapus data ${tipe} ini?`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#718096', confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal' }).then((result) => { if (result.isConfirmed) { const form = document.getElementById('formGlobalDeleteDebt'); form.action = `/buku-kas/debt/${uuid}`; form.submit(); } }); }
    function viewDebtDetail(debt, contact, details) { document.getElementById('debtDetailTitle').innerText = 'Detail ' + (debt.tipe.toLowerCase() === 'piutang' ? 'Piutang' : 'Hutang'); document.getElementById('debtDetailContact').innerText = contact ? contact.nama : '-'; document.getElementById('debtDetailSisa').innerText = 'Rp ' + parseInt(debt.sisa).toLocaleString('id-ID'); document.getElementById('debtDetailTotal').innerText = 'Rp ' + parseInt(debt.nominal).toLocaleString('id-ID'); document.getElementById('debtDetailTempo').innerText = new Date(debt.jatuh_tempo).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }); const historyList = document.getElementById('debtHistoryList'); historyList.innerHTML = ''; if (details.length === 0) { historyList.innerHTML = '<div style="text-align: center; color: #999; padding: 10px; font-size: 12px;">Belum ada riwayat pembayaran</div>'; } else { details.forEach(d => { historyList.innerHTML += `<div style="background: white; border: 1px solid #f1f5f9; padding: 10px; border-radius: 8px; font-size: 12px;"><div style="display: flex; justify-content: space-between; margin-bottom: 4px;"><span style="font-weight: 700; color: #2E7D32;">Bayar: Rp ${parseInt(d.bayar).toLocaleString('id-ID')}</span><span style="color: #94a3b8; font-size: 10px;">${new Date(d.tanggal).toLocaleDateString('id-ID')}</span></div><div style="display: flex; justify-content: space-between; color: #64748b; font-size: 11px;"><span>Sisa: Rp ${parseInt(d.sisa).toLocaleString('id-ID')}</span><span>Metode: ${d.payment_method ? d.payment_method.nama_metode : '-'}</span></div></div>`; }); } const btnBayar = document.getElementById('btnBukaModalBayar'); if (debt.sisa <= 0) { btnBayar.style.display = 'none'; } else { btnBayar.style.display = 'flex'; btnBayar.onclick = () => { document.getElementById('formBayarDebt').action = `/buku-kas/debt/${debt.uuid}/pay`; document.getElementById('inputBayarDebt').value = debt.sisa; document.getElementById('bayarMaxInfo').innerText = 'Maksimal pembayaran: Rp ' + parseInt(debt.sisa).toLocaleString('id-ID'); openModal('modalBayarDebt'); }; } const prodSection = document.getElementById('debtProductSection'); const prodList = document.getElementById('debtProductList'); prodSection.style.display = 'none'; prodList.innerHTML = ''; if (debt.transaction_id || debt.payment_order_id) { const refId = debt.transaction_id || debt.payment_order_id; fetch(`/buku-kas/reference/${refId}`).then(res => res.json()).then(res => { if (res.success && res.items.length > 0) { prodSection.style.display = 'block'; res.items.forEach(item => { prodList.innerHTML += `<div style="display: flex; justify-content: space-between; font-size: 12px; padding: 3px 0;"><span style="color: #475569;">${item.nama} <span style="color: #94a3b8;">x${item.qty}</span></span><span style="font-weight: 600; color: #1e293b;">Rp ${(item.harga * item.qty).toLocaleString('id-ID')}</span></div>`; }); } }); } openModal('modalDetailDebt'); }
    function openEditDebt(debt, contact) { const form = document.getElementById('modalEditDebtForm'); form.action = `/buku-kas/debt/${debt.uuid}`; document.getElementById('editDebtTitle').innerText = 'Edit ' + (debt.tipe.toLowerCase() === 'piutang' ? 'Piutang' : 'Hutang'); document.getElementById('editDebtContactLabel').innerText = (debt.tipe.toLowerCase() === 'piutang' ? 'Customer' : 'Supplier') + ' *'; document.getElementById('editDebtContactInput').value = contact ? contact.nama : ''; document.getElementById('editDebtNominalInput').value = debt.nominal; document.getElementById('editDebtTempoInput').value = debt.jatuh_tempo.substring(0, 10); openModal('modalEditDebt'); }
    function openExportModal(format) { document.getElementById('exportFormatInput').value = format; document.getElementById('exportFormatLabel').innerText = format.toUpperCase(); openModal('modalExport'); }
    function toggleAllKategori(source) { document.querySelectorAll('.export-checkbox').forEach(cb => cb.checked = source.checked); }
    function checkKategoriStatus() { const total = document.querySelectorAll('.export-checkbox').length; const checked = document.querySelectorAll('.export-checkbox:checked').length; document.getElementById('checkAllKategori').checked = (total === checked); }
    @if(session('success')) Swal.fire({ icon: 'success', title: 'Berhasil!', text: "{{ session('success') }}", showConfirmButton: false, timer: 2000 }); @endif
    document.addEventListener('DOMContentLoaded', () => { const today = new Date().toISOString().split('T')[0]; document.querySelectorAll('input[type="date"]').forEach(el => { if (el.name !== 'jatuh_tempo' && !el.hasAttribute('min')) el.setAttribute('max', today); }); });
</script>
@endsection
