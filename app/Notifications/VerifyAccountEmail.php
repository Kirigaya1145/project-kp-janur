<?php
namespace App\Notifications;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
class VerifyAccountEmail extends VerifyEmail {
    protected function buildMailMessage($url) {
        return (new MailMessage)->subject('Verifikasi Email — Janur Tangguh Abadi')
            ->greeting('Halo!')->line('Klik tombol berikut untuk memverifikasi email dan menyelesaikan registrasi Anda.')
            ->action('Verifikasi Email',$url)->line('Jika Anda tidak membuat akun ini, abaikan email ini.');
    }
}
