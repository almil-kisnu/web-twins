@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/fitur.css') }}">
    <link rel="stylesheet" href="{{ asset('css/perilaku.css') }}">
@endpush

@section('content')
    <div class="fitur-container" id="perilaku-app">
        {{-- PILL TABS --}}
        <div class="tab-navigation">
            <a href="#" class="tab-pill" onclick="switchTab('customer')" id="pill-customer">
                <iconify-icon icon="solar:users-group-rounded-bold-duotone"></iconify-icon>
                <span>Perilaku Customer</span>
            </a>
            <a href="#" class="tab-pill" onclick="switchTab('produk')" id="pill-produk">
                <iconify-icon icon="solar:box-minimalistic-bold-duotone"></iconify-icon>
                <span>Perilaku Produk</span>
            </a>
        </div>

        {{-- ACTION BAR --}}
        <div class="action-bar">
            <div style="display: contents;">
                <div class="left-actions-group">
                    {{-- Search --}}
                    <div class="search-wrapper" id="searchWrapper">
                        <iconify-icon icon="solar:magnifer-linear" class="search-icon"></iconify-icon>
                        <input type="text" id="globalSearch" class="search-input"
                            placeholder="Cari..." onkeyup="handleSearch()">
                    </div>

                    {{-- Filter Outlet --}}
                    <div class="dropdown">
                        <button type="button" class="btn-filter" title="Filter Toko" onclick="toggleDropdown(event)">
                            <iconify-icon icon="solar:shop-bold-duotone" style="font-size: 24px;"
                                class="{{ $store_id ? 'text-primary-blue' : '' }}"></iconify-icon>
                        </button>
                        <div class="dropdown-content">
                            @foreach ($outlets as $o)
                                <a href="javascript:void(0)" onclick="selectStore('{{ $o->uuid }}')"
                                    class="{{ $store_id == $o->uuid ? 'active-dropdown-item' : '' }}">{{ $o->nama }}</a>
                            @endforeach
                        </div>
                    </div>

                    {{-- Filter Year --}}
                    <div class="dropdown">
                        <button type="button" class="btn-filter" title="Filter Tahun" onclick="toggleDropdown(event)">
                            <iconify-icon icon="solar:calendar-bold-duotone" style="font-size: 24px;"></iconify-icon>
                        </button>
                        <div class="dropdown-content" style="padding: 15px; width: 200px;">
                            <label style="font-size: 11px; color: #888; display: block; margin-bottom: 4px;">Tahun</label>
                            <input id="year-selector" type="number" class="form-control" min="2020" max="2100"
                                value="{{ $year }}">
                            <div id="month-filter-group" style="margin-top: 10px; display: none;">
                                <label style="font-size: 11px; color: #888; display: block; margin-bottom: 4px;">Bulan
                                    (Opsional)</label>
                                <select id="month-selector" class="form-control">
                                    <option value="">-- Semua Bulan --</option>
                                    <option value="1">Januari</option>
                                    <option value="2">Februari</option>
                                    <option value="3">Maret</option>
                                    <option value="4">April</option>
                                    <option value="5">Mei</option>
                                    <option value="6">Juni</option>
                                    <option value="7">Juli</option>
                                    <option value="8">Agustus</option>
                                    <option value="9">September</option>
                                    <option value="10">Oktober</option>
                                    <option value="11">November</option>
                                    <option value="12">Desember</option>
                                </select>
                            </div>
                            <button type="button" class="btn-action"
                                style="width: 100%; justify-content: center; margin-top: 12px;"
                                onclick="applyFilter()">Terapkan</button>
                        </div>
                    </div>

                    {{-- Sort (visible for produk tab) --}}
                    <div class="dropdown" id="sortDropdown" style="display: none;">
                        <button type="button" class="btn-filter" title="Urutkan" onclick="toggleDropdown(event)">
                            <iconify-icon icon="solar:sort-from-top-to-bottom-bold-duotone"
                                style="font-size: 24px;"></iconify-icon>
                        </button>
                        <div class="dropdown-content">
                            <a href="javascript:void(0)" onclick="setSort('omset')" id="sort-omset"
                                class="active-dropdown-item">Omset Tertinggi</a>
                            <a href="javascript:void(0)" onclick="setSort('frekuensi')" id="sort-frekuensi">Frekuensi
                                Tertinggi</a>
                            <a href="javascript:void(0)" onclick="setSort('laba')" id="sort-laba">Laba Tertinggi</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- MAIN BOX --}}
        <div class="main-content-box">
            @include('perilaku._tab_customer')
            @include('perilaku._tab_produk')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        // ═══════════════════════════════════════
        //  State Variables
        // ═══════════════════════════════════════
        let currentTab = '{{ $active_tab ?? 'customer' }}';
        let currentStoreId = '{{ $store_id ?? '' }}';
        let currentYear = {{ $year ?? date('Y') }};
        let currentMonth = '';
        let currentSort = 'omset';
        let currentKanal = 'semua';
        let searchTimeout = null;

        const monthNames = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        function formatCurrency(value) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(value || 0);
        }

        // ═══════════════════════════════════════
        //  Tab Switching
        // ═══════════════════════════════════════
        function switchTab(tab) {
            currentTab = tab;
            document.querySelectorAll('.tab-pill').forEach(p => p.classList.remove('active'));
            document.getElementById('pill-' + tab).classList.add('active');

            document.getElementById('view-customer').style.display = (tab === 'customer') ? 'block' : 'none';
            document.getElementById('view-produk').style.display = (tab === 'produk') ? 'block' : 'none';

            // Toggle UI elements
            const searchInput = document.getElementById('globalSearch');
            if (tab === 'customer') {
                searchInput.placeholder = 'Cari nama customer...';
            } else {
                searchInput.placeholder = 'Cari nama produk / barcode...';
            }
            
            document.getElementById('sortDropdown').style.display = (tab === 'produk') ? 'inline-block' : 'none';
            document.getElementById('month-filter-group').style.display = (tab === 'produk') ? 'block' : 'none';

            loadData();
        }

        // ═══════════════════════════════════════
        //  Filters
        // ═══════════════════════════════════════
        function selectStore(storeId) {
            currentStoreId = storeId;
            document.querySelectorAll('.dropdown-content').forEach(d => d.classList.remove('show'));
            loadData();
        }

        function applyFilter() {
            currentYear = parseInt(document.getElementById('year-selector').value) || {{ date('Y') }};
            const monthSelect = document.getElementById('month-selector');
            currentMonth = monthSelect ? monthSelect.value : '';
            document.querySelectorAll('.dropdown-content').forEach(d => d.classList.remove('show'));
            loadData();
        }

        function setSort(sort) {
            currentSort = sort;
            document.querySelectorAll('#sortDropdown .dropdown-content a').forEach(a => a.classList.remove(
                'active-dropdown-item'));
            document.getElementById('sort-' + sort).classList.add('active-dropdown-item');
            document.querySelectorAll('.dropdown-content').forEach(d => d.classList.remove('show'));
            loadData();
        }

        function switchKanal(kanal) {
            currentKanal = kanal;
            document.getElementById('kanal-semua').classList.remove('active');
            document.getElementById('kanal-offline').classList.remove('active');
            document.getElementById('kanal-online').classList.remove('active');
            document.getElementById('kanal-' + kanal).classList.add('active');
            loadData();
        }

        function handleSearch() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => loadData(), 400);
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

        // ═══════════════════════════════════════
        //  Data Loading
        // ═══════════════════════════════════════
        function loadData() {
            if (currentTab === 'customer') {
                loadCustomerData();
            } else {
                loadProductData();
            }
        }

        // ─── CUSTOMER ───
        async function loadCustomerData() {
            const container = document.getElementById('customer-list');
            const summaryOmset = document.getElementById('cust-total-omset');
            const summaryCount = document.getElementById('cust-total-count');

            container.innerHTML = renderSkeleton(5);

            const search = document.getElementById('globalSearch')?.value || '';

            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 15000); // 15s timeout

            try {
                const res = await fetch(
                    `/perilaku/api/customer/yearly?store_id=${currentStoreId}&year=${currentYear}&kanal=${currentKanal}&search=${encodeURIComponent(search)}`,
                    { signal: controller.signal }
                );
                clearTimeout(timeout);

                const data = await res.json();

                if (!res.ok) {
                    const msg = data.error || 'Terjadi kesalahan saat memuat data';
                    container.innerHTML = renderError(msg);
                    return;
                }

                summaryOmset.textContent = formatCurrency(data.total_omset);
                summaryCount.textContent = data.total_customers + ' Customer';

                if (!data.customers || data.customers.length === 0) {
                    container.innerHTML = renderEmpty('Belum ada data customer');
                    return;
                }

                container.innerHTML = data.customers.map((c, i) => renderCustomerCard(c, i + 1)).join('');
            } catch (err) {
                clearTimeout(timeout);
                console.error(err);
                const isTimeout = err.name === 'AbortError';
                container.innerHTML = renderError(
                    isTimeout ? 'Koneksi terlalu lama. Periksa jaringan atau coba lagi.' : 'Terjadi kesalahan saat memuat data'
                );
            }
        }

        function renderCustomerCard(customer, rank) {
            const monthsHtml = customer.months.map(m => `
                <div class="perilaku-month-row">
                    <span class="month-label">${monthNames[m.bulan - 1] || 'Bulan ' + m.bulan}</span>
                    <span class="month-value">${formatCurrency(m.total_omset)}</span>
                    <a href="/perilaku/customer/${customer.contact_id}?store_id=${currentStoreId}&year=${currentYear}&month=${m.bulan}"
                       class="btn-chart-link" title="Lihat Grafik Harian">
                        <iconify-icon icon="solar:chart-2-bold-duotone"></iconify-icon>
                    </a>
                </div>
            `).join('');

            const rankClass = rank <= 3 ? `rank-top rank-${rank}` : '';

            return `
            <div class="perilaku-card" onclick="toggleAccordion(this)">
                <div class="perilaku-card-header">
                    <div class="perilaku-rank ${rankClass}">#${rank}</div>
                    <div class="perilaku-info">
                        <div class="perilaku-name">${customer.nama_customer}</div>
                        <div class="perilaku-subtitle">${customer.months.length} bulan aktif</div>
                    </div>
                    <div class="perilaku-value">
                        <div class="perilaku-omset">${formatCurrency(customer.total_omset)}</div>
                        <iconify-icon icon="solar:alt-arrow-down-bold-duotone" class="accordion-icon"></iconify-icon>
                    </div>
                </div>
                <div class="perilaku-card-detail" style="display: none;">
                    ${monthsHtml}
                </div>
            </div>`;
        }

        // ─── PRODUCT ───
        async function loadProductData() {
            const container = document.getElementById('product-list');
            const summaryOmset = document.getElementById('prod-total-omset');
            const summaryLaba = document.getElementById('prod-total-laba');
            const summaryFreq = document.getElementById('prod-total-freq');

            container.innerHTML = renderSkeleton(5);

            const search = document.getElementById('globalSearch')?.value || '';

            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 15000); // 15s timeout

            try {
                let url =
                    `/perilaku/api/product/yearly?store_id=${currentStoreId}&year=${currentYear}&sort=${currentSort}&search=${encodeURIComponent(search)}`;
                if (currentMonth) url += `&month=${currentMonth}`;

                const res = await fetch(url, { signal: controller.signal });
                clearTimeout(timeout);

                const data = await res.json();

                if (!res.ok) {
                    const msg = data.error || 'Terjadi kesalahan saat memuat data';
                    container.innerHTML = renderError(msg);
                    return;
                }

                summaryOmset.textContent = formatCurrency(data.total_omset);
                summaryLaba.textContent = formatCurrency(data.total_laba);
                summaryFreq.textContent = (data.total_freq || 0) + ' item';

                if (!data.products || data.products.length === 0) {
                    container.innerHTML = renderEmpty('Belum ada data produk');
                    return;
                }

                if (data.mode === 'monthly') {
                    container.innerHTML = data.products.map((p, i) => renderProductCardMonthly(p, i + 1)).join('');
                } else {
                    container.innerHTML = data.products.map((p, i) => renderProductCard(p, i + 1)).join('');
                }
            } catch (err) {
                clearTimeout(timeout);
                console.error(err);
                const isTimeout = err.name === 'AbortError';
                container.innerHTML = renderError(
                    isTimeout ? 'Koneksi terlalu lama. Periksa jaringan atau coba lagi.' : 'Terjadi kesalahan saat memuat data'
                );
            }
        }

        function renderProductCard(product, rank) {
            const monthsHtml = product.months.map(m => `
                <div class="perilaku-month-row">
                    <span class="month-label">${monthNames[m.bulan - 1] || 'Bulan ' + m.bulan}</span>
                    <div class="month-metrics">
                        <span class="metric-pill omset">${formatCurrency(m.total_omset)}</span>
                        <span class="metric-pill laba">${formatCurrency(m.total_laba)}</span>
                        <span class="metric-pill freq">${m.frekuensi} pcs</span>
                    </div>
                    <a href="/perilaku/produk/${product.product_id}?store_id=${currentStoreId}&year=${currentYear}&month=${m.bulan}"
                       class="btn-chart-link" title="Lihat Detail">
                        <iconify-icon icon="solar:chart-2-bold-duotone"></iconify-icon>
                    </a>
                </div>
            `).join('');

            const rankClass = rank <= 3 ? `rank-top rank-${rank}` : '';

            return `
            <div class="perilaku-card" onclick="toggleAccordion(this)">
                <div class="perilaku-card-header">
                    <div class="perilaku-rank ${rankClass}">#${rank}</div>
                    <div class="perilaku-info">
                        <div class="perilaku-name">${product.nama_produk}</div>
                        <div class="perilaku-subtitle">${product.barcode || '-'} • ${product.frekuensi} terjual</div>
                    </div>
                    <div class="perilaku-value">
                        <div class="perilaku-omset">${formatCurrency(product.total_omset)}</div>
                        <div class="perilaku-laba">${formatCurrency(product.total_laba)}</div>
                        <iconify-icon icon="solar:alt-arrow-down-bold-duotone" class="accordion-icon"></iconify-icon>
                    </div>
                </div>
                <div class="perilaku-card-detail" style="display: none;">
                    ${monthsHtml}
                </div>
            </div>`;
        }

        function renderProductCardMonthly(product, rank) {
            const rankClass = rank <= 3 ? `rank-top rank-${rank}` : '';

            return `
            <div class="perilaku-card perilaku-card-flat">
                <div class="perilaku-card-header">
                    <div class="perilaku-rank ${rankClass}">#${rank}</div>
                    <div class="perilaku-info">
                        <div class="perilaku-name">${product.nama_produk}</div>
                        <div class="perilaku-subtitle">${product.barcode || '-'} • ${product.frekuensi} terjual</div>
                    </div>
                    <div class="perilaku-value">
                        <div class="perilaku-omset">${formatCurrency(product.total_omset)}</div>
                        <div class="perilaku-laba">${formatCurrency(product.total_laba)}</div>
                        <a href="/perilaku/produk/${product.product_id}?store_id=${currentStoreId}&year=${currentYear}&month=${currentMonth}"
                           class="btn-chart-link" title="Lihat Detail">
                            <iconify-icon icon="solar:eye-bold-duotone"></iconify-icon>
                        </a>
                    </div>
                </div>
            </div>`;
        }

        // ═══════════════════════════════════════
        //  Helpers
        // ═══════════════════════════════════════
        function toggleAccordion(card) {
            const detail = card.querySelector('.perilaku-card-detail');
            const icon = card.querySelector('.accordion-icon');
            if (!detail) return;
            const isOpen = detail.style.display !== 'none';
            detail.style.display = isOpen ? 'none' : 'block';
            if (icon) icon.style.transform = isOpen ? '' : 'rotate(180deg)';
        }

        function renderSkeleton(count) {
            return Array(count).fill(0).map(() => `
                <div class="perilaku-card skeleton-card">
                    <div class="skeleton-line w60"></div>
                    <div class="skeleton-line w40"></div>
                </div>
            `).join('');
        }

        function renderEmpty(msg) {
            return `<div class="perilaku-empty">
                <iconify-icon icon="solar:ghost-bold-duotone" style="font-size: 64px; color: #cbd5e1;"></iconify-icon>
                <p>${msg}</p>
            </div>`;
        }

        function renderError(msg) {
            return `<div class="perilaku-empty">
                <iconify-icon icon="solar:danger-circle-bold-duotone" style="font-size: 64px; color: #f87171;"></iconify-icon>
                <p style="color: #f87171; font-weight: 600;">${msg}</p>
                <button onclick="loadData()" class="btn-action" style="margin-top: 12px; gap: 6px;">
                    <iconify-icon icon="solar:restart-bold-duotone"></iconify-icon>
                    Coba Lagi
                </button>
            </div>`;
        }

        // ═══════════════════════════════════════
        //  Init
        // ═══════════════════════════════════════
        switchTab(currentTab);
    </script>
@endsection
