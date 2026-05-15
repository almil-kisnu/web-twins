@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/fitur.css') }}">
    <style>
        .dropdown-content a.active {
            background-color: #e0f2fe;
            color: #0369a1;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-right: 12px !important;
        }

        .dropdown-content a.active::after {
            content: '✓';
            font-weight: bold;
            color: #0369a1;
            font-size: 16px;
        }
    </style>
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
                            <div class="dropdown-content" id="outlet-dropdown">
                                <a href="#" data-store-id="" onclick="selectStore(event)" class="outlet-item">Semua
                                    Outlet</a>
                                @foreach ($outlets as $outlet)
                                    <a href="#" data-store-id="{{ $outlet->uuid }}" onclick="selectStore(event)"
                                        class="outlet-item">
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

        <section x-show="tab === 'harian'" x-transition.opacity aria-live="polite" class="space-y-8 relative">
            <div id="daily-loading-overlay"
                class="fixed inset-0 z-50 flex items-center justify-center bg-gray-50/75 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-200 ease-out">
                <div class="flex items-center gap-3 rounded-3xl border border-gray-200 bg-white px-5 py-4 shadow-lg">
                    <svg class="h-5 w-5 animate-spin text-blue-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <div>
                        <div class="text-sm font-semibold text-gray-800">Memuat laporan harian</div>
                        <div class="text-xs text-gray-500">Menyiapkan ringkasan, cashbox, dan operator...</div>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <article
                    class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-3xl p-8 text-white shadow-xl shadow-blue-200">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-3 bg-white/20 rounded-2xl">
                            <iconify-icon icon="solar:money-bag-bold" class="text-2xl"></iconify-icon>
                        </div>
                        <h2 class="text-blue-100 font-medium">Total Omset Hari Ini</h2>
                    </div>
                    <p class="text-4xl font-bold tracking-tight" id="omset-value" aria-label="Nominal Omset">
                        Rp
                        0</p>
                    <div
                        class="mt-6 pt-6 border-t border-white/10 flex justify-between items-center text-sm text-blue-100">
                        <span>Laba Kotor</span>
                        <span class="font-bold" id="laba-kotor-value">Rp 0</span>
                    </div>
                </article>

                <div class="grid grid-cols-2 gap-4">
                    <article
                        class="bg-emerald-500 rounded-[1.75rem] p-6 text-white shadow-lg shadow-emerald-100/60 border border-white/10">
                        <p class="text-xs text-emerald-100/90 font-medium mb-1 uppercase tracking-[0.18em]">Pemasukan</p>
                        <p class="text-2xl font-bold" id="pemasukan-value">Rp 0</p>
                        <div class="mt-4 flex justify-end">
                            <iconify-icon icon="solar:wad-of-money-bold" class="text-4xl opacity-25"></iconify-icon>
                        </div>
                    </article>
                    <article
                        class="bg-amber-500 rounded-[1.75rem] p-6 text-white shadow-lg shadow-amber-100/60 border border-white/10">
                        <p class="text-xs text-amber-100/90 font-medium mb-1 uppercase tracking-[0.18em]">Pengeluaran</p>
                        <p class="text-2xl font-bold" id="pengeluaran-value">Rp 0</p>
                        <div class="mt-4 flex justify-end">
                            <iconify-icon icon="solar:card-transfer-bold" class="text-4xl opacity-25"></iconify-icon>
                        </div>
                    </article>
                </div>
            </div>

            <div class="bg-white rounded-[1.75rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-gray-800">Distribusi Cashbox</h3>
                        <p class="text-xs text-gray-500 mt-1">Aliran uang riil per metode pembayaran</p>
                    </div>
                    <span class="px-3 py-1 bg-blue-50 text-blue-600 text-xs font-bold rounded-full">Cashbox</span>
                </div>
                <div class="p-6">
                    <div id="daily-cashbox-list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 p-4 text-sm text-gray-500">
                            Memuat data cashbox...</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-[1.75rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-gray-800">Aktivitas per Operator</h3>
                        <p class="text-xs text-gray-500 mt-1">Uang di laci dan info stok per operator</p>
                    </div>
                    <span class="px-3 py-1 bg-blue-50 text-blue-600 text-xs font-bold rounded-full">Laci & Stok</span>
                </div>
                <div class="p-6">
                    <div id="operator-list" class="space-y-4">
                        <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 p-4 text-sm text-gray-500">
                            Memuat data operator...
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-[1.75rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-gray-800">Transaksi Online</h3>
                        <p class="text-xs text-gray-500 mt-1">Pesanan yang masuk dari kanal online</p>
                    </div>
                    <span class="px-3 py-1 bg-sky-50 text-sky-600 text-xs font-bold rounded-full">Online</span>
                </div>
                <div class="overflow-hidden">
                    <div id="daily-online-list" class="divide-y divide-gray-100">
                        <div class="px-6 py-5 text-sm text-gray-500">Memuat transaksi online...</div>
                    </div>
                </div>
            </div>
        </section>

        <section x-show="tab === 'bulanan' || tab === 'tahunan'" x-transition.opacity style="display: none;"
            class="relative">
            <div id="monthly-loading-overlay"
                class="fixed inset-0 z-50 flex items-center justify-center bg-gray-50/75 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-200 ease-out">
                <div class="flex items-center gap-3 rounded-3xl border border-gray-200 bg-white px-5 py-4 shadow-lg">
                    <svg class="h-5 w-5 animate-spin text-blue-600" viewBox="0 0 24 24" fill="none"
                        aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <div>
                        <div class="text-sm font-semibold text-gray-800">Memuat laporan bulanan</div>
                        <div class="text-xs text-gray-500">Menghitung ringkasan dan rincian bulan ini...</div>
                    </div>
                </div>
            </div>
            <div id="annual-loading-overlay"
                class="fixed inset-0 z-50 flex items-center justify-center bg-gray-50/75 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-200 ease-out">
                <div class="flex items-center gap-3 rounded-3xl border border-gray-200 bg-white px-5 py-4 shadow-lg">
                    <svg class="h-5 w-5 animate-spin text-slate-800" viewBox="0 0 24 24" fill="none"
                        aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <div>
                        <div class="text-sm font-semibold text-gray-800">Memuat laporan tahunan</div>
                        <div class="text-xs text-gray-500">Menyusun ringkasan dan tren tahunan...</div>
                    </div>
                </div>
            </div>

            <div x-show="tab === 'bulanan'" class="space-y-8 mb-8">
                <article
                    class="overflow-hidden rounded-[2rem] border border-blue-100 bg-gradient-to-br from-blue-600 via-indigo-700 to-slate-900 text-white shadow-2xl shadow-blue-200">
                    <div class="p-8 md:p-10">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="rounded-2xl bg-white/15 p-3">
                                    <iconify-icon icon="solar:calendar-mark-bold" class="text-2xl"></iconify-icon>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-blue-100/80">Laporan Bulanan</p>
                                    <h2 class="mt-1 text-2xl font-bold md:text-3xl">Ringkasan Keuangan Bulanan</h2>
                                </div>
                            </div>
                            <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3 text-right">
                                <div class="text-[11px] uppercase tracking-[0.2em] text-white/70">Laba Bersih</div>
                                <div id="monthly-laba-bersih-badge" class="mt-1 text-xl font-bold">Rp 0</div>
                            </div>
                        </div>

                        <div class="mt-8 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                            <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/10">
                                <div class="text-xs uppercase tracking-[0.18em] text-white/70">Penjualan Toko</div>
                                <div id="monthly-offline-omset-value" class="mt-2 text-xl font-bold">Rp 0</div>
                            </div>
                            <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/10">
                                <div class="text-xs uppercase tracking-[0.18em] text-white/70">Penjualan Online</div>
                                <div id="monthly-online-omset-value" class="mt-2 text-xl font-bold">Rp 0</div>
                            </div>
                            <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/10">
                                <div class="text-xs uppercase tracking-[0.18em] text-white/70">Total Omset</div>
                                <div id="monthly-total-omset-value" class="mt-2 text-xl font-bold">Rp 0</div>
                            </div>
                            <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/10">
                                <div class="text-xs uppercase tracking-[0.18em] text-white/70">HPP</div>
                                <div id="monthly-hpp-value" class="mt-2 text-xl font-bold">Rp 0</div>
                            </div>
                            <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/10">
                                <div class="text-xs uppercase tracking-[0.18em] text-white/70">Pemasukan</div>
                                <div id="monthly-pemasukan-value" class="mt-2 text-xl font-bold">Rp 0</div>
                            </div>
                            <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/10">
                                <div class="text-xs uppercase tracking-[0.18em] text-white/70">Pengeluaran</div>
                                <div id="monthly-pengeluaran-value" class="mt-2 text-xl font-bold">Rp 0</div>
                            </div>
                        </div>

                        <div class="mt-8 flex flex-wrap items-center gap-3 text-sm text-blue-100/80">
                            <span class="rounded-full bg-white/10 px-3 py-1">Laba Kotor: <strong id="monthly-laba-kotor-value" class="text-white">Rp 0</strong></span>
                            <span class="rounded-full bg-white/10 px-3 py-1">Laba Bersih: <strong id="monthly-laba-bersih-value" class="text-white">Rp 0</strong></span>
                            <span class="rounded-full bg-white/10 px-3 py-1">Rugi: <strong id="monthly-rugi-value" class="text-white">Rp 0</strong></span>
                        </div>
                    </div>
                </article>

                <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                    <section class="overflow-hidden rounded-[1.75rem] border border-gray-100 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-gray-50 px-6 py-5">
                            <div>
                                <h3 class="font-bold text-gray-800">Data Operator</h3>
                                <p class="mt-1 text-xs text-gray-500">Total uang masuk dan keluar per operator</p>
                            </div>
                            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-600">Data Function Supabase</span>
                        </div>
                        <div class="p-6" id="monthly-operator-list">
                            <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 p-4 text-sm text-gray-500">Pilih outlet dan bulan untuk memuat data</div>
                        </div>
                    </section>

                    <section class="overflow-hidden rounded-[1.75rem] border border-gray-100 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-gray-50 px-6 py-5">
                            <div>
                                <h3 class="font-bold text-gray-800">Data Hutang & Piutang</h3>
                                <p class="mt-1 text-xs text-gray-500">Total yang belum lunas secara keseluruhan</p>
                            </div>
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-600">Debt Summary</span>
                        </div>
                        <div class="p-6">
                            <div id="monthly-debt-list" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 p-4 text-sm text-gray-500">Pilih outlet untuk memuat data</div>
                            </div>
                        </div>
                    </section>
                </div>

                <section class="overflow-hidden rounded-[1.75rem] border border-gray-100 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-gray-50 px-6 py-5">
                        <div>
                            <h3 class="font-bold text-gray-800">Data Transaksi</h3>
                            <p class="mt-1 text-xs text-gray-500">Rincian transaksi bulanan per jenis</p>
                        </div>
                        <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-bold text-sky-600">Breakdown Bulanan</span>
                    </div>
                    <div class="p-6" id="monthly-transaction-list">
                        <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 p-4 text-sm text-gray-500">Pilih outlet dan bulan untuk memuat data</div>
                    </div>
                </section>
            </div>

            <div x-show="tab === 'tahunan'" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <article
                    class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-3xl p-8 text-white shadow-xl shadow-blue-200">
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

        function setLoadingState(scope, isLoading) {
            const overlay = document.getElementById(`${scope}-loading-overlay`);
            if (overlay) {
                overlay.classList.toggle('opacity-0', !isLoading);
                overlay.classList.toggle('opacity-100', isLoading);
                overlay.classList.toggle('pointer-events-none', !isLoading);
                overlay.classList.toggle('pointer-events-auto', isLoading);
            }
        }

        async function fetchDailyData() {
            const storeId = document.getElementById('store-id-hidden').value;
            const date = document.getElementById('date-selector').value;

            setLoadingState('daily', true);

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
                await Promise.all([
                    fetchOperatorData(storeId, date),
                    fetchDailyCashbox(storeId, date),
                    fetchDailyOnline(storeId, date),
                ]);
            } catch (error) {
                console.error('Error:', error);
                alert('Error mengambil data laporan');
            } finally {
                setLoadingState('daily', false);
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

            setLoadingState('monthly', true);

            const monthlyResetValueIds = [
                'monthly-offline-omset-value',
                'monthly-online-omset-value',
                'monthly-total-omset-value',
                'monthly-hpp-value',
                'monthly-laba-kotor-value',
                'monthly-laba-bersih-value',
                'monthly-laba-bersih-badge',
                'monthly-pemasukan-value',
                'monthly-pengeluaran-value',
                'monthly-rugi-value',
            ];

            monthlyResetValueIds.forEach((id) => {
                const el = document.getElementById(id);
                if (el) el.textContent = 'Rp 0';
            });

            const operatorList = document.getElementById('monthly-operator-list');
            const debtList = document.getElementById('monthly-debt-list');
            const transactionList = document.getElementById('monthly-transaction-list');

            if (operatorList) {
                operatorList.innerHTML = '<div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 p-4 text-sm text-gray-500">Pilih outlet dan bulan untuk memuat data</div>';
            }

            if (debtList) {
                debtList.innerHTML = '<div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 p-4 text-sm text-gray-500">Pilih outlet untuk memuat data</div>';
            }

            if (transactionList) {
                transactionList.innerHTML = '<div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 p-4 text-sm text-gray-500">Pilih outlet dan bulan untuk memuat data</div>';
            }

            if (!monthValue) {
                setLoadingState('monthly', false);
                return;
            }

            const [year, month] = monthValue.split('-');

            try {
                const summaryResponse = await fetch(
                    `/laporan/api/monthly/summary?store_id=${storeId}&month=${month}&year=${year}`
                );
                if (!summaryResponse.ok) throw new Error('Gagal fetch summary bulanan');
                const summaryData = await summaryResponse.json();

                const offlineOmset = Number(summaryData.omset || 0);
                const onlineOmset = Number(summaryData.penjualan_online || 0);
                const totalOmset = offlineOmset + onlineOmset;
                const hpp = Number(summaryData.hpp || 0);
                const labaKotor = Number(summaryData.laba_kotor ?? (totalOmset - hpp));
                const pemasukan = Number(summaryData.pemasukan || 0);
                const pengeluaran = Number(summaryData.pengeluaran || 0);
                const rugi = Number(summaryData.rugi || 0);
                const labaBersih = Number(summaryData.laba_bersih ?? ((labaKotor + pemasukan) - (pengeluaran + rugi)));

                const setValue = (id, value, negative = false) => {
                    const el = document.getElementById(id);
                    if (!el) return;
                    el.textContent = negative && value > 0 ? `-${formatCurrency(value)}` : formatCurrency(value);
                    el.classList.toggle('text-red-300', negative && value > 0);
                    el.classList.toggle('text-white', !(negative && value > 0));
                };

                setValue('monthly-offline-omset-value', offlineOmset);
                setValue('monthly-online-omset-value', onlineOmset);
                setValue('monthly-total-omset-value', totalOmset);
                setValue('monthly-hpp-value', hpp);
                setValue('monthly-laba-kotor-value', labaKotor);
                setValue('monthly-laba-bersih-value', labaBersih, labaBersih < 0);
                setValue('monthly-laba-bersih-badge', labaBersih, labaBersih < 0);
                setValue('monthly-pemasukan-value', pemasukan);
                setValue('monthly-pengeluaran-value', pengeluaran);
                setValue('monthly-rugi-value', rugi, true);

                const operatorsResponse = await fetch(
                    `/laporan/api/monthly/operators?store_id=${storeId}&month=${month}&year=${year}`
                );
                const debtResponse = await fetch(`/laporan/api/monthly/debt-summary?store_id=${storeId}`);
                const dailyResponse = await fetch(
                    `/laporan/api/monthly/daily?store_id=${storeId}&month=${month}&year=${year}`
                );

                const operatorsPromise = operatorsResponse.ok
                    ? operatorsResponse.json().then((operatorsData) => renderMonthlyOperators(operatorsData.operators || []))
                    : Promise.resolve();

                const debtPromise = debtResponse.ok
                    ? debtResponse.json().then((debtData) => renderMonthlyDebtSummary(debtData.items || []))
                    : Promise.resolve();

                const dailyPromise = dailyResponse.ok
                    ? dailyResponse.json().then((dailyData) => renderMonthlyTransactions(dailyData.daily || []))
                    : Promise.resolve();

                await Promise.all([operatorsPromise, debtPromise, dailyPromise]);
            } catch (error) {
                console.error('Error:', error);
                alert('Error mengambil data laporan bulanan');
            } finally {
                setLoadingState('monthly', false);
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

            setLoadingState('annual', true);

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
                setLoadingState('annual', false);
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
                const operatorsPromise = operatorsResponse.ok ?
                    operatorsResponse.json().then((operatorsData) => renderAnnualOperators(operatorsData.operators ||
                    [])) :
                    Promise.resolve();

                const monthlyResponse = await fetch(`/laporan/api/annual/monthly?store_id=${storeId}&year=${year}`);
                const monthlyPromise = monthlyResponse.ok ?
                    monthlyResponse.json().then((monthlyData) => renderAnnualMonthly(monthlyData.monthly || [])) :
                    Promise.resolve();

                const cashboxPromise = fetchAnnualCashbox(storeId, year);

                await Promise.all([operatorsPromise, monthlyPromise, cashboxPromise]);
            } catch (error) {
                console.error('Error annual:', error);
                alert('Error mengambil data laporan tahunan');
            } finally {
                setLoadingState('annual', false);
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

        function getDailyOperatorJenisLabel(jenis) {
            switch (jenis) {
                case 'penjualan':
                    return 'Penjualan';
                case 'pembelian':
                    return 'Pembelian';
                case 'transfer':
                    return 'Transfer';
                case 'pemasukan':
                    return 'Pemasukan';
                case 'pengeluaran':
                    return 'Pengeluaran';
                case 'pelunasan_piutang':
                    return 'Pelunasan Piutang';
                case 'pembayaran_hutang':
                    return 'Pembayaran Hutang';
                case 'retur':
                    return 'Retur';
                case 'rugi':
                    return 'Produk Rugi';
                default:
                    return jenis || '-';
            }
        }

        function isDailyOperatorStokJenis(jenis) {
            return ['retur', 'rugi'].includes(jenis);
        }

        function isDailyOperatorNegativeJenis(jenis) {
            return ['pembelian', 'pengeluaran', 'pembayaran_hutang', 'rugi'].includes(jenis);
        }

        function getDailyOperatorJenisOrder(jenis) {
            const order = ['penjualan', 'pembelian', 'transfer', 'pelunasan_piutang', 'pembayaran_hutang', 'pemasukan',
                'pengeluaran'
            ];
            const stokOrder = ['retur', 'rugi'];
            const idx = order.indexOf(jenis);
            if (idx >= 0) return idx;
            const stokIdx = stokOrder.indexOf(jenis);
            return stokIdx >= 0 ? 100 + stokIdx : 999;
        }

        function renderDailyOperatorCards(rows) {
            const list = document.getElementById('operator-list');
            const grouped = new Map();

            rows.forEach((row) => {
                const name = row.name || 'Unknown';
                const jenis = row.jenis || '';
                const total = Number(row.total || 0);

                if (!grouped.has(name)) {
                    grouped.set(name, {
                        name,
                        items: []
                    });
                }

                grouped.get(name).items.push({
                    jenis,
                    total
                });
            });

            if (grouped.size === 0) {
                list.innerHTML =
                    '<div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 p-4 text-sm text-gray-500">Belum ada data operator hari ini</div>';
                return;
            }

            list.innerHTML = Array.from(grouped.values()).map((group) => {
                const items = group.items.slice().sort((a, b) => getDailyOperatorJenisOrder(a.jenis) -
                    getDailyOperatorJenisOrder(b.jenis));
                const laciItems = items.filter((item) => !isDailyOperatorStokJenis(item.jenis));
                const stokItems = items.filter((item) => isDailyOperatorStokJenis(item.jenis));
                const netLaci = laciItems.reduce((sum, item) => {
                    const amount = Number(item.total || 0);
                    return sum + (isDailyOperatorNegativeJenis(item.jenis) ? -amount : amount);
                }, 0);

                const renderItem = (item) => {
                    const label = getDailyOperatorJenisLabel(item.jenis);
                    const amount = Number(item.total || 0);
                    const displayAmount = isDailyOperatorNegativeJenis(item.jenis) ?
                        `-${formatCurrency(amount)}` : formatCurrency(amount);
                    const amountClass = isDailyOperatorNegativeJenis(item.jenis) ? 'text-red-500' : (item
                        .jenis === 'retur' ? 'text-amber-600' : 'text-emerald-600');

                    return `
                        <div class="flex items-center justify-between gap-4 rounded-2xl bg-gray-50 px-4 py-3">
                            <div class="min-w-0">
                                <div class="font-semibold text-gray-900">${label}</div>
                                <div class="text-xs text-gray-500">${item.jenis || '-'}</div>
                            </div>
                            <div class="font-bold whitespace-nowrap ${amountClass}">${displayAmount}</div>
                        </div>
                    `;
                };

                return `
                    <article class="rounded-[1.5rem] border border-gray-100 bg-white shadow-sm overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-50 flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 font-bold uppercase">${(group.name || '?').charAt(0)}</div>
                            <div class="min-w-0">
                                <div class="font-bold text-gray-900 truncate">${group.name || 'Unknown'}</div>
                                <div class="text-xs text-gray-500">Aktivitas transaksi harian</div>
                            </div>
                        </div>
                        <div class="p-5 space-y-4">
                            <div>
                                <div class="mb-3 flex items-center justify-between text-xs font-bold uppercase tracking-[0.18em] text-gray-500">
                                    <span>Uang di Laci</span>
                                    <span>${laciItems.length} item</span>
                                </div>
                                <div class="space-y-2">
                                    ${laciItems.length > 0 ? laciItems.map(renderItem).join('') : '<div class="rounded-2xl border border-dashed border-gray-200 p-3 text-sm text-gray-500">Tidak ada data laci</div>'}
                                </div>
                                <div class="mt-3 flex items-center justify-between rounded-2xl bg-blue-50 px-4 py-3">
                                    <span class="font-semibold text-gray-700">Net Laci</span>
                                    <span class="font-bold text-blue-600">${formatCurrency(netLaci)}</span>
                                </div>
                            </div>
                            <div>
                                <div class="mb-3 flex items-center justify-between text-xs font-bold uppercase tracking-[0.18em] text-gray-500">
                                    <span>Info Stok</span>
                                    <span>${stokItems.length} item</span>
                                </div>
                                <div class="space-y-2">
                                    ${stokItems.length > 0 ? stokItems.map(renderItem).join('') : '<div class="rounded-2xl border border-dashed border-gray-200 p-3 text-sm text-gray-500">Tidak ada info stok</div>'}
                                </div>
                            </div>
                        </div>
                    </article>
                `;
            }).join('');
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
            const list = document.getElementById('monthly-operator-list');
            if (!list) return;

            if (operators.length === 0) {
                list.innerHTML = '<div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 p-4 text-sm text-gray-500">Tidak ada data operator bulan ini</div>';
                return;
            }

            const totalMasuk = operators.reduce((sum, op) => sum + Number(op.masuk || 0), 0);
            const totalKeluar = operators.reduce((sum, op) => sum + Number(op.keluar || 0), 0);

            list.innerHTML = `
                <div class="overflow-hidden rounded-[1.5rem] border border-gray-100 bg-white shadow-sm">
                    <div class="border-b border-gray-50 px-5 py-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <div class="font-bold text-gray-900">Daftar Operator</div>
                                <div class="mt-1 text-xs text-gray-500">Ringkasan transaksi masuk dan keluar per operator</div>
                            </div>
                            <div class="flex gap-2 text-xs font-semibold">
                                <span class="rounded-full bg-emerald-50 px-3 py-1 text-emerald-600">Masuk ${formatCurrency(totalMasuk)}</span>
                                <span class="rounded-full bg-rose-50 px-3 py-1 text-rose-600">Keluar ${formatCurrency(totalKeluar)}</span>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50/70 text-xs font-semibold uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="px-5 py-3">Nama Operator</th>
                                    <th class="px-5 py-3">Masuk</th>
                                    <th class="px-5 py-3 text-right">Keluar</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                ${operators.map((op) => `
                                    <tr class="hover:bg-gray-50/70 transition">
                                        <td class="px-5 py-4 font-bold text-gray-900">${op.name || 'Unknown'}</td>
                                        <td class="px-5 py-4 font-semibold text-emerald-600">${formatCurrency(op.masuk || 0)}</td>
                                        <td class="px-5 py-4 text-right font-semibold text-rose-500">${formatCurrency(op.keluar || 0)}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        }

        function renderMonthlyDebtSummary(items) {
            const list = document.getElementById('monthly-debt-list');
            if (!list) return;

            let hutang = 0;
            let piutang = 0;

            items.forEach((item) => {
                if ((item.tipe || '').toLowerCase() === 'utang') hutang = Number(item.total_belum_lunas || 0);
                if ((item.tipe || '').toLowerCase() === 'piutang') piutang = Number(item.total_belum_lunas || 0);
            });

            list.innerHTML = `
                <div class="rounded-[1.5rem] border border-rose-100 bg-rose-50/60 p-5">
                    <div class="flex items-center gap-3">
                        <div class="rounded-2xl bg-rose-500/10 p-3 text-rose-600">
                            <iconify-icon icon="solar:money-bag-bold" class="text-2xl"></iconify-icon>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-rose-700">Hutang</div>
                            <div class="text-xs text-rose-600/80">Total kewajiban yang belum lunas</div>
                        </div>
                    </div>
                    <div class="mt-4 text-2xl font-bold text-rose-600">${formatCurrency(hutang)}</div>
                </div>
                <div class="rounded-[1.5rem] border border-emerald-100 bg-emerald-50/60 p-5">
                    <div class="flex items-center gap-3">
                        <div class="rounded-2xl bg-emerald-500/10 p-3 text-emerald-600">
                            <iconify-icon icon="solar:wallet-money-bold" class="text-2xl"></iconify-icon>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-emerald-700">Piutang</div>
                            <div class="text-xs text-emerald-600/80">Total dana pelanggan yang belum lunas</div>
                        </div>
                    </div>
                    <div class="mt-4 text-2xl font-bold text-emerald-600">${formatCurrency(piutang)}</div>
                </div>
            `;
        }

        function getMonthlyTransactionLabel(jenis) {
            switch (jenis) {
                case 'penjualan':
                    return 'Penjualan Toko';
                case 'penjualan_online':
                    return 'Penjualan Online';
                case 'pembelian':
                    return 'Pembelian';
                case 'transfer':
                    return 'Transfer';
                case 'retur':
                    return 'Retur';
                case 'rugi':
                    return 'Produk Rugi';
                default:
                    return jenis || '-';
            }
        }

        function isMonthlyTransactionNegative(jenis) {
            return ['pembelian', 'rugi'].includes(jenis);
        }

        function renderMonthlyTransactions(daily) {
            const list = document.getElementById('monthly-transaction-list');
            if (!list) return;

            if (daily.length === 0) {
                list.innerHTML = '<div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 p-4 text-sm text-gray-500">Tidak ada data transaksi bulan ini</div>';
                return;
            }

            const grouped = {
                penjualan: [],
                penjualan_online: [],
                pembelian: [],
                transfer: [],
                retur: [],
                rugi: [],
            };

            daily.forEach((row) => {
                if (Object.prototype.hasOwnProperty.call(grouped, row.jenis)) {
                    grouped[row.jenis].push(row);
                }
            });

            const order = ['penjualan', 'penjualan_online', 'pembelian', 'transfer', 'retur', 'rugi'];

            list.innerHTML = order
                .map((jenis) => {
                    const rows = grouped[jenis] || [];
                    if (!rows.length) return '';

                    const label = getMonthlyTransactionLabel(jenis);
                    const showLaba = ['penjualan', 'penjualan_online'].includes(jenis);
                    const total = rows.reduce((sum, row) => sum + Number(row.total || 0), 0);
                    const laba = rows.reduce((sum, row) => sum + Number(row.laba || 0), 0);
                    const freq = rows.reduce((sum, row) => sum + Number(row.frekuensi || 0), 0);
                    const negative = isMonthlyTransactionNegative(jenis);

                    return `
                        <details class="group overflow-hidden rounded-[1.5rem] border border-gray-100 bg-white shadow-sm" ${showLaba ? 'open' : ''}>
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4">
                                <div class="min-w-0">
                                    <div class="font-bold text-gray-900">${label}</div>
                                    <div class="mt-1 text-xs text-gray-500">${rows.length} entri harian</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-bold ${negative ? 'text-rose-600' : 'text-gray-900'}">${negative ? '-' : ''}${formatCurrency(total)}</div>
                                    ${showLaba ? `<div class="text-xs font-semibold text-emerald-600">Laba ${formatCurrency(laba)}</div>` : ''}
                                    <div class="text-xs text-gray-500">${freq} trx</div>
                                </div>
                            </summary>
                            <div class="border-t border-gray-100 px-5 py-4">
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left text-sm">
                                        <thead class="text-xs uppercase tracking-wide text-gray-500">
                                            <tr>
                                                <th class="py-2 pr-4">Tanggal</th>
                                                <th class="py-2 pr-4">Total</th>
                                                ${showLaba ? '<th class="py-2 pr-4">Laba</th>' : ''}
                                                <th class="py-2 text-right">Frekuensi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            ${rows.map((row) => {
                                                const tanggal = row.tanggal ? new Date(row.tanggal).toLocaleDateString('id-ID', { day: '2-digit', month: 'short' }) : '-';
                                                const rowTotal = Number(row.total || 0);
                                                const rowLaba = Number(row.laba || 0);
                                                const rowFreq = Number(row.frekuensi || 0);
                                                return `
                                                    <tr>
                                                        <td class="py-3 pr-4 font-medium text-gray-900">${tanggal}</td>
                                                        <td class="py-3 pr-4 font-semibold ${negative ? 'text-rose-600' : 'text-gray-900'}">${negative ? '-' : ''}${formatCurrency(rowTotal)}</td>
                                                        ${showLaba ? `<td class="py-3 pr-4 font-semibold text-emerald-600">${formatCurrency(rowLaba)}</td>` : ''}
                                                        <td class="py-3 text-right text-gray-600">${rowFreq} trx</td>
                                                    </tr>
                                                `;
                                            }).join('')}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </details>
                    `;
                })
                .filter(Boolean)
                .join('');
        }

        async function fetchOperatorData(storeId, date) {
            try {
                const response = await fetch(`/laporan/api/daily/operators?store_id=${storeId}&date=${date}`);
                if (!response.ok) throw new Error('Gagal fetch data operator');

                const data = await response.json();
                renderDailyOperatorCards(data.operators || []);
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

            // Update active state in outlet dropdown
            document.querySelectorAll('.outlet-item').forEach((item) => {
                item.classList.remove('active');
            });
            event.currentTarget.classList.add('active');

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

        // Initialize active outlet on page load
        window.addEventListener('load', function() {
            const currentStoreId = document.getElementById('store-id-hidden').value || '';

            // Update UI to show active outlet
            document.querySelectorAll('.outlet-item').forEach((item) => {
                const itemStoreId = item.dataset.storeId || '';
                if (itemStoreId === currentStoreId) {
                    item.classList.add('active');
                } else {
                    item.classList.remove('active');
                }
            });

            // Update store label display
            updateStoreLabel(currentStoreId);
        });

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
