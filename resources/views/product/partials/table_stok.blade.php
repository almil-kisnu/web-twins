<div style="display: flex; gap: 15px; margin-bottom: 20px;">
    <div style="background: white; padding: 15px 20px; border-radius: 12px; border: 1px solid #eee; display: flex; align-items: center; gap: 12px; flex: 1; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
        <div style="background: #FFEBEE; color: #C62828; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <iconify-icon icon="solar:box-minimalistic-bold-duotone"></iconify-icon>
        </div>
        <div>
            <div style="font-size: 12px; color: #888;">📦 Stok Menipis</div>
            <div style="font-size: 18px; font-weight: 700;">{{ $stok_habis_count ?? 0 }}</div>
        </div>
    </div>
    <div style="background: white; padding: 15px 20px; border-radius: 12px; border: 1px solid #eee; display: flex; align-items: center; gap: 12px; flex: 1; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
        <div style="background: #FFF3E0; color: #E65100; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <iconify-icon icon="solar:danger-bold-duotone"></iconify-icon>
        </div>
        <div>
            <div style="font-size: 12px; color: #888;">⚠️ Hampir Expired</div>
            <div style="font-size: 18px; font-weight: 700;">{{ $expired_count ?? 0 }}</div>
        </div>
    </div>
</div>

<table class="fitur-table" id="stokTable">
    <thead>
        <tr>
            <th>PRODUK</th>
            <th>STOK</th>
            <th>TGL MASUK</th>
            <th>KADALUARSA</th>
            <th>STATUS</th>
            <th>AKSI</th>
        </tr>
    </thead>
    <tbody>
        @forelse($alerts as $alert)
            @php
                $minThreshold = $alert->stok_minimum ?? 10;
                $isLowStock = $alert->stok <= $minThreshold;
                $isExpired = $alert->kadaluarsa && \Carbon\Carbon::parse($alert->kadaluarsa)->isPast();
                $isNearExp = $alert->kadaluarsa && 
                            \Carbon\Carbon::parse($alert->kadaluarsa)->isFuture() && 
                            \Carbon\Carbon::parse($alert->kadaluarsa)->lessThanOrEqualTo(now()->addDays(30));
            @endphp
            <tr style="background: white">
                <td>
                    <div class="product-info">
                        <img src="{{ optional($alert->product)->resolved_image_url ?? asset('images/placeholder-product.png') }}" class="product-img">
                        <div>
                            <div style="font-weight: 600;">{{ optional($alert->product)->nama_produk ?? 'Produk Terhapus' }}</div>
                            <div style="font-size: 11px; color: #888;">{{ optional($alert->store)->nama ?? '-' }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <div style="font-weight: 700; color: {{ $isLowStock ? '#D9534F' : 'inherit' }}">
                        {{ $alert->stok }} Pcs
                        @if($isLowStock)
                            <div style="font-size: 10px; color: #D9534F; font-weight: 600;">(Stok Menipis)</div>
                        @endif
                    </div>
                </td>
                <td>
                    <div style="font-size: 13px; color: #666;">
                        {{ $alert->tanggal_masuk ? \Carbon\Carbon::parse($alert->tanggal_masuk)->format('d/m/Y') : '-' }}
                    </div>
                </td>
                <td>
                    @if($alert->kadaluarsa)
                        <span style="color: {{ $isExpired ? '#D9534F' : ($isNearExp ? '#FBC02D' : 'inherit') }}; font-weight: {{ $isNearExp || $isExpired ? '700' : '400' }}">
                            {{ \Carbon\Carbon::parse($alert->kadaluarsa)->format('d F Y') }}
                            @if($isExpired) 
                                (Sudah Expired) 
                            @elseif($isNearExp) 
                                (Akan Expired) 
                            @endif
                        </span>
                    @else
                        <span style="color: #999;">-</span>
                    @endif
                </td>
                <td>
                    @if($alert->status_aktif === false)
                        <span class="status-badge status-inactive" style="padding: 4px 10px; font-size: 10px;">Nonaktif</span>
                    @else
                        <span class="status-badge status-active" style="padding: 4px 10px; font-size: 10px;">Aktif</span>
                    @endif
                </td>
                <td>
                    <div style="display: flex; gap: 8px;">
                        <button type="button" class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: var(--primary-blue);" 
                            onclick="openViewModalFromAlert('{{ $alert->uuid }}')" title="Lihat Detail">
                            <iconify-icon icon="solar:eye-bold-duotone"></iconify-icon>
                        </button>
                        <button type="button" class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: var(--primary-blue);" 
                            onclick="openEditAlertModal('{{ $alert->uuid }}')" title="Update Data">
                            <iconify-icon icon="solar:pen-bold-duotone"></iconify-icon>
                        </button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 40px; color: #999;">Tidak ada produk yang perlu perhatian saat ini.</td>
            </tr>
        @endforelse
    </tbody>
</table>
<div class="pagination-container">
    {{ $alerts->links() }}
</div>
