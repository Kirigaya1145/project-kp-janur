<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = 'invoice';
    protected $primaryKey = 'invoice_id';
    public $timestamps = false;

    protected $fillable = [
        'no_invoice', 'booking_id', 'tanggal_invoice', 'subtotal', 'ppn_persen',
        'ppn_nominal', 'total_bayar', 'terms', 'status_bayar', 'tanggal_lunas', 'catatan',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }

    public function buktiPembayaran()
    {
        return $this->hasMany(BuktiPembayaran::class, 'invoice_id', 'invoice_id');
    }
}
