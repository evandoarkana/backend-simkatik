<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransaksiItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransaksiItemController extends Controller
{
    public function index()
    {
        $items = TransaksiItem::with(['transaksi', 'produk'])->latest()->get();
        return response()->json([
            'status' => true,
            'message' => 'Data transaksi item berhasil diambil',
            'data' => $items
        ]);
    }

    public function destroy($id)
    {
        $item = TransaksiItem::findOrFail($id);
        $item->delete();

        return response()->json([
            'status' => true,
            'message' => 'Item transaksi berhasil dihapus'
        ]);
    }
}
