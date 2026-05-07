<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Outlet;

class OutletController extends Controller
{
    public function index(Request $request)
    {
        $outlets = Outlet::with(['users.operator'])->get();
        
        // Data for Kinerja (can be added here or kept as mock for now as requested)
        // Data for Riwayat Stok (can be added here)
        
        $activeTab = $request->query('active_tab', 'data');

        return view('outlet.index', [
            'outlets' => $outlets,
            'active_tab' => $activeTab
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
