<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratJalan extends Model
{
    protected $table = 'surat_jalan';
    protected $primaryKey = 'surat_jalan_id';

    protected $fillable = [
        'no_surat_jalan', 'booking_id', 'tanggal', 'kendaraan', 'nopol_kendaraan',
        'nama_sopir', 'penerima_kepada', 'lokasi_penerima', 'nama_pengirim',
        'nama_penerima_ttd', 'tanggal_diterima',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }
}
