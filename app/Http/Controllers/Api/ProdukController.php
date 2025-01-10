<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Barryvdh\DomPDF\Facade as PDF;  // Pastikan menggunakan alias PDF
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
            'gambar_produk' => 'required|string',
            'nama_produk' => 'required|string',
            'id_kategori' => 'required|exists:kategori,id',
            'harga_jual' => 'required|integer',
            'harga_beli' => 'required|integer',
            'stok' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $produk = Produk::create($request->all());
        return response()->json($produk, 201);
    }

    public function update(Request $request, $id)
    {
        $produk = Produk::find($id);
        if (!$produk) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        $produk->update($request->all());
        return response()->json($produk);
    }

    public function destroy($id)
    {
        $produk = Produk::find($id);
        if (!$produk) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        $produk->delete();
        return response()->json(['message' => 'Produk berhasil dihapus']);
    }

    public function tambahStok(Request $request, $id)
    {
        $produk = Produk::find($id);
        if (!$produk) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        $produk->stok += $request->input('stok');
        $produk->save();

        return response()->json($produk);
    }
    public function printPdf($id)
    {
        $produk = Produk::find($id);

        if (!$produk) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('produk.pdf', compact('produk'));

        if (request()->query('action') === 'download') {
            return $pdf->download("produk_{$produk->id}.pdf");
        }

        return $pdf->stream("produk_{$produk->id}.pdf");
    }
}
