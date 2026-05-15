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
        $storeId = $request->get('store_id');
        $stores = Outlet::all();

        if ($user->role === 'owner') {
            $stats = $this->getStats($storeId, $today, $yesterday);

            // 1. SALES CHART DATA
            $hTrxData = Transaction::whereDate('tanggal', $today)->when($storeId, fn($q) => $q->where('store_id', $storeId))->select(DB::raw('EXTRACT(HOUR FROM tanggal) as hour'), DB::raw('SUM(total) as total'))->groupBy('hour')->pluck('total', 'hour')->all();
            $hPOData = PaymentOrder::whereDate('paid_at', $today)->whereNotNull('paid_at')->when($storeId, fn($q) => $q->where('outlet_id', $storeId))->select(DB::raw('EXTRACT(HOUR FROM paid_at) as hour'), DB::raw('SUM(total_amount) as total'))->groupBy('hour')->pluck('total', 'hour')->all();
            $chartHarian = ['labels' => [], 'offline' => [], 'online' => []];
            for ($i = 0; $i < 24; $i++) { $chartHarian['labels'][] = sprintf('%02d:00', $i); $chartHarian['offline'][] = (float)($hTrxData[$i] ?? 0); $chartHarian['online'][] = (float)($hPOData[$i] ?? 0); }

            $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
            $wTrxData = Transaction::whereBetween('tanggal', [$startOfWeek, Carbon::now()->endOfWeek(Carbon::SUNDAY)])->when($storeId, fn($q) => $q->where('store_id', $storeId))->select(DB::raw('EXTRACT(DOW FROM tanggal) as dow'), DB::raw('SUM(total) as total'))->groupBy('dow')->pluck('total', 'dow')->all();
            $wPOData = PaymentOrder::whereBetween('paid_at', [$startOfWeek, Carbon::now()->endOfWeek(Carbon::SUNDAY)])->whereNotNull('paid_at')->when($storeId, fn($q) => $q->where('outlet_id', $storeId))->select(DB::raw('EXTRACT(DOW FROM paid_at) as dow'), DB::raw('SUM(total_amount) as total'))->groupBy('dow')->pluck('total', 'dow')->all();
            $days = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 0 => 'Minggu'];
            $chartMingguan = ['labels' => [], 'offline' => [], 'online' => []];
            foreach ($days as $dow => $name) { $chartMingguan['labels'][] = $name; $chartMingguan['offline'][] = (float)($wTrxData[$dow] ?? 0); $chartMingguan['online'][] = (float)($wPOData[$dow] ?? 0); }

            $startOfYear = Carbon::now()->startOfYear();
            $mTrxData = Transaction::whereBetween('tanggal', [$startOfYear, Carbon::now()->endOfYear()])->when($storeId, fn($q) => $q->where('store_id', $storeId))->select(DB::raw('EXTRACT(MONTH FROM tanggal) as month'), DB::raw('SUM(total) as total'))->groupBy('month')->pluck('total', 'month')->all();
            $mPOData = PaymentOrder::whereBetween('paid_at', [$startOfYear, Carbon::now()->endOfYear()])->whereNotNull('paid_at')->when($storeId, fn($q) => $q->where('outlet_id', $storeId))->select(DB::raw('EXTRACT(MONTH FROM paid_at) as month'), DB::raw('SUM(total_amount) as total'))->groupBy('month')->pluck('total', 'month')->all();
            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            $chartBulanan = ['labels' => [], 'offline' => [], 'online' => []];
            for ($i = 1; $i <= 12; $i++) { $chartBulanan['labels'][] = $months[$i-1]; $chartBulanan['offline'][] = (float)($mTrxData[$i] ?? 0); $chartBulanan['online'][] = (float)($mPOData[$i] ?? 0); }

            $yearFrom = $request->get('year_from', Carbon::now()->year - 4);
            $yearTo = $request->get('year_to', Carbon::now()->year);
            $yTrxData = Transaction::whereYear('tanggal', '>=', $yearFrom)->whereYear('tanggal', '<=', $yearTo)->when($storeId, fn($q) => $q->where('store_id', $storeId))->select(DB::raw('EXTRACT(YEAR FROM tanggal) as year'), DB::raw('SUM(total) as total'))->groupBy('year')->pluck('total', 'year')->all();
            $yPOData = PaymentOrder::whereYear('paid_at', '>=', $yearFrom)->whereYear('paid_at', '<=', $yearTo)->whereNotNull('paid_at')->when($storeId, fn($q) => $q->where('outlet_id', $storeId))->select(DB::raw('EXTRACT(YEAR FROM paid_at) as year'), DB::raw('SUM(total_amount) as total'))->groupBy('year')->pluck('total', 'year')->all();
            $chartTahunan = ['labels' => [], 'offline' => [], 'online' => []];
            for ($y = $yearFrom; $y <= $yearTo; $y++) { $chartTahunan['labels'][] = (string)$y; $chartTahunan['offline'][] = (float)($yTrxData[$y] ?? 0); $chartTahunan['online'][] = (float)($yPOData[$y] ?? 0); }

            // 2. CASHFLOW DATA PRESETS
            $cfHarian = $this->getCashFlowData($today, $today, $storeId);
            $cfMingguan = $this->getCashFlowData($startOfWeek, Carbon::now()->endOfWeek(Carbon::SUNDAY), $storeId);
            $cfBulanan = $this->getCashFlowData($startOfYear, Carbon::now()->endOfYear(), $storeId);
            $cfTahunan = $this->getCashFlowData(Carbon::now()->subYears(4)->startOfYear(), Carbon::now()->endOfYear(), $storeId);

            // 3. OTHER WIDGETS
            $totalPiutang = Debt::where('tipe', 'piutang')->where('sisa', '>', 0)->when($storeId, fn($q) => $q->where('store_id', $storeId))->sum('sisa');
            $totalHutang = Debt::whereIn('tipe', ['utang', 'hutang'])->where('sisa', '>', 0)->when($storeId, fn($q) => $q->where('store_id', $storeId))->sum('sisa');

            $lowStockProducts = ProductStore::with(['product', 'store'])->where('stok', '<=', DB::raw('COALESCE(stok_minimum, 10)'))->when($storeId, fn($q) => $q->where('store_id', $storeId))->orderBy('stok', 'asc')->get();
            $expiredProducts = ProductStore::with(['product', 'store'])->whereNotNull('kadaluarsa')->where('kadaluarsa', '<=', Carbon::now()->addDays(30))->when($storeId, fn($q) => $q->where('store_id', $storeId))->orderBy('kadaluarsa', 'asc')->get();
            $topProducts = TransactionDetail::select('product_id', DB::raw('SUM(jmlh) as total_qty'), DB::raw('SUM(jmlh * harga_jual) as total_revenue'))->whereHas('transaction', fn($q) => $q->whereDate('tanggal', $today)->when($storeId, fn($q2) => $q2->where('store_id', $storeId)))->groupBy('product_id')->orderBy('total_qty', 'desc')->with('product')->take(5)->get();

            $combinedActivities = collect();
            Transaction::with('user')->whereDate('tanggal', $today)->when($storeId, fn($q) => $q->where('store_id', $storeId))->get()->each(fn($trx) => $combinedActivities->push(['user' => $trx->user->name ?? 'Guest', 'role' => 'Offline', 'action' => 'melakukan pembelian kasir', 'time' => Carbon::parse($trx->tanggal)->format('H:i'), 'timestamp' => Carbon::parse($trx->tanggal), 'icon' => 'solar:cart-large-minimalistic-bold']));
            PaymentOrder::with('user')->whereDate('paid_at', $today)->whereNotNull('paid_at')->when($storeId, fn($q) => $q->where('outlet_id', $storeId))->get()->each(fn($po) => $combinedActivities->push(['user' => $po->user->name ?? $po->recipient_name, 'role' => 'Online', 'action' => 'melakukan pembelian web', 'time' => Carbon::parse($po->paid_at)->format('H:i'), 'timestamp' => Carbon::parse($po->paid_at), 'icon' => 'solar:global-bold']));
            $activities = $combinedActivities->sortByDesc('timestamp')->take(5);

            return view('dashboard', array_merge($stats, [
                'chartHarian' => $chartHarian, 'chartMingguan' => $chartMingguan, 'chartBulanan' => $chartBulanan, 'chartTahunan' => $chartTahunan,
                'cfHarian' => $cfHarian, 'cfMingguan' => $cfMingguan, 'cfBulanan' => $cfBulanan, 'cfTahunan' => $cfTahunan,
                'totalPiutang' => $totalPiutang, 'totalHutang' => $totalHutang,
                'lowStockProducts' => $lowStockProducts, 'expiredProducts' => $expiredProducts, 'topProducts' => $topProducts, 'activities' => $activities,
                'stores' => $stores, 'currentStoreId' => $storeId, 'title' => 'Dashboard Owner'
            ]));
        } 
        return view('dashboard', ['title' => 'Dashboard']);
    }

    private function getCashFlowData($start, $end, $storeId)
    {
        $pemasukan = CashFlow::where('jenis', 'pemasukan')->whereBetween('tanggal', [$start, $end])->when($storeId, fn($q) => $q->where('store_id', $storeId));
        $pengeluaran = CashFlow::where('jenis', 'pengeluaran')->whereBetween('tanggal', [$start, $end])->when($storeId, fn($q) => $q->where('store_id', $storeId));
        $pData = $pemasukan->clone()->orderBy('tanggal', 'desc')->take(7)->pluck('nominal')->reverse()->values()->all();
        $eData = $pengeluaran->clone()->orderBy('tanggal', 'desc')->take(7)->pluck('nominal')->reverse()->values()->all();
        return ['total_pemasukan' => $pemasukan->sum('nominal'), 'total_pengeluaran' => $pengeluaran->sum('nominal'), 'p_series' => $pData ?: [0], 'e_series' => $eData ?: [0]];
    }

    private function getStats($storeId, $today, $yesterday)
    {
        $qTodayTrx = Transaction::whereDate('tanggal', $today)->when($storeId, fn($q) => $q->where('store_id', $storeId));
        $qPrevTrx = Transaction::whereDate('tanggal', $yesterday)->when($storeId, fn($q) => $q->where('store_id', $storeId));
        $qTodayPO = PaymentOrder::whereDate('paid_at', $today)->whereNotNull('paid_at')->when($storeId, fn($q) => $q->where('outlet_id', $storeId));
        $qPrevPO = PaymentOrder::whereDate('paid_at', $yesterday)->whereNotNull('paid_at')->when($storeId, fn($q) => $q->where('outlet_id', $storeId));

        $trxToday = $qTodayTrx->count() + $qTodayPO->count();
        $trxPrev = $qPrevTrx->count() + $qPrevPO->count();
        
        $revOfflineToday = (float)$qTodayTrx->sum('total');
        $revOfflinePrev = (float)$qPrevTrx->sum('total');
        $revOnlineToday = (float)$qTodayPO->sum('total_amount');
        $revOnlinePrev = (float)$qPrevPO->sum('total_amount');

        $soldToday = TransactionDetail::whereHas('transaction', fn($q) => $q->whereDate('tanggal', $today)->when($storeId, fn($q2) => $q2->where('store_id', $storeId)))->sum('jmlh') + $qTodayPO->sum('items_count');
        $soldPrev = TransactionDetail::whereHas('transaction', fn($q) => $q->whereDate('tanggal', $yesterday)->when($storeId, fn($q2) => $q2->where('store_id', $storeId)))->sum('jmlh') + $qPrevPO->sum('items_count');

        $cust = User::where(function($q) {
            $q->whereNull('operator_id')
              ->orWhereIn('operator_id', function($sq) {
                  $sq->select('uuid')->from('operator')->where('nama', 'User');
              });
        })->count();

        $revTotal = $revOfflineToday + $revOnlineToday;
        $revPrevTotal = $revOfflinePrev + $revOnlinePrev;

        return [
            'total_transaksi' => $trxToday, 'diff_transaksi' => $this->calculateDiff($trxToday, $trxPrev),
            'total_pendapatan' => (float)$revTotal, 'diff_pendapatan' => $this->calculateDiff($revTotal, $revPrevTotal),
            'rev_offline' => $revOfflineToday, 'diff_offline' => $this->calculateDiff($revOfflineToday, $revOfflinePrev),
            'rev_online' => $revOnlineToday, 'diff_online' => $this->calculateDiff($revOnlineToday, $revOnlinePrev),
            'total_produk_terjual' => (int)$soldToday, 'diff_produk_terjual' => $this->calculateDiff($soldToday, $soldPrev),
            'total_customers' => $cust,
        ];
    }

    private function calculateDiff($t, $y) { if ($y == 0) return $t > 0 ? 100 : 0; return round((($t - $y) / $y) * 100, 1); }
}
