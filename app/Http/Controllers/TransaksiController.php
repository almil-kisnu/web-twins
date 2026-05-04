<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Promo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Controllers\LandingController;
use App\Models\Fitur;

class TransaksiController extends Controller
{
    public function index()
    {
        return $this->riwayat();
    }

    public function riwayat()
    {
        $data = \App\Models\PaymentOrder::orderBy('created_at', 'desc')->get()->map(function($trx) {
            $meta = $trx->meta ?: [];
            $itemDiscount = (int)($meta['item_discount_total'] ?? 0);
            $globalDiscount = (int)($meta['global_discount_amount'] ?? 0);
            $totalDiscount = $itemDiscount + $globalDiscount;

            return [
                'id' => $trx->order_code,
                'tanggal' => $trx->created_at->format('d M Y H:i'),
                'kasir' => 'Online Checkout',
                'pelanggan' => $trx->recipient_name,
                'qty' => $trx->items_count,
                'total' => 'Rp ' . number_format($trx->total_amount, 0, ',', '.'),
                'diskon' => $totalDiscount > 0 ? '-Rp ' . number_format($totalDiscount, 0, ',', '.') : '-',
                'status' => ucfirst($trx->payment_status)
            ];
        });

        $sub_menus = Fitur::where('parent_id', 3)->orderBy('id')->get();
        return view('transaksi.riwayat', compact('data', 'sub_menus'));
    }

    public function diskon()
    {
        // Auto-fix & Debug columns
        try {
            $columns = \Illuminate\Support\Facades\Schema::getColumnListing('promo');
            \Illuminate\Support\Facades\Log::info('Kolom Tabel Promo: ' . implode(', ', $columns));
            
            if (!in_array('kode_promo', $columns)) {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE promo ADD COLUMN kode_promo VARCHAR(255) DEFAULT NULL");
                \Illuminate\Support\Facades\Log::info('Berhasil menambah kolom kode_promo secara paksa.');
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gagal akses/ubah tabel promo: ' . $e->getMessage());
        }

        $diskons = Promo::orderBy('tanggal_mulai', 'desc')->get();
        $products = \App\Models\Product::orderBy('nama_produk', 'asc')->get();
        $outlets = \App\Models\Outlet::orderBy('nama', 'asc')->get();
        
        $sub_menus = Fitur::where('parent_id', 3)->orderBy('id')->get();
        return view('transaksi.diskon', compact('diskons', 'products', 'outlets', 'sub_menus'));
    }

    public function storeDiskon(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('Memulai penyimpanan promo:', $request->except(['image_banner']));

        try {
            $request->validate([
                'nama_promo' => 'required|string|max:255',
                'kode_promo' => 'nullable|string|max:50|unique:promo,kode_promo',
                'tipe' => 'required|string',
                'nilai' => 'required|numeric',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date',
                'image_banner' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
                'product_ids' => 'nullable|array',
                'store_ids' => 'nullable|array'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::warning('Validasi promo gagal:', $e->errors());
            throw $e;
        }

        $data = $request->except(['product_ids', 'store_ids']);
        $data['status'] = $request->status == 'Aktif' ? true : false;

        if ($request->hasFile('image_banner')) {
            try {
                $cloudinaryUrl = LandingController::uploadToCloudinary($request->file('image_banner'), 'promos');
                if ($cloudinaryUrl) {
                    $data['image_banner'] = $cloudinaryUrl;
                } else {
                    throw new \Exception('Cloudinary return null');
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Cloudinary upload failed, falling back to local: ' . $e->getMessage());
                $file = $request->file('image_banner');
                $filename = time() . '_' . Str::slug($request->nama_promo) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('promos', $filename, 'public');
                $data['image_banner'] = '/storage/' . $path;
            }
        }

        $promo = Promo::create($data);
        \Illuminate\Support\Facades\Log::info('Promo created successfully with UUID: ' . $promo->uuid);

        // Simpan Relasi Produk
        if ($request->has('product_ids')) {
            foreach ($request->product_ids as $prodId) {
                \Illuminate\Support\Facades\DB::table('promo_products')->insert([
                    'uuid' => \Illuminate\Support\Str::uuid(),
                    'promo_id' => $promo->uuid,
                    'product_id' => $prodId,
                    'tipe_diskon' => 'persen',
                    'nilai_diskon' => $promo->nilai
                ]);
            }
        }

        // Simpan Relasi Toko/Outlet
        if ($request->has('store_ids')) {
            foreach ($request->store_ids as $storeId) {
                \Illuminate\Support\Facades\DB::table('promo_store')->insert([
                    'uuid' => \Illuminate\Support\Str::uuid(),
                    'promo_id' => $promo->uuid,
                    'store_id' => $storeId
                ]);
            }
        }
        
        return redirect()->route('transaksi.diskon')->with('success', 'Promo berhasil ditambahkan');
    }

    public function updateDiskon(Request $request, $id)
    {
        // Auto-fix missing columns right before update
        try {
            $columns = \Illuminate\Support\Facades\Schema::getColumnListing('promo');
            if (!in_array('kode_promo', $columns)) {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE promo ADD COLUMN kode_promo VARCHAR(255) DEFAULT NULL");
            }
        } catch (\Exception $e) {
            // Log if something really goes wrong
            \Illuminate\Support\Facades\Log::error('Gagal auto-fix update: ' . $e->getMessage());
        }

        $promo = Promo::findOrFail($id);
        
        $request->validate([
            'nama_promo' => 'required|string|max:255',
            'kode_promo' => 'nullable|string|max:50|unique:promo,kode_promo,' . $promo->uuid . ',uuid',
            'tipe' => 'required|string',
            'nilai' => 'required|numeric',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date',
            'image_banner' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'product_ids' => 'nullable|array'
        ]);

        $updateData = $request->except(['product_ids', 'store_ids', '_token', '_method']);
        $updateData['status'] = $request->status == 'Aktif' ? true : false;

        if ($request->hasFile('image_banner')) {
            $cloudinaryUrl = LandingController::uploadToCloudinary($request->file('image_banner'), 'promos');
            if ($cloudinaryUrl) {
                $updateData['image_banner'] = $cloudinaryUrl;
            } else {
                $file = $request->file('image_banner');
                $filename = time() . '_' . Str::slug($request->nama_promo) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('promos', $filename, 'public');
                $updateData['image_banner'] = '/storage/' . $path;
            }
        }

        $promo->update($updateData);

        // Update Relasi Produk jika dikirim
        if ($request->has('product_ids')) {
            \Illuminate\Support\Facades\DB::table('promo_products')->where('promo_id', $promo->uuid)->delete();
            foreach ($request->product_ids as $prodId) {
                \Illuminate\Support\Facades\DB::table('promo_products')->insert([
                    'uuid' => \Illuminate\Support\Str::uuid(),
                    'promo_id' => $promo->uuid,
                    'product_id' => $prodId,
                    'tipe_diskon' => 'persen',
                    'nilai_diskon' => $promo->nilai
                ]);
            }
        }
        
        return redirect()->route('transaksi.diskon')->with('success', 'Promo berhasil diperbarui');
    }

    public function destroyDiskon($id)
    {
        $promo = Promo::findOrFail($id);
        if ($promo->image_banner && !str_starts_with($promo->image_banner, 'http')) {
            $cleanPath = str_replace('/storage/', '', $promo->image_banner);
            Storage::disk('public')->delete($cleanPath);
        }
        $promo->delete();
        
        return redirect()->route('transaksi.diskon')->with('success', 'Promo berhasil dihapus');
    }
}