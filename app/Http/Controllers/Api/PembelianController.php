<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembelian;
use App\Models\Produk;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PembelianController extends Controller
{
    public function index()
    {
        $pembelian = Pembelian::with('produk')->get();

        return response()->json($pembelian);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_produk' => 'required|exists:produk,id',
            'unit' => 'required|integer|min:1',
            'harga_beli' => 'required|integer',
            'tanggal_dibeli' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    $tanggal = Carbon::parse($value);
                    $hariIni = now();

                    if ($tanggal->gt($hariIni)) {
                        $fail('Tanggal dibeli tidak boleh melebihi hari ini.');
                    }
                }
            ],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $produk = Produk::find($request->id_produk);

        $harga_beli_terbaru = Pembelian::where('id_produk', $produk->id)->latest('created_at')->value('harga_beli') ?? 0;

        if ($produk->harga_beli !== $harga_beli_terbaru) {
            $produk->harga_beli = $harga_beli_terbaru;
        }

        $produk->stok += $request->unit;
        $produk->save();

        Pembelian::create([
            'id_produk' => $produk->id,
            'unit' => $request->unit,
            'harga_beli' => $request->harga_beli,
            'total_harga' => $request->unit * $request->harga_beli,
            'tanggal_dibeli' => $request->tanggal_dibeli,
        ]);

        return response()->json([
            "message" => "Pembelian berhasil",
            "data" => $produk
        ], 201);
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
