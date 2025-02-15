<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class KaryawanController extends Controller
{
    public function index(): JsonResponse
    {
        $karyawan = User::where('role', 'Karyawan')->get();
        return response()->json([
            'success' => true,
            'message' => 'Daftar karyawan berhasil diambil',
            'data' => [
                'karyawan' => $karyawan
            ],
        ]);
    }

    public function show($id): JsonResponse
    {
        $karyawan = User::where('role', 'Karyawan')->find($id);

        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail karyawan berhasil diambil',
            'data' => [
                'karyawan' => $karyawan
            ],
        ]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $karyawan = User::where('role', 'Karyawan')->find($id);

        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'sometimes|required|string|max:255',
            'email' => "sometimes|required|email|unique:users,email,{$id}",
            'password' => 'sometimes|required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $karyawan->update([
            'nama_lengkap' => $request->nama_lengkap ?? $karyawan->nama_lengkap,
            'email' => $request->email ?? $karyawan->email,
            'password' => $request->password ? bcrypt($request->password) : $karyawan->password
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Karyawan berhasil diperbarui',
            'data' => [
                'karyawan' => $karyawan
            ],
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $karyawan = User::where('role', 'Karyawan')->find($id);

        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan tidak ditemukan'
            ], 404);
        }

        $karyawan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Karyawan berhasil dihapus'
        ]);
    }
}
