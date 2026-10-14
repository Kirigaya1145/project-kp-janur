@extends('layouts.app')
@section('title','Tarif Rute dan Kontainer')
@section('content')
<div class="container py-5"><div class="d-flex justify-content-between mb-4"><h1 class="h3">Tarif Rute dan Kontainer</h1><a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Dashboard</a></div>
<p>Harga per satu kontainer penuh. Kombinasi rute dan ukuran menentukan estimasi booking.</p>
<div class="border rounded-3 p-4 mb-4"><h2 class="h5">Tambah Rute</h2>
@include('admin.rute-form',['rute'=>null])</div>
@foreach($rutes as $rute)
<details class="border rounded-3 p-3 mb-3"><summary>{{ $rute->pelabuhan_asal }} → {{ $rute->pelabuhan_tujuan }} —
@foreach([20,40] as $size) {{ $size }} feet: {{ $rute->tarifs->firstWhere('ukuran_feet',$size)?'Rp '.number_format($rute->tarifs->firstWhere('ukuran_feet',$size)->harga,0,',','.'): 'Belum diatur' }} @endforeach
</summary><div class="mt-3">@include('admin.rute-form')</div></details>
@endforeach</div>
@endsection
