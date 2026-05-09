<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;

echo "--- Status Transaksi ---\n";
$statuses = Transaction::distinct()->pluck('status');
print_r($statuses->toArray());

echo "\n--- Contoh Transaksi Penjualan ---\n";
$sample = Transaction::where('jenis', 'penjualan')->first();
if ($sample) {
    print_r($sample->toArray());
} else {
    echo "Tidak ada transaksi jenis 'penjualan'\n";
    $any = Transaction::first();
    if ($any) {
        echo "Contoh transaksi apa saja:\n";
        print_r($any->toArray());
    }
}

echo "\n--- Cek Detail Transaksi ---\n";
$detailCount = TransactionDetail::count();
echo "Total baris transaction_detail: $detailCount\n";
if ($detailCount > 0) {
    $detail = TransactionDetail::first();
    print_r($detail->toArray());
}
