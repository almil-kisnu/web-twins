<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Outlet;

class OutletController extends Controller
{
    public function index(Request $request)
    {
        $outlets = Outlet::with(['users.operator'])->get();
        
        // Fetch Performance Data per Outlet
        $performanceData = $outlets->map(function($outlet) {
            // Omset & Laba Kotor from Transactions (Hanya status 'Selesai' atau 'Disetujui')
            $sales = \App\Models\Transaction::where('store_id', $outlet->uuid)
                ->where('jenis', 'penjualan')
                ->whereIn('status', ['Selesai', 'selesai', 'Disetujui', 'disetujui'])
                ->with('details')
                ->get();

            
            $omset = $sales->sum('total');
            $volumeTransaksi = $sales->count();
            
            $labaKotor = $sales->flatMap->details->sum(function($detail) {
                return ($detail->harga_jual - $detail->harga_modal) * $detail->jmlh;
            });

            // Pemasukan & Pengeluaran from CashFlows
            $cashFlows = \App\Models\CashFlow::where('store_id', $outlet->uuid)->get();
            $pemasukan = $cashFlows->where('jenis', 'pemasukan')->sum('nominal');
            $pengeluaran = $cashFlows->where('jenis', 'pengeluaran')->sum('nominal');

            // Laba Bersih (Laba Kotor - Pengeluaran)
            $labaBersih = $labaKotor - $pengeluaran;

            // 1. Get POS Top Product (transaction_detail)
            $posSales = \App\Models\TransactionDetail::whereIn('transaction_id', $sales->pluck('uuid'))
                ->select('product_id', \DB::raw('SUM(jmlh) as qty'))
                ->groupBy('product_id')
                ->get()
                ->pluck('qty', 'product_id');

            // 2. Get Online Top Product (payment_order_items)
            $onlineSales = \App\Models\PaymentOrderItem::whereHas('paymentOrder', function($query) use ($outlet) {
                    $query->where('outlet_id', $outlet->uuid)
                          ->whereIn('payment_status', ['paid', 'settlement', 'success']);
                })
                ->select('product_id', \DB::raw('SUM(quantity) as qty'))
                ->groupBy('product_id')
                ->get()
                ->pluck('qty', 'product_id');

            // Combine both sources
            $allProductIds = $posSales->keys()->concat($onlineSales->keys())->unique();
            $mergedSales = $allProductIds->mapWithKeys(function($id) use ($posSales, $onlineSales) {
                return [$id => (float)($posSales->get($id, 0) + $onlineSales->get($id, 0))];
            })->sortDesc();

            $top3 = $mergedSales->take(3)->map(function($qty, $id) {
                $prod = \App\Models\Product::find($id);
                if (!$prod) return null;
                return [
                    'nama' => $prod->nama_produk,
                    'image' => $prod->resolved_image_url,
                    'qty' => $qty
                ];
            })->filter()->values()->toArray();


            // Nilai Aset Stok (Stok x Harga Modal)
            $nilaiAset = \App\Models\ProductStore::where('store_id', $outlet->uuid)
                ->get()
                ->sum(function($ps) {
                    return $ps->stok * ($ps->product->harga_modal ?? 0);
                });

            return [
                'outlet_uuid' => $outlet->uuid,
                'nama' => $outlet->nama,
                'omset' => $omset,
                'laba_kotor' => $labaKotor,
                'laba_bersih' => $labaBersih,
                'pemasukan' => $pemasukan,
                'pengeluaran' => $pengeluaran,
                'volume_transaksi' => $volumeTransaksi,
                'top_products' => $top3,
                'nilai_aset' => $nilaiAset
            ];
        });

        // Global Top Product (All Outlets) - Merged POS & Online
        $posSalesAll = \App\Models\TransactionDetail::whereHas('transaction', function($q) {
                $q->where('jenis', 'penjualan')
                  ->whereIn('status', ['Selesai', 'selesai', 'Disetujui', 'disetujui']);
            })
            ->select('product_id', \DB::raw('SUM(jmlh) as qty'))
            ->groupBy('product_id')
            ->get()->pluck('qty', 'product_id');


        $onlineSalesAll = \App\Models\PaymentOrderItem::whereHas('paymentOrder', function($q) {
                $q->whereIn('payment_status', ['paid', 'settlement', 'success']);
            })
            ->select('product_id', \DB::raw('SUM(quantity) as qty'))
            ->groupBy('product_id')
            ->get()->pluck('qty', 'product_id');

        $allIdsAll = $posSalesAll->keys()->concat($onlineSalesAll->keys())->unique();
        $mergedAll = $allIdsAll->mapWithKeys(function($id) use ($posSalesAll, $onlineSalesAll) {
            return [$id => (float)($posSalesAll->get($id, 0) + $onlineSalesAll->get($id, 0))];
        })->sortDesc();

        $top3All = $mergedAll->take(3)->map(function($qty, $id) {
            $prod = \App\Models\Product::find($id);
            if (!$prod) return null;
            return [
                'nama' => $prod->nama_produk,
                'image' => $prod->resolved_image_url,
                'qty' => $qty
            ];
        })->filter()->values()->toArray();


        $activeTab = $request->query('active_tab', 'data');

        // Stock History (Stock Card)
        $stockHistoryQuery = \App\Models\StockCard::with(['product', 'store'])
            ->orderBy('created_at', 'desc');

        if ($request->has('search') && $request->search != '') {
            $search = strtolower($request->search);
            $stockHistoryQuery->where(function($q) use ($search) {
                $q->whereHas('product', function($sq) use ($search) {
                    $sq->whereRaw('LOWER(nama_produk) LIKE ?', ["%{$search}%"])
                       ->orWhereRaw('LOWER(barcode) LIKE ?', ["%{$search}%"]);
                })->orWhereRaw('LOWER(keterangan) LIKE ?', ["%{$search}%"]);
            });
        }

        if ($request->has('store_id') && $request->store_id != 'all' && $request->store_id != '') {
            $stockHistoryQuery->where('store_id', $request->store_id);
        }

        if ($request->has('start_date') && $request->start_date != '') {
            $stockHistoryQuery->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date != '') {
            $stockHistoryQuery->whereDate('created_at', '<=', $request->end_date);
        }

        $stockHistory = $stockHistoryQuery->paginate(50)->withQueryString();

        if ($request->ajax()) {
            return view('outlet.index', [
                'outlets' => $outlets,
                'stockHistory' => $stockHistory,
                'active_tab' => 'riwayat'
            ])->fragment('stock-history-table');
        }

        return view('outlet.index', [
            'outlets' => $outlets,
            'active_tab' => $activeTab,
            'performanceData' => $performanceData,
            'topProductsAll' => $top3All,
            'stockHistory' => $stockHistory
        ]);
    }

