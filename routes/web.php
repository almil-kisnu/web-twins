<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DashboardController; // Tambahkan ini
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\KontakController;
use App\Http\Controllers\BukuKasController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\KeuanganController;
use App\Http\Controllers\PerilakuController;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

Route::get('/', [LandingController::class, 'index'])->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'role:admin'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/stok', [ProductController::class, 'request'])->name('products.stok');
    Route::get('/products/restok', [ProductController::class, 'restok'])->name('products.restok');
    Route::get('/products/transfer', [ProductController::class, 'transfer'])->name('products.transfer');
    Route::get('/products/opname', [ProductController::class, 'opname'])->name('products.opname');
    Route::get('/products/request', [ProductController::class, 'request'])->name('products.request');
    Route::get('/products/opname-detail/{id}', [ProductController::class, 'show'])->name('products.opname.detail')->whereUuid('id');
    Route::get('/products/detail/{id}', [ProductController::class, 'showProductDetail'])->name('products.detail');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::post('/products/mass-delete', [ProductController::class, 'massDelete'])->name('products.mass-delete');
    Route::post('/products/mass-destroy', [ProductController::class, 'massDestroy'])->name('products.mass_destroy');

    // Opname Routes
    Route::post('/products/opname', [ProductController::class, 'storeOpname'])->name('products.opname.store');
    Route::put('/products/opname/{id}', [ProductController::class, 'updateOpname'])->name('products.opname.update');
    Route::delete('/products/opname/{id}', [ProductController::class, 'destroyOpname'])->name('products.opname.destroy');
    Route::post('/products/opname/{id}/finalize', [ProductController::class, 'finalizeOpname'])->name('products.opname.finalize');

    // Request Routes
    Route::post('/products/request', [ProductController::class, 'storeRequest'])->name('products.request.store');
    Route::put('/products/request/{id}', [ProductController::class, 'updateRequest'])->name('products.request.update');
    Route::delete('/products/request/{id}', [ProductController::class, 'destroyRequest'])->name('products.request.destroy');
    Route::post('/products/request/{id}/approve', [ProductController::class, 'approveRequest'])->name('products.request.approve');
    Route::post('/products/request/{id}/reject', [ProductController::class, 'rejectRequest'])->name('products.request.reject');
    Route::post('/products/request/{id}/ship', [ProductController::class, 'shipRequest'])->name('products.request.ship');
    Route::post('/products/request/{id}/receive', [ProductController::class, 'receiveRequest'])->name('products.request.receive');
    Route::put('/products/store-data/{uuid}', [ProductController::class, 'updateStoreData'])->name('products.store_data.update');

    // Export Routes
    Route::get('/products/export/excel', [ProductController::class, 'exportExcel'])->name('products.export.excel');
    Route::get('/products/export/pdf', [ProductController::class, 'exportPdf'])->name('products.export.pdf');
    Route::post('/products/category', [ProductController::class, 'storeCategory'])->name('products.category.store');
    Route::post('/products/restok', [ProductController::class, 'storeRestok'])->name('products.restok.store');
    Route::post('/products/restok/pay', [ProductController::class, 'payPurchaseDebt'])->name('products.restok.pay');
    Route::delete('/products/restok/{uuid}', [ProductController::class, 'destroyRestok'])->name('products.restok.destroy');



    Route::get('/products/restok/{uuid}', [ProductController::class, 'viewPurchaseDetail'])->name('products.restok.detail');
    Route::get('/transfer', [ProductController::class, 'transfer'])->name('products.transfer');
    Route::get('/products/by-store/{store_id}', [ProductController::class, 'getProductsByStore'])->name('products.by_store');
    Route::post('/transfer', [ProductController::class, 'storeTransfer'])->name('products.transfer.store');
    Route::post('/transfer/approve/{uuid}', [ProductController::class, 'approveTransfer'])->name('products.transfer.approve');
    Route::post('/transfer/ship/{uuid}', [ProductController::class, 'shipTransfer'])->name('products.transfer.ship');
    Route::post('/transfer/confirm/{uuid}', [ProductController::class, 'confirmTransfer'])->name('products.transfer.confirm');
});
use App\Http\Controllers\UserController;
use App\Http\Controllers\OutletController;

Route::prefix('users')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('users.index');
    Route::post('/', [UserController::class, 'store'])->name('users.store');
    Route::put('/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::post('/{id}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
});

Route::prefix('outlet')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [OutletController::class, 'index'])->name('outlet.index');
    Route::get('/kinerja', [OutletController::class, 'kinerja'])->name('outlet.kinerja');
    Route::get('/riwayat', [OutletController::class, 'riwayat'])->name('outlet.riwayat');
    Route::post('/', [OutletController::class, 'store'])->name('outlet.store');
    Route::put('/{id}', [OutletController::class, 'update'])->name('outlet.update');
    Route::delete('/{id}', [OutletController::class, 'destroy'])->name('outlet.destroy');
    Route::post('/{id}/toggle-status', [OutletController::class, 'toggleStatus'])->name('outlet.toggle-status');
});

