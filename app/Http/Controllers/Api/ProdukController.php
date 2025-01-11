<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembelian;
use App\Models\Produk;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProdukController extends Controller
{
    public function index()
    {
        $produk = Produk::with('kategori')->get();

        if ($produk->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada produk yang tersedia.',
            ], 404);
        }

        return response()->json($produk, 200);
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'gambar_produk' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'nama_produk' => 'required|string',
            'id_kategori' => 'required|exists:kategori,id',
            'harga_jual' => 'required|integer',
            'harga_beli' => 'required|integer',
            'stok' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if ($request->hasFile('gambar_produk') && $request->file('gambar_produk')->isValid()) {
            $path = $request->file('gambar_produk')->store('barang_picture', 'public');
        } else {
            return response()->json(['message' => 'Gambar produk harus ada dan valid.'], 400);
        }

        $produk = Produk::create([
            'gambar_produk' => $path,
            'nama_produk' => $request->nama_produk,
            'id_kategori' => $request->id_kategori,
            'harga_jual' => $request->harga_jual,
            'harga_beli' => $request->harga_beli,
            'stok' => $request->stok,
        ]);

        Pembelian::create([
            'id_produk' => $produk->id,
            'unit' => $produk->stok,
            'harga_beli' => $produk->harga_beli,
            'total_harga' => $produk->harga_beli * $produk->stok,
            'tanggal_dibeli' => now(),
        ]);

        return response()->json([
            "message" => "Produk berhasil ditambahkan dan pembelian dicatat",
            "data" => $produk
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $produk = Produk::find($id);

        if (!$produk) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        $stok_lama = $produk->stok;
        $stok_baru = $request->input('stok');

        $produk->update($request->all());

        if ($stok_baru > $stok_lama) {
            $selisih_stok = $stok_baru - $stok_lama;
        }

        return response()->json($produk);
    }

    public function destroy($id)
    {
        $produk = Produk::find($id);
        if (!$produk) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        Pembelian::where('id_produk', $produk->id)->delete();
        $produk->delete();

        return response()->json(['message' => 'Produk berhasil dihapus']);
    }

    public function tambahStok(Request $request, $id)
    {
        $produk = Produk::find($id);
        if (!$produk) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        $stok_lama = $produk->stok;
        $stok_baru = $stok_lama + $request->input('stok');

        $produk->stok = $stok_baru;
        $produk->save();

        Pembelian::create([
            'id_produk' => $produk->id,
            'unit' => $request->input('stok'),
            'harga_beli' => $produk->harga_beli,
            'total_harga' => $produk->harga_beli * $request->input('stok'),
            'tanggal_dibeli' => now(),
        ]);

        return response()->json($produk);
    }

    public function printPdf()
    {
        $produk = Produk::all();

        if ($produk->isEmpty()) {
            return response()->json(['message' => 'Tidak ada produk yang ditemukan.'], 404);
        }

        $pdf = Pdf::loadView('produk.pdf', compact('produk'));

        if (request()->query('action') === 'download') {
            return $pdf->download("laporan_semua_produk.pdf");
        }

        return $pdf->stream("laporan_semua_produk.pdf");
    }
}
