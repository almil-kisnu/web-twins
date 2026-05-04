<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class KeuanganController extends Controller
{
    public function index()
    {
        return redirect()->route('keuangan.kas-box');
    }

    public function kasBox()
    {
        return view('keuangan.kas-box');
    }

    public function arusUang()
    {
        return view('keuangan.arus-uang');
    }

    public function pemindahanSaldo()
    {
        return view('keuangan.pemindahan-saldo');
    }
}
