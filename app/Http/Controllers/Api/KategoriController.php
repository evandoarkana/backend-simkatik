<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    public function index()
    {
        try {
            $categories = Kategori::all();
            return response()->json([
                'status' => 'success',
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategori'
        ]);

        try {
            $Kategori = Kategori::create([
                'nama_kategori' => $request->nama_kategori
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Kategori created successfully',
                'data' => $Kategori
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_kategori' => "required|string|max:255|unique:kategori,nama_kategori,{$id}"

        ]);

        try {
            $Kategori = Kategori::findOrFail($id);
            $Kategori->update([
                'nama_kategori' => $request->nama_kategori
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Kategori updated successfully',
                'data' => $Kategori
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $Kategori = Kategori::findOrFail($id);

            if ($Kategori->products()->count() > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot delete Kategori with existing products'
                ], 400);
            }

            $Kategori->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Kategori deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}