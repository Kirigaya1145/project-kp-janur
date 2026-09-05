<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $primaryKey = 'booking_id';
    public $timestamps = false;

    protected $fillable = [
        'kode_booking', 'user_id', 'nama_customer', 'email_customer', 'no_hp_customer',
        'rute_id', 'harga_estimasi', 'harga_final', 'status_harga', 'diberikan_oleh',
        'tanggal_konfirmasi', 'estimasi_waktu', 'tanggal_booking', 'tanggal_pengiriman',
        'waktu_muat', 'jumlah_container', 'asal', 'tujuan', 'total_harga',
        'status_booking',
    ];

    protected $casts = [
        'tanggal_konfirmasi' => 'datetime',
        'tanggal_booking' => 'date',
        'tanggal_pengiriman' => 'date',
        'waktu_muat' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function staffPemberiHarga()
    {
        return $this->belongsTo(User::class, 'diberikan_oleh', 'user_id');
    }

    public function rute()
    {
        return $this->belongsTo(RuteHarga::class, 'rute_id', 'rute_id');
    }

    public function barang()
    {
        return $this->hasMany(BookingBarang::class, 'booking_id', 'booking_id');
    }

    public function container()
    {
        return $this->hasMany(BookingContainer::class, 'booking_id', 'booking_id');
    }

    public function statusHistory()
    {
        return $this->hasMany(StatusBooking::class, 'booking_id', 'booking_id');
    }

    public function suratJalan()
    {
        return $this->hasMany(SuratJalan::class, 'booking_id', 'booking_id');
    }

    public function invoice()
    {
        return $this->hasMany(Invoice::class, 'booking_id', 'booking_id');
    }

    public function latestInvoice()
    {
        return $this->hasOne(Invoice::class, 'booking_id', 'booking_id')->latestOfMany('invoice_id');
    }

    public function totalBerat(): float
    {
        return (float) $this->barang->sum(fn ($item) => (float) $item->berat_kg);
    }

    public function statusSederhana(): string
    {
        return match ($this->status_booking) {
            'menunggu_penawaran' => 'Menunggu Penawaran',
            'menunggu_konfirmasi_customer' => 'Menunggu Konfirmasi',
            'menunggu_pembayaran', 'pembayaran_ditolak' => 'Menunggu Pembayaran',
            'menunggu_verifikasi_pembayaran' => 'Verifikasi Pembayaran',
            'siap_operasional', 'dalam_pengiriman' => 'Proses Pengiriman',
            'diterima', 'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
            'penawaran_ditolak' => 'Penawaran Ditolak',
            default => match ($this->status_harga) {
                'sudah_ditawarkan' => 'Menunggu Konfirmasi',
                'dikonfirmasi_customer' => 'Menunggu Pembayaran',
                'ditolak_customer' => 'Penawaran Ditolak',
                default => 'Menunggu Penawaran',
            },
        };
    }
}
