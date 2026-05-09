<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CashFlow;
use App\Models\Debt;
use App\Models\DetailDebt;
use App\Models\Contact;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class BukuKasController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $outlets = collect();
        if ($user->role === 'owner') {
            $outlets = DB::table('store')->get();
        } elseif ($user->role === 'kepala_toko' && $user->outlet_id) {
            $outlets = DB::table('store')->where('uuid', $user->outlet_id)->get();
        }
        
        $defaultStore = $user->role === 'owner' ? 'all' : ($user->outlet_id ?? ($outlets->first()->uuid ?? null));
        $store_id = $request->input('store_id', $defaultStore);
        
        $active_tab = session('active_tab') ?? $request->input('active_tab', 'pengeluaran');

        $period = $request->get('period');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');

        if ($period === 'bulanan') {
            $month = $request->get('month_date', date('Y-m'));
            $start_date = $month . '-01';
            $end_date = date('Y-m-t', strtotime($start_date));
        } elseif ($period === 'tahunan') {
            $year = $request->get('year_date', date('Y'));
            $start_date = $year . '-01-01';
            $end_date = $year . '-12-31';
        } elseif ($period === 'harian') {
            if (!$start_date) $start_date = date('Y-m-d');
            if (!$end_date) $end_date = date('Y-m-d');
        } else {
            // No specific period requested, show all data
            $start_date = null;
            $end_date = null;
            $period = 'semua';
        }

        $pengeluaranQuery = CashFlow::with(['outlet', 'user', 'paymentMethod'])->where('jenis', 'pengeluaran')->where('keterangan', 'NOT LIKE', '%(Trx:%');
        $pemasukanQuery = CashFlow::with(['outlet', 'user', 'paymentMethod'])->where('jenis', 'pemasukan')->where('keterangan', 'NOT LIKE', '%(Trx:%');
        $status = $request->input('status');
        $hutangQuery = Debt::with(['contact', 'detailDebts.paymentMethod', 'paymentOrder.items'])->whereRaw('LOWER(tipe) IN (?, ?)', ['hutang', 'utang']);
        $piutangQuery = Debt::with(['contact', 'detailDebts.paymentMethod', 'transaction.details.product'])->whereRaw('LOWER(tipe) = ?', ['piutang']);

        if ($status === 'lunas') {
            $hutangQuery->where('sisa', '<=', 0);
            $piutangQuery->where('sisa', '<=', 0);
        } elseif ($status === 'belum_lunas') {
            $hutangQuery->where('sisa', '>', 0);
            $piutangQuery->where('sisa', '>', 0);
        }
        $suppliersQuery = Contact::whereRaw('LOWER(tipe) = ?', ['supplier']);
        $customersQuery = Contact::whereRaw('LOWER(tipe) = ?', ['customer']);

        if ($store_id !== 'all') {
            $pengeluaranQuery->where('store_id', $store_id);
            $pemasukanQuery->where('store_id', $store_id);
            $hutangQuery->where('store_id', $store_id);
            $piutangQuery->where('store_id', $store_id);
            $suppliersQuery->where(function($q) use ($store_id) {
                $q->where('store_id', $store_id)->orWhereNull('store_id');
            });
            $customersQuery->where(function($q) use ($store_id) {
                $q->where('store_id', $store_id)->orWhereNull('store_id');
            });
        }

        // Apply Date Filters ONLY if provided
        if ($start_date) {
            $pengeluaranQuery->whereDate('tanggal', '>=', $start_date);
            $pemasukanQuery->whereDate('tanggal', '>=', $start_date);
        }
        if ($end_date) {
            $pengeluaranQuery->whereDate('tanggal', '<=', $end_date);
            $pemasukanQuery->whereDate('tanggal', '<=', $end_date);
        }

        $pengeluaran = $pengeluaranQuery->orderBy('tanggal', 'desc')->get();
        $pemasukan = $pemasukanQuery->orderBy('tanggal', 'desc')->get();
        $hutang = $hutangQuery->orderBy('jatuh_tempo', 'asc')->get();
        $piutang = $piutangQuery->orderBy('jatuh_tempo', 'asc')->get();

        // Calculate Summaries
        $totalPemasukan = $pemasukan->sum('nominal');
        $totalPengeluaran = $pengeluaran->sum('nominal');
        $saldoKasBersih = $totalPemasukan - $totalPengeluaran;

        $suppliers = $suppliersQuery->get();
        $customers = $customersQuery->get();
        $paymentMethods = PaymentMethod::orderBy('nama_metode', 'asc')->get();

        return view('buku_kas.buku_kas', [
            'pengeluaran' => $pengeluaran,
            'pemasukan' => $pemasukan,
            'hutang' => $hutang,
            'piutang' => $piutang,
            'suppliers' => $suppliers,
            'customers' => $customers,
            'paymentMethods' => $paymentMethods,
            'outlets' => $outlets,
            'store_id' => $store_id,
            'status' => $status,
            'active_tab' => $active_tab,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'period' => $period,
            'is_filtered' => ($start_date !== null),
            'totalPemasukan' => $totalPemasukan,
            'totalPengeluaran' => $totalPengeluaran,
            'saldoKasBersih' => $saldoKasBersih,
        ]);
    }

    public function storeCashFlow(Request $request)
    {
        $request->validate([
            'store_id' => 'required',
            'jenis' => 'required|in:Pemasukan,Pengeluaran',
            'nominal' => 'required|numeric',
            'keterangan' => 'required|string',
            'tanggal' => 'required|date',
            'metode_pembayaran' => 'nullable|string',
        ]);

        // Combine date with current time for full timestamp
        $tanggal = $request->tanggal;
        if ($tanggal == date('Y-m-d')) {
            $tanggal = date('Y-m-d H:i:s');
        } else {
            $tanggal = $tanggal . ' ' . date('H:i:s');
        }

        CashFlow::create([
            'store_id' => $request->store_id,
            'user_id' => auth()->user()->uuid ?? auth()->id(),
            'jenis' => strtolower($request->jenis),
            'nominal' => $request->nominal,
            'keterangan' => $request->keterangan,
            'tanggal' => $tanggal,
            'metode_pembayaran' => $request->metode_pembayaran,
        ]);

        return redirect()->back()->with('success', $request->jenis . ' berhasil dicatat!')->with('active_tab', strtolower($request->jenis));
    }

    public function updateCashFlow(Request $request, $id)
    {
        $request->validate([
            'nominal' => 'required|numeric',
            'keterangan' => 'nullable|string',
            'metode_pembayaran' => 'nullable|string',
        ]);

        $cf = CashFlow::findOrFail($id);
        $cf->update([
            'nominal' => $request->nominal,
            'keterangan' => $request->keterangan,
            'metode_pembayaran' => $request->metode_pembayaran,
        ]);

        return redirect()->back()->with('success', 'Data berhasil diperbarui!')->with('active_tab', strtolower($cf->jenis));
    }

    public function deleteCashFlow($id)
    {
        $cf = CashFlow::findOrFail($id);
        $jenis = strtolower($cf->jenis);
        $cf->delete();
        return redirect()->back()->with('success', 'Data ' . ucfirst($jenis) . ' berhasil dihapus!')->with('active_tab', $jenis);
    }

    public function storeDebt(Request $request)
    {
        $request->validate([
            'store_id' => 'required',
            'tipe' => 'required|in:Hutang,Piutang',
            'kontak_id' => 'nullable',
            'kontak_nama' => 'required_without:kontak_id',
            'nominal' => 'required|numeric',
            'uang_muka' => 'nullable|numeric',
            'jatuh_tempo' => 'required|date',
            'metode_pembayaran' => 'nullable|string',
        ]);

        $kontakId = $request->kontak_id;

        if (!$kontakId && $request->kontak_nama) {
            $contact = Contact::where('store_id', $request->store_id)
                ->where('nama', $request->kontak_nama)
                ->where('tipe', $request->tipe == 'Hutang' ? 'supplier' : 'customer')
                ->first();

            if (!$contact) {
                $contact = Contact::create([
                    'store_id' => $request->store_id,
                    'nama' => $request->kontak_nama,
                    'tipe' => $request->tipe == 'Hutang' ? 'supplier' : 'customer',
                    'no_hp' => null, // Avoid unique constraint violation with '-'
                ]);
            }
            $kontakId = $contact->uuid;
        }

        $sisa = $request->nominal - ($request->uang_muka ?? 0);

        $debt = Debt::create([
            'store_id' => $request->store_id,
            'kontak_id' => $kontakId,
            'tipe' => strtolower($request->tipe) === 'hutang' ? 'utang' : strtolower($request->tipe),
            'nominal' => $request->nominal,
            'sisa' => $sisa,
            'jatuh_tempo' => $request->jatuh_tempo,
        ]);

        if ($request->uang_muka > 0) {
            DetailDebt::create([
                'debts_id' => $debt->uuid,
                'sebelum' => $request->nominal,
                'bayar' => $request->uang_muka,
                'sisa' => $sisa,
                'metode_pembayaran' => $request->metode_pembayaran,
                'tanggal' => date('Y-m-d H:i:s'),
                'user_id' => auth()->user()->uuid ?? auth()->id(),
            ]);
        }

        $tabTipe = strtolower($request->tipe) === 'utang' ? 'hutang' : strtolower($request->tipe);
        return redirect()->back()->with('success', $request->tipe . ' berhasil dicatat!')->with('active_tab', $tabTipe);
    }

    public function updateDebt(Request $request, $id)
    {
        $request->validate([
            'nominal' => 'required|numeric',
            'jatuh_tempo' => 'required|date',
            'kontak_nama' => 'required'
        ]);

        $debt = Debt::findOrFail($id);
        
        $contact = Contact::where('store_id', $debt->store_id)
            ->where('nama', $request->kontak_nama)
            ->where('tipe', $debt->tipe == 'Hutang' ? 'supplier' : 'customer')
            ->first();

        if (!$contact) {
            $contact = Contact::create([
                'store_id' => $debt->store_id,
                'nama' => $request->kontak_nama,
                'tipe' => $debt->tipe == 'Hutang' ? 'supplier' : 'customer',
                'no_hp' => null,
            ]);
        }

        $diff = $request->nominal - $debt->nominal;
        $sisaBaru = $debt->sisa + $diff;

        $debt->update([
            'kontak_id' => $contact->uuid,
            'nominal' => $request->nominal,
            'sisa' => $sisaBaru,
            'jatuh_tempo' => $request->jatuh_tempo,
        ]);

        $tabTipe = strtolower($debt->tipe) === 'utang' ? 'hutang' : strtolower($debt->tipe);
        return redirect()->back()->with('success', 'Data berhasil diperbarui!')->with('active_tab', $tabTipe);
    }

    public function payDebt(Request $request, $id)
    {
        $request->validate([
            'bayar' => 'required|numeric|min:1',
            'metode_pembayaran' => 'required|string',
        ]);

        $debt = Debt::findOrFail($id);
        
        $sebelum = $debt->sisa;
        $sisaBaru = max(0, $debt->sisa - $request->bayar);

        DetailDebt::create([
            'debts_id' => $debt->uuid,
            'sebelum' => $sebelum,
            'bayar' => $request->bayar,
            'sisa' => $sisaBaru,
            'metode_pembayaran' => $request->metode_pembayaran,
            'tanggal' => date('Y-m-d H:i:s'),
            'user_id' => auth()->user()->uuid ?? auth()->id(),
        ]);

        $debt->update(['sisa' => $sisaBaru]);

        $tabTipe = strtolower($debt->tipe) === 'utang' ? 'hutang' : strtolower($debt->tipe);
        return redirect()->back()->with('success', 'Pembayaran berhasil dicatat!')->with('active_tab', $tabTipe);
    }

    public function deleteDebt($id)
    {
        $debt = Debt::findOrFail($id);
        $tipe = strtolower($debt->tipe);
        DetailDebt::where('debts_id', $debt->uuid)->delete();
        $debt->delete();
        $tabTipe = $tipe === 'utang' ? 'hutang' : $tipe;
        $namaTipe = $tipe === 'utang' ? 'Hutang' : ucfirst($tipe);
        return redirect()->back()->with('success', 'Data ' . $namaTipe . ' berhasil dihapus!')->with('active_tab', $tabTipe);
    }

    public function export(Request $request)
    {
        $format = $request->input('format', 'pdf');
        $kategoriList = $request->input('kategori', []);
        $store_id = $request->input('store_id', 'all');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        $pengeluaranQuery = CashFlow::with(['outlet', 'user'])->where('jenis', 'pengeluaran');
        $pemasukanQuery = CashFlow::with(['outlet', 'user'])->where('jenis', 'pemasukan');
        $hutangQuery = Debt::with(['contact', 'detailDebts'])->whereRaw('LOWER(tipe) IN (?, ?)', ['hutang', 'utang']);
        $piutangQuery = Debt::with(['contact', 'detailDebts'])->whereRaw('LOWER(tipe) = ?', ['piutang']);

        if ($store_id !== 'all') {
            $pengeluaranQuery->where('store_id', $store_id);
            $pemasukanQuery->where('store_id', $store_id);
            $hutangQuery->where('store_id', $store_id);
            $piutangQuery->where('store_id', $store_id);
            $outlet_name = DB::table('store')->where('uuid', $store_id)->value('nama') ?? 'Semua Outlet';
        } else {
            $outlet_name = 'Semua Outlet';
        }

        if ($start_date) {
            $pengeluaranQuery->whereDate('tanggal', '>=', $start_date);
            $pemasukanQuery->whereDate('tanggal', '>=', $start_date);
            $hutangQuery->whereDate('jatuh_tempo', '>=', $start_date);
            $piutangQuery->whereDate('jatuh_tempo', '>=', $start_date);
        }
        if ($end_date) {
            $pengeluaranQuery->whereDate('tanggal', '<=', $end_date);
            $pemasukanQuery->whereDate('tanggal', '<=', $end_date);
            $hutangQuery->whereDate('jatuh_tempo', '<=', $end_date);
            $piutangQuery->whereDate('jatuh_tempo', '<=', $end_date);
        }

        $pengeluaran = $pengeluaranQuery->orderBy('tanggal', 'desc')->get();
        $pemasukan = $pemasukanQuery->orderBy('tanggal', 'desc')->get();
        $hutang = $hutangQuery->orderBy('jatuh_tempo', 'asc')->get();
        $piutang = $piutangQuery->orderBy('jatuh_tempo', 'asc')->get();

        $total_pemasukan = $pemasukan->sum('nominal');
        $total_pengeluaran = $pengeluaran->sum('nominal');
        $total_sisa_hutang = $hutang->sum('sisa');
        $total_sisa_piutang = $piutang->sum('sisa');

        $data = compact(
            'kategoriList', 'start_date', 'end_date', 'outlet_name',
            'pengeluaran', 'pemasukan', 'hutang', 'piutang',
            'total_pemasukan', 'total_pengeluaran', 'total_sisa_hutang', 'total_sisa_piutang'
        );

        if ($format === 'excel') {
            return response(view('buku_kas.export_excel', $data))
                ->header('Content-Type', 'application/vnd.ms-excel')
                ->header('Content-Disposition', 'attachment; filename="Export_Buku_Kas_'.date('Ymd_His').'.xls"');
        }

        $pdf = Pdf::loadView('buku_kas.export_pdf', $data);
        return $pdf->download('Export_Buku_Kas_'.date('Ymd_His').'.pdf');
    }

    public function getReferenceDetail($id)
    {
        // Try to find in Transactions (Sales or Restocks)
        $trx = \App\Models\Transaction::with(['details.product', 'user', 'store', 'contact'])->where('uuid', $id)->first();
        if ($trx) {
            $isPurchase = $trx->jenis === 'pembelian';
            return response()->json([
                'success' => true,
                'type' => $isPurchase ? 'restok' : 'penjualan',
                'user' => $trx->user->name ?? '-',
                'store' => $trx->store->nama ?? '-',
                'contact' => $trx->contact->nama ?? '-',
                'formatted_date' => \Carbon\Carbon::parse($trx->tanggal)->translatedFormat('d F Y H:i'),
                'items' => $trx->details->map(function($d) use ($isPurchase) {
                    return [
                        'nama' => $d->product->nama_produk ?? $d->product->nama ?? 'Produk Tidak Dikenal',
                        'qty' => $d->jmlh,
                        'harga' => $isPurchase ? ($d->harga_modal ?: $d->harga_jual) : $d->harga_jual
                    ];
                })
            ]);
        }

        // Try to find in PaymentOrders (Optional: for other types of automated cashflows)
        $order = \App\Models\PaymentOrder::with(['items', 'outlet'])->where('uuid', $id)->orWhere('midtrans_order_id', $id)->first();
        if ($order) {
            return response()->json([
                'success' => true,
                'type' => 'payment_order',
                'user' => $order->recipient_name ?? '-',
                'store' => $order->outlet->nama ?? '-',
                'formatted_date' => \Carbon\Carbon::parse($order->created_at)->translatedFormat('d F Y H:i'),
                'items' => $order->items->map(function($i) {
                    return [
                        'nama' => $i->product_name,
                        'qty' => $i->quantity,
                        'harga' => $i->price
                    ];
                })
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Data tidak ditemukan']);
    }
}
