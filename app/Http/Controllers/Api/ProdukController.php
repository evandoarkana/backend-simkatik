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
    public function update(Request $request, $id)
    {
        $produk = Produk::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama' => 'sometimes|required|string|max:255',
            'kategori_id' => 'sometimes|required|exists:kategori,id',
            'harga' => 'sometimes|required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $produk->update($request->all());
        return response()->json([
            'status' => true,
            'message' => 'Data produk berhasil diperbarui',
            'data' => $produk
        ]);
    }

    public function destroy($id)
    {
        $produk = Produk::findOrFail($id);
        $produk->pembelian()->update(['produk_id' => null]);

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
            return response()->json(['message' => 'Tidak ada produk yang ditemukan.'], 404);
        }

        $pdf = Pdf::loadView('produk.pdf', compact('produk'));

        if (request()->query('action') === 'download') {
            return $pdf->download("laporan_semua_produk.pdf");
        }

        return $pdf->stream("laporan_semua_produk.pdf");
    }
}
