<!-- MODAL ADD SHIFT -->
<div id="modalAddShift" class="modal-overlay">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3>Tambah Shift Baru</h3>
            <button class="close-modal" onclick="closeModal('modalAddShift')">&times;</button>
        </div>
        <form method="POST" action="{{ route('absensi.shift.store') }}">
            @csrf
            <div class="form-group">
                <label>Nama Shift (Contoh: Pagi / Malam)</label>
                <input type="text" name="nama" class="form-control" required placeholder="Contoh: Pagi">
            </div>
            <div class="form-group">
                <label>Waktu Mulai</label>
                <input type="time" name="waktu_mulai" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Waktu Selesai</label>
                <input type="time" name="waktu_selesai" class="form-control" required>
            </div>
            <button type="submit" class="btn-action" style="width:100%;justify-content:center;margin-top:15px;">Simpan Shift</button>
        </form>
    </div>
</div>

<!-- MODAL EDIT SHIFT -->
<div id="modalEditShift" class="modal-overlay">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3>Edit Shift</h3>
            <button class="close-modal" onclick="closeModal('modalEditShift')">&times;</button>
        </div>
        <form id="formEditShift" method="POST" action="">
            @csrf @method('PUT')
            <div class="form-group">
                <label>Nama Shift</label>
                <input type="text" name="nama" id="editShiftNama" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Waktu Mulai</label>
                <input type="time" name="waktu_mulai" id="editShiftMulai" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Waktu Selesai</label>
                <input type="time" name="waktu_selesai" id="editShiftSelesai" class="form-control" required>
            </div>
            <button type="submit" class="btn-action" style="width:100%;justify-content:center;margin-top:15px;background:#F59E0B;">Update Shift</button>
        </form>
    </div>
</div>

<script>
function openEditShift(shift) {
    document.getElementById('formEditShift').action = '/absensi/shift/' + shift.uuid;
    document.getElementById('editShiftNama').value = shift.nama;
    document.getElementById('editShiftMulai').value = shift.waktu_mulai ? shift.waktu_mulai.substring(0, 5) : '';
    document.getElementById('editShiftSelesai').value = shift.waktu_selesai ? shift.waktu_selesai.substring(0, 5) : '';
    openModal('modalEditShift');
}
</script>
