<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPasswordVerified
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('password_verified')) {
            return response()->json([
                'status' => false,
                'message' => 'Anda belum memverifikasi password lama.',
            ], 403);
        }

        return $next($request);
    }
}
