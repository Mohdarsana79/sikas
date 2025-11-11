<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LaporanRealisasiController extends Controller
{
    //
    public function index()
    {
        return view('laporan-realisasi.index');
    }
}
