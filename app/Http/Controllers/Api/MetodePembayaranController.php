<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MetodePembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MetodePembayaranController extends Controller
{
    public function index()
    {
        $metode = MetodePembayaran::all();
        return response()->json([
            'status' => true,
            'message' => 'Data metode pembayaran berhasil diambil',
            'data' => $metode
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|unique:metode_pembayaran,nama'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $metode = MetodePembayaran::create($request->only(['nama']));

        return response()->json([
            'status' => true,
            'message' => 'Metode pembayaran berhasil ditambahkan',
            'data' => $metode
        ], 201);
    }
}
