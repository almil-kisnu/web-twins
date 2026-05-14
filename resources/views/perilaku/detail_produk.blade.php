@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/fitur.css') }}">
    <link rel="stylesheet" href="{{ asset('css/perilaku.css') }}">
@endpush

@section('content')
    <div class="fitur-container">
        {{-- BACK BUTTON --}}
        <div class="detail-header">
            <a href="{{ route('perilaku.index', ['active_tab' => 'produk', 'store_id' => $store_id, 'year' => $year]) }}"
                class="btn-back">
                <iconify-icon icon="solar:arrow-left-bold-duotone"></iconify-icon>
                <span>Kembali</span>
            </a>
            <h2 class="detail-title" id="detail-product-name">Detail Produk</h2>
        </div>

        {{-- Sub tab: Grafik / Riwayat --}}
        <div class="sub-tab-navigation" style="margin-bottom: 20px;">
            <button class="sub-tab-pill active" id="subtab-chart" onclick="switchSubTab('chart')">
                <iconify-icon icon="solar:chart-2-bold-duotone"></iconify-icon> Grafik
            </button>
            <button class="sub-tab-pill" id="subtab-history" onclick="switchSubTab('history')">
                <iconify-icon icon="solar:clipboard-list-bold-duotone"></iconify-icon> Riwayat
            </button>
        </div>

        {{-- CHART VIEW --}}
        <div id="view-chart">
            <div class="detail-chart-container">
                <div class="chart-header">
                    <h3>Grafik Harian</h3>
                    <span class="chart-period" id="chart-period-label">-</span>
                </div>
                <div id="productChart" style="width: 100%; min-height: 350px;"></div>
            </div>

            <div class="detail-table-container" style="margin-top: 24px;">
                <h3 style="margin-bottom: 16px; color: #334155; font-weight: 700;">Rincian Harian</h3>
                <div class="table-container">
                    <table class="fitur-table" id="daily-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Omset</th>
                                <th>Laba</th>
                                <th>Frekuensi</th>
                            </tr>
                        </thead>
                        <tbody id="daily-table-body">
                            <tr>
                                <td colspan="4" style="text-align: center; color: #999; padding: 40px;">Memuat data...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- HISTORY VIEW --}}
        <div id="view-history" style="display: none;">
            <div class="detail-table-container">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h3 style="color: #334155; font-weight: 700;">Riwayat Transaksi</h3>
                    <div class="sub-tab-navigation" style="margin-bottom: 0;">
                        <button class="sub-tab-pill active" id="hist-tab-offline" onclick="switchHistoryTab('offline')" style="font-size: 12px; padding: 6px 12px;">
                            Offline (<span id="count-offline">0</span>)
                        </button>
                        <button class="sub-tab-pill" id="hist-tab-online" onclick="switchHistoryTab('online')" style="font-size: 12px; padding: 6px 12px;">
                            Online (<span id="count-online">0</span>)
                        </button>
                    </div>
                </div>
                
                <div class="table-container">
                    <table class="fitur-table" id="history-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Jumlah</th>
                                <th>Harga Jual</th>
                                <th>Total Transaksi</th>
                            </tr>
                        </thead>
                        <tbody id="history-table-body">
                            <tr>
                                <td colspan="4" style="text-align: center; color: #999; padding: 40px;">Memuat
                                    riwayat...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        let storeId = '{{ $store_id }}';
        let productId = '{{ $product_id }}';
        let year = {{ $year }};
        let month = {{ request('month', date('m')) }};
        let chart = null;
        let currentSubTab = 'chart';
        let currentHistoryTab = 'offline';
        let historyDataOffline = [];
        let historyDataOnline = [];

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

        function switchSubTab(tab) {
            currentSubTab = tab;
            document.querySelectorAll('.sub-tab-pill').forEach(p => p.classList.remove('active'));
            document.getElementById('subtab-' + tab).classList.add('active');
            document.getElementById('view-chart').style.display = (tab === 'chart') ? 'block' : 'none';
            document.getElementById('view-history').style.display = (tab === 'history') ? 'block' : 'none';
        }

        function switchHistoryTab(tab) {
            currentHistoryTab = tab;
            document.getElementById('hist-tab-offline').classList.remove('active');
            document.getElementById('hist-tab-online').classList.remove('active');
            document.getElementById('hist-tab-' + tab).classList.add('active');
            renderHistoryTable();
        }

        function loadAllData() {
            loadDailyData();
            loadHistoryData();
        }

        async function loadDailyData() {
            document.getElementById('chart-period-label').textContent =
                `${monthNames[month - 1]} ${year}`;

            const tbody = document.getElementById('daily-table-body');
            tbody.innerHTML =
                '<tr><td colspan="4" style="text-align: center; color: #999; padding: 40px;">Memuat data...</td></tr>';

            try {
                const res = await fetch(
                    `/perilaku/api/product/daily?store_id=${storeId}&product_id=${productId}&year=${year}&month=${month}`
                );
                if (!res.ok) throw new Error('Gagal memuat data');
                const data = await res.json();
                const daily = data.daily || [];

                if (daily.length === 0) {
                    tbody.innerHTML =
                        '<tr><td colspan="4" style="text-align: center; color: #999; padding: 40px;">Tidak ada data</td></tr>';
                    if (chart) chart.updateSeries([{
                        data: []
                    }, {
                        data: []
                    }, {
                        data: []
                    }]);
                    return;
                }

                // Render table
                tbody.innerHTML = daily.map(d => `
                    <tr>
                        <td>${new Date(d.tanggal).toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short' })}</td>
                        <td style="font-weight: 600; color: #1e40af;">${formatCurrency(d.total_omset)}</td>
                        <td style="color: #059669; font-weight: 600;">${formatCurrency(d.total_laba)}</td>
                        <td>${d.frekuensi} pcs</td>
                    </tr>
                `).join('');

                // Update chart
                const categories = daily.map(d => d.tanggal);
                const omsetData = daily.map(d => d.total_omset);
                const labaData = daily.map(d => d.total_laba);
                const freqData = daily.map(d => d.frekuensi);

                if (chart) {
                    chart.updateOptions({
                        xaxis: {
                            categories
                        }
                    });
                    chart.updateSeries([{
                        name: 'Omset',
                        data: omsetData
                    }, {
                        name: 'Laba',
                        data: labaData
                    }, {
                        name: 'Frekuensi',
                        data: freqData
                    }]);
                }
            } catch (err) {
                console.error(err);
                tbody.innerHTML =
                    '<tr><td colspan="4" style="text-align: center; color: #ef4444; padding: 40px;">Gagal memuat data</td></tr>';
            }
        }

        async function loadHistoryData() {
            const tbody = document.getElementById('history-table-body');
            tbody.innerHTML =
                '<tr><td colspan="4" style="text-align: center; color: #999; padding: 40px;">Memuat riwayat...</td></tr>';

            try {
                const res = await fetch(
                    `/perilaku/api/product/history?store_id=${storeId}&product_id=${productId}&year=${year}&month=${month}`
                );
                if (!res.ok) throw new Error('Gagal memuat data');
                const data = await res.json();
                const history = data.history || [];

                historyDataOffline = history.filter(h => h.jenis_kanal === 'Offline');
                historyDataOnline = history.filter(h => h.jenis_kanal === 'Online');

                document.getElementById('count-offline').textContent = historyDataOffline.length;
                document.getElementById('count-online').textContent = historyDataOnline.length;

                renderHistoryTable();
            } catch (err) {
                console.error(err);
                tbody.innerHTML =
                    '<tr><td colspan="4" style="text-align: center; color: #ef4444; padding: 40px;">Gagal memuat data</td></tr>';
            }
        }

        function renderHistoryTable() {
            const tbody = document.getElementById('history-table-body');
            const data = currentHistoryTab === 'offline' ? historyDataOffline : historyDataOnline;

            if (data.length === 0) {
                tbody.innerHTML =
                    '<tr><td colspan="4" style="text-align: center; color: #999; padding: 40px;">Tidak ada riwayat transaksi</td></tr>';
                return;
            }

            tbody.innerHTML = data.map(h => {
                const tanggal = new Date(h.tanggal).toLocaleDateString('id-ID', {
                    weekday: 'short',
                    day: 'numeric',
                    month: 'short',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                return `
                    <tr>
                        <td>${tanggal}</td>
                        <td>${h.jmlh} pcs</td>
                        <td>${formatCurrency(h.harga_jual)}</td>
                        <td style="font-weight: 700;">${formatCurrency(h.total_transaksi)}</td>
                    </tr>
                `;
            }).join('');
        }

        // Init chart
        document.addEventListener('DOMContentLoaded', () => {
            const options = {
                series: [{
                    name: 'Omset',
                    type: 'area',
                    data: []
                }, {
                    name: 'Laba',
                    type: 'area',
                    data: []
                }, {
                    name: 'Frekuensi',
                    type: 'line',
                    data: []
                }],
                chart: {
                    height: 350,
                    type: 'line',
                    toolbar: {
                        show: false
                    },
                    fontFamily: 'Inter, sans-serif',
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 600
                    }
                },
                colors: ['#3b82f6', '#10b981', '#f59e0b'],
                stroke: {
                    curve: 'smooth',
                    width: [0, 0, 3]
                },
                fill: {
                    type: ['gradient', 'gradient', 'solid'],
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.45,
                        opacityTo: 0.05
                    }
                },
                dataLabels: {
                    enabled: false
                },
                xaxis: {
                    categories: [],
                    labels: {
                        formatter: (val) => {
                            if (!val) return '';
                            const d = new Date(val);
                            return d.getDate() + '/' + (d.getMonth() + 1);
                        }
                    }
                },
                yaxis: [{
                    title: {
                        text: 'Omset (Rp)',
                        style: {
                            color: '#3b82f6'
                        }
                    },
                    labels: {
                        formatter: (val) => formatCurrency(val)
                    }
                }, {
                    show: false,
                    labels: {
                        formatter: (val) => formatCurrency(val)
                    }
                }, {
                    opposite: true,
                    title: {
                        text: 'Frekuensi',
                        style: {
                            color: '#f59e0b'
                        }
                    }
                }],
                tooltip: {
                    shared: true,
                    y: {
                        formatter: function(val, {
                            seriesIndex
                        }) {
                            if (seriesIndex <= 1) return formatCurrency(val);
                            return val + ' pcs';
                        }
                    }
                },
                grid: {
                    borderColor: '#f1f5f9'
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    offsetY: -10
                }
            };

            chart = new ApexCharts(document.querySelector('#productChart'), options);
            chart.render();
            loadAllData();
        });
    </script>
@endsection
