<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembelian;
use App\Models\Produk;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProdukController extends Controller
{
    public function index()
    {
        try {
            $produk = Produk::with('kategori')->latest()->get();

            return response()->json([
                'status' => true,
                'message' => 'Data produk berhasil diambil',
                'data' => $produk
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan sistem',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $produk = Produk::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama_produk' => "sometimes|required|string|max:255|unique:produk,nama_produk,{$id}",
            'kategori_id' => 'sometimes|required|exists:kategori,id',
            'harga_jual' => 'sometimes|required|numeric|min:0',
            'diskon' => 'sometimes|integer|min:0|max:100',
            'gambar_produk' => 'sometimes|nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $produk->update($request->only(['nama_produk', 'kategori_id', 'harga_jual', 'diskon', 'gambar_produk']));

        return response()->json([
            'status' => true,
            'message' => 'Data produk berhasil diperbarui',
            'data' => $produk
        ]);
    }

    public function destroy($id)
    {
        $produk = Produk::findOrFail($id);

        if ($produk->pembelian()->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'Produk tidak dapat dihapus karena masih memiliki riwayat pembelian'
            ], 400);
        }

        $produk->delete();

        return response()->json([
            'status' => true,
            'message' => 'Data produk berhasil dihapus'
        ]);
    }

    public function printPdf()
    {
        $produk = Produk::all();

        if ($produk->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Tidak ada produk yang ditemukan.'
            ], 404);
        }

        $pdf = Pdf::loadView('produk.pdf', compact('produk'));

        if (request()->query('action') === 'download') {
            return $pdf->download("laporan_semua_produk.pdf");
        }

        return $pdf->stream("laporan_semua_produk.pdf");
    }
}
