@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/fitur.css') }}">
@endpush

@section('content')
    <div x-data="{ tab: 'harian' }" class="fitur-container p-6 bg-gray-50 min-h-screen">

        <header class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-8">
            <div class="flex flex-wrap items-center gap-3">
                <nav class="flex space-x-1 bg-gray-200/50 p-1 rounded-2xl w-full md:w-fit" aria-label="Tab Laporan">
                    <button @click="tab = 'harian'; window.laporanActiveTab = 'harian'; fetchDailyData()"
                        :class="tab === 'harian' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                        class="px-8 py-3 text-sm font-semibold rounded-xl transition-all focus:outline-none focus:ring-2 focus:ring-blue-500">Harian</button>
                    <button @click="tab = 'bulanan'; window.laporanActiveTab = 'bulanan'; fetchMonthlyData()"
                        :class="tab === 'bulanan' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                        class="px-8 py-3 text-sm font-semibold rounded-xl transition-all focus:outline-none focus:ring-2 focus:ring-blue-500">Bulanan</button>
                    <button @click="tab = 'tahunan'; window.laporanActiveTab = 'tahunan'; fetchAnnualData()"
                        :class="tab === 'tahunan' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                        class="px-8 py-3 text-sm font-semibold rounded-xl transition-all focus:outline-none focus:ring-2 focus:ring-blue-500">Tahunan</button>
                </nav>

                <div class="action-bar" style="margin: 0; padding: 0; background: transparent; box-shadow: none;">
                    <div class="left-actions-group">
                        <div class="dropdown">
                            <button type="button" class="btn-filter" onclick="toggleDropdown(event)"
                                aria-label="Pilih Outlet Twins" title="Filter Toko">
                                <iconify-icon icon="solar:shop-bold-duotone" style="font-size: 20px;"></iconify-icon>
                            </button>
                            <div class="dropdown-content">
                                <a href="#" data-store-id="" onclick="selectStore(event)">Semua Outlet</a>
                                @foreach ($outlets as $outlet)
                                    <a href="#" data-store-id="{{ $outlet->uuid }}" onclick="selectStore(event)">
                                        {{ $outlet->nama }}
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        <input type="hidden" id="store-id-hidden" name="store_id" value="">

                        <div class="dropdown">
                            <button type="button" class="btn-filter" onclick="toggleDropdown(event)"
                                title="Filter Tanggal">
                                <iconify-icon icon="solar:calendar-bold-duotone" style="font-size: 20px;"></iconify-icon>
                            </button>
                            <div class="dropdown-content" style="padding: 15px; width: 320px; right: auto; left: 0;">
                                <div style="display: flex; flex-direction: column; gap: 12px;">
                                    <div>
                                        <label
                                            style="font-size: 11px; color: #888; display: block; margin-bottom: 4px;">Tanggal</label>
                                        <input id="date-selector" type="date" aria-label="Filter Tanggal"
                                            class="form-control" value="{{ date('Y-m-d') }}">
                                    </div>
                                    <div>
                                        <label
                                            style="font-size: 11px; color: #888; display: block; margin-bottom: 4px;">Bulan</label>
                                        <input id="month-selector" type="month" aria-label="Filter Bulan"
                                            class="form-control" value="{{ date('Y-m') }}">
                                    </div>
                                    <div>
                                        <label
                                            style="font-size: 11px; color: #888; display: block; margin-bottom: 4px;">Tahun</label>
                                        <input id="year-selector" type="number" aria-label="Filter Tahun" min="2020"
                                            max="2100" class="form-control" value="{{ date('Y') }}">
                                    </div>
                                    <button type="button" class="btn-action" style="width: 100%; justify-content: center;"
                                        onclick="applyCalendarFilter()">Terapkan</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="right-actions">
                <div class="dropdown">
                    <button type="button" class="btn-action" onclick="toggleDropdown(event)">
                        <iconify-icon icon="solar:document-text-bold-duotone"></iconify-icon>
                        <span>Extract</span>
                    </button>
                    <div class="dropdown-content">
                        <a href="javascript:void(0)" onclick="downloadLaporanExport('excel')">
                            <iconify-icon icon="vscode-icons:file-type-excel" style="margin-right: 8px;"></iconify-icon>
                            Excel
                        </a>
                        <a href="javascript:void(0)" onclick="downloadLaporanExport('pdf')">
                            <iconify-icon icon="vscode-icons:file-type-pdf" style="margin-right: 8px;"></iconify-icon>
                            PDF
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <section x-show="tab === 'harian'" x-transition.opacity aria-live="polite">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <article
                    class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-3xl p-8 text-white shadow-xl shadow-blue-200">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-3 bg-white/20 rounded-2xl">
                            <iconify-icon icon="solar:money-bag-bold" class="text-2xl"></iconify-icon>
                        </div>
                        <h2 class="text-blue-100 font-medium">Total Omset Hari Ini</h2>
                    </div>
                    <p class="text-4xl font-bold tracking-tight" id="omset-value" aria-label="Nominal Omset">Rp 0</p>
                    <div
                        class="mt-6 pt-6 border-t border-white/10 flex justify-between items-center text-sm text-blue-100">
                        <span>Laba Kotor</span>
                        <span class="font-bold" id="laba-kotor-value">Rp 0</span>
                    </div>
                </article>

                <div class="grid grid-cols-2 gap-4">
                    <article class="bg-emerald-500 rounded-3xl p-6 text-white shadow-lg shadow-emerald-100">
                        <p class="text-xs text-emerald-100 font-medium mb-1">Pemasukan Tunai</p>
                        <p class="text-xl font-bold" id="pemasukan-value">Rp 0</p>
                        <iconify-icon icon="solar:wad-of-money-bold" class="text-4xl mt-4 opacity-30"></iconify-icon>
                    </article>
                    <article class="bg-amber-500 rounded-3xl p-6 text-white shadow-lg shadow-amber-100">
                        <p class="text-xs text-amber-100 font-medium mb-1">Pemasukan Non-Tunai</p>
                        <p class="text-xl font-bold" id="pengeluaran-value">Rp 0</p>
                        <iconify-icon icon="solar:card-transfer-bold" class="text-4xl mt-4 opacity-30"></iconify-icon>
                    </article>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-gray-800">Distribusi Cashbox</h3>
                        <p class="text-xs text-gray-500 mt-1">Aliran uang riil per metode pembayaran</p>
                    </div>
                    <span class="px-3 py-1 bg-blue-50 text-blue-600 text-xs font-bold rounded-full">Cashbox</span>
                </div>
                <div class="p-6">
                    <div id="daily-cashbox-list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="rounded-2xl border border-dashed border-gray-200 p-4 text-sm text-gray-500">Memuat data
                            cashbox...</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-gray-800">Aktivitas per Operator</h3>
                        <p class="text-xs text-gray-500 mt-1">Uang di laci dan info stok per operator</p>
                    </div>
                    <span class="px-3 py-1 bg-blue-50 text-blue-600 text-xs font-bold rounded-full">Daftar Laci
                        Aktif</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50/50 text-gray-500 font-semibold text-xs uppercase">
                            <tr>
                                <th class="px-6 py-4">Nama Operator</th>
                                <th class="px-6 py-4">Penjualan</th>
                                <th class="px-6 py-4">Pemasukan Kas</th>
                                <th class="px-6 py-4">Pengeluaran Kas</th>
                                <th class="px-6 py-4 text-right">Net Laci</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50" id="operator-table-body">
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Memuat data
                                    operator...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-gray-800">Transaksi Online</h3>
                        <p class="text-xs text-gray-500 mt-1">Pesanan yang masuk dari kanal online</p>
                    </div>
                    <span class="px-3 py-1 bg-blue-50 text-blue-600 text-xs font-bold rounded-full">Online</span>
                </div>
                <div class="overflow-hidden">
                    <div id="daily-online-list" class="divide-y divide-gray-100">
                        <div class="px-6 py-4 text-sm text-gray-500">Memuat transaksi online...</div>
                    </div>
                </div>
            </div>
        </section>

        <section x-show="tab === 'bulanan' || tab === 'tahunan'" x-transition.opacity style="display: none;">
            <div x-show="tab === 'bulanan'" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <article
                    class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-3xl p-8 text-white shadow-xl shadow-blue-200">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-3 bg-white/20 rounded-2xl">
                            <iconify-icon icon="solar:money-bag-bold" class="text-2xl"></iconify-icon>
                        </div>
                        <h2 class="text-blue-100 font-medium">Total Omset Bulan Ini</h2>
                    </div>
                    <p class="text-4xl font-bold tracking-tight" id="monthly-omset-value"
                        aria-label="Nominal Omset Bulanan">Rp 0</p>
                    <div
                        class="mt-6 pt-6 border-t border-white/10 flex justify-between items-center text-sm text-blue-100">
                        <span>Laba Kotor</span>
                        <span class="font-bold" id="monthly-laba-kotor-value">Rp 0</span>
                    </div>
                </article>

                <div class="grid grid-cols-2 gap-4">
                    <article class="bg-emerald-500 rounded-3xl p-6 text-white shadow-lg shadow-emerald-100">
                        <p class="text-xs text-emerald-100 font-medium mb-1">Pemasukan</p>
                        <p class="text-xl font-bold" id="monthly-pemasukan-value">Rp 0</p>
                        <iconify-icon icon="solar:wad-of-money-bold" class="text-4xl mt-4 opacity-30"></iconify-icon>
                    </article>
                    <article class="bg-rose-500 rounded-3xl p-6 text-white shadow-lg shadow-rose-100">
                        <p class="text-xs text-rose-100 font-medium mb-1">HPP</p>
                        <p class="text-xl font-bold" id="monthly-hpp-value">Rp 0</p>
                        <iconify-icon icon="solar:cart-bold" class="text-4xl mt-4 opacity-30"></iconify-icon>
                    </article>
                </div>
            </div>

            <div x-show="tab === 'bulanan'"
                class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="font-bold text-gray-800">Aktivitas per Operator Bulanan</h3>
                    <span class="px-3 py-1 bg-blue-50 text-blue-600 text-xs font-bold rounded-full">Data Function
                        Supabase</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50/50 text-gray-500 font-semibold text-xs uppercase">
                            <tr>
                                <th class="px-6 py-4">Nama Operator</th>
                                <th class="px-6 py-4">Pemasukan</th>
                                <th class="px-6 py-4 text-right">Pengeluaran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50" id="monthly-operator-table-body">
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">Pilih outlet dan
                                    bulan untuk memuat data</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div x-show="tab === 'bulanan'"
                class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="font-bold text-gray-800">Rincian Harian Bulanan</h3>
                    <span
                        class="px-3 py-1 bg-blue-50 text-blue-600 text-xs font-bold rounded-full">get_monthly_transaction_daily</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-500 font-semibold text-xs uppercase">
                            <tr>
                                <th class="px-6 py-4">Tanggal</th>
                                <th class="px-6 py-4">Jenis</th>
                                <th class="px-6 py-4">Total</th>
                                <th class="px-6 py-4">Laba</th>
                                <th class="px-6 py-4 text-right">Frekuensi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50" id="monthly-daily-table-body">
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Pilih outlet dan
                                    bulan untuk memuat data</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div x-show="tab === 'tahunan'" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <article
                    class="bg-gradient-to-br from-slate-900 to-slate-700 rounded-3xl p-8 text-white shadow-xl shadow-slate-200">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-3 bg-white/10 rounded-2xl">
                            <iconify-icon icon="solar:money-bag-bold" class="text-2xl"></iconify-icon>
                        </div>
                        <h2 class="text-slate-100 font-medium">Total Omset Tahun Ini</h2>
                    </div>
                    <p class="text-4xl font-bold tracking-tight" id="annual-omset-value"
                        aria-label="Nominal Omset Tahunan">Rp 0</p>
                    <div
                        class="mt-6 pt-6 border-t border-white/10 flex justify-between items-center text-sm text-slate-100">
                        <span>Laba Kotor</span>
                        <span class="font-bold" id="annual-laba-kotor-value">Rp 0</span>
                    </div>
                </article>

                <div class="grid grid-cols-2 gap-4">
                    <article class="bg-emerald-500 rounded-3xl p-6 text-white shadow-lg shadow-emerald-100">
                        <p class="text-xs text-emerald-100 font-medium mb-1">Pemasukan</p>
                        <p class="text-xl font-bold" id="annual-pemasukan-value">Rp 0</p>
                        <iconify-icon icon="solar:wad-of-money-bold" class="text-4xl mt-4 opacity-30"></iconify-icon>
                    </article>
                    <article class="bg-rose-500 rounded-3xl p-6 text-white shadow-lg shadow-rose-100">
                        <p class="text-xs text-rose-100 font-medium mb-1">HPP</p>
                        <p class="text-xl font-bold" id="annual-hpp-value">Rp 0</p>
                        <iconify-icon icon="solar:cart-bold" class="text-4xl mt-4 opacity-30"></iconify-icon>
                    </article>
                </div>
            </div>

            <div x-show="tab === 'tahunan'"
                class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="font-bold text-gray-800">Distribusi Cashbox Tahunan</h3>
                    <span class="px-3 py-1 bg-blue-50 text-blue-600 text-xs font-bold rounded-full">annual cashbox</span>
                </div>
                <div class="p-6">
                    <div id="annual-cashbox-list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="rounded-2xl border border-dashed border-gray-200 p-4 text-sm text-gray-500">Pilih
                            outlet dan tahun untuk memuat data</div>
                    </div>
                </div>
            </div>

            <div x-show="tab === 'tahunan'"
                class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="font-bold text-gray-800">Aktivitas per Operator Tahunan</h3>
                    <span
                        class="px-3 py-1 bg-blue-50 text-blue-600 text-xs font-bold rounded-full">get_yearly_operator_summary</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50/50 text-gray-500 font-semibold text-xs uppercase">
                            <tr>
                                <th class="px-6 py-4">Nama Operator</th>
                                <th class="px-6 py-4">Pemasukan</th>
                                <th class="px-6 py-4 text-right">Pengeluaran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50" id="annual-operator-table-body">
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">Pilih outlet dan
                                    tahun untuk memuat data</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div x-show="tab === 'tahunan'"
                class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="font-bold text-gray-800">Rincian Bulanan Tahunan</h3>
                    <span
                        class="px-3 py-1 bg-blue-50 text-blue-600 text-xs font-bold rounded-full">get_yearly_transaction_monthly</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-500 font-semibold text-xs uppercase">
                            <tr>
                                <th class="px-6 py-4">Bulan</th>
                                <th class="px-6 py-4">Jenis</th>
                                <th class="px-6 py-4">Total</th>
                                <th class="px-6 py-4">Laba</th>
                                <th class="px-6 py-4 text-right">Frekuensi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50" id="annual-monthly-table-body">
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Pilih outlet dan
                                    tahun untuk memuat data</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 mb-8">
                <div class="flex justify-between items-center mb-8">
                    <h3 class="font-bold text-gray-800">Analisis Tren Penjualan Twins</h3>
                    <div class="flex gap-4 text-xs font-semibold">
                        <span class="flex items-center gap-1 text-blue-600">
                            <div class="w-2 h-2 rounded-full bg-blue-600"></div> Omset
                        </span>
                        <span class="flex items-center gap-1 text-amber-500">
                            <div class="w-2 h-2 rounded-full bg-amber-500"></div> Frekuensi
                        </span>
                    </div>
                </div>
                <div id="chartTwins" class="w-full h-80"></div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-50">
                    <h3 class="font-bold text-gray-800">Rincian Transaksi Kolektif</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-500 font-semibold text-xs uppercase">
                            <tr>
                                <th class="px-6 py-4">Periode</th>
                                <th class="px-6 py-4">Total Penjualan</th>
                                <th class="px-6 py-4">Total Laba</th>
                                <th class="px-6 py-4 text-right">Frekuensi Trx</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-medium text-gray-900">Mei 2026</td>
                                <td class="px-6 py-4 font-bold text-gray-900">Rp 0</td>
                                <td class="px-6 py-4 text-emerald-600 font-bold">Rp 0</td>
                                <td class="px-6 py-4 text-right">0 trx</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        function formatCurrency(value) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(value);
        }

        async function fetchDailyData() {
            const storeId = document.getElementById('store-id-hidden').value;
            const date = document.getElementById('date-selector').value;

            try {
                const response = await fetch(`/laporan/api/daily/summary?store_id=${storeId}&date=${date}`);
                if (!response.ok) throw new Error('Gagal fetch data');

                const data = await response.json();

                // Update Daily Summary Card
                document.getElementById('omset-value').textContent = formatCurrency(data.omset || 0);
                document.getElementById('laba-kotor-value').textContent = formatCurrency(data.laba_kotor || 0);

                // Update Mini Cards
                document.getElementById('pemasukan-value').textContent = formatCurrency(data.pemasukan || 0);
                document.getElementById('pengeluaran-value').textContent = formatCurrency(data.pengeluaran || 0);

                // Fetch dan update operator data
                fetchOperatorData(storeId, date);
                fetchDailyCashbox(storeId, date);
                fetchDailyOnline(storeId, date);
            } catch (error) {
                console.error('Error:', error);
                alert('Error mengambil data laporan');
            }
        }

        async function fetchDailyCashbox(storeId, date) {
            try {
                const response = await fetch(`/laporan/api/daily/cashbox?store_id=${storeId}&date=${date}`);
                if (!response.ok) throw new Error('Gagal fetch cashbox');

                const data = await response.json();
                const list = document.getElementById('daily-cashbox-list');
                const items = data.items || [];

                if (items.length === 0) {
                    list.innerHTML =
                        '<div class="rounded-2xl border border-dashed border-gray-200 p-4 text-sm text-gray-500">Belum ada data cashbox</div>';
                    return;
                }

                const palettes = [
                    ['from-indigo-600', 'to-violet-600'],
                    ['from-sky-600', 'to-blue-600'],
                    ['from-emerald-500', 'to-emerald-600'],
                    ['from-amber-500', 'to-orange-500'],
                ];

                list.innerHTML = items.map((item, index) => {
                    const palette = palettes[index % palettes.length];
                    return `
                        <div class="rounded-2xl p-4 text-white shadow-lg bg-gradient-to-br ${palette[0]} ${palette[1]}">
                            <div class="text-xs text-white/80 mb-2">${item.nama_metode || '-'}</div>
                            <div class="text-lg font-bold">${formatCurrency(item.total || 0)}</div>
                        </div>
                    `;
                }).join('');
            } catch (error) {
                console.error('Error cashbox:', error);
            }
        }

        async function fetchDailyOnline(storeId, date) {
            try {
                const response = await fetch(`/laporan/api/daily/online?store_id=${storeId}&date=${date}`);
                if (!response.ok) throw new Error('Gagal fetch online');

                const data = await response.json();
                const list = document.getElementById('daily-online-list');
                const orders = data.orders || [];

                if (orders.length === 0) {
                    list.innerHTML =
                        '<div class="px-6 py-4 text-sm text-gray-500">Tidak ada transaksi online hari ini</div>';
                    return;
                }

                list.innerHTML = orders.map((order) => {
                    const time = order.tanggal ? new Date(order.tanggal).toLocaleTimeString('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit'
                    }) : '-';
                    return `
                        <div class="px-6 py-4 flex items-center justify-between gap-4 hover:bg-gray-50 transition">
                            <div class="min-w-0">
                                <div class="font-semibold text-gray-900 truncate">${order.customer || 'Pelanggan'}</div>
                                <div class="text-xs text-gray-500">${order.gateway || 'Midtrans'} • ${time}</div>
                            </div>
                            <div class="font-bold text-emerald-600 whitespace-nowrap">${formatCurrency(order.total || 0)}</div>
                        </div>
                    `;
                }).join('');
            } catch (error) {
                console.error('Error online:', error);
            }
        }

        async function fetchMonthlyData() {
            const storeId = document.getElementById('store-id-hidden').value;
            const monthValue = document.getElementById('month-selector').value;

            if (!monthValue) {
                document.getElementById('monthly-omset-value').textContent = 'Rp 0';
                document.getElementById('monthly-laba-kotor-value').textContent = 'Rp 0';
                document.getElementById('monthly-pemasukan-value').textContent = 'Rp 0';
                document.getElementById('monthly-hpp-value').textContent = 'Rp 0';
                document.getElementById('monthly-operator-table-body').innerHTML =
                    '<tr><td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">Pilih outlet dan bulan untuk memuat data</td></tr>';
                document.getElementById('monthly-daily-table-body').innerHTML =
                    '<tr><td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Pilih outlet dan bulan untuk memuat data</td></tr>';
                return;
            }

            const [year, month] = monthValue.split('-');

            try {
                const summaryResponse = await fetch(
                    `/laporan/api/monthly/summary?store_id=${storeId}&month=${month}&year=${year}`);
                if (!summaryResponse.ok) throw new Error('Gagal fetch summary bulanan');
                const summaryData = await summaryResponse.json();

                document.getElementById('monthly-omset-value').textContent = formatCurrency(summaryData.omset || 0);
                document.getElementById('monthly-laba-kotor-value').textContent = formatCurrency(summaryData
                    .laba_kotor || 0);
                document.getElementById('monthly-pemasukan-value').textContent = formatCurrency(summaryData.pemasukan ||
                    0);
                document.getElementById('monthly-hpp-value').textContent = formatCurrency(summaryData.hpp || 0);

                const operatorsResponse = await fetch(
                    `/laporan/api/monthly/operators?store_id=${storeId}&month=${month}&year=${year}`);
                if (operatorsResponse.ok) {
                    const operatorsData = await operatorsResponse.json();
                    renderMonthlyOperators(operatorsData.operators || []);
                }

                const dailyResponse = await fetch(
                    `/laporan/api/monthly/daily?store_id=${storeId}&month=${month}&year=${year}`);
                if (dailyResponse.ok) {
                    const dailyData = await dailyResponse.json();
                    renderMonthlyDaily(dailyData.daily || []);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error mengambil data laporan bulanan');
            }
        }

        async function fetchAnnualCashbox(storeId, year) {
            try {
                const response = await fetch(`/laporan/api/annual/cashbox?store_id=${storeId}&year=${year}`);
                if (!response.ok) throw new Error('Gagal fetch cashbox tahunan');

                const data = await response.json();
                const list = document.getElementById('annual-cashbox-list');
                const items = data.items || [];

                if (items.length === 0) {
                    list.innerHTML =
                        '<div class="rounded-2xl border border-dashed border-gray-200 p-4 text-sm text-gray-500">Belum ada data cashbox tahunan</div>';
                    return;
                }

                const palettes = [
                    ['from-slate-900', 'to-slate-700'],
                    ['from-blue-600', 'to-indigo-600'],
                    ['from-emerald-500', 'to-emerald-600'],
                    ['from-amber-500', 'to-orange-500'],
                ];

                list.innerHTML = items.map((item, index) => {
                    const palette = palettes[index % palettes.length];
                    return `
                        <div class="rounded-2xl p-4 text-white shadow-lg bg-gradient-to-br ${palette[0]} ${palette[1]}">
                            <div class="text-xs text-white/80 mb-2">${item.nama_metode || '-'}</div>
                            <div class="text-lg font-bold">${formatCurrency(item.total || 0)}</div>
                        </div>
                    `;
                }).join('');
            } catch (error) {
                console.error('Error annual cashbox:', error);
            }
        }

        async function fetchAnnualData() {
            const storeId = document.getElementById('store-id-hidden').value;
            const year = document.getElementById('year-selector').value;

            if (!year) {
                document.getElementById('annual-omset-value').textContent = 'Rp 0';
                document.getElementById('annual-laba-kotor-value').textContent = 'Rp 0';
                document.getElementById('annual-pemasukan-value').textContent = 'Rp 0';
                document.getElementById('annual-hpp-value').textContent = 'Rp 0';
                document.getElementById('annual-operator-table-body').innerHTML =
                    '<tr><td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">Pilih outlet dan tahun untuk memuat data</td></tr>';
                document.getElementById('annual-monthly-table-body').innerHTML =
                    '<tr><td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Pilih outlet dan tahun untuk memuat data</td></tr>';
                document.getElementById('annual-cashbox-list').innerHTML =
                    '<div class="rounded-2xl border border-dashed border-gray-200 p-4 text-sm text-gray-500">Pilih outlet dan tahun untuk memuat data</div>';
                return;
            }

            try {
                const summaryResponse = await fetch(`/laporan/api/annual/summary?store_id=${storeId}&year=${year}`);
                if (!summaryResponse.ok) throw new Error('Gagal fetch summary tahunan');
                const summaryData = await summaryResponse.json();

                document.getElementById('annual-omset-value').textContent = formatCurrency(summaryData.omset || 0);
                document.getElementById('annual-laba-kotor-value').textContent = formatCurrency(summaryData
                    .laba_kotor || 0);
                document.getElementById('annual-pemasukan-value').textContent = formatCurrency(summaryData.pemasukan ||
                    0);
                document.getElementById('annual-hpp-value').textContent = formatCurrency(summaryData.hpp || 0);

                const operatorsResponse = await fetch(`/laporan/api/annual/operators?store_id=${storeId}&year=${year}`);
                if (operatorsResponse.ok) {
                    const operatorsData = await operatorsResponse.json();
                    renderAnnualOperators(operatorsData.operators || []);
                }

                const monthlyResponse = await fetch(`/laporan/api/annual/monthly?store_id=${storeId}&year=${year}`);
                if (monthlyResponse.ok) {
                    const monthlyData = await monthlyResponse.json();
                    renderAnnualMonthly(monthlyData.monthly || []);
                }

                fetchAnnualCashbox(storeId, year);
            } catch (error) {
                console.error('Error annual:', error);
                alert('Error mengambil data laporan tahunan');
            }
        }

        function renderAnnualOperators(operators) {
            const tbody = document.getElementById('annual-operator-table-body');
            tbody.innerHTML = '';

            if (operators.length === 0) {
                tbody.innerHTML =
                    '<tr><td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada data</td></tr>';
                return;
            }

            operators.forEach((op) => {
                tbody.innerHTML += `
                    <tr class="hover:bg-gray-50 transition cursor-pointer">
                        <td class="px-6 py-4 font-bold text-gray-900">${op.name || 'Unknown'}</td>
                        <td class="px-6 py-4 text-emerald-600 font-semibold">${formatCurrency(op.masuk || 0)}</td>
                        <td class="px-6 py-4 text-right text-red-500 font-semibold">${formatCurrency(op.keluar || 0)}</td>
                    </tr>
                `;
            });
        }

        function renderAnnualMonthly(monthly) {
            const tbody = document.getElementById('annual-monthly-table-body');
            tbody.innerHTML = '';

            if (monthly.length === 0) {
                tbody.innerHTML =
                    '<tr><td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada data</td></tr>';
                return;
            }

            const monthNames = [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];

            monthly.forEach((row) => {
                const bulanLabel = monthNames[(row.bulan || 1) - 1] || `Bulan ${row.bulan || '-'}`;
                tbody.innerHTML += `
                    <tr class="hover:bg-gray-50 transition cursor-pointer">
                        <td class="px-6 py-4 font-medium text-gray-900">${bulanLabel}</td>
                        <td class="px-6 py-4 text-gray-700">${row.jenis || '-'}</td>
                        <td class="px-6 py-4 font-semibold text-gray-900">${formatCurrency(row.total || 0)}</td>
                        <td class="px-6 py-4 text-emerald-600 font-semibold">${formatCurrency(row.laba || 0)}</td>
                        <td class="px-6 py-4 text-right text-gray-600">${row.frekuensi || 0} trx</td>
                    </tr>
                `;
            });
        }

        function updateStoreLabel(storeId) {
            const label = document.getElementById('store-label');
            if (!label) return;

            if (!storeId) {
                label.textContent = 'Semua Outlet';
                return;
            }

            const selectedLink = document.querySelector(`.dropdown-content a[data-store-id="${storeId}"]`);
            label.textContent = selectedLink ? selectedLink.textContent.trim() : 'Semua Outlet';
        }

        function loadActiveTabData() {
            const activeTab = window.laporanActiveTab || 'harian';

            if (activeTab === 'bulanan') {
                fetchMonthlyData();
                return;
            }

            if (activeTab === 'tahunan') {
                fetchAnnualData();
                return;
            }

            fetchDailyData();
        }

        function downloadLaporanExport(format) {
            const activeTab = window.laporanActiveTab || 'harian';
            const storeId = document.getElementById('store-id-hidden').value || '';
            const date = document.getElementById('date-selector').value || '';
            const monthValue = document.getElementById('month-selector').value || '';
            const year = document.getElementById('year-selector').value || '';

            const url = new URL(format === 'pdf' ?
                @json(route('laporan.export.pdf')) :
                @json(route('laporan.export.excel')),
                window.location.origin);

            url.searchParams.set('active_tab', activeTab);

            if (storeId) {
                url.searchParams.set('store_id', storeId);
            }

            if (activeTab === 'harian') {
                if (date) url.searchParams.set('date', date);
            } else if (activeTab === 'bulanan') {
                if (monthValue) {
                    const [month, yearValue] = monthValue.split('-');
                    url.searchParams.set('month', month);
                    url.searchParams.set('year', yearValue);
                }
            } else if (activeTab === 'tahunan' && year) {
                url.searchParams.set('year', year);
            }

            window.location.href = url.toString();
        }

        function applyCalendarFilter() {
            document.querySelectorAll('.dropdown-content').forEach((dropdown) => dropdown.classList.remove('show'));
            loadActiveTabData();
        }

        function toggleDropdown(event) {
            event.stopPropagation();
            const dropdown = event.currentTarget.nextElementSibling;

            document.querySelectorAll('.dropdown-content').forEach((content) => {
                if (content !== dropdown) {
                    content.classList.remove('show');
                }
            });

            dropdown.classList.toggle('show');
        }

        function renderMonthlyOperators(operators) {
            const tbody = document.getElementById('monthly-operator-table-body');
            tbody.innerHTML = '';

            if (operators.length === 0) {
                tbody.innerHTML =
                    '<tr><td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada data</td></tr>';
                return;
            }

            operators.forEach((op) => {
                tbody.innerHTML += `
                    <tr class="hover:bg-gray-50 transition cursor-pointer">
                        <td class="px-6 py-4 font-bold text-gray-900">${op.name || 'Unknown'}</td>
                        <td class="px-6 py-4 text-emerald-600 font-semibold">${formatCurrency(op.masuk || 0)}</td>
                        <td class="px-6 py-4 text-right text-red-500 font-semibold">${formatCurrency(op.keluar || 0)}</td>
                    </tr>
                `;
            });
        }

        function renderMonthlyDaily(daily) {
            const tbody = document.getElementById('monthly-daily-table-body');
            tbody.innerHTML = '';

            if (daily.length === 0) {
                tbody.innerHTML =
                    '<tr><td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada data</td></tr>';
                return;
            }

            daily.forEach((row) => {
                const tanggal = row.tanggal ? new Date(row.tanggal).toLocaleDateString('id-ID') : '-';
                tbody.innerHTML += `
                    <tr class="hover:bg-gray-50 transition cursor-pointer">
                        <td class="px-6 py-4 font-medium text-gray-900">${tanggal}</td>
                        <td class="px-6 py-4 text-gray-700">${row.jenis || '-'}</td>
                        <td class="px-6 py-4 font-semibold text-gray-900">${formatCurrency(row.total || 0)}</td>
                        <td class="px-6 py-4 text-emerald-600 font-semibold">${formatCurrency(row.laba || 0)}</td>
                        <td class="px-6 py-4 text-right text-gray-600">${row.frekuensi || 0} trx</td>
                    </tr>
                `;
            });
        }

        async function fetchOperatorData(storeId, date) {
            try {
                const response = await fetch(`/laporan/api/daily/operators?store_id=${storeId}&date=${date}`);
                if (!response.ok) throw new Error('Gagal fetch data operator');

                const data = await response.json();
                const tbody = document.getElementById('operator-table-body');
                tbody.innerHTML = '';

                if (data.operators && data.operators.length > 0) {
                    data.operators.forEach(op => {
                        const row = `
                            <tr class="hover:bg-gray-50 transition cursor-pointer">
                                <td class="px-6 py-4 font-bold text-gray-900">${op.name}</td>
                                <td class="px-6 py-4">${formatCurrency(op.penjualan || 0)}</td>
                                <td class="px-6 py-4 text-emerald-600">${formatCurrency(op.pemasukan || 0)}</td>
                                <td class="px-6 py-4 text-red-500">${formatCurrency(op.pengeluaran || 0)}</td>
                                <td class="px-6 py-4 text-right font-black text-gray-900">${formatCurrency((op.penjualan || 0) + (op.pemasukan || 0) - (op.pengeluaran || 0))}</td>
                            </tr>
                        `;
                        tbody.innerHTML += row;
                    });
                } else {
                    tbody.innerHTML =
                        '<tr><td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada data</td></tr>';
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        function selectStore(event) {
            event.preventDefault();
            event.stopPropagation();

            const storeId = event.currentTarget.dataset.storeId || '';
            document.getElementById('store-id-hidden').value = storeId;
            updateStoreLabel(storeId);
            document.querySelectorAll('.dropdown-content').forEach((dropdown) => dropdown.classList.remove('show'));
            loadActiveTabData();
        }

        window.addEventListener('click', function(event) {
            if (!event.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-content').forEach((dropdown) => dropdown.classList.remove(
                    'show'));
            }
        });

        // Event listeners
        document.getElementById('date-selector').addEventListener('change', fetchDailyData);
        document.getElementById('month-selector').addEventListener('change', fetchMonthlyData);
        document.getElementById('year-selector').addEventListener('change', fetchAnnualData);

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            window.laporanActiveTab = 'harian';
            updateStoreLabel(document.getElementById('store-id-hidden').value);
            loadActiveTabData();

            // Initialize ApexCharts for monthly/yearly trends
            const options = {
                series: [{
                    name: 'Omset',
                    type: 'area',
                    data: [31, 40, 28, 51, 42, 109, 100]
                }, {
                    name: 'Frekuensi',
                    type: 'line',
                    data: [11, 32, 45, 32, 34, 52, 41]
                }],
                chart: {
                    height: 350,
                    type: 'line',
                    toolbar: {
                        show: false
                    },
                    fontFamily: 'Plus Jakarta Sans, sans-serif'
                },
                colors: ['#3b82f6', '#f59e0b'],
                stroke: {
                    curve: 'smooth',
                    width: [0, 3]
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.4,
                        opacityTo: 0.1
                    }
                },
                dataLabels: {
                    enabled: false
                },
                yaxis: [{
                    title: {
                        text: 'Nominal Omset',
                        style: {
                            color: '#3b82f6'
                        }
                    },
                    labels: {
                        formatter: (val) => "Rp " + val.toLocaleString()
                    }
                }, {
                    opposite: true,
                    title: {
                        text: 'Jumlah Transaksi',
                        style: {
                            color: '#f59e0b'
                        }
                    }
                }],
                grid: {
                    borderColor: '#f1f1f1'
                }
            };

            const chart = new ApexCharts(document.querySelector("#chartTwins"), options);
            chart.render();
        });
    </script>
@endsection
