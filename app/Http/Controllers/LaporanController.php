<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Outlet;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isOwner = $user->operator && $user->operator->role === 'owner';

        // Get active outlets
        $outlets = DB::table('store')
            ->where('status_aktif', 1)
            ->orderBy('nama', 'asc')
            ->get();

        // Set default store to Jember Pusat
        $jemberPusat = DB::table('store')
            ->where('status_aktif', 1)
            ->where('nama', 'like', '%Jember%Pusat%')
            ->first();

        $defaultStoreId = $jemberPusat?->uuid ?? $outlets->first()?->uuid;

        return view('laporan.index', [
            'title' => 'Laporan Keseluruhan',
            'outlets' => $outlets,
            'defaultStoreId' => $defaultStoreId,
            'isOwner' => $isOwner,
        ]);
    }

    // Daily Summary API
    public function dailySummary(Request $request)
    {
        $store = $request->query('store_id');
        $date = $request->query('date', date('Y-m-d'));
        $paidStatuses = ['paid', 'settlement', 'success', 'capture'];

        $hppSubQuery = DB::table('transaction_detail')
            ->select('transaction_id', DB::raw('COALESCE(SUM(harga_modal * jmlh), 0) as total_hpp'))
            ->groupBy('transaction_id');

        $offlineOmsetQuery = DB::table('transactions')
            ->where('jenis', 'penjualan')
            ->whereDate('tanggal', $date);

        if ($store !== null && $store !== '') {
            $offlineOmsetQuery->where('store_id', $store);
        }

        $offlineOmset = (float) $offlineOmsetQuery->sum('total');

        $onlineOmsetQuery = DB::table('payment_orders')
            ->whereDate('created_at', $date)
            ->whereIn('payment_status', $paidStatuses);

        if ($store !== null && $store !== '') {
            $onlineOmsetQuery->whereRaw('CAST(outlet_id AS TEXT) = ?', [$store]);
        }

        $onlineOmset = (float) $onlineOmsetQuery->sum('total_amount');

        $offlineHppQuery = DB::table('transactions as t')
            ->leftJoinSub($hppSubQuery, 'hpp', function ($join) {
                $join->on('hpp.transaction_id', '=', 't.uuid');
            })
            ->where('t.jenis', 'penjualan')
            ->whereDate('t.tanggal', $date);

        if ($store !== null && $store !== '') {
            $offlineHppQuery->where('t.store_id', $store);
        }

        $offlineHpp = (float) ($offlineHppQuery->selectRaw('COALESCE(SUM(hpp.total_hpp), 0) as total_hpp')->value('total_hpp') ?? 0);

        $pembelian = (float) DB::table('transactions')
            ->where('jenis', 'pembelian')
            ->whereDate('tanggal', $date)
            ->when($store !== null && $store !== '', function ($query) use ($store) {
                $query->where('store_id', $store);
            })
            ->sum('total');

        $retur = (float) DB::table('transactions')
            ->where('jenis', 'retur')
            ->whereDate('tanggal', $date)
            ->when($store !== null && $store !== '', function ($query) use ($store) {
                $query->where('store_id', $store);
            })
            ->sum('total');

        $rugi = (float) DB::table('transactions')
            ->where('jenis', 'rugi')
            ->whereDate('tanggal', $date)
            ->when($store !== null && $store !== '', function ($query) use ($store) {
                $query->where('store_id', $store);
            })
            ->sum('total');

        $transfer = (float) DB::table('transactions')
            ->where('jenis', 'transfer')
            ->whereDate('tanggal', $date)
            ->when($store !== null && $store !== '', function ($query) use ($store) {
                $query->where('store_id', $store);
            })
            ->sum('total');

        $pemasukanCashFlow = (float) DB::table('cash_flows')
            ->where('jenis', 'pemasukan')
            ->whereDate('tanggal', $date)
            ->when($store !== null && $store !== '', function ($query) use ($store) {
                $query->where('store_id', $store);
            })
            ->sum('nominal');

        $pemasukanPiutang = (float) DB::table('detail_debts as dd')
            ->join('debts as d', 'd.uuid', '=', 'dd.debts_id')
            ->where('d.tipe', 'piutang')
            ->whereDate('dd.tanggal', $date)
            ->when($store !== null && $store !== '', function ($query) use ($store) {
                $query->where('d.store_id', $store);
            })
            ->sum('dd.bayar');

        $pengeluaranCashFlow = (float) DB::table('cash_flows')
            ->where('jenis', 'pengeluaran')
            ->whereDate('tanggal', $date)
            ->when($store !== null && $store !== '', function ($query) use ($store) {
                $query->where('store_id', $store);
            })
            ->sum('nominal');

        $pengeluaranUtang = (float) DB::table('detail_debts as dd')
            ->join('debts as d', 'd.uuid', '=', 'dd.debts_id')
            ->where('d.tipe', 'utang')
            ->whereDate('dd.tanggal', $date)
            ->when($store !== null && $store !== '', function ($query) use ($store) {
                $query->where('d.store_id', $store);
            })
            ->sum('dd.bayar');

        $omset = $offlineOmset + $onlineOmset;
        $hpp = $offlineHpp;
        $laba_kotor = (float) $omset - (float) $hpp;
        $pemasukan = $pemasukanCashFlow + $pemasukanPiutang;
        $pengeluaran = $pengeluaranCashFlow + $pengeluaranUtang;

        return response()->json([
            'omset' => $omset,
            'hpp' => $hpp,
            'laba_kotor' => $laba_kotor,
            'pemasukan' => $pemasukan,
            'pengeluaran' => $pengeluaran,
            'omset_online' => $onlineOmset,
            'pembelian' => $pembelian,
            'retur' => $retur,
            'rugi' => $rugi,
            'transfer' => $transfer,
        ]);
    }

    // Daily Operators API
    public function dailyOperators(Request $request)
    {
        $store = $request->query('store_id');
        $date = $request->query('date', date('Y-m-d'));
        $transactionStoreClause = $store !== null && $store !== '' ? ' AND t.store_id = ?' : '';
        $cashFlowStoreClause = $store !== null && $store !== '' ? ' AND cf.store_id = ?' : '';
        $debtStoreClause = $store !== null && $store !== '' ? ' AND d.store_id = ?' : '';

        $sql = <<<SQL
                        SELECT nama_operator, jenis, SUM(total) AS total
            FROM (
                SELECT
                    COALESCE(u.username, 'Unknown') AS nama_operator,
                                        t.jenis AS jenis,
                                        COALESCE(SUM(CASE WHEN t.kembalian < 0 THEN 0 ELSE t.total END), 0) AS total
                FROM transactions t
                LEFT JOIN users u ON u.uuid = t.user_id
                WHERE date(t.tanggal) = ?
                                    AND t.jenis IN ('penjualan', 'pembelian', 'retur', 'rugi', 'transfer')
                  {$transactionStoreClause}
                                GROUP BY t.user_id, u.username, t.jenis

                UNION ALL

                SELECT
                    COALESCE(u.username, 'Unknown') AS nama_operator,
                                        cf.jenis AS jenis,
                                        COALESCE(SUM(cf.nominal), 0) AS total
                FROM cash_flows cf
                LEFT JOIN users u ON u.uuid = cf.user_id
                WHERE date(cf.tanggal) = ?
                                    AND cf.jenis IN ('pemasukan', 'pengeluaran')
                  {$cashFlowStoreClause}
                                GROUP BY cf.user_id, u.username, cf.jenis

                UNION ALL

                SELECT
                    COALESCE(u.username, 'Unknown') AS nama_operator,
                                        'pelunasan_piutang' AS jenis,
                                        COALESCE(SUM(dd.bayar), 0) AS total
                FROM detail_debts dd
                INNER JOIN debts d ON d.uuid = dd.debts_id AND d.tipe = 'piutang'
                LEFT JOIN users u ON u.uuid = dd.user_id
                WHERE date(dd.tanggal) = ?
                  {$debtStoreClause}
                GROUP BY dd.user_id, u.username

                UNION ALL

                SELECT
                    COALESCE(u.username, 'Unknown') AS nama_operator,
                                        'pembayaran_hutang' AS jenis,
                                        COALESCE(SUM(dd.bayar), 0) AS total
                FROM detail_debts dd
                INNER JOIN debts d ON d.uuid = dd.debts_id AND d.tipe = 'utang'
                LEFT JOIN users u ON u.uuid = dd.user_id
                WHERE date(dd.tanggal) = ?
                  {$debtStoreClause}
                GROUP BY dd.user_id, u.username
            ) aggregated
                        GROUP BY nama_operator, jenis
                        ORDER BY nama_operator ASC, jenis ASC
        SQL;

        $bindings = [$date];
        if ($store !== null && $store !== '') {
            $bindings[] = $store;
        }

        $bindings[] = $date;
        if ($store !== null && $store !== '') {
            $bindings[] = $store;
        }

        $bindings[] = $date;
        if ($store !== null && $store !== '') {
            $bindings[] = $store;
        }

        $bindings[] = $date;
        if ($store !== null && $store !== '') {
            $bindings[] = $store;
        }

        $rows = DB::select($sql, $bindings);

        $data = array_map(function ($row) {
            return [
                'name' => $row->nama_operator ?? 'Unknown',
                'jenis' => $row->jenis ?? '',
                'total' => (float) ($row->total ?? 0),
            ];
        }, $rows);

        return response()->json([
            'operators' => $data
        ]);
    }

    public function dailyCashbox(Request $request)
    {
        $store = $request->query('store_id');
        $date = $request->query('date', date('Y-m-d'));

        $transactionStoreCondition = $store !== null && $store !== '' ? 'WHERE store_id = ? AND date(tanggal) = ? AND jenis IN (\'penjualan\', \'pembelian\', \'transfer\')' : 'WHERE date(tanggal) = ? AND jenis IN (\'penjualan\', \'pembelian\', \'transfer\')';
        $transactionBindings = $store !== null && $store !== '' ? [$store, $date] : [$date];

        $debtStoreCondition = $store !== null && $store !== '' ? 'WHERE d.store_id = ? AND date(dd.tanggal) = ?' : 'WHERE date(dd.tanggal) = ?';
        $debtBindings = $store !== null && $store !== '' ? [$store, $date] : [$date];

        $cashFlowStoreCondition = $store !== null && $store !== '' ? 'WHERE store_id = ? AND date(tanggal) = ?' : 'WHERE date(tanggal) = ?';
        $cashFlowBindings = $store !== null && $store !== '' ? [$store, $date] : [$date];

        $paymentOrderCondition = $store !== null && $store !== ''
            ? 'WHERE CAST(outlet_id AS TEXT) = ? AND date(created_at) = ? AND payment_status IN (\'paid\', \'settlement\', \'success\', \'capture\')'
            : 'WHERE date(created_at) = ? AND payment_status IN (\'paid\', \'settlement\', \'success\', \'capture\')';
        $paymentOrderBindings = $store !== null && $store !== '' ? [$store, $date] : [$date];

        $sql = <<<'SQL'
            SELECT nama_metode, total
            FROM (
                SELECT pm.nama_metode AS nama_metode, COALESCE(SUM(unioned.amount), 0) AS total
                FROM payment_methods pm
                LEFT JOIN (
                    SELECT metode_pembayaran, CASE jenis
                        WHEN 'pembelian' THEN -(CASE WHEN kembalian < 0 THEN 0 ELSE total END)
                        ELSE (CASE WHEN kembalian < 0 THEN 0 ELSE total END)
                    END AS amount
                    FROM transactions
                    %TRANSACTION_WHERE%

                    UNION ALL

                    SELECT dd.metode_pembayaran, CASE d.tipe
                        WHEN 'piutang' THEN dd.bayar
                        WHEN 'utang' THEN -dd.bayar
                        ELSE 0
                    END AS amount
                    FROM detail_debts dd
                    INNER JOIN debts d ON d.uuid = dd.debts_id
                    %DEBT_WHERE%

                    UNION ALL

                    SELECT metode_pembayaran, CASE jenis
                        WHEN 'pemasukan' THEN nominal
                        WHEN 'pengeluaran' THEN -nominal
                        ELSE 0
                    END AS amount
                    FROM cash_flows
                    %CASHFLOW_WHERE%
                ) unioned ON unioned.metode_pembayaran = pm.uuid
                GROUP BY pm.uuid, pm.nama_metode

                UNION ALL

                SELECT 'Online' AS nama_metode, COALESCE(SUM(total_amount), 0) AS total
                FROM payment_orders
                %PAYMENT_ORDER_WHERE%
            ) cashbox
            ORDER BY total DESC
        SQL;

        $sql = str_replace(
            ['%TRANSACTION_WHERE%', '%DEBT_WHERE%', '%CASHFLOW_WHERE%', '%PAYMENT_ORDER_WHERE%'],
            [$transactionStoreCondition, $debtStoreCondition, $cashFlowStoreCondition, $paymentOrderCondition],
            $sql
        );

        $items = DB::select(
            $sql,
            array_merge($transactionBindings, $debtBindings, $cashFlowBindings, $paymentOrderBindings)
        );

        $data = array_map(function ($row) {
            return [
                'nama_metode' => $row->nama_metode,
                'total' => (float) $row->total,
            ];
        }, $items);

        return response()->json(['items' => $data]);
    }

    public function dailyOnlineTransactions(Request $request)
    {
        $store = $request->query('store_id');
        $date = $request->query('date', date('Y-m-d'));

        $ordersQuery = DB::table('payment_orders')
            ->whereDate('created_at', $date)
            ->whereIn('payment_status', ['paid', 'settlement', 'success', 'capture'])
            ->orderByDesc('created_at');

        if ($store !== null && $store !== '') {
            $ordersQuery->whereRaw('CAST(outlet_id AS TEXT) = ?', [$store]);
        }

        $orders = $ordersQuery->get([
            DB::raw('CAST(id AS TEXT) as id'),
            DB::raw('total_amount as total'),
            DB::raw('midtrans_payment_type as gateway'),
            DB::raw('payment_status as status'),
            DB::raw('recipient_name as customer'),
            DB::raw('created_at as tanggal'),
        ]);

        return response()->json(['orders' => $orders]);
    }

    public function monthlySummary(Request $request)
    {
        $store = $request->query('store_id');
        $month = (int) $request->query('month', date('m'));
        $year = (int) $request->query('year', date('Y'));
        $paidStatuses = ['paid', 'settlement', 'success', 'capture'];

        $hppSubQuery = DB::table('transaction_detail')
            ->select('transaction_id', DB::raw('COALESCE(SUM(harga_modal * jmlh), 0) as total_hpp'))
            ->groupBy('transaction_id');

        $onlineHppSubQuery = DB::table('payment_order_items as poi')
            ->leftJoin('products as p', DB::raw('CAST(p.uuid AS TEXT)'), '=', 'poi.product_id')
            ->select('poi.payment_order_id', DB::raw('COALESCE(SUM(COALESCE(p.harga_modal, 0) * poi.quantity), 0) as total_hpp'))
            ->groupBy('poi.payment_order_id');

        $omsetQuery = DB::table('transactions')
            ->where('jenis', 'penjualan')
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year);

        if ($store !== null && $store !== '') {
            $omsetQuery->where('store_id', $store);
        }

        $omset = (float) $omsetQuery->sum('total');

        $onlineOmsetQuery = DB::table('payment_orders')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->whereIn('payment_status', $paidStatuses);

        if ($store !== null && $store !== '') {
            $onlineOmsetQuery->whereRaw('CAST(outlet_id AS TEXT) = ?', [$store]);
        }

        $onlineOmset = (float) $onlineOmsetQuery->sum('total_amount');

        $hppQuery = DB::table('transactions as t')
            ->leftJoinSub($hppSubQuery, 'hpp', function ($join) {
                $join->on('hpp.transaction_id', '=', 't.uuid');
            })
            ->where('t.jenis', 'penjualan')
            ->whereMonth('t.tanggal', $month)
            ->whereYear('t.tanggal', $year);

        if ($store !== null && $store !== '') {
            $hppQuery->where('t.store_id', $store);
        }

        $hpp = (float) ($hppQuery->selectRaw('COALESCE(SUM(hpp.total_hpp), 0) as total_hpp')->value('total_hpp') ?? 0);

        $onlineHppQuery = DB::table('payment_orders as po')
            ->leftJoinSub($onlineHppSubQuery, 'hpp', function ($join) {
                $join->on('hpp.payment_order_id', '=', 'po.id');
            })
            ->whereMonth('po.created_at', $month)
            ->whereYear('po.created_at', $year)
            ->whereIn('po.payment_status', $paidStatuses);

        if ($store !== null && $store !== '') {
            $onlineHppQuery->whereRaw('CAST(po.outlet_id AS TEXT) = ?', [$store]);
        }

        $onlineHpp = (float) ($onlineHppQuery->selectRaw('COALESCE(SUM(hpp.total_hpp), 0) as total_hpp')->value('total_hpp') ?? 0);

        $cashFlowQuery = DB::table('cash_flows')
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->selectRaw("COALESCE(SUM(CASE WHEN jenis = 'pemasukan' THEN nominal ELSE 0 END), 0) as pemasukan")
            ->selectRaw("COALESCE(SUM(CASE WHEN jenis = 'pengeluaran' THEN nominal ELSE 0 END), 0) as pengeluaran");

        if ($store !== null && $store !== '') {
            $cashFlowQuery->where('store_id', $store);
        }

        $cashFlows = $cashFlowQuery->first();

        $piutangQuery = DB::table('detail_debts as dd')
            ->join('debts as d', 'd.uuid', '=', 'dd.debts_id')
            ->where('d.tipe', 'piutang')
            ->whereMonth('dd.tanggal', $month)
            ->whereYear('dd.tanggal', $year);

        if ($store !== null && $store !== '') {
            $piutangQuery->where('d.store_id', $store);
        }

        $piutang = (float) $piutangQuery->sum('dd.bayar');

        $utangQuery = DB::table('detail_debts as dd')
            ->join('debts as d', 'd.uuid', '=', 'dd.debts_id')
            ->where('d.tipe', 'utang')
            ->whereMonth('dd.tanggal', $month)
            ->whereYear('dd.tanggal', $year);

        if ($store !== null && $store !== '') {
            $utangQuery->where('d.store_id', $store);
        }

        $utang = (float) $utangQuery->sum('dd.bayar');

        $rugiQuery = DB::table('transactions')
            ->where('jenis', 'rugi')
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year);

        if ($store !== null && $store !== '') {
            $rugiQuery->where('store_id', $store);
        }

        $rugi = (float) $rugiQuery->sum('total');

        $pemasukan = (float) ($cashFlows->pemasukan ?? 0) + $piutang;
        $pengeluaran = (float) ($cashFlows->pengeluaran ?? 0) + $utang;
        $hppTotal = $hpp + $onlineHpp;
        $omsetTotal = $omset + $onlineOmset;
        $labaKotor = $omsetTotal - $hppTotal;
        $labaBersih = ($labaKotor + $pemasukan) - ($pengeluaran + $rugi);

        $data = [
            'omset' => $omset,
            'penjualan_online' => $onlineOmset,
            'hpp' => $hppTotal,
            'laba_kotor' => $labaKotor,
            'laba_bersih' => $labaBersih,
            'pemasukan' => $pemasukan,
            'pengeluaran' => $pengeluaran,
            'rugi' => $rugi,
        ];

        return response()->json($data);
    }

    public function monthlyOperators(Request $request)
    {
        $store = $request->query('store_id');
        $month = (int) $request->query('month', date('m'));
        $year = (int) $request->query('year', date('Y'));

        $storeClause = $store !== null && $store !== '' ? ' AND store_id = ?' : '';
        $outletClause = $store !== null && $store !== '' ? ' AND CAST(po.outlet_id AS TEXT) = ?' : '';

        $sql = <<<SQL
            SELECT nama_operator, SUM(masuk) AS masuk, SUM(keluar) AS keluar
            FROM (
                SELECT COALESCE(o.nama, u.username, 'Tidak Diketahui') AS nama_operator, COALESCE(SUM(t.total), 0) AS masuk, 0 AS keluar
                FROM transactions t
                LEFT JOIN users u ON u.uuid = t.user_id
                LEFT JOIN operator o ON o.uuid = u.operator_id
                WHERE t.jenis = 'penjualan'
                  AND EXTRACT(MONTH FROM t.tanggal) = ?
                  AND EXTRACT(YEAR FROM t.tanggal) = ?
                  {$storeClause}
                GROUP BY t.user_id, COALESCE(o.nama, u.username, 'Tidak Diketahui')

                UNION ALL

                SELECT 'Online' AS nama_operator, COALESCE(SUM(po.total_amount), 0) AS masuk, 0 AS keluar
                FROM payment_orders po
                WHERE EXTRACT(MONTH FROM po.created_at) = ?
                  AND EXTRACT(YEAR FROM po.created_at) = ?
                  AND po.payment_status IN ('paid', 'settlement', 'success', 'capture')
                  {$outletClause}

                UNION ALL

                SELECT COALESCE(o.nama, u.username, 'Tidak Diketahui') AS nama_operator, COALESCE(SUM(cf.nominal), 0) AS masuk, 0 AS keluar
                FROM cash_flows cf
                LEFT JOIN users u ON u.uuid = cf.user_id
                LEFT JOIN operator o ON o.uuid = u.operator_id
                WHERE cf.jenis = 'pemasukan'
                  AND EXTRACT(MONTH FROM cf.tanggal) = ?
                  AND EXTRACT(YEAR FROM cf.tanggal) = ?
                  {$storeClause}
                GROUP BY cf.user_id, COALESCE(o.nama, u.username, 'Tidak Diketahui')

                UNION ALL

                SELECT COALESCE(o.nama, u.username, 'Tidak Diketahui') AS nama_operator, 0 AS masuk, COALESCE(SUM(t.total), 0) AS keluar
                FROM transactions t
                LEFT JOIN users u ON u.uuid = t.user_id
                LEFT JOIN operator o ON o.uuid = u.operator_id
                WHERE t.jenis IN ('pembelian', 'retur', 'rugi')
                  AND EXTRACT(MONTH FROM t.tanggal) = ?
                  AND EXTRACT(YEAR FROM t.tanggal) = ?
                  {$storeClause}
                GROUP BY t.user_id, COALESCE(o.nama, u.username, 'Tidak Diketahui')

                UNION ALL

                SELECT COALESCE(o.nama, u.username, 'Tidak Diketahui') AS nama_operator, 0 AS masuk, COALESCE(SUM(cf.nominal), 0) AS keluar
                FROM cash_flows cf
                LEFT JOIN users u ON u.uuid = cf.user_id
                LEFT JOIN operator o ON o.uuid = u.operator_id
                WHERE cf.jenis = 'pengeluaran'
                  AND EXTRACT(MONTH FROM cf.tanggal) = ?
                  AND EXTRACT(YEAR FROM cf.tanggal) = ?
                  {$storeClause}
                GROUP BY cf.user_id, COALESCE(o.nama, u.username, 'Tidak Diketahui')

                UNION ALL

                SELECT COALESCE(o.nama, u.username, 'Tidak Diketahui') AS nama_operator, COALESCE(SUM(dd.bayar), 0) AS masuk, 0 AS keluar
                FROM detail_debts dd
                INNER JOIN debts d ON d.uuid = dd.debts_id AND d.tipe = 'piutang'
                LEFT JOIN users u ON u.uuid = dd.user_id
                LEFT JOIN operator o ON o.uuid = u.operator_id
                WHERE EXTRACT(MONTH FROM dd.tanggal) = ?
                  AND EXTRACT(YEAR FROM dd.tanggal) = ?
                  {$storeClause}
                GROUP BY dd.user_id, COALESCE(o.nama, u.username, 'Tidak Diketahui')

                UNION ALL

                SELECT COALESCE(o.nama, u.username, 'Tidak Diketahui') AS nama_operator, 0 AS masuk, COALESCE(SUM(dd.bayar), 0) AS keluar
                FROM detail_debts dd
                INNER JOIN debts d ON d.uuid = dd.debts_id AND d.tipe = 'utang'
                LEFT JOIN users u ON u.uuid = dd.user_id
                LEFT JOIN operator o ON o.uuid = u.operator_id
                WHERE EXTRACT(MONTH FROM dd.tanggal) = ?
                  AND EXTRACT(YEAR FROM dd.tanggal) = ?
                  {$storeClause}
                GROUP BY dd.user_id, COALESCE(o.nama, u.username, 'Tidak Diketahui')
            ) aggregated
            GROUP BY nama_operator
            ORDER BY masuk DESC, keluar DESC, nama_operator ASC
        SQL;

        $bindings = [$month, $year];

        if ($store !== null && $store !== '') {
            $bindings[] = $store;
        }

        $bindings[] = $month;
        $bindings[] = $year;

        if ($store !== null && $store !== '') {
            $bindings[] = $store;
        }

        $bindings[] = $month;
        $bindings[] = $year;

        if ($store !== null && $store !== '') {
            $bindings[] = $store;
        }

        $bindings[] = $month;
        $bindings[] = $year;

        if ($store !== null && $store !== '') {
            $bindings[] = $store;
        }

        $bindings[] = $month;
        $bindings[] = $year;

        if ($store !== null && $store !== '') {
            $bindings[] = $store;
        }

        $bindings[] = $month;
        $bindings[] = $year;

        if ($store !== null && $store !== '') {
            $bindings[] = $store;
        }

        $bindings[] = $month;
        $bindings[] = $year;

        if ($store !== null && $store !== '') {
            $bindings[] = $store;
        }

        $result = DB::select($sql, $bindings);

        $operators = array_map(function ($row) {
            return [
                'name' => $row->nama_operator,
                'masuk' => (float) $row->masuk,
                'keluar' => (float) $row->keluar,
            ];
        }, $result);

        return response()->json(['operators' => $operators]);
    }

    public function monthlyDebtSummary(Request $request)
    {
        $store = $request->query('store_id');

        $query = DB::table('debts')
            ->selectRaw('tipe, COALESCE(SUM(sisa), 0) as total_belum_lunas')
            ->where('sisa', '>', 0)
            ->groupBy('tipe');

        if ($store !== null && $store !== '') {
            $query->where('store_id', $store);
        }

        return response()->json(['items' => $query->get()]);
    }

    public function monthlyDaily(Request $request)
    {
        $store = $request->query('store_id');
        $month = (int) $request->query('month', date('m'));
        $year = (int) $request->query('year', date('Y'));
        $paidStatuses = ['paid', 'settlement', 'success', 'capture'];

        $hppSubQuery = DB::table('transaction_detail')
            ->select('transaction_id', DB::raw('COALESCE(SUM(harga_modal * jmlh), 0) as total_hpp'))
            ->groupBy('transaction_id');

        $onlineHppSubQuery = DB::table('payment_order_items as poi')
            ->leftJoin('products as p', DB::raw('CAST(p.uuid AS TEXT)'), '=', 'poi.product_id')
            ->select('poi.payment_order_id', DB::raw('COALESCE(SUM(COALESCE(p.harga_modal, 0) * poi.quantity), 0) as total_hpp'))
            ->groupBy('poi.payment_order_id');

        $dailyQuery = DB::table('transactions as t')
            ->leftJoinSub($hppSubQuery, 'hpp', function ($join) {
                $join->on('hpp.transaction_id', '=', 't.uuid');
            })
            ->whereMonth('t.tanggal', $month)
            ->whereYear('t.tanggal', $year)
            ->selectRaw('DATE(t.tanggal) as tanggal, t.jenis, COALESCE(SUM(t.total),0) as total, COALESCE(SUM(hpp.total_hpp),0) as total_hpp, COUNT(t.uuid) as frekuensi')
            ->groupBy(DB::raw('DATE(t.tanggal)'), 't.jenis')
            ->orderBy('tanggal');

        if ($store !== null && $store !== '') {
            $dailyQuery->where('t.store_id', $store);
        }

        $onlineQuery = DB::table('payment_orders as po')
            ->leftJoinSub($onlineHppSubQuery, 'hpp', function ($join) {
                $join->on('hpp.payment_order_id', '=', 'po.id');
            })
            ->whereMonth('po.created_at', $month)
            ->whereYear('po.created_at', $year)
            ->whereIn('po.payment_status', $paidStatuses)
            ->selectRaw("DATE(po.created_at) as tanggal, 'penjualan_online' as jenis, COALESCE(SUM(po.total_amount),0) as total, COALESCE(SUM(po.total_amount - COALESCE(hpp.total_hpp, 0)),0) as total_hpp, COUNT(po.id) as frekuensi")
            ->groupBy(DB::raw('DATE(po.created_at)'))
            ->orderBy('tanggal');

        if ($store !== null && $store !== '') {
            $onlineQuery->whereRaw('CAST(po.outlet_id AS TEXT) = ?', [$store]);
        }

        $dailyQuery = $dailyQuery->unionAll($onlineQuery);

        $result = $dailyQuery->get();

        $daily = array_map(function ($row) {
            return [
                'jenis' => $row->jenis,
                'tanggal' => $row->tanggal,
                'total' => (float) $row->total,
                'laba' => (float) ((float) $row->total - (float) ($row->total_hpp ?? 0)),
                'frekuensi' => (int) $row->frekuensi,
            ];
        }, $result->all());

        return response()->json(['daily' => $daily]);
    }

    public function annualSummary(Request $request)
    {
        $store = $request->query('store_id');
        $year = (int) $request->query('year', date('Y'));

        $hppSubQuery = DB::table('transaction_detail')
            ->select('transaction_id', DB::raw('COALESCE(SUM(harga_modal * jmlh), 0) as total_hpp'))
            ->groupBy('transaction_id');

        $omsetQuery = DB::table('transactions')
            ->where('jenis', 'penjualan')
            ->whereYear('tanggal', $year);

        if ($store !== null && $store !== '') {
            $omsetQuery->where('store_id', $store);
        }

        $omset = (float) $omsetQuery->sum('total');

        $hppQuery = DB::table('transactions as t')
            ->leftJoinSub($hppSubQuery, 'hpp', function ($join) {
                $join->on('hpp.transaction_id', '=', 't.uuid');
            })
            ->where('t.jenis', 'penjualan')
            ->whereYear('t.tanggal', $year);

        if ($store !== null && $store !== '') {
            $hppQuery->where('t.store_id', $store);
        }

        $hpp = (float) ($hppQuery->selectRaw('COALESCE(SUM(hpp.total_hpp), 0) as total_hpp')->value('total_hpp') ?? 0);

        $cashFlowQuery = DB::table('cash_flows')
            ->whereYear('tanggal', $year)
            ->selectRaw("COALESCE(SUM(CASE WHEN jenis = 'pemasukan' THEN nominal ELSE 0 END), 0) as pemasukan")
            ->selectRaw("COALESCE(SUM(CASE WHEN jenis = 'pengeluaran' THEN nominal ELSE 0 END), 0) as pengeluaran");

        if ($store !== null && $store !== '') {
            $cashFlowQuery->where('store_id', $store);
        }

        $cashFlows = $cashFlowQuery->first();

        $data = [
            'omset' => $omset,
            'hpp' => $hpp,
            'laba_kotor' => (float) $omset - (float) $hpp,
            'pemasukan' => (float) ($cashFlows->pemasukan ?? 0),
            'pengeluaran' => (float) ($cashFlows->pengeluaran ?? 0),
        ];

        return response()->json($data);
    }

    public function annualOperators(Request $request)
    {
        $store = $request->query('store_id');
        $year = (int) $request->query('year', date('Y'));

        $operatorsQuery = DB::table('transactions')
            ->leftJoin('users', 'transactions.user_id', '=', 'users.uuid')
            ->whereYear('transactions.tanggal', $year)
            ->selectRaw('COALESCE(users.username, \'Unknown\') as name, COALESCE(SUM(transactions.total),0) as masuk, 0 as keluar')
            ->groupBy('transactions.user_id', 'users.username');

        if ($store !== null && $store !== '') {
            $operatorsQuery->where('transactions.store_id', $store);
        }

        $result = $operatorsQuery->get();

        $operators = array_map(function ($row) {
            return [
                'name' => $row->name,
                'masuk' => (float) $row->masuk,
                'keluar' => (float) $row->keluar,
            ];
        }, $result->all());

        return response()->json(['operators' => $operators]);
    }

    public function annualMonthly(Request $request)
    {
        $store = $request->query('store_id');
        $year = (int) $request->query('year', date('Y'));

        $hppSubQuery = DB::table('transaction_detail')
            ->select('transaction_id', DB::raw('COALESCE(SUM(harga_modal * jmlh), 0) as total_hpp'))
            ->groupBy('transaction_id');

        $monthlyQuery = DB::table('transactions as t')
            ->leftJoinSub($hppSubQuery, 'hpp', function ($join) {
                $join->on('hpp.transaction_id', '=', 't.uuid');
            })
            ->whereYear('t.tanggal', $year)
            ->selectRaw('EXTRACT(MONTH FROM t.tanggal) as bulan, t.jenis, COALESCE(SUM(t.total),0) as total, COALESCE(SUM(hpp.total_hpp),0) as total_hpp, COUNT(t.uuid) as frekuensi')
            ->groupBy(DB::raw('EXTRACT(MONTH FROM t.tanggal)'), 't.jenis')
            ->orderBy('bulan');

        if ($store !== null && $store !== '') {
            $monthlyQuery->where('t.store_id', $store);
        }

        $result = $monthlyQuery->get();

        $monthly = array_map(function ($row) {
            return [
                'jenis' => $row->jenis,
                'bulan' => (int) $row->bulan,
                'total' => (float) $row->total,
                'laba' => (float) ((float) $row->total - (float) ($row->total_hpp ?? 0)),
                'frekuensi' => (int) $row->frekuensi,
            ];
        }, $result->all());

        return response()->json(['monthly' => $monthly]);
    }

    public function annualCashbox(Request $request)
    {
        $store = $request->query('store_id');
        $year = (int) $request->query('year', date('Y'));

        $transactionWhere = $store !== null && $store !== '' ? 'WHERE store_id = ? AND EXTRACT(YEAR FROM tanggal) = ? AND jenis IN (\'penjualan\', \'pembelian\', \'transfer\')' : 'WHERE EXTRACT(YEAR FROM tanggal) = ? AND jenis IN (\'penjualan\', \'pembelian\', \'transfer\')';
        $transactionBindings = $store !== null && $store !== '' ? [$store, $year] : [$year];

        $debtWhere = $store !== null && $store !== '' ? 'WHERE d.store_id = ? AND EXTRACT(YEAR FROM dd.tanggal) = ?' : 'WHERE EXTRACT(YEAR FROM dd.tanggal) = ?';
        $debtBindings = $store !== null && $store !== '' ? [$store, $year] : [$year];

        $cashFlowWhere = $store !== null && $store !== '' ? 'WHERE store_id = ? AND EXTRACT(YEAR FROM tanggal) = ?' : 'WHERE EXTRACT(YEAR FROM tanggal) = ?';
        $cashFlowBindings = $store !== null && $store !== '' ? [$store, $year] : [$year];

        $sql = <<<'SQL'
            SELECT pm.nama_metode AS nama_metode, COALESCE(SUM(unioned.amount), 0) AS total
            FROM payment_methods pm
            LEFT JOIN (
                SELECT metode_pembayaran, CASE jenis
                    WHEN 'pembelian' THEN -(CASE WHEN kembalian < 0 THEN 0 ELSE transactions.total END)
                    ELSE (CASE WHEN kembalian < 0 THEN 0 ELSE transactions.total END)
                END AS amount
                FROM transactions
                %TRANSACTION_WHERE%

                UNION ALL

                SELECT dd.metode_pembayaran, CASE d.tipe
                    WHEN 'piutang' THEN dd.bayar
                    WHEN 'utang' THEN -dd.bayar
                    ELSE 0
                END AS amount
                FROM detail_debts dd
                INNER JOIN debts d ON d.uuid = dd.debts_id
                %DEBT_WHERE%

                UNION ALL

                SELECT metode_pembayaran, CASE jenis
                    WHEN 'pemasukan' THEN nominal
                    WHEN 'pengeluaran' THEN -nominal
                    ELSE 0
                END AS amount
                FROM cash_flows
                %CASHFLOW_WHERE%
            ) unioned ON unioned.metode_pembayaran = pm.uuid
            GROUP BY pm.uuid, pm.nama_metode
            ORDER BY total DESC
        SQL;

        $sql = str_replace(
            ['%TRANSACTION_WHERE%', '%DEBT_WHERE%', '%CASHFLOW_WHERE%'],
            [$transactionWhere, $debtWhere, $cashFlowWhere],
            $sql
        );

        $items = DB::select($sql, array_merge($transactionBindings, $debtBindings, $cashFlowBindings));

        $data = array_map(function ($row) {
            return [
                'nama_metode' => $row->nama_metode,
                'total' => (float) $row->total,
            ];
        }, $items);

        return response()->json(['items' => $data]);
    }

    public function exportExcel(Request $request)
    {
        $tab = $request->query('active_tab', 'harian');
        $store = $request->query('store_id');

        [$title, $rows] = $this->buildExportRows($request, $tab, $store);
        $filename = 'Laporan_' . ucfirst($tab) . '_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        return response()->streamDownload(function () use ($title, $rows, $tab) {
            $output = fopen('php://output', 'w');
            fwrite($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($output, [$title]);
            fputcsv($output, []);

            foreach ($rows as $section) {
                fputcsv($output, [$section['title']]);
                fputcsv($output, $section['headers']);

                foreach ($section['rows'] as $row) {
                    fputcsv($output, $row);
                }

                fputcsv($output, []);
            }

            fclose($output);
        }, $filename, $headers);
    }

    public function exportPdf(Request $request)
    {
        $tab = $request->query('active_tab', 'harian');
        $store = $request->query('store_id');

        [$title, $rows, $meta] = $this->buildExportRowsForPdf($request, $tab, $store);

        $pdf = Pdf::loadView('laporan.export_pdf', [
            'title' => $title,
            'rows' => $rows,
            'meta' => $meta,
            'generatedAt' => now()->format('d F Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Laporan_' . ucfirst($tab) . '_' . date('Ymd_His') . '.pdf');
    }

    private function buildExportRows(Request $request, string $tab, ?string $store): array
    {
        [$title, $rows, $meta] = $this->buildExportRowsForPdf($request, $tab, $store);

        $sections = array_map(function ($section) {
            return [
                'title' => $section['title'],
                'headers' => $section['headers'],
                'rows' => $section['rows'],
            ];
        }, $rows);

        return [$title, $sections];
    }

    private function buildExportRowsForPdf(Request $request, string $tab, ?string $store): array
    {
        if ($tab === 'bulanan') {
            return $this->buildMonthlyExport($request, $store);
        }

        if ($tab === 'tahunan') {
            return $this->buildAnnualExport($request, $store);
        }

        return $this->buildDailyExport($request, $store);
    }

    private function buildDailyExport(Request $request, ?string $store): array
    {
        $date = $request->query('date', date('Y-m-d'));
        $title = 'Laporan Harian';
        $storeLabel = $this->resolveStoreLabel($store);

        $summary = $this->resolveDailySummary($date, $store);
        $operators = $this->resolveDailyOperators($date, $store);
        $cashbox = $this->resolveDailyCashbox($date, $store);
        $online = $this->resolveDailyOnline($date, $store);

        return [
            $title,
            [
                [
                    'title' => 'Ringkasan',
                    'headers' => ['Periode', 'Outlet', 'Omset', 'HPP', 'Laba Kotor', 'Pemasukan', 'Pengeluaran'],
                    'rows' => [
                        [
                            $date,
                            $storeLabel,
                            $this->formatExportCurrency($summary['omset']),
                            $this->formatExportCurrency($summary['hpp']),
                            $this->formatExportCurrency($summary['laba_kotor']),
                            $this->formatExportCurrency($summary['pemasukan']),
                            $this->formatExportCurrency($summary['pengeluaran']),
                        ]
                    ],
                ],
                [
                    'title' => 'Operator',
                    'headers' => ['Nama Operator', 'Penjualan', 'Pemasukan Kas', 'Pengeluaran Kas', 'Net Laci'],
                    'rows' => array_map(function ($row) {
                        return [
                            $row['name'],
                            $this->formatExportCurrency($row['penjualan']),
                            $this->formatExportCurrency($row['pemasukan']),
                            $this->formatExportCurrency($row['pengeluaran']),
                            $this->formatExportCurrency($row['net_laci']),
                        ];
                    }, $operators),
                ],
                [
                    'title' => 'Cashbox',
                    'headers' => ['Metode Pembayaran', 'Total'],
                    'rows' => array_map(function ($row) {
                        return [$row['nama_metode'], $this->formatExportCurrency($row['total'])];
                    }, $cashbox),
                ],
                [
                    'title' => 'Online',
                    'headers' => ['Customer', 'Gateway', 'Waktu', 'Total'],
                    'rows' => array_map(function ($row) {
                        return [$row['customer'], $row['gateway'], $row['tanggal'], $this->formatExportCurrency($row['total'])];
                    }, $online),
                ],
            ],
            [
                'store' => $storeLabel,
                'period' => $date,
            ]
        ];
    }

    private function buildMonthlyExport(Request $request, ?string $store): array
    {
        $month = (int) $request->query('month', date('m'));
        $year = (int) $request->query('year', date('Y'));
        $title = 'Laporan Bulanan';
        $storeLabel = $this->resolveStoreLabel($store);

        $summary = $this->resolveMonthlySummary($month, $year, $store);
        $operators = $this->resolveMonthlyOperators($month, $year, $store);
        $daily = $this->resolveMonthlyDaily($month, $year, $store);

        return [
            $title,
            [
                [
                    'title' => 'Ringkasan',
                    'headers' => ['Periode', 'Outlet', 'Omset', 'HPP', 'Laba Kotor', 'Pemasukan', 'Pengeluaran'],
                    'rows' => [
                        [
                            $month . '/' . $year,
                            $storeLabel,
                            $this->formatExportCurrency($summary['omset']),
                            $this->formatExportCurrency($summary['hpp']),
                            $this->formatExportCurrency($summary['laba_kotor']),
                            $this->formatExportCurrency($summary['pemasukan']),
                            $this->formatExportCurrency($summary['pengeluaran']),
                        ]
                    ],
                ],
                [
                    'title' => 'Operator',
                    'headers' => ['Nama Operator', 'Masuk', 'Keluar'],
                    'rows' => array_map(function ($row) {
                        return [
                            $row['name'],
                            $this->formatExportCurrency($row['masuk']),
                            $this->formatExportCurrency($row['keluar']),
                        ];
                    }, $operators),
                ],
                [
                    'title' => 'Rincian Harian',
                    'headers' => ['Tanggal', 'Jenis', 'Total', 'Laba', 'Frekuensi'],
                    'rows' => array_map(function ($row) {
                        return [
                            $row['tanggal'],
                            $row['jenis'],
                            $this->formatExportCurrency($row['total']),
                            $this->formatExportCurrency($row['laba']),
                            $row['frekuensi'],
                        ];
                    }, $daily),
                ],
            ],
            [
                'store' => $storeLabel,
                'period' => $month . '/' . $year,
            ]
        ];
    }

    private function buildAnnualExport(Request $request, ?string $store): array
    {
        $year = (int) $request->query('year', date('Y'));
        $title = 'Laporan Tahunan';
        $storeLabel = $this->resolveStoreLabel($store);

        $summary = $this->resolveAnnualSummary($year, $store);
        $operators = $this->resolveAnnualOperators($year, $store);
        $monthly = $this->resolveAnnualMonthly($year, $store);
        $cashbox = $this->resolveAnnualCashbox($year, $store);

        return [
            $title,
            [
                [
                    'title' => 'Ringkasan',
                    'headers' => ['Tahun', 'Outlet', 'Omset', 'HPP', 'Laba Kotor', 'Pemasukan', 'Pengeluaran'],
                    'rows' => [
                        [
                            (string) $year,
                            $storeLabel,
                            $this->formatExportCurrency($summary['omset']),
                            $this->formatExportCurrency($summary['hpp']),
                            $this->formatExportCurrency($summary['laba_kotor']),
                            $this->formatExportCurrency($summary['pemasukan']),
                            $this->formatExportCurrency($summary['pengeluaran']),
                        ]
                    ],
                ],
                [
                    'title' => 'Operator',
                    'headers' => ['Nama Operator', 'Masuk', 'Keluar'],
                    'rows' => array_map(function ($row) {
                        return [
                            $row['name'],
                            $this->formatExportCurrency($row['masuk']),
                            $this->formatExportCurrency($row['keluar']),
                        ];
                    }, $operators),
                ],
                [
                    'title' => 'Rincian Bulanan',
                    'headers' => ['Bulan', 'Jenis', 'Total', 'Laba', 'Frekuensi'],
                    'rows' => array_map(function ($row) {
                        return [
                            $row['bulan'],
                            $row['jenis'],
                            $this->formatExportCurrency($row['total']),
                            $this->formatExportCurrency($row['laba']),
                            $row['frekuensi'],
                        ];
                    }, $monthly),
                ],
                [
                    'title' => 'Cashbox',
                    'headers' => ['Metode Pembayaran', 'Total'],
                    'rows' => array_map(function ($row) {
                        return [$row['nama_metode'], $this->formatExportCurrency($row['total'])];
                    }, $cashbox),
                ],
            ],
            [
                'store' => $storeLabel,
                'period' => (string) $year,
            ]
        ];
    }

    private function resolveStoreLabel(?string $store): string
    {
        if (!$store) {
            return 'Semua Outlet';
        }

        $outlet = DB::table('store')->where('uuid', $store)->value('nama');

        return $outlet ?: 'Semua Outlet';
    }

    private function formatExportCurrency(float|int $value): string
    {
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }

    private function resolveDailySummary(string $date, ?string $store): array
    {
        $response = $this->dailySummary(new Request(['date' => $date, 'store_id' => $store]));
        return $response->getData(true);
    }

    private function resolveDailyOperators(string $date, ?string $store): array
    {
        $rows = $this->dailyOperators(new Request(['date' => $date, 'store_id' => $store]))->getData(true)['operators'] ?? [];
        $grouped = [];

        foreach ($rows as $row) {
            $name = $row['name'] ?? 'Unknown';
            $jenis = $row['jenis'] ?? '';
            $total = (float) ($row['total'] ?? 0);

            if (!isset($grouped[$name])) {
                $grouped[$name] = [
                    'name' => $name,
                    'penjualan' => 0.0,
                    'pembelian' => 0.0,
                    'transfer' => 0.0,
                    'pemasukan' => 0.0,
                    'pengeluaran' => 0.0,
                    'pelunasan_piutang' => 0.0,
                    'pembayaran_hutang' => 0.0,
                    'retur' => 0.0,
                    'rugi' => 0.0,
                    'net_laci' => 0.0,
                ];
            }

            if (array_key_exists($jenis, $grouped[$name])) {
                $grouped[$name][$jenis] += $total;
            }
        }

        foreach ($grouped as &$item) {
            $item['net_laci'] = ($item['penjualan'] + $item['transfer'] + $item['pemasukan'] + $item['pelunasan_piutang'])
                - ($item['pembelian'] + $item['pengeluaran'] + $item['pembayaran_hutang']);
        }
        unset($item);

        return array_values($grouped);
    }

    private function resolveDailyCashbox(string $date, ?string $store): array
    {
        return $this->dailyCashbox(new Request(['date' => $date, 'store_id' => $store]))->getData(true)['items'] ?? [];
    }

    private function resolveDailyOnline(string $date, ?string $store): array
    {
        return $this->dailyOnlineTransactions(new Request(['date' => $date, 'store_id' => $store]))->getData(true)['orders'] ?? [];
    }

    private function resolveMonthlySummary(int $month, int $year, ?string $store): array
    {
        return $this->monthlySummary(new Request(['month' => $month, 'year' => $year, 'store_id' => $store]))->getData(true);
    }

    private function resolveMonthlyOperators(int $month, int $year, ?string $store): array
    {
        return $this->monthlyOperators(new Request(['month' => $month, 'year' => $year, 'store_id' => $store]))->getData(true)['operators'] ?? [];
    }

    private function resolveMonthlyDaily(int $month, int $year, ?string $store): array
    {
        return $this->monthlyDaily(new Request(['month' => $month, 'year' => $year, 'store_id' => $store]))->getData(true)['daily'] ?? [];
    }

    private function resolveAnnualSummary(int $year, ?string $store): array
    {
        return $this->annualSummary(new Request(['year' => $year, 'store_id' => $store]))->getData(true);
    }

    private function resolveAnnualOperators(int $year, ?string $store): array
    {
        return $this->annualOperators(new Request(['year' => $year, 'store_id' => $store]))->getData(true)['operators'] ?? [];
    }

    private function resolveAnnualMonthly(int $year, ?string $store): array
    {
        return $this->annualMonthly(new Request(['year' => $year, 'store_id' => $store]))->getData(true)['monthly'] ?? [];
    }

    private function resolveAnnualCashbox(int $year, ?string $store): array
    {
        return $this->annualCashbox(new Request(['year' => $year, 'store_id' => $store]))->getData(true)['items'] ?? [];
    }
}
