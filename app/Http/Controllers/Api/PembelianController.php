<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembelian;
use App\Models\Produk;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'nama_produk' => 'required|string|unique:produk,nama_produk',
            'kategori_id' => 'required|exists:kategori,id',
            'jumlah' => 'required|integer|min:1',
            'satuan' => 'required|in:Pcs,Box',
            'isi_perbox' => 'required_if:Satuan,Box|nullable|integer|min:1',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'diskon' => 'nullable|numeric|min:0',
            'gambar_produk' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
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

            $gambarPath = null;
            if ($request->hasFile('gambar_produk')) {
                $gambarPath = $request->file('gambar_produk')->store('produk', 'public');
            }

            $produk = Produk::create([
                'nama_produk' => $request->nama_produk,
                'kategori_id' => $request->kategori_id,
                'stok' => $request->jumlah,
                'harga_jual' => $request->harga_jual,
                'harga_beli' => $request->harga_beli,
                'diskon' => $request->diskon ?? 0,
                'isi_perbox' => $request->satuan === 'Box' ? $request->isi_perbox : null,
                'gambar_produk' => $gambarPath
            ]);

            $pembelian = Pembelian::create([
                'produk_id' => $produk->id,
                'jumlah' => $request->jumlah,
                'satuan' => $request->satuan,
                'harga_beli' => $request->harga_beli, 
                'diskon' => $request->diskon ?? 0,
                'isi_perbox' => $request->satuan === 'Box' ? $request->isi_perbox : null,
                'total_harga' => ($request->harga_beli * $request->jumlah) - ($request->diskon ?? 0),
                'tanggal' => now()->format('Y-m-d'),
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Produk dan pembelian berhasil ditambahkan',
                'data' => [
                    'produk' => $produk,
                    'pembelian' => $pembelian
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menyimpan pembelian',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function printPdf(Request $request)
    {
        $query = Pembelian::with('produk');

        if ($request->has('nama_produk')) {
            $query->whereHas('produk', function ($q) use ($request) {
                $q->where('nama', 'like', "%{$request->nama_produk}%");
            });
        }

        if ($request->has('bulan') && $request->has('tahun')) {
            $query->whereMonth('tanggal', $request->bulan)
                ->whereYear('tanggal', $request->tahun);
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

    public function tambahStok(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'produk_id' => 'required|exists:produk,id',
            'jumlah' => 'required|integer|min:1',
            'satuan' => 'required|in:Pcs,Box',
            'isi_perbox' => 'required_if:satuan,Box|nullable|integer|min:1',
            'harga_beli' => 'required|numeric|min:0',
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

            $produk = Produk::findOrFail($request->produk_id);

            $jumlahTambahan = $request->satuan === 'Box'
                ? $request->jumlah * $produk->isi_perbox
                : $request->jumlah;

            $pembelian = Pembelian::create([
                'produk_id' => $produk->id,
                'jumlah' => $request->jumlah,
                'satuan' => $request->satuan,
                'isi_perbox' => $request->satuan === 'Box' ? $request->isi_perbox : null,
                'harga_beli' => $request->harga_beli,
                'total_harga' => $request->harga_beli * $request->jumlah,
                'tanggal' => now()->format('Y-m-d'),
            ]);

            $produk->increment('stok', $jumlahTambahan);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Stok produk berhasil ditambahkan!',
                'data' => [
                    'produk' => $produk,
                    'pembelian' => $pembelian
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menambahkan stok',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
