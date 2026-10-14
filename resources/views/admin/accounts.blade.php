@extends('layouts.app')
@section('title','Kelola Akun')
@section('content')
<div class="container py-5"><div class="d-flex justify-content-between"><h1 class="h3">Kelola Akun</h1><a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Dashboard</a></div>
<form method="GET" class="d-flex gap-2 my-4"><input name="q" class="form-control" placeholder="Cari nama atau email" value="{{ $search }}"><button class="btn btn-accent">Cari</button></form>
<details class="border rounded-3 p-3 mb-4" @if(old('account_form')==='new') open @endif><summary class="fw-bold">Tambah Akun</summary>
<form method="POST" action="{{ route('admin.accounts.store') }}" class="row g-3 mt-2">@csrf
<input type="hidden" name="account_form" value="new">
@include('admin.account-fields',['account'=>null,'prefix'=>'new'])
<div><button class="btn btn-accent">Tambah Akun</button></div></form></details>
@foreach($users as $account)
<details class="border rounded-3 p-3 mb-3" @if(old('account_form')===(string)$account->user_id) open @endif><summary>{{ $account->nama }} — {{ $account->email }} ({{ $account->role }})</summary>
<form method="POST" action="{{ route('admin.accounts.update',$account) }}" class="row g-3 mt-2">@csrf @method('PUT')
<input type="hidden" name="account_form" value="{{ $account->user_id }}">
@include('admin.account-fields',['prefix'=>(string)$account->user_id])
<div><button class="btn btn-accent">Simpan Perubahan</button></div></form></details>
@endforeach
{{ $users->links() }}</div>
@endsection
