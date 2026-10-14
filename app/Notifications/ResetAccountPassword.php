<?php
namespace App\Notifications;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
class ResetAccountPassword extends ResetPassword {
    public function toMail($notifiable) {
        $url=url(route('password.reset',['token'=>$this->token,'email'=>$notifiable->getEmailForPasswordReset()],false));
        return (new MailMessage)->subject('Reset Password — Janur Tangguh Abadi')
            ->greeting('Halo!')->line('Kami menerima permintaan untuk mengganti password akun Anda.')
            ->action('Reset Password',$url)->line('Tautan berlaku selama '.config('auth.passwords.users.expire').' menit.')
            ->line('Jika Anda tidak meminta reset password, abaikan email ini.');
    }
}
