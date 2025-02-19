<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembelian;
use App\Models\Produk;
use Barryvdh\DomPDF\Facade\Pdf;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PembelianController extends Controller
{
    public function index()
    {
        try {
            $pembelian = Pembelian::with('produk')->latest()->get();
            return response()->json([
                'status' => true,
                'message' => 'Data pembelian berhasil diambil',
                'data' => $pembelian
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan sistem',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'produk_id' => 'required|exists:produk,id',
            'jumlah' => 'required|integer|min:1',
            'harga_satuan' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();
            $pembelian = Pembelian::create($request->all());

            $produk = Produk::find($request->produk_id);
            $produk->increment('stok', $request->jumlah);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Pembelian berhasil ditambahkan dan stok diperbarui',
                'data' => $pembelian
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menambahkan pembelian',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function printPdf(Request $request)
    {
        $query = Pembelian::with('produk');

        if ($request->has('nama_produk')) {
            $query->whereHas('produk', function ($q) use ($request) {
                $q->where('nama_produk', 'like', "%{$request->nama_produk}%");
            });
        }

        if ($request->has('bulan') && $request->has('tahun')) {
            $query->whereMonth('tanggal_dibeli', $request->bulan)
                ->whereYear('tanggal_dibeli', $request->tahun);
        }

        $pembelian = $query->get();

        if ($pembelian->isEmpty()) {
            return response()->json(['message' => 'Data pembelian tidak ditemukan'], 404);
        }

        $pdf = Pdf::loadView('pembelian.pdf', compact('pembelian'));

        if ($request->query('action') === 'download') {
            return $pdf->download('laporan_pembelian.pdf');
        }

        return $pdf->stream('laporan_pembelian.pdf');
    }
}
