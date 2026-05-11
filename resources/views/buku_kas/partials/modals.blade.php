<!-- Modal Pengeluaran -->
<div id="modalPengeluaran" class="modal-overlay">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header"><h3>Tambah Pengeluaran</h3><button type="button" class="close-modal" onclick="closeModal('modalPengeluaran')">&times;</button></div>
        <form action="{{ route('keuangan.cashflow.store') }}" method="POST">
            @csrf
            <div class="modal-body-scroll">
                @if($store_id === 'all')
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="font-size: 11px; color: #888; display: block;">Pilih Outlet Tujuan *</label>
                        <select name="store_id" class="form-control" required>
                            <option value="">-- Pilih Outlet --</option>
                            @foreach($outlets as $o)<option value="{{ $o->uuid }}">{{ $o->nama }}</option>@endforeach
                        </select>
                    </div>
                @else <input type="hidden" name="store_id" value="{{ $store_id }}"> @endif
                <input type="hidden" name="jenis" value="Pengeluaran">
                <div class="form-group" style="border: 1px solid #ddd; border-radius: 8px; padding: 10px; margin-bottom: 15px;"><label style="font-size: 11px; color: #888; display: block;">Tanggal Transaksi *</label><input type="date" name="tanggal" class="form-control" style="border:none; padding:5px 0" value="{{ date('Y-m-d') }}" required></div>
                <div class="form-group"><label>Nominal *</label><div class="nominal-wrapper"><input type="number" name="nominal" class="form-control" placeholder="0" required></div></div>
                <div class="form-group" style="margin-bottom: 15px;"><label style="font-size: 11px; color: #888; display: block;">Metode Pembayaran *</label><select name="metode_pembayaran" class="form-control" required><option value="">-- Pilih Metode --</option>@foreach($paymentMethods as $pm)<option value="{{ $pm->uuid }}">{{ $pm->nama_metode }}</option>@endforeach</select></div>
                <div class="form-group"><label>Keterangan *</label><textarea name="keterangan" id="ketPengeluaran" class="form-control" style="min-height: 80px;" placeholder="Tulis keterangan..." required></textarea><div class="chips-container">@foreach(['Gaji', 'Sewa Tempat', 'Listrik', 'Air', 'Bensin', 'Bahan Baku', 'Lain-lain'] as $sar)<button type="button" class="chip" onclick="document.getElementById('ketPengeluaran').value = '{{ $sar }}'">{{ $sar }}</button>@endforeach</div></div>
            </div>
            <div class="modal-footer"><button type="button" onclick="closeModal('modalPengeluaran')" class="btn-action" style="flex:1; background:#f1f5f9; color:#64748b; justify-content:center;">Batal</button><button type="submit" class="btn-action" style="flex:1; justify-content:center; background:#0081C9; color:white;">Simpan</button></div>
        </form>
    </div>
</div>

<!-- Modal Pemasukan -->
<div id="modalPemasukan" class="modal-overlay">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header"><h3>Tambah Pemasukan Lainnya</h3><button type="button" class="close-modal" onclick="closeModal('modalPemasukan')">&times;</button></div>
        <form action="{{ route('keuangan.cashflow.store') }}" method="POST">
            @csrf
            <div class="modal-body-scroll">
                @if($store_id === 'all')
                    <div class="form-group" style="margin-bottom: 15px;"><label style="font-size: 11px; color: #888; display: block;">Pilih Outlet Tujuan *</label><select name="store_id" class="form-control" required><option value="">-- Pilih Outlet --</option>@foreach($outlets as $o)<option value="{{ $o->uuid }}">{{ $o->nama }}</option>@endforeach</select></div>
                @else <input type="hidden" name="store_id" value="{{ $store_id }}"> @endif
                <input type="hidden" name="jenis" value="Pemasukan">
                <div class="form-group" style="border: 1px solid #ddd; border-radius: 8px; padding: 10px; margin-bottom: 15px;"><label style="font-size: 11px; color: #888; display: block;">Tanggal Transaksi *</label><input type="date" name="tanggal" class="form-control" style="border:none; padding:5px 0" value="{{ date('Y-m-d') }}" required></div>
                <div class="form-group"><label>Nominal *</label><div class="nominal-wrapper"><input type="number" name="nominal" class="form-control" placeholder="0" required></div></div>
                <div class="form-group" style="margin-bottom: 15px;"><label style="font-size: 11px; color: #888; display: block;">Metode Pembayaran *</label><select name="metode_pembayaran" class="form-control" required><option value="">-- Pilih Metode --</option>@foreach($paymentMethods as $pm)<option value="{{ $pm->uuid }}">{{ $pm->nama_metode }}</option>@endforeach</select></div>
                <div class="form-group"><label>Keterangan *</label><textarea name="keterangan" id="ketPemasukan" class="form-control" style="min-height: 80px;" placeholder="Tulis keterangan..." required></textarea><div class="chips-container">@foreach(['Modal Awal', 'Investasi', 'Pinjaman', 'Hibah', 'Lain-lain'] as $sar)<button type="button" class="chip" onclick="document.getElementById('ketPemasukan').value = '{{ $sar }}'">{{ $sar }}</button>@endforeach</div></div>
            </div>
            <div class="modal-footer"><button type="button" onclick="closeModal('modalPemasukan')" class="btn-action" style="flex:1; background:#f1f5f9; color:#64748b; justify-content:center;">Batal</button><button type="submit" class="btn-action" style="flex:1; justify-content:center; background:#2E7D32; color:white;">Simpan</button></div>
        </form>
    </div>
