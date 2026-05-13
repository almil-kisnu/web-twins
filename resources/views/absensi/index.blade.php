@extends('layouts.app')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/fitur.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
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

        .status-izin {
            background: #FFF3E0;
            color: #E65100;
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

        .rekap-card {
            background: #f8fafc;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .rekap-stat {
            flex: 1;
            min-width: 120px;
            text-align: center;
            padding: 16px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .rekap-stat .number {
            font-size: 2rem;
            font-weight: 700;
        }

        .rekap-stat .label {
            font-size: 0.8rem;
            color: #666;
            margin-top: 4px;
        }

        .hari-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .hari-chip {
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 600;
            background: #E3F2FD;
            color: #1565C0;
        }

        .inline-select {
            padding: 4px 8px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 12px;
            cursor: pointer;
        }

        .filter-bar {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .filter-bar input,
        .filter-bar select {
            padding: 8px 12px;
            border-radius: 10px;
            border: 1.5px solid #ddd;
            font-size: 13px;
            outline: none;
        }

        .filter-bar input:focus,
        .filter-bar select:focus {
            border-color: var(--primary-blue);
        }

        .tab-pill,
        .btn-action,
        .close-modal,
        .btn-filter {
            user-select: none;
        }
    </style>

    <div class="fitur-container" id="absensi-app">
        {{-- PILL TABS --}}
        <div class="tab-navigation">
            <a href="#" class="tab-pill" onclick="switchTab('shift')" id="pill-shift">
                <iconify-icon icon="solar:clock-circle-bold-duotone"></iconify-icon>
                <span>Master Shift</span>
            </a>
            <a href="#" class="tab-pill" onclick="switchTab('jadwal')" id="pill-jadwal">
                <iconify-icon icon="solar:calendar-add-bold-duotone"></iconify-icon>
                <span>Jadwal Karyawan</span>
            </a>
            <a href="#" class="tab-pill" onclick="switchTab('riwayat')" id="pill-riwayat">
                <iconify-icon icon="solar:history-bold-duotone"></iconify-icon>
                <span>Riwayat Absensi</span>
            </a>
            <a href="#" class="tab-pill" onclick="switchTab('rekap')" id="pill-rekap">
                <iconify-icon icon="solar:chart-2-bold-duotone"></iconify-icon>
                <span>Rekap Absensi</span>
            </a>
        </div>

        {{-- ACTION BAR --}}
        <div class="action-bar">
            <div style="display: contents;">
                <div class="left-actions-group" id="headerLeftActions">
                    <div class="search-wrapper">
                        <iconify-icon icon="solar:magnifer-linear" class="search-icon"></iconify-icon>
                        <input type="text" id="globalSearch" class="search-input" placeholder="masukan nama/hari"
                            onkeyup="filterTable()">
                    </div>
                    @if(Auth::user()->role === 'owner' || (Auth::user()->role === 'kepala_toko' && $outlets->count() > 1))
                        {{-- Form Filter Global --}}
                        <form id="globalFilterForm" method="GET" action="{{ route('absensi.index') }}" style="display:none;">
                            <input type="hidden" name="active_tab" id="filterActiveTab" value="{{ $active_tab }}">
                            <input type="hidden" name="store_id" id="filterStoreId" value="{{ $store_id }}">
                            <input type="hidden" name="shift_id" id="filterShiftId" value="{{ $shift_id }}">
                        </form>

                        <div style="display: flex; gap: 8px;">
                            {{-- Dropdown Toko --}}
                            <div class="dropdown">
                                <button type="button" class="btn-filter" title="Filter Toko" onclick="toggleDropdown(event)">
                                    <iconify-icon icon="solar:shop-bold-duotone" style="font-size: 24px;"
                                        class="{{ $store_id != 'all' ? 'text-primary-blue' : '' }}"></iconify-icon>
                                </button>
                                <div class="dropdown-content">
                                    <a href="javascript:void(0)" onclick="applyGlobalFilter('store', 'all')" class="{{ $store_id === 'all' ? 'active-dropdown-item' : '' }}">Semua Outlet</a>
                                    @foreach($outlets as $o)
                                        <a href="javascript:void(0)" onclick="applyGlobalFilter('store', '{{ $o->uuid }}')" class="{{ $store_id == $o->uuid ? 'active-dropdown-item' : '' }}">{{ $o->nama }}</a>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Dropdown Shift --}}
                            <div class="dropdown">
                                <button type="button" class="btn-filter" title="Filter Shift" onclick="toggleDropdown(event)">
                                    <iconify-icon icon="solar:clock-circle-bold-duotone" style="font-size: 24px;"
                                        class="{{ $shift_id != 'all' ? 'text-primary-blue' : '' }}"></iconify-icon>
                                </button>
                                <div class="dropdown-content">
                                    <a href="javascript:void(0)" onclick="applyGlobalFilter('shift', 'all')" class="{{ $shift_id === 'all' ? 'active-dropdown-item' : '' }}">Semua Shift</a>
                                    @foreach($shifts as $s)
                                        <a href="javascript:void(0)" onclick="applyGlobalFilter('shift', '{{ $s->uuid }}')" class="{{ $shift_id == $s->uuid ? 'active-dropdown-item' : '' }}">{{ $s->nama }}</a>
                                    @endforeach
                                </div>
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

        <form id="formGlobalDelete" method="POST" style="display: none;">@csrf @method('DELETE')</form>

        {{-- MAIN BOX --}}
        <div class="main-content-box">
            <div class="table-container">

                @include('absensi._tab_shift')
                @include('absensi._tab_jadwal')
                @include('absensi._tab_riwayat')
                @include('absensi._tab_rekap')

            </div>
        </div>
    </div>

    {{-- MODALS --}}
    @include('absensi._modal_shift')
    @include('absensi._modal_jadwal')

    <script>
        function applyGlobalFilter(type, value) {
            document.getElementById('filterActiveTab').value = currentTab;
            if (type === 'store') document.getElementById('filterStoreId').value = value;
            if (type === 'shift') document.getElementById('filterShiftId').value = value;
            document.getElementById('globalFilterForm').submit();
        }

        function toggleDropdown(event) {
            event.stopPropagation();
            const dd = event.currentTarget.nextElementSibling;
            document.querySelectorAll('.dropdown-content').forEach(el => {
                if (el !== dd) el.classList.remove('show');
            });
            dd.classList.toggle('show');
        }

        window.addEventListener('click', e => {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-content').forEach(d => d.classList.remove('show'));
            }
        });

        let currentTab = '{{ $active_tab ?? "shift" }}';

        function switchTab(tab) {
            currentTab = tab;
            document.querySelectorAll('.tab-pill').forEach(p => p.classList.remove('active'));
            document.getElementById('pill-' + tab).classList.add('active');

            ['shift', 'jadwal', 'riwayat', 'rekap'].forEach(t => {
                const v = document.getElementById('view-' + t);
                if (v) v.style.display = (t === tab) ? 'block' : 'none';
            });

            // Handle Header Actions Visibility
            const leftActions = document.getElementById('headerLeftActions');
            const btnAdd = document.getElementById('btnAddMain');
            const txtAdd = document.getElementById('txtAddMain');
            const actionBar = document.querySelector('.action-bar');

            const showGlobalFilters = (tab === 'jadwal');
            const showAddButton = (tab === 'shift' || tab === 'jadwal');

            if (leftActions) leftActions.style.display = showGlobalFilters ? 'flex' : 'none';
            if (btnAdd) {
                btnAdd.style.display = showAddButton ? 'flex' : 'none';
                if (tab === 'shift') txtAdd.innerText = 'Tambah Shift';
                if (tab === 'jadwal') txtAdd.innerText = 'Tambah Jadwal';
            }

            // Hide action bar entirely if no filters and no add button
            if (actionBar) {
                actionBar.style.display = (showGlobalFilters || showAddButton) ? 'flex' : 'none';
            }

            filterTable();
        }

        function openCurrentModal() {
            if (currentTab === 'shift') openModal('modalAddShift');
            else if (currentTab === 'jadwal') openModal('modalAddJadwal');
        }

        function openModal(id) { document.getElementById(id).style.display = 'flex'; }
        function closeModal(id) { document.getElementById(id).style.display = 'none'; }

        function filterTable() {
            const text = document.getElementById('globalSearch').value.toLowerCase();
            const tbl = document.querySelector(`#view-${currentTab} table`);
            if (!tbl) return;
            const rows = tbl.querySelectorAll('tbody tr.searchable-row');
            let vis = 0;
            rows.forEach(r => {
                const match = r.innerText.toLowerCase().includes(text);
                r.style.display = match ? '' : 'none';
                if (match) vis++;
            });
            const empty = tbl.querySelector('.empty-state')?.parentElement;
            if (empty) empty.style.display = vis === 0 ? '' : 'none';
        }

        function globalDelete(url, label) {
            Swal.fire({
                title: `Hapus ${label}?`, text: 'Data ini akan dihapus permanen!', icon: 'warning',
                showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#aaa',
                confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal'
            }).then(r => {
                if (r.isConfirmed) {
                    let f = document.getElementById('formGlobalDelete');
                    f.action = url; f.submit();
                }
            });
        }

        function updateAbsensiStatus(uuid) {
            const sel = document.getElementById('status-' + uuid);
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/absensi/riwayat/' + uuid + '/status';
            form.innerHTML = '@csrf @method("PUT") <input type="hidden" name="status_kehadiran" value="' + sel.value + '">';
            document.body.appendChild(form);
            form.submit();
        }

        switchTab(currentTab);

        @if(session('success'))
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: '{{ session("success") }}', showConfirmButton: false, timer: 2000 });
        @endif
        @if(session('error'))
            Swal.fire({ icon: 'error', title: 'Gagal!', text: '{{ session("error") }}' });
        @endif
        @if($errors->any())
            Swal.fire({ icon: 'error', title: 'Terjadi Kesalahan!', html: '<ul style="text-align:left">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>' });
        @endif
    </script>
@endsection