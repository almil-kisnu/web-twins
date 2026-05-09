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
    .chips-container { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
    .chip { background: #f1f5f9; color: #475569; padding: 6px 12px; border-radius: 20px; font-size: 0.8rem; cursor: pointer; transition: 0.2s; border: none; font-weight: 500; }
    .chip:hover { background: #e2e8f0; }
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
    {{-- TAB NAVIGATION --}}
    @include('buku_kas.partials.tabs')

    {{-- ACTION BAR --}}
    <div class="action-bar">
        <div style="display: contents;">
            <div class="left-actions-group">
                <div class="search-wrapper">
                    <iconify-icon icon="solar:magnifer-linear" class="search-icon"></iconify-icon>
                    <input type="text" id="globalSearch" class="search-input" placeholder="Cari pemasukan..." onkeyup="filterTable()">
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
                                <input type="hidden" name="start_date" value="{{ $start_date }}">
                                <input type="hidden" name="end_date" value="{{ $end_date }}">
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
                <button type="button" class="btn-action" onclick="openModal('modalPemasukan')">
                    <iconify-icon icon="solar:add-circle-bold-duotone"></iconify-icon>
                    <span>Tambah Pemasukan</span>
                </button>
            </div>
        </div>
    </div>
    
    {{-- MAIN TABLE --}}
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
                <tbody id="tbody-pemasukan">
                    @forelse($pemasukan as $p)
                    <tr>
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

{{-- MODALS --}}
<div id="modalPemasukan" class="modal-overlay">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header"><h3>Tambah Pemasukan Lainnya</h3><button type="button" class="close-modal" onclick="closeModal('modalPemasukan')">&times;</button></div>
        <form action="{{ route('keuangan.cashflow.store') }}" method="POST" novalidate>
            @csrf
            @if($store_id === 'all')
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-size: 11px; color: #888; display: block;">Pilih Outlet Tujuan *</label>
                    <select name="store_id" class="form-control" style="border: 1px solid #ddd; padding: 8px; border-radius: 8px;" required_js>
                        <option value="">-- Pilih Outlet --</option>
                        @foreach($outlets as $o)<option value="{{ $o->uuid }}">{{ $o->nama }}</option>@endforeach
                    </select>
                    <div class="invalid-feedback">Outlet wajib dipilih</div>
                </div>
            @else <input type="hidden" name="store_id" value="{{ $store_id }}"> @endif
            <input type="hidden" name="jenis" value="Pemasukan">
            <div class="form-group" style="border: 1px solid #ddd; border-radius: 8px; padding: 10px; margin-bottom: 15px;"><label style="font-size: 11px; color: #888; display: block;">Tanggal Transaksi *</label><input type="date" name="tanggal" class="form-control" style="border:none; padding:5px 0" value="{{ date('Y-m-d') }}" required></div>
            <div class="form-group"><label>Nominal *</label><div class="nominal-wrapper"><input type="number" name="nominal" class="form-control" placeholder="0" required_js><div class="invalid-feedback">Nominal wajib diisi</div></div></div>
            <div class="form-group" style="margin-bottom: 15px;"><label style="font-size: 11px; color: #888; display: block;">Metode Pembayaran *</label><select name="metode_pembayaran" class="form-control" style="border: 1px solid #ddd; padding: 8px; border-radius: 8px;" required_js><option value="">-- Pilih Metode --</option>@foreach($paymentMethods as $pm)<option value="{{ $pm->uuid }}">{{ $pm->nama_metode }}</option>@endforeach</select><div class="invalid-feedback">Pilih metode pembayaran</div></div>
            <div class="form-group"><label>Keterangan *</label><textarea name="keterangan" id="ketPemasukan" class="form-control" style="min-height: 80px;" placeholder="Tulis keterangan pemasukan..." required_js></textarea><div class="invalid-feedback">Keterangan wajib diisi</div><div class="chips-container">@foreach(['Tip/Upah', 'Bonus', 'Pengembalian', 'Koreksi Kas', 'Lain-lain'] as $sar)<button type="button" class="chip" onclick="document.getElementById('ketPemasukan').value = '{{ $sar }}'">{{ $sar }}</button>@endforeach</div></div>
            <div style="display: flex; gap: 10px; margin-top: 20px;"><button type="button" onclick="closeModal('modalPemasukan')" class="btn-action btn-danger" style="flex:1; justify-content:center;">Batal</button><button type="submit" class="btn-action" style="flex:1; justify-content:center;">Simpan</button></div>
        </form>
    </div>
</div>

<div id="modalEditCashFlow" class="modal-overlay">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header"><h3 id="editCfTitle">Edit Transaksi</h3><button type="button" class="close-modal" onclick="closeModal('modalEditCashFlow')">&times;</button></div>
        <form id="formEditCashFlow" method="POST" novalidate>
            @csrf @method('PUT')
            <div class="form-group" style="border: 1px solid #ddd; border-radius: 8px; padding: 10px; margin-bottom: 15px;"><label style="font-size: 11px; color: #888; display: block;">Tanggal Transaksi</label><input type="date" name="tanggal" id="editCfTanggalInput" class="form-control" style="border:none; padding:5px 0" required></div>
            <div class="form-group"><label>Nominal *</label><div class="nominal-wrapper"><input type="number" name="nominal" id="editCfNominalInput" class="form-control" placeholder="0" required></div></div>
            <div class="form-group" style="margin-bottom: 15px;"><label style="font-size: 11px; color: #888; display: block;">Metode Pembayaran *</label><select name="metode_pembayaran" id="editCfMetodeInput" class="form-control" style="border: 1px solid #ddd; padding: 8px; border-radius: 8px;" required_js><option value="">-- Pilih Metode --</option>@foreach($paymentMethods as $pm)<option value="{{ $pm->uuid }}">{{ $pm->nama_metode }}</option>@endforeach</select></div>
            <div class="form-group"><label>Keterangan</label><textarea name="keterangan" id="editCfKeteranganInput" class="form-control" style="min-height: 80px;" placeholder="Tulis keterangan..."></textarea></div>
            <div style="display: flex; gap: 10px; margin-top: 20px;"><button type="button" onclick="closeModal('modalEditCashFlow')" class="btn-action btn-danger" style="flex:1; justify-content:center;">Batal</button><button type="submit" class="btn-action" style="flex:1; justify-content:center; background: #007BFF;">Update</button></div>
        </form>
    </div>
</div>

<div id="modalDetailCashFlow" class="modal-overlay">
    <div class="modal-content" style="max-width: 380px; padding: 30px;">
        <div class="modal-header" style="margin-bottom: 0; padding-bottom: 0;"><h3></h3><button type="button" class="close-modal" onclick="closeModal('modalDetailCashFlow')">&times;</button></div>
        <div style="text-align: center;"><div id="cfIcon" style="width: 60px; height: 60px; border-radius: 30px; display: inline-flex; justify-content: center; align-items: center; font-size: 30px; margin-bottom: 10px;"></div><h4 id="cfTitle" style="margin: 0; color: #333; font-size: 16px; font-weight: 700;">Detail Transaksi</h4><h2 id="cfNominal" style="margin: 10px 0 25px 0; font-size: 28px; color: #1e293b;">Rp 0</h2></div>
        <div style="font-size: 13px; color: #475569; display: flex; flex-direction: column; gap: 15px;">
            <div style="display: flex; gap: 10px;"><iconify-icon icon="solar:document-text-outline" style="font-size: 18px; color: #94a3b8;"></iconify-icon><div><div style="color: #94a3b8; font-size: 11px; margin-bottom: 2px;">Keterangan</div><div id="cfKeterangan" style="font-weight: 600;">-</div></div></div>
            <div style="display: flex; gap: 10px;"><iconify-icon icon="solar:clock-circle-outline" style="font-size: 18px; color: #94a3b8;"></iconify-icon><div><div style="color: #94a3b8; font-size: 11px; margin-bottom: 2px;">Tanggal & Waktu</div><div id="cfTanggal" style="font-weight: 600;">-</div></div></div>
            <div style="display: flex; gap: 10px;"><iconify-icon icon="solar:shop-outline" style="font-size: 18px; color: #94a3b8;"></iconify-icon><div><div style="color: #94a3b8; font-size: 11px; margin-bottom: 2px;">Toko</div><div id="cfToko" style="font-weight: 600;">-</div></div></div>
            <div style="display: flex; gap: 10px;"><iconify-icon icon="solar:user-outline" style="font-size: 18px; color: #94a3b8;"></iconify-icon><div><div style="color: #94a3b8; font-size: 11px; margin-bottom: 2px;">Karyawan</div><div id="cfKaryawan" style="font-weight: 600;">-</div></div></div>
            <div style="display: flex; gap: 10px;"><iconify-icon icon="solar:card-outline" style="font-size: 18px; color: #94a3b8;"></iconify-icon><div><div style="color: #94a3b8; font-size: 11px; margin-bottom: 2px;">Metode Pembayaran</div><div id="cfMetode" style="font-weight: 600;">-</div></div></div>
        </div>
        <div id="cfProductSection" style="display: none; margin-top: 20px;"><div style="font-size: 12px; font-weight: 700; color: #1e293b; margin-bottom: 10px; border-top: 1px dashed #e2e8f0; padding-top: 15px;">Daftar Produk / Item:</div><div id="cfProductList" style="display: flex; flex-direction: column; gap: 8px; max-height: 200px; overflow-y: auto;"></div></div>
        <div style="display: flex; justify-content: center; margin-top: 35px;"><button type="button" class="btn-action" style="background: #f1f5f9; color: #475569; width: 100%; justify-content: center;" onclick="closeModal('modalDetailCashFlow')">Tutup</button></div>
    </div>
</div>

<div id="modalExport" class="modal-overlay">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header"><h3>Export Data <span id="exportFormatLabel"></span></h3><button type="button" class="close-modal" onclick="closeModal('modalExport')">&times;</button></div>
        <form id="formExport" method="GET" action="{{ route('keuangan.export') }}">
            <input type="hidden" name="format" id="exportFormatInput" value=""><input type="hidden" name="store_id" value="{{ $store_id }}">
            <div class="form-group"><label style="display: block; margin-bottom: 8px;">Pilih Data yang Diekstrak</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; padding: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600; color: var(--primary-blue); grid-column: span 2; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 4px;"><input type="checkbox" id="checkAllKategori" onchange="toggleAllKategori(this)"> Semua Kategori</label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: normal;"><input type="checkbox" name="kategori[]" value="Pemasukan" class="export-checkbox" onchange="checkKategoriStatus()"> Pemasukan</label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: normal;"><input type="checkbox" name="kategori[]" value="Pengeluaran" class="export-checkbox" onchange="checkKategoriStatus()"> Pengeluaran</label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: normal;"><input type="checkbox" name="kategori[]" value="Hutang" class="export-checkbox" onchange="checkKategoriStatus()"> Hutang</label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: normal;"><input type="checkbox" name="kategori[]" value="Piutang" class="export-checkbox" onchange="checkKategoriStatus()"> Piutang</label>
                </div>
            </div>
            <div class="form-group" style="margin-top: 15px;"><label>Rentang Waktu (Opsional)</label><div style="display: flex; gap: 10px; align-items: center;"><input type="date" name="start_date" class="form-control" style="flex: 1;"><span>-</span><input type="date" name="end_date" class="form-control" style="flex: 1;"></div></div>
            <div style="display: flex; gap: 10px; margin-top: 25px;"><button type="button" onclick="closeModal('modalExport')" class="btn-action btn-danger" style="flex:1; justify-content:center;">Batal</button><button type="submit" class="btn-action" style="flex:1; justify-content:center; background: #2E7D32;" onclick="setTimeout(() => closeModal('modalExport'), 1000)"><iconify-icon icon="solar:download-bold-duotone"></iconify-icon> Download</button></div>
        </form>
    </div>
</div>

<form id="formGlobalDeleteCf" method="POST" style="display: none;">@csrf @method('DELETE')</form>

<script>
    function openModal(id) { const modal = document.getElementById(id); if (modal) { const form = modal.querySelector('form'); if (form && !id.toLowerCase().includes('edit') && !id.toLowerCase().includes('detail') && !id.toLowerCase().includes('export')) { form.reset(); form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid')); } modal.style.display = 'flex'; } }
    function closeModal(id) { const modal = document.getElementById(id); if (modal) modal.style.display = 'none'; }
    window.onclick = function(event) { if (event.target.classList.contains('modal-overlay')) event.target.style.display = 'none'; };
    function toggleDropdown(event) { event.stopPropagation(); const dropdown = event.currentTarget.nextElementSibling; document.querySelectorAll('.dropdown-content').forEach(d => { if (d !== dropdown) d.classList.remove('show'); }); dropdown.classList.toggle('show'); }
    document.addEventListener('click', () => { document.querySelectorAll('.dropdown-content').forEach(d => d.classList.remove('show')); });
    document.querySelectorAll('form').forEach(form => { form.addEventListener('submit', function(e) { let isValid = true; const inputs = this.querySelectorAll('[required_js]'); inputs.forEach(input => { if (!input.value.trim()) { isValid = false; input.classList.add('is-invalid'); } else { input.classList.remove('is-invalid'); } }); if (!isValid) e.preventDefault(); }); });
    document.querySelectorAll('[required_js]').forEach(input => { input.addEventListener('input', function() { if (this.value.trim()) this.classList.remove('is-invalid'); }); });
    function applyBukuKasRangeFilter() { const start = document.getElementById('filterStartDate').value; const end = document.getElementById('filterEndDate').value; const url = new URL(window.location.href); url.searchParams.set('start_date', start); url.searchParams.set('end_date', end); url.searchParams.set('period', 'harian'); window.location.href = url.toString(); }
    function filterTable() { const search = document.getElementById('globalSearch').value.toLowerCase(); const rows = document.querySelectorAll('tbody tr'); rows.forEach(row => { if (row.querySelector('.empty-state')) return; const text = row.innerText.toLowerCase(); row.style.display = text.includes(search) ? '' : 'none'; }); }
    function deleteCf(uuid, jenis) { Swal.fire({ title: 'Hapus Data?', text: `Apakah Anda yakin ingin menghapus data ${jenis} ini?`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#718096', confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal' }).then((result) => { if (result.isConfirmed) { const form = document.getElementById('formGlobalDeleteCf'); form.action = `/buku-kas/cashflow/${uuid}`; form.submit(); } }); }
    function viewCashFlowDetail(data) {
        const iconBox = document.getElementById('cfIcon'); const title = document.getElementById('cfTitle'); const nominal = document.getElementById('cfNominal'); const keterangan = document.getElementById('cfKeterangan'); const tanggal = document.getElementById('cfTanggal'); const toko = document.getElementById('cfToko'); const karyawan = document.getElementById('cfKaryawan'); const metode = document.getElementById('cfMetode'); const productSection = document.getElementById('cfProductSection'); const productList = document.getElementById('cfProductList');
        const isPemasukan = data.jenis.toLowerCase() === 'pemasukan';
        title.innerText = isPemasukan ? 'Detail Pemasukan Lainnya' : 'Detail Pengeluaran';
        iconBox.innerHTML = isPemasukan ? '<iconify-icon icon="solar:round-arrow-right-up-bold-duotone"></iconify-icon>' : '<iconify-icon icon="solar:round-arrow-left-down-bold-duotone"></iconify-icon>';
        iconBox.style.background = isPemasukan ? '#E8F5E9' : '#FFEBEE'; iconBox.style.color = isPemasukan ? '#2E7D32' : '#C62828';
        nominal.innerText = (isPemasukan ? '+ ' : '- ') + 'Rp ' + parseInt(data.nominal).toLocaleString('id-ID'); nominal.style.color = isPemasukan ? '#2E7D32' : '#C62828';
        keterangan.innerText = data.keterangan.replace(/\(Trx: [a-f0-9-]{36}\)/i, '(Otomatis)');
        tanggal.innerText = new Date(data.tanggal).toLocaleString('id-ID', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        toko.innerText = data.outlet ? data.outlet.nama : '-'; karyawan.innerText = data.user ? (data.user.username || data.user.name) : '-'; metode.innerText = data.payment_method ? data.payment_method.nama_metode : '-';
        productSection.style.display = 'none'; productList.innerHTML = '';
        const refId = data.keterangan.match(/\(Trx: ([a-f0-9-]{36})\)/i);
        if (refId && refId[1]) { fetch(`/buku-kas/reference/${refId[1]}`).then(res => res.json()).then(res => { if (res.success && res.items.length > 0) { productSection.style.display = 'block'; res.items.forEach(item => { productList.innerHTML += `<div style="display: flex; justify-content: space-between; font-size: 12px; padding: 5px 0; border-bottom: 1px solid #f1f5f9;"><span style="color: #475569;">${item.nama} <span style="color: #94a3b8;">x${item.qty}</span></span><span style="font-weight: 600; color: #1e293b;">Rp ${(item.harga * item.qty).toLocaleString('id-ID')}</span></div>`; }); } }); }
        openModal('modalDetailCashFlow');
    }
    function openEditCashFlow(data) { const form = document.getElementById('formEditCashFlow'); form.action = `/buku-kas/cashflow/${data.uuid}`; document.getElementById('editCfTitle').innerText = 'Edit ' + (data.jenis.toLowerCase() === 'pemasukan' ? 'Pemasukan' : 'Pengeluaran'); document.getElementById('editCfTanggalInput').value = data.tanggal.substring(0, 10); document.getElementById('editCfNominalInput').value = data.nominal; document.getElementById('editCfKeteranganInput').value = data.keterangan; document.getElementById('editCfMetodeInput').value = data.metode_pembayaran; openModal('modalEditCashFlow'); }
    function openExportModal(format) { document.getElementById('exportFormatInput').value = format; document.getElementById('exportFormatLabel').innerText = format.toUpperCase(); openModal('modalExport'); }
    function toggleAllKategori(source) { document.querySelectorAll('.export-checkbox').forEach(cb => cb.checked = source.checked); }
    function checkKategoriStatus() { const total = document.querySelectorAll('.export-checkbox').length; const checked = document.querySelectorAll('.export-checkbox:checked').length; document.getElementById('checkAllKategori').checked = (total === checked); }
    @if(session('success')) Swal.fire({ icon: 'success', title: 'Berhasil!', text: "{{ session('success') }}", showConfirmButton: false, timer: 2000 }); @endif
    document.addEventListener('DOMContentLoaded', () => { const today = new Date().toISOString().split('T')[0]; document.querySelectorAll('input[type="date"]').forEach(el => { if (el.name !== 'jatuh_tempo' && !el.hasAttribute('min')) el.setAttribute('max', today); }); });
</script>
@endsection
