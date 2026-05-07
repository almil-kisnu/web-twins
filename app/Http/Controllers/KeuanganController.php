<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class KeuanganController extends Controller
{
    public function index()
    {
        return view('keuangan.manage');
    }

    public function kasBox()
    {
        return view('keuangan.manage');
    }

    public function arusUang()
    {
        return view('keuangan.manage');
    }

    public function pemindahanSaldo()
    {
        return view('keuangan.manage');
    }
}
