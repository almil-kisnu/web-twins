@extends('layouts.app')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/fitur.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .tab-pill,
        .btn-action,
        .chip,
        .close-modal,
        .btn-filter {
            user-select: none;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }

        .status-hadir {
            background: #E8F5E9;
            color: #2E7D32;
        }

        .status-telat {
            background: #FFF3E0;
            color: #E65100;
        }

        .status-izin {
            background: #E3F2FD;
            color: #1565C0;
        }

        .status-alpha {
            background: #FFEBEE;
            color: #C62828;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }
    </style>

    <div class="fitur-container" id="absensi-app">
        {{-- PILL TABS --}}
        <div class="tab-navigation">
            <a href="#jadwal karyawan" class="tab-pill" onclick="switchTab('penugasan')" id="pill-penugasan">
                <iconify-icon icon="solar:calendar-add-bold-duotone"></iconify-icon>
                <span>Jadwal Karyawan</span>
            </a>
            <a href="#riwayat absensi" class="tab-pill" onclick="switchTab('riwayat')" id="pill-riwayat">
                <iconify-icon icon="solar:history-bold-duotone"></iconify-icon>
                <span>Riwayat Absensi</span>
            </a>
            <a href="#jam operasional" class="tab-pill" onclick="switchTab('jadwal')" id="pill-jadwal">
                <iconify-icon icon="solar:clock-circle-bold-duotone"></iconify-icon>
                <span>Jam Operasional</span>
            </a>
        </div>

        {{-- ACTION BAR --}}
        <div class="action-bar">
            <div style="display: contents;">
                <div class="left-actions-group">
                    <div class="search-wrapper">
                        <iconify-icon icon="solar:magnifer-linear" class="search-icon"></iconify-icon>
                        <input type="text" id="globalSearch" class="search-input" placeholder="Cari data..."
                            onkeyup="filterTable()">
                    </div>

                    <div style="position: relative;">
                        <input type="date" id="dateFilter" onchange="filterTable()"
                            style="opacity: 0; position: absolute; width: 100%; height: 100%; top: 0; left: 0; cursor: pointer; z-index: 2;"
                            title="Filter Tanggal">
                        <button type="button" class="btn-filter" title="Filter Kalender"
                            style="position: relative; z-index: 1;">
                            <iconify-icon icon="solar:calendar-bold-duotone" style="font-size: 24px;"></iconify-icon>
                        </button>
                    </div>

                    @if(Auth::user()->role === 'owner' || (Auth::user()->role === 'kepala_toko' && $outlets->count() > 1))
                        <div class="dropdown">
                            <button type="button" class="btn-filter"
                                title="Filter Toko: {{ $store_id == 'all' ? 'Semua Outlet' : ($outlets->firstWhere('uuid', $store_id)->nama ?? 'Semua') }}"
                                onclick="toggleDropdown(event)">
                                <iconify-icon icon="solar:shop-bold-duotone" style="font-size: 24px;"
                                    class="{{ $store_id != 'all' ? 'text-primary-blue' : '' }}"></iconify-icon>
                            </button>
                            <div class="dropdown-content">
                                <form id="storeForm" method="GET" action="{{ route('absensi.index') }}">
                                    <input type="hidden" name="active_tab" id="storeFormActiveTab" value="">
                                    <input type="hidden" name="store_id" id="storeFormStoreId" value="{{ $store_id }}">
                                </form>
                                @if(Auth::user()->role === 'owner')
                                    <a href="javascript:void(0)"
                                        onclick="document.getElementById('storeFormActiveTab').value = currentTab; document.getElementById('storeFormStoreId').value = 'all'; document.getElementById('storeForm').submit()"
                                        class="{{ $store_id === 'all' ? 'active-dropdown-item' : '' }}">
                                        Semua Outlet
                                    </a>
                                @endif
                                @foreach($outlets as $o)
                                    <a href="javascript:void(0)"
                                        onclick="document.getElementById('storeFormActiveTab').value = currentTab; document.getElementById('storeFormStoreId').value = '{{ $o->uuid }}'; document.getElementById('storeForm').submit()"
                                        class="{{ $store_id == $o->uuid ? 'active-dropdown-item' : '' }}">
                                        {{ $o->nama }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="right-actions">
                    <button type="button" class="btn-action" id="btnAddMain" onclick="openCurrentModal()">
                        <iconify-icon icon="solar:add-circle-bold-duotone"></iconify-icon>
                        <span id="txtAddMain">Tambah</span>
                    </button>
                </div>
            </div>
        </div>


        <form id="formGlobalDeleteShift" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
        <form id="formGlobalDeletePenugasan" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>

        {{-- MAIN BOX --}}
        <div class="main-content-box">
            <div class="table-container">

                <!-- VIEW PENUGASAN JADWAL -->
                <div id="view-penugasan" style="display: none;">
                    <table class="fitur-table">
                        <thead>
                            <tr>
                                <th>TANGGAL</th>
                                <th>KARYAWAN</th>
                                <th>OUTLET</th>
                                <th>SHIFT / JAM KERJA</th>
                                <th style="width: 100px; text-align: center;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($penugasan as $p)
                                <tr class="searchable-row" data-date="{{ $p->tanggal ?? 'permanen' }}">
                                    <td>
                                        @if($p->tanggal)
                                            {{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}
                                        @else
                                            <span class="status-badge" style="background: #E3F2FD; color: #1565C0;">Setiap Hari
                                                (Permanen)</span>
                                        @endif
                                    </td>
                                    <td style="font-weight: 600;">
                                        {{ $p->user->name ?? '-' }}<br>
                                        <small
                                            style="font-weight: normal; color: #666;">{{ $p->user->operator->nama ?? 'Karyawan' }}</small>
                                    </td>
                                    <td>{{ $p->user->store->nama ?? '-' }}</td>
                                    <td>
                                        <span style="font-weight: 600; color: #0081C9;">{{ $p->shift->nama ?? '-' }}</span><br>
                                        @if($p->shift)
                                            <small
                                                style="color: #666;">{{ \Carbon\Carbon::parse($p->shift->waktu_mulai)->format('H:i') }}
                                                - {{ \Carbon\Carbon::parse($p->shift->waktu_selesai)->format('H:i') }}</small>
                                        @endif
                                    </td>
                                    <td style="text-align: center;">
                                        <div style="display: flex; gap: 8px; justify-content: center;">
                                            <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #FBC02D; border-color: #FFF9C4;"
                                                onclick="openEditPenugasan({{ $p->id }}, '{{ addslashes($p->user->name ?? '') }}', '{{ $p->user_id }}', '{{ $p->shift_uuid }}', '{{ $p->tanggal }}', '{{ $p->user->store_id ?? '' }}')"
                                                title="Edit Jadwal">
                                                <iconify-icon icon="solar:pen-bold-duotone"></iconify-icon>
                                            </button>
                                            <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #D9534F; border-color: #ffcccc;"
                                                onclick="deletePenugasan({{ $p->id }}, '{{ $p->user->name ?? '' }}')"
                                                title="Hapus Jadwal">
                                                <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="empty-state">Belum ada penugasan jadwal karyawan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- VIEW SHIFT -->
                <div id="view-jadwal" style="display: none;">
                    <table class="fitur-table">
                        <thead>
                            <tr>
                                <th>NAMA SHIFT</th>
                                <th>OUTLET</th>
                                <th>WAKTU MULAI</th>
                                <th>WAKTU SELESAI</th>
                                @if(Auth::user()->role === 'owner')
                                    <th style="width: 100px; text-align: center;">AKSI</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($shifts as $s)
                                <tr class="searchable-row" data-date="">
                                    <td style="font-weight: 600; color: #333;">{{ $s->nama }}</td>
                                    <td>{{ $s->store->nama ?? '-' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($s->waktu_mulai)->format('H:i') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($s->waktu_selesai)->format('H:i') }}</td>
                                    @if(Auth::user()->role === 'owner')
                                        <td style="text-align: center;">
                                            <div style="display: flex; gap: 8px; justify-content: center;">
                                                <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #FBC02D; border-color: #FFF9C4;"
                                                    onclick="openEditShift({{ json_encode($s) }})" title="Edit">
                                                    <iconify-icon icon="solar:pen-bold-duotone"></iconify-icon>
                                                </button>
                                                <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #D9534F; border-color: #ffcccc;"
                                                    onclick="deleteShift('{{ $s->uuid }}', '{{ $s->nama }}')" title="Hapus">
                                                    <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                                                </button>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="empty-state">Belum ada pengaturan jam operasional.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- VIEW RIWAYAT -->
                <div id="view-riwayat" style="display: none;">
                    <table class="fitur-table">
                        <thead>
                            <tr>
                                <th>TANGGAL</th>
                                <th>KARYAWAN</th>
                                <th>OUTLET</th>
                                <th>JAM MASUK</th>
                                <th>JAM PULANG</th>
                                <th class="text-center">STATUS</th>
                                <th>KETERANGAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($riwayat as $r)
                                <tr class="searchable-row" data-date="{{ $r->tanggal }}">
                                    <td>{{ \Carbon\Carbon::parse($r->tanggal)->format('d/m/Y') }}</td>
                                    <td style="font-weight: 600;">
                                        {{ $r->user->name ?? '-' }}<br>
                                        <small
                                            style="font-weight: normal; color: #666;">{{ $r->user->operator->nama ?? 'Karyawan' }}</small>
                                    </td>
                                    <td>{{ $r->user->store->nama ?? '-' }}</td>
                                    <td>{{ $r->jam_masuk ? \Carbon\Carbon::parse($r->jam_masuk)->format('H:i') : '-' }}</td>
                                    <td>{{ $r->jam_pulang ? \Carbon\Carbon::parse($r->jam_pulang)->format('H:i') : '-' }}</td>
                                    <td class="text-center">
                                        @php
                                            $badgeClass = 'status-hadir';
                                            if (strtolower($r->status) == 'telat')
                                                $badgeClass = 'status-telat';
                                            if (strtolower($r->status) == 'izin')
                                                $badgeClass = 'status-izin';
                                            if (strtolower($r->status) == 'alpha' || strtolower($r->status) == 'absen')
                                                $badgeClass = 'status-alpha';
                                        @endphp
                                        <span class="status-badge {{ $badgeClass }}">{{ ucfirst($r->status ?? '-') }}</span>
                                    </td>
                                    <td>{{ $r->keterangan ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="empty-state">Belum ada riwayat absensi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <!-- MODAL ADD PENUGASAN -->
    <div id="modalAddPenugasan" class="modal-overlay">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h3>Tugaskan Karyawan</h3>
                <button class="close-modal" onclick="closeModal('modalAddPenugasan')">&times;</button>
            </div>
            <form method="POST" action="{{ route('absensi.penugasan.store') }}">
                @csrf

                <div class="form-group">
                    <label>Karyawan</label>
                    <select name="user_id" id="selectKaryawan" class="form-control" required onchange="onKaryawanChange()">
                        <option value="" disabled selected>-- Pilih Karyawan --</option>
                        @foreach($karyawanList as $k)
                            <option value="{{ $k->uuid }}" data-store="{{ $k->store_id }}">{{ $k->name }} -
                                {{ $k->operator->nama ?? 'Karyawan' }} ({{ $k->store->nama ?? 'Semua Outlet' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Pilih Shift / Jam Kerja</label>
                    <select name="shift_uuid" id="selectShift" class="form-control" required>
                        <option value="" id="shiftPlaceholder" disabled selected>-- Pilih Karyawan Dulu --</option>
                        @foreach($shifts as $s)
                            <option class="shift-item" value="{{ $s->uuid }}" data-store="{{ $s->store_id }}"
                                data-original="{{ $s->nama }} | {{ \Carbon\Carbon::parse($s->waktu_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($s->waktu_selesai)->format('H:i') }}">
                                {{ $s->nama }} | {{ \Carbon\Carbon::parse($s->waktu_mulai)->format('H:i') }} -
                                {{ \Carbon\Carbon::parse($s->waktu_selesai)->format('H:i') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 10px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600;">
                        <input type="checkbox" name="is_permanent" id="isPermanentCheck" onchange="toggleDateInput()"
                            style="width: 18px; height: 18px;">
                        Jadikan Jadwal Permanen (Setiap Hari)
                    </label>
                </div>

                <div class="form-group" id="tanggalGroup">
                    <label>Pilih Tanggal Penugasan <span style="color: #E65100; font-size: 0.8rem;">(Bisa pilih banyak
                            tanggal sekaligus)</span></label>
                    <input type="text" name="tanggal" id="tanggalInput" class="form-control multi-datepicker"
                        placeholder="Klik untuk memilih tanggal...">
                </div>

                <button type="submit" class="btn-action"
                    style="width: 100%; justify-content: center; margin-top: 15px;">Simpan Penugasan</button>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT PENUGASAN -->
    <div id="modalEditPenugasan" class="modal-overlay">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h3>Edit Jadwal Karyawan</h3>
                <button class="close-modal" onclick="closeModal('modalEditPenugasan')">&times;</button>
            </div>
            <form id="formEditPenugasan" method="POST" action="">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Karyawan</label>
                    <!-- Disabled agar tidak ganti nama saat edit, kirim via hidden input -->
                    <input type="text" id="editPenugasanUserName" class="form-control" disabled>
                    <input type="hidden" name="user_id" id="editPenugasanUserId">
                </div>

                <div class="form-group">
                    <label>Pilih Shift / Jam Kerja Baru</label>
                    <select name="shift_uuid" id="editPenugasanShift" class="form-control" required>
                        @foreach($shifts as $s)
                            <option value="{{ $s->uuid }}" data-store="{{ $s->store_id }}">
                                {{ $s->nama }} | {{ \Carbon\Carbon::parse($s->waktu_mulai)->format('H:i') }} -
                                {{ \Carbon\Carbon::parse($s->waktu_selesai)->format('H:i') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 10px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600;">
                        <input type="checkbox" name="is_permanent" id="editIsPermanentCheck"
                            onchange="toggleEditDateInput()" style="width: 18px; height: 18px;">
                        Jadikan Jadwal Permanen (Setiap Hari)
                    </label>
                </div>

                <div class="form-group" id="editTanggalGroup">
                    <label>Pilih Tanggal Penugasan</label>
                    <input type="date" name="tanggal" id="editTanggalInput" class="form-control"
                        placeholder="Pilih tanggal...">
                </div>

                <button type="submit" class="btn-action"
                    style="width: 100%; justify-content: center; margin-top: 15px; background: #F59E0B;">Update
                    Penugasan</button>
            </form>
        </div>
    </div>

    <!-- MODAL ADD SHIFT -->
    <div id="modalAddShift" class="modal-overlay">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h3>Tambah Jam Operasional Baru</h3>
                <button class="close-modal" onclick="closeModal('modalAddShift')">&times;</button>
            </div>
            <form method="POST" action="{{ route('absensi.shift.store') }}">
                @csrf

                @if(Auth::user()->role === 'owner' || (Auth::user()->role === 'kepala_toko' && $outlets->count() > 1))
                    <div class="form-group">
                        <label>Outlet</label>
                        <select name="store_id" class="form-control" required>
                            @foreach($outlets as $o)
                                <option value="{{ $o->uuid }}">{{ $o->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <input type="hidden" name="store_id" value="{{ Auth::user()->outlet_id ?? $outlets->first()->uuid ?? '' }}">
                @endif

                <div class="form-group">
                    <label>Nama Shift (Contoh: Pagi / Malam)</label>
                    <input type="text" name="nama" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Waktu Mulai</label>
                    <input type="time" name="waktu_mulai" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Waktu Selesai</label>
                    <input type="time" name="waktu_selesai" class="form-control" required>
                </div>
                <button type="submit" class="btn-action"
                    style="width: 100%; justify-content: center; margin-top: 15px;">Simpan Jam Operasional</button>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT SHIFT -->
    <div id="modalEditShift" class="modal-overlay">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h3>Edit Jam Operasional</h3>
                <button class="close-modal" onclick="closeModal('modalEditShift')">&times;</button>
            </div>
            <form id="formEditShift" method="POST" action="">
                @csrf
                @method('PUT')

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
                <button type="submit" class="btn-action"
                    style="width: 100%; justify-content: center; margin-top: 15px; background: #F59E0B;">Update Jam
                    Operasional</button>
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

        window.addEventListener('click', function (e) {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-content').forEach(d => d.classList.remove('show'));
            }
        });

        let currentTab = '{{ $active_tab ?? "penugasan" }}';
        if (!currentTab) currentTab = 'penugasan';

        setTimeout(() => {
            let alert = document.getElementById('alertSuccess');
            if (alert) alert.style.opacity = '0';
            setTimeout(() => { if (alert) alert.remove(); }, 500);
        }, 3000);

        function switchTab(tab) {
            currentTab = tab;
            document.querySelectorAll('.tab-pill').forEach(el => el.classList.remove('active'));
            const pill = document.getElementById('pill-' + tab);
            if (pill) pill.classList.add('active');

            document.getElementById('view-penugasan').style.display = 'none';
            document.getElementById('view-jadwal').style.display = 'none';
            document.getElementById('view-riwayat').style.display = 'none';

            const view = document.getElementById('view-' + tab);
            if (view) view.style.display = 'block';

            const btnAdd = document.getElementById('btnAddMain');
            const txtAdd = document.getElementById('txtAddMain');

            if (tab === 'jadwal') {
                @if(Auth::user()->role === 'owner')
                    btnAdd.style.display = 'flex';
                    txtAdd.innerText = 'Tambah Shift';
                @else
                    btnAdd.style.display = 'none';
                @endif
                } else if (tab === 'penugasan') {
                btnAdd.style.display = 'flex';
                txtAdd.innerText = 'Beri Tugas';
            } else if (tab === 'riwayat') {
                btnAdd.style.display = 'none';
            }

            filterTable();
        }

        function openCurrentModal() {
            if (currentTab === 'jadwal') openModal('modalAddShift');
            else if (currentTab === 'penugasan') openModal('modalAddPenugasan');
        }

        function openModal(id) {
            document.getElementById(id).style.display = 'flex';
        }

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        function openEditShift(shift) {
            document.getElementById('formEditShift').action = '/absensi/shift/' + shift.uuid;
            document.getElementById('editShiftNama').value = shift.nama;
            document.getElementById('editShiftMulai').value = shift.waktu_mulai.substring(0, 5);
            document.getElementById('editShiftSelesai').value = shift.waktu_selesai.substring(0, 5);
            openModal('modalEditShift');
        }

        function openEditPenugasan(id, userName, userId, shiftUuid, tanggal, storeId) {
            document.getElementById('formEditPenugasan').action = '/absensi/penugasan/' + id;
            document.getElementById('editPenugasanUserName').value = userName;
            document.getElementById('editPenugasanUserId').value = userId;

            const shiftSelect = document.getElementById('editPenugasanShift');
            // Hide shifts not belonging to the user's store
            Array.from(shiftSelect.options).forEach(opt => {
                if (opt.getAttribute('data-store') == storeId) {
                    opt.style.display = '';
                } else {
                    opt.style.display = 'none';
                }
            });
            shiftSelect.value = shiftUuid;

            if (tanggal === '' || tanggal === 'null' || !tanggal) {
                document.getElementById('editIsPermanentCheck').checked = true;
                document.getElementById('editTanggalInput').value = '';
            } else {
                document.getElementById('editIsPermanentCheck').checked = false;
                document.getElementById('editTanggalInput').value = tanggal;
            }
            toggleEditDateInput();
            openModal('modalEditPenugasan');
        }

        function toggleEditDateInput() {
            const isPerm = document.getElementById('editIsPermanentCheck').checked;
            const tgGroup = document.getElementById('editTanggalGroup');
            const tgInput = document.getElementById('editTanggalInput');
            if (isPerm) {
                tgGroup.style.display = 'none';
                tgInput.removeAttribute('required');
            } else {
                tgGroup.style.display = 'block';
                tgInput.setAttribute('required', 'required');
            }
        }

        function deleteShift(uuid, nama) {
            Swal.fire({
                title: `Hapus Shift ${nama}?`,
                text: "Data ini akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#aaa',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    let form = document.getElementById('formGlobalDeleteShift');
                    form.action = '/absensi/shift/' + uuid;
                    form.submit();
                }
            });
        }

        function deletePenugasan(id, nama) {
            Swal.fire({
                title: `Hapus Jadwal ${nama}?`,
                text: "Data ini akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#aaa',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    let form = document.getElementById('formGlobalDeletePenugasan');
                    form.action = '/absensi/penugasan/' + id;
                    form.submit();
                }
            });
        }

        function filterTable() {
            const searchText = document.getElementById('globalSearch').value.toLowerCase();
            const dateRaw = document.getElementById('dateFilter').value;
            let filterDate = '';
            if (dateRaw) {
                let [y, m, d] = dateRaw.split('-');
                filterDate = `${y}-${m}-${d}`;
            }

            const activeTable = document.querySelector(`#view-${currentTab} table`);
            if (!activeTable) return;
            const rows = activeTable.querySelectorAll('tbody tr.searchable-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const textContent = row.innerText.toLowerCase();
                const rowDateRaw = row.getAttribute('data-date');

                let matchesSearch = textContent.includes(searchText);
                let matchesDate = true;

                if (dateRaw && rowDateRaw) {
                    matchesDate = (rowDateRaw === filterDate);
                }

                if (matchesSearch && matchesDate) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            const emptyRow = activeTable.querySelector('.empty-state')?.parentElement;
            if (emptyRow) {
                emptyRow.style.display = visibleCount === 0 ? '' : 'none';
            }
        }

        flatpickr(".multi-datepicker", {
            mode: "multiple",
            dateFormat: "Y-m-d",
            minDate: "today",
            allowInput: true
        });

        function toggleDateInput() {
            const isPermanent = document.getElementById('isPermanentCheck').checked;
            const tanggalGroup = document.getElementById('tanggalGroup');
            const tanggalInput = document.getElementById('tanggalInput');

            if (isPermanent) {
                tanggalGroup.style.display = 'none';
                tanggalInput.removeAttribute('required');
            } else {
                tanggalGroup.style.display = 'block';
                tanggalInput.setAttribute('required', 'required');
            }
        }

        const allPenugasan = @json($penugasanJson ?? []);

        function onKaryawanChange() {
            const selectKar = document.getElementById('selectKaryawan');
            const selectedOpt = selectKar.options[selectKar.selectedIndex];
            const storeId = selectedOpt.getAttribute('data-store');

            const selectShift = document.getElementById('selectShift');
            const shiftOptions = selectShift.querySelectorAll('.shift-item');
            const placeholder = document.getElementById('shiftPlaceholder');

            let firstValid = false;

            shiftOptions.forEach(opt => {
                const optStore = opt.getAttribute('data-store');
                // Hanya tampilkan shift yang outletnya sama dengan outlet karyawan
                if (optStore === storeId || !optStore) {
                    opt.style.display = '';

                    // Cari siapa saja yang SUDAH permanen di shift ini
                    const shiftId = opt.value;
                    const peopleInShift = allPenugasan.filter(p => p.shift_uuid === shiftId && p.tanggal === null && p.store_id === storeId);
                    const names = peopleInShift.map(p => p.user_name).join(', ');

                    const originalText = opt.getAttribute('data-original');
                    if (names.length > 0) {
                        opt.text = `${originalText} (Terisi: ${names})`;
                    } else {
                        opt.text = `${originalText} (Kosong)`;
                    }

                    if (!firstValid) {
                        opt.selected = true;
                        firstValid = true;
                    }
                } else {
                    opt.style.display = 'none';
                }
            });

            if (!firstValid) {
                placeholder.text = 'Belum ada Jam Operasional di outlet ini';
                placeholder.selected = true;
            } else {
                placeholder.text = '-- Pilih Shift --';
            }
        }

        switchTab(currentTab);

        const absensiMenu = document.querySelector('.menu-item[href*="absensi"]');
        if (absensiMenu && typeof setActive === 'function') {
            setActive(absensiMenu, 'Sistem Absensi', 'calendar-days');
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