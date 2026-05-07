<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\Promo;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\Category;
use App\Models\StoreReview;
use App\Models\PaymentOrder;
use App\Models\PaymentOrderItem;
use App\Models\Contact;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class LandingController extends Controller
{
    public function index()
    {
        $outlets = Outlet::where('status_aktif', true)->get();
        $now = Carbon::now();
        $promos = Promo::with('stores')
            ->where('status', true)
            ->whereNotNull('image_banner')
            ->orderBy('tanggal_mulai', 'desc')
            ->get();

        $promoProducts = [];
        foreach ($promos as $promo) {
            $store = $promo->stores->first();

            $promoProducts[] = (object) [
                'nama_promo' => $promo->nama_promo,
                'image_banner' => self::resolveImageUrl($promo->image_banner),
                'deskripsi' => $promo->deskripsi,
                'outlet_name' => $store ? $store->nama : 'TWINS Bakery',
                'outlet_address' => $store ? $store->alamat : 'Semua Cabang'
            ];
        }

        $testimonials = StoreReview::with(['user', 'store'])
            ->where('rating', '>=', 4)
            ->orderBy('created_at', 'desc')
            ->limit(14)
            ->get();

        return view('welcome', compact('outlets', 'promoProducts', 'testimonials'));
    }

    public function showOutlet($id)
    {
        $outlet = Outlet::findOrFail($id);
        // 1. Ambil promo aktif untuk store ini
        $activePromos = Promo::where('status', true)
            ->whereHas('stores', function ($q) use ($outlet) {
                $q->where('store_id', $outlet->uuid);
            })
            ->with([
                'products' => function ($q) {
                    // Jangan hanya select uuid, tapi ambil semua data yang dibutuhkan View
                    $q->select('products.uuid', 'products.nama_produk', 'products.harga_jual', 'products.image_url', 'products.kategori_id');
                }
            ])
            ->get();

        // 2. Map produk ke diskon (Ambil diskon terbesar jika ada multiple)
        $productDiscounts = [];
        foreach ($activePromos as $promo) {
            foreach ($promo->products as $p) {
                $tipe = strtolower($p->pivot->tipe_diskon); // Paksa huruf kecil sesuai skema
                $nilai = (int) $p->pivot->nilai_diskon;

                $productDiscounts[$p->uuid] = [
                    'tipe' => $tipe,
                    'nilai' => $nilai,
                    'nama' => $promo->nama_promo
                ];
            }
        }

        $products = Product::with('priceLevels')
            ->join('product_store', 'products.uuid', '=', 'product_store.product_id')
            ->where('product_store.store_id', $outlet->uuid)
            ->select(
                'products.*',
                'product_store.stok as stok'
            )
            ->get()
            ->map(function ($p) use ($productDiscounts) {
                $originalPrice = (int) $p->harga_jual;
                $discountPrice = $originalPrice;
                $isDiscount = false;
                $discountLabel = '';

                if (isset($productDiscounts[$p->uuid])) {
                    $d = $productDiscounts[$p->uuid];
                    $isDiscount = true;
                    if ($d['tipe'] === 'persen') {
                        $discountPrice = $originalPrice - ($originalPrice * ($d['nilai'] / 100));
                        $discountLabel = $d['nilai'] . '%';
                    } else {
                        $discountPrice = $originalPrice - $d['nilai'];
                        $discountLabel = 'Rp ' . number_format($d['nilai'], 0, ',', '.');
                    }
                }

                return [
                    'id' => $p->uuid,
                    'name' => $p->nama_produk,
                    'price' => (int) $discountPrice,
                    'original_price' => $originalPrice,
                    'is_discount' => $isDiscount,
                    'discount_label' => $discountLabel,
                    'stok' => (int) $p->stok,
                    'category_id' => $p->kategori_id,
                    'category' => $p->kategori_id,
                    'img' => \App\Http\Controllers\LandingController::resolveImageUrl($p->image_url),
                    'price_levels' => $p->priceLevels->map(function ($level) {
                        return [
                            'uuid' => $level->uuid,
                            'product_id' => $level->product_id,
                            'jmlh' => (int) $level->jmlh,
                            'harga' => (int) $level->harga,
                        ];
                    })->values()->all(),
                    'rating' => 4.8
                ];
            })->values()->all();

        $categories = Category::all()->map(function ($cat) {
            return [
                'id' => $cat->uuid,
                'name' => $cat->nama_category
            ];
        });

        $reviews = StoreReview::with('user')
            ->where('store_id', $outlet->uuid)
            ->orderBy('created_at', 'desc')
            ->get();

        $stockMap = [];
        foreach ($products as $p) {
            $stockMap[$p['id']] = $p['stok'];
        }

        $storedDeliveryAddress = session('delivery_address.' . $outlet->uuid);
        $deliveryPreference = null;

        if (is_array($storedDeliveryAddress) && !empty($storedDeliveryAddress['address'])) {
            $coords = $storedDeliveryAddress['coordinates'] ?? null;
            $validCoordinates = is_array($coords)
                && isset($coords['lat'], $coords['lng'])
                && is_numeric($coords['lat'])
                && is_numeric($coords['lng']);

            $deliveryPreference = [
                'address' => trim((string) $storedDeliveryAddress['address']),
                'coordinates' => $validCoordinates ? [
                    'lat' => (float) $coords['lat'],
                    'lng' => (float) $coords['lng'],
                ] : null,
            ];
        }

        // Ambil promo untuk banner (Sama seperti activePromos tapi dengan relasi lengkap)
        $discounts = $activePromos;

        return view('user', compact('outlet', 'products', 'categories', 'reviews', 'discounts', 'stockMap', 'deliveryPreference'));
    }

    public function saveDeliveryAddress(Request $request, $id)
    {
        $outlet = Outlet::findOrFail($id);

        $validated = $request->validate([
            'address' => ['required', 'string', 'max:1000'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'recipient_phone' => ['required', 'string', 'max:20'],
            'coordinates' => ['nullable', 'array'],
            'coordinates.lat' => ['nullable', 'numeric', 'between:-90,90'],
            'coordinates.lng' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $coordinates = null;
        if (isset($validated['coordinates']['lat'], $validated['coordinates']['lng'])) {
            $coordinates = [
                'lat' => (float) $validated['coordinates']['lat'],
                'lng' => (float) $validated['coordinates']['lng'],
            ];
        }

        $deliveryData = [
            'address' => trim($validated['address']),
            'recipient_name' => $validated['recipient_name'],
            'recipient_phone' => $validated['recipient_phone'],
            'coordinates' => $coordinates,
            'updated_at' => now()->toIso8601String(),
        ];

        session()->put('delivery_address.' . $outlet->uuid, $deliveryData);

        try {
            // Gunakan data akun jika login
            $user = Auth::user();
            $finalName = ($user && $user->username) ? $user->username : $validated['recipient_name'];
            $finalPhone = ($user && $user->no_hp) ? $user->no_hp : $validated['recipient_phone'];

            $contactData = [
                'nama' => $finalName,
                'tipe' => 'customer'
            ];

            $matchCriteria = ['no_hp' => $finalPhone];

            if (\Illuminate\Support\Facades\Schema::hasColumn('contacts', 'store_id')) {
                $matchCriteria['store_id'] = $outlet->uuid;
            }

            if ($user && \Illuminate\Support\Facades\Schema::hasColumn('contacts', 'user_id')) {
                $contactData['user_id'] = $user->uuid;
            }

            Contact::updateOrCreate($matchCriteria, $contactData);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gagal menyimpan kontak otomatis: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Alamat pengiriman dan kontak berhasil disimpan.',
            'delivery' => $deliveryData,
        ]);
    }

    public function createCheckoutToken(Request $request, $id)
    {
        $outlet = Outlet::findOrFail($id);

        $validated = $request->validate([
            'recipient_name' => ['required', 'string', 'max:150'],
            'recipient_phone' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:1000'],
            'coordinates' => ['nullable', 'array'],
            'coordinates.lat' => ['nullable', 'numeric', 'between:-90,90'],
            'coordinates.lng' => ['nullable', 'numeric', 'between:-180,180'],
            'distance_km' => ['nullable', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'shipping_fee' => ['required', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'string', 'max:100'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:1'],
            'items.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $serverKey = (string) config('services.midtrans.server_key', '');
        if ($serverKey === '') {
            return response()->json([
                'message' => 'Midtrans belum dikonfigurasi (server key kosong).',
            ], 422);
        }

        $discountPercent = (float) ($validated['discount_percent'] ?? 0);
        $shippingFee = (int) ceil((float) $validated['shipping_fee']);
        $distanceKm = (float) ($validated['distance_km'] ?? 0);

        $itemDetails = [];
        $dbItems = [];
        $subTotal = 0;
        $itemDiscountTotal = 0;

        foreach ($validated['items'] as $idx => $item) {
            $qty = (int) $item['qty'];
            $unitPrice = (int) ceil((float) $item['unit_price']);
            $lineSubtotal = $unitPrice * $qty;
            $subTotal += $lineSubtotal;

            $itemDiscountPercent = (float) ($item['discount_percent'] ?? 0);
            $itemDiscountAmount = (int) ceil((float) ($item['discount_amount'] ?? 0));

            if ($itemDiscountAmount <= 0 && $itemDiscountPercent > 0) {
                $itemDiscountAmount = (int) round($lineSubtotal * $itemDiscountPercent);
            }

            if ($itemDiscountAmount > $lineSubtotal) {
                $itemDiscountAmount = $lineSubtotal;
            }

            if ($itemDiscountPercent <= 0 && $itemDiscountAmount > 0 && $lineSubtotal > 0) {
                $itemDiscountPercent = round($itemDiscountAmount / $lineSubtotal, 4);
            }

            $finalPrice = max(0, $lineSubtotal - $itemDiscountAmount);
            $itemDiscountTotal += $itemDiscountAmount;

            $safeName = Str::limit(trim((string) $item['name']), 50, '');
            if ($safeName === '') {
                $safeName = 'Item ' . ($idx + 1);
            }

            $safeDbName = Str::limit(trim((string) $item['name']), 255, '');
            if ($safeDbName === '') {
                $safeDbName = $safeName;
            }

            $itemDetails[] = [
                'id' => trim((string) ($item['product_id'] ?? 'ITEM-' . ($idx + 1))),
                'price' => $unitPrice,
                'quantity' => $qty,
                'name' => $safeName,
            ];

            if ($itemDiscountAmount > 0) {
                $itemDetails[] = [
                    'id' => trim((string) ($item['product_id'] ?? 'ITEM-' . ($idx + 1))) . '-DISC',
                    'price' => -$itemDiscountAmount,
                    'quantity' => 1,
                    'name' => 'Diskon ' . $safeName,
                ];
            }

            $dbItems[] = [
                'product_id' => isset($item['product_id']) ? trim((string) $item['product_id']) : null,
                'product_name' => $safeDbName,
                'unit_price' => $unitPrice,
                'quantity' => $qty,
                'subtotal' => $lineSubtotal,
                'discount_percent' => $itemDiscountPercent,
                'discount_amount' => $itemDiscountAmount,
                'final_price' => $finalPrice,
                'meta' => null,
            ];
        }

        if ($subTotal <= 0) {
            return response()->json([
                'message' => 'Subtotal transaksi tidak valid.',
            ], 422);
        }

        $subtotalAfterItemDiscount = max(0, $subTotal - $itemDiscountTotal);
        $globalDiscountAmount = (int) round($subtotalAfterItemDiscount * $discountPercent);
        $grossAmount = max(1, $subtotalAfterItemDiscount - $globalDiscountAmount + $shippingFee);

        if ($globalDiscountAmount > 0) {
            $itemDetails[] = [
                'id' => 'DISKON',
                'price' => -$globalDiscountAmount,
                'quantity' => 1,
                'name' => 'Diskon Promo',
            ];
        }

        if ($shippingFee > 0) {
            $itemDetails[] = [
                'id' => 'ONGKIR',
                'price' => $shippingFee,
                'quantity' => 1,
                'name' => 'Biaya Pengiriman',
            ];
        }

        $orderId = 'TWINS-' . strtoupper(Str::random(6)) . '-' . now()->format('YmdHis');
        $isProduction = (bool) config('services.midtrans.is_production', false);
        $snapUrl = $isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        $user = Auth::user();
        $hasCoordinates = isset($validated['coordinates']['lat'], $validated['coordinates']['lng']);

        $paymentOrder = DB::transaction(function () use ($orderId, $outlet, $user, $validated, $hasCoordinates, $distanceKm, $dbItems, $subTotal, $discountPercent, $shippingFee, $grossAmount, $itemDiscountTotal, $globalDiscountAmount, $subtotalAfterItemDiscount) {
            $order = PaymentOrder::create([
                'order_code' => $orderId,
                'midtrans_order_id' => $orderId,
                'user_id' => $user ? (string) $user->getAuthIdentifier() : null,
                'outlet_id' => (string) $outlet->uuid,
                'recipient_name' => $validated['recipient_name'],
                'recipient_phone' => $validated['recipient_phone'],
                'delivery_address' => $validated['address'],
                'delivery_lat' => $hasCoordinates ? (float) $validated['coordinates']['lat'] : null,
                'delivery_lng' => $hasCoordinates ? (float) $validated['coordinates']['lng'] : null,
                'delivery_distance_km' => $distanceKm,
                'items_count' => count($dbItems),
                'subtotal_amount' => $subTotal,
                'discount_percent' => $discountPercent,
                'shipping_fee' => $shippingFee,
                'total_amount' => $grossAmount,
                'payment_status' => 'pending',
                'meta' => [
                    'source' => 'landing_checkout',
                    'item_discount_total' => $itemDiscountTotal,
                    'global_discount_amount' => $globalDiscountAmount,
                    'subtotal_after_item_discount' => $subtotalAfterItemDiscount,
                ],
            ]);

            // Sinkronisasi otomatis ke tabel contacts
            try {
                // Prioritas: Gunakan Nama & No HP dari Akun yang sedang Login
                // Jika tidak login, gunakan Nama & No HP Penerima
                $finalName = ($user && $user->username) ? $user->username : $validated['recipient_name'];
                $finalPhone = ($user && $user->no_hp) ? $user->no_hp : $validated['recipient_phone'];

                $contactData = [
                    'nama' => $finalName,
                    'tipe' => 'customer'
                ];

                $matchCriteria = ['no_hp' => $finalPhone];
                if (\Illuminate\Support\Facades\Schema::hasColumn('contacts', 'store_id')) {
                    $matchCriteria['store_id'] = $outlet->uuid;
                }
                if ($user && \Illuminate\Support\Facades\Schema::hasColumn('contacts', 'user_id')) {
                    $contactData['user_id'] = $user->uuid;
                }

                Contact::updateOrCreate($matchCriteria, $contactData);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Gagal sinkronisasi kontak saat checkout: ' . $e->getMessage());
            }

            if (!empty($dbItems)) {
                $rows = [];
                $now = now();
                foreach ($dbItems as $item) {
                    $rows[] = [
                        'payment_order_id' => $order->id,
                        'product_id' => $item['product_id'] ?: null,
                        'product_name' => $item['product_name'],
                        'unit_price' => $item['unit_price'],
                        'quantity' => $item['quantity'],
                        'subtotal' => $item['subtotal'],
                        'discount_percent' => $item['discount_percent'],
                        'discount_amount' => $item['discount_amount'],
                        'final_price' => $item['final_price'],
                        'meta' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                PaymentOrderItem::insert($rows);
            }

            return $order;
        });

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'item_details' => $itemDetails,
            'customer_details' => [
                'first_name' => $validated['recipient_name'],
                'phone' => $validated['recipient_phone'],
                'billing_address' => [
                    'address' => $validated['address'],
                ],
                'shipping_address' => [
                    'address' => $validated['address'],
                ],
            ],
            'custom_field1' => $outlet->nama,
            'custom_field2' => 'Jarak: ' . number_format($distanceKm, 2, '.', '') . ' km',
        ];

        $notificationUrl = trim((string) config('services.midtrans.notification_url', ''));
        if ($notificationUrl === '') {
            $notificationUrl = rtrim($request->getSchemeAndHttpHost(), '/') . '/midtrans/webhook';
        }

        $payload['notification_url'] = $notificationUrl;

        $callbackBaseUrl = trim((string) config('services.midtrans.callback_base_url', ''));
        if ($callbackBaseUrl !== '') {
            $payload['callbacks'] = [
                'finish' => rtrim($callbackBaseUrl, '/') . '/outlet/' . $outlet->uuid,
                'error' => rtrim($callbackBaseUrl, '/') . '/outlet/' . $outlet->uuid,
            ];
        }

        try {
            $response = Http::withBasicAuth($serverKey, '')
                ->acceptJson()
                ->post($snapUrl, $payload);

            if (!$response->successful()) {
                return response()->json([
                    'message' => 'Gagal membuat transaksi Midtrans.',
                    'midtrans_error' => $response->json() ?: $response->body(),
                ], 422);
            }

            $json = $response->json();
            if (!is_array($json) || empty($json['token'])) {
                $paymentOrder->update([
                    'payment_status' => 'failed',
                    'midtrans_response' => [
                        'message' => 'Token Midtrans tidak ditemukan.',
                        'raw' => $json,
                    ],
                ]);

                return response()->json([
                    'message' => 'Token Midtrans tidak ditemukan pada response.',
                ], 422);
            }

            $paymentOrder->update([
                'snap_token' => (string) $json['token'],
                'midtrans_response' => $json,
                'payment_status' => 'pending',
            ]);

            return response()->json([
                'message' => 'Snap token berhasil dibuat.',
                'order_id' => $orderId,
                'snap_token' => $json['token'],
                'redirect_url' => $json['redirect_url'] ?? null,
                'gross_amount' => $grossAmount,
                'payment_order_id' => $paymentOrder->id,
            ]);
        } catch (\Throwable $e) {
            $paymentOrder->update([
                'payment_status' => 'failed',
                'midtrans_response' => [
                    'message' => $e->getMessage(),
                ],
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan saat menghubungi Midtrans.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function storeReview(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        $outlet = Outlet::findOrFail($id);
        $exists = StoreReview::where('store_id', $outlet->uuid)
            ->where('user_id', Auth::id())
            ->exists();

        if ($exists) {
            return back()->with('error', 'Anda sudah memberikan ulasan untuk toko ini.');
        }

        DB::transaction(function () use ($request, $outlet) {
            StoreReview::create([
                'store_id' => $outlet->uuid,
                'user_id' => Auth::id(),
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);

            // Update average rating in store table
            $avgRating = StoreReview::where('store_id', $outlet->uuid)->avg('rating');
            $outlet->update(['rating' => $avgRating]);
        });

        return back()->with('success', 'Terima kasih atas ulasan Anda!');
    }

    // Return payment order JSON for frontend detail modal
    public function showPaymentOrder(Request $request, $id, $orderId)
    {
        $outlet = Outlet::findOrFail($id);

        $query = PaymentOrder::with('items')->where('outlet_id', (string) $outlet->uuid);

        if (is_numeric($orderId)) {
            $query->where('id', (int) $orderId);
        } else {
            $query->where(function ($q) use ($orderId) {
                $q->where('order_code', $orderId)
                    ->orWhere('midtrans_order_id', $orderId);
            });
        }

        $order = $query->first();
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json(['order' => $order]);
    }

    public function generalReview(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:store,uuid',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        $outlet = Outlet::findOrFail($request->store_id);

        $exists = StoreReview::where('store_id', $outlet->uuid)
            ->where('user_id', Auth::id())
            ->exists();

        if ($exists) {
            return back()->with('error', 'Anda sudah memberikan ulasan untuk cabang ini.');
        }

        DB::transaction(function () use ($request, $outlet) {
            StoreReview::create([
                'store_id' => $outlet->uuid,
                'user_id' => Auth::id(),
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);

            $avgRating = StoreReview::where('store_id', $outlet->uuid)->avg('rating');
            $outlet->update(['rating' => $avgRating]);
        });

        return back()->with('success', 'Terima kasih atas ulasan Anda!');
    }

    public static function resolveImageUrl($path)
    {
        if (!$path) {
            return asset('images/placeholder-product.png');
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        // Clean path from /storage/ prefix or leading slashes
        $cleanPath = ltrim($path, '/');
        if (str_starts_with($cleanPath, 'storage/')) {
            $cleanPath = substr($cleanPath, 8);
        }

        // Strip 'products/' if it's already in the path to avoid duplication with the base URL
        if (str_starts_with($cleanPath, 'products/')) {
            $cleanPath = substr($cleanPath, 9);
        }

        // Use the specific Cloudinary URL provided by the user
        return "https://res.cloudinary.com/dryxdouod/image/upload/v1777305563/products/" . $cleanPath;
    }

    public static function uploadToCloudinary($file, $folder = 'twins')
    {
        $cloudName = env('CLOUDINARY_CLOUD_NAME');
        $uploadPreset = env('CLOUDINARY_UPLOAD_PRESET', 'ml_default');

        if (!$cloudName) {
            return null;
        }

        $url = "https://api.cloudinary.com/v1_1/{$cloudName}/image/upload";

        try {
            // Jika file adalah base64 string
            if (is_string($file) && str_starts_with($file, 'data:image')) {
                $response = Http::asForm()->post($url, [
                    'file' => $file,
                    'upload_preset' => $uploadPreset,
                    'folder' => $folder
                ]);
            }
            // Jika file adalah UploadedFile object
            else {
                $response = Http::attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                    ->post($url, [
                        'upload_preset' => $uploadPreset,
                        'folder' => $folder
                    ]);
            }

            if ($response->successful()) {
                return $response->json()['secure_url'];
            }

            \Illuminate\Support\Facades\Log::error('Cloudinary Upload Failed: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Cloudinary Exception: ' . $e->getMessage());
            return null;
        }
    }

    public function getUserHistory($id)
    {
        $outlet = Outlet::findOrFail($id);
        $user = Auth::user();

        if (!$user) {
            return response()->json(['history' => []]);
        }

        $orders = PaymentOrder::with('items')
            ->where('outlet_id', (string) $outlet->uuid)
            ->where('user_id', (string) $user->getAuthIdentifier())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->order_code,
                    'payment_order_db_id' => $order->id,
                    'date' => $order->created_at->format('d/m/Y H:i'),
                    'items' => $order->items->map(function ($item) {
                        return [
                            'name' => $item->product_name,
                            'qty' => $item->quantity,
                            'price' => $item->unit_price,
                            'product_id' => $item->product_id
                        ];
                    }),
                    'total' => (float) $order->total_amount,
                    'shipping_fee' => (float) $order->shipping_fee,
                    'recipient_name' => $order->recipient_name,
                    'recipient_phone' => $order->recipient_phone,
                    'address' => $order->delivery_address,
                    'payment_status' => $order->payment_status,
                ];
            });

        return response()->json(['history' => $orders]);
    }
}
