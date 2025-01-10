<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Penjualan;
use App\Models\Produk;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PenjualanController extends Controller
{
    public function index(Request $request)
    {
        $tahun = $request->query('tahun');
        $bulan = $request->query('bulan');

        $query = Penjualan::with('produk');

        if ($tahun) {
            $query->whereYear('tanggal_terjual', $tahun);
        }

        if ($bulan) {
            $query->whereMonth('tanggal_terjual', $bulan);
        }

        $penjualan = $query->get();

        if ($penjualan->isEmpty()) {
            return response()->json(['message' => 'Tidak ada data penjualan yang ditemukan.'], 404);
        }

        return response()->json($penjualan, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_produk' => 'required|exists:produk,id',
            'unit' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $produk = Produk::find($request->id_produk);

        if ($produk->stok < $request->unit) {
            return response()->json(['message' => 'Stok tidak mencukupi'], 400);
        }

        $total_harga = $request->unit * $produk->harga_jual;
        $keuntungan = $request->unit * ($produk->harga_jual - $produk->harga_beli);

        $penjualan = Penjualan::create([
            'id_produk' => $request->id_produk,
            'unit' => $request->unit,
            'harga_jual' => $produk->harga_jual,
            'total_harga' => $total_harga,
            'keuntungan' => $keuntungan,
            'tanggal_terjual' => now(),
        ]);

        $produk->stok -= $request->unit;
        $produk->save();

        return response()->json([
            'message' => 'Penjualan berhasil ditambahkan',
            'data' => $penjualan
        ], 201);
    }

    public function printPdf(Request $request)
    {
        $tahun = $request->query('tahun');
        $bulan = $request->query('bulan');

        $query = Penjualan::with('produk');

        if ($tahun) {
            $query->whereYear('tanggal_terjual', $tahun);
        }

        if ($bulan) {
            $query->whereMonth('tanggal_terjual', $bulan);
        }

        $penjualan = $query->get();

        if ($penjualan->isEmpty()) {
            return response()->json(['message' => 'Tidak ada data penjualan untuk filter ini.'], 404);
        }

        $pdf = Pdf::loadView('penjualan.pdf', compact('penjualan', 'tahun', 'bulan'));

        return $pdf->stream('laporan-penjualan.pdf');
    }

}
