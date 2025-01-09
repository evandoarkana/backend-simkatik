<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ViewVerificationHelper;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Helpers\EmailHelper;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'profile_picture' => 'image|mimes:jpeg,png,jpg|max:2048'
        ]);

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
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Email tidak ditemukan'
            ], 404);
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => $token,
                'created_at' => Carbon::now()
            ]
        );

        $resetUrl = config('app.frontend_url') . '/reset-password?token=' . $token;

        EmailHelper::sendResetPasswordEmail($user->email, $user->username, $resetUrl);

        return response()->json([
            'message' => 'Link reset password telah dikirim ke email'
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'password' => 'required|min:8|confirmed'
        ]);

        $resetToken = DB::table('password_reset_tokens')
            ->where('token', $request->token)
            ->first();

        if (!$resetToken) {
            return response()->json([
                'message' => 'Token reset password tidak valid'
            ], 400);
        }

        if (Carbon::parse($resetToken->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('token', $request->token)->delete();
            return response()->json([
                'message' => 'Token reset password sudah kadaluarsa'
            ], 400);
        }

        $user = User::where('email', $resetToken->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_tokens')->where('token', $request->token)->delete();

        return response()->json([
            'message' => 'Password berhasil direset'
        ]);
    }

    public function getProfile(Request $request)
    {
        $user = $request->user();

        $user->profile_picture_url = $user->profile_picture === 'default.jpg'
            ? asset("storage/profile_picture/default.jpg")
            : asset("storage/profile_picture/{$user->profile_picture}");

        return response()->json([
            'status' => true,
            'message' => 'Profil pengguna berhasil diambil.',
            'user' => $user,
        ], 200);
    }


    public function updateProfilePicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $user = $request->user();

        if ($user->profile_picture !== 'default.jpg') {
            Storage::delete('public/profile_picture/' . $user->profile_picture);
        }

        $file = $request->file('profile_picture');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->storeAs('public/profile_picture', $filename);

        $user->profile_picture = $filename;
        $user->save();

        return response()->json([
            'message' => 'Foto profil berhasil diperbarui',
            'user' => $user
        ]);
    }
}