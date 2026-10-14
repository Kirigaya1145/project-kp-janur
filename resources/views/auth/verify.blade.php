@extends('layouts.app')
@section('title','Verifikasi Email')
@section('content')
<div class="container py-5" style="max-width:760px"><div class="border rounded-3 p-4">
<h1 class="h3">Verifikasi Email Anda</h1>
<p>Tautan verifikasi dikirim ke <strong>{{ auth()->user()->email }}</strong>. Buka email tersebut dan klik tautan untuk menyelesaikan registrasi.</p>
@if(session('resent'))<div class="alert alert-success">Tautan verifikasi baru berhasil dikirim.</div>@endif
<p>Periksa folder spam jika email belum masuk.</p>
<form method="POST" action="{{ route('verification.resend') }}">@csrf<button class="btn btn-accent">Kirim Ulang Email Verifikasi</button></form>
<a class="d-inline-block mt-3" href="{{ route('profile.edit') }}">Perbaiki alamat email</a>
</div></div>
@endsection
