<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RuteHarga extends Model
{
    protected $table = 'rute_harga';
    protected $primaryKey = 'rute_id';

    protected $fillable = [
        'pelabuhan_asal', 'pelabuhan_tujuan', 'harga_dasar', 'keterangan',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'rute_id', 'rute_id');
    }
}
