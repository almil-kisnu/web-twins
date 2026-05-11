@if($transfers->count() > 0)
    <table class="fitur-table" id="transferTable">
        <thead>
            <tr>
                <th>TANGGAL</th>
                <th>DARI</th>
                <th>TUJUAN</th>
                <th>STATUS</th>
                <th>PETUGAS</th>
                <th>AKSI</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transfers as $t)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($t->tanggal)->format('d/m/Y H:i') }}</td>
                    <td>{{ $t->store->nama ?? '-' }}</td>
                    <td>{{ $t->tujuanStore->nama ?? '-' }}</td>
                    <td>
                        @php $s = strtolower($t->status ?: 'pending'); @endphp
                        @if($s == 'selesai')
                            <span class="status-badge status-active" style="padding: 4px 10px; font-size: 10px;">Diterima</span>
                        @elseif($s == 'dikirim')
                            <span class="status-badge" style="padding: 4px 10px; font-size: 10px; background: #E3F2FD; color: #1565C0;">Dikirim</span>
                        @elseif($s == 'disetujui')
                            <span class="status-badge" style="padding: 4px 10px; font-size: 10px; background: #E8F5E9; color: #2E7D32;">Disetujui</span>
                        @else
                            <span class="status-badge" style="padding: 4px 10px; font-size: 10px; background: #FFF3E0; color: #E65100;">{{ $t->status ?: 'Pending' }}</span>
                        @endif
                    </td>
                    <td>{{ $t->user->username ?? '-' }}</td>
                    <td>
                        <div style="display: flex; gap: 8px;">
                            <button class="btn-filter" title="Lihat Detail" style="width: 32px; height: 32px; border-radius: 8px; color: var(--primary-blue);" onclick="viewTransferDetail('{{ $t->uuid }}')">
                                <iconify-icon icon="solar:eye-bold-duotone"></iconify-icon>
                            </button>
                            @if(in_array(strtolower($t->status ?: 'pending'), ['pending', 'proses', '']) && Auth::user()->isOwner())
                                <button class="btn-filter" title="Setujui Transfer" style="width: 32px; height: 32px; border-radius: 8px; color: #0081C9; border-color: #0081C9;" onclick="approveTransfer('{{ $t->uuid }}')">
                                    <iconify-icon icon="solar:check-circle-bold-duotone"></iconify-icon>
                                </button>
                            @endif
                            @if(strtolower($t->status) == 'disetujui' && Auth::user()->store_id == $t->store_id)
                                <button class="btn-filter" title="Kirim Barang" style="width: 32px; height: 32px; border-radius: 8px; color: #E65100; border-color: #FFE0B2;" onclick="shipTransfer('{{ $t->uuid }}')">
                                    <iconify-icon icon="solar:delivery-bold-duotone"></iconify-icon>
                                </button>
                            @endif
                            @if(strtolower($t->status) == 'dikirim' && Auth::user()->store_id == $t->tujuan_store_id)
                                <button class="btn-filter" title="Terima Barang" style="width: 32px; height: 32px; border-radius: 8px; color: #2F855A; border-color: #C6F6D5;" onclick="confirmReceiveTransfer('{{ $t->uuid }}')">
                                    <iconify-icon icon="solar:box-bold-duotone"></iconify-icon>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="pagination-container">
        {{ $transfers->links() }}
    </div>
@else
    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 60px 20px;">
        <div style="width: 80px; height: 80px; background: var(--light-blue); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; color: var(--primary-blue); font-size: 40px;">
            <iconify-icon icon="solar:transfer-vertical-bold-duotone"></iconify-icon>
        </div>
        <h3 style="color: #334155; margin-bottom: 8px;">Belum Ada Riwayat Transfer</h3>
        <p style="color: #64748b; text-align: center; max-width: 400px; margin-bottom: 24px;">Klik tombol di bawah untuk mulai memindahkan stok antar toko.</p>
        <button type="button" class="btn-action" onclick="openTransferModal()" style="padding: 10px 24px;">
            Buat Transfer Baru
        </button>
    </div>
@endif