</div>

<!-- Modal Hutang -->
<div id="modalHutang" class="modal-overlay">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header"><h3>Tambah Hutang Baru</h3><button type="button" class="close-modal" onclick="closeModal('modalHutang')">&times;</button></div>
        <form action="{{ route('keuangan.debt.store') }}" method="POST">
            @csrf
            <div class="modal-body-scroll">
                @if($store_id === 'all')
                    <div class="form-group" style="margin-bottom: 15px;"><label style="font-size: 11px; color: #888; display: block;">Pilih Outlet Tujuan *</label><select name="store_id" class="form-control" required><option value="">-- Pilih Outlet --</option>@foreach($outlets as $o)<option value="{{ $o->uuid }}">{{ $o->nama }}</option>@endforeach</select></div>
                @else <input type="hidden" name="store_id" value="{{ $store_id }}"> @endif
                <input type="hidden" name="tipe" value="Hutang">
                <div class="form-group"><label>Supplier / Kontak *</label><select name="kontak_nama" class="form-control" required><option value="">-- Pilih Kontak --</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->nama }}">{{ $supplier->nama }}</option>@endforeach</select></div>
                <div class="form-group"><label>Total Nilai Hutang *</label><div class="nominal-wrapper"><input type="number" name="nominal" class="form-control" placeholder="0" required></div></div>
                <div class="form-group"><label>Opsi: Uang Muka / DP</label><div class="nominal-wrapper"><input type="number" name="uang_muka" class="form-control" placeholder="Masukkan jika ada DP"></div></div>
                <div class="form-group" style="margin-bottom: 15px;"><label style="font-size: 11px; color: #888; display: block;">Metode Pembayaran (Untuk DP)</label><select name="metode_pembayaran" class="form-control"><option value="">-- Pilih Metode --</option>@foreach($paymentMethods as $pm)<option value="{{ $pm->uuid }}">{{ $pm->nama_metode }}</option>@endforeach</select></div>
                <div class="form-group"><label>Jatuh Tempo *</label><input type="date" name="jatuh_tempo" class="form-control" required></div>
            </div>
            <div class="modal-footer"><button type="button" onclick="closeModal('modalHutang')" class="btn-action" style="flex:1; background:#f1f5f9; color:#64748b; justify-content:center;">Batal</button><button type="submit" class="btn-action" style="flex:1; justify-content:center; background:#0081C9; color:white;">Simpan</button></div>
        </form>
    </div>
</div>

