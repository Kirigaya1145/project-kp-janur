<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuktiPembayaran extends Model
{
    protected $table = 'bukti_pembayaran';
    protected $primaryKey = 'bukti_id';

    protected $fillable = [
        'invoice_id', 'jumlah_bayar', 'file_bukti', 'tanggal_upload',
        'status_konfirmasi', 'dikonfirmasi_oleh', 'tanggal_konfirmasi', 'catatan_admin',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'invoice_id');
    }

    public function konfirmator()
    {
        return $this->belongsTo(User::class, 'dikonfirmasi_oleh', 'user_id');
    }
}
