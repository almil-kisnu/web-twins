<table class="fitur-table" id="produkTable">
    <thead>
        <tr>
            <th class="mass-delete-checkbox" style="display: none; width: 40px; text-align: center;">
                <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll()" style="transform: scale(1.2); cursor: pointer;">
            </th>
            <th>PRODUK</th>
            <th>KATEGORI</th>
            <th style="white-space: nowrap;">HARGA MODAL</th>
            <th style="white-space: nowrap;">HARGA JUAL</th>
            <th>STOK</th>
            <th style="white-space: nowrap; min-width: 130px;">AKSI</th>
        </tr>
    </thead>
    <tbody>
        @forelse($products as $product)
            <tr>
                <td class="mass-delete-checkbox" style="display: none; text-align: center;">
                    <input type="checkbox" class="product-checkbox" value="{{ $product->uuid }}" data-nama="{{ $product->nama_produk }}" onchange="updateMassDeleteCount()" style="transform: scale(1.2); cursor: pointer;">
                </td>
                <td>
                    <div class="product-info">
                        <img src="{{ $product->resolved_image_url }}?t={{ time() }}" class="product-img">
                        <div>
                            <div style="font-weight: 600;">{{ $product->nama_produk }}</div>
                            <div style="font-size: 12px; color: #888;">{{ $product->barcode ?? '-' }}</div>
                        </div>
                    </div>
                </td>
                <td>{{ $product->category->nama_category ?? '-' }}</td>
                <td class="price-text" style="white-space: nowrap;">Rp {{ number_format($product->harga_modal, 0, ',', '.') }}</td>
                <td class="price-text" style="white-space: nowrap;">Rp {{ number_format($product->harga_jual, 0, ',', '.') }}</td>
                <td style="min-width: 120px;">
                    @if(Auth::user()->isOwner())
                        <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                            @foreach($product->stores as $ps)
                                <div style="display: flex; align-items: center; gap: 5px; background: {{ $ps->stok > 0 ? '#eff6ff' : '#f1f5f9' }}; padding: 4px 8px; border-radius: 6px; border: 1px solid {{ $ps->stok > 0 ? '#dbeafe' : '#e2e8f0' }};" title="{{ $ps->store->nama ?? '-' }}">
                                    <span style="font-size: 10px; font-weight: 600; color: {{ $ps->stok > 0 ? 'var(--primary-blue)' : '#64748b' }};">
                                        @php
                                            $storeName = $ps->store->nama ?? '-';
                                            if (preg_match('/\((.*?)\)/', $storeName, $match)) {
                                                $displayName = $match[1];
                                            } else {
                                                $displayName = Str::limit($storeName, 10);
                                            }
                                        @endphp
                                        {{ $displayName }}:
                                    </span>
                                    <span style="font-size: 11px; font-weight: 800; color: {{ $ps->stok > 0 ? 'var(--primary-blue)' : '#64748b' }};">
                                        {{ $ps->stok }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div style="font-weight: 800; color: var(--primary-blue); font-size: 16px;">
                            {{ $product->current_stok }}
                        </div>
                        <div style="font-size: 10px; color: #64748b; font-weight: 500;">Exp: {{ $product->current_kadaluarsa }}</div>
                        <div style="font-size: 10px; color: #94a3b8;">Di {{ Auth::user()->store->nama ?? 'Cabang' }}</div>
                    @endif
                </td>
                <td style="white-space: nowrap;">
                    <div style="display: flex; gap: 8px;">
                        <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: var(--primary-blue);" onclick="openViewModal('{{ $product->uuid }}')" title="Lihat">
                            <iconify-icon icon="solar:eye-bold-duotone"></iconify-icon>
                        </button>
                        <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: var(--primary-blue);" onclick="openEditModal('{{ $product->uuid }}')" title="Edit">
                            <iconify-icon icon="solar:pen-bold-duotone"></iconify-icon>
                        </button>
                        <button class="btn-filter" style="width: 32px; height: 32px; border-radius: 8px; color: #D9534F; border-color: #ffcccc;" onclick="confirmDelete('{{ $product->uuid }}', '{{ $product->nama_produk }}')">
                            <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                        </button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" id="emptyProdukTd" style="text-align: center; padding: 40px; color: #999;">Belum ada data produk.</td>
            </tr>
        @endforelse
    </tbody>
</table>

{{-- Data transfer for JS Maps (Syncing during AJAX) --}}
<div id="js-data-transfer" style="display: none;" 
     data-products="{{ json_encode($all_products ?? []) }}"
     data-alerts="{{ json_encode($alerts ?? []) }}">
</div>

@if(isset($products) && $products instanceof \Illuminate\Pagination\LengthAwarePaginator)
    <div class="pagination-container">
        {{ $products->appends(request()->query())->links() }}
    </div>
@endif
