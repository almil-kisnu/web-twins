@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/fitur.css') }}">
<style>
    .view-section {
        display: none;
    }

    .view-section.active {
        display: block;
    }
</style>
@push('styles')
    <style>
        .is-invalid {
            border-color: #dc2626 !important;
        }

        .invalid-feedback {
            display: none;
            color: #dc2626;
            font-size: 12px;
            margin-top: 5px;
            font-weight: 600;
        }

        .is-invalid+.invalid-feedback {
            display: block !important;
        }
    </style>
@endpush

@section('content')
    <div class="fitur-container">
        {{-- TAB NAVIGATION --}}
        @include('keuangan.partials.tabs')

        <div class="main-content-box">

            <!-- SECTION CASHBOX (Daftar Metode Pembayaran) -->
            <div id="view-cashbox" class="view-section active">
                <!-- ACTION BAR -->
                <div class="action-bar" style="margin-bottom: 20px;">
                    <div class="left-actions-group">
                        <div class="search-wrapper">
                            <iconify-icon icon="solar:magnifer-linear" class="search-icon"></iconify-icon>
                            <input type="text" id="cashboxSearch" class="search-input" placeholder="Cari nama cashbox..."
                                onkeyup="filterCashbox()">
                        </div>
                    </div>
                    <div class="right-actions">
                        <button onclick="openModal('modalAddCashbox')" class="btn-action">
                            <iconify-icon icon="solar:add-circle-bold-duotone" style="font-size: 20px;"></iconify-icon>
                            <span>Tambah Cashbox</span>
                        </button>
                    </div>
                </div>

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
                                <tr>
                                    <td colspan="2" style="text-align: center; padding: 30px; color: #888;">Belum ada data
                                        Cashbox.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    {{-- MODAL ADD CASHBOX --}}
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
                    <input type="text" name="nama_metode" class="form-control" placeholder="Contoh: Cash, QRIS, dll"
                        required_js>
                    <div class="invalid-feedback">Nama cashbox wajib diisi</div>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 25px;">
                    <button type="button" onclick="closeModal('modalAddCashbox')" class="btn-action btn-danger"
                        style="flex:1; justify-content:center;">Batal</button>
                    <button type="submit" class="btn-action" style="flex:1; justify-content:center;">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL EDIT CASHBOX --}}
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
                    <input type="text" name="nama_metode" id="edit_nama_metode" class="form-control" required_js>
                    <div class="invalid-feedback">Nama cashbox wajib diisi</div>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 25px;">
                    <button type="button" onclick="closeModal('modalEditCashbox')" class="btn-action btn-danger"
                        style="flex:1; justify-content:center;">Batal</button>
                    <button type="submit" class="btn-action" style="flex:1; justify-content:center;">Simpan
                        Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- HIDDEN DELETE FORM --}}
    <form id="formDeleteCashbox" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <script>
        function openModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                const form = modal.querySelector('form');
                if (form && !id.toLowerCase().includes('edit')) {
                    form.reset();
                    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                }
                modal.style.display = 'flex';
            }
        }

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        function openEditCashbox(uuid, nama) {
            const form = document.getElementById('formEditCashbox');
            form.action = `/keuangan/cashbox/${uuid}`;
            document.getElementById('edit_nama_metode').value = nama;
            openModal('modalEditCashbox');
        }

        function deleteCashbox(uuid, nama) {
            Swal.fire({
                title: 'Hapus Cashbox?',
                text: `Apakah Anda yakin ingin menghapus "${nama}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#718096',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
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
            const rows = document.querySelectorAll('#tbody-cashbox tr');

            rows.forEach(row => {
                if (row.querySelector('.empty-state') || row.cells.length < 2) return;
                const text = row.cells[0].innerText.toLowerCase();
                row.style.display = text.includes(searchText) ? '' : 'none';
            });
        }

        // Handle SweetAlert Alerts
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 2000
            });
        @endif

        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                html: '{!! implode("<br>", $errors->all()) !!}'
            });
        @endif

        // Client-side Validation Logic
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function (e) {
                let isValid = true;
                const inputs = this.querySelectorAll('[required_js]');

                inputs.forEach(input => {
                    if (!input.value.trim()) {
                        isValid = false;
                        input.classList.add('is-invalid');
                    } else {
                        input.classList.remove('is-invalid');
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                }
            });
        });

        // Real-time validation feedback
        document.querySelectorAll('[required_js]').forEach(input => {
            input.addEventListener('input', function () {
                if (this.value.trim()) {
                    this.classList.remove('is-invalid');
                }
            });
        });
    </script>
@endsection