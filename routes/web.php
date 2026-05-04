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
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

Route::get('/', [LandingController::class, 'index'])->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'role:owner,kepala_toko'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', 'role:owner,kepala_toko'])->group(function () {
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/stok', [ProductController::class, 'request'])->name('products.stok');
    Route::get('/products/restok', [ProductController::class, 'restok'])->name('products.restok');
    Route::get('/products/transfer', [ProductController::class, 'transfer'])->name('products.transfer');
    Route::get('/products/opname', [ProductController::class, 'opname'])->name('products.opname');
    Route::get('/products/request', [ProductController::class, 'request'])->name('products.request');
    Route::get('/products/opname-detail/{id}', [ProductController::class, 'show'])->name('products.opname.detail')->whereUuid('id');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
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

Route::prefix('users')->middleware(['auth', 'verified', 'role:owner,kepala_toko'])->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('users.index');
    Route::post('/', [UserController::class, 'store'])->name('users.store');
    Route::put('/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::post('/{id}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
});

Route::prefix('outlet')->middleware(['auth', 'verified', 'role:owner'])->group(function () {
    Route::get('/', [OutletController::class, 'index'])->name('outlet.index');
    Route::get('/kinerja', [OutletController::class, 'kinerja'])->name('outlet.kinerja');
    Route::get('/riwayat', [OutletController::class, 'riwayat'])->name('outlet.riwayat');
    Route::post('/', [OutletController::class, 'store'])->name('outlet.store');
    Route::put('/{id}', [OutletController::class, 'update'])->name('outlet.update');
    Route::delete('/{id}', [OutletController::class, 'destroy'])->name('outlet.destroy');
    Route::post('/{id}/toggle-status', [OutletController::class, 'toggleStatus'])->name('outlet.toggle-status');
});

Route::prefix('transaksi')->middleware(['auth', 'verified', 'role:owner,kepala_toko'])->group(function () {
    Route::get('/', [TransaksiController::class, 'riwayat'])->name('transaksi.index');
    Route::get('/riwayat', [TransaksiController::class, 'riwayat'])->name('transaksi.riwayat');
    Route::get('/diskon', [TransaksiController::class, 'diskon'])->name('transaksi.diskon');
    Route::post('/diskon', [TransaksiController::class, 'storeDiskon'])->name('transaksi.diskon.store');
    Route::put('/diskon/{id}', [TransaksiController::class, 'updateDiskon'])->name('transaksi.diskon.update');
    Route::delete('/diskon/{id}', [TransaksiController::class, 'destroyDiskon'])->name('transaksi.diskon.destroy');
});

Route::prefix('keuangan')->middleware(['auth', 'verified', 'role:owner,kepala_toko'])->group(function () {
    Route::get('/', [KeuanganController::class, 'index'])->name('keuangan.index');
    Route::get('/kas-box', [KeuanganController::class, 'kasBox'])->name('keuangan.kas-box');
    Route::get('/arus-uang', [KeuanganController::class, 'arusUang'])->name('keuangan.arus-uang');
    Route::get('/pemindahan-saldo', [KeuanganController::class, 'pemindahanSaldo'])->name('keuangan.pemindahan-saldo');
});

Route::prefix('kontak')->middleware(['auth', 'verified', 'role:owner,kepala_toko'])->group(function () {
    Route::get('/', [KontakController::class, 'index'])->name('kontak.index');
    Route::post('/sync', [KontakController::class, 'syncFromOrders'])->name('kontak.sync');
    Route::post('/', [KontakController::class, 'store'])->name('kontak.store');
    Route::put('/{id}', [KontakController::class, 'update'])->name('kontak.update');
    Route::delete('/{id}', [KontakController::class, 'destroy'])->name('kontak.destroy');
});

Route::prefix('buku-kas')->middleware(['auth', 'verified', 'role:owner,kepala_toko'])->group(function () {
    Route::get('/', [BukuKasController::class, 'index'])->name('keuangan.transaksi');
    Route::get('/export', [BukuKasController::class, 'export'])->name('keuangan.export');
    Route::post('/cashflow', [BukuKasController::class, 'storeCashFlow'])->name('keuangan.cashflow.store');
    Route::put('/cashflow/{id}', [BukuKasController::class, 'updateCashFlow'])->name('keuangan.cashflow.update');
    Route::delete('/cashflow/{id}', [BukuKasController::class, 'deleteCashFlow'])->name('keuangan.cashflow.destroy');

    Route::post('/debt', [BukuKasController::class, 'storeDebt'])->name('keuangan.debt.store');
    Route::put('/debt/{id}', [BukuKasController::class, 'updateDebt'])->name('keuangan.debt.update');
    Route::post('/debt/{id}/pay', [BukuKasController::class, 'payDebt'])->name('keuangan.debt.pay');
    Route::delete('/debt/{id}', [BukuKasController::class, 'deleteDebt'])->name('keuangan.debt.destroy');
});

Route::get('/laporan', [LaporanController::class, 'index'])
    ->middleware(['auth', 'verified', 'role:owner,kepala_toko'])
    ->name('laporan.index');

Route::prefix('absensi')->middleware(['auth', 'verified', 'role:owner,kepala_toko'])->group(function () {
    Route::get('/', [AbsensiController::class, 'index'])->name('absensi.index');
    Route::post('/shift', [AbsensiController::class, 'storeShift'])->name('absensi.shift.store');
    Route::put('/shift/{uuid}', [AbsensiController::class, 'updateShift'])->name('absensi.shift.update');
    Route::delete('/shift/{uuid}', [AbsensiController::class, 'deleteShift'])->name('absensi.shift.destroy');
    Route::post('/penugasan', [AbsensiController::class, 'storePenugasan'])->name('absensi.penugasan.store');
    Route::put('/penugasan/{id}', [AbsensiController::class, 'updatePenugasan'])->name('absensi.penugasan.update');
    Route::delete('/penugasan/{id}', [AbsensiController::class, 'deletePenugasan'])->name('absensi.penugasan.destroy');
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
Route::post('/submit-general-review', [LandingController::class, 'generalReview'])
    ->middleware(['auth', 'verified'])
    ->name('landing.review.store');

require __DIR__ . '/auth.php';
