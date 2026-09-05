@extends('layouts.app')

@section('title', 'Booking Berhasil')

@section('content')
<section class="section-soft py-5">
    <div class="container">
        <div class="bg-white border rounded-3 p-5 text-center mx-auto" style="max-width: 680px;">
            <div class="fs-1 text-success mb-3"><i class="bi bi-check-circle"></i></div>
            <h1 class="h3 text-navy">Booking Berhasil Diajukan</h1>
            <p class="text-steel mb-3">Kode booking Anda:</p>
            <div class="display-6 fw-bold text-navy mb-4">{{ $kodeBooking }}</div>
            <p class="text-steel">Tim PT Janur Tangguh Abadi akan meninjau barang dan rute untuk menentukan harga final.</p>
            <div class="d-flex justify-content-center gap-2 flex-wrap">
                <a href="{{ route('booking.show', $kodeBooking) }}" class="btn btn-accent">Lihat Status</a>
                <a href="{{ route('booking.create') }}" class="btn btn-outline-accent">Booking Baru</a>
            </div>
        </div>
    </div>
</section>
@endsection
