@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<section class="section-soft py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h1 class="h3 text-navy mb-1">Dashboard Booking</h1>
                <p class="text-steel mb-0">Kelola review harga, invoice, pembayaran, dan pengiriman.</p>
            </div>
            <a href="{{ route('admin.rute.index') }}" class="btn btn-outline-accent"><i class="bi bi-geo-alt me-1"></i> Kelola Rute</a>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3"><div class="bg-white border rounded-3 p-3"><div class="small text-steel">Total Booking</div><div class="h4 mb-0">{{ $bookings->count() }}</div></div></div>
            <div class="col-md-3"><div class="bg-white border rounded-3 p-3"><div class="small text-steel">Perlu Penawaran</div><div class="h4 mb-0">{{ $bookings->filter(fn ($booking) => $booking->statusSederhana() === 'Menunggu Penawaran')->count() }}</div></div></div>
            <div class="col-md-3"><div class="bg-white border rounded-3 p-3"><div class="small text-steel">Menunggu Bayar</div><div class="h4 mb-0">{{ $bookings->filter(fn ($booking) => $booking->statusSederhana() === 'Menunggu Pembayaran')->count() }}</div></div></div>
            <div class="col-md-3"><div class="bg-white border rounded-3 p-3"><div class="small text-steel">Proses Kirim</div><div class="h4 mb-0">{{ $bookings->filter(fn ($booking) => $booking->statusSederhana() === 'Proses Pengiriman')->count() }}</div></div></div>
        </div>

        <div class="bg-white border rounded-3 table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Customer</th>
                        <th>Rute</th>
                        <th>Estimasi</th>
                        <th>Final</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        <tr>
                            <td class="fw-semibold">{{ $booking->kode_booking }}</td>
                            <td>{{ $booking->nama_customer }}</td>
                            <td>{{ $booking->asal }} - {{ $booking->tujuan }}</td>
                            <td>Rp {{ number_format($booking->harga_estimasi ?? 0, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($booking->harga_final ?? 0, 0, ',', '.') }}</td>
                            <td><span class="badge text-bg-light border">{{ $booking->statusSederhana() }}</span></td>
                            <td class="text-end"><a href="{{ route('admin.booking.show', $booking) }}" class="btn btn-sm btn-outline-accent">Kelola</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-steel py-4">Belum ada booking.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