<!-- Modal Piutang -->
<div id="modalPiutang" class="modal-overlay">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header"><h3>Tambah Piutang Baru</h3><button type="button" class="close-modal" onclick="closeModal('modalPiutang')">&times;</button></div>
        <form action="{{ route('keuangan.debt.store') }}" method="POST">
            @csrf
            <div class="modal-body-scroll">
                @if($store_id === 'all')
                    <div class="form-group" style="margin-bottom: 15px;"><label style="font-size: 11px; color: #888; display: block;">Pilih Outlet Tujuan *</label><select name="store_id" class="form-control" required><option value="">-- Pilih Outlet --</option>@foreach($outlets as $o)<option value="{{ $o->uuid }}">{{ $o->nama }}</option>@endforeach</select></div>
                @else <input type="hidden" name="store_id" value="{{ $store_id }}"> @endif
                <input type="hidden" name="tipe" value="Piutang">
                <div class="form-group"><label>Customer / Kontak *</label><select name="kontak_nama" class="form-control" required><option value="">-- Pilih Kontak --</option>@foreach($customers as $customer)<option value="{{ $customer->nama }}">{{ $customer->nama }}</option>@endforeach</select></div>
                <div class="form-group"><label>Total Nilai Piutang *</label><div class="nominal-wrapper"><input type="number" name="nominal" class="form-control" placeholder="0" required></div></div>
                <div class="form-group"><label>Opsi: DP / Terbayar</label><div class="nominal-wrapper"><input type="number" name="uang_muka" class="form-control" placeholder="Masukkan jika ada DP"></div></div>
                <div class="form-group" style="margin-bottom: 15px;"><label style="font-size: 11px; color: #888; display: block;">Metode Pembayaran (Untuk DP)</label><select name="metode_pembayaran" class="form-control"><option value="">-- Pilih Metode --</option>@foreach($paymentMethods as $pm)<option value="{{ $pm->uuid }}">{{ $pm->nama_metode }}</option>@endforeach</select></div>
                <div class="form-group"><label>Jatuh Tempo *</label><input type="date" name="jatuh_tempo" class="form-control" required></div>
            </div>
            <div class="modal-footer"><button type="button" onclick="closeModal('modalPiutang')" class="btn-action" style="flex:1; background:#f1f5f9; color:#64748b; justify-content:center;">Batal</button><button type="submit" class="btn-action" style="flex:1; justify-content:center; background:#2E7D32; color:white;">Simpan</button></div>
        </form>
    </div>
</div>

<!-- COMMON MODALS FOR DEBT & CASHFLOW DETAIL/EDIT -->
<div id="modalDetailDebt" class="modal-overlay">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header"><h3 id="debtDetailTitle">Detail Tagihan</h3><button type="button" class="close-modal" onclick="closeModal('modalDetailDebt')">&times;</button></div>
        <div class="modal-body-scroll">
            <div style="padding-bottom: 20px; border-bottom: 1px solid #f1f5f9;"><div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;"><div><div style="font-size: 12px; color: #64748b; margin-bottom: 4px;">Nama Kontak</div><div id="debtDetailContact" style="font-size: 18px; font-weight: 700; color: #1e293b;">-</div></div><div style="text-align: right;"><div style="font-size: 12px; color: #64748b; margin-bottom: 4px;">Sisa Tagihan</div><div id="debtDetailSisa" style="font-size: 18px; font-weight: 700; color: var(--primary-blue);">Rp 0</div></div></div><div style="background: #f8fafc; border-radius: 12px; padding: 15px; display: grid; grid-template-columns: 1fr 1fr; gap: 15px;"><div><div style="font-size: 11px; color: #64748b; margin-bottom: 2px;">Total Tagihan</div><div id="debtDetailTotal" style="font-weight: 600;">Rp 0</div></div><div><div style="font-size: 11px; color: #64748b; margin-bottom: 2px;">Jatuh Tempo</div><div id="debtDetailTempo" style="font-weight: 600;">-</div></div></div><div id="debtProductSection" style="display: none; margin-top: 15px; border-top: 1px dashed #e2e8f0; padding-top: 15px;"><div style="font-size: 12px; font-weight: 700; color: #1e293b; margin-bottom: 10px;">Daftar Produk:</div><div id="debtProductList" style="display: flex; flex-direction: column; gap: 8px;"></div></div></div>
            <div style="padding-top: 20px;"><div style="font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 12px; display: flex; justify-content: space-between;"><span>Riwayat Pembayaran</span><button type="button" id="btnBukaModalBayar" class="btn-action" style="padding: 4px 10px; font-size: 11px; background: #E8F5E9; color: #2E7D32;">+ Bayar Cicilan</button></div><div id="debtHistoryList" style="display: flex; flex-direction: column; gap: 10px;"></div></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn-action" style="background: #f1f5f9; color: #475569; width: 100%; justify-content: center;" onclick="closeModal('modalDetailDebt')">Tutup</button></div>
    </div>
