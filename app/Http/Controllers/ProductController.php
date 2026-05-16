<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Opname;
use App\Models\OpnameDetail;
use App\Models\Outlet;
use App\Models\StockRequest;
use App\Models\StockCard;
use App\Models\Category;
use App\Models\ProductStore;
use App\Models\PriceLevel;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\LandingController;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Contact;
use App\Models\CashFlow;
use App\Models\Debt;
use Illuminate\Support\Str;
use App\Models\Fitur;
use App\Models\DetailDebt;


class ProductController extends Controller
{
    /**
     * Display the product list (Tab 1).
     */
    public function index(Request $request)
    {
        $data = $this->getConsolidatedData($request);
        
        if ($request->ajax()) {
            return view('product.index', $data)->fragment('tab-content-' . $data['active_tab']);
        }

        return view('product.index', $data);
    }

    private function getProductsData(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $selectedStoreId = $request->get('store_id');

        $query = Product::with(['category', 'priceLevels']);

        if ($user->isOwner()) {
            if ($selectedStoreId && $selectedStoreId !== 'all') {
                $query->with(['stores' => function($q) use ($selectedStoreId) {
                    $q->where('store_id', $selectedStoreId);
                }]);
                // We'll still need the relation for the specific store info, 
                // but for the sum we can be more efficient.
                $query->withSum(['stores as current_stok' => function($q) use ($selectedStoreId) {
                    $q->where('store_id', $selectedStoreId);
                }], 'stok');
            } else {
                $query->withSum('stores as current_stok', 'stok');
            }
        } else {
            $query->withSum(['stores as current_stok' => function($q) use ($user) {
                $q->where('store_id', $user->store_id);
            }], 'stok');
        }

        if ($request->has('category_id') && $request->category_id != '') {
            $query->where('kategori_id', $request->category_id);
        }

        if ($selectedStoreId && $selectedStoreId !== 'all') {
            $query->whereHas('stores', function($q) use ($selectedStoreId) {
                $q->where('store_id', $selectedStoreId);
            });
        }

        if ($request->has('search') && $request->search != '') {
            $search = strtolower($request->search);
            $query->whereRaw('LOWER(nama_produk) LIKE ?', ["%{$search}%"]);
        }

        $products = $query->paginate(10);

        $products->getCollection()->transform(function ($product) use ($user, $selectedStoreId) {
            $product->resolved_image_url = \App\Http\Controllers\LandingController::resolveImageUrl($product->image_url);
            
            // Re-calculating current_kadaluarsa from eager-loaded stores
            $storeRelation = null;
            if ($user->isOwner()) {
                if ($selectedStoreId && $selectedStoreId !== 'all') {
                    $storeRelation = $product->stores->where('store_id', $selectedStoreId)->first();
                }
            } else {
                $storeRelation = $product->stores->where('store_id', $user->store_id)->first();
            }
            $product->current_kadaluarsa = $storeRelation && $storeRelation->kadaluarsa ? \Carbon\Carbon::parse($storeRelation->kadaluarsa)->format('d F Y') : '-';
            
            return $product;
        });

        return [
            'active_tab' => 'produk',
            'products' => $products,
            'categories' => Category::all(),
            'stores' => $user->isOwner() ? Outlet::where('status_aktif', true)->get() : collect([$user->store]),
            'selected_store_id' => $selectedStoreId,
            'all_products' => $this->mapProductsForJs(Product::with(['category', 'priceLevels', 'stores.store'])->get(), $user, $selectedStoreId),
            'sub_menus' => Fitur::where('parent_id', 2)->orderBy('id')->get()
        ];
    }

    /**
     * Display the stock opname list (Tab 2).
     */
    public function opname(Request $request)
    {
        $data = $this->getConsolidatedData($request, 'opname');
        if ($request->ajax()) {
            return view('product.index', $data)->fragment('tab-content-' . $data['active_tab']);
        }
        return view('product.index', $data);
    }

    private function getOpnameData(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $sub_tab = $request->get('sub_tab', 'semua');

        $summaryQuery = Opname::query();
        if (!$user->isOwner()) {
            $summaryQuery->where('store_id', $user->store_id);
        }
        $pending_count = (clone $summaryQuery)->where('status', 'Pending')->count();
        $selesai_count = (clone $summaryQuery)->where('status', 'Selesai')->count();
        $total_loss = OpnameDetail::whereIn('opname_id', (clone $summaryQuery)->select('uuid'))
            ->join('products', 'opname_detail.product_id', '=', 'products.uuid')
            ->where('opname_detail.selisih', '<', 0)
            ->sum(DB::raw('opname_detail.selisih * COALESCE(products.harga_modal, 0)')) ?? 0;

        if ($sub_tab == 'produk_rugi') {
            $query = OpnameDetail::with(['product', 'opname.store'])->where('selisih', '<', 0);
            if (!$user->isOwner()) {
                $query->whereHas('opname', function($q) use ($user) {
                    $q->where('store_id', $user->store_id);
                });
            }
            if ($request->has('search') && $request->search != '') {
                $search = strtolower($request->search);
                $query->whereHas('product', function($q) use ($search) {
                    $q->whereRaw('LOWER(nama_produk) LIKE ?', ["%{$search}%"]);
                });
            }
            $opname_details = $query->orderBy('uuid', 'desc')->paginate(10)->withQueryString();
            $opnames = collect();
        } else {
            $query = Opname::with(['store', 'user', 'details.product'])->orderBy('tanggal', 'desc');
            if (!$user->isOwner()) {
                $query->where('store_id', $user->store_id);
            } elseif ($request->has('store_id') && $request->store_id != '') {
                $query->where('store_id', $request->store_id);
            }
            if ($request->has('category_id') && $request->category_id != '') {
                $query->where('kategori_id', $request->category_id);
            }
            if ($request->has('status') && $request->status != '') {
                $query->where('status', $request->status);
            }
            if ($request->has('search') && $request->search != '') {
                $search = strtolower($request->search);
                $query->where(function($q) use ($search) {
                    $q->whereHas('details.product', function($sq) use ($search) {
                        $sq->whereRaw('LOWER(nama_produk) LIKE ?', ["%{$search}%"]);
                    })->orWhereHas('store', function($sq) use ($search) {
                        $sq->whereRaw('LOWER(nama) LIKE ?', ["%{$search}%"]);
                    });
                });
            }
            $opnames = $query->paginate(10)->withQueryString();
            $opname_details = collect();
        }

        return [
            'active_tab' => 'opname',
            'sub_tab' => $sub_tab,
            'opnames' => $opnames,
            'opname_details' => $opname_details,
            'pending_count' => $pending_count,
            'selesai_count' => $selesai_count,
            'total_loss' => $total_loss,
            'categories' => Category::all(),
            'outlets' => $user->isOwner() ? Outlet::where('status_aktif', true)->get() : collect([$user->store]),
            'stores' => $user->isOwner() ? Outlet::where('status_aktif', true)->get() : collect([$user->store]),
            'selected_store_id' => $request->store_id,
            'all_products' => $this->mapProductsForJs(
                Product::whereHas('stores', function($q) use ($user) {
                    if (!$user->isOwner()) {
                        $q->where('store_id', $user->store_id);
                    }
                })->get(), 
                $user, 
                $request->store_id
            ),
            'sub_menus' => Fitur::where('parent_id', 2)->orderBy('id')->get()
        ];
    }