    public function kinerja()
    {
        return redirect()->route('outlet.index', ['active_tab' => 'kinerja']);
    }

    public function riwayat()
    {
        return redirect()->route('outlet.index', ['active_tab' => 'riwayat']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'notelp' => 'nullable|string|max:20',
            'jam_buka' => 'nullable|string|max:255',
        ]);

        Outlet::create([
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'notelp' => $request->notelp,
            'jam_buka' => $request->jam_buka ?? '08.00 - 23.59',
            'status_aktif' => true,
        ]);

        return redirect()->route('outlet.index')->with('success', 'Outlet berhasil ditambahkan');
    }

    public function update(Request $request, $uuid)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'notelp' => 'nullable|string|max:20',
            'jam_buka' => 'nullable|string|max:255',
        ]);

        $outlet = Outlet::where('uuid', $uuid)->firstOrFail();
        
        $outlet->update([
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'notelp' => $request->notelp,
            'jam_buka' => $request->jam_buka,
        ]);

        return redirect()->route('outlet.index')->with('success', 'Outlet berhasil diperbarui');
    }

    public function toggleStatus($uuid)
    {
        $outlet = Outlet::where('uuid', $uuid)->firstOrFail();
        $outlet->status_aktif = !$outlet->status_aktif;
        $outlet->save();

        $status = $outlet->status_aktif ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('outlet.index')->with('success', "Outlet berhasil $status");
    }

    public function destroy($uuid)
    {
        $outlet = Outlet::where('uuid', $uuid)->firstOrFail();
        $outlet->delete();
        return redirect()->route('outlet.index')->with('success', 'Outlet berhasil dihapus');
    }
}
