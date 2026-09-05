@extends('layouts.app')

@section('title', 'Riwayat Booking')

@section('content')
<section class="section-soft py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h1 class="h3 text-navy mb-1">Riwayat Booking</h1>
                <p class="text-steel mb-0">Pantau penawaran, invoice, dan pembayaran Anda.</p>
            </div>
            <a href="{{ route('booking.create') }}" class="btn btn-accent"><i class="bi bi-plus-lg me-1"></i> Booking Baru</a>
        </div>

        <div class="bg-white border rounded-3 table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Kode</th>
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
                            <td>{{ $booking->asal }} - {{ $booking->tujuan }}</td>
                            <td>Rp {{ number_format($booking->harga_estimasi ?? 0, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($booking->harga_final ?? 0, 0, ',', '.') }}</td>
                            <td><span class="badge text-bg-light border">{{ $booking->statusSederhana() }}</span></td>
                            <td class="text-end"><a href="{{ route('booking.show', $booking->kode_booking) }}" class="btn btn-sm btn-outline-accent">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-steel py-4">Belum ada booking.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