    /**
     * Display the stock and expired alerts (Tab 3).
     */
    public function request(Request $request)
    {
        $data = $this->getConsolidatedData($request, 'stok');
        if ($request->ajax()) {
            return view('product.index', $data)->fragment('tab-content-' . $data['active_tab']);
        }
        return view('product.index', $data);
    }

    private function getAlertData(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $query = ProductStore::with(['product.category', 'store']);

        $selectedStoreId = 'all';
        if (!$user->isOwner()) {
            $query->where('store_id', $user->store_id);
            $selectedStoreId = $user->store_id;
        } else {
            $selectedStoreId = $request->get('store_id', 'all');
            if ($selectedStoreId && $selectedStoreId !== 'all') {
                $query->where('store_id', $selectedStoreId);
            }
        }

        $type = $request->get('type');
        if ($type == 'stok_habis') {
            $query->whereRaw('stok <= COALESCE(stok_minimum, 10)');
        } elseif ($type == 'expired') {
            $query->whereNotNull('kadaluarsa')->where('kadaluarsa', '<=', now()->addDays(30));
        }

        if ($request->has('search') && $request->search != '') {
            $search = strtolower($request->search);
            $query->whereHas('product', function($q) use ($search) {
                $q->whereRaw('LOWER(nama_produk) LIKE ?', ["%{$search}%"]);
            });
        }

        if ($request->has('category_id') && $request->category_id != '') {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('kategori_id', $request->category_id);
            });
        }
        $alerts = $query->paginate(10)->withQueryString();
        $alerts->getCollection()->each(function($alert) {
            if ($alert->product) {
                $alert->product->resolved_image_url = \App\Http\Controllers\LandingController::resolveImageUrl($alert->product->image_url);
                $alert->product->load('priceLevels');
            }
        });
        
        $stok_habis_count = (clone $query)->whereRaw('stok <= COALESCE(stok_minimum, 10)')->count();
        $expired_count = (clone $query)->whereNotNull('kadaluarsa')->where('kadaluarsa', '<=', now()->addDays(30))->count();

        return [
            'active_tab' => 'stok',
            'alerts' => $alerts,
            'categories' => Category::all(),
            'stores' => $user->isOwner() ? Outlet::where('status_aktif', true)->get() : collect([$user->store]),
            'selected_store_id' => $selectedStoreId,
            'stok_habis_count' => $stok_habis_count,
            'expired_count' => $expired_count,
            'all_products' => Product::all(),
            'type' => $type,
            'sub_menus' => Fitur::where('parent_id', 2)->orderBy('id')->get()
        ];
    }

    public function restok(Request $request)
    {
        $data = $this->getConsolidatedData($request, 'restok');
        if ($request->ajax()) {
            return view('product.index', $data)->fragment('tab-content-' . $data['active_tab']);
        }
        return view('product.index', $data);
    }

    private function getRestokData(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $query = Transaction::where('jenis', 'pembelian')->with(['contact', 'store', 'user'])->orderBy('tanggal', 'desc');

        if ($request->search) {
            $query->whereHas('contact', function($q) use ($request) {
                $q->where('nama', 'ilike', "%{$request->search}%");
            });
        }
        if ($request->supplier_id) { $query->where('contact_id', $request->supplier_id); }

        if (!$user->isOwner()) {
            $query->where('store_id', $user->store_id);
        } elseif ($request->store_id && $request->store_id != 'all') {
            $query->where('store_id', $request->store_id);
        }

        if ($request->start_date) { $query->where('tanggal', '>=', $request->start_date); }
        if ($request->end_date) { $query->where('tanggal', '<=', $request->end_date); }

        if ($request->filter == 'today') {
            $query->whereDate('tanggal', today());
        } elseif ($request->filter == 'week') {
            $query->whereBetween('tanggal', [now()->startOfWeek(), now()->endOfWeek()]);
        }

        if ($request->status_bayar == 'Lunas') {
            $query->whereRaw('bayar >= total');
        } elseif ($request->status_bayar == 'Hutang') {
            $query->whereRaw('bayar < total');
        }

        return [
            'active_tab' => 'restok',
            'purchases' => $query->paginate(10),
            'suppliers' => Contact::where('tipe', 'ilike', 'supplier')->get(),
            'categories' => Category::all(),
            'stores' => $user->isOwner() ? Outlet::where('status_aktif', true)->get() : collect([$user->store]),
            'all_products' => Product::all(),
            'filter' => $request->filter,
            'status_bayar' => $request->status_bayar,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'supplier_id' => $request->supplier_id,
            'payment_methods' => \App\Models\PaymentMethod::all(),
            'sub_menus' => Fitur::where('parent_id', 2)->orderBy('id')->get()
        ];
    }

    public function transfer(Request $request)
    {
        $data = $this->getConsolidatedData($request, 'transfer');
        if ($request->ajax()) {
            return view('product.index', $data)->fragment('tab-content-' . $data['active_tab']);
        }
        return view('product.index', $data);
    }

    private function getConsolidatedData(Request $request, $defaultTab = 'produk')
    {
        /** @var User $user */
        $user = Auth::user();
        $active_tab = $request->get('tab', $defaultTab);
        
        // If it's an AJAX request, we prioritize the active tab to ensure "zero-loading" speed.
        if ($request->ajax()) {
            $data = ['active_tab' => $active_tab];
            
            switch ($active_tab) {
                case 'produk':
                    $data = array_merge($data, $this->getProductsData($request));
                    break;
                case 'opname':
                    $data = array_merge($data, $this->getOpnameData($request));
                    break;
                case 'stok':
                    $data = array_merge($data, $this->getAlertData($request));
                    break;
                case 'restok':
                    $data = array_merge($data, $this->getRestokData($request));
                    break;
                case 'transfer':
                    $data = array_merge($data, $this->getTransferData($request));
                    break;
            }
            
            // Fill missing variables with empty collections to prevent Blade errors
            $data['products'] = $data['products'] ?? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
            $data['opnames'] = $data['opnames'] ?? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
            $data['opname_details'] = $data['opname_details'] ?? collect();
            $data['alerts'] = $data['alerts'] ?? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
            $data['purchases'] = $data['purchases'] ?? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
            $data['transfers'] = $data['transfers'] ?? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
            
            $data['categories'] = $data['categories'] ?? Category::all();
            $data['stores'] = $data['stores'] ?? ($user->isOwner() ? Outlet::where('status_aktif', true)->get() : collect([$user->store]));
            $data['sub_menus'] = $data['sub_menus'] ?? Fitur::where('parent_id', 2)->orderBy('id')->get();
            $data['suppliers'] = $data['suppliers'] ?? Contact::where('tipe', 'ilike', 'supplier')->get();
            $data['payment_methods'] = $data['payment_methods'] ?? \App\Models\PaymentMethod::all();
            
            return $data;
        }

        // Full load: Fetch all data for instant SPA-like switching
        return array_merge(
            $this->getProductsData($request),
            $this->getOpnameData($request),
            $this->getAlertData($request),
            $this->getRestokData($request),
            $this->getTransferData($request),
            ['active_tab' => $active_tab]
        );
    }

    private function getTransferData(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $query = Transaction::where('jenis', 'transfer')->with(['store', 'tujuanStore', 'user'])->orderBy('tanggal', 'desc');

        if (!$user->isOwner()) {
            $query->where(function($q) use ($user) {
                $q->where('store_id', $user->store_id)->orWhere('tujuan_store_id', $user->store_id);
            });
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('uuid', 'ilike', "%{$search}%")->orWhere('catatan', 'ilike', "%{$search}%");
                if (Schema::hasColumn('transactions', 'status')) { $q->orWhere('status', 'ilike', "%{$search}%"); }
                $q->orWhereHas('tujuanStore', function($sq) use ($search) { $sq->where('nama', 'ilike', "%{$search}%"); })
                  ->orWhereHas('user', function($sq) use ($search) { $sq->where('username', 'ilike', "%{$search}%"); })
                  ->orWhereHas('store', function($sq) use ($search) { $sq->where('nama', 'ilike', "%{$search}%"); })
                  ->orWhereHas('details.product', function($sq) use ($search) {
                      $sq->where('nama_produk', 'ilike', "%{$search}%")->orWhere('barcode', 'ilike', "%{$search}%");
                  });
            });
        }

        if ($request->status && Schema::hasColumn('transactions', 'status')) { $query->where('status', $request->status); }
        if ($request->store_id) {
            $storeId = $request->store_id;
            $query->where(function($q) use ($storeId) { $q->where('store_id', $storeId)->orWhere('tujuan_store_id', $storeId); });
        }

        if ($request->start_date) { $query->where('tanggal', '>=', $request->start_date); }
        if ($request->end_date) { $query->where('tanggal', '<=', $request->end_date); }

        $transfers = $query->paginate(10);
        $stores = Outlet::where('status_aktif', true)->get();
        $sourceStoreId = $user->isOwner() ? ($request->source_store_id ?? $user->store_id) : $user->store_id;
        if ($user->isOwner() && !$sourceStoreId && $stores->count() > 0) { $sourceStoreId = $stores->first()->uuid; }

        $products = [];
        if ($sourceStoreId) {
            $products = ProductStore::where('store_id', $sourceStoreId)->where('stok', '>', 0)->where('status_aktif', true)->with('product')->get()
                ->map(function ($item) {
                    return [
                        'uuid' => $item->product->uuid,
                        'nama_produk' => $item->product->nama_produk,
                        'barcode' => $item->product->barcode,
                        'stok' => $item->stok
                    ];
                });
        }

        return [
            'active_tab' => 'transfer',
            'transfers' => $transfers,
            'categories' => Category::all(),
            'stores' => $stores,
            'current_source_store' => $sourceStoreId,
            'all_products' => $products,
            'sub_menus' => Fitur::where('parent_id', 2)->orderBy('id')->get()
        ];
    }

    public function getProductsByStore($store_id)
    {
        $products = Product::whereHas('stores', function($q) use ($store_id) {
            $q->where('store_id', $store_id);
        })->with(['stores' => function($q) use ($store_id) {
            $q->where('store_id', $store_id);
        }, 'category'])->get();

        $mapped = $products->map(function($p) {
            $storeData = $p->stores->first();
            return [
                'uuid' => $p->uuid,
                'nama_produk' => $p->nama_produk,
                'barcode' => $p->barcode,
                'category_name' => $p->category->nama_category ?? '-',
                'current_stok' => $storeData ? (float)$storeData->stok : 0,
                'harga_modal' => (float)($p->harga_modal ?? 0),
                'harga_jual' => (float)($p->harga_jual ?? 0)
            ];
        });

        return response()->json($mapped);
    }

    public function storeTransfer(Request $request)
    {
        $request->validate([
            'tujuan_store_id' => 'required|uuid',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|uuid',
            'items.*.qty' => 'required|numeric|min:0.01',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $sourceStoreId = $user->store_id; 
        
        if ($user->isOwner()) {
             $sourceStoreId = $request->store_id ?: $user->store_id;
        }

        if (!$sourceStoreId) {
            return back()->with('error', 'Toko asal tidak diketahui.');
        }

        if ($sourceStoreId == $request->tujuan_store_id) {
            return back()->with('error', 'Toko tujuan tidak boleh sama dengan toko asal.');
        }

        DB::beginTransaction();
        try {
            $insertData = [
                'uuid' => Str::uuid(),
                'jenis' => 'transfer',
                'store_id' => $sourceStoreId,
                'tujuan_store_id' => $request->tujuan_store_id,
                'user_id' => $user->uuid,
                'tanggal' => now(),
                'catatan' => $request->catatan,
            ];

            $insertData['status'] = 'Pending';

            $transaction = Transaction::create($insertData);

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                TransactionDetail::create([
                    'uuid' => Str::uuid(),
                    'transaction_id' => $transaction->uuid,
                    'product_id' => $item['product_id'],
                    'jmlh' => $item['qty'],
                    'harga_modal' => $product->harga_modal,
                    'harga_jual' => $product->harga_jual,
                ]);
            }

            DB::commit();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Permintaan transfer stok berhasil dibuat dan menunggu persetujuan.'
                ]);
            }
            
            return back()->with('success', 'Permintaan transfer stok berhasil dibuat dan menunggu persetujuan.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }
            
            return back()->with('error', $e->getMessage());
        }
    }

    public function approveTransfer($uuid)
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user->isOwner()) {
            return back()->with('error', 'Hanya Owner yang diperbolehkan menyetujui transfer stok.');
        }

        $transaction = Transaction::where('uuid', $uuid)->firstOrFail();
        
        $currentStatus = trim($transaction->status ?: 'Pending');
        if (strcasecmp($currentStatus, 'Pending') !== 0) {
            return back()->with('error', 'Status: ' . $currentStatus . '. Hanya transfer pending yang bisa disetujui.');
        }

        $transaction->update(['status' => 'Disetujui']);
        return back()->with('success', 'Transfer stok telah disetujui.');
    }

    public function shipTransfer($uuid)
    {
        /** @var User $user */
        $user = Auth::user();
        $transaction = Transaction::with('details')->where('uuid', $uuid)->firstOrFail();

        // Security: ONLY Source Store (Owner cannot ship unless assigned to store)
        if ($user->store_id != $transaction->store_id) {
            return back()->with('error', 'Anda tidak memiliki akses untuk mengirim barang dari outlet ini.');
        }

        if (strcasecmp($transaction->status, 'Disetujui') !== 0) {
            return back()->with('error', 'Hanya transfer yang sudah disetujui yang bisa dikirim.');
        }

        DB::beginTransaction();
        try {
            foreach ($transaction->details as $detail) {
                $sourceStock = ProductStore::where('product_id', $detail->product_id)
                    ->where('store_id', $transaction->store_id)
                    ->first();

                if (!$sourceStock || $sourceStock->stok < $detail->jmlh) {
                    $product = Product::find($detail->product_id);
                    throw new \Exception("Stok produk " . ($product->nama_produk ?? '') . " tidak mencukupi di toko asal.");
                }

                $sourceStock->decrement('stok', $detail->jmlh);

                // Audit Trail Asal
                StockCard::create([
                    'uuid' => (string) Str::uuid(),
                    'product_id' => $detail->product_id,
                    'store_id' => $transaction->store_id,
                    'jmlh' => -$detail->jmlh,
                    'keterangan' => "Transfer (Dikirim) ke " . ($transaction->tujuanStore->nama ?? 'Toko Tujuan'),
                ]);
            }

            $transaction->update(['status' => 'Dikirim']);
            DB::commit();
            return back()->with('success', 'Barang berhasil ditandai sebagai dikirim.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function confirmTransfer($uuid)
    {
        /** @var User $user */
        $user = Auth::user();
        $transaction = Transaction::with('details')->where('uuid', $uuid)->firstOrFail();

        // Security: ONLY Target Store (Owner cannot receive unless assigned to store)
        if ($user->store_id != $transaction->tujuan_store_id) {
            return back()->with('error', 'Anda tidak memiliki akses untuk menerima barang di outlet ini.');
        }

        if (strcasecmp($transaction->status, 'Dikirim') !== 0) {
            return back()->with('error', 'Hanya transfer yang sudah dikirim yang bisa diterima.');
        }

        DB::beginTransaction();
        try {
            foreach ($transaction->details as $detail) {
                $targetStock = ProductStore::firstOrCreate(
                    ['product_id' => $detail->product_id, 'store_id' => $transaction->tujuan_store_id],
                    ['stok' => 0, 'status_aktif' => true]
                );

                $targetStock->increment('stok', $detail->jmlh);

                // Audit Trail Tujuan
                StockCard::create([
                    'uuid' => (string) Str::uuid(),
                    'product_id' => $detail->product_id,
                    'store_id' => $transaction->tujuan_store_id,
                    'jmlh' => $detail->jmlh,
                    'keterangan' => "Terima Transfer dari " . ($transaction->store->nama ?? 'Toko Asal'),
                ]);
            }

            $transaction->update(['status' => 'Selesai']);
            DB::commit();
            return back()->with('success', 'Barang berhasil diterima dan stok tujuan telah diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function viewPurchaseDetail($uuid)
    {
        $transaction = Transaction::with([
            'details' => function($q) {
                $q->select('uuid', 'transaction_id', 'product_id', 'jmlh', 'harga_modal', 'harga_jual');
            },
            'details.product' => function($q) {
                $q->select('uuid', 'nama_produk', 'barcode');
            },
            'contact' => function($q) { $q->select('uuid', 'nama'); },
            'store' => function($q) { $q->select('uuid', 'nama'); },
            'user' => function($q) { $q->select('uuid', 'username'); },
            'tujuanStore' => function($q) { $q->select('uuid', 'nama'); }
        ])
        ->where('uuid', $uuid)
        ->firstOrFail();

        // Speed up: remove unnecessary appends that calculate URLs
        $transaction->details->each(function($detail) {
            if ($detail->product) {
                $detail->product->setAppends([]);
            }
        });

        return response()->json($transaction);
    }

    public function storeRestok(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'contact_id' => 'required|exists:contacts,uuid',
            'payment_type' => 'required|in:Tunai,Kredit',
            'metode_pembayaran' => 'required|exists:payment_methods,uuid',
            'dp_amount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,uuid',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.harga_beli' => 'required|numeric|min:0',
            'items.*.harga_jual_baru' => 'nullable|numeric|min:0',
            'items.*.kadaluarsa' => 'nullable|date',
        ]);

        $store_id = $user->store_id; 
        if ($user->isOwner()) {
            $store_id = $request->store_id ?: $user->store_id ?: Outlet::first()->uuid;
        }

        DB::beginTransaction();
        try {
            $total = 0;
            foreach ($request->items as $item) {
                $total += ($item['qty'] * $item['harga_beli']);
            }

            $dp_paid = $request->payment_type == 'Tunai' ? $total : ($request->dp_amount ?? 0);

            // 1. Pencatatan Transaksi
            $transaction = Transaction::create([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'total' => $total,
                'bayar' => $dp_paid,
                'kembalian' => 0,
                'jenis' => 'pembelian',
                'store_id' => $store_id,
                'user_id' => $user->uuid,
                'contact_id' => $request->contact_id,
                'metode_pembayaran' => $request->metode_pembayaran,
                'catatan' => $request->catatan,
                'tanggal' => now(),
            ]);

            foreach ($request->items as $item) {
                // 2. Simpan Transaction Detail
                TransactionDetail::create([
                    'transaction_id' => $transaction->uuid,
                    'product_id' => $item['product_id'],
                    'jmlh' => $item['qty'],
                    'harga_modal' => $item['harga_beli'],
                    'harga_jual' => $item['harga_jual_baru'] ?? 0,
                ]);

                // 3. Update Stok di Toko
                $productStore = ProductStore::firstOrCreate(
                    ['product_id' => $item['product_id'], 'store_id' => $store_id],
                    ['stok' => 0, 'status_aktif' => true]
                );
                $productStore->increment('stok', $item['qty']);
                if ($item['kadaluarsa']) {
                    $productStore->update(['kadaluarsa' => $item['kadaluarsa']]);
                }

                // 4. Update Harga di Master Produk
                $product = Product::findOrFail($item['product_id']);
                $productUpdate = ['harga_modal' => $item['harga_beli']];
                if (!empty($item['harga_jual_baru']) && $item['harga_jual_baru'] > 0) {
                    $productUpdate['harga_jual'] = $item['harga_jual_baru'];
                }
                $product->update($productUpdate);

                // 5. Stock Card
                StockCard::create([
                    'product_id' => $item['product_id'],
                    'store_id' => $store_id,
                    'jmlh' => $item['qty'],
                    'keterangan' => "Restok dari Supplier (Trx: {$transaction->uuid})",
                ]);
            }

            // 6. Keuangan
            if ($request->payment_type == 'Tunai' || $dp_paid > 0) {
                CashFlow::create([
                    'store_id' => $store_id,
                    'user_id' => $user->uuid,
                    'jenis' => 'pengeluaran',
                    'nominal' => $dp_paid,
                    'metode_pembayaran' => $request->metode_pembayaran,
                    'keterangan' => $request->payment_type == 'Tunai' ? "Pembelian stok / Restok (Trx: {$transaction->uuid})" : "DP Pembelian stok / Restok (Trx: {$transaction->uuid})",
                    'tanggal' => now(),
                ]);
            }

            if ($request->payment_type == 'Kredit') {
                $debt_amount = $total - $dp_paid;
                if ($debt_amount > 0) {
                    Debt::create([
                        'store_id' => $store_id,
                        'kontak_id' => $request->contact_id,
                        'tipe' => 'utang',
                        'nominal' => $debt_amount,
                        'sisa' => $debt_amount,
                        'jatuh_tempo' => now()->addDays(30), 
                        'keterangan' => "Sisa pembayaran restok (Trx: {$transaction->uuid})",
                        'reference_id' => $transaction->uuid,
                        'reference_type' => 'transaction'
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('products.restok')->with('success', 'Restok berhasil disimpan dan stok telah diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan restok: ' . $e->getMessage());
        }
    }

    public function updateStoreData(Request $request, $uuid)
    {
        $productStore = ProductStore::findOrFail($uuid);
        
        $request->validate([
            'stok' => 'required|integer',
            'kadaluarsa' => 'nullable|date',
            'stok_minimum' => 'nullable|integer|min:0',
        ]);

        $oldStok = $productStore->stok;
        $updateData = [
            'stok' => $request->stok,
            'kadaluarsa' => $request->kadaluarsa,
            'stok_minimum' => $request->stok_minimum,
            'status_aktif' => $request->has('status_aktif') ? true : false,
        ];

        $productStore->update($updateData);

        // Log to stock card if stock changed
        if ($oldStok != $request->stok) {
            StockCard::create([
                'product_id' => $productStore->product_id,
                'store_id' => $productStore->store_id,
                'jmlh' => $request->stok - $oldStok,
                'keterangan' => 'Penyesuaian stok manual di menu Stok & Expired',
            ]);
        }

        return redirect()->back()->with('success', 'Data stok dan kadaluarsa berhasil diperbarui!');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'kategori_id' => 'required|exists:category,uuid',
            'harga_modal' => 'nullable|numeric',
            'harga_jual' => 'nullable|numeric',
        ]);

        $imageUrl = null;

        if ($request->filled('cropped_image')) {
            $base64Image = $request->input('cropped_image');
            $cloudinaryUrl = LandingController::uploadToCloudinary($base64Image, 'products');
            
            if ($cloudinaryUrl) {
                $imageUrl = $cloudinaryUrl;
            } else {
                // Fallback to local
                @list(, $fileData) = explode(';', $base64Image);
                @list(, $fileData) = explode(',', $fileData);
                $imageBinary = base64_decode($fileData);
                $fileName = \Illuminate\Support\Str::uuid() . '.png';
                \Illuminate\Support\Facades\Storage::disk('public')->put('products/' . $fileName, $imageBinary);
                $imageUrl = 'products/' . $fileName;
            }
        }

        $product = Product::create([
            'nama_produk' => $request->nama_produk,
            'barcode' => $request->barcode,
            'kategori_id' => $request->kategori_id,
            'harga_modal' => $request->harga_modal ?? 0,
            'harga_jual' => $request->harga_jual ?? 0,
            'image_url' => $imageUrl,
        ]);

        // Save Price Levels (Grosir)
        if ($request->has('price_levels')) {
            foreach ($request->price_levels as $level) {
                if ($level['jmlh'] > 0 && $level['harga'] > 0) {
                    PriceLevel::create([
                        'product_id' => $product->uuid,
                        'jmlh' => $level['jmlh'],
                        'harga' => $level['harga'],
                    ]);
                }
            }
        }

        StockCard::create([
            'product_id' => $product->uuid,
            'jmlh' => 0,
            'keterangan' => 'Produk baru ditambahkan ke sistem',
        ]);

        // Initialize ProductStore for all active stores with 0 stock
        $activeStores = Outlet::where('status_aktif', true)->get();
        foreach ($activeStores as $s) {
            ProductStore::create([
                'product_id' => $product->uuid,
                'store_id' => $s->uuid,
                'stok' => 0,
                'status_aktif' => true,
            ]);
        }

        return redirect()->back()->with('success', 'Produk berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        
        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'kategori_id' => 'required|exists:category,uuid',
            'harga_modal' => 'nullable|numeric',
            'harga_jual' => 'nullable|numeric',
        ]);

        $updateData = [
            'nama_produk' => $request->nama_produk,
            'barcode' => $request->barcode,
            'kategori_id' => $request->kategori_id,
            'harga_modal' => $request->harga_modal ?? 0,
            'harga_jual' => $request->harga_jual ?? 0,
        ];

        if ($request->filled('cropped_image')) {
            $base64Image = $request->input('cropped_image');
            \Illuminate\Support\Facades\Log::info('Updating product image for: ' . $product->uuid);
            
            $cloudinaryUrl = LandingController::uploadToCloudinary($base64Image, 'products');

            if ($cloudinaryUrl) {
                $updateData['image_url'] = $cloudinaryUrl;
                \Illuminate\Support\Facades\Log::info('Cloudinary upload success: ' . $cloudinaryUrl);
            } else {
                \Illuminate\Support\Facades\Log::info('Falling back to local storage for product image');
                @list(, $fileData) = explode(';', $base64Image);
                @list(, $fileData) = explode(',', $fileData);
                
                if ($fileData) {
                    $imageBinary = base64_decode($fileData);
                    $fileName = \Illuminate\Support\Str::uuid() . '.png';
                    $newPath = 'products/' . $fileName;
                    
                    // Deletion logic with path normalization
                    if ($product->image_url && !str_starts_with($product->image_url, 'http')) {
                        $oldPath = ltrim(str_replace(['storage/', '/storage/'], '', $product->image_url), '/');
                        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($oldPath)) {
                            \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
                            \Illuminate\Support\Facades\Log::info('Old local image deleted: ' . $oldPath);
                        }
                    }

                    \Illuminate\Support\Facades\Storage::disk('public')->put($newPath, $imageBinary);
                    $updateData['image_url'] = '/storage/' . $newPath;
                    \Illuminate\Support\Facades\Log::info('New local image saved: /storage/' . $newPath);
                } else {
                    \Illuminate\Support\Facades\Log::warning('Invalid base64 image data received for product: ' . $product->uuid);
                }
            }
        }

        $product->update($updateData);

        // Update Price Levels (Grosir) - Simple Sync
        PriceLevel::where('product_id', $product->uuid)->delete();
        if ($request->has('price_levels')) {
            foreach ($request->price_levels as $level) {
                if ($level['jmlh'] > 0 && $level['harga'] > 0) {
                    PriceLevel::create([
                        'product_id' => $product->uuid,
                        'jmlh' => $level['jmlh'],
                        'harga' => $level['harga'],
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'Produk berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        
        DB::beginTransaction();
        try {
            // Delete related price levels
            PriceLevel::where('product_id', $product->uuid)->delete();
            
            // Delete related product store links
            ProductStore::where('product_id', $product->uuid)->delete();

            // Delete related stock cards
            StockCard::where('product_id', $product->uuid)->delete();

            // Delete related opname details
            OpnameDetail::where('product_id', $product->uuid)->delete();

            // Delete related transaction details (sales history)
            TransactionDetail::where('product_id', $product->uuid)->delete();

            // Detach from promos if relationship exists
            if (method_exists($product, 'promos')) {
                $product->promos()->detach();
            }

            // Delete image if local
            if ($product->image_url && !str_starts_with($product->image_url, 'http')) {
                $oldPath = ltrim(str_replace(['storage/', '/storage/'], '', $product->image_url), '/');
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($oldPath)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
                }
            }

            $product->delete();
            
            DB::commit();
            return redirect()->back()->with('success', 'Produk berhasil dihapus secara permanen!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Product Deletion Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus produk: ' . $e->getMessage());
        }
    }

    public function massDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:products,uuid'
        ]);

        /** @var User $user */
        $user = Auth::user();

        if (!$user->isOwner()) {
            ProductStore::whereIn('product_id', $request->ids)
                ->where('store_id', $user->store_id)
                ->update(['status_aktif' => false]);
        } else {
            ProductStore::whereIn('product_id', $request->ids)
                ->update(['status_aktif' => false]);
        }

        return redirect()->back()->with('success', count($request->ids) . ' Produk berhasil dihapus dari daftar!');
    }

    public function storeOpname(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'store_id' => 'required|exists:store,uuid',
            'kategori_id' => 'nullable|exists:category,uuid',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,uuid',
            'items.*.stok_sistem' => 'required|numeric',
            'items.*.stok_fisik' => 'nullable|numeric',
            'items.*.alasan_selisih' => 'nullable|string',
        ]);

        $storeId = $user->isOwner() ? $request->store_id : $user->store_id;

        DB::beginTransaction();
        try {
            $opname = Opname::create([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'tanggal' => now(),
                'store_id' => $storeId,
                'user_id' => $user->uuid,
                'status' => 'Pending',
                'kategori_id' => $request->kategori_id
            ]);

            foreach ($request->items as $item) {
                $fisik = (isset($item['stok_fisik']) && $item['stok_fisik'] !== '') ? $item['stok_fisik'] : null;
                $sistem = $item['stok_sistem'];
                $selisih = ($fisik !== null) ? ($fisik - $sistem) : 0;

                OpnameDetail::create([
                    'uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'opname_id' => $opname->uuid,
                    'product_id' => $item['product_id'],
                    'stok_sistem' => $sistem,
                    'stok_fisik' => $fisik,
                    'selisih' => $selisih,
                    'keterangan' => $item['alasan_selisih'] ?? null,
                ]);
            }

            DB::commit();

            if ($request->action == 'finalize') {
                return $this->finalizeOpname($opname->uuid);
            }

            return redirect()->back()->with('success', 'Sesi Opname berhasil dibuat (Status: Pending)!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Opname Store Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan opname: ' . $e->getMessage());
        }
    }

    public function updateOpname(Request $request, $id)
    {
        /** @var User $user */
        $user = Auth::user();

        $opname = Opname::findOrFail($id);
        if ($opname->status == 'Selesai') {
            return redirect()->back()->with('error', 'Opname yang sudah difinalisasi tidak dapat diubah.');
        }

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,uuid',
            'items.*.stok_sistem' => 'required|numeric',
            'items.*.stok_fisik' => 'nullable|numeric',
            'items.*.alasan_selisih' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Sync category if changed (though usually fixed once created)
            if ($request->has('kategori_id')) {
                $opname->update(['kategori_id' => $request->kategori_id]);
            }

            // Remove old details and insert new ones (simpler than syncing)
            OpnameDetail::where('opname_id', $opname->uuid)->delete();

            foreach ($request->items as $item) {
                $fisik = (isset($item['stok_fisik']) && $item['stok_fisik'] !== '') ? $item['stok_fisik'] : null;
                $sistem = $item['stok_sistem'];
                $selisih = ($fisik !== null) ? ($fisik - $sistem) : 0;

                OpnameDetail::create([
                    'uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'opname_id' => $opname->uuid,
                    'product_id' => $item['product_id'],
                    'stok_sistem' => $sistem,
                    'stok_fisik' => $fisik,
                    'selisih' => $selisih,
                    'keterangan' => $item['alasan_selisih'] ?? null,
                ]);
            }

            DB::commit();

            if ($request->action == 'finalize') {
                return $this->finalizeOpname($opname->uuid);
            }

            return redirect()->back()->with('success', 'Sesi Opname berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Opname Update Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui opname: ' . $e->getMessage());
        }
    }

    public function finalizeOpname($id)
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user->isOwner() && !$user->isKepalaToko()) {
            return redirect()->back()->with('error', 'Hanya Owner atau Kepala Toko yang bisa melakukan finalisasi opname.');
        }

        $opname = Opname::with('details')->findOrFail($id);
        if ($opname->status == 'Selesai') {
            return redirect()->back()->with('error', 'Opname ini sudah difinalisasi sebelumnya.');
        }

        DB::beginTransaction();
        try {
            foreach ($opname->details as $detail) {
                $ps = ProductStore::where('product_id', $detail->product_id)
                    ->where('store_id', $opname->store_id)
                    ->first();
                
                if ($ps) {
                    $ps->update(['stok' => $detail->stok_fisik]);

                    if ($detail->selisih != 0) {
                        StockCard::create([
                            'uuid' => (string) \Illuminate\Support\Str::uuid(),
                            'product_id' => $detail->product_id,
                            'store_id' => $opname->store_id,
                            'jmlh' => $detail->selisih,
                            'keterangan' => "Opname: {$opname->uuid}",
                            'created_at' => now()
                        ]);
                    }
                }
            }

            $opname->update(['status' => 'Selesai']);

            DB::commit();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Opname berhasil difinalisasi!']);
            }

            return redirect()->back()->with('success', 'Opname berhasil difinalisasi dan stok telah diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Opname Finalize Error: ' . $e->getMessage());
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'Gagal finalisasi opname: ' . $e->getMessage());
        }
    }

    public function destroyOpname($id)
    {
        $opname = Opname::with('details')->findOrFail($id);
        
        DB::beginTransaction();
        try {
            if ($opname->status == 'Selesai') {
                // Rollback stock changes
                foreach ($opname->details as $detail) {
                    $ps = ProductStore::where('product_id', $detail->product_id)
                        ->where('store_id', $opname->store_id)
                        ->first();
                    
                    if ($ps && $detail->selisih != 0) {
                        // Reverse the adjustment: NewStock = CurrentStock - Selisih
                        $ps->decrement('stok', $detail->selisih);
                        
                        // Remove audit trail entry if exists
                        StockCard::where('product_id', $detail->product_id)
                            ->where('store_id', $opname->store_id)
                            ->where('keterangan', "Opname: {$opname->uuid}")
                            ->delete();
                    }
                }
            }
            
            $opname->details()->delete();
            $opname->delete();
            
            DB::commit();
            return redirect()->back()->with('success', 'Riwayat opname berhasil dihapus dan stok dikembalikan (jika sudah difinalisasi).');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus opname: ' . $e->getMessage());
        }
    }



    public function show($id)
    {
        $opname = Opname::with(['details.product.stores', 'store', 'user'])->findOrFail($id);
        
        foreach ($opname->details as $detail) {
            if ($detail->product && $detail->product->stores) {
                $currentStore = $detail->product->stores->where('store_id', $opname->store_id)->first();
                $detail->current_system_stock = $currentStore ? $currentStore->stok : 0;
            } else {
                $detail->current_system_stock = 0;
            }
        }
        
        return response()->json($opname);
    }

    public function storeRequest(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'product_id' => 'required|exists:products,uuid',
            'jumlah_minta' => 'required|integer|min:1',
            'prioritas' => 'required|in:Tinggi,Sedang,Rendah',
            'store_id' => 'nullable|exists:store,uuid',
        ]);

        $target_store_id = $user->isOwner() && $request->has('store_id') ? $request->store_id : $user->store_id;

        StockRequest::create([
            'product_id' => $request->product_id,
            'jumlah_minta' => $request->jumlah_minta,
            'prioritas' => $request->prioritas,
            'pemohon' => $user->name,
            'alasan_permintaan' => $request->alasan_permintaan,
            'status' => 'Pending',
            'store_id' => $target_store_id,
        ]);

        return redirect()->back()->with('success', 'Request produk berhasil dikirim!');
    }

    public function updateRequest(Request $request, $id)
    {
        $req = StockRequest::findOrFail($id);
        if ($req->status != 'Pending') {
            return redirect()->back()->with('error', 'Hanya request pending yang bisa diubah.');
        }

        $request->validate([
            'product_id' => 'required|exists:products,uuid',
            'jumlah_minta' => 'required|integer|min:1',
            'prioritas' => 'required|in:Tinggi,Sedang,Rendah',
        ]);

        $req->update([
            'product_id' => $request->product_id,
            'jumlah_minta' => $request->jumlah_minta,
            'prioritas' => $request->prioritas,
            'alasan_permintaan' => $request->alasan_permintaan,
        ]);
        return redirect()->back()->with('success', 'Request produk berhasil diperbarui!');
    }

    public function destroyRequest($id)
    {
        $req = StockRequest::findOrFail($id);
        if ($req->status != 'Pending') {
            return redirect()->back()->with('error', 'Hanya request pending yang bisa dibatalkan.');
        }

        $req->delete();
        return redirect()->back()->with('success', 'Request produk berhasil dibatalkan!');
    }

    public function approveRequest($id)
    {
        $req = StockRequest::findOrFail($id);
        $req->update(['status' => 'Diproses']);

        return redirect()->back()->with('success', 'Request produk telah disetujui dan sedang diproses!');
    }

    public function rejectRequest($id)
    {
        $req = StockRequest::findOrFail($id);
        $req->update(['status' => 'Ditolak']);
        return redirect()->back()->with('success', 'Request produk telah ditolak!');
    }

    public function shipRequest($id)
    {
        $req = StockRequest::findOrFail($id);
        if ($req->status != 'Diproses') {
            return redirect()->back()->with('error', 'Hanya request yang sedang diproses yang bisa dikirim.');
        }
        $req->update(['status' => 'Dikirim']);
        return redirect()->back()->with('success', 'Request produk berhasil ditandai sebagai dikirim!');
    }

    public function receiveRequest($id)
    {
        $req = StockRequest::findOrFail($id);
        if ($req->status != 'Dikirim') {
            return redirect()->back()->with('error', 'Hanya request yang sedang dikirim yang bisa diselesaikan.');
        }
        
        $req->update(['status' => 'Selesai']);
        $productStore = ProductStore::firstOrCreate(
            ['product_id' => $req->product_id, 'store_id' => $req->store_id],
            ['stok' => 0, 'status_aktif' => true]
        );
        $productStore->increment('stok', $req->jumlah_minta);
        $productStore->update(['status_aktif' => true]);

        return redirect()->back()->with('success', 'Barang telah diterima, request selesai dan stok cabang bertambah otomatis!');
    }

    /*untuk export ke Excel*/
    public function exportExcel(Request $request)
    {
        $tab = $request->active_tab ?? 'produk';
        $data = $this->getExportData($request, $tab);
        $filename = "export_{$tab}_" . date('Y-m-d_His') . ".csv";

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = $this->getExportColumns($tab);

        $callback = function() use ($data, $columns, $tab) {
            $file = fopen('php://output', 'w');
            // Add BOM to fix Excel encoding and parsing issues
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            foreach ($data as $item) {
                $row = $this->formatExportRow($item, $tab);
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /*untuk export ke PDF*/
    public function exportPdf(Request $request)
    {
        $tab = $request->active_tab ?? 'produk';
        $data = $this->getExportData($request, $tab);
        $title = "Laporan " . ($tab == 'produk' ? 'Produk' : ($tab == 'opname' ? 'Stock Opname' : 'Stok & Expired'));

        $pdf = Pdf::loadView('exports.pdf', [
            'title' => $title,
            'tab' => $tab,
            'data' => $data,
            'date' => date('d F Y')
        ])->setPaper('a4', 'landscape');

        return $pdf->download("export_{$tab}_" . date('Y-m-d') . ".pdf");
    }

    private function getExportData(Request $request, $tab)
    {
        /** @var User $user */
        $user = Auth::user();
        
        if ($tab == 'produk') {
            $query = Product::with(['category', 'stores']);
            $selectedStoreId = $request->get('store_id');
            
            if (!$user->isOwner()) {
                $selectedStoreId = $user->store_id;
            }

            if ($selectedStoreId && $selectedStoreId !== 'all') {
                $query->whereHas('stores', function($q) use ($selectedStoreId) {
                    $q->where('store_id', $selectedStoreId)->where('status_aktif', true);
                });
            } else {
                $query->whereHas('stores', function($q) {
                    $q->where('status_aktif', true);
                });
            }

            if ($request->category_id) $query->where('kategori_id', $request->category_id);
            if ($request->search) $query->where('nama_produk', 'ilike', '%' . $request->search . '%');
            return $query->get();
        } 
        
        if ($tab == 'opname') {
            if ($request->sub_tab == 'produk_rugi') {
                $query = OpnameDetail::with(['product', 'opname.store'])
                    ->where('selisih', '<', 0);
                
                if (!$user->isOwner()) {
                    $query->whereHas('opname', function($q) use ($user) {
                        $q->where('store_id', $user->store_id);
                    });
                }

                if ($request->search) {
                    $search = strtolower($request->search);
                    $query->whereHas('product', function($q) use ($search) {
                        $q->whereRaw('LOWER(nama_produk) LIKE ?', ["%{$search}%"]);
                    });
                }
                return $query->get();
            }

            $query = Opname::with(['store', 'user', 'details.product'])->orderBy('tanggal', 'desc');
            if (!$user->isOwner()) $query->where('store_id', $user->store_id);
            if ($request->search) {
                $query->where(function($q) use ($request) {
                    $q->where('uuid', 'ilike', '%' . $request->search . '%')
                      ->orWhereHas('user', function($uq) use ($request) {
                          $uq->where('name', 'ilike', '%' . $request->search . '%');
                      });
                });
            }
            return $query->get();
        }

        if ($tab == 'request') {
            $query = ProductStore::with(['product.category', 'store'])->where('status_aktif', true);
            if (!$user->isOwner()) $query->where('store_id', $user->store_id);
            if ($request->search) {
                $query->whereHas('product', function($q) use ($request) {
                    $q->where('nama_produk', 'ilike', '%' . $request->search . '%');
                });
            }
            if ($request->type == 'stok_habis') {
                $query->where('stok', '<=', 0);
            } elseif ($request->type == 'expired') {
                $query->whereNotNull('kadaluarsa')->where('kadaluarsa', '<=', now()->addDays(30));
            }
            return $query->get();
        }

        return collect();
    }

    private function getExportColumns($tab)
    {
        if ($tab == 'produk') return ['Nama Produk', 'Barcode', 'Kategori', 'Harga Modal', 'Harga Jual', 'Stok'];
        if ($tab == 'opname') {
            if (request('sub_tab') == 'produk_rugi') {
                return ['Nama Produk', 'Barcode', 'Outlet', 'Tanggal Opname', 'Stok Sistem', 'Stok Fisik', 'Selisih', 'Kerugian (Rp)'];
            }
            return ['No Ref', 'Tanggal', 'Petugas', 'Outlet', 'Total Item', 'Total Selisih', 'Potensi Kerugian (Rp)', 'Status'];
        }
        if ($tab == 'request') return ['Produk', 'Outlet', 'Stok', 'Kadaluarsa', 'Kategori'];
        return [];
    }

    public function showProductDetail($id)
    {
        $user = Auth::user();
        $selectedStoreId = request('selected_store_id');
        $product = Product::with(['category', 'priceLevels', 'stores.store'])->findOrFail($id);
        
        $mapped = $this->mapProductsForJs(collect([$product]), $user, $selectedStoreId)->first();
        return response()->json($mapped);
    }

    private function mapProductsForJs($products, $user, $selectedStoreId = null)
    {
        return $products->map(function($p) use ($user, $selectedStoreId) {
            $data = $p->toArray();
            
            // Critical: Ensure these keys exist for the Detail Modal JS
            $data['resolved_image_url'] = \App\Http\Controllers\LandingController::resolveImageUrl($p->image_url);
            $data['nama_category'] = $p->category ? $p->category->nama_category : ($p->kategori ? $p->kategori->nama_category : null);
            
            // Map stores and price levels explicitly
            $data['price_levels'] = $p->priceLevels->toArray();
            $data['stores'] = $p->stores->map(function($ps) {
                return [
                    'stok' => $ps->stok,
                    'store' => [
                        'nama' => $ps->store->nama ?? 'Cabang'
                    ]
                ];
            })->toArray();

            // Calculate current_stok based on context
            if ($user->isOwner()) {
                if ($selectedStoreId && $selectedStoreId !== 'all') {
                    $stRel = $p->stores->where('store_id', $selectedStoreId)->first();
                    $data['current_stok'] = $stRel ? $stRel->stok : 0;
                } else {
                    $data['current_stok'] = $p->stores->sum('stok');
                }
            } else {
                $stRel = $p->stores->where('store_id', $user->store_id)->first();
                $data['current_stok'] = $stRel ? $stRel->stok : 0;
            }

            return $data;
        });
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'nama_category' => 'required|string|max:255|unique:category,nama_category',
        ]);

        $category = Category::create([
            'nama_category' => $request->nama_category,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil ditambahkan!',
                'category' => $category
            ]);
        }

        return redirect()->back()->with('success', 'Kategori berhasil ditambahkan!');
    }

    private function formatExportRow($item, $tab)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($tab == 'produk') {
            $selectedStoreId = request('store_id');
            $stok = 0;

            if (!$user->isOwner()) {
                $selectedStoreId = $user->store_id;
            }

            if ($selectedStoreId && $selectedStoreId !== 'all') {
                $stok = $item->stores->where('store_id', $selectedStoreId)->first()->stok ?? 0;
            } else {
                $stok = $item->stores->sum('stok');
            }

             return [
                $item->nama_produk,
                $item->barcode,
                $item->category->nama_category ?? '-',
                $item->harga_modal,
                $item->harga_jual,
                $stok
            ];
        }
        if ($tab == 'opname') {
            if (request('sub_tab') == 'produk_rugi') {
                $modal = $item->product->harga_modal ?? 0;
                $kerugian = abs($item->selisih * $modal);
                return [
                    $item->product->nama_produk ?? '-',
                    $item->product->barcode ?? '-',
                    $item->opname->store->nama ?? '-',
                    \Carbon\Carbon::parse($item->opname->tanggal)->format('d-m-Y'),
                    $item->stok_sistem,
                    $item->stok_fisik,
                    $item->selisih,
                    $kerugian
                ];
            }
            return [
                $item->uuid,
                ' ' . \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y'),
                $item->user->name ?? $item->user->username ?? '-',
                $item->store->nama ?? '-',
                $item->total_items,
                $item->total_selisih,
                number_format(abs($item->total_kerugian), 0, ',', '.'),
                $item->status
            ];
        }
        if ($tab == 'request') {
            return [
                $item->product->nama_produk ?? '-',
                $item->store->nama ?? '-',
                $item->stok,
                $item->kadaluarsa ? \Carbon\Carbon::parse($item->kadaluarsa)->format('d-m-Y') : '-',
                $item->product->category->nama_category ?? '-'
            ];
        }
        return [];
    }

    public function payPurchaseDebt(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|exists:transactions,uuid',
            'nominal' => 'required|numeric|min:1',
            'metode_pembayaran' => 'required|exists:payment_methods,uuid',
        ]);

        DB::beginTransaction();
        try {
            $transaction = Transaction::findOrFail($request->transaction_id);
            
            // Find associated debt
            $debt = Debt::where('keterangan', 'like', "%{$transaction->uuid}%")
                        ->where('tipe', 'utang')
                        ->first();

            if (!$debt) {
                if ($transaction->bayar < $transaction->total) {
                     throw new \Exception('Data hutang tidak ditemukan untuk transaksi ini.');
                }
                throw new \Exception('Transaksi ini tidak memiliki hutang.');
            }

            $bayar = $request->nominal;
            if ($bayar > $debt->sisa) {
                $bayar = $debt->sisa;
            }

            $sebelum = $debt->sisa;
            $sisaBaru = $debt->sisa - $bayar;

            // 1. Create DetailDebt
            DetailDebt::create([
                'debts_id' => $debt->uuid,
                'sebelum' => $sebelum,
                'bayar' => $bayar,
                'sisa' => $sisaBaru
            ]);

            // 2. Update Debt
            $debt->update(['sisa' => $sisaBaru]);

            // 3. Update Transaction (increment total bayar)
            $transaction->increment('bayar', $bayar);

            // 4. Create CashFlow (pengeluaran)
            CashFlow::create([
                'store_id' => $transaction->store_id,
                'user_id' => Auth::id(),
                'jenis' => 'pengeluaran',
                'nominal' => $bayar,
                'keterangan' => "Cicilan pelunasan hutang restok (Trx: {$transaction->uuid})",
                'tanggal' => now(),
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Pembayaran berhasil dicatat!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    public function destroyRestok($uuid)
    {
        DB::beginTransaction();
        try {
            $transaction = Transaction::where('uuid', $uuid)->where('jenis', 'pembelian')->firstOrFail();
            $details = TransactionDetail::where('transaction_id', $uuid)->get();

            foreach ($details as $detail) {
                // 1. Kembalikan Stok di ProductStore (Kurangi stok yang tadi ditambah)
                $productStore = ProductStore::where('product_id', $detail->product_id)
                    ->where('store_id', $transaction->store_id)
                    ->first();

                if ($productStore) {
                    $productStore->decrement('stok', $detail->jmlh);
                }

                // 2. Catat di Stock Card sebagai pengurang (Reversal)
                StockCard::create([
                    'product_id' => $detail->product_id,
                    'store_id' => $transaction->store_id,
                    'jmlh' => -$detail->jmlh,
                    'keterangan' => "Penghapusan Restok (Trx: {$uuid})",
                ]);
            }

            // 3. Hapus catatan Keuangan (CashFlow)
            // Mencari cashflow yang mencantumkan UUID transaksi di keterangannya
            CashFlow::where('keterangan', 'like', "%{$uuid}%")->delete();

            // 4. Hapus catatan Hutang (Debt) dan Detail Pembayarannya jika ada
            $debt = Debt::where('keterangan', 'like', "%{$uuid}%")->first();
            if ($debt) {
                DetailDebt::where('debts_id', $debt->uuid)->delete();
                $debt->delete();
            }

            // 5. Hapus Detail Transaksi dan Transaksi Utama
            TransactionDetail::where('transaction_id', $uuid)->delete();
            $transaction->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Restok berhasil dihapus. Stok produk dan catatan keuangan telah dikembalikan ke kondisi semula.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menghapus restok: ' . $e->getMessage()], 500);
        }
    }

    public function massDelete(Request $request)
    {
        $uuids = explode(',', $request->uuids);
        if (empty($uuids) || (count($uuids) == 1 && empty($uuids[0]))) {
            return back()->with('error', 'Tidak ada produk terpilih untuk dihapus.');
        }

        DB::beginTransaction();
        try {
            ProductStore::whereIn('product_id', $uuids)->delete();
            Product::whereIn('uuid', $uuids)->delete();

            DB::commit();
            return back()->with('success', count($uuids) . ' produk berhasil dihapus secara massal.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus produk secara massal: ' . $e->getMessage());
        }
    }
}
