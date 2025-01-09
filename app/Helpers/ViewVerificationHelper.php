<?php

namespace App\Helpers;

class ViewVerificationHelper
{
    public static function emailVerificationPage($status = 'success', $message = null)
    {
        $html = '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Email Verification</title>
            <script src="https://cdn.tailwindcss.com"></script>
        </head>
        <body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">';

        if ($status === 'success') {
            $html .= '
            <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
                <div class="mb-6">
                    <svg class="mx-auto h-16 w-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-3">Email Berhasil Diverifikasi!</h1>
                <p class="text-gray-600 mb-6">' . ($message ?? 'Terima kasih telah memverifikasi email Anda. Akun Anda sekarang sudah aktif.') . '</p>
                <a href="' . config('app.frontend_url') . '/login" class="inline-block bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition-colors">
                    Login ke Aplikasi
                </a>
            </div>';
        } else {
            $html .= '
            <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
                <div class="mb-6">
                    <svg class="mx-auto h-16 w-16 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-3">Verifikasi Gagal</h1>
                <p class="text-gray-600 mb-6">' . ($message ?? 'Link verifikasi tidak valid atau sudah kadaluarsa.') . '</p>
                <a href="' . config('app.frontend_url') . '" class="inline-block bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition-colors">
                    Kembali ke Beranda
                </a>
            </div>';
        }

        $html .= '
        </body>
        </html>';

        return $html;
    }
}