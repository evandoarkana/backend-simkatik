<?php

namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
use Illuminate\Support\Facades\Log;

class EmailHelper
{

    public static function sendEmail($email, $subject, $message)
    {
        $mail = new PHPMailer(true);

        try {
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            $mail->Debugoutput = function ($str, $level) {
                Log::info("SMTP Debug: $str");
            };

            $mail->isSMTP();
            $mail->Host = env('MAIL_HOST');
            $mail->SMTPAuth = true;
            $mail->Username = env('MAIL_USERNAME');
            $mail->Password = env('MAIL_PASSWORD');
            $mail->SMTPSecure = env('MAIL_ENCRYPTION');
            $mail->Port = env('MAIL_PORT');

            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;

            $mail->send();
            Log::info("Email berhasil dikirim ke $email");
            return true;
        } catch (Exception $e) {
            Log::error("Gagal mengirim email: {$mail->ErrorInfo}");
            return false;
        }
    }

    public static function sendVerificationEmail($email, $username, $verificationUrl)
    {
        $emailSubject = 'Verifikasi Email';
        $emailMessage = "
    <div
    style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; background-color: #f9f9f9;'>
    <h1 style='color: #333; text-align: center;'>Verifikasi Email</h1>
    <p style='font-size: 16px; color: #555;'>Halo $username,</p>
    <p style='font-size: 16px; color: #555;'>Terima kasih telah mendaftar! Silakan verifikasi email Anda dengan mengklik
        tombol di bawah ini:</p>
        <div style='text-align: center; margin: 30px 0;'>
            <a href='$verificationUrl'
            style='background-color: #4CAF50; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-size: 16px;'>Verifikasi
            Email</a>
        </div>
    </div>";

        return self::sendEmail($email, $emailSubject, $emailMessage);
    }

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

    public static function sendResetPasswordVerificationEmail($email, $username, $verificationCode)
    {
        $emailSubject = 'Kode Verifikasi untuk Reset Password';
        $emailMessage = "
        <div
            style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; background-color: #f9f9f9;'>
            <h1 style='color: #333; text-align: center;'>Verifikasi Reset Password</h1>
            <p style='font-size: 16px; color: #555;'>Halo $username,</p>
            <p style='font-size: 16px; color: #555;'>Kami menerima permintaan untuk mereset password akun Anda. Berikut adalah kode verifikasi untuk melanjutkan proses reset password Anda:</p>
            <div style='text-align: center; margin: 30px 0;'>
                <strong style='color: black; padding: 12px 30px; border-radius: 5px; font-size: 20px;'>$verificationCode</strong>
            </div>
            <p style='font-size: 14px; color: #777;'>Jika Anda tidak merasa meminta reset password, abaikan email ini.</p>
        </div>";

        return self::sendEmail($email, $emailSubject, $emailMessage);
    }

}


