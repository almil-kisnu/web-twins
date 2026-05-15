<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;

use Illuminate\Support\Facades\Schema;

class KontakController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $parentFitur = \App\Models\Fitur::where('nama', 'Kelola Kontak')->first();
        $sub_menus = $parentFitur ? \App\Models\Fitur::where('parent_id', $parentFitur->id)->orderBy('id')->get() : collect();

        $hasPelanggan = false;
        $hasSupplier = false;

        foreach($sub_menus as $sm) {
            if ($sm->nama == 'Pelanggan' && $user->hasFeature($sm->id)) $hasPelanggan = true;
            if ($sm->nama == 'Supplier' && $user->hasFeature($sm->id)) $hasSupplier = true;
        }

        $active_tab = $request->query('active_tab', 'pelanggan');
        if ($active_tab == 'pelanggan' && !$hasPelanggan) $active_tab = '';
        if ($active_tab == 'supplier' && !$hasSupplier) $active_tab = '';

        if (!$active_tab) {
            if ($hasPelanggan) $active_tab = 'pelanggan';
            elseif ($hasSupplier) $active_tab = 'supplier';
        }

        $sort = $request->get('sort', 'terbaru');
        $order = $sort == 'terlama' ? 'asc' : 'desc';
        
        // Cek apakah kolom created_at ada, jika tidak gunakan uuid sebagai fallback
        $sortBy = Schema::hasColumn('contacts', 'created_at') ? 'created_at' : 'uuid';

        $query = Contact::where('tipe', 'customer');
        
        // Menghitung transaksi dan mengambil username otomatis dengan Super Normalisasi
        $query->select('contacts.*')
            ->selectSub(function ($q) {
                $q->from('payment_orders')
                  ->whereIn('payment_status', ['paid', 'settlement', 'success', 'capture', 'pending'])
                  ->where(function($sub) {
                      // 1. Cari berdasarkan User ID yang cocok dengan kontak ini (via HP/Nama di tabel users)
                      $sub->whereIn('payment_orders.user_id', function($sq) {
                          $sq->selectRaw("uuid::text")
                             ->from('users')
                             ->whereRaw("regexp_replace(regexp_replace(users.no_hp, '[^0-9]', '', 'g'), '^(0|62)', '') = regexp_replace(regexp_replace(contacts.no_hp, '[^0-9]', '', 'g'), '^(0|62)', '')")
                             ->orWhereRaw("LOWER(TRIM(users.username)) = LOWER(TRIM(contacts.nama))");
                      })
                      // 2. Cari berdasarkan HP/Nama langsung di tabel orders (sebagai backup)
                      ->orWhereRaw("regexp_replace(regexp_replace(payment_orders.recipient_phone, '[^0-9]', '', 'g'), '^(0|62)', '') = regexp_replace(regexp_replace(contacts.no_hp, '[^0-9]', '', 'g'), '^(0|62)', '')")
                      ->orWhereRaw("LOWER(TRIM(payment_orders.recipient_name)) = LOWER(TRIM(contacts.nama))");
                  })
                  ->selectRaw('count(*)');
            }, 'total_transaksi')
            ->selectSub(function ($q) {
                $hasUserId = Schema::hasColumn('contacts', 'user_id');
                $q->from('users')
                  ->where(function($sub) use ($hasUserId) {
                      $sub->whereRaw("regexp_replace(regexp_replace(users.no_hp, '[^0-9]', '', 'g'), '^(0|62)', '') = regexp_replace(regexp_replace(contacts.no_hp, '[^0-9]', '', 'g'), '^(0|62)', '')")
                          ->orWhereRaw("LOWER(TRIM(users.username)) = LOWER(TRIM(contacts.nama))")
                          ->orWhereIn('users.uuid', function($sq) {
                              $sq->selectRaw("user_id::uuid")
                                 ->from('payment_orders')
                                 ->whereRaw("user_id IS NOT NULL")
                                 ->whereRaw("regexp_replace(regexp_replace(payment_orders.recipient_phone, '[^0-9]', '', 'g'), '^(0|62)', '') = regexp_replace(regexp_replace(contacts.no_hp, '[^0-9]', '', 'g'), '^(0|62)', '')");
                          });
                      if ($hasUserId) {
                          $sub->orWhereRaw("users.uuid::text = contacts.user_id::text");
                      }
                  })
                  ->select('username')
                  ->limit(1);
            }, 'matching_username')
            ->selectSub(function ($q) {
                $hasUserId = Schema::hasColumn('contacts', 'user_id');
                $q->from('users')
                  ->where(function($sub) use ($hasUserId) {
                      $sub->whereRaw("regexp_replace(regexp_replace(users.no_hp, '[^0-9]', '', 'g'), '^(0|62)', '') = regexp_replace(regexp_replace(contacts.no_hp, '[^0-9]', '', 'g'), '^(0|62)', '')")
                          ->orWhereRaw("LOWER(TRIM(users.username)) = LOWER(TRIM(contacts.nama))")
                          ->orWhereIn('users.uuid', function($sq) {
                              $sq->selectRaw("user_id::uuid")
                                 ->from('payment_orders')
                                 ->whereRaw("user_id IS NOT NULL")
                                 ->whereRaw("regexp_replace(regexp_replace(payment_orders.recipient_phone, '[^0-9]', '', 'g'), '^(0|62)', '') = regexp_replace(regexp_replace(contacts.no_hp, '[^0-9]', '', 'g'), '^(0|62)', '')");
                          });
                      if ($hasUserId) {
                          $sub->orWhereRaw("users.uuid::text = contacts.user_id::text");
                      }
                  })
                  ->select('email')
                  ->limit(1);
            }, 'matching_email');

        if (Schema::hasColumn('contacts', 'user_id')) {
            $query->with(['user', 'paymentOrders.items.product']);
        }
        
        $pelanggan = $query->orderBy($sortBy, $order)->get();
        $supplier = Contact::where('tipe', 'supplier');
        if (Schema::hasColumn('contacts', 'user_id')) {
            $supplier->with(['user', 'paymentOrders.items.product']);
        }
        $supplier = $supplier->orderBy($sortBy, $order)->get();
        $users = \App\Models\User::orderBy('username')->get();
        $orders = \App\Models\PaymentOrder::orderBy('created_at', 'desc')->get();

        // QUICK STATS
        $totalPelanggan = Contact::where('tipe', 'customer')->count();
        $aktifBulanIni = \App\Models\PaymentOrder::whereIn('payment_status', ['paid', 'settlement', 'success', 'capture', 'pending'])
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->distinct()
            ->count('user_id');
        $topSpender = $pelanggan->sortByDesc('total_transaksi')->first();

        return view('kontak.index', compact('pelanggan', 'supplier', 'orders', 'sort', 'users', 'totalPelanggan', 'aktifBulanIni', 'topSpender', 'hasPelanggan', 'hasSupplier', 'sub_menus', 'active_tab'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'tipe' => 'required|in:customer,supplier',
        ]);

        Contact::create([
            'nama' => $request->nama,
            'no_hp' => $request->no_hp,
            'tipe' => $request->tipe,
        ]);

        return redirect()->route('kontak.index')->with('success', 'Kontak berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
        ]);

        $contact = Contact::findOrFail($id);
        $contact->update([
            'nama' => $request->nama,
            'no_hp' => $request->no_hp,
        ]);

        return redirect()->route('kontak.index')->with('success', 'Kontak berhasil diperbarui');
    }

    public function destroy($id)
    {
        $contact = Contact::findOrFail($id);
        $contact->delete();

        return redirect()->route('kontak.index')->with('success', 'Kontak berhasil dihapus');
    }

    public function syncFromOrders()
    {
        // Ambil semua user_id unik dari payment_orders
        $userIds = \App\Models\PaymentOrder::whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id');

        $syncedCount = 0;
        $updatedCount = 0;

        foreach ($userIds as $userId) {
            // Cari user di tabel users
            $user = \App\Models\User::where('uuid', $userId)->first();
            
            if ($user) {
                // Gunakan updateOrCreate untuk menghindari duplikasi berdasarkan user_id atau no_hp
                // Kita prioritaskan user_id jika kolomnya ada
                $hasUserId = Schema::hasColumn('contacts', 'user_id');
                
                $contact = null;
                if ($hasUserId) {
                    $contact = Contact::where('user_id', $user->uuid)->first();
                }
                
                if (!$contact && $user->no_hp) {
                    $contact = Contact::where('no_hp', $user->no_hp)->first();
                }

                $updateData = [
                    'nama' => $user->username,
                    'no_hp' => $user->no_hp ?? ($contact ? $contact->no_hp : null),
                    'store_id' => $user->store_id ?? ($contact ? $contact->store_id : null),
                ];
                
                if ($hasUserId) {
                    $updateData['user_id'] = $user->uuid;
                }

                if ($contact) {
                    $contact->update($updateData);
                    $updatedCount++;
                } else {
                    $createData = $updateData;
                    $createData['tipe'] = 'customer';
                    Contact::create($createData);
                    $syncedCount++;
                }
            }
        }

        return redirect()->route('kontak.index')->with('success', "$syncedCount kontak baru ditambahkan dan $updatedCount kontak diperbarui dari data pesanan.");
    }
}