</div>
<div id="modalBayarDebt" class="modal-overlay">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header"><h3>Catat Pembayaran</h3><button type="button" class="close-modal" onclick="closeModal('modalBayarDebt')">&times;</button></div>
        <form id="formBayarDebt" method="POST">@csrf
            <div class="modal-body-scroll">
                <div class="form-group"><label>Nominal Bayar *</label><div class="nominal-wrapper"><input type="number" name="bayar" id="inputBayarDebt" class="form-control" placeholder="0" required></div><div id="bayarMaxInfo" style="font-size: 11px; color: #64748b; margin-top: 5px;"></div></div><div class="form-group" style="margin-top: 15px;"><label>Metode Pembayaran *</label><select name="metode_pembayaran" class="form-control" required><option value="">-- Pilih Metode --</option>@foreach($paymentMethods as $pm)<option value="{{ $pm->uuid }}">{{ $pm->nama_metode }}</option>@endforeach</select></div>
            </div>
            <div class="modal-footer"><button type="button" onclick="closeModal('modalBayarDebt')" class="btn-action" style="flex:1; background:#f1f5f9; color:#64748b; justify-content:center;">Batal</button><button type="submit" class="btn-action" style="flex:1; justify-content:center; background:#0081C9; color:white;">Simpan</button></div>
        </form>
    </div>
</div>
<div id="modalEditDebt" class="modal-overlay">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header"><h3 id="editDebtTitle">Edit Tagihan</h3><button type="button" class="close-modal" onclick="closeModal('modalEditDebt')">&times;</button></div>
        <form id="modalEditDebtForm" method="POST">@csrf @method('PUT')
            <div class="modal-body-scroll">
                <div class="form-group"><label id="editDebtContactLabel">Nama / Kontak *</label><input type="text" id="editDebtContactInput" name="kontak_nama" class="form-control" required></div><div class="form-group"><label>Total Nilai *</label><div class="nominal-wrapper"><input type="number" id="editDebtNominalInput" name="nominal" class="form-control" placeholder="0" required></div></div><div class="form-group"><label>Jatuh Tempo *</label><input type="date" id="editDebtTempoInput" name="jatuh_tempo" class="form-control" required></div>
            </div>
            <div class="modal-footer"><button type="button" onclick="closeModal('modalEditDebt')" class="btn-action" style="flex:1; background:#f1f5f9; color:#64748b; justify-content:center;">Batal</button><button type="submit" class="btn-action" style="flex:1; justify-content:center; background: #007BFF; color:white;">Update</button></div>
        </form>
    </div>
</div>

<div id="modalEditCashFlow" class="modal-overlay">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header"><h3 id="editCfTitle">Edit Transaksi</h3><button type="button" class="close-modal" onclick="closeModal('modalEditCashFlow')">&times;</button></div>
        <form id="formEditCashFlow" method="POST">@csrf @method('PUT')
            <div class="modal-body-scroll">
                <div class="form-group" style="border: 1px solid #ddd; border-radius: 8px; padding: 10px; margin-bottom: 15px;"><label style="font-size: 11px; color: #888; display: block;">Tanggal Transaksi</label><input type="date" name="tanggal" id="editCfTanggalInput" class="form-control" style="border:none; padding:5px 0" required></div><div class="form-group"><label>Nominal *</label><div class="nominal-wrapper"><input type="number" name="nominal" id="editCfNominalInput" class="form-control" placeholder="0" required></div></div><div class="form-group" style="margin-bottom: 15px;"><label style="font-size: 11px; color: #888; display: block;">Metode Pembayaran *</label><select name="metode_pembayaran" id="editCfMetodeInput" class="form-control" style="border: 1px solid #ddd; padding: 8px; border-radius: 8px;" required><option value="">-- Pilih Metode --</option>@foreach($paymentMethods as $pm)<option value="{{ $pm->uuid }}">{{ $pm->nama_metode }}</option>@endforeach</select></div><div class="form-group"><label>Keterangan</label><textarea name="keterangan" id="editCfKeteranganInput" class="form-control" style="min-height: 80px;" placeholder="Tulis keterangan..."></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" onclick="closeModal('modalEditCashFlow')" class="btn-action" style="flex:1; background:#f1f5f9; color:#64748b; justify-content:center;">Batal</button><button type="submit" class="btn-action" style="flex:1; justify-content:center; background: #007BFF; color:white;">Update</button></div>
        </form>
    </div>
