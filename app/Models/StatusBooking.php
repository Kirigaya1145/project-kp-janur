<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusBooking extends Model
{
    protected $table = 'status_bookings';
    protected $primaryKey = 'status_id';

    protected $fillable = [
        'booking_id', 'updated_by', 'status', 'keterangan',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by', 'user_id');
    }
}
