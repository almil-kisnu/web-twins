<style>
    /* Action Buttons Premium Style (Identical to Image 2) */
    .btn-action-premium {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 2px solid transparent;
        background: white;
    }
    .btn-read-premium {
        background: #f0f9ff;
        color: #0ea5e9;
        border-color: #38bdf8; /* Brighter blue border */
    }
    .btn-read-premium:hover {
        background: #0ea5e9;
        color: white;
        border-color: #0ea5e9;
    }
    .btn-delete-premium {
        background: #fff1f2;
        color: #f43f5e;
        border-color: #fecdd3; /* Soft pinkish border */
    }
    .btn-delete-premium:hover {
        background: #f43f5e;
        color: white;
        border-color: #f43f5e;
    }
    .btn-action-premium iconify-icon {
        font-size: 14px;
    }
</style>

{{-- Sub-tab Navigation --}}
<div class="sub-tab-navigation" style="margin-bottom: 20px;">
    <a href="{{ route('products.opname', ['sub_tab' => 'semua']) }}" class="sub-tab-pill {{ ($sub_tab ?? 'semua') == 'semua' ? 'active' : '' }}" onclick="event.preventDefault(); updateTableContent(this.href)">
        <iconify-icon icon="solar:layers-bold-duotone"></iconify-icon>
        Semua Sesi
    </a>
    <a href="{{ route('products.opname', ['sub_tab' => 'produk_rugi']) }}" class="sub-tab-pill {{ ($sub_tab ?? '') == 'produk_rugi' ? 'active' : '' }}" onclick="event.preventDefault(); updateTableContent(this.href)">
        <iconify-icon icon="solar:danger-bold-duotone"></iconify-icon>
        Produk Rugi
    </a>
</div>

@if(Auth::user()->isOwner() || Auth::user()->isKepalaToko())
    <div style="display: flex; gap: 15px; margin-bottom: 20px;">
        <div style="background: white; padding: 15px 20px; border-radius: 12px; border: 1px solid #eee; display: flex; align-items: center; gap: 12px; flex: 1; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
            <div style="background: #FFEBEE; color: #C62828; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                <iconify-icon icon="solar:danger-bold-duotone"></iconify-icon>
            </div>
            <div>
                <div style="font-size: 12px; color: #888;">🔥 Menunggu Approval</div>
                <div style="font-size: 18px; font-weight: 700;">{{ $pending_count ?? 0 }}</div>
            </div>
        </div>
        <div style="background: white; padding: 15px 20px; border-radius: 12px; border: 1px solid #eee; display: flex; align-items: center; gap: 12px; flex: 1; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
            <div style="background: #FFF3E0; color: #E65100; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                <iconify-icon icon="solar:wad-of-money-bold-duotone"></iconify-icon>
            </div>
            <div>
                <div style="font-size: 12px; color: #888;">📉 Total Kerugian (Seluruh)</div>
                <div style="font-size: 18px; font-weight: 700; color: #C62828;">
                    Rp {{ number_format(abs($total_loss ?? 0), 0, ',', '.') }}
                </div>
            </div>
        </div>
        <div style="background: white; padding: 15px 20px; border-radius: 12px; border: 1px solid #eee; display: flex; align-items: center; gap: 12px; flex: 1; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
            <div style="background: #E8F5E9; color: #2E7D32; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                <iconify-icon icon="solar:check-circle-bold-duotone"></iconify-icon>
            </div>
            <div>
                <div style="font-size: 12px; color: #888;">✅ Selesai (Finalized)</div>
                <div style="font-size: 18px; font-weight: 700;">{{ $selesai_count ?? 0 }}</div>
            </div>
        </div>
    </div>
@endif

@if(($sub_tab ?? 'semua') == 'semua')
    <table class="fitur-table" id="opnameTable">
        <thead>
            <tr>
                <th>TANGGAL</th>
                <th>OUTLET</th>
                <th>PETUGAS</th>
                <th>SUMMARY</th>
                @if(Auth::user()->isOwner())
                    <th>KERUGIAN (RP)</th>
                @endif
                <th>STATUS</th>
                <th>AKSI</th>
            </tr>
        </thead>
        <tbody>
            @forelse($opnames as $opname)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($opname->tanggal)->format('d F Y') }}</td>
                    <td>{{ $opname->store->nama ?? '-' }}</td>
                    <td><strong>{{ $opname->user->name ?? $opname->user->username ?? '-' }}</strong></td>
                    <td>
                        <div style="font-weight: 600;">{{ $opname->total_items }} item</div>
                        <div style="font-size: 12px; display: flex; align-items: center; gap: 4px; color: {{ $opname->total_selisih != 0 ? '#ef4444' : '#22c55e' }}; font-weight: 600;">
                            <span>{{ $opname->total_selisih > 0 ? '+' : '' }}{{ $opname->total_selisih }}</span>
                        </div>
                    </td>
                    @if(Auth::user()->isOwner())
                        <td style="font-weight: 700; color: {{ $opname->total_kerugian < 0 ? '#C62828' : ($opname->total_kerugian > 0 ? '#2E7D32' : '#666') }}">
                            Rp {{ number_format(abs($opname->total_kerugian), 0, ',', '.') }}
                        </td>
                    @endif
                    <td>
                        <span class="status-badge stat-{{ strtolower($opname->status) }}">{{ $opname->status }}</span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 8px;">
                            <button type="button" class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: var(--primary-blue);" onclick="openOpnameDetailModal('{{ $opname->uuid }}')" title="Detail">
                                <iconify-icon icon="solar:eye-bold-duotone"></iconify-icon>
                            </button>
                            @if($opname->status == 'Pending')
                                <button type="button" class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #16a34a; border-color: #dcfce7; background: #f0fdf4;" onclick="openEditOpnameModal('{{ $opname->uuid }}')" title="Edit">
                                    <iconify-icon icon="solar:pen-bold-duotone"></iconify-icon>
                                </button>
                            @endif
                            <button type="button" class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #ef4444; border-color: #fee2e2; background: #fff5f5;" onclick="confirmDeleteOpname('{{ $opname->uuid }}', '{{ $opname->tanggal }}')" title="Hapus">
                                <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align: center; padding: 40px; color: #999;">Belum ada riwayat opname.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="pagination-container">
        {{ $opnames->links() }}
    </div>
@else
    <table class="fitur-table" id="rugiTable">
        <thead>
            <tr>
                <th>PRODUK</th>
                <th>OUTLET</th>
                <th>TANGGAL</th>
                <th style="text-align: center;">SISTEM</th>
                <th style="text-align: center;">FISIK</th>
                <th style="text-align: center;">SELISIH</th>
                <th style="text-align: right;">KERUGIAN (RP)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($opname_details as $detail)
                <tr>
                    <td>
                        <div style="font-weight: 600;">{{ $detail->product->nama_produk ?? '-' }}</div>
                    </td>
                    <td>{{ $detail->opname->store->nama ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($detail->opname->tanggal)->format('d/m/Y') }}</td>
                    <td style="text-align: center;">{{ (float)$detail->stok_sistem }}</td>
                    <td style="text-align: center;">{{ (float)$detail->stok_fisik }}</td>
                    <td style="text-align: center; color: #C62828;">{{ (float)$detail->selisih }}</td>
                    <td style="text-align: right; color: #C62828;">Rp {{ number_format(abs($detail->selisih * ($detail->product->harga_modal ?? 0)), 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align: center; padding: 40px; color: #999;">Tidak ada produk rugi.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="pagination-container">
        {{ $opname_details->links() }}
    </div>
@endif
