@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/fitur.css') }}">
    <link rel="stylesheet" href="{{ asset('css/perilaku.css') }}">
@endpush

@section('content')
    <div class="fitur-container">
        {{-- BACK BUTTON --}}
        <div class="detail-header">
            <a href="{{ route('perilaku.index', ['active_tab' => 'customer', 'store_id' => $store_id, 'year' => $year]) }}"
                class="btn-back">
                <iconify-icon icon="solar:arrow-left-bold-duotone"></iconify-icon>
                <span>Kembali</span>
            </a>
            <h2 class="detail-title" id="detail-customer-name">Detail Customer</h2>
        </div>

        {{-- SUB TABS --}}
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
            <div id="customerChart" style="width: 100%; min-height: 350px;"></div>
        </div>

        {{-- SUMMARY TABLE --}}
        <div class="detail-table-container" style="margin-top: 24px;">
            <h3 style="margin-bottom: 16px; color: #334155; font-weight: 700;">Rincian Harian</h3>
            <div class="table-container">
                <table class="fitur-table" id="daily-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Total Omset</th>
                            <th>Frekuensi</th>
                        </tr>
                    </thead>
                    <tbody id="daily-table-body">
                        <tr>
                            <td colspan="3" style="text-align: center; color: #999; padding: 40px;">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        </div>

        {{-- HISTORY VIEW --}}
        <div id="view-history" style="display: none;">
            <div class="detail-table-container">
                <h3 style="margin-bottom: 16px; color: #334155; font-weight: 700;">Riwayat Transaksi</h3>
                <div class="table-container">
                    <table class="fitur-table" id="history-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Kanal</th>
                                <th>Total Transaksi</th>
                            </tr>
                        </thead>
                        <tbody id="history-table-body">
                            <tr>
                                <td colspan="3" style="text-align: center; color: #999; padding: 40px;">Memuat
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
        let contactId = '{{ $contact_id }}';
        let year = {{ $year }};
        let month = {{ request('month', date('m')) }};
        let chart = null;
        let currentSubTab = 'chart';

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

        function loadAllData() {
            loadDailyData();
            loadHistoryData();
        }

        async function loadDailyData() {
            document.getElementById('chart-period-label').textContent =
                `${monthNames[month - 1]} ${year}`;

            const tbody = document.getElementById('daily-table-body');
            tbody.innerHTML =
                '<tr><td colspan="3" style="text-align: center; color: #999; padding: 40px;">Memuat data...</td></tr>';

            try {
                const res = await fetch(
                    `/perilaku/api/customer/daily?store_id=${storeId}&contact_id=${contactId}&year=${year}&month=${month}`
                );
                if (!res.ok) throw new Error('Gagal memuat data');
                const data = await res.json();
                const daily = data.daily || [];

                if (daily.length === 0) {
                    tbody.innerHTML =
                        '<tr><td colspan="3" style="text-align: center; color: #999; padding: 40px;">Tidak ada data transaksi</td></tr>';
                    if (chart) chart.updateSeries([{
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
                        <td style="font-weight: 600; color: #1e40af;">${formatCurrency(d.total)}</td>
                        <td>${d.frekuensi} trx</td>
                    </tr>
                `).join('');

                // Update chart
                const categories = daily.map(d => d.tanggal);
                const omsetData = daily.map(d => d.total);
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
                        name: 'Frekuensi',
                        data: freqData
                    }]);
                }
            } catch (err) {
                console.error(err);
                tbody.innerHTML =
                    '<tr><td colspan="3" style="text-align: center; color: #ef4444; padding: 40px;">Gagal memuat data</td></tr>';
            }
        }

        async function loadHistoryData() {
            const tbody = document.getElementById('history-table-body');
            tbody.innerHTML =
                '<tr><td colspan="3" style="text-align: center; color: #999; padding: 40px;">Memuat riwayat...</td></tr>';

            try {
                const res = await fetch(
                    `/perilaku/api/customer/history?store_id=${storeId}&contact_id=${contactId}&year=${year}&month=${month}`
                );
                if (!res.ok) throw new Error('Gagal memuat data');
                const data = await res.json();
                const history = data.history || [];

                if (history.length === 0) {
                    tbody.innerHTML =
                        '<tr><td colspan="3" style="text-align: center; color: #999; padding: 40px;">Tidak ada riwayat transaksi</td></tr>';
                    return;
                }

                tbody.innerHTML = history.map(h => {
                    const tanggal = new Date(h.tanggal).toLocaleDateString('id-ID', {
                        weekday: 'short',
                        day: 'numeric',
                        month: 'short',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    const kanalClass = h.jenis_kanal === 'Online' ? 'kanal-online' : 'kanal-offline';
                    return `
                        <tr>
                            <td>${tanggal}</td>
                            <td><span class="kanal-badge ${kanalClass}">${h.jenis_kanal}</span></td>
                            <td style="font-weight: 700;">${formatCurrency(h.total)}</td>
                        </tr>
                    `;
                }).join('');
            } catch (err) {
                console.error(err);
                tbody.innerHTML =
                    '<tr><td colspan="3" style="text-align: center; color: #ef4444; padding: 40px;">Gagal memuat data</td></tr>';
            }
        }

        // Init chart
        document.addEventListener('DOMContentLoaded', () => {
            const options = {
                series: [{
                    name: 'Omset',
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
                colors: ['#3b82f6', '#f59e0b'],
                stroke: {
                    curve: 'smooth',
                    width: [0, 3]
                },
                fill: {
                    type: ['gradient', 'solid'],
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.5,
                        opacityTo: 0.1
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
                            if (seriesIndex === 0) return formatCurrency(val);
                            return val + ' trx';
                        }
                    }
                },
                grid: {
                    borderColor: '#f1f5f9'
                }
            };

            chart = new ApexCharts(document.querySelector('#customerChart'), options);
            chart.render();
            loadAllData();
        });
    </script>
@endsection