</div>

<div id="modalDetailCashFlow" class="modal-overlay">
    <div class="modal-content" style="max-width: 380px;">
        <div class="modal-header" style="margin-bottom: 0; padding-bottom: 0;"><h3></h3><button type="button" class="close-modal" onclick="closeModal('modalDetailCashFlow')">&times;</button></div>
        <div class="modal-body-scroll">
            <div style="text-align: center;"><div id="cfIcon" style="width: 60px; height: 60px; border-radius: 30px; display: inline-flex; justify-content: center; align-items: center; font-size: 30px; margin-bottom: 10px;"></div><h4 id="cfTitle" style="margin: 0; color: #333; font-size: 16px; font-weight: 700;">Detail Transaksi</h4><h2 id="cfNominal" style="margin: 10px 0 25px 0; font-size: 28px; color: #1e293b;">Rp 0</h2></div>
            <div style="font-size: 13px; color: #475569; display: flex; flex-direction: column; gap: 15px;">
                <div style="display: flex; gap: 10px;"><iconify-icon icon="solar:document-text-outline" style="font-size: 18px; color: #94a3b8;"></iconify-icon><div><div style="color: #94a3b8; font-size: 11px; margin-bottom: 2px;">Keterangan</div><div id="cfKeterangan" style="font-weight: 600;">-</div></div></div>
                <div style="display: flex; gap: 10px;"><iconify-icon icon="solar:clock-circle-outline" style="font-size: 18px; color: #94a3b8;"></iconify-icon><div><div style="color: #94a3b8; font-size: 11px; margin-bottom: 2px;">Tanggal & Waktu</div><div id="cfTanggal" style="font-weight: 600;">-</div></div></div>
                <div style="display: flex; gap: 10px;"><iconify-icon icon="solar:shop-outline" style="font-size: 18px; color: #94a3b8;"></iconify-icon><div><div style="color: #94a3b8; font-size: 11px; margin-bottom: 2px;">Toko</div><div id="cfToko" style="font-weight: 600;">-</div></div></div>
                <div style="display: flex; gap: 10px;"><iconify-icon icon="solar:user-outline" style="font-size: 18px; color: #94a3b8;"></iconify-icon><div><div style="color: #94a3b8; font-size: 11px; margin-bottom: 2px;">Karyawan</div><div id="cfKaryawan" style="font-weight: 600;">-</div></div></div>
                <div style="display: flex; gap: 10px;"><iconify-icon icon="solar:card-outline" style="font-size: 18px; color: #94a3b8;"></iconify-icon><div><div style="color: #94a3b8; font-size: 11px; margin-bottom: 2px;">Metode Pembayaran</div><div id="cfMetode" style="font-weight: 600;">-</div></div></div>
            </div>
            <div id="cfProductSection" style="display: none; margin-top: 20px;"><div style="font-size: 12px; font-weight: 700; color: #1e293b; margin-bottom: 10px; border-top: 1px dashed #e2e8f0; padding-top: 15px;">Daftar Produk / Item:</div><div id="cfProductList" style="display: flex; flex-direction: column; gap: 8px;"></div></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn-action" style="background: #f1f5f9; color: #475569; width: 100%; justify-content: center;" onclick="closeModal('modalDetailCashFlow')">Tutup</button></div>
    </div>
</div>

<div id="modalExport" class="modal-overlay">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header"><h3>Export Data <span id="exportFormatLabel"></span></h3><button type="button" class="close-modal" onclick="closeModal('modalExport')">&times;</button></div>
        <form id="formExport" method="GET" action="{{ route('keuangan.export') }}">
            <input type="hidden" name="format" id="exportFormatInput" value=""><input type="hidden" name="store_id" value="{{ $store_id }}">
            <div class="modal-body-scroll">
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
            </div>
            <div class="modal-footer"><button type="button" onclick="closeModal('modalExport')" class="btn-action btn-danger" style="flex:1; justify-content:center;">Batal</button><button type="submit" class="btn-action" style="flex:1; justify-content:center; background: #2E7D32; color:white;" onclick="setTimeout(() => closeModal('modalExport'), 1000)"><iconify-icon icon="solar:download-bold-duotone"></iconify-icon> Download</button></div>
        </form>
    </div>
</div>
