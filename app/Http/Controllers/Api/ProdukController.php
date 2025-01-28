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
            'nama_produk' => 'required|string|unique:produk,nama_produk',
            'id_kategori' => 'required|exists:kategori,id',
            'harga_jual' => 'required|integer',
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
            'stok' => 0,
        ]);

        $harga_beli_terbaru = Pembelian::where('id_produk', $produk->id)->latest('created_at')->value('harga_beli') ?? 0;

        Pembelian::create([
            'id_produk' => $produk->id,
            'unit' => $produk->stok,
            'harga_beli' => $harga_beli_terbaru,
            'total_harga' => $harga_beli_terbaru * $produk->stok,
        ]);

        return response()->json([
            "message" => "Produk berhasil ditambahkan",
            "data" => $produk
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $produk = Produk::find($id);

        if (!$produk) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'harga_jual' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $produk->update([
            'harga_jual' => $request->harga_jual,
        ]);

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
