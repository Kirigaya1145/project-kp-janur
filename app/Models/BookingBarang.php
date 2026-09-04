<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingBarang extends Model
{
    protected $table = 'booking_barang';
    protected $primaryKey = 'booking_barang_id';

    protected $fillable = [
        'booking_id', 'kategori_barang', 'nama_barang', 'qty', 'berat_kg', 'keterangan',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }
}
