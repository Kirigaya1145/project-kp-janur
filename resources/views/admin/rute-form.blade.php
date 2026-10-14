<form method="POST" action="{{ route('admin.rute.store') }}" class="row g-3">@csrf
@foreach(['pelabuhan_asal'=>'Pelabuhan Asal','pelabuhan_tujuan'=>'Pelabuhan Tujuan'] as $field=>$label)
<div class="col-md-6"><label class="form-label">{{ $label }}</label><input name="{{ $field }}" class="form-control" value="{{ $rute?->$field ?? old($field) }}" @readonly($rute) required></div>
@endforeach
@foreach([20,40] as $size)
<div class="col-md-6"><label class="form-label">Tarif {{ $size }} feet per Kontainer (Rp)</label><input type="number" min="1" max="9999999999" step="0.01" name="harga_{{ $size }}" class="form-control" value="{{ $rute?->tarifs->firstWhere('ukuran_feet',$size)?->harga ?? old('harga_'.$size) }}" required></div>
@endforeach
<div><label class="form-label">Keterangan</label><textarea name="keterangan" class="form-control">{{ $rute?->keterangan ?? old('keterangan') }}</textarea></div>
<div><button class="btn btn-accent">Simpan Tarif</button></div></form>
