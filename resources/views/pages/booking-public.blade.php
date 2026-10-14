@extends('layouts.app')
@section('content')
<div class="container py-5"><h1 class="h3">Status Booking {{ $booking->kode_booking }}</h1>
<p>{{ $booking->statusSederhana() }}</p>
<p>Masuk menggunakan akun pemesan untuk melihat detail pengiriman dan invoice.</p>
<a class="btn btn-accent" href="{{ route('login') }}">Login</a></div>
@endsection