Route::prefix('transaksi')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [TransaksiController::class, 'index'])->name('transaksi.index');
    Route::get('/riwayat', [TransaksiController::class, 'riwayat'])->name('transaksi.riwayat');
    Route::get('/diskon', [TransaksiController::class, 'diskon'])->name('transaksi.diskon');
    Route::post('/diskon', [TransaksiController::class, 'storeDiskon'])->name('transaksi.diskon.store');
    Route::put('/diskon/{id}', [TransaksiController::class, 'updateDiskon'])->name('transaksi.diskon.update');
    Route::delete('/diskon/{id}', [TransaksiController::class, 'destroyDiskon'])->name('transaksi.diskon.destroy');
});

Route::prefix('keuangan')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [KeuanganController::class, 'index'])->name('keuangan.index');
    Route::post('/cashbox', [KeuanganController::class, 'storeCashbox'])->name('keuangan.cashbox.store');
    Route::put('/cashbox/{id}', [KeuanganController::class, 'updateCashbox'])->name('keuangan.cashbox.update');
    Route::delete('/cashbox/{id}', [KeuanganController::class, 'destroyCashbox'])->name('keuangan.cashbox.destroy');
    Route::get('/kas-box', [KeuanganController::class, 'kasBox'])->name('keuangan.kas-box');
    Route::get('/arus-uang', [KeuanganController::class, 'arusUang'])->name('keuangan.arus-uang');
    Route::get('/pemindahan-saldo', [KeuanganController::class, 'pemindahanSaldo'])->name('keuangan.pemindahan-saldo');
    Route::post('/transfer', [KeuanganController::class, 'transferSaldo'])->name('keuangan.transfer.store');
});

Route::prefix('kontak')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [KontakController::class, 'index'])->name('kontak.index');
    Route::post('/sync', [KontakController::class, 'syncFromOrders'])->name('kontak.sync');
    Route::post('/', [KontakController::class, 'store'])->name('kontak.store');
    Route::put('/{id}', [KontakController::class, 'update'])->name('kontak.update');
    Route::delete('/{id}', [KontakController::class, 'destroy'])->name('kontak.destroy');
});

Route::prefix('buku-kas')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [BukuKasController::class, 'pengeluaran'])->name('keuangan.transaksi');
    Route::get('/pengeluaran', [BukuKasController::class, 'pengeluaran'])->name('keuangan.pengeluaran');
    Route::get('/pemasukan', [BukuKasController::class, 'pemasukan'])->name('keuangan.pemasukan');
    Route::get('/hutang', [BukuKasController::class, 'hutang'])->name('keuangan.hutang');
    Route::get('/piutang', [BukuKasController::class, 'piutang'])->name('keuangan.piutang');
    Route::get('/export', [BukuKasController::class, 'export'])->name('keuangan.export');
    Route::post('/cashflow', [BukuKasController::class, 'storeCashFlow'])->name('keuangan.cashflow.store');
    Route::get('/cashflow', function () {
        return redirect()->route('keuangan.transaksi', ['active_tab' => 'pengeluaran']);
    });
    Route::put('/cashflow/{id}', [BukuKasController::class, 'updateCashFlow'])->name('keuangan.cashflow.update');
    Route::delete('/cashflow/{id}', [BukuKasController::class, 'deleteCashFlow'])->name('keuangan.cashflow.destroy');

    Route::post('/debt', [BukuKasController::class, 'storeDebt'])->name('keuangan.debt.store');
    Route::get('/debt', function () {
        return redirect()->route('keuangan.transaksi', ['active_tab' => 'hutang']);
    });
    Route::put('/debt/{id}', [BukuKasController::class, 'updateDebt'])->name('keuangan.debt.update');
    Route::post('/debt/{id}/pay', [BukuKasController::class, 'payDebt'])->name('keuangan.debt.pay');
    Route::delete('/debt/{id}', [BukuKasController::class, 'deleteDebt'])->name('keuangan.debt.destroy');
    Route::get('/reference-detail/{id}', [BukuKasController::class, 'getReferenceDetail'])->name('keuangan.reference-detail');
});

Route::get('/laporan', [LaporanController::class, 'index'])
    ->middleware(['auth', 'role:admin'])
    ->name('laporan.index');

Route::middleware(['auth', 'role:admin'])->prefix('laporan/export')->group(function () {
    Route::get('/excel', [LaporanController::class, 'exportExcel'])->name('laporan.export.excel');
    Route::get('/pdf', [LaporanController::class, 'exportPdf'])->name('laporan.export.pdf');
});

