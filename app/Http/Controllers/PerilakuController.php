<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class PerilakuController extends Controller
{
    /**
     * Main Perilaku index page — two tabs: Customer & Produk
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Outlets available to this user
        $outlets = collect();
        if ($user->role === 'owner') {
            $outlets = DB::table('store')->where('status_aktif', true)->get();
        } elseif ($user->role === 'kepala_toko' && $user->outlet_id) {
            $outlets = DB::table('store')->where('uuid', $user->outlet_id)->get();
        }

        $defaultStore = $user->role === 'owner'
            ? ($outlets->first()->uuid ?? null)
            : ($user->outlet_id ?? ($outlets->first()->uuid ?? null));

        $store_id = $request->input('store_id', session('perilaku_store_id', $defaultStore));
        $year = $request->input('year', date('Y'));
        $active_tab = $request->input('active_tab', 'customer');

        // Save to session
        session(['perilaku_store_id' => $store_id]);

        return view('perilaku.index', [
            'outlets'    => $outlets,
            'store_id'   => $store_id,
            'year'       => $year,
            'active_tab' => $active_tab,
        ]);
    }

    // ═══════════════════════════════════════════
    //  API: Customer Behavior Yearly
    // ═══════════════════════════════════════════

    public function customerYearly(Request $request)
    {
        $store_id = $request->input('store_id');
        $year = (int) $request->input('year', date('Y'));
        $search = $request->input('search', '');
        $kanal = $request->input('kanal', 'semua'); // semua | offline | online

        if (!$store_id) {
            return response()->json(['customers' => [], 'total_omset' => 0, 'total_customers' => 0]);
        }

        $cacheKey = "customer_behavior_{$store_id}_{$year}";

        try {
            $data = Cache::remember($cacheKey, 1800, function () use ($store_id, $year) {
                return DB::select('SELECT * FROM get_customer_behavior_yearly(?, ?)', [$store_id, $year]);
            });
        } catch (\Exception $e) {
            \Log::error('[PerilakuController] customerYearly RPC failed: ' . $e->getMessage());
            return response()->json([
                'customers'       => [],
                'total_omset'     => 0,
                'total_customers' => 0,
                'error'           => 'Gagal memuat data dari database. Coba lagi beberapa saat.',
            ], 500);
        }

        // Group by customer
        $grouped = collect($data)->groupBy('contact_id')->map(function ($rows) {
            $first = $rows->first();
            $months = [];
            $totalOmset = 0;

            foreach ($rows as $row) {
                $months[] = [
                    'bulan' => (int) $row->bulan,
                    'total_omset' => (float) $row->total_omset,
                ];
                $totalOmset += (float) $row->total_omset;
            }

            return [
                'contact_id'    => $first->contact_id,
                'nama_customer' => $first->nama_customer,
                'total_omset'   => $totalOmset,
                'months'        => $months,
            ];
        })->values();

        // Filter by kanal
        if ($kanal !== 'semua') {
            $grouped = $grouped->filter(function ($item) use ($kanal) {
                $isOnline = str_ends_with($item['nama_customer'], ' (Online)');
                if ($kanal === 'online') return $isOnline;
                if ($kanal === 'offline') return !$isOnline;
                return true;
            })->values();
        }

        // Filter by search
        if ($search) {
            $grouped = $grouped->filter(function ($item) use ($search) {
                $q = strtolower($search);
                return str_contains(strtolower($item['nama_customer']), $q);
            })->values();
        }

        // Sort by omset desc (default)
        $sorted = $grouped->sortByDesc('total_omset')->values();

        return response()->json([
            'customers'       => $sorted,
            'total_omset'     => $sorted->sum('total_omset'),
            'total_customers' => $sorted->count(),
        ]);
    }

    // ═══════════════════════════════════════════
    //  API: Customer Behavior Daily (for chart)
    // ═══════════════════════════════════════════

    public function customerDaily(Request $request)
    {
        $store_id = $request->input('store_id');
        $contact_id = $request->input('contact_id');
        $year = (int) $request->input('year', date('Y'));
        $month = (int) $request->input('month', date('m'));

        if (!$store_id || !$contact_id) {
            return response()->json(['daily' => []]);
        }

        try {
            $data = DB::select(
                'SELECT * FROM get_customer_behavior_daily(?, ?, ?, ?)',
                [$store_id, $contact_id, $year, $month]
            );
        } catch (\Exception $e) {
            \Log::error('[PerilakuController] customerDaily RPC failed: ' . $e->getMessage());
            return response()->json(['daily' => [], 'error' => 'Gagal memuat data harian customer.'], 500);
        }

        $daily = collect($data)->map(function ($row) {
            return [
                'tanggal'   => $row->tanggal,
                'total'     => (float) $row->total,
                'frekuensi' => (int) $row->frekuensi,
            ];
        });

        return response()->json(['daily' => $daily]);
    }

    // ═══════════════════════════════════════════
    //  API: Customer Transaction History
    // ═══════════════════════════════════════════

    public function customerHistory(Request $request)
    {
        $store_id = $request->input('store_id');
        $contact_id = $request->input('contact_id');
        $year = (int) $request->input('year', date('Y'));
        $month = (int) $request->input('month', date('m'));

        if (!$store_id || !$contact_id) {
            return response()->json(['history' => []]);
        }

        try {
            $data = DB::select(
                'SELECT * FROM get_customer_transactions_history(?, ?, ?, ?)',
                [$store_id, $contact_id, $year, $month]
            );
        } catch (\Exception $e) {
            \Log::error('[PerilakuController] customerHistory RPC failed: ' . $e->getMessage());
            return response()->json(['history' => [], 'error' => 'Gagal memuat riwayat transaksi customer.'], 500);
        }

        $history = collect($data)->map(function ($row) {
            return [
                'tanggal'          => $row->tanggal,
                'jenis_kanal'      => $row->jenis_kanal,
                'total'            => (float) $row->total,
                'kembalian'        => (float) $row->kembalian,
                'transaction_uuid' => $row->transaction_uuid,
            ];
        });

        return response()->json(['history' => $history]);
    }

    // ═══════════════════════════════════════════
    //  API: Product Behavior Yearly
    // ═══════════════════════════════════════════

    public function productYearly(Request $request)
    {
        $store_id = $request->input('store_id');
        $year = (int) $request->input('year', date('Y'));
        $month = $request->input('month'); // optional
        $sort = $request->input('sort', 'omset'); // omset | frekuensi | laba
        $search = $request->input('search', '');

        if (!$store_id) {
            return response()->json(['products' => [], 'total_omset' => 0, 'total_products' => 0]);
        }

        // If month selected, call daily function instead
        if ($month) {
            return $this->productMonthly($store_id, $year, (int) $month, $sort, $search);
        }

        $cacheKey = "product_behavior_{$store_id}_{$year}";

        try {
            $data = Cache::remember($cacheKey, 1800, function () use ($store_id, $year) {
                return DB::select('SELECT * FROM get_product_behavior_yearly(?, ?)', [$store_id, $year]);
            });
        } catch (\Exception $e) {
            \Log::error('[PerilakuController] productYearly RPC failed: ' . $e->getMessage());
            return response()->json([
                'products'       => [],
                'total_omset'    => 0,
                'total_laba'     => 0,
                'total_freq'     => 0,
                'total_products' => 0,
                'mode'           => 'yearly',
                'error'          => 'Gagal memuat data dari database. Coba lagi beberapa saat.',
            ], 500);
        }

        // Group by product
        $grouped = collect($data)->groupBy('product_id')->map(function ($rows) {
            $first = $rows->first();
            $months = [];
            $totalOmset = 0;
            $totalLaba = 0;
            $totalFreq = 0;

            foreach ($rows as $row) {
                $months[] = [
                    'bulan'       => (int) $row->bulan,
                    'total_omset' => (float) $row->total_omset,
                    'total_laba'  => (float) $row->total_laba,
                    'frekuensi'   => (int) $row->frekuensi,
                ];
                $totalOmset += (float) $row->total_omset;
                $totalLaba += (float) $row->total_laba;
                $totalFreq += (int) $row->frekuensi;
            }

            return [
                'product_id'    => $first->product_id,
                'barcode'       => $first->barcode,
                'nama_produk'   => $first->nama_produk,
                'total_omset'   => $totalOmset,
                'total_laba'    => $totalLaba,
                'frekuensi'     => $totalFreq,
                'months'        => $months,
            ];
        })->values();

        // Filter by search
        if ($search) {
            $grouped = $grouped->filter(function ($item) use ($search) {
                $q = strtolower($search);
                return str_contains(strtolower($item['nama_produk']), $q)
                    || str_contains(strtolower($item['barcode'] ?? ''), $q);
            })->values();
        }

        // Sort
        $sortField = match ($sort) {
            'frekuensi' => 'frekuensi',
            'laba' => 'total_laba',
            default => 'total_omset',
        };
        $sorted = $grouped->sortByDesc($sortField)->values();

        return response()->json([
            'products'       => $sorted,
            'total_omset'    => $sorted->sum('total_omset'),
            'total_laba'     => $sorted->sum('total_laba'),
            'total_freq'     => $sorted->sum('frekuensi'),
            'total_products' => $sorted->count(),
            'mode'           => 'yearly',
        ]);
    }

    /**
     * Product monthly (daily breakdown) — called when month filter is active
     */
    private function productMonthly(string $store_id, int $year, int $month, string $sort, string $search)
    {
        $cacheKey = "product_behavior_{$store_id}_{$year}_{$month}";

        // Get yearly data first, then filter to specific month to get product list
        try {
            $yearlyData = Cache::remember("product_behavior_{$store_id}_{$year}", 1800, function () use ($store_id, $year) {
                return DB::select('SELECT * FROM get_product_behavior_yearly(?, ?)', [$store_id, $year]);
            });
        } catch (\Exception $e) {
            \Log::error('[PerilakuController] productMonthly RPC failed: ' . $e->getMessage());
            return response()->json([
                'products'       => [],
                'total_omset'    => 0,
                'total_laba'     => 0,
                'total_freq'     => 0,
                'total_products' => 0,
                'mode'           => 'monthly',
                'error'          => 'Gagal memuat data bulan ini.',
            ], 500);
        }

        // Filter to specific month
        $monthData = collect($yearlyData)->filter(function ($row) use ($month) {
            return (int) $row->bulan === $month;
        });

        $products = $monthData->map(function ($row) {
            return [
                'product_id'  => $row->product_id,
                'barcode'     => $row->barcode,
                'nama_produk' => $row->nama_produk,
                'total_omset' => (float) $row->total_omset,
                'total_laba'  => (float) $row->total_laba,
                'frekuensi'   => (int) $row->frekuensi,
            ];
        });

        // Filter by search
        if ($search) {
            $products = $products->filter(function ($item) use ($search) {
                $q = strtolower($search);
                return str_contains(strtolower($item['nama_produk']), $q)
                    || str_contains(strtolower($item['barcode'] ?? ''), $q);
            });
        }

        // Sort
        $sortField = match ($sort) {
            'frekuensi' => 'frekuensi',
            'laba' => 'total_laba',
            default => 'total_omset',
        };
        $sorted = $products->sortByDesc($sortField)->values();

        return response()->json([
            'products'       => $sorted,
            'total_omset'    => $sorted->sum('total_omset'),
            'total_laba'     => $sorted->sum('total_laba'),
            'total_freq'     => $sorted->sum('frekuensi'),
            'total_products' => $sorted->count(),
            'mode'           => 'monthly',
        ]);
    }

    // ═══════════════════════════════════════════
    //  API: Product Behavior Daily (for chart)
    // ═══════════════════════════════════════════

    public function productDaily(Request $request)
    {
        $store_id = $request->input('store_id');
        $product_id = $request->input('product_id');
        $year = (int) $request->input('year', date('Y'));
        $month = (int) $request->input('month', date('m'));

        if (!$store_id || !$product_id) {
            return response()->json(['daily' => []]);
        }

        try {
            $data = DB::select(
                'SELECT * FROM get_product_behavior_daily(?, ?, ?, ?)',
                [$store_id, $product_id, $year, $month]
            );
        } catch (\Exception $e) {
            \Log::error('[PerilakuController] productDaily RPC failed: ' . $e->getMessage());
            return response()->json(['daily' => [], 'error' => 'Gagal memuat data harian produk.'], 500);
        }

        $daily = collect($data)->map(function ($row) {
            return [
                'tanggal'     => $row->tanggal,
                'total_omset' => (float) $row->total_omset,
                'total_laba'  => (float) $row->total_laba,
                'frekuensi'   => (int) $row->frekuensi,
            ];
        });

        return response()->json(['daily' => $daily]);
    }

    // ═══════════════════════════════════════════
    //  API: Product Transaction History
    // ═══════════════════════════════════════════

    public function productHistory(Request $request)
    {
        $store_id = $request->input('store_id');
        $product_id = $request->input('product_id');
        $year = (int) $request->input('year', date('Y'));
        $month = (int) $request->input('month', date('m'));

        if (!$store_id || !$product_id) {
            return response()->json(['history' => []]);
        }

        try {
            $data = DB::select(
                'SELECT * FROM get_product_transactions_history(?, ?, ?, ?)',
                [$store_id, $product_id, $year, $month]
            );
        } catch (\Exception $e) {
            \Log::error('[PerilakuController] productHistory RPC failed: ' . $e->getMessage());
            return response()->json(['history' => [], 'error' => 'Gagal memuat riwayat transaksi produk.'], 500);
        }

        $history = collect($data)->map(function ($row) {
            return [
                'tanggal'          => $row->tanggal,
                'jenis_kanal'      => $row->jenis_kanal,
                'jmlh'             => (int) $row->jmlh,
                'harga_jual'       => (float) $row->harga_jual,
                'total_transaksi'  => (float) $row->total_transaksi,
                'kembalian'        => (float) $row->kembalian,
                'transaction_uuid' => $row->transaction_uuid,
            ];
        });

        return response()->json(['history' => $history]);
    }

    // ═══════════════════════════════════════════
    //  Detail Pages
    // ═══════════════════════════════════════════

    public function detailCustomer(Request $request, $contact_id)
    {
        $user = Auth::user();
        $outlets = collect();
        if ($user->role === 'owner') {
            $outlets = DB::table('store')->where('status_aktif', true)->get();
        } elseif ($user->role === 'kepala_toko' && $user->outlet_id) {
            $outlets = DB::table('store')->where('uuid', $user->outlet_id)->get();
        }

        $store_id = $request->input('store_id', session('perilaku_store_id'));
        $year = $request->input('year', date('Y'));

        return view('perilaku.detail_customer', [
            'contact_id' => $contact_id,
            'outlets'    => $outlets,
            'store_id'   => $store_id,
            'year'       => $year,
        ]);
    }

    public function detailProduk(Request $request, $product_id)
    {
        $user = Auth::user();
        $outlets = collect();
        if ($user->role === 'owner') {
            $outlets = DB::table('store')->where('status_aktif', true)->get();
        } elseif ($user->role === 'kepala_toko' && $user->outlet_id) {
            $outlets = DB::table('store')->where('uuid', $user->outlet_id)->get();
        }

        $store_id = $request->input('store_id', session('perilaku_store_id'));
        $year = $request->input('year', date('Y'));

        return view('perilaku.detail_produk', [
            'product_id' => $product_id,
            'outlets'    => $outlets,
            'store_id'   => $store_id,
            'year'       => $year,
        ]);
    }
}
