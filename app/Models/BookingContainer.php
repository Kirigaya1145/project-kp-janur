<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingContainer extends Model
{
    protected $table = 'booking_container';
    protected $primaryKey = 'container_id';
    public $timestamps = false;

    protected $fillable = [
        'booking_id', 'joa_number', 'no_container', 'shipping_line',
        'feeder_vessel', 'connecting_vessel', 'destination',
        'stuff_date', 'etd', 'eta',
    ];

    protected $casts = [
        'stuff_date' => 'date',
        'etd' => 'date',
        'eta' => 'date',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }
}
