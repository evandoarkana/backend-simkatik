<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ViewVerificationHelper;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Helpers\EmailHelper;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'profile_picture' => 'image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal melakukan registrasi',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = new User();
        $user->username = $request->username;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
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

        EmailHelper::sendVerificationEmail($user->email, $user->username, $verificationUrl);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi berhasil, silakan cek email untuk verifikasi',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }

        if (!$user->email_verified_at) {
            return response()->json([
                'message' => 'Email belum diverifikasi'
            ], 403);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'user' => $user,
            'token' => $token
        ]);
    }

    public function getProfile(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 401);
            }

            $profilePictureUrl = null;
            if ($user->profile_picture) {
                $profilePictureUrl = $user->profile_picture === 'default.jpg'
                    ? asset('storage/profile_picture/default.jpg')
                    : Storage::url("public/profile_picture/{$user->profile_picture}");
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Data profil berhasil dimuat',
                'data' => [
                    'user' => $user,
                    'profile_picture_url' => $profilePictureUrl
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateProfilePicture(Request $request)
    {
        try {
            Log::info('Start update profile picture');

            $validator = Validator::make($request->all(), [
                'profile_picture' => [
                    'required',
                    'image',
                    'mimes:jpeg,png,jpg',
                    'max:2048',
                ]
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed:', $validator->errors()->toArray());
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            Log::info('Validation passed');

            $user = $request->user();
            Log::info('User data:', ['user' => $user]);

            if ($user->profile_picture && $user->profile_picture !== 'default.jpg') {
                Log::info('Attempting to delete old profile picture:', ['old_picture' => $user->profile_picture]);
                Storage::delete("public/profile_picture/{$user->profile_picture}");
            }

            $file = $request->file('profile_picture');
            $filename = time() . '_' . $file->getClientOriginalName();
            Log::info('New filename:', ['filename' => $filename]);

            $path = $file->storeAs('public/profile_picture', $filename);
            Log::info('File stored at:', ['path' => $path]);

            $user->profile_picture = $filename;
            $user->save();
            Log::info('User updated with new profile picture');

            $response = [
                'status' => 'success',
                'message' => 'Foto profil berhasil diperbarui',
                'data' => [
                    'user' => $user,
                    'profile_picture_url' => Storage::url("public/profile_picture/{$filename}")
                ]
            ];

            Log::info('Sending response:', $response);
            return response()->json($response, 200);

        } catch (\Exception $e) {
            Log::error('Exception occurred:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil'
        ]);
    }

    public function verifyEmail(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (!hash_equals(sha1($user->email), $request->hash)) {
            return response(ViewVerificationHelper::emailVerificationPage('error', 'Link verifikasi tidak valid'))
                ->header('Content-Type', 'text/html');
        }

        if ($user->email_verified_at) {
            return response(ViewVerificationHelper::emailVerificationPage('success', 'Email sudah terverifikasi sebelumnya'))
                ->header('Content-Type', 'text/html');
        }

        $user->email_verified_at = now();
        $user->save();

        return response(ViewVerificationHelper::emailVerificationPage('success'))
            ->header('Content-Type', 'text/html');
    }

    public function verifyOldPasswordV2(Request $request)
    {
        $request->validate([
            'old_password' => 'required|string',
        ]);

        $user = $request->user();

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Password lama tidak cocok.',
            ], 400);
        }

        session(['password_verified' => true]);

        return response()->json([
            'status' => true,
            'message' => 'Password lama cocok. Silakan masukkan password baru.',
        ], 200);
    }

    public function resetPasswordV2(Request $request)
    {
        $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if (!session('password_verified')) {
            return response()->json([
                'status' => false,
                'message' => 'Anda belum memverifikasi password lama.',
            ], 403);
        }

        $user = $request->user();

        $user->password = Hash::make($request->new_password);
        $user->save();

        session()->forget('password_verified');

        return response()->json([
            'status' => true,
            'message' => 'Password berhasil diperbarui.',
        ], 200);
    }
    public function forgotPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email tidak ditemukan'
                ], 404);
            }

            $resetToken = Str::random(32);
            $verificationCode = Str::random(6);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => $verificationCode,
                    'created_at' => Carbon::now()
                ]
            );

            Cache::put("reset_password_{$resetToken}", $user->email, now()->addMinutes(30));

            $emailSent = EmailHelper::sendResetPasswordVerificationEmail($user->email, $user->username, $verificationCode);

            if (!$emailSent) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal mengirim kode verifikasi ke email Anda'
                ], 500);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Kode verifikasi telah dikirim ke email Anda.',
                'reset_token' => $resetToken
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function verifyVerificationCode(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'reset_token' => 'required|string',
                'verification_code' => 'required|string|size:6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $email = Cache::get("reset_password_{$request->reset_token}");


            if (!$email) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token reset password tidak valid atau telah kadaluarsa.'
                ], 401);
            }

            $tokenData = DB::table('password_reset_tokens')
                ->where('email', $email)
                ->first();

            if (!$tokenData) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kode verifikasi tidak ditemukan.'
                ], 404);
            }

            if ($tokenData->token !== $request->verification_code) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kode verifikasi tidak valid.'
                ], 400);
            }

            $expiryTime = Carbon::parse($tokenData->created_at)->addMinutes(30);
            if (Carbon::now()->greaterThan($expiryTime)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kode verifikasi telah kedaluwarsa.'
                ], 400);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Kode verifikasi valid. Silakan lanjutkan dengan reset password.',
                'reset_token' => $request->reset_token
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }


    public function resendVerificationCode(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'reset_token' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $email = Cache::get("reset_password_{$request->reset_token}");

            if (!$email) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token reset password tidak valid atau telah kadaluarsa.'
                ], 401);
            }

            $user = User::where('email', $email)->first();
            $verificationCode = Str::random(6);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $email],
                [
                    'token' => $verificationCode,
                    'created_at' => Carbon::now()
                ]
            );

            $emailSent = EmailHelper::sendResetPasswordVerificationEmail($user->email, $user->username, $verificationCode);

            if (!$emailSent) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal mengirim ulang kode verifikasi'
                ], 500);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Kode verifikasi baru telah dikirim ke email Anda.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    public function resetPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'reset_token' => 'required|string',
                'password' => 'required|string|min:8|confirmed',
                'password_confirmation' => 'required|string|min:8'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $email = Cache::get("reset_password_{$request->reset_token}");

            if (!$email) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token reset password tidak valid atau telah kadaluarsa.'
                ], 401);
            }

            $user = User::where('email', $email)->first();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pengguna tidak ditemukan.'
                ], 404);
            }

            $user->password = Hash::make($request->password);
            $user->save();

            Cache::forget("reset_password_{$request->reset_token}");
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Password berhasil diubah.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
