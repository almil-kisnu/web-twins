<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Models\PaymentMethod;
use Illuminate\Support\Str;

class KeuanganController extends Controller
{
    public function index(Request $request)
    {
        return $this->manage($request);
    }

    public function manage(Request $request)
    {
        $user = auth()->user();
        
        // 1. Data Cashbox
        $cashboxes = PaymentMethod::orderBy('nama_metode', 'asc')->get();

        // 2. Data Arus Uang
        // Filter Outlet
        $outlets = collect();
        if ($user->role === 'owner') {
            $outlets = \App\Models\Outlet::all();
        } elseif ($user->role === 'kepala_toko' && $user->store_id) {
            $outlets = \App\Models\Outlet::where('uuid', $user->store_id)->get();
        }
        
        $defaultStore = $user->role === 'owner' ? 'all' : ($user->store_id ?? ($outlets->first()->uuid ?? null));
        $store_id = $request->input('store_id', $defaultStore);
        
        // Filter Tanggal
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        
        // Query Cash Flow
        $query = \App\Models\CashFlow::with(['outlet', 'user', 'paymentMethod']);
        
        if ($store_id !== 'all') {
            $query->where('store_id', $store_id);
        }
        
        if ($start_date) {
            $query->whereDate('tanggal', '>=', $start_date);
        }
        if ($end_date) {
            $query->whereDate('tanggal', '<=', $end_date);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('keterangan', 'like', "%$search%")
                  ->orWhere('jenis', 'like', "%$search%");
            });
        }

        // Clone query for history with pagination
        $historyQuery = clone $query;
        if ($request->filled('type') && $request->type !== 'semua') {
            $historyQuery->where('jenis', $request->type);
        }
        $history = $historyQuery->orderBy('tanggal', 'desc')->paginate(100)->appends($request->query());

        // Calculate Summaries
        $pemasukan = (clone $query)->where('jenis', 'pemasukan')->sum('nominal');
        $pengeluaran = (clone $query)->where('jenis', 'pengeluaran')->sum('nominal');
        $saldo_bersih = $pemasukan - $pengeluaran;

        return view('keuangan.manage', compact(
            'cashboxes', 
            'history', 'pemasukan', 'pengeluaran', 'saldo_bersih', 'outlets', 'store_id', 'start_date', 'end_date'
        ));
    }

    public function storeCashbox(Request $request)
    {
        $request->validate([
            'nama_metode' => 'required|string|max:255|unique:payment_methods,nama_metode',
        ]);

        PaymentMethod::create([
            'uuid' => (string) Str::uuid(),
            'nama_metode' => $request->nama_metode
        ]);

        return redirect()->route('keuangan.index', ['tab' => 'cashbox'])->with('success', 'Cashbox berhasil ditambahkan!');
    }

    public function updateCashbox(Request $request, $id)
    {
        $pm = PaymentMethod::findOrFail($id);
        
        $request->validate([
            'nama_metode' => 'required|string|max:255|unique:payment_methods,nama_metode,' . $id . ',uuid',
        ]);

        $pm->update([
            'nama_metode' => $request->nama_metode
        ]);

        return redirect()->route('keuangan.index', ['tab' => 'cashbox'])->with('success', 'Cashbox berhasil diperbarui!');
    }

    public function destroyCashbox($id)
    {
        $pm = PaymentMethod::findOrFail($id);
        $pm->delete();

        return redirect()->route('keuangan.index', ['tab' => 'cashbox'])->with('success', 'Cashbox berhasil dihapus!');
    }

    public function kasBox()
    {
        return redirect()->route('keuangan.index', ['tab' => 'cashbox']);
    }

    public function arusUang(Request $request)
    {
        return redirect()->route('keuangan.index', ['tab' => 'arus-uang']);
    }

    public function pemindahanSaldo()
    {
        return redirect()->route('keuangan.index', ['tab' => 'pemindahan-saldo']);
    }
}
