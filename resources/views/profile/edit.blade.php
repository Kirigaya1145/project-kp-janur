@extends('layouts.app')
@section('title','Edit Profil')
@section('content')
<div class="container py-5" style="max-width:760px"><h1 class="h3 text-navy mb-4">Edit Profil</h1>
<form method="POST" action="{{ route('profile.update') }}" class="border rounded-3 p-4">@csrf @method('PUT')
@foreach(['nama'=>'Nama','email'=>'Email','no_hp'=>'Nomor HP'] as $field=>$label)
<div class="mb-3"><label for="{{ $field }}" class="form-label">{{ $label }}</label>
<input id="{{ $field }}" name="{{ $field }}" type="{{ $field==='email'?'email':'text' }}" class="form-control" value="{{ old($field,$user->$field) }}" @required($field!=='no_hp')></div>
@endforeach
<div class="mb-3"><label class="form-label" for="alamat">Alamat</label><textarea id="alamat" name="alamat" class="form-control">{{ old('alamat',$user->alamat) }}</textarea></div>
<div class="mb-3"><label class="form-label" for="current_password">Password Saat Ini</label><input id="current_password" name="current_password" type="password" autocomplete="current-password" class="form-control" required></div>
<div class="mb-3"><label class="form-label" for="password">Password Baru (opsional)</label><input id="password" name="password" type="password" minlength="8" autocomplete="new-password" class="form-control"></div>
<div class="mb-3"><label class="form-label" for="password_confirmation">Konfirmasi Password Baru</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="form-control"></div>
<button class="btn btn-accent">Simpan Profil</button></form></div>
@endsection
