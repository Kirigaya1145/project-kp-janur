@php($restore=old('account_form')===$prefix)
@foreach(['nama'=>'Nama','email'=>'Email','no_hp'=>'Nomor HP','alamat'=>'Alamat'] as $field=>$label)
<div class="col-md-6"><label class="form-label" for="{{ $prefix.'-'.$field }}">{{ $label }}</label><input id="{{ $prefix.'-'.$field }}" type="{{ $field==='email'?'email':'text' }}" name="{{ $field }}" class="form-control" value="{{ $restore?old($field):($account?->$field ?? '') }}" @required(in_array($field,['nama','email']))></div>
@endforeach
<div class="col-md-6"><label class="form-label">Peran</label><select name="role" class="form-select">@foreach(['customer','staff','admin'] as $role)<option value="{{ $role }}" @selected(($restore?old('role'):($account?->role ?? 'customer'))===$role)>{{ ucfirst($role) }}</option>@endforeach</select></div>
<div class="col-md-6"><label class="form-label">Password {{ $account?'Baru (opsional)':'' }}</label><input name="password" type="password" minlength="8" class="form-control" autocomplete="new-password" @required(!$account)></div>
<div class="col-md-6"><label class="form-label">Konfirmasi Password</label><input name="password_confirmation" type="password" class="form-control" autocomplete="new-password" @required(!$account)></div>
