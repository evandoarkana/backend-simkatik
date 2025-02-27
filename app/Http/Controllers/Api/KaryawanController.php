<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
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

    public function register(Request $request)
    {
        if (Auth::user()->role !== UserRole::Admin->value) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak. Hanya admin yang dapat mendaftarkan karyawan.',
                'code' => 403
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:255',
            'username' => 'required|unique:users,username|regex:/^[a-z0-9_]+$/',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
                'code' => 422
            ], 422);
        }

        $user = new User();
        $user->nama_lengkap = strtolower($request->nama_lengkap);
        $user->username = $request->username;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->role = UserRole::Karyawan->value;
        $user->profile_picture = 'default.jpg';

        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/profile_picture', $filename);
            $user->profile_picture = $filename;
        }

        $user->save();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email)
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi berhasil. Silakan cek email untuk verifikasi.',
            'data' => [
                'user' => $user
            ],
            'verification_url' => $verificationUrl,
            'code' => 201
        ], 201);
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
