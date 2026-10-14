<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CustomerUpdate extends Notification
{
    public function __construct(public array $snapshot, public string $title, public array $details = []) {}

    public function via($notifiable): array { return ['mail']; }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)->subject($this->title.' — PT Janur Tangguh Abadi')
            ->greeting('Halo '.$this->snapshot['name'].',')
            ->line($this->title);
        if (!empty($this->snapshot['code'])) {
            $mail->line('Kode booking: '.$this->snapshot['code'])
                ->line('Rute: '.$this->snapshot['route'])
                ->line('Status: '.$this->snapshot['status']);
        }
        foreach ($this->details as $detail) {
            if ($detail !== null && $detail !== '') $mail->line((string)$detail);
        }
        return $mail->action(empty($this->snapshot['code']) ? 'Buka Akun' : 'Lihat Detail Booking', $this->snapshot['url'])
            ->line('Login menggunakan akun pemesan untuk melihat detail lengkap.')
            ->salutation('PT Janur Tangguh Abadi');
    }
}