Route::middleware(['auth', 'role:admin'])->prefix('laporan/api')->group(function () {
    Route::get('/daily/summary', [LaporanController::class, 'dailySummary'])->name('laporan.api.daily.summary');
    Route::get('/daily/operators', [LaporanController::class, 'dailyOperators'])->name('laporan.api.daily.operators');
    Route::get('/daily/cashbox', [LaporanController::class, 'dailyCashbox'])->name('laporan.api.daily.cashbox');
    Route::get('/daily/online', [LaporanController::class, 'dailyOnlineTransactions'])->name('laporan.api.daily.online');
    Route::get('/monthly/summary', [LaporanController::class, 'monthlySummary'])->name('laporan.api.monthly.summary');
    Route::get('/monthly/operators', [LaporanController::class, 'monthlyOperators'])->name('laporan.api.monthly.operators');
    Route::get('/monthly/debt-summary', [LaporanController::class, 'monthlyDebtSummary'])->name('laporan.api.monthly.debt-summary');
    Route::get('/monthly/daily', [LaporanController::class, 'monthlyDaily'])->name('laporan.api.monthly.daily');
    Route::get('/annual/summary', [LaporanController::class, 'annualSummary'])->name('laporan.api.annual.summary');
    Route::get('/annual/operators', [LaporanController::class, 'annualOperators'])->name('laporan.api.annual.operators');
    Route::get('/annual/monthly', [LaporanController::class, 'annualMonthly'])->name('laporan.api.annual.monthly');
    Route::get('/annual/cashbox', [LaporanController::class, 'annualCashbox'])->name('laporan.api.annual.cashbox');
});

Route::prefix('absensi')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [AbsensiController::class, 'index'])->name('absensi.index');

    // Master Shift
    Route::post('/shift', [AbsensiController::class, 'storeShift'])->name('absensi.shift.store');
    Route::put('/shift/{uuid}', [AbsensiController::class, 'updateShift'])->name('absensi.shift.update');
    Route::delete('/shift/{uuid}', [AbsensiController::class, 'deleteShift'])->name('absensi.shift.destroy');

    // Jadwal Karyawan
    Route::post('/jadwal', [AbsensiController::class, 'storeJadwal'])->name('absensi.jadwal.store');
    Route::put('/jadwal/{uuid}', [AbsensiController::class, 'updateJadwal'])->name('absensi.jadwal.update');
    Route::delete('/jadwal/{uuid}', [AbsensiController::class, 'deleteJadwal'])->name('absensi.jadwal.destroy');

    // Riwayat Absensi — Update Status
    Route::put('/riwayat/{uuid}/status', [AbsensiController::class, 'updateAbsensiStatus'])->name('absensi.riwayat.update-status');
});

Route::prefix('perilaku')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [PerilakuController::class, 'index'])->name('perilaku.index');
    Route::get('/customer/{contact_id}', [PerilakuController::class, 'detailCustomer'])->name('perilaku.customer.detail');
    Route::get('/produk/{product_id}', [PerilakuController::class, 'detailProduk'])->name('perilaku.produk.detail');

    // API endpoints (AJAX)
    Route::get('/api/customer/yearly', [PerilakuController::class, 'customerYearly'])->name('perilaku.api.customer.yearly');
    Route::get('/api/customer/daily', [PerilakuController::class, 'customerDaily'])->name('perilaku.api.customer.daily');
    Route::get('/api/customer/history', [PerilakuController::class, 'customerHistory'])->name('perilaku.api.customer.history');
    Route::get('/api/product/yearly', [PerilakuController::class, 'productYearly'])->name('perilaku.api.product.yearly');
    Route::get('/api/product/daily', [PerilakuController::class, 'productDaily'])->name('perilaku.api.product.daily');
    Route::get('/api/product/history', [PerilakuController::class, 'productHistory'])->name('perilaku.api.product.history');
});

Route::post('/midtrans/webhook', [MidtransWebhookController::class, 'handle'])
    ->name('midtrans.webhook');

Route::get('/outlet/{id}', [LandingController::class, 'showOutlet'])->name('user.index');
Route::post('/outlet/{id}/delivery-address', [LandingController::class, 'saveDeliveryAddress'])
    ->middleware(['auth'])
    ->name('user.delivery-address.store');
Route::post('/outlet/{id}/checkout-token', [LandingController::class, 'createCheckoutToken'])
    ->middleware(['auth'])
    ->name('user.checkout.token');
Route::get('/outlet/{id}/payment-order/{orderId}', [LandingController::class, 'showPaymentOrder'])
    ->middleware(['auth'])
    ->name('user.payment-order.show');
Route::get('/outlet/{id}/history', [LandingController::class, 'getUserHistory'])
    ->middleware(['auth'])
    ->name('user.history.api');
Route::post('/outlet/{id}/review', [LandingController::class, 'storeReview'])
    ->middleware(['auth', 'verified'])
    ->name('store.review.store');
Route::post('/outlet/{id}/sync-payment', [LandingController::class, 'syncPaymentStatus'])
    ->middleware(['auth'])
    ->name('user.payment.sync');
Route::post('/submit-general-review', [LandingController::class, 'generalReview'])
    ->middleware(['auth', 'verified'])
    ->name('landing.review.store');

require __DIR__ . '/auth.php';
