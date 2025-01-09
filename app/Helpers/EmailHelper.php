<?php

namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
use Illuminate\Support\Facades\Log;

class EmailHelper
{
    public static function sendVerificationEmail($email, $username, $verificationUrl)
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
            $mail->addAddress($email, $username);

            $mail->isHTML(true);
            $mail->Subject = 'Verifikasi Email';
            $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; background-color: #f9f9f9;'>
                <h1 style='color: #333; text-align: center;'>Verifikasi Email</h1>
                <p style='font-size: 16px; color: #555;'>Halo $username,</p>
                <p style='font-size: 16px; color: #555;'>Terima kasih telah mendaftar! Silakan verifikasi email Anda dengan mengklik tombol di bawah ini:</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='$verificationUrl' style='background-color: #4CAF50; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-size: 16px;'>Verifikasi Email</a>
                </div>
                <p style='font-size: 14px; color: #777; margin-top: 20px;'>Jika Anda mengalami masalah dengan tombol di atas, salin dan tempel URL berikut ke browser Anda:</p>
                <p style='font-size: 14px; color: #777;'>$verificationUrl</p>
            </div>";

            $mail->send();
            Log::info("Email verifikasi berhasil dikirim ke $email");
            return true;
        } catch (Exception $e) {
            Log::error("Gagal mengirim email verifikasi: {$mail->ErrorInfo}");
            return false;
        }
    }

    public static function sendResetPasswordEmail($email, $username, $resetUrl)
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
            $mail->addAddress($email, $username);

            $mail->isHTML(true);
            $mail->Subject = 'Reset Password';
            $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; background-color: #f9f9f9;'>
                <h1 style='color: #333; text-align: center;'>Reset Password</h1>
                <p style='font-size: 16px; color: #555;'>Halo $username,</p>
                <p style='font-size: 16px; color: #555;'>Kami menerima permintaan untuk mereset password akun Anda.</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='$resetUrl' style='background-color: #4CAF50; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-size: 16px;'>Reset Password</a>
                </div>
                <p style='font-size: 16px; color: #555;'>Link reset password ini akan kadaluarsa dalam 60 menit.</p>
                <p style='font-size: 14px; color: #777; margin-top: 20px;'>Jika Anda mengalami masalah dengan tombol di atas, salin dan tempel URL berikut ke browser Anda:</p>
                <p style='font-size: 14px; color: #777;'>$resetUrl</p>
            </div>";

            $mail->send();
            Log::info("Email reset password berhasil dikirim ke $email");
            return true;
        } catch (Exception $e) {
            Log::error("Gagal mengirim email reset password: {$mail->ErrorInfo}");
            return false;
        }
    }
}