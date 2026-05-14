<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Outlet;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\Contact;
use App\Models\PaymentOrder;
use App\Models\User;
use App\Models\CashFlow;
use App\Models\Debt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $monthStart = Carbon::now()->startOfMonth();
        
        // Filter by store for owner
        $storeId = $request->get('store_id');
        $stores = Outlet::all();

        if ($user->role === 'owner') {
            // 1. Top Row Stats
            $stats = $this->getStats($storeId, $today, $yesterday);

            // 2. Chart Data: Penjualan Hari Ini (per jam)
            $queryChart = Transaction::whereDate('tanggal', $today);
            if ($storeId) $queryChart->where('store_id', $storeId);
            
            $salesPerHour = $queryChart
                ->select(DB::raw('EXTRACT(HOUR FROM tanggal) as hour'), DB::raw('SUM(total) as total'))
                ->groupBy('hour')
                ->pluck('total', 'hour')
                ->all();
            
            $chartData = [];
            for ($i = 0; $i < 24; $i++) {
                $chartData[] = [
                    'x' => sprintf('%02d:00', $i),
                    'y' => (float)($salesPerHour[$i] ?? 0)
                ];
            }

            // 3. CashFlow (Pemasukan & Pengeluaran) - Current Month
            $pemasukanMonth = CashFlow::where('jenis', 'pemasukan')->where('tanggal', '>=', $monthStart);
            $pengeluaranMonth = CashFlow::where('jenis', 'pengeluaran')->where('tanggal', '>=', $monthStart);
            if ($storeId) {
                $pemasukanMonth->where('store_id', $storeId);
                $pengeluaranMonth->where('store_id', $storeId);
            }
            $totalPemasukanMonth = $pemasukanMonth->sum('nominal');
            $totalPengeluaranMonth = $pengeluaranMonth->sum('nominal');

            // 4. Hutang & Piutang Summary
            $piutangQuery = Debt::where('tipe', 'piutang')->where('sisa', '>', 0);
            $hutangQuery = Debt::whereIn('tipe', ['utang', 'hutang'])->where('sisa', '>', 0);
            if ($storeId) {
                $piutangQuery->where('store_id', $storeId);
                $hutangQuery->where('store_id', $storeId);
            }
            $totalPiutang = $piutangQuery->sum('sisa');
            $totalHutang = $hutangQuery->sum('sisa');

            // 5. Stok Menipis (Table)
            $queryLowStock = ProductStore::with('product')
                ->where('stok', '<=', DB::raw('stok_minimum'));
            if ($storeId) $queryLowStock->where('store_id', $storeId);
            $lowStockProducts = $queryLowStock->take(5)->get();

            // 6. Produk Terlaris
            $queryTopProducts = TransactionDetail::select('product_id', DB::raw('SUM(jmlh) as total_qty'), DB::raw('SUM(jmlh * harga_jual) as total_revenue'))
                ->whereHas('transaction', function($q) use ($today, $storeId) {
                    $q->whereDate('tanggal', $today);
                    if ($storeId) $q->where('store_id', $storeId);
                })
                ->groupBy('product_id')
                ->orderBy('total_qty', 'desc')
                ->with('product')
                ->take(5);
            $topProducts = $queryTopProducts->get();

            // 7. Aktivitas Karyawan
            $queryActivities = Transaction::with('user')
                ->whereDate('tanggal', $today);
            if ($storeId) $queryActivities->where('store_id', $storeId);
            $activities = $queryActivities->latest('tanggal')
                ->take(5)
                ->get()
                ->map(function($trx) {
                    return [
                        'user' => $trx->user->name ?? 'System',
                        'role' => $trx->user->role ?? 'Kasir',
                        'action' => 'memproses transaksi #' . substr($trx->uuid, 0, 8),
                        'time' => Carbon::parse($trx->tanggal)->format('H:i'),
                        'type' => 'transaction'
                    ];
                });

            $data = array_merge($stats, [
                'chartData' => $chartData,
                'totalPemasukanMonth' => $totalPemasukanMonth,
                'totalPengeluaranMonth' => $totalPengeluaranMonth,
                'totalPiutang' => $totalPiutang,
                'totalHutang' => $totalHutang,
                'lowStockProducts' => $lowStockProducts,
                'topProducts' => $topProducts,
                'activities' => $activities,
                'stores' => $stores,
                'currentStoreId' => $storeId,
                'title' => 'Dashboard Owner'
            ]);

            return view('dashboard', $data);
        } 
        
        return view('dashboard', ['title' => 'Dashboard']);
    }

    private function getStats($storeId, $today, $yesterday)
    {
        $queryToday = Transaction::whereDate('tanggal', $today);
        $queryYesterday = Transaction::whereDate('tanggal', $yesterday);

        if ($storeId) {
            $queryToday->where('store_id', $storeId);
            $queryYesterday->where('store_id', $storeId);
        }

        $transaksiToday = $queryToday->count();
        $transaksiYesterday = $queryYesterday->count();
        $diffTransaksi = $this->calculateDiff($transaksiToday, $transaksiYesterday);

        $pendapatanToday = $queryToday->sum('total');
        $pendapatanYesterday = $queryYesterday->sum('total');
        $diffPendapatan = $this->calculateDiff($pendapatanToday, $pendapatanYesterday);

        $produkTerjualToday = TransactionDetail::whereHas('transaction', function($q) use ($today, $storeId) {
            $q->whereDate('tanggal', $today);
            if ($storeId) $q->where('store_id', $storeId);
        })->sum('jmlh');
        
        $produkTerjualYesterday = TransactionDetail::whereHas('transaction', function($q) use ($yesterday, $storeId) {
            $q->whereDate('tanggal', $yesterday);
            if ($storeId) $q->where('store_id', $storeId);
        })->sum('jmlh');
        $diffProduk = $this->calculateDiff($produkTerjualToday, $produkTerjualYesterday);

        $stokMenipisCount = ProductStore::where('stok', '<=', DB::raw('stok_minimum'));
        if ($storeId) $stokMenipisCount->where('store_id', $storeId);
        $stokMenipisCount = $stokMenipisCount->count();

        $karyawanQuery = User::whereHas('operator', function($q) {
            $q->where('nama', '!=', 'Owner');
        });
        if ($storeId) $karyawanQuery->where('store_id', $storeId);
        $karyawanAktif = $karyawanQuery->count();

        return [
            'total_transaksi' => $transaksiToday,
            'diff_transaksi' => $diffTransaksi,
            'total_pendapatan' => (float)$pendapatanToday,
            'diff_pendapatan' => $diffPendapatan,
            'total_produk_terjual' => (int)$produkTerjualToday,
            'diff_produk_terjual' => $diffProduk,
            'total_stok_menipis' => $stokMenipisCount,
            'total_karyawan_aktif' => $karyawanAktif,
        ];
    }

    private function calculateDiff($today, $yesterday)
    {
        if ($yesterday == 0) return $today > 0 ? 100 : 0;
        return round((($today - $yesterday) / $yesterday) * 100, 1);
    }
}
