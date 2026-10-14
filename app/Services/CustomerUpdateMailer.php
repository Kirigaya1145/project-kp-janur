<?php
namespace App\Services;

use App\Models\{Booking,User};
use App\Notifications\CustomerUpdate;
use Illuminate\Support\Facades\{Notification,Log};

class CustomerUpdateMailer
{
    public function booking(Booking $booking, string $title, array $details = []): void
    {
        $booking->refresh()->load('customer');
        // Notify the account that owns the booking, including bookings under another name.
        // Old bookings without an account can use their recorded customer email.
        $email = $booking->customer?->email ?: $booking->email_customer;
        $snapshot = ['name'=>$booking->customer?->nama ?: $booking->nama_customer,
            'code'=>$booking->kode_booking, 'route'=>$booking->asal.' → '.$booking->tujuan,
            'status'=>$booking->statusSederhana(), 'url'=>route('booking.show',$booking->kode_booking)];
        $this->send($email,$snapshot,$title,$details);
    }

    public function account(User $user, array $details): void
    {
        if ($user->role !== 'customer') return;
        $this->send($user->email,['name'=>$user->nama,'url'=>route('profile.edit')],
            'Data akun Anda diperbarui oleh admin',$details);
    }

    private function send(?string $email, array $snapshot, string $title, array $details): void
    {
        if (!filter_var($email,FILTER_VALIDATE_EMAIL)) {
            Log::warning('Customer update email missing',['booking'=>$snapshot['code'] ?? null]);
            session()->flash('email_warning','Pembaruan tersimpan, tetapi alamat email customer tidak tersedia atau tidak valid.');
            return;
        }
        try {
            Notification::route('mail',[$email=>$snapshot['name']])->notify(new CustomerUpdate($snapshot,$title,$details));
        } catch (\Throwable $error) {
            // Sending happens after database commit. SMTP failure must not undo a saved update.
            Log::error('Customer update email failed',['booking'=>$snapshot['code'] ?? null,'exception'=>$error]);
            session()->flash('email_warning','Pembaruan tersimpan, tetapi email belum terkirim. Periksa konfigurasi SMTP dan log aplikasi.');
        }
    }
}
