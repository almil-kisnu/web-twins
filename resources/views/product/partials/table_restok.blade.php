@if($purchases->count() > 0)
    <table class="fitur-table" id="restokTable">
        <thead>
            <tr>
                <th>TANGGAL</th>
                <th>SUPPLIER</th>
                <th>TOTAL</th>
                <th>STATUS</th>
                <th>PETUGAS</th>
                <th>AKSI</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchases as $p)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y H:i') }}</td>
                    <td>{{ $p->contact->nama ?? 'Umum' }}</td>
                    <td style="font-weight: 700;">Rp {{ number_format($p->total, 0, ',', '.') }}</td>
                    <td>
                        @php 
                            $isHutang = $p->bayar < $p->total; 
                            $percent = $p->total > 0 ? round(($p->bayar / $p->total) * 100) : 0;
                        @endphp
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <span class="status-badge {{ $isHutang ? 'status-inactive' : 'status-active' }}" style="padding: 2px 8px; font-size: 9px; width: fit-content; background: {{ $isHutang ? '#FFF5F5' : '#F0FFF4' }}; color: {{ $isHutang ? '#C53030' : '#2F855A' }};">
                                {{ $isHutang ? 'Hutang' : 'Lunas' }}
                            </span>
                            @if($isHutang)
                                <div style="width: 100px; height: 4px; background: #eee; border-radius: 10px; overflow: hidden; margin-top: 2px;">
                                    <div style="width: {{ $percent }}%; height: 100%; background: #ef4444; border-radius: 10px;"></div>
                                </div>
                                <small style="font-size: 9px; color: #666;">Terbayar {{ $percent }}%</small>
                            @endif
                        </div>
                    </td>
                    <td>{{ $p->user->name ?? '-' }}</td>
                    <td>
                        <div style="display: flex; gap: 8px;">
                            <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: var(--primary-blue);" onclick="viewPurchaseDetail('{{ $p->uuid }}')" title="Lihat Detail">
                                <iconify-icon icon="solar:eye-bold-duotone"></iconify-icon>
                            </button>
                            @if($isHutang)
                                <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #E53E3E; border-color: #FED7D7; background: #FFF5F5;" onclick="openPayDebtModal('{{ $p->uuid }}', {{ $p->total - $p->bayar }})" title="Bayar Hutang">
                                    <iconify-icon icon="solar:card-transfer-bold-duotone"></iconify-icon>
                                </button>
                            @endif
                            <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #ef4444; border-color: #fee2e2; background: #fff5f5;" onclick="deleteRestok('{{ $p->uuid }}')" title="Hapus Restok">
                                <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                            </button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="pagination-container">
        {{ $purchases->links() }}
    </div>
@else
    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 60px 20px;">
        <div style="width: 80px; height: 80px; background: var(--light-blue); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; color: var(--primary-blue); font-size: 40px;">
            <iconify-icon icon="solar:box-bold-duotone"></iconify-icon>
        </div>
        <h3 style="color: #334155; margin-bottom: 8px;">Belum Ada Riwayat Restok</h3>
        <p style="color: #64748b; text-align: center; max-width: 400px; margin-bottom: 24px;">Klik tombol di bawah untuk mulai menambah stok produk Anda.</p>
        <button type="button" class="btn-action" onclick="openRestokModal()" style="padding: 10px 24px;">
            Mulai Restok Sekarang
        </button>
    </div>
@endif
