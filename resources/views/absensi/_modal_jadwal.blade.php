<!-- MODAL ADD JADWAL -->
<div id="modalAddJadwal" class="modal-overlay">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header">
            <h3>Tambah Jadwal Karyawan</h3>
            <button class="close-modal" onclick="closeModal('modalAddJadwal')">&times;</button>
        </div>
        <form method="POST" action="{{ route('absensi.jadwal.store') }}">
            @csrf

            <div class="form-group">
                <label>Pilih Toko</label>
                <select name="store_id" id="jadwalStoreId" class="form-control" required onchange="onJadwalStoreChange()">
                    <option value="" disabled selected>-- Pilih Toko --</option>
                    @foreach($outlets as $o)
                        <option value="{{ $o->uuid }}" {{ $store_id == $o->uuid ? 'selected' : '' }}>{{ $o->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Karyawan</label>
                <select name="user_id" id="jadwalUserId" class="form-control" required onchange="onKaryawanChange()">
                    <option value="" disabled selected>-- Pilih Toko Dulu --</option>
                    @foreach($karyawanList as $k)
                        <option value="{{ $k->uuid }}" data-store="{{ $k->store_id }}">{{ $k->name }} - {{ $k->operator->nama ?? 'Karyawan' }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Shift</label>
                <select name="shift_id" id="jadwalShiftId" class="form-control" required>
                    <option value="" disabled selected>-- Pilih Shift --</option>
                    @foreach($shifts as $s)
                        <option value="{{ $s->uuid }}">{{ $s->nama }} | {{ \Carbon\Carbon::parse($s->waktu_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($s->waktu_selesai)->format('H:i') }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Hari Kerja <span style="color:#E65100;font-size:0.8rem;">(Bisa pilih lebih dari satu)</span></label>
                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:6px;">
                    @foreach([1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat',6=>'Sabtu',7=>'Minggu'] as $num => $name)
                        <label style="display:flex;align-items:center;gap:6px;padding:8px 14px;border:1.5px solid #ddd;border-radius:10px;cursor:pointer;font-size:13px;font-weight:500;transition:all 0.2s;">
                            <input type="checkbox" name="hari_dalam_minggu[]" value="{{ $num }}" style="width:16px;height:16px;">
                            {{ $name }}
                        </label>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="btn-action" style="width:100%;justify-content:center;margin-top:15px;">Simpan Jadwal</button>
        </form>
    </div>
</div>

<!-- MODAL EDIT JADWAL -->
<div id="modalEditJadwal" class="modal-overlay">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3>Edit Jadwal</h3>
            <button class="close-modal" onclick="closeModal('modalEditJadwal')">&times;</button>
        </div>
        <form id="formEditJadwal" method="POST" action="">
            @csrf @method('PUT')

            <div class="form-group">
                <label>Shift</label>
                <select name="shift_id" id="editJadwalShift" class="form-control" required>
                    @foreach($shifts as $s)
                        <option value="{{ $s->uuid }}">{{ $s->nama }} | {{ \Carbon\Carbon::parse($s->waktu_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($s->waktu_selesai)->format('H:i') }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Hari</label>
                <select name="hari_dalam_minggu" id="editJadwalHari" class="form-control" required>
                    @foreach([1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat',6=>'Sabtu',7=>'Minggu'] as $num => $name)
                        <option value="{{ $num }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn-action" style="width:100%;justify-content:center;margin-top:15px;background:#F59E0B;">Update Jadwal</button>
        </form>
    </div>
</div>

<script>
const existingJadwals = @json($jadwalListJson ?? []);

function onJadwalStoreChange() {
    const storeId = document.getElementById('jadwalStoreId').value;
    const userSelect = document.getElementById('jadwalUserId');
    Array.from(userSelect.options).forEach(opt => {
        if (!opt.value) return;
        opt.style.display = (opt.getAttribute('data-store') === storeId) ? '' : 'none';
    });
    userSelect.value = '';
    onKaryawanChange(); // Reset checkboxes
}

function onKaryawanChange() {
    const userId = document.getElementById('jadwalUserId').value;
    const storeId = document.getElementById('jadwalStoreId').value;
    const checkboxes = document.querySelectorAll('input[name="hari_dalam_minggu[]"]');
    
    // Reset all checkboxes first
    checkboxes.forEach(cb => {
        cb.disabled = false;
        cb.checked = false;
        cb.parentElement.style.opacity = '1';
        cb.parentElement.style.cursor = 'pointer';
        cb.parentElement.title = '';
    });

    if (!userId) return;

    // Filter schedules for this user (optionally also by store if needed, 
    // but usually an employee shouldn't work 2 shifts on same day even in different stores)
    const userSchedules = existingJadwals.filter(j => j.user_id === userId);

    userSchedules.forEach(j => {
        const cb = document.querySelector(`input[name="hari_dalam_minggu[]"][value="${j.hari}"]`);
        if (cb) {
            cb.disabled = true;
            cb.checked = true; // Mark as already taken
            cb.parentElement.style.opacity = '0.5';
            cb.parentElement.style.cursor = 'not-allowed';
            cb.parentElement.title = 'Karyawan sudah memiliki jadwal di hari ini';
        }
    });
}

function openEditJadwal(uuid, shiftId, hari) {
    document.getElementById('formEditJadwal').action = '/absensi/jadwal/' + uuid;
    document.getElementById('editJadwalShift').value = shiftId;
    document.getElementById('editJadwalHari').value = hari;
    openModal('modalEditJadwal');
}

// Initial filter if store is pre-selected
if (document.getElementById('jadwalStoreId').value) {
    onJadwalStoreChange();
}
</script>
