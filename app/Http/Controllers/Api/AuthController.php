<?php

namespace App\Http\Controllers;

use App\Models\User;
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

        // Create user
        $user = new User();
        $user->username = $request->username;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->profile_picture = 'default.jpg';

        // Handle profile picture upload if provided
        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/profile_picture', $filename);
            $user->profile_picture = $filename;
        }

        $user->save();

        // Generate verification URL
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email)
            ]
        );

        // Send verification email
        EmailHelper::sendVerificationEmail($user->email, $user->username, $verificationUrl);

        // Generate token for API
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

        // Check if user exists
        if (!$user) {
            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }

        // Check if email is verified
        if (!$user->email_verified_at) {
            return response()->json([
                'message' => 'Email belum diverifikasi'
            ], 403);
        }

        // Check password
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
            return response()->json([
                'message' => 'Link verifikasi tidak valid'
            ], 400);
        }

        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'Email sudah terverifikasi'
            ]);
        }

        $user->email_verified_at = Carbon::now();
        $user->save();

        return response()->json([
            'message' => 'Email berhasil diverifikasi'
        ]);
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

        // Generate reset token
        $token = Str::random(64);

        // Save token to database
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => $token,
                'created_at' => Carbon::now()
            ]
        );

        // Generate reset URL
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

        // Check if token is expired (60 minutes)
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

    public function updateProfilePicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $user = $request->user();

        // Delete old profile picture if not default
        if ($user->profile_picture !== 'default.jpg') {
            Storage::delete('public/profile_picture/' . $user->profile_picture);
        }

        // Upload new profile picture
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